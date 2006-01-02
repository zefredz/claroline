<?php // $Id$
/**
 * CLAROLINE
 *
 * @version 1.8 $Revision$
 *
 * @copyright (c) 2001-2005 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @see http://www.claroline.net/wiki/CLCRS/
 *
 * @package COURSE
 * @since 28-nov.-2005
 *
 * @author Claro Team <cvs@claroline.net>
 * @author Christophe Gesché <moosh@claroline.net>
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
 */

function claro_disp_datagrid($dataGrid, $option = null)
{
    if(is_null($option) || ! is_array($option) )  $option=array();

    if (! array_key_exists('idLine',      $option)) $option['idLine'] = 'numeric';
    if (! array_key_exists('idLineShift', $option)) $option['idLineShift'] = 1;
    if (! array_key_exists('colTitleList', $option)) $option['colTitleList'] = array_keys($dataGrid[0]);



    $stream = '';
    if (is_array($dataGrid) && count($dataGrid))
    {
        $stream .= '<table class="claroTable emphaseLine" width="100%" border="0" cellspacing="2">'
        .          '<thead>' . "\n"
        .          '<tr class="headerX" align="center" valign="top">' . "\n"
        .          '<th>'
        .          '</th>' . "\n"
        ;
        $i=0;
        foreach ($option['colTitleList'] as $colTitle)
            $stream .= '<th scope="col" id="c' . $i++ . '" >' . $colTitle . '</th>' . "\n";

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

        $stream .= '<tbody>' . "\n"
        ;
        $internalkey = 0;
        foreach ($dataGrid as $key => $dataLine)
        {
            switch ($option['idLine'])
            {
                case 'blank'   : $idLine = '';   break;
                case 'key'     : $idLine = $key + $option['idLineShift']; break;
                case 'numeric' : $idLine = $internalkey++ + $option['idLineShift']; break;
                default        : $idLine = '';   break;
            }

            $stream .= '<tr>' . "\n"
            .          '<td>' . $idLine . '</td>' . "\n"
            ;
            $i=0;
            foreach ($dataLine as $dataCell)
            {
                $stream .= '<td headers="c' . $i++ . '">';
                $stream .= $dataCell;
                $stream .= '</td>' . "\n";
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
