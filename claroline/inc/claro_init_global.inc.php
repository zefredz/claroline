<?php # $Id$

// Most PHP package has increase the error reporting. 
// The line below set the error reporting to the most fitting one for Claroline
error_reporting(error_reporting() & ~ E_NOTICE);

// Start session

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





// a shorter reference to $PHP_SELF. Useful to lighten the HTML code.

$_SELF = basename($PHP_SELF);

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

// load previously english file to be sure every $lang variable 
// have at least some content

include($includePath."/../lang/english/trad4all.inc.php");
include($includePath."/../lang/".$languageInterface."/trad4all.inc.php");

/*
 * tool specific language translation
 */

// load previously english file to be sure every $lang variable
// have at least some content

include($includePath."/../lang/english/".$langFile.".inc.php");
@include($includePath."/../lang/".$languageInterface."/".$langFile.".inc.php");

?>