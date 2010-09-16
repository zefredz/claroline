CREATE TABLE IF NOT EXISTS `__CL_COURSE__calendar_event` (
    `id` int(11) NOT NULL auto_increment,
    `titre` varchar(200),
    `contenu` text,
    `day` date NOT NULL default '0000-00-00',
    `hour` time NOT NULL default '00:00:00',
    `lasting` varchar(20),
    `visibility` enum('SHOW','HIDE') NOT NULL default 'SHOW',
    `location` varchar(50),
    PRIMARY KEY (id)
) TYPE=MyISAM;