<?php // $Id$
/******************************************************************************
 * CLAROLINE
 ******************************************************************************
 * Campus Home Page
 *
 * @version 1.8 $Revision$
 * @copyright (c) 2001-2005 Universite catholique de Louvain (UCL)
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

// logout request : delete session data

if (isset($_REQUEST['logout'])) session_destroy();

// CLAROLINE HEADER AND BANNER
require $includePath . '/claro_init_header.inc.php';

?>

<table width="100%" border="0" cellpadding="4" >
<tr>
<td valign="top">

<?php

// INTRODUCTION MESSAGE IF NEEDED
if ( file_exists('./textzone_top.inc.html') ) include './textzone_top.inc.html';

if ($is_platformAdmin) // edit command
{
    echo '&nbsp;'
    .    '<a style="font-size: smaller" href="claroline/admin/managing/editFile.php?cmd=edit&amp;file=0">'
    .    '<img src="claroline/img/edit.gif" alt="" />' . get_lang('EditTextZone')
    .    '</a>' . "\n"
    ;
}


if ( isset($_uid) ) // AUTHENTICATED USER SECTION
{
    require $includePath . '/index_mycourses.inc.php';
}
else // ANONYMOUS (DEFAULT) SECTION
{
    event_open();
    require $includePath . '/index_platformcourses.inc.php';
}

?>

</td>

<td width="200" valign="top" class="claroRightMenu">

<?php
if ( isset($_uid) ) // AUTHENTICATED USER SECTION
{
    require $includePath . '/index_mydigest.inc.php';
}
else // ANONYMOUS (DEFAULT) SECTION
{
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
