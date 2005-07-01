<?php // $Id$
/**
 * CLAROLINE 
 *
 * This tool manage properties of an exiting course
 *
 * @version 1.7
 *
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
claro_unquote_gpc();
$nameTools = $langCourseSettings;

$dialogBox = '';

if ( ! $_cid ) claro_disp_select_course();
if ( ! $is_courseAllowed ) claro_disp_auth_form();

include_once( $includePath . '/lib/auth.lib.inc.php');
include_once( $includePath . '/lib/course.lib.inc.php');
include_once( $includePath . '/lib/form.lib.php');
include_once( $includePath . '/conf/course_main.conf.php');

/*
 * Configuration array , define here which field can be left empty or not
 */

 $canBeEmpty['intitule']      = !$human_label_needed;
 $canBeEmpty['category']      = false;
 $canBeEmpty['lecturer']      = true;
 $canBeEmpty['screenCode']    = !$human_code_needed;
 $canBeEmpty['lanCourseForm'] = false;
 $canBeEmpty['extLinkName']   = !$extLinkNameNeeded;
 $canBeEmpty['extLinkUrl']    = !$extLinkUrlNeeded;
 $canBeEmpty['email']         = !$course_email_needed;

/*
 * DB tables definition
 */
 
$tbl_cdb_names = claro_sql_get_course_tbl();
$tbl_mdb_names = claro_sql_get_main_tbl();
$tbl_rel_course_user  = $tbl_mdb_names['rel_course_user'];
$tbl_course           = $tbl_mdb_names['course'         ];
$tbl_category         = $tbl_mdb_names['category'       ];

/*
 * Perfield value for the form :
 */

// Get course data not stored in $_course
$thisCourse = claro_get_course_data($_cid);

$int               = $thisCourse['name'];
$category          = $thisCourse['categoryCode'];
$currentCourseCode = $thisCourse['officialCode'];
$titulary          = $thisCourse['titular'];
$languageCourse    = $thisCourse['language'];
$extLinkName       = $thisCourse['extLink']['name'];
$extLinkUrl        = $thisCourse['extLink']['url'];
$email             = $thisCourse['email'];
$directory         = $thisCourse['path'];
$currentCourseID         = $_course['sysCode'];
$currentCourseRepository = $_course['path'];


//if values were posted, we overwrite DB info with values previously set by user

if (isset($_REQUEST['screenCode']))
{
    $currentCourseCode = $_REQUEST['screenCode'];
}
if (isset($_REQUEST['titulary']))
{
    $titulary = $_REQUEST['titulary'];
}
if (isset($_REQUEST['email']))  
{
    $email = $_REQUEST['email'];
}
if (isset($_REQUEST['int']))
{
    $int = $_REQUEST['int'];
}
if (isset($_REQUEST['extLinkName']))
{
    $extLinkName = $_REQUEST['extLinkName'];
}
if (isset($_REQUEST['extLinkUrl']))
{
    $extLinkUrl = $_REQUEST['extLinkUrl'];
}
if (isset($_REQUEST['lanCourseForm']))
{
    $languageCourse = $_REQUEST['lanCourseForm'];
}
if (isset($_REQUEST['category']))
{
    $category = $_REQUEST['category'];
}
if (isset($_REQUEST['visible']))
{
    if ($_REQUEST['visible']=="true")
    {    
        $thisCourse['visibility'] = TRUE;
    }
    else
    { 
        $thisCourse['visibility'] = FALSE;
    } 
}
if ( isset($_REQUEST['allowedToSubscribe']) )
{
    if ( $_REQUEST['allowedToSubscribe'] == 'true' )
    { 
        $thisCourse['registrationAllowed'] = TRUE;
    }
    else
    {
        $thisCourse['registrationAllowed'] = FALSE;
    }    
}
 
// in case of admin access (from admin tool) to the script, 
// we must determine which course we are working with

if (isset($_REQUEST['cidToEdit']) && ($is_platformAdmin))
{
    $interbredcrump[]= array ('url' => $rootAdminWeb, 'name' => $langAdministration); 
    // bred crump different in admin access
    unset($_cid);
    $current_cid = trim($_REQUEST['cidToEdit']);
    $toAddtoURL = '&amp;cidToEdit=' . $cidToEdit;
}
else
{
    $current_cid = $_course['sysCode'];
}

####################### SUBMIT #################################

$is_allowedToEdit = $is_courseAdmin || $is_platformAdmin;

if( $is_allowedToEdit )
{
    // check if form submitted
    if ( isset($_REQUEST['changeProperties']) )
    {
        //create error message(s) if fields are not set properly
        
        if ((!$canBeEmpty['intitule']) && $_REQUEST['int'] == '')
            $dialogBox .= $langErrorCourseTitleEmpty . '<br>';
        if ((!$canBeEmpty['category']) && $_REQUEST['category'] == '')
            $dialogBox .= $langErrorCategoryEmpty . '<br>';
        if ((!$canBeEmpty['lecturer']) && $_REQUEST['titulary'] == '')
            $dialogBox .= $langErrorLecturerEmpty . '<br>';
        if ((!$canBeEmpty['screenCode']) && $_REQUEST['screenCode'] == '')
            $dialogBox .= $langErrorCourseCodeEmpty . '<br>';
        if ((!$canBeEmpty['lanCourseForm']) && $_REQUEST['lanCourseForm'] == '')
            $dialogBox .= $langErrorLanguageEmpty . '<br>';
        if ((!$canBeEmpty['extLinkName']) && $_REQUEST['extLinkName'] == '')
            $dialogBox .= $langErrorDepartmentEmpty . '<br>';
        if ((!$canBeEmpty['extLinkUrl']) && $_REQUEST['extLinkUrl'] == '')
            $dialogBox .= $langErrorDepartmentURLEmpty . '<br>';
        if ((!$canBeEmpty['email']) && $_REQUEST['email'] == '')
            $dialogBox .= $langErrorEmailEmpty . '<br>';
            
        // check if department url is set properly
        
        $regexp = "^(http|https|ftp)\://[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(:[a-zA-Z0-9]*)?/?([a-zA-Z0-9\-\._\?\,\'/\\\+&%\$#\=~])*$";
        
        if ((!empty($_REQUEST['extLinkUrl'])) && !eregi( $regexp, $_REQUEST['extLinkUrl']))            
            $dialogBox .= $langErrorDepartmentURLWrong . '<br>';
        
        //check e-mail validity

        if ( !empty($_REQUEST['email']) && ! is_well_formed_email_address( $_REQUEST['email'] ) )
        {
            $dialogBox .= $langErrorEmailInvalid . '<br>';
        }
        
        //if at least one error is found, we cancel update
        
        if (!$dialogBox)
        {

            
            //build query to update course table in DB
        
            if ($_REQUEST['int'] != '' || $canBeEmpty['int'])
                $fieldsToUpdate[]= "`intitule`='" . addslashes( trim(  $_REQUEST['int'] ) ) . "'";
                
            if ($_REQUEST['category'] != '' || $canBeEmpty['category'])
                $fieldsToUpdate[]= "`faculte`='" . addslashes( trim(   $_REQUEST['category'] ) ) . "'";
                
            if ( $_REQUEST["titulary"] != '' || $canBeEmpty['titulary'])
                $fieldsToUpdate[]= "`titulaires`='" . addslashes( trim(  $_REQUEST['titulary'] ) ) . "'";
                
            if ($_REQUEST['screenCode'] != '' || $canBeEmpty['screenCode'])
                $fieldsToUpdate[]= "`fake_code`='" . addslashes( trim( $_REQUEST['screenCode'] ) ) . "'";
                
            if ($_REQUEST['lanCourseForm'] != '' || $canBeEmpty['lanCourseForm'])
                $fieldsToUpdate[]= "`languageCourse`='" . addslashes( trim(    $_REQUEST['lanCourseForm'] ) ) . "'";
                
            if ($_REQUEST['extLinkName'] != '' || $canBeEmpty['extLinkName'])
                $fieldsToUpdate[]= "`departmentUrlName`='" . addslashes( trim( $_REQUEST['extLinkName'] ) ) . "'";    
                
            if ($_REQUEST['extLinkUrl'] !='' || $canBeEmpty['extLinkUrl'])
                $fieldsToUpdate[]= "`departmentUrl`='" . addslashes( trim(   $_REQUEST['extLinkUrl'] ) ) . "'";
                
            if($_REQUEST['email'] != '' || $canBeEmpty['email'])
                $fieldsToUpdate[]= "`email`='" . addslashes( trim( $_REQUEST['email'] ) ) . "'";
                
            if ($_REQUEST['visible'] == 'false'     && $_REQUEST['allowedToSubscribe']=='false')
                $fieldsToUpdate[]= "visible='0'";
            elseif ($_REQUEST['visible'] == 'false' && $_REQUEST['allowedToSubscribe']=='true')
                $fieldsToUpdate[]= "visible='1'";
            elseif ($_REQUEST['visible'] == 'true'  && $_REQUEST['allowedToSubscribe']=='false')
                $fieldsToUpdate[]= "visible='3'";
            elseif ($_REQUEST['visible'] == 'true'  && $_REQUEST['allowedToSubscribe']=='true')
                $fieldsToUpdate[]= "visible='2'";
                
            //update in DB
            $sql = "UPDATE `" . $tbl_course . "`
                        SET " . implode(',', $fieldsToUpdate) . "
                        WHERE code='" . addslashes($current_cid) . "'";
            
            claro_sql_query($sql);

            $dialogBox = $langModifDone;
        }
    
    
    $cidReset = true;
    $cidReq = $current_cid;
    include($includePath . '/claro_init_local.inc.php');

/**
 * FORM
 */
    
}

//////////////////////PREPARE DISPLAY

$language_list = claro_get_lang_flat_list();

$category_array = claro_get_cat_flat_list();
// If there is no current $category, add a fake option 
// to prevent auto select the first in list
// to prevent auto select the first in list
if ( array_key_exists($category,$category_array))
{ 
    $cat_preselect = $category;
}
else 
{
    $cat_preselect = 'choose_one';
    $category_array = array_merge(array('choose_one'=>'--'),$category_array);
}


















//////////////////////////////////////////////////////////////
/////////////////////// OUTPUT

include($includePath . '/claro_init_header.inc.php' );

echo claro_disp_tool_title($nameTools);
//display dialogbox with error and/or action(s) done to user
            
if (!empty ($dialogBox)) 
{
    echo claro_disp_message_box($dialogBox);
}

?>
<form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">

<table  cellpadding="3" border="0">

<tr>
<td align="right"><label for="int"><?php echo $langCourseTitle ?></label> :</td>
<td><input type="Text" name="int" id="int" value="<?php echo htmlentities($int); ?>" size="60"></td>
</tr>

<tr>
<td align="right"><label for="screenCode"><?php echo $langCode ?></label>&nbsp;:</td>
<td><input type="text" id="screenCode" name="screenCode" value="<?php echo htmlentities($currentCourseCode); ?>" size="20"></td>
</tr>

<tr>
<td align="right"><label for="titulary"><?php echo $langProfessors ?></label>&nbsp;:</td>
<td><input type="text"  id="titulary" name="titulary" value="<?php echo htmlentities($titulary); ?>" size="60"></td>
</tr>

<tr>
<td align="right"><label for="email"><?php echo $langEmail ?></label>&nbsp;:</td>
<td><input type="text"  id="email" name="email" value="<?php echo htmlentities($email); ?>" size="30" maxlength="255"></td>
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
<td><input type="text" name="extLinkName" id="extLinkName" value="<?php echo htmlentities($extLinkName); ?>" size="20" maxlength="30"></td>
</tr>

<tr>
<td align="right" nowrap><label for="extLinkUrl" ><?php echo $langDepartmentUrl ?></label>&nbsp;:</td>
<td><input type="text" name="extLinkUrl" id="extLinkUrl" value="<?php echo htmlentities($extLinkUrl); ?>" size="60" maxlength="180"></td>
</tr>

<tr>
<td valign="top" align="right"><label for="lanCourseForm"><?php echo $langLanguage ?></label> : </td>
<td>
<?php echo claro_html_form_select( 'lanCourseForm'
                                 , $language_list
                                 , $languageCourse
                                 , array('id'=>'lanCourseForm'))
                                 ; ?>

<br><small><font color="gray"><?php echo $langTipLang ?></font></small>
</td>
</td>
</tr>

<tr>
<td></td>
<td>
<?php
if (isset($cidToEdit) && ($is_platformAdmin))
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
<input type="radio" id="visible_true" name="visible" value="true" <?php echo $thisCourse['visibility']?'checked':'' ?>> <label for="visible_true"><?php echo $langPublicAccess; ?></label><br>
<input type="radio" id="visible_false" name="visible" value="false" <?php echo !$thisCourse['visibility']?'checked':''; ?>> <label for="visible_false"><?php echo $langPrivateAccess; ?></label>
</td>
</tr>

<tr>
<td valign="top"align="right"><?php echo $langSubscription; ?> : </td>
<td>
<input type="radio" id="allowedToSubscribe_true" name="allowedToSubscribe" value="true" <?php echo $thisCourse['registrationAllowed']?'checked':''; ?>> <label for="allowedToSubscribe_true"><?php echo $langAllowed; ?></label><br>
<input type="radio" id="allowedToSubscribe_false"  name="allowedToSubscribe" value="false" <?php echo !$thisCourse['registrationAllowed']?'checked':''; ?>> <label for="allowedToSubscribe_false"><?php echo $langDenied; ?></label>
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
<?php echo claro_disp_button($_SERVER['HTTP_REFERER'], $langCancel); ?>
</td>
</tr>

</table>
</form>
<hr noshade size="1">
<?php

$toAdd='';

if($showLinkToDeleteThisCourse)
{
    if (isset($cidToEdit))
    {
        $toAdd ='?cidToEdit=' . $current_cid;
        $toAdd.='&amp;cfrom=' . $cfrom;
    }

    echo '<a class="claroCmd" href="delete_course.php' . $toAdd . '">'
    .    '<img src="' . $imgRepositoryWeb . 'delete.gif">'
    .    $langDelCourse
    .    '</a>'
    
    .    ' | '
    .    '<a class="claroCmd" href="' . $clarolineRepositoryWeb . 'course_home/course_home_edit.php">'
    .    '<img src="' . $imgRepositoryWeb . 'edit.gif">'
    .    $langEditToolList 
    .    '</a>';

    if( $is_trackingEnabled )
    {
        echo ' | <a class="claroCmd" href="' . $clarolineRepositoryWeb . 'tracking/courseLog.php">'
        .    '<img src="' . $imgRepositoryWeb . 'statistics.gif" alt="">'
        .    $langStatistics
        .    '</a>'
        ;
    }

    echo ' | '
    .    '<a class="claroCmd" href="' . $coursesRepositoryWeb . $currentCourseRepository . '/index.php">' 
    .    '<img src="' . $imgRepositoryWeb . 'course.gif" alt="">'
    .    $langHome 
    .    '</a>'
    ;


    if( $is_platformAdmin && isset($_REQUEST['adminContext']) )
    {
        echo ' | '
        .    '<a class="claroCmd" href="../admin/index.php">' 
        .    $langBackToAdmin 
        .    '</a>'
        ;
    }

    if (isset($cfrom) && ($is_platformAdmin))
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
}   // if uid==prof_id
####################STUDENT VIEW ##################################
else
{
    echo $langNotAllowed;
}   // else
include( $includePath . '/claro_init_footer.inc.php');
?>