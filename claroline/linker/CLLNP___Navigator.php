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
    * Class Learning Path Navigator
    *
    * @package CLLNP
    * @subpackage CLLINKER
    *
    * @author Fallier Renaud <renaud.claroline@gmail.com>
    */
    class CLLNP___Navigator extends Navigator
    {
        /*-------------------------
                 variable
         ------------------------*/
        var $_claroContainer;
        var $_basePath;

        /*----------------------------
                public method
        ---------------------------*/

        /**
        * Constructor
        *
        * @param  $basePath string path root directory of courses
        */
        function CLLNP___Navigator($basePath)
        {
            $this->_claroContainer = FALSE;
            $this->_basePath = $basePath;
        }

        /**
        * list the contents of a learnPath
        *
        * @param  $node string of current node (crl)
        * @return ClaroContainer who contains the objects current node
        * @throws  E_USER_ERROR if the node is not intended for the tool learnPath
        * @throws  E_USER_ERROR if the node is empty
        */
        function getResource($node = null)
        {
            if($node)
            {
                if(CRLTool::isForThisTool($node, 'CLLNP___'))
                {
                     $elementCRLArray = CRLTool::parseCRL($node);

                     if( !isset ($elementCRLArray['resource_id']) )
                     {
                         $learnPath = $this->_listLearnPath($elementCRLArray['course_sys_code']);
                         $elementList = array();

                         foreach ( $learnPath as $learnPathItem )
                         {
                             $crl = $node . "/" .$learnPathItem['learnPath_id'] ;

                             if($learnPathItem['visibility'] == 'HIDE')
                             {
                                 $isVisible = FALSE;
                             }
                             else
                             {
                                 $isVisible = TRUE;
                             }
                             $object = new ClaroObject( $learnPathItem['name'], $crl, TRUE , FALSE , $isVisible);
                             $elementList[] = $object ;
                         }

                         $container = new ClaroContainer ( '' , $node , $elementList );

                         return $container;
                     }
                     else
                     {
                         trigger_error ("Error : not crl valide", E_USER_ERROR);
                     }
                }
                else
                {
                    trigger_error ("Error : not crl for a learnPath tool", E_USER_ERROR);
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
        * list the learnPath
        *
        * @param  $course_sys_code  the id of a course
        * @return $array a array which contains the id and the name and the visibility of a learnPath
        */
        function _listLearnPath($course_sys_code)
        {
            $courseInfoArray = get_info_course($course_sys_code);
            $tbl_cdb_names = claro_sql_get_course_tbl($courseInfoArray["dbNameGlu"]);
            $tbl_learnPath = $tbl_cdb_names['lp_learnPath'];

            $sql = 'SELECT `learnPath_id`,`name`,`visibility`
                    FROM `'.$tbl_learnPath.'`';
            $learnPath = claro_sql_query_fetch_all($sql);

            return $learnPath;
        }
    }
?>
