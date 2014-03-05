<?php // $Id$

/**
 * Claroline extension modules setup functions
 *
 * @version     1.12 $Revision$
 * @copyright   (c) 2001-2014, Universite catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html
 *      GNU GENERAL PUBLIC LICENSE version 2 or later
 * @author      Claro Team <cvs@claroline.net>
 * @package     kernel.module
 * @since       1.12
 */

// MULTIPLE SQL QUERY LIBRARY
require_once __DIR__ . '/../sqlxtra.lib.php';
// MODULE MANAGEMENT LIBRARIES
require_once __DIR__ . '/manage.lib.php';

/**
 * Get the list of modules that cannot be uninstalled
 * @return array
 */
function get_not_uninstallable_tool_list()
{
    return array(
        'CLANN',
        'CLCAL',
        'CLFRM',
        'CLCHT',
        'CLDOC',
        'CLDSC',
        'CLUSR',
        'CLLNP',
        'CLQWZ',
        'CLWRK',
        'CLWIKI',
        'CLLNK',
        'CLGRP'
    );
}

/**
 * Install a specific module to the platform
 * @param string $modulePath path to the module
 * @param bool $skipCheckDir skip checking if module directory already exists (default false)
 * @return array( backlog, int )
 *      backlog object containing the messages
 *      int moduleId if the install process suceeded, false otherwise
 * @todo remove the need of the Backlog and use Exceptions instead
 */
function install_module($modulePath, $skipCheckDir = false, $registerModuleInCourses = false )
{
    $backlog = new Backlog;
    $moduleId = false;

    if (false === ($module_info = readModuleManifest($modulePath)))
    {
        claro_delete_file($modulePath);
        $backlog->failure( claro_failure::get_last_failure() );
    }
    else
    {
        //check if a module with the same LABEL is already installed, if yes, we cancel everything
        // TODO extract from install function should be tested BEFORE calling install_module
        if ( (!$skipCheckDir) && check_name_exist(get_module_path($module_info['LABEL']) . '/'))
        {
            $backlog->failure( get_lang('Module %module is already installed on your platform'
                , array('%module'=>$module_info['LABEL'])));
            // claro_delete_file($modulePath);
            // TODO : add code to point on existing instance of tool.
            // TODO : how to overwrite . prupose uninstall ?
        }
        else
        {
            //3- Save the module information into DB
            if ( false === ( $moduleId = register_module_core($module_info) ) )
            {
                claro_delete_file($modulePath);
                $backlog->failure(claro_failure::get_last_failure());
                $backlog->failure( get_lang('Module registration failed') );
            }
            else
            {
                //in case of tool type module, the dock can not be selected and must added also now

                if ('tool' == $module_info['TYPE'])
                {
                    // TODO FIXME handle failure
                    register_module_tool($moduleId,$module_info);
                }

                if (array_key_exists('DEFAULT_DOCK',$module_info))
                {
                    foreach($module_info['DEFAULT_DOCK'] as $dock)
                    {
                        // TODO FIXME handle failure
                        add_module_in_dock($moduleId, $dock);
                    }
                }

                //4- Rename the module repository with label
                $currentPlace = realpath($modulePath) . '/';
                $destPath = get_module_path( $module_info['LABEL'] );
                claro_mkdir(get_path('rootSys') . 'module/', CLARO_FILE_PERMISSIONS, true);
                if (!@rename( $currentPlace , $destPath ))
                {
                   $backlog->failure(get_lang("Error while renaming module folder").
                   ' from:' . $currentPlace  .
                   ' to:' . $destPath
                   );
                }
                else
                {
                    // force access rights on module root dir after rename() because some modules written on M$ Win$#!t are causing issues
                    chmod( $destPath,CLARO_FILE_PERMISSIONS );
                    
                    //5-Include the local 'install.sql' and 'install.php' file of the module if they exist
                    if ( isset( $installSqlScript ) ) unset ( $installSqlScript );
                    $installSqlScript = get_module_path( $module_info['LABEL'] ) . '/setup/install.sql';

                    if (file_exists( $installSqlScript ) )
                    {
                        $sql = file_get_contents( $installSqlScript );

                        if (!empty($sql))
                        {
                            $sql = str_replace ('__CL_MAIN__',get_conf('mainTblPrefix'), $sql);

                            if ( claro_sql_multi_query($sql) === false )
                            {
                                $backlog->failure(get_lang( 'Sql installation query failed' ));
                            }
                            else
                            {
                                $backlog->failure(get_lang( 'Sql installation query succeeded' ));
                            }
                        }
                    }
                    
                    // generate the conf if a def file exists
                    if ( file_exists( get_module_path($module_info['LABEL'])
                        . '/conf/def/'.$module_info['LABEL'].'.def.conf.inc.php' ) )
                    {
                        require_once __DIR__ . '/../config.lib.inc.php';
                        $config = new Config($module_info['LABEL']);
                        list ($confMessage, $status ) = generate_conf($config);

                        $backlog->info($confMessage);
                    }

                    // call install.php after initialising database in case it requires database to run
                    if ( isset( $installPhpScript ) ) unset ( $installPhpScript );
                    $installPhpScript = get_module_path($module_info['LABEL']) . '/setup/install.php';

                    if (file_exists($installPhpScript))
                    {
                        language::load_translation( );
                        language::load_locale_settings( );
                        language::load_module_translation( $module_info['LABEL'] );
                        load_module_config( $module_info['LABEL'] );
                        
                        // FIXME this is very dangerous !!!!
                        require $installPhpScript;
                        $backlog->info(get_lang( 'Module installation script called' ));
                    }

                    $moduleInfo =  get_module_info($moduleId);

                    if (($registerModuleInCourses && $moduleInfo['type'] =='tool') && $moduleId)
                    {
                        list ( $backlog2, $success2 ) = register_module_in_courses( $moduleId );

                        if ( $success2 )
                        {
                            $backlog->success( get_lang('Module installed in all courses') );
                        }
                        else
                        {
                            $backlog->append( $backlog2 );
                        }
                    }

                    //6- cache file with the module's include must be renewed after installation of the module

                    if ( ! generate_module_cache() )
                    {
                        $backlog->failure(get_lang( 'Module cache update failed' ));
                    }
                    else
                    {
                        $backlog->success(get_lang( 'Module cache update succeeded' ));
                    }
                }
            }
        }
    }

    return array( $backlog, $moduleId );
}

/**
 * Uninstall a specific module to the platform
 *
 * @param integer $moduleId the id of the module to uninstall
 * @return array( backlog, boolean )
 *      backlog object
 *      boolean true if the uninstall process suceeded, false otherwise
 * @todo remove the need of the Backlog and use Exceptions instead
 */
function uninstall_module($moduleId, $deleteModuleData = true)
{
    $success = true;
    $backlog = new Backlog;

    //first thing to do : deactivate the module

    // deactivate_module($moduleId);
    $moduleInfo =  get_module_info($moduleId);
    if ( ($moduleInfo['type'] =='tool') && $moduleId )
    {

        // 2- delete the module in the cours_tool table, used for every course creation

        list ( $backlog2, $success2 ) = unregister_module_from_courses( $moduleId );

        if ( $success2 )
        {
            $backlog->success( get_lang('Module uninstalled in all courses') );
        }
        else
        {
            $backlog->append( $backlog2 );
        }
    }

    //Needed tables and vars

    $tbl = claro_sql_get_main_tbl();

    $backlog = new Backlog;

    // 0- find info about the module to uninstall

    $sql = "SELECT `label`
              FROM `" . $tbl['module'] . "`
             WHERE `id` = " . (int) $moduleId;

    $module = claro_sql_query_get_single_row($sql);

    if ( $module == false )
    {
        $backlog->failure(get_lang("No module to uninstall"));
        $success = false;
    }
    else
    {
        // 1- Include the local 'uninstall.sql' and 'uninstall.php' file of the module if they exist

        // call uninstall.php first in case it requires module database schema to run
        if ( isset( $uninstallPhpScript ) ) unset ( $uninstallPhpScript );
        $uninstallPhpScript = get_module_path($module['label']) . '/setup/uninstall.php';
        if (file_exists( $uninstallPhpScript ))
        {
            language::load_translation( );
            language::load_locale_settings( );
            language::load_module_translation( $module['label'] );
            load_module_config( $module['label'] );
            
            require $uninstallPhpScript;
            
            $backlog->info( get_lang('Module uninstallation script called') );
        }

        if ( isset( $uninstallSqlScript ) ) unset ( $uninstallSqlScript );
        $uninstallSqlScript = get_module_path($module['label']) . '/setup/uninstall.sql';
        
        if ($deleteModuleData && file_exists( $uninstallSqlScript ))
        {
            $sql = file_get_contents( $uninstallSqlScript );
            if (!empty($sql))
            {
                $sql = str_replace ('__CL_MAIN__',get_conf('mainTblPrefix'), $sql);

                if ( false !== claro_sql_multi_query($sql) )
                {
                    $backlog->success(get_lang( 'Database uninstallation succeeded' ));
                }
                else
                {
                    $backlog->failure(get_lang( 'Database uninstallation failed' ));
                    $success = false;
                }
            }
        }
        elseif ( ! $deleteModuleData && file_exists( $uninstallSqlScript ) )
        {
            $backlog->info(get_lang( 'Database uninstallation skipped' ));
        }

        // 2- delete related files and folders

        $modulePath = get_module_path($module['label']);

        if ( file_exists($modulePath) )
        {
            if(claro_delete_file($modulePath))
            {
                $backlog->success( get_lang('Delete scripts of the module') );
            }
            else
            {
                $backlog->failure( get_lang('Error while deleting the scripts of the module') );
                $success = false;
            }
        }

        //  delete the module in the cours_tool table, used for every course creation

        //retrieve this module_id first

        $sql = "SELECT id as tool_id FROM `" . $tbl['tool']."`
                WHERE claro_label = '".$module['label']."'";
        $tool_to_delete = claro_sql_query_get_single_row($sql);
        $tool_id = $tool_to_delete['tool_id'];


        $sql = "DELETE FROM `" . $tbl['tool']."`
                WHERE claro_label = '".$module['label']."'
            ";

        claro_sql_query($sql);

        // 3- delete related entries in main DB

        $sql = "DELETE FROM `" . $tbl['module'] . "`
                WHERE `id` = ". (int) $moduleId;
        claro_sql_query($sql);

        $sql = "DELETE FROM `" . $tbl['module_info'] . "`
                WHERE `module_id` = " . (int) $moduleId;
        claro_sql_query($sql);
        
        $sql = "DELETE FROM `" . $tbl['module_contexts'] . "`
                WHERE `module_id` = " . (int) $moduleId;
        claro_sql_query($sql);

        // 4-Manage right - Delete read action
        $action = new RightToolAction();
        $action->setName('read');
        $action->setToolId($tool_id);
        $action->delete();

        // Manage right - Delete edit action
        $action = new RightToolAction();
        $action->setName('edit');
        $action->setToolId($tool_id);
        $action->delete();

        // 5- remove all docks entries in which the module displays
        // TODO FIXME handle failure
        remove_module_dock($moduleId, 'ALL');

        // 6- cache file with the module's include must be renewed after uninstallation of the module

        if ( ! generate_module_cache() )
        {
            $backlog->failure(get_lang( 'Module cache update failed' ));
            $success = false;
        }
        else
        {
            $backlog->success(get_lang( 'Module cache update succeeded' ));
        }
    }

    return array( $backlog, $success );

}

/**
 * Install database for the given module in the given course
 * @param   string moduleLabel
 * @param   string courseId
 * @return  boolean
 * @author  Frederic Minne <zefredz@claroline.net>
 */
function install_module_in_course( $moduleLabel, $courseId )
{
    install_module_database_in_course( $moduleLabel, $courseId );

    install_module_script_in_course( $moduleLabel, $courseId );
}

/**
 * Create and initialize the module database in the given course
 * @param string $moduleLabel
 * @param string $courseId
 * @todo what to return if the script file does not exists ?!?
 * @return boolean
 */
function install_module_database_in_course( $moduleLabel, $courseId )
{
    $sqlPath = get_module_path( $moduleLabel ) . '/setup/course_install.sql';

    if ( file_exists( $sqlPath ) )
    {
        if ( ! execute_sql_file_in_course( $sqlPath, $courseId ) )
        {
            return false;
        }
        else
        {
            return true;
        }
    }
}

/**
 * Execute module initialization script in the given course
 * @param string $moduleLabel
 * @param string $courseId
 */
function install_module_script_in_course( $moduleLabel, $courseId )
{
    $phpPath = get_module_path( $moduleLabel ) . '/setup/course_install.php';

    if ( file_exists( $phpPath ) )
    {
        $courseDirectory = claro_get_current_course_data( 'path' );
        $moduleCourseTblList = $courseTbl = claro_sql_get_course_tbl();
        
        // include the language file with all language variables
        language::load_translation( );
        language::load_locale_settings( );
        language::load_module_translation( $moduleLabel );
        load_module_config( $moduleLabel );

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
