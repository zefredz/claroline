CREATE TABLE IF NOT EXISTS `__CL_COURSE__document` (
    `id` int(4) NOT NULL auto_increment,
    `path` varchar(255) NOT NULL,
    `visibility` char(1) DEFAULT 'v' NOT NULL,
    `comment` varchar(255),
    PRIMARY KEY (id)
) TYPE=MyISAM;