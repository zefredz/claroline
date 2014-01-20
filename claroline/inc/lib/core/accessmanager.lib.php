<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * Access manager classes
 * 
 * @version     1.12 $Revision$
 * @copyright   (c) 2001-2014, Universite catholique de Louvain (UCL)
 * @author      Claroline Team <info@claroline.net>
 * @author      Frederic Minne <zefredz@claroline.net>
 * @license     http://www.gnu.org/copyleft/gpl.html
 *              GNU GENERAL PUBLIC LICENSE version 2 or later
 * @package     kernel.core.accessmanager
 */

require_once __DIR__ . '/privileges.lib.php';

/**
 * The main Access Manager class the only one you really need to know about.
 */
class Claro_AccessManager
{
    const 
        /**
         * Can view/read/access the module but without modifying ressources
         */
        ACCESS_READ = 'read',
        /**
         * Can modify resources in the module and see invisible resources
         */
        ACCESS_EDIT = 'edit';
    
    protected $database;
    
    public function __construct( $database = null )
    {
        $this->database = $database ? $database : Claroline::getDatabase();
    }
    
    /**
     * This is the method to check the access right 
     * @param string $moduleLabel the label of the module
     * @param string $action on of ACCESS_READ and ACCESS_EDIT
     * @param Claro_User $user user that wants to do the action
     * @param Claro_Course $course given course or null if not in a course (default)
     * @param Claro_GroupTeam $group given group or null if not in a group
     * @return boolean true if access granted, false if not 
     */
    protected function checkAccessRight( $moduleLabel, $action, $user = null, $course = null, $group = null )
    {
        $moduleAccess = new Claro_ModuleAccessManager ( new Claro_Module( $moduleLabel ), $this->database );

        $userPrivileges = new Claro_UserPrivileges ( $user );
        return $moduleAccess->checkAccessRight($userPrivileges, $action, $course, $group);
    }
    
    /**
     * Check editor/writer/manager access right 
     * @param string $moduleLabel the label of the module
     * @param Claro_User $user user that wants to do the action
     * @param Claro_Course $course given course or null if not in a course (default)
     * @param Claro_GroupTeam $group given group or null if not in a group
     * @return boolean true if access granted, false if not 
     */
    public function isAllowedToEdit ( $moduleLabel, $user = null, $course = null, $group = null )
    {
        return $this->checkAccessRight($moduleLabel, self::ACCESS_EDIT, $user, $course, $group);
    }
    
    /**
     * Check reader/viewer access right 
     * @param string $moduleLabel the label of the module
     * @param Claro_User $user id of the user that wants to do the action
     * @param Claro_Course $course given course or null if not in a course (default)
     * @param Claro_GroupTeam $group given group or null if not in a group
     * @return boolean true if access granted, false if not 
     */
    public function isAllowedToRead ( $moduleLabel, $user = null, $course = null, $group = null )
    {
        return $this->checkAccessRight($moduleLabel, self::ACCESS_READ, $user, $course, $group);
    }
}

/**
 * Access Manager for Modules this is where most of the access management logic lies
 * @todo add specific behaviour through an access manager connector
 */
class Claro_ModuleAccessManager
{
    protected 
        $module,
        $database;
    
    public function __construct( Claro_Module $module, $database = null )
    {
        $this->module = $module; 
        $this->database = $database ? $database : Claroline::getDatabase();
    }
    
    /**
     * Check if a given user is granted access right for a given action in a given context
     * @param Claro_USerPrivileges $userPrivileges
     * @param string $action one of the action defined in Claro_AccessManager
     * @param Claro_Course $course
     * @param Claro_GroupTeam $group
     * @return boolean
     */
    public function checkAccessRight( $userPrivileges, $action, $course = null, $group = null )
    {
        if ( $this->module->isActivated() )
        {
            if ( !$this->validateTypeAndContext( $userPrivileges, $course, $group ) )
            {
                claro_debug_mode() && pushClaroMessage('wrong context :- false','debug');
                return false;
            }
            else
            {
                // Über User
                if ( $userPrivileges->isSuperUser() )
                {
                    claro_debug_mode() && pushClaroMessage('isSuperUser :- true','debug');
                    return true;
                }
                else
                {
                    if ( $course )
                    {
                        $coursePrivileges = $userPrivileges->getCoursePrivileges( $course );
                        
                        $courseTool = new Claro_CourseTool( $this->module->getLabel(), $coursePrivileges->getCourseId(), $this->database );
                        
                        // Super User
                        if ( $coursePrivileges->isSuperUser() )
                        {
                            claro_debug_mode() && pushClaroMessage('isSuperUser in course :- true','debug');
                            return true;
                        }
                        elseif ( $coursePrivileges->isCourseAllowed() && $courseTool->isActivated () && $courseTool->isVisible() )
                        {
                            if ( $group )
                            {
                                $groupPrivileges = $coursePrivileges->getGroupPrivileges( $group );
                                
                                if ( ! $groupPrivileges->isAllowedInGroup() )
                                {
                                    claro_debug_mode() && pushClaroMessage('group not allowed :- false','debug');
                                    return false;
                                }
                                else
                                {
                                    if ( $action == Claro_AccessManager::ACCESS_READ )
                                    {
                                        claro_debug_mode() && pushClaroMessage('check group read','debug');
                                        return $this->isAllowedToReadInGroup($userPrivileges, $coursePrivileges, $groupPrivileges);
                                    }
                                    else
                                    {
                                        claro_debug_mode() && pushClaroMessage('check group edit','debug');
                                        return $this->isAllowedToEditInGroup($userPrivileges, $coursePrivileges, $groupPrivileges);
                                    }
                                }
                            }
                            else
                            {
                                if ( $action == Claro_AccessManager::ACCESS_READ )
                                {
                                    claro_debug_mode() && pushClaroMessage('check course read','debug');
                                    return $this->isAllowedToReadInCourse($userPrivileges, $coursePrivileges);
                                }
                                else
                                {
                                    claro_debug_mode() && pushClaroMessage('check course edit','debug');
                                    return $this->isAllowedToEditInCourse($userPrivileges, $coursePrivileges);
                                }
                            }
                        }
                        else
                        {
                            claro_debug_mode() && pushClaroMessage('not course allowed :- false','debug');
                            return false;
                        }
                    }
                    else
                    {
                        if ( $action == Claro_AccessManager::ACCESS_READ )
                        {
                            claro_debug_mode() && pushClaroMessage('check module read','debug');
                            return $this->isAllowedToRead($userPrivileges);
                        }
                        else
                        {
                            claro_debug_mode() && pushClaroMessage('check module edit','debug');
                            return $this->isAllowedToEdit($userPrivileges);
                        }
                    }
                }   
            }
        }
        else
        {
            return false;
        }
    }

    protected function validateTypeAndContext( $userPrivileges, $course = null, $group = null )
    {
        if ( !$this->module->isActivated() )
        {
            return false;
        }
        else
        {
            if ( $this->module->getType() == 'admin' )
            {
                return $userPrivileges->isPlatformAdmin(); 
            }
            elseif ( $this->module->getType() == 'crsmanage' )
            {
                if ( $course )
                {
                    return $userPrivileges->getCoursePrivileges($course)->isSuperUser();
                }
                else
                {
                    return false;
                }
            }
            elseif ( $this->module->getType() == 'tool' )
            {
                if ( $course )
                {
                    if ( $group )
                    {
                        return $this->module->hasContext('group');
                    }
                    else
                    {
                        return $this->module->hasContext('course');
                    }
                }
                else
                {
                    return $this->module->hasContext('platform');
                }
            }
            else
            {
                return true;
            }
        }
    }
    
    // platform context 
    
    protected function isAllowedToRead( $userPrivileges )
    {
        if ( $this->module->getType() == 'tool' )
        {
            return $this->module->hasContext('platform');
        }
        else // applets have to manage their access rights by themselves
        {
            return true;
        }
    }
    
    protected function isAllowedToEdit( $userPrivileges )
    {
        return $this->module->hasContext('platform')
            && $userPrivileges->isAuthenticated();
    }
    
    // course context
    
    protected function isAllowedToReadInCourse( $userPrivileges, $coursePrivileges )
    {
        return $coursePrivileges->isCourseManager()
            || $coursePrivileges->getCourseUserProfile()->profileAllowsToRead($this->module);
    }
    
    protected function isAllowedToEditInCourse( $userPrivileges, $coursePrivileges )
    {
        if ( $coursePrivileges->isCourseManager() )
        {
            claro_debug_mode() && pushClaroMessage('course manager :- true','debug');
            return true;
        }
        else
        {
            claro_debug_mode() && pushClaroMessage('test course profile edit','debug');
            return $coursePrivileges->getCourseUserProfile()->profileAllowsToEdit($this->module);
        }
    }
    
    // group context
    
    protected function isAllowedToReadInGroup( $userPrivileges, $coursePrivileges, $groupPrivileges )
    {   
        if ( $this->canAccessModuleInGroup( $userPrivileges, $coursePrivileges, $groupPrivileges ) )
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    
    protected function isAllowedToEditInGroup( $userPrivileges, $coursePrivileges, $groupPrivileges )
    {
        if ( $this->canAccessModuleInGroup( $userPrivileges, $coursePrivileges, $groupPrivileges ) )
        {
            return $groupPrivileges->isGroupMember() || $groupPrivileges->isGroupTutor();
        }
        else
        {
            return false;
        }
    }
    
    protected function canAccessModuleInGroup( $userPrivileges, $coursePrivileges, $groupPrivileges )
    {        
        $courseUserProfile = new Claro_CourseUserProfile( $userPrivileges, $coursePrivileges );
        
        $clgrp = new Claro_CourseTool( 'CLGRP', $coursePrivileges->getCourseId(), $this->database );
        
        $is_allowedToAccessCLGRP = $clgrp->isActivated() 
            && $clgrp->isVisible()
            && $courseUserProfile->profileAllowsToRead('CLGRP');
        
        if ( $is_allowedToAccessCLGRP )
        {
            $_groupProperties = $coursePrivileges->getCourse()->getGroupProperties();

            return array_key_exists( $this->module->getLabel(), $_groupProperties ['tools'])
                && $_groupProperties ['tools'] [ $this->module->getLabel() ];
        }
        else
        {
            return false;
        }
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

class Claro_ModuleAccessPermissions
{
    private $permissions;
    
    protected function __construct( $permissions )
    {
        $this->permissions = $permissions;
    }
    
    /**
     * Check if an invisible resource can be accessed for this module
     * @return boolean
     */
    public function isResourceAccessAllowedWhileInsivible()
    {
        if ( ! isset( $this->permissions['resourceAccessAllowedWhileInsivible'] ) )
        {
            return false;
        }
        
        return $this->permissions['resourceAccessAllowedWhileInsivible'] === true ? true : false;
    }
    
    /**
     * Check if the module is available when embedded
     * @return boolean
     */
    public function isAvailableWhenEmbedded()
    {
        if ( ! isset( $this->permissions['availableWhenEmbedded'] ) )
        {
            return false;
        }
        
        return $this->permissions['availableWhenEmbedded'] === true ? true : false;
    }
    
    /**
     * Load and get the permission for the requested module
     * @param string $moduleLabel label of the requested module
     * @return Claro_ModuleAccessPermissions
     */
    public static function loadPermissions( $moduleLabel )
    {
        $permissions = array();
        
        $moduleAccessPermissionsPath = get_module_path( $moduleLabel ) . '/connector/permissions.ini';
        
        if ( file_exists( $moduleAccessPermissionsPath ) )
        {
            $permissions = parse_ini_file( $moduleAccessPermissionsPath );
        }
        
        $mAP = new self( $permissions );
        
        return $mAP;
    }
}

/**
 * Represent a tool in a course
 */
class Claro_CourseTool
{
    protected $moduleLabel, $mainToolId, $courseId, $database;
    
    // cache
    protected $_activated = null;
    protected $_visible = null;
    
    /**
     * 
     * @param string $moduleLabel
     * @param string $courseId
     * @param Database_Connection $database (optional)
     */
    public function __construct ( $moduleLabel, $courseId, $database = null )
    {
        $this->moduleLabel = $moduleLabel;
        $this->mainToolId = get_tool_id_from_module_label($moduleLabel);
        $this->courseId = $courseId;
        $this->database = $database ? $database : Claroline::getDatabase();
    }
    
    /**
     * Check if the tool is activated in the course
     * @return bool
     */
    public function isActivated()
    {
        if ( is_null( $this->_activated ) )
        {
            $tbl_cdb_names = claro_sql_get_course_tbl();

            if ( $this->database->query( "
                SELECT 
                    ctl.activated AS activated
                FROM 
                    `" . $tbl_cdb_names['tool'] . "` as ctl 
                WHERE 
                    ctl.tool_id = " . (int)$this->mainToolId . "
                AND 
                    ctl.activated = 'true' " )->numRows() )
            {
                $this->_activated = true;
            }
            else
            {
                $this->_activated = false;
            }
        }
        
        return $this->_activated;
    }
    
    /**
     * Check if the tool is visible in the course
     * @return bool
     */
    public function isVisible()
    {
        if ( is_null( $this->_visible ) )
        {
            $tbl_cdb_names = claro_sql_get_course_tbl();

            if ( $this->database->query( "
                SELECT 
                    ctl.activated AS activated
                FROM 
                    `" . $tbl_cdb_names['tool'] . "` as ctl 
                WHERE 
                    ctl.tool_id = " . (int)$this->mainToolId . "
                AND 
                    ctl.visibility = 1 " )->numRows() )
            {
                $this->_visible = true;
            }
            else
            {
                $this->_visible = false;
            }
        }
        
        return $this->_visible;
    }
}
