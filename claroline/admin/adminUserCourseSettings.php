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

define ('USER_SELECT_FORM', 1);
define ('USER_DATA_FORM', 2);

$cidReset = TRUE;$gidReset = TRUE;$tidReset = TRUE;
require '../inc/claro_init_global.inc.php';

// Security check
if ( ! $_uid ) claro_disp_auth_form();
if ( ! $is_platformAdmin ) claro_die(get_lang('Not allowed'));

require_once $includePath . '/lib/admin.lib.inc.php';
require_once $includePath . '/lib/user.lib.php';
require_once $includePath . '/lib/claro_html.class.php';

include($includePath . '/conf/user_profile.conf.php'); // find this file to modify values.

// used tables
$tbl_mdb_names = claro_sql_get_main_tbl();

// deal with session variables (must unset variables if come back from enroll script)
unset($_SESSION['userEdit']);


// see which user we are working with ...
$user_id   = $_REQUEST['uidToEdit'];
$uidToEdit = $_REQUEST['uidToEdit'];
$cidToEdit = $_REQUEST['cidToEdit'];

//------------------------------------
// Execute COMMAND section
//------------------------------------


//Display "form and info" about the user

if (isset($_REQUEST['ccfrom'])) {$ccfrom = $_REQUEST['ccfrom'];} else {$ccfrom = '';}
if (isset($_REQUEST['cfrom']))  {$cfrom  = $_REQUEST['cfrom'];} else {$cfrom = '';}

$cmd =(isset($_REQUEST['cmd'])) ? $_REQUEST['cmd'] : null;

switch ($cmd)
{
    case 'changeStatus' :
    {
        if ( $_REQUEST['status_form'] == 'teacher' )
        {
            $properties['status'] = 1;
            $properties['role']   = get_lang('Course manager');
            $properties['tutor']  = 1;
            $done = user_update_course_properties($uidToEdit, $cidToEdit, $properties);
            if ($done)
            {
                $dialogBox = get_lang('UserIsNowCourseManager');
            }
            else
            {
                $dialogBox = get_lang('StatusChangeNotMade');
            }
        }
        elseif ( $_REQUEST['status_form'] == 'student' )
        {
            $properties['status'] = 5;
            $properties['role']   = get_lang('Student');
            $properties['tutor']  = 0;
            $done = user_update_course_properties($uidToEdit, $cidToEdit, $properties);
            if ($done)
            {
                $dialogBox = get_lang('UserIsNowStudent');
            }
            else
            {
                $dialogBox = get_lang('StatusChangeNotMade');
            }
        }
    }
    break;
}

//------------------------------------
//FIND GLOBAL INFO SECTION
//------------------------------------

if(isset($user_id))

{
    // claro_get_user_data
    $sqlGetInfoUser ="
    SELECT user_id,
           nom,
           prenom,
           username,
           email,
           phoneNumber
        FROM  `" . $tbl_mdb_names['user'] . "`
        WHERE user_id='". (int)$user_id . "'";
    $result=claro_sql_query($sqlGetInfoUser);

    //echo $sqlGetInfoUser;
    $myrow          = mysql_fetch_array($result);
    $user_id        = $myrow['user_id'];
    $nom_form       = $myrow['nom'];
    $prenom_form    = $myrow['prenom'];
    $username_form  = $myrow['username'];
    $email_form     = $myrow['email'];
    $userphone_form = $myrow['phoneNumber'];
    // end of claro_get_user_data


    $display = USER_DATA_FORM;

    $courseData = claro_get_course_data($cidToEdit);



    // claro_get_course_user_data
    // find course user settings, must see if the user is teacher for the course
    $sql = 'SELECT * FROM `' . $tbl_mdb_names['rel_course_user'] . '`
            WHERE user_id="' . (int)$uidToEdit . '"
            AND code_cours="' . addslashes($cidToEdit) . '"';
    $resultCourseUser = claro_sql_query($sql);
    $list = mysql_fetch_array($resultCourseUser);

    if ($list['statut'] == '1')
    {
       $isCourseManager = TRUE;
       $isStudent = FALSE;
    }
    else
    {
       $isCourseManager = false;
       $isStudent = TRUE;
    }
    // end of claro_get_course_user_data
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
                if (confirm(\"".clean_str_for_javascript(get_lang('AreYouSureToUnsubscribe'))." \"+ name + \"? \"))
                    {return true;}
                else
                    {return false;}
            }
            </script>";

$displayBackToCU = false;
$displayBackToUC = false;
if ( $ccfrom == 'culist' )//coming from courseuser list
{
    $displayBackToCU = TRUE;
}
elseif ($ccfrom=='uclist')//coming from usercourse list
{
    $displayBackToUC = TRUE;
}


$cmd_menu[] = '<a class="claroCmd" href="adminuserunregistered.php'
.             '?cidToEdit=' . $cidToEdit
.             '&amp;cmd=UnReg'
.             '&amp;uidToEdit=' . $user_id . '" '
.             ' onClick="return confirmationUnReg(\'' . clean_str_for_javascript($prenom_form . ' ' . $nom_form) . '\');">'
.             get_lang('Unsubscribe')
.             '</a>'
;

$cmd_menu[] = '<a class="claroCmd" href="adminprofile.php'
.             '?uidToEdit=' . $uidToEdit . '">'
.             get_lang('Last 7 days')
.             '</a>'
;

//link to go back to list : depend where we come from...

if ( $displayBackToCU )//coming from courseuser list
{
    $cmd_menu[] = '<a class="claroCmd" href="admincourseusers.php'
    .             '?cidToEdit=' . $cidToEdit
    .             '&amp;uidToEdit=' . $uidToEdit . '">'
    .             get_lang('BackToList')
    .             '</a> '
    ;
}
elseif ( $displayBackToUC )//coming from usercourse list
{
    $cmd_menu[] = '<a class="claroCmd" href="adminusercourses.php'
    .             '?cidToEdit=' . $cidToEdit
    .             '&amp;uidToEdit=' . $uidToEdit . '">'
    .             get_lang('BackToList')
    .             '</a> '
    ;
}




//------------------------------------
// DISPLAY
//------------------------------------

include $includePath . '/claro_init_header.inc.php';

// Display tool title

echo claro_disp_tool_title( array( 'mainTitle' =>$nameTools
                                 , 'subTitle' => get_lang('Course') . ' : '
                                              .  $courseData['name']
                                              .  '<br />'
                                              .  get_lang('User') . ' : '
                                              .  $prenom_form
                                              .  ' '
                                              .  $nom_form
                                 )
                          );

//Display Forms or dialog box(if needed)

if(isset($dialogBox))
{
    echo claro_disp_message_box($dialogBox);
}
?>

<form method="POST" action="<?php echo $_SERVER['PHP_SELF'] ?>">
<table width="100%" >
<tr>
<td><?php echo get_lang('UserStatus')?> : </td>
<td>
<input type="radio" name="status_form" value="student" id="status_form_student" <?php if ($isStudent) { echo "checked"; }?> >
<label for="status_form_student"><?php echo get_lang('Student')?></label>
<input type="radio" name="status_form" value="teacher" id="status_form_teacher" <?php if ($isCourseManager) { echo "checked"; }?> >
<label for="status_form_teacher"><?php echo get_lang('Course manager')?></label>
<input type="hidden" name="uidToEdit" value="<?php echo $user_id?>">
<input type="hidden" name="cidToEdit" value="<?php echo $cidToEdit?>">
<input type="submit" name="applyChange" value="<?php echo get_lang('SaveChanges')?>">
<input type="hidden" name="cmd" value="changeStatus">
<input type="hidden" name="cfrom" value="<?php echo $cfrom?>">
<input type="hidden" name="ccfrom" value="<?php echo $ccfrom?>">
</td>
</tr>
</table>
</form>

<?php
// display TOOL links :
echo claro_html::menu_horizontal($cmd_menu);
// display footer

include $includePath . '/claro_init_footer.inc.php';
?>