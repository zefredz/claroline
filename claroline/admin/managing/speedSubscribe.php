<?php # $Id$
//----------------------------------------------------------------------
// CLAROLINE
//----------------------------------------------------------------------
// Copyright (c) 2001-2003 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see 'credits' file
//----------------------------------------------------------------------
/*

What's do the page
They permit  to an ADMIN to subscribe to a Cursus, very fast.
usefull  for urgency testing
J'ai  fais ce script  pour permettre de s'incrire très rapidement à un cour en cas d'urgence.
*/
$lang_speed_subscribe = "fast subscribe";
$lang_no_access_here ="Pas d'accès ";
$langFile = "speedSubscribe";
require '../inc/claro_init_global.inc.php';
$nameTools = $lang_speed_subscribe;
$interbredcrump[]= array ("url"=>"index.php", "name"=> $langAdmin);
@include("./checkIfHtAccessIsPresent.php");
include($includePath."/claro_init_header.inc.php");
//$TABLEAGENDA 		= $_course["dbName"]."`.`agenda";
$is_allowedToAdmin 	= $is_platformAdmin || $PHP_AUTH_USER;
if ($is_allowedToAdmin)
{
claro_disp_tool_title(
	array(
	'mainTitle'=>$nameTools,
	'subTitle'=>$langUpgrade." - ".$PHP_AUTH_USER." - ".$siteName." - ".$clarolineVersion." - ".$dateNow
	)
	);

if ($HTTP_SESSION_VARS["uid"] =="" || !isset($HTTP_SESSION_VARS["uid"]))
	exit ("$langAuthRequest
<HR>
 				<form action = \"/index.php?mon_icampus=yes\" method='post'>
					<p>
						<font size=1 face=\"arial, helvetica\">
							UserName
						</font size>
						<br>
						<input type=\"text\" name=\"uname\" size=\"10\">
						<br>
						<font size=1 face=\"arial, helvetica\">
							Pass
						</font size>
						<br>
						<input type=\"password\" name=\"pass\" size=\"10\"><br>
						<input type=\"submit\" value=\"Enter\" name=\"submit\">
					</p>
				</form>
				$langAndGoBack;
 ");

$db = mysql_connect("$mysqlServer", "$mysqlUser", "$mysqlPassword");
mysql_select_db("$mainDbName",$db);


 include "barre.inc.php"; 


#################################
## Y a-t-il des info à traiter ##
#################################
if ($submit =="Ok")
{
	?>
	<h3>
		<?php echo $lang_subscribe_processing ?>
	</h3>
	pour
	<?php echo $prenom." ".$nom." // ID :".$uid; ?>
	<br>
	<?

	$lesStatutDeCours["1"] = "Etudiant/student";
	$lesStatutDeCours["5"] = "Enseignant/professor";
	while (list($key,$contenu)= each($course))
	{
		echo "<HR>";
		echo "[".$key."]";
		echo $contenu;
		$sql = "INSERT INTO `cours_user` (`code_cours`, `user_id`, `statut`, `role`) VALUES ('$contenu', '$uid', '1', 'Test - iCampus')";
		$res =mysql_query($sql);
		if ( $res )
			echo "<BR> result : [".mysql_query($sql)."]<BR>";	
		elseif (mysql_errno()==1062)
		{
			$sql2 = "Select `statut` sCours, `role` rCours From `cours_user` Where `code_cours` = '$contenu' And `user_id`= '$uid'";
			$res2 =mysql_query($sql2);
			$lelienUserCours = mysql_fetch_array($res2);
			echo "
	<FONT color=\"red\">
		!!!
		<strong>
			$langAlreadySubscribe
		</strong>
		!!!
	</FONT>
	comme ".$lesStatutDeCours[$lelienUserCours["sCours"]]." // ".$lelienUserCours["rCours"]."
	<BR>";
		}
	}
	echo $langSubscribeDone."
	<br>
	<A Href=\"../..\">
		- ".$langBack." -
	</A>
	<HR color=\"blue\" noshade size=4>";
	include "barre.inc.php";
}
?>
<form name="speedSub" action="<?= $PHP_SELF ?>" method="post">
<font size=2 face='arial, helvetica'>
<?
$sql = "SELECT 
			cours.faculte f, 
			cours.code k, 
			cours.fake_code c,
			cours.intitule i,
			cours.titulaires t
		FROM cours
		ORDER BY cours.faculte, cours.code";

$result=mysql_query($sql);	

while ($mycours = mysql_fetch_array($result)) 
{ 
	if($mycours[f]!=$facOnce)
	{ 
		echo "
	<hr noshade size=1>
	<font color=navy>
		$mycours[f]
	</font color>
	<br>";
	}
	$facOnce=$mycours[f];
	if($mycours[k]!=$codeOnce)
	{ 
		echo "
	<input type=checkbox name=course[] value=$mycours[k]>
	$mycours[c] $mycours[i] $mycours[t]
	<br>";
	}
	$codeOnce=$mycours[k];
//	echo "$mycours[n]<br>";
}
?>

<br>
	<input type="submit" name="submit" value="Ok" >
</form>
<?php	include "barre.inc.php"; 
}
else
{
	echo $lang_no_access_here;
}

@include($includePath."/claro_init_footer.inc.php");
?>
