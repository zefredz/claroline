<?php //    $Id$

// Set error reporting to sane value.
// It will NOT report uninitialized variables
//error_reporting  (E_ERROR | E_WARNING | E_PARSE);

$tlabelReq = 'CLFRM___';
require '../inc/claro_init_global.inc.php';


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

// Set this to the web path to your phpBB installation. For example, if you 
// have phpBB installed in http://www.mysite.com/phpBB, leave this setting 
// EXACTLY how it is, you're done. If you have phpBB installed in 
// http://www.mysite.com/forums Change this to: $url_phpbb = "/forums";

$url_phpbb       = $rootWeb.'claroline/phpbb';
$url_admin       = $url_phpbb . '/admin';
$url_images      = $clarolineRepositoryWeb.'img';
$url_smiles      = $url_images . '/smiles';
$url_phpbb_index = $url_phpbb  . '/index.php';
$url_admin_index = $url_admin  . '/index.php';

/* Stuff for priv msgs - not in DB yet: */

$allow_pmsg_bbcode   = 1; // Allow BBCode in private messages?
$allow_pmsg_html     = 0; // Allow HTML in private message?

// Setup forum Options.
$sitename             = stripslashes('');
$allow_html           = 1;
$allow_bbcode         = 1;
$allow_sig            = 1;
$allow_namechange     = 0;
$posts_per_page       = 5;
$hot_threshold        = 15;
$topics_per_page      = 5;
$override_user_themes = 0;
$email_sig            = 'Yours sincerely, your professor';
$email_from           = '';
$default_lang         = 'english';
$sys_lang             = $default_lang;


/* -- Other Settings -- */
$phpbbversion       = '1.4.0';
$dbhost             = $dbHost;
$dbuser             = $dbLogin;
$dbpasswd           = $dbPass;


/* -- DB table names  -- */

$tbl_cdb_names = claro_sql_get_course_tbl();
$tbl_mdb_names = claro_sql_get_main_tbl();

$tbl_categories       = $tbl_cdb_names['bb_categories'];
$tbl_forums           = $tbl_cdb_names['bb_forums'];
$tbl_posts            = $tbl_cdb_names['bb_posts'];
$tbl_posts_text       = $tbl_cdb_names['bb_posts_text'];
$tbl_priv_msgs        = $tbl_cdb_names['bb_priv_msgs'];
$tbl_topics           = $tbl_cdb_names['bb_topics'];
$tbl_user_notify      = $tbl_cdb_names['bb_rel_topic_userstonotify'];
$tbl_whosonline       = $tbl_cdb_names['bb_whosonline'];

$tbl_group_properties = $tbl_cdb_names['group_property'];
$tbl_student_group	  = $tbl_cdb_names['group_team'];
$tbl_user_group       = $tbl_cdb_names['group_rel_team_user'];

$tbl_users            = $tbl_mdb_names['user'];
$tbl_course_user      = $tbl_mdb_names['rel_course_user'];

$is_groupPrivate      = $_groupProperties['private'];
$userdata               = array();
$userdata['first_name'] = $_user['firstName'];
$userdata['last_name' ] = $_user['lastName' ];
$userdata['user_id']    = ($_uid) ?  $_uid : -1;
$nom                    = $_user['lastName' ]; // FROM CLAROLINE
$prenom                 = $_user['firstName']; // FROM CLAROLINE
$last_visit             = $_user['lastLogin']; // FROM CLAROLINE
$user_logged_in         = $_uid ? 1 : 0;
$logged_in              = 0; // so it's set even if the cookie's not present.
$now_time               = time();

if( is_banned($REMOTE_ADDR, 'ip', $db) ) error_die($l_banned);


// Disable Magic Quotes
function stripslashes_array(&$the_array_element, $the_array_element_key)
{
   $the_array_element = stripslashes($the_array_element);
}

if( get_magic_quotes_gpc() == 1)
{
    switch($REQUEST_METHOD)
    {
        case 'POST':
            $HttpReqVarList = & $_POST;
            break;
        case 'GET':
            $HttpReqVarList = & $_GET;
            break;
        default: 
            $HttpReqVarList = array();
    }

    while (list ($key, $val) = each ($HttpReqVarList))
    {
        if( is_array($val) )
        {
            array_walk($val, 'stripslashes_array');
            $$key = $val;
        }
        else
        {
            $$key = stripslashes($val);
        }
    }
}

$config_file_name = 'config.php';

if( strstr($_SERVER['PHP_SELF'], 'admin') && ! strstr($_SERVER['PHP_SELF'], 'topicadmin') )
{
    $config_file_name = '../config.php';
}

?>
