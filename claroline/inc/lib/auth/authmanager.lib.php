<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * Authentication Manager
 *
 * @version     1.9 $Revision$
 * @copyright   2001-2008 Universite catholique de Louvain (UCL)
 * @author      Claroline Team <info@claroline.net>
 * @author      Frederic Minne <zefredz@claroline.net>
 * @license     http://www.gnu.org/copyleft/gpl.html
 *              GNU GENERAL PUBLIC LICENSE version 2 or later
 * @package     kernel.auth
 */

// Get required libraries
FromKernel::uses('core/claroline.lib','database/database.lib','kernel/user.lib');

class AuthManager
{
    protected static $extraMessage = null;
    
    public static function getFailureMessage()
    {
        return self::$extraMessage;
    }
    
    protected static function setFailureMessage( $message )
    {
        self::$extraMessage = $message;
    }
    
    public function authenticate( $username, $password )
    {
        if ( !empty($username) && $authSource = self::getAuthSource( $username ) )
        {
            Console::debug("Found authentication source {$authSource}");
            $driverList = array( AuthDriverManager::getDriver( $authSource ) );
        }
        else
        {
            $authSource = null;
            $driverList = AuthDriverManager::getRegisteredDrivers();
        }
        
        foreach ( $driverList as $driver )
        {
            $driver->setAuthenticationParams( $username, $password );
            
            if ( $driver->authenticate() )
            {
                if ( $uid = self::registered( $username, $driver->getAuthSource() ) )
                {
                    $driver->update( $uid );
                    
                    return $driver->getUser();
                }
                elseif ( $driver->userRegistrationAllowed() )
                {
                    $driver->register();
                    
                    return $driver->getUser();
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
    
    public static function registered( $username, $authSourceName = null )
    {
        if ( empty( $authSourceName ) )
        {
            return false;
        }
        
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
            return $res->fetch(Database_ResultSet::FETCH_VALUE);
        }
        else
        {
            return false;
        }
    }
    
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
}

class AuthDriverManager
{
    protected static $drivers = false;
    
    public static function getRegisteredDrivers()
    {
        if ( ! self::$drivers )
        {
            self::initDriverList();
        }
        
        return  self::$drivers;
    }
    
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
    
    protected static function initDriverList()
    {
        // todo : get from config
        self::$drivers = array(
            'claroline' => new ClarolineLocalAuthDriver(),
            'disabled' => new UserDisabledAuthDriver()
        );
        
        if ( ! file_exists ( get_path('rootSys') . 'platform/conf/extauth' ) )
        {
            FromKernel::uses('fileManage.lib');
            claro_mkdir(get_path('rootSys') . 'platform/conf/extauth', CLARO_FILE_PERMISSIONS, true );
        }
        
        $it = new DirectoryIterator( get_path('rootSys') . 'platform/conf/extauth' );
        
        $driverConfig = array();
        
        foreach ( $it as $file )
        {
            if ( $file->isFile() )
            {
                include $file->getPathname();
                
                if ( $driverConfig['driver']['enabled'] == true )
                {
                    if ( $driverConfig['driver']['class'] == 'PearAuthDriver' )
                    {
                        self::$drivers[$driverConfig['driver']['authSourceName']] = PearAuthDriver::fromConfig( $driverConfig );
                    }
                    else
                    {
                        $driverClass = $driverConfig['driver']['class'];
                        
                        if ( class_exists( $driverClass ) )
                        {
                            self::$drivers[$driverConfig['driver']['authSourceName']] = new $driverClass( $driverConfig );
                        }
                        else
                        {
                            $driverPath = dirname(__FILE__). '/drivers/' . strtolower($driverClass).'.lib.php';
                            
                            if ( file_exists($driverPath) )
                            {
                                require_once $driverPath;
                                
                                if ( class_exists( $driverClass ) )
                                {
                                    self::$drivers[$driverConfig['driver']['authSourceName']] = new $driverClass( $driverConfig );
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
                }
            }
            
            $driverConfig = array();
        }
    }
}

interface AuthDriver
{
    public function setAuthenticationParams( $username, $password );
    public function authenticate();
    public function update( $uid );
    public function register();
    public function getUSerData();
    public function getUser();
    public function getUserId();
    public function getAuthSource();
    public function userRegistrationAllowed();
    public function getFailureMessage();
}

abstract class AbstractAuthDriver implements AuthDriver
{
    protected $userId = null;
    protected $extAuthIgnoreUpdateList = array();
    protected $username = null, $password = null;
    protected $extraMessage = null;
    
    // abstract public function getUserData();
    
    protected function setFailureMessage( $message )
    {
        $this->extraMessage = $message;
    }
    
    public function getFailureMessage()
    {
        return $this->extraMessage;
    }
    
    public function setAuthenticationParams( $username, $password )
    {
        $this->username = $username;
        $this->password = $password;
    }
    
    protected function registerUser( $userAttrList, $uid = null )
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
            
        foreach ( $dbFieldToClaroMap as $dbFieldName => $claroAttribName )
        {
            if ( ! is_null($userAttrList[$claroAttribName])
                && ( !$uid || !in_array($claroAttribName, $this->extAuthIgnoreUpdateList ) ) )
            {
                $preparedList[] = $dbFieldName
                    . ' = '
                    . Claroline::getDatabase()->quote($userAttrList[$claroAttribName])
                    ;
            }
        }
        
        $tbl = claro_sql_get_main_tbl();
        
        $sql = ( $uid ? 'UPDATE' : 'INSERT INTO' ) 
            . " `{$tbl['user']}`\n"
            . "SET " . implode(",\n", $preparedList ) . "\n"
            . ( $uid ? "WHERE  user_id = " . (int) $uid : '' )
            ;
        
        try
        {
            Claroline::getDatabase()->exec($sql);
            
            $this->userId = $uid ? $uid : Claroline::getDatabase()->insertId();
            
            return $this->userId;
        }
        catch( Exception $e )
        {
            throw new Exception("Fail to insert or update user in database !!!!");
        }
    }
    
    public function getUser()
    {
        if ( $this->getUSerId() )
        {
            return Claro_CurrentUser::getInstance($this->getUserId());
        }
        else
        {
            return null;
        }
    }
    
    public function update( $uid )
    {
        $this->userId = $this->registerUser( $this->getUserData(), $uid );
    }
    
    public function register()
    {
        $this->userId = $this->registerUser( $this->getUserData() );
    }
    
    public function getUserId()
    {
        return $this->userId;
    }
}

class UserDisabledAuthDriver extends AbstractAuthDriver
{
    public function getFailureMessage()
    {
        // we use get_lang here to force the language file builder to add this
        // variable, but since this code is executed before the language files are loaded
        // we have to call get_lang a second time when the message is displayed...
        return get_lang('This account has been disabled, please contact the platform administrator');
    }
    
    public function userRegistrationAllowed()
    {
        return false;
    }
    
    public function getAuthSource()
    {
        return 'disabled';
    }
    
    public function update( $uid )
    {
        return false;
    }
    
    public function register()
    {
        return false;
    }
    
    public function getUserId()
    {
        return null;
    }
    
    public function getUser()
    {
        return null;
    }
    
    public function authenticate()
    {
        return false;
    }
    
    public function getUserData()
    {
        return null;
    }
}

class ClarolineLocalAuthDriver extends AbstractAuthDriver
{
    protected $alwaysCrypted = false;
    
    public function userRegistrationAllowed()
    {
        return false;
    }
    
    public function getAuthSource()
    {
        return 'claroline';
    }
    
    public function setAuthenticationParams( $username, $password )
    {
        $this->username = $username;
        
        if ( get_conf('userPasswordCrypted',false) )
        {
            $this->password = md5($password);
        }
        else
        {
            $this->password = $password;
        }
    }
    
    public function authenticate()
    {
        if ( empty( $this->username ) || empty( $this->password ) )
        {
            return false;
        }
        
        $tbl = claro_sql_get_main_tbl();
        
        $sql = "SELECT user_id, username, password, authSource\n"
            . "FROM `{$tbl['user']}`\n"
            . "WHERE "
            . ( get_conf('claro_authUsernameCaseSensitive',true) ? 'BINARY ' : '')
            . "username = ". Claroline::getDatabase()->quote($this->username) . "\n"
            . "AND authSource = 'claroline'" . "\n"
            . "ORDER BY user_id DESC LIMIT 1"
            ;
            
        $userDataList = Claroline::getDatabase()->query( $sql );
        
        if ( $userDataList->numRows() > 0 )
        {
            foreach ( $userDataList as $userData )
            {
                if ( $this->password === $userData['password'] )
                {
                    $this->userId = $userData['user_id'];
                    return true;
                }
                else
                {
                    return false;
                }
            }
        }
        else
        {
            return false;
        }
    }
    
    public function getUserData()
    {
        return $this->getUser()->getRawData();
    }
    
    public function registered()
    {
        $userId = $this->getUserId();
        return !empty($userId);
    }
    
    public function update( $uid )
    {
        return $this->getUserId();
    }
    
    public function register()
    {
        return $this->getUserId();
    }
}

class PearAuthDriver extends AbstractAuthDriver
{
    protected $driverConfig;
    protected $authType;
    protected $authSourceName;
    protected $userRegistrationAllowed;
    protected $extAuthOptionList;
    protected $extAuthAttribNameList;
    protected $extAuthAttribTreatmentList;
    
    protected $auth;
    
    public function __construct( $driverConfig )
    {
        $this->driverConfig = $driverConfig;
        $this->authType = $driverConfig['driver']['authSourceType'];
        $this->authSourceName = $driverConfig['driver']['authSourceName'];
        $this->userRegistrationAllowed = $driverConfig['driver']['userRegistrationAllowed'];
        $this->extAuthOptionList = $driverConfig['extAuthOptionList'];
        $this->extAuthAttribNameList = $driverConfig['extAuthAttribNameList'];
        $this->extAuthAttribTreatmentList = $driverConfig['extAuthAttribTreatmentList'];
        $this->extAuthIgnoreUpdateList = $driverConfig['extAuthAttribToIgnore'];
    }
    
    /* public function __construct( 
        $authType,
        $authSourceName,
        $extAuthOptionList,
        $extAuthAttribNameList,
        $extAuthAttribTreatmentList,
        $extAuthIgnoreUpdateList = array() )
    {
        $this->authType = $authType;
        $this->authSourceName = $authSourceName;
        $this->extAuthOptionList = $extAuthOptionList;
        $this->extAuthAttribNameList = $extAuthAttribNameList;
        $this->extAuthAttribTreatmentList = $extAuthAttribTreatmentList;
        $this->extAuthIgnoreUpdateList = $extAuthIgnoreUpdateList;
    }*/
    
    public function userRegistrationAllowed()
    {
        return $this->userRegistrationAllowed;
    }
    
    public function getAuthSource()
    {
        return $this->authSourceName;
    }
    
    public function authenticate()
    {
        if ( empty( $this->username ) || empty( $this->password ) )
        {
            return false;
        }
        
        $_POST['username'] = $this->username;
        $_POST['password'] = $this->password;
        
        if ( $this->authType === 'LDAP')
        {
            // CASUAL PATCH (Nov 21 2005) : due to a sort of bug in the
            // PEAR AUTH LDAP container, we add a specific option wich forces
            // to return attributes to a format compatible with the attribute
            // format of the other AUTH containers

            $this->extAuthOptionList ['attrformat'] = 'AUTH';
        }
        
        require_once 'Auth/Auth.php';

        $this->auth = new Auth( $this->authType, $this->extAuthOptionList, '', false);

        $this->auth->start();
        
        return $this->auth->getAuth();
    }
    
    public function getUserData()
    {
        $userAttrList = array('lastname'     => NULL,
                          'firstname'    => NULL,
                          'loginName'    => NULL,
                          'email'        => NULL,
                          'officialCode' => NULL,
                          'phoneNumber'  => NULL,
                          'isCourseCreator' => NULL,
                          'authSource'   => NULL);

        foreach($this->extAuthAttribNameList as $claroAttribName => $extAuthAttribName)
        {
            if ( ! is_null($extAuthAttribName) )
            {
                $userAttrList[$claroAttribName] = $this->auth->getAuthData($extAuthAttribName);
            }
        }
        
        foreach($userAttrList as $claroAttribName => $claroAttribValue)
        {
            if ( array_key_exists($claroAttribName, $this->extAuthAttribTreatmentList ) )
            {
                $treatmentCallback = $this->extAuthAttribTreatmentList[$claroAttribName];

                if ( is_callable( $treatmentCallback ) )
                {
                    $claroAttribValue = $treatmentCallback($claroAttribValue);
                }
                else
                {
                    $claroAttribValue = $treatmentCallback;
                }
            }

            $userAttrList[$claroAttribName] = $claroAttribValue;
        } // end foreach

        /* Two fields retrieving info from another source ... */

        $userAttrList['loginName' ] = $this->auth->getUsername();
        $userAttrList['authSource'] = $this->authSourceName;
        
        if ( isset($userAttrList['status']) )
        {
            $userAttrList['isCourseCreator'] = ($userAttrList['status'] == 1) ? 1 : 0;
        }
        
        return $userAttrList;
    }
    
    public static function fromConfig( $driverConfig )
    {
        /* $driver = new self(
            $driverConfig['driver']['authSourceType'],
            $driverConfig['driver']['authSourceName'],
            $driverConfig['extAuthOptionList'],
            $driverConfig['extAuthAttribNameList'],
            $driverConfig['extAuthAttribTreatmentList'],
            $driverConfig['extAuthAttribToIgnore']
        );*/
        
        $driver = new self( $driverConfig );
        
        return $driver;
    }
}
