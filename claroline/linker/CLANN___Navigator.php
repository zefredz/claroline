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

    require_once (dirname(__FILE__) . '/navigator.lib.php');
    require_once (dirname(__FILE__) . '/CLANN___Resolver.php');

   /**
    * Class announcement Navigator  
    *
    * @package CLANN 
    * @subpackage CLLINKER 
    *
    * @author Fallier Renaud <renaud.claroline@gmail.com>
    */
    class CLANN___Navigator extends Navigator  
    {
        /**
         * @var $_claroContainer
         */
        var $_claroContainer;  

        /*----------------------------
                public method
        ---------------------------*/

        /**
        * Constructor
        *
        * @param   $basePath string path root directory of courses  
        */
        function CLANN___Navigator($basePath = null)
        {
            global $_course;
            $this->_claroContainer = FALSE; 
        }

        /**
        * list the contents of a announcement
        *
        * @param  $node string of current node (crl)
        * @return ClaroContainer who contains the objects current node
        * @throws E_USER_ERROR if the node is not intended for the tool forum
        * @throws E_USER_ERROR if the node is empty
        * @global path rootWeb
        */
        function getResource($node = null)
        {
            global $rootWeb;
            
            // if the node is not null    
            if($node)
            {
                // if this node (crl) is for announcement
                if(CRLTool::isForThisTool($node, 'CLANN___'))
                {
                     $elementCRLArray = CRLTool::parseCRL($node);

                     if( !isset ($elementCRLArray['resource_id']) )               
                     {
                         // listing of annoncouncement
                         $annonce = $this->_listAnnonce($elementCRLArray['course_sys_code']);
                         $elementList = array();

                         foreach ($annonce as $itemAnnonce )
                         {
                             $crl = $node . '/' . $itemAnnonce['id']; 
                             $res = new CLANN___Resolver($rootWeb); 
                             $title = $res->getTitle($elementCRLArray['course_sys_code'], $itemAnnonce['id']); 
                             $isVisible = ( $itemAnnonce['visibility'] == 'SHOW');
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
                    trigger_error ("Error : not crl for a announcement tool", E_USER_ERROR);
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
         function _listAnnonce($course_sys_code)
        {
            $courseInfoArray = get_info_course($course_sys_code); 
            $tbl_cdb_names = claro_sql_get_course_tbl($courseInfoArray["dbNameGlu"]);
            $tbl_annonce = $tbl_cdb_names['announcement'];
            
            $sql = 'SELECT `id`,`title` , `visibility` FROM `'.$tbl_annonce.'`'; 
            $annonces = claro_sql_query_fetch_all($sql);
            
            return $annonces;
        }
    }
?>
