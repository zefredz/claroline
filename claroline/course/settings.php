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

require '../inc/claro_init_global.inc.php';

$nameTools = get_lang('Course settings');

$dialogBox = '';

if ( ! $_cid || ! $_uid) claro_disp_auth_form(true);

$is_allowedToEdit = $is_courseAdmin;

if ( ! $is_allowedToEdit )
{
    claro_die(get_lang('Not allowed'));
}

include_once $includePath . '/lib/auth.lib.inc.php';
include_once $includePath . '/lib/course.lib.inc.php';
include_once $includePath . '/lib/form.lib.php';
include_once $includePath . '/conf/course_main.conf.php';

/**
 * Configuration array , define here which field can be left empty or not
 */

$fieldRequiredStateList['category'     ] = true;
$fieldRequiredStateList['lanCourseForm'] = true;
$fieldRequiredStateList['lecturer'     ] = false;
$fieldRequiredStateList['intitule'     ] = get_conf('human_label_needed');
$fieldRequiredStateList['screenCode'   ] = get_conf('human_code_needed');
$fieldRequiredStateList['extLinkName'  ] = get_conf('extLinkNameNeeded');
$fieldRequiredStateList['extLinkUrl'   ] = get_conf('extLinkUrlNeeded');
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

$courseTitle        = isset($_REQUEST['int'          ]) ? trim(strip_tags($_REQUEST['int'          ])) : $thisCourse['name'    ];
$courseHolder       = isset($_REQUEST['titulary'     ]) ? trim(strip_tags($_REQUEST['titulary'     ])) : $thisCourse['titular' ];
$courseLanguage     = isset($_REQUEST['lanCourseForm']) ? trim(strip_tags($_REQUEST['lanCourseForm'])) : $thisCourse['language'];
$courseEmail        = isset($_REQUEST['email'        ]) ? trim(strip_tags($_REQUEST['email'        ])) : $thisCourse['email'   ];
$courseCategory     = isset($_REQUEST['category'     ]) ? trim(strip_tags($_REQUEST['category'     ])) : $thisCourse['categoryCode'];
$courseOfficialCode = isset($_REQUEST['screenCode'   ]) ? trim(strip_tags($_REQUEST['screenCode'   ])) : $thisCourse['officialCode'];
$extLinkName        = isset($_REQUEST['extLinkName'  ]) ? trim(strip_tags($_REQUEST['extLinkName'  ])) : $thisCourse['extLinkName'];
$extLinkUrl         = isset($_REQUEST['extLinkUrl'   ]) ? trim(strip_tags($_REQUEST['extLinkUrl'   ])) : $thisCourse['extLinkUrl'];
$enrollmentKey      = isset($_REQUEST['enrollmentKey']) ? trim(strip_tags($_REQUEST['enrollmentKey'])) : $thisCourse['enrollmentKey'];

$visibility          = isset($_REQUEST['visible']           )
                       ? ($_REQUEST['visible'            ] == 'true' ? true : false)
                       :  $thisCourse['visibility'];
$registrationAllowed = isset($_REQUEST['allowedToSubscribe'])
                       ? ($_REQUEST['allowedToSubscribe'] == 'true' ? true : false)
                       : $thisCourse['registrationAllowed'];

$directory               = $thisCourse['path'   ];
$currentCourseID         = $thisCourse['sysCode'];
$currentCourseRepository = $thisCourse['path'   ];

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

    if ( empty($courseTitle)        && $fieldRequiredStateList['intitule'])
        $errorMsgList[] = get_lang('ErrorCourseTitleEmpty');
    if ( is_null($courseCategory)   && $fieldRequiredStateList['category'])
        $errorMsgList[] = get_lang('ErrorCategoryEmpty');
    if ( empty($courseHolder)       && $fieldRequiredStateList['lecturer'])
        $errorMsgList[] = get_lang('ErrorLecturerEmpty');
    if ( empty($courseOfficialCode) && $fieldRequiredStateList['screenCode'])
        $errorMsgList[] = get_lang('ErrorCourseCodeEmpty');
    if ( empty($courseLanguage)     && $fieldRequiredStateList['lanCourseForm'])
        $errorMsgList[] = get_lang('ErrorLanguageEmpty');
    if ( empty($extLinkName)        && $fieldRequiredStateList['extLinkName'])
        $errorMsgList[] = get_lang('ErrorDepartmentEmpty');
    if ( empty($extLinkUrl)        && $fieldRequiredStateList['extLinkUrl'])
        $errorMsgList[] = get_lang('ErrorDepartmentURLEmpty');
    if ( empty($courseEmail)        && $fieldRequiredStateList['email'])
        $errorMsgList[] = get_lang('ErrorEmailEmpty');


    // check if department url is set properly
    $regexp = "^(http|https|ftp)\://[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(:[a-zA-Z0-9]*)?/?([a-zA-Z0-9\-\._\?\,\'/\\\+&%\$#\=~])*$";
    if ( (!empty($extLinkUrl)) && !eregi( $regexp, $extLinkUrl) )
    {
        // problem with url. try to repair
        // if  it  only the protocol missing add http
        if (eregi('^[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(:[a-zA-Z0-9]*)?/?([a-zA-Z0-9\-\._\?\,\'/\\\+&%\$#\=~])*$', $extLinkUrl )
        && (eregi($regexp, 'http://' . $extLinkUrl )))
        {
            $extLinkUrl = 'http://' . $extLinkUrl;
        }
        else
        {
             $errorMsgList[] = get_lang('ErrorDepartmentURLWrong');
        }
    }


    /**
     * Check e-mail validity
     *
     * email can be a list
     *  accept ; [space] and , as separator but  if all is right replace all by ;
     * if  one is wrong display the erronous and dont change
     */
    $is_emailListValid = true;
    $emailControlList = (strpos($courseEmail,';')===false) ? strtr($courseEmail,', ',';;'):$email;
    $emailControlList = explode(';',$emailControlList);
    foreach ($emailControlList as $emailControl )
    if ( ! is_well_formed_email_address( trim($emailControl)) )
    {
        $is_emailListValid = false;
        $errorMsgList[] = get_lang('ErrorEmailInvalid') . ' : <i>' . $emailControl . '</i>';
    }
    else
    {
        $emailValidList[] = trim($emailControl);
    }
    if ($is_emailListValid && is_array($emailValidList))
    {
        $courseEmail = implode(';',$emailValidList);
    }
    if ( count($errorMsgList) > 0)
    {
        $dialogBox .= '<p>'
        .             get_lang('NotSaved')
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
        if     ( ! $visibility && ! $registrationAllowed) $visibilityState = 0;
        elseif ( ! $visibility &&   $registrationAllowed) $visibilityState = 1;
        elseif (   $visibility && ! $registrationAllowed) $visibilityState = 3;
        elseif (   $visibility &&   $registrationAllowed) $visibilityState = 2;

        /**
         * @todo TODO create a function and  merge this  job with create course
         */
        $sql = "UPDATE `" . $tbl_course . "`
                SET `intitule`         = '" . addslashes($courseTitle)       . "',
                    `faculte`          = '" . addslashes($courseCategory)    . "',
                    `titulaires`       = '" . addslashes($courseHolder)      . "',
                    `fake_code`        = '" . addslashes($courseOfficialCode). "',
                    `languageCourse`   = '" . addslashes($courseLanguage)    . "',
                    `departmentUrlName`= '" . addslashes($extLinkName)       . "',
                    `departmentUrl`    = '" . addslashes($extLinkUrl)        . "',
                    `email`            = '" . addslashes($courseEmail)       . "',
                    `enrollment_key`   = '" . addslashes($enrollmentKey)     . "',
                    `visible`          = "  . (int) $visibilityState         . "
                WHERE code='" . addslashes($current_cid) . "'";

        claro_sql_query($sql);

        $dialogBox = get_lang('ModifDone');


    }


$cidReset = true;
$cidReq = $current_cid;
include($includePath . '/claro_init_local.inc.php');
}

//////////////////////PREPARE DISPLAY

$language_list = claro_get_lang_flat_list();

$category_array = claro_get_cat_flat_list();
if(get_conf('rootCanHaveCourse', true))
{
    $category_array = array_merge(array('root' => get_lang('top')),$category_array);
}

// If there is no current $courseCategory, add a fake option
// to prevent auto select the first in list
// to prevent auto select the first in list
if ( array_key_exists( $courseCategory, $category_array ) )
{
    $cat_preselect = $courseCategory;
}
else
{
    $cat_preselect = 'choose_one';
    $category_array = array_merge( array('choose_one'=>'--'), $category_array);
}

/******************************************************************************
                                     OUTPUT
 ******************************************************************************/

include $includePath . '/claro_init_header.inc.php';

echo claro_disp_tool_title($nameTools);
if ( ! empty ($dialogBox) ) echo claro_disp_message_box($dialogBox);



$toAdd='';

if ( isset($cidToEdit) )
{
    $toAdd ='?cidToEdit=' . $current_cid;
    $toAdd.='&amp;cfrom=' . $cfrom;
}

// initialise links array

$links = array();

// add course home link

$links[] = '<a class="claroCmd" href="' . $clarolineRepositoryWeb . 'course/index.php?cid=' . htmlspecialchars($_cid) . '">'
        .    '<img src="' . $imgRepositoryWeb . 'course.gif" alt="" />'
        .    get_lang('Home')
        .    '</a>';

// add course tool list edit

$links[] = '<a class="claroCmd" href="' . $clarolineRepositoryWeb . 'course/tools.php">'
        .    '<img src="' . $imgRepositoryWeb . 'edit.gif" alt="" />'
        .    get_lang('EditToolList')
        .    '</a>';

// add tracking link

if ( $is_trackingEnabled )
{
    $links[] = '<a class="claroCmd" href="' . $clarolineRepositoryWeb . 'tracking/courseLog.php">'
            .    '<img src="' . $imgRepositoryWeb . 'statistics.gif" alt="" />'
            .    get_lang('Statistics')
            .    '</a>' ;
}


// add link to admin page

if ( $is_platformAdmin && isset($_REQUEST['adminContext']) )
{
    $links[] = '<a class="claroCmd" href="../admin/index.php">'
            .    get_lang('BackToAdmin')
            .    '</a>';
}

// add delete course link

if ( get_conf('showLinkToDeleteThisCourse') )
{

    $links[] = '<a class="claroCmd" href="' . $clarolineRepositoryWeb . 'course/delete.php' . $toAdd . '">'
    .    '<img src="' . $imgRepositoryWeb . 'delete.gif" alt="" />'
    .    get_lang('DelCourse')
    .    '</a>';
}


// add link to course admin page

if ( isset($cfrom) && ($is_platformAdmin) )
{
    if ( $cfrom == 'clist' )  //in case we come from the course list in admintool
    {
        $links[] = '<a class="claroCmd" href="../admin/admincourses.php'. $toAdd . '">'
                . get_lang('BackToList')
                . '</a>';
    }
}

// Display links
echo '<p>' . implode(' | ',$links) . '</p>' . "\n";

// Display form

?>
<form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">

<table  cellpadding="3" border="0">

<tr>
<td align="right"><label for="int"><?php echo (get_conf('human_label_needed') ? '<span class="required">*</span>' :'') . get_lang('CourseTitle') ?></label> :</td>
<td><input type="Text" name="int" id="int" value="<?php echo htmlspecialchars($courseTitle); ?>" size="60"></td>
</tr>

<tr>
<td align="right"><label for="screenCode"><?php echo (get_conf('human_code_needed') ? '<span class="required">*</span>' :'') . get_lang('Code') ?></label>&nbsp;:</td>
<td><input type="text" id="screenCode" name="screenCode" value="<?php echo htmlspecialchars($courseOfficialCode); ?>" size="20"></td>
</tr>

<tr>
<td align="right"><label for="titulary"><?php echo get_lang('Professors') ?></label>&nbsp;:</td>
<td><input type="text"  id="titulary" name="titulary" value="<?php echo htmlspecialchars($courseHolder); ?>" size="60"></td>
</tr>

<tr>
<td align="right"><label for="email"><?php echo ( get_conf('course_email_needed') ? '<span class="required">*</span>' : '') . get_lang('Email') ?></label>&nbsp;:</td>
<td><input type="text"  id="email" name="email" value="<?php echo htmlspecialchars($courseEmail); ?>" size="60" maxlength="255"></td>
</tr>

<tr>
<td align="right"><label for="category"><span class="required">*</span><?php echo get_lang('Category') ?></label> :</td>
<td>
<?php echo claro_html_form_select( 'category'
                                 , $category_array
                                 , $cat_preselect
                                 , array('id'=>'category'))
                                 ; ?>
</td>
</tr>

<tr valign="top">
<td align="right"><label for="extLinkName"><?php echo get_lang('DepartmentUrlName') ?></label>&nbsp;: </td>
<td><input type="text" name="extLinkName" id="extLinkName" value="<?php echo htmlspecialchars($extLinkName); ?>" size="20" maxlength="30"></td>
</tr>

<tr valign="top" >
<td align="right" nowrap><label for="extLinkUrl" ><?php echo get_lang('DepartmentUrl') ?></label>&nbsp;:</td>
<td><input type="text" name="extLinkUrl" id="extLinkUrl" value="<?php echo htmlspecialchars($extLinkUrl); ?>" size="60" maxlength="180"></td>
</tr>

<tr valign="top" >
<td align="right">
<label for="lanCourseForm"><span class="required">*</span><?php echo get_lang('Language') ?></label> :
</td>
<td>
<?php echo claro_html_form_select( 'lanCourseForm'
                                 , $language_list
                                 , $courseLanguage
                                 , array('id'=>'lanCourseForm'))
                                 ; ?>

<br /><small><font color="gray"><?php echo get_lang('TipLang') ?></font></small>
</td>
</tr>

<tr>
<td></td>
<td>
<?php
if ( isset($cidToEdit) && ($is_platformAdmin))
{
    echo '<a  href="../admin/admincourseusers.php'
    .    '?cidToEdit=' . $cidToEdit . '">' . get_lang('AllUsersOfThisCourse') . '</a>'
    ;
}
?>
</td>
</tr>

<tr valign="top" >
<td align="right" nowrap><?php echo get_lang('CourseAccess'); ?> : </td>
<td>
<input type="radio" id="visible_true" name="visible" value="true" <?php echo $visibility ? 'checked':'' ?>> <label for="visible_true"><?php echo get_lang('PublicAccess'); ?></label><br />
<input type="radio" id="visible_false" name="visible" value="false" <?php echo ! $visibility  ?'checked':''; ?>> <label for="visible_false"><?php echo get_lang('PrivateAccess'); ?></label>
</td>
</tr>
<tr valign="top">
<td align="right"><?php echo get_lang('Subscription'); ?> : </td>
<td>
<input type="radio" id="allowedToSubscribe_true" name="allowedToSubscribe" value="true" <?php echo $registrationAllowed ?'checked':''; ?>> <label for="allowedToSubscribe_true"><?php echo get_lang('Allowed'); ?></label>
<label for="enrollmentKey">
- <?php echo get_lang('EnrollmentKey') ?> <small>(<?php echo strtolower(get_lang('Optional')); ?>)</small> :
</label>
<input type="text" id="enrollmentKey" name="enrollmentKey" value="<?php echo htmlspecialchars($enrollmentKey); ?>">
<br />
<input type="radio" id="allowedToSubscribe_false"  name="allowedToSubscribe" value="false" <?php echo ! $registrationAllowed ?'checked':''; ?>> <label for="allowedToSubscribe_false"><?php echo get_lang('Denied'); ?></label>
<?php
if (isset($cidToEdit))
{
    echo '<input type="hidden" name="cidToEdit" value="'.$cidToEdit.'">';
}
?>
</td>
</tr>
<tr valign="top">
<td align="right"></small></td>
<td>

</td>
</tr>
<tr>
<td>&nbsp;</td>
<td><small><font color="gray"><?php echo get_lang('ConfTip') ?></font></small></td>
</tr>
<tr>
<td></td>
<td>
<?php echo get_lang('LegendRequiredFields') ?>
</td>
</tr>
<tr>
<td></td>
<td>
<input type="submit" name="changeProperties" value=" <?php echo get_lang('Ok') ?> ">
<?php
echo claro_disp_button( $clarolineRepositoryWeb . 'course/index.php?cid=' . htmlspecialchars($_cid), get_lang('Cancel'));
?>
</td>
</tr>

</table>
</form>
<?php

// Display links
echo '<p>' . implode(' | ',$links) . '</p>' . "\n";

// Display footer
include $includePath . '/claro_init_footer.inc.php' ;

?>
