<?php // $Id$

die ("deprecated");

$mtime = microtime();
$mtime = explode(" ",$mtime);
$mtime = $mtime[1] + $mtime[0];
$starttime = $mtime;
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.4.* $Revision$                             |
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
      +----------------------------------------------------------------------+
 */

/**
 * try to create main database of claroline without remove existing content
 */

$langFile = "admin";
require '../../inc/claro_init_global.inc.php';
$nameTools = $langAdministrationTools;
//@include($includePath."/config.inc.php");

define ("NO_WAY", 0);
define ("CONFIRM_FORM", 1);
define ("SHOW_PROGRESS", 2);
define ("GO_TO_NEXT", 3);
define ("SHOW_ERROR_LIST", 4);

if ($statsDbName=="") $statsDbName = $mainDbName;
$sqlForUpdate[] = "USE ".$mainDbName;
@include("createMainBase.sql.php");
@include("repairTables.sql.php");
$sqlForUpdate[] = "USE ".$mainDbName;
@include("createPMAextBase.sql.php");
@include("repairTables.sql.php");
$sqlForUpdate[] = "USE ".$statsDbName;
@include("createTrackingBase.sql.php");
@include("repairTables.sql.php");

$langUpgradeDataBase = "Upgrading Main Database ";

if (!function_exists(mysql_info)) {function mysql_info() {return "";}} // mysql_info is used in 

$db = mysql_connect("$mysqlServer", "$mysqlUser", "$mysqlPassword");
mysql_select_db($mainDbName);
$nameTools = $langUpgradeDataBase;

$up_link ="checkCourseDatabase.php";

if ($encrypt)
{
	$result=mysql_query(" SELECT * FROM user");
	while ($myrow = mysql_fetch_array($result))
	{
		$id=$myrow[user_id];
		$newpass=md5($myrow[password]);
		$sqlForUpdate[] = " UPDATE user SET password = '$newpass' WHERE user_id = $id";
	}
}
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<TITLE>
      -- Claroline upgrade -- version <?php echo $clarolineVersion ?>
	</TITLE>
<link rel="stylesheet" href="../../css/default.css" type="text/css">

<style  media="print" >
.notethis {	border: thin double Black;	margin-left: 15px;	margin-right: 15px;}
PRE { size : -1 }
</style>

</head>
<body bgcolor="white" dir="<?php echo $text_dir ?>">
<div align="center">
<table cellpadding="6" cellspacing="0" border="0" width="650" bgcolor="#E6E6E6">
	<tr bgcolor="#4171B5">
		<td valign="top">
			<font color="white" face="arial, helvetica">
				-- Claroline upgrade -- version <?php echo $clarolineVersion ?>
			</font>
		</td>
	</tr>
	<tr bgcolor="#E6E6E6">
		<td>
			<font face="arial, helvetica">
<?
echo "
<br>
<br>
<strong>Step 4</strong>: main Claroline database (<code>".$mainDbName."</code>) upgraded
<br>";

echo "
<OL>";
$nbError =0;
while (list($key,$sqlTodo) = each($sqlForUpdate))
{
	if ($sqlTodo[0] == "#")
	{
		if ($verbose)
		{
			echo "
<BR>
<BR>
<font color=blue>
	-------------  $sqlTodo -------------
</font>
<br>";	}
	}
	else
	{
		$res = @mysql_query($sqlTodo);
		if ($verbose)
		{
			echo "
	<LI>
		<u>$currentCourseDbName</u> : <Small><TT>$sqlTodo</TT></Small>
		<BR>
		".mysql_affected_rows()." affected rows
		<br>
		".mysql_info ();
		}
		if (mysql_errno() > 0)
		{
			if (mysql_errno() == 1060 || mysql_errno() == 1062 || mysql_errno() == 1091 || mysql_errno() == 1054 )
			{
				if ($verbose)
				{
					echo "
		<BR>
		<font color=green>
			".mysql_errno()."
			</u> : ".mysql_error()."

		</font>
	</LI>";
				}
			}
			else
			{
				echo "
		<BR>
		<font color=red>
			<strong>
				".(++$nbError)."
			</strong> n°
			<u>
				".mysql_errno()."
			</u> : ".mysql_error()."
			<br><code><small>".$sqlTodo."</small></code>
		</font>
	</LI>";
			}
		}
	}
}
mysql_close();
echo "
</OL>
";
$mtime = microtime();
$mtime = explode(" ",$mtime);
$mtime = $mtime[1] + $mtime[0];
$endtime = $mtime;
$totaltime = ($endtime - $starttime);

if ($nbError>0 )
{
	echo "
		<font color=red>
			<strong>$nbError errors found</strong>
		</font>
<br>
<Form action=\"".$PHP_SELF."\" >
	Retry with more detail.
	<input type=\"hidden\" name=\"verbose\" id=\"verbose\" value=\"true\">
	<input type=\"submit\" name=\"retry\" value=\"retry\">
</FORM>

<br>
<br>
<br>
		";
}
else
{
	?>
<p align="right">
	<font color=green>
			<strong>Ok</strong>&nbsp;&nbsp;&nbsp;
	</font>
</p>
<Form action="./batchUpdateDb.php" >
	<strong>Step 5</strong>: Upgrade all courses databases
	<DIV>
	<input type="checkbox" name="verbose" value="true">
	verbose (<small>output massives information, use only if needed</small>)<br>
	<input type="submit" name="upgrade" value="Upgrade">
	</DIV>
</FORM>
<br>
<br>
	<?

}

switch($display)
{
	case NO_WAY :
//		echo "you are not logged";
	break;
	case CONFIRM_FORM :
	break;
	case SHOW_PROGRESS :
	break;
	case SHOW_ERROR_LIST :
	break;
	case GO_TO_NEXT :
	?>
<p align="right">
	<font color=green>
			<strong>Ok</strong>&nbsp;&nbsp;&nbsp;
	</font>
</p>
<Form action="./batchUpdateDb.php" >
	Upgrade all courses databases
	<input type="submit" name="upgrade" value="Upgrade">
</FORM>
<br>
<br>
	<?
	break;
}
echo "-".$totaltime."-";
?>
