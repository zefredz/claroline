<?php

$langFile = "chat";

require '../inc/claro_init_global.inc.php'; 
// the following constant defines the default display of the learning path browser
// 0 : display only table of content and content
// 1 : display claroline header and footer and table of content, and content

$nameTools  = $langChat;;
if(!empty($nameTools))
{
  $titlePage .= $nameTools.' - ';
}

if(!empty($_course['officialCode']))
{
  $titlePage .= $_course['officialCode'].' - ';
}
$titlePage .= $siteName;

// Redirect previously sent paramaters in the correct subframe (messageList.php)
$paramList = array();

if ( isset($_REQUEST['gidReset']) && $_REQUEST['gidReset'] == true )
{
	$paramList[] = 'gidReset=1';
}

if ( isset($_REQUEST['gidReq']) )
{
	$paramList[] = 'gidReq='.$_REQUEST['gidReq'];
}

if (is_array($paramList))
{
	$paramLine = '?'.implode('&', $paramList);
}


?>

<!doctype html public "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>

<head><title><?php echo $titlePage; ?></title></head>

	<frameset rows="215,*,120" marginwidth="0" frameborder="yes">
		<frame src="chat_header.php" name="topBanner" scrolling="no">
		<frame src="messageList.php#final<?php echo $paramLine ?>" name="messageList">
		<frame src="messageEditor.php" name="messageEditor" scrolling="no">
	</frameset>

</html>
