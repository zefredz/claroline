<?php // $Id$

if ( count( get_included_files() ) == 1 ) die( '---' );

/**
 * CLAROLINE
 *
 * Course Class
 *
 * @version 1.9 $Revision$
 *
 * @copyright 2001-2008 Universite catholique de Louvain (UCL)
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @package Kernel
 * @author Claro Team <cvs@claroline.net>
 * @author Mathieu Laurent <laurent@cerdecam.be>
 * @author Sebastien Piraux <piraux@cerdecam.be>
 */

require_once dirname(__FILE__) . '/backlog.class.php';
require_once dirname(__FILE__) . '/admin.lib.inc.php'; // for delete course function
require_once dirname(__FILE__) . '/../../messaging/lib/message/messagetosend.lib.php';
require_once dirname(__FILE__) . '/../../messaging/lib/recipient/userlistrecipient.lib.php';

class ClaroCourse
{
    // Identifier
    var $courseId;

    // Name
    var $title;

    // Official code
    var $officialCode;

    // Titular
    var $titular;

    // Email
    var $email;

    // Course category code
    var $category;

    // Depatment Name
    var $departmentName;

    // Department Url
    var $extLinkUrl;

    // Language of the course
    var $language;

    // Course access (true = public, false = private)
    var $access;

    // Course visibility (true = shown, false = hidden)
    var $visibility;

    // registration (true = open, false = close)
    var $registration;

    // registration key
    var $registrationKey;

    // Backlog object
    var $backlog;

    // List of GET or POST parameters
    var $htmlParamList = array();

    /**
     * Constructor
     */

    function ClaroCourse ($creatorFirstName = '', $creatorLastName = '', $creatorEmail = '')
    {
        $this->courseId = '';
        $this->title = '';
        $this->officialCode = '';
        $this->titular = $creatorFirstName . ' ' . $creatorLastName;
        $this->email = $creatorEmail;
        $this->category = '';
        $this->departmentName = '';
        $this->extLinkUrl = '';
        $this->language     = get_conf('platformLanguage');
        # FIXME FIXME FIXME
        $this->access       = get_conf('defaultAccessOnCourseCreation');
        $this->visibility   = get_conf('defaultVisibilityOnCourseCreation');
        $this->registration = get_conf('defaultRegistrationOnCourseCreation') ;
        $this->registrationKey = '';

        $this->backlog = new Backlog();
    }

    /**
     * load course data from database
     *
     * @param $courseId string course identifier
     * @return boolean success
     */

    function load ($courseId)
    {
        if ( ( $course_data = claro_get_course_data($courseId) ) !== false )
        {
            $this->courseId = $courseId;
            $this->title = $course_data['name'];
            $this->officialCode = $course_data['officialCode'];
            $this->titular = $course_data['titular'];
            $this->email = $course_data['email'];
            $this->category = $course_data['categoryCode'];
            $this->departmentName = $course_data['extLinkName'];
            $this->extLinkUrl = $course_data['extLinkUrl'];
            $this->language = $course_data['language'];
            $this->access = $course_data['access'];
            $this->visibility = $course_data['visibility'];
            $this->registration = $course_data['registrationAllowed'];
            $this->registrationKey = $course_data['registrationKey'];
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * insert or update course data
     *
     * @return boolean success
     */

    function save ()
    {
        if ( empty($this->courseId) )
        {
            // insert
            $keys = define_course_keys ($this->officialCode,'',get_conf('dbNamePrefix'));

            $courseSysCode      = $keys['currentCourseId'];
            $courseDbName       = $keys['currentCourseDbName'];
            $courseDirectory    = $keys['currentCourseRepository'];
            $courseExpirationDate = '';

            if (   prepare_course_repository($courseDirectory, $courseSysCode)
                && register_course($courseSysCode
                   ,               $this->officialCode
                   ,               $courseDirectory
                   ,               $courseDbName
                   ,               $this->titular
                   ,               $this->email
                   ,               $this->category
                   ,               $this->title
                   ,               $this->language
                   ,               $GLOBALS['_uid']
                   ,               $this->access
                   ,               $this->registration
                   ,               $this->registrationKey
                   ,               $this->visibility
                   ,               $courseExpirationDate
                   ,               $this->departmentName
                   ,               $this->extLinkUrl)
                && install_course_database( $courseDbName )
                && install_course_tools( $courseDbName, $this->language, $courseDirectory )
                )
            {
                // set course id
                $this->courseId = $courseSysCode;

                // notify event manager
                $args['courseSysCode'  ] = $courseSysCode;
                $args['courseDbName'   ] = $courseDbName;
                $args['courseDirectory'] = $courseDirectory;
                $args['courseCategory' ] = $this->category;

                $GLOBALS['eventNotifier']->notifyEvent("course_created",$args);

                return true;
            }
            else
            {
                $lastFailure = claro_failure::get_last_failure();
                $this->backlog->failure( 'Error : '. $lastFailure );
                return false;
            }

        }
        else
        {
            // update
            $tbl_mdb_names = claro_sql_get_main_tbl();
            $tbl_course = $tbl_mdb_names['course'];
            $tbl_cdb_names = claro_sql_get_course_tbl();
            $tbl_course_properties = $tbl_cdb_names['course_properties'];

            $sql = "UPDATE `" . $tbl_course . "`
                    SET `intitule`             = '" . claro_sql_escape($this->title) . "',
                        `faculte`              = '" . claro_sql_escape($this->category) . "',
                        `titulaires`           = '" . claro_sql_escape($this->titular) . "',
                        `administrativeNumber` = '" . claro_sql_escape($this->officialCode) . "',
                        `language`             = '" . claro_sql_escape($this->language) . "',
                        `extLinkName`          = '" . claro_sql_escape($this->departmentName) . "',
                        `extLinkUrl`           = '" . claro_sql_escape($this->extLinkUrl) . "',
                        `email`                = '" . claro_sql_escape($this->email) . "',
                        `visibility`           = '" . ($this->visibility ? 'VISIBLE':'INVISIBLE') . "',
                        `access`               = '" . claro_sql_escape( $this->access ) . "',
                        `registration`         = '" . ($this->registration ? 'OPEN':'CLOSE') . "',
                        `registrationKey`      = '" . claro_sql_escape($this->registrationKey) . "'
                    WHERE code='" . claro_sql_escape($this->courseId) . "'";

            return claro_sql_query($sql);
        }
    }

    /**
     * delete course data and content
     *
     * @return boolean success
     */

    function delete ()
    {
        return delete_course($this->courseId);
    }

    /**
     * retrieve course data from form
     */

    function handleForm ()
    {
        if ( isset($_REQUEST['course_title'        ]) ) $this->title = trim(strip_tags($_REQUEST['course_title']));

        if ( isset($_REQUEST['course_officialCode' ]) )
        {
            $this->officialCode = trim(strip_tags($_REQUEST['course_officialCode']));
            $this->officialCode = ereg_replace('[^A-Za-z0-9_]', '', $this->officialCode);
            $this->officialCode = strtoupper($this->officialCode);
        }

        if ( isset($_REQUEST['course_titular'      ]) ) $this->titular = trim(strip_tags($_REQUEST['course_titular']));
        if ( isset($_REQUEST['course_email'        ]) ) $this->email = trim(strip_tags($_REQUEST['course_email']));
        if ( isset($_REQUEST['course_category'     ]) ) $this->category = trim(strip_tags($_REQUEST['course_category']));
        if ( isset($_REQUEST['course_departmentName']) ) $this->departmentName = trim(strip_tags($_REQUEST['course_departmentName']));
        if ( isset($_REQUEST['course_extLinkUrl']) ) $this->extLinkUrl = trim(strip_tags($_REQUEST['course_extLinkUrl']));
        if ( isset($_REQUEST['course_language'     ]) ) $this->language = trim(strip_tags($_REQUEST['course_language']));
        if ( isset($_REQUEST['course_visibility'   ]) ) $this->visibility  = (bool) $_REQUEST['course_visibility'];
        if ( isset($_REQUEST['course_access'       ]) ) $this->access = $_REQUEST['course_access'];
        if ( isset($_REQUEST['course_registration' ]) ) $this->registration = (bool) $_REQUEST['course_registration'];
        if ( isset($_REQUEST['course_registrationKey' ]) ) $this->registrationKey = trim(strip_tags($_REQUEST['course_registrationKey']));
    }

    /**
     * validate data from object.  Error handling with a backlog object.
     *
     * @return boolean success
     */

    function validate ()
    {
        $success = true ;

        /**
         * Configuration array , define here which field can be left empty or not
         */

        $fieldRequiredStateList['title'         ] = get_conf('human_label_needed');
        $fieldRequiredStateList['officialCode'  ] = get_conf('human_code_needed');
        $fieldRequiredStateList['titular'       ] = false;
        $fieldRequiredStateList['email'         ] = get_conf('course_email_needed');
        $fieldRequiredStateList['category'      ] = true;
        $fieldRequiredStateList['language'      ] = true;
        $fieldRequiredStateList['departmentName'] = get_conf('extLinkNameNeeded');
        $fieldRequiredStateList['extLinkUrl' ] = get_conf('extLinkUrlNeeded');
        
        // Validate course access
        if ( empty($this->access) || ! in_array($this->access, array('public','private','platform')) )
        {
            $this->backlog->failure(get_lang('Course title needed'));
            $success = false ;
        }

        // Validate course title
        if ( empty($this->title) && $fieldRequiredStateList['title'] )
        {
            $this->backlog->failure(get_lang('Course title needed'));
            $success = false ;
        }

        // Validate course code
        if ( empty($this->officialCode) && $fieldRequiredStateList['officialCode'])
        {
            $this->backlog->failure(get_lang('Course code needed'));
            $success = false ;
        }
        
        // Check course length
        if( strlen($this->officialCode) > 12 )
        {
            $this->backlog->failure(get_lang('Course code too long'));
            $success = false;
        }

        // Validate email
        if ( empty($this->email) && $fieldRequiredStateList['email'])
        {
            $this->backlog->failure(get_lang('Email needed'));
            $success = false ;
        }
        else
        {
            if ( ! $this->validateEmailList() )
            {
                $this->backlog->failure(get_lang('The email address is not valid'));
                $success = false;
            }
        }

        // Validate course category
        if ( is_null($this->category) && $fieldRequiredStateList['category'] || $this->category == 'choose_one' )
        {
            $this->backlog->failure(get_lang('Category needed'));
            $success = false ;
        }

        // Validate course language
        if ( empty($this->language) && $fieldRequiredStateList['language'])
        {
            $this->backlog->failure(get_lang('Language needed'));
            $success = false ;
        }

        // Validate course departmentName
        if ( empty($this->departmentName) && $fieldRequiredStateList['departmentName'])
        {
            $this->backlog->failure(get_lang('Department needed'));
            $success = false ;
        }

        // Validate course extLinkUrl
        if ( empty($this->extLinkUrl) && $fieldRequiredStateList['extLinkUrl'])
        {
            $this->backlog->failure(get_lang('Department url needed'));
            $success = false ;
        }

        // Validate department url
        if ( ! $this->validateExtLinkUrl() )
        {
            $this->backlog->failure(get_lang('Department URL is not valid'));
            $success = false ;
        }

        return $success;
    }

    /**
     * validate url and try to repair it if no protocol specified
     *
     * @return boolean success
     */

    function validateExtLinkUrl ()
    {
        if ( empty($this->extLinkUrl) ) return true;

        $regexp = "^(http|https|ftp)\://[a-zA-Z0-9\.-]+\.[a-zA-Z0-9]{1,3}(:[a-zA-Z0-9]*)?/?([a-zA-Z0-9\._\?\,\'/\\\+&%\$#\=~-])*$";

        if ( ! eregi($regexp,$this->extLinkUrl) )
        {
            // Problem with url. try to repair
            // if  it  only the protocol missing add http
            if ( eregi('^[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(:[a-zA-Z0-9]*)?/?([a-zA-Z0-9\-\._\?\,\'/\\\+&%\$#\=~])*$', $this->extLinkUrl)
                && ( eregi($regexp, 'http://' . $this->extLinkUrl)))
            {
                $this->extLinkUrl = 'http://' . $this->extLinkUrl;
            }
            else
            {
                 return false;
            }
        }

        return true;
    }

    /**
     * validate email ( and semi-column separated email list )
     *
     * @return boolean success
     */

    function validateEmailList ()
    {
        // empty email is valide as we already checked if field was required
        if( empty($this->email) ) return true;

        $emailControlList = strtr($this->email,', ',';');
        $emailControlList = preg_replace( '/;+/', ';', $emailControlList );

        $emailControlList = explode(';',$emailControlList);

        $emailValidList = array();

           foreach ( $emailControlList as $emailControl )
        {
            $emailControl = trim($emailControl);

               if ( ! is_well_formed_email_address( $emailControl ) )
               {
                return false;
               }
            else
               {
                   $emailValidList[] = $emailControl;
            }
        }

           $this->email = implode(';',$emailValidList);
           return true;
    }

    /**
     * Display form
     *
     * @param $cancelUrl string url of the cancel button
     * @return string html output of form
     */

    function displayForm ($cancelUrl=null)
    {

        $languageList = claro_get_lang_flat_list();
        $categoryList = claro_get_cat_flat_list();

        if ( ! in_array($this->category,$categoryList) )
        {
            $this->category = 'choose_one';
            $categoryList = array_merge( array(get_lang('Choose one')=>'choose_one'), $categoryList);
        }

        // TODO cancelUrl cannot be null
        if ( is_null($cancelUrl) )
            $cancelUrl = get_path('clarolineRepositoryWeb') . 'course/index.php?cid=' . htmlspecialchars($this->courseId);

        $html = '';

        $html .= '<form method="post" action="' . $_SERVER['PHP_SELF'] . '">' . "\n"
        .    claro_form_relay_context()
            . '<input type="hidden" name="cmd" value="'.(empty($this->courseId)?'rqProgress':'exEdit').'" />' . "\n"
            . '<input type="hidden" name="claroFormId" value="' . uniqid('') . '" />' . "\n"

            . $this->getHtmlParamList('POST')

            . '<table cellpadding="3" border="0">' . "\n" ;

        // Course title

        $html .= '<tr valign="top">' . "\n"
            . '<td align="right">'
            . '<label for="course_title">'
            . (get_conf('human_label_needed') ? '<span class="required">*</span> ':'') . get_lang('Course title')
            .'</label>&nbsp;:</td>'
            . '<td>'
            . '<input type="text" name="course_title" id="course_title" value="' . htmlspecialchars($this->title) . '" size="60" />'
            . (empty($this->courseId) ? '<br /><small>'.get_lang('e.g. <em>History of Literature</em>').'</small>':'')
            . '</td></tr>' . "\n" ;

        // Course code

        $html .= '<tr valign="top">' . "\n"
            . '<td align="right">'
            . '<label for="course_officialCode">'
            . (get_conf('human_code_needed') ? '<span class="required">*</span> ' :'') . get_lang('Course code')
            . '</label>&nbsp;:</td>'
            . '<td><input type="text" id="course_officialCode" name="course_officialCode" value="' . htmlspecialchars($this->officialCode) . '" size="20" maxlength="12" />'
            . (empty($this->courseId) ? '<br /><small>'.get_lang('max. 12 characters, e.g. <em>ROM2121</em>').'</small>':'')
            . '</td></tr>' . "\n" ;

        // Course titular

        $html .= '<tr>' . "\n"
            . '<td align="right">'
            . '<label for="course_titular">' . get_lang('Lecturer(s)') . '</label>&nbsp;:</td>'
            . '<td><input type="text"  id="course_titular" name="course_titular" value="' . htmlspecialchars($this->titular) . '" size="60" /></td>'
            . '</tr>' . "\n" ;

        // Course email

        $html .= '<tr>' . "\n"
            . '<td align="right">'
            . '<label for="course_email">'
            . (get_conf('course_email_needed')?'<span class="required">*</span> ':'') . get_lang('Email')
            . '</label>'
            . '&nbsp;:'
            . '</td>'
            . '<td>'
            . '<input type="text" id="course_email" name="course_email" value="' . htmlspecialchars($this->email) . '" size="60" maxlength="255" />'
            . '</td>'
            . '</tr>' . "\n";

        // Course category select box

        $html .= '<tr valign="top">' . "\n"
            . '<td align="right">'
            . '<label for="course_category">'
            . '<span class="required">*</span> ' . get_lang('Category') . '</label>'
            . ' :'
            . '</td>'
            . '<td>'
            . claro_html_form_select( 'course_category', $categoryList, $this->category, array('id'=>'course_category') )
            . (empty($this->courseId) ? '<br />'
            . '<small>'.get_lang('This is the faculty, department or school where the course is delivered').'</small>':'')
            . '</td>'
            . '</tr>' . "\n" ;

        // Course department name

        $html .= '<tr valign="top">' . "\n"
            . '<td align="right">'
            . '<label for="course_departmentName">'
            . (get_conf('extLinkNameNeeded')?'<span class="required">*</span> ':'')
            . get_lang('Department') . '</label>&nbsp;: </td>'
            . '<td>'
            . '<input type="text" name="course_departmentName" id="course_departmentName" value="' . htmlspecialchars($this->departmentName) . '" size="20" maxlength="30" />'
            . '</td>'
            . '</tr>' . "\n" ;

        // Course department url

        $html .= '<tr valign="top" >' . "\n"
            . '<td align="right" nowrap="nowrap">'
            . (get_conf('extLinkUrlNeeded')?'<span class="required">*</span> ':'')
            . '<label for="course_extLinkUrl" >' . get_lang('Department URL') . '</label>'
            . '&nbsp;:'
            . '</td>'
            . '<td>'
            . '<input type="text" name="course_extLinkUrl" id="course_extLinkUrl" value="' . htmlspecialchars($this->extLinkUrl) . '" size="60" maxlength="180" />'
            . '</td>'
            . '</tr>' . "\n" ;

        // Course language select box

        $html .= '<tr valign="top" >' . "\n"
            . '<td align="right">'
            . '<label for="course_language">'
            . '<span class="required">*</span> ' . get_lang('Language') . '</label>'
            . '&nbsp;:'
            . '</td>'
            . '<td>'
            . claro_html_form_select('course_language', $languageList, $this->language, array('id'=>'course_language'))
            . '</td>'
            . '</tr>' . "\n" ;

        // Course access

        $html .= '<tr valign="top" >' . "\n"
            . '<td align="right" nowrap="nowrap">' . get_lang('Course access') . '&nbsp;:</td>'
            . '<td>'
            . '<img src="' . get_icon_url('access_open') . '" alt="' . get_lang('open') . '" />'
            . '<input type="radio" id="access_public" name="course_access" value="public" ' . ($this->access == 'public' ? 'checked="checked"':'') . ' />'
            . '&nbsp;'
            . '<label for="access_public">' . get_lang('Access allowed to anybody (even without login)') . '</label>'
            . '<br />' . "\n"
            . '<img src="' . get_icon_url('access_platform') . '" alt="' . get_lang('open') . '" />'
            . '<input type="radio" id="access_reserved" name="course_access" value="platform" ' . ($this->access == 'platform' ? 'checked="checked"':'') . ' />'
            . '&nbsp;'
            . '<label for="access_reserved">' . get_lang('Access allowed only to platform members (user registered to the platform)') . '</label>'
            . '<br />' . "\n"
            . '<img src="' . get_icon_url('access_locked') . '"  alt="' . get_lang('locked') . '" />'
            . '<input type="radio" id="access_private" name="course_access" value="private" ' . ($this->access == 'private' ? 'checked="checked"':'' ) . ' />'
            . '&nbsp;'
            . '<label for="access_private">';

        if( empty($this->courseId) )
            $html .= get_lang('Access allowed only to course members (people on the course user list)');
        else
            $html .= get_lang('Access allowed only to course members (people on the <a href="%url">course user list</a>)' , array('%url'=> '../user/user.php'));

        $html .= '</label>'
            . '</td>'
            . '</tr>' . "\n" ;

        // Course registration + registration key

        $html .= '<tr valign="top">' . "\n"
            . '<td align="right">' . get_lang('Enrolment') . '&nbsp;:</td>'
            . '<td>'
            . '<img src="' . get_icon_url('enroll_allowed') . '"  alt="" />'
            . '<input type="radio" id="registration_true" name="course_registration" value="1" ' . ($this->registration && empty($this->registrationKey) ?'checked="checked"':'') . ' />'
            . '&nbsp;'
            . '<label for="registration_true">' . get_lang('Allowed') . '</label>'
            . '<br />' . "\n"
            . '<img src="' . get_icon_url('enroll_key') . '"  alt="" />'
            . '<input type="radio" id="registration_key" name="course_registration" value="1" ' . ($this->registration && !empty($this->registrationKey) ?'checked="checked"':'') . ' />'
            . '&nbsp;'
            . '<label for="registration_key">' . get_lang('Allowed with enrolment key') . '</label>'
            . '&nbsp;'
            //. '<label for="registrationKey">'
            //. ' - ' . get_lang('Enrolment key') . ' : ' /* . ' <small>(' . get_lang('Optional') . ')</small> : ' */
            // . '</label>'*/
            . '<input type="text" id="registrationKey" name="course_registrationKey" value="' . htmlspecialchars($this->registrationKey) . '" />'
            . '<br />' . "\n"
            . '<img src="' . get_icon_url('enroll_forbidden') . '"  alt="" />'
            . '<input type="radio" id="registration_false"  name="course_registration" value="0" ' . ( ! $this->registration ?'checked="checked"':'') . ' />'
            . '&nbsp;'
            . '<label for="registration_false">' . get_lang('Denied') . '</label>'
            . '</td>'
            . '</tr>' . "\n" ;

        // Block course settings tip

        $html .= '<tr>' . "\n"
            . '<td>&nbsp;</td>'
            . '<td><small><font color="gray">' . get_block('blockCourseSettingsTip') . '</font></small></td>'
            . '</tr>' . "\n" ;


        // Course visibility
        if (claro_is_platform_admin())
        $html .= '<tr valign="top">' . "\n"
        .        '<td align="right">' . get_lang('Course visibility') . '&nbsp;:</td>'
            . '<td>'
            . '<img src="' . get_icon_url('visible') . '" alt="" />'
            . '<input type="radio" id="visibility_show" name="course_visibility" value="1" ' . ($this->visibility ? 'checked="checked"':'') . ' />&nbsp;'
            . '<label for="visibility_show">' . get_lang('The course is shown in the courses listing') . '</label>'
            . '<br />' . "\n"
            . '<img src="' . get_icon_url('invisible') . '" alt="" />'
            . '<input type="radio" id="visibility_hidden" name="course_visibility" value="0" ' . ( ! $this->visibility ? 'checked="checked"':'' ) . ' />&nbsp;'
            . '<label for="visibility_hidden">'
            . get_lang('Visible only to people on the user list')
            . '</label>'
            . '</td>'
            . '</tr>' . "\n"
            ;        // Required legend

        $html .= '<tr>' . "\n"
            . '<td>&nbsp;</td>'
            . '<td>' . get_lang('<span class="required">*</span> denotes required field') . '</td>'
            . '</tr>' . "\n" ;

        $html .= '<tr>' . "\n"
            . '<td>&nbsp;</td>'
            . '<td>'
            . '<input type="submit" name="changeProperties" value="' . get_lang('Ok') . '" />'
            . '&nbsp;'
            . claro_html_button($cancelUrl, get_lang('Cancel'))
            . '</td>' . "\n"
            . '</tr>' . "\n" ;

        $html .= '</table>' . "\n"
            .'</form>' . "\n" ;

        return $html;

    }

    /**
     * Display question of delete confirmation
     *
     * @param $cancelUrl string url of the cancel button
     * @return string html output of form
     */

    function displayDeleteConfirmation ()
    {
        $paramString = $this->getHtmlParamList('GET');

        $deleteUrl = './settings.php?cmd=exDelete&amp;'.$paramString;
        $cancelUrl = './settings.php?'.$paramString ;

        $html = '';

        $html .= '<p>'
        .    '<font color="#CC0000">'
        .    get_lang('Deleting this course will permanently delete all its documents and unenroll all its students.')
        .    get_lang('Are you sure to delete the course "%course_name" ( %course_code ) ?', array('%course_name' => $this->title,
                                                                                                         '%course_code' => $this->officialCode ))
        .    '</font>'
        .    '</p>'
        .    '<p>'
        .    '<font color="#CC0000">'
        .    '<a href="'.$deleteUrl.'">'.get_lang('Yes').'</a>'
        .    '&nbsp;|&nbsp;'
        .    '<a href="'.$cancelUrl.'">'.get_lang('No').'</a>'
        .    '</font>'
        .    '</p>'
        ;

        return $html;
    }

    /**
     * Add html parameter to list
     *
     * @param $name string input name
     * @param $value string input value
     *
     *
     */

    function addHtmlParam($name, $value)
    {
        $this->htmlParamList[$name] = $value;
    }

    /**
     * Get html representing parameter list depending on method (POST for form, GET for URL's')
     *
     * @param $method string GET OR POST
     * @return string html output of params for $method method
     */

    function getHtmlParamList($method = 'GET')
    {
        if ( empty($this->htmlParamList) ) return '';

        $html = '';

        if ( $method == 'POST' )
        {
            foreach ( $this->htmlParamList as $name => $value )
            {
                $html .= '<input type="hidden" name="' . htmlspecialchars($name) . '" value="' . htmlspecialchars($value) . '" />' . "\n" ;
            }
        }
        else // GET
        {
            $params = array();
            foreach ( $this->htmlParamList as $name => $value )
            {
                $params[] = rawurlencode($name) . '=' . rawurlencode($value);
            }

            $html = implode('&amp;', $params );
        }

        return $html;
    }

    /**
     * Get visibility
     *
     * @param $access string
     * @param $registration string
     * @return integer value of visibility field
     *
     * @deprecated 1.9
     */

    function getVisibility ( $access, $registration )
    {
        $visibility = 0 ;

        if     ( ! $access && ! $registration ) $visibility = 0;
        elseif ( ! $access &&   $registration ) $visibility = 1;
        elseif (   $access && ! $registration ) $visibility = 3;
        elseif (   $access &&   $registration ) $visibility = 2;

        return $visibility ;
    }

    /**
     * Get access value from visibility field
     *
     * @param $visbility integer value of field
     * @return boolean public true, private false
     */

    function getAccess ( $visibility )
    {
        if ( $visibility >= 2 ) return true ;
        else                    return false ;
    }

    /**
     * Get registration value from visibility field
     *
     * @param $visbility integer value of field
     * @return boolean open true, close false
     */

    function getRegistration ( $visibility )
    {
        if ( $visibility == 1 || $visibility == 2 ) return true ;
        else                                        return false;
    }

    /**
     * Send course creation information by mail to all platform administrators
     *
     * @param string creator firstName
     * @param string creator lastname
     * @param string creator email
     */

    function mailAdministratorOnCourseCreation ($creatorFirstName, $creatorLastName, $creatorEmail)
    {
        $subject = get_lang('Course created : %course_name',array('%course_name'=> $this->title));

        $body = get_block('blockCourseCreationEmailMessage', array( '%date' => claro_html_localised_date(get_locale('dateTimeFormatLong')),
                                '%sitename' => get_conf('siteName'),
                                '%user_firstname' => $creatorFirstName,
                                '%user_lastname' => $creatorLastName,
                                '%user_email' => $creatorEmail,
                                '%course_code' => $this->officialCode,
                                '%course_title' => $this->title,
                                '%course_lecturers' => $this->titular,
                                '%course_email' => $this->email,
                                '%course_category' => $this->category,
                                '%course_language' => $this->language,
                                '%course_url' => get_path('rootWeb') . 'claroline/course/index.php?cid=' . htmlspecialchars($this->courseId)) );

        // Get the concerned senders of the email

        $mailToUidList = claro_get_uid_of_system_notification_recipient();
        if(empty($mailToUidList)) $mailToUidList = claro_get_uid_of_platform_admin();

        $message = new MessageToSend(claro_get_current_user_id(),$subject,$body);
        
        $recipient = new UserListRecipient();
        $recipient->addUserIdList($mailToUidList);
        
        //$message->sendTo($recipient);
        $recipient->sendMessage($message);
        
    }

    /**
     * Build progress param url
     *
     * @return string url
     */

    function buildProgressUrl ()
    {
        $url = $_SERVER['PHP_SELF'] . '?cmd=exEdit';

        $paramList = array();

        $paramList['course_title'] = $this->title;
        $paramList['course_officialCode'] = $this->officialCode;
        $paramList['course_titular'] = $this->titular;
        $paramList['course_email'] = $this->email;
        $paramList['course_category'] = $this->category;
        $paramList['course_departmentName'] = $this->departmentName;
        $paramList['course_extLinkUrl'] = $this->extLinkUrl;
        $paramList['course_language'] = $this->language;
        $paramList['course_visibility'] = $this->visibility;
        $paramList['course_access'] = $this->access;
        $paramList['course_registration'] = $this->registration;
        $paramList['course_registrationKey'] = $this->registrationKey;

        $paramList = array_merge($paramList, $this->htmlParamList);

        foreach ($paramList as $key => $value)
        {
            $url .= '&amp;' . rawurlencode($key) . '=' . rawurlencode($value);
        }

        return $url;
    }
}
