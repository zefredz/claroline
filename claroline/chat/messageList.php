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
		.'<meta http-equiv="refresh" content="200;url="'.$PHP_SELF.'#final">'
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

        $fileChatName   = $courseId.'.'.$groupId.'.chat.html';
        $tmpArchiveFile = $courseId.'.'.$groupId.'.tmpChatArchive.html';
        $pathToSaveChat = $coursePath.'/group/'.$_group['directory'].'/';
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

    $fileChatName   = $courseId.'.chat.html';
    $tmpArchiveFile = $courseId.'.tmpChatArchive.html';
    $pathToSaveChat = $coursePath.'/document/';
}


define('MESSAGE_LINE_NB',  20); // no more used // seb
define('MAX_LINE_IN_FILE', 200);

$dateNow = claro_format_locale_date($dateTimeFormatLong);
$timeNow = claro_format_locale_date("%H:%M");

if ( ! file_exists($fileChatName))
{
  // create the file
	$fp = @fopen($fileChatName, 'w')
		or die ('<center>unable to initialize chat file.</center>');
	fclose($fp);
  $dateLastWrite = $langNewChat;
}
else
{
  $dateLastWrite = $langDateLastWrite.date("F d Y H:i:s.", fileatime($fileChatName));
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
	fwrite($fchat, "<small>".$timeNow." -------- ".$langChatResetBy." ".$nick." --------</small><br />\n");
	fclose($fchat);

	@unlink($tmpArchiveFile);
}


/*--------------------------
         STORE COMMAND
  --------------------------*/

if ($store && $is_allowedToStore)
{
  $i = 1;
	$chatDate = "chat.".date("Y-m-j")."_";
  while ( file_exists($pathToSaveChat.$chatDate.$i.".html") )
  {
    $i++;
  }
  $saveIn = $chatDate.$i.".html";
	// COMPLETE ARCHIVE FILE WITH THE LAST LINES BEFORE STORING

  buffer('<html><body>', $tmpArchiveFile);
	buffer(implode('', file($fileChatName)), $tmpArchiveFile);
  buffer('</body></html>', $tmpArchiveFile);
  
	if (copy($tmpArchiveFile, $pathToSaveChat.$saveIn) )
	{
		echo	"<blockquote>",
				"<a href=\"../document/document.php\" target=\"top\">",
				"<strong>".$saveIn."</strong>",
				"</a> ".$langIsNowInYourDocDir.
				"</blockquote>";
    @unlink($tmpArchiveFile);
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
	fwrite($fchat,'<small>'.$timeNow.' <b>'.$nick.'</b> &gt; '.htmlentities(stripslashes($chatLine),ENT_QUOTES)."</small><br />\n");
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

foreach($fileContent as $thisLine )
{
    echo $thisLine;
}
// echo last access time 
echo "<p align=\"right\"><small>".$dateLastWrite."</small></p>";
// echo an anchor to directly display the last line when the page refreshes
echo "<a name=\"final\">";
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
