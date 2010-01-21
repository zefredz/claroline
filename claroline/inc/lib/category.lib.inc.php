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
			c.canHaveCoursesChild	AS canHaveCoursesChild, 
			c.canHaveCatChild		AS canHaveCatChild

			FROM `" . $tbl_category . "` AS c
			WHERE c.id = '" . claro_sql_escape($id) . "'";
	
	return claro_sql_query_get_single_row($sql);
	
}


/**
 * Insert datas for a category
 * 
 * @param $name name of the category
 * @param $code code of the category
 * @param $idParent id of the parent category (default: 0)
 * @param $rank position in the tree's level
 * @param $canHaveCoursesChild authorized to possess courses (TRUE or FALSE; default: TRUE)
 * @param $canHaveCoursesChild authorized to have sub categories (TRUE or FALSE; default: TRUE)
 * @return handler
 */
function claro_insert_cat_datas($name, $code, $idParent, $rank, $visible, $canHaveCoursesChild, $canHaveCatChild)
{
	// Get table name
	$tbl_mdb_names    = claro_sql_get_main_tbl();
	$tbl_category     = $tbl_mdb_names['category_dev'];
	
    $sql = "INSERT INTO `" . $tbl_category . "` SET
            name					= '" . claro_sql_escape($name) . "',
            code					= '" . claro_sql_escape($code) . "',
            idParent				= '" . is_null(claro_sql_escape($idParent))?(0):claro_sql_escape($idParent) . "',
            rank					= '" . is_null(claro_sql_escape($rank))?'NULL':claro_sql_escape($rank) . "',
            visible					= '" . claro_sql_escape($visible) . "'
            nbChildren				= '" . claro_sql_escape($nbChildren) . "',
            canHaveCoursesChild		= '" . ($canHaveCoursesChild?'TRUE':'FALSE') . "',
            canHaveCatChild			= '" . ($canHaveCatChild?'TRUE':'FALSE') . "'";
    
    return claro_sql_query($sql);
}


/**
 * Update datas of a category
 * 
 * @param $id identifier of the category
 * @param $name name of the category
 * @param $code code of the category
 * @param $idParent id of the parent category (default: 0)
 * @param $rank position in the tree's level
 * @param $canHaveCoursesChild authorized to possess courses (TRUE or FALSE; default: TRUE)
 * @param $canHaveCoursesChild authorized to have sub categories (TRUE or FALSE; default: TRUE)
 * @return handler
 */
function claro_update_cat_datas($id, $name, $code, $idParent, $rank, $visible, $canHaveCoursesChild, $canHaveCatChild)
{
	// Get table name
	$tbl_mdb_names   = claro_sql_get_main_tbl();
	$tbl_category    = $tbl_mdb_names['category_dev'];
	
    $sql = "UPDATE `" . $tbl_category . "` SET
            name					= '" . claro_sql_escape($name) . "',
            code					= '" . claro_sql_escape($code) . "',
            idParent				= '" . is_null(claro_sql_escape($idParent))?('NULL'):claro_sql_escape($idParent) . "',
            rank					= '" . is_null(claro_sql_escape($rank))?'NULL':claro_sql_escape($rank) . "',
            visibile				= '" . $visible . "'
            canHaveCoursesChild		= '" . ($canHaveCoursesChild?'TRUE':'FALSE') . "',
            canHaveCatChild			= '" . ($canHaveCatChild?'TRUE':'FALSE') . "'
            
            WHERE id='" . claro_sql_escape($id) . "'";
    
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
	$sql = "SELECT COUNT(rcc.courseId) AS nbCourses, c.id, c.name, c.code, c.idParent, c.rank, c.visible, c.canHaveCoursesChild, c.canHaveCatChild 
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
    		FROM `" . tbl_rel_course_category . "`
        	WHERE categoryId = '" . claro_sql_escape($id) . "'";
    
    return claro_sql_query_get_single_row($sql);
}

?>