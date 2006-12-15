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
 * @package CLCHT
 *
 * @author Claro Team <cvs@claroline.net>
 * @author Christophe Gesch� <moosh@claroline.net>
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


$cmdMenu = array();
if ($is_allowedToManage)
{
    $cmdMenu[] = claro_html_cmd_link( 'messageList.php?cmd=reset' . claro_url_relay_context('&amp;')
                                    , get_lang('Reset')
                                    , array('target'=> "messageList")
                                    );
    $cmdMenu[] = claro_html_cmd_link( 'messageList.php?cmd=store' . claro_url_relay_context('&amp;')
                                    , get_lang('Store Chat')
                                    , array('target'=> "messageList")
                                    );
}

$hide_banner = TRUE;
include $includePath . '/claro_init_header.inc.php' ;

echo '<form name="chatForm" action="messageList.php#final" method="post" target="messageList" onSubmit="return prepare_message();">' . "\n"
.    claro_form_relay_context()
.    '<input type="text"    name="msg" size="80">' . "\n"
.    '<input type="hidden"  name="chatLine">' . "\n"
.    '<input type="submit" value=" >> ">' . "\n"
.    '<br />' . "\n"
.    '' . "\n"
.    claro_html_menu_horizontal($cmdMenu)
.    '</form>';

include  $includePath . '/claro_init_footer.inc.php' ;
?>