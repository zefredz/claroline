<?php // $Id$

/**
 * CLAROLINE
 *
 * CSV exporter class.
 *
 * @version     Claroline 1.12 $Revision$
 * @copyright   (c) 2001-2014, Universite catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @package     kernel.utils
 * @author      Claro Team <cvs@claroline.net>
 */

class CsvExporter
{
    private $delimiter;
    private $quote;
    
    
    /**
     * Constructor.
     *
     * @param char $delimitor
     */
    public function __construct($delimiter, $quote)
    {
        $this->delimiter    = $delimiter;
        $this->quote        = $quote;
    }
    
    
    /**
     * Convert data array into csv string.
     *
     * @param array $dataArray
     * @return string $csv
     */
    public function export ($dataArray)
    {
        $csv = '';
        
        foreach ($dataArray as $row)
        {
            $csv .= sprintf("%s\n",
                implode($this->delimiter,
                    array_map(array('self', 'wrapWithQuotes'), $row)
                )
            );
        }
        
        return $csv;
    }
    
    
    /**
     * Wrap a row in quotes.
     *
     * @param <type> $row
     * @return <type>
     */
    public function wrapWithQuotes ($str)
    {
        $str = preg_replace('/"(.+)"/', '""$1""', $str);
        return sprintf($this->quote.'%s'.$this->quote, $str);
    }
}
