<?php // $Id$

/**
 * get the list of aivailable  for a module
 *
 * @param string $context
 * @return array
 */
function get_module_list($context)
{
    $moduleList =array();

    if('course' == $context)
    {

        $tbl_mdb_names = claro_sql_get_main_tbl();
        $sql ="
               SELECT claro_label,
                      icon,
                      access_manager,
                      add_in_course
               FROM `" . $tbl_mdb_names['tool'] . "` ";
        //            WHERE context course
        $moduleList = claro_sql_query_fetch_all($sql);
    }
    elseif('group' == $context)
    {
        $moduleList = array();

        $moduleList[] = array('claro_label' => 'CLWIKI', 'add_in_group' => 'AUTOMATIC');
        $moduleList[] = array('claro_label' => 'CLFRM',  'add_in_group' => 'AUTOMATIC');
        $moduleList[] = array('claro_label' => 'CLDOC',  'add_in_group' => 'AUTOMATIC');
        $moduleList[] = array('claro_label' => 'CLCHT',  'add_in_group' => 'AUTOMATIC');
    }

    return $moduleList;
}

function get_module_data($claro_label)
{
    $tbl_mdb_names = claro_sql_get_main_tbl();
    $sql ="
           SELECT id,
                  claro_label,
                  icon,
                  access_manager,
                  add_in_course,
                  def_rank,
                  def_access
           FROM `" . $tbl_mdb_names['tool'] . "`
           WHERE claro_label = '" . addslashes($claro_label) . "'";

    return claro_sql_query_get_single_row($sql);
}


/**
 * install tool in a course
 *
 * @return datatype description
 *
 * @author Christophe Gesché <moosh@claroline.net>
 */

function claro_install_module($tool_label, $context, $contextData)
{

    global $includePath;
    $libPath =  get_module_path($tool_label) . '/lib/claroline.lib.php';

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
    return false;

}

/**
 *
 * @param claro_label $tool_label label of tool to activate.
 * @return
 * @author Christophe Gesché <moosh@claroline.net>
 *
 */
function claro_enable_module($claro_label, $context, $contextId)
{
    if ('course'==$context)
    {
        $tbl_cdb_names   = claro_sql_get_course_tbl(claro_get_course_db_name_glued($contextId));
        $moduleDataList = get_module_data($claro_label);
        $sql_insert = "
                        INSERT INTO `" . $tbl_cdb_names['tool'] . "`
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
        case 'CLQWZ' : return get_conf('clarolineRepositorySys') . 'exercice';
        case 'CLWRK' : return get_conf('clarolineRepositorySys') . 'work';
        case 'CLWIKI' : return get_conf('clarolineRepositorySys') . 'wiki';
        case 'CLGRP' : return '';
        default: trigger_error( $toolLabel . ' is not know by get_tool_path',E_USER_WARNING);
    }
    return '';

}



?>