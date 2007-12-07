<?php // $Id$
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.5.*                              |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2004 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      |   German Translation                                                 |
      +----------------------------------------------------------------------+

      +----------------------------------------------------------------------+
      | Translator :                                                         |
      |          Ralf Hilgenstock  <rh@dialoge.net>                |
      +----------------------------------------------------------------------+
      | revised and complemented: 2004/09/22 by								 |
	  | Udo Scharf  <UScharf@netway-online.de>     							 |
      +----------------------------------------------------------------------+

 */
$langEG               = "z. B.";
$langDBHost           = "Datenbank Host";
$langDBLogin          = "Datenbank Username";
$langDBPassword       = "Datenbank Passwort";
$langMainDB           = "Haupt-Claroline DB";
$langStatDB           = "Tracking über Datenbank (Nur sinnvoll in der Multidatenbankinstallation)";
$langEnableTracking   = "Tracking aktivieren";
$langAllFieldsRequired = "alle Felder erforderlich";
$langPrintVers         = "Druckversion";
$langLocalPath         = "Zugehöriger lokaler Pfad (absoluter Pfad)";
$langAdminEmail        = "Administrator E-Mail";
$langAdminName         = "Administrator Nachname";
$langAdminSurname      = "Administrator Vorname";
$langAdminLogin        = "Administrator Username";
$langAdminPass         = "Administrator Passwort";
$langEducationManager  = "Bildungsverantwortlicher";
$langHelpDeskPhone     = "Telefonservice";
$langCampusName        = "Der Name Ihrer Lernumgebung";
$langInstituteShortName = "Name Organisation(Kurzform)";
$langInstituteName     = "URL der Organisation";


$langDBConnectionParameters = "MySQK Connection Parameter";
$lang_Note_this_account_would_be_existing ="HINWEIS : Dieser Zugang würde existieren";
$langDBNamesRules	= "Namen der Datenbanken";
$langPMADB			= "DB for extention of PhpMyAdmin";// show in multi DB
$langDbName			= "DB Name"; // show in single DB
$langDBUse			= "Database usage";
$langDBSettingIntro                = "
                                Installatioonprozess erstellt die Datenbank. Claroline legt mehrere Datenbanken an
                                 (außer wenn Sie die Option \"One\" wählen). Bei vielen Providern können nur eine begrenzte Anzhal von mySQL-Datenbanken angelegt werden";
$langDBSettingAccountIntro		= "
				Claroline is build to work with many DBs but can works with only one Db,
				To work with many DBs, your account need to have Db creation right.<BR>
				If you are allowed only one
				DB for your website by your Hosting Services, You need select option \"One\" below.";
$langDBSettingNamesIntro		= "
				Install script will create main claroline databases. 
				You can create different database 
				for tracking and PhpMyAdmin extension if you want 
				or gathering all these stuff in one database, like you want. 
				Afterwards, Claroline will create a new database for each new course created. 
				You can specify a prefix for these database names.
				<p>
				If you are allowed to use only one database by your database system administrator, 
				get back to the previous page and select option \"Single\"
				</p>
				";
$langDBSettingNameIntro		= "
				Install script will create table of claroline main, tracking and PhpMyAdmin relation DB in your
				single DB.
				Choose name for these Db and a prefix for future Courses Tables.<BR>
				If you are allowed to create many DB, back to previous page and select option \"Several\".
				It's really more convivial for use";
$langStep1      = "Schritt 1 von 8 ";
$langStep2      = "Schritt 2 von 8 ";
$langStep3      = "Schritt 3 von 8 ";
$langStep4      = "Schritt 4 von 8 ";
$langStep5      = "Schritt 5 von 8 ";
$langStep6      = "Schritt 6 von 8 ";
$langStep7 		= "Schritt 7 von 8 ";
$langStep8 		= "Schritt 8 von 8 ";

$langCfgSetting  = "Konfiguratonseinstellungen";
$langDBSetting   = "MySQL Datenbankeinstellungen";
$langMainLang    = "Haupt-Sprache";
$langLicence     = "Lizenz";
$langLastCheck   = "Letzte Übersicht vor der Installation";
$langRequirements = "Anforderungen";

$langDbPrefixForm  = "MySQL Präfix";
$langDbPrefixCom   = "Leer lassen, wenn nicht benötigt";
$langEncryptUserPass  = "Passwörter in Datenbank verschlüsseln";
$langSingleDb        = "Nutzen Sie eine oder mehrere Datenbanken für Claroline";

$langWarningResponsible =  "Benutzen Sie dieses Script erst nach einem Backup. Das Claroline Team trägt keine Verantwortung bei Datenverlust /-beschädigung";
$langAllowSelfReg       =  "Selbstregistrierung für Lerner erlauben";
$langAllowSelfRegProf   =  "Selbstregistrierung als Tutor/Dozent erlauben";
$langRecommended        =  "(empfohlen)";
?>