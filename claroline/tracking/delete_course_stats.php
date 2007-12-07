<?php // $Id$
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.6
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2004 Universite catholique de Louvain (UCL)      |
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

// regroup table names for maintenance purpose
$tbl_cdb_names = claro_sql_get_course_tbl();
$TABLETRACK_ACCESS      = $tbl_cdb_names['track_e_access'];
$TABLETRACK_DOWNLOADS   = $tbl_cdb_names['track_e_downloads'];
$TABLETRACK_UPLOADS     = $tbl_cdb_names['track_e_uploads'];
$TABLETRACK_EXERCISES   = $tbl_cdb_names['track_e_exercices'];


$interbredcrump[]= array ("url"=>"courseLog.php", "name"=> $langStatistics);

$nameTools = $langDelCourseStats;

include($includePath."/claro_init_header.inc.php");

$isAllowedToDelete = ($is_courseAdmin || $is_platformAdmin);

if( $isAllowedToDelete )
{
    claro_disp_tool_title($nameTools);
    
    if($delete)
    {
        // do delete
        $sql = "TRUNCATE TABLE `".$TABLETRACK_ACCESS."`" ;
        claro_sql_query($sql);
        
        $sql = "TRUNCATE TABLE `".$TABLETRACK_DOWNLOADS."`" ;
        claro_sql_query($sql);
        
        $sql = "TRUNCATE TABLE `".$TABLETRACK_UPLOADS."`" ;
        claro_sql_query($sql);
        
        $sql = "TRUNCATE TABLE `".$TABLETRACK_EXERCISES."`" ;
        claro_sql_query($sql);
        
        // display confirm msg and back link
        echo $langDelCourseStatsDone 
              ."<br /><br />"
              ."<a href=\"courseLog.php\">"
              .$langBack
              ."</a>";
        
    }					// end if $delete
    else
    {
      // ASK DELETE CONFIRMATION TO THE USER
    
      echo "\n<p>\n"
		.$langConfirmDeleteStats
		."\n</p>\n"
        ."<p>\n"
	  	."<a href=\"".$_SERVER['PHP_SELF']."?delete=yes\">"
		.$langYes
        ."</a>"
		."&nbsp;|&nbsp;"
		."<a href=\"courseLog.php\">"
		.$langNo
		."</a>\n"
    	."</p>\n";
    
    }		// end else if $delete
} //end if isAllowedToDelete
else
{
  die ( $langNotAllowed );
}

include($includePath."/claro_init_footer.inc.php");
?>
