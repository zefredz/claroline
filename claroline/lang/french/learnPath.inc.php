<?php // $Id$

   
   $langToolName="Parcours p&eacute;dagogique";
   $langCreateNewLearningPath="Cr&eacute;er un nouveau parcours p&eacute;dagogique";
   $langNoLearningPath = "Aucun parcours";
   $langimportLearningPath="Importer un parcours";
   $langLearningPath="Parcours p&eacute;dagogique";
   $langLearningPathList="Liste des parcours p&eacute;dagogiques";
   $langLearningPathAdmin="Administration du parcours";
   $langModule = "Module";
   $langStatistics="Statistiques";
   $langTracking = "Suivi";
   $langOrder="Ordre";
   $langVisible="Visibilit&eacute;";
   $langBlock = "Bloquer";
   $langProgress = "Progression";
   $langIntroLearningPath="Utiliser cet outils pour fournir à vos apprenants un parcours s&eacute;quentiel d&eacute;fini par vos soins entre des documents, exercices, pages HTML, liens... ou importer des contenus SCORM existants<BR><BR>Si vous désirez ajouter un texte d'introduction, cliquer sur ce bouton.<br>";

   $langStartModule = "Commencer le module";
   $langModuleAdmin = "Administration du module";
   $langModuleHelpHandmade = "Liste des ressources utilis&eacute;es dans ce module.<br /> La ressource de d&eacute;marrage est le point d'entr&eacute;e des apprenants dans le module, la premi&egrave;re page qui leur sera montr&eacute;e.<br /> Un module sans aucune ressource de d&eacute;marrage d&eacute;finie ne sera pas rendu accessible aux apprenants!<br /> Rappelez-vous que les apprenants naviguerons dans le module en utilsant les liens que vous int&eacute;grerez dans vos pages.";
   $langModuleHelpDocument = "Vous pouvez choisir un document qui remplacera l'actuel docuement de ce module.";
   $langAsset = "Ressource";
   $langStartAsset = "Ressource de d&eacute;marrage";

   $langRename = "Renommer";
   $langRemoveFromLPShort = "Retirer";
   $langRemoveFromLPLong = "Retirer de ce parcours p&eacute;dagogique";
   $langComment = "Commentaire";
   $langModuleType = "Type";
   $langAccess = "Accessibilit&eacute;";
   $langAddModulesButton = "Ajouter le(s) module(s)";
   $langAddOneModuleButton = "Ajouter le module";
   $langInsertNewModuleName="Ins&eacute;rer le nouveau nom";
   $langModifyCommentModuleName="Ins&eacute;rer un nouveau commentaire pour";
   $langShareWithOtherCourse="Cliquez pour partager ce module avec les modules des autres cours";
   $langStopShare="Cliquez pour cacher ce module aux professeurs des autres cours";

   $langGlobalProgress = "Progression du parcours p&eacute;dagogique : ";

   //tools titles
   $langInsertMyModulesTitle = "Ins&eacute;rer un module du cours";
   $langAddModule = "Ajouter";
   $langPathContentTitle = "Contenu du parcours p&eacute;dagogique";

   // alt comments for images
   $langAltMove = "D&eacute;placer";
   $langAltMoveUp = "Monter";
   $langAltMoveDown = "Descendre";   
   $langAltMakeVisible = "Rendre visible";
   $langAltMakeInvisible = "Rendre invisible";
   $langAltMakeBlocking = "Rendre bloquant";
   $langAltMakeNotBlocking = "Rendre non bloquant";
   $langAltPathBlocked = "Les apprenants doivent finir le dernier module de ce parcours pour acc&eacute;der aux parcours suivants";
   $langAltPathNotBlocked = "Les apprenants peuvent acc&eacute;der aux parcours suivants sans finir ce parcours";
   // forms
   $langLearningPathName= "Nom du nouveau parcours : ";
   $langNewModuleName = "Nom du nouveau module name et type de contenu : ";
   $langButtonImport= "Importer";
   $langAddComment = "Ajouter un commentaire";
   $langAddAddedComment = "Ajouter un commentaire sp&eacute;cifique au parcours";
   $langChangeOrder = "Changer l'ordre";

   // lang for learningPathAdmin
   $langNewModule = "Cr&eacute;er un module vide";
   $langExerciseAsModule    = "Utiliser un exercice";
   $langDocumentAsModule     =  "Utiliser un document";
   $langModuleOfMyCourse  = "Utiliser un module de ce cours";
   $langGetModuleFromOtherCourse   = "Ajouter un module d'un autre cours";
   $langAlertBlockingMakedInvisible = "Ce module est bloquant, \\nle rendre invisible permettra aux apprenants d\'acc&eacute;der \\n aux modules suivants du parcours sans devoir r&eacute;ussir celui-ci. \\n\\nConfirmer ?";
   $langAlertBlockingPathMadeInvisible = "Ce parcours est bloquant. \\nle rendre invisible permettra aux apprenants d\'acc&eacute;der \\n aux parcours suivants sans devoir r&eacute;sussir celui-ci. \\n\\nConfirmer ?";
   $langCreateLabel = "Cr&eacute;er un titre";
   $langNewLabel = "Cr&eacute;er un titre dans ce parcours pédagogique";
   $langRoot = "Niveau sup&eacute;rieur";
   $langWrongOperation = "Op&eacute;ration impossible";
   $langMove = "D&eacute;placer";
   $langTo = "vers";
   $langModuleMoved = "Module d&eacute;plac&eacute;";
   
   //lang for learningpathList

   $langPathsInCourseProg = "Progression dans le cours";

   // $interbredcrump
   $langAdmin = "admin";

   // confirm
   $langAreYouSureToDelete = "Etes-vous sur de vouloir effacer ";
   $langModuleStillInPool = "Les modules de ce parcours seront toujours accessibles dans la banque de modules";
   $langAreYouSureToRemove = "Etes vous sur de vouloir retirer ce module du parcours p&eacute;dagogique : ";
   $langAreYouSureToRemoveSCORM = "Les modules conformes &agrave; SCORM sont d&eacute;finitivement effac&eacute;s du serveur lorsqu\'ils sont effac&eacute;s dans un parcours p&eacute;dagogique.";
   $langAreYouSureToRemoveStd = "Le module sera toujours accessible dans la banque de modules.";
   $langAreYouSureToRemoveLabel = "Effacer un titre efface &eacute;galement tous les titres et modules qu\'il contient.";   
   $langAreYouSureToDeleteScorm = "Ce parcours est issu de l\'importation d'un package SCORM. Si vous effacer ce parcours, tout les contenus SCORM de ses modules seront supprimer du serveur.  Etes-vous sur de vouloir effacer le parcours p&eacute;dagogique ";
   $langAreYouSureToDeleteScormModule = "Etes vous sur de vouloir effacer ce module SCORM? Le module ne sera plus accessible sur le serveur.";

   // this var is used in javascript popup so \n are escaped to be read by javascript only
   $langAreYouSureDeleteModule = "Etes-vous sur de vouloir totalement effacer ce module ?\\n\\nIL sera définitivement effacé du serveur et du parcours pédagogique.\\nVous ne pourrez plus l'utiliser dans aucun parcours pédagogique.\\n\\nConfirmer la suppression de : ";
   $langUsedInLearningPaths = "\\nNombre de parcours utilisant ce module : ";

   // success messages
   $langOKNewPath  = "Creation r&eacute;ussie";

   // errors messages
   $langErrorNameAlreadyExists = "Erreur : Le nom existe d&eacute;j&agrave;";
   $langErrorInvalidParms = "Erreur : param&egrave;tre invalide (utilisez seulement des nombres)";
   $langErrorValuesInDouble = "Erreur : Une ou plusieures valeurs sont doubl&eacute;es";

   // insertMyModule
   $langNoMoreModuleToAdd="Tous les modules de ce cours sont d&eacute;j&agrave; utilis&eacute;s dans ce parcours.";
   $langInsertMyModuleToolName="Ins&eacute;rer mon module";
   $langErrorEmptyName="Le nom doit &ecirc;tre compl&egrave;t&eacute;";
   $langModuleType = "Type";
   $langAddedComment = "Commentaire sp&eacute;cifique";

   // insertMyDoc
   $langInsertMyDocToolName = "Ins&eacute;rer un document comme module";
   $langDocInsertedAsModule = "a &eacute;t&eacute; ajout&eacute; comme module";
   $langFileAlreadyExistsInDestinationDir = "Un fichier portant le m&ecirc;me nom est d&eacute;j&agrave; pr&eacute;sent dans votre liste de module";
   $langDocumentAlreadyUsed = "Ce document est d&eacute;j&agrave; utilis&eacute; comme module dans ce parcours p&eacute;dagogique";

   $langDocModuleFileModified = "Le fichier a &eacute;t&eacute; modifi&eacute;";
   $langDocumentInModule = "Document dans le module";
   $langFileName = "Nom du fichier";

   // insertPublicModule
   $langCategories = "Cat&eacute;gories de cours";
   $langPublicModule = "Module(s) publique(s)";
   $langClose ="Fermer";
   $langInsertPublicModuleToolName="Ajouter un module publique";
   $langNoPublicModule = "Pas de module publique disponible";
   $langAvailable = "module(s) disponible(s)";
   $langImportedCourse = "Import&eacute; d'un cours";

   // insertMyExercise
   $langInsertMyExerciseToolName = "Ajouter mon exercice";
   $langExercise = "Exercice";
   $langExInsertedAsModule = "a &eacute;t&eacute; ajout&eacute; comme module de ce cours et comme module de ce parcours p&eacute;dagogique";
   $langExAlreadyUsed = "Cet exercice est d&eacute;j&agrave; utilis&eacute; comme module dans ce parcours p&eacute;dagogique";
   $langExAlreadyUsedInModule = "Cet exercice est d&eacute;j&agrave; utilis&eacute; dans ce module";

   // modules pool
   $langModulesPoolToolName = "Banque de modules";
   $langNoModule = "Pas de module";
   $langUseOfPool = "Cette page vous permet de voir tous les modules disponibles dans votre cours. <br>
                     Tous les exercices ou document qui ont &eacute;t&eacute; ajout&eacute; dans un parcours apparaîtront aussi dans cette liste.";

   //assets
   $langStartAssetSet = "La ressource de d&eacute;marrage a &eacute;t&eacute; modifi&eacute;e";
   $langNoMoreStartAsset = "Vous avez effac&eacute; la ressource de d&eacute;marrage.<br>Il n'y a plus de ressource de d&eacute;marrage pour ce module.";
   $langNoStartAsset = "Il n'y a pas de ressource de d&eacute;marrage d&eacute;finie pour ce module.";

   // module admin / exercise
   $langChangeRaw = "Changer le score minimum pour r&eacute;sussir ce module (en pourcents) : ";
   $langModuleHelpExercise = "Vous pouvez changer le score minimum n&eacute;cessaire que doit obtenir l'apprenant pour r&eacute;ussir ce module.";
   $langRawHasBeenChanged = "Le score minimum pour r&eacute;ussir le module a &eacute;t&eacute; chang&eacute;";
   $langExerciseInModule = "Exercice du module";
   $langModifyAll = "dans tous les parcours p&eacute;dagogiques ";
   $langModifyThis = "seulement dans ce parcours";
   $langUsedInSeveralModules = "Attention ! Cet exercice est utilis&eacute; dans un ou plusieurs modules de parcours p&eacute;dagogique. Voulez-vous le changer?";

   $langModuleModified = "Le module a &eacute;t&eacute; modifi&eacute;";
   $langQuitViewer = "Retour à la liste";
   $langNext = "Suivant";
   $langPrevious = "Pr&eacute;c&eacute;dent";
   $langBrowserCannotSeeFrames = "Votre navigateur ne supporte pas les frames";

   // default comment
   $langDefaultLearningPathComment = "Ceci est le texte d'introduction du parcours p&eacute;dagogique. Pour le remplacer par votre propre texte, cliquez en-dessous sur <b>modifier</b>.";
   $langDefaultModuleComment = "Ceci est un texte d'introduction du module, il apparaîtra dans chaque parcours contenant ce module. Pour le remplacer par votre propre texte, cliquez en dessous sur <b>modifier</b>.";
   $langDefaultModuleAddedComment = "Ceci est un texte additionel d'introduction du module. Il est sp&eacute;cifique &agrave; la pr&eacute;sence de ce module dans ce parcours p&eacute;dagogique. Pour le remplacer par votre propre texte, cliquez en dessous sur <b>modifier</b>.";

   $langAlt['document'] = "Document";
   $langAlt['handmade'] = "Handmade";
   $langAlt['exercise'] = "Exercise";
   $langAlt['clarodoc'] = "Clarodoc";
   $langAlt['scorm']    = "Scorm";

   // import learning path
   $langImport = "Importer";

   // import learning path / error messages
   $langErrorReadingManifest = "Erreur &agrave; la lecture du fichier <i>imsmanifest.xml</i>";
   $langErrortExtractingManifest = "Impossible d'extraire le manifeste du fichier zip (fichier corrompu ?)";
   $langErrorOpeningManifest = "Le manifeste n'a pas &eacute;t&eacute; trouv&eacute; dans le package.<br /> Fichier manquant : imsmanifest.xml";
   $langErrorReadingXMLFile = "Erreur &agrave; la lecture d'un fichier secondaire d'initialisation : ";
   $langErrorOpeningXMLFile = "Un fichier XML secondaire d'initialisation n'a pas pu &ecirc;tre trouv&eacute;.<br /> Fichier manquant : ";
   $langErrorFileMustBeZip = "Le fichier upload&eacute; doit &ecirc;tre au format zip (.zip)";
   $langErrorNoZlibExtension = "L'extension php 'zlib' est requise pour l'utilisation de cet outil. Contactez l'administrateur de la plateforme.";
   $langErrorReadingZipFile = "Erreur lors de la lecture du fichier zip.";
   $langErrorNoModuleInPackage = "Pas de module dans le package";
   $langErrorAssetNotFound = "Ressource non trouv&eacute;e : ";
   $langErrorSql = "Erreur dans les requ&ecirc;tes SQL";

   // import learning path / ok messages
   $langScormIntroTextForDummies = "Les packages import&eacute;s doivent &ecirc;tre des fichiers zip et r&eacute;pondre &agrave; la norme SCORM 1.2";
   $langOkFileReceived = "Fichier reçu : ";
   $langOkManifestRead = "Manifest lu.";
   $langOkManifestFound = "Manifest trouv&eacute;.";
   $langOkModuleAdded = "Module ajout&eacute; : ";
   $langOkChapterHeadAdded = "Titre ajout&eacute; : ";
   $langOkDefaultTitleUsed ="attention : l'installation n'a pas trouv&eacute; le nom du parcours p&eacute;dagogique et a attribu&eacute; un nom par d&eacute;faut. Vous pourrez le changer par la suite.";
   $langOkDefaultCommentUsed = "attention : l'installation n'a pas trouv&eacute; la description du parcours p&eacute;dagogique et a attribu&eacute; un commentaire par d&eacute;faut. Vous pourrez les changer par la suite";

   $langUnamedPath = "Parcours sans nom" ;
   $langUnamedModule = "Module sans nom";

   $langNotInstalled = "Une erreur est survenue.  L'importation du parcours p&eacute;dagogique a &eacute;chou&eacute;.";
   $langInstalled = "L'importation du parcours p&eacute;dagogique a r&eacute;ussi.";


   //just before module start

   $langProgInModuleTitle = "Votre progression dans ce module";
   $langInfoProgNameTitle = "Information";
   $langPersoValue = "Valeurs";
   $langTotalTimeSpent = "Temps total";
   $langLastSessionTimeSpent = "Temps de la derni&egrave;re session";
   $langLessonStatus = "Statut du module";
   $langYourBestScore = "Votre meilleur score";
   $langNumbAttempt = "Tentative(s)";
   $langBrowsed = "visit&eacute;";
   $langTimes = "fois";
   $langTypeOfModule = "Type de module";
   $langSCORMTypeDesc = "SCORM 1.2 conformant content";
   $langEXERCISETypeDesc = "Claroline exercise";
   $langDOCUMENTTypeDesc = "Document";
   $langHANDMADETypeDesc = "Pages HTML";
   $langAlreadyBrowsed = "D&eacute;j&agrave; visit&eacute;";
   $langNeverBrowsed = "Jamais visit&eacute;";
   $langBackModule = "Retour &agrave; la liste";

  // in viewer
  $langExerciseCancelled = "Exercice annul&eacute;, choisissez un module dans la liste pour continuer.";
  $langExerciseDone = "Votre progression a &eacute;t&eacute; enregistr&eacute;e.<br />Si ce module &eacute;tait bloquant, et que vous avez obtenu un score suffisant,  vous pouvez d&eacute;sormais passer au suivant en utilisant la liste."; 
  $langView = "Vue";
  $langFullScreen = "Plein &eacute;cran";
  $langInFrames = "En cadres";
?>
