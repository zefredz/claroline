<?php // $Id$
/** 
 * CLAROLINE 
 *
 * @version 1.6
 *
 * @copyright 2001-2005 Universite catholique de Louvain (UCL)
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
    global $garbageRepositorySys,$currentCourseRepository,$coursesRepositorySys,
           $tbl_GroupsUsers,$tbl_Groups,$tbl_Forums, $db;

    /*
     * Check the data
     */

    if ( strtoupper($groupIdList) == 'ALL' )
    {
        $sql_condition = '';
    }
    elseif ( is_array($groupIdList) )
    {
        foreach($groupIdList as $thisGroupId)
        {
            if (! is_int($thisGroupId) ) return false;
        }

        $sql_condition = 'WHERE id IN ('.implode(' , ', $groupIdList).')';
    }
    else
    {
        if ( settype($groupIdList, 'integer') )
        {
            $sql_condition = '  WHERE id = '.$groupIdList;
        }
        else
        {
            return false;
        }
    }


    /*
     * Search the groups data necessary to delete them
     */

    $sql_searchGroup = "SELECT `id` `gid`, `secretDirectory` `groupRepository`, `forumId`
                        FROM `".$tbl_Groups."`".
                        $sql_condition;

    $res_searchGroup = claro_sql_query($sql_searchGroup);

    if ($res_searchGroup)
    {
        while ($gpData =mysql_fetch_array($res_searchGroup))
        {
            $groupList['id'       ][] = $gpData['gid'            ];
            $groupList['directory'][] = $gpData['groupRepository'];
            $groupList['forumId'  ][] = $gpData['forumId'        ];
        }
    }

    if ($groupList)
    {
        /*
         * Remove users, group(s) and group forum(s) from the course tables
         */

        $sql_deleteGroup        = "DELETE FROM `".$tbl_Groups."`
                                   WHERE id IN (".implode(" , ", $groupList['id']).")";

        $sql_cleanOutGroupUsers = "DELETE FROM `".$tbl_GroupsUsers."`
                                   WHERE team IN (".implode(" , ", $groupList['id']).")";

        $sql_deleteGroupForums  = "DELETE FROM `".$tbl_Forums."`
                                   WHERE cat_id='1'
                                   AND forum_id IN (".implode(" , ", $groupList['forumId']).")";

        // Deleting group record in table
        $res_deleteGroup    = claro_sql_query($sql_deleteGroup);
        $deletedGroupNumber = mysql_affected_rows();

        // Delete all members of deleted group(s)
        $res_cleanOutGroupUsers = claro_sql_query($sql_cleanOutGroupUsers);

        // Delete all Forum of deleted group(s)
        $res_deleteGroupForums = claro_sql_query($sql_deleteGroupForums);


        // Reset auto_increment
        $sql_getmaxId = 'SELECT MAX( id ) max From  `'.$tbl_Groups.'` ';
        $maxGroupId = claro_sql_query_fetch_all($sql_getmaxId);
        $sql_reset_autoincrement = "ALTER TABLE `".$tbl_Groups."` 
                                    PACK_KEYS =0 
                                    CHECKSUM =0 
                                    DELAY_KEY_WRITE =0 
                                    AUTO_INCREMENT = ".($maxGroupId[0]['max']+1)."";
        claro_sql_query($sql_reset_autoincrement);
        
        /*
         * Archive and delete the group files
         */

        // define repository for deleted element

        $groupGarbage =    $garbageRepositorySys."/".$currentCourseRepository."/group/";
        if ( ! file_exists($groupGarbage) ) mkdirs($groupGarbage, 0777);

        foreach($groupList['directory'] as $thisDirectory)
        {
            if (file_exists($coursesRepositorySys.$currentCourseRepository."/group/".$thisDirectory))
            {
                rename($coursesRepositorySys.$currentCourseRepository."/group/".$thisDirectory,
                       $groupGarbage.$thisDirectory);
            }
        }

        
        return $deletedGroupNumber;

    }                            // end if $groupList
    else
    {
        return FALSE;
    }
}

/** 
 * alias of delete_groups() called without parameters
 */

function deleteAllGroups()
{
    return delete_groups();
}

/**
 * is $_cid set. 
 * @param $ifNot default "DIE" 
 * @return boolean Whether is set $_cid
 */
function cidNeeded( $ifNot = "DIE" )
{
    global $_cid;

    if( ! isset($_cid))
    {
        switch ($ifNot)
        {
            case "DIE"  :
                die ("\$_cid missing");
            case "echo" :
                echo ("\$_cid missing");
                break;
            case "rtnFals" :
                return false;
        }
    }

    return TRUE;
}

/**
 * Like in Java, creates the directory named by this abstract pathname,
 * including any necessary but nonexistent parent directories.
 *
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @author Christophe Gesche <gesche@ipm.ucl.ac.be>
 *
 * @param  string $path - path to create
 * @param  string $mode - directory permission (default is '777')
 *
 * @return boolean TRUE if succeeds FALSE otherwise
 */

function mkdirs($path, $mode = 0777)
{
    if ( file_exists($path) )
    {
        return false;
    }
    else
    {
        mkdirs( dirname($path) , $mode);
        return mkdir($path, $mode);
    }
}

/**
 * Fill in the groups with still unenrolled students.
 * The algorithm takes care to fill first the freest groups
 * with the less enrolled users
 *
 * @author Chrisptophe Gesché <christophe.geshe@claroline.net>,
 * @author Hugues Peeters     <hugues.peeters@claroline.net>
 *
 * @return void
 */

function fill_in_groups()
{
    global $currentCourseId, $nbGroupPerUser,
           $tbl_CoursUsers, $tbl_Groups, $tbl_Users, $tbl_GroupsUsers;
    
    // check if nbGroupPerUser is a positive integer else return false
    if( !settype($nbGroupPerUser, "integer") || $nbGroupPerUser < 0 )
        return FALSE;
    /*
     * Retrieve all the groups where enrollment is still allowed
     * (reverse) ordered by the number of place available
     */

    $sql = "SELECT g.id gid, g.maxStudent-count(ug.user) nbPlaces
            FROM `".$tbl_Groups."` g
            LEFT JOIN  `".$tbl_GroupsUsers."` ug
            ON    `g`.`id` = `ug`.`team`
            GROUP BY (`g`.`id`)
            HAVING nbPlaces > 0
            ORDER BY nbPlaces DESC";
    $result = claro_sql_query($sql);

    while( $group = mysql_fetch_array($result, MYSQL_ASSOC) )
    {
        $groupAvailPlace[$group['gid']] = $group['nbPlaces'];
    }
    
    /*
     * Retrieve course users (reverse) ordered by the number
     * of group they are already enrolled
     */
    
    $sql = "SELECT cu.user_id uid,  (".$nbGroupPerUser."-count(ug.team)) nbTicket
             FROM `".$tbl_CoursUsers."` cu
            LEFT JOIN  `".$tbl_GroupsUsers."` ug
            ON    `ug`.`user`      = `cu`.`user_id`
            WHERE `cu`.`code_cours`='".$currentCourseId."'
            AND   `cu`.`statut`    = 5 #no teacher
            AND   `cu`.`tutor`     = 0 #no tutor
            GROUP BY (cu.user_id)
            HAVING nbTicket > 0
            ORDER BY nbTicket DESC";
    $result = claro_sql_query($sql);

    while($user = mysql_fetch_array($result, MYSQL_ASSOC))
    {
        $userToken[$user['uid']] = $user['nbTicket'];
    }

    /*
     * Retrieve the present state of the users repartion in groups
     */

    $sql   ="SELECT user uid, team gid FROM `".$tbl_GroupsUsers."`";

    $result = claro_sql_query($sql);

    while ($member = mysql_fetch_array($result,MYSQL_ASSOC))
    {
        $groupUser[$member['gid']] [] = $member['uid'];
    }

    /*
     * Compute the most approriate group fill in
     */

    while    (   is_array($groupAvailPlace) && !empty($groupAvailPlace)
             && is_array($userToken      ) && !empty($userToken      ) )
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
                if (    ! is_array( $groupUser[$thisGroup] )
                     || ! in_array( $thisUser, $groupUser[$thisGroup]) )
                {
                    $groupUser[$thisGroup][] = $thisUser;

                    $prepareQuery [] = '('.$thisUser.', '.$thisGroup.')';

                    if ( -- $groupAvailPlace[$thisGroup] <= 0 )
                        unset( $groupAvailPlace[$thisGroup] );

                    if ( -- $userToken[$thisUser] <= 0)
                        unset( $userToken[$thisUser] );

                    $userPutSucceed = TRUE;
                }
            }
            // if the user cannot be put in any group delete him from the userToken
            if ( $userPutSucceed == false) unset( $userToken[$thisUser] );
        }
    }


    /*
     * STORE THE 'FILL IN' PROCESS IN THE DATABASE
     */

    if ( is_array($prepareQuery) )
    {
            $sql = "INSERT INTO `".$tbl_GroupsUsers."`
                    (`user`, `team`)
                    VALUES ".implode(" , ", $prepareQuery);

            claro_sql_query($sql);
    }
    // else : no student without groups
}
?>