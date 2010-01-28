<?php

/**
 * Get datas for a category
 * 
 * @param $id identifier of the category
 * @return $array containing datas
 */
function claro_get_cat_datas($id)
{
	// Get table name
	$tbl_mdb_names   = claro_sql_get_main_tbl();
	$tbl_category    = $tbl_mdb_names['category_dev'];
	        
	$sql =  "SELECT
			c.id					AS id,
			c.name					AS name,
			c.code					AS code,
			c.idParent				AS idParent,
			c.rank					AS rank,
			c.visible				AS visible,
			c.canHaveCoursesChild	AS canHaveCoursesChild

			FROM `" . $tbl_category . "` AS c
			WHERE c.id = '" . claro_sql_escape($id) . "'";
	
	return claro_sql_query_get_single_row($sql);
	
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
			WHERE idParent='" . claro_sql_escape($idParent) . "'";
	
	$result = claro_sql_query_get_single_row($sql);
	$newRank = $result['maxRank'] + 1;
	mysql_free_result($result);
	
    $sql = "INSERT INTO `" . $tbl_category . "` SET 
            `name`					= '" . claro_sql_escape($name) . "',
            `code`					= '" . claro_sql_escape($code) . "',
            `idParent`				= '" . (is_null(claro_sql_escape($idParent))?(0):(claro_sql_escape($idParent))) . "', 
            `rank`					= '" . $newRank. "',
            `visible`				= '" . (is_null(claro_sql_escape($visible))?(1):(0)) . "',
            `canHaveCoursesChild`	= '" . (is_null(claro_sql_escape($canHaveCoursesChild))?(1):(0)) . "'";
    
    return claro_sql_query($sql);
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
			WHERE id='" . $id . "'";
	
	$result = claro_sql_query_get_single_row($sql);
	
	if ($result['idParent'] == $idParent) // Parent hasn't changed
	{
	    $sql = "UPDATE `" . $tbl_category . "` SET
	            `name`					= '" . claro_sql_escape($name) . "',
	            `code`					= '" . claro_sql_escape($code) . "',
	            `visible`				= '" . (is_null(claro_sql_escape($visible))?(1):(0)) . "',
	            `canHaveCoursesChild`	= '" . (is_null(claro_sql_escape($canHaveCoursesChild))?(1):(0)) . "'
	            WHERE id = '" . claro_sql_escape($id) . "'";
	}
	else // Parent has changed
	{
		// Get the higher rank for the designated parent
		$sql = "SELECT MAX(rank) AS maxRank 
				FROM `" . $tbl_category . "` 
				WHERE idParent='" . claro_sql_escape($idParent) . "'";
		
		$result = claro_sql_query_get_single_row($sql);
		$newRank = $result['maxRank'] + 1;
		
	    $sql = "UPDATE `" . $tbl_category . "` SET
	            `name`					= '" . claro_sql_escape($name) . "',
	            `code`					= '" . claro_sql_escape($code) . "',
	            `idParent`				= '" . (is_null(claro_sql_escape($idParent))?(0):(claro_sql_escape($idParent))) . "', 
	            `rank`					= '" . $newRank. "',
	            `visible`				= '" . (is_null(claro_sql_escape($visible))?(1):(0)) . "',
	            `canHaveCoursesChild`	= '" . (is_null(claro_sql_escape($canHaveCoursesChild))?(1):(0)) . "'
	            WHERE id = '" . claro_sql_escape($id) . "'";
	}
    
    return claro_sql_query($sql);
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
        	WHERE id = '" . claro_sql_escape($id) . "'";
    
    return claro_sql_query($sql);
}


/**
 * Return an array containing all the categories from the node $parent
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
    
	//Retrieve all children of the id $parent
	$sql = "SELECT COUNT(rcc.courseId) AS nbCourses, c.id, c.name, c.code, c.idParent, c.rank, c.visible, c.canHaveCoursesChild 
			FROM `" . $tbl_category . "` AS c LEFT JOIN `" . $tbl_rel_course_category . "` AS rcc
			ON rcc.categoryId = c.id
			WHERE idParent='" . claro_sql_escape($parent) . "'
			GROUP BY c.`id`
			ORDER BY c.`rank`";
	
	$result = claro_sql_query_fetch_all($sql);
	$result_array = array();
	
	//Get each child
	foreach ( $result as $row ) 
	{
		$row['level'] = $level;
		$result_array[] = $row;
		// call this function again to display the next level of the tree
		$result_array = array_merge( $result_array, claro_get_all_categories($row['id'], $level+1) );
	}
	
	return $result_array;
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
            visible	= '" . claro_sql_escape($visible) . "'
        	WHERE id = '" . claro_sql_escape($id) . "'";
    
    return claro_sql_query($sql);
}


/**
 * Count the number of courses directly attached to the category
 * 
 * @param $id identifier of the category
 * @return integer number of courses
 */
function claro_count_category_courses($id)
{
    // Get table name
    $tbl_mdb_names             = claro_sql_get_main_tbl();
    $tbl_rel_course_category   = $tbl_mdb_names['rel_course_category'];
    
    $sql = "SELECT COUNT(courseId) as nbCourses 
    		FROM `" . $tbl_rel_course_category . "`
        	WHERE categoryId = '" . claro_sql_escape($id) . "'";
    
    return claro_sql_query_get_single_row($sql);
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
    
    $sql = "SELECT id 
    		FROM `" . $tbl_category . "`
        	WHERE code = '" . claro_sql_escape($code) . "'";

    if (!is_null($id)) 
    	$sql .= " AND id != '" . claro_sql_escape($id) . "'";
    
    return claro_sql_query_get_single_row($sql);
}

?>