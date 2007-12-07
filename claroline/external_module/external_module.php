<?php // $Id$

//----------------------------------------------------------------------
// CLAROLINE 1.4.*
//----------------------------------------------------------------------
// Copyright (c) 2001-2003 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see 'credits' file
//----------------------------------------------------------------------

$langFile = "external_module";

$iconForImportedTools = "external.gif";
$iconForInactiveImportedTools = "external_inactive.gif";
require '../inc/claro_init_global.inc.php';

$tbl_courseHome=$_course['dbNameGlu']."tool_list";

$nameTools = $langAddPage;

include("../inc/claro_init_header.inc.php");

$is_allowedToEdit = $is_courseAdmin;
?>

<h3><?= $nameTools ?></h3>

<?php

if($is_allowedToEdit)
{
	if($formSubmitted == 'page' && $file_type == 'text/html')
	{
		include("../inc/lib/fileUpload.lib.php");

		$updir = $rootSys."/".$_course['path']."/page/"; //path to upload directory
		$size = "20000000"; //file size ex: 5000000 bytes = 5 megabytes
		$url = $rootWeb.$_course['path']."/page/";

		/* <DEBUG> 
		echo "<pre style='color:red;font-weight:bold'>url où est la page : $url</pre>";
		/* </DEBUG> */
		

		if ( ($file_name != "") && ($file_size <= "$size" ) )
		{
			if ( trim($nom_fichier) == "" )
			{
				$nom_fichier = $file_name;
			}

			$file_name = replace_dangerous_char($file_name);
			$file_name = php2phps ($file_name);

			@copy($file, $updir.$file_name) or die($langCouldNot);

			mysql_query("INSERT INTO `$tbl_courseHome`
			             SET rubrique = \"$nom_fichier\",
			               lien     = \"../claroline/external_module/frameset_link.php?link=".$url.$file_name."\",
			               image    = \"../claroline/img/".$iconForImportedTools."\",
			               visible  = \"1\",
			               admin    = \"0\",
			               address  = \"../claroline/img/".$iconForInactiveImportedTools."\"");

			echo $langOkSentPage;
		}							// end if($formSubmitted == 'page')
		else
		{
			die($langTooBig);
		}
	}
	elseif ($formSubmitted == 'link')
	{
		mysql_query("INSERT INTO `$tbl_courseHome`
		             SET rubrique = '$name_link',
		                 lien =\"$link\",
		                 image = \"../claroline/img/".$iconForImportedTools."\",
		                 visible =\"1\",
		                 admin =\"0\",
		                 address =\"../claroline/img/".$iconForInactiveImportedTools."\"");

		echo $langOkSentLink;
	}								// end if ($formSubmitted == 'link')
	else
	{
		echo	"<table border=\"0\">\n",

				"<form method=\"POST\" action=\"$PHP_SELF\" enctype=\"multipart/form-data\">\n",
				"<input type=\"hidden\" name=\"formSubmitted\" value=\"page\">\n",

				"<tr>\n",
				"<td colspan=\"2\"><p>",$langExplanation,"</p><br></td>\n",
				"</tr>\n",

				"<tr>\n",
				"<td align=\"right\">",$langSendPage," : </td>\n",
				"<td>\n",
				"<input type=\"file\" name=\"file\" size=\"35\" accept=\"text/html\">\n",
				"</td>\n",

				"<tr>\n",
				"<td align=\"right\"><label for=\"nom_fichier\">",$langPgTitle," : </label></td>\n",
				"<td>",
				"<input type=\"text\" name=\"nom_fichier\" id=\"nom_fichier\" size=\"51\">",
				"<input type=\"submit\" value=\"",$langOk,"\">",
				"</td>\n",
				"</tr>\n",

				"</form>\n",

				"<form method=\"post\" action=\"$PHP_SELF\">",
				"<input type=\"hidden\" name=\"formSubmitted\" value=\"link\">\n",

				"<tr>\n",
				"<td colspan=\"2\"><br><h3>",$langLinkSite,"</h3></td>\n",
				"</tr>\n",

				"<tr>\n",
				"<td colspan=\"2\"><p>",$langSubTitle,"</p></br></td>\n",
				"</tr>\n",

				"<tr>\n",
				"<td align=\"right\"><label for=\"link\">",$langLink, " : </label></td>\n",
				"<td><input type=\"text\" name=\"link\" id=\"link\" size=\"50\" value=\"http://\"></td>\n",
				"</tr>\n",

				"<tr>\n",
				"<td align=\"right\"><label=\"name_link\">",$langName," : </label></td>\n",
				"<td>",
				"<input type=\"text\" name=\"name_link\" id=\"name_link\" size=\"50\">",
				"<input type=\"submit\" value=\"",$langOk,"\">",
				"</td>\n",
				"</tr>\n",

				"</table>\n";
	}
}								// end if $is_allowedToEdit

else
{
	// Print You are not identified as responsible for this course
	echo $langNotAllowed;
}	// else
echo "<br>";
@include($includePath."/claro_init_footer.inc.php");
?>