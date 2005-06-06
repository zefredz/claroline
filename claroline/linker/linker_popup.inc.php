<?php // $Id$
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
    * linkerPopup script
    *
    * @author Fallier Renaud
    **/
    
    // for hide the claro banner
    $hide_banner = TRUE;
    require_once '../inc/claro_init_global.inc.php';
    
    // Library for the linker (navigator and resolver)
    require_once("navigator.lib.php");
    require_once("resolver.lib.php");
    require_once("CRLTool.php");
    require_once("linker_sql.lib.php");
    require_once("linker_popup.lib.php");
    require_once("linker_popup_display.lib.php");
    
    $htmlHeadXtra[] = "<script type=\"text/javascript\">
				var coursecrl = '".CRLTool::createCRL($platform_id,$_course['sysCode'])."';</script>\n";	
	
	$htmlHeadXtra[] = "<script type=\"text/javascript\">"
				. "var lang_linker_prompt_for_url = '".addslashes($langLinkerPromptForUrl)."';</script>\n";
		
	$htmlHeadXtra[] = "<script type=\"text/javascript\">"
				. "var lang_linker_prompt_invalid_url = '".addslashes($langLinkerPromptInvalidUrl)."';</script>\n";
		
	$htmlHeadXtra[] = "<script type=\"text/javascript\">"
				. "var lang_linker_prompt_invalid_email = '".addslashes($langLinkerPromptInvalidEmail)."';</script>\n";
				
	$htmlHeadXtra[] = "<script type=\"text/javascript\" src=\"" 
			. path() . "/prompt_utils.js\"></script>\n";	
    
   	require_once '../inc/claro_init_header.inc.php';
   
    // javascript function 
    echo "<script type='text/javascript'>\n";
    
    echo "function linker_confirm()\n{\n";
    echo "linker_cancel();\n}\n";
    
    echo "function linker_cancel()\n{\n";
    echo "window.close();\n}\n";
    
    echo "</script>\n";
    
    $isToolAllowed = claro_is_allowed_to_edit();     
	
    if ($isToolAllowed)
    {          
       /*-------------------------------------------------*
     	* TO FIX issue in Calendar and Announcement tools *
     	*-------------------------------------------------*/
     	if ( !isset ($_REQUEST['cmd']) )
        {
     		$crlSource = getSourceCrl();
        	if( isset($_SESSION['claro_linker_current']) )
        	{
        		if(	$crlSource != $_SESSION['claro_linker_current'] )
        		{
        			$_SESSION['claro_linker_current'] = $crlSource;
        			$_SESSION['AttachmentList'] = array();
        			$_SESSION['AttachmentList']['crl'] = array();
                    $_SESSION['AttachmentList']['title'] = array();
        			$_SESSION['servAdd'] = array();
        			$_SESSION['servDel'] = array();	
        		}	
        	}
        	$_SESSION['claro_linker_current'] = $crlSource;
        }
        // END OF FIX CALENDAR
        
        // FIX E_ALL
        
        if( is_array( $_SESSION['AttachmentList'] )
            && ( ! isset($_SESSION['AttachmentList']['crl'])
                && ! isset($_SESSION['AttachmentList']['title']) ) )
        {
            $_SESSION['AttachmentList']['crl'] = array();
            $_SESSION['AttachmentList']['title'] = array();
        }
        
        // END OF FIX E_ALL
        
        // init the variable
        $baseServDir = $coursesRepositorySys;
        $baseServUrl = $rootWeb;
        $sysCode = $_course['sysCode'];
        $cmd = "browse";
        $crl = "";
        $current_crl = CRLTool::createCRL($platform_id,$sysCode);
		$caddy = new AttachmentList();

//-------------------------------------------------------------------------------------------------------------------------
	   // init the caddy
		if ( !isset ($_REQUEST['cmd']) )
        {
			$crlSource = getSourceCrl();
            $caddy->initAttachmentList();      
        }
//-------------------------------------------------------------------------------------------------------------------------		
		// get the request variable
        if ( isset ($_REQUEST['cmd']) )
        {
            $cmd = $_REQUEST['cmd'];   
        }
        if ( isset ($_REQUEST['crl']) )
        {
            $crl = stripslashes($_REQUEST['crl']);
        }
        if ( isset ($_REQUEST['current_crl']) )
        {
            $current_crl = stripslashes($_REQUEST['current_crl']);
        }
//-------------------------------------------------------------------------------------------------------------------------
      // command processing   
        if ($cmd == "browse" || $cmd == "delete")
        {
            if( $cmd == "delete")
            {
       
               $caddy->removeItem($crl);
            }
            
            if($current_crl != $crl)
			{
	            displayNav($baseServDir,$current_crl);
	        }
	        else
	        {
	        	displayInterfaceOfMyOtherCourse($baseServDir , $current_crl);  
	        }
        }
        else if($cmd == "add")
        {
            if(! ($caddy->addItem($crl)) )
            {
	    	    $res = new Resolver("");
		    $title = $res->getResourceName($crl);
		   claro_disp_message_box("[".$title."]".$langLinkerAlreadyInAttachementList);
            }

			if($current_crl != $crl)
			{
	            displayNav($baseServDir,$current_crl);
	        }
	        else
	        {
	        	displayInterfaceOfMyOtherCourse($baseServDir , $current_crl);  
	        }
        }
        else if($cmd == "browseMyCourses")
        {
            displayInterfaceOfMyOtherCourse($baseServDir , $current_crl);    
        }
        else if($cmd == "browsePublicCourses")
        {
            displayInterfaceOfPublicCourse($baseServDir , $current_crl);    
        }
        else
        {
            echo "acces denied<br />\n";
        }       
    }
    else
    {
        echo "<h2>acces denied because you are a student or that the course is not select</h2><br />\n";
    }
  
   require_once '../inc/claro_init_footer.inc.php';
       
?>
