<div class="topBanner">
<table width="100%" cellpadding="4" cellspacing="0" border="0">
<tr>
<td bgcolor="#000066">
<big><b><a href="<?= $rootWeb?>index.php" target="_top"><font color="white"><?= $siteName ?></font></a></b></big>
</td>
<td align="right" bgcolor="#000066">
<font color="white">
<big><b><a href="<?= $institution["url"] ?>" target="_top"><font color="white"><?= $institution["name"] ?></font></a></b></big>
<?php

if ($_course['extLink']['name']!="")    /* --- External Link Section --- */
{
	echo " / ";
	if ($_course['extLink']['url']!="")
	{
		?><a href="<?=$_course['extLink']['url' ]?>" target="_top"><?
	}
	?><font color="white"><?= $_course['extLink']['name'] ?></font><?
	if ($_course['extLink']['url']!="")
	{
		echo "</a>";
	}
}
?>
</font>
</td>
</tr>
<?php

if($_uid)                                    /* --- User Section --- */
{
?>
<tr bgcolor="#666666">
<td colspan="2">
<font color="white">
<small>
<b>
<?= $_user ['firstName']," ",$_user ['lastName'] ?> : 

<a href="<?php echo $rootWeb?>index.php" target="_top"><font color="white"><?php echo $langMyCourses; ?></font></a>
 |
<a href="<?php echo $clarolineRepositoryWeb ?>calendar/myagenda.php" target="_top"><font color="white"><?php echo $langMyAgenda; ?></font></a>
<?php 
if($is_platformAdmin)
{
?>
 | <a href="<?= $clarolineRepositoryWeb ?>admin/" target="_top"><font color="white">Platform Administration</font></a>
<?php 
} 
?>
 |
<a href="<?php echo $clarolineRepositoryWeb ?>auth/profile.php" target="_top"><font color="white"><?php echo $langModifyProfile; ?></font></a>
 |
<a href="<?php echo $rootWeb?>index.php?logout=true" target="_top"><font color="white"><?php echo $langLogout; ?></font></a>
</b>
</small>
</font>
</td>
</tr>
<?php
}

if (isset($_cid))                /* --- Course Title Section --- */
{

?>
<tr>
<td bgcolor="#DDDEBC" colspan="2">
<b>
<big><a href="<?= $coursesRepositoryWeb.$_course['path'] ?>/index.php" target="_top"><font color="#003366"><?= $_course['name'] ?></a></font></big>
<br><small><font color="#003366"><?= $_course['officialCode']," - ", $_course['titular'] ?></font></small>
</b>
</td>
</tr>
<?php
}
?>
</table>
</div>
<small>
<?php
if(isset($_cid))
{
?>
 <a href="<?= $rootWeb ?>index.php" target="_top"><?= $siteName ?></a>
 &gt <a href="<?= $coursesRepositoryWeb.$_course['path']?>/index.php" target="_top"><?php echo ($langFile == 'course_home')?'<b>'.$_course['officialCode'].'</b>':$_course['officialCode']; ?></a>
<?php
}
// if name tools or interbredcrump defined, we don't set the Site name bold
elseif(isset($nameTools) || is_array($interbredcrump))
{
?>
 <a href="<?= $rootWeb ?>index.php" target="_top"><?= $siteName ?></a>
<?php
}
// else we set the Site name bold
else
{
?>
 <a href="<?= $rootWeb ?>index.php" target="_top"><b><?= $siteName ?></b></a>
<?php
}

if (is_array($interbredcrump) )
{
	while ( list(,$bredcrumpStep) = each($interbredcrump) )
	{
		echo	" &gt <a href=\"",$bredcrumpStep['url'],"\" target=\"_top\">",$bredcrumpStep['name'],"</a>\n";
	}
}

if (isset($nameTools) && $langFile != 'course_home')
{
	if ($noPHP_SELF)
	{
		echo	" &gt <b>",$nameTools,"</b>\n";
	}
	elseif ($noQUERY_STRING)
	{
		echo	" &gt <b>",
				"<a href=",$PHP_SELF," target=\"_top\">",
					$nameTools,
				"</a>",
				"</b>\n";
	}
	else
	{
		echo	" &gt <b>",
				"<a href=",$PHP_SELF,'?',$QUERY_STRING," target=\"_top\">",
					$nameTools,
				"</a>",
				"</b>\n";
	}
}

?>
</small>
<br>
<?php
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
<!----------------------  Begin Of script Output  ---------------------->
