<?php session_start();

/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.3.1 $Revision$                            |
      +----------------------------------------------------------------------+
      | $Id$                |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2002 Universite catholique de Louvain (UCL)      |
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

$langAddAdminInApache ="Ajouter un utilisateur dans l'administration";
$langAddFaculties = "Ajouter des facultés"; 
$langSearchACourse  = "Chercher un cours";
$langSearchAUser  ="Chercher un utilisateur";


/*
info : Cette  page  sert d'index  pour l'administration de claroline
* 
* Elle nécéssite une  protection
*/
@include('../include/config.php');
@include("../lang/french/trad4all.inc.php");
@include("../lang/english/trad4all.inc.php");
@include("../lang/$language/trad4all.inc.php");
header('Content-Type: text/html; charset='. $charset);
@include("../lang/french/admin.inc.php");
@include("../lang/english/admin.inc.php");
@include("../lang/$language/admin.inc.php");

// 6 includes to be certain to  have a name  on each $lang, and  iff possible  in english

$nameTools = $langAdmin;
//$interbredcrump[]= array ("url"=>"index.php", "name"=> $langAdmin);



@include("./checkIfHtAccessIsPresent.php");
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">

<html>
<head>
	<title>
		<?php echo "$nameTools - $langAdmin - $siteName - $clarolineVersion"; ?>

	</title>
<link rel="stylesheet" href="../css/default.css" type="text/css">

</head>
<body bgcolor=#FFFFFF>
<table border="0" align="center" cellpadding="0" cellspacing="0" width="<?php echo $mainInterfaceWidth?>">
	<tr>
		<td>
			<?php include("../include/claroline_header.php"); ?>

		</TD>
	</TR>
	<tr>
		<td>
<?php include "barre.inc.php";?>
<h3>
	<?php echo  $langTools ?>
</h3>



<uL>
    <LI>
		<B>
            <?php echo  $langAdministrationTools ?>
		</B>
	</LI>
<? 
if (isset($phpMyAdminWeb))
{
	?>
	<LI>
		<a href="<?php echo $phpMyAdminWeb?>/tbl_change.php?db=<?php echo $mainDbName ?>&table=faculte&goto=tbl_properties.php&back=tbl_properties.php"><?php echo $langAddFaculties ?></a>
	</LI>
	<LI>
		<a href="<?php echo $phpMyAdminWeb?>/tbl_select.php?db=<?php echo $mainDbName ?>&table=cours"><?php echo $langSearchACourse ?></a>
	</LI>
	<LI>
		<a href="<?php echo $phpMyAdminWeb?>/tbl_select.php?db=<?php echo $mainDbName ?>&table=user"><?php echo $langSearchAUser ?></a>
	</LI>
	<LI>
		<a href="<?php echo $phpMyAdminWeb?>">PHP My Admin</a>
	</LI>
<? 
} 
?>
	<LI>
		<a href="statClaro.php"><?php echo $langStatOf." ".$siteName ?></a>
	</LI>
<? 
if (isset($phpMyAdminWeb))
{
	?>
	<LI>
		<a href="<?php echo $phpMyAdminWeb ?>sql.php?db=<?php echo $mainDbName ?>&table=loginout&goto=db_details.php&sql_query=SELECT+%2A+FROM+%60loginout%60&pos=0">-<?php echo $langLogIdentLogout?>-</a>
	</LI>
	<LI>
		<a href="<?php echo $phpMyAdminWeb ?>sql.php?db=<?php echo $mainDbName ?>&table=loginout&goto=db_details.php&sql_query=SELECT+loginout.idLog,+loginout.when,+loginout.ip,+loginout.id_user,+user.username,+user.prenom,+user.nom,+user.statut,+user.email,+user.password,+loginout.action+FROM+loginout,+user+WHERE+(loginout.id_user+=+user.user_id)+order+by+loginout.when+desc"><?= $langLogIdentLogoutComplete?></a>
	</LI>
<?
}
?>
</uL>
<?php include "barre.inc.php"; ?>
<H3>
	<?php echo $langLinksToClaroProjectSite ?>
</H3>
[<A href="http://www.claroline.net">Claroline</A>]
[<A href="doc.admin.html">Documentation</A>]
<!-- [<A href="http://www.claroline.net/actu/">Actu</A>]-->
[<A href="http://phedre.ipm.ucl.ac.be/phpBB/">Forum</A>]
<!--[<A href="http://www.icampus.ucl.ac.be/CLARO01/">CLARO 01</A>] -->
<br><br>
<?php include "barre.inc.php"; ?>
<!--
<H3>En dessous ce n'est pas moi</H3>
<OL>
	<li><a href="agenda.php">Agenda</a></li>
	<li><a href="cours_upgrade.php">Update lesson</a></li>
	<li><a href="liste_documents.txt">Doc List</a></li>
	<li><a href="littletest.php">little test</a></li>
	<li><a href="mise_a_jour_icampus.php">Claroline update</a></li>
	<li><a href="bigtest2.php">bigTest</a></li>
    <li><a href="updateDataBase.php">Vérifier la version des tables</a></li>
</OL>
<br>
-->
<?// include "barre.inc.php";?>
<?// phpinfo(); ?>
		</TD>
	</TR>
</table>
<?php
	@include($includePath."/claro_init_footer.inc.php");
?>