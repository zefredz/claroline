<?php # $Id$

if (!isset($hide_body) || $hide_body == false)
{
	echo "\n</div>\n"
			."<!-- --------------------   End of Claroline Body   -------------------- -->\n\n\n";
}

//echo "<pre>".var_export($_courseToolList,1)."</pre>";

// depends on $claro_brailleViewMode
if ( isset($claro_banner) ) 
{
	echo $claro_banner;
}

// don't display the footer text if requested, only display minimal html closing tags
if (!isset($hide_footer) || $hide_footer == false)
{


?>

<div id="footer">

<hr />

<div id="platformManager">
<?php echo $langManager." ".$siteName; ?> : <a href="mailto:<?php echo $administrator["email"]."?body=".$_course['officialCode']."&subject=[".rawurlencode($siteName)."]" ?>">
<?php echo $administrator["name"] ?></a>
</div>

<div id="poweredBy">
<?php echo $langPoweredBy ?> <a href="http://www.claroline.net" target="_blank">Claroline</a> &copy; 2001 - 2004
</div>

<?php
	if ($_user['is_devel'] && function_exists( 'printInit')) printInit() ;
?>

<div id="courseManager">
<?php
if(isset($_cid))
{
?>

<?php echo $lang_footer_CourseManager ?> :
<a href="<?php echo (empty($_course['email'])?$clarolineRepositoryWeb."user/user.php":"mailto:".$_course['email']."?body=".$_course['officialCode']."&subject=[".rawurlencode($siteName)."]") ?>"><?php echo $_course['titular'] ?></a>

<?php
}
else
{
	echo "&nbsp;";
}
?>
</div>

</div>

<?php
} // if (!isset($hide_footer) || $hide_footer == false)
?>
</body>
</html>
