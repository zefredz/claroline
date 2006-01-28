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
            $htmlStream .= '<LI>' . "\n";
            if (is_array($item))
            {
                switch($item['type'])
                {
                    case 'link' :
                    {
                        $htmlStream .= '<a href="' . $item['url'] . '" ' . $item['attribute'] . ' >'
                        .              $item['label']
                        .              '</a>'
                        ;

                    } break;
                    case 'button' :
                    {
                        if(!isset($item['confirmMessage'])) $item['confirmMessage']='';
                        $htmlStream .=  claro_disp_button($item['url'], $item['label'],$item['confirmMessage']);

                    } break;
                }
            }
            else
            {
                $htmlStream .= $item;
            }

            $htmlStream .= '</LI>' . "\n"
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
            if (is_array($item))
            {
                switch($item['type'])
                {
                    case 'link' :
                    {
                        $htmlStream[] = '<a href="' . $item['url'] . '" ' . $item['attribute'] . ' >'
                        .              $item['label']
                        .              '</a>' . "\n"
                        ;

                    } break;
                    case 'button' :
                    {
                        if(!isset($item['confirmMessage'])) $item['confirmMessage']='';
                        $htmlStream[] = claro_disp_button($item['url'], $item['label'],$item['confirmMessage']);
                    } break;

                }
            }
            else
            {
                $htmlStream[] = $item;
            }

        }
        $htmlStream = implode( "\n" . '&nbsp;|&nbsp;' . "\n",$htmlStream);
        return $htmlStream;
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


?>