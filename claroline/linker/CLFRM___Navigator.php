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
    * Class forum Navigator
    *
    * @package CLFRM
    * @subpackage CLLINKER
    *
    * @author Fallier Renaud <renaud.claroline@gmail.com>
    */
    class CLFRM___Navigator extends Navigator
    {
        /*-------------------------
                 variable
         ------------------------*/
        var $_claroContainer;
        var $_tbl_cdb_names;

        /*----------------------------
                public method
        ---------------------------*/

        /**
        * Constructor
        *
        * @param   $basePath string path root directory of courses
        */
        function CLFRM___Navigator($basePath = NULL)
        {
            $this->_claroContainer = FALSE;
            $this->_tbl_cdb_names = claro_sql_get_course_tbl();
        }

        /**
        * list the contents of a category, a forum or a topic
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
                if(CRLTool::isForThisTool($node, 'CLFRM___'))
                {
                    $elementCRLArray = CRLTool::parseCRL($node);

                    $courseInfoArray = get_info_course($elementCRLArray['course_sys_code']);
                    $this->_tbl_cdb_names = claro_sql_get_course_tbl($courseInfoArray["dbNameGlu"]);

                    // listing of topic for a groups
                    if( isset ($elementCRLArray["team"]) )
                    {
                        $tbl_forums = $this->_tbl_cdb_names['bb_forums'];
                        $tbl_topics = $this->_tbl_cdb_names['bb_topics'];

                        $sql = 'SELECT `forum_id`
                                FROM `'.$tbl_forums.'`
                                WHERE `group_id` ='. (int)$elementCRLArray["team"];
                        $forumId = claro_sql_query_get_single_value($sql);

                        $sql = 'SELECT `topic_id`,`topic_title`
                                FROM `'.$tbl_topics.'`
                                WHERE `forum_id` ='. (int)$forumId;
                        $groupTopicList = claro_sql_query_fetch_all($sql);
                        $elementList = array();
                        $name = "";

                        foreach ($groupTopicList as $groupTopic )
                        {
                            $crl = CRLTool::createCRL( get_conf('platform_id') , $elementCRLArray['course_sys_code'] , $elementCRLArray["tool_name"] , $groupTopic["topic_id"] , $elementCRLArray["team"] );
                            $container = new ClaroObject( $groupTopic["topic_title"] , $crl  );
                            $elementList[] = $container ;
                        }

                    }
                    else if(!isset ($elementCRLArray['resource_id']) )
                    {
                         // listing of a categories, it is a container no linkable
                         $categories = $this->_listCat();
                         $name = get_lang("Categories");
                         $elementList = array();

                         foreach ($categories as $itemCategorie )
                         {
                             $crl = $this->_createObjectCRL( $elementCRLArray , $itemCategorie["cat_id"] );
                             $container = new ClaroContainer( $itemCategorie["cat_title"] , $crl , FALSE , FALSE );
                             $elementList[] = $container ;
                         }
                     }
                     else
                     {
                        $resultat = $this->_toolForumTopic($elementCRLArray['resource_id']);

                         if( $resultat["type"] == "forum")
                         {
                             $forums = $this->_listForum($resultat["id"]);
                             $name = get_lang("Forums");
                             $elementList = array();

                             foreach ($forums as $itemForum )
                             {
                                 $crl = $this->_createObjectCRL( $elementCRLArray , $itemForum["forum_id"] );
                                 $container = new ClaroContainer( $itemForum["forum_name"] , $crl  );
                                 $elementList[] = $container ;
                             }
                         }
                         else
                         {
                             $topics = $this->_listTopic($resultat["id"]);
                             $name = get_lang("Topics");
                             $elementList = array();

                             foreach ($topics as $itemTopic )
                             {
                                 $crl = $this->_createObjectCRL( $elementCRLArray , $itemTopic["topic_id"] );
                                 $container = new ClaroObject( $itemTopic["topic_title"] , $crl  );
                                 $elementList[] = $container ;
                             }
                         }
                     }
                     $this->_claroContainer = new ClaroContainer ( $name , $node , $elementList );

                     return $this->_claroContainer;
                }
                else
                {
                    trigger_error ("Error : not crl for a forum tool", E_USER_ERROR);
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
        * list the categories which do not belong to a group
        *
        * @return $array a array which contains the id and the name of a category
        */
        function _listCat()
        {
            $tbl_categories = $this->_tbl_cdb_names['bb_categories'];
            $sql = 'SELECT `cat_id` ,`cat_title`
                    FROM `'.$tbl_categories.'`
                    WHERE `cat_id` !=1';

            $categories = claro_sql_query_fetch_all($sql);

            return $categories;
        }

        /**
        *  list forum according to the category
        *
        * @param $idCat integer the id of the category
        * @return  array a array which the id and the name of a forum
        */
        function _listForum($idCat)
        {
            $tbl_forums = $this->_tbl_cdb_names['bb_forums'];
            $sql = 'SELECT `forum_id` , `forum_name`
                    FROM `'.$tbl_forums.'`
                    WHERE `cat_id` = '. (int)$idCat.'';
            $forum = claro_sql_query_fetch_all($sql);

            return $forum;
        }

        /**
        *  list topic according to the forum
        *
        * @param $idCat integer the id of the forum
        * @return  array a array which the id and the name of a topic
        */
        function _listTopic($idForum)
        {
            $tbl_topics = $this->_tbl_cdb_names['bb_topics'];
            $sql = 'SELECT `topic_id` , `topic_title`
                    FROM `'.$tbl_topics.'`
                    WHERE `forum_id` = '. (int)$idForum.'';
            $topic = claro_sql_query_fetch_all($sql);

            return $topic;
        }

        /**
        *  identify the resource, if it is a forum or a topic
        *
        * @param $resource_id string
        * @return  array a array  which the type and the id of a resource id
        */
        function _toolForumTopic($resource_id)
        {
            $resultat = array();
            $resourceElements = explode("/",$resource_id);

            if( count($resourceElements) == 1)
            {
                $resultat["type"] = "forum";
                $resultat["id"] = $resourceElements[0];
            }
            else
            {
                $resultat["type"] = "topic";
                $resultat["id"] = $resourceElements[1];
            }

            return $resultat;
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
             if( isset ($elementCRLArray['resource_id']) )
             {
                 $resource_id = $elementCRLArray['resource_id']."/".$partResourceId;
             }
             else
             {
                 $resource_id = $partResourceId;
             }

             $crl = CRLTool::createCRL( get_conf('platform_id') , $elementCRLArray['course_sys_code'] , $elementCRLArray["tool_name"] ,$resource_id );

             return $crl;
        }
    }
?>