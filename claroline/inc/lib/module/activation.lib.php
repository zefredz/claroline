<?php // $Id$

/**
 * Claroline extension modules activation functions
 *
 * @version     1.12 $Revision$
 * @copyright   (c) 2001-2014, Universite catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html
 *      GNU GENERAL PUBLIC LICENSE version 2 or later
 * @author      Claro Team <cvs@claroline.net>
 * @package     kernel.module
 * @since       1.12
 */

require_once __DIR__ . '/../backlog.class.php';
require_once __DIR__ . '/cache.lib.php';

/**
 * Get the list of modules that cannot be deactivated
 * @return array
 */
function get_not_deactivable_tool_list()
{
    return array(
        'CLDOC',
        'CLGRP',
        'CLUSR',
        'CLFRM',
        'CLLNP'
    );
}

/**
 * Activate a module, its effect is
 * - to call the activation script of the module (if there is any)
 * - to modify the information in the main DB
 * @param  integer $moduleId : ID of the module that must be activated
 * @return array( backlog, boolean )
 *      backlog object
 *      boolean true if the activation process suceeded, false otherwise
 */
function activate_module($moduleId, $activateInAllCourses = false)
{
    $success = true;
    $backlog1 = new Backlog;
    // find module informations

    $tbl = claro_sql_get_main_tbl();
    $moduleInfo =  get_module_info($moduleId);

    list( $backlog2, $success ) = activate_module_in_platform($moduleId);
    
    if( ! $success )
    {
        return array( $backlog2, $success );
    }
    
    $backlog1->append($backlog2);
    
    if ( $activateInAllCourses && $moduleInfo['type'] == 'tool' /*&& $moduleInfo['activateInCourses'] == 'AUTOMATIC'*/ )
    {
        // FIXME : ONLY WHEN INSTALLING A MODULE !
        if ( activate_module_in_all_courses( $moduleInfo['label'] ) )
        {
            $success = true;
            $backlog1->success( get_lang('Module activation in courses succeeded'));
        }
        else
        {
            $success = false;
            $backlog1->failure( get_lang('Module activation in courses failed'));
        }
    }

    return array( $backlog1, $success );
}

/**
 * Activate the module for the plateforme
 * @param int $moduleId
 * @return array array( Backlog $backlog, boolean $success );
 * @todo remove the need of the Backlog and use Exceptions instead
 */
function activate_module_in_platform( $moduleId )
{
    $success = true;
    $backlog = new Backlog;
    // find module informations

    $tbl = claro_sql_get_main_tbl();

    // TODO : 1- call activation script (if any) from the module repository


    // 2- change related entry of module table in the main DB

    $sql = "UPDATE `" . $tbl['module']."`
            SET `activation` = 'activated'
            WHERE `id` = " . (int) $moduleId;

    $result = claro_sql_query($sql);

    if ( ! $result )
    {
        $success = false;
        $backlog->failure(get_lang( 'Cannot update database' ));
    }
    else
    {
        $backlog->success(get_lang( 'Database update successful' ));
        //5- cache file with the module's include must be renewed after activation of the module

        if ( generate_module_cache() )
        {
            $backlog->success(get_lang( 'Module cache update succeeded' ));
        }
        else
        {
            $backlog->failure(get_lang( 'Module cache update failed' ));
            $success = false;
        }
    }
    
    return array( $backlog, $success );
}

/**
 * Activate the module in all courses
 * @param string $moduleLabel
 * @return array array( Backlog $backlog, boolean $success );
 * @todo remove the need of the Backlog and use Exceptions instead
 */
function activate_module_in_all_courses( $toolLabel )
{
    $toolId = get_tool_id_from_module_label( $toolLabel );
    $tbl = claro_sql_get_main_tbl();
    
    $sql = "SELECT `code` FROM `" . $tbl['course'] . "`";

    $courseList = claro_sql_query_fetch_all( $sql );
    
    foreach ( $courseList as $course )
    {
        if ( ! update_course_tool_activation_in_course( $toolId,
            $course['code'],
            true ) )
        {
            return false;
        }
    }
    
    return true;
}

/**
 * Desactivate a module, its effect is
 *   - to call the desactivation script of the module (if there is any)
 *   - to modify the information in the main DB
 * @param  integer $moduleId : ID of the module that must be desactivated
 * @return array( backlog, boolean )
 *      backlog object
 *      boolean true if the deactivation process suceeded, false otherwise
 * @todo remove the need of the Backlog and use Exceptions instead
 */
function deactivate_module($moduleId)
{
    $success = true;
    $backlog = new Backlog;

    //find needed info :

    $moduleInfo =  get_module_info($moduleId);
    $tbl = claro_sql_get_main_tbl();

    // TODO : 1- call desactivation script (if any) from the module repository

    //4- change related entry in the main DB, module table

    $tbl = claro_sql_get_main_tbl();

    $sql = "UPDATE `" . $tbl['module'] . "`
            SET `activation` = 'desactivated'
            WHERE `id`= " . (int) $moduleId;

    $result = claro_sql_query($sql);

    if ( ! $result )
    {
        $success = false;
        $backlog->failure(get_lang( 'Cannot update database' ));
    }
    else
    {
        $backlog->success(get_lang( 'Database update successful' ));
        //5- cache file with the module's include must be renewed after desactivation of the module

        if ( generate_module_cache() )
        {
            $backlog->success(get_lang( 'Module cache update succeeded' ));
        }
        else
        {
            $backlog->failure(get_lang( 'Module cache update failed' ));
            $success = false;
        }
    }

    return array( $backlog, $success );
}

/**
 * Activate a module in all the groups of the given course
 * @param Database_Connection $database, use Claroline::getDatabase()
 * @param string $moduleLabel
 * @param string $courseId
 * @return boolean
 */
function activate_module_in_groups( $database, $moduleLabel, $courseId )
{
    return change_module_activation_in_groups ( $database, $moduleLabel, $courseId, true );
}

/**
 * Deactivate a module in all the groups of the given course
 * @param Database_Connection $database, use Claroline::getDatabase()
 * @param string $moduleLabel
 * @param string $courseId
 * @return boolean
 */
function deactivate_module_in_groups( $database, $moduleLabel, $courseId )
{
    return change_module_activation_in_groups ( $database, $moduleLabel, $courseId, false );
}

/**
 * Change a module activation in all the groups of the given course
 * @param Database_Connection $database, use Claroline::getDatabase()
 * @param string $moduleLabel
 * @param string $courseId
 * @param boolean $activated
 * @return boolean
 */
function change_module_activation_in_groups ( $database, $moduleLabel, $courseId, $activated )
{
    $tbl = get_module_course_tbl(array('course_properties'), $courseId);

    $activation = $activated ? 1 : 0;

    if ( $database->query("
        SELECT
            `value`
        FROM
            `{$tbl['course_properties']}`
        WHERE
            `name` = " . $database->quote( $moduleLabel ) . "
        AND
            `category` = 'GROUP'" )->numRows() )
    {
        // update
        return $database->exec( "
            UPDATE
                `{$tbl['course_properties']}`
            SET
                `value` = {$activation}
            WHERE
                `name` = " . $database->quote( $moduleLabel ) . "
            AND
                `category` = 'GROUP'
         " );
    }
    else
    {
        // insert
        return $database->exec( "
            INSERT INTO
                `{$tbl['course_properties']}`
            SET
                `value` = {$activation},
                `name` = " . $database->quote( $moduleLabel ) . ",
                `category` = 'GROUP'
         " );
    }
}

/**
 * Get the list of modules activated for the groups in the given course
 * @param string $courseId course code
 * @return array or false
 */
function get_activated_group_tool_label_list( $courseId )
{
    return module_get_course_tool_list( $courseId,
                                    true,
                                    true,
                                    'group' );
}

/**
 * Is the given tool activated in the given course ?
 * @param int $toolId tool id
 * @param string $courseIdReq course code
 * @return boolean
 */
function is_tool_activated_in_course( $toolId, $courseIdReq, $forceCacheRefresh = false )
{
    static $courseActivatedToolList = false;
    
    if ( ! $courseActivatedToolList )
    {
        $courseActivatedToolList = array();
    }
    
    if ( ! isset($courseActivatedToolList[$courseIdReq]) || $forceCacheRefresh )
    {
        
        $courseActivatedToolList[$courseIdReq] = array();
    
        $tbl_cdb_names = claro_sql_get_course_tbl(
            claro_get_course_db_name_glued( $courseIdReq ) 
        );
        
        $tbl_course_tool_list = $tbl_cdb_names['tool'];

        $sql = "SELECT tool_id \n"
            . "FROM `{$tbl_course_tool_list}`\n"
            . "WHERE `activated` = 'true'"
            ;
        
        $result = claro_sql_query_fetch_all_rows($sql);
    
        foreach ( $result as $tool )
        {
            $courseActivatedToolList[$courseIdReq][$tool['tool_id']] = true;
        }
    
    }
    
    if ( isset( $courseActivatedToolList[$courseIdReq][$toolId] ) )
    {
        return true;
    }
    else
    {
        return false;
    }
}

/**
 * Is the given tool activated in the given course ? This version does not call
 * claro_get_course_data and should be used in conjunction with get_user_course_list
 * @param int $toolId tool id
 * @param array $course course( 'sysCode' => sysCode, 'db' => dbName
 * @return boolean
 */
function is_tool_activated_in_course_lightversion( $toolId, $course, $forceCacheRefresh = false )
{
    static $courseActivatedToolList = false;
    
    if ( ! $courseActivatedToolList )
    {
        $courseActivatedToolList = array();
    }
    
    $courseIdReq = $course['sysCode'];
    
    if ( ! isset($courseActivatedToolList[$courseIdReq]) || $forceCacheRefresh )
    {
        
        $courseActivatedToolList[$courseIdReq] = array();
    
        $tbl_cdb_names = claro_sql_get_course_tbl(
            get_conf('courseTablePrefix') . $course['db'] . get_conf('dbGlu')
        );
        
        $tbl_course_tool_list = $tbl_cdb_names['tool'];

        $sql = "SELECT tool_id \n"
            . "FROM `{$tbl_course_tool_list}`\n"
            . "WHERE `activated` = 'true'"
            ;
        
        $result = claro_sql_query_fetch_all_rows($sql);
    
        foreach ( $result as $tool )
        {
            $courseActivatedToolList[$courseIdReq][$tool['tool_id']] = true;
        }
    
    }
    
    if ( isset( $courseActivatedToolList[$courseIdReq][$toolId] ) )
    {
        return true;
    }
    else
    {
        return false;
    }
}

/**
 * Is the given module activated in the groups of the given course ?
 * @param string $courseId course code
 * @param string $toolLabel module label
 * @return boolean
 */
function is_tool_activated_in_groups( $courseId, $toolLabel )
{
    $activatedGroupToolList = get_activated_group_tool_label_list( $courseId );
    
    foreach ( $activatedGroupToolList as $groupTool )
    {
        if ( $groupTool['label'] == $toolLabel )
        {
            return true;
        }
    }
    
    return false;
}
