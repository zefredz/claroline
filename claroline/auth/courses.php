<?php // $Id$
/**
 * CLAROLINE
 *
 * prupose list of course to enroll or leave
 *
 * @version 1.8 $Revision$
 *
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @package AUTH
 *
 * @author Claro Team <cvs@claroline.net>
 */

require '../inc/claro_init_global.inc.php';
require $includePath.'/lib/courselist.lib.php';

$nameTools  = get_lang('_course_enrollment');
$noPHP_SELF = TRUE;

/*---------------------------------------------------------------------
Security Check
---------------------------------------------------------------------*/

if ( ! $_uid ) claro_disp_auth_form();

/*---------------------------------------------------------------------
Include Files and initialize variables
---------------------------------------------------------------------*/

require $includePath . '/lib/admin.lib.inc.php';
require $includePath . '/lib/user.lib.php';
require $includePath . '/conf/user_profile.conf.php';

$parentCategoryCode = '';
$userSettingMode    = FALSE;
$message            = '';
$courseList = array();
$categoryList = array();

/*---------------------------------------------------------------------
Get tables name
---------------------------------------------------------------------*/

$tbl_mdb_names = claro_sql_get_main_tbl();

$tbl_course           = $tbl_mdb_names['course'           ];
$tbl_rel_course_user  = $tbl_mdb_names['rel_course_user'  ];
$tbl_course_nodes     = $tbl_mdb_names['category'         ];
$tbl_class            = $tbl_mdb_names['class'            ];

/*---------------------------------------------------------------------
Define Display
---------------------------------------------------------------------*/

define ('DISPLAY_USER_COURSES'       , __LINE__);
define ('DISPLAY_COURSE_TREE'        , __LINE__);
define ('DISPLAY_MESSAGE_SCREEN'     , __LINE__);
define ('DISPLAY_ENROLLMENT_KEY_FORM', __LINE__);

$displayMode = DISPLAY_USER_COURSES; // default display

/*---------------------------------------------------------------------
Get request variables
---------------------------------------------------------------------*/

if ( isset($_REQUEST['cmd']) ) $cmd = $_REQUEST['cmd'];
else                           $cmd = '';

if ( isset($_REQUEST['uidToEdit']) ) $uidToEdit = (int) $_REQUEST['uidToEdit'];
else                                 $uidToEdit = 0;

if ( isset($_REQUEST['fromAdmin']) && $is_platformAdmin ) $fromAdmin = trim($_REQUEST['fromAdmin']);
else                                 $fromAdmin = '';

if ( isset($_REQUEST['course']) ) $course = trim($_REQUEST['course']);
else                              $course = '';

if ( isset($_REQUEST['category']) ) $category = trim($_REQUEST['category']);
else                                $category = '';

/*=====================================================================
Main Section
=====================================================================*/

/*---------------------------------------------------------------------
Define user we are working with...
---------------------------------------------------------------------*/

$inURL = ''; // parameters to add in URL

if ( !$is_platformAdmin )
{
    if ($allowToSelfEnroll)
    {
        $userId    = $_uid; // default use is enroll for itself...
        $uidToEdit = $_uid;
    }
    else
    {
        header('location:..');
    }

}
else
{
    // security : only platform admin can edit other user than himself...

    if ( isset($fromAdmin)
    && ( $fromAdmin == 'settings' || $fromAdmin == 'usercourse' )
    && !empty($uidToEdit)
    )
    {
        $userSettingMode = TRUE;
    }

    if ( !empty($fromAdmin) ) $inURL .= '&amp;fromAdmin=' . $_REQUEST['fromAdmin'];
    if ( !empty($uidToEdit) ) $inURL .= '&amp;uidToEdit=' . $_REQUEST['uidToEdit'];

    // in admin mode, there 2 possibilities : we might want to enroll themself or either be here from admin tool

    if ( !empty($uidToEdit) )
    {
        $userId = $uidToEdit;
    }
    else
    {
        $userId = $_uid; // default use is enroll for itself...
        $uidToEdit = $_uid;
    }

} // if (!$is_platformAdmin)

/*---------------------------------------------------------------------
Define bredcrumps
---------------------------------------------------------------------*/

if ( isset($_REQUEST['addNewCourse']) )
{
    $interbredcrump[] = array('url' => $_SERVER['PHP_SELF'], 'name' => get_lang('_my_personnal_course_list'));
}

/*---------------------------------------------------------------------
Bredcrumps different if we come from admin tool
---------------------------------------------------------------------*/

if ( !empty($fromAdmin) )
{
    if ( $fromAdmin == 'settings' || $fromAdmin == 'usercourse' || $fromAdmin == 'class' )
    {
        $interbredcrump[]= array ('url' => $rootAdminWeb, 'name' => get_lang('Administration'));
    }

    if ( $fromAdmin == 'class' )
    {
        // bred different if we come from admin tool for a CLASS
        $nameTools = get_lang('EnrollClass');

        //find info about the class
        $sqlclass = "SELECT id, name, class_parent_id, class_level
                     FROM `" . $tbl_class . "`
                     WHERE `id` = '" . (int) $_SESSION['admin_user_class_id'] . "'";

        list($classinfo) = claro_sql_query_fetch_all($sqlclass);
    }
}

/*---------------------------------------------------------------------
DB tables initialisation
Find info about user we are working with
---------------------------------------------------------------------*/

$userInfo = user_get_data($userId);
if(!$userInfo)
{
    $cmd='';
    switch (claro_failure::get_last_failure())
    {
        case 'user_not_found':
        $msg = 'User not found';
        break;

        default:
        $msg = 'user invalid';
        break;
    }
}


/*----------------------------------------------------------------------------
Unsubscribe from a course
----------------------------------------------------------------------------*/

if ( $cmd == 'exUnreg' )
{
    if ( user_remove_from_course($userId, $course) )
    {
        event_default('COURSE_UNSUBSCRIBE',array('user'=>$userId,'course'=>$course));
        $message = get_lang('_your_enrollment_to_the_course_has_been_removed');
    }
    else
    {
        switch ( claro_failure::get_last_failure() )
        {
            case 'cannot_unsubscribe_the_last_course_manager' :
            $message = get_lang('CannotUnsubscribeLastCourseManager');
            break;
            case 'course_manager_cannot_unsubscribe_himself' :
            $message = get_lang('CourseManagerCannotUnsubscribeHimself');
            break;
            default :
            $message = get_lang('UnableToRemoveCourseRegistration');
        }
    }

    $displayMode = DISPLAY_MESSAGE_SCREEN;
} //if ($cmd == 'exUnreg')

/*----------------------------------------------------------------------------
Subscribe to a course
----------------------------------------------------------------------------*/

if ( $cmd == 'exReg' )
{
    // if user is platform admin, register to private course can be forced.
    // Otherwise not

    if ( is_course_enrollment_allowed($course) || $is_platformAdmin)
    {
        $courseEnrollmentKey = get_course_enrollment_key($course);

        if (    $is_platformAdmin
        || ( is_null($courseEnrollmentKey) || empty($courseEnrollmentKey) )
        || (   isset($_REQUEST['enrollmentKey'] )
        && strtolower(trim($_REQUEST['enrollmentKey'] )) == strtolower(trim($courseEnrollmentKey))) )
        {
            // try to register user
            if ( user_add_to_course($userId, $course) )
            {
                if ( $_uid != $uidToEdit )
                {
                    // message for admin
                    $message = get_lang('_user_has_been_enrolled_to_the_course');
                }
                else
                {
                    $message = get_lang('_you_have_been_enrolled_to_the_course');
                }

                if ( !empty($_REQUEST['asTeacher']) && $is_platformAdmin )
                {
                    $properties['status'] = 1;
                    $properties['role']   = get_lang('Course manager');
                    $properties['tutor']  = 1;
                    user_update_course_properties($userId, $course, $properties);
                }
            }
            else
            {
                switch (claro_failure::get_last_failure())
                {
                    case 'already_enrolled_in_course' :
                    $message = get_lang('_TheUserIsAlreadyEnrolledInTheCourse');
                    break;
                    default:
                    $message = get_lang('UnableToEnrollInCourse');
                }
            }

            $displayMode = DISPLAY_MESSAGE_SCREEN;

        } // end else if is_null $courseEnrollmentKey
        else
        {
            if ( isset($_REQUEST['enrollmentKey']) )
            {
                $message = 'Wrong Enrollment Key.';
            }

            $displayMode = DISPLAY_ENROLLMENT_KEY_FORM;
        } // end else if is_null $courseEnrollmentKey
    }
    else
    {
        $message = get_lang('UnableToEnrollInCourse');
        $displayMode = DISPLAY_MESSAGE_SCREEN;
    }


} // end if ($cmd == 'exReg')

/*----------------------------------------------------------------------------
User course list to unregister
----------------------------------------------------------------------------*/

if ( $cmd == 'rqUnreg' )
{
    $courseList = get_user_course_list($userId);
    $displayMode = DISPLAY_USER_COURSES;
} // if ($cmd == 'rqUnreg')

/*----------------------------------------------------------------------------
Search a course to register
----------------------------------------------------------------------------*/

if ( $cmd == 'rqReg' ) // show course of a specific category
{
    /*
    * Search by keyword
    */

    if ( isset($_REQUEST['keyword']) )
    {
        $title   = get_lang('_select_course_in_search_results');
        $keyword = trim($_REQUEST['keyword']);
        $result  = search_course($keyword);

        if ( count($result) > 0 )
        {
            $courseList = $result;
        }
        else
        {
            $message = get_lang('_no_course_available_fitting_this_keyword');
        }

        $displayMode = DISPLAY_COURSE_TREE;

    } // end if isset keyword

    /*
    * Get the courses contained in this category
    */

    else
    {
        $courseCategoryBrowser = new category_browser($category, $userId);

        $currentCategory     = $courseCategoryBrowser->get_current_category_settings();
        $currentCategoryName = $currentCategory['name'  ];
        $parentCategoryCode  = $currentCategory['code_P'];

        $categoryList = $courseCategoryBrowser->get_sub_category_list();

        $courseList   = $courseCategoryBrowser->get_course_list();

        $displayMode = DISPLAY_COURSE_TREE;
    }

} // end cmd == rqReg

/*=====================================================================
Display Section
=====================================================================*/

/*
* SET 'BACK' LINK
*/

if ( $cmd == 'rqReg' && ( !empty($category) || !empty($parentCategoryCode) ) )
{
    $backUrl   = $_SERVER['PHP_SELF'].'?cmd=rqReg&amp;category=' . $parentCategoryCode;
    $backLabel = get_lang('_back_to_parent_category');
}
else
{

    if ( $userSettingMode == true ) //enroll page accessed by admin tool to set user settings
    {
        if ( $fromAdmin == 'settings' )
        {
            $backUrl   = '../admin/adminprofile.php?uidToEdit=' . $userId;
            $backLabel = get_lang('BackToUserSettings');
        }
        if ( $fromAdmin == 'usercourse' ) // admin tool used: list of a user's courses.
        {
            $backUrl   = '../admin/adminusercourses.php?uidToEdit=' . $userId;
            $backLabel = get_lang('BackToCourseList');
        }
    }
    elseif ( $fromAdmin == 'class' ) // admin tool used : class registration
    {
        $backUrl   = '../admin/admin_class_user.php?';
        $backLabel = get_lang('BackToClass');
    }
    else
    {
        $backUrl   = '../../index.php?';
        $backLabel = get_lang('_back_to_my_personnal_course_list');
    }
} // ($cmd == 'rqReg' && ($category || ! is_null($parentCategoryCode) ) )

$backUrl .= $inURL; //notify userid of the user we are working with in admin mode and that we come from admin
$backLink = '<p><small><a href="' . $backUrl . '" title="' . $backLabel. '" >&lt;&lt; ' . $backLabel . '</a></small></p>' . "\n\n";

/*---------------------------------------------------------------------
Display header
---------------------------------------------------------------------*/

include $includePath . '/claro_init_header.inc.php';

if (isset($msg)) echo claro_disp_message_box($msg);
echo $backLink;

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

        if ( empty($category) ) $currentCategoryName = $siteName;

        //  Display Title

        if ( $fromAdmin != 'class' )
        {

            echo claro_disp_tool_title( array( 'mainTitle' => get_lang('_course_enrollment')
            .                ' : '
            .                $userInfo['firstname'] . ' '
            .                $userInfo['lastname']
            ,      'subTitle'  => get_lang('_select_course_in')
            .                ' '
            .                $currentCategoryName
            )
            );
        }
        else
        {
            echo claro_disp_tool_title( array( 'mainTitle' => get_lang('EnrollClass') . ' : ' . $classinfo['name']
            , 'subTitle'  => get_lang('_select_course_in') . ' ' . $currentCategoryName
            )
            );
        }

        // Display message

        if ( !empty($message) )
        {
            echo claro_disp_message_box($message);
        }

        // Display categories

        if ( count($categoryList) > 0)
        {
            echo '<h4>' . get_lang('Categories') . '</h4>' . "\n"
            .    '<ul>' . "\n"
            ;

            foreach ( $categoryList as $thisCategory )
            {
                if ( $thisCategory['code'] != $category )
                {
                    echo '<li>' . "\n";

                    if ($thisCategory['nbCourse'] + $thisCategory['nb_childs'] > 0)
                    {
                        echo '<a href="' . $_SERVER['PHP_SELF']
                        .    '?cmd=rqReg&amp;category=' . $thisCategory['code'] . $inURL . '">'
                        .    $thisCategory['name']
                        .    '</a>'
                        .    '&nbsp<small>(' . $thisCategory['nbCourse'] . ')</small>'
                        ;
                    }
                    else
                    {
                        echo $thisCategory['name'];
                    }

                    echo '</li>' . "\n";
                }
            } // end foreach categoryList

            echo '</ul>' . "\n";
        }

        // Separator between category list and course list

        if ( count($courseList) > 0  && count($categoryList) > 0 )
        {
            echo '<hr size="1" noshade="noshade">' . "\n";
        }

        // Course List

        if ( count($courseList) > 0 )
        {
            echo '<h4>' . get_lang('CourseList') . '</h4>' . "\n"
            .    '<blockquote>' . "\n"
            .    '<table class="claroTable emphaseLine" >' . "\n"
            ;

            if ( $userSettingMode ) //display links to enroll as student and also as teacher (but not for a class)
            {

                echo '<thead>' . "\n"
                .    '<tr class="headerX">' . "\n"
                .    '<th>&nbsp;</th>' . "\n"
                .    '<th>' . get_lang('Enroll as student') . '</th>' . "\n"
                .    '<th>' . get_lang('EnrollAsTeacher') . '</th>' . "\n"
                .    '<tr>' . "\n"
                .    '</thead>' . "\n"
                ;
            }
            elseif ( $fromAdmin == 'class' )
            {
                echo '<thead>' . "\n"
                .    '<tr class="headerX">' . "\n"
                .    '<th>&nbsp;</th>' . "\n"
                .    '<th>' . get_lang('EnrollClass') . '</th>' . "\n"
                .    '</tr>' . "\n"
                .    '</thead>' . "\n"
                ;
            }

            echo '<tbody>' . "\n";

            foreach($courseList as $thisCourse)
            {
                echo '<tr>' . "\n"
                .    '<td>' . $thisCourse['officialCode'] . ' - ' . $thisCourse['title'] . '<br />' . "\n"
                .    '<small>' . $thisCourse['titular'] . '</small>' ."\n"
                .    '</td>' . "\n"
                ;

                // enroll link

                if ( $userSettingMode )
                {
                    if ( $thisCourse['enrolled'] )
                    {
                        echo '<td valign="top" colspan="2" align="center">' . "\n"
                        .    '<small><span class="highlight">' . get_lang('_already_enrolled') . '</span></small>'
                        .    '</td>' . "\n"
                        ;
                    }
                    else
                    {
                        // class may not be enrolled as teachers

                        echo '<td valign="top" align="center">' . "\n"
                        .    '<a href="' . $_SERVER['PHP_SELF'] . '?cmd=exReg&amp;course=' . $thisCourse['code'] . $inURL . '">'
                        .    '<img src="' . $imgRepositoryWeb . 'enroll.gif" alt="' . get_lang('Enroll as student') . '">'
                        .    '</a></td>' . "\n"
                        .    '<td valign="top" align="center">' . "\n"
                        .    '<a href="' . $_SERVER['PHP_SELF'] . '?cmd=exReg&amp;asTeacher=true&amp;course=' . $thisCourse['code'] .$inURL . '">'
                        .    '<img src="' . $imgRepositoryWeb . 'enroll.gif"  alt="' . get_lang('EnrollAsTeacher') . '">'
                        .    '</a>'
                        .    '</td>' . "\n"
                        ;
                    }
                }
                elseif ( $fromAdmin == 'class')
                {
                    echo '<td valign="top"  align="center">' . "\n"
                    .    '<a href="' . $clarolineRepositoryWeb . 'admin/admin_class_course_registered.php'
                    .    '?cmd=exReg&course=' . $thisCourse['code']
                    .    '&class=' . $classinfo['id'] . $inURL . '">'
                    .    '<img src="' . $imgRepositoryWeb . 'enroll.gif" border="0" alt="' . get_lang('EnrollClass') . '">'
                    .     '</a>'
                    .     '</td>' . "\n"
                    ;
                }
                else
                {
                    echo '<td valign="top">' . "\n";

                    if ( $thisCourse['enrolled'] )
                    {
                        echo '<small><span class="highlight">' . get_lang('_already_enrolled') . '</span></small>' . "\n";
                    }
                    elseif($thisCourse['visible'] == 1 || $thisCourse['visible'] == 2)
                    {
                        echo '<a href="' . $_SERVER['PHP_SELF']
                        .    '?cmd=exReg&course=' . $thisCourse['sysCode'] . $inURL . '">'
                        .    '<img src="' . $imgRepositoryWeb . 'enroll.gif" border="0" alt="' . get_lang('Subscription') . '">'
                        .    '</a>'
                        ;
                    }
                    else
                    {
                        echo '<img src="' . $imgRepositoryWeb . 'locked.gif" border="0" alt="' . get_lang('Locked') . '">';
                    }

                    echo '</td>' . "\n";

                }

                echo '</tr>' . "\n";

            } // end foreach courseList

            echo '</tbody>' . "\n"
            .    '</table>' . "\n"
            .    '</blockquote>' . "\n"
            ;
        }

        // Form: Search a course with a keyword

        echo '<blockquote>' . "\n"
        .    '<p><label for="keyword">' . get_lang('_or_search_from_keyword') . '</label> : </p>' . "\n"
        .    '<form method="post" action="' . $_SERVER['PHP_SELF'] . '">' . "\n"
        .    '<input type="hidden" name="cmd" value="rqReg" />' . "\n"
        .    '<input type="hidden" name="fromAdmin" value="' . $fromAdmin . '" />' . "\n"
        .    '<input type="hidden" name="uidToEdit" value="' . $uidToEdit . '" />' . "\n"
        .    '<input type="text" name="keyword" id="keyword" />' . "\n"
        .    '&nbsp;<input type="submit" value="' . get_lang('Search') . '" />' . "\n"
        .    '</form>' . "\n"
        .    '</blockquote>' . "\n"
        ;
    }   break;

    /*---------------------------------------------------------------------
    Display message
    ---------------------------------------------------------------------*/

    case DISPLAY_MESSAGE_SCREEN :
    {

        // echo claro_disp_tool_title( get_lang('_course_enrollment'));

        echo claro_disp_tool_title(get_lang('_course_enrollment') . ' : ' . $userInfo['firstname'] . ' ' . $userInfo['lastname'] );

        echo '<blockquote>' . "\n";

        if ( !empty($message) )
        {
            echo claro_disp_message_box( '<p>'.$message.'</p>' . "\n"
            .    '<p align="center">'
            .    '<a href="' . $backUrl . '">' .$backLabel . '</a>'
            .    '</p>'  . "\n"
            );
        }
        echo '</blockquote>' . "\n";

    }   break;

    /*---------------------------------------------------------------------
    Display user courses ( Default display)
    ---------------------------------------------------------------------*/

    case DISPLAY_USER_COURSES :
    {

        echo claro_disp_tool_title( array('mainTitle' => get_lang('_course_enrollment') . ' : ' . $userInfo['firstname'] . ' ' . $userInfo['lastname'],
        'subTitle' => get_lang('_remove_course_from_your_personnal_course_list')));

        if ( count($courseList) > 0 )
        {
            echo '<blockquote>' . "\n"
            .    '<table class="claroTable">' . "\n"
            ;

            foreach ($courseList as $thisCourse)
            {
                echo '<tr>' . "\n"
                .    '<td>' . "\n"
                .    $thisCourse['title'] . '<br />' . "\n"
                .    '<small>' . $thisCourse['officialCode'] . ' - ' . $thisCourse['titular'] . '</small>'
                .    '</td>' . "\n"
                .    '<td>' . "\n"
                ;

                if ( $thisCourse['userSatus'] != 1 )
                {
                    echo '<a href="' . $_SERVER['PHP_SELF'] . '?cmd=exUnreg&amp;course=' . $thisCourse['code'] . $inURL . '"'
                    .    ' onclick="javascript:if(!confirm(\''
                    .    clean_str_for_javascript(get_lang('_are_you_sure_to_remove_the_course_from_your_list'))
                    .    '\')) return false;">' . "\n"
                    .    '<img src="' . $imgRepositoryWeb . 'unenroll.gif" border="0" alt="' . get_lang('_unsubscribe') . '">' . "\n"
                    .    '</a>' . "\n"
                    ;
                }
                else
                {
                    echo '<small>'
                    .    '<span class="highlight">'
                    .    get_lang('Course manager')
                    .    '</span></small>' . "\n"
                    ;
                }

                echo '</td>' . "\n"
                .    '</tr>' . "\n"
                ;
            } // foreach $courseList as $thisCourse

            echo '</table>' . "\n"
            .    '</blockquote>' . "\n"
            ;
        }
    }
    break;

    case DISPLAY_ENROLLMENT_KEY_FORM :
    {

        if ( ! empty($message) ) echo claro_disp_message_box($message);

        echo  '<blockquote>' . get_lang('This course requires a key for enrollment') . '</p>' . "\n"
        .     '<form action="' . $_SERVER['PHP_SELF'] . '" method="POST">' . "\n"
        .     '<input type="hidden" name="cmd" value="exReg">' . "\n"
        .     get_lang('Key')
        .     ' : '
        .     '<input type="hidden" name="course" value="' . $_REQUEST['course'] . '">'
        .     '<input type="text" name="enrollmentKey">' . "\n"
        .     '<p>'
        .     '<input type="submit" value="' . get_lang('Ok') . '">&nbsp;' . "\n"
        .     claro_disp_button($_SERVER['PHP_SELF'].'?cmd=rqReg', get_lang('Cancel'))
        .     '</p>'
        .     '</form>' . "\n"
        .     '</blockquote>'
        ;
    }   break;


} // end of switch ($displayMode)

echo $backLink;

/*---------------------------------------------------------------------
Display footer
---------------------------------------------------------------------*/
include $includePath . '/claro_init_footer.inc.php';

?>