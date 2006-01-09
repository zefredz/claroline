<?php # -$Id$

/******************************************************************************
 * CLAROLINE
 ******************************************************************************
 * @copyright (c) 2001-2005 Universite catholique de Louvain (UCL)
 * @license (GPL) GENERAL PUBLIC LICENSE - http://www.gnu.org/copyleft/gpl.html
 ******************************************************************************/

class category_browser
{
    function category_browser($categoryCode = null)
    {
        $this->categoryCode = $categoryCode;

        $tbl_mdb_names          = claro_sql_get_main_tbl();
        $tbl_courses           = $tbl_mdb_names['course'  ];
        $tbl_courses_nodes     = $tbl_mdb_names['category'];

        $sql = "SELECT `faculte`.`code`  , `faculte`.`name`,
                       `faculte`.`code_P`, `faculte`.`nb_childs`,
                       COUNT( `cours`.`cours_id` ) `nbCourse`
                FROM `".$tbl_courses_nodes."` `faculte`

                LEFT JOIN `".$tbl_courses_nodes."` `subCat`
                       ON (`subCat`.`treePos` >= `faculte`.`treePos`
                      AND `subCat`.`treePos` <= (`faculte`.`treePos`+`faculte`.`nb_childs`) )

                LEFT JOIN `".$tbl_courses."` `cours`
                       ON `cours`.`faculte` = `subCat`.`code` \n";

        if ($categoryCode)
        {
            $sql .= "WHERE UPPER(`faculte`.`code_P`) = UPPER(\"".addslashes($categoryCode)."\")
                        OR UPPER(`faculte`.`code`)   = UPPER(\"".addslashes($categoryCode)."\") \n";
        }
        else
        {
            $sql .= "WHERE `faculte`.`code`   IS NULL
                        OR `faculte`.`code_P` IS NULL \n";
        }

        $sql .= "GROUP  BY `faculte`.`code`
                 ORDER BY  `faculte`.`treePos`";

        $this->categoryList = claro_sql_query_fetch_all($sql);
    }

    function get_current_category_settings()
    {
        if ($this->categoryCode) return $this->categoryList[0];
        else                     return null;
    }

    function get_sub_category_list()
    {
        if ($this->categoryCode) return array_slice($this->categoryList, 1);
        else                     return $this->categoryList;
    }

    function get_course_list()
    {
        $tbl_mdb_names = claro_sql_get_main_tbl();
        $tbl_courses   = $tbl_mdb_names['course'];

        $sql = "SELECT `intitule`   `title`,
                       `titulaires` `titular`,
                       `code`       `sysCode`,
                       `fake_code`  `officialCode`,
                       `directory` 
                FROM `".$tbl_courses."` 
                WHERE `faculte` = '".$this->categoryCode."'
                ORDER BY UPPER(fake_code)";

        return claro_sql_query_fetch_all($sql); 
    }
}

/**
 * search a specific course based on his course code
 *
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 *
 * @param  string  $courseCode course code from the cours table
 *
 * @return array    course parameters
 *         boolean  FALSE  otherwise.
 */

function search_course($keyword, $userId = null)
{
    $tbl_mdb_names        = claro_sql_get_main_tbl();
    $tbl_course           = $tbl_mdb_names['course'           ];
    $tbl_rel_course_user  = $tbl_mdb_names['rel_course_user'  ];

    $keyword = trim($keyword);

    if (empty($keyword) ) return array();
    $upperKeyword = trim(strtoupper($keyword));

    $sql = 'SELECT c.intitule   AS title,
                   c.titulaires AS titular,
                   c.fake_code  AS officialCode,
                   c.directory,
                   c.code,
                   c.visible '

         .  ($userId ? ', cu.user_id AS enrolled ' : '')

         .  'FROM `' . $tbl_course . '` c '

         .  ($userId ? 'LEFT JOIN `'.$tbl_rel_course_user.'` cu
                        ON  c.code = cu.code_cours
                        AND cu.user_id = "' . (int) $userId . '"'

                     :  '') 

         . 'WHERE (UPPER(fake_code)  LIKE "%'.$upperKeyword.'%"
               OR  UPPER(intitule)   LIKE "%'.$upperKeyword.'%"
               OR  UPPER(titulaires) LIKE "%'.$upperKeyword.'%")

            ORDER BY officialCode';

    $courseList = claro_sql_query_fetch_all($sql);

    if (count($courseList) > 0) return $courseList;
    else                        return false;
} // function search_course($keyword)

function get_user_course_list($userId, $renew = false)
{
    static $uid = null, $userCourseList = null;

    if ($uid != $userId || is_null($userCourseList) || $renew)
    {
        $uid = $userId;

        $tbl_mdb_names         = claro_sql_get_main_tbl();
        $tbl_courses           = $tbl_mdb_names['course'         ];
        $tbl_link_user_courses = $tbl_mdb_names['rel_course_user'];

        $sql = "SELECT course.code           `sysCode`,
                       course.directory      `directory`,
                       course.fake_code      `officialCode`,
                       course.dbName         `db`,
                       course.intitule       `title`,
                       course.titulaires     `titular`,
                       course.languageCourse `language`,
                       course_user.statut    `userSatus`

                       FROM `" . $tbl_courses . "`           course,
                            `" . $tbl_link_user_courses . "` course_user

                       WHERE course.code         = course_user.code_cours
                         AND course_user.user_id = '" . (int) $userId . "'";

        if ( get_conf('course_order_by') == 'official_code' )
        {
            $sql .= " ORDER BY UPPER(`fake_code`), `title`";
        }
        else
        {
            $sql .= " ORDER BY `title`, UPPER(`fake_code`)";
        }

        $userCourseList = claro_sql_query_fetch_all($sql);
    }
    
    return $userCourseList;
}


?>