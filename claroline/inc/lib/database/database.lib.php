<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * Light Object-Oriented Database Layer for Claroline
 *
 * FIXME : move to inc/lib/database/database.lib.php and
 * replace old object-oriented database layer
 *
 * @version     1.9 $Revision$
 * @copyright   2001-2008 Universite catholique de Louvain (UCL)
 * @author      Claroline Team <info@claroline.net>
 * @author      Frederic Minne <zefredz@claroline.net>
 * @license     http://www.gnu.org/copyleft/gpl.html
 *              GNU GENERAL PUBLIC LICENSE version 2 or later
 * @package     database
 */

/**
 * Specific Exception
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
     * @return  MysqlResultSet
     * @throws  Database_Connection_Exception
     */
    public function query( $sql );
    
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
            $this->selectDatabase( $database );
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
        
        $tmp = new MysqlResultSet( $res );
        
        return $tmp;
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
        if ( ! claro_sql_select_db( $database ) )
        {
            throw new Database_Connection_Exception("Cannot select database {$database} on {$this->username}@{$this->host}");
        }
    }
    
    /**
     * @see Database_Connection
     */
    public function affectedRows()
    {
        return claro_sql_affected_rows();
    }
    
    /**
     * @see Database_Connection
     */
    public function insertId()
    {
        return claro_sql_insert_id();
    }
    
    /**
     * @see Database_Connection
     */
    public function exec( $sql )
    {
        if ( ! claro_sql_query( $sql ) )
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
        if ( false === ( $result = claro_sql_query( $sql ) ) )
        {
            throw new Database_Connection_Exception( "Error in {$sql} : ".claro_sql_error(), claro_sql_errno() );
        }
        
        $tmp = new MysqlResultSet( $result );
        
        return $tmp;
    }
    
    /**
     * @see Database_Connection
     */
    public function escape( $str )
    {
        return claro_sql_escape( $str );
    }
    
    /**
     * @see Database_Connection
     */
    public function quote( $str )
    {
        return "'".claro_sql_escape($str)."'";
    }
}

/**
 * Mysql _Database_Connection Result Set class
 * implements iterator and countable interfaces for
 * array-like behaviour.
 */
class MysqlResultSet implements Iterator, Countable
{
    protected $mode;
    protected $idx;
    protected $valid;
    protected $numrows;
    protected $resultSet;
    
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
     * Fetch the value of the first column of the result set
     */
    const FETCH_VALUE = 'FETCH_VALUE';
    
    /**
     * @param   resource $result Mysql native resultset
     * @param   mixed $mode fetch mode (optional, default FETCH_ASSOC)
     */
    public function __construct( $result, $mode = self::FETCH_ASSOC )
    {
        $this->resultSet = $result;
        $this->mode = $mode;
        // set to 0 if false;
        $this->numrows = (int) @mysql_num_rows( $this->resultSet );
        $this->idx = 0;
    }
    
    public function __destruct()
    {
        @mysql_free_result($this->resultSet);
        
        unset( $this->numrows );
        unset( $this->mode );
        unset( $this->valid );
        unset( $this->idx );
    }
    
    /**
     * Set fetch mode
     * @param   mixed $mode fetch mode
     */
    public function setFetchMode( $mode )
    {
        $this->mode = $mode;
    }
    
    /**
     * Get the number of rows in the result set
     * @return  int
     */
    public function numRows()
    {
        return $this->numrows;
    }
    
    /**
     * Check if the result set is empty
     * @return  boolean
     */
    public function isEmpty()
    {
        return (0 == $this->numRows());
    }
    
    /**
     * Get the next row in the Result Set
     * @param   mixed $mode fetch mode (optional, default FETCH_ASSOC)
     */
    public function fetch( $mode = self::FETCH_ASSOC )
    {
        if ( $mode == self::FETCH_OBJECT )
        {
            return mysql_fetch_object( $this->resultSet );
        }
        elseif ( $mode == self::FETCH_VALUE )
        {
            $res = mysql_fetch_array( $this->resultSet, self::FETCH_NUM );
            
            // use side effect of the [] operator : will return null if !$res
            return $res[0];
        }
        else
        {
            return mysql_fetch_array( $this->resultSet, $mode );
        }
    }
    
    // --- Countable methods ---
    
    /**
     * @see     Countable
     */
    public function count()
    {
        return $this->numRows();
    }
    
    // --- Iterator methods ---
    
    /**
     * @see     Iterator
     */
    public function valid()
    {
        return $this->valid;
    }
    
    /**
     * @see     Iterator
     */
    public function current()
    {
        // Go to the correct data
        @mysql_data_seek( $this->resultSet, $this->idx );
        
        return $this->fetch( $this->mode );
    }
    
    /**
     * @see     Iterator
     */
    public function next()
    {
        $this->idx++;
        $this->valid = $this->idx < $this->numrows;
    }
    
    /**
     * @see     Iterator
     */
    public function rewind()
    {
        $this->idx = 0;
        $this->valid = @mysql_data_seek( $this->resultSet, 0 );
    }
    
    /**
     * @see     Iterator
     */
    public function key()
    {
        return $this->idx;
    }
}
