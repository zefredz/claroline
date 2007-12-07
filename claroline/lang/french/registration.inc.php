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

// lang vars
$langAdminOfCourse		= "admin";  //
$langSimpleUserOfCourse = "normal"; // strings for synopsis
$langIsTutor  			= "tuteur"; //

$langCourseCode			= "Cours";	// strings for list Mode
$langParamInTheCourse 	= "Statut"; //

$langSummaryTable = "Cette table liste les utilisateurs du cours.";
$langSummaryNavBar = "Barre de navigation";
$langAddNewUser = "Ajouter un utilisateur au système";
$langMember ="inscrit";

$langDelete	="supprimer";
$langLock	= "bloquer";
$langUnlock	= "libérer"; // JCC 

$langHaveNoCourse = "Pas de cours"; // JCC 

$langFirstname = "Prénom"; // JCC 
$langLastname = "Nom";
$langEmail = "Adresse d'e-mail"; // JCC
$langAbbrEmail = "E-mail"; // JCC 
$langRetrieve ="Retrouver  mes paramètres d'identification";
$langMailSentToAdmin = "Un e-mail a été adressé à l'administrateur."; // JCC 
$langAccountNotExist = "Ce compte semble ne pas exister.<BR>".$langMailSentToAdmin." Il fera une recherche manuelle.<BR><BR>";
$langAccountExist = "Ce compte semble exister.<BR> Un e-mail a été adressé à l'administrateur. <BR><BR>"; // JCC 
$langWaitAMailOn = "Attendez vous à une réponse sur ";
$langCaseSensitiveCaution = "Le système fait la différence entre les minuscules et les majuscules.";
$langDataFromUser = "Données envoyées par l'utilisateur";
$langDataFromDb = "Données correspondantes dans la base de données"; // JCC 
$langLoginRequest = "Demande de login";
$langTotalEntryFound = " Nombre d'entrées trouvées"; // JCC 
$langEmailNotSent = "Quelque chose n'a pas fonctionné, veuillez envoyer ceci à"; // JCC 
$langTryWith ="essayez avec ";
$langInPlaceOf ="au lieu de";
$langParamSentTo = "Vos paramètres de connection sont envoyés sur l'adresse";

// lost password
$langLostPassword = "Mot de passe perdu";
$langExplainFormLostPass = "Tapez ce que vous pensez avoir introduit comme données lors de votre inscription.";
$langEmailNotSent = "Quelque chose n'a pas fonctionné, veuillez envoyer ceci à ";
$langYourAccountParam = "Voici vos paramètres de connexion";
$langPasswordHasBeenEmailed = "Votre mot depasse a été envoyé à l'adresse ";
$langEmailAddressNotFound = "Il n'existe aucun compte utilisateur lié à cette adresse.";
$langEnterMail = "Saisissez votre adresse email et confirmez.";
$langPlatformAdmin = "l'administrateur de la plateforme";

// REGISTRATION - AUTH - inscription.php
$langRegistration="Inscription";
$langName="Nom";
$langSurname="Prénom";
$langUsername="Nom d'utilisateur";
$langPass="Mot de passe";
$langConfirmation="Confirmation";
$langStatus="Action";
$langRegStudent="M'inscrire à des cours";
$langRegAdmin="Créer des sites de cours";
$langPhone = "Téléphone"; // JCC cette variable manquait
$langSaveChange ="Enregistrer les changements"; // JCC cette variable manquait
$langTitular = "Titulaire";
// inscription_second.php


$langRegistration = "Inscription";
$langPassTwice    = "Vous n'avez pas tapé deux fois le même mot de passe.
Utilisez le bouton de retour en arrière de votre navigateur
et recommencez.";

$langEmptyFields = "Vous n'avez pas rempli tous les champs.
Utilisez le bouton de retour en arrière de votre navigateur et recommencez.";

$langPassTooEasy ="Ce mot de passe est trop simple. Choisissez un autre mot de passe  comme par exemple : "; // JCC 

$langUserFree    = "Le nom d'utilisateur que vous avez choisi est déjà pris.
Utilisez le bouton de retour en arrière de votre navigateur
et choisissez-en un autre.";

$langYourReg                = "Votre inscription sur";
$langDear                   = "Cher(ère)";
$langYouAreReg              = "Vous êtes inscrit(e) sur";
$langSettings               = "avec les paramètre suivants:\nNom d'utilisateur:";
$langAddress                = "L'adresse de";
$langIs                     = "est";
$langProblem                = "En cas de problème, n'hésitez pas à prendre contact avec nous";
$langFormula                = "Cordialement";
$langManager                = "Responsable";
$langPersonalSettings       = "Vos coordonnées personnelles ont été enregistrées et un e-mail vous a été envoyé
pour vous rappeler votre nom d'utilisateur et votre mot de passe.</p>";
$langNowGoChooseYourCourses ="Vous  pouvez maintenant aller sélectionner les cours auxquels vous souhaitez avoir accès.";
$langNowGoCreateYourCourse  = "Vous  pouvez maintenant aller créer votre cours";
$langYourRegTo              = "Vos modifications";
$langIsReg                  = "Vos modifications ont été enregistrées";
$langCanEnter               = "Vous pouvez maintenant <a href=../../index.php>entrer dans le campus</a>";

// profile.php

$langModifProfile = "Modifier mon profil";
$langPassTwo      = "Vous n'avez pas tapé deux fois le même mot de passe";
$langAgain        = "Recommencez !"; // JCC 
$langFields       = "Vous n'avez pas rempli tous les champs";
$langUserTaken    = "Le nom d'utilisateur que vous avez choisi est déjà pris";
$langEmailWrong   = "L'adresse d'e-mail que vous avez introduite n'est pas complète
ou contient certains caractères non valides"; // JCC 
$langProfileReg   = "Votre nouveau profil a été enregistré";
$langHome         = "Retourner à l'accueil";
$langMyStats      = "Voir mes statistiques";
$langReturnSearchUser="Revenir à l'utilisateur"; // JCC 


// user.php

$langUsers    = "Utilisateurs";
$langModRight ="Modifier les droits de : ";
$langNone     ="non";
$langAll      ="oui";

$langNoAdmin            = "n'a désormais <b>aucun droit d'administration sur ce site</b>";
$langAllAdmin           = "a désormais <b>tous les droits d'administration sur ce site</b>";
$langModRole            = "Modifier le rôle de";
$langRole               = "Rôle (facultatif)";
$langIsNow              = "est désormais";
$langInC                = "dans ce cours";
$langFilled             = "Vous n'avez pas rempli tous les champs.";
$langUserNo             = "Le nom d'utilisateur que vous avez choisi";
$langTaken              = "est déjà pris. Choisissez-en un autre.";
$langOneResp            = "L'un des responsables du cours";
$langRegYou             = "vous a inscrit sur";
$langTheU               ="L'utilisateur";
$langAddedU             ="a été ajouté. Si vous avez introduit son adresse, un message lui a été envoyé pour lui communiquer son nom d'utilisateur";
$langAndP               = "et son mot de passe";
$langDereg              = "a été radié de ce cours"; // JCC 
$langAddAU              = "Ajouter des utilisateurs";
$langStudent            = "étudiant";
$langBegin              = "début";
$langPreced50           = "50 précédents";
$langFollow50           = "50 suivants";
$langEnd                = "fin";
$langAdmR               = "Admin";
$langUnreg              = "Radier"; // JCC 
$langAddHereSomeCourses = "<font size=2 face='arial, helvetica'><big>Mes cours</big><br><br>
			Cochez les cours que vous souhaitez suivre et décochez ceux que vous
			ne voulez plus suivre (les cours dont vous êtes responsable
			ne peuvent être décochés). Cliquez ensuite sur Ok en bas de la liste.";

$langCanNotUnsubscribeYourSelf = "Vous ne pouvez pas vous radier
				vous-même d'un cours dont vous êtes administrateur.
				Seul un autre administrateur du cours peut le faire."; // JCC 

$langGroup="Groupe";
$langUserNoneMasc="-";

$langTutor                = "Tuteur";
$langTutorDefinition      = "Tuteur (droit de superviser des groupes)";
$langAdminDefinition      = "Administrateur (droit de modifier le contenu du site)";
$langDeleteUserDefinition ="Radier (supprimer de la liste des utilisateurs de <b>ce</b> cours)"; // JCC 
$langNoTutor              = "n'est pas tuteur pour ce cours";
$langYesTutor             = "est tuteur pour ce cours";
$langUserRights           = "Droits des utilisateurs";
$langNow                  = "actuellement";
$langOneByOne             = "Ajouter manuellement un utilisateur";
$langUserMany             = "Importer une liste d'utilisateurs via un fichier texte";
$langNo                   = "non";
$langYes                  = "oui";

$langUserAddExplanation   = "Chaque ligne du fichier à envoyer
		contiendra nécessairement et uniquement les
		5 champs <b>Nom&nbsp;&nbsp;&nbsp;Prénom&nbsp;&nbsp;&nbsp;
		Nom d'utilisateur&nbsp;&nbsp;&nbsp;Mot de passe&nbsp;
		&nbsp;&nbsp;E-mail</b> séparés par des tabulations
		et présentés dans cet ordre. Les utilisateurs recevront
		par e-mail leur nom d'utilisateur et leur mot de passe."; // JCC 

$langSend             = "Envoyer";
$langDownloadUserList = "Envoyer la liste";
$langUserNumber       = "nombre";
$langGiveAdmin        = "Rendre admin";
$langRemoveRight      = "Retirer ce droit";
$langGiveTutor        = "Rendre tuteur";

$langUserOneByOneExplanation = "Il recevra par e-mail son nom d'utilisateur et son mot de passe"; // JCC 
$langBackUser                = "Retour à la liste des utilisateurs";
$langUserAlreadyRegistered   = "Un utilisateur ayant mêmes nom et prénom est déjà inscrit dans le cours.";

$langAddedToCourse           = "a été inscrit à votre cours";

$langGroupUserManagement     = "Gestion des groupes";

$langIfYouWantToAddManyUsers = "Si vous voulez ajouter une liste d'utilisateurs à votre cours, contactez votre web administrateur.";

$langCourses    = "cours.";
$langLastVisits = "Mes dernières visites";
$langSee        = "Voir";
$langSubscribe  = "M'inscrire<br>coché&nbsp;=&nbsp;oui";
$langCourseName = "Nom du cours";
$langLanguage   = "Langue";

$langConfirmUnsubscribe = "Confirmez la radiation de cet utilisateur"; // JCC 
$langAdded              = "Ajoutés";
$langDeleted            = "Supprimés";
$langPreserved          = "Conservés";
$langDate               = "Date";
$langAction             = "Action";
$langLogin              = "Entrer"; // JCC 
//$langLogout             = "Quitter";
$langModify             = "Modifier";
$langUserName           = "Nom d'utilisateur"; // JCC 
$langEdit               = "Editer";

$langCourseManager       = "Gestionnaire du cours";
$langManage              = "Gestion du campus";
$langAdministrationTools = "Outils d'administration";
$langModifProfile	     = "Modifier le profil";
$langUserProfileReg	     = "La modification a été effectuée";
$lang_lost_password      = "Mot de passe perdu";

$lang_enter_email_and_well_send_you_password  = "Tapez l'adresse d'e-mail que vous avez utilisée pour vous enregistrer et nous vous enverrons votre mot de passe."; // JCC
$lang_your_password_has_been_emailed_to_you   = "Votre mot de passe vous a été envoyé par e-mail."; // JCC
$lang_no_user_account_with_this_email_address = "Il n'y a pas de compte utilisateur avec cette adresse d'e-mail."; // JCC
$langCourses4User  = "Cours pour cet utilisateur";
$langCoursesByUser = "Vue d'ensemble des cours par utilisateur";

$langAddImage = "Ajoutez une photo";
$langUpdateImage = "Changez de photo";
$langDelImage = "Retirez la photo";
$langOfficialCode = "Matricule";

$langAuthInfo = "Paramètres de connection";
$langEnter2passToChange = "Tapez deux fois le nouveau mot de passe pour le changer, laissez vide pour garder l'ancien."; // JCC

$lang_SearchUser_ModifOk            = "Les modifications ont été effectuées correctement";

$langNoUserSelected = "Aucun utilisateur n'a été selectionné!";

// dialogbox messages

$langUserUnsubscribed = "L'utilisateur a bien été radié du cours"; // JCC 
$langUserNotUnsubscribed = "Erreur!! vous ne pouvez pas radier un professeur du cours";

?>