<?php // $Id$

//----------------------------------------------------------------------
// CLAROLINE
//----------------------------------------------------------------------
// Copyright (c) 2001-2005 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see 'credits' file
//---------------------------------------------------------------------- 

/**
 * Checks a file or a directory actually exist at this location
 *
 * @author - Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param  - filePath (string) - path of the presume existing file or dir
 * @return - boolean TRUE if the file or the directory exists
 *           boolean FALSE otherwise.
 */

function check_name_exist($filePath)
{
	clearstatcache();
	return file_exists($filePath);
}


/**
 * Delete a file or a directory (and its whole content)
 *
 * @author - Hugues Peeters
 * @param  - $filePath (String) - the path of file or directory to delete
 * @return - boolean - true if the delete succeed
 *           boolean - false otherwise.
 */

function claro_delete_file($filePath)
{
    if( is_file($filePath) )
    {
        return unlink($filePath);
    }
    elseif( is_dir($filePath) )
    {
        $dirHandle = opendir($filePath);

        if ( ! $dirHandle ) return false;

        $removableFileList = array();

        while ( $file = readdir($dirHandle) )
        {
            if ( $file == '.' || $file == '..') continue;

            $removableFileList[] = $filePath.'/'.$file;
        }

        closedir($dirHandle); // impossible to test, closedir return void ...

        if ( sizeof($removableFileList) > 0)
        {
            foreach($removableFileList as $thisFile)
            {
                if ( ! claro_delete_file($thisFile) ) return false;
            }
        }
       
        return rmdir($filePath);

    } // end elseif is_dir()
}

//------------------------------------------------------------------------------


/**
 * Rename a file or a directory
 *
 * @author - Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param  - $filePath (string) - complete path of the file or the directory
 * @param  - $newFileName (string) - new name for the file or the directory
 * @return - string  - new file path if it succeeds
 *         - boolean - false otherwise
 * @see    - rename() uses the check_name_exist() and php2phps() functions
 */

function claro_rename_file($oldFilePath, $newFilePath)
{

    if (realpath($oldFilePath) == realpath($newFilePath) ) return true;

    /* CHECK IF THE NEW NAME HAS AN EXTENSION */

    if (( ! ereg('[[:print:]]+\.[[:alnum:]]+$', $newFilePath))
        &&  ereg('[[:print:]]+\.([[:alnum:]]+)$', $oldFilePath, $extension))
    {
        $newFilePath .= ".".$extension[1];
    }

    /* PREVENT FILE NAME WITH PHP EXTENSION */

    $newFilePath = get_secure_file_name($newFilePath);

    /* REPLACE CHARACTER POTENTIALY DANGEROUS FOR THE SYSTEM */

    $newFilePath = dirname($newFilePath).'/'
                  .replace_dangerous_char(basename($newFilePath));

    if (check_name_exist($newFilePath)
        && $newFilePath != $oldFilePath)
    {
        return false;
    }
    else
    {
        if ( rename($oldFilePath, $newFilePath) )
        {
            return $newFilePath;
        }
        else
        {
            return false;
        }
    }
}

//------------------------------------------------------------------------------


/**
 * Move a file or a directory to an other area
 *
 * @author - Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param  - $sourcePath (String) - the path of file or directory to move
 * @param  - $targetPath (String) - the path of the new area
 * @return - boolean - true if the move succeed
 *           boolean - false otherwise.
 */


function claro_move_file($sourcePath, $targetPath)
{
    if (realpath($sourcePath) == realpath($targetPath) ) return true;

    // check to not copy a directory inside itself
    if (   is_dir($sourcePath) 
        && ereg('^'.$sourcePath.'/', $targetPath.'/') ) 
        return claro_failure::set_failure('MOVE INSIDE ITSELF');

    $sourceFileName = basename($sourcePath);
    
    if (   $sourcePath == $targetPath 
        || file_exists($targetPath.'/'.$sourceFileName) )
         return claro_failure::set_failure('FILE EXISTS');

    if ( is_dir($targetPath) )
    {
        return rename($sourcePath, $targetPath.'/'.$sourceFileName);
    }
    else
    {
        return rename($sourcePath, $targePath);
    }
}

//------------------------------------------------------------------------------


/**
 * Copy a a file or a directory and its content to an other area
 *
 * @author - Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param  - $origDirPath (String) - the path of the directory to move
 * @param  - $destination (String) - the path of the new area
 * @param  - $delete (bool) - move or copy the file
 * @return - void no return !!
 */

function claro_copy_file($sourcePath, $targetPath)
{
    $fileName = basename($sourcePath);

    if ( is_file($sourcePath) )
    {
    	return copy($sourcePath , $targetPath.'/'.$fileName);
    }
    elseif ( is_dir($sourcePath) )
    {
        // check to not copy the directory inside itself
        if ( ereg('^'.$sourcePath.'/', $targetPath.'/') ) return false;

        if ( ! mkdir($targetPath.'/'.$fileName, 0777) )   return false;

        $dirHandle = opendir($sourcePath);

        if ( ! $dirHandle ) return false;

        $copiableFileList = array();

        while ($element = readdir($dirHandle) )
        {
            if ( $element == '.' || $element == '..') continue;

            $copiableFileList[] = $sourcePath.'/'.$element;
        }

        if ( ! closedir($dirHandle) ) return false;

        if ( count($copiableFileList) > 0 )
        {
            foreach($copiableFileList as $thisFile)
            {
                if ( ! claro_copy_file($thisFile) ) return false;
            }
        }

        return true;
    } // end elseif is_dir()
}

//------------------------------------------------------------------------------


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

/* NOTE: These functions batch is used to automatically build HTML forms
 * with a list of the directories contained on the course Directory.
 *
 * From a thechnical point of view, form_dir_lists calls sort_dir wich calls index_dir
 */

/**
 * Indexes all the directories and subdirectories
 * contented in a given directory
 * 
 * @author - Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param  - path (string) - directory path of the one to index
 * @return - an array containing the path of all the subdirectories
 */

function index_dir($path)
{
	chdir($path);
	$handle = opendir($path);

    $dirList = array();

	// reads directory content end record subdirectoies names in $dir_array
	while ($element = readdir($handle) )
	{
		if ( $element == '.' || $element == '..') continue;	// skip the current and parent directories
		if ( is_dir($element) )	 $dirList[] = $path.'/'.$element;
	}

	closedir($handle) ;

	// recursive operation if subdirectories exist
	$dirNumber = sizeof($dirList);
	if ( $dirNumber > 0 )
	{
		for ($i = 0 ; $i < $dirNumber ; $i++ )
		{
			$subDirList = index_dir( $dirList[$i] ) ;			    // function recursivity
			$dirList  =  array_merge( $dirList , $subDirList ) ;	// data merge
		}
	}

	chdir("..") ;

	return $dirList ;
}


/**
 * Indexes all the directories and subdirectories
 * contented in a given directory, and sort them alphabetically
 *
 * @author - Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param  - path (string) - directory path of the one to index
 * @return - an array containing the path of all the subdirectories sorted
 *           false, if there is no directory
 * @see    - index_and_sort_dir uses the index_dir() function
 */

function index_and_sort_dir($path)
{
	$dir_list = index_dir($path);

	if ($dir_list)
	{
		sort($dir_list);
		return $dir_list;
	}
	else
	{
		return false;
	}
}


/**
 * build an html form listing all directories of a given directory and file to move
 *
 * @param file        string: filename to o move
 * @param baseWorkDir string: complete path to root directory to prupose as target for move
 */

function form_dir_list($file, $baseWorkDir)
{
    global $_SERVER, $langCopy, $langTo;

	$dirList = index_and_sort_dir($baseWorkDir);

	$dialogBox .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"post\">\n"
	             ."<input type=\"hidden\" name=\"cmd\" value=\"exMv\">\n"
	             ."<input type=\"hidden\" name=\"file\" value=\"".$file."\">\n"	
	             .$langCopy.' <i>'.basename($file).'</i> '.$langTo." :\n"
	             ."<select name=\"destination\">\n";

    if ( dirname($file) == '/' || dirname($file) == '\\')
    {
        $dialogBox .= "<option value=\"\" class=\"invisible\">root</option>\n";
    }
    else 
    {
        $dialogBox .= "<option value=\"\" >root</option>\n";
    }


	$bwdLen = strlen($baseWorkDir) ;	// base directories lenght, used under

	/* build html form inputs */

	if ($dirList)
	{
		while (list( , $pathValue) = each($dirList) )
		{

			$pathValue = substr ( $pathValue , $bwdLen );		// truncate confidential informations
			$dirname = basename ($pathValue);					// extract $pathValue directory name

			/* compute de the display tab */

			$tab = '';										// $tab reinitialisation
			$depth = substr_count($pathValue, '/');			// The number of nombre '/' indicates the directory deepness

			for ($h = 0; $h < $depth; $h++)
			{
				$tab .= '&nbsp;&nbsp';
			}

            if ($file == $pathValue OR dirname($file) == $pathValue)
            {
                $dialogBox .= '<option class="invisible" value="'.$pathValue.'">'.$tab.' &gt; '.$dirname.'</option>'."\n";
            }
            else
            {
                $dialogBox .= '<option value="'.$pathValue.'">'.$tab.' &gt; '.$dirname.'</option>'."\n";
            }
		}
	}

	$dialogBox .= "</select>\n";
	$dialogBox .= "<input type=\"submit\" value=\"Ok\">";
	$dialogBox .= "</form>\n";

	return $dialogBox;
}

//------------------------------------------------------------------------------



/**
 * create directory
 *
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param string  $pathname
 * @param int     $mode directory permission (optional)
 * @param boolean $recursive (optional)
 * @return boolean TRUE if succeed, false otherwise
 */

function claro_mkdir($pathName, $mode = 0777, $recursive = false)
{
    if ($recursive)
    {
        $dirList = explode( '/', str_replace('\\', '/', $pathName) );

        $dirTrail = '';

        foreach($dirList as $thisDir)
        {
            $dirTrail .= empty($dirTrail) ? $thisDir : '/'.$thisDir;

            if ( file_exists($dirTrail) ) 
            {
                if ( is_dir($dirTrail) ) continue;
                else                     return false;
            }
            else
            {
                 if ( ! @mkdir($dirTrail , $mode) ) return false;
            }

        }
        return true;
    }
    else
    {
        return @mkdir($pathName, $mode);
    }
}


/**
 * to extract the extention of the filename 
 *
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param  string $file
 * @return string extension
 *         bool false
 */

function get_file_extension($file)
{ 
    $pieceList = explode('.', $file);

    if ( count($pieceList) > 1) // there is more than one piece
    {
        $lastPiece = array_pop($pieceList); // the last cell should be the extansion

        if ( ! strstr('/', $lastPiece) ) // check the dot is not into 
        {                                // a parent directory name
            return $lastPiece;
        }
    }

    return false;
}



/**
 * to compute the size of the directory 
 *
 * @returns integer size
 * @param 	string	$path path to size
 * @param 	boolean $recursive if true , include subdir in total
 */

function claro_get_file_size($filePath)
{ 
    if     ( is_file($filePath) ) return filesize($filePath);
    elseif ( is_dir($filePath)  ) return disk_total_space($filePath);
    else                          return 0;
}

/**
 * search files or directory whose name fit a pattern
 *
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param string $searchPattern - regex pattern to search on file name
 * @param string $baseDirPath - directory path where to start the search
 * @param string $fileType (optional) - filter allowing to restrict search 
 *        on files or directories (allowed value are 'ALL', 'FILE', 'DIR').
 * @param array $excludedPathList (optional) - list of files or directories 
 *        that have to be excluded from the search
 * @return array path list of the files fitting the search pattern
 */

function claro_search_file($searchPattern             , $baseDirPath, 
                           $recursive        = false , $fileType = 'ALL',
                           $excludedPathList = array()                    )
{
        $searchResultList = array();

        $dirPt = opendir($baseDirPath);

        if ( ! $dirPt) return false;

        while ( $fileName = readdir($dirPt) )
        {
            if (   $fileName == '.' || $fileName == '..' 
                || in_array($baseDirPath.'/'.$fileName, $excludedPathList ) )
            {
                continue;
            }
            else
            {

                $filePath = $baseDirPath.'/'.$fileName;

                if ( is_dir($filePath) ) $dirList[] = $filePath;

                if ( $fileType == 'DIR'  && is_file($filePath) )
                {
                    continue;
                }
                
                if ( $fileType == 'FILE' && is_dir($filePath) ) 
                {
                    continue;
                }

                if ( preg_match($searchPattern, $fileName) )
                {
                    $searchResultList[] = $filePath;
                }

            }
        }

        closedir($dirPt);

        if ( $recursive && count($dirList) > 0)
        {
            foreach($dirList as $thisDir)
            {
                $searchResultList = 
                    array_merge( $searchResultList, 
                                 claro_search_file($searchPattern, $thisDir, 
                                                   $recursive, $fileType,
                                                   $excludedPathList) 
                               );
            }
        }

        return $searchResultList;
}

/**
 * get the url written in files specially created by Claroline 
 * to redirect to a specific url
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 * @param param string $file complete file path
 * @return string url
 */

function get_link_file_url($file)
{
   $fileContent = implode("\n", file ($file));

   preg_match("^<meta http-equiv=\"refresh\" content=\"[0-9]+;url=([-a-zA-Z:/.0-9]+)\">^",
              $fileContent,
              $matchList);
   
   return $matchList[1];
}

//------------------------------------------------------------------------------


/**
 * Update the file or directory path in the document db document table
 *
 * @param  String action    - action type require : 'delete' or 'update'
 * @param  String filePath  - original path of the file
 * @param  String $newParamList - new param of the file, can contain
 *                              'path', 'visibility' and 'comment'
 *
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 *
 */

function update_db_info($action, $filePath, $newParamList = array())
{
    global $dbTable; // table 'document'

    if ($action == 'delete') // case for a delete
    {
        $theQuery = "DELETE FROM `".$dbTable."`
                     WHERE path=\"".$filePath."\"
                     OR    path LIKE \"".addslashes($filePath)."/%\"";
        claro_sql_query($theQuery);
    }
    elseif ($action == 'update')
    {
        $sql = "SELECT path, comment, visibility
                FROM `".$dbTable."`
                WHERE path=\"".addslashes($filePath)."\"";

        list($attribute) = claro_sql_query_fetch_all($sql);

        if (is_null($attribute)) // case where there isn't any record in the db
        {                        // concerning this file yet ...
            if (   ( isset($newParamList['comment'])    && ! empty($newParamList['comment']) )
                || ( isset($newParamList['visibility']) && $newParamList['visibility'] != 'v') )
            {
                $newParamList['visibility'] != 'i' ? $newParamList['visibility'] = 'v' : '';
                $insertedPath = ( trim($newParamList['path']) != '' ? $newParamList['path'] : $filePath);

                $theQuery = "INSERT INTO `".$dbTable."`
                             SET path       = \"".addslashes($insertedPath)."\",
                                 comment    = \"".addslashes($newParamList['comment'   ])."\",
                                 visibility = \"".addslashes($newParamList['visibility'])."\"";              
            }
            // else noop
        }
        else // case there is already a record in the db concerning this file
        {
            if ( ! isset($newParamList['visibility']) )
            {
                $newParamList['visibility'] = $attribute['visibility'];
            }

            if ( ! isset($newParamList['comment']) )
            {
                $newParamList['comment'] = $attribute['comment'];
            }


            if (empty($newParamList['comment']) && $newParamList['visibility'] == 'v')
            {
                $theQuery = "DELETE FROM `".$dbTable."`
                             WHERE path=\"".$filePath."\"";
            }
            else
            {
                $theQuery = "UPDATE `".$dbTable."`
                             SET comment    = \"".addslashes($newParamList['comment'   ])."\",
                                 visibility = \"".addslashes($newParamList['visibility'])."\"
                             WHERE path=\"".addslashes($filePath)."\"";
            }
        }

        if (isset($theQuery)) claro_sql_query($theQuery);


        if ( ! empty($newParamList['path']) )
        {
            $theQuery = "UPDATE `".$dbTable."`
            SET path = CONCAT(\"".addslashes($newParamList['path'])."\", SUBSTRING(path, LENGTH(\"".addslashes($filePath)."\")+1) )
            WHERE path = \"".addslashes($filePath)."\" OR path LIKE \"".addslashes($filePath)."/%\"";
            $r = claro_sql_query($theQuery);
        }
    } // end else if action == update
}

//------------------------------------------------------------------------------


function update_Doc_Path_in_Assets($type, $oldPath, $newPath) {

        global $TABLEASSET, $TABLELEARNPATH, $TABLELEARNPATHMODULE, 
               $TABLEUSERMODULEPROGRESS, $TABLEMODULE, $TABLEEXERCISES;

        switch ($type)
        {
            case 'update' :

                  // Find and update assets that are concerned by this move

                  $sql = "UPDATE `".$TABLEASSET."`
                          SET `path` = CONCAT(\"".$newPath."\", SUBSTRING(`path`, LENGTH(\"".$oldPath."\")+1) )
                          WHERE `path` LIKE \"".$oldPath."%\"
                          ";

                  claro_sql_query($sql);

                  break;

            case 'delete' :

                  // delete assets, modules, learning path modules, and userprogress that are based on this document

                  // find all assets concerned by this deletion

                  $sql ="SELECT *
                         FROM `".$TABLEASSET."`
                         WHERE `path` LIKE \"".$oldPath."%\" 
                         ";

                  $result = claro_sql_query($sql);

                  $num = mysql_numrows($result);
                  if ($num != 0)
                  {
                        //find all learning path module concerned by the deletion

                        $sqllpm ="SELECT *
                                  FROM `".$TABLELEARNPATHMODULE."`
                                  WHERE 0=1
                                  ";

                        while ($list=mysql_fetch_array($result))
                        {
                           $sqllpm.= " OR `module_id` = '".$list['module_id']."' ";
                        }
                        
                        $result2 = claro_sql_query($sqllpm);

                        //delete the learning path module(s)

                        $sql1 ="DELETE
                                FROM `".$TABLELEARNPATHMODULE."`
                                WHERE 0=1
                                ";

                        // delete the module(s) concerned
						$sql2 ="DELETE
                                FROM `".$TABLEMODULE."`
                                WHERE 0=1
                               ";
                               
                        $result = mysql_query($sqllpm);//:to reset result resused

                        while ($list=mysql_fetch_array($result))
                        {
                           $sql1.= " OR `module_id` = '".$list['module_id']."' ";
                           $sql2.= " OR `module_id` = '".$list['module_id']."' ";
                        }

                        claro_sql_query($sql1);
                        claro_sql_query($sql2);

                        //delete the user module progress concerned

                        $sql ="DELETE
                               FROM `".$TABLEUSERMODULEPROGRESS."`
                               WHERE 0=1
                               ";
                        while ($list=mysql_fetch_array($result2))
                        {
                           $sql.= " OR `learnPath_module_id` = '".$list['learnPath_module_id']."' ";
                        }

                        claro_sql_query($sql);

                        // delete the assets

                        $sql ="DELETE
                               FROM `".$TABLEASSET."`
                               WHERE
                               `path` LIKE \"".$oldPath."%\"
                               ";

                        claro_sql_query($sql);
                  } //end if($num !=0)
                  break;
         }

}

?>
