<?php // $Id$
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.4.* INSTALL SCRIPT $Revision$              |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2003 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      | Authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
      |          Hugues Peeters    <peeters@ipm.ucl.ac.be>                   |
      |          Christophe Gesché <gesche@ipm.ucl.ac.be>                    |
      +----------------------------------------------------------------------+

	  // this code  is  manually writed but soon generated from a.sql file.
	  // this code need to contain all sql in $sqlForUpdate array to CREATE or UPDATE a cours database.
	  // this code does'nt contain sql to upgrade content
*/
//if ($singleDbEnabled) die('$singleDbEnabled is true,  we can upgrade this actually');

$sqlForUpdate[] = "# Try upgrade tables content";
$sqlForUpdate[] = "# place tools  in  home of  course";
$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` set addedTool = 'NO' where lien ='../claroline/calendar/agenda.php';";
$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` set addedTool = 'NO' where lien ='../claroline/link/link.php';";
$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` set addedTool = 'NO' where lien ='../claroline/document/document.php';";
$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` set addedTool = 'YES' where lien ='../claroline/video/video.php';";
$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` set addedTool = 'NO' where lien ='../claroline/work/work.php';";

$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` set addedTool = 'NO' where lien ='../claroline/announcements/announcements.php';";
$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` set addedTool = 'NO' where lien ='../claroline/user/user.php';";
$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` set addedTool = 'NO' where lien ='../claroline/phpbb/index.php';";
$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` set addedTool = 'NO' where lien ='../claroline/exercice/exercice.php';";
$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` set addedTool = 'NO' where lien ='../claroline/group/group.php';";
$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` set addedTool = 'YES' where lien LIKE '../claroline/stat/index2.php3%';";
$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` set addedTool = 'NO' where lien LIKE '../claroline/external_module/external_module.php?%';";
$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` set addedTool = 'NO' where lien ='../claroline/course_info/infocours.php?';";
$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` set addedTool = 'NO' where lien ='../claroline/chat/chat.php';";
$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` set addedTool = 'NO' where lien ='../claroline/course_description/';";
$sqlForUpdate[] = "# use change icons";
$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` set `image` = REPLACE(`image`,'/images/','/img/'), `address` = REPLACE(`address`,'/images/','/img/');";
$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` set `image` = REPLACE(`image`,'/image/','/img/'), `address` = REPLACE(`address`,'/image/','/img/');";
$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` set `image` = REPLACE(`image`,'../../claroline/','../claroline/'), `address` = REPLACE(`address`,'../../claroline/','../claroline/');";
$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` set `address` = CONCAT('../claroline/',`address`) WHERE NOT (LEFT(`address`,17) = '../claroline/img/');";
$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` set `image` = CONCAT('../claroline/',`image`) WHERE NOT (LEFT(`image`,17) = '../claroline/img/');";
$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` set `image` = REPLACE(`image`,'../claroline/../claroline/','../claroline/'), `address` = REPLACE(`address`,'../claroline/../claroline/','../claroline/');";
$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` set `image` = REPLACE(`image`,'../claroline/../claroline/','../claroline/'), `address` = REPLACE(`address`,'../claroline/../claroline/','../claroline/');";
$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` set `image` = REPLACE(`image`,'../claroline/../claroline/','../claroline/'), `address` = REPLACE(`address`,'../claroline/../claroline/','../claroline/');";
$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` set `image` = REPLACE(`image`,'../claroline/../claroline/','../claroline/'), `address` = REPLACE(`address`,'../claroline/../claroline/','../claroline/');";
$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` SET `image` = '../claroline/img/agenda.gif'			, address='../claroline/img/pastillegris.gif'	 WHERE lien LIKE '%agenda.ph%'";
$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` SET `image` = '../claroline/img/liens.gif'			, address='../claroline/img/pastillegris.gif'	 WHERE lien LIKE '%link.ph%'";
$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` SET `image` = '../claroline/img/documents.gif'		, address='../claroline/img/pastillegris.gif'	 WHERE lien LIKE '%document.ph%'";
$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` SET `image` = '../claroline/img/works.gif'			, address='../claroline/img/pastillegris.gif'	 WHERE lien LIKE '%work.ph%'";
$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` SET `image` = '../claroline/img/valves.gif'			, address='../claroline/img/pastillegris.gif'	 WHERE lien LIKE '%announcements.ph%'";
$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` SET `image` = '../claroline/img/membres.gif'			, address='../claroline/img/pastillegris.gif'	 WHERE lien LIKE '%user.ph%'";
$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` SET `image` = '../claroline/img/forum.gif'			, address='../claroline/img/pastillegris.gif'	 WHERE lien LIKE '%phpbb/index.ph%'";
$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` SET `image` = '../claroline/img/quiz.gif'			, address='../claroline/img/pastillegris.gif'	 WHERE lien LIKE '%exercice.ph%'";
$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` SET `image` = '../claroline/img/group.gif'			, address='../claroline/img/pastillegris.gif'	 WHERE lien LIKE '%group.ph%'";
$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` SET `image` = '../claroline/img/info.gif'			, address='../claroline/img/pastillegris.gif'	 WHERE lien LIKE '%course_description/%'";
$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` SET `image` = '../claroline/img/videos.gif'			, address='../claroline/img/pastillegris.gif'	 WHERE lien LIKE '%video/video.php%'";
$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` SET `image` = '../claroline/img/forum.gif'			, address='../claroline/img/pastillegris.gif'	 WHERE lien LIKE '%chat.ph%'";
$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` SET `image` = '../claroline/img/statistiques.gif'	, address='../claroline/img/pastillegris.gif'	 WHERE lien LIKE '%stat/index2.php%'";
$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` SET `image` = '../claroline/img/statistiques.gif'	, address='../claroline/img/pastillegris.gif'	 WHERE lien LIKE '%courseLog.ph%'";
$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` SET `image` = '../claroline/img/page.gif'			, address='../claroline/img/pastillegris.gif'	 WHERE lien LIKE '%import.ph%'";
$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` SET `image` = '../claroline/img/npage.gif'			, address='../claroline/img/pastillegris.gif'	 WHERE lien LIKE '%/external_module.ph%'";
$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` SET `image` = '../claroline/img/referencement.gif'	, address='../claroline/img/pastillegris.gif'	 WHERE lien LIKE '../claroline/course_info/infocours.ph%'";
$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` SET `image` 	= '../claroline/img/external.gif'		,`address`	= '../claroline/img/external_inactive.gif' WHERE `lien` NOT LIKE '%work.ph%' and (`image` LIKE '%travaux.pn%' OR address LIKE '%travaux.pn%')";
$sqlForUpdate[] = "# use new tools  for  external tools and link on home page";
$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` set lien=replace(replace(`lien`,'import/import_page.php','external_module/frameset_link.php'),'link=','link=".$currentcoursePathWeb."page/'),
 addedTool = 'YES' where lien LIKE '../claroline/import/import_page.php%';";
$sqlForUpdate[] = "Update `".$currentCourseDbNameGlu."accueil` set lien=replace(`lien`,'link=','link=".$currentcoursePathWeb."page/'),
 addedTool = 'YES' where lien LIKE '%frameset_link.php?link=%' AND NOT (lien LIKE '%link=http%');";
 $sqlForUpdate[] = "# remove student view (don't work anymore)";
// Some course have anold  link to self with flag student view, no more used
$sqlForUpdate[] = "DELETE FROM `".$currentCourseDbNameGlu."accueil`  where lien like '%studentview=yes%';";
$sqlForUpdate[] = "# remove Import tools ( merged with external)";
$sqlForUpdate[] = "DELETE FROM `".$currentCourseDbNameGlu."accueil` where lien LIKE '%claroline/import/import.php?%';";

// Forum
 
$sqlForUpdate[] = "# add security data";
$sqlForUpdate[] = "UPDATE `".$currentCourseDbNameGlu."forums` SET `md5` = 'Grp".md5(uniqid ( "", true))."' WHERE `md5` = '';";

// annonce

$sqlForUpdate[] = "# Import Announcement in new Announcement table";
$sqlForUpdate[] = "INSERT IGNORE INTO `".$currentCourseDbNameGlu."annonces`
 (`id`, `contenu`, `temps`, `ordre`)
 SELECT
 	`id`, `contenu`, `temps`, `ordre` FROM `".$mainDbName."`.`annonces`
	WHERE `code_cours` = @currentCourseCode";

/**
 * $Log$
 * Revision 1.1  2004/06/02 07:49:04  moosh
 * Initial revision
 *
 * Revision 1.5  2004/05/25 12:51:34  mathieu
 * add other updates
 *
 * Revision 1.4  2003/09/11 13:43:24  moosh
 * remove upgrade of a deleted tool
 *
 * Revision 1.3  2003/09/10 15:38:43  moosh
 * - use new tools  for  external tools and link on home page
 * - fill m5 field in forums and not forum
 * -  remove student view (don't work anymore)
 * - remove Import tools ( merged with external)
 *
 * Revision 1.2  2003/09/05 14:31:01  moosh
 * - remove  old  link "studen view"
 *
 * Revision 1.1  2003/09/05 13:47:42  moosh
 * use  little module to upgrade some specifica part  of  course tool on each course
 *
 * moveVideo : remove tools of video and  move documents in  document tool.
 * moveStat :  use  tracking engine instead of ezboo (don't  wash  ezboo acutally)
 * movecoursePorgram :  moove courseProgram Link in a  external link of course
 * fill md5  field  of  forum (used by groups)
 *
 */
?>
