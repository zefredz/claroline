<?php // $Id$
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
 * @author Guillaume lederer <guim@claroline.net>
 */

/**
 * Get installed module list, its effect is
 * * to return an array containing the installed module's labels
 * @param string $type : type of the module that msu be returned, if null, then all the modules are returned
 * @return array containing the ids, the labels and the names of the modules installed on the platform
 */

function get_installed_module_list($type = null)
{
    $tbl_name = claro_sql_get_main_tbl();
    $tbl_module = $tbl_name['module'];

    $sql = "SELECT `id`,
                   `label`,
                   `name`
            FROM   `" . $tbl_module."`";
    if (isset($type))
    {
        $sql.= " WHERE `type`='".$type."'";
    }

    $moduleList = claro_sql_query_fetch_all($sql);
    return $moduleList;
}

/**
 * Get the list of the repositories found in the module repository where all modules are installed, its effect is
 * * returning the expected list
 * @return an array with all the repositories found in the module repository where all modules are installed
 */

function get_module_repositories()
{
    $baseWorkDir = get_conf('rootSys') . 'claroline/module/';

    if ($handle = opendir($baseWorkDir))
    {
        while (false !== ($file = readdir($handle)))
        {
            // skip eventual files found at this place
            if (!is_dir($baseWorkDir.$file) ) continue ;

            // skip '.', '..' and 'CVS'
            if ( $file == '.' || $file == '..' || $file == 'CVS' ) continue;
        }
    }

   closedir($handle);
}

/**
 * Get the list of the repositories found in the module repository where all modules are installed, its effect is
 * * returning the expected list
 * @return an array containing paths of the suspicious folders found that did not correspond to an installed module
 */

function check_module_repositories()
{
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
    $tbl_name = claro_sql_get_main_tbl();
    $tbl_module = $tbl_name['module'];
    //1- call activation script (if any) from the module repository

    /*TO DO*/

    //2- change related entry in the main DB

    $sql = "UPDATE `" . $tbl_module."`
            SET `activation` = 'activated'
            WHERE `id` = " . (int) $moduleId;
    $result = claro_sql_query($sql);

    //3- cache file with the module's include must be renewed after activation of the module

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

function desactivate_module($moduleId)
{
    //1- call desactivation script (if any) from the module repository

    /*TO DO*/

    //2- change related entry in the main DB

    $tbl_name = claro_sql_get_main_tbl();
    $tbl_module = $tbl_name['module'];

    $sql = "UPDATE `" . $tbl_module . "`
            SET `activation` = 'desactivated'
            WHERE `id`= " . (int) $moduleId;

    $result = claro_sql_query($sql);

    //3- cache file with the module's include must be renewed after desactivation of the module

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
function add_module_in_dock($moduleId, $newDockName)
{
    $tbl_name = claro_sql_get_main_tbl();
    $tbl_module = $tbl_name['module'];
    $tbl_dock = $tbl_name['dock'];

    //find info about this module occurence in this dock in the DB

    $sql = "SELECT D.`name`      AS dockname,
                   D.`rank`      AS oldRank
            FROM `" . $tbl_module . "` AS M
               , `" . $tbl_dock   . "` AS D
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

        $sql = "INSERT INTO `" . $tbl_dock . "`
                SET module_id = " . (int) $moduleId . ",
                    name =  '" . addslashes($newDockName) . "',
                    rank = " . ((int)$max_rank+1) ;
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

function remove_module_dock($moduleId,$dockName)
{
    $tbl_name = claro_sql_get_main_tbl();
    $tbl_dock = $tbl_name['dock'];

    //call of this function to remove ALL occurence of the module in any dock

    if ('ALL' == $dockName)
    {
        //1- find all dock in which the dock displays

        $sql="SELECT `name` AS dockName
              FROM   `" . $tbl_dock . "`
              WHERE  `module_id` = " . (int) $moduleId;

        $dock_list = claro_sql_query_fetch_all($sql);

        //2- re-call of this function which each dock concerned

        foreach($dock_list as $dock)
        {
            remove_module_dock($moduleId,$dock['dockName']);
        }
    }

    else

    //call of this function to remove ONE SPECIFIC occurence of the module in the dock

    {
        //find the rank of the module in this dock :

        $sql = "SELECT `rank` AS oldRank
                FROM   `" . $tbl_dock . "`
                WHERE  `module_id` = " . (int) $moduleId . "
                AND    `name` = '" .$dockName ."'";
        $module = claro_sql_query_get_single_row($sql);

        //move up all modules displayed in this dock

        $sql = "UPDATE `" . $tbl_dock . "`
                SET `rank` = `rank`-1
                WHERE `name` = '" . $dockName . "'
                AND `rank` > " . (int) $module['oldRank'];
        claro_sql_query($sql);

        //delete the module line in the dock table

        $sql = "DELETE FROM `" . $tbl_dock . "`
                WHERE `module_id` = " . (int) $moduleId. "
                AND   `name` = '" .$dockName ."'";
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
    $tbl_name        = claro_sql_get_main_tbl();
    $tbl_dock        = $tbl_name['dock'];

    switch ($direction)
    {
        case 'up' :

            //1-find value of current module rank in the dock
            $sql = "SELECT `rank`
                    FROM `" . $tbl_dock . "`
                    WHERE `module_id`=" . (int) $moduleId . "
                    AND `name`='" . addslashes($dockName) . "'";
            $result=claro_sql_query_get_single_value($sql);

            //2-move down above module
            $sql = "UPDATE `" . $tbl_dock . "`
                    SET `rank` = `rank`+1
                    WHERE `module_id` != " . (int) $moduleId . "
                    AND `name`       = '" . addslashes($dockName) . "'
                    AND `rank`       = " . (int) $result['rank'] . " -1 ";

            claro_sql_query($sql);

            //3-move up current module
            $sql = "UPDATE `" . $tbl_dock . "`
                    SET `rank` = `rank`-1
                    WHERE `module_id` = " . (int) $moduleId . "
                    AND `name`      = '" .  addslashes($dockName) . "'
                    AND `rank` > 1"; // this last condition is to avoid wrong update due to a page refreshment
            claro_sql_query($sql);

            break;

        case 'down' :

            //1-find value of current module rank in the dock
            $sql = "SELECT `rank`
                    FROM `" . $tbl_dock . "`
                    WHERE `module_id`=" . (int) $moduleId . "
                    AND `name`='" . addslashes($dockName) . "'";
            $result=claro_sql_query_get_single_value($sql);

            //this second query is to avoid a page refreshment wrong update

            $sqlmax= "SELECT MAX(`rank`) AS `max_rank`
                      FROM `" . $tbl_dock . "`
                      WHERE `name`='" .  addslashes($dockName) . "'";
            $resultmax=claro_sql_query_get_single_value($sqlmax);

            if ($resultmax['max_rank'] == $result['rank']) break;

            //2-move up above module
            $sql = "UPDATE `" . $tbl_dock . "`
                    SET `rank` = `rank` - 1
                    WHERE `module_id` != " . $moduleId . "
                    AND `name` = '" . addslashes($dockName) . "'
                    AND `rank` = " . (int) $result['rank'] . " + 1
                    AND `rank` > 1";
            claro_sql_query($sql);

            //3-move down current module
            $sql = "UPDATE `" . $tbl_dock . "`
                    SET `rank` = `rank` + 1
                    WHERE `module_id`=" . (int) $moduleId . "
                    AND `name`='" .  addslashes($dockName) . "'";
            claro_sql_query($sql);

            break;
    }

    generate_module_cache();
}


/**
 * function to install a specific module to the platform
 *
 */

function install_module()
{

    global $debug_mode;
    global $includePath;
    global $maxFilledSpaceForModule;
    // needed for parser
    global $element_pile;
    global $module_info;
    global $mainTblPrefix;

    $tbl_name = claro_sql_get_main_tbl();
    $tbl_module = $tbl_name['module'];
    $tbl_module_info = $tbl_name['module_info'];

    $backlog_message = array();

    //1- Unzip folder in a new repository in claroline/module

    include $includePath . '/lib/pclzip/pclzip.lib.php';

    //Check if the file is valid (not to big and exists)

    if( !isset($_FILES['uploadedModule']) || !is_uploaded_file($_FILES['uploadedModule']['tmp_name']))
    {
        array_push($backlog_message, get_lang('Problem with file upload'));
    }
    else
    {
        array_push($backlog_message, get_lang('Temporary file is : ') . $_FILES['uploadedModule']['tmp_name']);
    }

    //unzip files

    $baseWorkDir = get_conf('rootSys') . 'claroline/module/';

    //create temp dir for upload

    $uploadDirFullPath   = tempdir($baseWorkDir);
    $uploadDir           = str_replace($baseWorkDir,'',$uploadDirFullPath);
    $workDir             = $baseWorkDir.$uploadDir.'/';

    if ( preg_match('/.zip$/i', $_FILES['uploadedModule']['name']) && treat_uploaded_file($_FILES['uploadedModule'],$baseWorkDir, $uploadDir, $maxFilledSpaceForModule,'unzip',true))
    {
        array_push ($backlog_message, get_lang('Files dezipped sucessfully in ' ). $workDir);

        if (!function_exists('gzopen'))
        {
            array_push ($backlog_message,get_lang('Error : no zlib extension found'));
            claro_delete_file($workDir);
            return $backlog_message;
        }
    }
    else
    {
        array_push ($backlog_message, get_lang('Impossible to unzip file'));
        claro_delete_file($workDir);
        return $backlog_message;
    }

    //2- Find XML manifest and parse it to retrieve module informations

    //check if manifest is present

    if (check_name_exist($workDir . 'manifest.xml'))
    {
        array_push ($backlog_message, get_lang('Manifest found'));
    }
    else
    {
        array_push ($backlog_message, get_lang('Manifest missing : %filename',array('%filename' => $workDir.'manifest.xml')));
        claro_delete_file($workDir);
        return $backlog_message;
    }

    //create parser and array to retrieve info from manifest

    $element_pile = array();  //pile to known the depth in which we are

    $module_info = array();   //array to store the info we need
    $module_info['DEFAULT_DOCK'] = array();//array off possible default dock in which module can be set

    $xml_parser = xml_parser_create();
    xml_set_element_handler($xml_parser, 'startElement', 'endElement');
    xml_set_character_data_handler($xml_parser, 'elementData');

    $file = $workDir. 'manifest.xml';

    //open manifest

    if (!($fp = @fopen($file, 'r')))
    {
        array_push ($backlog_message, get_lang("Error opening module's manifest"));
        claro_delete_file($workDir);
        return $backlog_message;
    }
    else
    {
        array_push ($backlog_message, get_lang('Manifest open : manifest.xml'));
        $data = fread($fp, filesize($file));
    }

    //parse manifest

    if (!xml_parse($xml_parser, $data, feof($fp)))
    {
        // if reading of the xml file in not successfull :
        // set errorFound, set error msg, break while statement

        array_push ($backlog_message, get_lang('Error reading manifest') );
        claro_delete_file($workDir);
        return $backlog_message;
    }
    // close file

    fclose($fp);

    //display debug info

    if ($debug_mode)
    {
        foreach ($module_info as $key => $info)
        {
            array_push ($backlog_message, 'The metadata ' . $key . ' as been found : <b>' . $info . '</b>');
        }
    }

    // liberate parser ressources

    xml_parser_free($xml_parser);


    //check if a module with the same LABEL is already installed, if yes, we cancel everything

    if (check_name_exist($baseWorkDir . $module_info['LABEL'] . '/'))
    {
        array_push ($backlog_message,get_lang('This module is already installed on your platform '));
        claro_delete_file($workDir);
        return $backlog_message;
    }

    //3- Save the module information into DB

    $sql = "INSERT INTO `" . $tbl_module . "`
            SET label = '" . addslashes($module_info['LABEL'      ]) . "',
                name  = '" . addslashes($module_info['MODULE_NAME']) . "',
                type  = '" . addslashes($module_info['MODULE_TYPE']) . "'";
    $moduleId = claro_sql_query_insert_id($sql);


    $sql = "INSERT INTO `" . $tbl_module_info . "`
            SET module_id    = " . (int) $moduleId . ",
                version      = '" . addslashes($module_info['CLARO_VERSION']) . "',
                author       = '" . addslashes($module_info['AUTHOR_NAME'  ]) . "',
                author_email = '" . addslashes($module_info['AUTHOR_EMAIL' ]) . "',
                website      = '" . addslashes($module_info['AUTHOR_WEB'   ]) . "',
                description  = '" . addslashes($module_info['DESCRIPTION'  ]) . "',
                license      = '" . addslashes($module_info['LICENSE'      ]) . "'";

    $module_info_id =  claro_sql_query_insert_id($sql);

    $sql = "UPDATE `" . $tbl_module . "`
            SET `module_info_id` = '" . $module_info_id . "'
            WHERE `id`= " . (int) $moduleId;
    claro_sql_query($sql);

    //in case of coursetool type module, the dock can not be selected and must added also now

    if ('coursetool' == $module_info['MODULE_TYPE'])
    {
        add_module_in_dock($moduleId, 'coursetool');
    }

    elseif (sizeof($module_info['DEFAULT_DOCK'])>=1)

    //If at least one default dock is set (and that this is not a coursetool module), then we create the dock instance in the DB

    {
        foreach($module_info['DEFAULT_DOCK'] as $the_dock)
        {
            add_module_in_dock($moduleId, $the_dock);
            array_push ($backlog_message, get_lang("Default dock of the module found and set")." : ".$the_dock);
        }
    }

    array_push ($backlog_message, get_lang("The information has been saved into the DB"));

    //4- Rename the module repository with label

    if (!rename( $workDir, $baseWorkDir.$module_info['LABEL'].'/'))
    {
        array_push ($backlog_message, get_lang("Error while renaming the module's folder"));
        return $backlog_message;
    }
    else
    {
        array_push ($backlog_message, get_lang('Repository renamed successfully'));
    }

    //5-Include the local 'install.sql' and 'install.php' file of the module if they exist

    if (file_exists($baseWorkDir . $module_info['LABEL'] . '/install/install.sql'))
    {
        $sql = file_get_contents($baseWorkDir.$module_info['LABEL'].'/install/install.sql');
        if (!empty($sql))
        {
            $sql = str_replace ('__CL_MAIN__',$mainTblPrefix, $sql);
            claro_sql_multi_query($sql); //multiquery should be assumed here
        }
        array_push ($backlog_message, get_lang('<b>%filename</b> file found and called in the module repository',array('%filename'=>'install.sql')));
    }

    if (file_exists($baseWorkDir . $module_info['LABEL'] . '/install/install.php'))
    {
        require $baseWorkDir . $module_info['LABEL'] . '/install/install.php';
        array_push ($backlog_message, get_lang('<b>%filename</b> file found and called in the module repository',array('%filename'=>'install.php')));
    }

    //6- cache file with the module's include must be renewed after installation of the module

    generate_module_cache();

    //7- return the backlog

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

    global $rootSys;
    global $mainTblPrefix;

    //Needed tables and vars

    $tbl_name = claro_sql_get_main_tbl();
    $tbl_module = $tbl_name['module'];
    $tbl_module_info = $tbl_name['module_info'];

    $baseWorkDir = $rootSys.'claroline/module/';
    $backlog_message = array();

    // 0- find info about the module to uninstall

    $sql = "SELECT `label`
            FROM `" . $tbl_module . "`
            WHERE `id` = " . (int) $moduleId;

    $module = claro_sql_query_get_single_row($sql);

    // 1- Include the local 'uninstall.sql' and 'uninstall.php' file of the module if they exist

    if (file_exists($baseWorkDir.$module['label'] . '/uninstall/uninstall.sql'))
    {
        $sql = file_get_contents($baseWorkDir . $module['label'] . '/uninstall/uninstall.sql');
        if (!empty($sql))
        {
            $sql = str_replace ('__CL_MAIN__',$mainTblPrefix, $sql);
            claro_sql_multi_query($sql); //multiquery should be assumed here
        }
        array_push ($backlog_message, get_lang('<b>%filename</b> file found and called in the module repository',array('%filename'=>'uninstall.sql')));
    }

    if (file_exists($baseWorkDir . $module['label'] . '/uninstall/uninstall.php'))
    {
        require $baseWorkDir . $module['label'] . '/uninstall/uninstall.php';
        array_push ($backlog_message,get_lang('<b>%filename</b> file found and called in the module repository',array('%filename'=>'uninstall.php')));
    }

    // 2- delete related files and folders

    $workDir = $baseWorkDir.$module['label'];

    claro_delete_file($workDir);
    array_push ($backlog_message, get_lang('<b>%dirname</b> has been deleted on the server',array('%dirname'=>$workDir)));

    // 3- delete related entries in main DB

    $sql = "DELETE FROM `" . $tbl_module . "`
            WHERE `id` = ". (int) $moduleId;
    claro_sql_query($sql);

    $sql = "DELETE FROM `" . $tbl_module_info . "`
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
    $tbl_name = claro_sql_get_main_tbl();
    $tbl_dock = $tbl_name['dock'];

    $sql = "SELECT MAX(rank) AS mrank
            FROM `" . $tbl_dock . "` AS D
            WHERE D . `name` = '" . addslashes($dockName) . "'";
    $max_rank = claro_sql_query_get_single_value($sql);
    return (int) $max_rank;
}

//XML PARSER FUNCTIONS : needed functions for the manifest parser :

/**
 * Function used by the SAX xml parser when the parser meets a opening tag
 *
 * @param unknown_type $parser xml parser created with "xml_parser_create()"
 * @param unknown_type $name name of the element
 * @param unknown_type $attributes
 */
function startElement($parser, $name, $attributes)
{
    global $element_pile;
    global $module_info;

    array_push($element_pile,$name);
    $current_element = end($element_pile);

    switch ($current_element)
    {
        case 'MODULE_TYPE' :
            $module_info['MODULE_TYPE'] = $attributes['VALUE'];
            break;

        case 'DEFAULT_DOCK' :
            $module_info['DEFAULT_DOCK'][] = $attributes['VALUE'];
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
        case 'DESCRIPTION' :
            {
                $module_info['DESCRIPTION'] = $data;
            }   break;

        case 'EMAIL':
                $module_info['AUTHOR_EMAIL'] = $data;
             break;

        case 'LABEL':
                $module_info['LABEL'] = $data;
            break;

        case 'LICENSE':
                $module_info['LICENSE'] = $data;
               break;

        case 'NAME':
                $parent = prev($element_pile);
                switch ($parent)
                {
                    case 'MODULE':
                        {
                            $module_info['MODULE_NAME'] = $data;
                        }break;

                    case 'AUTHOR':
                        {
                            $module_info['AUTHOR_NAME'] = $data;
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
                            $module_info['MODULE_VERSION'] = $data;
                           break;

                    case 'CLAROLINE' :
                            $module_info['CLARO_VERSION'] = $data;
                           break;
                }
               break;

        case 'WEB':

                $module_info['AUTHOR_WEB'] = $data;
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
    global $includePath;
    $module_cache_filename = get_conf('module_cache_filename');

    $tbl_name = claro_sql_get_main_tbl();
    $tbl_module = $tbl_name['module'];

    $sql = "SELECT M.`label` AS `label`
              FROM `".$tbl_module."` AS M
             WHERE M.`activation` = 'activated'";
    $module_list = claro_sql_query_fetch_all($sql);

    if (is_writable($includePath)) $handle = fopen($includePath.$module_cache_filename,'w');
    else                           trigger_error('ERROR: directory is not writable',E_USER_NOTICE);

    fwrite($handle, '<?php '."\n");

    foreach($module_list as $module)
    {
        if (file_exists($includePath.'/../module/'.$module['label'].'/functions.php'))
        {
            $dock_include  = "if (file_exists('".$includePath.'/../module/'.$module['label'].'/functions.php\')) ';
            $dock_include .= 'require "'.$includePath.'/../module/'.$module['label'].'/functions.php"; '."\n";

            if (fwrite($handle, $dock_include) === FALSE)
            {
                echo "ERROR: could not write in (".$module_cache_filename.")";
            }
        }
    }

    fwrite($handle, '?>'."\n");
    fclose($handle);
}

?>