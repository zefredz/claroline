<?php
/***************************************************************************
                          auth.php  -  description
                             -------------------
    begin                : Sat June 17 2000
    copyright            : (C) 2001 The phpBB Group
    email                : support@phpbb.com

    $Id$

 ***************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/
// Set error reporting to sane value. It will NOT report uninitialized variables
error_reporting  (E_ERROR | E_WARNING | E_PARSE);

// Disable Magic Quotes
function stripslashes_array(&$the_array_element, $the_array_element_key, $data)
{
   $the_array_element = stripslashes($the_array_element);
}

if( get_magic_quotes_gpc() == 1)
{
	switch($REQUEST_METHOD)
	{
		case 'POST':
            $HttpReqVarList = & $HTTP_POST_VARS;
            break;
        case 'GET':
            $HttpReqVarList = & $HTTP_GET_VARS;
            break;
        default: 
            $HttpReqVarList = array();
    }

    while (list ($key, $val) = each ($HttpReqVarList))
    {
        if( is_array($val) )
        {
            array_walk($val, 'stripslashes_array', '');
            $$key = $val;
        }
        else
        {
            $$key = stripslashes($val);
        }
    }
}

$config_file_name = 'config.php';

if( strstr($PHP_SELF, 'admin') && ! strstr($PHP_SELF, 'topicadmin') )
{
    $config_file_name = '../config.php';
}

if( is_banned($REMOTE_ADDR, 'ip', $db) ) error_die($l_banned);


// We MUST do this up here, so it's set even if the cookie's not present.

$user_logged_in = 0;
$logged_in      = 0;
$userdata       = Array();


$now_time   = time();
$last_visit = $_user ['lastLogin'];
// The code above was customized by CLAROLINE team. Previously, PhpBB works 
// with a cookie concerning the las forum visit. In claroline, we use instead a 
// variable stored in session.

// Include the appropriate language file.
if (strstr($PHP_SELF, 'admin') && ! strstr($PHP_SELF, 'topicadmin') )
{
    @include('../language/lang_'.$default_lang.'.php');
}
else
{
    @include('language/lang_'.$default_lang.'.php');
}

?>