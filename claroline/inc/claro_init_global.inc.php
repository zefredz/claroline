<?php # $Id$

// Most PHP package has increase the error reporting. 
// The line below set the error reporting to the most fitting one for Claroline
error_reporting(error_reporting() & ~ E_NOTICE);

// Start session
// session_name(md5(realpath(__FILE__))); UNCOMMENT THIS LINE IF YOU HAVE MANY CAMPUS ON SAME SERVER
session_start();

///////////////////////////////////////////////////////////////////////
//// theses lines are added to accept the low security of php < 4.2.3
//// with a new php.
//// This is patchy
//// we must find how to default use the better security.
///////////////////////////////////////////////////////////////////////
//if (!empty($_GET))     {extract($_GET, EXTR_OVERWRITE);}           //
//if (!empty($_POST))    {extract($_POST, EXTR_OVERWRITE);}          //
//if (!empty($_SERVER))   {extract($_SERVER, EXTR_OVERWRITE);}       //
//if (!empty($_SESSION))  {extract($_SESSION, EXTR_OVERWRITE);}      //
//ini_set("session.bug_compat_warn","off");                          //
///////////////////////////////////////////////////////////////////////





// a shorter reference to $_SERVER['PHP_SELF']. Useful to lighten the HTML code.

$_SELF = basename($_SERVER['PHP_SELF']);

// include the main Claroline platform configuration file

// Determine the directory path where this current file lies
// This path will be useful to include the other intialisation files

$includePath = dirname(__FILE__);

// YOU CAN REMOVE THE @ of the following line after install.
@include($includePath."/conf/claro_main.conf.php");

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

define ('LANGMODE','DEVEL'); // must be move in claro_main_conf.php


if (LANGMODE == 'DEVEL')
{
 
    // include the language file with all language variables

    include ($includePath.'/../lang/english/complete.lang.php');

    if ($languageInterface  != 'english') // avoid inutile
    {
        include($includePath.'/../lang/'.$languageInterface.'/complete.lang.php');
    }
    
    
}
else
{

    if (isset($course_homepage) && $course_homepage == TRUE)
    {
        $langFile = 'claroline_course_home_course_home.lang.php';
    } 
    else
    {
	    /*
	     * tool specific language translation
	     */
	
	    // build lang file of the tool    
	    $langFile = str_replace ($urlAppend.'/','',$_SERVER['PHP_SELF']);
	    $langFile = str_replace("/","_", $langFile);
	    $langFile = str_replace('.php','.lang.php',$langFile);
    }
	    
    include($includePath.'/../lang/english/'.$langFile);
	
    // load previously english file to be sure every $lang variable
    // have at least some content

    if ($languageInterface  != 'english')
    {
        @include($includePath.'/../lang/'.$languageInterface.'/'.$langFile);
    }
    
}
    
// include the locale settings language

include($includePath.'/../lang/english/locale_settings.php');
    
if ($languageInterface  != 'english') // avoid inutile
{
   include($includePath.'/../lang/'.$languageInterface.'/locale_settings.php');
}

?>
