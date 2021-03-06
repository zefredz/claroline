<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * Authentication Manager
 *
 * @version     Claroline 1.11 $Revision$
 * @copyright   (c) 2001-2014, Universite catholique de Louvain (UCL)
 * @author      Claroline Team <info@claroline.net>
 * @author      Frederic Minne <zefredz@claroline.net>
 * @license     http://www.gnu.org/copyleft/gpl.html
 *              GNU GENERAL PUBLIC LICENSE version 2 or later
 * @package     kernel.auth
 */

// Get required libraries
require_once __DIR__ . '/../core/claroline.lib.php';
require_once __DIR__ . '/../database/database.lib.php';
require_once __DIR__ . '/../kernel/user.lib.php';

require_once __DIR__ . '/authdrivers.lib.php';
require_once __DIR__ . '/ldapauthdriver.lib.php';

require_once __DIR__ . '/authprofile.lib.php';

/**
 * Authentication manager. This is the class that executes the authentication 
 * process in Claroline
 */
class AuthManager
{
    protected static $extraMessage = null;
    
    /**
     * @return string failure message
     */
    public static function getFailureMessage()
    {
        return self::$extraMessage;
    }
    
    protected static function setFailureMessage( $message )
    {
        self::$extraMessage = $message;
    }
    
    /**
     * Authenticate the user
     * @param string $username
     * @param string $password
     * @return false or Claro_CurrentUser instance
     */
    public static function authenticate( $username, $password )
    {
        if ( !empty($username) && $authSource = AuthUserTable::getAuthSource( $username ) )
        {
            Console::debug("Found authentication source {$authSource} for {$username}");
            $driverList = array( AuthDriverManager::getDriver( $authSource ) );
        }
        else
        {
            // avoid issues with session collision when many users connect from
            // the same computer at the same time with the same browser session !
            if ( AuthUserTable::userExists( $username ) )
            {
                self::setFailureMessage( get_lang( "There is already an account with this username." ) );
                return false;
            }
            
            $authSource = null;
            $driverList = AuthDriverManager::getRegisteredDrivers();
        }
        
        foreach ( $driverList as $driver )
        {
            $driver->setAuthenticationParams( $username, $password );
            
            if ( $driver->authenticate() )
            {

                $uid = AuthUserTable::registered( $username, $driver->getAuthSource() );
                
                if ( $uid )
                {
                    if ( $driver->userUpdateAllowed() )
                    {
                        $userAttrList =  $driver->getFilteredUserData();
                        
                        if ( isset( $userAttrList['loginName'] ) )
                        {
                            $newUserName = $userAttrList['loginName'];
                            
                            if ( ! get_conf('claro_authUsernameCaseSensitive', true) )
                            {
                                $newUsername = strtolower($newUserName);
                                $username = strtolower($username);
                            }
                            
                            // avoid session collisions !
                            if ( $username != $newUserName )
                            {
                                Console::error( "EXTAUTH ERROR : try to overwrite an existing user {$username} with another one" . var_export($userAttrList, true) );
                            }
                            else
                            {
                                AuthUserTable::updateUser( $uid, $userAttrList );
                                Console::info( "EXTAUTH INFO : update user {$uid} {$username} with " . var_export($userAttrList, true) );
                            }
                        }
                        else
                        {
                            Console::error( "EXTAUTH ERROR : no loginName given for user {$username} by authSource " . $driver->getAuthSource() );
                        }
                    }
                    
                    return Claro_CurrentUser::getInstance( $uid, true );
                }
                elseif ( $driver->userRegistrationAllowed() )
                {
                    // duplicate code here to avoid issue with multiple requests on a busy server !
                    if ( AuthUserTable::userExists( $username ) )
                    {
                        self::setFailureMessage( get_lang( "There is already an account with this username." ) );
                        return false;
                    }
                    
                    $uid = AuthUserTable::createUser( $driver->getUserData() );
                    
                    return Claro_CurrentUser::getInstance( $uid, true );
                }
            }
            elseif ( $authSource )
            {
                self::setFailureMessage( $driver->getFailureMessage() );
            }
        }
        
        // authentication failed
        return false;
    }
}

/**
 * Access the user authentication table
 */
class AuthUserTable
{
    /**
     * Check if a user exists given its username
     * @param string $username
     * @return bool
     */
    public static function userExists( $username )
    {
        $tbl = claro_sql_get_main_tbl();

        $sql = "SELECT user_id, authSource\n"
            . "FROM `{$tbl['user']}`\n"
            . "WHERE "
            . ( get_conf('claro_authUsernameCaseSensitive',true) ? 'BINARY ' : '')
            . "username = ". Claroline::getDatabase()->quote($username) . "\n"
            ;

        $res = Claroline::getDatabase()->query( $sql );

        if ( $res->numRows() )
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    
    /**
     * Check if a user identified by its username is registrered with the given authentication source
     * @param string $username
     * @param string $authSourceName name of the authentication source
     * @return bool
     */
    public static function registered( $username, $authSourceName )
    {
        $tbl = claro_sql_get_main_tbl();
        
        $sql = "SELECT user_id\n"
            . "FROM `{$tbl['user']}`\n"
            . "WHERE "
            . ( get_conf('claro_authUsernameCaseSensitive',true) ? 'BINARY ' : '')
            . "username = ". Claroline::getDatabase()->quote($username) . "\n"
            . "AND\n"
            . "authSource = " . Claroline::getDatabase()->quote($authSourceName) . "\n"
            . "ORDER BY user_id DESC LIMIT 1"
            ;
            
        $res = Claroline::getDatabase()->query( $sql );
        
        if ( $res->numRows() )
        {
            $uidArr = $res->fetch();
            
            return (int) $uidArr['user_id'];
        }
        else
        {
            return false;
        }
    }
    
    /**
     * Retrieve the authentication source for a given user identified by its username
     * @param string $username
     * @return string authentication source name
     */
    public static function getAuthSource( $username )
    {
        $tbl = claro_sql_get_main_tbl();
        
        $sql = "SELECT authSource\n"
            . "FROM `{$tbl['user']}`\n"
            . "WHERE "
            . ( get_conf('claro_authUsernameCaseSensitive',true) ? "BINARY " : "" )
            . "username = ". Claroline::getDatabase()->quote($username) . "\n"
            . "ORDER BY user_id DESC LIMIT 1"
            ;
            
        return  Claroline::getDatabase()->query( $sql )->fetch(Database_ResultSet::FETCH_VALUE);
    }
    
    /**
     * Create a user given its attributes
     * @param array $userAttrList array of user attributes
     * @return int $uid or false
     */
    public static function createUser( $userAttrList )
    {
        return self::registerUser( $userAttrList, null );
    }
    
    /**
     * Update a user given its attributes and user id
     * @param int $uid user id
     * @param array $userAttrList array of user attributes
     * @return int $uid or false
     */
    public static function updateUser( $uid, $userAttrList )
    {
        return self::registerUser( $userAttrList, $uid );
    }
    
    /**
     * Create or update a user
     * @param array $userAttrList
     * @param int $uid
     * @return boolean false on error or int $uid
     */
    protected static function registerUser( $userAttrList, $uid = null )
    {
        $preparedList = array();
        
        // Map database fields
        $dbFieldToClaroMap = array(
            'nom' => 'lastname',
            'prenom' => 'firstname',
            'username' => 'loginName',
            'email' => 'email',
            'officialCode' => 'officialCode',
            'phoneNumber' => 'phoneNumber',
            'isCourseCreator' => 'isCourseCreator',
            'authSource' => 'authSource');
        
        // Do not overwrite username and authsource for an existing user !!!
        if ( ! is_null( $uid ) )
        {
            unset( $dbFieldToClaroMap['username'] );
            unset( $dbFieldToClaroMap['authSource'] );
        }

        
        foreach ( $dbFieldToClaroMap as $dbFieldName => $claroAttribName )
        {
            if ( isset($userAttrList[$claroAttribName])
                && ! is_null($userAttrList[$claroAttribName]) )
            {
                $preparedList[] = $dbFieldName
                    . ' = '
                    . Claroline::getDatabase()->quote($userAttrList[$claroAttribName])
                    ;
            }
        }
        
        if ( empty( $preparedList ) )
        {
            return false;
        }
        
        $tbl = claro_sql_get_main_tbl();
        
        $sql = ( $uid ? 'UPDATE' : 'INSERT INTO' )
            . " `{$tbl['user']}`\n"
            . "SET " . implode(",\n", $preparedList ) . "\n"
            . ( $uid ? "WHERE  user_id = " . (int) $uid : '' )
            ;
        
        Claroline::getDatabase()->exec($sql);
        
        if ( ! $uid )
        {
            $uid = Claroline::getDatabase()->insertId();
        }
        
        return $uid;
    }
}

/**
 * Authentication drivers manager. One driver is associated with one 
 * authentication source name
 */
class AuthDriverManager
{
    protected static $drivers = false;
    protected static $driversAllowingLostPassword = false;
    
    /**
     * Register the available drivers
     * @return array of drivers
     */
    public static function getRegisteredDrivers()
    {
        if ( ! self::$drivers )
        {
            self::initDriverList();
        }
        
        return  self::$drivers;
    }
    
    /**
     * Get the driver corresponding to an authentication source
     * @param type $authSource
     * @return type
     * @throws Exception
     */
    public static function getDriver( $authSource )
    {
        if ( ! self::$drivers )
        {
            self::initDriverList();
        }
        
        if ( array_key_exists( $authSource, self::$drivers ) )
        {
            return self::$drivers[$authSource];
        }
        else
        {
            throw new Exception("No auth driver found for {$authSource} !");
        }
    }
    
    /**
     * Load a driver given its driver configuration file path
     * @param string $driverConfigPath path to the configuration file of the driver
     * @return void
     * @throws Exception in case of error (in debug mode only) in normal mode the error 
     * is logged in the log table in the database
     */
    protected static function loadDriver ( $driverConfigPath )
    {
        if ( !file_exists( $driverConfigPath ) )
        {
            if ( claro_debug_mode() )
            {
                throw new Exception("Driver configuration {$driverConfigPath} not found");
            }

            Console::error( "Driver configuration {$driverConfigPath} not found" );
            
            return;
        }
        
        $driverConfig  = array();
        
        include $driverConfigPath;
        
        if ( $driverConfig['driver']['enabled'] == true )
        {
            $driverClass = $driverConfig['driver']['class'];
            
            // search for kernel drivers
            if ( class_exists( $driverClass ) )
            {
                $driver = new $driverClass;
                $driver->setDriverOptions( $driverConfig );
                self::$drivers[$driverConfig['driver']['authSourceName']] = $driver;
            }
            // search for user defined drivers
            else
            {
                // load dynamic drivers
                if ( ! file_exists ( get_path('rootSys') . 'platform/conf/extauth/drivers' ) )
                {
                    require_once __DIR__ . '/../fileManage.lib.php';
                    claro_mkdir(get_path('rootSys') . 'platform/conf/extauth/drivers', CLARO_FILE_PERMISSIONS, true );
                }
                
                $driverPath = get_path('rootSys') 
                    . 'platform/conf/extauth/drivers/' 
                    . strtolower($driverClass).'.drv.php';

                if ( file_exists($driverPath) )
                {
                    require_once $driverPath;

                    if ( class_exists( $driverClass ) )
                    {
                        $driver = new $driverClass;
                        $driver->setDriverOptions( $driverConfig );
                        self::$drivers[$driverConfig['driver']['authSourceName']] = $driver;

                    }
                    else
                    {
                        if ( claro_debug_mode() )
                        {
                            throw new Exception("Driver class {$driverClass} not found");
                        }

                        Console::error( "Driver class {$driverClass} not found" );
                    }
                }
                else
                {
                    if ( claro_debug_mode() )
                    {
                        throw new Exception("Driver class {$driverClass} not found");
                    }

                    Console::error( "Driver class {$driverClass} not found" );
                }
            }
        }
        
        if ( isset($driverConfig['driver']['lostPasswordAllowed']) && $driverConfig['driver']['lostPasswordAllowed'] == true )
        {
            self::$driversAllowingLostPassword[$driverConfig['driver']['authSourceName']] = $driverConfig['driver']['authSourceName'];
        }
    }
    
    /**
     * Get the list of authentication source names for which the driver supports 
     * the lost password script
     * @return array of authentication source names
     */
    public static function getDriversAllowingLostPassword()
    {
        if ( ! self::$drivers )
        {
            self::initDriverList();
        }
        
        return self::$driversAllowingLostPassword;
    }
    
    /**
     * Check if the driver of a given authentication source supports the lost password script
     * @param string $authSourceName authentication source name
     * @return boolean
     */
    public static function checkIfDriverSupportsLostPassword( $authSourceName )
    {
        if ( ! self::$drivers )
        {
            self::initDriverList();
        }
        
        if ( isset( self::$driversAllowingLostPassword[$authSourceName] ) )
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    
    /**
     * Initialize the authentication driver list
     */
    protected static function initDriverList()
    {
        // load static drivers
        self::$drivers = array(
            'claroline' => new ClarolineLocalAuthDriver(),
            'disabled' => new UserDisabledAuthDriver(),
            'temp' => new TemporaryAccountAuthDriver()
        );
        
        self::$driversAllowingLostPassword = array(
            'claroline' => 'claroline',
            'clarocrypt' => 'clarocrypt'
        );
        
        // load dynamic drivers
        if ( ! file_exists ( get_path('rootSys') . 'platform/conf/extauth' ) )
        {
            require_once __DIR__ . '/../fileManage.lib.php';
            claro_mkdir(get_path('rootSys') . 'platform/conf/extauth', CLARO_FILE_PERMISSIONS, true );
        }
        
        if ( get_conf( 'claro_authDriversAutoDiscovery', true ) )
        {
            $driversToLoad = array();
            
            $it = new DirectoryIterator( get_path('rootSys') . 'platform/conf/extauth' );
            
            foreach ( $it as $file )
            {
                if ( $file->isFile() && substr( $file->getFilename(), -9 ) == '.conf.php' )
                {
                    $driversToLoad[] = $file->getPathname();
                }          
            }

            sort( $driversToLoad );

            foreach ( $driversToLoad as $driverFile )
            {
                self::loadDriver($driverFile);
            }        
        }
        else
        {
            if ( file_exists( get_path('rootSys') . 'platform/conf/extauth/drivers.list' ) )
            {
                $authDriverList = file( get_path('rootSys') . 'platform/conf/extauth/drivers.list' );
                
                foreach ( $authDriverList as $authDriver )
                {
                    $authDriver = trim($authDriver);
                    
                    if ( ! empty( $authDriver ) )
                    {
                        self::loadDriver(ltrim(rtrim(get_path('rootSys') . 'platform/conf/extauth/'.$authDriver)));
                    }
                }
            }
        }
    }
}
