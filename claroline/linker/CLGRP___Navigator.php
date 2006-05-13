<?php // $Id$
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
    * Class groups Navigator 
    *
    * @package CLGRP
    * @subpackage CLLINKER 
    *
    * @author Fallier Renaud <renaud.claroline@gmail.com>
    */
    class CLGRP___Navigator extends Navigator 
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
        function CLGRP___Navigator($basePath = null)
        {
            global $_course;
            $this->_claroContainer = FALSE; 
        }

        /**
        * list the contents of a announcement
        *
        * @param  $node string of current node (crl)
        * @return ClaroContainer who contains the objects current node
        * @throws  E_USER_ERROR if the node is not intended for the tool forum
        * @throws  E_USER_ERROR if the node is empty
        */
        function getResource($node = null)
        {
            
            if($node)
            {
                if(CRLTool::isForThisTool($node, 'CLGRP___'))
                {
                     $elementCRLArray = CRLTool::parseCRL($node);

                     if( !isset ($elementCRLArray['resource_id']) )               
                     {
                         // listing of annonce
                         $groups = $this->_listGroup($elementCRLArray['course_sys_code']);
                         $elementList = array();
                         
                         foreach ($groups as $itemGroups )
                         {
                             $crl = CRLTool::createCRL($elementCRLArray['platform_id'],$elementCRLArray['course_sys_code'],"","",$itemGroups["id"]);
                             $title = $itemGroups['name']; 
                             $container = new ClaroContainer( $title , $crl );
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
                    trigger_error ("Error : not crl for tool groups", E_USER_ERROR);
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
        * list the announcement of a course
        *
        * @return $array a array which contains the id and the title of a announcement
        */
         function _listGroup($course_sys_code)
        {
            $courseInfoArray = get_info_course($course_sys_code); 
            $tbl_cdb_names = claro_sql_get_course_tbl($courseInfoArray["dbNameGlu"]);
            $tbl_groups = $tbl_cdb_names['group_team'];
            
            $sql = 'SELECT `id`,`name` FROM `'.$tbl_groups.'`';
            $groups = claro_sql_query_fetch_all($sql);
            
            return $groups;
        }
    }
?>
