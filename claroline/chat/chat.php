<?php

// Redirect previously sent paramaters in the correct subframe (messageList.php)

if ($HTTP_GET_VARS['gidReset'])
{
	$paramList[] = 'gidReset=1';
}

if ($HTTP_GET_VARS['gidReq'])
{
	$paramList[] = 'gidReq='.$gidReq;
}

if (is_array($paramList))
{
	$paramLine = '?'.implode('&', $paramList);
}


?>

<!doctype html public "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>

<head><title></title></head>

	<frameset rows="*,*,*" marginwidth="0" frameborder="NO" border="0">
		<frame src="chat_header.php" name="topBanner">
		<frame src="messageList.php<?php echo $paramLine ?>" name="messageList">
		<frame src="messageEditor.php" name="messageEditor">
	</frameset>

</html>