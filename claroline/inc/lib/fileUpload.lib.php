<?php # $Id$

//----------------------------------------------------------------------
// CLAROLINE
//----------------------------------------------------------------------
// Copyright (c) 2001-2004 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available 
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see 'credits' file
//----------------------------------------------------------------------

/*============================================================================
                              FILE UPLOAD LIBRARY
  ============================================================================*/

/**
 * replaces some dangerous character in a string for HTML use
 *
 * @author - Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param  - string (string) string
 * @param  - string $strict (optional) removes also scores and simple quotes
 * @return - the string cleaned of dangerous character
 */

function replace_dangerous_char($string, $strict = 'loose')
{
	$search[] = ' ';  $replace[] = '_';
	$search[] = '/';  $replace[] = '-';
	$search[] = '\\'; $replace[] = '-';
	$search[] = '"';  $replace[] = '-';
	$search[] = '\''; $replace[] = '_';
	$search[] = '?';  $replace[] = '-';
	$search[] = '*';  $replace[] = '-';
	$search[] = '>';  $replace[] = '';
	$search[] = '<';  $replace[] = '-';
	$search[] = '|';  $replace[] = '-';
	$search[] = ':';  $replace[] = '-';
	$search[] = '$';  $replace[] = '-';
	$search[] = '(';  $replace[] = '-';
	$search[] = ')';  $replace[] = '-';
	$search[] = '^';  $replace[] = '-';
	$search[] = '[';  $replace[] = '-';
	$search[] = ']';  $replace[] = '-';

	foreach($search as $key=>$char )
	{
		$string = str_replace($char, $replace[$key], $string);
	}

	if ($strict == 'strict')
	{
        $string = str_replace('-', '_', $string);
        $string = str_replace("'", '', $string);
        $string = strtr($string,
                        '¿¡¬√ƒ≈‡·‚„‰Â“”‘’÷ÿÚÛÙıˆ¯»… ÀËÈÍÎ«ÁÃÕŒœÏÌÓÔŸ⁄€‹˘˙˚¸ˇ—Ò',
                        'AAAAAAaaaaaaOOOOOOooooooEEEEeeeeCcIIIIiiiiUUUUuuuuyNn');
	}

	return $string;
}

//------------------------------------------------------------------------------

/**
 * change the file name extension from .php to .phps
 * Useful to secure a site !!
 *
 * @author - Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param  - fileName (string) name of a file
 * @return - string the filename phps'ized
 */

function php2phps ($fileName)
{
	$fileName = eregi_replace("\.(php.?|phtml)$", ".phps", $fileName);
	return $fileName;
}

/**
 * change the file named .htacess in htacess.txt
 * Useful to secure a site working on Apache.
 *
 * @author - Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param  - fileName (string) name of a file
 * @return - string 'Apache safe' file name
 */


function htaccess2txt($fileName)
{
	$fileName = str_replace('.htaccess', 'htaccess.txt', $fileName);
	$fileName = str_replace('.HTACCESS', 'HTACCESS.txt', $fileName);
    return $fileName;
}


/**
 * change the file named .htacess in htacess.txt
 * Useful to secure a site working on Apache.
 *
 * @author - Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param  - fileName (string) name of a file
 * @return - string innocuous filename
 * @see    - htaccess2txt and php2phps
 */


function get_secure_file_name($fileName)
{
    $fileName = php2phps ($fileName);
    $fileName = htaccess2txt($fileName);
    return $fileName;
}

//------------------------------------------------------------------------------


/** 
 * Check if there is enough place to add a file on a directory
 * on the base of a maximum directory size allowed
 *
 * @author - Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param  - fileSize (int) - size of the file in byte
 * @param  - dir (string) - Path of the directory
 *           whe the file should be added
 * @param  - maxDirSpace (int) - maximum size of the diretory in byte
 * @return - boolean true if there is enough space
 * @return - false otherwise
 *
 * @see    - enough_size() uses  dir_total_space() function
 */

function enough_size($fileSize, $dir, $maxDirSpace)
{
	if ($maxDirSpace)
	{
		$alreadyFilledSpace = dir_total_space($dir);

		if ( ($fileSize + $alreadyFilledSpace) > $maxDirSpace)
		{
			return false;
		}
	}

	return true;
}

//------------------------------------------------------------------------------

/** 
 * Compute the size already occupied by a directory and is subdirectories
 *
 * @author - Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param  - dirPath (string) - size of the file in byte
 * @return - int - return the directory size in bytes
 */

function dir_total_space($dirPath)
{
	chdir ($dirPath) ;
	$handle = opendir($dirPath);

	while ($element = readdir($handle) )
	{
		if ( $element == "." || $element == "..")
		{
			continue; // skip the current and parent directories
		}
		if ( is_file($element) )
		{
			$sumSize += filesize($element);
		}
		if ( is_dir($element) )
		{
			$dirList[] = $dirPath.'/'.$element;
		}
	}

	closedir($handle) ;

	if ( sizeof($dirList) > 0)
	{
		foreach($dirList as $j)
		{
			$sizeDir = dir_total_space($j);	// recursivity
			$sumSize += $sizeDir;
		}
	}

	return $sumSize;
}


//------------------------------------------------------------------------------

/** 
 * Try to add an extension to files witout extension
 * Some applications on Macintosh computers don't add an extension to the files.
 * This subroutine try to fix this on the basis of the MIME type send 
 * by the browser.
 *
 * Note : some browsers don't send the MIME Type (e.g. Netscape 4).
 *        We don't have solution for this kind of situation
 *
 * @author - Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param  - fileName (string) - Name of the file
 * @return - fileName (string)
 *
 */

function add_ext_on_mime($fileName)
{
	global $HTTP_POST_FILES;

	/* 
	 * Check if the file has an extension AND if the browser has send a MIME Type
	 */

	if(!ereg("([[:alnum:]]|[[[:punct:]])+\.[[:alnum:]]+$", $fileName)
		&& $HTTP_POST_FILES['userfile']['type'])
	{
		/*
		 * Build a "MIME-types / extensions" connection table
		 */

		static $mimeType = array();

		$mimeType[] = 'application/msword';             $extension[] ='.doc';
		$mimeType[] = 'application/rtf';                $extension[] ='.rtf';
		$mimeType[] = 'application/vnd.ms-powerpoint';  $extension[] ='.ppt';
		$mimeType[] = 'application/vnd.ms-excel';       $extension[] ='.xls';
		$mimeType[] = 'application/pdf';                $extension[] ='.pdf';
		$mimeType[] = 'application/postscript';         $extension[] ='.ps';
		$mimeType[] = 'application/mac-binhex40';       $extension[] ='.hqx';
		$mimeType[] = 'application/x-gzip';             $extension[] ='tar.gz';
		$mimeType[] = 'application/x-shockwave-flash';  $extension[] ='.swf';
		$mimeType[] = 'application/x-stuffit';          $extension[] ='.sit';
		$mimeType[] = 'application/x-tar';              $extension[] ='.tar';
		$mimeType[] = 'application/zip';                $extension[] ='.zip';
		$mimeType[] = 'application/x-tar';              $extension[] ='.tar';
		$mimeType[] = 'text/html';                      $extension[] ='.htm';
		$mimeType[] = 'text/plain';                     $extension[] ='.txt';
		$mimeType[] = 'text/rtf';                       $extension[] ='.rtf';
		$mimeType[] = 'img/gif';                        $extension[] ='.gif';
		$mimeType[] = 'img/jpeg';                       $extension[] ='.jpg';
		$mimeType[] = 'img/png';                        $extension[] ='.png';
		$mimeType[] = 'audio/midi';                     $extension[] ='.mid';
		$mimeType[] = 'audio/mpeg';                     $extension[] ='.mp3';
		$mimeType[] = 'audio/x-aiff';                   $extension[] ='.aif';
		$mimeType[] = 'audio/x-pn-realaudio';           $extension[] ='.rm';
		$mimeType[] = 'audio/x-pn-realaudio-plugin';    $extension[] ='.rpm';
		$mimeType[] = 'audio/x-wav';                    $extension[] ='.wav';
		$mimeType[] = 'video/mpeg';                     $extension[] ='.mpg';
		$mimeType[] = 'video/quicktime';                $extension[] ='.mov';
		$mimeType[] = 'video/x-msvideo';                $extension[] ='.avi';


		/*
		 * Check if the MIME type send by the browser is in the table
		 */

		foreach($mimeType as $key=>$type)
		{
			if ($type == $HTTP_POST_FILES['userfile']['type'])
			{
				$fileName .=  $extension[$key];
				break;
			}
		}

		unset($mimeType, $extension, $type, $key); // Delete to eschew possible collisions
	}

	return $fileName;
}

/**
 * executes all the necessary operation to upload the file in the document tool
 * 
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 *
 * @param  array $uploadedFile - follows the $HTTP_POST_FILES Structure
 * @param  string $baseWorkDir - base working directory of the module
 * @param  string $uploadPath  - destination of the upload. 
 *                               This path is to append to $baseWorkDir
 * @param  int $maxFilledSpace - amount of bytes to not exceed in the base 
 *                               working directory
 *
 * @return boolean true if it succeds, false otherwise
 */

function treat_uploaded_file($uploadedFile, $baseWorkDir, $uploadPath, $maxFilledSpace, $uncompress= '')
{
	if ( ! enough_size($uploadedFile['size'], $baseWorkDir, $maxFilledSpace))
	{
		return claro_failure::set_failure('not_enough_space');
	}

	if (   $uncompress == 'unzip' 
        && preg_match('/.zip$/i', $uploadedFile['name']) )
	{
		return unzip_uploaded_file($uploadedFile, $uploadPath, $baseWorkDir, $maxFilledSpace);
	}
	else
	{
		$fileName = trim($uploadedFile['name']);

		/* CHECK FOR NO DESIRED CHARACTERS */
		$fileName = replace_dangerous_char($fileName);
		
		/* TRY TO ADD AN EXTENSION TO FILES WITOUT EXTENSION */
		$fileName = add_ext_on_mime($fileName);

		/* HANDLE DANGEROUS FILE NAME FOR SERVER SECURITY */
		$fileName = get_secure_file_name($fileName);

		/* COPY THE FILE TO THE DESIRED DESTINATION */
		if (move_uploaded_file($uploadedFile['tmp_name'], 
            $baseWorkDir.$uploadPath.'/'.$fileName) )
		{
			return $fileName;
		}
	}

    return false;
}



/**
 * Manages all the unzipping process of an uploaded document 
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 *
 * @param  array  $uploadedFile - follows the $HTTP_POST_FILES Structure
 * @param  string $uploadPath   - destination of the upload. 
 *                                This path is to append to $baseWorkDir
 * @param  string $baseWorkDir  - base working directory of the module
 * @param  int $maxFilledSpace  - amount of bytes to not exceed in the base 
 *                                working directory
 *
 * @return boolean true if it succeeds false otherwise
 */

function unzip_uploaded_file($uploadedFile, $uploadPath, $baseWorkDir, $maxFilledSpace)
{
	$zipFile = new pclZip($uploadedFile['tmp_name']);

	// Check the zip content (real size and file extension)

	$zipContentArray = $zipFile->listContent();

	foreach($zipContentArray as $thisContent)
	{
		if ( preg_match('~.(php.*|phtml)$~i', $thisContent['filename']) )
		{
			return claro_failure::set_failure('php_file_in_zip_file');
		}

		$realFileSize += $thisContent['size'];
	}
		
	if (! enough_size($realFileSize, $baseWorkDir, $maxFilledSpace) )
	{
		return claro_failure::set_failure('not_enough_space');
	}

	/*
	 * Uncompressing phase
	 */

	if (PHP_OS == 'Linux' && ! get_cfg_var('safe_mode'))
	{
		// Shell Method - if this is possible, it gains some speed
		exec("unzip -d \"".$baseWorkDir.$uploadPath."/\" "
			 .$uploadedFile['tmp_name']);
	}
	else
	{
		// PHP method - slower...

		chdir($baseWorkDir.$uploadPath);
		$unzippingState = $zipFile->extract();
	}

	return true;
}


/**
 * retrieve the image path list in a html file
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 * @param  string $htmlFile
 * @return array -  images path list
 */

function search_img_from_html($htmlFile)
{
	$imgFilePath = array();
	if(!file_exists($htmlFile))
	{
		return $imgFilePath;
	}
	
	$fp = fopen($htmlFile, "r");

	    // search and store occurences of the <IMG> tag in an array

	$buffer = fread( $fp, filesize($htmlFile) ) or die('<center>can not read file</center>');;

	if ( preg_match_all('~<[[:space:]]*img[^>]*>~i', $buffer, $matches) )
	{
		$imgTagList = $matches[0];
	}

	fclose ($fp); unset($buffer);

	// Search the image file path from all the <IMG> tag detected

	if ( sizeof($imgTagList)  > 0)
	{
		foreach($imgTagList as $thisImgTag)
		{
			if ( preg_match('~src[[:space:]]*=[[:space:]]*[\"]{1}([^\"]+)[\"]{1}~i', 
							$thisImgTag, $matches) )
			{
				$imgPathList[] = $matches[1];
			}
		}

		$imgPathList = array_unique($imgPathList);		// remove duplicate entries
	}

	return $imgPathList;

}

/**
 * creates a new directory trying to find a directory name 
 * that doesn't already exist
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 * @param string $desiredDirName complete path of the desired name
 * @return string actual directory name if it succeeds, 
 *         boolean false otherwise
 */

function create_unexisting_directory($desiredDirName)
{

    $finalName = get_unexisting_file_name($desiredDirName);
	
	if ( mkdir($finalName, 0777) ) return $finalName;
	else                           return false;
}

/**
 * creates a guinely file name that doesn't already exist 
 * inside a specific path
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 * @param string $desiredDirName complete path of the desired name
 * @return string actual file name if it succeeds, 
 *         boolean false otherwise
 */


function get_unexisting_file_name($desiredDirName)
{
	$nb = '';
    
    $fileName = $desiredDirName;

	while ( file_exists($fileName.$nb) )
	{
		$nb += 1;
	}

    return $fileName.$nb;
}

/**
 * 
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 * @param array $uploadedFileCollection - follows the $HTTP_POST_FILES Structure
 * @param  string $destPath
 * @return string $destPath
 */

function move_uploaded_file_collection_into_directory($uploadedFileCollection, $destPath)
{
    $uploadedFileNb = count($uploadedFileCollection['name']);

	for ($i=0; $i < $uploadedFileNb; $i++)
	{

		if (is_uploaded_file($uploadedFileCollection['tmp_name'][$i]))
		{
            if ( move_uploaded_file($uploadedFileCollection['tmp_name'][$i],
                                    $destPath.'/'.php2phps($uploadedFileCollection['name'][$i])) )
			{
				$newFileList[] = basename($destPath).'/'.$uploadedFileCollection['name'][$i];
			}
            else
            {
            	die('<center>can not move uploaded file</center>');
            }
		}
	}
	
	return $newFileList;
}

function replace_img_path_in_html_file($originalImgPath, $newImgPath, $htmlFile)
{
	/*
	 * Open the old html file and replace the src path into the img tag
	 */

	$fp = fopen($htmlFile, 'r') or die ('<center>cannot open file</center>');

	while ( !feof($fp) )
	{
		$buffer = fgets($fp, 4096);

		for ($i = 0, $fileNb = count($originalImgPath); $i < $fileNb ; $i++)
		{
			$buffer = str_replace(	$originalImgPath[$i],
									'./'.$newImgPath[$i],
									$buffer);
		}

		$newHtmlFileContent .= $buffer;
	}

	fclose ($fp) or die ('<center>cannot close file</center>');;

	/*
	 * Write the resulted new file
	 */

	$fp = fopen($htmlFile, 'w')      or die('<center>cannot open file</center>');
	fwrite($fp, $newHtmlFileContent) or die('<center>cannot write in file</center>');
}

/**
 * Creates a file containing an html redirection to a given url
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 * @param string $filePath
 * @param string $url
 * @return void
 */

function create_link_file($filePath, $url)
{
    $fileContent = '<html>'
                  .'<head>'
                  .'<meta http-equiv="content-Type" content="text/html;charset=ISO-8859-5">'
                  .'<meta http-equiv="refresh" content="0;url='.$url.'">'
                  .'</head>'
                  .'<body>'
		          .'<div align="center">'
                  .'<a href="'.$url.'">'.$url.'</a>'
                  .'</div>'
                  .'</body>'
                  .'</html>';

    create_file($filePath, $fileContent);
}

function create_file($filePath, $fileContent)
{
    $fp = fopen ($filePath, 'w') or die ('can not create file');
    fwrite($fp, $fileContent);
}


/**
 * returns the dir path of a specific file or directory
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 * @param string $filePath
 * @return string dir name
 */ 

function claro_dirname($filePath)
{
	 return str_replace('\\', '', dirname($filePath) );
	 
	 // str_replace is necessary because, when there is no
     // dirname, PHP leaves a ' \ ' (at least on windows)
}

/**
 * Determine the maximum size allowed to upload. This size is based on
 * the tool $maxFilledSpace regarding the space already opccupied
 * by previous uploaded files, and the php.ini upload_max_filesize
 * and post_max_size parameters. This value is diplayed on the upload
 * form.
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 * @param int local max allowed file size e.g. remaining place in
 *	an allocated course directory	
 * @return int lower value between php.ini values of upload_max_filesize and 
 *	post_max_size and the claroline value of size left in directory 
 * @see    - get_max_upload_size() uses  dir_total_space() function
 */
function get_max_upload_size($maxFilledSpace, $baseWorkDir)
{
        $php_uploadMaxFile = ini_get('upload_max_filesize');
        if (strstr($php_uploadMaxFile, 'M')) $php_uploadMaxFile = intval($php_uploadMaxFile) * 1048576;
        $php_postMaxFile  = ini_get('post_max_size');
        if (strstr($php_postMaxFile, 'M')) $php_postMaxFile     = intval($php_postMaxFile) * 1048576;
        $docRepSpaceAvailable  = $maxFilledSpace - dir_total_space($baseWorkDir);

        $fileSizeLimitList = array( $php_uploadMaxFile, $php_postMaxFile , $docRepSpaceAvailable );
        sort($fileSizeLimitList);
        list($maxFileSize) = $fileSizeLimitList;

	return $maxFileSize;
}
?>
