<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );
/**
 * CLAROLINE
 *
 * Config lib contain function to manage conf file
 *
 * @version 1.8 $Revision$
 *
 * @copyright 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @see http://www.claroline.net/wiki/config_def/
 *
 * @package CONFIG
 *
 * @author Claro Team <cvs@claroline.net>
 * @author Christophe Gesché <moosh@claroline.net>
 * @author Mathieu Laurent <laurent@cerdecam.be>
 *
 */

require_once dirname(__FILE__) . '/config.class.php';

/**
 * Proceed to rename conf.php.dist file in unexisting .conf.php files
 *
 * @param string $file syspath:complete path to .dist file
 *
 * @return boolean whether succes return true
 *
 * @author Mathieu Laurent <laurent@cerdecam.be>
 *
 */

function claro_undist_file ($file)
{
    if ( !file_exists($file))
    {
        if ( file_exists($file.".dist"))
        {
            /**
             * @var $perms file permission of dist file are keep to set perms of new file
             */

            $perms = fileperms($file.".dist");

            /**
             * @var $group internal var for affect same group to new file
             */

            $group = filegroup($file.".dist");

            // $perms|bindec(110000) <- preserve perms but force rw right on group
            @copy($file.".dist",$file) && chmod ($file,$perms|bindec(110000)) && @chgrp($file,$group);
            if (file_exists($file))
            {
                return TRUE;
            }
            else
            {
                return FALSE;
            }
        }
        else
        {
            return FALSE;
        }
    }
    else
    {
        return TRUE;
    }
}

/**
 * The boolean value as string
 *
 * @param $booleanState boolean
 *
 * @return string boolean value as string
 *
 */

function trueFalse($booleanState)
{
    return ($booleanState?'TRUE':'FALSE');
}

/**
 * return the path of a def file following the configCode
 *
 * @param string $configCode
 * @return path
 *
 * @todo $centralizedDef won't be hardcoded.
 */

function claro_get_conf_def_file($configCode)
{
    $centralizedDef = array('CLCRS','CLAUTH', 'CLSSO',  'CLCAS', 'CLHOME', 'CLKCACHE','CLLINKER','CLMAIN','CLPROFIL' ,'CLRSS','CLICAL','CLGRP');
    if(in_array($configCode,$centralizedDef)) return realpath($GLOBALS['includePath'] . '/conf/def/') ;
    else                                      return get_module_path($configCode) . '/conf/def/';
}

/**
 * Generate the conf for a given config
 *
 * @param  object $config instance of config to manage.
 * @param  array $properties array of properties to changes
 *
 * @return array list of messages and error tag
 */

function generate_conf(&$config,$properties = null)
{
    // load configuration if not loaded before
    if ( !$config->def_loaded )
    {
        if ( !$config->load() )
        {
            // error loading the configuration
            $message = $config->backlog->output();
            return array($message , false);
        }
    }

    $config_code = $config->conf_def['config_code'];
    $config_name = $config->conf_def['config_name'];

    // validate config
    if ( $config->validate($properties) )
    {
        // save config file
        $config->save();
        $message[] = get_lang('Properties for %config_name, (%config_code) are now effective on server.'
        , array('%config_name' => $config_name, '%config_code' => $config_code));
    }
    else
    {
        // no valid
        $error = true ;
        $message = $config->backlog->output();
    }

    if (!empty($error))
    {
        return array ($message, true);
    }
    else
    {
        return array ($message, false);
    }
}

/**
 * Return array list of found definition files
 * @return array list of found definition files
 * @global string includePath use to access to def repository.
 */

function get_def_file_list($type = 'all')
{
    require_once(dirname(__FILE__) . '/module.manage.lib.php');

    //path where we can search defFile : kernel and modules
    // defs of kernel
    if ($type == 'kernel' || $type == 'all')
    $defConfPathList[] = $GLOBALS['includePath'] . '/conf/def';

    // defs of modules
    if ($type == 'module' || $type == 'all')
    {
        $moduleList = get_installed_module_list();
        foreach ($moduleList as $module)
        {
            $possiblePath = get_module_path($module) . '/conf/def';
            if (file_exists($possiblePath)) $defConfPathList[] = $possiblePath;
        }
    }

    $defConfFileList = array();

    foreach ($defConfPathList as $defConfPath)
    {
        if (is_dir($defConfPath) && $handle = opendir($defConfPath))
        {
            // group of def list
            // Browse folder of definition file

            while (FALSE !== ($file = readdir($handle)))
            {

                if ($file != "." && $file != ".." && substr($file, -17)=='.def.conf.inc.php')
                {
                    $config_code = str_replace('.def.conf.inc.php','',$file);
                    $config = new Config($config_code);
                    if($config->load())
                    {
                        $defConfFileList[$config_code]['name'] = $config->get_conf_name($config_code);
                        $defConfFileList[$config_code]['class'] = $config->get_conf_class();
                    }
                }
            }
            closedir($handle);
        }
    }
    return $defConfFileList;
}

?>
