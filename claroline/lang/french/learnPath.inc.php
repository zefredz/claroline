<?php // $Id$

   
   $langToolName="Parcours pédagogique";
   $langCreateNewLearningPath="Créer un nouveau parcours pédagogique";
   $langNoLearningPath = "Aucun parcours";
   $langimportLearningPath="Importer un parcours";
   $langLearningPath="Parcours pédagogique";
   $langLearningPathList="Liste des parcours pédagogiques";
   $langLearningPathAdmin="Administration du parcours";
   $langModule = "Module";
   $langStatistics="Statistiques";
   $langTracking = "Suivi";
   $langOrder="Ordre";
   $langVisible="Visibilité";
   $langBlock = "Bloquer";
   $langProgress = "Progression";
   $langIntroLearningPath="Utilisez cet outil pour fournir à vos apprenants un parcours séquentiel défini par vos soins entre des documents, exercices, pages HTML, liens... ou importez des contenus SCORM existants<br /><br />Si vous désirez ajouter un texte d'introduction, cliquez sur ce bouton.<br />"; // JCC 

   $langStartModule = "Commencer le module";
   $langModuleAdmin = "Administration du module";
   $langModuleHelpDocument = "Vous pouvez choisir un document qui remplacera l'actuel document de ce module.";
   $langAsset = "Ressource";
   $langStartAsset = "Ressource de démarrage";

   $langRename = "Renommer";
   $langRemoveFromLPShort = "Retirer";
   $langRemoveFromLPLong = "Retirer de ce parcours pédagogique";
   $langComment = "Commentaire";
   $langModuleType = "Type";
   $langAddModulesButton = "Ajouter le(s) module(s)";
   $langAddOneModuleButton = "Ajouter le module";
   $langInsertNewModuleName="Insérer le nouveau nom";
   $langModifyCommentModuleName="Insérer un nouveau commentaire pour";

   $langGlobalProgress = "Progression du parcours pédagogique : ";

   //tools titles
   $langInsertMyModulesTitle = "Insérer un module du cours";
   $langAddModule = "Ajouter";
   $langPathContentTitle = "Contenu du parcours pédagogique";

   // alt comments for images
   $langAltMove = "Déplacer";
   $langAltMoveUp = "Monter";
   $langAltMoveDown = "Descendre";   
   $langAltMakeVisible = "Rendre visible";
   $langAltMakeInvisible = "Rendre invisible";
   $langAltMakeBlocking = "Rendre bloquant";
   $langAltMakeNotBlocking = "Rendre non bloquant";
   
   // forms
   $langLearningPathName= "Nom du nouveau parcours : ";
   $langNewModuleName = "Nom du nouveau module et type de contenu : "; // JCC 
   $langButtonImport= "Importer";
   $langAddComment = "Ajouter un commentaire";
   $langAddAddedComment = "Ajouter un commentaire spécifique au parcours";
   $langChangeOrder = "Changer l'ordre";

   // lang for learningPathAdmin
   $langNewModule = "Créer un module vide";
   $langExerciseAsModule    = "Utiliser un exercice";
   $langDocumentAsModule     =  "Utiliser un document";
   $langModuleOfMyCourse  = "Utiliser un module de ce cours";
   $langAlertBlockingMakedInvisible = "Ce module est bloquant, \\nle rendre invisible permettra aux apprenants d\'accéder \\n aux modules suivants du parcours sans devoir réussir celui-ci. \\n\\nConfirmer ?";
   $langAlertBlockingPathMadeInvisible = "Ce parcours est bloquant. \\nle rendre invisible permettra aux apprenants d\'accéder \\n aux parcours suivants sans devoir réussir celui-ci. \\n\\nConfirmer ?"; // JCC 
   $langCreateLabel = "Créer un titre";
   $langNewLabel = "Créer un titre dans ce parcours pédagogique";
   $langRoot = "Niveau supérieur";
   $langWrongOperation = "Opération impossible";
   $langMove = "Déplacer";
   $langTo = "vers";
   $langModuleMoved = "Module déplacé";
   $langBackToLPAdmin = "Retour au parcours pédagogique";
   
   //lang for learningpathList

   $langPathsInCourseProg = "Progression dans le cours";

   // $interbredcrump
   $langAdmin = "administrateur"; // JCC

   // confirm
   $langAreYouSureToDelete = "Etes-vous sûr de vouloir effacer "; // JCC 
   $langModuleStillInPool = "Les modules de ce parcours seront toujours accessibles dans la banque de modules";
   $langAreYouSureToRemove = "Etes-vous sûr de vouloir retirer ce module du parcours pédagogique : "; // JCC 
   $langAreYouSureToRemoveSCORM = "Les modules conformes à SCORM sont définitivement effacés du serveur lorsqu'ils sont effacés dans un parcours pédagogique."; // JCC
   $langAreYouSureToRemoveStd = "Le module sera toujours accessible dans la banque de modules.";
   $langAreYouSureToRemoveLabel = "Effacer un titre efface également tous les titres et modules qu\'il contient.";   
   $langAreYouSureToDeleteScorm = "Ce parcours est issu de l'importation d'un package SCORM. Si vous effacez ce parcours, tous les contenus SCORM de ses modules seront supprimés du serveur.  Etes-vous sûr de vouloir effacer le parcours pédagogique "; // JCC 
   $langAreYouSureToDeleteScormModule = "Etes-vous sûr de vouloir effacer ce module SCORM ? Le module ne sera plus accessible sur le serveur."; // JCC 

   // this var is used in javascript popup so \n are escaped to be read by javascript only
   $langAreYouSureDeleteModule = "Etes-vous sûr de vouloir totalement effacer ce module ?\\n\\nIl sera définitivement effacé du serveur et du parcours pédagogique.\\nVous ne pourrez plus l'utiliser dans aucun parcours pédagogique.\\n\\nConfirmer la suppression de : "; // JCC 
   $langUsedInLearningPaths = "\\nNombre de parcours utilisant ce module : ";

   // success messages
   $langOKNewPath  = "Creation réussie";

   // errors messages
   $langErrorNameAlreadyExists = "Erreur : Le nom existe déjà";

   // insertMyModule
   $langNoMoreModuleToAdd="Tous les modules de ce cours sont déjà utilisés dans ce parcours.";
   $langInsertMyModuleToolName="Insérer mon module";
   $langErrorEmptyName="Le nom doit être complèté";
   $langModuleType = "Type";
   $langAddedComment = "Commentaire spécifique";

   // insertMyDoc
   $langInsertMyDocToolName = "Insérer un document comme module";
   $langDocInsertedAsModule = "a été ajouté comme module";
   $langFileAlreadyExistsInDestinationDir = "Un fichier portant le même nom est déjà présent dans votre liste de module";
   $langDocumentAlreadyUsed = "Ce document est déjà utilisé comme module dans ce parcours pédagogique";

   $langDocModuleFileModified = "Le fichier a été modifié";
   $langDocumentInModule = "Document dans le module";
   $langFileName = "Nom du fichier";

   // insertPublicModule
   $langCategories = "Catégories de cours";
   $langClose ="Fermer";
   $langAvailable = "module(s) disponible(s)";

   // insertMyExercise
   $langInsertMyExerciseToolName = "Ajouter mon exercice";
   $langExercise = "Exercice";
   $langExInsertedAsModule = "a été ajouté comme module de ce cours et comme module de ce parcours pédagogique";
   $langExAlreadyUsed = "Cet exercice est déjà utilisé comme module dans ce parcours pédagogique";
   $langExAlreadyUsedInModule = "Cet exercice est déjà utilisé dans ce module";

   // modules pool
   $langModulesPoolToolName = "Banque de modules";
   $langNoModule = "Pas de module";
   $langUseOfPool = "Cette page vous permet de voir tous les modules disponibles dans votre cours. <br />
                     Tous les exercices ou document qui ont été ajoutés dans un parcours apparaîtront aussi dans cette liste.";

   //assets
   $langNoStartAsset = "Il n'y a pas de ressource de démarrage définie pour ce module.";

   // module admin / exercise
   $langChangeRaw = "Changer le score minimum pour réussir ce module (en pour cent) : "; // JCC 
   $langModuleHelpExercise = "Vous pouvez changer le score minimum nécessaire que doit obtenir l'apprenant pour réussir ce module.";
   $langRawHasBeenChanged = "Le score minimum pour réussir le module a été changé";
   $langExerciseInModule = "Exercice du module";
   $langModifyAll = "dans tous les parcours pédagogiques ";
   $langModifyThis = "seulement dans ce parcours";
   $langUsedInSeveralModules = "Attention ! Cet exercice est utilisé dans un ou plusieurs modules de parcours pédagogique. Voulez-vous le changer ?"; // JCC

   $langModuleModified = "Le module a été modifié";
   $langQuitViewer = "Retour à la liste";
   $langNext = "Suivant";
   $langPrevious = "Précédent";
   $langBrowserCannotSeeFrames = "Votre navigateur ne supporte pas les frames";

   // default comment
   $langDefaultLearningPathComment = "Ceci est le texte d'introduction du parcours pédagogique. Pour le remplacer par votre propre texte, cliquez en-dessous sur <b>modifier</b>.";
   $langDefaultModuleComment = "Ceci est un texte d'introduction du module, il apparaîtra dans chaque parcours contenant ce module. Pour le remplacer par votre propre texte, cliquez en dessous sur <b>modifier</b>.";
   $langDefaultModuleAddedComment = "Ceci est un texte additionnel d'introduction du module. Il est spécifique à la présence de ce module dans ce parcours pédagogique. Pour le remplacer par votre propre texte, cliquez en dessous sur <b>modifier</b>."; // JCC

   $langAlt['document'] = "Document";
   $langAlt['handmade'] = "Handmade";
   $langAlt['exercise'] = "Exercise";
   $langAlt['clarodoc'] = "Clarodoc";
   $langAlt['scorm']    = "Scorm";

   // import learning path
   $langImport = "Importer";

   // import learning path / error messages
   $langErrorReadingManifest = "Erreur à la lecture du fichier <i>imsmanifest.xml</i>";
   $langErrortExtractingManifest = "Impossible d'extraire le manifeste du fichier zip (fichier corrompu ?)";
   $langErrorOpeningManifest = "Le manifeste n'a pas été trouvé dans le package.<br /> Fichier manquant : imsmanifest.xml";
   $langErrorReadingXMLFile = "Erreur à la lecture d'un fichier secondaire d'initialisation : ";
   $langErrorOpeningXMLFile = "Un fichier XML secondaire d'initialisation n'a pas pu être trouvé.<br /> Fichier manquant : ";
   $langErrorFileMustBeZip = "Le fichier uploadé doit être au format zip (.zip)";
   $langErrorNoZlibExtension = "L'extension php 'zlib' est requise pour l'utilisation de cet outil. Contactez l'administrateur de la plate-forme."; // JCC
   $langErrorReadingZipFile = "Erreur lors de la lecture du fichier zip.";
   $langErrorNoModuleInPackage = "Pas de module dans le package";
   $langErrorAssetNotFound = "Ressource non trouvée : ";
   $langErrorSql = "Erreur dans les requêtes SQL";

   // import learning path / ok messages
   $langScormIntroTextForDummies = "Les packages importés doivent être des fichiers zip et répondre à la norme SCORM 1.2";
   $langOkFileReceived = "Fichier reçu : ";
   $langOkManifestRead = "Manifest lu.";
   $langOkManifestFound = "Manifest trouvé.";
   $langOkModuleAdded = "Module ajouté : ";
   $langOkChapterHeadAdded = "Titre ajouté : ";
   $langOkDefaultTitleUsed ="attention : l'installation n'a pas trouvé le nom du parcours pédagogique et a attribué un nom par défaut. Vous pourrez le changer par la suite.";
   $langOkDefaultCommentUsed = "attention : l'installation n'a pas trouvé la description du parcours pédagogique et a attribué un commentaire par défaut. Vous pourrez le changer par la suite"; // JCC 

   $langUnamedPath = "Parcours sans nom" ;
   $langUnamedModule = "Module sans nom";

   $langNotInstalled = "Une erreur est survenue.  L'importation du parcours pédagogique a échoué.";
   $langInstalled = "L'importation du parcours pédagogique a réussi.";


   //just before module start

   $langProgInModuleTitle = "Votre progression dans ce module";
   $langInfoProgNameTitle = "Information";
   $langPersoValue = "Valeurs";
   $langTotalTimeSpent = "Temps total";
   $langLastSessionTimeSpent = "Temps de la dernière session";
   $langLessonStatus = "Statut du module";
   $langYourBestScore = "Votre meilleur score";
   $langNumbAttempt = "Tentative(s)";
   $langBrowsed = "visité";
   $langTimes = "fois";
   $langTypeOfModule = "Type de module";
   $langSCORMTypeDesc = "Contenu conforme à SCORM 1.2"; // JCC 
   $langEXERCISETypeDesc = "Exercice Claroline"; // JCC 
   $langDOCUMENTTypeDesc = "Document";
   $langHANDMADETypeDesc = "Pages HTML";
   $langAlreadyBrowsed = "Déjà visité";
   $langNeverBrowsed = "Jamais visité";
   $langBackModule = "Retour à la liste";

  // in viewer
  $langExerciseCancelled = "Exercice annulé, choisissez un module dans la liste pour continuer.";
  $langExerciseDone = "Votre progression a été enregistrée.<br />Si ce module était bloquant, et que vous avez obtenu un score suffisant,  vous pouvez désormais passer au suivant en utilisant la liste."; 
  $langView = "Vue";
  $langFullScreen = "Plein écran";
  $langInFrames = "En cadres";
?>