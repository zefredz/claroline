<?php // $Id$

/**
 * CLAROLINE
 *
 * @version 1.8 $Revision$
 *
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @package CLIMPORT
 *
 * @author Yannick Wautelet <yannick_wautelet@hotmail.com>
 * @author Claro Team <cvs@claroline.net>
 */

//$tlabelReq = 'CLCRS';
//$cidReq='ES1';
$dialogBox = array();
		
require '../inc/claro_init_global.inc.php';
//if ( ! $_cid || ! $is_courseAllowed ) claro_disp_auth_form(true);

include_once($includePath . '/lib/import.lib.php');

// filter incoming data
$acceptCmd = array('doImport');
$cmd= (isset($_REQUEST['cmd']) && in_array($_REQUEST['cmd'],$acceptCmd)) ? $_REQUEST['cmd'] : '';
$archiveFile = $_cid.".zip";
$filePath = "c:/program files/easyPHP1-8/www/cvs/claroline.test/export";

// command
$taskDoImport = false;
switch($cmd)
{
	case 'doImport' :
	{
		$taskDoImport = true;
	} break;
}
// task

if ($taskDoImport)
{	
	
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
 	 *      - Must be an array of arrays of arrays
 	 * 		- Must be like this : $importGroupInfo[index][group_info][groupInfos]
 	 * 													 [tools][tool_id]
 	 * 		- Each index points to a group
 	 * 		- The 0 index points to the general course
 	 * 		- A groupinfo can be tools id like "CLWIKI", "CLANN", etc.. and must contain a boolean
 	 * 		- A groupinfo can also be "mustImportUsers" to chose to import users or not for this group
 	 * 		- One groupInfo must be "id" and must contain the group_id to import
 	 * 		- "id" must be null when for general course information
 	 * 		- When "id" is not null. A groupInfo can be "mustImportTools". This means we can choose to not import 
 	 * 		  a group tool to replace him with a empty tool.   		 
 	 * 		  
 	 */     
	$importGroupInfo[0]['group_info']['id'] = null;
    $importGroupInfo[0]['group_info']['oldId'] = null;
    $importGroupInfo[0]['group_info']['mustImportUsers'] = true;
    $importGroupInfo[0]['group_info']['manifest'] = true;
    $importGroupInfo[0]['group_info']['tool'] = true;
    $importGroupInfo[0]['tools']['CLCHT'] = true;
    $importGroupInfo[0]['tools']['CLDOC'] = true;
    $importGroupInfo[0]['tools']['CLFRM'] = true;
    $importGroupInfo[0]['tools']['CLWIKI'] = true;
    $importGroupInfo[0]['tools']['CLWRK'] = true;            
    //$importGroupInfo[0]['tools']['CLQWZ'] = true;      
    $importGroupInfo[0]['tools']['CLLNP'] = true;      
    $importGroupInfo[0]['tools']['CLCAL'] = true;
    $importGroupInfo[0]['tools']['CLANN'] = true;    
    /*

    $importGroupInfo[1]['group_info']['id'] = 9;
    $importGroupInfo[1]['group_info']['oldId'] = 9;
    $importGroupInfo[1]['group_info']['mustImportUsers'] = true;
   	$importGroupInfo[1]['group_info']['mustImportTools'] = true;
    $importGroupInfo[1]['tools']['CLCHT'] = true;
    $importGroupInfo[1]['tools']['CLDOC'] = true;
    $importGroupInfo[1]['tools']['CLFRM'] = true;
    $importGroupInfo[1]['tools']['CLWIKI'] = true;*/
 
       
    $archive_file = $filePath."/es1.zip";
    $course_id = $_cid;
   
    $tmpDir = basename($archive_file,".zip");
    $tab = import_manifest_from_file($tmpDir);
   	$exported_course_id = $tab['course']['code'];   	
 
	$errorFound = false;	

    if (false === extract_archive($archive_file, EXTRACT_PATH))
    {    	
    	$errorFound = true;
    	$dialogBox['error'][] = get_lang("Import failed : <br>".claro_failure::get_last_failure());
    }     
    else
    {                         	
    	if (false === ($course_ids = import_manifest($tmpDir, $course_id, $importGroupInfo[0]['group_info'])))
    	{    
	    	$errorFound = true;
       		$dialogBox['error'][] = get_lang("Import failed : <br>".claro_failure::get_last_failure());       	       	  
    	}
    	else
    	{
    		$oldCourse_id = $course_ids['old'];
        	$course_id = $course_ids['new'];        	
        	
	    	$cidReset = true;
    		$cidReq = $course_id;             
    		include ($GLOBALS['includePath'] . '/claro_init_local.inc.php');
    	}
            	
	    if (false === ($usersIdToChange = import_users($tmpDir,$course_id,$importGroupInfo[0]['group_info'])))
    	{
    		$errorFound = true;
    		$dialogBox['error'][] = get_lang("Import failed : <br>".claro_failure::get_last_failure());
    	} 
    	    	
	    if (isset($importGroupInfo[0]['group_info']['mustImportUsers']) && true === $importGroupInfo[0]['group_info']['mustImportUsers']) 
	       	$mustImportusers = true;
	    else $mustImportusers = false;	   
	    
	    if (false === import_tool($tmpDir, $course_id, $importGroupInfo[0]['group_info']))
    	{
    		$errorFound = true;
     	    $dialogBox['error'][] = get_lang("Import failed : <br>".claro_failure::get_last_failure());
    	}   
    	foreach($importGroupInfo as $index => $group_array)
    	{
    		//if it's a group and not the general course
    		if(null !== $group_array['group_info']['id'])
    		{    			
    			//we don't need to import users in groups if we choosed to not import users in the course'
    			if(false === $importGroupInfo[0]['group_info']['mustImportUsers'])
    				$group_array['group_info']['mustImportUsers'] = false;
    			$group_array['group_info'] =  create_new_group($tmpDir, $course_id, $group_array, $usersIdToChange,true);    				
    		}
    		foreach($group_array["tools"] as $tool_label => $mustImportTool)
    		{    			
    			if(true === $group_array["tools"][$tool_label])    			
    			{
		    		if (false === import_data_tool($tool_label,$tmpDir, $course_id, $group_array["group_info"], $usersIdToChange))
		        	{            	
		            	$errorFound = true;
		    	    	$dialogBox['error'][] = get_lang("Import failed : <br>".claro_failure::get_last_failure());
		        	}
    			}		        		
    		}
    	}
    }    
    
   	if (!$errorFound) $dialogBox['error'][] = get_lang('Import succeed');
                  
}


// prepare display
$nameTools = get_lang('Import course');

// Display 
include $includePath . '/claro_init_header.inc.php';

echo claro_html_tool_title($nameTools);
echo claro_html_msg_list($dialogBox);

echo '<a href="' . $_SERVER['PHP_SELF'] . '?cmd=doImport">'. get_lang('Import this course') . '</a>';

include $includePath . '/claro_init_footer.inc.php';
?>