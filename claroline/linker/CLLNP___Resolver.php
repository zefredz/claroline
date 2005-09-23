<?php // $Id$
/**
 * CLAROLINE 
 *
 * @version 1.7 $Revision$ 
 * @copyright (c) 2001-2005 Universite catholique de Louvain (UCL)
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
    * Class Learning Path CRL Resolver 
    *
    * @package CLLNP
    * @subpackage CLLINKER 
    *
    * @author Fallier Renaud <renaud.claroline@gmail.com>
    */
    class CLLNP___Resolver extends Resolver  
    {
        /*-------------------------
                 variable
         ------------------------*/
        var $_basePath;

        /*----------------------------
                public method
        ---------------------------*/

       /**
        * Constructor
        *
        * @param  $basePath string path root directory of courses 
        **/
        function CLLNP___Resolver($basePath)
        {
            $basePath = preg_replace( '~/$~', "", $basePath );
            $this->_basePath = $basePath; 
        }

       /**
        * translated a crl into valid URL for the announcement tool
        *
        * @param  $CRL string a crl
        * @return string a url valide who corresponds to the crl
        * @throws E_USER_ERROR if tool_name is empty
        * @throws E_USER_ERROR if it isn't for tool lp
        * @throws E_USER_ERROR if the crl is empty     
        **/
        function resolve($crl)
        {
           if($crl)
           {
                if(CRLTool::isForThisTool($crl,'CLLNP___'))
               {    
                   $elementCRLArray = CRLTool::parseCRL($crl);
                   $url = $this->_basePath . "/claroline/learnPath/";
                   
                   if( isset($elementCRLArray["tool_name"]) && isset($elementCRLArray['resource_id']) )
                   {
                       $url .= "learningPath.php?path_id={$elementCRLArray['resource_id']}&cidReq={$elementCRLArray['course_sys_code']}";   
                        
                       return $url;
                   }
                   else 
                   {
                       trigger_error('ERROR: tool_name required',E_USER_ERROR);
                   }
               }
               else
               {
                   trigger_error("ERROR: isn't for tool learnPath",E_USER_ERROR);
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
        * @global $_courseToolList
        * @throws  E_USER_ERROR if it isn't for tool announcement
        **/
        function getResourceName($crl)
        {
            global $_courseToolList;
            
            if(CRLTool::isForThisTool($crl,'CLLNP___'))
            {    
                $elementCRLArray = CRLTool::parseCRL($crl);
                if( isset($elementCRLArray['resource_id']) )
                {
                    $title  = get_toolname_title( $elementCRLArray );
                    $title .= " > ". stripslashes($this->_getTitle($elementCRLArray['course_sys_code'],$elementCRLArray['resource_id']));    
                }
            
                return $title;
            }
            else
            {
                trigger_error("Error: missing resource id for learnPath ",E_USER_ERROR);    
            }                      
        }      

       /**
        *  FIXME use same field name for title in DB tables
        * @param  $course_sys_code identifies a course in data base    
        * @param  $id integer who identifies the learnPath
        * @return the title of a learnPath
        **/
        function _getTitle($course_sys_code , $id)
        {
            $courseInfoArray = get_info_course($course_sys_code); 
            $tbl_cdb_names = claro_sql_get_course_tbl($courseInfoArray["dbNameGlu"]);
            $tbl_learnPath = $tbl_cdb_names['lp_learnPath'];
            
            $sql = 'SELECT `name` 
                    FROM `'.$tbl_learnPath.'` 
                    WHERE `learnPath_id` ='. (int)$id; 
            $lpTitle = claro_sql_query_get_single_value($sql);
            
            return $lpTitle;
        }
    }
?>
