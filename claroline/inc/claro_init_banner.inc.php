<?php

ob_start();

?>

<!-- - - - - - - - - - -   Claroline Banner  - - - - - - - - - -  -->

<div id="topBanner">

<div id="platformBanner">

<div id="siteName">
<a href="<?php echo $rootWeb?>index.php" target="_top"><?php echo $siteName ?></a>
</div>

<div id="institution">
<a href="<?php echo $institution['url'] ?>" target="_top"><?php echo $institution['name'] ?></a>
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

</div>

</div>



<?php
/******************************************************************************
                                  USER SECTION
 ******************************************************************************/


if($_uid)
{
?>

<div id="userBanner">

<div id="userLinks">
<?php echo $_user ['firstName'].' '.$_user ['lastName'] ?> : 
<a href="<?php echo $rootWeb?>index.php" target="_top"><?php echo $langMyCourses; ?></a>
 |
<a href="<?php echo $clarolineRepositoryWeb ?>calendar/myagenda.php" target="_top"><?php echo $langMyAgenda; ?></a>
<?php 

if($is_platformAdmin)
{
?>
 | 
<a href="<?php echo $clarolineRepositoryWeb ?>admin/" target="_top"><?php echo $langPlatformAdministration ?></a>
<?php 
} 
?>
 |
<a href="<?php echo $clarolineRepositoryWeb ?>auth/profile.php" target="_top"><?php echo $langModifyProfile; ?></a>
 |
<a href="<?php echo $rootWeb?>index.php?logout=true" target="_top"><?php echo $langLogout; ?></a>
</div>

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
	                      'CLLNP___' => $langLearnPath,
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
                .( $_courseToolData['id'] == $_tid ? 'selected' : '').'>'
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

<div id="course">
<span class="courseName"><a href="<?php echo $coursesRepositoryWeb.$_course['path'] ?>/index.php" target="_top"><?php echo $_course['name'] ?></a></span>
<span class="courseCode"><?php echo $_course['officialCode']," - ", $_course['titular'] ?></span>
</div>


</div>



<?php
	}
} // end if _cid
?>

<hr />
</div>

<?php

/*
 * BORDER BEHAVIOR
 *
 * Note : Maybe you should change the color 
 * if you aim to change the page background color
 */
/******************************************************************************/



/******************************************************************************
                                BREADCRUMB LINE
 ******************************************************************************/

echo "<div id=\"breadCrumbLine\">\n\n";

if ($claro_toolViewOptionEnabled)
{
  	echo "<div id=\"toolViewOption\">\n";
	claro_disp_tool_view_option($_REQUEST['viewMode']);
	echo "\n</div>\n";
}

if( isset($_cid) || isset($nameTools) || is_array($interbredcrump) )
{
    echo "<a href=\"".$rootWeb."index.php\" target=\"_top\">"
        ."<img src=\"".$clarolineRepositoryWeb."img/home.gif\" hspace=\"5\" alt=\"\">"
        .$siteName
        ."</a>";

    if ( isset($_cid) )
    {
        echo " &gt; "
            ."<a href=\"".$coursesRepositoryWeb.$_course['path']."/index.php\" target=\"_top\">"
            .(($langFile == 'course_home') ? '<em>'.$_course['officialCode'].'</em>' : $_course['officialCode'])
            ."</a>\n";
    }

    if (is_array($interbredcrump) )
    {
        while ( (list(,$bredcrumpStep) = each($interbredcrump)) )
        {
            echo	" &gt; "
                    ."<a href=\"",$bredcrumpStep['url']
                    ."\" target=\"_top\">",$bredcrumpStep['name']
                    ."</a>\n";
        }
    }

    if (isset($nameTools) && $langFile != 'course_home')
    {
        if ($noPHP_SELF)
        {
            echo	" &gt; <b>",$nameTools,"</b>\n";
        }
        elseif ($noQUERY_STRING)
        {
            echo	" &gt;  "
                    ."<b>"
                    ."<a href=",$_SERVER['PHP_SELF']," target=\"_top\">"
                    .$nameTools
                    ."</a>"
                    ."</b>\n";
        }
        else
        {
            echo	" &gt; " 
                    ."<b>"
                    ."<a href=",$_SERVER['PHP_SELF'],'?',$QUERY_STRING," target=\"_top\">"
                    .$nameTools
                    ."</a>"
                    ."</b>\n";
        }
    }
}
else
{
	echo "&nbsp;\n";
}

?>

<hr />
</div>


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
