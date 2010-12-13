CREATE TABLE IF NOT EXISTS `__CL_COURSE__announcement` (
    `id` mediumint(11) NOT NULL auto_increment,
    `title` varchar(80) default NULL,
    `contenu` text,
    `temps` date default NULL,
    `ordre` mediumint(11) NOT NULL default '0',
    `visibility` enum('SHOW','HIDE') NOT NULL default 'SHOW',
    PRIMARY KEY  (`id`)
) TYPE=MyISAM COMMENT='announcements table';