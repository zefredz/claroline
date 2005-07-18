<?php // $Id$
/*
+----------------------------------------------------------------------+
| CLAROLINE 1.6                                                        |
+----------------------------------------------------------------------+
| Copyright (c) 2001, 2004 Universite catholique de Louvain (UCL)      |
+----------------------------------------------------------------------+
|   This program is free software; you can redistribute it and/or
|   modify it under the terms of the GNU General Public License
|   as published by the Free Software Foundation; either version 2
|   of the License, or (at your option) any later version.
+----------------------------------------------------------------------+
| Authors: Sébastien Piraux
+----------------------------------------------------------------------+
*/

include_once($rootSys.$clarolineRepositoryAppend.'exercice/question.class.php');
include_once($rootSys.$clarolineRepositoryAppend.'exercice/answer.class.php');
include_once( dirname(__FILE__) . '/csv.class.php');

// answer types
if(!defined('UNIQUE_ANSWER')) 	define('UNIQUE_ANSWER',   1);
if(!defined('MULTIPLE_ANSWER')) define('MULTIPLE_ANSWER', 2);
if(!defined('FILL_IN_BLANKS')) 	define('FILL_IN_BLANKS',  3);
if(!defined('MATCHING')) 		define('MATCHING',        4);
if(!defined('TRUEFALSE')) 		define('TRUEFALSE',	 	  5);

class csvTrackSingle extends csv
{
    var $exercise;
	var $question;

	function csvTrackSingle($question)
	{
   		parent::csv(); // call constructor of parent class
		$this->question = $question;
  	}
	// build : date;username;statement;answer
   	function buildRecords()
   	{
		$tbl_mdb_names = claro_sql_get_main_tbl();
		$tbl_user = $tbl_mdb_names['user'					];
		
      	$tbl_cdb_names = claro_sql_get_course_tbl();
  		$tbl_quiz_answer			= $tbl_cdb_names['quiz_answer'			];
		$tbl_quiz_question			= $tbl_cdb_names['quiz_question'		];
		$tbl_quiz_rel_test_question	= $tbl_cdb_names['quiz_rel_test_question'];
  		$tbl_track_e_exercises		= $tbl_cdb_names['track_e_exercices'	];
		$tbl_track_e_exe_details	= $tbl_cdb_names['track_e_exe_details'	];
		$tbl_track_e_exe_answers	= $tbl_cdb_names['track_e_exe_answers'	];
		
		// this query doesn't show attempts without any answer
		$sql = "SELECT `TE`.`exe_date`, CONCAT(`U`.`prenom`,' ',`U`.`nom`) AS `name`, `Q`.`question`, `A`.`reponse`
				FROM `".$tbl_quiz_question."` AS `Q`,
					`".$tbl_quiz_rel_test_question."` AS `RTQ`,
					`".$tbl_quiz_answer."` AS `A`,
					`".$tbl_track_e_exercises."` AS `TE`,
					`".$tbl_track_e_exe_details."` AS `TED`,
					`".$tbl_user."` AS `U`
				LEFT JOIN `".$tbl_track_e_exe_answers."` AS `TEA`
				    ON `TEA`.`details_id` = `TED`.`id`
    			WHERE `RTQ`.`question_id` = `Q`.`id`
					AND `Q`.`id` = `A`.`question_id`
					AND `RTQ`.`exercice_id` = `TE`.`exe_exo_id`
					AND `TE`.`exe_id` = `TED`.`exercise_track_id`
					AND `U`.`user_id` = `TE`.`exe_user_id`
					AND `TEA`.`answer` = `A`.`id`
					AND `TED`.`question_id` = `Q`.`id`
					AND `Q`.`id` = ".$this->question->selectId()."
				ORDER BY `TE`.`exe_date` ASC, `name` ASC";
				
  		if( $this->recordList = claro_sql_query_fetch_all($sql) )
			return true;
		else
		    return false;
	}
}

class csvTrackMulti extends csv
{
    var $exercise;
	var $question;

	function csvTrackMulti($question)
	{
        parent::csv(); // call constructor of parent class
		$this->question = $question;
		$this->answer = new Answer($this->question->selectId());
	}

	// build : date,username,statement,answer
   	function buildRecords()
   	{
		$tbl_mdb_names = claro_sql_get_main_tbl();
		$tbl_user 					= $tbl_mdb_names['user'					];

      	$tbl_cdb_names = claro_sql_get_course_tbl();
  		$tbl_quiz_answer			= $tbl_cdb_names['quiz_answer'			];
		$tbl_quiz_question			= $tbl_cdb_names['quiz_question'		];
		$tbl_quiz_rel_test_question	= $tbl_cdb_names['quiz_rel_test_question'];
  		$tbl_track_e_exercises		= $tbl_cdb_names['track_e_exercices'	];
		$tbl_track_e_exe_details	= $tbl_cdb_names['track_e_exe_details'	];
		$tbl_track_e_exe_answers	= $tbl_cdb_names['track_e_exe_answers'	];
		
        // this query doesn't show attempts without any answer
		$sql = "SELECT `TE`.`exe_date` AS `date`,
						CONCAT(`U`.`prenom`,' ',`U`.`nom`) AS `name`,
						`Q`.`question`,
						`A`.`reponse`
				FROM `".$tbl_quiz_question."` AS `Q`,
					`".$tbl_quiz_rel_test_question."` AS `RTQ`,
					`".$tbl_quiz_answer."` AS `A`,
					`".$tbl_track_e_exercises."` AS `TE`,
					`".$tbl_track_e_exe_details."` AS `TED`,
					`".$tbl_user."` AS `U`
				LEFT JOIN `".$tbl_track_e_exe_answers."` AS `TEA`
				    ON `TEA`.`details_id` = `TED`.`id`
    			WHERE `RTQ`.`question_id` = `Q`.`id`
					AND `Q`.`id` = `A`.`question_id`
					AND `RTQ`.`exercice_id` = `TE`.`exe_exo_id`
					AND `TE`.`exe_id` = `TED`.`exercise_track_id`
					AND `U`.`user_id` = `TE`.`exe_user_id`
					AND `TEA`.`answer` = `A`.`id`
					AND `TED`.`question_id` = `Q`.`id`
					AND `Q`.`id` = ".$this->question->selectId()."
				ORDER BY `TE`.`exe_date` ASC, `name` ASC";

		// we need to compile all answers of one attempt on the same line
		$tmpRecordList = claro_sql_query_fetch_all($sql);

		$previousKey = '';
		foreach( $tmpRecordList as $tmpRecord )
		{
			// build a unique key for each line of the csv
			// different answer of a same attempt have same date and name
			$key = $tmpRecord['date'].$tmpRecord['name'];
			if( $key != $previousKey )
			{
				// add infos in record list
				$recordList[$key]['date'] = $tmpRecord['date'];
				$recordList[$key]['name'] = $tmpRecord['name'];
				$recordList[$key]['question'] = $tmpRecord['question'];
			}
			// add answer one by one
			$recordList[$key][] = $tmpRecord['reponse'];
			
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
    var $exercise;
	var $question;

	function csvTrackFIB($question)
	{
        parent::csv(); // call constructor of parent class
		$this->question = $question;
  	}

   	// create record list
   	function buildRecords()
   	{
		$tbl_mdb_names = claro_sql_get_main_tbl();
		$tbl_user 					= $tbl_mdb_names['user'					];

      	$tbl_cdb_names = claro_sql_get_course_tbl();
  		$tbl_quiz_answer			= $tbl_cdb_names['quiz_answer'			];
		$tbl_quiz_question			= $tbl_cdb_names['quiz_question'		];
		$tbl_quiz_rel_test_question	= $tbl_cdb_names['quiz_rel_test_question'];
  		$tbl_track_e_exercises		= $tbl_cdb_names['track_e_exercices'	];
		$tbl_track_e_exe_details	= $tbl_cdb_names['track_e_exe_details'	];
		$tbl_track_e_exe_answers	= $tbl_cdb_names['track_e_exe_answers'	];

		$sql = "SELECT `TE`.`exe_date` as `date`,
						CONCAT(`U`.`prenom`,' ',`U`.`nom`) AS `name`,
						`Q`.`question`,
						`TEA`.`answer`
				FROM `".$tbl_quiz_question."` AS `Q`,
					`".$tbl_quiz_rel_test_question."` AS `RTQ`,
					`".$tbl_quiz_answer."` AS `A`,
					`".$tbl_track_e_exercises."` AS `TE`,
					`".$tbl_track_e_exe_details."` AS `TED`,
					`".$tbl_user."` AS `U`
				LEFT JOIN `".$tbl_track_e_exe_answers."` AS `TEA`
				    ON `TEA`.`details_id` = `TED`.`id`
    			WHERE `RTQ`.`question_id` = `Q`.`id`
					AND `Q`.`id` = `A`.`question_id`
					AND `RTQ`.`exercice_id` = `TE`.`exe_exo_id`
					AND `TE`.`exe_id` = `TED`.`exercise_track_id`
					AND `U`.`user_id` = `TE`.`exe_user_id`
					AND `TED`.`question_id` = `Q`.`id`
					AND `Q`.`id` = ".$this->question->selectId()."
				ORDER BY `TE`.`exe_date` ASC, `name` ASC";

		// we need to compile all answers of one attempt on the same line
		$tmpRecordList = claro_sql_query_fetch_all($sql);

		$previousKey = '';
		foreach( $tmpRecordList as $tmpRecord )
		{
			// build a unique key for each line of the csv
			// different answer of a same attempt have same date and name
			$key = $tmpRecord['date'].$tmpRecord['name'];
			if( $key != $previousKey )
			{
				// add infos in record list
				$recordList[$key]['date'] = $tmpRecord['date'];
				$recordList[$key]['name'] = $tmpRecord['name'];
				$recordList[$key]['question'] = $tmpRecord['question'];
			}
			// add answer one by one
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
    var $exercise;
	var $question;

	function csvTrackMatching($question)
	{
        parent::csv(); // call constructor of parent class
		$this->question = $question;
	}

   	// create record list
   	function buildRecords()
   	{

	}
}

function export_question_tracking($questionId)
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
            $cvsTrack = new csvTrackSingle($objQuestion);
            break;
        case MULTIPLE_ANSWER:
            $cvsTrack = new csvTrackMulti($objQuestion);
            break;
        case FILL_IN_BLANKS:
            $cvsTrack = new csvTrackFIB($objQuestion);
            break;
        case MATCHING:
            $cvsTrack = new csvTrackMatching($objQuestion);
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
?>