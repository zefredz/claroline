<?php // $Id$

$stable = true;
$clarolinePhase = "";
$is_upgrade_available = true;

// var version_db  max. 10 chars
// The version of database should not change in the branch release 
// examples :
//
// $version_file_cvs = "1.5.2";
// $version_db_cvs   = "1.5";
//

$version_file_cvs = "1.5.2";
$version_db_cvs   = "1.5.2";

if (!$is_upgrade_available)
{
	$version_file_cvs = $version_file_cvs .".[unstable:".date("yzBs")."]";
	$version_db_cvs	  = $version_db_cvs .".[unstable:".date("yzBs")."]";
}

// to keep compatibility the two next value are set
// but  it's same name than values in main conf.
// code would be parse to be able to remove these two lines /

$clarolineVersion = $version_file_cvs;
$versionDb = $version_db_cvs;

?>
