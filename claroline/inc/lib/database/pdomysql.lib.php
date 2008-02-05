<?php // $Id$

    // vim: expandtab sw=4 ts=4 sts=4:
    
    /**
     * PDO Driver
     *
     * @version     1.9 $Revision$
     * @copyright   2001-2007 Universite catholique de Louvain (UCL)
     * @author      Claroline Team <info@claroline.net>
     * @author      Frederic Minne <zefredz@claroline.net>
     * @license     http://www.gnu.org/copyleft/gpl.html
     *              GNU GENERAL PUBLIC LICENSE version 2 or later
     * @package     PACKAGE_NAME
     */

    if ( count( get_included_files() ) == 1 )
    {
        die( 'The file ' . basename(__FILE__) . ' cannot be accessed directly, use include instead' );
    }
    
    uses ( 'database/database.lib' );
    
    $driverInfo = array(
        'driver'        => 'pdomysql',
        'main'          => 'ClaroPdoMysqlDatabaseConnection',
        'connection'    => 'PdoMysqlDatabaseConnection'
    );
    
    class PdoMysqlConnectException extends DatabaseException
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
    
    class PdoMysqlDatabaseConnection extends DatabaseConnection
    {
        protected $host, $username, $passwd, $dbname;
        protected $_affectedRows = 0;
        protected $db;
        
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
            
            $dsn = 'mysql:'
                . 'host='.$this->host.';'
                . 'dbname='.$this->dbname.';'
                ;
                
            $this->db = new PDO ( $dsn, $this->username, $this->passwd );
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            if( ! $this->db )
            {
                throw new PdoMysqlConnectException( $this->host, $this->username );
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
                $this->connected = false;
                unset($this->db);
                $this->db = null;
            }
            else
            {
                throw new DatabaseException('No database connection to close !');
            }
        }
        
        public function executeQuery( $query )
        {
            if ( ! $this->isConnected() )
            {
                $this->connect();
            }
            
            $result = $this->db->exec( $query );
            
            $this->_affectedRows = $result;

            return $result;
        }
        
        protected function getQueryResult( $query )
        {
            if ( ! $this->isConnected() )
            {
                $this->connect();
            }
            
            $result = $this->db->query( $query );
            
            $this->_affectedRows = $result->rowCount();

            return $result;
        }
        
        public function getAllObjectsFromQuery( $query )
        {
            $result = $this->getQueryResult( $query );
            $result->setFetchMode(PDO::FETCH_OBJECT );
            
            $ret= array();
            
            if ( $result->rowCount() > 0 )
            {
                while( ( $item = $result->fetch() ) != false )
                {
                    $ret[] = $item;
                }
            }

            $result->closeCursor();

            return $ret;
        }
        
        public function getObjectFromQuery( $query )
        {
            $result = $this->getQueryResult( $query );
            $result->setFetchMode(PDO::FETCH_OBJECT );
            
            $ret= array();
            
            if ( false != ( $item = $result->fetch() ) )
            {
                $result->closeCursor();

                return $item;
            }
            else
            {
                return null;
            }

            $result->closeCursor();

            return $ret;
        }
        
        public function getAllRowsFromQuery( $query )
        {
            $result = $this->getQueryResult( $query );
            $result->setFetchMode(PDO::FETCH_ASSOC );
            
            $ret= array();
            
            if ( $result->rowCount() > 0 )
            {
                while( ( $item = $result->fetch() ) != false )
                {
                    $ret[] = $item;
                }
            }

            $result->closeCursor();

            return $ret;
        }
        
        public function getRowFromQuery( $query )
        {
            $result = $this->getQueryResult( $query );
            $result->setFetchMode(PDO::FETCH_ASSOC );
            
            $ret= array();
            
            if ( false != ( $item = $result->fetch() ) )
            {
                $result->closeCursor();

                return $item;
            }
            else
            {
                return null;
            }

            $result->closeCursor();

            return $ret;
        }
        
        public function queryReturnsResult( $query )
        {
            $result = $this->getQueryResult( $query );
            
            if ( $result->rowCount() > 0 )
            {
                $result->closeCursor();

                return true;
            }
            else
            {
                $result->closeCursor();

                return false;
            }
        }
        
        public function getLastInsertId()
        {
            return $this->db->lastInsertId();
        }
        
        public function getAffectedRows()
        {
            return $this->_affectedRows;
        }
    }
    
    /**
     * Claroline main connection for Mysqli driver
     * @see DatabaseConnection
     */
    class ClaroPdoMysqlDatabaseConnection extends PdoMysqlDatabaseConnection
    {
        private $queryCounter = 1;
        private $profiler;
        
        public function __construct( $host, $username, $passwd, $dbname )
        {
            parent::__construct( $host, $username, $passwd, $dbname );
            $this->profiler = new Profiler;
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