<?php // $Id$
/** 
 * CLAROLINE 
 *
 * This is the group HOME page
 * This page list existing group in course.
 * If allowed to enter, a link is under the group name 
 * user can subscribe to a group if
 * - user is member of the course
 * - auto subscribe is aivailable
 * - user don't hev hit the max group per user
 * - the group is not full
 * Course Admin have more tools.
 * - Create groups
 * - Edit groups 
 * - Fill groups
 * - empty groups
 * - remove (all) groups
 * complete listing of  groups member is not aivailable. the  unsorted info is in user tool
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
 *
 */

////**************** INITIALISATION************************

$tlabelReq = 'CLGRP___';
require '../inc/claro_init_global.inc.php';
if ( ! $_cid) claro_disp_select_course();
$nameTools     = $langGroups;

@include($includePath.'/lib/debug.lib.inc.php');
include($includePath.'/lib/group.lib.inc.php');
include($includePath.'/lib/fileManage.lib.php');

include($includePath.'/lib/events.lib.inc.php');

$htmlHeadXtra[] =
'<script>
function confirmationEmpty ()
{
        if (confirm(" '.clean_str_for_javascript($langConfirmEmptyGroups) .' "))
                {return true;}
        else
                {return false;}
};
function confirmationDelete ()
{
        if (confirm(" '.clean_str_for_javascript($langConfirmDeleteGroups).' "))
                {return true;}
        else
                {return false;}
};
</script>';

$htmlHeadXtra[] =
'<style type=text/css>
<!--
.comment { margin-left: 30px}
-->
</style>';

// use viewMode
claro_set_display_mode_available(TRUE);

/**
 * DB TABLE NAMES INIT
 */

$tbl_cdb_names = claro_sql_get_course_tbl();
$tbl_mdb_names = claro_sql_get_main_tbl();

$tbl_user             = $tbl_mdb_names['user'               ];
$tbl_CoursUsers       = $tbl_mdb_names['rel_course_user'    ];
$tbl_Groups           = $tbl_cdb_names['group_team'         ];
$tbl_GroupsProperties = $tbl_cdb_names['group_property'     ];
$tbl_GroupsUsers      = $tbl_cdb_names['group_rel_team_user'];
$tbl_Forums           = $tbl_cdb_names['bb_forums'          ];

/**
 * MAIN SETTINGS INIT
 */

$currentCourseRepository = $_course['path'     ];
$currentCourseId         = $_course['sysCode'  ];
$is_allowedToManage      = claro_is_allowed_to_edit();
//$garbageRepositorySys  = $clarolineRepositorySys.'garbage/';
$isGroupRegAllowed       =     $_groupProperties ['registrationAllowed']
                           && (  !$is_courseTutor
                               || (  $is_courseTutor
                                   && $tutorCanBeSimpleMemberOfOthersGroupsAsStudent
                                  )
                               );
// Warning $groupRegAllowed is not valable before check of groupPerUserQuota

$groupPrivate               = $_groupProperties ['private'            ];
$nbGroupPerUser             = $_groupProperties ['nbGroupPerUser'     ];

if ( !$nbGroupPerUser )
{
    $sql = "SELECT COUNT(*)
         FROM `".$tbl_Groups."`";
    $result = claro_sql_query($sql);
    $tmp = mysql_fetch_array($result);
    $nbGroupPerUser = $tmp[0];
}
$tools['forum'   ] = $_groupProperties ['tools'] ['forum'    ];
$tools['document'] = $_groupProperties ['tools'] ['document' ];
$tools['wiki'    ] = $_groupProperties ['tools'] ['wiki'     ];
$tools['chat'    ] = $_groupProperties ['tools'] ['chat'     ];

//  THIS 2 SQL query  upgrade course db on fly.  
$sql = "ALTER IGNORE TABLE `".$tbl_GroupsProperties."`
        CHANGE `nbCoursPerUser` `nbGroupPerUser` TINYINT UNSIGNED DEFAULT '1'";
@mysql_query($sql);

$sql = "ALTER IGNORE TABLE `".$tbl_GroupsProperties."`
        ADD `nbGroupPerUser` TINYINT UNSIGNED DEFAULT '1'
        AFTER `self_registration`";
@mysql_query($sql);

//// **************** ACTIONS ***********************

$display_groupadmin_manager = (bool) $is_allowedToManage;

// ACTIONS

// This is called by the form build in group_creation.php

if ($is_allowedToManage)
{
    if($_REQUEST['creation'])
    {
        // For all Group forums, cat_id=1
        $group_quantity = (int) $_REQUEST['group_quantity'];
        if ($group_quantity<1) $group_quantity = 1;

        for ($i = 1; $i <= $group_quantity; $i++)
        {
            /**
             * Insert a new group in the course group table and keep its ID
             */

            $sql = "INSERT INTO `".$tbl_Groups."`
                    (maxStudent) VALUES ('".$group_max."')";

            claro_sql_query($sql);

            $lastId = mysql_insert_id();

            /*
             * Create a forum for the group in the forum table
             */
        // we need to know what is the max forum_order only if lastOrder 
        // is not already set
        if (!$lastOrder)
        {
        // select max order in the forum cat only (cat_id = 1)
            $sql = "SELECT MAX(`forum_order`)
                FROM `".$tbl_Forums."`
                WHERE `cat_id` = 1"; 
            $result = claro_sql_query($sql);
            $tmp = mysql_fetch_array($result);
            $lastOrder = $tmp[0];
        }
        $lastOrder += 1;
        
            $sql = "INSERT INTO `".$tbl_Forums."`
                    SET forum_id           = '',
                        forum_name         = '".$langForumGroup." ".$lastId."',
                        forum_desc         = '',
                        forum_access       = 2,
                        forum_moderator    = 1,
                        forum_topics       = 0,
                        forum_posts        = 0,
                        forum_last_post_id = '0',
                        cat_id             = '1',
                        forum_type         = '0',
                        md5                = '".md5(time())."',
                        forum_order        = '".$lastOrder."'";
            claro_sql_query($sql);

            $forumInsertId = mysql_insert_id();

            /*
             * Create a directory for to allow group student to upload documents
             */

            /*  Create a Unique ID path preventing other enter */

            $secretDirectory    =    uniqid("")."_team_".$lastId;

            while ( check_name_exist($coursesRepositorySys.$currentCourseRepository."/group/$secretDirectory") )
            {
                $secretDirectory = uniqid("")."_team_".$lastId;
            }

            mkdirs($coursesRepositorySys.$currentCourseRepository."/group/".$secretDirectory, 0777);

            /* Stores the directory path into the group table */

            $sql = "UPDATE `".$tbl_Groups."`
                    SET   name            = '".$langGroup." ".$lastId."',
                          forumId         = '".$forumInsertId."',
                          secretDirectory = '".$secretDirectory."'
                    WHERE id ='".$lastId."'";

            mysql_query($sql);

        }    // end for ($i = 1; $i <= $group_quantity; $i++)

        $message= $group_quantity.' '.$langGroupsAdded;
        event_default('GROUPMANAGING',array ('CREATE_GROUP' => $group_quantity));

    }    // end if $submit


    /**
     * GROUP PROPERTIES
     */

    // This is called by the form in group_properties.php
    // set common properties for all groups
    if($_REQUEST['properties'])
    {
        if($_REQUEST['limitNbGroupPerUser'] == "ALL")
        {
            $sqlLimitNbGroupPerUser = "NULL";
        }
        else
        {
            $limitNbGroupPerUser = (int) $_REQUEST['limitNbGroupPerUser'];

            if ($limitNbGroupPerUser < 1 ) $limitNbGroupPerUser = 1;

            $sqlLimitNbGroupPerUser = "'".$limitNbGroupPerUser."'";
            $nbGroupPerUser         = $limitNbGroupPerUser;
        }

        /**
         * In case of the table is empty (it seems to happen)
         * insert the parameters.
         */
        $self_registration = ($_REQUEST['self_registration']==1?1:0);
        $private           = ($_REQUEST['private']==1?1:0);
        $forum             = ($_REQUEST['forum']==1?1:0);
        $chat              = ($_REQUEST['chat']==1?1:0);
        $wiki              = ($_REQUEST['wiki']==1?1:0);
        
        $sql ="INSERT IGNORE INTO `".$tbl_GroupsProperties."`
               SET id                =  1 ,
                   self_registration = '".$self_registration."',
                   private           = '".$private."',
                   forum             = '1', # always active 
                   chat              = '".$chat."',
                   wiki              = '".$wiki."',
                   document          = '1' , # always active and private.
                  `nbGroupPerUser`   = ".$sqlLimitNbGroupPerUser.""; // DO NOT ADD '' around 
        
        claro_sql_query($sql);

        /*
         * Real update ...
         */

        $sql = "UPDATE `".$tbl_GroupsProperties."`
                SET `self_registration` = '".$self_registration."',
                    `private`           = '".$private."',
                    `forum`             = '1', # always active 
                    `chat`              = '".$chat."',
                    `wiki`              = '".$wiki."',
                    `document`          = '1' , # always active and private.
                    `nbGroupPerUser`    = ".$sqlLimitNbGroupPerUser." # DO NOT ADD '' around 
                WHERE id = 1" ;

        claro_sql_query($sql);

        $message  = $langGroupPropertiesModified;
        event_default('GROUPMANAGING',array ('CONFIG_GROUP' => TRUE));

        $cidReset = TRUE;
        $cidReq   = $_cid;

        include('../inc/claro_init_local.inc.php');

        $isGroupRegAllowed            = $_groupProperties ['registrationAllowed']
                                    && (
                                            !$is_courseTutor
                                            || (
                                                    $is_courseTutor
                                                    &&
                                                    $tutorCanBeSimpleMemberOfOthersGroupsAsStudent
                                                )
                                        );

        $groupPrivate    = $_groupProperties ['private'            ];
        $groupHaveForum  = $_groupProperties ['tools'] ['forum'    ];
        $groupHaveDocs   = $_groupProperties ['tools'] ['document' ];
        $groupHaveWiki   = $_groupProperties ['tools'] ['wiki'     ];
        $groupHaveChat      = $_groupProperties ['tools'] ['chat'     ];

    }    // end if $submit


    /*----------------------
         DELETE ALL GROUPS
      ----------------------*/

    elseif ($_REQUEST['delete'])
    {
        $nbGroupDeleted = deleteAllGroups();

        if ($nbGroupDeleted>0) $message= $langGroupsDeleted;
        else                   $message="!!!! no group deleted";
        event_default('GROUPMANAGING',array ('DELETE_GROUP' => $nbGroupDeleted));

    }

    /*----------------------
         DELETE ONE GROUP
      ----------------------*/

    elseif ($_REQUEST['delete_one'])
    {
        $id = (int) $_REQUEST['id'];
        $nbGroupDeleted = delete_groups($id);

        if     ($nbGroupDeleted==1) $message = $langGroupDel ;
        elseif ($nbGroupDeleted>1)  $message = $nbGroupDeleted." ".$langGroupDel;
        else                        $message = "No group deleted !";
    }

    /*-------------------
       EMPTY ALL GROUPS
      -------------------*/

    elseif ($_REQUEST['empty'])
    {
        $sql = "DELETE FROM `".$tbl_GroupsUsers."`";
        $result  = claro_sql_query($sql);

        $sql = 'UPDATE `'.$tbl_Groups.'` SET tutor="0"';
        $result2 = claro_sql_query($sql);
        event_default('GROUPMANAGING',array ('EMPTY_GROUP' => TRUE));
        $message = $langGroupsEmptied;
    }

    /*-----------------
      FILL ALL GROUPS
      -----------------*/

    elseif ($_REQUEST['fill'])
    {
        fill_in_groups();
        event_default('GROUPMANAGING',array ('FILL_GROUP' => TRUE));

        $message = $langGroupFilledGroups;

    }    // end FILL

} // end if is_allowedToManage


////**************** OUTPUT ************************
/* OUTPUT PANEL IS Divide in  3 Parts
    1° Message box
    2° Admin panel
        Show command to manage groups and course properties about group.
    3° Common panel.
        List existing group and show commands
        aivailable for the current user.
*/

include($includePath."/claro_init_header.inc.php");
//stats
event_access_tool($_tid, $_courseTool['label']);

claro_disp_tool_title($nameTools);
     /*-------------
       MESSAGE BOX
      -------------*/

if($message)
{
    claro_disp_message_box($message);
}

unset($message);

/*==========================
    COURSE ADMIN ONLY
  ==========================*/

if ($display_groupadmin_manager)
{
    /*--------------------
       COMMANDS BUTTONS
      --------------------*/

echo '<p>'."\n"
    .'<a class="claroCmd" href="group_creation.php">'
    .'<img src="'.$imgRepositoryWeb.'group.gif">'
    .$langNewGroupCreate
    .'</a> |'."\n"
    .'&nbsp;'
    .'<a class="claroCmd" href="'.$_SERVER['PHP_SELF'].'?delete=yes" onClick="return confirmationDelete();">'
    .'<img src="'.$imgRepositoryWeb.'delete.gif">'
    .$langDeleteGroups
    .'</a> |'."\n"
    .'&nbsp;'
    .'<a class="claroCmd" href="'.$_SERVER['PHP_SELF'].'?fill=yes"  >'
    .'<img src="'.$imgRepositoryWeb.'fill.gif">'
    .$langFillGroups
    .'</a> |'."\n"
    .'&nbsp;'
    .'<a class="claroCmd" href="'.$_SERVER['PHP_SELF'].'?empty=yes"  onClick="return confirmationEmpty();">'
    .'<img src="'.$imgRepositoryWeb.'sweep.gif">'
    .$langEmtpyGroups
    .'</a> |'."\n"
    .'&nbsp;'
    .'<a class="claroCmd" href="group_properties.php">'
    .'<img src="'.$imgRepositoryWeb.'settings.gif">'
    .'Modify Group Settings'
    .'</a>'."\n"
    .'&nbsp;'
    .'</p>'."\n"
    ;

}    // end course admin only



/**
  VIEW COMMON TO STUDENT & TEACHERS
   - List of existing groups
   - For each, show name, qty of member and qty of place
   - Add link if group is "open" to current user
   - show subscribe button if needed
   - show link to edit and delete if authorised
 */

/*
 * If Group self registration is allowed, previously check if the user
 * is actually registered to the course...
 */

if ($isGroupRegAllowed && isset($_uid))
{
    if ( ! $is_courseMember) $isGroupRegAllowed = FALSE;
}

/*
 * Check in how many groups a user is allowed to register
 */

if ( ! is_null($nbGroupPerUser)) $nbGroupPerUser = (int) $nbGroupPerUser;

if (is_integer($nbGroupPerUser))
{
    $sql              = "SELECT COUNT(`team`) nbGroups
                         FROM `".$tbl_GroupsUsers."` WHERE user='".$_uid."'";

    $countTeamUser    = mysql_fetch_array( mysql_query($sql) );
    $countTeamUser    = $countTeamUser['nbGroups'];

    if($countTeamUser >= $nbGroupPerUser) $isGroupRegAllowed = FALSE;
}


echo '<table class="claroTable emphaseLine" border="0" cellspacing="2" cellpadding="2" width="100%">';

 /*-------------
      HEADINGS
   -------------*/

echo '<tr class="headerX" align="center">'
   . '<th align="left">'
   . '&nbsp; '.$langExistingGroups
   . '</th>'
   ;

if($isGroupRegAllowed && ! $is_allowedToManage) // If self-registration allowed
{
    echo '<th align="left">'
       . $langGroupSelfRegistration
       . '</th>'
       ;
}

echo '<th>'
   . $langRegistered
   . '</th>'
   . '<th>'
   . $langMax
   . '</th>'
   ;

if ($is_allowedToManage) // only for course administrator
{
    echo '<th>'
       . $langEdit
       . '</th>'
       . '<th>'
       . $langDelete
       . '</th>'
       ;
}

echo '</tr><tbody>';

//////////////////////////////////////////////////////////////////////////////

    $sql = "SELECT `g`.`id` id, `g`.`name` name,
                   `g`.`maxStudent`        maxStudent,
                   `g`.`secretDirectory`   secretDirectory,
                   `g`.`tutor`             id_tutor,
                   `g`.`description`       description, 

                    `tutor`.`user_id`, `tutor`.`nom`, `tutor`.`prenom`,
                    `tutor`.`username`, `tutor`.`email`,

                   `ug`.`user` is_member,
                    COUNT(`ug2`.`id`) nbMember,

                   `tutor`.user_id user_id,
                   `tutor`.`nom` nom, `tutor`.`prenom` prenom,
                   `tutor`.`username` username, `tutor`.`email` email

            FROM `".$tbl_Groups."` `g`

          # retrieve the tutor id
            LEFT JOIN  `".$tbl_user."` `tutor`
            ON `tutor`.`user_id` = `g`.`tutor`

          # retrieve the user group(s)
            LEFT JOIN `".$tbl_GroupsUsers."` `ug`
            ON `ug`.`team` = `g`.`id` AND `ug`.`user` = '".$_uid."'

          # count the registered users in each group
            LEFT JOIN `".$tbl_GroupsUsers."` `ug2`
            ON `ug2`.`team` = `g`.`id`

            GROUP BY `g`.`id`
            ORDER BY UPPER(g.name)";

$groupList = mysql_query($sql);

$totalRegistered = 0;

while ($thisGroup = mysql_fetch_array($groupList))
{

    // COLUMN 1 - NAME OF GROUP + If open LINK.

    echo '<tr align="center">'
       . '<td align="left">'
       ;
        /**
         * Note : student are allowed to enter into group only if they are
         * group member.
         * Tutors are allowed to enter in any groups, they
         * are also able to notice whose groups they are responsible
         */
      if(       $is_allowedToManage
             ||   $thisGroup['id_tutor'] == $_uid
             ||   $thisGroup['is_member']
             || ! $_groupProperties['private'])
        {
            echo '<img src="'.$imgRepositoryWeb.'group.gif"> '
               . '<a href="group_space.php?gidReq='.$thisGroup['id'].'">'
               . $thisGroup['name']
               . '</a>'
               ;
            
            if     ($_uid && $_uid == $thisGroup['id_tutor']) echo ' ('.$langOneMyGroups.')';
            elseif ($thisGroup['is_member'])                  echo ' ('.$langMyGroup.')';
        }
        else
        {
            echo '<img src="'.$imgRepositoryWeb.'group.gif"> ' 
               . $thisGroup['name']
               ;
        }

    echo '</td>';


    /*----------------------------
      COLUMN 2 - SELF REGISTRATION
      ----------------------------*/

    if (! $is_allowedToManage)
    {
        if($isGroupRegAllowed)
        {
            echo '<td align="left">';

            if( (! $_uid)
                OR ( $thisGroup['is_member'])
                OR ( $_uid == $thisGroup['id_tutor'])
                OR (($thisGroup['nbMember'] >= $thisGroup['maxStudent'])
                    AND ($thisGroup['maxStudent'] != 0))) // causes to prevent registration
            {
                echo '&nbsp;-';
            }
            else
            {
                echo '&nbsp;'
                   . '<a href="group_space.php?selfReg=1&amp;gidReq='.$thisGroup['id'].'">'
                   . $langGroupSelfRegInf
                   . '</a>'
                   ;
            }
            echo "</td>";
        }    // end If $isGroupRegAllowed
    }


    /*------------------
        MEMBER NUMBER
      ------------------*/

    echo    '<td>'.$thisGroup['nbMember'].'</td>';

    /*------------------
      MAX MEMBER NUMBER
      ------------------*/

    if ($thisGroup['maxStudent'] == 0)   echo "<td> - </td>";
    else                               echo "<td>",$thisGroup['maxStudent'],"</td>";

    if ($is_allowedToManage)
    {
        echo '<td>'
           . '<a href="group_edit.php?gidReq='.$thisGroup['id'].'">'
           . '<img src="'.$imgRepositoryWeb.'edit.gif" border="0" alt="'.$langEdit.'">'
           . '</a>'
           . '</td>'
           . '<td>'
           . '<a href="'.$_SERVER['PHP_SELF'].'?delete_one=yes&amp;id='.$thisGroup['id'].'">'
           . '<img src="'.$imgRepositoryWeb.'delete.gif" border="0" alt="'.$langDelete.'">'
           . '</a>'
           . '</td>'
           ;
    }

    echo '</tr>'."\n";

    if (   ! is_null($thisGroup['description']) 
        && trim($thisGroup['description']) != '' )
    {
        echo '<tr><td colspan="5">'
           . '<div class="comment">'
           . $thisGroup['description']
           . '</div>'
           . '</td></tr>'."\n"
           ;
    }
    

    $totalRegistered = $totalRegistered + $thisGroup['nbMember'];

}    // while loop

echo '</tbody></table>';

//////////////////////////////////////////////////////////////////////////////

if ($is_allowedToManage)
{
    
    // COUNT STUDENTS REGISTERED TO THE COURSE

    $sql              = "SELECT COUNT(user_id) FROM `".$tbl_CoursUsers."`
                         WHERE  code_cours =' ".$currentCourseId."'
                         AND    statut = 5 AND tutor = 0";

    $countUsers       = mysql_fetch_array(mysql_query($sql));
    $countUsers       = $countUsers[0];
    
    // COUNT STUDENTS REGISTERED TO A GROUPS
    
    $sql              = "SELECT COUNT(user) FROM `".$tbl_GroupsUsers."`GROUP by user";
    $usersWithGroups  =  mysql_fetch_array(mysql_query($sql));
    $usersWithGroups  =  $usersWithGroups[0];
    
    // COUNT STUDENTS UNREGISTERED TO A GROUP
    
    $countNoGroup     =  $countUsers - $usersWithGroups;
    
    // COUNT ALL REGISTERED USER AND GROUP BY STATUS

    unset($byStatus);
    unset($tutors  );
    unset($nbUser  );
    
    $sql = "SELECT COUNT(user_id) nbUser, statut, tutor
            FROM `".$tbl_CoursUsers."`
            WHERE code_cours='".$currentCourseId."'
            GROUP BY statut, tutor;";

    $coursUsersSelect = mysql_query($sql);
    
    while ($counts = mysql_fetch_array($coursUsersSelect))
    {
        $byStatus [$counts['statut']] += $counts['nbUser'];
        $tutors   [$counts['tutor'] ] += $counts['nbUser'];
        $nbUser                       += $counts['nbUser'];
    }
    
    if (!$multiGroupAllowed) // All this have to be rewriten for $multiGroupAllowed all counts are wrong
    {
    ?>

    <hr noshade size="1">

    
    <table align="center" border="0" cellspacing="0" width="100%" cellpadding="6">
    
    <tr>
    <td align="right"><b><?php echo $nbUser ?></b></td>
    <td></td>
    <td></td>
    <td><?php echo $langSubscribed ?><br></td>
    </tr>
    
    <tr>
    <td colspan="4"><hr></td>
    </tr>
    
    <tr>
    <td></td>
    <td align="right"><b><?php echo $byStatus[1] ?></b></td>
    <td></td>
    <td><?php echo $langAdminsOfThisCours ?></td>
    </tr>

    <?php
        if    ($tutors[1] > 0)
        {
            echo '<tr>'
               . '<td></td>'
               . '<td align="right">'
               . '<b>'.$tutors[1].'</b>'
               . '</td>'
               . '<td></td>'
               .' <td>'
               ;
        
            if ($tutors[1] == 1) echo $langGroupTutor;  // singular form
            else                 echo $langGroupTutors; // plural form
        
            echo '<td>'
               . '</tr>'
               ;
        
        } // end if $tutor[1] > 0
    ?>

        <tr>
        <td></td>
        <td align="right"><b><?php echo $countUsers ?></b></td>
        <td></td>
        <td>
        <?php echo $langGroupStudentsRegistered ?><small>(<?php echo $langGroupUsersList ?>)</small>.
        </td>
        </tr>

        <tr>
        <td colspan="4"><hr></td>
        </tr>

        <tr>
        <td></td>
        <td></td>
        <td align="right"><b><?php echo $totalRegistered ?></b></td>
        <td><?php echo $langGroupStudentsInGroup ?></td>
        </tr>

        <tr>
        <td></td>
        <td></td>
        <td align="right"><b><?php echo $countNoGroup ?></b></td>
        <td><?php echo $langGroupNoGroup ?></td>
        </tr>
        </table>
    <?php
    }         // end if ! $multiGroupAllowed
}             // end if $is_allowedToManage

include($includePath.'/claro_init_footer.inc.php');

?>