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
// Set the error reporting to a sane value:
error_reporting  (E_ERROR | E_WARNING | E_PARSE); // This will NOT report uninitialized variables

// Disable Magic Quotes
function stripslashes_array(&$the_array_element, $the_array_element_key, $data)
{
   $the_array_element = stripslashes($the_array_element);
}

if(get_magic_quotes_gpc() == 1)
{
	switch($REQUEST_METHOD)
	{
		case 'POST':
			while (list ($key, $val) = each ($HTTP_POST_VARS))
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
		break;

		case 'GET':
		while (list ($key, $val) = each ($HTTP_GET_VARS))
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
		break;
	}												// end switch
}													//end if get_magic_quote_gpc

$config_file_name = "config.php";

if(strstr($PHP_SELF, 'admin'))
{
	if( ! strstr($PHP_SELF, "topicadmin") )
	{
		$config_file_name = "../config.php";
	}
}

if(is_banned($REMOTE_ADDR, 'ip', $db)) error_die($l_banned);

// Setup forum Options.
$sql = "SELECT * FROM `$tbl_config` WHERE selected = 1";
if($result = mysql_query($sql, $db))
{
	if($myrow = mysql_fetch_array($result))
	{
		$sitename             = stripslashes($myrow['sitename']);
		$allow_html           = $myrow['allow_html'      ];
		$allow_bbcode         = $myrow['allow_bbcode'    ];
		$allow_sig            = $myrow['allow_sig'       ];
		$allow_namechange     = $myrow['allow_namechange'];
		$posts_per_page       = $myrow['posts_per_page'  ];
		$hot_threshold        = $myrow['hot_threshold'   ];
		$topics_per_page      = $myrow['topics_per_page' ];
		$override_user_themes = $myrow['override_themes' ];
		$email_sig            = stripslashes($myrow['email_sig']);
		$email_from           = $myrow['email_from'      ];
		$default_lang         = $myrow['default_lang'    ];
		$sys_lang             = $default_lang;
	}
}

// We MUST do this up here, so it's set even if the cookie's not present.
$user_logged_in = 0;
$logged_in      = 0;
$userdata       = Array();

// Check for a cookie on the users's machine.
// If the cookie exists, build an array of the users info and setup the theme.


$now_time = time();

$last_visit = $_user ['lastLogin'];

//previous version : $last_visit = $HTTP_SESSION_VARS["user_last_login_datetime"];

// The code above was customized by claroline team.
// Previously, PhpBB works with a cookie concerning the las forum visit.
// In claroline, we use instead a variable stored in session
// called $user_last_login_datetime.

// Include the appropriate language file.
if(!strstr($PHP_SELF, 'admin'))
{
   @include('language/lang_'.$default_lang.'.'.$phpEx);
}
else
{
    if(strstr($PHP_SELF, 'topicadmin'))
    {
         @include('language/lang_'.$default_lang.'.'.$phpEx);
    } 
    else
    {
        @include('../language/lang_'.$default_lang.'.'.$phpEx);
    }
}

?>