<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );

/**
 * CLAROLINE
 *
 * This module displays the course list of a the current authenticated user
 *
 * @version     1.9 $Revision$
 * @copyright   2001-2010 Universite catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @package     CLINDEX
 * @author      Claro Team <cvs@claroline.net>
 * @author      Antonin Bourguignon <antonin.bourguignon@claroline.net>
 * @since       1.10
 */

if ( ! claro_is_user_authenticated() ) claro_disp_auth_form();

//List
$userCourseList = render_user_course_list();
$userCourseListDesactivated =  render_user_course_list_desactivated();

echo claro_html_tool_title(get_lang('My course list'));
// display activated list
if( !empty( $userCourseList ) )
{
    echo $userCourseList;
}
elseif( empty( $userCourseListDesactivated ) )
{
    echo get_lang('You are not enrolled to any course on this platform or all your courses are deactivated');
}
else
{
    echo get_lang( 'All your courses are deactivated (see list below)' );
}
//display legend if required
if( !empty($modified_course) )
{
    echo '<br />'
    .    '<small><span class="item hot"> '.get_lang('denotes new items').'</span></small>'
    .     '</td>' . "\n";
}

// DISPLAY DEACTIVATED COURSES

if ( !empty( $userCourseListDesactivated ) )
{
    echo claro_html_tool_title(get_lang('Deactivated course list'));
    echo $userCourseListDesactivated;
}
