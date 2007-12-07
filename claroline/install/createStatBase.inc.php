<?php # $Id$

//----------------------------------------------------------------------
// CLAROLINE
//----------------------------------------------------------------------
// Copyright (c) 2001-2003 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see 'credits' file
//----------------------------------------------------------------------
        /**************Statistics Tables****************/
        //mysql_query("DROP TABLE IF EXISTS track_c_browsers");
//         mysql_query("DROP TABLE IF EXISTS track_c_countries");
//         mysql_query("DROP TABLE IF EXISTS track_c_os");
//         mysql_query("DROP TABLE IF EXISTS track_c_providers");
//         mysql_query("DROP TABLE IF EXISTS track_c_referers");
// 
//         mysql_query("DROP TABLE IF EXISTS track_e_access");
//         mysql_query("DROP TABLE IF EXISTS track_e_default");
//         mysql_query("DROP TABLE IF EXISTS track_e_downloads");
//         mysql_query("DROP TABLE IF EXISTS track_e_exercices");
//         mysql_query("DROP TABLE IF EXISTS track_e_links");
//         mysql_query("DROP TABLE IF EXISTS track_e_login");
//         mysql_query("DROP TABLE IF EXISTS track_e_open");
        //mysql_query("DROP TABLE IF EXISTS track_e_subscriptions");

        $sql = "CREATE TABLE `track_c_browsers` (
                  `id` int(11) NOT NULL auto_increment,
                  `browser` varchar(255) NOT NULL default '',
                  `counter` int(11) NOT NULL default '0',
                  PRIMARY KEY  (`id`)
                ) TYPE=MyISAM COMMENT='record browsers occurences'";
        mysql_query($sql);
        $sql = "CREATE TABLE `track_c_countries` (
                  `id` int(11) NOT NULL auto_increment,
                  `code` varchar(40) NOT NULL default '',
                  `country` varchar(50) NOT NULL default '',
                  `counter` int(11) NOT NULL default '0',
                  PRIMARY KEY  (`id`)
                ) TYPE=MyISAM";
        mysql_query($sql);
        $sql = "CREATE TABLE `track_c_os` (
                  `id` int(11) NOT NULL auto_increment,
                  `os` varchar(255) NOT NULL default '',
                  `counter` int(11) NOT NULL default '0',
                  PRIMARY KEY  (`id`)
                ) TYPE=MyISAM COMMENT='record OS occurences'";
        mysql_query($sql);
        $sql = "CREATE TABLE `track_c_providers` (
                  `id` int(11) NOT NULL auto_increment,
                  `provider` varchar(255) NOT NULL default '',
                  `counter` int(11) NOT NULL default '0',
                  PRIMARY KEY  (`id`)
                ) TYPE=MyISAM COMMENT='list of providers used by users and number of occurences'";
        mysql_query($sql);
        $sql = "CREATE TABLE `track_c_referers` (
                  `id` int(11) NOT NULL auto_increment,
                  `referer` varchar(255) NOT NULL default '',
                  `counter` int(11) NOT NULL default '0',
                  PRIMARY KEY  (`id`)
                ) TYPE=MyISAM COMMENT='record refering url occurences'";
        mysql_query($sql);
        $sql = "CREATE TABLE `track_e_default` (
                  `default_id` int(11) NOT NULL auto_increment,
                  `default_user_id` int(10) NOT NULL default '0',
                  `default_cours_code` varchar(40) NOT NULL default '',
                  `default_date` datetime NOT NULL default '0000-00-00 00:00:00',
                  `default_event_type` varchar(20) NOT NULL default '',
                  `default_value_type` varchar(20) NOT NULL default '',
                  `default_value` tinytext NOT NULL,
                  PRIMARY KEY  (`default_id`)
                ) TYPE=MyISAM COMMENT='Use for other develloppers users'";
        mysql_query($sql);
        $sql = "CREATE TABLE `track_e_login` (
                  `login_id` int(11) NOT NULL auto_increment,
                  `login_user_id` int(10) NOT NULL default '0',
                  `login_date` datetime NOT NULL default '0000-00-00 00:00:00',
                  `login_ip` char(15) NOT NULL default '',
                  PRIMARY KEY  (`login_id`)
                ) TYPE=MyISAM COMMENT='Record informations about logins'";
        mysql_query($sql);
        $sql = "CREATE TABLE `track_e_open` (
                  `open_id` int(11) NOT NULL auto_increment,
                  `open_date` datetime NOT NULL default '0000-00-00 00:00:00',
                  PRIMARY KEY  (`open_id`)
                ) TYPE=MyISAM COMMENT='Record informations about software used by users'";
        mysql_query($sql);
        /*
        $sql = "CREATE TABLE `track_e_subscriptions` (
                  `sub_id` int(11) NOT NULL auto_increment,
                  `sub_user_id` int(10) NOT NULL default '0',
                  `sub_date` datetime NOT NULL default '0000-00-00 00:00:00',
                  `sub_cours_id` int(11) NOT NULL default '0',
                  `sub_action` enum('sub','unsub') NOT NULL default 'sub',
                  PRIMARY KEY  (`sub_id`)
                ) TYPE=MyISAM COMMENT='Record informations about subscriptions to courses'";
        mysql_query($sql);
        */
?>
