<?php $langFile = "chat"; include('../inc/claro_init_global.inc.php'); 
$is_allowedToManage = $is_courseAdmin;
?>

<html>
<head>
<link rel="stylesheet" type="text/css" href="<?php echo $clarolineRepositoryWeb ?>css/default.css"  />
<script>
function prepare_message()
{
	document.chatForm.chatLine.value=document.chatForm.msg.value;
	document.chatForm.msg.value = "";
	document.chatForm.msg.focus();
	return true;
}
</script>

</head>
<body>
<form name     = "chatForm" 
	  action   = "messageList.php#bottom"
	  method   = "get"
	  target   = "messageList"
	  onSubmit = "return prepare_message();">


<input type="text"    name="msg"      size="80">
<input type="hidden"  name="chatLine">
<input type="submit" value=" >> ">
</form>

<?php if ($is_allowedToManage) { ?>
<a href="messageList.php?reset=true" target="messageList"><?= $langResetChat ?></a> | 
<a href="messageList.php?store=true" target="messageList"><?= $langStoreChat ?></a>
<?php }

/*==========================
           FOOTER
  ==========================*/

@include($includePath."/claro_init_footer.inc.php");
