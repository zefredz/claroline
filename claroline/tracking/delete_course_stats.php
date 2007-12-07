<?php // $Id$
/**
 * CLAROLINE
 *
 * @version 1.8 $Revision$
 *
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @author Sebastien Piraux  <piraux_seb@hotmail.com>
 * @author Christophe Gesché <moosh@claroline.net>
 *
 */

define('DISP_FORM', __LINE__);
define('DISP_FLUSH_RESULT', __LINE__);
$msg = array();

require '../inc/claro_init_global.inc.php';
require_once $includePath . '/lib/form.lib.php';

if ( ! $_cid || ! $is_courseAllowed ) claro_disp_auth_form(true);
if ( ! $is_courseAdmin ) claro_die(get_lang('Not allowed'));

$validCmdList = array('delete');
$cmd = isset($_REQUEST['cmd'] ) && in_array($_REQUEST['cmd'],$validCmdList) ? $_REQUEST['cmd'] : null;
$validScopeList=array('ALL','BEFORE');
$scope = isset($_REQUEST['scope'] ) && in_array($_REQUEST['scope'], $validScopeList) ? $_REQUEST['scope'] : null;

 if ( isset($_REQUEST['beforeDate'])
    && is_array($_REQUEST['beforeDate'])
    && array_key_exists('day',$_REQUEST['beforeDate'])
    && array_key_exists('month',$_REQUEST['beforeDate'])
    && array_key_exists('year',$_REQUEST['beforeDate'])
    && (bool) checkdate( $_REQUEST['beforeDate']['month'], $_REQUEST['beforeDate']['day'], $_REQUEST['beforeDate']['year'] ))
$beforeDate = mktime(0,0,0, $_REQUEST['beforeDate']['month'], $_REQUEST['beforeDate']['day'], $_REQUEST['beforeDate']['year'] );
else  $beforeDate = null;

// mktime($beforeDate[])
// regroup table names for maintenance purpose
$tbl_cdb_names = claro_sql_get_course_tbl();
$tbl_track_e_access          = $tbl_cdb_names['track_e_access'];
$tbl_track_e_downloads       = $tbl_cdb_names['track_e_downloads'];
$tbl_track_e_uploads         = $tbl_cdb_names['track_e_uploads'];
$tbl_track_e_exercices       = $tbl_cdb_names['track_e_exercices'];
$tbl_track_e_exe_details     = $tbl_cdb_names['track_e_exe_details'];
$tbl_track_e_exe_answers     = $tbl_cdb_names['track_e_exe_answers'];
$tbl_lp_user_module_progress = $tbl_cdb_names['lp_user_module_progress'];

if( 'delete' == $cmd && 'BEFORE' == $scope )
{
    $msg['info'][] = get_block('Delete all event before %date in statistics',array('%date'=>claro_disp_localised_date($GLOBALS['dateFormatLong'], $beforeDate)));

    if(!is_null($beforeDate))
    {
        // do delete

        $sql = "DELETE FROM `" . $tbl_track_e_access . "` WHERE UNIX_TIMESTAMP(access_date) < " . (int) $beforeDate;
        claro_sql_query($sql);
        $sql = "DELETE FROM `" . $tbl_track_e_downloads . "` WHERE UNIX_TIMESTAMP(down_date) < " . (int) $beforeDate ;
        claro_sql_query($sql);
        $sql = "DELETE FROM `" . $tbl_track_e_uploads . "` WHERE  UNIX_TIMESTAMP(upload_date)  < " . (int) $beforeDate ;
        claro_sql_query($sql);

        $sql = "SELECT `exe_id` FROM `" . $tbl_track_e_exercices . "` WHERE  UNIX_TIMESTAMP(exe_date)  < " . (int) $beforeDate;
        $exeList = claro_sql_query_fetch_all_cols($sql);
        $exeList = $exeList['exe_id'];

        $sql = "DELETE FROM `" . $tbl_track_e_exercices . "` WHERE  UNIX_TIMESTAMP(exe_date)  < " . (int) $beforeDate;
        claro_sql_query($sql);

        $sql = "SELECT `id` FROM `".$tbl_track_e_exe_details."` WHERE `exercise_track_id` IN ('" . implode("', '",$exeList) . "')";
        $detailList = claro_sql_query_fetch_all_cols($sql);
        $detailList = $detailList['id'];
        $sql = "DELETE FROM `" . $tbl_track_e_exe_details . "` WHERE `exercise_track_id` IN ('" . implode("', '",$exeList) . "')";
        claro_sql_query($sql);

        $sql = "DELETE FROM `" . $tbl_track_e_exe_answers . "` WHERE details_id  IN ('" . implode("', '",$detailList) . "')";
        claro_sql_query($sql);


        /*
        $sql = "DELETE FROM `".$tbl_lp_user_module_progress."` WHERE date < '" . $beforeDate . "'";
        claro_sql_query($sql);
        */

    }
    else
    {
        $msg['error'][] = get_block('%date not valid',array('%date'=>claro_disp_localised_date($GLOBALS['dateFormatLong'])));
    }

    $display = DISP_FLUSH_RESULT;

}

if( 'delete' == $cmd && 'ALL' == $scope )
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

    $display = DISP_FLUSH_RESULT;


}                    // end if $delete
else
{
    // ASK DELETE CONFIRMATION TO THE USER
    $display = DISP_FORM;
}        // end else if $delete



//PREPARE DISPLAY


$interbredcrump[]= array ('url' => 'courseLog.php', 'name' => get_lang('Statistics'));
$nameTools = get_lang('Delete all course statistics');

if (DISP_FORM == $display)
{
    if (is_null($beforeDate))
    {
        $beforeDate  = time();
        $sql = "SELECT min(UNIX_TIMESTAMP(access_date)) FROM `".$tbl_track_e_access."`";
        $min = claro_sql_query_get_single_value($sql);
        if(!is_null($min) && $beforeDate > $min) $beforeDate  = $min;
        $sql = "SELECT min(UNIX_TIMESTAMP(down_date)) FROM `".$tbl_track_e_downloads."`";
        $min = claro_sql_query_get_single_value($sql);
        if(!is_null($min) && $beforeDate > $min) $beforeDate  = $min;
        $sql = "SELECT min(UNIX_TIMESTAMP(upload_date)) FROM `".$tbl_track_e_uploads."`";
        $min = claro_sql_query_get_single_value($sql);
        if(!is_null($min) && $beforeDate > $min) $beforeDate  = $min;
        $sql = "SELECT min(UNIX_TIMESTAMP(exe_date)) FROM `".$tbl_track_e_exercices."`";
        $min = claro_sql_query_get_single_value($sql);
        if(!is_null($min) && $beforeDate > $min) $beforeDate  = $min;

    }
}

// RUN DISPLAY

include($includePath . '/claro_init_header.inc.php');

echo claro_html::tool_title($nameTools);
echo claro_html::msg_list($msg);

if  ( DISP_FLUSH_RESULT == $display)
{
    // display confirm msg and back link
    echo get_lang('Course statistics deleted')."\n"
    .    '<br /><br />' . "\n"
    .    '<small>'
    .    '<a href="courseLog.php">'
    .    '&lt;&lt;&nbsp;'
    .    get_lang('Back')
    .    '</a>'
    .    '</small>' . "\n"
    ;

} elseif  ( DISP_FORM == $display)
{
    // ASK DELETE CONFIRMATION TO THE USER

    echo '<p>' . "\n"
    .    '<form action="' . $_SERVER['PHP_SELF'] . '">'
    .    get_lang('Delete all course statistics')
    .    '<br>'
    .    '<br>'
    .    '<input type="radio" name="scope" id="scope_all" value="ALL">'
    .    '<label for="scope_all">' . get_lang('All') . '</label>'
    .    '<br>'
    .    '<input type="radio" name="scope" id="scope_before" value="BEFORE" checked="checked">'
    .    '<label for="scope_before" >' . get_lang('Before') . '</label> '
    .    claro_disp_date_form('beforeDate[day]', 'beforeDate[month]', 'beforeDate[year]', $beforeDate, 'short' )
    .    '<input type="hidden" name="cmd" value="delete">'
    .    '<br>'
    .    '<input type="submit" name="action" value="' . get_lang('Ok') . '"> '
    .    claro_html_button('courseLog.php', get_lang('Cancel') )
    .    '<br>'
    .    '<br>'
    .    '</p>' . "\n"
    ;

}        // end else if $delete

include ( $includePath . '/claro_init_footer.inc.php' );
?>