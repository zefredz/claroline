<?php // $Id$
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.5.0 $Revision$
      +----------------------------------------------------------------------
      | Copyright (c) 2001, 2004 Universite catholique de Louvain (UCL)
      +----------------------------------------------------------------------+
      |   This program is free software; you can redistribute it and/or
      |   modify it under the terms of the GNU General Public License
      |   as published by the Free Software Foundation; either version 2
      |   of the License, or (at your option) any later version.
      +----------------------------------------------------------------------+
      | Authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>
      |          Hugues Peeters    <peeters@ipm.ucl.ac.be>
      |          Christophe Gesché <gesche@ipm.ucl.ac.be>
      +----------------------------------------------------------------------+
 */

$englishLangName = "french";
$localLangName = "français";

$iso639_2_code = "fr";
$iso639_1_code = "fre";

$langNameOfLang['arabic'		] = "arabe";
$langNameOfLang['brazilian'		] = "brésilien";
$langNameOfLang['croatian'		] = "croate";
$langNameOfLang['catalan'		] = "catalan";
$langNameOfLang['dutch'			] = "néerlandais";
$langNameOfLang['english'		] = "anglais";
$langNameOfLang['finnish'		] = "finlandais";
$langNameOfLang['french'		] = "français";
$langNameOfLang['german'		] = "allemand";
$langNameOfLang['greek'			] = "grec";
$langNameOfLang['italian'		] = "italien";
$langNameOfLang['japanese'		] = "japonais"; // JCC 
$langNameOfLang['polish'		] = "polonais";
$langNameOfLang['simpl_chinese'	] ="chinois simple";
$langNameOfLang['spanish'		] = "espagnol";
$langNameOfLang['swedish'		] = "suédois";
$langNameOfLang['thai'			] = "thaïlandais";
$langNameOfLang['turkish'		] = "turc";

$charset = 'iso-8859-1';
$text_dir = 'ltr';
$left_font_family = 'verdana, helvetica, arial, geneva, sans-serif';
$right_font_family = 'helvetica, arial, geneva, sans-serif';
$number_thousands_separator = ' ';
$number_decimal_separator = ',';
$byteUnits = array('Octets', 'Ko', 'Mo', 'Go');

$langDay_of_weekNames['init'] = array('D', 'L', 'M', 'M', 'J', 'V', 'S');
$langDay_of_weekNames['short'] = array('Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'); // JCC 
$langDay_of_weekNames['long'] = array('Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi');

$langMonthNames['init']  = array('J', 'F', 'M', 'A', 'M', 'J', 'J', 'A', 'S', 'O', 'N', 'D');
$langMonthNames['short'] = array('Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc');
$langMonthNames['long'] = array('Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre');

// Voir http://www.php.net/manual/en/function.strftime.php pour la variable
// ci-dessous

$dateFormatShort =  "%a %d %b %y";
$dateFormatLong  = '%A %d %B %Y';
$dateTimeFormatLong  = '%A %d %B %Y à %H:%M';
$timeNoSecFormat = '%H:%M';
// GENERIC
$langYes="Oui";
$langNo="Non";
$langBack="Retour";
$langNext="Suivant";
$langAllowed="Autorisé";
$langDenied="Refusé";
$langBackHome="Retour à la page principale";
$langPropositions="Propositions d'amélioration de";
$langMaj="Mise à jour";
$langModify="Modifier";
$langDelete="Effacer";
$langMove="Déplacer";
$langTitle="Titre";
$langHelp="Aide";
$langOk="Valider";
$langAdd="Ajouter";
$langAddIntro="Ajouter un texte d'introduction";
$langBackList="Retour à la liste";
$langText="Texte";
$langEmpty="Vide";
$langConfirmYourChoice="Veuillez confirmer votre choix";
$langAnd="et";
$langChoice="Votre choix";
$langFinish="Terminer";
$langCancel="Annuler";
$langNotAllowed="Vous n'êtes pas autorisé à accéder à cette section";
$langManager="Responsable";
$langPlatform="Utilise la plate-forme";
$langOptional="Facultatif";
$langNextPage="Page suivante";
$langPreviousPage="Page précédente";
$langUse="Utiliser";
$langTotal="Total";
$langTake="prendre";
$langOne="Une";
$langSeveral="Plusieurs";
$langNotice="Remarque";
$langDate="Date";
$langAmong="parmi";


// banner
$langMyCourses="Liste de mes cours";
$langModifyProfile="Modifier mon profil";
$langPlatformAdmin="Administration";
$langMyAgenda = "Mon agenda";
$langLogout="Quitter";

$lang_this_course_is_protected = 'Ce cours est protégé';
$lang_enter_your_user_name_and_password = "Tapez votre nom d'utilisateur et votre mot de passe"; // JCC
$lang_if_you_dont_have_a_user_account_on = "Si vous n'avez pas encore de compte utilisateur sur"; // JCC 
$lang_click_here = 'cliquez ici';
$lang_your_user_profile_doesnt_seem_to_be_enrolled_to_this_course = "Votre profil utilisateur ne semble pas être inscrit à ce cours";
$lang_if_you_wish_to_enroll_to_this_course = "Si vous souhaitez vous inscrire à ce cours,";


// TOOLS NAMES
$langCourseHome = "Accueil"; // JCC 

$langAgenda="Agenda";
$langLink="Liens";
$langDocument="Documents et liens";
$langWork="Travaux";
$langAnnouncement="Annonces";
$langUser="Utilisateurs";
$langForum="Forums";
$langExercise="Exercices";
$langGroups ="Groupes";
$langChat ="Discussion";
$langLearnPath="Parcours pédagogique";
$langDescriptionCours  = "Description du cours";
$langCourseManagerview = "Responsable du cours";
$lang_footer_CourseManager = "Responsable(s) du cours";
$langPlatformAdministration = "Administration";
?>