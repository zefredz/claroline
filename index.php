<?php # $Id$

//----------------------------------------------------------------------
// CLAROLINE 1.5
//----------------------------------------------------------------------
// Copyright (c) 2001-2004 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see 'credits' file
//----------------------------------------------------------------------

// this script include 2 view.

 // If  user is authentified 	// The personal course list.
								// The menu bar
// Anonymous View				// The tree of course
								// The login form
								// The public menu

// Greetz PREVIEW SCRIPT BASED ON rene haentjens hack (rene.haentjens@intec.ugent.be)


/***** CONFIG Params */    // <- find this to tune the script


// Don't Change this
define("SCRIPTVAL_No",0);								// Don't Change this
define("SCRIPTVAL_InCourseList",1);						// Don't Change this
define("SCRIPTVAL_UnderCourseList",2);					// Don't Change this
define("SCRIPTVAL_Both",3);								// Don't Change this
define("SCRIPTVAL_NewEntriesOfTheDay",4);				// Don't Change this
define("SCRIPTVAL_NewEntriesOfTheDayOfLastLogin",5);	// Don't Change this
define("SCRIPTVAL_NoTimeLimit",6);						// Don't Change this

$langFile = "index";

unset($includePath);
$cidReset = true; /* Flag forcing the 'current course' reset,
                     as we're not anymore inside a course */
// Don't Change this

include("./claroline/inc/claro_init_global.inc.php");
//stats
include($includePath."/lib/events.lib.inc.php");
include($includePath."/lib/text.lib.php");
include_once($includePath."/lib/debug.lib.inc.php");
if ($logout) session_destroy();

$tbl_user   			= $mainDbName."`.`user";
$tbl_admin				= $mainDbName."`.`admin";
$tbl_courses			= $mainDbName."`.`cours";
$tbl_link_user_courses	= $mainDbName."`.`cours_user";
$tbl_courses_nodes   	= $mainDbName."`.`faculte";
$TABLETRACK_LOGIN 		= $statsDbName."`.`track_e_login";

/***** CONFIG Params*/

//******************************************* Cat LIST option
define("CONFVAL_showNodeEmpty",TRUE);
define("CONFVAL_showNumberOfChild",TRUE); // actually  count are only for direct childs
define("CONFVAL_ShowLinkBackToTopOfTree",false);


//******************************************* Course List Option
define("CONFVAL_showCourseLangIfNotSameThatPlatform",TRUE);

//******************************************* preview of course content
// to dislab all  set CONFVAL_maxTotalByCourse = 0
define("CONFVAL_maxValvasByCourse",3); // Maximum number of entries
define("CONFVAL_maxAgendaByCourse",1); //  collected from each course
define("CONFVAL_maxTotalByCourse",4); //  and displayed in summary.
define("CONFVAL_NB_CHAR_FROM_CONTENT",80);
// order to sort datas
$orderKey =array("keyTools","keyTime","keyCourse"); // Best Choice
//$orderKey =array("keyTools","keyCourse","keyTime");
//$orderKey =array("keyCourse","keyTime","keyTools");
//$orderKey =array("keyCourse","keyTools","keyTime");

define("CONFVAL_showExtractInfo",SCRIPTVAL_UnderCourseList);
									// SCRIPTVAL_InCourseList    // /best choice if $orderKey[0]="keyCourse"
									// SCRIPTVAL_UnderCourseList //best choice
									// SCRIPTVAL_Both // probably only for debug
//$dateFormatForInfosFromCourses = $dateFormatShort;
$dateFormatForInfosFromCourses = $dateFormatLong;

//define("CONFVAL_limitPreviewTo",SCRIPTVAL_NewEntriesOfTheDay);
//define("CONFVAL_limitPreviewTo",SCRIPTVAL_NoTimeLimit);
define("CONFVAL_limitPreviewTo",SCRIPTVAL_NewEntriesOfTheDayOfLastLogin);

//******************************************* right rules
$display_addCourse_Link = $is_allowedCreateCourse;
/***** End of CONFIG PArams*/


// Check config integrity
if (CONFVAL_showExtractInfo != SCRIPTVAL_UnderCourseList and $orderKey[0]!="keyCourse" )
{
	// CONFVAL_showExtractInfo must be SCRIPTVAL_UnderCourseList to accept $orderKey[0] !="keyCourse"
	if (DEBUG||$is_platformAdmin) // Show bug if admin. Else force a new order
	die("
	<strong>
	config error:".__FILE__."</strong>
	<br>
	set
	<UL>
		<LI>
			CONFVAL_showExtractInfo=SCRIPTVAL_UnderCourseList
			<small>(actually : ".CONFVAL_showExtractInfo.")</small>
		</LI>
	</UL>
	or
	<UL>
		<LI>
			\$orderKey[0] !=\"keyCourse\"
			<small>(actually : ".$orderKey[0].")</small>
		</LI>
	</UL>" );
	else
	{
		$orderKey = array("keyCourse","keyTools","keyTime");
	}

}


// no more used
//unset($alreadyHome); /* unset system that records visitor only once by course for statistics */

/*==========================
            LOGIN
  ==========================*/

if($submitAuth)
{
	// To ensure legacy compatibility, we set the following variable.
	// But it should be removed at last.

	$uid 		= $_uid;
	$nom 		= $_user['lastName' ];
	$prenom 	= $_user['firstName'];
	$email 		= $_user['mail'     ];
	$statut 	= $uData['statut'   ];
	$is_admin 	= $is_platformAdmin;

	if (isset($_uid))
	{
		$sqlLastLogin = "SELECT UNIX_TIMESTAMP(`login_date`)
				FROM `".$TABLETRACK_LOGIN."`
				WHERE `login_user_id` = '".$_uid."'
				ORDER BY `login_date` DESC LIMIT 1";

		$resLastLogin = claro_sql_query($sqlLastLogin);
		if (!$resLastLogin)
		if (mysql_num_rows($resLastLogin) > 0)
		{
			$user_last_login_datetime = mysql_fetch_array($resLastLogin);
			$user_last_login_datetime = $user_last_login_datetime[0];
			session_register('user_last_login_datetime');
		}
		mysql_free_result($resLastLogin);
		event_login();
	}
}				// end login -- if($submit)
else
{
    // only il login form not send because if the form is sent the user was
    // already on the page.
    event_open();
}


// include the HTTP, HTML headers plus the top banner
include($includePath."/claro_init_header.inc.php");


echo "<table width=\"100%\" border=\"0\" cellpadding=\"4\" >\n\n";


/*>>>>>>>>>>>> LOGGED USER SECTION <<<<<<<<<<<<*/

/*============================
    PERSONNAL COURSES LIST
  ============================*/

if (isset($_uid))
{
	if (!isset($maxValvas)) $maxValvas = CONFVAL_maxValvasByCourse; // Maximum number of entries
	if (!isset($maxAgenda)) $maxAgenda = CONFVAL_maxAgendaByCourse; //  collected from each course
	if (!isset($maxCourse)) $maxCourse = CONFVAL_maxTotalByCourse; //  and displayed in summary.

	$maxValvas = (int) $maxValvas;
	$maxAgenda = (int) $maxAgenda;
	$maxCourse = (int) $maxCourse; // 0 if invalid

	$resCourseListOfUser = claro_sql_query("SELECT cours.code k, cours.directory d, 
	                                           cours.fake_code c, cours.dbName db,
	                                           cours.intitule i, cours.titulaires t,
	                                           cours.languageCourse l,
	                                           cours_user.statut s

	                                   FROM    `".$tbl_courses."`       cours,
	                                   `".$tbl_link_user_courses."`   cours_user
	                                  
	                                  WHERE cours.code = cours_user.code_cours
	                                  AND   cours_user.user_id = '".$_uid."'");


	if ($maxCourse > 0) // && isset($_uid) - see above
	{
		unset($allentries); // we shall collect all summary$key1 entries in here:

		$toolsList['agenda']['name'] = $langAgenda;
		$toolsList['agenda']['path'] = $clarolineRepositoryWeb."calendar/agenda.php?cidReq=";
		$toolsList['valvas']['name'] = $langValvas;
		$toolsList['valvas']['path'] = $clarolineRepositoryWeb."announcements/announcements.php?cidReq=";
	}


	echo	"<tr valign=\"top\">\n",
			"<td>\n",
			"<h3>",$langMyCourses,"</h3>\n";

    echo	"<p>"
           ."<small>\n"
           ."<b>";

	if ($display_addCourse_Link) /* 'Create Course Site' command.
	                                 Only available for teacher. */
	{
		echo '<a href="claroline/create_course/add_course.php">'.$langCourseCreate.'</a>&nbsp;|&nbsp;';
	}

//	echo	"&nbsp;&nbsp;&nbsp;",
//			"<a href=\"claroline/auth/courses.php\">",$lang_edit_my_course_list,"</a>",

    echo '<a href="claroline/auth/courses.php?cmd=rqReg&category=">'.$lang_enroll_to_a_new_course.'</a>&nbsp;|&nbsp;';

//    if ($userCourseCount > 0) 
//    since i've bring up these commands the flag above
//    can't temporarly be used anymore, as it is computed latter in the script. 
//    While waiting a bit of refactoring I've commented it.
//    {
       echo '<a href="claroline/auth/courses.php?cmd=rqUnreg">'.$lang_remove_course_enrollment.'</a>';
//    }

    echo    "</b>"
            ."</small>\n"
            ."</p>\n";

	// Display courses

    $userCourseCount = 0;

	echo "<ul>\n";
    
	while ($mycours = mysql_fetch_array($resCourseListOfUser))
	{
        $userCourseCount ++;

        $thisCourseDbName     = $mycours['db'];
		$thisCourseSysCode    = $mycours['k'];
		$thisCoursePublicCode = $mycours['c'];
		$thisCoursePath       = $mycours['d'];

		$dbname               = $mycours['k'];
		$status[$dbname]      = $mycours['s'];

		$nbDigestEntries = 0; // number of entries already collected

		if ($maxCourse < $maxValvas) $maxValvas = $maxCourse;

		if ($maxCourse >0)
		{
			$courses[$thisCourseSysCode]['coursePath'] = $thisCoursePath;
			$courses[$thisCourseSysCode]['courseCode'] = $thisCoursePublicCode;
		}

	 /*--------------------------------------
	              ANNOUNCEMENTS
	   --------------------------------------*/

		if ( $maxValvas > 0) // collect from advalvas
		{
			/* SEARCH ANNOUNCEMENT
			 * Take the entries listed at the top of advalvas:
			 */

			$tableAnn = $courseTablePrefix . $thisCourseDbName . $dbGlu . "announcement";

			$sqlGetLastAnnouncements = "SELECT temps publicationDate, CONCAT(title,' ',contenu) content
			                            FROM `".$tableAnn."`
										WHERE 
										CONCAT(title,contenu) != ''
										";
	switch(CONFVAL_limitPreviewTo)
	{
		case SCRIPTVAL_NewEntriesOfTheDay :
			$sqlGetLastAnnouncements .= "AND DATE_FORMAT(temps,'%Y %m %d') >= '".date("Y m d")."'";
			break;

		case SCRIPTVAL_NoTimeLimit :
			break;

		case SCRIPTVAL_NewEntriesOfTheDayOfLastLogin	:
		// take care mysql -> DATE_FORMAT(time,format) php -> date(format,date)
			$sqlGetLastAnnouncements .= "AND DATE_FORMAT(temps,'%Y %m %d') >= '".date("Y m d",$_user["lastLogin"])."'";
	}

			$sqlGetLastAnnouncements .= "ORDER BY temps DESC
			                             LIMIT ".$maxValvas."";
			$resGetLastAnnouncements = claro_sql_query($sqlGetLastAnnouncements);

			if ($resGetLastAnnouncements)
			{
				while ($annoncement = mysql_fetch_array($resGetLastAnnouncements))
				{
					if (!(trim(strip_tags($annoncement["content"]))==""))
					{
						$keyTools 	= "valvas";
						$keyTime	= $annoncement['publicationDate'];
						$keyCourse	= $thisCourseSysCode;
                        $annoncement["content"]=strip_tags(preg_replace('/<br( \/)?>/'," ",$annoncement["content"]));
						$digest[$$orderKey[0]][$$orderKey[1]][$$orderKey[2]][] = substr($annoncement["content"],0,CONFVAL_NB_CHAR_FROM_CONTENT);
						$nbDigestEntries ++; // summary has same order as advalvas
					}
				}
			}

		}


		/*--------------------------------------
						AGENDA
		--------------------------------------*/

		$thisAgenda = $maxCourse - $nbDigestEntries; // new max entries for agenda

		if ($maxAgenda < $thisAgenda) $thisAgenda = $maxAgenda;

		if ($thisAgenda > 0) // collect from agenda
		{

			$tableCal = $courseTablePrefix . $thisCourseDbName . $dbGlu . "calendar_event";

			$sqlGetNextAgendaEvent = "SELECT  `day` , CONCAT(titre,' ',contenu) content, hour
			                          FROM `".$tableCal."`
			                          WHERE `day` >= CURDATE()
									  AND CONCAT(titre,contenu) != ''
			                          ORDER BY `day`, `hour`
			                          LIMIT ".$maxAgenda."";

			$resGetNextAgendaEvent = claro_sql_query($sqlGetNextAgendaEvent);

			if ($resGetNextAgendaEvent)
			{
				while ($agendaEvent = mysql_fetch_array($resGetNextAgendaEvent))
				{
					if (!(trim(strip_tags($agendaEvent["content"]))==""))
					{
						$keyTools 	= "agenda";
						$keyTime	= $agendaEvent['day'];
						$keyCourse	= $thisCourseSysCode;
                        $agendaEvent["content"]=strip_tags(preg_replace('/<br( \/)?>/'," ",$agendaEvent["content"]));
						$digest[$$orderKey[0]][$$orderKey[1]][$$orderKey[2]][] = substr($agendaEvent["content"],0,CONFVAL_NB_CHAR_FROM_CONTENT);
						$nbDigestEntries ++; // summary has same order as advalvas
					}
				}	
			}
			

		}

	/*--------------------------------------
	            DIGEST DISPLAY
	  --------------------------------------*/


		echo	"<li>\n",
				"<a href=\"",$coursesRepositoryWeb,$mycours['d'],"/\">",$mycours['i'],"</a><br>\n",
				"<small>",$mycours['c']," - ",$mycours['t'],"</small>\n";

		if ( (   CONFVAL_showExtractInfo == SCRIPTVAL_InCourseList
			  || CONFVAL_showExtractInfo == SCRIPTVAL_Both)
			&& $nbDigestEntries>0)
		{
			reset($digest);

			echo	"<small><small>",
					"<ul>\n";

				while ( list($key2) = each($digest[$thisCourseSysCode]))
				{
					echo	"<li>\n",
							"<strong>\n";

					if ($orderKey[1] == 'keyTools')
					{
						echo	"<a href=\"",$toolsList[$key2]["path"],$thisCourseSysCode,"\">",
								$toolsList[$key2]["name"],
								"</a>";
					}
					else
					{
						echo claro_format_locale_date($dateFormatForInfosFromCourses,strtotime($key2));
					}

					echo	"</strong>\n",
							"<ul>\n";

					reset($digest[$thisCourseSysCode][$key2]);

					while (list($key3,$dataFromCourse) = each($digest[$thisCourseSysCode][$key2]))
					{

						echo "<li>\n";

						if ($orderKey[2] == 'keyTools')
						{
							echo	"<a href=\"",$toolsList[$key3]["path"],$thisCourseSysCode,"\">",
									$toolsList[$key3]["name"],
									"</a>\n";
						}
						else
						{
							echo claro_format_locale_date($dateFormatForInfosFromCourses,strtotime($key3));
						}

						echo "<ul compact=\"compact\">\n";

						reset($digest[$thisCourseSysCode][$key2][$key3]);

						while (list($key4,$dataFromCourse) = each($digest[$thisCourseSysCode][$key2][$key3]))
						{
							echo	"<li>\n",
									substr(strip_tags($dataFromCourse),
									                                  0,CONFVAL_NB_CHAR_FROM_CONTENT),"
									</li>\n";
						}

						echo	"</ul>\n",
								"</li>\n";
					}
					
					echo	"</ul>\n",
							"</li>\n";

				}//While key 2

				echo 	"</ul>\n",
						"</small></small>\n";

			} //CONFVAL_showExtractInfo here

			echo	"</li>\n";
		}

	echo	"</ul>\n";


	echo	"</td>\n";

	// Register whether full admin or null admin course
	// by course through an array dbname x user status

	session_register('status');















































/*==========================
         RIGHT MENU
  ==========================*/
?>

<td width="200" bgcolor="#DDDEBC">

<?php

	if (   is_array($digest) 
		&& (CONFVAL_showExtractInfo == SCRIPTVAL_UnderCourseList 
		    || CONFVAL_showExtractInfo == SCRIPTVAL_Both))
	{
		// // // LEVEL 1 // // //
		reset($digest);

		echo "<small>\n";

		while( list($key1) = each($digest) )
		{
			if (is_array($digest[$key1]))
			{
				// // // Title of LEVEL 1 // // //

				echo "<b>\n";

				if ($orderKey[0] == 'keyTools')
				{
					$tools = $key1;
					echo $toolsList[$key1]['name'];
				}
				elseif ($orderKey[0] == 'keyCourse')
				{
					$courseSysCode = $key1;
					echo "<a href=\"", $coursesRepositoryWeb, $courses[$key1]['coursePath'], "\">",
					$courses[$key1]['courseCode'],"</a>\n";
				}
				elseif ($orderKey[0] == 'keyTime')
				{
					echo claro_format_locale_date($dateFormatForInfosFromCourses,strtotime($digest[$key1]));
				}

				echo "</b>\n";

				// // // End Of Title of LEVEL 1 // // //
				// // // LEVEL 2 // // //
				reset($digest[$key1]);

				while (list($key2)=each($digest[$key1]))
				{
					// // // Title of LEVEL 2 // // //
					echo	"<p>\n",
							"<small>\n";

					if ($orderKey[1] == 'keyTools')
					{
						$tools = $key2;
						echo $toolsList[$key2]['name'];
					}
					elseif ($orderKey[1] == 'keyCourse')
					{
						$courseSysCode = $key2;

						echo	"<a href=\"",$coursesRepositoryWeb,$courses[$key2]['coursePath' ],"\">",
								$courses[$key2]['courseCode' ],"</a>\n";
					}
					elseif ($orderKey[1] == 'keyTime')
					{
						echo claro_format_locale_date($dateFormatForInfosFromCourses,strtotime($key2));
					}

					echo "</small>\n";
					echo "</p>";

					// // // End Of Title of LEVEL 2 // // //
					// // // LEVEL 3 // // //
					
					reset($digest[$key1][$key2]);
					
					while (list($key3,$dataFromCourse) = each($digest[$key1][$key2]))
					{
						// // // Title of LEVEL 3 // // //

						if ($orderKey[2]== 'keyTools')
						{
							$level3title = "<a href=\"".$toolsList[$key3]["path"].$courseSysCode."\">"
							              .$toolsList[$key3]["name"]
							              ."</a>";
						}
						elseif ($orderKey[2] == 'keyCourse')
						{
							$level3title = "&#8226; <a href=\"".$toolsList[$tools]["path"].$key3."\">"
							              .$courses[$key3]['courseCode' ]
							              ."</a>\n";
						}
						elseif ($orderKey[2] == 'keyTime')
						{
							$level3title = "&#8226; <a href=\"".$toolsList[$tools]["path"].$courseSysCode."\">"
							              .claro_format_locale_date($dateFormatForInfosFromCourses,strtotime($key3))
							              ."</a>";
						}
						// // // End Of Title of LEVEL 3 // // //
						// // // LEVEL 4 (data) // // //

						reset($digest[$key1][$key2][$key3]);

						while (list($key4,$dataFromCourse) = each($digest[$key1][$key2][$key3]))
						{
							echo	$level3title," - ",
									substr(strip_tags($dataFromCourse),
									                 0,CONFVAL_NB_CHAR_FROM_CONTENT),"<br>";
						}
						// // End Of of LEVEL 4 // // //

					// // End Of of LEVEL 3 // // //
					echo "<br>";
					}
				// // End Of of LEVEL 2 // // //
				}
			}				// end if is_array($digest)
		}					// end while

		echo "</small>";

	} //  if undercourslist

?>
<div align="center">
<a href="claroline/calendar/myagenda.php"><?php echo $langSeeAgenda ?></a>
</div>
<hr noshade size="1">

<p>
<a href="#" onClick="MyWindow=window.open('claroline/help/help_claroline.php','MyWindow','toolbar=no,location=no,directories=no,status=yes,menubar=no,scrollbars=yes,resizable=yes,width=400,height=500,left=300,top=10'); return false;"><?php echo $langHelp ?></a>
</p>

<p>
<a href="http://www.claroline.net/forum/"><?php echo $langSupportForum ?></a>
</p>

<?php
//---------------------------------------------------------------------------
// 'Conseil pédagogique' link, added from a suggestion of Marcel Lebrun.
// Only valid on iCampus not for Claroline. Thomas, 30.9.2002.
//
//	if ($statut==1)
//	{
//		echo	"<p><a href=\"#\"",
//				"onClick=\"MyWindow=window.open",
//				"('conseil.htm','MyWindow','toolbar=no,location=no,directories=no,status=yes,",
//				"menubar=no,scrollbars=yes,resizable=yes,width=400,height=500,left=300,top=10');",
//				" return false;\">",$langAdvises,"</a></p>";
//	}
//---------------------------------------------------------------------------
?>

<p>
<a href="http://www.claroline.net/documentation.htm"><?= $langDoc ?></a>
</p>

<?php
	if ($is_platformAdmin) /* Admin Section links.
	                  Only available for platform administrator */
	{
?>
<hr noshade size="1">


<p><a href="claroline/admin/"><?= $langPlatformAdmin ?></a></p>

<?php
	}
?>


</td>

</tr>

</table>
<?
}	// end elseif (isset($uid)) == if visitor logged in













































/*>>>>>>>>>>>> ANONYMOUS USER SECTION (DEFAULT) <<<<<<<<<<<<*/


else
{
	echo	"<tr>\n",
			"<td valign=\"top\">\n";

	@include 'infoImportante.html'; // Previous text zone, kept for ascending 
                                    // compatibility with claroline 1.4

	@include 'textzone_top.inc.html'; // Introduction message if needed


/*==================================
  DISPLAY COURSES LIST OF A CATEGORY
  ==================================*/

if ($category!="")
{
	$sqlExpression = "   UPPER(`faculte`.`code_P`) = UPPER(\"".$category."\")
	                  OR UPPER(`faculte`.`code`)   = UPPER(\"".$category."\")";
}
else
{
	$sqlExpression = "    `faculte`.`code` IS NULL
		               OR `faculte`.`code_P` IS NULL";
}

$sqlGetCourseList = "SELECT * FROM `".$tbl_courses."` cours
					 WHERE faculte = '".$category."'
					 AND (cours.visible=\"2\" OR cours.visible=\"3\")
					 ORDER BY UPPER(fake_code)";

$sqlGetSubCatList = "SELECT `faculte`.`code`, `faculte`.`name`,
                            `faculte`.`code_P`, `faculte`.`nb_childs`,
                            COUNT( `cours`.`cours_id` ) `nbCourse`
                     FROM `".$tbl_courses_nodes."` `faculte`

                     LEFT JOIN `".$tbl_courses_nodes."` `subCat`
                     ON (    `subCat`.`treePos` >= `faculte`.`treePos`
                         AND `subCat`.`treePos` <= (`faculte`.`treePos`+`faculte`.`nb_childs`) )

                     LEFT JOIN `".$tbl_courses."` `cours`
                     ON      `cours`.`faculte` = `subCat`.`code`
				     AND (`cours`.`visible`=\"2\" OR `cours`.`visible`=\"3\")

                     WHERE ".$sqlExpression."

                     GROUP  BY `faculte`.`code`
                     ORDER BY  `faculte`.`treePos`";



$resultCourses = claro_sql_query($sqlGetCourseList);
$resCats       = claro_sql_query($sqlGetSubCatList) ;

$thereIsSubCat = FALSE;

if ( mysql_num_rows($resCats) > 0)
{
	$htmlListCat = "<h4>".$langCatList."</h4>"

	              ."<ul>";

	while ($catLine = mysql_fetch_array($resCats))
	{
		if ($catLine['code'] != $category)
		{
			$htmlListCat .= "<li>";

			if ( $catLine['nbCourse'] + $catLine['nb_childs'] > 0 )
			{
				$htmlListCat .= "<a href=\"".$_SERVER['PHP_SELF']."?category=".$catLine['code']."\">"
				               .$catLine['name']
				               ."</a>";

				if (CONFVAL_showNumberOfChild)
				{
					$htmlListCat .= " <small>(".$catLine['nbCourse'].")</small>";
				}
			}
			elseif(CONFVAL_showNodeEmpty)
			{
				$htmlListCat .= $catLine['name'];
			}

			$htmlListCat .="</li>\n";

			$thereIsSubCat = true;
		}
		else
		{
			$htmlTitre = "<p>";

			if (CONFVAL_ShowLinkBackToTopOfTree)
			{
				$htmlTitre .= "<small>"
				             ."<a href=\"$PHP_SELF\">"
							 ."&lt;&lt; ".$langBackToHomePage
				             ."</a>"
				             ."</small>";
			}

			if ( !  is_null($catLine['code_P'])
			     || (! CONFVAL_ShowLinkBackToTopOfTree
			         && ! is_null($catLine['code'] )))
			{
				$htmlTitre .= "<small>"
				             ."<a href=\"".$_SERVER['PHP_SELF']."?category=".$catLine['code_P']."\">"
				             ."&lt;&lt; ".$langUp
				             ."</a>"
				             ."</small>";
			}

			$htmlTitre .="</p>\n";

			if  ($category !="" && ! is_null($catLine['code']) )
			{
				$htmlTitre .="<h3>".$catLine['name']."</h3>\n";
			}
			else
			{
				$htmlTitre .="<h3>".$langCategories."</h3>\n";
			}
		}
	}

	$htmlListCat .="</ul>\n";
}

echo $htmlTitre;

if ($thereIsSubCat) echo $htmlListCat;


while ($categoryName = mysql_fetch_array($resCats))
{
	echo "<h3>",$categoryName['name'],"</h3>\n";
}

$numrows = mysql_num_rows($resultCourses);

if ($numrows > 0)
{
	if ($thereIsSubCat) echo "<hr size=\"1\" noshade=\"noshade\">\n";

	echo	"<h4>",$langCourseList,"</h4>\n",

	        "<ul>\n";

	while ($course = mysql_fetch_array($resultCourses))
	{
		echo	"<li>\n",

				"<a href=\"",$coursesRepositoryWeb,$course['directory'],"/\">",
				$course['intitule'],
				"</a>",
				"<br>",
				"<small class=\courseName\">",
				$course['fake_code']," - ",$course['titulaires'],
			((CONFVAL_showCourseLangIfNotSameThatPlatform && $course['languageCourse'] != $platformLanguage)?" - ".$course['languageCourse']:""),
				"</small>\n",

				"</li>\n";
	}

	echo "</ul>\n";

}
else
{
	// echo "<blockquote>",$lang_No_course_publicly_available,"</blockquote>\n";
}

if ($category!="")
echo	"<p>",
		"<small>",
		"<a href=\"$PHP_SELF\"><b>&lt;&lt;</b> ",$langBackToHomePage,"</a>",
		"</small>",
		"</p>\n";

/*---------------------------------------------------------------------------*/
	echo '</td>';
/*---------------------------------------------------------------------------*/




















































/*=================================
  RIGHT MENU MENU (IDENTIFICATION)
  =================================*/
?>

<td width="200" valign="top" bgcolor="#DDDEBC">

<form action ="<?php echo $rootWeb,basename($PHP_SELF); ?>?mon_icampus=yes" method="post">
<p>
<small>

<label for="login">
<?php echo $langUserName; ?><br>
<input type="text" name="login" id="login" size="12"><br>
</label>

<label for="password" >
<?php echo $langPass ?><br>
<input type="password" name="password" id="password" size="12"><br>
</label>
<input type="submit" value="<?php echo $langEnter ?>" name="submitAuth">

</small>
</p>
</form>

<?php
	if ($loginFailed)

		echo	"<table border=\"0\" cellpadding=\"5\">",
				"<tr><td bgcolor=\"white\">",
				"<font color=\"red\">".$langInvalidId."</font>",
				"</td></tr>",
				"</table>";

if ($allowSelfReg || !isset($allowSelfReg))
	{
echo "<p><a href=\"claroline/auth/inscription.php\">$langReg</a></p>";
	}

?>

<p><a href="claroline/auth/lostPassword.php"><?php echo $langLostPassword ?></a></p>
<p><a href="#" onClick="MyWindow=window.open('claroline/help/help_claroline.php','MyWindow','toolbar=no,location=no,directories=no,status=yes,menubar=no,scrollbars=yes,resizable=yes,width=400,height=500,left=300,top=10'); return false;" ><?= $langHelp ?></a></p>
<p><a href="http://www.claroline.net/forum/"><?php echo $langSupportForum ?></a></p>
<?php @include 'textzone_right.inc.html'; ?>

</td>
</tr>
</table>

<?
}				// end ANONYMOUS (if $logout)

/*==========================
           FOOTER
  ==========================*/

include($includePath."/claro_init_footer.inc.php");
?>
