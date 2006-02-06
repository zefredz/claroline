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
 *
 * @author claro team <cvs@claroline.net>
 */

/**
 * function to activate a module, its effect is
 *   - to call the activation script of the module (if there is any)
 *   - to modify the information in the main DB
 * @param  the ID of the module that must be activated
 * @return true if the activation suceeded, false otherwise
 */

function activate_module($module_id)
{
    $tbl_name = claro_sql_get_main_tbl();
    $tbl_module = $tbl_name['module'];
    //1- call activation script (if any) from the module repository

    //2- change related entry in the main DB

    $sql = "UPDATE `" . $tbl_module."`
            SET `activation` = 'activated'
            WHERE `id` = " . (int) $module_id;

    return claro_sql_query($sql);
}

/**
 * function to desactivate a module, its effect is
 *   - to call the desactivation script of the module (if there is any)
 *   - to modify the information in the main DB
 * @param  the ID of the module that must be desactivated
 * @return true if the desactivation suceeded, false otherwise
 */

function desactivate_module($module_id)
{
    //1- call desactivation script (if any) from the module repository

    //2- change related entry in the main DB

    $tbl_name = claro_sql_get_main_tbl();
    $tbl_module = $tbl_name['module'];

    $sql = "UPDATE `" . $tbl_module . "`
            SET `activation` = 'desactivated'
            WHERE `id`= " . (int) $module_id;

    return claro_sql_query($sql);
}

/**
 * function to set the dock in which the module displays its content
 *
 * @param unknown_type $module_id
 * @param unknown_type $new_dock
 */
function set_module_dock($module_id, $new_dock)
{
    $tbl_name = claro_sql_get_main_tbl();
    $tbl_module = $tbl_name['module'];
    $tbl_module_info = $tbl_name['module_info'];
    $tbl_dock = $tbl_name['dock'];

    //find info about this module in DB

    $sql = "SELECT D.`name` AS old_dockname,
                   D.`rank` AS old_rank,
                   D.`module_id` AS module_id
            FROM `" . $tbl_module . "` AS M
               , `" . $tbl_dock   . "` AS D
            WHERE M.`id` = D.`module_id`
              AND M.`id` = " . (int) $module_id;
    $module = claro_sql_query_get_single_row($sql);

    //find the highest rank already used in the new dock

    $max_rank = get_max_rank_in_dock($new_dock);


    //update info in DB

    if (isset($module['module_id']))
    {
        if ($new_dock != $module['old_dockname'])
        {
            // the module has already one dock, we just change it
            $sql = "UPDATE `" . $tbl_dock . "`
                    SET `name` = '" . $new_dock . "',
                        `rank` = " . (int) $max_rank . " + 1
                    WHERE `module_id`=" . (int) $module_id;
            claro_sql_query($sql);

            //we must also move up the rank of other module still in the previous dock

            $sql = "UPDATE `" . $tbl_dock . "`
                    SET `rank` = `rank`-1
                    WHERE `name` = '" . $module['old_dockname'] . "'
                      AND `rank` > " . (int) $module['old_rank'];
            claro_sql_query($sql);
        }
        else
        {
            //we are not changing the dock!
            echo get_lang('we are not changing dock');
        }
    }
    else
    {
        // the module has not dock, we create an entry in the DB for it
        $sql = "INSERT INTO `" . $tbl_dock . "` (
                   module_id,
                   name,
                   rank
                   )
                   VALUES (
                   '".$module_id."',
                   '".$new_dock."',
                   ".$max_rank."+1
                   )";
        claro_sql_query($sql);
    }
}

/**
 * function to remove a module from a dock in which the module displays
 *
 */

function remove_module_dock($module_id)
{
    $tbl_name = claro_sql_get_main_tbl();
    $tbl_dock = $tbl_name['dock'];

    //find the dock in which the module is displayed :

    $sql = "SELECT `name` AS old_dockname,
                   `rank` AS old_rank
            FROM   `" . $tbl_dock . "`
            WHERE  `module_id` = " . (int) $module_id;
    $module = claro_sql_query_get_single_row($sql);

    //move up all modules displayed in this dock

    $sql = "UPDATE `" . $tbl_dock . "`
            SET `rank` = `rank`-1
            WHERE `name` = '" . $module['old_dockname'] . "'
              AND `rank` > " . (int) $module['old_rank'];
    claro_sql_query($sql);

    $sql = "DELETE FROM `" . $tbl_dock . "`
            WHERE `module_id` = " . (int) $module_id;
    claro_sql_query($sql);
}

/**
 * function to install a specific module to the platform
 *
 */

function install_module()
{

    global $debug_mode;
    global $includePath;
    global $rootSys;
    global $maxFilledSpaceForModule;
    // needed for parser
    global $element_pile;
    global $module_info;

    $tbl_name = claro_sql_get_main_tbl();
    $tbl_module = $tbl_name['module'];
    $tbl_module_info = $tbl_name['module_info'];
    $tbl_dock = $tbl_name['dock'];


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
        array_push($backlog_message, get_lang('Temporary file is : ' . $_FILES['uploadedModule']['tmp_name']));
    }

    //unzip files

    $baseWorkDir = $rootSys . 'claroline/module/';

    //create temp dir for upload

    $uploadDirFullPath   = tempdir($baseWorkDir);
    $uploadDir           = str_replace($baseWorkDir,'',$uploadDirFullPath);
    $workDir             = $baseWorkDir.$uploadDir.'/';

    if ( preg_match('/.zip$/i', $_FILES['uploadedModule']['name']) && treat_uploaded_file($_FILES['uploadedModule'],$baseWorkDir, $uploadDir, $maxFilledSpaceForModule,'unzip',true))
    {
        array_push ($backlog_message, get_lang('Files dezipped sucessfully in ' . $workDir));

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
        array_push ($backlog_message, get_lang('Manifest missing :'.$workDir.'manifest.xml' ));
        claro_delete_file($workDir);
        return $backlog_message;
    }

    //create parser and array to retrieve info from manifest

    $element_pile = array();  //pile to known the depth in which we are
    $module_info = array();   //array to store the info we need

    $xml_parser = xml_parser_create();
    xml_set_element_handler($xml_parser, 'startElement', 'endElement');
    xml_set_character_data_handler($xml_parser, 'elementData');

    $file = $workDir. 'manifest.xml';

    //open manifest

    if (!($fp = @fopen($file, "r")))
    {
        array_push ($backlog_message, get_lang('Error opening module\'s manifest'));
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

    $sql = "INSERT INTO `" . $tbl_module . "` (
                   label,
                   name,
                   type
                   )
                   VALUES (
                   '" . $module_info['LABEL'      ] . "',
                   '" . $module_info['MODULE_NAME'] . "',
                   '" . $module_info['PLUGINTYPE' ] . "'
                   )";
    $module_id = claro_sql_query_insert_id($sql);

    $sql = "INSERT INTO `" . $tbl_module_info . "` (
                module_id,
                version,
                author,
                author_email,
                website,
                description,
                license
                )
                VALUES (
                '" . $module_id . "',
                '" . $module_info['CLARO_VERSION'] . "',
                '" . $module_info['AUTHOR_NAME'  ] . "',
                '" . $module_info['AUTHOR_EMAIL' ] . "',
                '" . $module_info['AUTHOR_WEB'   ] . "',
                '" . $module_info['DESCRIPTION'  ] . "',
                '" . $module_info['LICENSE'      ] . "'
                )";
    $module_info_id =  claro_sql_query_insert_id($sql);

    $sql = "UPDATE `" . $tbl_module . "`
            SET `module_info_id` = '" . $module_info_id . "'
            WHERE `id`= " . (int) $module_id;
    claro_sql_query($sql);

    //in case of coursetool type plugin, the dock can not be selected and must added also now

    $max_rank = get_max_rank_in_dock('coursetool');
    if ($module_info['PLUGINTYPE'] == 'coursetool')
    {
        set_module_dock($module_id, 'coursetool');
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
            claro_sql_query($sql); //multiquery should be assumed here
        }
        array_push ($backlog_message, get_lang("<b>install.sql</b> file found and called in the module repository"));
    }

    if (file_exists($baseWorkDir . $module_info['LABEL'] . '/install/install.php'))
    {
        require $baseWorkDir.$module_info['LABEL'] . '/install/install.php';
        array_push ($backlog_message, get_lang('<b>install.php</b> file found and called in the module repository'));
    }

    //6- return the backlog

    return $backlog_message;
}

/**
 * function to uninstall a specific module to the platform
 *
 * @param integer $module_id the id of the module to uninstall
 * @return boolean true if the uninstall process suceeded, false otherwise
 *
 */

function uninstall_module($module_id)
{

    global $rootSys;

    //Needed tables and vars

    $tbl_name = claro_sql_get_main_tbl();
    $tbl_module = $tbl_name['module'];
    $tbl_module_info = $tbl_name['module_info'];
    $tbl_dock = $tbl_name['dock'];

    $baseWorkDir = $rootSys.'claroline/module/';
    $backlog_message = array();

    // 0- find info about the module to uninstall

    $sql = "SELECT `label`
            FROM `" . $tbl_module . "`
            WHERE `id` = " . (int) $module_id;

    $module = claro_sql_query_get_single_row($sql);

    // 1- Include the local 'uninstall.sql' and 'uninstall.php' file of the module if they exist

    if (file_exists($baseWorkDir.$module['label'] . '/uninstall/uninstall.sql'))
    {
        $sql = file_get_contents($baseWorkDir . $module['label'] . '/uninstall/uninstall.sql');
        if (!empty($sql))
        {
            claro_sql_query($sql); //multiquery should be assumed here
        }
        array_push ($backlog_message, get_lang('<b>uninstall.sql</b> file found and called in the module repository'));
    }

    if (file_exists($baseWorkDir . $module['label'] . '/uninstall/uninstall.php'))
    {
        require $baseWorkDir . $module['label'] . '/uninstall/uninstall.php';
        array_push ($backlog_message, get_lang('<b>uninstall.php</b> file found and called in the module repository'));
    }

    // 2- delete related files and folders

    $baseWorkDir = $rootSys.'claroline/module/';
    $workDir = $baseWorkDir.$module['label'];

    claro_delete_file($workDir);
    array_push ($backlog_message, get_lang("<b>".$workDir."</b> has been deleted on the server"));

    // 3- delete related entries in main DB

    $sql = "DELETE FROM `".$tbl_module."`
                  WHERE `id` = ". (int)$module_id;
    claro_sql_query($sql);

    $sql = "DELETE FROM `".$tbl_module_info."`
                  WHERE `module_id` = ". (int)$module_id;
    claro_sql_query($sql);

    // 4- remove all docks entries in which the module displays

    remove_module_dock($module_id);

    return $backlog_message;

}



//---------------------------------------------------------------------------------



/**
 * Function used by the SAX xml parser when the parser meets a opening tag
 *
 * @param tring $dockname the dock from which we want this info
 * @return integer : the max rank used for this dock
 *
 */


function get_max_rank_in_dock($dockname)
{
    $tbl_name = claro_sql_get_main_tbl();
    $tbl_dock = $tbl_name['dock'];

    $sql = "SELECT MAX(rank) AS mrank
            FROM `" . $tbl_dock . "` AS D
            WHERE D . `name` = '" . addslashes($dockname) . "'";
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
        case 'PLUGINTYPE' :
        {
        $module_info['PLUGINTYPE'] = $attributes['VALUE'];
        }   break;
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
        {
            $module_info['AUTHOR_EMAIL'] = $data;
        } break;

        case 'LABEL':
        {
            $module_info['LABEL'] = $data;
        }
        break;

        case 'LICENSE':
        {
            $module_info['LICENSE'] = $data;
        }   break;

        case 'NAME':
        {
            $parent = prev($element_pile);
            switch ($parent)
            {
                case 'PLUGIN':
                {
                    $module_info['MODULE_NAME'] = $data;
                }break;

                case 'AUTHOR':
                {
                    $module_info['AUTHOR_NAME'] = $data;
                }
                break;
            }
        }   break;

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
        {
            $parent = prev($element_pile);
            switch ($parent)
            {
                case 'PLUGIN':
                {
                    $module_info['MODULE_VERSION'] = $data;
                }   break;

                case 'CLAROLINE' :
                {
                    $module_info['CLARO_VERSION'] = $data;
                }   break;
            }
        }   break;

        case 'WEB':
        {
            $module_info['AUTHOR_WEB'] = $data;
        }   break;

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

?>