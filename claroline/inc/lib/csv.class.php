<?php // $Id$

FromKernel::uses('csvexporter.class');

/**
 * CLAROLINE
 *
 * CSV class.
 *
 * This class will be correctly implemented soon, within CsvExporter and
 * CsvImporter classes.
 * Meanwhile, it will just act as a patch solution.
 *
 * @version     $Revision$
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @package     KERNEL
 * @author      Claro Team <cvs@claroline.net>
 */

class Csv extends CsvExporter
{
    public function __construct($delimiter, $quote)
    {
        parent::__construct($delimiter, $quote);
    }
}