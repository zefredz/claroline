<?php // $id$

$langFile = "chat";
$tlabelReq = "CLCHT___";

require '../inc/claro_init_global.inc.php'; 

$nameTools  = $langChat;

// STATS & TRACKING
include($includePath.'/lib/events.lib.inc.php');
event_access_tool($_tid, $_SESSION['_courseTool']['label']);


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
		<frame src="messageList.php<?php echo $paramLine ?>#final" name="messageList">
		<frame src="messageEditor.php" name="messageEditor" scrolling="no">
	</frameset>

</html>
