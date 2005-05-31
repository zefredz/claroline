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
    * claro_linker_popup_display.lib
    *
    * is a lib of function for the display of the linker popup.  
    *
    * @author Fallier Renaud
    **/
	
   /**
    * display the navigator in the popup
    *
    * @param $baseServDir System Path to web base value
    * @param $current_crl the current crl of a resource
    */   
	function displayNav( $baseServDir , $current_crl )
    {	    
	 	//allows to browse in a tool
        $nav = new Navigator($baseServDir, $current_crl);
        $elementCRLArray = CRLTool::parseCRL($current_crl);
     	
     	displayGeneralTitle();
     	displayAttachmentList( $current_crl );
     	display( $nav , $current_crl , $elementCRLArray );

        displayLinkerButtons();
    }
    
   /**
    * display the crl attached in the popup
    *
    * @param $current_crl the current crl of a resource
    * @global $caddy,$rootWeb,$imgRepositoryWeb
    */   
    function displayAttachmentList($current_crl)
    {
 		global $caddy,$rootWeb,$imgRepositoryWeb;
 		global $langLinkerDelete,$langEmpty,$langLinkerAttachements;
 		
 		$baseServUrl = $rootWeb;
 		
        $content = $caddy->getAttachmentList();
        
		if( is_array($content) && isset($content["crl"]) && count( $content["crl"] ) > 0 )
        {
            echo "<hr><b>".$langLinkerAttachements."</b><br />\n";
			
			for($i=0 ; $i<(count($content["crl"])) ; $i++)
			{
				echo $content["title"][$i] ;
				echo '&nbsp;<a href="'.$_SERVER["PHP_SELF"].'?cmd=delete&amp;crl='.$content["crl"][$i].'&amp;current_crl='.urlencode($current_crl).'" class="claroCmd">';
                echo $langLinkerDelete."</A><br>\n";
			}

        }
        else
        {
           // AttachmentList is empty
           echo "<hr><b>".$langLinkerAttachements."</b><br />\n";
           echo "&lt;&lt;&nbsp;".$langEmpty."&nbsp;&gt;&gt;<br>\n";
        }
    }
    
   /**
    * display the link for the other course
    *
    * @param $baseServDir
    * @param $current_crl
    */   
    function displayInterfaceOfMyOtherCourse( $baseServDir , $current_crl ) 
    {	    
        $nav = new Navigator( $baseServDir , $current_crl );
        
        displayGeneralTitle();
        displayAttachmentList( $current_crl ); 
    	displayOtherCourse( $nav , $current_crl );    

        displayLinkerButtons();
    }
    
    /**
    * display the link for the public course
    *
    * @param $baseServDir
    * @param $current_crl
    */   
    function displayInterfaceOfPublicCourse( $baseServDir , $current_crl ) 
    {
	    
		$nav = new Navigator( $baseServDir , $current_crl );
		
		displayGeneralTitle();
		displayAttachmentList( $current_crl ); 
    	displayPublicCourse( $nav , $current_crl );    
         
        displayLinkerButtons();
    }
    
    /**
    * display the tree of the current node
    *
    * @param $navigator
    * @param $crl
    * @global $_course a assosiatif array for the info of a course
    */
    function display( $navigator , $crl , $elementCRLArray )
    {
        global $_course;
		global $langLinkerAdd,$langEmpty;
             
        $container = $navigator->getResource();
        $iterator = $container->iterator();
         
        
        
        echo "<div class=\"claroMessageBox\" style=\"margin-top : 1em;margin-bottom : 1em;\">\n";
        
        displayOtherCoursesLink();
        displayPublicCoursesLink();
        displayExternalLink( $crl );
        displayCourseTitle( $navigator );
        displayParentLink ( $navigator );
        displayOtherInfo( $container );
        
        // permeat to check if the resource is empty 
        $passed = FALSE;
        	 
        //--------------------------------------------------------
        // the loop of resources
        //--------------------------------------------------------
        while ($iterator->hasNext() )
        {
		
        	if (! $passed )
        	{
        	    $passed = TRUE;
        	}
        		     
        	$object = $iterator->getNext();

        	if ($object->isContainer() && $object->isVisible() )
        	{
        		echo '<a href="'.$_SERVER["PHP_SELF"].'?cmd=browse&amp;current_crl='. urlencode ($object->getCRL()).'">';
        	    echo $object->getName()."</A>"."\n";
        	}
        	else if ($object->isContainer() && !$object->isVisible() )
        	{
        		echo '<a href="'.$_SERVER["PHP_SELF"].'?cmd=browse&amp;current_crl='. urlencode ($object->getCRL()).'" class="invisible">';
        	    echo $object->getName()."</A>"."\n";
        	}
        	else if(!$object->isContainer() && !$object->isVisible() )
        	{
        		echo '<span class="invisible">'.$object->getName().'</span>';
        	}
        	else
        	{
        	    echo $object->getName();
        	} 
	
        	if ($object->isLinkable() && $object->isVisible() )
        	{  
        		echo "\t".'&nbsp;<a href="'.$_SERVER["PHP_SELF"].'?cmd=add&amp;crl='.urlencode($object->getCRL()).'&amp;current_crl='.urlencode($crl).'" class="claroCmd">';          
				echo "[".$langLinkerAdd."]</A><br>"."\n";
        	}
        	else if($object->isLinkable() && !$object->isVisible() )
        	{
        	    echo "\t".'&nbsp;<a href="'.$_SERVER["PHP_SELF"].'?cmd=add&amp;crl='.urlencode($object->getCRL()).'&amp;current_crl='.urlencode($crl).'" class="claroCmd">'; 
		    	echo "[".$langLinkerAdd."]</A><br>"."\n";
        	}
        	else
        	{
        	    echo "<br>\n";
        	}
        }
        // if a directory is empty
        if (!$passed )
        {
            echo "&lt;&lt;&nbsp;".$langEmpty."&nbsp;&gt;&gt;\n";
        }
        echo "</div>"; 	
    }	
     
    /**
    * display the list the other course
    *
    * @global $platform_id  the id of the platforme
    * @throw E_USER_ERROR if it is not a array
    */
    function displayOtherCourse( $navigator , $crl )
    {
        global $platform_id,$langLinkerMyOtherCourses,$langLinkerAdd;

        echo "<div class=\"claroMessageBox\" style=\"margin-top : 1em;margin-bottom : 1em;\">\n";
        
        displayOtherCoursesLink( FALSE );
        displayPublicCoursesLink();
		displayExternalLink( $crl );
        echo "<br><b>".$langLinkerMyOtherCourses."</b><hr>";
        displayParentLink ( $navigator , FALSE );

        $otherCourseInfo = $navigator->getOtherCoursesList();
        
        if( is_array($otherCourseInfo) )
        {    
            foreach ($otherCourseInfo as $courseInfo )
            {
                $crl = CRLTool::createCRL($platform_id , $courseInfo['code'] );
                echo '<a href="'.$_SERVER["PHP_SELF"].'?fct=add&amp;cmd=browse'.'&current_crl='. urlencode ($crl).'">';
                echo $courseInfo['fake_code']." : ".$courseInfo['intitule']."</A>\n";
            	echo '&nbsp;<a href="'.$_SERVER["PHP_SELF"].'?cmd=add&amp;crl='.urlencode($crl).'&amp;current_crl='.urlencode($crl).'" class="claroCmd">';        
				echo "[".$langLinkerAdd."]</A><br>"."\n";
            }
        }
        else
        {
        	trigger_error("Error: not an array",E_USER_ERROR);
        }
        
       echo "</div>";  
    }
     
     
    /**
    * display the list the public course
    *
    * @global $platform_id  the id of the platforme
    * @throw E_USER_ERROR if it is not a array
    */
    function displayPublicCourse( $navigator , $crl )
    {
        global $platform_id,$langLinkerPublicCourses,$langLinkerAdd;
         
        echo "<div class=\"claroMessageBox\" style=\"margin-top : 1em;margin-bottom : 1em;\">\n";
        
        displayOtherCoursesLink( TRUE );
        displayPublicCoursesLink( FALSE );
		displayExternalLink( $crl );
        echo "<br><b>".$langLinkerPublicCourses."</b><hr>";
        displayParentLink ( $navigator , FALSE );

        $publicCourseInfo = $navigator->getPublicCoursesList();
        
        if( is_array($publicCourseInfo) )
        {    
            foreach ($publicCourseInfo as $courseInfo )
            {
                $crl = CRLTool::createCRL($platform_id , $courseInfo['code'] );
                echo '<a href="'.$_SERVER["PHP_SELF"].'?fct=add&amp;cmd=browse'.'&current_crl='. urlencode ($crl).'">';
                echo $courseInfo['fake_code']." : ".$courseInfo['intitule']."</A>\n";
            	echo '&nbsp;<a href="'.$_SERVER["PHP_SELF"].'?cmd=add&amp;crl='.urlencode($crl).'&amp;current_crl='.urlencode($crl).'" class="claroCmd">';        
                echo "[".$langLinkerAdd."]</A><br>"."\n";
            }
        }
        else
        {
        	trigger_error("Error: not an array",E_USER_ERROR);
        }
        
        echo "</div>"; 
    } 
    /**
    * display the link of the parent of the current node
    *
    * @param $navigator 
    */
    function displayParentLink ( $navigator , $isLink = TRUE)
    {    	
    	global $langUp,$imgRepositoryWeb;
	    
		$crlParent = $navigator->getParent();

        if( $isLink && $crlParent)
        {
            echo '<a href="'.$_SERVER["PHP_SELF"].'?fct=add&amp;cmd=browse'.'&current_crl='. urlencode ($crlParent). '" class="claroCmd">';
            echo "<img src=\"".$imgRepositoryWeb."parent.gif\" border=\"0\" alt=\"\" />".$langUp."</A><br><br>\n";
        }
        else
        {
        	echo "<span class='claroCmdDisabled'><img src=\"".$imgRepositoryWeb."parentdisabled.gif\" border=\"0\" alt=\"\" />".$langUp."</span><br><br>\n";
        } 	
    }
     
    /**
    * display the link of the other course
    *
    * @param $isLink boolean
    * @global $otherCoursesAllowed -> config variable
    */ 
    function displayOtherCoursesLink( $isLink = TRUE )
    {   
    	global $otherCoursesAllowed;//-> config variable
		global $langLinkerMyOtherCourses;
 	
 		if ($otherCoursesAllowed)
 		{
 			if( $isLink )
    		{
    			echo '<a href="'.$_SERVER["PHP_SELF"].'?cmd=browseMyCourses" class="claroCmd">';
        		echo $langLinkerMyOtherCourses."</A>&nbsp;\n";
        	}
        	else
        	{
        		echo '<span class="claroCmdDisabled">'.$langLinkerMyOtherCourses."</span>&nbsp;\n";
        	}
        }

    } 
    
    /**
    * display the link of the public course
    *
    * @param $isLink boolean
    * @global $publicCoursesAllowed -> config variable
    */ 
    function displayPublicCoursesLink( $isLink = TRUE )
    {   
    	global $publicCoursesAllowed;//-> config variable
		global $langLinkerPublicCourses;
 	
 		if ($publicCoursesAllowed)
 		{
 			if( $isLink )
    		{
    			echo '<a href="'.$_SERVER["PHP_SELF"].'?cmd=browsePublicCourses" class="claroCmd">';
        		echo $langLinkerPublicCourses."</A>&nbsp;\n";
        	}
        	else
        	{
        		echo '<span class="claroCmdDisabled">'.$langLinkerPublicCourses."</span>&nbsp;\n";
        	}
        }

    } 
    
    /**
    * display the title of the course
    *
    * @param $navigator
    */ 
    function displayCourseTitle( $navigator )
    {
    	echo "<br><b>".$navigator->getCourseTitle()."</b><hr>";
    } 
    
    /**
    * display other info for exemple in the forum tool 
    * display whereiam
    *
    * @param $container
    */ 
    function displayOtherInfo( $container )
    {
    	if ($container->getName() != "")
        {
            echo "<h2>".$container->getName()." </h2>\n";
        }
    }
    
    /**
    * display the general title 
    * 
    * @global $langLinkerResourceAttachment
    */ 
    function displayGeneralTitle()
    {    
    	global $langLinkerResourceAttachment;
    		
    	echo "<h1>".$langLinkerResourceAttachment."</h1>";
    }
    
    /**
    * display the link of the external link
    *
    * @global $externalLinkAllowed,$langLinkerExternalLink
    */ 
    function displayExternalLink($current_crl)
    {    
    	global $externalLinkAllowed;//-> config variable
		global $langLinkerExternalLink;
 		
 		if ($externalLinkAllowed)
 		{	
    		echo "<A href=\"http://claroline.net\" class=\"claroCmd\" onclick=\"prompt_popup_for_external_link('".$current_crl."');return false;\">"; 
			echo $langLinkerExternalLink."</A>\n";
		}
		
    }
    
    
    function displayLinkerButtons()
    {
    	global $langLinkerClosePopup;
    	
    	echo "<input type=\"submit\" onclick=\"linker_confirm();return false;\" value=\"".$langLinkerClosePopup."\" \n/>";
    }
?>
