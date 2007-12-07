<?php # $Id$
if ((bool) stristr($_SERVER['PHP_SELF'], basename(__FILE__))) die('DIRECT CALL FORBIDDEN');

/*
 * The INTRODUCTION MICRO MODULE is used to insert and edit
 * an introduction section on a Claroline Module.
 * It can be inserted on any Claroline Module, provided a connection 
 * to a course Database is already active.
 *
 * The introduction content are stored on a table called "introduction" 
 * in the course Database. Each module introduction has an Id stored on 
 * the table. It is this id that can make correspondance to a specific module.
 *
 * 'introduction' table description
 *   id : int
 *   texte_intro :text
 *
 *
 * usage :
 *
 * $moduleId = XX // specifying the module Id
 * include(moduleIntro.inc.php);
 */

// New trable sructure for intro section v2
// 
// CREATE TABLE `c_ctc01_tool_intro` (
//   `id` int(11) NOT NULL auto_increment,
//   `texte_intro` text,
//   `tid` int(11) NOT NULL default '0',
//   `rank` int(11) default NULL,
//   PRIMARY KEY  (`id`)
// ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=24 ;
        

$tbl_cdb_names = claro_sql_get_course_tbl();
$TBL_INTRODUCTION = $tbl_cdb_names['tool_intro'];

$intro_editAllowed = claro_is_allowed_to_edit();

if ( isset($_REQUEST['introCmd']) && $intro_editAllowed )
{
    $introCmd = $_REQUEST['introCmd'];
}
else
{
    $introCmd = false;
}


$intro_exDel = false;

/*=========================================================
  INTRODUCTION MICRO MODULE - COMMANDS SECTION (IF ALLOWED)
  ========================================================*/

if ($intro_editAllowed)
{
	/* Replace command */

	if( $introCmd == 'exAdd')
    {
        $intro_content = trim($_REQUEST['intro_content']);

            $sql = "INSERT INTO `" . $TBL_INTRODUCTION . "` 
                    SET `tool_id` = '" . (int)$moduleId . "',
                        `content` = '" . addslashes($intro_content) . "'";

           $introId = claro_sql_query_insert_id($sql);

           if ( $introId )
           {
                $eventNotifier->notifyCourseEvent('introsection_modified', $_cid, $_tid, $moduleId, $_gid, '0');
           }
           else
           {
             // unsucceed ...
           }
    }

    if( $introCmd == 'exEd')
    {
        $intro_content = trim($_REQUEST['intro_content']);
        $introId       = $_REQUEST['introId'];

        if ( ! empty($intro_content) )
        {
            $sql = "UPDATE `" . $TBL_INTRODUCTION . "` 
                    SET   `content` = '" . addslashes($intro_content) . "'
                    WHERE `id` = ".(int)$introId;

           if ( claro_sql_query($sql) != false)
           {
                $eventNotifier->notifyCourseEvent('introsection_modified', $_cid, $_tid, $moduleId, $_gid, '0');
           }
           else
           {
             // unsucceed
           }
		}
		else 
		{
			$introCmd = 'exDel';	// got to the delete command
		}
	}

    if ($introCmd == 'rqEd')
    {
    	$sql = "SELECT `id`, `content` 
                FROM `" . $TBL_INTRODUCTION . "`
                WHERE `id` = ".(int)$_REQUEST['introId'];

       $introSettingList = claro_sql_query_fetch_all($sql);

       if (isset($introSettingList[0])) $introSettingList = $introSettingList[0];
       else                             $introSettingList = false;
    
    }


	/* Delete Command */

    if( $introCmd == 'exDel')
    {
        $sql = "DELETE FROM `" . $TBL_INTRODUCTION . "` 
                WHERE `id` = '" . $_REQUEST['introId'] . "'";

        claro_sql_query($sql);
    }
}

/*===========================================
  INTRODUCTION MICRO MODULE - DISPLAY SECTION
  ===========================================*/

/* Determines the correct display */

if ( $intro_editAllowed && ($introCmd == 'rqEd' || $introCmd == 'rqAdd' ) )
{
	$intro_dispDefault = false;
	$intro_dispForm    = true;
	$intro_dispCommand = false;
}
else
{
	$intro_dispDefault = true;
	$intro_dispForm    = false;
    $intro_dispCommand = $intro_editAllowed ;
}

/* Executes the display */

if ($intro_dispForm)
{
    $introContent = isset($introSettingList['content']) ? $introSettingList['content'] : '';
    $introId      = isset($introSettingList['id']) ? $introSettingList['id'] : false;
    $introEditorCmdValue = $introId ? 'exEd' : 'exAdd';

    echo '<form action="' . $_SERVER['PHP_SELF'] . '" method="post">' . "\n"
    .    '<input type="hidden" name="claroFormId" value="'.uniqid('').'">'
    .    '<input type="hidden" name="introCmd" value="'.$introEditorCmdValue.'">'
    .    ($introId ? '<input type="hidden" name="introId" value="'.$introId.'">' : '')
    .    claro_disp_html_area('intro_content', trim($introContent))
    .	'<br />'."\n"
    .   '<input class="claroButton" type="submit" value="' . $langOk . '">'."\n"  
    .   claro_disp_button($_SERVER['PHP_SELF'], $langCancel)
    .   '<br />'."\n"
    .   '</form>'."\n\n"
    ;
}

if ($intro_dispDefault)
{
    $sql = "SELECT `id`, `rank`, `title`, `content`, 
                   `visibility`, `display_date`
            FROM `" . $TBL_INTRODUCTION . "` 
            WHERE `tool_id` = '" . (int)$moduleId . "'";

    $textIntroList = claro_sql_query_fetch_all($sql);

    if ( count($textIntroList) == 0 && $intro_dispCommand )
    {
        echo '<div class="HelpText">' . "\n"
        .    $helpAddIntroText        . "\n" 
        .    '</div>'                 . "\n"
        .    '<p>' . "\n"
        .    '<a class="claroCmd" href="' . $_SERVER['PHP_SELF'] . '?introCmd=rqAdd">'
        .    '<img src="' . $urlAppend . '/claroline/img/edit.gif" alt="" border="0">'
        .    $langAddIntro
        .    '</a>' . "\n"
        .    '</p>' . "\n\n"
        ;

    }
    else
    {
        foreach($textIntroList as $thisTextIntro)
        {
            $introId       = $thisTextIntro['id'];
            $intro_content = claro_parse_user_text($thisTextIntro['content']);
            
            if( trim(strip_tags($intro_content,'<img>')) != '' ) // no need to display a div for an empty string
            {
                echo '<div class="claroIntroSection">' . "\n"
                .    $intro_content . "\n"
                .    '</div>' . "\n\n"
                ;
            }

            if ($intro_dispCommand)
            {
                echo '<p>' . "\n"
                .    '<a class="claroCmd" href="' . $_SERVER['PHP_SELF'] . '?introCmd=rqEd&introId='.$introId.'">'
                .    '<img src="' . $urlAppend . '/claroline/img/edit.gif" alt="' . $langModify . '" border="0">'
                .    '</a>' . "\n"
                .    '<a class="claroCmd" href="' . $_SERVER['PHP_SELF'] . '?introCmd=exDel&introId='.$introId.'" onclick="javascript:if(!confirm(\''.clean_str_for_javascript($langConfirmYourChoice).'\')) return false;"><img src="' . $urlAppend . '/claroline/img/delete.gif" alt="' . $langDelete . '" border="0"></a>' . "\n"
                .    '</p>' . "\n\n"
                ;
            }
        } // end foreach textIntroList

    } // end if count textIntroList > 0
} // end if intro_dispDefault


?>
