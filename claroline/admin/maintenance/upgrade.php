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

$langMakeABackupBefore = "<p>In case of trouble, we strongly recommend you to backup your previous courses data before commiting the Claroline upgrade.<br />
You must confirm the backup procedure has been done before the upgrade</p>";
$langConfirm = "Confirm";

// inclue lib files

$newIncludePath = "../../inc/";
$oldIncludePath = "../../include/";

include ($newIncludePath."installedVersion.inc.php");
include ($newIncludePath."/lib/config.lib.inc.php");

$thisClarolineVersion 	= $version_file_cvs;
$thisVersionDb 		= $version_db_cvs;

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
define("DISPVAL_upgrade_main_conf_needed",1);
define("DISPVAL_upgrade_main_db_needed",2);
define("DISPVAL_upgrade_courses_needed",3);
define("DISPVAL_upgrade_done",4);

// save confirm backup in session

session_start();

if ($_GET['confirm_backup'] == 0) {
	session_unregister('confirm_backup');
	$confirm_backup = 0;
}

if (!isset($_SESSION['confirm_backup'])) 
{
    if ($_POST['confirm_backup'] == 1 ) 
    {
    	$_SESSION['confirm_backup'] = 1;
	$confirm_backup = 1;
    }
    else
    {
	$confirm_backup = 0;
    }
} else 
{
    $confirm_backup  = $_SESSION['confirm_backup'];
}


if (!$confirm_backup) 
{
	// ask to confirm backup
	$display = DISPVAL_backup_needed;	
	
}
elseif ($thisClarolineVersion!=$clarolineVersion)
{
	// upgrade of main conf needed.upgrade_main_conf_needed
	$display = DISPVAL_upgrade_main_conf_needed;
}
elseif ($thisVersionDb!=$versionDb)
{
	// upgrade of main conf needed.
	$display = DISPVAL_upgrade_main_db_needed;
}
else
{
	// check course table to view wich course aren't upgraded
	mysql_connect($dbServer,$dbLogin,$dbPass);
	$sqlNbCourses = "
	SELECT count(*) as nb FROM ".$mainDbName.".cours 
	where not ( versionDb = '".$thisVersionDb."' )";
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
  <title>-- Claroline upgrade -- version <?php echo $clarolineVersion ?></title>  
  <link rel="stylesheet" type="text/css" href="upgrade.css" media="screen" />
  <style media="print" >
    .notethis {	border: thin double Black;	margin-left: 15px;	margin-right: 15px;}
  </style>
</head>
<body bgcolor="white" dir="<?php echo $text_dir ?>">

<div id="header">
<?php
 echo "<h1>Claroline upgrade -- version " . $clarolineVersion . "</h1>";
?>
</div>
<div id="menu">
<p>Upgrade</p>
</div>

<div id="content">

<?php

switch ($display)
{
	case DISPVAL_backup_needed :
		echo "<form action=\"" . $_SERVER['PHP_SELF'] . "\" method=\"post\">";
		echo "<p>" . $langMakeABackupBefore . "<br />";
		echo "<label for=\"confirm_backup\">" . $langConfirm . "</label><input type=\"checkbox\" id=\"confirm_backup\" name=\"confirm_backup\" value=\"1\" />";
		echo "<input type=\"submit\" value=\"OK\" />";
		echo "</p>";
		echo "</form>";
		break;
	case DISPVAL_upgrade_main_conf_needed :
		echo "<h2>Done:</h2>";
		echo "<ul>";	
		echo "<li>Backup confirmed (<a href=\"" . $_SERVER['PHP_SELF'] . "?confirm_backup=0\">cancel</a>)</li>";
		echo "</ul>";
		echo "<h2>To do:</h2>";
		echo "<ul>";
		echo "<li><a href=\"upgrade_conf.php\">Upgrade configuration files</a></li>";
		echo "<li>Upgrade main database</li>";
		echo "<li>Upgrade each courses</li>";
		echo "</ul>";
		break;
	case DISPVAL_upgrade_main_db_needed :
		echo "<h2>Done:</h2>";
		echo "<ul>";	
		echo "<li>Backup confirmed (<a href=\"" . $_SERVER['PHP_SELF'] . "?confirm_backup=0\">cancel</a>)</li>";
		echo "<li>Upgrade configuration files (<a href=\"upgrade_conf.php\">start again</a>)</li>";
		echo "</ul>";
		echo "<h2>To do:</h2>";
		echo "<ul>";
		echo "<li><a href=\"upgrade_main_db.php\">Upgrade main database</a></li>";
		echo "<li>Upgrade each courses</li>";
		echo "</ul>";
		break;
	case DISPVAL_upgrade_courses_needed :
		echo "<h2>Done:</h2>";
		echo "<ul>";
		echo "<li>Backup confirmed (<a href=\"" . $_SERVER['PHP_SELF'] . "?confirm_backup=0\">cancel</a>)</li>";
		echo "<li>Upgrade configuration files (<a href=\"upgrade_conf.php\">start again</a>)</li>";
		echo "<li>Upgrade main database (<a href=\"upgrade_main_db.php\">start again</a>)</li>";
		echo "</ul>";
		echo "<h2>To do:</h2>";
		echo "<ul>";
		echo "<li><a href=\"upgrade_courses.php\">Upgrade courses</a> - ".$nbCourses['nb']." course(s) to upgrade</li>";
		echo "</ul>";
		break;
	case DISPVAL_upgrade_done :
		echo "<h2>Done:</h2>";
		echo "<ul>";
		echo "<li>Backup confirmed (<a href=\"" . $_SERVER['PHP_SELF'] . "?confirm_backup=0\">cancel</a>)</li>";
		echo "<li>Upgrade configuration files (<a href=\"upgrade_conf.php\">start again</a>)</li>";
		echo "<li>Upgrade main database (<a href=\"upgrade_main_db.php\">start again</a>)</li>";
		echo "<li>Upgrade courses - ".$nbCourses['nb']." course(s) to upgrade (<a href=\"upgrade_courses.php\">start again</a>)</li>";
		echo "</ul>";
		echo "<h2>Go To:</h2>";
		echo "<ul>";
		echo "<li><a href=\"../../..\">Your upgraded campus</a></li>";
		echo "<li><a href=\"..\">Your admin</a></li>";
		echo "</ul>";
		break;
	default : 
		echo "<p>Nothing to do</p>";
}

?>

</div>

</body>
</html>
