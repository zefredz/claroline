<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );
/**
 * CLAROLINE
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
 * @return boolean result of operation
 */
function CLQWZ_install_tool($context,$course_id)
{
    global $coursesRepositorySys;
    $courseRepository = claro_get_course_path($course_id);

    $tbl_cdb_names = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
    // Exercise
    $TABLEQWZEXERCISE         = $tbl_cdb_names['qwz_exercise'];
    $TABLEQWZQUESTION	= $tbl_cdb_names['qwz_question'];
    $TABLEQWZRELEXERCISEQUESTION = $tbl_cdb_names['qwz_rel_exercise_question'];
    
    //  Exercise answers
    $TABLEQWZANSWERTRUEFALSE = $tbl_cdb_names['qwz_answer_truefalse'];
    $TABLEQWZANSWERMULTIPLECHOICE = $tbl_cdb_names['qwz_answer_multiple_choice'];
    $TABLEQWZANSWERFIB = $tbl_cdb_names['qwz_answer_fib'];
    $TABLEQWZANSWERMATCHING = $tbl_cdb_names['qwz_answer_matching'];

	$sqlList[] = " 
	CREATE TABLE `".$TABLEQWZEXERCISE."` (
		`id` int(11) NOT NULL auto_increment,
		`title` varchar(255) NOT NULL,
		`description` text NOT NULL,
		`visibility` enum('VISIBLE','INVISIBLE') NOT NULL default 'VISIBLE',
		`displayType` enum('SEQUENTIAL','ONEPAGE') NOT NULL default 'ONEPAGE',
		`shuffle` smallint(6) NOT NULL default '0',
		`showAnswers` enum('ALWAYS','NEVER','LASTTRY') NOT NULL default 'ALWAYS',
		`startDate` datetime NOT NULL,
		`endDate` datetime NOT NULL,
		`timeLimit` smallint(6) NOT NULL default '0',
		`attempts` tinyint(4) NOT NULL default '0',
		`anonymousAttempts` enum('ALLOWED','NOTALLOWED') NOT NULL default 'ALLOWED',
	PRIMARY KEY  (`id`)
	)";
			
	$sqlList[] = " 
	CREATE TABLE `".$TABLEQWZQUESTION."` (
		`id` int(11) NOT NULL auto_increment,
		`title` varchar(255) NOT NULL default '',
		`description` text NOT NULL,
		`attachment` varchar(255) NOT NULL default '',
		`type` enum('MCUA','MCMA','TF','FIB','MATCHING') NOT NULL default 'MCUA',
		`grade` float NOT NULL default '0',
	PRIMARY KEY  (`id`)
	)";
	
	$sqlList[] = " 
	CREATE TABLE `".$TABLEQWZRELEXERCISEQUESTION."` (
		`exerciseId` int(11) NOT NULL,
		`questionId` int(11) NOT NULL,
		`rank` int(11) NOT NULL default '0'
	)";
			
	$sqlList[] = " 
	CREATE TABLE `".$TABLEQWZANSWERTRUEFALSE."` (
		`id` int(11) NOT NULL auto_increment,
		`questionId` int(11) NOT NULL,
		`trueFeedback` text NOT NULL,
		`trueGrade` float NOT NULL,
		`falseFeedback` text NOT NULL,
		`falseGrade` float NOT NULL,
		`correctAnswer` enum('TRUE','FALSE') NOT NULL,
		PRIMARY KEY  (`id`)
	)";
	
	$sqlList[] = " 	
	CREATE TABLE `".$TABLEQWZANSWERMULTIPLECHOICE."` (
		`id` int(11) NOT NULL auto_increment,
		`questionId` int(11) NOT NULL,
		`answer` text NOT NULL,
		`correct` tinyint(4) NOT NULL,
		`grade` float NOT NULL,
		`comment` text NOT NULL,
		PRIMARY KEY  (`id`)
	)";
			
	$sqlList[] = " 
	CREATE TABLE `".$TABLEQWZANSWERFIB."` (
		`id` int(11) NOT NULL auto_increment,
		`questionId` int(11) NOT NULL,
		`answer` text NOT NULL,
		`gradeList` text NOT NULL,
		`wrongAnswerList` text NOT NULL,
		`type` tinyint(4) NOT NULL,
		PRIMARY KEY  (`id`)
	)";
	
	$sqlList[] = " 
	CREATE TABLE `".$TABLEQWZANSWERMATCHING."` (
		`id` int(11) NOT NULL auto_increment,
		`questionId` int(11) NOT NULL,
		`answer` text NOT NULL,
		`match` varchar(32) default NULL,
		`grade` float NOT NULL default '0',
		`code` varchar(32) default NULL,
		PRIMARY KEY  (`id`)
	)";
	
	foreach($sqlList as $thisSql)
    {
        if ( claro_sql_query($thisSql) == false) return false;
        else                                     continue;
    }
    
	claro_mkdir($coursesRepositorySys . $courseRepository . '/exercise', CLARO_FILE_PERMISSIONS);
	
	return true;
}

/**
 * @param cours_code $course_id id of course where do the work
 * @return boolean result of operation
 */
function CLQWZ_enable_tool($context,$course_id)
{


    $tbl_cdb_names = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
    
    // Exercise
    $TABLEQWZEXERCISE         = $tbl_cdb_names['qwz_exercise'];
    $TABLEQWZQUESTION	= $tbl_cdb_names['qwz_question'];
    $TABLEQWZRELEXERCISEQUESTION = $tbl_cdb_names['qwz_rel_exercise_question'];
    
    //  Exercise answers
    $TABLEQWZANSWERTRUEFALSE = $tbl_cdb_names['qwz_answer_truefalse'];
    $TABLEQWZANSWERMULTIPLECHOICE = $tbl_cdb_names['qwz_answer_multiple_choice'];
    $TABLEQWZANSWERFIB = $tbl_cdb_names['qwz_answer_fib'];
    $TABLEQWZANSWERMATCHING = $tbl_cdb_names['qwz_answer_matching'];


	// create question
	$sql = "INSERT INTO `".$TABLEQWZQUESTION."` (`title`, `description`, `attachment`, `type`, `grade`)
				VALUES
				('".addslashes(get_lang('sampleQuizQuestionTitle'))."', '".addslashes(get_lang('sampleQuizQuestionText'))."', '', 'MCMA', '10' )";
				
	$questionId = claro_sql_query_insert_id($sql); 				
	if( !$questionId ) return false;		
	
	// create answers
	$sql = "INSERT INTO `".$TABLEQWZANSWERMULTIPLECHOICE."`(`questionId`,`answer`,`correct`,`grade`,`comment`)
				VALUES 
				('".$questionId."','".addslashes(get_lang('sampleQuizAnswer1'))."','0','-5','".addslashes(get_lang('sampleQuizAnswer1Comment'))."'),
				('".$questionId."','".addslashes(get_lang('sampleQuizAnswer2'))."','0','-5','".addslashes(get_lang('sampleQuizAnswer2Comment'))."'),
				('".$questionId."','".addslashes(get_lang('sampleQuizAnswer3'))."','1','5','".addslashes(get_lang('sampleQuizAnswer3Comment'))."'),
				('".$questionId."','".addslashes(get_lang('sampleQuizAnswer4'))."','1','5','".addslashes(get_lang('sampleQuizAnswer4Comment'))."')";
	
	if( !claro_sql_query($sql) ) return false;				
				
	// create exercise
	$sql = "INSERT INTO `".$TABLEQWZEXERCISE."` (`title`, `description`, `visibility`, `startDate`, `endDate`)	
				VALUES
				('".addslashes(get_lang('sampleQuizTitle'))."', '".addslashes(get_lang('sampleQuizDescription'))."', 'INVISIBLE', NOW(), DATE_ADD(NOW(), INTERVAL 1 YEAR) )";
	
	$exerciseId = claro_sql_query_insert_id($sql);
	if( !$exerciseId ) return false;
				
	// put question in exercise
	$sql = "INSERT INTO `".$TABLEQWZRELEXERCISEQUESTION."` VALUES ($exerciseId, $questionId, 1)";
	
	if( !claro_sql_query($sql) ) return false;		

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