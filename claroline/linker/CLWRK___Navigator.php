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

    require_once dirname(__FILE__) . '/navigator.lib.php';

    
    /**
    * Class Assignement Navigator 
    *
    * @package CLWRK
    * @subpackage CLLINKER 
    *
    * @author Fallier Renaud <renaud.claroline@gmail.com>
    */
    class CLWRK___Navigator extends Navigator  
    {
        /*-------------------------
                 variable
         ------------------------*/
        var $_claroContainer;  

        /*----------------------------
                public method
        ---------------------------*/

        /**
        * Constructor
        *
        * @param   $basePath string path root directory of courses 
        * @global  $_course 
        */
        function CLWRK___Navigator($basePath = NULL)
        {
            global $_course;
            $this->_claroContainer = FALSE; 
        }

        /**
        * list the contents of a work 
        *
        * @param  $node string of current node (crl)
        * @return ClaroContainer who contains the objects current node
        * @throws  E_USER_ERROR if the node is not intended for the tool forum
        * @throws  E_USER_ERROR if the node is empty
        */
        function getResource($node = NULL)
        {
            if($node)
            {
                if(CRLTool::isForThisTool($node, 'CLWRK___'))
                {
                     $elementCRLArray = CRLTool::parseCRL($node);

                     if( !isset ($elementCRLArray['resource_id']) )               
                     {
                         // listing of work
                         $work = $this->_listWork($elementCRLArray['course_sys_code']);
                         $elementList = array();
                         
                         foreach ($work as $itemWork )
                         {
                             $crl = $node."/".$itemWork["id"];  
                             
                             if( $itemWork["visibility"] == 'VISIBLE' )
                             {
                                 $isVisible = TRUE;
                             }
                             else
                             {
                                 $isVisible = FALSE;
                             }
                             
                             $container = new ClaroObject( $itemWork["title"] , $crl , TRUE , FALSE , $isVisible);
                             $elementList[] = $container ;   
                         }    
                          
                         $this->_claroContainer = new ClaroContainer ( '' , $node , $elementList );   
                         
                         return $this->_claroContainer;
                     
                     }
                     else
                     {
                         trigger_error ("Error : resource_id must be empty", E_USER_ERROR);   
                     }                  
                }
                else
                {
                    trigger_error ("Error : not crl for a work tool", E_USER_ERROR);
                }               
            }
            // if the node is NULL
            else
            {
                trigger_error ('Error : crl is empty', E_USER_ERROR);
            }   
        }

        /*----------------------------
                private method
        ---------------------------*/

        /**
        * list the work of a course
        *
        * @return $array a array which contains the id and the title of a work
        */
        function _listWork($course_sys_code)
        {
            $courseInfoArray = get_info_course($course_sys_code); 
            $tbl_cdb_names = claro_sql_get_course_tbl($courseInfoArray["dbNameGlu"]);
            $tbl_work = $tbl_cdb_names['wrk_assignment'];

            $sql =  $sql = 'SELECT `id`,`title`,`visibility` FROM `'.$tbl_work.'`'; 
            $work = claro_sql_query_fetch_all($sql);
            
            return $work;
        }
    }
?>
