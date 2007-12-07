<?php # $Id$

// Most PHP package has increase the error reporting.
// The line below set the error reporting to the most fitting one for Claroline
error_reporting(error_reporting() & ~ E_NOTICE);

// a shorter reference to $_SERVER['PHP_SELF']. Useful to lighten the HTML code.

$_SELF = basename($_SERVER['PHP_SELF']);

// include the main Claroline platform configuration file

// Determine the directory path where this current file lies
// This path will be useful to include the other intialisation files
if ((bool) stristr($_SERVER['PHP_SELF'], basename(__FILE__))) die('---');

$includePath = dirname(__FILE__);

// YOU CAN REMOVE THE @ of the following line after install.
@include($includePath.'/conf/claro_main.conf.php');

// Path to the PEAR library. PEAR stands for "PHP Extension and Application
// Repository". It is a framework and distribution system for reusable PHP
// components. More on http://pear.php.net.
// Claroline is provided with the basic PEAR components needed by the
// application in the "claroline/inc/lib/pear" directory. But, server
// administator can redirect to their own PEAR library directory by setting
// its path to the PEAR_LIB_PATH constant.

define('PEAR_LIB_PATH', $includePath.'/lib/pear');
$clarolineRepositorySys = $rootSys.$clarolineRepositoryAppend;
$clarolineRepositoryWeb = $rootWeb.$clarolineRepositoryAppend;
$userImageRepositorySys = $rootSys.$userImageRepositoryAppend;
$userImageRepositoryWeb = $rootWeb.$userImageRepositoryAppend;
$coursesRepositorySys   = $rootSys.$coursesRepositoryAppend;
$coursesRepositoryWeb   = $rootWeb.$coursesRepositoryAppend;
$rootAdminSys           = $clarolineRepositorySys.$rootAdminAppend;
$rootAdminWeb           = $clarolineRepositoryWeb.$rootAdminAppend;
$imgRepositoryAppend    = 'img/'; // <-this line would be editable in claroline 1.7
$imgRepositorySys       = $clarolineRepositorySys.$imgRepositoryAppend;
$imgRepositoryWeb       = $clarolineRepositoryWeb.$imgRepositoryAppend;

// Add the Claroline PEAR path to the php.ini include path
// This action is mandatory because PEAR inner include() statements
// rely on the php.ini include_path settings=

ini_set('include_path',
        ini_get('include_path') . ( strstr(PHP_OS, 'WIN') ?';':':') . PEAR_LIB_PATH );

// Start session
if (isset($platform_id))
{
    session_name($platform_id);
}
session_start();

if ($statsDbName == '')
{
	$statsDbName = $mainDbName;
}

include($includePath.'/lib/claro_main.lib.php');

// connect to the server database and select the main claroline DB

$db = @mysql_connect($dbHost, $dbLogin, $dbPass)
or die ( "<center>"
	   ."WARNING ! SYSTEM UNABLE TO CONNECT TO THE DATABASE SERVER"
	   ."<br>If it is your first connection to your Clarolineplatform, "
	   ."read <code>INSTALL.txt</code>."
	   ."</center>");

$selectResult = mysql_select_db($mainDbName,$db)
or die ( "<center>"
		."WARNING ! SYSTEM UNABLE TO SELECT THE MAIN CLAROLINE DATABASE"
		."</center>");

// include the local (contextual) parameters of this course or section

include($includePath."/claro_init_local.inc.php");


/*----------------------------------------
        LOAD LANGUAGE FILES SECTION
  --------------------------------------*/

if ($_course['language'])
{
	$languageInterface = $_course['language'];
}
else
{
	$languageInterface = $platformLanguage;
}

/*
 * common language properties and generic expressions
 */


if ( defined('CLAROLANG') && CLAROLANG == 'TRANSLATION')
{

    // include the language file with all language variables

    include ($includePath.'/../lang/english/complete.lang.php');

    if ($languageInterface  != 'english') // Avoid useless include as English lang is preloaded
    {
        include($includePath.'/../lang/'.$languageInterface.'/complete.lang.php');
    }


}
else
{

    if (isset($course_homepage) && $course_homepage == TRUE)
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

        $pos = strpos($languageFilename,'claroline/');

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
    $languageFile = $languageFilename.'.lang.php';

    if ( ! file_exists($includePath.'/../lang/english/'.$languageFile) )
    {
        include ($includePath.'/../lang/english/complete.lang.php');
    }
    else
    {
        include($includePath.'/../lang/english/'.$languageFile);
    }

    // load previously english file to be sure every $lang variable
    // have at least some content

    if ($languageInterface  != 'english')
    {
        @include($includePath.'/../lang/'.$languageInterface.'/'.$languageFile);
    }

}

// include the locale settings language

include($includePath.'/../lang/english/locale_settings.php');

if ($languageInterface  != 'english') // // Avoid useless include as English lang is preloaded
{
   include($includePath.'/../lang/'.$languageInterface.'/locale_settings.php');
}

?>
