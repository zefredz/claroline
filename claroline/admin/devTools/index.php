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
info : Cette  page  sert d'index  pour les  outils de développements
*
* Elle nécéssite une  protection
*/
// 6 includes to be certain to  have a name  on each $lang, and  iff possible  in english
$langDiffTranslation = "Comparer des fichers de traduction";
$langMakeFileOfTranslation = "Construire un fichier de traduction";
$lang_no_access_here ="Pas d'accès ";
$langTranslations ="Traductions";
$langFilling	="Remplissage de la base avec des valeur test";
$langFillUsers	= "Inserer des Utilisateurs";
$langFillTree	= "Inserer des catégories/faculté dans l'arbre";
$langFillCourses	= "Créer des cours de test";
$langNomPageDevIndex = "Outils de développement";
$langAdmin = "Administration";

$langFile = "admin";
require '../../inc/claro_init_global.inc.php';



$is_allowedToAdmin 	= $is_platformAdmin || $PHP_AUTH_USER;
if ($is_allowedToAdmin)
{
	if ($PHP_AUTH_USER=="" && ($REMOTE_ADDR != $SERVER_ADDR))
	{
		session_unregister("is_platformAdmin");
		header("Location:.");
		die ();
	}
}

$nameTools = $langNomPageDevIndex;
$interbredcrump[]= array ("url"=>"../index.php", "name"=> $langAdmin);
//$interbredcrump[]= array ("url"=>"../index.php", "name"=> $langNomPageDevIndex);
@include("../checkIfHtAccessIsPresent.php");
include($includePath.'/claro_init_header.inc.php');
claro_disp_tool_title(
	array(
	'mainTitle'=>$nameTools,
	'subTitle'=>$PHP_AUTH_USER." - ".$siteName." - ".$clarolineVersion
	)
	);
claro_disp_msg_arr($controlMsg);
if ($is_allowedToAdmin)
{
?>
<H4>
	<?php echo $langTranslations ?>
</H4>
<ul>
	<LI>
		<a href="./langFile.php"><?php echo $langDiffTranslation ?></a>
	</LI>
	<!--LI>
		<a href="./makeLangFile.php"><?php echo $langMakeFileOfTranslation ?></a>
	</LI-->
</uL>
<H4><?php echo $langFilling ?></H4>
<UL>
	<LI>
		<a href="./fillUser.php"><?php echo $langFillUsers ?></a>
	</LI>
	<LI>
		<a href="./fillTree.php"><?php echo $langFillTree ?></a>
	</LI>
	<LI>
		<a href="./fillCourses.php"><?php echo $langFillCourses ?></a>
	</LI>
</UL>
<?php

}
else
{
	echo $lang_no_access_here;
}
include($includePath."/claro_init_footer.inc.php");
?>
