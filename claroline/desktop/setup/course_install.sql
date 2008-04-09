CREATE TABLE IF NOT EXISTS `__CL_desktop_portlet` (
  `label` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `rank` int(11) NOT NULL,
  `activated` int(11) NOT NULL,
  PRIMARY KEY  (`label`)
) TYPE=MyISAM;

CREATE TABLE IF NOT EXISTS `__CL_desktop_portlet_data` (
  `label` varchar(255) NOT NULL,
  `idUser` int(11) NOT NULL,
  `data` text NOT NULL,
  PRIMARY KEY  (`label`),
  KEY `label` (`label`,`idUser`)
) TYPE=MyISAM;