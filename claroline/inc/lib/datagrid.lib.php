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
 */

function claro_disp_datagrid($dataGrid, $option = null)
{
    if(is_null($option) || ! is_array($option) )  $option=array();

    if (! array_key_exists('idLineShift', $option)) $option['idLineShift'] = 1;
    if (! array_key_exists('colTitleList', $option)) $option['colTitleList'] = array_keys($dataGrid[0]);
    if (! array_key_exists('idLine',      $option)) $option['idLine'] = 'numeric';

    $dispIdCol = true;
    switch (strtolower($option['idLine']))
    {
        case 'blank'   : $idLine = '';       break;
        case 'none'    : $dispIdCol = false; break;
        case 'numeric' : $internalkey = 0;   break;
        default        : $idLine = '';       break;
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
        .          '<tr class="headerX" align="center" valign="top">' . "\n"
        ;

        if ($dispIdCol) $stream .= '<th></th>' . "\n";

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
            switch ($option['idLine'])
            {
                case 'key'     : $idLine = $option['idLineShift'] + $key ;           break;
                case 'numeric' : $idLine = $option['idLineShift'] + $internalkey++ ; break;
            }

            $stream .= '<tr>' . "\n";

            if ($dispIdCol) $stream .= '<td>' . $idLine . '</td>' . "\n";

            $i=0;
            foreach ($dataLine as $colId => $dataCell)
            {
                $stream .= '<td headers="c' . $i++ . '" ' . ( key_exists($colId,$attrCol)?$attrCol[$colId]:'') . '>';
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