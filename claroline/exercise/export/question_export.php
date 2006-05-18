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
 * @author Amand Tihon <amand@alrj.org>
 * @author Sebastien Piraux <pir@cerdecam.be>   
 *
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
        $this->answer = $question->answer;
        $this->questionIdent = "QST_" . $question->getId() ;
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
        return '<item title="' . htmlspecialchars($this->question->getTitle()) . '" ident="' . $this->questionIdent . '">' . "\n";
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
        return '<presentation label="' . $this->questionIdent . '"><flow>' . "\n"
             . '<material><mattext><![CDATA[' . $this->question->getDescription() . "]]></mattext></material>\n";
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
      * Export the question as an IMS/QTI Item.
      *
      * This is a default behaviour, some classes may want to override this.
      *
      * @param $standalone: Boolean stating if it should be exported as a stand-alone question
      * @return A string, the XML flow for an Item.
      * @author Amand Tihon <amand@alrj.org>
      */
     function export($standalone = False)
     {
        global $charset;
        $head = $foot = "";
        
        if( $standalone )
        {
            $head = '<?xml version = "1.0" encoding = "'.$charset.'" standalone = "no"?>' . "\n"
                  . '<!DOCTYPE questestinterop SYSTEM "ims_qtiasiv1p2p1.dtd">' . "\n"
                  . "<questestinterop>\n";
            $foot = "</questestinterop>\n";
        }
        
        return $head
               . $this->start_item() 
                . $this->start_presentation()
                    . $this->answer->imsExportResponses($this->questionIdent)
                . $this->end_presentation()
                . $this->start_processing()
                    . $this->answer->imsExportProcessing($this->questionIdent)
                . $this->end_processing()
                . $this->answer->imsExportFeedback($this->questionIdent)
               . $this->end_item()
              . $foot;
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
    $question = new Question();
    if( !$question->load($questionId) )
    {
        return '';
    }
    
    $ims = new ImsItem($question);
    
    return $ims->export($standalone);

}

?>
