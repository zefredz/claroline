<?php  session_start();
exit();
include('../include/config.php');
include('../include/lang.php');

//$nameTools = $langTodo;
//$interbredcrump[]= array ("url"=>"index.php", "name"=> $langAdmin);


/*********************************************************************************
                    MODIFY A COURSE DATABASE AFTER UPGRADE
**********************************************************************************

AUTHORS : Thomas De Praetere, Hugues Peeters, IPM, UCL, December 2001
************************************************************************/
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">

<html>
<head>
	<title>
			<?php echo "$nameTools - $langAdmin - $siteName - $clarolineVersion"; ?>

	</title>
</head>
<body bgcolor=white>
<?php
echo "Modifier la table 'cours_new' pour y introduire les titulaires<br>

$db = mysql_connect("$mysqlServer", "$mysqlUser", "$mysqlPassword");
mysql_select_db("$repertoire",$db);

mysql_query("SELECT nom, prenom FROM user, user_cours, cours
mysql_query("INSERT INTO accueil VALUES (
	'NULL',
	'Utilisateurs',
	'../claroline/user/user.php',
	'membres.png',
	'0',
	'0',
	'pastillegris.png')");
	echo "La talble accueil de la base $repertoire a été modifiée";
}
?>
</body></html>