<?php # $Id$
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.5.*
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2003 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      | Authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
      |          Hugues Peeters    <peeters@ipm.ucl.ac.be>                   |
      |      	 Christophe Gesché  <gesche@ipm.ucl.ac.be>                   |
      +----------------------------------------------------------------------+
	  |   English Translation                                                |
      +----------------------------------------------------------------------+
      | Translator :                                                         |
      |          Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
      |          Andrew Lynn       <Andrew.Lynn@strath.ac.uk>                |
      +----------------------------------------------------------------------+
 */
$langEG 			= "e. g.";
$langDBConnectionParameters = "Mysql connection parameters";
$lang_Note_this_account_would_be_existing ="Note : this account would be existing";
$langDBHost			= "Database Host";
$langDBLogin		= "Database Username";
$langDBPassword 	= "Database Password";
$langDBNamesRules	= "Database Names";
$langMainDB			= "Main claroline DB"; // show in multi DB
$langStatDB			= "Tracking DB.";// show in multi DB
$langPMADB			= "DB for extention of PhpMyAdmin";// show in multi DB
$langDbName			= "DB Name"; // show in single DB
$langDBUse			= "Database usage";
$langEnableTracking     = "Enable Tracking";
$langAllFieldsRequired	= "all fields required";
$langPrintVers			= "Printable version";
$langLocalPath			= "Corresponding local path";
$langAdminEmail			= "Administrator email";
$langAdminName			= "Administrator name";
$langAdminSurname		= "Administrator surname";
$langAdminLogin			= "Administrator login";
$langAdminPass			= "Administrator password";
$langEducationManager	= "Education manager";
$langHelpDeskPhone		= "Helpdesk telephone";
$langCampusName			= "Your campus' name";
$langInstituteShortName = "Organisation short name";
$langInstituteName		= "URL of this organisation";


$langDBSettingIntro		= "
				Install script will create claroline main DB. Please note that Claroline
				will need to create many DBs (unless you select option \"One\" below). If you are allowed only one
				DB for your website by your Hosting Services, Claroline will not work.";
$langDBSettingAccountIntro		= "
				Claroline is build to work with many DBs but can works with only one Db,
				To work with many DBs, your account need to have Db creation right.<BR>
				If you are allowed only one
				DB for your website by your Hosting Services, You need select option \"One\" below.";
$langDBSettingNamesIntro		= "
				Install script will create central DB for
				claroline main data, tracking data and PhpMyAdmin relation data.
				Choose names for these Db and a prefix for future Courses DB.<BR>
				<B>You can use the same base for many central DB</B><BR>
				If you are allowed only one DB, back to previous page and select option \"One\"";
$langDBSettingNameIntro		= "
				Install script will create table of claroline main, tracking and PhpMyAdmin relation DB in your
				single DB.
				Choose name for these Db and a prefix for future Courses Tables.<BR>
				If you are allowed to create many DB, back to previous page and select option \"Several\".
				It's really more convivial for use";
$langStep1 			= "Step 1 of 6 ";
$langStep2 			= "Step 2 of 6 ";
$langStep3 			= "Step 3 of 6 ";
$langStep4 			= "Step 4 of 6 ";
$langStep5 			= "Step 5 of 6 ";
$langStep6 			= "Step 6 of 6 ";
$langCfgSetting		= "Config settings";
$langDBSetting 		= "MySQL database settings";
$langMainLang 		= "Main language";
$langLicence		= "License";
$langLastCheck		= "Last check before install";
$langRequirements	= "Requirements";

$langDbPrefixForm	= "Prefix Name for Course Db";
$langTbPrefixForm	= "Prefix Name for Course Table";
$langDbPrefixCom	= "e.g. 'CL_'";
$langEncryptUserPass	= "Encrypt user passwords in database";
$langSingleDb	= "Use one or several DB for Claroline";


$langWarningResponsible = "Use this script only after backup. Claroline team is not responsible if you lost or corrupted data";
$langAllowSelfReg	=	"Allow self-registration";
$langAllowSelfRegProf =	"Allow self-registration as course creator";
$langRecommended	=	"(recommended)";


?>
