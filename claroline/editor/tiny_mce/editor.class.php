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

require dirname(__FILE__) . '/../GenericEditor.class.php';
/**
 * Class to manage htmlarea overring simple textarea html
 * @package EDITOR
 */
class editor extends GenericEditor
{
    /**
     * @var $_tag metadata comment added to identify editor
     */
    var $_tag;

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
        parent::GenericEditor( $name,$content,$rows,$cols,$optAttrib,$webPath );

        $this->_tag = '<!-- content: html tiny_mce -->';

        $this->prepareContent();
    }


    /**
     * Returns the html code needed to display an advanced (default) version of the editor
     * @return string html code needed to display an advanced (default) version of the editor
       */
    function getAdvancedEditor()
    {
        // TODO limit to one editor object instance that will give output of several textarea instance
        global $isJsLoaded;

        $returnString = '';

        if( !isset($isJsLoaded) )
        {
            $returnString .=
                "\n\n"
                .'<script language="javascript" type="text/javascript" src="'.$this->webPath.'/tiny_mce.js"></script>'."\n";

            $isJsLoaded = true;
        }

        // configure this editor instance
        $returnString .=
            "\n"
            .'<script language="javascript" type="text/javascript">'."\n"
            .'tinyMCE.init({'."\n"
            .'    mode : "exact",'."\n"
            .'    elements: "'.$this->name.'",'."\n"
            .'    theme : "advanced",'."\n"
            .'    browsers : "msie,gecko,opera",' . "\n" // disable tinymce for safari. default value is "msie,gecko,safari,opera"
            .'    plugins : "media,paste,table",'."\n"
            .'    theme_advanced_buttons1 : "fontselect,fontsizeselect,formatselect,bold,italic,underline,strikethrough,separator,sub,sup,separator,undo,redo",'."\n"
            .'    theme_advanced_buttons2 : "cut,copy,paste,pasteword,separator,justifyleft,justifycenter,justifyright,justifyfull,separator,bullist,numlist,separator,outdent,indent,separator,forecolor,backcolor,separator,hr,link,unlink,image,media,code",'."\n"
            .'    theme_advanced_buttons3 : "tablecontrols,separator,help",'."\n"
            .'    theme_advanced_toolbar_location : "top",'."\n"
            .'    theme_advanced_toolbar_align : "left",'."\n"
            .'    theme_advanced_path : true,'."\n"
            .'    theme_advanced_path_location : "bottom",'."\n"
            .'    apply_source_formatting : true,'."\n"
            .'	  cleanup_on_startup : true,'."\n"
            .'    convert_fonts_to_spans : true,'."\n"
            .'	  directionality : "'.get_locale("text_dir").'",' . "\n"
            .'    convert_urls : false,'."\n" // prevent forced conversion to relative url
            .'    relative_urls : false,'."\n" // prevent forced conversion to relative url
			.'    extended_valid_elements : "a[name|href|target|title|onclick],img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name],hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style]"'."\n"
            .'});'."\n\n"
            .'</script>'."\n\n";

        // add standard text area
        $returnString .= $this->getTextArea();

        return  $returnString;
    }

    /**
     * Introduce a comment stating that the content is html and edited with this editor
     *
     * @access private
     */
    function prepareContent()
    {
        // remove old 'metadata' and add the good one
        $this->content = preg_replace('/<!-- content:[^(\-\->)]*-->/', '', $this->content) . $this->_tag;

        return true;
    }
}
?>