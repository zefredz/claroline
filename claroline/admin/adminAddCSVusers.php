<?php // $Id$
//----------------------------------------------------------------------
// CLAROLINE
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


//----------------------LANG TO ADD -------------------------------------------------------------------------------


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

// Deal with interbredcrumps  and title variable

$nameTools        = "Add a user list";
$interbredcrump[] = array ("url"=>$rootAdminWeb, "name"=> "Administration");

//Header declaration

include($includePath."/claro_init_header.inc.php");

//display bredcrump and title

claro_disp_tool_title($nameTools);

//deal with session variables

if ((($cmd=="exImpSec"  || $cmd=="exImp") && $_SESSION['claro_CSV_done']) || empty($cmd)) // this is to avoid a redo because of a page reload in browser
{
    $cmd = "";
    $display = "default";
    $_SESSION['claro_CSV_done'] = FALSE;
}

//Set format used for CSV files

$defaultFormat = "surname;name;email;phone;username;password;officialCode";

if (empty($_SESSION['claro_usedFormat'])) 
{
    $_SESSION['claro_usedFormat'] = $defaultFormat;
}

if ($_REQUEST['usedFormat'])
{
    $_SESSION['claro_usedFormat'] = $_REQUEST['usedFormat'];
}

$usedFormat = $_SESSION['claro_usedFormat'];

/*-----------------------------------*/
/*	Execute command section      */
/*-----------------------------------*/

switch ($cmd)
{
    
    //STEP ONE : FILE UPLOADED, CHECK FOR POTENTIAL ERRORS
    
    case "exImp" :
    
	//store the uploaded file in a temporary dir
	
	move_uploaded_file($_FILES["CSVfile"]["tmp_name"], $uploadTempDir.$_FILES["CSVfile"]["name"]);
	
	$openfile = @fopen($uploadTempDir.$_FILES['CSVfile']['name'],"r") or die ("Impossible to open file ".$_FILES['CSVfile']['name']);
	
	//Read each ligne : we put one user in an array, and build an array of arrays for the list of user.
	
	$CSVParser = new CSV($uploadTempDir.$_FILES["CSVfile"]["name"],";",$usedFormat);
	$userlist = $CSVParser->results;
	
	//save this 2D array userlist in session
	
	$_SESSION['claro_csv_userlist'] = $userlist;
	
	// test for each user if it is addable, get possible errors messages in tables
	
	   //first, we inverse the 2D array containing the lines of CSV file just parsed 
	   //because it is much easier and faster to have line numbers of the CSV file as second indice in the array
	
	$cols[] = "surname";
	$cols[] = "name";
	$cols[] = "email";
	$cols[] = "phone";
	$cols[] = "username";
	$cols[] = "password";
	$cols[] = "officialCode";   
	
	//var_dump($_SESSION['claro_csv_userlist']);
	   
	$working2Darray = array_swap_cols_and_rows($_SESSION['claro_csv_userlist'],$cols);
	
	//look for possible new errors
	      
	$mail_synthax_error           = check_email_synthax_userlist($working2Darray);
	$mail_used_error              = check_mail_used_userlist($working2Darray);
	$username_used_error          = check_username_used_userlist($working2Darray);
	$officialcode_used_error      = check_officialcode_used_userlist($working2Darray);
	$password_error               = check_password_userlist($working2Darray);
	$mail_duplicate_error         = check_duplicate_mail_userlist($working2Darray);
	$username_duplicate_error     = check_duplicate_username_userlist($working2Darray);
	$officialcode_duplicate_error = check_duplicate_officialcode_userlist($working2Darray);
	
	//save error arrays in session (needed in second step)
	
	$_SESSION['claro_mail_synthax_error']               =  $mail_synthax_error;    
	$_SESSION['claro_mail_used_error']                  =  $mail_used_error;      
	$_SESSION['claro_username_used_error']              =  $username_used_error;   
	$_SESSION['claro_officialcode_used_error']          =  $officialcode_used_error;
	$_SESSION['claro_password_error']                   =  $password_error;
	$_SESSION['claro_mail_duplicate_error']             =  $mail_duplicate_error;
	$_SESSION['claro_username_duplicate_error']         =  $username_duplicate_error;
	$_SESSION['claro_officialcode_duplicate_error']     =  $officialcode_duplicate_error;
	
	//delete the temp file
	
	unlink($uploadTempDir.$_FILES["CSVfile"]["name"]);
	
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
        
	//notify in session that action was done
	
	$_SESSION['claro_CSV_done'] = TRUE;
	
	// select display type

	$display = "steptwo";
        
	break;    
    
}
/*-----------------------------------*/
/*	Display section              */
/*-----------------------------------*/

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

The expected format for each line of your CSV file is :<p>
<b>
<?php
if ($_REQUEST['chformat']=="yes")
{
    echo "<form metod=\"POST\" action=\"$PHP_SELF\">"
        ."  <input type=\"text\" name=\"usedFormat\" value=\"$usedFormat\" size=\"55\">"
	."  <input type=\"submit\" value=\"$langOk\""
	."</form>";
}
else
{ 
    echo $usedFormat;
}	 
?>
</b>

<form enctype="multipart/form-data"  method="POST" action="<?php echo $PHP_SELF ?>"> 
     <a href="<?php echo $_PHP_SELF."?display=default&usedFormat=".$defaultFormat.""; ?>"><small>Use default</small></a> 
     | <a href="<?php echo $_PHP_SELF."?display=default&chformat=yes"; ?>"><small>Change it</small></a>
    <br><br>
    <input type="file" name="CSVfile">
    <br><br>
    <input type="submit" name="submitCSV" value="Add user list">
    <input type="hidden" name="cmd" value="exImp">
</form>

<?php
    break;

case "chFormat" :

    echo "okokok";
    
    break;    
    
// STEP ONE DISPLAY : display the possible error with uploaded file and ask for continue or cancel
        
case "stepone" :
    
    
    if (!(empty($mail_synthax_error)) ||
        !(empty($mail_used_error)) ||
	!(empty($username_used_error)) ||
	!(empty($officialcode_used_error)) ||
	!(empty($password_error)) ||
	!(empty($mail_duplicate_error)) ||
	!(empty($username_duplicate_error)) ||
	!(empty($officialcode_duplicate_error)))
    {
        echo "<b>The following errors were found :</b><br><br>\n";
    
        /*
        var_dump($mail_synthax_error);
        var_dump($mail_used_error);
        var_dump($username_used_error);
        var_dump($officialcode_used_error);
        var_dump($password_error);
        */
    
        for ($i=0, $size=sizeof($_SESSION['claro_csv_userlist']); $i<=$size; $i++)
        {
            $line=$i+1;
	
	    if ($mail_synthax_error[$i]) 
	    {
	        echo "<b>line $line :</b> \"".$_SESSION['claro_csv_userlist'][$i]['email']."\" <b>:</b> Mail synthax error. <br>";
	    }
	      
	    if ($mail_used_error[$i])
	    {
	        echo "<b>line $line :</b> \"".$_SESSION['claro_csv_userlist'][$i]['email']."\" <b>:</b> Mail is already used by another user. <br>\n";         
	    }
	    if ($username_used_error[$i])
	    {
	        echo "<b>line $line :</b> \"".$_SESSION['claro_csv_userlist'][$i]['username']."\" <b>:</b> This username is already used by another user. <br>\n";     
	    }
	    if ($officialcode_used_error[$i])
	    {
	        echo "<b>line $line :</b> \"".$_SESSION['claro_csv_userlist'][$i]['officialCode']."\" <b>:</b> This official code is already used by another user. <br>\n"; 
	    }
	    if ($password_error[$i])
	    {
	        echo "<b>line $line :</b> \"".$_SESSION['claro_csv_userlist'][$i]['password']."\" <b>:</b> Password given to simple or to close to username. <br>\n";
	    }
	    if ($mail_duplicate_error[$i])
	    {
	        echo "<b>line $line :</b> \"".$_SESSION['claro_csv_userlist'][$i]['email']."\" <b>:</b> This mail appears already in a previous line of the CSV file. <br>\n";
	    }
	    if ($username_duplicate_error[$i])
	    {
	        echo "<b>line $line :</b> \"".$_SESSION['claro_csv_userlist'][$i]['username']."\" <b>:</b> This username appears already in a previous line of the CSV file. <br>\n";
	    }
	    if ($officialcode_duplicate_error[$i])
	    {
	        echo "<b>line $line :</b> \"".$_SESSION['claro_csv_userlist'][$i]['officialCode']."\" <b>:</b> This official code appears already in a previous line of the CSV file. <br>\n";
	    }
        }
	$no_error = FALSE;
    }
    else 
    {
        echo "No error in file found.<br>";
	$noerror = TRUE;
    }
        echo "<br>"
            ."Do you want to continue? <br>";
	if (!$noerror) 
	{
	    echo "(if you choose to continue, lines with errors will be simply ignored)<br>";        
	}
	echo "<br><form method=\"POST\" action=\"".$PHP_SELF."?cmd=exImpSec\">\n";
	
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


//used class from php.net


   class CSV
   {
       var $raw_data;
       var $new_data;
       var $mapping;
       var $results = array();
       var $errors = array();
      
       function CRLFclean()
       {
           $replace = array(
               "\n",
               "\r",
               "\r\n"
           );
           $this->raw_data = str_replace($replace,"\n",$this->raw_data);
       }
      
       function validKEY($v)
       {
           return ereg_replace("[^a-zA-Z0-9_\s]","",$v);
       }
      
       function stripENCLOSED(&$v,$eb)
       {
           if($eb!="" AND strpos($v,$eb)!==false)
             {
                   if($v[0]==$eb)
                       $v = substr($v,1,strlen($v));
                   if($v[strlen($v)-1]==$eb)
                       $v = substr($v,0,-1);
                   $v = stripslashes($v);
               }
               else
                   return;
       }

       function CSV($filename,$delim,$linedef,$enclosed_by="",$eol="\n")
           {
               //open the file
               $this->raw_data = implode("",file($filename));
              
               // make sure all CRLF's are consistent
               $this->CRLFclean();
              
               // use custom $eol (if exists)
               if($eol!="\n" AND trim($eol)=="")
                   $this->error("Couldn't split data via empty \$eol, please specify a valid end of line character.");
               else
               {
                   $this->new_data = @explode($eol,$this->raw_data);
                   if(count($this->new_data)==0)
                       $this->error("Couldn't split data via given \$eol.<li>\$eol='".$eol."'");
               }
              
               // create data keys with the line definition given in params
               
	       $temp = @explode($delim,$linedef);
	       
	       foreach($temp AS $field_index=>$field_value)
	       {            
                   $this->mapping[] = $this->validKEY($field_value); 
	       }
	       
	       // fill the 2D array using the keys given
	           
	       foreach($this->new_data AS $index1=>$line)
               {
                   $temp = @explode($delim,$line);
                  
                   if(trim($line)=="")
                       // skip empty lines
                       continue;
                   elseif(count($temp)==0)
                       // line didn't split properly so record error
                       $this->errors[] = "Couldn't split data line ".$c." via given \$delim.";
                   else
                   {
                       $data_set = array();
                       foreach($temp AS $field_index=>$field_value)
                       {
                           // Remove enclose characters
                           $this->stripENCLOSED($field_value,$enclosed_by);
                           $data_set[$this->mapping[$field_index]] = $field_value;
                   }
                   if(count($data_set)>0)
                           $this->results[] = $data_set;
                   }       
                   unset($data_set);
               }

               return $this->results;
              
       }
      
       function error($msg)
       {
           exit(
           "<hr size=1 noshade>".
           "<font color=red face=arial size=3>".
           "<h2>CSV Class Exception</h2>".
           $msg.
           "<p><b>Script Halted</b>".
           "</font>".
           "<hr size=1 noshade>"
           );
       }
      
       function help()
       {
           print(
           "<hr size=1 noshade>".
           "<font face=arial size=3>".
           "<h2>CSV Class Usage</h2>".
           "\$myVar = new CSV(\"path_to_my_file\",\"field delimeter\",\"fields enclosed by\",\"EOL character (defaults to \\n)\");<p>".
           "Output is a 2d result array (\$myVar->results)".
           "</font>".
           "<hr size=1 noshade>"
           );
       }
  }
?>