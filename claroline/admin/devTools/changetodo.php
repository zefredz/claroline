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
This page is target of todo admin page.
They kill, change, hide,... todo entry (after confirmation)
*/

$lang_no_access_here ="No access ";
$langFile = "todo";
include('../../inc/claro_init_global.inc.php');
$nameTools = $langTodo;
$interbredcrump[]= array ("url"=>"index.php", "name"=> $langAdmin);

$htmlHeadXtra[] = "<style type=\"text/css\">
<!--
	.listtodo {	font : lighter normal x-small monospace;}
	.runningTask {	background-color : #FDF5E6;	border : thin groove Black;	font-family : monospace; font-size : smaller;}
--></style>";
@include("./checkIfHtAccessIsPresent.php");

$is_allowedToEdit 	= $is_platformAdmin || $PHP_AUTH_USER;
if ($is_allowedToEdit)
{
	include($includePath.'/claro_init_header.inc.php');
	claro_disp_tool_title(
	array(
	'mainTitle'=>$nameTools,
	'subTitle'=>$PHP_AUTH_USER." - ".$siteName." - ".$clarolineVersion
	)
	);
	claro_disp_msg_arr($controlMsg);



/* *********************** */
/* Traitement des "actions" */
/**
 * Suppression
 * update
 * hide

 * dans les 3 cas -> on affiche et on exécute une requete SQL

/* *********************** */
	if ($HTTP_POST_VARS["action"]=="update")
	{
		echo "
			<ul>";
		while (list($key,$nom) = each($HTTP_POST_VARS))
		{
			echo "
				<LI>$key : $nom // ";
					print_r($nom);
			echo "
			</LI>";
		}
		echo "
			</ul>";

		if ($killOldName=="Ok")
		{
			if (substr($toDocontenu, 0, 17) == "<font color=navy>")
			{
				$content = trim(substr($toDocontenu, strpos($toDocontenu, "</font > : ")+15));
			}
		}
		$sqlUpdate = "Update todo set "
		   ." author = '".$toDoauteur."', "
		   ." email = '".$toDoemail."', "
		   ." target = '".$toDocible."', "
		   ." type  = '".$toDotype."', "
		   ." status  = '".$toDostatut."', "
		   ." priority  = '".$toDopriority."', "
		   ." content  = '".$toDocontenu."', "
		   ." assignTo  = '".$toDoassignTo."' "
		   ." where id ='".$toDoidToDo."'";

		echo "
			<DIV class='runningTask'>
				<strong>
					SQL code to execute for update
				</strong> : 
				<br>
				<em>
					".$sqlUpdate."
				</em>
			</DIV>";
		mysql_query($sqlUpdate);
	}
################################################
## Fin de l'éventuelle traitement des actions ##
################################################

	echo "
<FORM method=\"POST\" action=\"".$PHP_SELF."\">";
	if ($modif)
	{
		echo "
			<H1>
				Modification of proposition n°".$modif."
			</H1>
			<TABLE>";
		$selectToDo = "Select * from todo where id= '".$modif."';";      
		$resToDo = mysql_query($selectToDo);
		$todo = mysql_fetch_array($resToDo);
		if ($todo[author]=="")
		{ 
			if (substr($todo[content], 0, 17) == "<font color=navy>")
			{ 
				$todo[author] = trim(substr($todo[content], 17, strpos($todo[content], "</font > : ")-17));
				$chkBoxKillOldName ="Delete the name in the content? : <input type=\"checkbox\" name=\"killOldName\" value=\"Ok\">";
			}
		}
	
	//  $todo = mysql_fetch_row ($todo);
		$lesChamps = array("author","email","target","type","status","priority","content","assignTo");
		$i =0;
		while (list(,$nom) = each($lesChamps)) 
		{ 
			if($i%2==0) 
			{
				echo "<tr bgcolor=\"$colorMedium\" class=\"alternativeBgLight\">";
			}
			elseif ($i%2==1)
			{
				echo "<tr bgcolor=\"$colorLight\" class=\"alternativeBgDark\">";
			}
   ?>
   						<td>
							[<a href="todo.php?so<?=$nom?>=<?=addslashes($todo[$nom])?>">same <?=$nom?></A>] 
						</TD>
						<TD>
							<?=$todo[$nom]?>
						</TD>
						<TD> 
<?php 
			if ($nom !="content")
			{
				if ($nom =="assignTo")
				{ 
					echo selectbox("idUser");
				}
				else
				{ ?>  
						<input type="text" size="50" name="toDo<?=$nom?>" value="<?=$todo[$nom]?>"><?
				} 
			} 
			else
			{ ?>  
						<textarea cols="50" rows="5" name="toDo<?=$nom?>"><?=$todo[$nom]?></textarea><?
			} 
			echo "
					</TD>
				</TR>";
		}
		echo "
			</TABLE>
			".$chkBoxKillOldName; ?>
			<input type="hidden" name="toDoidToDo" value="<?=$todo["id"]?>">
			<input type="submit" name="action" value="update">
</FORM>
 			<br>
 
<?
		if (!@include($includePath."/../lang/".$languageInterface."/tableTypesTodo.inc.html"))
		{
			 include($includePath."/../lang/english/tableTypesTodo.inc.html");
		}


	}
	elseif ($supprimer) // if ($modifier) {...}
	{
		if ($confKill==1)
	    {
	?>

			Removal of proposal n° <?= $supprimer;?>
			<br>
	<?php
			$sqlDeleteToDo ="Delete from todo where id= '".$supprimer."';";
		    mysql_query($sqlDeleteToDo);
	   ?>
	   		done.<br>
			<a href="./todo.php">Back to Todo list</a>
<?php 
		} 
		else
		{ 
?>
			Confirm removal of proposal n° <?= $supprimer;?>
			<br> 
			<div align="center">
				[<a href="?supprimer=<?= $supprimer;?>&confKill=1">YES</A>]
				[<a href="todo.php">NO</A>]
			</div>  
<?
		}
	} 
	elseif 	($masquer) //if ($modifier) {...} elseif ($supprimer) {...}
	{
		echo "proposal n°".$masquer." will now be invisible for users";
		$sqlUpdate = "Update todo set showToUsers  = 'NO' "
	                  ." where id ='".$masquer."'";
		echo "<DIV class='runningTask'><strong>SQL to execute</strong> : <br><em>".$sqlUpdate."</em>";
		@mysql_query($sqlUpdate) or die("error");
		echo "</DIV>";
	} 
	elseif ($afficher) //if ($modifier) {...} elseif ($supprimer) {...}
	{
		echo "proposal n°".$afficher." will now be visible for users";
		$sqlUpdate = "Update todo set showToUsers  = 'YES' "
	                    ." where id ='".$afficher."'";
		echo "
			<DIV class='runningTask'>
				<strong>
					Sql to run
				</strong> : 
				<br>
				<em>
					".$sqlUpdate."
				</em>";
		@mysql_query($sqlUpdate) or die("error");
		echo "
			</DIV>";
	} 
?>
<div align="center">
	<a href="todo.php">Back to Todo Admin</a>
</div>

<?php
			include $rootAdminSys."/barre.inc.php";
}
else
{
	echo $lang_no_access_here;
}

include($includePath."/claro_init_footer.inc.php");


function selectbox($champs)
{
	$sqlSelect = "
SELECT idUser as id, user.username as nom
	FROM admin
	LEFT Join user
		on user.user_id=admin.idUser" ;
	// echo $sqlSelect;
	$lesChamps = mysql_query($sqlSelect);
	$leSB = "
			<select name='toDoassignTo'>";
	$leSB .=  "
				<option value=\"0\" >
					- - -
				</option>";
	while ($leChamps = mysql_fetch_array($lesChamps))
	{
		if ($leChamps[nom]!="")
			$leSB .=  "
				<option value=\"".$leChamps[id]."\" >
					".$leChamps[nom]."
				</option>\n";
	}
	$leSB .=  "
			</select>";
	return $leSB;
}

?>