<?php // | $Id$ |
/*
  +----------------------------------------------------------------------+
  | CLAROLINE version 1.3.2 $Revision$                      	     |
  +----------------------------------------------------------------------+
  | Copyright (c) 2001, 2003 Universite catholique de Louvain (UCL)      |
  +----------------------------------------------------------------------+
  | Authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
  |          Hugues Peeters    <peeters@ipm.ucl.ac.be>                   |
  |          Christophe Gesché <gesche@ipm.ucl.ac.be>                    |
  +----------------------------------------------------------------------+

// This  tool must be rewrited in mvc.

 */

$langFile = "todo";
include("../inc/claro_init_global.inc.php"); 
include($includePath."/lib/text.lib.php"); 
$tbl_todo 			= $mainDbName."`.`todo";
$tbl_user 			= $mainDbName."`.`user";
$is_allowedToEdit 	= $is_courseAdmin;


$nameTools = $langTodo;
$htmlHeadXtra[] = "<style type=\"text/css\">
.listtodo {	font : lighter normal x-small \"Courier New\", Courier, monospace;}
.alternativeBgLight { background-color: ".$color1."; }
.alternativeBgDark { background-color: 	".$color2."; }
</style>";



// on recup les admin dans une table
// Select admin in an array
$result = mysql_query("SELECT DISTINCT user_ID as id,  user.username as nom FROM `".$tbl_todo."` todo LEFT JOIN `".$tbl_user."` user on AssignTo = user_id");
{
	while ($lesAdmins = mysql_fetch_array($result))
	{
		$admin[$lesAdmins["id"]] = $lesAdmins["nom"];
	}
}


include($includePath."/claro_init_header.inc.php");
claro_disp_tool_title($nameTools);
//claro_disp_msg_arr($controlMsg);


// si  sumit alors il y a des données à ajouter
if (isset($HTTP_POST_VARS["submitTask"]))
{
// Adding  if  old  format
	$oldContent="<font color=\"navy\">$prenom $nom</font > : $contenu";
	$sql = "INSERT INTO `".$tbl_todo."` (contenu, temps) VALUES ('$oldContent', NOW())";
	$idResult = mysql_query($sql);
	//echo $sql;
// adding info new format
	$idTodo = mysql_insert_id();
	$auteur = $prenom." ".$nom;
	$sql = "Update IGNORE `".$tbl_todo."` todo SET"
		   ." auteur = '".$auteur."', "
		   ." contenu  = '".$contenu."', "
		   ." temps = NOW(),"
		   ." email = '".$email."', "
		   ." cible = '".$cible."', "
		   ." type  = '".$type."', "
		   ." statut  = 'NEW', "
		   ." priority  = '0', "
		   ." assignTo  = '0'
		    where id = '".$idTodo."';";
	$idResult = mysql_query($sql);
	echo "
	$langPropAd
	<br><br>
	<a href=\"$PHP_SELF\">$langBackList</a>
	<br>";
}
else
{
	######################### Si pas de SUBMIT ########################
	if (!$id)
	{
		echo "
	<h3>
		$langPropositions $siteName
	</h3>
	<input type=\"hidden\" name=\"id\" value=\"$id\">
	<!-- Adding TODO -->
<form method=\"post\" action=\"$PHP_SELF\">
		$langYourProp
		<br>";
	    if (isset($toolsName))
			echo "
		<input type=\"hidden\" name=\"cible\" value=\"".$toolsName."\">";
		if (isset($typeTodo))
			echo "
		<input type=\"hidden\" name=\"typeTodo\" value=\"".$typeTodo."\">";
		if ($_uid)
		{
			$resUser	= mysql_query("SELECT nom, prenom, email FROM  `".$tbl_user."` user WHERE user_id='$_uid'");
			$dataUser 	= mysql_fetch_array($resUser);
			$auteur		= $dataUser[prenom]." ".$dataUser[nom];
			$email		= $dataUser[email];
			echo "
		<input type=\"hidden\" name=\"_uid\" value=\"".$_uid."\">
		<input type=\"hidden\" name=\"auteur\" value=\"".$auteur."\">
		<input type=\"hidden\" name=\"email\" value=\"".$email."\">";
		}
			else
		{
			echo "
		name	:
		<input type=\"text\" name=\"auteur\" size=\"40\" maxlength=\"80\">
		<BR>
		email	:
		<input type=\"text\" name=\"email\" size=\"40\" maxlength=\"80\">
		<br>";
		}
		echo "
		<textarea wrap=\"physical\" rows=\"10\" cols=\"60\" name=\"contenu\">$contenu</textarea>
		<br>
		<input type=\"Submit\" name=\"submitTask\" value=\"$langOk\">
</form>
		<br>";
	}
	echo "
		<table width=\"90%\" cellpadding=\"4\" cellspacing=\"2\" border=\"0\">";
	// print the list
	//  Tout ce qui suis permet de travailler sur les  2 formats de table, 
	// quand l'ancien format sera abandonné,  if ( $leToDo[showToUsers] != "NO") pourra être supprimé et  on pourra utiliser un where dans le select
  
	//  $result = mysql_query("SELECT id, contenu, temps, auteur, email, statut FROM todo ORDER BY id DESC",$db);
	//  $result = mysql_query("SELECT * FROM todo Where showToUsers = 'YES' ORDER BY id DESC ",$db) ;
	$sqlTodo = "SELECT id, statut, contenu, showToUsers, email, auteur, assignTo, temps FROM  `".$tbl_todo."` ORDER BY id DESC ";
	$result = mysql_query($sqlTodo);
	$i=1;		
	while ($leToDo = mysql_fetch_array($result)) 
	{

		$content = $leToDo[contenu];		
		$content = claro_parse_user_text($content);
		$content = make_clickable($content);


		if ( $leToDo[showToUsers] != "NO")
		{
			if($i%2==0)
			{
				echo "<tr bgcolor=\"$color1\" class=\"alternativeBgLight\">";
			}
			elseif ($i%2==1)
			{
				echo "
			<tr bgcolor=\"$color2\" class=\"alternativeBgDark\">";
			}
			echo "
				<td>
					<font color=\"navy\"><small>
						$langPubl&nbsp;:
						".claro_format_locale_date($dateTimeFormatLong,strToTime($leToDo[temps]))."&nbsp;&nbsp;
						<a href=\"mailto:".$leToDo[email]."\">".$leToDo[auteur]."</a>";
			//les 3 echos suivants peuvent être vide -> ancien système
			if ($admin[$leToDo[assignTo]])
				echo "
					&nbsp;&nbsp;traité par ".$admin[$leToDo[assignTo]]."  ";
			echo "
					&nbsp;&nbsp;
					$leToDo[statut]
					<br>
					</small>
					<font color=\"black\">
						<br>
						$leToDo[id]. $content
					</font>";
			echo "
				</td>
			</tr>";
			$i++;
		}
	}    // while
	echo "
		</table>";
}	// if ! id
include($includePath."/claro_init_footer.inc.php");
?>
