<?php # $Id$

// Most PHP package has increase the error reporting. 
// The line below set the error reporting to the most fitting one for Claroline
error_reporting(error_reporting() & ~ E_NOTICE);

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

// Start session
if (isset($platform_id))
{
    session_name($platform_id);
    session_start();
}
else
{
    if(file_exists($includePath.'/conf/claro_main.conf.php'))
    die ('<strong>$platform_id</strong> missing in config. <br>
    Reinstall claroline<br>
    or <br>
    add the following line in <tt>'.realpath($includePath.'/conf/claro_main.conf.php').'</tt><br><br>
    
    &nbsp;&nbsp;<em>$platform_id="'.md5(realpath($includePath.'/../install/do_install.inc.php')).'";</em>');
}

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
        $languageFilename = 'claroline_course_home';
    } 
    else
    {
	    /*
	     * tool specific language translation
	     */
	
	    // build lang file of the tool    
	    $languageFilename = str_replace ($urlAppend.'/','',$_SERVER['PHP_SELF']);

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
	    
    include($includePath.'/../lang/english/'.$languageFile);
	
    // load previously english file to be sure every $lang variable
    // have at least some content

    if ($languageInterface  != 'english')
    {
        @include($includePath.'/../lang/'.$languageInterface.'/'.$languageFile);
    }
    
}
    
// include the locale settings language

include($includePath.'/../lang/english/locale_settings.php');
    
if ($languageInterface  != 'english') // avoid inutile
{
   include($includePath.'/../lang/'.$languageInterface.'/locale_settings.php');
}

?>
