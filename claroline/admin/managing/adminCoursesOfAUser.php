<?php # $Id$
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
$langCoursesByUser ="Panel Course-User";


$langFile = "courses";
require '../../inc/claro_init_global.inc.php';

$interbredcrump[]= array ("url"=>$rootAdminWeb, "name"=> $langAdministrationTools);
$interbredcrump[]= array ("url"=>$rootAdminWeb."managing/", "name"=> $langManage);
$nameTools=$lang_course_enrollment;

$noPHP_SELF=true;
$htmlHeadXtra[] =
"<style type=\"text/css\">
		BODY,H1,H2,H3,H4,H5,H6,P,BLOCQUOTE,TD,OL,UL,input  {	font-family: Arial, Helvetica, sans-serif; }
		th, td { font-size: x-small;	font-family: Helvetica, Arial, sans-serif;	}
		.textStyle { border: 0px; background-color: White; color: Blue; text-decoration: underline; }
		TABLE.list TR TD.add { background-color: ".$color2."; }
		TABLE.list TR TD.rem { background-color: ".$color1."; }
		TABLE.list TR TH {	border: 1px solid grey;		}
</STYLE>";

$now = gmdate('D, d M Y H:i:s') . ' GMT';
header('Expires: 0'); // rfc2616 - Section 14.21
header('Last-Modified: ' . $now);
header('Cache-Control: no-store, no-cache, must-revalidate'); // HTTP/1.1
header('Cache-Control: pre-check=0, post-check=0, max-age=0'); // HTTP/1.1
header('Pragma: no-cache'); // HTTP/1.0

$langVisibility[0] = $langHideAndSubscribeClosed;
$langVisibility[1] = $langHideAndSubscribeOpen;
$langVisibility[2] = $langShowAndSubscribeOpen;
$langVisibility[3] = $langShowAndSubscribeClosed;

$is_allowedToEdit = $isCampusAdmin || $PHP_AUTH_USER;

$tbl_cours			= $mainDbName."`.`cours";
$tbl_user 			= $mainDbName."`.`user";
$tbl_cours_users	= $mainDbName."`.`cours_user";
$tbl_users_groups  	= $_course['dbNameGlu']."user_group";
$tbl_groups 		= $_course['dbNameGlu']."student_group";
$tbl_department		= $mainDbName."`.`faculte";

//Default view
$displayGoOut = true;
if (!$is_allowedToEdit)
{
	$uidToEdit = "xxx";
	$displayGoOut = true;
}
else
{
	if (isset($uidToEdit)&&is_numeric($uidToEdit))
	{
		$displayListOfCourse = true;
		if (
				(isset($selectCourse) && is_array($selectCourse))
				||
				(isset($HTTP_POST_VARS["submit"]) && $HTTP_POST_VARS["submit"]!="")
			)
		{
			$uidToEdit = $HTTP_POST_VARS["uidToEdit"];
			$sqlCoursesOfUser = "
			SELECT `courses_user`.code_cours code_cours
				FROM `$tbl_cours_users` `courses_user`
			WHERE  `user_id`= '".$uidToEdit."' ";


			$resCourseOfUserBefore = @mysql_query($sqlCoursesOfUser);
			if (mysql_errno() > 0) echo mysql_errno().": ".mysql_error()." &lt;- $sqlCoursesOfUser Before<br>";
   			while ($courseOfUserBefore =  mysql_fetch_array($resCourseOfUserBefore))
			{
				$listCoursesBefore[] = $courseOfUserBefore["code_cours"];
			};
   			mysql_free_result($resCourseOfUserBefore);

			$sqlWashUser =
			"DELETE
			FROM `$tbl_cours_users`
			WHERE statut != 1
				AND `user_id`= '".$uidToEdit."'";
			@mysql_query($sqlWashUser);
		//    echo "<BR>".$sqlWashUser."<BR>";
			if (mysql_errno() > 0)
				echo mysql_errno().": ".mysql_error()." &lt;- $sqlWashUser<br>";
			if (is_array($selectCourse))
				while (list($key,$contenu)=  each ($selectCourse))
				{
					$sqlInsertCourse =
		"INSERT
			INTO `$tbl_cours_users`
				(`code_cours`, `user_id`, `statut`, `role`)
				VALUES
				('".$contenu."', '".$uidToEdit."', '5', ' ')";
					@mysql_query($sqlInsertCourse) ;
		//			echo "<BR>".$sqlInsertCourse."<BR>";
					if (mysql_errno() > 0) echo mysql_errno().": ".mysql_error()." &lt;- $sqlInsertCourse<br>";
				}
		/*	$sqlAfter = "
			SELECT `cours_user`.code_cours code_cours
				From `$mainDbName`.`cours_user`
			WHERE `user_id`= '".$uidToEdit."' ";
			*/
			$resCourseOfUserAfter = mysql_query($sqlCoursesOfUser);
			if (mysql_errno() > 0) echo mysql_errno().": ".mysql_error()." &lt;- $sqlCoursesOfUser After <br>";
			while ($courseOfUserAfter =  mysql_fetch_array($resCourseOfUserAfter))
			{
				$listCoursesAfter[] = $courseOfUserAfter["code_cours"];
			};
			if (is_array($listCoursesBefore)&&is_array($listCoursesAfter))
			{
				$coursesUnsubscribed 	= array_diff($listCoursesBefore,$listCoursesAfter);
				$coursesSubscribed 		= array_diff($listCoursesAfter,$listCoursesBefore );
				$coursesNoChange 		= array_intersect($listCoursesAfter,$listCoursesBefore );
			}


			//SELECT DISPLAY
			$displayListOfCourse 	= false;
			$displayResultOfChange  = true;
		}
		else
		{
			$uidToEdit = $HTTP_GET_VARS["uidToEdit"];
		}


		if ( $displayListOfCourse || $displayResultOfChange  )

		{
			$sqlGetInfoUser ="
			SELECT nom, prenom, username, email
				FROM  `".$tbl_user."`
				WHERE user_id='$uidToEdit'";
			$result=mysql_query($sqlGetInfoUser) or die("Erreur SELECT FROM user");
			//echo $sqlGetInfoUser;
			$myrow = mysql_fetch_array($result);

			$userInfoHtml =
			"<UL>
				<LI>nom : "			.$myrow["nom"]		."</LI>
				<LI>prenom : "		.$myrow["prenom"]	."</LI>
				<LI>username : "	.$myrow["username"]	."</LI>
				<LI>email : "		.$myrow["email"]	."</LI>
			</UL>";

		}
	}
	else
	{
		$displayListOfUsers = true;
		$sqlGetListUser = "SELECT user_id, nom, prenom, username, email FROM  `".$tbl_user."`";
		$resListOfUsers=mysql_query($sqlGetListUser) or die("Erreur SELECT FROM user");
		//echo $sqlGetInfoUser;

	}

}









//////////////OUTPUT//////////////////////



include($includePath."/claro_init_header.inc.php");
claro_disp_tool_title(
	array(
	'mainTitle'=>$nameTools,
	'subTitle'=> $siteName." - ".$clarolineVersion." - ".$dateNow
	)
	);
////////////////////////////
// $displayResultOfChange //
////////////////////////////
if ($displayResultOfChange)
{
	echo "
	<H3>
		",$langIsReg,"
	</H3>
	",$userInfoHtml,"
	<!-- Look of  this table  is  defined  in  style -->
	<table class=\"list\" width=\"100%\" cellpadding=\"3\" cellspacing=\"8\"  >
<tr>
	<th>",$langAdded,"</th>
	<th>",$langDeleted,"</th>
	<th>",$langKeeped,"</th>
</tr>
<tr>
	<td class=\"add\" valign=\"top\" width=\"35%\">";
	if (is_array($coursesSubscribed))
	foreach($coursesSubscribed as $courseSubscribed)
	{
		echo "$courseSubscribed<br>";
	};

	echo "
	</td>
	<td class=\"rem\" valign=\"top\" width=\"35%\" >";
	if (is_array($coursesUnsubscribed ))
	foreach($coursesUnsubscribed as $courseUnsubscribed)
	{
		echo "$courseUnsubscribed ";
		deleteUserFromGroupIfNotInCourse($courseUnsubscribed);
	};
	echo "
	</td>
	<td  valign=\"top\" width=\"30%\" >";
	if (is_array($coursesNoChange ))
	foreach($coursesNoChange as $courseNoChange)
	{
		echo "$courseNoChange<br>";
	};
	echo "
	</td>
</tr>
</table>


	<br>
	<hr>
	<a href=\"adminprofile.php?uidToEdit=",$uidToEdit,"\">",$langAdminThisUser,"</a>
	<a href=\"",$SELF,"?uidToEdit=",$uidToEdit,"\">",$langBackToListOfThisUser,"</a>
	<a href=\"userManagement.php\">Liste des utilisateurs</a>

	";
}

//////////////////////////
// $displayListOfCourse //
//////////////////////////
elseif ($displayListOfCourse)
{
	echo $langAddHereSomeCourses,"<br>",$userInfoHtml,"
<form action=\"",$PHP_SELF,"?statut=",$statut,"\" method=\"post\">";

	// build a list  of  courses include  fac info.
	$sqlListOfCourses =
"SELECT
		`courses`.`faculte` 		`f`,
		`dept`.`name` 				`n`,
		`courses`.`code` 			`k`,
		`courses`.`fake_code` 		`c`,
		`courses`.`intitule` 		`i`,
		`courses`.`titulaires` 		`t`,
		`courses`.`languageCourse` 	`l`,
		`courses`.`visible` 		`v`
	FROM `".$tbl_cours."` `courses`, `".$tbl_department."` `dept`
	WHERE `courses`.`faculte` = `dept`.`code`
	ORDER BY `dept`.`treePos`, `courses`.`code`";
	// build a list of  course follow  by  user.
	$sqlListOfCoursesOfUser =
"SELECT
		code_cours cc,
		statut ss
	FROM `".$tbl_cours_users."`
	WHERE `user_id` = '".$uidToEdit."'";

	$listOfCourses = 	mysql_query($sqlListOfCourses);
	$listOfCoursesOfUser = 	mysql_query($sqlListOfCoursesOfUser);
	$facOnce  ="";
	$codeOnce ="";
	// build array of user's courses
	while ($rowMyCourses = mysql_fetch_array($listOfCoursesOfUser))
	{
		$myCourses[$rowMyCourses["cc"]]["subscribed"]= TRUE;
		$myCourses[$rowMyCourses["cc"]]["statut"]= $rowMyCourses["ss"];
	}
?>
			<table border="0" width="100%" cellpadding="1" cellspacing="2" >
	<?
	// output list of  courses
	while ($courses = mysql_fetch_array($listOfCourses))
	{
		//if ( $courses["v"] =="1" || $courses["v"] =="2" || $myCourses[$courses["c"]]["subscribed"])

		// check  if  it's another  fac,   if yes add header
		if($courses["f"] != $facOnce)
		{
			echo "
			<TR>
				<TD colspan=\"6\">
					<input type=\"submit\" name=\"submit\" value=\"".$langOk."\" class=\"textStyle\">
					<hr noshade size=\"1\">
					<font color=\"navy\">
						".$courses["f"]."
						".$courses["n"]."
					</font >
				</TD>
			</TR>
			<TR>
				<th >
					".$langSubscribe."
				</th>
				<th >
					".$langCodeCourse."
				</th>
				<th>
					".$langCourseName."
				</th>
				<th>
					".$langTitular."
				</th>
				<th>
					".$langLanguage."
				</th>
				<td>
				</td>
			</tr>
				";
		}
		$facOnce = $courses["f"];

		if($courses["k"] != $codeOnce)
		{
			if (isset ($myCourses[$courses["k"]]["subscribed"]) )
			{
				echo "
				<TR>
					<TD>";
				if ($myCourses[$courses["k"]]["statut"]!=1)
				{
					echo "
				<input type='checkbox' name='selectCourse[]' value='".$courses["k"]."'  checked >";
					}
				else
				{
					echo "
				[$langTitular] ";
/*					if ($is_admin)
						{
							echo "<br>
							!!! <input type='checkbox' name='selectCourse[]' value='".$courses["k"]."'  checked >";
						}
		*/
				}
				echo  "
					</td>";
				}
				else
				{
					echo "
				<TR>
					<TD>
						<input type='checkbox' name='selectCourse[]' value='".$courses["k"]."'>
					</td>";
				}
				echo  "
					<td>
						<strong>
							".$courses["c"],"
						</strong>
						<br>
						",$langVisibility[$courses["v"]],"
					</td>
					<td>
						",$courses["i"],"
					</td>
					<td>
						",$courses["t"],"
					</TD>
					<td>
						",$langNameOfLang[$courses["l"]],"
					</TD>
					<td>";
						echo "
						<a Href=\"",$urlServer,$courses["k"],"\" target=\"_see\">",$langSee,"</a>
					</TD>
				</TR>";
			}
		$codeOnce = $courses["k"];

	}
	echo "
		<Tr>
			<TD colspan=\"6\">
				<input type=\"hidden\" name=\"uidToEdit\" value=\"",$uidToEdit,"\">
				<input type=\"submit\" name=\"submit\" value=\"",$langOk,"\" >
			</TD>
		</TR>
	</table>
</form>
<hr>
<a href=\"adminprofile.php?uidToEdit=",$uidToEdit,"\">",$langAdminThisUser,"</a>
<a href=\"userManagement.php\">Liste des utilisateurs</a>";
}

/////////////////////////
// $displayListOfUsers //
/////////////////////////

elseif ($displayListOfUsers)
{
?></form>
<form action="<?php echo $PHP_SELF ?>" method="GET">
<select name="uidToEdit" tabindex=2>
<?php
	while ($user = mysql_fetch_array($resListOfUsers))
	{
		echo "
	<OPTION  value=\"",$user["user_id"],"\" >
		",$user["nom"]," ",$user["prenom"],"
		(",$user["username"],")
		",$user["email"],"
	</OPTION>";
	}
?>
</select>
<input type="submit" value="edit this user">
</form>
<?php
echo "
<a href=\"userManagement.php\">",$langCoursesByUser,"</a>";
}

///////////////////
// $displayGoOut //
///////////////////
else
{
	echo "Vous n'avez pas accès à ceci";
}

include($includePath."/claro_init_footer.inc.php");



function deleteUserFromGroupIfNotInCourse($codeCours='ALL COURSES' )
{
	GLOBAL $tbl_cours_users, $tbl_cours, $tbl_user;

	if ($codeCours=='ALL COURSES')
	{
		return ("not aivailable");
	}
	if ($codeCours=='')
	{
		return ("error");
	}

	$currentCourseID = $codeCours;
	$sqlCourseDatas = "SELECT *
					FROM `$tbl_cours`
					WHERE code='$currentCourseID'";
	// echo $sqlCourseDatas;
	$resCoursesData = mysql_query($sqlCourseDatas);
	$coursesData = mysql_fetch_array($resCoursesData);

	$tbl_users_groups  	= $coursesData['dbNameGlu']."user_group";


	$sqlUserToKeep = "SELECT DISTINCT u.user_id
					FROM `$tbl_cours_users` `cu`, `$tbl_user` `u`
						WHERE cu.code_cours='".$currentCourseID."'
							AND cu.user_id = u.user_id";

	//echo $sqlUserToKeep;

	$resUserToKeep = mysql_query($sqlUserToKeep);
	while ($id = mysql_fetch_array($resUserToKeep))
	{
		$listOfId[]= $id["user_id"];
	};
	$listOfId = implode($listOfId,",");
	if ($listOfId!="")
	{
		$sqlUserToRemoveFromGroup = "
		DELETE FROM `".$tbl_users_groups."`.`user_group`
		WHERE user NOT IN (".$listOfId.")";
		$resCourseOfUserBefore = @mysql_query($sqlUserToRemoveFromGroup );
		$sqlUserToRemoveFromGroup ;
    	if (mysql_errno() > 0)

		mail($emailAdministrator,  "[".$siteName."] error ".mysql_errno(),
		"
		error on ".$siteName."\n
		".mysql_errno().": ".mysql_error()." \n
		 	sqlUserToRemoveFromGroup : $sqlUserToRemoveFromGroup\n
			sqlUserToKeep :$sqlUserToKeep\n
			listOfId : $listOfId\n");

	}
}
?>
