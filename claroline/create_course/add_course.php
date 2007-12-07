<?php // $Id$
/**
 * CLAROLINE
 *
 * This  script  manage the creation of a new course.
 *
 * it contain 3 panel
 * - Form
 * - Wait
 * - Done
 *
 * @version 1.7 $Revision$
 *
 * @copyright (c) 2001-2005 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @see http://www.claroline.net/wiki/CLCRS/
 *
 * @package COURSE
 *
 * @author Claro Team <cvs@claroline.net>
 *
 */

define('DISP_COURSE_CREATION_FORM'     ,__LINE__);
define('DISP_COURSE_CREATION_SUCCEED'  ,__LINE__);
define('DISP_COURSE_CREATION_FAILED'   ,__LINE__);
define('DISP_COURSE_CREATION_PROGRESS' ,__LINE__);

require '../inc/claro_init_global.inc.php';

if ( ! $_uid )                   claro_disp_auth_form();
if ( ! $is_allowedCreateCourse ) claro_die($langNotAllowed);

include $includePath . '/conf/course_main.conf.php';
require_once $includePath . '/lib/add_course.lib.inc.php';
require_once $includePath . '/lib/course.lib.inc.php';
require_once $includePath . '/lib/fileManage.lib.php';
require_once $includePath . '/lib/form.lib.php';
require_once $includePath . '/lib/claro_mail.lib.inc.php';

$cmd = isset($_REQUEST['cmd']) ? $_REQUEST['cmd'] : null;

/*
 * DATA PREFILL
 */

$courseTitle         = isset($_REQUEST['title'          ]) ? trim(strip_tags($_REQUEST['title'          ])) : null;
$courseHolder        = isset($_REQUEST['holder'         ]) ? trim(strip_tags($_REQUEST['holder'         ])) : $_user['firstName'].' '.$_user['lastName'];
$courseLanguage      = isset($_REQUEST['languageCourse' ]) ? trim(strip_tags($_REQUEST['languageCourse' ])) : $platformLanguage;
$courseEmail         = isset($_REQUEST['email'          ]) ? trim(strip_tags($_REQUEST['email'          ])) : $_user['mail'];
$courseCategory      = isset($_REQUEST['category'       ]) ? trim(strip_tags($_REQUEST['category'       ])) : null;
$courseOfficialCode  = isset($_REQUEST['officialCode'   ]) ? trim(strip_tags($_REQUEST['officialCode'   ])) : null;
$extLinkName         = isset($_REQUEST['extLinkName'    ]) ? trim(strip_tags($_REQUEST['extLinkName'    ])) : null;
$extLinkUrl          = isset($_REQUEST['extLinkUrl'     ]) ? trim(strip_tags($_REQUEST['extLinkUrl'     ])) : null;
$courseEnrollmentKey = isset($_REQUEST['enrollmentKey'  ]) ? trim(strip_tags($_REQUEST['enrollmentKey'  ])) : null;

$courseVisibility   = isset($_REQUEST['courseVisibility']    )
                    ? ($_REQUEST['courseVisibility'         ] == 'true' ? true : false)
                    :  true;
$courseEnrollAllowed = isset($_REQUEST['courseEnrollAllowed'])
                     ? ($_REQUEST['courseEnrollAllowed'] == 'true' ? true : false)
                     : true;


$display   = DISP_COURSE_CREATION_FORM; // default display ...
$errorList = array();



if ( isset($_REQUEST['submitFromCoursProperties']) )
{
    // SUBMITTED DATA CHECKING

    if ( ! $courseTitle && $human_label_needed)                 $errorList[] = $langLabelCanBeEmpty;
    if ( ! $courseOfficialCode && $human_code_needed )          $errorList[] = $langCodeCanBeEmpty;
    if ( ! $courseCategory || $courseCategory == 'choose_one')  $errorList[] = sprintf($lang_p_aCategoryWouldBeSelected, 'mailto:'.$administrator_email);
    if ( empty($courseEmail) && $course_email_needed )          $errorList[] = $langEmailCanBeEmpty;
    if ( ! empty( $courseEmail )
        && ! is_well_formed_email_address($courseEmail) )       $errorList[] = $langEmailWrong;

    if (count ($errorList) > 0) $okToCreate  = FALSE;
    else                       $okToCreate = TRUE;

    // PREPARE COURSE INTERNAL SYSTEM SETTINGS

    $courseOfficialCode = ereg_replace('[^A-Za-z0-9_]', '', $courseOfficialCode);
    $courseOfficialCode = strtoupper($courseOfficialCode);

    $keys = define_course_keys ($courseOfficialCode,'',$dbNamePrefix);

    $courseSysCode      = $keys[ 'currentCourseId'         ];
    $courseDbName       = $keys[ 'currentCourseDbName'     ];
    $courseDirectory    = $keys[ 'currentCourseRepository' ];
    $courseCreationDate = time();

    if ($okToCreate)
    {
        if( isset($_REQUEST['cmd']) && $_REQUEST['cmd'] == 'exCreate' )
        {
            // START COURSE CREATION PORCESSS

            if (   prepare_course_repository($courseDirectory, $courseSysCode)
                && fill_course_repository($courseDirectory)
                && update_db_course($courseDbName)
                && fill_db_course( $courseDbName, $courseLanguage )
                && register_course($courseSysCode
                   ,               $courseOfficialCode
                   ,               $courseDirectory
                   ,               $courseDbName
                   ,               $courseHolder
                   ,               $courseEmail
                   ,               $courseCategory
                   ,               $courseTitle
                   ,               $courseLanguage
                   ,               $_uid
                   ,               $courseVisibility
                   ,               $courseEnrollAllowed
                   ,               $courseEnrollmentKey
                   ,               $courseCreationDate
                   ,               $extLinkName
                   ,               $extLinkUrl)
                )
            {      // COURSE CREATION  SUCEEEDED

                $display = DISP_COURSE_CREATION_SUCCEED;

                // WARN PLATFORM ADMINISTRATOR OF THE COURSE CREATION
                $mailSubject =
                '['.$siteName.'] '.$langCreationMailNotificationSubject.' : '.$courseTitle;

                $mailBody    =
                  claro_disp_localised_date($dateTimeFormatLong) . "\n\n"
                . $langCreationMailNotificationBody .' ' . $siteName . ' '
                . $langByUser . ' ' . $_user['firstName'] . ' ' . $_user['lastName']
                . ' (' . $_user['mail'] . ') '
                . "\n\n"
                . $langCode          . "\t:\t" . $courseOfficialCode  ."\n"
                . $langCourseTitle   . "\t:\t" . $courseTitle         ."\n"
                . $langProfessors    . "\t:\t" . $courseHolder        ."\n"
                . $langEmail         . "\t:\t" . $courseEmail         ."\n"
                . $langCategory      . "\t:\t" . $courseCategory      ."\n"
                . $langLanguage      . "\t:\t" . $courseLanguage      ."\n"
                . "\n"
                . $coursesRepositoryWeb.$courseDirectory."/\n\n"
                ;

                // GET THE CONCERNED SENDEES OF THE EMAIL
                $platformAdminList = claro_get_admin_list ();

                foreach( $platformAdminList as $thisPlatformAdmin )
                {
                    claro_mail_user( $thisPlatformAdmin['idUser'], $mailBody, $mailSubject);
                }
            }
            else
            {
                $lastFailure = claro_failure::get_last_failure();

                switch ($lastFailure )
                {
                    case 'READ_ONLY_SYSTEM_FILE' :
                    {
                        $errorList['error'] = 'READ ONLY SYSTEM FILE';
                    } break;
/*                  case 'CANT_CREATE_COURSE_REP_CLQWZ' :
                    {
                        $errorList['error'] = 'READ ONLY SYSTEM FILE';
                    } break;
                    case 'CANT_CREATE_COURSE_REP_CLDOC' :
                    {
                        $errorList['error'] = 'READ ONLY SYSTEM FILE';
                    } break;
                    case 'CANT_CREATE_COURSE_REP_CLWRK' :
                    {
                        $errorList['error'] = 'READ ONLY SYSTEM FILE';
                    } break;
                    case 'CANT_CREATE_COURSE_REP_CLGRP' :
                    {
                        $errorList['error'] = 'READ ONLY SYSTEM FILE';
                    } break;
                    case 'CANT_CREATE_COURSE_REP_CLCHT' :
                    {
                        $errorList['error'] = 'READ ONLY SYSTEM FILE';
                    } break;
                    case 'CANT_CREATE_COURSE_REP_MODULES' :
                    {
                        $errorList['error'] = 'READ ONLY SYSTEM FILE';
                    } break;
                    case 'CANT_CREATE_COURSE_REP_MODULE_1' :
                    {
                        $errorList['error'] = 'READ ONLY SYSTEM FILE';
                    } break;
                    case 'CANT_CREATE_COURSE_REP_SCORM' :
                    {
                        $errorList['error'] = 'READ ONLY SYSTEM FILE';
                    } break;
                    case 'CANT_CREATE_COURSE_INDEX' :
                    {
                        $errorList['error'] = 'READ ONLY SYSTEM FILE';
                    } break;
                    case 'CANT_WRITE_COURSE_INDEX' :
                    {
                        $errorList['error'] = 'READ ONLY SYSTEM FILE';
                    } break;
                    case 'CANT_SAVE_COURSE_INDEX' :
                    {
                        $errorList['error'] = 'READ ONLY SYSTEM FILE';
                    } break;
*/
                    default:
                    {
                        $errorList['error'] = 'Error code : '. $lastFailure;
                    }
                }
                $display = DISP_COURSE_CREATION_FAILED;


            }
        }
        else
        {       // TRIG WAITING SCREEN AS COURSE CREATION MAY TAKE A WHILE ...
            $display     = DISP_COURSE_CREATION_PROGRESS;

            $paramString = $_SERVER['PHP_SELF'] . '?cmd=exCreate';

            foreach ($_REQUEST as $requestKey => $requestValue)
            {
               $paramString .= '&amp;' . rawurlencode($requestKey) . '=' . rawurlencode($requestValue);
            }

            $htmlHeadXtra[] = '<meta http-equiv="REFRESH" content="0; URL=' . $paramString . '">';

            // $noQUERY_STRING = true; dont the purpose of this statement
        }
    } // end if ($okToCreate)
} // end elseif ($submitFromCoursProperties)


/******************************************************************************
                                     OUTPUT
 ******************************************************************************/

// SPECIAL BRAEADCRUMB WHEN COMING FROM THE PLATFORM ADMINISTRATION PANEL

if ( isset($_REQUEST['fromAdmin']) && $_REQUEST['fromAdmin'] == 'yes' )
{
    $interbredcrump[] = array ('url' => $rootAdminWeb, 'name' => $langAdministration);
}

include $includePath . '/claro_init_header.inc.php';

echo claro_disp_tool_title($langCreateSite);

if ( count($errorList) > 0 ) echo claro_disp_message_box(implode('<br />', $errorList));

/*----------------------------------------------------------------------------
                                    FORM DISPLAY
  ----------------------------------------------------------------------------*/

if( $display == DISP_COURSE_CREATION_FORM )
{
    $language_list        = claro_get_lang_flat_list();
    $courseCategory_array = claro_get_cat_flat_list();

    // If there is no current course category, add a fake option
    // to prevent user to simply select the first in list without purpose

    if ( array_key_exists($courseCategory , $courseCategory_array))
    {
        $cat_preselect = $courseCategory;
    }
    else
    {
        $cat_preselect        = 'choose_one';
        $courseCategory_array = array_merge(array('choose_one'=>'--'),$courseCategory_array);
    }
?>
<form lang="<?php echo $iso639_2_code ?>" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" accept-charset="<?php echo $charset ?>">
<table>
<tr valign="top">
<td colspan="2"></td>
</tr>

<tr valign="top">
<td align="right">
<label for="title"><?php echo ($human_label_needed ? '<span class="required">*</span>' :'') . $langCourseTitle ?></label> :
</td>
<td valign="top">
<input type="Text" name="title" id="title" size="60" value="<?php echo htmlspecialchars($courseTitle) ?>" />
<br /><small><?php echo $langEx ?></small>
</td>
</tr>

<tr valign="top">
<td align="right">
<label for="officialCode"><?php echo ($human_code_needed ? '<span class="required">*</span>' :'') . $langCode ?></label> :
</td>
<td >
    <input type="Text" id="officialCode" name="officialCode" maxlength="12" value="<?php echo htmlspecialchars($courseOfficialCode) ?>" />
    <br />
    <small><?php echo $langMaxSizeCourseCode ?></small>
</td>
</tr>

<tr valign="top">
<td align="right">
<label for="holder"><?php echo $langProfessors ?></label> :
</td>
<td>
<input type="Text" name="holder" id="holder" size="60" value="<?php echo htmlspecialchars($courseHolder) ?>" />
</td>
</tr>

<tr>
<td align="right">
<label for="email"><?php echo ($course_email_needed ? '<span class="required">*</span>' : '') . $langEmail ?></label>&nbsp;:
</td>
<td>
<input type="text" name="email" id="email" value="<?php echo htmlspecialchars($courseEmail); ?>" size="30" maxlength="255">
</td>
</tr>

<tr valign="top">
<td align="right">
<label for="category"><span class="required">*</span><?php echo $langCategory ?></label> :
</td>
<td>
<?php echo claro_html_form_select( 'category'
                                 , $courseCategory_array
                                 , $cat_preselect
                                 , array('id'=>'category'))
                                 ; ?>
<br /><small><?php echo $langTargetFac ?></small>
</td>
</tr>

<tr valign="top">
<td align="right"><label for="extLinkName"><?php echo $langDepartmentUrlName ?></label>&nbsp;: </td>
<td>
<input type="text" name="extLinkName" id="extLinkName" value="" size="20" maxlength="30" />
</td>
</tr>

<tr valign="top" >
<td align="right" nowrap><label for="extLinkUrl" ><?php echo $langDepartmentUrl ?></label>&nbsp;:</td>
<td>
<input type="text" name="extLinkUrl" id="extLinkUrl" value="" size="60" maxlength="180" />
</td>
</tr>

<tr valign="top">
<td align="right">
<label for="languageCourse"><span class="required">*</span><?php echo $langLanguage ?></label> :
</td>
<td>
<?php echo claro_html_form_select( 'languageCourse'
                                 , $language_list
                                 , $courseLanguage
                                 , array('id'=>'languageCourse'))
                                 ; ?>
</td>
</tr>
<tr valign="top" >
<td align="right" nowrap><?php echo $langCourseAccess; ?> : </td>
<td>
<input type="radio" id="courseVisibility_true" name="courseVisibility" value="true" <?php echo $courseVisibility ? 'checked':'' ?> />
<label for="courseVisibility_true"><?php echo $langPublicAccess; ?></label><br />
<input type="radio" id="courseVisibility_false" name="courseVisibility" value="false" <?php echo ! $courseVisibility  ?'checked':''; ?> />
<label for="courseVisibility_false"><?php echo strip_tags($langPrivateAccess); ?></label>
</td>
</tr>
<tr valign="top">
<td align="right"><?php echo $langSubscription; ?> : </td>
<td>
<input type="radio" id="courseEnrollAllowed_true" name="courseEnrollAllowed" value="true" <?php echo $courseEnrollAllowed ?'checked':''; ?> />
<label for="allowedToSubscribe_true"><?php echo $langAllowed; ?></label>
<label for="courseEnrollmentKey">
- <?php echo $langEnrollmentKey ?>
<small>(<?php echo strtolower($langOptional); ?>)</small> :
</label>
<input type="text" id="enrollmentKey" name="enrollmentKey" value="<?php echo htmlspecialchars($courseEnrollmentKey); ?>" />
<br />
<input type="radio" id="courseEnrollAllowed_false"  name="courseEnrollAllowed" value="false" <?php echo ! $courseEnrollAllowed ?'checked':''; ?> /> <label for="courseEnrollAllowed_false"><?php echo $langDenied; ?></label>
<tr valign="top">
<td align="right">
<label for="submitFromCoursProperties"><?php echo $langCreate ?> : </label>
</td>
<td>
<input type="Submit" name="submitFromCoursProperties" id ="submitFromCoursProperties" value="<?php echo $langOk?>">
<?php echo claro_disp_button($_SERVER['HTTP_REFERER'], $langCancel); ?>
</td>
</tr>
<tr>
<td></td>
<td>
<?php echo $langLegendRequiredFields ?>
</td>
</tr>
</table>
</form>
<p><?php echo $langExplanation ?>.</p>

<?php
}

/*----------------------------------------------------------------------------
                            RESULT DISPLAY
  ----------------------------------------------------------------------------*/

if ($display == DISP_COURSE_CREATION_FAILED)
{
    if (count ($errorList) > 0 ) $errorString = implode ('<br />'. $errorList);
    else                        $errorString = '';

    echo claro_disp_message_box('<p>Course Creation failed.</p>'
                                . $errorString );
}



if( $display == DISP_COURSE_CREATION_SUCCEED)
{
    // Replace HTML special chars by equivalent - cannot use html_specialchars
    // Special for french

    $dialogBox = "\n"
    .            $langJustCreated
    .            ' : '
    .            '<strong>'
    .            $courseOfficialCode
    .            '</strong>'
    ;

    echo claro_disp_message_box($dialogBox)
    .    '<br />'
    .    '<a class="claroCmd" href="../../index.php">'
    .    $langBackToMyCourseList
    .    '</a>'
    ;
} // if all fields fullfilled

/*----------------------------------------------------------------------------
                    WAIT PANEL DISPLAY
  ----------------------------------------------------------------------------*/


if ( $display == DISP_COURSE_CREATION_PROGRESS )
{
    echo claro_disp_message_box(  $langCreatingCourse
                                .'<br />'
                                .'<p align="center">'
                                .'<img src="' . $imgRepositoryWeb . '/processing.gif" / alt="">'
                                .'</p>'
                                .'<p>'
                                . sprintf($lang_p_IfNothingHappendClickHere,$paramString)
                                .'</p>');
}


include $includePath . '/claro_init_footer.inc.php';
?>
