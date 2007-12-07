<?php // $Id$
/**
 * CLAROLINE
 *
 * @version 1.7 $Revision$
 * 
 * @copyright (c) 2001-2005 Universite catholique de Louvain (UCL)
 * 
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE 
 *
 * @see http://www.claroline.net/wiki/CLCRS/
 *
 * @package CLCRS
 *
 * @author Claro Team <cvs@claroline.net>
 *
 */

/**
 * return the title of a course  
 *
 * @param $course_sys_code id of a course
 * @return string a string with the title of the course 
 * @todo replace content of this function with claro_get_course_officialCode and claro_get_course_name
 */
function get_course_title($cid)
{
    $k = claro_get_course_data($cid); 
    if (isset($k['officialCode']) && isset($k['name'])) return stripslashes($k['officialCode'] . ' : ' . $k['name']);
    else                                                return NULL;
}


/**
 * return all info of a course 
 *
 * @param $cid the id of a course
 * @return array (array) an associative array containing all info of the course
 * @global $courseTablePrefix
 * @global $dbGlu
 * @todo use claro_get_course_data
 */
function get_info_course($cid)
{
    global $courseTablePrefix, $dbGlu;

    $tbl_mdb_names = claro_sql_get_main_tbl();
    $tbl_course    = $tbl_mdb_names['course'   ];
    $tbl_category  = $tbl_mdb_names['category' ];

    if ($cid)
    {
        $_course = claro_get_course_data($cid);
        // GET COURSE TABLE

        // read of group tools config related to this course

        $sql = "SELECT self_registration,
                       private, 
                       nbGroupPerUser, 
                       forum, document, 
                       wiki, 
                       chat
                FROM `".$_course['dbNameGlu']."group_property`";

        $result = claro_sql_query($sql)  or die ('WARNING !! DB QUERY FAILED ! '.__LINE__);

        $gpData = mysql_fetch_array($result);

        $_course ['registrationAllowed'] = (bool) ($gpData['self_registration'] == 1);
        $_course ['private'            ] = (bool) ($gpData['private'          ] == 1);
        $_course ['nbGroupPerUser'     ] = $gpData['nbGroupPerUser'];
        $_course ['tools'] ['forum'    ] = (bool) ($gpData['forum'            ] == 1);
        $_course ['tools'] ['document' ] = (bool) ($gpData['document'         ] == 1);
        $_course ['tools'] ['wiki'     ] = (bool) ($gpData['wiki'             ] == 1);
        $_course ['tools'] ['chat'     ] = (bool) ($gpData['chat'             ] == 1);
    }
    else
    {
        $_course = NULL;
        //// all groups of these course
        ///  ( theses properies  are from the link  between  course and  group,
        //// but a group  can be only in one course)

        $_course ['registrationAllowed'] = FALSE;
        $_course ['tools'] ['forum'    ] = FALSE;
        $_course ['tools'] ['document' ] = FALSE;
        $_course ['tools'] ['wiki'     ] = FALSE;
        $_course ['tools'] ['chat'     ] = FALSE;
        $_course ['private'            ] = TRUE;
    }

    return $_course;
}


/**
 * Get the name of a course.
 * @param  string $course_id (optionnal)  If not set, it use the current course 
 *         will be taken.
 * @return string path
 * @author Christophe Gesché <moosh@claroline.net>
 * @since 1.7
 */
function claro_get_course_name($cid=NULL) 
{
    $k =claro_get_course_data($cid); 
    if (isset($k['name'])) return $k['name'];
    else                   return NULL;
}



/**
 * Get the official code of a course.
 * @param  string $course_id (optionnal)  If not set, it use the current course 
 *         will be taken.
 * @return string path
 * @author Christophe Gesché <moosh@claroline.net>
 * @since 1.7
 */
function claro_get_course_officialCode($cid=NULL) 
{
    $k =claro_get_course_data($cid); 
    if (isset($k['officialCode'])) return $k['officialCode'];
    else                           return NULL;
}

/**
    * return all info of tool for a course
    *
    * @param $cid the id of a course
    * @return array (array) an associative array containing all info of tool for a course
    * @global $clarolineRepositoryWeb
    */ 
function get_course_tool_list($cid)
{
    global $clarolineRepositoryWeb;

    $toolNameList = claro_get_tool_name_list();

    $_course = get_info_course($cid);

    $tbl_mdb_names = claro_sql_get_main_tbl();
    $tbl_tool = $tbl_mdb_names['tool'];

    $courseToolList = array();

    if ($cid) // have course keys to search data
    {
        $sql ="SELECT ctl.id             id,
                        pct.claro_label    label,
                        ctl.script_name    name,
                        ctl.access         access,
                        pct.icon           icon,
                        pct.access_manager access_manager,

                        IF(pct.script_url IS NULL ,
                           ctl.script_url,CONCAT('".$clarolineRepositoryWeb."', 
                           pct.script_url)) url

                           FROM `".$_course['dbNameGlu']."tool_list` ctl

                           LEFT JOIN `" . $tbl_tool . "` pct
                           ON       pct.id = ctl.tool_id

                           ORDER BY ctl.rank";

            $result = claro_sql_query($sql)  or die ('WARNING !! DB QUERY FAILED ! '.__LINE__);

             while( $tlistData = mysql_fetch_array($result) ) 
            {
                $courseToolList[] = $tlistData;
               }

               $tmp = array();

               foreach($courseToolList as $courseTool)
               {
                   if( isset($courseTool['label']) )
                   {
                       $courseTool['name'] = $toolNameList[$courseTool['label']];    
                   } 
                   $tmp[] = $courseTool;
               }

               $courseToolList = $tmp;
               unset( $tmp );
        }
        
        return $courseToolList;
    }   
?>