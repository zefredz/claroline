<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );
// Prevent direct call of this file from the web
// NOTE. The use of PHP_SELF is not appropriate in this case
// as PHP_SELF can also contain the path info ...


// The CLARO_INCLUDE_ALLOWED constant allows to include PHP file further in the
// code. Files which are meant to be included check if this constant is defined.
// If it isn't the case, these files immediately die.
// This process prevents hacking by direct calls of included file and setting
// of global variable (when PHP register_globals is set to 'ON')

define('CLARO_INCLUDE_ALLOWED', true);

// include the main Claroline platform configuration file

// Determine the directory path where this current file lies
// This path will be useful to include the other intialisation files

$includePath = dirname(__FILE__);
require_once $includePath . '/lib/claro_main.lib.php';
$mainConfPath =  $includePath . '/../../platform/conf/' . 'claro_main.conf.php';

if ( file_exists($mainConfPath) )
{
    include $mainConfPath;
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

define('PEAR_LIB_PATH', $includePath . '/lib/pear');

// Add the Claroline PEAR path to the php.ini include path
// This action is mandatory because PEAR inner include() statements
// rely on the php.ini include_path settings

set_include_path( '.' . PATH_SEPARATOR . PEAR_LIB_PATH . PATH_SEPARATOR . get_include_path() );

$clarolineRepositorySys = get_conf('rootSys') . $clarolineRepositoryAppend;
$clarolineRepositoryWeb = $urlAppend . '/' . $clarolineRepositoryAppend;
$userImageRepositorySys = get_conf('rootSys') . $userImageRepositoryAppend;
$userImageRepositoryWeb = $urlAppend . '/' . $userImageRepositoryAppend;
$coursesRepositorySys   = get_conf('rootSys') . $coursesRepositoryAppend;
$coursesRepositoryWeb   = $urlAppend . '/' . $coursesRepositoryAppend;
$rootAdminSys           = $clarolineRepositorySys . $rootAdminAppend;
$rootAdminWeb           = $clarolineRepositoryWeb . $rootAdminAppend;
$imgRepositoryAppend    = 'img/'; // <-this line would be editable in claroline 1.7
$imgRepositorySys       = $clarolineRepositorySys . $imgRepositoryAppend;
$imgRepositoryWeb       = $clarolineRepositoryWeb . $imgRepositoryAppend;

// Unix file permission access ...

define('CLARO_FILE_PERMISSIONS', 0777);

// Web server

$is_IIS = strstr($_SERVER['SERVER_SOFTWARE'], 'Microsoft-IIS') ? 1 : 0;
$is_Apache = strstr($_SERVER['SERVER_SOFTWARE'], 'Apache') ? 1 : 0;

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

require_once $includePath . '/lib/language.lib.php';
require_once $includePath . '/lib/right/right_profile.lib.php';

/*----------------------------------------------------------------------
  Include Plugin libraries and create needed buffer
  ----------------------------------------------------------------------*/
require_once $includePath . '/lib/buffer.lib.php';

/*----------------------------------------------------------------------
  Unquote GET, POST AND COOKIES if magic quote gpc is enabled in php.ini
  ----------------------------------------------------------------------*/

claro_unquote_gpc();

/*----------------------------------------------------------------------
  Connect to the server database and select the main claroline DB
  ----------------------------------------------------------------------*/


if ( ! defined('CLIENT_FOUND_ROWS') ) define('CLIENT_FOUND_ROWS', 2);
// NOTE. For some reasons, this flag is not always defined in PHP.

$db = @mysql_connect($dbHost, $dbLogin, $dbPass, false, CLIENT_FOUND_ROWS)
or die ('<center>'
       .'WARNING ! SYSTEM UNABLE TO CONNECT TO THE DATABASE SERVER.'
       .'</center>');

// NOTE. CLIENT_FOUND_ROWS is required to make claro_sql_query_affected_rows()
// work properly. When using UPDATE, MySQL will not update columns where the new
// value is the same as the old value. This creates the possiblity that
// mysql_affected_rows() may not actually equal the number of rows matched,
// only the number of rows that were literally affected by the query.
// But this behavior can be changed by setting the CLIENT_FOUND_ROWS flag in
// mysql_connect(). mysql_affected_rows() will return then the number of rows
// matched, even if none are updated.



$selectResult = mysql_select_db($mainDbName,$db)
or die ( '<center>'
        .'WARNING ! SYSTEM UNABLE TO SELECT THE MAIN CLAROLINE DATABASE.'
        .'</center>');

if ($statsDbName == '')
{
    $statsDbName = $mainDbName;
}

/*----------------------------------------------------------------------
  Include the events library for tracking
  ----------------------------------------------------------------------*/

require $includePath . '/lib/events.lib.inc.php';

/*----------------------------------------------------------------------
  Include the local (contextual) parameters of this course or section
  ----------------------------------------------------------------------*/

require $includePath . '/claro_init_local.inc.php';

/*----------------------------------------------------------------------
  Include the event manager declarations for the notification system
  ----------------------------------------------------------------------*/

require $includePath . '/lib/event/init_event_manager.inc.php';

/*----------------------------------------------------------------------
  Load language translation and locale settings
  ----------------------------------------------------------------------*/

language::load_translation();
language::load_locale_settings();

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
//    <input type="hidden" name="claroFormId" value="< ?php echo uniqid(''); ? >">
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

/*----------------------------------------------------------------------
  Find MODULES's includes to add and include them using a cache system
 ----------------------------------------------------------------------*/

// TODO : move module_cache to cache directory
// TODO : includePath is probably not needed

$module_cache_filename = get_conf('module_cache_filename','moduleCache.inc.php');
$cacheRepositorySys = get_conf('rootSys') . get_conf('cacheRepository', 'tmp/cache/');
if (!file_exists($cacheRepositorySys . $module_cache_filename))
{
    require_once $includePath . '/lib/module.manage.lib.php';
    generate_module_cache();
}

if (file_exists($cacheRepositorySys . $module_cache_filename))
{
    include $cacheRepositorySys . $module_cache_filename;
}
else trigger_error('module_cache not found',E_USER_WARNING);

?>