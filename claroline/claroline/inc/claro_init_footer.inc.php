<?php # $Id$
//echo "<pre>".var_export($_courseToolList,1)."</pre>";
?>
<hr noshade size="1">

<table width="100%" border="0">
<tr>
<?php
if(isset($_cid))
{
?>
<td width="30%">
<small>
<?php echo $lang_footer_CourseManager ?> : <a href="<?php echo (empty($_course['email'])?$clarolineRepositoryWeb."user/user.php":"mailto:".$_course['email']."?body=".$_course['officialCode']."&subject=[".rawurlencode($siteName)."]") ?>">
<?php echo $_course['titular'] ?></a>
</small>
</td>
<td align="center" width="*">
<?php
}
else
{
?>
<td width="*">
<?php
}
?>

<small>
<?php echo $langManager." ".$siteName; ?> : <a href="mailto:<?php echo $administrator["email"]."?body=".$_course['officialCode']."&subject=[".rawurlencode($siteName)."]" ?>">
<?php echo $administrator["name"] ?></a>
</small>
</td>
<td align="right" width="30%">
<small>
<?php echo $langPlatform ?> <a href="http://www.claroline.net" target="_blank">Claroline</a> &copy; 2001 - 2004
</small>
</td>
</tr>
</table>
<?php 
	if ($_user['is_devel'] && function_exists( 'printInit')) printInit() ;
?>
</body>
</html>