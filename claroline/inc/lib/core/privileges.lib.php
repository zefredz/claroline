<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * User privileges classes
 * 
 * WARNING! In order to get a more refined behaviour when checking access
 * rights and privileges, this class behaves differently than the init.lib 
 * functions.
 * 
 * One of the main changes is that some privileges combined in 
 * init.lib have been left separated in the nbew privileges classes. Another 
 * one is that the platform administration and course manager profiles of the 
 * user that calls the new classes are not taken into account anymore when 
 * evaluating the privileges of another user.
 * 
 * @version     1.12 $Revision$
 * @copyright   (c) 2001-2014, Universite catholique de Louvain (UCL)
 * @author      Claroline Team <info@claroline.net>
 * @author      Frederic Minne <zefredz@claroline.net>
 * @license     http://www.gnu.org/copyleft/gpl.html
 *              GNU GENERAL PUBLIC LICENSE version 2 or later
 * @package     kernel.core.accessmanager
 */

require_once __DIR__ . '/../utils/iterators.lib.php';
require_once __DIR__ . '/../kernel/user.lib.php';
require_once __DIR__ . '/../kernel/course.lib.php';
require_once __DIR__ . '/../kernel/groupteam.lib.php';

/**
 * Platform level privileges for a user
 */
class Claro_UserPrivileges
{
    protected 
        $userId,
        $userPrivileges,
        $userData,
        $database;
    
    public function __construct( $user = null, $database = null )
    {
        $this->userPrivileges = array();
        
        if ( $user )
        {
            $this->userId = $user->userId;
            $this->user = $user;
        }
        else
        {
            $this->user = null;
            $this->userId = null;
        }
        
        $this->database = $database ? $database : Claroline::getDatabase();
    }
    
    public function isAuthenticated()
    {
        return (bool) $this->userId;
    }
    
    // helpers
    
    public function getCoursePrivileges( $course )
    {
        if ( ! isset ( $this->userPrivileges[$course->courseId] ) )
        {
            $this->userPrivileges[$course->courseId] = new Claro_CourseUserPrivileges( $this, $course, $this->database );
        }
        
        return $this->userPrivileges[$course->courseId];
    }
    
    public function getGroupPrivileges( $course, $group )
    {
        return $this->getUserCoursePrivilege( $course )
            ->getGroupPrivileges( $group );
    }
    
    // rights
    
    public function isCourseCreator()
    {
        return $this->isAuthenticated() && $this->user->isCourseCreator;
    }
    
    public function isPlatformAdmin()
    {
        return $this->isAuthenticated() && $this->user->isPlatformAdmin;
    }
    
    public function isSuperUser()
    {
        return $this->isPlatformAdmin();
    }
    
    public function getUserId()
    {
        return $this->userId;
    }
    
    public function getUser()
    {
        return $this->user;
    }
}

/**
 * Course level privileges for a user
 *
 * WARNING! In order to get a more refined behaviour when checking access
 * rights, this class behaves differently than the init.lib function
 * claro_get_course_user_privilege() :
 * 
 *  1) the platform admin status given by Claro_UserPrivileges::isPlatformAdmin
 *      is not taken into account when evaluating 
 *      Claro_CourseUserPrivileges::isCourseManager
 * 
 *  2) Claro_CourseUserPrivileges::isPending is not taken into account when 
 *      evaluating Claro_CourseUserPrivileges::isCourseMember
 * 
 * But the combined user privileges are used when evaluating the following 
 * methods :
 * 
 *  1) Claro_CourseUserPrivileges::isCourseAllowed, which takes both 
 *      Claro_CourseUserPrivileges::isPending AND 
 *      Claro_CourseUserPrivileges::isCourseMember into account along with other
 *      privileges
 * 
 *  2) Claro_CourseUserPrivileges::isSuperUser takes both 
 *      Claro_CourseUserPrivileges::isCourseMananer AND 
 *      Claro_UserPrivileges::isPlatformAdmin into account  along with other
 *      privileges
 */
class Claro_CourseUserPrivileges
{
    protected 
        $userId,
        $courseId,
        $coursePrivileges, 
        $userPrivileges, 
        $courseUserProfile = false,
        $database;

    /**
     * Course level privileges for the given user
     * @param Claro_UserPrivileges $userPrivileges privileges of the user at the platform level
     * @param Claro_Course $course course in which the privileges are defined
     * @param Database_Connection $database optionnal database connection (use default connection if  none is given)
     */
    public function __construct( $userPrivileges, $course, $database = null )
    {
        $this->database = $database ? $database : Claroline::getDatabase();
        
        $this->courseId = $course->courseId;
        $this->userId = $userPrivileges->getUserId();
        
        $this->userPrivileges = $userPrivileges;
        
        $this->coursePrivileges = $this->loadCourseUserPrivileges();
        
        $this->course = $course;        
    }
    
    /**
     * Get the user profile in the course
     * @return Claro_CourseUserProfile
     */
    public function getCourseUserProfile()
    {
        if ( ! $this->courseUserProfile )
        {
            $this->courseUserProfile = new Claro_CourseUserProfile( $this );
        }
        
        
        return $this->courseUserProfile;        
    }
    
    protected function loadCourseUserPrivileges()
    {
        $course_user_privilege = array();
        
        if ( $this->userPrivileges->isAuthenticated() )
        {
            $tbl_mdb_names = claro_sql_get_main_tbl();
            $tbl_rel_course_user = $tbl_mdb_names['rel_course_user'];

            $result = $this->database->query("
                SELECT profile_id AS profileId,
                       isCourseManager,
                       isPending,
                       tutor,
                       count_class_enrol
                FROM `" . $tbl_rel_course_user . "`
                WHERE `user_id`  = " . $this->database->escape( $this->userPrivileges->getUserId() ) . "
                AND `code_cours` = " . $this->database->quote( $this->courseId ) );
            
            if ( $result->numRows() )
            {
                $cuData = $result->fetch();

                $course_user_privilege['_profileId']        = $cuData['profileId'];
                
                $course_user_privilege['is_courseMember']   = true; // (bool) ($cuData['isPending' ] == 0 );
                $course_user_privilege['is_coursePending']   = (bool) ($cuData['isPending' ]);
                $course_user_privilege['is_courseTutor']    = (bool) ($cuData['tutor' ] == 1 );
                $course_user_privilege['is_courseAdmin']    = (bool) ($cuData['isCourseManager'] == 1);
                $course_user_privilege['is_registeredByClass'] = ((int)$cuData['count_class_enrol'] > 0);
            }
            else // this user has no status related to this course
            {
                $course_user_privilege['_profileId'] = claro_get_profile_id('guest');

                $course_user_privilege['is_courseMember']   = false;
                $course_user_privilege['is_coursePending']  = false;
                $course_user_privilege['is_courseAdmin']    = false;
                $course_user_privilege['is_courseTutor']    = false;
                $course_user_privilege['is_registeredByClass'] = false;
            }
        }
        else // the user is anonymous
        {
            $course_user_privilege['_profileId'] = claro_get_profile_id('anonymous');
            
            $course_user_privilege['is_courseMember']   = false;
            $course_user_privilege['is_coursePending']  = false;
            $course_user_privilege['is_courseAdmin']    = false;
            $course_user_privilege['is_courseTutor']    = false;
            $course_user_privilege['is_registeredByClass'] = false;
        }

        return $course_user_privilege;
    }
    
    /**
     * Get the id of the course in which those privileges are defined for the user
     * @return type
     */
    public function getCourseId()
    {
        return $this->courseId;
    }
    
    /**
     * Get the course in which those privileges are defined for the user
     * @return Claro_Course
     */
    public function getCourse()
    {
        return $this->course;
    }
    
    /**
     * Is the user a course manager ?
     * @return bool
     */
    public function isCourseManager()
    {
        return $this->coursePrivileges[ 'is_courseAdmin' ];
    }
    
    /**
     * Is the user a tutor within the course
     * @return bool
     */
    public function isCourseTutor()
    {
        return $this->coursePrivileges[ 'is_courseTutor' ];
    }
    
    /**
     * Is the user a member of the course ?
     * @return bool
     */
    public function isCourseMember()
    {
        return $this->coursePrivileges[ 'is_courseMember' ];
    }
    
    public function isCourseAllowed()
    {
        return ( $this->isCourseMember() && ! $this->isEnrolmentPending () )
            || $this->course->access == 'public'
            || ( $this->course->access == 'platform' && $this->userPrivileges->isAuthenticated() )
            || $this->isSuperUser()
            ;
    }
    
    /**
     * Does the user have super user privileges in this course ?
     * @return bool
     */
    public function isSuperUser()
    {
        return $this->userPrivileges->isSuperUser() 
            || $this->isCourseManager()
            ;
    }
    
    /**
     * Is the user's enrolment in the course pending ?
     * @return bool
     */
    public function isEnrolmentPending()
    {
        return $this->coursePrivileges[ 'is_coursePending'];
    }
    
    /**
     * Is the register through a class ?
     * @return bool
     */
    public function isRegisteredByClass()
    {
        return $this->coursePrivileges[ 'is_registeredByClass'];
    }
    
    public function getProfileId()
    {
        return $this->coursePrivileges[ '_profileId' ];
    }
    
    public function getGroupPrivileges( $group )
    {
        if ( ! isset ( $this->groupPrivileges[$group->id] ) )
        {
            $this->groupPrivileges[$group->id] = new Claro_GroupUserPrivileges( $this->userPrivileges, $this, $group, $this->database );
        }
        
        return $this->groupPrivileges[$group->id];
    }
    
    public static function fromArray( $courseId, $userId = null, $data = null, $database = null )
    { 
        $course = new Claro_Course($courseId);
        $course->load();
        
        if ( $userId )
        {
            $user = new Claro_User($userId);
            $user->load();
        }
        else
        {
            $user = null;
        }

        $userPrivileges = new Claro_UserPrivileges( $user, $database ? $database : Claroline::getDatabase() );

        $priv = new self( $userPrivileges, $course );

        if ( !empty($data) )
        {
            $course_user_privilege['_profileId']        = $data['profileId'];
            $course_user_privilege['is_coursePending']  = (bool) ($data['isPending' ]);
            $course_user_privilege['is_courseMember']   = true;
            $course_user_privilege['is_courseTutor']    = (bool) ($data['tutor'] == 1 );
            $course_user_privilege['is_courseAdmin']    = (bool) ($data['isCourseManager'] == 1 );
            $course_user_privilege['is_registeredByClass'] = ((int)$data['count_class_enrol'] > 0);
        }
        else
        {
            $course_user_privilege['_profileId']        = $userId ? claro_get_profile_id( GUEST_PROFILE ) : claro_get_profile_id ( ANONYMOUS_PROFILE );
            $course_user_privilege['is_coursePending']  = false;
            $course_user_privilege['is_courseMember']   = false;
            $course_user_privilege['is_courseTutor']    = false;
            $course_user_privilege['is_courseAdmin']    = false;
            $course_user_privilege['is_registeredByClass'] = false;
        }
        
        $priv->coursePrivileges = $course_user_privilege;

        return $priv;
    }
}

class Claro_GroupUserPrivileges
{
    protected 
        $userPrivileges, 
        $coursePrivileges, 
        $groupId,
        $_isGroupMember,
        $database;
    
    public function __construct( $userPrivileges, $coursePrivileges, $group, $database = null )
    {
        $this->database = $database ? $database : Claroline::getDatabase();
        
        $this->userPrivileges = $userPrivileges;
        $this->coursePrivileges = $coursePrivileges;
        $this->groupId = $group->id;
        $this->groupProperties = $this->coursePrivileges->getCourse()->getGroupProperties();
        $this->_isGroupMember = null;
        
        $this->group = $group;
        
    }
    
    protected function loadGroupMembership()
    {
        if ( $this->userPrivileges->isAuthenticated() && $this->coursePrivileges->isCourseMember() )
        {
            $userGroupProperties = $this->group->getUserProperties( $this->userPrivileges->getUser() );

            $this->_isGroupMember = $userGroupProperties['isGroupMember'];
        }
        else
        {
            $this->_isGroupMember = false;
        }
    }
    
    public function getGroupId()
    {
        return $this->groupId;
    }
    
    public function isGroupMember()
    {
        if ( is_null( $this->_isGroupMember ) )
        {
            $this->loadGroupMembership();
        }
        
        return $this->_isGroupMember;
    }
    
    public function isGroupTutor()
    {
        return $this->userPrivileges->isAuthenticated() 
            && $this->groupProperties['tutorId'] == $this->userPrivileges->getUserId()
            ;
    }
    
    public function isAllowedInGroup()
    {
        return !$this->groupProperties['private']
            || $this->isGroupMember()
            || $this->isSuperUser()
            ;
    }
    
    public function isSuperUser()
    {
        return $this->coursePrivileges->isSuperUser() 
            || $this->isGroupTutor()
            ;
    }
}


class Claro_CourseUserProfile
{
    protected 
        $coursePrivileges, 
        $profileRightList = null,
        $database;
    
    public function __construct( $coursePrivileges, $database = null )
    {
        $this->database = $database ? $database : Claroline::getDatabase();
        
        $this->coursePrivileges = $coursePrivileges;
        
        $profile = new RightProfile();

        $profile->load( $this->coursePrivileges->getProfileId() );

        $courseProfileToolRight = new RightCourseProfileToolRight();
        $courseProfileToolRight->setCourseId( $this->coursePrivileges->getCourseId() );
        $courseProfileToolRight->load( $profile );

        $this->profileRightList = $courseProfileToolRight->getToolActionList();
        
    }
    
    public function getProfileId()
    {
        return $this->coursePrivileges->getProfiledId();
    }
    
    public function profileAllowsToRead ( Claro_Module $module )
    {
        pushClaroMessage('check course profile allows read','debug');
        return $this->profileAllowsAction( $module, Claro_AccessManager::ACCESS_READ );
    }
    
    public function profileAllowsToEdit ( Claro_Module $module )
    {
        pushClaroMessage('check course profile allows edit','debug');
        return $this->profileAllowsAction( $module, Claro_AccessManager::ACCESS_EDIT );
    }
    
    protected function profileAllowsAction ( Claro_Module $module, $action )
    {
        pushClaroMessage('check course profile allows action','debug');
        
        return isset($this->profileRightList[$module->getMainToolId()][$action]) && $this->profileRightList[$module->getMainToolId()][$action];
    }
}

class Claro_CourseUserPrivilegesIterator extends RowToObjectArrayIterator
{
    public function __construct( $userId, $data )
    {
        $this->userId = $userId;
        
        parent::__construct($data);
    }
    
    public function current()
    {
        $data = $this->collection[$this->key()];
        
        if ( !isset( $data['courseId'] ) )
        {
            throw new Exception("Missing courseId in data");
        }
        
        return Claro_CourseUserPrivileges::fromArray( $this->userId, $data['courseId'], $data );
    }
}

class Claro_CourseUserPrivilegesList
{
    protected $userId, $coursePrivilegesList;
    
    public function __construct( $userId = null, $database = null )
    {
        $this->database = $database ? $database : Claroline::getDatabase();
        $this->userId = $userId;
        $this->coursePrivilegesList = array();
    }
    
    public function load()
    {
        if ( $this->userId )
        {
            $tbl_mdb_names = claro_sql_get_main_tbl();

            $coursePrivilegesList = $this->database->query("
                SELECT
                    cu.code_cours AS courseId,
                    cu.profile_id AS profileId,
                    cu.isCourseManager,
                    cu.isPending,
                    cu.tutor,
                    cu.count_class_enrol
                FROM
                    `{$tbl_mdb_names['rel_course_user']}` `cu`
                WHERE
                    cu.`user_id`  = " . $this->database->escape($this->userId) );

            foreach ( $coursePrivilegesList as $coursePrivileges )
            {
                $this->coursePrivilegesList[$coursePrivileges['courseId']] = $coursePrivileges;
            }
        }
        else
        {
            $coursePrivilegesList = array();
        }
    }
    
    public function getCoursePrivileges( $courseCode )
    {
        if ( isset( $this->coursePrivilegesList[$courseCode] ) )
        {
            $priv = Claro_CourseUserPrivileges::fromArray($courseCode, $this->userId, $this->coursePrivilegesList[$courseCode] );
        }
        else
        {
            $priv = Claro_CourseUserPrivileges::fromArray($courseCode, $this->userId);
        }
        
        return $priv;
    }
    
    public function getIterator()
    {
        $it = new Claro_CourseUserPrivilegesIterator( $this->userId, $this->coursePrivilegesList );
        
        return $it;
    }
}
