<?php //    $Id$

session_start();
$langFile = 'phpbb';
$tlabelReq = 'CLFRM___';
require '../inc/claro_init_global.inc.php';
include('../inc/lib/debug.lib.inc.php');

/***************************************************************************
                          config.php  -  description
                             -------------------
    begin                : Sat June 17 2000
    copyright            : (C) 2001 The phpBB Group
 	 email                : support@phpbb.com


 ***************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/
// This is the only setting you should need to change in this file.
// You should set this to the web path to your phpBB installation.
// For example, if you have phpBB installed in:
// http://www.mysite.com/phpBB
// Leave this setting EXACTLY how it is, you're done.
// If you have phpBB installed in:
// http://www.mysite.com/forums
// Change this to:
// $url_phpbb = "/forums";
// Once this is set you should not need to modify anything else in this file.
$url_phpbb       =$urlAppend."/claroline/phpbb";

// -- Edit the following ONLY if you cannot login and $url_phpbb is set correclty --
// You shouldn't have to change any of these 5.
$url_admin           = $url_phpbb . '/admin';
$url_images         = $clarolineRepositoryWeb.'img';
$url_smiles         = $url_images . '/smiles';
$url_phpbb_index    = $url_phpbb  . '/index.' . $phpEx;
$url_admin_index    = $url_admin  . '/index.' . $phpEx;

/* -- Cookie settings (lastvisit, userid) -- */

// Most likely you can leave this be, however if you have problems
// logging into the forum set this to your domain name, without
// the http://
// For example, if your forum is at http://www.mysite.com/phpBB then
// set this value to
// $cookiedomain = "www.mysite.com";
$cookiedomain        = parse_url($rootWeb);
$cookiedomain        = $cookiedomain["host"];
// It should be safe to leave this alone as well. But if you do change it
// make sure you don't set it to a variable already in use such as 'forum'.
$cookiename          = "phpBB";

// It should be safe to leave these alone as well.
$cookiepath          = $url_phpbb;
$cookiesecure        = false;

/* -- Cookie settings (sessions) -- */

// This is the cookie name for the sessions cookie, you shouldn't have to change it
$sesscookiename = "phpBBsession";
// This is the number of seconds that a session lasts for, 3600 == 1 hour.
// The session will exprire if the user dosan't view a page on the forum within
// this amount of time.
$sesscookietime      = 3600;

/**
 * This setting is only for people running Microsoft IIS.
 * If you're running IIS and your users cannot login using
 * the "login" link on the main page, but they CAN login
 * through other pages like preferences, then you should
 * change this setting to 1. Otherwise, leave at set
 * to 0, because this is an ugly hack around some IIS junk.
 */
// Change to "define('USE_IIS_LOGIN_HACK', 1);" if you need to.
define('USE_IIS_LOGIN_HACK', 0);

/* Stuff for priv msgs - not in DB yet: */

$allow_pmsg_bbcode   = 1; // Allow BBCode in private messages?
$allow_pmsg_html     = 0; // Allow HTML in private message?

/* -- You shouldn't have to change anything after this point */

/* -- Cosmetic Settings -- */

$FontColor           = "#FFFFFF";
$textcolorMessage    = "#FFFFFF";  // Message Font Text Color
$FontSizeMessage     = "1";        // Message Font Text Size
$FontFaceMessage     = "Arial";    // Message Font Text Face

/* -- Other Settings -- */
$phpbbversion       = "1.4.0";
$dbhost             = $dbHost;
$dbuser             = $dbLogin;
$dbpasswd           = $dbPass;


/* -- DB table names  -- */

$tbl_access           = $_course['dbNameGlu'].'bb_access';
$tbl_banlist          = $_course['dbNameGlu'].'bb_banlist';
$tbl_catagories       = $_course['dbNameGlu'].'bb_categories';
$tbl_config           = $_course['dbNameGlu'].'bb_config';
$tbl_disallow         = $_course['dbNameGlu'].'bb_disallow';
$tbl_access           = $_course['dbNameGlu'].'bb_access';
$tbl_mods             = $_course['dbNameGlu'].'bb_mods';
$tbl_forums           = $_course['dbNameGlu'].'bb_forums';
$tbl_headermetafooter = $_course['dbNameGlu'].'bb_headermetafooter';
$tbl_posts            = $_course['dbNameGlu'].'bb_posts';
$tbl_posts_text       = $_course['dbNameGlu'].'bb_posts_text';
$tbl_priv_msgs        = $_course['dbNameGlu'].'bb_priv_msgs';
$tbl_ranks            = $_course['dbNameGlu'].'bb_ranks';
$tbl_sessions         = $_course['dbNameGlu'].'bb_sessions';
$tbl_smiles           = $_course['dbNameGlu'].'bb_smiles';
$tbl_themes           = $_course['dbNameGlu'].'bb_themes';
$tbl_topics           = $_course['dbNameGlu'].'bb_topics';
$tbl_users            = $_course['dbNameGlu'].'bb_users';
$tbl_whosonline       = $_course['dbNameGlu'].'bb_whosonline';
$tbl_words            = $_course['dbNameGlu'].'bb_words';
$tbl_group_properties = $_course['dbNameGlu'].'group_property';
$tbl_student_group	  = $_course['dbNameGlu'].'group_team';
$tbl_user_group       = $_course['dbNameGlu'].'group_rel_team_user';
$tbl_user_notify      = $_course['dbNameGlu'].'bb_rel_topic_userstonotify';
$is_groupPrivate      = $_groupProperties['private'];

$nom    = $_user['lastName' ];
$prenom = $_user['firstName'];

//$is_groupAllowed      = $is_groupAllowed;
?>