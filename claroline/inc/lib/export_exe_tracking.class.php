<?php // $Id$
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

include_once(get_conf('rootSys').$clarolineRepositoryAppend.'exercice/question.class.php');
include_once(get_conf('rootSys').$clarolineRepositoryAppend.'exercice/answer.class.php');
include_once( dirname(__FILE__) . '/csv.class.php');

// answer types
if(!defined('UNIQUE_ANSWER'))     define('UNIQUE_ANSWER',   1);
if(!defined('MULTIPLE_ANSWER')) define('MULTIPLE_ANSWER', 2);
if(!defined('FILL_IN_BLANKS'))     define('FILL_IN_BLANKS',  3);
if(!defined('MATCHING'))         define('MATCHING',        4);
if(!defined('TRUEFALSE'))         define('TRUEFALSE',           5);

class csvTrackSingle extends csv
{
    var $question;
    var $exerciseId;

    function csvTrackSingle($objQuestion, $exerciseId = '')
    {
           parent::csv(); // call constructor of parent class
        $this->question = $objQuestion;
        $this->exerciseId = $exerciseId;
      }
    // build : date;username;statement;answer
       function buildRecords()
       {
        $tbl_mdb_names = claro_sql_get_main_tbl();
        $tbl_user = $tbl_mdb_names['user'                    ];
        
          $tbl_cdb_names = claro_sql_get_course_tbl();
          $tbl_quiz_answer            = $tbl_cdb_names['quiz_answer'            ];
        $tbl_quiz_question            = $tbl_cdb_names['quiz_question'        ];
        $tbl_quiz_rel_test_question    = $tbl_cdb_names['quiz_rel_test_question'];
          $tbl_track_e_exercises        = $tbl_cdb_names['track_e_exercices'    ];
        $tbl_track_e_exe_details    = $tbl_cdb_names['track_e_exe_details'    ];
        $tbl_track_e_exe_answers    = $tbl_cdb_names['track_e_exe_answers'    ];
        
          // this query doesn't show attempts without any answer
        $sql = "SELECT `TE`.`exe_date`,
                        CONCAT(`U`.`prenom`,' ',`U`.`nom`) AS `name`,
                        `Q`.`question`,
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
                WHERE `RTQ`.`question_id` = `Q`.`id`
                    AND `RTQ`.`exercice_id` = `TE`.`exe_exo_id`
                    AND `TE`.`exe_id` = `TED`.`exercise_track_id`
                    AND `U`.`user_id` = `TE`.`exe_user_id`
                    AND `TED`.`question_id` = `Q`.`id`
                    AND `Q`.`id` = ".$this->question->selectId();

        if( !empty($this->exerciseId) ) $sql .= " AND `RTQ`.`exercice_id` = ".$this->exerciseId;
        
        $sql .= " ORDER BY `TE`.`exe_date` ASC, `name` ASC";

        $attempts = claro_sql_query_fetch_all($sql);

        // get the list of possible answers and their ids
        $sql = "SELECT `A`.`id`, `A`.`reponse`
                FROM `".$tbl_quiz_answer."` AS `A`
                WHERE `A`.`question_id` = ".$this->question->selectId();
        $answers = claro_sql_query_fetch_all($sql);

        // order the answer list to have the id as the key
        foreach( $answers as $answer )    $orderedAnswers[$answer['id']] = $answer['reponse'];

        // build recordlist with good values for answers
        $i = 0;
        foreach( $attempts as $attempt )
        {
            $this->recordList[$i] = $attempt;
            
            if( isset($orderedAnswers[$attempt['answer']]) )
                $this->recordList[$i]['answer'] = $orderedAnswers[$attempt['answer']];
            else
                $this->recordList[$i]['answer'] = '';
                
            $i++;
        }

        
          if( isset($this->recordList) && is_array($this->recordList) )
            return true;
        else
            return false;
    }
}

class csvTrackMulti extends csv
{
    var $question;
    var $exerciseId;

    function csvTrackMulti($objQuestion, $exerciseId)
    {
        parent::csv(); // call constructor of parent class
        $this->question = $objQuestion;
        $this->exerciseId = $exerciseId;
    }

    // build : date,username,statement,answer
       function buildRecords()
       {
        $tbl_mdb_names = claro_sql_get_main_tbl();
        $tbl_user                     = $tbl_mdb_names['user'                    ];

          $tbl_cdb_names = claro_sql_get_course_tbl();
          $tbl_quiz_answer            = $tbl_cdb_names['quiz_answer'            ];
        $tbl_quiz_question            = $tbl_cdb_names['quiz_question'        ];
        $tbl_quiz_rel_test_question    = $tbl_cdb_names['quiz_rel_test_question'];
          $tbl_track_e_exercises        = $tbl_cdb_names['track_e_exercices'    ];
        $tbl_track_e_exe_details    = $tbl_cdb_names['track_e_exe_details'    ];
        $tbl_track_e_exe_answers    = $tbl_cdb_names['track_e_exe_answers'    ];
        
        // this query doesn't show attempts without any answer
        $sql = "SELECT  `TE`.`exe_id`,
                        `TE`.`exe_date` AS `date`,
                        CONCAT(`U`.`prenom`,' ',`U`.`nom`) AS `name`,
                        `Q`.`question`,
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
                WHERE `RTQ`.`question_id` = `Q`.`id`
                    AND `RTQ`.`exercice_id` = `TE`.`exe_exo_id`
                    AND `TE`.`exe_id` = `TED`.`exercise_track_id`
                    AND `U`.`user_id` = `TE`.`exe_user_id`
                    AND `TED`.`question_id` = `Q`.`id`
                    AND `Q`.`id` = ".$this->question->selectId();

        if( !empty($this->exerciseId) ) $sql .= " AND `RTQ`.`exercice_id` = ".$this->exerciseId;

        $sql .= " ORDER BY `TE`.`exe_date` ASC, `name` ASC";

        // we need to compile all answers of one attempt on the same line
        $tmpRecordList = claro_sql_query_fetch_all($sql);
        
        // get the list of possible answers and their ids
        $sql = "SELECT `A`.`id`, `A`.`reponse`
                FROM `".$tbl_quiz_answer."` AS `A`
                WHERE `A`.`question_id` = ".$this->question->selectId();
        $answers = claro_sql_query_fetch_all($sql);

        // order the answer list to have the id as the key
        foreach( $answers as $answer )    $orderedAnswers[$answer['id']] = $answer['reponse'];
        
        $previousKey = '';
        foreach( $tmpRecordList as $tmpRecord )
        {
            // build a unique key for each line of the csv
            // different answer of a same attempt have same date and name
            $key = $tmpRecord['exe_id'];
            if( $key != $previousKey )
            {
                // add infos in record list
                $recordList[$key]['date'] = $tmpRecord['date'];
                $recordList[$key]['name'] = $tmpRecord['name'];
                $recordList[$key]['question'] = $tmpRecord['question'];
            }
            // add answer one by one
               if( isset($orderedAnswers[$tmpRecord['answer']]) )
                $recordList[$key][] = $orderedAnswers[$tmpRecord['answer']];
            else
                $recordList[$key][] = '';
                
            $previousKey = $key;
        }

          if( isset($recordList) && is_array($recordList) )
          {
              $this->recordList = $recordList;
            return true;
        }
        else
        {
            return false;
        }
    }
}

class csvTrackFIB extends csv
{
    var $question;
    var $exerciseId;

    function csvTrackFIB($objQuestion, $exerciseId)
    {
        parent::csv(); // call constructor of parent class
        $this->question = $objQuestion;
        $this->exerciseId = $exerciseId;
      }

       // create record list
       function buildRecords()
       {
        $tbl_mdb_names = claro_sql_get_main_tbl();
        $tbl_user                     = $tbl_mdb_names['user'                    ];

          $tbl_cdb_names = claro_sql_get_course_tbl();
          $tbl_quiz_answer            = $tbl_cdb_names['quiz_answer'            ];
        $tbl_quiz_question            = $tbl_cdb_names['quiz_question'        ];
        $tbl_quiz_rel_test_question    = $tbl_cdb_names['quiz_rel_test_question'];
          $tbl_track_e_exercises        = $tbl_cdb_names['track_e_exercices'    ];
        $tbl_track_e_exe_details    = $tbl_cdb_names['track_e_exe_details'    ];
        $tbl_track_e_exe_answers    = $tbl_cdb_names['track_e_exe_answers'    ];

        $sql = "SELECT  `TE`.`exe_id`,
                        `TE`.`exe_date` as `date`,
                        CONCAT(`U`.`prenom`,' ',`U`.`nom`) AS `name`,
                        `Q`.`question`,
                        `TEA`.`answer`
                FROM (
                    `".$tbl_quiz_question."` AS `Q`,
                    `".$tbl_quiz_rel_test_question."` AS `RTQ`,
                    `".$tbl_quiz_answer."` AS `A`,
                    `".$tbl_track_e_exercises."` AS `TE`,
                    `".$tbl_track_e_exe_details."` AS `TED`,
                    `".$tbl_user."` AS `U`
                    )
                LEFT JOIN `".$tbl_track_e_exe_answers."` AS `TEA`
                    ON `TEA`.`details_id` = `TED`.`id`
                WHERE `RTQ`.`question_id` = `Q`.`id`
                    AND `Q`.`id` = `A`.`question_id`
                    AND `RTQ`.`exercice_id` = `TE`.`exe_exo_id`
                    AND `TE`.`exe_id` = `TED`.`exercise_track_id`
                    AND `U`.`user_id` = `TE`.`exe_user_id`
                    AND `TED`.`question_id` = `Q`.`id`
                    AND `Q`.`id` = ".$this->question->selectId();

        if( !empty($this->exerciseId) ) $sql .= " AND `RTQ`.`exercice_id` = ".$this->exerciseId;

        $sql .= " ORDER BY `TE`.`exe_date` ASC, `name` ASC";

        // we need to compile all answers of one attempt on the same line
        $tmpRecordList = claro_sql_query_fetch_all($sql);

        $previousKey = '';
        foreach( $tmpRecordList as $tmpRecord )
        {
            // build a unique key for each line of the csv
            // different answer of a same attempt have same date and name
            $key = $tmpRecord['exe_id'];
            if( $key != $previousKey )
            {
                // add infos in record list
                $recordList[$key]['date'] = $tmpRecord['date'];
                $recordList[$key]['name'] = $tmpRecord['name'];
                $recordList[$key]['question'] = $tmpRecord['question'];
            }
            
            // add answer one by one
            // for a better display replace entities by real chars
            $charsToReplace = array('&#58;&#58;','&#91;','&#93;','&lt;','&gt;');
            $replacingChars = array('::','[',']','<','>');
            $tmpRecord['answer'] = str_replace($charsToReplace,$replacingChars,trim($tmpRecord['answer']));
            
            $recordList[$key][] = $tmpRecord['answer'];

            $previousKey = $key;
        }

          if( isset($recordList) && is_array($recordList) )
          {
              $this->recordList = $recordList;
            return true;
        }
        else
        {
            return false;
        }
    }
}

class csvTrackMatching extends csv
{
    var $question;
    var $exerciseId;

    function csvTrackMatching($objQuestion, $exerciseId)
    {
        parent::csv(); // call constructor of parent class
        $this->question = $objQuestion;
        $this->exerciseId = $exerciseId;
    }

       // create record list
       function buildRecords()
       {
        $tbl_mdb_names = claro_sql_get_main_tbl();
        $tbl_user                     = $tbl_mdb_names['user'                    ];

          $tbl_cdb_names = claro_sql_get_course_tbl();
          $tbl_quiz_answer            = $tbl_cdb_names['quiz_answer'            ];
        $tbl_quiz_question            = $tbl_cdb_names['quiz_question'        ];
        $tbl_quiz_rel_test_question    = $tbl_cdb_names['quiz_rel_test_question'];
          $tbl_track_e_exercises        = $tbl_cdb_names['track_e_exercices'    ];
        $tbl_track_e_exe_details    = $tbl_cdb_names['track_e_exe_details'    ];
        $tbl_track_e_exe_answers    = $tbl_cdb_names['track_e_exe_answers'    ];

        $sql = "SELECT  `TE`.`exe_id`,
                        `TE`.`exe_date` as `date`,
                        CONCAT(`U`.`prenom`,' ',`U`.`nom`) AS `name`,
                        `Q`.`question`,
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
                WHERE `RTQ`.`question_id` = `Q`.`id`
                    AND `RTQ`.`exercice_id` = `TE`.`exe_exo_id`
                    AND `TE`.`exe_id` = `TED`.`exercise_track_id`
                    AND `U`.`user_id` = `TE`.`exe_user_id`
                    AND `TED`.`question_id` = `Q`.`id`
                    AND `Q`.`id` = ".$this->question->selectId();

        if( !empty($this->exerciseId) ) $sql .= " AND `RTQ`.`exercice_id` = ".$this->exerciseId;

        $sql .= " ORDER BY `TE`.`exe_date` ASC, `name` ASC";

        // we need to compile all answers of one attempt on the same line
        $tmpRecordList = claro_sql_query_fetch_all($sql);

        // get the list of right propositions (letters)
        // we need id and order to get the correct letters
        $objAnswer = new Answer($this->question->selectId());
        $nbrAnswers = $objAnswer->selectNbrAnswers();
        $letter = 'A';
        for($answerId = 1;$answerId <= $nbrAnswers;$answerId++)
        {
            $answer = $objAnswer->selectAnswer($answerId);
            $answerCorrect = $objAnswer->isCorrect($answerId);

            if(!$answerCorrect)
            {
                // so we can associate id of answer in tracking with letter in question display
                $rightAnswer[$answerId] = $letter;
                $letter++;
            }
        }
        
        $previousKey = '';
        foreach( $tmpRecordList as $tmpRecord )
        {
            // build a unique key for each line of the csv
            // different answer of a same attempt have same date and name
            $key = $tmpRecord['exe_id'];
            if( $key != $previousKey )
            {
                // add infos in record list
                $recordList[$key]['date'] = $tmpRecord['date'];
                $recordList[$key]['name'] = $tmpRecord['name'];
                $recordList[$key]['question'] = $tmpRecord['question'];
            }
            // add answer one by one
               $splittedAnswer = explode('-',$tmpRecord['answer']);

            if( isset($rightAnswer[$splittedAnswer[1]]) )
                $recordList[$key][] = $rightAnswer[$splittedAnswer[1]];
            else
                $recordList[$key][] = '';

            $previousKey = $key;
        }

        if( isset($recordList) && is_array($recordList) )
          {
              $this->recordList = $recordList;
            return true;
        }
        else
        {
            return false;
        }
        
    }
}

function export_question_tracking($questionId, $exerciseId = '')
{
    $objQuestion = new Question();
    if( !$objQuestion->read($questionId) )
    {
        return "";
    }
    
    switch($objQuestion->type)
    {
          case TRUEFALSE: // do the same as unique answer
        case UNIQUE_ANSWER:
            $cvsTrack = new csvTrackSingle($objQuestion, $exerciseId);
            break;
        case MULTIPLE_ANSWER:
            $cvsTrack = new csvTrackMulti($objQuestion, $exerciseId);
            break;
        case FILL_IN_BLANKS:
            $cvsTrack = new csvTrackFIB($objQuestion, $exerciseId);
            break;
        case MATCHING:
            $cvsTrack = new csvTrackMatching($objQuestion, $exerciseId);
            break;
        /*default:
            break;*/
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

function export_exercise_tracking($exerciseId)
{
    $objExercise = new Exercise();
    if( !$objExercise->read($exerciseId) )
    {
        return "";
    }

    $questionList = $objExercise->selectQuestionList();
    
    $exerciseCsv = '';
    foreach( $questionList as $questionId )
    {
        $exerciseCsv .= export_question_tracking($questionId, $exerciseId);
    }

    return $exerciseCsv;
}
?>