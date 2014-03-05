<?php // $Id$

/**
 * CLAROLINE
 *
 * Claroline extension modules management library
 *
 * @version     1.12 $Revision$
 * @copyright   (c) 2001-2014, Universite catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html
 *      GNU GENERAL PUBLIC LICENSE version 2 or later
 * @author      Claro Team <cvs@claroline.net>
 * @package     kernel.module
 * @todo why do we need that much identifiers for a module ?!?
 */

// REQUIRED KERNEL LIBRARIES
require_once __DIR__ . '/../fileManage.lib.php';
require_once __DIR__ . '/../backlog.class.php';
// ACCESS RIGHTS MANAGEMENT
require_once __DIR__ . '/../right/profileToolRight.class.php';
require_once __DIR__ . '/../right/right_profile.lib.php';

// MODULE FUNCTIONS
require_once __DIR__ . '/module.lib.php';
// MODULE REPOSITORY FUNCTIONS
require_once __DIR__ . '/repository.lib.php';
// MODULE PACKAGE FUNCTIONS
require_once __DIR__ . '/package.lib.php';
// MODULE MANIFEST PARSER
require_once __DIR__ . '/manifest.lib.php';
// MODULE CACHE FUNCTIONS
require_once __DIR__ . '/cache.lib.php';
// MODULE REGISTRATION FUNCTIONS
require_once __DIR__ . '/registration.lib.php';
// COURSE TOOL RANK MANAGEMENT FUNCTIONS
require_once __DIR__ . '/course.lib.php';
// MODULE ACTIVATION FUNCTIONS
require_once __DIR__ . '/activation.lib.php';
// MODULE DOCK MANAGEMENT FUNCTIONS
require_once __DIR__ . '/dock.lib.php';
// MODULE SETUP FUNCTIONS
require_once __DIR__ . '/setup.lib.php';

// INFORMATION AND UTILITY FUNCTIONS

/**
 * Return info of a module, including extra info and specific info for tool if case
 *
 * @param integer $moduleId
 * @return array (label, id, module_name,activation, type, script_url, icon,
 *  version, author, author_email, website, description, license)
 */
function get_module_info($moduleId)
{
    $tbl = claro_sql_get_tbl(array('module', 'module_info', 'course_tool'));

    $sql = "
        SELECT M.`label`  AS label,
               M.`id`         AS module_id,
               M.`name`       AS module_name,
               M.`activation` AS activation,
               M.`type`       AS type,
               M.`script_url` AS script_url,

               CT.`icon`       AS icon,
               CT.`add_in_course` AS activateInCourses,
               CT.`access_manager` AS accessManager,

               MI.version      AS version,
               MI.author       AS author,
               MI.author_email AS author_email ,
               MI.website      AS website,
               MI.description  AS description,
               MI.license      AS license

          FROM `" . $tbl['module']      . "` AS M

     LEFT JOIN `" . $tbl['module_info'] . "` AS MI
            ON M.`id` = MI . `module_id`

     LEFT JOIN `" . $tbl['course_tool'] . "` AS CT
            ON CT.`claro_label`= M.label

         WHERE M.`id` = " . (int) $moduleId;

    return claro_sql_query_get_single_row($sql);

}

/**
* Gest the list of module type already installed
* @return array of string
*
*/
function claro_get_module_types()
{
    $tbl = claro_sql_get_tbl('module');
    $sql = "SELECT distinct M.`type` AS `type`
           FROM `" . $tbl['module'] . "` AS M";
    $moduleType = claro_sql_query_fetch_all_cols($sql);
    return $moduleType['type'];
}

/**
 * Get installed module list, its effect is
 * to return an array containing the installed module's labels
 * @param string $type : type of the module that must be returned,
 *        if null, then all the modules are returned
 * @return array containing the labels of the modules installed
 *         on the platform
 *         false if query failed
 */
function get_installed_module_list($type = null)
{
    $tbl = claro_sql_get_main_tbl();

    $sql = "SELECT label FROM `" . $tbl['module'] . "`";

    if (!is_null($type))
    {
        $sql.= " WHERE `type`= '" . claro_sql_escape($type) . "'";
    }

    $moduleList = claro_sql_query_fetch_all_cols($sql);

    if ( is_array( $moduleList ) && array_key_exists('label', $moduleList ) )
    {
        return $moduleList['label'];
    }
    else
    {
        return array();
    }
}

/**
 * Get the tool id in a course (not the main tool id!) from the module label
 * @param the label of a course tool
 * @return the id in the course tool tabel
 *         false if tool not found
 */
function get_course_tool_id( $label )
{
    $tbl = claro_sql_get_main_tbl();

    $sql ="SELECT id FROM `" . $tbl['tool'] . "`
           WHERE claro_label='".$label."'";

    return claro_sql_query_get_single_value($sql);
}

/**
 * Set module visibility in all courses
 * @param int $moduleId id of the module
 * @param bool $visibility true for visible, false for invisible
 * @return array( backlog, boolean )
 *      backlog object
 *      boolean true if suceeded, false otherwise
 * @todo remove the need of the Backlog and use Exceptions instead
 */
function set_module_visibility( $moduleId, $visibility )
{
    $backlog = new Backlog;
    $success = true;

    $tbl = claro_sql_get_main_tbl();
    $moduleInfo =  get_module_info($moduleId);

    $tool_id = get_course_tool_id($moduleInfo['label'] );

    $sql = "SELECT `code` FROM `" . $tbl['course'] . "`";

    $course_list = claro_sql_query_fetch_all($sql);

    $default_visibility = false;

    foreach ($course_list as $course)
    {
        if ( false === set_module_visibility_in_course( $tool_id, $course['code'], $visibility ) )
        {
            $success = false;
            $backlog->failure(get_lang( 'Cannot change module visibility in %course'
                , array( '%course' => $course['code'] )));

            break;
        }
    }

    return array( $backlog, $success );
}

/**
 * Set module tool visibility in one course
 * @param int $tool_id id of the module tool
 * @param string $courseCode
 * @param bool $visibility true for visible, false for invisible
 * @return array( backlog, boolean )
 *      backlog object
 *      boolean true if suceeded, false otherwise
 * @todo remove the need of the Backlog and use Exceptions instead
 */
function set_module_visibility_in_course( $tool_id, $courseCode, $visibility )
{
    $currentCourseDbNameGlu = claro_get_course_db_name_glued( $courseCode );
    $course_tbl = claro_sql_get_course_tbl($currentCourseDbNameGlu);
    //$default_visibility = false;

    $sql = "UPDATE `" . $course_tbl['tool'] . "`
            SET visibility   = '" . ( $visibility ? 1 : 0 ) . "'
            WHERE `tool_id` = " . (int)$tool_id;

    if ( false === claro_sql_query($sql) )
    {
        return false;
    }
    else
    {
        return true;
    }
}

/**
 * Set the autoactivation the given module in courses
 * @param string $moduleLabel
 * @param boolean $value
 */
function set_module_autoactivation_in_course( $moduleLabel, $autoActivation )
{
    $sql_autoActivation = $autoActivation ? 'AUTOMATIC' : 'MANUAL';

    /* @todo move to a lib */
    $tbl_mdb_names        = claro_sql_get_main_tbl();
    $tbl_tool_list        = $tbl_mdb_names['tool'];

    return Claroline::getDatabase()->exec("
        UPDATE
            `{$tbl_tool_list}`
        SET
            add_in_course = '{$sql_autoActivation}'
        WHERE
            claro_label = " . Claroline::getDatabase()->quote( $moduleLabel ) . ";
    ");
}

/**
 * Allow course managers to activate the given module in their courses
 * @param string $moduleLabel
 * @param boolean $value
 */
function allow_module_activation_by_course_manager( $moduleLabel, $courseManagerCanActivate )
{
    $sql_accessManager = $courseManagerCanActivate ? 'COURSE_ADMIN' : 'PLATFORM_ADMIN';

    /* @todo move to a lib */
    $tbl_mdb_names        = claro_sql_get_main_tbl();
    $tbl_tool_list        = $tbl_mdb_names['tool'];

    return Claroline::getDatabase()->exec("
        UPDATE
            `{$tbl_tool_list}`
        SET
            access_manager = '{$sql_accessManager}'
        WHERE
            claro_label = " . Claroline::getDatabase()->quote( $moduleLabel ) . ";
    ");
}

/**
 * Get the count of modules by type.
 * @param bool $onlyActivated set to true to count only activated module 
 *      (default)
 * @return array [type => count]
 * @since Claroline 1.9.10, 1.10.7, 1.11
 */
function count_modules_by_type( $onlyActivated = true )
{
    $cnt = array();
    
    foreach ( get_available_module_types() as $moduleType )
    {
        $cnt[$moduleType] = 0;
    }
    
    $tbl = claro_sql_get_main_tbl();
    
    if ( $onlyActivated )
    {
        $activation = "WHERE `activation` = 'activated'";
    }
    else
    {
        $activation = "WHERE 1 = 1";
    }
    
    $rs = Claroline::getDatabase()->query("
        SELECT 
            `type`,
            COUNT(*) AS `count`
        FROM 
            `{$tbl['module']}`
        {$activation}
        GROUP BY `type`" );
    
    foreach ( $rs as $moduleTypeCount )
    {
        $cnt[$moduleTypeCount['type']] = $moduleTypeCount['count'];
    }
    
    return $cnt;
}

/**
 * Get the list of module types available on the platform
 * @return type 
 * @since Claroline 1.9.10, 1.10.7, 1.11
 */
function get_available_module_types()
{
    return array( 'tool', 'applet', 'crsmanage', 'admin' );
}

/**
 * Set the visibility this tool visible at the course creation
 * @param string moduleLabel
 * @param boolean visibility
 * since claroline 1.11
 */
function set_tool_visibility_at_course_creation($moduleLabel,$visibility)
{
    $tbl_mdb_names        = claro_sql_get_main_tbl();
    $tbl_tool_list        = $tbl_mdb_names['tool'];

    if ($visibility == 1) $def_access = 'ALL';
    else $def_access = 'COURSE_ADMIN';
    
    return Claroline::getDatabase()->exec("
        UPDATE
            `{$tbl_tool_list}`
        SET
            def_access = '{$def_access}'
        WHERE
            claro_label = " . Claroline::getDatabase()->quote( $moduleLabel ) . ";
    ");
}
