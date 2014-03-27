<?php // $Id$

/**
 * CLAROLINE
 *
 * CSV class.
 *
 * This class will be correctly implemented soon, within CsvExporter and
 * CsvImporter classes.
 * Meanwhile, it will just act as a patch solution.
 *
 * @version     Claroline 1.12 $Revision$
 * @copyright   (c) 2001-2014, Universite catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @package     kernel.utils
 * @author      Claro Team <cvs@claroline.net>
 */

require_once __DIR__ . '/csvexporter.class.php';

class CsvRecordlistExporter // extends CsvExporter
{
    public $recordList = array();
    
    private $csvExporter;
    
    public function __construct($delimiter = ',', $quote = '"')
    {
        $this->csvExporter = new CsvExporter($delimiter, $quote);
    }
    
    /**
     * Export internal record list to csv
     * @return string
     */
    public function export()
    {
        return $this->csvExporter->export($this->recordList);
    }
}
