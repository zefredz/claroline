<?php // $Id$

die ("deprecated");

/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.3.2 - $Revision$                          |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2002 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      | Authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
      |          Hugues Peeters    <peeters@ipm.ucl.ac.be>                   |
      |          Christophe Gesché <gesche@ipm.ucl.ac.be>                    |
      +----------------------------------------------------------------------+
      | Stats d'utilisation de claroline.                                    |
      +----------------------------------------------------------------------+
 */
session_start();

include('../include/config.php');
$db = mysql_connect("$mysqlServer", "$mysqlUser", "$mysqlPassword");

$langAdmin = "administration technique de la plateforme";

@include("../lang/english/complete.lang.php");

header('Content-Type: text/html; charset='. $charset);

$nameTools = $langStatistics;
$interbredcrump[]= array ("url"=>"index.php", "name"=> $langAdmin);

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>
<head>
<link rel="stylesheet" href="../css/default.css" type="text/css">

<title>
	<?php echo "$nameTools - admin - $siteName - $clarolineVersion"; ?>
</title>
</head>
<body bgcolor="white" dir="<?php echo $text_dir ?>">
<table border="0" align="center" cellpadding="0" cellspacing="0" width="<?php echo $mainInterfaceWidth?>">
	<tr>
		<td>
<?php
include('../include/claroline_header.php');
	include "barre.inc.php";
?>
		</td>
	</TR>
	<tr valign="top">
		<td >
			<h4>
				<?php echo $nameTools ?>
				
			</h4>
			<br>
		</td>
	</tr>
	<tr>
		<td>
			<UL>
<!-- // Nombre de profs -->
				<LI>
					<a Href="<?php echo $phpMyAdminWeb?>/db_stats.php?lang=fr&server=1">Stat Database</a>
				</LI>
				<LI>
					<?php echo $langNbLogin	?>
					<UL>
						<LI>
							depuis le <?php 
							echo list_1Result("select loginout.when from loginout order by loginout.when limit 1 "); ?>
			 				: <?php echo list_1Result("select count(*) from loginout where loginout.action ='LOGIN' "); ?>

						<LI>
							<?php echo $langLast30Days	?>
							: <?php echo list_1Result("select count(*) from loginout where action ='LOGIN' and (loginout.when > DATE_ADD(CURDATE(), INTERVAL -31 DAY))  "); ?>

						<LI>
							<?php echo $langLast7Days ?>
							: <?php echo list_1Result("select count(*) from loginout where action ='LOGIN' and (loginout.when > DATE_ADD(CURDATE(), INTERVAL -7 DAY))  "); ?>
						<LI>
			<?php echo $langToday	?>
			aujourd'hui: 
  <?php 
	echo list_1Result("select count(*) from loginout where action ='LOGIN' and (loginout.when > curdate())"); ?>
	</UL>
	<LI>
		<?php echo $langNbProf ?> (1)
		: <?php echo list_1Result("select count(*) from user where statut = 1;");?>
	</LI>
	<LI>
		<?php echo $langNbStudents ?> (5)
		: <?php echo list_1Result("select count(*) from user where statut = 5;");?>
	</LI>
	
	<LI>
		Nombre d'inscrits par statut   
		: <?php tablize(list_ManyResult("select DISTINCT statut, count(*) from user Group by statut ")); ?>
	<LI>
		Nombre de cours 
		: <?php echo list_1Result("select count(*) from cours;");?>
	</LI>

	<LI>
		Nombre de cours par Facultés
		: <?php tablize(list_ManyResult("select DISTINCT faculte, count(*) from cours Group by faculte")); ?>
	</LI>

	<LI>                    

		Nombre d'étudiant par Facultés
		: <?php tablize(list_ManyResult("SELECT DISTINCT c.faculte, count(cu.user_id) FROM `cours_user` cu , cours c where cu.code_cours = c.code GROUP BY c.faculte")); ?>
	</LI>

	<LI>
		Nombre de cours par langues
		: <?php tablize(list_ManyResult("select DISTINCT languageCourse, count(*) from cours Group by languageCourse ")); ?>
	</LI>

	<LI>
		Nombre de cours par Visibilité 
                : <?php tablize(list_ManyResult("select DISTINCT visible, count(*) from cours Group by visible")); ?>
	</LI>

	<LI>
		Nombre d'inscrit par cours :
	  <?php tablize(list_ManyResult("select CONCAT(code_cours,\" Statut :\",statut), count(user_id) from cours_user Group by code_cours, statut order by code_cours")); ?>
	</LI>

	<LI>
		<?php echo $langNbAnnoucement."
		:
		".list_1Result("select count(*) from annonces;");?>
	</LI>
	</LI>

</UL>
<?php	include "barre.inc.php";?>
<font size="+2" color="#FF0000">Erreurs :</font>
<UL>
  <LI><strong>LOGIN multiples </strong> : <br>
  

  <?php  
$sqlLoginDouble = "select DISTINCT username , count(*) as nb from user group by username HAVING nb > 1  order by nb desc ";
$loginDouble = list_ManyResult($sqlLoginDouble);
echo $sqlLoginDouble;
if (is_array($loginDouble))
{ 	
	echo "<BR>";
	echoDefcon(6);
 	echo "<BR>";
	tablize($loginDouble);
} 
else
{ 
	echoDefcon();
}
 ?></LI>
  <LI><strong>email multiples </strong> : <br>
  

  <?php  
$sqlLoginDouble = "select DISTINCT email , count(*) as nb from user group by email HAVING nb > 1  order by nb desc";
$loginDouble = list_ManyResult($sqlLoginDouble);
echo $sqlLoginDouble;
if (is_array($loginDouble))
{ 	
	echo "<BR>";
	echoDefcon(7);
 	echo "<BR>";
	tablize($loginDouble);
} 
else
{ 
	echoDefcon();
}
 ?></LI>
<LI><strong>paire LOGIN - PASS multiples </strong>: <br>


<?php  
$sqlLoginDouble = "
select 
	DISTINCT CONCAT(username, \" -- \", password) as paire, count(*) as nb from user group by paire HAVING nb > 1   order by nb desc";
$loginDouble = list_ManyResult($sqlLoginDouble);
echo $sqlLoginDouble;
if (is_array($loginDouble))
{ 
	echo "<BR>";
	echoDefcon(4);
	echo "<BR>";
	tablize($loginDouble);
} 
else
{ 
	echoDefcon();
}
  ?>
</LI>

</UL>
</td>
      </tr>
      <tr>
		<td colspan=2>
			<br>
			<?php	include "barre.inc.php";
				?>
			
			</td>
	</tr>
</table>
</body>
</html>







<?

/**
 * output an <Table> with an array
 *
 * @return void
 * @param  array $tableau arrey to output
 * @desc output an <Table> with an array
 */

 
function tablize($tableau)
{ 
	if (is_array($tableau))
  	{ 
		echo "<table ";
		echo "align=\"center\"  ";
    	echo "bgcolor=\"#ffcccc\"  border=\"1\" ";
    	echo "cellpadding=\"1\" cellspacing=\"0\" > ";
    	while ( list( $key, $laValeur ) = each($tableau))
	    { 
			echo "<TR>"; 
			echo "<TD bgcolor=\"#99CCFF\">".$key."</td>";
			echo "<TD bgcolor=\"#eeeeee\">".$laValeur."</td>";
			echo"</tr>";
		}
	echo "</table>";
	}
}

function echoDefcon($levelOfDefcon="7")
{  
	if ($levelOfDefcon==7)
    	echo "<font color=\"#00FF000\">All Right</font>";
    else 
		echo "<font size=\"+".(7-$levelOfDefcon)."\" color=\"#FF0000\"><B>!! W A R N !! Defcon ".$levelOfDefcon."</B></font>";
} 


function list_1Result($sql)
{
	GLOBAL $db;
	$res = claro_sql_query($sql ,$db);
	$res    = mysql_fetch_array($res);
	return $res[0];
}

function list_ManyResult($sql)
{ 
	GLOBAL $db;
	$res = claro_sql_query($sql ,$db);
	while ($resA = mysql_fetch_array($res))
	{ 
		$resu[$resA[0]]=$resA[1];
	}
	return $resu;
}

?>
