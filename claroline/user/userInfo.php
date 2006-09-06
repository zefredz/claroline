<?php // $Id$
/**
 *
 * CLAROLINE
 *
 * mangage personal user info in a course.
 *
 * @version 1.8 $Revision$
 *
 * @copyright 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @see http://www.claroline.net/CLUSR/
 *
 * @package CLUSR
 *
 * @author Claro Team <cvs@claroline.net>
 *
 */

$tlabelReq = 'CLUSR';
$gidReset = true;

$descSizeToPrupose = array(3,5,10,15,20); // size in lines for desc - don't add 1

require '../inc/claro_init_global.inc.php';

require_once $includePath . '/lib/admin.lib.inc.php' ;
require_once $includePath . '/lib/user.lib.php';
require_once $includePath . '/lib/course_user.lib.php';
require_once $includePath . '/lib/user_info.lib.php';

$interbredcrump[]= array ('url' => 'user.php', 'name' => get_lang('Users'));

$nameTools = get_lang('User');

/** OUTPUT **/
claro_set_display_mode_available(TRUE);

if ( !$_cid || ! $is_courseAllowed ) claro_disp_auth_form();

/*
* data  found  in settings  are :
*    $uid
*    $isAdmin
*    $isAdminOfCourse
*
*/

if (isset($_REQUEST['uInfo'])) $userIdViewed = (int) $_REQUEST['uInfo']; // Id of the user we want to view coming from the user.php
else $userIdViewed = 0;

/*--------------------------------------------------------
Connection API between Claroline and the current script
--------------------------------------------------------*/

$course_id               = $_course['sysCode'];
$tbl_mdb_names           = claro_sql_get_main_tbl();
$tbl_crs_names           = claro_sql_get_course_tbl();
$tbl_rel_course_user     = $tbl_mdb_names['rel_course_user'    ];
$tbl_group_rel_team_user = $tbl_crs_names['group_rel_team_user'];
$TBL_USERINFO_CONTENT    = $tbl_crs_names['userinfo_content'];


$userIdViewer = $_uid; // id fo the user currently online
//$userIdViewed = $_GET['userIdViewed']; // Id of the user we want to view

$allowedToEditContent     = ($userIdViewer == $userIdViewed) || claro_is_allowed_to_edit();
$allowedToEditDef         = claro_is_allowed_to_edit();
$is_allowedToTrack        =  claro_is_allowed_to_edit() && get_conf('is_trackingEnabled')
|| ($userIdViewer == $userIdViewed );

if ( ! claro_is_allowed_to_edit() && ! get_conf('linkToUserInfo') )
{
    claro_die(get_lang('Not allowed'));
}

// clean field submited by the user
if ($_POST)
{
    foreach($_POST as $key => $value)
    {
        $$key = replace_dangerous_char($value);
    }
}

/*======================================
COMMANDS SECTION
======================================*/

$displayMode = "viewContentList";
$dialogBox = '';

$cmd = isset($_REQUEST['cmd'])?$_REQUEST['cmd']:null;

if ($allowedToEditDef)
{
    if (isset($_REQUEST['submitDef']) && $_REQUEST['submitDef'])
    {
        if (isset($_REQUEST['id']) && $_REQUEST['id']!="")
        {
            claro_user_info_edit_cat_def($_REQUEST['id'], $_REQUEST['title'], $_REQUEST['comment'], $_REQUEST['nbline']);
        }
        else
        {
            claro_user_info_create_cat_def($_REQUEST['title'], $_REQUEST['comment'], $_REQUEST['nbline']);
        }

        $displayMode = "viewDefList";
    }
    elseif (isset($_REQUEST['removeDef']) && $_REQUEST['removeDef'])
    {
        claro_user_info_remove_cat_def($_REQUEST['removeDef'], true);
        $displayMode = "viewDefList";
    }
    elseif (isset($_REQUEST['editDef']) && $_REQUEST['editDef'])
    {
        $displayMode = "viewDefEdit";
    }
    elseif (isset($_REQUEST['addDef']))
    {
        $displayMode = "viewDefEdit";
    }
    elseif (isset($_REQUEST['moveUpDef']))
    {
        claro_user_info_move_cat_rank($_REQUEST['moveUpDef'], "up");
        $displayMode = "viewDefList";
    }
    elseif (isset($_REQUEST['moveDownDef']))
    {
        claro_user_info_move_cat_rank($_REQUEST['moveDownDef'], "down");
        $displayMode = "viewDefList";
    }
    elseif(isset($_REQUEST['viewDefList']))
    {
        $displayMode = "viewDefList";
    }
    elseif (isset($_REQUEST['editMainUserInfo']))
    {
        $userIdViewed = (int) $_REQUEST['editMainUserInfo'];
        $displayMode = "viewMainInfoEdit";
    }
    elseif ( $cmd == 'exUpdateCourseUserProperties' )
    {
        $userIdViewed = $_REQUEST['submitMainUserInfo'];

        // Set variable for course manager or student status

        if ( !empty($_REQUEST['profileId']) && $userIdViewed != $_uid )
        {
            $userProperties['profileId'] = $_REQUEST['profileId'];
        }

        // Set variable for tutor setting

        if (isset($_REQUEST['isTutor']))
        {
            // check first the user isn't registered to a group yet

            $sql = "SELECT COUNT(user)
                    FROM `".$tbl_group_rel_team_user."`
                    WHERE user = ".(int) $userIdViewed;

            if ( 0 == claro_sql_query_get_single_value($sql) )
            {
                $userProperties['tutor' ] = 1;
            }
            else
            {
                $userProperties['tutor' ] = 0;
                $dialogBox .= get_lang('Impossible to promote group tutor a student already register to group');
            }
        }
        else
        {
            $userProperties['tutor' ] = 0;
        }

        //set variable for role setting

        $userProperties['role'] =  $_REQUEST['role'];

        // apply changes in DB

        user_set_course_properties($userIdViewed, $course_id, $userProperties);
        $displayMode = "viewContentList";
    }
}

// COMMON COMMANDS

if ($allowedToEditContent)
{
    if (isset($_REQUEST['submitContent']))
    {
        if ($cntId)    // submit a content change
        {
            claro_user_info_edit_cat_content($_REQUEST['catId'], $userIdViewed, $_REQUEST['content'], $_SERVER['REMOTE_ADDR']);
        }
        else        // submit a totally new content
        {
            claro_user_info_fill_new_cat_content($_REQUEST['catId'], $userIdViewed, $_REQUEST['content'], $_SERVER['REMOTE_ADDR']);
        }

        $displayMode = "viewContentList";
    }
    elseif (isset($_REQUEST['editContent']))
    {
        $displayMode = "viewContentEdit";
    }
}

//////////////////////////////////////////////////////////////////////////////
// OUTPUT
//////////////////////////////
if( $displayMode != "viewContentList" ) claro_set_display_mode_available(false);

event_access_tool($_tid, $_courseTool['label']);

include $includePath . '/claro_init_header.inc.php';

echo claro_html_tool_title($nameTools);

/*======================================
DISPLAY MODES
======================================*/

// Back button for each display mode (Top)
echo '<p><small><a href="user.php">&lt;&lt;&nbsp;'.get_lang('Back to user list').'</a></small></p>' . "\n";

// Display Forms or dialog box (if needed)

if(isset($dialogBox) && $dialogBox!="")
{
    echo claro_html_message_box($dialogBox);
}

if ($displayMode == "viewDefEdit")
{
    /*>>>>>>>>>>>> CATEGORIES DEFINITIONS : EDIT <<<<<<<<<<<<*/

    if (isset($_REQUEST['editDef'])) $catToEdit = claro_user_info_get_cat_def($_REQUEST['editDef']);
    else
    {
        $catToEdit = array();
        $catToEdit['title'] = "";
        $catToEdit['comment'] = "";
        $catToEdit['nbline'] = 1;
        $catToEdit['id'] = "";
    }
?>

<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?uInfo=<?php echo $userIdViewed; ?>">
<input type="hidden" name="claroFormId" value="<?php echo uniqid(''); ?>" />
<input type="hidden" name="id" value="<?php echo $catToEdit['id']?>" />
<table>
<tr>
<td>
<label for="title" ><?php echo get_lang('Heading')?></label> :
</td>
<td>
<input type="text" name="title" id="title" size="80" maxlength="80" value ="<?php echo htmlspecialchars($catToEdit['title']); ?>" />
</td>
</tr>

<tr>
<td>
<label for="comment" ><?php echo get_lang('Comment')?></label> :
</td>
<td>
<textarea name="comment" id="comment" cols="60" rows="3" wrap="virtual"><?php echo $catToEdit['comment']?></textarea>
</td>
</tr>

<tr>
<td nowrap>
<label for="nbline" ><?php echo get_lang('Line Number')?></label> :
</td>
<td>
<select name="nbline" id="nbline">
<?php
if ($catToEdit['nbline'] && $catToEdit['nbline']!=1)
{ ?>
    <option value="<?php echo $catToEdit['nbline']?>" selected><?php echo $catToEdit['nbline']?> <?php echo get_lang('line(s)')?></option>
    <option>---</option>
<?php
}
sort($descSizeToPrupose);
?>
<option value="1">1 <?php echo get_lang('line'); ?></option>
<?php
foreach($descSizeToPrupose as $nblines)
{
    echo '<option value="'.$nblines.'">'.$nblines.' '.get_lang('lines').'</option>';
}

?>
</select>
</td>
<tr>
<td>&nbsp;</td>
<td align="center"><input type="submit" name="submitDef" value="<?php echo get_lang('Ok')?>" /></td>
</tr>
</table>
</form>

<?php
}
elseif ($displayMode == "viewDefList")
{
    /*>>>>>>>>>>>> CATEGORIES DEFINITIONS : LIST <<<<<<<<<<<<*/

    $catList = claro_user_info_claro_user_info_get_cat_def_list();

    if ($catList)
    {

        foreach ($catList as $thisCat)
        {
            // displays Title and comments

            echo    '<p>' . "\n"
            .    '<b>'.htmlize($thisCat['title']).'</b><br />' . "\n"
            .    '<i>'.htmlize($thisCat['comment']).'</i>' . "\n"
            .    '</p>' . "\n";

            // displays lines

            echo    '<blockquote>' . "\n"
            .    '<font color="gray">' . "\n";

            for ($i=1;$i<=$thisCat['nbline'];$i++ )
            {
                echo '<br />__________________________________________' . "\n";
            }

            echo    '</font>' . "\n"
            .    '</blockquote>' . "\n";

            // displays commands

            echo     '<a href="'.$_SERVER['PHP_SELF'].'?removeDef='.$thisCat['catId'].'">'
            .    '<img src="'.$imgRepositoryWeb.'delete.gif" border="0" alt="'.get_lang('Delete').'">'
            .    '</a>' . "\n"
            .    '<a href="'.$_SERVER['PHP_SELF'].'?editDef='.$thisCat['catId'].'">'
            .    '<img src="'.$imgRepositoryWeb.'edit.gif" border="0" alt="'.get_lang('Edit').'">'
            .    '</a>' . "\n"
            .    '<a href="'.$_SERVER['PHP_SELF'].'?moveUpDef='.$thisCat['catId'].'">'
            .    '<img src="'.$imgRepositoryWeb.'up.gif" border="0" alt="'.get_lang('Move up').'">'
            .    '</a>' . "\n"
            .    '<a href="'.$_SERVER['PHP_SELF'].'?moveDownDef='.$thisCat['catId'].'">'
            .    '<img src="'.$imgRepositoryWeb.'down.gif" border="0" alt="'.get_lang('Move down').'">'
            .    '</a>' . "\n";
        } // end for each

    } // end if ($catList)


    echo     '<div align="center">' . "\n"
    .    '<form method="post" action="'.$_SERVER['PHP_SELF'].'?uInfo='.$userIdViewed.'">' . "\n"
    .    '<input type="submit" name="addDef" value="'.get_lang('Add new heading').'" />' . "\n"
    .    '</form>' . "\n"
    .    '</div>' . "\n";

}
elseif ($displayMode == "viewContentEdit")
{
    /*>>>>>>>>>>>> CATEGORIES CONTENTS : EDIT <<<<<<<<<<<<*/
    $catToEdit = claro_user_info_get_cat_content($userIdViewed,$_REQUEST['editContent']);
?>

<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?uInfo=<?php echo $userIdViewed; ?>">
<input type="hidden" name="claroFormId" value="<?php echo uniqid(''); ?>" />
<input type="hidden" name="cntId" value="<?php echo $catToEdit['contentId']; ?>" />
<input type="hidden" name="catId" value="<?php echo $catToEdit['catId'    ]; ?>" />
<input type="hidden" name="uInfo"  value="<?php echo $userIdViewed; ?>" />
<p><label for="content" ><b><?php echo $catToEdit['title']?></b></label></p>
<p><i><?php echo htmlize($catToEdit['comment'])?></i></p>
<?php if ($catToEdit['nbline']==1)
{
?><input  type="text" name="content" id="content" size="80" value="<?php echo htmlspecialchars($catToEdit['content']); ?>" />
<?php
}
else
{
?><textarea  cols="80" rows="<?php echo $catToEdit['nbline']?>" name="content" id="content" wrap="VIRTUAL"><?php echo $catToEdit['content']?></textarea>
<?php }
?><input type="submit" name="submitContent" value="<?php echo get_lang('Ok')?>" />
</form>

<?php
}
elseif ($displayMode =="viewMainInfoEdit")
{
    /*>>>>>>>>>>>> CATEGORIES MAIN INFO : EDIT <<<<<<<<<<<<*/

    $mainUserInfo = course_user_get_properties($userIdViewed, $course_id);

    if ($mainUserInfo)
    {
        $hidden_param = array ( 'submitMainUserInfo' => $userIdViewed,
                                'uInfo' => $userIdViewed);
        echo course_user_html_form($mainUserInfo, $course_id, $userIdViewed, $hidden_param);
    }
}
elseif ($displayMode == "viewContentList") // default display
{
    /*>>>>>>>>>>>> CATEGORIES CONTENTS : LIST <<<<<<<<<<<<*/

    $mainUserInfo = course_user_get_properties($userIdViewed, $course_id);

    if ($mainUserInfo)
    {
        $mainUserInfo['tutor'] = ($mainUserInfo['isTutor'] == 1 ? get_lang('Group Tutor') : ' - ');
        $mainUserInfo['isCourseManager'] = ($mainUserInfo['isCourseManager'] == 1 ? get_lang('Course manager') : ' - ');

        if ($mainUserInfo['picture'] != '')
        {
            echo '<img src="' . $imgRepositoryWeb . 'users/' . $mainUserInfo['picture'] . '" border="1">';
        }

        echo '<table class="claroTable" width="80%" border="0">' . "\n"
        .    '<thead>' . "\n"
        .    '<tr class="headerX">' . "\n"
        .    '<th align="left">'.get_lang('Name').'</th>' . "\n"
        .    '<th align="left">'.get_lang('Profile').'</th>' . "\n"
        .    '<th align="left">'.get_lang('Role').'</th>' . "\n"
        .    '<th>'.get_lang('Group Tutor').'</th>' . "\n"
        .    '<th>'.get_lang('Course manager').'</th>' . "\n"
        .    ($allowedToEditDef?'<th>'.get_lang('Edit').'</th>' . "\n":'')
        .    '<th>'.get_lang('Forum posts').'</th>'
        .    ($is_allowedToTrack?"<th>".get_lang('Tracking').'</th>' . "\n":'')
        .    '</tr>' . "\n"
        .    '</thead>' . "\n"
        .    '<tbody>' . "\n"
        .    '<tr align="center">' . "\n"
        .    '<td align="left"><b>'.htmlize($mainUserInfo['firstName']).' '.htmlize($mainUserInfo['lastName']).'</b></td>' . "\n"
        .    '<td align="left">'.htmlize(claro_get_profile_name($mainUserInfo['profileId'])).'</td>' . "\n"
        .    '<td align="left">'.htmlize($mainUserInfo['role']).'</td>' . "\n"
        .    '<td>'.$mainUserInfo['tutor'].'</td>'
        .    '<td>'.$mainUserInfo['isCourseManager'].'</td>'
        ;

        if($allowedToEditDef)
        {
            echo '<td>'
            .    '<a href="'.$_SERVER['PHP_SELF'].'?editMainUserInfo='.$userIdViewed.'">'
            .    '<img border="0" alt="'.get_lang('Edit').'" src="'.$imgRepositoryWeb.'edit.gif" />'
            .    '</a>'
            .    '</td>' . "\n"
            ;
        }

        echo '<td>'
        .    '<a href="'.$clarolineRepositoryWeb.'phpbb/viewsearch.php?searchUser='.$userIdViewed.'">'
        .    '<img src="'.$imgRepositoryWeb.'post.gif" alt="'.get_lang('Forum posts').'">'
        .    '</a>'
        .    '</td>';

        if($is_allowedToTrack)
        {
            echo '<td>'
            .    '<a href="'.$clarolineRepositoryWeb.'tracking/userLog.php?uInfo='.$userIdViewed.'">'
            .    '<img border="0" alt="'.get_lang('Tracking').'" src="'.$imgRepositoryWeb.'statistics.gif" />'
            .    '</a>'
            .    '</td>' . "\n"
            ;
        }

        echo '</tr>' . "\n"
        .    '</tbody>' . "\n"
        .    '</table>'."\n\n";

        if ( ! empty($_uid) || ! get_conf('user_email_hidden_to_anonymous') )
        {
            echo '<p><a href="mailto:'.$mainUserInfo['email'].'">'.$mainUserInfo['email'].'</a></p>';
        }

        echo '<hr noshade="noshade" size="1" />' . "\n" ;
    }


    if ($allowedToEditDef) // only course administrators see this line
    {
        echo "\n\n"
        .    '<div align="right">' . "\n"
        .    '<form method="post" action="'.$_SERVER['PHP_SELF'].'?uInfo='.$userIdViewed.'">' . "\n"
        .    get_lang('Course administrator only').' : '
        .    '<input type="submit" name="viewDefList" value="'.get_lang('Define Headings').'" />' . "\n"
        .    '</form>' . "\n"
        .    '<hr noshade="noshade" size="1" />' . "\n"
        .    '</div>'
        ;
    }

    $catList = claro_user_info_get_course_user_info($userIdViewed);

    if ($catList)
    {
        foreach ($catList as $thisCat)
        {
            // Category title

            echo '<p><b>'.$thisCat['title'].'</b></p>' . "\n"
            .    '<blockquote>' . "\n"
            ;
            // Category content

            if ($thisCat['content']) echo htmlize($thisCat['content'])."\n";
            else                     echo '....';

            // Edit command

            if ($allowedToEditContent)
            {
                echo '<br /><br />' . "\n"
                .    '<a href="'.$_SERVER['PHP_SELF'].'?editContent='.$thisCat['catId'].'&amp;uInfo='.$userIdViewed.'">'
                .    '<img src="' . $imgRepositoryWeb . 'edit.gif" border="0" alt="' . get_lang('Edit') . '" />'
                .    '</a>' . "\n"
                ;
            }

            echo    '</blockquote>' . "\n";
        }
    }
}

// Back button for each display mode (bottom)
echo '<p><small><a href="user.php">&lt;&lt;&nbsp;' . get_lang('Back to user list') . '</a></small></p>' . "\n";

include $includePath . '/claro_init_footer.inc.php';
?>
