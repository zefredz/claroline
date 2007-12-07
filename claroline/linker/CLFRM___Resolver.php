<?php // $Id$
if ( ! defined('CLARO_INCLUDE_ALLOWED') ) die('---');
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
    * Class Forum CRL Resolver
    *
    * @package CLFRM
    * @subpackage CLLINKER
    *
    * @author Fallier Renaud <renaud.claroline@gmail.com>
    */
    class CLFRM___Resolver extends Resolver
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
        function CLFRM___Resolver($basePath)
        {
            $basePath = preg_replace( '~/$~', "", $basePath );
            $this->_basePath = $basePath;
        }

        /**
        * translated a crl into valid URL for the forum tool
        *
        * @param  $CRL string a crl
        * @return string a url valide who corresponds to the crl
        * @throws E_USER_ERROR if crl is not valid for forum tool
        * @throws E_USER_ERROR if tool_name is empty
        * @throws E_USER_ERROR if it isn't for tool forum
        * @throws E_USER_ERROR if the crl is empty
        */
        function resolve($crl)
        {
           if($crl)
           {
                if(CRLTool::isForThisTool($crl,'CLFRM___'))
               {
                   $elementCRLArray = CRLTool::parseCRL($crl);
                   $url = $this->_basePath . "/claroline/phpbb/";

                   if( isset($elementCRLArray['resource_id']))
                   {
                       $resourceElement = explode("/",$elementCRLArray['resource_id']);
                       //forum of group
                       if( isset($elementCRLArray["team"])  )
                       {
                              $url .= "viewtopic.php?topic=".$elementCRLArray['resource_id']."&cidReq=".$elementCRLArray['course_sys_code']."&gidReq=".$elementCRLArray["team"];
                       }
                       //forum
                       else if( count($resourceElement) == 2 )
                       {
                           $url .= "viewforum.php?forum=".$resourceElement[1]."&cidReq=".$elementCRLArray['course_sys_code'];
                       }
                       //topic
                       else if( count($resourceElement) == 3 )
                       {
                           $url .= "viewtopic.php?topic=".$resourceElement[2]."&cidReq=".$elementCRLArray['course_sys_code'];
                       }
                       //categorie or error
                       else
                       {
                           trigger_error("ERROR: crl not valid for this tool ",E_USER_ERROR);
                       }
                       return $url;
                   }
                   else if( isset($elementCRLArray["tool_name"]) )
                   {
                       $url .= "index.php?cidReq={$elementCRLArray['course_sys_code']}";
                       return $url;
                   }
                   else
                   {
                       trigger_error('ERROR: tool_name required',E_USER_ERROR);
                   }
               }
               else
               {
                   trigger_error("ERROR: isn't for tool forum",E_USER_ERROR);
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
        * @param  $crl a string who cotains the crl
        * @return string who contains the name of the resource
        * @throws  E_USER_ERROR if it isn't for tool announcement
        **/
        function getResourceName($crl)
        {
            $title = "";

            if(CRLTool::isForThisTool($crl,'CLFRM___'))
            {
                $elementCRLArray = CRLTool::parseCRL($crl);
                if( isset($elementCRLArray['resource_id']) )
                {
                    $title = get_toolname_title( $elementCRLArray );

                    $elementResource = explode("/",$elementCRLArray['resource_id']);

                    if( isset($elementCRLArray["team"]) )
                    {
                       $title .= " > ".stripslashes($this->_getTopicTitle($elementCRLArray['course_sys_code'],$elementCRLArray['resource_id']));
                    }

                    if( count($elementResource) == 0 || count($elementResource) == 1 )
                    {
                         return $title;
                    }
                    else if( count($elementResource) == 2 )
                    {
                        $title .= " > ".stripslashes($this->_getForumTitle($elementCRLArray['course_sys_code'],$elementResource[1]));
                    }
                    else if( count($elementResource) == 3)
                    {
                        $title .= " > ".stripslashes($this->_getForumTitle($elementCRLArray['course_sys_code'],$elementResource[1]));
                        $title .= " > ".stripslashes($this->_getTopicTitle($elementCRLArray['course_sys_code'],$elementResource[2]));
                    }
                    else
                    {
                         trigger_error("Error: invalid resource id",E_USER_ERROR);
                    }
                }

                return $title;
            }
            else
            {
                trigger_error("Error: missing resource id for forum",E_USER_ERROR);
            }
        }


       /**
        *  get the title of a forum
        *
        * @param $course_sys_code the id of a cours
        * @param $idForum integer the id of a forum
        * @return string the title of the forum
        */
        function _getForumTitle($course_sys_code,$idForum)
        {
            $courseInfoArray = get_info_course($course_sys_code);
            $tbl_cdb_names = claro_sql_get_course_tbl($courseInfoArray["dbNameGlu"]);
            $tbl_forums = $tbl_cdb_names['bb_forums'];

            $sql = 'SELECT `forum_name`
                    FROM `'.$tbl_forums.'`
                    WHERE `forum_id` = '. (int)$idForum.'';
            $forum = claro_sql_query_get_single_value($sql);

            return $forum;
        }

        /**
        * get the title of a topic
        *
        * @param $course_sys_code the id of a cours
        * @param $idTopic integer the id of a topic
        * @return string the title of the topic
        */
        function _getTopicTitle($course_sys_code,$idTopic)
        {
            $courseInfoArray = get_info_course($course_sys_code);
            $tbl_cdb_names = claro_sql_get_course_tbl($courseInfoArray["dbNameGlu"]);
            $tbl_topics = $tbl_cdb_names['bb_topics'];

            $sql = 'SELECT `topic_title`
                    FROM `'.$tbl_topics.'`
                    WHERE `topic_id` = '. (int)$idTopic.'';
            $topic = claro_sql_query_get_single_value($sql);

            return $topic;
        }
    }
?>
