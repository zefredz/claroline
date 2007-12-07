<?php # $Id$

/**
  UPDATE
  
  This script 
  - read current version
  - check if update of main conf is needed
          whether do it (upgrade_conf.php)
  - check if update of main db   is needed
          whether do it (upgrade_main_db.php)
  - scan course to check if update of db is needed
          whether do loop (upgrade_courses.php)
                - update course db
                - update course repository content
*/


// lang variable

include ("../../lang/english/upgrade.inc.php");

// include lib files

$newIncludePath = "../../inc/";
$oldIncludePath = "../../include/";

include ($newIncludePath."installedVersion.inc.php");
include ($newIncludePath."/lib/config.lib.inc.php");

$thisClarolineVersion 	= $version_file_cvs;
$thisVersionDb 		= $version_db_cvs;

$patternVersion    = '/^1.5/';
$patternSqlVersion = '1.5%';

/**
 Find config file.
*/

if ($fileSource=="") 
{
	$fileSource 		= $newIncludePath."conf/"."claro_main.conf.php";
}
if (!file_exists($fileSource))
{
	$fileSource 		= $oldIncludePath."config.inc.php";
}
if (!file_exists($fileSource))
{
	$fileSource 		= "../../include/config.php";
}
if (!file_exists($fileSource))
{
	$fileSource 		= $oldIncludePath."config.php";
}
if (!file_exists($fileSource))
{
	$fileSource 		= $oldIncludePath."config.inc.php.dist";
}
if ($fileTarget=="")
{
	$fileTarget 		= $newIncludePath ."conf/"."claro_main.conf.php";
}
	
@include ($fileSource); // read Values in sources

define("DISPVAL_upgrade_backup_needed",0);
define("DISPVAL_upgrade_main_db_needed",1);
define("DISPVAL_upgrade_courses_needed",2);
define("DISPVAL_upgrade_done",3);

// save confirm backup in session

session_start();

if ($_GET['reset_confirm_backup'] == 1 || $_SESSION['confirm_backup'] == 0) {
        session_unregister('confirm_backup');
        $confirm_backup = 0;
}

if (!isset($_SESSION['confirm_backup'])) 
{

//    if ($_GET['confirm_backup'] == 1 && $_GET['confirm_copy_conf'] == 1 ) 
    if ($_GET['confirm_backup'] == 1 ) 
    {
    	$_SESSION['confirm_backup'] = 1;
	$confirm_backup = 1;
    }
    else
    {
	$confirm_backup = 0;
    }
} 
else 
{
    $confirm_backup  = $_SESSION['confirm_backup'];
}

if (!$confirm_backup) 
{
	// ask to confirm backup
	$display = DISPVAL_upgrade_backup_needed;	
}
elseif (!preg_match($patternVersion,$clarolineVersion))
{
	// config file not upgraded go to first step
         header("Location: upgrade_conf.php");
}
elseif (!preg_match($patternVersion,$versionDb))
{
	// upgrade of main conf needed.
	$display = DISPVAL_upgrade_main_db_needed;
}
else
{
	// check course table to view wich course aren't upgraded
	mysql_connect($dbServer,$dbLogin,$dbPass);
	$sqlNbCourses = "SELECT count(*) as nb FROM `".$mainDbName."`.`cours`
                         where not ( versionDb like '" . $patternSqlVersion . "' )";
	$res_NbCourses = mysql_query($sqlNbCourses);
	$nbCourses = mysql_fetch_array($res_NbCourses);
	
	if ($nbCourses['nb']>0)
	{
		// upgrade of main conf needed.
		$display = DISPVAL_upgrade_courses_needed;
	}
	else
	{
		$display = DISPVAL_upgrade_done;
	}
}


?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
 
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">

<head>
  <meta http-equiv="Content-Type" content="text/HTML; charset=iso-8859-1"  />
  <title>-- Claroline upgrade -- version <?php echo $thisClarolineVersion ?></title>  
  <link rel="stylesheet" type="text/css" href="upgrade.css" media="screen" />
  <style media="print" >
    .notethis {	border: thin double Black;	margin-left: 15px;	margin-right: 15px;}
  </style>
</head>
<body bgcolor="white" dir="<?php echo $text_dir ?>">

<center>

<table cellpadding="10" cellspacing="0" border="0" width="650" bgcolor="#E6E6E6">
<tbody>
<tr bgcolor="navy">
<td valign="top" align="left">
<div id="header">
<?php
 echo sprintf("<h1>Claroline (%s) - upgrade</h1>",$thisClarolineVersion);
?>
</div>
</td>
</tr>
<tr bgcolor="#E6E6E6">
<td align="left">
<div id="content">
<?php

switch ($display)
{
	case DISPVAL_backup_needed :
                echo $langTitleUpgrade;
		echo "<form action=\"" . $_SERVER['PHP_SELF'] . "\" method=\"GET\">";
                $str1 = "<input type=\"checkbox\" id=\"confirm_backup\" name=\"confirm_backup\" value=\"1\" /><label for=\"confirm_backup\">" . $langConfirm . "</label>";
                //$str2 = "<input type=\"checkbox\" id=\"confirm_copy_conf\" name=\"confirm_copy_conf\" value=\"1\" /><label for=\"confirm_copy_conf\">" . $langConfirm . "</label>";
		echo sprintf($langMakeABackupBefore,$str1);
		echo "<div align=\"right\"><input type=\"submit\" value=\"Next >\" /></div>";
		echo "</p>";
		echo "</form>";
		break;
	case DISPVAL_upgrade_main_db_needed :
                echo $langTitleUpgrade;
		echo "<h2>$langDone:</h2>";
		echo "<ul>";	
                echo sprintf ("<li>%s (<a href=\"" . $_SERVER['PHP_SELF'] . "?reset_confirm_backup=1\">cancel</a>)</li>",$langStep0);
		echo sprintf ("<li>%s (<a href=\"upgrade_conf.php\">start again</a>)</li>",$langStep1);
		echo "</ul>";
		echo "<h2>$langTodo:</h2>";
		echo "<ul>";
		echo sprintf("<li><a href=\"upgrade_main_db.php\">%s</a></li>",$langStep2);
		echo "<li>$langStep3</li>";
		echo "</ul>";
		break;
	case DISPVAL_upgrade_courses_needed :
                echo $langTitleUpgrade;
		echo "<h2>$langDone:</h2>";
		echo "<ul>";
                echo sprintf ("<li>%s (<a href=\"" . $_SERVER['PHP_SELF'] . "?reset_confirm_backup=1\">cancel</a>)</li>",$langStep0);
		echo sprintf ("<li>%s (<a href=\"upgrade_conf.php\">start again</a>)</li>",$langStep1);
                echo sprintf ("<li>%s (<a href=\"upgrade_main_db.php\">start again</a>)</li>",$langStep2);
		echo "</ul>";
		echo "<h2>$langTodo:</h2>";
		echo "<ul>";
		echo sprintf("<li><a href=\"upgrade_courses.php\">%s</a> - %s course(s) to upgrade</li>",$langStep3,$nbCourses['nb']);
		echo "</ul>";
		break;
	case DISPVAL_upgrade_done :
                echo "<h2>$langAchieved</h2>";
                echo "<p>The <em>Claroline Upgrade Tool</em> has completly upgraded your platform.</p>";
		echo "<ul>";
		echo "<li><a href=\"../../..\">Log on to your platform</a></li>";
		echo "<li><a href=\"..\">Go to the administration section</a></li>";
		echo "</ul>";
		break;
	default : 
		echo "<p>Nothing to do</p>";
}

?>

</div>
</td>
</tr>
</tbody>
</table>


</body>
</html>
