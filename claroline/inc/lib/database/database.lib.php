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
     *              GNU GENERAL PUBLIC LICENSE version 2.0
     * @package     database
     */
     
    class DatabaseException extends Exception
    {
    }
    
    interface DatabaseIterator extends Iterator
    {
        public function size();
    }
    
    class Database
    {
        private static $_loadedDriver = false;
        
        public static function loadDriver( $name )
        {
            if ( ! self::$_loadedDriver )
            {
                try 
                {
                    if ( file_exists( dirname(__FILE__).'/'.$name.'.lib.php' ) )
                    {
                        $driverInfo = false;
                        require_once dirname(__FILE__).'/'.$name.'.lib.php';
                        
                        // var_dump( $driverInfo );
                        
                        self::$_loadedDriver = $driverInfo;
                        
                        Console::debug('Load database driver ' . $name);
                    }
                    else
                    {
                        throw new Exception("Database driver $name not found!");
                    }                    
                }
                catch ( Exception $e )
                {
                    throw new Exception("Database driver $name not found : " . $e->getMessage() );
                }
            }
            else
            {
                throw new Exception('Database driver allready loaded !');
            }
        }
        
        private static $_instance = false;
        
        public static function getClaroDatabaseConnection( $host, $user, $passwd, $dbname )
        {
            if ( ! self::$_loadedDriver )
            {
                throw new Exception('No database driver loaded !');
            }
            else
            {
                if ( ! self::$_instance )
                {
                    $className = self::$_loadedDriver['main'];
                    self::$_instance = new $className( $host, $user, $passwd, $dbname  );
                }
                
                return self::$_instance;
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
         * @param   boolean forceReconnect
         * @return  boolean
         */
        abstract public function connect( $forceReconnect = false );

        /**
         * reconnect a connection to the database
         * @return  boolean
         */
        public function reconnect()
        {
            return $this->connect(true);
        }

        /**
         * check if the connection is active
         * @return boolean true if the conection is active, false either
         */
        public function isConnected()
        {
            return $this->connected;
        }

        /**
         * close the connection
         */
        abstract public function close();

        /**
         * execute a query to the database and returns the number of
         * rows affected
         * @param string query database query
         * @return resource execution result
         */
        abstract public function executeQuery( $query );

        /**
         * execute the given query and returns all results as objects in an array
         * @param string query
         * @return array of objects
         */
        abstract public function getAllObjectsFromQuery( $query );

        /**
         * execute the given query and returns one result as an object
         * @param string query
         * @return object
         */
        abstract public function getObjectFromQuery( $query );

        /**
         * execute the given query and returns all returned rows in an array
         * @param string query
         * @return array of rows
         */
        abstract public function getAllRowsFromQuery( $query );

        /**
         * execute the given query and returns one row in an array
         * @param string query
         * @return array row
         */
        abstract public function getRowFromQuery( $query );

        /**
         * execute a database query and return true if the query returns a
         * result
         * @param string query
         * @return boolean true if the query returns a result, false either
         */
        abstract public function queryReturnsResult( $query );

        /**
         * get the ID of the last inserted row in the database
         * return int id of the last inserted row
         */
        abstract public function getLastInsertId();
        
        /**
         * get the number of rows affected by the query
         * return int number of rows affected by the query
         */
        abstract public function getAffectedRows();
        
        public function escapeString( $str )
        {
            return addslashes( $str );
        }

        // ----- extended connection -----

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
        
        public function executeMultipleQuery( $sql, $strictMode = false )
        {
            $queries = $this->parseMultipleQuery( $sql );
            
            $this->executeQueryArray( $queries, $strictMode );
        }
        
        protected function parseMultipleQuery( $sql )
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
            
            // make something usable...
            
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
        
        // Prepared queries
        
        /**
         * Prepare a query by replacing the ? by the value 
         * in the given parameters array in their order of appearance
         *
         * @access  public
         * @since   1.9
         * @param   string query, the query to prepare
         * @param   array params, the parameters to insert in the query
         * @param   string formatString, i for int d for float s for string
         * @return  string the prepared query
         */
        public function prepareQuery( $query, $params, $formatString )
        {
            if ( ! ( is_array( $params ) && count( $params ) > 0 ) )
            {
                throw new DatabaseException('Invalid parameters array');
            }
            elseif ( count( $params) != strlen( $formatString ) )
            {
                throw new DatabaseException('Wrong format string for given parameters');
            }
            else
            {
                if ( ! $this->isConnected() )
                {
                    $this->connect();
                }
                
                for ( $i = 0; $i < count ( $params ); $i++ )
                {
                    $value = $params[$i];
                    $format = $formatString{$i};
                    
                    if ( false !== ( $pos = strpos( $query, '?' ) ) )
                    {
                        $query = substr( $query, 0, $pos )
                            . $this->prepareValue($value, $format)
                            . substr( $query, $pos + 1 )
                            ;
                    }
                    else
                    {
                        throw new DatabaseException( "Number of arguments mismatch" );
                    }
                }
                
                return $query;
            }
        }
        
        protected function prepareValue( $value, $format )
        {
            if ( false === strpos( 'ids', $format ) )
            {
                throw new Exception('Invalid data format ' . $format);
            }
            else
            {
                if ( 'i' == $format )
                {
                    return (int) $value;
                }
                elseif ( 'd' == $format )
                {
                    return (float) $value;
                }
                else
                {
                    return "'".$this->escapeString( $value )."'";
                }
            }
        }
    }
?>