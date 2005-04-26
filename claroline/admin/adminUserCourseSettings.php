<?php // $Id$
//----------------------------------------------------------------------
// CLAROLINE 1.6.0
//----------------------------------------------------------------------
// Copyright (c) 2001-2004 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see 'credits' file
//----------------------------------------------------------------------

define ("USER_SELECT_FORM", 1);
define ("USER_DATA_FORM", 2);

$cidReset = TRUE;$gidReset = TRUE;$tidReset = TRUE;
require '../inc/claro_init_global.inc.php';
//SECURITY CHECK
if (!$is_platformAdmin) claro_disp_auth_form();

include($includePath.'/lib/admin.lib.inc.php');
include($includePath.'/conf/user_profile.conf.php'); // find this file to modify values.

$nameTools=$langModifUserCourseSettings;

$interbredcrump[]= array ("url"=>$rootAdminWeb, "name"=> $langAdministration);

  // javascript confirm pop up declaration
   $htmlHeadXtra[] =
            "<script>
            function confirmationUnReg (name)
            {
                if (confirm(\"".clean_str_for_javascript($langAreYouSureToUnsubscribe)." \"+ name + \"? \"))
                    {return true;}
                else
                    {return false;}
            }
            </script>";

// used tables

$tbl_mdb_names = claro_sql_get_main_tbl();
$tbl_course           = $tbl_mdb_names['course'           ];
$tbl_rel_course_user  = $tbl_mdb_names['rel_course_user'  ];
$tbl_admin            = $tbl_mdb_names['admin'         ];
$tbl_user             = $tbl_mdb_names['user'             ];

include($includePath.'/claro_init_header.inc.php');

// deal with sesison variables (must unset variables if come back from enroll script)

session_unregister("userEdit");


// see which user we are working with ...

$user_id = $_REQUEST['uidToEdit'];

//------------------------------------
// Execute COMMAND section
//------------------------------------

switch (isset($cmd))
{
   case "changeStatus" :
	
	if ($status_form == "teacher")
        {
            $properties['status'] = 1;
            $properties['role']   = "Professor";
            $properties['tutor']  = 1;
            $done = update_user_course_properties($uidToEdit, $cidToEdit, $properties);
            if ($done)
            {
               $dialogBox = $langUserIsNowCourseManager;
            }
            else
            {
               $dialogBox = $langStatusChangeNotMade;
            }
        }
        if ($status_form == "student")
        {
            $properties['status'] = 5;
            $properties['role']   = "Student";
            $properties['tutor']  = 0;
            $done = update_user_course_properties($uidToEdit, $cidToEdit, $properties);
            if ($done)
            {
               $dialogBox = $langUserIsNowStudent;
            }
            else
            {
               $dialogBox = $langStatusChangeNotMade;
            }
        }
        break;
}

//------------------------------------
//FIND GLOBAL INFO SECTION
//------------------------------------

if(isset($user_id))

{
    $sqlGetInfoUser ="
    SELECT *
        FROM  `".$tbl_user."`
        WHERE user_id='".$user_id."'";
    $result=claro_sql_query($sqlGetInfoUser);
    //echo $sqlGetInfoUser;

    $myrow          = mysql_fetch_array($result);
    $user_id        = $myrow['user_id'];
    $nom_form       = $myrow['nom'];
    $prenom_form    = $myrow['prenom'];
    $username_form  = $myrow['username'];
    $email_form     = $myrow['email'];
    $userphone_form = $myrow['phoneNumber'];
    $display = USER_DATA_FORM;

    // find global course info

    $sql = "SELECT * FROM `".$tbl_course."`
            WHERE code='".$cidToEdit."'";
    $resultCourse = claro_sql_query($sql);
    $courseList = mysql_fetch_array($resultCourse);

    // find course user settings, must see if the user is teacher for the course

    $sql = 'SELECT * FROM `'.$tbl_rel_course_user.'`
            WHERE user_id="'.$uidToEdit.'"
            AND code_cours="'.$cidToEdit.'"';
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
}

//------------------------------------
// DISPLAY
//------------------------------------


// Display tool title

claro_disp_tool_title($nameTools." : ".$courseList['intitule']);

  //subtitle
?>
<h4><?php echo $langUser." : ".$prenom_form." ".$nom_form; ?></h4>
<?php

//Display Forms or dialog box(if needed)

if($dialogBox)
  {
    claro_disp_message_box($dialogBox);
  }

//Display "form and info" about the user

?>

<form method="GET" action="<?php echo $_SERVER['PHP_SELF'] ?>">
<table width="100%" >

            <tr>
               <td><?php echo $langUserStatus?> : </td>
               <td>
                 <input type="radio" name="status_form" value="student" id="status_form_student" <?php if ($isStudent) { echo "checked"; }?> >
				 <label for="status_form_student"><?php echo $langStudent?></label>
                 <input type="radio" name="status_form" value="teacher" id="status_form_teacher" <?php if ($isCourseManager) { echo "checked"; }?> >
				 <label for="status_form_teacher"><?php echo $langCourseManager?></label>
                 <input type="hidden" name="uidToEdit" value="<?php echo $user_id?>">
                 <input type="hidden" name="cidToEdit" value="<?php echo $cidToEdit?>">
                 <input type="submit" name="applyChange" value="<?php echo $langSaveChanges?>">
                 <input type="hidden" name="cmd" value="changeStatus">
                 <input type="hidden" name="cfrom" value="<?php echo $cfrom?>">
                 <input type="hidden" name="ccfrom" value="<?php echo $ccfrom?>">
               </td>
            </tr>
     </table>
</form>

<?php

// display TOOL links :

echo "<a class=\"claroCmd\" href=\"adminuserunregistered.php?cidToEdit=".$cidToEdit."&cmd=UnReg&uidToEdit=".$user_id."\" onClick=\"return confirmationUnReg('".clean_str_for_javascript($prenom_form." ".$nom_form)."');\">".$langUnsubscribe."</a> | ";

echo "<a class=\"claroCmd\" href=\"adminprofile.php?uidToEdit=".$uidToEdit."\">".$langGoToMainUserSettings."</a>";

//link to go back to list : depend where we come from...

if ($ccfrom=="culist")//coming from courseuser list
{
    echo " | <a class=\"claroCmd\" href=\"admincourseusers.php?cidToEdit=".$cidToEdit."&uidToEdit=".$uidToEdit."\">".$langBackToList."</a> ";
}
elseif ($ccfrom=="uclist")//coming from usercourse list
{
    echo " | <a class=\"claroCmd\" href=\"adminusercourses.php?cidToEdit=".$cidToEdit."&uidToEdit=".$uidToEdit."\">".$langBackToList."</a> ";
}

// display footer

include($includePath."/claro_init_footer.inc.php");
?>
