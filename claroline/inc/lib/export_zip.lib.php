<?php

/**
* CLAROLINE
*
* @version 1.8 $Revision$
*
* @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
* @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
*
* @package CLEXPORT
*
* @author Yannick Wautelet <yannick_wautelet@hotmail.com>
* @author Claro Team <cvs@claroline.net>
*/

require_once($includePath.'\lib\pclzip\pclzip.lib.php');
require_once($includePath.'\lib\claro_main.lib.php');
/**
 * 		
 * compress the directory in the same path with the name $directory.zip
 * 
 * @author Yannick Wautelet <yannick_wautelet@hotmail.com> 
 * @param  string  $directory       
 * @return false if a problem occured, true if not.  
 */     	
function compress_directory($directory)
{	
	if(!file_exists($directory)) return claro_failure::set_failure("dir doesn't exist");
	else if(!is_dir($directory)) return claro_failure::set_failure("is not a directory");		
	$archive = new PclZip($directory."../".basename($directory).".zip");	
	$archive->create($directory,PCLZIP_OPT_REMOVE_PATH, $directory) == 0;
	
	return true;
}
/**
 * 		
 * extract the $archiveName into the $path directory with the basename of $archiveName
 * 
 * @author Yannick Wautelet <yannick_wautelet@hotmail.com> 
 * @param  string  $archiveName     - path and file of origin
 * @param  string  $path     		- path of destination
 * @return false if a problem occured, true if not.  
 */ 
function extract_archive($archiveName,$path)
{
	if(!file_exists($archiveName)) return claro_failure::set_failure("archive doesn't exist");   	
	if(!($archive = new PclZip($archiveName))) return claro_failure::set_failure("opendir failed");
				
	if(false === ($archive->extract(PCLZIP_OPT_ADD_PATH,$path."/". basename($archiveName,'.zip')))) 
			return claro_failure::set_failure("couldn't extract file");

	return true;
}
?>