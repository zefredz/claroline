<?php # $Id$

//----------------------------------------------------------------------
// CLAROLINE
//----------------------------------------------------------------------
// Copyright (c) 2001-2004 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see 'credits' file
//----------------------------------------------------------------------

	###############PHPMyAdminTables##################
	mysql_query("INSERT INTO `pma_column_comments` VALUES (1, '".$mainDbName."', 'cours', 'code', 'sysCode')");
 	mysql_query("INSERT INTO `pma_column_comments` VALUES (2, '".$mainDbName."', 'cours', 'directory', 'path')");
 	mysql_query("INSERT INTO `pma_column_comments` VALUES (3, '".$mainDbName."', 'cours', 'dbName', 'dbName')");
 	mysql_query("INSERT INTO `pma_column_comments` VALUES (4, '".$mainDbName."', 'cours', 'description', 'not used')");
 	mysql_query("INSERT INTO `pma_column_comments` VALUES (5, '".$mainDbName."', 'cours', 'faculte', 'category')");
 	mysql_query("INSERT INTO `pma_column_comments` VALUES (6, '".$mainDbName."', 'cours', 'visible', 'show to anonymous user')");
 	mysql_query("INSERT INTO `pma_column_comments` VALUES (7, '".$mainDbName."', 'cours', 'cahier_charges', 'depreacated')");
 	mysql_query("INSERT INTO `pma_column_comments` VALUES (8, '".$mainDbName."', 'cours', 'scoreShow', 'boolean')");
 	mysql_query("INSERT INTO `pma_column_comments` VALUES (9, '".$mainDbName."', 'cours', 'titulaires', 'no link with users , simple string')");
 	mysql_query("INSERT INTO `pma_column_comments` VALUES (10, '".$mainDbName."', 'cours', 'fake_code', 'officialCode')");
 	mysql_query("INSERT INTO `pma_column_comments` VALUES (11, '".$mainDbName."', 'cours_user', 'code_cours', 'from cours table')");
 	mysql_query("INSERT INTO `pma_column_comments` VALUES (12, '".$mainDbName."', 'cours_user', 'user_id', 'from user table')");
 	mysql_query("INSERT INTO `pma_column_comments` VALUES (13, '".$mainDbName."', 'cours_user', 'statut', '1=course admin, 5= student')");
 	mysql_query("INSERT INTO `pma_column_comments` VALUES (14, '".$mainDbName."', 'cours_user', 'role', 'label')");
 	mysql_query("INSERT INTO `pma_column_comments` VALUES (15, '".$mainDbName."', 'cours_user', 'team', 'deprecated')");
 	mysql_query("INSERT INTO `pma_column_comments` VALUES (16, '".$mainDbName."', 'cours_user', 'tutor', '1 is tutor in course')");
 	mysql_query("INSERT  INTO `pma_column_comments` VALUES (17, '".$mainDbName."', 'admin', 'idUser', 'relation with idUser from user table')");
 	mysql_query("INSERT  INTO `pma_column_comments` VALUES (18, '".$mainDbName."', 'faculte', 'name', 'name of caregory')");
 	mysql_query("INSERT  INTO `pma_column_comments` VALUES (19, '".$mainDbName."', 'faculte', 'code', 'code of caregory')");
 	mysql_query("INSERT  INTO `pma_column_comments` VALUES (20, '".$mainDbName."', 'faculte', 'code_P', 'code of parent caregory')");
 	mysql_query("INSERT  INTO `pma_column_comments` VALUES (21, '".$mainDbName."', 'faculte', 'treePos', 'Position tree')");
 	mysql_query("INSERT  INTO `pma_column_comments` VALUES (22, '".$mainDbName."', 'faculte', 'nb_childs', 'qty of child')");
 	mysql_query("INSERT  INTO `pma_column_comments` VALUES (23, '".$mainDbName."', 'faculte', 'canHaveCoursesChild', 'if true , teacher can use this categoy to link her course')");


#
# Contenu de la table `pma_relation`
#

	mysql_query("INSERT  INTO `pma_relation` VALUES ('".$mainDbName."', 'admin', 'idUser', '".$mainDbName."', 'user', 'user_id')");
	mysql_query("INSERT  INTO `pma_relation` VALUES ('".$mainDbName."', 'cours', 'code', '".$mainDbName."', 'cours_user', 'code_cours')");
	mysql_query("INSERT  INTO `pma_relation` VALUES ('".$mainDbName."', 'cours', 'directory', '".$mainDbName."', 'cours', 'directory')");
	mysql_query("INSERT  INTO `pma_relation` VALUES ('".$mainDbName."', 'cours', 'dbName', '".$mainDbName."', 'cours', 'dbName')");
	mysql_query("INSERT  INTO `pma_relation` VALUES ('".$mainDbName."', 'cours', 'languageCourse', '".$mainDbName."', 'cours', 'languageCourse')");
	mysql_query("INSERT  INTO `pma_relation` VALUES ('".$mainDbName."', 'cours', 'faculte', '".$mainDbName."', 'faculte', 'code')");
	mysql_query("INSERT  INTO `pma_relation` VALUES ('".$mainDbName."', 'cours', 'fake_code', '".$mainDbName."', 'cours', 'fake_code')");
	mysql_query("INSERT  INTO `pma_relation` VALUES ('".$mainDbName."', 'user', 'user_id', '".$mainDbName."', 'cours_user', 'user_id')");
	mysql_query("INSERT  INTO `pma_relation` VALUES ('".$mainDbName."', 'user', 'nom', '".$mainDbName."', 'user', 'nom')");
	mysql_query("INSERT  INTO `pma_relation` VALUES ('".$mainDbName."', 'user', 'prenom', '".$mainDbName."', 'user', 'prenom')");
	mysql_query("INSERT  INTO `pma_relation` VALUES ('".$mainDbName."', 'user', 'username', '".$mainDbName."', 'user', 'username')");
	mysql_query("INSERT  INTO `pma_relation` VALUES ('".$mainDbName."', 'user', 'password', '".$mainDbName."', 'user', 'password')");
	mysql_query("INSERT  INTO `pma_relation` VALUES ('".$mainDbName."', 'user', 'email', '".$mainDbName."', 'user', 'email')");
	mysql_query("INSERT  INTO `pma_relation` VALUES ('".$mainDbName."', 'user', 'officialCode', '".$mainDbName."', 'user', 'officialCode')");
	mysql_query("INSERT  INTO `pma_relation` VALUES ('".$mainDbName."', 'user', 'creatorId', '".$mainDbName."', 'user', 'user_id')");
	mysql_query("INSERT  INTO `pma_relation` VALUES ('".$mainDbName."', 'cours_user', 'code_cours', '".$mainDbName."', 'cours', 'code')");
	mysql_query("INSERT  INTO `pma_relation` VALUES ('".$mainDbName."', 'cours_user', 'user_id', '".$mainDbName."', 'user', 'user_id')");
	mysql_query("INSERT  INTO `pma_relation` VALUES ('".$mainDbName."', 'faculte', 'code', '".$mainDbName."', 'cours', 'cours_id')");
	mysql_query("INSERT  INTO `pma_relation` VALUES ('".$mainDbName."', 'faculte', 'code_P', '".$mainDbName."', 'faculte', 'code')");

?>
