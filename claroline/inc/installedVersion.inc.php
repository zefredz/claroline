<?php // $Id$

$stable = false;
$clarolinePhase = "RC1";
$is_upgrade_available = true;

$clarolineVersion 	= "1.5.0.RC1";
$versionDb 			= "1.5.0.RC1";


if (!$stable)
{
	$clarolineVersion 	= $clarolineVersion.".[unstable:".date("yzBs")."]";
	$versionDb 			= $versionDb.".[unstable:".date("yzBs")."]";
}

?>