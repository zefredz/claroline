<?php // $Id$

/**
 * CLAROLINE
 *
 * @version     Claroline 1.12 $Revision$
 * @copyright   (c) 2001-2014, Universite catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @see         http://www.claroline.net/wiki/config_def/
 * @package     kernel.editor
 * @author      Claro Team <cvs@claroline.net>
 * @author      Sebastien Piraux <pir@cerdecam.be>
 */
 
/**
 * Class to manage htmlarea overring simple textarea html
 */
class GenericEditor
{
    /**
     * @protected $name content for attribute name and id of textarea
     */
    protected $name;

    /**
     * @protected $content content of textarea
     */
    protected $content;
    
    /**
     * @protected $rows number of lines of textarea
     */
    protected $rows;

    /**
     * @protected $cols number of cols of textarea
     */
    protected $cols;

    /**
     * @protected $optAttrib additionnal attributes that can be added to textarea
     */
    protected $optAttrib;

    /**
     * @protected $webPath path to access via the web to the directory of the editor
     */
    protected $webPath;
    
    /**
     * Constructor
     * @param string $name name and id of textarea
     * @param string $content content of textarea
     * @param string $rows number of rows of textarea
     * @param string $cols number of cols of textarea
     * @param array $optAttrib additionnal attributes that can be added to textarea
     * @param string $webPath path to access via the web to the directory of the editor
     */
    public function __construct( $name,$content,$rows,$cols,$optAttrib,$webPath )
    {
        $this->name = $name;
        $this->content = $content;
        $this->rows = $rows;
        $this->cols = $cols;
        $this->optAttrib = $optAttrib;
        $this->webPath = $webPath;
    }


    /**
     * Returns the html code needed to display an advanced (default) version of the editor
     * ! Needs to be overloaded by extending classes
     * $returnString .= $this->getTextArea();
     * @return string html code needed to display an advanced (default) version of the editor
       */
    public function getAdvancedEditor()
    {
        return $this->getTextArea();
    }

    /**
     * Returns the html code needed to display a simple version of the editor
     * ! Needs to be overloaded by extending classes
     * @return string html code needed to display a simple version of the editor
       */
    public function getSimpleEditor()
    {
        return $this->getTextArea();
    }
    
    /**
     * Returns the html code needed to display the default textarea
     *
     * @access private
     * @return string html code needed to display the default textarea
     */
    public function getTextArea($class = '')
    {
        $textArea = "\n"
        .    '<textarea '
        .    'id="'.$this->name.'" '
        .    'name="'.$this->name.'" '
        .    'style="width:100%" ';

        if( !empty($class) ) $textArea .= 'class="'.$class.'" ';
                
        $textArea .= 'rows="'.$this->rows.'" '
        .    'cols="'.$this->cols.'" '
        .   $this->optAttrib.' >'
        .claro_htmlspecialchars($this->content)
        .    '</textarea>'."\n";

        return $textArea;
    }
}
