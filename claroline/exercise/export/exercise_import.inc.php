<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );
/**
 * CLAROLINE
 * @version 1.8
 *
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @package ADMIN
 * @since 1.8
 *
 * @author claro team <cvs@claroline.net>
 * @author Guillaume Lederer <guillaume@claroline.net>
 */

/**
 * function to create a temporary directory (SAME AS IN MODULE ADMIN)
 * concat a "unique" directory name to $dir
 *
 * @param string $dir repository for temp path
 * @param string $prefix prefix the name
 * @param integer $mode chmod of path
 * @return string new dir path
 */

function tempdir($dir, $prefix='tmp', $mode=0777)
{
    if (substr($dir, -1) != '/') $dir .= '/';

    do
    {
        $path = $dir.$prefix.mt_rand(0, 9999999);
    } while (!claro_mkdir($path, $mode));

    return $path;
}


/**
 * @return the path of the temporary directory where the exercise was uploaded and unzipped
 */

function get_and_unzip_uploaded_exercise()
{
    //Check if the file is valid (not to big and exists)

    if( !isset($_FILES['uploadedExercise'])
    || !is_uploaded_file($_FILES['uploadedExercise']['tmp_name']))
    {
        // upload failed
        return false;
    }

    //1- Unzip folder in a new repository in claroline/module

    include_once (realpath(dirname(__FILE__) . '/../../inc/lib/pclzip/') . '/pclzip.lib.php');

    //unzip files

    $tmpUploadDir = get_conf('rootSys') . get_conf('tmpPathSys') . 'upload/';
    //create temp dir for upload
    claro_mkdir($tmpUploadDir);

    $tmpExerciseDir = tempdir($tmpUploadDir); // this function should return the dir name and not the full path ...
    $uploadDir = str_replace($tmpUploadDir,'',$tmpExerciseDir); // ... because we need to remove it

    if ( preg_match('/.zip$/i', $_FILES['uploadedExercise']['name']) && treat_uploaded_file($_FILES['uploadedExercise'],$tmpUploadDir, $uploadDir, get_conf('maxFilledSpaceForExercise' , 10000000),'unzip',true))
    {

        if (!function_exists('gzopen'))
        {
            claro_delete_file($tmpExerciseDir);
            return false;
        }
        // upload successfull
        return $tmpExerciseDir;
    }
    else
    {
        claro_delete_file($tmpExerciseDir);
        return false;
    }

}
/**
 * Main function to import an exercise.
 *
 * @param string $file path of the file
 * @param array $backlog_message
 *
 * @return the id of the created exercise or false if the operation failed
 */
function import_exercise($file, &$backlog_message)
{
    global $exercise_info;
    global $element_pile;
    global $non_HTML_tag_to_avoid;
    global $record_item_body;
    //used to specify the question directory where files could be found in relation in any question
    global $questionTempDir;

    //get required table names

    $tbl_cdb_names = claro_sql_get_course_tbl();
    $tbl_quiz_exercise = $tbl_cdb_names['qwz_exercise'];
    $tbl_quiz_question = $tbl_cdb_names['qwz_question'];

    //set some default values for the new exercise

    $exercise_info   = array();
    $exercise_info['name'] = preg_replace('/.zip$/i','' ,$file);
    $exercise_info['description'] = '';
    $exercise_info['question'] = array();
    $element_pile    = array();

    //create parser and array to retrieve info from manifest

    $element_pile = array();  //pile to known the depth in which we are
    $module_info = array();   //array to store the info we need

	//if file is not a .zip, then we cancel all

	if ( !preg_match('/.zip$/i', $_FILES['uploadedExercise']['name']))
	{
		$backlog_message[] = get_lang('You must upload a zip file');
		return false;
	}

    //unzip the uploaded file in a tmp directory

    $tmpExerciseDir = get_and_unzip_uploaded_exercise();
    if( !$tmpExerciseDir )
    {
        $backlog_message[] = get_lang('Upload failed');
        return false;
    }

    //find the different manifests for each question and parse them.

    $exerciseHandle = opendir($tmpExerciseDir);

    //find each question repository in the uploaded exercise folder

    $question_number = 0;


    //1- parse the parent directory

	$file_found = false;

    $questionHandle = opendir($tmpExerciseDir);

    while( false !== ($questionFile = readdir($questionHandle)) )
    {
        if( preg_match('/.xml$/i' ,$questionFile) )
        {
            parse_file($tmpExerciseDir, '', $questionFile);
			$file_found = true;
        }//end if xml question file found
    }//end while question rep


    //2- parse every subdirectory to search xml question files

    while( false !== ($file = readdir($exerciseHandle)) )
    {
        if (is_dir($tmpExerciseDir.'/'.$file) && $file != "." && $file != "..")
        {
            //find each manifest for each question repository found

            $questionHandle = opendir($tmpExerciseDir.'/'.$file);

            while (false !== ($questionFile = readdir($questionHandle)))
            {
                if (preg_match('/.xml$/i' ,$questionFile))
                {
                    parse_file($tmpExerciseDir, $file, $questionFile);
					$file_found = true;
                }//end if xml question file found
            }//end while question rep
        } //if is_dir
    }//end while loop to find each question data's

	if( !$file_found )
	{
		$backlog_message[] = get_lang('No XML file found in the zip');
		return false;
	}

    //---------------------
    //add exercise in tool
    //---------------------

    //1.create exercise

    $exercise = new Exercise();

    $exercise->setTitle($exercise_info['name']);
    $exercise->setDescription($exercise_info['description']);

    if ($exercise->validate())
    {
        $exercise_id = $exercise->save();
    }
    else
    {
        $backlog_message[] = get_lang('There is an error in exercise data of imported file.');
    }

    //For each question found...

    foreach($exercise_info['question'] as $key => $question_array)
    {
        //2.create question

        $question = new Ims2Question();

        if (isset($question_array['title'])) $question->setTitle($question_array['title']);
        if (isset($question_array['statement'])) $question->setDescription($question_array['statement']);
        $question->setType($question_array['type']);

        if ($question->validate())
        {
            $question_id = $question->save();

            if ($question_id)
            {
                //3.create answers
                $question->setAnswer();
                $question->import($exercise_info['question'][$key], $exercise_info['question'][$key]['tempdir']);
                $exercise->addQuestion($question_id);
                $question->answer->save();
                $question->setGrade($question->answer->getGrade());
                $question->save();
            }
            else
            {
                $backlog_message[] = get_lang('Cannot save question.');
                return false;
            }
        }
        else
        {
            $backlog_message[] = get_lang('There is an error in exercise data of imported file.');
            return false;
        }
    }

    // delete the temp dir where the exercise was unzipped
    claro_delete_file($tmpExerciseDir);

    $backlog_message[] = get_lang('Import successfull');
    return $exercise_id;
}

/**
 * parse an xml file to find info
 *
 * @param unknown_type $exercisePath
 * @param unknown_type $file
 * @param unknown_type $questionFile
 * @return unknown
 */

function parse_file($exercisePath, $file, $questionFile)
{
    global $exercise_info;
    global $element_pile;
    global $non_HTML_tag_to_avoid;
    global $record_item_body;

    $questionTempDir = $exercisePath.'/'.$file.'/';
    $questionFilePath = $questionTempDir.$questionFile;
    $backlog_message = array();
    array_push ($backlog_message, "* ".$questionFile);

    if (!($fp = @fopen($questionFilePath, 'r')))
    {
        array_push ($backlog_message, get_lang("Error opening question's XML file"));
        return $backlog_message;
    }
    else
    {
        $data = fread($fp, filesize( $questionFilePath));
    }

    //parse XML question file

    //used global variable start values declaration :

    $record_item_body = false;
    $non_HTML_tag_to_avoid = array(
    "SIMPLECHOICE",
    "CHOICEINTERACTION",
    "INLINECHOICEINTERACTION",
    "INLINECHOICE",
    "SIMPLEMATCHSET",
    "SIMPLEASSOCIABLECHOICE",
    "TEXTENTRYINTERACTION",
    "FEEDBACKINLINE",
    "MATCHINTERACTION",
    "ITEMBODY",
    "BR",
    "IMG"
    );

    //this array to detect tag not supported by claroline import in the xml file to warn the user.

    $non_supported_content_in_question = array(
    "GAPMATCHINTERACTION",
    "EXTENDEDTEXTINTERACTION",
    "HOTTEXTINTERACTION",
    "HOTSPOTINTERACTION",
    "SELECTPOINTINTERACTION",
    "GRAPHICORDERINTERACTION",
    "GRAPHICASSOCIATIONINTERACTION",
    "GRAPHICGAPMATCHINTERACTION",
    "POSITIONOBJECTINTERACTION",
    "SLIDERINTERACTION",
    "DRAWINGINTERACTION",
    "UPLOADINTERACTION",
    "RESPONSECONDITION",
    "RESPONSEIF"
    );
    $question_format_supported = true;

    $xml_parser = xml_parser_create();
    xml_set_element_handler($xml_parser, 'startElement', 'endElement');
    xml_set_character_data_handler($xml_parser, 'elementData');

    if (!xml_parse($xml_parser, $data, feof($fp)))
    {
    // if reading of the xml file in not successfull :
    // set errorFound, set error msg, break while statement

        array_push ($backlog_message, get_lang('Error reading XML file') );
        return $backlog_message;
    }

    //close file

    fclose($fp);

    if ($question_format_supported)
    {
        array_push ($backlog_message, get_lang('Question format found') );
    }
    else
    {
        array_push ($backlog_message, get_lang('ERROR in:<b>'.$questionFile.'</b> Question format unknown') );
    }
}


/**
 * Function used by the SAX xml parser when the parser meets a opening tag
 *
 * @param unknown_type $parser xml parser created with "xml_parser_create()"
 * @param unknown_type $name name of the element
 * @param unknown_type $attributes
 */

function startElement($parser, $name, $attributes)
{
    global $element_pile;
    global $exercise_info;
    global $current_question_ident;
    global $current_answer_id;
    global $current_match_set;
    global $currentAssociableChoice;
    global $current_question_item_body;
    global $record_item_body;
    global $non_HTML_tag_to_avoid;
    global $current_inlinechoice_id;
    global $cardinality;
    global $questionTempDir;

    foreach( $attributes as $key => $value)
    {
        $attributes[$key] = utf8_decode_if_is_utf8($value);
    }

    array_push($element_pile,$name);
    $current_element = end($element_pile);
    if (sizeof($element_pile)>=2) $parent_element        = $element_pile[sizeof($element_pile)-2]; else $parent_element = "";
    if (sizeof($element_pile)>=3) $grant_parent_element  = $element_pile[sizeof($element_pile)-3]; else $grant_parent_element ="";

    if ($record_item_body)
    {

        if ((!in_array($current_element,$non_HTML_tag_to_avoid)))
        {
            $current_question_item_body .= "<".$name;

            foreach ($attributes as $attribute_name => $attribute_value)
            {
                $current_question_item_body .= " ".$attribute_name."=\"".$attribute_value."\"";
            }
            $current_question_item_body .= ">";
        }
        else
        {
            //in case of FIB question, we replace the IMS-QTI tag b y the correct answer between "[" "]",
            //we first save with claroline tags ,then when the answer will be parsed, the claroline tags will be replaced

            if ($current_element=='INLINECHOICEINTERACTION')
            {

                  $current_question_item_body .="**claroline_start**".$attributes['RESPONSEIDENTIFIER']."**claroline_end**";
            }
            if ($current_element=='TEXTENTRYINTERACTION')
            {
                $correct_answer_value = $exercise_info['question'][$current_question_ident]['correct_answers'][$current_answer_id];
                $current_question_item_body .= "[".$correct_answer_value."]";

            }
            if ($current_element=='BR')
            {
                $current_question_item_body .= "<BR/>";
            }
        }

    }

    switch ($current_element)
    {
        case 'ASSESSMENTITEM' :
        {
            //retrieve current question

			$current_question_ident = $attributes['IDENTIFIER'];
            $exercise_info['question'][$current_question_ident] = array();
            $exercise_info['question'][$current_question_ident]['answer'] = array();
            $exercise_info['question'][$current_question_ident]['correct_answers'] = array();
            $exercise_info['question'][$current_question_ident]['title'] = $attributes['TITLE'];
            $exercise_info['question'][$current_question_ident]['tempdir'] = $questionTempDir;
        }
        break;

        case 'SECTION' :
        {
         	//retrieve exercise name

			$exercise_info['name'] = $attributes['TITLE'];

        }
		break;

		case 'RESPONSEDECLARATION' :
        {
         	//retrieve question type

			if ( "multiple" == $attributes['CARDINALITY'])
			{
				$exercise_info['question'][$current_question_ident]['type'] = 'MCMA';
                $cardinality = 'multiple';
			}
			if ( "single" == $attributes['CARDINALITY'])
			{
				$exercise_info['question'][$current_question_ident]['type'] = 'MCUA';
                $cardinality = 'single';
			}

            //needed for FIB

            $current_answer_id = $attributes['IDENTIFIER'];

        }
		break;

        case 'INLINECHOICEINTERACTION' :
        {
            $exercise_info['question'][$current_question_ident]['type'] = 'FIB';
            $exercise_info['question'][$current_question_ident]['subtype'] = 'LISTBOX_FILL';
            $current_answer_id = $attributes['RESPONSEIDENTIFIER'];

        }
        break;

        case 'INLINECHOICE' :
        {
            $current_inlinechoice_id = $attributes['IDENTIFIER'];
        }
        break;

        case 'TEXTENTRYINTERACTION' :
        {
            $exercise_info['question'][$current_question_ident]['type'] = 'FIB';
            $exercise_info['question'][$current_question_ident]['subtype'] = 'TEXTFIELD_FILL';
            $exercise_info['question'][$current_question_ident]['response_text'] = $current_question_item_body;

            //replace claroline tags

        }
        break;

        case 'MATCHINTERACTION' :
        {
			//retrieve question type

            $exercise_info['question'][$current_question_ident]['type'] = 'MATCHING';
        }
        break;

        case 'SIMPLEMATCHSET' :
        {
            if (!isset($current_match_set))
            {
                $current_match_set = 1;
            }
            else
            {
                $current_match_set++;
            }
            $exercise_info['question'][$current_question_ident]['answer'][$current_match_set] = array();
        }
        break;

        case 'SIMPLEASSOCIABLECHOICE' :
        {
            $currentAssociableChoice = $attributes['IDENTIFIER'];
        }
        break;

        //retrieve answers id for MCUA and MCMA questions

        case 'SIMPLECHOICE':
        {
            $current_answer_id = $attributes['IDENTIFIER'];
            if (!isset($exercise_info['question'][$current_question_ident]['answer'][$current_answer_id]))
            {
                $exercise_info['question'][$current_question_ident]['answer'][$current_answer_id] = array();
            }
        }
        break;

        case 'MAPENTRY':
        {
            if ($parent_element == "MAPPING")
            {
                $answer_id = $attributes['MAPKEY'];

                if (!isset($exercise_info['question'][$current_question_ident]['weighting']))
                {
                    $exercise_info['question'][$current_question_ident]['weighting'] = array();
                }
                $exercise_info['question'][$current_question_ident]['weighting'][$answer_id] = $attributes['MAPPEDVALUE'];
            }
        }
        break;

        case 'MAPPING':
        {
            if (isset($attributes['DEFAULTVALUE']))
            {
                $exercise_info['question'][$current_question_ident]['default_weighting'] = $attributes['DEFAULTVALUE'];
            }
        }

        case 'ITEMBODY':
        {
            $record_item_body = true;
            $current_question_item_body = '';
        }
        break;

        case 'IMG' :
        {
            $exercise_info['question'][$current_question_ident]['attached_file_url'] =  $attributes['SRC'];
        }
        break;
    }
}

/**
 * Function used by the SAX xml parser when the parser meets a closing tag
 *
 * @param $parser xml parser created with "xml_parser_create()"
 * @param $name name of the element
 */

function endElement($parser,$name)
{
    global $element_pile;
    global $exercise_info;
	global $current_question_ident;
    global $record_item_body;
    global $current_question_item_body;
    global $non_HTML_tag_to_avoid;
    global $cardinality;

	$current_element = end($element_pile);

    //treat the record of the full content of itembody tag :

    if ($record_item_body && (!in_array($current_element,$non_HTML_tag_to_avoid)))
    {
        $current_question_item_body .= "</".$name.">";
    }

    switch ($name)
    {
        case 'ITEMBODY':
            {
                $record_item_body = false;
                if ($exercise_info['question'][$current_question_ident]['type']=='FIB')
                {
                    $exercise_info['question'][$current_question_ident]['response_text'] = $current_question_item_body;
                }
                else
                {
                    $exercise_info['question'][$current_question_ident]['statement'] = $current_question_item_body;
                }
            }
        break;
    }
    array_pop($element_pile);

}

function elementData($parser,$data)
{

    global $element_pile;
    global $exercise_info;
	global $current_question_ident;
    global $current_answer_id;
    global $current_match_set;
    global $currentAssociableChoice;
    global $current_question_item_body;
    global $record_item_body;
    global $non_HTML_tag_to_avoid;
    global $current_inlinechoice_id;
    global $cardinality;

    $data = trim(utf8_decode_if_is_utf8($data));
	
    $current_element       = end($element_pile);
	if (sizeof($element_pile)>=2) $parent_element        = $element_pile[sizeof($element_pile)-2]; else $parent_element = "";
	if (sizeof($element_pile)>=3) $grant_parent_element  = $element_pile[sizeof($element_pile)-3]; else $grant_parent_element = "";

	//treat the record of the full content of itembody tag (needed for question statment and/or FIB text:

    if ($record_item_body && (!in_array($current_element,$non_HTML_tag_to_avoid)))
    {
        $current_question_item_body .= $data;
    }

    switch ($current_element)
    {
        case 'SIMPLECHOICE':
        {
            if (!isset($exercise_info['question'][$current_question_ident]['answer'][$current_answer_id]['value']))
            {
                $exercise_info['question'][$current_question_ident]['answer'][$current_answer_id]['value'] = trim($data);
            }
            else
            {
                $exercise_info['question'][$current_question_ident]['answer'][$current_answer_id]['value'] .= ' '.trim($data);
            }
        }
        break;

        case 'FEEDBACKINLINE' :
        {
            if (!isset($exercise_info['question'][$current_question_ident]['answer'][$current_answer_id]['feedback']))
            {
                $exercise_info['question'][$current_question_ident]['answer'][$current_answer_id]['feedback'] = trim($data);
            }
            else
            {
                $exercise_info['question'][$current_question_ident]['answer'][$current_answer_id]['feedback'] .= ' '.trim($data);
            }
        }
        break;

        case 'SIMPLEASSOCIABLECHOICE' :
        {
            $exercise_info['question'][$current_question_ident]['answer'][$current_match_set][$currentAssociableChoice] = trim($data);
        }
        break;

        case 'VALUE':
        {
            if ($parent_element=="CORRECTRESPONSE")
            {
                if ($cardinality=="single")
                {
                    $exercise_info['question'][$current_question_ident]['correct_answers'][$current_answer_id] = $data;
                }
                else
                {
                    $exercise_info['question'][$current_question_ident]['correct_answers'][] = $data;
                }
            }
        }
        break;

        case 'ITEMBODY' :
        {
            $current_question_item_body .= $data;

        }
        break;

        case 'INLINECHOICE' :
        {

            // if this is the right answer, then we must replace the claroline tags in the FIB text bye the answer between "[" and "]" :

            $answer_identifier = $exercise_info['question'][$current_question_ident]['correct_answers'][$current_answer_id];

            if ($current_inlinechoice_id == $answer_identifier)
            {

                $current_question_item_body = str_replace("**claroline_start**".$current_answer_id."**claroline_end**", "[".$data."]", $current_question_item_body);
            }
            else // save wrong answers in an array
            {
                if(!isset($exercise_info['question'][$current_question_ident]['wrong_answers']))
                {
                    $exercise_info['question'][$current_question_ident]['wrong_answers'] = array();
                }
                $exercise_info['question'][$current_question_ident]['wrong_answers'][] = $data;
            }
        }
        break;
    }
}

?>
