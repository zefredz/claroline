<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );
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
     * @var $tag metadata comment added to identify editor
     */
    var $tag;

    /**
     * @var $askStrip ask user if the content can be cleaned ?
     */
    var $askStrip;
    
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
        
		$this->tag = '<!-- content: html tiny_mce -->';
		
		// test content before preparing because preparation adds $this->tag
		$this->askStrip = $this->needCleaning();		
		
	    $this->prepareContent();
    }
    
   
    /**
     * Returns the html code needed to display an advanced (default) version of the editor
     * Advanced version is now the standard one
     * $returnString .= $this->getTextArea();
     * @return string html code needed to display an advanced (default) version of the editor
       */
    function getAdvancedEditor()
    {
        // configure editor
        $returnString =
            "\n\n"
            .'<script language="javascript" type="text/javascript" src="'.$this->webPath.'/tiny_mce_src.js"></script>'."\n"
            .'<script language="javascript" type="text/javascript">'."\n\n";

        $returnString .=
            'tinyMCE.init({'."\n"
            .'    mode : "exact",'."\n"
            .'    elements: "'.$this->name.'",'."\n"
            .'    theme : "advanced",'."\n"
            .'    plugins : "flash,paste",'."\n"
            .'    theme_advanced_buttons1 : "fontselect,fontsizeselect,formatselect,bold,italic,underline,strikethrough,separator,sub,sup,separator,undo,redo,separator",'."\n"
            .'    theme_advanced_buttons2 : "cut,copy,paste,pasteword,separator,justifyleft,justifycenter,justifyright,justifyfull,separator,bullist,numlist,separator,outdent,indent,separator,forecolor,backcolor,separator,hr,link,unlink,image,flash,code,separator,help",'."\n"
            .'    theme_advanced_buttons3 : "",'."\n"
            .'    theme_advanced_toolbar_location : "top",'."\n"
            .'    theme_advanced_toolbar_align : "left",'."\n"
            .'    theme_advanced_path : true,'."\n"
            .'    theme_advanced_path_location : "bottom",'."\n"
            .'    convert_urls : false,'."\n" // prevent forced conversion to relative url 
            .'    relative_urls : false,'."\n"; // prevent forced conversion to relative url
		
		if( $this->askStrip ) $returnString .='    setupcontent_callback : "strip_old_htmlarea",'."\n";
            
        $returnString .=
            '    extended_valid_elements : "a[name|href|target|title|onclick],img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name],hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style]"'."\n"
            .'});'."\n\n"
            .'</script>'."\n\n";
        
		if( $this->askStrip )
		{
			$returnString .=
	            "\n\n"
            	.'<script language="javascript" type="text/javascript">'."\n\n"
        	    .'function strip_old_htmlarea(editor_id,body,doc)'."\n"
		        .'{'."\n"
        	    .'    if( confirm(" '.get_lang('This text layout should be modified to be editable in this editor.\nCancel to keep your original text layout.\n').' ") )'."\n"
    	        .'    {'."\n"
				.'        content = body.innerHTML;'."\n\n"	
        	    .'        content = content.replace(/style="[^"]*"/g, "");'."\n"
    	        .'        content = content.replace(/<span[^>]*>/g, "");'."\n"
	            .'        content = content.replace(/<\/span>/g, "");'."\n\n"
        	    .'        body.innerHTML = content ;'."\n"
    	        .'        return true;'."\n"            
	            .'    }'."\n"            
        	    .'    return false;'."\n"
    	        .'}'."\n\n"
	            .'</script>'."\n\n";
        }
        
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
        $textArea = "\n"
            .'<textarea '
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
     * Introduce a comment stating that the content is html and edited with this editor
     *
     * @access private
     */
    function prepareContent()
    {
    	// remove old 'metadata' and add the good one
    	$this->content = preg_replace('/<!-- content:[^(\-\->)]*-->/', '', $this->content) . $this->tag;

        return true;
    }
    
    /**
     * check if the text require a cleaning to be editable by tinymce
     *
     * @return boolean is content requiring a cleaning to be
     * @access private
     */
    function needCleaning()
    {
    	// if we already have the tinymce tag content cleaning is not required
	    if( strpos($this->content,$this->tag) !== false ) return false;    

	    // if content contains only the tiny_mce tag cleaning is not required
	    if( '' == str_replace($this->tag,'',$this->content) ) return false;

    	if( preg_match('/style="[^"]*"/',$this->content) )
    	{
   			// if we have style attributes : cleaning is required
    		return true;
    	}    	
    	elseif( preg_match('/<span[^>]*>/', $this->content) )
    	{
  			// if we have span tags : cleaning is required
    		return true;
    	}
    	else
    	{
    		// nor style attributes neither span tags : cleaning is not required
    		return false;
    	}    	
    }

}
?>
