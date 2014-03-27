<?php // $Id$

/**
 * Claroline extension modules repository functions
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
 * Get the list of the repositories found in the module repository
 * where all modules are installed, its effect is
 * returning the expected list
 *
 * @return an array with all the repositories found in the module repository
 * where all modules are installed
 */
function get_module_repositories()
{

    $moduleRepositorySys = get_path('rootSys') . 'module/';
    $folder_array = array();
    if(file_exists($moduleRepositorySys))
    {
        if (false !== ($handle = opendir($moduleRepositorySys)))
        {
            while (false !== ($file = readdir($handle)))
            {
                if ( $file == '.' || $file == '..' || $file == 'CVS' )
                {
                    continue;
                }
                elseif (!is_dir($moduleRepositorySys . $file) )
                {
                    continue ;
                }
                elseif( is_dir($file) && $file[0] == '.' )
                {
                    continue;
                }
                else
                {
                    $folder_array[] = $file;
                }
            }
        }

        closedir($handle);
    }
    return $folder_array;
}

/**
 * Check the presence of unexpected module repositories or unexpected module
 * in DB, its effect is returning a list of module not installed in DB but
 * present on server, or module installed in DB but not present on server.
 * @return an array two arrays :
 *            ['folder'] containing paths of the suspicious folders found that
 *                       did not correspond to an installed module in DB
 *            ['DB']     containing label of modules found in DB for which no
 *                       corresponding folder was found on server
 */
function check_module_repositories()
{
    $mistake_array           = array();
    $mistake_array['folder'] = array();
    $mistake_array['DB']     = array();

    $registredModuleList = get_installed_module_list();

    foreach ($registredModuleList as $registredModuleLabel)
    {
        $modulePath = get_module_path($registredModuleLabel);

        if ( !file_exists($modulePath) )
        {
            $mistake_array['DB'][] = $registredModuleLabel;
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
