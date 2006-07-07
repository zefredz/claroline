<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );
/**
 * CLAROLINE
 * @version 1.8
 *
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @package ADMIN
 * @since 1.8
 *
 * @author claro team <cvs@claroline.net>
 * @author Guillaume lederer <guillaume@claroline.net>
 */

require_once dirname(__FILE__). '/../../inc/lib/fileManage.lib.php';
require_once dirname(__FILE__). '/../../inc/lib/right/profileToolRight.class.php';

/**
 * Get installed module list, its effect is
 * to return an array containing the installed module's labels
 * @param string $type : type of the module that msu be returned,
 *        if null, then all the modules are returned
 * @return array containing the labels of the modules installed
 *         on the platform
 */

function get_installed_module_list($type = null)
{
    $tbl = claro_sql_get_main_tbl();

    $sql = "SELECT label FROM `" . $tbl['module'] . "`";

    if (isset($type))
    {
        $sql.= " WHERE `type`= '" . addslashes($type) . "'";
    }

    $moduleList = claro_sql_query_fetch_all_cols($sql);
    return  $moduleList['label'];
}

/**
 * Get the list of the repositories found in the module repository
 * where all modules are installed, its effect is
 * returning the expected list
 *
 * @return an array with all the repositories found in the module repository
 * where all modules are installed
 */

function get_module_repositories()
{

    $moduleRepositorySys = get_conf('rootSys') . 'module/';
    $folder_array = array();
    if(file_exists($moduleRepositorySys))
    {
        if (true === ($handle = opendir($moduleRepositorySys)))
        {
            while (false !== ($file = readdir($handle)))
            {
                // skip eventual files found at this place
                if (!is_dir($moduleRepositorySys . $file) ) continue ;

                // skip '.', '..' and 'CVS'
                if ( $file == '.' || $file == '..' || $file == 'CVS' ) continue;

                $folder_array[] = $file;
            }
        }

        closedir($handle);
    }
    return $folder_array;
}

/**
 * Check the presence of unexpected module repositories or unexpected module in DB, its effect is
 * * returning a list of module not installed in DB but present on server, or module installed in DB but not present on server.
 * @return an array two arrays :
 *            ['folder'] containing paths of the suspicious folders found that did not correspond to an installed module in DB
 *            ['DB']     containing label of modules found in DB for which no corresponding folder was found on server
 */

function check_module_repositories()
{
    $mistake_array           = array();
    $mistake_array['folder'] = array();

    $registredModuleList = get_installed_module_list();
    $mistake_array['DB'] = array();
    foreach ($registredModuleList as $registredModule)
    {
        $moduleData = get_module_info($registredModule);
        $moduleRepositorySys = get_conf('rootSys') . 'module/';
        $moduleEntry = realpath(get_module_url($moduleData['label']) . $moduleData['script_url']);

        if(!file_exists($moduleEntry))
        {
            $mistake_array['DB'][] = $registredModule;
        }


    }
    $folders_found = get_module_repositories();



    foreach ($folders_found as $module_folder)
    {
        if (!in_array($module_folder,$registredModuleList))
        {
            $mistake_array['folder'][] = $module_folder;
        }
    }

    return $mistake_array;
}

/**
 * Activate a module, its effect is
 * * to call the activation script of the module (if there is any)
 * * to modify the information in the main DB
 * @param  integer $moduleId : ID of the module that must be activated
 * @return boolean Returns whether the activation succeed, false otherwise
 */

function activate_module($moduleId)
{

    // find module informations

    $tbl = claro_sql_get_main_tbl();
    $module_info =  get_module_info($moduleId);

    // 1- call activation script (if any) from the module repository

    /*TO DO*/

    // 2- change related entry of module table in the main DB

    $sql = "UPDATE `" . $tbl['module']."`
            SET `activation` = 'activated'
            WHERE `id` = " . (int) $moduleId;
    $result = claro_sql_query($sql);

    // 3 - add the module in the cours_tool table, used for every course creation

    if (($module_info['type'] =='tool') && $moduleId)
    {

        // find max rank in the course_tool table

        $sql = "SELECT MAX(def_rank) AS maxrank FROM `" . $tbl['tool'] . "`";
        $maxresult = claro_sql_query_get_single_row($sql);

        // insert the new course tool

        $trimlabel = rtrim($module_info['label'],'_');

        $sql = "INSERT INTO `" . $tbl['tool']."`
                SET
                claro_label = '".$module_info['label']."',
                script_url = '".$module_info['script_url']."',
                icon = '".$module_info['icon']."',
                def_access = 'ALL',
                def_rank = (".(int)$maxresult['maxrank']."+1),
                add_in_course = 'AUTOMATIC',
                access_manager = 'COURSE_ADMIN'
            ";

        $tool_id = claro_sql_query_insert_id($sql);

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

        // load profile
        $profile = new RightProfile();
        $profile->load(claro_get_profile_id('manager'));
        $profileRight = new RightProfileToolRight();
        $profileRight->load($profile);
        $profileRight->setToolRight($tool_id,'manager');
        $profileRight->save();


        // 4- update every course tool list to add the tool if it is a tool

        $module_type = $module_info['type'];

        $sql = "SELECT `code` FROM `" . $tbl['course'] . "`";
        $course_list = claro_sql_query_fetch_all($sql);
        $default_visibility = false ;

        foreach ($course_list as $course)
        {
            $currentCourseDbNameGlu = claro_get_course_db_name_glued($course['code']);
            $course_tbl = claro_sql_get_course_tbl($currentCourseDbNameGlu);

            //find max rank in the tool_list

            $sql = "SELECT MAX(rank) AS maxrank FROM  `" . $course_tbl['tool'] . "`";
            $maxresult = claro_sql_query_get_single_row($sql);
            //insert the tool at the end of the list

            $sql = "INSERT INTO `" . $course_tbl['tool'] . "`
            SET tool_id      = " . $tool_id . ",
                rank         = (" . (int) $maxresult['maxrank'] . "+1),
                visibility   = '" . ($default_visibility?1:0) . "',
                script_url   = NULL,
                script_name  = NULL,
                addedTool    = 'YES'";
            claro_sql_query($sql);
        }
    }

    //5- cache file with the module's include must be renewed after activation of the module

    generate_module_cache();

    return $result;
}

/**
 * Desactivate a module, its effect is
 *   - to call the desactivation script of the module (if there is any)
 *   - to modify the information in the main DB
 * @param  integer $moduleId : ID of the module that must be desactivated
 * @return boolean : Returns whether the desactivation suceeded, false otherwise
 */

function deactivate_module($moduleId)
{
    //find needed info :

    $module_info =  get_module_info($moduleId);
    $tbl = claro_sql_get_main_tbl();

    // 1- call desactivation script (if any) from the module repository

    /*TO DO*/

    if (($module_info['type'] =='tool') && $moduleId)
    {

        // 2- delete the module in the cours_tool table, used for every course creation

        //retrieve this module_id first

        $sql = "SELECT id as tool_id FROM `" . $tbl['tool']."`
                WHERE claro_label = '".$module_info['label']."'";
        $tool_to_delete = claro_sql_query_get_single_row($sql);
        $tool_id = $tool_to_delete['tool_id'];

        $sql = "DELETE FROM `" . $tbl['tool']."`
                WHERE claro_label = '".$module_info['label']."'
            ";

        claro_sql_query($sql);

        // Manage right - Delete read action
        $action = new RightToolAction();
        $action->setName('read');
        $action->setToolId($tool_id);
        $action->delete();

        // Manage right - Delete edit action
        $action = new RightToolAction();
        $action->setName('edit');
        $action->setToolId($tool_id);
        $action->delete();

        // 3- update every course tool list to add the tool if it is a tool

        $sql = "SELECT `code` FROM `".$tbl['course']."`";
        $course_list = claro_sql_query_fetch_all($sql);


        foreach ($course_list as $course)
        {
            $currentCourseDbNameGlu = claro_get_course_db_name_glued($course['code']);
            $course_tbl = claro_sql_get_course_tbl($currentCourseDbNameGlu);

            $sql = "DELETE FROM `".$course_tbl['tool']."`
                    WHERE  `tool_id` = " . (int)$tool_id;
            claro_sql_query($sql);
        }
    }

    //4- change related entry in the main DB, module table

    $tbl = claro_sql_get_main_tbl();

    $sql = "UPDATE `" . $tbl['module'] . "`
            SET `activation` = 'desactivated'
            WHERE `id`= " . (int) $moduleId;

    $result = claro_sql_query($sql);

    //5- cache file with the module's include must be renewed after desactivation of the module

    generate_module_cache();

    return $result;
}

/**
 * Set the dock in which the module displays its content
 *
 * @param integer $moduleId id of the module to rename
 * @param string $newDockName new name  for the doc
 *
 * @return handler result of insert
 */
function add_module_in_dock($moduleId, $newDockName, $context='')
{
    $tbl = claro_sql_get_main_tbl();

    //find info about this module occurence in this dock in the DB

    $sql = "SELECT D.`name`      AS dockname,
                   D.`rank`      AS oldRank
            FROM `" . $tbl['module'] . "` AS M
               , `" . $tbl['dock']   . "` AS D
            WHERE M.`id` = D.`module_id`
            AND M.`id` = " . (int) $moduleId . "
            AND D.`name` = '" . $newDockName . "'";
    $module = claro_sql_query_get_single_row($sql);

    //if the module is already in the dock ,we just do nothing and return true.

    if (isset($module['dockname']) && $module['dockname'] == $newDockName)
    {
        return true;
    }
    else
    {
        //find the highest rank already used in the new dock
        $max_rank = get_max_rank_in_dock($newDockName);
        // the module is not already in this dock, we just insert it into this in the DB

        $sql = "INSERT INTO `" . $tbl['dock'] . "`
                SET module_id = " . (int) $moduleId . ",
                    name    = '" . addslashes($newDockName) . "',
                    #context = '" . addslashes($context) . "',
                    rank    = " . ((int) $max_rank + 1) ;
        $result = claro_sql_query($sql);
        generate_module_cache();

        return $result;
    }
}

/**
 * Remove a module from a dock in which the module displays
 *
 * @param integer $moduleId
 * @param string  $dockName
 *
 */

function remove_module_dock($moduleId, $dockName)
{
    $tbl = claro_sql_get_main_tbl();

    // call of this function to remove ALL occurence of the module in any dock

    if ('ALL' == $dockName)
    {
        //1- find all dock in which the dock displays

        $sql="SELECT `name` AS dockName
              FROM   `" . $tbl['dock'] . "`
              WHERE  `module_id` = " . (int) $moduleId;

        $dockList = claro_sql_query_fetch_all($sql);

        //2- re-call of this function which each dock concerned

        foreach($dockList as $dock)
        {
            remove_module_dock($moduleId,$dock['dockName']);
        }
    }

    else

    //call of this function to remove ONE SPECIFIC occurence of the module in the dock

    {
        //find the rank of the module in this dock :

        $sql = "SELECT `rank` AS oldRank
                FROM   `" . $tbl['dock'] . "`
                WHERE  `module_id` = " . (int) $moduleId . "
                AND    `name` = '" .$dockName . "'";
        $module = claro_sql_query_get_single_row($sql);

        //move up all modules displayed in this dock

        $sql = "UPDATE `" . $tbl['dock'] . "`
                SET `rank` = `rank` - 1
                WHERE `name` = '" . $dockName . "'
                AND `rank` > " . (int) $module['oldRank'];
        claro_sql_query($sql);

        //delete the module line in the dock table

        $sql = "DELETE FROM `" . $tbl['dock'] . "`
                WHERE `module_id` = " . (int) $moduleId. "
                AND   `name` = '" . $dockName . "'";
        claro_sql_query($sql);

        generate_module_cache();
    }
}

/**
 * Move a module inside its dock (change its position in the display
 *
 * @param integer $moduleId
 * @param string $dockName
 * @param string $direction 'up' or 'down'
 */

function move_module_in_dock($moduleId, $dockName, $direction)
{
    $tbl = claro_sql_get_main_tbl();

    switch ($direction)
    {
        case 'up' :

            //1-find value of current module rank in the dock
            $sql = "SELECT `rank`
                    FROM `" . $tbl['dock'] . "`
                    WHERE `module_id`=" . (int) $moduleId . "
                    AND `name`='" . addslashes($dockName) . "'";
            $result=claro_sql_query_get_single_value($sql);

            //2-move down above module
            $sql = "UPDATE `" . $tbl['dock'] . "`
                    SET `rank` = `rank`+1
                    WHERE `module_id` != " . (int) $moduleId . "
                    AND `name`       = '" . addslashes($dockName) . "'
                    AND `rank`       = " . (int) $result['rank'] . " -1 ";

            claro_sql_query($sql);

            //3-move up current module
            $sql = "UPDATE `" . $tbl['dock'] . "`
                    SET `rank` = `rank`-1
                    WHERE `module_id` = " . (int) $moduleId . "
                    AND `name`      = '" .  addslashes($dockName) . "'
                    AND `rank` > 1"; // this last condition is to avoid wrong update due to a page refreshment
            claro_sql_query($sql);

            break;

        case 'down' :

            //1-find value of current module rank in the dock
            $sql = "SELECT `rank`
                    FROM `" . $tbl['dock'] . "`
                    WHERE `module_id`=" . (int) $moduleId . "
                    AND `name`='" . addslashes($dockName) . "'";
            $result=claro_sql_query_get_single_value($sql);

            //this second query is to avoid a page refreshment wrong update

            $sqlmax= "SELECT MAX(`rank`) AS `max_rank`
                      FROM `" . $tbl['dock'] . "`
                      WHERE `name`='" .  addslashes($dockName) . "'";
            $resultmax=claro_sql_query_get_single_value($sqlmax);

            if ($resultmax['max_rank'] == $result['rank']) break;

            //2-move up above module
            $sql = "UPDATE `" . $tbl['dock'] . "`
                    SET `rank` = `rank` - 1
                    WHERE `module_id` != " . $moduleId . "
                    AND `name` = '" . addslashes($dockName) . "'
                    AND `rank` = " . (int) $result['rank'] . " + 1
                    AND `rank` > 1";
            claro_sql_query($sql);

            //3-move down current module
            $sql = "UPDATE `" . $tbl['dock'] . "`
                    SET `rank` = `rank` + 1
                    WHERE `module_id`=" . (int) $moduleId . "
                    AND `name`='" .  addslashes($dockName) . "'";
            claro_sql_query($sql);

            break;
    }

    generate_module_cache();
}


function get_and_unzip_uploaded_package()
{
    $backlog_message = array();

    //Check if the file is valid (not to big and exists)

    if( !isset($_FILES['uploadedModule'])
    || !is_uploaded_file($_FILES['uploadedModule']['tmp_name']))
    {
        $backlog_message[] = get_lang('Problem with file upload');
    }
    else
    {
        $backlog_message[] = get_lang('Temporary file is : ') . $_FILES['uploadedModule']['tmp_name'];
    }
    //1- Unzip folder in a new repository in claroline/module

    include_once (realpath(dirname(__FILE__) . '/../../inc/lib/pclzip/') . '/pclzip.lib.php');
    //unzip files

    $moduleRepositorySys = get_conf('rootSys') . 'module/';
    //create temp dir for upload
    claro_mkdir($moduleRepositorySys, CLARO_FILE_PERMISSIONS, true);
    $uploadDirFullPath = tempdir($moduleRepositorySys);
    $uploadDir         = str_replace($moduleRepositorySys,'',$uploadDirFullPath);
    $modulePath        = $moduleRepositorySys.$uploadDir.'/';

    if ( preg_match('/.zip$/i', $_FILES['uploadedModule']['name']) && treat_uploaded_file($_FILES['uploadedModule'],$moduleRepositorySys, $uploadDir, get_conf('maxFilledSpaceForModule' , 10000000),'unzip',true))
    {
        $backlog_message[] = get_lang('Files dezipped sucessfully in ' ) . $modulePath;

        if (!function_exists('gzopen'))
        {
            $backlog_message[] = get_lang('Error : no zlib extension found');
            claro_delete_file($modulePath);
            return claro_failure::set_failure($backlog_message);
        }
    }
    else
    {
        $backlog_message[] = get_lang('Impossible to unzip file');
        claro_delete_file($modulePath);
        return claro_failure::set_failure($backlog_message);
    }

    return $modulePath;
}

/**
 * function to install a specific module to the platform
 *
 */

function install_module($modulePath)
{
    global $includePath;

    $backlog_message = array();

    if (false === ($module_info = readModuleManifest($modulePath)))
    {
        claro_delete_file($modulePath);
        $backlog_message[] = claro_failure::get_last_failure();
        return $backlog_message;
    }

    //check if a module with the same LABEL is already installed, if yes, we cancel everything

    if (check_name_exist(get_module_path($module_info['LABEL']) . '/'))
    {
        array_push ($backlog_message,get_lang('This module is already installed on your platform '));
        claro_delete_file($modulePath);
        // TODO : add code to point on existing instance of tool.
        // TODO : how to overwrite . prupose uninstall ?
        return $backlog_message;
    }

    //3- Save the module information into DB
    $moduleId = register_module_core($module_info);

    //in case of tool type module, the dock can not be selected and must added also now

    if ('tool' == $module_info['TYPE'])
    {
        $idTool = register_module_tool($moduleId,$module_info);

        if (isset($module_info['CONTEXT']))
        {
            foreach ($module_info['CONTEXT'] as $context => $contextPropertyList)
            {
                register_module_tool_in_context($idTool,$context, $contextPropertyList);
            }
        }
    }

    if (array_key_exists('DEFAULT_DOCK',$module_info))
    {
        foreach($module_info['DEFAULT_DOCK'] as $dock)
        {
            add_module_in_dock($moduleId, $dock);
            array_push ($backlog_message, get_lang('Default dock of the module found and set') . ' : ' . $dock);
        }
    }

    array_push ($backlog_message, get_lang("The information has been saved into the DB"));

    //4- Rename the module repository with label

    if (!rename( $modulePath, get_module_path($module_info['LABEL']) . '/'))
    {
        array_push ($backlog_message, get_lang("Error while renaming the module's folder"));
        return $backlog_message;
    }
    else $backlog_message[] = get_lang('Repository renamed successfully');



    //5-Include the local 'install.sql' and 'install.php' file of the module if they exist

    if (file_exists(get_module_path($module_info['LABEL']) . '/install/install.sql'))
    {
        $sql = file_get_contents(get_module_path($module_info['LABEL']) . '/install/install.sql');
        if (!empty($sql))
        {
            $sql = str_replace ('__CL_MAIN__',get_conf('mainTblPrefix'), $sql);
            claro_sql_multi_query($sql); //multiquery should be assumed here
        }
        array_push ($backlog_message, get_lang('<b>%filename</b> file found and called in the module repository',array('%filename'=>'install.sql')));
    }

    if (file_exists(get_module_path($module_info['LABEL']) . '/install/install.php'))
    {
        require get_module_path($module_info['LABEL']) . '/install/install.php';
        array_push ($backlog_message, get_lang('<b>%filename</b> file found and called in the module repository',array('%filename'=>'install.php')));
    }

    //6- cache file with the module's include must be renewed after installation of the module

    generate_module_cache();

    //7- generate the conf if a def file exists

	require_once $includePath . '/lib/config.lib.inc.php';
	$config = new Config($module_info['LABEL']);
	list ($confMessage, $error) = generate_conf($config);
	$backlog_message = array_merge ($backlog_message,$confMessage);

    //8- return the backlog

    return $backlog_message;
}

/**
 * function to uninstall a specific module to the platform
 *
 * @param integer $moduleId the id of the module to uninstall
 * @return boolean true if the uninstall process suceeded, false otherwise
 *
 */

function uninstall_module($moduleId)
{

    //first thing to do : deactivate the module

    deactivate_module($moduleId);

    //Needed tables and vars

    $tbl = claro_sql_get_main_tbl();

    $backlog_message = array();

    // 0- find info about the module to uninstall

    $sql = "SELECT `label`
            FROM `" . $tbl['module'] . "`
            WHERE `id` = " . (int) $moduleId;

    $module = claro_sql_query_get_single_row($sql);

    if ($module==false) return array(get_lang("No module to uninstall"));

    // 1- Include the local 'uninstall.sql' and 'uninstall.php' file of the module if they exist

    if (file_exists(get_module_path($module['label']) . '/uninstall/uninstall.sql'))
    {
        $sql = file_get_contents(get_module_path($module['label']) . '/uninstall/uninstall.sql');
        if (!empty($sql))
        {
            $sql = str_replace ('__CL_MAIN__',get_conf('mainTblPrefix'), $sql);
            claro_sql_multi_query($sql); //multiquery should be assumed here
        }
        array_push ($backlog_message, get_lang('<b>%filename</b> file found and called in the module repository',array('%filename'=>'uninstall.sql')));
    }

    if (file_exists(get_module_path($module['label']) . '/uninstall/uninstall.php'))
    {
        require get_module_path($module['label']) . '/uninstall/uninstall.php';
        array_push ($backlog_message,get_lang('<b>%filename</b> file found and called in the module repository',array('%filename'=>'uninstall.php')));
    }

    // 2- delete related files and folders

    $modulePath = get_module_path($module['label']);

    if(claro_delete_file($modulePath))
    $backlog_message[] = get_lang('<b>%dirname</b> has been deleted from file system',array('%dirname'=>$module['label']));
    else
    $backlog_message[] = get_lang('Error on deletion of <b>%dirname</b> of file system',array('%dirname'=>$module['label']));

    // 3- delete related entries in main DB

    $sql = "DELETE FROM `" . $tbl['module'] . "`
            WHERE `id` = ". (int) $moduleId;
    claro_sql_query($sql);

    $sql = "DELETE FROM `" . $tbl['module_info'] . "`
            WHERE `module_id` = " . (int) $moduleId;
    claro_sql_query($sql);

    // 4- remove all docks entries in which the module displays

    remove_module_dock($moduleId, 'ALL');

    //5- cache file with the module's include must be renewed after uninstallation of the module

    generate_module_cache();

    return $backlog_message;

}



//---------------------------------------------------------------------------------



/**
 * Function used by the SAX xml parser when the parser meets a opening tag
 *
 * @param tring $dockName the dock from which we want this info
 * @return integer : the max rank used for this dock
 *
 */


function get_max_rank_in_dock($dockName)
{
    $tbl = claro_sql_get_main_tbl();


    $sql = "SELECT MAX(rank) AS mrank
            FROM `" . $tbl['dock'] . "` AS D
            WHERE D . `name` = '" . addslashes($dockName) . "'";
    $max_rank = claro_sql_query_get_single_value($sql);
    return (int) $max_rank;
}

//XML PARSER FUNCTIONS : needed functions for the manifest parser :

/**
 * Function used by the SAX xml parser when the parser meets a opening tag
 *
 * @param handler $parser xml parser created with "xml_parser_create()"
 * @param string $name name of the element
 * @param array  $attributes
 *
 * @global array $module_info array where are add found info
 * @return void
 */
function startElement($parser, $name, $attributes)
{
    global $element_pile;
    global $module_info;

    array_push($element_pile,$name);
    $current_element = end($element_pile);

    switch ($current_element)
    {

        case 'DEFAULT_DOCK' :
            $module_info['DEFAULT_DOCK'][] = $attributes['VALUE'];
            break;

        case 'LINK' :
            $parent = prev($element_pile);
            $module_info['CONTEXT'][$parent]['LINKS'][] = $attributes;
            break;

        case 'COURSE': case 'GROUP': case 'USER':
            $parent = prev($element_pile);
            if ('CONTEXT' == $parent)
            {
                $module_info['CONTEXT'][$current_element] = $attributes;
            }

            break;

    }
}

/**
 * Function used by the SAX xml parser when the parser meets a closing tag
 *
 * @param $parser xml parser created with "xml_parser_create()"
 * @param $name name of the element
 */

function endElement($parser,$name)
{
    global $element_pile;
    array_pop($element_pile);
}

function elementData($parser,$data)
{
    global $element_pile;
    global $module_info;

    $current_element = end($element_pile);

    switch ($current_element)
    {
        case 'TYPE' :
            $module_info['TYPE'] = $data;
            break;

        case 'DESCRIPTION' :
            {
                $module_info['DESCRIPTION'] = $data;
            }   break;

        case 'EMAIL':
            $module_info['AUTHOR']['EMAIL'] = $data;
            break;

        case 'LABEL':
            $module_info['LABEL'] = $data;
            break;

        case 'ENTRY':
            $module_info['ENTRY'] = $data;
            break;

        case 'LICENSE':
            $module_info['LICENSE'] = $data;
            break;

        case 'ICON':
            $module_info['ICON'] =  $data;
            break;

        case 'NAME':
            $parent = prev($element_pile);
            switch ($parent)
            {

                case 'MODULE':
                    {
                        $module_info['NAME'] = $data;
                    }break;

                case 'AUTHOR':
                    {
                        $module_info['AUTHOR']['NAME'] = $data;
                    }
                    break;
            }
            break;

        case 'MINVERSION':
            $parent = prev($element_pile);
            switch ($parent)
            {
                case 'PHP':
                    $module_info['PHP_MIN_VERSION'] = $data;
                    break;

                case 'MYSQL':
                    $module_info['MYSQL_MIN_VERSION'] = $data;
                    break;
            }
            break;

        case 'MAXVERSION':
            $parent = prev($element_pile);
            switch ($parent)
            {
                case 'PHP':
                    $module_info['PHP_MAX_VERSION'] = $data;
                    break;

                case 'MYSQL':
                    $module_info['MYSQL_MAX_VERSION'] = $data;
                    break;
            }
            break;

        case 'VERSION':

            $parent = prev($element_pile);
            switch ($parent)
            {
                case 'MODULE':
                    $module_info['VERSION'] = $data;
                    break;

                case 'CLAROLINE' :
                    $module_info['CLAROLINE']['VERSION'] = $data;
                    break;
            }
            break;

        case 'WEB':
            $parent = prev($element_pile);
            switch ($parent)
            {
                case 'MODULE':
                    $module_info['WEB'] = $data;
                    break;

                case 'AUTHOR':
                    $module_info['AUTHOR']['WEB'] = $data;
                    break;
            }

            break;

        case 'LINK' :

            $context = prev($element_pile);
            $parent = prev($element_pile);
            break;

        case 'DATABASE' : case 'FILE' :

            $context = prev($element_pile);
            $parent = prev($element_pile);
            if ('CONTEXT' == $parent)
            {
                $module_info['CONTEXT'][$context][$current_element] = $data;
            }

            break;
    }
}

/**
 * function to create a temporary directory
 */

function tempdir($dir, $prefix='tmp', $mode=0777)
{
    if (substr($dir, -1) != '/') $dir .= '/';

    do
    {
        $path = $dir.$prefix.mt_rand(0, 9999999);
    } while (!claro_mkdir($path, $mode));

    return $path;
}

/**
 * Generate the cache php file with the needed include of activated module of the platform.
 *
 */

function generate_module_cache()
{

    $module_cache_filename = get_conf('module_cache_filename','module_cache.inc.php');
    $cacheRepositorySys = get_conf('rootSys') . get_conf('cacheRepository', 'tmp/cache/');
    claro_mkdir($cacheRepositorySys, CLARO_FILE_PERMISSIONS, true);
    $tbl = claro_sql_get_main_tbl();


    $sql = "SELECT `label`
              FROM `" . $tbl['module'] . "`
             WHERE activation = 'activated'";
    $module_list = claro_sql_query_fetch_all($sql);

    if (file_exists($cacheRepositorySys) && is_writable($cacheRepositorySys)) $handle = fopen($cacheRepositorySys . $module_cache_filename,'w');
    else                          trigger_error('ERROR: directory ' . $cacheRepositorySys . ' is not writable',E_USER_NOTICE);


    fwrite($handle, '<?php //auto created by claroline '."\n");
    fwrite($handle, 'if ((bool) stristr($_SERVER[\'PHP_SELF\'], basename(__FILE__))) die();'."\n");

    $moduleRepositorySys = get_conf('rootSys') . 'module/';
    foreach($module_list as $module)
    {
        if (file_exists(get_module_path($module['label']) . '/functions.php'))
        {
            $dock_include  = "if (file_exists('" . get_module_path($module['label']) . '/functions.php\')) ';
            $dock_include .= 'require "' . get_module_path($module['label']) . '/functions.php"; ' . "\n";

            if (fwrite($handle, $dock_include) === FALSE)
            {
                echo "ERROR: could not write in (" . $module_cache_filename . ")";
            }
        }
    }

    fwrite($handle, "\n" . '?>');
    fclose($handle);


}

/**
 * Add module in claroline, giving  its path
 *
 * @param string $modulePath
 * @return install result
 */
function register_module($modulePath)
{
    global $regLog;

    $regLog = array();
    if (file_exists($modulePath))
    {
        $module_info = readModuleManifest($modulePath);

        if (is_array($module_info) && false !== ($moduleId = register_module_core($module_info)))
        {
            $regLog['info'][] = get_lang('%claroLabel registred', array('%claroLabel'=>$module_info['LABEL']));

            if('TOOL' == strtoupper($module_info['TYPE']))
            {
                if (false !== ($toolId   = register_module_tool($moduleId,$module_info)))
                {
                    $regLog['info'][] = get_lang('%claroLabel registred as tool', array('%claroLabel'=>$module_info['LABEL']));
                    if (array_key_exists('CONTEXT', $module_info))
                    {
                        foreach ($module_info['CONTEXT'] as $context => $contextInfo)
                        {
                            if (false !== register_module_tool_in_context($toolId,$context,$contextInfo))
                            {
                                $regLog['info'][] = get_lang('%claroLabel registred in %context', array('%claroLabel'=>$module_info['LABEL'],'%context'=>$context));
                                foreach ($contextInfo['LINKS'] AS $menu)
                                {
                                    add_tool_in_context_menu($toolId, $menu['TARGET'], $context, $menu['PATH']);
                                    $regLog['info'][] = get_lang('Module found and set : %dock', array('%dock' => var_export($menu['TARGET'],1)));
                                }
                            }
                        }
                    }
                    else  $regLog['error'][] = get_lang('tool %label have no context', array('%label' => $module_info['LABEL']));
                }
                else
                {
                    $regLog['error'][] = get_lang('can not register tool %label', array('%label' => $module_info['LABEL']));
                }
            }
            elseif('APPLET' == $module_info['TYPE'])
            {
                if (array_key_exists('DEFAULT_DOCK',$module_info) && is_array($module_info['DEFAULT_DOCK']))
                {
                    foreach($module_info['DEFAULT_DOCK'] as $dock)
                    {
                        add_module_in_dock($moduleId, $dock);
                        $regLog['info'][] = get_lang('Module found and set : %dock', array('%dock' => $dock));
                    }
                }
            }
        }
        else $regLog['error'][] = get_lang('can not register module %label', array('%label' => $module_info['LABEL']));
    }
    else $regLog['error'][] = get_lang('can not find module');

    return $moduleId;
    //return $regLog;
}

/**
 * Add common info about a module in main module registry.
 * In Claroline this  info is split in two type of info
 * into two tables :
 * * module  for really use info,
 * * module_info for descriptive info
 *
 * @param array $module_info.
 * @return integer moduleId in the registry.
 */
function register_module_core($module_info)
{
    $tbl = claro_sql_get_tbl(array('module','module_info'));
    $missingElement = array_diff(array('LABEL','NAME','TYPE','CLAROLINE','AUTHOR','DESCRIPTION','LICENSE'),array_keys($module_info));
    if (count($missingElement)>0)
    {
        echo '<div>'.__LINE__.': $missingElement = <pre>'. var_export($missingElement,1).'</PRE></div>';
        return claro_failure::set_failure($missingElement);

    }

    if (isset($module_info['CONTEXT']['COURSE']['LINKS'][0]['PATH']))
    {
        $script_url = $module_info['CONTEXT']['COURSE']['LINKS'][0]['PATH'];
    }
    elseif (isset($module_info['CONTEXT']['COURSE']['ENTRY']))
    {
        $script_url = $module_info['CONTEXT']['COURSE']['ENTRY'];
    }
    elseif (isset($module_info['ENTRY']))
    {
        $script_url = $module_info['ENTRY'];
    }
    else
    {
        $script_url = 'entry.php';
    }

    $sql = "INSERT INTO `" . $tbl['module'] . "`
            SET label      = '" . addslashes($module_info['LABEL'      ]) . "',
                name       = '" . addslashes($module_info['NAME']) . "',
                type       = '" . addslashes($module_info['TYPE']) . "',
                script_url = '" . addslashes($script_url)."'
                ";
    $moduleId = claro_sql_query_insert_id($sql);


    $sql = "INSERT INTO `" . $tbl['module_info'] . "`
            SET module_id    = " . (int) $moduleId . ",
                version      = '" . addslashes($module_info['CLAROLINE']['VERSION']) . "',
                author       = '" . addslashes($module_info['AUTHOR']['NAME'  ]) . "',
                author_email = '" . addslashes($module_info['AUTHOR']['EMAIL' ]) . "',
                website      = '" . addslashes($module_info['AUTHOR']['WEB'   ]) . "',
                description  = '" . addslashes($module_info['DESCRIPTION'     ]) . "',
                license      = '" . addslashes($module_info['LICENSE'         ]) . "'";

    claro_sql_query($sql);

    return $moduleId;
}

function register_module_tool($moduleId,$moduleToolData)
{
    $tbl = claro_sql_get_tbl('module_tool');
    if (is_array($moduleToolData))
    {
        $entry = (array_key_exists('ENTRY',$moduleToolData) ? $moduleToolData['ENTRY']:'index.php');
        $icon     = (array_key_exists('ICON',$moduleToolData) ? "'" . addslashes( $moduleToolData['ICON']) . "'" :'NULL');
        //    if (!file_exists($entry))
        //    {
        //        trigger_error($entry . 'not found', E_USER_WARNING);
        //    }
        $sql = "INSERT INTO `" . $tbl['module_tool'] . "`
                SET module_id = " . (int) $moduleId . ",
                    icon      = " . $icon  ;
        $module_inserted_id = claro_sql_query_insert_id($sql);

        return $module_inserted_id;
    }
}

function register_module_tool_in_context($toolId, $toolContext, $toolContextProperty)
{
    $tbl = claro_sql_get_tbl('module_rel_tool_context');
    $sql = "SELECT max(def_rank) FROM `" . $tbl['module_rel_tool_context'] . "`
            WHERE context        = '" . addslashes($toolContext) . "'";
    $rank = claro_sql_query_get_single_value($sql);
    $sql = "INSERT INTO `" . $tbl['module_rel_tool_context'] . "`
        SET  tool_id        = " . (int) $toolId . ",
             context        = '" . addslashes($toolContext) . "',
             enabling       = '" . addslashes($toolContextProperty['ENABLING']) . "',
             def_access     = '" . addslashes($toolContextProperty['DEFAULT_ACCESS']) . "',
             def_rank       = '" . $rank . "',
             access_manager = '" . addslashes($toolContextProperty['ACCESS_MANAGER']) . "'
             ";

    return claro_sql_query_insert_id($sql);
}

function add_tool_in_context_menu($toolId, $menu, $contextId, $path)
{

    $tbl = claro_sql_get_tbl(array('module_rel_tool_context_menu', 'module_menu',));
    $sql = "REPLACE  INTO `" . $tbl['module_menu'] . "`
            SET contextId     = '" . addslashes($menu) . "',
                menuId        = " . (int) $menuId ;
    $menuId = claro_sql_query_insert_id($sql);

    $sql = "SELECT max(defaultRank) FROM `" . $tbl['module_rel_tool_context_menu'] . "`
            WHERE toolId        = " . (int) $toolId . ",
              AND contextId     = '" . addslashes($contextId) . "',
              AND menuId        = " . (int) $menuId ;

    $rank = claro_sql_query_get_single_value($sql);
    $sql = "INSERT INTO `" . $tbl['module_rel_tool_context_menu'] . "`
        SET  toolId        = " . (int) $toolId . ",
             contextId     = '" . addslashes($contextId) . "',
             menuId        = " . (int) $menuId . ",
             path          = '" . addslashes($path) . "',
             defaultRank   = " . (int) $rank ;

    return claro_sql_query_insert_id($sql);

}

function claro_get_module_types()
{
    $tbl = claro_sql_get_tbl('module');
    $sql = "SELECT distinct M.`type` AS `type`
           FROM `" . $tbl['module'] . "` AS M";
    $moduleType = claro_sql_query_fetch_all_cols($sql);
    return $moduleType['type'];
}


function readModuleManifest($modulePath)
{
    global $module_info;
    global $element_pile;
    $backlog_message=array();
    // Find XML manifest and parse it to retrieve module informations

    //check if manifest is present
    $manifestPath = $modulePath. '/manifest.xml';
    if (! check_name_exist($manifestPath))
    {
        return claro_failure::set_failure(get_lang('Manifest missing : %filename',array('%filename' => $manifestPath)));
    }

    //create parser and array to retrieve info from manifest
    $element_pile = array();  //pile to known the depth in which we are
    $module_info = array();   //array to store the info we need
    $module_info['DEFAULT_DOCK'] = array();//array off possible default dock in which module can be set

    $xml_parser = xml_parser_create();
    xml_set_element_handler($xml_parser, 'startElement', 'endElement');
    xml_set_character_data_handler($xml_parser, 'elementData');

    //open manifest

    if (!($fp = @fopen($manifestPath, 'r')))
    {
        return claro_failure::set_failure("Error opening module's manifest");
    }
    else
    {
        array_push ($backlog_message, get_lang('Manifest open : manifest.xml'));
        $data = fread($fp, filesize($manifestPath));
    }

    //parse manifest

    if (!xml_parse($xml_parser, $data, feof($fp)))
    {
        // if reading of the xml file in not successfull :
        // set errorFound, set error msg, break while statement
        return claro_failure::set_failure('Error reading manifest');
    }
    // close file

    fclose($fp);

    //display debug info

    if (get_conf('CLARO_DEBUG_MODE',false) )
    {
        // array_push ($backlog_message, '<PRE>' . htmlentities( implode("", file($file))) . '</pre>');
        foreach ($module_info as $key => $info)
        {
            array_push ($backlog_message, 'The metadata ' . $key . ' as been found : <b>' . var_export($info,1) . '</b>');
        }
    }

    // liberate parser ressources
    xml_parser_free($xml_parser);

    return $module_info;

}


function get_dock_list($moduleType,$context='ALL')
{
    $dockList   = array();
    switch($moduleType)
    {
        case 'applet' :
            $dockList[] = "campusBannerLeft";
            $dockList[] = "campusBannerRight";
            $dockList[] = "userBannerLeft";
            $dockList[] = "userBannerRight";
            $dockList[] = "courseBannerLeft";
            $dockList[] = "courseBannerRight";
            $dockList[] = "homePageCenter";
            $dockList[] = "campusHomePageBottom";
            $dockList[] = "homePageRightMenu";
            $dockList[] = "campusFooterCenter";
            $dockList[] = "campusFooterLeft";
            $dockList[] = "campusFooterRight";
            break;
        case 'tool' :
            $dockList[] = "commonToolList";
    }
    return $dockList;
}

function get_module_info($moduleId)
{

    $tbl = claro_sql_get_tbl(array('module', 'module_info', 'module_tool'));

    $sql = "SELECT M.`label`      AS label,
               M.`id`         AS id,
               M.`name`       AS `module_name`,
               M.`activation` AS `activation`,
               M.`type`       AS type,
               M.`script_url` AS script_url,
               MT.`icon`       AS icon,
               MI.*
        FROM (`" . $tbl['module']      . "` AS M
           , `" . $tbl['module_info'] . "` AS MI )
        LEFT JOIN `" . $tbl['module_tool'] . "` AS MT
              ON MT.`module_id`= M.id

        WHERE  M.`id` = MI . `module_id`
        AND    M.`id` = " . (int) $moduleId;

    return claro_sql_query_get_single_row($sql);

}
?>
