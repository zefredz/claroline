<?php
    require_once $includePath.'/lib/export_zip.lib.php';
    require_once $includePath.'/../wiki/lib/lib.createwiki.php';
    require_once ($includePath.'/lib/pclzip/pclzip.lib.php');
    require_once ($includePath.'/lib/claro_main.lib.php');
    require_once $includePath.'/lib/fileManage.lib.php';
    require_once $includePath.'/lib/forum.lib.php';
    require_once $includePath.'/lib/import.xmlparser.lib.php';
     
    require $includePath.'/lib/debug.lib.inc.php';
    require $includePath.'/lib/group.lib.inc.php';
     
    define("EXTRACT_PATH", 'C:\Program Files\EasyPHP1-8\www\cvs\claroline.test\claroline\export');
    /**
	 * Import data course into a exisiting course on the claroline platform 
	 * The information to import is contained in a zip file. 
	 * This zip file contains xml files in which information for the db is stored
	 * This zip file also contains the course documents to import
	 * 
	 * The whole course is not always imported
	 * To filter what must be imported and what does not, 
	 * information must be contained in the $importGoupInfo array 
 	 *
 	 * Its format must follow those rule :
 	 *      - Must be an array of arrays
 	 * 		- Must be like this : $importGroupInfo[index][groupInfos]
 	 * 		- Each index points to a group
 	 * 		- A groupinfo can be tools name like "wiki", "announcement", etc.. and must contain a boolean
 	 * 		- A groupinfo can also be "mustImportUsers" to chose to import users or not for this group
 	 * 		- One groupInfo must be "id" and must contain the group_id to import
 	 * 		- "id" must be null when for general course information
 	 * 		- When "id" is not null. A groupInfo can be "mustImportTools". This means we can choose to not import 
 	 * 		  a group tool to replace him with a empty tool.   		 
 	 * 		
	 * @author Yannick Wautelet <yannick_wautelet@hotmail.com> 
	 * @param  string  $archive_file     - the zip file's complete path 
	 * @param  string  $course_id    
	 * @param  array   $importGroupInfo  - array containing informations about what to import and what not
	 * @return false if a problem occured, true if not.  
 	 */     	  	 
    function import_all_data_course_in_db($archive_file, $course_id,$importGroupInfo)
    {
        $exported_course_id = basename($archive_file, '.zip');
        //if (false == test_zip_file($archive_file)) return false;
        if (false === extract_archive($archive_file, EXTRACT_PATH))
            return false;
      
        $usersIdToChange = import_users($exported_course_id,$course_id);
        if (false === $usersIdToChange)
            return false;              
         
        if (isset($importGroupInfo[0]['group']) && true === $importGroupInfo[0]['group'])
        	$importGroupInfo = import_group($exported_course_id, $GLOBALS['_cid'], $usersIdToChange, $importGroupInfo);
         
        if (false === $importGroupInfo)
            return false;
         
        if (false == importGroupDocuments($exported_course_id, $course_id, $importGroupInfo[0]))
            return false;
         
        /*
        if (false === import_manifest($exported_course_id, $course_id, $importGroupInfoInfo))
        return false;
        */
        if (false === import_announcement($exported_course_id, $GLOBALS['_cid'], $importGroupInfo[0]))
            return false;
         
        if (false === import_course_description($exported_course_id, $GLOBALS['_cid'], $importGroupInfo[0]))
            return false;
         
        if (false === import_calendar($exported_course_id, $GLOBALS['_cid'], $importGroupInfo[0]))
            return false;
         
        if (false === import_link($exported_course_id, $GLOBALS['_cid'], $importGroupInfo[0]))
            return false;
         
        if (false === import_lp($exported_course_id, $GLOBALS['_cid'], $importGroupInfo[0], $usersIdToChange))
            return false;
         
        if (false === import_quiz($exported_course_id, $GLOBALS['_cid'], $importGroupInfo[0]))
            return false;
         
        if (false === import_tool($exported_course_id, $GLOBALS['_cid'], $importGroupInfo[0]))
            return false;
         
        if (false === import_document($exported_course_id, $GLOBALS['_cid'], $importGroupInfo[0]))
            return false;
         
        if (false == import_bb($exported_course_id, $GLOBALS['_cid'], $importGroupInfo[0], $usersIdToChange))
            return false;
         
        if (false == import_wiki($exported_course_id, $GLOBALS['_cid'], $importGroupInfo[0], $usersIdToChange))
            return false;
         
        if (false === import_wrk($exported_course_id, $GLOBALS['_cid'], $importGroupInfo, $usersIdToChange))
            return false;
         
        if (false === import_userinfo($exported_course_id, $GLOBALS['_cid'], $importGroupInfo[0], $usersIdToChange))
            return false;
         
        // claro_delete_file(EXTRACT_PATH."/".$exported_course_id);
         
        return true;
    }
    /**
     * 	Based on the $tab array containing user data,
     *  this function create the array $usersIdToChange which 
     *  say, for each users, what is his old id, his new id and 
     *  if we must import this user or not in the db	
     * 
	 * @author Yannick Wautelet <yannick_wautelet@hotmail.com> 
	 *  
	 * @param  string  $course_id
	 * @param  string  $tab              - contain the user data    	  
	 * @return array $usersIdToChange which contains relation between the old user_id 
	 * 		   and the new user_id who will be used for the import             
	 * 
 	 */     
    function filter_users($course_id,$tab)
    {    	        	    	     
         $tbl = claro_sql_get_main_tbl();
    	 
    	 $sql = "SELECT u.`user_id`, u.`nom` AS `firstname`, u.`prenom`  AS  `lastname`, u.`officialCode` 
    	 		 FROM `".$tbl['user']."` AS  u, `".$tbl['rel_course_user']."` AS  cu
				 WHERE u.user_id = cu.user_id and cu.code_cours = '".$course_id."'";    	
    	 $result = claro_sql_query_fetch_all($sql);    	
    	 
    	 $sql = "SELECT max(user_id) FROM `".$tbl['user']."`";
    	 $user_offset = claro_sql_query_get_single_value($sql);    	 
    	 
    	 $usersIdToChange = array();      	    	 
    	 foreach($tab['user'] as $id => $userToAdd)
    	 {    	
    	 	$usersIdToChange[$id]['oldUserId'] = $userToAdd['user_id'];   
    	 	$usersIdToChange[$id]['newUserId'] = $userToAdd['user_id'] + $user_offset;   
    	 	$usersIdToChange[$id]['mustImportUser'] = true;
    	 		 	    		    	 				 	    	    	     	 	    		     
    	 	foreach($result as $userInDb)
    	 	{    	 		    	 		    	 		    
    	 		if($userToAdd['lastname'] === $userInDb['lastname'] && $userToAdd['firstname'] === $userInDb['firstname'])
    	 		{    	 		
    	 			if($userToAdd['officialCode'] == $userInDb['officialCode'])
    	 			{    	 	    			    	 			
    	 				$usersIdToChange[$id]['oldUserId'] = $userInDb['user_id'];   
    	 				$usersIdToChange[$id]['newUserId'] = $userInDb['user_id'];
    	 				$usersIdToChange[$id]['mustImportUser'] = false;    	     	 							    	   				
    	 			}    	 			
    	 		}
    	 	}
    	 }        	         	       
    	 return $usersIdToChange;    
    }
     /**
     * 		
     * Import users for a course in db.
     * If a user doesnt exist, we create it
     * The user is added in the users table and also in the courses-users relation table.
     *   
	 * @author Yannick Wautelet <yannick_wautelet@hotmail.com>  
	 * @param  string  $exported_course_id  - course_id of the exported course
	 * @param  string  $course_id           - course_id of the course in which the import will occur
	 * @return false if a problem occured, true if not.         
	 * 
 	 */     
    function import_users($exported_course_id,$course_id)
    {
        //import users from file in a tab
        $tab = import_users_from_file($exported_course_id);
        if (false !== $tab)
        {        	
            //filter users and put it in a new tab
            $usersIdToChange = filter_users($course_id,$tab);           
                                  
            //put users in db
            import_users_in_db($tab,$course_id,$usersIdToChange);
        }
        else
        	return false;                  
         
        return $usersIdToChange;
    }
    function import_announcement($exported_course_id, $course_id = NULL, $importGroupInfo)
    {
        flush_announcement_table($course_id);
        if (isset ($importGroupInfo["announcement"]) && true == $importGroupInfo["announcement"])
        {
            $tab = import_announcement_from_file($exported_course_id);
             
            if (false !== $tab)              
                import_announcement_in_db($tab, $course_id);            
            else return false;
        }
        return true;
    }
    function import_course_description($exported_course_id, $course_id = NULL, $importGroupInfo)
    {
        flush_course_description_table($course_id);
         
        if (isset ($importGroupInfo["description"]) && true == $importGroupInfo["description"])
        {
            $tab = import_course_description_from_file($exported_course_id);
            if (false !== $tab)
                import_course_description_in_db($tab, $course_id);                        
            else return false;
        }
        return true;
    }
    function import_calendar($exported_course_id, $course_id = NULL, $importGroupInfo)
    {
        flush_calendar_table($course_id);
         
        if (isset ($importGroupInfo["calendar"]) && true == $importGroupInfo["calendar"])
        {
            $tab = import_calendar_from_file($exported_course_id);
            if (false !== $tab)                
                import_calendar_in_db($tab, $course_id);            
            else return false;
        }
         
        return true;
    }
    function import_link($exported_course_id, $course_id = NULL, $importGroupInfo)
    {
        flush_link_table($course_id);
         
        if (isset ($importGroupInfo["link"]) && true == $importGroupInfo["link"])
        {
            $tab = import_link_from_file($exported_course_id);
            if (false !== $tab)            
                import_link_in_db($tab, $course_id);            
            else return false;
        }
         
        return true;
    }
    function set_lpIds($tab,$course_id)
    {
    	$tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
         
        $sql = "SELECT max(asset_id) FROM `".$tbl['lp_asset']."`";
        $lp_asset_offset = claro_sql_query_get_single_value($sql);
        if (!isset($lp_asset_offset)) $lp_asset_offset = 0;
        $sql = "SELECT max(learnpath_id) FROM `".$tbl['lp_learnPath']."`";
        $lp_learnPath_offset = claro_sql_query_get_single_value($sql);
        if (!isset($lp_learnPath_offset)) $lp_learnPath_offset = 0;
        $sql = "SELECT max(module_id) FROM `".$tbl['lp_module']."`";
        $lp_module_offset = claro_sql_query_get_single_value($sql);             
        if (!isset($lp_module_offset)) $lp_module_offset = 0;
        $sql = "SELECT max(learnpath_module_id) FROM `".$tbl['lp_rel_learnPath_module']."`";
        $lp_rel_learnpath_module_offset = claro_sql_query_get_single_value($sql);             
        if (!isset($lp_rel_learnpath_module_offset)) $lp_rel_learnpath_module_offset = 0;
        $sql = "SELECT max(user_module_progress_id) FROM `".$tbl['lp_user_module_progress']."`";
        $lp_module_progress_offset = claro_sql_query_get_single_value($sql);             
        if (!isset($lp_module_progress_offset)) $lp_module_progress_offset = 0;
         
        if (isset ($tab['asset']) && is_array($tab['asset']) && (count($tab['asset']) > 0))
        {
            foreach ($tab['asset'] as $lacle => $asset)
            {
                $tab['asset'][$lacle]['asset_id'] = $asset['asset_id'] + $lp_asset_offset;         
                $tab['asset'][$lacle]['module_id'] = $asset['module_id'] + $lp_module_offset;
            }
        }
        if (isset ($tab['learnpath']) && is_array($tab['learnpath']) && (count($tab['learnpath']) > 0))
        {
            foreach ($tab['learnpath'] as $lacle => $learnpath)
            {
                $tab['learnpath'][$lacle]['learnPath_id'] = $learnpath['learnPath_id'] + $lp_learnPath_offset;
            }
        }
        if (isset ($tab['module']) && is_array($tab['module']) && (count($tab['module']) > 0))
        {
            foreach ($tab['module'] as $lacle => $module)
            {
                $tab['module'][$lacle]['module_id'] = $module['module_id'] + $lp_module_offset;
                $tab['module'][$lacle]['startAsset_id'] = $module['startAsset_id'] + $lp_asset_offset;
            }
        }
        if (isset ($tab['rel_learnpath_module']) && is_array($tab['rel_learnpath_module']) && (count($tab['rel_learnpath_module']) > 0))
        {
            foreach ($tab['rel_learnpath_module'] as $lacle => $learnpath_module)
            {
                $tab['rel_learnpath_module'][$lacle]['learnPath_module_id'] = $learnpath_module['learnPath_module_id'] + $lp_rel_learnpath_module_offset;
                $tab['rel_learnpath_module'][$lacle]['module_id'] = $learnpath_module['module_id'] + $lp_module_offset;
                $tab['rel_learnpath_module'][$lacle]['learnPath_id'] = $learnpath_module['learnPath_id'] + $lp_learnPath_offset;
            }
        }
        if (isset ($tab['module_progress']) && is_array($tab['module_progress']) && (count($tab['module_progress']) > 0))
        {
            foreach ($tab['module_progress'] as $lacle => $module_progress)
            {
                $tab['module_progress'][$lacle]['module_progress_id'] = $module_progress['module_progress_id'] + $lp_module_progress_offset;           
                $tab['module_progress'][$lacle]['learnPath_module_id'] = $module_progress['learnPath_module_id'] + $lp_rel_learnpath_module_offset;
                $tab['module_progress'][$lacle]['learnPath_id'] = $module_progress['learnPath_id'] + $lp_learnPath_offset;
            }
        }
       
        return $tab;
    }
    function import_lp($exported_course_id, $course_id = NULL, $importGroupInfo, $usersIdToChange)
    {
        flush_lp_table($course_id);
         
        if (isset ($importGroupInfo["lp"]) && true == $importGroupInfo["lp"])
        {
            $tab = import_lp_from_file($exported_course_id);
            if (false !== $tab)
            {               
                if (isset ($tab["lp_user_module_progress"]))                               
                    $tab["lp_user_module_progress"] = replaceUserId($usersIdToChange, $tab["lp_user_module_progress"], "user_id");                                                                	
                
                import_lp_in_db($tab, $course_id);
                set_lpIds($tab,$course_id);
            }
            else
          		return false;
        }                
        return true;
    }
    function set_quizIds($tab,$course_id)
    {
    	 $tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
         
        $sql = "SELECT max(id) FROM `".$tbl['quiz_answer']."`";
        $quiz_answer_offset = claro_sql_query_get_single_value($sql);
        if (!isset($quiz_answer_offset)) $quiz_answer_offset = 0;
        $sql = "SELECT max(id) FROM `".$tbl['quiz_question']."`";
        $quiz_question_offset = claro_sql_query_get_single_value($sql);
        if (!isset($quiz_question_offset)) $quiz_question_offset = 0;
        $sql = "SELECT max(id) FROM `".$tbl['quiz_test']."`";
        $quiz_test_offset = claro_sql_query_get_single_value($sql);      
        if (!isset($quiz_test_offset)) $quiz_test_offset = 0;
         
        if (isset ($tab['quiz_answer']) && is_array($tab['quiz_answer']) && (count($tab['quiz_answer']) > 0))
        {
            foreach ($tab['quiz_answer'] as $lacle => $quiz_answer)
            {
                $tab['quiz_answer'][$lacle]['id'] = $quiz_answer['id'] + $quiz_answer_offset;
                $tab['quiz_answer'][$lacle]['question_id'] = $quiz_answer['question_id'] + $quiz_question_offset;
            }
        }
        if (isset ($tab['quiz_question']) && is_array($tab['quiz_question']) && (count($tab['quiz_question']) > 0))
        {
            foreach ($tab['quiz_question'] as $lacle => $quiz_question)
            {
                $tab['quiz_question'][$lacle]['id'] = $quiz_question['id'] + $quiz_question_offset;
            }
        }
         if (isset ($tab['quiz_rel_test_question']) && is_array($tab['quiz_rel_test_question']) && (count($tab['quiz_rel_test_question']) > 0))
        {
            foreach ($tab['quiz_rel_test_question'] as $lacle => $quiz_question)
            {
                $tab['quiz_rel_test_question'][$lacle]['question_id'] = $quiz_question['question_id'] + $quiz_question_offset;
            }
        }
        if (isset ($tab['quiz_test']) && is_array($tab['quiz_test']) && (count($tab['quiz_test']) > 0))
        {
            foreach ($tab['quiz_test'] as $lacle => $quiz_test)
            {
                $tab['quiz_test'][$lacle]['id'] = $quiz_test['id'] + $quiz_test_offset;           
            }
        }
       
        return $tab;
    }
    function import_quiz($exported_course_id, $course_id = NULL, $importGroupInfo)
    {
        flush_quiz_table($course_id);
         
        if (isset ($importGroupInfo["quiz"]) && true == $importGroupInfo["quiz"])
        {
            $tab = import_quiz_from_file($exported_course_id);
            if (false !== $tab) import_quiz_in_db($tab, $course_id);
            if (false !== $tab) set_quizIds($tab, $course_id);
            
            else
            return false;
        }
        return true;
    }
    function import_tool($exported_course_id, $course_id = NULL, $importGroupInfo)
    {
        flush_tool_table($course_id);
         
        if (isset ($importGroupInfo["tool"]) && true == $importGroupInfo["tool"])
        {
            $tab = import_tool_from_file($exported_course_id);
            if (false !== $tab)
            {
                import_tool_in_db($tab, $course_id);
            }
            else
            return false;
        }
        return true;
    }
    function import_document($exported_course_id, $course_id = NULL, $importGroupInfo)
    {
        flush_document_table($course_id);
         
        if (isset ($importGroupInfo["document"]) && true == $importGroupInfo["document"])
        {
            if (false === importDocDocuments($exported_course_id, $course_id, $importGroupInfo))
                return false;
            $tab = import_document_from_file($exported_course_id);
            if (false !== $tab)
            {
                import_document_in_db($tab, $course_id);
            }
            else
            return false;
        }
        return true;
    } 	
    function filter_group($importGroupInfo_data, $importGroupInfo)
    {
        $tbl = array();
        $tbl[0] = null;
         
        if (isset ($importGroupInfo_data["group_team"]))
        {
            foreach ($importGroupInfo_data["group_team"] as $tab2_content)
            {
                if ($importGroupInfo["id"] == $tab2_content["id"])
                {
                    $tbl[0]["group_team"] = $tab2_content;
                     
                    $tbl[1] = $importGroupInfo;
                    $tbl[1]['id'] = '';
                    $tbl[1]['oldId'] = $tab2_content['id'];
                    if (isset($importGroupInfo['chat'])) $tbl[1]['chat'] = $importGroupInfo['chat'];
                    else $tbl[1]['chat'] = false;
                    if (isset($importGroupInfo['document'])) $tbl[1]['document'] = $importGroupInfo['document'];
                    else $tbl[1]['document'] = false;
                    $tbl[1]['directory'] = $tab2_content['secretDirectory'];
                    $tbl[1]['name'] = $tab2_content['name'];
                     
                }
            }
        }
        $tbl[0]["group_rel_team_user"] = null;
        if (isset ($importGroupInfo_data["group_rel_team_user"]) && isset($importGroupInfo['mustImportUsers']) && true === $importGroupInfo['mustImportUsers'])
         {
            foreach ($importGroupInfo_data["group_rel_team_user"] as $tab2_content)
            {
                if ($importGroupInfo["id"] == $tab2_content["team"])
                {
                    $tbl[0]["group_rel_team_user"] = $tab2_content;
                }
            }
        }
        $tbl[0]["group_property"] = null;
        if (isset ($importGroupInfo_data["group_property"]))
        {
            foreach ($importGroupInfo_data["group_property"] as $tab2_content)
            {
                $tbl[0]["group_property"] = $tab2_content;
            }
        }
         
        return $tbl;
    }
    function setAnonymousUser($usersIdToChange)
    {
        foreach ($usersIdToChange as $id => $users)
        {
            $usersIdToChange[$id]['newUserId'] = 0;
        }
        return $usersIdToChange;
    }
    function create_group_for_import($exported_course_id, $course_id, $importGroupInfo, $importGroupInfo_data, $usersIdToChange)
    {
    	
        if (isset ($importGroupInfo_data["group_rel_team_user"]))
        {
            $importGroupInfo_data["group_rel_team_user"] = replaceUserId($usersIdToChange, $importGroupInfo_data["group_rel_team_user"], "user");
        }
         
        $importGroupInfo_data = filter_group($importGroupInfo_data, $importGroupInfo);
         
        if (isset($importGroupInfo_data[1])) $importGroupInfo = $importGroupInfo_data[1];
        if (isset($importGroupInfo_data[0])) $importGroupInfo_data = $importGroupInfo_data[0];
                  
        if (false === $importGroupInfo['mustImportUsers'])
        	$usersIdToChange = setAnonymousUser($usersIdToChange);
                
        $importGroupInfo = import_group_in_db($importGroupInfo_data, $course_id, $importGroupInfo);
        if (isset($importGroupInfo_data) && isset($importGroupInfo['mustImportTools']) && true === $importGroupInfo['mustImportTools'])
        {           
            if (isset($importGroupInfo['wiki']) && true === $importGroupInfo['wiki'])
            {                 
                if (false === import_wiki($exported_course_id, $course_id, $importGroupInfo, $usersIdToChange))
                return false;
            }
            else if (null != $importGroupInfo['id'])
            {
                if (false === create_wiki($importGroupInfo['id'], $importGroupInfo['name'].' - Wiki'))
                return false;
            }                          
            if (isset($importGroupInfo['forum']) && true === $importGroupInfo['forum'])
            {
                if (false === import_bb($exported_course_id, $course_id, $importGroupInfo, $usersIdToChange))
                return false;
            }
            else if (null != $importGroupInfo['id'])
            {
                if (false === create_forum($importGroupInfo['name']." - forum", '', 2, 1, $importGroupInfo['id'], $course_id))
                return false;
            }
             
            if (null != $importGroupInfo['id'])
            {                 
                if (false == importGroupDocuments($exported_course_id, $course_id, $importGroupInfo))
                    return false;
            }
        }
        else
        {
       	   if (false === create_wiki($importGroupInfo['id'], $importGroupInfo['name'].' - Wiki'))
               	return false;
           if (false === create_forum($importGroupInfo['name']." - forum", '', 2, 1, $importGroupInfo['id'], $course_id))
                return false;
        }
        return $importGroupInfo;
    }
    function import_group($exported_course_id, $course_id, $usersIdToChange, $importGroupInfoInfo,$mustDeleteGroups = false)
    {         
        $importGroupInfo_data = import_group_from_file($exported_course_id);
        
        if (false !== $importGroupInfo_data)
        {
            foreach($importGroupInfoInfo as $id => $importGroupInfo)
            {            	
                if ($importGroupInfo['id'] != null)
                {
                    if (false === create_group_for_import($exported_course_id, $course_id, $importGroupInfo, $importGroupInfo_data, $usersIdToChange))
                    return false;
                }
            }
        }
        return $importGroupInfoInfo;
    }
    function set_userinfoIds($tab,$course_id)
    {
    	$tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
        
        $sql = "SELECT max(id) FROM `".$tbl['userinfo_content']."`";        
        $userinfo_content_offset = claro_sql_query_get_single_value($sql);
        if (!isset($userinfo_content_offset)) $userinfo_content_offset = 0;
        $sql = "SELECT max(id) FROM `".$tbl['userinfo_def']."`";
        $userinfo_def_offset = claro_sql_query_get_single_value($sql);                      
        if (!isset($userinfo_def_offset)) $userinfo_def_offset = 0;
                          
    	if (isset ($tab['content']) && is_array($tab['content']) && (count($tab['content']) > 0))
    	{
    		foreach($tab['content'] as $lacle => $tab_content)    
    		{    			
    			$tab['content'][$lacle]['id'] = (int)$tab_content['id'] + $userinfo_content_offset;    		
    			$tab['content'][$lacle]['def_id'] = (int)$tab_content['def_id'] + $userinfo_def_offset;
    		}
    	}
    	    	
    	if (isset ($tab['def']) && is_array($tab['def']) && (count($tab['def']) > 0))
    	{
    		foreach($tab['def'] as $lacle => $tab_content)
	    	{
	    		$tab['def'][$lacle]['id'] = (int)$tab_content['id'] + $userinfo_def_offset;	    	
    		}
    	}    	    	    
    	return $tab;
    } 
    function import_userinfo($exported_course_id, $course_id = NULL, $importGroupInfoInfo, $usersIdToChange)
    {
        flush_userinfo_table($course_id);
         
        if (isset ($importGroupInfo["userinfo"]) && true == $importGroupInfo["userinfo"])
        {
            $tab = import_userinfo_from_file($exported_course_id);
            if (false !== $tab)
            {
                if (isset ($tab["userinfo_content"]))            
                    $tab["userinfo_content"] = replaceUserId($usersIdToChange, $tab["userinfo_content"], "user_id");
            
                import_userinfo_in_db($tab, $course_id);
                set_userinfoIds($tab,$course_id);
            }
            else
            return false;
        }
        return true;
    }
    function import_track($exported_course_id, $course_id = NULL)
    {
        $tab = import_track_from_file($exported_course_id);
        if (false !== $tab)
        {
            flush_track_table($course_id);
            import_track_in_db($tab, $course_id);
        }
        else
        return false;
         
        return true;
    }
    function filterBb($tab, $importGroupInfo, $course_id)
    {         
        $main = claro_sql_get_main_tbl();
        $sql = "SELECT user_id FROM `".$main['rel_course_user']."` WHERE code_cours ='".$course_id."'";
        $users = claro_sql_query_fetch_all($sql);
         
        //the cat with cat_id = 1 MUST be imported
        $tbl['bb_categories'][1] = $tab['bb_categories'][1];
         
        if (isset ($tab['bb_forums']))
        {
            foreach ($tab['bb_forums'] as $id => $forum)
            {
                $mustImportForum = false;                
                if ($importGroupInfo['id'] === $forum['group_id'] && isset($importGroupInfo['forum']) && $importGroupInfo['forum'] === true)
                {
                    $tbl['bb_forums'][$id] = $forum;
                    $mustImportForum = true;
                }
                if (isset ($tab['bb_categories']))
                {
                    foreach ($tab['bb_categories'] as $cat_id => $cat)
                    {
                        if ($forum['cat_id'] == $cat['cat_id']&& $mustImportForum)
                            {
                            $tbl['bb_categories'][$cat_id] = $cat;
                        }
                    }
                }
                if (isset ($tab['bb_topics']))
                {
                    foreach ($tab['bb_topics'] as $topic_id => $topic)
                    {
                        $mustImportTopic = false;
                        if ($topic['forum_id'] == $forum['forum_id']&& $mustImportForum)
                        {
                            $tbl['bb_topics'][$topic_id] = $topic;
                            $mustImportTopic = true;
                        }
                    }
                }
                if (isset ($tab['bb_posts']))
                {
                    foreach ($tab['bb_posts'] as $post_id => $posts)
                    {                    	
                        $mustImportPost = false;
                        if ($posts['forum_id'] == $forum['forum_id'] && $mustImportForum)
                        {
                            $tbl['bb_posts'][$post_id] = $posts;
                            $mustImportPost = true;
                        }
                        else
                        if ($posts['topic_id'] == $topic['topic_id'] && $mustImportTopic)
                        {
                            $tbl['bb_posts'][$post_id] = $posts;
                            $mustImportPost = true;
                        }
                         
                        if (isset ($tab['bb_posts_text']))
                        {
                            foreach ($tab['bb_posts_text'] as $post_text_id => $posts_text)
                            {
                                if ($posts_text['post_id'] == $posts['post_id'] && $mustImportPost)
                                    {
                                    $tbl['bb_posts_text'][$post_text_id] = $posts_text;
                                }
                            }
                        }
                    }
                }
                if (isset ($tab['bb_rel_topic_usertonotify']))
                {
                    foreach ($tab['bb_rel_topic_usertonotify'] as $bb_rel_id => $bb_rel)
                    {
                        foreach ($users as $user_id)
                        {
                            if ($user_id == $bb_rel['user_id'])
                                {
                                $tbl['bb_rel_topic_usertonotify'][$bb_rel_id] = $bb_rel;
                            }
                        }
                    }
                }
                if (isset ($tab['bb_priv_msgs']))
                {
                    foreach ($tab['bb_priv_msgs'] as $bb_priv_msgs_id => $bb_privs_msgs)
                    {
                        foreach ($users as $user_id)
                        {
                            if ($user_id == $bb_privs_msgs['user_id'])
                            {
                                $tbl['bb_priv_msgs'][$bb_priv_msgs_id] = $bb_privs_msgs;
                            }
                        }
                    }
                }
            }
        }
         
        return $tbl;
    }
    function flush_course_forums($course_id)
    {
        $tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
        $sql = "SELECT forum_id FROM `".$tbl['bb_forums']."` WHERE group_id IS NULL";
        $result = claro_sql_query_fetch_all($sql);
        if (isset($result))
        {
            foreach ($result as $res)
            {
                delete_forum($res['forum_id']);
            }
        }
         
        $sql = "DELETE FROM `".$tbl['bb_categories']."` WHERE cat_id != 1";
        claro_sql_query($sql);
         
         
    }
    function import_bb($exported_course_id, $course_id = NULL, $importGroupInfo, $usersIdToChange)
    {
        $tab = import_bb_from_file($exported_course_id);
        if (false !== $tab)
        {
            if (isset ($tab["bb_posts"]))
            {
                $tab["bb_posts"] = replaceUserId($usersIdToChange, $tab["bb_posts"], "poster_id");
            }
            if (isset ($tab["bb_forums"]))
            {
                $tab["bb_forums"] = replaceUserId($usersIdToChange, $tab["bb_forums"], "forum_moderator");
            }
            if (isset ($tab["bb_rel_topic_userstonotify"]))
            {
                $tab["bb_rel_topic_userstonotify"] = replaceUserId($usersIdToChange, $tab["bb_rel_topic_userstonotify"], "user_id");
            }
            if (isset ($tab["bb_priv_msgs"]))
            {
                $tab["bb_priv_msgs"] = replaceUserId($usersIdToChange, $tab["bb_priv_msgs"], "from_userid");
            }
            if (isset ($tab["bb_priv_msgs"]))
            {
                $tab["bb_priv_msgs"] = replaceUserId($usersIdToChange, $tab["bb_priv_msgs"], "to_userid");
            }
            if (isset ($tab["bb_topics"]))
            {
                $tab["bb_topics"] = replaceUserId($usersIdToChange, $tab["bb_topics"], "topic_poster");                
            }
             
            if (isset ($tab["bb_forums"]))
            {
                $tab["bb_forums"] = replaceGroupId($importGroupInfo, $tab["bb_forums"], "group_id");
            }
             
             
            $tab = filterBb($tab, $importGroupInfo, $course_id);
                                    
            $tab = set_bbIds($course_id, $tab);
             
            if (null == $importGroupInfo['id'])
           	   flush_course_forums($course_id);
            import_bb_in_db($tab, $course_id, $importGroupInfo);
        }
        else
        return false;
        return true;
    }
    function import_wiki($exported_course_id, $course_id = NULL, $importGroupInfo, $usersIdToChange)
    {       
        $tab = import_wiki_from_file($exported_course_id);
                 
        if (false !== $tab)
        {
            if (isset ($tab["wiki_pages"]))            
                $tab["wiki_pages"] = replaceUserId($usersIdToChange, $tab["wiki_pages"], "owner_id");            
            if (isset ($tab["wiki_pages_content"]))            
                $tab["wiki_pages_content"] = replaceUserId($usersIdToChange, $tab["wiki_pages_content"], "editor_id");            
            if (isset ($tab["wiki_properties"]))            
                $tab["wiki_properties"] = replaceGroupId($importGroupInfo, $tab["wiki_properties"], "group_id");
            
            $importGroupInfoNull["oldId"] = 0;
            $importGroupInfoNull["id"] = null;
	        if (isset ($tab["wiki_properties"]))    
                $tab["wiki_properties"] = replaceGroupId($importGroupInfoNull, $tab["wiki_properties"], "group_id");    
                    
            $tab = filterWikiTab($tab, $importGroupInfo);        
            $tab = set_wikiIds($course_id, $tab);
            if (null == $importGroupInfo['id']) delete_wiki($importGroupInfo['id'] );
            import_wiki_in_db($tab, $course_id);
        }
        else
        	return false;
        return true;
    }
    /**
 	 * Filter $tab to get only data which need to be added in the database.
 	 * 
 	 * The submissions are data related to the users.
 	 * We dont have to import a submission if we don't import the related user. 
 	 * So, first, we need to check the 'mustImportUsers' variable for the course ($importGroupInfo[0]) 
 	 * We also check if we import the related group
 	 * If not, we put 'group_id' at null
 	 *
 	 * @param $tab : array with users submissions
 	 * 		  $importGroupInfo : array with groups import information
	 * @return array with users submissions filtered  
	 * @author YannickWautelet <yannick_wautelet@hotmail.com@hotmail.com>
 	*/    
    function filter_work($tab,$importGroupInfo)
    {
    	$tbl = array();
    	
    	//The 'mustImportUsers' value of $importGroupInfo[0] (the course) must be checked first
    	if(isset($importGroupInfo[0]['mustImportUsers']) && true === $importGroupInfo[0]['mustImportUsers'])
	    {    			    	
	    	foreach ($tab as $id => $tab_content)
		    {    	
		    	$tbl[$id] = $tab_content;
		    			    	
		    	if(!isset($importGroupInfo[$tab_content['group_id']]))
		    	{		    		
		    		$tbl[$id]['group_id'] = null;
		    	}
		    			    	
		    }	
    	}
    	return $tbl;
    /*
    	$tbl = array();
    	
    	//The 'mustImportUsers' value of $importGroupInfo[0] (the course) must be checked first
    	//It has the priority before the 'mustImportUsers' value of the other groups   
    	if(isset($importGroupInfo[0]['mustImportUsers']) && true === $importGroupInfo[0]['mustImportUsers'])
	    {    		
	    	    		    	
    		foreach ($importGroupInfo as $importGroupInfo_content)
	    	{     	    		    			   
	    		//Even if the 'mustImportUsers' value of $importGroupInfo[0] is set to true, we must check it for each group
	    		//It is possible we accept to import users for the course but not for a group. 
	    		if(isset($importGroupInfo_content['mustImportUsers']) && true === $importGroupInfo_content['mustImportUsers'])
	    		{	    			
	    			foreach ($tab as $id => $tab_content)
		    		{    			    					    	
	    				if($tab_content['group_id'] == $importGroupInfo_content['oldId'] || 
	    					(null == $importGroupInfo_content['oldId'] && 0 == $tab_content['group_id'])) 
	    					//very particular case : when group_id = 0
	    					//in this case, we can't set this group_id = null to express that this is 
	    					//the course data and not a group data
	    					//because a group_id = null mean here that the related group does'nt exist in this platform 
	    				{	    	    							
    						$tbl[$id] = $tab_content;
    						if(!isset($importGroupInfo_content['mustImportUsers']) || false === $importGroupInfo_content['mustImportUsers'])
    						{
    							$tbl[$id]['group_id'] = null;
    						}
	    				}    				    	
    				}
	    		}
    		}
    	}
    	return $tbl;*/
    }
    /**
     * Flush work files of a group defined by $importGroupInfo_id
     * To flush work files of the course, group_id must be 0
     *    
     */
    function flush_wrk_files($course_id)
    {    	    	          	
        $course_path = get_conf("rootSys").'courses/'.$course_id;
        $course_wrk_path = $course_path."/work"; 
                                                
        if(file_exists($course_wrk_path))
        	if(false === claro_delete_file($course_wrk_path))
        		return false;                        
    }
    function import_wrk_file($tab,$course_id)
    {
    	$course_path = get_conf("rootSys").'courses/'.$course_id;
        $course_wrk_path = $course_path.'/work'; 
        $course_extracted_wrk_path = $course_wrk_path.'/work';
        $archive_path = get_conf("rootSys").'claroline/export/'.$course_id.'/tools/work'; 
             
        if (false === extract_archive($archive_path.'/work.zip', $course_wrk_path))
            return claro_failure :: set_failure("cant_extract_file");   
			
		if(isset($tab['assignment']) && 0 != count($tab['assignment']))
		{
			foreach($tab['assignment'] as $tab_content)
			{
				if(false === claro_mkdir($course_wrk_path.'/assig_'.$tab_content['id']))
    	    		return false;	
			}
		}
		if(isset($tab['submission']) && 0 != count($tab['submission']))
		{			         
   	   		foreach($tab['submission'] as $tab_content)
	        {        	
    	    	$file_path = $course_extracted_wrk_path.'/assig_'.$tab_content['id'].'/'.$tab_content['submitted_doc_path'];
        		$destination_path = $course_wrk_path.'/assig_'.$tab_content['assignment_id'];       	        
       	
	        	if(false === claro_copy_file($file_path,$destination_path))
    	    		return false;
        	}
		} 
        if(false === claro_delete_file($course_extracted_wrk_path))
        	return false;
           
    }
    function set_wrkIds($tab,$course_id)
    {	    	    
        $tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
         
        $sql = "SELECT max(id) FROM `".$tbl['wrk_submission']."`";        
        $wrk_submission_offset = claro_sql_query_get_single_value($sql);
        if (!isset($wrk_submission_offset)) $wrk_submission_offset = 0;
        $sql = "SELECT max(id) FROM `".$tbl['wrk_assignment']."`";
        $wrk_assignment_offset = claro_sql_query_get_single_value($sql);              
        if (!isset($wrk_assignment_offset)) $wrk_assignment_offset = 0;
                          
    	if (isset ($tab['assignment']) && is_array($tab['assignment']) && (count($tab['assignment']) > 0))
    	{
    		foreach($tab['assignment'] as $ass_id => $tab_content)    
    		{    			
    			$tab['assignment'][$ass_id]['id'] = (int)$tab_content['id'] + $wrk_assignment_offset;    		
    		}
    	}
    	    	
    	if (isset ($tab['submission']) && is_array($tab['submission']) && (count($tab['submission']) > 0))
    	{
    		foreach($tab['submission'] as $sub_id => $tab_content)
	    	{
	    		$tab['submission'][$sub_id]['id'] = (int)$tab_content['id'] + $wrk_submission_offset;
	    		$tab['submission'][$sub_id]['assignment_id'] = (int)$tab_content['assignment_id'] + $wrk_assignment_offset;
    		}
    	}    	    	    
    	return $tab;
    }
    /**
     * Import the work data tool (assignments and submissions)
     * The check if the import must occur or not is made in the function
     * In all case, we flush all old data 
     * 
     */
    function import_wrk($exported_course_id, $course_id = NULL, $importGroupInfo, $usersIdToChange)    
    {    
    	if(false === flush_wrk_files($course_id))
    	 	return false;      
    	flush_wrk_table($course_id);  
         
        if (isset ($importGroupInfo[0]["work"]) && true === $importGroupInfo[0]["work"])
        {               	
            $tab = import_wrk_from_file($exported_course_id);    
                
            if (false !== $tab)
            {
                if (isset ($tab['submission']))
                {
                    $tab['submission'] = replaceUserId($usersIdToChange, $tab['submission'], 'user_id');
                }
                
                $importGroupInfoNull["oldId"] = 0;
           		$importGroupInfoNull["id"] = null;
	        	if (isset ($tab["submission"]))    
                	$tab["submission"] = replaceGroupId($importGroupInfoNull, $tab["submission"], "group_id");                            
                if (isset($tab)) 
                {              
                	    
                	if(isset($tab['submission']))
                	{
                		$tab['submission'] = filter_work($tab['submission'],$importGroupInfo);
                	}
                	                  	                	               	                        	                          
               		$tab = set_wrkIds($tab,$course_id);               		                	
	                import_wrk_in_db($tab, $course_id);                
                	import_wrk_file($tab,$course_id);                	
                }                
            }
            else
       		    return false;
        }
        return true;
    }
    function import_manifest($exported_course_id, $course_id, $importGroupInfo)
    {
        if (isset ($importGroupInfo["manifest"]) && true == $importGroupInfo["manifest"])
        {
            $tab = import_manifest_from_file($exported_course_id);
            if (false !== $tab)
            {
                import_manifest_in_db($tab, $course_id);
            }
            else
            return false;
        }
        return true;
    }
    function test_zip_file($archive_file)
    {
        if (!file_exists($archive_file))
            return claro_failure :: set_failure("archive doesn't exist");
        if (!($archive = new PclZip($archive_file)))
            return claro_failure :: set_failure("opendir failed");
        $zipContentArray = $archive->listContent();
        if (is_array($zipContentArray))
        	 foreach ($zipContentArray as $thisContent)
        	{	
            	if (!preg_match('~.xml$~i', $thisContent['filename']))
                	return claro_failure :: set_failure('file format error');
	        }
        return true;
    }
    function flush_announcement_table($course_id = null)
    {
        $tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
         
        $sql = "DELETE FROM `".$tbl["announcement"]."`";
        claro_sql_query($sql);
    }
    function import_announcement_in_db($tab, $course_id = null)
    {
        $tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
         
        if (isset ($tab) && is_array($tab) && (count($tab) > 0))
        {
            foreach ($tab as $tab_content)
            {
                $sql = "INSERT INTO `".$tbl["announcement"].'` (title,contenu,temps,ordre,visibility)
                    VALUES ("'.addslashes($tab_content['title']).'","'.addslashes($tab_content['content']).'","'.$tab_content['time'].'","'.(int) $tab_content['order'].'","'.addslashes($tab_content['visibility']).'")';
                 
                claro_sql_query($sql);
            }
        }
    }
    function import_announcement_from_file($course_id)
    {
        if (empty ($course_id))
            return claro_failure :: set_failure("Empty dir name");
        $file = EXTRACT_PATH."/".$course_id."/tools/announcement/announcement.xml";
         
        $xml = xml_parser_create($GLOBALS['charset']);
         
        $annoucement = new announcement_parser;
         
        xml_set_object($xml, $annoucement);
        xml_set_element_handler($xml, 'start_element', 'end_element');
        xml_set_character_data_handler($xml, 'get_data');
        xml_parser_set_option($xml, XML_OPTION_CASE_FOLDING, false);
        if (!is_dir(EXTRACT_PATH."/".$course_id))
        {
            return claro_failure :: set_failure("directory does not exist");
        }
        if (!is_file($file))
            return claro_failure :: set_failure("file does not exist");
        if (false == ($fp = fopen($file, 'r')))
            return claro_failure :: set_failure("can't open file");
        while ($data = fread($fp, 4096))
        {
            if (false == xml_parse($xml, $data, feof($fp)))
                return claro_failure :: set_failure("can't parse file");
        }
        fclose($fp);
         
        xml_parser_free($xml);
         
        return $annoucement->get_tab();
    }
    function flush_course_description_table($course_id = null)
    {
        $tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
         
        $sql = "DELETE FROM `".$tbl["course_description"]."`";
        claro_sql_query($sql);
    }
    function import_course_description_in_db($tab, $course_id = NULL)
    {
        $tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
         
        if (isset ($tab) && is_array($tab) && (count($tab) > 0))
        {
            foreach ($tab as $tab_content)
            {
                 
                $sql = "INSERT INTO `".$tbl["course_description"].'` (title,content,`upDate`,visibility)
                    VALUES ("'.addslashes($tab_content['title']).'","'.addslashes($tab_content['content']).'","'.$tab_content['upDate'].'","'.addslashes($tab_content['visibility']).'")';
                 
                claro_sql_query($sql);
            }
        }
    }
    function import_course_description_from_file($course_id)
    {
        if (empty ($course_id))
            return claro_failure :: set_failure("Empty dir name");
        $file = EXTRACT_PATH."/".$course_id."/tools/description/description.xml";
         
        $xml = xml_parser_create($GLOBALS['charset']);
         
        $description = new description_parser;
         
        xml_set_object($xml, $description);
        xml_set_element_handler($xml, 'start_element', 'end_element');
        xml_set_character_data_handler($xml, 'get_data');
        xml_parser_set_option($xml, XML_OPTION_CASE_FOLDING, false);
        if (!is_dir(EXTRACT_PATH."/".$course_id))
        {
            return claro_failure :: set_failure("directory does not exist");
        }
        if (!is_file($file))
            return claro_failure :: set_failure("file does not exist");
        if (false == ($fp = fopen($file, 'r')))
            return claro_failure :: set_failure("can't open file");
        while ($data = fread($fp, 4096))
        {
            if (false == xml_parse($xml, $data, feof($fp)))
                return claro_failure :: set_failure("can't parse file");
        }
        fclose($fp);
        xml_parser_free($xml);
        return $description->get_tab();
    }
    function flush_calendar_table($course_id = NULL)
    {
        $tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
         
        $sql = "DELETE FROM `".$tbl["calendar_event"]."`";
        claro_sql_query($sql);
    }
    function import_calendar_in_db($tab, $course_id = NULL)
    {
        $tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
         
        if (isset ($tab) && is_array($tab) && (count($tab) > 0))
        {
            foreach ($tab as $tab_content)
            {
                $sql = "INSERT INTO `".$tbl["calendar_event"].'` (titre,contenu,day,hour,lasting,visibility)
                    VALUES ("'.addslashes($tab_content['title']).'","'.addslashes($tab_content['content']).'","'.$tab_content['day'].'","'.$tab_content['hour'].'","'.addslashes($tab_content['lasting']).'","'.addslashes($tab_content['visibility']).'")';
                 
                claro_sql_query($sql);
            }
        }
         
    }
     
    function import_calendar_from_file($course_id)
    {
        if (empty ($course_id))
            return claro_failure :: set_failure("Empty dir name");
        $file = EXTRACT_PATH."/".$course_id."/tools/calendar/calendar.xml";
         
        $xml = xml_parser_create($GLOBALS['charset']);
         
        $calendar = new calendar_parser;
         
        xml_set_object($xml, $calendar);
        xml_set_element_handler($xml, 'start_element', 'end_element');
        xml_set_character_data_handler($xml, 'get_data');
        xml_parser_set_option($xml, XML_OPTION_CASE_FOLDING, false);
        if (!is_dir(EXTRACT_PATH."/".$course_id))
        {
            return claro_failure :: set_failure("directory does not exist");
        }
        if (!is_file($file))
            return claro_failure :: set_failure("file does not exist");
        if (false == ($fp = fopen($file, 'r')))
            return claro_failure :: set_failure("can't open file");
        while ($data = fread($fp, 4096))
        {
            if (false == xml_parse($xml, $data, feof($fp)))
                return claro_failure :: set_failure("can't parse file");
        }
        fclose($fp);
        xml_parser_free($xml);
        return $calendar->get_tab();
    }
    function flush_link_table($course_id = NULL)
    {
        $tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
         
        $sql = "DELETE FROM `".$tbl["links"]."`";
        claro_sql_query($sql);
        $sql = "DELETE FROM `".$tbl["resources"]."`";
        claro_sql_query($sql);
    }
    function import_link_in_db($tab, $course_id = NULL)
    {
        $tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
         
        if (isset ($tab['links']) && (is_array($tab['links'])) && (count($tab['links']) > 0))
        {
            foreach ($tab['links'] as $tab_content)
            {
                $sql = "INSERT INTO `".$tbl["links"].'` (src_id,dest_id,creation_time)
                    VALUES ("'.(int) $tab_content['src_id'].'","'.(int) $tab_content['dest_id'].'","'.$tab_content['creation_time'].'")';
                 
                claro_sql_query($sql);
            }
        }
         
        if (isset ($tab['resources']) && (is_array($tab['resources'])) && (count($tab['resources']) > 0))
        {
            foreach ($tab['resources'] as $tab_content)
            {
                $sql = "INSERT INTO `".$tbl["resources"].'` (crl,title)
                    VALUES ("'.addslashes($tab_content['crl']).'","'.addslashes($tab_content['title']).'")';
                 
                claro_sql_query($sql);
            }
        }
    }
    function import_link_from_file($course_id)
    {
        if (empty ($course_id))
            return claro_failure :: set_failure("Empty dir name");
        $file = EXTRACT_PATH."/".$course_id."/meta_data/link/link.xml";
         
        $xml = xml_parser_create($GLOBALS['charset']);
         
        $link = new link_parser;
         
        xml_set_object($xml, $link);
        xml_set_element_handler($xml, 'start_element', 'end_element');
        xml_set_character_data_handler($xml, 'get_data');
        xml_parser_set_option($xml, XML_OPTION_CASE_FOLDING, false);
        if (!is_dir(EXTRACT_PATH."/".$course_id))
        {
            return claro_failure :: set_failure("directory does not exist");
        }
        if (!is_file($file))
            return claro_failure :: set_failure("file does not exist");
        if (false == ($fp = fopen($file, 'r')))
            return claro_failure :: set_failure("can't open file");
        while ($data = fread($fp, 4096))
        {
            if (false == xml_parse($xml, $data, feof($fp)))
                return claro_failure :: set_failure("can't parse file");
        }
        fclose($fp);
        xml_parser_free($xml);
        return $link->get_tab();
    }
    function flush_lp_table($course_id = NULL)
    {
        $tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
         
        $sql = "DELETE FROM `".$tbl["lp_asset"]."`";
        claro_sql_query($sql);
        $sql = "DELETE FROM `".$tbl["lp_learnPath"]."`";
        claro_sql_query($sql);
        $sql = "DELETE FROM `".$tbl["lp_module"]."`";
        claro_sql_query($sql);
        $sql = "DELETE FROM `".$tbl["lp_rel_learnPath_module"]."`";
        claro_sql_query($sql);
        $sql = "DELETE FROM `".$tbl["lp_user_module_progress"]."`";
        claro_sql_query($sql);
    }
    function import_lp_in_db($tab, $course_id = NULL)
    {
        $tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
         
        if (isset ($tab['asset']) && (is_array($tab['asset'])) && (count($tab['asset']) > 0))
        {
            foreach ($tab['asset'] as $tab_content)
            {
                $sql = "INSERT INTO `".$tbl["lp_asset"].'` (module_id,path,comment)
                    VALUES ("'.(int) $tab_content['module_id'].'","'.addslashes($tab_content['path']).'","'.addslashes($tab_content['comment']).'")';
                 
                claro_sql_query($sql);
            }
        }
        if (isset ($tab['learnpath']) && (is_array($tab['learnpath'])) && (count($tab['learnpath']) > 0))
        {
            foreach ($tab['learnpath'] as $tab_content)
            {
                $sql = "INSERT INTO `".$tbl['lp_learnPath'].'` (learnpath_id,name,comment,`lock`,visibility,rank)
                    VALUES ('.(int) $tab_content['learnPath_id'].',"'.addslashes($tab_content['name']).'","'.addslashes($tab_content['comment']).'","'.addslashes($tab_content['lock']).'","'.addslashes($tab_content['visibility']).'","'.(int) $tab_content['rank'].'")';
                 
                claro_sql_query($sql);
            }
        }
        if (isset ($tab['module']) && (is_array($tab['module'])) && (count($tab['module']) > 0))
        {
            foreach ($tab['module'] as $tab_content)
            {
                $sql = "INSERT INTO `".$tbl["lp_module"].'` (module_id,name,comment,accessibility,startAsset_id,
                											 contentType,launch_data)
                    VALUES ('.(int) $tab_content['module_id'].',"'.addslashes($tab_content['name']).'","'.addslashes($tab_content['comment']).'","'.addslashes($tab_content['accessibility']).'","'.(int) $tab_content['startAsset_id'].'","'.addslashes($tab_content['contentType']).'","'.addslashes($tab_content['launch_data']).'")';
                 
                claro_sql_query($sql);
            }
        }
        if (isset ($tab['rel_learnpath_module']) && (is_array($tab['rel_learnpath_module'])) && (count($tab['rel_learnpath_module']) > 0))
        {
            foreach ($tab['rel_learnpath_module'] as $tab_content)
            {
                $sql = "INSERT INTO `".$tbl["lp_rel_learnPath_module"].'` (learnpath_module_id,learnPath_id,module_id,`lock`,
                													       visibility,specificComment,rank,parent,raw_to_pass)
                    VALUES ('.(int) $tab_content['learnPath_module_id'].','.(int) $tab_content['learnPath_id'].','.(int) $tab_content['module_id'].',"'.addslashes($tab_content['lock']).'","'.addslashes($tab_content['visibility']).'","'.addslashes($tab_content['specificComment']).'","'.(int) $tab_content['rank'].'","'.(int) $tab_content['parent'].'","'.(int) $tab_content['raw_to_pass'].'")';
                claro_sql_query($sql);
            }
        }
        if (isset ($tab['user_module_progress']) && (is_array($tab['user_module_progress'])) && (count($tab['user_module_progress']) > 0))
        {
            foreach ($tab['user_module_progress'] as $tab_content)
            {
                $sql = "INSERT INTO `".$tbl["lp_user_module_progress"].'`(user_module_progress_id,user_id,learnPath_module_id,learnPath_id,lesson_location,lesson_status,
                    													  entry,raw,scoreMin,scoreMax,total_time,session_time,suspend_data,credit)
                    VALUES ('.(int) $tab_content['user_module_progress_id'].','.(int) $tab_content['user_id'].',"'.(int) $tab_content['learnPath_module_id'].'","'.(int) $tab_content['learnPath_id'].'","'.addslashes($tab_content['lesson_location']).'","'.addslashes($tab_content['lesson_status']).'","'.addslashes($tab_content['entry']).'","'.(int) $tab_content['raw'].'","'.(int) $tab_content['scoreMin'].'","'.(int) $tab_content['scoreMax'].'","'.addslashes($tab_content['total_time']).'","'.addslashes($tab_content['session_time']).'","'.addslashes($tab_content['suspend_data']).'","'.addslashes($tab_content['credit']).'")';                 
                claro_sql_query($sql);
            }
        }
    }
    function import_lp_from_file($course_id)
    {
        if (empty ($course_id))
            return claro_failure :: set_failure("Empty dir name");
        $file = EXTRACT_PATH."/".$course_id."/tools/lp/lp.xml";
         
        $xml = xml_parser_create($GLOBALS['charset']);
         
        $lp = new lp_parser;
         
        xml_set_object($xml, $lp);
        xml_set_element_handler($xml, 'start_element', 'end_element');
        xml_set_character_data_handler($xml, 'get_data');
        xml_parser_set_option($xml, XML_OPTION_CASE_FOLDING, false);
        if (!is_dir(EXTRACT_PATH."/".$course_id))
        {
            return claro_failure :: set_failure("directory does not exist");
        }
        if (!is_file($file))
            return claro_failure :: set_failure("file does not exist");
        if (false == ($fp = fopen($file, 'r')))
            return claro_failure :: set_failure("can't open file");
        while ($data = fread($fp, 4096))
        {
            if (false == xml_parse($xml, $data, feof($fp)))
                return claro_failure :: set_failure("can't parse file");
        }
        fclose($fp);
        xml_parser_free($xml);
        return $lp->get_tab();
    }
    function flush_quiz_table($course_id = NULL)
    {
        $tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
         
        $sql = "DELETE FROM `".$tbl["quiz_answer"]."`";
        claro_sql_query($sql);
        $sql = "DELETE FROM `".$tbl["quiz_question"]."`";
        claro_sql_query($sql);
        $sql = "DELETE FROM `".$tbl["quiz_rel_test_question"]."`";
        claro_sql_query($sql);
        $sql = "DELETE FROM `".$tbl["quiz_test"]."`";
        claro_sql_query($sql);
    }
     
    function import_quiz_in_db($tab, $course_id = NULL)
    {
        $tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
        if (isset ($tab['answer']) && (is_array($tab['answer'])) && (count($tab['answer']) > 0))
        {
            foreach ($tab['answer'] as $tab_content)
            {
                $sql = "INSERT INTO `".$tbl["quiz_answer"].'` (id,question_id,reponse,correct,comment,ponderation,r_position)
                    VALUES ('.(int) $tab_content['id'].',"'.(int) $tab_content['question_id'].'","'.addslashes($tab_content['reponse']).'","'.(int) $tab_content['correct'].'","'.addslashes($tab_content['comment']).'","'.(int) $tab_content['ponderation'].'","'.(int) $tab_content['r_position'].'")';
                 
                claro_sql_query($sql);
            }
        }
        if (isset ($tab['question']) && (is_array($tab['question'])) && (count($tab['question']) > 0))
        {
            foreach ($tab['question'] as $tab_content)
            {
                $sql = "INSERT INTO `".$tbl["quiz_question"].'` (id,question,description,ponderation,q_position,
                    type,attached_file)
                    VALUES ('.(int) $tab_content['id'].',"'.addslashes($tab_content['question']).'","'.addslashes($tab_content['description']).'","'.(int) $tab_content['ponderation'].'","'.(int) $tab_content['q_position'].'","'.(int) $tab_content['type'].'","'.addslashes($tab_content['attached_file']).'")';
                 
                claro_sql_query($sql);
            }
        }
         
        if (isset ($tab['rel_test_question']) && (is_array($tab['rel_test_question'])) && (count($tab['rel_test_question']) > 0))
        {
            foreach ($tab['rel_test_question'] as $tab_content)
            {
                $sql = "INSERT INTO `".$tbl["quiz_rel_test_question"].'` (question_id,exercice_id)
                    VALUES ("'.(int) $tab_content['question_id'].'","'.(int) $tab_content['exercice_id'].'")';
                 
                claro_sql_query($sql);
            }
        }
         
        if (isset ($tab['test']) && (is_array($tab['test'])) && (count($tab['test']) > 0))
        {
            foreach ($tab['test'] as $tab_content)
            {
                $sql = "INSERT INTO `".$tbl["quiz_test"].'` (id,titre,description,type,random,active,max_time,max_attempt,show_answer,anonymous_attempts,start_date,end_date)
                    VALUES ('.(int) $tab_content['id'].',"'.addslashes($tab_content['titre']).'","'.addslashes($tab_content['description']).'","'.(int) $tab_content['type'].'","'.(int) $tab_content['random'].'","'.(int) $tab_content['active'].'","'.(int) $tab_content['max_time'].'","'.(int) $tab_content['max_attempt'].'","'.addslashes($tab_content['show_answer']).'","'.addslashes($tab_content['anonymous_attempts']).'","'.$tab_content['start_date'].'","'.$tab_content['end_date'].'")';
                 
                claro_sql_query($sql);
            }
        }
    }
    function import_quiz_from_file($course_id)
    {
        if (empty ($course_id))
            return claro_failure :: set_failure("Empty dir name");
        $file = EXTRACT_PATH."/".$course_id."/tools/quiz/quiz.xml";
         
        $xml = xml_parser_create($GLOBALS['charset']);
         
        $quiz = new quiz_parser;
         
        xml_set_object($xml, $quiz);
        xml_set_element_handler($xml, 'start_element', 'end_element');
        xml_set_character_data_handler($xml, 'get_data');
        xml_parser_set_option($xml, XML_OPTION_CASE_FOLDING, false);
        if (!is_dir(EXTRACT_PATH."/".$course_id))
        {
            return claro_failure :: set_failure("directory does not exist");
        }
        if (!is_file($file))
            return claro_failure :: set_failure("file does not exist");
        if (false == ($fp = fopen($file, 'r')))
            return claro_failure :: set_failure("can't open file");
        while ($data = fread($fp, 4096))
        {
            if (false == xml_parse($xml, $data, feof($fp)))
                return claro_failure :: set_failure("can't parse file");
        }
        fclose($fp);
        xml_parser_free($xml);
        return $quiz->get_tab();
    }
    function flush_tool_table($course_id = NULL)
    {
        $tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
         
        $sql = "DELETE FROM `".$tbl["tool_intro"]."`";
        claro_sql_query($sql);
        $sql = "DELETE FROM `".$tbl["tool"]."`";
        claro_sql_query($sql);
    }
    function import_tool_in_db($tab, $course_id = NULL)
    {
        $tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
         
        if (isset ($tab['tool_intro']) && (is_array($tab['tool_intro'])) && (count($tab['tool_intro']) > 0))
        {
            foreach ($tab['tool_intro'] as $tab_content)
            {
                $sql = "INSERT INTO `".$tbl["tool_intro"].'` (tool_id,title,display_date,content,rank,visibility)
                    VALUES ("'.(int) $tab_content['tool_id'].'","'.addslashes($tab_content['title']).'","'.$tab_content['display_date'].'","'.addslashes($tab_content['content']).'","'.(int) $tab_content['rank'].'","'.addslashes($tab_content['visibility']).'")';
                 
                claro_sql_query($sql);
            }
        }
        if (isset ($tab['tool_list']) && (is_array($tab['tool_list'])) && (count($tab['tool_list']) > 0))
        {
            foreach ($tab['tool_list'] as $tab_content)
            {
                $sql = "INSERT INTO `".$tbl["tool"]."` (tool_id,rank,access,script_url,script_name,addedTool)
                    VALUES (".(int) $tab_content['tool_id'].",
                    ".(int) $tab_content['rank'].",
                    '".addslashes($tab_content['access'])."',
                    ". (is_null($tab_content['script_url']) ? "NULL" : "'".addslashes($tab_content['script_url'])."'").",
                    ". (is_null($tab_content['script_name']) ? "NULL" : "'".addslashes($tab_content['script_name'])."'").",
                    '".addslashes($tab_content['addedTool'])."')";
                 
                claro_sql_query($sql);
            }
        }
         
    }
    function import_tool_from_file($course_id)
    {
        if (empty ($course_id))
            return claro_failure :: set_failure("Empty dir name");
        $file = EXTRACT_PATH."/".$course_id."/meta_data/tool/tool.xml";
         
        $xml = xml_parser_create($GLOBALS['charset']);
         
        $tool = new tool_parser;
         
        xml_set_object($xml, $tool);
        xml_set_element_handler($xml, 'start_element', 'end_element');
        xml_set_character_data_handler($xml, 'get_data');
        xml_parser_set_option($xml, XML_OPTION_CASE_FOLDING, false);
        if (!is_dir(EXTRACT_PATH."/".$course_id))
        {
            return claro_failure :: set_failure("directory does not exist");
        }
        if (!is_file($file))
            return claro_failure :: set_failure("file does not exist");
        if (false == ($fp = fopen($file, 'r')))
            return claro_failure :: set_failure("can't open file");
        while ($data = fread($fp, 4096))
        {
            if (false == xml_parse($xml, $data, feof($fp)))
                return claro_failure :: set_failure("can't parse file");
        }
        fclose($fp);
        xml_parser_free($xml);
        return $tool->get_tab();
    }
    function flush_document_table($course_id = NULL)
    {
        $tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
         
        $sql = "DELETE FROM `".$tbl["document"]."`";
        claro_sql_query($sql);
    }
    function import_document_in_db($tab, $course_id)
    {
        $tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
         
        if (isset ($tab) && (is_array($tab)) && (count($tab) > 0))
        {
            foreach ($tab as $tab_content)
            {
                $sql = "INSERT INTO `".$tbl["document"].'` (path,visibility,comment)
                    VALUES ("'.addslashes($tab_content['path']).'","'.addslashes($tab_content['visibility']).'","'.$tab_content['comment'].'")';
                 
                claro_sql_query($sql);
            }
        }
    }
    function import_document_from_file($course_id)
    {
        if (empty ($course_id))
            return claro_failure :: set_failure("Empty dir name");
        $file = EXTRACT_PATH."/".$course_id."/tools/document/document.xml";
         
        $xml = xml_parser_create($GLOBALS['charset']);
         
        $document = new document_parser;
         
        xml_set_object($xml, $document);
        xml_set_element_handler($xml, 'start_element', 'end_element');
        xml_set_character_data_handler($xml, 'get_data');
        xml_parser_set_option($xml, XML_OPTION_CASE_FOLDING, false);
        if (!is_dir(EXTRACT_PATH."/".$course_id))
            {
            return claro_failure :: set_failure("directory does not exist");
        }
        if (!is_file($file))
            return claro_failure :: set_failure("file does not exist");
        if (false == ($fp = fopen($file, 'r')))
            return claro_failure :: set_failure("can't open file");
        while ($data = fread($fp, 4096))
        {
            if (false == xml_parse($xml, $data, feof($fp)))
                return claro_failure :: set_failure("can't parse file");
        }
        fclose($fp);
        xml_parser_free($xml);
        return $document->get_tab();
    }
    function flush_group_table($course_id)
    {
        $tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));         
        $sql = "DELETE FROM `".$tbl["group_property"]."`";
        claro_sql_query($sql);
        $sql = "DELETE FROM `".$tbl["group_rel_team_user"]."`";
        claro_sql_query($sql);
        $sql = "DELETE FROM `".$tbl["group_team"]."`";
        claro_sql_query($sql);
    }
    function import_group_in_db($tab, $course_id, $importGroupInfo)
    {
        $tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
        if(isset ($tab['group_property'])) $importGroupInfo_property = $tab['group_property'];
        if(isset ($tab['group_team'])) $importGroupInfo_team = $tab['group_team'];
        if(isset ($tab['group_rel_team_user'])) $importGroupInfo_rel_team_user = $tab['group_rel_team_user'];
        
        if (isset ($tab['group_property']) && is_array($tab['group_property']) && (count($tab['group_property']) > 0))
        {
            $sql = "DELETE FROM`".$tbl["group_property"]."`";
            claro_sql_query($sql);
             
            $sql = "INSERT INTO `".$tbl["group_property"].'` (self_registration,nbGroupPerUser,private,
                forum,document,wiki,chat)
                VALUES ("'.(int) $importGroupInfo_property['self_registration'].'","'.(int) $importGroupInfo_property['nbGroupPerUser'].'","'.(int) $importGroupInfo_property['private'].'","'.(int) $importGroupInfo_property['forum'].'","'.(int) $importGroupInfo_property['document'].'","'.(int) $importGroupInfo_property['wiki'].'","'.(int) $importGroupInfo_property['chat'].'")';
            claro_sql_query($sql);
        }
         
        if (isset ($tab['group_team']) && is_array($tab['group_team']) && (count($tab['group_team']) > 0))
        {
             
            if ($importGroupInfo_team['id'] == $importGroupInfo['oldId'])
             {
                $sql = "INSERT INTO `".$tbl["group_team"].'` (name,description,tutor,maxStudent,secretDirectory)
                    VALUES ("'.addslashes($importGroupInfo_team['name']).'","'.addslashes($importGroupInfo_team['description']).'","'.(int) $importGroupInfo_team['tutor'].'","'.(int) $importGroupInfo_team['maxStudent'].'","'.addslashes($importGroupInfo_team['secretDirectory']).'")';
                 
                $id = claro_sql_query_insert_id($sql);
                 
                if (isset ($tab['group_rel_team_user']) && is_array($tab['group_rel_team_user']) && (count($tab['group_rel_team_user']) > 0))
                    {
                    $sql = "INSERT INTO `".$tbl["group_rel_team_user"].'` (user,team,status,role)
                        VALUES ("'.(int) $importGroupInfo_rel_team_user['user'].'","'.$id.'","'.(int) $importGroupInfo_rel_team_user['status'].'","'.addslashes($importGroupInfo_rel_team_user['role']).'")';
                    claro_sql_query($sql);
                }
                $importGroupInfo['id'] = $id;
            }
        }
         
        return $importGroupInfo;
    }
     
    function import_group_from_file($course_id)
    {
        if (empty ($course_id))
            return claro_failure :: set_failure("Empty dir name");
        $file = EXTRACT_PATH."/".$course_id."/tools/group/group.xml";
         
        $xml = xml_parser_create($GLOBALS['charset']);
         
        $importGroupInfo = new group_parser;
         
        xml_set_object($xml, $importGroupInfo);
        xml_set_element_handler($xml, 'start_element', 'end_element');
        xml_set_character_data_handler($xml, 'get_data');
        xml_parser_set_option($xml, XML_OPTION_CASE_FOLDING, false);
        if (!is_dir(EXTRACT_PATH."/".$course_id))
        {
            return claro_failure :: set_failure("directory does not exist");
        }
        if (!is_file($file))
            return claro_failure :: set_failure("file does not exist");
        if (false == ($fp = fopen($file, 'r')))
            return claro_failure :: set_failure("can't open file");
        while ($data = fread($fp, 4096))
        {
            if (false == xml_parse($xml, $data, feof($fp)))
                return claro_failure :: set_failure("can't parse file");
        }
        fclose($fp);
        xml_parser_free($xml);
        return $importGroupInfo->get_tab();
    }
    function flush_userinfo_table($course_id = NULL)
    {
        $tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
         
        $sql = "DELETE FROM `".$tbl["userinfo_def"]."`";
        claro_sql_query($sql);
        $sql = "DELETE FROM `".$tbl["userinfo_content"]."`";
        claro_sql_query($sql);
    }
     
    function import_userinfo_in_db($tab, $course_id = NULL)
    {
        $tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
         
        if (isset ($tab['userinfo_def']) && is_array($tab['userinfo_def']) && (count($tab['userinfo_def']) > 0))
        {
            foreach ($tab['userinfo_def'] as $tab_content)
            {
                $sql = "INSERT INTO `".$tbl["userinfo_def"].'` (title,comment,nbLine,rank)
                    VALUES ("'.addslashes($tab_content['title']).'","'.addslashes($tab_content['comment']).'","'.(int) $tab_content['nbLine'].'","'.(int) $tab_content['rank'].'")';
                 
                claro_sql_query($sql);
            }
        }
        if (isset ($tab['userinfo_content']) && is_array($tab['userinfo_content']) && (count($tab['userinfo_content']) > 0))
        {
            foreach ($tab['userinfo_content'] as $tab_content)
            {
                $sql = "INSERT INTO `".$tbl["userinfo_content"].'` (user_id,def_id,ed_ip,ed_date,content)
                    VALUES ("'.(int) $tab_content['user_id'].'","'.(int) $tab_content['def_id'].'","'.addslashes($tab_content['ed_ip']).'","'.$tab_content['ed_date'].'","'.addslashes($tab_content['content']).'")';
                 
                claro_sql_query($sql);
            }
        }
    }
    function import_userinfo_from_file($course_id)
    {
        if (empty ($course_id))
            return claro_failure :: set_failure("Empty dir name");
        $file = EXTRACT_PATH."/".$course_id."/meta_data/userinfo/userinfo.xml";
         
        $xml = xml_parser_create($GLOBALS['charset']);
         
        $userinfo = new userinfo_parser;
         
        xml_set_object($xml, $userinfo);
        xml_set_element_handler($xml, 'start_element', 'end_element');
        xml_set_character_data_handler($xml, 'get_data');
        xml_parser_set_option($xml, XML_OPTION_CASE_FOLDING, false);
        if (!is_dir(EXTRACT_PATH."/".$course_id))
        {
            return claro_failure :: set_failure("directory does not exist");
        }
        if (!is_file($file))
            return claro_failure :: set_failure("file does not exist");
        if (false == ($fp = fopen($file, 'r')))
            return claro_failure :: set_failure("can't open file");
        while ($data = fread($fp, 4096))
        {
            if (false == xml_parse($xml, $data, feof($fp)))
                return claro_failure :: set_failure("can't parse file");
        }
        fclose($fp);
        xml_parser_free($xml);
        return $userinfo->get_tab();
    }
    function flush_track_table($course_id = NULL)
    {
        $tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
         
        $sql = "DELETE FROM `".$tbl["track_e_access"]."`";
        claro_sql_query($sql);
        $sql = "DELETE FROM `".$tbl["track_e_downloads"]."`";
        claro_sql_query($sql);
        $sql = "DELETE FROM `".$tbl["track_e_exe_answers"]."`";
        claro_sql_query($sql);
        $sql = "DELETE FROM `".$tbl["track_e_exe_details"]."`";
        claro_sql_query($sql);
        $sql = "DELETE FROM `".$tbl["track_e_exercices"]."`";
        claro_sql_query($sql);
        $sql = "DELETE FROM `".$tbl["track_e_uploads"]."`";
        claro_sql_query($sql);
    }
    function import_track_in_db($tab, $course_id = NULL)
    {
        $tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
         
        if (isset ($tab['track_e_access']) && is_array($tab['track_e_access']) && (count($tab['track_e_access']) > 0))
        {
            foreach ($tab['track_e_access'] as $tab_content)
            {
                $sql = "INSERT INTO `".$tbl["track_e_access"].'` (access_user_id,access_date,access_tid,
                    access_tlabel)
                    VALUES ("'.(int) $tab_content['access_user_id'].'","'.$tab_content['access_date'].'","'.(int) $tab_content['access_tid'].'","'.addslashes($tab_content['access_tlabel']).'")';
                 
                claro_sql_query($sql);
            }
        }
        if (isset ($tab['track_e_downloads']) && is_array($tab['track_e_downloads']) && (count($tab['track_e_downloads']) > 0))
            {
            foreach ($tab['track_e_downloads'] as $tab_content)
            {
                $sql = "INSERT INTO `".$tbl["track_e_downloads"].'` (down_user_id,down_date,down_doc_path)
                    VALUES ("'.(int) $tab_content['down_user_id'].'","'.$tab_content['down_date'].'","'.addslashes($tab_content['down_doc_path']).'")';
                 
                claro_sql_query($sql);
            }
        }
        if (isset ($tab['track_e_exe_answers']) && is_array($tab['track_e_exe_answers']) && (count($tab['track_e_exe_answers']) > 0))
        {
            foreach ($tab['track_e_exe_answers'] as $tab_content)
            {
                $sql = "INSERT INTO `".$tbl["track_e_exe_answers"].'` (details_id,answer)
                    VALUES ("'.(int) $tab_content['details_id'].'","'.addslashes($tab_content['answer']).'")';
                 
                claro_sql_query($sql);
            }
        }
        if (isset ($tab['track_e_exe_details']) && is_array($tab['track_e_exe_details']) && (count($tab['track_e_exe_details']) > 0))
        {
            foreach ($tab['track_e_exe_details'] as $tab_content)
            {
                $sql = "INSERT INTO `".$tbl["track_e_exe_details"].'` (exercise_track_id,question_id,result)
                    VALUES ("'.(int) $tab_content['exercise_track_id'].'","'.(int) $tab_content['question_id'].'","'.(int) $tab_content['result'].'")';
                 
                claro_sql_query($sql);
            }
        }
        if (isset ($tab['track_e_exercices']) && is_array($tab['track_e_exercices']) && (count($tab['track_e_exercices']) > 0))
        {
            foreach ($tab['track_e_exercices'] as $tab_content)
            {
                $sql = "INSERT INTO `".$tbl["track_e_exe_details"].'` (exe_user_id,exe_date,exe_exo_id,exe_result,
                    exe_time,exe_weighting)
                    VALUES ("'.(int) $tab_content['exe_user_id'].'","'.(int) $tab_content['exe_date'].'","'.(int) $tab_content['exe_exo_id'].'","'.(int) $tab_content['exe_result'].'","'.(int) $tab_content['exe_time'].'","'.(int) $tab_content['exe_weighting'].'")';
                 
                claro_sql_query($sql);
            }
        }
        if (isset ($tab['track_e_uploads']) && is_array($tab['track_e_uploads']) && (count($tab['track_e_exercices']) > 0))
        {
            foreach ($tab['track_e_exercices'] as $tab_content)
            {
                $sql = "INSERT INTO `".$tbl["track_e_uploads"].'` (upload_user_id,upload_date,upload_work_id)
                    VALUES ("'.(int) $tab_content['upload_user_id'].'","'.(int) $tab_content['upload_date'].'","'.(int) $tab_content['upload_work_id'].'")';
                 
                claro_sql_query($sql);
            }
        }
    }
    function import_track_from_file($course_id)
    {
        if (empty ($course_id))
            return claro_failure :: set_failure("Empty dir name");
        $file = EXTRACT_PATH."/".$course_id."/tools/track/track.xml";
         
        $xml = xml_parser_create($GLOBALS['charset']);
         
        $track = new track_parser;
         
        xml_set_object($xml, $track);
        xml_set_element_handler($xml, 'start_element', 'end_element');
        xml_set_character_data_handler($xml, 'get_data');
        xml_parser_set_option($xml, XML_OPTION_CASE_FOLDING, false);
        if (!is_dir(EXTRACT_PATH."/".$course_id))
        {
            return claro_failure :: set_failure("directory does not exist");
        }
        if (!is_file($file))
            return claro_failure :: set_failure("file does not exist");
        if (false == ($fp = fopen($file, 'r')))
            return claro_failure :: set_failure("can't open file");
        while ($data = fread($fp, 4096))
        {
            if (false == xml_parse($xml, $data, feof($fp)))
                return claro_failure :: set_failure("can't parse file");
        }
        fclose($fp);
        xml_parser_free($xml);
        return $track->get_tab();
    }
    function import_bb_in_db($tab, $course_id = NULL)
    {
        $tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
         
         
        if (isset ($tab['bb_categories']) && is_array($tab['bb_categories']) && (count($tab['bb_categories']) > 0))
        {
            foreach ($tab['bb_categories'] as $bb_cat)
            {
                if ($bb_cat['cat_id'] != 1)
                {
                    $sql = "INSERT INTO `".$tbl["bb_categories"].'` (cat_id,cat_title,cat_order)
                        VALUES ('.(int)$bb_cat['cat_id'].',"'.addslashes($bb_cat['cat_title']).'","'.addslashes($bb_cat['cat_order']).'")';
                     
                    claro_sql_query($sql);
                }
            }
        }
        if (isset ($tab['bb_forums']) && is_array($tab['bb_forums']) && (count($tab['bb_forums']) > 0))
        {
            foreach ($tab['bb_forums'] as $bb_forums)
            {
                $sql = "INSERT INTO `".$tbl["bb_forums"].'` (forum_id,group_id,forum_name,forum_desc,forum_access,
                    forum_moderator,forum_topics,forum_posts,
                    forum_last_post_id,cat_id,forum_type,forum_order)
                    VALUES ('.(int)$bb_forums['forum_id'].','. ($bb_forums['group_id'] == null ? "null" : $bb_forums['group_id']).',"'.addslashes($bb_forums['forum_name']).'","'.addslashes($bb_forums['forum_desc']).'","'.(int) $bb_forums['forum_access'].'","'.(int) $bb_forums['forum_moderator'].'","'.(int) $bb_forums['forum_topics'].'","'.(int) $bb_forums['forum_posts'].'","'.(int) $bb_forums['forum_last_post_id'].'","'.(int)$bb_forums['cat_id'].'","'.(int) $bb_forums['forum_type'].'","'.(int) $bb_forums['forum_order'].'")';
                 
                claro_sql_query($sql);
                 
                 
            }
        }
        if (isset ($tab['bb_posts']) && is_array($tab['bb_posts']) && (count($tab['bb_posts']) > 0))
        {
            foreach ($tab['bb_posts'] as $bb_posts)
            {
                $sql = "INSERT INTO `".$tbl["bb_posts"] . "` " . "(post_id,topic_id,forum_id,poster_id,post_time,poster_ip, nom,prenom)" . "VALUES " . "(".(int)$bb_posts['post_id'].",".(int) $bb_posts['topic_id'].',"'.(int) $bb_posts['forum_id'].'","'.(int) $bb_posts['poster_id'].'","'.addslashes($bb_posts['post_time']).'","'.addslashes($bb_posts['poster_ip']).'","'.addslashes($bb_posts['firstname']).'","'.addslashes($bb_posts['lastname']).'")';
                claro_sql_query($sql);
            }
        }
        if (isset ($tab['bb_posts_text']) && is_array($tab['bb_posts_text']) && (count($tab['bb_posts_text']) > 0))
        {
            foreach ($tab['bb_posts_text'] as $bb_posts_text)
            {
                $sql = "INSERT INTO `".$tbl["bb_posts_text"].'` (post_id,post_text)
                    VALUES ("'.(int)$bb_posts_text['post_id'].'","'.addslashes($bb_posts_text['post_text']).'")';
                claro_sql_query($sql);
            }
        }
         
        if (isset ($tab['bb_priv_msgs']) && is_array($tab['bb_priv_msgs']) && (count($tab['bb_priv_msgs']) > 0))
        {
            foreach ($tab['bb_priv_msgs'] as $tab_content)
            {
                $sql = "INSERT INTO `".$tbl["bb_priv_msgs"].'` (from_userid,to_userid,msg_time,poster_ip,msg_status,msg_text)
                    VALUES ("'.(int) $tab_content['from_userid'].'","'.(int) $tab_content['to_userid'].'","'.addslashes($tab_content['msg_time']).'","'.addslashes($tab_content['poster_ip']).'","'.(int) $tab_content['msg_status'].'","'.addslashes($tab_content['msg_text']).'")';
                claro_sql_query($sql);
            }
        }
        if (isset ($tab['bb_rel_topic_userstonotify']) && is_array($tab['bb_rel_topic_userstonotify']) && (count($tab['bb_rel_topic_userstonotify']) > 0))
        {
            foreach ($tab['bb_rel_topic_userstonotify'] as $tab_content)
            {
                $sql = "INSERT INTO `".$tbl["bb_rel_topic_userstonotify"].'` (user_id,topic_id)
                    VALUES ("'.(int) $tab_content['user_id'].'","'.(int) $tab_content['topic_id'].'")';
                claro_sql_query($sql);
            }
        }
        if (isset ($tab['bb_topics']) && is_array($tab['bb_topics']) && (count($tab['bb_topics']) > 0))
        {
            foreach ($tab['bb_topics'] as $bb_topics)
            {
                 
                $sql = "INSERT INTO `".$tbl["bb_topics"].'` (topic_id,topic_title,topic_poster,topic_time,topic_views,
                    topic_replies,topic_last_post_id,forum_id,
                    topic_status,topic_notify,nom,prenom)
                    VALUES ('.(int)$bb_topics['topic_id'].',"'.addslashes($bb_topics['topic_title']).'","'.(int) $bb_topics['topic_poster'].'","'.addslashes($bb_topics['topic_time']).'","'.(int) $bb_topics['topic_views'].'","'.(int) $bb_topics['topic_replies'].'","'.(int) $bb_topics['topic_last_post_id'].'","'.$bb_topics['forum_id'].'","'.(int) $bb_topics['topic_status'].'","'.(int) $bb_topics['topic_notify'].'","'.addslashes($bb_topics['firstname']).'","'.addslashes($bb_topics['lastname']).'")';
                $topic_id = claro_sql_query_insert_id($sql);
            }
        }
        if (isset ($tab['bb_users']) && is_array($tab['bb_users']) && (count($tab['bb_users']) > 0))
        {
            foreach ($tab['bb_users'] as $tab_content)
            {
                $sql = "INSERT INTO `".$tbl["bb_users"].'` (username,user_regdate,user_password,user_email,
                    user_icq,user_website,user_occ,user_from,user_intrest,
                    user_sig,user_viewemail,user_theme,user_aim,user_yim,
                    user_msnm,user_posts,user_attachsig,user_desmile,
                    user_html,user_bbcode,user_rank,user_level,user_lang,
                    user_actkey,user_newpasswd)
                    VALUES ("'.addslashes($tab_content['username']).'","'.addslashes($tab_content['user_regdate']).'","'.addslashes($tab_content['user_password']).'","'.addslashes($tab_content['user_email']).'","'.addslashes($tab_content['user_icq']).'","'.addslashes($tab_content['user_website']).'","'.addslashes($tab_content['user_occ']).'","'.addslashes($tab_content['user_from']).'","'.addslashes($tab_content['user_intrest']).'","'.addslashes($tab_content['user_sig']).'","'.(int) $tab_content['user_viewemail'].'","'.(int) $tab_content['user_theme'].'","'.addslashes($tab_content['user_aim']).'","'.addslashes($tab_content['user_yim']).'","'.addslashes($tab_content['user_msnm']).'","'.(int) $tab_content['user_posts'].'","'.(int) $tab_content['user_attachsig'].'","'.(int) $tab_content['user_desmile'].'","'.(int) $tab_content['user_html'].'","'.(int) $tab_content['user_bbcode'].'","'.(int) $tab_content['user_rank'].'","'.(int) $tab_content['user_level'].'","'.addslashes($tab_content['user_lang']).'","'.addslashes($tab_content['user_actkey']).'","'.addslashes($tab_content['user_newpasswd']).'")';
                claro_sql_query($sql);
            }
        }
         
    }
    function flush_bb_table($course_id = NULL)
    {
        $tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
         
        $sql = "DELETE FROM `".$tbl["bb_categories"]."`";
        claro_sql_query($sql);
        $sql = "DELETE FROM `".$tbl["bb_forums"]."`";
        claro_sql_query($sql);
        $sql = "DELETE FROM `".$tbl["bb_posts"]."`";
        claro_sql_query($sql);
        $sql = "DELETE FROM `".$tbl["bb_posts_text"]."`";
        claro_sql_query($sql);
        $sql = "DELETE FROM `".$tbl["bb_priv_msgs"]."`";
        claro_sql_query($sql);
        $sql = "DELETE FROM `".$tbl["bb_rel_topic_userstonotify"]."`";
        claro_sql_query($sql);
        $sql = "DELETE FROM `".$tbl["bb_topics"]."`";
        claro_sql_query($sql);
        $sql = "DELETE FROM `".$tbl["bb_users"]."`";
        claro_sql_query($sql);
        $sql = "DELETE FROM `".$tbl["bb_whosonline"]."`";
        claro_sql_query($sql);
    }
    function import_bb_from_file($course_id)
    {
        if (empty ($course_id))
            return claro_failure :: set_failure("Empty dir name");
        $file = EXTRACT_PATH."/".$course_id."/tools/forum/forum.xml";
         
        $xml = xml_parser_create($GLOBALS['charset']);
         
        $bb = new bb_parser;
         
        xml_set_object($xml, $bb);
        xml_set_element_handler($xml, 'start_element', 'end_element');
        xml_set_character_data_handler($xml, 'get_data');
        xml_parser_set_option($xml, XML_OPTION_CASE_FOLDING, false);
        if (!is_dir(EXTRACT_PATH."/".$course_id))        
            return claro_failure :: set_failure("directory does not exist");
        
        if (!is_file($file))
            return claro_failure :: set_failure("file does not exist");
        if (false == ($fp = fopen($file, 'r')))
            return claro_failure :: set_failure("can't open file");
        while ($data = fread($fp, 4096))
        {
            if (false == xml_parse($xml, $data, feof($fp)))
                return claro_failure :: set_failure("can't parse file");
        }
        fclose($fp);
        xml_parser_free($xml);
        return $bb->get_tab();
    }
    function import_wiki_in_db($tab, $course_id = NULL)
    {
        $tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
        if (isset ($tab['wiki_properties']) && is_array($tab['wiki_properties']) && (count($tab['wiki_properties']) > 0))
        {  
            foreach ($tab['wiki_properties'] as $tab_content)
            {
                $sql = "INSERT INTO `".$tbl["wiki_properties"].'` (id,title,description,group_id)
                    VALUES ('.($tab_content['id'] == null ?0:(int) $tab_content['id']).',"'.addslashes($tab_content['title']).'","'.addslashes($tab_content['description']).'","'.(int) $tab_content['group_id'].'")';
                claro_sql_query($sql);
                 
            }
        }
         
        if (isset ($tab['wiki_acls']) && is_array($tab['wiki_acls']) && (count($tab['wiki_acls']) > 0))
        {
            foreach ($tab['wiki_acls'] as $tab_content)
            {
                $sql = "INSERT INTO `".$tbl["wiki_acls"].'`(wiki_id,flag,value)
                    VALUES ('.(int) $tab_content['wiki_id'].',"'.addslashes($tab_content['flag']).'","'.addslashes($tab_content['value']).'")';
                 
                claro_sql_query($sql);
            }
        }
        if (isset ($tab['wiki_pages']) && is_array($tab['wiki_pages']) && (count($tab['wiki_pages']) > 0))
        {
            $last_version_id = array ();
             
            foreach ($tab['wiki_pages'] as $id => $tab_content)
            {
                $sql = "INSERT INTO `".$tbl["wiki_pages"].'` (id,wiki_id,owner_id,title,ctime,last_version,last_mtime)
                    VALUES ('.(int) $tab_content['id'].','.(int) $tab_content['wiki_id'].','.(int) $tab_content['owner_id'].',"'.addslashes($tab_content['title']).'","'.$tab_content['ctime'].'",'.(int) $tab_content['last_version'].',"'.$tab_content['last_mtime'].'")';
                 
                claro_sql_query($sql);
                 
            }
        }
        if (isset ($tab['wiki_pages_content']) && is_array($tab['wiki_pages_content']) && (count($tab['wiki_pages_content']) > 0))
        {
            foreach ($tab['wiki_pages_content'] as $tab_content)
            {
                $sql = "INSERT INTO `".$tbl["wiki_pages_content"].'` (id,pid,editor_id,mtime,content)
                    VALUES ('.(int) $tab_content['id'].','.(int) $tab_content['pid'].','.(int) $tab_content['editor_id'].',"'.$tab_content['mtime'].'","'.addslashes($tab_content['content']).'")';
                 
                claro_sql_query($sql);
                 
            }
        }
         
    }
     
    function flush_wiki_table($course_id = NULL)
    {
        $tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
         
        $sql = "DELETE FROM `".$tbl["wiki_acls"]."`";
        claro_sql_query($sql);
        $sql = "DELETE FROM `".$tbl["wiki_pages"]."`";
        claro_sql_query($sql);
        $sql = "DELETE FROM `".$tbl["wiki_pages_content"]."`";
        claro_sql_query($sql);
        $sql = "DELETE FROM `".$tbl["wiki_properties"]."`";
        claro_sql_query($sql);
    }
    function import_wiki_from_file($course_id)
    {
        if (empty ($course_id))
            return claro_failure :: set_failure("Empty dir name");
        $file = EXTRACT_PATH."/".$course_id."/tools/wiki/wiki.xml";
         
        $xml = xml_parser_create($GLOBALS['charset']);
         
        $wiki = new wiki_parser;
         
        xml_set_object($xml, $wiki);
        xml_set_element_handler($xml, 'start_element', 'end_element');
        xml_set_character_data_handler($xml, 'get_data');
        xml_parser_set_option($xml, XML_OPTION_CASE_FOLDING, false);
        if (!is_dir(EXTRACT_PATH."/".$course_id))
        {
            return claro_failure :: set_failure("directory does not exist");
        }
        if (!is_file($file))
            return claro_failure :: set_failure("file does not exist");
        if (false == ($fp = fopen($file, 'r')))
            return claro_failure :: set_failure("can't open file");
        while ($data = fread($fp, 4096))
        {
            if (false == xml_parse($xml, $data, feof($fp)))
                return claro_failure :: set_failure("can't parse file");
        }
        fclose($fp);
        xml_parser_free($xml);
        return $wiki->get_tab();
    }
    /**
     * Import the filtered tab data in DB
     * 
     * @param : $tab = the filtered data work tab
     * 			$course_id = id of the course where the import must occur
     */
    function import_wrk_in_db($tab, $course_id)
    {
        $tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
           
        if (isset ($tab['assignment']) && is_array($tab['assignment']) && (count($tab['assignment']) > 0))
        {
            foreach ($tab['assignment'] as $tab_content)
            {
                $sql = "INSERT INTO `".$tbl["wrk_assignment"].'` (id,title,description,visibility,def_submission_visibility,
                    assignment_type,authorized_content,allow_late_upload,
                    start_date,end_date,prefill_text,prefill_doc_path,
                    prefill_submit)
                    VALUES ('.(int) $tab_content['id'].',"'.addslashes($tab_content['title']).'","'.addslashes($tab_content['description']).'","'.addslashes($tab_content['visibility']).'","'.addslashes($tab_content['def_submission_visibility']).'","'.addslashes($tab_content['assignment_type']).'","'.addslashes($tab_content['authorized_content']).'","'.addslashes($tab_content['allow_late_upload']).'","'.$tab_content['start_date'].'","'.$tab_content['end_date'].'","'.addslashes($tab_content['prefill_text']).'","'.addslashes($tab_content['prefill_doc_path']).'","'.addslashes($tab_content['prefill_submit']).'")';
                 
                claro_sql_query($sql);
            }
        }
        if (isset ($tab['submission']) && is_array($tab['submission']) && (count($tab['submission']) > 0))
        {
        	
            foreach ($tab['submission'] as $tab_content)
            {            	   
                $sql = "INSERT INTO `".$tbl["wrk_submission"].'` (id,assignment_id,parent_id,user_id,group_id,title,visibility,
                    creation_date,last_edit_date,authors,submitted_text,submitted_doc_path,
                    private_feedback,original_id,score)
                    VALUES ('.(int) $tab_content['id'].','.(int) $tab_content['assignment_id'].',"'.(int) $tab_content['parent_id'].'","'.(int) $tab_content['user_id'].'",'.(null == $tab_content['group_id']?"null":"'".(int) $tab_content['group_id']."'").',"'.addslashes($tab_content['title']).'","'.addslashes($tab_content['visibility']).'","'.$tab_content['creation_date'].'","'.$tab_content['last_edit_date'].'","'.addslashes($tab_content['authors']).'","'.addslashes($tab_content['submitted_text']).'","'.addslashes($tab_content['submitted_doc_path']).'","'.addslashes($tab_content['private_feedback']).'",'.($tab_content['original_id'] == ""? "null" : (int) $tab_content['original_id'].'"').',"'.(int) $tab_content['score'].'")';          
                claro_sql_query($sql);
            }
        }
    }
    /**
     * Flush the work tables    
     */
    function flush_wrk_table($course_id)    
    {
        $tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
         
        $sql = "DELETE FROM `".$tbl["wrk_assignment"]."`";
        claro_sql_query($sql);
        $sql = "DELETE FROM `".$tbl["wrk_submission"]."`";
        claro_sql_query($sql);
    }
    function import_wrk_from_file($course_id)
    {
        if (empty ($course_id))
            return claro_failure :: set_failure("Empty dir name");
        $file = EXTRACT_PATH."/".$course_id."/tools/work/work.xml";
         
        $xml = xml_parser_create($GLOBALS['charset']);
         
        $wrk = new wrk_parser;
         
        xml_set_object($xml, $wrk);
        xml_set_element_handler($xml, 'start_element', 'end_element');
        xml_set_character_data_handler($xml, 'get_data');
        xml_parser_set_option($xml, XML_OPTION_CASE_FOLDING, false);
        if (!is_dir(EXTRACT_PATH."/".$course_id))
        {
            return claro_failure :: set_failure("directory does not exist");
        }
        if (!is_file($file))
            return claro_failure :: set_failure("file does not exist");
        if (false == ($fp = fopen($file, 'r')))
            return claro_failure :: set_failure("can't open file");
        while ($data = fread($fp, 4096))
        {
            if (false == xml_parse($xml, $data, feof($fp)))
                return claro_failure :: set_failure("can't parse file");
        }
        fclose($fp);
        xml_parser_free($xml);
        return $wrk->get_tab();
    }
    function import_manifest_in_db($tab, $course_id = NULL)
    {
        global $_course;
         
        $tbl = claro_sql_get_main_tbl();
        $sql = "SELECT cours_id FROM `".$tbl["course"]."`
            WHERE code ='".$_course["sysCode"]."' ";
        $result = claro_sql_query_fetch_all($sql);
         
        //if(count($result) === 0){
        $sql = "INSERT INTO `".$tbl["course"].'` (code,fake_code,directory,dbName,languageCourse,intitule,
            faculte,visible,enrollment_key,titulaires,email,departmentUrlName,
            departmentUrl,diskQuota,versionDb,versionClaro,lastVisit,lastEdit,
            creationDate,expirationDate)
            VALUES ("'.addslashes($tab['code']).'","'.addslashes($tab['fake_code']).'","'.addslashes($tab['directory']).'","'.addslashes($tab['dbName']).'","'.addslashes($tab['languageCourse']).'","'.addslashes($tab['intitule']).'","'.addslashes($tab['faculte']).'","'.addslashes($tab['visible']).'","'.addslashes($tab['enrollment_key']).'","'.addslashes($tab['titulaires']).'","'.addslashes($tab['email']).'","'.addslashes($tab['departmentUrlName']).'","'.addslashes($tab['departmentUrl']).'","'.(int) $tab['diskQuota'].'","'.addslashes($tab['versionDb']).'","'.addslashes($tab['versionClaro']).'","'.$tab['lastVisit'].'","'.$tab['lastEdit'].'","'.$tab['creationDate'].'","'.$tab['expirationDate'].'")';
         
        claro_sql_query($sql);
        /*}
        else
        {
         
        $sql = "REPLACE INTO `".$tbl["course"].'` (cours_id,code,fake_code,directory,dbName,languageCourse,intitule,
        faculte,visible,enrollment_key,titulaires,email,departmentUrlName,
        departmentUrl,diskQuota,versionDb,versionClaro,lastVisit,lastEdit,
        creationDate,expirationDate)
        VALUES ("'.
        (int)$result[0]['cours_id'].'","'.
        addslashes($tab['code']).'","'.
        addslashes($tab['fake_code']).'","'.
        addslashes($tab['directory']).'","'.
        addslashes($tab['dbName']).'","'.
        addslashes($tab['languageCourse']).'","'.
        addslashes($tab['intitule']).'","'.
        addslashes($tab['faculte']).'","'.
        addslashes($tab['visible']).'","'.
        addslashes($tab['enrollment_key']).'","'.
        addslashes($tab['titulaires']).'","'.
        addslashes($tab['email']).'","'.
        addslashes($tab['departmentUrlName']).'","'.
        addslashes($tab['departmentUrl']).'","'.
        (int)$tab['diskQuota'].'","'.
        addslashes($tab['versionDb']).'","'.
        addslashes($tab['versionClaro']).'","'.
        $tab['lastVisit'].'","'.
        $tab['lastEdit'].'","'.
        $tab['creationDate'].'","'.
        $tab['expirationDate'].'")';
         
        claro_sql_query($sql);
        }*/
    }
     
    function import_manifest_from_file($course_id)
    {
        if (empty ($course_id))
            return claro_failure :: set_failure("Empty dir name");
        $file = EXTRACT_PATH."/".$course_id."/meta_data/manifest/manifest.xml";
         
        $xml = xml_parser_create($GLOBALS['charset']);
         
        $manifest = new manifest_parser;
         
        xml_set_object($xml, $manifest);
        xml_set_element_handler($xml, 'start_element', 'end_element');
        xml_set_character_data_handler($xml, 'get_data');
        xml_parser_set_option($xml, XML_OPTION_CASE_FOLDING, false);
        if (!is_dir(EXTRACT_PATH."/".$course_id))
            {
            return claro_failure :: set_failure("directory does not exist");
        }
        if (!is_file($file))
            return claro_failure :: set_failure("file does not exist");
        if (false == ($fp = fopen($file, 'r')))
            return claro_failure :: set_failure("can't open file");
        while ($data = fread($fp, 4096))
        {
            if (false == xml_parse($xml, $data, feof($fp)))
                return claro_failure :: set_failure("can't parse file");
        }
        fclose($fp);
        xml_parser_free($xml);
        return $manifest->get_tab();
    }
     
    function import_users_from_file($course_id)
    {
        if (empty ($course_id))
            return claro_failure :: set_failure("Empty dir name");
        $file = EXTRACT_PATH."/".$course_id."/meta_data/users/users.xml";
         
        $xml = xml_parser_create($GLOBALS['charset']);
         
        $users = new users_parser;
         
        xml_set_object($xml, $users);
        xml_set_element_handler($xml, 'start_element', 'end_element');
        xml_set_character_data_handler($xml, 'get_data');
        xml_parser_set_option($xml, XML_OPTION_CASE_FOLDING, false);
        if (!is_dir(EXTRACT_PATH."/".$course_id))
        {
            return claro_failure :: set_failure("directory does not exist");
        }
        if (!is_file($file))
            return claro_failure :: set_failure("file does not exist");
        if (false == ($fp = fopen($file, 'r')))
            return claro_failure :: set_failure("can't open file");
        while ($data = fread($fp, 4096))
        {
            if (false == xml_parse($xml, $data, feof($fp)))
                return claro_failure :: set_failure("can't parse file");
        }
        fclose($fp);
        xml_parser_free($xml);
        return $users->get_tab();
    }
    function import_users_in_db($tab,$course_id,$usersIdToChange)
    {
        $tbl = claro_sql_get_main_tbl();
                  
        if (is_array($tab))
        {        
            foreach ($usersIdToChange as $tab_content)
            {                       	            	      	    
            	if(isset($tab_content['mustImportUser']) && true === $tab_content['mustImportUser'])
            	{                    		    	                        	
            		$data_user = $tab['user'][$tab_content['oldUserId']] ;
            		$data_rel_course_tab = $tab['rel_course_user'][$tab_content['oldUserId']];            		
                	$sql = "INSERT INTO `".$tbl["user"].'` (user_id,nom,prenom,username,password,authsource,email,statut,officialCode,phoneNumber,
                    										pictureUri,creatorId)
                    	VALUES ('.(int) $tab_content['newUserId'].',"'.addslashes($data_user['firstname']).'","'.addslashes($data_user['lastname']).'","'.addslashes($data_user['username']).'","'.addslashes($data_user['password']).'","'.addslashes($data_user['authSource']).'","'.addslashes($data_user['email']).'","'.$data_user['statut'].'","'.addslashes($data_user['officialCode']).'","'.addslashes($data_user['phoneNumber']).'","'.addslashes($data_user['pictureUri']).'","'.(int) $data_user['creatorId'].'")';
                 
                	claro_sql_query($sql);
                	$sql = "INSERT INTO `".$tbl["rel_course_user"]."` (code_cours,user_id,statut,role,team,tutor)
                			VALUES ('".addslashes($course_id)."',".(int)$tab_content['newUserId'].",".(int)$data_rel_course_tab['statut'].",".
                					 ($data_rel_course_tab['role'] == ""? "null" : "'".addslashes($data_rel_course_tab['role'])."'").",".(int)$data_rel_course_tab['team'].",".(int)$data_rel_course_tab['tutor'].")";
					claro_sql_query($sql);                				               	
            	}
            }         
        }
    }
  
    function replaceUserId($usersIdToChange, $tab, $index)
    {     
        foreach ($usersIdToChange as $tab_content)
        {
            foreach ($tab as $lacle => $tab2_content)
            {
                if ($tab2_content[$index] == $tab_content["oldUserId"])
                {
                    $tab[$lacle][$index] = $tab_content["newUserId"];
                }
            }
        }
        return $tab;
    }
    function replaceGroupId($importGroupInfo, $tab, $index)
    {
        foreach ($tab as $lacle => $tab2_content)
        {             
            if ($tab2_content[$index] == $importGroupInfo["oldId"])
            {                 
                $tab[$lacle][$index] = $importGroupInfo["id"];
            }
        }
        return $tab;
    }
    function import_documents($toolName, $imported_course_id, $course_id, $importGroupInfoInfo)
    {
        if (null === $importGroupInfoInfo['id'])
        {
            $course_path = get_conf("rootSys")."courses/".$course_id;
            $archive_path = EXTRACT_PATH."/".$imported_course_id."/tools/".$toolName."/";
            if (file_exists($archive_path.$toolName.".zip"))
            {
                if (false === extract_archive($archive_path.$toolName.".zip", $course_path))
                    claro_failure :: set_failure("cant_extract_file");
                else
                    return true;
            }
        }
        return true;
    }
    function clearGroupFiles($course_id)
    {
        $importGroupInfo_path = get_conf("rootSys").'courses/'.$course_id.'/group';
        claro_delete_file($importGroupInfo_path);
        claro_mkdir($importGroupInfo_path);
    }
    function importGroupDocuments($imported_courseId, $courseId, $importGroupInfo)
    {
        if (isset($importGroupInfo['chat']) && 'true' == $importGroupInfo['chat'])
            if (false === importChatDocuments($imported_courseId, $courseId, $importGroupInfo))
            return false;
         
        if (isset($importGroupInfo['document']) && 'true' == $importGroupInfo['document'])
            if (false === importDocDocuments($imported_courseId, $courseId, $importGroupInfo))
            return false;
         
        if ($importGroupInfo['id'] == null && isset ($importGroupInfo['exercise']) && 'true' == $importGroupInfo['exercise'])
            if (false === import_documents("exercise", $imported_courseId, $courseId, $importGroupInfo))
            return false;
        if ($importGroupInfo['id'] == null && isset ($importGroupInfo['modules']) && 'true' == $importGroupInfo['modules'])
            if (false === import_documents("modules", $imported_courseId, $courseId, $importGroupInfo))
            return false;
        if ($importGroupInfo['id'] == null && isset ($importGroupInfo['work']) && 'true' == $importGroupInfo['work'])
            if (false === import_documents("work", $imported_courseId, $courseId, $importGroupInfo))
            return false;
        if ($importGroupInfo['id'] == null && isset ($importGroupInfo['quiz']) && 'true' == $importGroupInfo['quiz'])
            if (false === import_documents("quiz", $imported_courseId, $courseId, $importGroupInfo))
            return false;
        return true;
    }
    function importDocDocuments($imported_course_id, $course_id, $importGroupInfoInfo)
    {
         
        $course_path = get_conf("rootSys").'courses/'.$course_id;
        if (null == $importGroupInfoInfo['id'])
        {
            $archive_path = EXTRACT_PATH."/".$imported_course_id.'/tools/document/';
             
            if (file_exists($archive_path.'document.zip'))
            {
                if (false === extract_archive($archive_path.'document.zip', $archive_path))
                    return claro_failure :: set_failure("cant_extract_file");
                else
                {
                    claro_delete_file($course_path.'/document');
                    claro_copy_file($archive_path.'/document/', $course_path);
                    claro_delete_file($archive_path.'/document/');
                }
            }
        }
        else
            {
            $archive_path = EXTRACT_PATH."/".$imported_course_id.'/tools/group/';
            $importGroupInfo_path = $course_path.'/group/'.$importGroupInfoInfo['directory'];
             
            if (false === extract_archive($archive_path.'group.zip', $archive_path))
                return claro_failure :: set_failure("cant_extract_file");
            else
                {
                if (false === claro_delete_file($importGroupInfo_path))
                return false;
                if (false === claro_copy_file($archive_path.'group/'.$importGroupInfoInfo['directory'], $course_path.'/group'))
                return false;
                if (false === claro_delete_file($archive_path.'group/'))
                return false;
            }
        }
    }
    function importChatDocuments($imported_course_id, $course_id, $importGroupInfoInfo)
    {
         
        $course_path = get_conf("rootSys").'courses/'.$course_id;
        $chat_path = $course_path.'/chat';
        $archive_path = EXTRACT_PATH."/".$imported_course_id.'/tools/chat/';
        if (file_exists($archive_path.'chat.zip'))
            {
             
            if (false === extract_archive($archive_path.'chat.zip', $archive_path))
                return claro_failure :: set_failure("cant_extract_file");
            else
                {
                if (null == $importGroupInfoInfo['id'] && file_exists($archive_path.'chat/'.$imported_course_id.'.chat.html'))
                    {
                    if (false === claro_delete_file($chat_path))
                    return false;
                    if (false === claro_mkdir($chat_path, 0777, true))
                    return false;
                    if (false === copy($archive_path.'chat/'.$imported_course_id.'.chat.html', $chat_path.'/'.$imported_course_id.'.chat.html'))
                    return false;
                }
                elseif (file_exists($archive_path.'chat/'.$imported_course_id.'.'.$importGroupInfoInfo['oldId'].'.chat.html'))
                {
                    if (false === claro_delete_file($chat_path))
                    return false;
                    if (false === claro_mkdir($chat_path))
                    return false;
                    if (false === copy($archive_path.'chat/'.$imported_course_id.'.'.$importGroupInfoInfo['oldId'].'.chat.html', $chat_path.'/'.$course_id.'.'.$importGroupInfoInfo['id'].'.chat.html'))
                    return false;
                }
                else
                    {
                    // nothing to do
                }
                return true;
            }
            claro_delete_file($archive_path.'/chat');
        }
        return true;
    }
    function filterWikiTab($tab_origine, $importGroupInfo)
    {
        $tab_fin = array();       
        if (isset ($tab_origine['wiki_properties']))
        {
        	             
            foreach ($tab_origine['wiki_properties'] as $wiki_id => $tab_content)
            {
            	
                $mustImportwiki = false;
                if ($tab_content['group_id'] == $importGroupInfo['id'] && isset($importGroupInfo['wiki']) && true === $importGroupInfo['wiki'])
                {                	
                    $tab_fin['wiki_properties'][$wiki_id] = $tab_origine['wiki_properties'][$wiki_id];
                    $mustImportwiki = true;
                }
                if (isset ($tab_origine['wiki_acls']))
                {
                    foreach ($tab_origine['wiki_acls'] as $id => $tab_content)
                    {
                        if ($tab_content['wiki_id']['wiki_id'] == $wiki_id && $mustImportwiki)
                            {
                            $tab_fin['wiki_acls'][$id] = $tab_origine['wiki_acls'][$id];
                        }
                    }
                }
                if (isset ($tab_origine['wiki_pages']))
                {
                    foreach ($tab_origine['wiki_pages'] as $pageId => $tab_content)
                    {
                        if ($tab_content['wiki_id'] == $wiki_id && $mustImportwiki)
                        {
                            $tab_fin['wiki_pages'][$pageId] = $tab_origine['wiki_pages'][$pageId];
                            $last_version = $tab_origine['wiki_pages'][$pageId]['last_version'];
                            if (isset ($tab_origine['wiki_pages_content']))
                            {
                                foreach ($tab_origine['wiki_pages_content'] as $id => $tab_content)
                                {
                                    if ($tab_content['pid'] == $pageId)
                                    {
                                        $tab_fin['wiki_pages_content'][$id] = $tab_origine['wiki_pages_content'][$id];
                                    }
                                     
                                    if ($tab_content['id'] == $last_version)
                                    {
                                        $tab_fin['wiki_pages_content'][$id] = $tab_origine['wiki_pages_content'][$id];
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return $tab_fin;
    }
     
    function set_wikiIds($course_id, $tab)
    {
        $tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
         
        $sql = "SELECT max(id) FROM `".$tbl['wiki_properties']."`";
        $wiki_properties_offset = claro_sql_query_get_single_value($sql);
        if (!isset($wiki_properties_offset)) $wiki_properties_offset = 0;
        $sql = "SELECT max(id) FROM `".$tbl['wiki_pages']."`";
        $wiki_pages_offset = claro_sql_query_get_single_value($sql);
        if (!isset($wiki_pages_offset)) $wiki_pages_offset = 0;
        $sql = "SELECT max(id) FROM `".$tbl['wiki_pages_content']."`";
        $wiki_pages_content_offset = claro_sql_query_get_single_value($sql);
        if (!isset($wiki_pages_content_offset)) $wiki_pages_content_offset = 0;
         
        if (isset ($tab['wiki_properties']) && is_array($tab['wiki_properties']) && (count($tab['wiki_properties']) > 0))
        {
            foreach ($tab['wiki_properties'] as $lacle => $wiki_properties)
            {
                $tab['wiki_properties'][$lacle]['id'] = $wiki_properties['id'] + $wiki_properties_offset;
            }
        }
        if (isset ($tab['wiki_acls']) && is_array($tab['wiki_acls']) && (count($tab['wiki_acls']) > 0))
        {
            foreach ($tab['wiki_acls'] as $lacle => $wiki_acls)
            {
                $tab['wiki_acls'][$lacle]['wiki_id']['wiki_id'] = $wiki_acls['wiki_id']['wiki_id'] + $wiki_properties_offset;
            }
        }
        if (isset ($tab['wiki_pages']) && is_array($tab['wiki_pages']) && (count($tab['wiki_pages']) > 0))
        {
            foreach ($tab['wiki_pages'] as $lacle => $wiki_pages)
            {
                $tab['wiki_pages'][$lacle]['id'] = $wiki_pages['id'] + $wiki_pages_offset;
                $tab['wiki_pages'][$lacle]['wiki_id'] = $wiki_pages['wiki_id'] + $wiki_properties_offset;
                $tab['wiki_pages'][$lacle]['last_version'] = $wiki_pages['last_version'] + $wiki_pages_content_offset;
            }
        }
        if (isset ($tab['wiki_pages_content']) && is_array($tab['wiki_pages_content']) && (count($tab['wiki_pages_content']) > 0))
        {
            foreach ($tab['wiki_pages_content'] as $lacle => $wiki_content)
            {
                $tab['wiki_pages_content'][$lacle]['id'] = $wiki_content['id'] + $wiki_pages_content_offset;
                $tab['wiki_pages_content'][$lacle]['pid'] = $wiki_content['pid'] + $wiki_pages_offset;
            }
        }
        return $tab;
    }
    function set_bbIds($course_id, $tab)
    {
        $tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
         
        $sql = "SELECT max(cat_id) FROM `".$tbl['bb_categories']."`";
        $bb_catergories_offset = claro_sql_query_get_single_value($sql);
        if (!isset($bb_catergories_offset)) $bb_catergories_offset = 0;
        $sql = "SELECT max(forum_id) FROM `".$tbl['bb_forums']."`";
        $bb_forums_offset = claro_sql_query_get_single_value($sql);
        if (!isset($bb_forums_offset)) $bb_forums_offset = 0;
        $sql = "SELECT max(post_id) FROM `".$tbl['bb_posts']."`";
        $bb_posts_offset = claro_sql_query_get_single_value($sql);
        if (!isset($bb_posts_offset)) $bb_posts_offset = 0;
        $sql = "SELECT max(topic_id) FROM `".$tbl['bb_topics']."`";
        $bb_topics_offset = claro_sql_query_get_single_value($sql);
        if (!isset($bb_topics_offset)) $bb_topics_offset = 0;
         
        if (isset ($tab['bb_categories']) && is_array($tab['bb_categories']) && (count($tab['bb_categories']) > 0))
        {
            foreach ($tab['bb_categories'] as $lacle => $bb_cat)
            {                
                if ($bb_cat['cat_id'] != 1)
                	$tab['bb_categories'][$lacle]['cat_id'] = $bb_cat['cat_id'] + $bb_catergories_offset;
            }
        }
        if (isset ($tab['bb_forums']) && is_array($tab['bb_forums']) && (count($tab['bb_forums']) > 0))
        {
            foreach ($tab['bb_forums'] as $lacle => $bb_forums)
            {
                if ($bb_forums['cat_id'] != 1)
                	$tab['bb_forums'][$lacle]['cat_id'] = $bb_forums['cat_id'] + $bb_catergories_offset;
                $tab['bb_forums'][$lacle]['forum_id'] = $bb_forums['forum_id'] + $bb_forums_offset;
                $tab['bb_forums'][$lacle]['forum_last_post_id'] = $bb_forums['forum_last_post_id'] + $bb_posts_offset;
            }
        }
        if (isset ($tab['bb_posts']) && is_array($tab['bb_posts']) && (count($tab['bb_posts']) > 0))
        {
            foreach ($tab['bb_posts'] as $lacle => $bb_posts)
            {
                $tab['bb_posts'][$lacle]['post_id'] = $bb_posts['post_id'] + $bb_posts_offset;
                $tab['bb_posts'][$lacle]['topic_id'] = $bb_posts['topic_id'] + $bb_topics_offset;
                $tab['bb_posts'][$lacle]['forum_id'] = $bb_posts['forum_id'] + $bb_forums_offset;
            }
        }
        if (isset ($tab['bb_posts_text']) && is_array($tab['bb_posts_text']) && (count($tab['bb_posts_text']) > 0))
        {
            foreach ($tab['bb_posts_text'] as $lacle => $bb_posts_text)
            {
                $tab['bb_posts_text'][$lacle]['post_id'] = $bb_posts_text['post_id'] + $bb_posts_offset;
            }
        }
        if (isset ($tab['bb_topics']) && is_array($tab['bb_topics']) && (count($tab['bb_topics']) > 0))
        {
            foreach ($tab['bb_topics'] as $lacle => $bb_topics)
            {
                $tab['bb_topics'][$lacle]['topic_id'] = $bb_topics['topic_id'] + $bb_topics_offset;
                $tab['bb_topics'][$lacle]['topic_last_post_id'] = $bb_topics['topic_last_post_id'] + $bb_posts_offset;
                $tab['bb_topics'][$lacle]['forum_id'] = $bb_topics['forum_id'] + $bb_forums_offset;
            }
        }
        if (isset ($tab['bb_rel_topic_userstonotify']) && is_array($tab['bb_rel_topic_userstonotify']) && (count($tab['bb_rel_topic_userstonotify']) > 0))
        {
            foreach ($tab['bb_rel_topic_userstonotify'] as $bb_rel)
            {
                $tab['bb_rel_topic_userstonotify'][$lacle]['topic_id'] = $bb_rel['topic_id'] + $bb_topics_offset;
            }
        }
        return $tab;
    }
     /*
    function get_oldGroupId($course_id, $importGroupInfoInfo, $tab)
    {
        $tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
        $sql = "SELECT name,id FROM `".$tbl['group_team']."`";
        $result = claro_sql_query_fetch_all($sql);
         
        foreach($result as $tab_content)
        {
            foreach($tab[0]['group_team'] as $importGroupInfo_content)
            {
                if ($tab_content['name'] === $importGroupInfo_content['name'])
                {
                    $importGroupInfoInfo[$importGroupInfo_content['id']]['oldIdInDb'] = $tab_content['id'];
                }
            }
        }
         
        return $importGroupInfoInfo;
    }*/
?>