<?php // $Id$

    // vim: expandtab sw=4 ts=4 sts=4:

    if ( count( get_included_files() ) == 1 )
    {
        die( 'The file ' . basename(__FILE__) . ' cannot be accessed directly, use include instead' );
    }

    /**
     * Database abstraction layer
     *
     * @version     1.9 $Revision$
     * @copyright   2001-2007 Universite catholique de Louvain (UCL)
     * @author      Claroline Team <info@claroline.net>
     * @author      Frederic Minne <zefredz@claroline.net>
     * @license     http://www.gnu.org/copyleft/gpl.html
     *              GNU GENERAL PUBLIC LICENSE version 2 or later
     * @package     database
     */
    
    /**
     * Database Exception class 
     */ 
    class DatabaseException extends Exception
    {
    }
    
    /**
     * Database driver management and connection factory class
     * Usage :
     *  1. load driver : Database::loadDriver('mysqli');
     *  2. get main claroline connection : $conn = Database::getMainConnection(); 
     *  or 3. get other database connection : $conn = Database::getConnection( 
     *      $host, $user, $pass, $dbname );
     */
    class Database
    {
        // Driver management 
        
        private static $_loadedDriver = false;
        
        /**
         * Load the database driver
         * @param   string $name driver name
         * @throws  Exception if driver not found or already loaded
         */
        public static function loadDriver( $name )
        {
            if ( ! self::$_loadedDriver )
            {
                if ( file_exists( dirname(__FILE__).'/'.$name.'.lib.php' ) )
                {
                    $driverInfo = false;
                    require_once dirname(__FILE__).'/'.$name.'.lib.php';
                    
                    self::$_loadedDriver = $driverInfo;
                    
                    Console::debug('Load database driver ' . $name);
                }
                else
                {
                    throw new Exception("Database driver $name not found!");
                }                    
                
            }
            else
            {
                throw new Exception('Database driver allready loaded !');
            }
        }
        
        // Main connection
        
        private static $_mainInstance = false;
        
        /**
         * Get the main database connection
         * @return  DatabaseConnection main connection
         * @throws  DatabaseException on error
         */
        public static function getMainConnection()
        {
            if ( ! self::$_loadedDriver )
            {
                throw new Exception('No database driver loaded !');
            }
            else
            {
                if ( ! self::$_mainInstance )
                {
                    $className = self::$_loadedDriver['main'];
                    self::$_mainInstance = new $className( 
                        get_conf( 'dbHost' ),
                        get_conf( 'dbLogin' ),
                        get_conf( 'dbPass' ),
                        get_conf( 'mainDbName' )
                    );
                }
                
                return self::$_mainInstance;
            }
        }
        
        // Standard connection
        
        /**
         * Get a database connection instance
         * @param   string $host database server host
         * @param   string $user database user account name
         * @param   string $passwd database user account password
         * @param   string $database name of the database to connect to
         * @return  DatabaseConnection
         * @throws  DatabaseException
         */
        public static function getConnection( $host, $user, $passwd, $dbname )
        {
            if ( ! self::$_loadedDriver )
            {
                throw new Exception('No database driver loaded !');
            }
            else
            {
                $className = self::$_loadedDriver['connection'];
                $_instance = new $className( $host, $user, $passwd, $dbname  );
                return $_instance;
            }
        }
    }
    
    /**
     * Abstract database connection class
     */
    abstract class DatabaseConnection
    {
        protected $connected = false;

        /**
         * open a connection to the database
         * @param   boolean $forceReconnect
         * @return  boolean
         * @throws  DatabaseException
         */
        abstract public function connect( $forceReconnect = false );

        /**
         * reconnect a connection to the database
         * @return  boolean
         * @throws  DatabaseException
         */
        public function reconnect()
        {
            return $this->connect(true);
        }

        /**
         * check if the connection is active
         * @return  boolean true if the conection is active, false either
         * @throws  DatabaseException
         */
        public function isConnected()
        {
            return $this->connected;
        }

        /**
         * close the connection
         * @throws  DatabaseException
         */
        abstract public function close();

        /**
         * execute a query to the database and returns the number of
         * rows affected
         * @param   string $query sql query
         * @return  int affacted rows
         * @throws  DatabaseException
         */
        abstract public function executeQuery( $query );
        
        /**
         * execute a query to the database and returns the result
         * @param   string $query sql query
         * @return  mixed execution result
         * @throws  DatabaseException
         */
        abstract protected function getQueryResult( $query );

        /**
         * execute the given query and returns all results as objects in an array
         * @param   string $query
         * @return  array of objects
         * @throws  DatabaseException
         */
        abstract public function getAllObjectsFromQuery( $query );

        /**
         * execute the given query and returns one result as an object
         * @param   string $query
         * @return  object
         * @throws  DatabaseException
         */
        abstract public function getObjectFromQuery( $query );

        /**
         * execute the given query and returns all returned rows in an array
         * @param   string $query
         * @return  array of rows
         * @throws  DatabaseException
         */
        abstract public function getAllRowsFromQuery( $query );

        /**
         * execute the given query and returns one row in an array
         * @param   string $query
         * @return  array row
         * @throws  DatabaseException
         */
        abstract public function getRowFromQuery( $query );

        /**
         * execute a database query and return true if the query returns a
         * result
         * @param   string $query
         * @return  boolean true if the query returns a result, false either
         * @throws  DatabaseException
         */
        abstract public function queryReturnsResult( $query );

        /**
         * get the ID of the last inserted row in the database
         * @return  int id of the last inserted row
         * @throws  DatabaseException
         */
        abstract public function getLastInsertId();
        
        /**
         * get the number of rows affected by the query
         * @return  int number of rows affected by the query
         * @throws  DatabaseException
         */
        abstract public function getAffectedRows();
        
        /**
         * Escapes the given string to avoid SQL injections
         * @param   string $str
         * @return  escaped string
         * @throws  DatabaseException
         */
        public function escapeString( $str )
        {
            return addslashes( $str );
        }

        // ----- extended connection -----
        
        /**
         * Get a single column from the query
         * @param   string $sql
         * @return  array
         * @throws  DatabaseException
         */
        public function getColumnFromQuery( $sql )
        {
        	$res = $this->getAllRowsFromQuery( $sql );
        	
            $tmp = array();
            foreach ( $res as $row )
            {
                $tmp[] = $row[0];
            }
            return $tmp;
        }
        
        /**
         * Get a single value from the query
         * @param   string $sql
         * @return  mixed
         * @throws  DatabaseException
         */
        public function getSingleValueFromQuery( $sql )
        {
            $row = $this->getRowFromQuery( $sql );

            if ( is_array( $row ) && !empty( $row ) )
            {
                return array_shift($row);
            }
            else
            {
                return false;
            }
        }
        
        // Multiple queries
        
        /**
         * Execute an array of queries
         * @param   array $arr
         * @param   boolean $strictMode set to true to stop the execution of
         *  all queries on error
         * @return  boolean true on success
         * @throws  DatabaseException
         */
        protected function executeQueryArray( $arr, $strictMode = false )
        {
            $exceptionOccurs = false;
            $exceptions = '';
            
            foreach ( $arr as $query )
            {
                try
                {
                    $this->db->executeQuery( $query );
                }
                catch( DatabaseException $e )
                {
                    if ( $strictMode )
                    {
                        throw $e;
                    }
                    else
                    {
                        $exceptionOccurs = true;
                        $exceptions .= $e . "\n";
                    }
                }
                
                if ( $exceptionOccurs )
                {
                    throw new DatabaseException( $exceptions );
                }
                else
                {
                    return true;
                }
            }
        }
        
        /**
         * Execute list of queries given in a single string
         * @param   string $str
         * @param   boolean $strictMode set to true to stop the execution of
         *  all queries on error
         * @return  boolean true on success
         * @throws  DatabaseException
         */
        public function executeMultipleQuery( $sql, $strictMode = false )
        {
            $queries = $this->parseMultipleQuery( $sql );
            
            $this->executeQueryArray( $queries, $strictMode );
        }
        
        /**
         * Parse a list of queries given in a single string
         * @param   string $str
         * @return  array of queries
         * @author  PhpMyAdmin team
         */
        protected function parseMultipleQuery( $sql )
        {
            // make something usable...
            
            $ret = $this->pmaParse( $sql );
            
            $queries = array();
        
            foreach ( $ret as $item )
            {
                if ( ! $item['empty'] )
                {
                    $queries[] = $item['query'];
                }
            }
            
            return $queries;
        }
        
        protected function pmaParse( $sql )
        {
            $ret = array();
            
            $sql          = rtrim($sql, "\n\r");
            $sql_len      = strlen($sql);
            $char         = '';
            $string_start = '';
            $in_string    = false;
            $nothing      = true;
            
            for ($i = 0; $i < $sql_len; ++$i)
            {
                $char = $sql[$i];
                // We are in a string, check for not escaped end of strings except for
                // backquotes that can't be escaped
                if ($in_string)
                {
                    for (;;)
                    {
                        $i         = strpos($sql, $string_start, $i);
                        // No end of string found -> add the current substring to the
                        // returned array
                        if (!$i)
                        {
                            $ret[] = array('query' => $sql, 'empty' => $nothing);
                            return $ret;
                        }
                        // Backquotes or no backslashes before quotes: it's indeed the
                        // end of the string -> exit the loop
                        else if ($string_start == '`' || $sql[$i-1] != '\\')
                        {
                            $string_start      = '';
                            $in_string         = false;
                            break;
                        }
                        // one or more Backslashes before the presumed end of string...
                        else
                        {
                            // ... first checks for escaped backslashes
                            $j                     = 2;
                            $escaped_backslash     = false;
                            while ($i-$j > 0 && $sql[$i-$j] == '\\') {
                                $escaped_backslash = !$escaped_backslash;
                                $j++;
                            }
                            // ... if escaped backslashes: it's really the end of the
                            // string -> exit the loop
                            if ($escaped_backslash)
                            {
                                $string_start  = '';
                                $in_string     = false;
                                break;
                            }
                            // ... else loop
                            else
                            {
                                $i++;
                            }
                        } // end if...elseif...else
                    } // end for
                } // end if (in string)
                
                // lets skip comments (/*, -- and #)
                else if (($char == '-' && $sql_len > $i + 2 && $sql[$i + 1] == '-' && $sql[$i + 2] <= ' ') 
                    || $char == '#' || ($char == '/' && $sql_len > $i + 1 && $sql[$i + 1] == '*'))
                {
                    $i = strpos($sql, $char == '/' ? '*/' : "\n", $i);
                    // didn't we hit end of string?
                    if ($i === false)
                    {
                        break;
                    }
                    if ($char == '/') $i++;
                }
                
                // We are not in a string, first check for delimiter...
                else if ($char == ';')
                {
                    // if delimiter found, add the parsed part to the returned array
                    $ret[]      = array('query' => substr($sql, 0, $i), 'empty' => $nothing);
                    $nothing    = true;
                    $sql        = ltrim(substr($sql, min($i + 1, $sql_len)));
                    $sql_len    = strlen($sql);
                    if ($sql_len)
                    {
                        $i      = -1;
                    }
                    else
                    {
                        // The submited statement(s) end(s) here
                        return $ret;
                    }
                } // end else if (is delimiter)
        
                // ... then check for start of a string,...
                else if (($char == '"') || ($char == '\'') || ($char == '`'))
                {
                    $in_string    = true;
                    $nothing      = false;
                    $string_start = $char;
                } // end else if (is start of string)
                elseif ($nothing)
                {
                    $nothing = false;
                }
            } // end for
        
            // add any rest to the returned array
            if (!empty($sql) && preg_match('@[^[:space:]]+@', $sql))
            {
                $ret[] = array('query' => $sql, 'empty' => $nothing);
            }
            
            return $ret;
        }
        
//        // Prepared queries
//        
//        /**
//         * Prepare a query by replacing the ? by the value 
//         * in the given parameters array in their order of appearance
//         * @param   string $query, the query to prepare
//         * @param   array $params, the parameters to insert in the query
//         * @param   string $formatString, i for int d for float s for string
//         * @return  string the prepared query
//         * @throws  DatabaseException
//         */
//        public function prepareQuery( $query, $params, $formatString )
//        {
//            if ( ! ( is_array( $params ) && count( $params ) > 0 ) )
//            {
//                throw new DatabaseException('Invalid parameters array');
//            }
//            elseif ( count( $params) != strlen( $formatString ) )
//            {
//                throw new DatabaseException('Wrong format string for given parameters');
//            }
//            else
//            {
//                if ( ! $this->isConnected() )
//                {
//                    $this->connect();
//                }
//                
//                for ( $i = 0; $i < count ( $params ); $i++ )
//                {
//                    $value = $params[$i];
//                    $format = $formatString{$i};
//                    
//                    if ( false !== ( $pos = strpos( $query, '?' ) ) )
//                    {
//                        $query = substr( $query, 0, $pos )
//                            . $this->prepareValue($value, $format)
//                            . substr( $query, $pos + 1 )
//                            ;
//                    }
//                    else
//                    {
//                        throw new DatabaseException( "Number of arguments mismatch" );
//                    }
//                }
//                
//                return $query;
//            }
//        }
//        
//        /**
//         * Prepare the values to be used in prepared queries
//         * @param   mixed $value
//         * @param   string $format 'i', 'd' or 's'
//         * @return  mixed prepared value
//         */
//        protected function prepareValue( $value, $format )
//        {
//            if ( false === strpos( 'ids', $format ) )
//            {
//                throw new Exception('Invalid data format ' . $format);
//            }
//            else
//            {
//                if ( 'i' == $format )
//                {
//                    return (int) $value;
//                }
//                elseif ( 'd' == $format )
//                {
//                    return (float) $value;
//                }
//                else
//                {
//                    if ( is_null( $value ) )
//                    {
//                        return 'NULL';
//                    }
//                    elseif ( is_bool( $value ) )
//                    {
//                        return $value ? "'true'" : "'false'";
//                    }
//                    else
//                    {
//                        return "'".$this->escapeString( $value )."'";
//                    }
//                }
//            }
//        }
    }
    
    class ClarolineDatabase
    {
        public static function toClaroQuery( $sql, $courseId = null )
        {
            $courseId = is_null( $courseId ) 
                ? claro_get_current_course_id() 
                : $courseId
                ;
            
            // replace __CL_MAIN__ with main database prefix
            $sql = str_replace ('__CL_MAIN__',get_conf('mainTblPrefix'), $sql);
            
            // replace __CL_COURSE__ with course database prefix
            $sql = str_replace('__CL_COURSE__'
                , claro_get_course_db_name_glued( $courseId )
                , $sql );
                
            return $sql;
        }
    }
?>