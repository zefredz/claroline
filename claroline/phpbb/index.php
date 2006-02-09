<?php // $Id$
/**
 * CLAROLINE
 *
 * Script for forum tool
 *
 * @version 1.8 $Revision$
 *
 * @copyright 2001-2006 Universite catholique de Louvain (UCL)
 * @copyright (C) 2001 The phpBB Group
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @author Claro Team <cvs@claroline.net>
 *
 * @package CLFRM
 *
 * @todo  $last_post would be always a timestamp 
 *
 */

/*=================================================================
  Init Section
 =================================================================*/

$tlabelReq = 'CLFRM___';

require '../inc/claro_init_global.inc.php';

$nameTools = get_lang('Forums');

if ( ! $_cid || ! $is_courseAllowed ) claro_disp_auth_form(true);

claro_set_display_mode_available(true); // view mode

/*-----------------------------------------------------------------
  Stats
 -----------------------------------------------------------------*/

event_access_tool($_tid, $_courseTool['label']);

/*-----------------------------------------------------------------
  Library
 -----------------------------------------------------------------*/

include $includePath . '/lib/forum.lib.php';

/*-----------------------------------------------------------------
  Initialise variables
 -----------------------------------------------------------------*/

$last_visit = $_user['lastLogin'];
$is_allowedToEdit = $is_courseAdmin ;
$dialogBox = '';

/*=================================================================
  Main Section
 =================================================================*/

/*-----------------------------------------------------------------
  Administration command
 -----------------------------------------------------------------*/

if ( $is_allowedToEdit ) include_once('./admin.php');

/*-----------------------------------------------------------------
  Get forums categories
 -----------------------------------------------------------------*/

$categories       = get_category_list();
$total_categories = count($categories);

$forum_list = get_forum_list();

if ( ! empty($_uid) )
{
    $userGroupList  = get_user_group_list($_uid);
    $tutorGroupList = get_tutor_group_list($_uid);
}
else
{
    $userGroupList = array();
    $tutorGroupList = array();
}


/*=================================================================
  Display Section
 =================================================================*/

// Claroline Header

include $includePath . '/claro_init_header.inc.php';

$pagetype  = 'index';

$is_allowedToEdit = claro_is_allowed_to_edit() 
                    || ( $is_groupTutor && !$is_courseAdmin);
                    // ( $is_groupTutor 
                    //  is added to give admin status to tutor 
                    // && !$is_courseAdmin)
                    // is added  to let course admin, tutor of current group, use student mode

$is_forumAdmin    = claro_is_allowed_to_edit();

$is_groupPrivate   = $_groupProperties ['private'];

echo claro_disp_tool_title(get_lang('Forums'), 
                      $is_allowedToEdit ? 'help_forum.php' : false);

echo disp_search_box();

if ( !empty($dialogBox) ) echo claro_html::message_box($dialogBox);

// Forum toolbar

disp_forum_toolbar($pagetype, 0, 0, 0);

/*-----------------------------------------------------------------
  Display Forum Index Page
------------------------------------------------------------------*/

echo '<table width="100%" class="claroTable emphaseLine">' . "\n";

$colspan = $is_allowedToEdit ? 9 : 4;

$categoryIterator = 0;

foreach ( $categories as $this_category )
{
    ++$categoryIterator;

    // Pass category for sumple user if no forum inside
    if ($this_category['forum_count'] == 0 && ! $is_allowedToEdit) continue;

    if ($this_category['forum_count'] > 0) $thCssClass = '';
    else                                   $thCssClass = ' class="invisible" ';

    echo '<tr class="superHeader" align="left" valign="top">' . "\n"
    
    .    '<th colspan="'.$colspan.'" '.$thCssClass.'>';

    if($is_allowedToEdit)
    {
        echo '<div style="float:right">'
        .    '<a href="'.$_SERVER['PHP_SELF'].'?cmd=rqEdCat&amp;catId='.$this_category['cat_id'].'">'
        .    '<img src="'.$imgRepositoryWeb.'edit.gif" alt="'.get_lang('Edit').'" />'
        .    '</a>'
        .    '&nbsp;'
        ;

        if ( $this_category['cat_id'] != GROUP_FORUMS_CATEGORY )
        echo '<a href="'.$_SERVER['PHP_SELF'].'?cmd=exDelCat&amp;catId='.$this_category['cat_id'].'" '
        .    'onClick="return confirm_delete(\''. clean_str_for_javascript($this_category['cat_title']).'\');" >'
        .    '<img src="'.$imgRepositoryWeb.'delete.gif" alt="'.get_lang('Delete').'" />'
        .    '</a>'
        .    '&nbsp;'
        ;

        if ( $categoryIterator > 1)
        echo '<a href="'.$_SERVER['PHP_SELF'].'?cmd=exMvUpCat&amp;catId='.$this_category['cat_id'].'">'
        .    '<img src="'.$imgRepositoryWeb.'up.gif" alt="'.get_lang('Move up').'" />'
        .    '</a>';
        
        if ( $categoryIterator < $total_categories)
        echo '<a href="'.$_SERVER['PHP_SELF'].'?cmd=exMvDownCat&amp;catId='.$this_category['cat_id'].'">'
        .    '<img src="'.$imgRepositoryWeb.'down.gif" alt="'.get_lang('Move down').'" />'
        .    '</a>';

        echo '</div>'
        ;   
    }
    
    echo htmlspecialchars($this_category['cat_title']);

    if ( $this_category['cat_id'] == GROUP_FORUMS_CATEGORY)
    {
        echo '&nbsp;<a href="'.$clarolineRepositoryWeb.'group/group.php">'
        .    '<img src="'.$imgRepositoryWeb. 'group.gif" alt="' . get_lang('Groups') . '">'
        .    '</a>';
    }

    echo '</th>' . "\n"
    .    '</tr>' . "\n";


    
    if ($this_category['forum_count'] == 0)
    {
        echo '<tr>' . "\n"
        .     '<td  colspan="' . $colspan . '" align="center">' . get_lang('No forum') . '</td>' . "\n"
        .     '</tr>' . "\n";
    }
    else
    {
        echo ' <tr class="headerX" align="center">' . "\n"
        .    ' <th align="left">' . get_lang('Forum') . '</th>' . "\n"
        .    ' <th>' . get_lang('Topics') . '</th>' . "\n"
        .    ' <th>' . get_lang('Posts')  . '</th>' . "\n"
        .    ' <th>' . get_lang('Last message') . '</th>' . "\n"
        ;       

        if ($is_allowedToEdit)
        {
            echo '<th>'.get_lang('Edit').'</th>'
            .    '<th>'.get_lang('Empty').'</th>'
            .    '<th>'.get_lang('Delete').'</th>'
            .    '<th colspan="2">'.get_lang('Move').'</th>'
            ;
        }
        echo '</tr>' . "\n";
    }

    $forumIterator = 0;

    if (isset($_uid)) $date = $claro_notifier->get_notification_date($_uid);
    
    foreach ( $forum_list as $this_forum )
    {
        if ( $this_forum['cat_id'] == $this_category['cat_id'] )
        {
            ++ $forumIterator;
            
            $forum_name   = htmlspecialchars($this_forum['forum_name']);
            $forum_desc   = htmlspecialchars($this_forum['forum_desc']);
            $last_post    = $this_forum['post_time'];
            $total_topics = (int) $this_forum['forum_topics'];
            $total_posts  = (int) $this_forum['forum_posts' ];
            $forum_id     = (int) $this_forum['forum_id'    ];
            $group_id     = is_null($this_forum['group_id']) ? 
                            null : (int) $this_forum['group_id'];

            $forum_post_allowed = ($this_forum['forum_access'] != 0) ? true : false;

            echo '<tr align="left" valign="top">' . "\n";

            if (isset($_uid) && $claro_notifier->is_a_notified_forum($_cid, $date, $_uid, $_gid, $_tid, $this_forum['forum_id']))
            {
                $forum_img = 'forum_hot.gif';
            }
            else
            {
                $forum_img = 'forum.gif';
            }

            if ( $forum_post_allowed)
            {
                $locked_string = '';
            }
            else
            {
                $locked_string = ' <img src="'.$imgRepositoryWeb.'locked.gif" alt="'.get_lang('Locked').'" title="'.get_lang('Locked').'" /> <small>('.get_lang('No new post allowed').')</small>';
            }

            echo '<td>'                                               . "\n"
            .    '<img src="' . $imgRepositoryWeb . $forum_img . '" alt="" />' . "\n"
            .    '&nbsp;'                                             . "\n"
            ;

            // Visit only my group forum if not admin or tutor.
            // If tutor, see all groups but indicate my groups.
            // Group Category == 1

            if ( ! is_null($group_id ) )
            {
                if (   in_array($group_id, $userGroupList )
                    || in_array($group_id, $tutorGroupList) 
                    || ! $is_groupPrivate || $is_forumAdmin 
                   )
                {
                    echo '<a href="viewforum.php?gidReq=' . $group_id
                    .    '&amp;forum=' . $forum_id . '">'
                    .    $forum_name
                    .    '</a>' 
                    ;

                    echo  '&nbsp;<a href="'.$clarolineRepositoryWeb.'group/group_space.php?gidReq='.$group_id.'">'
                        . '<img src="'.$imgRepositoryWeb. 'group.gif" alt="' . get_lang('Group area') . '">'
                        . '</a>';

                    if ( is_array($tutorGroupList) && in_array($group_id, $tutorGroupList) )
                    {
                        echo '&nbsp;<small>(' . get_lang('My supervision') . ')</small>';
                    }

                    if ( is_array($userGroupList) && in_array($group_id, $userGroupList) )
                    {
                        echo '&nbsp;<small>(' . get_lang('My group') . ')</small>';
                    }
                }
                else
                {
                    echo $forum_name;
                }
            }
            else
            {
                echo '<a href="viewforum.php?forum=' . $forum_id . '">'
                .    $forum_name
                .    '</a> ';
            }

            echo $locked_string;

            echo '<br /><div class="comment">' . $forum_desc . '</div>' . "\n"
            .    '</td>' . "\n"

            .    '<td align="center">' . "\n"
            .    '<small>' . $total_topics . '</small>' . "\n"
            .    '</td>' . "\n"

            .    '<td align="center">' . "\n"
            .    '<small>' . $total_posts . '</small>' . "\n"
            .    '</td>' . "\n"
            .    '<td align="center">' . "\n"
            .    '<small>' 
            .    ( 
                   ($last_post > 0) ? 
                   claro_disp_localised_date($dateTimeFormatShort, datetime_to_timestamp($last_post)) :
                   get_lang('No post')
                  )
            . '</small>'
            .    '</td>' . "\n"
            ;

            
            if( $is_allowedToEdit)
            {
                echo '<td align="center">';

                if ( is_null($group_id ) )
                {
                    echo '<a href="'.$_SERVER['PHP_SELF'].'?cmd=rqEdForum&amp;forumId='.$forum_id.'">'
                    .    '<img src="' . $imgRepositoryWeb . 'edit.gif" alt="'.get_lang('Edit').'" />'
                    .    '</a>'
                    ;
                }
                else
                {
                    echo '&nbsp;';
                }

                echo '</td>'

                .    '<td align="center">'
                .    '<a href="'.$_SERVER['PHP_SELF'].'?cmd=exEmptyForum&amp;forumId='.$forum_id.'" '
                .    'onClick="return confirm_empty(\''. clean_str_for_javascript($forum_name).'\');" >'
                .    '<img src="' . $imgRepositoryWeb . 'sweep.gif" alt="'.get_lang('Empty').'" />'
                .    '</a>'
                .    '</td>'

                .'<td align="center">';

                if ( is_null($group_id ) )
                {
                    echo '<a href="'.$_SERVER['PHP_SELF'].'?cmd=exDelForum&amp;forumId='.$forum_id.'" '
                    .    'onClick="return confirm_delete(\''. clean_str_for_javascript($forum_name).'\');" >'
                    .    '<img src="' . $imgRepositoryWeb . 'delete.gif" alt="'.get_lang('Delete').'" />'
                    .    '</a>';
                }
                else echo '&nbsp;';

                echo '</td>'

                .    '<td align="center">';
                
                if ($forumIterator > 1) 
                {
                    echo '<a href="'.$_SERVER['PHP_SELF'].'?cmd=exMvUpForum&amp;forumId='.$forum_id.'">'
                    .    '<img src="' . $imgRepositoryWeb . 'up.gif" alt="'.get_lang('Move up').'" />'
                    .    '</a>';
                }
                else echo '&nbsp;';
                             
                echo '</td>';
                
                echo '<td align="center">';   
                           
                if ( $forumIterator < $this_category['forum_count'] )
                {
                    echo '<a href="'.$_SERVER['PHP_SELF'].'?cmd=exMvDownForum&amp;forumId='.$forum_id.'">'
                    .    '<img src="' . $imgRepositoryWeb . 'down.gif" alt="'.get_lang('Move down').'" />'
                    .    '</a>';
                }
                else echo '&nbsp;';

                echo   '</td>';
            }

            echo '</tr>' . "\n";
        }
    }
}

echo '</table>' . "\n";

// Display Forum Footer

include($includePath . '/claro_init_footer.inc.php');

?>
