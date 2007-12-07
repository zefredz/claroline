<?php # $Id$

if (!isset($hide_body) || $hide_body == false)
{
	echo "\n</div>\n"
			."<!-- - - - - - - - - - -   End of Claroline Body   - - - - - - - - - - -->\n\n\n";
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

<?php
if(isset($_cid))
{
?>
<div id="courseManager">
<?php printf($lang_footer_p_CourseManager, $_course['officialCode'])?> :
<a href="<?php echo (empty($_course['email'])?$clarolineRepositoryWeb."user/user.php":"mailto:".$_course['email']."?body=".$_course['officialCode']."&amp;subject=[".rawurlencode($siteName)."]") ?>"><?php echo $_course['titular'] ?></a>
</div>
<?php
}
?>

<div id="platformManager">
<?php printf($lang_p_platformManager,$siteName); ?> : 
<a href="mailto:<?php echo $administrator_email."?body=".$_course['officialCode']."&amp;subject=[".rawurlencode($siteName)."]" ?>"><?php echo $administrator_name ?></a>
</div>

<div id="poweredBy">
<?php echo $langPoweredBy ?> <a href="http://www.claroline.net" target="_blank">Claroline</a> &copy; 2001 - 2005
</div>

</div>

<?php
} // if (!isset($hide_footer) || $hide_footer == false)
?>
</body>
</html>
