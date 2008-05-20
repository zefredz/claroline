<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * Claroline database driver : uses Claroline Kernel database connection
 *
 * @version     1.9 $Revision$
 * @copyright   2001-2008 Universite catholique de Louvain (UCL)
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
    'driver'        => 'claroline',
    'main'          => 'ClaroMainDatabaseConnection',
    'connection'    => 'ClaroDatabaseConnection'
);

/**
 * MysqlDatabaseException
 */
class ClaroDatabaseException extends DatabaseException
{
    private $sql;
    
    /**
     * Constructor
     * @param   resource $dbLink link to the database connection
     * @param   string $sql query that generates the exception
     */
    public function __construct( $sql )
    {
        parent::__construct( mysql_error(), mysql_errno() );
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
 * MySQL database connection class
 * @see     DatabaseConnection
 */
class ClaroDatabaseConnection extends DatabaseConnection
{

    /**
     * Empty !
     */
    public function __construct(  )
    {
        // all is done by the kernel
    }

    public function connect( $forceReconnect = false )
    {
        return true;
    }

    public function close()
    {
        // empty
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
        
        $result = mysql_query( $query );

        if( mysql_errno() != 0 )
        {
            throw new ClaroDatabaseException( $query );
        }

        return $result;
    }

    public function getAllObjectsFromQuery( $query )
    {
        try
        {
            $result = $this->getQueryResult( $query );
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
        catch ( ClaroDatabaseException $mdbe )
        {
            throw $mdbe;
        }
    }

    public function getObjectFromQuery( $query )
    {
        try
        {
            $result = $this->getQueryResult( $query );
            
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
        catch ( ClaroDatabaseException $mdbe )
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
        catch ( ClaroDatabaseException $mdbe )
        {
            throw $mdbe;
        }
    }

    public function getRowFromQuery( $query )
    {
        try
        {
            $result = $this->getQueryResult( $query );
            
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
        catch ( ClaroDatabaseException $mdbe )
        {
            throw $mdbe;
        }
    }

    public function queryReturnsResult( $query )
    {
        try
        {
            $result = $this->getQueryResult( $query );
        
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
        catch ( ClaroDatabaseException $mdbe )
        {
            throw $mdbe;
        }
    }

    public function getLastInsertId()
    {
        return mysql_insert_id( );
    }
    
    public function getAffectedRows()
    {
        return mysql_affected_rows(  );
    }
    
    public function escapeString( $str )
    {
        return mysql_real_escape_string( $str );
    }
}

/**
 * Claroline main connection for Mysql driver
 * @see DatabaseConnection
 */
class ClaroMainDatabaseConnection extends ClaroDatabaseConnection
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
    
    public function __construct()
    {
        parent::__construct();
        $this->profiler = new Profiler;
    }
    
    public function connect( $forceReconnect = false )
    {
        // all is done by the kernel !
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
