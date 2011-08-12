<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * Authentication Profiles
 *
 * @version     1.11 $Revision$
 * @copyright   2001-2011 Universite catholique de Louvain (UCL)
 * @author      Claroline Team <info@claroline.net>
 * @author      Frederic Minne <zefredz@claroline.net>
 * @license     http://www.gnu.org/copyleft/gpl.html
 *              GNU GENERAL PUBLIC LICENSE version 2 or later
 * @package     kernel.auth
 */

interface AuthProfileInterface
{
    /**
     * Set the authentication profile options. Contains
     *  $data['canRegisterToCourses'] with value true, false or null (let the platform decide)
     *  $data['courseEnrolmentMode'] with value 'open', 'close', 'validation' or null (let the platform decide)
     *  $data['defaultCourseProfile'] profile attributed by default to the user when registering to a new course or null
     *  $data['editableProfileFields'] array of editable fileds in the user profile or null
     * @return $this
     */
    public function setAuthDriverOptions( $data );
    
    /**
     * Get the profile to give to the user when enroled in a course
     * @return string
     */
    public function getCourseProfile();

    /**
     * Get user enrolment mode
     * @return 'string'pending', 'auto' or null
     */
    public function getCourseRegistrationMode();

    /**
     * Get the list of user profile fields editable for this user
     * @return array of string, editable field list
     */
    public function getEditableProfileFields();

    /**
     * Is the user allowed to change profile picture ?
     * @return bool
     */
    //public function canChangeProfilePicture();
}

/**
 * Default Authentication Profile class
 */
class AuthProfile implements AuthProfileInterface
{
    protected
        $courseRegistrationAllowed = null,
        $defaultCourseProfile = null,
        $courseEnrolmentMode = null,
        $editableProfileFields = null;

    public function setAuthDriverOptions( $data )
    {
        if ( isset($data['courseEnrolmentMode']) )
        {
            $this->courseEnrolmentMode = $data['courseEnrolmentMode'];
        }
        else
        {
            $this->courseEnrolmentMode = null;
        }

        if ( isset($data['defaultCourseProfile']) && ! is_null($data['defaultCourseProfile']) )
        {
            $this->defaultCourseProfile = $data['defaultCourseProfile'];
        }
        else
        {
            $this->defaultCourseProfile = 'user';
        }

        if ( isset($data['editableProfileFields']) && ! is_null($data['editableProfileFields']) )
        {
            $this->editableProfileFields = $data['editableProfileFields'];
        }
        else
        {
            load_kernel_config('CLPROFIL');
            $this->editableProfileFields = get_conf('profile_editable');
        }
        
        return $this;
    }

    public function getCourseProfile()
    {
        return $this->defaultCourseProfile;
    }

    public function getCourseRegistrationMode()
    {
        return $this->courseEnrolmentMode;
    }

    public function getEditableProfileFields()
    {
        return $this->editableProfileFields;
    }
}

class AuthProfileManager
{
    
    public function getAuthProfile( $authSource )
    {
        $authProfile = new AuthProfile( AuthDriverManager::getDriver( $authSource )->getAuthProfileData() );
        
        pushClaroMessage(var_export( AuthDriverManager::getDriver( $authSource )->getAuthProfileData(), true), 'debug' );
        
        return $authProfile;
    }

    public function getUserAuthProfile( $userId )
    {
        if ( $userId != claro_get_current_user_id() )
        {
            $user = new Claro_User($userId);
            $user->loadFromDatabase();
        }
        else
        {
            $user = Claro_CurrentUser::getInstance();
        }
        
        $authSource = $user->authSource;
        
        if ( ! $authSource )
        {
            throw new Exception("Cannot find user authentication source for user {$userId}");
        }
        else
        {
            $authProfile = new AuthProfile();
            $authProfile->setAuthDriverOptions(AuthDriverManager::getDriver( $authSource )->getAuthProfileOptions());
            
            pushClaroMessage(var_export(AuthDriverManager::getDriver( $authSource )->getAuthProfileOptions(), true), 'debug');
            
            return $authProfile;
        }
    }
}

class CourseAuthProfilePermission
{
    protected $userAuthProfile, $courseId;
    
    public function __construct( AuthProfile $userAuthProfile, $courseId )
    {
        $this->userAuthProfile = $userAuthProfile;
        $this->courseId = $courseId;
    }
    
    public function isRegistrationAllowed()
    {
        $profileRegistrationMode = $this->userAuthProfile->getCourseRegistrationMode();
        
        if ( is_null( $profileRegistrationMode ) )
        {
            if ( get_conf('allowToSelfEnroll') )
            {
                return is_course_registration_allowed($this->courseId);
            }
            else
            {
                return false;
            }
        }
        else
        {
            return $profileRegistrationMode != 'close';
        }
    }
    
    public function getCourseRegistrationMode ()
    {
        $profileRegistrationMode = $this->userAuthProfile->getCourseRegistrationMode();
        
        if ( is_null( $profileRegistrationMode ) )
        {
            if ( get_conf('allowToSelfEnroll') )
            {
                if ( $this->courseId == claro_get_current_course_id() )
                {
                    return claro_get_current_course_data('registration');
                }
                else
                {
                    $courseData = claro_get_course_data($this->courseId);
                    
                    return $courseData['registration'];
                }
            }
            else
            {
                return 'close';
            }
        }
        else
        {
            return $profileRegistrationMode;
        }
    }
    
    public function getCourseProfile ()
    {
        return $this->userAuthProfile->getCourseProfile();
    }
}
