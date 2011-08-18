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

/**
 * Authentication Profile class
 */
class AuthProfile
{
    protected
        $courseRegistrationAllowed = null,
        $defaultCourseProfile = null,
        $courseEnrolmentMode = null,
        $editableProfileFields = null,
        $userId,
        $authSource;
    
    public function __construct( $userId, $authSource )
    {
        $this->userId = $userId;
        $this->authSource = $authSource;
    }
    
    public function getUserId()
    {
        return $this->userId;
    }
    
    /**
     * Set the authentication profile options. Contains
     *  $data['canRegisterToCourses'] with value true, false or null (let the platform decide)
     *  $data['courseEnrolmentMode'] with value 'open', 'close', 'validation' or null (let the platform decide)
     *  $data['defaultCourseProfile'] profile attributed by default to the user when registering to a new course or null
     *  $data['editableProfileFields'] array of editable fileds in the user profile or null
     * @return $this
     */
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
    
    /**
     * Get the profile to give to the user when enroled in a course
     * @return string
     */
    public function getCourseProfile()
    {
        return $this->defaultCourseProfile;
    }
    
    /**
     * Get user enrolment mode
     * @return 'string'pending', 'auto' or null
     */
    public function getCourseRegistrationMode()
    {
        return $this->courseEnrolmentMode;
    }
    
    /**
     * Get the list of user profile fields editable for this user
     * @return array of string, editable field list
     */
    public function getEditableProfileFields()
    {
        return $this->editableProfileFields;
    }
}

class AuthProfileManager
{
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
            $authProfile = new AuthProfile( $userId, $authSource );
            $authProfile->setAuthDriverOptions(AuthDriverManager::getDriver( $authSource )->getAuthProfileOptions());
            
            pushClaroMessage(var_export(AuthDriverManager::getDriver( $authSource )->getAuthProfileOptions(), true), 'debug');
            
            return $authProfile;
        }
    }
}

class CourseUserRegistration
{
    const 
        STATUS_OK = 0,
        STATUS_REGISTRATION_FAILED = 1,
        STATUS_KEYVALIDATION_FAILED = 2,
        STATUS_SYSTEM_ERROR = 4,
        STATUS_REGISTRATION_NOTAVAILABLE = 8;
    
    protected 
        $admin = false, 
        $tutor = false, 
        $registerByClass = false,
        $userAuthProfile, 
        $course, 
        $givenCourseKey, 
        $categoryId;
    
    protected $status = 0, $errorMessage = '';
    
    /**
     *
     * @param AuthProfile $userAuthProfile profile of the user we want to enrol to the cours
     * @param ClaroCourse $course object representing the course
     * @param type $givenCourseKey optionnal given registration key (default null)
     * @param type $categoryId optionnal given categoryId (default null)
     */
    public function __construct( 
        AuthProfile $userAuthProfile, 
        ClaroCourse $course, 
        $givenCourseKey = null,
        $categoryId = null )
    {
        $this->userAuthProfile = $userAuthProfile;
        $this->course = $course;
        $this->givenCourseKey = $givenCourseKey;
        $this->categoryId = $categoryId;
        
        // is the user doing the registration a super user ?
        if ( claro_is_in_a_course() 
            && claro_get_current_course_id() == $this->course->courseId )
        {
            $this->isSuperUser = claro_is_platform_admin() 
                || claro_is_course_manager() 
                || claro_is_allowed_tool_edit( get_module_data( 'CLUSER', 'id' ) );
        }
        else
        {
            $this->isSuperUser = claro_is_platform_admin();
        }
    }
    
    public function getStatus()
    {
        return $this->status;
    }
    
    /**
     * Return the reason why addUser returned false
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }
    
    public function setCourseAdmin()
    {
        $this->admin = true;
    }
    
    public function setCourseTutor()
    {
        $this->tutor = true;
    }
    
    public function setClassRegistrationMode()
    {
        $this->registerByClass = true;
    }
    
    /**
     * Subscribe a specific user to a specific course.  If this course is a session
     * course, the user will also be subscribed to the source course.
     * @return boolean TRUE  if it succeeds, FALSE otherwise
     */
    public function addUser()
    {
        if ( !$this->isRegistrationAllowed() )
        {
            
            return false;
        }
        
        if ( !$this->checkRegistrationKey() )
        {
            $this->status = self::STATUS_KEYVALIDATION_FAILED;
            return false;
        }
        
        $userId = $this->userAuthProfile->getUserId();
        $courseCode = $this->course->courseId;
        
        $tbl_mdb_names          = claro_sql_get_main_tbl();
        $tbl_user               = $tbl_mdb_names['user'];
        $tbl_course             = $tbl_mdb_names['course'];
        $tbl_rel_course_user    = $tbl_mdb_names['rel_course_user'];

        if (  Claroline::getDatabase()->query("
            SELECT 
                user_id
            FROM 
                `{$tbl_user}`
            WHERE 
                user_id = " . Claroline::getDatabase()->escape($userId) )->numRows() == 0 )
        {
            $this->status = self::STATUS_SYSTEM_ERROR;
            $this->errorMessage = get_lang('User not found');
            return false;
        }
        else
        {
            // Previously check if the user isn't already subscribed to the course
            $courseUserListResultSet = Claroline::getDatabase()->query( "
                SELECT 
                    count_user_enrol, count_class_enrol
                FROM 
                    `{$tbl_rel_course_user}`
                WHERE 
                    user_id = " . Claroline::getDatabase()->escape($userId) . "
                AND 
                    code_cours = " . Claroline::getDatabase()->quote($courseCode) );

            if ( $courseUserListResultSet->numRows() > 0 )
            {
                $course_user_list = $courseUserListResultSet->fetch(Mysql_ResultSet::FETCH_OBJECT);
                
                $count_user_enrol = (int) $course_user_list->count_user_enrol;
                $count_class_enrol = (int) $course_user_list->count_class_enrol;

                // Increment the count of registration by the user or class
                if ( ! $this->registerByClass )  
                {
                    $count_user_enrol = 1;
                }
                else
                {
                    $count_class_enrol++;
                }

                if ( !Claroline::getDatabase()->exec("
                    UPDATE 
                        `{$tbl_rel_course_user}`
                    SET 
                        `count_user_enrol` = " . $count_user_enrol . ",
                        `count_class_enrol` = " . $count_class_enrol . "
                    WHERE 
                        user_id = " . Claroline::getDatabase()->escape($userId) . "
                    AND 
                        code_cours = " . Claroline::getDatabase()->quote($courseCode) 
                ) )
                {
                    $this->status = self::STATUS_SYSTEM_ERROR;
                    $this->errorMessage = get_lang('Cannot register user in course');
                    return false;
                }
                else
                {
                    return true;
                }
            }
            else
            {
                // First registration to the course
                $count_user_enrol = 0;
                $count_class_enrol = 0;

                // If a validation is requested for this course: isPending is true
                // If the user is course manager, never flag him as "pending"
                $isPending = !$this->admin && $this->isValidationRequired() ? 1 : 0;


                if ( ! $this->registerByClass )
                {
                    $count_user_enrol = 1;
                }
                else
                {
                    $count_class_enrol = 1;
                }

                if ( $this->admin )
                {
                    $profileId = claro_get_profile_id('manager');
                }
                else
                {
                    $profileId = claro_get_profile_id($this->getCourseProfile());
                }

                // If this course is a session course, enrol to the source course
                if ( $this->course->sourceCourseId )
                {
                    $sourceCourseCode = ClaroCourse::getCodeFromId( $this->course->sourceCourseId );

                    if ( !Claroline::getDatabase()->exec("INSERT INTO `" . $tbl_rel_course_user . "`
                            SET code_cours      = " . Claroline::getDatabase()->quote( $sourceCourseCode )  . ",
                                user_id         = " . (int) $userId . ",
                                profile_id      = " . (int) $profileId . ",
                                isCourseManager = " . (int) ($this->admin ? 1 : 0 ) . ",
                                isPending       = " . $isPending . ",
                                tutor           = " . (int) ($this->tutor ? 1 : 0) . ",
                                count_user_enrol = " . $count_user_enrol . ",
                                count_class_enrol = " . $count_class_enrol ) )
                    {
                        $this->status = self::STATUS_SYSTEM_ERROR;
                        $this->errorMessage = get_lang('Cannot register user in source course');
                        return false;
                    }
                }

                if ( !Claroline::getDatabase()->exec("INSERT INTO `" . $tbl_rel_course_user . "`
                        SET code_cours      = " . Claroline::getDatabase()->quote( $courseCode )  . ",
                            user_id         = " . (int) $userId . ",
                            profile_id      = " . (int) $profileId . ",
                            isCourseManager = " . (int) ($this->admin ? 1 : 0 ) . ",
                            isPending       = " . $isPending . ",
                            tutor           = " . (int) ($this->tutor ? 1 : 0) . ",
                            count_user_enrol = " . $count_user_enrol . ",
                            count_class_enrol = " . $count_class_enrol ) )
                {
                    $this->status = self::STATUS_SYSTEM_ERROR;
                    $this->errorMessage = get_lang('Cannot register user in source course');
                    return false;
                }
                else
                {
                    return true;
                }
            }
        } // end else user register in the platform
    }
    
    // business logic...
    
    /**
     * Get user enrolment mode
     * @return 'string'pending', 'auto' or null
     */
    protected function getCourseRegistrationMode()
    {
        if ( $this->isSuperUser )
        {
            return 'open';
        }
        
        $authProfileRegistrationMode = $this->userAuthProfile->getCourseRegistrationMode();
        
        if ( empty( $authProfileRegistrationMode ) )
        {
            return $this->course->registration;
        }
        else
        {
            return $authProfileRegistrationMode;
        }
    }
    
    /**
     * Is the user allowed to enrol to the course
     * @return boolean 
     */
    protected function isRegistrationAllowed()
    {
        if ( $this->isSuperUser )
        {
            return true;
        }

        if ( get_conf( 'allowToSelfEnroll', true ) )
        {

            if( $this->isCourseRegistrationAllowed() )
            {
                return $this->checkRegistrationKey();
            }
            else
            {
                return false;
            }
        }
        else
        {
            $this->status = self::STATUS_REGISTRATION_NOTAVAILABLE;
            $this->errorMessage = get_lang('Course self-enrolment is not allowed on this platform.');
            return false;
        }
    }
    
    /**
     * Is the validation required
     * @return boolean
     */
    public function isValidationRequired()
    {
        return !$this->isSuperUser && ($this->getCourseRegistrationMode() == 'validation');
    }
    
    
    /**
     * Get the profile name in the course
     * @return string 
     */
    protected function getCourseProfile ()
    {
        return $this->userAuthProfile->getCourseProfile();
    }
    
    protected function checkRegistrationKey()
    {
        if ( $this->course->registration == 'open' && !empty( $this->course->registrationKey ) )
        {
            if ( $this->givenCourseKey != $this->course->registrationKey )
            {
                $this->errorMessage = get_lang('Invalid enrolment key given');
                $this->status = self::STATUS_KEYVALIDATION_FAILED;
                return false;
            }
            else
            {
                return true;
            }
        }
        else
        {
            return true;
        }
    }
    
    protected function isCourseRegistrationAllowed()
    {
        $curdate = claro_time();
        
        if ( !is_null( $this->categoryId ) && ! $this->isAllowedToRegisterToCategory() )
        {
            $this->status = self::STATUS_REGISTRATION_FAILED;
            $isUserAllowedToEnrol = false;
            $this->errorMessage = get_lang('You have to be registered to this course\'s category in order to enrol the course');
        }
        elseif ( $this->isUserLimitExceeded() )
        {
            $this->status = self::STATUS_REGISTRATION_FAILED;
            $isUserAllowedToEnrol = false;
            $this->errorMessage = get_lang('The users limit for this course has been reached');
        }
        elseif ( !in_array( $this->getCourseRegistrationMode(), array('open', 'validation') ) )
        {
            $isUserAllowedToEnrol = false;
            $this->status = self::STATUS_REGISTRATION_NOTAVAILABLE;
            $this->errorMessage = get_lang(
                'This course currently does not allow new enrolments (registration: %registration)', 
                array('%registration' => $this->getCourseRegistrationMode()) );
        }
        elseif ( !in_array( $this->course->status, array('enable', 'date') ) )
        {
            $isUserAllowedToEnrol = false;
            $this->status = self::STATUS_REGISTRATION_NOTAVAILABLE;
            $this->errorMessage = get_lang(
                'This course currently does not allow new enrolments (status: %status)', 
                array('%status' => $this->course->status));
        }
        elseif ( $this->course->status == 'date' && !empty($this->course->publicationDate) && $this->course->publicationDate >= $curdate )
        {
            $isUserAllowedToEnrol = false;
            $this->status = self::STATUS_REGISTRATION_NOTAVAILABLE;
            $this->errorMessage = get_lang(
                'This course will be enabled on the %date',
                array('%date' => claro_date('d/m/Y', $this->course->publicationDate)));
        }
        elseif ( $this->course->status == 'date' && !empty($this->course->expirationDate) && $this->course->expirationDate <= $curdate )
        {
            $isUserAllowedToEnrol = false;
            $this->status = self::STATUS_REGISTRATION_NOTAVAILABLE;
            $this->errorMessage = get_lang(
                'This course has been disabled on the %date',
                array('%date' => claro_date('d/m/Y', $this->course->expirationDate)));
            
        }
        elseif ( $this->course->status == 'date' 
            && ( empty($this->course->expirationDate) && empty($this->course->publicationDate) ) )
        {
            $isUserAllowedToEnrol = false;
            $this->status = self::STATUS_SYSTEM_ERROR;
            $this->errorMessage = get_lang('This course is not available');
            Console::error( 
                "Invalid publication and expiration date for course " . $this->course->courseId );
        }
        else
        {
            $isUserAllowedToEnrol = true;
        }
        
        return $isUserAllowedToEnrol;
    }
    
    protected function isAllowedToRegisterToCategory()
    {
        if ( $this->isSuperUser )
        {
            return true;
        }
        
        if( get_conf( 'registrationRestrictedThroughCategories', false ) )
        {
            if ( !ClaroCategory::isRegistredToCategory( $this->userAuthProfile->getUserId(), $this->categoryId ) )
            {
                return false;
            }
            else
            {
                return true;
            }
        }
        else
        {
            return true;
        }
    }
    
    protected function isUserLimitExceeded()
    {
        if ( $this->course->userLimit != 0 
            && $this->countCourseUsers() >= $this->course->userLimit )
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    
    protected function countCourseUsers()
    {
        $tbl_mdb_names          = claro_sql_get_main_tbl();
        $tbl_rel_course_user    = $tbl_mdb_names['rel_course_user'];
        
        return Claroline::getDatabase()->query("
            SELECT *
            FROM `{$tbl_rel_course_user}`
            WHERE code_cours = " . Claroline::getDatabase()->quote($this->course->courseId) . "
            AND tutor = 0
            AND isCourseManager = 0")->numRows();
    }
}
