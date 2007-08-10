<?php // $Id$

    // vim: expandtab sw=4 ts=4 sts=4:
    
    if ( count( get_included_files() ) == 1 )
    {
        die( 'The file ' . basename(__FILE__) . ' cannot be accessed directly, use include instead' );
    }
    
    /**
     * CLAROLINE
     * manifest parser class and utility functions
     * @version 1.9 $Revision$
     * @copyright 2001-2007 Universite catholique de Louvain (UCL)
     * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
     * @see http://www.claroline.net/wiki/index.php/Install
     * @author Claro Team <cvs@claroline.net>
     * @package MODULES
     */
    
    require_once dirname(__FILE__) . '/../backlog.class.php';

    /**
     *  Module manifest parser
     */
    class ModuleManifestParser
    {
        var $elementPile;
        var $moduleInfo;
        var $backlog;

        function ModuleManifestParser()
        {
            $this->backlog = new Backlog;
            $this->elementPile = array();
            $this->moduleInfo = array();
        }

        // TODO handle other module types
        /**
         *  Parse the manifest file given in argument
         *  @param   string manifestPath, path to the manifest file
         *  @return  bool false on failure, array moduleInfo on success
         */
        function parse( $manifestPath )
        {
            // reset state
            $this->elementPile = array();
            $this->moduleInfo = array();
            
            $this->backlog->info( 'Parsing manifest file ' . $manifestPath );
            
            if (! file_exists( $manifestPath ) )
            {
                $this->backlog->failure(get_lang('Manifest missing : %filename'
                    ,array('%filename' => $manifestPath)));
                return false;
            }

            $xmlParser = xml_parser_create();

            xml_set_element_handler( $xmlParser
                , array(&$this, 'startElement')
                , array(&$this, 'endElement') );

            xml_set_character_data_handler( $xmlParser
                , array(&$this, 'elementData') );

            // read manifest file

            if ( false === ($data = @file_get_contents($manifestPath) ) )
            {
                $this->backlog->failure(get_lang('Cannot open manifest file'));
                return false;
            }
            else
            {
                $this->backlog->debug('Manifest open : '.$manifestPath);
                $data = html_entity_decode(urldecode($data));
            }

            if ( !xml_parse($xmlParser, $data) )
            {
                // if reading of the xml file in not successfull :
                // set errorFound, set error msg, break while statement
                $this->backlog->failure(get_lang('Error while parsing manifest'));
                return false;
            }

            // liberate parser ressources
            xml_parser_free($xmlParser);

            return $this->moduleInfo;
        }

        /**
         *  SAX Parser Callback method : end of an element
         */
        function endElement($parser,$name)
        {
            array_pop($this->elementPile);
        }

        /**
         *  SAX Parser Callback method : start of an element
         */
        function startElement($parser, $name, $attributes)
        {
            array_push($this->elementPile,$name);
        }

        /**
         *  SAX Parser Callback method : data handler
         */
        function elementData($parser,$data)
        {
            $currentElement = end($this->elementPile);

            if ( claro_debug_mode() )
            {
                $this->backlog->debug( 'The metadata ' . $currentElement
                    . ' as been found with value ' . var_export($data,true) );
            }

            switch ($currentElement)
            {
                case 'TYPE' :
                {
                    $this->moduleInfo['TYPE'] = $data;
                    break;
                }
                case 'DESCRIPTION' :
                {
                    $this->moduleInfo['DESCRIPTION'] = $data;
                    break;
                }
                case 'EMAIL':
                {
                    $parent = prev($this->elementPile);
                    switch ($parent)
                    {
                        case 'AUTHOR':
                        {
                            $this->moduleInfo['AUTHOR']['EMAIL'] = $data;
                            break;
                        }
                    }
                    break;
                }
                case 'LABEL':
                {
                    $this->moduleInfo['LABEL'] = $data;
                    break;
                }
                case 'ENTRY':
                {
                    $this->moduleInfo['ENTRY'] = $data;
                    break;
                }
                case 'LICENSE':
                {
                    $this->moduleInfo['LICENSE'] = $data;
                    break;
                }
                case 'ICON':
                {
                    $this->moduleInfo['ICON'] =  $data;
                    break;
                }
                case 'NAME':
                {
                    $parent = prev($this->elementPile);
                    switch ($parent)
                    {
                        case 'MODULE':
                        {
                            $this->moduleInfo['NAME'] = $data;
                            break;
                        }
                        case 'AUTHOR':
                        {
                            $this->moduleInfo['AUTHOR']['NAME'] = $data;
                            break;
                        }
                    }
                    break;
                }
                case 'DEFAULT_DOCK' :
                {
                    if ( claro_debug_mode() )
                    {
                        $this->backlog->debug(
                            'The use of default_dock is deprecated in manifest file, please use defaultDock instead' );
                    }

                    // nobreak
                }
                case 'DEFAULTDOCK':
                {
                    if ( ! array_key_exists( 'DEFAULT_DOCK', $this->moduleInfo ) )
                    {
                        $this->moduleInfo['DEFAULT_DOCK'] = array();
                    }

                    $this->moduleInfo['DEFAULT_DOCK'][] = $data;
                    break;
                }
                case 'WEB':
                {
                    $parent = prev($this->elementPile);
                    switch ($parent)
                    {
                        case 'MODULE':
                        {
                            $this->moduleInfo['WEB'] = $data;
                            break;
                        }
                        case 'AUTHOR':
                        {
                            $this->moduleInfo['AUTHOR']['WEB'] = $data;
                            break;
                        }
                    }

                    break;
                }
                // PHP/MySQL/Claroline versions dependencies
                // TODO check in install
                case 'MINVERSION':
                {
                    $parent = prev($this->elementPile);
                    switch ($parent)
                    {
                        case 'PHP':
                        {
                            $this->moduleInfo['PHP_MIN_VERSION'] = $data;
                            break;
                        }
                        case 'MYSQL':
                        {
                            $this->moduleInfo['MYSQL_MIN_VERSION'] = $data;
                            break;
                        }
                        case 'CLAROLINE' :
                        {
                            $this->moduleInfo['CLAROLINE_MIN_VERSION'] = $data;
                            break;
                        }
                    }
                    break;
                }
                case 'MAXVERSION':
                {
                    $parent = prev($this->elementPile);
                    switch ($parent)
                    {
                        case 'PHP':
                        {
                            $this->moduleInfo['PHP_MAX_VERSION'] = $data;
                            break;
                        }
                        case 'MYSQL':
                        {
                            $this->moduleInfo['MYSQL_MAX_VERSION'] = $data;
                            break;
                        }
                        case 'CLAROLINE' :
                        {
                            $this->moduleInfo['CLAROLINE_MAX_VERSION'] = $data;
                            break;
                        }
                    }
                    break;
                }
                // FIXME I'm not sure this is cool...
                // if VERSION is 1.8 what about 1.8.* ?
                // check the behaviour of version_compare...
                case 'VERSION':
                {
                    $parent = prev($this->elementPile);
                    switch ($parent)
                    {
                        case 'MODULE':
                        {
                            $this->moduleInfo['VERSION'] = $data;
                            break;
                        }
                        case 'CLAROLINE' :
                        {
                            $this->moduleInfo['CLAROLINE_MIN_VERSION'] = $data;
                            $this->moduleInfo['CLAROLINE_MAX_VERSION'] = $data;
                            break;
                        }
                        case 'PHP':
                        {
                            $this->moduleInfo['PHP_MIN_VERSION'] = $data;
                            $this->moduleInfo['PHP_MAX_VERSION'] = $data;
                            break;
                        }
                        case 'MYSQL':
                        {
                            $this->moduleInfo['MYSQL_MIN_VERSION'] = $data;
                            $this->moduleInfo['MYSQL_MAX_VERSION'] = $data;
                            break;
                        }
                    }
                    break;
                }
            }
        }
    }
    
    /**
     *  Add missing optional elements to module info
     *  @param   array module_info
     *  @return  array, completed module_info
     */
    function completeModuleInfo( $module_info )
    {
        // complete module info for missing optional elements

        if ( ! array_key_exists( 'LICENSE', $module_info ) )
        {
            $module_info['LICENSE'] = '';
        }

        if ( ! array_key_exists( 'VERSION', $module_info ) )
        {
            $module_info['VERSION'] = '';
        }

        if ( ! array_key_exists( 'DESCRIPTION', $module_info ) )
        {
            $module_info['DESCRIPTION'] = '';
        }

        if ( ! array_key_exists( 'AUTHOR', $module_info ) )
        {
            $module_info['AUTHOR'] = array();
        }

        if ( ! array_key_exists( 'NAME', $module_info['AUTHOR'] ) )
        {
            $module_info['AUTHOR']['NAME'] = '';
        }

        if ( ! array_key_exists( 'EMAIL', $module_info['AUTHOR'] ) )
        {
            $module_info['AUTHOR']['EMAIL'] = '';
        }

        if ( ! array_key_exists( 'WEB', $module_info['AUTHOR'] ) )
        {
            $module_info['AUTHOR']['WEB'] = '';
        }

        if ( ! array_key_exists( 'WEB', $module_info ) )
        {
            $module_info['WEB'] = '';
        }
        
        return $module_info;
    }
    
    /**
     *  Check if the module information are valid :
     *      - is an array
     *      - in not empty
     *      - contains required elements (label, name, type)
     */
    function checkModuleInfo ( $module_info )
    {
        if ( ! is_array( $module_info ) || count( $module_info ) == 0 )
        {
            return claro_failure::set_failure(get_lang('Empty manifest'));
        }
        
        $missingElement = array_diff(array('LABEL','NAME','TYPE'),array_keys($module_info));

        if (count($missingElement)>0)
        {
            return claro_failure::set_failure(get_lang('Missing elements in module Manifest : %MissingElements' , array('%MissingElements' => implode(',',$missingElement))));
        }
        else
        {
            return true;
        }
    }
    
    /**
     *  Helper function to read, validate and complete module information from
     *  a manifest file
     */
    function readModuleManifest($modulePath)
    {
        $manifestPath = $modulePath. '/manifest.xml';
        
        if (! file_exists ($manifestPath) )
        {
            return claro_failure::set_failure(get_lang('Manifest missing : %filename',array('%filename' => $manifestPath)));
        }
        else
        {
            $parser = new ModuleManifestParser;
            $module_info = $parser->parse($manifestPath);
            
            if ( ! checkModuleInfo( $module_info ) )
            {
                return false;
            }
            else
            {
                return completeModuleInfo( $module_info );
            }
        }

    }
?>