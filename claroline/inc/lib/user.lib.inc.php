<?php // $Id$
/**
 * user lib contain function to ,
 */

/**
  * search informations of the group of two users
  * @author Muret Benoît <muret_ben@hotmail.com>
  *
  * @param string $user1
  *        string $user2
  *
  * @return array information of the course and the group of each user
  *
  * @desc The function search informations of the group of two users
  */
function searchCoursesGroup($user1,$user2)
{
    GLOBAL $tbl_user;
    GLOBAL $tbl_courses;
    GLOBAL $tbl_course_user;
    GLOBAL $courseTablePrefix;
    GLOBAL $dbGlu;

    $sql_searchCourseData =
    "select `cu`.`user_id`,`cu`.`code_cours`,`cu`.`statut`,`cu`.`role`,`cu`.`tutor` titular,`c`.`cours_id`,`c`.`code` sysCode
            ,`c`.`languageCourse`,`c`.`intitule`,`c`.`faculte`,`c`.`titulaires`,`c`.`fake_code`,`c`.`directory`,`c`.`dbName`
        FROM `".$tbl_course_user."` cu,`".$tbl_courses."` c
        WHERE `cu`.`code_cours`=`c`.`code` AND (`cu`.`user_id`='".$user1."' OR `cu`.`user_id`='".$user2."')";

    $res_searchCourseData = claro_sql_query_fetch_all($sql_searchCourseData) ;

    //this is the choose course
    if($res_searchCourseData)
    {
        foreach($res_searchCourseData as $one_course)
        {
            $_course["dbNameGlu"]    = $courseTablePrefix . $one_course["dbName"] . $dbGlu; // use in all queries
            $tbl_rel_usergroup       = $_course["dbNameGlu"]."group_rel_team_user";
            $tbl_group               = $_course["dbNameGlu"]."group_team";

            //search the user groups in this course
            $sql_searchCourseUserGroup =
            "select
                    `g`.`name`, `g`.`description`, `g`.`tutor`,  `g`.`secretDirectory`,
                    `g`.`id` id_group,
                    `ug`.`role`,
                    `tutor`.`nom` lastname,
                    `tutor`.`prenom` firstname,
                    `tutor`.`email`
                FROM `$tbl_rel_usergroup` ug, `".$tbl_group."` g
                LEFT JOIN `".$tbl_user."` tutor
                    ON `g`.`tutor` = `tutor`.`user_id`
                WHERE `ug`.`team`  = `g`.`id`
                AND ug.user='".$one_course["user_id"]."'";

            $courseUserGroup[] = claro_sql_query_fetch_all($sql_searchCourseUserGroup) ;
        }
    }
    $array[0]=$res_searchCourseData;
    $array[1]=$courseUserGroup;
    return $array;
}







/*----------------------------------------
     CATEGORIES DEFINITION TREATMENT
 --------------------------------------*/
/**
 * create a new category definition for the user information
 *
 * @author - Hugues peeters <peeters@ipm.ucl.ac.be>
 * @author - Christophe Gesché <gesche@ipm.ucl.ac.be>
 * @param  - string $title - category title
 * @param  - string $comment - title comment
 * @param  - int$nbline - lines number for the field the user will fill.
 * @return - bollean true if succeed, else bolean false
 */

function claro_user_info_create_cat_def($title="", $comment="", $nbline="5")
{
	global $TBL_USERINFO_DEF;

	if ( 0 == (int) $nbline || empty($title))
	{
		return false;
	}

	$sql = "SELECT MAX(`rank`) maxRank FROM `".$TBL_USERINFO_DEF."`";
	$result = sql_query($sql) or die (WARNING_MESSAGE);
	if ($result) $maxRank = mysql_fetch_array($result);

	$maxRank = $maxRank['maxRank'];

	$thisRank = $maxRank + 1;

	$title   = trim($title);
	$comment = trim($comment);

	$sql = "INSERT INTO `".$TBL_USERINFO_DEF."` SET
			`title`		= \"$title\",
			`comment`	= \"$comment\",
			`nbline`	= \"$nbline\",
			`rank`		= \"$thisRank\"";

	sql_query($sql)  or die(WARNING_MESSAGE);

	return true;
}

/**
 * modify the definition of a user information category
 *
 * @author - Hugues peeters <peeters@ipm.ucl.ac.be>
 * @author - Christophe Gesché <gesche@ipm.ucl.ac.be>
 * @param  - int $id - id of the category
 * @param  - string $title - category title
 * @param  - string $comment - title comment
 * @param  - int$nbline - lines number for the field the user will fill.
 * @return - boolean true if succeed, else otherwise
 */

function claro_user_info_edit_cat_def($id, $title, $comment, $nbline)
{
	global $TBL_USERINFO_DEF;

	if ( 0 == (int) $nbline || 0 == (int) $id )
	{
		return false;
	}
	$title   = trim($title);
	$comment = trim($comment);

	$sql = "UPDATE `".$TBL_USERINFO_DEF."` SET
			`title`		= \"$title\",
			`comment`	= \"$comment\",
			`nbline`	= \"$nbline\"
			WHERE id	= $id";

	sql_query($sql) or die(WARNING_MESSAGE);

	return true;
}

/**
 * remove a category from the category list
 *
 * @author - Hugues peeters <peeters@ipm.ucl.ac.be>
 * @author - Christophe Gesché <gesche@ipm.ucl.ac.be>
 *
 * @param  - int $id - id of the category
 *				or "ALL" for all category
 * @param  - boolean $force - FALSE (default) : prevents removal if users have
 *                            already fill this category
 *                            TRUE : bypass user content existence check
 * @param  - int $nbline - lines number for the field the user will fill.
 * @return - bollean  - TRUE if succeed, ELSE otherwise
 */

function claro_user_info_remove_cat_def($id, $force = false)
{
	global $TBL_USERINFO_CONTENT, $TBL_USERINFO_DEF;

	if ( (0 == (int) $id || $id == "ALL") || ! is_bool($force))
	{
		return false;
	}

	if ( $id != "ALL")
	{
		$sqlCondition = " WHERE id = $id";
	}

	if ($force == false)
	{
		$sql = "SELECT * FROM `".$TBL_USERINFO_CONTENT."` ".$sqlCondition;
		$result = sql_query($sql) or die(WARNING_MESSAGE);

		if ( mysql_num_rows($result) > 0)
		{
			return false;
		}
	}

	$sql = "DELETE FROM `".$TBL_USERINFO_DEF."` ".$sqlCondition;
	sql_query($sql) or die(WARNING_MESSAGE);
}

/**
 * move a category in the category list
 *
 * @author - Hugues peeters <peeters@ipm.ucl.ac.be>
 * @author - Christophe Gesché <gesche@ipm.ucl.ac.be>
 *
 * @param  - int $id - id of the category
 * @param  - direction "up" or "down" :
 *					"up"	decrease the rank of gived $id by switching rank with the just lower
 *					"down"	increase the rank of gived $id by switching rank with the just upper
 *
 * @return - boolean true if succeed, else bolean false
 */

function claro_user_info_move_cat_rank($id, $direction) // up & down.
{
	global $TBL_USERINFO_DEF;

	if ( 0 == (int) $id || ! ($direction == "up" || $direction == "down") )
	{
		return false;
	}

	$sql = "SELECT rank FROM `".$TBL_USERINFO_DEF."` WHERE id = $id";
	$result = sql_query($sql) or die(WARNING_MESSAGE."4");

	if (mysql_num_rows($result) < 1)
	{
		return false;
	}

	$cat = mysql_fetch_array($result);
	$rank = (int) $cat["rank"];
	return claro_user_info_move_cat_rank_by_rank($rank, $direction);
}

/**
 * move a category in the category list
 *
 * @author - Hugues peeters <peeters@ipm.ucl.ac.be>
 * @author - Christophe Gesché <gesche@ipm.ucl.ac.be>
 *
 * @param  - int $rank - actual rank of the category
 * @param  - direction "up" or "down" :
 *					"up"	decrease the rank of gived $rank by switching rank with the just lower
 *					"down"	increase the rank of gived $rank by switching rank with the just upper
 *
 * @return - boolean true if succeed, else bolean false
 */

function claro_user_info_move_cat_rank_by_rank($rank, $direction) // up & down.
{
	global $TBL_USERINFO_DEF;

	if ( 0 == (int) $rank || ! ($direction == "up" || $direction == "down") )
	{
		return false;
	}

	if ($direction == "down") // thus increase rank ...
	{
		$sort = "ASC";
		$compOp = ">=";
	}
	elseif ($direction == "up") // thus decrease rank ...
	{
		$sort = "DESC";
		$compOp = "<=";
	}

	// this request find the 2 line to be switched (on rank value)
	$sql = "SELECT id, rank FROM `".$TBL_USERINFO_DEF."` WHERE rank $compOp $rank
	ORDER BY rank $sort LIMIT 2";

	$result = sql_query($sql) or die (WARNING_MESSAGE."3");

	if (mysql_num_rows($result) < 2)
	{
		return false;
	}

	$thisCat = mysql_fetch_array($result);
	$nextCat = mysql_fetch_array($result);

	$sql1 = "UPDATE `".$TBL_USERINFO_DEF."` SET rank =".$nextCat['rank'].
			" WHERE id = ".$thisCat['id'];
	$sql2 = "UPDATE `".$TBL_USERINFO_DEF."` SET rank =".$thisCat['rank'].
			" WHERE id = ".$nextCat['id'];

	sql_query($sql1) or die (WARNING_MESSAGE."1");
	sql_query($sql2) or die (WARNING_MESSAGE."2");

	return true;
}

/*----------------------------------------
     CATEGORIES CONTENT TREATMENT
 --------------------------------------*/





/**
 * fill a bloc for information category
 *
 * @author - Hugues peeters <peeters@ipm.ucl.ac.be>
 * @author - Christophe Gesché <gesche@ipm.ucl.ac.be>
 * @param  - $def_id,
 * @param  - $user_id,
 * @param  - $user_ip,
 * @param  - $content
 * @return - boolean true if succeed, else bolean false
 */

function claro_user_info_fill_new_cat_content($def_id, $user_id, $content="", $user_ip="")
{
	global $TBL_USERINFO_CONTENT;

	if (empty($user_ip))
	{
		global $REMOTE_ADDR;
		$user_ip = $REMOTE_ADDR;
	}

	$content = trim($content);


	if ( 0 == (int) $def_id || 0 == (int) $user_id || $content == "")
	{
		// Here we should introduce an error handling system...

		return false;
	}

	// Do not create if already exist

	$sql = "SELECT id FROM `".$TBL_USERINFO_CONTENT."`
			WHERE	`def_id`	= $def_id
			AND		`user_id`	= $user_id";

	$result = sql_query($sql) or die (WARNING_MESSAGE);

	if (mysql_num_rows($result) > 0)
	{
		return false;
	}


	$sql = "INSERT INTO `".$TBL_USERINFO_CONTENT."` SET
			`content`	= '$content',
			`def_id`	= $def_id,
			`user_id`	= $user_id,
			`ed_ip`		= '$user_ip',
			`ed_date`	= now()";

	sql_query($sql) or die (WARNING_MESSAGE);

	return true;
}

/**
 * edit a bloc for information category
 *
 * @author - Hugues peeters <peeters@ipm.ucl.ac.be>
 * @author - Christophe Gesché <gesche@ipm.ucl.ac.be>
 * @param  - $def_id,
 * @param  - $user_id,
 * @param  - $user_ip, DEFAULT $REMOTE_ADDR
 * @param  - $content ; if empty call delete the bloc
 * @return - boolean true if succeed, else bolean false
 */

function claro_user_info_edit_cat_content($def_id, $user_id, $content ="", $user_ip="")
{
	global $TBL_USERINFO_CONTENT;

	if (empty($user_ip))
	{
		global $REMOTE_ADDR;
		$user_ip = $REMOTE_ADDR;
	}

	if (0 == (int) $user_id || 0 == (int) $def_id)
	{
		return false;
	}

	$content = trim($content);

	if ( trim($content) == "")
	{
		return claro_user_info_cleanout_cat_content($user_id, $def_id);
	}


	$sql= "UPDATE `".$TBL_USERINFO_CONTENT."` SET
			`content`	= '$content',
			`ed_ip`		= '$user_ip',
			`ed_date`	= now()
			WHERE def_id = $def_id AND user_id = $user_id";

	sql_query($sql) or die (WARNING_MESSAGE);

	return true;
}

/**
 * clean the content of a bloc for information category
 *
 * @author - Hugues peeters <peeters@ipm.ucl.ac.be>
 * @author - Christophe Gesché <gesche@ipm.ucl.ac.be>
 * @param  - $def_id,
 * @param  - $user_id
 * @return - boolean true if succeed, else bolean false
 */

function claro_user_info_cleanout_cat_content($user_id, $def_id)
{
	global $TBL_USERINFO_CONTENT;

	if (0 == (int) $user_id || 0 == (int) $def_id)
	{
		return false;
	}

	$sql = "DELETE FROM `".$TBL_USERINFO_CONTENT."`
			WHERE user_id = $user_id  AND def_id = $def_id";

	sql_query($sql) or die (WARNIG_MESSAGE);

	return true;
}



/*----------------------------------------
     SHOW USER INFORMATION TREATMENT
 --------------------------------------*/

/**
 * get the user info from the user id
 * @author - Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @author - Christophe Gesché <gesche@ipm.ucl.ac.be>
 * @param - int $user_id user id as stored in the claroline main db
 * @return - array containg user info sort by categories rank
 *           each rank contains 'title', 'comment', 'content', 'cat_id'
 */


function claro_user_info_get_course_user_info($user_id)
{
	global $TBL_USERINFO_CONTENT, $TBL_USERINFO_DEF;

	$sql = "SELECT	cat.id catId,	cat.title,
					cat.comment ,	content.content
			FROM  	`".$TBL_USERINFO_DEF."` cat LEFT JOIN `".$TBL_USERINFO_CONTENT."` content
			ON cat.id = content.def_id 	AND content.user_id = '$user_id'
			ORDER BY cat.rank, content.id";

	$result = sql_query($sql) or die (WARNING_MESSAGE);

	if (mysql_num_rows($result) > 0)
	{
		while ($userInfo = mysql_fetch_array($result, MYSQL_ASSOC))
		{
			$userInfos[]=$userInfo;
		}

		return $userInfos;
	}

	return false;
}



/**
 * get the main user information
 * @author - Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @author - Christophe Gesché <gesche@ipm.ucl.ac.be>
 * @param -  int $user_id user id as stored in the claroline main db
 * @return - array containing user info as 'lastName', 'firstName'
 *           'email', 'role'
 */

function claro_user_info_get_main_user_info($user_id, $courseCode)
{
	if (0 == (int) $user_id)
	{
		return false;
	}

	global $mainDB;

	$sql = "SELECT	u.nom lastName, u.prenom firstName, 
	                u.email, u.pictureUri picture, cu.role, 
	                cu.`statut` `status`, cu.tutor
	        FROM    `".$mainDB."`.`user` u, `".$mainDB."`.cours_user cu
	        WHERE   u.user_id = cu.user_id
	        AND     u.user_id = $user_id
	        AND     cu.code_cours = \"$courseCode\"";

	$result = sql_query($sql) or die (WARNING_MESSAGE);

	if (mysql_num_rows($result) > 0)
	{
		$userInfo = mysql_fetch_array($result, MYSQL_ASSOC);
		return $userInfo;
	}

	return false;
}




/**
 * get the user content of a categories plus the categories definition
 * @author - Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @author - Christophe Gesché <gesche@ipm.ucl.ac.be>
 * @param  - int $userId - id of the user
 * @param  - int $catId - id of the categories
 * @return - array containing 'catId', 'title', 'comment',
 *           'nbline', 'contentId' and 'content'
 */

function claro_user_info_get_cat_content($userId, $catId)
{
	global $TBL_USERINFO_CONTENT, $TBL_USERINFO_DEF;

	$sql = "SELECT	cat.id catId,	cat.title,
					cat.comment ,	cat.nbline,
					content.id contentId, 	content.content
			FROM  	`".$TBL_USERINFO_DEF."` cat LEFT JOIN `".$TBL_USERINFO_CONTENT."` content
			ON cat.id = content.def_id
			AND content.user_id = '$userId'
			WHERE cat.id = '$catId' ";

	$result = sql_query($sql) or die (WARNING_MESSAGE);

	if (mysql_num_rows($result) > 0)
	{
		$catContent = mysql_fetch_array($result, MYSQL_ASSOC);
		return $catContent;
	}

	return false;
}


/**
 * get the definition of a category
 *
 * @author - Christophe Gesché <gesche@ipm.ucl.ac.be>
 * @author - Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param  - int $catId - id of the categories
 * @return - array containing 'id', 'title', 'comment', and 'nbline',
 */


function claro_user_info_get_cat_def($catId)
{
	global $TBL_USERINFO_DEF;

	$sql = "SELECT id, title, comment, nbline, rank FROM `".$TBL_USERINFO_DEF."` WHERE id = '$catId'";

	$result = sql_query($sql) or die (WARNING_MESSAGE);

	if (mysql_num_rows($result) > 0)
	{
		$catDef = mysql_fetch_array($result, MYSQL_ASSOC);
		return $catDef;
	}

	return false;
}


/**
 * get list of all this course categories
 *
 * @author - Christophe Gesché <gesche@ipm.ucl.ac.be>
 * @author - Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @return - array containing a list of arrays.
 *           And each of these arrays contains
 *           'catId', 'title', 'comment', and 'nbline',
 */


function claro_user_info_claro_user_info_get_cat_def_list()
{
	global $TBL_USERINFO_DEF;

	$sql = "SELECT	id catId,	title,	comment , nbline
			FROM  `".$TBL_USERINFO_DEF."`
			ORDER BY rank";

	$result = sql_query($sql) or die (WARNING_MESSAGE);

	if (mysql_num_rows($result) > 0)
	{
		while ($cat_def = mysql_fetch_array($result, MYSQL_ASSOC))
		{
			$cat_def_list[]=$cat_def;
		}

		return $cat_def_list;
	}

	return false;
}

/**
 * transform content in a html display
 * @author - Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param  - string $string string to htmlize
 * @ return  - string htmlized
 */

function htmlize($phrase)
{
	return claro_parse_user_text(htmlspecialchars($phrase));
}


/**
 * replaces some dangerous character in a string for HTML use
 *
 * @author - Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param  - string (string) string
 * @return - the string cleaned of dangerous character
 */

function replace_dangerous_char($string)
{
	$search[]="/" ; $replace[]="-";
	$search[]="\|"; $replace[]="-";
	$search[]="\""; $replace[]=" ";

	foreach($search as $key=>$char )
	{
		$string = str_replace($char, $replace[$key], $string);
	}

	return $string;
}

//////////////////////////////////////////////////////////////////////////////
//                                DEBUG
//////////////////////////////////////////////////////////////////////////////

function sql_query($query)
{
	// echo "<pre style='color:navy'>",$query,"</pre>\n";
	$handle = mysql_query($query);
	if (mysql_errno())
	    echo "<pre style='color:green'>".mysql_errno().": ".mysql_error()."\n".$query."</pre><hr>";
	return $handle;
}

function debug($var)
{
	echo "<pre  style='color:green'>";
	var_dump($var);
	echo "</pre>\n";
}
//////////////////////////////////////////////////////////////////////////////
 ?>
