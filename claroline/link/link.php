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

$langFile = "link";

include("../inc/claro_init_global.inc.php");


$tbl_link = $_course['dbNameGlu']."link";
$is_allowedToEdit = $is_courseAdmin;

if($is_allowedToEdit)
{
	if($buttonCancel)
	{
		unset($id);
		unset($url);
		unset($titre);
		unset($description);
	}
	elseif($submitLink)
	{
		$url=trim($url);
		$titre=trim($titre);
		$description=trim($description);

		if(empty($url))
		{
			$controlMsg["error"][] = $langGiveURL;
		}
		else
		{
			if(empty($titre))
			{
				$titre=$url;
			}

			// If no ID, Add else modify ...

                        //check for "http://", if the user forgot "http://" or "ftp://" or ...
                        // the link will not be correct
                        if( !ereg( "://",$url ) )
                        {
                            // add "http://" as default protocol for url
                            $url = "http://".$url;
                        }

                        if($id)
			{
				$sql = "UPDATE `".$tbl_link."`
						SET url='$url', titre='$titre', description='$description'
						WHERE id='$id'";

				unset($id);
				unset($url);
				unset($titre);
				unset($description);
				$controlMsg["success"][]= $langLinkUpdated;
	  		}
			else
			{
				$sql = "INSERT INTO `".$tbl_link."`
						(url,titre,description)
						VALUES ('$url','$titre','$description')";

				unset($url);
				unset($titre);
				unset($description);
				$controlMsg["success"][]= $langLinkAdded;
			}

			mysql_query($sql);


		}
	}
	elseif($delete)
	{
		if($id)
		{
			$sql="DELETE FROM `".$tbl_link."` WHERE id='$id'";
			mysql_query($sql);
			$controlMsg["success"][]= $langLink_Deleted;

			unset($id);
		}
		else
		{
			$sql="DELETE FROM `".$tbl_link."`";
			mysql_query($sql);
			$controlMsg["success"][]= $langAll_Link_Deleted;
		}
	}
}

$nameTools = $langLinks;

include($includePath."/claro_init_header.inc.php");
if ( ! $is_courseAllowed)
	claro_disp_auth_form();
//stats
include($includePath."/lib/events.lib.inc.php");
event_access_tool($nameTools);

claro_disp_tool_title($nameTools);
claro_disp_msg_arr($controlMsg);
if($is_allowedToEdit)
{
	if ($id)
	{
		// Moify (then choose) RECORD
		$sql = "SELECT * FROM `".$tbl_link."` WHERE id=$id ORDER BY titre";
		$result = mysql_query($sql);
		if($myrow  = mysql_fetch_array($result))
		{
			$url         = $myrow[1];
			$titre       = $myrow[2];
			$description = $myrow[3];

			echo	"<h4>",
					$langModifyLink,
					"</h4>";
		}
		else
		{
			unset($id);

			echo	"<h4>",
					$langAddLink,
					"</h4>";
		}
	}
	else
	{
		echo	"<h4>",
				$langAddLink,
				"</h4>";
	}
?>

<form method="post" action="<?php echo $PHP_SELF; ?>">

<input type="hidden" name="id" value="<?php echo $id; ?>">

<table>

<tr>
<td align="right"><label for="urlfield">URL</label> : </td>
<td><input type="TEXT" id="urlfield" name="url" value="<?php echo htmlentities($url); ?>" size="55"></td>
</tr>

<tr>
<td align="right"><label for="urlLabel"><?php echo $langLinkName; ?></label> : </td>
<td><input type="TEXT" id="urlLabel" name="titre" value="<?php echo htmlspecialchars($titre); ?>" size="55"></td>

</tr>

<tr>
<td align="right" valign="top"><label for="urlDescription"><?php echo $langDescription; ?></label> : </td>
<td><textarea wrap="physical" rows="3" cols="50"  id="urlDescription" name="description"><?php echo htmlspecialchars($description); ?></textarea></td>
</tr>

<tr>
<td>
</td>

<?php
	if($id)
	{
?>

<td><input type="submit" name="buttonCancel" value="<?php echo $langCancel; ?>">
&nbsp;&nbsp;<input type="Submit" name="submitLink" value="<?php echo $langOk; ?>"></td>

<?php
	}
	else
	{
?>

<td><input type="Submit" name="submitLink" value="<?php echo $langOk; ?>"></td>

<?php
	}
?>

</tr>

</table>
</form>

<?php
}

$sqlLinks = "SELECT * FROM `".$tbl_link."` ORDER BY titre";
$result = mysql_query($sqlLinks);

if($is_allowedToEdit && mysql_num_rows($result))
{
	echo '<hr noshade="noshade" size="1">';

	echo	"<a href=\"".$PHP_SELF."?delete=1\" onclick=\"javascript:if(!confirm('".addslashes(htmlspecialchars($langDelList." ".$langConfirmYourChoice))."')) return false;\">
			".$langDelList."</a><br><br>";
}

$i=0;

echo "<table border=\"0\">";

while ($myrow = mysql_fetch_array($result))
{
	echo	"<DIV>",

			"<a href=\"$myrow[1]\" target=\"_new\">",
			"<img src=\"../../claroline/img/liens.png\" border=\"0\" alt=\"".$langLinks."\">",
			//"<a href=\"$myrow[1]#" target=\"_new\">",$myrow[2],"</a>",
                        "<a href=\"link_goto.php?link_id=",$myrow[0],"&link_url=",$myrow[1],"\">",$myrow[2],"</a>\n",
			"<br>",$myrow[3],"";

	if ($is_allowedToEdit)
	{
		echo	"<br>",
				"<a href=\"$PHP_SELF?id=$myrow[0]\">",
				"<img src=\"../img/edit.gif\" border=\"0\" alt=\"",$langModify,"\">",
				"</a>",

				" <a href=\"",$PHP_SELF,"?id=",$myrow[0],"&delete=1\" onclick=\"javascript:if(!confirm('".addslashes(htmlspecialchars($langDelete." ".$langConfirmYourChoice))."')) return false;\">",
				"<img src=\"../img/delete.gif\" border=\"0\" alt=\"".$langDelete."\">",
				"</a>";
	}

	echo	"</DIV>";

	$i++;
}

echo "</table>";

//////////////////////////////////////////////////////////////////////////////

include($includePath."/claro_init_footer.inc.php");
?>
