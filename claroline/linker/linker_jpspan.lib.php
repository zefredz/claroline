<?php  
//----------------------------------------------------------------------
// CLAROLINE
//----------------------------------------------------------------------
// Copyright (c) 2001-2005 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see 'credits' file
//----------------------------------------------------------------------
 
   /**
    * linker_jpspan.lib
    *
    * is a lib of function for the linker Jpspan.  
    *
    * @author Fallier Renaud
    **/
    
    
   /**
    * load the Javascript which will be necessary 
    * to the execution of jpspan
    *
    * @global  $htmlHeadXtra,$claroBodyOnload,$otherCoursesAllowed,$publicCoursesAllowed,$externalLinkAllowed
    * @global  $platform_id,global $_course
    * @global langAdd,langDelete,langEmpty,langUp
    */	
	function linker_html_head_xtra()
	{
		global $htmlHeadXtra;
		global $claroBodyOnload;
		global $platform_id;  
        global $_course;
		global $otherCoursesAllowed; // -> config variable
		global $publicCoursesAllowed; // -> config variable
		global $externalLinkAllowed; // -> config variable
		global $imgRepositoryWeb;
		global $langEmpty;
		global $langUp;
		global $langLinkerAdd;
		global $langLinkerAlreadyInAttachementList;
		global $langLinkerAttachements;
		global $langLinkerDelete;
		global $langLinkerExternalLink;
		global $langLinkerMyOtherCourses;
		global $langLinkerUntitled;
		global $langLinkerPublicCourses;
		
			
		
		require_once("../inc/lib/JPSpan/JPSpan.php");
    	require_once("../inc/lib/JPSpan/JPSpan/Include.php");
	
		$htmlHeadXtra[] = "<script type=\"text/javascript\" src=\""
			. path() ."/linker_jpspan_server.php?client\"></script>\n"
			;
		$htmlHeadXtra[] = "<script type=\"text/javascript\" src=\"" 
			. path() . "/arrayutils.js\"></script>\n"
			;
		$htmlHeadXtra[] = "<script type=\"text/javascript\" src=\"" 
			. path() . "/prompt_utils.js\"></script>\n"
			;	
		$htmlHeadXtra[] = "<script type=\"text/javascript\" src=\""
			. path() . "/linker_jpspan_display.js\"></script>\n"
			;
		$htmlHeadXtra[] = "<script type=\"text/javascript\">"
			. "var linklistallreadysubmitted = false;</script>\n"
			;	
			
		$courseCrl = CRLTool::createCRL($platform_id,$_course['sysCode']);	
		$htmlHeadXtra[] = "<script type=\"text/javascript\">
				var coursecrl = '".$courseCrl."';</script>\n";	
		
		if($otherCoursesAllowed)
		{	
			$htmlHeadXtra[] = "<script type=\"text/javascript\">"
				. "var other_courses_allowed = true;</script>\n";
		}
		else
		{
			$htmlHeadXtra[] = "<script type=\"text/javascript\">"
				. "var other_courses_allowed = false;</script>\n";
		}
		
		if($publicCoursesAllowed)
		{	
			$htmlHeadXtra[] = "<script type=\"text/javascript\">"
				. "var public_courses_allowed = true;</script>\n";
		}
		else
		{
			$htmlHeadXtra[] = "<script type=\"text/javascript\">"
				. "var public_courses_allowed = false;</script>\n";	
		}	
		
		if($externalLinkAllowed)
		{	
			$htmlHeadXtra[] = "<script type=\"text/javascript\">"
				. "var external_link_allowed = true;</script>\n";
		}
		else
		{
			$htmlHeadXtra[] = "<script type=\"text/javascript\">"
				. "var external_link_allowed = false;</script>\n";	
		}	

		//lang variable
		$htmlHeadXtra[] = "<script type=\"text/javascript\">"
				. "var lang_add = '".$langLinkerAdd."';</script>\n";	
				
		$htmlHeadXtra[] = "<script type=\"text/javascript\">"
				. "var lang_delete = '".$langLinkerDelete."';</script>\n";	
		
		$htmlHeadXtra[] = "<script type=\"text/javascript\">"
				. "var lang_empty = '".$langEmpty."';</script>\n";	
				
		$htmlHeadXtra[] = "<script type=\"text/javascript\">"
				. "var lang_up = '".$langUp."';</script>\n";	
		
		$htmlHeadXtra[] = "<script type=\"text/javascript\">"
				. "var lang_my_other_courses = '".$langLinkerMyOtherCourses."';</script>\n";
		
		$htmlHeadXtra[] = "<script type=\"text/javascript\">"
				. "var lang_public_courses = '".$langLinkerPublicCourses."';</script>\n";
		
		$htmlHeadXtra[] = "<script type=\"text/javascript\">"
				. "var lang_external_link = '".$langLinkerExternalLink."';</script>\n";
		
		$htmlHeadXtra[] = "<script type=\"text/javascript\">"
				. "var lang_attachements = '".$langLinkerAttachements."';</script>\n";
				
		$htmlHeadXtra[] = "<script type=\"text/javascript\">"
				. "var lang_already_in_attachement_list = '".$langLinkerAlreadyInAttachementList."';</script>\n";
	     
	    $htmlHeadXtra[] = "<script type=\"text/javascript\">"
				. "var img_repository_web  = '".$imgRepositoryWeb ."';</script>\n";
				
		
		$claroBodyOnload[] = "clear_all();";	
		$claroBodyOnload[] = "hide_div('navbox');";	
		$claroBodyOnload[] = "init_shopping_cart();";
	}
	
   /**
    * set the id of resource in the sript 
    * what makes it possible jpspan to recover this id	 
    *
    * @param $isSetResouceId integer of the resource 
    * @global $htmlHeadXtra 
    */	
	function linker_set_local_crl( $isSetResouceId )
	{
		global $htmlHeadXtra;
		
		if( $isSetResouceId )
		{
			$crlSource =  getSourceCrl();
    	
    		$htmlHeadXtra[] = "<script type=\"text/javascript\">
				var localcrl = '".$crlSource."';</script>\n";
		}
		else
		{
			$htmlHeadXtra[] = "<script type=\"text/javascript\">
				var localcrl = false;</script>\n";				
		}
	}
	
	/**
    * the dislay of the linker 
    *
    */	
	function linker_set_display()
    {   
    	echo "<div id=\"shoppingCart\" style=\"width:100%\">\n";
    	echo "</div>\n";
    	
    	echo "<div style=\"margin-top : 1em;margin-bottom : 1em;\" id=\"openCloseAttachment\">\n";    
    	echo "<A href=\"#\" onclick=\"change_button('open');\">Add new attachment</A>\n";
    	echo "</div>\n";  
       	
       	echo "<div class=\"claroMessageBox\" style=\"margin-top : 1em;margin-bottom : 1em;\" id=\"navbox\">\n";
    	echo "<div id=\"toolBar\">\n";
    	echo "</div>\n";
    	
    	echo "<div id=\"courseBar\">\n";
    	echo "<hr>\n";
    	echo "</div>\n";
    	
    	echo "<div id=\"nav\">\n";
    	echo "</div>\n";
    	echo "</div>\n";
    }	
    
    	
   

?>