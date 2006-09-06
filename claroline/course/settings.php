<?php // $Id$
/**
 * CLAROLINE
 *
 * This tool manage properties of an exiting course
 *
 * @version 1.8 $Revision$
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @author claroline Team <cvs@claroline.net>
 *
 * old version : http://cvs.claroline.net/cgi-bin/viewcvs.cgi/claroline/claroline/course_info/infocours.php
 *
 * @package CLCRS
 *
 */
$gidReset = true;
require '../inc/claro_init_global.inc.php';

$nameTools = get_lang('Course settings');
$noPHP_SELF = true;

$dialogBox = '';

if ( ! $_cid || ! $_uid) claro_disp_auth_form(true);

$is_allowedToEdit = $is_courseAdmin;

if ( ! $is_allowedToEdit )
{
    claro_die(get_lang('Not allowed'));
}

include_once $includePath . '/lib/user.lib.php'; // needed for is_well_formed_email-address()
include_once $includePath . '/lib/course.lib.inc.php';
include_once $includePath . '/lib/form.lib.php';
include_once claro_get_conf_repository() . 'course_main.conf.php';

/**
 * Configuration array , define here which field can be left empty or not
 */

$fieldRequiredStateList['category'     ] = true;
$fieldRequiredStateList['lanCourseForm'] = true;
$fieldRequiredStateList['lecturer'     ] = false;
$fieldRequiredStateList['title'        ] = get_conf('human_label_needed');
$fieldRequiredStateList['officialCode' ] = get_conf('human_code_needed');
$fieldRequiredStateList['email'        ] = get_conf('course_email_needed');

/**
 * DB tables definition
 */

$tbl_cdb_names = claro_sql_get_course_tbl();
$tbl_mdb_names = claro_sql_get_main_tbl();

$tbl_rel_course_user  = $tbl_mdb_names['rel_course_user'];
$tbl_course           = $tbl_mdb_names['course'         ];
$tbl_category         = $tbl_mdb_names['category'       ];

/**
 * PRE FILL OF  FORM FIELDS
 */

$thisCourse = claro_get_course_data($_cid);

$course = array();
$course['title']          = isset($_REQUEST['course_title'        ]) ? trim(strip_tags($_REQUEST['course_title'        ])) : $thisCourse['name'    ];
$course['titular']        = isset($_REQUEST['course_titular'      ]) ? trim(strip_tags($_REQUEST['course_titular'      ])) : $thisCourse['titular' ];
$course['language']       = isset($_REQUEST['course_language'     ]) ? trim(strip_tags($_REQUEST['course_language'     ])) : $thisCourse['language'];
$course['email']          = isset($_REQUEST['course_email'        ]) ? trim(strip_tags($_REQUEST['course_email'        ])) : $thisCourse['email'   ];
$course['category']       = isset($_REQUEST['course_category'     ]) ? trim(strip_tags($_REQUEST['course_category'     ])) : $thisCourse['categoryCode'];
$course['officialCode']   = isset($_REQUEST['course_code'         ]) ? trim(strip_tags($_REQUEST['course_code'         ])) : $thisCourse['officialCode'];
$course['departmentName'] = isset($_REQUEST['course_dept_name'    ]) ? trim(strip_tags($_REQUEST['course_dept_name'    ])) : $thisCourse['extLinkName'];
$course['departmentUrl']  = isset($_REQUEST['course_dept_url'     ]) ? trim(strip_tags($_REQUEST['course_dept_url'     ])) : $thisCourse['extLinkUrl'];
$course['enrolmentKey']   = isset($_REQUEST['course_enrolment_key']) ? trim(strip_tags($_REQUEST['course_enrolment_key'])) : $thisCourse['enrollmentKey'];

$course['access']         = isset($_REQUEST['course_access'       ]) ? (bool) $_REQUEST['course_access'       ] : $thisCourse['visibility'];
$course['enrolment']      = isset($_REQUEST['course_enrolment'    ]) ? (bool) $_REQUEST['course_enrolment'    ] : $thisCourse['registrationAllowed'];

$visibility               = getCourseVisibility($course['access'],$course['enrolment']);

$directory                = $thisCourse['path'   ];
$currentCourseID          = $thisCourse['sysCode'];
$currentCourseRepository  = $thisCourse['path'   ];

// in case of admin access (from admin tool) to the script,
// we must determine which course we are working with

if ( isset($_REQUEST['cidToEdit']) && $is_platformAdmin )
{
    $interbredcrump[]= array ('url' => $rootAdminWeb, 'name' => get_lang('Administration'));
    // braedcrumb different in admin access
    unset($_cid);
    $current_cid = trim($_REQUEST['cidToEdit']);
    $toAddtoURL = '&amp;cidToEdit=' . $cidToEdit;
}
else
{
    $current_cid = $_course['sysCode'];
}

/******************************************************************************
                                 SUBMIT PROCESS
 ******************************************************************************/

if ( isset($_REQUEST['changeProperties']) )
{

/*----------------------------------------------------------------------------
                                 DATA CHECKING
  ----------------------------------------------------------------------------*/
    $errorMsgList = array();

    if ( empty($course['title'])        && $fieldRequiredStateList['title'])
        $errorMsgList[] = get_lang('Course title needed');
    if ( is_null($course['category'])   && $fieldRequiredStateList['category'])
        $errorMsgList[] = get_lang('Category needed (you must choose a category)');
    if ( empty($course['officialCode']) && $fieldRequiredStateList['officialCode'])
        $errorMsgList[] = get_lang('Course code needed');
    if ( empty($course['email'])        && $fieldRequiredStateList['email'])
        $errorMsgList[] = get_lang('Email needed');


    // check if department url is set properly
    $regexp = "^(http|https|ftp)\://[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(:[a-zA-Z0-9]*)?/?([a-zA-Z0-9\-\._\?\,\'/\\\+&%\$#\=~])*$";
    if ( (!empty($course['departmentUrl'])) && !eregi( $regexp, $course['departmentUrl']) )
    {
        // problem with url. try to repair
        // if  it  only the protocol missing add http
        if (eregi('^[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(:[a-zA-Z0-9]*)?/?([a-zA-Z0-9\-\._\?\,\'/\\\+&%\$#\=~])*$', $course['departmentUrl'])
        && (eregi($regexp, 'http://' . $course['departmentUrl'])))
        {
            $course['departmentUrl'] = 'http://' . $course['departmentUrl'];
        }
        else
        {
             $errorMsgList[] = get_lang('Department URL is not valid');
        }
    }

    /**
     * Check e-mail validity
     *
     * email can be a list
     *  accept ; [space] and , as separator but  if all is right replace all by ;
     * if  one is wrong display the erronous and dont change
     */
    if ( ! empty( $course['email'] ) || $fieldRequiredStateList['email'] )
    {
        $is_emailListValid = true;

        /* TODO check if the fix for bug #716 and 717 does not break the code
         * since moosh does not remember why the strpos was there.
         */
        $emailControlList = strtr($course['email'],', ',';');
        $emailControlList = preg_replace( '/;+/', ';', $emailControlList );

        $emailControlList = explode(';',$emailControlList);
        
        foreach ($emailControlList as $emailControl )
        {
            if ( ! is_well_formed_email_address( trim($emailControl)) )
            {
                $is_emailListValid = false;
                $errorMsgList[] = get_lang('The email address is not valid');
            }
            else
            {
                $emailValidList[] = trim($emailControl);
            }
        }

        if ($is_emailListValid && is_array($emailValidList))
        {
            $course['email'] = implode(';',$emailValidList);
        }
    }

    if ( count($errorMsgList) > 0)
    {
        $dialogBox .= '<p>'
        .             get_lang('Unable to save')
        .             '<br />'
        .             implode('<br />' , $errorMsgList)
        .             '</p>'
        ;
        $dbUpdateAllowed =  false;
    }
    else
    {
        $dbUpdateAllowed = true;
    }

    // if at least one error is found, we cancel update

    if ( $dbUpdateAllowed )
    {
        /**
         * @todo TODO create a function and  merge this  job with create course
         */
        $sql = "UPDATE `" . $tbl_course . "`
                SET `intitule`         = '" . addslashes($course['title'])       . "',
                    `faculte`          = '" . addslashes($course['category'])    . "',
                    `titulaires`       = '" . addslashes($course['titular'])      . "',
                    `fake_code`        = '" . addslashes($course['officialCode']). "',
                    `languageCourse`   = '" . addslashes($course['language'])    . "',
                    `departmentUrlName`= '" . addslashes($course['departmentName'])       . "',
                    `departmentUrl`    = '" . addslashes($course['departmentUrl'])        . "',
                    `email`            = '" . addslashes($course['email'])       . "',
                    `enrollment_key`   = '" . addslashes($course['enrolmentKey'])     . "',
                    `visible`          = "  . (int) $visibility         . "
                WHERE code='" . addslashes($current_cid) . "'";

        claro_sql_query($sql);

        $dialogBox = get_lang('The information has been modified');


    }


$cidReset = true;
$cidReq = $current_cid;
include($includePath . '/claro_init_local.inc.php');
}

/******************************************************************************
                                     OUTPUT
 ******************************************************************************/

include $includePath . '/claro_init_header.inc.php';

echo claro_html_tool_title($nameTools);
if ( ! empty ($dialogBox) ) echo claro_html_message_box($dialogBox);



$toAdd='';

if ( isset($cidToEdit) )
{
    $toAdd ='?cidToEdit=' . $current_cid;
    $toAdd.='&amp;cfrom=' . $cfrom;
}

// initialise links array

$links = array();

// add course home link

$url_course = $clarolineRepositoryWeb . 'course/index.php?cid=' . htmlspecialchars($_cid);
$url_course_edit_tool_list = $clarolineRepositoryWeb . 'course/tools.php';
$url_course_tracking = $clarolineRepositoryWeb . 'tracking/courseLog.php';
$url_admin = $clarolineRepositoryWeb . 'admin/index.php';
$url_course_delete = $clarolineRepositoryWeb . 'course/delete.php';
$url_course_import = $clarolineRepositoryWeb . 'course/import.php';
$url_course_export = $clarolineRepositoryWeb . 'course/export.php';
$url_admin_course = $clarolineRepositoryWeb . 'admin/admincourses.php'. $toAdd ;

// add course tool list edit

$links[] = '<a class="claroCmd" href="' . $url_course_edit_tool_list . '">'
.          '<img src="' . $imgRepositoryWeb . 'edit.gif" alt="" />'
.          get_lang('Edit Tool list')
.          '</a>'
;

// Main group settings
$links[] = '<a class="claroCmd" href="../group/group_properties.php">'
.          '<img src="' . $imgRepositoryWeb . 'settings.gif" alt="" />'
.          get_lang("Main Group Settings")
.          '</a>'
;

// add tracking link

if ( get_conf('is_trackingEnabled') )
{
    $links[] = '<a class="claroCmd" href="' . $url_course_tracking . '">'
    .          '<img src="' . $imgRepositoryWeb . 'statistics.gif" alt="" />'
    .          get_lang('Statistics')
    .          '</a>'
    ;
}



// add link to admin page

if ( $is_platformAdmin && isset($_REQUEST['adminContext']) )
{
    $links[] = '<a class="claroCmd" href="' . $url_admin . '">'
    .          get_lang('Back to administration page')
    .          '</a>'
    ;
}

// add delete course link

if ( get_conf('showLinkToDeleteThisCourse') )
{

    $links[] = '<a class="claroCmd" href="' . $url_course_delete . '">'
    .          '<img src="' . $imgRepositoryWeb . 'delete.gif" alt="" />'
    .          get_lang('Delete the whole course website')
    .          '</a>'
    ;
}

// export course link

if ( get_conf('showLinkToExportThisCourse',false) )
{
    $links[] = '<a class="claroCmd" href="' . $url_course_export . '" '
    .          '   title="' . get_lang('Save the whole course website') . '">'
    .          '<img src="' . $imgRepositoryWeb . 'export.gif" alt="" />'
    .          get_lang('Backup')
    .          '</a>'
    ;
}

// import course link

if ( get_conf('showLinkToImportThisCourse',false) )
{
    $links[] = '<a class="claroCmd" href="' . $url_course_import . '"'
    .          '   title="' . get_lang('Import course data from an archive') . '>'
    .          '<img src="' . $imgRepositoryWeb . 'import.gif" alt="" />'
    .          get_lang('Import')
    .          '</a>'
    ;
}

// add link to course admin page

if ( isset($cfrom) && ($is_platformAdmin) )
{
    if ( $cfrom == 'clist' )  //in case we come from the course list in admintool
    {
        $links[] = '<a class="claroCmd" href="' . $url_admin_course . '">'
        .          get_lang('Back to list')
        .          '</a>'
        ;
    }
}


echo '<p>'
.    claro_html_menu_horizontal($links)
.    '</p>'
;

// Display form

echo course_display_form($course,$currentCourseID);

// Display footer
include $includePath . '/claro_init_footer.inc.php' ;
?>
