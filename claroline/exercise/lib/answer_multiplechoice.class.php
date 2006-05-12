<?php // $Id$
/**
 * CLAROLINE
 *
 * @version 1.8 $Revision$
 *
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @author Claro Team <cvs@claroline.net>
 *
 */
 
class answerMultipleChoice
{
	/**
     * @var $id id of question, -1 if answer doesn't exist already
     */
    var $questionId;
        
	/**
     * @var $answerList array with list of proposal
     *      $answerList[]['id'] // int 
     * 		$answerList[]['answer'] // text
     * 		$answerList[]['correct'] // boolean
     * 		$answerList[]['grade'] // float
     * 		$answerList[]['comment'] // text
     */
    var $answerList;

    /**
     * @var $multipleAnswer boolean true if multiple answer
     */
    var $multipleAnswer;

    //----- Others
    
    /** 
     * @var $response response sent by user and stored in object for easiest use  
     * use extractResponseFromRequest to set it
     */
	var $response;
	    
    /**
     * @var $errorList is used to store error that comes on form post
     */
    var $errorList;
    
    /**
     * @var $tblAnswer
     */
    var $tblAnswer;  
    
    /**
     * constructor
     *
     * @author Sbastien Piraux <pir@cerdecam.be>
     * @param $questionId integer question that use this answer 
     * @param $multipleAnswer boolean true if several answer can be checked by user
     * @param $course_id to use the class when not in course context
     * @return string   
     */	     
    function answerMultipleChoice($questionId, $multipleAnswer = false, $course_id = null) 
    {
		$this->questionId = (int) $questionId;
    	
    	$this->multipleAnswer = (bool) $multipleAnswer;

    	$this->answerList = array();
    	// add 2 empty answers as minimum requested number of answers
    	$this->addAnswer();
    	$this->addAnswer();
    	    	
    	$this->response = array();
    	$this->errorList = array();
    	
    	$tbl_cdb_names = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
		$this->tblAnswer = $tbl_cdb_names['qwz_answer_multiple_choice'];
    }

    /**
     * load answers in object
     *
     * @author Sbastien Piraux <pir@cerdecam.be>
     * @return boolean result of operation   
     */	      
    function load() 
    {
    	$sql = "SELECT
    				`id`,
	                `answer`,
	                `correct`,
	                `grade`,
	                `comment`
	        FROM `".$this->tblAnswer."`
	        WHERE `questionId` = ".(int) $this->questionId."
	        ORDER BY `id`";
	
	    $data = claro_sql_query_fetch_all($sql);

	    if( !empty($data) )
	    {
	    	$this->answerList = $data;
	    	if( count($data) == 1 )
	    	{
	    		// it is not a normal comportment but we need at least 2 answers !
	    		$this->addAnswer();	
	    	}
					
			return true;
	    }
	    else
	    {
	        return false;
	    }
    }

    /**
     * save object in db
     *
     * @author Sbastien Piraux <pir@cerdecam.be>
     * @return boolean result of operation   
     */	        
    function save() 
    {
    	$sql = "DELETE FROM `".$this->tblAnswer."` 
                WHERE questionId = ".(int) $this->questionId;
        
        if( claro_sql_query($sql) == false ) return false;
       
       	// inserts new answers into data base
        $sql = "INSERT INTO `".$this->tblAnswer."` (`questionId`,`answer`,`correct`,`grade`,`comment`)
                VALUES ";

        foreach($this->answerList as $anAnswer)
        {
            $sql .= "(".(int) $this->questionId.",
            		'".addslashes($anAnswer['answer'])."',
        			'".addslashes($anAnswer['correct'])."',
        			'".addslashes($anAnswer['grade'])."',
        			'".addslashes($anAnswer['comment'])."'),";
        }
        
        $sql = substr($sql,0,-1); // remove trailing ,
        
        return claro_sql_query($sql);
    }

    /**
     * delete answers from db
     *
     * @author Sbastien Piraux <pir@cerdecam.be>
     * @return boolean result of operation   
     */	      
    function delete() 
    {
    	$sql = "DELETE FROM `".$this->tblAnswer."` 
                WHERE `questionId` = ".(int) $this->questionId;
        
        return claro_sql_query($sql);
    }

    /**
     * clone the object
     *
     * @author Sbastien Piraux <pir@cerdecam.be>
     * @return boolean result of operation   
     */	    
    function duplicate()
    {
    	// TODO duplicate	
    }
    
    /**
     * check if the object content is valide (use before using save method)
     *
     * @author Sbastien Piraux <pir@cerdecam.be>
     * @return boolean result of operation   
     */	     
    function validate()
    {
    	// must have at least a correct answer
    	$hasGoodAnswer = false;
		// must have text in answer
    	foreach( $this->answerList as $answer )
    	{
			if( $answer['correct'] == 1 )
			{
				$hasGoodAnswer = true;
			}	
    		
    		if( trim($answer['answer']) == '' )
    		{
	    		$this->errorList[] = get_lang('Please give the answers to the question');
	    		return false;
    		}
    	}
    	
    	if( !$hasGoodAnswer )
    	{
    		$this->errorList[] = get_lang('Please choose a good answer');
    		return false;
    	}
    	   	
    	return true;
    }
    
    /**
     * handle the form, get data of request and put in the object, handle commands if required
     *
     * @author Sbastien Piraux <pir@cerdecam.be>
     * @return boolean true if form can be checked and saved, false   
     */
    function handleForm()
    {
    	$this->answerList = array();
    	
    	// set form value in object
		for( $i = 0; $i < $_REQUEST['answerCount']; $i++ )
		{
			$answerNumber = $i + 1;

			//-- answer text			
			$answer = 'answer_'.$this->questionId.'_'.$answerNumber;						
			if( isset($_REQUEST[$answer]) ) 	$this->answerList[$i]['answer'] = $_REQUEST[$answer];
			else								$this->answerList[$i]['answer'] = '';
			
			//-- correct answer
			$correct = 'correct_'.$this->questionId.'_'.$answerNumber;
			if( $this->multipleAnswer )
			{				
				if( isset($_REQUEST[$correct]) ) 	$this->answerList[$i]['correct'] = 1;
				else								$this->answerList[$i]['correct'] = 0;
			}
			else
			{
				if( isset($_REQUEST['correct']) && $_REQUEST['correct'] == $correct ) 	
				{
					$this->answerList[$i]['correct'] = 1;
				}
				else
				{
					$this->answerList[$i]['correct'] = 0;
				}	
			}
			
			//-- feedbacks
			$comment = 'comment_'.$this->questionId.'_'.$answerNumber;
			if( isset($_REQUEST[$comment]) ) 	$this->answerList[$i]['comment'] = $_REQUEST[$comment];
			else								$this->answerList[$i]['comment'] = '';
			
			//-- grade
			$grade = 'grade_'.$this->questionId.'_'.$answerNumber;
			if( isset($_REQUEST[$grade]) ) 		
			{	
				if( $this->answerList[$i]['correct'] == 1 )
				{
					// correct answer must have positive answer
					$this->answerList[$i]['grade'] = abs($_REQUEST[$grade]);
				}
				else
				{
					// incorrect answer must have negative score
					$this->answerList[$i]['grade'] = 0 - abs($_REQUEST[$grade]);
				}
			}
			else
			{
				$this->answerList[$i]['grade'] = 0;
			} 					
		}

    	//-- cmd
		if( isset($_REQUEST['cmdRemAnsw']) )
		{	
			$this->remAnswer();
    		return false;
		}
		
		if( isset($_REQUEST['cmdAddAnsw']) )
		{
			$this->addAnswer();
    		return false;
		}
		
		// no special command
		return true; 
    }
    
    /**
     * provide the list of error that validate found
     *
     * @author Sbastien Piraux <pir@cerdecam.be>
     * @return array list of errors   
     */	    
    function getErrorList()
    {
    	return $this->errorList;
    }

    /**
     * display the answers as a form part for display in quizz submission page
     *
     * @author Sbastien Piraux <pir@cerdecam.be>
     * @return string html code for display of answer   
     */	   
    function getAnswerHtml()
    {
		if( empty($this->answerList) )
    	{
    		$html = "\n" . '<p>' . get_lang('There is no answer for the moment') . '</p>' . "\n\n";
    	}
    	else
    	{
    		if( $this->multipleAnswer )
			{
				$questionTypeLang = get_lang('Multiple choice (Multiple answers)');	
			}
			else
			{
				$questionTypeLang = get_lang('Multiple choice (Unique answer)');
			}
			
	    	$html = '<table width="100%">' . "\n\n";
			
			foreach( $this->answerList as $answer )
			{
				$isSelected = array_key_exists($answer['id'], $this->response);
				
				$html .= 
					'<tr>' . "\n" 
				. 	'<td align="center" width="5%">' . "\n";
				
				if( $this->multipleAnswer )
				{
	    			$html .= 
						'<input name="a_'.$this->questionId.'_'.$answer['id'].'" id="a_'.$this->questionId.'_'.$answer['id'].'" value="true" type="checkbox" '
					.	( $isSelected ? 'checked="checked"':'' )
					.	'/>' . "\n";
				}
				else
				{
					$html .= 
						'<input name="a_'.$this->questionId.'" id="a_'.$this->questionId.'_'.$answer['id'].'" value="'.$answer['id'].'" type="radio" '
					.	( $isSelected ? 'checked="checked"':'' )
					.	'/>' . "\n";
				}
				
	    		$html .=
	    			'</td>' . "\n"
	    		.	'<td width="95%">' . "\n"
	    		.	'<label for="a_'.$this->questionId.'_'.$answer['id'].'">' . $answer['answer'] . '</label>' . "\n"
	    		.	'</td>' . "\n"
	    		.	'</tr>' . "\n\n";
			}	

	    	$html .= 
				'</table>' . "\n"
			.	'<p><small>' . $questionTypeLang . '</small></p>' . "\n";
    	}
    	
    	return $html;	
    }

    /**
     * display the input hidden field depending on what was submitted in exercise submit form
     *
     * @author Sbastien Piraux <pir@cerdecam.be>
     * @return string html code for display of hidden sent data 
     */	   
    function getHiddenAnswerHtml()
    {
    	$html = "\n" . '<!-- ' . $this->questionId . ' -->' . "\n";
    	
    	foreach( $this->answerList as $answer )
    	{  		
    		if( array_key_exists($answer['id'], $this->response) ) 
    		{
    			if( $this->multipleAnswer )
				{
    				$html .= '<input type="hidden" name="a_'.$this->questionId.'_'.$answer['id'].'" value="true" />' . "\n";
				}
				else
				{
					$html .= '<input type="hidden" name="a_'.$this->questionId.'" value="'.$answer['id'].'" />' . "\n";
					// only one response is required so get out of the loop
					break;	
				}
    		}
    	}
   	
    	$html .= "\n" . '<!-- ' . $this->questionId . '(end) -->' . "\n";
    	return $html;
    }

    /**
     * display the input hidden field depending on what was submitted in exercise submit form
     *
     * @author Sbastien Piraux <pir@cerdecam.be>
     * @return string html code for display of feedback for this answer   
     */	   
    function getAnswerFeedbackHtml()
    {
    	global $imgRepositoryWeb;
    	
	    if( $this->multipleAnswer )
		{
			$questionTypeLang = get_lang('Multiple choice (Multiple answers)');	
			$imgOnHtml = '<img src="'.$imgRepositoryWeb.'checkbox_on.gif" border="0" alt="[X]" />';
    		$imgOffHtml = '<img src="'.$imgRepositoryWeb.'checkbox_off.gif" border="0" alt="[ ]" />';
		}
		else
		{
			$questionTypeLang = get_lang('Multiple choice (Unique answer)');
			$imgOnHtml = '<img src="'.$imgRepositoryWeb.'radio_on.gif" border="0" alt="(X)" />';
    		$imgOffHtml = '<img src="'.$imgRepositoryWeb.'radio_off.gif" border="0" alt="( )" />';
		}    	
    	    	 
    	$html = 
			'<table width="100%">' . "\n\n"
			
		.	'<tr style="font-style:italic;font-size:small;">' . "\n"
		.	'<td align="center" valign="top" width="5%">'.get_lang('Your choice').'</td>' . "\n"
		.	'<td align="center" valign="top" width="5%">'.get_lang('Expected choice').'</td>' . "\n"
		.	'<td valign="top" width="45%">'.get_lang('Answer').'</td>' . "\n"
		.	'<td valign="top" width="45%">'.get_lang('Comment').'</td>' . "\n"		
		.	'</tr>' . "\n\n";
			
			
		foreach( $this->answerList as $answer )
		{
			$isSelected = array_key_exists($answer['id'], $this->response);
			
			$html .=
	    		'<tr>' . "\n" 
			.	'<td align="center" width="5%">'
	    	.	( $isSelected ? $imgOnHtml : $imgOffHtml ) 
	    	.	'</td>' . "\n"
	    	.	'<td align="center" width="5%">'  
	   		.	( $answer['correct'] ? $imgOnHtml : $imgOffHtml )
	    	.	'</td>' . "\n"
			.	'<td width="45%">'
	    	.	$answer['answer'] 
	    	.	'</td>' . "\n"
	    	.	'<td width="45%">'
	    	.	( $isSelected ? $answer['comment'] : '&nbsp;' ) 
	    	.	'</td>' . "\n"
	    	.	'</tr>' . "\n\n";
		}
		
    		
		$html .=
			'</table>' . "\n"
		.	'<p><small>' . $questionTypeLang . '</small></p>' . "\n";
    	
    	return $html; 

    }
        
    /**
     * display the form to edit answers
     *
     * @author Sbastien Piraux <pir@cerdecam.be>
     * @return string html code for display of answer edition form   
     */	    
    function getFormHtml($exId = null)
    {
	   	$html = 
    		'<form method="post" action="./edit_answers?exId='.$exId.'&amp;quId='.$this->questionId.'">' . "\n"
    	. 	'<input type="hidden" name="cmd" value="exEdit" />' . "\n"
    	.	'<input type="hidden" name="answerCount" value="'.count($this->answerList).'" />' . "\n" 
    	.	'<input type="hidden" name="claroFormId" value="'.uniqid('').'">' . "\n"
    	.	'<table class="claroTable">' . "\n"
    		
    	.	'<tr class="headerX">' . "\n"
    	.	'<th>' . get_lang('Expected choice') . '</th>' . "\n"
    	.	'<th>' . get_lang('Answer') . '</th>' . "\n"
    	.	'<th>' . get_lang('Comment') . '</th>' . "\n"
    	.	'<th>' . get_lang('Weighting') . '</th>' . "\n"
    	.	'</tr>' . "\n\n";
		
		$i = 1;
		foreach( $this->answerList as $answer )
		{    	
    		$html .= 
				'<tr>' . "\n"
    		. 	'<td valign="top" align="center">';
    		
    		if( $this->multipleAnswer )
    		{
				$html .= 
					'<input name="correct_'.$this->questionId.'_'.$i.'" id="correct_'.$this->questionId.'_'.$i.'" '
					.( $answer['correct'] ? 'checked="checked"':'')
					.' type="checkbox" value="1" />';
    		}
    		else
    		{
    			$html .= 
					'<input name="correct" id="correct_'.$this->questionId.'_'.$i.'" '
					.( $answer['correct'] ? 'checked="checked"':'')
					.' type="radio" value="correct_'.$this->questionId.'_'.$i.'" />';
    		}
    		
			$html .= 
				'</td>' . "\n"
    		. 	'<td valign="top"><textarea rows="7" cols="25" name="answer_'.$this->questionId.'_'.$i.'">' . $answer['answer'] . '</textarea></td>' . "\n"
    		. 	'<td><textarea rows="7" cols="25" name="comment_'.$this->questionId.'_'.$i.'">' .$answer['comment'] . '</textarea></td>' . "\n"
    		. 	'<td valign="top"><input name="grade_'.$this->questionId.'_'.$i.'" size="5" value="' . $answer['grade'] . '" type="text" /></td>' . "\n"
    		. 	'</tr>' . "\n\n";
			
			$i++;   		
		}
    	
    	$html .=	    		
			'<tr>' . "\n"
		. 	'<td colspan="4" align="center">'
		. 	'<input type="submit" name="cmdOk" value="' . get_lang('Ok') . '" />&nbsp;&nbsp;'
		. 	'<input type="submit" name="cmdRemAnsw" value="' . get_lang('Rem. answ.') . '" />&nbsp;&nbsp;'
		. 	'<input type="submit" name="cmdAddAnsw" value="' . get_lang('Add answ.') . '" />&nbsp;&nbsp;'
		. 	claro_html_button('./edit_question.php?exId='.$exId.'&amp;quId='.$this->questionId, get_lang("Cancel") )
		. 	'</td>' . "\n"
		. 	'</tr>' . "\n\n"
    		
    	.	'</table>' . "\n\n"
    	.	'</form>' . "\n\n";
			
    	return $html;	
    }

	/**
	 * add empty answer at end of answerList
	 * 
	 * @return boolean result of operation
	 */ 
	function addAnswer()
	{
		// id is mainly use for creation on new answer, 
		// will be overwritten by the id in db
		$addedAnswer = array(
							'id'	=> 0, 
							'answer' => '',
							'correct' => 0,
							'grade' => 0,
							'comment' => '',
							);
							
		$this->answerList[] = $addedAnswer;
	}

	/**
	 * remove empty answer ad end of answerList
	 * 
	 * @return boolean result of operation
	 */ 	
	function remAnswer()
	{
		if( count($this->answerList) > 2 )
		{
			$removedAnswer = array_pop($this->answerList);
			
			if( !is_null($removedAnswer) )
			{
				return true;
			}
			else
			{
				return false;	
			}
		}
		else
		{
			return false; 	
		}
	} 

	/** 
	 * read response from request grade it, write grade in object, return grade
	 * 
     * @author Sbastien Piraux <pir@cerdecam.be>
	 * @return float question grade 
	 * @desc return score of checked answer or 0 if nothing was checked
	 */
	function gradeResponse()
	{
		$grade = 0;
		
		foreach( $this->answerList as $answer )
    	{
    		if( array_key_exists($answer['id'], $this->response) )
    		{
   				$grade += $answer['grade'];
    			
    			// if not multiple we only need one response so get out of the loop
    			if( !$this->multipleAnswer ) break;
    		}
    	}
	    return $grade;
	}
	
	/** 
	 * get response of user via $_REQUEST and store it in object
	 * 
     * @author Sbastien Piraux <pir@cerdecam.be>
	 * @return boolean result of operation 
	 */
	function extractResponseFromRequest()
	{
		if( $this->multipleAnswer )
		{
			foreach( $this->answerList as $answer )
	    	{
				if( isset($_REQUEST['a_'.$this->questionId.'_'.$answer['id']]) ) 
				{
					$this->response[$answer['id']] = true;
				}
	    	}
		}
	    else
	    {
	    	if( isset($_REQUEST['a_'.$this->questionId]) ) 
			{
				$this->response[$_REQUEST['a_'.$this->questionId]] = true;
			}		
	    }
    	return true;
	}
		
	/** 
	 * compute grade of question from answer
	 * 
     * @author Sbastien Piraux <pir@cerdecam.be>
	 * @return float question grade 
	 */
	function getGrade()
	{
		$grade = 0; 
		
    	foreach( $this->answerList as $answer )
    	{
    		if( $answer['correct'] ) 
    		{
				$grade += $answer['grade'];
    		}
    	}
	   	return $grade;
	} 	    
	
	//-- EXPORT
	/**
     * Return the XML flow for the possible answers. 
     * That's one <response_lid>, containing several <flow_label>
     *
     * @author Amand Tihon <amand@alrj.org>
     */
    function imsExportResponses($questionIdent)
    {
        // Opening of the response block.
        if( $this->multipleAnswer )
        {
		    $out = '<response_lid ident = "MCM_' . $questionIdent . '" rcardinality = "Multiple" rtiming = "No">' . "\n"
		         . '<render_choice shuffle = "No" minnumber = "1" maxnumber = "' . count($this->answerList) . '">' . "\n";
        }
        else
        {
			$out = '<response_lid ident="MCS_' . $questionIdent . '" rcardinality="Single" rtiming="No"><render_choice shuffle="No">' . "\n";
        }
        
        // Loop over answers
        foreach( $this->answerList as $answer )
        {
            $responseIdent = $questionIdent . "_A_" . $answer['id'];
            
            $out.= '  <flow_label><response_label ident="' . $responseIdent . '">'.(!$this->multipleAnswer ? '<flow_mat class="list">':'').'<material>' . "\n"
                . '    <mattext><![CDATA[' . $answer['answer'] . ']]></mattext>' . "\n"
                . '  </material>'.(!$this->multipleAnswer ? '</flow_mat>':'').'</response_label></flow_label>' . "\n";
        }
        $out.= "</render_choice></response_lid>\n";
        
        return $out;
    }
    
    /**
     * Return the XML flow of answer processing : a succession of <respcondition>. 
     *
     * @author Amand Tihon <amand@alrj.org>
     */
    function imsExportProcessing($questionIdent)
    {
        $out = '';
        
        foreach( $this->answerList as $answer )
        {
            $responseIdent = $questionIdent . "_A_" . $answer['id'];
            $feedbackIdent = $questionIdent . "_F_" . $answer['id'];
            $conditionIdent = $questionIdent . "_C_" . $answer['id'];
            
            if( $this->multipleAnswer )
        	{
	            $out .= '<respcondition title="' . $conditionIdent . '" continue="Yes"><conditionvar>' . "\n"
				.	 '  <varequal respident="MCM_' . $questionIdent . '">' . $responseIdent . '</varequal>' . "\n";
        	}
        	else
        	{
	            $out .= '<respcondition title="' . $conditionIdent . '"><conditionvar>' . "\n"
				.	 '  <varequal respident="MCS_' . $questionIdent . '">' . $responseIdent . '</varequal>' . "\n";
        	}
               
            $out .= "  </conditionvar>\n" . '  <setvar action="Add">' . $answer['grade'] . "</setvar>\n";
                
            // Only add references for actually existing comments/feedbacks.
            if( !empty($answer['comment']) )
            {
                $out .= '  <displayfeedback feedbacktype="Response" linkrefid="' . $feedbackIdent . '" />' . "\n";
            }
            $out .= "</respcondition>\n";
        }
        return $out;
    }
         
     /**
      * Export the feedback (comments to selected answers) to IMS/QTI
      * 
      * @author Amand Tihon <amand@alrj.org>
      */
     function imsExportFeedback($questionIdent)
     {
        $out = "";
        foreach( $this->answerList as $answer )
        {
            if( !empty($answer['comment']) )
            {
                $feedbackIdent = $questionIdent . "_F_" . $answer['id'];
                $out.= '<itemfeedback ident="' . $feedbackIdent . '" view="Candidate"><flow_mat><material>' . "\n"
                    . '  <mattext><![CDATA[' . $answer['comment'] . "]]></mattext>\n"
                    . "</material></flow_mat></itemfeedback>\n";
            }
        }
        return $out;
     } 
}
?>