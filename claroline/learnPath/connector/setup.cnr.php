<?php // $Id$
/**
 * CLAROLINE
 *
 * Forum
 *
 * @version 1.8 $Revision$
 *
 * @copyright 2001-2005 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @see http://www.claroline.net/wiki/index.php/CLLNP
 *
 * @package CLLNP
 *
 * @author Claro Team <cvs@claroline.net>
 * @author Christophe Gesché <moosh@claroline.net>
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 *
 */

/**
 * This function retrun to kernel context that this plugin support.
 * This is probably redudant with a future value of the manifest.
 *
 * @return unknown
 */
function CLLNP_aivailable_context_tool()
{
    return array(CLARO_CONTEXT_COURSE);
}


/**
 * install work space for tool in the given course
 * @param cours_code $contextData id of course where do the work
 * @return true
 */
function CLLNP_install_tool($context,$contextData)
{
    if (CLARO_CONTEXT_COURSE == $context)
    {
        $coursesRepositorySys = $GLOBALS['coursesRepositorySys'];
        $courseRepository = claro_get_course_path($contextData);
        $tbl_cdb_names = claro_sql_get_course_tbl(claro_get_course_db_name_glued($contextData));

        $TABLELEARNPATH          = $tbl_cdb_names['lp_learnPath'];//  "lp_learnPath";
        $TABLEMODULE             = $tbl_cdb_names['lp_module'];//  "lp_module";
        $TABLELEARNPATHMODULE    = $tbl_cdb_names['lp_rel_learnPath_module'];//  "lp_rel_learnPath_module";
        $TABLEASSET              = $tbl_cdb_names['lp_asset'];//  "lp_asset";
        $TABLEUSERMODULEPROGRESS = $tbl_cdb_names['lp_user_module_progress'];//  "lp_user_module_progress";

        $sql ="
         CREATE TABLE `" . $TABLEMODULE . "` (
              `module_id` int(11) NOT NULL auto_increment,
              `name` varchar(255) NOT NULL default '',
              `comment` text NOT NULL,
              `accessibility` enum('PRIVATE','PUBLIC') NOT NULL default 'PRIVATE',
              `startAsset_id` int(11) NOT NULL default '0',
              `contentType` enum('CLARODOC','DOCUMENT','EXERCISE','HANDMADE','SCORM','LABEL') NOT NULL,
              `launch_data` text NOT NULL,
              PRIMARY KEY  (`module_id`)
            ) TYPE=MyISAM COMMENT='List of available modules used in learning paths';";
        claro_sql_query ($sql);
        $sql ="
          CREATE TABLE `" . $TABLELEARNPATH . "` (
              `learnPath_id` int(11) NOT NULL auto_increment,
              `name` varchar(255) NOT NULL default '',
              `comment` text NOT NULL,
              `lock` enum('OPEN','CLOSE') NOT NULL default 'OPEN',
              `visibility` enum('HIDE','SHOW') NOT NULL default 'SHOW',
              `rank` int(11) NOT NULL default '0',
              PRIMARY KEY  (`learnPath_id`),
              UNIQUE KEY rank (`rank`)
            ) TYPE=MyISAM COMMENT='List of learning Paths';";
        claro_sql_query ($sql);
        $sql ="
          CREATE TABLE `" . $TABLELEARNPATHMODULE . "` (
                `learnPath_module_id` int(11) NOT NULL auto_increment,
                `learnPath_id` int(11) NOT NULL default '0',
                `module_id` int(11) NOT NULL default '0',
                `lock` enum('OPEN','CLOSE') NOT NULL default 'OPEN',
                `visibility` enum('HIDE','SHOW') NOT NULL default 'SHOW',
                `specificComment` text NOT NULL,
                `rank` int(11) NOT NULL default '0',
                `parent` int(11) NOT NULL default '0',
                `raw_to_pass` tinyint(4) NOT NULL default '50',
                PRIMARY KEY  (`learnPath_module_id`)
              ) TYPE=MyISAM COMMENT='This table links module to the learning path using them';";
        claro_sql_query ($sql);
        $sql ="
          CREATE TABLE `" . $TABLEASSET . "` (
              `asset_id` int(11) NOT NULL auto_increment,
              `module_id` int(11) NOT NULL default '0',
              `path` varchar(255) NOT NULL default '',
              `comment` varchar(255) default NULL,
              PRIMARY KEY  (`asset_id`)
            ) TYPE=MyISAM COMMENT='List of resources of module of learning paths';";
        claro_sql_query ($sql);
        $sql ="
          CREATE TABLE `" . $TABLEUSERMODULEPROGRESS . "` (
              `user_module_progress_id` int(22) NOT NULL auto_increment,
              `user_id` mediumint(9) NOT NULL default '0',
              `learnPath_module_id` int(11) NOT NULL default '0',
              `learnPath_id` int(11) NOT NULL default '0',
              `lesson_location` varchar(255) NOT NULL default '',
              `lesson_status` enum('NOT ATTEMPTED','PASSED','FAILED','COMPLETED','BROWSED','INCOMPLETE','UNKNOWN') NOT NULL default 'NOT ATTEMPTED',
              `entry` enum('AB-INITIO','RESUME','') NOT NULL default 'AB-INITIO',
              `raw` tinyint(4) NOT NULL default '-1',
              `scoreMin` tinyint(4) NOT NULL default '-1',
              `scoreMax` tinyint(4) NOT NULL default '-1',
              `total_time` varchar(13) NOT NULL default '0000:00:00.00',
              `session_time` varchar(13) NOT NULL default '0000:00:00.00',
              `suspend_data` text NOT NULL,
              `credit` enum('CREDIT','NO-CREDIT') NOT NULL default 'NO-CREDIT',
              PRIMARY KEY  (`user_module_progress_id`)
            ) TYPE=MyISAM COMMENT='Record the last known status of the user in the course';";
        claro_sql_query ($sql);

        claro_mkdir($coursesRepositorySys . $courseRepository . '/modules', CLARO_FILE_PERMISSIONS);
        claro_mkdir($coursesRepositorySys . $courseRepository . '/scormPackages', CLARO_FILE_PERMISSIONS);
        claro_mkdir($coursesRepositorySys . $courseRepository . '/modules/module_1', CLARO_FILE_PERMISSIONS);
        return true;
    }
}

/**
 * @param cours_code $contextData id of course where do the work
 * @return true
 */
function CLLNP_enable_tool($context,$contextData)
{
    // learning path
    global $langSampleLearnPath, $langSampleLearnPathDesc, $langSampleDocument,
           $langSampleDocumentDesc, $langExerciceEx, $langSampleExerciseDesc ;
    $tbl_cdb_names = claro_sql_get_course_tbl(claro_get_course_db_name_glued($contextData));
      $TABLELEARNPATH          = $tbl_cdb_names['lp_learnPath'];//  "lp_learnPath";
    $TABLEMODULE             = $tbl_cdb_names['lp_module'];//  "lp_module";
    $TABLELEARNPATHMODULE    = $tbl_cdb_names['lp_rel_learnPath_module'];//  "lp_rel_learnPath_module";
    $TABLEASSET              = $tbl_cdb_names['lp_asset'];//  "lp_asset";

    // HANDMADE module type are not used for first version of claroline 1.5 beta so we don't show any exemple!
  claro_sql_query("INSERT INTO `" . $TABLELEARNPATH . "` VALUES ('1', '".addslashes($langSampleLearnPath)."', '".addslashes($langSampleLearnPathDesc)."', 'OPEN', 'SHOW', '1')");

  claro_sql_query("INSERT INTO `" . $TABLELEARNPATHMODULE . "` VALUES ('1', '1', '1', 'OPEN', 'SHOW', '', '1', '0', '50')");
  claro_sql_query("INSERT INTO `" . $TABLELEARNPATHMODULE . "` VALUES ('2', '1', '2', 'OPEN', 'SHOW', '', '2', '0', '50')");

  claro_sql_query("INSERT INTO `" . $TABLEMODULE . "` VALUES ('1', '".addslashes($langSampleDocument)."', '".addslashes($langSampleDocumentDesc)."', 'PRIVATE', '1', 'DOCUMENT', '')");
  claro_sql_query("INSERT INTO `" . $TABLEMODULE . "` VALUES ('2', '".addslashes($langExerciceEx)."', '".addslashes($langSampleExerciseDesc)."', 'PRIVATE', '2', 'EXERCISE', '')");

  claro_sql_query("INSERT INTO `" . $TABLEASSET . "` VALUES ('1', '1', '/Example_document.pdf', '')");
  claro_sql_query("INSERT INTO `" . $TABLEASSET . "` VALUES ('2', '2', '1', '')");


    return true;
}

/**
 * @param cours_code $contextData id of course where do the work
 * @return true
 */
function CLLNP_disable_tool($context,$contextData)
{
    return true;
}

/**
 * @param cours_code $contextData id of course where do the work
 * @return true
 */
function CLLNP_export_tool($context,$contextData)
{
    return true;
}
?>