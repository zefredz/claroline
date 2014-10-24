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
        ACCESS_EDIT = 'edit',
    
        /**
         * Can manage the module
         */
        ACCESS_MANAGE = 'manage';
    
    const 
        /**
         * Can view/read/access the module but without modifying ressources
         */
        CONTEXT_PLATFORM = 'platform',
        /**
         * Can modify resources in the module and see invisible resources
         */
        CONTEXT_COURSE = 'course',
    
        /**
         * Can manage the module
         */
        CONTEXT_GROUP = 'group';
    
    protected $database;
    
    /**
     * Constructor
     * @param Database_Connection $database
     */
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
     * Check editor/writer access right 
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
    
    /**
     * Check manager access right 
     * @param string $moduleLabel the label of the module
     * @param Claro_User $user id of the user that wants to do the action
     * @param Claro_Course $course given course or null if not in a course (default)
     * @param Claro_GroupTeam $group given group or null if not in a group
     * @return boolean true if access granted, false if not 
     */
    public function isAllowedToManage ( $moduleLabel, $user = null, $course = null, $group = null )
    {
        return $this->checkAccessRight($moduleLabel, self::ACCESS_MANAGE, $user, $course, $group);
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
    
    /**
     * Constructor
     * @param Claro_Module $module
     * @param Database_Connection $database
     */
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
     * @throws Exception if invalid action supplied
     */
    public function checkAccessRight( $userPrivileges, $action, $course = null, $group = null )
    {
        if ( $action !== Claro_AccessManager::ACCESS_READ 
            && $action !== Claro_AccessManager::ACCESS_EDIT 
            && $action !== Claro_AccessManager::ACCESS_MANAGE )
        {
            throw new Exception("Invalid action supplied : {$action}");
        }
        
        if ( $this->module->isActivated() )
        {
            if ( !$this->validateTypeAndContext( $userPrivileges, $course, $group ) )
            {
                claro_debug_mode() && pushClaroMessage('wrong context :- false','debug');
                return false;
            }
            else // type and context validation ok
            {
                // Über User
                if ( $userPrivileges->isSuperUser() )
                {
                    claro_debug_mode() && pushClaroMessage('isSuperUser :- true','debug');
                    return true;
                }
                else // not platform super user
                {
                    $modulePermissions = Claro_ModuleAccessPermissions::loadPermissions($this->module->getLabel ());
                    
                    if ( $course )
                    {
                        $coursePrivileges = $userPrivileges->getCoursePrivileges( $course );
                        
                        $courseTool = new Claro_CourseTool( $this->module->getLabel(), $coursePrivileges->getCourseId(), $this->database );
                        
                        if ( $coursePrivileges->isSuperUser() )
                        {
                            claro_debug_mode() && pushClaroMessage('isSuperUser in course :- true','debug');
                            return true;
                        } // end course super user
                        elseif ( $coursePrivileges->isCourseAllowed() 
                            && $courseTool->isActivated () 
                            && ( get_conf( 'use_visibility_as_access', true ) && $courseTool->isVisible() ) ) // allowed in course
                        {
                            if ( $group )
                            {
                                $groupPrivileges = $coursePrivileges->getGroupPrivileges ( $group );

                                return $this->checkGroupAccessRight ( $action, $modulePermissions, $userPrivileges, $coursePrivileges, $groupPrivileges );
                            } // end in group
                            else // not in group
                            {
                                return $this->checkCourseAccessRight($action, $modulePermissions, $userPrivileges, $coursePrivileges);
                            } // end not in group
                        } // end course allowed
                        else
                        {
                            claro_debug_mode() && pushClaroMessage('not course allowed :- false','debug');
                            return false;
                        } // end course not allowed
                    } // end in course
                    else // not in course
                    {
                        return $this->checkPlatformAccessRight($action, $modulePermissions, $userPrivileges);
                    } // end not in course
                } // end not platform super user   
            } // end type and context validation ok
        } // end module activated
        else // module not activated
        {
            return false;
        } // end module not activated
    }
    
    protected function checkPlatformAccessRight( $action, $modulePermissions, $userPrivileges )
    {
        if ( !is_null ( $modulePermissions ) && $modulePermissions->overridePermission ( $action, Claro_AccessManager::CONTEXT_PLATFORM ) )
        {
            return $modulePermissions->moduleAccessOverride ( $action, Claro_AccessManager::CONTEXT_PLATFORM, $userPrivileges );
        } // end module overrides course permissions
        else // module does not override platform permissions
        {
            switch ( $action )
            {
                case Claro_AccessManager::ACCESS_READ:
                    {
                        claro_debug_mode () && pushClaroMessage ( 'check platform read', 'debug' );

                        if ( $this->isAllowedToRead ( $userPrivileges ) )
                        {
                            return true;
                        }
                    } // no break cascading access check
                case Claro_AccessManager::ACCESS_EDIT:
                    {
                        claro_debug_mode () && pushClaroMessage ( 'check platform edit', 'debug' );

                        if ( $this->isAllowedToEdit ( $userPrivileges ) )
                        {
                            return true;
                        }
                    } // no break cascading access check
                case Claro_AccessManager::ACCESS_MANAGE:
                    {
                        claro_debug_mode () && pushClaroMessage ( 'check platform manage', 'debug' );

                        if ( $this->isAllowedToManage ( $userPrivileges ) )
                        {
                            return true;
                        }
                    } // no break cascading access check
                default:
                    {
                        return false;
                    }
            } // end switch action
        } // end module does not override course permissions
    }
    
    protected function checkCourseAccessRight( $action, $modulePermissions, $userPrivileges, $coursePrivileges )
    {
        if ( !is_null ( $modulePermissions ) && $modulePermissions->overridePermission ( $action, Claro_AccessManager::CONTEXT_COURSE ) )
        {
            return $modulePermissions->moduleAccessOverride ( $action, Claro_AccessManager::CONTEXT_COURSE, $userPrivileges, $coursePrivileges );
        } // end module overrides course permissions
        else // module does not override course permissions
        {
            switch ( $action )
            {
                case Claro_AccessManager::ACCESS_READ:
                    {
                        claro_debug_mode () && pushClaroMessage ( 'check course read', 'debug' );

                        if ( $this->isAllowedToReadInCourse ( $userPrivileges, $coursePrivileges ) )
                        {
                            return true;
                        }
                    } // no break cascading access check
                case Claro_AccessManager::ACCESS_EDIT:
                    {
                        claro_debug_mode () && pushClaroMessage ( 'check course edit', 'debug' );

                        if ( $this->isAllowedToEditInCourse ( $userPrivileges, $coursePrivileges ) )
                        {
                            return true;
                        }
                    } // no break cascading access check
                case Claro_AccessManager::ACCESS_MANAGE:
                    {
                        claro_debug_mode () && pushClaroMessage ( 'check course manage', 'debug' );

                        if ( $this->isAllowedToManageInCourse ( $userPrivileges, $coursePrivileges ) )
                        {
                            return true;
                        }
                    } // no break cascading access check
                default:
                    {
                        return false;
                    }
            } // end switch action
        } // end module does not override course permissions
    }
    
    protected function checkGroupAccessRight( $action, $modulePermissions, $userPrivileges, $coursePrivileges, $groupPrivileges )
    {
        if ( !$groupPrivileges->isAllowedInGroup () )
        {
            claro_debug_mode () && pushClaroMessage ( 'group not allowed :- false', 'debug' );
            return false;
        } // end not group allowed
        else // allowed in group
        {
            if ( !is_null ( $modulePermissions ) && $modulePermissions->overridePermission ( $action, Claro_AccessManager::CONTEXT_GROUP ) )
            {
                return $modulePermissions->moduleAccessOverride ( $action, Claro_AccessManager::CONTEXT_GROUP, $userPrivileges, $coursePrivileges, $groupPrivileges );
            } // end module overrides group permissions
            else // module does not override group permissions
            {
                switch ( $action )
                {
                    case Claro_AccessManager::ACCESS_READ:
                        {
                            claro_debug_mode () && pushClaroMessage ( 'check group read', 'debug' );

                            if ( $this->isAllowedToReadInGroup ( $userPrivileges, $coursePrivileges, $groupPrivileges ) )
                            {
                                return true;
                            }
                        } // no break cascading access check
                    case Claro_AccessManager::ACCESS_EDIT:
                        {
                            claro_debug_mode () && pushClaroMessage ( 'check group edit', 'debug' );

                            if ( $this->isAllowedToEditInGroup ( $userPrivileges, $coursePrivileges, $groupPrivileges ) )
                            {
                                return true;
                            }
                        } // no break cascading access check
                    case Claro_AccessManager::ACCESS_MANAGE:
                        {
                            claro_debug_mode () && pushClaroMessage ( 'check group manage', 'debug' );

                            if ( $this->isAllowedToManageInGroup ( $userPrivileges, $coursePrivileges, $groupPrivileges ) )
                            {
                                return true;
                            }
                        } // no break cascading access check
                    default:
                        {
                            return false;
                        }
                } // end switch group action
            } // module does not override group permissions
        } // end group allowed
    }
    
    /**
     * Validate the type and context of a module with the privileges of the user
     * @param Claro_UserPrivileges $userPrivileges
     * @param Claro_Course $course
     * @param Claro_GroupTeam $group
     * @return boolean true if valid, false if not
     */
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
    
    /**
     * Check if a user is allowed to read resources at the platform level
     * @param Claro_UserPrivileges $userPrivileges
     * @return boolean
     */
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
    
    /**
     * Check if a user is allowed to modify a resource at the platform level
     * @param Claro_UserPrivileges $userPrivileges
     * @return bool
     */
    protected function isAllowedToEdit( $userPrivileges )
    {
        return $this->module->hasContext('platform')
            && $userPrivileges->isAuthenticated();
    }
    
    /**
     * Check if a user is allowed to manage a tool at the platform level
     * @param Claro_UserPrivileges $userPrivileges
     * @return bool
     */
    protected function isAllowedToManage( $userPrivileges )
    {
        return $userPrivileges->isSuperUser();
    }
    
    // course context
    
    /**
     * Check if a user is allowed to read a resources from the user's course privileges
     * @param Claro_UserPrivileges $userPrivileges
     * @param Claro_CourseUSerPrivileges $coursePrivileges
     * @return bool
     */
    protected function isAllowedToReadInCourse( $userPrivileges, $coursePrivileges )
    {
        return $coursePrivileges->isCourseManager()
            || $coursePrivileges->getCourseUserProfile()->profileAllowsToRead($this->module);
    }
    
    /**
     * Check if a user is allowed to modify a resources from the user's course privileges
     * @param Claro_UserPrivileges $userPrivileges
     * @param Claro_CourseUSerPrivileges $coursePrivileges
     * @return bool
     */
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
    
    /**
     * Check if a user is allowed to modify a resources from the user's course privileges
     * @param Claro_UserPrivileges $userPrivileges
     * @param Claro_CourseUSerPrivileges $coursePrivileges
     * @return bool
     */
    protected function isAllowedToManageInCourse( $userPrivileges, $coursePrivileges )
    {
        return $coursePrivileges->isSuperUser();
    }
    
    // group context
    
    /**
     * Check if a user is allowed to access a resources from the user's group privileges
     * @param Claro_UserPrivileges $userPrivileges
     * @param Claro_CourseUserPrivileges $coursePrivileges
     * @param Claro_GroupUserPrivileges $groupPrivileges
     * @return boolean
     */
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
    
    /**
     * Check if a user is allowed to modify a resources from the user's group privileges
     * @param Claro_UserPrivileges $userPrivileges
     * @param Claro_CourseUserPrivileges $coursePrivileges
     * @param Claro_GroupUserPrivileges $groupPrivileges
     * @return boolean
     */
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
    
    /**
     * Check if a user is allowed to manage a tool from the user's group privileges
     * @param Claro_UserPrivileges $userPrivileges
     * @param Claro_CourseUserPrivileges $coursePrivileges
     * @param Claro_GroupUserPrivileges $groupPrivileges
     * @return boolean
     */
    protected function isAllowedToManageInGroup( $userPrivileges, $coursePrivileges, $groupPrivileges )
    {
        if ( $this->canAccessModuleInGroup( $userPrivileges, $coursePrivileges, $groupPrivileges ) )
        {
            return $groupPrivileges->isSuperUser ();
        }
        else
        {
            return false;
        }
    }
    
    /**
     * Check if a user is allowed to access a module from the user's group privileges
     * @param Claro_UserPrivileges $userPrivileges
     * @param Claro_CourseUserPrivileges $coursePrivileges
     * @param Claro_GroupUserPrivileges $groupPrivileges
     * @return boolean
     */
    protected function canAccessModuleInGroup( $userPrivileges, $coursePrivileges, $groupPrivileges )
    {        
        $courseUserProfile = new Claro_CourseUserProfile( $userPrivileges, $coursePrivileges );
        
        $clgrp = new Claro_CourseTool( 'CLGRP', $coursePrivileges->getCourseId(), $this->database );
        
        $is_allowedToAccessCLGRP = $clgrp->isActivated() 
            && $clgrp->isVisible()
            && $courseUserProfile->profileAllowsToRead(new Claro_Module('CLGRP'));
        
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
 * Access permissions of a module
 */
class Claro_ModuleAccessPermissions
{
    private $permissions;
    
    /**
     * 
     * @param array $permissions
     */
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
    
    public function overridePermission( $action, $context )
    {
        if ( ! isset( $this->permissions['override'] ) )
        {
            return false;
        }
        
        if ( ! isset($this->permissions['override'][$context]) 
            && ! isset($this->permissions['override'][$context][$action]) )
        {
            return false;
        }
        
        return true;
    }
    
    protected function getPermissionOverrideCallback( $context, $action )
    {
        $callback = $this->permissions['override'][$context][$action];
        
        if ( ! is_callable( $callback ) )
        {
            throw new Exception("Invalid permission override callback given for context {$context} and action {$action}");
        }
        
        return $callback;
    }
    
    public function moduleAccessOverride( $action, $context, $userPrivileges, $coursePrivileges = null, $groupPrivileges = null )
    {
        if ( $this->overridePermission ( $action, $context ) )
        {
            return null;
        }
        
        $callback = $this->getPermissionOverrideCallback( $context, $action );
        
        switch ( $context )
        {
            case Claro_AccessManager::CONTEXT_PLATFORM:
            {
                return call_user_func( $callback,$userPrivileges );
            } break;
            case Claro_AccessManager::CONTEXT_COURSE:
            {
                return call_user_func( $callback,$userPrivileges, $coursePrivileges );
            } break;
            case Claro_AccessManager::CONTEXT_GROUP:
            {
                return call_user_func( $callback,$userPrivileges, $coursePrivileges, $groupPrivileges );
            } break;
            default:
            {
                throw new Exception("Invalid context supplied : {$context}");
            }
        }
    }
    
    /**
     * Load and get the permission for the requested module
     * @param string $moduleLabel label of the requested module
     * @return Claro_ModuleAccessPermissions|null
     */
    public static function loadPermissions( $moduleLabel )
    {
        $permissions = array();
        
        $moduleAccessPermissionsPath = get_module_path( $moduleLabel ) . '/connector/permissions.cnr.php';
        
        if ( file_exists( $moduleAccessPermissionsPath ) )
        {
            $permissions = include( $moduleAccessPermissionsPath );
            
            $mAP = new self( $permissions );
        
            return $mAP;
        }
        else
        {
            return null;
        }        
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
