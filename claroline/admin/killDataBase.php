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
      +----------------------------------------------------------------------+
      | Authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
      |          Hugues Peeters    <peeters@ipm.ucl.ac.be>                   |
      |          Christophe Gesché <gesche@ipm.ucl.ac.be>                    |
      +----------------------------------------------------------------------+
 */
/**
 * On this  pages,  we must  be very safe.
 * They can  delete some Cursus.
 *
 * And  cursus  can't be  delete directly
 * 
 * all cursus must be backed up before/during delete.
 *  Pour  bien faire,  
 *  on devrait 
 *    - 1° Faire  un  backup, 
 *    - 2° Placer sur la page d'accueil de l'admin un Warning et  lui envoyer un  email
 *    - 3° Supprimer le cours 

 *    Perfectionnement :
 *    	- Délai avant la suppression.
 *  		- Les sites  sont marqué  pour  "suppression envisagée"
 *  		- un code détermine 
 *  			- si la suppression est  obligatoire;  
 *  					le délai est donné pour éventuellement récuperer des données
 *  			- si la suppression est effective en cas de non signe de vie
 *  			- 
 *  	
 *    
 */
$nomOutil  = "Kill Database";
$nomPage   = "Admin";

$langFile = "admin";
require '../inc/claro_init_global.inc.php'; 
$is_allowedToEdit 	= $is_platformAdmin || $PHP_AUTH_USER;
//SECURITY CHECK
if (!$is_allowedToEdit) claro_disp_auth_form();

@include("./checkIfHtAccessIsPresent.php");


$nameTools = $langCheckDatabase;
$interbredcrump[]= array ("url"=>"index.php", "name"=> $langAdmin);
include($includePath."/claro_init_header.inc.php");


if ($is_allowedToEdit)
{
?>
<h3>
	<?php echo $nameTools," - ",$langAdmin," - ",$siteName," - ",$clarolineVersion; ?>
</h3>
<?php include "barre.inc.php";?>
<?php 
	$db = mysql_connect("$mysqlServer", "$mysqlUser", "$mysqlPassword");
	mysql_select_db("$mainDbName",$db);
	
	#################################
	## Is there inf to work        ##
	#################################
	if ($submit =="Ok")
	{ 
		?> <h3>Traitement de suppression des bases</h3>
	     par  <?php echo $PHP_AUTH_USER ?><br>
	<?
		$lesStatutDeCours["1"] = "Etudiant";
		$lesStatutDeCours["5"] = "Enseignant";
	
		while (list($key,$contenu)= each($HTTP_POST_VARS["coursToDelete"]))
		{ 
			echo "<HR>";
		    echo "[".$key."]  ";
			echo $langWeGoToDelete." <strong>".$contenu."</strong><BR>\n";
	
	 		############# ###########
			# Backup before delete. #
			############# ###########
	
			echo "\n\t<br>\n\t"."Step 1 : backup"."\n\t<br>\n\t";
	        echo "\n\t<br>\n\t"."---- NON FONCTIONNEL ----"."\n\t<br>\n\t";
	
			mysql_drop_db ("$contenu");
			claro_sql_query("DELETE FROM cours WHERE code='$contenu'");
			claro_sql_query("DELETE FROM cours_user WHERE code_cours='$contenu'");
	//		claro_sql_query("DELETE FROM cours_faculte WHERE code='$contenu'");
			mkPath($garbageRepositorySys);
			rename("../../$contenu", $garbageRepositorySys.$contenu);
	
		}
	    echo "Suppressions réalisées (sauf si erreur affichée)<br>
	    <HR color=\"blue\" noshade size=\"4\">";
		include "barre.inc.php";
	}
 #####################################################################
 ## List of cursus with Checkbox to select wich would be  deleted
 #####################################################################

	include "barre.inc.php"; 

	$sql = "SELECT 
		      cours.faculte f, 
		      cours.code k, 
	   	      cours.fake_code c,
		      cours.intitule i,
		      cours.titulaires t
		    FROM cours
		    ORDER BY cours.faculte, cours.code";
	
	echo "<form name=\"bulkDeleteCourses\" action=\"".$_SERVER['PHP_SELF']."\" method=\"post\">";
	
	$listOfCourses = claro_sql_query($sql);	
	
	while ($course = mysql_fetch_array($listOfCourses))
	{ 
		if($course['f']!=$facOnce)
		{ 
			echo "\n\t<hr noshade size=\"1\">\n\t\t<font color=\"navy\">".$course['f']."</font>";
		}
		$facOnce=$course['f'];
		if($course['k']!=$codeOnce)
		{ 
			echo "<br>\n\t<input type=\"checkbox\" name=\"coursToDelete[]\" value=\"".$course['k']."\"> ".$course['c']." ".$course['i']." ".$course['t'];
		}
		$codeOnce=$course['k'];
	  // echo "$course['n']<br>";
	
		//echo "<input type=\"checkbox\" name=\"coursToDelete[]\" value=\"".$contenu."\">".$contenu."";
	}
	
	echo "\n\t<input type=\"submit\" name=\"submit\" value=\"Ok\" >
\n</form>";
	include "barre.inc.php"; 
}
else
{
	echo $lang_no_access_here;
}

include($includePath."/claro_init_footer.inc.php");
?>
