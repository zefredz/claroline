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

$langFile = "searchUser";
$cidReset=TRUE;
include("../../inc/claro_init_global.inc.php");
include("../../inc/lib/user.lib.inc.php");
include($includePath."/lib/text.lib.php");
include($includePath."/lib/debug.lib.inc.php");

$dateNow 			= claro_format_locale_date($dateTimeFormatLong);
$is_allowedToAdmin 	= $is_platformAdmin;


//TABLES

$tbl_user 			= $mainDbName."`.`user";
$tbl_courses		= $mainDbName."`.`cours";
$tbl_course_user	= $mainDbName."`.`cours_user";
$tbl_admin			= $mainDbName."`.`admin";
$tbl_todo			= $mainDbName."`.`todo";
$tbl_track_default	= $statsDbName."`.`track_e_default";// default_user_id
$tbl_track_login	= $statsDbName."`.`track_e_login";	// login_user_id

// ENTRY DATA

$su_user_id  = $_REQUEST["user_id"];
$cours_id 	 = $_REQUEST["cours_id"];
$search_user = $_REQUEST["search_user"];


// WORKS

// fix the display
$display_choice		=TRUE;
$display_form		=FALSE;
$display_listUser	=FALSE;
$display_user		=FALSE;
$display_userCourse	=FALSE;
$display_course		=FALSE;
$display_infoUserFus=FALSE;

if(!$is_allowedToAdmin)
{
	$display_choice	= FALSE;
	$controlMsg["error"][]=$lang_SearchUser_NoAdmin;
}
else
{

	if(isset($_REQUEST["searchForm"]))
	{
		$display_choice=FALSE;
		$display_form=TRUE;
	}

	if(isset($_REQUEST["fusion"]))
	{
		$display_choice=FALSE;
		$display_choiseShowFus=TRUE;
	}


	/*-------------------------------------------------------------------
	Delete a user
	--------------------------------------------------------------------*/
	if(isset($_REQUEST["delete"]))
	{
		$display_choice	=FALSE;
		$display_form	=TRUE;

		$sql_searchCourseData =
			"select
				`c`.`dbName`
			FROM `".$tbl_course_user."` cu,`".$tbl_courses."` c
			WHERE `cu`.`code_cours`=`c`.`code` AND `cu`.`user_id`='".$su_user_id."'";

			$res_searchCourseData = claro_sql_query_fetch_all($sql_searchCourseData) ;

			//For each course of the user
			if($res_searchCourseData)
			{
				foreach($res_searchCourseData as $one_course)
				{
					$_course["dbNameGlu"]	= $courseTablePrefix . $one_course["dbName"] . $dbGlu; // use in all queries
					$tbl_rel_usergroup 		= $_course["dbNameGlu"]."group_rel_team_user";
					$tbl_group 		   		= $_course["dbNameGlu"]."group_team";
					$tbl_userInfo			= $_course["dbNameGlu"]."userinfo_content";

					$tbl_track_access    = $_course["dbNameGlu"]."track_e_access";    // access_user_id
					$tbl_track_downloads = $_course["dbNameGlu"]."track_e_downloads";
					$tbl_track_exercices = $_course["dbNameGlu"]."track_e_exercices";
	//                $tbl_track_link      = $_course["dbNameGlu"]."track_e_links";    //links_user_id
					$tbl_track_upload    = $_course["dbNameGlu"]."track_e_uploads";// upload_user_id

					//delete user information in the table group_rel_team_user
					$sql_deleteUserFromGroup = "delete from `$tbl_rel_usergroup` where user='".$su_user_id."'";
					claro_sql_query($sql_deleteUserFromGroup) ;

					//delete user information in the table userinfo_content
					$sql_deleteUserFromGroup = "delete from `$tbl_userInfo` where user_id='".$su_user_id."'";
					claro_sql_query($sql_deleteUserFromGroup) ;

					//change tutor -> NULL for the course where the the tutor is the user deleting
					$sql_update="update `$tbl_group` set tutor=NULL where tutor='".$su_user_id."'";
					claro_sql_query($sql_update) ;

					$sql_DeleteUser="delete from `$tbl_track_access` where access_user_id='".$su_user_id."'";
					claro_sql_query($sql_DeleteUser);

					$sql_DeleteUser="delete from `$tbl_track_downloads` where down_user_id='".$su_user_id."'";
					claro_sql_query($sql_DeleteUser);

					$sql_DeleteUser="delete from `$tbl_track_exercices` where exe_user_id='".$su_user_id."'";
					claro_sql_query($sql_DeleteUser);

					$sql_DeleteUser="delete from `$tbl_track_upload` where upload_user_id='".$su_user_id."'";
					claro_sql_query($sql_DeleteUser);
				}
			}

		//delete the user in the table user
		$sql_DeleteUser="delete from `$tbl_user` where user_id='".$su_user_id."'";
		claro_sql_query($sql_DeleteUser);

		//delete user information in the table course_user
		$sql_DeleteUser="delete from `$tbl_course_user` where user_id='".$su_user_id."'";
		claro_sql_query($sql_DeleteUser);

		//delete user information in the table admin
		$sql_DeleteUser="delete from `$tbl_admin` where idUser='".$su_user_id."'";
		claro_sql_query($sql_DeleteUser);

		//change assignTo -> 0 from the table todo where assignTo is the user
		$sql_update="update `$tbl_todo` set assignTo=0 where assignTo='".$su_user_id."'";
		claro_sql_query($sql_update);

		//Change creatorId -> NULL
		$sql_update="update `$tbl_user` set creatorId=NULL where creatorId='".$su_user_id."'";
		claro_sql_query($sql_update);

		//delete user information in the tables clarolineStat
		$sql_DeleteUser="delete from `$tbl_track_default` where default_user_id='".$su_user_id."'";
		claro_sql_query($sql_DeleteUser);

		$sql_DeleteUser="delete from `$tbl_track_login` where login_user_id='".$su_user_id."'";
		claro_sql_query($sql_DeleteUser);

		unset($su_user_id);
	}




	/*-------------------------------------------------------------------
	Get informations about the user
	--------------------------------------------------------------------*/
	if(isset($search_user))
	{
		$display_choice=FALSE;
		$display_form=TRUE;

		$su_lastname 	= $_REQUEST["lastname"];
		$su_firstname 	= $_REQUEST["firstname"];
		$su_username 	= $_REQUEST["username"];
		$su_authSource	= $_REQUEST["authSource"];
		$su_status 		= $_REQUEST["statut"];
		$su_password 	= $_REQUEST["password"];
		$su_email 		= $_REQUEST["email"];
		$su_code 		= $_REQUEST["code"];
		$su_phone 		= $_REQUEST["phone"];
		$su_picture 	= $_REQUEST["picture"];
		$su_creatorId 	= $_REQUEST["creatorId"];
		$su_order		=($_REQUEST["order"]?$_REQUEST["order"]:"nom,prenom");
		$su_ascDesc		=($_REQUEST["ascDesc"]?$_REQUEST["ascDesc"]:"ASC");


		//search informations from the table
		$sql_searchuser = "
		select `user_id`,`nom` lastname,`prenom` firstname,`username`,`password`,`authSource`,`email`,`statut`
				,`officialCode` code,`phoneNumber` phone,`pictureUri` picture,`creatorId`
				FROM `".$tbl_user."` WHERE ".

		(!empty($su_user_id)	?"UPPER(`user_id`)			LIKE '".trim(strtoupper($su_user_id))."'		AND ":"").
		(!empty($su_lastname)	?"UPPER(`nom`)				LIKE '".trim(strtoupper($su_lastname))."'		AND ":"").
		(!empty($su_firstname)	?"UPPER(`prenom`)			LIKE '".trim(strtoupper($su_firstname))."'	AND ":"").
		(!empty($su_username)	?"UPPER(`username`)			LIKE '".trim(strtoupper($su_username))."'		AND ":"").
		(!empty($su_password)	?"UPPER(`password`)			LIKE '".trim(strtoupper($su_password))."'		AND ":"").
		(!empty($su_authSource)	?"UPPER(`authSource`)		LIKE '".trim(strtoupper($su_authSource))."'	AND ":"").
		(!empty($su_email)		?"UPPER(`email`)			LIKE '".trim(strtoupper($su_email))."'		AND ":"").
		(!empty($su_status)		?"UPPER(`statut`)			LIKE '".trim(strtoupper($su_status))."'		AND ":"").
		(!empty($su_code)		?"UPPER(`officialCode`)		LIKE '".trim(strtoupper($su_code))."'			AND ":"").
		(!empty($su_creatorId)	?"UPPER(`creatorId`)		LIKE '".trim(strtoupper($su_creatorId))."'	AND ":"").
		"1
		ORDER by ".$su_order." ".$su_ascDesc;

		$res_searchuser = claro_sql_query_fetch_all($sql_searchuser);

		// Error if anyone input parameters
		if(empty($su_user_id) && empty($su_lastname) && empty($su_firstname) && empty($su_username)
				&& empty($su_password) && empty($su_authSource) && empty($su_email) && empty($su_status)
				&& empty($su_code) && empty($su_phone) && empty($su_creatorId))
		{
			$controlMsg["error"][]= $lang_SearchUser_NoParameter;
		}
		else
		{
			if(count($res_searchuser)>1)
			{
				$user=$res_searchuser;
				// display all user's informations
				$display_listUser=TRUE;
				$display_form=FALSE;
			}
			//if the are one result, display  user informations and  user courses
			elseif($res_searchuser && count($res_searchuser)== 1)
			{
				$user = $res_searchuser[0];
				$one_result = $user["user_id"];
				$display_user=TRUE;
				$display_userCourse=TRUE;
				$display_form=FALSE;
			}
			else
			{
				$controlMsg["warning"][]= $lang_SearchUser_NoUser;
			}
		}

	}




	/*-------------------------------------------------------------------
	For the display of user informations and user courses
	--------------------------------------------------------------------*/
	if($_REQUEST["display"] || isset($one_result))
	{
		$noQUERY_STRING 	= FALSE;

		$display_choice		= FALSE;
		$display_user		= TRUE;
		$display_userCourse	= TRUE;

		$id = (isset($one_result)?$one_result:$su_user_id);

		if($_REQUEST["deleteOfCourse"])
		{
			$sql_searchCourse="select dbName from `$tbl_courses` where code='".$_REQUEST["deleteOfGroup"]."'";
			$dbName=claro_sql_query_fetch_all($sql_searchCourse);

			$_course["dbNameGlu"]	= $courseTablePrefix . $dbName[0]["dbName"] . $dbGlu;
			$tbl_rel_usergroup 		= $_course["dbNameGlu"]."group_rel_team_user";

			//delete de student in the table group_rel_team_user and cours_user
			$sql_deleteInscribe="delete from `$tbl_rel_usergroup` where user='".$id."'";
			claro_sql_query($sql_deleteInscribe);

			$sql_deleteInscribe="delete from `$tbl_course_user` where user_id='".$id."' and
									code_cours='".$_REQUEST["deleteOfGroupe"]."'";
			claro_sql_query($sql_deleteInscribe);
		}

		if($_REQUEST["deleteOfGroup"])
		{
			$sql_searchCourse="select dbName from `$tbl_courses` where code='".$_REQUEST["sysCode"]."'";
			$dbName=claro_sql_query_fetch_all($sql_searchCourse);

			$_course["dbNameGlu"]	= $courseTablePrefix . $dbName[0]["dbName"] . $dbGlu;
			$tbl_rel_usergroup 		= $_course["dbNameGlu"]."group_rel_team_user";

			//delete de student in the table group_rel_team_user and cours_user
			$sql_deleteInscribe="delete from `$tbl_rel_usergroup` where user='".$id."' and team='".$_REQUEST["deleteOfGroup"]."'";
			claro_sql_query($sql_deleteInscribe);
		}

		//search informations's users from the table
		$sql_searchuser ="select `nom` lastname,`prenom` firstname,`username`,`password`,`authSource`,`email`,`statut`,
			`officialCode` code,`phoneNumber` phone,`pictureUri` picture,`creatorId` FROM `".$tbl_user."` WHERE `user_id`='".$id."'";

		$res_searchuser = claro_sql_query_fetch_all($sql_searchuser);
		$user=$res_searchuser[0];


		//Search the course that the user have subscribe
		if(isset($cours_id))
		{
			$display_course=TRUE;

			$sql_searchCourseData =
			"select `cu`.`statut`,`cu`.`role`,`cu`.`tutor` titular,`c`.`cours_id`,`c`.`code` sysCode,`c`.`languageCourse`
						,`c`.`intitule`,`c`.`faculte`,`c`.`titulaires`,`c`.`fake_code`,`c`.`directory`,`c`.`dbName`
					FROM `".$tbl_course_user."` cu,`".$tbl_courses."` c
					WHERE `cu`.`code_cours`=`c`.`code` AND `cu`.`user_id`='".$id."' AND `c`.`cours_id`='".$cours_id."'";

			$res_searchCourseData = claro_sql_query_fetch_all($sql_searchCourseData) ;

			//this is the choose course
			if($res_searchCourseData && count($res_searchCourseData)==1)
			{
				$cours_user=$res_searchCourseData;

				$_course["dbNameGlu"]	= $courseTablePrefix . $cours_user[0]["dbName"] . $dbGlu; // use in all queries
				$tbl_rel_usergroup 		= $_course["dbNameGlu"]."group_rel_team_user";
				$tbl_group 		   		= $_course["dbNameGlu"]."group_team";

				//search the user groups in this course
				$sql_searchCourseUserGroup =
					"select
							`g`.`name`, `g`.`description`, `g`.`tutor`,  `g`.`secretDirectory`,
							`g`.`id` id_group,
							`ug`.`role`,
							`tutor`.`nom` lastname,
							`tutor`.`prenom` firstname,
							`tutor`.`email`
						FROM `$tbl_rel_usergroup` ug, `".$tbl_group."` g
						LEFT JOIN `".$tbl_user."` tutor
							ON `g`.`tutor` = `tutor`.`user_id`
						WHERE `ug`.`team`   = `g`.`id`
						AND ug.user='".$id."' order by name";

				$courseUserGroup = claro_sql_query_fetch_all($sql_searchCourseUserGroup) ;

				//The sudent don't part of a group in this course
				if ( count($courseUserGroup)<1 || !$courseUserGroup)
				{
					$controlMsg["info"][]= $lang_SearchUser_NoTeam;
				}

			}
		}
		else  //Get  all courses of user
		{
			$su_orderC=(isset($_REQUEST["orderC"])?$_REQUEST["orderC"]:"code");
			$su_ascDescC=(isset($_REQUEST["ascDescC"])?$_REQUEST["ascDescC"]:"ASC");

			$sql_searchUserData =
			"select `cu`.`code_cours`,`cu`.`statut`,`cu`.`role`,`cu`.`tutor` titular,`c`.`cours_id`,`c`.`code` sysCode
						,`c`.`languageCourse`,`c`.`intitule`,`c`.`faculte`,`c`.`titulaires`,`c`.`fake_code`,`c`.`directory`
						,`c`.`dbName`
				FROM `".$tbl_course_user."` cu,`".$tbl_courses."` c
				WHERE `cu`.`user_id`='".$id."' AND `c`.`code`=`cu`.`code_cours` order by ".$su_orderC." ".$su_ascDescC;

			$cours_user=claro_sql_query_fetch_all($sql_searchUserData) ;

			//the student don't have course subscribe
			if (count($cours_user)<1 || !$cours_user)
			{
				$controlMsg["info"][]=$lang_SearchUser_NoUserCourses;
			}
		}
	}



	/*-------------------------------------------------------------------
	Look if they are conflit int the group of courses when they fusion two user
	--------------------------------------------------------------------*/
	if(isset($_REQUEST["lookConflit"]))
	{
		$display_choice			= FALSE;
		$display_choiseShowFus	= TRUE;
		$display_infoUserFus	= TRUE;

		$fusOk=TRUE;

		if(!is_numeric($_REQUEST["user1"]))
		{
			$controlMsg["error"][]=$lang_SearchUser_NotIntUser1;
			$fusOk=FALSE;
		}
		else
		{
			//search informations of the user 1
			$sql_searchuser ="select `user_id`,`nom` lastname,`prenom` firstname,`username`,`password`,`authSource`,`email`,`statut`
								,`officialCode` code,`phoneNumber` phone,`pictureUri` picture,`creatorId`
								FROM `".$tbl_user."` WHERE `user_id`='".$_REQUEST["user1"]."'";

			$res_searchuser = claro_sql_query_fetch_all($sql_searchuser);
			$user1=$res_searchuser[0];

			//If the user don't exist -> warning
			if(empty($user1))
			{
				$controlMsg["warning"][]=$_REQUEST["user1"].$lang_SearchUser_UserDontExist;
				$fusOk=FALSE;
			}
		}

		if(!is_numeric($_REQUEST["user2"]))
		{
			$controlMsg["error"][]=$lang_SearchUser_NotIntUser2;
			$user2["user_id"]=NULL;
			$fusOk=FALSE;
		}
		else
		{
			//search informations of the user 2
			$sql_searchuser ="select `user_id`,`nom` lastname,`prenom` firstname,`username`,`password`,`authSource`,`email`,`statut`
								,`officialCode` code,`phoneNumber` phone,`pictureUri` picture,`creatorId`
								FROM `".$tbl_user."` WHERE `user_id`='".$_REQUEST["user2"]."'";

			$res_searchuser = claro_sql_query_fetch_all($sql_searchuser);
			$user2=$res_searchuser[0];

			if(empty($user2))
			{
				$controlMsg["warning"][]=$_REQUEST["user2"].$lang_SearchUser_UserDontExist;
				$fusOk=FALSE;
			}
		}

		//If they are a problem, don't display information of the two users
		if(!is_numeric($_REQUEST["user1"]) && !is_numeric($_REQUEST["user2"]) || (empty($user1) || empty($user2)) )
			$display_infoUserFus=FALSE;

		//If the user choose fusion
		if(!strcmp($_REQUEST["showORfus"],"fusion") && $fusOk)
		{
			$display_choiseShowFus=FALSE;
			$display_confirmFus=TRUE;

			//search informations of course and group about the two users
			$array=searchCoursesGroup($user1,$user2);
			$res_searchCourseData=$array[0];
			$courseUserGroup=$array[1];

			//Look if they are conflit (if the user is subscribe in a group in a same course in her two inscription)
			$i=0;
			while($i<count($res_searchCourseData))
			{
				$courses=$courseUserGroup[$i];

				$haveGroup=false;
				if($courses)
					$haveGroup=true;

				if(!strcmp($savCourse["sysCode"],$res_searchCourseData[$i]["sysCode"]) && $savCourse["haveGroup"] && $haveGroup)
					$CourseConflit.=$res_searchCourseData[$i]["intitule"].", ";

				$savCourse["sysCode"]=$res_searchCourseData[$i]["sysCode"];
				$savCourse["haveGroup"]=$haveGroup;
				$i++;
			}
		}

	}



	/*-------------------------------------------------------------------
	Make the fusion
	--------------------------------------------------------------------*/
	if(isset($_REQUEST["makeFusion"]))
	{
		$display_choice=FALSE;
		$display_choiseShowFus=TRUE;

		//search informations of course and group about the two users
		$array=searchCoursesGroup($user1,$user2);
		$res_searchCourseData=$array[0];
		$courseUserGroup=$array[1];

		$i=0;
		while($i<count($res_searchCourseData))
		{
			$courses=$courseUserGroup[$i];

			$haveGroup=FALSE;
			if($courses)
				$haveGroup=TRUE;

			if($res_searchCourseData[$i]["user_id"]==$_REQUEST["user2"])
			{
				$_course["dbNameGlu"]	= $courseTablePrefix . $res_searchCourseData[$i]["dbName"] . $dbGlu;
				$tbl_rel_usergroup 		= $_course["dbNameGlu"]."group_rel_team_user";
				$tbl_group 		   		= $_course["dbNameGlu"]."group_team";
				$tbl_userInfo			= $_course["dbNameGlu"]."userinfo_content";
				$tbl_track_access       = $_course["dbNameGlu"]."track_e_access";    // access_user_id
				$tbl_track_downloads    = $_course["dbNameGlu"]."track_e_downloads";
				$tbl_track_exercices    = $_course["dbNameGlu"]."track_e_exercices";
				//$tbl_track_link         = $_course["dbNameGlu"]."track_e_links";    //links_user_id
				$tbl_track_upload       = $_course["dbNameGlu"]."track_e_uploads";// upload_user_id

				//if group in the two same course, Conflit -> delete group and course from the user minor
				if(!strcmp($savCourse["sysCode"],$res_searchCourseData[$i]["sysCode"]) && $savCourse["haveGroup"])
				{
					$sql_delete="delete from `$tbl_course_user` where user_id='".$_REQUEST["user2"]."' AND
								code_cours='".$res_searchCourseData[$i]["sysCode"]."'";
					claro_sql_query($sql_delete);

					$sql_delete="delete from `$tbl_rel_usergroup` where user='".$_REQUEST["user2"]."'";
					claro_sql_query($sql_delete);
				}//If group in the same course from the minor user -> delete course of the major user and change id of the course and group
					// from the minor user
				elseif(!strcmp($savCourse["sysCode"],$res_searchCourseData[$i]["sysCode"]) && !$savCourse["haveGroup"])
				{
					$sql_delete="delete from `$tbl_course_user` where user_id='".$_REQUEST["user1"]."' AND
								code_cours='".$res_searchCourseData[$i]["sysCode"]."'";
					claro_sql_query($sql_delete);

					$sql_update="update `$tbl_course_user` set user_id='".$_REQUEST["user1"]."' where user_id='".$_REQUEST["user2"].
								"' AND code_cours='".$res_searchCourseData[$i]["sysCode"]."'";
					claro_sql_query($sql_update);

					$sql_update="update `$tbl_rel_usergroup` set user='".$_REQUEST["user1"]."' where user='".$_REQUEST["user2"]."'";
					claro_sql_query($sql_update);
				}
				else	//The course don't exist in the major user, change id
				{
					$sql_update="update `$tbl_course_user` set user_id='".$_REQUEST["user1"]."' where user_id='".$_REQUEST["user2"].
								"' AND code_cours='".$res_searchCourseData[$i]["sysCode"]."'";
					claro_sql_query($sql_update);

					$sql_update="update `$tbl_rel_usergroup` set user='".$_REQUEST["user1"]."' where user='".$_REQUEST["user2"]."'";
					claro_sql_query($sql_update);
				}

				//Change id of tutors
				$sql_update="update `$tbl_group` set tutor='".$_REQUEST["user1"]."' where tutor='".$_REQUEST["user2"]."'";
				claro_sql_query($sql_update);

				//delete info user of the minor user
				$sql_delete="delete from `$tbl_userInfo` where user_id='".$_REQUEST["user2"]."'";
				claro_sql_query($sql_delete);

				//Change id for table stat
				$sql_update="update `$tbl_track_access` set access_user_id='".$_REQUEST["user1"]."' where access_user_id='".$_REQUEST["user2"]."'";
				claro_sql_query($sql_update);

				$sql_update="update `$tbl_track_downloads` set down_user_id='".$_REQUEST["user1"]."' where down_user_id='".$_REQUEST["user2"]."'";
				claro_sql_query($sql_update);

				$sql_update="update `$tbl_track_exercices` set exe_user_id='".$_REQUEST["user1"]."' where exe_user_id='".$_REQUEST["user2"]."'";
				claro_sql_query($sql_update);

			//    $sql_update="update `$tbl_track_link` set links_user_id='".$_REQUEST["user1"]."' where links_user_id='".$_REQUEST["user2"]."'";
			//    claro_sql_query($sql_update);

				$sql_update="update `$tbl_track_upload` set upload_user_id='".$_REQUEST["user1"]."' where upload_user_id='".$_REQUEST["user2"]."'";
				claro_sql_query($sql_update);


			}

			//Save sysCode and haveGroup to compare with the next course
			$savCourse["sysCode"]=$res_searchCourseData[$i]["sysCode"];
			$savCourse["haveGroup"]=$haveGroup;
			$i++;
		}

		//delete minor user
		$sql_delete="delete from `$tbl_user` where user_id='".$_REQUEST["user2"]."'";
		claro_sql_query($sql_delete);

		//Change id of the creator id
		$sql_update="update `$tbl_user` set creatorId='".$_REQUEST["user1"]."' where creatorId='".$_REQUEST["user2"]."'";
		claro_sql_query($sql_update);

		//Change assignTo
		$sql_update="update `$tbl_todo` set assignTo='".$_REQUEST["user1"]."' where assignTo='".$_REQUEST["user2"]."'";
		claro_sql_query($sql_update);

		//Change admin, if major user is admin, delete minor user, else if minor user is admin, change the id
		$sql_searchAdmin="select * from `$tbl_admin` where idUser='".$_REQUEST["user1"]."'";
		$array=claro_sql_query_fetch_all($sql_searchAdmin);

		if($array)
		{
			$sql_delete="delete from `$tbl_admin` where idUser='".$_REQUEST["user2"]."'";
			claro_sql_query($sql_delete);
		}
		else
		{
			$sql_update="update `$tbl_admin` set idUser='".$_REQUEST["user1"]."' where idUser='".$_REQUEST["user2"]."'";
		}

		//Change id for table stat
		$sql_update="update `$tbl_track_default` set default_user_id='".$_REQUEST["user1"].
					"' where default_user_id='".$_REQUEST["user2"]."'";
		claro_sql_query($sql_update);

		$sql_update="update `$tbl_track_login` set login_user_id='".$_REQUEST["user1"]."' where login_user_id='".$_REQUEST["user2"]."'";
		claro_sql_query($sql_update);

		//delete number of user in the input text
		$user1["user_id"]="";
		$user2["user_id"]="";

		$controlMsg["info"][]=$lang_SearchUser_FusionOk;
	}
}


/*-------------------------------------------------------------------
 Create the interbredcrumps
--------------------------------------------------------------------*/
//$nameTools 			= $lang_SearchUser_SearchUser;
$interbredcrump[]	= array ("url"=>$rootAdminWeb, "name"=> $langAdministrationTools);
$interbredcrump[]	= array ("url"=>"index.php", "name"=> $langManage);
$noQUERY_STRING 	= TRUE;

if(!$display_choice && !$display_choiseShowFus && !$display_confirmFus && !$display_infoUserFus)
	$interbredcrump[]	= array ("url"=>$PHP_SELF."?searchForm", "name"=> $lang_SearchUser_SearchAUser);
elseif(!$display_choice)
	$nameTools = $lang_SearchUser_Merge;



// END OF WORKS



include($includePath."/claro_init_header.inc.php");

claro_disp_tool_title(
	array(
	'mainTitle'=>$nameTools,
	'subTitle'=> $PHP_AUTH_USER." - ".$siteName." - ".$clarolineVersion." - ".$dateNow
	)
	);
claro_disp_msg_arr($controlMsg);



//OUTPUT


/*-------------------------------------------------------------------
 Display the three choices of operation of a user(search,fusion and add)
--------------------------------------------------------------------*/
if($display_choice)
{
?>
<br>
<a href="<?php echo $PHP_SELF."?searchForm=1"?>"> <?php echo $lang_SearchUser_SearchEditDelete ?> </a>
<br>
<a href="<?php echo $PHP_SELF."?fusion=1"?>"> <?php echo $lang_SearchUser_Fusion ?> </a>
<br>
<a href="<?php echo "add_users.php"?>"> <?php echo $lang_SearchUser_Create ?> </a>

<?php
}



/*-------------------------------------------------------------------
 Display the form to search a user
--------------------------------------------------------------------*/
if ($display_form)
{
?>
<form action="<?php echo $PHP_SELF ?>" method="POST" target="_self" title="search a user" name="su">

<table  border="0" >

<tr>
<td width="250" align="RIGHT">	<label for="user_id"> <?php echo $lang_SearchUser_UserId." :"; ?> </label > </td>
<td align="left">	<input type="text" name="user_id" id="user_id" value="<?php echo $su_user_id ?>" size="8" maxlength="8" tabindex="10">
</td>
</tr>

<tr>
<td align="RIGHT">	<label for="lastname"> <?php echo $lang_SearchUser_LastName." :"; ?> </label >	</td>
<td>	<input type="text" name="lastname" id="lastname"  value="<?php echo $su_lastname ?>" size="20" maxlength="60" tabindex="10"> </td>
</tr>

<tr>
<td align="RIGHT">	<label for="firstname"> <?php echo $lang_SearchUser_FirstName." :"; ?> </label >	</td>
<td>	<input type="text" name="firstname" id="firstname"  value="<?php echo $su_firstname ?>" size="20" maxlength="60" tabindex="10"> </td>
</tr>

<tr>
<td align="RIGHT">	<label for="username"> <?php echo $lang_SearchUser_UserName." :"; ?> </label >	</td>
<td>	<input type="text" name="username" id="username"  value="<?php echo $su_username ?>" size="20" maxlength="20" tabindex="10"> </td>
</tr>

<tr>
<td align="RIGHT">	<label for="password"> <?php echo $lang_SearchUser_Password." :"; ?> </label >	</td>
<td>	<input type="text" name="password"  id="password" value="<?php echo $su_password ?>" size="20" maxlength="50" tabindex="10"> </td>
</tr>

<tr>
<td align="RIGHT">	<label for="authSource"> <?php echo $lang_SearchUser_AuthSource." :"; ?> </label >	</td>
<td>	<input type="text" name="authSource" id="authSource"  value="<?php echo $su_authSource ?>" size="20" maxlength="50" tabindex="10"> </td>
</tr>

<tr>
<td align="RIGHT">	<label for="email"> <?php echo $lang_SearchUser_Email." :"; ?> </label >	</td>
<td>	<input type="text" name="email" id="email"  value="<?php echo $su_email ?>" size="20" maxlength="100" tabindex="10"> </td>
</tr>

<tr>
<td align="RIGHT">	<label for="statut"> <?php echo $lang_SearchUser_Statut." :"; ?> </label >	</td>
<td>	<input type="radio" name="statut" checked="checked"  value="" > <?php echo $lang_SearchUser_YesandNo; ?>
		<input type="radio" name="statut"  value="1" <?php if($su_status==1) echo "checked=\"checked\""; ?>>
		<?php echo $lang_SearchUser_Yes;?>
		<input type="radio" name="statut"  value="5" <?php if($su_status==5) echo "checked=\"checked\""; ?>>
		<?php echo $lang_SearchUser_No;?>
</td>
</tr>

<tr>
<td align="RIGHT">	<label for="code"> <?php echo $lang_SearchUser_OfficialCode." :"; ?> </label >	</td>
<td>	<input type="text" name="code" id="code"  value="<?php echo $su_code ?>" size="20" maxlength="40" tabindex="10"></td>
</tr>

<tr>
<td align="RIGHT">	<label for="creatorId"> <?php echo $lang_SearchUser_CreatorId." :"; ?> </label >	</td>
<td>	<input type="text" name="creatorId" id="creatorId"  value="<?php echo $su_creatorId ?>" size="8" maxlength="8" tabindex="10"> </td>
</tr>

<tr>
<td>	<input type="hidden" name="order" value="nom, prenom">
</td>
<td><br>
</td>
</tr>

<tr>
<td>
</td>
<td align="LEFT">
<input type="submit" value="<?php echo $lang_SearchUser_ButtonSearch?>" name="search_user">
</td>
</tr>

</table>
</form>

<?php
}




/*-------------------------------------------------------------------
 Display the list user who correspond to the parameters
--------------------------------------------------------------------*/
if($display_listUser)
{
//display the input parameters
?>
<hr>
<b><u> <?php echo $lang_SearchUser_ParameterSearch." : "; ?> </u></b>
<br><br>
<?php if(!empty($su_user_id)){		echo "     ".$lang_SearchUser_UserId." : ".$su_user_id; ?>
<br> <?php } ?>
<?php if(!empty($su_lastname)){		echo "     ".$lang_SearchUser_LastName." : ".$su_lastname; ?>
<br> <?php } ?>
<?php if(!empty($su_firstname)){	echo "     ".$lang_SearchUser_FirstName." : ".$su_firstname; ?>
<br> <?php } ?>
<?php if(!empty($su_username)){ 	echo "     ".$lang_SearchUser_UserName." : ".$su_username; ?>
<br> <?php } ?>
<?php if(!empty($su_password)){ 	echo "     ".$lang_SearchUser_Password." : ".$su_password; ?>
<br> <?php } ?>
<?php if(!empty($su_authSource)){ 	echo "     ".$lang_SearchUser_AuthSource." : ".$su_authSource; ?>
<br> <?php } ?>
<?php if(!empty($su_email)){ 		echo "     ".$lang_SearchUser_Email." : ".$su_email; ?>
<br> <?php } ?>
<?php if(!empty($su_status)){ 		echo "     ".$lang_SearchUser_Statut." : ".$su_status; ?>
<br> <?php } ?>
<?php if(!empty($su_code)){ 		echo "     ".$lang_SearchUser_OfficialCode." : ".$su_code; ?>
<br> <?php } ?>
<?php if(!empty($su_creatorId)) 	echo "     ".$lang_SearchUser_CreatorId." : ".$su_creatorId; ?>
<hr> <br>

<!-- display the user's list -->
<table width="100%" class="claroTable">
  <thead>
    <tr>

	  <!-- Create the URL to display the list's user in a other order -->
	  <?php

	  $url=$PHP_SELF."?user_id=".$su_user_id."&lastname=".$su_lastname."&firstname=".$su_firstname."&username=".
	  $su_username."&password=".$su_password."&authSource=".$su_authSource."&email=".$su_email."&statut=".$su_status.
	  "&code=".$su_code."&phone=".$su_phone."&picture=".$su_picture."&creatorId=".$su_creatorId."&search_user";

	  ?>

	  <!-- $su_ascDesc is for display the list in the opposite sense -->
      <th scope=col><a href="<?php $ascD=(($su_order=="user_id") 		&& ($su_ascDesc=="ASC")?"DESC":"ASC");
	  	echo $url."&order=user_id&ascDesc=".$ascD."#lid";		?>" name="lid">
		<small> <?php echo $lang_SearchUser_UserId 		?> </small></a></th>

      <th scope=col><a href="<?php $ascD=(($su_order=="lastname") 		&& ($su_ascDesc=="ASC")?"DESC":"ASC");
	  	echo $url."&order=lastname&ascDesc=".$ascD."#last"; 	?>" name="last">
		<small> <?php echo $lang_SearchUser_LastName 		?> </small></a></th>

      <th scope=col><a href="<?php $ascD=(($su_order=="firstname") 	&& ($su_ascDesc=="ASC")?"DESC":"ASC");
	  	echo $url."&order=firstname&ascDesc=".$ascD."#first"; 	?>" name="first">
		<small> <?php echo $lang_SearchUser_FirstName 	?> </small></a></th>

      <th scope=col><a href="<?php $ascD=(($su_order=="username") 		&& ($su_ascDesc=="ASC")?"DESC":"ASC");
	  	echo $url."&order=username&ascDesc=".$ascD."#log"; 	?>" name="log">
		 <small> <?php echo $lang_SearchUser_UserName 		?> </small></a></th>

	  <th scope=col><a href="<?php $ascD=(($su_order=="password") 		&& ($su_ascDesc=="ASC")?"DESC":"ASC");
	  	echo $url."&order=password&ascDesc=".$ascD."#pass"; 	?>" name="pass">
		 <small> <?php echo $lang_SearchUser_Password 		?> </small></a></th>

	  <th scope=col><a href="<?php $ascD=(($su_order=="email") 		&& ($su_ascDesc=="ASC")?"DESC":"ASC");
	  	echo $url."&order=email&ascDesc=".$ascD."#mail"; 		?>" name="mail">
		 <small> <?php echo $lang_SearchUser_Email 		?> </small></a></th>

	  <th scope=col><a href="<?php $ascD=(($su_order=="code") 			&& ($su_ascDesc=="ASC")?"DESC":"ASC");
	  	echo $url."&order=code&ascDesc=".$ascDc."#code"; 		?>" name="code">
		 <small> <?php echo $lang_SearchUser_OfficialCode	?> </small></a></th>

	  <th scope=col><a href="<?php $ascD=(($su_order=="creatorId") 	&& ($su_ascDesc=="ASC")?"DESC":"ASC");
	  	echo $url."&order=creatorId&ascDesc=".$ascD."#creator"; 	?>" name="creator">
		 <small> <?php echo $lang_SearchUser_CreatorId 	?> </small></a></th>

      <th scope=col><a href="<?php $ascD=(($su_order=="authSource") 	&& ($su_ascDesc=="ASC")?"DESC":"ASC");
	  	echo $url."&order=authSource&ascDesc=".$ascD."#source"; 	?>" name="source">
		 <small> <?php echo $lang_SearchUser_AuthSource 	?> </small></a></th>

      <th scope=col><a href="<?php $ascD=(($su_order=="statut") 		&& ($su_ascDesc=="ASC")?"DESC":"ASC");
	  	echo $url."&order=statut&ascDesc=".$ascD."#stat"; 		?>" name="stat">
		 <small> <?php echo $lang_SearchUser_Statut 		?> </small></a></th>

	  <th scope=col> <small> <?php echo $lang_SearchUser_Edit; ?>  </small> </th>
	  <th scope=col> <small> <?php echo $lang_SearchUser_Delete; ?>  </small> </th>


	</tr>
  </thead>
  <tbody>

<?php

	foreach ($user as $one_user)
	{

?>
	<tr>
      <td align="CENTER">	<a  class="topBanner" href="<?php echo $PHP_SELF."?display=1&user_id=".$one_user["user_id"]; ?>" >
	  		<small> <?php echo $one_user["user_id"];?>  </small> </a>
	  </td>

      <td align="CENTER">	<small> <?php echo $one_user["lastname"];?> </small>
	  </td>
      <td align="CENTER">	<small> <?php echo $one_user["firstname"];?> </small>
	  </td>
      <td align="CENTER">  <small> <?php echo $one_user["username"];?> </small>
	  </td>
      <td align="CENTER">  <small> <?php echo $one_user["password"];?> </small>
	  </td>
	  <td align="CENTER">  <small> <a href="mailto:<?php echo $user["email"] ?>"> <?php echo $one_user["email"];?> </a> </small>
	  </td>
	  <td align="CENTER">  <small> <?php echo $one_user["code"];?> </small>
	  </td>
	  <td align="CENTER">	<small> <?php echo $one_user["creatorId"];?> </small>
	  </td>
      <td align="CENTER">  <small> <?php echo $one_user["authSource"];?> </small>
	  </td>
      <td align="CENTER">  <small> <?php echo (($one_user["statut"]==1)?"oui":"non"); ?> </small>
	  </td>
	  <td align="CENTER">  <a href="<?php echo "../../auth/profile.php?user_id=".$one_user["user_id"];
	   ?>">
	   <img src="../../img/edit.gif" border="0" alt="<?php echo $lang_SearchUser_imgEdit; ?>"> </a>
	  </td>
	  <td align="CENTER">  <a href="<?php echo $PHP_SELF."?delete=1&user_id=".$one_user["user_id"]; ?>"
	  onclick="javascript:if(!confirm('<?php echo addslashes(htmlentities($lang_SearchUser_ConfirmDelete.$one_user["user_id"])) ?>'))
	  return false;" >
	   <img src="../../img/delete.gif" border="0" alt="<?php echo $lang_SearchUser_imgDelete; ?>"> </a>
	  </td>


    </tr>
<?php
	}
?>
  </tbody>
</table>

<?php
}




/*-------------------------------------------------------------------
 Display user's informations
--------------------------------------------------------------------*/
if($display_user)
{
?>
	<table class="claroTable">
	<thead>
	<th scope=col> </th>
	<th scope=col> </th>
	<th scope=col> <?php echo "&nbsp;&nbsp;&nbsp;".$lang_SearchUser_Edit ?> </th>
	<th scope=col> <?php echo "&nbsp;&nbsp;&nbsp;".$lang_SearchUser_Delete ?></th>
	</tr>
  	</thead>
  		<tbody>
		<tr>
			<td width="250"> <?php echo $lang_SearchUser_UserId." : "; ?> </td>
			<td> <font color="#6666FF"> <?php echo $id;?> </font></td>

			<td align="center">  <a href="<?php echo
			"../../auth/profile.php?user_id=".$id; ?>" >
   				<img src="../../img/edit.gif" border="0" alt="<?php echo $lang_SearchUser_imgEdit; ?>"> </a>
			</td>
			<td align="center">  <a href="<?php echo $PHP_SELF."?delete=1&user_id=".$id; ?>"
				onclick="javascript:if(!confirm('<?php echo
				addslashes(htmlentities($lang_SearchUser_ConfirmDelete.$one_user["user_id"])) ?>')) return false;" >
   				<img src="../../img/delete.gif" border="0" alt="<?php echo $lang_SearchUser_imgDelete; ?>"> </a>
			</td>
		</tr>
		<tr>
			<td> <?php echo $lang_SearchUser_LastName." : "; ?> </td>
			<td> <font color="#6666FF"> <?php echo $user["lastname"];?> </font></td>
		</tr>
		<tr>
			<td> <?php echo $lang_SearchUser_FirstName." : "; ?> </td>
			<td> <font color="#6666FF"> <?php echo $user["firstname"];?> </font></td>
		</tr>
		<tr>
			<td> <?php echo $lang_SearchUser_UserName." : "; ?> </td>
			<td> <font color="#6666FF"> <?php echo $user["username"];?> </font></td>
		</tr>
		<tr>
			<td> <?php echo $lang_SearchUser_Password." : "; ?> </td>
			<td> <font color="#6666FF"> <?php echo $user["password"];?> </font></td>
		</tr>
		<tr>
			<td> <?php echo $lang_SearchUser_AuthSource." : "; ?> </td>
			<td> <font color="#6666FF"> <?php echo $user["authSource"];?> </font></td>
		</tr>
		<tr>
			<td> <?php echo $lang_SearchUser_Email." : "; ?> </td>
			<td> <font color="#6666FF"> <a href="mailto:<?php echo $user["email"] ?>"> <?php echo $user["email"];?>
			</a></font></td>
		</tr>
		<tr>
			<td> <?php echo $lang_SearchUser_Statut." : "; ?> </td>
			<td> <font color="#6666FF"> <?php echo (($user["statut"]==1)?$lang_SearchUser_Yes:$lang_SearchUser_No);?> </font></td>
		</tr>
		<tr>
			<td> <?php echo $lang_SearchUser_OfficialCode." : "; ?> </td>
			<td> <font color="#6666FF"> <?php echo $user["code"];?> </font></td>
		</tr>
		<tr>
			<td> <?php echo $lang_SearchUser_PhoneNumber." : "; ?> </td>
			<td> <font color="#6666FF"> <?php echo $user["phone"];?> </font></td>
		</tr>
		<tr>
			<td> <?php echo $lang_SearchUser_PictureUri." : "; ?> </td>
			<td> <font color="#6666FF"> <?php echo $user["picture"];?> </font></td>
		</tr>
		<tr>
			<td> <?php echo $lang_SearchUser_CreatorId." : "; ?> </td>
			<td> <font color="#6666FF"> <?php echo $user["creatorId"];?> </font></td>
		</tr>
	</tbody>
	</table>

	<br><br>

	<?php
}





/*-------------------------------------------------------------------
 Display courses's user
--------------------------------------------------------------------*/
if($display_userCourse)
{
	//If the are at least a course
    if(!empty($cours_user))
    {
    ?>
        <b> <u> <?php    if(!$display_course)
                            echo $lang_SearchUser_TitleCourses;
                        else
                            echo $lang_SearchUser_TitleCourse;
                ?> </u></b> <br><br>


        <table border="1" width="100%" class="claroTable">
        <thead>
            <tr>
            <th scope=col> <small>
            <?php if(!$display_course)
                    {
                        ?> <a href="<?php $ascD=(($su_orderC=="code")    && ($su_ascDescC=="ASC")?"DESC":"ASC");
                          echo $PHP_SELF."?display=1&user_id=".$id."&orderC=code&ascDescC=".$ascD."#sys";        ?>"
                        name="sys">
            <?php    }

                 echo $lang_SearchUser_SysCode." (".$lang_SearchUser_Edit.") ";
                 if($display_course)
                    echo " </a> ";
            ?> </small> </th>

            <th scope=col> <small>
            <?php if(!$display_course)
                    {
                    ?>    <a href="<?php $ascD=(($su_orderC=="statut")    && ($su_ascDescC=="ASC")?"DESC":"ASC");
                      echo $PHP_SELF."?display=1&user_id=".$id."&orderC=statut&ascDescC=".$ascD."#stat";        ?>"
                    name="stat">
            <?php    }

                echo $lang_SearchUser_StatutCourses;
                 if($display_course)
                    echo " </a> ";

            ?> </small> </th>

            <th scope=col> <small>
            <?php if(!$display_course)
                    {
                    ?> <a href="<?php $ascD=(($su_orderC=="role")    && ($su_ascDescC=="ASC")?"DESC":"ASC");
                          echo $PHP_SELF."?display=1&user_id=".$id."&orderC=role&ascDescC=".$ascD."#role";        ?>"
                        name="role">
            <?php    }

                echo $lang_SearchUser_Role;
                if($display_course)
                    echo " </a> ";

            ?> </small> </th>

            <th scope=col> <small>
            <?php if(!$display_course)
                    {
                    ?> <a href="<?php $ascD=(($su_orderC=="tutor")    && ($su_ascDescC=="ASC")?"DESC":"ASC");
                          echo $PHP_SELF."?display=1&user_id=".$id."&orderC=tutor&ascDescC=".$ascD."#tut";        ?>"
                        name="tut">
            <?php    }

                echo $lang_SearchUser_Tutor;
                if($display_course)
                    echo " </a> ";

            ?></small> </th>

            <th scope=col> <small>
            <?php if(!$display_course)
                    {
                    ?> <a href="<?php $ascD=(($su_orderC=="intitule")    && ($su_ascDescC=="ASC")?"DESC":"ASC");
                          echo $PHP_SELF."?display=1&user_id=".$id."&orderC=intitule&ascDescC=".$ascD."#int";        ?>"
                        name="int" >
            <?php     }

                echo $lang_SearchUser_Intitule;
                if($display_course)
                    echo " </a> ";

            ?> </small> </th>

            <th scope=col> <small>
            <?php if(!$display_course)
                    {
                    ?> <a href="<?php $ascD=(($su_orderC=="languageCourse")    && ($su_ascDescC=="ASC")?"DESC":"ASC");
                          echo $PHP_SELF."?display=1&user_id=".$id."&orderC=languageCourse&ascDescC=".$ascD."#lang";        ?>"
                        name="lang" >
            <?php     }

                echo $lang_SearchUser_langage;
                if($display_course)
                    echo " </a> ";

            ?> </small> </th>

            <th scope=col> <small>
            <?php if(!$display_course)
                    {
                    ?> <a href="<?php $ascD=(($su_orderC=="faculte")    && ($su_ascDescC=="ASC")?"DESC":"ASC");
                          echo $PHP_SELF."?display=1&user_id=".$id."&orderC=faculte&ascDescC=".$ascD."#fac";        ?>"
                        name="fac" >
            <?php     }

                echo $lang_SearchUser_faculte;
                if($display_course)
                    echo " </a> ";

            ?> </small> </th>

            <th scope=col> <small>
            <?php if(!$display_course)
                    {
                    ?> <a href="<?php $ascD=(($su_orderC=="titulaires")    && ($su_ascDescC=="ASC")?"DESC":"ASC");
                          echo $PHP_SELF."?display=1&user_id=".$id."&orderC=titulaires&ascDescC=".$ascD."#tit";        ?>"
                        name="tit">
            <?php     }

                echo $lang_SearchUser_Titulaire;
                if($display_course)
                    echo " </a> ";

            ?> </small> </th>

            <th scope=col> <small>
            <?php if(!$display_course)
                    {
                    ?> <a href="<?php $ascD=(($su_orderC=="fake_code")    && ($su_ascDescC=="ASC")?"DESC":"ASC");
                          echo $PHP_SELF."?display=1&user_id=".$id."&orderC=fake_code&ascDescC=".$ascD."#fak";        ?>"
                        name="fac" >
            <?php    }

                echo $lang_SearchUser_fakeCode;
                if($display_course)
                    echo " </a> ";

             ?> </small> </th>

    <?php     if(!$display_course)
            {
    ?>
                <th scope=col> <small> <?php echo $lang_SearchUser_group        ?> </small> </th>
    <?php    }
    ?>
            <th scope=col> <small> <?php echo $lang_SearchUser_link                ?> </small> </th>
			<th scope=col> <small> <?php echo $lang_SearchUser_DeleteOfCourse;		?></small></th>
            </tr>

        </thead>
        <tbody>

        <?php
        foreach ($cours_user as $num_course => $one_course )
        {
        ?>
            <tr>
                <td align="CENTER"> <a href="<?php echo "search_course.php?display_course&sysCode=".$one_course["sysCode"]; ?>"
                 > <small><?php echo $one_course["sysCode"]; ?> </small></a>
                </td>
                <td align="CENTER"> <small> <?php echo
                 (($one_course["statut"]==1)?$lang_SearchUser_StatutAdmin:$lang_SearchUser_StatutUser); ?> </small>
                 </td>
                <td align="CENTER"> <small> <?php echo $one_course["role"]; ?> </small>
                </td>
                <td align="CENTER"> <small> <?php echo (($one_course["titular"]==1)?$lang_SearchUser_Yes:$lang_SearchUser_No); ?>
                 </small>
                </td>
                <td align="CENTER"> <small> <?php echo $one_course["intitule"]; ?> </small>
                </td>
                <td align="CENTER"> <small> <?php echo $one_course["languageCourse"]; ?> </small>
                </td>
                <td align="CENTER"> <small> <?php echo $one_course["faculte"]; ?> </small>
                </td>
                <td align="CENTER"> <small> <?php echo $one_course["titulaires"]; ?> </small>
                </td>
                <td align="CENTER"> <small> <?php echo $one_course["fake_code"]; ?> </small>
                </td>
        <?php     if(!$display_course)
                {
        ?>
                <td align="CENTER"> <small> <a href="<?php echo
                     $PHP_SELF."?display=1&user_id=".$id."&cours_id=".$one_course["cours_id"];
                     ?>" ><?php echo $lang_SearchUser_See ?> </small> </a>
                </td>
        <?php    }
        ?>
                <td align="CENTER"> <small> <a href="<?php echo $rootWeb.$one_course["directory"] ?> ">
                    <?php echo $lang_SearchUser_See; ?> </small> </a>
                </td>
				<td align="CENTER"> <small> <a href="
				<?php echo $PHP_SELF."?display=1&user_id=".$id."&deleteOfCourse=".$one_course["sysCode"]; ?>">
				<img src="../../img/delete.gif" border="0" alt="<?php echo $lang_SearchCourse_imgDelete; ?>"> </a> </small>
				</td>
            </tr>

        <?php
        }
        ?>
        </tbody>
        </table>

    <?php
    }
}




/*-------------------------------------------------------------------
Display informations from the choosed course
--------------------------------------------------------------------*/
if($display_course)
{
    if(!empty($courseUserGroup))
    {
?>
        <br><br> <b> <u> <?php echo $lang_SearchUser_TitleTeam; ?> </u></b> <br><br>

        <table border="1" width="100%" class="claroTable">
            <thead>
                <tr>
                <th scope=col> <small> <?php echo $lang_SearchUser_Role             ?> </small> </th>
                <th scope=col> <small> <?php echo $lang_SearchUser_Name             ?> </small> </th>
                <th scope=col> <small> <?php echo $lang_SearchUser_Titulaire         ?> </small> </th>
                <th scope=col> <small> <?php echo $lang_SearchUser_SecretDirectory     ?> </small> </th>
                <th scope=col> <small> <?php echo $lang_SearchUser_Description         ?> </small> </th>
                <th scope=col> <small> <?php echo $lang_SearchUser_InfoGroup         ?> </small> </th>
				<th scope=col> <small> <?php echo $lang_SearchUser_DeleteOfGroup		?> </small></th>
                </tr>
            </thead>
            <tbody>

        <?php

		//display group
        foreach ($courseUserGroup as $num_courseUserGroup => $one_courseUserGroup )
        {
        ?>

            <tr>
                <td align="CENTER"> <small> <?php echo $one_courseUserGroup["role"];                 ?> </small>
                </td>
                <td align="CENTER"> <small> <?php echo $one_courseUserGroup["name"];                 ?> </small>
                </td>
                <td align="CENTER"> <small> <?php echo $one_courseUserGroup["tutor"];                 ?> </small>
                </td>
                <td align="CENTER"> <small> <?php echo $one_courseUserGroup["secretDirectory"];     ?> </small>
                </td>
                <td align="CENTER"> <small> <?php echo $one_courseUserGroup["description"];         ?> </small>
                </td>
                <td align="CENTER"> <small> <a href="<?php echo
                $rootWeb."claroline/group/group_space.php?gidReq=".$one_courseUserGroup["id_group"]."&cidReq=".$one_course["sysCode"];
                ?>"> <?php echo $lang_SearchUser_See; ?>  </a>
                </td>
				<td align="CENTER"> <small> <a href="
				<?php echo $PHP_SELF."?display=1&user_id=".$id."&cours_id=".$one_course["cours_id"]."&sysCode=".$one_course["sysCode"]."&deleteOfGroup=".$one_courseUserGroup["id_group"];; ?>">
				<img src="../../img/delete.gif" border="0" alt="<?php echo $lang_SearchUser_imgDelete; ?>"> </small></td>
            </tr>
        <?php
        }
        ?>
        </tbody>
        </table>
    <?php
    }
?>
	<br><br>
	<a href="<?php echo $PHP_SELF."?display=1&user_id=".$id; ?>"> <?php echo  $lang_SearchUser_LinkCourses; ?> </a>
<?php
}



/*-------------------------------------------------------------------
Display informations of the users fusion
--------------------------------------------------------------------*/
if($display_infoUserFus)
{
?>
    <table border="0" width="100%" cellspacing="1" cellpadding="1">
    <thead>
                <tr>
                <th scope=col align="LEFT"> <?php echo $lang_SearchUser_MajorUser        ?> </th>
                <th scope=col align="LEFT"> <?php echo $lang_SearchUser_Info            ?> </th>
                <th scope=col align="LEFT"> <?php echo $lang_SearchUser_MinorUser         ?> </th>
                <th scope=col align="LEFT"> <?php echo $lang_SearchUser_Info             ?> </th>
                </tr>
    </thead>
    <tbody>
        <tr> <td><br></td> </tr>
        <tr>
            <td width="250"> <?php echo $lang_SearchUser_UserId." : "; ?> </td>
            <td> <a href="<?php echo $PHP_SELF."?search_user&user_id=".$user1["user_id"];?>">
            <?php echo $user1["user_id"];?> </a></td>
            <td width="250"> <?php echo $lang_SearchUser_UserId." : "; ?> </td>
            <td> <a href="<?php echo $PHP_SELF."?search_user&user_id=".$user2["user_id"];?>">
            <?php echo $user2["user_id"];?> </a></td>
        </tr>
        <tr>
            <td> <?php echo $lang_SearchUser_LastName." : "; ?> </td>
            <td> <font color="#6666FF"> <?php echo $user1["lastname"];?> </font></td>
            <td> <?php echo $lang_SearchUser_LastName." : "; ?> </td>
            <td> <font color="#6666FF"> <?php echo $user2["lastname"];?> </font></td>
        </tr>
        <tr>
            <td> <?php echo $lang_SearchUser_FirstName." : "; ?> </td>
            <td> <font color="#6666FF"> <?php echo $user1["firstname"];?> </font></td>
            <td> <?php echo $lang_SearchUser_FirstName." : "; ?> </td>
            <td> <font color="#6666FF"> <?php echo $user2["firstname"];?> </font></td>
        </tr>
        <tr>
            <td> <?php echo $lang_SearchUser_UserName." : "; ?> </td>
            <td> <font color="#6666FF"> <?php echo $user1["username"];?> </font></td>
            <td> <?php echo $lang_SearchUser_UserName." : "; ?> </td>
            <td> <font color="#6666FF"> <?php echo $user2["username"];?> </font></td>
        </tr>
        <tr>
            <td> <?php echo $lang_SearchUser_Password." : "; ?> </td>
            <td> <font color="#6666FF"> <?php echo $user1["password"];?> </font></td>
            <td> <?php echo $lang_SearchUser_Password." : "; ?> </td>
            <td> <font color="#6666FF"> <?php echo $user2["password"];?> </font></td>
        </tr>
        <tr>
            <td> <?php echo $lang_SearchUser_AuthSource." : "; ?> </td>
            <td> <font color="#6666FF"> <?php echo $user1["authSource"];?> </font></td>
            <td> <?php echo $lang_SearchUser_AuthSource." : "; ?> </td>
            <td> <font color="#6666FF"> <?php echo $user2["authSource"];?> </font></td>
        </tr>
        <tr>
            <td> <?php echo $lang_SearchUser_Email." : "; ?> </td>
            <td> <font color="#6666FF"> <?php echo $user1["email"];?> </font></td>
            <td> <?php echo $lang_SearchUser_Email." : "; ?> </td>
            <td> <font color="#6666FF"> <?php echo $user2["email"];?> </font></td>
        </tr>
        <tr>
            <td> <?php echo $lang_SearchUser_Statut." : "; ?> </td>
            <td> <font color="#6666FF">
            <?php if(!empty($user1["statut"]))
                     echo ($user1["statut"]==1?$lang_SearchUser_Yes:$lang_SearchUser_No);
            ?> </font></td>
            <td> <?php echo $lang_SearchUser_Statut." : "; ?> </td>
            <td> <font color="#6666FF">
            <?php if(!empty($user2["statut"]))
                    echo ($user2["statut"]==1?$lang_SearchUser_Yes:$lang_SearchUser_No);
            ?> </font></td>
        </tr>
        <tr>
            <td> <?php echo $lang_SearchUser_OfficialCode." : "; ?> </td>
            <td> <font color="#6666FF"> <?php echo $user1["code"];?> </font></td>
            <td> <?php echo $lang_SearchUser_OfficialCode." : "; ?> </td>
            <td> <font color="#6666FF"> <?php echo $user2["code"];?> </font></td>
        </tr>
        <tr>
            <td> <?php echo $lang_SearchUser_PhoneNumber." : "; ?> </td>
            <td> <font color="#6666FF"> <?php echo $user1["phone"];?> </font></td>
            <td> <?php echo $lang_SearchUser_PhoneNumber." : "; ?> </td>
            <td> <font color="#6666FF"> <?php echo $user2["phone"];?> </font></td>
        </tr>
        <tr>
            <td> <?php echo $lang_SearchUser_CreatorId." : "; ?> </td>
            <td> <font color="#6666FF"> <?php echo $user1["creatorId"];?> </font></td>
            <td> <?php echo $lang_SearchUser_CreatorId." : "; ?> </td>
            <td> <font color="#6666FF"> <?php echo $user2["creatorId"];?> </font></td>
        </tr>
    </tbody>
    </table>

    <br>
    <hr>
<?php
}




/*-------------------------------------------------------------------
Display form to fusion two users
--------------------------------------------------------------------*/
if($display_choiseShowFus)
{
?>
<form action="<?php echo $PHP_SELF ?>">
<br>
<table border="0">
<tr>
<td>    <label for="user1"> <?php echo $lang_SearchUser_MajorUser." :"; ?> </label >
</td>
<td align="LEFT"> <input type="text" name="user1" value="<?php echo $user1["user_id"] ?>" size="5" maxlength="10">
</td>
<td> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </td>
<td>    <label for="user2"> <?php echo $lang_SearchUser_MinorUser." :"; ?> </label >
</td>
<td align="LEFT"> <input type="text" name="user2" value="<?php echo $user2["user_id"] ?>" size="5" maxlength="10">
</td>
</tr>

</table>
<br>
    <input type="radio" name="showORfus" value="show" checked="checked"> <?php echo  $lang_SearchUser_SeeInfoUser; ?><br>
    <input type="radio" name="showORfus" value="fusion"> <?php echo $lang_SearchUser_FusionUser; ?>
<br><br>

    <input type="submit" value="<?php echo $lang_SearchUser_Treat ?>" name="lookConflit">

<?php
}



/*-------------------------------------------------------------------
Display page to confirm the fusion
--------------------------------------------------------------------*/
if($display_confirmFus)
{
?>
<form action="<?php echo $PHP_SELF ?>">

    <input type="hidden" name="user1" value="<?php echo $_REQUEST["user1"]?>">
    <input type="hidden" name="user2" value="<?php echo $_REQUEST["user2"]?>">

    <input type="submit" name="makeFusion" value="Fusionner"
     <?php if($CourseConflit)
             {
                 echo " onclick=\"javascript:if(!confirm('".
                addslashes(htmlentities($lang_SearchUser_Goose.$CourseConflit.$lang_SearchUser_ContinueFusion))."'))return false;\"";
            } ?>
    >
</form>

<?php
}

include($includePath."/claro_init_footer.inc.php");

?>
