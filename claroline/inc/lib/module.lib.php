<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );
/**
 * CLAROLINE
 *
 * This lib make the interface with kernel task
 * and module extention for theses task.
 *
 * Provide also some function making abstracation
 * for transition between structures before and after modularity
 *
 * @version 1.8 $Revision$
 *
 * @copyright (c) 2001-2007 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @author see 'credits' file
 *
 * @package KERNEL
 *
 * @since 1.8
 *
 */

defined('CLARO_CONTEXT_PLATFORM')     || define('CLARO_CONTEXT_PLATFORM','platform');
defined('CLARO_CONTEXT_COURSE')       || define('CLARO_CONTEXT_COURSE','course');
defined('CLARO_CONTEXT_GROUP')        || define('CLARO_CONTEXT_GROUP','group');
defined('CLARO_CONTEXT_USER')         || define('CLARO_CONTEXT_USER','user');
defined('CLARO_CONTEXT_TOOLINSTANCE') || define('CLARO_CONTEXT_TOOLINSTANCE','toolInstance');
defined('CLARO_CONTEXT_TOOLLABEL')    || define('CLARO_CONTEXT_TOOLLABEL','toolLabel');

/**
 * This function return the core repositroy of a module.
 *
 * @param string $toolLabel
 * @return string
 */

function get_module_path($toolLabel)
{

    $toolLabel = rtrim($toolLabel,'_'); // keep this line until  all claro_label
    switch ($toolLabel)
    {
        case 'CLANN' : return get_path('clarolineRepositorySys') . 'announcements';
        case 'CLCAL' : return get_path('clarolineRepositorySys') . 'calendar';
        case 'CLFRM' : return get_path('clarolineRepositorySys') . 'phpbb';
        case 'CLCHT' : return get_path('clarolineRepositorySys') . 'chat';
        case 'CLDOC' : return get_path('clarolineRepositorySys') . 'document';
        case 'CLDSC' : return get_path('clarolineRepositorySys') . 'course_description';
        case 'CLUSR' : return get_path('clarolineRepositorySys') . 'user';
        case 'CLLNP' : return get_path('clarolineRepositorySys') . 'learnPath';
        case 'CLQWZ' : return get_path('clarolineRepositorySys') . 'exercise';
        case 'CLWRK' : return get_path('clarolineRepositorySys') . 'work';
        case 'CLWIKI' : return get_path('clarolineRepositorySys') . 'wiki';
        case 'CLLNK' : return get_path('clarolineRepositorySys') . 'linker';
        case 'CLGRP' : return get_path('clarolineRepositorySys') . 'group';
        case 'CLSTAT' : return get_path('clarolineRepositorySys') . 'tracking';
        default: return get_path('rootSys') . 'module/' . rtrim($toolLabel,'_');
    }
    return '';
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
    switch ($toolLabel)
    {
        case 'CLANN' : return get_path('clarolineRepositoryWeb') . 'announcements';
        case 'CLCAL' : return get_path('clarolineRepositoryWeb') . 'calendar';
        case 'CLFRM' : return get_path('clarolineRepositoryWeb') . 'phpbb';
        case 'CLCHT' : return get_path('clarolineRepositoryWeb') . 'chat';
        case 'CLDOC' : return get_path('clarolineRepositoryWeb') . 'document';
        case 'CLDSC' : return get_path('clarolineRepositoryWeb') . 'course_description';
        case 'CLUSR' : return get_path('clarolineRepositoryWeb') . 'user';
        case 'CLLNP' : return get_path('clarolineRepositoryWeb') . 'learnPath';
        case 'CLQWZ' : return get_path('clarolineRepositoryWeb') . 'exercise';
        case 'CLWRK' : return get_path('clarolineRepositoryWeb') . 'work';
        case 'CLLNK' : return get_path('clarolineRepositoryWeb') . 'linker';
        case 'CLWIKI' : return get_path('clarolineRepositoryWeb') . 'wiki';
        case 'CLGRP' : return get_path('clarolineRepositoryWeb') . 'group';
        default: return get_conf('urlAppend') . '/module/' . $toolLabel;
    }
    return '';

}

/**
 * Return the list of context that the tool can use but not manage.
 *
 * @param string $toolId
 * @return array
 */

function get_module_db_dependance($toolId)
{
    // actual place of this info prom module

    $dbconfFile = get_module_path($toolId) . '/connector/db.conf.php';
    if (file_exists($dbconfFile))
    {
        $contextDbSupport =false;
        include($dbconfFile);
        return $contextDbSupport;
    }
    else
    switch ($toolId)
    {
        // read in manifest

        //case 'CLUNFO' : return array(CLARO_CONTEXT_COURSE,CLARO_CONTEXT_GROUP);
        case 'CLANN'  : return array(CLARO_CONTEXT_COURSE,CLARO_CONTEXT_GROUP);
        case 'CLWIKI' : return array(CLARO_CONTEXT_COURSE,CLARO_CONTEXT_GROUP);
        case 'CLQWZ'  : return array(CLARO_CONTEXT_COURSE,CLARO_CONTEXT_GROUP);
        case 'CLDOC'  : return array(CLARO_CONTEXT_COURSE,CLARO_CONTEXT_GROUP);
        case 'CLCAL'  : return array(CLARO_CONTEXT_COURSE,CLARO_CONTEXT_GROUP);

        //case 'CLBLOG' : return array (CLARO_CONTEXT_USER,CLARO_CONTEXT_COURSE);
        case 'CLLNK' :  return array(CLARO_CONTEXT_COURSE);
        case 'CLDSC'  : return array(CLARO_CONTEXT_COURSE);
        case 'CLFRM'  : return array(CLARO_CONTEXT_COURSE);
        case 'CLLNP'  : return array(CLARO_CONTEXT_COURSE);
        case 'CLUSR'  : return array(CLARO_CONTEXT_COURSE);
        case 'CLWRK'  : return array(CLARO_CONTEXT_COURSE);

        default :       return array();
    }
}

/**
 * return the syspath where a tool can store these file for a given context
 *
 * @param unknown_type $context
 */
function claro_get_data_path($contextData=array())
{
    if(is_null($contextData)
        || !array_key_exists(CLARO_CONTEXT_TOOLLABEL,$contextData))
    {
        $contextData[CLARO_CONTEXT_TOOLLABEL] = rtrim($GLOBALS['_courseTool']['label'],'_');
    }
    if(is_null($contextData)
        || !array_key_exists(CLARO_CONTEXT_COURSE,$contextData))
    {
        $contextData[CLARO_CONTEXT_COURSE] = claro_get_current_course_id();
    }
    if(is_null($contextData)
        || !array_key_exists(CLARO_CONTEXT_GROUP,$contextData))
    {
        $contextData[CLARO_CONTEXT_GROUP] = claro_get_current_group_id();
    }
    if(is_null($contextData)
        || !array_key_exists(CLARO_CONTEXT_USER,$contextData))
    {
        $contextData[CLARO_CONTEXT_USER] = claro_get_current_user_id();
    }
    /*if(is_null($contextData)
        || !array_key_exists(CLARO_CONTEXT_TOOLINSTANCE,$contextData))
    {
        $contextData[CLARO_CONTEXT_TOOLINSTANCE] = claro_get_current_tool_id();
    }*/

    if (isset($contextData[CLARO_CONTEXT_COURSE]))
    {
        if (isset($contextData[CLARO_CONTEXT_GROUP]))
        {
            $path = claro_get_group_data($contextData[CLARO_CONTEXT_GROUP]
                ,$contextData[CLARO_CONTEXT_COURSE]);
        }
        else
        {
            $path = get_conf('coursesRepositorySys')
                . claro_get_course_path($contextData[CLARO_CONTEXT_COURSE])
                ;
        }
    }

    if (isset($contextData[CLARO_CONTEXT_TOOLLABEL]))
    {
        switch ($contextData[CLARO_CONTEXT_TOOLLABEL])
        {
            case 'CLDOC' : $path = $path . '/document/';        break;
            case 'CLCHT' : $path = $path . '/chat/';            break;
            case 'CLWRK' : $path = $path . '/work/';            break;
            case 'CLQWZ' : $path = $path . '/exercise/';        break;
            case 'CLLNP' : $path = $path . '/scormPackages/';    break;
            default : $path = $path . $contextData[CLARO_CONTEXT_TOOLLABEL] . '/';

        }
    }

    return $path;

}

function get_module_entry($claroLabel)
{
    $moduleData = get_module_data($claroLabel);
    return $moduleData['entry'];

}


/**
 * return the complete path to the entry of an module.
 *
 * @param string $claroLabel
 * @return string
 */
function get_module_entry_url($claroLabel)
{
    $moduleData = get_module_data($claroLabel);
    return get_module_url($claroLabel) . '/' . ltrim($moduleData['entry'],'/');
}

function get_module_data($claroLabel, $ignoreCache=false)
{
    static $cachedModuleDataList = null;
    if ($ignoreCache || is_null($cachedModuleDataList) || ! array_key_exists($claroLabel,$cachedModuleDataList))
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
                   CT.`add_in_course` AS add_in_course

        FROM `" . $tbl['module'] . "` AS M
        LEFT JOIN `" . $tbl['course_tool'] . "` AS CT
            ON CT.`claro_label`= M.label
        WHERE  M.`label` = '" . addslashes($claroLabel) . "'";
        $cachedModuleDataList[$claroLabel] = claro_sql_query_get_single_row($sql);
    }
    return $cachedModuleDataList[$claroLabel];
}

/**
 * Check if a module is installed and actived.
 *
 * @param string $modLabel
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
 * Merge module lang with lang file
 *
 * @param $moduleLabel label of module
 * @return array
 */
function add_module_lang_array($moduleLabel)
{
    global $_lang;

    $module_uri = get_path('rootSys').'module/'.$moduleLabel.'/';

    $current_lang = language::current_language();

    if ($current_lang != 'english' && file_exists($module_uri.'lang/lang_'.$current_lang.'.php'))
    {
        /* TODO use $_lang instead of $mod_lang in module lang files */
        $mod_lang = array();
        include $module_uri.'lang/lang_'.$current_lang.'.php';
        $_lang = array_merge($_lang,$mod_lang);
    }
    elseif (file_exists($module_uri.'lang/lang_english.php'))
    {
        /* TODO use $_lang instead of $mod_lang in module lang files */
        $mod_lang = array();
        include $module_uri.'lang/lang_english.php';
        $_lang = array_merge($_lang,$mod_lang);
    }
}

/**
 * Get the list of all modules on the platform
 * @param   bool activeModulesOnly selects only active module (default true)
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
    
    $sql = "SELECT `label` \n"
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
            $moduleLabelList[] = $module['label'];
        }
        
        return $moduleLabelList;
    }
}

/**
 * Module (un)installation functions
 */

require_once dirname(__FILE__) . '/sqlxtra.lib.php';
require_once dirname(__FILE__) . '/backlog.class.php';

/**
 * Install database for the given module in the given course
 * @param   string moduleLabel
 * @param   string courseId
 * @return  boolean
 * @author  Frederic Minne <zefredz@claroline.net>
 */
function install_module_in_course( $moduleLabel, $courseId )
{
    $sqlPath = get_module_path( $moduleLabel ) . '/setup/course_install.sql';
    $phpPath = get_module_path( $moduleLabel ) . '/setup/course_install.php';

    if ( file_exists( $sqlPath ) )
    {
        if ( ! execute_sql_file_in_course( $sqlPath, $courseId ) )
        {
            return false;
        }
    }

    if ( file_exists( $phpPath ) )
    {
        require_once $phpPath;
    }
}

/**
 * Remove database for all modules in the given course
 * @param   string courseId
 * @return  array(
 *  boolean success
 *  Backlog log )
 * @author  Frederic Minne <zefredz@claroline.net>
 */
function delete_all_modules_from_course( $courseId )
{
    $backlog = new Backlog;
    $success = true;
    
    if ( ! $moduleLabelList = get_module_label_list(false) )
    {
        $success = false;
        $backlog->failure( claro_failure::get_last_failure() );
    }
    else
    {
        foreach ( $moduleLabelList as $moduleLabel )
        {
            if ( ! delete_module_in_course( $moduleLabel, $courseId ) )
            {
                $backlog->failure( get_lang('delete failed for module %module'
                    , array( '%module' => $moduleLabel ) ) );
                
                $success = false;
            }
            else
            {
                $backlog->success( get_lang('delete succeeded for module %module'
                    , array( '%module' => $moduleLabel ) ) );
            }
        }
    }
    
    return array( $success, $backlog );
}

/**
 * Remove database for the given module in the given course
 * @param   string moduleLabel
 * @param   string courseId
 * @return  boolean
 * @author  Frederic Minne <zefredz@claroline.net>
 */
function delete_module_in_course( $moduleLabel, $courseId )
{
    $sqlPath = get_module_path( $moduleLabel ) . '/setup/course_uninstall.sql';
    $phpPath = get_module_path( $moduleLabel ) . '/setup/course_uninstall.php';
    
    if ( file_exists( $phpPath ) )
    {
        require_once $phpPath;
    }
    
    if ( file_exists( $sqlPath ) )
    {
        if ( ! execute_sql_file_in_course( $sqlPath, $courseId ) )
        {
            return false;
        }
    }
    
    return true;
}

/**
 * Execute course related SQL files by replacing __CL__COURSE__ place holder
 * with given course code, then executing the file
 * @param   string file path to the sql file
 * @param   string courseId course sys code
 * @return  boolean
 * @author  Frederic Minne <zefredz@claroline.net>
 * @throws  SQL_FILE_NOT_FOUND, SQL_QUERY_FAILED
 */
function execute_sql_file_in_course( $file, $courseId )
{
    if ( file_exists( $file ) )
    {
        $sql = file_get_contents( $file );

        if ( !empty( $courseId ) )
        {
            $currentCourseDbNameGlu = claro_get_course_db_name_glued( $courseId );
            $sql = str_replace('__CL_COURSE__', $currentCourseDbNameGlu, $sql );
        }
        
        if ( ! claro_sql_multi_query($sql) )
        {
            return claro_failure::set_failure( 'SQL_QUERY_FAILED' );
        }
        else
        {
            return true;
        }
    }
    else
    {
        return claro_failure::set_failure( 'SQL_FILE_NOT_FOUND' );
    }
}

// ---- Database table list helpers

/**
 * Get list of module table names 'localized' for the given course
 * @param array $arrTblName of tableName
 * @param string $courseCode course code
 * @return array $tableName => $dbNameGlue . $tableName
 */
function get_module_course_tbl( $arrTblName, $courseCode )
{
    $currentCourseDbNameGlu = claro_get_course_db_name_glued( $courseCode );
    $arrToReturn = array();

    foreach ( $arrTblName as $name )
    {
        $arrToReturn[$name] = $currentCourseDbNameGlu . $name;
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
    $mainDbNameGlu = get_conf('mainDbName') . '`.`' . get_conf('mainTblPrefix');
    $arrToReturn = array();

    foreach ( $arrTblName as $name )
    {
        $arrToReturn[$name] = $mainDbNameGlu . $name;
    }

    return $arrToReturn;
}

function load_module_language ( $module = null )
{
    global $_lang ;

    if ( is_null ( $module ) )
    {
        $module = get_current_module_label();
    }

    if ( ! empty ( $module ) )
    {
        $moduleLangPath = get_module_path( $module )
            . '/lang/lang_'.language::current_language()
            . '.php'
            ;

        if ( file_exists ( $moduleLangPath ) )
        {
            if ( claro_debug_mode() )
            {
                pushClaroMessage(__FUNCTION__."::".$module.'::'
                    . language::current_language().' loaded', 'debug');
            }

            include $moduleLangPath;
            
            return true;
        }
        else
        {
            if ( claro_debug_mode() )
            {
                pushClaroMessage(__FUNCTION__."::".$module.'::'
                    . language::current_language().' not found', 'debug');
            }
            
            return false;
        }
    }
    else
    {
        return false;
    }
}

?>