<?php // $Id$

//----------------------------------------------------------------------
// CLAROLINE 1.6.*
//----------------------------------------------------------------------
// Copyright (c) 2001-2004 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see 'credits' file
//----------------------------------------------------------------------
        /**************Statistics Tables****************/
        //claro_sql_query("DROP TABLE IF EXISTS track_c_browsers");
//         claro_sql_query("DROP TABLE IF EXISTS track_c_countries");
//         claro_sql_query("DROP TABLE IF EXISTS track_c_os");
//         claro_sql_query("DROP TABLE IF EXISTS track_c_providers");
//         claro_sql_query("DROP TABLE IF EXISTS track_c_referers");
// 
//         claro_sql_query("DROP TABLE IF EXISTS track_e_access");
//         claro_sql_query("DROP TABLE IF EXISTS track_e_default");
//         claro_sql_query("DROP TABLE IF EXISTS track_e_downloads");
//         claro_sql_query("DROP TABLE IF EXISTS track_e_exercices");
//         claro_sql_query("DROP TABLE IF EXISTS track_e_links");
//         claro_sql_query("DROP TABLE IF EXISTS track_e_login");
//         claro_sql_query("DROP TABLE IF EXISTS track_e_open");
        //claro_sql_query("DROP TABLE IF EXISTS track_e_subscriptions");

        $sql = "CREATE TABLE `track_c_browsers` (
                  `id` int(11) NOT NULL auto_increment,
                  `browser` varchar(255) NOT NULL default '',
                  `counter` int(11) NOT NULL default '0',
                  PRIMARY KEY  (`id`)
                ) TYPE=MyISAM COMMENT='record browsers occurences'";
        claro_sql_query($sql);
        $sql = "CREATE TABLE `track_c_countries` (
                  `id` int(11) NOT NULL auto_increment,
                  `code` varchar(40) NOT NULL default '',
                  `country` varchar(50) NOT NULL default '',
                  `counter` int(11) NOT NULL default '0',
                  PRIMARY KEY  (`id`)
                ) TYPE=MyISAM";
        claro_sql_query($sql);
        $sql = "CREATE TABLE `track_c_os` (
                  `id` int(11) NOT NULL auto_increment,
                  `os` varchar(255) NOT NULL default '',
                  `counter` int(11) NOT NULL default '0',
                  PRIMARY KEY  (`id`)
                ) TYPE=MyISAM COMMENT='record OS occurences'";
        claro_sql_query($sql);
        $sql = "CREATE TABLE `track_c_providers` (
                  `id` int(11) NOT NULL auto_increment,
                  `provider` varchar(255) NOT NULL default '',
                  `counter` int(11) NOT NULL default '0',
                  PRIMARY KEY  (`id`)
                ) TYPE=MyISAM COMMENT='list of providers used by users and number of occurences'";
        claro_sql_query($sql);
        $sql = "CREATE TABLE `track_c_referers` (
                  `id` int(11) NOT NULL auto_increment,
                  `referer` varchar(255) NOT NULL default '',
                  `counter` int(11) NOT NULL default '0',
                  PRIMARY KEY  (`id`)
                ) TYPE=MyISAM COMMENT='record refering url occurences'";
        claro_sql_query($sql);
        $sql = "CREATE TABLE `track_e_default` (
                  `default_id` int(11) NOT NULL auto_increment,
                  `default_user_id` int(11)  NOT NULL default '0',
                  `default_cours_code` varchar(40) NOT NULL default '',
                  `default_date` datetime NOT NULL default '0000-00-00 00:00:00',
                  `default_event_type` varchar(20) NOT NULL default '',
                  `default_value_type` varchar(20) NOT NULL default '',
                  `default_value` tinytext NOT NULL,
                  PRIMARY KEY  (`default_id`)
                ) TYPE=MyISAM COMMENT='Use for other develloppers users'";
        claro_sql_query($sql);
        $sql = "CREATE TABLE `track_e_login` (
                  `login_id` int(11) NOT NULL auto_increment,
                  `login_user_id` int(11)  NOT NULL default '0',
                  `login_date` datetime NOT NULL default '0000-00-00 00:00:00',
                  `login_ip` char(15) NOT NULL default '',
                  PRIMARY KEY  (`login_id`)
                ) TYPE=MyISAM COMMENT='Record informations about logins'";
        claro_sql_query($sql);
        $sql = "CREATE TABLE `track_e_open` (
                  `open_id` int(11) NOT NULL auto_increment,
                  `open_date` datetime NOT NULL default '0000-00-00 00:00:00',
                  PRIMARY KEY  (`open_id`)
                ) TYPE=MyISAM COMMENT='Record informations about software used by users'";
        claro_sql_query($sql);
        /*
        $sql = "CREATE TABLE `track_e_subscriptions` (
                  `sub_id` int(11) NOT NULL auto_increment,
                  `sub_user_id` int(11)  NOT NULL default '0',
                  `sub_date` datetime NOT NULL default '0000-00-00 00:00:00',
                  `sub_cours_id` int(11) NOT NULL default '0',
                  `sub_action` enum('sub','unsub') NOT NULL default 'sub',
                  PRIMARY KEY  (`sub_id`)
                ) TYPE=MyISAM COMMENT='Record informations about subscriptions to courses'";
        claro_sql_query($sql);
        */
?>
