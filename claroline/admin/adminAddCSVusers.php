<?php // $Id$
//----------------------------------------------------------------------
// CLAROLINE 1.6
//----------------------------------------------------------------------
// Copyright (c) 2001-2004 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see 'credits' file
//----------------------------------------------------------------------

//used langfile
$langFile = "admin";

//----------------------LANG TO ADD -------------------------------------------------------------------------------

$lang_add_user_list      = 'Add user list';
$lang_fields_enclosed_by = 'Fields enclosed by';
$lang_if_you_choose_to_continue_lines_with_errors_will_be_simply_ignored= 'if you choose to continue, lines with errors will be simply ignored';
$lang_the_following_errors_were_found ='The following errors were found ';
$lang_no_error_in_file_found = "No error in file found.";
$lang_do_you_want_to_continue = "Do you want to continue?";
//----------------------LANG TO ADD -------------------------------------------------------------------------------

/*--------------------------------------------------------------------------------------------------------------*/
/*	Declaration section     */
/*--------------------------------------------------------------------------------------------------------------*/

//used libraries
require '../inc/claro_init_global.inc.php';
@include ($includePath."/installedVersion.inc.php");
include($includePath."/lib/admin.lib.inc.php");

/*
 * DB tables definition
 */

$tbl_mdb_names             = claro_sql_get_main_tbl();
$tbl_user                  = $tbl_mdb_names['user'];
$tbl_class                 = $tbl_mdb_names['user_category'];
$tbl_class_user            = $tbl_mdb_names['user_rel_profile_category'];

//temporary upload directory

$uploadTempDir = "tmp/";

//deal with session variables to know in which step we are really and avoid doing changes twice

if ((($cmd=="exImpSec"  || $cmd=="exImp") && $_SESSION['claro_CSV_done']) || empty($cmd)) // this is to avoid a redo because of a page reload in browser
{
    $cmd = "";
    $display = "default";
    $_SESSION['claro_CSV_done'] = FALSE;
}

//Set format, fields separator and enclosion used for CSV files

$defaultFormat = "surname;name;email;phone;username;password;officialCode";

if (empty($_SESSION['claro_usedFormat'])) 
{
    $_SESSION['claro_usedFormat'] = $defaultFormat;
}

if ($_REQUEST['usedFormat'])
{
    //check if posted new format is OK
    
    $field_correct = claro_CSV_format_ok($_REQUEST['usedFormat']);
          
    if (!$field_correct)
    {
        $dialogBox = "ERROR: The format you gave is not compatible with Claroline";
    }
    else
    {
        $dialogBox ="Format changed";
	$_SESSION['claro_usedFormat'] = $_REQUEST['usedFormat'];
    }
}

$usedFormat = $_SESSION['claro_usedFormat'];

/*--------------------------------------------------------------------------------------------------------------*/
/*	Execute command section      */
/*--------------------------------------------------------------------------------------------------------------*/

switch ($cmd)
{
    
    //STEP ONE : FILE UPLOADED, CHECK FOR POTENTIAL ERRORS
    
    case "exImp" :
        
	//see if format is defined in session or in file
    
	if ($_REQUEST['firstLineFormat']=="YES")
	{
	    $useFirstLine = true;
	}
	else
	{
	    $fieldSeparator  = $_REQUEST['fieldSeparator'];    
	    $enclosedBy      = $_REQUEST['enclosedBy'];
	    if ($_REQUEST['enclosedBy']=="dbquote") 
	    {
	        $enclosedBy = "\"";
	    } 
	}
	
	//check file content to see potentiel problems to add the users in this campus (errors are saved in session)
	
	claro_check_campus_CSV_File($uploadTempDir, $useFirstLine, $usedFormat, $fieldSeparator, $enclosedBy);
	
	// select display type
	
	$display = "stepone";
	
        break;
	
    //STEP TWO : ADD CONFIRMED, USERS ARE ADDED
	
    case "exImpSec" :
    	
        //build 2D array with users who will be add, avoiding those with error(s).
	
	$usersToAdd = array();
	
	for ($i=0, $size=sizeof($_SESSION['claro_csv_userlist']); $i<$size; $i++)
        {
	    // user must be added only if we encountered exactly no error
	    
	    if ((!($_SESSION['claro_mail_synthax_error'][$i])) &&
	        (!($_SESSION['claro_mail_used_error'][$i])) &&
		(!($_SESSION['claro_username_used_error'][$i])) &&              
	        (!($_SESSION['claro_officialcode_used_error'][$i])) &&         
	        (!($_SESSION['claro_password_error'][$i])) &&                  
	        (!($_SESSION['claro_mail_duplicate_error'][$i])) &&               
	        (!($_SESSION['claro_username_duplicate_error'][$i])) &&         
	        (!($_SESSION['claro_officialcode_duplicate_error'][$i])))
	    {
	        $usersToAdd[] = $_SESSION['claro_csv_userlist'][$i];
            }
        }
	
	// perform subscription of "no error" new users  

        add_userlist($usersToAdd);
        
	//notify in session that action was done (to prevent double action if user uses back button of browser
	
	$_SESSION['claro_CSV_done'] = TRUE;
	
	// select display type

	$display = "steptwo";
        
	break;    
    
}

/*----------------------------------------------------------------------------------------------------------*/
/*	Display section          */
/*----------------------------------------------------------------------------------------------------------*/

// Deal with interbredcrumps and title variable

$noQUERY_STRING   = true;
$nameTools        = "Add a user list";
$interbredcrump[] = array ("url"=>$rootAdminWeb, "name"=> "Administration");

//Header declaration

include($includePath."/claro_init_header.inc.php");

//display title

claro_disp_tool_title($nameTools);

//modify dialogbox if user asked form to change used format

if ($_REQUEST['chformat']=="yes")
{
    $dialogBox = "Modify the format :<br><br>"
        ."The fields \"<b>surname;</b>\", \"<b>name;</b>\", \"<b>username;</b>\" and \"<b>password;</b>\" are compulsory.<br><br>"
        ."<form metod=\"POST\" action=\"".$_SERVER['PHP_SELF']."\">"
        ."  <input type=\"text\" name=\"usedFormat\" value=\"".$usedFormat."\" size=\"55\">"
	."  <input type=\"submit\" value=\"".$langOk."\""
	."</form>";
}


//display dialog Box (or any forms)

if($dialogBox)
{
    claro_disp_message_box($dialogBox);
    echo "<br>";
}

switch ($display)	
{

//DEFAULT DISPLAY : display form to upload

case "default" :

    $_SESSION['claro_CSV_done'] = FALSE;
?>
You must specify the CSV format used in your file :<br><br>
<form enctype="multipart/form-data"  method="POST" action="<?php echo $_SERVER['PHP_SELF'] ?>"> 
  <input type="radio" name="firstLineFormat" value="YES" id="firstLineFormat_YES"> <label for="firstLineFormat_YES">Use format defined in first line of file</label><br><br>
  <input type="radio" name="firstLineFormat" value="NO" checked id="firstLineFormat_NO"> <label for="firstLineFormat_NO">Use the following format :</label><br><br>
    <b>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $usedFormat; ?><br><br>
    </b>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    [<a href="<?php echo $_SERVER['PHP_SELF']."?display=default&usedFormat=".$defaultFormat.""; ?>">Load default format</a>] 
    | [<a href="<?php echo $_SERVER['PHP_SELF']."?display=default&chformat=yes"; ?>">Edit format to use</a>]<br><br>
    
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

    <label for="fieldSeparator">Fields separator used</label>: 
    
    <select name="fieldSeparator" id="fieldSeparator">
      <option value=";">;</option>
      <option value=" ">(Blank space)</option>       
    </select> 
    
    
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <label for="enclosedBy">
	<?php echo $lang_fields_enclosed_by; ?> :
	</label>
    
    <select name="enclosedBy" id="enclosedBy">
      <option value="dbquote">"</option>
      <option value=",">,</option>
      <option value=".">.</option>
      <option value="">(None)</option>
      
    </select>
    
    <br><br>
    <input type="file" name="CSVfile">
    <br><br>
    <input type="submit" name="submitCSV" value="<?php echo $lang_add_user_list; ?>">
    <input type="hidden" name="cmd" value="exImp">
</form>

<?php
    break;
    
// STEP ONE DISPLAY : display the possible error with uploaded file and ask for continue or cancel
        
case "stepone" :
   
    if (!(empty($_SESSION['claro_mail_synthax_error']))       ||
        !(empty($_SESSION['claro_mail_used_error']))          ||
	!(empty($_SESSION['claro_username_used_error']))      ||
	!(empty($_SESSION['claro_officialcode_used_error']))  ||
	!(empty($_SESSION['claro_password_error']))           ||
	!(empty($_SESSION['claro_mail_duplicate_error']))     ||
	!(empty($_SESSION['claro_username_duplicate_error'])) ||
	!(empty($_SESSION['claro_officialcode_duplicate_error'])))
    {
        echo '<b>'.$lang_the_following_errors_were_found." :</b><br><br>\n";
 
	//display errors encountered while trying to add users
	
	claro_disp_CSV_error_backlog();
	$no_error = FALSE;
    }
    else 
    {
        echo $lang_no_error_in_file_found."<br>";
	$noerror = TRUE;
    }
        echo "<br>"
            .$lang_do_you_want_to_continue
			."<br>";
	if (!$noerror) 
	{
	    echo '('.$lang_if_you_choose_to_continue_lines_with_errors_will_be_simply_ignored.')<br>';        
	}
	echo "<br><form method=\"POST\" action=\"".$_SERVER['PHP_SELF']."?cmd=exImpSec\">\n";

        claro_disp_button("index.php", $langCancel); 
      
        echo "<input type=\"submit\" value=\"Continue\">\n "
            .""
            ."</form>\n";
    break;

// STEP TWO DISPLAY : display what happened, confirm users added      

case "steptwo" :
    
    echo "<b>".sizeof($usersToAdd)." new users in the platform : </b> <br><br>";

    foreach ($usersToAdd as $user)
    {
       echo $user['surname']." ".$user['name']." has been added to the campus<br>";
    }
    
    echo "<br><a href=\"adminusers.php\">&gt;&gt; See user list</a>";
    
    break;
}

//footer

include($includePath."/claro_init_footer.inc.php");
?>