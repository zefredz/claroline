<?php # $Id$
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.4.* $Revision$                             |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2003 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      |   This program is free software; you can redistribute it and/or      |
      |   modify it under the terms of the GNU General Public License        |
      |   as published by the Free Software Foundation; either version 2     |
      |   of the License, or (at your option) any later version.             |
      +----------------------------------------------------------------------+
      | Authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
      |          Hugues Peeters    <peeters@ipm.ucl.ac.be>                   |
      |          Christophe Gesché <gesche@ipm.ucl.ac.be>                    |
      +----------------------------------------------------------------------+
 */

$langFile = "admin.technical";
include('../../inc/claro_init_global.inc.php');
$nameTools = $langCheckDatabase;
$interbredcrump[]= array ("url"=>"index.php", "name"=> $langAdmin);
@include($rootAdminSys."/checkIfHtAccessIsPresent.php");
$is_allowedToCheck 	= $is_platformAdmin || $PHP_AUTH_USER;

if ($is_allowedToCheck)
{

include($includePath."/claro_init_header.inc.php");
claro_disp_tool_title(
	array(
	'mainTitle'=>$nameTools,
	'subTitle'=>$langUpgrade." - ".$PHP_AUTH_USER." - ".$siteName." - ".$clarolineVersion." - ".$dateNow
	)
	);
claro_disp_msg_arr($controlMsg); ?>
<b>
<div class="warn">
experimental
</div></b><?
/************
First Step  trying  to use Mysql
 **********/

$db = @ mysql_connect("$dbHost", "$dbLogin", "$dbPass");
if (mysql_errno()>0) // problem with server
{
	$no = mysql_errno();
    $msg = mysql_error();
	echo "
<HR>
[".$no."] - ".$msg."
<HR>";
// HERE we  must add a  Test of error code.
// If connection doesn't work -> Message

   echo "
The Server Mysql  doesn't run.<br>
please  chech this values
host : ".$dbHost."<br>
user : ".$dbLogin."<br>
password  : ".$dbPass."<br>";

}
else
{

/************
Mysql server run  show  some  info
 **********/

echo  "
<hr>
<B>".$langServerStatus."</B>
<BR>".$langClient." : ".mysql_get_client_info()."
- ".$langServer." :".mysql_get_server_info()."
- host :".mysql_get_host_info()."
- proto :".mysql_get_proto_info()."
<BR>".$dbHost." ".$langRun."
-  user : ".$dbLogin;
foreach (gethostbynamel($dbHost) as $key => $ip)
{
	echo "- ip $key : $ip - ".	gethostbyaddr($ip);
}
include($rootAdminSys."/barre.inc.php");
echo
" <HR> ";


/************
 if we have a database name to check,  we check the it
 **********/
    if ($dbToCheck!=""&&isset($dbToCheck))
    {
		$sqlInfoCour =
"SELECT c.faculte,
		c.intitule,
		c.visible, c.fake_code, c.titulaires, c.languageCourse
	FROM `$mainDbName`.`cours` c
	WHERE c.code='$dbToCheck'";
		$result = mysql_query($sqlInfoCour);
	    $myrow = mysql_fetch_array($result);
		if(is_array($myrow))
		{
			echo $langCourses.
			" [".$myrow["fake_code"]."] ".$myrow["intitule"].
			"<br>facu 	: ".$myrow["faculte"].
	    	"<br>visible : ".$myrow["visible"].
			"<br>".$langtitulary." : ".$myrow["titulaires"].
			"<br>".$langLanguage." : ".$myrow["languageCourse"];
			echo "<HR>";
		}
		mysql_select_db("$dbToCheck",$db);
		if (mysql_errno()>0) // problem with database
		{
			$no = mysql_errno();
		    $msg = mysql_error();
			echo "<HR>[".$no."] - ".$msg."<HR>";
			echo "
			<br>
			<DIV class=\"source\">
				$dbToCheck = \"".$dbToCheck."\";<br>
			</DIV>
			and existing base on $dbHost are";
//		error_reporting(E_ALL);
    	}
    	else
        {
            echo  "
			<BR>
			".$langDataBase."
			<B>
				".$dbToCheck."
			</B>
			".$langBaseFound."
			<br>
			<a href=\"../maintenance/updateDataBase.php?currentCourseID=".$dbToCheck."\">Upgrade</a><br>
				";

			// main Database exist
		  	// check of  needed table  in main database.
            $list_table_in_CourseBase["access"]["needed"]="1";
            $list_table_in_CourseBase["agenda"]["needed"]="1";
            $list_table_in_CourseBase["accueil"]["needed"]="1";
            $list_table_in_CourseBase["banlist"]["needed"]="1";
            $list_table_in_CourseBase["catagories"]["needed"]="1";
            $list_table_in_CourseBase["config"]["needed"]="1";
            $list_table_in_CourseBase["disallow"]["needed"]="1";
            $list_table_in_CourseBase["document"]["needed"]="1";
            $list_table_in_CourseBase["exercice_question"]["needed"]="1";
            $list_table_in_CourseBase["exercices"]["needed"]="1";
            $list_table_in_CourseBase["forum_access"]["needed"]="1";
            $list_table_in_CourseBase["forum_mods"]["needed"]="1";
            $list_table_in_CourseBase["forums"]["needed"]="1";
            $list_table_in_CourseBase["group_properties"]["needed"]="1";
            $list_table_in_CourseBase["headermetafooter"]["needed"]="1";
            $list_table_in_CourseBase["introduction"]["needed"]="1";
            $list_table_in_CourseBase["liens"]["needed"]="1";
   //         $list_table_in_CourseBase["liste_domaines"]["needed"]="1";
            $list_table_in_CourseBase["pages"]["needed"]="1";
            $list_table_in_CourseBase["posts"]["needed"]="1";
            $list_table_in_CourseBase["posts_text"]["needed"]="1";
            $list_table_in_CourseBase["priv_msgs"]["needed"]="1";
            $list_table_in_CourseBase["questions"]["needed"]="1";
            $list_table_in_CourseBase["ranks"]["needed"]="1";
            $list_table_in_CourseBase["reponses"]["needed"]="1";
            $list_table_in_CourseBase["sessions"]["needed"]="1";
            //$list_table_in_CourseBase["stat_accueil"]["needed"]="1";
            $list_table_in_CourseBase["student_group"]["needed"]="1";
            $list_table_in_CourseBase["themes"]["needed"]="1";
            $list_table_in_CourseBase["topics"]["needed"]="1";
            $list_table_in_CourseBase["user_group"]["needed"]="1";
            $list_table_in_CourseBase["users"]["needed"]="1";
            //$list_table_in_CourseBase["video"]["needed"]="0";
            $list_table_in_CourseBase["whosonline"]["needed"]="1";
            $list_table_in_CourseBase["words"]["needed"]="1";
            $list_table_in_CourseBase["work"]["needed"]="1";
            $list_table_in_CourseBase["course_description"]["needed"]="1";
            $list_table_in_CourseBase["annonces"]["needed"]="1";
            $list_table_in_CourseBase["userinfo_content"]["needed"]="1";
            $list_table_in_CourseBase["userinfo_def"]["needed"]="1";
            $tableForTool["course_description"]="program";
            $tableForTool["access"]="forum";
            $tableForTool["agenda"]="agenda";
            $tableForTool["accueil"]="home";
            $tableForTool["banlist"]="forum";
            $tableForTool["catagories"]="forum";
            $tableForTool["config"]="forum";
            $tableForTool["disallow"]="forum";
            $tableForTool["document"]="document";
            $tableForTool["exercice_question"]="exercices";
            $tableForTool["exercices"]="exercices";
            $tableForTool["forum_access"]="forum";
            $tableForTool["forum_mods"]="forum";
            $tableForTool["forums"]="forum";
            $tableForTool["group_properties"]="team";
            $tableForTool["headermetafooter"]="forum";
            $tableForTool["introduction"]="home";
            $tableForTool["liens"]="liens";
            $tableForTool["liste_domaines"]="forum";
            $tableForTool["pages"]="forum";
            $tableForTool["posts"]="forum";
            $tableForTool["posts_text"]="forum";
            $tableForTool["priv_msgs"]="forum";
            $tableForTool["questions"]="exercices";
            $tableForTool["ranks"]="exercices";
            $tableForTool["reponses"]="exercices";
            $tableForTool["sessions"]="forum";
            //$tableForTool["stat_accueil"]="stat";
            $tableForTool["student_group"]="team";
            $tableForTool["themes"]="forum";
            $tableForTool["topics"]="forum";
            $tableForTool["user_group"]="team";
            $tableForTool["users"]="forum";
            //$tableForTool["video"]="document";
            $tableForTool["whosonline"]="forum";
            $tableForTool["words"]="forum";
            $tableForTool["work"]="work";
            $tableForTool["work_student"]="team";
			$tableForTool["annonces"]	="announcement1";
            $tableForTool["userinfo_content"]="User info";
            $tableForTool["userinfo_def"]="User info";


			$result = mysql_list_tables ($dbToCheck);
			$i = 0;
			while ($i < mysql_num_rows($result))
			{
				$tb_names[$i] = mysql_tablename ($result, $i);
				$list_table_in_CourseBase[$tb_names[$i]]["present"]="1";
				$i++;
			}
			reset($list_table_in_CourseBase);
			echo "
<Form action=\"createClaroTables.php\" method=\"post\" lang=\"$iso639_2_code\" >
			<TABLE border=\"1\" cellpadding=\"4\" cellspacing=\"0\">";
			$are_missing = false;
			while (list($nomTable,$needability) = each($list_table_in_CourseBase))
                        {
                            echo "
				<tr>
					<td>
						<a href=\"".$phpMyAdminWeb."tbl_properties.php?db=".$dbToCheck."&table=".$nomTable."\">".$nomTable."</a>
					</td>
					<td>";
					if (isset($needability["needed"])&&$needability["needed"])
						echo $langNeeded;
					else
						echo "
						!!
						<font color=\"#800000\">
							$langNotNeeded
						</font> !!";
					echo "
					</td>
					<td>
						".$tableForTool[$nomTable]."
					</td>
					<td>";
					if (isset($needability["archive"]) && $needability["archive"])
						echo $langArchive;
					else
						echo "
						!!
						<font color=\"#800000\">
							$langUsed
						</font> !!";
					echo "
					</td>
					<td>";
					if (isset($needability["present"])&&$needability["present"])
					{
						echo "
						<font color=\"#008000\">
							$langPresent
						</font>";
						echo "
					</td>
					<td>
					</td>
					<td ";
					}
					else
					{
						echo "
						<font size=\"+1\" color=\"#FF0000\">
							$langMissing
						</font>";
					    $are_missing = true;
						echo "
					</td>
					<td >
	<!--// remove when createdatabaseisready
						<input type=\"checkbox\" name=\"create[]\" value=\"$nomTable\" ";
						if ($needability["needed"]==1)
							echo " checked ";
						echo ">
	 remove when createdatabaseisready -->
					</td>
					<td ";
					}
					echo "
					</td>
					<td ";
					if (!(isset($needability["needed"])&&isset($needability["present"])))
						echo "bgcolor=\"#8080ff\"> <- ";
					else
						echo ">";
					echo "
					</TD>
				</tr>";
				}
			echo "
			</table>
	<!--// remove when createdatabaseisready
			<br>
			";
			if ($are_missing)
			{
				echo "
			$langCreateMissingNow
			<input type='submit'  name='yes'>
			<br>
			<br>";
			}
			echo "
	remove when createdatabaseisready -->
	</form>";
	}
    }
    else
    {
    	mysql_select_db("$mainDbName",$db);
		if (mysql_errno()>0) // problem with database
		{
			$no = mysql_errno();
		    $msg = mysql_error();
			echo "<HR>[".$no."] - ".$msg."<HR>";

			// HERE we  must add a  Test of error code.
			// If connection doesn't work -> Message

			echo "
		<br>
		$langPleaseCheckConfigForMainDataBaseName
	<em>".realpath($includePath."/config.inc.php").'</em> : <br>
	<DIV class="source">
		$mainDbName = "'.$mainDbName."\";<br>
	</DIV>
	and existing base on $dbHost are";
//		error_reporting(E_ALL);
			$db_list = mysql_list_dbs();
			$i = 0;
			$cnt = mysql_num_rows($db_list);
		    echo "
		<OL>";
			$main_base_exist =false;
			while ($i < $cnt)
			{
				$this_db_name = mysql_db_name($db_list, $i++);
				echo "
			<LI>
				".$this_db_name. "\n";
		        if ($this_db_name==$mainDbName)
				{
					echo "
				<font color=\"#FF0000\">&lt;- this  is the main base</font> ";
					$main_base_exist = true;
				}
			}
		    echo "
		</OL>";
			if (!$main_base_exist)
			{
				echo "
		<HR>
		You must create the database
		<strong>
			$mainDbName
		</strong>
		on the server
		<strong>
			$dbHost
		</strong>
		<br>
		In this  base  you must first run the script
		<br>
		<em>
			".realpath($clarolineRepositorySys."/sql/claroline.sql").'
		</em> with
		<a href="<?php echo $phpMyAdminWeb?>">phpMyAdmin</a>
		<br>
		or
		use this
		<a href="../maintenance/updateMainDataBase.php">Create database</a>
		<br>
		<br>
		OR
		<br>
		<br>
				choose one  of existing database and correct
		        <strong>$mainDbName</strong> in <br>
				<em>'.realpath("../include/config.php").'</em> : <br>
				';
			}
		} else
		{
			echo  "<B>".$mainDbName."</B> ".$langBaseFound;

			if ($needToBeUpgrade || 1)
			{
				echo  "
				<br>
				<a href=\"../maintenance/updateMainDataBase.php\"> ".$langUpgradeThisBase."</a><br>
				<br>
				";
			}

			// main Database exist
		  	// check of  needed table  in main database.

			$list_table_in_mainBase["admin"]["needed"]="1";
			$list_table_in_mainBase["cours"]["needed"]="1";
			//$list_table_in_mainBase["cours_faculte"]["needed"]="0";
			$list_table_in_mainBase["cours_user"]["needed"]="1";
			$list_table_in_mainBase["faculte"]["needed"]="1";
//			$list_table_in_mainBase["loginout"]["needed"]="1";
			$list_table_in_mainBase["todo"]["needed"]="1";
			$list_table_in_mainBase["user"]["needed"]="1";
//			$list_table_in_mainBase["tools"]["needed"]="1";
			$list_table_in_mainBase["pma_bookmark"]["needed"]="1";
			$list_table_in_mainBase["pma_column_comments"]["needed"]="1";
			$list_table_in_mainBase["pma_pdf_pages"]["needed"]="1";
			$list_table_in_mainBase["pma_relation"]["needed"]="1";
			$list_table_in_mainBase["pma_table_coords"]["needed"]="1";
			$list_table_in_mainBase["pma_table_info"]["needed"]="1";
			$list_table_in_mainBase["pma_history"]["needed"]="1";

			$list_table_in_mainBase["track_c_browsers"]["needed"]="1";
			$list_table_in_mainBase["track_c_countries"]["needed"]="1";
			$list_table_in_mainBase["track_c_os"]["needed"]="1";
			$list_table_in_mainBase["track_c_providers"]["needed"]="1";
			$list_table_in_mainBase["track_c_referers"]["needed"]="1";
			$list_table_in_mainBase["track_e_access"]["needed"]="1";
			$list_table_in_mainBase["track_e_downloads"]["needed"]="1";
			$list_table_in_mainBase["track_e_exercices"]["needed"]="1";
			$list_table_in_mainBase["track_e_links"]["needed"]="1";
			$list_table_in_mainBase["track_e_login"]["needed"]="1";
			$list_table_in_mainBase["track_e_open"]["needed"]="1";
			$list_table_in_mainBase["track_e_subscriptions"]["needed"]="1";
			$list_table_in_mainBase["track_e_uploads"]["needed"]="1";

			 $result = mysql_list_tables ($mainDbName);
			$i = 0;
			while ($i < mysql_num_rows($result))
			{
				$tb_names[$i] = mysql_tablename ($result, $i);
				$list_table_in_mainBase[$tb_names[$i]]["present"]="1";
				$i++;
			}
			reset($list_table_in_mainBase);
			echo "
	<Form action=\"createClaroTables.php\" method=\"post\" lang=\"$iso639_2_code\" >
			<TABLE border=\"1\" cellpadding=\"4\" cellspacing=\"0\">";
				$are_missing = false;
				while (list($nomTable,$needability) = each($list_table_in_mainBase))
				{
					echo "
				<tr>
					<td>
						$nomTable
					</td>
					<td>
						<a href=\"".$phpMyAdminWeb."/tbl_properties.php?db=".$mainDbName."&table=".$nomTable."\">PhpMyAdmin</a>
					</td>
					<td>";
					if (isset($needability["needed"])&&$needability["needed"])
						echo $langNeeded;
					else
						echo "
						!!
						<font color=\"#800000\">
							$langNotNeeded
						</font> !!";
     echo "
					</td>
					<td>";
					if (isset($needability["archive"]) && $needability["archive"])
						echo $langArchive;
					else
						echo "
						!!
						<font color=\"#800000\">
							$langUsed
						</font> !!";
					echo "
					</td>
					<td>";
					if (isset($needability["present"])&&$needability["present"])
					{
						echo "
						<font color=\"#008000\">
							$langPresent
						</font>";
						echo "
					</td>
					<td>
					</td>
					<td ";
					}
					else
					{
						echo "
						<font size=\"+1\" color=\"#FF0000\">
							$langMissing
						</font>";
					    $are_missing = true;
						echo "
					</td>
					<td >
	<!--// remove when createdatabaseisready
						<input type=\"checkbox\" name=\"create[]\" value=\"$nomTable\" ";
						if ($needability["needed"]==1)
							echo " checked ";
						echo ">
	 remove when createdatabaseisready -->
					</td>
					<td ";
					}
					echo "
					</td>
					<td ";
					if (!(isset($needability["needed"])&&isset($needability["present"])))
						echo "bgcolor=\"#8080ff\"> <- ";
					else
						echo ">";
					echo "
					</TD>
				</tr>";
				}
			echo "
			</table>
	<!--// remove when createdatabaseisready
			<br>
			";
			if ($are_missing)
			{
				echo "
			$langCreateMissingNow
			<input type='submit'  name='yes'>
			<br>
			<br>";
			}
			echo "
	remove when createdatabaseisready -->
	</form>";
		if ($list_table_in_mainBase["cours"]["present"]=="1")
		{
			echo "
		$langCheckingCourses
		<br>
		<TABLE border=\"1\" cellpadding=\"4\" cellspacing=\"0\">";

			/*
			3 Try to found courses
			1st, list in course table in main base.
			2nd, list of db
			3th, list of rep
			*/

			// 1 - list in course table in main base.
			$res_listCourses = mysql_query( "SELECT cours.code cc, versionDb FROM cours order by cc");
			$i=0;
			while ($cours = mysql_fetch_array($res_listCourses))
			{
				$Courses[$cours["cc"]]["presentInDb"]=1;
				$Courses[$cours["cc"]]["versionDb"]=$cours["versionDb"];
			}

			// 2 - list of db
			$db_list = mysql_list_dbs();
			$i = 0;
			$cnt = mysql_num_rows($db_list);
			while ($i < $cnt)
			{
				$Courses[mysql_db_name($db_list, $i)]["dbExist"]=1;
				$i++;
			}
			unset($Courses["mysql"]);
			unset($Courses[$mainDbName]);

			// 3 - list of rep
			$dirname = "../../";
			if($dirname[strlen($dirname)-1]!='/')
				$dirname.='/';
			$handle=opendir($dirname);
			while ($entries = readdir($handle))
			{
				if ($entries=='.'||$entries=='..'||$entries=='CVS'||$entries=='claroline')
					continue;
				if (is_dir($dirname.$entries))
				{
					$Courses[$entries]["pathExist"]=1;
				}
			}
			closedir($handle);

			echo "
			<TR>
				<td>
					Base
				</td>
				<td>
					table cours
				</td>
				<td>
					database
				</td>
				<td>
					path
				</td>
				<td>
					phpmyadmin
				</td>
				<td>
					check
				</td>
				<td>
					upgrade
				</td>
			</TR>";

			reset($Courses);
			$i=0;
			while (list($cours,$state) = each($Courses))
			{
				echo "
			<tr>
				<td>
					".$cours."
				</td>
				<td>";
				if ($Courses[$cours]["presentInDb"]==1)
					echo "[".$langExist."]";
				else
					echo "[".$langMissing."]<br>
					<a h ref=\"./addCourseInMainTable.php?currentCourseID=".$cours."\">Create it</a>";
				echo "
				</td>
				<td>";
				if ($Courses[$cours]["dbExist"]==1)
					echo "[".$langExist."]";
				else
					echo "[".$langMissing."]<br>
					<a h ref=\"./createDbOfThisCourse.php?currentCourseID=".$cours."\">Create it</a>";
				echo "
				</td>
				<td>";
				if ($Courses[$cours]["pathExist"]==1)
					echo "[".$langExist."]";
				else
					echo "[".$langMissing."]<br>
					<a href=\"./createPathOfCourse.php?currentCourseID=".$cours."\">Create it</a>";

				echo "
				</td>
				<td>
					<a href=\"".$phpMyAdminWeb."tbl_properties.php?db=".$cours."\">PhpMyAdmin</a>
				</td>
				<td>
					<a href=\"./checkCourseDatabase.php?dbToCheck=".$cours."\">Check</a>
				</td>
				<td>
					".$Courses[$cours]["versionDb"]."
					<br>";
				if ($Courses[$cours]["versionDb"] != $versionDb)
				echo"
					<a href=\"../maintenance/updateDataBase.php?currentCourseID=".$cours."\">Upgrade</a>";
				echo"
				</td>
			</TR>";
					// here  we  ca add  a link  to
					//  prupose  check  of  the  course database
			} //end  of While






/*


			$res_listCourses = mysql_query( "SELECT cours.code cc FROM cours order by cc");
			$i=0;
			while ($cours = mysql_fetch_array($res_listCourses))
			{
				echo "
			<tr>
				<td>
					".$cours["cc"]."
				</td>
				";
				mysql_select_db($cours["cc"],$db);
				if (mysql_errno()>0) // problem with database
				{
					$no = mysql_errno();
				    $msg = mysql_error();
					echo "
				<TD colspan=4>
					[".$no."] - ".$msg."
					<HR noshade>
					<a href=\"".$phpMyAdminWeb."/tbl_properties.php?db=".$mainDbName."&table=".$nomTable."\">".$nomTable."</a>";

				}
				else
				{
					echo "
				<td>
					".$langExist."
				</td>
				<td>
					<a href=\"".$phpMyAdminWeb."/tbl_properties.php?db=".$cours["cc"]."\">PhpMyAdmin</a>
				</td>
				<td>
					<a href=\"./checkCourseDatabase.php?dbToCheck=".$cours["cc"]."\">Check</a>
				</td>
				<td>
					<a href=\"../maintenance/updateDataBase.php?currentCourseID=".$cours["cc"]."\">Upgrade</a>
				</td>";

				}
				echo "
			</TR>";
					// here  we  ca add  a link  to
					//  prupose  check  of  the  course database
			} //end  of While
*/
			echo "
		</TABLE>";
		}
	}	}
}
 include $rootAdminSys."/barre.inc.php";
}
else
{
	echo $lang_no_access_here;
}

@include($includePath."/claro_init_footer.inc.php");
?>
