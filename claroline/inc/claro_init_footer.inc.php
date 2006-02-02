<?php // $Id$
if ((bool) stristr($_SERVER['PHP_SELF'], basename(__FILE__))) die('---');

if (!isset($hide_body) || $hide_body == false)
{
    echo "\n" . '</div>' . "\n"
    .    '<!-- - - - - - - - - - -   End of Claroline Body   - - - - - - - - - - -->' . "\n\n\n"
   ;
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

<div id="campusFooter">
<hr />
<?php 

//FOOTER LEFT DOCK declaration

$footerLeftDock = new Dock('campusFooterLeft');
$appletList = getAppletList($footerLeftDock);
$footerLeftDock->setAppletList($appletList);

if(isset($_cid))
{
$courseManagerOutput = '<div id="courseManager">
'. sprintf(get_lang('_footer_p_CourseManager'), $_course['officialCode']).' :
<a href="'. (empty($_course['email'])?$clarolineRepositoryWeb."user/user.php":"mailto:".$_course['email']."?body=".$_course['officialCode']."&amp;subject=[".rawurlencode($siteName)."]") .'">'. $_course['titular'].'</a>
</div>';
$footerLeftDock->addOutput($courseManagerOutput,true);
}

echo $footerLeftDock->render();

//FOOTER RIGHT DOCK declaration

$footerRightDock = new Dock('campusFooterRight');
$appletList = getAppletList($footerRightDock);
$footerRightDock->setAppletList($appletList);

$platformManagerOutput = '<div id="platformManager">'.sprintf(get_lang('_p_platformManager'),$siteName). ' : 
<a href="mailto:' . $administrator_email."?body=".$_course['officialCode']."&amp;subject=[".rawurlencode($siteName)."]".'">'. $administrator_name .'</a>
</div>';

$footerRightDock->addOutput($platformManagerOutput,true);

echo $footerRightDock->render();


//FOOTER CENTER DOCK declaration

$footerCenterDock = new Dock('campusFooterCenter');
$appletList = getAppletList($footerCenterDock);
$footerCenterDock->setAppletList($appletList);

$poweredByOutput = '<div id="poweredBy">'. get_lang('Powered by') . ' <a href="http://www.claroline.net" target="_blank">Claroline</a> &copy; 2001 - 2006 </div></div>';


$footerCenterDock->addOutput($poweredByOutput,true);

echo $footerCenterDock->render();



} // if (!isset($hide_footer) || $hide_footer == false)
?>
</body>
</html>
