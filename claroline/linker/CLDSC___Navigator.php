<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );
/**
 * CLAROLINE
 *
 * @version 1.8 $Revision$
 *
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @package CLDSC
 * @subpackage CLLNK
 *
 */

    require_once dirname(__FILE__) . '/navigator.lib.php';
    require_once dirname(__FILE__) . '/../inc/lib/claro_utils.lib.php';

    /**
    * Class Course Description Navigator
    *
    * @package CLDSC
    * @subpackage CLLINKER
    *
    * @author Fallier Renaud
    */
    class CLDSC___Navigator extends Navigator
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
        function CLDSC___Navigator($basePath = NULL)
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
                if(CRLTool::isForThisTool($node, 'CLDSC___'))
                {
                     $elementCRLArray = CRLTool::parseCRL($node);

                     if( !isset ($elementCRLArray['resource_id']) )
                     {
                         $description = $this->_listDescritpion($elementCRLArray['course_sys_code']);
                         $elementList = array();

                         foreach ($description as $itemDescription )
                         {
                             $crl = $node."/".$itemDescription["id"];
                             $isVisible = ( $itemDescription["visibility"] == 'SHOW');

                              if( strlen($itemDescription["title"]) > 0)
                              {
                                  $title = stripslashes($itemDescription["title"]);
                              }
                              else if( !empty($itemDescription["content"])  )
                              {
                                   $title = cutstring( $itemDescription["content"], 15 , FALSE , 3) ;
                              }
                              else
                              {
                                  /*--------------------------------------------------
                                   *   todo : no name of Title of course description -
                                   *--------------------------------------------------*/
                                     $title = "no name";
                                 }

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
                    trigger_error ("Error : not crl for the course description", E_USER_ERROR);
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
        * list the course description
        *
        * @param $course_sys_code the id of the course
        * @return $array a array which contains the id and the name of a category
        */
        function _listDescritpion($course_sys_code)
        {
            $courseInfoArray = get_info_course($course_sys_code);
            $tbl_cdb_names = claro_sql_get_course_tbl($courseInfoArray["dbNameGlu"]);
            $tbl_descritpion = $tbl_cdb_names['course_description'];

            $sql = 'SELECT `id`,`title`,`content`, `visibility` FROM `'.$tbl_descritpion.'`';
            $description = claro_sql_query_fetch_all($sql);

            return $description;
        }
    }
?>