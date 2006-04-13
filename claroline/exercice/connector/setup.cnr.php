<?php // $Id$
/**
 * CLAROLINE
 *
 * The lib provide claroline kernel library extention for 'annoucement' tools
 *
 * DB Table structure:
 * ---
 *
 * id         : announcement id
 * contenu    : announcement content
 * temps      : date of the announcement introduction / modification
 * title      : optionnal title for an announcement
 * ordre      : order of the announcement display
 *              (the announcements are display in desc order)
 *
 * @version 1.8 $Revision$
 *
 * @copyright (c) 2001-2005 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @package CLQWZ
 *
 * @author Claro Team <cvs@claroline.net>
 * @author Christophe Gesché <moosh@claroline.net>
 */


/**
 * This function retrun to kernel context that this plugin support.
 * This is probably redudant with a future value of the manifest.
 *
 * @return unknown
 */
function CLQWZ_aivailable_context_tool()
{
    return array(CLARO_CONTEXT_COURSE);
}

/**
 * install work space for tool in the given course
 * @param cours_code $course_id id of course where do the work
 * @return true
 */
function CLQWZ_install_tool($context,$course_id)
{
    global $coursesRepositorySys;
    $courseRepository = claro_get_course_path($course_id);

    $tbl_cdb_names = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
    $TABLEQUIZ              = $tbl_cdb_names['quiz_test'];//  $courseDbName."quiz_test";
    $TABLEQUIZQUESTION      = $tbl_cdb_names['quiz_rel_test_question'];
    $TABLEQUIZQUESTIONLIST  = $tbl_cdb_names['quiz_question'];//  "quiz_question";
    $TABLEQUIZANSWERSLIST   = $tbl_cdb_names['quiz_answer'];//  "quiz_answer";

//  EXERCICES
claro_sql_query("
    CREATE TABLE `" . $TABLEQUIZ . "` (
        `id` mediumint(8) unsigned NOT NULL auto_increment,
        `titre` varchar(200) NOT NULL,
        `description` text NOT NULL,
        `type` tinyint(4) unsigned NOT NULL default '1',
        `random` smallint(6) NOT NULL default '0',
        `active` tinyint(4) unsigned NOT NULL default '0',
        `max_time` smallint(5) unsigned NOT NULL default '0',
        `max_attempt` tinyint(3) unsigned NOT NULL default '0',
        `show_answer` enum('ALWAYS','NEVER','LASTTRY') NOT NULL default 'ALWAYS',
        `anonymous_attempts` enum('YES','NO') NOT NULL default 'YES',
        `start_date` datetime NOT NULL default '0000-00-00 00:00:00',
        `end_date` datetime NOT NULL default '0000-00-00 00:00:00',
    PRIMARY KEY  (id)
    )");

//  QUESTIONS
claro_sql_query("
    CREATE TABLE `" . $TABLEQUIZQUESTIONLIST . "` (
        id mediumint(8) unsigned NOT NULL auto_increment,
        question varchar(200) NOT NULL,
        description text NOT NULL,
        ponderation float unsigned default NULL,
        q_position mediumint(8) unsigned NOT NULL default '1',
        type tinyint(3) unsigned NOT NULL default '2',
   attached_file varchar(50) default '',
    PRIMARY KEY  (id)
    )");



//  REPONSES
claro_sql_query("
    CREATE TABLE `" . $TABLEQUIZANSWERSLIST . "` (
        id mediumint(8) unsigned NOT NULL default '0',
        question_id mediumint(8) unsigned NOT NULL default '0',
        reponse text NOT NULL,
        correct mediumint(8) unsigned default NULL,
        comment text default NULL,
        ponderation float default NULL,
        r_position mediumint(8) unsigned NOT NULL default '1',
    PRIMARY KEY  (id, question_id)
    )");

//  EXERCICE_QUESTION
$sql= "
    CREATE TABLE `" . $TABLEQUIZQUESTION . "` (
        question_id mediumint(8) unsigned NOT NULL default '0',
        exercice_id mediumint(8) unsigned NOT NULL default '0',
    PRIMARY KEY  (question_id,exercice_id)
    )";

claro_sql_query($sql);
claro_mkdir($coursesRepositorySys . $courseRepository . '/exercise', CLARO_FILE_PERMISSIONS);
}

/**
 * @param cours_code $course_id id of course where do the work
 * @return true
 */
function CLQWZ_enable_tool($context,$course_id)
{
        // exercises
    global $langRidiculise, $langNoPsychology, $langAdmitError, $langNoSeduction, $langForce,
           $langIndeed, $langContradiction, $langNotFalse, $langExerciceEx,
           $langAntique, $langSocraticIrony, $langManyAnswers;

    $tbl_cdb_names = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
    $TABLEQUIZ                = $tbl_cdb_names['quiz_test'];//  $courseDbName."quiz_test";
    $TABLEQUIZQUESTION        = $tbl_cdb_names['quiz_rel_test_question'];
    $TABLEQUIZQUESTIONLIST    = $tbl_cdb_names['quiz_question'];//  "quiz_question";
    $TABLEQUIZANSWERSLIST     = $tbl_cdb_names['quiz_answer'];//  "quiz_answer";

    claro_sql_query("INSERT INTO `" . $TABLEQUIZANSWERSLIST . "` VALUES ( '1', '1', '".addslashes($langRidiculise)."', '0', '".addslashes($langNoPsychology)."', '-5', '1')");
    claro_sql_query("INSERT INTO `" . $TABLEQUIZANSWERSLIST . "` VALUES ( '2', '1', '".addslashes($langAdmitError)."', '0', '".addslashes($langNoSeduction)."', '-5', '2')");
    claro_sql_query("INSERT INTO `" . $TABLEQUIZANSWERSLIST . "` VALUES ( '3', '1', '".addslashes($langForce)."', '1', '".addslashes($langIndeed)."', '5', '3')");
    claro_sql_query("INSERT INTO `" . $TABLEQUIZANSWERSLIST . "` VALUES ( '4', '1', '".addslashes($langContradiction)."', '1', '".addslashes($langNotFalse)."', '5', '4')");
    claro_sql_query("INSERT INTO `" . $TABLEQUIZ . "` VALUES ( '1', '".addslashes($langExerciceEx)."', '".addslashes($langAntique)."', '1', '0', '0', '0', '0' , 'ALWAYS', 'NO', NOW(), DATE_ADD(NOW(), INTERVAL 1 YEAR) )");
    claro_sql_query("INSERT INTO `" . $TABLEQUIZQUESTIONLIST . "` VALUES ( '1', '".addslashes($langSocraticIrony)."', '".addslashes($langManyAnswers)."', '10', '1', '2','')");
    claro_sql_query("INSERT INTO `" . $TABLEQUIZQUESTION . "` VALUES ( '1', '1')");


        return true;
}

/**
 * @param cours_code $course_id id of course where do the work
 * @return true
 */
function CLQWZ_disable_tool($context,$course_id)
{
        return true;
}

/**
 * @param cours_code $course_id id of course where do the work
 * @return true
 */
function CLQWZ_export_tool($context,$course_id)
{
        return true;
}
?>