<?php // $Id$

/**
 * CLAROLINE
 *
 * CSV class
 *
 * @version     $Revision$
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @package     KERNEL
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
    public function  __construct ($delimiter, $quote)
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
     * Convert data array into csv string and send it to the user.
     *
     * @param string $filename
     * @param array $dataArray
     * @return string $csv
     */
    public function exportAndSend ($filename, $dataArray)
    {
        $csv = $this->export($dataArray);
        
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-Length: " . strlen($csv));
        
        // Output to browser with appropriate mime type
        header("Content-type: text/csv");
        header("Content-Disposition: attachment; filename=$filename");
        
        echo $csv;
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