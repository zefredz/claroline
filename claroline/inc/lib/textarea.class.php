<?php # $Id$
if ( ! defined('CLARO_INCLUDE_ALLOWED') ) die('---');
/**
 * CLAROLINE
 *
 * simply produces an HTML textarea. It allows to dynamicaly
 * offers wysiwig editor or not, according the the browser possibilities.
 *
 * @version 1.7 $Revision$
 *
 * @copyright 2001-2005 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @see http://www.claroline.net/wiki/config_def/
 *
 * @package KERNEL
 *
 * @author Claro Team <cvs@claroline.net>
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 *
 */

/**
 * Class to manage htmlarea overring simple textarea html
 * @package KERNEL
 */
class Textarea
{
    /**
     *@var $areaName content for  attribut name  of <textarea>
     */
    var $areaName, $areaContent;
    /**
     *@var $standartCols content for  attribut Cols  of <textarea>
     */
    var $standartCols = 30;
    /**
     *@var $standartRows content for  attribut rows  of <textarea>
     */
    var $standartRows = 30;

    var $areaAttributeList = array();

    /**
     * constructor
     *
     * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
     * @param string $formId id of the form wich contains the textarea
     * @param string $areaName name of the area
     * @param string $areaContent (optional) content wich previously fills the area
     */

    function Textarea($formId, $areaName, $areaContent="")
    {
        $this->parentForm  = $formId;
        $this->areaName    = $areaName;
        $this->areaContent = $areaContent;
    }

/**
 * previsously fills the area with content
 *
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param string $content content to fills in the textarea
 * @return void
 */

    function set_content($content="")
    {
        $this->areaContent = $content;
    }

/**
 * adds attribute to the text area
 *
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param string $name name of the attribute
 * @param string $value value of the attribute
 */

    function set_attribute($name, $value="")
    {
        $name = strtolower($name);
        $this->areaAttributeList[$name] = $value;
    }

/**
 * Output the text area and adapt the editing possibilites
 * according to the browser. Note : browser identifcation works
 * only with Apache server.
 *
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @return void
 */

    function output()
    {
        global $urlAppend;

        if (use_rich_text())
        {
            echo    "<object id=\"richedit\" \n",
                    "        style=\"background-color: buttonface\" \n",
                    "        data=\"",$urlAppend,"/claroline/rte/richedit.html\" \n",
                    "        width=\"595\" height=\"400\" \n",
                    "        type=\"text/x-scriptlet\" viewastext>\n",
                    "</object>\n";

            echo    "<textarea name=\"",$this->areaName,"\" style=\"display:none\">",
                    $this->areaContent,
                    "</textarea>\n";

            echo    "<script language =\"javascript\" event=\"onload\" for=\"window\"> \n",
                    "document.richedit.options = \"history=,o;source=no\"; \n",
                    "document.richedit.docHtml = ",$this->parentForm,".",$this->areaName,".value; \n",
                    "</script>\n";

            echo    "<script language=\"JavaScript\" event=\"onsubmit\" for=\"",$this->parentForm,"\">\n",
                    $this->parentForm,".",$this->areaName,".value = document.richedit.docHtml;\n",
                    "</script>\n";
        }
        else
        {
            if (! in_array('cols', array_keys($this->areaAttributeList)))
                $this->areaAttributeList['cols'] = $this->standartCols;
            if (! in_array('rows', array_keys($this->areaAttributeList)))
                $this->areaAttributeList['rows'] = $this->standartRows;
            if (! in_array('wrap', array_keys($this->areaAttributeList)))
                $this->areaAttributeList['wrap'] = '';

            echo '<textarea';

            foreach($this->areaAttributeList as $name => $value)
            {
                echo ' ' . $name;

                if ($value != '') echo '="' . $value . '"';
            }

            echo '>'
            .    $this->areaContent
            .    '</textarea>'
            ;
        }
    }
}


/**
 * Checks if wwe can display a wysyg editor.
 * Note : this function really works only on Apache server.
 *
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @return bool true browser is rich text compatible,
 *              false otherwise
 */


function use_rich_text()
{
    if (strstr ($_SERVER['HTTP_USER_AGENT'], 'MSIE 6.0')) return true;
    else                                                 return false;
}

?>