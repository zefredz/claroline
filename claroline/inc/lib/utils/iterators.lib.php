<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * Iterator classes
 *
 * @version     Claroline 1.12 $Revision$
 * @copyright   (c) 2001-2014, Universite catholique de Louvain (UCL)
 * @author      Claroline Team <info@claroline.net>
 * @author      Frederic Minne <zefredz@claroline.net>
 * @license     http://www.gnu.org/copyleft/gpl.html
 *              GNU GENERAL PUBLIC LICENSE version 2 or later
 * @package     kernel.utils
 * @since       Claroline 1.11
 */

/**
 * Convert an array or iterator of [ value1, value2, ... ] to a set 
 * [ value1 => something, value2 => something, ... ]
 * @param mixed $iterator array or Iterator of string or int values
 * @param bool $useRowAsValue use the row itself as both the key and the value in the set
 * @param mixed $setValue value to use if $useRowAsValue is set to false
 * @return type
 */
function valuelist_to_set( $iterator, $useRowAsValue = true, $setValue = true )
{
    $set = array();
    
    foreach ( $iterator as $row )
    {
        $set[$row] = $useRowAsValue ? $rows : $setValue;
    }
    
    return $set;
}

/**
 * Define a countable iterator interface
 */
interface CountableIterator extends Countable, Iterator{};

/**
 * Define a countable and seekable iterator interface
 */
interface CountableSeekableIterator extends CountableIterator, SeekableIterator{};


/**
 * Define a generic array row to object array iterator
 * You must extends it and implement the current() method
 */
abstract class RowToObjectArrayIterator implements CountableIterator
{

  protected $collection = null;
  protected $currentIndex = 0;
  protected $maxIndex;
  protected $keys = null;
  
  /**
   *
   * @param Array $array 
   */
  public function __construct($array) 
  {

    $this->collection = $array;
    $this->maxIndex = count( $array );
    $this->keys = array_keys( $array );
  }
  
  /**
   * @see Iterator
   */
  public function key()
  {
    return $this->keys[$this->currentIndex];
  }
  
  /**
   * @see Iterator
   */
  public function next()
  {
    ++$this->currentIndex;
  }
  
  /**
   * @see Iterator
   */
  public function rewind()
  {
    $this->currentIndex = 0;
  }
  
  /**
   * @see Iterator
   */
  public function valid()
  {
    return ( isset($this->keys[$this->currentIndex]) );
  }
  
  /**
   * @see Countable
   */
  public function count()
  {
      return count($this->collection);
  }
  
}

/**
 * Define a generic row to object iterator iterator
 * You must extends it and implement the current() method
 */
abstract class RowToObjectIteratorIterator implements CountableIterator
{
    protected $internalIterator;
    
    /**
     * Constructor
     * @param CountableIterator $internalIterator
     */
    public function __construct(CountableIterator $internalIterator)
    {
        $this->internalIterator = $internalIterator;
    }
    
    /**
     * @see Iterator
     */
    public function next ()
    {
        return $this->internalIterator->next();
    }
    
    /**
     * @see Iterator
     */
    public function key ()
    {
        return $this->internalIterator->key();
    }
    
    /**
     * @see Iterator
     */
    public function valid ()
    {
        return $this->internalIterator->valid();
    }
    
    /**
     * @see Iterator
     */
    public function rewind ()
    {
        return $this->internalIterator->rewind();
    }
    
    /**
     * @see Countable
     */
    public function count ()
    {
        return count( $this->internalIterator );
    }
}
