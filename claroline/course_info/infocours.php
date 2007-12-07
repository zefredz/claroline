<?php // $Id$
/**
 * CLAROLINE 
 *
 * This tool manage properties of an exiting course
 *
 * @version 1.7 $Revision$ 
 * @copyright (c) 2001-2005 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE 
 * 
 * @author claroline Team <cvs@claroline.net>
 *
 * @package CLCRS
 *
 */

require '../inc/claro_init_global.inc.php';

$nameTools = $langCourseSettings;

$dialogBox = '';

if ( ! $_cid || ! $_uid) claro_disp_auth_form(true);

$is_allowedToEdit = $is_courseAdmin;

if ( ! $is_allowedToEdit )
{
    claro_die($langNotAllowed);
}

include_once $includePath . '/lib/auth.lib.inc.php';
include_once $includePath . '/lib/course.lib.inc.php';
include_once $includePath . '/lib/form.lib.php';
include_once $includePath . '/conf/course_main.conf.php';

/*
 * Configuration array , define here which field can be left empty or not
 */

$fieldRequiredStateList['category'     ] = true;
$fieldRequiredStateList['lanCourseForm'] = true;
$fieldRequiredStateList['lecturer'     ] = false;
$fieldRequiredStateList['intitule'     ] = $human_label_needed;
$fieldRequiredStateList['screenCode'   ] = $human_code_needed;
$fieldRequiredStateList['extLinkName'  ] = $extLinkNameNeeded;
$fieldRequiredStateList['extLinkUrl'   ] = $extLinkUrlNeeded;
$fieldRequiredStateList['email'        ] = $course_email_needed;

/*
 * DB tables definition
 */
 
$tbl_cdb_names = claro_sql_get_course_tbl();
$tbl_mdb_names = claro_sql_get_main_tbl();

$tbl_rel_course_user  = $tbl_mdb_names['rel_course_user'];
$tbl_course           = $tbl_mdb_names['course'         ];
$tbl_category         = $tbl_mdb_names['category'       ];


/*
 * PRE FILL OF  FORM FIELDS
 */

$thisCourse = claro_get_course_data($_cid);

$courseTitle        = isset($_REQUEST['int'          ]) ? trim($_REQUEST['int'          ]) : $thisCourse['name'    ];
$courseHolder       = isset($_REQUEST['titulary'     ]) ? trim($_REQUEST['titulary'     ]) : $thisCourse['titular' ];
$courseLanguage     = isset($_REQUEST['lanCourseForm']) ? trim($_REQUEST['lanCourseForm']) : $thisCourse['language'];
$email              = isset($_REQUEST['email'        ]) ? trim($_REQUEST['email'        ]) : $thisCourse['email'   ];
$courseCategory     = isset($_REQUEST['category'     ]) ? trim($_REQUEST['category'     ]) : $thisCourse['categoryCode'];
$courseOfficialCode = isset($_REQUEST['screenCode'   ]) ? trim($_REQUEST['screenCode'   ]) : $thisCourse['officialCode'];
$extLinkName        = isset($_REQUEST['extLinkName'  ]) ? trim($_REQUEST['extLinkName'  ]) : $thisCourse['extLink' ]['name'];
$extLinkUrl         = isset($_REQUEST['extLinkUrl'   ]) ? trim($_REQUEST['extLinkUrl'   ]) : $thisCourse['extLink' ]['url'];

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
    $interbredcrump[]= array ('url' => $rootAdminWeb, 'name' => $langAdministration); 
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
        $errorMsgList[] = $langErrorCourseTitleEmpty;
    if ( empty($courseCategory)     && $fieldRequiredStateList['category'])
        $errorMsgList[] = $langErrorCategoryEmpty;
    if ( empty($courseHolder)       && $fieldRequiredStateList['lecturer'])
        $errorMsgList[] = $langErrorLecturerEmpty;
    if ( empty($courseOfficialCode) && $fieldRequiredStateList['screenCode'])
        $errorMsgList[] = $langErrorCourseCodeEmpty;
    if ( empty($courseLanguage)     && $fieldRequiredStateList['lanCourseForm'])
        $errorMsgList[] = $langErrorLanguageEmpty;
    if ( empty($extLinkName)        && $fieldRequiredStateList['extLinkName'])
        $errorMsgList[] = $langErrorDepartmentEmpty;
    if ( empty($_extLinkUrl)        && $fieldRequiredStateList['extLinkUrl'])
        $errorMsgList[] = $langErrorDepartmentURLEmpty;
    if ( empty($email)              && $fieldRequiredStateList['email'])
        $errorMsgList[] = $langErrorEmailEmpty;

    // check if department url is set properly
    
    $regexp = "^(http|https|ftp)\://[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(:[a-zA-Z0-9]*)?/?([a-zA-Z0-9\-\._\?\,\'/\\\+&%\$#\=~])*$";
    
    if ( (!empty($_REQUEST['extLinkUrl'])) && !eregi( $regexp, $_REQUEST['extLinkUrl']) )
        $errorMsgList[] = $langErrorDepartmentURLWrong;
    
    //check e-mail validity

    if ( ! empty($_REQUEST['email']) && ! is_well_formed_email_address( $_REQUEST['email'] ) )
    {
        $errorMsgList[] = $langErrorEmailInvalid;
    }
    
    if ( count($errorMsgList) > 0)
    {
    	$dialogBox .= '<p>' . implode('<br />' , $errorMsgList) .'</p>';
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

        $sql = "UPDATE `" . $tbl_course . "`
                SET `intitule`         ='" . addslashes($courseTitle)              . "',
                    `faculte`          ='" . addslashes($courseCategory)         . "',
                    `titulaires`       ='" . addslashes($courseHolder)         . "',
                    `fake_code`        ='" . addslashes($courseOfficialCode). "',
                    `languageCourse`   ='" . addslashes($courseLanguage)   . "',
                    `departmentUrlName`='" . addslashes($extLinkName)      . "',
                    `departmentUrl`    ='" . addslashes($extLinkUrl)       . "',
                    `email`            ='" . addslashes($email)            . "',
                    `visible`          ="  . (int) $visibilityState        ."
                WHERE code='" . addslashes($current_cid) . "'";
        
        claro_sql_query($sql);

        $dialogBox = $langModifDone;
    }


$cidReset = true;
$cidReq = $current_cid;
include($includePath . '/claro_init_local.inc.php');
    
}

//////////////////////PREPARE DISPLAY

$language_list = claro_get_lang_flat_list();

$category_array = claro_get_cat_flat_list();
// If there is no current $courseCategory, add a fake option 
// to prevent auto select the first in list
// to prevent auto select the first in list
if ( array_key_exists($courseCategory,$category_array))
{ 
    $cat_preselect = $courseCategory;
}
else 
{
    $cat_preselect = 'choose_one';
    $category_array = array_merge(array('choose_one'=>'--'),$category_array);
}

/******************************************************************************
                                     OUTPUT
 ******************************************************************************/

include $includePath . '/claro_init_header.inc.php';

echo claro_disp_tool_title($nameTools);
if ( ! empty ($dialogBox) ) echo claro_disp_message_box($dialogBox);

// Display form

?>
<form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">

<table  cellpadding="3" border="0">

<tr>
<td align="right"><label for="int"><?php echo $langCourseTitle ?></label> :</td>
<td><input type="Text" name="int" id="int" value="<?php echo htmlspecialchars($courseTitle); ?>" size="60"></td>
</tr>

<tr>
<td align="right"><label for="screenCode"><?php echo $langCode ?></label>&nbsp;:</td>
<td><input type="text" id="screenCode" name="screenCode" value="<?php echo htmlspecialchars($courseOfficialCode); ?>" size="20"></td>
</tr>

<tr>
<td align="right"><label for="titulary"><?php echo $langProfessors ?></label>&nbsp;:</td>
<td><input type="text"  id="titulary" name="titulary" value="<?php echo htmlspecialchars($courseHolder); ?>" size="60"></td>
</tr>

<tr>
<td align="right"><label for="email"><?php echo $langEmail ?></label>&nbsp;:</td>
<td><input type="text"  id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" size="30" maxlength="255"></td>
</tr>

<tr>
<td align="right"><label for="category"><?php echo $langCategory ?></label> :</td>
<td>
<?php echo claro_html_form_select( 'category'
                                 , $category_array
                                 , $cat_preselect
                                 , array('id'=>'category'))
                                 ; ?>
</td>
</tr>

<tr>
<td align="right"><label for="extLinkName"><?php echo $langDepartmentUrlName ?></label>&nbsp;: </td>
<td><input type="text" name="extLinkName" id="extLinkName" value="<?php echo htmlspecialchars($extLinkName); ?>" size="20" maxlength="30"></td>
</tr>

<tr>
<td align="right" nowrap><label for="extLinkUrl" ><?php echo $langDepartmentUrl ?></label>&nbsp;:</td>
<td><input type="text" name="extLinkUrl" id="extLinkUrl" value="<?php echo htmlspecialchars($extLinkUrl); ?>" size="60" maxlength="180"></td>
</tr>

<tr>
<td valign="top" align="right"><label for="lanCourseForm"><?php echo $langLanguage ?></label> : </td>
<td>
<?php echo claro_html_form_select( 'lanCourseForm'
                                 , $language_list
                                 , $courseLanguage
                                 , array('id'=>'lanCourseForm'))
                                 ; ?>

<br /><small><font color="gray"><?php echo $langTipLang ?></font></small>
</td>
</tr>

<tr>
<td></td>
<td>
<?php
if ( isset($cidToEdit) && ($is_platformAdmin))
{
    echo '<a  href="../admin/admincourseusers.php'
    .    '?cidToEdit=' . $cidToEdit . '">' . $langAllUsersOfThisCourse . '</a>'
    ;
}
?>
</td>
</tr>

<tr>
<td valign="top" align="right" nowrap><?php echo $langCourseAccess; ?> : </td>
<td>
<input type="radio" id="visible_true" name="visible" value="true" <?php echo $visibility ? 'checked':'' ?>> <label for="visible_true"><?php echo $langPublicAccess; ?></label><br />
<input type="radio" id="visible_false" name="visible" value="false" <?php echo ! $visibility  ?'checked':''; ?>> <label for="visible_false"><?php echo $langPrivateAccess; ?></label>
</td>
</tr>

<tr>
<td valign="top"align="right"><?php echo $langSubscription; ?> : </td>
<td>
<input type="radio" id="allowedToSubscribe_true" name="allowedToSubscribe" value="true" <?php echo $registrationAllowed ?'checked':''; ?>> <label for="allowedToSubscribe_true"><?php echo $langAllowed; ?></label><br />
<input type="radio" id="allowedToSubscribe_false"  name="allowedToSubscribe" value="false" <?php echo ! $registrationAllowed ?'checked':''; ?>> <label for="allowedToSubscribe_false"><?php echo $langDenied; ?></label>
<?php 
if (isset($cidToEdit))
{
    echo '<input type="hidden" name="cidToEdit" value="'.$cidToEdit.'">';
}
?>
</td>
</tr>

<tr>
<td></td>
<td><small><font color="gray"><?php echo $langConfTip ?></font></small></td>
</tr>

<tr>
<td></td>
<td>
<input type="submit" name="changeProperties" value=" <?php echo $langOk ?> ">
<?php 
echo claro_disp_button( $coursesRepositoryWeb .$currentCourseRepository .'/index.php', $langCancel); 
?>
</td>
</tr>

</table>
</form>
<hr noshade size="1">
<?php

$toAdd='';

if ($showLinkToDeleteThisCourse)
{
    if (isset($cidToEdit))
    {
        $toAdd ='?cidToEdit=' . $current_cid;
        $toAdd.='&amp;cfrom=' . $cfrom;
    }

    echo '<a class="claroCmd" href="delete_course.php' . $toAdd . '">'
    .    '<img src="' . $imgRepositoryWeb . 'delete.gif" alt="" />'
    .    $langDelCourse
    .    '</a>'

    .    ' | '
    .    '<a class="claroCmd" href="' . $clarolineRepositoryWeb . 'course_home/course_home_edit.php">'
    .    '<img src="' . $imgRepositoryWeb . 'edit.gif" alt="" />'
    .    $langEditToolList 
    .    '</a>';

    if ( $is_trackingEnabled )
    {
        echo ' | <a class="claroCmd" href="' . $clarolineRepositoryWeb . 'tracking/courseLog.php">'
        .    '<img src="' . $imgRepositoryWeb . 'statistics.gif" alt="" />'
        .    $langStatistics
        .    '</a>'
        ;
    }

    echo ' | '
    .    '<a class="claroCmd" href="' . $coursesRepositoryWeb . $currentCourseRepository . '/index.php">' 
    .    '<img src="' . $imgRepositoryWeb . 'course.gif" alt="" />'
    .    $langHome 
    .    '</a>'
    ;


    if ( $is_platformAdmin && isset($_REQUEST['adminContext']) )
    {
        echo ' | '
        .    '<a class="claroCmd" href="../admin/index.php">' 
        .    $langBackToAdmin 
        .    '</a>'
        ;
    }

    if ( isset($cfrom) && ($is_platformAdmin) )
    {
        if ($cfrom=="clist")  //in case we come from the course list in admintool
        {
           echo ' | <a class="claroCmd" href="../admin/admincourses.php'
           .    $toAdd 
           .    '">' . $langBackToList . '</a>'
           ;           
        }
    }
}

include( $includePath . '/claro_init_footer.inc.php');

?>
