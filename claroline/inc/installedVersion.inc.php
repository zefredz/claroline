<?php // $Id$

$stable = false;
$clarolinePhase = "beta";
$is_upgrade_available = false;

$clarolineVersion 	= "1.5.0.beta";
$versionDb 			= "1.5.0.beta";


if (!$stable)
{
	$clarolineVersion 	= $clarolineVersion.".[unstable:".date("yzBs")."]";
	$versionDb 			= $versionDb.".[unstable:".date("yzBs")."]";
}

?>