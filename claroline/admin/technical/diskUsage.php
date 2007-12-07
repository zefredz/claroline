<?php // $Id$
/** 
 * @version Claroline 1.6
 * @license GLP
 * @author  Christophe Gesché <moosh@claroline.net>
 * @package maintenance
 * This  tool comput the disk Usage of each course.
 *
 */

require '../../inc/claro_init_global.inc.php';

$is_allowedToAdmin 	= $is_platformAdmin;
if ( ! $is_allowedToAdmin ) claro_disp_auth_form();

@include($includePath."/lib/debug.lib.inc.php");
include($includePath."/lib/fileManage.lib.php");

$tbl_cdb_names = claro_sql_get_main_tbl();
$tbl_course = $tbl_cdb_names['course'];

$nameTools = $langDiskUsage;

$interbredcrump[]= array ("url"=>$rootAdminWeb, "name"=> $langAdministration);
$interbredcrump[]= array ("url"=>"index.php", "name"=> $langTechAdmin);

$dateNow = claro_disp_localised_date($dateTimeFormatLong);

include($includePath."/claro_init_header.inc.php");

claro_disp_tool_title(
	array(
	'mainTitle'=>$nameTools,
	'subTitle'=> $siteName
	)
);

echo $langCourse_Repository." : ".$coursesRepositorySys."<br>".$langMysql_Repository." : ".($mysqlRepositorySys?$mysqlRepositorySys:"!!! ".$langMissing)."<br>";

?>
<ul>
<?php
if ($display_all_size_of_clarolineRepositorySys )
	echo "
	<li>
		Claroline : ",sprintf("%01.2f", diskUsage($clarolineRepositorySys,"","m"))." ".$byteUnits[2]."
	</li>";

if ($display_all_size_of_Total_Courses)
	echo "
	<li>
		".$langCourses." : ",sprintf("%01.2f", diskUsage($coursesRepositorySys, $mysqlRepositorySys, "m"))." ".$byteUnits[2]."
		(".$langPerhaps_with_others_directory.")
	</li>";
if ($display_all_size_of_garbageRepositorySys )
	echo "
	<li>
		".$langGarbage." :  ",sprintf("%01.2f", diskUsage($garbageRepositorySys,"","m"))." ".$byteUnits[2]."
	</li>";
?>
<li>
<hr>
<form  method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
<input type="checkbox" name="display_all_size_of_clarolineRepositorySys" value="true" > <?php echo $langSize_of_claroline_scripts ?>
<br>
<input type="checkbox" name="display_all_size_of_Total_Courses" value="true" >
<?php echo $langSize_of_course_repository ?>
<br>
<input type="checkbox" name="display_all_size_of_garbageRepositorySys" value="true" > size of garbage
<br>
<input type="checkbox" name="display_all_size_of_selected_courses" value="true" >
<?php echo $langSize_of_selected_courses ?><br>

<select name="coursesToCheck[]" size="" multiple>
		<option value=" all " >** <?php echo $langAll ?> ** !!! <?php echo $langHigh_resources ?></option>
		<?php
			$sqlListCoursesSel = "SELECT fake_code officialCode, code sysCode FROM `".$tbl_course."` order by trim(fake_code) ASC";
			$resCoursesSel= mysql_query_dbg($sqlListCoursesSel);
			while ($courseSel = mysql_fetch_array($resCoursesSel,MYSQL_ASSOC))
			{
				echo "\t<option value=\"".$courseSel['sysCode']."\" >".$courseSel['officialCode']."</option>\n";
			}
			mysql_free_result($resCoursesSel);

		?>
</select>
<input type="submit">
</form>
<hr>
</li>
<?php
if ($display_all_size_of_selected_courses && $coursesToCheck)
{
	echo "
	<li>
		<ol>";
	$sqlListCourses = "SELECT fake_code code, directory dir, dbName db, diskQuota FROM `".$tbl_course."` ";
	if($coursesToCheck[0]==" all ")
	{
		$sqlListCourses .= " order by dbName";
	}
	elseif (is_array($coursesToCheck))
	{
		$sqlListCourses .= " where code in ('".implode( "','", $coursesToCheck )."') order by dbName";
	}
	else
	{
		unset($sqlListCourses);
	}

	if (isset($sqlListCourses))
	{
		$resCourses= mysql_query_dbg($sqlListCourses);
		while ($course = mysql_fetch_array($resCourses,MYSQL_ASSOC))
		{
			$duFiles = diskUsage($coursesRepositorySys.$course["dir"]."/","","k");
			$duBase  = diskUsage($mysqlRepositorySys.$course["db"]."/","","k");
//			$duBase  = getdbsize($course["db"],k);
			
			$duTotal = diskUsage($coursesRepositorySys.$course["dir"]."/",$mysqlRepositorySys.$course["db"]."/","m");
			$quota   = $course["diskQuota"]*1; 
			echo "
			<li>
				".$course["code"]." : ".
				(is_null($course["diskQuota"])?" ".$langNoQuota." ":"Quota : ".$course["diskQuota"])." ".$byteUnits[2]." | ".
				  sprintf("%01.2f", $duFiles )." ".$byteUnits[1]."
				+
				".sprintf("%01.2f", $duBase  )." ".$byteUnits[1]."
				=
				<strong>".sprintf("%01.2f", $duTotal)." ".$byteUnits[2]."</strong>
				".(is_null($course["diskQuota"]) || ($quota > (int) $duTotal)?" ok ":" <font color=\"#FF0000\">!!!!!!!! OVER QUOTA !!!!!!</font>")."
			</li>";
		}
	}

?>
		</ol>
	</li>
<?php
}
?>
</ul>

<?php

include($includePath."/claro_init_footer.inc.php");



function diskUsage($dirFiles="",$dirBase="",$precision="m")
{
	// $precision  -> b Bytes, k Kilobyte, m Megabyte
	switch (PHP_OS)
	{
		case "Linux" :
			$usedspace = (int)`du -sc$precision $dirFiles`;
			$usedspace += (int)`du -sc$precision $dirBase`;
//			$usedspace += (int) getdbsize($course["db"],k);

			break;
		//case "WIN32" : // no  optimazing found  for  WIN32, use  long version
		//case "WINNT" : // no  optimazing found  for  WINNT, use  long version
		default :
			$usedspace	= claro_get_file_size($dirFiles);
			$usedspace += claro_get_file_size($dirBase);
			switch ($precision)
			{
				case "m" : $usedspace /= 1024;
				case "k" : $usedspace /= 1024;
			}
			break;
	}
	return $usedspace;
}

function getdbsize($tdb)
{
	global $dbHost,$dbLogin,$dbPass;
	$db = mysql_connect($dbHost, $dbLogin, $dbPass) or die ("Error connecting to MySQL Server!\n");
	mysql_select_db($tdb, $db);

	$sql_result = "SHOW TABLE STATUS FROM " .$tdb;
	$result = claro_sql_query($sql_result);
	mysql_close($db);

	if($result)
	{
		$size = 0;
		while ($data = mysql_fetch_array($result))
		{
			$size = $size + $data['Data_length'] + $data['Index_length'];
		}
		return $size;
	}
	else
	{
		return FALSE;
	}
}

?>
