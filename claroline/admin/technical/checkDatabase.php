<?php // $Id$
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.4.1 $Revision$                             |
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
$lang_no_access_here ="Pas d'accès ";

$langFile = "admin";
@include('../../inc/claro_init_global.inc.php');

$nameTools = $langCheckDatabase;
$interbredcrump[]= array ("url"=>"index.php", "name"=> $langAdmin);

@include($rootAdminSys."/checkIfHtAccessIsPresent.php");
@include($includePath."/claro_init_header.inc.php");

//$TABLEAGENDA 		= $_course["dbName"]."`.`agenda";
$is_allowedToAdmin 	= $is_platformAdmin || $PHP_AUTH_USER;
if ($is_allowedToAdmin)
{


	claro_disp_tool_title($nameTools, $dateNow);
	claro_disp_msg_arr($controlMsg);
	$db = @	mysql_connect("$dbHost", "$dbLogin", "$dbPass");
	if (mysql_errno()>0) // problem with server
	{
		$no = mysql_errno();
	    $msg = mysql_error();
		echo "<HR>[".$no."] - ".$msg."<HR>";
	// HERE we  must add a  Test of error code.
	// If connection doesn't work -> Message

	   echo "The Server Mysql  doesn't run.<br>
	   please  chech this values
	    host : ".$dbHost."<br>
		user : ".$dbLogin."<br>
		password  : ".$dbPass."<br>
	    ";

	}
	else
	{
		echo  "<hr>$dbHost run - client :";
		echo mysql_get_client_info()." - host :";
		echo mysql_get_host_info()." - proto :";
		echo mysql_get_proto_info()." - serv :";
		echo mysql_get_server_info()." <HR> ";

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
	<em>".realpath($clarolineRepositorySys."/config.inc.php").'</em> : <br>
	<DIV class="source">$mainDbName = "'.$mainDbName."\";<br>
	</DIV>
	and existing base on $dbHost are";
//			error_reporting(E_ALL);
			$db_list = mysql_list_dbs();
			$i = 0;
			$cnt = mysql_num_rows($db_list);
		    echo "
		<OL>";
			$main_base_exist =false;
			while ($i < $cnt)
			{
				$this_db_name = mysql_db_name($db_list, $i++);
				echo "	<LI>".$this_db_name. "\n";
		        if ($this_db_name==$mainDbName)
				{
					echo " <font color=\"#FF0000\">&lt;- this  is the main base</font> ";
					$main_base_exist = true;
				}
			}
		    echo "
		</OL>";
			if (!$main_base_exist)
			{
				echo "<HR>You must create the database <strong>$mainDbName</strong> on the server <strong>$dbHost</strong><br>

			In this  base  you must first run the script <br>
			<em>".realpath($clarolineRepositorySys."/sql/claroline.sql").'</em> with <a href="<?php echo $phpMyAdminWeb?>">phpMyAdmin</a><br><br>
			<br>OR<br>
			<br>
			choose one  of existing database and correct
	        <strong>$mainDbName</strong> in <br>
			<em>'.realpath($includePath."/config.inc.php").'</em> : <br>

			';
			}
		} else
		{
			echo  $mainDbName." ".$langBaseFound;
			// main Database exist
		  	// check of  needed table  in main database.

		  	$list_table_in_mainBase["annonces"]["needed"]="1";
			$list_table_in_mainBase["admin"]["needed"]="1";
			$list_table_in_mainBase["cours"]["needed"]="1";
			$list_table_in_mainBase["cours_user"]["needed"]="1";
			$list_table_in_mainBase["faculte"]["needed"]="1";
			$list_table_in_mainBase["todo"]["needed"]="1";
			$list_table_in_mainBase["user"]["needed"]="1";
			$list_table_in_mainBase["tools"]["needed"]="1";

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
					<a href=\"".$phpMyAdminWeb."/tbl_properties.php?db=".$mainDbName."&table=".$nomTable."\">".$nomTable."</a>
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
		<OL>
			";
				$res_listCourses =mysql_query( "SELECT 	cours.code cc FROM cours ");
				$i=0;
				while ($cours = mysql_fetch_array($res_listCourses))
				{
					echo "
			<LI>".$cours["cc"];
				// base exist ?
					echo " - ";
					mysql_select_db($cours["cc"],$db);
					if (mysql_errno()>0) // problem with database
					{
						$no = mysql_errno();
					    $msg = mysql_error();
						echo "
				<HR>
				[".$no."] - ".$msg."
				<HR noshade>
				<a href=\"".$phpMyAdminWeb."/tbl_properties.php?db=".$mainDbName."&table=".$nomTable."\">".$nomTable."</a>
				<HR>";

					}
					else
					{
						echo $langExist;
					// here  we  ca add  a link  to
					//  prupose  check  of  the  course database
					}
				} //end  of While
				echo "
		</OL>";
			}
		}
	}
include $rootAdminSys."/barre.inc.php";
}
else
{
	echo $lang_no_access_here;
}

include($includePath."/claro_init_footer.inc.php");

/**
 * $Log$
 * Revision 1.1  2004/06/02 07:49:08  moosh
 * Initial revision
 *
 * Revision 1.7  2004/03/24 15:16:54  moosh
 * - use new varName from main conf because old are deprecated and would removed from conf create by install (and upgrade)
 *
 * Revision 1.6  2003/12/30 12:05:39  moosh
 * use  new disp functions
 * claro_disp_tool_title and claro_disp_msg_arr
 *
 * Revision 1.5  2003/10/27 16:20:16  peeters
 * Important changes on the Claroline include paths. Now the paths follows these settings : "inc/lib" instead of "include/libs" and "include", "inc/conf/" instead of "include/config" and "include". The goal of this operation is to put some order in the Claroline path. Now all the library file should be put in "inc/lib" and all configuration file in "inc/conf".
 *
 * Revision 1.4  2003/06/27 20:39:24  moosh
 * give access to admin
 *
 * Revision 1.3  2003/06/19 10:40:08  moosh
 * give  access to  .htaccess authentified
 *
 * Revision 1.2  2003/06/04 16:26:55  moosh
 * uptdate to new database structure
 *
 * Revision 1.1  2003/05/23 05:19:43  moosh
 * tools to config, tune and repair
 *
 * Revision 1.23  2003/03/18 10:38:42  moosh
 * include  new init
 *
 * Revision 1.22  2003/03/18 09:47:47  moosh
 * include  new init
 *
 * Revision 1.21  2003/03/12 13:59:48  moosh
 * phpMyAdminWeb in place of phpMyAdminUrl
 *
 * Revision 1.20  2003/02/14 13:24:00  moosh
 * don't  use  anymore the  cours_faculte table.
 * The field  "faculte" in course table is sufficient
 *
 * Revision 1.19  2003/02/11 14:11:31  olivier
 * -  nothing change,  It's just a test to show CVS at Olivier
 *
 */
 ?>
