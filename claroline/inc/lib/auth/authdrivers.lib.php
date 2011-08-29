<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * Authentication Drivers
 *
 * @version     1.11 $Revision$
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 * @author      Claroline Team <info@claroline.net>
 * @author      Frederic Minne <zefredz@claroline.net>
 * @license     http://www.gnu.org/copyleft/gpl.html
 *              GNU GENERAL PUBLIC LICENSE version 2 or later
 * @package     kernel.auth
 */

interface AuthDriver
{
    public function setDriverOptions( $driverConfig );
    
    public function setAuthenticationParams( $username, $password );
    public function authenticate();
    
    public function getUserData();
    public function getFilteredUserData();
    public function getAuthSource();
    
    public function userRegistrationAllowed();
    public function userUpdateAllowed();
    
    public function getFailureMessage();

    public function getAuthProfileOptions();
}

abstract class AbstractAuthDriver implements AuthDriver
{
    protected 
        $userId = null,
        $username = null, 
        $password = null,
        $authSourceName;
    
    protected
        $driverConfig,
        $userRegistrationAllowed = false,
        $userUpdateAllowed = false,
        
        $extAuthOptionList,
        $extAuthAttribNameList,
        $extAuthAttribTreatmentList,
        $extAuthIgnoreUpdateList = array(),
        
        $authProfileOptions = array(
            'courseRegistrationAllowed' => null,
            'courseEnrolmentMode' => null, 
            'defaultCourseProfile' => null, 
            'editableProfileFields' => null 
        );
    
    
    protected $extraMessage = null;
    
    // abstract public function getUserData();
    
    public function setDriverOptions( $driverConfig )
    {
        $this->driverConfig = $driverConfig;
        $this->authSourceName = $driverConfig['driver']['authSourceName'];
        
        $this->userRegistrationAllowed = isset( $driverConfig['driver']['userRegistrationAllowed'] )
            ? $driverConfig['driver']['userRegistrationAllowed']
            : false
            ;
        $this->userUpdateAllowed = isset( $driverConfig['driver']['userUpdateAllowed'] )
            ? $driverConfig['driver']['userUpdateAllowed']
            : false
            ;
            
        $this->extAuthOptionList = $driverConfig['extAuthOptionList'];
        $this->extAuthAttribNameList = $driverConfig['extAuthAttribNameList'];
        $this->extAuthAttribTreatmentList = $driverConfig['extAuthAttribTreatmentList'];
        $this->extAuthIgnoreUpdateList = $driverConfig['extAuthAttribToIgnore'];

        // @since 1.9.9 
        $this->authProfileOptions = isset($driverConfig['authProfileOptions'])
            ? $driverConfig['authProfileOptions']
            : array( 
                'courseRegistrationAllowed' => null,
                'courseEnrolmentMode' => null, 
                'defaultCourseProfile' => null, 
                'editableProfileFields' => null )
            ;
    }
    
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
    
    public function getFilteredUserData()
    {
        $data  = $this->getUserData();
        
        if ( ! is_array($data) )
        {
            return array();
        }
        
        foreach ( $data as $key => $value )
        {
            if ( in_array( $key, $this->extAuthIgnoreUpdateList ) )
            {
                unset( $data[$key] );
            }
        }
        
        return $data;
    }
    
    public function userRegistrationAllowed()
    {
        return false;
    }
    
    public function userUpdateAllowed()
    {
        return false;
    }

    /**
     * @since 1.9.9
     * @return <type>
     */
    public function getAuthProfileOptions()
    {
        return $this->authProfileOptions;
    }
}

abstract class LocalDatabaseAuthDriver extends AbstractAuthDriver
{
    protected $userId;
    
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
            . "AND authSource = '".$this->getAuthSource()."'" . "\n"
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
    
    public function userRegistrationAllowed()
    {
        return $this->userRegistrationAllowed;
    }
    
    public function userUpdateAllowed()
    {
        return $this->userUpdateAllowed;
    }
    
    public function getAuthSource()
    {
        return $this->authSourceName;
    }
    
    public function getUserData()
    {
        return null;
    }
    
    public function getFilteredUserData()
    {
        return array();
    }
}

class ClarolineLocalAuthDriver extends LocalDatabaseAuthDriver
{
    public function getAuthSource()
    {
        return 'claroline';
    }
}

class TemporaryAccountAuthDriver extends LocalDatabaseAuthDriver
{
    protected $failureMsg = null;
    
    public function getAuthSource()
    {
        return 'temp';
    }
    
    public function getFilteredUserData()
    {
        return array();
    }
    
    public function authenticate()
    {
        if ( parent::authenticate() )
        {
            $tbl = claro_sql_get_main_tbl();
            
            $sql = "SELECT propertyValue\n"
                . "FROM `{$tbl['user_property']}`\n"
                . "WHERE "
                . "userId = ". Claroline::getDatabase()->quote(parent::userId) . "\n"
                . "AND propertyId = 'accountExpirationDate'"
                ;

            $res = Claroline::getDatabase()->query( $sql );

            if ( $res->numRows() )
            {
                $date = $res->fetch(Database_ResultSet::FETCH_VALUE);

                if ( strtotime($date) <= time() )
                {
                    $this->setFailureMessage(
                        get_lang(
                            "Your account has expired, please contact the platform adminitrator."
                            )
                        );

                    return false;
                }
                else
                {
                    return true;
                }
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }
    
    public function getUserData()
    {
        return null;
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
    
    public function getAuthSource()
    {
        return 'disabled';
    }
    
    public function authenticate()
    {
        return false;
    }
    
    public function getUserData()
    {
        return null;
    }
    
    public function getFilteredUserData()
    {
        return array();
    }

    public function getAuthProfileOptions()
    {
        return array(
            'courseRegistrationAllowed' => null,
            'courseEnrolmentMode' => null,
            'defaultCourseProfile' => null,
            'editableProfileFields' => null
        );
    }
    
    public function userRegistrationAllowed()
    {
        return false;
    }
    
    public function userUpdateAllowed()
    {
        return false;
    }
    
    public function setDriverOptions($driverConfig)
    {
        parent::setDriverOptions($driverConfig);// nothing to do;
    }
}

