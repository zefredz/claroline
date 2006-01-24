<?php

// Prevent direct reference to this script
if ((bool) stristr($_SERVER['PHP_SELF'], basename(__FILE__))) die();


if ( !empty ($_REQUEST['category']) ) $category = $_REQUEST['category'];
else                                  $category = null;



if ( isset($_REQUEST['cmd']) && $_REQUEST['cmd'] = 'search')
{
    $categoryList = array();
    $courseList = search_course($_REQUEST['keyword']);
}
else
{
    $categoryBrowser = new category_browser($category);
    $parentCategory = $categoryBrowser->get_current_category_settings();
    $categoryList   = $categoryBrowser->get_sub_category_list();
    $courseList     = $categoryBrowser->get_course_list();
}


if ( trim($category) != '' ) // means that we are not on the root level of the category tree
{
    $backCommandLine = '<p>'
                      .'<small>'
                      .'<a href="'.$_SERVER['PHP_SELF']."?category=".$parentCategory['code_P'].'">'
                      .'&lt;&lt; '.get_lang('PreviousLevel')
                      .'</a>'
                      .'</small>'
                      .'</p>'. "\n";

    $pageTitle      = $parentCategory['name'];
}
else
{
    $backCommandLine = '';
    $pageTitle       = get_lang('Platform Courses');
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
        echo '<li>' . "\n"
            . '<a href="' .  $urlAppend . '/claroline/course/index.php?cid=' . htmlspecialchars($thisCourse['sysCode']) . '">'
            . $thisCourse['officialCode'] . ' - '
            . $thisCourse['title']
            . '</a>'
            . '<br>'
            . '<small>' . $thisCourse['titular'] . '</small>' . "\n"
            . '</li>' . "\n";
    }

	echo '</ul>' . "\n";

}
else
{
	// echo "<blockquote>",$lang_No_course_publicly_available,"</blockquote>\n";
}

echo '<blockquote>' . "\n"
.    '<p><label for="keyword">' . get_lang('_or_search_from_keyword') . '</label> : </p>' . "\n"
.    '<form method="post" action="' . $_SERVER['PHP_SELF'] . '">' . "\n"
.    '<input type="hidden" name="cmd" value="search" />' . "\n"
.    '<input type="text" name="keyword" id="keyword" />' . "\n"
.    '&nbsp;<input type="submit" value="' . get_lang('Search') . '" />' . "\n"
.    '</form>' . "\n"
.    '</blockquote>' . "\n";

echo $backCommandLine;


echo '</td>';

?>
