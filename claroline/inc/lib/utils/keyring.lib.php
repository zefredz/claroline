<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * Keyring lib
 *
 * @version     1.12 $Revision$
 * @copyright   2001-2014 Universite catholique de Louvain (UCL)
 * @author      Claroline Team <info@claroline.net>
 * @author      Frederic Minne <zefredz@claroline.net>
 * @license     http://www.gnu.org/copyleft/gpl.html
 *              GNU GENERAL PUBLIC LICENSE version 2 or later
 * @package     CLKRNG
 */

/**
 * Keyring helper
 */
class Claro_Keyring_Helper
{
    protected static $instance = false;
    protected static $options = array();
    
    /**
     * Get the keyring
     * @return Keyring
     */
    public static function getInstance()
    {
        if ( ! self::$instance )
        {
            self::$instance = new Claro_Keyring( Claroline::getDatabase ());
        }
        
        return self::$instance;
    }
    
    /**
     * Set an option for the keyring helper
     * @param string $option
     * @param mixed $value
     */
    public static function setOption($option, $value)
    {
        self::$options[$option] = $value;
    }
    
    /**
     * Get the value of an option (null if not set)
     * @param string $option
     * @return mixed|null
     */
    public static function getOption($option)
    {
        return isset( self::$options[$option]) ? self::$options[$option] : null;
    }
    
    /**
     * Check the service key for the given service and host names
     * @param string $serviceName
     * @param string $serviceHost
     * @param string $serviceKey
     * @return boolean
     */
    public static function checkKey ( $serviceName, $serviceHost, $serviceKey )
    {
        $mngr = self::getInstance();
        
        return $mngr->check ( $serviceName, $serviceHost, $serviceKey );
    }
    
    /**
     * Check the service key for the given service and host names using gethostbyaddr and gethostbyname
     * @param string $serviceName
     * @param string $serviceHost
     * @param string $serviceKey
     * @return boolean
     */
    public static function checkKeyForHost ( $serviceName, $serviceHost, $serviceKey )
    {
        $mngr = self::getInstance();
        
        $serviceHostList = array( $serviceHost );
    
        $serviceHostList[] = @gethostbyaddr( $serviceHost );
        $serviceHostList[] = @gethostbyname( $serviceHost );

        if ( false !== ( $hostNameList = @gethostbynamel( $serviceHost ) ) ) 
        {   
            foreach ( $hostNameList as $hostName )
            {   
                $serviceHostList[] = $hostName;
            }   
        }   

        foreach ( $serviceHostList as $serviceHost )
        {   
            if ( $mngr->check ( $serviceName, $serviceHost, $serviceKey ) )
            {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * This is a helper funcion to allow easy integration of CLKRNG
     * This is a bit ugly since this function echoes some data and stops the 
     * execution of the script
     * @param string $serviceName
     */
    public static function checkForService( $serviceName )
    {
        $userInput = Claro_userInput::getInstance();
        
        try
        {
            $serviceUser = isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : null;
            
            $serviceKey = $userInput->get('serviceKey', null);
            
            if ( ! $serviceKey )
            {
                throw new Exception(get_lang("Missing service key"));
            }
            else
            {
                if ( !empty( $serviceUser ) )
                {
                    if ( ! self::checkKeyForHost( $serviceName, $serviceUser, $serviceKey ) )
                    {
                        throw new Exception(get_lang('Wrong service key or host'));
                    }
                }
                else
                {
                    throw new Exception(get_lang("Missing service host"));
                }   
            }
        }
        catch ( Exception $e )
        {
            if ( self::getOption ( 'errorMode' ) == 'exception' )
            {
                throw $e;
            }
            elseif ( self::getOption ( 'errorMode' ) == 'returnfalse' )
            {
                Console::debug($e->__toString());
                return false;
            }
            else
            {
                header( 'Forbidden', true, 403 );

                echo '<h1>Forbidden !</h1>';

                if ( claro_debug_mode() )
                {
                    echo '<pre>'.$e->__toString().'</pre>';
                }
                else
                {
                    echo '<p>An exception occurs : '.$e->getMessage().'</p>';
                }        

                exit();
            }
        }
    }
}

/**
 * Keyring class
 */
class Claro_Keyring
{
    /**
     * Constructor
     * @param Database_Connection $database optional database connection
     */
    public function __construct ( $database = null )
    {
        $this->database = $database ? $database : Claroline::getDatabase();
        $this->table = claro_sql_get_tbl('clkrng_keyring');
    }
    
    /**
     * Add a key for the given service and host
     * @param string $serviceName
     * @param string $serviceHost
     * @param string $serviceKey
     * @return \Keyring
     * @throws Exception
     */
    public function add ( $serviceName, $serviceHost, $serviceKey )
    {
        if ( ! $this->database->exec("
            INSERT 
            INTO
                `{$this->table['clkrng_keyring']}`
            SET
                `service` = ".$this->database->quote($serviceName).",
                `host` = ".$this->database->quote($serviceHost).",
                `key` = ".$this->database->quote($serviceKey) ."
        ") )
        {
             throw new Exception(get_lang("Cannot insert key {$serviceKey} for service {$serviceName} and host {$serviceHost}"));
        }
                
        return $this;
    }
    
    /**
     * Update a key for the given service and host
     * @param string $oldServiceName
     * @param string $oldServiceHost
     * @param string $serviceName
     * @param string $serviceHost
     * @param string $serviceKey
     * @return \Keyring
     * @throws Exception
     */
    public function update ( $oldServiceName, $oldServiceHost, $serviceName, $serviceHost, $serviceKey )
    {
        if ( ! $this->database->exec("
            UPDATE 
                `{$this->table['clkrng_keyring']}`
            SET
                `service` = ".$this->database->quote($serviceName).",
                `host` = ".$this->database->quote($serviceHost).",
                `key` = ".$this->database->quote($serviceKey)."
            WHERE
                `service` = ".$this->database->quote($oldServiceName)."
            AND
                `host` = ".$this->database->quote($oldServiceHost)."
        ") )
        {
             throw new Exception(get_lang("Cannot update key for service {$oldServiceName} and host {$oldServiceHost}"));
        }
                
        return $this;
    }
    
    /**
     * Delete the key for the given host and service
     * @param string $serviceName
     * @param string $serviceHost
     * @return \Keyring
     * @throws Exception
     */
    public function delete ( $serviceName, $serviceHost )
    {
        if ( ! $this->database->exec("
            DELETE 
            FROM
                `{$this->table['clkrng_keyring']}`
            WHERE
                `service` = ".$this->database->quote($serviceName)."
            AND
                `host` = ".$this->database->quote($serviceHost)."
        ") )
        {
            throw new Exception ("no key for {$serviceName}:{$serviceHost}");
        }
        
        return $this;
    }
    
    /**
     * Get the service (serviceKey, serviceNAme, serviceHost) triplet for the given service name and host
     * @param string $serviceName
     * @param string $serviceHost
     * @return array ['serviceName' => ..., 'serviceHost' => ..., 'serviceKey' => ...]
     * @throws Exception
     */
    public function get ( $serviceName, $serviceHost )
    {
        $result = $this->database->query("
            SELECT 
                `service` AS serviceName,
                `host` AS serviceHost,
                `key` AS serviceKey
            FROM
                `{$this->table['clkrng_keyring']}`
            WHERE
                `service` = ".$this->database->quote($serviceName)."
            AND
                `host` = ".$this->database->quote($serviceHost)."
        ")->fetch();
                
        if ( $result )
        {
            return $result;
        }
        else
        {
            throw new Exception ("no key for {$serviceName}:{$serviceHost}");
        }
    }
    
    /**
     * Check if the key is associated to the given service and host
     * @param string $serviceName
     * @param string $serviceHost
     * @param string $serviceKeyToCheck
     * @return boolean
     */
    public function check ( $serviceName, $serviceHost, $serviceKeyToCheck )
    {
        return $this->database->query("
            SELECT 
                `service`,
                `host`,
                `key`
            FROM
                `{$this->table['clkrng_keyring']}`
            WHERE
                `service` = ".$this->database->quote($serviceName)."
            AND
                `host` = ".$this->database->quote($serviceHost)."
            AND
                `key` = ".$this->database->quote($serviceKeyToCheck)."
        ")->numRows() > 0;
    }
    
    /**
     * Get the list of all registered (serviceKey, serviceNAme, serviceHost) triplets
     * @return array [['serviceName' => ..., 'serviceHost' => ..., 'serviceKey' => ...], ...]
     */
    public function getServiceList()
    {
        $toRet = array();
        
        $resultSet = $this->database->query("
            SELECT 
                `service` AS `serviceName`,
                `host` AS `serviceHost`,
                `key` AS `serviceKey`
            FROM
                `{$this->table['clkrng_keyring']}`
            WHERE
                1 = 1
        ");
                
        foreach ( $resultSet as $row )
        {
            $toRet[] = $row;
        }
        
        return $toRet;
    }
}
