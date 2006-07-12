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
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
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
 * Install tool in a course
 *
 * @return datatype description
 *
 * @author Christophe Gesché <moosh@claroline.net>
 */

function claro_install_module($tool_label, $context, $contextData)
{
    $libPath =  get_module_path($tool_label) . '/connector/setup.cnr.php';

    if(file_exists($libPath))
    {
        include_once($libPath );

        if (class_exists($tool_label))
        {
            $thisTool = new $tool_label;
            if(method_exists($thisTool,'aivailable_context_tool'))
            {
                if (in_array($context,$thisTool->aivailable_context_tool()))
                {
                    if(method_exists($thisTool,'install_tool'))
                    {
                        $thisTool->install_tool($context,$contextData);
                    }
                    if(method_exists($thisTool,'enable_tool'))
                    {
                        $thisTool->enable_tool($context,$contextData);
                    }
                }
            }
        }
        else
        {
            $claro_context_check_function = $tool_label . '_aivailable_context_tool';
            if(function_exists($claro_context_check_function))
            {
                if (in_array($context,call_user_func($claro_context_check_function)))
                {
                    $claro_install_function = $tool_label . '_install_tool';
                    if(function_exists($claro_install_function))
                    {
                        call_user_func($claro_install_function,$context,$contextData);
                    }
                    $claro_enable_function = $tool_label . '_enable_tool';
                    if(function_exists($claro_enable_function))
                    {
                        call_user_func($claro_enable_function,$context,$contextData);
                    }
                }
            }
        }
    }
    return true;

}

/**
 *
 * @param claro_label $tool_label label of tool to activate.
 * @return id of instance of the module tool in the context
 * @throws claro_failure : string
 * @author Christophe Gesché <moosh@claroline.net>
 *
 */
function claro_enable_module($claroLabel, $context, $contextId)
{
    if (CLARO_CONTEXT_COURSE == $context)
    {
        $tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($contextId));
        $moduleDataList = get_module_data($claroLabel);
        $sql_insert = "
                        INSERT INTO `" . $tbl['tool'] . "`
                        SET tool_id = '" . $moduleDataList['id'] . "',
                            rank    = '" . $moduleDataList['def_rank'] . "',
                            access  = '" . $moduleDataList['def_access'] . "'";

        return claro_sql_query_insert_id($sql_insert);
    }
    else
    trigger_error('claro_enable_module support only course context',E_USER_NOTICE);
}


/**
 *
 * @param $tool_id integer id of tool to activate.
 * @return
 * @author Christophe Gesché <moosh@claroline.net>
 *
 */
function claro_disable_module($tool_id, $course_id)
{
    $tbl_cdb_names   = claro_sql_get_course_tbl(claro_get_course_db_name_glued($course_id));
    $sql = " DELETE FROM `" . $tbl_cdb_names['tool'] . "` "
         . " WHERE tool_id = '" . (int) $tool_id . "'";
    return claro_sql_query($sql);
}



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
        case 'CLANN' : return get_conf('clarolineRepositorySys') . 'announcements';
        case 'CLCAL' : return get_conf('clarolineRepositorySys') . 'calendar';
        case 'CLFRM' : return get_conf('clarolineRepositorySys') . 'phpbb';
        case 'CLCHT' : return get_conf('clarolineRepositorySys') . 'chat';
        case 'CLDOC' : return get_conf('clarolineRepositorySys') . 'document';
        case 'CLDSC' : return get_conf('clarolineRepositorySys') . 'course_description';
        case 'CLUSR' : return get_conf('clarolineRepositorySys') . 'user';
        case 'CLLNP' : return get_conf('clarolineRepositorySys') . 'learnPath';
        case 'CLQWZ' : return get_conf('clarolineRepositorySys') . 'exercise';
        case 'CLWRK' : return get_conf('clarolineRepositorySys') . 'work';
        case 'CLWIKI' : return get_conf('clarolineRepositorySys') . 'wiki';
        case 'CLLNK' : return get_conf('clarolineRepositorySys') . 'linker';
        case 'CLGRP' : return get_conf('clarolineRepositorySys') . 'group';
        case 'CLSTAT' : return get_conf('clarolineRepositorySys') . 'tracking';
        default: return get_conf('rootSys') . 'module/' . rtrim($toolLabel,'_');
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
        case 'CLANN' : return get_conf('clarolineRepositoryWeb') . 'announcements';
        case 'CLCAL' : return get_conf('clarolineRepositoryWeb') . 'calendar';
        case 'CLFRM' : return get_conf('clarolineRepositoryWeb') . 'phpbb';
        case 'CLCHT' : return get_conf('clarolineRepositoryWeb') . 'chat';
        case 'CLDOC' : return get_conf('clarolineRepositoryWeb') . 'document';
        case 'CLDSC' : return get_conf('clarolineRepositoryWeb') . 'course_description';
        case 'CLUSR' : return get_conf('clarolineRepositoryWeb') . 'user';
        case 'CLLNP' : return get_conf('clarolineRepositoryWeb') . 'learnPath';
        case 'CLQWZ' : return get_conf('clarolineRepositoryWeb') . 'exercise';
        case 'CLWRK' : return get_conf('clarolineRepositoryWeb') . 'work';
        case 'CLLNK' : return get_conf('clarolineRepositoryWeb') . 'linker';
        case 'CLWIKI' : return get_conf('clarolineRepositoryWeb') . 'wiki';
        case 'CLGRP' : return get_conf('clarolineRepositoryWeb') . 'group';
        default: return get_conf('rootWeb') . 'module/' . $toolLabel;
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

        case 'CLUNFO' : return array(CLARO_CONTEXT_COURSE,CLARO_CONTEXT_GROUP);
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
    if(is_null($contextData) || !array_key_exists(CLARO_CONTEXT_TOOLLABEL,$contextData))    $contextData[CLARO_CONTEXT_TOOLLABEL]    = rtrim($GLOBALS['_courseTool']['label'],'_');
    if(is_null($contextData) || !array_key_exists(CLARO_CONTEXT_COURSE,$contextData))       $contextData[CLARO_CONTEXT_COURSE]       = get_init('_cid');
    if(is_null($contextData) || !array_key_exists(CLARO_CONTEXT_GROUP,$contextData))        $contextData[CLARO_CONTEXT_GROUP]        = get_init('_gid');
    if(is_null($contextData) || !array_key_exists(CLARO_CONTEXT_USER,$contextData))         $contextData[CLARO_CONTEXT_USER]         = get_init('_uid');
    if(is_null($contextData) || !array_key_exists(CLARO_CONTEXT_TOOLINSTANCE,$contextData)) $contextData[CLARO_CONTEXT_TOOLINSTANCE] = get_init('_tid');

    if (isset($contextData[CLARO_CONTEXT_COURSE]))
    {
        if (isset($contextData[CLARO_CONTEXT_GROUP]))
        {
            $path = claro_get_group_data($contextData[CLARO_CONTEXT_GROUP],$contextData[CLARO_CONTEXT_COURSE]);
        }
        else
        {
            $path = get_conf('coursesRepositorySys') . claro_get_course_path($contextData[CLARO_CONTEXT_COURSE]);
        }
    }

    if (isset($contextData[CLARO_CONTEXT_TOOLLABEL]))
    {
        switch ($contextData[CLARO_CONTEXT_TOOLLABEL])
        {
            case 'CLDOC' : $path = $path . '/document/';		break;
            case 'CLCHT' : $path = $path . '/chat/';			break;
            case 'CLWRK' : $path = $path . '/work/';			break;
            case 'CLQWZ' : $path = $path . '/exercise/';		break;
            case 'CLLNP' : $path = $path . '/scormPackages/';	break;
            default : $path = $path . $contextData[CLARO_CONTEXT_TOOLLABEL] . '/';

        }
    }

    return $path;

}


/**
 * return the directory of a config file for a given configCode.
 *
 * @param string $configCode
 * @return unknown
 */
// TODO : rewrite this code :

function claro_get_conf_dir($configCode)
{
    if (CLARO_DEBUG_MODE)
    {
        pushClaroMessage('claro_get_conf_dir still called in ' );
        $dbgBtList = debug_backtrace();
        foreach ($dbgBtList as $dbgBt)
        foreach ($dbgBt as $infoName => $infoContent)
        pushClaroMessage('<b>'.$infoName . '</b>: '. var_export($infoContent,1));
    }
    return claro_get_conf_repository();

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
    return get_module_url($claroLabel) . '/' . $moduleData['entry'];
}

function get_module_data($claroLabel, $ignoreCache=false)
{
    static $cachedModuleDataList = null;
    if ($ignoreCache || is_null($cachedModuleDataList) || ! array_key_exists($claroLabel,$cachedModuleDataList))
    {
        $tbl = claro_sql_get_tbl(array('module', 'course_tool'));
        $sql = "SELECT M.`label`  AS label,
                   M.`id`         AS id,
                   M.`name`       AS moduleName,
                   M.`activation` AS activation,
                   M.`type`       AS type,
                   M.`script_url` AS entry,
                   CT.`icon`      AS icon

        FROM `" . $tbl['module'] . "` AS M
        LEFT JOIN `" . $tbl['course_tool'] . "` AS CT
            ON CT.`claro_label`= M.label
        WHERE  M.`label` = '" . addslashes($claroLabel) . "'";
        $cachedModuleDataList[$claroLabel] = claro_sql_query_get_single_row($sql);
    }
    return $cachedModuleDataList[$claroLabel];
}

?>