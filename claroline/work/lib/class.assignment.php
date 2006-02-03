<?php

class Assignment 
{
	/**
     * @var $id id of assignment, 0 if assignment doesn't exist already
     */
    var $id;
	/**
     * @var $title name of the assignment
     */
    var $title;
    
	/**
     * @var $description statement of the assignment
     */
    var $description;
    
    /**
     * @var $visibility visibility of the assignment
     */
    var $visibility;
    
    /**
     * @var $defaultSubmissionVisibility default visibility of new submissions in this assignement
     */
    var $defaultSubmissionVisibility;
    
    /**
     * @var $assignmentType is the assignment for groups or for individuals
     */
    var $assignmentType;
    
    /**
     * @var $submissionType expected submission type (text, text and file, file)
     */
    var $submissionType;
    
    /**
     * @var $allowLateUpload is upload allowed after assignment end date
     */
    var $allowLateUpload;
    
    /**
     * @var $startDate submissions are not possible before this date
     */
    var $startDate;
    
    /**
     * @var $endDate submissions are not possible after this date (except if $allowLateUpload is true)
     */
    var $endDate;
    
    /**
     * @var $autoFeedbackText text of automatic feedback
     */
    var $autoFeedbackText;
    
    /**
     * @var $autoFeedbackFilename file of automatic feedback
     */
    var $autoFeedbackFilename;
    
    /**
     * @var $autoFeedbackSubmitMethod automatic feedback submit method
     */
    var $autoFeedbackSubmitMethod;

    /**
     * @var $assigDirSys sys path to assignment dir
     */
    var $assigDirSys;

	/**
     * @var $assigDirWeb web path to assignment dir
     */
    var $assigDirWeb; 

    /**
     * @var $tblAssignment sys path to assignment dir
     */
    var $tblAssignment; 
    
	/**
     * @var $tblSubmission web path to assignment dir
     */
    var $tblSubmission;     
        
    /**
     * constructor
     *
     * @author Sébastien Piraux <pir@cerdecam.be>
     */    
    function Assignment($course_id = null) 
    {    
    	$this->id = (int) 0;
    	$this->title = '';
	    $this->description = '';
	    $this->visibility = 'VISIBLE';
	    $this->defaultSubmissionVisibility = 'VISIBLE';
	    $this->assignmentType = 'INDIVIDUAL';
	    $this->submissionType = 'FILE';
	    $this->allowLateUpload = 'YES';
	    $this->startDate = '';
	    $this->endDate = '';
	    $this->autoFeedbackText = '';
	    $this->autoFeedbackFilename = '';
	    $this->autoFeedbackSubmitMethod = 'ENDDATE';
	    
	    $this->assigDirSys = '';
	    $this->assigDirWeb = '';	    
	    
	    $tbl_cdb_names = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
		$this->tblAssignment = $tbl_cdb_names['wrk_assignment'];
		$this->tblSubmission = $tbl_cdb_names['wrk_submission'];
    }
    
    /**
     * load an assignment from DB 
     *
     * @author Sébastien Piraux <pir@cerdecam.be>
     * @param integer $assignment_id id of assignment
     * @return boolean load successfull ?
     */   
    function load($assignment_id)
    {
    		
	    $sql = "SELECT
					`id`,
	                `title`,
	                `description`,
	                `visibility`,
	                `def_submission_visibility`,
	                `assignment_type`,
	                `authorized_content`,
	                `allow_late_upload`,
                	UNIX_TIMESTAMP(`start_date`) AS `unix_start_date`,
					UNIX_TIMESTAMP(`end_date`) AS `unix_end_date`,
	                `prefill_text`,
	                `prefill_doc_path`,
	                `prefill_submit`
	        FROM `".$this->tblAssignment."`
	        WHERE `id` = ".(int) $assignment_id;
	
	    $data = claro_sql_query_get_single_row($sql);
	
	    if( !empty($data) )
	    {
	    	// from query
	        $this->id = (int) $data['id'];
	    	$this->title = $data['title'];
		    $this->description = $data['description'];
		    $this->visibility = $data['visibility'];
		    $this->defaultSubmissionVisibility = $data['def_submission_visibility'];
		    $this->assignmentType = $data['assignment_type'];
		    $this->submissionType = $data['authorized_content'];
		    $this->allowLateUpload = $data['allow_late_upload'];
		    $this->startDate = $data['unix_start_date'];
		    $this->endDate = $data['unix_end_date'];
		    $this->autoFeedbackText = $data['prefill_text'];
		    $this->autoFeedbackFilename = $data['prefill_doc_path'];
		    $this->autoFeedbackSubmitMethod = $data['prefill_submit'];
		    
		    // build
			$this->buildDirPaths();
			
		    return true;
	    }
	    else
	    {
	        return false;
	    }
    }
    
    /**
     * save assignment to DB
     *
     * @author Sébastien Piraux <pir@cerdecam.be>
     * @return mixed false or id of the record
     */   
    function save()
    {		
    	// TODO method to validate data
    	if( $this->id == 0 )
    	{
    		// insert	
		    $sql = "INSERT INTO `".$this->tblAssignment."`
		            SET `title` = '".addslashes($this->title)."',
		                `description` = '".addslashes($this->description)."',
		                `visibility` = '".addslashes($this->visibility)."',
		                `def_submission_visibility` = '".addslashes($this->defaultSubmissionVisibility)."',
		                `assignment_type` = '".addslashes($this->assignmentType)."',
		                `authorized_content` = '".addslashes($this->submissionType)."',
		                `allow_late_upload` = '".addslashes($this->allowLateUpload)."',
		                `start_date` = FROM_UNIXTIME('".addslashes($this->startDate)."'),
		                `end_date` = FROM_UNIXTIME('".addslashes($this->endDate)."'),
						`prefill_text` = '".addslashes($this->autoFeedbackText)."', 	
						`prefill_doc_path` = '".addslashes($this->autoFeedbackFilename)."', 	
						`prefill_submit` = '".addslashes($this->autoFeedbackSubmitMethod)."'";
		
		    // on creation of an assignment the automated feedback take the default values from mysql
		
		    // execute the creation query and get id of inserted assignment
		    $lastAssigId = claro_sql_query_insert_id($sql);
		
		    if( $lastAssigId )
		    {
		    	$this->id = (int) $lastAssigId;
		    
		    	$this->buildDirPaths();		    	
		    	
		        // create the assignment directory if query was successfull and dir not already exists
		        if( !is_dir( $this->assigDirSys ) ) mkdir( $this->assigDirSys , CLARO_FILE_PERMISSIONS );
		        		        
		        return $this->id;
		    }
		    else
		    {
		        return false;
		    }
    	}
    	else
    	{
			if( !get_conf('confval_def_sub_vis_change_only_new') )
		    {
		        // get current assignment defaultSubmissionVisibility
		        $sqlGetOldData = "SELECT `def_submission_visibility`
				        		 FROM `".$this->tblAssignment."`
				            	 WHERE `id` = '".$this->id."'";
				            	 
		        $prevDefaultSubmissionVisibility = claro_sql_query_get_single_value($sqlGetOldData);
		        
		        // change visibility of all works only if defaultSubmissionVisibility has changed
		        if( $this->defaultSubmissionVisibility != $prevDefaultSubmissionVisibility )
		        {
		        	$this->updateAllSubmissionsVisibility($this->defaultSubmissionVisibility);
		        }
		    }		        
		    
    		// update, main query	
		    $sql = "UPDATE `".$this->tblAssignment."`
		            SET `title` = '".addslashes($this->title)."',
		                `description` = '".addslashes($this->description)."',
		                `visibility` = '".addslashes($this->visibility)."',
		                `def_submission_visibility` = '".addslashes($this->defaultSubmissionVisibility)."',
		                `assignment_type` = '".addslashes($this->assignmentType)."',
		                `authorized_content` = '".addslashes($this->submissionType)."',
		                `allow_late_upload` = '".addslashes($this->allowLateUpload)."',
		                `start_date` = FROM_UNIXTIME('".addslashes($this->startDate)."'),
		                `end_date` = FROM_UNIXTIME('".addslashes($this->endDate)."'),
						`prefill_text` = '".addslashes($this->autoFeedbackText)."', 	
						`prefill_doc_path` = '".addslashes($this->autoFeedbackFilename)."', 	
						`prefill_submit` = '".addslashes($this->autoFeedbackSubmitMethod)."'
		            WHERE `id` = '".$this->id."'";
		
		    // execute and return main query
		    if( claro_sql_query($sql) )
		    {
		    	return $this->id;
		    }
		    else
		    {
		    	return false;
		    }
    	}
    }
    
    /**
     * delete assignment from DB
     *
     * @author Sébastien Piraux <pir@cerdecam.be>
     * @return boolean 
     */
	function delete()
	{		
			// TODO $this->getSubmissionList() and delete each submission of this assignment using $submission->delete();
			foreach( $this->getSubmissionList() as $submission )
			{
				SUBMISSION::delete($submission['id'],$this->assigDirSys.$submission['filename']);
			}
			// TODO if no error : delete assignment directory
			return false; // if error	    

		$sql = "DELETE FROM `".$this->tblAssignment."` WHERE `assignment_id`= '".$this->id."'";
		claro_sql_query($sql);
		
		return true;
	}

	/**
     * update visibility of all submissions of the assignment
     *
     * @author Sébastien Piraux <pir@cerdecam.be>
     * @param string $visibility
     * @return boolean  
     */    
    function updateAllSubmissionsVisibility($visibility)
    {
    	$acceptedValues = array('VISIBLE', 'INVISIBLE');
		
		if( in_array($visibility, $acceptedValues) )
		{
			// adapt visibility of all submissions of the assignment
			// according to the default submission visibility
			$sql = "UPDATE `".$this->tblSubmission."`
		            SET `visibility` = '".addslashes($visibility)."'
		            WHERE `assignment_id` = ".$this->id."
		            AND `visibility` != '".addslashes($visibility)."'";
			
			return claro_sql_query ($sql);
		}	
		
		return false;
    }
	/**
     * get submission list of assignment
     *
     * @author Sébastien Piraux <pir@cerdecam.be>
     * @return array  
     */
    function getSubmissionList()
    {
		$tbl_cdb_names = claro_sql_get_course_tbl();
		$this->tblSubmission = $tbl_cdb_names['wrk_submission'];

		$sql = "SELECT `id`, `submitted_doc_path` 
				FROM `".$this->tblSubmission."`
				WHERE `assignment_id` =  ".$this->id;
		
		return claro_sql_query_fetch_all($sql);
    }
    /*
		//TODO get unique filename function 
		while( file_exists($assigDirSys.$filename.'_'.$i.$extension) ) $i++;

        $prefillDocPath = $filename.'_'.$i.$extension;
     */
    // getter and setter
    // TODO getter and setter as unix timestamp AND as datetime for date

	function getTitle()
	{
		return $this->title;		
	}
	
	function setTitle($value)
	{
		$this->title = $value;	
	}
	
	 
	function getDescription()
	{
		return $this->description;	
	}
	
	function setDescription($value)
	{
		$this->description = $value;	
	}
	
	 
	function getVisibility()
	{
		return $this->visibility;
	}
	
	function setVisibility($value)
	{
		$acceptedValues = array('VISIBLE', 'INVISIBLE');
		
		if( in_array($value, $acceptedValues) )
		{
			$this->visibility = $value;
			return true;	
		}
		return false;
	}
	
	 
	function getDefaultSubmissionVisibility()
	{
		return $this->defaultSubmissionVisibility;
	}
	
	function setDefaultSubmissionVisibility($value)
	{
		$acceptedValues = array('VISIBLE', 'INVISIBLE');
		
		if( in_array($value, $acceptedValues) )
		{
			$this->defaultSubmissionVisibility = $value;
			return true;	
		}
		return false;
	}
	
	 
	function getAssignmentType()
	{
		return $this->assignmentType;
	}
	
	function setAssignmentType($value)
	{
		$acceptedValues = array('INDIVIDUAL', 'GROUP');
		
		if( in_array($value, $acceptedValues) )
		{
			$this->assignmentType = $value;
			return true;	
		}
		return false;
	}
	
	 
	function getSubmissionType()
	{
		return $this->submissionType;
	}
	
	function setSubmissionType($value)
	{
		$acceptedValues = array('TEXT', 'TEXTFILE', 'FILE');
		
		if( in_array($value, $acceptedValues) )
		{
			$this->submissionType = $value;
			return true;	
		}
		return false;
	}
	
	 
	function getAllowLateUpload()
	{
		return $this->allowLateUpload;
	}
	
	function setAllowLateUpload($value)
	{
		$acceptedValues = array('YES', 'NO');
		
		if( in_array($value, $acceptedValues) )
		{
			$this->allowLateUpload = $value;
			return true;	
		}
		return false;			
	}
	
	 
	function getStartDate()
	{
		return $this->startDate;
	}
	
	function setStartDate($value)
	{
		$this->startDate = (int) $value;
	}
	
	function getEndDate()
	{
		return $this->endDate;
	}
	
	function setEndDate($value)
	{
		$this->endDate = (int) $value;
	}
	 
	function getAutoFeedbackText()
	{
		return $this->autoFeedbackText;	
	}
	
	function setAutoFeedbackText($value)
	{
		$this->autoFeedbackText = $value;
	}
	
	 
	function getAutoFeedbackFilename()
	{
		return $this->autoFeedbackFilename;
	}
	
	function setAutoFeedbackFilename($value)
	{
		$this->autoFeedbackFilename = $value;
	}
	
	
	function getAutoFeedbackSubmitMethod()
	{
		return $this->autoFeedbackSubmitMethod;
	}
	
	function setAutoFeedbackSubmitMethod($value)
	{
		$acceptedValues = array('ENDDATE', 'AFTERPOST');
		
		if( in_array($value, $acceptedValues) )
		{
			$this->autoFeedbackSubmitMethod = $value;
			return true;	
		}
		return false;	
	}

	function buildDirPaths()
	{
		global $_course;
		
		$this->assigDirSys = get_conf('coursesRepositorySys').$_course['path'].'/'.'work/assig_'.$this->id.'/';
		$this->assigDirWeb = get_conf('coursesRepositoryWeb').$_course['path'].'/'.'work/assig_'.$this->id.'/';
			
	}

}
?>