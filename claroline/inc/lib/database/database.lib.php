<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * Light-weight and extensible Object-Oriented Database Layer for Claroline
 * the main goal is to have some of the advantages of mysqli or pdo
 * and being compatible to the old Claroline kernel database connection.
 *
 * This library provides the following interfaces :
 * 
 * 1. Database_Connection interface provided to allow implementation of other
 * database connections
 * 2. Database_ResultSet interface provided to allow implementation of other
 * database result sets
 * 
 * This library provides the following classes :
 * 
 * 1. Claroline_Database_Connection is an adapter build upon the Claroline
 * kernel database connection and provided by the Claroline core class through
 * Claroline::getDatabase()
 * 2. Mysql_Database_connection is an adapater build upon the mysql extension
 * provided to connect to other databases
 * 3. Mysql_ResultSet implementation of Database_ResultSet to store and access
 * database query result based on mysql extension and used by both
 * Mysql_Database_Connection and Claroline_Database_Connection
 * 4. Database_Connection_Exception exception class specific to database
 * connections
 *
 * @version     1.9 $Revision$
 * @copyright   2001-2008 Universite catholique de Louvain (UCL)
 * @author      Claroline Team <info@claroline.net>
 * @author      Frederic Minne <zefredz@claroline.net>
 * @license     http://www.gnu.org/copyleft/gpl.html
 *              GNU GENERAL PUBLIC LICENSE version 2 or later
 * @package     database
 */

FromKernel::uses('database/pager.lib');

/**
 * Database Specific Exception
 */
class Database_Connection_Exception extends Exception{};

/**
 * Database_Connection generic interface
 */
interface Database_Connection
{
    /**
     * Connect to the database
     * @throws  Database_Connection_Exception
     */
    public function connect();
    
    /**
     * Select a database
     * @param   string $database database name
     * @throws  Database_Connection_Exception on failure
     */
    public function selectDatabase( $database );
    
    /**
     * Execute a query and returns the number of affected rows
     * @return  int
     * @throws  Database_Connection_Exception
     */
    public function exec( $sql );
    
    /**
     * Execute a query and returns the result set
     * @return  Database_ResultSet
     * @throws  Database_Connection_Exception
     */
    public function query( $sql );
    
    /**
     * Get a pager for the query
     * @return  Database_Pager
     * @throws  Database_Connection_Exception
     * @deprecated since Claroline 1.9.5, will be removed in 1.9.6 and 1.10
     */
    public function pager( $sql );
    
    /**
     * Returns the number of rows affected by the last query
     * @return  int
     * @throws  Database_Connection_Exception
     */
    public function affectedRows();
    
    /**
     * Get the ID generated from the previous INSERT operation
     * @return  int
     * @throws  Database_Connection_Exception
     */
    public function insertId();
    
    /**
     * Escape dangerous characters in the given string
     * @param   string $str
     * @return  string
     */
    public function escape( $str );
    
    /**
     * Escape dangerous characters and enquote the given string
     * @param   string $str
     * @return  string
     */
    public function quote( $str );
}

/**
 * Mysql specific Database_Connection
 */
class Mysql_Database_Connection implements Database_Connection
{
    protected $host, $username, $password, $database;
    protected $dbLink;
    
    /**
     * Create a new Mysql_Database_Connection instance
     * @param   string $host database host
     * @param   string $username database user name
     * @param   string $password database user password
     * @param   string $database name of the database to select (optional)
     */
    public function __construct( $host, $username, $password, $database = null )
    {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->database = $database;
        $this->dbLink = false;
    }
    
    protected function isConnected()
    {
        return !empty($this->dbLink);
    }
    
    /**
     * @see Database_Connection
     */
    public function connect()
    {
        if ( $this->isConnected() )
        {
            throw new Database_Connection_Exception("Already to database server {$this->username}@{$this->host}");
        }
        
        $this->dbLink = @mysql_connect( $this->host, $this->username, $this->password );
        
        if ( ! $this->dbLink )
        {
            throw new Database_Connection_Exception("Cannot connect to database server {$this->username}@{$this->host}");
        }
        
        if ( !empty( $this->database ) )
        {
            $this->selectDatabase( $this->database );
        }
    }
    
    /**
     * @see Database_Connection
     */
    public function selectDatabase( $database )
    {
        if ( ! $this->isConnected() )
        {
            throw new Database_Connection_Exception("No connection found to database server, please connect first");
        }
        
        if ( ! @mysql_select_db( $database, $this->dbLink ) )
        {
            throw new Database_Connection_Exception("Cannot select database {$database} on {$this->username}@{$this->host}");
        }
        
        $this->database = $database;
    }
    
    /**
     * @see Database_Connection
     */
    public function affectedRows()
    {
        if ( ! $this->isConnected() )
        {
            throw new Database_Connection_Exception("No connection found to database server, please connect first");
        }
        
        return @mysql_affected_rows( $this->dbLink );
    }
    
    /**
     * @see Database_Connection
     */
    public function insertId()
    {
        if ( ! $this->isConnected() )
        {
            throw new Database_Connection_Exception("No connection found to database server, please connect first");
        }
        
        return @mysql_insert_id( $this->dbLink );
    }
    
    /**
     * @see Database_Connection
     */
    public function exec( $sql )
    {
        if ( ! $this->isConnected() )
        {
            throw new Database_Connection_Exception("No connection found to database server, please connect first");
        }
        
        if ( false === @mysql_query( $sql ) )
        {
            throw new Database_Connection_Exception( "Error in {$sql} : ".@mysql_error($this->dbLink), @mysql_errno($this->dbLink) );
        }
        
        return $this->affectedRows();
    }
    
    /**
     * @see Database_Connection
     */
    public function query( $sql )
    {
        if ( ! $this->isConnected() )
        {
            throw new Database_Connection_Exception("No connection found to database server, please connect first");
        }
        
        if ( false === ( $result = @mysql_query( $sql ) ) )
        {
            throw new Database_Connection_Exception( "Error in {$sql} : ".@mysql_error($this->dbLink), @mysql_errno($this->dbLink) );
        }
        
        $tmp = new Mysql_ResultSet( $result );
        
        return $tmp;
    }
    
    /**
     * @see Database_Connection
     * @deprecated since Claroline 1.9.5, will be removed in 1.9.6 and 1.10
     */
    public function pager( $sql )
    {
        if ( ! $this->isConnected() )
        {
            throw new Database_Connection_Exception("No connection found to database server, please connect first");
        }
        
        $pager = new Mysql_Pager( $sql, $this );
        
        return $pager;
    }
    
    /**
     * @see Database_Connection
     */
    public function escape( $str )
    {
        return mysql_real_escape_string( $str, $this->dbLink );
    }
    
    /**
     * @see Database_Connection
     */
    public function quote( $str )
    {
        return "'".$this->escape($str)."'";
    }
}

/**
 * Claroline kernel database specific Database_Connection
 */
class Claroline_Database_Connection implements Database_Connection
{
    protected $dbLink;
    
    public function __construct( $dbLink )
    {
        $this->dbLink = $dbLink;
    }
    
    /**
     * @see Database_Connection
     */
    public function connect()
    {
        // already connected through claroline kernel
    }
    
    /**
     * @see Database_Connection
     */
    public function selectDatabase( $database )
    {
        if ( ! claro_sql_select_db( $database, $this->dbLink ) )
        {
            throw new Database_Connection_Exception("Cannot select database {$database} on {$this->username}@{$this->host}");
        }
    }
    
    /**
     * @see Database_Connection
     */
    public function affectedRows()
    {
        return claro_sql_affected_rows( $this->dbLink );
    }
    
    /**
     * @see Database_Connection
     */
    public function insertId()
    {
        return claro_sql_insert_id( $this->dbLink );
    }
    
    /**
     * @see Database_Connection
     */
    public function exec( $sql )
    {
        if ( ! claro_sql_query( $sql, $this->dbLink ) )
        {
            throw new Database_Connection_Exception( "Error in {$sql} : ".claro_sql_error(), claro_sql_errno() );
        }
        
        return $this->affectedRows();
    }
    
    /**
     * @see Database_Connection
     */
    public function query( $sql )
    {
        if ( false === ( $result = claro_sql_query( $sql, $this->dbLink ) ) )
        {
            throw new Database_Connection_Exception( "Error in {$sql} : ".claro_sql_error(), claro_sql_errno() );
        }
        
        $tmp = new Mysql_ResultSet( $result );
        
        return $tmp;
    }
    
    /**
     * @see Database_Connection
     * @deprecated since Claroline 1.9.5, will be removed in 1.9.6 and 1.10
     */
    public function pager( $sql )
    {
        if ( ! $this->isConnected() )
        {
            throw new Database_Connection_Exception("No connection found to database server, please connect first");
        }
        
        $pager = new Mysql_Pager( $sql, $this );
        
        return $pager;
    }
    
    /**
     * @see Database_Connection
     */
    public function escape( $str )
    {
        return claro_sql_escape( $str, $this->dbLink );
    }
    
    /**
     * @see Database_Connection
     */
    public function quote( $str )
    {
        return "'".claro_sql_escape( $str, $this->dbLink )."'";
    }
}

/**
 * Database_ResultSet generic interface
 */
interface Database_ResultSet extends SeekableIterator, Countable
{
    /**
     * Associative array fetch mode constant
     */
    const FETCH_ASSOC = MYSQL_ASSOC;
    
    /**
     * Numeric index array fetch mode constant
     */
    const FETCH_NUM = MYSQL_NUM;
    
    /**
     * Associative and numeric array fetch mode constant
     */
    const FETCH_BOTH = MYSQL_BOTH;
    
    /**
     * Object fetch mode constant
     */
    const FETCH_OBJECT = 'FETCH_OBJECT';
    
    /**
     * Fetch the value of the first column  of the first row of the result set
     */
    const FETCH_VALUE = 'FETCH_VALUE';
    
    /**
     * Fetch the value of the first column of each row of the result set
     */
    const FETCH_COLUMN = 'FETCH_COLUMN';
    
    /**
     * Set fetch mode
     * @param   mixed $mode fetch mode
     */
    public function setFetchMode( $mode );
    
    /**
     * Get the next row in the Result Set
     * @param   mixed $mode fetch mode (optional, use internal fetch mode :
     *      FETCH_ASSOC by default or set by setFetchMode())
     * @return  mixed result row, returned data type depends on fetch mode :
     *      FETCH_ASSOC, FETCH_NUM or FETCH_BOTH : array
     *      FETCH_OBJECT : object representation of the current row
     *      FETCH_VALUE : value of the first field in the current row
     */
    public function fetch( $mode = null );
    
    /**
     * Get the number of rows in the result set
     * @return  int
     */
    public function numRows();
    
    /**
     * Check if the result set is empty
     * @return  boolean
     */
    public function isEmpty();
}

/**
 * Mysql _Database_Connection Result Set class
 * implements iterator and countable interfaces for
 * array-like behaviour.
 */
class Mysql_ResultSet implements Database_ResultSet
{
    protected $mode;
    protected $idx;
    protected $valid;
    protected $numrows;
    protected $resultSet;
    
    /**
     * @param   resource $result Mysql native resultset
     * @param   mixed $mode fetch mode (optional, default FETCH_ASSOC)
     */
    public function __construct( $result, $mode = self::FETCH_ASSOC )
    {
        if ( $result )
        {
            $this->resultSet = $result;
            $this->mode = $mode;
            // set to 0 if false;
            $this->numrows = (int) @mysql_num_rows( $this->resultSet );
            $this->idx = 0;
        }
        else
        {
            throw new Database_Connection_Exception("Invalid SQL result passed to " . __CLASS__);
        }
    }
    
    public function __destruct()
    {
        if ( $this->resultSet )
        {
            @mysql_free_result($this->resultSet);
        }
        
        unset( $this->resultSet );
        unset( $this->numrows );
        unset( $this->mode );
        unset( $this->valid );
        unset( $this->idx );
    }
    
    // --- Database_ResultSet  ---
    
    /**
     * Set the fetch mode
     * @see     Database_ResultSet
     * @return  void
     */
    public function setFetchMode( $mode )
    {
        $this->mode = $mode;
    }
    
    /**
     * Get the number of rows in the result set
     * @see     Database_ResultSet
     * @return  int
     */
    public function numRows()
    {
        return $this->numrows;
    }
    
    /**
     * Check if the result set is empty
     * @see     Database_ResultSet
     * @return  boolean
     */
    public function isEmpty()
    {
        return (0 == $this->numRows());
    }
    
    /**
     * Get the next row in the Result Set
     * @see     Database_ResultSet
     * @param   mixed $mode fetch mode (optional, use internal fetch mode :
     *      FETCH_ASSOC by default or set by setFetchMode())
     * @return  mixed result row, returned data type depends on fetch mode :
     *      FETCH_ASSOC, FETCH_NUM or FETCH_BOTH : array
     *      FETCH_OBJECT : object representation of the current row
     *      FETCH_VALUE : value of the first field in the current row
     */
    public function fetch( $mode = null )
    {
        $mode = empty( $mode ) ? $this->mode : $mode;
        
        if ( $mode == self::FETCH_OBJECT )
        {
            return @mysql_fetch_object( $this->resultSet );
        }
        // FIXME : FETCH_VALUE should not be called twice !
        elseif ( $mode == self::FETCH_VALUE || $mode == self::FETCH_COLUMN )
        {
            $res = @mysql_fetch_array( $this->resultSet, self::FETCH_NUM );
            
            // use side effect of the [] operator : will return null if !$res
            return $res[0];
        }
        else
        {
            return @mysql_fetch_array( $this->resultSet, $mode );
        }
    }
    
    // --- Countable  ---
    
    /**
     * Count the number of rows in the result set
     * Usage :
     *      $size = count( $resultSet );
     * 
     * @see     Countable
     * @return  int size of the result set (ie number of rows)
     */
    public function count()
    {
        return $this->numRows();
    }
    
    // --- Iterator ---
    
    /**
     * Check if the current position in the result set is valid
     * @see     Iterator
     * @return  boolean
     */
    public function valid()
    {
        return $this->valid;
    }
    
    /**
     * Return the current row
     * @see     Iterator
     * @see     Database_ResultSet::fetch() for return value data type
     * @return  mixed, current row
     */
    public function current()
    {
        // Go to the correct data
        $this->seek( $this->idx );
        
        return $this->fetch( $this->mode );
    }
    
    /**
     * Advance to the next row in the result set
     * @see     Iterator
     */
    public function next()
    {
        $this->idx++;
        $this->valid = $this->idx < $this->numRows();
    }
    
    /**
     * Rewind to the first row
     * @see     Iterator
     */
    public function rewind()
    {
        $this->idx = 0;
        
        if ( $this->numRows() )
        {
            $this->valid = @mysql_data_seek( $this->resultSet, 0 );
        }
        else
        {
            $this->valid = false;
        }
    }
    
    /**
     * Return the index of the current row
     * @see     Iterator
     * @return  int
     */
    public function key()
    {
        return $this->idx;
    }
    
    // --- SeekableIterator ---
    
    /**
     * Usage :
     *      $resultSet->seek( 5 );
     *      $r = $resultSet->fetch();
     *      
     * @see     SeekableIterator
     * @param   int $idx
     * @return  void
     * @throws  OutOfBoundsException if invalid index
     */
    public function seek( $idx )
    {
        if ( $idx < $this->numRows() && $idx >= 0 && ! $this->isEmpty() && $this->valid() )
        {
            $this->idx = $idx;
            @mysql_data_seek( $this->resultSet, $this->idx );
        }
        else
        {
            throw new OutOfBoundsException('Invalid seek position');
        }
    }
}
