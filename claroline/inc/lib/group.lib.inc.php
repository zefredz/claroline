<?php // $Id$
/**
 * CLAROLINE
 *
 * @version 1.8 $Revision$
 *
 * @copyright 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @see http://www.claroline.net/wiki/index.php/CLGRP
 *
 * @package CLGRP
 *
 * @author Claro Team <cvs@claroline.net>
 * @author Christophe Gesché <moosh@claroline.net>
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 *
 */
include_once dirname(__FILE__) . '/fileManage.lib.php';

function empty_group($groupIdList = 'ALL', $course_id = null)
{
    $groupFilter = false;
    $tbl_c_names = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));

    if ( ctype_digit(($groupIdList) )) $groupIdList[] = (int) $groupIdList;
    if ( strtoupper($groupIdList) == 'ALL' ) $sql_condition = '';
    elseif ( is_array($groupIdList) )
    {
        foreach ($groupIdList as $thisGroupId )
        {
            if ( ! is_int($thisGroupId) ) return claro_failure::set_failure('GROUP_LIST_ACTION_UNKNOWN');
        }
        $groupFilter = true;
        $sql_condition = implode(" , ", $groupIdList) ;
    }
    else
    {
        return claro_failure::set_failure('GROUP_LIST_ACTION_UNKNOWN');
    }


    $sql = "DELETE "
    .      "FROM `" . $tbl_c_names['group_rel_team_user'] . "`"
    .      ($groupFilter ? "WHERE team IN (" . $sql_condition . ")":"")
    ;
    if (!claro_sql_query($sql)) return claro_failure::get_last_failure();

    $sql = "UPDATE `" . $tbl_c_names['group_team'] . "` SET tutor='0'"
    .      ($groupFilter ? "WHERE id IN (" . $sql_condition . ")":"")
    ;
    if (!claro_sql_query($sql)) return claro_failure::get_last_failure();

    return true;
}

/**
 * function delete_groups($groupIdList = 'ALL')
 * deletes groups and their datas.
 * @param  mixed   $groupIdList - group(s) to delete. It can be a single id
 *                                (int) or a list of id (array). If no id is
 *                                given all the course group are deleted
 *
 * @return integer              - number of groups deleted.
 */

function delete_groups($groupIdList = 'ALL')
{
    global $garbageRepositorySys,$currentCourseRepository,$coursesRepositorySys;
    global $includePath;
    global $_cid,$_tid,$eventNotifier;

    $tbl_c_names = claro_sql_get_course_tbl();

    $tbl_groups           = $tbl_c_names['group_team'         ];
    $tbl_groupsUsers      = $tbl_c_names['group_rel_team_user'];
    $tbl_Forums           = $tbl_c_names['bb_forums'          ];

    require_once $includePath . '/../wiki/lib/lib.createwiki.php';

    delete_group_wikis( $groupIdList );

    /*
     * Check the data and notify eventmanager of the deletion
     */

    if ( strtoupper($groupIdList) == 'ALL' )
    {
        $sql_condition = '';
    }
    elseif ( is_array($groupIdList) )
    {
        foreach ($groupIdList as $thisGroupId )
        {
            if ( ! is_int($thisGroupId) ) return false;
        }

        $sql_condition = 'WHERE id IN ('. implode(' , ', $groupIdList) . ')';
    }
    else
    {
        if ( settype($groupIdList, 'integer') )
        {
            $sql_condition = '  WHERE id = ' . (int)$groupIdList ;

            $eventNotifier->notifyCourseEvent('group_deleted'
                                             , $_cid
                                             , $_tid
                                             , '0'
                                             , $groupIdList
                                             , '0');
        }
        else
        {
            return false;
        }
    }

    /*
     * Search the groups data necessary to delete them
     */

    $sql_searchGroup = "SELECT `id` AS `id`,
                               `secretDirectory` AS `directory`
                        FROM `" . $tbl_groups . "`".
                        $sql_condition;

    $groupList = claro_sql_query_fetch_all_cols($sql_searchGroup);

    //notify event manager about the deletion for each group

    foreach ($groupList['id'] as $thisGroupId )
    {
        $eventNotifier->notifyCourseEvent('group_deleted'
                                         , $_cid
                                         , $_tid
                                         , '0'
                                         , $thisGroupId
                                         , '0');
    }

    if ( count($groupList['id']) > 0 )
    {
        /*
         * Remove users, group(s) and group forum(s) from the course tables
         */

        $sql_deleteGroup        = "DELETE FROM `" . $tbl_groups . "`
                                   WHERE id IN (" . implode(' , ', $groupList['id']) . ")
                                    # ".__FUNCTION__."
                                    # ".__FILE__."
                                    # ".__LINE__;

        $sql_cleanOutGroupUsers = "DELETE FROM `" . $tbl_groupsUsers . "`
                                   WHERE team IN (" . implode(' , ', $groupList['id']) . ")
                                    # ".__FUNCTION__."
                                    # ".__FILE__."
                                    # ".__LINE__;

        $sql_deleteGroupForums  = "DELETE FROM `" . $tbl_Forums . "`
                                   WHERE group_id IN (" . implode(' , ', $groupList['id']) . ")
                                    # ".__FUNCTION__."
                                    # ".__FILE__."
                                    # ".__LINE__;

        // Deleting group record in table
        $deletedGroupNumber = claro_sql_query_affected_rows($sql_deleteGroup);

        // Delete all members of deleted group(s)
        claro_sql_query($sql_cleanOutGroupUsers);

        // Delete all Forum of deleted group(s)
        claro_sql_query($sql_deleteGroupForums);

        // Reset auto_increment
        $sql_getmaxId = 'SELECT MAX( id ) max From  `' . $tbl_groups . '` ';
        $maxGroupId = claro_sql_query_fetch_all($sql_getmaxId);
        $sql_reset_autoincrement = "ALTER TABLE `" . $tbl_groups . "`
                                    PACK_KEYS =0
                                    CHECKSUM =0
                                    DELAY_KEY_WRITE =0
                                    AUTO_INCREMENT = " . ($maxGroupId[0]['max']+1) ."
                                    # ".__FUNCTION__."
                                    # ".__FILE__."
                                    # ".__LINE__
                                    ;
        claro_sql_query($sql_reset_autoincrement);

        /**
         * Archive and delete the group files
         */

        // define repository for deleted element

        $groupGarbage = $garbageRepositorySys . '/' . $currentCourseRepository . '/group/';
        if ( ! file_exists($groupGarbage) ) claro_mkdir($groupGarbage, CLARO_FILE_PERMISSIONS, true);

        foreach ( $groupList['directory'] as $thisDirectory )
        {
            if ( file_exists($coursesRepositorySys.$currentCourseRepository . '/group/' . $thisDirectory) )
            {
                rename($coursesRepositorySys . $currentCourseRepository . '/group/' . $thisDirectory,
                       $groupGarbage . $thisDirectory);
            }
        }

        return $deletedGroupNumber;

    } // end if $groupList
    else
    {
        return FALSE;
    }
}

/**
 * Alias of delete_groups() called without parameters
 */

function deleteAllGroups()
{
    return delete_groups('ALL');
}

/**
 * Fill in the groups with still unenrolled students.
 * The algorithm takes care to fill first the freest groups
 * with the less enrolled users
 *
 * @author Chrisptophe Gesché <moosh@claroline.net>,
 * @author Hugues Peeters     <hugues.peeters@claroline.net>
 *
 * @return void
 */

function fill_in_groups($course_id = NULL)
{
    global $currentCourseId, $nbGroupPerUser;
    $tbl_m_names = claro_sql_get_main_tbl();
    $tbl_c_names = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));

    $tbl_CoursUsers       = $tbl_m_names['rel_course_user'    ];
    $tbl_groups           = $tbl_c_names['group_team'         ];
    $tbl_groupsUsers      = $tbl_c_names['group_rel_team_user'];

    // check if nbGroupPerUser is a positive integer else return false
    if( !settype($nbGroupPerUser, 'integer') || $nbGroupPerUser < 0 )
        return FALSE;
    /*
     * Retrieve all the groups where enrollment is still allowed
     * (reverse) ordered by the number of place available
     */

    $sql = "SELECT g.id AS gid, g.maxStudent-count(ug.user) AS  nbPlaces, g.maxStudent AS g_maxStudent
    # g.maxStudent AS g_maxStudent is not use  in code but would be added  for exists in HAVING
            FROM `" . $tbl_groups . "` AS  g
            LEFT JOIN  `" . $tbl_groupsUsers . "` ug
            ON    `g`.`id` = `ug`.`team`
            GROUP BY (`g`.`id`)
            HAVING nbPlaces > 0 OR g_maxStudent IS NULL
            ORDER BY nbPlaces DESC";

    $groupAvailPlace = array();
    $groupList = claro_sql_query_fetch_all($sql);
    foreach ($groupList as $group) $groupAvailPlace[$group['gid']] = $group['nbPlaces'];

    /*
     * Retrieve course users (reverse) ordered by the number
     * of group they are already enrolled
     */

    $sql = "SELECT
                cu.user_id AS uid,
                (" . $nbGroupPerUser . "-count(ug.team)) AS nbTicket
            FROM `" . $tbl_CoursUsers . "` cu
            LEFT JOIN  `" . $tbl_groupsUsers . "` AS ug
            ON    `ug`.`user`      = `cu`.`user_id`
            WHERE `cu`.`code_cours`='" . addslashes($currentCourseId) . "'
            AND   `cu`.`statut`    = 5 #no teacher
            AND   `cu`.`tutor`     = 0 #no tutor
            GROUP BY (cu.user_id)
            HAVING nbTicket > 0
            ORDER BY nbTicket DESC";
    $userToken = array();
    $userList = claro_sql_query_fetch_all($sql);
    foreach ($userList as $user) $userToken[$user['uid']] = $user['nbTicket'];
    unset($userList,$user);

    /**
     * Retrieve the present state of the users repartion in groups
     */

    $sql = "SELECT user AS uid, team AS gid FROM `" . $tbl_groupsUsers . "`";
    $groupUser = array();
    $memberList = claro_sql_query_fetch_all($sql);
    foreach ($memberList as $member) $groupUser[$member['gid']] [] = $member['uid'];
    unset($memberList,$member);


    /**
     * Compute the most approriate group fill in
     */

    $prepareQuery = array();

    while    (   is_array($groupAvailPlace) && !empty($groupAvailPlace)
              && !empty($userToken) && is_array($userToken))
    {

        /*
         * Sort the users to always start with the less enrolled user
         * to reach first a balance between groups
         */

        arsort($userToken);
        reset($userToken);
        $userPutSucceed = false; // default initialisation

        while (   ( $userPutSucceed == false               )
               && ( list($thisUser, ) = each($userToken) ) )
        {
            /*
             * Sort the groups to always start with the freest group
             * to reach first a balance between groups
             */

            arsort($groupAvailPlace);
            reset($groupAvailPlace);
            while (   ( $userPutSucceed == false )
                   && (list ($thisGroup, ) = each ($groupAvailPlace) ) )
            {
                if ( ! isset($groupUser[$thisGroup])
                     || ! is_array( $groupUser[$thisGroup] )
                     || ! in_array( $thisUser, $groupUser[$thisGroup]) )
                {
                    $groupUser[$thisGroup][] = $thisUser;

                    $prepareQuery[] = "(" . $thisUser . ", ".$thisGroup.")";

                    if ( -- $groupAvailPlace[$thisGroup] <= 0 )
                        unset( $groupAvailPlace[$thisGroup] );

                    if ( -- $userToken[$thisUser] <= 0)
                        unset( $userToken[$thisUser] );

                    $userPutSucceed = TRUE;
                }
            }
            // if the user cannot be put in any group delete him from the userToken
            if ( $userPutSucceed == false ) unset( $userToken[$thisUser] );
        }
    }


    /*
     * STORE THE 'FILL IN' PROCESS IN THE DATABASE
     */

    if ( is_array($prepareQuery) && count($prepareQuery) > 0)
    {
            $sql = "INSERT INTO `" . $tbl_groupsUsers . "`
                    (`user`, `team`)
                    VALUES " . implode(" , ", $prepareQuery) ;
            claro_sql_query($sql);
    }
    // else : no student without groups

    return true;
}


/**
 * count user in course.
 * @param course_id
 * @return user qty in the given course
 * @author Christophe Gesché <moosh@claroline.net>
 */
function group_count_students_in_course($course_id)
{
    $tbl_mdb_names = claro_sql_get_main_tbl();

    $sql = "SELECT COUNT(user_id) AS qty
            FROM `" . $tbl_mdb_names['rel_course_user'] . "`
            WHERE  code_cours = '" . addslashes($course_id) . "'
            AND    statut = 5 AND tutor = 0";

    return claro_sql_query_get_single_value($sql);

}
/**
 * Count users in all groups.
 * @param interger (optional) course_id
 * @return interger user quantity
 * @author Christophe Gesché <moosh@claroline.net>
 * @todo rename this function or change it. count include non student users.
 */
function group_count_students_in_groups($course_id=null)
{
    $tbl_cdb_names = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));

    $sql = "SELECT COUNT(user)
            FROM `" . $tbl_cdb_names['group_rel_team_user'] . "`";
    return (int) claro_sql_query_get_single_value($sql);
}

/**
 * Count users in a given group.
 * @param interger (optional) group_id
 * @param interger (optional) course_id
 * @return interger user quantity
 * @author Christophe Gesché <moosh@claroline.net>
 * @todo rename this function or change it. count include non student users.
 */
function group_count_students_in_group($group_id,$course_id=null)
{
    $tbl_cdb_names = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));

    $sql = "SELECT COUNT(user)
            FROM `" . $tbl_cdb_names['group_rel_team_user'] . "`
            WHERE `team` = '". (int) $group_id . "'";
    return (int) claro_sql_query_get_single_value($sql);
}

/**
 * Count groups where a user is ennrolled in a given course
 * @param $user_id
 * @param interger (optional) course_id
 * @return integer Count of groups where a given user is ennrolled in a given (o current) course
 * @author Christophe Gesché <moosh@claroline.net>
 *
 */
function group_count_group_of_a_user($user_id, $course_id=null)
{
    $tbl_cdb_names   = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
    $sql = "SELECT COUNT(`team`)
            FROM `" . $tbl_cdb_names['group_rel_team_user'] . "`
            WHERE user='" . (int) $user_id . "'";

    return claro_sql_query_get_single_value($sql);
}

/**
 * Create a new group
 *
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param  string $groupName - name of the group
 * @param  int $maxMember  - max user allowed for this group
 * @return int group id
 */

function create_group($groupName, $maxMember)
{
    global $coursesRepositorySys, $currentCourseRepository, $includePath ;

    require_once $includePath . '/lib/forum.lib.php';
    require_once $includePath . '/lib/fileManage.lib.php';

    $tbl_cdb_names = claro_sql_get_course_tbl();
    $tbl_groups    = $tbl_cdb_names['group_team'];

    /**
     * Create a directory allowing group student to upload documents
     */

    //  Create a Unique ID path preventing other enter

    do
    {
        $groupRepository = uniqid($groupName . '_');
    }
    while ( check_name_exist(  $coursesRepositorySys
                             . $currentCourseRepository
                             . '/group/' . $groupRepository) );

    claro_mkdir($coursesRepositorySys . $currentCourseRepository . '/group/' . $groupRepository, CLARO_FILE_PERMISSIONS);

    /*
     * Insert a new group in the course group table and keep its ID
     */

    $sql = "INSERT INTO `" . $tbl_groups . "`
            SET name = '" . $groupName . "',
               `maxStudent`  = ". (is_null($maxMember) ? 'NULL' : "'" . (int) $maxMember ."'") .",
                secretDirectory = '" . addslashes($groupRepository) . "'";

    $createdGroupId = claro_sql_query_insert_id($sql);

    /*
     * Create a forum for the group in the forum table
     */

    $forumInsertId = create_forum( $groupName. ' - '. strtolower(get_lang("Forum"))
                                 , '' // forum description
                                 , 2  // means forum post allowed,
                                 , (int) GROUP_FORUMS_CATEGORY
                                 , $createdGroupId
                                 );

     require_once $includePath . '/../wiki/lib/lib.createwiki.php';
     create_wiki( $createdGroupId, $groupName. ' - Wiki' );

     return $createdGroupId;
}


?>
