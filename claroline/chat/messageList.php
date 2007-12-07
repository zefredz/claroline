<?php // $Id$

//  THIS SCRIPT  CHAT SIMPLY WORKS WITH A FLAT FILE WHERE LINES ARE APPENDED. 
//  SIMPLE USER CAN  JUST  WRITE LINES. CHAT MANAGER CAN RESET AND STORE THE 
//  CHAT IF $CHATFORGROUP IS TRUE,  THE FILE  IS RESERVED BECAUSE ALWAYS FORMED 
//  WITH THE GROUP ID OF THE CURRENT USER IN THE CURRENT COURSE.


// CLAROLINE INIT

$langFile = 'chat';

require '../inc/claro_init_global.inc.php';

if ( !$_cid ) claro_disp_select_course();
if ( ! $is_courseAllowed )	claro_disp_auth_form();

require $includePath.'/lib/text.lib.php';
/*============================================================================
                                CONNECTION BLOC
  ============================================================================*/


$coursePath  = $coursesRepositorySys.$_course['path'];
$courseId    = $_cid;
$groupId     = $_gid;

$is_allowedToManage = $is_courseAdmin;
$is_allowedToStore  = $is_courseAdmin;
$is_allowedToReset  = $is_courseAdmin;

$nick               = $_user ['firstName']." ".$_user ['lastName'];





/*============================================================================
                                   CHAT INIT
  ============================================================================*/


// THE CHAT NEEDS A TEMP FILE TO RECORD CONVERSATIONS.
// THIS FILE IS STORED IN THE COURSE DIRECTORY

$curChatRep = $coursePath.'/chat/';

// IN CASE OF AN UPGRADE THE DIRECTORY MAY NOT EXIST 
// A PREVIOUS CHECK (AND CREATE IF NEEDED) IS THUS NECESSARY 

if ( ! is_dir($curChatRep) ) mkdir($curChatRep, 0777);

// DETERMINE IF THE CHAT SYSTEM WILL WORK  
// EITHER AT THE COURSE LEVEL OR THE GROUP LEVEL

if ($_gid)
{
    if ($is_groupMember || $is_groupTutor || $is_courseAdmin)
    {
        $groupContext  = true;
        $courseContext = false;

        $activeChatFile = $curChatRep.$courseId.'.'.$groupId.'.chat.html';
        $onflySaveFile  = $curChatRep.$courseId.'.'.$groupId.'.tmpChatArchive.html';
        $exportFile     = $coursePath.'/group/'.$_group['directory'].'/';
    }
    else
    {
        die('<center>'.$langNotGroupMember.'</center>');
    }
}
else
{
    $groupContext  = false;
    $courseContext = true;

    $activeChatFile = $curChatRep.$courseId.'.chat.html';
    $onflySaveFile  = $curChatRep.$courseId.'.tmpChatArchive.html';
    $exportFile     = $coursePath.'/document/';
}


define('REFRESH_DISPLAY_RATE', 10);

// MAX LINE IN THE ACTIVE CHAT FILE. FOR PERFORMANCE REASON IT IS INTERESTING 
// TO WORK WITH NOT TOO BIG FILE

define('MAX_LINE_IN_FILE', 200); 

// MAXIMUM LINE DIPLAYED TO THE USER SCREEN. AS THE ACTIVE CHAT FILE IS 
// REGULARLY SHRINKED ('SEE MAX_LINE_IN_FILE), KEEPING THIS PARAMETER SMALLER 
// THAN  MAX_LINE_IN_FILE ALLOWS SMOOTH DISPLAY (WHERE NO BIG LINE CHUNK ARE 
// REMOVED WHEN THE EXCESS LINE FROM THE ACTIVE CHAT FILE ARE BUFFERED ON FLY

define('MAX_LINE_TO_DISPLAY',  20);


$dateNow = claro_format_locale_date($dateTimeFormatLong);
$timeNow = claro_format_locale_date('%d/%m/%y [%H:%M]');

if ( ! file_exists($activeChatFile))
{
  // create the file
	$fp = @fopen($activeChatFile, 'w')
	       or die ("<center>".$langCannotInitChat."</center>");
	fclose($fp);
	
  $dateLastWrite = $langNewChat;
}








/*============================================================================
                                    COMMANDS
  ============================================================================*/
       



/*----------------------------------------------------------------------------
                                 RESET COMMAND
  ----------------------------------------------------------------------------*/
          

if ($reset && $is_allowedToReset)
{
	$fchat = fopen($activeChatFile,'w');
	fwrite($fchat, "<small>".$timeNow." -------- ".$langChatResetBy." ".$nick." --------</small><br />\n");
	fclose($fchat);

	@unlink($onflySaveFile);
}




 /*----------------------------------------------------------------------------
                                 STORE COMMAND
 ----------------------------------------------------------------------------*/

if ($store && $is_allowedToStore)
{
    $chatDate = 'chat.'.date('Y-m-j').'_';
 
    // TRY DO DETERMINE A FILE NAME THAT DOESN'T ALREADY EXISTS 
    // IN THE DIRECTORY WHERE THE CHAT EXPORT WILL BE STORED

    $i = 1;
    while ( file_exists($exportFile.$chatDate.$i.".html") ) $i++;

    $saveIn = $chatDate.$i.'.html';

    // COMPLETE THE ON FLY BUFFER FILE WITH THE LAST LINES DISPLAYED 
    // BEFORE PROCEED TO COMPLETE FILE STORAGE

    buffer( implode('', file($activeChatFile) )."</body>\n</html>\n",
            $onflySaveFile);

	if (copy($onflySaveFile, $exportFile.$saveIn) )
	{
		$saveStatusString	= "<blockquote>"
									."<a href=\"../document/document.php\" target=\"top\">"
									."<strong>".$saveIn."</strong>"
									."</a> "
									.$langIsNowInYourDocDir
									."</blockquote>\n";
				
    	@unlink($onflySaveFile);
	}
	else
	{
		$saveStatusString = "<blockquote>".$langCopyFailed."</blockquote>\n";
	}
}




/*----------------------------------------------------------------------------
                             'ADD NEW LINE' COMMAND
  ----------------------------------------------------------------------------*/

if ($chatLine)
{
	$fchat = fopen($activeChatFile,'a');
	$chatLine = htmlspecialchars( stripslashes($chatLine) );
	$chatLine = ereg_replace("(http://)(([[:punct:]]|[[:alnum:]])*)","<a href=\"\\0\" target=\"_blank\">\\2</a>",$chatLine);

	fwrite($fchat,
	       '<small>'
	       .$timeNow.' <b>'.$nick.'</b> &gt; '.$chatLine
	       ."</small><br />\n");
	
	fclose($fchat);
}








 /*============================================================================
                              DISPLAY MESSAGE LIST
 ============================================================================*/

if ( !$dateLastWrite )
{
  $dateLastWrite = $langDateLastWrite
                  .strftime( $dateTimeFormatLong , filemtime($activeChatFile) );
}


// WE DON'T SHOW THE COMPLETE MESSAGE LIST.
// WE TAIL THE LAST LINES


$activeLineList  = file($activeChatFile);
$activeLineCount = count($activeLineList);

$excessLineCount = $activeLineCount - MAX_LINE_TO_DISPLAY;
if ($excessLineCount < 0) $excessLineCount = 0;
$excessLineList = array_splice($activeLineList, 0 , $excessLineCount);
$curDisplayLineList = $activeLineList;



// DISPLAY

// CHAT MESSAGE LIST OWN'S HEADER

echo "<html>\n"
    ."<head>\n"
    ."<meta http-equiv=\"refresh\" content=\"".REFRESH_DISPLAY_RATE.";url=".$_SERVER['PHP_SELF']."#final\">\n"
    ."<link rel=\"stylesheet\" type=\"text/css\" href=\"".$clarolineRepositoryWeb."css/default.css\" />\n"
    ."</head>\n"

    ."<body>\n";
if( isset($saveStatusString) )
{
	echo $saveStatusString;
}
echo implode("\n", $curDisplayLineList) // LAST LINES
    ."<p align=\"right\"><small>"
    .$dateLastWrite                 // LAST MESSAGE DATE TIME
    ."</small></p>\n"
    ."<a name=\"final\">\n"       // ANCHOR ALLOWING TO DIRECTLY POINT LAST LINE 

    ."</body>\n"
    ."</html>\n";



// FOR PERFORMANCE REASON, WE TRY TO KEEP THE ACTIVE CHAT FILE OF REASONNABLE 
// SIZE WHEN THE EXCESS LINES BECOME TOO HIGH WE REMOVE THEM FROM THE ACTIVE 
// CHAT FILE AND STORE THEM IN A SORT OF 'ON FLY BUFFER' WHILE WAITHING A 
// POSSIBLE EXPORT FOR DEFINITIVE STORAGE


if ($activeLineCount > MAX_LINE_IN_FILE)
{

	// STORE THE EXCESS LINES INTO THE 'ON FLY BUFFER'

    buffer(implode('',$excessLineList), $onflySaveFile);

	// REFLESH THE ACTIVE CHAT FILE TO KEEP ONLY NON SAVED TAIL

	$fp = fopen($activeChatFile, 'w');
	fwrite($fp, implode("\n", $curDisplayLineList));
}

//////////////////////////////////////////////////////////////////////////////

function buffer($content, $tmpFile)
{
	if ( ! file_exists($tmpFile) )
	{
        $content = "<html>\n"
                  ."<body>\n"
                  .$content;
    }

    $fp = fopen($tmpFile, 'a');
    fwrite($fp, $content);
}
?>
