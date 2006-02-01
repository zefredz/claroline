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

if (isset($_REQUEST['logout'])) session_destroy();

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
    .    '<img src="claroline/img/edit.gif" alt="" />' . get_lang('EditTextZone')
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
            .    get_lang('CourseCreate')
            .    '</a>'
            ;
            if ($allowToSelfEnroll) echo '&nbsp;|&nbsp;';
        }

        if ($allowToSelfEnroll)
        {
            echo '<a href="claroline/auth/courses.php?cmd=rqReg&amp;category=" class="claroCmd">'
            .    '<img src="'.$imgRepositoryWeb.'enroll.gif" alt="" /> '
            .    get_lang('_enroll_to_a_new_course')
            .    '</a>'
            .    '&nbsp;|&nbsp;'

            .    '<a href="claroline/auth/courses.php?cmd=rqUnreg" class="claroCmd">'
            .    '<img src="'.$imgRepositoryWeb.'unenroll.gif" alt="" /> '
            .    get_lang('_remove_course_enrollment')
            .    '</a>'
            ;
        }

        if ( isset($_REQUEST['category']) )
        {
            echo '&nbsp;|&nbsp;'
                .'<a href="'.$_SERVER['PHP_SELF'].'" class="claroCmd">'
                .'<img src="'.$imgRepositoryWeb.'course.gif" alt="" />'
                . get_lang('MyCourses')
                .'</a>'
                ;
        }
        else
        {
            echo '&nbsp;|&nbsp;'
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

if ( get_conf('l10n_platfrom',true) )
{
    echo claro_display_preferred_language_form();
}

if ( isset($_uid) )
{
    // DISPLAY CROSS COURSE DIGEST FOR USER
    require $includePath . '/index_mydigest.inc.php';
}
else
{
    // DISPLAY LOGIN FORM
    require $includePath . '/index_loginzone.inc.php';
}

if ( file_exists('./textzone_right.inc.html') ) include './textzone_right.inc.html';

if ( $is_platformAdmin )
{
    echo '&nbsp;'
    .    '<a style="font-size: smaller" href="claroline/admin/managing/editFile.php?cmd=edit&amp;file=1">'
    .    '<img src="claroline/img/edit.gif" alt="" />' . get_lang('EditTextZone')
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
