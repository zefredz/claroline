<?php // $Id$
/**
 * CLAROLINE
 *
  * This tool edit status of user in a course
 * Strangly, the is nothing to edit role and courseTutor status
 *
 * @version 1.8 $Revision$
 * @copyright 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @see http://www.claroline.net/wiki/index.php/CLUSR
 *
 * @package CLUSR
 * @package CLCOURSES
 *
 * @author Claro Team <cvs@claroline.net>
 *
 */

$cidReset = TRUE;$gidReset = TRUE;$tidReset = TRUE;

require '../inc/claro_init_global.inc.php';

// Security check
if ( ! $_uid ) claro_disp_auth_form();
if ( ! $is_platformAdmin ) claro_die(get_lang('Not allowed'));

require_once $includePath . '/lib/course_user.lib.php';

include($includePath . '/conf/user_profile.conf.php'); // find this file to modify values.

// used tables
$tbl_mdb_names = claro_sql_get_main_tbl();

// deal with session variables (must unset variables if come back from enroll script)
unset($_SESSION['userEdit']);


// see which user we are working with ...
$uidToEdit = $_REQUEST['uidToEdit'];
$cidToEdit = $_REQUEST['cidToEdit'];

//------------------------------------
// Execute COMMAND section
//------------------------------------


//Display "form and info" about the user

$ccfrom = isset($_REQUEST['ccfrom'])?$_REQUEST['ccfrom']:'';
$cfrom  = isset($_REQUEST['cfrom'])?$_REQUEST['cfrom']:'';

$cmd = isset($_REQUEST['cmd'])?$_REQUEST['cmd']:null ;

switch ($cmd)
{
    case 'exUpdateCourseUserProperties' :

        $properties['profileId'] = isset($_REQUEST['profileId'])?$_REQUEST['profileId']:null;
        $properties['tutor'] = isset($_REQUEST['isTutor'])?(int)$_REQUEST['isTutor']:null;
        $properties['role']  = isset($_REQUEST['role'])?trim($_REQUEST['role']):null;
        
        if ( claro_get_profile_name($properties['profileId']) == 'Manager' )
        {
            $dialogBox = get_lang('User is now course manager');
        }
        else
        {
            $dialogBox = get_lang('User is now student for this course');
        }

        $done = user_set_course_properties($uidToEdit, $cidToEdit, $properties);

        if ( ! $done )
        {
            $dialogBox = get_lang('No change applied');
        }

    break;
}

//------------------------------------
// FIND GLOBAL INFO SECTION
//------------------------------------

if ( isset($uidToEdit) )
{
    // get course user info 
    $courseUserProperties = course_user_get_properties($uidToEdit, $cidToEdit);
}

//------------------------------------
// PREPARE DISPLAY
//------------------------------------

$nameTools=get_lang('User course settings');

$interbredcrump[]= array ('url' => $rootAdminWeb, 'name' => get_lang('Administration'));

// javascript confirm pop up declaration
$htmlHeadXtra[] =
            "<script>
            function confirmationUnReg (name)
            {
                if (confirm(\"".clean_str_for_javascript(get_lang('Are you sure you want to unregister'))." \"+ name + \"? \"))
                    {return true;}
                else
                    {return false;}
            }
            </script>";

$displayBackToCU = false;
$displayBackToUC = false;
if ( 'culist'== $ccfrom )//coming from courseuser list
{
    $displayBackToCU = TRUE;
}
elseif ('uclist'== $ccfrom)//coming from usercourse list
{
    $displayBackToUC = TRUE;
}

$cmd_menu[] = '<a class="claroCmd" href="adminuserunregistered.php'
.             '?cidToEdit=' . $cidToEdit
.             '&amp;cmd=UnReg'
.             '&amp;uidToEdit=' . $uidToEdit . '" '
.             ' onClick="return confirmationUnReg(\'' . clean_str_for_javascript(htmlspecialchars($courseUserProperties['firstName']) . ' ' . htmlspecialchars($courseUserProperties['lastName'])) . '\');">'
.             get_lang('Unsubscribe')
.             '</a>'
;

$cmd_menu[] = '<a class="claroCmd" href="adminprofile.php'
.             '?uidToEdit=' . $uidToEdit . '">'
.             get_lang('User settings')
.             '</a>'
;

//link to go back to list : depend where we come from...

if ( $displayBackToCU )//coming from courseuser list
{
    $cmd_menu[] = '<a class="claroCmd" href="admincourseusers.php'
    .             '?cidToEdit=' . $cidToEdit
    .             '&amp;uidToEdit=' . $uidToEdit . '">'
    .             get_lang('Back to list')
    .             '</a> ' ;
}
elseif ( $displayBackToUC )//coming from usercourse list
{
    $cmd_menu[] = '<a class="claroCmd" href="adminusercourses.php'
    .             '?cidToEdit=' . $cidToEdit
    .             '&amp;uidToEdit=' . $uidToEdit . '">'
    .             get_lang('Back to list')
    .             '</a> ' ;
}

//------------------------------------
// DISPLAY
//------------------------------------

include $includePath . '/claro_init_header.inc.php';

// Display tool title

echo claro_html_tool_title( array( 'mainTitle' =>$nameTools
                                 , 'subTitle' => get_lang('Course') . ' : '
                                              .  htmlspecialchars($courseUserProperties['courseName'])
                                              .  '<br />'
                                              .  get_lang('User') . ' : '
                                              .  htmlspecialchars($courseUserProperties['firstName'])
                                              .  ' '
                                              .  htmlspecialchars($courseUserProperties['lastName'])
                                 )
                          );

// Display Forms or dialog box(if needed)

if ( isset($dialogBox) )
{
    echo claro_html_message_box($dialogBox);
}

$hidden_param = array( 'uidToEdit' => $uidToEdit,
                       'cidToEdit' => $cidToEdit,
                       'cfrom' => $cfrom,
                       'ccfrom' => $ccfrom);

echo course_user_html_form ( $courseUserProperties, $cidToEdit, $uidToEdit, $hidden_param );

echo claro_html_menu_horizontal($cmd_menu) ;

include $includePath . '/claro_init_footer.inc.php';
?>
