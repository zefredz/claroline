<?php // $Id$
/**
 * CLAROLINE
 *
 * This tool manage properties of an exiting course
 *
 * @version 1.8 $Revision$
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @author claroline Team <cvs@claroline.net>
 *
 * old version : http://cvs.claroline.net/cgi-bin/viewcvs.cgi/claroline/claroline/course_info/infocours.php
 *
 * @package CLCRS
 *
 */

$gidReset = true;
require '../inc/claro_init_global.inc.php';

$nameTools = get_lang('Course settings');
$noPHP_SELF = true;

if ( ! $_cid || ! $_uid) claro_disp_auth_form(true);

$is_allowedToEdit = $is_courseAdmin;

if ( ! $is_allowedToEdit )
{
    claro_die(get_lang('Not allowed'));
}

//=================================
// Main section
//=================================

include claro_get_conf_repository() . 'course_main.conf.php';
require_once $includePath . '/lib/course.lib.inc.php';
require_once $includePath . '/lib/user.lib.php';
require_once $includePath . '/lib/form.lib.php';
require_once $includePath . '/lib/claroCourse.class.php';

// initialise variables

$dialogBox = '';

$cmd = isset($_REQUEST['cmd']) ? $_REQUEST['cmd'] : null;
$adminContext = isset($_REQUEST['adminContext']) ? (bool) $_REQUEST['adminContext'] : null;
$current_cid = null;

// New course object

$course = new ClaroCourse();

// Initialise current course id

if ( $adminContext && $is_platformAdmin )
{
	// from admin
	if ( isset($_REQUEST['cidToEdit']) )
	{
		$current_cid = trim($_REQUEST['cidToEdit']);
	} 
	elseif ( isset($_REQUEST['cidReq']) )
	{
		$current_cid = trim($_REQUEST['cidReq']);
	}		

    // add param to form
    $course->addHiddenParamForm('adminContext','1');
    $course->addHiddenParamForm('cidToEdit',$current_cid);

	// Back url    
   	$backUrl = $rootAdminWeb . 'admincourses.php' ;
}
elseif ( !empty($_course['sysCode']) )
{
	// from my course
    $current_cid = $_course['sysCode'];
    $backUrl = $clarolineRepositoryWeb . 'course/index.php?cid=' . htmlspecialchars($current_cid);
}
else
{
	$current_cid = null ;
}

if ( $course->load($current_cid) )
{
	if ( $cmd == 'exEdit' )
	{
	    $course->handleForm();
	    
	    if( $course->validate() )
	    {
	    	if( $course->save() )
	    	{
	    		$dialogBox = get_lang('The information has been modified') . '<br />' . "\n"
	    			. '<a href="' . $backUrl . '">' . get_lang('Continue') . '</a>' ;
	    		
	    		if ( ! $adminContext )
	    		{
		    		// force reload of the "course session" of the user
		    		$cidReset = true;
					$cidReq = $current_cid;
					include($includePath . '/claro_init_local.inc.php');
				}
	    	}
	    	else
	    	{
	    		$dialogBox = get_lang('Unable to save');
	    	}
	    }
	    else
	    {
	    	$dialogBox = $course->backlog->output();
	    }
	}

}
else
{
	// course data load failed
	claro_die(get_lang('Wrong parameters'));
}

//----------------------------
// initialise links array
//----------------------------

$links = array();

// add course tool list edit

$url_course_edit_tool_list = $clarolineRepositoryWeb . 'course/tools.php';

$links[] = '<a class="claroCmd" href="' . $url_course_edit_tool_list . '">'
.          '<img src="' . $imgRepositoryWeb . 'edit.gif" alt="" />'
.          get_lang('Edit Tool list')
.          '</a>' ;

// Main group settings
$links[] = '<a class="claroCmd" href="../group/group_properties.php">'
.          '<img src="' . $imgRepositoryWeb . 'settings.gif" alt="" />'
.          get_lang("Main Group Settings")
.          '</a>' ;

// add tracking link

if ( get_conf('is_trackingEnabled') )
{
	$url_course_tracking = $clarolineRepositoryWeb . 'tracking/courseLog.php';

    $links[] = '<a class="claroCmd" href="' . $url_course_tracking . '">'
    .          '<img src="' . $imgRepositoryWeb . 'statistics.gif" alt="" />'
    .          get_lang('Statistics')
    .          '</a>' ;
}

// add delete course link

if ( get_conf('showLinkToDeleteThisCourse') )
{
	$url_course_delete = $clarolineRepositoryWeb . 'course/delete.php';

    $links[] = '<a class="claroCmd" href="' . $url_course_delete . '">'
    .          '<img src="' . $imgRepositoryWeb . 'delete.gif" alt="" />'
    .          get_lang('Delete the whole course website')
    .          '</a>' ;
}

if ( $adminContext && $is_platformAdmin )
{
    // switch to admin breadcrumb
	$interbredcrump[]= array ('url' => $rootAdminWeb, 'name' => get_lang('Administration'));
    unset($_cid);    	
    
    $links[] = '<a class="claroCmd" href="' . $backUrl . '">'
    .          get_lang('Back to course list')
    .          '</a>' ;
}

//=================================
// Display section
//=================================

include $includePath . '/claro_init_header.inc.php';

echo claro_html_tool_title($nameTools);

if ( ! empty ($dialogBox) ) echo claro_html_message_box($dialogBox);

echo '<p>' . claro_html_menu_horizontal($links) . '</p>' . "\n\n" ;


// Display form
echo $course->displayForm($backUrl);


include $includePath . '/claro_init_footer.inc.php' ;

?>
