<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );

if ( !empty( $_REQUEST['category']) ) $category = $_REQUEST['category'];
else                                  $category = null;

$out = '';

if ( isset($_REQUEST['cmd'] ) && $_REQUEST['cmd'] == 'search' )
{
    $categoryList = array();
    $courseList = search_course( $_REQUEST['keyword'] );
}
else
{
    $categoryBrowser = new category_browser( $category );
    $parentCategory  = $categoryBrowser->get_current_category_settings();
    $categoryList    = $categoryBrowser->get_sub_category_list();
    $courseList      = $categoryBrowser->get_course_list();
}

if ( trim( $category ) != '' ) // means that we are not on the root level of the category tree
{
    $backCommandLine = '<p>'
                      .'<small>'
                      .'<a href="' . $_SERVER['PHP_SELF'] . "?category=" . urlencode( $parentCategory['code_P'] ) . '">'
                      .'&lt;&lt; ' . get_lang( 'previous level' )
                      .'</a>'
                      .'</small>'
                      .'</p>'. "\n";

    $pageTitle = $parentCategory['name'];
}
else
{
    $backCommandLine = '';
    $pageTitle = get_lang( 'Platform Courses' );
}

$out .= $backCommandLine;

$out .= claro_html_tool_title($pageTitle);

if ( ( count( $categoryList ) - 1 )  >= 0 )
{

    $out .= claro_html_title( get_lang( 'Categories' ), 4 )
         .  '<ul>'  . "\n"
    ;

    foreach( $categoryList as $thisCategory )
    {
        $out .= '<li>' . "\n";

        if ( $thisCategory['nbCourse'] + $thisCategory['nb_childs'] > 0 )
        {
            $out .= '<a href="' . $_SERVER['PHP_SELF'] . '?category=' . urlencode( $thisCategory['code'] ) . '">'
                 .  $thisCategory['name']
                 .  '</a>'
            ;
        }
        else
        {
            $out .= $thisCategory['name'];
        }

        $out .= ' <small>(' . $thisCategory['nbCourse'] . ')</small>' . "\n"
             .  '</li>' . "\n"
        ;
    }

    $out .= '</ul>' . "\n";
}

if ( count( $courseList ) > 0 )
{
    if ( ( count( $categoryList ) - 1 )  > 0 )
    {
        $out .= '<hr size="1" noshade="noshade" />' . "\n";
    }

    $out .= '<h4>' . get_lang( 'Course list' ) . '</h4>' . "\n"
         .  '<dl class="userCourseList">' . "\n";

    foreach( $courseList as $thisCourse )
        {


        // Otherwise it will name normally be displayed
        //$hot = (bool) in_array ($thisCourse['sysCode'], $modified_course);
        $out .= render_course_dt_in_dd_list( $thisCourse, false );

    }
    $out .= '</dl>' . "\n";

}
else
{
    if ( isset($_REQUEST['cmd']) && $_REQUEST['cmd'] = 'search')
    {
        $out .= '<blockquote>' . get_lang( 'Your search did not match any courses' ) . '</blockquote>' . "\n";
    }
}

$out .= "\n" . '<blockquote>' . "\n"
     .  '<p><label for="keyword">' . get_lang( 'Search from keyword' ) . '</label> : </p>' . "\n"
     .  '<form method="post" action="' . $_SERVER['PHP_SELF'] . '">' . "\n"
     .  '<input type="hidden" name="cmd" value="search" />' . "\n"
     .  '<input type="text" name="keyword" id="keyword" />' . "\n"
     .  '&nbsp;<input type="submit" value="' . get_lang( 'Search' ) . '" />' . "\n"
     .  '</form>' . "\n"
     .  '</blockquote>' . "\n";

$out .= $backCommandLine;

echo $out;
