<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );
/*
+----------------------------------------------------------------------+
| CLAROLINE 1.6                                                        |
+----------------------------------------------------------------------+
| Copyright (c) 2001-2006 Universite catholique de Louvain (UCL)      |
+----------------------------------------------------------------------+
|   This program is free software; you can redistribute it and/or
|   modify it under the terms of the GNU General Public License
|   as published by the Free Software Foundation; either version 2
|   of the License, or (at your option) any later version.
+----------------------------------------------------------------------+
| Authors: Sébastien Piraux
+----------------------------------------------------------------------+
*/

include_once( dirname(__FILE__) . '/../../exercise/lib/question.class.php');
include_once( dirname(__FILE__) . '/csv.class.php');

class csvTrackTrueFalse extends csv
{
    var $question;
    var $exId;
    
    function csvTrackTrueFalse($question, $exId = '')
    {
        parent::csv(); // call constructor of parent class
        
        $this->question = $question;
        $this->exId = $exId;
    }
    
    function buildRecords()
    {
        $tbl_mdb_names = claro_sql_get_main_tbl();
        $tbl_user = $tbl_mdb_names['user'];
        
        
        $tbl_cdb_names = claro_sql_get_course_tbl();
        $tbl_quiz_rel_test_question = $tbl_cdb_names['qwz_rel_exercise_question'];
        $tbl_quiz_question = $this->question->tblQuestion;
        
        $tbl_track_e_exercises        = $tbl_cdb_names['track_e_exercices'    ];
        $tbl_track_e_exe_details    = $tbl_cdb_names['track_e_exe_details'    ];
        $tbl_track_e_exe_answers    = $tbl_cdb_names['track_e_exe_answers'    ];

        
        
        // this query doesn't show attempts without any answer
        $sql = "SELECT `TE`.`exe_date`,
                        CONCAT(`U`.`prenom`,' ',`U`.`nom`) AS `name`,
                        `Q`.`title`,
                        `TEA`.`answer`
                FROM (
                    `".$tbl_quiz_question."` AS `Q`,
                    `".$tbl_quiz_rel_test_question."` AS `RTQ`,
                    `".$tbl_track_e_exercises."` AS `TE`,
                    `".$tbl_track_e_exe_details."` AS `TED`,
                    `".$tbl_user."` AS `U`
                    )
                LEFT JOIN `".$tbl_track_e_exe_answers."` AS `TEA`
                    ON `TEA`.`details_id` = `TED`.`id`
                WHERE `RTQ`.`questionId` = `Q`.`id`
                    AND `RTQ`.`exerciseId` = `TE`.`exe_exo_id`
                    AND `TE`.`exe_id` = `TED`.`exercise_track_id`
                    AND `U`.`user_id` = `TE`.`exe_user_id`
                    AND `TED`.`question_id` = `Q`.`id`
                    AND `Q`.`id` = ".$this->question->getId();

        if( !empty($this->exerciseId) ) $sql .= " AND `RTQ`.`exercice_id` = ".$this->exerciseId;

        $sql .= " ORDER BY `TE`.`exe_date` ASC, `name` ASC";

        $attempts = claro_sql_query_fetch_all($sql);

        // build recordlist with good values for answers
        if( is_array($attempts) )
        {
            $i = 0;
            foreach( $attempts as $attempt )
            {
                $this->recordList[$i] = $attempt;

                if( $attempt['answer'] == 'TRUE' )
                    $this->recordList[$i]['answer'] = get_lang('True');
                elseif( $attempt['answer'] == 'FALSE' )
                    $this->recordList[$i]['answer'] = get_lang('False');
                else
                    $this->recordList[$i]['answer'] = '';

                $i++;
            }

            if( isset($this->recordList) && is_array($this->recordList) ) return true;
        }
        
        return false;
    }
}



class csvTrackMultipleChoice extends csv
{
    var $question;
    var $exId;

    function csvTrackMultipleChoice($question, $exId = '')
    {
        parent::csv(); // call constructor of parent class
        
        $this->question = $question;
        $this->exId = $exId;
    }
    
    // build : date;username;statement;answer
    function buildRecords()
    {
        $tbl_mdb_names = claro_sql_get_main_tbl();
        $tbl_user = $tbl_mdb_names['user'];

        $tbl_cdb_names = claro_sql_get_course_tbl();
        $tbl_quiz_rel_test_question    = $tbl_cdb_names['qwz_rel_exercise_question'];
        $tbl_quiz_question = $this->question->tblQuestion;
        
        $tbl_track_e_exercises        = $tbl_cdb_names['track_e_exercices'    ];
        $tbl_track_e_exe_details    = $tbl_cdb_names['track_e_exe_details'    ];
        $tbl_track_e_exe_answers    = $tbl_cdb_names['track_e_exe_answers'    ];

        // this query doesn't show attempts without any answer
        $sql = "SELECT `TE`.`exe_date`,
                        CONCAT(`U`.`prenom`,' ',`U`.`nom`) AS `name`,
                        `Q`.`title`,
                        `TEA`.`answer`
                FROM (
                    `".$tbl_quiz_question."` AS `Q`,
                    `".$tbl_quiz_rel_test_question."` AS `RTQ`,
                    `".$tbl_track_e_exercises."` AS `TE`,
                    `".$tbl_track_e_exe_details."` AS `TED`,
                    `".$tbl_user."` AS `U`
                    )
                LEFT JOIN `".$tbl_track_e_exe_answers."` AS `TEA`
                    ON `TEA`.`details_id` = `TED`.`id`
                WHERE `RTQ`.`questionId` = `Q`.`id`
                    AND `RTQ`.`exerciseId` = `TE`.`exe_exo_id`
                    AND `TE`.`exe_id` = `TED`.`exercise_track_id`
                    AND `U`.`user_id` = `TE`.`exe_user_id`
                    AND `TED`.`question_id` = `Q`.`id`
                    AND `Q`.`id` = ".$this->question->getId();

        if( !empty($this->exerciseId) ) $sql .= " AND `RTQ`.`exercice_id` = ".$this->exerciseId;

        $sql .= " ORDER BY `TE`.`exe_date` ASC, `name` ASC";

        $attempts = claro_sql_query_fetch_all($sql);

        if( is_array($attempts) )
        {
            // build recordlist with good values for answers
            $i = 0;
            foreach( $attempts as $attempt )
            {
                $this->recordList[$i] = $attempt;
                $i++;
            }

            if( isset($this->recordList) && is_array($this->recordList) )
                return true;
            else
                return false;
        }
        
        return false;
    }
}


class csvTrackFIB extends csv
{
    var $question;
    var $exerciseId;

    function csvTrackFIB($question, $exId = '')
    {
        parent::csv(); // call constructor of parent class
        
        $this->question = $question;
        $this->exId = $exId;
    }
    
    // build : date;username;statement;answer
    function buildRecords()
    {
        $tbl_mdb_names = claro_sql_get_main_tbl();
        $tbl_user = $tbl_mdb_names['user'];

        $tbl_cdb_names = claro_sql_get_course_tbl();
        $tbl_quiz_rel_test_question    = $tbl_cdb_names['qwz_rel_exercise_question'];
        $tbl_quiz_question = $this->question->tblQuestion;
        
        $tbl_track_e_exercises        = $tbl_cdb_names['track_e_exercices'    ];
        $tbl_track_e_exe_details    = $tbl_cdb_names['track_e_exe_details'    ];
        $tbl_track_e_exe_answers    = $tbl_cdb_names['track_e_exe_answers'    ];

        // this query doesn't show attempts without any answer
        $sql = "SELECT `TE`.`exe_date`,
                        CONCAT(`U`.`prenom`,' ',`U`.`nom`) AS `name`,
                        `Q`.`title`,
                        `TEA`.`answer`
                FROM (
                    `".$tbl_quiz_question."` AS `Q`,
                    `".$tbl_quiz_rel_test_question."` AS `RTQ`,
                    `".$tbl_track_e_exercises."` AS `TE`,
                    `".$tbl_track_e_exe_details."` AS `TED`,
                    `".$tbl_user."` AS `U`
                    )
                LEFT JOIN `".$tbl_track_e_exe_answers."` AS `TEA`
                    ON `TEA`.`details_id` = `TED`.`id`
                WHERE `RTQ`.`questionId` = `Q`.`id`
                    AND `RTQ`.`exerciseId` = `TE`.`exe_exo_id`
                    AND `TE`.`exe_id` = `TED`.`exercise_track_id`
                    AND `U`.`user_id` = `TE`.`exe_user_id`
                    AND `TED`.`question_id` = `Q`.`id`
                    AND `Q`.`id` = ".$this->question->getId();

        if( !empty($this->exerciseId) ) $sql .= " AND `RTQ`.`exercice_id` = ".$this->exerciseId;

        $sql .= " ORDER BY `TE`.`exe_date` ASC, `name` ASC";

        $attempts = claro_sql_query_fetch_all($sql);

        if( is_array($attempts) )
        {
            // build recordlist with good values for answers
            $i = 0;
            foreach( $attempts as $attempt )
            {
                $this->recordList[$i] = $attempt;
                $i++;
            }

            if( isset($this->recordList) && is_array($this->recordList) )
                return true;
            else
                return false;
        }
        
        return false;
    }
}

class csvTrackMatching extends csv
{
    var $question;
    var $exerciseId;

    function csvTrackMatching($question, $exId = '')
    {
        parent::csv(); // call constructor of parent class
        
        $this->question = $question;
        $this->exId = $exId;
    }
    
    // build : date;username;statement;answer
    function buildRecords()
    {
        $tbl_mdb_names = claro_sql_get_main_tbl();
        $tbl_user = $tbl_mdb_names['user'];

        $tbl_cdb_names = claro_sql_get_course_tbl();
        $tbl_quiz_rel_test_question    = $tbl_cdb_names['qwz_rel_exercise_question'];
        $tbl_quiz_question = $this->question->tblQuestion;
        
        $tbl_track_e_exercises        = $tbl_cdb_names['track_e_exercices'    ];
        $tbl_track_e_exe_details    = $tbl_cdb_names['track_e_exe_details'    ];
        $tbl_track_e_exe_answers    = $tbl_cdb_names['track_e_exe_answers'    ];

        // this query doesn't show attempts without any answer
        $sql = "SELECT `TE`.`exe_date`,
                        CONCAT(`U`.`prenom`,' ',`U`.`nom`) AS `name`,
                        `Q`.`title`,
                        `TEA`.`answer`
                FROM (
                    `".$tbl_quiz_question."` AS `Q`,
                    `".$tbl_quiz_rel_test_question."` AS `RTQ`,
                    `".$tbl_track_e_exercises."` AS `TE`,
                    `".$tbl_track_e_exe_details."` AS `TED`,
                    `".$tbl_user."` AS `U`
                    )
                LEFT JOIN `".$tbl_track_e_exe_answers."` AS `TEA`
                    ON `TEA`.`details_id` = `TED`.`id`
                WHERE `RTQ`.`questionId` = `Q`.`id`
                    AND `RTQ`.`exerciseId` = `TE`.`exe_exo_id`
                    AND `TE`.`exe_id` = `TED`.`exercise_track_id`
                    AND `U`.`user_id` = `TE`.`exe_user_id`
                    AND `TED`.`question_id` = `Q`.`id`
                    AND `Q`.`id` = ".$this->question->getId();

        if( !empty($this->exerciseId) ) $sql .= " AND `RTQ`.`exercice_id` = ".$this->exerciseId;

        $sql .= " ORDER BY `TE`.`exe_date` ASC, `name` ASC";

        $attempts = claro_sql_query_fetch_all($sql);

        if( is_array($attempts) )
        {
            // build recordlist with good values for answers
            $i = 0;
            foreach( $attempts as $attempt )
            {
                $this->recordList[$i] = $attempt;
                $i++;
            }

            if( isset($this->recordList) && is_array($this->recordList) )
                return true;
            else
                return false;
        }
        
        return false;
    }
}

/**
 * @return string csv data or empty string
 *
 */
function export_question_tracking($quId, $exId = '')
{
    $question = new Question();
    if( !$question->load($quId) )
    {
        return "";
    }

    switch($question->getType())
    {
        case 'TF': 
            $cvsTrack = new csvTrackTrueFalse($question, $exId);
            break;
        case 'MCUA':
        case 'MCMA':
            $cvsTrack = new csvTrackMultipleChoice($question, $exId);
            break;
        case 'FIB':
            $cvsTrack = new csvTrackFIB($question, $exId);
            break;
        case 'MATCHING':
            $cvsTrack = new csvTrackMatching($question, $exId);
            break;
        default:
            break;
    }

    if( isset($cvsTrack) )
    {
        $cvsTrack->buildRecords();
        return $cvsTrack->export();
    }
    else
    {
        return "";
    }
}

function export_exercise_tracking($exId)
{
    $exercise = new Exercise();
    if( !$exercise->load($exId) )
    {
        return "";
    }

    $questionList = $exercise->getQuestionList();
    
    $exerciseCsv = '';
    foreach( $questionList as $question )
    {
        $exerciseCsv .= export_question_tracking($question['id'], $exId);
    }

    return $exerciseCsv;
}
?>