<?php
$mtime = microtime();
$mtime = explode(" ",$mtime);
$mtime = $mtime[1] + $mtime[0];
$starttime = $mtime;
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.3.0 $Revision$                             |
      +----------------------------------------------------------------------+
      |  $Id$   |
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

/**
 * try to create main database of claroline without remove existing content
 */


$sqlForUpdate[] = "# Try create tables";

$sqlForUpdate[] = "
CREATE TABLE  IF NOT EXISTS `admin` (
  `idUser` int(10) unsigned NOT NULL default '0',
  UNIQUE KEY `idUser` (`idUser`)
) TYPE=MyISAM ;";

$sqlForUpdate[] = "CREATE TABLE  IF NOT EXISTS `annonces` (
  `id` mediumint(11) NOT NULL auto_increment,
  `contenu` text,
  `temps` date default NULL,
  `code_cours` varchar(20) default NULL,
  `ordre` mediumint(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;";

$sqlForUpdate[] = "CREATE TABLE  IF NOT EXISTS `cours` (
  `cours_id` int(11) NOT NULL auto_increment,
  `code` varchar(20) default NULL,
  `languageCourse` varchar(15) default NULL,
  `intitule` varchar(250) default NULL,
  `description` text,
  `faculte` varchar(12) default NULL,
  `visible` tinyint(4) default NULL,
  `cahier_charges` varchar(250) default NULL,
  `scoreShow` int(11) NOT NULL default '1',
  `titulaires` varchar(200) default NULL,
  `fake_code` varchar(20) default NULL,
  `departmentUrlName` varchar(30) default NULL,
  `departmentUrl` varchar(180) default NULL,
  `versionDb` varchar(10) NOT NULL default 'NEVER SET',
  `versionClaro` varchar(10) NOT NULL default 'NEVER SET',
  `lastVisit` date NOT NULL default '0000-00-00',
  `lastEdit` datetime NOT NULL default '0000-00-00 00:00:00',
  `expirationDate` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`cours_id`)
) TYPE=MyISAM;";

/*$sqlForUpdate[] = "CREATE TABLE  IF NOT EXISTS `cours_faculte` (
  `id` int(11) NOT NULL auto_increment,
  `faculte` varchar(12) NOT NULL default '',
  `code` varchar(20) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;";
*/
$sqlForUpdate[] = "CREATE TABLE  IF NOT EXISTS `cours_user` (
  `code_cours` varchar(30) NOT NULL default '0',
  `user_id` int(11) unsigned NOT NULL default '0',
  `statut` tinyint(4) NOT NULL default '0',
  `role` varchar(60) default NULL,
  `team` int(11) NOT NULL default '0',
  `tutor` int(11) NOT NULL default '0',
  PRIMARY KEY  (`code_cours`,`user_id`)
) TYPE=MyISAM;";


$sqlForUpdate[] = "
CREATE TABLE  IF NOT EXISTS `faculte` (
  `id` int(11) NOT NULL auto_increment,
  `code` varchar(10) NOT NULL default '',
  `name` varchar(100) NOT NULL default '',
  `number` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `number` (`number`)
) TYPE=MyISAM;";

$sqlForUpdate[] = "CREATE TABLE  IF NOT EXISTS `loginout` (
  `idLog` mediumint(9) unsigned NOT NULL auto_increment,
  `id_user` mediumint(9) unsigned NOT NULL default '0',
  `ip` char(16) NOT NULL default '0.0.0.0',
  `when` datetime NOT NULL default '0000-00-00 00:00:00',
  `action` enum('LOGIN','LOGOUT') NOT NULL default 'LOGIN',
  PRIMARY KEY  (`idLog`)
) TYPE=MyISAM;";

$sqlForUpdate[] = "CREATE TABLE  IF NOT EXISTS `todo` (
  `id` mediumint(9) NOT NULL auto_increment,
  `contenu` text,
  `temps` datetime default '0000-00-00 00:00:00',
  `auteur` varchar(80) default NULL,
  `email` varchar(80) default NULL,
  `priority` tinyint(4) default '0',
  `type` varchar(8) default NULL,
  `cible` varchar(30) default NULL,
  `statut` varchar(8) default NULL,
  `assignTo` mediumint(9) default NULL,
  `showToUsers` enum('YES','NO') NOT NULL default 'YES',
  PRIMARY KEY  (`id`),
  KEY `temps` (`temps`)
) TYPE=MyISAM;";

$sqlForUpdate[] = "CREATE TABLE  IF NOT EXISTS `user` (
  `user_id` mediumint(8) unsigned NOT NULL auto_increment,
  `nom` varchar(60) default NULL,
  `prenom` varchar(60) default NULL,
  `username` varchar(20) default 'empty',
  `password` varchar(50) default 'empty',
  `email` varchar(100) default NULL,
  `statut` tinyint(4) default NULL,
  PRIMARY KEY  (`user_id`)
) TYPE=MyISAM;";


$sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `pma_bookmark` (
  `id` int(11) NOT NULL auto_increment,
  `dbase` varchar(255) NOT NULL default '',
  `user` varchar(255) NOT NULL default '',
  `label` varchar(255) NOT NULL default '',
  `query` text NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM COMMENT='Bookmarks';";

$sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `pma_column_comments` (
  `id` int(5) unsigned NOT NULL auto_increment,
  `db_name` varchar(64) NOT NULL default '',
  `table_name` varchar(64) NOT NULL default '',
  `column_name` varchar(64) NOT NULL default '',
  `comment` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `db_name` (`db_name`,`table_name`,`column_name`)
) TYPE=MyISAM COMMENT='Comments for Columns';";

$sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `pma_pdf_pages` (
  `db_name` varchar(64) NOT NULL default '',
  `page_nr` int(10) unsigned NOT NULL auto_increment,
  `page_descr` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`page_nr`),
  KEY `db_name` (`db_name`)
) TYPE=MyISAM COMMENT='PDF Relationpages for PMA';";

$sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `pma_relation` (
  `master_db` varchar(64) NOT NULL default '',
  `master_table` varchar(64) NOT NULL default '',
  `master_field` varchar(64) NOT NULL default '',
  `foreign_db` varchar(64) NOT NULL default '',
  `foreign_table` varchar(64) NOT NULL default '',
  `foreign_field` varchar(64) NOT NULL default '',
  PRIMARY KEY  (`master_db`,`master_table`,`master_field`),
  KEY `foreign_field` (`foreign_db`,`foreign_table`)
) TYPE=MyISAM COMMENT='Relation table';";

$sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `pma_table_coords` (
  `db_name` varchar(64) NOT NULL default '',
  `table_name` varchar(64) NOT NULL default '',
  `pdf_page_number` int(11) NOT NULL default '0',
  `x` float unsigned NOT NULL default '0',
  `y` float unsigned NOT NULL default '0',
  PRIMARY KEY  (`db_name`,`table_name`,`pdf_page_number`)
) TYPE=MyISAM COMMENT='Table coordinates for phpMyAdmin PDF output';";

$sqlForUpdate[] = "CREATE TABLE IF NOT EXISTS `pma_table_info` (
  `db_name` varchar(64) NOT NULL default '',
  `table_name` varchar(64) NOT NULL default '',
  `display_field` varchar(64) NOT NULL default '',
  PRIMARY KEY  (`db_name`,`table_name`)
) TYPE=MyISAM COMMENT='Table information for phpMyAdmin';";


$sqlForUpdate[] = "# Try too add missing fields";

$sqlForUpdate[] = "# table Admin ";
$sqlForUpdate[] = " ALTER IGNORE  TABLE admin ADD  idUser INT UNSIGNED DEFAULT '0' NOT NULL;";

$sqlForUpdate[] = "# table annonces ";
$sqlForUpdate[] = " ALTER IGNORE  TABLE annonces ADD id mediumint(11) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE  TABLE annonces ADD contenu text;";
$sqlForUpdate[] = " ALTER IGNORE  TABLE annonces ADD temps date default NULL;";
$sqlForUpdate[] = " ALTER IGNORE  TABLE annonces ADD code_cours varchar(20) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE  TABLE annonces ADD ordre mediumint(11) NOT NULL default '0';";


$sqlForUpdate[] = "# table `cours`";
$sqlForUpdate[] = " ALTER IGNORE  TABLE cours ADD cours_id int(11) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE  TABLE cours ADD code varchar(20) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE  TABLE cours ADD languageCourse varchar(15) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE  TABLE cours ADD intitule varchar(250) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE  TABLE cours ADD description   text;";
$sqlForUpdate[] = " ALTER IGNORE  TABLE cours ADD faculte varchar(12) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE  TABLE cours ADD visible tinyint(4) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE  TABLE cours ADD cahier_charges   varchar(250) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE  TABLE cours ADD scoreShow int(11) NOT NULL default '1';";
$sqlForUpdate[] = " ALTER IGNORE  TABLE cours ADD titulaires varchar(200) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE  TABLE cours ADD fake_code varchar(20) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE  TABLE cours ADD departmentUrlName varchar(30) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE  TABLE cours ADD departmentUrl varchar(180) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE  TABLE cours ADD versionDb varchar(10) NOT NULL default 'NEVER SET';";
$sqlForUpdate[] = " ALTER IGNORE  TABLE cours ADD versionClaro varchar(10) NOT NULL default 'NEVER SET';";
$sqlForUpdate[] = " ALTER IGNORE  TABLE cours ADD lastVisit date NOT NULL default '0000-00-00';";
$sqlForUpdate[] = " ALTER IGNORE  TABLE cours ADD lastEdit datetime NOT NULL default '0000-00-00 00:00:00';";
$sqlForUpdate[] = " ALTER IGNORE  TABLE cours ADD expirationDate datetime NOT NULL default '0000-00-00 00:00:00';";



$sqlForUpdate[] = "# table `cours_faculte`";
//$sqlForUpdate[] = " ALTER IGNORE  TABLE cours_faculte ADD id int(11) NOT NULL auto_increment;";
//$sqlForUpdate[] = " ALTER IGNORE  TABLE cours_faculte ADD faculte varchar(12) NOT NULL default '';";
//$sqlForUpdate[] = " ALTER IGNORE  TABLE cours_faculte ADD code varchar(20) NOT NULL default '';";


$sqlForUpdate[] = "# table `cours_user`";
$sqlForUpdate[] = " ALTER IGNORE  TABLE cours_user ADD code_cours varchar(30) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE  TABLE cours_user ADD user_id INT UNSIGNED DEFAULT '0' NOT NULL;";
$sqlForUpdate[] = " ALTER IGNORE  TABLE cours_user ADD statut tinyint(4) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE  TABLE cours_user ADD role varchar(60) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE  TABLE cours_user ADD team int(11) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER IGNORE  TABLE cours_user ADD tutor int(11) NOT NULL default '0';";

$sqlForUpdate[] = "# table `faculte`";
$sqlForUpdate[] = " ALTER IGNORE  TABLE faculte ADD id int(11) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE  TABLE faculte ADD code varchar(10) NOT NULL default '';";
$sqlForUpdate[] = " ALTER IGNORE  TABLE faculte ADD name varchar(100) NOT NULL default '';";
$sqlForUpdate[] = " ALTER IGNORE  TABLE faculte ADD number int(11) NOT NULL default '0';";

$sqlForUpdate[] = "# table `loginout`";
$sqlForUpdate[] = " ALTER IGNORE  TABLE loginout ADD idLog mediumint(9) unsigned NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE  TABLE loginout ADD id_user INT UNSIGNED DEFAULT '0' NOT NULL;";
$sqlForUpdate[] = " ALTER IGNORE  TABLE loginout ADD ip char(16) NOT NULL default '0.0.0.0';";
$sqlForUpdate[] = " ALTER IGNORE  TABLE loginout ADD loginout.when datetime NOT NULL default '0000-00-00 00:00:00';";
$sqlForUpdate[] = " ALTER IGNORE  TABLE loginout ADD action enum('LOGIN','LOGOUT') NOT NULL default 'LOGIN';";

$sqlForUpdate[] = "# table `todo`";
$sqlForUpdate[] = " ALTER IGNORE  TABLE todo ADD id mediumint(9) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE  TABLE todo ADD contenu text;";
$sqlForUpdate[] = " ALTER IGNORE  TABLE todo ADD temps datetime default '0000-00-00 00:00:00';";
$sqlForUpdate[] = " ALTER IGNORE  TABLE todo ADD auteur varchar(80) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE  TABLE todo ADD email varchar(80) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE  TABLE todo ADD priority tinyint(4) default '0';";
$sqlForUpdate[] = " ALTER IGNORE  TABLE todo ADD type varchar(8) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE  TABLE todo ADD cible varchar(30) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE  TABLE todo ADD statut varchar(8) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE  TABLE todo ADD assignTo INT UNSIGNED DEFAULT '0' NOT NULL;";
$sqlForUpdate[] = " ALTER IGNORE  TABLE todo ADD showToUsers enum('YES','NO') NOT NULL default 'YES';";

$sqlForUpdate[] = "# table `user`";
$sqlForUpdate[] = " ALTER IGNORE  TABLE user ADD user_id INT UNSIGNED DEFAULT '0' NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER IGNORE  TABLE user ADD nom varchar(60) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE  TABLE user ADD prenom varchar(60) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE  TABLE user ADD username varchar(20) default 'empty';";
$sqlForUpdate[] = " ALTER IGNORE  TABLE user ADD password varchar(50) default 'empty';";
$sqlForUpdate[] = " ALTER IGNORE  TABLE user ADD email varchar(100) default NULL;";
$sqlForUpdate[] = " ALTER IGNORE  TABLE user ADD statut tinyint(4) default NULL;";

$sqlForUpdate[] = "#Ok  maintenant on modifie toutes ces tables";

$sqlForUpdate[] = "#Ok  table admin";
$sqlForUpdate[] = " ALTER TABLE admin CHANGE idUser idUser INT UNSIGNED DEFAULT '0' NOT NULL;";

$sqlForUpdate[] = "# Structure de la table `annonces`";
$sqlForUpdate[] = " ALTER TABLE annonces CHANGE id id mediumint(11) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER TABLE annonces CHANGE contenu contenu text;";
$sqlForUpdate[] = " ALTER TABLE annonces CHANGE temps   temps date default NULL;";
$sqlForUpdate[] = " ALTER TABLE annonces CHANGE code_cours code_cours varchar(20) default NULL;";
$sqlForUpdate[] = " ALTER TABLE annonces CHANGE ordre   ordre mediumint(11) NOT NULL default '0';";

$sqlForUpdate[] = "# Structure de la table `cours`";
$sqlForUpdate[] = " ALTER TABLE cours CHANGE cours_id   cours_id int(11) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER TABLE cours CHANGE code   code varchar(20) default NULL;";
$sqlForUpdate[] = " ALTER TABLE cours CHANGE languageCourse   languageCourse varchar(15) default NULL;";
$sqlForUpdate[] = " ALTER TABLE cours CHANGE intitule   intitule varchar(250) default NULL;";
$sqlForUpdate[] = " ALTER TABLE cours CHANGE description   description   text;";
$sqlForUpdate[] = " ALTER TABLE cours CHANGE faculte   faculte varchar(12) default NULL;";
$sqlForUpdate[] = " ALTER TABLE cours CHANGE visible   visible tinyint(4) default NULL;";
$sqlForUpdate[] = " ALTER TABLE cours CHANGE cahier_charges   cahier_charges   varchar(250) default NULL;";
$sqlForUpdate[] = " ALTER TABLE cours CHANGE scoreShow   scoreShow int(11) NOT NULL default '1';";
$sqlForUpdate[] = " ALTER TABLE cours CHANGE titulaires   titulaires varchar(200) default NULL;";
$sqlForUpdate[] = " ALTER TABLE cours CHANGE fake_code   fake_code varchar(20) default NULL;";
$sqlForUpdate[] = " ALTER TABLE cours CHANGE departmentUrlName   departmentUrlName varchar(30) default NULL;";
$sqlForUpdate[] = " ALTER TABLE cours CHANGE departmentUrl   departmentUrl varchar(180) default NULL;";
$sqlForUpdate[] = " ALTER TABLE cours CHANGE versionDb versionDb varchar(10) NOT NULL default 'NEVER SET';";
$sqlForUpdate[] = " ALTER TABLE cours CHANGE versionClaro versionClaro varchar(10) NOT NULL default 'NEVER SET';";
$sqlForUpdate[] = " ALTER TABLE cours CHANGE lastVisit lastVisit date NOT NULL default '0000-00-00';";
$sqlForUpdate[] = " ALTER TABLE cours CHANGE lastEdit lastEdit datetime NOT NULL default '0000-00-00 00:00:00';";
$sqlForUpdate[] = " ALTER TABLE cours CHANGE expirationDate expirationDate datetime NOT NULL default '0000-00-00 00:00:00';";


//$sqlForUpdate[] = "# Structure de la table `cours_faculte`";
//$sqlForUpdate[] = " ALTER TABLE cours_faculte CHANGE id id int(11) NOT NULL auto_increment;";
//$sqlForUpdate[] = " ALTER TABLE cours_faculte CHANGE faculte faculte varchar(12) NOT NULL default '';";
//$sqlForUpdate[] = " ALTER TABLE cours_faculte CHANGE code code varchar(20) NOT NULL default '';";

$sqlForUpdate[] = "# Structure de la table `cours_user`";
$sqlForUpdate[] = " ALTER TABLE cours_user CHANGE code_cours code_cours varchar(30) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER TABLE cours_user CHANGE user_id user_id INT UNSIGNED DEFAULT '0' NOT NULL;";
$sqlForUpdate[] = " ALTER TABLE cours_user CHANGE statut statut tinyint(4) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER TABLE cours_user CHANGE role role varchar(60) default NULL;";
$sqlForUpdate[] = " ALTER TABLE cours_user CHANGE team team int(11) NOT NULL default '0';";
$sqlForUpdate[] = " ALTER TABLE cours_user CHANGE tutor tutor int(11) NOT NULL default '0';";


$sqlForUpdate[] = "# Structure de la table `faculte`";
$sqlForUpdate[] = " ALTER TABLE faculte CHANGE id id int(11) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER TABLE faculte CHANGE code code varchar(10) NOT NULL default '';";
$sqlForUpdate[] = " ALTER TABLE faculte CHANGE name name varchar(100) NOT NULL default '';";
$sqlForUpdate[] = " ALTER TABLE faculte CHANGE number number int(11) NOT NULL default '0';";


$sqlForUpdate[] = "# Structure de la table `loginout`";
$sqlForUpdate[] = " ALTER TABLE loginout CHANGE idLog idLog mediumint(9) unsigned NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER TABLE loginout CHANGE id_user id_user INT UNSIGNED DEFAULT '0' NOT NULL;";
$sqlForUpdate[] = " ALTER TABLE loginout CHANGE ip ip char(16) NOT NULL default '0.0.0.0';";
$sqlForUpdate[] = " ALTER TABLE loginout CHANGE loginout.when loginout.when datetime NOT NULL default '0000-00-00 00:00:00';";
$sqlForUpdate[] = " ALTER TABLE loginout CHANGE action action enum('LOGIN','LOGOUT') NOT NULL default 'LOGIN';";


$sqlForUpdate[] = "# Structure de la table `todo`";
$sqlForUpdate[] = " ALTER TABLE todo CHANGE id id mediumint(9) NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER TABLE todo CHANGE contenu contenu text;";
$sqlForUpdate[] = " ALTER TABLE todo CHANGE temps temps datetime default '0000-00-00 00:00:00';";
$sqlForUpdate[] = " ALTER TABLE todo CHANGE auteur auteur varchar(80) default NULL;";
$sqlForUpdate[] = " ALTER TABLE todo CHANGE email email varchar(80) default NULL;";
$sqlForUpdate[] = " ALTER TABLE todo CHANGE priority priority tinyint(4) default '0';";
$sqlForUpdate[] = " ALTER TABLE todo CHANGE type type varchar(8) default NULL;";
$sqlForUpdate[] = " ALTER TABLE todo CHANGE cible cible varchar(30) default NULL;";
$sqlForUpdate[] = " ALTER TABLE todo CHANGE statut statut varchar(8) default NULL;";
$sqlForUpdate[] = " ALTER TABLE todo CHANGE assignTo assignTo INT UNSIGNED DEFAULT '0' NOT NULL;";
$sqlForUpdate[] = " ALTER TABLE todo CHANGE showToUsers showToUsers enum('YES','NO') NOT NULL default 'YES';";

$sqlForUpdate[] = "# Structure de la table `user`";
$sqlForUpdate[] = " ALTER TABLE user CHANGE user_id user_id INT UNSIGNED DEFAULT '0' NOT NULL auto_increment;";
$sqlForUpdate[] = " ALTER TABLE user CHANGE nom nom varchar(60) default NULL;";
$sqlForUpdate[] = " ALTER TABLE user CHANGE prenom prenom varchar(60) default NULL;";
$sqlForUpdate[] = " ALTER TABLE user CHANGE username username varchar(20) default 'empty';";
$sqlForUpdate[] = " ALTER TABLE user CHANGE password password varchar(50) default 'empty';";
$sqlForUpdate[] = " ALTER TABLE user CHANGE email email varchar(100) default NULL;";
$sqlForUpdate[] = " ALTER TABLE user CHANGE statut statut tinyint(4) default NULL;";

$sqlForUpdate[] = "# Commentaires / Comments";
$sqlForUpdate[] = " ALTER TABLE annonces  		COMMENT='news';";
$sqlForUpdate[] = " ALTER TABLE cours  			COMMENT='data of courses';";
//$sqlForUpdate[] = " ALTER TABLE cours_faculte  	COMMENT='link between courses and department';";
$sqlForUpdate[] = " ALTER TABLE cours_user  	COMMENT='link between courses and users (subscribe state)';";
$sqlForUpdate[] = " ALTER TABLE faculte  		COMMENT='department of the institution';";
$sqlForUpdate[] = " ALTER TABLE loginout  		COMMENT='connection of users';";
$sqlForUpdate[] = " ALTER TABLE todo  			COMMENT='suggestion';";
$sqlForUpdate[] = " ALTER TABLE user  			COMMENT='data of users';";

$langUpgradeDataBase = "Upgrading Main Database ";

@include($includePath."/config.inc.php");
@include("../../lang/english/trad4all.inc.php");
@include("../../lang/$language/trad4all.inc.php");
header('Content-Type: text/html; charset='. $charset);
@include("../../lang/english/admin.inc.php");
@include("../../lang/$language/admin.inc.php");

$db = mysql_connect("$mysqlServer", "$mysqlUser", "$mysqlPassword");
mysql_select_db($mainDbName);
$nameTools = $langUpgradeDataBase;

$up_link ="checkCourseDatabase.php";

if ($encrypt)
{
	$result=mysql_query(" SELECT * FROM user");
	while ($myrow = mysql_fetch_array($result))
	{
		$id=$myrow[user_id];
		$newpass=md5($myrow[password]);
		$sqlForUpdate[] = " UPDATE user SET password = '$newpass' WHERE user_id = $id";
	}
}
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">


<html>
<head>
	<TITLE>
      -- Claroline upgrade -- version <?php echo $clarolineVersion ?>
	</TITLE>
<link rel="stylesheet" href="../css/default.css" type="text/css">


<style  media="print" >
.notethis {	border: thin double Black;	margin-left: 15px;	margin-right: 15px;}
</style>

</head>
<body bgcolor="white" dir="<?php echo $text_dir ?>">
<div align="center">
<table cellpadding="6" cellspacing="0" border="0" width="650" bgcolor="#E6E6E6">
	<tr bgcolor="#4171B5">
		<td valign="top">
			<font color="white" face="arial, helvetica">
				-- Claroline upgrade -- version <?php echo $clarolineVersion ?>
			</font>
		</td>
	</tr>
	<tr bgcolor="#E6E6E6">
		<td>
			<font face="arial, helvetica">
<?
echo "
<br>
<br>
<strong>Step 4</strong>: main Claroline database (<code>".$mainDbName."</code>) upgraded  
<br>";

echo "
<OL>";
$nbError =0;
while (list($key,$sqlTodo) = each($sqlForUpdate))
{ 
	if ($sqlTodo[0] == "#")
	{
		if ($verbose)
		{
			echo "
<BR>
<BR>
<font color=blue>
	-------------  $sqlTodo -------------
</font>
<br>";	}
	}
	else
	{
		$res = @mysql_query($sqlTodo);
		if ($verbose)
		{
			echo "
	<LI>
		".$sqlTodo;	
		}
		if (mysql_errno() > 0)
		{
			if (mysql_errno() == 1060 || mysql_errno() == 1062)
			{
				if ($verbose)
				{
					echo "
		<BR>
		<font color=green>
			".mysql_errno()."
			</u> : ".mysql_error()."

		</font>
	</LI>";	
				}
			}
			else	
			{
				echo "
		<BR>
		<font color=red>
			<strong>
				".(++$nbError)."
			</strong> n° 
			<u>
				".mysql_errno()."
			</u> : ".mysql_error()."
		</font>
	</LI>";	
			}
		}
	}
}
mysql_close();
echo "
</OL>
";
$mtime = microtime();
$mtime = explode(" ",$mtime);
$mtime = $mtime[1] + $mtime[0];
$endtime = $mtime;
$totaltime = ($endtime - $starttime);

if ($nbError>0 )
{
	echo "
		<font color=red>
			<strong>$nbError errors found</strong>
		</font>
<br>
<Form action=\"".$PHP_SELF."\" >
	Retry with more detail.
	<input type=\"hidden\" name=\"verbose\" id=\"verbose\" value=\"true\">
	<input type=\"submit\" name=\"retry\" value=\"retry\">
</FORM>

<br>
<br>
<br>
		";
}
else
{
	?>
<p align="right">
	<font color=green>
			<strong>Ok</strong>&nbsp;&nbsp;&nbsp;
	</font>
</p>
<Form action="../admin/batchUpdateDb.php" >
	<strong>Step 5</strong>: Upgrading all courses own databases
	<input type="submit" name="upgrade" value="Upgrade">
</FORM>
<br>
<br>
	<?

}

?>
