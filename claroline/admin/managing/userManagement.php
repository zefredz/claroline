<?php // | $Id$ |
//----------------------------------------------------------------------
// CLAROLINE
//----------------------------------------------------------------------
// Copyright (c) 2001-2003 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see 'credits' file
//----------------------------------------------------------------------

////////// DONT  CHANGE  anythink here
unset($stepUser) ; //  null or unset  to dislabing pagingation
unset($stepCourse) ; //  null or unset  to dislabing pagingation
define ("STATUS_ADMIN_OF_COURSE" , 1);
define ("STATUS_MEMBER_OF_COURSE", 5);
////////// DONT  CHANGE  anythink here

$langLocked = "locked";
$langExplainLock ="This user still exists but cannot log in";
$langFile = 'registration';
include("../../inc/claro_init_global.inc.php");

$interbredcrump[]= array ("url"=>$rootAdminWeb, "name"=> $langAdministrationTools);
$interbredcrump[]= array ("url"=>$rootAdminWeb."managing/", "name"=> $langManage);

$nameTools = $langUsers;
include($includePath."/conf/admin.usermanagement.conf.php");
@include($includePath."/lib/debug.lib.inc.php");
//stats
//include($includePath."/lib/events.lib.inc.php");
//event_access_tool($nameTools);

// DO NOT CHANGE THIS, CHANGE IN CONFIG AND LANG FILES
			$idColor = 1;
			$colorArr[1] 	= $colorEvenLines;
			$colorArr[-1] 	= $colorOddLines;
			// $display1colPerCourse = false <- default value

			if (isset($HTTP_GET_VARS["display1colPerCourse"]))
			{
				$display1colPerCourse = $HTTP_GET_VARS["display1colPerCourse"];
			}
			elseif (isset($HTTP_SESSION_VARS["display1colPerCourse"]))
			{
				$display1colPerCourse = $HTTP_SESSION_VARS["display1colPerCourse"];
			}

			if (!isset($display1colPerCourse))
			{
				$display1colPerCourse = false; // true -> synopsis | False -> List
			}
			// $listAllCourses4EachUser= false; <- default value
			// but  value  have no sense if $display1colPerCourse is true

			if ($display1colPerCourse)
			{
				$listAllCourses4EachUser=false;
			}
			elseif (isset($HTTP_SESSION_VARS["listAllCourses4EachUser"]))
			{
				$listAllCourses4EachUser = $HTTP_SESSION_VARS["listAllCourses4EachUser"];
			}
			elseif (!isset($listAllCourses4EachUser))
			{
				$listAllCourses4EachUser = false;
			}

			if ($listAllCourses4EachUser)
			{
				unset($stepCourse) ;
			}

			session_register("listAllCourses4EachUser","display1colPerCourse");
			$langStatusArr[0] 	= "ERROR";
			$langStatusArr[1] 	= "ERROR";
			$langStatusArr[2] 	= "ERROR";
			$langStatusArr[3] 	= "ERROR";
			$langStatusArr[4] 	= "ERROR";
			$langStatusArr[5] 	= "ERROR";
			$langStatusArr[6] 	= "ERROR";
			$langStatusArr[STATUS_ADMIN_OF_COURSE] 	= $langAdminOfCourse;
			$langStatusArr[STATUS_MEMBER_OF_COURSE] = $langSimpleUserOfCourse;
			$langTutorArr[1] = $langIsTutor;
// DO NOT CHANGE THIS, CHANGE IN CONFIG AND LANG FILES





$htmlHeadXtra[] =
"<script>
function confirmation (name)
{
	if (confirm(\" $langAreYouSureToDelete \"+ name + \" ?\"))
		{return true;}
	else
		{return false;}
}
</script>
<script src=\"".$phpMyAdminWeb."libraries/functions.js\" type=\"text/javascript\" language=\"javascript\"></script>
";

$editOtherUsersAllowed	=	$is_platformAdmin || $PHP_AUTH_USER;

$tbl_courses		= $mainDbName."`.`cours";
$TABLECOURS 		= $tbl_courses;
$TABLEUSER 			= $mainDbName."`.`user";
$tbl_user			= $TABLEUSER;
$TABLECOURSUSER		= $mainDbName."`.`cours_user";
$tbl_cours_user  	= $TABLECOURSUSER;
$result 			= mysql_query("SELECT user.user_id FROM `$TABLEUSER` `user`");
$userNb 			= mysql_num_rows($result);
$courseResult 		= mysql_query("SELECT `cours`.`cours_id` FROM `$TABLECOURS` `cours`");
$courseNb 			= mysql_num_rows($courseResult);


/////////////////////////////////////////////////////////////////////////



///////////////////////// ACTION FROM POST //////////////////////////////



/////////////////////////////////////////////////////////////////////////

if($editOtherUsersAllowed)
{
	// Unregister user from course
	// (notice : it does not delete user from claroline main DB)

	if($HTTP_POST_VARS["remove"])
	{
		if (is_array($HTTP_POST_VARS["actionOnUser"]))
		{

			$sql =	"SELECT `cours`.`dbName`
					FROM `".$TABLECOURS."` `cours`
					WHERE `cours`.`code` = '$cidToEdit'";

			$result = mysql_query($sql)  or die ("WARNING !! DB QUERY FAILED ! ".__LINE__);

			if (mysql_num_rows($result)>0)
			{
				$thisCourse = mysql_fetch_array($result,MYSQL_ASSOC);
				$_course['dbNameGlu'   ]         = $courseTablePrefix . $thisCourse['dbName'] . $dbGlu; // use in all queries
			}

			$TABLEGROUP     		= $_course['dbNameGlu']."user_group";
			$TABLESTUDENTGROUP 		= $_course['dbNameGlu']."student_group";
			$tbl_student_group		= $TABLESTUDENTGROUP;
			$tbl_user_group 		= $TABLEGROUP;
			$tbl_group_properties	= $_course['dbNameGlu']."group_properties";

			foreach($HTTP_POST_VARS["actionOnUser"] as $user_id_to_remove)
			{
				remove_user_from_platform($user_id_to_remove);
			}
		}
		else
		{
		}


	}
	elseif($HTTP_POST_VARS["lock"])
	{
		if (is_array($HTTP_POST_VARS["actionOnUser"]))
		{
			$tbl_user = $mainDbName."`.`user";
			foreach($HTTP_POST_VARS["actionOnUser"] as $user_id_to_lock)
			{
				$sqlLockUsers = "UPDATE `".$tbl_user."` SET
				`username` = CONCAT(' ',LTRIM(`username`)),
				`password` = CONCAT(' ',LTRIM(`password`))
				WHERE `user_id` =  '".$user_id_to_lock."'";
				@mysql_query($sqlLockUsers);
			}
		}
		else
		{
		}
	}
	elseif($HTTP_POST_VARS["unLock"])
	{
		if (is_array($HTTP_POST_VARS["actionOnUser"]))
		{
			$tbl_user = $mainDbName."`.`user";
			foreach($HTTP_POST_VARS["actionOnUser"] as $user_id_to_unlock)
			{
				$sqlUnlockUsers = "UPDATE `".$tbl_user."` SET
				`username` = LTRIM(`username`),
				`password` = LTRIM(`password`)
				WHERE `user_id` =  '".$user_id_to_unlock."'";
				@mysql_query($sqlUnlockUsers);
			}
		}
		else
		{


		}
	}
	elseif($HTTP_POST_VARS["update"])
	{
		//BIG WORK//
		$sqlCoursesOfUser = "
			SELECT `courses_user`.code_cours code_cours, `user_id`
				FROM `$tbl_cours_user` `courses_user`
			WHERE	`user_id` in ('".implode("','",$HTTP_POST_VARS["usersPool"])."')
				AND	code_cours  in ('".implode("','",$HTTP_POST_VARS["coursePool"])."')";
			$resCourseOfUserBefore = @mysql_query($sqlCoursesOfUser);
			if (mysql_errno() > 0) echo mysql_errno().": ".mysql_error()." &lt;- $sqlCoursesOfUser (".__LINE__.") <br>";
			while ($courseOfUserBefore =  mysql_fetch_array($resCourseOfUserBefore,MYSQL_ASSOC))
			{
				$listCoursesBefore[$courseOfUserBefore["user_id"]][$courseOfUserBefore["code_cours"]]=true;
			};
			mysql_free_result($resCourseOfUserBefore);

		while (list(,$uidToEdit)=each($HTTP_POST_VARS["usersPool"]))
		{
			reset($HTTP_POST_VARS["coursePool"]);
			while (list(,$cidToEdit)=each($HTTP_POST_VARS["coursePool"]))
			{
				if ($HTTP_POST_VARS["changeUserInCourse"][$uidToEdit][$cidToEdit])
				{
					if ($listCoursesBefore[$uidToEdit][$cidToEdit])
					{
						//no change
//						if ($DEBUG) echo "<br>no change for [$uidToEdit][$cidToEdit]";
					}
					else
					{
//						if ($DEBUG) echo "<br>enroll [$uidToEdit][$cidToEdit]";

						$sql =	"SELECT `cours`.`dbName`
								FROM `".$TABLECOURS."` `cours`
								WHERE `cours`.`code` = '$cidToEdit'";

						$result = mysql_query($sql)  or die ("WARNING !! DB QUERY FAILED ! ".__LINE__);

						if (mysql_num_rows($result)>0)
						{
							$thisCourse = mysql_fetch_array($result,MYSQL_ASSOC);
							$_course['dbNameGlu'   ]         = $courseTablePrefix . $thisCourse['dbName'] . $dbGlu; // use in all queries
						}

						$TABLEGROUP     		= $_course['dbNameGlu']."user_group";
						$TABLESTUDENTGROUP 		= $_course['dbNameGlu']."student_group";
						$tbl_student_group		= $TABLESTUDENTGROUP;
						$tbl_user_group 		= $TABLEGROUP;
						$tbl_group_properties	= $_course['dbNameGlu']."group_properties";

						enroll_user_to_course($uidToEdit,$cidToEdit, STATUS_MEMBER_OF_COURSE);
					}
				}
				else
				{
					if ($listCoursesBefore[$uidToEdit][$cidToEdit])
					{
						$sql =	"SELECT `cours`.`dbName`
								FROM `".$TABLECOURS."` `cours`
								WHERE `cours`.`code` = '$cidToEdit'";

						$result = mysql_query($sql)  or die ("WARNING !! DB QUERY FAILED ! ".__LINE__);

						if (mysql_num_rows($result)>0)
						{
							$thisCourse = mysql_fetch_array($result,MYSQL_ASSOC);
							$_course['dbNameGlu'   ]         = $courseTablePrefix . $thisCourse['dbName'] . $dbGlu; // use in all queries
						}

						$TABLEGROUP     		= $_course['dbNameGlu']."user_group";
						$TABLESTUDENTGROUP 		= $_course['dbNameGlu']."student_group";
						$tbl_student_group		= $TABLESTUDENTGROUP;
						$tbl_user_group 		= $TABLEGROUP;
						$tbl_group_properties	= $_course['dbNameGlu']."group_properties";
						remove_user_from_course($uidToEdit,$cidToEdit);
						//if ($DEBUG) echo "<br>remove [$uidToEdit][$cidToEdit]";
					}
					else
					{
						//if ($DEBUG) echo "<br>no change for [$uidToEdit][$cidToEdit]";
						//no change
					}
				}
			}
		}
	}
}	// end if allowed to edit









// Build PAGER - don't show the pager link if there are less than $stepUser user
//// USERS
if (isset($stepUser))
{
	if ($userNb > $stepUser)
	{
		if(!isset($offsetUser))
		{
			$offsetUser=0;
		}

		$next     = $offsetUser + $stepUser;
		$previous = $offsetUser - $stepUser;

		$navLinkUser = "<table width=\"100%\" border=\"0\">\n"
				."<tr>\n"
				."<td align=\"left\">";

		if ($previous >= 0)
		{
			$navLinkUser .= "<small><a href=\"$PHP_SELF?offsetUser=$previous\">&lt;&lt; ".$stepUser." utilisateurs précédents </a></small>";
		}
		else
		{
			$navLinkUser .= "<small>Debut de liste des utilisateurs</small>";
		}

		$navLinkUser .= "</td>\n"
				."<td align=\"right\">";

		if ($next < $userNb)
		{
			$navLinkUser .= "<small><a href=\"$PHP_SELF?offsetUser=$next\">".$stepUser." utilisateurs suivants &gt;&gt;</a></small>";
		}
		else
		{
			$navLinkUser .= "<small>Fin de liste des utilisateurs</small>";
		}

		$navLinkUser .= "</td>\n"
				."</tr>\n"
				."</table>\n";
	}
	else
	{
		$offsetUser = 0;
	}
	$limitSelectUser = "LIMIT $offsetUser, $stepUser";
	session_register("offsetUser");
}
else
{
	$limitSelectUser = "";
	unset($offsetUser);
}



/////////////////////////////////// COURSES////////////////
if (isset($stepCourse))
{
	if ($courseNb > $stepCourse)
	{
		if(!isset($offsetCourse))
		{
			$offsetCourse=0;
		}

		$next     = $offsetCourse + $stepCourse;
		$previous = $offsetCourse - $stepCourse;

		$navLinkCourse = "<table width=\"100%\" border=\"0\">\n"
				."<tr>\n"
				."<td align=\"left\">";

		if ($previous >= 0)
		{
			$navLinkCourse .= "<small><a href=\"$PHP_SELF?offsetCourse=$previous\">&lt;&lt; ".$stepCourse." cours précédents</a></small>";
		}
		else
		{
			$navLinkCourse .= "<small>Debut de liste des cours</small>";
		}

		$navLinkCourse .= "</td>\n"
				."<td align=\"right\">";

		if ($next < $courseNb)
		{
			$navLinkCourse .= "<small><a href=\"$PHP_SELF?offsetCourse=$next\">".$stepCourse." cours suivants &gt;&gt;</a></small>";
		}
		else
		{
			$navLinkCourse .= "<small>Fin de liste des cours</small>";
		}

		$navLinkCourse .= "</td>\n"
				."</tr>\n"
				."</table>\n";
	}
	else
	{
		$offsetCourse = 0;
	}
	$limitSelectCourse = "LIMIT $offsetCourse, $stepCourse";
	session_register("offsetCourse");
}
else
{
	$limitSelectCourse = "";
	unset($offsetCourse);
}
////////////////////////END OF PAGER/////////////////////////////////



///////////////////////////////
//Build content of cells
$usersResult = mysql_query("SELECT `user_id`, `nom`, `prenom`, `username`, `password`
					FROM `$TABLEUSER`
					ORDER BY `nom`, `prenom`
					".$limitSelectUser);
$courseResult = mysql_query("SELECT `cours`.`code` `sysCode`,`cours`.`fake_code` `officialCode`, `cours`.`directory` `path` FROM `$TABLECOURS` `cours` ORDER BY `officialCode` ".$limitSelectCourse);
$courseNb = mysql_num_rows($courseResult);

$usersInCoursesResult = mysql_query("
SELECT `code_cours` `sysCode`, `user_id` `uid`,  `statut`, `tutor`, `role`
FROM `$TABLECOURSUSER` ORDER BY `sysCode` ");


while ($thisCourse = mysql_fetch_array($usersInCoursesResult,MYSQL_ASSOC))
{
	$userInCourse[$thisCourse["uid"]][$thisCourse["sysCode"]]["member"] 	= true;
	$userInCourse[$thisCourse["uid"]][$thisCourse["sysCode"]]["role"] 		= $thisCourse["role"];
	$userInCourse[$thisCourse["uid"]][$thisCourse["sysCode"]]["statut"] 	= $thisCourse["statut"];
	$userInCourse[$thisCourse["uid"]][$thisCourse["sysCode"]]["tutor"] 		= $thisCourse["tutor"];
}


while ($thisCourse = mysql_fetch_array($courseResult,MYSQL_ASSOC))
{
 	if ($thisCourse["sysCode"]!="") // I forget why this test ;((((
	{
		$courseList[] = array(	"officialCode" 	=> $thisCourse["officialCode"],
								"sysCode" 		=> $thisCourse["sysCode"],
								"path" 			=> $thisCourse["path"]);
	}
}

//////////////////////////////////////////
//////////////BEGIN OUTPUT
//////////////////////////////////////////

include($includePath."/claro_init_header.inc.php");
claro_disp_tool_title(
	array(
	'mainTitle'=>$nameTools,
	'subTitle'=>"(".$langUserNumber." : ".$userNb.")"
	)
	);

echo $navLinkUser;
echo $navLinkCourse;

// NOTE !! NEED ALSO 'SHOW ALL USERS'

// Numerating the items in the list to show: starts at 1 and not 0
$i= $startList + 1;


// OUTPUT THE  FORM
echo "<FORM METHOD=\"POST\" ACTION=\"$PHP_SELF?sqlQueries=yes\">";
//echo "<FORM METHOD=\"POST\" ACTION=\"../devTools/echoPost.php\">";
/*==========================
      ADD NEW USER
  ==========================*/
$addNewUserLink="<a href=\"".$clarolineRepositoryWeb."user/user_add.php\">".$langAddNewUser."</a>";
echo "&nbsp; &nbsp;";
echo $addNewUserLink;
echo "<BR><BR>";


/*==========================
      USERS LIST DISPLAY
  ==========================*/
/////// 3 View available.
// $display1colPerCourse True
// $display1colPerCourse False and $listAllCourses4EachUser True
// $display1colPerCourse False and $listAllCourses4EachUser False


/*----------------------------------------
              COLUMN HEADERS
 --------------------------------------*/

echo "<hr>";
echo 	"
<table width=\"100%\" cellpadding=\"2\" cellspacing=\"1\" border=\"0\">\n";
$headerLine = "
	<tr  align=\"center\" valign=\"top\" bgcolor=\"".$headerBgColor."\">
		<th align=\"center\" valign=\"top\" bgcolor=\"".$colorUserAction."\">
		</th>
		<th align=\"center\" valign=\"top\">
			Id
		</th>
		<th align=\"center\" valign=\"top\">
			".$langUserName."
		</th>";

if ($display1colPerCourse)
{
	reset($courseList);
	while (list(,$thisCourse) = each($courseList))
	{
		$headerLine .= "
		<th align=\"center\" valign=\"top\">
			<a href=\"".$urlUnderCoursesNamesBefore.$thisCourse["path"].$urlUnderCoursesNamesAfter."\" >".writeDir($thisCourse["officialCode"],$dirTextInHeaders)."</a>
		</th>";
	}
}
else
{

	$headerLine .= "
		<TH>
			".$langCourseCode."
		</TH>
		<TH>
			".$langParamInTheCourse."
		</TH>";
}

$headerLine .= "
	</TR>";


echo "<PRE>".$headerLine."</PRE>" ;

$i=0;
while ($thisUser = mysql_fetch_array($usersResult,MYSQL_ASSOC))
{
// User name column
$listUserId=$thisUser['user_id'];


	// Close table line and start new one

	if ($display1colPerCourse)
	{
		$courseNbToShow = 1;
	}
	elseif ($listAllCourses4EachUser)
	{
		$courseNbToShow = $courseNb;
	}
	else
	{
		$courseNbToShow = count($userInCourse[$thisUser['user_id']]);
	}

	$idColor *=-1;
	$bgColorOfTR = $colorArr[$idColor];

	echo	"
	<tr bgcolor=\"",$bgColorOfTR,"\"";
	if ($display1colPerCourse)
	{
		echo	"onmouseover=\"setPointer(this, $listUserId, 'over', '",$bgColorOfTR,"', '",$colorHoverLines,"', '",$colorClickedLines,"');\"	onmouseout=\"setPointer(this, $listUserId, 'out', '",$bgColorOfTR,"', '",$colorHoverLines,"', '",$colorClickedLines,"');\"	onmousedown=\"setPointer(this, $listUserId, 'click', '",$bgColorOfTR,"', '",$colorHoverLines,"', '",$colorClickedLines,"');\"";
	}
	echo " >
		<td  bgcolor=\"",$colorUserAction,"\" rowspan=\"",$courseNbToShow,"\"  valign=\"top\">
			<INPUT tabindex=\"".(1000+$tabindexactionOnUser++)."\" TYPE=\"checkbox\" NAME=\"actionOnUser[]\" value=\"".$listUserId."\">";
		if ($thisUser['username'][0]==" " && $thisUser['password'][0]==" " )
		{
			echo "<img src=\"".$clarolineRepositoryWeb."/img/lock_topic.gif\" alt=\"".$langLocked."\" border=\"0\">";
		}
		echo "
		</td>
		<td rowspan=\"",$courseNbToShow,"\" valign=\"top\">
			",$listUserId,"
		</td>
		<td rowspan=\"",$courseNbToShow,"\"  valign=\"top\">
			<a href=\"adminProfile.php?uidToEdit=",$thisUser['user_id'],"\">","\n\t",
			$thisUser['nom']," ",$thisUser['prenom'],
			"</a>
			<input type=\"hidden\" name=\"usersPool[]\" value=\"",$thisUser['user_id'],"\">
			</td>";

	if ($display1colPerCourse)
		for ($col = 0; $col < $courseNb; $col++)
		{
			echo "<td valign=\"top\">";
			if ($showCheckBox)
			{
				if($editSubscription)
				{
					$checked = $userInCourse[$thisUser["user_id"]][$courseList[$col]["sysCode"]]["member"]?"checked":"";
					echo "<input type=\"checkbox\"  name=\"changeUserInCourse[".$thisUser['user_id']."][".$courseList[$col]["sysCode"]."]\" value=\"checked\" ".$checked.">";
				}
				elseif($editStatut)
				{
					if ($userInCourse[$thisUser["user_id"]][$courseList[$col]["sysCode"]]["member"])
					{
						if ($userInCourse[$thisUser["user_id"]][$courseList[$col]["sysCode"]]["statut"]==1)
						{
							echo "<input type=\"checkbox\"  name=\"changeUserInCourse[".$thisUser['user_id']."][".$courseList[$col]["sysCode"]."]\" value=\"checked\" ".$checked.">";
						}
					}
				}
				elseif($editTutor)
				{
					if ($userInCourse[$thisUser["user_id"]][$courseList[$col]["sysCode"]]["member"])
					{
						if ($userInCourse[$thisUser["user_id"]][$courseList[$col]["sysCode"]]["tutor"]==1)
						{
							echo "<input type=\"checkbox\"  name=\"changeUserInCourse[".$thisUser['user_id']."][".$courseList[$col]["sysCode"]."]\" value=\"checked\" ".$checked.">";
						}
					}
				}
			}
			if ($showIfMember && $userInCourse[$thisUser["user_id"]][$courseList[$col]["sysCode"]]["member"])
				echo "<br>",$langMember;
			if ($showStatut )
				echo "<br>",$langStatusArr[$userInCourse[$thisUser["user_id"]][$courseList[$col]["sysCode"]]["statut"]];
			if ($showIfTutor)
				echo "<br>",$langStatusArr[$userInCourse[$thisUser["user_id"]][$courseList[$col]["sysCode"]]["tutor"]];
			echo "</td>";
		}
	else
	{
		$firstTRisEmpty ="";
		if ($listAllCourses4EachUser)
		{
			for ($col = 0; $col < $courseNb; $col++)
			{
				echo    $firstTRisEmpty;
				$firstTRisEmpty = "\n\t<tr bgcolor=\"".$bgColorOfTR."\">";
				echo 	"\n\t\t","<td><!-- cours ",$col," -->",
						"<a href=\"",$urlUnderCoursesNames,$thisCourse,"/\"  >",$courseList[$col]["sysCode"],"</a>",
						"\n\t\t</td>\n\t\t<td>";
				if ($showCheckBox)
				{
					if($editSubscription)
					{
						$checked = $userInCourse[$thisUser["user_id"]][$courseList[$col]["sysCode"]]["member"]?"checked":"";
						echo "<input type=\"checkbox\"  name=\"changeUserInCourse[".$thisUser['user_id']."][".$courseList[$col]["sysCode"]."]\" value=\"checked\" ".$checked.">";
					}
					elseif($editStatut)
					{
						if ($userInCourse[$thisUser["user_id"]][$courseList[$col]["sysCode"]]["member"])
						{
							if ($userInCourse[$thisUser["user_id"]][$courseList[$col]["sysCode"]]["statut"]==1)
							{
								echo "<input type=\"checkbox\"  name=\"changeUserInCourse[".$thisUser['user_id']."][".$courseList[$col]["sysCode"]."]\" value=\"checked\" ".$checked.">";
							}
						}
					}
					elseif($editTutor)
					{
						if ($userInCourse[$thisUser["user_id"]][$courseList[$col]["sysCode"]]["member"])
						{
							if ($userInCourse[$thisUser["user_id"]][$courseList[$col]["sysCode"]]["tutor"]==1)
							{
								echo "<input type=\"checkbox\"  name=\"changeUserInCourse[".$thisUser['user_id']."][".$courseList[$col]["sysCode"]."]\" value=\"checked\" ".$checked.">";
							}
						}
					}
				}
				if ($showIfMember && $userInCourse[$thisUser["user_id"]][$courseList[$col]["sysCode"]]["member"])
					echo " ",$langMember;
				if ($showStatut )
					echo " ",$langStatusArr[$userInCourse[$thisUser["user_id"]][$courseList[$col]["sysCode"]]["statut"]];
				if ($showIfTutor)
					echo " ",$langStatusArr[$userInCourse[$thisUser["user_id"]][$courseList[$col]["sysCode"]]["tutor"]];
				echo "\n\t\t</td>\n\t</TR>";
			}
		}
		else
		{
			$coursesForThisUser = $userInCourse[$thisUser['user_id']];
			if (is_array($coursesForThisUser))
			{
				while (list($codeForThisCourse,$statusForThisCourse) = each ($coursesForThisUser))
				{
					echo "<!-- Liste des cours -->", $firstTRisEmpty;
					$firstTRisEmpty = "\n\t<TR  bgcolor=\"".$bgColorOfTR."\">";
					echo 	"\n\t\t","<td><!-- cours ",$codeForThisCourse," -->",
							"<b>",$codeForThisCourse,"</b>",
							"\n\t\t</td>\n\t\t<td>";

					if ($showCheckBox)
					{
						if($editSubscription)
						{
							$checked = $userInCourse[$thisUser["user_id"]][$codeForThisCourse]["member"]?"checked":"";
							echo "<input type=\"checkbox\"  name=\"changeUserInCourse[".$thisUser['user_id']."][".$codeForThisCourse."]\" value=\"checked\" ".$checked.">";
						}
						elseif($editStatut)
						{
							if ($userInCourse[$thisUser["user_id"]][$codeForThisCourse]["member"])
							{
								if ($userInCourse[$thisUser["user_id"]][$codeForThisCourse]["statut"]==1)
								{
									echo "<input type=\"checkbox\"  name=\"changeUserInCourse[".$thisUser['user_id']."][".$codeForThisCourse."]\" value=\"checked\" ".$checked.">";
								}
							}
						}
						elseif($editTutor)
						{
							if ($userInCourse[$thisUser["user_id"]][$codeForThisCourse]["member"])
							{
								if ($userInCourse[$thisUser["user_id"]][$codeForThisCourse]["tutor"]==1)
								{
									echo "<input type=\"checkbox\"  name=\"changeUserInCourse[".$thisUser['user_id']."][".$codeForThisCourse."]\" value=\"checked\" ".$checked.">";
								}
							}
						}
					}
					if ($showIfMember && $userInCourse[$thisUser["user_id"]][$codeForThisCourse]["member"])
						echo " ",$langMember;
					if ($showStatut )
						echo " ",$langStatusArr[$userInCourse[$thisUser["user_id"]][$codeForThisCourse]["statut"]];
					if ($showIfTutor)
						echo " ",$langTutorArr[$userInCourse[$thisUser["user_id"]][$codeForThisCourse]["tutor"]];
								"\n\t\t</td>\n\t</TR>";
				}
			}
			else
			{
				echo "<td colspan=\"2\">",$langHaveNoCourse,"</td></TR>";

			}
		}
	}

echo "</tr>";

	$i++;
}	// END - while fetch_array



/*==========================
      VALIDATE BUTTON
  ==========================*/

$removeOfClarolineButton	= "<INPUT TYPE=\"submit\" name=\"remove\" value=\"$langDelete\">";
$lockUserButton				= "<INPUT TYPE=\"submit\" name=\"lock\" value=\"$langLock\">";
$unlockUserButton			= "<INPUT TYPE=\"submit\" name=\"unLock\" value=\"$langUnlock\">";
$updateSubscriptionButton	= "<INPUT TYPE=\"submit\" name=\"update\" value=\"$langOk\">";

// VALIDATE + ADD NEW USER SHOW
echo "
	<TR>
		<TD colspan=\"1\"  bgcolor=\"",$colorUserAction,"\">
		</TD>
		<TD align=\"right\" valign=\"top\" colspan=\"",(2+$courseNb),"\">
			<HR noshade size=\"1\">";
if($showUpdateSubscriptionButton)
{
	echo "
			",$updateSubscriptionButton,"<br>";
}
echo "
			<br>
		</TD>
	</TR>
	<TR>
		<TD  bgcolor=\"",$colorUserAction,"\" valign=\"top\"  col span=\"",(3+$courseNb),"\">";


if($showRemoveOfClarolineButton)
{
	echo "
			",$removeOfClarolineButton,"<br>";
}
if($showLockUnlockUserButton)
{
	echo "
			",$lockUserButton,"
			<br>
			",$unlockUserButton,"
			<br>";
}
	echo "
		</TD>
		<TD colspan=\"".$courseNb."\">";
if($showLockUnlockUserButton)
{
	echo "
			<img src=\"".$clarolineRepositoryWeb."/img/lock_topic.gif\" alt=\"".$langLocked."\" border=\"0\">
			 = ",$langExplainLock;
}
echo "
		</TD>
	</TR>";

echo "</table>\n
",$addNewUserLink,"";

if(isset($stepCourse))
{
	reset($courseList);
	while (list(,$thisCourse) = each($courseList))
	{
		echo	"<input type=\"hidden\" name=\"coursePool[]\" value=\"",$thisCourse["sysCode"],"\">\n";
	}
}else
{

	echo	"<input type=\"hidden\" name=\"coursePool\" value=\"\">\n";
}

echo "</FORM>";

echo $navLinkUser;
echo $navLinkCourse;
?>
<HR>
View : <a href="?display1colPerCourse=1">Synoptic</a> <a href="?display1colPerCourse=0&listAllCourses4EachUser=0">Short</a> <a href="?display1colPerCourse=0&listAllCourses4EachUser=1">Extended</a>
<?
include($includePath."/claro_init_footer.inc.php");

function writeDir($stringToOutput,$dir="H")
{
	if ($dir=="V")
	{
		return wordwrap($stringToOutput,1,"<BR>",true);
	}
	else
	{
		return $stringToOutput;
	}
}

/**
 * Enrolls a user to a course
 * @param int    $uid,
 *        string $cid,
 *        int    $status,
 *        int    $tutor,
 *        string $role
 *
 * @return boolean true if it suceeds
 *                 false otherwise
 */

function enroll_user_to_course($uid, $cid, $status, $tutor = "", $role = "")
{
	global $tbl_cours_user;

	/*
	 * Previously check the this user id isn't already enrolled to this course id
	 */

	$result = sql_query("SELECT * FROM `$tbl_cours_user` WHERE `user_id`=\"".$uid."\" AND code_cours=\"".$cid."\"");

	if($result)
	{
		if (mysql_num_rows($result) > 0) return error("user already enrolled to the course",__FILE__,__LINE__);

		/*
		* Insert the user enrollment into the table
		*/
		$result = sql_query("INSERT INTO `".$tbl_cours_user."`
							SET user_id = '".$uid."',
							code_cours     = '".$cid."',
							statut  = '".$status."'");
		if($result)
		{
			return true;
		}
	}
	else
	{
		echo "---SELECT * FROM `$tbl_cours_user` WHERE `user_id`=\"".$uid."\" AND code_cours=\"".$cid."\"---";
		return error("user already enrolled to the course");
	}
}

 /**
  * removes a user from one or all courses
  * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
  * @param int $uid,
  *        string/array $cid (optionnal)
  * @return boolean true if succeed,
  *                 false otherwise
  */

function remove_user_from_course($uid, $cid="")
{
    global $tbl_cours_user;
	if ($cid=="ALL" || $cid=="")
    {
		$getCourseOfUser = "
		SELECT `code_cours` as `code`
			FROM `$tbl_cours_user`
			WHERE `user_id`=\"".$uid."\"";
		$result = sql_query($getCourseOfUser);
		if($result)
		{
			while ($course = mysql_fetch_array($result,MYSQL_ASSOC))
			{
				$courseList[] = $course['code'];
			}
		}
	}
	elseif(is_array($cid))
	{
		$courseList = $cid;
	}
	elseif(isset($cid))
	{
		$courseList[] = $cid;
	}
	if (is_array($courseList))
	{
		$endQuery   = " AND code_cours IN (\"". implode("\", \"",$courseList) ."\")";
		$removeFromGroups = true;
		foreach ($courseList as $thisCourse)
		{
			if (!remove_user_from_group($uid, $thisCourse))
			$removeFromGroups = false;
		}

		if ($removeFromGroups)
		{
			$result = sql_query("DELETE FROM `$tbl_cours_user`
				WHERE `user_id` = \"$uid\" $endQuery ");
		}
		else
		{
			$result = false;
		}
	}
	else
	{
		$result = true;
	}
	if ($result)
	{
		return true;
	}
	else
	{
		return false;
	}

}


/**
 * remove a user from a group
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param  int $uid
 *         string $cid
 *         int/string/array $gid (optionnal)
 */

function remove_user_from_group($uid, $cid, $gid="")
{
	global $tbl_student_group, $tbl_user_group, $tbl_group_properties;

	if ($uid=="")
	{
		die ("(".__LINE__.") : remove_user_from_group uid empty ".$uid);
	}

	if ($cid=="")
	{
		die ("(".__LINE__.") : remove_user_from_group cid empty ".$cid);
	}

	$table_names = getTablesOfCourses($cid);

	/*
	 * Prepare the SQL query end acccording to the content of $gid
	 */

	if ($gid == "ALL" || $gid=="")
	{
		$endQuery = "";
	}
	elseif (is_int($gid))
	{
		$endQuery = "AND team = \"$gid\"";
	}
	elseif (is_array($gid))
	{
		foreach($gid as $thisGid )
		{
			if (! is_int($gid[$i])) return error("not a gid");
		}

		$gidList = implode(",", $gid);

		$endQuery = "AND team IN ($gidLIst)";
	}

	$result = sql_query("DELETE FROM `".$table_names["tbl_user_group"]."`
	                     WHERE user =\"$uid\" $endQuery",false,false);
	if ($result)
	{
		return true;
	}
	else
	{
		return false;
	}
}

//////////////////////////////////////////////////////////////////////////////
function remove_user_from_platform($uid)
{
	global $tbl_user;

	if (remove_user_from_course($uid))
	{

		$sqlDeleteUser = "DELETE FROM `$tbl_user` WHERE user_id = \"$uid\"";
		$result = sql_query($sqlDeleteUser);
	}

	if ($result)
	{
		return true;
	}
	else
	{
		return false;
	}

}

////////////////////////////////////////////////////////////////////////////////

/**
 * Wrap the mysql_query allowing to dispay the query when something goes wrong
 *
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>,
 *         Christophe Gesché <gesche@ipm.ucl.ac.be>
 * @param  string $query - SQL statement
 * @param  handler $db - db handler
 * @return handler - return the result handler
 */

function sql_query($query, $db = false, $forceEchoSql=false)
{
	global $debug;

	if ($db)
	{
		$handle = mysql_query($query, $db);
	}
    else
	{
		$handle = mysql_query($query);
	}

	if ($forceEchoSql || (mysql_errno() && $debug))
	{
		echo "<pre style='color:red'>".mysql_errno().": ".mysql_error()."\n".$query."</pre><hr>";
	}
	return $handle;

}

 ////////////////////////////////////////////////////////////////////////////////

 /**
  * handles the error inside Claroline
  * @author Hugues Peeters <peeters@ipm.ucl.ac.be>,
  *         Christophe Gesché <gesche@ipm.ucl.ac.be>
  * @desc It fills a global array called $errorList.
  *       This array collects all the error occuring during the script run
  * @param string $errorMsg - the message sent by the error
  * @param string $file - path of the file where the error occured (optionnal)
  * @param int    $line - line where the error occured (optionnal)
  * @param int    $no - code number of the error if it exists (optionnal)
  *
  * @return bolean false to stay consistent with the main script
  */

function error($errorMsg, $file="", $line="", $no="", $caller="")
{
	global $errorList;

	if ($caller)
	{
		$myError['caller'] = $caller;
	}
	else
	{
		if (function_exists('debug_backtrace'))
		{
			list($functionCall,)  = debug_backtrace(); // works only since PHP 4.3
			$myError['caller' ]   = $functionCall['function'] ;
			$myError['location']  = 'in '.$functionCall['file'].'on line '.$functionCall['line'];
			$myError['args']      = $functionCall['args'];
		}
	}

	if ($file && $line) $myError['location'] = "in $file on line $line";

	$myError['message'] = $errorMsg;
	$myError['no']      = $no;

	$errorList[] = $myError;

	return false;
}

/**
 * Read Info about the course.
 *
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>,
 *         Christophe Gesché <gesche@ipm.ucl.ac.be>
 * @param  string $cid - sysCode
 * @return handler - return an array with all properties in same structure than init.
 */

function getCourseInfo($cid)
{
	GLOBAL $dbGlu, 	$TABLECOURS,$courseTablePrefix;
	$sql =	"SELECT `cours`.`dbName`
			FROM `".$TABLECOURS."` `cours`
			WHERE `cours`.`code` = '".$cid."'";

	$result = mysql_query($sql)  or die ("WARNING !! DB QUERY FAILED ! ".__LINE__);

	if (mysql_num_rows($result)>0)
	{
		$thisCourse = mysql_fetch_array($result,MYSQL_ASSOC);

		$_course['name'        ]         = $thisCourse['intitule'      			];
		$_course['officialCode']         = $thisCourse['fake_code'      		]; // use in echo
		$_course['sysCode'     ]         = $thisCourse['code'   		   		]; // use as key in db
		$_course['path'        ]         = $thisCourse['directory'      		]; // use as key in path
		$_course['dbName'      ]         = $thisCourseData['dbName'         		]; // use as key in db list
		$_course['dbNameGlu'   ]         = $courseTablePrefix . $thisCourse['dbName'] . $dbGlu; // use in all queries
		$_course['titular'     ]         = $thisCourse['titulaires'     		];
		$_course['language'    ]         = $thisCourse['languageCourse' 		];
		$_course['extLink'     ]['url' ] = $thisCourse['departmentUrl' 		];
		$_course['extLink'     ]['name'] = $thisCourse['departmentUrlName'	];
		$_course['categoryCode']         = $thisCourse['faCode'				];
		$_course['categoryName']         = $thisCourse['faName'         		];
		$_course['visibility'  ]         = (bool) ($thisCourse['visible']==2 || $thisCourse['visible']==3);
		$_course['registrationAllowed']  = (bool) ($thisCourse['visible']==1 || $thisCourse['visible']==2);
	}

	return $_course;
}

/**
 * build a tablesname of a course.
 *
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>,
 *         Christophe Gesché <gesche@ipm.ucl.ac.be>
 * @param  string $cid - sysCode
 * @return handler - return an array with all table name for the course.
 */

function getTablesOfCourses($cid)
{
	GLOBAL $dbGlu, $tbl_courses, $courseTablePrefix;

	if ($tbl_courses=="")
	{
		die ("\$tbl_courses must exist".__LINE__);
	}
	$sql =	"SELECT `cours`.`dbName`
			FROM `".$tbl_courses."` `cours`
			WHERE `cours`.`code` = '".$cid."'";

	$result = sql_query($sql)  or die ("WARNING !! DB QUERY FAILED ! ".__LINE__);

	if (mysql_num_rows($result)>0)
	{
		$thisCourse = mysql_fetch_array($result,MYSQL_ASSOC);

		$_course['dbName'      ]   			= $thisCourseData['dbName'         		]; // use as key in db list
		$_course['dbNameGlu'   ]   			= $courseTablePrefix . $thisCourse['dbName'] . $dbGlu; // use in all queries
		$_course['tbl_user_group'] 			= $_course['dbNameGlu']."user_group";
		$_course['tbl_group_properties']	= $_course['dbNameGlu']."group_properties";
		$_course['tbl_groups']				= $_course['dbNameGlu']."student_group";

		$_course['tbl_tools'					]	= $_course['dbNameGlu']."accueil";

		$_course['tbl_forum_access'				]	= $_course['dbNameGlu']."access";
		$_course['tbl_forum_banlist'			]	= $_course['dbNameGlu']."banlist";
		$_course['tbl_forum_config'				]	= $_course['dbNameGlu']."config";
		$_course['tbl_forum_disallow'			]	= $_course['dbNameGlu']."disallow";
		$_course['tbl_forum_forum_access'		]	= $_course['dbNameGlu']."forum_access";
		$_course['tbl_forum_forum_mods'			]	= $_course['dbNameGlu']."forum_mods";
		$_course['tbl_forum_forums'				]	= $_course['dbNameGlu']."forums";
		$_course['tbl_forum_headermetafooter'	]	= $_course['dbNameGlu']."headermetafooter";
		$_course['tbl_forum_pages'				]	= $_course['dbNameGlu']."catagories";
		$_course['tbl_forum_pages'				]	= $_course['dbNameGlu']."pages";
		$_course['tbl_forum_posts'				]	= $_course['dbNameGlu']."posts";
		$_course['tbl_forum_posts_text'			]	= $_course['dbNameGlu']."posts_text";
		$_course['tbl_forum_priv_msgs'			]	= $_course['dbNameGlu']."priv_msgs";
		$_course['tbl_forum_themes'				]	= $_course['dbNameGlu']."themes";
		$_course['tbl_forum_users'				]	= $_course['dbNameGlu']."users";
		$_course['tbl_forum_whosonline'			]	= $_course['dbNameGlu']."whosonline";
		$_course['tbl_forum_words'				]	= $_course['dbNameGlu']."words";
		$_course['tbl_forum_sessions'			]	= $_course['dbNameGlu']."sessions";
		$_course['tbl_forum_topics'				]	= $_course['dbNameGlu']."topics";

		$_course['tbl_calandar']					= $_course['dbNameGlu']."agenda";

		$_course['tbl_announcement']				= $_course['dbNameGlu']."annonces";

		$_course['tbl_course_description']			= $_course['dbNameGlu']."course_description";

		$_course['tbl_document']					= $_course['dbNameGlu']."document";

		$_course['tbl_introduction']				= $_course['dbNameGlu']."introduction";

		$_course['tbl_link']						= $_course['dbNameGlu']."liens";

		$_course['tbl_exercices_exercices']			= $_course['dbNameGlu']."exercices";
		$_course['tbl_exercices_questions']			= $_course['dbNameGlu']."questions";
		$_course['tbl_exercices_answer']			= $_course['dbNameGlu']."reponses";
		$_course['tbl_exercices_exercice_answer']	= $_course['dbNameGlu']."exercice_question";
		$_course['tbl_exercices_ranks']				= $_course['dbNameGlu']."ranks";

		$_course['tbl_userinfo_content']			= $_course['dbNameGlu']."userinfo_content";
		$_course['tbl_userinfo_def']				= $_course['dbNameGlu']."userinfo_def";

		$_course['tbl_work']						= $_course['dbNameGlu']."work";
	}
	return $_course;
}
?>
