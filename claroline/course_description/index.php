<?php // $Id$
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.6
      +----------------------------------------------------------------------+
      | Copyright (c) 2001-2005 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
/**
 * This  page show  to the user, the course description
 *
 * If ist's the admin, he can access to the editing
 *
 */

$tlabelReq = 'CLDSC___';

require '../inc/claro_init_global.inc.php';

if ( ! $_cid)             claro_disp_select_course();
if ( ! $is_courseAllowed) claro_disp_auth_form();

$nameTools = $langCourseProgram;

/*
 * DB tables definition
 */

$tbl_cdb_names           = claro_sql_get_course_tbl();
$tbl_course_description  = $tbl_cdb_names['course_description'];

include 'tiplistinit.inc.php';

//stats
include $includePath.'/lib/events.lib.inc.php';




         /*> > > > > > > > > > > > COMMANDS < < < < < < < < < < < < */


if ( isset($_REQUEST['cmd']) ) $cmd = $_REQUEST['cmd'];
else                           $cmd = null;




/******************************************************************************
                          EDIT / ADD DESCRIPTION ITEM
 ******************************************************************************/


if ($cmd == 'exEdit')
{
    if ($_REQUEST['id'])
    {
        $sql ="UPDATE `".$tbl_course_description."` 
               SET   `title`   = '".trim(claro_addslashes($_REQUEST['descTitle'  ]))."',
                     `content` = '".trim(claro_addslashes($_REQUEST['descContent']))."',
                     `upDate`  = NOW()
               WHERE `id` = '". (int) $_REQUEST['id'] ."'";        

        if ( claro_sql_query($sql) != false)
        {
            $msg .= '<p>'.$langDescUpdated.'</p>';
        }
        else
        {
            $msg .= '<p>'.$langDescUnableToUpdate.'</p>';
        }
    }
    else
    {
        $sql = "SELECT MAX(id) 
                FROM `".$tbl_course_description."` ";

        $maxId = claro_sql_query_get_single_value($sql);

        $sql ="INSERT INTO `".$tbl_course_description."` 
               SET   `title`   = '".trim(claro_addslashes($_REQUEST['descTitle'  ]))."',
                     `content` = '".trim(claro_addslashes($_REQUEST['descContent']))."',
                     `upDate`  = NOW(),
                     `id` = ". (int) ($maxId + 1);

        if ( claro_sql_query($sql) !== false)
        {
            $msg .= '<p>'.$langDescAdded.'</p>';
        }
        else
        {
            $msg .= '<p>'.$langUnableDescToAdd.'</p>';
        }
    }    
}





/******************************************************************************
                        REQUEST DESCRIPTION ITEM EDITION
 ******************************************************************************/


if($cmd == 'rqEdit')
{
    if ( isset($_REQUEST['id'] ) )
    {
        $sql = 'SELECT id, title, content
                FROM `'.$tbl_course_description.'`
                WHERE id = '.(int)$_REQUEST['id'];

        list($descItem) = claro_sql_query_fetch_all($sql);

        $descPresetKey = array_search($descItem['title'] , $titreBloc);

    }
    else
    {
    	$descItem['id'     ] = null;
        $descItem['title'  ] = '';
        $descItem['content'] = '';

        if ( isset($_REQUEST['numBloc']) && $_REQUEST['numBloc'] > 0)
        {
            $descPresetKey = $_REQUEST['numBloc'];
        }
    }



    if ( $descPresetKey )
    {
         $descPresetTitle    = $titreBloc    [$descPresetKey];
         $descPresetQuestion = $questionPlan [$descPresetKey];
         $descPresetTip      = $info2Say     [$descPresetKey];
    }
    else
    {
         $descPresetTitle    = null;
         $descPresetQuestion = null;
         $descPresetTip      = null;
    }

    $displayForm = true;
}




/******************************************************************************
                            DELETE DESCRIPTION ITEM
 ******************************************************************************/


if ($cmd == 'exDelete')
{
    $sql ="DELETE FROM `".$tbl_course_description."` 
           WHERE id = '". (int) $_REQUEST['id']."'";

    if ( claro_sql_query($sql) !== false) 
    {
        $msg .= '<p>'.$langDescDeleted.'</p>';	
    }
    else
    {
        $msg .= '<p>'.$langDescUnableToDelete.'</p>';
    }
}

/*---------------------------------------------------------------------------*/


event_access_tool($_tid, $_courseTool['label']);

/******************************************************************************
                           LOAD THE DESCRIPTION LIST
 ******************************************************************************/

$sql = "SELECT `id`, `title`, `content` 
        FROM `".$tbl_course_description."` 
        ORDER BY `id`";

$descList = claro_sql_query_fetch_all($sql);

/*---------------------------------------------------------------------------*/




          /*> > > > > > > > > > > > OUTPUT < < < < < < < < < < < < */


claro_set_display_mode_available(true);

require $includePath.'/claro_init_header.inc.php';

claro_disp_tool_title( array('mainTitle' => $nameTools) );

if ( isset($msg) && ! empty($msg) )
{
    claro_disp_message_box($msg);
    echo '<br />'."\n";
}

$is_allowedToEdit = claro_is_allowed_to_edit();

if ($is_allowedToEdit)
{
    /**************************************************************************
                               EDIT FORM DISPLAY
     **************************************************************************/


    if ($displayForm)
    {
        echo '<table border="0">'."\n"
            .'<tr>'              ."\n"
            .'<td>'              ."\n"

            .'<form  method="post" action="'.$_SERVER['PHP_SELF'].'">'."\n"

            .'<input type="hidden" name="cmd" value="exEdit">'

            .($descItem['id'] ? '<input type="hidden" name="id" value="'.$descItem['id'].'">' : '')

            .'<p><label for="descTitle"><b>'.$langTitle.' : </b></label><br /></p>'."\n"

            .($descPresetTitle ? $descPresetTitle
                                .'<input type="hidden" name="descTitle" value="'.$descPresetTitle.'">'
                                :
                                '<input type="text" name="descTitle" id="descTitle" size="50" value="'.$descItem['title'].'">'."\n")

            .'<p><label for="descContent"><b>'.$langContent.' : </b></label><br /></td></tr><tr><td>'."\n";

        claro_disp_html_area('descContent', $descItem['content'], 20, 80, $optAttrib=' wrap="virtual"')."\n";

        echo '<input type="submit" name="save" value="'.$langValid.'">'         ."\n";

        claro_disp_button($_SERVER['PHP_SELF'], $langCancel);

        echo '</form>'."\n"
            
            .'</td>'  ."\n"

            .'<td valign="top">'."\n";
            
            if ($descPresetQuestion)
            {
                echo '<h4>' . $langQuestionPlan . '</h4>'."\n"
                    .$descPresetQuestion;
            }
            
            if ($descPresetTip)
            {
                echo '<h4>' . $langInfo2Say . '</h4>'."\n"
                   .$descPresetTip;
            }
            

       echo '</td>'."\n"

            .'</tr>'   ."\n"
            .'</table>'."\n";

    } // end if display form

    else 
    {
    
    /**************************************************************************
                                ADD FORM DISPLAY
     **************************************************************************/

        echo '<form method="get" action="'.$_SERVER['PHP_SELF'].'?edIdBloc=add">'
            .'<input type="hidden" name="cmd" value="rqEdit">'
            .'<select name="numBloc">';

        foreach( $titreBloc as $key => $thisBlocTitle )
        {
            foreach( $descList as $thisDesc )
            {
              if ($thisDesc['title'] == $thisBlocTitle) $alreadyUsed = true;
              else                                      $alreadyUsed = false;
            }
            
            if ( ! $alreadyUsed)
            {
                echo '<option value="'.$key.'">'.$thisBlocTitle.'</option>';
            }
        }
            
        echo '<option value="">'.$langNewBloc.'</option>'
            .'</select>'
            .'<input type="submit" name="add" value="'.$langAdd.'">'
            .'</form>';
    }
} // end if is_allowedToEdit




/******************************************************************************
                            DESCRIPTION LIST DISPLAY
 ******************************************************************************/


if ( count($descList) )
{
    foreach($descList as $thisDesc)
    {
        echo '<h4>'.$thisDesc['title'].'</h4>'."\n"
            .'<blockquote>'."\n"
            . claro_parse_user_text($thisDesc['content'])."\n"
            .'<br>'."\n"
            .'</blockquote>'."\n";

        if ($is_allowedToEdit)
        {
            echo '<a href="'.$_SERVER['PHP_SELF'].'?cmd=rqEdit&amp;id='.$thisDesc['id'].'">'
                .'<img src="'.$imgRepositoryWeb.'edit.gif" alt="'.$langModify.'">'
                .'</a>'."\n"
                .'<a href="'.$_SERVER['PHP_SELF'].'?cmd=exDelete&amp;id='.$thisDesc['id'].'"'
                .' onClick="if(!confirm(\''.clean_str_for_javascript($langAreYouSureToDelete).' '.$thisDesc['title'].' ?\')){ return false}">'
                .'<img src="'.$imgRepositoryWeb.'delete.gif" alt="'.$langDelete.'">'
                .'</a>'."\n";
        }
    }
}
else
{
	echo '<p>'.$langThisCourseDescriptionIsEmpty.'</p>'."\n";
}


include $includePath.'/claro_init_footer.inc.php';
?>