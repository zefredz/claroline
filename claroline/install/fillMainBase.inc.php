<?php // $Id$
/**
 * --------------------------------------------------------------------------
 * @version CLAROLINE 1.6
 * --------------------------------------------------------------------------
 * @copyright (c) 2001-2005 Universite catholique de Louvain (UCL)
 * --------------------------------------------------------------------------
 * @license GPL
 * This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
 * as published by the FREE SOFTWARE FOUNDATION. The GPL is available
 * through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
 * --------------------------------------------------------------------------
 * @author claro team <info@claroline.net>
 * --------------------------------------------------------------------------
 */


$sql_insert_sample_cats = "
INSERT INTO `".$mainTblPrefixForm."faculte`
(`code`, `code_P`, `bc`, `treePos`, `nb_childs`, `canHaveCoursesChild`, `canHaveCatChild`, `name`)
VALUES
( 'SC',    NULL, NULL, 1, 0, 'TRUE', 'TRUE', 'Sciences'),
( 'ECO',    NULL, NULL, 2, 0, 'TRUE', 'TRUE', 'Economics'),
( 'HUMA',    NULL, NULL, 3, 0, 'TRUE', 'TRUE', 'Humanities'),
( 'PSYCHO', NULL, NULL, 4, 0, 'TRUE', 'TRUE', 'Psychology'),
( 'MD',     NULL, NULL, 5, 0, 'TRUE', 'TRUE', 'Medicine')
";

claro_sql_query($sql_insert_sample_cats);

    # add admin as user with statut prof (1)
    if ($encryptPassForm)
        $passToStore=md5($passForm);
    else
        $passToStore=($passForm);


    $sql = 'select username, nom lastname, prenom firstname
            from `'.$mainTblPrefixForm.'user`
            where username = "'.cleanwritevalue($loginForm).'"';
    $res = @claro_sql_query($sql);
    if(mysql_errno()>0)
    {
    // No problem
    }
    else
    $controlUser = mysql_num_rows($res);

    $sql = "
INSERT INTO `".$mainTblPrefixForm."user` (`nom`, `prenom`, `username`, `password`, `email`, `statut`, `phoneNumber` )
VALUES
(  \"".cleanwritevalue($adminNameForm)."\", \"".cleanwritevalue($adminSurnameForm)."\", \"".cleanwritevalue($loginForm)."\",\"".cleanwritevalue($passToStore)."\",\"".$adminEmailForm."\",'1',\"".cleanwritevalue($adminPhoneForm)."\" )
";
    if ($controlUser>0)
    {
        $sql = "
        UPDATE `".$mainTblPrefixForm."user` SET (`nom`, `prenom`, `username`, `password`, `email`, `statut`, `phoneNumber` )
            VALUES
        (  \"".cleanwritevalue($adminNameForm)."\", \"".cleanwritevalue($adminSurnameForm)."\", \""
        .cleanwritevalue($loginForm)."\",\""
        .cleanwritevalue($passToStore)."\",\""
        .$adminEmailForm."\",'1',\""
        .cleanwritevalue($adminPhoneForm)."\" )
                ";
    }
    else
    {
        claro_sql_query($sql);
        ## get id of admin  to  write  it in admin table.
        $idOfAdmin=mysql_insert_id();

        #add admin in list of admin
        $sql = "INSERT INTO `".$mainTblPrefixForm."admin` VALUES ('".$idOfAdmin."')";
        claro_sql_query($sql);
    }


//TOOLS

$sql = " INSERT INTO `".$mainTblPrefixForm."course_tool`
(`id`,`claro_label`,`script_url`,`icon`,`def_access`,`def_rank`,`add_in_course`,`access_manager`)
VALUES
(1, 'CLDSC___', 'course_description/index.php', 'info.gif', 'ALL', 1, 'AUTOMATIC', 'COURSE_ADMIN'),
(5, 'CLQWZ___', 'exercice/exercice.php', 'quiz.gif', 'ALL', 5, 'AUTOMATIC', 'COURSE_ADMIN'),
(6, 'CLLNP___', 'learnPath/learningPathList.php', 'step.gif', 'ALL', 6, 'AUTOMATIC', 'COURSE_ADMIN')";
claro_sql_query($sql);

// ANNOUNCEMENT
$sql = " INSERT INTO `".$mainTblPrefixForm."course_tool`
(`claro_label`,`script_url`,`icon`,`def_access`,`def_rank`,`add_in_course`,`access_manager`)
VALUES
('CLANN___', 'announcements/announcements.php', 'valves.gif', 'ALL', 3, 'AUTOMATIC', 'COURSE_ADMIN')";
claro_sql_query($sql);


// AGENDA
$sql = " INSERT INTO `".$mainTblPrefixForm."course_tool`
(`claro_label`,`script_url`,`icon`,`def_access`,`def_rank`,`add_in_course`,`access_manager`)
VALUES
('CLCAL___', 'calendar/agenda.php', 'agenda.gif', 'ALL', 2, 'AUTOMATIC', 'COURSE_ADMIN')";
claro_sql_query($sql);

// CHAT
$sql = " INSERT INTO `".$mainTblPrefixForm."course_tool`
(`claro_label`,`script_url`,`icon`,`def_access`,`def_rank`,`add_in_course`,`access_manager`)
VALUES
('CLCHT___', 'chat/chat.php', 'forum.gif', 'ALL', 11, 'AUTOMATIC', 'COURSE_ADMIN')";
claro_sql_query($sql);


// DOCUMENTS
$sql = " INSERT INTO `".$mainTblPrefixForm."course_tool`
(`claro_label`,`script_url`,`icon`,`def_access`,`def_rank`,`add_in_course`,`access_manager`)
VALUES
('CLDOC___', 'document/document.php', 'documents.gif', 'ALL', 4, 'AUTOMATIC', 'COURSE_ADMIN')";
claro_sql_query($sql);


// FORUM
$sql = " INSERT INTO `".$mainTblPrefixForm."course_tool`
(`claro_label`,`script_url`,`icon`,`def_access`,`def_rank`,`add_in_course`,`access_manager`)
VALUES
('CLFRM___', 'phpbb/index.php', 'forum.gif', 'ALL', 8, 'AUTOMATIC', 'COURSE_ADMIN')
";
claro_sql_query($sql);


// GROUPS
$sql = " INSERT INTO `".$mainTblPrefixForm."course_tool`
(`claro_label`,`script_url`,`icon`,`def_access`,`def_rank`,`add_in_course`,`access_manager`)
VALUES
('CLGRP___', 'group/group.php', 'group.gif', 'ALL', 9, 'AUTOMATIC', 'COURSE_ADMIN')
";
claro_sql_query($sql);



// USERS LIST
$sql = " INSERT INTO `".$mainTblPrefixForm."course_tool`
(`claro_label`,`script_url`,`icon`,`def_access`,`def_rank`,`add_in_course`,`access_manager`)
VALUES
('CLUSR___', 'user/user.php', 'membres.gif', 'ALL', 10, 'AUTOMATIC', 'COURSE_ADMIN')
";
claro_sql_query($sql);

$sql = " INSERT INTO `".$mainTblPrefixForm."course_tool`
(`claro_label`,`script_url`,`icon`,`def_access`,`def_rank`,`add_in_course`,`access_manager`)
VALUES
('CLWRK___', 'work/work.php', 'works.gif', 'ALL', 7, 'AUTOMATIC', 'COURSE_ADMIN')
";
claro_sql_query($sql);

?>