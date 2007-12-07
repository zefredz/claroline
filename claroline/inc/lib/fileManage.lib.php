<?php # $Id$

//----------------------------------------------------------------------
// CLAROLINE
//----------------------------------------------------------------------
// Copyright (c) 2001-2003 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see 'credits' file
//----------------------------------------------------------------------

/**
 * Update the file or directory path in the document db document table
 *
 * @author - Hugues Peeters <peeters@ipm.ucl.ac.be>
 *
 * @param  - action (string)   - action type require : 'delete' or 'update'
 * @param  - filePath (string) - original path of the file
 * @param  - $newParam (array) - new param of the file, can contain
 *                              'path', 'visibility' and 'comment'
 *
 */

function update_db_info($action, $filePath, $newParam = array())
{
    global $dbTable; // table 'document'

    if ($action == 'delete') // case for a delete
    {
        $theQuery = "DELETE FROM `".$dbTable."`
                     WHERE path=\"".$filePath."\"
                     OR    path LIKE \"".addslashes($filePath)."/%\"";
    }
    elseif ($action == 'update')
    {
        $sql = "SELECT path, comment, visibility
                FROM `".$dbTable."`
                WHERE path=\"".addslashes($filePath)."\"";

        list($attribute) = claro_sql_query_fetch_all($sql);

        if (is_null($attribute)) // case where there isn't any record in the db
        {                        // concerning this file yet ...
            if (   ( isset($newParam['comment'])    && ! empty($newParam['comment']) )
                || ( isset($newParam['visibility']) && $newParam['visibility'] != 'v') )
            {
                $newParam['visibility'] != 'i' ? $newParam['visibility'] = 'v' : '';

                $theQuery = "INSERT INTO `".$dbTable."`
                             SET path       = \"".addslashes($filePath)."\",
                                 comment    = \"".addslashes($newParam['comment'   ])."\",
                                 visibility = \"".addslashes($newParam['visibility'])."\"";
            }
            // else noop
        }
        else // case there is already a record in the db concerning this file
        {
            if ( ! isset($newParam['visibility']) )
            {
                $newParam['visibility'] = $attribute['visibility'];
            }

            if ( ! isset($newParam['comment']) )
            {
                $newParam['comment'] = $attribute['comment'];
            }


            if (empty($newParam['comment']) && $newParam['visibility'] == 'v')
            {
                $theQuery = "DELETE FROM `".$dbTable."`
                             WHERE path=\"".$filePath."\"";
            }
            else
            {
                $theQuery = "UPDATE `".$dbTable."`
                             SET comment    = \"".addslashes($newParam['comment'   ])."\",
                                 visibility = \"".addslashes($newParam['visibility'])."\"
                             WHERE path=\"".addslashes($filePath)."\"";
            }
        }

        if (isset($theQuery)) claro_sql_query($theQuery);

        if ( ! empty($newParam['path']) )
        {
            $theQuery = "UPDATE `".$dbTable."`
            SET path = CONCAT(\"".addslashes($newParam['path'])."\", SUBSTRING(path, LENGTH(\"".addslashes($filePath)."\")+1) )
            WHERE path = \"".addslashes($filePath)."\" OR path LIKE \"".addslashes($filePath)."/%\"";

            claro_sql_query($theQuery);
        }

    } // end else if action == update
}
//------------------------------------------------------------------------------

/**
 * Cheks a file or a directory actually exist at this location
 *
 * @author - Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param  - filePath (string) - path of the presume existing file or dir
 * @return - boolean TRUE if the file or the directory exists
 *           boolean FALSE otherwise.
 */

function check_name_exist($filePath)
{
	clearstatcache();
	chdir ( dirname($filePath) );
	$fileName = basename ($filePath);

	if (file_exists( $fileName ))
	{
		return true;
	}
	else
	{
		return false;
	}
}


/**
 * Delete a file or a directory
 *
 * @author - Hugues Peeters
 * @param  - $file (String) - the path of file or directory to delete
 * @return - bolean - true if the delete succeed
 *           bolean - false otherwise.
 * @see    - delete() uses check_name_exist() and removeDir() functions
 */

function my_delete($file)
{
	if ( check_name_exist($file) )
	{
		if ( is_file($file) ) // FILE CASE
		{
			unlink($file);
			return true;
		}

		elseif ( is_dir($file) ) // DIRECTORY CASE
		{
			removeDir($file);
			return true;
		}
	}
	else
	{
		return false; // no file or directory to delete
	}

}

//------------------------------------------------------------------------------

/**
 * Delete a directory and its whole content
 *
 * @author - Hugues Peeters
 * @param  - $dirPath (String) - the path of the directory to delete
 * @return - no return !
 */


function removeDir($dirPath)
{
	/* Try to remove the directory. If it can not manage to remove it,
	 * it's probable the directory contains some files or other directories,
	 * and that we must first delete them to remove the original directory.
	 */

	if ( ! @rmdir($dirPath) ) // If PHP can not manage to remove the dir...
	{
		$handle = opendir($dirPath);

		while ($element = readdir($handle) )
		{
            if ( $element == '.' || $element == '..')
			{
				continue;	// skip current and parent directories
			}
			elseif ( is_file($dirPath.'/'.$element) )
			{
				unlink($dirPath.'/'.$element);
			}
			elseif ( is_dir ($dirPath.'/'.$element) )
			{
				$dirToRemove[] = $dirPath.'/'.$element;
			}
		}

		closedir ($handle) ;


		if ( sizeof($dirToRemove) > 0)
		{
			foreach($dirToRemove as $j) removedir($j) ; // recursivity
		}

		rmdir( $dirPath ) ;
	}
}

//------------------------------------------------------------------------------


/**
 * Rename a file or a directory
 *
 * @author - Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param  - $filePath (string) - complete path of the file or the directory
 * @param  - $newFileName (string) - new name for the file or the directory
 * @return - boolean - true if succeed
 *         - boolean - false otherwise
 * @see    - rename() uses the check_name_exist() and php2phps() functions
 */

function my_rename($oldFilePath, $newFilePath)
{

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
 * @param  - $source (String) - the path of file or directory to move
 * @param  - $target (String) - the path of the new area
 * @return - bolean - true if the move succeed
 *           bolean - false otherwise.
 * @see    - move() uses check_name_exist() and copyDirTo() functions
 */


function move($source, $target)
{
	if ( check_name_exist($source) )
	{
		$fileName = basename($source);

		if ( check_name_exist($target.'/'.$fileName) )
		{
			return false;
		}
		else
		{	/* File case */

			if ( is_file($source) )
			{
				copy($source , $target."/".$fileName);
				unlink($source);
				return true;
			}

			/* Directory case */
			elseif (is_dir($source))
			{
				// check to not copy the directory inside itself
				if (ereg('^'.$source.'/', $target.'/'))
				{
					return false;
				}
				else
				{
					copyDirTo($source, $target, true);
					return true;
				}
			}
		}
	}
	else
	{
		return false;
	}

}

//------------------------------------------------------------------------------


/**
 * Move a directory and its content to an other area
 *
 * @author - Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param  - $origDirPath (String) - the path of the directory to move
 * @param  - $destination (String) - the path of the new area
 * @param  - $delete (bool) - move or copy the file
 * @return - no return !!
 */

function copyDirTo($origDirPath, $destination, $delete)
{
	// extract directory name - create it at destination - update destination trail
	$dirName = basename($origDirPath);
	mkdir ($destination."/".$dirName, 0775);
	$destinationTrail = $destination."/".$dirName;

	chdir ($origDirPath) ;
	$handle = opendir($origDirPath);

	while ($element = readdir($handle) )
	{
		if ( $element == "." || $element == "..")
		{
			continue; // skip the current and parent directories
		}
		elseif ( is_file($element) )
		{
			copy($element, $destinationTrail."/".$element);
            if ($delete)
    			unlink($element);
		}
		elseif ( is_dir($element) )
		{
			$dirToCopy[] = $origDirPath."/".$element;
		}
	}

	closedir($handle) ;

	if ( sizeof($dirToCopy) > 0)
	{
		foreach($dirToCopy as $thisDir)
		{
			copyDirTo($thisDir, $destinationTrail, $delete);	// recursivity
		}
	}

	if($delete)
		rmdir ($origDirPath);

}

//------------------------------------------------------------------------------


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
 * build an html form listing all directories of a given directory
 *
 */

function form_dir_list($file, $baseWorkDir)
{
	global $PHP_SELF, $langCopy, $langTo;

	$dirList = index_and_sort_dir($baseWorkDir);

	$dialogBox .= "<form action=\"".$PHP_SELF."\" method=\"post\">\n"
	             ."<input type=\"hidden\" name=\"cmd\" value=\"exMv\">\n"
	             ."<input type=\"hidden\" name=\"file\" value=\"".$file."\">\n"	
	             .$langCopy.' <i>'.basename($file).'</i> '.$langTo." :\n"
	             ."<select name=\"destination\">\n";
    
    if ( dirname($file) == '/' )
    {
        $dialogBox .= "<option value=\"\" style=\"color:#999999\">root</option>\n";
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

			$pathValue = substr ( $pathValue , $bwdLen );		// truncate cunfidential informations confidentielles
			$dirname = basename ($pathValue);					// extract $pathValue directory name du nom

			/* compute de the display tab */

			$tab = "";										// $tab reinitialisation
			$depth = substr_count($pathValue, "/");			// The number of nombre '/' indicates the directory deepness

			for ($h=0; $h<$depth; $h++)
			{
				$tab .= "&nbsp;&nbsp";
			}

            if ($file == $pathValue OR dirname($file) == $pathValue)
            {
                $dialogBox .= "<option style=\"color:#999999\" value=\"$pathValue\">$tab>$dirname</option>\n";
            }
            else
            {
    			$dialogBox .= "<option value=\"$pathValue\">$tab>$dirname</option>\n";
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
 * to create missing directory in a gived path
 *
 * @returns a resource identifier or FALSE if the query was not executed correctly. 
 * @author KilerCris@Mail.com original function from  php manual 
 * @author Christophe Gesché gesche@ipm.ucl.ac.be Claroline Team 
 * @since  28-Aug-2001 09:12 
 * @param 	sting	$path 		wanted path
 * @param 	boolean	$verbose	fix if comments must be printed
 * @param 	string	$mode		fix if chmod is same of parent or default
 * @global 	string  $langCreatedIn string to say "create in"
 */

function mkpath($path, $verbose = false, $mode = "herit")  
{
	GLOBAL $langCreatedIn;
	if ($langCreatedIn =="") $langCreatedIn ="Create in";
	$path = str_replace("/","\\",$path);
	$dirs = explode("\\",$path);
//	print_r ($dirs);
	$path = $dirs[0];
	if ($verbose)
		echo "<UL>";
	for($i = 1;$i < count($dirs);$i++) 
	{
		$path .= "/".$dirs[$i];
		if(!is_dir($path))
		{
//			if ($mode=="herit")
//				$mode =	fileperms($path."/../");
//			$mode = "0700";
			$ret=mkdir($path, 0770);
			if ($ret)
			{
				if ($verbose)
					echo "
				<LI>
					<strong>
						".basename($path)."
					</strong>
					<br>
				 	".$langCreatedIn." 
					<br>
				 	<strong>
						".realpath($path."/..")."
					</strong>";
			}
			else
			{
				if ($verbose)
					echo "
				</UL>
				error : ".$path." not created";
//				return false;	
			}
		}
	}
	if ($verbose)
		echo "</UL>";
	return $ret;
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

function DirSize($path , $recursive=TRUE)
{ 
	$result = 0; 
	if(!is_dir($path) || !is_readable($path)) 
   		return 0; 
	$fd = dir($path); 
	while($file = $fd->read())
	{ 
	   	if(($file != ".") && ($file != ".."))
   		{ 
    	if (@is_dir("$path$file/")) 
 			$result += $recursive?DirSize("$path$file/"):0; 
    	else  
			$result += filesize("$path$file"); 
		} 
	}
	$fd->close(); 
	return $result; 
} 

function update_Doc_Path_in_Assets($type, $oldPath, $newPath) {

        global $TABLEASSET;
        global $TABLELEARNPATH;
        global $TABLELEARNPATHMODULE;
        global $TABLEUSERMODULEPROGRESS;
        global $TABLEMODULE;
        global $TABLEEXERCISES;

        switch ($type)
        {
            case "update" :

                  // Find and update assets that are concerned by this move

                  $sql = "UPDATE `".$TABLEASSET."`
                                SET `path` = CONCAT(\"".$newPath."\", SUBSTRING(`path`, LENGTH(\"".$oldPath."\")+1) )
                                WHERE `path` LIKE \"".$oldPath."%\"";

                  mysql_query($sql);

                  break;

            case "delete" :

                  // delete assets, modules, learning path modules, and userprogress that are based on this document

                  // find all assets concerned by this deletion

                  $sql ="SELECT *
                         FROM `".$TABLEASSET."`
                         WHERE
                         `path` LIKE \"".$oldPath."%\"
                         ";

                  $result = mysql_query($sql);

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
                        
                        $result2 = mysql_query($sqllpm);

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


?>
