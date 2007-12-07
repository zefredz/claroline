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
$path = dirname(__FILE__); 
include_once $path . '/../../lib/answer_multiplechoice.class.php';
include_once $path . '/../../lib/answer_truefalse.class.php';
include_once $path . '/../../lib/answer_fib.class.php';
include_once $path . '/../../lib/answer_matching.class.php';

class Ims2Question extends Question
{
    /**
     * Include the correct answer class and create answer
     */
    function setAnswer()
    {
        switch($this->type)
        {
            case 'MCUA' :
                $this->answer = new ImsAnswerMultipleChoice($this->id, false);
                break; 
            case 'MCMA' :
                $this->answer = new ImsAnswerMultipleChoice($this->id, true);   
                break;
            case 'TF' :
                $this->answer = new ImsAnswerMultipleChoice($this->id, true); 
                break;
            case 'FIB' :
                $this->answer = new ImsAnswerFillInBlanks($this->id); 
                break;
            case 'MATCHING' :
                $this->answer = new ImsAnswerMatching($this->id); 
                break;
            default :
                $this->answer = null;
                break;
        }

        return true;
    }
} 

class ImsAnswerMultipleChoice extends answerMultipleChoice
{
    /**
     * Return the XML flow for the possible answers. 
     *
     */
    function imsExportResponses($questionIdent, $questionStatment)
    {

        $out  = '    <choiceInteraction responseIdentifier="' . $questionIdent . '" >' . "\n";
        $out .= '      <prompt> ' . $questionStatment . ' </prompt>'. "\n";

        foreach ($this->answerList as $current_answer)
        {
            $out .= '      <simpleChoice identifier="answer_' . $current_answer['id'] . '" fixed="false">' . $current_answer['answer'];
            if (isset($current_answer['comment']) && $current_answer['comment'] != '')
            {
                $out .= '<feedbackInline identifier="answer_' . $current_answer['id'] . '">' . $current_answer['comment'] . '</feedbackInline>';
            }
            $out .= '</simpleChoice>'. "\n";
        }

        $out .= '    </choiceInteraction>'. "\n"; 
        return $out;
    }

    /**
     * Return the XML flow of answer ResponsesDeclaration
     *
     */
    function imsExportResponsesDeclaration($questionIdent)
    {

        if ($this->multipleAnswer == 'MCMA')  $cardinality = 'mutliple'; else $cardinality = 'single';

        $out = '  <responseDeclaration identifier="' . $questionIdent . '" cardinality="' . $cardinality . '" baseType="identifier">' . "\n";

        //Match the correct answers

        $out .= '    <correctResponse>'. "\n";

        foreach($this->answerList as $current_answer)
        {
            if ($current_answer['correct'])
            {
                $out .= '      <value>answer_'. $current_answer['id'] .'</value>'. "\n";
            }
        }
        $out .= '    </correctResponse>'. "\n";

        //Add the grading

        $out .= '    <mapping>'. "\n";

        foreach($this->answerList as $current_answer)
        {
            if (isset($current_answer['grade']))
            {
                $out .= '      <mapEntry mapKey="answer_'. $current_answer['id'] .'" mappedValue="'.$current_answer['grade'].'" />'. "\n";
            }
        }
        $out .= '    </mapping>'. "\n";

        $out .= '  </responseDeclaration>'. "\n";

        return $out;
    }
}

class ImsAnswerFillInBlanks extends answerFillInBlanks 
{
    /**
     * Export the text with missing words.
     *
     *
     */
    function imsExportResponses($questionIdent, $questionStatment)
    {
        global $charset;

        switch ($this->type)
        {
            case TEXTFIELD_FILL :
            {
                $text = $this->answerText;

                foreach ($this->answerList as $key=>$answer)
                {
                    $text = str_replace('['.$answer.']','<textEntryInteraction responseIdentifier="fill_'.$key.'" expectedLength="'.strlen($answer).'"/>', $text);
                }
                $out = $text;
            }
            break;

            case LISTBOX_FILL :
            {
                $text = $this->answerText;
 
                foreach ($this->answerList as $answerKey=>$answer)
                {

                    //build inlinechoice list

                    $inlineChoiceList = '';

                    //1-start interaction tag 

                    $inlineChoiceList .= '<inlineChoiceInteraction responseIdentifier="fill_'.$answerKey.'" >'. "\n";

                    //2- add wrong answer array

                    foreach ($this->wrongAnswerList as $choiceKey=>$wrongAnswer)
                    {
                        $inlineChoiceList .= '  <inlineChoice identifier="choice_w_'.$answerKey.'_'.$choiceKey.'">'.$wrongAnswer.'</inlineChoice>'. "\n";
                    }

                    //3- add correct answers array
                    foreach ($this->answerList as $choiceKey=>$correctAnswer)
                    {
                        $inlineChoiceList .= '  <inlineChoice identifier="choice_c_'.$answerKey.'_'.$choiceKey.'">'.$correctAnswer.'</inlineChoice>'. "\n";
                    }

                    //4- finish interaction tag

                    $inlineChoiceList .= '</inlineChoiceInteraction>';

                    $text = str_replace('['.$answer.']',$inlineChoiceList, $text);
                }
                $out = $text;

            }
            break;
        }

        return $out;
        
    }

    /**
     *
     */
    function imsExportResponsesDeclaration($questionIdent)
    {

        $out = '';

        foreach ($this->answerList as $answerKey=>$answer)
        {
            $out .= '  <responseDeclaration identifier="fill_' . $answerKey . '" cardinality="single" baseType="identifier">' . "\n";
            $out .= '    <correctResponse>'. "\n";

            if ($this->type==TEXTFIELD_FILL)
            {
                $out .= '      <value>'.$answer.'</value>'. "\n";
            }
            else
            {
                //find correct answer key to apply in manifest and output it
               
                foreach ($this->answerList as $choiceKey=>$correctAnswer)
                {
                    if ($correctAnswer==$answer)
                    {
                        $out .= '      <value>choice_c_'.$answerKey.'_'.$choiceKey.'</value>'. "\n";
                    }
                }
            }
            
            $out .= '    </correctResponse>'. "\n";
    
            if (isset($this->gradeList[$answerKey]))
            {
                $out .= '    <mapping>'. "\n";
                $out .= '      <mapEntry mapKey="'.$answer.'" mappedValue="'.$this->gradeList[$answerKey].'"/>'. "\n";
                $out .= '    </mapping>'. "\n";
            }

            $out .= '  </responseDeclaration>'. "\n";
        }

       return $out;
    }
}

class ImsAnswerMatching extends answerMatching
{
    /**
     * Export the question part as a matrix-choice, with only one possible answer per line.
     */
    function imsExportResponses($questionIdent, $questionStatment)
    {
        $maxAssociation = max(count($this->leftList), count($this->rightList));

        $out = "";

        $out .= '<matchInteraction responseIdentifier="' . $questionIdent . '" maxAssociations="'. $maxAssociation .'">'. "\n";
        $out .= $questionStatment;

        //add left column

        $out .= '  <simpleMatchSet>'. "\n";

        foreach ($this->leftList as $leftKey=>$leftElement)
        {
            $out .= '    <simpleAssociableChoice identifier="left_'.$leftKey.'" >'. $leftElement['answer'] .'</simpleAssociableChoice>'. "\n";
        }

        $out .= '  </simpleMatchSet>'. "\n";

        //add right column

        $out .= '  <simpleMatchSet>'. "\n";

        $i = 0;

        foreach($this->rightList as $rightKey=>$rightElement)
        {
            $out .= '    <simpleAssociableChoice identifier="right_'.$i.'" >'. $rightElement['answer'] .'</simpleAssociableChoice>'. "\n";
            $i++;
        }

        $out .= '  </simpleMatchSet>'. "\n";

        $out .= '</matchInteraction>'. "\n";

        return $out; 
    }

    /**
     *
     */
    function imsExportResponsesDeclaration($questionIdent)
    {
        $out =  '  <responseDeclaration identifier="' . $questionIdent . '" cardinality="single" baseType="identifier">' . "\n";
        $out .= '    <correctResponse>' . "\n";

        $gradeArray = array();

        foreach ($this->leftList as $leftKey=>$leftElement)
        {
            $i=0;
            foreach ($this->rightList as $rightKey=>$rightElement)
            {
                if( ($leftElement['match'] == $rightElement['code']))
                {
                    $out .= '      <value>left_' . $leftKey . ' right_'.$i.'</value>'. "\n";

                    $gradeArray['left_' . $leftKey . ' right_'.$i] = $leftElement['grade'];
                }
                $i++;
            }
        }
        $out .= '    </correctResponse>'. "\n";
        $out .= '    <mapping>' . "\n";
        foreach ($gradeArray as $gradeKey=>$grade)
        {
            $out .= '          <mapEntry mapKey="'.$gradeKey.'" mappedValue="'.$grade.'"/>' . "\n";
        }
        $out .= '    </mapping>' . "\n";
        $out .= '  </responseDeclaration>'. "\n";

        return $out;
    }

} 
?>