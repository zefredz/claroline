<?php // $Id$

session_start();

/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.4.0 $Revision$                             |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2002 Universite catholique de Louvain (UCL)      |
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
<link rel="stylesheet" href="<?php echo $clarolineRepositoryWeb ?>"css/default.css" type="text/css">

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