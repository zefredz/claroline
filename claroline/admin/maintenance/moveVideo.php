<?php // $Id$
/*
	In the  new claroline, the  tools video does'nt exist anymore.
	the directory "video" is  moved to document.
*/

die ("deprecated");

/****** MOVE FILES **********/
$coursePath = $currentcoursePathSys;
$source = $coursePath."video";
@unlink($source."/millikan.rm");
@unlink($source."/micro.rm");
if(dir_empty($source))
{
	$sqlForUpdate[] = "# VIDEO Tool is deprecated and empty.";
	$sqlForUpdate[] = "DELETE FROM `".$currentCourseDbNameGlu."accueil` WHERE lien LIKE '%video/video.php%'";
}
else
{
	$destInDocument = "video";
	$dest = $coursePath."document/".$destInDocument;
	while (is_dir($dest)) 
	{
		$destInDocument .="_";
		$dest = $coursePath."document/".$destInDocument;
	}
	
	rename($source,$dest);
	$sqlForUpdate[] = "# VIDEO Tool is deprecated. Source : ".$source. " -&gt; ".$coursePath."document/".$destInDocument."";
	$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` SET `lien` = '../claroline/document/document.php?openDir=/".$destInDocument."' WHERE lien LIKE '%video/video.php%'";
	$sqlForUpdate[] = "INSERT `".$currentCourseDbNameGlu."document` SET `visibility` = 'i', `comment` = 'ex video tools content', `path` = '/".$destInDocument."' ";
}

?>