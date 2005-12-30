<?php # -$Id$

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

/*==================================
  DISPLAY COURSES LIST OF A CATEGORY
  ==================================*/

// Prevent direct reference to this script
if ((bool) stristr($_SERVER['PHP_SELF'], basename(__FILE__))) die();


if ( !empty ($_REQUEST['category']) ) $category = $_REQUEST['category'];
else                                  $category = null;


$categoryBrowser = new category_browser($category);

$parentCategory = $categoryBrowser->get_current_category_settings();
$categoryList   = $categoryBrowser->get_sub_category_list();
$courseList     = $categoryBrowser->get_course_list();

if ( trim($category) != '' ) // means that we are not on the root level of the category tree
{
    $backCommandLine = "<p>"
                      ."<small>"
                      ."<a href=\"".$_SERVER['PHP_SELF']."?category=".$parentCategory['code_P']."\">"
                      ."&lt;&lt; ".get_lang('PreviousLevel')
                      ."</a>"
                      ."</small>"
                      ."</p>";

    $pageTitle      = $parentCategory['name'];
}
else
{
    $backCommandLine = "<p>&nbsp;</p>";
    $pageTitle       = get_lang('Categories');
}

echo $backCommandLine;

echo claro_disp_tool_title($pageTitle);

if ( ( count($categoryList) - 1 )  >= 0 )
{
    echo '<h4>'.get_lang('Categories').'</h4>' . "\n";
    echo '<ul>'                                . "\n";

    foreach($categoryList as $thisCategory)
    {
        echo '<li>'                                           ."\n";

        if ( $thisCategory['nbCourse'] + $thisCategory['nb_childs'] > 0 )
        {
            echo '<a href="'.$_SERVER['PHP_SELF'].'?category='.$thisCategory['code'].'">'."\n"
            .    $thisCategory['name']
            .    '</a>'                                                                  ."\n"
            ;
        }
        else
        {
            echo $thisCategory['name'] . "\n";
        }

        echo ' <small>('.$thisCategory['nbCourse'].')</small>'."\n"
        .    '</li>'                                          . "\n"
        ;
    }

    echo "</ul>\n";
}

if ( count($courseList) > 0 )
{
   if ( ( count($categoryList) - 1 )  > 0 )
   {
       echo "<hr size=\"1\" noshade=\"noshade\">\n";
   }

    echo "<h4>".get_lang('CourseList')."</h4>\n"
        ."<ul style=\"list-style-image:url(claroline/img/course.gif);\">\n";

    foreach($courseList as $thisCourse)
    {
        echo "<li>\n"

            ."<a href=\"".$coursesRepositoryWeb.$thisCourse['directory']."/\">"
            .$thisCourse['officialCode']." - "
            .$thisCourse['title']
            ."</a>"
            ."<br>"
            ."<small>".$thisCourse['titular']."</small>\n"

            ."</li>\n";
    }

	echo "</ul>\n";

}
else
{
	// echo "<blockquote>",$lang_No_course_publicly_available,"</blockquote>\n";
}


echo $backCommandLine;


echo '</td>';

//////////////////////////////////////////////////////////////////////////////

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



?>