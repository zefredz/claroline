<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );


if ( !empty ($_REQUEST['category']) ) $category = $_REQUEST['category'];
else                                  $category = null;



if ( isset($_REQUEST['cmd']) && $_REQUEST['cmd'] == 'search')
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
                      .'<a href="'.$_SERVER['PHP_SELF']."?category=". urlencode($parentCategory['code_P']) .'">'
                      .'&lt;&lt; '.get_lang('previous level')
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

echo claro_html_tool_title($pageTitle);

if ( ( count($categoryList) - 1 )  >= 0 )
{

    echo claro_html_title(get_lang('Categories'),4)
    .    '<ul>'  . "\n"
    ;

    foreach($categoryList as $thisCategory)
    {
        echo '<li>'                                           ."\n";

        if ( $thisCategory['nbCourse'] + $thisCategory['nb_childs'] > 0 )
        {
            echo '<a href="'.$_SERVER['PHP_SELF'].'?category='. urlencode($thisCategory['code']) .'">'
            .    $thisCategory['name']
            .    '</a>'
            ;
        }
        else
        {
            echo $thisCategory['name'];
        }

        echo ' <small>(' . $thisCategory['nbCourse'] . ')</small>'."\n"
        .    '</li>'                                              . "\n"
        ;
    }

    echo '</ul>' . "\n";
}

if ( count($courseList) > 0 )
{
    if ( ( count($categoryList) - 1 )  > 0 )
    {
        echo '<hr size="1" noshade="noshade" />' . "\n";
    }

    echo '<h4>'.get_lang('Course list').'</h4>' . "\n"
    .    '<ul style="list-style-image:url(claroline/img/course.gif);">' . "\n";

    foreach($courseList as $thisCourse)
    {
        echo '<li>' . "\n"
        .    '<a href="' .   get_path('url') . '/claroline/course/index.php?cid=' . htmlspecialchars($thisCourse['sysCode']) . '">'
        .    $thisCourse['officialCode'] . ' - '
        .    $thisCourse['title']
        .    '</a>'
        .    '<br />';
        if (claro_is_user_authenticated())
        {
            echo '<small><a href="mailto:'.$thisCourse['email'].'">' . $thisCourse['titular'] . '</a></small>' . "\n";
        }
        else
        {
            echo '<small>' . $thisCourse['titular'] . '</small>' . "\n";
        }

        echo '</li>' . "\n";
    }

    echo '</ul>' . "\n";

}
else
{
    if ( isset($_REQUEST['cmd']) && $_REQUEST['cmd'] = 'search')
    {
        echo '<blockquote>' . get_lang('Your search did not match any courses') . '</blockquote>' . "\n";
    }
}

echo "\n" . '<blockquote>' . "\n"
.    '<p><label for="keyword">' . get_lang('Search from keyword') . '</label> : </p>' . "\n"
.    '<form method="post" action="' . $_SERVER['PHP_SELF'] . '">' . "\n"
.    '<input type="hidden" name="cmd" value="search" />' . "\n"
.    '<input type="text" name="keyword" id="keyword" />' . "\n"
.    '&nbsp;<input type="submit" value="' . get_lang('Search') . '" />' . "\n"
.    '</form>' . "\n"
.    '</blockquote>' . "\n";

echo $backCommandLine;
