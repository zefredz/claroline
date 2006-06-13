<?php // $Id$
/**
 * CLAROLINE
 *
 *     THIS LIBRARY script propose some basic function to administrate the campus :
 *
 *     register a user,
 *     delete a user of the plateform,
 *     unregister a user form a specific course,
 *     remove a user fro ma group,
 *     delete a course of the plateform,
 *     back up a hole course,
 *     change status of a user : admin, prof or student,
 *     Add users with CSV files
 *     ...see details of pre/post for each function's proper use.
 *
 * @version 1.8 $Revision$
 * 
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @see http://www.claroline.net/wiki/CLCRS/
 *
 * @package COURSE
 *
 * @author Claro Team <cvs@claroline.net>
 * @author Christophe Gesch� <moosh@claroline.net>
 *
 */

include_once( dirname(__FILE__) . '/fileManage.lib.php');

/**
 * delete a course of the plateform
 *
 * @author
 *
 * @param
 *
 * @return boolean TRUE        if suceed
 *         boolean FALSE       otherwise.
 */

function delete_course($code)
{
    global $coursesRepositorySys;
    global $eventNotifier;

    //declare needed tables
    $tbl_mdb_names = claro_sql_get_main_tbl();
    $tbl_course           = $tbl_mdb_names['course'           ];
    $tbl_rel_course_user  = $tbl_mdb_names['rel_course_user'  ];
    $tbl_notify           = $tbl_mdb_names['notify'  ];
    $tbl_course_class      = $tbl_mdb_names['rel_course_class'];

    $this_course = claro_get_course_data($code);
    $currentCourseId = $this_course['sysCode'];

    // DELETE USER ENROLLMENT INTO THIS COURSE

    $sql = 'DELETE FROM `' . $tbl_rel_course_user . '`
            WHERE code_cours="' . $currentCourseId . '"';

    claro_sql_query($sql);

    // Remove any recording in rel_cours_class
  	 
  	$sql = "DELETE FROM `" . $tbl_course_class . "`
  	        WHERE courseId ='" . addslashes($currentCourseId) . "'";
  	 
  	claro_sql_query($sql);

    // DELETE THE COURSE INSIDE THE PLATFORM COURSE REGISTERY

    $sql = 'DELETE FROM `' . $tbl_course . '`
            WHERE code= "' . addslashes($currentCourseId) . '"';

    claro_sql_query($sql);

    //notify the course deletion event
    $args['courseSysCode'] = $this_course['sysCode'];
    $args['courseDbName'] = $this_course['dbName'];
    $args['courseDirectory'] = $this_course['path'];
    $args['courseCategory']	= $this_course['categoryCode'];

    $eventNotifier->notifyEvent("course_deleted",$args);

    if ($currentCourseId == $code)
    {
        $currentCourseDbName    = $this_course['dbName'];
        $currentCourseDbNameGlu = $this_course['dbNameGlu'];
        $currentCoursePath      = $this_course['path'];

        if(get_conf('singleDbEnabled'))
        // IF THE PLATFORM IS IN MONO DATABASE MODE
        {
            // SEARCH ALL TABLES RELATED TO THE CURRENT COURSE
            claro_sql_query("use " . get_conf('mainDbName'));
            $tbl_to_delete = claro_sql_get_course_tbl(claro_get_course_db_name_glued($currentCourseId));
            foreach($tbl_to_delete as $tbl_name)
            {
                $sql = 'DROP TABLE IF EXISTS `' . $tbl_name . '`';
                claro_sql_query($sql);
            }
            // underscores must be replaced because they are used as wildcards in LIKE sql statement
            $cleanCourseDbNameGlu = str_replace("_","\_", $currentCourseDbNameGlu);
            $sql = 'SHOW TABLES LIKE "' . $cleanCourseDbNameGlu . '%"';

            $result = claro_sql_query($sql);
            // DELETE ALL TABLES OF THE CURRENT COURSE

            $tblSurvivor = array();
            while( $courseTable = mysql_fetch_array($result,MYSQL_NUM ) )
            {
                $tblSurvivor[]=$courseTable[0];
                //$tblSurvivor[$courseTable]='not deleted';
            }
            if (sizeof($tblSurvivor) > 0)
            {
                event_default( 'DELETE_COURSE'
                , array_merge(array ('DELETED_COURSE_CODE'=>$code
                ,'UNDELETED_TABLE_COUNTER'=>sizeof($tblSurvivor)
                )
                , $tblSurvivor )
                );
            }
        }
        else
        // IF THE PLATFORM IS IN MULTI DATABASE MODE
        {
            $sql = "DROP DATABASE `" . $currentCourseDbName . "`";
            claro_sql_query($sql);
        }

        // MOVE THE COURSE DIRECTORY INTO THE COURSE GARBAGE COLLECTOR

        claro_mkdir(get_conf('garbageRepositorySys'), CLARO_FILE_PERMISSIONS, true);

        rename(get_conf('coursesRepositorySys') . $currentCoursePath . '/',
        get_conf('garbageRepositorySys','garbage') . '/' . $currentCoursePath . '_' . date('YmdHis')
        );
    }
    else
    {
        die('WRONG CID');
    }
}

/**
 * to know if user is registered to a course or not
 *
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param  int     id of user in DB
 * @param  int     id of course in DB
 * @return boolean true if user is enrolled false otherwise
 */
function is_registered_to($user_id, $course_id)
{

    $tbl_mdb_names = claro_sql_get_main_tbl();
    $tbl_rel_course_user = $tbl_mdb_names['rel_course_user'];

    $sql = "SELECT count(*) `user_reg`
                 FROM `" . $tbl_rel_course_user . "`
                 WHERE `code_cours` = '" . addslashes($course_id) . "' AND `user_id` = '" . (int)$user_id . "'";
    $res = claro_sql_query_fetch_all($sql);
    return (bool) ($res[0]['user_reg']>0);
}

/**
 * Transfrom a key word into a usable key word ina SQL : "*" must be replaced by "%" and "%" by "\%"
 * @param  the string to transform
 * @return the string modified
 */

function pr_star_replace($string)
{
    $string = str_replace("%",'\%', $string);
    $string = str_replace("*",'%', $string);
    return $string;
}

?>
