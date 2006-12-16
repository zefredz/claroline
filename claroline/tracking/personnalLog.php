<?php # $Id$
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

$interbredcrump[]= array ("url"=>"../auth/profile.php", "name"=> get_lang('My User Account'));
$nameTools = get_lang('Statistics');

if (! claro_is_user_authenticated()) claro_disp_auth_form();

$tbl_mdb_names = claro_sql_get_main_tbl();
$tbl_courses            = $tbl_mdb_names['course'];
$tbl_link_user_courses    = $tbl_mdb_names['rel_course_user'];

include(get_path('incRepositorySys')."/lib/statsUtils.lib.inc.php");

////////////// OUTPUT //////////////////////

include(get_path('incRepositorySys')."/claro_init_header.inc.php");
echo claro_html_tool_title($nameTools);

if ( get_conf('is_trackingEnabled') )
{
    // display list of course of the student with links to the corresponding userLog
    $sql = "SELECT `cours`.`code` as `code`,
                `cours`.`intitule` as `name`,
                `cours`.`titulaires` as `prof`
            FROM `".$tbl_courses."` as `cours`,
                `".$tbl_link_user_courses."` as `cours_user`
            WHERE `cours`.`code` = `cours_user`.`code_cours`
            AND `cours_user`.`user_id` = '". (int) claro_get_current_user_id() . "'";

    $courseListOfUser = claro_sql_query_fetch_all($sql);

    if( is_array($courseListOfUser) && !empty($courseListOfUser) )
    {
        echo "\n\n".'<ul>'."\n\n";
        foreach ( $courseListOfUser as $courseOfUser )
        {
            echo '<li>' . "\n"
            .    '<a href="userLog.php?uInfo=' . claro_get_current_user_id() . '&amp;cidReset=true&amp;cidReq=' . $courseOfUser['code'] . '">' . $courseOfUser['name'] . '</a><br />' . "\n"
            .    '<small>' . $courseOfUser['code'] . ' - ' . $courseOfUser['prof'] . '</small>' . "\n"
            .    '</li>' . "\n"
            ;
        }
        echo "\n".'</ul>'."\n";
    }
    else
    {
        echo get_lang('No stats to show.  You haven\'t registered any course.');
    }
}
else
{
    echo get_lang('Tracking has been disabled by system administrator.');
}

include(get_path('incRepositorySys') . '/claro_init_footer.inc.php');
?>