<?php
    require_once $includePath.'/../export/export_zip.lib.php';
    require_once $includePath.'/../wiki/lib/lib.createwiki.php';
    require_once ($includePath.'/lib/pclzip/pclzip.lib.php');
    require_once ($includePath.'/lib/claro_main.lib.php');
    require_once $includePath.'/lib/fileManage.lib.php';
    require_once $includePath.'/lib/forum.lib.php';
     
    require $includePath.'/lib/debug.lib.inc.php';
    require $includePath.'/lib/group.lib.inc.php';
     
    define("EXTRACT_PATH", 'C:\Program Files\EasyPHP1-8\www\cvs\claroline.test\claroline\export');
     
    function import_all_data_course_in_db($archive_file, $course_id = NULL)
    {
        $exported_course_id = basename($archive_file, '.zip');
        //if (false == test_zip_file($archive_file)) return false;
        if (false === extract_archive($archive_file, EXTRACT_PATH))
            return false;
        
        $usersIdToChange[0]["oldUserId"] = 1;
        $usersIdToChange[0]["newUserId"] = 1;
        $usersIdToChange = import_users($exported_course_id, $usersIdToChange);
        if (false === $usersIdToChange)
            return false;
         
        $group[0]['id'] = 0;
        $group[0]['oldid'] = 0;
        $group[0]['chat'] = true;
        $group[0]['document'] = true;
        $group[0]['forum'] = true;
        $group[0]['wiki'] = true;
        $group[0]['exercise'] = true;
        $group[0]['work'] = true;
        $group[0]['tool'] = true;
        $group[0]['group'] = true;
        $group[0]['quiz'] = true;      
        $group[0]['lp'] = true;  
     //	$group[0]['mustImportUsers'] = true;
         /*
        $group[1]['id'] = 1;
        $group[1]['oldid'] = 1;
        $group[1]['mustImportUsers'] = true;
        $group[1]['mustImportTools'] = false;
        $group[1]['chat'] = true;
        $group[1]['document'] = true;
        $group[1]['forum'] = true;
        $group[1]['wiki'] = true;
         
        $group[2]['id'] = 2;
        $group[2]['oldid'] = 2;
        $group[2]['mustImportUsers'] = false;
        $group[2]['mustImportTools'] = false;
        $group[2]['chat'] = true;
        $group[2]['document'] = true;
        $group[2]['forum'] = true;
        $group[2]['wiki'] = true;*/
         
        if (isset($group[0]['group']) && true === $group[0]['group'])
        $group = import_group($exported_course_id, $GLOBALS['_cid'], $usersIdToChange, $group);
         
        if (false === $group)
            return false;
         
        if (false == importGroupDocuments($exported_course_id, $course_id, $group[0]))
            return false;
         
        /*
        if (false === import_manifest($exported_course_id, $course_id, $groupInfo))
        return false;
        */
        if (false === import_announcement($exported_course_id, $GLOBALS['_cid'], $group[0]))
            return false;
         
        if (false === import_course_description($exported_course_id, $GLOBALS['_cid'], $group[0]))
            return false;
         
        if (false === import_calendar($exported_course_id, $GLOBALS['_cid'], $group[0]))
            return false;
         
        if (false === import_link($exported_course_id, $GLOBALS['_cid'], $group[0]))
            return false;
         
        if (false === import_lp($exported_course_id, $GLOBALS['_cid'], $group[0], $usersIdToChange))
            return false;
         
        if (false === import_quiz($exported_course_id, $GLOBALS['_cid'], $group[0]))
            return false;
         
        if (false === import_tool($exported_course_id, $GLOBALS['_cid'], $group[0]))
            return false;
         
        if (false === import_document($exported_course_id, $GLOBALS['_cid'], $group[0]))
            return false;
         
        if (false == import_bb($exported_course_id, $GLOBALS['_cid'], $group[0], $usersIdToChange))
            return false;
         
        if (false == import_wiki($exported_course_id, $GLOBALS['_cid'], $group[0], $usersIdToChange))
            return false;
         
        if (false === import_wrk($exported_course_id, $GLOBALS['_cid'], $group[0], $usersIdToChange))
            return false;
         
        if (false === import_userinfo($exported_course_id, $GLOBALS['_cid'], $group[0], $usersIdToChange))
            return false;
         
        // claro_delete_file(EXTRACT_PATH."/".$exported_course_id);
         
        return true;
    }
    function isToolInTab($toolListTab, $tool)
    {
        foreach ($toolListTab as $tab_content)
        {
            if ($tab_content === $tool)
                {
                return true;
            }
        }
        return false;
    }
    function filter_users($tab, $usersIdToChange)
    {
        // [0] = user to add
        // [1] = tab with oldUserId and newUserId
        $tbl = array ();
        $tbl[0] = array ();
        $tbl[0] = $tab;
         
        $tbl[1] = $usersIdToChange;
        return $tbl;
    }
    function import_users($exported_course_id, $usersIdToChange)
    {
        //import users from file in a tab
        $tab = import_users_from_file($exported_course_id);
        if (false !== $tab)
        {
            //filter users and put it in a new tab
            $tab = filter_users($tab, $usersIdToChange);
            //put users in db
            import_users_in_db($tab[0]);
        }
        else
        return false;
         
        return $tab[1];
    }
    function import_announcement($exported_course_id, $course_id = NULL, $group)
    {
        flush_announcement_table($course_id);
        if (isset ($group["announcement"]) && true == $group["announcement"])
        {
            $tab = import_announcement_from_file($exported_course_id);
             
            if (false !== $tab)              
                import_announcement_in_db($tab, $course_id);            
            else return false;
        }
        return true;
    }
    function import_course_description($exported_course_id, $course_id = NULL, $group)
    {
        flush_course_description_table($course_id);
         
        if (isset ($group["description"]) && true == $group["description"])
        {
            $tab = import_course_description_from_file($exported_course_id);
            if (false !== $tab)
                import_course_description_in_db($tab, $course_id);                        
            else return false;
        }
        return true;
    }
    function import_calendar($exported_course_id, $course_id = NULL, $group)
    {
        flush_calendar_table($course_id);
         
        if (isset ($group["calendar"]) && true == $group["calendar"])
        {
            $tab = import_calendar_from_file($exported_course_id);
            if (false !== $tab)                
                import_calendar_in_db($tab, $course_id);            
            else return false;
        }
         
        return true;
    }
    function import_link($exported_course_id, $course_id = NULL, $group)
    {
        flush_link_table($course_id);
         
        if (isset ($group["link"]) && true == $group["link"])
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
    function import_lp($exported_course_id, $course_id = NULL, $group, $usersIdToChange)
    {
        flush_lp_table($course_id);
         
        if (isset ($group["lp"]) && true == $group["lp"])
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
    function import_quiz($exported_course_id, $course_id = NULL, $group)
    {
        flush_quiz_table($course_id);
         
        if (isset ($group["quiz"]) && true == $group["quiz"])
        {
            $tab = import_quiz_from_file($exported_course_id);
            if (false !== $tab) import_quiz_in_db($tab, $course_id);
            if (false !== $tab) set_quizIds($tab, $course_id);
            
            else
            return false;
        }
        return true;
    }
    function import_tool($exported_course_id, $course_id = NULL, $group)
    {
        flush_tool_table($course_id);
         
        if (isset ($group["tool"]) && true == $group["tool"])
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
    function import_document($exported_course_id, $course_id = NULL, $group)
    {
        flush_document_table($course_id);
         
        if (isset ($group["document"]) && true == $group["document"])
            {
            if (false === importDocDocuments($exported_course_id, $course_id, $group))
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
    function filter_group($group_data, $group)
    {
        $tbl = array();
        $tbl[0] = null;
         
        if (isset ($group_data["group_team"]))
            {
            foreach ($group_data["group_team"] as $tab2_content)
            {
                if ($group["id"] == $tab2_content["id"])
                    {
                    $tbl[0]["group_team"] = $tab2_content;
                     
                    $tbl[1] = $group;
                    $tbl[1]['id'] = '';
                    $tbl[1]['oldid'] = $tab2_content['id'];
                    if (isset($group['chat'])) $tbl[1]['chat'] = $group['chat'];
                    else $tbl[1]['chat'] = false;
                    if (isset($group['document'])) $tbl[1]['document'] = $group['document'];
                    else $tbl[1]['document'] = false;
                    $tbl[1]['directory'] = $tab2_content['secretDirectory'];
                    $tbl[1]['name'] = $tab2_content['name'];
                     
                }
            }
        }
        $tbl[0]["group_rel_team_user"] = null;
        if (isset ($group_data["group_rel_team_user"]) && isset($group['mustImportUsers']) && true === $group['mustImportUsers'])
            {
            foreach ($group_data["group_rel_team_user"] as $tab2_content)
            {
                if ($group["id"] == $tab2_content["team"])
                    {
                    $tbl[0]["group_rel_team_user"] = $tab2_content;
                }
            }
        }
        $tbl[0]["group_property"] = null;
        if (isset ($group_data["group_property"]))
            {
            foreach ($group_data["group_property"] as $tab2_content)
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
    function create_group_for_import($exported_course_id, $course_id, $group, $group_data, $usersIdToChange)
    {
    	
        if (isset ($group_data["group_rel_team_user"]))
        {
            $group_data["group_rel_team_user"] = replaceUserId($usersIdToChange, $tab["group_rel_team_user"], "user");
        }
         
        $group_data = filter_group($group_data, $group);
         
        if (isset($group_data[1])) $group = $group_data[1];
        if (isset($group_data[0])) $group_data = $group_data[0];
         
         
        if (false === $group['mustImportUsers'])
        	$usersIdToChange = setAnonymousUser($usersIdToChange);
         
        $group = import_group_in_db($group_data, $course_id, $group);
        if (isset($group_data) && isset($group['mustImportTools']) && true === $group['mustImportTools'])
        {           
            if (isset($group['wiki']) && true === $group['wiki'])
            {                 
                if (false === import_wiki($exported_course_id, $course_id, $group, $usersIdToChange))
                return false;
            }
            else if (0 != $group['id'])
            {
                if (false === create_wiki($group['id'], $group['name'].' - Wiki'))
                return false;
            }                          
            if (isset($group['forum']) && true === $group['forum'])
            {
                if (false === import_bb($exported_course_id, $course_id, $group, $usersIdToChange))
                return false;
            }
            else if (0 != $group['id'])
            {
                if (false === create_forum($group['name']." - forum", '', 2, 1, $group['id'], $course_id))
                return false;
            }
             
            if (0 != $group['id'])
            {                 
                if (false == importGroupDocuments($exported_course_id, $course_id, $group))
                    return false;
            }
        }
        else
        {
       	   if (false === create_wiki($group['id'], $group['name'].' - Wiki'))
               	return false;
           if (false === create_forum($group['name']." - forum", '', 2, 1, $group['id'], $course_id))
                return false;
        }
        return $group;
    }
    function import_group($exported_course_id, $course_id, $usersIdToChange, $groupInfo,$mustDeleteGroups = false)
    {
         
        $group_data = import_group_from_file($exported_course_id);
        
        if (false !== $group_data)
        {
            foreach($groupInfo as $id => $group)
            {            	
                if ($group['id'] != 0)
                {
                    if (false === create_group_for_import($exported_course_id, $course_id, $group, $group_data, $usersIdToChange))
                    return false;
                }
            }
        }
        return $groupInfo;
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
    function import_userinfo($exported_course_id, $course_id = NULL, $groupInfo, $usersIdToChange)
    {
        flush_userinfo_table($course_id);
         
        if (isset ($group["userinfo"]) && true == $group["userinfo"])
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
    function filterBb($tab, $group, $course_id)
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
                 
                if ($group['id'] === $forum['group_id'] && isset($group['forum']) && $group['forum'] === true)
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
    function import_bb($exported_course_id, $course_id = NULL, $group, $usersIdToChange)
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
                $tab["bb_forums"] = replaceGroupId($group, $tab["bb_forums"], "group_id");
            }
             
            $tab = filterBb($tab, $group, $course_id);
             
             
            $tab = set_bbIds($course_id, $tab);
             
            if (0 == $group['id'])
            flush_course_forums($course_id);
            import_bb_in_db($tab, $course_id, $group);
        }
        else
        return false;
        return true;
    }
    function import_wiki($exported_course_id, $course_id = NULL, $group, $usersIdToChange)
    {       
        $tab = import_wiki_from_file($exported_course_id);
         
        if (false !== $tab)
            {
            if (isset ($tab["wiki_pages"]))
                {
                $tab["wiki_pages"] = replaceUserId($usersIdToChange, $tab["wiki_pages"], "owner_id");
            }
            if (isset ($tab["wiki_pages_content"]))
                {
                $tab["wiki_pages_content"] = replaceUserId($usersIdToChange, $tab["wiki_pages_content"], "editor_id");
            }
            if (isset ($tab["wiki_properties"]))
                {
                $tab["wiki_properties"] = replaceGroupId($group, $tab["wiki_properties"], "group_id");
            }
             
            $tab = filterWikiTab($tab, $group);
            $tab = set_wikiIds($course_id, $tab);
            if (0 == $group['id']) delete_wiki($group['id'] );
            import_wiki_in_db($tab, $course_id);
        }
        else
        	return false;
        return true;
    }
    function filter_work($tab,$group)
    {
    	$tbl = array();    	
    	if(isset($group['mustImportUsers']) && true === $group['mustImportUsers'])
    	{
    		foreach ($tab as $id => $tab_content)
	    	{    	
    			$tbl[$id] = $tab_content;    		
    		}
    	}
    	return $tbl;
    }
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
    function import_wrk($exported_course_id, $course_id = NULL, $group, $usersIdToChange)
    {    
    	flush_wrk_table($course_id);  
    	if(false === flush_wrk_files($course_id))
    	 	return false;           
        if (isset ($group["work"]) && true === $group["work"])
        {               	
            $tab = import_wrk_from_file($exported_course_id);
          
            if (false !== $tab)
            {
                if (isset ($tab['submission']))
                {
                    $tab['submission'] = replaceUserId($usersIdToChange, $tab['submission'], 'user_id');
                }
                if(isset($tab) && isset($tab['submission'])) 
                {
                	$tab['submission'] = filter_work($tab['submission'],$group);                           	                           	
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
    function import_manifest($exported_course_id, $course_id, $group)
    {
        if (isset ($group["manifest"]) && true == $group["manifest"])
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
    function import_group_in_db($tab, $course_id, $group)
    {
    	echo "<pre>";
    	var_dump($tab);
    	echo "</pre>";
        $tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
        if(isset ($tab['group_property'])) $group_property = $tab['group_property'];
        if(isset ($tab['group_team'])) $group_team = $tab['group_team'];
        if(isset ($tab['group_rel_team_user'])) $group_rel_team_user = $tab['group_rel_team_user'];
        
        if (isset ($tab['group_property']) && is_array($tab['group_property']) && (count($tab['group_property']) > 0))
        {
            $sql = "DELETE FROM`".$tbl["group_property"]."`";
            claro_sql_query($sql);
             
            $sql = "INSERT INTO `".$tbl["group_property"].'` (self_registration,nbGroupPerUser,private,
                forum,document,wiki,chat)
                VALUES ("'.(int) $group_property['self_registration'].'","'.(int) $group_property['nbGroupPerUser'].'","'.(int) $group_property['private'].'","'.(int) $group_property['forum'].'","'.(int) $group_property['document'].'","'.(int) $group_property['wiki'].'","'.(int) $group_property['chat'].'")';
            claro_sql_query($sql);
        }
         
        if (isset ($tab['group_team']) && is_array($tab['group_team']) && (count($tab['group_team']) > 0))
            {
             
            if ($group_team['id'] == $group['oldid'])
                {
                $sql = "INSERT INTO `".$tbl["group_team"].'` (name,description,tutor,maxStudent,secretDirectory)
                    VALUES ("'.addslashes($group_team['name']).'","'.addslashes($group_team['description']).'","'.(int) $group_team['tutor'].'","'.(int) $group_team['maxStudent'].'","'.addslashes($group_team['secretDirectory']).'")';
                 
                $id = claro_sql_query_insert_id($sql);
                 
                if (isset ($tab['group_rel_team_user']) && is_array($tab['group_rel_team_user']) && (count($tab['group_rel_team_user']) > 0))
                    {
                    $sql = "INSERT INTO `".$tbl["group_rel_team_user"].'` (user,team,status,role)
                        VALUES ("'.(int) $group_rel_team_user['user'].'","'.$id.'","'.(int) $group_rel_team_user['status'].'","'.addslashes($group_rel_team_user['role']).'")';
                    claro_sql_query($sql);
                }
                $group['id'] = $id;
            }
        }
         
        return $group;
    }
     
    function import_group_from_file($course_id)
    {
        if (empty ($course_id))
            return claro_failure :: set_failure("Empty dir name");
        $file = EXTRACT_PATH."/".$course_id."/tools/group/group.xml";
         
        $xml = xml_parser_create($GLOBALS['charset']);
         
        $group = new group_parser;
         
        xml_set_object($xml, $group);
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
        return $group->get_tab();
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
                    VALUES ('.(int) $tab_content['id'].',"'.addslashes($tab_content['title']).'","'.addslashes($tab_content['description']).'","'.(int) $tab_content['group_id'].'")';
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
    function import_wrk_in_db($tab, $course_id = NULL)
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
                    VALUES ('.(int) $tab_content['id'].','.(int) $tab_content['assignment_id'].',"'.(int) $tab_content['parent_id'].'","'.(int) $tab_content['user_id'].'","'.(int) $tab_content['group_id'].'","'.addslashes($tab_content['title']).'","'.addslashes($tab_content['visibility']).'","'.$tab_content['creation_date'].'","'.$tab_content['last_edit_date'].'","'.addslashes($tab_content['authors']).'","'.addslashes($tab_content['submitted_text']).'","'.addslashes($tab_content['submitted_doc_path']).'","'.addslashes($tab_content['private_feedback']).'",'.($tab_content['original_id'] == ""? "null" : (int) $tab_content['original_id'].'"').',"'.(int) $tab_content['score'].'")';          
                claro_sql_query($sql);
            }
        }
    }
    function flush_wrk_table($course_id = NULL)
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
        $file = EXTRACT_PATH."/".$course_id."/tools/users/users.xml";
         
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
    function import_users_in_db($tab)
    {
        $tbl = claro_sql_get_main_tbl();
         
        if (isset ($tab) && is_array($tab) && (count($tab) > 0))
            {
            foreach ($tab as $tab_content)
            {
                $sql = "INSERT INTO `".$tbl["user"].'` (nom,prenom,username,password,authsource,email,statut,officialCode,phoneNumber,
                    pictureUri,creatorId)
                    VALUES ("'.addslashes($tab_content['firstname']).'","'.addslashes($tab_content['lastname']).'","'.addslashes($tab_content['username']).'","'.addslashes($tab_content['password']).'","'.addslashes($tab_content['authSource']).'","'.addslashes($tab_content['email']).'","'.$tab_content['statut'].'","'.addslashes($tab_content['officialCode']).'","'.addslashes($tab_content['phoneNumber']).'","'.addslashes($tab_content['pictureUri']).'","'.(int) $tab_content['creatorId'].'")';
                 
                claro_sql_query($sql);
            }
             
        }
    }
    class description_parser {
        var $tab = array ();
        var $tag;
        var $id;
         
        function start_element($parser, $tag, $attributes)
        {
            $this->tag = $tag;
            if ('id' == $this->tag)
                {
                $this->id = $attributes["id"];
                $this->tab[$this->id] = array ();
                $this->tab[$this->id]['id'] = $this->id;
                $this->tab[$this->id]['title'] = '';
                $this->tab[$this->id]['content'] = '';
                $this->tab[$this->id]['upDate'] = '';
                $this->tab[$this->id]['visibility'] = '';
            }
        }
         
        function end_element($parser, $tag)
        {
             
        }
        function get_data($parser, $data)
        {
            if ('title' == $this->tag)
                {
                $this->tab[$this->id]['title'] .= $data;
            }
            else
            if ('content' == $this->tag)
                {
                $this->tab[$this->id]['content'] .= $data;
            }
            else
            if ('upDate' == $this->tag)
                {
                $this->tab[$this->id]['upDate'] .= $data;
            }
            else
            if ('visibility' == $this->tag)
                {
                $this->tab[$this->id]['visibility'] .= $data;
            }
        }
         
        function get_tab()
        {
            return $this->tab;
        }
    }
    class announcement_parser {
        var $tab = array ();
        var $tag;
        var $id;
         
        function start_element($parser, $tag, $attributes)
        {
            $this->tag = $tag;
            if ('id' == $this->tag)
                {
                 
                $this->id = $attributes["id"];
                $this->tab[$this->id] = array ();
                $this->tab[$this->id]['id'] = $this->id;
                $this->tab[$this->id]['title'] = '';
                $this->tab[$this->id]['content'] = '';
                $this->tab[$this->id]['time'] = '';
                $this->tab[$this->id]['order'] = '';
                $this->tab[$this->id]['visibility'] = '';
            }
        }
         
        function end_element($parser, $tag)
        {
             
        }
        function get_data($parser, $data)
        {
            if ('title' == $this->tag)
                {
                $this->tab[$this->id]['title'] .= $data;
            }
            else
            if ('content' == $this->tag)
                {
                $this->tab[$this->id]['content'] .= $data;
            }
            else
            if ('time' == $this->tag)
                {
                $this->tab[$this->id]['time'] .= $data;
            }
            else
            if ('rank' == $this->tag)
                {
                $this->tab[$this->id]['order'] .= $data;
            }
            else
            if ('visibility' == $this->tag)
                {
                $this->tab[$this->id]['visibility'] .= $data;
            }
        }
         
        function get_tab()
        {
            return $this->tab;
        }
    }
    class calendar_parser {
        var $tab = array ();
        var $tag;
        var $id;
         
        function start_element($parser, $tag, $attributes)
        {
            $this->tag = $tag;
            if ('id' == $this->tag)
                {
                 
                $this->id = $attributes["id"];
                $this->tab[$this->id] = array ();
                $this->tab[$this->id]['id'] = $this->id;
                $this->tab[$this->id]['title'] = '';
                $this->tab[$this->id]['content'] = '';
                $this->tab[$this->id]['day'] = '';
                $this->tab[$this->id]['hour'] = '';
                $this->tab[$this->id]['lasting'] = '';
                $this->tab[$this->id]['visibility'] = '';
            }
        }
         
        function end_element($parser, $tag)
        {
             
        }
        function get_data($parser, $data)
        {
            if ('title' == $this->tag)
                {
                $this->tab[$this->id]['title'] .= $data;
            }
            else
            if ('content' == $this->tag)
                {
                $this->tab[$this->id]['content'] .= $data;
            }
            else
            if ('day' == $this->tag)
                {
                $this->tab[$this->id]['day'] .= $data;
            }
            else
            if ('hour' == $this->tag)
                {
                $this->tab[$this->id]['hour'] .= $data;
            }
            else
            if ('lasting' == $this->tag)
                {
                $this->tab[$this->id]['lasting'] .= $data;
            }
            else
            if ('visibility' == $this->tag)
                {
                $this->tab[$this->id]['visibility'] .= $data;
            }
        }
         
        function get_tab()
        {
            return $this->tab;
        }
    }
    class link_parser {
        var $tab = array ();
        var $tag;
        var $id;
        var $tabName;
         
        function start_element($parser, $tag, $attributes)
        {
            $this->tag = $tag;
            if ('links' == $this->tag)
                {
                $this->tabName = $tag;
                $this->tab[$this->tabName] = array ();
            }
            if ('resources' == $this->tag)
                {
                $this->tabName = $tag;
                $this->tab[$this->tabName] = array ();
            }
            if ('id' == $this->tag)
                {
                 
                $this->id = $attributes["id"];
                if ('links' == $this->tabName)
                    {
                    $this->tab[$this->tabName][$this->id] = array ();
                    $this->tab[$this->tabName][$this->id]['id'] = $this->id;
                    $this->tab[$this->tabName][$this->id]['src_id'] = '';
                    $this->tab[$this->tabName][$this->id]['dest_id'] = '';
                    $this->tab[$this->tabName][$this->id]['creation_time'] = '';
                }
                if ('resources' == $this->tabName)
                    {
                    $this->tab[$this->tabName][$this->id] = array ();
                    $this->tab[$this->tabName][$this->id]['id'] = $this->id;
                    $this->tab[$this->tabName][$this->id]['crl'] = '';
                    $this->tab[$this->tabName][$this->id]['title'] = '';
                }
                 
            }
        }
         
        function end_element($parser, $tag)
        {
             
        }
        function get_data($parser, $data)
        {
            if ('links' == $this->tabName)
                {
                if ('src_id' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['src_id'] .= $data;
                }
                else
                if ('dest_id' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['dest_id'] .= $data;
                }
                else
                if ('creation_time' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['creation_time'] .= $data;
                }
            }
            if ('resources' == $this->tabName)
                {
                if ('crl' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['crl'] .= $data;
                }
                else
                if ('title' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['title'] .= $data;
                }
            }
        }
         
        function get_tab()
        {
            return $this->tab;
        }
    }
    class lp_parser {
        var $tab = array ();
        var $tag;
        var $id;
        var $tabName;
         
        function start_element($parser, $tag, $attributes)
        {
            $this->tag = $tag;
             
            if ('asset' == $this->tag)
                {
                 
                $this->tabName = $tag;
                $this->tab[$this->tabName] = array ();
            }
            if ('learnpath' == $this->tag)
                {
                $this->tabName = $tag;
                $this->tab[$this->tabName] = array ();
            }
            if ('module' == $this->tag)
                {
                $this->tabName = $tag;
                $this->tab[$this->tabName] = array ();
            }
            if ('rel_learnpath_module' == $this->tag)
                {
                $this->tabName = $tag;
                $this->tab[$this->tabName] = array ();
            }
            if ('user_module_progress' == $this->tag)
                {
                $this->tabName = $tag;
                $this->tab[$this->tabName] = array ();
            }
            if (('asset_id' == $this->tag) & ('asset' == $this->tabName))
                {
                $this->id = $attributes["asset_id"];
                $this->tab[$this->tabName][$this->id] = array ();
                $this->tab[$this->tabName][$this->id]['asset_id'] = $this->id;
                $this->tab[$this->tabName][$this->id]['module_id'] = '';
                $this->tab[$this->tabName][$this->id]['path'] = '';
                $this->tab[$this->tabName][$this->id]['comment'] = '';
                 
            }
            if (('learnPath_id' == $this->tag) & ('learnpath' == $this->tabName))
                {
                 
                $this->id = $attributes["learnPath_id"];
                $this->tab[$this->tabName][$this->id] = array ();
                $this->tab[$this->tabName][$this->id]['learnPath_id'] = $this->id;
                $this->tab[$this->tabName][$this->id]['name'] = '';
                $this->tab[$this->tabName][$this->id]['comment'] = '';
                $this->tab[$this->tabName][$this->id]['lock'] = '';
                $this->tab[$this->tabName][$this->id]['visibility'] = '';
                $this->tab[$this->tabName][$this->id]['rank'] = '';
            }
            if (('module_id' == $this->tag) & ('module' == $this->tabName))
                {
                $this->id = $attributes["module_id"];
                $this->tab[$this->tabName][$this->id] = array ();
                $this->tab[$this->tabName][$this->id]['module_id'] = $this->id;
                $this->tab[$this->tabName][$this->id]['name'] = '';
                $this->tab[$this->tabName][$this->id]['comment'] = '';
                $this->tab[$this->tabName][$this->id]['accessibility'] = '';
                $this->tab[$this->tabName][$this->id]['startAsset_id'] = '';
                $this->tab[$this->tabName][$this->id]['contentType'] = '';
                $this->tab[$this->tabName][$this->id]['launch_data'] = '';
            }
            if (('learnPath_module_id' == $this->tag) & ('rel_learnpath_module' == $this->tabName))
                {
                $this->id = $attributes["learnPath_module_id"];
                $this->tab[$this->tabName][$this->id] = array ();
                $this->tab[$this->tabName][$this->id]['learnPath_module_id'] = $this->id;
                $this->tab[$this->tabName][$this->id]['learnPath_id'] = '';
                $this->tab[$this->tabName][$this->id]['module_id'] = '';
                $this->tab[$this->tabName][$this->id]['lock'] = '';
                $this->tab[$this->tabName][$this->id]['visibility'] = '';
                $this->tab[$this->tabName][$this->id]['specificComment'] = '';
                $this->tab[$this->tabName][$this->id]['rank'] = '';
                $this->tab[$this->tabName][$this->id]['parent'] = '';
                $this->tab[$this->tabName][$this->id]['raw_to_pass'] = '';
            }
            if (('user_module_progress_id' == $this->tag) & ('user_module_progress' == $this->tabName))
                {
                $this->id = $attributes["user_module_progress_id"];
                $this->tab[$this->tabName][$this->id] = array ();
                $this->tab[$this->tabName][$this->id]['user_module_progress_id'] = $this->id;
                $this->tab[$this->tabName][$this->id]['user_id'] = '';
                $this->tab[$this->tabName][$this->id]['learnPath_module_id'] = '';
                $this->tab[$this->tabName][$this->id]['learnPath_id'] = '';
                $this->tab[$this->tabName][$this->id]['lesson_location'] = '';
                $this->tab[$this->tabName][$this->id]['lesson_status'] = '';
                $this->tab[$this->tabName][$this->id]['entry'] = '';
                $this->tab[$this->tabName][$this->id]['raw'] = '';
                $this->tab[$this->tabName][$this->id]['scoreMin'] = '';
                $this->tab[$this->tabName][$this->id]['scoreMax'] = '';
                $this->tab[$this->tabName][$this->id]['total_time'] = '';
                $this->tab[$this->tabName][$this->id]['session_time'] = '';
                $this->tab[$this->tabName][$this->id]['suspend_data'] = '';
                $this->tab[$this->tabName][$this->id]['credit'] = '';
            }
        }
         
        function end_element($parser, $tag)
        {
             
        }
        function get_data($parser, $data)
        {
            if ('asset' == $this->tabName)
                {
                if ('module_id' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['module_id'] .= $data;
                }
                else
                if ('path' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['path'] .= $data;
                }
                else
                if ('comment' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['comment'] .= $data;
                }
            }
            if ('learnpath' == $this->tabName)
                {
                if ('name' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['name'] .= $data;
                }
                else
                if ('comment' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['comment'] .= $data;
                }
                else
                if ('lock' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['lock'] .= $data;
                }
                else
                if ('visibility' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['visibility'] .= $data;
                }
                else
                if ('rank' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['rank'] .= $data;
                }
            }
            if ('module' == $this->tabName)
                {
                if ('name' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['name'] .= $data;
                }
                else
                if ('comment' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['comment'] .= $data;
                }
                else
                if ('accessibility' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['accessibility'] .= $data;
                }
                else
                if ('startAsset_id' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['startAsset_id'] .= $data;
                }
                else
                if ('contentType' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['contentType'] .= $data;
                }
                else
                if ('launch_data' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['launch_data'] .= $data;
                }
            }
            if ('rel_learnpath_module' == $this->tabName)
                {
                if ('learnPath_id' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['learnPath_id'] .= $data;
                }
                else
                if ('module_id' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['module_id'] .= $data;
                }
                else
                if ('lock' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['lock'] .= $data;
                }
                else
                if ('visibility' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['visibility'] .= $data;
                }
                else
                if ('specificComment' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['specificComment'] .= $data;
                }
                else
                if ('rank' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['rank'] .= $data;
                }
                else
                if ('parent' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['parent'] .= $data;
                }
                else
                if ('raw_to_pass' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['raw_to_pass'] .= $data;
                }
            }
             
            if ('user_module_progress' == $this->tabName)
                {
                if ('user_id' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['user_id'] .= $data;
                }
                else
                if ('learnPath_module_id' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['learnPath_module_id'] .= $data;
                }
                else
                if ('learnPath_id' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['learnPath_id'] .= $data;
                }
                else
                if ('lesson_location' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['lesson_location'] .= $data;
                }
                else
                if ('lesson_status' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['lesson_status'] .= $data;
                }
                else
                if ('entry' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['entry'] .= $data;
                }
                else
                if ('raw' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['raw'] .= $data;
                }
                else
                if ('scoreMin' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['scoreMin'] .= $data;
                }
                else
                if ('scoreMax' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['scoreMax'] .= $data;
                }
                else
                if ('total_time' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['total_time'] .= $data;
                }
                else
                if ('session_time' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['session_time'] .= $data;
                }
                else
                if ('suspend_data' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['suspend_data'] .= $data;
                }
                else
                if ('credit' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['credit'] .= $data;
                }
            }
        }
         
        function get_tab()
        {
            return $this->tab;
        }
    }
    class quiz_parser {
        var $tab = array ();
        var $tag;
        var $id;
        var $tabName;
        var $cpt;
         
        function start_element($parser, $tag, $attributes)
        {
            $this->tag = $tag;
             
            if ('answer' == $this->tag)
                {
                $this->tabName = $tag;
                $this->tab[$this->tabName] = array ();
            }
            if ('question' == $this->tag)
                {
                if ('question' !== $this->tabName)
                    {
                    $this->tabName = $tag;
                    $this->tab[$this->tabName] = array ();
                }
            }
            if ('rel_test_question' == $this->tag)
                {
                $this->tabName = $tag;
                $this->tab[$this->tabName] = array ();
            }
            if ('test' == $this->tag)
                {
                $this->tabName = $tag;
                $this->tab[$this->tabName] = array ();
            }
            if (('id' == $this->tag) & ('answer' == $this->tabName))
                {
                 
                $this->id = $attributes["id"];
                $this->tab[$this->tabName][$this->id] = array ();
                $this->tab[$this->tabName][$this->id]['id'] = $this->id;
                $this->tab[$this->tabName][$this->id]['question_id'] = '';
                $this->tab[$this->tabName][$this->id]['reponse'] = '';
                $this->tab[$this->tabName][$this->id]['correct'] = '';
                $this->tab[$this->tabName][$this->id]['comment'] = '';
                $this->tab[$this->tabName][$this->id]['ponderation'] = '';
                $this->tab[$this->tabName][$this->id]['r_position'] = '';
            }
            if (('id' == $this->tag) & ('question' == $this->tabName))
                {
                 
                $this->id = $attributes["id"];
                $this->tab[$this->tabName][$this->id] = array ();
                $this->tab[$this->tabName][$this->id]['id'] = $this->id;
                $this->tab[$this->tabName][$this->id]['question'] = '';
                $this->tab[$this->tabName][$this->id]['description'] = '';
                $this->tab[$this->tabName][$this->id]['ponderation'] = '';
                $this->tab[$this->tabName][$this->id]['q_position'] = '';
                $this->tab[$this->tabName][$this->id]['type'] = '';
                $this->tab[$this->tabName][$this->id]['attached_file'] = '';
            }
            if ('question_id' == $this->tag & ('rel_test_question' == $this->tabName))
                {
                $this->cpt = $this->cpt++;
                $this->tab[$this->tabName][$this->cpt]['question_id'] = '';
                $this->tab[$this->tabName][$this->cpt]['exercice_id'] = '';
            }
            if (('id' == $this->tag) & ('test' == $this->tabName))
                {
                $this->id = $attributes["id"];
                $this->tab[$this->tabName][$this->id] = array ();
                $this->tab[$this->tabName][$this->id]['id'] = $this->id;
                $this->tab[$this->tabName][$this->id]['titre'] = '';
                $this->tab[$this->tabName][$this->id]['description'] = '';
                $this->tab[$this->tabName][$this->id]['type'] = '';
                $this->tab[$this->tabName][$this->id]['random'] = '';
                $this->tab[$this->tabName][$this->id]['active'] = '';
                $this->tab[$this->tabName][$this->id]['max_time'] = '';
                $this->tab[$this->tabName][$this->id]['max_attempt'] = '';
                $this->tab[$this->tabName][$this->id]['show_answer'] = '';
                $this->tab[$this->tabName][$this->id]['anonymous_attempts'] = '';
                $this->tab[$this->tabName][$this->id]['start_date'] = '';
                $this->tab[$this->tabName][$this->id]['end_date'] = '';
            }
        }
         
        function end_element($parser, $tag)
        {
             
        }
        function get_data($parser, $data)
        {
             
            if ('answer' == $this->tabName)
                {
                if ('id' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['id'] .= $data;
                }
                else
                if ('question_id' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['question_id'] .= $data;
                }
                else
                if ('reponse' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['reponse'] .= $data;
                }
                else
                if ('correct' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['correct'] .= $data;
                }
                else
                if ('comment' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['comment'] .= $data;
                }
                else
                if ('ponderation' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['ponderation'] .= $data;
                }
                else
                if ('r_position' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['r_position'] .= $data;
                }
                 
            }
            if ('question' == $this->tabName)
                {
                if ('question' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['question'] .= $data;
                }
                else
                if ('description' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['description'] .= $data;
                }
                else
                if ('ponderation' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['ponderation'] .= $data;
                }
                else
                if ('q_position' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['q_position'] .= $data;
                }
                else
                if ('type' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['type'] .= $data;
                }
                else
                if ('attached_file' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['attached_file'] .= $data;
                }
            }
            if ('rel_test_question' == $this->tabName)
                {
                if ('question_id' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->cpt]['question_id'] .= $data;
                }
                else
                if ('exercice_id' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->cpt]['exercice_id'] .= $data;
                }
            }
            if ('test' == $this->tabName)
                {
                if ('title' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['titre'] .= $data;
                }
                else
                if ('description' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['description'] .= $data;
                }
                else
                if ('type' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['type'] .= $data;
                }
                else
                if ('random' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['random'] .= $data;
                }
                else
                if ('active' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['active'] .= $data;
                }
                else
                if ('max_time' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['max_time'] .= $data;
                }
                else
                if ('max_attempt' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['max_attempt'] .= $data;
                }
                else
                if ('show_answer' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['show_answer'] .= $data;
                }
                else
                if ('anonymous_attempts' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['anonymous_attempts'] .= $data;
                }
                else
                if ('start_date' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['start_date'] .= $data;
                }
                else
                if ('end_date' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['end_date'] .= $data;
                }
            }
        }
         
        function get_tab()
        {
            return $this->tab;
        }
    }
    class tool_parser {
        var $tab = array ();
        var $tag;
        var $id;
        var $tabName;
         
        function start_element($parser, $tag, $attributes)
        {
            $this->tag = $tag;
             
            if ('tool_intro' == $this->tag)
                {
                $this->tabName = $tag;
                $this->tab[$this->tabName] = array ();
            }
            if ('tool_list' == $this->tag)
                {
                $this->tabName = $tag;
                $this->tab[$this->tabName] = array ();
            }
             
            if (('id' == $this->tag) & ('tool_intro' == $this->tabName))
                {
                $this->id = $attributes["id"];
                $this->tab[$this->tabName][$this->id] = array ();
                $this->tab[$this->tabName][$this->id]['id'] = $this->id;
                $this->tab[$this->tabName][$this->id]['tool_id'] = '';
                $this->tab[$this->tabName][$this->id]['title'] = '';
                $this->tab[$this->tabName][$this->id]['display_date'] = '';
                $this->tab[$this->tabName][$this->id]['content'] = '';
                $this->tab[$this->tabName][$this->id]['rank'] = '';
                $this->tab[$this->tabName][$this->id]['visibility'] = '';
            }
            if (('id' == $this->tag) & ('tool_list' == $this->tabName))
                {
                 
                $this->id = $attributes["id"];
                $this->tab[$this->tabName][$this->id] = array ();
                $this->tab[$this->tabName][$this->id]['id'] = $this->id;
                $this->tab[$this->tabName][$this->id]['tool_id'] = '';
                $this->tab[$this->tabName][$this->id]['rank'] = '';
                $this->tab[$this->tabName][$this->id]['access'] = '';
                $this->tab[$this->tabName][$this->id]['script_url'] = NULL;
                $this->tab[$this->tabName][$this->id]['script_name'] = NULL;
                $this->tab[$this->tabName][$this->id]['addedTool'] = '';
            }
        }
        function end_element($parser, $tag)
        {
             
        }
        function get_data($parser, $data)
        {
             
            if ('tool_intro' == $this->tabName)
                {
                if ('tool_id' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['tool_id'] .= $data;
                }
                else
                if ('title' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['title'] .= $data;
                }
                else
                if ('display_date' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['display_date'] .= $data;
                }
                else
                if ('content' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['content'] .= $data;
                }
                else
                if ('rank' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['rank'] .= $data;
                }
                else
                if ('visibility' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['visibility'] .= $data;
                }
            }
            if ('tool_list' == $this->tabName)
                {
                if ('tool_id' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['tool_id'] .= $data;
                }
                else
                if ('rank' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['rank'] .= $data;
                }
                else
                if ('access' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['access'] .= $data;
                }
                else
                if ('script_url' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['script_url'] .= $data;
                }
                else
                if ('script_name' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['script_name'] .= $data;
                }
                else
                if ('addedTool' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['addedTool'] .= $data;
                }
            }
        }
         
        function get_tab()
        {
            return $this->tab;
        }
    }
    class document_parser {
        var $tab = array ();
        var $tag;
        var $id;
         
        function start_element($parser, $tag, $attributes)
        {
            $this->tag = $tag;
            if ('id' == $this->tag)
                {
                $this->id = $attributes["id"];
                $this->tab[$this->id] = array ();
                $this->tab[$this->id]['id'] = $this->id;
                $this->tab[$this->id]['path'] = '';
                $this->tab[$this->id]['visibility'] = '';
                $this->tab[$this->id]['comment'] = '';
            }
        }
         
        function end_element($parser, $tag)
        {
             
        }
        function get_data($parser, $data)
        {
            if ('path' == $this->tag)
                {
                $this->tab[$this->id]['path'] .= $data;
            }
            else
            if ('visibility' == $this->tag)
                {
                $this->tab[$this->id]['visibility'] .= $data;
            }
            else
            if ('comment' == $this->tag)
                {
                $this->tab[$this->id]['comment'] .= $data;
            }
        }
         
        function get_tab()
        {
            return $this->tab;
        }
    }
    class group_parser {
        var $tab = array ();
        var $tag;
        var $id;
        var $tabName;
         
        function start_element($parser, $tag, $attributes)
        {
            $this->tag = $tag;
            if (('group_property' == $tag))
                {
                $this->tabName = $tag;
            }
            if (('group_rel_team_user' == $tag))
                {
                $this->tabName = $tag;
            }
            if (('group_team' == $tag))
                {
                $this->tabName = $tag;
            }
            if (('id' == $this->tag) & ('group_property' == $this->tabName))
                {
                $this->id = $attributes["id"];
                $this->tab[$this->tabName][$this->id] = array ();
                $this->tab[$this->tabName][$this->id]['id'] = $this->id;
                $this->tab[$this->tabName][$this->id]['self_registration'] = '';
                $this->tab[$this->tabName][$this->id]['nbGroupPerUser'] = '';
                $this->tab[$this->tabName][$this->id]['private'] = '';
                $this->tab[$this->tabName][$this->id]['forum'] = '';
                $this->tab[$this->tabName][$this->id]['document'] = '';
                $this->tab[$this->tabName][$this->id]['wiki'] = '';
                $this->tab[$this->tabName][$this->id]['chat'] = '';
            }
            if (('id' == $this->tag) & ('group_rel_team_user' == $this->tabName))
                {
                $this->id = $attributes["id"];
                $this->tab[$this->tabName][$this->id] = array ();
                $this->tab[$this->tabName][$this->id]['id'] = $this->id;
                $this->tab[$this->tabName][$this->id]['user'] = '';
                $this->tab[$this->tabName][$this->id]['team'] = '';
                $this->tab[$this->tabName][$this->id]['status'] = '';
                $this->tab[$this->tabName][$this->id]['role'] = '';
            }
            if (('id' == $this->tag) & ('group_team' == $this->tabName))
                {
                $this->id = $attributes["id"];
                $this->tab[$this->tabName][$this->id] = array ();
                $this->tab[$this->tabName][$this->id]['id'] = $this->id;
                $this->tab[$this->tabName][$this->id]['name'] = '';
                $this->tab[$this->tabName][$this->id]['description'] = '';
                $this->tab[$this->tabName][$this->id]['tutor'] = '';
                $this->tab[$this->tabName][$this->id]['maxStudent'] = '';
                $this->tab[$this->tabName][$this->id]['secretDirectory'] = '';
            }
        }
         
        function end_element($parser, $tag)
        {
             
        }
        function get_data($parser, $data)
        {
            if ('group_property' == $this->tabName)
                {
                if ('self_registration' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['self_registration'] .= $data;
                }
                else
                if ('nbGroupPerUser' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['nbGroupPerUser'] .= $data;
                }
                else
                if ('private' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['private'] .= $data;
                }
                else
                if ('forum' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['forum'] .= $data;
                }
                else
                if ('document' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['document'] .= $data;
                }
                else
                if ('wiki' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['wiki'] .= $data;
                }
                else
                if ('chat' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['chat'] .= $data;
                }
            }
            if ('group_rel_team_user' == $this->tabName)
                {
                if ('user' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['user'] .= $data;
                }
                else
                if ('team' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['team'] .= $data;
                }
                else
                if ('status' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['status'] .= $data;
                }
                else
                if ('role' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['role'] .= $data;
                }
            }
            if ('group_team' == $this->tabName)
                {
                if ('name' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['name'] .= $data;
                }
                else
                if ('description' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['description'] .= $data;
                }
                else
                if ('tutor' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['tutor'] .= $data;
                }
                else
                if ('maxStudent' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['maxStudent'] .= $data;
                }
                else
                if ('secretDirectory' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['secretDirectory'] .= $data;
                }
            }
        }
         
        function get_tab()
        {
            return $this->tab;
        }
    }
    class userinfo_parser {
        var $tab = array ();
        var $tag;
        var $id;
        var $tabName;
         
        function start_element($parser, $tag, $attributes)
        {
            $this->tag = $tag;
            if ('userinfo_def' == $tag)
                {
                $this->tabName = $tag;
            }
            if ('userinfo_content' == $tag)
                {
                $this->tabName = $tag;
            }
            if ('id' == $this->tag && 'userinfo_def' == $this->tabName)
                {
                $this->id = $attributes["id"];
                $this->tab[$this->tabName][$this->id] = array ();
                $this->tab[$this->tabName][$this->id]['id'] = $this->id;
                $this->tab[$this->tabName][$this->id]['title'] = '';
                $this->tab[$this->tabName][$this->id]['comment'] = '';
                $this->tab[$this->tabName][$this->id]['nbLine'] = '';
                $this->tab[$this->tabName][$this->id]['rank'] = '';
            }
            if ('id' == $this->tag && 'userinfo_content' == $this->tabName)
                {
                $this->id = $attributes["id"];
                $this->tab[$this->tabName][$this->id] = array ();
                $this->tab[$this->tabName][$this->id]['id'] = $this->id;
                $this->tab[$this->tabName][$this->id]['user_id'] = '';
                $this->tab[$this->tabName][$this->id]['def_id'] = '';
                $this->tab[$this->tabName][$this->id]['ed_ip'] = '';
                $this->tab[$this->tabName][$this->id]['ed_date'] = '';
                $this->tab[$this->tabName][$this->id]['content'] = '';
            }
        }
         
        function end_element($parser, $tag)
        {
             
        }
        function get_data($parser, $data)
        {
            if ('userinfo_def' == $this->tabName)
                {
                if ('title' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['title'] .= $data;
                }
                else
                if ('comment' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['comment'] .= $data;
                }
                else
                if ('nbLine' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['nbLine'] .= $data;
                }
                else
                if ('rank' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['rank'] .= $data;
                }
            }
            if ('userinfo_content' == $this->tabName)
                {
                if ('user_id' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['user_id'] .= $data;
                }
                else
                if ('def_id' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['def_id'] .= $data;
                }
                else
                if ('ed_ip' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['ed_ip'] .= $data;
                }
                else
                if ('ed_date' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['ed_date'] .= $data;
                }
                else
                if ('content' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['content'] .= $data;
                }
            }
             
        }
         
        function get_tab()
        {
            return $this->tab;
        }
    }
    class track_parser {
        var $tab = array ();
        var $tag;
        var $id;
        var $tabName;
         
        function start_element($parser, $tag, $attributes)
        {
            $this->tag = $tag;
            if ('track_e_acess' == $tag)
                {
                $this->tabName = $tag;
            }
            if ('tack_e_downloads' == $tag)
                {
                $this->tabName = $tag;
            }
            if ('track_e_exe_answers' == $tag)
                {
                $this->tabName = $tag;
            }
            if ('track_e_exe_details' == $tag)
                {
                $this->tabName = $tag;
            }
            if ('track_e_exercices' == $tag)
                {
                $this->tabName = $tag;
            }
            if ('track_e_uploads' == $tag)
                {
                $this->tabName = $tag;
            }
            if ('access_id' == $this->tag && 'track_e_acess' == $this->tabName)
                {
                $this->id = $attributes["access_id"];
                $this->tab[$this->tabName][$this->id] = array ();
                $this->tab[$this->tabName][$this->id]['access_id'] = $this->id;
                $this->tab[$this->tabName][$this->id]['acesss_user_id'] = '';
                $this->tab[$this->tabName][$this->id]['access_date'] = '';
                $this->tab[$this->tabName][$this->id]['access_tid'] = '';
                $this->tab[$this->tabName][$this->id]['access_tlabel'] = '';
            }
            if ('down_id' == $this->tag && 'tack_e_downloads' == $this->tabName)
                {
                $this->id = $attributes["down_id"];
                $this->tab[$this->tabName][$this->id] = array ();
                $this->tab[$this->tabName][$this->id]['down_id'] = $this->id;
                $this->tab[$this->tabName][$this->id]['down_user_id'] = '';
                $this->tab[$this->tabName][$this->id]['down_date'] = '';
                $this->tab[$this->tabName][$this->id]['down_doc_path'] = '';
            }
            if ('id' == $this->tag && 'tack_e_exe_answer' == $this->tabName)
                {
                $this->id = $attributes["id"];
                $this->tab[$this->tabName][$this->id] = array ();
                $this->tab[$this->tabName][$this->id]['id'] = $this->id;
                $this->tab[$this->tabName][$this->id]['details_id'] = '';
                $this->tab[$this->tabName][$this->id]['answer'] = '';
            }
            if ('id' == $this->tag && 'tack_e_exe_details' == $this->tabName)
                {
                $this->id = $attributes["id"];
                $this->tab[$this->tabName][$this->id] = array ();
                $this->tab[$this->tabName][$this->id]['id'] = $this->id;
                $this->tab[$this->tabName][$this->id]['exercice_track_id'] = '';
                $this->tab[$this->tabName][$this->id]['question_id'] = '';
                $this->tab[$this->tabName][$this->id]['result'] = '';
            }
            if ('exe_id' == $this->tag && 'tack_e_exercices' == $this->tabName)
                {
                $this->id = $attributes["exe_id"];
                $this->tab[$this->tabName][$this->id] = array ();
                $this->tab[$this->tabName][$this->id]['exe_id'] = $this->id;
                $this->tab[$this->tabName][$this->id]['exe_user_id'] = '';
                $this->tab[$this->tabName][$this->id]['exe_date'] = '';
                $this->tab[$this->tabName][$this->id]['exe_exo_id'] = '';
                $this->tab[$this->tabName][$this->id]['exe_result'] = '';
                $this->tab[$this->tabName][$this->id]['exe_time'] = '';
                $this->tab[$this->tabName][$this->id]['exe_weighting'] = '';
            }
            if ('upload_id' == $this->tag && 'tack_e_uploads' == $this->tabName)
                {
                $this->id = $attributes["upload_id"];
                $this->tab[$this->tabName][$this->id] = array ();
                $this->tab[$this->tabName][$this->id]['upload_id'] = $this->id;
                $this->tab[$this->tabName][$this->id]['upload_user_id'] = '';
                $this->tab[$this->tabName][$this->id]['upload_date'] = '';
                $this->tab[$this->tabName][$this->id]['upload_work_id'] = '';
            }
        }
         
        function end_element($parser, $tag)
        {
             
        }
        function get_data($parser, $data)
        {
            if ('track_e_access')
                {
                if ("access_user_id" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['access_user_id'] .= $data;
                }
                else
                if ("access_date" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['access_date'] .= $data;
                }
                else
                if ("access_tid" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['access_tid'] .= $data;
                }
                else
                if ("access_tlabel" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['access_tlabel'] .= $data;
                }
            }
            if ('track_e_downloads')
                {
                if ("down_user_id" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['down_user_id'] .= $data;
                }
                else
                if ("down_date" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['access_date'] .= $data;
                }
                else
                if ("down_doc_path" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['down_doc_path'] .= $data;
                }
            }
            if ('track_e_exe_answers')
                {
                if ("details_id" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['details_id'] .= $data;
                }
                else
                if ("answer" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['answer'] .= $data;
                }
            }
            if ('track_e_exe_details')
                {
                if ("exercise_track_id" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['excercise_trak_id'] .= $data;
                }
                else
                if ("question_id" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['question_id'] .= $data;
                }
                else
                if ("result" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['result'] .= $data;
                }
            }
            if ('track_e_exercices')
                {
                if ("exe_user_id" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['exe_user_id'] .= $data;
                }
                else
                if ("exe_date" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['exe_date'] .= $data;
                }
                else
                if ("exe_exo_id" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['exe_exo_id'] .= $data;
                }
                else
                if ("exe_result" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['exe_result'] .= $data;
                }
                else
                if ("exe_time" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['exe_time'] .= $data;
                }
                else
                if ("exe_weighting" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['exe_time'] .= $data;
                }
            }
            if ('track_e_uploads')
                {
                if ("upload_user_id" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['upload_user_id'] .= $data;
                }
                if ("upload_date" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['upload_date'] .= $data;
                }
                else
                if ("upload_work_id" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['upload_work_id'] .= $data;
                }
            }
        }
         
        function get_tab()
        {
            return $this->tab;
        }
    }
    class bb_parser {
        var $tab = array ();
        var $tag;
        var $id;
        var $tabName;
         
        function start_element($parser, $tag, $attributes)
        {
            $this->tag = $tag;
            if ('bb_categories' == $tag)
                {
                $this->tabName = $tag;
            }
            if ('bb_forums' == $tag)
                {
                $this->tabName = $tag;
            }
            if ('bb_posts' == $tag)
                {
                $this->tabName = $tag;
            }
            if ('bb_posts_text' == $tag)
                {
                $this->tabName = $tag;
            }
            if ('bb_priv_msgs' == $tag)
                {
                $this->tabName = $tag;
            }
            if ('bb_rel_topic_userstonotify' == $tag)
                {
                $this->tabName = $tag;
            }
            if ('bb_topics' == $tag)
                {
                $this->tabName = $tag;
            }
            if ('bb_users' == $tag)
                {
                $this->tabName = $tag;
            }
            if ('cat_id' == $this->tag && 'bb_categories' == $this->tabName)
                {
                 
                $this->id = $attributes["cat_id"];
                $this->tab[$this->tabName][$this->id] = array ();
                $this->tab[$this->tabName][$this->id]['cat_id'] = $this->id;
                $this->tab[$this->tabName][$this->id]['cat_title'] = '';
                $this->tab[$this->tabName][$this->id]['cat_order'] = '';
            }
            if ('forum_id' == $this->tag && 'bb_forums' == $this->tabName)
                {
                $this->id = $attributes["forum_id"];
                $this->tab[$this->tabName][$this->id] = array ();
                $this->tab[$this->tabName][$this->id]['forum_id'] = $this->id;
                $this->tab[$this->tabName][$this->id]['group_id'] = null;
                $this->tab[$this->tabName][$this->id]['forum_name'] = '';
                $this->tab[$this->tabName][$this->id]['forum_desc'] = '';
                $this->tab[$this->tabName][$this->id]['forum_access'] = '';
                $this->tab[$this->tabName][$this->id]['forum_moderator'] = '';
                $this->tab[$this->tabName][$this->id]['forum_topics'] = '';
                $this->tab[$this->tabName][$this->id]['forum_posts'] = '';
                $this->tab[$this->tabName][$this->id]['forum_last_post_id'] = '';
                $this->tab[$this->tabName][$this->id]['cat_id'] = '';
                $this->tab[$this->tabName][$this->id]['forum_type'] = '';
                $this->tab[$this->tabName][$this->id]['forum_order'] = '';
            }
            if ('post_id' == $this->tag && 'bb_posts' == $this->tabName)
                {
                $this->id = $attributes["post_id"];
                $this->tab[$this->tabName][$this->id] = array ();
                $this->tab[$this->tabName][$this->id]['post_id'] = $this->id;
                $this->tab[$this->tabName][$this->id]['topic_id'] = '';
                $this->tab[$this->tabName][$this->id]['forum_id'] = '';
                $this->tab[$this->tabName][$this->id]['poster_id'] = '';
                $this->tab[$this->tabName][$this->id]['post_time'] = '';
                $this->tab[$this->tabName][$this->id]['poster_ip'] = '';
                $this->tab[$this->tabName][$this->id]['firstname'] = '';
                $this->tab[$this->tabName][$this->id]['lastname'] = '';
            }
            if ('post_id' == $this->tag && 'bb_posts_text' == $this->tabName)
                {
                $this->id = $attributes["post_id"];
                $this->tab[$this->tabName][$this->id] = array ();
                $this->tab[$this->tabName][$this->id]['post_id'] = $this->id;
                $this->tab[$this->tabName][$this->id]['post_text'] = '';
            }
            if ('msg_id' == $this->tag && 'bb_priv_msgs' == $this->tabName)
                {
                $this->id = $attributes["msg_id"];
                $this->tab[$this->tabName][$this->id] = array ();
                $this->tab[$this->tabName][$this->id]['msg_id'] = $this->id;
                $this->tab[$this->tabName][$this->id]['from_userid'] = '';
                $this->tab[$this->tabName][$this->id]['to_userid'] = '';
                $this->tab[$this->tabName][$this->id]['msg_time'] = '';
                $this->tab[$this->tabName][$this->id]['poster_ip'] = '';
                $this->tab[$this->tabName][$this->id]['msg_status'] = '';
                $this->tab[$this->tabName][$this->id]['msg_text'] = '';
            }
            if ('notify_id' == $this->tag && 'bb_rel_topic_userstonotify' == $this->tabName)
                {
                $this->id = $attributes["notify_id"];
                $this->tab[$this->tabName][$this->id] = array ();
                $this->tab[$this->tabName][$this->id]['notify_id'] = $this->id;
                $this->tab[$this->tabName][$this->id]['user_id'] = '';
                $this->tab[$this->tabName][$this->id]['topic_id'] = '';
            }
            if ('topic_id' == $this->tag && 'bb_topics' == $this->tabName)
                {
                $this->id = $attributes["topic_id"];
                $this->tab[$this->tabName][$this->id] = array ();
                $this->tab[$this->tabName][$this->id]['topic_id'] = $this->id;
                $this->tab[$this->tabName][$this->id]['topic_title'] = '';
                $this->tab[$this->tabName][$this->id]['topic_poster'] = '';
                $this->tab[$this->tabName][$this->id]['topic_time'] = '';
                $this->tab[$this->tabName][$this->id]['topic_views'] = '';
                $this->tab[$this->tabName][$this->id]['topic_replies'] = '';
                $this->tab[$this->tabName][$this->id]['topic_last_post_id'] = '';
                $this->tab[$this->tabName][$this->id]['forum_id'] = '';
                $this->tab[$this->tabName][$this->id]['topic_status'] = '';
                $this->tab[$this->tabName][$this->id]['topic_notify'] = '';
                $this->tab[$this->tabName][$this->id]['lastname'] = '';
                $this->tab[$this->tabName][$this->id]['firstname'] = '';
            }
            if ('user_id' == $this->tag && 'bb_users' == $this->tabName)
                {
                $this->id = $attributes["user_id"];
                $this->tab[$this->tabName][$this->id] = array ();
                $this->tab[$this->tabName][$this->id]['user_id'] = $this->id;
                $this->tab[$this->tabName][$this->id]['username'] = '';
                $this->tab[$this->tabName][$this->id]['user_regdate'] = '';
                $this->tab[$this->tabName][$this->id]['user_password'] = '';
                $this->tab[$this->tabName][$this->id]['user_email'] = '';
                $this->tab[$this->tabName][$this->id]['user_icq'] = '';
                $this->tab[$this->tabName][$this->id]['user_website'] = '';
                $this->tab[$this->tabName][$this->id]['user_occ'] = '';
                $this->tab[$this->tabName][$this->id]['user_from'] = '';
                $this->tab[$this->tabName][$this->id]['user_intrest'] = '';
                $this->tab[$this->tabName][$this->id]['user_sig'] = '';
                $this->tab[$this->tabName][$this->id]['user_viewemail'] = '';
                $this->tab[$this->tabName][$this->id]['user_theme'] = '';
                $this->tab[$this->tabName][$this->id]['user_aim'] = '';
                $this->tab[$this->tabName][$this->id]['user_yim'] = '';
                $this->tab[$this->tabName][$this->id]['user_msnm'] = '';
                $this->tab[$this->tabName][$this->id]['user_posts'] = '';
                $this->tab[$this->tabName][$this->id]['user_attachsig'] = '';
                $this->tab[$this->tabName][$this->id]['user_desmile'] = '';
                $this->tab[$this->tabName][$this->id]['user_html'] = '';
                $this->tab[$this->tabName][$this->id]['user_bbcode'] = '';
                $this->tab[$this->tabName][$this->id]['user_rank'] = '';
                $this->tab[$this->tabName][$this->id]['user_level'] = '';
                $this->tab[$this->tabName][$this->id]['user_lang'] = '';
                $this->tab[$this->tabName][$this->id]['user_actkey'] = '';
                $this->tab[$this->tabName][$this->id]['user_newpasswd'] = '';
            }
             
        }
        function end_element($parser, $tag)
        {
             
        }
        function get_data($parser, $data)
        {
            if ('bb_categories' == $this->tabName)
                {
                if ("cat_id" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['cat_id'] .= $data;
                }
                else
                if ("cat_title" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['cat_title'] .= $data;
                }
                else
                if ("cat_order" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['cat_order'] .= $data;
                }
            }
            if ('bb_forums' == $this->tabName)
                {
                if ("group_id" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['group_id'] .= $data;
                }
                else
                if ("forum_name" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['forum_name'] .= $data;
                }
                else
                if ("forum_desc" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['forum_desc'] .= $data;
                }
                else
                if ("forum_access" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['forum_access'] .= $data;
                }
                else
                if ("forum_moderator" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['forum_moderator'] .= $data;
                }
                else
                if ("forum_topics" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['forum_topics'] .= $data;
                }
                else
                if ("forum_posts" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['forum_posts'] .= $data;
                }
                else
                if ("forum_last_post_id" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['forum_last_post_id'] .= $data;
                }
                else
                if ("cat_id" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['cat_id'] .= $data;
                }
                else
                if ("forum_type" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['forum_type'] .= $data;
                }
                else
                if ("forum_order" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['forum_order'] .= $data;
                }
            }
            if ('bb_posts' == $this->tabName)
                {
                if ("topic_id" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['topic_id'] .= $data;
                }
                else
                if ("forum_id" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['forum_id'] .= $data;
                }
                else
                if ("poster_id" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['poster_id'] .= $data;
                }
                else
                if ("post_time" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['post_time'] .= $data;
                }
                else
                if ("poster_ip" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['poster_ip'] .= $data;
                }
                else
                if ("lastname" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['lastname'] .= $data;
                }
                else
                if ("firstname" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['firstname'] .= $data;
                }
            }
            if ('bb_posts_text' == $this->tabName)
                {
                if ("post_text" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['post_text'] .= $data;
                }
            }
            if ('bb_priv_msgs' == $this->tabName)
                {
                if ("from_userid" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['from_userid'] .= $data;
                }
                else
                if ("to_userid" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['to_userid'] .= $data;
                }
                else
                if ("msg_time" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['msg_time'] .= $data;
                }
                else
                if ("poster_ip" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['poster_ip'] .= $data;
                }
                else
                if ("msg_status" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['msg_status'] .= $data;
                }
                else
                if ("msg_text" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['msg_text'] .= $data;
                }
            }
            if ('bb_rel_topic_userstonotify' == $this->tabName)
                {
                if ("user_id" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['user_id'] .= $data;
                }
                else
                if ("topic_id" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['topic_id'] .= $data;
                }
            }
            if ('bb_topics' == $this->tabName)
                {
                if ("topic_title" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['topic_title'] .= $data;
                }
                else
                if ("topic_poster" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['topic_poster'] .= $data;
                }
                else
                if ("topic_time" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['topic_time'] .= $data;
                }
                else
                if ("topic_views" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['topic_views'] .= $data;
                }
                else
                if ("topic_replies" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['topic_replies'] .= $data;
                }
                else
                if ("topic_last_post_id" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['topic_last_post_id'] .= $data;
                }
                else
                if ("forum_id" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['forum_id'] .= $data;
                }
                else
                if ("topic_status" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['topic_status'] .= $data;
                }
                else
                if ("topic_notify" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['topic_notify'] .= $data;
                }
                else
                if ("firstname" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['firstname'] .= $data;
                }
                else
                if ("lastname" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['lastname'] .= $data;
                }
            }
            if ('bb_users' == $this->tabName)
                {
                if ("username" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['username'] .= $data;
                }
                else
                if ("user_regdate" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['user_regdate'] .= $data;
                }
                else
                if ("user_password" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['user_password'] .= $data;
                }
                else
                if ("user_email" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['user_email'] .= $data;
                }
                else
                if ("user_icq" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['user_icq'] .= $data;
                }
                else
                if ("user_website" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['user_website'] .= $data;
                }
                else
                if ("user_occ" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['user_occ'] .= $data;
                }
                else
                if ("user_from" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['user_from'] .= $data;
                }
                else
                if ("user_intrest" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['user_intrest'] .= $data;
                }
                else
                if ("user_sig" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['user_sig'] .= $data;
                }
                else
                if ("user_viewemail" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['user_viewemail'] .= $data;
                }
                else
                if ("user_theme" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['user_theme'] .= $data;
                }
                else
                if ("user_aim" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['user_aim'] .= $data;
                }
                else
                if ("user_yim" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['user_yim'] .= $data;
                }
                else
                if ("user_msnm" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['user_msnm'] .= $data;
                }
                else
                if ("user_posts" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['user_posts'] .= $data;
                }
                else
                if ("user_attachsig" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['user_attachsig'] .= $data;
                }
                else
                if ("user_desmile" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['user_desmile'] .= $data;
                }
                else
                if ("user_html" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['user_html'] .= $data;
                }
                else
                if ("user_bbcode" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['user_bbcode'] .= $data;
                }
                else
                if ("user_rank" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['user_rank'] .= $data;
                }
                else
                if ("user_level" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['user_level'] .= $data;
                }
                else
                if ("user_lang" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['user_lang'] .= $data;
                }
                else
                if ("user_actkey" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['user_actkey'] .= $data;
                }
                else
                if ("user_newpasswd" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]['user_newpasswd'] .= $data;
                }
            }
             
        }
        function get_tab()
        {
            return $this->tab;
        }
    }
    class manifest_parser {
        var $tab = array ();
        var $tag;
        var $id;
        function manifest_parser()
        {
            $this->tab["cours_id"] = "";
            $this->tab["code"] = "";
            $this->tab["fake_code"] = "";
            $this->tab["directory"] = "";
            $this->tab["dbName"] = "";
            $this->tab["languageCourse"] = "";
            $this->tab["intitule"] = "";
            $this->tab["faculte"] = "";
            $this->tab["visible"] = "";
            $this->tab["enrollment_key"] = "";
            $this->tab["titulaires"] = "";
            $this->tab["email"] = "";
            $this->tab["departmentUrlName"] = "";
            $this->tab["departmentUrl"] = "";
            $this->tab["diskQuota"] = "";
            $this->tab["versionDb"] = "";
            $this->tab["versionClaro"] = "";
            $this->tab["lastVisit"] = "";
            $this->tab["lastEdit"] = "";
            $this->tab["creationDate"] = "";
            $this->tab["ExpirationDate"] = "";
        }
        function start_element($parser, $tag, $attributes)
        {
            $this->tag = $tag;
        }
         
        function end_element($parser, $tag)
        {
             
        }
        function get_data($parser, $data)
        {
            if ('cours_id' == $this->tag)
                {
                $this->tab['cours_id'] = $data;
            }
            else
            if ('code' == $this->tag)
                {
                $this->tab['code'] = $data;
            }
            else
            if ('fake_code' == $this->tag)
                {
                $this->tab['fake_code'] = $data;
            }
            else
            if ('directory' == $this->tag)
                {
                $this->tab['directory'] = $data;
            }
            else
            if ('dbName' == $this->tag)
                {
                $this->tab['dbName'] = $data;
            }
            else
            if ('languageCourse' == $this->tag)
                {
                $this->tab['languageCourse'] = $data;
            }
            else
            if ('intitule' == $this->tag)
                {
                $this->tab['intitule'] = $data;
            }
            else
            if ('faculte' == $this->tag)
                {
                $this->tab['faculte'] = $data;
            }
            else
            if ('visible' == $this->tag)
                {
                $this->tab['visible'] = $data;
            }
            else
            if ('enrollment_key' == $this->tag)
                {
                $this->tab['enrollment_key'] = $data;
            }
            else
            if ('titulaires' == $this->tag)
                {
                $this->tab['titulaires'] = $data;
            }
            else
            if ('email' == $this->tag)
                {
                $this->tab['email'] = $data;
            }
            else
            if ('departmentUrlName' == $this->tag)
                {
                $this->tab['departmentUrlName'] = $data;
            }
            else
            if ('departmentUrl' == $this->tag)
                {
                $this->tab['departmentUrl'] = $data;
            }
            else
            if ('diskQuota' == $this->tag)
                {
                $this->tab['diskQuota'] = $data;
            }
            else
            if ('versionDb' == $this->tag)
                {
                $this->tab['versionDb'] = $data;
            }
            else
            if ('versionClaro' == $this->tag)
                {
                $this->tab['versionClaro'] = $data;
            }
            else
            if ('lastVisit' == $this->tag)
                {
                $this->tab['lastVisit'] = $data;
            }
            else
            if ('lastEdit' == $this->tag)
                {
                $this->tab['lastEdit'] = $data;
            }
            else
            if ('creationDate' == $this->tag)
                {
                $this->tab['creationDate'] = $data;
            }
            else
            if ('expirationDate' == $this->tag)
                {
                $this->tab['expirationDate'] = $data;
            }
        }
         
        function get_tab()
        {
            return $this->tab;
        }
    }
     
    class wiki_parser {
        var $tab = array ();
        var $tag;
        var $id;
        var $cpt = -1;
        var $tabName;
         
        function start_element($parser, $tag, $attributes)
        {
            $this->tag = $tag;
            if ('wiki_acls' == $this->tag)
                {
                $this->tabName = $this->tag;
            }
            if ('wiki_pages' == $this->tag)
                {
                $this->tabName = $this->tag;
            }
            if ('wiki_pages_content' == $this->tag)
                {
                $this->tabName = $this->tag;
            }
            if ('wiki_properties' == $this->tag)
                {
                $this->tabName = $this->tag;
            }
            if ('wiki_id' == $this->tag && 'wiki_acls' == $this->tabName)
                {
                $this->cpt++;
                $this->tab[$this->tabName][$this->cpt]["wiki_id"] = $attributes;
                $this->tab[$this->tabName][$this->cpt]["flag"] = "";
                $this->tab[$this->tabName][$this->cpt]["value"] = "";
                 
            }
            if ('id' == $this->tag && 'wiki_pages' == $this->tabName)
                {
                $this->id = $attributes["id"];
                $this->tab[$this->tabName][$this->id]["id"] = $this->id;
                $this->tab[$this->tabName][$this->id]["wiki_id"] = "";
                $this->tab[$this->tabName][$this->id]["owner_id"] = "";
                $this->tab[$this->tabName][$this->id]["title"] = "";
                $this->tab[$this->tabName][$this->id]["ctime"] = "";
                $this->tab[$this->tabName][$this->id]["last_version"] = "";
                $this->tab[$this->tabName][$this->id]["last_mtime"] = "";
            }
            if ('id' == $this->tag && 'wiki_pages_content' == $this->tabName)
                {
                $this->id = $attributes["id"];
                $this->tab[$this->tabName][$this->id]["id"] = $this->id;
                $this->tab[$this->tabName][$this->id]["pid"] = "";
                $this->tab[$this->tabName][$this->id]["editor_id"] = "";
                $this->tab[$this->tabName][$this->id]["mtime"] = "";
                $this->tab[$this->tabName][$this->id]["content"] = "";
            }
            if ('id' == $this->tag && 'wiki_properties' == $this->tabName)
                {
                $this->id = $attributes["id"];
                $this->tab[$this->tabName][$this->id]["id"] = $this->id;
                $this->tab[$this->tabName][$this->id]["title"] = "";
                $this->tab[$this->tabName][$this->id]["description"] = "";
                $this->tab[$this->tabName][$this->id]["group_id"] = "";
            }
        }
         
        function end_element($parser, $tag)
        {
             
        }
        function get_data($parser, $data)
        {
            if ('wiki_acls' == $this->tabName)
                {
                if ("flag" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->cpt]["flag"] .= $data;
                }
                else
                if ("value" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->cpt]["value"] .= $data;
                }
            }
            if ('wiki_pages' == $this->tabName)
                {
                if ("wiki_id" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]["wiki_id"] .= $data;
                }
                else
                if ("owner_id" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]["owner_id"] .= $data;
                }
                else
                if ("title" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]["title"] .= $data;
                }
                else
                if ("ctime" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]["ctime"] .= $data;
                }
                else
                if ("last_version" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]["last_version"] .= $data;
                }
                else
                if ("last_mtime" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]["last_mtime"] .= $data;
                }
            }
            if ('wiki_pages_content' == $this->tabName)
                {
                if ("pid" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]["pid"] .= $data;
                }
                else
                if ("editor_id" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]["editor_id"] .= $data;
                }
                else
                if ("mtime" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]["mtime"] .= $data;
                }
                else
                if ("content" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]["content"] .= $data;
                }
            }
            if ('wiki_properties' == $this->tabName)
                {
                if ("title" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]["title"] .= $data;
                }
                else
                if ("description" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]["description"] .= $data;
                }
                else
                if ("group_id" == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]["group_id"] .= $data;
                }
            }
        }
         
        function get_tab()
        {
            return $this->tab;
        }
    }
    class wrk_parser {
        var $tab = array ();
        var $tag;
        var $id;
        var $tabName;
         
        function start_element($parser, $tag, $attributes)
        {
            $this->tag = $tag;
            if ('assignment' == $this->tag)
                {
                $this->tabName = $this->tag;
            }
            if ('submission' == $this->tag)
                {
                $this->tabName = $this->tag;
            }
            if ('id' == $this->tag && 'assignment' == $this->tabName)
                {
                $this->id = $this->tag;         
                $this->tab[$this->tabName][$this->id]["id"] =   $attributes["id"];
                $this->tab[$this->tabName][$this->id]["title"] = "";
                $this->tab[$this->tabName][$this->id]["description"] = "";
                $this->tab[$this->tabName][$this->id]["visibility"] = "";
                $this->tab[$this->tabName][$this->id]["def_submission_visibility"] = "";
                $this->tab[$this->tabName][$this->id]["assignment_type"] = "";
                $this->tab[$this->tabName][$this->id]["authorized_content"] = "";
                $this->tab[$this->tabName][$this->id]["allow_late_upload"] = "";
                $this->tab[$this->tabName][$this->id]["start_date"] = "";
                $this->tab[$this->tabName][$this->id]["end_date"] = "";
                $this->tab[$this->tabName][$this->id]["prefill_text"] = "";
                $this->tab[$this->tabName][$this->id]["prefill_doc_path"] = "";
                $this->tab[$this->tabName][$this->id]["prefill_submit"] = "";
            }
            if ('id' == $this->tag && 'submission' == $this->tabName)
                {
                $this->id = $this->tag;     
                $this->tab[$this->tabName][$this->id]["id"] = $attributes["id"];  
                $this->tab[$this->tabName][$this->id]["assignment_id"] = "";
                $this->tab[$this->tabName][$this->id]["parent_id"] = "";
                $this->tab[$this->tabName][$this->id]["user_id"] = "";
                $this->tab[$this->tabName][$this->id]["group_id"] = "";
                $this->tab[$this->tabName][$this->id]["title"] = "";
                $this->tab[$this->tabName][$this->id]["visibility"] = "";
                $this->tab[$this->tabName][$this->id]["creation_date"] = "";
                $this->tab[$this->tabName][$this->id]["last_edit_date"] = "";
                $this->tab[$this->tabName][$this->id]["authors"] = "";
                $this->tab[$this->tabName][$this->id]["submitted_text"] = "";
                $this->tab[$this->tabName][$this->id]["submitted_doc_path"] = "";
                $this->tab[$this->tabName][$this->id]["private_feedback"] = "";
                $this->tab[$this->tabName][$this->id]["original_id"] = "";
                $this->tab[$this->tabName][$this->id]["score"] = "";
            }
             
        }
         
        function end_element($parser, $tag)
        {
             
        }
        function get_data($parser, $data)
        {
            if ('assignment' == $this->tabName)
                {
                if ('title' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]["title"] .= $data;
                }
                else
                if ('description' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]["description"] .= $data;
                }
                else
                if ('visibility' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]["visibility"] .= $data;
                }
                else
                if ('def_submission_visibility' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]["def_submission_visibility"] .= $data;
                }
                else
                if ('assignment_type' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]["assignment_type"] .= $data;
                }
                else
                if ('authorized_content' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]["authorized_content"] .= $data;
                }
                else
                if ('allow_late_upload' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]["allow_late_upload"] .= $data;
                }
                else
                if ('start_date' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]["start_date"] .= $data;
                }
                else
                if ('end_date' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]["end_date"] .= $data;
                }
                else
                if ('prefill_text' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]["prefill_text"] .= $data;
                }
                else
                if ('prefill_doc_path' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]["prefill_doc_path"] .= $data;
                }
                else
                if ('prefill_submit' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]["prefill_submit"] .= $data;
                }
            }
            if ('submission' == $this->tabName)
                {
                if ('assignment_id' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]["assignment_id"] .= $data;
                }
                else
                if ('parent_id' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]["parent_id"] .= $data;
                }
                else
                if ('user_id' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]["user_id"] .= $data;
                }
                else
                if ('group_id' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]["group_id"] .= $data;
                }
                else
                if ('title' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]["title"] .= $data;
                }
                else
                if ('visibility' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]["visibility"] .= $data;
                }
                else
                if ('creation_date' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]["creation_date"] .= $data;
                }
                else
                if ('last_edit_date' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]["last_edit_date"] .= $data;
                }
                else
                if ('authors' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]["authors"] .= $data;
                }
                else
                if ('submitted_text' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]["submitted_text"] .= $data;
                }
                else
                if ('submitted_doc_path' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]["submitted_doc_path"] .= $data;
                }
                else
                if ('private_feedback' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]["private_feedback"] .= $data;
                }
                else
                if ('original_id' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]["original_id"] .= $data;
                }
                else
                if ('score' == $this->tag)
                    {
                    $this->tab[$this->tabName][$this->id]["score"] .= $data;
                }
            }
        }
         
        function get_tab()
        {
            return $this->tab;
        }
    }
    class users_parser 
    {
        var $tab = array ();
        var $tag;
        var $id;
         
        function start_element($parser, $tag, $attributes)
        {
            $this->tag = $tag;
             
            if ('user_id' == $this->tag)
                {
                $this->id = $this->tag;
                $this->tab[$this->id]["user_id"] = $attributes;
                $this->tab[$this->id]["firstname"] = "";
                $this->tab[$this->id]["lastname"] = "";
                $this->tab[$this->id]["username"] = "";
                $this->tab[$this->id]["password"] = "";
                $this->tab[$this->id]["authSource"] = "";
                $this->tab[$this->id]["email"] = "";
                $this->tab[$this->id]["statut"] = "";
                $this->tab[$this->id]["officialCode"] = "";
                $this->tab[$this->id]["phoneNumber"] = "";
                $this->tab[$this->id]["pictureUri"] = "";
                $this->tab[$this->id]["creatorId"] = "";
            }
        }
        function end_element($parser, $tag)
        {
             
        }
        function get_data($parser, $data)
        {
             
            if ('firstname' == $this->tag)
                {
                $this->tab[$this->id]["firstname"] .= $data;
            }
            else
            if ('lastname' == $this->tag)
                {
                $this->tab[$this->id]["lastname"] .= $data;
            }
            else
            if ('username' == $this->tag)
                {
                $this->tab[$this->id]["username"] .= $data;
            }
            else
            if ('password' == $this->tag)
                {
                $this->tab[$this->id]["password"] .= $data;
            }
            else
            if ('authSource' == $this->tag)
                {
                $this->tab[$this->id]["authSource"] .= $data;
            }
            else
            if ('email' == $this->tag)
                {
                $this->tab[$this->id]["email"] .= $data;
            }
            else
            if ('statut' == $this->tag)
                {
                $this->tab[$this->id]["statut"] .= $data;
            }
            else
            if ('officialCode' == $this->tag)
                {
                $this->tab[$this->id]["officialCode"] .= $data;
            }
            else
            if ('phoneNumber' == $this->tag)
                {
                $this->tab[$this->id]["phoneNumber"] .= $data;
            }
            else
            if ('pictureUri' == $this->tag)
                {
                $this->tab[$this->id]["pictureUri"] .= $data;
            }
            else
            if ('creatorId' == $this->tag)
                {
                $this->tab[$this->id]["creatorId"] .= $data;
            }
        }
         
        function get_tab()
        {
            return $this->tab;
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
    function replaceGroupId($group, $tab, $index)
    {
        foreach ($tab as $lacle => $tab2_content)
        {
             
            if ($tab2_content[$index] == $group["oldid"])
                {
                 
                $tab[$lacle][$index] = $group["id"];
            }
        }
        return $tab;
    }
    function import_documents($toolName, $imported_course_id, $course_id, $groupInfo)
    {
        if ('0' == $groupInfo['id'])
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
        $group_path = get_conf("rootSys").'courses/'.$course_id.'/group';
        claro_delete_file($group_path);
        claro_mkdir($group_path);
    }
    function importGroupDocuments($imported_courseId, $courseId, $group)
    {
        if (isset($group['chat']) && 'true' == $group['chat'])
            if (false === importChatDocuments($imported_courseId, $courseId, $group))
            return false;
         
        if (isset($group['document']) && 'true' == $group['document'])
            if (false === importDocDocuments($imported_courseId, $courseId, $group))
            return false;
         
        if ($group['id'] == 0 && isset ($group['exercise']) && 'true' == $group['exercise'])
            if (false === import_documents("exercise", $imported_courseId, $courseId, $group))
            return false;
        if ($group['id'] == 0 && isset ($group['modules']) && 'true' == $group['modules'])
            if (false === import_documents("modules", $imported_courseId, $courseId, $group))
            return false;
        if ($group['id'] == 0 && isset ($group['work']) && 'true' == $group['work'])
            if (false === import_documents("work", $imported_courseId, $courseId, $group))
            return false;
        if ($group['id'] == 0 && isset ($group['quiz']) && 'true' == $group['quiz'])
            if (false === import_documents("quiz", $imported_courseId, $courseId, $group))
            return false;
        return true;
    }
    function importDocDocuments($imported_course_id, $course_id, $groupInfo)
    {
         
        $course_path = get_conf("rootSys").'courses/'.$course_id;
        if (0 == $groupInfo['id'])
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
            $group_path = $course_path.'/group/'.$groupInfo['directory'];
             
            if (false === extract_archive($archive_path.'group.zip', $archive_path))
                return claro_failure :: set_failure("cant_extract_file");
            else
                {
                if (false === claro_delete_file($group_path))
                return false;
                if (false === claro_copy_file($archive_path.'group/'.$groupInfo['directory'], $course_path.'/group'))
                return false;
                if (false === claro_delete_file($archive_path.'group/'))
                return false;
            }
        }
    }
    function importChatDocuments($imported_course_id, $course_id, $groupInfo)
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
                if (0 == $groupInfo['id'] && file_exists($archive_path.'chat/'.$imported_course_id.'.chat.html'))
                    {
                    if (false === claro_delete_file($chat_path))
                    return false;
                    if (false === claro_mkdir($chat_path, 0777, true))
                    return false;
                    if (false === copy($archive_path.'chat/'.$imported_course_id.'.chat.html', $chat_path.'/'.$imported_course_id.'.chat.html'))
                    return false;
                }
                elseif (file_exists($archive_path.'chat/'.$imported_course_id.'.'.$groupInfo['oldid'].'.chat.html'))
                {
                    if (false === claro_delete_file($chat_path))
                    return false;
                    if (false === claro_mkdir($chat_path))
                    return false;
                    if (false === copy($archive_path.'chat/'.$imported_course_id.'.'.$groupInfo['oldid'].'.chat.html', $chat_path.'/'.$course_id.'.'.$groupInfo['id'].'.chat.html'))
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
    function filterWikiTab($tab_origine, $group)
    {
        $tab_fin = array();
         
        if (isset ($tab_origine['wiki_properties']))
            {
             
            foreach ($tab_origine['wiki_properties'] as $wiki_id => $tab_content)
            {
                $mustImportwiki = false;
                if ($tab_content['group_id'] == $group['id'] && isset($group['wiki']) && true === $group['wiki'])
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
     
    function get_oldGroupId($course_id, $groupInfo, $tab)
    {
        $tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
        $sql = "SELECT name,id FROM `".$tbl['group_team']."`";
        $result = claro_sql_query_fetch_all($sql);
         
        foreach($result as $tab_content)
        {
            foreach($tab[0]['group_team'] as $group_content)
            {
                if ($tab_content['name'] === $group_content['name'])
                {
                    $groupInfo[$group_content['id']]['oldIdInDb'] = $tab_content['id'];
                }
            }
        }
         
        return $groupInfo;
    }
?>