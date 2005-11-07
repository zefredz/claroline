<?php // $Id$
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.6
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2004 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      |   This program is free software; you can redistribute it and/or      |
      |   modify it under the terms of the GNU General Public License        |
      |   as published by the Free Software Foundation; either version 2     |
      |   of the License, or (at your option) any later version.             |
      +----------------------------------------------------------------------+
*/
if(!class_exists('Question')):

		/*>>>>>>>>>>>>>>>>>>>> CLASS QUESTION <<<<<<<<<<<<<<<<<<<<*/

/**
 * This class allows to instantiate an object of type Question
 */
class Question
{
	var $id;
	var $question;
	var $description;
	var $weighting;
	var $position;
	var $type;
	var $attachedFile;
  
	var $tempAttachedFile;

	var $exerciseList;  // array with the list of exercises which this question is in

	/**
	 * constructor of the class
	 *
	 * @author - Olivier Brouckaert
	 */
	function Question()
	{
		$this->id=0;
		$this->question='';
		$this->description='';
		$this->weighting=0;
		$this->position=1;
		$this->type=2;
		$this->attachedFile='';

		$this->tempAttachedFile = '';

		$this->exerciseList=array();
	}

	/**
	 * reads question informations from the data base
	 *
	 * @author - Olivier Brouckaert
	 * @param - integer $id - question ID
	 * @return - boolean - true if question exists, otherwise false
	 */
	function read($id)
	{
		global $tbl_quiz_question, $tbl_quiz_rel_test_question;

		$sql = "SELECT question,description,ponderation,q_position,type,attached_file FROM `".$tbl_quiz_question."` WHERE id='".$id."'";
		$result = claro_sql_query($sql);

		// if the question has been found
		if($object = mysql_fetch_object($result))
		{
			$this->id = $id;
			$this->question = $object->question;
			$this->description = $object->description;
			$this->weighting = $object->ponderation;
			$this->position = $object->q_position;
			$this->type = $object->type;
			$this->attachedFile = $object->attached_file;

			$sql = "SELECT `exercice_id` FROM `".$tbl_quiz_rel_test_question."` WHERE `question_id` = '".$id."'";

			$result = claro_sql_query($sql);

			// fills the array with the exercises which this question is in
			while($object = mysql_fetch_object($result))
			{
				$this->exerciseList[] = $object->exercice_id;
			}

			return true;
		}

		// question not found
		return false;
	}

	/**
	 * returns the question ID
	 *
	 * @author - Olivier Brouckaert
	 * @return - integer - question ID
	 */
	function selectId()
	{
		return $this->id;
	}

	/**
	 * returns the question title
	 *
	 * @author - Olivier Brouckaert
	 * @return - string - question title
	 */
	function selectTitle()
	{
		return $this->question;
	}

	/**
	 * returns the question description
	 *
	 * @author - Olivier Brouckaert
	 * @return - string - question description
	 */
	function selectDescription()
	{
		return $this->description;
	}

	/**
	 * returns the question weighting
	 *
	 * @author - Olivier Brouckaert
	 * @return - integer - question weighting
	 */
	function selectWeighting()
	{
		return $this->weighting;
	}

	/**
	 * returns the question position
	 *
	 * @author - Olivier Brouckaert
	 * @return - integer - question position
	 */
	function selectPosition()
	{
		return $this->position;
	}

	/**
	 * returns the answer type
	 *
	 * @author - Olivier Brouckaert
	 * @return - integer - answer type
	 */
	function selectType()
	{
		return $this->type;
	}

	/**
	 * returns the array with the exercise ID list
	 *
	 * @author - Olivier Brouckaert
	 * @return - array - list of exercise ID which the question is in
	 */
	function selectExerciseList()
	{
		return $this->exerciseList;
	}

	/**
	 * returns the number of exercises which this question is in
	 *
	 * @author - Olivier Brouckaert
	 * @return - integer - number of exercises
	 */
	function selectNbrExercises()
	{
		return sizeof($this->exerciseList);
	}
        
  /**
   * returns the attached file name
   *
   */
  function selectAttachedFile() 
  {
        return $this->attachedFile;
  }
  
  /**
   * returns the temporary attached file name
   *
   */
  function selectTempAttachedFile() 
  {
        return $this->tempAttachedFile;
  }  

	/**
	 * changes the question title
	 *
	 * @author - Olivier Brouckaert
	 * @param - string $title - question title
	 */
	function updateTitle($title)
	{
		$this->question = $title;
	}

	/**
	 * changes the question description
	 *
	 * @author - Olivier Brouckaert
	 * @param - string $description - question description
	 */
	function updateDescription($description)
	{
		$this->description = $description;
	}

	/**
	 * changes the question weighting
	 *
	 * @author - Olivier Brouckaert
	 * @param - integer $weighting - question weighting
	 */
	function updateWeighting($weighting)
	{
		$this->weighting = $weighting;
	}

	/**
	 * changes the question position
	 *
	 * @author - Olivier Brouckaert
	 * @param - integer $position - question position
	 */
	function updatePosition($position)
	{
		$this->position = $position;
	}

	/**
	 * changes the answer type. If the user changes the type from "unique answer" to "multiple answers"
	 * (or conversely) answers are not deleted, otherwise yes
	 *
	 * @author - Olivier Brouckaert
	 * @param - integer $type - answer type
	 */
	function updateType($type)
	{
		global $tbl_quiz_answer;

		// if we really change the type
		if($type != $this->type)
		{
			// if we don't change from "unique answer" to "multiple answers" (or conversely)
			if(!in_array($this->type,array(UNIQUE_ANSWER,MULTIPLE_ANSWER)) || !in_array($type,array(UNIQUE_ANSWER,MULTIPLE_ANSWER)))
			{
				// removes old answers
				$sql="DELETE FROM `".$tbl_quiz_answer."` WHERE `question_id` = '".$this->id."'";
				claro_sql_query($sql);
			}

			$this->type=$type;
		}
	}

  /**
   *
   *
   *
   */   
  function updateTempAttachedFile($tempAttachedFileName)
  {
      $this->tempAttachedFile = $tempAttachedFileName;
  }
  
  
	/**
	 * attach a file to the question
	 *
	 * @author - Olivier Brouckaert
	 * @param - string $tempAttachedFile - temporary path of the file to upload
         * @param - string $attachedFile - Name(with extension)of the file
	 * @return - boolean - true if uploaded, otherwise false
	 */
	function uploadAttachedFile($tempAttachedFile,$attachedFile)
	{
		global $attachedFilePathSys;

		// if the question has got an ID
		if($this->id)
		{
        $extension=substr(strrchr($attachedFile, '.'), 1);
        
        $this->attachedFile = 'quiz-'.$this->id.'.'.$extension;
                        
	  		return @move_uploaded_file($tempAttachedFile,$attachedFilePathSys.'/'.$this->attachedFile)?true:false;
		}

		return false;
	}

	/**
	 * deletes the attached file
	 *
	 * @author - Olivier Brouckaert
	 * @return - boolean - true if removed, otherwise false
	 */
	function removeAttachedFile()
	{
		global $attachedFilePathSys;

		// if the question has got an ID and if the file exists
		if($this->id && !empty($this->attachedFile))
		{
      $attachedFile=$this->attachedFile;
      $this->attachedFile = '';
                        
			return @unlink($attachedFilePathSys.'/'.$attachedFile)?true:false;
		}

		return false;
	}

	/**
	 * exports a file to another question
	 *
	 * @author - Olivier Brouckaert
	 * @param - integer $questionId - ID of the target question
	 * @return - boolean - true if copied, otherwise false
	 */
	function exportAttachedFile($questionId)
	{
		global $tbl_quiz_question,$attachedFilePathSys;

		// if the question has got an ID and if the file exists
		if($this->id &&  !empty($this->attachedFile))
    {
        $extension=substr(strrchr($this->attachedFile, '.'), 1);
        $attachedFile='quiz-'.$questionId.'.'.$extension;
        
        $sql="UPDATE `".$tbl_quiz_question."` SET attached_file = '".$attachedFile."' WHERE id='".$questionId."'";
        claro_sql_query($sql);
        
        return @copy($attachedFilePathSys.'/'.$this->attachedFile,$attachedFilePathSys.'/'.$attachedFile)?true:false;
		}

		return false;
	}

	/**
	 * saves the file coming from POST into a temporary file
	 * Temporary files are used when we don't want to save a file right after a form submission.
	 * For example, if we first show a confirmation box.
	 *
	 * @author Olivier Brouckaert
	 * @param string $tempAttachedFile - temporary path of the file to move
   * @return string the name of the temporary file
	 */
	function setTmpAttachedFile($tempAttachedFile,$attachedFile)
	{
		global $attachedFilePathSys;
                
	    $extension=substr(strrchr($attachedFile, '.'), 1);

			// saves the file into a temporary file
	    $this->tempAttachedFile = "tmp".$this->id.".".$extension;
		if ( move_uploaded_file($tempAttachedFile,$attachedFilePathSys.'/'.$this->tempAttachedFile) )
		{
            chmod($attachedFilePathSys.'/'.$this->tempAttachedFile,CLARO_FILE_PERMISSIONS);
            return $fileName;
		}
        else
        {
            return false;
        }
	}

	/**
	 * moves the temporary question "tmp" to "quiz-$questionId.$extension"
	 * Temporary files are used when we don't want to save an attached file right after a form submission.
	 * For example, if we first show a confirmation box.
	 *
	 * @author - Olivier Brouckaert
   * @param $tmpFileName 
	 * @return - boolean - true if moved, otherwise false
	 */
	function getTmpAttachedFile()
	{
		global $attachedFilePathSys;
		// if the question has got an ID and if the file exists
		if($this->id && file_exists($attachedFilePathSys."/".$this->tempAttachedFile) )
		{
        $extension=substr(strrchr($this->tempAttachedFile, '.'), 1);
        
        $this->attachedFile = 'quiz-'.$this->id.'.'.$extension;
        return rename($attachedFilePathSys."/".$this->tempAttachedFile,$attachedFilePathSys.'/'.$this->attachedFile)?true:false;
		}
		return false;
	}

	/**
	 * updates the question in the data base
	 * if an exercise ID is provided, we add that exercise ID into the exercise list
	 *
	 * @author - Olivier Brouckaert
	 * @param - integer $exerciseId - exercise ID if saving in an exercise
	 */
	function save($exerciseId=0)
	{
		global $tbl_quiz_question;

		$id = $this->id;
		$question = addslashes($this->question);
		$description = addslashes($this->description);
		$weighting = $this->weighting;
		$position = $this->position;
		$type = $this->type;
		$attachedFile = $this->attachedFile;

		// question already exists
		if($id)
		{
			$sql = "UPDATE `".$tbl_quiz_question."` 
                    SET question='".$question."',
                        description='".$description."',
                        ponderation='".$weighting."',
                        q_position='".$position."',
                        type='".$type."',
                        attached_file='".$attachedFile."' 
                    WHERE id='".$id."'";
			claro_sql_query($sql);
		}
		// creates a new question
		else
		{
			$sql = "INSERT INTO `".$tbl_quiz_question."`(question,description,ponderation,q_position,type,attached_file) 
                    VALUES ('".$question."','".$description."','".$weighting."','".$position."','".$type."','".$attachedFile."')";
			claro_sql_query($sql);

			$this->id = mysql_insert_id();
		}

		// if the question is created in an exercise
		if($exerciseId)
		{
			// adds the exercise into the exercise list of this question
			$this->addToList($exerciseId);
		}
	}

	/**
	 * adds an exercise into the exercise list
	 *
	 * @author - Olivier Brouckaert
	 * @param - integer $exerciseId - exercise ID
	 */
	function addToList($exerciseId)
	{
		global $tbl_quiz_rel_test_question;

		$id = $this->id;

		// checks if the exercise ID is not in the list
		if(!in_array($exerciseId,$this->exerciseList))
		{
			$this->exerciseList[] = $exerciseId;

			$sql = "INSERT INTO `".$tbl_quiz_rel_test_question."` (question_id,exercice_id) VALUES('".$id."','".$exerciseId."')";
			claro_sql_query($sql);
		}
	}

	/**
	 * removes an exercise from the exercise list
	 *
	 * @author - Olivier Brouckaert
	 * @param - integer $exerciseId - exercise ID
	 * @return - boolean - true if removed, otherwise false
	 */
	function removeFromList($exerciseId)
	{
		global $tbl_quiz_rel_test_question;

		$id = $this->id;

		// searches the position of the exercise ID in the list
		$pos = array_search($exerciseId,$this->exerciseList);

		// exercise not found
		if($pos === false)
		{
			return false;
		}
		else
		{
			// deletes the position in the array containing the wanted exercise ID
			unset($this->exerciseList[$pos]);

			$sql = "DELETE FROM `".$tbl_quiz_rel_test_question."` 
                    WHERE question_id = '".$id."' AND exercice_id = '".$exerciseId."'";
			claro_sql_query($sql);

			return true;
		}
	}

	/**
	 * deletes a question from the database
	 * the parameter tells if the question is removed from all exercises (value = 0),
	 * or just from one exercise (value = exercise ID)
	 *
	 * @author - Olivier Brouckaert
	 * @param - integer $deleteFromEx - exercise ID if the question is only removed from one exercise
	 */
	function delete($deleteFromEx=0)
	{
		global $tbl_quiz_rel_test_question, $tbl_quiz_question, $tbl_quiz_answer;

		$id = $this->id;

		// if the question must be removed from all exercises
		if(!$deleteFromEx)
		{
			$sql = "DELETE FROM `".$tbl_quiz_rel_test_question."` 
                    WHERE `question_id` = '".$id."'";
			claro_sql_query($sql);

			$sql = "DELETE FROM `".$tbl_quiz_question."` 
                    WHERE `id` = '".$id."'";
			claro_sql_query($sql);

			$sql = "DELETE FROM `".$tbl_quiz_answer."` 
                    WHERE `question_id` = '".$id."'";
			claro_sql_query($sql);

			$this->removeAttachedFile();

			// resets the object
			$this->Question();
		}
		// just removes the exercise from the list
		else
		{
			$this->removeFromList($deleteFromEx);
		}
	}

	/**
	 * duplicates the question
	 *
	 * @author - Olivier Brouckaert
	 * @return - integer - ID of the new question
	 */
	function duplicate()
	{
		global $tbl_quiz_question;

		$question = addslashes($this->question);
		$description = addslashes($this->description);
		$weighting = $this->weighting;
		$position = $this->position;
		$type = $this->type;

		$sql = "INSERT INTO `".$tbl_quiz_question."` (question,description,ponderation,q_position,type)
			    VALUES('".$question."','".$description."','".$weighting."','".$position."','".$type."')";
			
		$id = claro_sql_query_insert_id($sql);

		// duplicates the attached file
		$this->exportAttachedFile($id);

		return $id;
	}
}

endif;
?>
