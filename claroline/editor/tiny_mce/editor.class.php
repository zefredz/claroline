<?php // $Id$
/**
 * CLAROLINE
 *
 * Driver for tinyMCE wysiwyg editor ( http://tinymce.moxiecode.com/ )
 *
 * @version 1.8 $Revision$
 *
 * @copyright 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @see http://www.claroline.net/wiki/config_def/
 *
 * @package EDITOR
 *
 * @author Claro Team <cvs@claroline.net>
 * @author Sébastien Piraux <pir@cerdecam.be>
 *
 */
 
/**
 * Class to manage htmlarea overring simple textarea html
 * @package EDITOR
 */
class editor
{
      /**
     * @var $name content for attribute name and id of textarea
     */
    var $name;

    /**
     * @var $content content of textarea
     */
    var $content;
    
    /**
     * @var $rows number of lines of textarea
     */
    var $rows;

    /**
     * @var $cols number of cols of textarea
     */
    var $cols;

    /**
     * @var $optAttrib additionnal attributes that can be added to textarea
     */
    var $optAttrib;

    /**
     * @var $webPath path to access via the web to the directory of the editor
     */
    var $webPath;

    /**
     * constructor
     *
     * @author Sébastien Piraux <pir@cerdecam.be>
     * @param string $name content for attribute name and id of textarea
     * @param string $content content of the textarea
     * @param string $rows number of lines of textarea
     * @param string $cols number of cols of textarea
     * @param string $optAttrib additionnal attributes that can be added to textarea
     * @param string $webPath path to access via the web to the directory of the editor
     */
    function editor( $name,$content,$rows,$cols,$optAttrib,$webPath )
    {
        $this->name = $name;
        $this->content = $content;
        $this->rows = $rows;
        $this->cols = $cols;
        $this->optAttrib = $optAttrib;
        $this->webPath = $webPath;
        
        $this->prepareContent();
    }
    
    /**
     * Returns the html code needed to display a simple version of the editor
     *
     * @return string html code needed to display a simple version of the editor
     */
    /* not used at this time
    function getSimpleEditor()
    {
        // configure editor
        $returnString =
            "\n\n"
            .'<script language="javascript" type="text/javascript" src="'.$this->webPath.'/tiny_mce_src.js"></script>'."\n"
            .'<script language="javascript" type="text/javascript">'."\n"
            .'tinyMCE.init({'."\n"
            .'    mode : "exact",'."\n"
            .'    elements: "'.$this->name.'",'."\n"
            .'    theme : "advanced",'."\n"
            .'    theme_advanced_buttons1 : "bold,italic,underline,separator,strikethrough,justifyleft,justifycenter,justifyright, justifyfull,bullist,numlist,undo,redo,link,unlink",'."\n"
            .'    theme_advanced_buttons2 : "",'."\n"
            .'    theme_advanced_buttons3 : "",'."\n"
            .'    theme_advanced_toolbar_location : "top",'."\n"
            .'    theme_advanced_toolbar_align : "left",'."\n"
            .'    theme_advanced_path_location : "bottom",'."\n"
            .'    extended_valid_elements : "a[name|href|target|title|onclick],img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name],hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style]"'."\n"
            .'});'."\n"
            .'</script>'."\n\n";

        // add standard text area
        $returnString .= $this->getTextArea();
        
        return  $returnString;
    }
    */
    
    /**
     * Returns the html code needed to display an advanced (default) version of the editor
     * Advanced version is now the standard one
     *
     * @return string html code needed to display an advanced (default) version of the editor
       */
    function getAdvancedEditor()
    {
        // configure editor
        $returnString =
            "\n\n"
               .'<script language="javascript" type="text/javascript" src="'.$this->webPath.'/tiny_mce_src.js"></script>'."\n"
            .'<script language="javascript" type="text/javascript">'."\n"
            .'tinyMCE.init({'."\n"
            .'    mode : "exact",'."\n"
            .'    elements: "'.$this->name.'",'."\n"
            .'    theme : "advanced",'."\n"
            .'    theme_advanced_buttons1 : "fontselect,fontsizeselect,formatselect,bold,italic,underline,strikethrough,separator,sub,sup,separator,undo,redo,separator",'."\n"
            .'    theme_advanced_buttons2 : "justifyleft,justifycenter,justifyright,justifyfull,separator,bullist,numlist,separator,outdent,indent,separator,forecolor,backcolor,separator,hr,link,unlink,image,code,separator,help",'."\n"
            .'    theme_advanced_buttons3 : "",'."\n"
            .'    theme_advanced_toolbar_location : "top",'."\n"
            .'    theme_advanced_toolbar_align : "left",'."\n"
            .'    theme_advanced_path : true,'."\n"
            .'  theme_advanced_path_location : "bottom",'."\n"
            .'    extended_valid_elements : "a[name|href|target|title|onclick],img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name],hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style]"'."\n"
            .'});'."\n"
            .'</script>'."\n\n";
            
          // add standard text area
        $returnString .= $this->getTextArea();

        return  $returnString;
    }
    
    /**
     * Returns the html code needed to display the default textarea
     *
     * @access private
     * @return string html code needed to display the default textarea
     */
    function getTextArea()
    {
        $textArea =
            '<textarea '
            .'id="'.$this->name.'" '
            .'name="'.$this->name.'" '
            .'style="width:100%" '
            .'rows="'.$this->rows.'" '
            .'cols="'.$this->cols.'" '
            .$this->optAttrib.' >'
            ."\n".$this->content."\n"
            .'</textarea>'."\n";

        return $textArea;
    }

    /**
     * Introduce a comment stating that, since the client uses HTML Area,
     * the content will necessarly be in html format.
     *
     * @access private
     */
    function prepareContent()
    {
        if( strpos( $this->content, '<!-- content: html -->' ) === false )
        {
            $this->content = '<!-- content: html -->' . $this->content;
        }
        
        return true;
    }
}
?>