<?php

die("deprecated");

 session_start();
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.3.0 $Revision$                             |
      +----------------------------------------------------------------------+
      | $Id$	             |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2002 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      | Authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
      |          Hugues Peeters    <peeters@ipm.ucl.ac.be>                   |
      |          Christophe Gesché <gesche@ipm.ucl.ac.be>                    |
      +----------------------------------------------------------------------+
 */
/**
 * Login
 */

$langUsername = "Nom d'utilisateur";
$langPass = "Mot de passe";

include('../include/config.php');
$db = mysql_connect("$mysqlServer", "$mysqlUser", "$mysqlPassword");
mysql_select_db("$mainDbName",$db);
@include("../lang/english/trad4all.inc.php");
@include("../lang/$language/trad4all.inc.php");
@header('Content-Type: text/html; charset='. $charset);
@include("../lang/english/login.inc.php");
@include("../lang/$language/login.inc.php");

unset($uid);
if(isset($submit) && $submit)
{ 
	$sqlLogin= "
SELECT
		user_id, nom, prenom, statut, email, iduser is_admin 	
	FROM user 
	LEFT JOIN admin 
		ON user.user_id = admin.iduser
	WHERE 
		password='".$HTTP_POST_VARS["password"]."'
		AND 
		username='".$HTTP_POST_VARS["uname"]."' ;"; 
	$result=mysql_query($sqlLogin,$db); 
	while ($myrow = mysql_fetch_array($result)) 
	{
		$uid		= $myrow["user_id"];
		$nom		= $myrow["nom"];
		$prenom		= $myrow["prenom"];
		$statut		= $myrow["statut"];
		$email		= $myrow["email"];
		$is_admin	= $myrow["is_admin"];
	}	// while 

	if (empty($uid))
	{ 
		$avertissement='
<small><font color="red" >
	'.$langInvalidId.'
</font></small>';
	}    // if empty uid 
	else 
	{ 
		$log='yes';
		session_register('uid');
		session_register('nom');
		session_register('prenom');
		session_register('email');
		session_register('statut');
		session_register('is_admin');
		mysql_query("INSERT INTO loginout (loginout.idLog, loginout.id_user, loginout.ip, loginout.when, loginout.action) 
			VALUES ('', '".$uid."', '".$REMOTE_ADDR."', NOW(), 'LOGIN')");

			
			// verifier si c un prof ou pas

		header("Location:".$HTTP_POST_VARS["backto"]);
	}
} //submit 

$nameTools = "login";
//$interbredcrump[]= array ("url"=>"inscription.php", "name"=> $langRegistration);

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>
<head>
<link rel="stylesheet" href="../css/default.css" type="text/css">

<title>
	<?php echo "$nameTools - $langRegistration - $siteName - $clarolineVersion"; ?>
</title>
</head>
<body bgcolor="white" dir="<?php echo $text_dir ?>">
<table border="0" align="center" cellpadding="0" cellspacing="0" width="<?php echo $mainInterfaceWidth?>">
	<tr>
		<td>
			<?php include('../include/claroline_header.php'); ?>
			
		</td>
	</TR>
	<tr>
		<td>
			<h4>
				<?php echo $langIdentification ?>
							
			</h4>
			<br>
		</td>
	</TR>
	<tr>
		<td>
<form action="login.php" method="post">
			<table cellpadding="3" cellspacing="0" border="0" width="100%">
				<tr>
					<td>
						<label for="uname"><?php echo $langUsername;?> : </label>
					</td>
					<td>
						<input type="text" name="uname">
					</td>
				</tr>
				<tr>
					<td>
						<label for="password"><?php echo $langPass;?> :< </label>
					</td>
					<td>
						<input type="password" id="password" name="password">
					</td>
				</tr>
				<tr>
					<td>
					  	&nbsp;
					</td>
					<td>
						<input type="submit" name="submit" value="<?php echo $langOk;?>" >
					</td>
				</tr>
				<tr>
					<td colspan="2" >
						<input type="hidden" name="backto" value="<?php echo $HTTP_REFERER;?>" >
					</td>
				</tr>
			</table>
</form>
		</td>
	</tr>
</table>
</body>
</html>
