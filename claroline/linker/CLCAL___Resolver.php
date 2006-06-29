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
 * @author Renaud Fallier <captren@gmail.com>
 * @author Frédéric Minne <minne@ipm.ucl.ac.be>
 *
 * @package CLLINKER
 *
 */
    require_once dirname(__FILE__) . '/resolver.lib.php';
    require_once dirname(__FILE__) . '/../inc/lib/claro_utils.lib.php';

    /**
    * Class Agenda/calendar CRL Resolver 
    *
    * @package CLCAL 
    * @subpackage CLLINKER 
    *
    * @author Fallier Renaud
    */
    class CLCAL___Resolver extends Resolver 
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
        */
        function CLCAL___Resolver($basePath)
        {
            $basePath = preg_replace( '~/$~', "", $basePath );
            $this->_basePath = $basePath; 
        }

        /**
        * translated a crl into valid URL for the forum tool
        *
        * @param  $CRL string a crl
        * @return string a url valide who corresponds to the crl
        * @throws E_USER_ERROR if tool_name is empty
        * @throws E_USER_ERROR if it isn't for tool calendar
        * @throws E_USER_ERROR if the crl is empty     
        */
        function resolve($crl)
        {
           if($crl)
           {
               if(CRLTool::isForThisTool($crl,'CLCAL___'))
               {    
                   $elementCRLArray = CRLTool::parseCRL($crl);
                   $url = $this->_basePath . "/claroline/calendar/";
                   $url .= "agenda.php?cidReq={$elementCRLArray['course_sys_code']}";
                   
                   if( isset($elementCRLArray["tool_name"]) && isset($elementCRLArray['resource_id']) )
                   {
                       $url .= "#event{$elementCRLArray['resource_id']}";   
                        
                       return $url;    
                   }
                   else
                   {
                       trigger_error('ERROR: tool_name required',E_USER_ERROR);
                   }
               }
               else
               {
                   trigger_error("ERROR: isn't for calendar tool",E_USER_ERROR);
               }
           }
           else
           {
               trigger_error("ERROR: crl is required",E_USER_ERROR);
           }     
        }

        /**
        * get the resource identifier of an event 
        *
        * @global $insert_id  integer of an identifier of event. This east creates after the insertion of the dB 
        * @global $thisAnnouncement integer of an identifier of event when the announcement are posted 
        * @param  $tool_name the Tlabel of a tool 
        * @return string who contains the resouce id
        * @throws  E_USER_ERROR if tool_name is empty
        */
        function getResourceId($tool_name)
        {
            // global $insert_id;
            global $entryId;
            global $thisEvent;
              
            if( isset( $tool_name ) )
            { 
               if( isset( $thisEvent['id'] ) )
               {
                       $resource_id = $thisEvent['id'];
               }
                   
               else if( $entryId != FALSE ) 
               {
                       $resource_id = $entryId;
               }
               
               else if( isset($_REQUEST['id']) )
               {
                       $resource_id = $_REQUEST['id'];            
               } 
               else
               {    
                       return FALSE;
               }
             
               return $resource_id;    
            } 
            else
            {
                trigger_error("Error: missing tool name ",E_USER_ERROR);
            }
        }

        
        /**
        * the name of the resource which will be posted
        *
        * @param $crl a string who cotains the crl
        * @return string who contains the name of the resource
        */
        function getResourceName($crl)
        {
            if(CRLTool::isForThisTool($crl,'CLCAL___'))
            {    
                $elementCRLArray = CRLTool::parseCRL($crl);
                $title = "";
                
                if( isset($elementCRLArray['resource_id']) )
                {
                    $title  = get_toolname_title( $elementCRLArray );
                    $title .= " > ".$this->getTitle($elementCRLArray['course_sys_code'],$elementCRLArray['resource_id']);    
                }

                return $title;
            }
            else
            {
                trigger_error("Error: missing resource id for calendar",E_USER_ERROR);    
            }                      
        }

        /**
        * $FIXME use same field name for title in DB tables
        *
        * @param  $course_sys_code identifies a course in data base
        * @param  $id integer who identifies the event
        * @return the title of a annoncement
        */
        function _getInfo( $course_sys_code , $id )
        {
            $courseInfoArray = get_info_course($course_sys_code); 
            $tbl_cdb_names = claro_sql_get_course_tbl($courseInfoArray["dbNameGlu"]);
            $tbl_agenda = $tbl_cdb_names['calendar_event'];

            $sql = 'SELECT `titre`,`day`,`contenu` FROM `'.$tbl_agenda.'` WHERE `id`='. (int)$id;
            $agendaInfo = claro_sql_query_fetch_all($sql);
            
            return $agendaInfo;
        }   

        /**
        *
        * @param  $course_sys_code identifies a course in data base
        * @param  $id integer who identifies the event
        * @return the title of a annoncement
        */ 
        function getTitle( $course_sys_code , $id )
        {           
            $agendaInfo = $this->_getInfo( $course_sys_code , $id );

            $content = trim( stripslashes(strip_tags($agendaInfo[0]["contenu"])));     
                
            if( strlen($agendaInfo[0]["titre"]) > 0)
            {
                $titreEvent = stripslashes($agendaInfo[0]["titre"]);
                $title = cutstring( $titreEvent, 15 , FALSE ) ." {". $agendaInfo[0]["day"]."}";  
            }
            else if( !empty($content) )
            {    
                $titreEvent = $content;
                $title = cutstring( $titreEvent, 15 , FALSE , 3) ." {". $agendaInfo[0]["day"]."}";      
            }
            else 
            {
                  /*------------------------------
                   *   todo : no name of event   -
                   *-----------------------------*/
                   $title = get_lang('Untitled')." {" . $agendaInfo[0]["day"]."}";      
               }
               
               return $title; 
        }
    }
?>
