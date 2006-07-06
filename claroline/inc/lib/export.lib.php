<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );


/**
* CLAROLINE
*
* @version 1.8 $Revision$
*
* @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
* @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
*
* @package CLEXPORT
*
* @author Yannick Wautelet <yannick_wautelet@hotmail.com>
* @author Claro Team <cvs@claroline.net>
*/

//used libraries
require $includePath . '/installedVersion.inc.php';
require $includePath . '/currentVersion.inc.php';
require_once $includePath . '/lib/fileManage.lib.php';
require_once $includePath . '/lib/export_zip.lib.php';

if (!defined("EXPORT_PATH")) define('EXPORT_PATH', 'C:\Program Files\EasyPHP1-8\www\cvs\claroline.test\claroline\export');


function get_tool_path($toolId)
{
	return '';
}
/**
 * Based on the tool id, this function find the adequate export function
 * If there is no specified export function for the tool, we use the export generic function
 *
 * @see http://www.claroline.net/wiki/index.php/Plugin_system_modelisation#Module_Class
 * @author Yannick Wautelet <yannick_wautelet@hotmail.com>
 *
 * @param  string  $toolId
 * @param  string  $course_id
 * @param  string  $groupId     - can be used as parameter for some function
 *
 * @return false if a problem occured, true if not.
 *
 */
function export_data_tool($toolId,$courseId=null,$groupId=null)
{
	$exportLib      =  get_module_path($toolId) . '/connector/exchange.cnr.php';
	$exportFuncName = $toolId . '_export_content';

	if (file_exists($exportLib))
	{
		echo 'chargement de ' .$exportLib . '<BR />';
		include_once($exportLib);
		if (function_exists($exportFuncName))
		{
			echo 'appel de ' . $exportFuncName . '<BR />';
			call_user_func($exportFuncName,$courseId,$groupId);
		}
		else {export_generic_data_tool($courseId,$toolId);}
	}
	else {export_generic_data_tool($courseId,$toolId);}

}
/**
 * Manage the export of a course
 * It exports ALL tools and document of a course
 * and its meta data
 *
 *
 * @author Yannick Wautelet <yannick_wautelet@hotmail.com>
 *
 * @param  string  $course_id
 *
 * @return false if a problem occured, true if not.
 *
 */
function export_all_data_course_in_file($courseId=null)
{

	if (is_null($courseId)) $courseId = $GLOBALS['_cid'];
	if ('' != $courseId)
	{
		$toolList = import_get_course_tool_list($courseId);

		if(false === export_manifest($courseId))
			return false;
		if(false === export_tool($courseId))
			return false;
		if(false === export_group($courseId))
			return false;
		if(false === export_users($courseId))
			return false;

		foreach($toolList[0] as $toolId)
		{
			if(false === export_data_tool($toolId,$courseId))
			return false;
		}

		if (false == compress_directory(EXPORT_PATH.$courseId."/"))
			return false;

		if (false == claro_delete_file(EXPORT_PATH.$courseId))
				return claro_failure::set_failure("can't delete dir");

		return true;
	} else return claro_failure :: set_failure("invalid course id");
}
/**
 * Get the tool list of this course
 * for the course and for the group
 * and put it into an array
 *
 * @author Yannick Wautelet <yannick_wautelet@hotmail.com>
 *
 * @param  string  $course_id
 *
 * @return false if a problem occured, the array if not.
 *
 */
function export_get_course_tool_list($courseId)
{
	/*
	 * IMPORTANT NOTE : THIS FUNCTION WILL REQUIRE MODIFICATIONS
	 *
	 * at this time those lines are written, the correct function cant be written
	 * The acutel claroline devloppement version is not enough complete for that
	 */
	$tab[0][0] = "CLANN";
	$tab[0][1] = "CLFRM";
	$tab[0][2] = "CLCAL";
	$tab[0][3] = "CLLNP";
	$tab[0][4] = "CLWIKI";
	$tab[0][5] = "CLDSC";
	$tab[0][6] = "CLDOC";
	$tab[0][7] = "CLWRK";
	$tab[0][8] = "CLLNK";
	$tab[0][8] = "CLCHT";

	$tbl = read_group_team_from_db($courseId);
	foreach ($tbl as $group)
	{
		$tab[$group['id']][0] = "CLCHT";
		$tab[$group['id']][1] = "CLWIKI";
		$tab[$group['id']][2] = "CLFRM";
		$tab[$group['id']][3] = "CLDOC";
	}

	return $tab;
}
/**
 * Export a tool into a xml file (for the db data) and a zip file (for the document file)
 * and put it in the temporary directory
 *
 * @author Yannick Wautelet <yannick_wautelet@hotmail.com>
 *
 * @param  string  $course_id
 * @param  string  $tool_id
 *
 * @return false if a problem occured, the array if not.
 *
 */
function export_generic_data_tool($course_id,$tool_id)
{
	$tmp = getTablesList($course_id,$tool_id);
	$prefix = $tmp[0];
	$tablesNameList = $tmp[1];

	// export its documents
	$course_path = get_module_path($course_id);
	export_tool_document($course_id, $course_path);

	// exports its db data
	foreach ($tablesNameList as $tableNameArr )
	{
		$tableName = array_pop($tableNameArr);
		$tableContent = selectAllFromTable($tableName);
		$toolData[$tableName] = $tableContent;
		$toolData[$tableName]['table_name'] = $tableName;
	}
	$prefix = str_replace("\_","_",$prefix);
	$prefix = str_replace("%","",$prefix);
	$prefix = str_replace("'","",$prefix);
	$dom = export_generic_data_in_dom($toolData, $course_id,$tool_id,$prefix);
	if (false !== $dom)
	{
		dump_file("tools",$tool_id,$dom,$course_id);
		return true;
	}
	else return false;

}

/**
 * Export all informations about a tool into a dom object
 * Not only the db data but also other information, like the create table sql code of the tables
 *
 * @author Yannick Wautelet <yannick_wautelet@hotmail.com>
 * @param  array   $tablesContent  - the db data of the tool
 * @param  string  $course_id
 * @param  string  $tool_id
 * @param  string  $prefix		   - prefix of the tool tables, this will be usefull for the import
 *
 * @return false if a problem occured, the dom object if not.
 *
 */
function export_generic_data_in_dom($tablesContent,$course_id,$tool_id,$prefix)
{
	$dom = domxml_new_doc('1.0');
	$generic_data = $dom->append_child($dom->create_element($tool_id));
	if (is_array($tablesContent) && (count($tablesContent) > 0))
	{
		foreach ($tablesContent as $tableName => $tableContent)
		{
			$tableElement= $generic_data->append_child($dom->create_element($tableName));
			foreach ($tableContent as $genericId => $rawContent)
			{
				if(isset($rawContent) && is_array($rawContent))
				{
					$genericElement = $tableElement->append_child($dom->create_element('content'));
					$genericElement->set_attribute('id', $genericId);
					foreach ($rawContent as $fieldName => $fieldContent)
					{
						$index = $genericElement->append_child($dom->create_element($fieldName));
						if(is_null($fieldContent))
						{
							$index->set_attribute("isNull","true");
						}
						$index->append_child($dom->create_text_node(utf8_encode($fieldContent)));
					}
				}
			}

			$sql = "SHOW CREATE TABLE `".$tableName."`";
			$result = claro_sql_query_fetch_all($sql);
			$create_table_sql_query = str_replace("CREATE TABLE","CREATE TABLE IF NOT EXISTS",$result[0]["Create Table"]);
			$index = $tableElement->append_child($dom->create_element("create_table"));
			$index->append_child($dom->create_cdata_section(utf8_encode($create_table_sql_query)));
			$index = $tableElement->append_child($dom->create_element("prefix"));
			$index->append_child($dom->create_text_node(utf8_encode($prefix)));
			$index = $tableElement->append_child($dom->create_element("table_name"));
			$index->append_child($dom->create_text_node(utf8_encode($tableContent['table_name'])));
		}
	}
	return $dom;
}


/**
 * Select all from a table
 *
 *
 * @author Yannick Wautelet <yannick_wautelet@hotmail.com>
 *
 *  @param  array  $tableName
 *
 * @return the result of the query
 *
 */
function selectAllFromTable($tableName)
{
	$sql = "SELECT * FROM `".$tableName."`";
	return claro_sql_query_fetch_all($sql);
}

/**
 * Select all from a table
 *
 * @author Yannick Wautelet <yannick_wautelet@hotmail.com>
 *
 * @param  array  $course_id
 * @param  array  $tool_id
 *
 * @return the result of the query
 *
 */
function getTablesList($course_id,$tool_id)
{
	/*
	 * IMPORTANT NOTE : THIS FUNCTION WILL NEED TO BE REWRITTEN
	 *
	 * at the moment where these lines are written, the claroline devloppement version miss some functions to
	 * complete this function
	 *
	 * for now, this function return all tables of a course
	 *
	 */
	$tab = array();
	$tab["cid"] = $course_id;
	$tab["tid"] = $tool_id;
	//$prefix = claro_sql_get_tables("",$tab);
	//$prefix = claro_sql_get_course_tbl("",$tab);

	//$prefix = "claroline`.`c_es1_001_tool";  // Mono avec claro_sql_get_tables
	//$prefix = "c_es1_001`.`tool";            // Multi avec claro_sql_get_tables
	//$prefix = "c_es1_001_tool";              // Mono  avec claro_sql_get_course_tbl
	//$prefix = "c_es1_001`.`tool";        	   // Multi avec claro_sql_get_course_tbl


	$prefix = "claroline`.`c_es1_001";
	$tab = explode('`.`',$prefix);
	$prefix = $tab[count($tab)-1];


	$prefix = str_replace("_","\_",$prefix);
	$prefix = str_replace("%","\%",$prefix);
	$prefix = "'".$prefix."%'";

	$sql = "SHOW TABLES LIKE ".$prefix;

	$return[0] = $prefix;
	$return[1] = claro_sql_query_fetch_all($sql);

	return $return;
}

/**
 *
 * Export all db data about tool in file "tool.xml"
 *
 * @author Yannick Wautelet <yannick_wautelet@hotmail.com>
 * @param  string  $course_id
 * @return false if a problem occured, true if not.
 */
function export_tool($course_id)
{
	$tbl = read_tool_from_db($course_id);
	$dom = export_tool_in_dom($tbl, $course_id);
	if (false !== $dom)
	{
		dump_file("meta_data","tool",$dom,$course_id);
		return true;
	}
	else return false;
}
/**
 *
 * Export all db data about the course in file "manifest.xml"
 *
 * @author Yannick Wautelet <yannick_wautelet@hotmail.com>
 * @param  string  $course_id
 * @return false if a problem occured, true if not.
 */
function export_manifest($course_id)
{
	$tbl['course'] = read_course_from_db($course_id);
	$tbl['group_team'] = read_group_team_from_db($course_id);
	$tbl['group_property'] = read_group_property_from_db($course_id);
	$tbl['users'] = read_users_from_db($course_id);

	$dom = export_manifest_in_dom($tbl, $course_id);
	if (false !== $dom)
	{
		dump_file("meta_data","manifest",$dom,$course_id);
		return true;
	}
	else return false;
}

/**
 *
 * Export all db data about group in file "group.xml"
 *
 * @author Yannick Wautelet <yannick_wautelet@hotmail.com>
 * @param  string  $course_id
 * @return false if a problem occured, true if not.
 */
function export_group($course_id)
{
	if (false == export_group_document($course_id))
			return false;
	$tbl = read_group_from_db($course_id);
	$dom = export_group_in_dom($tbl, $course_id);
	if (false !== $dom)
	{
		if(false === dump_file("tools","CLGRP",$dom,$course_id))
			return false;
		return true;
	}
	else return false;
}
/**
 *
 * Export all db data about users in file "users.xml"
 *
 * @author Yannick Wautelet <yannick_wautelet@hotmail.com>
 * @param  string  $course_id
 * @return false if a problem occured, true if not.
 */
function export_users($course_id)
{
	$tbl['user'] = read_users_from_db($course_id);
	$tbl['rel_course_user'] = read_rel_course_user_from_db($course_id);
	$dom = export_users_in_dom($tbl, $course_id);
	if (false !== $dom)
	{
		if(false === dump_file("meta_data","users",$dom,$course_id))
			return false;
		return true;
	}
	else return false;
}
/**
 *
 * Export all data contained in the array $tbl about users into a $dom object
 *
 * @author Yannick Wautelet <yannick_wautelet@hotmail.com>
 * @param  array   $tbl  		 - contain all data about users to export
 * @param  string  $course_id
 * @return the dom object
 */
function export_users_in_dom($tbl, $course_id)
{
	$dom = domxml_new_doc('1.0');
	$cl_user = $dom->append_child($dom->create_element('users'));

	$rel_course_user = $cl_user->append_child($dom->create_element('rel_course_user'));
	if (isset ($tbl["rel_course_user"]) && is_array($tbl["rel_course_user"]) && (count($tbl["rel_course_user"]) > 0))
	{
		foreach ($tbl["rel_course_user"] as $tab_content)
		{
			$code_cours = $rel_course_user->append_child($dom->create_element('user_id'));
			$code_cours->set_attribute('user_id', $tab_content['user_id']);
			$user_id = $code_cours->append_child($dom->create_element('course_id'));
			$user_id->append_child($dom->create_text_node(utf8_encode($tab_content['course_id'])));
			$statut = $code_cours->append_child($dom->create_element('statut'));
			$statut->append_child($dom->create_text_node($tab_content['statut']));
			$role = $code_cours->append_child($dom->create_element('role'));
			$role->append_child($dom->create_text_node(utf8_encode($tab_content['role'])));
			$team = $code_cours->append_child($dom->create_element('team'));
			$team->append_child($dom->create_text_node(utf8_encode($tab_content['team'])));
			$tutor = $code_cours->append_child($dom->create_element('tutor'));
			$tutor->append_child($dom->create_text_node(utf8_encode($tab_content['tutor'])));
		}
	}

	$user = $cl_user->append_child($dom->create_element('user'));

	if (is_array($tbl["user"]) && (count($tbl["user"]) > 0))
	{
		foreach ($tbl["user"] as $tab_content)
		{
			$user_id = $user->append_child($dom->create_element('user_id'));
			$user_id->set_attribute("user_id", $tab_content["user_id"]);
			$firstname = $user_id->append_child($dom->create_element('firstname'));
			$firstname->append_child($dom->create_text_node(utf8_encode($tab_content["firstname"])));
			$lastname = $user_id->append_child($dom->create_element('lastname'));
			$lastname->append_child($dom->create_text_node(utf8_encode($tab_content["lastname"])));
			$username = $user_id->append_child($dom->create_element('username'));
			$username->append_child($dom->create_text_node(utf8_encode($tab_content["username"])));
			$password = $user_id->append_child($dom->create_element('password'));
			$password->append_child($dom->create_text_node(utf8_encode($tab_content["password"])));
			$authSource = $user_id->append_child($dom->create_element('authSource'));
			$authSource->append_child($dom->create_text_node(utf8_encode($tab_content["authSource"])));
			$email = $user_id->append_child($dom->create_element('email'));
			$email->append_child($dom->create_text_node(utf8_encode($tab_content["email"])));
			$statut = $user_id->append_child($dom->create_element('statut'));
			$statut->append_child($dom->create_text_node($tab_content["statut"]));
			$officialCode = $user_id->append_child($dom->create_element('officialCode'));
			$officialCode->append_child($dom->create_text_node(utf8_encode($tab_content["officialCode"])));
			$phoneNumber = $user_id->append_child($dom->create_element('phoneNumber'));
			$phoneNumber->append_child($dom->create_text_node(utf8_encode($tab_content["phoneNumber"])));
			$pictureUri = $user_id->append_child($dom->create_element('pictureUri'));
			$pictureUri->append_child($dom->create_text_node(utf8_encode($tab_content["pictureUri"])));
			$creatorId = $user_id->append_child($dom->create_element('creatorId'));
			$creatorId->append_child($dom->create_text_node($tab_content["creatorId"]));
		}
	}
	if (!file_exists(EXPORT_PATH."/".$course_id))
		claro_mkdir(EXPORT_PATH."/".$course_id, CLARO_FILE_PERMISSIONS, true);

	return $dom;
}
/**
 *
 * Export all data contained in the array $tbl about the course and manifest into a $dom object
 *
 * @author Yannick Wautelet <yannick_wautelet@hotmail.com>
 * @param  array   $tbl  		- contain all data about the course to export
 * @param  string  $course_id
 * @return the dom object
 */
function export_manifest_in_dom($tbl, $course_id)
{
	$dom = domxml_new_doc('1.0');
	$manifest = $dom->append_child($dom->create_element('MANIFEST'));

	$group_team = $manifest->append_child($dom->create_element('group_team'));
	if (is_array($tbl['group_team']) && (count($tbl['group_team']) > 0))
	{
		foreach ($tbl['group_team'] as $tab_content)
		{
			$id = $group_team->append_child($dom->create_element('id'));
			$id->set_attribute('id', $tab_content['id']);
			$name = $id->append_child($dom->create_element('name'));
			$name->append_child($dom->create_text_node($tab_content['name']));
		}
	}
	$group_property = $manifest->append_child($dom->create_element('group_property'));
	if (is_array($tbl['group_property']) && (count($tbl['group_property']) > 0))
	{
		foreach ($tbl['group_property'] as $tab_content)
		{
			$id = $group_property->append_child($dom->create_element('id'));
			$id->set_attribute('id', $tab_content['id']);
			$self = $id->append_child($dom->create_element('self_registration'));
			$self->append_child($dom->create_text_node($tab_content['self_registration']));
			$nbgroup = $id->append_child($dom->create_element('nbGroupPerUser'));
			$nbgroup->append_child($dom->create_text_node($tab_content['nbGroupPerUser']));
			$private = $id->append_child($dom->create_element('private'));
			$private->append_child($dom->create_text_node($tab_content['private']));
			$forum = $id->append_child($dom->create_element('forum'));
			$forum->append_child($dom->create_text_node($tab_content['forum']));
			$document = $id->append_child($dom->create_element('document'));
			$document->append_child($dom->create_text_node($tab_content['document']));
			$wiki = $id->append_child($dom->create_element('wiki'));
			$wiki->append_child($dom->create_text_node($tab_content['wiki']));
			$chat = $id->append_child($dom->create_element('chat'));
			$chat->append_child($dom->create_text_node($tab_content['chat']));
		}
	}
	$users = $manifest->append_child($dom->create_element('users'));
	if (is_array($tbl['users']) && (count($tbl['users']) > 0))
	{
		foreach ($tbl['users'] as $tab_content)
		{
			$id = $users->append_child($dom->create_element('user_id'));
			$id->set_attribute('user_id', $tab_content['user_id']);
			$username = $id->append_child($dom->create_element('username'));
			$username->append_child($dom->create_text_node($tab_content['username']));
			$firstname = $id->append_child($dom->create_element('firstname'));
			$firstname->append_child($dom->create_text_node($tab_content['firstname']));
			$lastname = $id->append_child($dom->create_element('lastname'));
			$lastname->append_child($dom->create_text_node($tab_content['lastname']));
			$officialCode = $id->append_child($dom->create_element('officialCode'));
			$officialCode->append_child($dom->create_text_node($tab_content['officialCode']));
		}
	}

	$course = $manifest->append_child($dom->create_element('course'));

	if (is_array($tbl['course']) && (count($tbl['course']) > 0))
	{
		foreach ($tbl['course'] as $tab_content)
		{
			$cours_id = $course->append_child($dom->create_element('cours_id'));
			$cours_id->append_child($dom->create_text_node($tab_content['cours_id']));
			$code = $course->append_child($dom->create_element('code'));
			$code->append_child($dom->create_text_node(utf8_encode($tab_content['code'])));
			$fake_code = $course->append_child($dom->create_element('fake_code'));
			$fake_code->append_child($dom->create_text_node(utf8_encode($tab_content['fake_code'])));
			$directory = $course->append_child($dom->create_element('directory'));
			$directory->append_child($dom->create_text_node(utf8_encode($tab_content['directory'])));
			$dbName = $course->append_child($dom->create_element('dbName'));
			$dbName->append_child($dom->create_text_node(utf8_encode($tab_content['dbName'])));
			$languageCourse = $course->append_child($dom->create_element('languageCourse'));
			$languageCourse->append_child($dom->create_text_node(utf8_encode($tab_content['languageCourse'])));
			$intitule = $course->append_child($dom->create_element('intitule'));
			$intitule->append_child($dom->create_text_node(utf8_encode($tab_content['intitule'])));
			$faculte = $course->append_child($dom->create_element('faculte'));
			$faculte->append_child($dom->create_text_node(utf8_encode($tab_content['faculte'])));
			$enrollment_key = $course->append_child($dom->create_element('enrollment_key'));
			$enrollment_key->append_child($dom->create_text_node(utf8_encode($tab_content['enrollment_key'])));
			$titulaires = $course->append_child($dom->create_element('titulaires'));
			$titulaires->append_child($dom->create_text_node(utf8_encode($tab_content['titulaires'])));
			$email = $course->append_child($dom->create_element('email'));
			$email->append_child($dom->create_text_node(utf8_encode($tab_content['email'])));
			$departmentUrlName = $course->append_child($dom->create_element('departmentUrlName'));
			$departmentUrlName->append_child($dom->create_text_node(utf8_encode($tab_content['departmentUrlName'])));
			$departmentUrl = $course->append_child($dom->create_element('departmentUrl'));
			$departmentUrl->append_child($dom->create_text_node(utf8_encode($tab_content['departmentUrl'])));
			$diskQuota = $course->append_child($dom->create_element('diskQuota'));
			$diskQuota->append_child($dom->create_text_node($tab_content['diskQuota']));
			$versionDb = $course->append_child($dom->create_element('versionDb'));
			$versionDb->append_child($dom->create_text_node(utf8_encode($tab_content['versionDb'])));
			$versionClaro = $course->append_child($dom->create_element('versionClaro'));
			$versionClaro->append_child($dom->create_text_node(utf8_encode($tab_content['versionClaro'])));
			$lastVisit = $course->append_child($dom->create_element('lastVisit'));
			$lastVisit->append_child($dom->create_text_node($tab_content['lastVisit']));
			$lastEdit = $course->append_child($dom->create_element('lastEdit'));
			$lastEdit->append_child($dom->create_text_node($tab_content['lastEdit']));
			$creationDate = $course->append_child($dom->create_element('creationDate'));
			$creationDate->append_child($dom->create_text_node($tab_content['creationDate']));
			$expirationDate = $course->append_child($dom->create_element('expirationDate'));
			$expirationDate->append_child($dom->create_text_node($tab_content['expirationDate']));

			if(1 == $tab_content['visible'])
			{
				$courseVisibility = $course->append_child($dom->create_element('courseVisibility'));
				$courseVisibility->append_child($dom->create_text_node(false));
				$courseEnrollAllowed = $course->append_child($dom->create_element('courseEnrollAllowed'));
				$courseEnrollAllowed->append_child($dom->create_text_node(true));
			}
			else if(2 == $tab_content['visible'])
			{
				$courseVisibility = $course->append_child($dom->create_element('courseVisibility'));
				$courseVisibility->append_child($dom->create_text_node(true));
				$courseEnrollAllowed = $course->append_child($dom->create_element('courseEnrollAllowed'));
				$courseEnrollAllowed->append_child($dom->create_text_node(true));
			}
			else if(3 == $tab_content['visible'])
			{
				$courseVisibility = $course->append_child($dom->create_element('courseVisibility'));
				$courseVisibility->append_child($dom->create_text_node(true));
				$courseEnrollAllowed = $course->append_child($dom->create_element('courseEnrollAllowed'));
				$courseEnrollAllowed->append_child($dom->create_text_node(false));
			}
			else
			{
				$courseVisibility = $course->append_child($dom->create_element('courseVisibility'));
				$courseVisibility->append_child($dom->create_text_node(false));
				$courseEnrollAllowed = $course->append_child($dom->create_element('courseEnrollAllowed'));
				$courseEnrollAllowed->append_child($dom->create_text_node(false));
			}

		}
	}

	$tool = $manifest->append_child($dom->create_element('toolsInfo'));
	$groupToolList = import_get_course_tool_list($course_id);
	foreach ($groupToolList as $id => $toolList)
	{
		$groupInfo = $tool->append_child($dom->create_element("group"));
		$groupInfo->set_attribute("id",$id);
		foreach ($toolList as $tool_id)
		{
			$toolInfo = $groupInfo->append_child($dom->create_element("tool"));
			$toolInfo->append_child($dom->create_text_node($tool_id));
		}
	}
	$course = $manifest->append_child($dom->create_element('plateform'));
	$plateform = $course->append_child($dom->create_element('plateform_id'));
	$plateform->append_child($dom->create_text_node(get_conf('platform_id')));
	$newversion = $course->append_child($dom->create_element('new_version'));
	$newversion->append_child($dom->create_text_node(get_conf('new_version')));
	$new_ver_branch = $course->append_child($dom->create_element('new_version_branch'));
	$new_ver_branch->append_child($dom->create_text_node(get_conf('new_version_branch')));
	$clarolineVer = $course->append_child($dom->create_element('clarolineVersion'));
	$clarolineVer->append_child($dom->create_text_node(get_conf('clarolineVersion')));
	$verDb = $course->append_child($dom->create_element('versionDb'));
	$verDb->append_child($dom->create_text_node(get_conf('versionDb')));

	return $dom;
}
/**
 *
 * Export all data contained in db about tool into an array
 *
 * @author Yannick Wautelet <yannick_wautelet@hotmail.com>
 * @param  string  $course_id
 * @return the array
 */
function read_tool_from_db($course_id)
{
	$tab = array ();
	$tab["list"] = read_tool_list_from_db($course_id);
	$tab["intro"] = read_tool_intro_from_db($course_id);

	return $tab;
}
/**
 *
 * Export all data contained in the array $tbl about tool into a $dom object
 *
 * @author Yannick Wautelet <yannick_wautelet@hotmail.com>
 * @param  array   $tbl  		- contain all data about tool to export
 * @param  string  $course_id
 * @return the dom object
 */
function export_tool_in_dom($tbl, $course_id)
{
	$dom = domxml_new_doc('1.0');
	$tool = $dom->append_child($dom->create_element('tool'));

	$tool_list = $tool->append_child($dom->create_element('tool_list'));
	if (isset ($tbl["list"]) && is_array($tbl["list"]) && (count($tbl["list"]) > 0))
	{
		foreach ($tbl["list"] as $tab_content)
		{
			$id = $tool_list->append_child($dom->create_element('id'));
			$id->set_attribute("id", $tab_content["id"]);
			$tool_id = $id->append_child($dom->create_element('tool_id'));
			$tool_id->append_child($dom->create_text_node($tab_content["tool_id"]));
			$rank = $id->append_child($dom->create_element('rank'));
			$rank->append_child($dom->create_text_node($tab_content["rank"]));
			$access = $id->append_child($dom->create_element('access'));
			$access->append_child($dom->create_text_node($tab_content["access"]));
			$script_url = $id->append_child($dom->create_element('script_url'));
			$script_url->append_child($dom->create_text_node(utf8_encode($tab_content["script_url"])));
			$script_name = $id->append_child($dom->create_element('script_name'));
			$script_name->append_child($dom->create_text_node(utf8_encode($tab_content["script_name"])));
			$addedTool = $id->append_child($dom->create_element('addedTool'));
			$addedTool->append_child($dom->create_text_node($tab_content["addedTool"]));
		}
	}

	$tool_intro = $tool->append_child($dom->create_element('tool_intro'));
	if (isset ($tbl["intro"]) && is_array($tbl["intro"]) && (count($tbl["intro"]) > 0))
	{
		foreach ($tbl["intro"] as $tab_content)
		{
			$id = $tool_intro->append_child($dom->create_element('id'));
			$id->set_attribute("id", $tab_content["id"]);
			$tool_id = $id->append_child($dom->create_element('tool_id'));
			$tool_id->append_child($dom->create_text_node($tab_content["tool_id"]));
			$title = $id->append_child($dom->create_element('title'));
			$title->append_child($dom->create_text_node(utf8_encode($tab_content["title"])));
			$display_date = $id->append_child($dom->create_element('display_date'));
			$display_date->append_child($dom->create_text_node($tab_content["display_date"]));
			$content = $id->append_child($dom->create_element('content'));
			$content->append_child($dom->create_text_node(utf8_encode($tab_content["content"])));
			$rank = $id->append_child($dom->create_element('rank'));
			$rank->append_child($dom->create_text_node($tab_content["rank"]));
			$visibility = $id->append_child($dom->create_element('visibility'));
			$visibility->append_child($dom->create_text_node($tab_content["visibility"]));
		}
	}
	return $dom;
}

/**
 *
 * Export all data contained in db about group into an array
 *
 * @author Yannick Wautelet <yannick_wautelet@hotmail.com>
 * @param  string  $course_id
 * @return the array
 */
function read_group_from_db($course_id)
{
	$tab = array ();
	$tab["property"] = read_group_property_from_db($course_id);
	$tab["rel_team_user"] =  read_group_rel_team_user_from_db($course_id);
	$tab["team"] =  read_group_team_from_db($course_id);

	return $tab;
}
/**
 *
 * Export all data contained in the array $tbl about group into a $dom object
 *
 * @author Yannick Wautelet <yannick_wautelet@hotmail.com>
 * @param  array   $tbl  		- contain all data about group to export
 * @param  string  $course_id
 * @return the dom object
 */
function export_group_in_dom($tbl, $course_id)
{
	$dom = domxml_new_doc('1.0');
	$link = $dom->append_child($dom->create_element('group'));
	$links = $link->append_child($dom->create_element('group_property'));
	if (isset ($tbl["property"]) && is_array($tbl["property"]) && (count($tbl["property"]) > 0))
	{
		foreach ($tbl["property"] as $tab_content)
		{
			$id = $links->append_child($dom->create_element('id'));
			$id->set_attribute("id", $tab_content["id"]);
			$self_registration = $id->append_child($dom->create_element('self_registration'));
			$self_registration->append_child($dom->create_text_node($tab_content["self_registration"]));
			$nbGroupPerUser = $id->append_child($dom->create_element('nbGroupPerUser'));
			$nbGroupPerUser->append_child($dom->create_text_node($tab_content["nbGroupPerUser"]));
			$private = $id->append_child($dom->create_element('private'));
			$private->append_child($dom->create_text_node($tab_content["private"]));
			$forum = $id->append_child($dom->create_element('forum'));
			$forum->append_child($dom->create_text_node($tab_content["forum"]));
			$document = $id->append_child($dom->create_element('document'));
			$document->append_child($dom->create_text_node($tab_content["document"]));
			$wiki = $id->append_child($dom->create_element('wiki'));
			$wiki->append_child($dom->create_text_node($tab_content["wiki"]));
			$chat = $id->append_child($dom->create_element('chat'));
			$chat->append_child($dom->create_text_node($tab_content["chat"]));
		}
	}
	$resources = $link->append_child($dom->create_element('group_rel_team_user'));
	if (isset ($tbl["rel_team_user"]) && is_array($tbl["rel_team_user"]) && (count($tbl["rel_team_user"]) > 0))
	{
		foreach ($tbl["rel_team_user"] as $tab_content)
		{
			$id = $resources->append_child($dom->create_element('id'));
			$id->set_attribute("id", $tab_content["id"]);
			$user = $id->append_child($dom->create_element('user'));
			$user->append_child($dom->create_text_node($tab_content["user"]));
			$team = $id->append_child($dom->create_element('team'));
			$team->append_child($dom->create_text_node($tab_content["team"]));
			$status = $id->append_child($dom->create_element('status'));
			$status->append_child($dom->create_text_node($tab_content["status"]));
			$role = $id->append_child($dom->create_element('role'));
			$role->append_child($dom->create_text_node(utf8_encode($tab_content["role"])));
		}
	}
	$resources = $link->append_child($dom->create_element('group_team'));
	if (isset ($tbl["team"]) && is_array($tbl["team"]) && (count($tbl["team"]) > 0))
	{
		foreach ($tbl["team"] as $tab_content)
		{
			$id = $resources->append_child($dom->create_element('id'));
			$id->set_attribute("id", $tab_content["id"]);
			$name = $id->append_child($dom->create_element('name'));
			$name->append_child($dom->create_text_node(utf8_encode($tab_content["name"])));
			$description = $id->append_child($dom->create_element('description'));
			$description->append_child($dom->create_text_node(utf8_encode($tab_content["description"])));
			$tutor = $id->append_child($dom->create_element('tutor'));
			$tutor->append_child($dom->create_text_node($tab_content["tutor"]));
			$maxStudent = $id->append_child($dom->create_element('maxStudent'));
			$maxStudent->append_child($dom->create_text_node($tab_content["maxStudent"]));
			$secretDirectory = $id->append_child($dom->create_element('secretDirectory'));
			$secretDirectory->append_child($dom->create_text_node(utf8_encode($tab_content["secretDirectory"])));
		}
	}
	return $dom;
}

/**
 *
 * Read tool intro table in db and put its data into an array
 *
 * @author Yannick Wautelet <yannick_wautelet@hotmail.com>
 * @param  string  $course_id
 * @return false if a problem occured, the array
 */
function read_tool_intro_from_db($course_id)
{
	$tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
	$sql = "SELECT			id,
							tool_id,
							title,
							display_date,
							content,
							rank,
							visibility
			FROM `".$tbl['tool_intro']."`";

	return claro_sql_query_fetch_all($sql);
}
/**
 *
 * Read tool list table in db and put its data into an array
 *
 * @author Yannick Wautelet <yannick_wautelet@hotmail.com>
 * @param  string  $course_id
 * @return false if a problem occured, the array
 */
function read_tool_list_from_db($course_id)
{
	$tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
	$sql = "SELECT			id,
							tool_id,
							rank,
							access,
							script_url,
							script_name,
							addedTool
			FROM `".$tbl['tool']."`";

	return claro_sql_query_fetch_all($sql);
}

/**
 *
 * Read users table in db and put its data into an array
 *
 * @author Yannick Wautelet <yannick_wautelet@hotmail.com>
 * @param  string  $course_id
 * @return false if a problem occured, the array
 */
function  read_users_from_db($course_id)
{
	$tblMain = claro_sql_get_main_tbl();
	$sql = "SELECT			u.user_id,
				  	 nom AS firstname,
				  prenom AS lastname,
							username,
							password,
							authSource,
							email,
							u.statut,
							officialCode,
							phoneNumber,
							pictureUri,
							creatorId
	    	 FROM `".$tblMain['user']."` AS u
	    	 INNER JOIN `".$tblMain['rel_course_user']."` AS c
			 ON u.user_id = c.user_id
			 WHERE c.code_cours = '".$course_id."'";

	return claro_sql_query_fetch_all($sql);
}
/**
 *
 * Read rel_course_user table in db and put its data into an array
 *
 * @author Yannick Wautelet <yannick_wautelet@hotmail.com>
 * @param  string  $course_id
 * @return false if a problem occured, the array
 */
function read_rel_course_user_from_db($course_id)
{
	$tblMain = claro_sql_get_main_tbl();
	$sql = "SELECT   code_cours as course_id,
					 user_id,
					 statut,
					 role,
					 team,
					 tutor
			FROM `".$tblMain['rel_course_user']."`
		 	WHERE code_cours = '".$course_id."'";

	return claro_sql_query_fetch_all($sql);
}

/**
 *
 * Read course table in db and put its data into an array
 *
 * @author Yannick Wautelet <yannick_wautelet@hotmail.com>
 * @param  string  $course_id
 * @return false if a problem occured, the array
 */
function read_course_from_db($course_id)
{
	$tblMain = claro_sql_get_main_tbl();
	$sql = "SELECT		cours_id,
						code,
						fake_code,
						directory,
						dbName,
						languageCourse,
						intitule,
						faculte,
						visible,
						enrollment_key,
						titulaires,
						email,
						departmentUrlName,
						departmentUrl,
						diskQuota,
						versionDb,
						versionClaro,
						lastVisit,
						lastEdit,
						creationDate,
						expirationDate
		  	FROM `".$tblMain['course']."`
			WHERE code = '".$course_id."'";

	return claro_sql_query_fetch_all($sql);
}
/**
 *
 * Read group property table in db and put its data into an array
 *
 * @author Yannick Wautelet <yannick_wautelet@hotmail.com>
 * @param  string  $course_id
 * @return false if a problem occured, the array
 */
function read_group_property_from_db($course_id)
{
	$tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
	$sql = "SELECT		id,
						self_registration,
						nbGroupPerUser,
						private,
						forum,
						document,
						wiki,
						chat
			FROM `".$tbl['group_property']."`";

	return claro_sql_query_fetch_all($sql);
}

/**
 *
 * Read rel_team_user table in db and put its data into an array
 *
 * @author Yannick Wautelet <yannick_wautelet@hotmail.com>
 * @param  string  $course_id
 * @return false if a problem occured, the array
 */
function read_group_rel_team_user_from_db($course_id)
{
	$tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
	$sql = "SELECT 		id,
						user,
						team,
						status,
						role
			FROM `".$tbl['group_rel_team_user']."`";

	return claro_sql_query_fetch_all($sql);
}
/**
 *
 * Read group_team table in db and put its data into an array
 *
 * @author Yannick Wautelet <yannick_wautelet@hotmail.com>
 * @param  string  $course_id
 * @return false if a problem occured, the array
 */
function read_group_team_from_db($course_id)
{
	$tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
	$sql = "SELECT			id,
							name,
							description,
							tutor,
							maxStudent,
							secretDirectory
			FROM `".$tbl['group_team']."`";

	return claro_sql_query_fetch_all($sql);
}

/**
 *
 * Export group documents into file "group.zip"
 *
 * @author Yannick Wautelet <yannick_wautelet@hotmail.com>
 * @param  string  $course_id
 * @return false if a problem occured, true if not
 */
function export_group_document($course_id)
{
	return export_tool_document($course_id, "CLGRP");
}


/**
 *
 * Export a directory from "rootsys"/courses/"course_id"
 * to a zip file of the same name
 *
 *
 * @author Yannick Wautelet <yannick_wautelet@hotmail.com>
 * @param  string  $course_id
 * @param  string  $toolName     - name of the directory
 * @return false if a problem occured, true if not
 */
function export_tool_document($course_id, $toolId)
{
	$context[CLARO_CONTEXT_COURSE] = $course_id;
	$course_path = claro_get_data_path($context);
    $context[CLARO_CONTEXT_TOOLLABEL] = $toolId;
    $course_tool_path = claro_get_data_path($context);

	//test if the course folder exist
	if (file_exists($course_path) && is_dir($course_path))
	{
		//test if the course tool folder exist
		if (file_exists($course_tool_path) && is_dir($course_tool_path))
		{

			//test if no error occured while compressing
			if (false !== compress_directory($course_tool_path))
			{

				$tool_zip_folder_path = $course_path.basename($course_tool_path).".zip";

				$tool_zip_export_folder_path = EXPORT_PATH.$course_id.'/tools/'.$toolId.'/'.$toolId.".zip";

				//test if the temporary export folder exist
				if (!file_exists(dirname($tool_zip_export_folder_path)) && !is_dir($tool_zip_export_folder_path))
				{
					claro_mkdir(dirname($tool_zip_export_folder_path),0777,TRUE);
				}
				copy($tool_zip_folder_path, $tool_zip_export_folder_path);
				unlink($tool_zip_folder_path);
				//test if the old zip file is well removed
				if (file_exists($tool_zip_export_folder_path) && !file_exists($tool_zip_folder_path))
				{
					return true;
				} else
					return claro_failure :: set_failure("document_export_failed");
			} else
				return claro_failure :: set_failure("document_export_failed");
		} else{/*nothing to do*/}

	} else
		return claro_failure :: set_failure("document_export_failed");
}

/**
 *
 * Dump the dom file
 *
 * @author Yannick Wautelet <yannick_wautelet@hotmail.com>
 * @param  string  $data_type    - "metadata" or "tool", it's the name of the directory where the file must be
 * 								   this is user to separate the metadata from the tool data
 * @param  string  $toolName     - name of the directory
 * @param  object  $dom          - the dom object contained the info to put in file
 * @param  string  $course_id
 * @return the path of the tool folder if it exist, false if not
 */
function dump_file($data_type,$toolName,$dom,$course_id)
{
	$foo = EXPORT_PATH . '/' . $course_id . '/' . $data_type . '/' . $toolName . '/';
	if (!file_exists($foo)) claro_mkdir($foo,0777,TRUE);

	$result = $dom->dump_file( $foo . $toolName . '.xml', true, false);
	if ($result == 0)
		return claro_failure :: set_failure('cant_write_xml_file');
	return true;
}
?>