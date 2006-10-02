<?php // $Id$
/**
 * CLAROLINE
 *
 * This  script  manage the creation of a new course.
 *
 * it contain 3 panel
 * - Form
 * - Wait
 * - Done
 *
 * @version 1.8 $Revision$
 *
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @see http://www.claroline.net/wiki/CLCRS/
 *
 * @package COURSE
 *
 * old version : http://cvs.claroline.net/cgi-bin/viewcvs.cgi/claroline/claroline/create_course/add_course.php
 *
 * @author Claro Team <cvs@claroline.net>
 *
 */

require '../inc/claro_init_global.inc.php';

//=================================
// Security check
//=================================

if ( ! $_uid )                   claro_disp_auth_form();
if ( ! $is_allowedCreateCourse ) claro_die(get_lang('Not allowed'));

//=================================
// Main section
//=================================

include claro_get_conf_repository() . 'course_main.conf.php';
require_once $includePath . '/lib/add_course.lib.inc.php';
require_once $includePath . '/lib/course.lib.inc.php';
require_once $includePath . '/lib/course_user.lib.php';
require_once $includePath . '/lib/user.lib.php'; // for claro_get_uid_of_platform_admin()
require_once $includePath . '/lib/fileManage.lib.php';
require_once $includePath . '/lib/form.lib.php';
require_once $includePath . '/lib/sendmail.lib.php';
require_once $includePath . '/lib/claroCourse.class.php';

define('DISP_COURSE_CREATION_FORM'     ,__LINE__);
define('DISP_COURSE_CREATION_SUCCEED'  ,__LINE__);
define('DISP_COURSE_CREATION_FAILED'   ,__LINE__);
define('DISP_COURSE_CREATION_PROGRESS' ,__LINE__);

$display = DISP_COURSE_CREATION_FORM; // default display

$dialogBox = '' ;

$cmd = isset($_REQUEST['cmd']) ? $_REQUEST['cmd'] : null;
$adminContext = isset($_REQUEST['adminContext']) ? (bool) $_REQUEST['adminContext'] : null;

// New course object
$course = new ClaroCourse($_user['firstName'], $_user['lastName'], $_user['mail']);

if ( $adminContext && $is_platformAdmin )
{
	// from admin, add param to form
    $course->addHtmlParam('adminContext','1');
}

if ( $cmd == 'exEdit' )
{
    $course->handleForm();
    
    if( $course->validate() )
    {
    	if( $course->save() )
    	{
            // include the platform language file with all language variables
            language::load_translation();
            language::load_locale_settings();

    		$course->mailAdministratorOnCourseCreation($_user['firstName'], $_user['lastName'], $_user['mail']);
    		
    		$dialogBox = get_lang('You have just created the course website')
		    	. ' : ' . '<strong>' . $course->officialCode . '</strong>' . "\n";

    		$display = DISP_COURSE_CREATION_SUCCEED;
    	}
    	else
    	{
    		$display = DISP_COURSE_CREATION_FAILED;
    	}
    }
    else
    {
    	$dialogBox .= $course->backlog->output();
    	$display = DISP_COURSE_CREATION_FAILED;
    }
}

if( $cmd == 'rqProgress' )
{   
	$course->handleForm();
	
	if( $course->validate() )
    {
		// Trig a waiting screen as course creation may take a while ...
	    
	    $progressUrl = $course->buildProgressUrl();

	    $htmlHeadXtra[] = '<meta http-equiv="REFRESH" content="0; URL=' . $progressUrl . '">';

	    $display = DISP_COURSE_CREATION_PROGRESS;
	}
	else
	{
	   	$dialogBox .= $course->backlog->output();
		$display = DISP_COURSE_CREATION_FAILED;
	}
}

// Set navigation url

if ( $adminContext && $is_platformAdmin )
{
    $interbredcrump[] = array ('url' => $rootAdminWeb, 'name' => get_lang('Administration'));
    $backUrl = $rootAdminWeb;
}
else
{
	$backUrl = $urlAppend . '/index.php';
}

//=================================
// Display section
//=================================

include $includePath . '/claro_init_header.inc.php';

echo claro_html_tool_title(get_lang('Create a course website'));

if ( !empty($dialogBox) ) echo claro_html_message_box($dialogBox);


if( $display == DISP_COURSE_CREATION_FORM || $display == DISP_COURSE_CREATION_FAILED )
{	
	// display form
	echo $course->displayForm($backUrl);
}
elseif ( $display == DISP_COURSE_CREATION_PROGRESS )
{
	// display "progression" page
    $msg = get_lang('Creating course (it may take a while) ...') . '<br />' . "\n"
    .      '<p align="center">'
    .      '<img src="' . $imgRepositoryWeb . '/processing.gif" alt="" />'
    .      '</p>' . "\n"
    .      '<p>'
    .      get_lang('If after while no message appears confirming the course creation, please click <a href="%url">here</a>',array('%url' => $progressUrl))
    .      '</p>' . "\n\n";
    
    echo claro_html_message_box( $msg );
}
elseif ( $display == DISP_COURSE_CREATION_SUCCEED )
{
	// display confirmation
    echo '<p>'
    .    '<a class="claroCmd" href="' . $backUrl . '">'
    .    get_lang('Continue')
    .    '</a>'
    .	 '</p>' . "\n";
}


include $includePath . '/claro_init_footer.inc.php';
?>
