<?php
/*******************************************************
 * This File simply holds the file extention for your
 * phpBB files. If you use .php simply leave this file
 * alone
 * *****************************************************/

// Most PHP package has increase the error reporting. 
// The line below set the error reporting to the most fitting one for Claroline

// Most PHP package has increase the error reporting. 
// The line below set the error reporting to the most fitting one for Claroline
// Actually this operation is also done into claro_init_global.inc.php
// But this file is loaded to late in the forum scripts ...
error_reporting(error_reporting() & ~ E_NOTICE);


$phpEx = "php";
?>
