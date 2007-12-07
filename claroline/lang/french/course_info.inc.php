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

/***************************************************************
*                   Language translation
****************************************************************
GOAL
****
Translate the interface in chosen language
*****************************************************************/

$langLabelCourseAdmin = "Administration du cours"; // JCC
$langModifInfo="Propriétés du cours";
$langModifDone="Les informations ont été modifiées";
$langHome="Retour à la page d'accueil";
$langCode="Code du cours";
$langDelCourse="Supprimer ce cours";
$langProfessor="Titulaire";
$langProfessors="Titulaires";
$langTitle="Intitulé";
$langFaculty="Faculté";
$langDescription="Description";
$langConfidentiality="Confidentialité";
$langPublic="Accès public (depuis la page d'accueil de Claroline sans identifiant)";
$langPrivOpen="Accès privé, inscription ouverte";
$langPrivate="Accès privé (site réservé aux personnes figurant dans la liste <a href=../user/user.php>utilisateurs</a>)";
$langForbidden="Accès non autorisé";
$langLanguage="Langue";
$langConfTip="Par défaut, votre cours est accessible à tout le monde. Si vous souhaitez un minimum de confidentialité, le plus simple est d'ouvrir
l'inscription pendant une semaine, de demander aux étudiants de s'inscrire eux-mêmes
puis de fermer l'inscription et de vérifier dans la liste des utilisateurs les intrus éventuels.";
$langTipLang="Cette langue vaudra pour tous les visiteurs de votre site de cours.";
$langEditToolList="Modifier la liste d'outils";
$langIntroCourse="Bienvenue sur la page d'accueil du cours.<br /><br />Vous pouvez sur cette page :
<li class=HelpText>activer ou désactiver des outils (cliquer sur le bouton '".$langEditToolList."' dans le bas à gauche).
<li class=HelpText>changer les propriétés ou voir les statistiques (Cliquer sur les liens correspondants).<br /><br />
Pour présenter votre cours aux étudiants, cliquer sur ce bouton.<br />";

// Change Home Page
$langUplPage="Déposer une page et la lier à l\'accueil"; // JCC 
$langLinkSite="Ajouter un lien sur la page d\'accueil";
$langVid="Vidéo";
$langProgramMenu="Cahier des charges";
$langStats="Statistiques";

// delete_course.php
$langDelCourse="Supprimer la totalité du cours";
$langCourse="Le cours ";
$langHasDel="a été supprimé";
$langBackHome="Retour à la page d'accueil de ";
$langByDel="En supprimant ce site, vous supprimerez tous les documents
qu'il contient et radierez tous les étudiants qui y sont inscrits. <p>Voulez-vous réellement supprimer le cours"; // JCC 
$langY="OUI";
$langN="NON";

$langDepartmentUrl = "URL du département"; // JCC
$langDepartmentUrlName = "Département";
$langEmail="E-mail"; // JCC

$langArchive="Archive";
$langArchiveCourse = "Archivage du cours";
$langRestoreCourse = "Restauration d'un cours";
$langRestore="Restaurer";
$langCreatedIn = "créé dans";
$langCreateMissingDirectories ="Création des répertoires manquants";
$langCopyDirectoryCourse = "Copie des fichiers du cours";
$langDisk_free_space = "Espace libre";
$langBuildTheCompressedFile ="Création du fichier compressé";
$langFileCopied = "fichier copié";
$langArchiveLocation = "Emplacement de l'archive";
$langSizeOf ="Taille de";
$langArchiveName ="Nom de l'archive";
$langBackupSuccesfull = "Archivé avec succès";
$langBUCourseDataOfMainBase = "Archivage des données du cours dans la base de données principale pour";
$langBUUsersInMainBase = "Archivage des données des utilisateurs dans la base de données principale pour";
$langBUAnnounceInMainBase="Archivage des données des annonces dans la base de données principale pour";
$langBackupOfDataBase="Archivage de la base de données";
$langBackupCourse="Archiver ce cours";

$langCreationDate = "Créé";
$langExpirationDate  = "Date d'expiration";
$langPostPone = "Post pone"; // JCC ???
$langLastEdit = "Dernière édition";
$langLastVisit = "Dernière visite";

$langSubscription="Inscription";
$langCourseAccess="Accès au cours";

$langDownload="Télécharger";
$langConfirmBackup="Voulez-vous vraiment archiver le cours";

$langCreateSite="Créer un site de cours";

$langRestoreDescription="Le cours se trouve dans une archive que vous pouvez sélectionner ci-dessous.<br><br>
Lorsque vous aurez cliqué sur 'Restaurer', l'archive sera décompressée et le cours recréé."; // JCC
$langRestoreNotice="Ce script ne permet pas encore la restauration automatique des utilisateurs, mais les données sauvegardées dans le fichier 'users.csv' sont suffisantes pour que l'administrateur puisse effectuer cette opération manuellement."; // JCC
$langAvailableArchives="Liste des archives disponibles";
$langNoArchive="Aucune archive n'a été sélectionnée";
$langArchiveNotFound="Archive introuvable";
$langArchiveUncompressed="L'archive a été décompressée et installée.";
$langCsvPutIntoDocTool="Le fichier 'users.csv' a été placé dans l'outil Documents."; // JCC

$langSearchCours	= "Revenir sur les informations du cours";
$langManage			= "Gestion du campus";

$langAreYouSureToDelete ="Êtes vous sûr de vouloir supprimer ";
$langBackToAdminPage = "Retour à la page d'administration";
$langToCourseSettings = "Retour aux propriétés du cours";
$langSeeCourseUsers = "Voir les utilisateurs du cours";
$langBackToCourseList = "Retour à la liste de cours";
$langBackToList = "Retour à la liste";
$langAllUsersOfThisCourse = "Utilisateurs de ce cours";
$langViewCourse = "Voir le cours";
$langIntroEditToolList="Sélectionner les outils que vous voulez activer.
Les outils invisibles seront grisés dans votre page d'accueil du cours."; // JCC 
$langTools="Outils";
$langActivate="Activer";
$langAddExternalTool="Ajouter un lien externe.";
$langAddedExternalTool="Lien externe ajouté.";  
$langUnableAddExternalTool="Impossible d'ajouter cet outil";
$langMissingValue="Valeur manquante";
$langToolName="Nom du  lien";
$langToolUrl="URL du lien"; // JCC
$langChangedTool="L'accès au lien a été changé";
$langUnableChangedTool="Impossible de changer l'accès au lien";
$langUpdatedExternalTool="Lien externe modifié";
$langUnableUpdateExternalTool="Impossible de changer le lien externe";
$langDeletedExternalTool='Lien externe effacé';
$langUnableDeleteExternalTool='Impossible d\'effacer le lien externe';
$langAdministrationTools="Outils d'administration";

?>