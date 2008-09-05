<?php // $Id$
/**
 * CLAROLINE
 *
 * @version 1.9 $Revision$
 *
 * @copyright (c) 2001-2007 Universite catholique de Louvain (UCL)
 *
 * @author Sebastien Piraux <seb@claroline.net>
 *
 * @package CLTRACK
 */

/*
 * Kernel
 */
require_once dirname( __FILE__ ) . '../../inc/claro_init_global.inc.php';



/*
 * Usual check
 */
if( ! get_conf('is_trackingEnabled') ) claro_die(get_lang('Tracking has been disabled by system administrator.'));

/*
 * Libraries
 */
uses( 'user.lib', 'courselist.lib' );

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
if( claro_is_platform_admin() )
{
    // if I am platform admin I can always see tracking for all courses of user
    $is_allowedToTrack = true;
    $canSwitchCourses = true;
}
elseif( !is_null($userId) && claro_is_user_authenticated() && $userId == claro_get_current_user_id() )
{
    // if these are my tracking I can view tracking of my other courses
    $is_allowedToTrack = true;
    $canSwitchCourses = true;
}
elseif( claro_is_course_manager() )
{
    // if I am course manager I will only be able to see tracking of my course
    $is_allowedToTrack = true;
    $canSwitchCourses = false;
}
else
{
    // Mr Nobody can see nothing.  Let's get out of here !
    claro_die(get_lang('Not allowed'));
}

/*
 * Init some other vars
 */

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
    claro_die( get_lang('Cannot find user') );
}

/*
 * Output
 */
$cssLoader = CssLoader::getInstance();
$cssLoader->load( 'tracking', 'screen');

$nameTools = get_lang('Statistics');
ClaroBreadCrumbs::getInstance()->setCurrent( $nameTools, Url::Contextualize($_SERVER['PHP_SELF'] . '?userId=' . $userId ) );

if( $canSwitchCourses )
{
    ClaroBreadCrumbs::getInstance()->prepend( get_lang('My user account'), Url::Contextualize('../auth/profile.php') );
}
else
{
    ClaroBreadCrumbs::getInstance()->prepend( get_lang('Users'), Url::Contextualize('../user/user.php') );
}

$html = '';

/*
 * Output of : user information
 */
         
            
$html .= '<div id="userCart">' . "\n"
.     ' <div id="picture">' . "\n";

if( $pictureUrl = user_get_picture_url( $userData ) )
{
    $html .= '<img src="'.$pictureUrl.'" class="userPicture" alt="" />';
}
else
{
    $html .= '<img src="'.get_icon_url('nopicture').'" class="userPicture" alt="" />';
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
    .     ' <li><a '.(empty($courseId)?'class="current"':'').' href="userReport.php?userId='.$userId.'&amp;cidReset=true">'.get_lang('Platform').'</a></li>' . "\n";


    foreach( $userCourseList as $course )
    {
        if( $course['sysCode'] == $courseId )     $class = 'class="current"';
        else                                        $class = '';

        $html .= ' <li>'
        .     '<a '.$class.' href="'
        .     htmlspecialchars(Url::Contextualize( 'userReport.php?userId='.$userId.'&amp;cidReq='.$course['sysCode'] ))
        .     '">'.$course['title'].'</a>'
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
$trackingRendererRegistry = TrackingRendererRegistry::getInstance(claro_get_current_course_id());

// FIXME do something when courseId is null
if( ! is_null($courseId) )
{ 
    // we need user tracking renderers in course context
    $userTrackingRendererList = $trackingRendererRegistry->getUserRendererList(TrackingRendererRegistry::COURSE);
}
else
{
    // we need user tracking renderers in platform context
    $userTrackingRendererList = $trackingRendererRegistry->getUserRendererList(TrackingRendererRegistry::PLATFORM);
}
    
    foreach( $userTrackingRendererList as $ctr )
    {
        $renderer = new $ctr( $courseId, $userId );
        $html .= $renderer->render();
    }

/*
 * Output rendering
 */
$claroline->display->body->setContent($html);

echo $claroline->display->render();

?>