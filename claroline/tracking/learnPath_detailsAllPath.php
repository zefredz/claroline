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
 * @package CLSTAT
 *
 * @author Claro Team <cvs@claroline.net>
 *
 */
 
require '../inc/claro_init_global.inc.php';

if ( ! $_cid || ! $is_courseAllowed ) claro_disp_auth_form(true);
if ( ! $is_courseAdmin ) claro_die(get_lang('Not allowed'));

$interbredcrump[]= array ("url"=>"../learnPath/learningPathList.php", "name"=> get_lang('Learning path list'));

$nameTools = get_lang('Learning paths tracking');

$tbl_cdb_names               = claro_sql_get_course_tbl();
$tbl_mdb_names               = claro_sql_get_main_tbl();

$tbl_rel_course_user         = $tbl_mdb_names['rel_course_user'  ];
$tbl_user                    = $tbl_mdb_names['user'             ];
$tbl_lp_learnPath            = $tbl_cdb_names['lp_learnPath'           ];
$tbl_lp_rel_learnPath_module = $tbl_cdb_names['lp_rel_learnPath_module'];
$tbl_lp_user_module_progress = $tbl_cdb_names['lp_user_module_progress'];
$tbl_lp_module               = $tbl_cdb_names['lp_module'              ];
$tbl_lp_asset                = $tbl_cdb_names['lp_asset'               ];

// keep old name for inside the library that use the vars in global
$TABLELEARNPATH         = $tbl_lp_learnPath;
$TABLEMODULE            = $tbl_lp_module;
$TABLELEARNPATHMODULE   = $tbl_lp_rel_learnPath_module;
$TABLEASSET             = $tbl_lp_asset;
$TABLEUSERMODULEPROGRESS= $tbl_lp_user_module_progress;

$TABLECOURSUSER            = $tbl_rel_course_user;
$TABLEUSER              = $tbl_user;

include($includePath."/claro_init_header.inc.php");
include($includePath."/lib/statsUtils.lib.inc.php");


include($includePath."/lib/learnPath.lib.inc.php");

// display title
$titleTab['mainTitle'] = $nameTools;
$titleTab['subTitle'] = get_lang('Progression of users on all learning paths');

echo claro_html_tool_title($titleTab);

if ( get_conf('is_trackingEnabled') ) 
{
    // display a list of user and their respective progress
    
    $sql = "SELECT U.`nom`, U.`prenom`, U.`user_id`
          FROM `".$tbl_user."` AS U, `".$tbl_rel_course_user."`     AS CU
          WHERE U.`user_id`= CU.`user_id`
           AND CU.`code_cours` = '". addslashes($_cid) ."'";
    $usersList = claro_sql_query_fetch_all($sql);
    
    // display tab header
    echo '<table class="claroTable emphaseLine" width="100%" border="0" cellspacing="2">'."\n\n"
        .'<tr class="headerX" align="center" valign="top">'."\n"
        .'<th>'.get_lang('Student').'</th>'."\n"
        .'<th colspan="2">'.get_lang('Progress').'</th>'."\n"
        .'</tr>'."\n\n"
        .'<tbody>'."\n\n";
    
    
    // display tab content
    foreach ( $usersList as $user )
    {
        // list available learning paths
        $sql = "SELECT LP.`learnPath_id`
                 FROM `".$tbl_lp_learnPath."` AS LP";

        $learningPathList = claro_sql_query_fetch_all($sql);

        $iterator = 1;
        $globalprog = 0;

        foreach( $learningPathList as $learningPath )
        {
            // % progress
            $prog = get_learnPath_progress($learningPath['learnPath_id'], $user['user_id']);

            if ($prog >= 0)
            {
                $globalprog += $prog;
            }
            $iterator++;
        }


        if( $iterator == 1 )
        {
            echo '<tr><td align="center" colspan="8">'.get_lang('No learning path').'</td></tr>'."\n\n";
        }
        else
        {
            $total = round($globalprog/($iterator-1));
            echo '<tr>'."\n"
                .'<td><a href="'.$clarolineRepositoryWeb.'tracking/userLog.php?uInfo='.$user['user_id'].'&amp;view=0010000">'.$user['nom'].' '.$user['prenom'].'</a></td>'."\n"
                .'<td align="right">'
                .claro_html_progress_bar($total, 1)
                .'</td>'."\n"
                   .'<td align="left"><small>'.$total.'%</small></td>'."\n"
                .'</tr>'."\n\n";
        }

    }
    
    // foot of table
    echo '</tbody>'."\n\n".'</table>'."\n\n";
    
}
else
{
    echo get_lang('Tracking has been disabled by system administrator.');
}



include($includePath . '/claro_init_footer.inc.php');
?>