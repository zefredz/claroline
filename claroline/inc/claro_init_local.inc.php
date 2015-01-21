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

require_once __DIR__ . '/lib/core/kernel.lib.php';

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

try
{
    /*---------------------------------------------------------------------------
      Check authentification
     ---------------------------------------------------------------------------*/

    Claro_Kernel::executeAuthenticationProcess( $login, $password );

    /*---------------------------------------------------------------------------
      User initialisation
     ---------------------------------------------------------------------------*/

    Claro_Kernel::initializeUser();

    /*---------------------------------------------------------------------------
      Course initialisation
     ---------------------------------------------------------------------------*/

    Claro_Kernel::initializeCourse();
    
    /*---------------------------------------------------------------------------
      Group properties initialisation
     ---------------------------------------------------------------------------*/
    
  Claro_Kernel::initializeMainGroupProperties();

    /*---------------------------------------------------------------------------
      Course / user relation initialisation
     ---------------------------------------------------------------------------*/

    Claro_Kernel::initializeCoursePrivileges();

    /*---------------------------------------------------------------------------
      Install missing modules in course
     ---------------------------------------------------------------------------*/

    Claro_Kernel::installMissingModulesInCourse();

    /*---------------------------------------------------------------------------
      Course / tool relation initialisation
     ---------------------------------------------------------------------------*/

    Claro_Kernel::initializeCourseTool();

    /*---------------------------------------------------------------------------
      Group initialisation
     ---------------------------------------------------------------------------*/

    Claro_Kernel::initializeGroup();

    /*---------------------------------------------------------------------------
      Group / User relation initialisation
     ---------------------------------------------------------------------------*/

    Claro_Kernel::initializeGroupPrivileges();

    /*---------------------------------------------------------------------------
      COURSE TOOL / USER / GROUP REL. INIT
     ---------------------------------------------------------------------------*/

    Claro_Kernel::initializeToolPrivileges();

    /*---------------------------------------------------------------------------
      Course tool list initialisation for current user
     ---------------------------------------------------------------------------*/

    Claro_Kernel::initializeCourseToolList();

    /*===========================================================================
      Save all variables in session
     ===========================================================================*/

    Claro_Kernel::saveInitVariablesToSession();

    /* ---------------------------------------------------------------------------
     * Populate $claroline context and privileges
     * --------------------------------------------------------------------------
     */

    Claro_Kernel::populateClarolineDependencyInjectionContainer();

}
catch ( Exception $e )
{
    if ( claro_debug_mode () )
    {
        die ( "<pre style=\"text-align:center;\">{$e->__toString()}</pre>" );
    }
    else
    {
        die ( "<p style=\"text-align:center;\">{$e->getMessage()}</p>" );
    }
}
