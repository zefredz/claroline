<?php // $Id$

if ( count( get_included_files() ) == 1 )
{
    die( 'The file ' . basename(__FILE__) . ' cannot be accessed directly, use include instead' );
}

//----------------------------------------------------------------------
// CLAROLINE
//----------------------------------------------------------------------
// Copyright (c) 2001-2012 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see 'credits' file
//----------------------------------------------------------------------

/**
 * This is the kernel initialization script for Claroline
 *
 * @version     Claroline 1.12 $Revision$
 * @copyright   (c) 2001-2014, Universite catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @package     kernel
 * @author      Claro Team <cvs@claroline.net>
 */

/*******************************************************************************
 *
 *                             SCRIPT PURPOSE
 *
 * This script initializes and manages main Claroline session informations. It
 * keeps available session informations always up to date.
 *
 * You can request a course id. It will check if the course Id requested is the
 * same as the current one. If it isn't it will update session information from
 * the database. You can also force the course reset if you want ($GLOBALS['cidReset']).
 *
 * All the course informations are store in the $GLOBALS['_course'] array.
 *
 * You can request a group id. It will check if the group Id requested is the
 * same as the current one. If it isn't it will update session information from
 * the database. You can also force the course reset if you want ($GLOBALS['gidReset']).
 *
 * All the current group information are stored in the $GLOBALS['_group'] array
 *
 * The course id is stored in $GLOBALS['_cid'] session variable.
 * The group  id is stored in $GLOBALS['_gid'] session variable.
 *
 *
 *                    VARIABLES AFFECTING THE SCRIPT BEHAVIOR
 *
 * string  $login
 * string  $password
 * boolean $GLOBALS['logout']
 *
 * string  $cidReq   : course Id requested
 * boolean $GLOBALS['cidReset'] : ask for a course Reset, if no $cidReq is provided in the
 *                     same time, all course informations is removed from the
 *                     current session
 *
 * int     $GLOBALS['gidReq']   : group Id requested
 * boolean $GLOBALS['gidReset'] : ask for a group Reset, if no $GLOBALS['gidReq'] is provided in the
 *                     same time, all group informations is removed from the
 *                     current session
 *
 * int     $GLOBALS['tidReq']   : tool Id requested
 * boolean $tidReset : ask for a tool reset, if no $GLOBALS['tidReq'] or $GLOBALS['tlabelReq'] is
 *                     provided  in the same time, all information concerning
 *                     the current tool is removed from the current sesssion
 *
 * $GLOBALS['tlabelReq']        : more generic call to a tool. Each tool are identified by
 *                     a unique id into the course. But tools which are part of
 *                     the claroline release have also an generic label.
 *                     Tool label and tool id are decoupled. It means that one
 *                     can have several token of the same tool with different
 *                     settings in the same course.
 *
 *                   VARIABLES SET AND RETURNED BY THE SCRIPT
 *
 * Here is resumed below all the variables set and returned by this script.
 *
 * USER VARIABLES
 *
 * int $GLOBALS['_uid'] (the user id)
 *
 * string  $GLOBALS['_user'] ['firstName']
 * string  $GLOBALS['_user'] ['lastName' ]
 * string  $GLOBALS['_user'] ['mail'     ]
 * string  $GLOBALS['_user'] ['officialEmail'     ]
 * string  $GLOBALS['_user'] ['lastLogin']
 *
 * boolean $GLOBALS['is_platformAdmin']
 * boolean $GLOBALS['is_allowedCreateCourse']
 *
 * COURSE VARIABLES
 *
 * string  $GLOBALS['_cid'] (the course id)
 *
 * string  $GLOBALS['_course']['name'        ]
 * string  $GLOBALS['_course']['officialCode']
 * string  $GLOBALS['_course']['sysCode'     ]
 * string  $GLOBALS['_course']['path'        ]
 * string  $GLOBALS['_course']['dbName'      ]
 * string  $GLOBALS['_course']['dbNameGlu'   ]
 * string  $GLOBALS['_course']['titular'     ]
 * string  $GLOBALS['_course']['language'    ]
 * string  $GLOBALS['_course']['extLinkUrl'  ]
 * string  $GLOBALS['_course']['extLinkName' ]
 * string  $GLOBALS['_course']['categoryCode']
 * string  $GLOBALS['_course']['categoryName']
 *
 * PROPERTIES IN ALL GROUPS OF THE COURSE
 *
 * boolean $GLOBALS['_groupProperties'] ['registrationAllowed']
 * boolean $GLOBALS['_groupProperties'] ['private'            ]
 * int     $GLOBALS['_groupProperties'] ['nbGroupPerUser'     ]
 * boolean $GLOBALS['_groupProperties'] ['tools'] ['CLFRM']
 * boolean $GLOBALS['_groupProperties'] ['tools'] ['CLDOC']
 * boolean $GLOBALS['_groupProperties'] ['tools'] ['CLWIKI']
 * boolean $GLOBALS['_groupProperties'] ['tools'] ['CLCHT']
 *
 * REL COURSE USER VARIABLES
 * int     $GLOBALS['_profileId']
 * string  $GLOBALS['_courseUser']['role']
 * boolean $GLOBALS['is_courseMember']
 * boolean $GLOBALS['is_courseTutor']
 * boolean $GLOBALS['is_courseAdmin']
 *
 * REL COURSE GROUP VARIABLES
 *
 * int     $GLOBALS['_gid'] (the group id)
 *
 * string  $GLOBALS['_group'] ['name'       ]
 * string  $GLOBALS['_group'] ['description']
 * int     $GLOBALS['_group'] ['tutorId'    ]
 * string  $GLOBALS['_group'] ['directory'  ]
 * int     $GLOBALS['_group'] ['maxMember'  ]
 *
 * boolean $GLOBALS['is_groupMember']
 * boolean $GLOBALS['is_groupTutor']
 * boolean $GLOBALS['is_groupAllowed']
 *
 * TOOL VARIABLES
 *
 * int $GLOBALS['_tid']
 *
 * string $GLOBALS['_courseTool']['label'         ]
 * string $GLOBALS['_courseTool']['name'          ]
 * string $GLOBALS['_courseTool']['visibility'    ]
 * string $GLOBALS['_courseTool']['url'           ]
 * string $GLOBALS['_courseTool']['icon'          ]
 * string $GLOBALS['_courseTool']['access_manager']
 *
 * REL USER TOOL COURSE VARIABLES
 * boolean $GLOBALS['is_toolAllowed']
 *
 * LIST OF THE TOOLS AVAILABLE FOR THE CURRENT USER
 *
 * int     $GLOBALS['_courseToolList'][]['id'            ]
 * string  $GLOBALS['_courseToolList'][]['label'         ]
 * string  $GLOBALS['_courseToolList'][]['name'          ]
 * string  $GLOBALS['_courseToolList'][]['visibility'    ]
 * string  $GLOBALS['_courseToolList'][]['icon'          ]
 * string  $GLOBALS['_courseToolList'][]['access_manager']
 * string  $GLOBALS['_courseToolList'][]['url'           ]
 *
 *
 *                       IMPORTANT ADVICE FOR DEVELOPERS
 *
 * We strongly encourage developers to use a connection layer at the top of
 * their scripts rather than use these variables, as they are, inside the core
 * of their scripts. It will make Claroline code maintenance much easier.
 *
 * For example, a common practice is to connect the user status with action
 * permission flag at the top of the script like this :
 *
 *     $is_allowedToEdit = $GLOBALS['is_courseAdmin']
 *
 *
 *                               SCRIPT STRUCTURE
 *
 * 1. The script determines if there is an authentication attempt. This part
 * only chek if the login name and password are valid. Afterwards, it set the
 * $GLOBALS['_uid'] (user id) and the $GLOBALS['uidReset'] flag. Other user informations are retrieved
 * later. It's also in this section that optional external authentication
 * devices step in.
 *
 * 2. The script determines what other session informations have to be set or
 * reset, setting correctly $GLOBALS['cidReset'] (for course) and $GLOBALS['gidReset'] (for group).
 *
 * 3. If needed, the script retrieves the other user informations (first name,
 * last name, ...) and stores them in session.
 *
 * 4. If needed, the script retrieves the course information and stores them
 * in session
 *
 * 5. The script initializes the user status and permission for current course
 *
 * 6. If needed, the script retrieves group informations an store them in
 * session.
 *
 * 7. The script initializes the user status and permission for the current group.
 *
 * 8. The script initializes the user status and permission for the current tool
 *
 * 9. The script get the list of all the tool available into the current course
 *    for the current user.
 ******************************************************************************/

require_once __DIR__ . '/lib/auth/authmanager.lib.php';
require_once __DIR__ . '/lib/kernel/user.lib.php';
require_once __DIR__ . '/lib/kernel/course.lib.php';
require_once __DIR__ . '/lib/kernel/groupteam.lib.php';
require_once __DIR__ . '/lib/user.lib.php';
require_once __DIR__ . '/lib/core/claroline.lib.php';
require_once __DIR__ . '/lib/core/privileges.lib.php';
require_once __DIR__ . '/lib/core/accessmanager.lib.php';

// Load authentication config files
require_once claro_get_conf_repository() .  'auth.sso.conf.php';
require_once claro_get_conf_repository() .  'auth.extra.conf.php';

/*===========================================================================
  Set claro_init_local.inc.php variables coming from HTTP request into the
  global name space.
 ===========================================================================*/

$AllowedPhpRequestList = array('logout', 'uidReset',
                               'cidReset', 'cidReq',
                               'gidReset', 'gidReq',
                               'tidReset', 'tidReq', 'tlabelReq');

if ( isset($GLOBALS['contextReset']) && $GLOBALS['contextReset'] === true )
{
    $GLOBALS['cidReset'] = true;
    $GLOBALS['gidReset'] = true;
    $GLOBALS['tidReset'] = true;
}

// Cleaning up $GLOBALS to avoid issues with register_globals
foreach($AllowedPhpRequestList as $thisPhpRequestName)
{
    // some claroline scripts set these variables before calling
    // the claro init process. Avoid variable setting if it is the case.

    if ( isset($GLOBALS[$thisPhpRequestName]) )
    {
        continue;
    }

    if ( isset($_REQUEST[$thisPhpRequestName] ) )
    {
        $GLOBALS[$thisPhpRequestName] = $_REQUEST[$thisPhpRequestName];
    }
    else
    {
        $GLOBALS[$thisPhpRequestName] = null;
    }
}

/*
if ( !isset( $GLOBALS['cidReset'] ) || is_null ( $GLOBALS['cidReset'] ) )
{
    if ( isset( $GLOBALS['cidReq'] )
        && isset( $_SESSION['_cid'] )
        && $GLOBALS['cidReq'] != $_SESSION['_cid'] )
    {
        $GLOBALS['cidReset'] = true;
    }
    elseif ( isset( $GLOBALS['cidReq'] )
        && !isset( $_SESSION['_cid'] ) )
    {
        $GLOBALS['cidReset'] = true;
    }
    elseif ( !isset( $GLOBALS['cidReq'] )
        && isset( $_SESSION['_cid'] ) )
    {
        $GLOBALS['cidReset'] = true;
    }
}

if ( ! isset($GLOBALS['gidReset']) || is_null( $GLOBALS['gidReset'] ) )
{
    if ( isset( $GLOBALS['gidReq'] )
        && isset( $_SESSION['_gid'] )
        && $GLOBALS['gidReset'] != $_SESSION['_gid'] )
    {
        $GLOBALS['gidReset'] = true;
    }
    elseif ( isset( $GLOBALS['gidReq'] )
        && ! isset( $_SESSION['_gid'] ) )
    {
        $GLOBALS['gidReset'] = true;
    }
    elseif ( ! isset( $GLOBALS['gidReq'] )
        && isset( $_SESSION['_gid'] ) )
    {
        $GLOBALS['gidReset'] = true;
    }
}*/

$login    = isset($_REQUEST['login'   ]) ? trim( $_REQUEST['login'   ] ) : null;
$password = isset($_REQUEST['password']) ? trim( $_REQUEST['password'] ) : null;

/*---------------------------------------------------------------------------
  Check authentification
 ---------------------------------------------------------------------------*/

// default variables initialization
$claro_loginRequested = false;
$claro_loginSucceeded = false;
$GLOBALS['currentUser'] = false;

if ( $GLOBALS['logout'] && !empty($_SESSION['_uid']) )
{    
    // needed to notify that a user has just loggued out
    $logout_uid = $_SESSION['_uid'];
}

if ( ! empty($_SESSION['_uid']) && ! ($login || $GLOBALS['logout']) )
{
    if (isset($_REQUEST['switchToUser']))
    {
        if (! empty($_SESSION['_user']['isPlatformAdmin']))
        {
            if ((bool) $_SESSION['_user']['isPlatformAdmin'] === true)
            {
                $targetId = $_REQUEST['switchToUser'];

                if (user_is_admin($targetId))
                {
                    exit('ERROR !! You cannot access another administrator account !');
                }
                
                try
                {
                    $GLOBALS['currentUser'] = Claro_CurrentUser::getInstance($targetId, true);
                    $GLOBALS['currentUser']->saveToSession();
                    
                }
                catch (Exception $ex)
                {
                    exit('ERROR !! Undefined user id: the requested user doesn\'t exist'
                         . 'at line '.__LINE__);
                }
                
                $_SESSION['_uid']             = $targetId;
                $_SESSION['isVirtualUser']    = true;
                $_SESSION['is_platformAdmin'] = $_SESSION['_user']['isPlatformAdmin'];
                $_SESSION['is_allowedCreateCourse'] = $_SESSION['_user']['isCourseCreator'];
            }
        }
    }
    
    // uid is in session => login already done, continue with this value
    $GLOBALS['_uid'] = $_SESSION['_uid'];
    
    $GLOBALS['is_platformAdmin'] = !empty($_SESSION['is_platformAdmin'])
        ? $_SESSION['is_platformAdmin']
        : false
        ;

    $GLOBALS['is_allowedCreateCourse'] = !empty($_SESSION['is_allowedCreateCourse'])
        ? $_SESSION['is_allowedCreateCourse']
        : false
        ;
}
else
{
    // $GLOBALS['_uid']     = null;   // uid not in session ? prevent any hacking
    $GLOBALS['uidReset'] = false;
    
    // Unset current user authentication :
    if ( isset( $GLOBALS['_uid'] ) )
    {
        unset( $GLOBALS['_uid'] );
    }
    
    if ( isset( $_SESSION['_uid'] ) )
    {
        unset( $_SESSION['_uid'] );
    }
    
    if ( isset( $GLOBALS['_user'] ) )
    {
        unset( $GLOBALS['_user'] );
    }
    
    if ( isset( $_SESSION['_user'] ) )
    {
        unset( $_SESSION['_user'] );
    }

    if ( $login && $password ) // $login && $password are given to log in
    {
        // reinitalize all session variables
        session_unset();

        $claro_loginRequested = true;
        
        try
        {
            $GLOBALS['currentUser'] = AuthManager::authenticate($login, $password);

            if ( $GLOBALS['currentUser'] )
            {
                $GLOBALS['_uid'] = (int)$GLOBALS['currentUser']->userId;
                $GLOBALS['uidReset'] = true;
                $claro_loginSucceeded = true;
            }
            else
            {
                $GLOBALS['_uid'] = null;
                $claro_loginSucceeded = false;
            }
        }
        catch (Exception $e)
        {
            Console::error("Cannot authenticate user : " . $e->__toString());
            $GLOBALS['_uid'] = null;
            $claro_loginSucceeded = false;
        }
    } // end if $login & password
    else
    {
        $claro_loginRequested = false;
    }
}

/*---------------------------------------------------------------------------
  User initialisation
 ---------------------------------------------------------------------------*/

if ( !empty($GLOBALS['_uid']) ) // session data refresh requested && uid is given (log in succeeded)
{
    try
    {
        /*if (!$GLOBALS['currentUser'])
        {
            $GLOBALS['currentUser'] = Claro_CurrentUser::getInstance($GLOBALS['_uid']);
        }*/
        
        // User login
        if ( $GLOBALS['uidReset'] )
        {
            // Update the current session id with a newly generated one ( PHP >= 4.3.2 )
            // This function is vital in preventing session fixation attacks
            // function_exists('session_regenerate_id') && session_regenerate_id();
        
            $GLOBALS['cidReset'] = true;
            $GLOBALS['gidReset'] = true;
            
            $GLOBALS['currentUser'] = Claro_CurrentUser::getInstance( $GLOBALS['_uid'], true );
            
            $GLOBALS['_user'] = $GLOBALS['currentUser']->getRawData();
    
            // Extracting the user data
            $GLOBALS['is_platformAdmin'] = $GLOBALS['currentUser']->isPlatformAdmin;
            $GLOBALS['is_allowedCreateCourse']  = ( get_conf('courseCreationAllowed', true) && $GLOBALS['currentUser']->isCourseCreator ) || $GLOBALS['is_platformAdmin'];
            
            $GLOBALS['currentUser']->saveToSession();
    
            if ( $GLOBALS['currentUser']->firstLogin() )
            {
                // first login for a not self registred (e.g. registered by a teacher)
                // do nothing (code may be added later)
                $GLOBALS['currentUser']->updateCreatorId();
                $_SESSION['firstLogin'] = true;
            }
            else
            {
                $_SESSION['firstLogin'] = false;
            }
    
            // RECORD SSO COOKIE
            // $ssoEnabled set in conf/auth.sso.conf.php
            if ( get_conf('ssoEnabled',false ))
            {
                FromKernel::uses ( 'sso/cookie.lib' );
                $boolCookie = SingleSignOnCookie::setForUser( $GLOBALS['currentUser']->userId );
            } // end if ssoEnabled
        }
        // User in session
        else
        {
            $GLOBALS['currentUser'] = Claro_CurrentUser::getInstance($GLOBALS['_uid']);
            
            try
            {
                $GLOBALS['currentUser']->loadFromSession();
                $GLOBALS['_user'] = $GLOBALS['currentUser']->getRawData();
            }
            catch ( Exception $e )
            {
                $GLOBALS['_user'] = null;
            }
        }
    }
    catch ( Exception $e )
    {
        exit('WARNING !! Undefined user id: the requested user doesn\'t exist '
            . 'at line '.__LINE__);
    }
}
else
{
    // Anonymous, logout or login failed
    $GLOBALS['_user'] = null;
    $GLOBALS['_uid']  = null;
    $GLOBALS['is_platformAdmin']        = false;
    $GLOBALS['is_allowedCreateCourse']  = false;
}

/*---------------------------------------------------------------------------
  Course initialisation
 ---------------------------------------------------------------------------*/

// if the requested course is different from the course in session

if ( $cidReq && ( !isset($_SESSION['_cid']) || $cidReq != $_SESSION['_cid'] ) )
{
    $GLOBALS['cidReset'] = true;
    $GLOBALS['gidReset'] = true;    // As groups depend from courses, group id is reset
}

if ( $GLOBALS['cidReset'] ) // course session data refresh requested
{
    if ( $cidReq )
    {
        $GLOBALS['_course'] = claro_get_course_data($cidReq, true);

        if ($GLOBALS['_course'] == false)
        {
            die('WARNING !! The course\'s datas couldn\'t be loaded at line '
                .__LINE__.'.  Please contact your platform administrator.');
        }

        $GLOBALS['_cid']    = $GLOBALS['_course']['sysCode'];

        $GLOBALS['_groupProperties'] = claro_get_main_group_properties($GLOBALS['_cid']);

        if ($GLOBALS['_groupProperties'] == false)
        {
            die('WARNING !! The group\'s properties couldn\'t be loaded at line '
                .__LINE__.'.  Please contact your platform administrator.');
        }
    }
    else
    {
        $GLOBALS['_cid']    = null;
        $GLOBALS['_course'] = null;

        $GLOBALS['_groupProperties'] ['registrationAllowed'] = false;
        
        $groupToolList = get_group_tool_label_list();
        
        foreach ( $groupToolList as $thisGroupTool )
        {
            $thisGroupToolLabel = $thisGroupTool['label'];
            $GLOBALS['_groupProperties']['tools'][$thisGroupToolLabel] = false;
        }
        
        $GLOBALS['_groupProperties']['private'] = true;
    }

}
else // else of if($GLOBALS['cidReset']) - continue with the previous values
{
    $GLOBALS['_cid'] = !empty($_SESSION['_cid'])
        ? $_SESSION['_cid']
        : null
        ;
    
    $GLOBALS['_course'] = !empty($_SESSION['_course'])
        ? $_SESSION['_course']
        : null
        ;
    
    $GLOBALS['_groupProperties'] = !empty($_SESSION['_groupProperties'])
        ? $_SESSION['_groupProperties']
        : null
        ;
}

/*---------------------------------------------------------------------------
  Course / user relation initialisation
 ---------------------------------------------------------------------------*/

if ( $GLOBALS['uidReset'] || $GLOBALS['cidReset'] ) // session data refresh requested
{
    if ( $GLOBALS['_uid'] && $GLOBALS['_cid'] ) // have keys to search data
    {
          $_course_user_properties = claro_get_course_user_properties($GLOBALS['_cid'],$GLOBALS['_uid'],true);

          // would probably be less and less used because
          // claro_get_course_user_data($GLOBALS['_cid'],$GLOBALS['_uid'])
          // and claro_get_current_course_user_data() do the same job

          $GLOBALS['_profileId']      = $_course_user_properties['privilege']['_profileId'];
          $GLOBALS['is_courseMember'] = $_course_user_properties['privilege']['is_courseMember'];
          $GLOBALS['is_courseTutor']  = $_course_user_properties['privilege']['is_courseTutor'];
          $GLOBALS['is_courseAdmin']  = $_course_user_properties['privilege']['is_courseAdmin'];

          $GLOBALS['_courseUser'] = claro_get_course_user_data($GLOBALS['_cid'],$GLOBALS['_uid']);
    }
    else // keys missing => not anymore in the course - user relation
    {
        // course
        $GLOBALS['_profileId']      = claro_get_profile_id('anonymous');
        $GLOBALS['is_courseMember'] = false;
        $GLOBALS['is_courseAdmin']  = false;
        $GLOBALS['is_courseTutor']  = false;

        $GLOBALS['_courseUser'] = null; // not used
    }
    
    $GLOBALS['is_courseAllowed'] = (bool)
    (
        ( $GLOBALS['_course']['visibility']
          && ( $GLOBALS['_course']['access'] == 'public'
               || ( $GLOBALS['_course']['access'] == 'platform'
                    && claro_is_user_authenticated()
                  )
             )
        )
        || $GLOBALS['is_courseMember']
        || $GLOBALS['is_platformAdmin']
    ); // here because it's a right and not a state
}
else // else of if ($GLOBALS['uidReset'] || $GLOBALS['cidReset']) - continue with the previous values
{
    $GLOBALS['_profileId'] = !empty($_SESSION['_profileId'])
        ? $_SESSION['_profileId']
        : false
        ;
    
    $GLOBALS['is_courseMember'] = !empty($_SESSION['is_courseMember'])
        ? $_SESSION['is_courseMember']
        : false
        ;
    
    $GLOBALS['is_courseAdmin'] = !empty($_SESSION['is_courseAdmin'])
        ? $_SESSION['is_courseAdmin']
        : false
        ;
    
    $GLOBALS['is_courseAllowed'] = !empty($_SESSION['is_courseAllowed'])
        ? $_SESSION['is_courseAllowed' ]
        : false
        ;
    
    $GLOBALS['is_courseTutor'] = !empty($_SESSION['is_courseTutor'])
        ? $_SESSION['is_courseTutor']
        : false
        ;
    
    // not used !?!
    $GLOBALS['_courseUser'] = !empty($_SESSION['_courseUser'])
        ? $_SESSION['_courseUser']
        : null
        ;
}

// Installed module in course if available in platform and not in course
if ( $GLOBALS['_cid']
    && is_array( $GLOBALS['_course'] )
    && isset($GLOBALS['_course']['dbNameGlu'])
    && !empty($GLOBALS['_course']['dbNameGlu'])
    && trim($GLOBALS['_course']['dbNameGlu']) )
{
    // 0. load course configuration to avoid creating uneeded examples
    
    require claro_get_conf_repository() . 'course_main.conf.php';
    
    
    // 1. get tool list from main db
    
    $mainCourseToolList = claro_get_main_course_tool_list();
    
    // 2. get list af already installed tools from course
    
    $tbl_mdb_names = claro_sql_get_main_tbl();
    $tbl_tool            = $tbl_mdb_names['tool'           ];

    $sql = " SELECT pct.id                    AS toolId       ,
                  pct.claro_label           AS label

            FROM `".$GLOBALS['_course']['dbNameGlu']."tool_list` AS ctl
            INNER JOIN `".$tbl_tool."` AS pct
            ON `ctl`.`tool_id` = `pct`.`id`
            WHERE ctl.installed = 'true'";
    
    $courseToolList = claro_sql_query_fetch_all_rows($sql);
    
    $tmp = array();
    
    foreach ( $courseToolList as $thisCourseTool )
    {
        $tmp[$thisCourseTool['label']] = $thisCourseTool['toolId'];
    }
    
    // 3. compare the two lists and register and install/activate missing tool if necessary
    
    $listOfToolsToAdd = array();
    
    foreach ( $mainCourseToolList as $thisToolId => $thisMainCourseTool )
    {
        if ( ! array_key_exists( $thisMainCourseTool['label'], $tmp ) )
        {
            $listOfToolsToAdd[$thisMainCourseTool['label']] = $thisToolId;
        }
    }
    
    foreach ( $listOfToolsToAdd as $toolLabel => $toolId )
    {
        if ( ! is_module_registered_in_course( $toolId, $GLOBALS['_cid'] ) )
        {
            register_module_in_single_course( $toolId, $GLOBALS['_cid'] );
        }
        
        if ( !is_module_installed_in_course( $toolLabel, $GLOBALS['_cid'] )
            && 'AUTOMATIC' == get_module_data( $toolLabel, 'add_in_course' ) )
        {
            install_module_in_course( $toolLabel, $GLOBALS['_cid'] );
        }
        
        if ( 'AUTOMATIC' == get_module_data( $toolLabel, 'add_in_course' ) )
        {
            if ( 'activated' == get_module_data( $toolLabel, 'activation' ) )
            {
                update_course_tool_activation_in_course( $toolId,
                    $GLOBALS['_cid'],
                    true );
                
                set_module_visibility_in_course( $toolId, $GLOBALS['_cid'], true );
            }
        }
    }
}

/*---------------------------------------------------------------------------
  Course / tool relation initialisation
 ---------------------------------------------------------------------------*/

// if the requested tool is different from the current tool in session
// (special request can come from the tool id, or the tool label)

if (   ( $GLOBALS['tidReq']    && $GLOBALS['tidReq']    != $_SESSION['_tid']                 )
    || ( $GLOBALS['tlabelReq'] && ( ! isset($_SESSION['_courseTool']['label'])
                         || $GLOBALS['tlabelReq'] != $_SESSION['_courseTool']['label']) )
   )
{
    $tidReset = true;
}

if ( $tidReset || $GLOBALS['cidReset'] ) // session data refresh requested
{
    if ( ( $GLOBALS['tidReq'] || $GLOBALS['tlabelReq']) && $GLOBALS['_cid'] ) // have keys to search data
    {
        $tbl_mdb_names = claro_sql_get_main_tbl();
        $tbl_tool            = $tbl_mdb_names['tool'           ];

        $sql = " SELECT ctl.id                  AS id            ,
                      pct.id                    AS toolId       ,
                      pct.claro_label           AS label         ,
                      ctl.script_name           AS name          ,
                      ctl.visibility            AS visibility    ,
                      pct.icon                  AS icon          ,
                      pct.access_manager        AS access_manager,
                      pct.script_url            AS url

                   FROM `".$GLOBALS['_course']['dbNameGlu']."tool_list` ctl,
                    `".$tbl_tool."`  pct

               WHERE `ctl`.`tool_id` = `pct`.`id`
                 AND (`ctl`.`id`      = '". (int) $GLOBALS['tidReq']."'
                       OR   (".(int) is_null($GLOBALS['tidReq'])." AND pct.claro_label = '". claro_sql_escape($GLOBALS['tlabelReq']) ."')
                     )";

        // Note : 'ctl' stands for  'course tool list' and  'pct' for 'platform course tool'
        $GLOBALS['_courseTool'] = claro_sql_query_get_single_row($sql);

        if ( is_array($GLOBALS['_courseTool']) ) // this tool have a recorded state for this course
        {
            $GLOBALS['_tid']        = $GLOBALS['_courseTool']['id'];
            $GLOBALS['_mainToolId'] = $GLOBALS['_courseTool']['toolId'];
        }
        else // this tool has no status related to this course
        {
            $activatedModules = get_module_label_list( true );
            
            if ( ! in_array( $GLOBALS['tlabelReq'], $activatedModules ) )
            {
                exit('WARNING !! Undefined Tlabel or Tid: your script declare '
                    . 'be a tool wich is not registred at line '.__LINE__.'.  '
                    . 'Please contact your platform administrator.');
            }
            else
            {
                $GLOBALS['_tid']        = null;
                $GLOBALS['_mainToolId'] = null;
                $GLOBALS['_courseTool'] = null;
            }
        }
    }
    else // keys missing => not anymore in the course - tool relation
    {
        // course
        $GLOBALS['_tid']        = null;
        $GLOBALS['_mainToolId'] = null;
        $GLOBALS['_courseTool'] = null;
    }

}
else // continue with the previous values
{
    $GLOBALS['_tid'] = !empty($_SESSION['_tid'])
        ? $_SESSION['_tid']
        : null
        ;
    
    $GLOBALS['_mainToolId'] = !empty($_SESSION['_mainToolId'])
        ? $_SESSION['_mainToolId']
        : null
        ;
    
    $GLOBALS['_courseTool'] = !empty( $_SESSION['_courseTool'])
        ? $_SESSION['_courseTool']
        : null
        ;
}

/*---------------------------------------------------------------------------
  Group initialisation
 ---------------------------------------------------------------------------*/

// if the requested group is different from the group in session

if ( $GLOBALS['gidReq'] && ( !isset($_SESSION['_gid']) || $GLOBALS['gidReq'] != $_SESSION['_gid']) )
{
    $GLOBALS['gidReset'] = true;
}

if ( $GLOBALS['gidReset'] || $GLOBALS['cidReset'] ) // session data refresh requested
{
    if ( $GLOBALS['gidReq'] && $GLOBALS['_cid'] ) // have keys to search data
    {
        $context = array(
            CLARO_CONTEXT_COURSE => $GLOBALS['_cid'],
            CLARO_CONTEXT_GROUP => $GLOBALS['gidReq'] );
        
        $course_group_data = claro_get_group_data($context, true );

        $GLOBALS['_group'] = $course_group_data;
        
        if ( $GLOBALS['_group'] ) // This group has recorded status related to this course
        {
            $GLOBALS['_gid'] = $course_group_data ['id'];
        }
        else
        {
            claro_die('WARNING !! Undefined groupd id: the requested group '
                . ' doesn\'t exist at line '.__LINE__.'.  '
                . 'Please contact your platform administrator.');
        }
    }
    else  // Keys missing => not anymore in the group - course relation
    {
        $GLOBALS['_gid']   = null;
        $GLOBALS['_group'] = null;
    }
}
else // continue with the previous values
{
    $GLOBALS['_gid'] = !empty($_SESSION ['_gid'])
        ? $_SESSION ['_gid']
        : null
        ;
    
    $GLOBALS['_group'] = !empty($_SESSION ['_group'])
        ? $_SESSION ['_group']
        : null
        ;
}

/*---------------------------------------------------------------------------
  Group / User relation initialisation
 ---------------------------------------------------------------------------*/

if ($GLOBALS['uidReset'] || $GLOBALS['cidReset'] || $GLOBALS['gidReset']) // session data refresh requested
{
    if ($GLOBALS['_uid'] && $GLOBALS['_cid'] && $GLOBALS['_gid']) // have keys to search data
    {
        $sql = "SELECT status,
                       role
                FROM `" . $GLOBALS['_course']['dbNameGlu'] . "group_rel_team_user`
                WHERE `user` = '". (int) $GLOBALS['_uid'] . "'
                AND `team`   = '". (int) $GLOBALS['gidReq'] . "'";

        $result = claro_sql_query($sql)  or die ('WARNING !! Load user course_group status (DB QUERY) FAILED ! '.__LINE__);

        if (mysqli_num_rows($result) > 0) // This user has a recorded status related to this course group
        {
            $gpuData = mysqli_fetch_array($result);

            $GLOBALS['_groupUser'] ['status'] = $gpuData ['status'];
            $GLOBALS['_groupUser'] ['role'  ] = $gpuData ['role'  ];

            $GLOBALS['is_groupMember'] = true;
        }
        else
        {
            $GLOBALS['is_groupMember'] = false;
            $GLOBALS['_groupUser']     = null;
        }

        $GLOBALS['is_groupTutor'] = ($GLOBALS['_group']['tutorId'] == $GLOBALS['_uid']);

    }
    else  // Keys missing => not anymore in the user - group (of this course) relation
    {
        $GLOBALS['is_groupMember'] = false;
        $GLOBALS['is_groupTutor']  = false;

        $GLOBALS['_groupUser'] = null;
    }

    // user group access is allowed or user is group member or user is admin
    $GLOBALS['is_groupAllowed'] = (bool) (!$GLOBALS['_groupProperties']['private']
                               || $GLOBALS['is_groupMember']
                               || $GLOBALS['is_courseAdmin']
                               || claro_is_group_tutor()
                               || $GLOBALS['is_platformAdmin']);

}
else // continue with the previous values
{
    $GLOBALS['_groupUser'] = !empty($_SESSION['_groupUser'])
        ? $_SESSION['_groupUser']
        : null
        ;
        
    $GLOBALS['is_groupMember']  = !empty($_SESSION['is_groupMember'])
        ? $_SESSION['is_groupMember']
        : null
        ;
    
    $GLOBALS['is_groupTutor'] = !empty($_SESSION['is_groupTutor'])
        ? $_SESSION['is_groupTutor']
        : null
        ;
    
    $GLOBALS['is_groupAllowed'] = !empty($_SESSION['is_groupAllowed'])
        ? $_SESSION['is_groupAllowed']
        : null
        ;
}

/*---------------------------------------------------------------------------
  COURSE TOOL / USER / GROUP REL. INIT
 ---------------------------------------------------------------------------*/

if ( $GLOBALS['uidReset'] || $GLOBALS['cidReset'] || $GLOBALS['gidReset'] || $tidReset ) // session data refresh requested
{
    if ( $GLOBALS['_tid'] && $GLOBALS['_gid'] )
    {
        //echo 'passed here';

        $toolLabel = trim( $GLOBALS['_courseTool']['label'] , '_');

        $GLOBALS['is_toolAllowed'] = array_key_exists($toolLabel, $GLOBALS['_groupProperties'] ['tools'])
            && $GLOBALS['_groupProperties'] ['tools'] [$toolLabel]
            // do not allow to access group tools when groups are not allowed for current profile
            && claro_is_allowed_tool_read(get_tool_id_from_module_label('CLGRP'),$GLOBALS['_profileId'],$GLOBALS['_cid']);

        if ( $GLOBALS['_groupProperties'] ['private'] )
        {
            $GLOBALS['is_toolAllowed'] = $GLOBALS['is_toolAllowed'] && ( $GLOBALS['is_groupMember'] || claro_is_group_tutor() );
        }

        $GLOBALS['is_toolAllowed'] = $GLOBALS['is_toolAllowed'] || ( $GLOBALS['is_courseAdmin'] || $GLOBALS['is_platformAdmin'] );
    }
    elseif ( $GLOBALS['_tid'] )
    {
        if ( ( ! $GLOBALS['_courseTool']['visibility'] && ! claro_is_allowed_tool_edit($GLOBALS['_mainToolId'],$GLOBALS['_profileId'],$GLOBALS['_cid']) )
             || ! claro_is_allowed_tool_read($GLOBALS['_mainToolId'],$GLOBALS['_profileId'],$GLOBALS['_cid']) )
        {
            $GLOBALS['is_toolAllowed'] = false;
        }
        else
        {
            $GLOBALS['is_toolAllowed'] = true;
        }
    }
    else
    {
        $GLOBALS['is_toolAllowed'] = false;
    }

}
else // continue with the previous values
{
    $GLOBALS['is_toolAllowed'] = !empty( $_SESSION['is_toolAllowed'] )
        ? $_SESSION['is_toolAllowed']
        : null
        ;
}

/*---------------------------------------------------------------------------
  Course tool list initialisation for current user
 ---------------------------------------------------------------------------*/

if ($GLOBALS['uidReset'] || $GLOBALS['cidReset'])
{
    if ($GLOBALS['_cid']) // have course keys to search data
    {
        $GLOBALS['_courseToolList'] = claro_get_course_tool_list($GLOBALS['_cid'], $GLOBALS['_profileId'], true, true);
    }
    else
    {
        $GLOBALS['_courseToolList'] = null;
    }
}
else // continue with the previous values
{
    $GLOBALS['_courseToolList'] = !empty($_SESSION['_courseToolList'])
        ? $_SESSION['_courseToolList']
        : null
        ;
}

/*===========================================================================
  Save all variables in session
 ===========================================================================*/

/*---------------------------------------------------------------------------
  User info in the platform
 ---------------------------------------------------------------------------*/
$_SESSION['_uid'                  ] = $GLOBALS['_uid'];
$_SESSION['_user'                 ] = $GLOBALS['_user'];
$_SESSION['is_allowedCreateCourse'] = $GLOBALS['is_allowedCreateCourse'];
$_SESSION['is_platformAdmin'      ] = $GLOBALS['is_platformAdmin'];

/*---------------------------------------------------------------------------
  Course info of $GLOBALS['_cid'] course
 ---------------------------------------------------------------------------*/

$_SESSION['_cid'            ] = $GLOBALS['_cid'];
$_SESSION['_course'         ] = $GLOBALS['_course'];
$_SESSION['_groupProperties'] = $GLOBALS['_groupProperties'];

/*---------------------------------------------------------------------------
  User rights of $GLOBALS['_uid'] in $GLOBALS['_cid'] course
 ---------------------------------------------------------------------------*/

$_SESSION['_profileId'      ] = $GLOBALS['_profileId'];
$_SESSION['is_courseAdmin'  ] = $GLOBALS['is_courseAdmin'];
$_SESSION['is_courseAllowed'] = $GLOBALS['is_courseAllowed'];
$_SESSION['is_courseMember' ] = $GLOBALS['is_courseMember'];
$_SESSION['is_courseTutor'  ] = $GLOBALS['is_courseTutor'];

if ( isset($GLOBALS['_courseUser']) ) $_SESSION['_courseUser'] = $GLOBALS['_courseUser']; // not used

/*---------------------------------------------------------------------------
  Tool info of $GLOBALS['_tid'] in $GLOBALS['_cid'] course
 ---------------------------------------------------------------------------*/

$_SESSION['_tid'       ] = $GLOBALS['_tid'];
$_SESSION['_mainToolId'] = $GLOBALS['_mainToolId'];
$_SESSION['_courseTool'] = $GLOBALS['_courseTool'];

/*---------------------------------------------------------------------------
  Group info of $GLOBALS['_gid'] in $GLOBALS['_cid'] course
 ---------------------------------------------------------------------------*/

$_SESSION['_gid'           ] = $GLOBALS['_gid'];
$_SESSION['_group'         ] = $GLOBALS['_group'];
$_SESSION['is_groupAllowed'] = $GLOBALS['is_groupAllowed'];
$_SESSION['is_groupMember' ] = $GLOBALS['is_groupMember'];
$_SESSION['is_groupTutor'  ] = $GLOBALS['is_groupTutor'];

/*---------------------------------------------------------------------------
 Tool in $GLOBALS['_cid'] course allowed to $GLOBALS['_uid'] user
 ---------------------------------------------------------------------------*/

if ( $GLOBALS['_cid'] && $GLOBALS['_tid'] )
{
    $GLOBALS['is_toolAllowed'] = $GLOBALS['is_toolAllowed'] && claro_is_course_tool_activated( $GLOBALS['_cid'], $GLOBALS['_tid'] );
}

$_SESSION['is_toolAllowed'] = $GLOBALS['is_toolAllowed'];

/*---------------------------------------------------------------------------
  List of available tools in $GLOBALS['_cid'] course
 ---------------------------------------------------------------------------*/

$_SESSION['_courseToolList'] = $GLOBALS['_courseToolList'];
