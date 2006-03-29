<?php

require_once($includePath.'\lib\pclzip\pclzip.lib.php');
require_once($includePath.'\lib\claro_main.lib.php');

function compress_directory($directory)
{	
	if(!file_exists($directory)) return claro_failure::set_failure("dir doesn't exist");
	else if(!is_dir($directory)) return claro_failure::set_failure("is not a directory");
	
	$archive = new PclZip($directory.".zip");	
	$archive->create($directory,PCLZIP_OPT_REMOVE_PATH, $directory) == 0;
	
	return true;
}
function extract_archive($archiveName,$path)
{
	if(!file_exists($archiveName)) return claro_failure::set_failure("archive doesn't exist");   	
	if(!($archive = new PclZip($archiveName))) return claro_failure::set_failure("opendir failed");
				
	if(false === ($archive->extract(PCLZIP_OPT_ADD_PATH,$path."/". basename($archiveName,'.zip')))) 
			return claro_failure::set_failure("couldn't extract file");

	return true;
}
?>