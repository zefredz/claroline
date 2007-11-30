<?php // $Id$

    // vim: expandtab sw=4 ts=4 sts=4:
    
    /**
     * Mysql database driver
     *
     * @version     1.9 $Revision$
     * @copyright   2001-2007 Universite catholique de Louvain (UCL)
     * @author      Claroline Team <info@claroline.net>
     * @author      Frederic Minne <zefredz@claroline.net>
     * @license     http://www.gnu.org/copyleft/gpl.html
     *              GNU GENERAL PUBLIC LICENSE version 2 or later
     * @package     database
     */

    if ( count( get_included_files() ) == 1 )
    {
        die( 'The file ' . basename(__FILE__) . ' cannot be accessed directly, use include instead' );
    }
    
    uses ( 'database/database.lib' );
    
    $driverInfo = array(
        'driver'        => 'mysql',
        'main'          => 'ClaroMysqlDatabaseConnection',
        'connection'    => 'MysqlDatabaseConnection'
    );
    
    /**
     * MysqlDatabaseException
     */
    class MysqlDatabaseException extends DatabaseException
    {
        private $sql;
        
        /**
         * Constructor
         * @param   resource $dbLink link to the database connection
         * @param   string $sql query that generates the exception
         */
        public function __construct( $dbLink, $sql )
        {
            parent::__construct( mysql_error( $dbLink ), mysql_errno( $dbLink ) );
            $this->sql = $sql;
        }
        
        /**
         * Override the generic toString() method
         */
        public function __toString()
        {
            return __CLASS__." : {$this->sql} \n" . parent::__toString();
        }
    }
    
    /**
     * Row iterator for Mysql Driver
     * @see     DatabaseRowIterator
     */
    class MysqlRowsIterator implements DatabaseIterator
    {
        protected $_result;
        protected $_current;
        protected $_key = 0;
        protected $_size = 0;
        
        /**
         * Constructor
         * @param   resource $result
         */
        public function __construct( $result )
        {
            $this->_result = $result;
            $this->_size = mysql_num_rows( $result );
        }
        
        public function current()
        {
            return $this->_current;
        }
        
        public function rewind()
        {
            mysql_data_seek($this->_result, 0);
            $this->key = -1;
            $this->next();
        }
        
        public function valid()
        {
            return is_array( $this->_current );
        }
        
        public function next()
        {
            if ( false !== ( $res = mysql_fetch_assoc( $this->_result ) ) )
            {
                $this->_current = $res;
                $this->_key++;
            }
            else
            {
                throw new Exception('No result');
            }
        }
        
        public function key()
        {
            return $this->_key;
        }
        
        public function size()
        {
            return $this->_size;
        }
        
        public function toArray()
        {
            $ret= array();
                
            if ( mysql_num_rows( $this->_result ) > 0 )
            {
                while ( ( $item = mysql_fetch_assoc( $this->_result ) ) != false )
                {
                    $ret[] = $item;
                }
            }
            
            return $ret;
        }
    }
    
    /**
     * Object iterator for Mysql Driver
     * @see     DatabaseRowIterator
     */
    class MysqlObjectsIterator extends MysqlRowsIterator
    {
        public function next()
        {
            if ( false !== ( $res = mysql_fetch_object( $this->_result ) ) )
            {
                $this->_current = $res;
                $this->_key++;
            }
            else
            {
                throw new Exception('No result');
            }
        }
        
        public function valid()
        {
            return is_object( $this->_current );
        }
        
        public function toArray()
        {
            $ret= array();
                
            if ( mysql_num_rows( $this->_result ) > 0 )
            {
                while ( ( $item = mysql_fetch_object( $this->_result ) ) != false )
                {
                    $ret[] = $item;
                }
            }
            
            return $ret;
        }
    }
    
    /**
     * MySQL database connection class
     * @see     DatabaseConnection
     */
    class MysqlDatabaseConnection extends DatabaseConnection
    {        
        protected $db_link, $host, $username, $passwd, $dbname;

        /**
         * Constructor
         * @param   string host
         * @param   string username
         * @param   string passwd
         * @param   string dbname
         */
        public function __construct( $host, $username, $passwd, $dbname )
        {
            $this->db_link = null;
            $this->host = $host;
            $this->username = $username;
            $this->passwd = $passwd;
            $this->dbname = $dbname;
        }

        public function connect( $forceReconnect = false )
        {
            if ( $this->isConnected() && ! $forceReconnect )
            {
                return true;
            }

            $this->db_link = @mysql_connect(
                $this->host,
                $this->username,
                $this->passwd
            );

            if( ! $this->db_link )
            {
                throw new DatabaseException('Database connection failed!');
            }

            if( @mysql_select_db( $this->dbname, $this->db_link ) )
            {
                $this->connected = true;
                return true;
            }
            else
            {
                throw new DatabaseException('Database selection failed : ' . $this->dbname . '!');
            }
        }

        public function close()
        {
            if( $this->db_link != false )
            {
                @mysql_close( $this->db_link );
            }
            else
            {
                throw new DatabaseException('No database connection to close !');
            }
            $this->connected = false;
        }

        public function executeQuery( $query )
        {
            if ( ! $this->isConnected() )
            {
                $this->connect();
            }
            
            $result = mysql_query( $query, $this->db_link );

            if( mysql_errno( $this->db_link ) != 0 )
            {
                throw new MysqlDatabaseException( $this->db_link, $query );
            }

            return $result;
        }

        public function getAllObjectsFromQuery( $query )
        {
            try
            {
                $result = $this->executeQuery( $query );
                $ret= array();
                
                if ( @mysql_num_rows( $result ) > 0 )
                {
                    while( ( $item = @mysql_fetch_object( $result ) ) != false )
                    {
                        $ret[] = $item;
                    }
                }
    
                @mysql_free_result( $result );
    
                return $ret;
            }
            catch ( MysqlDatabaseException $mdbe )
            {
                throw $mdbe;
            }
        }

        public function getObjectFromQuery( $query )
        {
            try
            {
                $result = $this->executeQuery( $query );
                
                if ( false != ( $item = @mysql_fetch_object( $result ) ) )
                {
                    @mysql_free_result( $result );
    
                    return $item;
                }
                else
                {
                    return null;
                }
            }
            catch ( MysqlDatabaseException $mdbe )
            {
                throw $mdbe;
            }
        }

        public function getAllRowsFromQuery( $query )
        {
            try
            {
                $result = $this->executeQuery( $query );
                
                $ret= array();
    
                if ( @mysql_num_rows( $result ) > 0 )
                {
                    while ( ( $item = @mysql_fetch_assoc( $result ) ) != false )
                    {
                        $ret[] = $item;
                    }
                }
    
                @mysql_free_result( $result );
    
                return $ret;
            }
            catch ( MysqlDatabaseException $mdbe )
            {
                throw $mdbe;
            }
        }

        public function getRowFromQuery( $query )
        {
            try
            {
                $result = $this->executeQuery( $query );
                
                if ( false != ( $item = @mysql_fetch_assoc( $result ) ) )
                {
                    @mysql_free_result( $result );
    
                    return $item;
                }
                else
                {
                    return null;
                }
            }
            catch ( MysqlDatabaseException $mdbe )
            {
                throw $mdbe;
            }
        }

        public function queryReturnsResult( $query )
        {
            try
            {
                $result = $this->executeQuery( $query );
            
                if ( @mysql_num_rows( $result ) > 0 )
                {
                    @mysql_free_result( $result );

                    return true;
                }
                else
                {
                    @mysql_free_result( $result );

                    return false;
                }
            }
            catch ( MysqlDatabaseException $mdbe )
            {
                throw $mdbe;
            }
        }

        public function getLastInsertId()
        {
            return mysql_insert_id( $this->db_link );
        }
        
        public function getAffectedRows()
        {
            return mysql_affected_rows( $this->db_link );
        }
        
        public function escapeString( $str )
        {
            return mysql_real_escape_string( $str, $this->db_link );
        }
    }
    
    /**
     * Claroline main connection for Mysql driver
     * @see DatabaseConnection
     */
    class ClaroMysqlDatabaseConnection extends MysqlDatabaseConnection
    {
        const CLIENT_FOUND_ROWS = 2;
        // NOTE. CLIENT_FOUND_ROWS is required to make mysql_affected_rows()
        // work properly. When using UPDATE, MySQL will not update columns where the new
        // value is the same as the old value. This creates the possiblity that
        // mysql_affected_rows() may not actually equal the number of rows matched,
        // only the number of rows that were literally affected by the query.
        // But this behavior can be changed by setting the CLIENT_FOUND_ROWS flag in
        // mysql_connect(). mysql_affected_rows() will return then the number of rows
        // matched, even if none are updated.
        
        private $queryCounter = 1;
        private $profiler;
        
        public function __construct( $host, $username, $passwd, $dbname )
        {
            parent::__construct( $host, $username, $passwd, $dbname );
            $this->profiler = new Profiler;
        }
        
        public function connect( $forceReconnect = false )
        {
            if ( $this->isConnected() && ! $forceReconnect )
            {
                return true;
            }

            $this->db_link = @mysql_connect(
                $this->host,
                $this->username,
                $this->passwd,
                true,
                self::CLIENT_FOUND_ROWS
            );

            if( ! $this->db_link )
            {
                throw new DatabaseException('Database connection failed!');
            }

            if( @mysql_select_db( $this->dbname, $this->db_link ) )
            {
                $this->connected = true;
                return true;
            }
            else
            {
                throw new DatabaseException('Database selection failed : ' . $this->dbname . '!');
            }
        }
        
        public function executeQuery( $query )
        {
            if ( claro_debug_mode() && get_conf('CLARO_PROFILE_SQL',false) )
            {
                $this->profiler->restart();
                $this->profiler->mark(
                    __FILE__,
                    __LINE__,
                    '<br />'.nl2br(htmlspecialchars($query))
                );
            }
            
            try
            {
                $result = parent::executeQuery( $query );
                if ( claro_debug_mode() && get_conf('CLARO_PROFILE_SQL',false) )
                {
                    $this->profiler->stop();
                    $duration = $this->profiler->getElapsedTime();
                    
                    $info = 'execution time : ' . ($duration > 0.001
                        ? '<b>' . round($duration,4) . '</b>'
                        : '&lt;0.001')  . '&#181;s'
                        ;
    
                    $info .= ': affected rows :' . $this->getAffectedRows();
    
                    Console::debug( '<br />Query counter : <b>'
                        . $this->queryCounter++ . '</b> : ' . $info . '<br />'
                        . '<code><span class="sqlcode">' . nl2br($query)
                        . '</span></code>' );
    
                }
    
                return $result;
            }
            catch ( Exception $e )
            {
                Console::error($e);
                throw $e;
            }
        }
    }    
?>