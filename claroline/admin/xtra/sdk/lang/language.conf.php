<?php

include ('../../../../inc/claro_init_global.inc.php');

// Files and path

define ('LANG_COMPLETE_FILENAME','complete.lang.php'); 
define ('LANG_MISSING_FILENAME','missing.lang.php'); 

// Default values 

define ('DEFAULT_LANGUAGE','english'); 
 
// database authentification data

define('DB_TRANSLATION','`claro_translation`');
define('TABLE_TRANSLATION','`claro_translation`.`translation`');
define('TABLE_USED_LANG_VAR','`claro_translation`.`used_language`');

// message

$problemMessage = "Problem with the Data Base.";

?>
