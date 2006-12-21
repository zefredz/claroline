<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );
/**
 * CLAROLINE
 *
 * Get and set value of current session.
 *
 * @version 1.8 $Revision$
 *
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @author see 'credits' file
 *
 * @since claroline 1.8.3
 *
 * @package KERNEL
 *
 */


/// GET VALUES FROM INIT
/// 5 types of  values/Function
/// 1° Is in the context
/// 2° Get ID
/// 3° Get Values/properties
/// 4° Get right
/// 5° get_init : generic function  to prepare 4st previous during  developpement

/// 1° Is in the context

/**
 * Return the init status
 *
 * @author Christophe Gesché <moosh@claroline.net>
 * @return
boolean
 */
function claro_is_user_authenticated()
{
    return ! is_null($GLOBALS['_uid']);
}

/**
 * Return the init status
 *
 * @author Christophe Gesché <moosh@claroline.net>
 * @return
boolean
 */
// replace is_null(claro_get_current_course_id())
function claro_is_in_a_course()
{
    return ! is_null(claro_get_current_course_id());
}

/**
 * Return the init status
 *
 * @author Christophe Gesché <moosh@claroline.net>
 * @return
boolean
 */
// replace is_null(claro_get_current_course_id())
function claro_is_in_a_group()
{
    return ! is_null($GLOBALS['_gid']);
}

/**
 * Return the init status
 *
 * @author Christophe Gesché <moosh@claroline.net>
 * @return
boolean
 */
// replace is_null(get_current_tool_id()) , isset($_tid) ....
function claro_is_in_a_tool()
{
    return ! is_null($GLOBALS['_tid']);
}

/// 2° Get ID


/**
 * Return the init status
 *
 * @author Christophe Gesché <moosh@claroline.net>
 * @return
boolean
 */
function claro_get_current_user_id()
{
    return get_init('_uid');
}

/**
 * Return the init status
 *
 * @author Christophe Gesché <moosh@claroline.net>
 * @return
boolean
 */

function claro_get_current_course_id()
{
    return get_init('_cid');
}

/**
 * Return the init status
 *
 * @author Christophe Gesché <moosh@claroline.net>
 * @return
boolean
 */

function claro_get_current_group_id()
{
    return get_init('_gid');
}

/**
 * Return the init status
 *
 * @author Christophe Gesché <moosh@claroline.net>
 * @return
boolean
 */

function claro_get_current_tool_id()
{
    return get_init('_tid');
}

/**
 * Return the value of a Claroline configuration parameter
 * @param string $param config parameter
 * @param mixed $default (optionnal) - set a defaut to return value
 *                                     if no paramater with such a name is found.
 * @author Christophe Gesché <moosh@claroline.net>
 * @return
string param value
 * @todo http://www.claroline.net/forum/viewtopic.php?t=4579
*/

/// 3° Get Values/properties

/**
 * Return data of the current course
 *
 * @param string or null $dataName name of field, or null to keep an array of all fields
 * @author Christophe Gesché <moosh@claroline.net>
 * @return
string or array of string
 */
function claro_get_current_course_data($dataName=null)
{
    $c = get_init('_course');
    if (is_null($dataName)) return $c;
    elseif (is_array($c) && array_key_exists($dataName,$c)) return $c[$dataName];
    else
    {
        pushClaroMessage( ' -' . $dataName . '- does not exist for course data','error');
        return null;
    }
}

/**
 * Return group properties for the current course
 *
 * @param string or null $dataName property name, or null to keep an array of all properties
 * @author Christophe Gesché <moosh@claroline.net>
 * @return
array of all value or value of given property name
 */
function claro_get_current_group_properties_data($dataName=null)
{
    $gp = get_init('_groupProperties');
    if (is_null($dataName)) return $gp;
    elseif (is_array($gp) && array_key_exists($dataName,$gp)) return $gp[$dataName];
    else
    {
        pushClaroMessage( ' -' . $dataName . '- does not exist for group properties data','error');
        return null;
    };



}

/**
 * Return data of the current user
 *
 * @param string or null $dataName name of field, or null to keep an array of all fields
 * @author Christophe Gesché <moosh@claroline.net>
 * @return
string or array of string
 */

function claro_get_current_user_data($dataName=null)
{
    $u = get_init('_user');
    if (is_null($dataName)) return $u;
    elseif (is_array($u) && array_key_exists($dataName,$u)) return $u[$dataName];
    else
    {
        pushClaroMessage( ' -' . $dataName . '- does not exist for user data','error');
        return null;
    };

}

/**
 * Return properties for the current group
 *
 * @param string or null $dataName property name, or null to keep an array of all properties
 * @author Christophe Gesché <moosh@claroline.net>
 * @return
array of all value or value of given property name
 */
function claro_get_current_group_data($dataName=null)
{
    $g = get_init('_group');
    if (is_null($dataName)) return $g;
    elseif (is_array($g) && array_key_exists($dataName,$g)) return $g[$dataName];
    else
    {
        pushClaroMessage( ' -' . $dataName . '- does not exist for group data','error');
        return null;
    };

}

/**
 * Return properties for the current user in the current course
 *
 * @param string or null $dataName property name, or null to keep an array of all properties
 * @author Christophe Gesché <moosh@claroline.net>
 * @return
array of all value or value of given property name
 */

function claro_get_current_course_user_data($dataName=null)
{
    $cu = get_init('_courseUser');
    if (is_null($dataName)) return $cu;
    elseif (is_array($cu) && array_key_exists($dataName,$cu)) return $cu[$dataName];
    else
    {
        pushClaroMessage( ' -' . $dataName . '- does not exist for course user relation data','error');
        return null;
    };
}

/**
 * Return properties for the current tool
 *
 * @param string or null $dataName property name, or null to keep an array of all properties
 * @author Christophe Gesché <moosh@claroline.net>
 * @return
array of all value or value of given property name
 */

function claro_get_current_course_tool_data($dataName=null)
{
    $ct = get_init('_courseTool');
    if (is_null($dataName)) return $ct;
    elseif (is_array($ct) && array_key_exists($dataName,$ct)) return $ct[$dataName];
    else
    {
        pushClaroMessage( ' -' . $dataName . '- does not exist for course tool relation data','error');
        return null;
    };

}

/**
 * Return tool listbfor the current course
 *
 * @param string or null $dataName property name, or null to keep an array of all properties
 * @author Christophe Gesché <moosh@claroline.net>
 * @return
array of all value or value of given property name
 */

function claro_get_current_course_tool_list_data($dataName=null)
{
    $ctl = get_init('_courseToolList');
    if (is_null($dataName)) return $ctl;
    elseif (is_array($ctl) && array_key_exists($dataName,$ctl)) return $ctl[$dataName];
    else
    {
        pushClaroMessage( ' -' . $dataName . '- does not exist for course  tool list data','error');
        return null;
    };

}

/// 4° Get right

/**
 * Return the right of the current user
 *
 * @author Christophe Gesché <moosh@claroline.net>
 * @return
boolean
 */

function claro_is_course_member()
{
    return get_init('is_courseMember');
}

/**
 * Return the right of the current user
 *
 * @author Christophe Gesché <moosh@claroline.net>
 * @return
boolean
 */
function claro_is_course_tutor()
{
    return get_init('is_courseTutor');
}

/**
 * Return the right of the current user
 *
 * @author Christophe Gesché <moosh@claroline.net>
 * @return
boolean
 */
function  claro_is_platform_admin()
{
    return get_init('is_platformAdmin');
}

/**
 * Return the right of the current user
 *
 * @author Christophe Gesché <moosh@claroline.net>
 * @return
boolean
 */
function  claro_is_course_admin()
{
    pushClaroMessage('use claro_is_course_manager() instead of claro_is_course_admin()','code review');
    return claro_is_course_manager();
}


/**
 * Return the right of the current user
 *
 * @author Christophe Gesché <moosh@claroline.net>
 * @return
boolean
 */
function  claro_is_course_manager()
{
    return get_init('is_courseAdmin');
}
/**
 * Return the right of the current user
 *
 * @author Christophe Gesché <moosh@claroline.net>
 * @return
boolean
 */
function claro_is_course_allowed()
{
    return get_init('is_courseAllowed');
}

/**
 * Return the right of the current user
 *
 * @author Christophe Gesché <moosh@claroline.net>
 * @return
boolean
 */
function  claro_is_allowed_to_create_course()
{
    return get_init('is_allowedCreateCourse');
}



/**
 * Return the right of the current user
 *
 * @author Christophe Gesché <moosh@claroline.net>
 * @return
boolean
 */
function  claro_is_group_member()
{
    return get_init('is_groupMember');
}

/**
 * Return the right of the current user
 *
 * @author Christophe Gesché <moosh@claroline.net>
 * @return
boolean
 */
function  claro_is_group_tutor()
{
    return get_init('is_groupTutor');
}

/**
 * Return the right of the current user
 *
 * @author Christophe Gesché <moosh@claroline.net>
 * @return
boolean
 */
function  claro_is_group_allowed()
{
    return is_null(get_init('is_groupAllowed'))? false : get_init('is_groupAllowed');
}

/**
 * Return the right of the current user
 *
 * @author Christophe Gesché <moosh@claroline.net>
 * @return
boolean
 */
function claro_is_tool_allowed()
{
    return get_init('is_toolAllowed');
}

// 5° Generic get_init
/**
 * Return the value of a Claroline configuration parameter
 * @param string $param config parameter
 * @param mixed $default (optionnal) - set a defaut to return value
 *                                     if no paramater with such a name is found.
 * @author Christophe Gesché <moosh@claroline.net>
 * @return
string param value
 * @todo http://www.claroline.net/forum/viewtopic.php?t=4579
*/

function get_init($param)
{
    static $initValueList = array( '_uid'                   // claro_get_current_user_id()
    , '_cid'                   // claro_get_current_course_id()
    , '_gid'                   // claro_get_current_group_id()
    , '_tid'                   // claro_get_current_tool_id()
    , 'is_authenticated'       // is_authenticated()
    , 'in_course_context'      // is_in_course_context()
    , 'in_group_context'       // is_in_group_context()
    , 'is_platformAdmin'       // claro_is_platformAdmin()
    , '_course'                // claro_get_current_course_data(field=all)
    , '_user'                  // claro_get_current_user_data(field=all)
    , '_group'                 // claro_get_current_group_data(field=all)
    , '_groupProperties'       // claro_get_current_group_properties_data(field=all)
    , '_courseUser'            // claro_get_current_course_user_data(field=all)
    , '_courseTool'            // claro_get_current_course_tool_data(field=all)
    , '_courseToolList'        // claro_get_current_course_tool_list_data(field=all)
    , 'is_courseMember'        // claro_is_courseMember()
    , 'is_courseTutor'         // claro_is_courseTutor()
    , 'is_courseAdmin'         // claro_is_courseAdmin()
    , 'is_courseAllowed'       // claro_is_course_allowed()
    , 'is_allowedCreateCourse' // claro_is_allowedCreateCourse()
    , 'is_groupMember'         // claro_is_groupMember()
    , 'is_groupTutor'          // claro_is_groupTutor()
    , 'is_groupAllowed'        // claro_is_groupAllowed()
    , 'is_toolAllowed'         // claro_is_toolAllowed()
    );

    if(!in_array($param, $initValueList )) trigger_error( htmlentities($param) . ' is not a know init value name ', E_USER_NOTICE);
    //TODO create a real auth function to eval this state
    if ( $param == 'is_authenticated') return !(bool) is_null($GLOBALS['_uid']);
    //TODO create a real course function to eval this state
    if ( $param == 'in_course_context') return !(bool) is_null(claro_get_current_course_id());
    if     ( array_key_exists($param,$GLOBALS) )  return $GLOBALS[$param];
    elseif ( defined($param)         )            return constant($param);
    return null;
}

?>