<?php // $Id$

/**
 * CLAROLINE
 *
 * Test page.
 */

$cidReset = true;
$tidReset = true;

// Include Library and configuration files
require './claroline/inc/claro_init_global.inc.php'; // main init
require_once dirname(__FILE__) . '/claroline/inc/lib/course/courselist.lib.php';
require_once dirname(__FILE__) . '/claroline/inc/lib/clarocategory.class.php';

// Params
$userId = 1;
$categoryId = 4;
$date = Claroline::getInstance()->notification->get_notification_date($userId);

if (!empty($_REQUEST['viewCategory']))
{
    $categoryId = (int) $_REQUEST['viewCategory'];
    
    // CourseListIterator
    $uccl = new UserCategoryCourseList($userId, $categoryId);
    $uccli = $uccl->getIterator();
    
    // Hot courses
    $ncl = new NotifiedCourseList($userId);
    $modified_course = $ncl->getNotifiedCourseIdList();
    
    // User rights
    $cupList = new CourseUserPrivilegesList($userId);
    $cupList->load();
    
    // User categories
    $catList = ClaroCategory::getUserCategoriesFlat($userId);
    
    // CourseTree
    $ct = new CourseTree($uccli);
    
    // CourseTreeView
    $ctView = new CourseTreeView($ct->getRootNode(), $cupList, null, $catList, $categoryId);
    
    
}
else
{
    // CourseListIterator
    $ucl = new UserCourseList($userId);
    var_dump($ucl);
    $ucli = $ucl->getIterator();
    
    // Hot courses
    $ncl = new NotifiedCourseList($userId);
    $modified_course = $ncl->getNotifiedCourseIdList();
    
    // User rights
    $cupList = new CourseUserPrivilegesList($userId);
    $cupList->load();
    
    // User categories
    $catList = ClaroCategory::getUserCategoriesFlat($userId);
    
    // CourseTree
    $ct = new CourseTree($ucli);
    
    // CourseTreeView
    $ctView = new CourseTreeView($ct->getRootNode(), $cupList, null, $catList, null);
}



// Display
$out = '';

$out .= '<h1>Params</h1>'
      . '<p>$userId = ' . $userId . '<br />$date = ' . $date . '</p>'
      . '<h1>user_course_tree.tpl.php render</h1>'
      . $ctView->render()
      . '<h1>CourseTree::__toString()</h1>'
      . $ct;

// Render
$claroline->display->body->setContent($out);
echo $claroline->display->render();
