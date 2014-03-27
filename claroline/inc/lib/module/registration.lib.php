<?php // $Id$

/**
 * Claroline extension modules (un)registration functions
 *
 * @version     Claroline 1.12 $Revision$
 * @copyright   (c) 2001-2014, Universite catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html
 *      GNU GENERAL PUBLIC LICENSE version 2 or later
 * @author      Claro Team <cvs@claroline.net>
 * @package     kernel.module
 * @since       1.12
 */

/**
 * Add module in claroline, giving  its path
 *
 * @param string $modulePath
 * @return int module id or false
 * @todo remove the need of the Backlog and use Exceptions instead
 */
function register_module($modulePath)
{
    $backlog = new Backlog;
    if (file_exists($modulePath))
    {
        /*$parser = new ModuleManifestParser;
        $module_info = $parser->parse($modulePath.'/manifest.xml');*/
        
        $module_info = readModuleManifest( $modulePath );
        
        if ( false === $module_info )
        {
            $backlog->failure(get_lang( 'Cannot parse module manifest'));
            
            $moduleId = false;
        }
        elseif ( is_array($module_info)
            && false !== ($moduleId = register_module_core($module_info)) )
        {
            $backlog->failure(get_lang('Module %claroLabel registered',
                array('%claroLabel'=>$module_info['LABEL'])));
            
            if('TOOL' == strtoupper($module_info['TYPE']))
            {
                if (false !== ($toolId   = register_module_tool($moduleId,$module_info)))
                {
                    $backlog->failure(get_lang('Module %label registered as tool', array('%claroLabel'=>$module_info['LABEL'])));
            
                }
                else
                {
                    $backlog->failure( get_lang('Cannot register tool %label', array('%label' => $module_info['LABEL'])));
                }
            }
            elseif('APPLET' == strtoupper($module_info['TYPE']))
            {
                if ( array_key_exists('DEFAULT_DOCK',$module_info)
                    && is_array($module_info['DEFAULT_DOCK']) )
                {
                    foreach ( $module_info['DEFAULT_DOCK'] as $dock )
                    {
                        add_module_in_dock($moduleId, $dock);
                            $backlog->failure(get_lang('Module %label added in dock : %dock'
                            , array('%label' => $module_info['LABEL'], '%dock' => $dock)));
                        
                    }
                }
            }
        }
        else
        {
            $backlog->failure(get_lang('Cannot register module %label', array('%label' => $module_info['LABEL'])));
        }
    }
    else
    {
        $backlog->failure(get_lang('Cannot find module'));
    }

    return $moduleId;
}

/**
 * Add common info about a module in main module registry.
 * In Claroline this  info is split in two type of info
 * into two tables :
 * * module  for really use info,
 * * module_info for descriptive info
 *
 * @param array $module_info.
 * @return int moduleId in the registry.
 */
function register_module_core($module_info)
{
    $tbl             = claro_sql_get_tbl(array('module','module_info','tool','module_contexts'));
    $tbl_name        = claro_sql_get_main_tbl();

    $missingElement = array_diff(array('LABEL','NAME','TYPE'),array_keys($module_info));
    if (count($missingElement)>0)
    {
        return claro_failure::set_failure(get_lang('Missing elements in module Manifest : %MissingElements' , array('%MissingElements' => implode(',',$missingElement))));
    }
    
    if (isset($module_info['ENTRY']))
    {
        $script_url = $module_info['ENTRY'];
    }
    else
    {
        $script_url = 'entry.php';
    }

    $sql = "INSERT INTO `" . $tbl['module'] . "`
            SET label      = '" . claro_sql_escape($module_info['LABEL'      ]) . "',
                name       = '" . claro_sql_escape($module_info['NAME']) . "',
                type       = '" . claro_sql_escape($module_info['TYPE']) . "',
                script_url = '" . claro_sql_escape($script_url)."'
                ";
    $moduleId = claro_sql_query_insert_id($sql);

    $sql = "INSERT INTO `" . $tbl['module_info'] . "`
            SET module_id      = " . (int) $moduleId . ",
                version        = '" . claro_sql_escape($module_info['VERSION']) . "',
                author         = '" . claro_sql_escape($module_info['AUTHOR']['NAME']) . "',
                author_email   = '" . claro_sql_escape($module_info['AUTHOR']['EMAIL']) . "',
                author_website = '" . claro_sql_escape($module_info['AUTHOR']['WEB']) . "',
                website        = '" . claro_sql_escape($module_info['WEB']) . "',
                description    = '" . claro_sql_escape($module_info['DESCRIPTION']) . "',
                license        = '" . claro_sql_escape($module_info['LICENSE']) . "'";
    
    claro_sql_query($sql);
    
    foreach ( $module_info['CONTEXTS'] AS $context )
    {
        $sql = "INSERT INTO `{$tbl['module_contexts']}`\n"
            . "SET\n"
            . "  `module_id` = " . (int) $moduleId . ",\n"
            . "  `context` = '" . claro_sql_escape( $context ) . "'"
            ;
            
        claro_sql_query($sql);
    }

    return $moduleId;
}

/**
 * Store all unique info about a tool during install
 * @param integer $moduleId
 * @param array $moduleToolData, data from manifest
 * @return int tool id or false
 */
function register_module_tool($moduleId,$module_info)
{
    $tbl = claro_sql_get_tbl('course_tool');

    if ( is_array($module_info) )
    {
        $icon = array_key_exists('ICON',$module_info)
            ? "'" . claro_sql_escape($module_info['ICON']) . "'"
            : 'NULL'
            ;

        if ( !isset($module_info['ENTRY'])) $module_info['ENTRY'] = 'entry.php';

        // find max rank in the course_tool table

        $sql = "SELECT MAX(def_rank) AS maxrank FROM `" . $tbl['course_tool'] . "`";
        $maxresult = claro_sql_query_get_single_row($sql);

        // insert the new course tool

        $sql = "INSERT INTO `" . $tbl['course_tool'] ."`
                SET
                claro_label = '". claro_sql_escape($module_info['LABEL']) ."',
                script_url = '". claro_sql_escape($module_info['ENTRY']) ."',
                icon = " . $icon . ",
                def_access = 'ALL',
                def_rank = (". (int) $maxresult['maxrank']."+1),
                add_in_course = 'AUTOMATIC',
                access_manager = 'COURSE_ADMIN' ";

        $tool_id = claro_sql_query_insert_id($sql);

        // Init action/right

        // Manage right - Add read action
        $action = new RightToolAction();
        $action->setName('read');
        $action->setToolId($tool_id);
        $action->save();

        // Manage right - Add edit action
        $action = new RightToolAction();
        $action->setName('edit');
        $action->setToolId($tool_id);
        $action->save();

        // Init all profile/right

        $profileList = array_keys(claro_get_all_profile_name_list());

        foreach ( $profileList as $profileId )
        {
            $profile = new RightProfile();
            $profile->load($profileId);
            
            $profileRight = new RightProfileToolRight();
            $profileRight->load($profile);
            
            if ( claro_get_profile_id('manager') == $profileId )
            {
                $profileRight->setToolRight($tool_id,'manager');
            }
            else
            {
                $profileRight->setToolRight($tool_id,'user');
            }
            
            $profileRight->save();
        }

        return $tool_id;
    }
    else
    {
        return false ;
    }
}

/**
 * Register module in all courses
 * @param int $moduleId
 * @return array( backlog, boolean )
 *      backlog object
 *      boolean true if suceeded, false otherwise
 * @todo remove the need of the Backlog and use Exceptions instead
 */
function register_module_in_courses( $moduleId )
{
    $backlog = new Backlog;
    $success = true;
    // TODO : remove fields script_url, claro_label, def_access, access_manager
    // TODO : rename def_rank to rank
    // TODO : secure this code against query failure
    $tbl = claro_sql_get_main_tbl();
    $moduleInfo =  get_module_info($moduleId);

    $tool_id = get_course_tool_id($moduleInfo['label'] );

    // 4- update every course tool list to add the tool if it is a tool

    // $module_type = $moduleInfo['type'];

    $sql = "SELECT `code` FROM `" . $tbl['course'] . "`";

    $course_list = claro_sql_query_fetch_all($sql);

    $default_visibility = false;

    foreach ($course_list as $course)
    {
        if ( false === register_module_in_single_course( $tool_id, $course['code'] ) )
        {
            $success = false;
            $backlog->failure(get_lang( 'Cannot update course database for %course'
                , array( '%course' => $course['code'] )));

            break;
        }
    }

    return array( $backlog, $success );
}

/**
 * Register module in a course
 * @param int $tool_id
 * @param string $course_id
 * @return boolean true if suceeded, false otherwise
 */
function register_module_in_single_course( $tool_id, $course_code )
{
    $currentCourseDbNameGlu = claro_get_course_db_name_glued($course_code);
    $course_tbl = claro_sql_get_course_tbl($currentCourseDbNameGlu);
    $default_visibility = false;

    //find max rank in the tool_list

    $sql = "SELECT MAX(rank) AS maxrank FROM  `" . $course_tbl['tool'] . "`";
    $maxresult = claro_sql_query_get_single_row($sql);
    //insert the tool at the end of the list

    $sql = "INSERT INTO `" . $course_tbl['tool'] . "`
    SET tool_id      = " . $tool_id . ",
        rank         = (" . (int) $maxresult['maxrank'] . "+1),
        visibility   = '" . ( $default_visibility ? 1 : 0 ) . "',
        script_url   = NULL,
        script_name  = NULL,
        addedTool    = 'YES',
        `activated` = 'false',
        `installed` = 'false'";

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
 * Unregister module in all courses
 * @param int $moduleId
 * @return array( backlog, boolean )
 *      backlog object
 *      boolean true if suceeded, false otherwise
 * @todo remove the need of the Backlog and use Exceptions instead
 */
function unregister_module_from_courses( $moduleId )
{
    $backlog = new Backlog;
    $success = true;
    //retrieve this module_id first

    $moduleInfo =  get_module_info($moduleId);
    $tbl = claro_sql_get_main_tbl();

    $sql = "SELECT id AS tool_id
              FROM `" . $tbl['tool']."`
             WHERE claro_label = '".$moduleInfo['label']."'";
    $tool_to_delete = claro_sql_query_get_single_row($sql);
    $tool_id = $tool_to_delete['tool_id'];


    // 3- update every course tool list to add the tool if it is a tool

    $sql = "SELECT `code` FROM `".$tbl['course']."`";
    $course_list = claro_sql_query_fetch_all($sql);


    foreach ($course_list as $course)
    {
        if ( false === unregister_module_from_single_course( $tool_id, $course['code'] ) )
        {
            $success = false;
            $backlog->failure(get_lang( 'Cannot update course database for %course'
                , array( '%course' => $course['code'] )));

            break;
        }
    }

    return array( $backlog, $success );
}

/**
 * Unregister module in a course
 * @param int $tool_id
 * @param string $course_code
 * @return boolean true if suceeded, false otherwise
 */
function unregister_module_from_single_course( $tool_id, $course_code )
{
    $currentCourseDbNameGlu = claro_get_course_db_name_glued($course_code);
    $course_tbl = claro_sql_get_course_tbl($currentCourseDbNameGlu);

    $sql = "DELETE FROM `".$course_tbl['tool']."`
            WHERE  `tool_id` = " . (int)$tool_id;

    if ( false === claro_sql_query($sql) )
    {
        return false;
    }
    else
    {
        return true;
    }
}
