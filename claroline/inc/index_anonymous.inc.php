<?php // $Id$
/**
 * CLAROLINE 
 *
 * this  is  the  home page  of a campus  for an anonymous user
 * this  page  prupose a directory of open courses of the campus
 * when the user is authenticated, index°authenticated.inc.php 
 * is load instead of this code.
 *
 * @version 1.7 $Revision$ 
 * @copyright (c) 2001-2005 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE 
 * 
 * @author claroline Team <cvs@claroline.net>
 *
 * @package CLINDEX
 *
 */

echo '<table width="100%" border="0" cellpadding="4" >' . "\n\n"
.    '<tr>'
.    '<td valign="top">' . "\n"
;


if (file_exists('./textzone_top.inc.html'))
include './textzone_top.inc.html'; // Introduction message if needed


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
               `directory` ,
               `languageCourse` `language`
        FROM `".$tbl_courses."` 
        WHERE `faculte` = '". addslashes($category) ."'";

if ( empty($course_order_by) || $course_order_by == 'official_code' )
{
    $sql .= " ORDER BY UPPER(`fake_code`), `title`";
}
else
{
    $sql .= " ORDER BY `title`, UPPER(`fake_code`)";
}

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
    $sql .= "WHERE UPPER(`faculte`.`code_P`) = UPPER(\"". addslashes($category) ."\")
                OR UPPER(`faculte`.`code`)   = UPPER(\"". addslashes($category) ."\") \n";
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
    $backCommandLine = '<p>'
                      .'<small>'
                      .'<a href="' . $_SERVER['PHP_SELF'] . '?category=' . $parentCategory['code_P'] . '">'
                      .'&lt;&lt; ' . get_lang('PreviousLevel')
                      .'</a>'
                      .'</small>'
                      .'</p>'
                      ;

    $pageTitle      = $parentCategory['name'];
}
else
{
	$backCommandLine = '<p>&nbsp;</p>';
    $pageTitle       = get_lang('Categories');
}

echo $backCommandLine;

echo claro_disp_tool_title($pageTitle);

    if ( ( count($categoryList) - 1 )  > 0 && $category != null) // don't display subtitle of categories if we are at root or if there is only one categorie
    {
        echo '<h4>' . get_lang('Categories') . '</h4>' . "\n";
    }
    echo '<ul>' . "\n";

    foreach($categoryList as $thisCategory)
    {
        if ($thisCategory['code'] != $category) // jump the parent category
        {
            echo '<li>' . "\n";

            if ( $thisCategory['nbCourse'] + $thisCategory['nb_childs'] > 0 )
            {
                echo '<a href="' . $_SERVER['PHP_SELF'] . '?category=' . $thisCategory['code'] . '">' . "\n"                     
                .    $thisCategory['name'] . "\n"
                .    '</a>' . "\n"
                .    '<small>(' . $thisCategory['nbCourse'] . ')</small>'
                ;

            }
            else
            {
                echo $thisCategory['name'];
            }

            echo '</li>' . "\n";
		}
	}

	echo '</ul>' . "\n";

if ( count($courseList) > 0 )
{
   if ( ( count($categoryList) - 1 )  > 0 )
   {
       echo '<hr size="1" noshade="noshade">' . "\n";
   }

    echo '<h4>' . get_lang('CourseList') . '</h4>' . "\n"
    .    '<ul style="list-style-image:url(claroline/img/course.gif);">' . "\n"
    ;

    foreach($courseList as $thisCourse)
    {
        // show course language if not the same of the platform
        if ( $platformLanguage!=$thisCourse['language'] ) 
        {
            if ( !empty($langNameOfLang[$thisCourse['language']]) )
            {
                $course_language_txt = ' - ' . ucfirst($langNameOfLang[$thisCourse['language']]);
            }
            else
            {
                $course_language_txt = ' - ' . ucfirst($thisCourse['language']);
            }
        }
        else
        {
            $course_language_txt = '';
        }

        echo '<li>' . "\n"
        .    '<a href="' . $coursesRepositoryWeb . $thisCourse['directory'] . '/">';

        if ( empty($course_order_by) || $course_order_by == 'official_code' )
        {
             echo $thisCourse['officialCode'] . ' - ' . $thisCourse['title'];
        }
        else
        {
             echo $thisCourse['title'] . ' (' . $thisCourse['officialCode'] . ')';
        }

        echo '</a>'
        .    '<br>'
        .    '<small>' . $thisCourse['titular'] . $course_language_txt . '</small>'
        .    '</li>' . "\n"
        ;
    }

	echo '</ul>' . "\n";

}
else
{
	// echo "<blockquote>",get_lang('_No_course_publicly_available'),"</blockquote>\n";
}

echo $backCommandLine;

echo '</td>';

/*=================================
  RIGHT MENU MENU (IDENTIFICATION)
  =================================*/
?>

<td width="200" valign="top" class="claroRightMenu">

<?php
if (isset($claro_CasEnabled) && $claro_CasEnabled) // CAS is a special cas of external authentication
{
    echo '<div align="center">'
    .    '<a href="'.$clarolineRepositoryWeb . 'auth/login.php?authModeReq=CAS">'
    .    (isset($claro_CasLoginString) ? $claro_CasLoginString : get_lang('Login'))
    .    '</a>'
    .    '</div>';
}

if( !isset($claro_displayLocalAuthForm) || $claro_displayLocalAuthForm == true )
{
?>
<form action ="<?php echo $clarolineRepositoryWeb . 'auth/login.php' ?>"  method="post">
<fieldset style="padding: 7px;">
<legend><?php echo get_lang('Authentication') ?> : </legend>
<p>
<small>

<label for="login">
<?php echo get_lang('UserName'); ?><br>
<input type="text" name="login" id="login" size="12"><br>
</label>

<label for="password" >
<?php echo get_lang('Password') ?><br>
<input type="password" name="password" id="password" size="12"><br>
</label>
<input type="submit" value="<?php echo get_lang('Enter') ?>" name="submitAuth">

</small>
</p>
</fieldset>
</form>

<?php
    if ( $claro_loginRequested && ! $claro_loginSucceeded )
    {
        if ( ! isset($allowSelfReg) || $allowSelfReg == FALSE)
        {
    		echo claro_disp_message_box(get_lang('InvalidId'));
        }
        else
        {
        	echo claro_disp_message_box(sprintf(get_lang('InvalidIdSelfReg'),$urlAppend.'/claroline/auth/inscription.php') );
        }
        
    }
?>
<p>
<a href="claroline/auth/lostPassword.php"><?php echo get_lang('LostPassword') ?></a>
</p>

<?php
    if( $allowSelfReg || ! isset($allowSelfReg) )
    {
        ?>
        <p>
        <a href="claroline/auth/inscription.php"><?php echo get_lang('CreateUserAccount') ?></a>
        </p>

        <?php
    }
} // end else if claro_CasEnabled

if ( file_exists('./textzone_right.inc.html') ) include './textzone_right.inc.html'; ?>

</td>
</tr>
</table>
