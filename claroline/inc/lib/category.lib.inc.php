<?php

/**
 * CLAROLINE
 *
 * SQL requests for claroCategory Class
 *
 * @version 1.10 $Revision: 11894 $
 *
 * @copyright 2001-2010 Universite catholique de Louvain (UCL)
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @author Claro Team <cvs@claroline.net>
 * @author Antonin Bourguignon <antonin.bourguignon@claroline.net>
 * 
 * Development notes
 * /////////////////
 * 
 * * Table names are stored in inc/lib/sql.lib.php
 * * Inputs are secured through 
 * 	 - function Claroline::getDatabase()->quote($str) for strings
 * 	 - casting into int for integers
 * 
 */


/**
 * Get datas for a category
 * 
 * @param $id identifier of the category
 * @return $array containing category datas
 */
function claro_get_cat_datas($id)
{
	// Get table name
	$tbl_mdb_names   = claro_sql_get_main_tbl();
	$tbl_category    = $tbl_mdb_names['category_dev'];
	        
	$sql = "SELECT
			c.id					AS id,
			c.name					AS name,
			c.code					AS code,
			c.idParent				AS idParent,
			c.rank					AS rank,
			c.visible				AS visible,
			c.canHaveCoursesChild	AS canHaveCoursesChild

			FROM `" . $tbl_category . "` AS c
			WHERE c.id = " . (int) $id . "";
	
	$result = Claroline::getDatabase()->query($sql);
	
	return $result->fetch();
}


/**
 * Return the predecessor of a specified category (based on the rank attribute).  Ranks can 
 * be discontinued (1, 2, 4, 7, ...), so we can't just perform a $rank-1 to get the direct 
 * predecessor of a category.
 * 
 * @param $rank of the category that you want the predecessor
 * @param $idParent of the category that you want the predecessor
 * @return int $id of the direct predecessor (if any)
 */
function claro_get_previous_cat_datas($rank, $idParent)
{
	// Get table name
	$tbl_mdb_names   = claro_sql_get_main_tbl();
	$tbl_category    = $tbl_mdb_names['category_dev'];
	
	// Retrieve all the predecessors
	$sql = "SELECT id 
			FROM `" . $tbl_category . "` 
			WHERE idParent = " . (int) $idParent . "
			AND rank < " . (int) $rank . "
			ORDER BY `rank` DESC";
	
	$result = Claroline::getDatabase()->query($sql);
	
	// Are there any predecessors ?	
	$nbPredecessors = $result->count();
	
	if ( $nbPredecessors > 0 )
	{
		// Get the closest predecessor
		$result->rewind();
		return $result->fetch(Database_ResultSet::FETCH_VALUE);
	}
	else 
	{
		return false;
	}
}


/**
 * Return the successor of a specified category (based on the rank attribute).  Ranks can 
 * be discontinued (1, 2, 4, 7, ...), so we can't just perform a $rank+1 to get the direct 
 * successor of a category.
 * 
 * @param $rank of the category that you want the successor
 * @param $idParent of the category that you want the successor
 * @return int $id of the direct successor (if any)
 */
function claro_get_following_cat_datas($rank, $idParent)
{
	// Get table name
	$tbl_mdb_names   = claro_sql_get_main_tbl();
	$tbl_category    = $tbl_mdb_names['category_dev'];
	
	// Retrieve all the successors
	$sql = "SELECT id
			FROM `" . $tbl_category . "` 
			WHERE idParent = " . (int) $idParent . "
			AND rank > " . (int) $rank . "
			ORDER BY `rank` ASC";
	
	$result = Claroline::getDatabase()->query($sql);
	
	// Are there any successors ?
	$nbSuccessors = $result->count();
	
	if ( $nbSuccessors > 0 )
	{
		// Get the closest successor
		$result->rewind();
		return $result->fetch(Database_ResultSet::FETCH_VALUE);
	}
	else 
	{
		return false;
	}
}


/**
 * Return all the categories from the node $parent
 *
 * @param $parent the parent from wich we want to get the categories tree
 * @param $level the level where we start (default: 0)
 * @return $result_array containing all the categories organized hierarchically and ordered by rank
 */
function claro_get_all_categories($parent, $level = '0')
{
    // Get table name
    $tbl_mdb_names             = claro_sql_get_main_tbl();
    $tbl_category              = $tbl_mdb_names['category_dev'];
    $tbl_rel_course_category   = $tbl_mdb_names['rel_course_category'];
    
	// Retrieve all children of the id $parent
	$sql = "SELECT COUNT(rcc.courseId) AS nbCourses, c.id, c.name, c.code, c.idParent, c.rank, c.visible, c.canHaveCoursesChild 
			FROM `" . $tbl_category . "` AS c LEFT JOIN `" . $tbl_rel_course_category . "` AS rcc
			ON rcc.categoryId = c.id
			WHERE idParent = " . (int) $parent . "
			GROUP BY c.`id`
			ORDER BY c.`rank`";
	
	$result = Claroline::getDatabase()->query($sql);
	$result_array = array();
	
	// Get each child
	foreach ( $result as $row ) 
	{
		$row['level'] = $level;
		$result_array[] = $row;
		// Call this function again to get the next level of the tree
		$result_array = array_merge( $result_array, claro_get_all_categories($row['id'], $level+1) );
	}
	
	return $result_array;
}


/**
 * Return the identifiers of all the parents of a category.
 * Reserved category 0 never has any parent.
 *
 * @param $id identifier of the specified category
 * @return $result_array containing all the identifiers of the parents
 */
function claro_get_parents_ids($id)
{
    // Get table name
    $tbl_mdb_names             = claro_sql_get_main_tbl();
    $tbl_category              = $tbl_mdb_names['category_dev'];
    
	// Retrieve parent of the category
	$sql = "SELECT idParent
			FROM `" . $tbl_category . "`
			WHERE id = " . (int) $id . "";
	
	$result = Claroline::getDatabase()->query($sql);
	
	if (!$result->isEmpty())
	{
		$result->rewind();
		
		$result_array = array();
		
		// Keep going up until reaching the root
		if ( $temp['idParent'] != 0 )
		{
			$result_array[] = $result->fetch(Database_ResultSet::FETCH_VALUE);
			$result_array = array_merge( $result_array, claro_get_parents_ids($temp['idParent']) );
		}
	
	    return $result_array;
	}
	else
	{
	    return array();
	}
}


/**
 * Insert a category in database (with rank following the last category of the same parent)
 * 
 * @param $name name of the category
 * @param $code code of the category
 * @param $idParent id of the parent category (default: 0)
 * @param $rank position in the tree's level // Not used
 * @param $visible (default: 1)
 * @param $canHaveCoursesChild authorized to possess courses (default: 1)
 * @return handler
 */
function claro_insert_cat_datas($name, $code, $idParent, $rank, $visible, $canHaveCoursesChild)
{
	// Get table name
	$tbl_mdb_names    = claro_sql_get_main_tbl();
	$tbl_category     = $tbl_mdb_names['category_dev'];
	
	// Get the higher rank for the designated parent
	$sql = "SELECT MAX(rank) AS maxRank 
			FROM `" . $tbl_category . "` 
			WHERE idParent=" . (int) $idParent;
	
	$result = Claroline::getDatabase()->query($sql);
	$result->rewind();
	
	$newRank = $result->fetch(Database_ResultSet::FETCH_VALUE) + 1;
	
    $sql = "INSERT INTO `" . $tbl_category . "` SET 
            `name`					= " . Claroline::getDatabase()->quote($name) . ",
            `code`					= " . Claroline::getDatabase()->quote($code) . ",
            `idParent`				= " . (int) $idParent . ", 
            `rank`					= " . $newRank. ",
            `visible`				= " . (int) $visible . ",
            `canHaveCoursesChild`	= " . (int) $canHaveCoursesChild;
    
    return Claroline::getDatabase()->exec($sql);
}


/**
 * Update datas of a category.  If the parent ($idParent) is modified, category's rank will 
 * follow the last category of the new parent.
 * 
 * @param $id identifier of the category
 * @param $name name of the category
 * @param $code code of the category
 * @param $idParent id of the parent category (default: 0)
 * @param $rank position in the tree's level
 * @param $visible (default: 1)
 * @param $canHaveCoursesChild authorized to possess courses (default: 1)
 * @return handler
 */
function claro_update_cat_datas($id, $name, $code, $idParent, $rank, $visible, $canHaveCoursesChild)
{
	// Get table name
	$tbl_mdb_names   = claro_sql_get_main_tbl();
	$tbl_category    = $tbl_mdb_names['category_dev'];
	
	// New parent ?
	$sql = "SELECT idParent 
			FROM `" . $tbl_category . "` 
			WHERE id=" . (int) $id;
	
	$result = Claroline::getDatabase()->query($sql);
	$result->rewind();
	
	if ($result->fetch(Database_ResultSet::FETCH_VALUE) == $idParent) // Parent hasn't changed
	{
	    $sql = "UPDATE `" . $tbl_category . "` SET
	            `name`					= " . Claroline::getDatabase()->quote($name) . ",
	            `code`					= " . Claroline::getDatabase()->quote($code) . ",
	            `rank`					= " . (int) $rank . ",
	            `visible`				= " . (int) $visible . ",
	            `canHaveCoursesChild`	= " . (int) $canHaveCoursesChild . "
	            WHERE id = " . (int) $id;
	}
	else // Parent has changed
	{
		// Get the higher rank for the designated new parent
		$sql = "SELECT MAX(rank) AS maxRank 
				FROM `" . $tbl_category . "` 
				WHERE idParent=" . (int) $idParent;
		
		$result = Claroline::getDatabase()->query($sql);
		$result->rewind();
		
		$newRank = $result->fetch(Database_ResultSet::FETCH_VALUE) + 1;
		
		// Update datas
	    $sql = "UPDATE `" . $tbl_category . "` SET
	            `name`					= " . Claroline::getDatabase()->quote($name) . ",
	            `code`					= " . Claroline::getDatabase()->quote($code) . ",
	            `idParent`				= " . (int) $idParent . ", 
	            `rank`					= " . $newRank. ",
	            `visible`				= " . (int) $visible . ",
	            `canHaveCoursesChild`	= " . (int) $canHaveCoursesChild . "
	            WHERE id = " . (int) $id;
	}
    
    return Claroline::getDatabase()->exec($sql);
}


/**
 * Delete datas of a category
 * 
 * @param $id identifier of the category
 * @return handler
 */
function claro_delete_cat_datas($id)
{
	// Get table name
	$tbl_mdb_names   = claro_sql_get_main_tbl();
	$tbl_category    = $tbl_mdb_names['category_dev'];
	
    $sql = "DELETE FROM `" . $tbl_category . "` 
        	WHERE id = " . (int) $id . "";
    
    return Claroline::getDatabase()->exec($sql);
}


/**
 * Update the visibility value for a category
 * 
 * @param $id identifier of the category
 * @param $visibility 
 */
function claro_set_cat_visibility($id, $visible)
{
    // Get table name
    $tbl_mdb_names             = claro_sql_get_main_tbl();
    $tbl_category              = $tbl_mdb_names['category_dev'];
    
    $sql = "UPDATE `" . $tbl_category . "` SET
            visible	= " . (int) $visible . "
        	WHERE id = '" . (int) $id . "'";
    
    return Claroline::getDatabase()->exec($sql);
}


/**
 * Count the number of courses directly attached to the category
 * 
 * @param $id identifier of the category
 * @return integer number of courses
 */
function claro_count_courses($id)
{
    // Get table name
    $tbl_mdb_names             = claro_sql_get_main_tbl();
    $tbl_rel_course_category   = $tbl_mdb_names['rel_course_category'];
    
    $sql = "SELECT COUNT(courseId) as nbCourses 
    		FROM `" . $tbl_rel_course_category . "`
        	WHERE categoryId = " . (int) $id;
    
	$result = Claroline::getDatabase()->query($sql);
	$result->rewind();
	
	return $result->fetch(Database_ResultSet::FETCH_VALUE);
}


/**
 * Count the number of sub categories directly attached to the category
 * 
 * @param $id identifier of the category
 * @return integer number of sub categories
 */
function claro_count_sub_categories($id)
{
    // Get table name
    $tbl_mdb_names             = claro_sql_get_main_tbl();
    $tbl_category              = $tbl_mdb_names['category_dev'];
    
    $sql = "SELECT COUNT(id) as nbSubCategories
    		FROM `" . $tbl_category . "`
        	WHERE idParent = " . (int) $id;
    
	$result = Claroline::getDatabase()->query($sql);
	$result->rewind();
	
	return $result->fetch(Database_ResultSet::FETCH_VALUE);
}


/**
 * Count the number of categories having a specific value for the code attribute.  You can ignore 
 * a specific id in the counting.
 * 
 * @param $id the id that we want to ignore in the request
 * @param $code the code's value we search for
 * @return integer number of categories matching this value
 */
function claro_count_code($id = null, $code)
{
    // Get table name
    $tbl_mdb_names             = claro_sql_get_main_tbl();
    $tbl_category              = $tbl_mdb_names['category_dev'];
    
    $sql = "SELECT COUNT(id) nbMatching
    		FROM `" . $tbl_category . "`
        	WHERE code = " . Claroline::getDatabase()->quote($code);

    if (!is_null($id)) 
    	$sql .= " AND id != " . (int) $id;
    
	$result = Claroline::getDatabase()->query($sql);
	$result->rewind();
	
	return $result->fetch(Database_ResultSet::FETCH_VALUE);
}

?>