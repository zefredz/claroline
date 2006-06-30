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
require $includePath . '/conf/CLHOME.conf.php'; // conf file

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

<table width="100%" border="0" cellpadding="4" >
<tr>
<td valign="top">

<?php

// INTRODUCTION MESSAGE
if ( file_exists('./textzone_top.inc.html') ) include './textzone_top.inc.html';

if ($is_platformAdmin)
{
    // EDIT COMMAND
    echo '&nbsp;'
    .    '<a style="font-size: smaller" href="claroline/admin/managing/editFile.php?cmd=edit&amp;file=0">'
    .    '<img src="claroline/img/edit.gif" alt="" />' . get_lang('Edit text zone')
    .    '</a>' . "\n"
    ;
}


if ( isset($_uid) )
{
    /*
     * Commands line
     */

    echo '<p><nobr>';

        if ($is_allowedCreateCourse) /* 'Create Course Site' command.
                                         Only available for teacher. */
        {
            echo '<a href="claroline/course/create.php" class="claroCmd">'
            .    '<img src="' . $imgRepositoryWeb . 'course.gif" alt="" /> '
            .    get_lang('Create a course site')
            .    '</a>'
            ;
            if (get_conf('allowToSelfEnroll',true)) echo '</nobr>&nbsp;| <nobr>';
        }

        if (get_conf('allowToSelfEnroll',true))
        {
            echo '<a href="claroline/auth/courses.php?cmd=rqReg&amp;category=" class="claroCmd">'
            .    '<img src="' . $imgRepositoryWeb . 'enroll.gif" alt="" /> '
            .    get_lang('Enrol on a new course')
            .    '</a>'
            .    '&nbsp;| '

            .    '<a href="claroline/auth/courses.php?cmd=rqUnreg" class="claroCmd">'
            .    '<img src="' . $imgRepositoryWeb . 'unenroll.gif" alt="" /> '
            .    get_lang('Remove course enrolment')
            .    '</a>'
            ;
        }

        if ( isset($_REQUEST['category']) )
        {
            echo '</nobr>&nbsp;| <nobr>'
            .    '<a href="' . $_SERVER['PHP_SELF'] . '" class="claroCmd">'
            .    '<img src="' . $imgRepositoryWeb . 'course.gif" alt="" />'
            .    get_lang('My course list')
            .    '</a>'
            ;
        }
        else
        {
            echo '</nobr>&nbsp;| <nobr>'
                .'<a href="'.$_SERVER['PHP_SELF'].'?category=" class="claroCmd">'
                .'<img src="'.$imgRepositoryWeb.'course.gif" alt="" />'
                . get_lang('All platform courses')
                .'</a>'
                ;
        }

        echo  '</nobr></p>' . "\n";
}

if ( $_uid && ! isset($_REQUEST['category']) )
{
    // DISPLAY USER OWN COURSE LIST
    require $includePath . '/index_mycourses.inc.php';
}
else
{
    event_open();

    // DISPLAY PLATFORM COURSE LIST
    require $includePath . '/index_platformcourses.inc.php';
}

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
    if ( get_conf('l10n_platform',true))
    {
        echo claro_display_preferred_language_form();
    }

    // DISPLAY LOGIN FORM
    require $includePath . '/index_loginzone.inc.php';
}

//RIGHT MENU DOCK declaration

$homePageRightMenu = new Dock('homePageRightMenu');

echo $homePageRightMenu->render();

//Include right text zone, if there is any

if ( file_exists('./textzone_right.inc.html') ) include './textzone_right.inc.html';

if ( $is_platformAdmin )
{
    echo '&nbsp;'
    .    '<a style="font-size: smaller" href="claroline/admin/managing/editFile.php?cmd=edit&amp;file=1">'
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