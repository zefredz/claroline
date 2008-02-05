<?php // $Id$

    // vim: expandtab sw=4 ts=4 sts=4:
    
    /**
     * Mysqli database driver
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
        'driver'        => 'mysqli',
        'main'          => 'ClaroMysqliDatabaseConnection',
        'connection'    => 'MysqliDatabaseConnection'
    );
    
    /**
     * MysqlConnectionException
     */
    class MysqliConnectException extends DatabaseException
    {
        private $host, $user;
        
        /**
         * Constructor
         * @param   string $host server host
         * @param   string $user server user name
         */
        public function __construct( $host, $user )
        {
            parent::__construct( mysqli_connect_error(), mysqli_connect_errno() );
            $this->host = $host;
            $this->user = $user;
        }
        
        public function __toString()
        {
            return "Database connection failed! for {$this->user}@{$this->host} : " 
                . parent::__toString();
        }
    }
    
    /**
     * MysqlDatabaseException
     */
    class MysqliDatabaseException extends DatabaseException
    {
        private $sql;
        
        /**
         * Constructor
         * @param   mysqli $db link to the database connection
         * @param   string $sql query that generates the exception
         */
        public function __construct( $db, $sql )
        {
            parent::__construct( $db->error, $db->errno );
            $this->sql = $sql;
        }
        
        public function __toString()
        {
            return __CLASS__." : {$this->sql} \n" . parent::__toString();
        }
    }
    
    /**
     * MySQL database connection class
     * @see     DatabaseConnection
     */
    class MysqliDatabaseConnection extends DatabaseConnection
    {        
        protected $db, $host, $username, $passwd, $dbname;

        /**
         * Constructor
         * @param   string host
         * @param   string username
         * @param   string passwd
         * @param   string dbname
         */
        public function __construct( $host, $username, $passwd, $dbname )
        {
            $this->db = null;
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

            $this->db = new mysqli(
                $this->host,
                $this->username,
                $this->passwd,
                $this->dbname
            );

            if( ! $this->db )
            {
                throw new MysqliConnectException( $this->host, $this->username );
            }
            else
            {
                $this->connected = true;
                return true;
            }
        }

        public function close()
        {
            if( $this->db )
            {
                $this->db->close();
                $this->connected = false;
            }
            else
            {
                throw new DatabaseException('No database connection to close !');
            }
        }
        
        public function executeQuery( $query )
        {
            $this->getQueryResult( $query );

            return $this->getAffectedRows();
        }

        protected function getQueryResult( $query )
        {
            if ( ! $this->isConnected() )
            {
                $this->connect();
            }
            
            $result = $this->db->query( $query );

            if( $this->db->errno )
            {
                throw new MysqliDatabaseException( $this->db, $query );
            }

            return $result;
        }

        public function getAllObjectsFromQuery( $query )
        {
            try
            {
                $result = $this->getQueryResult( $query );
                $ret= array();
                
                if ( $result->num_rows > 0 )
                {
                    while( ( $item = $result->fetch_object() ) != false )
                    {
                        $ret[] = $item;
                    }
                }
    
                $result->close();
    
                return $ret;
            }
            catch ( DatabaseException $mdbe )
            {
                throw $mdbe;
            }
        }

        public function getObjectFromQuery( $query )
        {
            try
            {
                $result = $this->getQueryResult( $query );
                
                if ( false != ( $item = $result->fetch_object() ) )
                {
                    $result->close();
    
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
                $result = $this->getQueryResult( $query );
                
                $ret= array();
                
                if ( $result->num_rows > 0 )
                {
                    while ( ( $item = $result->fetch_assoc() ) != false )
                    {
                        $ret[] = $item;
                    }
                }
    
                $result->close();
                
                return $ret;
            }
            catch ( DatabaseException $mdbe )
            {
                throw $mdbe;
            }
        }

        public function getRowFromQuery( $query )
        {
            try
            {
                $result = $this->getQueryResult( $query );
                
                if ( false != ( $item = $result->fetch_assoc() ) )
                {
                    $result->close();
    
                    return $item;
                }
                else
                {
                    return null;
                }
            }
            catch ( DatabaseException $mdbe )
            {
                throw $mdbe;
            }
        }

        public function queryReturnsResult( $query )
        {
            try
            {
                $result = $this->getQueryResult( $query );
            
                if ( $result->num_rows > 0 )
                {
                    $result->close();

                    return true;
                }
                else
                {
                    $result->close();

                    return false;
                }
            }
            catch ( DatabaseException $mdbe )
            {
                throw $mdbe;
            }
        }

        public function getLastInsertId()
        {
            return $this->db->insert_id;
        }
        
        public function getAffectedRows()
        {
            return $this->db->affected_rows;
        }
        
        public function escapeString( $str )
        {
            return $this->db->real_escape_string( $str );
        }
    }
    
    /**
     * Claroline main connection for Mysqli driver
     * @see DatabaseConnection
     */
    class ClaroMysqliDatabaseConnection extends MysqliDatabaseConnection
    {
        private $queryCounter = 1;
        private $profiler;
        
        public function __construct( $host, $username, $passwd, $dbname )
        {
            parent::__construct( $host, $username, $passwd, $dbname );
            $this->profiler = new Profiler;
        }
        
        protected function getQueryResult( $query )
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
                $query = ClarolineDatabase::toClaroQuery( $query );
                
                $result = parent::getQueryResult( $query );
                
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