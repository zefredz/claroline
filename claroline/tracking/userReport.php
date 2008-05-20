<?php // $Id$
/**
 * CLAROLINE
 *
 * @version 1.9 $Revision$
 *
 * @copyright (c) 2001-2007 Universite catholique de Louvain (UCL)
 *
 * @author Sebastien Piraux <piraux_seb@hotmail.com>
 *
 * @package CLTRACK
 */

/*
 * Kernel
 */
require_once dirname( __FILE__ ) . '../../inc/claro_init_global.inc.php';



/*
 * Permissions
 */
if( ! get_conf('is_trackingEnabled') ) claro_die(get_lang('Tracking has been disabled by system administrator.')); 
if( ! claro_is_in_a_course() || ! claro_is_course_allowed() ) claro_disp_auth_form(true);

/*
 * Libraries
 */
uses( 'user.lib' );

require_once dirname( __FILE__ ) . '/lib/trackingRenderer.class.php';
require_once dirname( __FILE__ ) . '/lib/trackingRendererRegistry.class.php';

/*
 * Init request vars
 */

if( isset($_REQUEST['userId']) && is_numeric($_REQUEST['userId']) )   $userId = (int) $_REQUEST['userId'];
else                                                                  $userId = null;

if( isset($_REQUEST['courseId']) && !empty($_REQUEST['courseId']) )
{
    $courseId = $_REQUEST['courseId'];
}
else
{
    if( claro_is_in_a_course() ) $courseId = claro_get_current_course_id();
    else                         $courseId = null;
}


/*
 * Permissions
 */
$is_allowedToTrack = false;
$canSwitchCourses = false;

if( !is_null($userId) && claro_is_user_authenticated() )
{
    if(  $userId == claro_get_current_user_id() )
    {
        $is_allowedToTrack = true;
        $canSwitchCourses = true;
    }
}

if( claro_is_course_manager() || claro_is_platform_admin() )
{
    $is_allowedToTrack = true;

    if( claro_is_platform_admin() )
    {
        $canSwitchCourses = true;
    }
}

if( claro_is_in_a_course() )
{
    $canSwitchCourses = false;
}

/*
 * Init some other vars
 */

$dialogBox = '';

// user's course list
if( $canSwitchCourses )
{
    // get all
    $userCourseList = get_user_course_list($userId, true);

    if( !is_array($userCourseList) )
    {
        $userCourseList = array();
    }
}

// user's data
$userData = user_get_properties($userId);

if( !is_array($userData) )
{
    $dialogBox .= get_lang('Cannot find user.') ;
}

/*
 * Output
 */
$cssLoader = CssLoader::getInstance();
$cssLoader->load( 'tracking', 'screen');

// initialize output
$claroline->setDisplayType( CL_PAGE );

$nameTools = get_lang('User statistics');

$html = '';

/*
 * Output of : user information
 */
         
            
$html .= '<div id="userCart">' . "\n"
.     ' <div id="picture">' . "\n";

if( $pictureUrl = user_get_picture_url( $userData ) )
{
    $html .= '<img src="' . $pictureUrl . '" class="userPicture" alt="" />';
}
else
{
    $html .= '<img src="' . get_icon_url('nopicture') . '" class="userPicture" alt="" />';
}


$html .= '</div>' . "\n"
.     ' <div id="details">'
.     '  <p><span>' . get_lang('Last name') . '</span><br /> ' . htmlspecialchars($userData['lastname']) . '</p>'
.     '  <p><span>' . get_lang('First name') . '</span><br /> ' . htmlspecialchars($userData['firstname']) . '</p>'
.     '  <p><span>' . get_lang('Email') . '</span><br /> ' . htmlspecialchars($userData['email']) . '</p>'
.     ' </div>' . " \n"
.     '</div>' . "\n"
.     '<div class="spacer"></div>' . "\n";

/*
 * Output of : course list if required
 */
if( $canSwitchCourses )
{
    $html .= '<ul id="navlist">' . "\n"
    .     ' <li><a '.(empty($courseId)?'class="current"':'').' href="userLog.php?userId='.$userId.'">'.get_lang('Platform').'</a></li>' . "\n";


    foreach( $userCourseList as $course )
    {
        if( $course['sysCode'] == $courseId )     $class = 'class="current"';
        else                                        $class = '';

        $html .= ' <li>'
        .     '<a '.$class.' href=userLog.php?userId='.$userId.'&amp;courseId='.$course['sysCode'].'>'.$course['title'].'</a>'
        .     '</li>' . "\n";
    }

    $html .= '</ul>' . "\n\n";
}
else
{
    $html .= '<p>'
    .     '<a href="'.get_path('url').'/claroline/user/user.php' . claro_url_relay_context('?') . '"><small>'
    .    '&lt;&lt;&nbsp;'
    .    get_lang('Back to user list')
    .    '</small></a>' . "\n"
    .     '</p>' . "\n";
}
            
/*
 * Prepare rendering : 
 * Load and loop through available tracking renderers
 * Order of renderers blocks is arranged using "first found, first display" in the registry
 * Modify the registry to change the load order if required
 */
// get all renderers by using registry
$trackingRendererRegistry = TrackingRendererRegistry::getInstance();

// here we need course tracking renderers
$userTrackingRendererList = $trackingRendererRegistry->getUserRendererList();

foreach( $userTrackingRendererList as $ctr )
{
    $renderer = new $ctr( claro_get_current_course_id(), $userId );
    $html .= $renderer->render();
}


/*
 * Output rendering
 */
$claroline->display->body->setContent($html);

echo $claroline->display->render();

?>