<?php // $Id$
/**
 * CLAROLINE
 *
 * Script for forum tool
 *
 * @version 1.9 $Revision$
 *
 * @copyright 2001-2008 Universite catholique de Louvain (UCL)
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

$tlabelReq = 'CLFRM';
$gidReq=null;
$gidReset=true;

require '../inc/claro_init_global.inc.php';

$nameTools = get_lang('Forums');

if ( ! claro_is_in_a_course() || ! claro_is_course_allowed() ) claro_disp_auth_form(true);

claro_set_display_mode_available(true); // view mode

/*-----------------------------------------------------------------
Library
-----------------------------------------------------------------*/

include_once get_path('incRepositorySys') . '/lib/forum.lib.php';
include_once get_path('incRepositorySys') . '/lib/group.lib.inc.php';

/*-----------------------------------------------------------------
Initialise variables
-----------------------------------------------------------------*/

$last_visit = claro_get_current_user_data('lastLogin');
$is_allowedToEdit = claro_is_allowed_to_edit() ;
$dialogBox = new DialogBox();

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

if ( claro_is_user_authenticated() )
{
    $userGroupList  = get_user_group_list(claro_get_current_user_id());
    $userGroupList  = array_keys($userGroupList);
    $tutorGroupList = get_tutor_group_list(claro_get_current_user_id());
}
else
{
    $userGroupList = array();
    $tutorGroupList = array();
}


/*=================================================================
Display Section
=================================================================*/

$out = '';

$pagetype  = 'index';

$is_allowedToEdit = claro_is_allowed_to_edit()
|| ( claro_is_group_tutor() && !claro_is_course_manager() );
// ( claro_is_group_tutor()
//  is added to give admin status to tutor
// && !claro_is_course_manager())
// is added  to let course admin, tutor of current group, use student mode

$is_forumAdmin    = claro_is_allowed_to_edit();

$is_groupPrivate   = claro_get_current_group_properties_data('private');

$out .= claro_html_tool_title(get_lang('Forums'),
$is_allowedToEdit ? 'help_forum.php' : false);

$out .= disp_search_box();

$out .= $dialogBox->render();

// Forum toolbar

$out .= claro_html_menu_horizontal(disp_forum_toolbar($pagetype, 0, 0, 0));

/*-----------------------------------------------------------------
Display Forum Index Page
------------------------------------------------------------------*/

$out .= '<table width="100%" class="claroTable emphaseLine">' . "\n";

$colspan = $is_allowedToEdit ? 9 : 4;

$categoryIterator = 0;

$forumAvailableInGroups = is_tool_available_in_current_course_groups( 'CLFRM' );
if( !$forumAvailableInGroups ) 
{
    // we have to keep in mind that if group forums are disabled the group forum category will not
    // be displayed, so we have to remove it from the total of categories to avoid having up and down cmd
    // shown when not needed
    $total_categories--;
}

foreach ( $categories as $this_category )
{
    if ( !$forumAvailableInGroups && $this_category['cat_id'] == GROUP_FORUMS_CATEGORY )
    {
        continue;
    }

    $categoryIterator++;

    // Pass category for sumple user if no forum inside
    if ($this_category['forum_count'] == 0 && ! $is_allowedToEdit) continue;

    if ($this_category['forum_count'] > 0) $thCssClass = '';
    else                                   $thCssClass = ' class="invisible" ';

    $out .= '<tr class="superHeader" align="left" valign="top">' . "\n"

    .    '<th colspan="'.$colspan.'" '.$thCssClass.'>'
    ;

    if($is_allowedToEdit)
    {
        $out .= '<div style="float:right">'
        .    '<a href="' . htmlspecialchars(Url::Contextualize($_SERVER['PHP_SELF']
        .    '?cmd=rqEdCat&amp;catId=' . $this_category['cat_id'])) . '">'
        .    '<img src="' . get_icon_url('edit') . '" alt="' . get_lang('Edit') . '" />'
        .    '</a>'
        .    '&nbsp;'
        ;

        if ( $this_category['cat_id'] != GROUP_FORUMS_CATEGORY )
        {
            $out .= '<a href="'.htmlspecialchars(Url::Contextualize($_SERVER['PHP_SELF'].'?cmd=exDelCat&amp;catId='.$this_category['cat_id'])).'" '
            .    'onclick="return confirm_delete(\''. clean_str_for_javascript($this_category['cat_title']).'\');" >'
            .    '<img src="' . get_icon_url('delete') . '" alt="'.get_lang('Delete').'" />'
            .    '</a>'
            .    '&nbsp;'
            ;
        }

        if ( $categoryIterator > 1)
        {
            $out .= '<a href="'.htmlspecialchars(Url::Contextualize($_SERVER['PHP_SELF'].'?cmd=exMvUpCat&amp;catId='.$this_category['cat_id'])).'">'
            .    '<img src="' . get_icon_url('move_up') . '" alt="'.get_lang('Move up').'" />'
            .    '</a>'
            ;
        }

        if ( $categoryIterator < $total_categories)
        {
            $out .= '<a href="' . htmlspecialchars(Url::Contextualize($_SERVER['PHP_SELF']
            .    '?cmd=exMvDownCat'
            .    '&amp;catId=' . $this_category['cat_id'])) . '">'
            .    '<img src="' . get_icon_url('move_down') . '" alt="' . get_lang('Move down') . '" />'
            .    '</a>'
            ;
        }

        $out .= '</div>'
        ;
    }

    $out .= htmlspecialchars($this_category['cat_title']);

    if ( $this_category['cat_id'] == GROUP_FORUMS_CATEGORY)
    {
        $out .= '&nbsp;'
        .    '<a href="' . htmlspecialchars(Url::Contextualize(get_module_url('CLGRP') . '/group.php')).'">'
        .    '<img src="' . get_icon_url('group') . '" alt="' . get_lang('Groups') . '" />'
        .    '</a>'
        ;
    }

    $out .= '</th>' . "\n"
    .    '</tr>' . "\n"
    ;

    if ($this_category['forum_count'] == 0)
    {
        $out .= '<tr>' . "\n"
        .     '<td  colspan="' . $colspan . '" align="center">' . get_lang('No forum') . '</td>' . "\n"
        .     '</tr>' . "\n";
    }
    else
    {
        $out .= ' <tr class="headerX" align="center">' . "\n"
        .    ' <th align="left">' . get_lang('Forum') . '</th>' . "\n"
        .    ' <th>' . get_lang('Topics') . '</th>' . "\n"
        .    ' <th>' . get_lang('Posts')  . '</th>' . "\n"
        .    ' <th>' . get_lang('Last message') . '</th>' . "\n"
        ;

        if ($is_allowedToEdit)
        {
            $out .= '<th>'.get_lang('Edit').'</th>'
            .    '<th>'.get_lang('Empty it').'</th>'
            .    '<th>'.get_lang('Delete').'</th>'
            .    '<th colspan="2">'.get_lang('Move').'</th>'
            ;
        }
        $out .= '</tr>' . "\n";
    }

    $forumIterator = 0;

    if (claro_is_user_authenticated()) $date = $claro_notifier->get_notification_date(claro_get_current_user_id());

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
            $group_id     = is_null($this_forum['group_id']) ? null : (int) $this_forum['group_id'];

            $forum_post_allowed = ($this_forum['forum_access'] != 0) ? true : false;

            $out .= '<tr align="left" valign="top">' . "\n";

            if ( claro_is_user_authenticated()
                && $claro_notifier->is_a_notified_forum(claro_get_current_course_id(), $date, claro_get_current_user_id(), claro_get_current_group_id(), claro_get_current_tool_id(), $this_forum['forum_id']))
            {
                $class = 'item hot';
            }
            else
            {
                $class = 'item';
            }

            if ( $forum_post_allowed)
            {
                $locked_string = '';
            }
            else
            {
                $locked_string = ' <img src="' . get_icon_url('locked') . '" alt="'.get_lang('Locked').'" title="'.get_lang('Locked').'" /> <small>('.get_lang('No new post allowed').')</small>';
            }

            $out .= '<td>'                                               . "\n"
            .    '<span class="'.$class.'">'
            .    '<img src="' . get_icon_url( 'forum', 'CLFRM' ) . '" alt="" />' . "\n"
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
                    $out .= '<a href="'. htmlspecialchars(Url::Contextualize(get_module_url('CLFRM').'/viewforum.php?gidReq=' . $group_id
                    .    '&amp;forum=' . $forum_id  )). '">'
                    .    $forum_name
                    .    '</a>' . "\n"

                    .    '&nbsp;' . "\n"

                    .    '<a href="' . htmlspecialchars(Url::Contextualize(get_module_url('CLGRP') . '/group_space.php?gidReq=' . $group_id )) . '">'
                    .    '<img src="' . get_icon_url('group') .  '" alt="' . get_lang('Group area') . '" />'
                    .    '</a>' . "\n"
                    ;

                    if ( is_array($tutorGroupList) && in_array($group_id, $tutorGroupList) )
                    {
                        $out .= '&nbsp;<small>(' . get_lang('my supervision') . ')</small>';
                    }

                    if ( is_array($userGroupList) && in_array($group_id, $userGroupList) )
                    {
                        $out .= '&nbsp;<small>(' . get_lang('my group') . ')</small>';
                    }
                }
                else
                {
                    $out .= $forum_name;
                }
            }
            else
            {
                $out .= '<a href="'.htmlspecialchars(Url::Contextualize(get_module_url('CLFRM') . '/viewforum.php?forum=' . $forum_id )) . '">'
                .    $forum_name
                .    '</a> ';
            }

            $out .= $locked_string;
            
            $out .= '</span>';

            $out .= '<br /><div class="comment">' . $forum_desc . '</div>' . "\n"
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
            claro_html_localised_date(get_locale('dateTimeFormatShort'), datetime_to_timestamp($last_post)) :
            get_lang('No post')
            )
            . '</small>'
            .    '</td>' . "\n"
            ;


            if( $is_allowedToEdit)
            {
                $out .= '<td align="center">';

                if ( is_null($group_id ) )
                {
                    $out .= '<a href="'.htmlspecialchars(Url::Contextualize($_SERVER['PHP_SELF'].'?cmd=rqEdForum&amp;forumId='.$forum_id)).'">'
                    .    '<img src="' . get_icon_url('edit') . '" alt="'.get_lang('Edit').'" />'
                    .    '</a>'
                    ;
                }
                else
                {
                    $out .= '&nbsp;';
                }

                $out .= '</td>'

                .    '<td align="center">'
                .    '<a href="'.htmlspecialchars(Url::Contextualize($_SERVER['PHP_SELF'].'?cmd=exEmptyForum&amp;forumId='.$forum_id)).'" '
                .    'onclick="return confirm_empty(\''. clean_str_for_javascript($forum_name).'\');" >'
                .    '<img src="' . get_icon_url('sweep') . '" alt="'.get_lang('Empty').'" />'
                .    '</a>'
                .    '</td>'

                .'<td align="center">';

                if ( is_null($group_id ) )
                {
                    $out .= '<a href="'.htmlspecialchars(Url::Contextualize($_SERVER['PHP_SELF'].'?cmd=exDelForum&amp;forumId='.$forum_id)).'" '
                    .    'onclick="return confirm_delete(\''. clean_str_for_javascript($forum_name).'\');" >'
                    .    '<img src="' . get_icon_url('delete') . '" alt="'.get_lang('Delete').'" />'
                    .    '</a>';
                }
                else $out .= '&nbsp;';

                $out .= '</td>'

                .    '<td align="center">';

                if ($forumIterator > 1)
                {
                    $out .= '<a href="'.htmlspecialchars(Url::Contextualize($_SERVER['PHP_SELF'].'?cmd=exMvUpForum&amp;forumId='.$forum_id)).'">'
                    .    '<img src="' . get_icon_url('move_up') . '" alt="'.get_lang('Move up').'" />'
                    .    '</a>';
                }
                else $out .= '&nbsp;';

                $out .= '</td>'
                .    '<td align="center">'
                ;

                if ( $forumIterator < $this_category['forum_count'] )
                {
                    $out .= '<a href="'.htmlspecialchars(Url::Contextualize($_SERVER['PHP_SELF'].'?cmd=exMvDownForum&amp;forumId='.$forum_id)).'">'
                    .    '<img src="' . get_icon_url('move_down') . '" alt="'.get_lang('Move down').'" />'
                    .    '</a>';
                }
                else $out .= '&nbsp;';

                $out .=   '</td>';
            }

            $out .= '</tr>' . "\n";
        }
    }
}

$out .= '</table>' . "\n";

$claroline->display->body->appendContent($out);

echo $claroline->display->render();

?>