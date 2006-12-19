<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );
/**
 * CLAROLINE
 *
 * @version 1.8 $Revision$
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @author claroline Team <cvs@claroline.net>
 * @author Renaud Fallier <renaud.claroline@gmail.com>
 * @author Frédéric Minne <minne@ipm.ucl.ac.be>
 *
 * @package CLLINKER
 *
 */

   require_once dirname(__FILE__) . '/navigator.lib.php';

    /**
    * Class Document Navigator
    *
    * @package CLDOC
    * @subpackage CLLINKER
    *
    * @author Fallier Renaud <renaud.claroline@gmail.com>
    */
    class CLDOC___Navigator extends Navigator
    {
        /*-------------------------
                 variable
         ------------------------*/
        var $_claroContainer;
        var $_basePath;

        /*----------------------------
                public method
        ---------------------------*/

        /**
        * Constructor
        *
        * @param  $basePath string path root directory of courses
        */
        function CLDOC___Navigator($basePath)
        {
            $this->_claroContainer = FALSE;
            $this->_basePath = $basePath;
        }

        /**
        * seek the repertory and the files for a current node
        *
        * @param  $node string of current node (crl)
        * @return ClaroContainer who contains the objects current node
        * @throws  E_USER_ERROR if the node is not intended for the tool document
        * @throws  E_USER_ERROR if the node is empty
        */
        function getResource($node = null)
        {
            if($node)
            {
                if(CRLTool::isForThisTool($node, 'CLDOC___'))
                {
                     $elementCRLArray = CRLTool::parseCRL($node);

                     // the base dir path is different if in groups
                     if( isset ($elementCRLArray["team"]) )
                     {
                        $secretDirectory = $this->_getSecretDirectory($elementCRLArray);

                         $baseDirPath = $this->_basePath."/".$elementCRLArray['course_sys_code']."/group";
                         $baseDirPath .= "/".$secretDirectory;
                     }
                     else
                     {
                         $baseDirPath = $this->_basePath."/".$elementCRLArray['course_sys_code']."/document";
                     }

                     // add the resource if it exists
                     if( isset ($elementCRLArray['resource_id']) )
                     {
                         $baseDirPath .= "/".$elementCRLArray['resource_id'];
                         $pathFromBase = "/".$elementCRLArray['resource_id'];
                     }
                     else
                     {
                         $pathFromBase = "";
                     }

                     $elementDirList = $this->_listingDir ($baseDirPath);
                     $elementList = array();

                     $tbl_directories = $elementDirList['directories'];
                     $tbl_files = $elementDirList['files'];

                     foreach ( $tbl_directories as $valeur)
                     {
                         $crl = $this->_createObjectCRL( $elementCRLArray , $valeur );

                         $filePath = $pathFromBase.'/'.$valeur;
                         $isVisible = $this->_isVisible($filePath,$elementCRLArray['course_sys_code']);
                         $container = new ClaroContainer( $valeur, $crl , FALSE , TRUE , $isVisible );
                         $elementList[] = $container ;
                     }

                     foreach ( $tbl_files as $valeur)
                     {
                         $crl = $this->_createObjectCRL( $elementCRLArray , $valeur );
                         $filePath =  $pathFromBase.'/'.$valeur;
                         $isVisible = $this->_isVisible($filePath,$elementCRLArray['course_sys_code']);
                         $object = new ClaroObject( $valeur, $crl, TRUE , FALSE , $isVisible);
                         $elementList[] = $object ;
                     }

                     $this->_claroContainer = new ClaroContainer ( "" , $node,$elementList );

                     return $this->_claroContainer;
                }
                else
                {
                    trigger_error ("Error : not crl for a document tool", E_USER_ERROR);
                }
            }
            // if the node is null
            else
            {
                trigger_error ('Error : crl is empty', E_USER_ERROR);
            }
        }

        /*----------------------------
                private method
        ---------------------------*/

        /**
        * List the content of the given directory
        *
        * @param $baseDirPath string path root directory of the file selector
        * @return array associative array containing two sub arrays
        *   directories containing subdirectories of currentDir,
        *   files containing files of currentDir
        */
        function _listingDir($baseDirPath)
        {
            $fileList = array();
            $fileList['directories'] = array();
            $fileList['files'] = array();

            if(is_dir($baseDirPath))
            {
                $dir = opendir($baseDirPath);

                while ( $read_file = readdir ( $dir ) )
                {
                    if ($read_file == '.' || $read_file == '..')
                    {
                        continue;
                    }

                    $path = $baseDirPath."/".$read_file;

                    if( is_dir( $path ) )
                    {
                        $fileList['directories'][] = $read_file;
                    }

                    elseif( is_file( $path ) )
                    {
                        $fileList['files'][] = $read_file;
                    }
                }
                closedir($dir);
            }
            else
            {
                trigger_error ("Error : is not a dir", E_USER_ERROR);
            }

            // sort the array
            natcasesort($fileList['directories']);
            natcasesort($fileList['files']);

            return $fileList;
        }

        /**
        * Create a new CRL with a crl and a element of resource_id
        *
        * @param $elementCRLArray associative array who contains the information of a crl
        * @param $partResourceId  string element of a resource_id
        * @return string a valide crl
        */
        function _createObjectCRL($elementCRLArray,$partResourceId)
        {

             if( isset($elementCRLArray['resource_id']) )
             {
                 $resource_id = $elementCRLArray['resource_id']."/".$partResourceId;
             }
             else
             {
                 $resource_id = $partResourceId;
             }

             if( isset($elementCRLArray["team"]) )
             {
                 $crl = CRLTool::createCRL(get_conf('platform_id') , $elementCRLArray['course_sys_code'] , $elementCRLArray["tool_name"] ,$resource_id ,$elementCRLArray["team"]);
             }
             else
             {
                 $crl = CRLTool::createCRL(get_conf('platform_id') , $elementCRLArray['course_sys_code'] , $elementCRLArray["tool_name"] ,$resource_id );
             }

             return $crl;
        }

        /**
        * test if the file is visible
        *
        * @param $filePath the resource_id of the file
        * @return boolean TRUE if the files is visible
        */
        function _isVisible($filePath , $course_sys_code)
        {
            $filePath = addslashes($filePath);
            $isVisible = TRUE;

            $course = get_info_course($course_sys_code);
            $tbl_cdb_names = claro_sql_get_course_tbl($course['dbNameGlu']);
            $dbTable = $tbl_cdb_names['document'];

            $sql = "SELECT `visibility` FROM `".$dbTable."` WHERE path ='".$filePath."'" ;
            $attributeList = claro_sql_query_fetch_all_cols($sql);

            if( isset($attributeList["visibility"]) && count($attributeList["visibility"]) > 0 )
            {
                $isVisible = ($attributeList["visibility"][0] != "i" );
            }

            return $isVisible;
        }

        /**
        *  search the name of the secret directory of a group.
        *
        * @param $elementCRLArray associative array who contains the information of a crl
        * @return string the name of the directory
        */
        function _getSecretDirectory($elementCRLArray)
        {
            $courseInfoArray = get_info_course($elementCRLArray['course_sys_code']);
            $tbl_cdb_names = claro_sql_get_course_tbl($courseInfoArray["dbNameGlu"]);
            $tbl_group = $tbl_cdb_names['group_team'];

            $sql = 'SELECT `secretDirectory` FROM `'.$tbl_group.'` WHERE `id` ='.$elementCRLArray["team"];
            $secretDirectory = claro_sql_query_get_single_value($sql);

            return $secretDirectory;
        }

    }
?>