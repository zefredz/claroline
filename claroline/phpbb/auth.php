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
		case "POST":
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

		case "GET":
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

// Check if the config file is writable (shouldn't be!!)
$config_file_name = "config.$phpEx";

if(strstr($PHP_SELF, "admin"))
{
	if(!strstr($PHP_SELF, "topicadmin"))
	{
		$config_file_name = "../config.$phpEx";
	}
}

// Make a database connection.
if(!$db = @mysql_connect("$dbhost", "$dbuser", "$dbpasswd"))
	die('<big>An Error Occured</big><hr>phpBB was unable to connect to the database. <BR>Please check $dbhost, $dbuser, and $dbpasswd in config.php.');

if(!@mysql_select_db($mainDbName,$db))
	die("An Error Occured<hr>phpBB was unable to find the database <b>$mainDbName</b> on your MySQL server. <br>TRY too login again Back to forum <form action = \"/index.php?mon_icampus=yes\" method='post'>	Username : 	<input type=\"text\" name=\"uname\" size=\"10\"><br>	Password :  <input type=\"password\" name=\"pass\" size=\"10\"><br>	<input type=\"submit\" value=\"Entrer\" name=\"submit\">	</form>");

if(is_banned($REMOTE_ADDR, "ip", $db)) die($l_banned);

// Setup forum Options.
$sql = "SELECT * FROM `$tbl_config` WHERE selected = 1";
if($result = mysql_query($sql, $db))
{
	if($myrow = mysql_fetch_array($result))
	{
		$sitename             = stripslashes($myrow["sitename"]);
		$allow_html           = $myrow["allow_html"      ];
		$allow_bbcode         = $myrow["allow_bbcode"    ];
		$allow_sig            = $myrow["allow_sig"       ];
		$allow_namechange     = $myrow["allow_namechange"];
		$posts_per_page       = $myrow["posts_per_page"  ];
		$hot_threshold        = $myrow["hot_threshold"   ];
		$topics_per_page      = $myrow["topics_per_page" ];
		$override_user_themes = $myrow["override_themes" ];
		$email_sig            = stripslashes($myrow["email_sig"]);
		$email_from           = $myrow["email_from"      ];
		$default_lang         = $myrow["default_lang"    ];
		$sys_lang             = $default_lang;
	}
}

// We MUST do this up here, so it's set even if the cookie's not present.
$user_logged_in = 0;
$logged_in      = 0;
$userdata       = Array();

// Check for a cookie on the users's machine.
// If the cookie exists, build an array of the users info and setup the theme.


####################################################
####################################################
####################################################

// new code for the session ID cookie..
if(isset($HTTP_COOKIE_VARS[$sesscookiename]))
{
	$sessid = $HTTP_COOKIE_VARS[$sesscookiename];
	$userid = get_userid_from_session($sessid, $sesscookietime, $REMOTE_ADDR, $db);

	if ($userid)
	{
		$user_logged_in = 1;

		update_session_time($sessid, $db);

		$userdata = get_userdata_from_id($userid, $db);

		if(is_banned($userdata[user_id], "username", $db)) die($l_banned);

		$theme = setuptheme($userdata["user_theme"], $db);

		if($theme)
		{
			$bgcolor            = $theme['bgcolor'       ];
			$table_bgcolor      = $theme['table_bgcolor' ];
			$textcolor          = $theme['textcolor'     ];
			$color1             = $theme['color1'        ];
			$color2             = $theme['color2'        ];
			$header_image       = $theme['header_image'  ];
			$newtopic_image     = $theme['newtopic_image'];
			$reply_image        = $theme['reply_image'   ];
			$linkcolor          = $theme['linkcolor'     ];
			$vlinkcolor         = $theme['vlinkcolor'    ];
			$FontFace           = $theme['fontface'      ];
			$FontSize1          = $theme['fontsize1'     ];
			$FontSize2          = $theme['fontsize2'     ];
			$FontSize3          = $theme['fontsize3'     ];
			$FontSize4          = $theme['fontsize4'     ];
			$tablewidth         = $theme['tablewidth'    ];
			$TableWidth         = $tablewidth;
			$reply_locked_image = $theme['replylocked_image'];
		}

		// Use the language the user has choosen
		if($userdata['user_lang'] != '') $default_lang = $userdata['user_lang'];

	} // if $theme
}

####################################################
####################################################
####################################################

// Old code for the permanent userid cookie..
// We only need to run this if the user's not logged in.

if (!$user_logged_in)
{
	if(isset($HTTP_COOKIE_VARS[$cookiename]))
	{
		$userdata = get_userdata_from_id($HTTP_COOKIE_VARS["$cookiename"], $db);
		if(is_banned($userdata[user_id], "username", $db))
		{
			die($l_banned);
		}

		$theme = setuptheme($userdata["user_theme"], $db);
		
		if($theme)
		{
			$bgcolor            = $theme['bgcolor'         ];
			$table_bgcolor      = $theme['table_bgcolor'   ];
			$textcolor          = $theme['textcolor'       ];
			$color1             = $theme['color1'          ];
			$color2             = $theme['color2'          ];
			$header_image       = $theme['header_image'    ];
			$newtopic_image     = $theme['newtopic_image'  ];
			$reply_image        = $theme['reply_image'     ];
			$linkcolor          = $theme['linkcolor'       ];
			$vlinkcolor         = $theme['vlinkcolor'      ];
			$FontFace           = $theme['fontface'        ];
			$FontSize1          = $theme['fontsize1'       ];
			$FontSize2          = $theme['fontsize2'       ];
			$FontSize3          = $theme['fontsize3'       ];
			$FontSize4          = $theme['fontsize4'       ];
			$tablewidth         = $theme['tablewidth'      ];
			$TableWidth         = $tablewidth;
			$reply_locked_image = $theme['replylocked_image'];
		}

			// Use the language the user has choosen.
			if($userdata['user_lang'] != '') $default_lang = $userdata['user_lang'];
	}
}

####################################################
####################################################
####################################################


// Setup the default theme

if($override_user_themes == 1 || !$theme)
{
	$sql = "SELECT * FROM `$tbl_themes` WHERE theme_default = 1";
	if(!$r = mysql_query($sql, $db))
	{
		die('Error in file '.__FILE__.' at line '.__LINE__);
	}

	if($theme = mysql_fetch_array($r))
	{
		$bgcolor            = $theme["bgcolor"          ];
		$table_bgcolor      = $theme["table_bgcolor"    ];
		$textcolor          = $theme["textcolor"        ];
		$color1             = $theme["color1"           ];
		$color2             = $theme["color2"           ];
		$header_image       = $theme["header_image"     ];
		$newtopic_image     = $theme["newtopic_image"   ];
		$reply_image        = $theme["reply_image"      ];
		$linkcolor          = $theme["linkcolor"        ];
		$vlinkcolor         = $theme["vlinkcolor"       ];
		$FontFace           = $theme["fontface"         ];
		$FontSize1          = $theme["fontsize1"        ];
		$FontSize2          = $theme["fontsize2"        ];
		$FontSize3          = $theme["fontsize3"        ];
		$FontSize4          = $theme["fontsize4"        ];
		$tablewidth         = $theme["tablewidth"       ];
		$TableWidth         = $tablewidth;
		$reply_locked_image = $theme["replylocked_image"];
	}
}

$now_time = time();

$last_visit = $_user [lastLogin];

//previous version : $last_visit = $HTTP_SESSION_VARS["user_last_login_datetime"];

// The code above was customized by claroline team.
// Previously, PhpBB works with a cookie concerning the las forum visit.
// In claroline, we use instead a variable stored in session
// called $user_last_login_datetime.

// Include the appropriate language file.
if(!strstr($PHP_SELF, "admin"))
{
   @include('language/lang_'.$default_lang.'.'.$phpEx);
}
else
{
   if(strstr($PHP_SELF, "topicadmin")) {
     @include('language/lang_'.$default_lang.'.'.$phpEx);
	} else {
     @include('../language/lang_'.$default_lang.'.'.$phpEx);
	}
}

// See if translated pictures are available..
$header_image = get_translated_file($header_image);
$reply_locked_image = get_translated_file($reply_locked_image);
$newtopic_image = get_translated_file($newtopic_image);
$reply_image = get_translated_file($reply_image);

// Set documentation locations:
$faq_url = get_translated_file("faq.$phpEx");
$bbref_url = $faq_url . "#bbcode";
$smileref_url = $faq_url . "#smilies";

?>
