<?php // $Id$

   
   $langToolName="Leerpad";
   $langCreateNewLearningPath="Maak een nieuw leerpad";
   $langNoLearningPath = "Geen leerpad";
   $langimportLearningPath="Importeer een leerpad";
   $langLearningPath="Leerpad";
   $langLearningPathList="Leerpad Lijst";
   $langLearningPathAdmin="Leerpad Admin";
   $langModule = "Module";
   $langStatistics="Statistieken";
   $langTracking = "Volgen";
   $langOrder="Volgorde";
   $langVisible="Zichtbaarheid";
   $langBlock = "Blokkeren";
   $langProgress = "Voortgang";
   $langIntroLearningPath="Gebruik dit gereedschap om uw studenten documenten, oefeningen, HTML pagina's en links in een bepaalde volgorde aan te bieden<br><br>If you want to present your learning path at students, klik op de onderstaande knop.<br>";

   $langStartModule = "Start Module";
   $langModuleAdmin = "Module Admin";
   $langModuleHelpHandmade = "List of assets (ressources) used in this module.<br />The start asset is the entry point of student in the module, the first page that will be displayed to them.<br />A module without a defined start asset will not be able to launch!<br />Keep in mind that students will navigate in the module using the links you'll provide in your pages.";
   $langModuleHelpDocument = "You can chose a document that will replace the current one.";
   $langAsset = "Asset";
   $langStartAsset = "Start asset";

   $langRename = "Rename";
   $langRemoveFromLPShort = "Remove";
   $langRemoveFromLPLong = "Remove from this learning path";
   $langComment = "Comment";
   $langModuleType = "Type";
   $langAccess = "Accessibility";
   $langAddModulesButton = "Add module(s)";
   $langAddOneModuleButton = "Add module";
   $langInsertNewModuleName="Insert new name";
   $langModifyCommentModuleName="Insert new comment for";
   $langShareWithOtherCourse="Click to share this module with other courses";
   $langStopShare="Click to hide this module to other courses managers";

   $langGlobalProgress = "Learning path progression : ";

   //tools titles
   $langInsertMyModulesTitle = "Insert a module of the course";
   $langAddModule = "Add";
   $langPathContentTitle = "Learning path content";

   // alt comments for images
   $langAltMove = "Move";
   $langAltMoveUp = "Order up";
   $langAltMoveDown = "Order down";   
   $langAltMakeVisible = "Make visible";
   $langAltMakeInvisible = "Make invisible";
   $langAltMakeBlocking = "Make blocking";
   $langAltMakeNotBlocking = "Make not blocking";
   $langAltPathBlocked = "Students have to complete the last module of this path to access to the next paths";
   $langAltPathNotBlocked = "Students can access to the next paths without completing this path";
   // forms
   $langLearningPathName= "New learning path name : ";
   $langNewModuleName = "New module name and content type : ";
   $langButtonImport= "Import";
   $langAddComment = "Add a comment";
   $langAddAddedComment = "Add an added comment";
   $langChangeOrder = "Change order";

   // lang for learningPathAdmin
   $langNewModule = "Create an empty module";
   $langExerciseAsModule    = "Use an exercise";
   $langDocumentAsModule     =  "Use a document";
   $langModuleOfMyCourse  = "Use a module of this course";
   $langGetModuleFromOtherCourse   = "Get a module from another course";
   $langAlertBlockingMakedInvisible = "This module is blocking. \\nMaking it invisible will allow students to access \\n next modules without having to complete this one. \\n\\nConfirm ?";
   $langAlertBlockingPathMadeInvisible = "This path is blocking. \\nMaking it invisible will allow students to access \\n next paths without having to complete this one. \\n\\nConfirm ?";
   $langCreateLabel = "Create label";
   $langNewLabel = "Create a new label / title in this learning path";
   $langRoot = "root";
   $langWrongOperation = "Wrong operation";
   $langMove = "Move";
   $langTo = "to";
   $langModuleMoved = "Module moved";

   //lang for learningpathList

   $langPathsInCourseProg = "Course progression ";

   // $interbredcrump
   $langAdmin = "admin";

   // confirm
   $langAreYouSureToDelete = "Are you sure you want to delete ";
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
   $langOKNewPath  = "Creation successfull";

   // errors messages
   $langErrorNameAlreadyExists = "Error : Name already exists";
   $langErrorInvalidParms = "Error : Invalid parameter (use numbers only)";
   $langErrorValuesInDouble = "Error : One or more values are doubled";

   // insertMyModule
   $langNoMoreModuleToAdd="All modules of this course are already used in this learning path.";
   $langInsertMyModuleToolName="Insert my module";
   $langErrorEmptyName="Name must be completed";
   $langModuleType = "Type";
   $langAddedComment = "Added comment";

   // insertMyDoc
   $langInsertMyDocToolName = "Insert a document as module";
   $langDocInsertedAsModule = "has been added as module";
   $langFileAlreadyExistsInDestinationDir = "A file with the same name is already present in your module directory";
   $langDocumentAlreadyUsed = "This document is already used as a module in this learning path";

   $langDocModuleFileModified = "File has been modified";
   $langDocumentInModule = "Document in module";
   $langFileName = "Filename";

   // insertPublicModule
   $langCategories = "Course categories";
   $langPublicModule = "Public module(s)";
   $langClose ="Close";
   $langInsertPublicModuleToolName="Insert public module";
   $langNoPublicModule = "No public module available";
   $langAvailable = "module(s) available(s)";
   $langImportedCourse = "Imported from course";

   // insertMyExercise
   $langInsertMyExerciseToolName = "Insert my exercise";
   $langExercise = "Exercise";
   $langExInsertedAsModule = "has been added as a module of the course and of this learning path";
   $langExAlreadyUsed = "This exercise is already used as a module in this learning path";
   $langExAlreadyUsedInModule = "This exercise is already used in this module";

   // modules pool
   $langModulesPoolToolName = "Pool of modules";
   $langNoModule = "No module";
   $langUseOfPool = "This page allow you to view all the modules available in this course. <br>
                     Any exercise or document that has been added in a learning path will also appear in this list.";

   //assets
   $langStartAssetSet = "Start asset has been set";
   $langNoMoreStartAsset = "You have delete the start asset file.<br>There is no more start asset for this module.";
   $langNoStartAsset = "There is no start asset defined for this module.";

   // module admin / exercise
   $langChangeRaw = "Change minimum raw to pass this module (in pourcents) : ";
   $langModuleHelpExercise = "You can change the minimum raw for a student to pass this module.";
   $langRawHasBeenChanged = "Minimum raw to pass has been changed";
   $langExerciseInModule = "Exercise in module";
   $langModifyAll = "in all learning paths";
   $langModifyThis = "only in this learning path";
   $langUsedInSeveralModules = "Warning ! This module is used in several exercises. Would you like to modify it";


   $langModuleModified = "Module has been modified";
   $langQuitViewer = "Back to list";
   $langNext = "Next";
   $langPrevious = "Previous";
   $langBrowserCannotSeeFrames = "Your browser cannot see frames.";

   // default comment
   $langDefaultLearningPathComment = "This is the introduction text of this learning path. To replace it by your own text, click below on <b>modify</b>.";
   $langDefaultModuleComment = "This is the introduction text of this module, it will appears in each learning path that contains this module. To replace it by your own text, click below on <b>modify</b>.";
   $langDefaultModuleAddedComment = "This an additional introduction text about the presence of this module specially into this learning path. To replace it by your own text, click below on <b>modify</b>.";

   $langAlt['document'] = "Document";
   $langAlt['handmade'] = "Handmade";
   $langAlt['exercise'] = "Exercise";
   $langAlt['clarodoc'] = "Clarodoc";
   $langAlt['scorm'] = "Scorm";

   // import learning path
   $langImport = "Import";

   // import learning path / error messages
   $langErrorReadingManifest = "Error reading <i>manifest</i> file";
   $langErrortExtractingManifest = "Cannot extract manifest from zip file (corrupted file ? ).";
   $langErrorOpeningManifest = "Cannot find <i>manifest</i> file in the package.<br /> File not found : imsmanifest.xml";
   $langErrorReadingXMLFile = "Error reading a secondary initialisation file : ";
   $langErrorOpeningXMLFile = "Cannot find secondary initilisation file in the package.<br /> File not found : ";
   $langErrorFileMustBeZip = "File must be a zip file (.zip)";
   $langErrorNoZlibExtension = "Zlib php extension is required to use this tool.  Please contact your platform administrator.";
   $langErrorReadingZipFile = "Error reading zip file.";
   $langErrorNoModuleInPackage = "No module in package";
   $langErrorAssetNotFound = "Asset not found : ";
   $langErrorSql = "Error in SQL statement";

   // import learning path / ok messages
   $langScormIntroTextForDummies = "Imported packages must consist of a  zip file and SCORM 1.2 conformant";
   $langOkFileReceived = "File received : ";
   $langOkManifestRead = "Manifest read.";
   $langOkManifestFound = "Manifest found in zip file : ";
   $langOkModuleAdded = "Module added : ";
   $langOkChapterHeadAdded = "Title added : ";
   $langOkDefaultTitleUsed ="warning : Installation cannot find the name of the learning path and has set a default name.  You should change it.";
   $langOkDefaultCommentUsed = "warning : Installation cannot find the description of the learning path and has set a default comment.  You should change it";
   
   $langUnamedPath = "Unamed path" ;
   $langUnamedModule = "Unamed module";

   $langNotInstalled = "An error occured.  Learning Path import failed.";
   $langInstalled = "Learning path has been successfully imported.";


   //just before module start

   $langProgInModuleTitle = "Your progression in this module";
   $langInfoProgNameTitle = "Information";
   $langPersoValue = "Values";
   $langTotalTimeSpent = "Total time";
   $langLastSessionTimeSpent = "Last session time";
   $langLessonStatus = "Module status";
   $langYourBestScore = "Your best performance";
   $langNumbAttempt = "Attempt(s)";
   $langBrowsed = "Browsed";
   $langTimes = "time(s)";
   $langTypeOfModule = "Module type";
   $langSCORMTypeDesc = "SCORM 1.2 conformant content";
   $langEXERCISETypeDesc = "Claroline exercise";
   $langDOCUMENTTypeDesc = "Document";
   $langHANDMADETypeDesc = "HTML pages";
   $langAlreadyBrowsed = "Already browsed";
   $langNeverBrowsed = "Never browsed";
   $langBackModule = "Back to list";
   
   // in viewer
   $langExerciseCancelled = "Exercise cancelled, choose a module in the list to continue.";
   $langExerciseDone = "Your progression has been recorded, choose a module in the list to continue."; 
   $langView = "View";
   $langFullScreen = "Fullscreen";
   $langInFrames = "In frames";

?>
