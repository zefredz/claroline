<?php // $Id$
/******************************************************************************
 * CLAROLINE
 ******************************************************************************
 * Campus Home Page
 *
 * @version 1.8 $Revision$
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
 * @license (GPL) GENERAL PUBLIC LICENSE - http://www.gnu.org/copyleft/gpl.html
 * @package CLINDEX
 ******************************************************************************/

unset($includePath); // prevent hacking

// Flag forcing the 'current course' reset, as we're not anymore inside a course

$cidReset = TRUE;
$tidReset = TRUE;

// Include Library and configuration file

require './claroline/inc/claro_init_global.inc.php'; // main init
include claro_get_conf_repository() . 'CLHOME.conf.php'; // conf file

require_once $includePath . '/lib/courselist.lib.php'; // conf file


// logout request : delete session data

if (isset($_REQUEST['logout']))
{
    // notify that a user has just loggued out
    if (isset($logout_uid)) // Set  by local_init
    {
        $eventNotifier->notifyEvent('user_logout', array('uid' => $logout_uid));
    }
    session_destroy();
}

// CLAROLINE HEADER AND BANNER
require $includePath . '/claro_init_header.inc.php';

?>

<table width="100%" border="0" cellpadding="4">
<tr>
<td valign="top">

<?php

// INTRODUCTION MESSAGE
if ( file_exists('./textzone_top.inc.html') ) 
{
    include './textzone_top.inc.html';
}
else
{
    echo '<div style="text-align: center">'
    .    '<img src="./claroline/img/logo.png" border="0" alt="Claroline logo" height="250" width="254" />' . "\n"
    .    '<p><strong>Claroline Open Source e-Learning</strong></p>' . "\n"
    .    '</div>';   
}

if($is_platformAdmin)
{
    echo '<p>'
    .    get_lang('blockTextZoneHelp', array('%textZoneFile' => 'textzone_top.inc.html'))
    .    '</p>' . "\n"
    .    '&nbsp;'
    .    '<a href="claroline/admin/managing/editFile.php?cmd=edit&amp;file=0">'
    .    '<img src="claroline/img/edit.gif" alt="" />' . get_lang('Edit text zone')
    .    '</a>' . "\n"
    ;
}

// Dock - Campus homepage - Top

$campusHomePageTop = new Dock('campusHomePageTop');

echo $campusHomePageTop->render();

if ( isset($_uid) )
{
    /*
     * Commands line
     */
	$userCommands = array();
    
    $userCommands[] = '<a href="' . $_SERVER['PHP_SELF'] . '" class="claroCmd">'
    .    '<img src="' . $imgRepositoryWeb . 'course.gif" alt="" /> '
    .    get_lang('My course list')
    .    '</a>';

    if ($is_allowedCreateCourse) // 'Create Course Site' command. Only available for teacher. 
    {
        $userCommands[] = '<a href="claroline/course/create.php" class="claroCmd">'
        .    '<img src="' . $imgRepositoryWeb . 'course.gif" alt="" /> '
        .    get_lang('Create a course site')
        .    '</a>';
    }

    if (get_conf('allowToSelfEnroll',true))
    {
        $userCommands[] = '<a href="claroline/auth/courses.php?cmd=rqReg&amp;category=" class="claroCmd">'
        .    '<img src="' . $imgRepositoryWeb . 'enroll.gif" alt="" /> '
        .    get_lang('Enrol on a new course')
        .    '</a>';

        $userCommands[] = '<a href="claroline/auth/courses.php?cmd=rqUnreg" class="claroCmd">'
        .    '<img src="' . $imgRepositoryWeb . 'unenroll.gif" alt="" /> '
        .    get_lang('Remove course enrolment')
        .    '</a>';
    }

    $userCommands[] = '<a href="'.$_SERVER['PHP_SELF'].'?category=" class="claroCmd">'
    .	 '<img src="'.$imgRepositoryWeb.'course.gif" alt="" /> '
    .	 get_lang('All platform courses')
    .	 '</a>';
		
    echo '<p>' . claro_html_menu_horizontal($userCommands) . '</p>' . "\n";
}

if ( $_uid )
{
    if ( isset($_REQUEST['category']) || (isset($_REQUEST['cmd']) && $_REQUEST['cmd'] == 'search' ) )
    {
        // DISPLAY PLATFORM COURSE LIST and search result
        require $includePath . '/index_platformcourses.inc.php';
    }
    else
    {
        // DISPLAY USER OWN COURSE LIST
        require $includePath . '/index_mycourses.inc.php';
    }
}
else
{
    event_open();

    // DISPLAY PLATFORM COURSE LIST
    require $includePath . '/index_platformcourses.inc.php';
}

// Dock - Campus homepage - Bottom

$campusHomePageBottom = new Dock('campusHomePageBottom');

echo $campusHomePageBottom->render();

?>

</td>

<td width="200" valign="top" class="claroRightMenu">

<?php

if ( isset($_uid) )
{
    // DISPLAY CROSS COURSE DIGEST FOR USER
    require $includePath . '/index_mydigest.inc.php';
}
else
{
    // Display preferred language form
    echo claro_display_preferred_language_form();

    // DISPLAY LOGIN FORM
    require $includePath . '/index_loginzone.inc.php';
}

//RIGHT MENU DOCK declaration

$homePageRightMenu = new Dock('campusHomePageRightMenu');

echo $homePageRightMenu->render();

//Include right text zone, if there is any
if ( file_exists('./textzone_right.inc.html') ) 
{
    include './textzone_right.inc.html';
}
elseif($is_platformAdmin)
{
    echo '<p>'
    .    get_lang('blockTextZoneHelp', array('%textZoneFile' => 'textzone_right.inc.html'))
    .    '</p>' . "\n";
}

if($is_platformAdmin)
{
    echo '&nbsp;'
    .    '<a href="claroline/admin/managing/editFile.php?cmd=edit&amp;file=1">'
    .    '<img src="claroline/img/edit.gif" alt="" />' . get_lang('Edit text zone')
    .    '</a>' . "\n"
    ;
}

?>

</td>
</tr>
</table>

<?php

/*
 * CLAROLINE FOOTER
 */

require $includePath . '/claro_init_footer.inc.php';

?>
