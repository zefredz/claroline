CREATE TABLE IF NOT EXISTS `__CL_COURSE__tool_list` (
    `id` int(11) NOT NULL auto_increment,
    `tool_id` int(10) unsigned default NULL,
    `rank` int(10) unsigned NOT NULL,
    `visibility` tinyint(4) default 0,
    `script_url` varchar(255) default NULL,
    `script_name` varchar(255) default NULL,
    `addedTool` ENUM('YES','NO') DEFAULT 'YES',
PRIMARY KEY  (`id`)
) TYPE=MyISAM;

CREATE TABLE IF NOT EXISTS `__CL_COURSE__course_properties` (
    `id` int(11) NOT NULL auto_increment,
    `name` varchar(255) NOT NULL default '',
    `value` varchar(255) default NULL,
    `category` varchar(255) default NULL,
    PRIMARY KEY  (`id`)
) TYPE=MyISAM;

CREATE TABLE IF NOT EXISTS `__CL_COURSE__tool_intro` (
    `id` int(11) NOT NULL auto_increment,
    `tool_id` int(11) NOT NULL default '0',
    `title` varchar(255) default NULL,
    `display_date` datetime default NULL,
    `content` text,
    `rank` int(11) default '1',
    `visibility` enum('SHOW','HIDE') NOT NULL default 'SHOW',
PRIMARY KEY  (`id`)
) TYPE=MyISAM;

CREATE TABLE IF NOT EXISTS `__CL_COURSE__userinfo_content` (
   `id` int(10) unsigned NOT NULL auto_increment,
   `user_id` mediumint(8) unsigned NOT NULL default '0',
   `def_id` int(10) unsigned NOT NULL default '0',
   `ed_ip` varchar(39) default NULL,
   `ed_date` datetime default NULL,
   `content` text,
   PRIMARY KEY  (`id`),
   KEY `user_id` (`user_id`)
) TYPE=MyISAM COMMENT='content of users information';

CREATE TABLE IF NOT EXISTS `__CL_COURSE__userinfo_def` (
   `id` int(10) unsigned NOT NULL auto_increment,
   `title` varchar(80) NOT NULL default '',
   `comment` varchar(160) default NULL,
   `nbLine` int(10) unsigned NOT NULL default '5',
   `rank` tinyint(3) unsigned NOT NULL default '0',
   PRIMARY KEY  (`id`)
) TYPE=MyISAM COMMENT='categories definition for user information of a course';

CREATE TABLE IF NOT EXISTS `__CL_COURSE__group_team` (
    id int(11) NOT NULL auto_increment,
    name varchar(100) default NULL,
    description text,
    tutor int(11) default NULL,
    maxStudent int(11) NULL default '0',
    secretDirectory varchar(30) NOT NULL default '0',
PRIMARY KEY  (id)
) TYPE=MyISAM;

CREATE TABLE IF NOT EXISTS `__CL_COURSE__group_rel_team_user` (
    id int(11) NOT NULL auto_increment,
    user int(11) NOT NULL default '0',
    team int(11) NOT NULL default '0',
    status int(11) NOT NULL default '0',
    role varchar(50) NOT NULL default '',
PRIMARY KEY  (id)
) TYPE=MyISAM;

CREATE TABLE IF NOT EXISTS `__CL_COURSE__track_e_access` (
    `access_id` int(11) NOT NULL auto_increment,
    `access_user_id` int(10) default NULL,
    `access_date` datetime NOT NULL default '0000-00-00 00:00:00',
    `access_tid` int(10) default NULL,
    `access_tlabel` varchar(8) default NULL,
PRIMARY KEY  (`access_id`)
) TYPE=MyISAM  COMMENT='Record informations about access to course or tools';

CREATE TABLE IF NOT EXISTS `__CL_COURSE__track_e_downloads` (
    `down_id` int(11) NOT NULL auto_increment,
    `down_user_id` int(10) default NULL,
    `down_date` datetime NOT NULL default '0000-00-00 00:00:00',
    `down_doc_path` varchar(255) NOT NULL default '0',
PRIMARY KEY  (`down_id`)
) TYPE=MyISAM  COMMENT='Record informations about downloads';

CREATE TABLE IF NOT EXISTS `__CL_COURSE__track_e_uploads` (
    `upload_id` int(11) NOT NULL auto_increment,
    `upload_user_id` int(10) default NULL,
    `upload_date` datetime NOT NULL default '0000-00-00 00:00:00',
    `upload_work_id` int(11) NOT NULL default '0',
PRIMARY KEY  (`upload_id`)
) TYPE=MyISAM  COMMENT='Record some more informations about uploaded works';

CREATE TABLE IF NOT EXISTS `__CL_COURSE__lnk_links` (
    `id` int(11) NOT NULL auto_increment,
    `src_id` int(11) NOT NULL default '0',
    `dest_id` int(11) NOT NULL default '0',
    `creation_time` timestamp(14) NOT NULL,
PRIMARY KEY  (`id`)
) TYPE=MyISAM;

CREATE TABLE IF NOT EXISTS `__CL_COURSE__lnk_resources` (
    `id` int(11) NOT NULL auto_increment,
    `crl` text NOT NULL,
    `title` text NOT NULL,
PRIMARY KEY  (`id`)
) TYPE=MyISAM;

CREATE TABLE IF NOT EXISTS `__CL_COURSE__tracking_event` (
  `id` int(11) NOT NULL auto_increment,
  `tool_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `group_id` int(11) DEFAULT NULL,
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `type` varchar(60) NOT NULL DEFAULT '',
  `data` text NOT NULL DEFAULT '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;