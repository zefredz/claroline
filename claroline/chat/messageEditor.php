<?php // $Id$
/**
 * CLAROLINE
 *
 * @version 1.8 $Revision$
 *
 * @copyright 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @see http://www.claroline.net/wiki/index.php/CLCHT
 *
 * @package CLCHAT
 *
 * @author Claro Team <cvs@claroline.net>
 * @author Christophe Gesché <moosh@claroline.net>
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 *
 */

require '../inc/claro_init_global.inc.php';
$is_allowedToManage = $is_courseAdmin || (isset($_gid) && $is_groupTutor) ;

// header

$htmlHeadXtra[] = '
<script type="text/javascript">
function prepare_message()
{
    document.chatForm.chatLine.value=document.chatForm.msg.value;
    document.chatForm.msg.value = "";
    document.chatForm.msg.focus();
    return true;
}
</script>';


$cmd_menu = array();
if ($is_allowedToManage)
{
    $cmd_menu[] = '<a class="claroCmd" href="messageList.php?cmd=reset" target="messageList">'
    .             get_lang('Reset') . '</a>'
    ;
    $cmd_menu[] = '<a class="claroCmd" href="messageList.php?cmd=store" target="messageList">'
    .             get_lang('Store Chat') . '</a>'
    ;
}


$hide_banner = TRUE;
include $includePath . '/claro_init_header.inc.php' ;

echo '<form name="chatForm" action="messageList.php#final" method="post" target="messageList" onSubmit="return prepare_message();">' . "\n"
.    '<input type="text"    name="msg" size="80">' . "\n"
.    '<input type="hidden"  name="chatLine">' . "\n"
.    '<input type="submit" value=" >> ">' . "\n"
.    '<br />' . "\n"
.    '' . "\n";

if( !empty($cmd_menu) ) echo claro_html::menu_horizontal($cmd_menu) . "\n";

echo '</form>';

include  $includePath . '/claro_init_footer.inc.php' ;
?>
