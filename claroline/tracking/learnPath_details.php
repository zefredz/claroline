<?php // $Id$
/**
 * CLAROLINE
 *
 * This script displays the stats of all users of a course
 * for his progression into the chosen learning path
 *
 * @version 1.7 $Revision$ 
 * @copyright 2001-2005 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @package TRACKING
 *
 * @author Claro Team <cvs@claroline.net>
 * @author Sebastien Piraux  <piraux_seb@hotmail.com>
 *
 */
 
require '../inc/claro_init_global.inc.php';

if ( ! $_cid || ! $is_courseAllowed ) claro_disp_auth_form(true);
if ( ! $is_courseAdmin ) claro_die(get_lang('Not allowed')) ; 

// path id can not be empty, return to the list of learning paths
if( empty($_REQUEST['path_id']) )
{
    header("Location: ../learnPath/learningPathList.php");
    exit();
}

$interbredcrump[]= array ("url"=>"../learnPath/learningPathList.php", "name"=> get_lang('LearningPathList'));

$nameTools = get_lang('StatsOfLearnPath');

// regroup table names for maintenance purpose
/*
 * DB tables definition
 */

$tbl_cdb_names               = claro_sql_get_course_tbl();
$tbl_mdb_names               = claro_sql_get_main_tbl();
$tbl_rel_course_user         = $tbl_mdb_names['rel_course_user'  ];
$tbl_user                    = $tbl_mdb_names['user'             ];
$tbl_lp_learnPath            = $tbl_cdb_names['lp_learnPath'           ];
$tbl_lp_rel_learnPath_module = $tbl_cdb_names['lp_rel_learnPath_module'];
$tbl_lp_user_module_progress = $tbl_cdb_names['lp_user_module_progress'];
$tbl_lp_module               = $tbl_cdb_names['lp_module'              ];
$tbl_lp_asset                = $tbl_cdb_names['lp_asset'               ];

$TABLELEARNPATH         = $tbl_lp_learnPath;
$TABLEMODULE            = $tbl_lp_module;
$TABLELEARNPATHMODULE   = $tbl_lp_rel_learnPath_module;
$TABLEASSET             = $tbl_lp_asset;
$TABLEUSERMODULEPROGRESS= $tbl_lp_user_module_progress;

$TABLECOURSUSER            = $tbl_rel_course_user;
$TABLEUSER              = $tbl_user;

include($includePath.'/lib/statsUtils.lib.inc.php');
include($includePath.'/lib/learnPath.lib.inc.php');

include($includePath."/claro_init_header.inc.php");

if ( $is_trackingEnabled )  
{

    if ( !empty($_REQUEST['path_id']) )
    {
        $path_id = (int) $_REQUEST['path_id'];

        // get infos about the learningPath
        $sql = "SELECT `name` 
                FROM `".$TABLELEARNPATH."`
                WHERE `learnPath_id` = ". (int)$path_id;

        $learnPathName = claro_sql_query_get_single_value($sql);
    
        if( $learnPathName )
        {
            // display title
            $titleTab['mainTitle'] = $nameTools;
            $titleTab['subTitle'] = htmlspecialchars($learnPathName);
            echo claro_disp_tool_title($titleTab);

            // display a list of user and their respective progress    
            $sql = "SELECT U.`nom`, U.`prenom`, U.`user_id`
                    FROM `".$TABLEUSER."` AS U, 
                         `".$TABLECOURSUSER."` AS CU
                    WHERE U.`user_id`= CU.`user_id`
                    AND CU.`code_cours` = '". addslashes($_cid) ."'";

            $usersList = claro_sql_query_fetch_all($sql);

            // display tab header
            echo '<table class="claroTable" width="100%" border="0" cellspacing="2">'."\n\n"
                   .'<tr class="headerX" align="center" valign="top">'."\n"
                .'<th>'.get_lang('Student').'</th>'."\n"
                .'<th colspan="2">'.get_lang('Progress').'</th>'."\n"
                .'</tr>'."\n\n"
                .'<tbody>'."\n\n";

            // display tab content
            foreach ( $usersList as $user )
            {
                $lpProgress = get_learnPath_progress($path_id,$user['user_id']);
                echo '<tr>'."\n"
                    .'<td><a href="lp_modules_details.php?uInfo='.$user['user_id'].'&amp;path_id='.$path_id.'">'.$user['nom'].' '.$user['prenom'].'</a></td>'."\n"
                    .'<td align="right">'
                    .claro_disp_progress_bar($lpProgress, 1)
                      .'</td>'."\n"
                    .'<td align="left"><small>'.$lpProgress.'%</small></td>'."\n"
                    .'</tr>'."\n\n";
            }
            // foot of table
            echo '</tbody>'."\n\n".'</table>'."\n\n";
        }
    }
}
// not allowed
else
{
    echo claro_disp_message_box(get_lang('TrackingDisabled'));
}

include($includePath."/claro_init_footer.inc.php");
?>
