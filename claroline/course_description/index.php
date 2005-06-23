<?php // $Id$
/**
 * CLAROLINE
 *
 * This  page show  to the user, the course description
 *
 * If ist's the admin, he can access to the editing
 *
 * @version 1.7 $Revision$
 *
 * @copyright 2001-2005 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE 
 *
 * @see http://www.claroline.net/wiki/CLDSC/
 *
 * @author Claro Team <cvs@claroline.net>
 *
 * @package CLDSC
 * 
 */

$tlabelReq = 'CLDSC___';

require '../inc/claro_init_global.inc.php';

claro_unquote_gpc();

if ( ! $_cid)             claro_disp_select_course();
if ( ! $is_courseAllowed) claro_disp_auth_form();

claro_set_display_mode_available(TRUE);
$nameTools = $langCourseProgram;

$noQUERY_STRING = TRUE; // to remove parameters in the last bredcrumb link

/*
 * DB tables definition
 */

$tbl_cdb_names           = claro_sql_get_course_tbl();
$tbl_course_description  = $tbl_cdb_names['course_description'];

include 'tiplistinit.inc.php';

//stats
include $includePath . '/lib/events.lib.inc.php';

$dialogBox = '';


/******************************************************************************
                          UPDATE / ADD DESCRIPTION ITEM
 ******************************************************************************/

$is_allowedToEdit = claro_is_allowed_to_edit();

if ( $is_allowedToEdit )
{

    /*> > > > > > > > > > > > COMMANDS < < < < < < < < < < < < */

    $cmd         = isset($_REQUEST['cmd'])         ? $_REQUEST['cmd']               : NULL;
    $descTitle   = isset($_REQUEST['descTitle'])   ? trim($_REQUEST['descTitle'])   : '';
    $descContent = isset($_REQUEST['descContent']) ? trim($_REQUEST['descContent']) : '';
    $descId      = isset($_REQUEST['id'])          ? (int) $_REQUEST['id']          : 0 ;

    if ( $cmd == 'exEdit' )
    {
        if ( !empty($descId) )
        {
            // Update description
            if ( course_description_set_item($descId,$descTitle,$descContent) != FALSE )
            {
                $eventNotifier->notifyCourseEvent("course_description_modified", $_cid, $_tid, $descId, $_gid, "0");
                    $dialogBox .= '<p>' . $langDescUpdated . '</p>';
            }
            else
            {
                $dialogBox .= '<p>' . $langDescUnableToUpdate . '</p>';
            }
        }
        else
        {
            // Add new description
                
                $descId = course_description_add_item($descTitle,$descContent);
            if ($descId != FALSE )
            {
                $eventNotifier->notifyCourseEvent("course_description_added",$_cid, $_tid, $descId, $_gid, "0");
                    $dialogBox .= '<p>' . $langDescAdded . '</p>';
            }
            else
            {
                $dialogBox .= '<p>' . $langUnableDescToAdd . '</p>';
            }
        }    
    }
    
    /******************************************************************************
                            REQUEST DESCRIPTION ITEM EDITION
     ******************************************************************************/
    
    if ( $cmd == 'rqEdit' )
    {
        if ( !empty($descId) )
        {
            $descItem = course_description_get_item($descId);
            $descPresetKey = array_search($descItem['title'] , $titreBloc);
        }
        else
        {
            $descItem['id'     ] = NULL;
            $descItem['title'  ] = '';
            $descItem['content'] = '';
    
            if ( isset($_REQUEST['numBloc']) && $_REQUEST['numBloc'] >= 0 )
            {
                $descPresetKey = $_REQUEST['numBloc'];
            }
        }
    
        if ( !empty($descPresetKey) )
        {
             $descPresetTitle    = $titreBloc[$descPresetKey];
             $descPresetQuestion = $questionPlan[$descPresetKey];
             $descPresetTip      = $info2Say[$descPresetKey];
        }
        else
        {
             $descPresetTitle    = NULL;
             $descPresetQuestion = NULL;
             $descPresetTip      = NULL;
        }
    
        $displayForm = TRUE;
    }
    
    /******************************************************************************
                                DELETE DESCRIPTION ITEM
     ******************************************************************************/
    
    
    if ( $cmd == 'exDelete' && !empty($descId) )
    {
        if ( course_description_delete_item($descId) ) 
        {
            $dialogBox .= '<p>' . $langDescDeleted . '</p>';
        }
        else
        {
            $dialogBox .= '<p>' . $langDescUnableToDelete . '</p>';
        }
    }



    /******************************************************************************
                           EDIT  VISIBILITY DESCRIPTION ITEM
     ******************************************************************************/
    
    
    if ( ($cmd == 'mkShow'|| $cmd == 'mkHide') && !empty($descId) )
    {
        if ( course_description_visibility_item($descId , $cmd) ) 
        {
            $dialogBox .= '<p>' . $langViMod. '</p>';
        }
            
            //notify that an item is now visible
            
            if ($cmd == 'mkShow')
            {
                $eventNotifier->notifyCourseEvent("course_description_visible",$_cid, $_tid, $descId, $_gid, "0");
            }
    }
}

/*---------------------------------------------------------------------------*/

event_access_tool($_tid, $_courseTool['label']);

/******************************************************************************
                           LOAD THE DESCRIPTION LIST
 ******************************************************************************/

$descList = course_description_get_item_list();

/*> > > > > > > > > > > > OUTPUT < < < < < < < < < < < < */

require $includePath.'/claro_init_header.inc.php';

echo claro_disp_tool_title(array('mainTitle' => $nameTools));

if ( isset($dialogBox) && ! empty($dialogBox) )
{
    claro_disp_message_box($dialogBox);
    echo '<br />'."\n";
}

$is_allowedToEdit = claro_is_allowed_to_edit();

if ( $is_allowedToEdit )
{

    /**************************************************************************
                               EDIT FORM DISPLAY
     **************************************************************************/

    if ( isset($displayForm) && $displayForm )
    {
        echo '<table border="0">'."\n"
            .'<tr>'              ."\n"
            .'<td>'              ."\n"

            .'<form  method="post" action="'.$_SERVER['PHP_SELF'].'">'."\n"

            .'<input type="hidden" name="cmd" value="exEdit">'

            .($descItem['id'] ? '<input type="hidden" name="id" value="'.$descItem['id'].'">' : '')

            .'<p><label for="descTitle"><b>'.$langTitle.' : </b></label><br /></p>'."\n"

            .($descPresetTitle ? htmlspecialchars($descPresetTitle)
                                .'<input type="hidden" name="descTitle" value="'. htmlspecialchars($descPresetTitle) .'">'
                                :
                                '<input type="text" name="descTitle" id="descTitle" size="50" value="'. htmlspecialchars($descItem['title']) .'">'."\n")

            .'<p><label for="descContent"><b>'.$langContent.' : </b></label><br /></td></tr><tr><td>'."\n";

            claro_disp_html_area('descContent', $descItem['content'], 20, 80, $optAttrib=' wrap="virtual"')."\n";

            echo '<input type="submit" name="save" value="' . $langOk . '">' . "\n"
                 .claro_disp_button($_SERVER['PHP_SELF'], $langCancel)
                 .'</form>'."\n"

                 .'</td>'  ."\n"

                 .'<td valign="top">'."\n";
            
            if ( $descPresetQuestion )
            {
                echo '<h4>' . $langQuestionPlan . '</h4>' . "\n"
                    .'<p>' . $descPresetQuestion . '</p>' . "\n";
            }
            
            if ($descPresetTip)
            {
                echo '<h4>' . $langInfo2Say . '</h4>' . "\n"
                   . '<p>' . $descPresetTip . '</p>' . "\n";
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

        echo "\n\n"
        .    '<form method="get" action="' . $_SERVER['PHP_SELF'] . '?edIdBloc=add">' . "\n"
        .    '<input type="hidden" name="cmd" value="rqEdit">' . "\n"
        .    '<select name="numBloc">' . "\n"
        ;

        foreach ( $titreBloc as $key => $thisBlocTitle )
        {
            foreach ( $descList as $thisDesc )
            {
                $alreadyUsed =  ( $thisDesc['title'] == $thisBlocTitle ) ;
            }
            
            if ( !isset($alreadyUsed) || !$alreadyUsed )
            {
                echo '<option value="' . $key . '">' . $thisBlocTitle . '</option>' . "\n";
            }
        }
            
        echo '<option value="">' . $langNewBloc . '</option>' . "\n"
            .'</select>' . "\n"
            .'<input type="submit" name="add" value="' . $langAdd . '">' . "\n"
            .'</form>' . "\n\n";
    }
} // end if is_allowedToEdit

/******************************************************************************
                            DESCRIPTION LIST DISPLAY
 ******************************************************************************/
$hasDisplayedItems = false;

if ( count($descList) )
{
    foreach ( $descList as $thisDesc )
    {
        if (($thisDesc['visibility']=='HIDE' && $is_allowedToEdit) || $thisDesc['visibility']=='SHOW')
        {  
            if ($thisDesc['visibility']=='HIDE') $style = ' class="invisible"';  else $style='';
            echo "\n".'<div'.$style.'><h4>' . htmlspecialchars($thisDesc['title']) . '</h4>'."\n"
                  .'<blockquote>'."\n"
                  . claro_parse_user_text($thisDesc['content'])."\n"
                  .'<br></div>'."\n"
                  .'</blockquote>'."\n";

            $hasDisplayedItems = true;
        }
    
        if ( $is_allowedToEdit )
        {
            echo '<a href="' . $_SERVER['PHP_SELF'] . '?cmd=rqEdit&amp;id=' . $thisDesc['id'] . '">'
            .    '<img src="' . $imgRepositoryWeb.'edit.gif" alt="' . $langModify . '">'
            .    '</a>'."\n"
            .    '<a href="' . $_SERVER['PHP_SELF'] . '?cmd=exDelete&amp;id=' . $thisDesc['id'] . '"'
            .    ' onClick="if(!confirm(\'' . clean_str_for_javascript($langAreYouSureToDelete) 
            .    ' ' . $thisDesc['title'] . ' ?\')){ return false}">'
            .    '<img src="' . $imgRepositoryWeb . 'delete.gif" alt="'.$langDelete.'">'
            .    '</a>' . "\n\n"
            ;
           if ($thisDesc['visibility'] == 'SHOW')
           {
               echo '<a href="'.$_SERVER['PHP_SELF'].'?cmd=mkHide&amp;id='.$thisDesc['id'].'">'
                .'<img src="'.$imgRepositoryWeb.'visible.gif" alt="'.$langInvisible.'">'
                .'</a>'."\n";
           }
           else 
           {
               echo '<a href="'.$_SERVER['PHP_SELF'].'?cmd=mkShow&amp;id='.$thisDesc['id'].'">'
                .'<img src="'.$imgRepositoryWeb.'invisible.gif" alt="'.$langVisible.'">'
                .'</a>'."\n";           
           }
        }
        
    }
}

if( !$hasDisplayedItems )
{
    echo "\n".'<p>'.$langThisCourseDescriptionIsEmpty.'</p>'."\n";
}

include $includePath.'/claro_init_footer.inc.php';


/**
 * get all the items
 * 
 * @param $course_id string  glued dbName of the course to affect default: current course
 *
 * @return array of arrays with data of the item
 * 
 * @author Christophe Gesché <moosh@claroline.net>
 *
 */

function course_description_get_item_list($course_id=Null)
{
    $tbl_cdb_names           = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
    $tbl_course_description  = $tbl_cdb_names['course_description'];
    
    $sql = "SELECT `id`, `title`, `content` , `visibility`
            FROM `".$tbl_course_description."` 
            ORDER BY `id`";
    return  claro_sql_query_fetch_all($sql);
}



/**
 * get the item of the given id.
 * 
 * @param $descId   integer id of the item to get
 * @param $course_id string  glued dbName of the course to affect default: current course
 *
 * @return array with data of the item
 * 
 * @author Christophe Gesché <moosh@claroline.net>
 *
*/

function course_description_get_item($descId, $course_id=Null)
{
    $tbl_cdb_names           = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
    $tbl_course_description  = $tbl_cdb_names['course_description'];
    
    $sql = 'SELECT `id`, `title`, `content`, `visibility`
            FROM `'.$tbl_course_description.'`
            WHERE id = ' . (int) $descId;

    list($descItem) = claro_sql_query_fetch_all($sql);
    return $descItem;
}

/**
 * remove the item of the given id.
 * 
 * @param $descId   integer id of the item to delete
 * @param $course_id string  glued dbName of the course to affect default: current course
 *
 * @return result of query
 * 
 * @author Christophe Gesché <moosh@claroline.net>
 *
 */

function course_description_delete_item($descId, $course_id=Null)
{
    $tbl_cdb_names           = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
    $tbl_course_description  = $tbl_cdb_names['course_description'];
    
    $sql = 'DELETE FROM `'.$tbl_course_description.'`
            WHERE id = ' . (int) $descId;
    
    return  claro_sql_query($sql);
}


/**
 * update values of the item of the given id.
 * 
 * @param $descId       integer id of the item to update
 * @param $descTitle    string Title of the item
 * @param $descContent  string Content of the item
 * @param $course_id    string  glued dbName of the course to affect default: current course
 *
 * @return result of query
 * 
 * @author Christophe Gesché <moosh@claroline.net>
 *
 */

function course_description_set_item($descId , $descTitle , $descContent, $course_id=Null)
{        
    $tbl_cdb_names           = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
    $tbl_course_description  = $tbl_cdb_names['course_description'];

    $sql = "UPDATE `".$tbl_course_description."`
               SET   `title`   = '" . addslashes($descTitle) . "',
                     `content` = '" . addslashes($descContent) . "',
                     `upDate`  = NOW()
               WHERE `id` = '". $descId ."' ";

    return claro_sql_query($sql);
}


/**
 * insert values in a new item 
 * 
 * @param $descTitle    string Title of the item
 * @param $descContent  string Content of the item
 * @param $course_id    string  glued dbName of the course to affect default: current course
 *
 * @return integer id of the new item
 * 
 * @author Christophe Gesché <moosh@claroline.net>
 *
 */
function course_description_add_item($descTitle,$descContent, $course_id=Null)
{
    $tbl_cdb_names           = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
    $tbl_course_description  = $tbl_cdb_names['course_description'];
    $sql = "SELECT MAX(id)
                FROM `".$tbl_course_description."` ";

    $maxId = claro_sql_query_get_single_value($sql);

    $sql ="INSERT INTO `".$tbl_course_description."`
               SET   `title`   = '". addslashes($descTitle  ) . "',
                     `content` = '". addslashes($descContent) . "',
                     `upDate`  = NOW(),
                     `id` = ". (int) ($maxId + 1);
                     
    if (claro_sql_query($sql)) 
    {
        return ($maxId + 1); 
    }
    else 
    {
        return FALSE; 
    }
}

/**
 * insert values in a new item 
 * 
 * @param $descTitle    string Title of the item
 * @param $cmd          string with command to hide or show item
 * @param $dbnameGlu    string  glued dbName of the course to affect default: current course
 *
 * @return integer id of the new item
 * 
 * @author Christophe Gesché <moosh@claroline.net>
 *
 */
function course_description_visibility_item($descId, $cmd, $dbnameGlu=Null)
{
    $tbl_cdb_names           = claro_sql_get_course_tbl($dbnameGlu);
    $tbl_course_description  = $tbl_cdb_names['course_description'];
    
    if ($cmd == "mkShow")  $visibility = 'SHOW'; else $visibility = 'HIDE';
    if ($cmd == "mkHide")  $visibility = 'HIDE'; else $visibility = 'SHOW';
    
    $sql = "UPDATE `".$tbl_course_description."`
               SET   `visibility`   = '" . $visibility . "'
               WHERE `id` = '". $descId ."' ";

    return claro_sql_query($sql);
}

?>