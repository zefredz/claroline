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

$langFile = "searchCourse";
$cidReset=TRUE;
require '../../inc/claro_init_global.inc.php';
include($includePath."/lib/text.lib.php");
include($includePath."/lib/debug.lib.inc.php");
include("../../inc/conf/add_course.conf.php");
include("../../inc/lib/pclzip/pclzip.lib.php");
include("../../inc/lib/course.lib.inc.php");
include("../../inc/lib/faculty.lib.inc.php");

$dateNow 			= claro_format_locale_date($dateTimeFormatLong);
$is_allowedToAdmin 	= $is_platformAdmin;

//TABLES

$tbl_user 			= $mainDbName."`.`user";
$tbl_courses		= $mainDbName."`.`cours";
$tbl_course_user	= $mainDbName."`.`cours_user";
$tbl_faculty 		= $mainDbName."`.`faculte";

//WORKS
$display_choice		= TRUE;
$display_form 		= FALSE;
$display_list		= FALSE;
$display_course		= FALSE;
$display_group		= FALSE;
$display_user		= FALSE;
$display_editGroup	= FALSE;
$display_askWithUser= FALSE;

if(!$is_allowedToAdmin)
{
	$display_choice	= FALSE;
	$controlMsg["error"][]=$lang_SearchCourse_NoAdmin;
}
else
{
	/*-------------------------------------------------------------------
	To display the form search a course
	--------------------------------------------------------------------*/
	if(isset($_REQUEST["choiceSearch"]))
	{
		$display_choice	= FALSE;
		$display_form	= TRUE;
		$sql_searchfaculty = "select * FROM `$tbl_faculty` order by treePos";
		$arrayFaculty=claro_sql_query_fetch_all($sql_searchfaculty);
	}



	/*-------------------------------------------------------------------
	For search a course with sure parameters
	--------------------------------------------------------------------*/
	if(isset($_REQUEST["searchCourse"]))
	{
		$display_choice=FALSE;
		$display_form=TRUE;

		$sc_sysCode		=trim($_REQUEST["sc_sysCode"]);
		$sc_intitule	=trim($_REQUEST["sc_intitule"]);
		$sc_langage		=trim($_REQUEST["sc_langage"]);
		$sc_faculte		=trim($_REQUEST["sc_faculte"]);
		$sc_titulaire	=trim($_REQUEST["sc_titulaire"]);
		$sc_fakeCode	=trim($_REQUEST["sc_fakeCode"]);
		$sc_dateDay		=trim($_REQUEST["sc_dateDay"]);
		$sc_dateMound	=trim($_REQUEST["sc_dateMound"]);
		$sc_dateYear	=trim($_REQUEST["sc_dateYear"]);
		$sc_date		=$_REQUEST["sc_date"];
		$sc_order		=($_REQUEST["order"]?$_REQUEST["order"]:"code");
		$sc_ascDesc		=($_REQUEST["ascDesc"]?$_REQUEST["ascDesc"]:"ASC");

			// Error if anyone input parameters
		if(empty($sc_sysCode) && empty($sc_intitule) && empty($sc_langage) && empty($sc_faculte)
				&& empty($sc_titulaire) && empty($sc_fakeCode) && empty($sc_dateDay) && empty($sc_dateMound)
				&& empty($sc_dateYear) && empty($sc_date))
		{
			$controlMsg["error"][]= $lang_SearchCourse_NoParameter;
			$sql_searchfaculty = "select * FROM `$tbl_faculty` order by treePos";
			$arrayFaculty=claro_sql_query_fetch_all($sql_searchfaculty);
		}
		//Validate the date
		elseif((!empty($sc_dateDay) || !empty($sc_dateMound) || !empty($sc_dateYear)) && !checkdate($sc_dateMound,$sc_dateDay,$sc_dateYear))
		{
			$controlMsg["error"][]=$lang_SearchCourse_DateInvalidate;
			$sql_searchfaculty = "select * FROM `$tbl_faculty` order by treePos";
			$arrayFaculty=claro_sql_query_fetch_all($sql_searchfaculty);
		}
		else
		{
			//Create the date for the data base
			if(!empty($sc_dateDay))
				$sc_date=sprintf("%04d-%02d-%02d",$sc_dateYear,$sc_dateMound,$sc_dateDay);

			//Search the courses
			$sql_searchCourse = "
				select * FROM `".$tbl_courses."` WHERE ".

			(!empty($sc_sysCode)	?"UPPER(`code`)				LIKE '".strtoupper($sc_sysCode)."'		AND ":"").
			(!empty($sc_intitule)	?"UPPER(`intitule`)			LIKE '".strtoupper($sc_intitule)."'		AND ":"").
			(!empty($sc_langage)	?"UPPER(`languageCourse`)	LIKE '".strtoupper($sc_langage)."'		AND ":"").
			(!empty($sc_faculte)	?"UPPER(`faculte`)			LIKE '".strtoupper($sc_faculte)."'		AND ":"").
			(!empty($sc_titulaire)	?"UPPER(`titulaires`)		LIKE '".strtoupper($sc_titulaire)."'	AND ":"").
			(!empty($sc_fakeCode)	?"UPPER(`fake_code`)		LIKE '".strtoupper($sc_fakeCode)."'		AND ":"").
			(!empty($sc_date)		?"`creationDate`			>=   '".$sc_date."'						AND ":"").
			"1
			ORDER by ".$sc_order." ".$sc_ascDesc;

			$arrayCourse = claro_sql_query_fetch_all($sql_searchCourse);

			//No courses whith this parameters
			if(!$arrayCourse)
			{
				$controlMsg["warning"][]=$lang_SearchCourse_NoCourse;
				$sql_searchfaculty = "select * FROM `$tbl_faculty` order by treePos";
				$arrayFaculty=claro_sql_query_fetch_all($sql_searchfaculty);
			}
			elseif(count($arrayCourse)>1)  //If one course
			{
				$display_form=FALSE;
				$display_list=TRUE;
			}
			elseif(count($arrayCourse)==1) //If several courses
			{
				$display_form	= FALSE;
				$one_result		= TRUE;
			}
		}
	}




	/*-------------------------------------------------------------------
	Edit course of change parameters of the course
	--------------------------------------------------------------------*/
	if(isset($_REQUEST["editGroup"]) || isset($_REQUEST["changeGroup"]))
	{
		$display_choice		= FALSE;
		$display_editGroup	= TRUE;

		//Search the course
		$sql_searchCourse="select * from `$tbl_courses` where code='".$_REQUEST["sysCode"]."'";
		$arrayCourse=claro_sql_query_fetch_all($sql_searchCourse);

		$_course["dbNameGlu"]	= $courseTablePrefix . $arrayCourse[0]["dbName"] . $dbGlu;
		$tbl_rel_usergroup 		= $_course["dbNameGlu"]."group_rel_team_user";
		$tbl_group 		   		= $_course["dbNameGlu"]."group_team";

		//Search the groups of the course
		$sql_searchGroup="select * from `$tbl_group` where id=".$_REQUEST["idGroup"];
		$array_group=claro_sql_query_fetch_all($sql_searchGroup);
		$array_group=$array_group[0];
	}




	/*-------------------------------------------------------------------
	Change parameters of a group
	--------------------------------------------------------------------*/
	if(isset($_REQUEST["changeGroup"]))
	{
		$display_editGroup	= FALSE;
		$one_result			= TRUE;

		//Count the number of student in this group
		$sql_searchUser="select count(id) num from `$tbl_rel_usergroup` where team='".$_REQUEST["idGroup"]."'";
		$nbUserGroup=claro_sql_query_fetch_all($sql_searchUser);
		$nbUserGroup=$nbUserGroup[0]["num"];

		//If no changing
		if(!strcmp($array_group["name"],$_REQUEST["name"]) && !strcmp($array_group["tutor"],$_REQUEST["tutor"]) &&
			!strcmp($array_group["forumId"],$_REQUEST["forumId"]) && !strcmp($array_group["maxStudent"],$_REQUEST["maxStudent"]) &&
			!strcmp($array_group["description"],$_REQUEST["description"]))
		{
			$controlMsg["warning"][]=$lang_SearchCourse_NoChange;
		}
		//error if the name of max studen is null
		elseif( !strcmp($_REQUEST["name"],"") || !strcmp($_REQUEST["maxStudent"],"") )
		{
				if(!strcmp($_REQUEST["name"],""))
				$controlMsg["error"][]=$lang_SearchCourse_GroupNoName;

			if(!strcmp($_REQUEST["maxStudent"],""))
				$controlMsg["error"][]=$lang_SearchCourse_MaxStudentNoNull;
		}
		//check if the forumId, maxStudent ant tutor is numeric and the new number of maxStudent is > of the number of student in this group
		elseif(!is_numeric(trim($_REQUEST["forumId"])) || !is_numeric(trim($_REQUEST["maxStudent"])) ||
				(strcmp($_REQUEST["tutor"],"") &&  !is_numeric(trim($_REQUEST["tutor"])) )
				|| !strcmp($_REQUEST["name"],"") || !strcmp($_REQUEST["maxStudent"],"") || $nbUserGroup>$_REQUEST["maxStudent"])
		{
			if(strcmp($_REQUEST["tutor"],"") && !is_numeric($_REQUEST["tutor"]))
				$controlMsg["error"][]=$lang_SearchCourse_TutorNoInt;

			if(!is_numeric($_REQUEST["forumId"]))
				$controlMsg["error"][]=$lang_SearchCourse_IdForumNoInt;

			if(!is_numeric($_REQUEST["maxStudent"]))
				$controlMsg["error"][]=$lang_SearchCourse_NbMaxStudentNoInt;

			if(is_numeric($_REQUEST["maxStudent"]) && $nbUserGroup>$_REQUEST["maxStudent"])
				$controlMsg["error"][]=$lang_SearchCourse_MaxStudentToSmall;
		}
		else
		{
			//If is empty -> NULL in the data base
			$res_tutor=(!strcmp($_REQUEST["tutor"],"")?"NULL":"'".$_REQUEST["tutor"]."'");
			$res_forum=(!strcmp($_REQUEST["forumId"],"")?"NULL":"'".$_REQUEST["forumId"]."'");
			$res_description=(!strcmp($_REQUEST["description"],"")?"NULL":"'".$_REQUEST["description"]."'");

			//Change the parameter of the group
			$sql_update="update `$tbl_group` set name='".$_REQUEST["name"]."',tutor=".$res_tutor.",
						forumId=".$res_forum.",maxStudent='".$_REQUEST["maxStudent"]."',
						description=".$res_description." where id=".$_REQUEST["idGroup"];
			claro_sql_query($sql_update);

			$controlMsg["info"][]=$lang_SearchCourse_NewParamSav;
		}
	}





	/*-------------------------------------------------------------------
	Backup of a course
	--------------------------------------------------------------------*/
	if(isset($_REQUEST["backup"]))
	{
		//Create de file archive if don't exist
		if(!is_dir($localArchivesRepository))
			mkdir($localArchivesRepository);

		//If the platform is in multi database mode
		if( ! $singleDbEnabled)
		{
			backupDatabase($db,$_REQUEST["sysCode"],$dir);
		}
		else //If the platform is in mono database mode
		{
			$sql="select dbName from `$tbl_courses` where code='".$_REQUEST["sysCode"]."'";
			$res=claro_sql_query_fetch_all($sql);

			$currentCourseDbNameGlu=$courseTablePrefix.$res[0]["dbName"].$dbGlu;

			// Search all tables of this course
			$sql = "SHOW TABLES LIKE \"".$currentCourseDbNameGlu."%\"";
			$result=claro_sql_query($sql);

			// Backup of all tables of the course
			$i=0;
			while($result && $i<count($result))
			{
				backupDatabase($db,$_REQUEST["sysCode"],$dir);
				$i++;
			}
		}

		$i=strpos($dir,"/cours",0);
		$directory=substr($dir,0,$i);
		$controlMsg["info"][]=$lang_SearchCourse_BackupOk.$directory.".zip";
	}





	/*-------------------------------------------------------------------
	Delete a group
	--------------------------------------------------------------------*/
	if(isset($_REQUEST["deleteGroup"]))
	{
		$one_result=TRUE;

		//Search informations of the course
		$sql_searchCourse="select * from `$tbl_courses` where code='".$_REQUEST["sysCode"]."'";
		$arrayCourse=claro_sql_query_fetch_all($sql_searchCourse);

		$_course["dbNameGlu"]	= $courseTablePrefix . $arrayCourse[0]["dbName"] . $dbGlu;
		$tbl_rel_usergroup 		= $_course["dbNameGlu"]."group_rel_team_user";
		$tbl_group 		   		= $_course["dbNameGlu"]."group_team";

		//count the number of student in this group
		$sql_searchUserCourse="select count(id) num from `$tbl_rel_usergroup` where team=".$_REQUEST["idGroup"];
		$nbUserGroup=claro_sql_query_fetch_all($sql_searchUserCourse);
		$nbUserGroup=$nbUserGroup[0]["num"];

		//Dont delete the group if they are a student inscribe
		if($nbUserGroup>0)
			$controlMsg["error"][]=$lang_SearchCourse_GroupHaveStudent;
		else
		{
			//delete the group
			$sql_deleteGroup="delete from `$tbl_group` where id=".$_REQUEST["idGroup"];
			claro_sql_query($sql_deleteGroup);
		}
	}




	/*-------------------------------------------------------------------
	To display information of one course
	--------------------------------------------------------------------*/
	if(isset($_REQUEST["display_course"]) || $one_result)
	{
		//For the sort
		$sc_order	=(isset($_REQUEST["order"])?$_REQUEST["order"]:"team");
		$sc_ascDesc	=(isset($_REQUEST["ascDesc"])?$_REQUEST["ascDesc"]:"ASC");
		$sc_orderG 	=(isset($_REQUEST["orderG"])?$_REQUEST["orderG"]:"name");
		$sc_ascDescG=(isset($_REQUEST["ascDescG"])?$_REQUEST["ascDescG"]:"ASC");

		//If informations of the course is not already search
		if(isset($_REQUEST["display_course"]) || isset($_REQUEST["check"]) || isset($_REQUEST["deleteGroup"]) ||
			isset($_REQUEST["deleteCourse"]) )
		{
			$sql_searchCourse="select * from `$tbl_courses` where code='".$_REQUEST["sysCode"]."'";
			$arrayCourse=claro_sql_query_fetch_all($sql_searchCourse);
		}

		$_course["dbNameGlu"]	= $courseTablePrefix . $arrayCourse[0]["dbName"] . $dbGlu;
		$tbl_rel_usergroup 		= $_course["dbNameGlu"]."group_rel_team_user";
		$tbl_group 		   		= $_course["dbNameGlu"]."group_team";

		//If inscribe'nt student
		if(isset($_REQUEST["check"]))
		{
			$display_choice	= FALSE;
			$display_course	= TRUE;
			$display_user	= TRUE;

			$arrayCheckUser=$_REQUEST["check"];

			//for each student inscibe'nt
			foreach($arrayCheckUser as $one_user)
			{
				//delete de student in the table group_rel_team_user and cours_user
				$sql_deleteInscribe="delete from `$tbl_rel_usergroup` where user='".$one_user."'";
				claro_sql_query($sql_deleteInscribe);

				$sql_deleteInscribe="delete from `$tbl_course_user` where user_id='".$one_user."' and
										code_cours='".$_REQUEST["sysCode"]."'";
				claro_sql_query($sql_deleteInscribe);
			}
		}

		//If they are one course
		if(count($arrayCourse)==1)
		{
			$display_choice=FALSE;
			$display_course=TRUE;

			$arrayCourse=$arrayCourse[0];

			//Search the groups of the course
			$sql_searchGroup="select * from `$tbl_group` order by ".$sc_orderG." ".$sc_ascDescG;
			$array_group=claro_sql_query_fetch_all($sql_searchGroup);

			//If they are groups, display this groups
			if($array_group && count($array_group)>0)
				$display_group=TRUE;
			else
				$controlMsg["info"][]=$lang_SearchCourse_CoursHaventGroup;

			//Select all student in this cours (inscribe in a group of no)
			$sql_searchUserCourse="SELECT u.prenom firstname, u.nom lastname,u.email email,
										`cu`.user_id user,
										ug.team team
									FROM `$tbl_user` u,`$tbl_course_user` cu
									LEFT JOIN `$tbl_rel_usergroup` ug
										ON `cu`.user_id = `ug`.`user`
									where
									cu.code_cours='".$arrayCourse["code"]."'
									AND `cu`.user_id = `u`.`user_id`";
			$array_user=claro_sql_query_fetch_all($sql_searchUserCourse);

			if($array_user && count($array_user)>0)
			{
				$display_user=TRUE;
				//count the number of student
				$nbStudent=count($array_user);

				foreach($array_user as $key => $one_user)
				{
					//Search the name of the group for each student
					if($one_user["team"]==0)
						$array_user[$key]["team"]=$lang_SearchCourse_anyTeam;
				}

				//Sort the table
				foreach($array_user as $key => $one_user)
					$array_param[$key]=$one_user[$sc_order];

				if(!strcmp($sc_ascDesc,"ASC"))
					asort($array_param);
				else
					arsort($array_param);
			}
			else
				$controlMsg["info"][]=$lang_SearchCourse_HaventStudentInCourse;
		}
	}





	/*-------------------------------------------------------------------
	Ask if restore the cours with her student
	--------------------------------------------------------------------*/
	if(isset($_REQUEST["askWithUser"]))
	{
		$display_choice		= FALSE;

		if(isset($_REQUEST["fileRestore"]))
		{
			$display_askWithUser= TRUE;
			$fileRestore=$_REQUEST["fileRestore"];
		}
		else
		{
			$display_restore=TRUE;
			$controlMsg["error"][]=$lang_SearchCourse_NoCoursSelected;
		}
	}





	/*-------------------------------------------------------------------
	Restore the course
	--------------------------------------------------------------------*/
	if(isset($_REQUEST["restore"]))
	{
		$display_choice	=FALSE;
		$display_restore=TRUE;

		//The backup file
		$fileRestore=$_REQUEST["fileRestore"];
		$i=strpos($fileRestore,".zip",0);
		$fileRestore=substr($fileRestore,0,$i);

		$archive = new PclZip($localArchivesRepository.$fileRestore.".zip");

		if(!is_dir($localArchivesRepository.$fileRestore ))
			mkdir($localArchivesRepository.$fileRestore);

		$archive->extract(PCLZIP_OPT_PATH,$localArchivesRepository.$fileRestore);

		//Take the sysCode of the course
		$array=explode("_",$fileRestore);
		$sysCodeCours=$array[count($array)-4];

		if($singleDbEnabled)
			$tableCoursRestore=$mainDbName;
		else
			$tableCoursRestore=$dbNamePrefix.$sysCodeCours;

		$sql="select cours_id from `$tbl_courses` where code='".$sysCodeCours."'";
		$res=claro_sql_query_fetch_all($sql);

		if( ($res && count($res)>0))
		{
			$controlMsg["error"][]=$lang_SearchCourse_coursExist;
		}
		else
		{
			if(file_exists($localArchivesRepository.$fileRestore."/doc/".$sysCodeCours))
				copyDirTo($localArchivesRepository.$fileRestore."/doc/".$sysCodeCours."/",
					$coursesRepositorySys.$arrayCourse[0]["directory"]);

			//restore
			$ret=array();
			$dir=$localArchivesRepository.$fileRestore."/cours/".$fileRestore.".sql";
			$fp=fopen($dir,"r");

			if($fp)
			{
				$sql=fread($fp,filesize($dir));
				PMA_splitSqlFile($ret, $sql);

				//Restore
				foreach($ret as $com)
					mysql_query_dbg($com);
			}

			$sql="select * from `".$mainDbName."`.`temp_cours`";
			$res_cours=claro_sql_query_fetch_all($sql);
			$res_cours=$res_cours[0];

			if($_REQUEST["restoreUser"]==1)
			{
				$sql="select * from `".$mainDbName."`.`temp_user`";
				$res_user=claro_sql_query_fetch_all($sql);

				$sql="select * from `".$mainDbName."`.`temp_cours_user`";
				$res_coursUser=claro_sql_query_fetch_all($sql);
			}

			$controlMsg["info"][]=$lang_SearchCourse_RestoreOk;

			$sql="select max(cours_id) num from `$tbl_courses`";
			$res=claro_sql_query_fetch_all($sql);
			$newId=$res[0]["num"]+1;

			$i=0;
			if($res_cours && count($res_cours)>0)
				foreach($res_cours as $one_res)
				{
					//the first value is the id_cours
					if($i==0)
						$values.=$newId;
					else
					{
						$values.=",";

						if($one_res==NULL)
							$values.="NULL";
						else
							$values.="'".addslashes($one_res)."'";
					}
					$i++;
				}

			//Restore the course
			$sql="insert into `$tbl_courses` values(".$values.");";
			claro_sql_query($sql);

			//Restore user cours
			//Look if the user always exist
			if($_REQUEST["restoreUser"]==1)
			{
				if($res_user && count($res_user))
				{
					foreach($res_user as $one_user)
					{
						unset($select);
						$i=0;
						foreach($one_user as $key=>$one_res)
						{
							if($i==0)
								$select.=$key;
							else
								$select.=" and ".$key;

							if($one_res==NULL)
								$select.=" is NULL";
							else
								$select.="='".addslashes($one_res)."'";

							$i++;
						}

						$arrayUserRestore[]=$select;
					}
				}

				if(count($arrayUserRestore)>0)
					foreach($arrayUserRestore as $one_user)
					{
						$sql="select user_id from `$tbl_user` where ".$one_user.";";
						$res=claro_sql_query_fetch_all($sql);

						//Ajoute dans cours_user
						if($res && count($res)>0)
						{
							$i=0;
							while($i<count($res_coursUser))
							{
								if($res_coursUser[$i]["user_id"]==$res[0]["user_id"])
									break;
								$i++;
							}

							if(i<count($res_coursUser))
							{
								$j=0;
								unset($values);
								foreach($res_coursUser[$i] as $val)
								{
									if($j!=0)
										$values.=",";

									if($val==NULL)
										$values.="NULL";
									else
										$values.="'".addslashes($val)."'";

									$j++;
								}

								$sql="insert into `$tbl_course_user` values (".$values.");";
								claro_sql_query($sql);
							}
						}
					}
				}
		}

		deldir($localArchivesRepository.$fileRestore);
	}





	/*-------------------------------------------------------------------
	To display the backup files to restore a course
	--------------------------------------------------------------------*/
	if(isset($_REQUEST["choiceRestore"]) || $display_restore)
	{
		$display_choice	=FALSE;
		$display_restore=TRUE;

		//For the sort
		$sc_order	=(isset($_REQUEST["order"])?$_REQUEST["order"]:"date");
		$sc_ascDesc	=(isset($_REQUEST["ascDesc"])?$_REQUEST["ascDesc"]:"DESC");

		//restore
		if(is_dir($localArchivesRepository))
		{
			$dirname = $localArchivesRepository;
			if($dirname[strlen($dirname)-1]!='/')
				$dirname.='/';

			//Open the repertoy
			$handle=opendir($dirname);

			$i=0;
			//For each reportery in the repertory
			while ($entries = readdir($handle))
			{
				//else it is a repertory of a langage
				if (is_file($dirname.$entries) && $entries != "." && $entries!="..")
				{
					$cours_restore[$i]["file"]=$entries;
					$array=explode("_",$entries);

					$cours_restore[$i]["name"]=$array[count($array)-4];
					$j=strpos($array[count($array)-1],".",0);

					if($j)
						$day=substr($array[count($array)-1],0,$j);
					else
						$day=$array[count($array)-1];

					$cours_restore[$i]["date"]=date("Y-m-d", mktime (0,0,0,$array[count($array)-2],$day,$array[count($array)-3]));

					$i++;
				}
			}
			closedir($handle);

			//Sort the table
			if(count($cours_restore)>0)
			{
				foreach($cours_restore as $key => $one_restore)
					$array_restore[$key]=$one_restore[$sc_order];

				if(!strcmp($sc_ascDesc,"ASC"))
					asort($array_restore);
				else
					arsort($array_restore);
			}
		}
	}
}




/*-------------------------------------------------------------------
  Create the Interbredcrumps
--------------------------------------------------------------------*/
$interbredcrump[]	= array ("url"=>$rootAdminWeb, "name"=> $langAdministrationTools);
$interbredcrump[]	= array ("url"=>"index.php", "name"=> $langManage);
$noQUERY_STRING 	= TRUE;

if($display_restore || $display_askWithUser)
	$interbredcrump[]	= array ("url"=>$PHP_SELF."?choiceRestore", "name"=> $lang_SearchCourse_RestoreCourse);
else
	$interbredcrump[]	= array ("url"=>$PHP_SELF."?choiceSearch", "name"=> $lang_SearchCourse_SearchCourse);


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
  Display the choice of operation for a course(search or restore)
--------------------------------------------------------------------*/
if($display_choice)
{
	echo "<br>";
?>	<a href="<?php echo $PHP_SELF."?choiceSearch"; ?>"> <?php echo $lang_SearchCourse_ChoiceSearch; ?> </a>
	<br>
	<a href="<?php echo $PHP_SELF."?choiceRestore"; ?>"> <?php echo $lang_SearchCourse_ChoiceRestore; ?> </a>
<?php
}




/*-------------------------------------------------------------------
  Display the form to search a course
--------------------------------------------------------------------*/
if($display_form)
{
?>
<form action="<?php echo $PHP_SELF ?>" >
	<table border="0">
	<tr>
		<td align="RIGHT"> <label for="sc_sysCode"> <?php echo $lang_SearchCourse_sysCode." : "; ?> </label >
		</td>
		<td> <input type="text" name="sc_sysCode" value="" maxlength="40" size="20">
		</td>
	</tr>
	<tr>
		<td align="RIGHT"> <label for="sc_intitule">  <?php echo $lang_SearchCourse_intitule." : "; 			?> </label>
		</td>
		<td> <input type="text" name="sc_intitule" value="" maxlength="250" size="20">
		</td>
	</tr>
	<tr>
		<td align="RIGHT"> <label for="sc_langage">  <?php echo $lang_SearchCourse_langage." : "; 			?> </label>
		</td>
		<td> <select name="sc_langage">
			 	<option value=""></option>
				<?php
					echo createSelectBoxLangage();
				?>
				</select>
			</td>
	</tr>
	<tr>
		<td align="RIGHT"> <label for="sc_faculte">  <?php echo $lang_SearchCourse_faculte." : "; 			?> </label>
		</td>
		<td> <select name="sc_faculte">
			<option value="" ></option>
		<?php
		//Display each category in the select
		buildSelectFaculty($arrayFaculty,NULL,"","");
		?>
		</select>
		</td>
	</tr>
	<tr>
		<td align="RIGHT"> <label for="sc_titulaire">  <?php echo $lang_SearchCourse_titulaire." : "; 			?> </label>
		</td>
		<td> <input type="text" name="sc_titulaire" value="" maxlength="200" size="20">
		</td>
	</tr>
	<tr>

		<td align="RIGHT"> <label for="sc_fakeCode">  <?php echo $lang_SearchCourse_fakeCode." : "; 			?> </label>
		</td>
		<td> <input type="text" name="sc_fakeCode" value="" maxlength="40" size="20">
		</td>
	</tr>
		<tr>
		<td align="RIGHT">  <?php echo $lang_SearchCourse_dateCreate." : "; 			?>
		</td>
		<td>
		<!-- create select boxes whith the days, the mounds and the years -->
		<select name="sc_dateDay">
			<option value=""></option>
			<?php 	for($i=1;$i<=31;$i++)
						echo "<option value=\"".$i."\"> $i </option><br>";
			?>
		</select>

		<select name="sc_dateMound">
			<option value=""></option>
			<?php
			$array=array($lang_SearchCourse_january,$lang_SearchCourse_february,$lang_SearchCourse_march,$lang_SearchCourse_avril,
			$lang_SearchCourse_mei,$lang_SearchCourse_juni,$lang_SearchCourse_jullie,$lang_SearchCourse_augustus,
			$lang_SearchCourse_september,$lang_SearchCourse_october,$lang_SearchCourse_november,$lang_SearchCourse_december);

			for($i=0;$i<12;$i++)
			{
				$val=$i+1;
				echo "<option value=\"".$val."\">".$array[$i]."</option><br>";
			}
			?>
		</select>

		<select name="sc_dateYear">
			<option value=""></option>
			<?php 	for($i=1990;$i<=2100;$i++)
						echo "<option value=\"".$i."\">".$i."</option><br>";
			?>
			</select>
		</td>
	</tr>
	<tr><td><br></td></tr>
	<tr>
		<td align="RIGHT">
			<input type="submit" name="searchCourse" value="<?php echo $lang_SearchCourse_buttonSearch; ?>" >
		</td>
	</table>
</form>

<?php
}




/*-------------------------------------------------------------------
  Display the restore courses
--------------------------------------------------------------------*/
if($display_restore)
{
?>
	<b><?php echo $lang_SearchCourse_titleRestore ?></b>

	<br><br>
	<?php
		if(count($array_restore)>0)
		{
	?>
		<form action="<?php echo $PHP_SELF ?>">
		<table border="0" class="claroTable">
		<thead>
		<th scope=col></th>

		<th scope=col><a href="<?php $ascD=(($sc_order=="date") 			&& ($sc_ascDesc=="DESC")?"ASC":"DESC");
			echo $PHP_SELF."?choiceRestore&order=date&ascDesc=".$ascD;		?>" > <small> <?php echo $lang_SearchCourse_date 	?>
		</small></a></th>

		<th scope=col><a href="<?php $ascD=(($sc_order=="name") 		&& ($sc_ascDesc=="ASC")?"DESC":"ASC");
			echo $PHP_SELF."?choiceRestore&order=name&ascDesc=".$ascD; 	?>" > <small><?php echo $lang_SearchCourse_nameCours	?>
		</small></a></th>

		</thead>
		<tbody>
		<?php
		foreach($array_restore as $key=>$one_restore)
		{
			list($year,$month,$day)=explode("-",$cours_restore[$key]["date"]);
		?>
			<tr>
				<td align="CENTER"><input type="radio" name="fileRestore" value="<?php echo $cours_restore[$key]["file"] ?>">
				</td>
				<td align="CENTER" width="150"><small><?php echo $day."-".$month."-".$year; ?></small>
				</td>
				<td align="CENTER"><small><?php echo $cours_restore[$key]["name"]; ?></small>
				</td>
				<td align="CENTER" width="150"><small><a href="<?php echo $PHP_SELF."?searchCourse&sc_sysCode=".$cours_restore[$key]["name"]; ?>">
				 <?php echo $lang_SearchCourse_SeeInfoCourse; ?> </a></small></td></td>
			</tr>
		<?php
		}
		?>

		</table>
		<br>
		&nbsp;&nbsp;&nbsp;<input type="submit" name="askWithUser" value="<?php echo $lang_SearchCourse_buttonRestore; ?>" >

	</form>
	<?php
		}
		else
			echo $lang_SearchCourse_NoBackup;
}


/*-------------------------------------------------------------------
 Display the list of courses
--------------------------------------------------------------------*/
if($display_list)
{
	?>
	<hr>
	<b><u> <?php echo $lang_SearchCourse_ParameterSearch." : "; ?> </u></b>
	<br><br>
	<?php if(!empty($sc_sysCode)){		echo "     ".$lang_SearchCourse_sysCode." : ".$sc_sysCode; ?>
	<br> <?php } ?>
	<?php if(!empty($sc_intitule)){		echo "     ".$lang_SearchCourse_intitule." : ".$sc_intitule; ?>
	<br> <?php } ?>
	<?php if(!empty($sc_langage)){		echo "     ".$lang_SearchCourse_langage." : ".$sc_langage; ?>
	<br> <?php } ?>
	<?php if(!empty($sc_faculte)){ 		echo "     ".$lang_SearchCourse_faculte." : ".$sc_faculte; ?>
	<br> <?php } ?>
	<?php if(!empty($sc_titulaire)){ 	echo "     ".$lang_SearchCourse_titulaire." : ".$sc_titulaire; ?>
	<br> <?php } ?>
	<?php if(!empty($sc_fakeCode)){ 	echo "     ".$lang_SearchCourse_fakeCode." : ".$sc_fakeCode; ?>
	<br> <?php } ?>
	<?php 	if(!empty($sc_date))
			{
				list($year,$month,$day)=explode("-",$sc_date);
				echo "     ".$lang_SearchCourse_creationDate." : ".$day."-".$month."-".$year; ?>
	<br> <?php } ?>
	<hr> <br>

	<!-- display the user's list -->
	<table width="100%" class="claroTable">
	<thead>
		<tr>

		<!-- Create the URL to display the list course in a other order -->
		<?php

		$url=$PHP_SELF."?sc_sysCode=".rawurlencode($sc_sysCode)."&sc_intitule=".rawurlencode($sc_intitule)."&sc_langage=".
		rawurlencode($sc_langage)."&sc_faculte=".rawurlencode($sc_faculte)."&sc_titulaire=".rawurlencode($sc_titulaire).
		"&sc_fakeCode=".rawurlencode($sc_fakeCode)."&sc_date=".rawurlencode($sc_date)."&searchCourse";

		?>

		<!-- $su_ascDesc is for display the list in the opposite sense -->
		<th scope=col><a href="<?php $ascD=(($sc_order=="code") 			&& ($sc_ascDesc=="ASC")?"DESC":"ASC");
			echo $url."&order=code&ascDesc=".$ascD;		?>" > <small> <?php echo $lang_SearchCourse_sysCode 		?> </small></a></th>
		<th scope=col><a href="<?php $ascD=(($sc_order=="intitule") 		&& ($sc_ascDesc=="ASC")?"DESC":"ASC");
			echo $url."&order=intitule&ascDesc=".$ascD; 	?>" > <small><?php echo $lang_SearchCourse_intitule		?> </small></a></th>
		<th scope=col><a href="<?php $ascD=(($sc_order=="languageCourse") 	&& ($sc_ascDesc=="ASC")?"DESC":"ASC");
			echo $url."&order=languageCourse&ascDesc=".$ascD; 	?>" ><small> <?php echo $lang_SearchCourse_langage 	?> </small></a></th>
		<th scope=col><a href="<?php $ascD=(($sc_order=="faculte") 			&& ($sc_ascDesc=="ASC")?"DESC":"ASC");
			echo $url."&order=faculte&ascDesc=".$ascD; 	?>" > <small><?php echo $lang_SearchCourse_faculte		?> </small></a></th>
		<th scope=col><a href="<?php $ascD=(($sc_order=="titulaires") 		&& ($sc_ascDesc=="ASC")?"DESC":"ASC");
			echo $url."&order=titulaires&ascDesc=".$ascD; 	?>" > <small><?php echo $lang_SearchCourse_titulaire?> </small></a></th>
		<th scope=col><a href="<?php $ascD=(($sc_order=="email") 		&& ($sc_ascDesc=="ASC")?"DESC":"ASC");
			echo $url."&order=email&ascDesc=".$ascD; 	?>" > <small><?php echo $lang_SearchCourse_Email;?> </small></a></th>
		<th scope=col><a href="<?php $ascD=(($sc_order=="fake_code") 		&& ($sc_ascDesc=="ASC")?"DESC":"ASC");
			echo $url."&order=fake_code&ascDesc=".$ascD; 		?>" ><small> <?php echo $lang_SearchCourse_fakeCode ?> </small> </a></th>
		<th scope=col><a href="<?php $ascD=(($sc_order=="creationDate") 	&& ($sc_ascDesc=="ASC")?"DESC":"ASC");
			echo $url."&order=creationDate&ascDesc=".$ascD; 		?>" ><small> <?php echo $lang_SearchCourse_dateCreate?> </small>
			</a></th>
		<th scope=col> <small><?php echo $lang_SearchCourse_Edit; ?> </small> </th>
		<th scope=col> <small><?php echo $lang_SearchCourse_Delete; ?> </small> </th>
		<th scope=col> <small><?php echo $lang_SearchCourse_Backup; ?> </small></th>


		</tr>
	</thead>
	<tbody>

	<?php
		//for each course display her informations
		foreach ($arrayCourse as $one_course)
		{
			list($date,$hour)=explode(" ",$one_course["creationDate"]);
			list($year,$month,$day)=explode("-",$date);
	?>
		<tr>
		<td align="CENTER">	<a  class="topBanner" href="<?php echo $PHP_SELF."?display_course=1&sysCode=".$one_course["code"]; ?>" >
		<small><?php echo $one_course["code"];?> </small> </a>
		</td>

		<td align="CENTER"> <small>	<?php echo $one_course["intitule"];?> </small>
		</td>
		<td align="CENTER">	<small> <?php echo $one_course["languageCourse"];?> </small>
		</td>
		<td align="CENTER">  <small> <?php echo $one_course["faculte"];?> </small>
		</td>
		<td align="CENTER"> <small>	<?php echo $one_course["titulaires"];?> </small>
		</td>
		<td align="CENTER"> <small> <a href="<?php echo "mailto:".$one_course["email"]; ?>"> <?php echo $one_course["email"];?> </a></small>
		</td>
		<td align="CENTER"> <small> <?php echo $one_course["fake_code"];?> </small>
		</td>
		<td align="CENTER"> <small> <?php echo $day."-".$month."-".$year." ".$hour;?> </small>

		<td align="center"> <a href="<?php echo "../../course_info/infocours.php?cidReq=".$one_course["code"]; ?>">
		<img src="<?php echo $clarolineRepositoryWeb ?>/img/edit.gif" border="0" alt="<?php echo $lang_SearchCourse_imgEdit; ?>"> </a>
		</td>

		<td align="center">  <a href="<?php echo "../../course_info/delete_course.php?cidReq=".$one_course["code"]."&search_course"; ?>" >
		<img src="<?php echo $clarolineRepositoryWeb ?>/img/delete.gif" border="0" alt="<?php echo $lang_SearchCourse_imgDelete; ?>"> </a>
		</td>

		</td>
		<td align="center"> <a href="<?php echo $PHP_SELF."?backup&display_course=1&sysCode=".$one_course["code"]; ?>">
		<img src="<?php echo $clarolineRepositoryWeb ?>/img/enregistrer.gif" border="0" alt="<?php echo $lang_SearchCourse_imgSave; ?>"> </a>
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
Display information of a course subscribe
--------------------------------------------------------------------*/
if($display_course)
{
?>
    <table class="claroTable">
    <thead>
    <th scope=col> </th>
    <th scope=col> </th>
    <th scope=col> <?php echo "&nbsp;&nbsp;&nbsp;".$lang_SearchCourse_Edit?> </th>
    <th scope=col> <?php echo "&nbsp;&nbsp;&nbsp;".$lang_SearchCourse_Delete?></th>
    <th scope=col> <?php echo "&nbsp;&nbsp;&nbsp;".$lang_SearchCourse_Backup?></th>
    </tr>
      </thead>
          <tbody>
        <tr>
            <td width="250"> <?php echo $lang_SearchCourse_sysCode." : "; ?> </td>
            <td> <font color="#6666FF"> <?php echo $arrayCourse["code"];?> </font></td>

            <td align="center">  <a href="<?php echo 
            "../../course_info/infocours.php?cidReq=".$arrayCourse["code"];
            ?>">
            <img src="<?php echo $clarolineRepositoryWeb ?>/img/edit.gif" border="0" alt="<?php echo $lang_SearchCourse_imgEdit; ?>"> </a>
            </td>
            <td align="center">  <a href="<?php echo "../../course_info/delete_course.php?cidReq=".$arrayCourse["code"]."&search_course";
             ?>" >
            <img src="<?php echo $clarolineRepositoryWeb ?>/img/delete.gif" border="0" alt="<?php echo $lang_SearchCourse_imgDelete; ?>"> </a>
            </td>
            <td align="center"> <a href="<?php echo $PHP_SELF."?backup&display_course=1&sysCode=".$arrayCourse["code"]; ?>">
            <img src="<?php echo $clarolineRepositoryWeb ?>/img/enregistrer.gif" border="0" alt="<?php echo $lang_SearchCourse_imgSave; ?>"> </a>
            </td>
        </tr>
        <tr>
            <td> <?php echo $lang_SearchCourse_intitule." : "; ?> </td>
            <td> <font color="#6666FF"> <?php echo $arrayCourse["intitule"];?> </font></td>
        </tr>
        <tr>
            <td> <?php echo $lang_SearchCourse_langage." : "; ?> </td>
            <td> <font color="#6666FF"> <?php echo $arrayCourse["languageCourse"];?> </font></td>
        </tr>
        <tr>
            <td> <?php echo $lang_SearchCourse_faculte." : "; ?> </td>
            <td> <font color="#6666FF"> <?php echo $arrayCourse["faculte"];?> </font></td>
        </tr>
        <tr>
            <td> <?php echo $lang_SearchCourse_titulaire." : "; ?> </td>
            <td> <font color="#6666FF"> <?php echo $arrayCourse["titulaires"];?> </font></td>
        </tr>
		<tr>
			<td> <?php echo $lang_SearchCourse_Email." : "; ?> </td>
			<td> <a href="<?php echo "mailto:".$arrayCourse["email"];?>"> <?php echo $arrayCourse["email"]; ?></a></td>
		</tr>
        <tr>
            <td> <?php echo $lang_SearchCourse_fakeCode." : "; ?> </td>
            <td> <font color="#6666FF"> <?php echo $arrayCourse["fake_code"];?> </font></td>
        </tr>
        <tr>
            <td> <?php echo $lang_SearchCourse_creationDate." : "; ?> </td>
            <td> <font color="#6666FF"> <?php echo $arrayCourse["creationDate"];?> </font></td>
        </tr>
    </tbody>
    </table>

    <br>
    <a href="<?php echo $rootWeb.$arrayCourse["directory"] ?> "> <?php echo $lang_SearchCourse_link; ?> </a>
    <br>

    <?php
}




/*-------------------------------------------------------------------
Display the groups of the course
--------------------------------------------------------------------*/
if($display_group)
{
    echo "<hr><br><u>".$lang_SearchCourse_groupTitle."</u><br><br>"; ?>

    <table width="100%" class="claroTable">
        <thead>
        <th scope=col><a href="<?php $ascD=(($sc_orderG=="id") && ($sc_ascDescG=="ASC")?"DESC":"ASC");
         echo $PHP_SELF."?display_course&sysCode=".$arrayCourse["code"]."&orderG=id&ascDescG=".$ascD."#id_group"; ?>"
         name="id_group">
         <small><?php echo $lang_SearchCourse_idGroup ?> </small></a></th>

        <th scope=col><a href="<?php $ascD=(($sc_orderG=="name") && ($sc_ascDescG=="ASC")?"DESC":"ASC");
         echo $PHP_SELF."?display_course&sysCode=".$arrayCourse["code"]."&orderG=name&ascDescG=".$ascD."#name"; ?>"
         name="name">
         <small><?php echo $lang_SearchCourse_groupName ?> </small></a></th>

        <th scope=col><a href="<?php $ascD=(($sc_orderG=="tutor") && ($sc_ascDescG=="ASC")?"DESC":"ASC");
         echo $PHP_SELF."?display_course&sysCode=".$arrayCourse["code"]."&orderG=tutor&ascDescG=".$ascD."#tutor"; ?>"
         name="tutor">
         <small><?php echo $lang_SearchCourse_groupTurtor ?> </small></a></th>

        <th scope=col> <a href="<?php $ascD=(($sc_orderG=="forumId") && ($sc_ascDescG=="ASC")?"DESC":"ASC");
         echo $PHP_SELF."?display_course&sysCode=".$arrayCourse["code"]."&orderG=forumId&ascDescG=".$ascD."#forum"; ?>"
         name="forum">
         <small><?php echo $lang_SearchCourse_groupForum ?> </small></a></th>

        <th scope=col> <small><?php echo $lang_SearchCourse_groupMaxStudent ?> </small></th>

        <th scope=col> <a href="<?php $ascD=(($sc_orderG=="secretDirectory") && ($sc_ascDescG=="ASC")?"DESC":"ASC");
         echo $PHP_SELF."?display_course&sysCode=".$arrayCourse["code"]."&orderG=secretDirectory&ascDescG=".$ascD."#direct"; ?>"
         name="direct" >
         <small><?php echo $lang_SearchCourse_groupSecretDir?> </small></th>

        <th scope=col> <small><?php echo $lang_SearchCourse_groupDescription     ?> </small></th>
    <?php     if ($display_user)
            {
    ?>
                <th scope=col> <small><?php echo $lang_SearchCourse_GourpEmail            ?> </small></th>
    <?php     } ?>
        <th scope=col> <small><?php echo $lang_SearchCourse_Edit                 ?> </small></th>
        <th scope=col> <small><?php echo $lang_SearchCourse_Delete                 ?> </small></th>
        </tr>
        </thead>
            <tbody>
            <?php
            foreach($array_group as $one_group)
            {
            ?>
                <tr>
                <td align="CENTER">  <small><?php echo $one_group["id"];?> </small></td>
                <td align="CENTER">  <small><?php echo $one_group["name"];?> </small></td>
                <td align="CENTER">  <small><?php echo $one_group["tutor"];?> </small></td>
                <td align="CENTER">  <small><?php echo $one_group["forumId"];?> </small></td>
                <td align="CENTER">  <small><?php echo $one_group["maxStudent"];?> </small></td>
                <td align="CENTER">  <small><?php echo $one_group["secretDirectory"];?> </small></td>
                <td align="CENTER">  <small><?php echo $one_group["description"];?> </small></td>
        <?php     if ($display_user)
                {
        ?>
                    <td align="CENTER">  <a href="mailto:
                        <?php foreach($array_user as $user)
                                if($user["team"]==$one_group["id"])
                                    echo $user["email"].",";

                        ?>">
                        <img src="<?php echo $clarolineRepositoryWeb ?>/img/email.gif" border="0" alt="<?php echo $lang_SearchCourse_imgMail; ?>"> </a>
                    </td>
        <?php    } ?>

                <td align="CENTER">  <a href="<?php
                echo $PHP_SELF."?editGroup&sysCode=".$arrayCourse["code"]."&idGroup=".$one_group["id"];?>">
                    <img src="<?php echo $clarolineRepositoryWeb ?>/img/edit.gif" border="0" alt="<?php echo $lang_SearchCourse_imgEdit; ?>"> </a>
                </td>
                <td align="CENTER">  <a href="<?php echo 
                    $PHP_SELF."?deleteGroup=1&sysCode=".$arrayCourse["code"]."&idGroup=".$one_group["id"]; ?>"
                    onclick="javascript:if(!confirm('<?php echo 
                    addslashes(htmlentities($lang_SearchCourse_ConfirmDeleteGroup.$one_group["name"]))?>'))    return false;" >
                    <img src="<?php echo $clarolineRepositoryWeb ?>/img/delete.gif" border="0" alt="<?php echo $lang_SearchCourse_imgDelete; ?>"> </a>
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
Display the students of the course
--------------------------------------------------------------------*/
if($display_user)
{
    $mailto="mailto:";
    $tab=NULL;
    foreach($array_user as $user)
    {
        $exist=FALSE;
        if($tab)
            foreach($tab as $tuser)
                if($tuser==$user["user"])
                    $exist=TRUE;

        if(!$exist)
        {
            $mailto.=$user["email"].",";
            $tab[]=$user["user"];
        }
    }

    echo "<hr><br><u>".count($tab)." ".$lang_SearchCourse_UserTitle."</u>&nbsp;&nbsp;";
    ?> <a href="<?php echo $mailto; ?>" >
        <img src="<?php echo $clarolineRepositoryWeb ?>/img/email.gif" border="0" alt="<?php echo $lang_SearchCourse_imgMail; ?>">
        </a>
        <br><br>

<form action="<?php echo $PHP_SELF."?sysCode=".$arrayCourse["code"] ?>">

    <table width="100%" class="claroTable">
        <thead>
        <th scope=col> <small><?php echo $lang_SearchCourse_DeleteUserGroup ?></small> </th>

        <th scope=col><a href="<?php $ascD=(($sc_order=="team") && ($sc_ascDesc=="ASC")?"DESC":"ASC");
            echo $PHP_SELF."?display_course&sysCode=".$arrayCourse["code"]."&order=team&ascDesc=".$ascD."#namet"; ?>"
            name="namet">
            <small><?php echo $lang_SearchCourse_userGroup ?></small></th>

        <th scope=col><a href="<?php $ascD=(($sc_order=="user") && ($sc_ascDesc=="ASC")?"DESC":"ASC");
            echo $PHP_SELF."?display_course&sysCode=".$arrayCourse["code"]."&order=user&ascDesc=".$ascD."#user"; ?>"
            name="user">
            <small><?php echo $lang_SearchCourse_userId ?></small> </a></th>

        <th scope=col><a href="<?php $ascD=(($sc_order=="lastname") && ($sc_ascDesc=="ASC")?"DESC":"ASC");
            echo $PHP_SELF."?display_course&sysCode=".$arrayCourse["code"]."&order=lastname&ascDesc=".$ascD."#lastn"; ?>"
            name="lastn">
            <small><?php echo $lang_SearchCourse_userLastname ?></small></th>

        <th scope=col><a href="<?php $ascD=(($sc_order=="firstname") && ($sc_ascDesc=="ASC")?"DESC":"ASC");
            echo $PHP_SELF."?display_course&sysCode=".$arrayCourse["code"]."&order=firstname&ascDesc=".$ascD."#firstn"; ?>"
            name="firstn">
            <small><?php echo $lang_SearchCourse_userFirstname ?></small></th>

        <th scope=col><a href="<?php $ascD=(($sc_order=="email") && ($sc_ascDesc=="ASC")?"DESC":"ASC");
            echo $PHP_SELF."?display_course&sysCode=".$arrayCourse["code"]."&order=email&ascDesc=".$ascD."#emailn"; ?>"
            name="emailn">
            <small><?php echo $lang_SearchCourse_userEmail?></small></th>

        </tr>
        </thead>
            <tbody>
            <?php
            foreach($array_param as $keys => $param)
            {
            ?>
                <tr>
                <td align="CENTER"><input type="checkbox"
                name="check[]" value="<?php echo $array_user[$keys]["user"]; ?>">
                </td>
                <td align="CENTER">  <small><?php echo $array_user[$keys]["team"];?></small>
                </td>
                <td align="CENTER">  <a href="<?php echo "search_user.php?search_user&user_id=".$array_user[$keys]["user"]?>">
                <small><?php echo $array_user[$keys]["user"];?></small> </a>
                </td>
                <td align="CENTER">  <small><?php echo $array_user[$keys]["lastname"];?></small>
                </td>
                <td align="CENTER">  <small><?php echo $array_user[$keys]["firstname"];?></small>
                </td>
                <td align="CENTER"> <a href="mailto:<?php echo $array_user[$keys]["email"]; ?>">
                    <small><?php echo $array_user[$keys]["email"];?></small> </a>
                </td>
                </tr>
            <?php
            }
            ?>

            <tr>
            <td> <input type="hidden" name="sysCode" value="<?php echo $arrayCourse["code"] ?>"> </td>
            </tr>
            <tr>
            <td align="CENTER">
            <input type="submit" name="display_course" value="Désinscrire">
            </td>
            </tbody>
    </table>

<?php
}




/*-------------------------------------------------------------------
Display form to edit a group
--------------------------------------------------------------------*/
if($display_editGroup)
{
?>
<form action="<?php echo $PHP_SELF ?>">
    <table>
        <tr>
            <td align="RIGHT"> <?php echo $lang_SearchCourse_groupName." : "; ?> </td>
            <td> <input type="text" name="name" value="<?php echo $array_group["name"];?>"> </td>
        </tr>
        <tr>
            <td align="RIGHT"> <?php echo $lang_SearchCourse_groupTurtor." : "; ?> </td>
            <td> <input type="text" name="tutor" value="<?php echo $array_group["tutor"];?>"></td>
        </tr>
        <tr>
            <td align="RIGHT"> <?php echo $lang_SearchCourse_groupForum." : "; ?> </td>
            <td><input type="text" name="forumId" size="5" maxlength="5" value="<?php echo $array_group["forumId"];?>"></td>
        </tr>
        <tr>
            <td align="RIGHT"> <?php echo $lang_SearchCourse_groupMaxStudent." : "; ?> </td>
            <td> <input type="text" name="maxStudent" size="5" maxlength="5" value="<?php echo $array_group["maxStudent"];?>"></td>
        </tr>
        <tr>
            <td align="RIGHT" valign="TOP"> <?php echo $lang_SearchCourse_groupDescription." : "; ?> </td>
            <td> <textarea name="description" cols="25" rows="5"><?php echo $array_group["description"];?></textarea> </td>
        </tr>
        <tr>
            <td> <br> <input type="hidden" name="sysCode" value="<?php echo $_REQUEST["sysCode"] ?>">
                      <input type="hidden" name="idGroup" value="<?php echo $_REQUEST["idGroup"] ?>">
            </td>
        </tr>
        <tr>
            <td align="RIGHT">
             <input type="reset" name="reset" value="<?php echo $lang_SearchCourse_buttonReset; ?>" >
            </td>
            <td>
                 <input type="submit" name="changeGroup" value="<?php echo $lang_SearchCourse_buttonChange;;?>" >
            </td>
        </tr>
    </table>
</form>

<?php
}




/*-------------------------------------------------------------------
Ask if restore the course with or without the users
--------------------------------------------------------------------*/
if($display_askWithUser)
{
    echo $lang_SearchCourse_AskRestoreUser;
    echo "<br><br>";
?>
    &nbsp;
    <a href="<?php echo $PHP_SELF."?choiceRestore&restore&fileRestore=".$fileRestore."&restoreUser=1"; ?>">
        <?php echo $lang_SearchCourse_Yes; ?></a>
    &nbsp;|&nbsp;
    <a href="<?php echo $PHP_SELF."?choiceRestore&restore&fileRestore=".$fileRestore."&restoreUser=0"; ?>">
        <?php echo $lang_SearchCourse_No; ?></a>
<?php
}



include($includePath."/claro_init_footer.inc.php");


?>
