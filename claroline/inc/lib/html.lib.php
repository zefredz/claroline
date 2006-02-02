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
 * @author see 'credits' file
 *
 * @package KERNEL
 *
 */

/**
 * This lib (in a class to simulate namespace) provide html stream for various
 * uniformised output.
 *
 */

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

function claro_disp_button($url, $text, $confirmMessage = '')
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
        return '<nobr>[ <a href="' . $url . '">' . $text . '</a> ]</nobr>';
    }
}

class claro_html
{

    /**
 * display a item list as vertical menu.
 *
 * @param array $itemList each item are include in a list.
 *
 * @return unknown
 */
    function menu_vertical($itemList)
    {
        // class="toollink"
        $htmlStream = '<UL class="menu vmenu">' . "\n";
        foreach($itemList as $item )
        {
            $htmlStream .= '<LI>' . "\n"
            .              $item
            .              '</LI>' . "\n"
            ;
        }
        $htmlStream .= '</UL>' . "\n";
        return $htmlStream;
    }


    /**
 * display a item list as vertical menu.
 *
 * @param array $itemList each item are include in a list.
 *
 * @return string : list content as an horizontal menu.
 */
    function menu_horizontal($itemList)
    {
        // class="toollink"
        $htmlStream = array();
        if(is_array($itemList))
        foreach ($itemList as $item )
        {
            $htmlStream[] = $item;
        }
        $htmlStream = implode( "\n" . '&nbsp;|&nbsp;' . "\n",$htmlStream);
        return $htmlStream;
    }

    /**
 * Return the claroline sytled url for a link to a tool
 *
 * @param string $url
 * @param string $label
 * @param array $attributeList array of array(attributeName,attributeValue)
 * @return string html stream
 */
    function tool_link($url,$label,$attributeList=array())
    {
        $attributeConcat = 'class="toollink" ';
        if (is_array($attributeList))
        {
            foreach ($attributeList as $attribute)
            {
                $attributeConcat .= $attribute['name'].'="'.$attribute['value'].'" ';
            }

        }
        return '<a href="' . $url . '" ' . $attributeConcat . ' >'
        .              $label
        .              '</a>' . "\n"
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

    function cmd_button($url, $text, $confirmMessage = '')
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
            return '<nobr>[ <a href="' . $url . '">' . $text . '</a> ]</nobr>';
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

    function tool_title($titlePart, $helpUrl = false)
    {
        // if titleElement is simply a string transform it into an array

        if ( is_array($titlePart) )
        {
            $titleElement = $titlePart;
        }
        else
        {
            $titleElement['mainTitle'] = $titlePart;
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


        if ( isset($titleElement['supraTitle']) )
        {
            $string .= '<small>' . $titleElement['supraTitle'] . '</small><br />' . "\n";
        }

        if ( isset($titleElement['mainTitle']) )
        {
            $string .= $titleElement['mainTitle'] . "\n";
        }

        if ( isset($titleElement['subTitle']) )
        {
            $string .= '<br /><small>' . $titleElement['subTitle'] . '</small>' . "\n";
        }

        $string .= '</h3>'."\n\n";

        return $string;
    }


/**
 * Prepare display of the message box appearing on the top of the window,
 * just    below the tool title. It is recommended to use this function
 * to display any confirmation or error messages, or to ask to the user
 * to enter simple parameters.
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 * @param string $message - include your self any additionnal html
 *                          tag if you need them
 * @return $string html string for a message box
 */

    function message_box($message)
    {
        return "\n".'<table class="claroMessageBox" border="0" cellspacing="0" cellpadding="10">'
        .      '<tr>'
        .      '<td>'
        .      $message
        .      '</td>'
        .      '</tr>'
        .      '</table>' . "\n\n"
        ;
    }


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
 */

/**
 * display data array in a <table>
 *
 * @param array $dataGrid array of data
 * @param array $option array of options
 * @return string html stream
 *
 * $dataGrid[]=array('nom'=>'dubois', 'prenom'=>'jean');
 * $dataGrid[]=array('nom'=>'dupont', 'prenom'=>'pol');
 * $dataGrid[]=array('nom'=>'durand', 'prenom'=>'simon');
 *
 * $option
 * * idLine      : deprecated (renamed to idLineType)
 * * idLineType  : choose between 'none', 'blank', 'numeric' (default)
 * * idLineShift : when idLineType is numeric shith the first line number (use when external pagined datagird)
 * * colTitleList: array of string  to replace the colKey as title of column
 * * colHead     : set the col to use as colHeading (use by scope)
 * * caption     : add the caption of the datagrid
 * * dispCounter : whether true, add a tfoot line with  count of  line in datagird.
 * * colAttributeList
 *               : array of attibute by column
 *
 */

function claro_disp_datagrid($dataGrid, $option = null)
{
    if(is_null($option) || ! is_array($option) )  $option=array();

    if (array_key_exists('idLine', $option)) die('idLine n\'est plus une option valide, il faut utiliser idLineType');

    if (! array_key_exists('idLineType',   $option)) $option['idLineType'] = 'numeric';
    if (! array_key_exists('idLineShift',  $option)) $option['idLineShift'] = 1;
    if (! array_key_exists('colHead',      $option))     $option['colHead'] = null;
    if (! array_key_exists('colTitleList', $option)) $option['colTitleList'] = array_keys($dataGrid[0]);
    if (array_key_exists('caption',      $option))   $option['caption'] = '<caption>' . $option['caption'] . '</caption>';
    else                                             $option['caption'] = '';

    $dispIdCol = true;

    //* manage idLine option

    switch (strtolower($option['idLineType']))
    {
        case 'blank'   : $idLineType = '';   break;
        case 'none'    : $dispIdCol = false; break;
        case 'numeric' : $internalkey = 0;   break;
        default        : $idLineType = '';   break;
    }


    $stream = '';
    if (is_array($dataGrid) && count($dataGrid))
    {

        /**
         * Build attributes for column
         *
         * In  W3C <COL> seems be the good usage but browser don't follow the tag
         *
         * So all attribute would be in each td of column.
         */

        foreach (array_keys($option['colTitleList']) as $col)
        {
            $attrCol[$col]='';
            if (key_exists('colAttributeList',$option))
            if (key_exists($col,$option['colAttributeList']))
            foreach ($option['colAttributeList'][$col] as $attriName => $attriValue )
            {
                $attrCol[$col] .=' '.$attriName.'="'.$attriValue.'" ';
            }
        }

        $stream .= '<table class="claroTable emphaseLine" width="100%" border="0" cellspacing="2">'
        // THEAD LINE
        .          '<thead>' . "\n"
        .          $option['caption']
        .          '<tr class="headerX" align="center" valign="top">' . "\n"
        ;

        if ($dispIdCol) $stream .= '<th width="10"></th>' . "\n";

        $i=0;
        foreach ($option['colTitleList'] as $colTitle)
        {
            $stream .= '<th scope="col" id="c' . $i++ . '" >' . $colTitle . '</th>' . "\n";
        }
        $stream .= '</tr>' . "\n"
        .          '</thead>' . "\n"
        ;

        if (array_key_exists('dispCounter',$option))
        {
            $stream .= '<tfoot>' . "\n"
            .          '<tr class="headerX" align="center" valign="top">' . "\n"
            .          '<td>' . "\n"
            .          '</td>' . "\n"
            .          '<td>' . "\n"
            .          count($dataGrid)
            .          '</td>' . "\n"
            .          '</tr>' . "\n"
            .          '</tr>' . "\n"
            .          '</tfoot>' . "\n"
            ;

        }

        $stream .= '<tbody>' . "\n";
        foreach ($dataGrid as $key => $dataLine )
        {
            switch ($option['idLineType'])
            {
                case 'key'     : $idLineType = $option['idLineShift'] + $key ;           break;
                case 'numeric' : $idLineType = $option['idLineShift'] + $internalkey++ ; break;
            }

            $stream .= '<tr>' . "\n";

            if ($dispIdCol) $stream .= '<td align="right" valign="middle">' . $idLineType . '</td>' . "\n";

            $i=0;
            foreach ($dataLine as $colId => $dataCell)
            {
                if ($option['colHead'] == $colId)
                {
                    $stream .= '<td scope="line" id="L' . $key . '" headers="c' . $i++ . '" ' . ( key_exists($colId,$attrCol)?$attrCol[$colId]:'') . '>';
                    $stream .= $dataCell;
                    $stream .= '</td>' . "\n";
                }
                else
                {
                    $stream .= '<td headers="c' . $i++ . ' L' . $key . '" ' . ( key_exists($colId,$attrCol)?$attrCol[$colId]:'') . '>';
                    $stream .= $dataCell;
                    $stream .= '</td>' . "\n";
                }
            }
            $stream .= '</tr>' . "\n";

        }
        $stream .= '</tbody>' . "\n"
        .          '</table>' . "\n"
        ;

    }

    return $stream;

}


//////////////////////////////////////////////////////////////////////////////
//                              DISPLAY OPTIONS
//                            student    view, title, ...
//////////////////////////////////////////////////////////////////////////////


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

function claro_disp_tool_title($titlePart, $helpUrl = false)
{
    // if titleElement is simply a string transform it into an array

    if ( is_array($titlePart) )
    {
        $titleElement = $titlePart;
    }
    else
    {
        $titleElement['mainTitle'] = $titlePart;
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


    if ( isset($titleElement['supraTitle']) )
    {
        $string .= '<small>' . $titleElement['supraTitle'] . '</small><br />' . "\n";
    }

    if ( isset($titleElement['mainTitle']) )
    {
        $string .= $titleElement['mainTitle'] . "\n";
    }

    if ( isset($titleElement['subTitle']) )
    {
        $string .= '<br /><small>' . $titleElement['subTitle'] . '</small>' . "\n";
    }

    $string .= '</h3>'."\n\n";

    return $string;
}


/**
 * Display options to switch between student view and course manager view
 * This function is mainly used by the claro_init_banner.inc.php file
 * The display mode command will only be displayed if
 * claro_set_tool_view_mode(true) has been previously called.
 * This will affect the return value of claro_is_allowed_to_edit() function.
 * It will ten return false as the user is a simple student.
 *
 * @author roan embrechts
 * @author Hugues Peeters
 * @param string - $viewModeRequested.
 *                 For now it can be 'STUDENT' or 'COURSE_ADMIN'
 * @see claro_is_allowed_to_edit()
 * @see claro_is_display_mode_available()
 * @see claro_set_display_mode_available()
 * @see claro_get_tool_view_mode()
 * @see claro_set_tool_view_mode()
 * @return true;
 */


function claro_disp_tool_view_option($viewModeRequested = false)
{
    global $clarolineRepositoryWeb, $is_courseAdmin;

    if ( ! $is_courseAdmin || ! claro_is_display_mode_available() ) return false;

    if ($viewModeRequested) claro_set_tool_view_mode($viewModeRequested);

    $currentViewMode = claro_get_tool_view_mode();

    /*------------------------------------------------------------------------
    PREPARE URL
    ------------------------------------------------------------------------*/

    /*
    * check if the REQUEST_URI contains already URL parameters
    * (thus a questionmark)
    */

    if ( strstr($_SERVER['REQUEST_URI' ], '?') ) $url = $_SERVER['REQUEST_URI' ];
    else                                         $url = $_SERVER['PHP_SELF'].'?';

    /*
    * remove previous view mode request from the url
    */

    $url = str_replace('&viewMode=STUDENT'     , '', $url);
    $url = str_replace('&viewMode=COURSE_ADMIN', '', $url);

    /*------------------------------------------------------------------------
    INIT BUTTONS
    -------------------------------------------------------------------------*/


    switch ($currentViewMode)
    {
        case 'COURSE_ADMIN' :

        $studentButton     = '<a href="' . $url . '&amp;viewMode=STUDENT">'
        . get_lang('Student')
        . '</a>'
        ;
        $courseAdminButton = '<b>' . get_lang('Course manager') . '</b>';

        break;

        case 'STUDENT' :

        $studentButton     = '<b>'.get_lang('Student').'</b>';
        $courseAdminButton = '<a href="' . $url . '&amp;viewMode=COURSE_ADMIN">'
        . get_lang('Course manager')
        . '</a>';
        break;
    }

    /*------------------------------------------------------------------------
    DISPLAY COMMANDS MENU
    ------------------------------------------------------------------------*/

    echo get_lang('View mode') . ' : '
    .    $studentButton
    .    ' | '
    .    $courseAdminButton
    ;

    return true;
}

/**
    Display    list of    messages

    @param $msgArrBody array of messages
    @author Christophe Gesché <moosh@claroline.net>
    @version 1.0

    Example    code for using this    in your    tools:
    $msgArrBody["nameOfCssClass"][]="foo";
.    css    class can be defined in    script but try to use
    class from    generic    css    ()
    error success warning
    ...
*/

function claro_disp_msg_arr($msgArrBody, $return=true)
{
    $msgBox = '';
    if (is_array($msgArrBody) && count($msgArrBody) > 0)
    {
        foreach ($msgArrBody as $classMsg => $thisMsgArr)
        {
            if( is_array($thisMsgArr) && count($thisMsgArr) > 0 )
            {
                $msgBox .= '<div class="' . $classMsg . '">';
                foreach ($thisMsgArr as $anotherThis) $msgBox .= '<div class="msgLine" >' . $anotherThis . '</div>';
                $msgBox .= '</div>';
            }
        }
        if($return) return claro_disp_message_box($msgBox);
        else        echo   claro_disp_message_box($msgBox);
    }
}


/**
 * Route the script to an auhtentication form if user id is missing.
 * Once authenticated, the system get back to the source where the form
 * was trigged
 *
 * @param boolean $cidRequired - if the course id is required to leave the form
 * @author Christophe gesché <moosh@claroline.net>
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 */

function claro_disp_auth_form($cidRequired = false)
{
    global $rootWeb, $includePath, $_cid;

    $sourceUrl = ( isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on'
    ? 'https://'
    : 'http://')
    .  $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];

    // note : some people say that REQUEST_URI isn't available on IIS.
    // It has to be checked  ...

    if ( ! headers_sent () )
    {
        $urlCmd = ($cidRequired && ! $_cid ? '&cidRequired=true' : '');
        header('Location:' . $rootWeb . 'claroline/auth/login.php?sourceUrl=' . urlencode($sourceUrl) . $urlCmd );
    }
    else // HTTP header has already been sent - impossible to relocate
    {
        echo '<p align="center">'
        .    'WARNING ! Login Required <br />'
        .    'Click '
        .    '<a href="' . $rootWeb . 'claroline/auth/login.php'
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
 * Prepare display of the message box appearing on the top of the window,
 * just    below the tool title. It is recommended to use this function
 * to display any confirmation or error messages, or to ask to the user
 * to enter simple parameters.
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 * @param string $message - include your self any additionnal html
 *                          tag if you need them
 * @return $string - the
 */

function claro_disp_message_box($message)
{
    return "\n".'<table class="claroMessageBox" border="0" cellspacing="0" cellpadding="10">'
    .      '<tr>'
    .      '<td>'
    .      $message
    .      '</td>'
    .      '</tr>'
    .      '</table>' . "\n\n"
    ;
}

/**
 * Allows to easily display a breadcrumb trail
 *
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param array $nameList bame of each breadcrumb
 * @param array $urlList url corresponding to the breadcrumb name above
 * @param string $separator (optionnal) element which segregate the breadcrumbs
 * @param string $homeImg (optionnal) source url for a home icon at the trail start
 * @return string the build breadcrumb trail
 */

function claro_disp_breadcrumbtrail($nameList, $urlList, $separator = ' &gt; ', $homeImg = null)
{
    // trail of only one element has no sense ...
    if (count ($nameList) < 2 ) return '<div class="breadcrumbTrail">&nbsp;</div>';

    $breadCrumbList = array();

    foreach($nameList as $thisKey => $thisName)
    {
        if (   array_key_exists($thisKey, $urlList)
        && ! is_null($urlList[$thisKey])       )
        {
            $startAnchorTag = '<a href="'.$urlList[$thisKey].'" target="_top">';
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
 * @author Piraux Sébastien <pir@cerdecam.be>
 *
 * @param integer $progress progression in pourcent
 * @param integer $factor will be multiply by 100 to have the full size of the bar
 * (i.e. 1 will give a 100 pixel wide bar)
 */

function claro_disp_progress_bar ($progress, $factor)
{
    global $clarolineRepositoryWeb, $imgRepositoryWeb;
    $maxSize  = $factor * 100; //pixels
    $barwidth = $factor * $progress ;

    // display progress bar
    // origin of the bar
    $progressBar = '<img src="' . $imgRepositoryWeb . 'bar_1.gif" width="1" height="12" alt="">';

    if($progress != 0)
    $progressBar .= '<img src="' . $imgRepositoryWeb . 'bar_1u.gif" width="' . $barwidth . '" height="12" alt="">';
    // display 100% bar

    if($progress!= 100 && $progress != 0)
    $progressBar .= '<img src="' . $imgRepositoryWeb . 'bar_1m.gif" width="1" height="12" alt="">';

    if($progress != 100)
    $progressBar .= '<img src="' . $imgRepositoryWeb . 'bar_1r.gif" width="' . ($maxSize - $barwidth) . '" height="12" alt="">';
    // end of the bar
    $progressBar .=  '<img src="' . $imgRepositoryWeb . 'bar_1.gif" width="1" height="12" alt="">';

    return $progressBar;
}

/**
 * Display a date at localized format
 * @author Christophe Gesché <gesche@ipm.ucl.ac.be>
 * @param formatOfDate
         see http://www.php.net/manual/en/function.strftime.php
         for syntax to use for this string
         I suggest to use the format you can find in trad4all.inc.php files
 * @param timestamp timestamp of date to format
 */

function claro_disp_localised_date($formatOfDate,$timestamp = -1) //PMAInspiration :)
{
    $langDay_of_weekNames['long'] = get_lang_weekday_name_list('long');
    $langDay_of_weekNames['short'] = get_lang_weekday_name_list('short');

    $langMonthNames['short'] = get_lang_month_name_list('short');
    $langMonthNames['long'] = get_lang_month_name_list('long');

    if ($timestamp == -1) $timestamp = claro_time();

    // avec un ereg on fait nous même le replace des jours et des mois
    // with the ereg  we  replace %aAbB of date format
    //(they can be done by the system when  locale date aren't aivailable

    $formatOfDate = ereg_replace('%[A]', $langDay_of_weekNames['long'][(int)strftime('%w', $timestamp)], $formatOfDate);
    $formatOfDate = ereg_replace('%[a]', $langDay_of_weekNames['short'][(int)strftime('%w', $timestamp)], $formatOfDate);
    $formatOfDate = ereg_replace('%[B]', $langMonthNames['long'][(int)strftime('%m', $timestamp)-1], $formatOfDate);
    $formatOfDate = ereg_replace('%[b]', $langMonthNames['short'][(int)strftime('%m', $timestamp)-1], $formatOfDate);
    return strftime($formatOfDate, $timestamp);
}

/**
 * convert a duration in seconds to a human readable duration
 * @author Sébastien Piraux <pir@cerdecam.be>
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
 * @author Sébastien Piraux <pir@cerdecam.be>
 */

function claro_disp_textarea_editor($name, $content = '', $rows=20, $cols=80, $optAttrib='')
{
    global $rootWeb, $rootSys;
    global $claro_editor;

    if( !isset($claro_editor) ) $claro_editor = 'tiny_mce';

    $returnString = '';

    // default value of htmlEditor
    if( !isset($_SESSION['htmlEditor']) ) $_SESSION['htmlEditor'] = 'enabled';

    // get content if in url
    if( isset($_REQUEST['areaContent']) ) $content = stripslashes($_REQUEST['areaContent']);

    // $claro_editor is the directory name of the editor
    $incPath = $rootSys . 'claroline/editor/'.$claro_editor;
    $editorPath = $rootWeb . 'claroline/editor/';
    $webPath = $editorPath . $claro_editor;

    if( file_exists($incPath . '/editor.class.php') )
    {
        // include editor class
        include_once $incPath . '/editor.class.php';

        // editor instance
        $editor = new editor($name,$content,$rows,$cols,$optAttrib,$webPath);

        if (claro_is_javascript_enabled())
        {
            if ( isset($_SESSION['htmlEditor']) && $_SESSION['htmlEditor'] != 'disabled' )
            {
                $switchState = 'off';
                $message     = get_lang('Disable text editor');
                $confirmCommand = "if(!confirm('".clean_str_for_javascript(get_lang('SwitchEditorToTextConfirm'))."'))return(false);";
            }
            else
            {
                $switchState = 'on';
                $message     = get_lang('Enable text editor');
                $confirmCommand = '';
            }

            $location = '\''
            .           $editorPath.'/editorswitcher.php?'
            .           'switch='.$switchState
            .           '&sourceUrl=' . urlencode($_SERVER['REQUEST_URI'])
            .           '&areaContent='
            .           '\''
            .           '+escape(document.getElementById(\''.$name.'\').value)'
            ;
            // use REQUEST_URI in href to avoid an ugly error if there is a javascript error in onclick
            $returnString .=
            "\n".'<div align="right">'
            .    '<small>'
            .    '<b>'
            .    '<a href="'.$_SERVER['REQUEST_URI'].'" '
            .     'onClick ="' . $confirmCommand . 'window.location='
            .    $location . ';return(false);">'
            .    $message
            .    '</a>'
            .    '</b>'
            .    '</small>'
            .    '</div>'."\n"
            ;
        }

        if( isset($_SESSION['htmlEditor']) && $_SESSION['htmlEditor'] != 'disabled' )
        {
            $returnString .= $editor->getAdvancedEditor();
        }
        else
        {
            // get standard text area
            $returnString .=
            '<textarea '
            .'id="'.$name.'" '
            .'name="'.$name.'" '
            .'style="width:100%" '
            .'rows="'.$rows.'" '
            .'cols="'.$cols.'" '
            .$optAttrib.' >'
            ."\n".$content."\n"
            .'</textarea>'."\n";
        }
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
        ."\n".$content."\n"
        .'</textarea>'."\n";
    }

    return $returnString;
}

function claro_disp_html_area($name, $content = '',
$rows=20, $cols=80,
$optAttrib='')
{
    // becomes a alias while the function call is not replaced by the new one
    return claro_disp_textarea_editor($name,$content,$rows,$cols,$optAttrib);
}


/**
 * function claro_build_nested_select_menu($name, $elementList)
 * Build in a relevant way 'select' menu for an HTML form containing nested data
 *
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 *
 * @param string $name, name of the select tag
 *
 * @param array nested data in a composite way
 *
 *  Exemple :
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
 * @return string the HTML flow
 * @desc depends on prepare option tags
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
 * fucntion
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 * @param array $elementList
 * @param int  $deepness (optionnal, default is 0)
 * @return array of option tag list
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


?>