<?php


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

define('EXPORT_PATH', 'C:\Program Files\EasyPHP1-8\www\cvs\claroline.test\claroline\export');

/**
 * export data of a tool
 * 
 * @see http://www.claroline.net/wiki/index.php/Plugin_system_modelisation#Module_Class
 * 
 */
function get_tool_path($toolId)
{
	return '';
}
function export_data_tool($toolId,$courseId=null,$groupId=null)
{
	$exportLib      =  get_tool_path($toolId) . '/claroclasses/kernelClassesExtend.inc.php';
	$exportFuncName = array($toolId . '_Module', 'export_content');
	
	if (file_exists($exportLib)) include_once($exportLib);
	if (method_exists($toolId . '_Module','export_content')) call_user_func($exportFuncName,$courseId,$groupId);;
	
	
	switch($toolId)
	{
		case 'CLCAL' : export_data_course_calendar($courseId);     break;		
		case 'CLANN' : export_data_course_announcement($courseId); break;			
		case 'CLQWZ' : export_data_course_quiz($courseId);		         break;			
		case 'CLDOC' : export_data_course_document($courseId, $groupId); break;
		case 'CLLNK' : export_data_course_link($courseId);		 break;
		case 'CLLNP' : export_data_course_lp($courseId);	 break;
		case 'CLWKI' : export_data_course_wiki($courseId);			 break;	
		case 'CLUSR' : export_data_course_userinfo($courseId);		 break;
		case 'CLFRM' : export_data_course_bb($courseId);		 break;
		case 'CLDSC' : export_data_course_description($courseId);		 break;	
		case 'CLWRK' : export_data_course_wrk($courseId);	 break;
	}
	
}

function export_all_data_course_in_file($courseId=null)
{
	
	if (is_null($courseId)) $courseId = $GLOBALS['_cid'];
	if ('' != $courseId)
	{		
		if (false == export_chat_document($courseId))
		{
			if ('document_folder_doesnt_exist' !== claro_failure :: get_last_failure()  )
			{
				return false;
			}
		}					
		if (false == export_modules_document($courseId))
		{
			if ('document_folder_doesnt_exist' !== claro_failure :: get_last_failure() )
			{
				return false;
			}
		}
		
		export_course_metadata($courseId);
		export_course_tool_metadata($courseId);
		export_course_group_metadata($courseId);				
		export_course_user_metadata($courseId);
		
		/*
        // export course tools
        $courseToolList = get_courseToolList($courseId); 
        foreach($courseToolList as $courseTool)
        {
        	export_data_tool($courseTool,$courseId);				
        }
        // export group tools
        $groupList = get_groupList($courseId); 
        foreach($groupList as $group)
        {
	    	$courseToolList = get_courseGroupToolList($courseId,$group); 
            foreach($courseGroupToolList as $courseGroupTool)
	        {
	        	export_data_tool($courseGroupTool,$courseId, $group);				
	        }
        }
		*/
		export_data_tool('CLCAL',$courseId);				
		export_data_tool('CLANN',$courseId);
		export_data_tool('CLQWZ',$courseId);				
		export_data_tool('CLDOC',$courseId);	
		export_data_tool('CLLNK',$courseId);				
		export_data_tool('CLLNP',$courseId);	
		export_data_tool('CLWKI',$courseId);				
		export_data_tool('CLUSR',$courseId);
		export_data_tool('CLFRM',$courseId);				
		export_data_tool('CLDSC',$courseId);
		export_data_tool('CLWRK',$courseId);				
		
		if (false == compress_directory(EXPORT_PATH."/".$courseId))
			return false;
		if (false == claro_delete_file(EXPORT_PATH."/".$courseId))
		{
			return claro_failure::set_failure("can't delete dir");
		}
		

		return true;
	} else return claro_failure :: set_failure("invalid course id");
}
function export_data_course_calendar($_cid)
{
	$tbl = export_data_course_calendar_from_db($_cid);	
	$dom = export_data_course_calendar_in_file($tbl, $_cid);
	if (false !== $dom)
	{		
		dump_file("tools","calendar",$dom,$_cid);
		return true;
	}
	else return false;
}
function export_data_course_announcement($_cid)
{
	$tbl = export_data_course_announcement_from_db($_cid);
	$dom = export_data_course_announcement_in_file($tbl, $_cid);
	if (false !== $dom)
	{
		dump_file("tools","announcement",$dom,$_cid);
		return true;
	}
	else return false;	
}
function export_data_course_quiz($_cid)
{	
	if (false == export_exercise_document($_cid))
	{
		if (claro_failure :: get_last_failure() !== "document_folder_doesnt_exist")
		{
			return false;
		}
	}	
	$tbl = export_data_course_quiz_from_db($_cid);
	$dom = export_data_course_quiz_in_file($tbl, $_cid);
	if (false !== $dom)
	{
		dump_file("tools","quiz",$dom,$_cid);
		return true;
	}
	else return false;	
}
function export_data_course_document($_cid)
{	
	if (false == export_document_document($_cid))
	{
		if (claro_failure :: get_last_failure() !== "document_folder_doesnt_exist")
		{
			return false;
		}
	}
	$tbl = export_data_course_document_from_db($_cid);
	$dom = export_data_course_document_in_file($tbl, $_cid);
	if (false !== $dom)
	{
		dump_file("tools","document",$dom,$_cid);
		return true;
	}
	else return false;
}
function export_data_course_link($_cid)
{
	$tbl = export_data_course_link_from_db($_cid);
	$dom = export_data_course_link_in_file($tbl, $_cid);
	if (false !== $dom)
	{
		dump_file("meta_data","link",$dom,$_cid);
		return true;
	}
	else return false;			
}
function export_data_course_lp($_cid)
{	
	$tbl = export_data_course_lp_from_db($_cid);
	$dom = export_data_course_lp_in_file($tbl, $_cid);
	if (false !== $dom)
	{
		dump_file("tools","lp",$dom,$_cid);
		return true;
	}
	else return false;						
}
function export_course_tool_metadata($_cid)
{
	$tbl = export_course_tool_metadata_from_db($_cid);
	$dom = export_course_tool_metadata_in_file($tbl, $_cid);
	if (false !== $dom)
	{
		dump_file("meta_data","tool",$dom,$_cid);
		return true;
	}
	else return false;			
}
function export_data_course_wiki($_cid)
{
	$tbl = export_data_course_wiki_from_db($_cid);
	$dom = export_data_course_wiki_in_file($tbl, $_cid);
	if (false !== $dom)
	{
		dump_file("tools","wiki",$dom,$_cid);
		return true;
	}
	else return false;		
}
function export_data_course_userinfo($_cid)
{
	$tbl = export_data_course_userinfo_from_db($_cid);
	$dom = export_data_course_userinfo_in_file($tbl, $_cid);
	if (false !== $dom)
	{
		dump_file("meta_data","userinfo",$dom,$_cid);
		return true;
	}
	else return false;	
}
function export_data_course_bb($_cid)
{
	$tbl = export_data_course_bb_from_db($_cid);
	$dom = export_data_course_bb_in_file($tbl, $_cid);
	if (false !== $dom)
	{
		dump_file("tools","forum",$dom,$_cid);
	}
	else return false;			
}
function export_course_metadata($_cid)
{
	$tbl = export_course_data_from_db($_cid);
	$dom = export_data_course_in_file($tbl, $_cid);
	if (false !== $dom)
	{
		dump_file("meta_data","manifest",$dom,$_cid);
		return true;
	}
	else return false;		
}
function export_data_course_description($_cid)
{
	$tbl = export_data_course_description_from_db($_cid);	
	$dom = export_data_course_description_in_file($tbl, $_cid);
	if (false !== $dom)
	{
		dump_file("tools","description",$dom,$_cid);
		return true;
	}			
	else return false;	
}
function export_course_group_metadata($_cid)
{	
	if (false == export_group_document($_cid))
	{
		if (claro_failure :: get_last_failure() !== "document_folder_doesnt_exist")
		{
			return false;
		}
	}
		   
	$tbl = export_course_group_metadata_from_db($_cid);
	$dom = export_course_group_metadata_in_file($tbl, $_cid);
	if (false !== $dom)
	{
		dump_file("tools","group",$dom,$_cid);
		return true;
	}
	else return false;	
}
function export_data_course_wrk($_cid)
{		
	if (false == export_work_document($_cid))
	{
		if (claro_failure :: get_last_failure() !== "document_folder_doesnt_exist")
		{
			return false;
		}
	}	
	$tbl = export_data_course_wrk_from_db($_cid);
	$dom = export_data_course_wrk_in_file($tbl, $_cid);
	if (false !== $dom)
	{
		dump_file("tools","work",$dom,$_cid);
		return true;
	}
	else return false;		
}
function export_course_user_metadata($_cid)
{		
	$tbl['user'] = export_course_user_metadata_from_db($_cid);
	$tbl['rel_course_user'] = export_course_rel_course_user_from_db($_cid);
	$dom = export_course_user_metadata_in_file($tbl, $_cid);
	if (false !== $dom)
	{
		dump_file("meta_data","users",$dom,$_cid);
		return true;
	}
	else return false;	
}
function export_course_user_metadata_in_file($tbl, $_cid)
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
	if (!file_exists(EXPORT_PATH."/".$_cid))
		claro_mkdir(EXPORT_PATH."/".$_cid);

	return $dom;
}
function export_data_course_description_in_file($tbl, $_cid)
{	
	$dom = domxml_new_doc('1.0');
	$course_description = $dom->append_child($dom->create_element("course_description"));

	if (is_array($tbl) && (count($tbl) > 0))
	{
		foreach ($tbl as $tab_content)
		{
			$id = $course_description->append_child($dom->create_element('id'));
			$id->set_attribute("id", $tab_content["id"]);
			$title = $id->append_child($dom->create_element('title'));
			$title->append_child($dom->create_text_node(utf8_encode($tab_content['title'])));
			$content = $id->append_child($dom->create_element('content'));
			$content->append_child($dom->create_text_node(utf8_encode($tab_content['content'])));
			$upDate = $id->append_child($dom->create_element('upDate'));
			$upDate->append_child($dom->create_text_node($tab_content['upDate']));
			$visibility = $id->append_child($dom->create_element('visibility'));
			$visibility->append_child($dom->create_text_node($tab_content['visibility']));
		}
	}
	return $dom;
}
function export_data_course_in_file($tbl, $_cid)
{
	$dom = domxml_new_doc('1.0');
	$course = $dom->append_child($dom->create_element('MANIFEST'));

	if (is_array($tbl) && (count($tbl) > 0))
	{
		foreach ($tbl as $tab_content)
		{
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
			$cours_id = $course->append_child($dom->create_element('cours_id'));
			$cours_id->append_child($dom->create_text_node($tab_content["cours_id"]));
			$code = $course->append_child($dom->create_element('code'));
			$code->append_child($dom->create_text_node(utf8_encode($tab_content["code"])));
			$fake_code = $course->append_child($dom->create_element('fake_code'));
			$fake_code->append_child($dom->create_text_node(utf8_encode($tab_content["fake_code"])));
			$directory = $course->append_child($dom->create_element('directory'));
			$directory->append_child($dom->create_text_node(utf8_encode($tab_content["directory"])));
			$dbName = $course->append_child($dom->create_element('dbName'));
			$dbName->append_child($dom->create_text_node(utf8_encode($tab_content["dbName"])));
			$languageCourse = $course->append_child($dom->create_element('languageCourse'));
			$languageCourse->append_child($dom->create_text_node(utf8_encode($tab_content["languageCourse"])));
			$intitule = $course->append_child($dom->create_element('intitule'));
			$intitule->append_child($dom->create_text_node(utf8_encode($tab_content["intitule"])));
			$faculte = $course->append_child($dom->create_element('faculte'));
			$faculte->append_child($dom->create_text_node(utf8_encode($tab_content["faculte"])));
			$visible = $course->append_child($dom->create_element('visible'));
			$visible->append_child($dom->create_text_node($tab_content["visible"]));
			$enrollment_key = $course->append_child($dom->create_element('enrollment_key'));
			$enrollment_key->append_child($dom->create_text_node(utf8_encode($tab_content["enrollment_key"])));
			$titulaires = $course->append_child($dom->create_element('titulaires'));
			$titulaires->append_child($dom->create_text_node(utf8_encode($tab_content["titulaires"])));
			$email = $course->append_child($dom->create_element('email'));
			$email->append_child($dom->create_text_node(utf8_encode($tab_content["email"])));
			$departmentUrlName = $course->append_child($dom->create_element('departmentUrlName'));
			$departmentUrlName->append_child($dom->create_text_node(utf8_encode($tab_content["departmentUrlName"])));
			$departmentUrl = $course->append_child($dom->create_element('departmentUrl'));
			$departmentUrl->append_child($dom->create_text_node(utf8_encode($tab_content["departmentUrl"])));
			$diskQuota = $course->append_child($dom->create_element('diskQuota'));
			$diskQuota->append_child($dom->create_text_node($tab_content["diskQuota"]));
			$versionDb = $course->append_child($dom->create_element('versionDb'));
			$versionDb->append_child($dom->create_text_node(utf8_encode($tab_content["versionDb"])));
			$versionClaro = $course->append_child($dom->create_element('versionClaro'));
			$versionClaro->append_child($dom->create_text_node(utf8_encode($tab_content["versionClaro"])));
			$lastVisit = $course->append_child($dom->create_element('lastVisit'));
			$lastVisit->append_child($dom->create_text_node($tab_content["lastVisit"]));
			$lastEdit = $course->append_child($dom->create_element('lastEdit'));
			$lastEdit->append_child($dom->create_text_node($tab_content["lastEdit"]));
			$creationDate = $course->append_child($dom->create_element('creationDate'));
			$creationDate->append_child($dom->create_text_node($tab_content["creationDate"]));
			$expirationDate = $course->append_child($dom->create_element('expirationDate'));
			$expirationDate->append_child($dom->create_text_node($tab_content["expirationDate"]));

		}
	}
	return $dom;
}
function export_data_course_bb_from_db($_cid)
{
	$tab = array ();
	$tab["categories"] = export_data_course_bb_categories($_cid);
	$tab["forums"] = export_data_course_bb_forums($_cid);
	$tab["posts"] = export_data_course_bb_posts($_cid);
	$tab["posts_text"] = export_data_course_bb_posts_text($_cid);
	$tab["priv_msgs"] = export_data_course_bb_priv_msgs($_cid);
	$tab["rel_topic_userstonotify"] = export_data_course_bb_rel_topic_userstonotify($_cid);
	$tab["topics"] = export_data_course_bb_topics($_cid);
	$tab["users"] = export_data_course_bb_users($_cid);

	return $tab;

}
function export_data_course_bb_in_file($tbl, $_cid)
{
	$dom = domxml_new_doc('1.0');
	$bb = $dom->append_child($dom->create_element('bb'));

	$bb_categories = $bb->append_child($dom->create_element('bb_categories'));
	if (isset ($tbl["categories"]) && is_array($tbl["categories"]) && (count($tbl["categories"]) > 0))
	{
		foreach ($tbl["categories"] as $tab_content)
		{
			$cat_id = $bb_categories->append_child($dom->create_element('cat_id'));
			$cat_id->set_attribute('cat_id', $tab_content['cat_id']);
			$cat_title = $cat_id->append_child($dom->create_element('cat_title'));
			$cat_title->append_child($dom->create_text_node(utf8_encode($tab_content["cat_title"])));
			$cat_order = $cat_id->append_child($dom->create_element('cat_order'));
			$cat_order->append_child($dom->create_text_node($tab_content["cat_order"]));
		}
	}

	$bb_forums = $bb->append_child($dom->create_element('bb_forums'));
	if (isset ($tbl["forums"]) && is_array($tbl["forums"]) && (count($tbl["forums"]) > 0))
	{
		foreach ($tbl["forums"] as $tab_content)
		{
			$forum_id = $bb_forums->append_child($dom->create_element('forum_id'));
			$forum_id->set_attribute('forum_id', $tab_content['forum_id']);
			$group_id = $forum_id->append_child($dom->create_element('group_id'));
			$group_id->append_child($dom->create_text_node($tab_content["group_id"]));
			$forum_name = $forum_id->append_child($dom->create_element('forum_name'));
			$forum_name->append_child($dom->create_text_node(utf8_encode($tab_content["forum_name"])));
			$forum_desc = $forum_id->append_child($dom->create_element('forum_desc'));
			$forum_desc->append_child($dom->create_text_node(utf8_encode($tab_content["forum_desc"])));
			$forum_access = $forum_id->append_child($dom->create_element('forum_access'));
			$forum_access->append_child($dom->create_text_node($tab_content["forum_access"]));
			$forum_moderator = $forum_id->append_child($dom->create_element('forum_moderator'));
			$forum_moderator->append_child($dom->create_text_node($tab_content["forum_moderator"]));
			$forum_topics = $forum_id->append_child($dom->create_element('forum_topics'));
			$forum_topics->append_child($dom->create_text_node($tab_content["forum_topics"]));
			$forum_posts = $forum_id->append_child($dom->create_element('forum_posts'));
			$forum_posts->append_child($dom->create_text_node($tab_content["forum_posts"]));
			$forum_last_post_id = $forum_id->append_child($dom->create_element('forum_last_post_id'));
			$forum_last_post_id->append_child($dom->create_text_node($tab_content["forum_last_post_id"]));
			$cat_id = $forum_id->append_child($dom->create_element('cat_id'));
			$cat_id->append_child($dom->create_text_node($tab_content["cat_id"]));
			$forum_type = $forum_id->append_child($dom->create_element('forum_type'));
			$forum_type->append_child($dom->create_text_node($tab_content["forum_type"]));
			$forum_order = $forum_id->append_child($dom->create_element('forum_order'));
			$forum_order->append_child($dom->create_text_node($tab_content["forum_order"]));
		}
	}

	$bb_posts = $bb->append_child($dom->create_element('bb_posts'));
	if (isset ($tbl["posts"]) && is_array($tbl["posts"]) && (count($tbl["posts"]) > 0))
	{
		foreach ($tbl["posts"] as $tab_content)
		{
			$post_id = $bb_posts->append_child($dom->create_element('post_id'));
			$post_id->set_attribute('post_id', $tab_content['post_id']);
			$topic_id = $post_id->append_child($dom->create_element('topic_id'));
			$topic_id->append_child($dom->create_text_node($tab_content["topic_id"]));
			$forum_id = $post_id->append_child($dom->create_element('forum_id'));
			$forum_id->append_child($dom->create_text_node($tab_content["forum_id"]));
			$poster_id = $post_id->append_child($dom->create_element('poster_id'));
			$poster_id->append_child($dom->create_text_node($tab_content["poster_id"]));
			$post_time = $post_id->append_child($dom->create_element('post_time'));
			$post_time->append_child($dom->create_text_node(utf8_encode($tab_content["post_time"])));
			$poster_ip = $post_id->append_child($dom->create_element('poster_ip'));
			$poster_ip->append_child($dom->create_text_node(utf8_encode($tab_content["poster_ip"])));
			$firstname = $post_id->append_child($dom->create_element('firstname'));
			$firstname->append_child($dom->create_text_node(utf8_encode($tab_content["firstname"])));
			$lastname = $post_id->append_child($dom->create_element('lastname'));
			$lastname->append_child($dom->create_text_node(utf8_encode($tab_content["lastname"])));
		}

		$bb_posts_text = $bb->append_child($dom->create_element('bb_posts_text'));
		if (isset ($tbl["posts_text"]) && is_array($tbl["posts_text"]) && (count($tbl["posts_text"]) > 0))
		{
			foreach ($tbl["posts_text"] as $tab_content)
			{
				$post_id = $bb_posts_text->append_child($dom->create_element('post_id'));
				$post_id->set_attribute('post_id', $tab_content['post_id']);
				$post_text = $post_id->append_child($dom->create_element('post_text'));
				$post_text->append_child($dom->create_text_node(utf8_encode($tab_content["post_text"])));
			}
		}

		$bb_priv_msgs = $bb->append_child($dom->create_element('bb_priv_msgs'));
		if (isset ($tbl["priv_msgs"]) && is_array($tbl["priv_msgs"]) && (count($tbl["priv_msgs"]) > 0))
		{
			foreach ($tbl["priv_msgs"] as $tab_content)
			{
				$msg_id = $bb_priv_msgs->append_child($dom->create_element('msg_id'));
				$msg_id->set_attribute('msg_id', $tab_content['msg_id']);
				$from_userid = $msg_id->append_child($dom->create_element('from_userid'));
				$from_userid->append_child($dom->create_text_node($tab_content["from_userid"]));
				$to_userid = $msg_id->append_child($dom->create_element('to_userid'));
				$to_userid->append_child($dom->create_text_node($tab_content["to_userid"]));
				$msg_time = $msg_id->append_child($dom->create_element('msg_time'));
				$msg_time->append_child($dom->create_text_node($tab_content["msg_time"]));
				$poster_ip = $msg_id->append_child($dom->create_element('poster_ip'));
				$poster_ip->append_child($dom->create_text_node($tab_content["poster_ip"]));
				$msg_status = $msg_id->append_child($dom->create_element('msg_status'));
				$msg_status->append_child($dom->create_text_node($tab_content["msg_status"]));
				$msg_text = $msg_id->append_child($dom->create_element('msg_text'));
				$msg_text->append_child($dom->create_text_node(utf8_encode($tab_content["msg_text"])));
			}
		}

		$bb_rel_topic_userstonotify = $bb->append_child($dom->create_element('bb_rel_topic_userstonotify'));
		if (isset ($tbl["rel_topic_userstonotify"]) && is_array($tbl["rel_topic_userstonotify"]) && (count($tbl["rel_topic_userstonotify"]) > 0))
			foreach ($tbl["rel_topic_userstonotify"] as $tab_content)
			{
				$notify_id = $bb_rel_topic_userstonotify->append_child($dom->create_element('notify_id'));
				$notify_id->set_attribute('notify_id', $tab_content['notify_id']);
				$user_id = $notify_id->append_child($dom->create_element('user_id'));
				$user_id->append_child($dom->create_text_node($tab_content["user_id"]));
				$topic_id = $notify_id->append_child($dom->create_element('topic_id'));
				$topic_id->append_child($dom->create_text_node($tab_content["topic_id"]));
			}
	}

	$bb_topics = $bb->append_child($dom->create_element('bb_topics'));
	if (isset ($tbl["topics"]) && is_array($tbl["topics"]) && (count($tbl["topics"]) > 0))
	{
		foreach ($tbl["topics"] as $tab_content)
		{
			$topic_id = $bb_topics->append_child($dom->create_element('topic_id'));
			$topic_id->set_attribute('topic_id', $tab_content['topic_id']);
			$topic_title = $topic_id->append_child($dom->create_element('topic_title'));
			$topic_title->append_child($dom->create_text_node(utf8_encode($tab_content["topic_title"])));
			$topic_poster = $topic_id->append_child($dom->create_element('topic_poster'));
			$topic_poster->append_child($dom->create_text_node($tab_content["topic_poster"]));
			$topic_time = $topic_id->append_child($dom->create_element('topic_time'));
			$topic_time->append_child($dom->create_text_node(utf8_encode($tab_content["topic_time"])));
			$topic_views = $topic_id->append_child($dom->create_element('topic_views'));
			$topic_views->append_child($dom->create_text_node($tab_content["topic_views"]));
			$topic_replies = $topic_id->append_child($dom->create_element('topic_replies'));
			$topic_replies->append_child($dom->create_text_node($tab_content["topic_replies"]));
			$topic_last_post_id = $topic_id->append_child($dom->create_element('topic_last_post_id'));
			$topic_last_post_id->append_child($dom->create_text_node($tab_content["topic_last_post_id"]));
			$forum_id = $topic_id->append_child($dom->create_element('forum_id'));
			$forum_id->append_child($dom->create_text_node($tab_content["forum_id"]));
			$topic_status = $topic_id->append_child($dom->create_element('topic_status'));
			$topic_status->append_child($dom->create_text_node($tab_content["topic_status"]));
			$topic_notify = $topic_id->append_child($dom->create_element('topic_notify'));
			$topic_notify->append_child($dom->create_text_node($tab_content["topic_notify"]));
			$firstname = $topic_id->append_child($dom->create_element('firstname'));
			$firstname->append_child($dom->create_text_node(utf8_encode($tab_content["firstname"])));
			$lastname = $topic_id->append_child($dom->create_element('lastname'));
			$lastname->append_child($dom->create_text_node(utf8_encode($tab_content["lastname"])));
		}
	}

	$bb_users = $bb->append_child($dom->create_element('bb_users'));
	if (isset ($tbl["users"]) && is_array($tbl["users"]) && (count($tbl["users"]) > 0))
	{
		foreach ($tbl["users"] as $tab_content)
		{
			$user_id = $bb_users->append_child($dom->create_element('user_id'));
			$user_id->set_attribute('user_id', $tab_content['user_id']);
			$username = $user_id->append_child($dom->create_element('username'));
			$username->append_child($dom->create_text_node(utf8_encode($tab_content["username"])));
			$user_regdate = $user_id->append_child($dom->create_element('user_regdate'));
			$user_regdate->append_child($dom->create_text_node(utf8_encode($tab_content["user_regdate"])));
			$user_password = $user_id->append_child($dom->create_element('user_password'));
			$user_password->append_child($dom->create_text_node(utf8_encode($tab_content["user_password"])));
			$user_email = $user_id->append_child($dom->create_element('user_email'));
			$user_email->append_child($dom->create_text_node(utf8_encode($tab_content["user_email"])));
			$user_icq = $user_id->append_child($dom->create_element('user_icq'));
			$user_icq->append_child($dom->create_text_node(utf8_encode($tab_content["user_icq"])));
			$user_website = $user_id->append_child($dom->create_element('user_website'));
			$user_website->append_child($dom->create_text_node(utf8_encode($tab_content["user_website"])));
			$user_occ = $user_id->append_child($dom->create_element('user_occ'));
			$user_occ->append_child($dom->create_text_node(utf8_encode($tab_content["user_occ"])));
			$user_from = $user_id->append_child($dom->create_element('user_from'));
			$user_from->append_child($dom->create_text_node(utf8_encode($tab_content["user_from"])));
			$user_intrest = $user_id->append_child($dom->create_element('user_intrest'));
			$user_intrest->append_child($dom->create_text_node(utf8_encode($tab_content["user_intrest"])));
			$user_sig = $user_id->append_child($dom->create_element('user_sig'));
			$user_sig->append_child($dom->create_text_node(utf8_encode($tab_content["user_sig"])));
			$user_viewemail = $user_id->append_child($dom->create_element('user_viewemail'));
			$user_viewemail->append_child($dom->create_text_node($tab_content["user_viewemail"]));
			$user_theme = $user_id->append_child($dom->create_element('user_theme'));
			$user_theme->append_child($dom->create_text_node($tab_content["user_theme"]));
			$user_aim = $user_id->append_child($dom->create_element('user_aim'));
			$user_aim->append_child($dom->create_text_node(utf8_encode($tab_content["user_aim"])));
			$user_yim = $user_id->append_child($dom->create_element('user_yim'));
			$user_yim->append_child($dom->create_text_node(utf8_encode($tab_content["user_yim"])));
			$user_msnm = $user_id->append_child($dom->create_element('user_msnm'));
			$user_msnm->append_child($dom->create_text_node(utf8_encode($tab_content["user_msnm"])));
			$user_posts = $user_id->append_child($dom->create_element('user_posts'));
			$user_posts->append_child($dom->create_text_node($tab_content["user_posts"]));
			$user_attachsig = $user_id->append_child($dom->create_element('user_attachsig'));
			$user_attachsig->append_child($dom->create_text_node($tab_content["user_attachsig"]));
			$user_desmile = $user_id->append_child($dom->create_element('user_desmile'));
			$user_desmile->append_child($dom->create_text_node($tab_content["user_desmile"]));
			$user_html = $user_id->append_child($dom->create_element('user_html'));
			$user_html->append_child($dom->create_text_node($tab_content["user_html"]));
			$user_bbcode = $user_id->append_child($dom->create_element('user_bbcode'));
			$user_bbcode->append_child($dom->create_text_node($tab_content["user_bbcode"]));
			$user_rank = $user_id->append_child($dom->create_element('user_rank'));
			$user_rank->append_child($dom->create_text_node($tab_content["user_rank"]));
			$user_level = $user_id->append_child($dom->create_element('user_level'));
			$user_level->append_child($dom->create_text_node($tab_content["user_level"]));
			$user_lang = $user_id->append_child($dom->create_element('user_lang'));
			$user_lang->append_child($dom->create_text_node(utf8_encode($tab_content["user_lang"])));
			$user_actkey = $user_id->append_child($dom->create_element('user_actkey'));
			$user_actkey->append_child($dom->create_text_node(utf8_encode($tab_content["user_actkey"])));
			$user_newpasswd = $user_id->append_child($dom->create_element('user_newpasswd'));
			$user_newpasswd->append_child($dom->create_text_node(utf8_encode($tab_content["user_newpasswd"])));
		}
	}
	return $dom;
}
function export_data_course_userinfo_from_db($_cid)
{
	$tab = array ();
	$tab["def"] = export_data_course_userinfo_def($_cid);
	$tab["content"] = export_data_course_userinfo_content($_cid);

	return $tab;
}
function export_data_course_userinfo_in_file($tbl, $_cid)
{

	$dom = domxml_new_doc('1.0');
	$userinfo = $dom->append_child($dom->create_element('userinfo'));

	$userinfo_def = $userinfo->append_child($dom->create_element('userinfo_def'));
	if (isset ($tbl["def"]) && is_array($tbl["def"]) && (count($tbl["def"]) > 0))
	{
		foreach ($tbl["def"] as $tab_content)
		{
			$id = $userinfo_def->append_child($dom->create_element('id'));
			$id->set_attribute('id', $tab_content['id']);
			$title = $id->append_child($dom->create_element('title'));
			$title->append_child($dom->create_text_node(utf8_encode($tab_content['title'])));
			$comment = $id->append_child($dom->create_element('comment'));
			$comment->append_child($dom->create_text_node(utf8_encode($tab_content["comment"])));
			$nbLine = $id->append_child($dom->create_element('nbLine'));
			$nbLine->append_child($dom->create_text_node($tab_content["nbLine"]));
			$rank = $id->append_child($dom->create_element('rank'));
			$rank->append_child($dom->create_text_node($tab_content["rank"]));
		}
	}
	$userinfo_content = $userinfo->append_child($dom->create_element('userinfo_content'));
	if (isset ($tbl["content"]) && is_array($tbl["content"]) && (count($tbl["content"]) > 0))
	{
		foreach ($tbl["content"] as $tab_content)
		{
			$id = $userinfo_content->append_child($dom->create_element('id'));
			$id->set_attribute('id', $tab_content['id']);
			$user_id = $id->append_child($dom->create_element('user_id'));
			$user_id->append_child($dom->create_text_node($tab_content['user_id']));
			$def_id = $id->append_child($dom->create_element('def_id'));
			$def_id->append_child($dom->create_text_node($tab_content['def_id']));
			$ed_ip = $id->append_child($dom->create_element('ed_ip'));
			$ed_ip->append_child($dom->create_text_node($tab_content['ed_ip']));
			$ed_date = $id->append_child($dom->create_element('ed_date'));
			$ed_date->append_child($dom->create_text_node($tab_content['ed_date']));
			$content = $id->append_child($dom->create_element('content'));
			$content->append_child($dom->create_text_node(utf8_encode($tab_content['content'])));
		}
	}
	return $dom;
}

function export_data_course_wiki_from_db($_cid)
{
	$tab = array ();
	$tab["acls"] = export_data_course_wiki_acls($_cid);
	$tab["pages"] = export_data_course_wiki_pages($_cid);
	$tab["pages_content"] = export_data_course_wiki_pages_content($_cid);
	$tab["properties"] = export_data_course_wiki_properties($_cid);

	return $tab;
}
function export_data_course_wiki_in_file($tbl, $_cid)
{
	$dom = domxml_new_doc('1.0');
	$wiki = $dom->append_child($dom->create_element('wiki'));

	$wiki_acls = $wiki->append_child($dom->create_element('wiki_acls'));
	if (isset ($tbl["acls"]) && is_array($tbl["acls"]) && (count($tbl["acls"]) > 0))
	{
		foreach ($tbl["acls"] as $tab_content)
		{
			$wiki_id = $wiki_acls->append_child($dom->create_element('wiki_id'));
			$wiki_id->set_attribute('wiki_id', $tab_content['wiki_id']);
			$flag = $wiki_id->append_child($dom->create_element('flag'));
			$flag->append_child($dom->create_text_node($tab_content['flag']));
			$value = $wiki_id->append_child($dom->create_element('value'));
			$value->append_child($dom->create_text_node($tab_content['value']));
		}
	}
	$wiki_pages = $wiki->append_child($dom->create_element('wiki_pages'));
	if (isset ($tbl["pages"]) && is_array($tbl["pages"]) && (count($tbl["pages"]) > 0))
	{
		foreach ($tbl["pages"] as $tab_content)
		{
			$id = $wiki_pages->append_child($dom->create_element('id'));
			$id->set_attribute("id", $tab_content["id"]);
			$wiki_id = $id->append_child($dom->create_element('wiki_id'));
			$wiki_id->append_child($dom->create_text_node($tab_content["wiki_id"]));
			$owner_id = $id->append_child($dom->create_element('owner_id'));
			$owner_id->append_child($dom->create_text_node($tab_content["owner_id"]));
			$title = $id->append_child($dom->create_element('title'));
			$title->append_child($dom->create_text_node(utf8_encode($tab_content["title"])));
			$ctime = $id->append_child($dom->create_element('ctime'));
			$ctime->append_child($dom->create_text_node($tab_content["ctime"]));
			$last_version = $id->append_child($dom->create_element('last_version'));
			$last_version->append_child($dom->create_text_node($tab_content["last_version"]));
			$last_mtime = $id->append_child($dom->create_element('last_mtime'));
			$last_mtime->append_child($dom->create_text_node($tab_content["last_mtime"]));
		}
	}

	$wiki_pages_content = $wiki->append_child($dom->create_element('wiki_pages_content'));
	if (isset ($tbl["pages_content"]) && is_array($tbl["pages_content"]) && (count($tbl["pages_content"]) > 0))
	{
		foreach ($tbl["pages_content"] as $tab_content)
		{
			$id = $wiki_pages_content->append_child($dom->create_element('id'));
			$id->set_attribute("id", $tab_content["id"]);
			$pid = $id->append_child($dom->create_element('pid'));
			$pid->append_child($dom->create_text_node($tab_content["pid"]));
			$editor_id = $id->append_child($dom->create_element('editor_id'));
			$editor_id->append_child($dom->create_text_node($tab_content["editor_id"]));
			$mtime = $id->append_child($dom->create_element('mtime'));
			$mtime->append_child($dom->create_text_node($tab_content["mtime"]));
			$content = $id->append_child($dom->create_element('content'));
			$content->append_child($dom->create_text_node(utf8_encode($tab_content["content"])));
		}
	}
	$wiki_properties = $wiki->append_child($dom->create_element('wiki_properties'));
	if (isset ($tbl["properties"]) && is_array($tbl["properties"]) && (count($tbl["properties"]) > 0))
	{
		foreach ($tbl["properties"] as $tab_content)
		{
			$id = $wiki_properties->append_child($dom->create_element('id'));
			$id->set_attribute("id", $tab_content["id"]);
			$title = $id->append_child($dom->create_element('title'));
			$title->append_child($dom->create_text_node(utf8_encode($tab_content["title"])));
			$description = $id->append_child($dom->create_element('description'));
			$description->append_child($dom->create_text_node(utf8_encode($tab_content["description"])));
			$group_id = $id->append_child($dom->create_element('group_id'));
			$group_id->append_child($dom->create_text_node($tab_content["group_id"]));
		}
	}
	return $dom;
}
function export_course_tool_metadata_from_db($_cid)
{
	$tab = array ();
	$tab["list"] = export_course_tool_metadata_list($_cid);
	$tab["intro"] = export_course_tool_metadata_intro($_cid);

	return $tab;
}
function export_course_tool_metadata_in_file($tbl, $_cid)
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
function export_data_course_announcement_in_file($tbl, $_cid)
{
	$dom = domxml_new_doc('1.0');
	$announcement = $dom->append_child($dom->create_element('announcement'));
	if (is_array($tbl) && (count($tbl) > 0))
	{
		foreach ($tbl as $tab_content)
		{
			$id = $announcement->append_child($dom->create_element('id'));
			$id->set_attribute("id", $tab_content["id"]);
			$title = $id->append_child($dom->create_element('title'));
			$title->append_child($dom->create_text_node(utf8_encode($tab_content["title"])));
			$content = $id->append_child($dom->create_element('content'));
			$content->append_child($dom->create_text_node($tab_content["content"]));
			$time = $id->append_child($dom->create_element('time'));
			$time->append_child($dom->create_text_node($tab_content["time"]));
			$rank = $id->append_child($dom->create_element('rank'));
			$rank->append_child($dom->create_text_node($tab_content["rank"]));
			$visibility = $id->append_child($dom->create_element('visibility'));
			$visibility->append_child($dom->create_text_node($tab_content["visibility"]));
			
		}
	}
	return $dom;	
}
function export_data_course_calendar_in_file($tbl, $_cid)
{
	$dom = domxml_new_doc('1.0');
	$calendar = $dom->append_child($dom->create_element('calendar'));

	if (is_array($tbl) && (count($tbl) > 0))
	{
		foreach ($tbl as $tab_content)
		{
			$id = $calendar->append_child($dom->create_element('id'));
			$id->set_attribute("id", $tab_content["id"]);
			$title = $id->append_child($dom->create_element('title'));
			$title->append_child($dom->create_text_node(utf8_encode($tab_content["title"])));
			$content = $id->append_child($dom->create_element('content'));
			$content->append_child($dom->create_text_node(utf8_encode($tab_content["content"])));
			$day = $id->append_child($dom->create_element('day'));
			$day->append_child($dom->create_text_node($tab_content["day"]));
			$hour = $id->append_child($dom->create_element('hour'));
			$hour->append_child($dom->create_text_node($tab_content["hour"]));
			$lasting = $id->append_child($dom->create_element('lasting'));
			$lasting->append_child($dom->create_text_node(utf8_encode($tab_content["lasting"])));
			$visibility = $id->append_child($dom->create_element('visibility'));
			$visibility->append_child($dom->create_text_node($tab_content["visibility"]));
		}
	}
	return $dom;
}
function export_data_course_wrk_from_db($_cid)
{
	$tab = array ();
	$tab["assignment"] = export_data_course_wrk_assignment($_cid);
	$tab["submission"] = export_data_course_wrk_submission($_cid);

	return $tab;
}
function export_data_course_wrk_in_file($tbl, $_cid)
{
	$dom = domxml_new_doc('1.0');
	$wrk = $dom->append_child($dom->create_element('work'));

	$assignment = $wrk->append_child($dom->create_element('assignment'));
	if (isset ($tbl["assignment"]) && is_array($tbl["assignment"]) && (count($tbl["assignment"]) > 0))
	{
		foreach ($tbl["assignment"] as $tab_content)
		{
			$id = $assignment->append_child($dom->create_element('id'));
			$id->set_attribute("id", $tab_content["id"]);
			$title = $id->append_child($dom->create_element('title'));
			$title->append_child($dom->create_text_node(utf8_encode($tab_content["title"])));
			$description = $id->append_child($dom->create_element('description'));
			$description->append_child($dom->create_text_node(utf8_encode($tab_content["description"])));
			$visibility = $id->append_child($dom->create_element('visibility'));
			$visibility->append_child($dom->create_text_node($tab_content["visibility"]));
			$def_submission_visibility = $id->append_child($dom->create_element('def_submission_visibility'));
			$def_submission_visibility->append_child($dom->create_text_node($tab_content["def_submission_visibility"]));
			$assignment_type = $id->append_child($dom->create_element('assignment_type'));
			$assignment_type->append_child($dom->create_text_node($tab_content["assignment_type"]));
			$authorized_content = $id->append_child($dom->create_element('authorized_content'));
			$authorized_content->append_child($dom->create_text_node($tab_content["authorized_content"]));
			$allow_late_upload = $id->append_child($dom->create_element('allow_late_upload'));
			$allow_late_upload->append_child($dom->create_text_node($tab_content["allow_late_upload"]));
			$start_date = $id->append_child($dom->create_element('start_date'));
			$start_date->append_child($dom->create_text_node($tab_content["start_date"]));
			$end_date = $id->append_child($dom->create_element('end_date'));
			$end_date->append_child($dom->create_text_node($tab_content["end_date"]));
			$prefill_text = $id->append_child($dom->create_element('prefill_text'));
			$prefill_text->append_child($dom->create_text_node($tab_content["prefill_text"]));
			$prefill_doc_path = $id->append_child($dom->create_element('prefill_doc_path'));
			$prefill_doc_path->append_child($dom->create_text_node(utf8_encode($tab_content["prefill_doc_path"])));
			$prefill_submit = $id->append_child($dom->create_element('prefill_submit'));
			$prefill_submit->append_child($dom->create_text_node($tab_content["prefill_submit"]));
		}
	}
	$submission = $wrk->append_child($dom->create_element('submission'));

	if (isset ($tbl["submission"]) && is_array($tbl["submission"]) && (count($tbl["submission"]) > 0))
	{
		foreach ($tbl["submission"] as $tab_content)
		{
			$id = $submission->append_child($dom->create_element('id'));
			$id->set_attribute("id", $tab_content["id"]);
			$assignment_id = $id->append_child($dom->create_element('assignment_id'));
			$assignment_id->append_child($dom->create_text_node($tab_content["assignment_id"]));
			$parent_id = $id->append_child($dom->create_element('parent_id'));
			$parent_id->append_child($dom->create_text_node($tab_content["parent_id"]));
			$user_id = $id->append_child($dom->create_element('user_id'));
			$user_id->append_child($dom->create_text_node($tab_content["user_id"]));
			$group_id = $id->append_child($dom->create_element('group_id'));
			$group_id->append_child($dom->create_text_node($tab_content["group_id"]));
			$title = $id->append_child($dom->create_element('title'));
			$title->append_child($dom->create_text_node(utf8_encode($tab_content["title"])));
			$visibility = $id->append_child($dom->create_element('visibility'));
			$visibility->append_child($dom->create_text_node($tab_content["visibility"]));
			$creation_date = $id->append_child($dom->create_element('creation_date'));
			$creation_date->append_child($dom->create_text_node($tab_content["creation_date"]));
			$last_edit_date = $id->append_child($dom->create_element('last_edit_date'));
			$last_edit_date->append_child($dom->create_text_node($tab_content["last_edit_date"]));
			$authors = $id->append_child($dom->create_element('authors'));
			$authors->append_child($dom->create_text_node(utf8_encode($tab_content["authors"])));
			$submitted_text = $id->append_child($dom->create_element('submitted_text'));
			$submitted_text->append_child($dom->create_text_node(utf8_encode($tab_content["submitted_text"])));
			$submitted_doc_path = $id->append_child($dom->create_element('submitted_doc_path'));
			$submitted_doc_path->append_child($dom->create_text_node(utf8_encode($tab_content["submitted_doc_path"])));
			$private_feedback = $id->append_child($dom->create_element('private_feedback'));
			$private_feedback->append_child($dom->create_text_node(utf8_encode($tab_content["private_feedback"])));
			$original_id = $id->append_child($dom->create_element('original_id'));
			$original_id->append_child($dom->create_text_node($tab_content["original_id"]));
			$score = $id->append_child($dom->create_element('score'));
			$score->append_child($dom->create_text_node($tab_content["score"]));
		}
	}
	return $dom;
}
function export_data_course_quiz_from_db($_cid)
{
	$tab = array ();
	$tab["answer"] = export_data_course_quiz_answer($_cid);
	$tab["question"] = export_data_course_quiz_question($_cid);
	$tab["rel_test_question"] = export_data_course_quiz_rel_test_question($_cid);
	$tab["test"] = export_data_course_quiz_test($_cid);

	return $tab;
}
function export_data_course_quiz_in_file($tbl, $_cid)
{
	$dom = domxml_new_doc('1.0');
	$quiz = $dom->append_child($dom->create_element('quiz'));
	$answer = $quiz->append_child($dom->create_element('answer'));
	if (isset ($tbl["answer"]) && is_array($tbl["answer"]) && (count($tbl["answer"]) > 0))
	{
		foreach ($tbl["answer"] as $tab_content)
		{
			$id = $answer->append_child($dom->create_element('id'));
			$id->set_attribute("id", $tab_content["id"]);
			$question_id = $id->append_child($dom->create_element('question_id'));
			$question_id->append_child($dom->create_text_node($tab_content["question_id"]));
			$reponse = $id->append_child($dom->create_element('reponse'));
			$reponse->append_child($dom->create_text_node(utf8_encode($tab_content["reponse"])));
			$correct = $id->append_child($dom->create_element('correct'));
			$correct->append_child($dom->create_text_node($tab_content["correct"]));
			$comment = $id->append_child($dom->create_element('comment'));
			$comment->append_child($dom->create_text_node(utf8_encode($tab_content["comment"])));
			$ponderation = $id->append_child($dom->create_element('ponderation'));
			$ponderation->append_child($dom->create_text_node($tab_content["ponderation"]));
			$r_position = $id->append_child($dom->create_element('r_position'));
			$r_position->append_child($dom->create_text_node($tab_content["r_position"]));
		}
	}

	$question = $quiz->append_child($dom->create_element('question'));

	if (isset ($tbl["question"]) && is_array($tbl["question"]) && (count($tbl["question"]) > 0))
	{
		foreach ($tbl["question"] as $tab_content)
		{
			$id = $question->append_child($dom->create_element('id'));
			$id->set_attribute("id", $tab_content["id"]);
			$question = $id->append_child($dom->create_element('question'));
			$question->append_child($dom->create_text_node(utf8_encode($tab_content["question"])));
			$description = $id->append_child($dom->create_element('description'));
			$description->append_child($dom->create_text_node(utf8_encode($tab_content["description"])));
			$ponderation = $id->append_child($dom->create_element('ponderation'));
			$ponderation->append_child($dom->create_text_node($tab_content["ponderation"]));
			$q_position = $id->append_child($dom->create_element('q_position'));
			$q_position->append_child($dom->create_text_node($tab_content["q_position"]));
			$type = $id->append_child($dom->create_element('type'));
			$type->append_child($dom->create_text_node($tab_content["type"]));
			$attached_file = $id->append_child($dom->create_element('attached_file'));
			$attached_file->append_child($dom->create_text_node(utf8_encode($tab_content["attached_file"])));
		}
	}

	$rel_test_question = $quiz->append_child($dom->create_element('rel_test_question'));

	if (isset ($tbl["rel_test_question"]) && is_array($tbl["rel_test_question"]) && (count($tbl["rel_test_question"]) > 0))
	{
		foreach ($tbl["rel_test_question"] as $tab_content)
		{
			$question_id = $rel_test_question->append_child($dom->create_element('question_id'));
			$question_id->append_child($dom->create_text_node($tab_content["question_id"]));
			$exercice_id = $rel_test_question->append_child($dom->create_element('exercice_id'));
			$exercice_id->append_child($dom->create_text_node($tab_content["exercice_id"]));
		}
	}

	$test = $quiz->append_child($dom->create_element('test'));

	if (isset ($tbl["test"]) && is_array($tbl["test"]) && (count($tbl["test"]) > 0))
	{
		foreach ($tbl["test"] as $tab_content)
		{
			$id = $test->append_child($dom->create_element('id'));
			$id->set_attribute("id", $tab_content["id"]);
			$title = $id->append_child($dom->create_element('title'));
			$title->append_child($dom->create_text_node(utf8_encode($tab_content["title"])));
			$description = $id->append_child($dom->create_element('description'));
			$description->append_child($dom->create_text_node(utf8_encode($tab_content["description"])));
			$type = $id->append_child($dom->create_element('type'));
			$type->append_child($dom->create_text_node($tab_content["type"]));
			$random = $id->append_child($dom->create_element('random'));
			$random->append_child($dom->create_text_node($tab_content["random"]));
			$active = $id->append_child($dom->create_element('active'));
			$active->append_child($dom->create_text_node($tab_content["active"]));
			$max_time = $id->append_child($dom->create_element('max_time'));
			$max_time->append_child($dom->create_text_node($tab_content["max_time"]));
			$max_attempt = $id->append_child($dom->create_element('max_attempt'));
			$max_attempt->append_child($dom->create_text_node($tab_content["max_attempt"]));
			$show_answer = $id->append_child($dom->create_element('show_answer'));
			$show_answer->append_child($dom->create_text_node($tab_content["show_answer"]));
			$anonymous_attempts = $id->append_child($dom->create_element('anonymous_attempts'));
			$anonymous_attempts->append_child($dom->create_text_node($tab_content["anonymous_attempts"]));
			$stat_date = $id->append_child($dom->create_element('start_date'));
			$stat_date->append_child($dom->create_text_node($tab_content["start_date"]));
			$end_date = $id->append_child($dom->create_element('end_date'));
			$end_date->append_child($dom->create_text_node($tab_content["end_date"]));
		}
	}
	return $dom;
}
function export_data_course_document_in_file($tbl, $_cid)
{
	$dom = domxml_new_doc('1.0');
	$document = $dom->append_child($dom->create_element('document'));
	if (is_array($tbl) && (count($tbl) > 0))
	{
		foreach ($tbl as $tab_content)
		{
			$id = $document->append_child($dom->create_element('id'));
			$id->set_attribute("id", $tab_content["id"]);
			$path = $id->append_child($dom->create_element('path'));
			$path->append_child($dom->create_text_node(utf8_encode($tab_content["path"])));
			$visibility = $id->append_child($dom->create_element('visibility'));
			$visibility->append_child($dom->create_text_node($tab_content["visibility"]));
			$comment = $id->append_child($dom->create_element('comment'));
			$comment->append_child($dom->create_text_node(utf8_encode($tab_content["comment"])));

		}
	}
	return $dom;
}
function export_data_course_link_from_db($_cid)
{
	$tab = array ();
	$tab["links"] = export_data_course_link_links($_cid);
	$tab["resources"] = export_data_course_link_resources($_cid);

	return $tab;
}
function export_data_course_link_in_file($tbl, $_cid)
{

	$dom = domxml_new_doc('1.0');
	$link = $dom->append_child($dom->create_element('link'));
	$links = $link->append_child($dom->create_element('links'));
	if (isset ($tbl["links"]) && is_array($tbl["links"]) && (count($tbl["links"]) > 0))
	{
		foreach ($tbl["links"] as $tab_content)
		{
			$id = $links->append_child($dom->create_element('id'));
			$id->set_attribute("id", $tab_content["id"]);
			$src_id = $id->append_child($dom->create_element('src_id'));
			$src_id->append_child($dom->create_text_node($tab_content["src_id"]));
			$dest_id = $id->append_child($dom->create_element('dest_id'));
			$dest_id->append_child($dom->create_text_node($tab_content["dest_id"]));
			$creation_time = $id->append_child($dom->create_element('creation_time'));
			$creation_time->append_child($dom->create_text_node($tab_content["creation_time"]));
		}
	}
	$resources = $link->append_child($dom->create_element('resources'));
	if (isset ($tbl["resources"]) && is_array($tbl["resources"]) && (count($tbl["resources"]) > 0))
	{
		foreach ($tbl["resources"] as $tab_content)
		{
			$id = $resources->append_child($dom->create_element('id'));
			$id->set_attribute("id", $tab_content["id"]);
			$crl = $id->append_child($dom->create_element('crl'));
			$crl->append_child($dom->create_text_node(utf8_encode($tab_content["crl"])));
			$title = $id->append_child($dom->create_element('title'));
			$title->append_child($dom->create_text_node(utf8_encode($tab_content["title"])));
		}
	}
	return $dom;
}
function export_data_course_lp_from_db($_cid)
{
	$tab = array ();
	$tab["asset"] = export_data_course_lp_asset($_cid);
	$tab["learnpath"] = export_data_course_lp_learnpath($_cid);
	$tab["module"] = export_data_course_lp_module($_cid);
	$tab["rel_learnpath_module"] = export_data_course_lp_rel_learnpath_module($_cid);
	$tab["user_module_progress"] = export_data_course_lp_user_module_progress($_cid);

	return $tab;
}
function export_data_course_lp_in_file($tbl, $_cid)
{
	$dom = domxml_new_doc('1.0');
	$lp = $dom->append_child($dom->create_element('lp'));

	$asset = $lp->append_child($dom->create_element('asset'));
	if (isset ($tbl["asset"]) && is_array($tbl["asset"]) && (count($tbl["asset"]) > 0))
	{
		foreach ($tbl["asset"] as $tab_content)
		{
			$asset_id = $asset->append_child($dom->create_element('asset_id'));
			$asset_id->set_attribute("asset_id", $tab_content["asset_id"]);
			$module_id = $asset_id->append_child($dom->create_element('module_id'));
			$module_id->append_child($dom->create_text_node($tab_content["module_id"]));
			$path = $asset_id->append_child($dom->create_element('path'));
			$path->append_child($dom->create_text_node(utf8_encode($tab_content["path"])));
			$comment = $asset_id->append_child($dom->create_element('comment'));
			$comment->append_child($dom->create_text_node(utf8_encode($tab_content["comment"])));
		}
	}

	$learnpath = $lp->append_child($dom->create_element('learnpath'));
	if (isset ($tbl["learnpath"]) && is_array($tbl["learnpath"]) && (count($tbl["learnpath"]) > 0))
	{
		foreach ($tbl["learnpath"] as $tab_content)
		{
			$learnPath_id = $learnpath->append_child($dom->create_element('learnPath_id'));
			$learnPath_id->set_attribute("learnPath_id", $tab_content["learnPath_id"]);
			$name = $learnPath_id->append_child($dom->create_element('name'));
			$name->append_child($dom->create_text_node(utf8_encode($tab_content["name"])));
			$comment = $learnPath_id->append_child($dom->create_element('comment'));
			$comment->append_child($dom->create_text_node(utf8_encode($tab_content["comment"])));
			$lock = $learnPath_id->append_child($dom->create_element('lock'));
			$lock->append_child($dom->create_text_node($tab_content["lock"]));
			$visibility = $learnPath_id->append_child($dom->create_element('visibility'));
			$visibility->append_child($dom->create_text_node($tab_content["visibility"]));
			$rank = $learnPath_id->append_child($dom->create_element('rank'));
			$rank->append_child($dom->create_text_node($tab_content["rank"]));
		}
	}

	$module = $lp->append_child($dom->create_element('module'));
	if (isset ($tbl["module"]) && is_array($tbl["module"]) && (count($tbl["module"]) > 0))
	{
		foreach ($tbl["module"] as $tab_content)
		{
			$module_id = $module->append_child($dom->create_element('module_id'));
			$module_id->set_attribute("module_id", $tab_content["module_id"]);
			$name = $module_id->append_child($dom->create_element('name'));
			$name->append_child($dom->create_text_node(utf8_encode($tab_content["name"])));
			$comment = $module_id->append_child($dom->create_element('comment'));
			$comment->append_child($dom->create_text_node(utf8_encode($tab_content["comment"])));
			$accessibility = $module_id->append_child($dom->create_element('accessibility'));
			$accessibility->append_child($dom->create_text_node($tab_content["accessibility"]));
			$startAsset_id = $module_id->append_child($dom->create_element('startAsset_id'));
			$startAsset_id->append_child($dom->create_text_node($tab_content["startAsset_id"]));
			$contentType = $module_id->append_child($dom->create_element('contentType'));
			$contentType->append_child($dom->create_text_node($tab_content["contentType"]));
			$launch_data = $module_id->append_child($dom->create_element('launch_data'));
			$launch_data->append_child($dom->create_text_node(utf8_encode($tab_content["launch_data"])));
		}
	}
	$rel_learnpath_module = $lp->append_child($dom->create_element('rel_learnpath_module'));
	if (isset ($tbl["rel_learnpath_module"]) && is_array($tbl["rel_learnpath_module"]) && (count($tbl["rel_learnpath_module"]) > 0))
	{
		foreach ($tbl["rel_learnpath_module"] as $tab_content)
		{
			$learnPath_module_id = $rel_learnpath_module->append_child($dom->create_element('learnPath_module_id'));
			$learnPath_module_id->set_attribute("learnPath_module_id", $tab_content["learnPath_module_id"]);
			$learnPath_id = $learnPath_module_id->append_child($dom->create_element('learnPath_id'));
			$learnPath_id->append_child($dom->create_text_node($tab_content["learnPath_id"]));
			$module_id = $learnPath_module_id->append_child($dom->create_element('module_id'));
			$module_id->append_child($dom->create_text_node($tab_content["module_id"]));
			$lock = $learnPath_module_id->append_child($dom->create_element('lock'));
			$lock->append_child($dom->create_text_node($tab_content["lock"]));
			$visibility = $learnPath_module_id->append_child($dom->create_element('visibility'));
			$visibility->append_child($dom->create_text_node($tab_content["visibility"]));
			$specificComment = $learnPath_module_id->append_child($dom->create_element('specificComment'));
			$specificComment->append_child($dom->create_text_node(utf8_encode($tab_content["specificComment"])));
			$rank = $learnPath_module_id->append_child($dom->create_element('rank'));
			$rank->append_child($dom->create_text_node($tab_content["rank"]));
			$parent = $learnPath_module_id->append_child($dom->create_element('parent'));
			$parent->append_child($dom->create_text_node($tab_content["parent"]));
			$raw_to_pass = $learnPath_module_id->append_child($dom->create_element('raw_to_pass'));
			$raw_to_pass->append_child($dom->create_text_node($tab_content["raw_to_pass"]));
		}
	}
	$user_module_progress = $lp->append_child($dom->create_element('user_module_progress'));
	if (isset ($tbl["user_module_progress"]) && is_array($tbl["user_module_progress"]) && (count($tbl["user_module_progress"]) > 0))
	{
		foreach ($tbl["user_module_progress"] as $tab_content)
		{
			$user_module_progress_id = $user_module_progress->append_child($dom->create_element('user_module_progress_id'));
			$user_module_progress_id->set_attribute("user_module_progress_id", $tab_content["user_module_progress_id"]);
			$user_id = $user_module_progress_id->append_child($dom->create_element('user_id'));
			$user_id->append_child($dom->create_text_node($tab_content["user_id"]));
			$learnPath_module_id = $user_module_progress_id->append_child($dom->create_element('learnPath_module_id'));
			$learnPath_module_id->append_child($dom->create_text_node($tab_content["learnPath_module_id"]));
			$learnPath_id = $user_module_progress_id->append_child($dom->create_element('learnPath_id'));
			$learnPath_id->append_child($dom->create_text_node($tab_content["learnPath_id"]));
			$lesson_location = $user_module_progress_id->append_child($dom->create_element('lesson_location'));
			$lesson_location->append_child($dom->create_text_node($tab_content["lesson_location"]));
			$lesson_status = $user_module_progress_id->append_child($dom->create_element('lesson_status'));
			$lesson_status->append_child($dom->create_text_node($tab_content["lesson_status"]));
			$entry = $user_module_progress_id->append_child($dom->create_element('entry'));
			$entry->append_child($dom->create_text_node($tab_content["entry"]));
			$raw = $user_module_progress_id->append_child($dom->create_element('raw'));
			$raw->append_child($dom->create_text_node($tab_content["raw"]));
			$scoreMin = $user_module_progress_id->append_child($dom->create_element('scoreMin'));
			$scoreMin->append_child($dom->create_text_node($tab_content["scoreMin"]));
			$scoreMax = $user_module_progress_id->append_child($dom->create_element('scoreMax'));
			$scoreMax->append_child($dom->create_text_node($tab_content["scoreMax"]));
			$total_time = $user_module_progress_id->append_child($dom->create_element('total_time'));
			$total_time->append_child($dom->create_text_node($tab_content["total_time"]));
			$session_time = $user_module_progress_id->append_child($dom->create_element('session_time'));
			$session_time->append_child($dom->create_text_node($tab_content["session_time"]));
			$suspend_data = $user_module_progress_id->append_child($dom->create_element('suspend_data'));
			$suspend_data->append_child($dom->create_text_node(utf8_encode($tab_content["suspend_data"])));
			$credit = $user_module_progress_id->append_child($dom->create_element('credit'));
			$credit->append_child($dom->create_text_node($tab_content["credit"]));
		}
	}
	return $dom;
}
function export_course_group_metadata_from_db($_cid)
{
	$tab = array ();
	$tab["property"] = export_course_group_metadata_property($_cid);
	$tab["rel_team_user"] = export_course_group_metadata_rel_team_user($_cid);
	$tab["team"] = export_course_group_metadata_team($_cid);

	return $tab;
}
function export_course_group_metadata_in_file($tbl, $_cid)
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
function export_data_course_calendar_from_db($_cid)
{
	$tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($_cid));

	$sql = "SELECT			id,
			  				       titre AS title,
						  	     contenu AS content,                  
								  	 	    day,                             
										    hour,
			  						        lasting,
			  						        visibility                   
			     
					    	 FROM `".$tbl['calendar_event']."`";

	return claro_sql_query_fetch_all($sql);
}
function export_data_course_announcement_from_db($_cid)
{
	$tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($_cid));
	$sql = "SELECT			id,
											title,                   
							  	 contenu AS content,                  
						 		 temps   AS `time`,                             
											visibility,                   
							 	 ordre AS   rank            
					    	 FROM `".$tbl['announcement']."`";

	return claro_sql_query_fetch_all($sql);
}
function export_data_course_wrk_assignment($_cid)
{
	$tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($_cid));
	$sql = "SELECT			id,
											title,
											description,
											visibility,
											def_submission_visibility,
											assignment_type,
											authorized_content,
											allow_late_upload,
											start_date,
											end_date,
											prefill_text,
											prefill_doc_path,
											prefill_submit     
					    	 FROM `".$tbl['wrk_assignment']."`";

	return claro_sql_query_fetch_all($sql);
}
function export_data_course_wrk_submission($_cid)
{
	$tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($_cid));
	$sql = "SELECT			id,
											assignment_id,
											parent_id,
											user_id,
											group_id,
											title,
											visibility,
											creation_date,
											last_edit_date,
											authors,
											submitted_text,
											submitted_doc_path,
											private_feedback,
											original_id,
											score           
					    	 FROM `".$tbl['wrk_submission']."`";

	return claro_sql_query_fetch_all($sql);
}
function export_data_course_quiz_answer($_cid)
{
	$tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($_cid));
	$sql = "SELECT			id,
											question_id,
											reponse,
											correct,
											comment,
											ponderation,
											r_position     
					    	 FROM `".$tbl['quiz_answer']."`";

	return claro_sql_query_fetch_all($sql);
}
function export_data_course_quiz_question($_cid)
{
	$tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($_cid));
	$sql = "SELECT			id,								
											question,
											description,
											ponderation,
											q_position,
											type,
											attached_file   
					    	 FROM `".$tbl['quiz_question']."`";

	return claro_sql_query_fetch_all($sql);
}
function export_data_course_quiz_rel_test_question($_cid)
{
	$tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($_cid));
	$sql = "SELECT			question_id,								
											exercice_id
					    	 FROM `".$tbl['quiz_rel_test_question']."`";

	return claro_sql_query_fetch_all($sql);
}
function export_data_course_quiz_test($_cid)
{
	$tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($_cid));
	$sql = "SELECT			id,		
					               titre AS title,
											description,
											type,
											random,
											active,
											max_time,
											max_attempt,
											show_answer,
											anonymous_attempts,
											start_date,
											end_date
					    	 FROM `".$tbl['quiz_test']."`";

	return claro_sql_query_fetch_all($sql);
}
function export_data_course_document_from_db($_cid)
{
	$tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($_cid));
	$sql = "SELECT			id,	
											path,
											visibility,
											comment
					    	 FROM `".$tbl['document']."`";

	return claro_sql_query_fetch_all($sql);
}
function export_data_course_link_links($_cid)
{
	$tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($_cid));
	$sql = "SELECT			id,	
											src_id,
											dest_id,
											creation_time
					    	 FROM `".$tbl['links']."`";

	return claro_sql_query_fetch_all($sql);
}
function export_data_course_link_resources($_cid)
{
	$tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($_cid));
	$sql = "SELECT			id,	
											crl,
											title
					    	 FROM `".$tbl['resources']."`";

	return claro_sql_query_fetch_all($sql);
}
function export_data_course_lp_asset($_cid)
{
	$tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($_cid));
	$sql = "SELECT			asset_id,	
											module_id,
											path,
											comment
					    	 FROM `".$tbl['lp_asset']."`";

	return claro_sql_query_fetch_all($sql);
}
function export_data_course_lp_learnpath($_cid)
{
	$tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($_cid));
	$sql = "SELECT			learnPath_id,	
											name,
											comment,
											`lock`,
											visibility,
											rank
					    	 FROM `".$tbl['lp_learnPath']."`";

	return claro_sql_query_fetch_all($sql);
}
function export_data_course_lp_module($_cid)
{
	$tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($_cid));
	$sql = "SELECT			module_id,	
											name,
											comment,
											accessibility,
											startAsset_id,
											contentType,
											launch_data
					    	 FROM `".$tbl['lp_module']."`";

	return claro_sql_query_fetch_all($sql);
}
function export_data_course_lp_rel_learnpath_module($_cid)
{
	$tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($_cid));
	$sql = "SELECT			learnPath_module_id,	
											learnPath_id,
											module_id,
											`lock`,
											visibility,
											specificComment,
											rank,
											parent,
											raw_to_pass
					    	 FROM `".$tbl['lp_rel_learnPath_module']."`";

	return claro_sql_query_fetch_all($sql);
}
function export_data_course_lp_user_module_progress($_cid)
{
	$tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($_cid));
	$sql = "SELECT			user_module_progress_id,
											user_id,
											learnPath_module_id,
											learnPath_id,
											lesson_location,
											lesson_status,
											entry,
											raw,
											scoreMin,
											scoreMax,
											total_time,
											session_time,
											suspend_data,
											credit
					    	 FROM `".$tbl['lp_user_module_progress']."`";

	return claro_sql_query_fetch_all($sql);
}
function export_data_course_wiki_acls($_cid)
{
	$tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($_cid));
	$sql = "SELECT			wiki_id,
											flag,
											value
							FROM `".$tbl['wiki_acls']."`";

	return claro_sql_query_fetch_all($sql);
}
function export_data_course_wiki_pages($_cid)
{
	$tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($_cid));
	$sql = "SELECT			id,
											wiki_id,
											owner_id,
											title,
											ctime,
											last_version,
											last_mtime
							FROM `".$tbl['wiki_pages']."`";

	return claro_sql_query_fetch_all($sql);
}
function export_data_course_wiki_pages_content($_cid)
{
	$tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($_cid));
	$sql = "SELECT			id,
											pid,
											editor_id,
											mtime,
											content
							FROM `".$tbl['wiki_pages_content']."`";

	return claro_sql_query_fetch_all($sql);
}
function export_data_course_wiki_properties($_cid)
{
	$tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($_cid));
	$sql = "SELECT			id,
											title,
											description,
											group_id
							FROM `".$tbl['wiki_properties']."`";

	return claro_sql_query_fetch_all($sql);
}
function export_course_tool_metadata_intro($_cid)
{
	$tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($_cid));
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
function export_course_tool_metadata_list($_cid)
{
	$tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($_cid));
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
function export_data_course_userinfo_def($_cid)
{
	$tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($_cid));
	$sql = "SELECT			id,
											title,
											comment,
											nbLine,
											rank
							FROM `".$tbl['userinfo_def']."`";

	return claro_sql_query_fetch_all($sql);
}
function export_data_course_userinfo_content($_cid)
{
	$tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($_cid));
	$sql = "SELECT			id,
											user_id,
											def_id,
											ed_ip,
											ed_date,
										    content
							FROM `".$tbl['userinfo_content']."`";

	return claro_sql_query_fetch_all($sql);
}
function export_data_course_bb_categories($_cid)
{
	$tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($_cid));
	$sql = "SELECT			cat_id,
											cat_title,
											cat_order
							FROM `".$tbl['bb_categories']."`";

	return claro_sql_query_fetch_all($sql);
}
function export_data_course_bb_forums($_cid)
{
	$tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($_cid));
	$sql = "SELECT			forum_id,
											group_id,
											forum_name,
											forum_desc,
											forum_access,
											forum_moderator,
											forum_topics,
											forum_posts,
											forum_last_post_id,
											cat_id,
											forum_type,
											forum_order
							FROM `".$tbl['bb_forums']."`";

	return claro_sql_query_fetch_all($sql);
}
function export_data_course_bb_posts($_cid)
{
	$tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($_cid));
	$sql = "SELECT			post_id,
											topic_id,
											forum_id,
											poster_id,
											post_time,
											poster_ip,
			 						 nom AS firstname,
								  prenom AS lastname
							FROM `".$tbl['bb_posts']."`";

	return claro_sql_query_fetch_all($sql);
}
function export_data_course_bb_posts_text($_cid)
{
	$tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($_cid));
	$sql = "SELECT			post_id,
											post_text
																					
							FROM `".$tbl['bb_posts_text']."`";

	return claro_sql_query_fetch_all($sql);
}
function export_data_course_bb_priv_msgs($_cid)
{
	$tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($_cid));
	$sql = "SELECT			msg_id,
											from_userid,
											to_userid,
											msg_time,
											poster_ip,
											msg_status,
											msg_text
															
							FROM `".$tbl['bb_priv_msgs']."`";

	return claro_sql_query_fetch_all($sql);
}
function export_data_course_bb_rel_topic_userstonotify($_cid)
{
	$tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($_cid));
	$sql = "SELECT			notify_id,
											user_id,
											topic_id
							FROM `".$tbl['bb_rel_topic_userstonotify']."`";

	return claro_sql_query_fetch_all($sql);
}
function export_data_course_bb_topics($_cid)
{
	$tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($_cid));
	$sql = "SELECT			topic_id,
											topic_title,
											topic_poster,
											topic_time,
											topic_views,
											topic_replies,
											topic_last_post_id,
											forum_id,
											topic_status,
											topic_notify,
			 						 nom AS firstname,
								  prenom AS lastname
							FROM `".$tbl['bb_topics']."`";

	return claro_sql_query_fetch_all($sql);
}
function export_data_course_bb_users($_cid)
{

	$tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($_cid));
	$sql = "SELECT			user_id,
											username,
											user_regdate,
											user_password,
											user_email,
											user_icq,
											user_website,
											user_occ,
											user_from,
											user_intrest,
											user_sig,
											user_viewemail,
											user_theme,
											user_aim,
											user_yim,
											user_msnm,
											user_posts,
											user_attachsig,
											user_desmile,
											user_html,
											user_bbcode,
											user_rank,
											user_level,
											user_lang,
											user_actkey,
											user_newpasswd
							FROM `".$tbl['bb_users']."`";

	return claro_sql_query_fetch_all($sql);
}
function export_course_user_metadata_from_db($_cid)
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
							 WHERE c.code_cours = '".$_cid."'";
	 
	return claro_sql_query_fetch_all($sql);
}
function export_course_rel_course_user_from_db($_cid)
{
	$tblMain = claro_sql_get_main_tbl();
	$sql = "SELECT   code_cours as course_id,
					 user_id,
					 statut,
					 role,
					 team,
					 tutor
			FROM `".$tblMain['rel_course_user']."` 
		 	WHERE code_cours = '".$_cid."'";
	
	return claro_sql_query_fetch_all($sql);
}
function export_course_data_from_db($_cid)
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
							WHERE code = '".$_cid."'";

	return claro_sql_query_fetch_all($sql);
}
function export_data_course_description_from_db($_cid)
{
	$tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($_cid));
	$sql = "SELECT 		id,
										title,
										content,
										`upDate`,
										visibility			
							FROM `".$tbl['course_description']."`";

	return claro_sql_query_fetch_all($sql);
}
function export_course_group_metadata_property($_cid)
{
	$tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($_cid));
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
function export_course_group_metadata_rel_team_user($_cid)
{
	$tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($_cid));
	$sql = "SELECT 		id,
										user,
										team,
										status,
										role
					    	FROM `".$tbl['group_rel_team_user']."`";

	return claro_sql_query_fetch_all($sql);
}
function export_course_group_metadata_team($_cid)
{
	$tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($_cid));
	$sql = "SELECT			id,
											name,
											description,
											tutor,
											maxStudent,
											secretDirectory									
					    	FROM `".$tbl['group_team']."`";

	return claro_sql_query_fetch_all($sql);
}
function export_group_document($course_id)
{
	return export_tool_document($course_id, "group");
}
function export_exercise_document($course_id)
{
	return export_tool_document($course_id, "exercise");
}
function export_chat_document($course_id)
{
	return export_tool_document($course_id, "chat");
}
function export_document_document($course_id)
{
	return export_tool_document($course_id, "document");
}
function export_modules_document($course_id)
{
	return export_tool_document($course_id, "modules");
}
function export_work_document($course_id)
{
	return export_tool_document($course_id, "work");
}
function export_tool_document($course_id, $toolName)
{
	$course_path = get_conf("rootSys")."courses/".$course_id;
	//test if the course folder exist
	if (file_exists($course_path) && is_dir($course_path))
	{
		$course_tool_path = test_if_tool_folder_exist($course_path, $toolName);
		//test if the course tool folder exist
		if (false !== $course_tool_path)
		{
			//test if no error occured while compressing
			if (false !== compress_directory($course_tool_path))
			{

				$tool_zip_folder_path = $course_path.'/'.$toolName.".zip";
				$tool_zip_export_folder_path = EXPORT_PATH.'/'.$course_id.'/tools/'.$toolName.'/'.$toolName.".zip";
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
		} else
			return claro_failure :: set_failure("document_folder_doesnt_exist");
	} else
		return claro_failure :: set_failure("document_export_failed");
}
/*
 * This function return the path of the folder if it exist
 * false if not
 */
function test_if_tool_folder_exist($path, $toolName)
{
	$d = opendir($path);

	while (false !== ($f = readdir($d)))
	{
		if ($f == $toolName)
		{
			fclose($d);
			return $path."/".$toolName;

		}
	}
	fclose($d);
	return false;
}
function dump_file($data_type,$toolName,$dom,$course_id)
{
	$foo = EXPORT_PATH . '/' . $course_id . '/' . $data_type . '/' . $toolName . '/';
	if (!file_exists($foo)) claro_mkdir($foo,0777,TRUE);
		
	$result = $dom->dump_file( $foo . $toolName . '.xml', true, false);
	if ($result == 0)
		return claro_failure :: set_failure('cant_write_xml_file');
	return true;
}
function intervall($start, $end)
{
	echo '<span>*'.$end - $start.'</Span>';
	flush();
}

function intertime()
{
	static $start = null;
	static $end = null;

	if (is_null($start))
	{
		$start = microtime();
		return null;
	}
	$end = microtime();
	intervall($start, $end);
	$start = $end;
}
?>