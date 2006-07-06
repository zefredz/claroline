<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );

/**
 * install work space for tool in the given course
 * @param cours_code $course_id id of course where do the work
 * @return true
 */
function CLWRK_install_tool($context,$course_id)
{
    global $coursesRepositorySys;
    $tbl_cdb_names = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
    $courseRepositoryPath = $coursesRepositorySys.claro_get_course_path($course_id);

    $sql ="
    CREATE TABLE `" . $tbl_cdb_names['wrk_submission'] . "` (
        `id` int(11) NOT NULL auto_increment,
        `assignment_id` int(11) default NULL,
        `parent_id` int(11) default NULL,
        `user_id` int(11) default NULL,
        `group_id` int(11) default NULL,
        `title` varchar(200) NOT NULL default '',
        `visibility` enum('VISIBLE','INVISIBLE') default 'VISIBLE',
        `creation_date` datetime NOT NULL default '0000-00-00 00:00:00',
        `last_edit_date` datetime NOT NULL default '0000-00-00 00:00:00',
        `authors` varchar(200) NOT NULL default '',
        `submitted_text` text NOT NULL,
        `submitted_doc_path` varchar(200) NOT NULL default '',
        `private_feedback` text default NULL,
        `original_id` int(11) default NULL,
        `score` smallint(3) NULL default NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;";
    claro_sql_query($sql);

 $sql = "
    CREATE TABLE `" . $tbl_cdb_names['wrk_assignment'] . "` (
        `id` int(11) NOT NULL auto_increment,
        `title` varchar(200) NOT NULL default '',
        `description` text NOT NULL,
        `visibility` enum('VISIBLE','INVISIBLE') NOT NULL default 'VISIBLE',
        `def_submission_visibility` enum('VISIBLE','INVISIBLE') NOT NULL default 'VISIBLE',
        `assignment_type` enum('INDIVIDUAL','GROUP') NOT NULL default 'INDIVIDUAL',
        `authorized_content`  enum('TEXT','FILE','TEXTFILE') NOT NULL default 'FILE',
        `allow_late_upload` enum('YES','NO') NOT NULL default 'YES',
        `start_date` datetime NOT NULL default '0000-00-00 00:00:00',
        `end_date` datetime NOT NULL default '0000-00-00 00:00:00',
        `prefill_text` text NOT NULL,
        `prefill_doc_path` varchar(200) NOT NULL default '',
        `prefill_submit` enum('ENDDATE','AFTERPOST') NOT NULL default 'ENDDATE',
        PRIMARY KEY  (`id`)
    ) TYPE=MyISAM;";

    claro_sql_query($sql);
    claro_mkdir($courseRepositoryPath . '/work', CLARO_FILE_PERMISSIONS);

}

/**
 * @param cours_code $course_id id of course where do the work
 * @return true
 */
function CLWRK_enable_tool($context,$course_id)
{
    return true;
}
?>