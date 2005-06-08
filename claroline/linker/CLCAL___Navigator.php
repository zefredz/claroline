<?php // $Id$
//----------------------------------------------------------------------
// CLAROLINE
//----------------------------------------------------------------------
// Copyright (c) 2001-2005 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see 'credits' file
//----------------------------------------------------------------------

    require_once dirname(__FILE__) . '/navigator.lib.php';
    require_once ("CLCAL___Resolver.php");

    /**
    * Class AgendaNavigator 
    *
    *
    * @author Fallier Renaud
    */
    class CLCAL___Navigator extends Navigator 
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
        */
        function CLCAL___Navigator($basePath = null)
        {
            global $_course;
            $this->_claroContainer = FALSE; 
        }

        /**
        * list the contents of a agenda 
        *
        * @param  $node string of current node (crl)
        * @return ClaroContainer who contains the objects current node
        * @throw  E_USER_ERROR if the node is not intended for the tool forum
        * @throw  E_USER_ERROR if the node is empty
        * @global rootWeb
        */
        function getResource($node = null)
        {
            global $rootWeb;
            
            if($node)
            {
                if(CRLTool::isForThisTool($node, 'CLCAL___'))
                {
                     $elementCRLArray = CRLTool::parseCRL($node);

                     if( !isset ($elementCRLArray['resource_id']) )               
                     {
                         // listing of agenda
                         $agenda = $this->_listAgenda($elementCRLArray['course_sys_code']);
                         $elementList = array();
                         
                         foreach ($agenda as $itemAgenda )
                         {
                             $crl = $node."/".$itemAgenda["id"];
                             $res = new CLCAL___Resolver($rootWeb); 
                             $title = $res->getTitle($elementCRLArray['course_sys_code'],$itemAgenda["id"]);
                             $isVisible = ( $itemAgenda["visibility"] == 'SHOW');
                             $container = new ClaroObject( $title , $crl , TRUE , FALSE , $isVisible );
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
                    trigger_error ("Error : not crl for a calendar tool", E_USER_ERROR);
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
        * list the event of a course 
        *
        * @return $array a array which contains the id, the titre and the day of a event of a calandar
        */
        function _listAgenda($course_sys_code)
        {
            $courseInfoArray = get_info_course($course_sys_code); 
            $tbl_cdb_names = claro_sql_get_course_tbl($courseInfoArray["dbNameGlu"]);
            
            $tbl_agenda = $tbl_cdb_names['calendar_event'];

            $sql = 'SELECT `id`,`titre`,`day`, `visibility` FROM `'.$tbl_agenda.'` ORDER BY `day`  DESC';
            $agenda = claro_sql_query_fetch_all($sql);
            
            return $agenda;
        }
    }
?>