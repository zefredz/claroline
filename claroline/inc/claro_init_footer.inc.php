<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );

$currentCourse =  claro_get_current_course_data();
if (!isset($hide_body) || $hide_body == false)
{
    echo "\n" . '</div>' . "\n"
    .    '<!-- - - - - - - - - - -   End of Claroline Body   - - - - - - - - - - -->' . "\n\n\n"
   ;
}

// depends on claro_brailleViewMode (in config)
if ( isset($claro_banner) )
{
    echo $claro_banner;
}

// don't display the footer text if requested, only display minimal html closing tags
if (!isset($hide_footer) || $hide_footer == false)
{

    echo '<div id="campusFooter">' . "\n"
    .    '<hr />'
    ;

// FOOTER LEFT DOCK declaration

$footerLeftDock = new Dock('campusFooterLeft');

if ( claro_is_in_a_course() )
{

    $courseManagerOutput = '<div id="courseManager">' . "\n"
                         . get_lang('Manager(s) for %course_code', array('%course_code' => $currentCourse['officialCode']) ) . ' : ' ;

    if ( empty($currentCourse['email']) )
    {
        $courseManagerOutput .= '<a href="' . get_module_url('CLUSR') . '/user.php">'. $currentCourse['titular'].'</a>';
    }
    else
    {
        $courseManagerOutput .= '<a href="mailto:' . $currentCourse['email'] . '?body=' . $currentCourse['officialCode'] . '&amp;subject=[' . rawurlencode( get_conf('siteName')) . ']' . '">' . $currentCourse['titular'] . '</a>';
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
                       . '?subject=[' . rawurlencode( get_conf('siteName') ) . ']'.'">'
                       . get_conf('administrator_name')
                       . '</a>';

if ( get_conf('administrator_phone') != '' )
{
    $platformManagerOutput .= '<br />' . "\n" . get_lang('Phone : %phone_number', array('%phone_number' => get_conf('administrator_phone'))) ;
}

$platformManagerOutput .= '</div>' ;

$footerRightDock->addOutput($platformManagerOutput,true);

echo $footerRightDock->render();

// FOOTER CENTER DOCK declaration

$footerCenterDock = new Dock('campusFooterCenter');

$poweredByOutput = '<div id="poweredBy">'
                 . get_lang('Powered by')
                 . ' <a href="http://www.claroline.net" target="_blank">Claroline</a> '
                 . '&copy; 2001 - 2008'
                 . '</div>';

$footerCenterDock->addOutput($poweredByOutput,true);

echo $footerCenterDock->render();

} // if (!isset($hide_footer) || $hide_footer == false)

echo '</div>';

if (get_conf('CLARO_DEBUG_MODE',false))
{
    echo  claro_disp_debug_banner() .  "\n" ;
}

echo '</body>' . "\n"
    . '</html>' ;

?>
