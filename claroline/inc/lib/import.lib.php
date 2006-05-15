<?php

	define('DISP_COURSE_CREATION_FORM'     ,__LINE__);
	define('DISP_COURSE_CREATION_SUCCEED'  ,__LINE__);
	define('DISP_COURSE_CREATION_FAILED'   ,__LINE__);
	define('DISP_COURSE_CREATION_PROGRESS' ,__LINE__);

	require_once ($includePath . '/claro_init_global.inc.php');    
    
    require_once ($includePath . '/lib/export_zip.lib.php');
    require_once ($includePath . '/../wiki/lib/lib.createwiki.php');
    require_once ($includePath . '/lib/pclzip/pclzip.lib.php');
	require_once ($includePath . '/lib/fileManage.lib.php');
    require_once ($includePath . '/lib/forum.lib.php');
    require_once ($includePath . '/lib/import.xmlparser.lib.php');
    require_once ($includePath . '/lib/add_course.lib.inc.php');
	require_once ($includePath . '/lib/course.lib.inc.php');
	require_once ($includePath . '/lib/sendmail.lib.php');

    require $includePath.'/lib/debug.lib.inc.php';
    require $includePath.'/lib/group.lib.inc.php';
     
    define("EXTRACT_PATH", 'C:\Program Files\EasyPHP1-8\www\cvs\claroline.test\export');
    
	function import_data_tool($toolId,$tooldir,$courseId=null,$importGroupInfo, $usersIdToChange)
	{
		$importLib      =  get_module_path($toolId) . '/connector/exchange.cnr.php';
		$importFuncName = $toolId . '_import_content';
	
		if (file_exists($importLib)) 
		{
			echo 'chargement de ' .$importLib . '<BR />';
			include_once($importLib);
			
			if (function_exists($importFuncName)) 
			{
				echo 'appel de ' . $importFuncName . '<BR />';
				return call_user_func($importFuncName,$tooldir,$courseId,$importGroupInfo, $usersIdToChange);				
			}				
			else {import_generic_tool($toolId,$tooldir,$courseId,$importGroupInfo);}	
		}	
		else {import_generic_tool($toolId,$tooldir,$courseId,$importGroupInfo);}
		
	}
	
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
 	 * 		- The 0 index points to the general course
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
    	global $logout,$uidReset , $tidReq , $tlabelReq , $tidReset, $gidReq , $gidReset;
    	$dbGlu= get_conf('dbGlu');
		$courseTablePrefix = get_conf('courseTablePrefix');
				
    	$tooldir = basename($archive_file, '.zip');

        if (false === extract_archive($archive_file, EXTRACT_PATH))
            return false;
                              
        if (false === ($course_id = import_manifest($tooldir, $course_id, $importGroupInfo[0])))
        	return false;
        	        	        	
            $cidReset=true;
            $cidReq = $course_id;
             
        	include ($GLOBALS['includePath'] . '/claro_init_local.inc.php');
        	
        if (false === ($usersIdToChange = import_users($tooldir,$course_id,$importGroupInfo[0])))
            return false;   
        
        if (isset($importGroupInfo[0]['group']) && true === $importGroupInfo[0]['group'])
        	$importGroupInfo = import_group($tooldir, $GLOBALS['_cid'], $usersIdToChange, $importGroupInfo,$mustImportusers);
         
        if (false === $importGroupInfo)
            return false;
         
        if (false == importGroupDocuments($tooldir, $course_id, $importGroupInfo[0]))
            return false;                        
        
        if (false === import_announcement($tooldir, $course_id, $importGroupInfo[0]))
            return false;
         
        if (false === import_course_description($tooldir,$course_id, $importGroupInfo[0]))
            return false;
         
        if (false === import_calendar($tooldir, $course_id, $importGroupInfo[0]))
            return false;
         
        if (false === import_link($tooldir, $GLOBALS['_cid'], $importGroupInfo[0]))
            return false;
         
        if (false === import_lp($tooldir, $GLOBALS['_cid'], $importGroupInfo[0], $usersIdToChange))
            return false;
         
        if (false === import_quiz($tooldir, $GLOBALS['_cid'], $importGroupInfo[0]))
            return false;
         
        if (false === import_tool($tooldir, $GLOBALS['_cid'], $importGroupInfo[0]))
            return false;
         
        if (false === import_document($tooldir, $GLOBALS['_cid'], $importGroupInfo[0]))
            return false;
         
        if (false == import_bb($tooldir, $GLOBALS['_cid'], $importGroupInfo[0], $usersIdToChange))
            return false;
         
        if (false == import_wiki($tooldir, $GLOBALS['_cid'], $importGroupInfo[0], $usersIdToChange))
            return false;
         
        if (false === import_wrk($tooldir, $GLOBALS['_cid'], $importGroupInfo, $usersIdToChange))
            return false;
         
        if (false === import_userinfo($tooldir, $GLOBALS['_cid'], $importGroupInfo[0], $usersIdToChange))
            return false;
         
        // claro_delete_file(EXTRACT_PATH."/".$tooldir);
        return true;
    }

    function import_generic_tool($toolName,$importedCourseDir, $course_id, $importGroupInfo)
    {        	          
       if (isset ($importGroupInfo[$toolName]) && true == $importGroupInfo[$toolName])
       {       
            $tab = import_generic_tool_from_file($importedCourseDir,$toolName);
            $prefix = get_table_prefix($course_id,$toolName);                  
            
            if(false == import_documents($toolName, $importedCourseDir, $course_id, $importGroupInfo))
            	return false;
            
                          
            if (false !== $tab)
            {                          	
            	if(false === import_create_generic_table($tab,$course_id,$toolName,$prefix))
            		return false;            		
            	if(false === delete_all_in_all_tool_table($tab,$course_id,$prefix))
            	 	return false;
                if(false === import_generic_tool_in_db($tab, $course_id,$prefix))
                	return false;
            }           
            else return false;
        }
        return true;
    }
    function import_create_generic_table($tab,$course_id,$toolName,$prefix)
    {    
    	foreach ($tab as $tableName)
    	{
    		$ceate_table_old_sql_query = $tableName['create_table'];
    		$old_prefix = $tableName['prefix'];    		
    		$ceate_table_sql_query = str_replace($old_prefix,$prefix,$ceate_table_old_sql_query);    		    
    		if (false=== claro_sql_query($ceate_table_sql_query))
    			return claro_failure::set_failure('couldnt_create_table_in_db');    		
    	}
    	
    	return true;
	
    }
    function get_table_prefix($course_id,$toolName)
   	{   	
		$context["course"] = $course_id;
		$context["toolLabel"] = $toolName;
		$prefixx = claro_sql_get_tables("",$context);
	
		$prefix = "claroline`.`c_es1_001";
		if ($prefix!=$prefixx) echo "xxx: ".$prefix." ___ " . $prefixx . " :xxx";
		$tbl = explode('`.`',$prefix);
		$prefix = $tbl[count($tbl)-1];		
					
		return $prefix;	
   	}
    function delete_all_in_all_tool_table($tab,$course_id,$prefix)
    {    	
   		foreach ($tab as $old_tab_name)
    	{    		    	
    		$old_table_name = $old_tab_name['table_name'];
    		$table_name = str_replace($old_tab_name['prefix'],$prefix,$old_table_name);
    		$sql = "DELETE FROM `".$table_name."`";
    		if(false === claro_sql_query($sql))
	    		return claro_failure::set_failure("coudlnt_delete_in_db");             
    	}
    	return true;
    } 
    function import_generic_tool_in_db($tab, $course_id,$prefix)
    {       	
    	foreach ($tab as $export_table)
    	{    		    	
    		$old_table_name = $export_table['table_name'];    		
    		$table_name = str_replace($export_table['prefix'],$prefix,$old_table_name);    	
   			foreach ($export_table as $data_type => $export_table_index)
   			{   			   		   				
				if("content" == $data_type)
	    		{	    			    				    		
	    			foreach($export_table_index as $table_data)
	    			{	    				
	    				$sql = "INSERT INTO `".$table_name."` ( `";
	    				$index_tmp = array();
	    				$data_tmp = array();
	    				foreach($table_data as $index => $table_data_content)
	    				{	    					
	    					$index_tmp[$index] = $index;
	    					if(! is_null($table_data_content))
	    				 
	    						$data_tmp[$index] = addslashes($table_data_content);
	    					else {$data_tmp[$index] = "NULL";}

	    					
	    				}	 	    		    					    		
	    				$sql .= implode("`,`",$index_tmp);
	    				$sql .= "`) VALUES ('";	    			
	    				$sql .= implode("','",$data_tmp);
	    				$sql .= "')";
	    				$sql = str_replace("'NULL'","NULL",$sql);	    	
	    				if(false === claro_sql_query($sql))
	    			 		return claro_failure::set_failure("coudlnt_write_in_db");         	
	    			}	    				                    	    			
	    					
   				}    				
   			}   		
    	}
    	
        return true;  
    }
    function import_generic_tool_from_file($course_id,$toolName)
    {
        if (empty ($course_id))
            return claro_failure::set_failure("Empty dir name");
        $file = EXTRACT_PATH."/".$course_id."/tools/".$toolName."/".$toolName.".xml";
         
        $xml = xml_parser_create($GLOBALS['charset']);
         
        $generic = new generic_tool_parser;
         
        xml_set_object($xml, $generic);
        xml_set_element_handler($xml, 'start_element', 'end_element');
        xml_set_character_data_handler($xml, 'get_data');
        xml_parser_set_option($xml, XML_OPTION_CASE_FOLDING, false);
        if (!is_dir(EXTRACT_PATH."/".$course_id))
        {
            return claro_failure::set_failure("directory does not exist");
        }
        if (!is_file($file))
            return claro_failure::set_failure("file does not exist");
        if (false == ($fp = fopen($file, 'r')))
            return claro_failure::set_failure("can't open file");
        while ($data = fread($fp, filesize($file)))
        {
            if (false == xml_parse($xml, $data, feof($fp)))
                return claro_failure::set_failure("can't parse file");
        }
        fclose($fp);
        xml_parser_free($xml);
        return $generic->get_tab();
    }
    /**
     * Based on the $tab array containing user data,
     * this function create the array $usersIdToChange which 
     * say, for each users, what is his old id, his new id and 
     * if we must import this user or not in the db	
     * 
     * We must import a user if it does not already exist
     * This mean if there isn't 'another user with the same firstname, lastname and/or officialCode
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
        $sql = "SELECT max(user_id) FROM `".$tbl['user']."`";
    	$user_offset = claro_sql_query_get_single_value($sql);
    	    	
    	foreach ($tab['user'] as $id => $userToAdd)
    	{             		
    	    $sql = "SELECT `user_id`, `nom` AS `firstname`, `prenom`  AS  `lastname`, `officialCode` 
    	 		 	FROM `".$tbl['user']."` 
    	 			WHERE nom = '".$userToAdd['firstname']."' AND prenom = '".$userToAdd['lastname']."'";
    	 			 		    	
    	 	$result = claro_sql_query_fetch_all($sql);
    	 
    	 	$usersIdToChange[$id]['oldUserId'] = $userToAdd['user_id'];   
    	 	$usersIdToChange[$id]['newUserId'] = $userToAdd['user_id'] + $user_offset;   
    	 	$usersIdToChange[$id]['mustImportUser'] = true;
    	 	$usersIdToChange[$id]['mustImportUserInCourse'] = true;
    	 	    	 	 
    	 	if (isset($result))
    	 	{
    	 		foreach($result as $userInDb)
    	 		{    	    	 			    	 		    	 		
    	 			if($userInDb['officialCode'] != $userToAdd['officialCode'] || ($userInDb['officialCode'] === "" && $userToAdd['officialCode'] === ""))
    	 			{    	 		    	 			
    	 				   
    	 				$usersIdToChange[$id]['newUserId'] = $userInDb['user_id'];
    	 				$usersIdToChange[$id]['mustImportUser'] = false;    
    	 			}
    	 		}
    	 	}   
    	}

        $sql = "SELECT u.`user_id`, 
        			   u.`nom` AS `firstname`, 
        			   u.`prenom`  AS  `lastname`, 
        			   u.`officialCode` 
        		FROM `".$tbl['rel_course_user']."` as rel
        		INNER JOIN `".$tbl['user']."` as u
        		ON u.user_id = rel.user_id 
    	 		WHERE rel.code_cours = '".$course_id."'";
		$result = claro_sql_query_fetch_all($sql);   
		if (isset($result))
    	{    		
    		foreach($result as $userInDb)
    		{    			
    			foreach($tab['user'] as $id => $userToAdd)
    			{	
    				if($userToAdd['firstname'] == $userInDb['firstname'] && 
    				   $userToAdd['lastname'] == $userInDb['lastname'] && 
    				   ($userInDb['officialCode'] != $userToAdd['officialCode'] || 
    				   ($userInDb['officialCode'] == "" && $userToAdd['officialCode'] == "")))
    				{
    					$usersIdToChange[$id]['newUserId'] = $userInDb['user_id'];
    					$usersIdToChange[$id]['mustImportUserInCourse'] = false;
    				}
    			}    			 
    		}
    	}	 	    		
    	return $usersIdToChange;        	 
    }
  
     /**
     * 		
     * Import users for a course from file to db.
     * If a user doesnt exist, we create it
     * The user is added in the users table and also in the courses-users relation table.
     *   
	 * @author Yannick Wautelet <yannick_wautelet@hotmail.com>  
	 * @param  string  $tooldir  - course_id of the exported course
	 * @param  string  $course_id           - course_id of the course in which the import will occur
	 * @param  array   $importGroupInfo     - array containing importation rules for the general course
	 * @return false if a problem occured, true if not.         
	 * 
 	 */     
    function import_users($tooldir,$course_id,$importGroupInfo)
    {    	
    	$usersIdToChange = null;
	    if(isset($importGroupInfo['mustImportUsers']) && true === $importGroupInfo['mustImportUsers'])
	    {
	        //import users from file in a tab
    	    $tab = import_users_from_file($tooldir);
        	if (false !== $tab)
        	{        	        		
            	//filter users and put it in a new tab
            	$usersIdToChange = filter_users($course_id,$tab);           
                         
	            //put users in db
    	        if(false === import_users_in_db($tab,$course_id,$usersIdToChange))
    	        	return false;
        	}        
        	else
        		return false;
	    }                  
         
        return $usersIdToChange;
    }
    
   
    function import_manifest($tooldir, $course_id, $importGroupInfo)
    {    	    	
    	$tab = import_manifest_from_file($tooldir);
        $course_ids['old'] = $tab['course']['code'];		
        if (is_null($course_id) && isset ($importGroupInfo["manifest"]) && true == $importGroupInfo["manifest"])
        {        	           
            if (false !== $tab)
            {            
                                               
                $courseSysCode = $tab['course']['code'];
                $courseOfficialCode = $tab['course']['fake_code'];
                $courseDirectory = $tab['course']['directory'];
                $courseDbName = $tab['course']['dbName'];
                $courseHolder = $tab['course']['titulaires'];
                $courseEmail = $tab['course']['email'];
                $courseCategory = $tab['course']['faculte'];
                $courseTitle = $tab['course']['intitule'];
                $courseLanguage = $tab['course']['languageCourse'];                
                $courseVisibility = $tab['course']['courseVisibility'];
                $courseEnrollAllowed = $tab['course']['courseEnrollAllowed'];
                $courseEnrollmentKey = $tab['course']['enrollment_key'];
                $courseExpirationDate = $tab['course']['expirationDate'];
                $extLinkName = $tab['course']['departmentUrlName'];
                $extLinkUrl = $tab['course']['departmentUrl'];     
            
                $courseSysCode = create_course(
                                   $courseOfficialCode
                   ,               $courseHolder
                   ,               $courseEmail
                   ,               $courseCategory
                   ,               $courseTitle
                   ,               $courseLanguage                   
                   ,               $courseVisibility
                   ,               $courseEnrollAllowed
                   ,               $courseEnrollmentKey
                   ,               $courseExpirationDate
                   ,               $extLinkName
                   ,               $extLinkUrl);      
            }
            else
            	return false;
            $course_ids['new'] = $courseSysCode;
        	return $course_ids;    
        }   
        else {        	
        	$course_ids['new'] = $course_id;        	
        	return $course_ids;
        }     
        
    }
  
    /**
     * 		
     * Import tool data from file to db.
     *   
	 * @author Yannick Wautelet <yannick_wautelet@hotmail.com>  
	 * @param  string  $tooldir  - course_id of the exported course
	 * @param  string  $course_id           - course_id of the course in which the import will occur
	 * @param  array   $importGroupInfo     - array containing importation rules for a group
	 * @return false if a problem occured, true if not.         
	 * 
 	 */    
    function import_tool($coursedir, $course_id, $importGroupInfo)
    {
        flush_tool_table($course_id);
        if (isset ($importGroupInfo["tool"]) && true == $importGroupInfo["tool"])
        {        	
            $tab = import_tool_from_file($coursedir);            
            if (false !== $tab)
            {
                if(false === import_tool_in_db($tab, $course_id))
                	return false;
            }
            else
            return false;
        }
        return true;
    }
    /**     
     * 
     * import tool data in db
     *  
	 * @author Yannick Wautelet <yannick_wautelet@hotmail.com> 
	 *  
	 * @param  array  $tab              - contain the tool data
	 * @param  string $course_id    	  
	 * @return false if a problem occured, true if not             
	 * 
 	 */      
    function import_tool_in_db($tab, $course_id)
    {
        $tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
         
        if (isset ($tab['tool_intro']) && (is_array($tab['tool_intro'])) && (count($tab['tool_intro']) > 0))
        {
            foreach ($tab['tool_intro'] as $tab_content)
            {
                $sql = "INSERT INTO `".$tbl["tool_intro"].'` (tool_id,title,display_date,content,rank,visibility)
                    VALUES ("'.(int) $tab_content['tool_id'].'","'.addslashes($tab_content['title']).'","'.$tab_content['display_date'].'","'.addslashes($tab_content['content']).'","'.(int) $tab_content['rank'].'","'.addslashes($tab_content['visibility']).'")';
                 
                if (false === claro_sql_query($sql))
        			return claro_failure::set_failure('couldnt_write_in_db');
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
                 
                if (false === claro_sql_query($sql))
        			return claro_failure::set_failure('couldnt_write_in_db');
            }
        }
        return true;
    }
    /**
     * 		
     * Filter $tab containing group data for the db  
     * 
     * $tab contain data for all groups, the filter select data for only one group and return it     
     *  
	 * @author Yannick Wautelet <yannick_wautelet@hotmail.com>  
	 * @param  string  $tab - group data for the db  
	 * @param  array   $importGroupInfo     - array containing importation rules for a group
	 * @return false if a problem occured, an array containing filtered group data         
	 * 
 	 */    
    function filter_group($tab, $importGroupInfo)
    {
        $tbl = array();
        $tbl[0] = null;
                
        if (isset ($tab["group_team"]))
        {
            foreach ($tab["group_team"] as $tab2_content)
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
      
        if (isset ($tab["group_rel_team_user"]) && isset($importGroupInfo['mustImportUsers']) && true === $importGroupInfo['mustImportUsers'])
        {
           	foreach ($tab["group_rel_team_user"] as $id => $tab2_content)
           	{            	
               	if ($importGroupInfo["id"] == $tab2_content["team"])
               	{
                   	$tbl[0]["group_rel_team_user"][$id] = $tab2_content;
               	}
           	}
        }
       
        $tbl[0]["group_property"] = null;
        if (isset ($tab["group_property"]))
        {
            foreach ($tab["group_property"] as $tab2_content)
            {
                $tbl[0]["group_property"] = $tab2_content;
            }
        }
         
        return $tbl;
    }
    /**
     * 		
     * Modify the $usersIdToChange array by setting the 'newUserId' index to 0
     * For some tools (like the wiki for example), a user_id = 0 mean the user is anonymous 
     * 
     * $tab contain data for all groups, the filter select data for only one group et return it     
     *  
	 * @author Yannick Wautelet <yannick_wautelet@hotmail.com>  
	 * @param  array   $usersIdToChange     - array containing relation bewteen old users ids (at the export)
	 * 										  and new users ids (for the import)
	 * @return the array $usersIdToChange with all 'newUserId' = 0       
	 * 
 	 */    
    function setAnonymousUser($usersIdToChange)
    {
        foreach ($usersIdToChange as $id => $users)
        {
            $usersIdToChange[$id]['newUserId'] = 0;
        }
        return $usersIdToChange;
    }
    /**
     * 		
     * Manage a group importation. 
     * This include the group data in db and the tools (wiki,_forum, document,_etc...)
     * 
     * For each tool, a check is done if the exported data must be imported. If not, we create a empty tool     
     *  
	 * @author Yannick Wautelet <yannick_wautelet@hotmail.com>
	 * @param  string  $tooldir
	 * @param  string  $course_id
	 * @param  array   $importGroupInfo     - array containing importation rules for a group  
	 * @param  array    $group_data			- array containing db data of a group
	 * @param  array   $usersIdToChange     - array containing relation bewteen old users ids (at the export)
	 * 										  and new users ids (for the import) 
	 * @return the array $usersIdToChange with all 'newUserId' = 0       
	 * 
 	 */    
    function create_group_for_import($tooldir,$imported_course_id, $course_id, $importGroupInfo, $group_data, $usersIdToChange,$mustImportusers)
    {      	    	
        if (isset ($group_data["group_rel_team_user"]))
        {
            $group_data["group_rel_team_user"] = replaceUserId($usersIdToChange, $group_data["group_rel_team_user"], "user");
        }
         
        $group_data = filter_group($group_data, $importGroupInfo,$mustImportusers);
       
        if (isset($group_data[1])) $importGroupInfo = $group_data[1];
        if (isset($group_data[0])) $group_data = $group_data[0];                      	                             
                
        $importGroupInfo = import_group_in_db($group_data, $course_id, $importGroupInfo);
        
        
        if (isset($group_data) && isset($importGroupInfo['mustImportTools']) && true === $importGroupInfo['mustImportTools'])
        {           
            if (isset($importGroupInfo['wiki']) && true === $importGroupInfo['wiki'])
            {                 
                if (false === import_data_tool("CLWIKI",$tooldir, $course_id, $importGroupInfo, $usersIdToChange))
                	return false;
            }
            else if (null != $importGroupInfo['id'])
            {
                if (false === create_wiki($importGroupInfo['id'], $importGroupInfo['name'].' - Wiki'))
                	return false;
            }                          
            if (isset($importGroupInfo['forum']) && true === $importGroupInfo['forum'])
            {
                if (false === import_data_tool("CLFRM",$tooldir, $course_id, $importGroupInfo, $usersIdToChange))
                	return false;
            }
            else if (null != $importGroupInfo['id'])
            {
                if (false === create_forum($importGroupInfo['name']." - forum", '', 2, 1, $importGroupInfo['id'], $course_id))
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
   
    function create_new_group($imported_course_dir, $course_id, $importGroupInfo, $usersIdToChange)
    {   
    	$group_data = import_group_from_file($imported_course_dir);
    	        	            
    	if (isset ($group_data["group_rel_team_user"]))
        {
            $group_data["group_rel_team_user"] = replaceUserId($usersIdToChange, $group_data["group_rel_team_user"], "user");            
        }   
       
        $group_data = filter_group($group_data, $importGroupInfo['group_info']);
         
                                     
        if (isset($group_data[1])) $importGroupInfo['group_info'] = $group_data[1];
        if (isset($group_data[0])) $group_data = $group_data[0];                      	                             
          
          
        $importGroupInfo = import_group_in_db($group_data, $course_id, $importGroupInfo['group_info']);    	
    	    	
    	return $importGroupInfo;
    }
     /**
     * 		
     * Import groups data from file to db and import also their tools.  
     * To know which tool to import and which not, some information are contained in the $importGroupInfo array
     *  
	 * @author Yannick Wautelet <yannick_wautelet@hotmail.com>  
	 * @param  string  $tooldir
	 * @param  string  $course_id
	 * @param  array   $importGroupInfo     - array containing importation rules for a group    
	 * @param  array   $usersIdToChange     - array containing relation bewteen old users ids (at the export)
	 * 										  and new users ids (for the import)
	 * @param  boolean $mustDeleteGroups    - boolean to indicate if the old groups must be deleted or not
	 * @return false if a problem occured, true if not.         
	 * 
 	 */    
    function import_group($tooldir,$imported_course_id, $course_id, $usersIdToChange, $importGroupInfo,$mustImportusers,$mustDeleteOldGroups = false)
    {         
        $group_data = import_group_from_file($tooldir);
        
        if(true === $mustDeleteOldGroups)
        {
        	deleteAllGroups();
        }
        
        if (false !== $group_data)
        {
            foreach($importGroupInfo as $id => $groupInfo)
            {            	
                if ($groupInfo['id'] != null)
                {
                    if (false === create_group_for_import($tooldir, $imported_course_id, $course_id, $groupInfo, $group_data, $usersIdToChange,$mustImportusers))
                    return false;
                }
            }
        }
        return $importGroupInfo;
    }
    
   
    
    /**     
     * 
     * import tool data from the xml file into an array
     *  
	 * @author Yannick Wautelet <yannick_wautelet@hotmail.com> 
	 *  
	 * @param  string $course_id    	  
	 * @return false if a problem occured, the array if not             
	 * 
 	 */      
    function import_tool_from_file($exportedCourseDir)
    {
        if (empty ($exportedCourseDir))
            return claro_failure::set_failure("Empty dir name");
        $file = EXTRACT_PATH."/".$exportedCourseDir."/meta_data/tool/tool.xml";
         
        $xml = xml_parser_create($GLOBALS['charset']);
         
        $tool = new tool_parser;
         
        xml_set_object($xml, $tool);
        xml_set_element_handler($xml, 'start_element', 'end_element');
        xml_set_character_data_handler($xml, 'get_data');
        xml_parser_set_option($xml, XML_OPTION_CASE_FOLDING, false);
        if (!is_dir(EXTRACT_PATH."/".$exportedCourseDir))
        {
            return claro_failure::set_failure("directory does not exist");
        }
        if (!is_file($file))
            return claro_failure::set_failure("file does not exist");
        if (false == ($fp = fopen($file, 'r')))
            return claro_failure::set_failure("can't open file");
        while ($data = fread($fp, 4096))
        {
            if (false == xml_parse($xml, $data, feof($fp)))
                return claro_failure::set_failure("can't parse file");
        }
        fclose($fp);
        xml_parser_free($xml);
        return $tool->get_tab();
    }
	/**     
	 * 
	 * Delete all record from the group table of the course
	 *  
	 * @author Yannick Wautelet <yannick_wautelet@hotmail.com> 
	 *  
	 * @param  string $course_id    	  
	 * @return false if a problem occured, true if not             
	 * 
	 */ 
	function flush_tool_table($course_id)
	{
	        $tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
	         
	        $sql = "DELETE FROM `".$tbl["tool_intro"]."`";
	        if (false === claro_sql_query($sql))
	  			return claro_failure::set_failure('couldnt_write_in_db');
	        $sql = "DELETE FROM `".$tbl["tool"]."`";
	        if (false === claro_sql_query($sql))
	   			return claro_failure::set_failure('couldnt_write_in_db');
	}
    /**     
     * 
     * Delete all record from the group table of the course
     *  
	 * @author Yannick Wautelet <yannick_wautelet@hotmail.com> 
	 *  
	 * @param  string $course_id    	  
	 * @return false if a problem occured, true if not             
	 * 
 	 */ 
    function flush_group_table($course_id)
    {
        $tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));         
        $sql = "DELETE FROM `".$tbl["group_property"]."`";
        if (false === claro_sql_query($sql))
        	return claro_failure::set_failure('couldnt_write_in_db');
        $sql = "DELETE FROM `".$tbl["group_rel_team_user"]."`";
        if (false === claro_sql_query($sql))
        	return claro_failure::set_failure('couldnt_write_in_db');
        $sql = "DELETE FROM `".$tbl["group_team"]."`";
        if (false === claro_sql_query($sql))
        	return claro_failure::set_failure('couldnt_write_in_db');
        	
    	return true;
    }
    /**     
     * 
     * import group data in db
     *  
	 * @author Yannick Wautelet <yannick_wautelet@hotmail.com> 
	 *  
	 * @param  array  $tab              - contain the group data
	 * @param  string $course_id    	  
	 * @return false if a problem occured, true if not             
	 * 
 	 */      
    function import_group_in_db($tab, $course_id, $importGroupInfo)
    {
    	     	  
        $tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
        if(isset ($tab['group_property'])) $importGroupInfo_property = $tab['group_property'];
        if(isset ($tab['group_team'])) $importGroupInfo_team = $tab['group_team'];
        if(isset ($tab['group_rel_team_user'])) $importGroupInfo_rel_team_user = $tab['group_rel_team_user'];        
        if (isset ($tab['group_property']) && is_array($tab['group_property']) && (count($tab['group_property']) > 0))
        {
            $sql = "DELETE FROM`".$tbl["group_property"]."`";
            if (false === claro_sql_query($sql))
        		return claro_failure::set_failure('couldnt_write_in_db');
            $sql = "INSERT INTO `".$tbl["group_property"].'` (self_registration,nbGroupPerUser,private,
                											  forum,document,wiki,chat)
                VALUES ("'.(int) $importGroupInfo_property['self_registration'].'","'.(int) $importGroupInfo_property['nbGroupPerUser'].'","'.(int) $importGroupInfo_property['private'].'","'.(int) $importGroupInfo_property['forum'].'","'.(int) $importGroupInfo_property['document'].'","'.(int) $importGroupInfo_property['wiki'].'","'.(int) $importGroupInfo_property['chat'].'")';
            if (false === claro_sql_query($sql))
        		return claro_failure::set_failure('couldnt_write_in_db');
        }
         
        if (isset ($tab['group_team']) && is_array($tab['group_team']) && (count($tab['group_team']) > 0))
        {     
            if ($importGroupInfo_team['id'] == $importGroupInfo['oldId'])
             {   echo "okrrr<br>"; 
                $sql = "INSERT INTO `".$tbl["group_team"].'` (name,description,tutor,maxStudent,secretDirectory)
                    VALUES ("'.addslashes($importGroupInfo_team['name']).'","'.addslashes($importGroupInfo_team['description']).'","'.(int) $importGroupInfo_team['tutor'].'","'.(int) $importGroupInfo_team['maxStudent'].'","'.addslashes($importGroupInfo_team['secretDirectory']).'")';
                 
                if (false === ($id = claro_sql_query_insert_id($sql)))
        				return claro_failure::set_failure('couldnt_write_in_db');
        				
                 
                if (isset ($tab['group_rel_team_user']) && is_array($tab['group_rel_team_user']) && (count($tab['group_rel_team_user']) > 0))
                {
                	foreach ($importGroupInfo_rel_team_user as $tab_content)
                	{                		
                    	$sql = "INSERT INTO `".$tbl["group_rel_team_user"].'` (user,team,status,role)
	                        VALUES ("'.(int) $tab_content['user'].'","'.$id.'","'.(int) $tab_content['status'].'","'.addslashes($tab_content['role']).'")';
                    	if (false === claro_sql_query($sql))
        					return claro_failure::set_failure('couldnt_write_in_db');
                	}
                }
                $importGroupInfo['id'] = $id;
            }
        }
         
        return $importGroupInfo;
    }
    /**     
     * 
     * import group data from the xml file into an array
     *  
	 * @author Yannick Wautelet <yannick_wautelet@hotmail.com> 
	 *  
	 * @param  string $course_id    	  
	 * @return false if a problem occured, the array if not             
	 * 
 	 */      
    function import_group_from_file($exportedCourseDir)
    {
        if (empty ($exportedCourseDir))
            return claro_failure::set_failure("Empty dir name");
        $file = EXTRACT_PATH."/".$exportedCourseDir."/tools/CLGRP/CLGRP.xml";
         
        $xml = xml_parser_create($GLOBALS['charset']);
         
        $importGroupInfo = new group_parser;
         
        xml_set_object($xml, $importGroupInfo);
        xml_set_element_handler($xml, 'start_element', 'end_element');
        xml_set_character_data_handler($xml, 'get_data');
        xml_parser_set_option($xml, XML_OPTION_CASE_FOLDING, false);
        if (!is_dir(EXTRACT_PATH."/".$exportedCourseDir))
        {
            return claro_failure::set_failure("directory does not exist");
        }
        if (!is_file($file))
            return claro_failure::set_failure("file does not exist");
        if (false == ($fp = fopen($file, 'r')))
            return claro_failure::set_failure("can't open file");
        while ($data = fread($fp, 4096))
        {
            if (false == xml_parse($xml, $data, feof($fp)))
                return claro_failure::set_failure("can't parse file");
        }
        fclose($fp);
        xml_parser_free($xml);
        return $importGroupInfo->get_tab();
    }
  
  
    /**     
     * 
     * import manisfest data from the xml file into an array
     *  
	 * @author Yannick Wautelet <yannick_wautelet@hotmail.com> 
	 *  
	 * @param  string $course_id    	  
	 * @return false if a problem occured, the array if not             
	 * 
 	 */       
    function import_manifest_from_file($exportedCourseDir)
    {
        if (empty ($exportedCourseDir))
            return claro_failure::set_failure("Empty dir name");
        $file = EXTRACT_PATH."/".$exportedCourseDir."/meta_data/manifest/manifest.xml";
         
        $xml = xml_parser_create($GLOBALS['charset']);
         
        $manifest = new manifest_parser;
         
        xml_set_object($xml, $manifest);
        xml_set_element_handler($xml, 'start_element', 'end_element');
        xml_set_character_data_handler($xml, 'get_data');
        xml_parser_set_option($xml, XML_OPTION_CASE_FOLDING, false);
        if (!is_dir(EXTRACT_PATH."/".$exportedCourseDir))
            {
            return claro_failure::set_failure("directory does not exist");
        }
        if (!is_file($file))
            return claro_failure::set_failure("file does not exist");
        if (false == ($fp = fopen($file, 'r')))
            return claro_failure::set_failure("can't open file");
        while ($data = fread($fp, 4096))
        {
            if (false == xml_parse($xml, $data, feof($fp)))
                return claro_failure::set_failure("can't parse file");
        }
        fclose($fp);
        xml_parser_free($xml);
        return $manifest->get_tab();
    }
    /**     
     * 
     * import users data from the xml file into an array
     *  
	 * @author Yannick Wautelet <yannick_wautelet@hotmail.com> 
	 *  
	 * @param  string $course_id    	  
	 * @return false if a problem occured, the array if not             
	 * 
 	 */        
    function import_users_from_file($exportedCourseDir)
    {
        if (empty ($exportedCourseDir))
            return claro_failure::set_failure("Empty dir name");
        $file = EXTRACT_PATH."/".$exportedCourseDir."/meta_data/users/users.xml";
         
        $xml = xml_parser_create($GLOBALS['charset']);
         
        $users = new users_parser;
         
        xml_set_object($xml, $users);
        xml_set_element_handler($xml, 'start_element', 'end_element');
        xml_set_character_data_handler($xml, 'get_data');
        xml_parser_set_option($xml, XML_OPTION_CASE_FOLDING, false);
        if (!is_dir(EXTRACT_PATH."/".$exportedCourseDir))
        {
            return claro_failure::set_failure("directory does not exist");
        }
        if (!is_file($file))
            return claro_failure::set_failure("file does not exist");
        if (false == ($fp = fopen($file, 'r')))
            return claro_failure::set_failure("can't open file");
        while ($data = fread($fp, 4096))
        {
            if (false == xml_parse($xml, $data, feof($fp)))
                return claro_failure::set_failure("can't parse file");
        }
        fclose($fp);
        xml_parser_free($xml);
        return $users->get_tab();
    }
   /**     
     * 
     * import users in db
     *  
	 * @author Yannick Wautelet <yannick_wautelet@hotmail.com> 
	 *  
	 * @param  array  $tab              - contain the user data
	 * @param  string $course_id    	  
	 * @param  array  $usersImportInfo  - contain information for the users import
	 * @return false if a problem occured, true if not             
	 * 
 	 */     
    function import_users_in_db($tab,$course_id,$usersImportInfo)
    {
        $tbl = claro_sql_get_main_tbl();
          
        if (is_array($tab))
        {           
            foreach ($usersImportInfo as $tab_content)
            {                   	            	        	  	                        	               	                 	        	            	      	             
            	if(isset($tab_content['mustImportUser']) && true === $tab_content['mustImportUser'])
            	{
            		$data_user = $tab['user'][$tab_content['oldUserId']] ;                                				               	 	                                    		            		
                	$sql = "INSERT INTO `".$tbl["user"].'` (user_id,nom,prenom,username,password,authsource,email,statut,officialCode,phoneNumber,
                    										pictureUri,creatorId)
                    	VALUES ('.(int) $tab_content['newUserId'].',"'.addslashes($data_user['firstname']).'","'.addslashes($data_user['lastname']).'","'.addslashes($data_user['username']).'","'.addslashes($data_user['password']).'","'.addslashes($data_user['authSource']).'","'.addslashes($data_user['email']).'","'.$data_user['statut'].'","'.addslashes($data_user['officialCode']).'","'.addslashes($data_user['phoneNumber']).'","'.addslashes($data_user['pictureUri']).'","'.(int) $data_user['creatorId'].'")';
                                  	
                	if(false === claro_sql_query($sql))
                		return claro_failure::set_failure("coudlnt_write_in_db");
            	}
            	if(isset($tab_content['mustImportUserInCourse']) && true === $tab_content['mustImportUserInCourse'])
            	{
            		$data_rel_course_tab = $tab['rel_course_user'][$tab_content['oldUserId']];
                	$sql = "INSERT INTO `".$tbl["rel_course_user"]."` (code_cours,user_id,statut,role,team,tutor)
                			VALUES ('".addslashes($course_id)."',".(int)$tab_content['newUserId'].",".(int)$data_rel_course_tab['statut'].",".
                					 ($data_rel_course_tab['role'] == ""? "null" : "'".addslashes($data_rel_course_tab['role'])."'").",".(int)$data_rel_course_tab['team'].",".(int)$data_rel_course_tab['tutor'].")";
                	if(false === claro_sql_query($sql))
                		return claro_failure::set_failure("coudlnt_write_in_db");                				               	
            	}
            }         
        }
        return true;
    }
   
  	/**
  	 * Replace oldIds in $tab by newIds contained in usersIdToChange
  	 *
  	 * $tab must be an array with two dimension ($tab[$id][$index])
  	 * For each $id we check if the name $index is the index set in parameters
  	 * If true, we check its values and compare it to the oldUserIds contained in usersIdToChange
  	 * If a values is an old id, we change it.
  	 * 
  	 * 
  	 * @author Yannick Wautelet <yannick_wautelet@hotmail.com> 
  	 * @param  array   $usersIdToChange     - array containing relation bewteen old users ids (at the export)
	 * 										  and new users ids (for the import)
	 *  									  it must be set like set : $usersIdToChange[$id]["oldUserId"]
	 *									  												     ["newUserId"]		
	 * @param  array   $tab					- containing the array with the index to change
	 * @param  string  $index				- is the index name to change of the second dimnsion of $tab 
  	 * @return
  	 *  	 
  	 */
    function replaceUserId($usersIdToChange, $tab, $index)
    {
    	if(isset($usersIdToChange))
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
    	}
        return $tab;
    }
    /**
  	 * Replace oldIds in $tab by newIds contained in importGroupInfo
  	 *
  	 * $tab must be an array with two dimension ($tab[$id][$index])
  	 * For each $id we check if the name $index is the index set in parameters
  	 * If true, we check its values and compare it to the oldIds contained in usersIdToChange
  	 * If a values is an old id, we change it.
  	 *
  	 * @author Yannick Wautelet <yannick_wautelet@hotmail.com> 
  	 * @param  array   $usersIdToChange     - array containing relation bewteen old users ids (at the export)
	 * 										  and new users ids (for the import)
	 *  									  it must be set like set : $usersIdToChange[$id]["oldId"]
	 *									  												     ["newId"]		
	 * @param  array   $tab					- containing the array with the index to change
	 * @param  string  $index				- is the index name to change of the second dimnsion of $tab 
  	 * @return the array with the new ids
  	 *  	 
  	 */
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
   
    /**	 
     *
     * Import tool document file from the zip file to the claroline file system.
     * The name of the tool is set in parameter to define wich documents to import
     * @author Yannick Wautelet <yannick_wautelet@hotmail.com> 
     * @param  string  $toolName			
	 * @param  string  $tooldir  - course_id of the exported course
	 * @param  string  $course_id           - course_id of the course in which the import will occur
	 * @param  array   $importGroupInfo     - array containing importation rules for a group
	 * @return false if a problem occured, true if not
	 */
    function import_documents($toolId, $importedCourseDir, $course_id)
    {           
        $course_path = get_conf("coursesRepositorySys").claro_get_course_path($course_id);
        $archive_path = EXTRACT_PATH."/".$importedCourseDir."/tools/".$toolId."/";
        if (file_exists($archive_path.$toolId.".zip"))
        {
            if (false === extract_archive($archive_path.$toolId.".zip", $course_path))
                claro_failure::set_failure("cant_extract_file");
            else
                return true;
        }     
        return true;
    }   
    /**	 
     *
     * Import all document of a group if needed
     * This mean if importGroupInfo[$toolName] exists and is set to true
     *
     * @author Yannick Wautelet <yannick_wautelet@hotmail.com>    
	 * @param  string  $tooldir  			- directory name where the old data of the imported course are stored
	 * @param  string  $imported_course_id  - course_id of the exported course
	 * @param  string  $course_id           - course_id of the course in which the import will occur
	 * @param  array   $importGroupInfo     - array containing importation rules for a group
	 *	 									  rule like which group documents to import
	 * @return false if a problem occured, true if not
	 */
    function importGroupDocuments($tooldir,$imported_course_id, $courseId, $importGroupInfo)   
    {/*
        if (isset($importGroupInfo['chat']) && 'true' == $importGroupInfo['chat'])
            if (false === importChatDocuments($tooldir,$imported_course_id, $courseId, $importGroupInfo))
            {            	
            	return false;
            }
         
        if (isset($importGroupInfo['document']) && 'true' == $importGroupInfo['document'])
            if (false === importDocDocuments($tooldir, $courseId, $importGroupInfo))
            {            
            	return false;
            }
        
        foreach ($importGroupInfo as $toolName => $groupInfo_data)
        {
        	if('true' == $importGroupInfo[$toolName])
        	{
        		import_documents($toolName, $tooldir, $courseId, $importGroupInfo);
        		// if it returns false, this just means that there is no document file to import            		
        	} 
        }        
        return true;*/
    }
   
   
     
    function create_course($courseOfficialCode
                   ,               $courseHolder
                   ,               $courseEmail
                   ,               $courseCategory
                   ,               $courseTitle
                   ,               $courseLanguage                   
                   ,               $courseVisibility
                   ,               $courseEnrollAllowed
                   ,               $courseEnrollmentKey
                   ,               $courseExpirationDate
                   ,               $extLinkName
                   ,               $extLinkUrl)
    {
    	  // PREPARE COURSE INTERNAL SYSTEM SETTINGS

    global $dbNamePrefix;
    global $siteName;
    global $dateTimeFormatLong;
    global $coursesRepositoryWeb;
    global $_uid;
    global $_user;
    
    if($courseCategory == 'root') $courseCategory = null;
    $courseOfficialCode = ereg_replace('[^A-Za-z0-9_]', '', $courseOfficialCode);
    $courseOfficialCode = strtoupper($courseOfficialCode);

	
    $keys = define_course_keys ($courseOfficialCode,'',$dbNamePrefix);

	

    $courseSysCode      = $keys[ 'currentCourseId'         ];
    $courseDbName       = $keys[ 'currentCourseDbName'     ];
    $courseDirectory    = $keys[ 'currentCourseRepository' ];
    $courseExpirationDate = '';
   
      
            // START COURSE CREATION PORCESSS

            if (   prepare_course_repository($courseDirectory, $courseSysCode)
                && fill_course_repository($courseDirectory)
                && update_db_course($courseDbName)
                && fill_db_course( $courseDbName, $courseLanguage )
                && register_course($courseSysCode
                   ,               $courseOfficialCode
                   ,               $courseDirectory
                   ,               $courseDbName
                   ,               $courseHolder
                   ,               $courseEmail
                   ,               $courseCategory
                   ,               $courseTitle
                   ,               $courseLanguage
                   ,               $_uid
                   ,               $courseVisibility
                   ,               $courseEnrollAllowed
                   ,               $courseEnrollmentKey
                   ,               $courseExpirationDate
                   ,               $extLinkName
                   ,               $extLinkUrl)
                )
            {      // COURSE CREATION  SUCEEEDED

                $display = DISP_COURSE_CREATION_SUCCEED;

                // WARN PLATFORM ADMINISTRATOR OF THE COURSE CREATION

		$mailSubject = get_lang('[%site_name] Course creation %course_name',array('%site_name'=> $siteName ,
                                                                                          '%course_name'=> $courseTitle) );

		$mailBody = get_block('blockCourseCreationEmailMessage', array ( '%date' => $dateTimeFormatLong,
                                                                            '%sitename' => $siteName,
                                                                            '%user_firstname' => $_user['firstName'],
                                                                            '%user_lastname' => $_user['lastName'],
                                                                            '%user_email' => $_user['mail'],
                                                                            '%course_code' => $courseOfficialCode,
                                                                            '%course_title' => $courseTitle,
                                                                            '%course_lecturers' => $courseHolder,
                                                                            '%course_email' => $courseEmail,
                                                                            '%course_category' => $courseCategory,
                                                                            '%course_language' => $courseLanguage,
                                                                            '%course_url' => $coursesRepositoryWeb . $courseDirectory
                                                                          ) );

                // GET THE CONCERNED SENDERS OF THE EMAIL
                $platformAdminList = claro_get_admin_list ();

                foreach( $platformAdminList as $thisPlatformAdmin )
                {
                    claro_mail_user( $thisPlatformAdmin['idUser'], $mailBody, $mailSubject);
                }
            
            $args['courseSysCode'] = $courseSysCode;
            $args['courseDbName'] = $courseDbName;
            $args['courseDirectory'] = $courseDirectory; 
            $args['courseCategory']	= $courseCategory;
            
            //$eventNotifier->notifyEvent("course_created",$args);
            }
            else
            {
                $lastFailure = claro_failure::get_last_failure();

                switch ($lastFailure )
                {
                    case 'READ_ONLY_SYSTEM_FILE' :
                    {
                        $errorList['error'] = 'READ ONLY SYSTEM FILE';
                    } break;

                    default:
                    {
                        $errorList['error'] = 'Error code : '. $lastFailure;
                    }
                }
                $display = DISP_COURSE_CREATION_FAILED;


            }
       return $courseSysCode;
    }
/**
 * Copy a a file or a directory and its content to an other area
 *
 * @param  - $origDirPath (String) - the path of the directory to move
 * @param  - $destination (String) - the path of the new area
 * @param  - $delete (bool) - move or copy the file
 * @return - void no return !!
 */

function import_claro_copy_file($sourcePath, $targetPath)
{
    $fileName = basename($sourcePath);

    if ( is_file($sourcePath) )
    {
        return copy($sourcePath , $targetPath . '/' . $fileName);
    }
    elseif ( is_dir($sourcePath) )
    {
        // check to not copy the directory inside itself
        
        if ( ereg('^'.$sourcePath . '/', $targetPath . '/') ) return false;
		
        if(! file_exists($targetPath . '/' . $fileName)) if ( ! claro_mkdir($targetPath . '/' . $fileName, CLARO_FILE_PERMISSIONS) )   return false;

        $dirHandle = opendir($sourcePath);

        if ( ! $dirHandle ) return false;
	
        $copiableFileList = array();

        while ($element = readdir($dirHandle) )
        {
            if ( $element == '.' || $element == '..') continue;

            $copiableFileList[] = $sourcePath . '/' . $element;
        }

        closedir($dirHandle);

        if ( count($copiableFileList) > 0 )
        {
            foreach($copiableFileList as $thisFile)
            {
                if ( ! import_claro_copy_file($thisFile, $targetPath . '/' . $fileName) ) return false;              
            }
        }

        return true;
    } // end elseif is_dir()
}
?>