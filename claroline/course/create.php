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
 * @version 1.8 $Revision$
 *
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @see http://www.claroline.net/wiki/CLCRS/
 *
 * @package COURSE
 *
 * old version : http://cvs.claroline.net/cgi-bin/viewcvs.cgi/claroline/claroline/create_course/add_course.php
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
if ( ! $is_allowedCreateCourse ) claro_die(get_lang('Not allowed'));

include $includePath . '/conf/course_main.conf.php';
require_once $includePath . '/lib/add_course.lib.inc.php';
require_once $includePath . '/lib/course.lib.inc.php';
require_once $includePath . '/lib/user.lib.php'; // for claro_get_uid_of_platform_admin()
require_once $includePath . '/lib/fileManage.lib.php';
require_once $includePath . '/lib/form.lib.php';
require_once $includePath . '/lib/sendmail.lib.php';

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
                    : ( get_conf('defaultVisibilityForANewCourse') == 2 or get_conf('defaultVisibilityForANewCourse') == 3 ? true : false )  ;
$courseEnrollAllowed = isset($_REQUEST['courseEnrollAllowed'])
                    ? ($_REQUEST['courseEnrollAllowed'] == 'true' ? true : false)
                    : ( get_conf('defaultVisibilityForANewCourse') == 1 or get_conf('defaultVisibilityForANewCourse') == 2 ? true : false ) ;

$display   = DISP_COURSE_CREATION_FORM; // default display ...
$errorList = array();

if ( isset($_REQUEST['submitFromCoursProperties']) )
{
    // SUBMITTED DATA CHECKING

    if ( ! $courseTitle && get_conf('human_label_needed') )       $errorList[] = get_lang('Course title needed');
    if ( ! $courseOfficialCode && get_conf('human_code_needed') ) $errorList[] = get_lang('Course code needed');

    if ( ! $courseCategory || $courseCategory == 'choose_one')
    {
        $errorList[] = get_lang('Category needed (you must choose a category)');
    }

    if ( empty($courseEmail) && get_conf('course_email_needed') ) $errorList[] = get_lang('Email needed');

    if ( ! empty( $courseEmail )
        && ! is_well_formed_email_address($courseEmail) )       $errorList[] = get_lang('The email address is not valid');

    if (count ($errorList) > 0) $okToCreate  = FALSE;
    else                       $okToCreate = TRUE;

    // PREPARE COURSE INTERNAL SYSTEM SETTINGS

    if($courseCategory == 'root') $courseCategory = null;
    $courseOfficialCode = ereg_replace('[^A-Za-z0-9_]', '', $courseOfficialCode);
    $courseOfficialCode = strtoupper($courseOfficialCode);

    $keys = define_course_keys ($courseOfficialCode,'',get_conf('dbNamePrefix'));

    $courseSysCode      = $keys[ 'currentCourseId'         ];
    $courseDbName       = $keys[ 'currentCourseDbName'     ];
    $courseDirectory    = $keys[ 'currentCourseRepository' ];
    $courseExpirationDate = '';

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
                   ,               $courseExpirationDate
                   ,               $extLinkName
                   ,               $extLinkUrl)
                )
            {      // COURSE CREATION  SUCEEEDED

                $display = DISP_COURSE_CREATION_SUCCEED;

                // WARN PLATFORM ADMINISTRATOR OF THE COURSE CREATION

		$mailSubject = get_lang('[%site_name] Course creation %course_name',array('%site_name'=> $siteName ,
                                                                                          '%course_name'=> $courseTitle) );

		$mailBody = get_block('blockCourseCreationEmailMessage', array ( '%date' => $dateTimeFormatLong,
                                                                            '%sitename' => $siteName,
                                                                            '%user_firstname' => $_user['firstName'],
                                                                            '%user_lastname' => $_user['lastName'],
                                                                            '%user_email' => $_user['mail'],
                                                                            '%course_code' => $courseOfficialCode,
                                                                            '%course_title' => $courseTitle,
                                                                            '%course_lecturers' => $courseHolder,
                                                                            '%course_email' => $courseEmail,
                                                                            '%course_category' => $courseCategory,
                                                                            '%course_language' => $courseLanguage,
                                                                            '%course_url' => $coursesRepositoryWeb . $courseDirectory
                                                                          ) );

                // GET THE CONCERNED SENDERS OF THE EMAIL
                $platformAdminList = claro_get_uid_of_platform_admin();

                claro_mail_user( $platformAdminList, $mailBody, $mailSubject);


            $args['courseSysCode'  ] = $courseSysCode;
            $args['courseDbName'   ] = $courseDbName;
            $args['courseDirectory'] = $courseDirectory;
            $args['courseCategory' ] = $courseCategory;

            $eventNotifier->notifyEvent("course_created",$args);
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
    $interbredcrump[] = array ('url' => $rootAdminWeb, 'name' => get_lang('Administration'));
}

include $includePath . '/claro_init_header.inc.php';

echo claro_html_tool_title(get_lang('Create a course website'));

if ( count($errorList) > 0 ) echo claro_html_message_box(implode('<br />', $errorList));

/*----------------------------------------------------------------------------
                                    FORM DISPLAY
  ----------------------------------------------------------------------------*/

if( $display == DISP_COURSE_CREATION_FORM )
{
    $language_list        = claro_get_lang_flat_list();
    $courseCategory_array = claro_get_cat_flat_list();

    if(get_conf('rootCanHaveCourse', true))
    {
        $courseCategory_array = array_merge(array('root' => get_lang('Root')),$courseCategory_array);
    }
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
<label for="title"><?php echo ( get_conf('human_label_needed') ? '<span class="required">*</span>' :'') . get_lang('Course title') ?></label> :
</td>
<td valign="top">
<input type="Text" name="title" id="title" size="60" value="<?php echo htmlspecialchars($courseTitle) ?>" />
<br /><small><?php echo get_lang('e.g. <em>History of Literature</em>') ?></small>
</td>
</tr>

<tr valign="top">
<td align="right">
<label for="officialCode"><?php echo ( get_conf('human_code_needed') ? '<span class="required">*</span>' :'') . get_lang('Course code') ?></label> :
</td>
<td >
    <input type="Text" id="officialCode" name="officialCode" maxlength="12" value="<?php echo htmlspecialchars($courseOfficialCode) ?>" />
    <br />
    <small><?php echo get_lang('max. 12 characters, e.g. <em>ROM2121</em>') ?></small>
</td>
</tr>

<tr valign="top">
<td align="right">
<label for="holder"><?php echo get_lang('Lecturer(s)') ?></label> :
</td>
<td>
<input type="Text" name="holder" id="holder" size="60" value="<?php echo htmlspecialchars($courseHolder) ?>" />
</td>
</tr>

<tr>
<td align="right">
<label for="email"><?php echo ( get_conf('course_email_needed') ? '<span class="required">*</span>' : '') . get_lang('Email') ?></label>&nbsp;:
</td>
<td>
<input type="text" name="email" id="email" value="<?php echo htmlspecialchars($courseEmail); ?>" size="30" maxlength="255">
</td>
</tr>

<tr valign="top">
<td align="right">
<label for="category"><span class="required">*</span><?php echo get_lang('Category') ?></label> :
</td>
<td>
<?php echo claro_html_form_select( 'category'
                                 , $courseCategory_array
                                 , $cat_preselect
                                 , array('id'=>'category'))
                                 ; ?>
<br /><small><?php echo get_lang('This is the faculty, department or school where the course is delivered') ?></small>
</td>
</tr>

<tr valign="top">
<td align="right"><label for="extLinkName"><?php echo get_lang('Department') ?></label>&nbsp;: </td>
<td>
<input type="text" name="extLinkName" id="extLinkName" value="" size="20" maxlength="30" />
</td>
</tr>

<tr valign="top" >
<td align="right" nowrap><label for="extLinkUrl" ><?php echo get_lang('Department URL') ?></label>&nbsp;:</td>
<td>
<input type="text" name="extLinkUrl" id="extLinkUrl" value="" size="60" maxlength="180" />
</td>
</tr>

<tr valign="top">
<td align="right">
<label for="languageCourse"><span class="required">*</span><?php echo get_lang('Language') ?></label> :
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
<td align="right" nowrap><?php echo get_lang('Course access'); ?> : </td>
<td>
<input type="radio" id="courseVisibility_true" name="courseVisibility" value="true" <?php echo $courseVisibility ? 'checked':'' ?> />
<label for="courseVisibility_true"><?php echo get_lang('Public access from campus home page even without login'); ?></label><br />
<input type="radio" id="courseVisibility_false" name="courseVisibility" value="false" <?php echo ! $courseVisibility  ?'checked':''; ?> />
<label for="courseVisibility_false"><?php echo get_lang('Private access (site accessible only to people on the <a href="%url">User list</a>)',array('%url'=> '../user/user.php')); ?></label>
</td>
</tr>
<tr valign="top">
<td align="right"><?php echo get_lang('Enrolment'); ?> : </td>
<td>
<input type="radio" id="courseEnrollAllowed_true" name="courseEnrollAllowed" value="true" <?php echo $courseEnrollAllowed ?'checked':''; ?> />
<label for="allowedToSubscribe_true"><?php echo get_lang('Allowed'); ?></label>
<label for="courseEnrollmentKey">
- <?php echo get_lang('enrollment key') ?>
<small>(<?php echo strtolower(get_lang('Optional')); ?>)</small> :
</label>
<input type="text" id="enrollmentKey" name="enrollmentKey" value="<?php echo htmlspecialchars($courseEnrollmentKey); ?>" />
<br />
<input type="radio" id="courseEnrollAllowed_false"  name="courseEnrollAllowed" value="false" <?php echo ! $courseEnrollAllowed ?'checked':''; ?> /> <label for="courseEnrollAllowed_false"><?php echo get_lang('Denied'); ?></label>
<tr valign="top">
<td align="right">
<label for="submitFromCoursProperties"><?php echo get_lang('Create') ?> : </label>
</td>
<td>
<input type="Submit" name="submitFromCoursProperties" id ="submitFromCoursProperties" value="<?php echo get_lang('Ok')?>">
<?php echo claro_html_button($_SERVER['HTTP_REFERER'], get_lang('Cancel')); ?>
</td>
</tr>
<tr>
<td></td>
<td>
<?php echo get_lang('<span class=\"required\">*</span> denotes required field') ?>
</td>
</tr>
</table>
</form>

<?php
}

/*----------------------------------------------------------------------------
                            RESULT DISPLAY
  ----------------------------------------------------------------------------*/

if ($display == DISP_COURSE_CREATION_FAILED)
{
    if (count ($errorList) > 0 ) $errorString = implode ('<br />', $errorList);
    else                         $errorString = '';

    echo claro_html_message_box('<p>Course Creation failed.</p>'
                                . $errorString );
}



if( $display == DISP_COURSE_CREATION_SUCCEED)
{
    // Replace HTML special chars by equivalent - cannot use html_specialchars
    // Special for french

    $dialogBox = "\n"
    .            get_lang('You have just created the course website')
    .            ' : '
    .            '<strong>'
    .            $courseOfficialCode
    .            '</strong>'
    ;

    echo claro_html_message_box($dialogBox)
    .    '<br />'
    .    '<a class="claroCmd" href="../../index.php">'
    .    get_lang('Back to my course list')
    .    '</a>'
    ;
} // if all fields fullfilled

/*----------------------------------------------------------------------------
                    WAIT PANEL DISPLAY
  ----------------------------------------------------------------------------*/


if ( $display == DISP_COURSE_CREATION_PROGRESS )
{
    $msg = get_lang('Creating course (it may take a while) ...')
    .      ' <br />'
    .      '<p align="center">'
    .      '<img src="' . $imgRepositoryWeb . '/processing.gif" / alt="">'
    .      '</p>'
    .      '<p>'
    .      get_lang('If after while no message appears confirming the course creation, please click <a href="%url">here</a>',array('%url' => $paramString))
    .      '</p>'
    ;
    echo claro_html_message_box( $msg );
}


include $includePath . '/claro_init_footer.inc.php';
?>
