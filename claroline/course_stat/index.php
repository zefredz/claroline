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
      +----------------------------------------------------------------------+
 */

 /**
  * @desc This script write  some  info about the  course
  * 
  */

require '../inc/claro_init_global.inc.php'; 
$nameTools = $langStatsOfCourse;
@include($includePath."/claro_init_header.inc.php");

/*
 * DB tables definition
 */

$tbl_cdb_names = claro_sql_get_course_tbl();
$tbl_mdb_names = claro_sql_get_main_tbl();
$TABLEUSERCOURSE 	= $tbl_mdb_names['rel_course_user'  ];
$TABLEFORUM 		= $tbl_cdb_names['bb_forums'        ];
$TABLEAGENDA 		= $tbl_cdb_names['calendar_event'   ];
$TABLEDOCUMENT 		= $tbl_cdb_names['document'         ];
$TABLEEXERCICE 		= $tbl_cdb_names['quiz_test'        ];

$currentCourseID	= $_course['sysCode'];
claro_disp_tool_title(array("mainTitle"=>$nameTools,"subTitle"=>$langSubTitle));
?>
<H3>
<?php echo $langUserCourseList ?>
</H3>
<OL>
<?php
$sqlNbUserOfCourse = "SELECT count(user_id) nbUser
					FROM `".$TABLEUSERCOURSE."`
					WHERE code_cours='".$currentCourseID."' ";
$result = mysql_query($sqlNbUserOfCourse);
$myrow = mysql_fetch_array($result);
$countUser = $myrow["nbUser"] ;
?>
	<LI>il y a <?php echo $countUser ?> utilisateurs
<?php
$sqlNb = "SELECT count(id) nb FROM `".$TABLEAGENDA."`";
$result = mysql_query($sqlNb);
$myrow = mysql_fetch_array($result);
$count = $myrow["nb"] ;
?>
</OL>
<H3>
	l'agenda
</H3>
<OL>
	<LI>
		il y a <?php echo $count ?> entrées dans l'agenda
				</li>
</OL>
			<H3>
				les documents
			</H3>
			<OL>
<?php
$sqlNb = "SELECT count(id) nb FROM `".$TABLEDOCUMENT."`";
$result = mysql_query($sqlNb);
$myrow = mysql_fetch_array($result);
$count = $myrow["nb"] ;
?>
				<LI>
					il y a <?php echo $count ?> documents
<?php
$sqlNb = "SELECT count(id) nb FROM `".$TABLEDOCUMENT."` where visibility = 'v'";
$result = mysql_query($sqlNb);
$myrow = mysql_fetch_array($result);
$count2 = $myrow["nb"] ;
?>
					<uL>
						<LI>
							<?php echo $count2?> visibles
						</li>
						<LI>
							<?php echo ($count-$count2)?> non visibles
						</li>
					</uL>
				</LI>
			</OL>
			<H3>
				les exercices
			</H3>
			<OL>
<?php 
$sqlNb = "SELECT count(id) nb FROM `".$TABLEEXERCICE."`";
$result = mysql_query($sqlNb);
$myrow = mysql_fetch_array($result);
$count = $myrow["nb"] ;
?>
				<LI>
					il y a <?php echo $count?> exercices
<?php 
$sqlNb = "SELECT count(id) nb FROM `".$TABLEEXERCICE."` where active = '1'";
$result = mysql_query($sqlNb);
$myrow = mysql_fetch_array($result);
$count2 = $myrow["nb"] ;
?>
					<uL>
						<LI>
							<?php echo $count2?> actifs 
						</LI>
						<LI>
							<?php echo ($count-$count2)?> non actifs
						</LI>
					</uL>
				</LI>
			</OL>
			<H3>
				Les  Forums
			</H3>
<?php
$sqlNb = "SELECT * FROM `".$TABLEFORUM."`";
$result = mysql_query($sqlNb);
?>
<table width="95%" border="0" cellspacing="0" cellpadding="2" align="left" bgcolor="#C0C0C0">
<?php
while ($myrow = mysql_fetch_array($result))
{ 
	echo "
				<TR>
					<TD>
						<TABLE width=\"90%\"  align=\"center\" border=\"0\">
							<TR>
								<TD>
									".$myrow["forum_name"]."
								</TD>
								<TD>
									".$myrow["forum_desc"]."
								</TD>
							</TR>
						</TABLE>
					</TD>
				</TR>	
				<TR>
					<TD>
						<TABLE width=\"94%\"   align=\"center\" border=\"1\">
							<TR>
								<TD>
									access
									<br>".$myrow["forum_access"]."
								</TD>
								<TD>
									Topics
									<br>".$myrow["forum_topics"]."
								</TD>
								<TD>
									Messages
									<br>
									".$myrow["forum_posts"]."
								</TD>
							</TR>
						</TABLE>
					</TD>
				</TR>";	
	
}
?>		
				</TABLE>
<?php
include($includePath."/claro_init_footer.inc.php");
?>
