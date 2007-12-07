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
    * Class group crl Resolver 
    *
    * @package CLGRP
    * @subpackage CLLINKER 
    *
    * @author Fallier Renaud <renaud.claroline@gmail.com>
    */
    class CLGRP___Resolver extends Resolver 
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
        function CLGRP___Resolver($basePath)
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
        * @throws E_USER_ERROR if it isn't for tool announcement
        * @throws E_USER_ERROR if the crl is empty     
        */
        function resolve($crl)
        {
           if($crl)
           {
               $elementCRLArray = CRLTool::parseCRL($crl);
               $url = $this->_basePath . "/claroline/group/";
               $url .= "group_space.php?cidReq={$elementCRLArray['course_sys_code']}";  
               $url .= "&gidReq=".$elementCRLArray["team"];    
                       
              return $url;
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
            global $_courseToolList,$langGroups;
             
            $elementCRLArray = CRLTool::parseCRL($crl);
            $title = "";

            $title  = get_toolname_title( $elementCRLArray );
            $title .= " > $langGroups > ".$this->getTitle($elementCRLArray['course_sys_code'],$elementCRLArray["team"]);    
                
            return $title;
               
        }      

        /**
        * FIXME use same field name for title in DB tables
        *
        * @param  $course_sys_code identifies a course in data base    
        * @param  $id integer who identifies the announcement
        * @return the title of a annoncement
        */
        function _getInfo($course_sys_code , $id)
        {
            $courseInfoArray = get_info_course($course_sys_code); 
            $tbl_cdb_names = claro_sql_get_course_tbl($courseInfoArray["dbNameGlu"]);
            $tbl_groups = $tbl_cdb_names['group_team'];
            
            $sql = 'SELECT `name`,`description` 
                    FROM `'.$tbl_groups.'`
                    WHERE `id`='. (int)$id;
            $annonceInfo = claro_sql_query_fetch_all($sql);

            return $annonceInfo;
        }

        /**
        *
        * @param  $course_sys_code identifies a course in data base
        * @param  $id integer who identifies the event
        * @return the title of a annoncement
        */ 
        function getTitle( $course_sys_code , $id )
        {        
            $announcementInfo = $this->_getInfo( $course_sys_code , $id );
                    
            if( strlen($announcementInfo[0]["name"]) > 0)
            {
                $titreEvent = stripslashes($announcementInfo[0]["name"]);
                $title = cutstring( $titreEvent, 15 , FALSE , 3 ) ;  
            }
            else if( !empty($announcementInfo[0]["description"])  )
            {    
                $titreEvent = stripslashes($announcementInfo[0]["description"]);
                $title = cutstring( $titreEvent, 15 , FALSE , 3) ;      
            }
            else 
            {
                  /*------------------------------
                   *   todo : no name of annonce -
                   *-----------------------------*/
                   $title = "no name";      
               }
               
               return $title; 
        }
    }
?>
