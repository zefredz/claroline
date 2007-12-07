<?php // $Id$
/**
 * CLAROLINE
 ******************************************************************************
 * Campus Home Page
 *
 * @version 1.8 $Revision$
 *
 * @copyright (c) 2001-2007 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @package CLINDEX
 *
 * @author Claro Team <cvs@claroline.net>
 *
 */

unset($includePath); // prevent hacking

// Flag forcing the 'current course' reset, as we're not anymore inside a course

$cidReset = TRUE;
$tidReset = TRUE;

// Include Library and configuration file

require './claroline/inc/claro_init_global.inc.php'; // main init
include claro_get_conf_repository() . 'CLHOME.conf.php'; // conf file

require_once get_path('incRepositorySys') . '/lib/courselist.lib.php'; // conf file


// logout request : delete session data

if (isset($_REQUEST['logout']))
{
    // notify that a user has just loggued out
    if (isset($logout_uid)) // Set  by local_init
    {
        $eventNotifier->notifyEvent('user_logout', array('uid' => $logout_uid));
    }
    /* needed to be able to :
     	- log with claroline when 'magic login' has previously been clicked
     	- notify logout event
     	(logout from CAS has been commented in casProcess.inc.php)*/
    if( get_conf('claro_CasEnabled', false) && ( get_conf('claro_CasGlobalLogout') && !phpCAS::checkAuthentication() ) )
    {
    	phpCAS::logout((isset( $_SERVER['HTTPS']) && ($_SERVER['HTTPS']=='on'||$_SERVER['HTTPS']==1) ? 'https://' : 'http://')
                        . $_SERVER['HTTP_HOST'].get_conf('urlAppend').'/index.php'); 
    }
    session_destroy();
}

// CLAROLINE HEADER AND BANNER
require get_path('incRepositorySys') . '/claro_init_header.inc.php';

echo '<table width="100%" border="0" cellpadding="4">' . "\n"
.    '<tr>' . "\n"
.    '<td valign="top">' . "\n"
;

// INTRODUCTION MESSAGE
if ( file_exists('./textzone_top.inc.html') )
{
    include './textzone_top.inc.html';
}
else
{
    echo '<div style="text-align: center">'
    .    '<img src="./claroline/img/logo.gif" border="0" alt="Claroline logo" />' . "\n"
    .    '<p><strong>Claroline Open Source e-Learning</strong></p>' . "\n"
    .    '</div>'
    ;

    if(claro_is_platform_admin())
    {
        echo '<p>'
        .    get_lang('blockTextZoneHelp', array('%textZoneFile' => 'textzone_top.inc.html'))
        .    '</p>' . "\n";
    }
}

if( claro_is_platform_admin() )
{
    echo '<p>'
    .    '<a href="claroline/admin/managing/editFile.php?cmd=rqEdit&amp;file=0">'
    .    '<img src="claroline/img/edit.gif" alt="" />'
    .    get_lang('Edit text zone')
    .    '</a>'
    .    '</p>' . "\n"
    ;
}

if(claro_is_user_authenticated())
{
    if ( file_exists('./platform/textzone/textzone_top.authenticated.inc.html') )
    {
        include './platform/textzone/textzone_top.authenticated.inc.html';

        if( claro_is_platform_admin() )
        {
            echo '<p>'
            .    '<a href="claroline/admin/managing/editFile.php?cmd=rqEdit&amp;file=2">'
            .    '<img src="claroline/img/edit.gif" alt="" />' . get_lang('Edit text zone')
            .    '</a>'
            .    '</p>' . "\n";
        }
    }

}
else
{
    if ( file_exists('./platform/textzone/textzone_top.anonymous.inc.html') )
    {
        include './platform/textzone/textzone_top.anonymous.inc.html';
    }

}
// Dock - Campus homepage - Top

$campusHomePageTop = new Dock('campusHomePageTop');

echo $campusHomePageTop->render();

if ( claro_is_user_authenticated() )
{
   /**
     * Commands line
     */
	$userCommands = array();

    $userCommands[] = '<a href="' . $_SERVER['PHP_SELF'] . '" class="claroCmd">'
    .    '<img src="' . get_path('imgRepositoryWeb') . 'course.gif" alt="" /> '
    .    get_lang('My course list')
    .    '</a>';

    if (claro_is_allowed_to_create_course()) // 'Create Course Site' command. Only available for teacher.
    {
        $userCommands[] = '<a href="claroline/course/create.php" class="claroCmd">'
        .    '<img src="' . get_path('imgRepositoryWeb') . 'course.gif" alt="" /> '
        .    get_lang('Create a course site')
        .    '</a>';
    }

    if (get_conf('allowToSelfEnroll',true))
    {
        $userCommands[] = '<a href="claroline/auth/courses.php?cmd=rqReg&amp;category=" class="claroCmd">'
        .    '<img src="' . get_path('imgRepositoryWeb') . 'enroll.gif" alt="" /> '
        .    get_lang('Enrol on a new course')
        .    '</a>';

        $userCommands[] = '<a href="claroline/auth/courses.php?cmd=rqUnreg" class="claroCmd">'
        .    '<img src="' . get_path('imgRepositoryWeb') . 'unenroll.gif" alt="" /> '
        .    get_lang('Remove course enrolment')
        .    '</a>';
    }

    $userCommands[] = '<a href="'.$_SERVER['PHP_SELF'].'?category=" class="claroCmd">'
    .                 '<img src="' . get_path('imgRepositoryWeb') . 'course.gif" alt="" /> '
    .	 get_lang('All platform courses')
    .                 '</a>'
    ;

    echo '<p>' . claro_html_menu_horizontal($userCommands) . '</p>' . "\n";
}

if ( claro_get_current_user_id() )
{
    if ( isset($_REQUEST['category']) || (isset($_REQUEST['cmd']) && $_REQUEST['cmd'] == 'search' ) )
    {
        // DISPLAY PLATFORM COURSE LIST and search result
        require get_path('incRepositorySys') . '/index_platformcourses.inc.php';
    }
    else
    {
        // DISPLAY USER OWN COURSE LIST
        require get_path('incRepositorySys') . '/index_mycourses.inc.php';
    }
}
else
{
    event_open();

    if ( ! get_conf('course_categories_hidden_to_anonymous',false) )
    {
        // DISPLAY PLATFORM COURSE LIST
        require get_path('incRepositorySys') . '/index_platformcourses.inc.php';
    }
}

// Dock - Campus homepage - Bottom

$campusHomePageBottom = new Dock('campusHomePageBottom');

echo $campusHomePageBottom->render();

echo '</td>' . "\n"
.    '<td width="200" valign="top" class="claroRightMenu">'
;

if ( claro_is_user_authenticated() )
{
    // DISPLAY CROSS COURSE DIGEST FOR USER
    require get_path('incRepositorySys') . '/index_mydigest.inc.php';
}
else
{
    // Display preferred language form
    echo claro_display_preferred_language_form();

    // DISPLAY LOGIN FORM
    require get_path('incRepositorySys') . '/index_loginzone.inc.php';
}

//RIGHT MENU DOCK declaration

$homePageRightMenu = new Dock('campusHomePageRightMenu');

echo $homePageRightMenu->render();

//Include right text zone, if there is any
if ( file_exists('./textzone_right.inc.html') )
{
    include './textzone_right.inc.html';
}
elseif(claro_is_platform_admin())
{
    echo '<p>'
    .    get_lang('blockTextZoneHelp', array('%textZoneFile' => 'textzone_right.inc.html'))
    .    '</p>' . "\n";
}

if(claro_is_platform_admin())
{
    echo '<p>'
    .    '<a href="claroline/admin/managing/editFile.php?cmd=rqEdit&amp;file=3">'
    .    '<img src="claroline/img/edit.gif" alt="" />' . get_lang('Edit text zone')
    .    '</a>'
    .    '</p>' . "\n";
}

?>

</td>
</tr>
</table>

<?php

/*
 * CLAROLINE FOOTER
 */

require get_path('incRepositorySys') . '/claro_init_footer.inc.php';

?>
