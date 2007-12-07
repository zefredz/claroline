<?php // $Id$
	/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.5.*
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2004 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
 */
// create_course.php
$langCreateSite="Créer un site de cours";
$langFieldsRequ="Tous les champs sont obligatoires";
$langTitle="Intitulé";
$langEx="p. ex. <i>Histoire de la littérature</i>";
$langFac="Catégorie";
$langCode="Code cours";
$langTargetFac="Il s'agit de la faculté, du département, de l'école... dans lesquels se donne le cours";
$langMaxSizeCourseCode = "max. 12 caractères, p. ex.<i>ROM2121</i>"; // to change the ma
$langDoubt="En cas de doute sur l'intitulé exact ou le code de votre cours, consultez le";
$langProgram="Programme des cours</a>. Si le site que vous voulez créer ne correspond pas à un code cours existant, vous pouvez en inventer un. Par exemple <i>INNOVATION</i> s'il s'agit d'un programme de formation en gestion de l'innovation";
$langProfessors="Titulaire(s)";
$langExplanation="Une fois que vous aurez cliqué sur OK, un site contenant Forum, Liste de liens, Exercices, Agenda, Liste de documents... sera créé. Grâce à votre identifiant, vous pourrez en modifier le contenu";
$langEmpty="Vous n'avez pas rempli tous les champs.\n<br>\nUtilisez le bouton de retour en arrière de votre navigateur et recommencez.<br>Si vous ne connaissez pas le code de votre cours, consultez le programme des cours";
$langCodeTaken="Ce code cours est déjà pris.<br>Utilisez le bouton de retour en arrière de votre navigateur et recommencez";
$langBackToAdmin = "Retour à l'administration";
$langAnotherCreateSite = "Créer un autre cours";
$langAdministrationTools = "Administration";

// tables MySQL
$langFormula="Cordialement, votre professeur";
$langForumLanguage="french";	// other possibilities are english, spanish (this uses phpbb language functions)
$langTestForum="Forum d\'essais";
$langDelAdmin="A supprimer via l\'administration des forums";
$langMessage="Lorsque vous supprimerez le forum &quot;Forum d&rsquo;essai&quot;, cela supprimera également le présent sujet qui ne contient que ce seul message";
$langExMessage="Message exemple";
$langAnonymous="Anonyme";
$langExerciceEx="Exemple d\'exercice";
$langAntique="Histoire de la philosophie antique";
$langSocraticIrony="L\'ironie socratique consiste à...";
$langManyAnswers="(plusieurs bonnes réponses possibles)";
$langRidiculise="Ridiculiser son interlocuteur pour lui faire admettre son erreur.";
$langNoPsychology="Non. L\'ironie socratique ne se joue pas sur le terrain de la psychologie, mais sur celui de l\'argumentation.";
$langAdmitError="Reconnaître ses erreurs pour inviter son interlocuteur à faire de même.";
$langNoSeduction="Non. Il ne s\'agit pas d\'une stratégie de séduction ou d\'une méthode par l\'exemple.";
$langForce="Contraindre son interlocuteur, par une série de questions et de sous-questions, à reconnaître qu\'il ne connaît pas ce qu\'il prétend connaître.";
$langIndeed="En effet. L\'ironie socratique est une méthode interrogative. Le grec &quot;eirotao&quot; signifie d\'ailleurs &quot;interroger&quot;.";
$langContradiction="Utiliser le principe de non-contradiction pour amener son interlocuteur dans l\'impasse.";
$langNotFalse="Cette réponse n\'est pas fausse. Il est exact que la mise en évidence de l\'ignorance de l\'interlocuteur se fait en mettant en évidence les contradictions auxquelles aboutissent ses thèses."; // JCC 

$langSampleLearnPath = "Exemple de parcours pédagogique";
$langSampleLearnPathDesc = "Ceci est un exemple de parcours pédagogique, il utilise l\'exemple d\'exercice et l\'exemple de document de l\'outil d\'exercices et l\'outil de documents. Cliquez sur <b>Modifier</b> pour changer ce texte."; // JCC 
$langSampleHandmade = "Exemple de module \'fait main\'";
$langSampleHandmadeDesc = "Vous pouvez faire un module \'fait main\' en utilisant des pages HTML, animations FLASH, vidéos...<br /><br /> Afin de permettre aux apprenants de voir le contenu de votre nouveau module, vous devrez définir une ressource de démarrage du module."; // JCC 
$langSampleDocument = "document_exemple";
$langSampleDocumentDesc = "Vous pouvez utiliser n\'importe quel document de l\'outil de documents de ce cours.";
$langSampleExerciseDesc = "Vous pouvez utiliser n\'importe quel exercice de l\'outil d\'exercices de ce cours.";

// Home Page MySQL Table "accueil"
$langAgenda="Agenda";
$langLinks="Liens";
$langDoc="Documents et liens";
$langVideo="Vidéo"; // JCC 
$langWorks="Travaux";
$langCourseProgram="Cahier des charges";
$langAnnouncements="Annonces";
$langUsers="Utilisateurs";
$langForums="Forums";
$langExercices="Exercices";
$langStatistics="Statistiques";
$langAddPageHome="Déposer une page et la lier à page d\'accueil"; // JCC 
$langLinkSite="Ajouter un lien sur la page d\'accueil";
$langModifyInfo="Propriétés du cours";
$langCourseDesc = "Description du cours";
$langLearningPath="Parcours pédagogique";
$langEmail="E-mail"; // JCC


// create_course.php // JCC cette variable manquait
$langLn="Langue";


// Other SQL tables
$langAgendaTitle="Mardi 11 décembre 14h00 : cours de philosophie (1) - Local : Sud 18";
$langAgendaText="Introduction générale à la philosophie et explication sur le fonctionnement du cours";
$langMicro="Micro-trottoir";
$langVideoText="Ceci est un exemple en RealVideo. Vous pouvez envoyer des vidéos de tous formats (.mov, .rm, .mpeg...), pourvu que vos étudiants soient en mesure de les lire";
$langGoogle="Moteur de recherche généraliste performant";
//$langIntroductionText="Ceci est le texte d\'introduction de votre cours. Modifier ce texte régulièrement est une bonne façon d\'indiquer clairement que ce site est un lieu d\'interaction vivant et non un simple répertoire de documents.";

//$langIntroductionTwo="Cette page est un espace de publication. Elle permet à chaque étudiant ou groupe d\'étudiants d\'envoyer un document (Word, Excel, HTML... ) vers le site du cours afin de le rendre accessible aux autres étudiants ainsi qu\'au professeur.
//Si vous passez par votre espace de groupe pour publier le document (option publier), l\'outil de travaux fera un simple lien vers le document là où il se trouve dans votre répertoire de groupe sans le déplacer.";
//$langIntroductionLearningPath="<p>Ceci est le texte d\'introduction des parcours pédagogiques de votre cours.  Utilisez cet outil pour fournir à vos apprenants un parcours séquentiel défini par vos soins entre des documents, exercices, pages HTML,... ou importer des contenus SCORM existants</p><p>Remplacez ce texte par votre propre introduction.<br></p>"; // JCC 
$langCourseDescription="Ecrivez ici la description qui apparaîtra dans la liste des cours (Le contenu de ce champ ne s\'affiche actuellement nulle part et ne se trouve ici qu\'en préparation à une version prochaine de Claroline).";
$langProfessor="Responsable de cours"; // JCC 
$langAnnouncementExTitle = "Exemple d\'annonce";
$langAnnouncementEx="Ceci est un exemple d\'annonce.";
$langJustCreated="Vous venez de créer le site du cours";
$langEnter="Retourner à votre liste de cours";
$langMillikan="Expérience de Millikan";



// Groups
$langGroups="Groupes";
$langCreateCourseGroups="Groupes";

$langCatagoryMain = "Général";
$langCatagoryGroup = "Forums des Groupes";

$langChat ="Discuter";

$langRestoreCourse = "Restauration d'un cours";
$langAddedToCreator = "en plus de celui choisi  à la création";


$langOnly = "Seulement";
$langRandomLanguage = "Sélection aléatoire parmi toutes les langues"; // JCC 


// Dev tools : create many test courses
$langTipLang="Cette langue vaudra pour tous les visiteurs de votre site de cours.";
$langCourseAccess="Accès au cours";
$langPublic="Accès public (depuis la page d'accueil de Claroline sans identifiant)";
$langPrivate="Accès privé (site réservé aux personnes figurant dans la liste <a href=../user/user.php>utilisateurs</a>)";
$langSubscription="Inscription";
$langConfTip="Par défaut, votre cours n'est accessible
qu'à vous qui en êtes le seul utilisateur. Si vous souhaitez un minimum de confidentialité, le plus simple est d'ouvrir
l'inscription pendant une semaine, de demander aux étudiants de s'inscrire eux-mêmes
puis de fermer l'inscription et de vérifier dans la liste des utilisateurs les intrus éventuels.";

//Display
$langCreateCourse="Cours à créer";
$langQantity="Quantité  : ";
$langPrefix="Préfixe  : "; // JCC 
$langStudent="étudiants";
$langMin="Minimum : ";
$langMax="Maximum : ";
$langNumGroup="Nombre de groupes par cours"; // JCC 
$langMaxStudentGroup="Nombre maximum d'étudiants par groupe"; // JCC 
$langAdmin ="administration";
$langNumGroupStudent="Nombre de groupes dont peut faire partie un étudiant dans un cours"; // JCC 

$langLabelCanBeEmpty ="L'intitulé est obligatoire";
$langTitularCanBeEmpty ="Le champs titulaire doit être rempli";
$langEmailCanBeEmpty ="Le champs e-mail doit être rempli"; // JCC
$langCodeCanBeEmpty ="Le code cours doit être rempli";
$langEmailWrong = "L'e-mail n'est pas correct (corrigez-le, ou effacez-le)"; // JCC
$langCreationMailNotificationSubject = 'Création de cours';
$langCreationMailNotificationBody = 'Cours ajouté sur'; 
$langByUser = 'par l\'utilisateur';

?>
