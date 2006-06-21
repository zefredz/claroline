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

include_once $includePath . '/lib/user.lib.php'; // needed for is_well_formed_email-address()
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
        $errorMsgList[] = get_lang('Course title needed');
    if ( is_null($courseCategory)   && $fieldRequiredStateList['category'])
        $errorMsgList[] = get_lang('Category needed (you must choose a category)');
    if ( empty($courseOfficialCode) && $fieldRequiredStateList['screenCode'])
        $errorMsgList[] = get_lang('Course code needed');
    if ( empty($courseEmail)        && $fieldRequiredStateList['email'])
        $errorMsgList[] = get_lang('Email needed');


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
    if ( ! empty( $courseEmail ) || $fieldRequiredStateList['email'] )
    {
        $is_emailListValid = true;
        $emailControlList = (strpos($courseEmail,';')===false) ? strtr($courseEmail,', ',';;'):$email;
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
            $courseEmail = implode(';',$emailValidList);
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

        $dialogBox = get_lang('The information has been modified');


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
    $category_array = array_merge(array( get_lang('Root') => 'root'),$category_array);
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
    $category_array = array_merge( array('--'=>'choose_one'), $category_array);
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

$links[] = '<a class="claroCmd" href="' . $url_course . '">'
.          '<img src="' . $imgRepositoryWeb . 'course.gif" alt="" />'
.          get_lang('Back to Home page')
.          '</a>'
;

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


echo claro_html_menu_horizontal($links);

// Display form

?>
<form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">

<table  cellpadding="3" border="0">

<tr>
<td align="right"><label for="int"><?php echo (get_conf('human_label_needed') ? '<span class="required">*</span>' :'') . get_lang('Course title') ?></label> :</td>
<td><input type="Text" name="int" id="int" value="<?php echo htmlspecialchars($courseTitle); ?>" size="60"></td>
</tr>

<tr>
<td align="right"><label for="screenCode"><?php echo (get_conf('human_code_needed') ? '<span class="required">*</span>' :'') . get_lang('Course code') ?></label>&nbsp;:</td>
<td><input type="text" id="screenCode" name="screenCode" value="<?php echo htmlspecialchars($courseOfficialCode); ?>" size="20"></td>
</tr>

<tr>
<td align="right"><label for="titulary"><?php echo get_lang('Lecturer(s)') ?></label>&nbsp;:</td>
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
<td align="right"><label for="extLinkName"><?php echo get_lang('Department') ?></label>&nbsp;: </td>
<td><input type="text" name="extLinkName" id="extLinkName" value="<?php echo htmlspecialchars($extLinkName); ?>" size="20" maxlength="30"></td>
</tr>

<tr valign="top" >
<td align="right" nowrap><label for="extLinkUrl" ><?php echo get_lang('Department URL') ?></label>&nbsp;:</td>
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
</td>
</tr>

<tr>
<td></td>
<td>
<?php
if ( isset($cidToEdit) && ($is_platformAdmin))
{
    $url_admin_course_user = $clarolineRepositoryWeb . 'admin/admincourseusers.php?cidToEdit=' . $cidToEdit ;
    echo '<a  href="' . $url_admin_course_user . '">' . get_lang('Course members') . '</a>' ;
}
?>
</td>
</tr>

<tr valign="top" >
<td align="right" nowrap><?php echo get_lang('Course access'); ?> : </td>
<td>
<img src="<?php echo $imgRepositoryWeb ?>access_open.gif" /> 
<input type="radio" id="visible_true" name="visible" value="true" <?php echo $visibility ? 'checked':'' ?>> <label for="visible_true"><?php echo get_lang('Public access from campus home page even without login'); ?></label><br />
<img src="<?php echo $imgRepositoryWeb ?>access_locked.gif" /> 
<input type="radio" id="visible_false" name="visible" value="false" <?php echo ! $visibility  ?'checked':''; ?>> <label for="visible_false"><?php echo get_lang('Private access (site accessible only to people on the <a href="%url">User list</a>)',array('%url'=> '../user/user.php')); ?></label>
</td>
</tr>
<tr valign="top">
<td align="right"><?php echo get_lang('Enrolment'); ?> : </td>
<td>
<img src="<?php echo $imgRepositoryWeb ?>enroll_open.gif" /> 
<input type="radio" id="allowedToSubscribe_true" name="allowedToSubscribe" value="true" <?php echo $registrationAllowed ?'checked':''; ?>> <label for="allowedToSubscribe_true"><?php echo get_lang('Allowed'); ?></label>
<label for="enrollmentKey">
- <?php echo get_lang('enrollment key') ?> <small>(<?php echo strtolower(get_lang('Optional')); ?>)</small> :
</label>
<input type="text" id="enrollmentKey" name="enrollmentKey" value="<?php echo htmlspecialchars($enrollmentKey); ?>">
<br />
<img src="<?php echo $imgRepositoryWeb ?>enroll_locked.gif" /> 
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
<td><small><font color="gray"><?php echo get_block('blockCourseSettingsTip') ?></font></small></td>
</tr>
<tr>
<td></td>
<td>
<?php echo get_lang('<span class=\"required\">*</span> denotes required field') ?>
</td>
</tr>
<tr>
<td></td>
<td>
<input type="submit" name="changeProperties" value=" <?php echo get_lang('Ok') ?> ">
<?php
echo claro_html_button( $clarolineRepositoryWeb . 'course/index.php?cid=' . htmlspecialchars($_cid), get_lang('Cancel'))
.    '</td>' . "\n"
.    '</tr>' . "\n"
.    '</table>' . "\n"
.    '</form>' . "\n"
.    claro_html_menu_horizontal($links)
;
// Display footer
include $includePath . '/claro_init_footer.inc.php' ;

?>