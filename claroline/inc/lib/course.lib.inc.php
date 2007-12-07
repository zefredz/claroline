<?php // $Id$
//----------------------------------------------------------------------
// CLAROLINE
//----------------------------------------------------------------------
// Copyright (c) 2001-2003 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: Muret Benoît <muret_ben@hotmail.com>
//----------------------------------------------------------------------


/**
  * Create a backup of a cours
  * @author Muret Benoît <muret_ben@hotmail.com>
  *
  * @param         $link : link to the database
  *             $sysCode : sysCode of the course
  *             &$dir : the backup file
  *
  * @return nothing
  *
  * @desc The function create a backup of a cours in a file
  */
function backupDatabase($link,$sysCode,&$dir)
{
    global $tbl_courses,$coursesRepositorySys;

    $sql_searchCourse="select * from `$tbl_courses` where code='".$sysCode."'";
    $arrayCourse=claro_sql_query_fetch_all($sql_searchCourse);

    global $courseTablePrefix,$dbGlu;

    $db_name=$arrayCourse[0]["dbName"];

    $tbl_rel_usergroup = $courseTablePrefix.$db_name.$dbGlu."group_rel_team_user";

    if (!is_resource($link))
        return false;

    mysql_select_db($db_name);

    $format = strtoupper($format);

    global $localArchivesRepository;

    //Create the repertory who content the file sql
    umask(022);
    if(!is_dir($localArchivesRepository."backup_".$db_name."_".date("Y_m_d") ))
        mkdir($localArchivesRepository."backup_".$db_name."_".date("Y_m_d"));

    if(!is_dir($localArchivesRepository."backup_".$db_name."_".date("Y_m_d")."/cours/" ))
        mkdir($localArchivesRepository."backup_".$db_name."_".date("Y_m_d")."/cours/");

    if(!is_dir($localArchivesRepository."backup_".$db_name."_".date("Y_m_d")."/doc/" ))
        mkdir($localArchivesRepository."backup_".$db_name."_".date("Y_m_d")."/doc/");

    if(file_exists($coursesRepositorySys.$arrayCourse[0]["directory"]."/"))
        copyDirTo($coursesRepositorySys.$arrayCourse[0]["directory"]."/",
            $localArchivesRepository."backup_".$db_name."_".date("Y_m_d")."/doc/",false);

    $dir=$localArchivesRepository."backup_".$db_name."_".date("Y_m_d")."/cours/backup_".$db_name."_".date("Y_m_d").".sql";
    $fp = fopen($dir, "w");

    if (!is_resource($fp))
        return false;

    if(!$singleDbEnabled)
    {
        // Create the base
        fwrite($fp, "DROP DATABASE IF EXISTS ".$db_name.";\n\n");
        fwrite($fp, "CREATE DATABASE ".$db_name.";\n\n");
        fwrite($fp, "USE `".$db_name."`;\n\n");

        // List of tables
        $res = mysql_list_tables($db_name, $link);
        $num_rows = mysql_num_rows($res);
    }
    else
    {
        $sql="select dbName from `$tbl_courses` where code='".$sysCode."'";
        $res=claro_sql_query_fetch_all($sql);

        global $courseTablePrefix,$dbGlu;
        $currentCourseDbNameGlu=$courseTablePrefix.$res[0]["dbName"].$dbGlu;

        // Search all tables of this course
        $sql = "SHOW TABLES LIKE \"".$currentCourseDbNameGlu."%\"";
        $res=claro_sql_query($sql);
        $num_rows= mysql_num_rows($res);
    }

    $i = 0;
    while ($i < $num_rows)
    {
        $tablename = mysql_tablename($res, $i);

        fwrite($fp, "DROP TABLE IF EXISTS `$tablename`;\n");

        // request to created the table
        $query = "SHOW CREATE TABLE $tablename";
        $resCreate = claro_sql_query($query);
        $row = mysql_fetch_array($resCreate);
        $schema = $row[1].";";
        fwrite($fp, "$schema\n\n");

        // data of the table
        $query = "SELECT * FROM $tablename";
        $resData = claro_sql_query($query);

        insertRegistry($fp,$tablename,$resData);

        $i++;
    }

	global $mainDbName;
	fwrite($fp, "Use `$mainDbName`;\n");

	$com=commandCreateTableTemporary($tbl_courses,"temp_cours");
    fwrite($fp,$com."\n\n");

    $query = "SELECT * FROM `$tbl_courses` where code='".$sysCode."'";
    $resData = claro_sql_query($query);
    $tablename="temp_cours";

    insertRegistry($fp,$tablename,$resData);

    global $tbl_user,$tbl_course_user;
    $sql_searchUserCourse="SELECT `cu`.user_id user
                                FROM `$tbl_user` u,`$tbl_course_user` cu
                                LEFT JOIN `$tbl_rel_usergroup` ug
                                    ON `cu`.user_id = `ug`.`user`
                                where
                                    cu.code_cours='".$sysCode."'
                                AND `cu`.user_id = `u`.`user_id`";

    $resData = mysql_query($sql_searchUserCourse);

    while($res= mysql_fetch_array($resData))
    {
            $Data[]=$res["user"];
    }

    if(count($Data)>1)
    {
        $Data=array_unique($Data);
        sort($Data);
    }

    $i=0;
    while($i<count($Data))
    {
        if($i!=0)
            $user.=" OR user_id=";

        $user.=$Data[$i];
        $i++;
    }

	$com=commandCreateTableTemporary($tbl_user,"temp_user");

    fwrite($fp,$com."\n\n");

    if(isset($user))
    {
        global $tbl_user;
        $query = "SELECT * FROM `$tbl_user` where user_id=".$user;
        $resData = claro_sql_query($query);
        $tablename="temp_user";

        insertRegistry($fp,$tablename,$resData);
    }


	$com=commandCreateTableTemporary($tbl_course_user,"temp_cours_user");

    fwrite($fp,$com."\n\n");

    if(isset($user))
    {
        global $tbl_course_user;
        $query = "SELECT * FROM `$tbl_course_user` where code_cours='".$sysCode."' and (user_id=".$user.");";

        $resData = claro_sql_query($query);
        $tablename="temp_cours_user";

        insertRegistry($fp,$tablename,$resData);
    }

    fclose($fp);


  	$archive = new PclZip($localArchivesRepository."backup_".$db_name."_".date("Y_m_d").".zip");
  	$v_list = $archive->create($localArchivesRepository."backup_".$db_name."_".date("Y_m_d"),
							PCLZIP_OPT_REMOVE_PATH,$localArchivesRepository."backup_".$db_name."_".date("Y_m_d"));

	deldir($localArchivesRepository."backup_".$db_name."_".date("Y_m_d"));
}


/**
  * Insert to a file a sql order
  * @author Muret Benoît <muret_ben@hotmail.com>
  *
  * @param handler &$fp            the file
  *           string  $tablename     the table
  *           string  $resData         the values
  *
  * @return nothing
  *
  * @desc The function delete a directory and his below directoy
  */
function insertRegistry(&$fp,$tablename,$resData)
{
    if (mysql_num_rows($resData) > 0)
    {
        $sInsert = "INSERT INTO `$tablename` values ";

        while($rowdata = mysql_fetch_assoc($resData))
        {
            unset($lineData);
            $i=0;
            foreach($rowdata as $data)
            {
                if($i!=0)
                    $lineData.=",";

                if(is_NULL($data))
                    $lineData.="NULL";
                else
                    $lineData.="'".addslashes($data)."'";

                $i++;
            }

            $lesDonnees = "$sInsert($lineData);";

            fwrite($fp, "$lineData\n\n");
        }
    }
}


/**
* Removes comment lines and splits up large sql files into individual queries
*
* Last revision: September 23, 2001 - gandon
*
* @param   array    the splitted sql commands
* @param   string   the sql commands
*
* @return  boolean  always true
*
* @access  public
*/
function PMA_splitSqlFile(&$ret, $sql)
{
    $sql          = trim($sql);
    $sql_len      = strlen($sql);
    $char         = '';
    $string_start = '';
    $in_string    = FALSE;
    $time0        = time();

    for ($i = 0; $i < $sql_len; ++$i) {
        $char = $sql[$i];

        // We are in a string, check for not escaped end of strings except for
        // backquotes that can't be escaped
        if ($in_string) {
            for (;;) {
                $i         = strpos($sql, $string_start, $i);
                // No end of string found -> add the current substring to the
                // returned array
                if (!$i) {
                    $ret[] = $sql;
                    return TRUE;
                }
                // Backquotes or no backslashes before quotes: it's indeed the
                // end of the string -> exit the loop
                else if ($string_start == '`' || $sql[$i-1] != '\\') {
                    $string_start      = '';
                    $in_string         = FALSE;
                    break;
                }
                // one or more Backslashes before the presumed end of string...
                else {
                    // ... first checks for escaped backslashes
                    $j                     = 2;
                    $escaped_backslash     = FALSE;
                    while ($i-$j > 0 && $sql[$i-$j] == '\\') {
                        $escaped_backslash = !$escaped_backslash;
                        $j++;
                    }
                    // ... if escaped backslashes: it's really the end of the
                    // string -> exit the loop
                    if ($escaped_backslash) {
                        $string_start  = '';
                        $in_string     = FALSE;
                        break;
                    }
                    // ... else loop
                    else {
                        $i++;
                    }
                } // end if...elseif...else
            } // end for
        } // end if (in string)

        // We are not in a string, first check for delimiter...
        else if ($char == ';') {
            // if delimiter found, add the parsed part to the returned array
            $ret[]      = substr($sql, 0, $i);
            $sql        = ltrim(substr($sql, min($i + 1, $sql_len)));
            $sql_len    = strlen($sql);
            if ($sql_len) {
                $i      = -1;
            } else {
                // The submited statement(s) end(s) here
                return TRUE;
            }
        } // end else if (is delimiter)

        // ... then check for start of a string,...
        else if (($char == '"') || ($char == '\'') || ($char == '`')) {
            $in_string    = TRUE;
            $string_start = $char;
        } // end else if (is start of string)

        // ... for start of a comment (and remove this comment if found)...
        else if ($char == '#'
                 || ($char == ' ' && $i > 1 && $sql[$i-2] . $sql[$i-1] == '--')) {
            // starting position of the comment depends on the comment type
            $start_of_comment = (($sql[$i] == '#') ? $i : $i-2);
            // if no "\n" exits in the remaining string, checks for "\r"
            // (Mac eol style)
            $end_of_comment   = (strpos(' ' . $sql, "\012", $i+2))
                              ? strpos(' ' . $sql, "\012", $i+2)
                              : strpos(' ' . $sql, "\015", $i+2);
            if (!$end_of_comment) {
                // no eol found after '#', add the parsed part to the returned
                // array if required and exit
                if ($start_of_comment > 0) {
                    $ret[]    = trim(substr($sql, 0, $start_of_comment));
                }
                return TRUE;
            } else {
                $sql          = substr($sql, 0, $start_of_comment)
                              . ltrim(substr($sql, $end_of_comment));
                $sql_len      = strlen($sql);
                $i--;
            } // end if...else
        } // end else if (is comment)

        // loic1: send a fake header each 30 sec. to bypass browser timeout
        $time1     = time();
        if ($time1 >= $time0 + 30) {
            $time0 = $time1;
            header('X-pmaPing: Pong');
        } // end if
    } // end for

    // add any rest to the returned array
    if (!empty($sql) && ereg('[^[:space:]]+', $sql)) {
        $ret[] = $sql;
    }

    return TRUE;
} // end of the 'PMA_splitSqlFile()' function



/**
  * Delete a directory
  * @author Muret Benoît <muret_ben@hotmail.com>
  *
  * @param string $dir    the directory deleting
  *
  * @return nothing
  *
  * @desc The function delete a directory and his below directoy
  */
function deldir($dir){
    $current_dir = opendir($dir);

      while($entryname = readdir($current_dir))
      {
         if(is_dir("$dir/$entryname") && ($entryname != "." && $entryname!=".."))
         {
               deldir("${dir}/${entryname}");
         }
        elseif($entryname != "." && $entryname!="..")
        {
               unlink("${dir}/${entryname}");
         }
      }

      closedir($current_dir);
      rmdir(${dir}."/");
}


/**
  * Create the command to create a temporary table
  * @author Muret Benoît <muret_ben@hotmail.com>
  *
  * @param string $tbl    the table who exist
  *		   string $name   the name of the temporary table
  *
  * @return nothing
  *
  * @desc The function create the command to create a temporary table
  */
function CommandCreateTableTemporary($tbl,$name)
{
	$sql="describe `$tbl`";
	$res=claro_sql_query_fetch_all($sql);

	global $mainDbName;
	$com="CREATE TEMPORARY TABLE `$mainDbName`.`$name` (";
	foreach($res as $one_res)
	{
		$com.=$one_res["Field"]." ".$one_res["Type"]." ";
		if(strcmp($one_res["Null"],"YES"))
			$com.="NOT NULL ";

		if(!strcmp($one_res["Null"],"YES") || $one_res["Default"]!=NULL)
			$com.="default ".($one_res["Default"]==NULL?"NULL":"'".$one_res["Default"]."'");

		$com.=", ";
	}
	$com=substr($com,0,strlen($phrase)-2);
	$com.=");";

	return $com;
}

/**
  * Create a command to create a selectBox with the langage
  * @author Muret Benoît <muret_ben@hotmail.com>
  *
  * @param string $selected the langage selected
  *
  * @return the command to create the selectBox
  *
  * @desc The function create the command to create a selectBox with the langage
  */
function createSelectBoxLangage($selected=NULL)
{
	$arrayLangage=langageExist();
	foreach($arrayLangage as $entries)
	{
		$selectBox.="<option value=\"$entries\" ";

		if ($entries == $selected)
			$selectBox.=" selected ";

		$selectBox.=">".$entries;

		global $langNameOfLang;
		if (!empty($langNameOfLang[$entries]) && $langNameOfLang[$entries]!="" && $langNameOfLang[$entries]!=$entries)
			$selectBox.=" - $langNameOfLang[$entries]";

		$selectBox.="</option>\n";
	}

	return $selectBox;
}

/**
  * Return an array with the langage
  * @author Muret Benoît <muret_ben@hotmail.com>
  *
  * @param nothing
  *
  * @return an array with the langage
  *
  * @desc The function return an array with the langage
  */
function langageExist()
{
	global $clarolineRepositorySys;
	$dirname = $clarolineRepositorySys."lang/";

	if($dirname[strlen($dirname)-1]!='/')
		$dirname.='/';

	//Open the repertoy
	$handle=opendir($dirname);

	//For each reportery in the repertory /lang/
	while ($entries = readdir($handle))
	{
		//If . or .. or CVS continue
		if ($entries=='.' || $entries=='..' || $entries=='CVS')
			continue;

		//else it is a repertory of a langage
		if (is_dir($dirname.$entries))
		{
			$arrayLangage[]=$entries;
		}
	}
	closedir($handle);

	return $arrayLangage;
}


/**
  * build the <option> element with categories where we can create/have courses
  *
  * @param the code of the preselected categorie
  * @param the separator used between a cat and its paretn cat to display in the <select> 
  * @return echo all the <option> elements needed for a <select>. 
  * 
  */


function BuildEditableCatTable($selectedCat = null, $separator = "&gt;")
{
	global $TABLECOURSDOMAIN;
	 
	$result = claro_sql_query("SELECT *
                              FROM `".$TABLECOURSDOMAIN."`                              
			      ORDER BY `treepos`");
			      
	// first we get the categories available in DB from the SQL query result in parameter	

	while ($myfac = mysql_fetch_array($result))
	{
		$categories[$myfac["code"]]["code"]   = $myfac["code"];
		$categories[$myfac["code"]]["parent"] = $myfac["code_P"];
		$categories[$myfac["code"]]["name"]   = $myfac["name"];
		$categories[$myfac["code"]]["childs"] = $myfac["canHaveCoursesChild"];
	}

	// then we build the table we need : full path of editable cats in an array

	$tableToDisplay = array();
	echo "<select name =\"faculte\" id=\"faculte\">\n";	
	foreach ($categories as $cat)
	{
		if ($cat["childs"]=="TRUE") 
		{

			echo "<option value=\"".$cat["code"]."\"";
			if ($cat["code"]==$selectedCat) echo " selected ";
			echo ">";
			$tableToDisplay[$cat["code"]]= $cat;
			$parentPath  = getFullPath($categories, $cat["code"], $separator);

			$tableToDisplay[$cat["code"]]["fullpath"] = $parentPath;
			echo "(".$tableToDisplay[$cat["code"]]["fullpath"].") ".$cat["name"];
			echo "</option>\n";
		}
	}
	echo "</select>\n";

	return $tableToDisplay;
}

/**
  * Recursive function to get the full categories path of a specified categorie
  *
  * @param table of all the categories, 2 dimension tables, first dimension for cat codes, second for names, 
  *  parent's cat code. 
  * @param the categorie we want to have its full path from root categorie
  * 
  * 
  */

function getFullPath($categories, $catcode = null, $separator = " &gt; ")
{
	//Find parent code
	$parent = null;
	foreach ($categories as $currentCat)
	{
		if (( $currentCat['code'] == $catcode))
		{
			$parent = $currentCat['parent'];
		}
	}
	// RECURSION : find parent categorie in table
	if ($parent == null)
	{ 
		return $catcode;
	}
	foreach ($categories as $currentCat)
	{
		if (($currentCat['code'] == $parent))
		{
			return getFullPath($categories, $parent, $separator).$separator.$catcode;
			break;
		}
	}
}

?>