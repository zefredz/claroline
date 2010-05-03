<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );

$categoryId   = ( !empty( $_REQUEST['category']) ) ? ( (int) $_REQUEST['category'] ) : ( 0 );

$out = '';

if ( isset($_REQUEST['cmd'] ) && $_REQUEST['cmd'] == 'search' )
{
    $categoriesList = array();
    $coursesList = search_course( $_REQUEST['keyword'] );
}
else
{
    $categoryBrowser    = new category_browser( $categoryId, claro_get_current_user_id() );
    $currentCategory    = $categoryBrowser->get_current_category_settings();
    $categoriesList     = $categoryBrowser->get_sub_category_list();
    $coursesList        = (!is_null(claro_get_current_user_id())) ? 
                            $categoryBrowser->getCoursesWithoutSourceCourses():
                            $categoryBrowser->getCoursesWithoutSessionCourses();
}

// Are we in the root of the category tree ?
// If not, give a link to go up in the tree
if ( $categoryId != 0 ) 
{
    $backCommandLine = '<p>'
                     . '<small>'
                     . '<a href="' . $_SERVER['PHP_SELF'] . "?category=" 
                     .  urlencode( $currentCategory->idParent ) . '#categoryContent">'
                     . '&larr; ' . get_lang( 'previous level' )
                     . '</a>'
                     . '</small>'
                     . '</p>'. "\n";
    
    $pageTitle = $currentCategory->name;
}
else
{
    $backCommandLine    = '';
    $pageTitle          = get_lang( 'Platform Courses' );
}

$out .= $backCommandLine;
$out .= claro_html_tool_title($pageTitle);
$out .= '<div class="hidden" id="categoryContent"></div>';

if ( ( count( $categoriesList ) - 1 ) >= 0 )
{
    $out .= claro_html_title( get_lang( 'Categories' ), 4 );
    $out .= '<ul>' . "\n";
    
    foreach( $categoriesList as $category )
    {
        $nbCourses          = claroCategory::countAllCourses($category['id']);
        $nbSubCategories    = claroCategory::countAllSubCategories($category['id']);
        
        $out .= '<li>' . "\n";
        
        // If the category contains something else (subcategory or course),
        // make a link to access to these ressources
        if ($nbCourses + $nbSubCategories > 0)
            $out .= '<a href="' . $_SERVER['PHP_SELF'] . "?category=" . urlencode( $category['id'] ) . '#categoryContent">' . $category['name'] . '</a>';
        else
            $out .= $category['name'];
        
        $out .= '</li>' . "\n";
    }
    
    $out .= '</ul>' . "\n";
}

if ( count( $coursesList ) > 0 )
{
    if ( ( count( $categoriesList ) - 1 ) > 0 )
    {
        $out .= '<hr size="1" noshade="noshade" />' . "\n";
    }
    
    $out .= '<h4>' . get_lang( 'Course list' ) . '</h4>' . "\n"
          . '<dl class="userCourseList">' . "\n";
    
    foreach( $coursesList as $course )
    {
        // Otherwise it will name normally be displayed
        // $hot = (bool) in_array ($course['sysCode'], $modified_course);
        $out .= render_course_dt_in_dd_list( $course, false );
    }
    
    $out .= '</dl>' . "\n";
}
else
{
    if ( isset($_REQUEST['cmd']) && $_REQUEST['cmd'] = 'search')
    {
        $out .= '<blockquote>' 
              . get_lang( 'Your search did not match any courses' ) 
              . '</blockquote>' . "\n";
    }
}

$out .= "\n" 
      . '<blockquote>' . "\n"
      . '<p><label for="keyword">' . get_lang( 'Search from keyword' ) . '</label> : </p>' . "\n"
      . '<form method="post" action="' . $_SERVER['PHP_SELF'] . '">' . "\n"
      . '<input type="hidden" name="cmd" value="search" />' . "\n"
      . '<input type="text" name="keyword" id="keyword" />' . "\n"
      . '&nbsp;<input type="submit" value="' . get_lang( 'Search' ) . '" />' . "\n"
      . '</form>' . "\n"
      . '</blockquote>' . "\n";

$out .= $backCommandLine;

echo $out;