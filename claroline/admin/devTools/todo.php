<?php # $Id$
/*
      +----------------------------------------------------------------------+
      | Authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
      |          Hugues Peeters    <peeters@ipm.ucl.ac.be>                   |
      |          Christophe Gesché <gesche@ipm.ucl.ac.be>                    |
      +----------------------------------------------------------------------+
 */

$langPostOnClaroForum = "Forward this to the Claroline Development team forum";
$langForward = "Forward";

$langFile = "todo";
include('../../inc/claro_init_global.inc.php');
$nameTools = $langAdministrationTools;
$nameTools = $langTodo;
include($includePath."/lib/text.lib.php");
$htmlHeadXtra[] = "
	<style type=\"text/css\">
		BODY,H1,H2,H3,H4,H5,H6,P,BLOCQUOTE,TD,OL,UL {	font-family: Arial, Helvetica, sans-serif; }
    	body, p, blockquote, input, td {font-family: sans-serif;}
		.rep .reponse {	font : normal small-caps lighter x-small \"Courier New\", Courier, monospace; }
		.listtodo {	font : lighter normal x-small \"Courier New\", Courier, monospace;}
	</STYLE>";

$langAdmin = "Technical admin";
$interbredcrump[]= array ("url"=>"index.php", "name"=> $langAdmin);

/****
 This array containt  Color code  to show the  priority
 give to a todo
****/

$bgcolor =
array( "0"=>"#bbCCFF",
       "1"=>"#bbDDBB",
	   "2"=>"#ccEE77",
	   "3"=>"#ccFF33",
	   "4"=>"#ddee00",
	   "5"=>"#ddCC11",
	   "6"=>"#eeAA22",
	   "7"=>"#ee9933",
	   "8"=>"#FF7744",
	   "9"=>"#FF5555"
);


######### Build a bar memento with code and correcponding  color ########
reset ($bgcolor);
$mementoColor = "
<table>
	<TR>";
while(list($key,$val)=each($bgcolor))
{
	$mementoColor .= "
		<td bgcolor='$val'> $key </td>";
}
$mementoColor .=  "
	</TR>
</table>";

include($includePath.'/claro_init_header.inc.php');
claro_disp_tool_title($nameTools);
claro_disp_msg_arr($controlMsg);

#####################################################################
// CHECK TABLE VERSION
#####################################################################

$fields = mysql_list_fields($mainDbName, "todo", $db);
$columns = mysql_num_fields($fields);
if ($columns == 3)
{
	echo "
			<font color=\"red\">
				INVALID TABLE FORMAT (TODO)
				<br>
		        You must upgrade to the  new  format
			</font>
			<br>
			There is actually only $columns fields
			<UL>";
	for ($i = 0; $i < $columns; $i++)
	{
		echo "
				<LI>
					".mysql_field_name($fields, $i)."
				</li>";
	}
	echo "
			</UL>
			<br>
        	You can  do  it <a href=\"/claroline/admin\" >here</a> ";
	exit("<HR>");
}
#####################################################################
// END OF CHECK TABLE VERSION
#####################################################################


if (!$id) 
{
	/*     ---- PAS DE FORM D'AJOUT ----
		echo "<font face=\"arial, helvetica\" size=\"2\"><h4>$langPropositions $siteName</h4>";
     
	// FORM **************************************************************

		echo "<input type=\"hidden\" name=\"id\" value=\"$id\">
			<form method=\"post\" action=\"$PHP_SELF\"><font face=\"arial, helvetica\" size=2>$langYourProp<br>";
		echo "<textarea wrap=physical rows=10 cols=60 name=contenu>$contenu</textarea>";
		echo "<br><input type=\"Submit\" name=\"submit\" value=\"$langOk\"></form><br>";
    */
		
		
    $laSelection = "SELECT * FROM todo ";
    if (isset($soassignTo) && $soassignTo!="")
    	$laSelection .=" LEFT JOIN user on assignTo = user_id ";
    $laSelection .="where 1=1 ";
    if (isset($socible)    && $socible!="")
    	$laSelection .=" and cible = '$socible'";
    if (isset($sopriority) && $sopriority!="")
    	$laSelection .=" and priority = '$sopriority'";
    if (isset($sotype)     && $sotype!="")
    	$laSelection .=" and type = '$sotype'";
    if (isset($sostatut)   && $sostatut!="")
    	$laSelection .=" and statut = '$sostatut'";
    if (isset($soassignTo) && $soassignTo!="")
    	$laSelection .=" and assignTo = '$soassignTo'";
    if (isset($soshowToUsers) && $soshowToUsers!="")
    	$laSelection .=" and showToUsers = '$soshowToUsers'";
    if (isset($order) && $order!="" && $order!=" ")
	{ 
		$laSelection .=" ORDER BY ".$order.";";
	} 
	else
	{
		$laSelection .=" ORDER BY temps DESC;";
	}
	echo $laSelection;
	echo "
<table  width=\"100%\" cellpadding=\"4\" cellspacing=\"2\" border=\"0\" class=\"listtodo\">";
		// print the list

		function selectbox($champs)
		{
			$sqlSelect = "SELECT DISTINCT ".$champs." as nom FROM todo;" ;
          	if ($champs=="assignTo")
				$sqlSelect = "SELECT DISTINCT assignTo as nom , username as uName FROM todo LEFT JOIN user on assignTo = user_id;" ;
			$lesChamps = mysql_query($sqlSelect);
			$leSB = "
			<select name='so".$champs."'>";
			$leSB .=  "
				<option value=\"\" >
					-no-
				</option>";
			while ($leChamps = mysql_fetch_array($lesChamps))
			{
				if ($leChamps[nom]!="")
					$leSB .=  "
				<option value=\"".$leChamps[nom]."\" >
					".$leChamps[nom]."
				</option>";
				if ($champs=="assignTo")
					$leSB .=  "
				<option value=\"".$leChamps[nom]."\" >
					".$leChamps[uName]."
				</option>";
			}
			$leSB .=  "
			</select>";
			return $leSB;
		}

        function sortBox($champs)
// Cette fonction construit les liens  pour demander un  tri sur le champs 
		{ 
			$leSB .=  "
			<input type='radio' name='order' value=' '>
			natural
			<br>
			<input type='radio' name='order' value='".$champs."'>
			asc
			<br>
			<input type='radio' name='order' value='".$champs." DESC'>
			desc
			<br>
			<br>";

			return $leSB;
		}
?>


	<TR>
		<TD>
<form action="<?= $PHP_SELF ?>">
	<tr bgcolor="#ffffcc">
		<td bgcolor="#c0c0c0"><strong>Filter</strong></TD>
		<td>Target<br><?php		echo selectbox("cible") 		?></TD>
		<td>Priority<br><?php	echo selectbox("priority")		?></TD>
		<td>Status<br><?php		echo selectbox("statut")		?></TD>
		<td>Type<br><?php		echo selectbox("type")			?></TD>
		<td>Assigned to<br><?php	echo selectbox("assignTo")		?></TD>
		<td>Visible<br><?php	echo selectbox("showToUsers")	?></TD>
	</tr>
	<tr bgcolor="#99ff99">
		<td bgcolor="#c0c0c0"><strong>Order</strong></TD>
		<td><?php echo sortBox("cible") 		?></TD>
		<td><?php echo sortBox("priority")		?></TD>
		<td><?php echo sortBox("statut")		?></TD>
		<td><?php echo sortBox("type")			?></TD>
		<td><?php echo sortBox("assignTo")		?></TD>
		<td><?php echo sortBox("showToUsers")	?></TD>
	</tr>
	<TR>
		<TD colspan="6">
			<input type='submit' value='Submit'>
		</TD>
	</TR>
</form>
	<TR>
		<TD colspan="8">
			<? include $rootAdminSys."/barre.inc.php"; ?>
        	<?php  echo $mementoColor; ?>
       	</TD>
	</TR>
	<TR>
		<TD colspan=8 align="right">
			<?php echo $langForward," = ",$langPostOnClaroForum ?>
		</TD>
	</TR>
<?php
		$result = mysql_query($laSelection ,$db);
		while ($leTodo = mysql_fetch_array($result))
		{
		
			$content = $leTodo[contenu];		
			$content = nl2br($content);
			$content = make_clickable($content);

			if ($leTodo[auteur]=="")
		  { if (substr($leTodo[contenu], 0, 17) == "<font color=navy>")
            { $leTodo[auteur] = trim(substr($leTodo[contenu], 17, strpos($leTodo[contenu], "</font > : ")-17));
            }
		  }
		  $bgc = $bgcolor["$leTodo[priority]"];
          echo "\n<tr bgcolor=".$bgc.">";
          echo "\n\t<td><a href='mailto:".$leTodo[email]."'>".$leTodo[auteur]."</a></TD>";
          echo "\n\t<td>[<a href='?socible=".$leTodo[cible]."'>-</A>] ".$leTodo[cible]."</TD>";
          echo "\n\t<td>[<a href='?sopriority=".$leTodo[priority]."'>-</A>] ".$leTodo[priority]."</TD>";
          echo "\n\t<td>[<a href='?sostatut=".$leTodo[statut]."'>-</A>] ".$leTodo[statut]."</TD>";
          echo "\n\t<td>[<a href='?sotype=".$leTodo[type]."'>-</A>] ".$leTodo[type]."</TD>";
          echo "\n\t<td>[<a href='?soassignTo=".$leTodo[assignTo]."'>-</A>] ".$leTodo[assignTo]." ".$leTodo[username]."</TD>";
          echo "\n\t<td>[<a href='?soshowToUsers=".$leTodo[showToUsers]."'>-</A>] ".$leTodo[showToUsers]."</TD>";
          echo "\n\t<td>",$langForward,"</TD>";
          echo "\n</tr>\n\t<tr bgcolor=$bgc>";
          echo "\n\t<td colspan=6><font size=1 face=\"arial, helvetica\" color=\"navy\">".$langPubl."&nbsp;: ".$leTodo[temps]."<font size=2 color=black>
				<br>".$leTodo[id].". ".$content."";


				 //printf("<br><font size=\"1\">|&nbsp;<a href=\"%s?id=%s\">$langModify</a>&nbsp;|", $PHP_SELF, $leTodo[0]);
				 //printf("<a href=\"%s?id=%s&delete=yes\">$langDelete</a>&nbsp;|</font size></td></tr></table>", $PHP_SELF, $leTodo[0]);
          echo "\n\t</td>\n\t<td>";
          echo "\n\t\t[<a href='changetodo.php?modif=".$leTodo[id]."' 	>Edit</a>]<br>";
          echo "\n\t\t[<a href='changetodo.php?supprimer=".$leTodo[id]."' >Delete</a>]<br>";

          if ($leTodo[showToUsers]=="YES")
          {
          		echo "\n\t\t[<a href='changetodo.php?masquer=".$leTodo[id]."' 	>Hide</a>]<br>";
          } else
          {
          	 "\n\t\t[<a href='changetodo.php?afficher=".$leTodo[id]."' 	>Afficher</a>]<br>";
		  }
		  echo "\n\t</td>\n\t<td>";
$postSubject = "POST FROM ".$siteName." ";
$postContent =  $content."\n ---------------------------- \n".
$leTodo[username]." - ".
$leTodo[email]." - ".
$leTodo[auteur]." - ".$rootWeb." - v:".$clarolineVersion."-";
?>

<form action="http://www.claroline.net/forum/posting.php" method="post" name="post" >
	<INPUT name="message" Value="<?php echo strip_tags($postContent) ?>"  type="hidden">
	<input type="Hidden" name="subject" value="<?php echo strip_tags($postSubject) ?>" />
	<input type="hidden" name="disable_html" value="1" />
	<input type="hidden" name="topictype" value="0" />
	<input type="hidden" name="mode" value="newtopic" />
	<input type="hidden" name="f" value="31" />
	<input type="submit" name="post" value="POST" />
</FORM>
<?php
          echo "\n\t</td>\n</tr>";
          if (!!!(++$lines%5))
		  {?>
				<TR>
					<TD colspan=8 align="right">
			 			 <?php echo $langForward," = ",$langPostOnClaroForum ?>
					</TD>
				</TR>
				<TR>
		  			<TD>
						<?php echo $mementoColor ?>
					</TD>
					<TD colspan="7">
						<?php include $rootAdminSys."/barre.inc.php"; ?>
					</TD>
				</TR>
				<tr bgcolor="#ffffcc">
					<td ></TD>
					<td>Target</TD>
					<td>Priority</TD>
					<td>Status</TD>
					<td>Type</TD>
					<td>Assigned to</TD>
					<td>Visible</TD>
				</tr>
<?
		  }
		}    // while
		echo "
			</table>";

echo $mementoColor;
#######################################################################
	}	// if ! id
###############################################################################
echo "
		</td>
	</tr>
	<tr>
		<td colspan=\"2\">
			<br>
		</TD>";
include $rootAdminSys."/barre.inc.php";

?>
	</TR>
</TABLE>
</center>
</body>
</html>
