<?php ob_start();?>


<!----------------------  Claroline Banner  ---------------------->

<div class="topBanner">

<table width="100%" cellpadding="4" cellspacing="0" border="0">

<tr>

<td bgcolor="#000066">
<a href="<?php echo $rootWeb?>index.php" target="_top">
<big><b><font color="white"><?php echo $siteName ?></font></b></big>
</a>
</td>

<td align="right" bgcolor="#000066">
<font color="white">
<a href="<?php echo $institution['url'] ?>" target="_top">
<big><b><font color="white"><?php echo $institution['name'] ?></font></b></big>
</a>
<?php

if ($_course['extLink']['name'] != '')    /* --- External Link Section --- */
{
	echo ' / ';
	if ($_course['extLink']['url'] != '')
	{
		?><a href="<?php echo $_course['extLink']['url']?>" target="_top">
    <?php
	}
	?>
    <font color="white"><?php echo $_course['extLink']['name'] ?></font>
    <?php
	if ($_course['extLink']['url'] != '')
	{
    ?>
        </a>
    <?php
	}
}
?>
</font>
</td>

</tr>

<?php



/******************************************************************************
                                  USER SECTION
 ******************************************************************************/


if($_uid)
{
?>

<tr bgcolor="#666666">

<td colspan="2">
<font color="white">
<small>
<b>
<?php echo $_user ['firstName'].' '.$_user ['lastName'] ?> : 

<a href="<?php echo $rootWeb?>index.php" target="_top">
<font color="white"><?php echo $langMyCourses; ?></font>
</a>
 |
<a href="<?php echo $clarolineRepositoryWeb ?>calendar/myagenda.php" target="_top">
<font color="white"><?php echo $langMyAgenda; ?></font>
</a>
<?php 

if($is_platformAdmin)
{
?>
 | 
<a href="<?php echo $clarolineRepositoryWeb ?>admin/" target="_top">
<font color="white"><?php echo $langPlatformAdministration ?></font>
</a>
<?php 
} 
?>
 |
<a href="<?php echo $clarolineRepositoryWeb ?>auth/profile.php" target="_top">
<font color="white"><?php echo $langModifyProfile; ?></font>
</a>
 |
<a href="<?php echo $rootWeb?>index.php?logout=true" target="_top">
<font color="white"><?php echo $langLogout; ?></font>
</a>
</b>
</small>
</font>
</td>

</tr>

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

<tr>
<td bgcolor="#DDDEBC" >
<b>
<a href="<?php echo $coursesRepositoryWeb.$_course['path'] ?>/index.php" target="_top">
<big><font color="#003366"><?php echo $_course['name'] ?></font></big>
</a>
<br>
<small>
<font color="#003366">
<?php echo $_course['officialCode']," - ", $_course['titular'] ?>
</font>
</small>
</b>
</td>

<td bgcolor="#DDDEBC" align="right" >
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
	                      'CLFRM___' => $langForum,
	                      'CLCAL___' => $langAgenda,
	                      'CLCHT___' => $langChat,
	                      'CLDOC___' => $langDocument,
	                      'CLDSC___' => $langDescriptionCours,
	                      'CLGRP___' => $langGroups,
	                      'CLLNP___' => $langLearnPath,
	                      'CLQWZ___' => $langExercise,
	                      'CLWRK___' => $langWork,
	                      'CLUSR___' => $langUser);
	
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

</td>

</tr>
<?php
	}
} // end if _cid
?>
</table>
</div>
<table width="100%">
<tr>
<td>
<?php




/******************************************************************************
                                BREADCRUMB TRAIL
 ******************************************************************************/


if( isset($_cid) || isset($nameTools) || is_array($interbredcrump) )
{
    echo "<small>\n";

    echo "<a href=\"".$rootWeb."index.php\" target=\"_top\">"
        ."<img src=\"".$clarolineRepositoryWeb."img/home.gif\" hspace=\"5\" alt=\"\">"
        .$siteName
        ."</a>";

    if ( isset($_cid) )
    {
        echo " &gt; "
            ."<a href=\"".$coursesRepositoryWeb.$_course['path']."/index.php\" target=\"_top\">"
            .(($langFile == 'course_home') ? '<b>'.$_course['officialCode'].'</b>' : $_course['officialCode'])
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
                    ."<a href=",$PHP_SELF," target=\"_top\">"
                    .$nameTools
                    ."</a>"
                    ."</b>\n";
        }
        else
        {
            echo	" &gt; " 
                    ."<b>"
                    ."<a href=",$PHP_SELF,'?',$QUERY_STRING," target=\"_top\">"
                    .$nameTools
                    ."</a>"
                    ."</b>\n";
        }
    }

    echo "</small><br>\n";

}
?>
</td>
<td align="right">
<?php if ($claro_toolViewOptionEnabled) claro_disp_tool_view_option($_REQUEST['viewMode']); ?>
</td>
</tr>
</table>
<?php

if ($claro_brailleViewMode)
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


<!----------------------  End of Claroline Banner  ---------------------->




