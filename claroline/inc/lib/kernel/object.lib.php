<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * CLAROLINE
 *
 * "Magic" class to represent kernel objects. Defines __get, __set, __isset and
 * __unset magic methods.
 *
 * @version     Claroline 1.12 $Revision$
 * @copyright   (c) 2001-2014, Universite catholique de Louvain (UCL)
 * @author      Claroline Team <info@claroline.net>
 * @author      Frederic Minne <zefredz@claroline.net>
 * @license     http://www.gnu.org/copyleft/gpl.html
 *              GNU GENERAL PUBLIC LICENSE version 2 or later
 * @package     kernel.kernel
 */

abstract class KernelObject
{
    protected $_rawData = array();
    protected $sessionVarName;

    /**
     * Get the value of a property of the object. Magic method called by
     * $var = $obj->propertyName;
     * @param string $nm property name
     * @return mixed value of the property if the property exists, if not the
     * method returns null
     */
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

    /**
     * Prevent from changing the value of one of the object public property.
     * Magic method called by $obj->propertyName = $value;
     * @param string $nm
     * @param mixed $value
     * @throws Exception automaticaly ! (this object is read only)
     */
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

    /**
     * Magic method called by isset($obj->propertyName);
     * @param string $nm property name
     * @return boolean true if the property is set for the object
     */
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

    /**
     * Prevent from unsetting read only properties. Magic method called by
     * unset($obj->propertyName);
     * @param string $nm property name
     * @throws Exception automaticaly ! (this object is read only)
     */
    public function __unset( $nm )
    {
        throw new Exception("Cannot unset variable {$nm} : ".__CLASS__." is readonly !");
    }

    /**
     * Get the raw data of the object
     * @todo rewrite the kernel so thi method can be made 'protected'
     * @return array raw data contained in the object
     */
    public function getRawData()
    {
        return $this->_rawData;
    }
    
    public function saveToSession()
    {
        $_SESSION[$this->sessionVarName] = $this->_rawData;
        pushClaroMessage( "Kernel object {$this->sessionVarName} saved to session", 'debug' );
    }
    
    /**
     * Load user properties from session
     * @throws Exception if no data found in session
     */
    public function loadFromSession()
    {
        if ( !empty($_SESSION[$this->sessionVarName]) )
        {
            $this->_rawData = $_SESSION[$this->sessionVarName];
            pushClaroMessage( "Kernel object {$this->sessionVarName} loaded from session", 'debug' );
        }
        else
        {
            throw new Exception("Cannot load kernel object {$this->sessionVarName} from session");
        }
    }
    
    /**
     * Load the object
     * @param boolean $refresh refresh the cached data
     */
    public function load( $refresh = false )
    {
        if ( empty( $this->_rawData ) || $refresh )
        {
            $this->loadFromDatabase();
        }
    }
    
    /**
     * Load object from the database
     * Implementation tip : This method must populate _rawData with the loaded data
     * @fixme this method should retrun the loaded data as an array in order to populate _rawData within the load() method ! 
     */
    abstract protected function loadFromDatabase();
}
