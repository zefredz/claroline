<?php // $Id$
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

$langStat4Claroline = "Statistiques de la plateforme";
$langAdmin = "administration technique de la plateforme";

@include("../lang/french/trad4all.inc.php");
@include("../lang/english/trad4all.inc.php");
@include("../lang/$language/trad4all.inc.php");
header('Content-Type: text/html; charset='. $charset);
@include("../lang/french/admin.inc.php");
@include("../lang/english/admin.inc.php");
@include("../lang/$language/admin.inc.php");

$nameTools = $langStat4Claroline;
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
		: <?= list_1Result("select count(*) from user where statut = 1;");?>
	</LI>
	<LI>
		<?php echo $langNbStudents ?> (5)
		: <?= list_1Result("select count(*) from user where statut = 5;");?>
	</LI>
	
	<LI>
		Nombre d'inscrits par statut   
		: <?php tablize(list_ManyResult("select DISTINCT statut, count(*) from user Group by statut ")); ?>
	<LI>
		Nombre de cours 
		: <?= list_1Result("select count(*) from cours;");?>
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
<?	include "barre.inc.php";?>
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
			<?	include "barre.inc.php";
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
	$res = mysql_query($sql ,$db);
	$res    = mysql_fetch_array($res);
	return $res[0];
}

function list_ManyResult($sql)
{ 
	GLOBAL $db;
	$res = mysql_query($sql ,$db);
	while ($resA = mysql_fetch_array($res))
	{ 
		$resu[$resA[0]]=$resA[1];
	}
	return $resu;
}


/*
* $Log$
* Revision 1.1  2004/06/02 07:49:03  moosh
* Initial revision
*
* Revision 1.25  2003/03/12 13:59:48  moosh
* phpMyAdminWeb in place of phpMyAdminUrl
*
* Revision 1.24  2003/01/08 11:59:48  peeters
* Modified image path to 'claroline/img'
*
* Revision 1.23  2002/12/20 09:43:44  moosh
* show  number of  user by faculty
*
* Revision 1.22  2002/10/29 00:34:06  moosh
* there was 2 bug  in sql  for "n last days".
*
* Revision 1.21  2002/10/18 22:06:38  moosh
* - added dir  param in  body for  specify the writing direction
*
* Revision 1.20  2002/08/27 10:10:23  moosh
* FONT arial
*
* Revision 1.19  2002/07/31 15:03:26  moosh
* $mainInterfaceWith -> $mainInterfaceWidth
*
* Revision 1.18  2002/07/30 07:47:15  moosh
* - use claroline_header
* - use  $nameTools
* - standardize begin  of pages
*
* Revision 1.17  2002/07/11 12:28:01  moosh
* comment  function
*
* Revision 1.16  2002/07/04 06:53:41  moosh
* - 1.0 -> 1.3.0
*
* Revision 1.15  2002/04/23 08:32:08  moosh
* Using var in config (for  phpSysInfo and  phpMyAdmin)
* Adding  some  translations vars
*
* Revision 1.14  2002/04/22 07:29:53  moosh
* name  of  site  in title
*
* Revision 1.13  2002/04/22 07:10:44  moosh
* add some  request from loginout
*
* Revision 1.12  2002/04/11 13:56:05  moosh
* <? became <?php
*
* Revision 1.11  2002/03/25 17:00:37  moosh
* link  to stat database
*
* Revision 1.10  2002/03/13 19:47:09  moosh
* ajout nombre d'inscrit par cours
*
* Revision 1.9  2002/03/12 15:00:01  moosh
* ajout contrôle  email doubles
*
* Revision 1.8  2002/03/05 23:10:30  moosh
* mise en page à la moosh
*
* Revision 1.7  2002/03/05 17:20:01  moosh
* ajout de barre.inc.php
* ajout du test de double login
*
* Revision 1.6  2002/03/04 20:58:24  moosh
* 1° il y avait  inversion  entre  le  1 et 5  pour  prof étudiant
* 2° le test  de "paire" est sur  login pass et pas login seul
*
* Revision 1.5  2002/02/27 17:38:46  cvsuser
* ajout de detection doublon username
*
* Revision 1.4  2002/02/27 14:03:16  cvsuser
* ajout  couleur de fond
*
* Revision 1.3  2002/02/27 03:20:45  cvsuser
* ajouts de chiffres
*
* Revision 1.2  2002/02/27 02:13:39  cvsuser
* ajout de calculs
*
* Revision 1.1  2002/02/26 22:03:32  cvsuser
* Statistiques d'utilisation
*
*/
?>
