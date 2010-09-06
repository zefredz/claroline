<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );
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
 
class Question 
{
    /**
     * @var $id id of question, -1 if question doesn't exist already
     */
    var $id;
    
    /**
     * @var $title name of the question
     */
    var $title;
    
    /**
     * @var $description statement of the question
     */
    var $description;
    
    /**
     * @var $attachment attached file 
     */
    var $attachment;
    
    /**
     * @var $type MCUA (multiple choice unique answer), MCMA (mc multiple answer), 
     * TF (true/false), FIB (fill in blanks) or MATCHING
     */
    var $type;
    
    /**
     * @var $grade grade of the question
     */
    var $grade;
    
    /**
     * @var $questionDirSys
     */
    var $questionDirSys;

    /**
     * @var $questionDirWeb
     */
    var $questionDirWeb;
    
    /**
     * @var $answer answer object 
     */
    var $answer;
    
    /**
     * @var $exerciseId parent exercise id of the current question (optional)
     */
    var $exerciseId;
    
    /**
     * @var $tmpQuestionDirSys use for attachment upload on question creation 
     */
    var $tmpQuestionDirSys;
            
    /**
     * @var $tblQuestion
     */
    var $tblQuestion;  
        
    /**
     * @var $tblRelExerciseQuestion
     */
    var $tblRelExerciseQuestion;  
    
    /**
     * @var $rank
     */
    var $rank;
    
    function Question($course_id = null)
    {
        global $_course;
        
        $this->id = (int) -1;
        $this->title = '';
        $this->description = '';
        $this->attachment = '';
        $this->type = 'MCMA';
        $this->grade = 0;
        
        $this->answer = null;
        
        $this->exerciseId = null;
        
        $this->questionDirSys = '';
        $this->questionDirWeb = '';
        
        $this->tmpQuestionDirSys = get_conf('coursesRepositorySys').$_course['path'].'/'.'exercise/tmp'.uniqid('').'/';

        $tbl_cdb_names = get_module_course_tbl( array( 'qwz_question', 'qwz_rel_exercise_question' ), $course_id );
        $this->tblQuestion = $tbl_cdb_names['qwz_question'];
        $this->tblRelExerciseQuestion = $tbl_cdb_names['qwz_rel_exercise_question'];
    }    
    
    /**
     * load an question from DB 
     *
     * @author Sebastien Piraux <pir@cerdecam.be>
     * @param integer $id id of question
     * @return boolean load successfull ?
     */   
    function load($id)
    {            
        $sql = "SELECT
                    `id`,
                    `title`,
                    `description`,
                    `attachment`,
                    `type`,
                    `grade`
            FROM `".$this->tblQuestion."`
            WHERE `id` = ".(int) $id;
    
        $data = claro_sql_query_get_single_row($sql);
    
        if( !empty($data) )
        {
            // from query
            $this->id = (int) $data['id'];
            $this->title = $data['title'];
            $this->description = $data['description'];
            $this->attachment = $data['attachment'];
            $this->type = $data['type'];
            $this->grade = $data['grade'];
            
            // create answer object
            $this->setAnswer();
                        
            if( !is_null($this->answer) )
            {
                $this->answer->load();
            }
                        
            $this->buildDirPaths();
            
            return true;
        }
        else
        {
            return false;
        }
    }    

    /**
     * save question to DB
     *
     * @author Sebastien Piraux <pir@cerdecam.be>
     * @return mixed false or id of the record
     */   
    function save()
    {        
        if( $this->id == -1 )
        {
            // insert    
            $sql = "INSERT INTO `".$this->tblQuestion."`
                    SET `title` = '".claro_sql_escape($this->title)."',
                        `description` = '".claro_sql_escape($this->description)."',
                        `attachment` = '".claro_sql_escape($this->attachment)."',
                        `type` = '".claro_sql_escape($this->type)."',
                        `grade` = '".claro_sql_escape($this->grade)."'";
        
            // execute the creation query and get id of inserted assignment
            $insertedId = claro_sql_query_insert_id($sql);
        
            if( $insertedId )
            {
                $this->id = (int) $insertedId;
                
                $this->buildDirPaths();                
                
                // create the question directory if query was successfull and dir not already exists
                if( !is_dir( $this->questionDirSys ) ) claro_mkdir( $this->questionDirSys , CLARO_FILE_PERMISSIONS );
                
                // move attachment 
                // if there is one from tmp directory to the the question directory
                // and delete tmp directory
                $this->moveAttachment();
                                                    
                return $this->id;
            }
            else
            {
                return false;
            }
        }
        else
        {
            // update
            // never update the type of the exercise !
            $sql = "UPDATE `".$this->tblQuestion."`
                    SET `title` = '".claro_sql_escape($this->title)."',
                        `description` = '".claro_sql_escape($this->description)."',
                        `attachment` = '".claro_sql_escape($this->attachment)."',
                        `grade` = '".claro_sql_escape($this->grade)."'
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
     * check if data are valide
     *
     * @author Sebastien Piraux <pir@cerdecam.be>
     * @return boolean 
     */    
    function validate()
    {
        // title is a mandatory element
        $title = strip_tags($this->title);

        if( empty($title) )
        {
            claro_failure::set_failure('question_no_title');
            return false;
        }
        
        return true; // no errors, form is valide
    }   
        
    /**
     * delete question from DB
     *
     * @author Sebastien Piraux <pir@cerdecam.be>
     * @return boolean 
     */
    function delete()
    {
        // delete question from all exercises
        $sql = "DELETE FROM `".$this->tblRelExerciseQuestion."`
                WHERE `questionId` = '".(int) $this->id."'";
                
        if( !claro_sql_query($sql) ) return false;
                
        // TODO delete answers
        if( !$this->answer->delete() ) return false;
                
        // delete question
        $sql = "DELETE FROM `".$this->tblQuestion."`
                WHERE `id` = '".(int) $this->id."'";
                
        if( !claro_sql_query($sql) ) return false;
        
        
        // delete attachment
        if( !$this->deleteAttachment() ) return false;
                        
        // remove question directory
        if( !claro_delete_file($this->questionDirSys) ) return false;
                
        $this->id = -1;
            
        return true;
    }    

    /**
     * duplicate question from DB
     *
     * @author Sebastien Piraux <pir@cerdecam.be>
     * @return object duplicated question 
     */
    function duplicate()
    {
        // question
        $duplicated = new Question();
        $duplicated->setTitle($this->title);
        $duplicated->setDescription($this->description);
        $duplicated->setType($this->type);
        $duplicated->setGrade($this->grade);

        $duplicatedId = $duplicated->save();

        // attachment need to be copied in the correct repository but for that we need the id
        if( !empty($this->attachment) && file_exists($this->questionDirSys.$this->attachment) )
        {
            $duplicated->copyAttachment($this->questionDirSys.$this->attachment);
        }
        // else $duplicated->attachment keeps its default value

        // and its answers
        $duplicated->answer = $this->answer->duplicate($duplicatedId);

        return $duplicated;
    }
    
         
    /**
     * builds required paths and sets values in $questionDirSys and $questionDirWeb
     *
     * @author Sebastien Piraux <pir@cerdecam.be>
     */
    function buildDirPaths()
    {
        global $_course;
        
        $this->questionDirSys = get_conf('coursesRepositorySys').$_course['path'].'/'.'exercise/question_'.$this->rank.'/';
        $this->questionDirWeb = get_conf('coursesRepositoryWeb').$_course['path'].'/'.'exercise/question_'.$this->rank.'/';            
    }    

    /**
     * set attachment value and move uploaded image to a temporary file
     *
     * @author Sebastien Piraux <pir@cerdecam.be>
     */
    function setAttachment($file)
    {
        // remove the previous file if there was one 
        $this->deleteAttachment();
        
        $filename = $file['name'] . add_extension_for_uploaded_file($file);
        $filename = replace_dangerous_char($filename);    
        $filename = get_secure_file_name($filename);
                
        // if creation we use tmp directory
        if( $this->id == -1 )     $dir = $this->tmpQuestionDirSys;
        else                    $dir = $this->questionDirSys; 
    
        // be sure that directory exists
        if( !is_dir( $dir ) )
        {
            // create it 
            if( !claro_mkdir($dir, CLARO_FILE_PERMISSIONS) )
            {
                claro_failure::set_failure('cannot_create_tmp_dir');
                return false;
            }
        }
    
        // put file in directory
        if( move_uploaded_file($file['tmp_name'], $dir.$filename) )
        {
            chmod($dir.$filename, CLARO_FILE_PERMISSIONS);
        }
        else
        {
            claro_failure::set_failure('question_upload_failed');
            return false;
        }
    
        $this->attachment = $filename;

        return true;        
    }    

    /**
     * 
     *
     * @author Sebastien Piraux <pir@cerdecam.be>
     */    
    function moveAttachment()
    {
        if( !empty($this->attachment) && !empty($this->tmpQuestionDirSys) )
        {
            if( claro_move_file($this->tmpQuestionDirSys.$this->attachment, $this->questionDirSys.$this->attachment) )
            {
                claro_delete_file($this->tmpQuestionDirSys);
                $this->tmpQuestionDirSys = '';
                return true;    
            }
            else
            {
                return false;    
            }
            
        }    
        return true;
    }
    
    /**
     * try to remove the attachment if there is one
     *
     * @author Sebastien Piraux <pir@cerdecam.be>
     */
    function deleteAttachment()
    {
        if( !empty($this->attachment) && file_exists($this->questionDirSys.$this->attachment) )
        {
            if( unlink($this->questionDirSys.$this->attachment) )
            {
                $this->attachment = '';    
                return true;
            }        
            else
            {
                return false;    
            }
        }
        return true;                      
    }    
    
    /*
    * copy a file as the attachment of the question
    *
    * @author Sebastien Piraux <pir@cerdecam.be>
    */
    function copyAttachment($sourceFile)
    {
        if( !empty( $this->questionDirSys ) && file_exists($sourceFile) ) 
        {        
            // delete current attachment
            $this->deleteAttachment();
            
            $this->attachment = basename($sourceFile);
            
            if( claro_copy_file($sourceFile, $this->questionDirSys) )
            {
                return true;
            }
            else
            {
                $this->attachment = '';
                return false;
            }
        }
        else
        {
            return false;
        }
    }

    /*
    * copy a file as the attachment of the question
    *
    * @author Sebastien Piraux <pir@cerdecam.be>
    */
    
    function getAttachmentUrl()
    {
        $url = get_conf('urlAppend') . '/claroline/exercise/get_attachment.php?id='
            . 'download'
            . '_' . $this->id
            . '_' . $this->exerciseId
            . '_' . rand(0,1000) ;

        return $url;
    }
    
    /**
     * get html required to display the question
     *
     * @author Sebastien Piraux <pir@cerdecam.be>
     * @param string $value   
     */
    function getQuestionAnswerHtml()
    {
        $html = $this->getQuestionHtml();
        
        if( is_object($this->answer) ) 
        {
            $html .= $this->answer->getAnswerHtml();
        }
                
        return $html;
    }

    /**
     * get html required to display the question
     *
     * @author Sebastien Piraux <pir@cerdecam.be>
     * @param string $value   
     */    
    function getQuestionHtml($exerciseId = null)
    {
        $html = '<p>'
        .   '<strong>'.$this->title.'</strong>' . "\n"
        .   '</p>' . "\n"
        .   '<blockquote>' . "\n" . claro_parse_user_text($this->description) . "\n" . '</blockquote>' . "\n\n";
        
        if( !empty($this->attachment) ) 
        {
            $html .= claro_html_media_player($this->questionDirWeb.$this->attachment,$this->getAttachmentUrl());
        }
       
        return $html;    
    }
    
    /**
     * get html required to display the question
     *
     * @author Sebastien Piraux <pir@cerdecam.be>
     * @param string $value   
     */
 
    function getQuestionFeedbackHtml()
    {
        $html = $this->getQuestionHtml();
        
        $html .= $this->answer->getAnswerFeedbackHtml();
       
        return $html;    
    }
      
    /**
     * get id
     *
     * @author Sebastien Piraux <pir@cerdecam.be>
     * @return integer   
     */     
    function getId()
    {
        return (int) $this->id;        
    }
            
    /**
     * get title
     *
     * @author Sebastien Piraux <pir@cerdecam.be>
     * @return string   
     */     
    function getTitle()
    {
        return $this->title;        
    }

   
    /**
     * set title
     *
     * @author Sebastien Piraux <pir@cerdecam.be>
     * @param string $value   
     */         
    function setTitle($value)
    {
        $this->title = trim($value);    
    }
    
    /**
     * get description
     *
     * @author Sebastien Piraux <pir@cerdecam.be>
     * @return string   
     */      
    function getDescription()
    {
        return $this->description;    
    }
    
    /**
     * set description
     *
     * @author Sebastien Piraux <pir@cerdecam.be>
     * @param string $value   
     */    
    function setDescription($value)
    {
        $this->description = trim($value);    
    }
    
    /**
     * get attachment
     *
     * @author Sebastien Piraux <pir@cerdecam.be>
     * @return string   
     */      
    function getAttachment()
    {
        return $this->attachment;    
    }

    /**
     * get type ('VISIBLE', 'INVISIBLE')
     *
     * @author Sebastien Piraux <pir@cerdecam.be>
     * @return string   
     */          
    function getType()
    {
        return $this->type;
    }
    
    /**
     * set type
     *
     * @author Sebastien Piraux <pir@cerdecam.be>
     * @param string $value   
     */
    function setType($value)
    {
        $acceptedValues = array('MCUA', 'MCMA', 'TF', 'FIB', 'MATCHING');
        
        if( in_array($value, $acceptedValues) )
        {
            $this->type = $value;
            return true;    
        }
        return false;
    }
    
    /**
     * get grade
     *
     * @author Sebastien Piraux <pir@cerdecam.be>
     * @return float   
     */      
    function getGrade()
    {
        return $this->grade;    
    }
    
    /**
     * set grade
     *
     * @author Sebastien Piraux <pir@cerdecam.be>
     * @param float $value   
     */    
    function setGrade($value)
    {
        $this->grade = castToFloat($value);    
    }
    
    /**
     * get the full systeme path of the attachment directory
     *
     * @author Sebastien Piraux <pir@cerdecam.be>
     * @return string    
     */     
    function getQuestionDirSys()
    {
        return $this->questionDirSys;
    }
    
    /**
     * get the full web path of the attachment directory
     *
     * @author Sebastien Piraux <pir@cerdecam.be>
     * @return string    
     */         
    function getQuestionDirWeb()
    {
        return $this->questionDirWeb;    
    }
    
    /**
     * Include the correct answer class and create answer
     */
    function setAnswer()
    {
        $path = dirname(__FILE__);

        switch($this->type)
        {
            case 'MCUA' :
                include_once $path . '/answer_multiplechoice.class.php';
                $this->answer = new answerMultipleChoice($this->id, false);
                break; 
            case 'MCMA' :
                include_once $path . '/answer_multiplechoice.class.php';
                $this->answer = new answerMultipleChoice($this->id, true);    
                break;
            case 'TF' :
                include_once $path . '/answer_truefalse.class.php';
                $this->answer = new answerTrueFalse($this->id); 
                break;
            case 'FIB' :
                include_once $path . '/answer_fib.class.php';
                $this->answer = new answerFillInBlanks($this->id); 
                break;
            case 'MATCHING' :
                include_once $path . '/answer_matching.class.php';
                $this->answer = new answerMatching($this->id); 
                $this->answer->addExample();
                break;
            default :
                $this->answer = null;
                break;
        }

        return true;
    }

    /**
     * get exercise parent id of the current question
     *
     * @return string   
     */     
    function getExerciseId()
    {
        return $this->exerciseId;        
    }

   
    /**
     * set exercise parent id of the current question 
     *
     * @author Sebastien Piraux <pir@cerdecam.be>
     * @param string $value   
     */         
    function setExerciseId($value)
    {
        $this->exerciseId = (int) $value;
    }
    
    /**
     * get question rank
     * @return int $rank
     */
    public function getRank()
    {
        return $this->rank;
    }
    
    /**
     * set question rank
     * @param int $rank
     * @return boolean
     */
    public function setRank( $rank )
    {
        return $this->rank = (int) $rank;
    }
}
?>
