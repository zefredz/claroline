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
 * @author see 'credits' file
 *
 *
 */

/**
 * This lib (in a class to simulate namespace) provide html stream for various
 * uniformised output.
 *
 * @package HTML
 *
 */


/**
 * display a item list as vertical menu.
 *
 * @param array $itemList each item are include in a list.
 *
 * @return string html
 */
function claro_html_menu_vertical($itemList, $attrBloc=array(),$attrItem=array())
{
    $classBlocAttr = '';
    $otherBlocAttrString = '';
    foreach ($attrBloc as $attrName => $attrValue)
    {
        if ('class' == $attrName) $classBlocAttr = ' ' . trim($attrValue);
        else $otherBlocAttrString .= ' ' . $attrName . '="' . $attrValue . '"';
    }
    $itemAttrString = '';
    foreach ($attrItem as $attrName => $attrValue) $itemAttrString .= ' ' . $attrName . '="' . $attrValue . '"';

    if (! empty($itemList) && is_array($itemList))
    {
        $htmlStream = '<ul class="menu vmenu ' . $classBlocAttr . '" ' . $otherBlocAttrString . '>' . "\n";
        foreach($itemList as $item )
        {
            $htmlStream .= '<li' . $itemAttrString . '>' . "\n"
            .              $item
            .              '</li>' . "\n"
            ;
        }
        $htmlStream .= '</ul>' . "\n";
    }
    else
    $htmlStream ='';
    return $htmlStream;
}

/**
* display a item list as vertical menu.
*
* @param array $itemList each item are include in a list.
*
* @return string html
*/
function claro_html_menu_vertical_br($itemList, $attrBloc=array())
{
    $classBlocAttr = '';
    $otherBlocAttrString = '';
    foreach ($attrBloc as $attrName => $attrValue)
    {
        if ('class' == $attrName) $classBlocAttr = ' ' . trim($attrValue);
        else $otherBlocAttrString .= ' ' . $attrName . '="' . $attrValue . '"';
    }

    $htmlStream = '<div class="menu vmenu ' . $classBlocAttr . '" ' . $otherBlocAttrString . '>' . "\n";

    if (! empty($itemList) && is_array($itemList))
    {
            $htmlStream .= implode('<br />' . "\n",$itemList );
    }
    $htmlStream .= '</div>' . "\n";

    return $htmlStream;
}


/**
 * display a item list as vertical menu.
 *
 * @param array $itemList each item are include in a list.
 *
 * @return string : list content as an horizontal menu.
 */

function claro_html_menu_horizontal($itemList)
{
    if( !empty($itemList) && is_array($itemList))
    {
        return "\n\n"
        . '<span>' . "\n"
        . implode( "\n" . ' | ' . "\n",$itemList) . "\n"
        . '</span>'
        . "\n\n";
    }
    else
    {
        return '';
    }
}

/**
* Return the claroline sytled url for a link to a tool
*
* @param string $url
* @param string $label
* @param array $attributeList array of array(attributeName,attributeValue)
* @return string html stream
*/
function claro_html_tool_link($url,$label,$attributeList=array())
{
    $attributeConcat = 'class="toollink" ';

    if (is_array($attributeList))
    {
        foreach ($attributeList as $key => $attribute)
        {
            $attributeConcat .= (is_array($attribute) ? $attribute['name'].'="'.$attribute['value'].'" ' : $key.'="'.$attribute.'" ');
        }
    }
    else trigger_error('$attributeList would be an array', E_USER_WARNING);
    return '<a href="' . $url . '" ' . $attributeConcat . ' >'
    .       $label
    .       '</a>' . "\n"
    ;

}

/**
* Prepare the display of a clikcable button
*
* This function is needed because claroline buttons rely on javascript.
* The function return an optionnal behavior fo browser where javascript
* isn't  available.
*
* @author Hugues Peeters <hugues.peeters@claroline.net>
*
* @param string $url url inserted into the 'href' part of the tag
* @param string $text text inserted between the two <a>...</a> tags (note : it
*        could also be an image ...)
* @param string $confirmMessage (optionnal) introduce a javascript confirmation popup
* @return string the button
*/

function claro_html_button($url, $text, $confirmMessage = '')
{

    if (   claro_is_javascript_enabled()
    && ! preg_match('~^Mozilla/4\.[1234567]~', $_SERVER['HTTP_USER_AGENT']))
    {
        if ($confirmMessage != '')
        {
            $onClickCommand = "if(confirm('" . clean_str_for_javascript($confirmMessage) . "')){document.location='" . $url . "';return false}";
        }
        else
        {
            $onClickCommand = "document.location='".$url."';return false";
        }

        return '<button class="claroButton" onclick="' . $onClickCommand . '">'
        .      $text
        .      '</button>&nbsp;' . "\n"
        ;
    }
    else
    {
        return '[ <a href="' . $url . '">' . $text . '</a> ]';
    }
}


/**
* Displays the title of a tool. Optionally, there can be a subtitle below
* the normal title, and / or a supra title above the normal title.
*
* e.g. supra title:
* group
* GROUP PROPERTIES
*
* e.g. subtitle:
* AGENDA
* calender & events tool
*
* @author Hugues Peeters <hugues.peeters@claroline.net>
* @param  mixed $titleElement - it could either be a string or an array
*                               containing 'supraTitle', 'mainTitle',
*                               'subTitle'
* @return void
*/

function claro_html_tool_title($titlePart, $helpUrl = false)
{
    // if titleElement is simply a string transform it into an array

    if ( is_array($titlePart) ) $titleElement = $titlePart;
    else                        $titleElement['mainTitle'] = $titlePart;

    $stringPart= array();
    if ( isset($titleElement['supraTitle']) )
    {
        $stringPart[] = '<small>' . $titleElement['supraTitle'] . '</small>';
    }

    if ( isset($titleElement['mainTitle']) )
    {
        $stringPart[] = $titleElement['mainTitle'];
    }

    if ( isset($titleElement['subTitle']) )
    {
        $stringPart[] = '<small>' . $titleElement['subTitle'] . '</small>';
    }

    $string = "\n" . '<h3 class="claroToolTitle">' . "\n";

    if ($helpUrl)
    {
        global $clarolineRepositoryWeb, $imgRepositoryWeb;

        $string .= "<a href='#' onClick=\"MyWindow=window.open('". $clarolineRepositoryWeb . "help/" .$helpUrl
        ."','MyWindow','toolbar=no,location=no,directories=no,status=yes,menubar=no,scrollbars=yes,resizable=yes,width=350,height=450,left=300,top=10'); return false;\">"

        .'<img src="'.$imgRepositoryWeb.'/help.gif" '
        .' alt ="'.get_lang('Help').'"'
        .' align="right"'
        .' hspace="30">'
        .'</a>' . "\n"
        ;
    }

    $string .= implode('<br />' . "\n",$stringPart)
    .          '</h3>' . "\n\n"
    ;

    return $string;
}


/**
* Prepare display of the message box appearing on the top of the window,
* just    below the tool title. It is recommended to use this function
* to display any confirmation or error messages, or to ask to the user
* to enter simple parameters.
*
* @author Hugues Peeters <hugues.peeters@claroline.net>
* @param string $message include your self any additionnal html
*                        tag if you need them
* @since 1.8
*
* @return string html string for a message box
*/

function claro_html_message_box($message)
{
    $effectiveContent = trim(strip_tags($message));

    if(!empty($effectiveContent))
    return "\n" . '<table class="claroMessageBox" border="0" cellspacing="0" cellpadding="10">'
    .      '<tr>'
    .      '<td>'
    .      $message
    .      '</td>'
    .      '</tr>'
    .      '</table>' . "\n\n"
    ;
    else return '';
}


/**
* Allows to easily display a breadcrumb trail
*
* @param array $nameList bame of each breadcrumb
* @param array $urlList url corresponding to the breadcrumb name above
* @param string $separator (optionnal) element which segregate the breadcrumbs
* @param string $homeImg (optionnal) source url for a home icon at the trail start
* @return string : the build breadcrumb trail
*
* @author Hugues Peeters <peeters@ipm.ucl.ac.be>
*/

function claro_html_breadcrumbtrail($nameList, $urlList, $separator = ' &gt; ', $homeImg = null)
{
    // trail of only one element has no sense ...
    if (count ($nameList) < 2 ) return '<div class="breadcrumbTrail">&nbsp;</div>';

    $breadCrumbList = array();

    foreach($nameList as $thisKey => $thisName)
    {
        if (   array_key_exists($thisKey, $urlList)
        && ! is_null($urlList[$thisKey])       )
        {
            $startAnchorTag = '<a href="' . $urlList[$thisKey] . '" target="_top">';
            $endAnchorTag   = '</a>';
        }
        else
        {
            $startAnchorTag = '';
            $endAnchorTag   = '';
        }

        $htmlizedName = is_htmlspecialcharized($thisName)
        ? $thisName
        : htmlspecialchars($thisName);

        $breadCrumbList [] = $startAnchorTag
        . $htmlizedName
        . $endAnchorTag;
    }

    // Embed the last bread crumb entry of the list.

    $breadCrumbList[count($breadCrumbList)-1] = '<strong>'
    .end($breadCrumbList)
    .'</strong>';

    return  '<div class="breadcrumbTrail">'
    . ( is_null($homeImg) ? '' : '<img src="' . $homeImg . '" alt=""> ' )
    . implode($separator, $breadCrumbList)
    . '</div>';
}


/**
* Function used to draw a progression bar
*
* @author Piraux SÈbastien <pir@cerdecam.be>
*
* @param integer $progress progression in pourcent
* @param integer $factor will be multiply by 100 to have the full size of the bar
* (i.e. 1 will give a 100 pixel wide bar)
*/

function claro_html_progress_bar ($progress, $factor)
{
    global $clarolineRepositoryWeb, $imgRepositoryWeb;
    $maxSize  = $factor * 100; //pixels
    $barwidth = $factor * $progress ;

    // display progress bar
    // origin of the bar
    $progressBar = '<img src="' . $imgRepositoryWeb . 'bar_1.gif" width="1" height="12" alt="" />';

    if($progress != 0)
    $progressBar .= '<img src="' . $imgRepositoryWeb . 'bar_1u.gif" width="' . $barwidth . '" height="12" alt="" />';
    // display 100% bar

    if($progress!= 100 && $progress != 0)
    $progressBar .= '<img src="' . $imgRepositoryWeb . 'bar_1m.gif" width="1" height="12" alt="" />';

    if($progress != 100)
    $progressBar .= '<img src="' . $imgRepositoryWeb . 'bar_1r.gif" width="' . ($maxSize - $barwidth) . '" height="12" alt="" />';
    // end of the bar
    $progressBar .=  '<img src="' . $imgRepositoryWeb . 'bar_1.gif" width="1" height="12" alt="" />';

    return $progressBar;
}


/**
* Display list of messages in substyled boxes in a message_box
*
* In most of cases  function message_box() is enough.
*
* @param array $msgArrBody of array of blocs containing array of messages
* @author Christophe GeschÈ <moosh@claroline.net>
* @version 1.0
* @see  message_box()
*
*  code for using this    in your    tools:
*  $msgArrBody["nameOfCssClass"][]="foo";
*  css    class can be defined in    script but try to use
*  class from    generic    css    ()
*  error success warning
*  ...
*
* @todo this must be a message object where code add messages with a priority,
* and the rendering is set by by priority
*
*/

function claro_html_msg_list($msgArrBody, $return=true)
{
    $msgBox = '';

    if (is_array($msgArrBody) && count($msgArrBody) > 0)
    {
        foreach ($msgArrBody as $classMsg => $thisMsgArr)
        {
            if( is_array($thisMsgArr) )
            {
                $msgBox .= '<div class="' . $classMsg . '">';
                foreach ($thisMsgArr as $anotherThis) $msgBox .= '<div class="msgLine" >' . $anotherThis . '</div>';
                $msgBox .= '</div>';
            }
            else
            {
                $msgBox .= '<div class="' . $classMsg . '">';
                $msgBox .= '<div class="msgLine" >' . $thisMsgArr . '</div>';
                $msgBox .= '</div>';
            }
        }
    }

    if ($return) return claro_html_message_box($msgBox);
    else         echo   claro_html_message_box($msgBox);
    return true;
}



/**
* prepare the 'option' html tag for the claro_disp_nested_select_menu()
* function
*
* @author Christophe GeschÈ <moosh@claroline.net>
* @author Hugues Peeters <hugues.peeters@claroline.net>
* @param array $elementList
* @param integer  $deepness (optionnal, default is 0)
* @return array of option list
*/


function claro_html_nestedArrayToOptionList($elementList, $deepness = 0)
{
    foreach($elementList as $thisElement)
    {
        $tab = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $deepness);

        $optionTagList[$thisElement['value']] =  $tab.$thisElement['name'] ;
        if (   isset( $thisElement['children'] )
        && sizeof($thisElement['children'] ) > 0)
        {
            $optionTagList = array_merge( $optionTagList,
            prepare_option_tags($thisElement['children'],
            $deepness + 1 ) );
        }
    }

    return  $optionTagList;
}

/**
* prepare a mailto link
*
* @param string $mail
* @param string $mailLabel
* @return string : html stream
*/
function claro_html_mailTo($mail,$mailLabel=null)
{
    if (is_null($mailLabel)) $mailLabel = $mail;
    $mailHtml = '<a href="mailto:' . $mail . '" class="email" >' . $mailLabel . '</a>';
    return $mailHtml;
}

/**
* Insert a Wysiwyg editor inside a form instead of a textarea
* A standard textarea is displayed if the Wysiwyg editor is disabled or if
* the user's browser have no activated javascript support
*
* @param string $name content for name attribute in textarea tag
* @param string $content optional content previously inserted into    the    area
* @param int     $rows optional    textarea rows
* @param int    $cols optional    textarea columns
* @param string $optAttrib    optional - additionnal tag attributes
*                                       (wrap, class, ...)
* @return string html output for standard textarea or Wysiwyg editor
*
* @global string rootWeb from claro_main.conf.php
* @global string rootSys from claro_main.conf.php
* @global string langTextEditorDisable from lang file
* @global string langTextEditorEnable from lang file
* @global string langSwitchEditorToTextConfirm from lang file
*
* @author Hugues Peeters <hugues.peeters@claroline.net>
* @author SÈbastien Piraux <pir@cerdecam.be>
*/

function claro_html_textarea_editor($name, $content = '', $rows=20, $cols=80, $optAttrib='')
{
    global $urlAppend, $claro_editor;

    if( !get_conf('claro_editor') ) $claro_editor = 'tiny_mce';

    $returnString = '';

    // get content if in url
    if( isset($_REQUEST['areaContent']) ) $content = stripslashes($_REQUEST['areaContent']);

    // $claro_editor is the directory name of the editor
    $incPath = get_conf('rootSys') . 'claroline/editor/' . $claro_editor;
    $editorPath = $urlAppend . '/claroline/editor/';
    $webPath = $editorPath . $claro_editor;

    if( file_exists($incPath . '/editor.class.php') )
    {
        // include editor class
        include_once $incPath . '/editor.class.php';

        // editor instance
        $editor = new editor($name,$content,$rows,$cols,$optAttrib,$webPath);

        $returnString .= $editor->getAdvancedEditor();
    }
    else
    {
        // if the editor class doesn't exists we cannot rely on it to display
        // the standard textarea
        $returnString .=
        '<textarea '
        .'id="'.$name.'" '
        .'name="'.$name.'" '
        .'style="width:100%" '
        .'rows="'.$rows.'" '
        .'cols="'.$cols.'" '
        .$optAttrib.' >'
        ."\n".htmlspecialchars($content)."\n"
        .'</textarea>'."\n";
    }

    return $returnString;
}

/**
 * datagrid is actually a function but can became an object.
 *
 * function claro_disp_datagrid($dataGrid, $option = null)
 *
 * would became a static method.
 *
 * but in dynamic work,
 * new datagrid($dataGrid = null, $option_list = null)
 * set_grid(array of array $datagrid)
 * set_option_list(array $option_list)
 * set_idLineType(string $line_type)
 * set_idLineShift(integer $line_shift)
 * set_colTitleList(array('colName'=>'colTitle'));
 * set_colAttributeList(array('colName'=> array('attribName'=>'attribValue'))
 * set_caption(string 'caption');
 * set_counterLine(bool 'dispCounter')
 *
 * @package HTML
 *
 */
class claro_datagrid
{


    var $datagrid;

    var $idLineType =  'numeric';
    var $idLineShift = 1;
    var $colTitleList =null;
    var $colAttributeList = array();
    var $caption = '';
    var $counterLine;
    var $dispCounter = false;
    var $colHead =null;
    var $htmlNoRowMessage = null;

    var $dispIdCol = true;
    var $internalKey = 0;

    function claro_datagrid($datagrid = null)
    {
        if (!is_null($datagrid))    $this->set_grid($datagrid);

        $this->set_idLineType('none');
    }

    /**
     * set data grid
     *
     * @param array $datagrid
     */
    function set_grid($datagrid)
    {
        if (is_array($datagrid))
        {
            $this->internalKey = 0;
            $this->datagrid = $datagrid ;
        }
        else                     trigger_error('set_grid need an array : ' .var_export($datagrid,1). ' is not array' ,E_USER_NOTICE);

    }


    function set_option_list($option_list)
    {
        foreach ( $option_list as $option => $value )
        {
            switch ( $option )
            {
                case 'idLineShift':
                    $this->set_idLineShift($value);
                    break;
                case 'colTitleList':
                    $this->set_colTitleList($value);
                    break;
                case 'colAttributeList':
                    $this->set_colAttributeList($value);
                    break;
                case 'caption':
                    $this->set_caption($value);
                    break;
            }
        }
    }

    /**
     * set the  isLineType option
     *
     * @param string $line_type 'blank' 'numeric' 'key' 'none' default:'none'
     *
     */
    function set_idLineType( $idLineType)
    {

        //* manage idLine option
        $this->idLineType = $idLineType;
        switch (strtolower($idLineType))
        {
            case 'blank'   : $this->dispIdCol = true; $this->idLineType = '';   break;
            case 'numeric' : $this->dispIdCol = true; $this->internalKey = 0;   break;
            case 'key'     : $this->dispIdCol = true; break;
            case 'none'    : $this->dispIdCol = false; break;
            default        : $this->dispIdCol = false;
        }
    }

    /**
     * set the  idLineShift option
     *
     * @param integer $idLineShift
     */
    function set_idLineShift( $idLineShift)
    {
        $this->idLineShift = $idLineShift;
    }

    /**
     * set the  colTitleList option
     *
     * @param array $colTitleList array('colName'=>'colTitle')
     */

    function set_colTitleList( $colTitleList)
    {
        if (is_array($colTitleList)) $this->colTitleList = $colTitleList;
        else                         trigger_error('array attempt',E_USER_NOTICE);
    }


    /**
     * set the  colAttributeList option
     *
     * @param array $colAttributeList array('colName'=> array('attribName'=>'attribValue'))
     */

    function set_colAttributeList( $colAttributeList)
    {
        $this->colAttributeList = $colAttributeList;

    }

    /**
     * set the  colAttributeList option
     *
     * @param array $colAttributeList array('colName'=> array('attribName'=>'attribValue'))
     */

    function set_noRowMessage( $htmlNoRowMessage)
    {
        $this->htmlNoRowMessage = $htmlNoRowMessage;

    }

    /**
     * set the caption
     *
     * @param string $caption array('colName'=>'colTitle')
     */

    function set_caption($caption)
    {
        $this->caption =  '<caption>' . $caption . '</caption>';
    }


    /**
     * set the  caption option
     *
     * @param string $caption array('colName'=>'colTitle')
     */

    function set_colHead( $colHeadName)
    {
        $this->colHead = $colHeadName;
    }


    /**
     * set the  counterLine option
     *
     * @param integer $counterLine
     */

    function showCounterLine()
    {
        $this->dispCounter = true;
    }

    function render()
    {
        $stream = '';
        if (is_array($this->datagrid) )//&& count($this->datagrid))
        {

            /**
             * Build attributes for column
             * In  W3C <COL> seems be the good usage but browser don't follow the tag
             * So all attribute would be in each td of column.
             */
            if (!is_array($this->colTitleList)&&count($this->datagrid))
            {
                if (is_array($this->datagrid) && isset($this->datagrid[0]) && is_array($this->datagrid[0]))
                $this->colTitleList = array_keys($this->datagrid[0]);
            }

            if (isset($this->colAttributeList))
            foreach (array_keys($this->colAttributeList) as $col)
            {
                $attrCol[$col]='';
                foreach ($this->colAttributeList[$col] as $attriName => $attriValue )
                {
                    $attrCol[$col] .=' ' . $attriName . '="' . $attriValue . '" ';
                }
            }

            $stream .= '<table class="claroTable emphaseLine" width="100%" border="0" cellspacing="2">' . "\n"
            // THEAD LINE
            .          '<thead>' . "\n"
            .          $this->caption
            .          '<tr class="headerX" align="center" valign="top">' . "\n"
            ;

            if ($this->dispIdCol) $stream .= '<th width="10"></th>' . "\n";

            $i=0;
            foreach ($this->colTitleList as $colTitle)
            {
                $stream .= '<th scope="col" id="c' . $i++ . '" >' . $colTitle . '</th>' . "\n";
            }
            $stream .= '</tr>' . "\n"
            .          '</thead>' . "\n"
            ;

            if ($this->dispCounter)
            {
                $stream .= '<tfoot>' . "\n"
                .          '<tr class="headerX" align="center" valign="top">' . "\n"
                .          '<td>' . "\n"
                .          '</td>' . "\n"
                .          '<td>' . "\n"
                .          count($this->datagrid) . ' ' . get_lang('Lines')
                .          '</td>' . "\n"
                .          '</tr>' . "\n"
                .          '</tr>' . "\n"
                .          '</tfoot>' . "\n"
                ;

            }

            $stream .= '<tbody>' . "\n";
            if(count($this->datagrid))
            {

                foreach ($this->datagrid as $key => $dataLine )
                {
                    switch ($this->idLineType)
                    {
                        case 'key'     : $idLine = $key;                                       break;
                        case 'numeric' : $idLine = $this->idLineShift + $this->internalKey++ ; break;
                        default        : $idLine = '';
                    }

                    $stream .= '<tr>' . "\n";

                    if ($this->dispIdCol) $stream .= '<td align="right" valign="middle">' . $idLine . '</td>' . "\n";

                    $i=0;
                    foreach ($dataLine as $colId => $dataCell)
                    {
                        if ($this->colHead == $colId)
                        {
                            $stream .= '<td scope="line" id="L' . $key . '" headers="c' . $i++ . '" ' . ( isset($attrCol[$colId])?$attrCol[$colId]:'') . '>';
                            $stream .= $dataCell;
                            $stream .= '</td>' . "\n";
                        }
                        else
                        {
                            $stream .= '<td headers="c' . $i++ . ' L' . $key . '" ' . ( isset($attrCol[$colId])?$attrCol[$colId]:'') . '>';
                            $stream .= $dataCell;
                            $stream .= '</td>' . "\n";
                        }
                    }
                    $stream .= '</tr>' . "\n";

                }
            }
            else
            {
                if (is_null($this->htmlNoRowMessage )) $this->htmlNoRowMessage = get_lang('No result');
                $stream .= '<tr class="dgnoresult" ><td class="dgnoresult" colspan="' . count(array_keys($this->colTitleList)) . '">' . $this->htmlNoRowMessage  . '</td></tr>';
            }
            $stream .= '</tbody>' . "\n"
            .          '</table>' . "\n"
            ;

        }

        return $stream;

    }

}

//////////////////////////////////////////////////////////////////////////////
//                              DISPLAY OPTIONS
//                            student    view, title, ...
//////////////////////////////////////////////////////////////////////////////


/**
 * Route the script to an auhtentication form if user id is missing.
 * Once authenticated, the system get back to the source where the form
 * was trigged
 *
 * @param boolean $cidRequired - if the course id is required to leave the form
 * @author Christophe geschÈ <moosh@claroline.net>
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 */

function claro_disp_auth_form($cidRequired = false)
{
    global $urlAppend, $includePath, $_cid;

    $sourceUrl = ( isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on'
    ? 'https://'
    : 'http://')
    .  $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];

    // note : some people say that REQUEST_URI isn't available on IIS.
    // It has to be checked  ...

    if ( ! headers_sent () )
    {
        $urlCmd = ($cidRequired && ! $_cid ? '&cidRequired=true' : '');
        header('Location:' . $urlAppend . '/claroline/auth/login.php?sourceUrl=' . urlencode($sourceUrl) . $urlCmd );
    }
    else // HTTP header has already been sent - impossible to relocate
    {
        echo '<p align="center">'
        .    'WARNING ! Login Required <br />'
        .    'Click '
        .    '<a href="' . $urlAppend . '/claroline/auth/login.php'
        .    '?sourceUrl=' . urlencode($sourceUrl) . '">'
        .    'here'
        .    '</a>'
        .    '</p>'
        ;

        require $includePath . '/claro_init_footer.inc.php';
    }

    die(); // necessary to prevent any continuation of the application
}

/**
 * function claro_build_nested_select_menu($name, $elementList)
 * Build in a relevant way 'select' menu for an HTML form containing nested data
 *
 * @param string $name, name of the select tag
 * @param array nested data in a composite way
 *
 * @return string the HTML flow
 *
 *  $elementList[1]['name'    ] = 'level1';
 *  $elementList[1]['value'   ] = 'level1';
 *
 *  $elementList[1]['children'][1]['name' ] = 'level2';
 *  $elementList[1]['children'][1]['value'] = 'level2';
 *
 *  $elementList[1]['children'][2]['name' ] = 'level2';
 *  $elementList[1]['children'][2]['value'] = 'level2';
 *
 *  $elementList[2]['name' ]  = 'level1';
 *  $elementList[2]['value']  = 'level1';
 *
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 *
 */

function claro_build_nested_select_menu($name, $elementList)
{
    return '<select name="' . $name . '">' . "\n"
    .      implode("\n", prepare_option_tags($elementList) )
    .      '</select>' .  "\n"
    ;
}

/**
 * prepare the 'option' html tag for the claro_disp_nested_select_menu()
 * function
 *
 * @param array $elementList
 * @param int   $deepness (optionnal, default is 0)
 * @return array of option tag list
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 *
 */


function prepare_option_tags($elementList, $deepness = 0)
{
    foreach($elementList as $thisElement)
    {
        $tab = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $deepness);

        $optionTagList[] = '<option value="'.$thisElement['value'].'">'
        .                  $tab.$thisElement['name']
        .                  '</option>'
        ;
        if (   isset( $thisElement['children'] )
        && sizeof($thisElement['children'] ) > 0)
        {
            $optionTagList = array_merge( $optionTagList,
            prepare_option_tags($thisElement['children'],
            $deepness + 1 ) );
        }
    }

    return  $optionTagList;
}



/**
 * Checks if the string has been written html style (ie &eacute; etc)
 *
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param string $string
 * @return boolean true if the string is written in html style, false otherwise
 */

function is_htmlspecialcharized($string)
{
    return (bool) preg_match('/(&[a-z]+;)|(&#[0-9]+;)/', $string);
}

/**
 * function that cleans php string for javascript
 *
 * This function is needed to clean strings used in javascript output
 * Newlines are prohibited in the script, specialchar  are prohibited
 * quotes must be addslashes
 *
 * @param $str string original string
 * @return string : cleaned string
 *
 * @author Piraux SÈbastien <pir@cerdecam.be>
 *
 */
function clean_str_for_javascript( $str )
{
    $output = $str;
    // 1. addslashes, prevent problems with quotes
    // must be before the str_replace to avoid double backslash for \n
    $output = addslashes($output);
    // 2. turn windows CR into *nix CR
    $output = str_replace("\r", '', $output);
    // 3. replace "\n" by uninterpreted '\n'
    $output = str_replace("\n",'\n', $output);
    // 4. convert special chars into html entities
    $output = htmlspecialchars($output);

    return $output;
}

/**
 * Parse the user text (e.g. stored in database)
 * before displaying it to the screen
 * For example it change new line charater to <br> tag etc.
 *
 * @param string $userText original user tex
 * @return string : parsed user text
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 */

function claro_parse_user_text($userText)
{
   global $claro_texRendererUrl; // see 'conf/claro_main.conf.php'

   if ( !empty($claro_texRendererUrl) )
   {
       $userText = str_replace('[tex]',
                          '<img src="'.$claro_texRendererUrl.'?',
                          $userText);

       $userText = str_replace('[/tex]',
                           '" border="0" align="absmiddle">',
                           $userText);
   }
   else
   {
       $userText = str_replace('[tex]',
                              '<embed TYPE="application/x-techexplorer" texdata="',
                              $userText);

       $userText = str_replace('[/tex]',
                               '" width="100%" pluginspace="http://www.integretechpub.com/">',
                               $userText);
   }

   $userText = make_clickable($userText);

   if ( strpos($userText, '<!-- content: html -->') === false )
   {
        // only if the content isn't HTML change new line to <br>
        // Note the '<!-- content: html -->' is introduced by HTML Area
        $userText = nl2br($userText);
   }

    return $userText;
}

/**
 * Completes url contained in the text with "<a href ...".
 * However the function simply returns the submitted text without any
 * transformation if it already contains some "<a href:" or "<img src=".
 *
 * Actually this function is taken from the PHP BB 1.4 script
 * - Goes through the given string, and replaces xxxx://yyyy with an HTML <a> tag linking
 *  to that URL
 * - Goes through the given string, and replaces www.xxxx.yyyy[zzzz] with an HTML <a> tag linking
 *  to http://www.xxxx.yyyy[/zzzz]
 * - Goes through the given string, and replaces xxxx@yyyy with an HTML mailto: tag linking
 *      to that email address
 * - Only matches these 2 patterns either after a space, or at the beginning of a line
 *
 * Notes: the email one might get annoying - it's easy to make it more restrictive, though.. maybe
 * have it require something like xxxx@yyyy.zzzz or such. We'll see.
 *
 * @param  string $text text to be converted
 * @return string : text after conversion
 *
 * @author Rewritten by Nathan Codding - Feb 6, 2001.
 * @author completed by Hugues Peeters - July 22, 2002
 */

function make_clickable($text)
{

    // If the user has decided to deeply use html and manage himself hyperlink
    // cancel the make clickable() function and return the text untouched. HP

    if (preg_match ( "<(a|img)[[:space:]]*(href|src)[[:space:]]*=(.*)>", $text) )
    {
        return $text;
    }

    // pad it with a space so we can match things at the start of the 1st line.
    $ret = " " . $text;


    // matches an "xxxx://yyyy" URL at the start of a line, or after a space.
    // xxxx can only be alpha characters.
    // yyyy is anything up to the first space, newline, or comma.

    $ret = preg_replace("#([\n ])([a-z]+?)://([^, \n\r]+)#i",
                        "\\1<a href=\"\\2://\\3\" >\\2://\\3</a>",
                        $ret);

    // matches a "www.xxxx.yyyy[/zzzz]" kinda lazy URL thing
    // Must contain at least 2 dots. xxxx contains either alphanum, or "-"
    // yyyy contains either alphanum, "-", or "."
    // zzzz is optional.. will contain everything up to the first space, newline, or comma.
    // This is slightly restrictive - it's not going to match stuff like "forums.foo.com"
    // This is to keep it from getting annoying and matching stuff that's not meant to be a link.

    $ret = preg_replace("#([\n ])www\.([a-z0-9\-]+)\.([a-z0-9\-.\~]+)((?:/[^, \n\r]*)?)#i",
                        "\\1<a href=\"http://www.\\2.\\3\\4\" >www.\\2.\\3\\4</a>",
                        $ret);

    // matches an email@domain type address at the start of a line, or after a space.
    // Note: before the @ sign, the only valid characters are the alphanums and "-", "_", or ".".
    // After the @ sign, we accept anything up to the first space, linebreak, or comma.

    $ret = preg_replace("#([\n ])([a-z0-9\-_.]+?)@([^, \n\r]+)#i",
                        "\\1<a href=\"mailto:\\2@\\3\">\\2@\\3</a>",
                        $ret);

    // Remove our padding..
    $ret = substr($ret, 1);

    return($ret);
}


/**
 * Deprecated functions
 * Some function still present to prevent local developpement
 *
 * They would be removed after 1.8
 *
 */

/**
 * Enhance a simple textarea with an inline html editor.
 *
 * @param string $name name attribute for <textarea> tag
 * @param string $content content to prefill the area
 * @param integer $rows count of rows for the displayed editor area
 * @param integer $cols count of columns for the displayed editor area
 * @param string $optAttrib    optional - additionnal tag attributes
 *                                       (wrap, class, ...)
 * @return string html output for standard textarea or Wysiwyg editor
 *
 * @deprecated would be removed after 1.8
 * @see claro_html_textarea_editor
 *
 */
function claro_disp_html_area($name, $content = '', $rows=20, $cols=80, $optAttrib='')
{
    if(get_conf('CLARO_DEBUG_MODE',false) ) trigger_error('function claro_disp_html_area is deprecated, use claro_html_textarea_editor', E_USER_WARNING);
    // becomes a alias while the function call is not replaced by the new one
    return claro_html_textarea_editor($name,$content,$rows,$cols,$optAttrib);
}

/**
 * Displays the title of a tool. Optionally, there can be a subtitle below
 * the normal title, and / or a supra title above the normal title.
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 * @param  mixed $titleElement - it could either be a string or an array
 *                               containing 'supraTitle', 'mainTitle',
 *                               'subTitle'
 * @return void
 *
 * @deprecated in 1.8
 * @see claro_html_tool_title($titlePart, $helpUrl);
 */

function claro_disp_tool_title($titlePart, $helpUrl = false)
{
    if(get_conf('CLARO_DEBUG_MODE',false) ) trigger_error('function claro_disp_tool_title is deprecated, use claro_html_tool_title', E_USER_WARNING);

    return claro_html_tool_title($titlePart, $helpUrl);
}


/**
 * transform content in a html display
 * @param  - string $string string to htmlize
 * @return  - string htmlized
 */

function htmlize($phrase)
{
    return claro_parse_user_text(htmlspecialchars($phrase));
}

/**
 * replaces some dangerous character in a string for HTML use
 *
 * @param  string $string
 * @param  string $strict (optional) removes also scores and simple quotes
 * @return string : the string cleaned of dangerous character
 *
 */

function replace_dangerous_char($string, $strict = 'loose')
{
    $search[] = ' ';  $replace[] = '_';
    $search[] = '/';  $replace[] = '-';
    $search[] = '\\'; $replace[] = '-';
    $search[] = '"';  $replace[] = '-';
    $search[] = '\'';  $replace[] = '_';
    $search[] = '?';  $replace[] = '-';
    $search[] = '*';  $replace[] = '-';
    $search[] = '>';  $replace[] = '';
    $search[] = '<';  $replace[] = '-';
    $search[] = '|';  $replace[] = '-';
    $search[] = ':';  $replace[] = '-';
    $search[] = '$';  $replace[] = '-';
    $search[] = '(';  $replace[] = '-';
    $search[] = ')';  $replace[] = '-';
    $search[] = '^';  $replace[] = '-';
    $search[] = '[';  $replace[] = '-';
    $search[] = ']';  $replace[] = '-';
    $search[] = '..';  $replace[] = '';


    foreach($search as $key=>$char )
    {
        $string = str_replace($char, $replace[$key], $string);
    }

    if ($strict == 'strict')
    {
        $string = str_replace('-', '_', $string);
        $string = str_replace("'", '', $string);
        $string = strtr($string,
                        '¿¡¬√ƒ≈‡·‚„‰Â“”‘’÷ÿÚÛÙıˆ¯»… ÀËÈÍÎ«ÁÃÕŒœÏÌÓÔŸ⁄€‹˘˙˚¸ˇ—Ò',
                        'AAAAAAaaaaaaOOOOOOooooooEEEEeeeeCcIIIIiiiiUUUUuuuuyNn');
    }

    return $string;
}


/**
 * convert a duration in seconds to a human readable duration
 * @author SÈbastien Piraux <pir@cerdecam.be>
 * @param integer duration time in seconds to convert to a human readable duration
 */

function claro_disp_duration( $duration  )
{
    if( $duration == 0 ) return '0 '.get_lang('SecondShort');

    $days = floor(($duration/86400));
    $duration = $duration % 86400;

    $hours = floor(($duration/3600));
    $duration = $duration % 3600;

    $minutes = floor(($duration/60));
    $duration = $duration % 60;
    // $duration is now equal to seconds

    $durationString = '';

    if( $days > 0 ) $durationString .= $days . ' ' . get_lang('PeriodDayShort') . ' ';
    if( $hours > 0 ) $durationString .= $hours . ' ' . get_lang('PeriodHourShort') . ' ';
    if( $minutes > 0 ) $durationString .= $minutes . ' ' . get_lang('MinuteShort') . ' ';
    if( $duration > 0 ) $durationString .= $duration . ' ' . get_lang('SecondShort');

    return $durationString;
}


?>
