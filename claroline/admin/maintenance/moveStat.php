<?php // $Id$
/*
	In the  new claroline, the  tools stat with ezboo does'nt exist anymore.
	THIS FIRST VERSION  IS DIRTY. Only  link in course home is change. Data  and  script still in course space.
	Later an  other script can  "render data to a  stactic html version"  or wash  and  forget this past.
*/

die ("deprecated");

$sqlForUpdate[] = "# stat tool have change. And is_trackingEnabled = ".$is_trackingEnabled;
$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` SET visible = '2', admin = '1', `lien` = '../claroline/tracking/courseLog.php' WHERE lien LIKE '%/stat/%'";
if ($is_trackingEnabled)
{
	$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` SET visible = '0', admin = '1', `lien` = '../claroline/tracking/courseLog.php' WHERE lien LIKE '%tracking/courseLog.ph%'";
}
else
{
	$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` SET visible = '2', admin = '1', `lien` = '../claroline/tracking/courseLog.php' WHERE lien LIKE '%tracking/courseLog.ph%'";
}
?>