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
    * Class quizz Navigator
    *
    * @package CLQWZ
    * @subpackage CLLINKER
    *
    * @author Fallier Renaud <renaud.claroline@gmail.com>
    */
    class CLQWZ___Navigator extends Navigator
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
        * @global $_course
        */
        function CLQWZ___Navigator($basePath = NULL)
        {
            global $_course;
            $this->_claroContainer = FALSE;
        }

        /**
        * list the contents of a exercice
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
                //if(CRLTool::isForThisTool($node, 'exercice"))
                if(CRLTool::isForThisTool($node, 'CLQWZ___'))
                {
                     $elementCRLArray = CRLTool::parseCRL($node);

                     if( !isset ($elementCRLArray['resource_id']) )
                     {
                         // listing of annonce
                         $exercices = $this->_listExo($elementCRLArray['course_sys_code']);
                         $elementList = array();

                         foreach ($exercices as $itemExercice )
                         {
                             $crl = $node."/".$itemExercice["id"];
                             if( $itemExercice["visibility"] == 'VISIBLE' )
                             {
                                 $isVisible = TRUE;
                             }
                             else
                             {
                                 $isVisible = FALSE;
                             }
                             $container = new ClaroObject( $itemExercice["title"] , $crl , TRUE , FALSE , $isVisible);
                             $elementList[] = $container ;
                         }

                         $this->_claroContainer = new ClaroContainer ( '' , $node , $elementList );

                         return $this->_claroContainer;

                     }
                     else
                     {
                         trigger_error ('Error : resource_id must be empty', E_USER_ERROR);
                     }
                }
                else
                {
                    trigger_error ('Error : not crl for a exercice tool', E_USER_ERROR);
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
        * list the exercice of a course
        *
        * @param $course_sys_code the id of the course
        * @return $array a array which contains the id and the name of a category
        */
        function _listExo($course_sys_code)
        {
            $courseInfoArray = get_info_course($course_sys_code);
            $tbl_cdb_names = claro_sql_get_course_tbl($courseInfoArray["dbNameGlu"]);
            $tbl_quiz_exercise = $tbl_cdb_names['qwz_exercise'];

            $sql = 'SELECT `id`,`title`,`visibility` FROM `'.$tbl_quiz_exercise.'`';
            $exercice = claro_sql_query_fetch_all($sql);

            return $exercice;
        }
    }
?>