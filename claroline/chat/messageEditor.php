<?php 
require '../inc/claro_init_global.inc.php';
$is_allowedToManage = $is_courseAdmin;

// header

$htmlHeadXtra[] = "
<script>
function prepare_message()
{
	document.chatForm.chatLine.value=document.chatForm.msg.value;
	document.chatForm.msg.value = \"\";
	document.chatForm.msg.focus();
	return true;
}
</script>";

$hide_banner=TRUE;
include($includePath."/claro_init_header.inc.php");


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
<a href="messageList.php?reset=true" target="messageList"><?php echo $langResetChat ?></a> | 
<a href="messageList.php?store=true" target="messageList"><?php echo $langStoreChat ?></a>
<?php }
?>
</form>
<?php
/*==========================
           FOOTER
  ==========================*/

include($includePath."/claro_init_footer.inc.php");
?>
