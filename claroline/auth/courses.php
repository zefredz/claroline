<?php // $Id$

/**
 * CLAROLINE
 *
 * Prupose list of course to enroll or leave.
 *
 * @version     $Revision$
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @author      Claro Team <cvs@claroline.net>
 * @package     AUTH
 */

require '../inc/claro_init_global.inc.php';

$nameTools  = get_lang('User\'s course');
$noPHP_SELF = true;

/*---------------------------------------------------------------------
Security Check
---------------------------------------------------------------------*/

if ( ! claro_is_user_authenticated() ) claro_disp_auth_form();
$can_see_hidden_course = claro_is_platform_admin();

/*---------------------------------------------------------------------
Include Files and initialize variables
---------------------------------------------------------------------*/

require_once get_path('incRepositorySys') . '/lib/user.lib.php';
require_once get_path('incRepositorySys') . '/lib/course_user.lib.php';
require_once get_path('incRepositorySys') . '/lib/class.lib.php';
require_once get_path('incRepositorySys') . '/lib/courselist.lib.php';
require_once get_path('incRepositorySys') . '/lib/coursesearchbox.class.php';

include claro_get_conf_repository() . 'user_profile.conf.php';
include claro_get_conf_repository() . 'course_main.conf.php';

$parentCategoryCode = '';
$userSettingMode    = false;
$dialogBox          = new DialogBox();
$coursesList        = array();
$categoriesList     = array();

/*---------------------------------------------------------------------
Define Display
---------------------------------------------------------------------*/

define ('DISPLAY_USER_COURSES',                 __LINE__);
define ('DISPLAY_COURSE_TREE',                  __LINE__);
define ('DISPLAY_MESSAGE_SCREEN',               __LINE__);
define ('DISPLAY_REGISTRATION_KEY_FORM',        __LINE__);
define ('DISPLAY_REGISTRATION_DISABLED_FORM',   __LINE__);

$displayMode = DISPLAY_USER_COURSES; // default display

/*---------------------------------------------------------------------
Get request variables
---------------------------------------------------------------------*/

$cmd        = ( isset($_REQUEST['cmd']) ) ? ( $_REQUEST['cmd'] ) : ( '' );
$uidToEdit  = ( isset($_REQUEST['uidToEdit']) ) ? ( (int) $_REQUEST['uidToEdit'] ) : ( 0 );
$fromAdmin  = ( isset($_REQUEST['fromAdmin']) && claro_is_platform_admin() ) ? ( trim($_REQUEST['fromAdmin']) ) : ( '' );
$courseCode = ( isset($_REQUEST['course']) ) ? ( trim($_REQUEST['course']) ) : ( '' );
$categoryId = null;
if (!empty($_REQUEST['categoryId'])) $categoryId = (int) $_REQUEST['categoryId'];
elseif (!empty($_REQUEST['category'])) $categoryId = (int) $_REQUEST['category'];

/*=====================================================================
Main Section
=====================================================================*/

/*---------------------------------------------------------------------
Define user we are working with and build enroll URL
---------------------------------------------------------------------*/

$inURL = ''; // parameters to add in URL

if ( !claro_is_platform_admin() )
{
    if (get_conf('allowToSelfEnroll', true))
    {
        $userId    = claro_get_current_user_id(); // default use is enroll for itself...
        $uidToEdit = claro_get_current_user_id();
    }
    else
    {
        claro_redirect('..');
    }
}
else
{
    //Security: only a platform admin can edit other users than himself...
    if ( isset($fromAdmin)
        && ( $fromAdmin == 'settings' || $fromAdmin == 'usercourse' )
        && !empty($uidToEdit)
        )
    {
        $userSettingMode = true;
    }
    
    if ( !empty($fromAdmin) ) $inURL    .= '&amp;fromAdmin=' . $_REQUEST['fromAdmin'];
    if ( !empty($uidToEdit) ) $inURL    .= '&amp;uidToEdit=' . $_REQUEST['uidToEdit'];
    
    /*
     * In admin mode, there are 2 possibilities: we might want to enroll 
     * themself or either be here from admin tool
     */
    if ( !empty($uidToEdit) )
    {
        $userId = $uidToEdit;
    }
    else
    {
        // Default use is enroll for itself
        $userId     = claro_get_current_user_id(); 
        $uidToEdit  = claro_get_current_user_id();
    }
    
} // if (!claro_is_platform_admin())

/*---------------------------------------------------------------------
Define breadcrumbs
---------------------------------------------------------------------*/

if ( isset($_REQUEST['addNewCourse']) )
{
    ClaroBreadCrumbs::getInstance()->prepend(
        get_lang('My personal course list'), 
        $_SERVER['PHP_SELF']);
}

/*---------------------------------------------------------------------
Breadcrumbs is different if we come from admin tool
---------------------------------------------------------------------*/

if ( !empty($fromAdmin) )
{
    if ( $fromAdmin == 'settings' || $fromAdmin == 'usercourse' || $fromAdmin == 'class' )
    {
        ClaroBreadCrumbs::getInstance()->prepend( get_lang('Administration'), get_path('rootAdminWeb') );
    }
    
    if ( $fromAdmin == 'class' )
    {
        if ( isset($_REQUEST['class_id']) )
        {
            $classId = trim($_REQUEST['class_id']);
            $_SESSION['admin_user_class_id'] = $classId;
        }
        elseif (isset($_SESSION['admin_user_class_id']))
        {
            $classId = $_SESSION['admin_user_class_id'];
        }
        else
        {
            $classId = '';
        }
        
        // Breadcrumbs different if we come from admin tool for a CLASS
        $nameTools = get_lang('Enrol class');
        
        $classinfo = class_get_properties ($_SESSION['admin_user_class_id']);
    }
}

/*---------------------------------------------------------------------
DB tables initialisation
Find info about user we are working with
---------------------------------------------------------------------*/

$userInfo = user_get_properties($userId);
if(!$userInfo)
{
    $cmd='';
    switch (claro_failure::get_last_failure())
    {
        case 'user_not_found' :
        {
            $msg = get_lang('User not found');
        }
        break;
        
        default :
        {
            $msg = get_lang('User is not valid');
        }
        break;
    }
}

/*----------------------------------------------------------------------------
Unsubscribe from a course
----------------------------------------------------------------------------*/

if ( $cmd == 'exUnreg' )
{
    if ( user_remove_from_course($userId, $courseCode, false, false, false) )
    {
        $claroline->log('COURSE_UNSUBSCRIBE',array('user'=>$userId,'course'=>$courseCode));
        $dialogBox->success( get_lang('Your enrolment on the course has been removed') );
    }
    else
    {
        switch ( claro_failure::get_last_failure() )
        {
            case 'cannot_unsubscribe_the_last_course_manager' :
            {
                $dialogBox->error( get_lang('You cannot unsubscribe the last course manager of the course') );
            }
            break;
            
            case 'course_manager_cannot_unsubscribe_himself' :
            {
                $dialogBox->error( get_lang('Course manager cannot unsubscribe himself') );
            }
            break;
            
            default :
            {
                $dialogBox->error( get_lang('Unable to remove your registration to the course') );
            }
            break;
        }
    }
    
    $displayMode = DISPLAY_MESSAGE_SCREEN;
} // end if ($cmd == 'exUnreg')

/*----------------------------------------------------------------------------
Subscribe to a course
----------------------------------------------------------------------------*/

if ( $cmd == 'exReg' )
{
    $registrationKey = isset($_REQUEST['registrationKey']) ? $_REQUEST['registrationKey'] : null;
    $categoryId = isset($_REQUEST['categoryId']) ? $_REQUEST['categoryId'] : null;
    
    $courseObj = new Claro_Course($courseCode);
    $courseObj->load();
    
    $courseRegistration = new CourseUserRegistration(
        AuthProfileManager::getUserAuthProfile($userId),
        $courseObj,
        $registrationKey,
        $categoryId
    );
    
    if ( !empty( $classId ) )
    {
        $courseRegistration->setClassRegistrationMode();
    }
    
    if ( $courseRegistration->addUser() )
    {
        $claroline->log('COURSE_SUBSCRIBE',array('user'=>$userId,'course'=>$courseCode));
        
        $displayMode = DISPLAY_MESSAGE_SCREEN;
        
        if ( claro_get_current_user_id() != $uidToEdit )
        {
            // Message for admin
            $dialogBox->success( get_lang('The user has been enroled to the course') );
        }
        else
        {
            $dialogBox->success( get_lang('You\'ve been enroled on the course') );
        }
        
        if ( !empty($_REQUEST['asTeacher']) && claro_is_platform_admin() )
        {
            $properties['isCourseManager']  = 1;
            $properties['role']             = get_lang('Course manager');
            $properties['tutor']            = 1;
            user_set_course_properties($userId, $courseCode, $properties);
        }
    }
    else
    {
        switch ($courseRegistration->getStatus())
        {
            case CourseUserRegistration::STATUS_KEYVALIDATION_FAILED :
            {
                $displayMode = DISPLAY_REGISTRATION_KEY_FORM;
                $dialogBox->error( $courseRegistration->getErrorMessage() );
            }
            break;
            
            case CourseUserRegistration::STATUS_SYSTEM_ERROR :
            {
                $displayMode = DISPLAY_MESSAGE_SCREEN;
                $dialogBox->error( $courseRegistration->getErrorMessage() );
            }
            break;
            
            case CourseUserRegistration::STATUS_REGISTRATION_NOTAVAILABLE :
            {
                $displayMode = DISPLAY_REGISTRATION_DISABLED_FORM;
                $dialogBox->error( $courseRegistration->getErrorMessage() );
                $dialogBox->info(
                    get_lang('Please contact the course manager : %email' ,
                    array ('%email' => '<a href="mailto:'.$courseObj->email . '?body=' . $courseObj->officialCode . '&amp;subject=[' . rawurlencode( get_conf('siteName')) . ']' . '">' . htmlspecialchars($courseObj->titular) . '</a>')) );
            }
            break;
            
            default :
            {
                $displayMode = DISPLAY_MESSAGE_SCREEN;
                $dialogBox->warning( $courseRegistration->getErrorMessage() );
            }
            break;
        }
    }
    
} // end if ($cmd == 'exReg')

/*----------------------------------------------------------------------------
User course list to unregister
----------------------------------------------------------------------------*/

if ( $cmd == 'rqUnreg' )
{
    $coursesList = get_user_course_list($userId);
    $inactiveCourseList = get_user_course_list_desactivated($userId);
    $displayMode = DISPLAY_USER_COURSES;
} // end if ($cmd == 'rqUnreg')

/*----------------------------------------------------------------------------
Search a course to register
----------------------------------------------------------------------------*/

if ( $cmd == 'rqReg' ) // show course of a specific category
{
    if ($fromAdmin == 'class')
        $user = null;
    else
        $user =  $userId;
    
    $categoryBrowser  = new CategoryBrowser($categoryId, $user);
    $viewOptions = new CourseTreeViewOptions(true, false, null, null);
    $categoryBrowser->setViewOptions($viewOptions);
    
    $currentCategory        = $categoryBrowser->getCurrentCategorySettings();
    $currentCategoryName    = $currentCategory->name;
    $parentCategoryId       = $currentCategory->idParent;
    
    $categoriesList         = $categoryBrowser->getSubCategoryList();
    
    $categoryBrowser->getCourseList();
    $coursesList            = $categoryBrowser->getCoursesWithoutSourceCourses();
    
    $displayMode = DISPLAY_COURSE_TREE;
} // end cmd == rqReg

/*=====================================================================
   Display Section
  =====================================================================*/

/*
 * SET 'BACK' LINK
 */

if ( $cmd == 'rqReg' && ( !empty($categoryId) || !empty($parentCategoryId) ) )
{
    $backUrl   = $_SERVER['PHP_SELF'].'?cmd=rqReg&amp;categoryId=' . urlencode($parentCategoryId);
    $backLabel = get_lang('Back to parent category');
}
else
{
    //enroll page accessed by admin tool to set user settings
    if ( $userSettingMode == true ) 
    {
        if ( $fromAdmin == 'settings' )
        {
            $backUrl   = '../admin/admin_profile.php?uidToEdit=' . $userId;
            $backLabel = get_lang('Back to user settings');
        }
        
        if ( $fromAdmin == 'usercourse' ) // admin tool used: list of a user's courses.
        {
            $backUrl   = '../admin/adminusercourses.php?uidToEdit=' . $userId;
            $backLabel = get_lang('Back to user\'s course list');
        }
    }
    elseif ( $fromAdmin == 'class' ) // admin tool used : class registration
    {
        $backUrl   = '../admin/admin_class_user.php?';
        
        if (isset($_SESSION['admin_user_class_id']))
        {
            $backUrl .= 'class_id='. $_SESSION['admin_user_class_id'];
        }
        
        $backLabel = get_lang('Back to the class');
    }
    else
    {
        $backUrl   = '../../index.php?';
        $backLabel = get_lang('Back to my personal course list');
    }
} // end if ( $cmd == 'rqReg' && ( !empty($categoryId) || !empty($parentCategoryId) ) )

$backUrl .= $inURL; //notify userid of the user we are working with in admin mode and that we come from admin
$backLink = '<p><a href="' . $backUrl . '" title="' . $backLabel. '" >'
          . '<span style="background-image: url('.get_icon_url('back').'); background-repeat: no-repeat; background-position: left center; padding-left: 20px;">' 
          . $backLabel . '</span></a></p>' . "\n\n";

$out = '';

switch ( $displayMode )
{
    /*---------------------------------------------------------------------
    Display course list
    ---------------------------------------------------------------------*/
    
    case DISPLAY_COURSE_TREE :
    {
        //  Note : if we are at the root category we're at the top of the campus
        //        root name equal platform name
        //        $siteName comes from claro_main.conf.php
        
        if ( empty($categoryId) ) 
        {
            $currentCategoryName = get_conf('siteName');
        }
        
        //  Display Title
        if ( $fromAdmin != 'class' )
        {
            $title = get_lang('User\'s course') . ' : ' 
                   . $userInfo['firstname'] . ' ' 
                   . $userInfo['lastname'];
            $subTitle = get_lang('Select course in') . ' : ' 
                      . $currentCategoryName ;
        }
        else
        {
            $title = get_lang('Enrol class') . ' : ' . $classinfo['name'] ;
            $subTitle = get_lang('Select course in') . ' : ' 
                      . $currentCategoryName ;
        }
        
        $out .= claro_html_tool_title(array(
            'mainTitle' =>  $title, 
            'subTitle' => $subTitle));
        
        // Display dialogbox and backlink
        $out .= $dialogBox->render()
              . $backLink;
        
        // Display categories
        if ( count($categoriesList) > 0)
        {
            $out .= '<h4>' . get_lang('Categories') . '</h4>' . "\n"
                  . '<ul>' . "\n";
            
            foreach( $categoriesList as $category )
            {
                $nbCourses = claroCategory::countAllCourses($category['id']);
                $nbSubCategories = claroCategory::countAllSubCategories($category['id']);
                
                // If the category contains something else (subcategory or course),
                // make a link to access to these ressources
                if ($nbCourses + $nbSubCategories > 0)
                {
                    $out .= '<li><a href="' . $_SERVER['PHP_SELF'] . "?cmd=rqReg&amp;categoryId="
                          . urlencode( $category['id'] ) . $inURL . '">'
                          . $category['name'] . '</a></li>';
                }
                else
                {
                    $out .= '<li>'.$category['name'].'</li>';
                }
            }
            
            $out .= '</ul>' . "\n";
        }
        
        // Separator between category list and course list
        if ( count($coursesList) > 0  && count($categoriesList) > 0 )
        {
            $out .= '<hr size="1" noshade="noshade" />' . "\n";
        }
        
        // Course List
        if ( count($coursesList) > 0 )
        {
            $out .= '<h4>' . get_lang('Course list') . '</h4>' . "\n"
                  . '<table class="claroTable emphaseLine" >' . "\n" ;
            
            /*
             * Display links to enroll as student and also as teacher 
             * (but not for a class)
             */
            if ( $userSettingMode ) 
            {
                $out .= '<thead>' . "\n"
                      . '<tr>' . "\n"
                      . '<th>&nbsp;</th>' . "\n"
                      . '<th>' . get_lang('Enrol as student') . '</th>' . "\n"
                      . '<th>' . get_lang('Enrol as teacher') . '</th>' . "\n"
                      . '<tr>' . "\n"
                      . '</thead>' . "\n";
            }
            elseif ( $fromAdmin == 'class' )
            {
                $out .= '<thead>' . "\n"
                      . '<tr>' . "\n"
                      . '<th>&nbsp;</th>' . "\n"
                      . '<th>' . get_lang('Enrol class') . '</th>' . "\n"
                      . '</tr>' . "\n"
                      . '</thead>' . "\n";
            }
            
            $out .= '<tbody>' . "\n";
            
            // Does the category prevent registration ?
            if((get_conf('registrationRestrictedThroughCategories')
                && ClaroCategory::isRegistredToCategory($userId, $categoryId))
                || (!get_conf('registrationRestrictedThroughCategories')))
            {
                $categoryRestricted = false; //Category doesn't prevent registration
            }
            else
            {
                $categoryRestricted = true; //Category does prevent registration
            }
            
            foreach($coursesList as $thisCourse)
            {
                $out .= '<tr>' . "\n"
                      . '<td>' . $thisCourse['officialCode'] . ' - ' 
                      . $thisCourse['title'] . '<br />' . "\n"
                      . '<small>';
                
                if( !empty($thisCourse['email']) )
                {
                    $out .= '<a href="mailto:'.$thisCourse['email'].'">' 
                          . $thisCourse['titular'] . '</a>';
                }
                else
                {
                    $out .= $thisCourse['titular'];
                }
                
                $out .= '</small>' . "\n" . '</td>' . "\n";
                
                // Enroll links for single users
                if ( $userSettingMode )
                {
                    // If the user is already enrolled
                    if ( $thisCourse['enroled'] )
                    {
                        $out .= '<td valign="top" colspan="2" align="center">' . "\n"
                              . '<span class="highlight">' . get_lang('Already enroled') . '</span>'
                              . '</td>' . "\n"
                        ;
                    }
                    else
                    {
                        // Class may not be enroled as teachers
                        $out .= '<td valign="top" align="center">' . "\n"
                              . '<a href="' . $_SERVER['PHP_SELF'] . '?cmd=exReg&amp;course=' 
                              . $thisCourse['sysCode']
                              . '&amp;categoryId=' . $categoryId . $inURL . '">'
                              . '<img src="' . get_icon_url('enroll') . '" alt="' 
                              . get_lang('Enrol as student') . '" />'
                              . '</a></td>' . "\n"
                              . '<td valign="top" align="center">' . "\n"
                              . '<a href="' . $_SERVER['PHP_SELF'] . '?cmd=exReg&amp;asTeacher=true&amp;course=' . $thisCourse['sysCode']
                              . '&amp;categoryId=' . $categoryId . $inURL . '">'
                              . '<img src="' . get_icon_url('enroll') . '"  alt="' 
                              . get_lang('Enrol as teacher') . '" />'
                              . '</a>'
                              . '</td>' . "\n"
                        ;
                    }
                }
                // Enroll links for classes
                elseif ( $fromAdmin == 'class')
                {
                    $classEnroled = false;
                    $classes = get_class_list_of_course($thisCourse['sysCode']);
                    
                    foreach ($classes as $thisClass)
                    {
                        if ($classId == $thisClass['id']) $classEnroled = true;
                    }
                    
                    if (!$classEnroled)
                    {
                        $out .= '<td valign="top"  align="center">' . "\n"
                              . '<a href="' . get_path('clarolineRepositoryWeb') 
                              . 'admin/admin_class_course_registered.php'
                              . '?cmd=exReg'
                              . '&amp;course_id=' . $thisCourse['sysCode']
                              . '&amp;class_id=' . $classinfo['id'] . $inURL . '">'
                              . '<img src="' . get_icon_url('enroll') . '" alt="' 
                              . get_lang('Enrol class') . '" />'
                              . '</a>'
                              . '</td>' . "\n";
                    }
                    else
                    {
                        $out .= '<td valign="top"  align="center">' . "\n"
                              . '<a href="' . get_path('clarolineRepositoryWeb') 
                              . 'admin/admin_class_course_registered.php'
                              . '?cmd=exUnreg'
                              . '&amp;course_id=' . $thisCourse['sysCode']
                              . '&amp;class_id=' . $classinfo['id'] . $inURL . '">'
                              . '<img src="' . get_icon_url('unenroll') . '" alt="' 
                              . get_lang('Unenrol class') . '" />'
                              .  '</a>'
                              .  '</td>' . "\n";
                    }
                }
                else
                {
                    $out .= '<td valign="top">' . "\n";
                    
                    if ( $thisCourse['enroled'] )
                    {
                        $out .= '<span class="highlight">' 
                              . get_lang('Already enroled') . '</span>' . "\n";
                    }
                    elseif(claro_is_platform_admin() ||
                        (in_array($thisCourse['registration'], array('open', 'validation')) && !$categoryRestricted))
                    {
                        $out .= '<a href="' . $_SERVER['PHP_SELF']
                              . '?cmd=exReg&amp;course=' . $thisCourse['sysCode']
                              . '&amp;categoryId=' . $categoryId . $inURL . '">'
                              . '<img src="' . get_icon_url('enroll') . '" alt="' . get_lang('Enrolment') . '" />'
                              . '</a>';
                    }
                    else
                    {
                        $out .= '<a href="' . $_SERVER['PHP_SELF']
                              . '?cmd=exReg&amp;course=' . $thisCourse['sysCode']
                              . '&amp;categoryId=' . $categoryId . $inURL . '">'
                              . '<img src="' . get_icon_url('locked') . '" alt="' . get_lang('Locked') . '" />'
                              . '</a>';
                    }
                    
                    // It's not pretty, can be enjoyed to show the protected courses.
                    if ( $can_see_hidden_course && $thisCourse['visibility']=='invisible') 
                    {
                        $out .= '('.get_lang('Invisible').')';
                    }
                    
                    $out .= '</td>' . "\n";
                }
                
                $out .= '</tr>' . "\n";
                
            } // end foreach courseList
            
            $out .= '</tbody>' . "\n"
                  . '</table>' . "\n";
        }
        
        // Form: Search a course with a keyword
        $searchBox = new CourseSearchBox($_SERVER['REQUEST_URI']);
        $viewOptions = new CourseTreeViewOptions(
            true,
            false,
            new Url($_SERVER['PHP_SELF'].'?cmd=exReg'),
            null);
        $searchBox->setViewOptions($viewOptions);
        
        $out .= $searchBox->render();
    }
    break;
    
    /*---------------------------------------------------------------------
    Display message
    ---------------------------------------------------------------------*/
    
    case DISPLAY_MESSAGE_SCREEN :
    {
        $out .= claro_html_tool_title(get_lang('User\'s course') . ' : ' 
              . $userInfo['firstname'] . ' ' . $userInfo['lastname'] )
              . $dialogBox->render();
    }
    break;
    
    /*---------------------------------------------------------------------
    Display user courses ( Default display)
    ---------------------------------------------------------------------*/
    
    case DISPLAY_USER_COURSES :
    {
        $out .= claro_html_tool_title( array('mainTitle' => get_lang('User\'s course') . ' : ' . $userInfo['firstname'] . ' ' . $userInfo['lastname'],
        'subTitle' => get_lang('Remove course from your personal course list')));
        
        $out .= $dialogBox->render();
        
        if ( count($coursesList) > 0 )
        {
            $out .= '<table class="claroTable">' . "\n";
            
            foreach ($coursesList as $thisCourse)
            {
                $out .= '<tr>' . "\n"
                      . '<td>' . "\n"
                      . $thisCourse['title'] . '<br />' . "\n"
                      . '<small>' . $thisCourse['officialCode'] . ' - ' . $thisCourse['titular'] . '</small>'
                      . '</td>' . "\n"
                      . '<td>' . "\n";
                
                if ( $thisCourse['isCourseManager'] != 1 )
                {
                    $out .= '<a href="' . $_SERVER['PHP_SELF'] . '?cmd=exUnreg&amp;course=' . $thisCourse['sysCode'] . $inURL . '"'
                          . ' onclick="javascript:if(!confirm(\''
                          . clean_str_for_javascript(get_lang('Are you sure you want to remove this course from your list ?'))
                          . '\')) return false;">' . "\n"
                          . '<img src="' . get_icon_url('unenroll') . '" alt="' . get_lang('Unsubscribe') . '" />' . "\n"
                          . '</a>' . "\n";
                }
                else
                {
                    $out .= '<span class="highlight">'
                          . get_lang('Course manager')
                          . '</span>' . "\n";
                }
                
                $out .= '</td>' . "\n"
                      . '</tr>' . "\n";
            } // foreach $coursesList as $thisCourse
            
            $out .= '</table>' . "\n";
        }
        
        $is_allowedToUnregisterFromInactive =
            get_conf('crslist_UserCanUnregFromInactiveCourses', false)
            || claro_is_platform_admin();
        
        if ( isset($inactiveCourseList) && count($inactiveCourseList) > 0 )
        {
            $out .= claro_html_tool_title(get_lang('Deactivated course list'))
                  . '<table class="claroTable">' . "\n";
            
            foreach ($inactiveCourseList as $thisCourse)
            {
                $out .= '<tr>' . "\n"
                      . '<td>' . "\n"
                      . $thisCourse['title'] . '<br />' . "\n"
                      . '<small>' . $thisCourse['officialCode'] . ' - ' . $thisCourse['titular'] . '</small>'
                      . '</td>' . "\n"
                      . '<td>' . "\n";
                
                if ( $thisCourse['isCourseManager'] != 1 && $is_allowedToUnregisterFromInactive )
                {
                    $out .= '<a href="' . $_SERVER['PHP_SELF'] . '?cmd=exUnreg&amp;course=' . $thisCourse['sysCode'] . $inURL . '"'
                          . ' onclick="javascript:if(!confirm(\''
                          . clean_str_for_javascript(get_lang('Are you sure you want to remove this course from your list ?'))
                          . '\')) return false;">' . "\n"
                          . '<img src="' . get_icon_url('unenroll') . '" alt="' . get_lang('Unsubscribe') . '" />' . "\n"
                          . '</a>' . "\n";
                }
                else
                {
                    if ( $thisCourse['isCourseManager'] == 1 )
                    {
                        $out .= '<span class="highlight">'
                              . get_lang('Course manager')
                              . '</span>' . "\n";
                    }
                    else
                    {
                        $out .= "-\n";
                    }
                }
                
                $out .= '</td>' . "\n"
                      . '</tr>' . "\n";
            } // foreach $courseList as $thisCourse
            
            $out .= '</table>' . "\n";
        }
    }
    break;
    
    case DISPLAY_REGISTRATION_KEY_FORM :
    {
        $courseData = claro_get_course_data($_REQUEST['course']);
        $courseName = $courseData['name'];
        
        $out .= claro_html_tool_title( array('mainTitle' => get_lang('User\'s course') . ' : ' . $userInfo['firstname'] . ' ' . $userInfo['lastname'],
        'subTitle' => get_lang('Enrol to %course', array('%course' => $courseName) )));
        
        $template = new CoreTemplate('course_registration_key_form.tpl.php');
        $template->assign('formAction', Url::Contextualize($_SERVER['PHP_SELF']));
        $template->assign('courseCode', $courseCode);
        
        $dialogBox->form($template->render());
        
        $out .= $dialogBox->render();
    }
    break;
    
    case DISPLAY_REGISTRATION_DISABLED_FORM :
    {
        if ( empty($courseData['email']) ) $courseData['email'] = get_conf('administrator_email');
        if ( empty($courseData['titular']) ) $courseData['titular'] = get_conf('administrator_name');
        
        $out .= $dialogBox->render();
    }
    break;
    
} // end of switch ($displayMode)

$out .= $backLink;

$claroline->display->body->appendContent($out);

echo $claroline->display->render();
