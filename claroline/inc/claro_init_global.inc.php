<?php # $Id$

// include the main Claroline platform configuration file

// Determine the directory path where this current file lies
// This path will be useful to include the other intialisation files

$includePath = dirname(__FILE__);

if ( file_exists($includePath . '/conf/claro_main.conf.php') )
{
    require $includePath . '/conf/claro_main.conf.php';
} 
else
{
    die ('<center>'
       .'WARNING ! SYSTEM UNABLE TO FIND CONFIGURATION SETTINGS.'
       .'<p>'
       .'If it is your first connection to your Claroline platform, '
       .'read thoroughly INSTALL.txt file provided in the Claroline package.'
       .'</p>'
       .'</center>');
}

// Most PHP package has increase the error reporting.
// The line below set the error reporting to the most fitting one for Claroline
if( !CLARO_DEBUG_MODE ) error_reporting(error_reporting() & ~ E_NOTICE);

/*----------------------------------------------------------------------
  Various Path Init
  ----------------------------------------------------------------------*/

// Path to the PEAR library. PEAR stands for "PHP Extension and Application
// Repository". It is a framework and distribution system for reusable PHP
// components. More on http://pear.php.net.
// Claroline is provided with the basic PEAR components needed by the
// application in the "claroline/inc/lib/pear" directory. But, server
// administator can redirect to their own PEAR library directory by setting
// its path to the PEAR_LIB_PATH constant.

define('PEAR_LIB_PATH', $includePath.'/lib/pear');

// Add the Claroline PEAR path to the php.ini include path
// This action is mandatory because PEAR inner include() statements 
// rely on the php.ini include_path settings

ini_set('include_path', 
        ini_get('include_path') . ( strstr(PHP_OS, 'WIN') ?';':':') . PEAR_LIB_PATH );

$clarolineRepositorySys = $rootSys . $clarolineRepositoryAppend;
$clarolineRepositoryWeb = $rootWeb . $clarolineRepositoryAppend;
$userImageRepositorySys = $rootSys . $userImageRepositoryAppend;
$userImageRepositoryWeb = $rootWeb . $userImageRepositoryAppend;
$coursesRepositorySys   = $rootSys . $coursesRepositoryAppend;
$coursesRepositoryWeb   = $rootWeb . $coursesRepositoryAppend;
$rootAdminSys           = $clarolineRepositorySys . $rootAdminAppend;
$rootAdminWeb           = $clarolineRepositoryWeb . $rootAdminAppend;
$imgRepositoryAppend    = 'img/'; // <-this line would be editable in claroline 1.7
$imgRepositorySys       = $clarolineRepositorySys . $imgRepositoryAppend;
$imgRepositoryWeb       = $clarolineRepositoryWeb . $imgRepositoryAppend;

// Unix file permission access ...

define('CLARO_FILE_PERMISSIONS', 0777);

// Compatibility with IIS web server - REQUEST_URI

if ( !isset($_SERVER['REQUEST_URI']) )
{
    $_SERVER['REQUEST_URI'] = $_SERVER['PHP_SELF'];
    if ( !empty($_SERVER['QUERY_STRING']) ) 
    {
        $_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
    }
}

/*----------------------------------------------------------------------
  Start session
  ----------------------------------------------------------------------*/

if ( isset($platform_id) )
{
    session_name($platform_id);
}

session_start();

/*----------------------------------------------------------------------
  Include main library
  ----------------------------------------------------------------------*/

require $includePath . '/lib/claro_main.lib.php';

/*----------------------------------------------------------------------
  Unquote GET, POST AND COOKIES if magic quote gpc is enabled in php.ini
  ----------------------------------------------------------------------*/

claro_unquote_gpc();

/*----------------------------------------------------------------------
  Connect to the server database and select the main claroline DB
  ----------------------------------------------------------------------*/

$db = @mysql_connect($dbHost, $dbLogin, $dbPass)
or die ('<center>'
	   .'WARNING ! SYSTEM UNABLE TO CONNECT TO THE DATABASE SERVER.'
	   .'</center>');

$selectResult = mysql_select_db($mainDbName,$db)
or die ( '<center>'
		.'WARNING ! SYSTEM UNABLE TO SELECT THE MAIN CLAROLINE DATABASE.'
		.'</center>');

if ($statsDbName == '')
{
	$statsDbName = $mainDbName;
}

/*----------------------------------------------------------------------
  Include the local (contextual) parameters of this course or section
  ----------------------------------------------------------------------*/

require $includePath . '/claro_init_local.inc.php';

/*----------------------------------------------------------------------
  Include the event manager declarations for the notification system
  ----------------------------------------------------------------------*/
  
require $includePath . '/lib/event/init_event_manager.inc.php';

/*----------------------------------------------------------------------
  Load language files
  ----------------------------------------------------------------------*/

if ($_course['language'])
{
	$languageInterface = $_course['language'];
}
else
{
	$languageInterface = $platformLanguage;
}

/*----------------------------------------------------------------------
  Common language properties and generic expressions
  ----------------------------------------------------------------------*/

if ( defined('CLAROLANG') && CLAROLANG == 'TRANSLATION' )
{
    // include the language file with all language variables

    include($includePath . '/../lang/english/complete.lang.php');

    if ($languageInterface  != 'english') // Avoid useless include as English lang is preloaded
    {
        include($includePath.'/../lang/' . $languageInterface . '/complete.lang.php');
    }
    
}
else
{

    if ( isset($course_homepage) && $course_homepage == TRUE )
    {
        $languageFilename = 'claroline_course_home';
    } 
    else
    {
        /*
         * tool specific language translation
         */
    
        // build lang file of the tool    
        $languageFilename = preg_replace('|^'.preg_quote($urlAppend.'/').'|', '',  $_SERVER['PHP_SELF'] );
        $pos = strpos($languageFilename, 'claroline/');

        if ($pos === FALSE || $pos != 0)
        {
            // if the script isn't in the claroline folder the language file base name is index
            $languageFilename = 'index';
        }
        else
        {
            // else language file basename is like claroline_folder_subfolder_...
            $languageFilename = dirname($languageFilename);
            $languageFilename = str_replace('/','_',$languageFilename);
        }
    }
    
    // add extension to file
    $languageFile = $languageFilename . '.lang.php'; 

    if ( ! file_exists($includePath . '/../lang/english/' . $languageFile) )
    {
        include($includePath . '/../lang/english/complete.lang.php');
    }
    else
    {   
        include($includePath . '/../lang/english/' . $languageFile);
    }
	
    // load previously english file to be sure every $lang variable
    // have at least some content

    if ( $languageInterface != 'english' )
    {
        @include($includePath . '/../lang/' . $languageInterface . '/' . $languageFile);
    }
    
}

// include the locale settings language

include($includePath.'/../lang/english/locale_settings.php');
    
if ( $languageInterface  != 'english' ) // // Avoid useless include as English lang is preloaded
{
   include($includePath.'/../lang/'.$languageInterface.'/locale_settings.php');
}

/*----------------------------------------------------------------------
  Prevent duplicate form submission
  ----------------------------------------------------------------------*/

// The code below is a routine to prevent duplicate form submission, for 
// example if the user clicks on the 'Refresh' or 'Back' button of his 
// browser. It will nullify all the variables posted to the server by the 
// form, provided this form complies to 2 points :
// 
// 1. The form is submitted by POST method (<form method="POST">). GET 
// method is not taken into account.
// 
// 2. A unique ID value is provided at form submission that way 
// 
//    <input type="hidden" name="claroFormId" value="<?php echo uniqid(''); >">
// 
// The routine records in PHP session all the the ID of the submitted 
// forms. Once a form is submitted, its ID is compared to recorded ID, to 
// check if the form hasn't be posted before. 
// 
// One can set a limit to the stored ID in session by adapting the 
// CLARO_MAX_REGISTERED_FORM_ID constant.

define('CLARO_MAX_REGISTERED_FORM_ID', 50);

if ( isset($_POST['claroFormId']) )
{
    if ( ! isset($_SESSION['claroFormIdList']) )
    {
        $_SESSION['claroFormIdList'] = array( $_POST['claroFormId'] );
    }
    elseif ( in_array($_POST['claroFormId'], $_SESSION['claroFormIdList']) )
    {
        foreach($_POST as $thisPostKey => $thisPostValue)
        {
            $_REQUEST[$thisPostKey] = null;
        }

        $_POST = null;
    }
    else
    {
         $claroFormIdListCount = array_unshift($_SESSION['claroFormIdList'], 
                                               $_POST['claroFormId']         );

         if ( $claroFormIdListCount > CLARO_MAX_REGISTERED_FORM_ID )
         {
            array_pop( $_SESSION['claroFormIdList'] );
         }
    }
}

?>
