<?php // $Id$
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.4.1 $Revision$                            |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2003 Universite catholique de Louvain (UCL)      |
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
 
$langFile = "tracking";
require '../inc/claro_init_global.inc.php';

$interbredcrump[]= array ("url"=>"courseLog.php", "name"=> $langToolName);

$nameTools = $langDelCourseStats;

$htmlHeadXtra[] = "<style type='text/css'>
<!--
.secLine {background-color : #E6E6E6;}
.content {padding-left : 15px;padding-right : 15px; }
.specialLink{color : #0000FF;}
-->
</style>
<STYLE media='print' type='text/css'>
<!--
TD {border-bottom: thin dashed Gray;}
-->
</STYLE>";

// regroup table names for maintenance purpose
$TABLETRACK_ACCESS      = $_course['dbNameGlu']."track_e_access";
$TABLETRACK_DOWNLOADS   = $_course['dbNameGlu']."track_e_downloads";
$TABLETRACK_UPLOADS     = $_course['dbNameGlu']."track_e_uploads";
$TABLETRACK_EXERCISES   = $_course['dbNameGlu']."track_e_exercices";


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
        echo $langDeleteDone 
              ."<br /><br />"
              ."<a href=\"courseLog.php\">"
              .$langBack
              ."</a>";
        
    }					// end if $delete
    else
    {
      // ASK DELETE CONFIRMATION TO THE USER
    
      echo	 "<p>"
          .$langConfirmDelete
          ."</p>"
    
          ."<p>";
    
      echo "<a href=\"".$PHP_SELF."?delete=yes\">"
        .$langYes
        ."</a>";
    
    
      echo "&nbsp;|&nbsp;";
    
          echo "<a href=\"courseLog.php\">"
          .$langNo
          ."</a>";
    
      echo "</p>";
    
    }		// end else if $delete
} //end if isAllowedToDelete
else
{
  die ( $langNotAllowed );
}


include($includePath."/claro_init_footer.inc.php");
?>