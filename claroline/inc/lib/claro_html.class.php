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
                        .              '</a>'
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
        $htmlStream = implode(' | ',$htmlStream);
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
?>