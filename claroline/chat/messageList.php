<?php // $Id$

/*
 * This script  chat simply works with a flat file where lines are appended.
 * Simple user can  just  write lines. Admininistraor can reset and store the 
 * chat if $chatForGroup is true,  the file  is reserved because always formed 
 * with the group id of the current user in the current course.
 */

// CLAROLINE INIT

$langFile = 'chat';
(@include ('../inc/claro_init_global.inc.php')) 
    or die('<center>unable to init claroline</center>');

(@include($includePath.'/lib/text.lib.php'))
    or die ('<center>unable to start script</center>');


// CHAT MESSAGE LIST OWN'S HEADER

echo	'<html>'

		.'<head>'
		.'<meta http-equiv="refresh" content="200;url="'.$PHP_SELF.'">'
		.'<link rel="stylesheet" type="text/css" href="'.$clarolineRepositoryWeb.'css/default.css" />'
		.'</head>'

		.'<body>';

if (! $is_courseAllowed) die ("<center>-not allowed----</center>");
 
/*==========================
      CONNECTION BLOC
  ==========================*/

$coursePath  =  $coursesRepositorySys.$_course['path'];
$courseId    = $_cid;
$groupId     = $_gid;

$is_allowedToManage = $is_courseAdmin;
$is_allowedToStore  = $is_courseAdmin;
$is_allowedToReset  = $is_courseAdmin;

$nick              = $_user ['firstName']." ".$_user ['lastName'];





/*==========================
          CHAT INIT
  ==========================*/

// Determine if the chat system will work at the courseor the group level

if ($_gid)
{
    if ($is_groupAllowed)
    {
        $groupContext  = true;
        $courseContext = false;

        $fileChatName   = $courseId.'.'.$groupId.'.chat.txt';
        $tmpArchiveFile = $courseId.'.'.$groupId.'.tmpChatArchive.txt';
        $pathToSaveChat = $coursePath.'/document/';
    }
    else
    {
    	die('<center>not allowed</center>');
    }
}
else
{
    $groupContext  = false;
    $courseContext = true;

    $fileChatName   = $courseId.'.chat.txt';
    $tmpArchiveFile = $courseId.'.tmpChatArchive.txt';
    $pathToSaveChat = $coursePath.'/document/';
}


define('MESSAGE_LINE_NB',  20);
define('MAX_LINE_IN_FILE', 80);

$dateNow = claro_format_locale_date($dateTimeFormatLong);
$timeNow = claro_format_locale_date($timeNoSecFormat);




if ( ! file_exists($fileChatName))
{
	$fp = @fopen($fileChatName, 'w')
		or die ('<center>unable to initialize chat file.</center>');
	fclose($fp);
}


/*==========================
          COMMANDS
  ==========================*/

/*---------------------------
          RESET COMMAND
  ---------------------------*/

if ($reset && $is_allowedToReset)
{
	$fchat = fopen($fileChatName,'w');
	fwrite($fchat, $timeNow." -------- ".$langChatResetBy." ".$nick." --------\n");
	fclose($fchat);

	@unlink($tmpArchiveFile);
}


/*--------------------------
         STORE COMMAND
  --------------------------*/

if ($store && $is_allowedToStore)
{
	$saveIn = "chat.".date("Y-m-j-B").".txt";

	// COMPLETE ARCHIVE FILE WITH THE LAST LINES BEFORE STORING

	buffer(implode('', file($fileChatName)), $tmpArchiveFile);

	if (copy($tmpArchiveFile, $pathToSaveChat.$saveIn) )
	{
		echo	"<blockquote>",
				"<a href=\"../document/document.php\" target=\"top\">",
				"<strong>".$saveIn."</strong>",
				"</a> ".$langIsNowInYourDocDir.
				"</blockquote>";
	}
	else
	{
		echo '<blockquote>'.$langCopyFailed.'</blockquote>';
	}
}



/*-----------------------------
      'ADD NEW LINE' COMMAND
  -----------------------------*/

if ($chatLine)
{
	$fchat = fopen($fileChatName,'a');
	fwrite($fchat,$timeNow.' - '.$nick.' : '.stripslashes($chatLine)."\n");
	fclose($fchat);
}


/*==========================
    DISPLAY MESSAGE LIST
  ==========================*/

/*
 * We don't show the complete message list.
 * We tail the last lines
 */

$fileContent  = file($fileChatName);
$FileNbLine   = count($fileContent);
$lineToRemove = $FileNbLine - MESSAGE_LINE_NB;
if ($lineToRemove < 0) $lineToRemove = 0;
$tmp = array_splice($fileContent, 0 , $lineToRemove);

foreach($fileContent as $thisLine )
{
    echo $thisLine.'<br />';
}

/* 
 * For performance reason, buffer the content 
 * in a temporary archive file
 * once the chat file is too large
 */

if ($FileNbLine > MAX_LINE_IN_FILE)
{

	buffer(implode("",$tmp), $tmpArchiveFile);

	// clean the original file

	$fp = fopen($fileChatName, "w");
	fwrite($fp, implode("", $fileContent));
}


function buffer($content, $tmpFile)
{
	$fp = fopen($tmpFile, "a");
	fwrite($fp, $content);
}


echo	'</body>',
		'</html>';
