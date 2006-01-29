<?php // $Id$
/**
 *
 * CLAROLINE 
 *
 * @version 1.8 $Revision$
 *
 * Copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
 *
 */

        /*>>>>>>>>>>>>>>>>>>>> EXERCISE TOOL LIBRARY <<<<<<<<<<<<<<<<<<<<*/

/**
 * shows a question and its answers
 *
 * @returns 'number of answers' if question exists, otherwise false
 * @param integer    $questionId        ID of the question to show
 * @param boolean    $onlyAnswers    set to true to show only answers
 */
function showQuestion($questionId, $onlyAnswers=false)
{
    global $attachedFilePathWeb;
    global $attachedFilePathSys;

    // construction of the Question object
    $objQuestionTmp = new Question();

    // reads question informations
    if(!$objQuestionTmp->read($questionId))
    {
        // question not found
        return false;
    }

    $answerType = $objQuestionTmp->selectType();
    $attachedFile = $objQuestionTmp->selectAttachedFile();

    if(!$onlyAnswers)
    {
        $questionName = $objQuestionTmp->selectTitle();
        $questionDescription = $objQuestionTmp->selectDescription();
?>

    <tr>
      <td valign="top" colspan="2">
        <?php echo $questionName; ?>
      </td>
    </tr>
    <tr>
      <td valign="top" colspan="2">
        <i><?php echo claro_parse_user_text($questionDescription); ?></i>
      </td>
    </tr>

<?php
        if(!empty($attachedFile))
        {
?>

    <tr>
      <td colspan="2"><?php echo display_attached_file($attachedFile); ?></td>
    </tr>

<?php
        }
    }  // end if(!$onlyAnswers)

    // construction of the Answer object
    $objAnswerTmp = new Answer($questionId);

    $nbrAnswers = $objAnswerTmp->selectNbrAnswers();

    // only used for the answer type "Matching"
    if($answerType == MATCHING)
    {
        $cpt1 = 'A';
        $cpt2 = 1;
        $Select = array();
    }

    for($answerId = 1;$answerId <= $nbrAnswers;$answerId++)
    {
        $answer = $objAnswerTmp->selectAnswer($answerId);
        $answerCorrect = $objAnswerTmp->isCorrect($answerId);

        if($answerType == FILL_IN_BLANKS)
        {
            // splits text and weightings that are joined with the character '::'
            $explodedAnswer = explode( '::',$answer);
            $answer = (isset($explodedAnswer[0]))?$explodedAnswer[0]:'';
            $weighting = (isset($explodedAnswer[1]))?$explodedAnswer[1]:'';
            $fillType = (!empty($explodedAnswer[2]))?$explodedAnswer[2]:1;
            // default value if value is invalid
            if( $fillType != TEXTFIELD_FILL && $fillType != LISTBOX_FILL )  $fillType = TEXTFIELD_FILL;
            $wrongAnswers = (!empty($explodedAnswer[3]))?explode('[',$explodedAnswer[3]):array();

            // according to the help type replace blanks by input or select box
            if( $fillType == LISTBOX_FILL )// listbox
            {
                // get the list of propositions to display (all good and wrong answers)
                // add wrongAnswers in the list
                $answerList = $wrongAnswers;
                // add good answers in the list

                // we save the answer because it will be modified
                $temp = $answer;
                while(1)
                {
                    // quits the loop if there are no more blanks
                    if(($pos = strpos($temp,'[')) === false)
                    {
                        break;
                    }
                    // removes characters till '['
                    $temp = substr($temp,$pos+1);
                    // quits the loop if there are no more blanks
                    if(($pos = strpos($temp,']')) === false)
                    {
                        break;
                    }
                    // stores the found blank into the array
                    $answerList[] = substr($temp,0,$pos);
                    // removes the character ']'
                    $temp = substr($temp,$pos+1);
                }
                // alphabetical sort of the array
                natcasesort($answerList);
                // replace all [blank] by a select box with all answers
                $selectBox = build_answers_select_box($answerList,$questionId);
                $answer = ereg_replace('\[[^]]+\]',$selectBox,claro_parse_user_text($answer));
            }
            else // default, fill text fields
            {
                // replaces all [blank] by an input field
                $answer = ereg_replace('\[[^]]+\]','<input type="text" name="choice['.$questionId.'][]" size="10">',claro_parse_user_text($answer));
            }
        }

        // unique answer
        if($answerType == UNIQUE_ANSWER || $answerType == TRUEFALSE)
        {
?>

    <tr>
      <td width="5%" align="center">
        <input type="radio" 
               name="choice[<?php echo $questionId; ?>]" 
               id="choice[<?php echo $questionId; ?>][<?php echo $answerId; ?>]"
               value="<?php echo $answerId; ?>">
      </td>
      <td width="95%">
        <label for="choice[<?php echo $questionId; ?>][<?php echo $answerId; ?>]">
        <?php echo $answer; ?> 
        </label>
      </td>
    </tr>

<?php
        }
        // multiple answers
        elseif($answerType == MULTIPLE_ANSWER)
        {
?>

    <tr>
      <td width="5%" align="center">
        <input type="checkbox" 
               name="choice[<?php echo $questionId; ?>][<?php echo $answerId; ?>]"
               id="choice[<?php echo $questionId; ?>][<?php echo $answerId; ?>]"
               value="1">
      </td>
      <td width="95%">
        <label for="choice[<?php echo $questionId; ?>][<?php echo $answerId; ?>]">
        <?php echo $answer; ?> 
        </label>
      </td>
    </tr>

<?php
        }
        // fill in blanks
        elseif($answerType == FILL_IN_BLANKS)
        {
?>

    <tr>
      <td colspan="2">
        <?php echo $answer; ?>
      </td>
    </tr>

<?php
        }
        // matching
        else
        {
            if(!$answerCorrect)
            {
                // options (A, B, C, ...) that will be put into the list-box
                $Select[$answerId]['Lettre']=$cpt1++;
                // answers that will be shown at the right side
                $Select[$answerId]['Reponse']=$answer;
            }
            else
            {
?>

    <tr>
      <td colspan="2">
        <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
          <td width="40%" valign="top"><?php echo '<b>'.$cpt2.'.</b> '.$answer; ?></td>
          <td width="20%" align="center">&nbsp;&nbsp;<select name="choice[<?php echo $questionId; ?>][<?php echo $answerId; ?>]">
            <option value="0">--</option>

<?php
                // fills the list-box
                foreach($Select as $key=>$val)
                {
?>

            <option value="<?php echo $key; ?>"><?php echo $val['Lettre']; ?></option>

<?php
                }  // end foreach()
?>

          </select>&nbsp;&nbsp;</td>
          <td width="40%" valign="top"><?php if(isset($Select[$cpt2])) echo '<b>'.$Select[$cpt2]['Lettre'].'.</b> '.$Select[$cpt2]['Reponse']; else echo '&nbsp;'; ?></td>
        </tr>
        </table>
      </td>
    </tr>

<?php
                $cpt2++;

                // if the left side of the "matching" has been completely shown
                if($answerId == $nbrAnswers)
                {
                    // if it remains answers to shown at the right side
                    while(isset($Select[$cpt2]))
                    {
?>

    <tr>
      <td colspan="2">
        <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
          <td width="40%">&nbsp;</td>
          <td width="20%">&nbsp;</td>
          <td width="40%" valign="top"><?php echo '<b>'.$Select[$cpt2]['Lettre'].'.</b> '.$Select[$cpt2]['Reponse']; ?></td>
        </tr>
        </table>
      </td>
    </tr>

<?php
                        $cpt2++;
                    }    // end while()
                }  // end if()
            }
        }
    }    // end for()

    // add a message to say that the user must check a single or many answerss
    if($answerType == UNIQUE_ANSWER || $answerType == TRUEFALSE)
    {
    ?>
        <tr>
        <td colspan="2"><small><?php echo get_lang('UniqueAnswer'); ?></small></td>
        </tr>
    <?php
    }
    elseif($answerType == MULTIPLE_ANSWER)
    {
    ?>
        <tr>
        <td colspan="2"><small><?php echo get_lang('MultipleAnswers'); ?></small></td>
        </tr>    
    <?php
    }
    // destruction of the Answer object
    unset($objAnswerTmp);

    // destruction of the Question object
    unset($objQuestionTmp);

    return $nbrAnswers;
}

/**
 *
 *
 *
 */
function display_attached_file($attachedFile)
{
  global $attachedFilePathWeb;
  global $attachedFilePathSys;
  
  if( !file_exists($attachedFilePathSys.'/'.$attachedFile) )return false;
  
  // get extension
  $extension = strtolower(substr(strrchr($attachedFile, '.'), 1));
  
  $returnedString = '<p>'."\n";
  switch($extension)
  {
    case 'jpg' :
    case 'jpeg' :
    case 'gif' :
    case 'png' :
    case 'bmp' :
        $returnedString .= '<img src="'.$attachedFilePathWeb.'/'.$attachedFile.'" border="0" alt="'.$attachedFile.'" />'."\n";
        break;
    /*    
    case 'mov' :
        $returnedString .= "<object>  
                      <param name=\"src\" value=\"".$attachedFilePathWeb."/".$attachedFile."\"> 
                      <param name=\"volume\" value=\"50%\">
                      <param name=\"loop\" value=\"false\">
                      <param name=\"controller\" value=\"true\">
                      <param name=\"autoplay\" value=\"false\">
                      <param name=\"type\" value=\"video/quicktime\">
                      <embed align=\"middle\" src=\"".$attachedFilePathWeb."/".$attachedFile."\" volume=\"50%\" loop=\"false\" controller=\"true\" autoplay=\"false\" type=\"video/quicktime\">
                      </embed> 
                      </object>
                      <br /><small><a href=\"".$attachedFilePathWeb."/".$attachedFile."\" target=\"_blank\">".get_lang('DownloadAttachedFile')." (.mov)</a></small>";
        break;
    */
    /*
    case 'wmv' :
        break;
    */
    case 'swf' :
        $returnedString .= '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"'
                    .' codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0"'
                    .' id="'.$attachedFile.'">'."\n"
                    .'<param name="movie" value="'.$attachedFilePathWeb.'/'.$attachedFile.'">'."\n"
                    .'<param name="quality" value="high">'."\n"
                    .'<param name="bgcolor" value="#FFFFFF">'."\n"
                    .'<embed src="'.$attachedFilePathWeb.'/'.$attachedFile.'"  quality="high" bgcolor="#FFFFFF" name="'.$attachedFile.'" type="application/x-shockwave-flash"  pluginspage="http://www.macromedia.com/go/getflashplayer">'."\n"
                    .'</embed>'."\n"
                    .'</object>'."\n";
        break;
    
    case 'mp3' :
            // get mp3 id3 tags (mainly for the bitrate that is required by the player
            include_once("mp3_id3_utils.php");
            $id3 = mp3_id($attachedFilePathSys."/".$attachedFile);

            // -1 means reading error, 0 means that the mp3 has no id3 tag
            if( $id3 == -1 || $id3 == 0 )
            {
                // if id3 tags cannot be read
                // set default bitrate 32
                $bitrate = 32;
                $mp3Title = "";
                // show filename instead of title and artist
            }
            else
            {
                $bitrate = $id3['bitrate'];

                if( !empty($id3['artist']) && !empty($id3['title']) )
                {
                    $mp3Title = $id3['artist']." - ".$id3['title'];
                }
                else
                {
                    // artist or title or both are empty
                    $mp3Title = "";
                    if( isset($id3['artist']) ) $mp3Title .= $id3['artist'];
                    if( isset($id3['title']) ) $mp3Title .= $id3['title'];
                }
            }
            $playerParams = '?file='.$attachedFilePathWeb.'/'.$attachedFile
                            .'&amp;autolaunch=false'
                            .'&amp;my_bitrate='.$bitrate
                            .'&amp;my_BackgroundColor=0xffffff'
                            .'&amp;fakeVar='.time();

            $returnedString .=
                    '<object id="mp3player" type="application/x-shockwave-flash" data="claroPlayer.swf'.$playerParams.'" width="220" height="30" style="vertical-align: bottom;">'."\n"
                    .'<!-- MP3 Flash player. Credits, license, contact & examples: http://pyg.keonox.com/flashmp3player/ -->'."\n"
                    .'<param name="type" value="application/x-shockwave-flash" />'."\n"
                    .'<param name="codebase" value="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,40,0" />'."\n"
                    .'<param name="movie" value="claroPlayer.swf'.$playerParams.'" />'."\n"
                    .'</object>'."\n"
                    .'<p>'."\n".'<small>'."\n"
                    .$mp3Title
                    .'<br /><a href="'.$attachedFilePathWeb.'/'.$attachedFile.'">'.get_lang('DownloadAttachedFile').' ('.$attachedFile.')</a>'."\n"
                    .'</small>'."\n\n"
                    ;
                          
        break;
    
    default :
        $returnedString .= '<a href="'.$attachedFilePathWeb.'/'.$attachedFile.'" target="_blank">'.get_lang('DownloadAttachedFile').'</a>'."\n";
        break;        
  
  }
  $returnedString .= '</p>'."\n";
  return $returnedString;
}

/**
 * return html code for the select box needed in fill in blanks questions
 *
 * @returns string html code of the select box
 * @param array $answerList list of answers to display in selectbox
 * @param integer    $questionId        ID of the question to show (need to be added in selectbox)
 */
function build_answers_select_box($answerList, $questionId)
{
    $selectBox = '<select name="choice['.$questionId.'][]">'."\n"
                .'<option value="">&nbsp</option>';
    
    foreach($answerList as $answer)
    {
        $selectBox .= '<option value="'.htmlspecialchars($answer).'">'.$answer.'</option>'."\n";
    }

    $selectBox .= '</select>'."\n";

    return $selectBox;
}
?>
