<?php  session_start();

/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.3.0 $Revision$                             |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2002 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      |   $Id$   |
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

include('../include/config.php');
include('../include/lang.php');
include("../lang/french/admin.inc.php");
@include("../lang/english/admin.inc.php");
@include("../lang/$language/trad4all.inc.php");
@include("../lang/$language/admin.inc.php");
header('Content-Type: text/html; charset='. $charset);

$nameTools = $langCreateClaroTables;
$interbredcrump[]= array ("url"=>"index.php", "name"=> $langAdmin);


function show_structure($nomTable)
{
};

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">

<html>
	<title>
		<?php echo "$nameTools - $langAdmin - $siteName - $clarolineVersion"; ?>

	</title>
<link rel="stylesheet" href="../css/default.css" type="text/css">

<style type="text/css">
.source {font-family : monospace; font-size : small; text-indent : 25px; background-color : silver;	color : blue;}
</style>
</head>
<body bgcolor=#FFFFFF>
<h4>
	<?php echo $langCreateClaroTables." ".$siteName." [Claroline - ".$clarolineVersion."] " ?>
</h4>

<P class="source"					>
This  Script  isn't ready, <br>

please   note   name  of table  <br>

Find  it  in  sql/claroline.sql<br>

Run creating request of  each tables.
</P>
<HR>

Demo
<hr>

<?
if (isset($create))
{
    echo "
tables à créer : 
<TABLE border=\"1\" >";
	while (list($key,$nomTable) = each($create))
	{ 
		echo "
	<TR>
		<TD>
			".($key+1)."
		</TD>
		<TD>
			<strong>$nomTable</strong>";
		switch ($nomTable)
		{
			case "user" :
				echo "
			-> adding ".$nomTable."
			<BR>";
			break;
			case "admin" : 
				echo "
			-> adding ".$nomTable."
			<BR>";
			break;
			case "work" : 
				echo "
			-> adding ".$nomTable."
			<BR>";
				show_structure($nomTable);
			break;
			default :
				echo "
				<br>
					--- $langTableStructureDontKnow ---
				<br>";
		}
		echo "
		</TD>
	</TR>";	
	}
	echo "
</TABLE>";

}
else
{
	echo "
	<br>
	 -> <a href=\"checkDatabase.php\">Check Database</a>";
}
?>

</body>
</html>
