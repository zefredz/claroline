<?php // $Id$
/**
 * CLAROLINE 
 *
 * Create Statistics Tables
 * @var $statsTblPrefixForm prefix set during  install, and keep in mainconf
 * @private $sql var where build sql request.
 *
 * @version 1.7 $Revision$
 *
 * @copyright 2001-2005 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @see http://www.claroline.net/wiki/index.php/Install
 *
 * @author Claro Team <cvs@claroline.net>
 * @author Christophe Gesché <moosh@claroline.net>
 *
 * @package INSTALL
 *
 */

/*         claro_sql_query("DROP TABLE IF EXISTS track_e_default");
 *         claro_sql_query("DROP TABLE IF EXISTS track_e_login");
 *         claro_sql_query("DROP TABLE IF EXISTS track_e_open");
 *
 */
        $sql = "CREATE TABLE `".$statsTblPrefixForm."track_e_default` (
                  `default_id` int(11) NOT NULL auto_increment,
                  `default_user_id` int(11)  NOT NULL default '0',
                  `default_cours_code` varchar(40) NOT NULL default '',
                  `default_date` datetime NOT NULL default '0000-00-00 00:00:00',
                  `default_event_type` varchar(20) NOT NULL default '',
                  `default_value_type` varchar(20) NOT NULL default '',
                  `default_value` tinytext NOT NULL,
                  PRIMARY KEY  (`default_id`)
                ) TYPE=MyISAM COMMENT='Use for other developpers users'";
        claro_sql_query($sql);


        $sql = "CREATE TABLE `".$statsTblPrefixForm."track_e_login` (
                  `login_id` int(11) NOT NULL auto_increment,
                  `login_user_id` int(11)  NOT NULL default '0',
                  `login_date` datetime NOT NULL default '0000-00-00 00:00:00',
                  `login_ip` char(15) NOT NULL default '',
                  PRIMARY KEY  (`login_id`)
                ) TYPE=MyISAM COMMENT='Record informations about logins'";
        claro_sql_query($sql);


        $sql = "CREATE TABLE `".$statsTblPrefixForm."track_e_open` (
                  `open_id` int(11) NOT NULL auto_increment,
                  `open_date` datetime NOT NULL default '0000-00-00 00:00:00',
                  PRIMARY KEY  (`open_id`)
                ) TYPE=MyISAM COMMENT='Record informations about software used by users'";
        claro_sql_query($sql);
?>