<?php // $Id$
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.5.* (1)				                             |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2004 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      |   Italian translation  October 2004                                  |
      +----------------------------------------------------------------------+
      +----------------------------------------------------------------------+
      | Translator: GIOACCHINO POLETTO (info@polettogioacchino.com)          |
      +----------------------------------------------------------------------+
 */
   
   $langToolName="Learning Path";
   $langCreateNewLearningPath="Crea un nuovo learning path";
   $langNoLearningPath = "Nessun learning path";
   $langimportLearningPath="Importa un learning path";
   $langLearningPath="Learning Path";
   $langLearningPathList="Learning Path - Lista";
   $langLearningPathAdmin="Learning Path - Amministra";
   $langModule = "Moduli";
   $langStatistics="Statistiche";
   $langTracking = "Monitorizza";
   $langOrder="Ordina";
   $langVisible="Visibilita";
   $langBlock = "Blocco";
   $langProgress = "Progressione";
   $langIntroLearningPath="Usare questa utilità per fornire agli studenti un path per gestire documenti, esercizi, pagine HTML , links,...<br><br>Se volete rendere noto il vostro learning path agli studenti, premere il pulsante sottostante.<br>";

   $langStartModule = "Modulo - Start";
   $langModuleAdmin = "Modulo - Admin";
   $langModuleHelpDocument = "Potete scegliere un documento che rimpiazzi l'esistente.";
   $langAsset = "Proprietà";
   $langStartAsset = "Proprietà di Avvio";

   $langRename = "Rinomina";
   $langRemoveFromLPShort = "Elimina";
   $langRemoveFromLPLong = "Elimina da questo learning path";
   $langComment = "Commenta";
   $langModuleType = "Tipo";
   $langAddModulesButton = "Aggiungi Modulo/i";
   $langAddOneModuleButton = "Aggiungi Modulo";
   $langInsertNewModuleName="Insert new name";
   $langModifyCommentModuleName="Inserire un nuovo commento per";

   $langGlobalProgress = "Learning path - Progressione : ";

   //tools titles
   $langInsertMyModulesTitle = "Inserire un modulo del corso";
   $langAddModule = "Aggiungi";
   $langPathContentTitle = "Learning path - Contenuto";

   // alt comments for images
   $langAltMove = "Sposta";
   $langAltMoveUp = "Ordina Su";
   $langAltMoveDown = "Ordina Giù";   
   $langAltMakeVisible = "Rendi visibile";
   $langAltMakeInvisible = "Rendi invisibile";
   $langAltMakeBlocking = "Blocca";
   $langAltMakeNotBlocking = "Sblocca";
   
   // forms
   $langLearningPathName= "Nuovo nome learning path : ";
   $langNewModuleName = "Nuovo nome modulo e tipo contenuto : ";
   $langButtonImport= "Importa";
   $langAddComment = "Aggiungi un commento";
   $langAddAddedComment = "Aggiungi un commento aggiuntivo";
   $langChangeOrder = "Cambia ordine";

   // lang for learningPathAdmin
   $langNewModule = "Crea un nuovo modulo vuoto";
   $langExerciseAsModule    = "Usa un esercizio";
   $langDocumentAsModule     =  "Usa un documento";
   $langModuleOfMyCourse  = "Usa un modulo di questo corso";
   $langAlertBlockingMakedInvisible = "Questo modulo è bloccato. \\nMaking it invisible will allow students to access \\n next modules without having to complete this one. \\n\\nConfirm ?";
   $langAlertBlockingPathMadeInvisible = "Questo percorso è bloccato. \\nMaking it invisible will allow students to access \\n next paths without having to complete this one. \\n\\nConfirm ?";
   $langCreateLabel = "Crea etichetta";
   $langNewLabel = "Crea nuova etichetta / titolo in questo learning path";
   $langRoot = "root";
   $langWrongOperation = "Operazione errata";
   $langMove = "Sposta";
   $langTo = "a";
   $langModuleMoved = "Modulo spostato";
   $langBackToLPAdmin = "Torma all'Amministrazione del learning path";

   //lang for learningpathList

   $langPathsInCourseProg = "Corso - Progressione ";

   // $interbredcrump
   $langAdmin = "Admin";

   // confirm
   $langAreYouSureToDelete = "Sicuro di voler cancellare ";
   $langModuleStillInPool = "Modules of this path will still be available in the pool of modules";
   $langAreYouSureToRemove = "Are you sure you want to remove the following module from the learning path : ";
   $langAreYouSureToRemoveSCORM = "SCORM conformant modules are definitively removed from server when deleted in their learning path.";
   $langAreYouSureToRemoveStd = "The module will still be available in the pool of modules.";
   $langAreYouSureToRemoveLabel = "By deleting a label you will delete all modules or label it contains.";
   $langAreYouSureToDeleteScorm = "This learning path is issue of a SCORM importation package. If you delete this path, all its SCORM conformant modules and related files will be deleted from the platform.  Are you sure you want to delete the learning path named ";
   $langAreYouSureToDeleteScormModule = "Are you sure you want to delete this SCORM conformant modules? The module won't be available on the platform any longer.";

   // this var is used in javascript popup so \n are escaped to be read by javascript only
   $langAreYouSureDeleteModule = "Are you sure to totally delete this module ?\\n\\nIt will be definitively deleted from the server and from any learning path it is in.\\nYou won't be able to used it in any learning path.\\n\\nConfirm delete of : ";
   $langUsedInLearningPaths = "\\nNumber of learning paths using this module : ";

   // success messages
   $langOKNewPath  = "Creazione Eseguita";

   // errors messages
   $langErrorNameAlreadyExists = "Errore: nome già esistente";

   // insertMyModule
   $langNoMoreModuleToAdd="Tutti i moduli di questo corso sono già utilizzati in questo learning path.";
   $langInsertMyModuleToolName="Inserisci i miei moduli";
   $langErrorEmptyName="Il nome deve essere completato";
   $langModuleType = "Tipo";
   $langAddedComment = "Aggiungi commento";

   // insertMyDoc
   $langInsertMyDocToolName = "Inserisci un documento come modulo";
   $langDocInsertedAsModule = "è stato aggiunto come modulo";
   $langFileAlreadyExistsInDestinationDir = "Un file con lo stesso nome è già presente nella vostra directory";
   $langDocumentAlreadyUsed = "Questo documento è già utilizzato come modulo in questo learning path";

   $langDocModuleFileModified = "Il file è stato modificato";
   $langDocumentInModule = "Documenti nel modulo";
   $langFileName = "Nome File";

   // insertPublicModule
   $langCategories = "Corsi - Categorie";
   $langClose ="Chiudi";
   $langAvailable = "modulo/i disponibile/i";

   // insertMyExercise
   $langInsertMyExerciseToolName = "Inserisci i miei esercizi";
   $langExercise = "Esercizio";
   $langExInsertedAsModule = "è stato aggiunto come modulo del corso e di questo learning path";
   $langExAlreadyUsed = "Questo esercizio è già utilizzato come modulo in questo learning path";
   $langExAlreadyUsedInModule = "Questo esercizio è già utilizzato in questo modulo";

   // modules pool
   $langModulesPoolToolName = "Gruppo di Moduli";
   $langNoModule = "Nessun modulo";
   $langUseOfPool = "Questa pagina vi permette di visualizzare tutti i moduli presenti in questo corso. <br>
                     Ogni esercizio o documento che è stato aggiunto in un learning path apparirà in questa lista.";

   //assets
   $langNoStartAsset = "Non ci sono proprietà iniziali definite per questo modulo.";

   // module admin / exercise
   $langChangeRaw = "Cambia il livello minimo per superare il modulo (in percentuale) : ";
   $langModuleHelpExercise = "Potete modificare il livello minimo richiesto ad uno studente per superare questo modulo";
   $langRawHasBeenChanged = "Il livello minimo per il superamento del modulo è stato modificato";
   $langExerciseInModule = "Esercizio nel modulo";
   $langModifyAll = "in tutti i learning paths";
   $langModifyThis = "solo in questo learning path";
   $langUsedInSeveralModules = "Attenzione ! Questo modello è utilizzato in vari esercizi. Volete comunque modificarlo";

   $langModuleModified = "Il modulo è stato modificato";
   $langQuitViewer = "Torna alla lista";
   $langNext = "Successivo";
   $langPrevious = "Precedente";
   $langBrowserCannotSeeFrames = "Il vostro browser non può visualizzare i frames.";

   // default comment
   $langDefaultLearningPathComment = "Questo è il testo introduttivo di questo learning path. Per cambiare il testo, premere il pulsante <b>modify</b>.";
   $langDefaultModuleComment = "Questo è il testo introduttivo di questo modulo, apparirà in ogni learning path che contiene questo modulo. Per cambiare il testo, premere il pulsante <b>modify</b>.";
   $langDefaultModuleAddedComment = "Questo è un testo intoduttivo addizionale sulla presenza di questo modulo specially in questo learning path. Per cambiare il testo, premere il pulsante <b>modify</b>.";

   $langAlt['document'] = "Document";
   $langAlt['handmade'] = "Handmade";
   $langAlt['exercise'] = "Exercise";
   $langAlt['clarodoc'] = "Clarodoc";
   $langAlt['scorm'] = "Scorm";

   // import learning path
   $langImport = "Importazione";

   // import learning path / error messages
   $langErrorReadingManifest = "Error reading <i>manifest</i> file";
   $langErrortExtractingManifest = "Cannot extract manifest from zip file (corrupted file ? ).";
   $langErrorOpeningManifest = "Cannot find <i>manifest</i> file in the package.<br /> File not found : imsmanifest.xml";
   $langErrorReadingXMLFile = "Error reading a secondary initialisation file : ";
   $langErrorOpeningXMLFile = "Cannot find secondary initilisation file in the package.<br /> File not found : ";
   $langErrorFileMustBeZip = "Il file deve essere di tipo ZIP (.zip)";
   $langErrorNoZlibExtension = "Zlib php extension is required to use this tool.  Please contact your platform administrator.";
   $langErrorReadingZipFile = "Errore nella lettura del file ZIP.";
   $langErrorNoModuleInPackage = "No module in package";
   $langErrorAssetNotFound = "Asset not found : ";
   $langErrorSql = "Error in SQL statement";

   // import learning path / ok messages
   $langScormIntroTextForDummies = "Il file importato deve essere in formato ZIP e corrispondente alla sintassi SCORM 1.2";
   $langOkFileReceived = "File ricevuto : ";
   $langOkManifestRead = "Manifesto letto.";
   $langOkManifestFound = "Manifesto trovato nel zip file : ";
   $langOkModuleAdded = "Modulo aggiunto : ";
   $langOkChapterHeadAdded = "Titolo aggiunto : ";
   $langOkDefaultTitleUsed ="Attenzione : il processo d'installazione non trova la descrizione per il learning path. Viene settata una descrizione di defalut.  Dovrebbe essere variata.";
   $langOkDefaultCommentUsed = "Attenzione : il processo d'installazione non trova la descrizione per il learning path. Viene settata una descrizione di defalut.  Dovrebbe essere variata";
   
   $langUnamedPath = "Path senza nome" ;
   $langUnamedModule = "Modulo senza nome";

   $langNotInstalled = "Errore.  Importazione del Learning Path fallita.";
   $langInstalled = "Il Learning path è stato importato con successo";


   //just before module start

   $langProgInModuleTitle = "La tua progressione in questo modulo";
   $langInfoProgNameTitle = "Informazione";
   $langPersoValue = "Valori";
   $langTotalTimeSpent = "Tempo totale";
   $langLastSessionTimeSpent = "Durata dell'utima sessione";
   $langLessonStatus = "Stato del Modulo";
   $langYourBestScore = "La vostra migliore prestazione";
   $langNumbAttempt = "Tentativo/i";
   $langBrowsed = "Visualizzato";
   $langTimes = "tempo/i";
   $langTypeOfModule = "Tipo modulo";
   $langSCORMTypeDesc = "SCORM 1.2 conformant content";
   $langEXERCISETypeDesc = "Claroline exercise";
   $langDOCUMENTTypeDesc = "Documenti";
   $langHANDMADETypeDesc = "HTML pages";
   $langAlreadyBrowsed = "Già visualizzato";
   $langNeverBrowsed = "Non visualizzato";
   $langBackModule = "Torna alla lista";
   
   // in viewer
   $langExerciseCancelled = "Esercizio cancellato, scegli un modulo dalla lista per continuare.";
   $langExerciseDone = "La tua progressione è stata registrata, scegli un modulo dalla lista per continuare."; 
   $langView = "Vedi";
   $langFullScreen = "Tutto Schermo";
   $langInFrames = "A fotogramma";

?>
