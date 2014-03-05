<?php // $Id$

/**
 * Claroline extension modules package related functions
 *
 * @version     1.12 $Revision$
 * @copyright   (c) 2001-2014, Universite catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html
 *      GNU GENERAL PUBLIC LICENSE version 2 or later
 * @author      Claro Team <cvs@claroline.net>
 * @package     kernel.module
 * @since       1.12
 */

/**
 * Unzip a module package archive and get the path of the unzipped files
 * @todo split this function and use unzip_package()
 * @todo remove the need of the Backlog and use Exceptions instead
 * @return string
 */
function get_and_unzip_uploaded_package()
{
    $backlog_message = array();

    //Check if the file is valid (not to big and exists)

    if( !isset($_FILES['uploadedModule'])
    || !is_uploaded_file($_FILES['uploadedModule']['tmp_name']))
    {
        $backlog_message[] = get_lang('Upload failed');
    }
    
    require_once __DIR__ . '/../thirdparty/pclzip/pclzip.lib.php';

    if (!function_exists('gzopen'))
    {
        $backlog_message[] = get_lang('Error : no zlib extension found');
        return claro_failure::set_failure($backlog_message);
    }

    //unzip files
    
    
    // $moduleRepositorySys is the place where go the installed module
    // $uploadDirFullPath is a temporary name of the dir in $moduleRepositorySys the module is unpack
    // $uploadDirFullPath would be renamed to $modulePath when install is done.
    
    $moduleRepositorySys = get_path('rootSys') . 'module/';
    //create temp dir for upload
    claro_mkdir($moduleRepositorySys, CLARO_FILE_PERMISSIONS, true);
    $uploadDirFullPath = claro_mkdir_tmp($moduleRepositorySys);
    $uploadDir         = str_replace(realpath($moduleRepositorySys),'',realpath($uploadDirFullPath));
    $modulePath        = realpath($moduleRepositorySys.$uploadDir.'/');

    //1- Unzip folder in a new repository in claroline/module

    // treat_uploaded_file : Executes all the necessary operation to upload the file in the document tool
    // TODO this function would be splited.
    
    if ( preg_match('/.zip$/i', $_FILES['uploadedModule']['name'])
      && treat_uploaded_file( $_FILES['uploadedModule']
                            , $moduleRepositorySys
                            , $uploadDir
                            , get_conf('maxFilledSpaceForModule' , 20000000)
                            , 'unzip'
                            , true)
                            )
    {
        $backlog_message[] = get_lang('Files dezipped sucessfully in %path', array ('%path' => $modulePath )) ;
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
 * Unzip the module package
 * @param string $packageFileName
 * @return string module path
 * @todo use this function in get_and_unzip_uploaded_package()
 * @todo remove the need of the Backlog and use Exceptions instead
 */
function unzip_package( $packageFileName )
{
    $backlog_message = array();

    //1- Unzip folder in a new repository in claroline/module
    require_once __DIR__ . '/../thirdparty/pclzip/pclzip.lib.php';
    
    if (!function_exists('gzopen'))
    {
        $backlog_message[] = get_lang('Error : no zlib extension found');
        return claro_failure::set_failure($backlog_message);
    }

        //unzip files

    $moduleRepositorySys = get_path('rootSys') . 'module/';
    //create temp dir for upload
    claro_mkdir($moduleRepositorySys, CLARO_FILE_PERMISSIONS, true);
    $uploadDirFullPath = claro_mkdir_tmp($moduleRepositorySys);
    $uploadDir         = str_replace($moduleRepositorySys,'',$uploadDirFullPath);
    $modulePath        = $moduleRepositorySys.$uploadDir.'/';
    
    if ( preg_match('/.zip$/i', $packageFileName)
      && treat_secure_file_unzip($packageFileName, $uploadDir, $moduleRepositorySys, get_conf('maxFilledSpaceForModule' , 10000000),true))
    {
        $backlog_message[] = get_lang('Files dezipped sucessfully in %path', array ('%path' => $modulePath )) ;
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
 * Check  if the  given  file path point on a claroline package file
 * @param string $packagePath
 * @return boolean
 */
function is_package_file($packagePath)
{
     $packagePath= realpath($packagePath);
     if (!file_exists($packagePath)) return false;
     if (!is_file($packagePath)) return false;
     if (is_dir($packagePath)) return false;
     if ('.zip' == strtolower(substr($packagePath,-4,4))) return true;
     return false;
}

