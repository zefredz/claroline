<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );

/******************************************************************************
 * CLAROLINE
 ******************************************************************************
 * This module displays the course list of a the current authenticated user
 *
 * @version 1.9 $Revision$
 * @copyright (c) 2001-2008 Universite catholique de Louvain (UCL)
 * @license (GPL) GENERAL PUBLIC LICENSE - http://www.gnu.org/copyleft/gpl.html
 * @package CLINDEX
 ******************************************************************************/

if ( ! claro_is_user_authenticated() ) claro_disp_auth_form();

echo claro_html_tool_title(get_lang('My course list'));
// display list
$userCourseList = render_user_course_list();
if( !empty( $userCourseList ) )
{
    echo $userCourseList;
}
else
{
    echo get_lang('You are not enrolled at any course on this plateform');
}
//display legend if required
if( !empty($modified_course) )
{
    echo '<br />'
    .    '<small><span class="item hot"> '.get_lang('denotes new items').'</span></small>'
    .     '</td>' . "\n";
}
