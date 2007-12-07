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
| Authors: Amand Tihon
+----------------------------------------------------------------------+
*/

/*
    Some quick notes on identifiers generation.
    The IMS format requires some blocks, like items, responses, feedbacks, to be uniquely
    identified. 
    The unicity is mandatory in a single XML, of course, but it's prefered that the identifier stays
    coherent for an entire site.
    
    Here's the method used to generate those identifiers.
    Question identifier :: "QST_" + <Question Id from the DB> + "_" + <Question numeric type>
    Response identifier :: <Question identifier> + "_A_" + <Response Id from the DB>
    Condition identifier :: <Question identifier> + "_C_" + <Response Id from the DB>
    Feedback identifier :: <Question identifier> + "_F_" + <Response Id from the DB>

/* >>>>>>>>>>>> SINGLE QUESTION EXPORT <<<<<<<<<<<< */

/*======================================
      CLAROLINE MAIN
  ======================================*/


include('question.class.php');
include('answer.class.php');

// answer types
define('UNIQUE_ANSWER',   1);
define('MULTIPLE_ANSWER', 2);
define('FILL_IN_BLANKS',  3);
define('MATCHING',        4);
define('TRUEFALSE',           5);

/*--------------------------------------------------------
      Classes
  --------------------------------------------------------*/

/**
 * An IMS/QTI item. It corresponds to a single question. 
 * This class allows export from Claroline to IMS/QTI XML format.
 * It is not usable as-is, but must be subclassed, to support different kinds of questions.
 *
 * Every start_*() and corresponding end_*(), as well as export_*() methods return a string.
 * 
 * @warning Attached files are NOT exported.
 * @author Amand Tihon <amand@alrj.org>
 */
class ImsItem
{
    var $question;
    var $question_ident;
    var $answer;

    /**
     * Constructor.
     *
     * @param $question The Question object we want to export.
     * @author Anamd Tihon
     */
     function ImsItem($question)
     {
        $this->question = $question;
        $this->answer = new Answer($question->selectId());
        $this->question_ident = "QST_" . $question->selectId() . "_" . $question->selectType();
     }
     
     /**
      * Start the XML flow.
      *
      * This opens the <item> block, with correct attributes.
      *
      * @author Amand Tihon <amand@alrj.org>
      */
      function start_item()
      {
        return '<item title="' . htmlspecialchars($this->question->selectTitle()) . '" ident="' . $this->question_ident . '">' . "\n";
      }
      
      /**
       * End the XML flow, closing the </item> tag.
       *
       * @author Amand Tihon <amand@alrj.org>
       */
      function end_item()
      {
        return "</item>\n";
      }
     
     /**
      * Create the opening, with the question itself.
      *
      * This means it opens the <presentation> but doesn't close it, as this is the role of end_presentation().
      * Inbetween, the export_responses from the subclass should have been called.
      *
      * @author Amand Tihon <amand@alrj.org>
      */
     function start_presentation()
     {
        return '<presentation label="' . $this->question_ident . '"><flow>' . "\n"
             . '<material><mattext><![CDATA[' . $this->question->selectDescription() . "]]></mattext></material>\n";
     }
     
     /**
      * End the </presentation> part, opened by export_header.
      *
      * @author Amand Tihon <amand@alrj.org>
      */
     function end_presentation()
     {
        return "</flow></presentation>\n";
     }
     
     /**
      * Start the response processing, and declare the default variable, SCORE, at 0 in the outcomes.
      * 
      * @author Amand Tihon <amand@alrj.org>
      */
     function start_processing()
     {
        return '<resprocessing><outcomes><decvar vartype="Integer" defaultval="0" /></outcomes>' . "\n";
     }
     
     /**
      * End the response processing part.
      *
      * @author Amand Tihon <amand@alrj.org>
      */
     function end_processing()
     {
        return "</resprocessing>\n";
     }
     
     /**
      * Export the feedback (comments to selected answers) to IMS/QTI
      * 
      * @author Amand Tihon <amand@alrj.org>
      */
     function export_feedback()
     {
        $out = "";
        for ($i=1; $i <= $this->answer->selectNbrAnswers(); $i++)
        {
            if ($this->answer->comment[$i])
            {
                $ident = $this->question_ident . "_F_" . $i;
                $out.= '<itemfeedback ident="' . $ident . '" view="Candidate"><flow_mat><material>' . "\n"
                    . '  <mattext><![CDATA[' . $this->answer->comment[$i] . "]]></mattext>\n"
                    . "</material></flow_mat></itemfeedback>\n";
            }
        }
        return $out;
     }
     
     /**
      * Export the question as an IMS/QTI Item.
      *
      * This is a default behaviour, some classes may want to override this.
      *
      * @param $standalone: Boolean stating if it should be exported as a stand-alone question
      * @return A string, the XML flow for an Item.
      * @author Amand Tihon <amand@alrj.org>
      */
     function export($standalone=False)
     {
        global $charset;
        $head = $foot = "";
        if ($standalone) {
            $head = '<?xml version = "1.0" encoding = "'.$charset.'" standalone = "no"?>' . "\n"
                  . '<!DOCTYPE questestinterop SYSTEM "ims_qtiasiv1p2p1.dtd">' . "\n"
                  . "<questestinterop>\n";
            $foot = "</questestinterop>\n";
        }
        return $head
               . $this->start_item() 
                . $this->start_presentation()
                    . $this->export_responses()
                . $this->end_presentation()
                . $this->start_processing()
                    . $this->export_processing()
                . $this->end_processing()
                . $this->export_feedback()
               . $this->end_item()
              . $foot;
     }     
}


/**
 * This class represents a Multiple choice, single answer, Question, intended to be exported in IMS/QTI.
 *
 * @note Has been tested with Qplayer and validated against the DTD.
 * @author Amand Tihon <amand@alrj.org>
 */
class ImsSingle extends ImsItem
{
    /**
     * Return the XML flow for the possible answers. 
     * That's one <response_lid>, containing several <flow_label>
     *
     * @author Amand Tihon <amand@alrj.org>
     */
    function export_responses()
    {
        // Opening of the response block.
        $out = '<response_lid ident="MCS_' . $this->question_ident . '" rcardinality="Single" rtiming="No"><render_choice shuffle="No">' . "\n";
        
        // Loop over answers
        for ($i = 1; $i <= $this->answer->selectNbrAnswers(); $i++)
        {
            $response_ident = $this->question_ident . "_A_" . $i;
            
            $out.= '  <flow_label><response_label ident="' . $response_ident . '"><flow_mat class="list"><material>' . "\n"
                . '    <mattext><![CDATA[' . $this->answer->selectAnswer($i) . "]]></mattext>\n"
                . "  </material></flow_mat></response_label></flow_label>\n";
        }
        $out.= "</render_choice></response_lid>\n";
        
        return $out;
    }
    
    /**
     * Return the XML flow of answer processing : a succession of <respcondition>. 
     *
     * @author Amand Tihon <amand@alrj.org>
     */
    function export_processing()
    {
        $out = '';
        
        for ($i = 1; $i <= $this->answer->selectNbrAnswers(); $i++)
        {
            $response_ident = $this->question_ident . "_A_" . $i;
            $feedback_ident = $this->question_ident . "_F_" . $i;
            $condition_ident = $this->question_ident . "_C_" . $i;
            
            $out.= '<respcondition title="' . $condition_ident . '"><conditionvar>' . "\n"
                . '  <varequal respident="MCS_' . $this->question_ident . '">' . $response_ident . '</varequal>' . "\n"
                . "  </conditionvar>\n" . '  <setvar action="Add">' . $this->answer->weighting[$i] . "</setvar>\n";
                
            // Only add references for actually existing comments/feedbacks.
            if ($this->answer->comment[$i])
            {
                $out.= '  <displayfeedback feedbacktype="Response" linkrefid="' . $feedback_ident . '" />' . "\n";
            }
            $out.= "</respcondition>\n";
        }
        return $out;
    }
}

/**
 * Represents a Multiple choice, multiple answer, Question, intended to be exported in IMS/QTI.
 *
 * @note Has been tested with Qplayer and validated against the DTD.
 * @author Amand Tihon <amand@alrj.org>
 */
class ImsMulti extends ImsItem
{
    /**
     * Return the XML flow for the possible answers. 
     * That's several <flow_label> in a <render_choice>
     *
     * @author Amand Tihon <amand@alrj.org>
     */
    function export_responses()
    {
        // Open the response block
        $out = '<response_lid ident = "MCM_' . $this->question_ident . '" rcardinality = "Multiple" rtiming = "No">' . "\n"
             . '<render_choice shuffle = "No" minnumber = "1" maxnumber = "' . $this->answer->selectNbrAnswers() . '">' . "\n";
             
        // Loop over possible answers
        for ($i = 1; $i <= $this->answer->selectNbrAnswers(); $i++)
        {
            $response_ident = $this->question_ident . "_A_" . $i;
            
            $out.= '  <flow_label><response_label ident="' . $response_ident . '"><material>' . "\n"
                 . '    <mattext><![CDATA[' . $this->answer->selectAnswer($i) . "]]></mattext>\n"
                 . "  </material></response_label></flow_label>\n";
        }
        
        $out.= "</render_choice></response_lid>\n";
        
        return $out;
    }
    
    /**
     * Return the XML flow of answer processing : a succession of <respcondition>. 
     *
     * @author Amand Tihon <amand@alrj.org>
     */
    function export_processing()
    {
        $out = "";
        
        for ($i = 1; $i <= $this->answer->selectNbrAnswers(); $i++)
        {
            $response_ident = $this->question_ident . "_A_" . $i;
            $feedback_ident = $this->question_ident . "_F_" . $i;
            $condition_ident = $this->question_ident . "_C_" . $i;
            
            $out.= '<respcondition title="' . $condition_ident . '" continue="Yes"><conditionvar>' . "\n"
                . '  <varequal respident="MCM_' . $this->question_ident . '">' . $response_ident . '</varequal>' . "\n"
                . "  </conditionvar>\n" . '  <setvar action="Add">' . $this->answer->weighting[$i] . "</setvar>\n";
            // Only add references for actually existing comments/feedbacks.
            if ($this->answer->comment[$i])
            {
                $out.= '  <displayfeedback feedbacktype="Response" linkrefid="' . $feedback_ident . '" />' . "\n";
            }
            $out.= "</respcondition>\n";
        }
        return $out;
    }
}

/**
 * This class represents a Fill In the Blanks question to be exported as IMS/QTI.
 *
 * @note Has been validated against the DTD but is not supported by Qplayer.
 * @author Amand Tihon <amand@alrj.org>
 */
class ImsFIB extends ImsItem
{
    var $word;
    var $weighting;
    
    /**
     * Export the text with missing words.
     *
     * As a side effect, it stores two lists in the class :
     * the missing words and their respective weightings.
     *
     * @author Amand Tihon <amand@alrj.org>
     */
    function export_responses()
    {
        global $charset;
    
        $out = "<flow>\n";
        
        // Separate the text from the weightings.
        list($response, $weighting) = explode('::', $this->answer->selectAnswer(1));
        
        // Save the weightings for later
        $this->weighting = explode(',',$weighting);

        $responsePart = explode(']', $response);
        $i = 0; // Used for the reference generation.
        foreach($responsePart as $part)
        {
            $response_ident = $this->question_ident . "_A_" . $i;
        
            if( strpos($part,'[') !== false )
            {
                list($rawText, $blank) = explode('[', $part);
            }
            else
            {
                $rawText = $part;
                $blank = "";
            }

            if ($rawText!="")
            {
                $out.="  <material><mattext><![CDATA[" . $rawText . "]]></mattext></material>\n";
            }
            
            if ($blank!="")
            {
                $this->word[] = $blank;
                $out.= '  <response_str ident="' . $response_ident . '" rcardinality="Single" rtiming="No">' . "\n"
                     . '    <render_fib fibtype="String" prompt="Box" encoding="' . $charset . '">' . "\n"
                     . '      <response_label ident="A"/>' . "\n"
                     . "     </render_fib>\n"
                     . "  </response_str>\n";
            }
            $i++;
        }
        $out.="</flow>\n";

        return $out;
        
    }
    
    /**
     * Exports the response processing.
     *
     * It uses the two lists build by export_responses(). This implies that export_responses MUST
     * be called before.
     *
     * @author Amand Tihon <amand@alrj.org>
     */
    function export_processing()
    {
        $out = "";
        
        for ($i=0; $i < count($this->word); $i++)
        {
            $response_ident = $this->question_ident . "_A_" . $i;
            $out.= '  <respcondition continue="Yes"><conditionvar>' . "\n"
                 . '    <varequal respident="' . $response_ident . '" case="No"><![CDATA[' . $this->word[$i] . ']]></varequal>' . "\n"
                 . '  </conditionvar><setvar action="Add">' . $this->weighting[$i] . "</setvar>\n"
                 . "  </respcondition>\n";
        }
        return $out;
    }

}

/**
 * This class represents a Matching question to be exported in IMS/QTI.
 * As this kind of question doesn't exist in QTI, we'll use a matrix based multiple choice,
 * which allows one answer per line.
 *
 * @note Has been validated against the DTD but is not supported by Qplayer.
 * @author Amand Tihon <amand@alrj.org>
 */
class ImsMatching extends ImsItem
{

    /**
     * Export the question part as a matrix-choice, with only one possible answer per line.
     * @author Amand Tihon <amand@alrj.org>
     */
    function export_responses()
    {
        // First, find the possible answers (the columns, in IMS's matrix)
        for ($i = 1; $i <= $this->answer->selectNbrAnswers(); $i++)
        {
            if (!$this->answer->isCorrect($i)) // This is an answer
            {
                $answerList[$i] = $this->answer->selectAnswer($i);
            }
        }

        $out = "";
        // Now, loop again, finding questions (rows)
        for ($i = 1; $i <= $this->answer->selectNbrAnswers(); $i++)
        {
            if ($this->answer->isCorrect($i))
            {
                $response_ident = $this->question_ident . "_A_" . $i;
                $out.= '<response_lid ident="' . $response_ident . '" rcardinality="Single" rtiming="No">' . "\n"
                     . '<material><mattext><![CDATA[' . $this->answer->selectAnswer($i) . "]]></mattext></material>\n"
                     . '  <render_choice shuffle="No"><flow_label>' . "\n";
                foreach($answerList as $ident => $answer) {
                    $out.= '    <response_label ident="' . $ident . '"><material>' . "\n"
                         . "      <mattext><![CDATA[" . $answer . "]]></mattext>\n"
                         . "    </material></response_label>\n";
                }
                $out.= "</flow_label></render_choice></response_lid>\n";
            }
        }
        
       return $out; 
    }
    
    /**
     * Export the response processing part
     * @author Amand Tihon <amand@alrj.org>
     */
    function export_processing()
    {
        $out = "";
        for ($i = 1; $i <= $this->answer->selectNbrAnswers(); $i++)
        {
            if ($correct = $this->answer->isCorrect($i))
            {
                $response_ident = $this->question_ident . "_A_" . $i;
                $out.= '  <respcondition continue="Yes"><conditionvar>' . "\n"
                     . '    <varequal respident="' . $response_ident . '">' . $correct . "</varequal>\n"
                     . '  </conditionvar><setvar action="Add">' . $this->answer->weighting[$i] . "</setvar>\n"
                     . "  </respcondition>\n";
            }
        }
        return $out;
    }
}


/*--------------------------------------------------------
      Functions
  --------------------------------------------------------*/

/**
 * Returns the XML flow corresponding to one question
 * 
 * @param int The question ID
 * @param bool standalone (ie including XML tag, DTD declaration, etc)
 * @author Amand Tihon <amand@alrj.org>
 */
function export_question($questionId, $standalone=True)
{
    $objQuestion = new Question();
    if (!$objQuestion->read($questionId))
    {
        return "";
    }
    switch($objQuestion->type)
    {
        case UNIQUE_ANSWER:
            $ims = new ImsSingle($objQuestion);
            break;
        case MULTIPLE_ANSWER:
            $ims = new ImsMulti($objQuestion);
            break;
        case FILL_IN_BLANKS:
            $ims = new ImsFIB($objQuestion);
            break;
        case MATCHING:
            $ims = new ImsMatching($objQuestion);
            break;
        case TRUEFALSE:
            $ims = new ImsSingle($objQuestion);
        /*default:
            break;*/
    }
    return $ims->export($standalone);

}

?>