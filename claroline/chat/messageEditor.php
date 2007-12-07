<?php // $Id$
/** 
 * CLAROLINE 
 *
 * @version 1.6 $Revision$
 *
 * @copyright 2001-2005 Universite catholique de Louvain (UCL)
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
<script>
function prepare_message()
{
	document.chatForm.chatLine.value=document.chatForm.msg.value;
	document.chatForm.msg.value = "";
	document.chatForm.msg.focus();
	return true;
}
</script>';

$hide_banner=TRUE;
include($includePath.'/claro_init_header.inc.php');


?>
<form name     = "chatForm" 
	  action   = "messageList.php#final"
	  method   = "post"
	  target   = "messageList"
	  onSubmit = "return prepare_message();">


<input type="text"    name="msg"      size="80">
<input type="hidden"  name="chatLine">
<input type="submit" value=" >> ">
<br />
<?php if ($is_allowedToManage) { ?>
<a class="claroCmd" href="messageList.php?reset=true" target="messageList"><?php echo $langResetChat ?></a> | 
<a class="claroCmd" href="messageList.php?store=true" target="messageList"><?php echo $langStoreChat ?></a>
<?php }
?>
</form>
<?php
/*==========================
           FOOTER
  ==========================*/

include($includePath.'/claro_init_footer.inc.php');
?>