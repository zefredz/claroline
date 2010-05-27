<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * Description
 *
 * @version     1.9 $Revision$
 * @copyright   2001-2008 Universite catholique de Louvain (UCL)
 * @author      Claroline Team <info@claroline.net>
 * @author      Frederic Minne <zefredz@claroline.net>
 * @license     http://www.gnu.org/copyleft/gpl.html
 *              GNU GENERAL PUBLIC LICENSE version 2 or later
 * @package     KERNEL
 */

if ( count( get_included_files() ) == 1 )
{
    die( 'The file ' . basename(__FILE__) . ' cannot be accessed directly, use include instead' );
}

class KernelObject
{
    protected $_rawData = array();
    
    public function __get( $nm )
    {
        if ( isset ( $this->_rawData[$nm] ) )
        {
            return $this->_rawData[$nm];
        }
        else
        {
            return null;
        }
    }
    
    public function __set( $nm, $value )
    {
        if ( $nm === '_rawData' )
        {
            $this->_rawData = $value;
        }
        else
        {
            throw new Exception("Cannot change variable {$nm} : ".__CLASS__." is readonly !");
        }
    }
    
    public function __isset( $nm )
    {
        if ( isset ( $this->_rawData[$nm] ) )
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    
    public function __unset( $nm )
    {
        throw new Exception("Cannot unset variable {$nm} : ".__CLASS__." is readonly !");
    }
    
    public function getRawData()
    {
        return $this->_rawData;
    }
}
