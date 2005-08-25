<?php // $Id$

// Most PHP package has increase the error reporting. 
// The line below set the error reporting to the most fitting one for Claroline
error_reporting(error_reporting() & ~ E_NOTICE);

// Determine the directory path where this current file lies
// This path will be useful to include the other intialisation files

$includePath = realpath(dirname(__FILE__).'/../../inc');

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

// Unix file permission access ...

define('CLARO_FILE_PERMISSIONS', 0777);

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

// include the language file with all language variables

include($includePath . '/../lang/english/complete.lang.php');

if ($languageInterface  != 'english') // Avoid useless include as English lang is preloaded
{
    include($includePath.'/../lang/' . $languageInterface . '/complete.lang.php');
}

/*----------------------------------------------------------------------
  Authentification as platform administrator
  ----------------------------------------------------------------------*/

$tbl_mdb_names = claro_sql_get_main_tbl();
$tbl_user      = $tbl_mdb_names['user' ];
$tbl_admin     = $tbl_mdb_names['admin'];

if ( isset($_REQUEST['login']) ) $login = $_REQUEST['login'];
else                             $login = null;

if ( isset($_REQUEST['password']) ) $password = $_REQUEST['password'];
else                                $password = null;

if ( ! empty($_SESSION['_uid']) && ! ($login) )
{
    // uid is in session => login already done, continue with this value
    $_uid = $_SESSION['_uid'];

    if ( !empty($_SESSION['is_platformAdmin']) ) $is_platformAdmin = $_SESSION['is_platformAdmin'];
    else                                         $is_platformAdmin = false;
}
else
{
    $_uid = null; // uid not in session ? prevent any hacking

    if ( $login && $password ) // $login && $password are given to log in
    {
        // lookup the user in the Claroline database

        $sql = "SELECT user_id, username, password, authSource, creatorId
                 FROM `".$tbl_user."` `user`, `". $tbl_admin  ."` `admin`
                 WHERE BINARY username = '". addslashes($login) ."'
                   AND `user`.`user_id` = `admin`.`idUser` ";

        $result = claro_sql_query($sql) or die ('WARNING !! DB QUERY FAILED ! '.__LINE__);

        if ( mysql_num_rows($result) > 0)
        {
            $uData = mysql_fetch_array($result);
             
            // the authentification of this user is managed by claroline itself
            $password = stripslashes( $password );
            $login    = stripslashes( $login    );

            // determine if the password needs to be crypted before checkin
            // $userPasswordCrypted is set in main configuration file

            if ( $userPasswordCrypted ) $password = md5($password);

            // check the user's password
            if ( $password == $uData['password'] )
            {
                $_uid = $uData['user_id'];
                $is_platformAdmin = true;
            }
            else // abnormal login -> login failed
            {
                $_uid        = null;
                $loginFailed = true;
            }
        }
    }
}

?>
