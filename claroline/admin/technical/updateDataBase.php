<?

$mtime = microtime();
$mtime = explode(" ",$mtime);
$mtime = $mtime[1] + $mtime[0];
$starttime = $mtime;

/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.3.0 $Revision$                            |
      +----------------------------------------------------------------------+
      |  $Id$       |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2002 Universite catholique de Louvain (UCL)      |
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
 * try to create database of course  without remove exitent content
 */
include("createBaseOfACourse.sql.php");

$langUpgradeDataBase = "Upgrading Database ".$currentCourseID;
@include('../include/config.php');
@include("../lang/french/trad4all.inc.php");
@include("../lang/english/trad4all.inc.php");
@include("../lang/$language/trad4all.inc.php");
header('Content-Type: text/html; charset='. $charset);
@include("../lang/french/admin.inc.php");
@include("../lang/english/admin.inc.php");
@include("../lang/$language/admin.inc.php");

$db = mysql_connect("$mysqlServer", "$mysqlUser", "$mysqlPassword");
if (!isset($currentCourseID))
	exit ("<a href=\".\">*** $langBack Course id Missing ***</a>");
mysql_select_db("$currentCourseID",$db);
$nameTools = $langUpgradeDataBase;

$up_link ="checkCourseDatabase.php";
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>
<head>
	<title>
		<?php echo "$nameTools - $langAdmin - $siteName - $clarolineVersion"; ?>

	</title>

</head>
<body bgcolor=#FFFFFF>
<table border="0" align="center" cellpadding="0" cellspacing="0" width="<?php echo $mainInterfaceWidth?>">
	<tr>
		<td>
			<?php include("../include/claroline_header.php"); ?>

		</TD>
	</TR>
	<tr>
		<td>
<?php include "barre.inc.php";?>
<h3>
	<?php echo  $langTools ?>
</h3>
<?
echo "
<H1>Upgrading ".$currentCourseID."</H1>
<OL>";
$nbError =0;
while (list($key,$sqlTodo) = each($sqlForUpdate))
{ 
	if ($sqlTodo[0] == "#")
		echo " 
		<BR>
		<BR>
		<font color=blue>
			-------------  $sqlTodo -------------
		</font>
		<br>";
	else
	{
		echo "
		<LI>
			".$sqlTodo;	
		$res = @mysql_query($sqlTodo);
		if (mysql_errno() > 0)
		{
		if (mysql_errno() == 1060)
		echo "
			<BR>
			<font color=green>
				was done
			</font>
		</LI>";	
		else	
		echo "
			<BR>
			<font color=red>
				<strong>".(++$nbError)."</strong> n° <u>".mysql_errno()."</u> : ".mysql_error()."
			</font>
		</LI>";	
		}
	}
}
echo "
</OL>
			<font color=red>
				<strong>$nbError errors found</strong>
			</font>
";
mysql_close();

$mtime = microtime();
$mtime = explode(" ",$mtime);
$mtime = $mtime[1] + $mtime[0];
$endtime = $mtime;
$totaltime = ($endtime - $starttime);
echo "
<br>
<br>
<font color=gray size=2>
	Upgrade of ".$currentCourseID." done in $totaltime seconds. 
</font><br>
";

 include "barre.inc.php";

/* -------------------- LOG CVS --------------------

 $Log$
 Revision 1.1  2004/06/02 07:49:08  moosh
 Initial revision

 Revision 1.1  2003/05/23 05:19:44  moosh
 tools to config, tune and repair

 Revision 1.8  2002/09/14 12:52:38  moosh
 - now sql is  in a include file (toh have the same  in this file and batchUpdateBases.php

 Revision 1.7  2002/09/09 21:36:35  moosh
 this script execute a list of Sql request to upgrade course Database
 (and  not only show the sql to copy paste in PMA)

 Revision 1.6  2002/06/03 10:03:07  moosh
 -add SQL to add 2 fields  in cours (for  department URL)

 Revision 1.5  2002/03/07 21:54:51  moosh
 AssignTo -> assignTo

 Revision 1.4  2002/02/27 23:26:31  cvsuser
 gestion des assignement de Todo a des admins

 Revision 1.3  2002/02/27 17:49:19  cvsuser
 ajout $Log$
 ajout Revision 1.1  2004/06/02 07:49:08  moosh
 ajout Initial revision
 ajout
 ajout Revision 1.1  2003/05/23 05:19:44  moosh
 ajout tools to config, tune and repair
 ajout
 ajout Revision 1.8  2002/09/14 12:52:38  moosh
 ajout - now sql is  in a include file (toh have the same  in this file and batchUpdateBases.php
 ajout
 ajout Revision 1.7  2002/09/09 21:36:35  moosh
 ajout this script execute a list of Sql request to upgrade course Database
 ajout (and  not only show the sql to copy paste in PMA)
 ajout
 ajout Revision 1.6  2002/06/03 10:03:07  moosh
 ajout -add SQL to add 2 fields  in cours (for  department URL)
 ajout
 ajout Revision 1.5  2002/03/07 21:54:51  moosh
 ajout AssignTo -> assignTo
 ajout
 ajout Revision 1.4  2002/02/27 23:26:31  cvsuser
 ajout gestion des assignement de Todo a des admins
 ajout

*/
?>