<?php  session_start();

/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.3.2 $Revision$                             |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2002 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      |   $Id$            |
      +----------------------------------------------------------------------+
      |   This program is free software; you can redistribute it and/or      |
      |   modify it under the terms of the GNU General Public License        |
      |   as published by the Free Software Foundation; either version 2     |
      |   of the License, or (at your option) any later version.             |
      |                                                                      |
      |   This program is distributed in the hope that it will be useful,    |
      |   but WITHOUT ANY WARRANTY; without even the implied warranty of     |
      |   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the      |
      |   GNU General Public License for more details.                       |
      |                                                                      |
      |   You should have received a copy of the GNU General Public License  |
      |   along with this program; if not, write to the Free Software        |
      |   Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA          |
      |   02111-1307, USA. The GNU GPL license is also available through     |
      |   the world-wide-web at http://www.gnu.org/copyleft/gpl.html         |
      +----------------------------------------------------------------------+
      | Authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
      |          Hugues Peeters    <peeters@ipm.ucl.ac.be>                   |
      |          Christophe Gesché <gesche@ipm.ucl.ac.be>                    |
      +----------------------------------------------------------------------+
 */

/*******************************************************************
*         IMPORT A PAGE INTO THE WEBSITE
********************************************************************

GOALS
*****
Allow professor to send quickly a page that will be integrated under the website header.

DETAIL
*********

1. Send a HTML file to /courseName/page directory
2. Insert document name / title correspondence in "accueil" SQL table

************************************************************/

include('../include/config.php');
$db = mysql_connect("$mysqlServer", "$mysqlUser", "$mysqlPassword");
$default_language = $language;
include('../include/settings.php');// include course settings

$TBL_ACCUEIL=$_course['dbNameGlu']."accueil";

@include("../lang/english/trad4all.inc.php");
@include("../lang/$default_language/trad4all.inc.php");
@include("../lang/$languageInterface/trad4all.inc.php");

@header('Content-Type: text/html; charset='. $charset);

@include("../lang/english/import.inc.php");
@include("../lang/$default_language/import.inc.php");
@include("../lang/$languageInterface/import.inc.php");

@include("../lang/english/external_module.inc.php");
@include("../lang/$default_language/external_module.inc.php");
@include("../lang/$languageInterface/external_module.inc.php");


$nameTools = $langAddPage;

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>
<head>
<title><?= $nameTools," - ",$currentCourseName," - ",$siteName ?></title>

<link rel="stylesheet" href="../css/default.css" type="text/css">

</head>

<body bgcolor="white" dir="<?php echo $text_dir ?>">

<?php include('../inc/claroline_header.php'); ?>

<h3><?= $nameTools ?></h3>

<?php

if($is_adminOfCourse) 
{ 
	if($submitPage) 
	{
		include("../inc/lib/fileUpload.lib.php");

		$updir = $webDir."/".$currentCourseID."/".page."/"; //path to upload directory
		$size = "20000000"; //file size ex: 5000000 bytes = 5 megabytes

		if ( ($file_name != "") && ($file_size <= "$size" ) )
		{
			if ( trim($nom_fichier) == "" )
			{
				$nom_fichier = $file_name;
			}

			$file_name = replace_dangerous_char($file_name);
			$file_name = php2phps ($file_name);

			@copy($file, $updir."/".$file_name) or die($langCouldNot);

			mysql_query("INSERT INTO `$TBL_ACCUEIL`
			             SET rubrique = \"$nom_fichier\",
			               lien     = \"../claroline/import/import_page.php?link=$file_name\",
			               image    = \"travaux.png\",
			               visible  = \"1\",
			               admin    = \"0\",
			               address  = \"../../$currentCourseID/page/$file_name\"");

			echo $langOkSent;
		}
		else 
		{
			die($langTooBig);
		}
	}
	elseif ($submitExtMod)
	{
		mysql_query("INSERT INTO `$TBL_ACCUEIL`
		             SET rubrique = '$name_link',
		                 lien =\"$link\",
		                 image = \"travaux.png\",
		                 visible =\"1\",
		                 admin =\"0\",
		                 address =\"$link\"");

		echo "<a href=\"../../",$currentCourseID,"/index.php\">",$langHome,"</a>";
	}
	else
	{
		echo	"<table border=\"0\">\n",

				"<form method=\"POST\" action=\"$PHP_SELF\" enctype=\"multipart/form-data\">\n",

				"<tr>\n",
				"<td colspan=\"2\"><p>",$langExplanation,"</p><br></td>\n",
				"</tr>\n",

				"<tr>\n",
				"<td align=\"right\"><label for=\"file\">",$langSendPage," : </label></td>\n",
				"<td>\n",
				"<input type=\"file\" id=\"file\" name=\"file\" size=\"35\" accept=\"text/html\">\n",
				"</td>\n",

				"<tr>\n",
				"<td align=\"right\"><label for=\"nom_fichier\">",$langPgTitle," : </label></td>\n",
				"<td>",
				"<input type=\"text\" name=\"nom_fichier\" id=\"nom_fichier\" size=\"51\">",
				"<input type=\"submit\" name=\"submitPage\" value=\"",$langOk,"\">",
				"</td>\n",
				"</tr>\n",

				"</form>\n",

				"<form method=\"post\" action=\"$PHP_SELF\">",

				"<tr>\n",
				"<td colspan=\"2\"><br><h3>",$langLinkSite,"</h3></td>\n",
				"</tr>\n",

				"<tr>\n",
				"<td colspan=\"2\"><p>",$langSubTitle,"</p></br></td>\n",
				"</tr>\n",

				"<tr>\n",
				"<td align=\"right\"><label for=\"link\">",$langLink, " : </label></td>\n",
				"<td><input type=\"text\" name=\"link\" size=\"50\" value=\"http://\"></td>\n",
				"</tr>\n",
				
				"<tr>\n",
				"<td align=\"right\"><label for=\"name_link\">",$langName," : </label></td>\n",
				"<td>",
				"<input type=\"text\" name=\"name_link\" id=\"name_link\" size=\"50\">",
				"<input type=\"submit\" name=\"submitExtMod\" value=\"",$langOk,"\">",
				"</td>\n",
				"</tr>\n",

				"</table>\n";
	}
} // end if $is_adminOfCourse

else 
{
	// Print You are not identified as responsible for this course
	echo $langNotAllowed;
}	// else

?>
</body>
</html>