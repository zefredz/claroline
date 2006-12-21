<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );

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
//   `content` text,
//   `rank` int(11) default NULL,
//   PRIMARY KEY  (`id`)
// ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=24 ;


require_once get_path('clarolineRepositorySys') . 'linker/linker.inc.php';

$tbl_cdb_names = claro_sql_get_course_tbl();
$TBL_INTRODUCTION = $tbl_cdb_names['tool_intro'];

$intro_editAllowed = claro_is_allowed_to_edit();


if ( isset($_REQUEST['introCmd']) && $intro_editAllowed )
{
    $introCmd = $_REQUEST['introCmd'];
    // linker_init_session();

    if ( $jpspanEnabled)
    {
        linker_set_local_crl( isset($_REQUEST['introId']) );
    }
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
        // DETERMINE THE ORDER OF THE NEW ANNOUNCEMENT
        $sql = "SELECT (MAX(rank) + 1) AS nextRank
                FROM  `" . $TBL_INTRODUCTION . "`";

        $nextRank = claro_sql_query_get_single_value($sql);

            $intro_content = trim($_REQUEST['intro_content']);

            $sql = "INSERT INTO `" . $TBL_INTRODUCTION . "`
                    SET content = '" . addslashes($intro_content) . "',
                        rank = " . (int) $nextRank;

           $introId = claro_sql_query_insert_id($sql);

           if ( $introId )
           {
             linker_update('CLINTRO_');
           }
           else
           {
             // unsucceed
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
                 linker_update('CLINTRO_');
                // notify that a new introsection has been posted
                $eventNotifier->notifyCourseEvent('introsection_modified', $_cid, $_tid, $moduleId, $_gid, '0');
           }
           else
           {
             // unsucceed
           }
        }
        else
        {
            $introCmd = 'exDel';    // got to the delete command
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

        if ( claro_sql_query($sql) != false )
        {
            linker_delete_resource('CLINTRO_');
        }
    }

    /* Move rank Command */

    if ( $introCmd == 'exMvDown' || $introCmd == 'exMvUp')
    {
        if ( $introCmd == 'exMvDown' )
        {
            $sortDirection = 'ASC';
            $operator = ' > ';
        }
        elseif ( $introCmd == 'exMvUp' )
        {
            $sortDirection = 'DESC';
            $operator = ' < ';
        }

        $currentEntryId = (int) $_REQUEST['introId'];

        $sql = "SELECT rank FROM `" . $TBL_INTRODUCTION ."`
                WHERE id = " . (int) $currentEntryId;

        $currentEntryRank = claro_sql_query_get_single_value($sql);

        if ( $currentEntryRank !== false)
        {
            $sql = "SELECT id, rank
                    FROM `". $TBL_INTRODUCTION ."`
                    WHERE rank ". $operator ." " . $currentEntryRank . "
                    ORDER BY rank ". $sortDirection . " LIMIT 1";

            $nextEntrySettingList = claro_sql_query_get_single_row($sql);

            if ( is_array($nextEntrySettingList) )
            {
                $nextEntryRank = $nextEntrySettingList['rank'];
                $nextEntryId    = $nextEntrySettingList['id'];
                $sql = "UPDATE `" . $TBL_INTRODUCTION . "`
                    SET rank = '" . (int) $nextEntryRank . "'
                    WHERE id =  '" . (int) $currentEntryId . "'";

                claro_sql_query($sql);

                $sql = "UPDATE `" . $TBL_INTRODUCTION . "`
                    SET rank = '" . (int) $currentEntryRank . "'
                    WHERE id =  '" . (int) $nextEntryId . "'";

                claro_sql_query($sql);
            }
        }
    }

    if ( $introCmd == 'mkVisible' || $introCmd == 'mkInvisible' )
    {
        $currentEntryId = (int) $_REQUEST['introId'];

        $visibility = ($introCmd == 'mkVisible') ? 'SHOW' : 'HIDE';

        $sql = "UPDATE `" . $TBL_INTRODUCTION . "`
                SET `visibility` = '".$visibility."'
                WHERE id =  '" . (int) $currentEntryId . "'";

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
    .    '<input type="hidden" name="claroFormId" value="'.uniqid(time()).'">'
    .    '<input type="hidden" name="introCmd" value="' . $introEditorCmdValue . '">'
    .    ($introId ? '<input type="hidden" name="introId" value="'.$introId.'">' : '')
    .    claro_html_textarea_editor('intro_content', trim($introContent))
    .	'<br />'."\n"
    ;

    //---------------------
    // linker

    if( $jpspanEnabled )
    {
        linker_set_local_crl( isset ($_REQUEST['introId'] ), 'CLINTRO_' );
        linker_set_display();
        echo '<input type="submit" class="claroButton" name="submitEvent" onClick="linker_confirm();" value="' . get_lang('Ok') . '">&nbsp;'."\n";
    }
    else // popup mode
    {
        if(isset($_REQUEST['introId'])) linker_set_display($_REQUEST['introId'], 'CLINTRO_', 'introId');
        else                       linker_set_display(false, 'CLINTRO_');

        echo '<input type="submit" class="claroButton" name="submitEvent" value="' . get_lang('Ok') . '">&nbsp;'."\n";
    }

    echo claro_html_button($_SERVER['PHP_SELF'], get_lang('Cancel'))
    .    '<br />' . "\n"
    .    '</form>' . "\n\n"
    ;

}

if ($intro_dispDefault)
{
    $sql = "SELECT `id`, `rank`, `content`, `visibility`
            FROM `" . $TBL_INTRODUCTION . "`
            WHERE `tool_id` <= 0
            ORDER BY rank ASC";

    $textIntroList = claro_sql_query_fetch_all($sql);

    $introListCount = count($textIntroList);

    if ( $introListCount == 0 && $intro_editAllowed )
    {
        echo '<div class="HelpText">' . "\n"
        .    $helpAddIntroText        . "\n"
        .    '</div>'                 . "\n";
    }
    else
    {
        foreach($textIntroList as $thisIntroKey => $thisTextIntro)
        {
            $introId       = $thisTextIntro['id'];
            $introVisibility = $thisTextIntro['visibility'];

            if ( $introVisibility == 'SHOW' || $intro_editAllowed )
            {
                $cssClass = ($introVisibility == 'HIDE') ? ' invisible' :'';
                $cssClass = ($intro_editAllowed) ? ' editable' :'';
                $intro_content = claro_parse_user_text($thisTextIntro['content']);

                $section = '';

                if( trim(strip_tags($intro_content,'<img><embed><object>')) != '' ) // no need to display a div for an empty string
                {
                    $section .= $intro_content . "\n";
                }
                elseif ($intro_editAllowed)
                {
                    $section .= '<div style="text-align:center;background-color:silver;margin:3px;">' . get_lang('This zone is empty') . '</div>' . "\n";
                }

                $section .= linker_display_resource('CLINTRO_');


                if ($intro_dispCommand)
                {
                    $section .= '<div class="toolbar">' . "\n";

                    $section .= '<a class="claroCmd" href="' . $_SERVER['PHP_SELF']
                    .       '?introCmd=rqEd&introId='.$introId.'">'
                    .    '<img src="' . get_path('imgRepositoryWeb') . 'edit.gif" alt="' . get_lang('Ok') . '" border="0">'
                    .    '</a>' . "\n"
                    .    '<a class="claroCmd" href="' . $_SERVER['PHP_SELF']
                    .      '?introCmd=exDel&introId=' . $introId . '" '
                    .      'onclick="javascript:if(!confirm(\''
                    .      clean_str_for_javascript( get_lang('Confirm Operation') . ' : ' . get_lang('Delete') ).'\')) '
                    .      'return false;">'
                    .    '<img src="' . get_path('imgRepositoryWeb') . 'delete.gif" alt="' . get_lang('Delete') . '" border="0">'
                    .    '</a>' . "\n"
                    ;

                    if ($thisIntroKey > 0 )
                    {
                        $section .= '<a href="'.$_SERVER['PHP_SELF'].'?introCmd=exMvUp&introId='.$introId.'">'
                        .    '<img src="' . get_path('imgRepositoryWeb') . 'up.gif" alt="'.get_lang('Move up').'">'
                        .    '</a> ';
                    }

                    if ($thisIntroKey + 1 < $introListCount )
                    {
                        $section .= ' <a href="'.$_SERVER['PHP_SELF'].'?introCmd=exMvDown&introId='.$introId.'">'
                        .    '<img src="' . get_path('imgRepositoryWeb') . 'down.gif" alt="'.get_lang('Move down').'">'
                        .    '</a>';
                    }

                    //  Visibility

                    if ( $introVisibility =='SHOW' )
                    {
                        $section .= '<a href="' . $_SERVER['PHP_SELF']
                            . '?introCmd=mkInvisible&amp;introId='
                            . $introId . '" title="'
                            . get_lang( 'Click to make invisible' ).'">'
                            ;
                        $section .= '<img src="' . get_path('imgRepositoryWeb')
                            . 'visible.gif" alt="'
                            . get_lang('Visible').'" />'
                            ;
                        $section .= '</a>' . "\n";
                    }
                    else
                    {
                        $section .= '<a href="' . $_SERVER['PHP_SELF']
                            . '?introCmd=mkVisible&amp;introId='
                            . $introId . '" title="'
                            . get_lang( 'Click to make visible' ).'">'
                            ;
                        $section .= '<img src="' . get_path('imgRepositoryWeb')
                            . 'invisible.gif" alt="'
                            . get_lang('Invisible') . '" />'
                            ;
                        $section .= '</a>' . "\n";

                    }

                    $section .= '</div>' . "\n\n";
                }

                if ( !empty( $section ) || $intro_editAllowed )
                {
                    $section = '<div class="claroIntroSection' . $cssClass . '">'
                        . "\n" . $section
                        ;

                    $section .= '</div>' . "\n\n";
                }

                echo $section;
            }
        } // end foreach textIntroList

    } // end if count textIntroList > 0

    if ($intro_dispCommand)
    {
        echo '<p>' . "\n"
        .    '<a class="claroCmd" href="' . $_SERVER['PHP_SELF'] . '?introCmd=rqAdd">'
        .    '<img src="' . get_path('imgRepositoryWeb') . '/textzone.gif" alt="" border="0">'
        .    get_lang('Add Text')
        .    '</a>' . "\n"
        .    '</p>' . "\n\n"
        ;
    }
} // end if intro_dispDefault


?>