<?php // $Id$
//----------------------------------------------------------------------
// CLAROLINE 1.7
//----------------------------------------------------------------------
// Copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see 'credits' file
//----------------------------------------------------------------------
/*----------------------------------------------------------------------*/
/*    Declaration and preliminar tests section                          */
/*----------------------------------------------------------------------*/

//used libraries

require '../inc/claro_init_global.inc.php';
include($includePath."/lib/admin.lib.inc.php");
include($includePath."/conf/user_profile.conf.php");
include($includePath."/lib/user.lib.php");
include($includePath."/lib/import_csv.lib.php");

/*
 * See in which context of user we are and check WHO is using the tool,there are 3 possibilities :
 * - adding CSV users by the admin tool                                                     (AddType=adminTool)
 * - adding CSV users by the admin, but with the class tool                                  (AddType=adminClassTool)
 * - adding CSV users by the user tool in a course (in this case, available to teacher too) (AddType=userTool)
 */

if ( isset($_REQUEST['AddType']) ) $AddType = $_REQUEST['AddType'];
else                               $AddType = 'userTool'; // default access is the user tool

switch ($AddType)
{
    case 'adminTool' :
    case 'adminClassTool' :
        if ( ! $_uid ) claro_disp_auth_form();
        if ( ! $is_platformAdmin ) claro_die($langNotAllowed);
    break;

    case 'userTool' :
    default :
        if ( ! $_cid || ! $is_courseAllowed ) claro_disp_auth_form(true);
        if ( ! $is_courseAdmin ) claro_die($langNotAllowed);
        $AddType = 'userTool' ;
    break;
}

/*
 * DB tables definition
 */

$tbl_mdb_names  = claro_sql_get_main_tbl();
$tbl_user       = $tbl_mdb_names['user'];
$tbl_class      = $tbl_mdb_names['user_category'];
$tbl_class_user = $tbl_mdb_names['user_rel_profile_category'];

//declare temporary upload directory

$uploadTempDir = "tmp/";

//deal with session variables to know in which step we are really and avoid doing changes twice

if (isset($_REQUEST['cmd']) && (($_REQUEST['cmd']=="exImpSec"  || $_REQUEST['cmd']=="exImp") && $_SESSION['claro_CSV_done']) || empty($_REQUEST['cmd'])) // this is to avoid a redo because of a page reload in browser
{
    $cmd = "";
    $display = "default";
    $_SESSION['claro_CSV_done'] = FALSE;
}

//Set format, fields separator and enclosion used for CSV files

$defaultFormat = "firstname;lastname;email;phone;username;password;officialCode";

if ( empty($_SESSION['claro_usedFormat']) )
{
    $_SESSION['claro_usedFormat'] = $defaultFormat;
}

if (isset($_REQUEST['loadDefault']) && ($_REQUEST['loadDefault'] =='yes'))
{
    $usedFormat                     = $defaultFormat;
    $_SESSION['claro_usedFormat']   = $defaultFormat;
    $_SESSION['CSV_fieldSeparator'] = ";";
    $_SESSION['CSV_enclosedBy']     = "";
    $dialogBox ="Format changed";
}

elseif (isset($_REQUEST['usedFormat']))
{
    //check if posted new format is OK

    $field_correct = claro_CSV_format_ok($_REQUEST['usedFormat'], $_REQUEST['fieldSeparator'], $_REQUEST['enclosedBy']);

    if (!$field_correct)
    {
        $dialogBox = $langErrorFormatCSV;
    }
    else
    {
        $dialogBox ="Format changed";
        $_SESSION['claro_usedFormat']   = $_REQUEST['usedFormat'];
        $_SESSION['CSV_fieldSeparator'] = $_REQUEST['fieldSeparator'];
        $_SESSION['CSV_enclosedBy']     = $_REQUEST['enclosedBy'];
    }
}

if (!isset($_SESSION['CSV_fieldSeparator'])) $_SESSION['CSV_fieldSeparator'] = ";";
if (!isset($_SESSION['CSV_enclosedBy']))     $_SESSION['CSV_enclosedBy'] = "\"";

$usedFormat = $_SESSION['claro_usedFormat'];

/*--------------------------------------------------------------------------------------------------------------*/
/*    Execute command section                                                                                 */
/*--------------------------------------------------------------------------------------------------------------*/

$cmd = isset($_REQUEST['cmd']) ? $_REQUEST['cmd'] : null ;

switch ($cmd)
{

    //STEP ONE : FILE UPLOADED, CHECK FOR POTENTIAL ERRORS

    case 'exImp' :

    //see if format is defined in session or in file

    if ($_REQUEST['firstLineFormat']=='YES')
    {
        $useFirstLine = true;
    }
    else
    {
        $fieldSeparator  = $_REQUEST['fieldSeparator'];
        $enclosedBy      = $_REQUEST['enclosedBy'];
        if ($_REQUEST['enclosedBy']=='dbquote')
        {
            $enclosedBy = '"';
        }
        $useFirstLine = false;
    }

    //check if a file was actually posted and that the mimetype is good

   $mimetypes = array(); //array used with supported mimetype for CSV files
   $mimetypes[] = "text/comma-separated-values";
   $mimetypes[] = "text/csv";
   $mimetypes[] = "text/plain";
   $mimetypes[] = "application/csv";
   $mimetypes[] = "x-application/csv";
   $mimetypes[] = "application/excel";
   $mimetypes[] = "application/vnd.ms-excel";
   $mimetypes[] = "application/vnd.msexcel";
   $mimetypes[] = "text/anytext";

    if ( $_FILES['CSVfile']['size'] == 0 )
    {
        $display   = 'default';
        $dialogBox = $langMustSelectAFile;
    }
    elseif (!in_array($_FILES["CSVfile"]['type'],$mimetypes) && (strpos($_FILES["CSVfile"]['type'],"text")===FALSE) )
    {
        $display   = 'default';
        $dialogBox = $langMustSelectATxtFile;
    }
    else
    {
       //check file content to see potentiel problems to add the users in this campus (errors are saved in session)
        
       claro_check_campus_CSV_File($uploadTempDir, $useFirstLine, $usedFormat, $_REQUEST['fieldSeparator'], $_REQUEST['enclosedBy']);
       $display = 'stepone';

    }

        break;

    //STEP TWO : ADD CONFIRMED, USERS ARE ADDED

    case 'exImpSec' :

        //build 2D array with users who will be add, avoiding those with error(s).

    $usersToAdd = array();

    for ($i=0, $size=sizeof($_SESSION['claro_csv_userlist']); $i<$size; $i++)
    {
        // user must be added only if we encountered exactly no error

        if (
             (!isset($_SESSION['claro_mail_synthax_error'][$i])      ||
                     $_SESSION['claro_mail_synthax_error'][$i]==false)       &&

             (!isset($_SESSION['claro_mail_used_error'][$i])         ||
                     $_SESSION['claro_mail_used_error'][$i]==false )         &&

             (!isset($_SESSION['claro_username_used_error'][$i])     ||
                     $_SESSION['claro_username_used_error'][$i]==false)      &&

             (!isset($_SESSION['claro_officialcode_used_error'][$i]) ||
                     $_SESSION['claro_officialcode_used_error'][$i]==false)  &&

             (!isset($_SESSION['claro_password_error'][$i])          ||
                     $_SESSION['claro_password_error'][$i]==false)           &&

             (!isset($_SESSION['claro_mail_duplicate_error'][$i])    ||
                     $_SESSION['claro_mail_duplicate_error'][$i]==false )    &&

             (!isset($_SESSION['claro_username_duplicate_error'][$i])||
                     $_SESSION['claro_username_duplicate_error'][$i]==false) &&

             (!isset($_SESSION['claro_officialcode_duplicate_error'][$i])||
                     $_SESSION['claro_officialcode_duplicate_error'][$i]==false)
           )
        {
            $usersToAdd[] = $_SESSION['claro_csv_userlist'][$i];
        }

    }


    // perform subscriptions of users with 'no error' found.

    foreach ($usersToAdd as $user)
    {

        //set empty fields if needed

        if (empty($user['phone']))        $user['phone'] = "";
        if (empty($user['email']))        $user['email'] = "";
        if (empty($user['officialCode'])) $user['officialCode'] = "";

        $uid = user_add($user);

        // for each use case alos perform thze other needed action :

        switch ($AddType)
        {
            case 'adminTool':
                    //its all done in this case
                    break;

              case 'adminClassTool':
                    user_add_to_class($uid, $_SESSION['admin_user_class_id']);

              break;

              case 'userTool':
                    user_add_to_course($uid, $_cid);
              break;
          }
        }


    // notify in session that action was done (to prevent double action if user uses back button of browser

    $_SESSION['claro_CSV_done'] = TRUE;

    // select display type

    $display = 'steptwo';

    break;

}

/*----------------------------------------------------------------------------------------------------------*/
/*    Display section          */
/*----------------------------------------------------------------------------------------------------------*/

// Deal with interbredcrumps and title variable this depends on the use case of the CSV import(see addType)

//echo $AddType; //echo just for debug

switch ($AddType)
{
    case 'adminTool':
        $noQUERY_STRING   = true;
        $nameTools        = $langAddCSVUsers;
        $interbredcrump[]    = array ('url'=>$rootAdminWeb, 'name'=> $langAdministration);
    break;

    case 'adminClassTool':
        $noQUERY_STRING      = true;
        $nameTools           = $langAddCSVUsersInClass;
        $interbredcrump[]    = array ('url'=>$rootAdminWeb, 'name'=> $langAdministration);
    $interbredcrump[]    = array ('url'=>$rootAdminWeb.'admin_class.php', 'name'=> $langClass);
    $interbredcrump[]    = array ('url'=>$rootAdminWeb.'admin_class_user.php', 'name'=> $langClassMembers);
    break;

    case 'userTool':
        $noQUERY_STRING   = true;
        $nameTools        = $langAddCSVUsersInCourse;
        $interbredcrump[] = array ('url'=>'user.php', 'name'=> $langUsers);
    break;
}



//Header declaration

include($includePath.'/claro_init_header.inc.php');

//display title

echo claro_disp_tool_title($nameTools);

//modify dialogbox if user asked form to change used format

if (isset($_REQUEST['chformat']) && $_REQUEST['chformat']=='yes')
{
    if (!empty($_SESSION['CSV_enclosedBy']) && $_SESSION['CSV_enclosedBy']=="dbquote") $dbquote_selected = "selected"; else $dbquote_selected = "";
    if (!empty($_SESSION['CSV_enclosedBy']) && $_SESSION['CSV_enclosedBy']=="")   $blank_selected   = "selected"; else $blank_selected   = "";
    if (!empty($_SESSION['CSV_enclosedBy']) && $_SESSION['CSV_enclosedBy']==",")  $coma_selected    = "selected"; else $coma_selected    = "";
    if (!empty($_SESSION['CSV_enclosedBy']) && $_SESSION['CSV_enclosedBy']==".")  $dot_selected     = "selected"; else $dot_selected     = "";
    
    if (!empty($_SESSION['CSV_fieldSeparator']) && $_SESSION['CSV_fieldSeparator']==";")  $dot_coma_selected_sep = "selected"; else $dot_coma_selected_sep = "";
    if (!empty($_SESSION['CSV_fieldSeparator']) && $_SESSION['CSV_fieldSeparator']==",")  $coma_selected_sep     = "selected"; else $coma_selected_sep = "";
    if (!empty($_SESSION['CSV_fieldSeparator']) && $_SESSION['CSV_fieldSeparator']=="")   $blank_selected_sep    = "selected"; else $blank_selected_sep = "";
    
    $dialogBox = $langModifyFormat .' :<br><br>'
    .            $langTheFields . ' <b>firstname</b>, <b>lastname</b>, <b>username</b>, <b>password</b> ' . $langAreCompulsory . '<br><br>'
    .          '<form method="POST" action="' . $_SERVER['PHP_SELF'] . '">'
    .          '<input type="hidden" name="AddType" value="' . $AddType . '" >'
    .            '<input type="text" name="usedFormat" value="' . htmlspecialchars($usedFormat) . '" size="55"><br /><br />'
    .            '<label for="fieldSeparator">' .  $langFieldSeparatorUsed . ' </label>:'

    .            '<select name="fieldSeparator" id="fieldSeparator">'
    .            '  <option value=";"  '.$dot_coma_selected_sep.'>;</option>'
    .            '  <option value=","  '.$coma_selected_sep.'    >,</option>'
    .            '  <option value=" "  '.$blank_selected_sep.'   >' . $langBlankSpace . ' </option>'
    .            '</select>'
    .' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
    .            '<label for="enclosedBy">'
    .            '  ' . $lang_fields_enclosed_by .' :'
    .            '</label>'

    .            '<select name="enclosedBy" id="enclosedBy">'
    .            ' <option value=""        '.$blank_selected.'>' . $langNone . ' </option>'
    .            ' <option value="dbquote" '.$dbquote_selected.'>"</option>'
    .            ' <option value=","       '.$coma_selected.'>,</option>'
    .            ' <option value="."       '.$dot_selected.'>.</option>'
    .            '</select><br />'
    .            '<input type="submit" value="' . $langOk . '"'
    .          '</form>'
    ;

}


//display dialog Box (or any forms)

if( isset( $dialogBox ) )
{
    echo claro_disp_message_box($dialogBox);
    echo '<br>';
}

switch ( $display )
{

//DEFAULT DISPLAY : display form to upload

case 'default' :

    $backButtonUrl = "";
    unset($_SESSION['claro_csv_userlist']);
    if ($_cid)
    {
        $backButtonUrl = $clarolineRepositoryWeb."user/user.php";
    }
    elseif (isset($addType) && $addType =="adminClassTool") //tricky fix, the use of addtype should be avoided
    {
        $backButtonUrl = $clarolineRepositoryWeb."admin/admin_class_user.php?class=".$_SESSION['admin_user_class_id'];
    }
    elseif ($is_platformAdmin)
    {
        $backButtonUrl = $clarolineRepositoryWeb."admin/";
    }

    $_SESSION['claro_CSV_done'] = FALSE;

    echo $langSpecifyFormat;
    ?>
    :
    <br><br>
<form enctype="multipart/form-data"  method="POST" action="<?php echo $_SERVER['PHP_SELF'] ?>">
  <input type="radio" name="firstLineFormat" value="YES" id="firstLineFormat_YES"> <label for="firstLineFormat_YES"><?php echo $langUseFormatDefined; ?></label><br><br>
  <input type="radio" name="firstLineFormat" value="NO" checked id="firstLineFormat_NO"> <label for="firstLineFormat_NO"><?php echo $langUseFollowingFormat; ?></label><br><br>
    <b>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $usedFormat; ?><br><br>
    </b>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    [<a class="claroCmd" href="<?php echo $_SERVER['PHP_SELF'] . '?display=default&amp;loadDefault=yes&amp;AddType=' . $AddType ; ?>"><?php echo $langLoadDefaultFormat; ?></a>]
    |
    [<a class="claroCmd" href="<?php echo $_SERVER['PHP_SELF'] . '?display=default&amp;chformat=yes&amp;AddType=' . $AddType; ?>"><?php echo $langEditFormat; ?></a>]<br><br>

    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

    <input type="hidden" name="fieldSeparator" value="<?php if (!empty($_SESSION['CSV_fieldSeparator'])) echo $_SESSION['CSV_fieldSeparator']; else echo ";" ?>" >
    <input type="hidden" name="enclosedBy" value="<?php echo $_SESSION['CSV_enclosedBy']; ?>" >
    <input type="hidden" name="AddType" value="<?php echo $AddType; ?>" >
    <br>
    <?php echo $langFileForCSVUpload; ?><input type="file" name="CSVfile">
    <br><br>
    <input type="submit" name="submitCSV" value="<?php echo $lang_add_user_list; ?>">
    <?php echo claro_disp_button($backButtonUrl,$langCancel); ?>
    <input type="hidden" name="cmd" value="exImp">
</form>

<?php
    break;

// STEP ONE DISPLAY : display the possible error with uploaded file and ask for continue or cancel

case "stepone" :

    if ((!empty($_SESSION['claro_invalid_format_error']) && $_SESSION['claro_invalid_format_error']==true) ||
        !(count($_SESSION['claro_mail_synthax_error'])==0)       ||
        !(count($_SESSION['claro_mail_used_error'])==0)          ||
        !(count($_SESSION['claro_username_used_error'])==0)      ||
        !(count($_SESSION['claro_officialcode_used_error'])==0)  ||
        !(count($_SESSION['claro_password_error'])==0)           ||
        !(count($_SESSION['claro_mail_duplicate_error'])==0)     ||
        !(count($_SESSION['claro_username_duplicate_error'])==0) ||
        !(count($_SESSION['claro_officialcode_duplicate_error'])==0))
    {
        echo '<b>' . $lang_the_following_errors_were_found . ' :</b><br><br>' . "\n";

        //display errors encountered while trying to add users

        claro_disp_CSV_error_backlog();

        $noerror = FALSE;
    }
    else
    {
        echo $lang_no_error_in_file_found."<br>";

        $noerror = TRUE;
    }


    if (!(isset($_SESSION['claro_invalid_format_error'])) || ($_SESSION['claro_invalid_format_error'] == false))
    {
        echo '<br>'
        .    $lang_do_you_want_to_continue
        .    '<br>'
        ;
        if (!$noerror)
        {
            echo '(' . $lang_if_you_choose_to_continue_lines_with_errors_will_be_simply_ignored . ')<br>';
        }
        echo '<br>'
        .    '<form method="POST" action="' . $_SERVER['PHP_SELF'] . '?cmd=exImpSec">' . "\n"
        .    '<input type="hidden" name="AddType" value="' . $AddType . '" >'
        .   '<input type="submit" value="' . $langContinue .'" >' . "\n"
        .   claro_disp_button($_SERVER['PHP_SELF'] . '?AddType=' . htmlspecialchars($AddType), $langCancel)
        .   '</form>' . "\n";

    }
    else
    {
        echo "<br>".claro_disp_button($_SERVER['PHP_SELF'], $langCancel)."<br>";
    }


    break;

// STEP TWO DISPLAY : display what happened, confirm users added (LOG)

case "steptwo" :

    echo "<b>". sizeof($usersToAdd) . " $langNewUsersIn </b> <br><br>";

    foreach ($usersToAdd as $user)
    {

       //display messages concerning actions done to new users...

       switch ($AddType)
       {
          case "adminTool":
              echo $user['firstname']." ".$user['lastname']." $langAddedToCampus <br>";
          break;

          case "adminClassTool":
              echo $user['firstname']." ".$user['lastname']." $langAddedToCampusAndClass <br>";
          break;

          case "userTool":
              echo $user['firstname']." ".$user['lastname']." $langAddedToCampusAndCourse <br>";
          break;
       }
    }

      // display back link at the end of the log

   switch ($AddType)
   {
      case "adminTool":
          echo "<br><a href=\"../admin/adminusers.php\">&gt;&gt; $langCSVSeeUserList</a>";
      break;

      case "adminClassTool":
          echo "<br><a href=\"../admin/admin_class.php\">&gt;&gt; $langBackToClassList</a>";
      break;

      case "userTool":
          echo "<br><a href=\"user.php\">&gt;&gt; $langBackToUserList</a>";
      break;
  }
    break;
}

//footer

include($includePath."/claro_init_footer.inc.php");
?>
