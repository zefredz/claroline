<?php // $Id$

/**
 * CLAROLINE 
 *
 * @version 1.8 $Revision$
 *
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @author Piraux Sébastien <pir@cerdecam.be>
 * @author Lederer Guillaume <led@cerdecam.be>
 *
 * @package CLLNP
 */

/*======================================
       CLAROLINE MAIN
  ======================================*/

$tlabelReq = 'CLLNP___';

require '../inc/claro_init_global.inc.php';

if ( ! $_cid || ! $is_courseAllowed ) claro_disp_auth_form(true);

// $_SESSION
// path_id
if ( isset($_GET['path_id']) && $_GET['path_id'] != '' )
{
    $_SESSION['path_id'] = $_GET['path_id'];
}
// module_id
if ( isset($_GET['module_id']) && $_GET['module_id'] != '')
{
    $_SESSION['module_id'] = $_GET['module_id'];
}

// use viewMode
claro_set_display_mode_available(true);

$is_AllowedToEdit = claro_is_allowed_to_edit();    // as teacher
//-- interbredcrump
$interbredcrump[]= array ("url"=>"../learnPath/learningPathList.php", "name"=> get_lang('LearningPathList'));
if ( $is_AllowedToEdit )
{
    $interbredcrump[]= array ("url"=>"../learnPath/learningPathAdmin.php", "name"=> get_lang('Learning path'));
}
else
{
    $interbredcrump[]= array ("url"=>"../learnPath/learningPath.php", "name"=> get_lang('Learning path'));
}

$nameTools = get_lang('Module');

// tables names
$TABLELEARNPATH         = $_course['dbNameGlu']."lp_learnPath";
$TABLEMODULE            = $_course['dbNameGlu']."lp_module";
$TABLELEARNPATHMODULE   = $_course['dbNameGlu']."lp_rel_learnPath_module";
$TABLEASSET             = $_course['dbNameGlu']."lp_asset";
$TABLEUSERMODULEPROGRESS= $_course['dbNameGlu']."lp_user_module_progress";

$TABLEQUIZTEST               = $_course['dbNameGlu']."quiz_test";

$dbTable = $TABLEASSET; // for old functions of document tool

//lib of this tool
include($includePath."/lib/learnPath.lib.inc.php");

include($includePath."/lib/fileDisplay.lib.php");
include($includePath."/lib/fileManage.lib.php");
include($includePath."/lib/fileUpload.lib.php");

// clean exercise session vars
if(session_is_registered('objExercise'))        { session_unregister('objExercise');        }
if(session_is_registered('objQuestion'))        { session_unregister('objQuestion');        }
if(session_is_registered('objAnswer'))          { session_unregister('objAnswer');          }
if(session_is_registered('questionList'))       { session_unregister('questionList');       }
if(session_is_registered('exerciseResult'))     { session_unregister('exerciseResult');     }

// main page
// FIRST WE SEE IF USER MUST SKIP THE PRESENTATION PAGE OR NOT
// triggers are : if there is no introdution text or no user module progression statistics yet and user is not admin,
// then there is nothing to show and we must enter in the module without displaying this page.

/*
 *  GET INFOS ABOUT MODULE and LEARNPATH_MODULE
 */

// check in the DB if there is a comment set for this module in general

$sql = "SELECT `comment`, `startAsset_id`, `contentType`
        FROM `".$TABLEMODULE."`
        WHERE `module_id` = ". (int)$_SESSION['module_id'];

$module = claro_sql_query_get_single_row($sql);

if( empty($module['comment']) || $module['comment'] == get_lang('DefaultModuleComment') )
{
      $noModuleComment = true;
}
else
{
   $noModuleComment = false;
}


if( $module['startAsset_id'] == 0 )
{
    $noStartAsset = true;
}
else
{
    $noStartAsset = false;
}


// check if there is a specific comment for this module in this path
$sql = "SELECT `specificComment`
        FROM `".$TABLELEARNPATHMODULE."`
        WHERE `module_id` = ". (int)$_SESSION['module_id'];

$learnpath_module = claro_sql_query_get_single_row($sql);

if( empty($learnpath_module['specificComment']) || $learnpath_module['specificComment'] == get_lang('DefaultModuleAddedComment') )
{
    $noModuleSpecificComment = true;
}
else
{
    $noModuleSpecificComment = false;
}

// check in DB if user has already browsed this module

$sql = "SELECT `contentType`,
                `total_time`,
                `session_time`,
                `scoreMax`,
                `raw`,
                `lesson_status`
        FROM `".$TABLEUSERMODULEPROGRESS."` AS UMP, 
             `".$TABLELEARNPATHMODULE."` AS LPM, 
             `".$TABLEMODULE."` AS M
        WHERE UMP.`user_id` = '$_uid'
          AND UMP.`learnPath_module_id` = LPM.`learnPath_module_id`
          AND LPM.`learnPath_id` = ".(int)$_SESSION['path_id']."
          AND LPM.`module_id` = ". (int)$_SESSION['module_id']."
          AND LPM.`module_id` = M.`module_id`
             ";
$resultBrowsed = claro_sql_query_get_single_row($sql);

// redirect user to the path browser if needed
if( !$is_AllowedToEdit
    && ( !is_array($resultBrowsed) || !$resultBrowsed || count($resultBrowsed) <= 0 )
    && $noModuleComment
    && $noModuleSpecificComment
    && !$noStartAsset
    )
{
    header("Location:./navigation/viewer.php");
    exit();
}

//header
include($includePath."/claro_init_header.inc.php");

//####################################################################################\\
//################################## MODULE NAME BOX #################################\\
//####################################################################################\\

echo '<br />'."\n";

$cmd = ( isset($_REQUEST['cmd']) )? $_REQUEST['cmd'] : '';

if ( $cmd == "updateName" )
{
    nameBox(MODULE_, UPDATE_);
}
else
{
    nameBox(MODULE_, DISPLAY_);
}

if($module['contentType'] != CTLABEL_ )
{

    //####################################################################################\\
    //############################### MODULE COMMENT BOX #################################\\
    //####################################################################################\\
    //#### COMMENT #### courseAdmin cannot modify this if this is a imported module ####\\
    // this the comment of the module in ALL learning paths
    if ( $cmd == "updatecomment" )
    {
        commentBox(MODULE_, UPDATE_);
    }
    elseif ($cmd == "delcomment" )
    {
        commentBox(MODULE_, DELETE_);
    }
    else
    {
        commentBox(MODULE_, DISPLAY_);
    }

    //#### ADDED COMMENT #### courseAdmin can always modify this ####\\
    // this is a comment for THIS module in THIS learning path
    if ( $cmd == "updatespecificComment" )
    {
        commentBox(LEARNINGPATHMODULE_, UPDATE_);
    }
    elseif ($cmd == "delspecificComment" )
    {
        commentBox(LEARNINGPATHMODULE_, DELETE_);
    }
    else
    {
        commentBox(LEARNINGPATHMODULE_, DISPLAY_);
    }
} //  if($module['contentType'] != CTLABEL_ )

//back button
if ($is_AllowedToEdit)
{
    $pathBack = "./learningPathAdmin.php";
}
else
{
    $pathBack = "./learningPath.php";
}

echo '<small><a href="'.$pathBack.'"><< '.get_lang('BackModule').'</a></small><br /><br />'."\n\n";

//####################################################################################\\
//############################ PROGRESS  AND  START LINK #############################\\
//####################################################################################\\

/* Display PROGRESS */

if($module['contentType'] != CTLABEL_) //
{
    if( $resultBrowsed && count($resultBrowsed) > 0 && $module['contentType'] != CTLABEL_)
    {
        $contentType_img = selectImage($resultBrowsed['contentType']);
        $contentType_alt = selectAlt($resultBrowsed['contentType']);

        if ($resultBrowsed['contentType']== CTSCORM_   ) { $contentDescType = get_lang('SCORMTypeDesc');    }
        if ($resultBrowsed['contentType']== CTEXERCISE_ ) { $contentDescType = get_lang('EXERCISETypeDesc'); }
        if ($resultBrowsed['contentType']== CTDOCUMENT_ ) { $contentDescType = get_lang('DOCUMENTTypeDesc'); }

        echo '<b>'.get_lang('ProgInModuleTitle').'</b><br /><br />'."\n\n"
            .'<table align="center" class="claroTable" border="0" cellspacing="2">'."\n"
            .'<thead>'."\n"
            .'<tr class="headerX">'."\n"
            .'<th>'.get_lang('InfoProgNameTitle').'</th>'."\n"
            .'<th>'.get_lang('PersoValue').'</th>'."\n"
            .'</tr>'."\n"
            .'</thead>'."\n\n"
            .'<tbody>'."\n\n";

        //display type of the module
        echo '<tr>'."\n"
            .'<td>'.get_lang('TypeOfModule').'</td>'."\n"
            .'<td><img src="'.$imgRepositoryWeb.$contentType_img.'" alt="'.$contentType_alt.'" border="0" />'.$contentDescType.'</td>'."\n"
            .'</tr>'."\n\n";

        //display total time already spent in the module
        echo '<tr>'."\n"
            .'<td>'.get_lang('TotalTimeSpent').'</td>'."\n"
            .'<td>'.$resultBrowsed['total_time'].'</td>'."\n"
            .'</tr>'."\n\n";

        //display time passed in last session
        echo '<tr>'."\n"
            .'<td>'.get_lang('LastSessionTimeSpent').'</td>'."\n"
            .'<td>'.$resultBrowsed['session_time'].'</td>'."\n"
            .'</tr>'."\n\n";
            
        //display user best score
        if ($resultBrowsed['scoreMax'] > 0)
        {
            $raw = round($resultBrowsed['raw']/$resultBrowsed['scoreMax']*100);
        }
        else
        {
            $raw = 0;
        }

        $raw = max($raw, 0);
        
        if (($resultBrowsed['contentType'] == CTSCORM_ ) && ($resultBrowsed['scoreMax'] <= 0)
            &&  (  ( ($resultBrowsed['lesson_status'] == "COMPLETED") || ($resultBrowsed['lesson_status'] == "PASSED") ) || ($resultBrowsed['raw'] != -1) ) )
        {
            $raw = 100;
        }

        // no sens to display a score in case of a document module
        if (($resultBrowsed['contentType'] != CTDOCUMENT_))
        {
            echo '<tr>'."\n"
                .'<td>'.get_lang('YourBestScore').'</td>'."\n"
                .'<td>'.claro_disp_progress_bar($raw, 1).' '.$raw.'%</td>'."\n"
                .'</tr>'."\n\n";
        }

        //display lesson status

        // document are just browsed or not, but not completed or passed...

        if (($resultBrowsed['contentType']== CTDOCUMENT_))
        {
            if ($resultBrowsed['lesson_status']=="COMPLETED")
            {
                $statusToDisplay = get_lang('Already browsed');
            }
            else
            {
                $statusToDisplay = get_lang('NeverBrowsed');
            }
        }
        else
        {
            $statusToDisplay = $resultBrowsed['lesson_status'];
        }
        echo '<tr>'."\n"
            .'<td>'.get_lang('LessonStatus').'</td>'."\n"
            .'<td>'.$statusToDisplay.'</td>'."\n"
            .'</tr>'."\n\n"
            .'</tbody>'."\n\n"
            .'</table>'."\n\n";

    } //end display stats

    /* START */
    // check if module.startAssed_id is set and if an asset has the corresponding asset_id
    // asset_id exists ?  for the good module  ?
    $sql = "SELECT `asset_id`
              FROM `".$TABLEASSET."`
             WHERE `asset_id` = ". (int)$module['startAsset_id']."
               AND `module_id` = ". (int)$_SESSION['module_id'];

    $asset = claro_sql_query_get_single_row($sql);

    if( $module['startAsset_id'] != "" && $asset['asset_id'] == $module['startAsset_id'] )
    {

        echo '<center>'."\n"
            .'<form action="./navigation/viewer.php" method="post">'."\n"
            .'<input type="submit" value="'.get_lang('StartModule').'" />'."\n"
            .'</form>'."\n"
            .'</center>'."\n\n";
    }
    else
    {
        echo '<p><center>'.get_lang('NoStartAsset').'</center></p>'."\n";
    }
}// end if($module['contentType'] != CTLABEL_) 
// if module is a label, only allow to change its name.
  
//####################################################################################\\
//################################# ADMIN DISPLAY ####################################\\
//####################################################################################\\

if( $is_AllowedToEdit ) // for teacher only
{
    switch ($module['contentType'])
    {
        case CTDOCUMENT_ :
            include("./include/document.inc.php");
            break;
        case CTEXERCISE_ :
            include("./include/exercise.inc.php");
            break;
        case CTSCORM_ :
            include("./include/scorm.inc.php");
            break;
        case CTCLARODOC_ :
            break;
        case CTLABEL_ :
            break;
    }
} // if ($is_AllowedToEdit)

// footer
include($includePath.'/claro_init_footer.inc.php');
?>
