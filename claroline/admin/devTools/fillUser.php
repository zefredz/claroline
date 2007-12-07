<?php // $Id$µ
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
/**
 * SHUFFLE COURSE SITE CREATION TOOL
 * GOALS
 * *******

// Créateur de cours bidon pour les tests
// fake course creator to test

// create nc courses
// insert between smin and smax students
// insert between pmin and pmax courses admins

 * ******************************************************************
 */


DEFINE("DISP_RESULT_INSERT"		,1);
DEFINE("DISP_FORM_SET_OPTION"	,2);
DEFINE("DISP_INSERT_COMPLETE"	,3);
unset($includePath);
$langFile = "dev.adduser";
require '../../inc/claro_init_global.inc.php';

if (!isset($includePath)) die("init not run");
if (!isset($_uid)) die("you need to be logged");
//// Config tool
include($includePath."/conf/add_course.conf.php");
include($includePath."/conf/user.conf.php");
//// LIBS
include($includePath."/lib/text.lib.php");
include($includePath."/lib/add_course.lib.inc.php");
include($includePath."/lib/debug.lib.inc.php");
include($includePath."/lib/fileManage.lib.php");
include($includePath."/conf/course_info.conf.php");
$nameTools = $langAdd_users;
$interbredcrump[]= array ("url"=>"../index.php", "name"=> $langAdmin);
$interbredcrump[]= array ("url"=>"index.php", "name"=> $langDevTools);
$htmlHeadXtra[] =
"<style type=\"text/css\">
		label
		{
			display: block;
			width: 25%;
			float: left;
			text-align: right;
			background-color: ".$color2.";
		}
		input, textarea { margin-left: 1em;	background-color: ".$color1.";}
		fieldset, form {  margin: 10; padding: 4;  }
-->
</STYLE>";

$TABLECOURSE 		= "$mainDbName`.`cours";
$TABLECOURSDOMAIN	= "$mainDbName`.`faculte";
$tbl_user			= "$mainDbName`.`user";
$TABLECOURSUSER 	= "$mainDbName`.`cours_user";
$TABLEANNOUNCEMENTS	= "annonces";
$can_create_courses = (bool) ($is_allowedCreateCourse);
$coursesRepositories = $rootSys;


$nc   = is_numeric($_REQUEST["nc"])?$_REQUEST["nc"]:DEFAULT_MIN_QTY_STUDENT_REGISTRED_IN_COURSE;
$smin = is_numeric($_REQUEST["smin"])?$_REQUEST["smin"]:DEFAULT_MIN_QTY_STUDENT_REGISTRED_IN_COURSE;
$smax = is_numeric($_REQUEST["smax"])?$_REQUEST["smax"]:DEFAULT_MAX_QTY_STUDENT_REGISTRED_IN_COURSE;
$pmin = is_numeric($_REQUEST["pmin"])?$_REQUEST["pmin"]:DEFAULT_MIN_QTY_TEACHER_REGISTRED_IN_COURSE;
$pmax = is_numeric($_REQUEST["pmax"])?$_REQUEST["pmax"]:DEFAULT_MAX_QTY_TEACHER_REGISTRED_IN_COURSE;



$sfMail = strtoupper($_REQUEST["sfMail"]!=""?$_REQUEST["sfMail"]:DEFAUL_SUFFIX_MAIL);
$nbp = is_numeric($_REQUEST["nbp"])?$_REQUEST["nbp"]:DEFAULT_QTY_TEACHER;
$nbs = is_numeric($_REQUEST["nbs"])?$_REQUEST["nbs"]:DEFAULT_QTY_STUDENT;
$nbUsers = $nbp + $nbs;

$display = DISP_INSERT_COMPLETE;
$display = DISP_FORM_SET_OPTION;
@include("../checkIfHtAccessIsPresent.php");

include($includePath.'/claro_init_header.inc.php');
claro_disp_tool_title(
	array(
	'mainTitle'=>$nameTools,
	'subTitle'=>$PHP_AUTH_USER." - ".$siteName." - ".$clarolineVersion
	)
	);
claro_disp_msg_arr($controlMsg);
if (isset($HTTP_POST_VARS["nbp"]))
{
/* fillUSER */

	srand ((double) microtime() * 10000000);
	$firstnames = array (
		"jean", "marc", "françois", "laurent", "mathieu", "matthieu",
		"simon", "pol", "paul", "greg", "gregoire", "gregory", "albert", "alfred",
		"adolfe", "armile", "armand", "jeff", "jo", "jack", "john", "claire",
		"annie", "cécile", "roland", "mark", "koen", "dirk", "jan", "kim",
		"riri", "fifi", "loulou", "michel", "robin", "serge", "david", "augustin",
		"sofienne", "lucien", "roberto", "analysia", "jaana", "satu", "christian",
		"marie", "julie", "justine", "yves", "lucas", "teddy", "giovanni",
		"yurgen", "sven", "fabien", "fabian", "pierre", "mouloud", "kevin",
		"axel", "hervé", "lydéric", "manory", "aly", "francis", "charles",
		"cédric", "quentin", "miguel", "khalid", "bilal", "dries", "pieter",
		"kjell", "mehdi", "damien", "cyril", "michael", "jamil", "mustafa",
		"georges", "christophe", "hugues", "thomas", "lorant", "stéphanie",
		"martine", "aurélie", "caroline", "simone", "nathalie", "audette", "carole",
		"farid", "antonella", "graziella", "lauredanna",
		"lyne", "laure", "jean-luc", "luc", "Nathanaël", "kofi", "sigmund", "Mateus",
		"Jesus", "Steve", "dave", "alan", "alain", "andré", "andrew", "Tahar",
		"mowgli", "tom", "donald", "olivier", "dimitri", "joseph", "mohamed",
		"sambegou", "björn", "jinks", "Gonzague", "Onder", "kris", "ivan",
		"cheikh", "taner", "Moussa", "Louis", "amadou", "arnaud", "rosario",
		"tilio", "julio", "jules", "julos", "liviu", "celia", "magda", "youssef",
		"essam", "boumedian", "walit", "thierry","zeev","jamal","ali");

		$voyel		= array( "a","e","i","o","u");
		$consonne	= array("","b","c","d","f","j","k","l","m","n","p","r","s","t","v","z");

	$sqlUsers = "Select * from `".$tbl_user."`";
	$resUsers = mysql_query_dbg($sqlUsers);
	while ($users = mysql_fetch_array($resUsers,MYSQL_ASSOC))
	{
		if(ADD_FIRSTNAMES_FROM_BASE)	$firstnames[] 	= $users["prenom"];
		if(ADD_NAMES_FROM_BASE)			$names[] 		= $users["nom"];
		if(ADD_USERNAMES_FROM_BASE)		$usernames[] 	= $users["username"];
	}
	if(USE_FIRSTNAMES_AS_LASTNAMES)		$names 	= array_merge ( $names,$firstnames);


	unset($users);


	for($noUser=0;$noUser<=($nbUsers*10); $noUser++)
    {
		$nom ="";
		for($s=0;$s<rand(1,3); $s++)
		{
			$nom .= field_rand($consonne).field_rand($voyel).field_rand($consonne);
		}
		$names[] = $nom;
	}

	echo "<OL>";
	for($noUser=0;$noUser<=$nbUsers; $noUser++)
	{
		$statut = 5;
		if ($nbp-- > 0) $statut = 1;
		$nom = ucfirst(strToLower(field_rand($names)));
		$prenom = ucfirst(strToLower(field_rand($firstnames)));
		$username = strToLower($nom);
		$password = strToLower($nom.$prenom);
		$email = strToLower($prenom.".".$noUser).$sfMail;
		$sqlInsertUser = "
	INSERT INTO `".$tbl_user."`
	(
	`nom`, `prenom`,
	`username`, `password`,
	`email`, `statut`,
	`creatorId`)
	VALUES
	('".$nom."', '".$prenom."',
	'".$username."', '".$password."',
	'".$email."', $statut,
	'".$_uid."')
		";
		mysql_query_dbg($sqlInsertUser);
		$nbssAdded += mysql_affected_rows();
		$users[]= $prenom." ".$nom.", L/P ".$username." / ".$password;
	}

	$display=DISP_RESULT_INSERT;

}





//////////////// OUTPUT
switch ($display)
{
	case DISP_RESULT_INSERT :
		?>

		<?php echo $lang_you_had_request; ?> :
		<UL>
			<LI>
				<?php echo $HTTP_POST_VARS["nbp"] ." " . $langTeachers; ?></LI>
			<LI>
				<?php echo $HTTP_POST_VARS["nbs"] ." " . $langStudents; ?>
			</LI>
		</UL>

		<?php
			echo $nbssAdded." new users";
			if (CONFVAL_LIST_USER_ADDED)
			{
				echo "<OL><LI>";
				echo implode("</LI><LI>",$users);
				echo "</LI></OL>";
			}

		?>

			<UL class="menu">
				<LI>
					<a href="<?php echo $PHP_SELF ?>" >Again</a>
				</LI>
				<LI>
					<a href="<?php echo $rootAdminWeb ?>" >Admin</a>
				</LI>
			</UL>
		<?php
		break;
	case DISP_FORM_SET_OPTION :
		?><br><br>
<form action="<?php echo $PHP_SELF ?>" method="POST" enctype="multipart/form-data" target="_self">
	<fieldset>
	<legend >Users à créer</legend>
	<label for="nbp">Quantité de prof  : </label>
	<input align="right" type="text" id="nbp" name="nbp" value="<?php echo $nbp ?>" size="5" maxlength="3"><br>
	<label for="nbs">Quantité d'étudiants  : </label>
	<input align="right" type="text" id="nbs" name="nbs" value="<?php echo $nbs ?>" size="5" maxlength="4"><br>
	</fieldset>
	<!--fieldset >
	<legend >Données</legend>
	<Label for="nom">Nom : </Label>
	<Label for="selNameRandom" >Random</Label>
	<input type="radio" id="selNameRandom" name="selName" value="rand"><br>
	<input type="radio" name="selName" value="fix"><input type="text" id="nom" align="right" name="nom" value="<?php echo $nom ?>" size="10" maxlength="25"><br>

	<Label for="prenom">Prenom : </Label>
	<Label for="selFirstnameRandom" >Random</Label>
	<input type="radio" id="selFirstnameRandom" name="selFirstname" value="rand"><br>
	<input type="radio" name="selFirstname" value="fix">
	<input type="text" id="prenom" align="right" name="prenom" value="<?php echo $prenom ?>" size="10" maxlength="25"><br>

	<Label for="login">Login : </Label>
	<Label for="selFirstnameRandom" >Random</Label>
	<input type="radio" id="selFirstnameRandom" name="selUsername" value="rand"><br>
	<Label for="selFirstnameRandom" >Nom</Label>
	<input type="radio" id="" name="selUsername" value=""><br>
	<Label for="selFirstnameRandom" >Prenom</Label>
	<input type="radio" id="" name="selUsername" value=""><br>
	<input type="radio" name="selFirstname" value="fix">
	<input type="text" id="prenom" align="right" name="prenom" value="<?php echo $prenom ?>" size="10" maxlength="25"><br>
	</fieldset-->
	<input type="submit" name="create" value="create">
</form>
		<?php
		break;
	case COMPLETE_INSERT :
	?>
INSERT INTO `user` (`nom`, `prenom`, `username`, `password`, `email`, `statut`, `officialCode`, `phoneNumber`, `pictureUri`, `creatorId`) VALUES ('nom_1', 'PHILIPPE', 'login_1', 'pass_1', 'PHILIPPE.1@TEST.be', 5, NULL, NULL, NULL, NULL),
('tuvu', 'Thomas', 'login_2', 'pass_2', 'Thomas.2@TEST.be', 5, NULL, NULL, NULL, NULL),
('kiroulnamassepasmouss', 'PIERRE', 'login_3', 'pass_3', 'PIERRE.3@TEST.be', 5, NULL, NULL, NULL, NULL),
('kilou', 'PATRICIA', 'login_4', 'pass_4', 'PATRICIA.4@TEST.be', 5, NULL, NULL, NULL, NULL),
('sanchez', 'BENEDICTE', 'login_5', 'pass_5', 'BENEDICTE.5@TEST.be', 5, NULL, NULL, NULL, NULL),
('fredreic', 'FRANCOIS', 'login_6', 'pass_6', 'FRANCOIS.6@TEST.be', 5, NULL, NULL, NULL, NULL),
('plantin', 'BENOIT', 'login_8', 'pass_8', 'BENOIT.8@TEST.be', 5, NULL, NULL, NULL, NULL),
('depoit', 'STEPHANIE', 'login_9', 'pass_9', 'STEPHANIE.9@TEST.be', 5, NULL, NULL, NULL, NULL),
('dupont', 'SOPHIE', 'login_10', 'pass_10', 'SOPHIE.10@TEST.be', 5, NULL, NULL, NULL, NULL),
('depol', 'XAVIER', 'login_11', 'pass_11', 'XAVIER.11@TEST.be', 5, NULL, NULL, NULL, NULL),
('appert', 'MARC', 'login_12', 'pass_12', 'MARC.12@TEST.be', 5, NULL, NULL, NULL, NULL),
('bonneau', 'JEAN', 'login_13', 'pass_13', 'JEAN.13@TEST.be', 5, NULL, NULL, NULL, NULL),
('star', 'Guest', 'login_14', 'pass_14', 'Guest.14@TEST.be', 5, NULL, NULL, NULL, NULL),
('des combles', 'JEAN-PIERRE', 'login_15', 'pass_15', 'JEAN-PIERRE.15@TEST.be', 5, NULL, NULL, NULL, NULL),
('lepetit', 'GREGORY', 'login_16', 'pass_16', 'GREGORY.16@TEST.be', 5, NULL, NULL, NULL, NULL),
('mertens', 'THIERRY', 'login_17', 'pass_17', 'THIERRY.17@TEST.be', 5, NULL, NULL, NULL, NULL),
('lepeti', 'NICOLAS', 'login_18', 'pass_18', 'NICOLAS.18@TEST.be', 5, NULL, NULL, NULL, NULL),
('desaccises', 'JEAN-FRANCOIS', 'login_20', 'pass_20', 'JEAN-FRANCOIS.20@TEST.be', 5, NULL, NULL, NULL, NULL),
('legailuron', 'THIERRY', 'login_21', 'pass_21', 'THIERRY.21@TEST.be', 5, NULL, NULL, NULL, NULL),
('grenier', 'LAURENT', 'login_22', 'pass_22', 'LAURENT.22@TEST.be', 5, NULL, NULL, NULL, NULL),
('danstoncostumetouneuf', 'THIBAULT', 'login_23', 'pass_23', 'THIBAULT.23@TEST.be', 5, NULL, NULL, NULL, NULL),
('jadrin', 'ANNE-CECILE', 'login_24', 'pass_24', 'ANNE-CECILE.24@TEST.be', 5, NULL, NULL, NULL, NULL),
('onvasketterlboutik', 'ERIC', 'login_25', 'pass_25', 'ERIC.25@TEST.be', 5, NULL, NULL, NULL, NULL),
('zoal', 'THIERRY', 'login_26', 'pass_26', 'THIERRY.26@TEST.be', 5, NULL, NULL, NULL, NULL),
('dupont', 'BEATRICE', 'login_27', 'pass_27', 'BEATRICE.27@TEST.be', 5, NULL, NULL, NULL, NULL),
('richemont', 'GREGORY', 'login_28', 'pass_28', 'GREGORY.28@TEST.be', 5, NULL, NULL, NULL, NULL),
('rousseau', 'OLIVIER', 'login_29', 'pass_29', 'OLIVIER.29@TEST.be', 5, NULL, NULL, NULL, NULL),
('estencorepleindevaisselle', 'OLIVIER', 'login_30', 'pass_30', 'OLIVIER.30@TEST.be', 5, NULL, NULL, NULL, NULL),
('randour', 'FREDERIC', 'login_31', 'pass_31', 'FREDERIC.31@TEST.be', 5, NULL, NULL, NULL, NULL),
('klop', 'PIERRE', 'login_32', 'pass_32', 'PIERRE.32@TEST.be', 5, NULL, NULL, NULL, NULL),
('ediop', 'GERALDINE', 'login_33', 'pass_33', 'GERALDINE.33@TEST.be', 5, NULL, NULL, NULL, NULL),
('carpette', 'BERNARD', 'login_34', 'pass_34', 'BERNARD.34@TEST.be', 5, NULL, NULL, NULL, NULL),
('roman', 'SEBASTIEN', 'login_35', 'pass_35', 'SEBASTIEN.35@TEST.be', 5, NULL, NULL, NULL, NULL),
('albert', 'LAURENCE', 'login_36', 'pass_36', 'LAURENCE.36@TEST.be', 5, NULL, NULL, NULL, NULL),
('simon', 'PHILIPPE', 'login_37', 'pass_37', 'PHILIPPE.37@TEST.be', 5, NULL, NULL, NULL, NULL),
('toc', 'JEAN-PIERRE', 'login_38', 'pass_38', 'JEAN-PIERRE.38@TEST.be', 5, NULL, NULL, NULL, NULL),
('marillon', 'Hervé', 'login_39', 'pass_39', 'Hervé.39@TEST.be', 5, NULL, NULL, NULL, NULL),
('ponsable', 'THERESE', 'login_40', 'pass_40', 'THERESE.40@TEST.be', 5, NULL, NULL, NULL, NULL),
('vaulotre', 'QUENTIN', 'login_41', 'pass_41', 'QUENTIN.41@TEST.be', 5, NULL, NULL, NULL, NULL),
('chmonpote', 'THIERRY', 'login_42', 'pass_42', 'THIERRY.42@TEST.be', 5, NULL, NULL, NULL, NULL),
('torvallit', 'ARMAND', 'login_43', 'pass_43', 'ARMAND.43@TEST.be', 5, NULL, NULL, NULL, NULL),
('moulien', 'XAVIER', 'login_44', 'pass_44', 'XAVIER.44@TEST.be', 5, NULL, NULL, NULL, NULL),
('mertens', 'RUDY', 'login_45', 'pass_45', 'RUDY.45@TEST.be', 5, NULL, NULL, NULL, NULL),
('dupont', 'EMMANUEL', 'login_46', 'pass_46', 'EMMANUEL.46@TEST.be', 5, NULL, NULL, NULL, NULL),
('assil', 'QUENTIN', 'login_47', 'pass_47', 'QUENTIN.47@TEST.be', 5, NULL, NULL, NULL, NULL),
('larousse', 'OLIVIER', 'login_48', 'pass_48', 'OLIVIER.48@TEST.be', 5, NULL, NULL, NULL, NULL),
('lebrun', 'FRANCOIS', 'login_49', 'pass_49', 'FRANCOIS.49@TEST.be', 5, NULL, NULL, NULL, NULL),
('leroux', 'CECILE', 'login_50', 'pass_50', 'CECILE.50@TEST.be', 5, NULL, NULL, NULL, NULL),
('DUPONT', 'VINCENT', 'login_51', 'pass_51', 'VINCENT.51@TEST.be', 5, NULL, NULL, NULL, NULL),
('lejonc', 'MARIE', 'login_52', 'pass_52', 'MARIE.52@TEST.be', 5, NULL, NULL, NULL, NULL),
('creper', 'JULIEN', 'login_53', 'pass_53', 'JULIEN.53@TEST.be', 5, NULL, NULL, NULL, NULL),
('legrand', 'THOMAS', 'login_54', 'pass_54', 'THOMAS.54@TEST.be', 5, NULL, NULL, NULL, NULL),
('LEFRANC', 'JEAN-MARC', 'login_55', 'pass_55', 'JEAN-MARC.55@TEST.be', 5, NULL, NULL, NULL, NULL),
('de patribon', 'PIERRE-YVES', 'login_56', 'pass_56', 'PIERRE-YVES.56@TEST.be', 5, NULL, NULL, NULL, NULL),
('vandenbosh', 'OLIVIER', 'login_57', 'pass_57', 'OLIVIER.57@TEST.be', 5, NULL, NULL, NULL, NULL),
('namour', 'CECILE', 'login_58', 'pass_58', 'CECILE.58@TEST.be', 5, NULL, NULL, NULL, NULL),
('SIMON', 'OLIVIER', 'login_59', 'pass_59', 'OLIVIER.59@TEST.be', 5, NULL, NULL, NULL, NULL),
('deparallah', 'GUILLAUME', 'login_60', 'pass_60', 'GUILLAUME.60@TEST.be', 5, NULL, NULL, NULL, NULL),
('mertens', 'HAROLD', 'login_61', 'pass_61', 'HAROLD.61@TEST.be', 5, NULL, NULL, NULL, NULL),
('et belle', 'SEBASTIEN', 'login_62', 'pass_62', 'SEBASTIEN.62@TEST.be', 5, NULL, NULL, NULL, NULL),
('rousseau', 'MATTHIEU', 'login_63', 'pass_63', 'MATTHIEU.63@TEST.be', 5, NULL, NULL, NULL, NULL),
('aturner', 'CHRISTINE', 'login_64', 'pass_64', 'CHRISTINE.64@TEST.be', 5, NULL, NULL, NULL, NULL),
('POM', 'PASCALE', 'login_65', 'pass_65', 'PASCALE.65@TEST.be', 5, NULL, NULL, NULL, NULL),
('thumaspas', 'MARIE', 'login_66', 'pass_66', 'MARIE.66@TEST.be', 5, NULL, NULL, NULL, NULL),
('lagnaux', 'PASCAL', 'login_67', 'pass_67', 'PASCAL.67@TEST.be', 5, NULL, NULL, NULL, NULL),
('tuop', 'DAMIEN', 'login_68', 'pass_68', 'DAMIEN.68@TEST.be', 5, NULL, NULL, NULL, NULL),
('marchaistoutsimplement', 'VERONIQUE', 'login_69', 'pass_69', 'VERONIQUE.69@TEST.be', 5, NULL, NULL, NULL, NULL),
('hochet', 'FREDERIC', 'login_70', 'pass_70', 'FREDERIC.70@TEST.be', 5, NULL, NULL, NULL, NULL),
('titude', 'NICOLAS', 'login_71', 'pass_71', 'NICOLAS.71@TEST.be', 5, NULL, NULL, NULL, NULL),
('tropet', 'CEDRIC', 'login_72', 'pass_72', 'CEDRIC.72@TEST.be', 5, NULL, NULL, NULL, NULL),
('decourchevel', 'FABRICE', 'login_73', 'pass_73', 'FABRICE.73@TEST.be', 5, NULL, NULL, NULL, NULL),
('assin', 'MARC', 'login_74', 'pass_74', 'MARC.74@TEST.be', 5, NULL, NULL, NULL, NULL),
('olé', 'CEDRIC', 'login_75', 'pass_75', 'CEDRIC.75@TEST.be', 5, NULL, NULL, NULL, NULL),
('mertens', 'STEPHANE', 'login_76', 'pass_76', 'STEPHANE.76@TEST.be', 5, NULL, NULL, NULL, NULL),
('erac', 'FREDERIC', 'login_77', 'pass_77', 'FREDERIC.77@TEST.be', 5, NULL, NULL, NULL, NULL),
('hie', 'ALEXANDRE', 'login_78', 'pass_78', 'ALEXANDRE.78@TEST.be', 5, NULL, NULL, NULL, NULL),
('lacroix', 'ISABELLE', 'login_79', 'pass_79', 'ISABELLE.79@TEST.be', 5, NULL, NULL, NULL, NULL),
('rousseau', 'PHILIPPE', 'login_80', 'pass_80', 'PHILIPPE.80@TEST.be', 5, NULL, NULL, NULL, NULL),
('cocacolaro', 'STEPHANE', 'login_81', 'pass_81', 'STEPHANE.81@TEST.be', 5, NULL, NULL, NULL, NULL),
('linux', 'YANNICK', 'login_82', 'pass_82', 'YANNICK.82@TEST.be', 5, NULL, NULL, NULL, NULL),
('dupont', 'ANNE', 'login_83', 'pass_83', 'ANNE.83@TEST.be', 5, NULL, NULL, NULL, NULL),
('lebilan', 'SAMUEL', 'login_84', 'pass_84', 'SAMUEL.84@TEST.be', 5, NULL, NULL, NULL, NULL),
('verichtingen', 'MICHAEL', 'login_85', 'pass_85', 'MICHAEL.85@TEST.be', 5, NULL, NULL, NULL, NULL),
('di rupa', 'JEAN-PHILIPPE', 'login_86', 'pass_86', 'JEAN-PHILIPPE.86@TEST.be', 5, NULL, NULL, NULL, NULL),
('detree', 'JONATHAN', 'login_87', 'pass_87', 'JONATHAN.87@TEST.be', 5, NULL, NULL, NULL, NULL),
('vilo', 'VINCENT', 'login_88', 'pass_88', 'VINCENT.88@TEST.be', 5, NULL, NULL, NULL, NULL),
('randour', 'GARY', 'login_89', 'pass_89', 'GARY.89@TEST.be', 5, NULL, NULL, NULL, NULL),
('loiseau', 'CHRISTELLE', 'login_90', 'pass_90', 'CHRISTELLE.90@TEST.be', 5, NULL, NULL, NULL, NULL),
('mertens', 'MAGALI', 'login_91', 'pass_91', 'MAGALI.91@TEST.be', 5, NULL, NULL, NULL, NULL),
('ro', 'DENIS', 'login_92', 'pass_92', 'DENIS.92@TEST.be', 5, NULL, NULL, NULL, NULL),
('time', 'VINCENT', 'login_93', 'pass_93', 'VINCENT.93@TEST.be', 5, NULL, NULL, NULL, NULL),
('terieur', 'ALAIN', 'login_94', 'pass_94', 'ALAIN.94@TEST.be', 5, NULL, NULL, NULL, NULL),
('thodus', 'ANDRE', 'login_95', 'pass_95', 'ANDRE.95@TEST.be', 5, NULL, NULL, NULL, NULL),
('de cajoux', 'BENOIT', 'login_96', 'pass_96', 'BENOIT.96@TEST.be', 5, NULL, NULL, NULL, NULL),
('rousseau', 'PIERRE-FRANCOIS', 'login_97', 'pass_97', 'PIERRE-FRANCOIS.97@TEST.be', 5, NULL, NULL, NULL, NULL),
('mac quick', 'RONNALD', 'login_98', 'pass_98', 'RONNALD.98@TEST.be', 5, NULL, NULL, NULL, NULL),
('dupont', 'CELINE', 'login_99', 'pass_99', 'CELINE.99@TEST.be', 5, NULL, NULL, NULL, NULL),
('tif', 'VERENA', 'login_100', 'pass_100', 'VERENA.100@TEST.be', 5, NULL, NULL, NULL, NULL),
('tulora', 'SEBASTIEN', 'login_101', 'pass_101', 'SEBASTIEN.101@TEST.be', 5, NULL, NULL, NULL, NULL),
('hure', 'VINCIANE', 'login_102', 'pass_102', 'VINCIANE.102@TEST.be', 5, NULL, NULL, NULL, NULL),
('jabon', 'GUILLAUME', 'login_103', 'pass_103', 'GUILLAUME.103@TEST.be', 5, NULL, NULL, NULL, NULL),
('guillet-laverdur', 'JONATHAN', 'login_104', 'pass_104', 'JONATHAN.104@TEST.be', 5, NULL, NULL, NULL, NULL),
('atik', 'STEPHANE', 'login_105', 'pass_105', 'STEPHANE.105@TEST.be', 5, NULL, NULL, NULL, NULL),
('mertens', 'OLIVIER', 'login_106', 'pass_106', 'OLIVIER.106@TEST.be', 5, NULL, NULL, NULL, NULL),
('de coco', 'BENOIT', 'login_107', 'pass_107', 'BENOIT.107@TEST.be', 5, NULL, NULL, NULL, NULL),
('qualor', 'NICOLAS', 'login_108', 'pass_108', 'NICOLAS.108@TEST.be', 5, NULL, NULL, NULL, NULL),
('yluke', 'LUC', 'login_109', 'pass_109', 'LUC.109@TEST.be', 5, NULL, NULL, NULL, NULL),
('hémad-kartier', 'FRANCINE', 'login_110', 'pass_110', 'FRANCINE.110@TEST.be', 5, NULL, NULL, NULL, NULL),
('leleux', 'FREDERIQUE', 'login_111', 'pass_111', 'FREDERIQUE.111@TEST.be', 5, NULL, NULL, NULL, NULL),
('Pujol', 'ESTELLE', 'login_112', 'pass_112', 'ESTELLE.112@TEST.be', 5, NULL, NULL, NULL, NULL),
('plote', 'DAVID', 'login_113', 'pass_113', 'DAVID.113@TEST.be', 5, NULL, NULL, NULL, NULL),
('rimbou', 'ANNE-FRANCOISE', 'login_114', 'pass_114', 'ANNE-FRANCOISE.114@TEST.be', 5, NULL, NULL, NULL, NULL),
('rousseau', 'JOHAN', 'login_115', 'pass_115', 'JOHAN.115@TEST.be', 5, NULL, NULL, NULL, NULL),
('scheppens', 'CHRISTOPHE', 'login_116', 'pass_116', 'CHRISTOPHE.116@TEST.be', 5, NULL, NULL, NULL, NULL),
('Pujol', 'VINCIANE', 'login_117', 'pass_117', 'VINCIANE.117@TEST.be', 5, NULL, NULL, NULL, NULL),
('legat', 'FREDERIC', 'login_118', 'pass_118', 'FREDERIC.118@TEST.be', 5, NULL, NULL, NULL, NULL),
('falipet', 'PIERRE', 'login_119', 'pass_119', 'PIERRE.119@TEST.be', 5, NULL, NULL, NULL, NULL),
('dupont', 'CHRISTOPHE', 'login_120', 'pass_120', 'CHRISTOPHE.120@TEST.be', 5, NULL, NULL, NULL, NULL),
('mertens', 'LAURENCE', 'login_121', 'pass_121', 'LAURENCE.121@TEST.be', 5, NULL, NULL, NULL, NULL),
('dupont', 'ISABELLE', 'login_122', 'pass_122', 'ISABELLE.122@TEST.be', 5, NULL, NULL, NULL, NULL),
('dugnenoux', 'YANNICK', 'login_123', 'pass_123', 'YANNICK.123@TEST.be', 5, NULL, NULL, NULL, NULL),
('de la bruyere', 'ANNE-CATHERINE', 'login_124', 'pass_124', 'ANNE-CATHERINE.124@TEST.be', 5, NULL, NULL, NULL, NULL),
('dupont', 'CEDRIC', 'login_125', 'pass_125', 'CEDRIC.125@TEST.be', 5, NULL, NULL, NULL, NULL),
('sarcomaxicosi', 'NICOLAS', 'login_126', 'pass_126', 'NICOLAS.126@TEST.be', 5, NULL, NULL, NULL, NULL),
('Pujol', 'LUCY', 'login_127', 'pass_127', 'LUCY.127@TEST.be', 5, NULL, NULL, NULL, NULL),
('fil', 'SEBASTIEN', 'login_128', 'pass_128', 'SEBASTIEN.128@TEST.be', 5, NULL, NULL, NULL, NULL),
('Pujol', 'JONATHAN', 'login_129', 'pass_129', 'JONATHAN.129@TEST.be', 5, NULL, NULL, NULL, NULL),
('rousseau', 'FRANCOIS', 'login_130', 'pass_130', 'FRANCOIS.130@TEST.be', 5, NULL, NULL, NULL, NULL),
('kostilanov', 'VLADIMIR', 'login_131', 'pass_131', 'VLADIMIR.131@TEST.be', 5, NULL, NULL, NULL, NULL),
('smith', 'DONALD', 'login_132', 'pass_132', 'DONALD.132@TEST.be', 5, NULL, NULL, NULL, NULL),
('richmont', 'MICHEL', 'login_133', 'pass_133', 'MICHEL.133@TEST.be', 5, NULL, NULL, NULL, NULL),
('erbot', 'SANDRINE', 'login_134', 'pass_134', 'SANDRINE.134@TEST.be', 5, NULL, NULL, NULL, NULL),
('vantrinpont', 'CHRISTOPHE', 'login_135', 'pass_135', 'CHRISTOPHE.135@TEST.be', 5, NULL, NULL, NULL, NULL),
('dupont', 'ALEXIA', 'login_136', 'pass_136', 'ALEXIA.136@TEST.be', 5, NULL, NULL, NULL, NULL),
('mertens', 'NATHALIE', 'login_137', 'pass_137', 'NATHALIE.137@TEST.be', 5, NULL, NULL, NULL, NULL),
('van gogh', 'FRANCOISE', 'login_138', 'pass_138', 'FRANCOISE.138@TEST.be', 5, NULL, NULL, NULL, NULL),
('lassus', 'STEPHANE', 'login_139', 'pass_139', 'STEPHANE.139@TEST.be', 5, NULL, NULL, NULL, NULL),
('dupont', 'DAVID', 'login_140', 'pass_140', 'DAVID.140@TEST.be', 5, NULL, NULL, NULL, NULL),
('alézieubleu', 'ISABELLE', 'login_141', 'pass_141', 'ISABELLE.141@TEST.be', 5, NULL, NULL, NULL, NULL),
('O\' susscion', 'PHILIPPE', 'login_142', 'pass_142', 'PHILIPPE.142@TEST.be', 5, NULL, NULL, NULL, NULL),
('dupont', 'CHRISTOPHE', 'login_143', 'pass_143', 'CHRISTOPHE.143@TEST.be', 5, NULL, NULL, NULL, NULL),
('pennac', 'STEPHANE', 'login_144', 'pass_144', 'STEPHANE.144@TEST.be', 5, NULL, NULL, NULL, NULL),
('durieux', 'DENIS', 'login_145', 'pass_145', 'DENIS.145@TEST.be', 5, NULL, NULL, NULL, NULL),
('durant', 'CHRISTIAN', 'login_146', 'pass_146', 'CHRISTIAN.146@TEST.be', 5, NULL, NULL, NULL, NULL),
('abelatchixtchix', 'CATHERINE', 'login_147', 'pass_147', 'CATHERINE.147@TEST.be', 5, NULL, NULL, NULL, NULL),
('rousseau', 'GUERRIC', 'login_148', 'pass_148', 'GUERRIC.148@TEST.be', 5, NULL, NULL, NULL, NULL),
('tupousslebouchonunpeuloin', 'MAURICE', 'login_149', 'pass_149', 'MAURICE.149@TEST.be', 5, NULL, NULL, NULL, NULL),
('sarly', 'JEAN-PAUL', 'login_150', 'pass_150', 'JEAN-PAUL.150@TEST.be', 5, NULL, NULL, NULL, NULL),
('dupont', 'Marc', 'login_151', 'pass_151', 'Marc.151@TEST.be', 5, NULL, NULL, NULL, NULL),
('poret', 'Quentin', 'login_152', 'pass_152', 'Quentin.152@TEST.be', 5, NULL, NULL, NULL, NULL),
('moulin', 'Anne', 'login_153', 'pass_153', 'Anne.153@TEST.be', 5, NULL, NULL, NULL, NULL),
('cottet', 'Cécile', 'login_154', 'pass_154', 'Cécile.154@TEST.be', 5, NULL, NULL, NULL, NULL),
('rousseau', 'Mathieu', 'login_155', 'pass_155', 'Mathieu.155@TEST.be', 5, NULL, NULL, NULL, NULL),
('secret', 'Christophe', 'login_158', 'pass_158', 'Christophe.158@TEST.be', 5, NULL, NULL, NULL, NULL),
('dupont', 'Thomas', 'login_160', 'pass_160', 'Thomas.160@TEST.be', 5, NULL, NULL, NULL, NULL),
('dufront', 'jacques', 'login_163', 'pass_163', 'jacques.163@TEST.be', 5, NULL, NULL, NULL, NULL);

	<?php
	default : "hum erreur de display";

}

function field_rand($arr)
{
	$rand_keys	= array_rand ($arr);
	return $arr[$rand_keys];
}

?>
