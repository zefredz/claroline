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
 * @version     Claroline 1.12 $Revision$
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
    
    /**
     * Get the privileges object for a user
     * @param Claro_User $user
     * @param Database_Connection $database
     */
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
    
    /**
     * Is the user authenticated
     * @return bool
     */
    public function isAuthenticated()
    {
        return (bool) $this->userId;
    }
    
    // helpers
    
    /**
     * Get the privileges of the user in the given course
     * @param Claro_Course $course
     * @return Claro_CourseUserPrivileges
     */
    public function getCoursePrivileges( $course )
    {
        if ( ! isset ( $this->userPrivileges[$course->courseId] ) )
        {
            $this->userPrivileges[$course->courseId] = new Claro_CourseUserPrivileges( $this, $course, $this->database );
        }
        
        return $this->userPrivileges[$course->courseId];
    }
    
    /**
     * Get the privileges of the user in the given group of the given course
     * @param Claro_Course $course
     * @param Claro_GroupTeam $group
     * @return Claro_CourseUserPrivileges
     */
    public function getGroupPrivileges( $course, $group )
    {
        return $this->getCoursePrivileges( $course )
            ->getGroupPrivileges( $group );
    }
    
    // rights
    
    /**
     * Is the user allowed to create courses ?
     * @return bool
     */
    public function isCourseCreator()
    {
        return $this->isAuthenticated() && $this->user->isCourseCreator;
    }
    
    /**
     * Is the user a platform administrator ?
     * @return bool
     */
    public function isPlatformAdmin()
    {
        return $this->isAuthenticated() && $this->user->isPlatformAdmin;
    }
    
    /**
     * Is the user a super user at the platform level ?
     * @return bool
     */
    public function isSuperUser()
    {
        return $this->isPlatformAdmin();
    }
    
    /**
     * Get the id of the user
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }
    
    /**
     * Get the user
     * @return Claro_User
     */
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
            $this->courseUserProfile = new Claro_CourseUserProfile( $this->userPrivileges, $this );
        }
        
        
        return $this->courseUserProfile;        
    }
    
    /**
     * Load the privileges of the user in the course
     * @return array
     */
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
    
    /**
     * Is the user allowed to access the course ?
     * @return bool
     */
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
    
    /**
     * Get the id of the profile of the user in the course
     * @return type
     */
    public function getProfileId()
    {
        if ( $this->isEnrolmentPending () )
        {
            return claro_get_profile_id('guest');
        }
        else
        {
            return $this->coursePrivileges[ '_profileId' ];
        }
    }
    
    /**
     * Get the id of the profile of the user in the course
     * @return type
     */
    public function getRealProfileId()
    {
        return $this->coursePrivileges[ '_profileId' ];
    }
    
    /**
     * Get the privileges of the user in the given group of this course
     * @param Claro_GroupTeam $group
     * @return Claro_GroupUserPrivileges
     */
    public function getGroupPrivileges( $group )
    {
        if ( ! isset ( $this->groupPrivileges[$group->id] ) )
        {
            $this->groupPrivileges[$group->id] = new Claro_GroupUserPrivileges( $this->userPrivileges, $this, $group, $this->database );
        }
        
        return $this->groupPrivileges[$group->id];
    }
    
    /**
     * Get User Privileges
     * @return Claro_UserPrivileges
     */
    public function getUserPrivileges()
    {
        return $this->userPrivileges;
    }
    
    /**
     * Create the privileges of the user in a course from an array of data
     * @param string $courseId course sys code
     * @param int $userId
     * @param array $data course user privileges data
     * @param Database_Connection $database
     * @return \self
     */
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

/**
 * Privileges of a user in a group of a course
 */
class Claro_GroupUserPrivileges
{
    protected 
        $userPrivileges, 
        $coursePrivileges, 
        $groupId,
        $_isGroupMember,
        $database;
    
    /**
     * Get the privileges of the given user in the given group of the given course
     * @param Claro_UserPrivileges $userPrivileges privileges of the user
     * @param Claro_CourseUserPrivileges $coursePrivileges privileges of the user in the course
     * @param Claro_GroupTeam $group group
     * @param Database_Connection $database database connection
     */
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
    
    /**
     * LOad the group membership information for the user
     */
    protected function loadGroupMembership()
    {
        if ( $this->userPrivileges->isAuthenticated() && $this->coursePrivileges->isCourseMember() )
        {
            $userGroupProperties = $this->group->getUserPropertiesInGroup( $this->userPrivileges->getUser() );

            $this->_isGroupMember = $userGroupProperties->isGroupMember;
        }
        else
        {
            $this->_isGroupMember = false;
        }
    }
    
    /**
     * Get the group id
     * @return int
     */
    public function getGroupId()
    {
        return $this->groupId;
    }
    
    /**
     * Is the user a member of this group ?
     * @return bool
     */
    public function isGroupMember()
    {
        if ( is_null( $this->_isGroupMember ) )
        {
            $this->loadGroupMembership();
        }
        
        return $this->_isGroupMember;
    }
    
    /**
     * Is the user a tutor for this group ?
     * @return bool
     */
    public function isGroupTutor()
    {
        return $this->userPrivileges->isAuthenticated() 
            && ($this->group->getTutor() && ($this->group->getTutor()->userId == $this->userPrivileges->getUserId()))
            ;
    }
    
    /**
     * Is the user allowed to acces the group ?
     * @return bool
     */
    public function isAllowedInGroup()
    {
        return !$this->groupProperties['private']
            || $this->isGroupMember()
            || $this->isSuperUser()
            ;
    }
    
    /**
     * IS the user a super user in the group ?
     * @return bool
     */
    public function isSuperUser()
    {
        return $this->coursePrivileges->isSuperUser() 
            /* || $this->isGroupTutor() */ // being a group tutor does not grant you super user rights in group context, this gives you group tutor rights
            || $this->coursePrivileges->getCourseUserProfile()->profileAllowsToEdit( new Claro_Module('CLGRP')  )
            ;
    }
}

/**
 * Profile of a user in a course and related allowed actions
 */
class Claro_CourseUserProfile
{
    protected 
        $userPrivileges,
        $coursePrivileges, 
        $profileRightList = null,
        $database;
    
    /**
     * Get the profile of a user in the given course
     * @param Claro_CourseUserPrivileges $coursePrivileges privileges of the user in the course
     * @param Database_Connection $database
     */
    public function __construct( $userPrivileges, $coursePrivileges, $database = null )
    {
        $this->database = $database ? $database : Claroline::getDatabase();
        
        $this->userPrivileges = $userPrivileges;
        $this->coursePrivileges = $coursePrivileges;
        
        $profile = new RightProfile();

        $profile->load( $this->coursePrivileges->getProfileId() );

        $courseProfileToolRight = new RightCourseProfileToolRight();
        $courseProfileToolRight->setCourseId( $this->coursePrivileges->getCourseId() );
        $courseProfileToolRight->load( $profile );

        $this->profileRightList = $courseProfileToolRight->getToolActionList();
        
    }
    
    /**
     * Get the id of the user's profile in the course
     * @return int
     */
    public function getProfileId()
    {
        return $this->coursePrivileges->getProfiledId();
    }
    
    /**
     * Does the user's profile allows the user to access resources in the given module in the course ?
     * @param Claro_Module $module
     * @return bool
     */
    public function profileAllowsToRead ( Claro_Module $module )
    {
        pushClaroMessage('check course profile allows read','debug');
        return $this->profileAllowsAction( $module, Claro_AccessManager::ACCESS_READ );
    }
    
    /**
     * Does the user's profile allows the user to modify resources in the given module in the course ?
     * @param Claro_Module $module
     * @return bool
     */
    public function profileAllowsToEdit ( Claro_Module $module )
    {
        pushClaroMessage('check course profile allows edit','debug');
        return $this->profileAllowsAction( $module, Claro_AccessManager::ACCESS_EDIT );
    }
    
    /**
     * Generic action check method
     * @param Claro_Module $module
     * @param string $action
     * @return bool
     */
    protected function profileAllowsAction ( Claro_Module $module, $action )
    {
        pushClaroMessage('check course profile allows action','debug');
        
        return isset($this->profileRightList[$module->getMainToolId()][$action]) && $this->profileRightList[$module->getMainToolId()][$action];
    }
}

/**
 * Iterator of the privileges of a user in all the courses this user is enrolled 
 * into. This class is not meant to be instanciated outside of Claro_CourseUserPrivilegesList
 */
class Claro_CourseUserPrivilegesIterator extends RowToObjectArrayIterator
{
    /**
     * Get the iterator of the privileges of a user in all the courses this user is enrolled into
     * @param int $userId user id
     * @param array $data array of raw user privileges in course (one course per row)
     */
    public function __construct( $userId, $data )
    {
        $this->userId = $userId;
        
        parent::__construct($data);
    }
    
    /**
     * @see RowToObjectArrayIterator
     * @return Claro_CourseUserPrivileges
     * @throws Exception on error
     */
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

/**
 * The list of privileges of a user in all the courses
 */
class Claro_CourseUserPrivilegesList
{
    protected $userId, $coursePrivilegesList;
    
    /**
     * Get the list of course privileges of the given user
     * @param int $userId
     * @param Database_connection $database
     */
    public function __construct( $userId = null, $database = null )
    {
        $this->database = $database ? $database : Claroline::getDatabase();
        $this->userId = $userId;
        $this->coursePrivilegesList = array();
    }
    
    /**
     * Load the list of course privileges of the user
     */
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
    
    /**
     * Get the privileges of the user in the given course
     * @param string $courseCode
     * @return Claro_CourseUserPrivileges
     */
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
    
    /**
     * Get the iterator of the privileges of the user in all the course this user is enrolled into
     * @return \Claro_CourseUserPrivilegesIterator
     */
    public function getIterator()
    {
        $it = new Claro_CourseUserPrivilegesIterator( $this->userId, $this->coursePrivilegesList );
        
        return $it;
    }
}

/**
 * This class is currently a wrapper for the procedural API
 */
class Claro_Module
{
    protected
        $moduleLabel, 
        $mainToolId,
        $moduleData,
        $moduleContexts;
    
    /**
     * Represents a module
     * @param string $moduleLabel
     */
    public function __construct( $moduleLabel )
    {
        $this->moduleLabel = $moduleLabel;
        $this->loadModuleData();
    }
    
    /**
     * Load the module data
     */
    protected function loadModuleData()
    {
        $this->mainToolId = get_tool_id_from_module_label( $this->moduleLabel );
        $this->moduleData = get_module_data( $this->moduleLabel );
        $this->moduleContexts = iterator_to_array( get_module_context_list( $this->moduleLabel ) );
    }
    
    /**
     * Get the label of the module
     * @return string
     */
    public function getLabel()
    {
        return $this->moduleLabel;
    }
    
    /**
     * Get module dataarray or value for the given dataName
     * @param string $dataName optionnal wanted variable name
     * @return mixed
     */
    public function getData( $dataName = null )
    {
        if ( $dataName )
        {
            if ( isset ( $this->moduleData[$dataName] ) )
            {
                return $this->moduleData[$dataName];
            }
            else
            {
                return null;
            }
        }
        else
        {
            return $this->moduleData;
        }
    }
    
    /**
     * Get the contexts in which the module can be executed
     * @return array of contexts (a context is represented by a string)
     */
    public function getContexts()
    {
        return $this->moduleContexts;
    }
    
    /**
     * Check if the module can be executed in the given context
     * @param string $contextName
     * @return bool
     */
    public function hasContext( $contextName )
    {
        return in_array ( $contextName, $this->moduleContexts );
    }
    
    /**
     * Get the tool id corresponding to the module in the main tool list 
     * (i.e. the id used at the platform level, not the one that appears in 
     * the course tool list)
     * @return int
     */
    public function getMainToolId()
    {
        return $this->mainToolId;
    }
    
    /**
     * Check if the module is activated at the platform level
     * @return bool
     */
    public function isActivated()
    {
        return $this->getData('activation') == 'activated';
    }
    
    /**
     * Get the type of the module (tool, applet, admin...)
     * @return string
     */
    public function getType()
    {
        return $this->getData('type');
    }
}
