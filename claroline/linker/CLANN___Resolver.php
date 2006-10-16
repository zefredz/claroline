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
    require_once dirname(__FILE__) . '/../inc/lib/claro_utils.lib.php';

    /**
    * Class Announcement CRL Resolver
    *
    * @package CLANN
    * @subpackage CLLINKER
    *
    * @author Fallier Renaud <renaud.claroline@gmail.com>
    */
    class CLANN___Resolver extends Resolver
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
        function CLANN___Resolver($basePath)
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
               if(CRLTool::isForThisTool($crl,'CLANN___'))
               {
                   $elementCRLArray = CRLTool::parseCRL($crl);
                   $url = $this->_basePath . "/claroline/announcements/";
                   $url .= "announcements.php?cidReq={$elementCRLArray['course_sys_code']}";

                   if( isset($elementCRLArray["tool_name"]) && isset($elementCRLArray['resource_id']) )
                   {
                       $url .= "#ann{$elementCRLArray['resource_id']}";

                       return $url;
                   }
                   else
                   {
                       trigger_error('ERROR: tool_name required',E_USER_ERROR);
                   }
               }
               else
               {
                   trigger_error("ERROR: CRL isn't for tool announcement",E_USER_ERROR);
               }
           }
           else
           {
               trigger_error("ERROR: crl is required",E_USER_ERROR);
           }
        }

        /**
        * get the resource identifier of an annoucement
        *
        * @global $insert_id  integer of an identifier of annoucement. This east creates after the insertion of the dB
        * @global $thisAnnouncement integer of an identifier of annoucement when the announcement are posted
        * @param  Tlabel $tool_name the Tlabel of a tool
        * @return string who contains the resouce id
        * @throws  E_USER_ERROR if tool_name is empty
        */
        function getResourceId($tool_name)
        {
            global $insert_id;
            global $thisAnnouncement;

            if( isset( $tool_name ) )
            {
               if( isset( $thisAnnouncement['id'] ) )
               {
                       $resource_id = $thisAnnouncement['id'];
               }
               else if( isset($_REQUEST['id']) )
               {
                       $resource_id = $_REQUEST['id'];
               }
               else if( $insert_id != FALSE )
               {
                       $resource_id = $insert_id;
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
        * @throws  E_USER_ERROR if it isn't for tool announcement
        **/
        function getResourceName($crl)
        {
            if(CRLTool::isForThisTool($crl,'CLANN___'))
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
                trigger_error("Error: isn't for tool announcement",E_USER_ERROR);
            }
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
            $tbl_annonce = $tbl_cdb_names['announcement'];

            $sql = 'SELECT `title`,`contenu` FROM `'.$tbl_annonce.'` WHERE `id`='. (int)$id;
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
            $content = trim( stripslashes(strip_tags($announcementInfo[0]["contenu"])));

            if( strlen($announcementInfo[0]["title"]) > 0)
            {
                $titreEvent = stripslashes($announcementInfo[0]["title"]);
                $title = cutstring( $titreEvent, 15 , FALSE , 3 ) ;
            }
            else if( !empty($content)  )
            {
                $titreEvent = $content;
                $title = cutstring( $titreEvent, 15 , FALSE , 3) ;
            }
            else
            {
                  /*------------------------------
                   *   todo : no name of annonce -
                   *-----------------------------*/

                   $title = get_lang("Untitled");
               }

               return $title;
        }
    }
?>