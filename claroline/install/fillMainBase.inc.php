<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );
/**
 * CLAROLINE
 *
 * These code is run to fill central tables during install
 *
 * @version 1.8 $Revision$
 *
 * @copyright 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @see http://www.claroline.net/wiki/index.php/Install
 *
 * @author Claro Team <cvs@claroline.net>
 *
 * @package INSTALL
 *
 */


$fillStatementList[] = "
INSERT INTO `" . $mainTblPrefixForm . "faculte`
(`code`, `code_P`, `treePos`, `nb_childs`, `name`)
VALUES
( 'SC',     NULL,  1, 0, 'Sciences'),
( 'ECO',    NULL,  2, 0, 'Economics'),
( 'HUMA',   NULL,  3, 0, 'Humanities')
";

/*

$fillStatementList[] = " INSERT INTO `".$mainTblPrefixForm."course_tool`
(`id`,`claro_label`,`script_url`,`icon`,`def_access`,`def_rank`,`add_in_course`,`access_manager`)
VALUES
(1, 'CLDSC', 'course_description/index.php', 'info.gif', 'ALL', 1, 'AUTOMATIC', 'COURSE_ADMIN'),
(5, 'CLQWZ', 'exercise/exercise.php', 'quiz.gif', 'ALL', 5, 'AUTOMATIC', 'COURSE_ADMIN'),
(6, 'CLLNP', 'learnPath/learningPathList.php', 'learnpath.gif', 'ALL', 6, 'AUTOMATIC', 'COURSE_ADMIN')";


// ANNOUNCEMENT
$fillStatementList[] = " INSERT INTO `".$mainTblPrefixForm."course_tool`
(`claro_label`,`script_url`,`icon`,`def_access`,`def_rank`,`add_in_course`,`access_manager`)
VALUES
('CLANN', 'announcements/announcements.php', 'announcement.gif', 'ALL', 3, 'AUTOMATIC', 'COURSE_ADMIN')";



// AGENDA
$fillStatementList[] = " INSERT INTO `".$mainTblPrefixForm."course_tool`
(`claro_label`,`script_url`,`icon`,`def_access`,`def_rank`,`add_in_course`,`access_manager`)
VALUES
('CLCAL', 'calendar/agenda.php', 'agenda.gif', 'ALL', 2, 'AUTOMATIC', 'COURSE_ADMIN')";


// CHAT
$fillStatementList[] = " INSERT INTO `".$mainTblPrefixForm."course_tool`
(`claro_label`,`script_url`,`icon`,`def_access`,`def_rank`,`add_in_course`,`access_manager`)
VALUES
('CLCHT', 'chat/chat.php', 'chat.gif', 'ALL', 11, 'AUTOMATIC', 'COURSE_ADMIN')";



// DOCUMENTS
$fillStatementList[] = " INSERT INTO `".$mainTblPrefixForm."course_tool`
(`claro_label`,`script_url`,`icon`,`def_access`,`def_rank`,`add_in_course`,`access_manager`)
VALUES
('CLDOC', 'document/document.php', 'document.gif', 'ALL', 4, 'AUTOMATIC', 'COURSE_ADMIN')";



// FORUM
$fillStatementList[] = " INSERT INTO `".$mainTblPrefixForm."course_tool`
(`claro_label`,`script_url`,`icon`,`def_access`,`def_rank`,`add_in_course`,`access_manager`)
VALUES
('CLFRM', 'phpbb/index.php', 'forum.gif', 'ALL', 8, 'AUTOMATIC', 'COURSE_ADMIN')
";



// GROUPS
$fillStatementList[] = " INSERT INTO `".$mainTblPrefixForm."course_tool`
(`claro_label`,`script_url`,`icon`,`def_access`,`def_rank`,`add_in_course`,`access_manager`)
VALUES
('CLGRP', 'group/group.php', 'group.gif', 'ALL', 9, 'AUTOMATIC', 'COURSE_ADMIN')
";




// USERS LIST
$fillStatementList[] = " INSERT INTO `".$mainTblPrefixForm."course_tool`
(`claro_label`,`script_url`,`icon`,`def_access`,`def_rank`,`add_in_course`,`access_manager`)
VALUES
('CLUSR', 'user/user.php', 'user.gif', 'ALL', 10, 'AUTOMATIC', 'COURSE_ADMIN')
";

//ASSIGNMENT
$fillStatementList[] = " INSERT INTO `".$mainTblPrefixForm."course_tool`
(`claro_label`,`script_url`,`icon`,`def_access`,`def_rank`,`add_in_course`,`access_manager`)
VALUES
('CLWRK', 'work/work.php', 'assignment.gif', 'ALL', 7, 'AUTOMATIC', 'COURSE_ADMIN')
";


// WIKI
$fillStatementList[] = " INSERT INTO `".$mainTblPrefixForm."course_tool`
(`claro_label`,`script_url`,`icon`,`def_access`,`def_rank`,`add_in_course`,`access_manager`)
VALUES
('CLWIKI', 'wiki/wiki.php', 'wiki.gif', 'ALL', 12, 'AUTOMATIC', 'COURSE_ADMIN')
";

*/

?>