<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );
/**
 * CLAROLINE
 *
 * The script works with the 'assignment' tables in the main claroline table
 *
 * @version 1.8 $Revision$
 *
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @package CLWRK
 *
 * @author Claro Team <cvs@claroline.net>
 * @author Sébastien Piraux <pir@cerdecam.be>
 */


/**
 * This class would manage read info about relation between a user  of a course and a group of same course
 * @author Christophe Gesché <moosh@claroline.net>
 * @todo find a good name
 * @static
 *
 */

class REL_GROUP_USER
{
    /**
     * Return list of groupe subscribed by a given user in a given/current course
     *
     * @param integer $_uid
     * @param course_syscode $course
     *
     */
    function get_user_group_list($_uid,$course=null)
    {
        $tbl_cdb_names = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course));
        $tbl_group_team          = $tbl_cdb_names['group_team'];
        $tbl_group_rel_team_user = $tbl_cdb_names['group_rel_team_user'];

        $userGroupList = array();

        $sql = "SELECT `tu`.`team` as `id` , `t`.`name`
                FROM `" . $tbl_group_rel_team_user . "` as `tu`
                INNER JOIN `" . $tbl_group_team . "`    as `t`
                  ON `tu`.`team` = `t`.`id`
                WHERE `tu`.`user` = " . (int) $_uid ;

        $groupList = claro_sql_query_fetch_all($sql);

        if( is_array($groupList) )
        {
            foreach( $groupList AS $group ) $userGroupList[$group['id']] = $group;
        }

        return $userGroupList;

    }
}


?>