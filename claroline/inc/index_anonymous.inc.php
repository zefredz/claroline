<?php // $Id$

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

echo "<table width=\"100%\" border=\"0\" cellpadding=\"4\" >\n\n"
    ."<tr>\n"
    ."<td valign=\"top\">\n";

@include './infoImportante.html'; // Previous text zone, kept for ascending 
                                // compatibility with claroline 1.4

@include './textzone_top.inc.html'; // Introduction message if needed


/*==================================
  DISPLAY COURSES LIST OF A CATEGORY
  ==================================*/

/*
 * GET THE COURSES INSIDE THE REQUESTED CATEGORY
 */

if ( !empty ($_REQUEST['category']) )
    $category = $_REQUEST['category'];
else
    $category = NULL;

$sql = "SELECT `intitule`   `title`,
               `titulaires` `titular`,
               `code`       `sysCode`,
               `fake_code`  `officialCode`,
               `directory` 
        FROM `".$tbl_courses."` 
        WHERE `faculte` = '".$category."'
        ORDER BY UPPER(fake_code)";

$courseList = claro_sql_query_fetch_all($sql);

/*
 * GET THE SUB CATEGORIES OF THE REQUESTED CATEGORY
 */

$sql = "SELECT `faculte`.`code`  , `faculte`.`name`,
               `faculte`.`code_P`, `faculte`.`nb_childs`,
               COUNT( `cours`.`cours_id` ) `nbCourse`
        FROM `".$tbl_courses_nodes."` `faculte`

        LEFT JOIN `".$tbl_courses_nodes."` `subCat`
               ON (`subCat`.`treePos` >= `faculte`.`treePos`
              AND `subCat`.`treePos` <= (`faculte`.`treePos`+`faculte`.`nb_childs`) )

        LEFT JOIN `".$tbl_courses."` `cours`
               ON `cours`.`faculte` = `subCat`.`code` \n";

if ($category)
{
    $sql .= "WHERE UPPER(`faculte`.`code_P`) = UPPER(\"".$category."\")
                OR UPPER(`faculte`.`code`)   = UPPER(\"".$category."\") \n";
}
else
{
	$sql .= "WHERE `faculte`.`code`   IS NULL
		        OR `faculte`.`code_P` IS NULL \n";
}

$sql .= "GROUP  BY `faculte`.`code`
         ORDER BY  `faculte`.`treePos`";

$categoryList = claro_sql_query_fetch_all($sql);

// get the first category which is always the parent category (except at the root)
list( , $parentCategory) = each($categoryList);

if ( trim($category) != '' ) // means that we are not on the root level of the category tree
{
    $backCommandLine = "<p>"
                      ."<small>"
                      ."<a href=\"".$_SERVER['PHP_SELF']."?category=".$parentCategory['code_P']."\">"
                      ."&lt;&lt; ".$langPreviousLevel
                      ."</a>"
                      ."</small>"
                      ."</p>";

    $pageTitle      = $parentCategory['name'];
}
else
{
	$backCommandLine = "<p>&nbsp;</p>";
    $pageTitle       = $langCategories;
}

echo $backCommandLine;

claro_disp_tool_title($pageTitle);

if ( ( count($categoryList) - 1 )  > 0 )
{
    echo "<h4>".$langCategories."</h4>\n";
    echo "<ul>\n";

    foreach($categoryList as $thisCategory)
    {
        if ($thisCategory['code'] != $category) // jump the parent category
        {
            echo "<li>\n";

            if ( $thisCategory['nbCourse'] + $thisCategory['nb_childs'] > 0 )
            {
                echo "<a href=\"".$_SERVER['PHP_SELF']."?category=".$thisCategory['code']."\">\n"
                     .$thisCategory['name']."\n"
                     ."</a>\n"
                     ." <small>(".$thisCategory['nbCourse'].")</small>";

            }
            else
            {
                echo $thisCategory['name'];
            }

            echo "</li>\n";
		}
	}

	echo "</ul>\n";
}

if ( count($courseList) > 0 )
{
   if ( ( count($categoryList) - 1 )  > 0 )
   {
       echo "<hr size=\"1\" noshade=\"noshade\">\n";
   }

    echo "<h4>".$langCourseList."</h4>\n"
        ."<ul style=\"list-style-image:url(claroline/img/course.gif); 
\">\n";

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


/*=================================
  RIGHT MENU MENU (IDENTIFICATION)
  =================================*/
?>

<td width="200" valign="top" class="claroRightMenu">

<form action ="<?php echo $rootWeb,basename($_SERVER['PHP_SELF']); ?>" method="post">
<fieldset style="border: 1px solid gray; padding: 7px;">
<legend><?php echo $langAuthentication ?> : </legend>
<p>
<small>

<label for="login">
<?php echo $langUserName; ?><br>
<input type="text" name="login" id="login" size="12"><br>
</label>

<label for="password" >
<?php echo $langPassword ?><br>
<input type="password" name="password" id="password" size="12"><br>
</label>
<input type="submit" value="<?php echo $langEnter ?>" name="submitAuth">

</small>
</p>
</fieldset>
</form>

<?php
    if (isset($loginFailed) && $loginFailed)
    {
        claro_disp_message_box($langInvalidId);
    }
?>
<p>
<a href="claroline/auth/lostPassword.php"><?php echo $langLostPassword ?></a>
</p>
<?php
    if( $allowSelfReg || ! isset($allowSelfReg) )
    {
        ?>
        <p>
        <a href="claroline/auth/inscription.php"><?php echo $langCreateUserAccount ?></a>
        </p>
        <?php
    }

?>

<?php @include './textzone_right.inc.html'; ?>

</td>
</tr>
</table>
