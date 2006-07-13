<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );

if (!isset($hide_body) || $hide_body == false)
{
    echo "\n" . '</div>' . "\n"
    .    '<!-- - - - - - - - - - -   End of Claroline Body   - - - - - - - - - - -->' . "\n\n\n"
   ;
}

//echo "<pre>".var_export($_courseToolList,1)."</pre>";

// depends on claro_brailleViewMode (in config)
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

// FOOTER LEFT DOCK declaration

$footerLeftDock = new Dock('campusFooterLeft');

if ( isset($_cid) )
{

    $courseManagerOutput = '<div id="courseManager">' . "\n"
                         . get_lang('Manager(s) for %course_code', array('%course_code' => $_course['officialCode']) ) . ' : ' ;

    if ( empty($_course['email']) )
    {
        $courseManagerOutput .= '<a href="' . $clarolineRepositoryWeb . 'user/user.php">'. $_course['titular'].'</a>';
    }
    else
    {
        $courseManagerOutput .= '<a href="mailto:' . $_course['email'] . '?body=' . $_course['officialCode'] . '&amp;subject=[' . rawurlencode( get_conf('siteName')) . ']' . '">' . $_course['titular'] . '</a>';
    }

    $courseManagerOutput .= '</div>';
    $footerLeftDock->addOutput($courseManagerOutput,true);
}

echo $footerLeftDock->render();

// FOOTER RIGHT DOCK declaration

$footerRightDock = new Dock('campusFooterRight');

$platformManagerOutput = '<div id="platformManager">'
                       . get_lang('Administrator for %site_name', array('%site_name'=>get_conf('siteName'))). ' : '
                       . '<a href="mailto:' . get_conf('administrator_email')
                       . '?body=' . $_course['officialCode']
                       . '&amp;subject=[' . rawurlencode( get_conf('siteName') ) . ']'.'">'
                       . get_conf('administrator_name')
                       . '</a>'
                       . '</div>'
                       ;

$footerRightDock->addOutput($platformManagerOutput,true);

echo $footerRightDock->render();

// FOOTER CENTER DOCK declaration

$footerCenterDock = new Dock('campusFooterCenter');

$poweredByOutput = '<div id="poweredBy">'
                 . get_lang('Powered by')
                 . ' <a href="http://www.claroline.net" target="_blank">Claroline</a> '
                 . '&copy; 2001 - 2006'
                 . '</div>';

$footerCenterDock->addOutput($poweredByOutput,true);

echo $footerCenterDock->render();

} // if (!isset($hide_footer) || $hide_footer == false)

?>
</div>

<?php

    if (CLARO_DEBUG_MODE)
    {
        $claroMsgList = getClaroMessageList();
        if (0 < count($claroMsgList))
        echo claro_html_tool_title('Debug info');
        echo claro_html_msg_list($claroMsgList);
    }

?>

</body>
</html>