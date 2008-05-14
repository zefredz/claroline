<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * File Finder Library
 * TODO Add to kernel !
 *
 * @version     1.9 $Revision$
 * @copyright   2001-2007 Universite catholique de Louvain (UCL)
 * @author      Frederic Minne <zefredz@claroline.net>
 * @license     http://www.gnu.org/copyleft/gpl.html
 *              GNU GENERAL PUBLIC LICENSE version 2 or later
 * @package     kernel
 */

class FileFinder extends FilterIterator
{
    protected $searchString;

    public function __construct( $path, $searchString, $recursive = true )
    {
        $this->searchString = $searchString;

        if ( ! $recursive )
        {
            parent::__construct(
                new IteratorIterator(
                    new DirectoryIterator($path) ) );
        }
        else
        {
             parent::__construct(
                new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($path)));
        }
    }

    public function getSearchString()
    {
        return $this->searchString;
    }

    public function accept()
    {
        return !strcmp($this->getSearchString(), $this->current() );
    }
}

class RegexpFileFinder extends FileFinder
{
    public function accept()
    {
        return preg_match( $this->current(), $this->getSearchString() );
    }
}

class ExtensionFileFinder extends FileFinder
{
    public function accept()
    {
        return ( substr( $this->current(), - ( strlen($this->getSearchString()) ) )
            == $this->getSearchString() );
    }
}
?>