<?php

ob_start();

?>

<!-- - - - - - - - - - -   Claroline Banner  - - - - - - - - - -  -->

<div id="topBanner">

<div id="platformBanner">

<span id="siteName"><a href="<?php echo $rootWeb?>index.php" target="_top"><?php echo $siteName ?></a></span>
<span id="institution">
<a href="<?php echo $institution_url ?>" target="_top"><?php echo $institution_name ?></a>
<?php

if ($_course['extLink']['name'] != '')    /* --- External Link Section --- */
{
	echo ' / ';
	if ($_course['extLink']['url'] != '')
	{
		echo "<a href=\"".$_course['extLink']['url']."\" target=\"_top\">";
	}
	echo $_course['extLink']['name'];
	
	if ($_course['extLink']['url'] != '')
	{
	        echo "</a>\n";
	}
}
?>
</span>

<div class="spacer"></div>
</div>



<?php
/******************************************************************************
                                  USER SECTION
 ******************************************************************************/


if($_uid)
{
?>

<div id="userBanner">

<span id="userName"><?php echo $_user ['firstName'].' '.$_user ['lastName'] ?></span>
<ul id="userLinks">
<li><a href="<?php echo $rootWeb?>index.php" target="_top"><?php echo $langMyCourses; ?></a></li>
<li><a href="<?php echo $clarolineRepositoryWeb ?>calendar/myagenda.php" target="_top"><?php echo $langMyAgenda; ?></a></li>
<?php 

if($is_platformAdmin)
{
?>
<li><a href="<?php echo $clarolineRepositoryWeb ?>admin/" target="_top"><?php echo $langPlatformAdministration ?></a></li>
<?php 
} 
?>
<li><a href="<?php echo $clarolineRepositoryWeb ?>auth/profile.php" target="_top"><?php echo $langModifyProfile; ?></a></li>
<li><a href="<?php echo $rootWeb?>index.php?logout=true" target="_top"><?php echo $langLogout; ?></a></li>
</ul>
<div class="spacer"></div>
</div>

<?php
} // end if _uid

/******************************************************************************
                              COURSE SECTION
 ******************************************************************************/

if (isset($_cid))
{
    /*------------------------------------------------------------------------
                         COURSE TITLE, CODE & TITULARS
      ------------------------------------------------------------------------*/
?>

<div id="courseBanner">


<div id="course">
<h2 id="courseName"><a href="<?php echo $coursesRepositoryWeb.$_course['path'] ?>/index.php" target="_top"><?php echo $_course['name'] ?></a></h2>
<span id="courseCode"><?php echo $_course['officialCode']," - ", $_course['titular'] ?></span>
</div>

<div id="courseToolList">
<?php

    /*------------------------------------------------------------------------
                             COURSE TOOLS SELECTOR
      ------------------------------------------------------------------------*/

/*
 * Language initialisation of the tool names
 */
if (is_array($_courseToolList))
{
	$toolNameList = array('CLANN___' => $langAnnouncement,
	                      'CLFRM___' => $langForums,
	                      'CLCAL___' => $langAgenda,
	                      'CLCHT___' => $langChat,
	                      'CLDOC___' => $langDocument,
	                      'CLDSC___' => $langDescriptionCours,
	                      'CLGRP___' => $langGroups,
	                      'CLLNP___' => $langLearningPath,
	                      'CLQWZ___' => $langExercises,
	                      'CLWRK___' => $langWork,
	                      'CLUSR___' => $langUsers);
	
	foreach($_courseToolList as $_courseToolKey => $_courseToolDatas)
	{
	    if (is_null($_courseToolDatas['name']))
	        $_courseToolList[ $_courseToolKey ] [ 'name' ] = $toolNameList[ $_courseToolDatas['label'] ];
	
	    // now recheck to be sure the value is really filled before going further
	    if ($_courseToolList[ $_courseToolKey ] [ 'name' ] =='')
	        $_courseToolList[ $_courseToolKey ] [ 'name' ] = 'No Name';
	
	}

?>

<form action="<?php echo $clarolineRepositoryWeb ?>redirector.php" 
      name="redirector" >

<select name="url" size="1" 
        onchange="top.location=redirector.url.options[selectedIndex].value" >

<option value="<?php echo $coursesRepositoryWeb.$_course['path'] ?>/index.php">
<?php echo $langCourseHome; ?>
</option>
<?php 
    if (is_array($_courseToolList))
    {
        foreach($_courseToolList as $_courseToolKey => $_courseToolData)
        {
            echo '<option value="'.$_courseToolData['url'].'" '
                .( $_courseToolData['id'] == $_tid ? 'selected="selected"' : '').'>'
                .$_courseToolData['name']
                ."</option>\n";
        }
    } // end if is_array _courseToolList
?>
</select>

<noscript>
<input type="submit" name="gotool" validationmsg="ok" value="go">
</noscript>

</form>
</div>
<div class="spacer"></div>
</div>



<?php
	}
} // end if _cid
?>

</div>

<?php

/*
 * BORDER BEHAVIOR
 *
 * Note : Maybe you should change the color 
 * if you aim to change the page background color
 */

/******************************************************************************
                                BREADCRUMB LINE
 ******************************************************************************/


if( isset($_cid) || isset($nameTools) || (isset($interbredcrump) && is_array($interbredcrump)) )
{
    echo "<div id=\"breadcrumbLine\">\n\n<hr />\n";

	echo "<div id=\"breadcrumb\">\n";
	echo "<a href=\"".$rootWeb."index.php\" target=\"_top\">"
        ."<img src=\"".$imgRepositoryWeb."home.gif\" alt=\"\">"
        .$siteName
        ."</a>\n";

    if ( isset($_cid) )
    {
        echo "&gt;&nbsp;<a href=\"".$coursesRepositoryWeb.$_course['path']."/index.php\" target=\"_top\">"
            .((isset($course_homepage) && $course_homepage == TRUE) ? '<b>'.$_course['officialCode'].'</b>' : $_course['officialCode'])
            ."</a>\n";
    }

    if (isset($interbredcrump) && is_array($interbredcrump) )
    {
        while ( (list(,$bredcrumpStep) = each($interbredcrump)) )
        {
            echo	"&gt;&nbsp;<a href=\"",$bredcrumpStep['url']
                    ."\" target=\"_top\">",$bredcrumpStep['name']
                    ."</a>\n";
        }
    }

    if (isset($nameTools) && !(isset($course_homepage) && $course_homepage == TRUE))
    {
        if (isset($noPHP_SELF) && $noPHP_SELF)
        {
            echo	"&gt;&nbsp;<b>",$nameTools,"</b>\n";
        }
        elseif (isset($noQUERY_STRING) && $noQUERY_STRING)
        {
            echo	"&gt;&nbsp;<b>"
                    ."<a href=",$_SERVER['PHP_SELF']," target=\"_top\">"
                    .$nameTools
                    ."</a>"
                    ."</b>\n";
        }
        else
        {
            
            // set Query string to empty if not exists
            if (!isset($QUERY_STRING)) $QUERY_STRING = ""; 

            echo	"&gt;&nbsp;<b>"
                    ."<a href=",$_SERVER['PHP_SELF'],'?',$QUERY_STRING," target=\"_top\">"
                    .$nameTools
                    ."</a>"
                    ."</b>\n";
        }
    }
	echo "</div>\n";

    if ( claro_is_display_mode_available() )
    {
  	    echo "<div id=\"toolViewOption\">\n";
    	if ( isset($_REQUEST['viewMode']) )
	    {
    		claro_disp_tool_view_option($_REQUEST['viewMode']);
	    }
    	else
	    {
    		claro_disp_tool_view_option();
    	}
	    echo "\n</div>\n";
    }

    echo '<div class="spacer"></div>' . "\n"
         . '<hr />' . "\n"
         . '</div>' . "\n";

}
else
{
	echo '<br />';
}

?>



<?php

if ( isset($claro_brailleViewMode) && $claro_brailleViewMode )
{
    $claro_banner = ob_get_contents();
    ob_clean();
}
else
{
    ob_end_flush();
    $claro_banner = false;
}

if( isset($db) )
{
	// connect to the main database.
	// if single database, don't pefix table names with the main database name in SQL queries
	// (ex. SELECT * FROM `table`)
	// if multiple database, prefix table names with the course database name in SQL queries (or no prefix if the table is in
	// the main database)
	// (ex. SELECT * FROM `table_from_main_db`  -  SELECT * FROM `courseDB`.`table_from_course_db`)
	mysql_select_db($mainDbName, $db);
}
?>


<!-- - - - - - - - - - -  End of Claroline Banner  - - - - - - - - - - -->
