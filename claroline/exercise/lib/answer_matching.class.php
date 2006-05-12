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

/*
 * "match" contains the value of "code" of the answer
 * 
 */
class answerMatching 
{
	/**
     * @var $id id of question, -1 if answer doesn't exist already
     */
    var $questionId;
        
	/**
     * @var $leftList array with list of proposal 
     * 		$leftList[]['answer'] // text
     * 		$leftList[]['match'] // code of matching answer
     * 		$leftList[]['grade'] // float
     * 		$leftList[]['code'] // string,  see getUniqueCode
     * 		$leftlist[]['response'] // choice of user needs to be compared with rightList['code']
     */
    var $leftList;

	/**
     * @var $rightList array with list of proposal 
     * 		$rightList[$code]['answer'] // text
     * 		$rightList[$code]['code'] // integer
     */
    var $rightList;
    
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
     * @author Sebastien Piraux <pir@cerdecam.be>
     * @param $questionId integer question that use this answer 
     * @param $course_id to use the class when not in course context
     * @return string   
     */
    function answerMatching($questionId, $course_id = null)  
    {
    	$this->questionId = $questionId;
    	
    	$this->leftList = array();
    	$this->rightList = array();
    	    
    	$this->addExample();
    		
    	$this->errorList = array();
    	
		$tbl_cdb_names = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
		$this->tblAnswer = $tbl_cdb_names['qwz_answer_matching'];
    }
    
    /**
     * load answers in object
     *
     * @author Sebastien Piraux <pir@cerdecam.be>
     * @return boolean result of operation   
     */	        
    function load() 
    {
		$sql = "SELECT
	                `answer`,
	                `match`,
	                `grade`,
	                `code`
	        FROM `".$this->tblAnswer."`
	        WHERE `questionId` = ".(int) $this->questionId."
	        ORDER BY `id` ASC";
	
	    $answerList = claro_sql_query_fetch_all($sql);

	    if( !empty($answerList) )
	    {
	    	$this->leftList = array();
	    	$this->rightList = array();
	    	foreach( $answerList as $answer )
	    	{
	    		if( !is_null($answer['match']) )
	    		{
	    			$this->addLeft($answer['answer'],$answer['match'],$answer['grade'],$answer['code']);
	    		}
	    		else // match is null -> right option
	    		{
	    			$this->addRight($answer['answer'], $answer['code']);	
	    		}	
	    	}
	    	
	    	// ensure we have minimum requirements
	    	while( count($this->leftList) < 2 )
	    	{
	    		// we need at least 2 answers !
	    		$this->addLeft();	
	    	}
	    	
	    	while( count($this->rightList) < 2 )
	    	{
	    		// we need at least 2 answers !
	    		$this->addRight();	
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
     * @author Sebastien Piraux <pir@cerdecam.be>
     * @return boolean result of operation   
     */	     
	function save() 
    {
    	$sql = "DELETE FROM `".$this->tblAnswer."` 
                WHERE `questionId` = ".(int) $this->questionId;
        
        if( claro_sql_query($sql) == false ) return false;
       
       	// inserts new answers into data base
        $sql = "INSERT INTO `".$this->tblAnswer."`(`questionId`,`answer`,`match`,`grade`,`code`)
                VALUES ";

        foreach( $this->leftList as $leftElt )
        {
            $sql .= "(".(int) $this->questionId."," 
            		."'".addslashes($leftElt['answer'])."',"
            		."'".addslashes($leftElt['match'])."',"
            		."'".addslashes($leftElt['grade'])."',"
            		."'".addslashes($leftElt['code'])."'),";
        }
        
        foreach( $this->rightList as $rightElt )
        {
        	$sql .= "(".(int) $this->questionId.","
        			."'".addslashes($rightElt['answer'])."',"
        			."NULL,"
        			."'0',"
        			."'".addslashes($rightElt['code'])."'),";
        }

        $sql = substr($sql,0,-1); // remove trailing ,
        
        return claro_sql_query($sql);
    }
    
    /**
     * delete answers from db
     *
     * @author Sebastien Piraux <pir@cerdecam.be>
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
     * @author Sebastien Piraux <pir@cerdecam.be>
     * @return boolean result of operation   
     */	    
    function duplicate()
    {
    	// TODO duplicate	
    }
    
    /**
     * check if the object content is valide (use before using save method)
     *
     * @author Sebastien Piraux <pir@cerdecam.be>
     * @return boolean result of operation   
     */	
	function validate() 
    {
    	$rightCodeList = array();

    	// check that all right values are filled
    	foreach( $this->rightList as $rightElt )
    	{
    		if( empty($rightElt['answer']) )
    		{
    			$this->errorList[] = get_lang('Please fill the two lists below');
	    		return false;	
    		}
    		$rightCodeList[] = $rightElt['code'];
    	}
    
    	foreach( $this->leftList as $leftElt )
    	{
    		// check that all left values are filled
    		if( empty($leftElt['answer']) )
    		{
    			$this->errorList[] = get_lang('Please fill the two lists below');
	    		return false;	
    		}
    		
    		// find matching code in right proposals
    		if( ! in_array($leftElt['match'], $rightCodeList) )
    		{
    			$this->errorList[] = get_lang('Invalid matching choice');
	    		return false;		
    		}	
    	}
    	
    	return true;
    }
    
    /**
     * handle the form, get data of request and put in the object, handle commands if required
     *
     * @author Sebastien Piraux <pir@cerdecam.be>
     * @return boolean true if form can be checked and saved, false   
     */
    function handleForm() 
    {
    	// reinit array
    	$this->leftList = array();
    	$this->rightList = array();
    	
    	//-- set form value in object
	    for( $i = 0; $i < $_REQUEST['rightCount']; $i++ )
		{
			$answerNumber = $i + 1;
    		
    		//-- answer text			
			$right = 'right_'.$this->questionId.'_'.$answerNumber;						
			if( isset($_REQUEST[$right]) ) 		$answer = trim($_REQUEST[$right]);
			else								$answer = '';
			
			$this->addRight($answer);
		}
		
		// we need to have a correspondance between number of the answer in the form and code of corresponding proposal
		$righCodeList = array_keys($this->rightList);
		
    	for( $i = 0; $i < $_REQUEST['leftCount']; $i++ )
		{
			$answerNumber = $i + 1;
    		
    		//-- answer text			
			$answerFieldName = 'answer_'.$this->questionId.'_'.$answerNumber;						
			if( isset($_REQUEST[$answerFieldName]) ) 	$answer = trim($_REQUEST[$answerFieldName]);
			else										$answer = '';
			
			//-- matching choice
			$matchFieldName = 'match_'.$this->questionId.'_'.$answerNumber;						
			if( isset($_REQUEST[$matchFieldName]) )
			{				
				// 'match' value is the code of the matching right answer
				if( isset($righCodeList[$_REQUEST[$matchFieldName]]) )
				{
					$match = $righCodeList[$_REQUEST[$matchFieldName]];
				}
				else
				{
					$match = '';
				}
			}
			else
			{
				$match = '';
			}
			
			//-- grade
			$gradeFieldName = 'grade_'.$this->questionId.'_'.$answerNumber;						
			if( isset($_REQUEST[$gradeFieldName]) ) $grade = (int) $_REQUEST[$gradeFieldName];
			else									$grade = '';
			
			$this->addLeft($answer, $match, $grade );    	
		}
				
    	//-- cmd
		if( isset($_REQUEST['cmdRemLeft']) )
		{	
			$this->remLeft();
    		return false;
		}
		
		if( isset($_REQUEST['cmdAddLeft']) )
		{
			$this->addLeft();
    		return false;
		}
		
		if( isset($_REQUEST['cmdRemRight']) )
		{	
			$this->remRight();
    		return false;
		}
		
		if( isset($_REQUEST['cmdAddRight']) )
		{
			$this->addRight();
    		return false;
		}

		// no special command
		return true; 
    	
    }
    
    /**
     * provide the list of error that validate found
     *
     * @author Sebastien Piraux <pir@cerdecam.be>
     * @return array list of errors   
     */	
	function getErrorList() 
    {
    	return $this->errorList;
    }

	/**
     * display the answers as a form part for display in quizz submission page
     *
     * @author Sebastien Piraux <pir@cerdecam.be>
     * @return string html code for display of answer   
     */	 
	function getAnswerHtml() 
    {
    	if( empty($this->leftList) || empty($this->rightList) )
    	{
    		$html = "\n" . '<p>' . get_lang('There is no answer for the moment') . '</p>' . "\n\n";
    	}
    	else
    	{   
    		
    		// prepare list of right proposition to allow
    		// - easiest display
    		// - easiest randomisation if needed one day 
    		// (here I use array_values to change array keys from $code1 $code2 ... to 0 1 ...)	
    		$displayedRightList = array_values($this->rightList);

			// get max length of displayed array
			$arrayLength = max( count($this->leftList), count($this->rightList) );
	
			$html = '<table width="100%">' . "\n\n";
			
			$leftCpt = 1;
			$rightCpt = 'A';
			for( $i = 0; $i < $arrayLength; $i++ ) 
			{
				if( isset($this->leftList[$i]['answer']) )
				{
					// build html option list - we have to do this here for "selected" attribute
					$optionList = array();
					$optionCpt = 'A';
					$selected = '--';
					$optionList[0] = '--';
					
					foreach( $this->rightList as $rightElt )
					{
						$optionList[$rightElt['code']] = $optionCpt;

						if( $this->leftList[$i]['response'] == $rightElt['code'] ) $selected = $rightElt['code'];
						
						$optionCpt++;		
					}

					$leftHtml = $leftCpt . '. ' . $this->leftList[$i]['answer'];
					$centerHtml = claro_html_form_select('a_'.$this->questionId.'_'.$this->leftList[$i]['code'], $optionList, $selected);	
				}
				else
				{
					$leftHtml = '&nbsp;';
					$centerHtml = '&nbsp;';
				}
				
				if( isset($displayedRightList[$i]['answer']) )
				{
					$rightHtml = $rightCpt . '. ' . $displayedRightList[$i]['answer'];
				}
				else
				{
					$rightHtml = '&nbsp;';
				}
				
				$html .= 
					'<tr>' . "\n"	
				. 	'<td valign="top" width="40%">' . "\n" . $leftHtml . "\n" . '</td>' . "\n"
	    		. 	'<td valign="top" width="20%">' . "\n" . $centerHtml . "\n" . '</td>' . "\n"	    		
	    		. 	'<td valign="top" width="40%">' . "\n" . $rightHtml . "\n" . '</td>' . "\n"
	    		.	'</tr>' . "\n\n";
				
				$leftCpt++;
				$rightCpt++;
			}

			
			$html .= 
				'</table>' . "\n"
			.	'<p><small>' . get_lang('Matching') . '</small></p>' . "\n";
    	}
    	
    	return $html;	
    }

    /**
     * display the input hidden field depending on what was submitted in exercise submit form
     *
     * @author Sebastien Piraux <pir@cerdecam.be>
     * @return string html code for display of hidden sent data  
     */	   
    function getHiddenAnswerHtml()
    {
    	$html = "\n" . '<!-- ' . $this->questionId . ' -->' . "\n";
    	
    	foreach( $this->leftList as $leftElt )
    	{
    		if( !empty($leftElt['response']) ) 
    		{
    			$html .= '<input type="hidden" name="a_'.$this->questionId.'_'.$leftElt['code'].'" value="'.$leftElt['response'].'" />' . "\n";
    		}
    	}
    	$html .= "\n" . '<!-- ' . $this->questionId . '(end) -->' . "\n";
    	return $html;
    }
        
    /**
     * display the input hidden field depending on what was submitted in exercise submit form
     *
     * @author Sebastien Piraux <pir@cerdecam.be>
     * @return string html code for display of feedback for this answer   
     */	   
    function getAnswerFeedbackHtml()
    {
    	$html = 
			'<table width="100%">' . "\n\n"
			
		.	'<tr style="font-style:italic;font-size:small;">' . "\n"
		.	'<td valign="top" width="50%">'.get_lang('Element list').'</td>' . "\n"
		.	'<td valign="top" width="50%">'.get_lang('Corresponds to').'</td>' . "\n"
		.	'</tr>' . "\n\n";   
		

		foreach( $this->leftList as $leftElt )
		{
			$html .=
				'<tr>' . "\n"
			.	'<td>'.$leftElt['answer'].'</td>' . "\n"
			.	'<td>';

			if( $leftElt['match'] == $leftElt['response'] )
    		{
   				$html .= $this->rightList[$leftElt['response']]['answer'] . ' / <span class="correct"><b>'.$this->rightList[$leftElt['match']]['answer'].'</b></span>';  
	   		}
	   		elseif( empty($leftElt['response']) )
	   		{
   			   	$html .= '&nbsp;&nbsp;&nbsp; / <span class="correct"><b>'.$this->rightList[$leftElt['match']]['answer'].'</b></span>';
	   		}
	   		else
	   		{
	   			$html .= '<span class="error">' . $this->rightList[$leftElt['response']]['answer'] . '</span> / <span class="correct"><b>'.$this->rightList[$leftElt['match']]['answer'].'</b></span>';
	   		}

			$html .=
				'</td>' . "\n"
			.	'</tr>' . "\n\n";
		}
		
		$html .=
			'</table>' . "\n"
		.	'<p><small>' . get_lang('Matching') . '</small></p>' . "\n";
    	
    	return $html;  	
    	
    }
    
    /**
     * display the form to edit answers
     *
     * @author Sebastien Piraux <pir@cerdecam.be>
     * @return string html code for display of answer edition form   
     */
    function getFormHtml($exId = null) 
    {
    	$exId = isset($_REQUEST['exId'])?$_REQUEST['exId']:'';
			
    	$html =
    		'<form method="post" action="./edit_answers?exId='.$exId.'&amp;quId='.$this->questionId.'">' . "\n"
    	.	'<input type="hidden" name="cmd" value="exEdit" />' . "\n"
    	.	'<input type="hidden" name="leftCount" value="'.count($this->leftList).'" />' . "\n"
    	.	'<input type="hidden" name="rightCount" value="'.count($this->rightList).'" />' . "\n" 
		.	'<input type="hidden" name="claroFormId" value="'.uniqid('').'">' . "\n"
    	.	'<table border="0" cellpadding="5">' . "\n\n"
    		
		. 	'<tr>' . "\n"
		.	'<td colspan="3">' . get_lang('Make correspond') . '&nbsp;:</td>' . "\n"
		.	'<td>' . get_lang('Weighting') . '&nbsp;:</td>' . "\n"
		.	'</tr>' . "\n\n";
		
		$leftCpt = 1;
		$i = 1;
		foreach( $this->leftList as $leftElt )
		{
			// build html option list - we have to do this here for "selected" attribute
			$optionList = '';
			$rightCpt = 'A';
			$j = 0;
			foreach( $this->rightList as $rightElt )
			{
				$optionList .= '<option value="'.$j.'"';
				if( $leftElt['match'] == $rightElt['code'] ) $optionList .= ' selected="selected"';
				$optionList .= '>'.$rightCpt.'</option>' . "\n";
				
				$rightCpt++;
				$j++;				
			}
			
			$html .=
			 	'<tr>' . "\n"
			.	'<td>' . $leftCpt . '.</td>' . "\n"
			.	'<td><input type="text" name="answer_'.$this->questionId.'_'.$i.'" size="58" value="'.$leftElt['answer'].'" /></td>' . "\n"
			.	'<td>'
			.	'<select name="match_'.$this->questionId.'_'.$i.'" />' . "\n" . $optionList . "\n" . '</select>' . "\n"
			.	'</td>' . "\n"
			.	'<td><input type="text" name="grade_'.$this->questionId.'_'.$i.'" size="8" value="'.$leftElt['grade'].'" /></td>' . "\n"
			.	'</tr>' . "\n\n";
				
			;
			
			$leftCpt++;
			$i++;
		}
		
		$html .=	    		
			'<tr>' . "\n"
		.	'<td colspan="4">'
		. 	'<input type="submit" name="cmdRemLeft" value="' . get_lang('Rem. elem.') . '" />&nbsp;&nbsp;'
		. 	'<input type="submit" name="cmdAddLeft" value="' . get_lang('Add elem.') . '" />'
		. 	'</td>' . "\n"
		. 	'</tr>' . "\n\n";
				
		$html .=
		 	'<tr>' . "\n"
		.	'<td colspan="4">' . get_lang('Please define the options') . '&nbsp;:</td>' . "\n"
		.	'</tr>' . "\n\n";
		
		
		$rightCpt = 'A';
		$i = 1;
		foreach( $this->rightList as $rightElt )
		{
			$html .=
			 	'<tr>' . "\n"
			.	'<td>' . $rightCpt . '.</td>' . "\n"
			.	'<td colspan="3"><input type="text" name="right_'.$this->questionId.'_'.$i.'" size="58" value="'.$rightElt['answer'].'" /></td>' . "\n"
			.	'</tr>' . "\n\n";
				
			;
			
			$rightCpt++;
			$i++;
		}
		
		$html .=	    		
			'<tr>' . "\n"
		. 	'<td colspan="4">'
		. 	'<input type="submit" name="cmdRemRight" value="' . get_lang('Rem. elem.') . '" />&nbsp;&nbsp;'
		. 	'<input type="submit" name="cmdAddRight" value="' . get_lang('Add elem.') . '" />'
		. 	'</td>' . "\n"
		. 	'</tr>' . "\n\n";
		
		$html .=	    		
			'<tr>' . "\n"
		. 	'<td colspan="4" align="center">'
		. 	'<input type="submit" name="cmdOk" value="' . get_lang('Ok') . '" />&nbsp;&nbsp;'
		. 	claro_html_button('./edit_question.php?exId='.$exId.'&amp;quId='.$this->questionId, get_lang("Cancel") )
		. 	'</td>' . "\n"
		. 	'</tr>' . "\n\n"
		.	'</table>';		
		
		return $html;
    }
    
    function getUniqueCode()
    {
    	return md5(uniqid(''));
    }
    
    /**
	 * add example content
	 * 
	 * @return boolean result of operation
	 */
    function addExample()
    {
    	$code = $this->addRight( get_lang('rich') );
    	$this->addLeft( get_lang('Your daddy is'), $code, 5 );
    	
    	$code = $this->addRight( get_lang('good looking') );
    	$this->addLeft( get_lang('Your mother is'), $code, 5 );

    	return true;
    }
    
    /**
	 * add empty answer at end of answerList
	 * 
	 * @return true
	 */ 
	function addLeft($answer = '', $match = 0, $grade = 0, $code = '', $response = '')
	{
		if( empty($code) ) $code = $this->getUniqueCode();
		
		$addedAnswer = array(
							'answer' => $answer,
							'match' => $match,
							'grade' => $grade,
							'code' => $code,
							'response' => $response
							);
							
		$this->leftList[] = $addedAnswer;
		return true;
	}

	/**
	 * remove empty answer at end of answerList
	 * 
	 * @return boolean result of operation
	 */ 	
	function remLeft()
	{
		if( count($this->leftList) > 2 )
		{
			$removedAnswer = array_pop($this->leftList);
			
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
	 * add empty option at the end of optionList
	 * 
	 * @return 'code' of inserted row
	 */ 
	function addRight($answer = '', $code = '')
	{
		if( empty($code) ) $code = $this->getUniqueCode();
		
		$addedAnswer = array(
							'answer' => $answer,
							'code' => $code
							);
							
		$this->rightList[$code] = $addedAnswer;
		
		return $code;
	}

	/**
	 * remove last right answer
	 * 
	 * @return boolean result of operation
	 */ 	
	function remRight()
	{
		if( count($this->rightList) > 2 )
		{
			$removedAnswer = array_pop($this->rightList);
			
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
     * @author Sebastien Piraux <pir@cerdecam.be>
	 * @return float question grade 
	 * @desc return score of checked answer or 0 if nothing was checked
	 */
	function gradeResponse()
	{
		$grade = 0;
		
		foreach( $this->leftList as $leftElt )
    	{
    		if( $leftElt['match'] == $leftElt['response'] )
    		{
   				$grade += $leftElt['grade'];
	   		}
    	}
	    return $grade;
	}	
		
	/** 
	 * get response of user via $_REQUEST and store it in object
	 * 
     * @author Sebastien Piraux <pir@cerdecam.be>
	 * @return boolean result of operation 
	 */
	function extractResponseFromRequest()
	{
		$answerCount = count($this->leftList);
   	
    	for( $i = 0; $i < $answerCount ; $i++ )
    	{
    		if( isset($_REQUEST['a_'.$this->questionId.'_'.$this->leftList[$i]['code']]) ) 
    		{
    			$this->leftList[$i]['response'] = $_REQUEST['a_'.$this->questionId.'_'.$this->leftList[$i]['code']];
    		}
    		else
    		{
				$this->leftList[$i]['response'] = '';    			
    		}
    	}
    	return true;
	}
		
	/** 
	 * compute grade of question from answer
	 * 
     * @author Sebastien Piraux <pir@cerdecam.be>
	 * @return float question grade 
	 */
	function getGrade()
	{
		$grade = 0; 
		
    	foreach( $this->leftList as $answer )
    	{
	    	$grade += $answer['grade'];
    	}
    	
	   	return $grade;
	} 	   
	
	//-- EXPORT
	/**
     * Export the question part as a matrix-choice, with only one possible answer per line.
     * @author Amand Tihon <amand@alrj.org>
     */
    function imsExportResponses($questionIdent)
    {
		$out = "";
        // Now, loop again, finding questions (rows)
        foreach( $this->leftList as $leftElt )
        {
            $responseIdent = $questionIdent . "_A_" . $leftElt['code'];
            $out.= '<response_lid ident="' . $responseIdent . '" rcardinality="Single" rtiming="No">' . "\n"
                 . '<material><mattext><![CDATA[' . $leftElt['answer'] . "]]></mattext></material>\n"
                 . '  <render_choice shuffle="No"><flow_label>' . "\n";
                 
            foreach( $this->rightList as $rightElt ) 
            {
                $out.= '    <response_label ident="' . $rightElt['code'] . '"><material>' . "\n"
                     . "      <mattext><![CDATA[" . $rightElt['answer'] . "]]></mattext>\n"
                     . "    </material></response_label>\n";
            }
            
            $out.= "</flow_label></render_choice></response_lid>\n";
        }
        
       return $out; 
    }
    
    /**
     * Export the response processing part
     * @author Amand Tihon <amand@alrj.org>
     */
    function imsExportProcessing($questionIdent)
    {
        $out = "";
        foreach( $this->leftList as $leftElt )
        {
            $responseIdent = $questionIdent . "_A_" . $leftElt['code'];
            $out.= '  <respcondition continue="Yes"><conditionvar>' . "\n"
                 . '    <varequal respident="' . $responseIdent . '">' . $leftElt['match'] . "</varequal>\n"
                 . '  </conditionvar><setvar action="Add">' . $leftElt['grade'] . "</setvar>\n"
                 . "  </respcondition>\n";
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
		// no feedback in this question type
        return '';
     }  
}
?>