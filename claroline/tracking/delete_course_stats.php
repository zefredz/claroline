<?php // $Id$
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.6
      +----------------------------------------------------------------------+
      | Copyright (c) 2001-2006 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      |   This program is free software; you can redistribute it and/or      |
      |   modify it under the terms of the GNU General Public License        |
      |   as published by the Free Software Foundation; either version 2     |
      |   of the License, or (at your option) any later version.             |
      |                                                                      |
      |   This program is distributed in the hope that it will be useful,    |
      |   but WITHOUT ANY WARRANTY; without even the implied warranty of     |
      |   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the      |
      |   GNU General Public License for more details.                       |
      |                                                                      |
      |   You should have received a copy of the GNU General Public License  |
      |   along with this program; if not, write to the Free Software        |
      |   Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA          |
      |   02111-1307, USA. The GNU GPL license is also available through     |
      |   the world-wide-web at http://www.gnu.org/copyleft/gpl.html         |
      +----------------------------------------------------------------------+
      | Authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
      |          Hugues Peeters    <peeters@ipm.ucl.ac.be>                   |
      |          Christophe Gesché <gesche@ipm.ucl.ac.be>                    |
      |          Sebastien Piraux  <piraux_seb@hotmail.com>
      +----------------------------------------------------------------------+
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

$nameTools = get_lang('DelCourseStats');

include($includePath."/claro_init_header.inc.php");

echo claro_disp_tool_title($nameTools);

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
    echo get_lang('DelCourseStatsDone')."\n"
         .'<br /><br />'."\n"
         .'<small><a href="courseLog.php">&lt;&lt;&nbsp;'.get_lang('Back').'</a></small>'."\n";
    
}                    // end if $delete
else
{
  // ASK DELETE CONFIRMATION TO THE USER

  echo "\n".'<p>'."\n"
    .get_lang('ConfirmDeleteStats')."\n"
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
