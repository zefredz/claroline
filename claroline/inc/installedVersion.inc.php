<?php // $Id$

$stable = false;
$clarolinePhase = "RC2cvs";
$is_upgrade_available = true;

$version_file_cvs = "1.5.0.RC2cvs";
$version_db_cvs   = "1.5.0.RC2cvs";

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
