<?php // $Id$
/**
 * CLAROLINE
 *
 * This is the groups page
 * This page list existing groups in course.
 * If allowed to enter, a link is under the group name
 * user can subscribe to a group if
 *  - user is member of the course
 *  - auto subscribe is aivailable
 *  - user don't hev hit the max group per user
 *  - the group is not full
 * Course Admin have more tools.
 *  - Create groups
 *  - Edit groups
 *  - Fill groups
 *  - empty groups
 *  - remove (all) groups
 * complete listing of  groups member is not aivailable. the  unsorted info is in user tool
 *
 * @version 1.7 $Revision$
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

//**************** INITIALISATION************************

$tlabelReq = 'CLGRP___';
DEFINE('DISP_GROUP_LIST', __LINE__);
DEFINE('DISP_GROUP_SELECT_FOR_ACTION', __LINE__);

require '../inc/claro_init_global.inc.php';
if ( ! $_cid || ! $is_courseAllowed ) claro_disp_auth_form(true);
include_once $includePath . '/lib/group.lib.inc.php' ;
include_once $includePath . '/lib/pager.lib.php';
//stats
event_access_tool($_tid, $_courseTool['label']);

// use viewMode
claro_set_display_mode_available(TRUE);


$display = DISP_GROUP_LIST;
$nameTools = get_lang('Groups');



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

$currentCourseRepository = $_course['path'];
$currentCourseId         = $_course['sysCode'];
$is_allowedToManage      = claro_is_allowed_to_edit();

$isGroupRegAllowed       =     $_groupProperties ['registrationAllowed']
                           && (  !$is_courseTutor
                               || (  $is_courseTutor
                                   && get_conf('tutorCanBeSimpleMemberOfOthersGroupsAsStudent')
                         )
                               );
// Warning $groupRegAllowed is not valable before check of groupPerUserQuota

$groupPrivate   = $_groupProperties ['private'];
$nbGroupPerUser = $_groupProperties ['nbGroupPerUser'];

if ( ! $nbGroupPerUser )
{
    $sql = "SELECT COUNT(*)
            FROM `" . $tbl_Groups . "`";
    $nbGroupPerUser = claro_sql_query_get_single_value($sql);
}

$tools['forum'   ] = $_groupProperties['tools']['forum'    ];
$tools['document'] = $_groupProperties['tools']['document' ];
$tools['wiki'    ] = $_groupProperties['tools']['wiki'     ];
$tools['chat'    ] = $_groupProperties['tools']['chat'     ];

//// **************** ACTIONS ***********************

$display_groupadmin_manager = (bool) $is_allowedToManage;

// ACTIONS

if ( $is_allowedToManage )
{
    if ( isset($_REQUEST['cmd']) ) $cmd = $_REQUEST['cmd'];
    else                           $cmd = null;

    if ( $cmd == 'exMkGroup')
    {
        $noQUERY_STRING = true;
        // require the forum library to create the related forums
        require_once $includePath . '/lib/forum.lib.php';

        // For all Group forums, cat_id=1

        if ( isset($_REQUEST['group_max'])
           && ctype_digit($_REQUEST['group_max'])
           && (trim($_REQUEST['group_max']) != '') )
        {
            $groupMax = (int) $_REQUEST['group_max'];
        }
        else
        {
            $groupMax = NULL;
        }

        $groupQuantity = (int) $_REQUEST['group_quantity'];

        if ( $groupQuantity < 1 ) $groupQuantity = 1;

        $sql = 'SELECT MAX(id)
                FROM `' . $tbl_Groups . '`';

        $startNum = claro_sql_query_get_single_value($sql);

        $groupCreatedList = array();

        for ( $i = 1, $groupNum = $startNum + 1 ; $i <= $groupQuantity; $i++, $groupNum++ )
        {
            $groupId = create_group(get_lang('Group').' '.$groupNum, $groupMax);
            $groupCreatedList[] = $groupId;
        }

        $message= count($groupCreatedList) . ' ' . get_lang('GroupsAdded');

        event_default( 'GROUPMANAGING' , array ('CREATE_GROUP' => $groupQuantity) );

    }    // end if $submit

    if ($cmd == 'rqMkGroup')
    {
        $message = '<b>' . get_lang('NewGroupCreate') . '</b>'


        .          '<form method="post" action="group.php">'                         ."\n"
        .          '<input type="hidden" name="claroFormId" value="'.uniqid('').'">' ."\n"
        .          '<input type="hidden" name="cmd" value="exMkGroup">'

        .          '<table>'                                                         ."\n"

        .          '<tr valign="top">'
        .          '<td>'
        .          '<label for="group_quantity">' . get_lang('Create') . '</label>'
        .          '</td>'
        .          '<td>'
        .          '<input type="text" name="group_quantity" id="group_quantity" size="3" value="1">'
        .          '<label for="group_quantity">' . get_lang('NewGroups') . '</label>'
        .          '</td>'                                                           ."\n"
        .          '</tr>'                                                           ."\n"

        .          '<tr valign="top">'                                               ."\n"
        .          '<td>'                                                            ."\n"
        .          '<label for="group_max">' . get_lang('Max') . '</label>'
        .          '</td>'                                                           ."\n"
        .          '<td>'                                                            ."\n"
        .          '<input type="text" name="group_max" id="group_max" size="3" value="8">'
        .          get_lang('Places')
        .          '</td>'                                                           ."\n"
        .          '</tr>'                                                           ."\n"

        .          '<tr>'                                                            ."\n"
        .          '<td>'                                                            ."\n"
        .          '<label for="creation">'
        .          get_lang('Create')
        .          '</label>'
        .          '</td>'                                                           ."\n"
        .          '<td>'                                                            ."\n"
        .          '<input type="submit" value="'.get_lang('Ok').'" name="creation" id="creation"> '
        .          claro_disp_button($_SERVER['HTTP_REFERER'], get_lang('Cancel'))
        .          '</td>'                                                           ."\n"
        .          '</tr>'                                                           ."\n"

        .          '</table>'                                                        ."\n"
        .          '</form>'                                                         ."\n"
        ;
    }

    if ( $cmd == 'exDelGroup')
    {
            /*----------------------
                DELETE ALL GROUPS
              ----------------------*/

        if ($_REQUEST['id'] == 'ALL')
        {
            $nbGroupDeleted = deleteAllGroups();

            if ($nbGroupDeleted > 0) $message = get_lang('GroupsDeleted');
            else                     $message = get_lang('NoGroupsDeleted');
            event_default('GROUPMANAGING',array ('DELETE_GROUP' => $nbGroupDeleted));

        }
        elseif(0 < (int)$_REQUEST['id'])
        {
            /*----------------------
                DELETE ONE GROUP
             ----------------------*/

            $nbGroupDeleted = delete_groups( (int) $_REQUEST['id']);

            if     ( $nbGroupDeleted == 1 ) $message = get_lang('GroupDel') ;
            elseif ( $nbGroupDeleted >  1 ) $message = $nbGroupDeleted . ' ' . get_lang('GroupDel');
            else                            $message = get_lang('NoGroupsDeleted') . ' !';
        }
        $cidReset = TRUE;
        $cidReq   = $_cid;

        include('../inc/claro_init_local.inc.php');
        $noQUERY_STRING = true;
    }

    /*-------------------
       EMPTY ALL GROUPS
      -------------------*/

    elseif ( $cmd == 'exEmptyGroup' )
    {

        if (empty_group())
        {
            event_default('GROUPMANAGING',array ('EMPTY_GROUP' => TRUE));
            $message = get_lang('GroupsEmptied');
        }
        else
        {
            echo claro_failure::get_last_failure();
            $message = get_lang('GroupsNotEmptied');
        }

    }

    /*-----------------
      FILL ALL GROUPS
      -----------------*/

    elseif ( $cmd == 'exFillGroup' )
    {
        fill_in_groups();
        event_default('GROUPMANAGING',array ('FILL_GROUP' => TRUE));

        $message = get_lang('GroupFilledGroups');

    }    // end FILL

    /**
     * GROUP PROPERTIES
     */

    // This is called by the form in group_properties.php
    // set common properties for all groups

    if ( isset($_REQUEST['properties']) )
    {
        if ( $_REQUEST['limitNbGroupPerUser'] == 'ALL')
        {
            $sqlLimitNbGroupPerUser = 'NULL';
        }
        else
        {
            $limitNbGroupPerUser = (int) $_REQUEST['limitNbGroupPerUser'];

            if ( $limitNbGroupPerUser < 1 ) $limitNbGroupPerUser = 1;

            $sqlLimitNbGroupPerUser = "'" . $limitNbGroupPerUser . "'";
            $nbGroupPerUser         = $limitNbGroupPerUser;
        }

        /**
         * In case of the table is empty (it seems to happen)
         * insert the parameters.
         */

        if ( isset($_REQUEST['self_registration']) ) $self_registration = (int) $_REQUEST['self_registration'];
        else                                         $self_registration = 0;

        if ( isset($_REQUEST['private']) ) $private = (int) $_REQUEST['private'];
        else                               $private = 0;

//        if ( isset($_REQUEST['forum']) ) $forum = (int) $_REQUEST['forum'];
//        else                             $forum = 0;

        if ( isset($_REQUEST['chat']) ) $chat = (int) $_REQUEST['chat'];
        else                            $chat = 0;

        if ( isset($_REQUEST['wiki']) ) $wiki = (int) $_REQUEST['wiki'];
        else                            $wiki = 0;

        $sql = "INSERT IGNORE INTO `" . $tbl_GroupsProperties . "`
               SET id                =  1 ,
                   self_registration = '" . $self_registration . "',
                   private           = '" . $private . "',
                   forum             = '1', # always active
                   chat              = '" . $chat . "',
                   wiki              = '" . $wiki . "',
                   document          = '1' , # always active and private.
                  `nbGroupPerUser`   = " . $sqlLimitNbGroupPerUser . ""; // DO NOT ADD '' around

        claro_sql_query($sql);

        /*
         * Real update ...
         */

        $sql = "UPDATE `".$tbl_GroupsProperties."`
                SET `self_registration` = '" . $self_registration . "',
                    `private`           = '" . $private . "',
                    `forum`             = '1', # always active
                    `chat`              = '" . $chat . "',
                    `wiki`              = '" . $wiki ."',
                    `document`          = '1' , # always active and private.
                    `nbGroupPerUser`    = " . $sqlLimitNbGroupPerUser . " # DO NOT ADD '' around
                WHERE id = 1" ;

        claro_sql_query($sql);

        $message  = get_lang('GroupPropertiesModified');
        event_default('GROUPMANAGING',array ('CONFIG_GROUP' => TRUE));

        $cidReset = TRUE;
        $cidReq   = $_cid;

        include $includePath . '/claro_init_local.inc.php';

        $isGroupRegAllowed = $_groupProperties['registrationAllowed']
                             && (
                                  !$is_courseTutor
                                  || (
                                       $is_courseTutor
                                       &&
                                       get_conf('tutorCanBeSimpleMemberOfOthersGroupsAsStudent')
                                       )
                                );

        $groupPrivate    = $_groupProperties['private'           ];
        $groupHaveForum  = $_groupProperties['tools']['forum'    ];
        $groupHaveDocs   = $_groupProperties['tools']['document' ];
        $groupHaveWiki   = $_groupProperties['tools']['wiki'     ];
        $groupHaveChat   = $_groupProperties['tools']['chat'     ];

    }    // end if $submit
} // end if is_allowedToManage


////**************** OUTPUT ************************

if ($display == DISP_GROUP_LIST)
{

    $sql = "SELECT `g`.`id`              AS id,
                   `g`.`name`            AS name,
                   `g`.`maxStudent`      AS maxStudent,
                   `g`.`secretDirectory` AS secretDirectory,
                   `g`.`tutor`           AS id_tutor,
                   `g`.`description`     AS description,

                   `ug`.`user`        AS is_member
                    ,COUNT(`ug2`.`id`) AS nbMember

          FROM `" . $tbl_Groups . "` `g`

          # retrieve the tutor id
          LEFT JOIN  `" . $tbl_user . "` AS `tutor`
          ON `tutor`.`user_id` = `g`.`tutor`

          # retrieve the user group(s)
          LEFT JOIN `" . $tbl_GroupsUsers . "` AS `ug`
          ON `ug`.`team` = `g`.`id` AND `ug`.`user` = " . (int) $_uid . "

          # count the registered users in each group
          LEFT JOIN `" . $tbl_GroupsUsers . "` `ug2`
          ON `ug2`.`team` = `g`.`id`

          GROUP BY `g`.`id`";

    $groupPager = new claro_sql_pager($sql);

    $sortKey = isset($_GET['sort']) ? $_GET['sort'] : 'name';
    $sortDir = isset($_GET['dir' ]) ? $_GET['dir' ] : SORT_ASC;

    $groupPager->add_sort_key($sortKey, $sortDir);

    $groupList = $groupPager->get_result_list($sql);




    $htmlHeadXtra[] =
    '<script type="text/javascript">

    function confirmationEmpty ()
    {
        if (confirm(\'' . clean_str_for_javascript(get_lang('ConfirmEmptyGroups'))  . '\'))
        {
            return true;
        }
        else
        {
            return false;
        }
    };

    function confirmationDelete ()
    {
        if (confirm(\'' . clean_str_for_javascript(get_lang('ConfirmDeleteGroups')) . '\'))
        {
            return true;
        }
        else
        {
            return false;
        }
    };

    function confirmationDeleteThisGroup (name)
    {
        if (confirm(\'' . clean_str_for_javascript(get_lang('ConfirmDeleteThisGroup')) . ' \\n\' + name ))
        {
            return true;
        }
        else
        {
            return false;
        }
    };

    function confirmationFill ()
    {
        if (confirm(\'' . clean_str_for_javascript(get_lang('FillGroups')) . '\'))
        {
            return true;
        }
        else
        {
            return false;
        }
    };

    </script>'."\n";
}

$htmlHeadXtra[] =
'<style type=text/css>
<!--
.comment { margin-left: 30px}
-->
</style>'."\n";

include $includePath . '/claro_init_header.inc.php';

echo claro_disp_tool_title($nameTools);

/*-------------
  MESSAGE BOX
 -------------*/

if ( !empty($message) )
{
    echo claro_disp_message_box($message);
}

/*==========================
    COURSE ADMIN ONLY
  ==========================*/

if ( $display_groupadmin_manager )
{
    /*--------------------
       COMMANDS BUTTONS
      --------------------*/

    echo '<p>' . "\n"

    // Create new groups
    .    '<a class="claroCmd" href="'.$_SERVER['PHP_SELF'].'?cmd=rqMkGroup">'
    .    '<img src="' . $imgRepositoryWeb . 'group.gif" alt="" />'
    .    get_lang('NewGroupCreate')
    .    '</a> |'
    .    '&nbsp;' . "\n"

    // Delete all groups
    .    '<a class="claroCmd" href="' . $_SERVER['PHP_SELF'] . '?cmd=exDelGroup&id=ALL" onClick="return confirmationDelete();">'
    .    '<img src="' . $imgRepositoryWeb . 'delete.gif" alt="" />'
    .    get_lang('DeleteGroups')
    .    '</a> |'
    .    '&nbsp;' . "\n"
    // Fill groups
    .    '<a class="claroCmd" href="' . $_SERVER['PHP_SELF'] . '?cmd=exFillGroup" onClick="return confirmationFill();">'
    .    '<img src="' . $imgRepositoryWeb . 'fill.gif" alt="" />'
    .    get_lang('FillGroups')
    .    '</a> |'
    .    '&nbsp;' . "\n"

    // Empty all groups
    .    '<a class="claroCmd" href="' . $_SERVER['PHP_SELF'] . '?cmd=exEmptyGroup"  onClick="return confirmationEmpty();">'
    .    '<img src="' . $imgRepositoryWeb . 'sweep.gif" alt="" />'
    .    get_lang('EmtpyGroups')
    .    '</a> |'
    .    '&nbsp;' . "\n"

    // Main group settings
    .    '<a class="claroCmd" href="group_properties.php">'
    .    '<img src="' . $imgRepositoryWeb . 'settings.gif" alt="" />'
    .    get_lang('MainGroupSettings')
    .    '</a>'
    .    '&nbsp;' . "\n"
    .    '</p>' . "\n"
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

if ( $isGroupRegAllowed && isset($_uid) )
{
    if ( ! $is_courseMember) $isGroupRegAllowed = FALSE;
}

/*
 * Check in how many groups a user is allowed to register
 */

if ( ! is_null($nbGroupPerUser) ) $nbGroupPerUser = (int) $nbGroupPerUser;

if ( is_integer($nbGroupPerUser) )
{
    $countTeamUser = group_count_group_of_a_user($_uid);
    if ( $countTeamUser >= $nbGroupPerUser ) $isGroupRegAllowed = FALSE;
}

echo $groupPager->disp_pager_tool_bar($_SERVER['PHP_SELF']);

echo                                                         "\n"
.    '<table class="claroTable emphaseLine" width="100%">' . "\n";

 /*-------------
      HEADINGS
   -------------*/

$sortUrlList = $groupPager->get_sort_url_list($_SERVER['PHP_SELF']);

echo '<tr class="headerX" align="center">' . "\n"
.    '<th align="left">&nbsp;<a href="'.$sortUrlList['name'].'">' . get_lang('ExistingGroups') . '</a></th>' . "\n"
;

if($isGroupRegAllowed && ! $is_allowedToManage) // If self-registration allowed
{
    echo '<th align="left">' . get_lang('GroupSelfRegistration') . '</th>' . "\n"  ;
}

echo '<th><a href="'.$sortUrlList['nbMember'].'">' . get_lang('Registered') . '</a></th>' . "\n"
.    '<th><a href="'.$sortUrlList['maxStudent'].'">' . get_lang('Max') . '</a></th>' . "\n"
;

if ( $is_allowedToManage ) // only for course administrator
{
    echo '<th>' . get_lang('Edit') . '</th>' . "\n"
    .    '<th>' . get_lang('Delete') . '</th>' . "\n"
    ;
}

echo '</tr>' . "\n"
.    '<tbody>' . "\n"
;

//////////////////////////////////////////////////////////////////////////////
$totalRegistered = 0;
// get group id where new events have been recorded since last login of the user

if (isset($_uid))
{
    $date = $claro_notifier->get_notification_date($_uid);
    $modified_groups = $claro_notifier->get_notified_groups($_cid, $date);
}
else $modified_groups = array();

 /*-------------
      DISPLAY
   -------------*/

foreach ($groupList as $thisGroup)
{
    // COLUMN 1 - NAME OF GROUP + If open LINK.

    echo '<tr align="center">' . "\n"
    .    '<td align="left">'
    ;
        /**
         * Note : student are allowed to enter into group only if they are
         * group member.
         * Tutors are allowed to enter in any groups, they
         * are also able to notice whose groups they are responsible
         */
    if( $is_allowedToManage
        ||   $thisGroup['id_tutor'] == $_uid
        ||   $thisGroup['is_member']
        || ! $_groupProperties['private'])
    {
        // see if group name must be displayed as "containing new item" or not

        if (in_array($thisGroup['id'], $modified_groups))
        {
            $classItem = '<div class="item hot">';
        }
        else // otherwise just display its name normally
        {
           $classItem = '<div class="item">';
        }

        echo $classItem . '<img src="' . $imgRepositoryWeb . 'group.gif" alt="" /> '
        .    '<a href="group_space.php?gidReq=' . $thisGroup['id'] . '">'
        .    $thisGroup['name']
        .    '</a>'
        .    '</div>'
        ;

        if     ($_uid && $_uid == $thisGroup['id_tutor']) echo ' (' . get_lang('OneMyGroups') . ')';
        elseif ($thisGroup['is_member'])                  echo ' (' . get_lang('MyGroup') . ')';
    }
    else
    {
        echo '<img src="' . $imgRepositoryWeb . 'group.gif" alt="" /> '
        .    $thisGroup['name']
        ;
    }

    echo '</td>' . "\n";

    /*----------------------------
      COLUMN 2 - SELF REGISTRATION
      ----------------------------*/

    if (! $is_allowedToManage)
    {
        if($isGroupRegAllowed)
        {
            echo '<td align="center">';

            if( (! $_uid)
                OR ( $thisGroup['is_member'])
                OR ( $_uid == $thisGroup['id_tutor'])
                OR (!is_null($thisGroup['maxStudent']) //unlimited
                    AND ($thisGroup['nbMember'] >= $thisGroup['maxStudent']) // still free place
                    ))
            {
                echo '&nbsp;-';
            }
            else
            {
                echo '&nbsp;'
                .    '<a href="group_space.php?selfReg=1&amp;gidReq=' . $thisGroup['id'] . '">'
                .    '<img src="' . $imgRepositoryWeb . 'enroll.gif" alt="' . get_lang('GroupSelfRegInf') . '">'
                .    '</a>'
                ;
            }
            echo '</td>' . "\n";
        }    // end If $isGroupRegAllowed
    }

    /*------------------
        MEMBER NUMBER
      ------------------*/

    echo    '<td>' . $thisGroup['nbMember'] . '</td>' . "\n";

    /*------------------
      MAX MEMBER NUMBER
      ------------------*/

    if (is_null($thisGroup['maxStudent'])) echo '<td> - </td>' . "\n";
    else                                   echo '<td>' . $thisGroup['maxStudent'] . '</td>' . "\n";

    if ($is_allowedToManage)
    {
        echo '<td>'
        .    '<a href="group_edit.php?gidReq=' . $thisGroup['id'] . '">'
        .    '<img src="' . $imgRepositoryWeb . 'edit.gif" border="0" alt="' . get_lang('Edit') . '">'
        .    '</a>'
        .    '</td>' . "\n"
        .    '<td>'
        .    '<a href="' . $_SERVER['PHP_SELF'] . '?cmd=exDelGroup&amp;id=' . $thisGroup['id'] . '" '
        .    ' onClick="return confirmationDeleteThisGroup(\'' . clean_str_for_javascript($thisGroup['name']) . '\');">'
        .    '<img src="' . $imgRepositoryWeb . 'delete.gif" border="0" alt="' . get_lang('Delete') . '">'
        .    '</a>'
        .    '</td>' . "\n"
        ;
    }

    echo '</tr>' . "\n\n";

    if (   ! is_null($thisGroup['description'])
        && trim($thisGroup['description']) != '' )
    {
        echo '<tr>' . "\n"
        .    '<td colspan="5">' . "\n"
        .    '<div class="comment">'
        .    $thisGroup['description']
        .    '</div>'
        .    '</td>' . "\n"
        .    '</tr>' . "\n"
        ;
    }


    $totalRegistered = $totalRegistered + $thisGroup['nbMember'];

}    // while loop

echo '</tbody>' . "\n"
.     '</table>' . "\n"
;


include $includePath . '/claro_init_footer.inc.php';

?>
