<?php // $Id$

/**
 * Claroline extension modules library
 *
 * This lib make the interface with kernel task and module extention for theses
 * task. It also provide some backward compatibility functions.
 *
 * @version     1.12 $Revision$
 * @copyright   (c) 2001-2014, Universite catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GENERAL PUBLIC LICENSE
 *              version 2 or later
 * @author      Claro Team <cvs@claroline.net>
 * @package     kernel.module
 * @since       1.12
 */

// COURSE TOOL RANK MANAGEMENT FUNCTIONS
require_once __DIR__ . '/course.lib.php';
// MODULE ACTIVATION FUNCTIONS
require_once __DIR__ . '/activation.lib.php';

// CONSTANTS

defined('CLARO_CONTEXT_PLATFORM')     || define('CLARO_CONTEXT_PLATFORM','platform');
defined('CLARO_CONTEXT_COURSE')       || define('CLARO_CONTEXT_COURSE','course');
defined('CLARO_CONTEXT_GROUP')        || define('CLARO_CONTEXT_GROUP','group');
defined('CLARO_CONTEXT_USER')         || define('CLARO_CONTEXT_USER','user');
defined('CLARO_CONTEXT_TOOLINSTANCE') || define('CLARO_CONTEXT_TOOLINSTANCE','toolInstance');
defined('CLARO_CONTEXT_TOOLLABEL')    || define('CLARO_CONTEXT_TOOLLABEL','toolLabel');

/**
 * This function return the core repository of a package (zipped module).
 *
 * @return string
 * @since 1.9
 */
function get_package_path()
{
    return get_path('rootSys') . 'packages/';
}

/**
 * This function return the name of the folder of a module given its label.
 *
 * @param string $toolLabel
 * @return string folder name
 * @since 1.11
 */
function get_module_folder_name( $toolLabel )
{
    switch ($toolLabel)
    {
        // legacy modules
        case 'CLANN' : return 'announcements';
        case 'CLCAL' : return 'calendar';
        case 'CLFRM' : return 'phpbb';
        case 'CLCHT' : return 'chat';
        case 'CLDOC' : return 'document';
        case 'CLDSC' : return 'course_description';
        case 'CLUSR' : return 'user';
        case 'CLLNP' : return 'learnPath';
        case 'CLQWZ' : return 'exercise';
        case 'CLWRK' : return 'work';
        case 'CLWIKI' : return 'wiki';
        case 'CLLNK' : return 'linker';
        case 'CLGRP' : return 'group';
        case 'CLSTAT' : return 'tracking';
        case 'CLTI' : return 'tool_intro';
        // modern modules
        default: return $toolLabel;
    }
}

/**
 * This function return the path to a module folder given its label.
 *
 * @param string $toolLabel
 * @return string pâth to the module folder
 */
function get_module_path($toolLabel)
{

    $folderName = get_module_folder_name( rtrim($toolLabel,'_') ); // keep this line until  all claro_label
    
    // core modules
    if ( file_exists(get_path('clarolineRepositorySys') . $folderName) )
    {
        return get_path('clarolineRepositorySys') . $folderName;
    }
    // add-on modules
    else
    {
        return get_path('rootSys') . 'module/' . $folderName;
    }
}

/**
 * This function return the core repository of a module.
 *
 * @param string $toolLabel
 * @return string
 */
function get_module_url($toolLabel)
{
    $toolLabel = rtrim($toolLabel,'_');
    
    $folderName = get_module_folder_name( rtrim($toolLabel,'_') );
    
    // core modules
    if ( file_exists( get_path('clarolineRepositorySys') . $folderName ) )
    {
        return get_path('clarolineRepositoryWeb') . $folderName;
    }
    // add-on modules
    else
    {
        return get_conf('urlAppend') . '/module/' . $folderName;
    }
}

/**
 * Return the list of context that the tool can use but not manage.
 *
 * @param string $moduleLabel
 * @return array
 */
function get_module_db_dependance ( $moduleLabel )
{
    $tbl = claro_sql_get_main_tbl();
    
    $sqlModuleLabel = claro_sql_escape($moduleLabel);
    
    $contextList = claro_sql_query_fetch_all_rows("
        SELECT 
            `mc`.`context` AS `context`
        FROM 
            `{$tbl['module_contexts']}` AS `mc`
        LEFT JOIN 
            `{$tbl['module']}` AS `m`
        ON 
            `m`.`id` = `mc`.`module_id`
        WHERE 
            `m`.`label` = '{$sqlModuleLabel}'
    ");
    
    $contextListToReturn = array();
    
    foreach ( $contextList as $context )
    {
        if ( $context['context'] == 'course' )
        {
            $contextListToReturn[] = CLARO_CONTEXT_COURSE;
        }
        elseif ( $context['context'] == 'group' )
        {
            $contextListToReturn[] = CLARO_CONTEXT_GROUP;
        }
    }
    
    return $contextListToReturn;
}

/**
 * Get module entry filename
 * @param string $claroLabel module label
 * @return string
 */
function get_module_entry( $claroLabel )
{
    return get_module_data($claroLabel, 'entry' );

}

/**
 * Get the complete path to the entry of an module.
 *
 * @param string $claroLabel module label
 * @return string
 */
function get_module_entry_url( $claroLabel )
{
    return get_module_url($claroLabel) . '/'
        . ltrim(get_module_entry($claroLabel),'/')
        ;
}

/**
 * Get information about a module
 * @param string $claroLabel module label
 * @param string $dataName
 * @param boolean $ignoreCache
 * @return mixed
 */
function get_module_data( $claroLabel, $dataName = null, $ignoreCache = false )
{
    static $cachedModuleDataList = null;

    if ( is_null ($cachedModuleDataList) )
    {
        $cachedModuleDataList = array();
    }

    if ($ignoreCache || ! array_key_exists($claroLabel,$cachedModuleDataList))
    {
        $tbl = claro_sql_get_tbl(array('module', 'course_tool'));
        $sql = "SELECT M.`label`      AS label,
                   M.`id`             AS id,
                   M.`name`           AS moduleName,
                   M.`activation`     AS activation,
                   M.`type`           AS type,
                   M.`script_url`     AS entry,
                   CT.`icon`          AS icon,
                   CT.`def_rank`      AS rank,
                   CT.`add_in_course` AS add_in_course,
                   CT.`access_manager` AS access_manager

        FROM `" . $tbl['module'] . "` AS M
        LEFT JOIN `" . $tbl['course_tool'] . "` AS CT
            ON CT.`claro_label`= M.label
        WHERE  M.`label` = '" . claro_sql_escape($claroLabel) . "'";

        $cachedModuleDataList[$claroLabel] = claro_sql_query_get_single_row($sql);
    }
    
    if ( !is_null( $dataName ) )
    {
        return $cachedModuleDataList[$claroLabel][$dataName];
    }
    else
    {
        return $cachedModuleDataList[$claroLabel];
    }
}

/**
 * Check if a module is installed and actived.
 *
 * @param string $modLabel module label
 * @return array
 */
function check_module($modLabel)
{
    $tbl_name        = claro_sql_get_main_tbl();
    $tbl_module      = $tbl_name['module'];

    $sql = "SELECT M.`id`              AS `id`,
                   M.`label`           AS `label`,
                   M.`activation`      AS `activation`
            FROM `" . $tbl_module . "` AS M
            WHERE M.`label` = '".$modLabel."'";

    $result = claro_sql_query_get_single_row($sql);

    if (empty($result))
    {
        $message[] = "The ".$modLabel." hasn't been installed!";
        return array(false,$message);
    }
    else
    {
        if ($result['activation'] == 'desactivated')
        {
            $message[] = "The ".$modLabel." hasn't been activated!";
            return array(false,$message);
        }
        else
            return array(true,null);
    }
}

/**
 * Load language file for a module
 * @deprecated since Claroline 1.9, use language::load_module_translation
 * @param   $moduleLabel module label (default null for current module)
 * @param   $language language name (default null for current language)
 * @deprecated since 1.9
 */
function load_module_language ( $moduleLabel = null, $language = null )
{
    language::load_module_translation( $moduleLabel, $language );
}

/**
 * Merge module lang with lang file
 * @deprecated since Claroline 1.9, use language::load_module_translation
 * @param   $moduleLabel module label (default null for current module)
 * @param   $language language name (default null for current language)
 * @deprecated since 1.9
 */
function add_module_lang_array( $moduleLabel = null, $language = null )
{
    language::load_module_translation( $moduleLabel, $language );
}

/**
 * Get the list of all modules on the platform
 * @param   bool $activeModulesOnly selects only active module (default true)
 * @return  array module label list
 * @throws  COULD_NOT_GET_MODULE_LABEL_LIST
 * @author  Frederic Minne <zefredz@claroline.net>
 */
function get_module_label_list( $activeModulesOnly = true )
{
    $tbl_name_list = claro_sql_get_main_tbl();
    $tbl_module = $tbl_name_list['module'];

    $activationSQL = $activeModulesOnly
        ? "WHERE `activation` = 'activated'"
        : ''
        ;

    $sql = "SELECT `label`, `id` \n"
        . "FROM `" . $tbl_module . "`\n"
        . $activationSQL
        ;

    if ( ! ( $result = claro_sql_query_fetch_all( $sql ) ) )
    {
        return claro_failure::set_failure('COULD_NOT_GET_MODULE_LABEL_LIST');
    }
    else
    {
        $moduleLabelList = array();

        foreach( $result as $module )
        {
            $moduleLabelList[$module['id']] = $module['label'];
        }

        return $moduleLabelList;
    }
}

// ---- Database table list helpers

/**
 * Get main database table aliases
 * @return array 
 */
function get_main_tbl_aliases()
{
    return array (    
        'course'                    => 'cours',
        'user_category'             => 'class',
        'user_rel_profile_category' => 'rel_class_user',
        'course_user'               => 'rel_course_user',
    );
}

/**
 * Get course database table aliases
 * @return array 
 */
function get_course_tbl_aliases()
{
    return array(
        'links'                  => 'lnk_links',
        'resources'              => 'lnk_resources',
    );
}

/**
 * Get list of module table names 'localized' for the given course
 * @param array $arrTblName of tableName
 * @param string $courseCode course code
 * @return array $tableName => $dbNameGlue . $tableName
 * @throws Exception if no course code given and not in a course or
 *  course not valid
 */
function get_module_course_tbl( $arrTblName, $courseCode = null )
{
    if ( empty ( $courseCode ) )
    {
        if ( ! claro_is_in_a_course() )
        {
            throw new Exception('Not in a course !');
        }
        else
        {
            $courseCode = claro_get_current_course_id();
        }
    }
    
    $currentCourseDbNameGlu = claro_get_course_db_name_glued( $courseCode );

    if ( ! $currentCourseDbNameGlu )
    {
        throw new Exception('Invalid course !');
    }

    $aliases = get_main_tbl_aliases();

    $arrToReturn = array();

    foreach ( $arrTblName as $name )
    {
        if ( array_key_exists( $name, $aliases ) )
        {
            $arrToReturn[$name] = $currentCourseDbNameGlu . $aliases[$name];
        }
        else
        {
            $arrToReturn[$name] = $currentCourseDbNameGlu . $name;
        }

    }

    return $arrToReturn;
}

/**
 * Get list of module table names 'localized' for the main db
 * @param array $arrTblName of tableName
 * @return array $tableName => mainTblPrefix . $tableName
 */
function get_module_main_tbl( $arrTblName )
{
    $aliases = get_main_tbl_aliases();
    
    $mainDbNameGlu = get_conf('mainDbName') . '`.`' . get_conf('mainTblPrefix');
    
    $arrToReturn = array();

    foreach ( $arrTblName as $name )
    {
        if ( array_key_exists( $name, $aliases) )
        {
            $arrToReturn[$name] = $mainDbNameGlu . $aliases[$name];
        }
        else
        {
            $arrToReturn[$name] = $mainDbNameGlu . $name;
        }
    }

    return $arrToReturn;
}

/**
 * Load configuration file for a module
 * @param   $module module label (default null for current module)
 */
function load_module_config ( $moduleLabel = null )
{
    if ( !$moduleLabel )
    {
        $moduleLabel = get_current_module_label();
    }
    
    // load main config file
    $mainConfigFile = claro_get_conf_repository() . $moduleLabel . '.conf.php';
    
    if ( file_exists( $mainConfigFile ) )
    {
        include $mainConfigFile;
    }
    
    // check if config overwritten in course and load config file
    if ( claro_is_in_a_course() )
    {
        $courseConfigFile = get_conf('coursesRepositorySys')
            . claro_get_current_course_data('path')
            . '/conf/' . $moduleLabel . '.conf.php'
            ;
        
        if ( file_exists($courseConfigFile))
        {
            include $courseConfigFile;
        }
    }
}

/**
 * Get the list of tools in a course
 * @param string $courseIdReq course code
 * @param boolean $platformActive get only modules activated for the platform
 * @param boolean $courseActive get only modules activated in the current course
 * @param string $context context of the module
 * @return array or false
 */
function module_get_course_tool_list( $courseIdReq,
                                    $platformActive = true,
                                    $courseActive = true,
                                    $context = null )
{
    $tbl_mdb_names        = claro_sql_get_main_tbl();
    $tbl_tool_list        = $tbl_mdb_names['tool'];
    $tbl_module           = $tbl_mdb_names['module'];
    $tbl_module_contexts  = $tbl_mdb_names['module_contexts'];
    $tbl_cdb_names        = claro_sql_get_course_tbl( claro_get_course_db_name_glued($courseIdReq) );
    $tbl_course_tool_list = $tbl_cdb_names['tool'];
    
    if ( ! is_null ($context) )
    {
        $contextJoin = "LEFT JOIN `{$tbl_module_contexts}` AS mc\n"
            . " ON mc.module_id = `m`.id\n"
            ;
        
        $contextCondition = " AND `mc`.`context` = '"
            .claro_sql_escape($context)."'"
            ;
    }
    else
    {
        $contextJoin = "";
        $contextCondition = "";
    }
    
    /*
    * Search all the tool corresponding to this access levels
    */
    
    if ( is_null( $courseActive ) )
    {
        $sql_courseActive = '';
    }
    else
    {
        $sql_courseActive = $courseActive
            ? " AND ctl.activated = 'true' "
            : " AND ctl.activated = 'false' "
            ;
    }
    
    if ( is_null( $platformActive ) )
    {
        $sql_platformActive = '';
    }
    else
    {
        $sql_platformActive = $platformActive
            ? " AND m.activation = 'activated' "
            : " AND m.activation = 'deactivated' "
            ;
    }

    // find module or claroline existing tools
    
    $sql = "SELECT DISTINCT ctl.id            AS id,
                  pct.id                      AS tool_id,
                  pct.claro_label             AS label,
                  ctl.script_name             AS external_name,
                  ctl.visibility              AS visibility,
                  IFNULL(pct.icon,'tool.png') AS icon,
                  ISNULL(ctl.tool_id)         AS external,
                  m.activation ,
                  m.name                      AS name,
                  pct.access_manager            AS access_manager,
                  ctl.activated,
                  IFNULL( ctl.script_url ,
                          pct.script_url )    AS url
            FROM `{$tbl_tool_list}` AS pct
            LEFT JOIN `{$tbl_course_tool_list}` AS ctl
            ON ctl.tool_id = pct.id
            LEFT JOIN `{$tbl_module}` AS m
            ON m.label = pct.claro_label
            {$contextJoin}
            WHERE 1
            {$sql_platformActive}
            {$sql_courseActive}
            {$contextCondition}
            ORDER BY external, pct.def_rank, ctl.rank
        
    ";

    return claro_sql_query_fetch_all($sql);
}

/**
 * Get the list of labels for the modules available in groups
 * @param boolean $activatedOnly get only activated modules
 * @return array or false
 */
function get_group_tool_label_list( $activatedOnly = true )
{
    $tbl = claro_sql_get_main_tbl();
    
    $sql = "SELECT `m`.`label` AS `label`\n"
        . " FROM `{$tbl['module']}` AS `m`\n"
        . "LEFT JOIN `{$tbl['module_contexts']}` AS `mc`\n"
        . " ON `mc`.`module_id` = `m`.`id`\n"
        . "WHERE `mc`.`context` = 'group' "
        . " AND `m`.`type` = 'tool' "
        . ( $activatedOnly ? " AND `m`.`activation` = 'activated' " : '' )
        ;
    
    return claro_sql_query_fetch_all_rows($sql);
}

/**
 * Get the tool id corresponding to the given module label
 * @param string $moduleLabel
 * @param boolean $forceCacheRefresh
 * @return int
 */
function get_tool_id_from_module_label( $moduleLabel, $forceCacheRefresh = false )
{
    static $toolIdList = false;
    
    if ( !$toolIdList || $forceCacheRefresh )
    {
        $toolIdList = array();
        
        $tbl = claro_sql_get_main_tbl();

        $sql = "SELECT claro_label, id
                FROM `" . $tbl['tool']."`";

        $result = claro_sql_query_fetch_all_rows($sql);
        
        foreach ( $result as $tool )
        {
            $toolIdList[$tool['claro_label']] = $tool['id'];
        }
    }
    
    if ( isset( $toolIdList[$moduleLabel] ) )
    {
        return $toolIdList[$moduleLabel];
    }
    else
    {
        return false;
    }
}

/**
 * Get the module label corresponding to the given tool id
 * @param int $toolId
 * @return string
 */
function get_module_label_from_tool_id( $toolId, $forceCacheRefresh = false )
{
    static $toolIdList = false;
    
    if ( ! $toolIdList || $forceCacheRefresh )
    {
        $toolIdList = array();
        
        $tbl = claro_sql_get_main_tbl();

        $sql = "SELECT claro_label, id
                FROM `" . $tbl['tool']."`";

        $result = claro_sql_query_fetch_all_rows($sql);
        
        foreach ( $result as $tool )
        {
            $toolIdList[$tool['id']] = $tool['claro_label'];
        }
    }
    
    if ( isset($toolIdList[$toolId]) )
    {
        return $toolIdList[$toolId];
    }
    else
    {
        return false;
    }
}

/**
 * Get the context list of a module
 * @param string $moduleLabel
 * @return Iterator 
 */
function get_module_context_list( $moduleLabel )
{
    $tbl = claro_sql_get_main_tbl();
    
    $sqlModuleLabel = Claroline::getDatabase()->quote($moduleLabel);
    
    $contextList = Claroline::getDatabase()->query("
        SELECT 
            `mc`.`context` AS `context`
        FROM 
            `{$tbl['module_contexts']}` AS `mc`
        LEFT JOIN 
            `{$tbl['module']}` AS `m`
        ON 
            `m`.`id` = `mc`.`module_id`
        WHERE 
            `m`.`label` = {$sqlModuleLabel}
    ");
    
    $contextList->setFetchMode(Mysql_ResultSet::FETCH_COLUMN);
    
    return $contextList;
}

/**
 * Get url of a module icon
 * @param string $moduleLabel label of the module
 * @param string $default default icon
 * @return string
 * @since Claroline 1.9.10, 1.10.7, 1.11
 */
function get_module_icon_url( $moduleLabel, $moduleIcon = null, $default = 'exe' )
{
    if ( !empty($moduleIcon) && file_exists(get_module_path($moduleLabel) . '/' . $moduleIcon))
    {
        $icon = get_module_url($moduleLabel) . '/' . $moduleIcon;
    }
    elseif (file_exists(get_module_path($moduleLabel) . '/icon.png'))
    {
        $icon = get_module_url($moduleLabel) . '/icon.png';
    }
    elseif (file_exists(get_module_path($moduleLabel) . '/icon.gif'))
    {
        $icon = get_module_url($moduleLabel) . '/icon.gif';
    }
    else
    {
        $icon = get_icon_url($default);
    }
    
    return $icon;
}

/**
 * Get the list of course management modules
 * @param bool $onlyActivated
 * @return Database_Resultset [id,label,name,icon,activation]
 * @since Claroline 1.9.10, 1.10.7, 1.11
 */
function get_course_manage_module_list( $onlyActivated = true )
{
    return get_module_list_by_type( 'crsmanage', $onlyActivated );
}

/**
 * Get the list of platform administration modules
 * @param bool $onlyActivated
 * @return Database_Resultset [id,label,name,icon,activation]
 * @since Claroline 1.9.10, 1.10.7, 1.11
 */
function get_admin_module_list( $onlyActivated = true )
{
    return get_module_list_by_type( 'admin', $onlyActivated );
}


/**
 * Get the list of modules by type
 * @param bool $onlyActivated
 * @return Database_Resultset [id,label,name,icon,activation]
 * @since Claroline 1.9.10, 1.10.7, 1.11
 */
function get_module_list_by_type( $type, $onlyActivated = true )
{
    $tbl = claro_sql_get_main_tbl();
    
    if ( $onlyActivated )
    {
        $activation = "AND `activation` = 'activated'";
    }
    else
    {
        $activation = '';
    }
    
    return Claroline::getDatabase()->query("
        SELECT 
            M.`id`, 
            M.`label`, 
            M.`name`, 
            CT.`icon`,
            M.`activation`
        FROM 
            `{$tbl['module']}` AS M
        LEFT JOIN 
            `{$tbl['tool']}` AS CT
        ON 
            CT.`claro_label`= M.label
        WHERE 
            M.`type` = ".Claroline::getDatabase()->quote($type)."
        {$activation}" );
}

/**
 * Helper to set current module label and load config and language files
 * @param string $moduleLabel
 */
function set_and_load_current_module( $moduleLabel )
{
    load_module_language($moduleLabel);
    load_module_config($moduleLabel);
    set_current_module_label($moduleLabel);
}

/**
 * Get tool visibility for course when not in course context
 * @param int $toolId
 * @param string $courseCode
 * @return bool
 * @since Claroline 1.11.7
 */
function is_tool_visible_for_portlet( $toolId, $courseCode )
{ 
    $tbl_cdb_names = claro_sql_get_course_tbl(claro_get_course_db_name_glued($courseCode));
    return (bool) Claroline::getDatabase()->query( 
            "SELECT `visibility`
               FROM `" . $tbl_cdb_names['tool'] . "`
              WHERE `tool_id` = " . Claroline::getDatabase()->quote( $toolId ) )->fetch(Mysql_ResultSet::FETCH_VALUE); ;
   
}
