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

    require_once dirname(__FILE__) . '/resolver.lib.php';

    /**
    * Class External ressource Resolver
    *
    * @package CLEXT
    * @subpackage CLLINKER
    *
    * @author Fallier Renaud <renaud.claroline@gmail.com>
    */
    class CLEXT___Resolver extends Resolver
    {
        /*----------------------------
                public method
        ---------------------------*/

        /**
        * Constructor
        *
        * @param  $basePath string path root directory of courses
        */
        function CLEXT___Resolver($basePath)
        {
        }

        /**
        * translated a crl into valid URL for the forum tool
        *
        * @param  $CRL string a crl
        * @return string a url valide who corresponds to the crl
        * @throws E_USER_ERROR if tool_name is empty
        * @throws E_USER_ERROR if it isn't for tool extern tool
        * @throws E_USER_ERROR if the crl is empty
        */
        function resolve($crl)
        {
           if($crl)
           {
               if(CRLTool::isForThisTool($crl,'CLEXT___'))
               {
                   $elementCRLArray = CRLTool::parseCRL($crl);

                   if( isset($elementCRLArray['resource_id']) )
                   {
                       $url = $elementCRLArray['resource_id'];
                       return $url;
                   }
                   else
                   {
                       trigger_error("ERROR: missing resource id",E_USER_ERROR);
                   }
               }
               else
               {
                   trigger_error("ERROR: isn't for extern tool",E_USER_ERROR);
               }
           }
           else
           {
               trigger_error("ERROR: crl is required",E_USER_ERROR);
           }
        }

       /**
        * the name of the resource which will be posted
        *
        * @param $crl a string who cotains the crl
        * @return string who contains the name of the resource
        * @throws  E_USER_ERROR if it isn't for extern tool
        **/
        function getResourceName($crl)
        {
            if(CRLTool::isForThisTool($crl,'CLEXT___'))
            {
                $elementCRLArray = CRLTool::parseCRL($crl);
                $tblTitle = $this->_getNameByUrl( $elementCRLArray['course_sys_code'] , $elementCRLArray['resource_id'] );
                $title = "rien";

                if($tblTitle)
                {
                    $title = get_course_title($elementCRLArray['course_sys_code']);
                    $title .= " > ". $tblTitle;
                }
                else
                {
                    $title = $elementCRLArray['resource_id'];
                }

                return $title;
            }
            else
            {
                trigger_error("Error: missing resource id for extern tool ",E_USER_ERROR);
            }
        }

        /**
        *  give the URL of a extern tool
        *
        * @param $course_sys_code id of a course
        * @param $toolName string the name of the tool (isn't the tlabel)
        * @return string the url of a extern tool
        */
        function _getNameByUrl( $course_sys_code , $url )
        {
            $courseInfoArray = get_info_course($course_sys_code);
            $tbl_cdb_names = claro_sql_get_course_tbl($courseInfoArray["dbNameGlu"]);
            $tbl_extern = $tbl_cdb_names['tool'];


            $sql = "SELECT `script_name` FROM `".$tbl_extern."` WHERE `script_url` = '".claro_sql_escape($url)."'";
            $name = claro_sql_query_get_single_value($sql);

            return $name;
        }
    }
?>