<?php // $Id$
/**
 /**
 * CLAROLINE
 *
 * @version 1.8 $Revision$
 *
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @author Sebastien Piraux  <piraux_seb@hotmail.com>
 */

require '../inc/claro_init_global.inc.php';

if ( ! $_cid || ! $is_courseAllowed ) claro_disp_auth_form(true);
if ( ! $is_courseAdmin ) claro_die(get_lang('Not allowed'));

// regroup table names for maintenance purpose
$tbl_cdb_names = claro_sql_get_course_tbl();
$tbl_track_e_access            = $tbl_cdb_names['track_e_access'];
$tbl_track_e_downloads      = $tbl_cdb_names['track_e_downloads'];
$tbl_track_e_uploads         = $tbl_cdb_names['track_e_uploads'];
$tbl_track_e_exercices         = $tbl_cdb_names['track_e_exercices'];
$tbl_track_e_exe_details     = $tbl_cdb_names['track_e_exe_details'];
$tbl_track_e_exe_answers     = $tbl_cdb_names['track_e_exe_answers'];
$tbl_lp_user_module_progress = $tbl_cdb_names['lp_user_module_progress'];

$interbredcrump[]= array ("url"=>"courseLog.php", "name"=> get_lang('Statistics'));

$nameTools = get_lang('Delete all course statistics');

include($includePath."/claro_init_header.inc.php");

echo claro_html::tool_title($nameTools);

if( isset($_REQUEST['delete']) && $_REQUEST['delete'] == "yes" )
{
    // do delete
    $sql = "TRUNCATE TABLE `".$tbl_track_e_access."`";
    claro_sql_query($sql);

    $sql = "TRUNCATE TABLE `".$tbl_track_e_downloads."`";
    claro_sql_query($sql);

    $sql = "TRUNCATE TABLE `".$tbl_track_e_uploads."`";
    claro_sql_query($sql);

    $sql = "TRUNCATE TABLE `".$tbl_track_e_exercices."`";
    claro_sql_query($sql);

    $sql = "TRUNCATE TABLE `".$tbl_track_e_exe_details."`";
    claro_sql_query($sql);

    $sql = "TRUNCATE TABLE `".$tbl_track_e_exe_answers."`";
    claro_sql_query($sql);

    $sql = "TRUNCATE TABLE `".$tbl_lp_user_module_progress."`";
    claro_sql_query($sql);

    // display confirm msg and back link
    echo get_lang('Course statistics deleted')."\n"
         .'<br /><br />'."\n"
         .'<small><a href="courseLog.php">&lt;&lt;&nbsp;'.get_lang('Back').'</a></small>'."\n";

}                    // end if $delete
else
{
  // ASK DELETE CONFIRMATION TO THE USER

  echo "\n".'<p>'."\n"
    .get_block('blockConfirmDeleteStats')."\n"
    .'</p>'."\n"
    .'<p>'."\n"
      .'<a href="'.$_SERVER['PHP_SELF'].'?delete=yes">'
    .get_lang('Yes')
    .'</a>'
    .'&nbsp;|&nbsp;'
    .'<a href="courseLog.php">'
    .get_lang('No')
    .'</a>'."\n"
    .'</p>'."\n";

}        // end else if $delete

include($includePath."/claro_init_footer.inc.php");
?>
