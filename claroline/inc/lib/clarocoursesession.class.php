<?php

if ( count( get_included_files() ) == 1 ) die( '---' );

/**
 * CLAROLINE
 *
 * CourseSession Class
 *
 * @version 1.10
 *
 * @copyright 2001-2010 Universite catholique de Louvain (UCL)
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @package Kernel
 * @author Claro Team <cvs@claroline.net>
 * @author Antonin Bourguignon <antonin.bourguignon@claroline.net>
 * @since 1.10
 */

require_once dirname(__FILE__) . '/backlog.class.php';
require_once dirname(__FILE__) . '/coursesession.lib.inc.php';
require_once dirname(__FILE__) . '/claroCourse.class.php';
require_once dirname(__FILE__) . '/clarocategory.class.php';
require_once dirname(__FILE__) . '/../../messaging/lib/message/messagetosend.lib.php';
require_once dirname(__FILE__) . '/../../messaging/lib/recipient/userlistrecipient.lib.php';

$jsLoader = JavascriptLoader::getInstance();
$jsLoader->load( 'claroline.ui');

class ClaroCourseSession extends ClaroCourse
{
    // Source course of this session course
    public $sourceCourseId;

    /**
     * Constructor.
     */
    function ClaroCourseSession ($creatorFirstName = '', $creatorLastName = '', $creatorEmail = '')
    {
        $this->id                   = null;
        $this->courseId             = '';
        $this->title                = '';
        $this->officialCode         = '';
        $this->sourceCourseId       = null;
        $this->titular              = $creatorFirstName . ' ' . $creatorLastName;
        $this->email                = $creatorEmail;
        $this->categories           = array();
        $this->departmentName       = '';
        $this->extLinkUrl           = '';
        $this->language             = get_conf('platformLanguage');
        # FIXME FIXME FIXME
        $this->access               = get_conf('defaultAccessOnCourseCreation');
        $this->visibility           = get_conf('defaultVisibilityOnCourseCreation');
        $this->registration         = get_conf('defaultRegistrationOnCourseCreation') ;
        $this->registrationKey      = '';
        $this->publicationDate      =  time();
        $this->expirationDate       = 0;
        $this->useExpirationDate    = false;
        $this->status               = 'enable';
        
        $this->backlog = new Backlog();
    }
    
    
    /**
     * Load course data from database.
     *
     * @param string        course identifier
     * @return boolean      success
     */
    function load ($courseId)
    {
        if ( ( $course_data = claro_get_session_course_data($courseId) ) !== false )
        {
            // Generate the array of categories
            $categoriesList = array();
            foreach ($course_data['categories'] as $cat)
            {
                $tempCat = new claroCategory();
                $tempCat->load($cat['categoryId']);
                $categoriesList[] = $tempCat;
            }
            
            // Assign
            $this->courseId           = $courseId;
            $this->id                 = $course_data['id'];
            $this->title              = $course_data['name'];
            $this->officialCode       = $course_data['officialCode'];
            $this->sourceCourseId     = $course_data['sourceCourseId'];
            $this->titular            = $course_data['titular'];
            $this->email              = $course_data['email'];
            $this->categories         = $categoriesList;
            $this->departmentName     = $course_data['extLinkName'];
            $this->extLinkUrl         = $course_data['extLinkUrl'];
            $this->language           = $course_data['language'];
            $this->access             = $course_data['access'];
            $this->visibility         = $course_data['visibility'];
            $this->registration       = $course_data['registrationAllowed'];
            $this->registrationKey    = $course_data['registrationKey'];
            $this->publicationDate    = $course_data['publicationDate'];
            $this->expirationDate     = $course_data['expirationDate'];
            $this->status             = $course_data['status'];
            
            pushClaroMessage($course_data['publicationDate']);
            
            $this->useExpirationDate = isset($this->expirationDate);
            
            return true;
        }
        else
        {
            return false;
        }
    }
    
    
    /**
     * Insert or update course data.
     *
     * @return boolean      success
     */
    function save ()
    {
        if ( empty($this->courseId) )
        {
            // Insert
            $keys = define_course_keys ($this->officialCode,'',get_conf('dbNamePrefix'));
            
            $courseSysCode      = $keys['currentCourseId'];
            $courseDbName       = $keys['currentCourseDbName'];
            $courseDirectory    = $keys['currentCourseRepository'];
            if ( !$this->useExpirationDate ) $this->expirationDate = 'NULL';
            
            if (   prepare_course_repository($courseDirectory, $courseSysCode)
                && register_session_course(
                                   $courseSysCode,
                                   $this->officialCode,
                                   $this->sourceCourseId,
                                   $courseDirectory,
                                   $courseDbName,
                                   $this->titular,
                                   $this->email,
                                   $this->categories,
                                   $this->title,
                                   $this->language,
                                   $GLOBALS['_uid'],
                                   $this->access,
                                   $this->registration,
                                   $this->registrationKey,
                                   $this->visibility,
                                   $this->departmentName,
                                   $this->extLinkUrl,
                                   $this->publicationDate,
                                   $this->expirationDate,
                                   $this->status )
                && install_course_database( $courseDbName )
                && install_course_tools( $courseDbName, $this->language, $courseDirectory )
                )
            {
                // Set course id
                $this->courseId = $courseSysCode;
                
                // Notify event manager
                $args['courseSysCode'  ] = $courseSysCode;
                $args['courseDbName'   ] = $courseDbName;
                $args['courseDirectory'] = $courseDirectory;
                $args['courseCategory' ] = $this->categories;
                
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
            // Update
            $tbl_mdb_names = claro_sql_get_main_tbl();
            $tbl_course = $tbl_mdb_names['course'];
            $tbl_cdb_names = claro_sql_get_course_tbl();
            $tbl_course_properties = $tbl_cdb_names['course_properties'];
            
            if ( !$this->useExpirationDate ) $this->expirationDate = null;
            
            $sqlExpirationDate = is_null($this->expirationDate) 
                ? 'NULL' 
                : 'FROM_UNIXTIME(' . claro_sql_escape($this->expirationDate) . ')'
                ;
            
            $sqlCreationDate = is_null($this->publicationDate) 
                ? 'NULL' 
                : 'FROM_UNIXTIME(' . claro_sql_escape($this->publicationDate) . ')'
                ;
            
            $sql = "UPDATE `" . $tbl_course . "` 
                    SET `intitule`             = '" . claro_sql_escape($this->title) . "',
                        `titulaires`           = '" . claro_sql_escape($this->titular) . "',
                        `administrativeNumber` = '" . claro_sql_escape($this->officialCode) . "',
                        `language`             = '" . claro_sql_escape($this->language) . "',
                        `extLinkName`          = '" . claro_sql_escape($this->departmentName) . "',
                        `extLinkUrl`           = '" . claro_sql_escape($this->extLinkUrl) . "',
                        `email`                = '" . claro_sql_escape($this->email) . "',
                        `visibility`           = '" . ($this->visibility ? 'visible':'invisible') . "',
                        `access`               = '" . claro_sql_escape( $this->access ) . "',
                        `registration`         = '" . ($this->registration ? 'open':'close') . "',
                        `registrationKey`      = '" . claro_sql_escape($this->registrationKey) . "',
                        `lastEdit`             = NOW(),
                        `creationDate`         = " . $sqlCreationDate . ", 
                        `expirationDate`       = " . $sqlExpirationDate . ", 
                        `status`               = '" . claro_sql_escape($this->status)   . "' 
                    WHERE code='" . claro_sql_escape($this->courseId) . "'";
            
            return claro_sql_query($sql);
        }
    }
    
    
    /**
     * Delete course data and content.
     *
     * @return boolean      success
     */
    function delete ()
    {
        return delete_session_course($this->courseId, $this->sourceCourseId);
    }
    
    
    /**
     * Retrieve course data from form.
     */
    function handleForm ()
    {
        if ( isset($_REQUEST['course_title'          ]) ) $this->title = trim(strip_tags($_REQUEST['course_title']));
        
        if ( isset($_REQUEST['course_officialCode'   ]) )
        {
            $this->officialCode = trim(strip_tags($_REQUEST['course_officialCode']));
            $this->officialCode = preg_replace('/[^A-Za-z0-9_]/', '', $this->officialCode);
            $this->officialCode = strtoupper($this->officialCode);
        }
        if ( isset($_REQUEST['course_sourceCourseId' ]) ) $this->sourceCourseId = trim(strip_tags($_REQUEST['course_sourceCourseId']));
        if ( isset($_REQUEST['course_titular'        ]) ) $this->titular = trim(strip_tags($_REQUEST['course_titular']));
        if ( isset($_REQUEST['course_email'          ]) ) $this->email = trim(strip_tags($_REQUEST['course_email']));
        if ( isset($_REQUEST['course_departmentName' ]) ) $this->departmentName = trim(strip_tags($_REQUEST['course_departmentName']));
        if ( isset($_REQUEST['course_extLinkUrl'     ]) ) $this->extLinkUrl = trim(strip_tags($_REQUEST['course_extLinkUrl']));
        if ( isset($_REQUEST['course_language'       ]) ) $this->language = trim(strip_tags($_REQUEST['course_language']));
        if ( isset($_REQUEST['course_visibility'     ]) ) $this->visibility  = (bool) $_REQUEST['course_visibility'];
        if ( isset($_REQUEST['course_access'         ]) ) $this->access = $_REQUEST['course_access'];
        if ( isset($_REQUEST['course_registration'   ]) ) $this->registration = (bool) $_REQUEST['course_registration'];
        if ( isset($_REQUEST['course_registrationKey']) ) $this->registrationKey = trim(strip_tags($_REQUEST['course_registrationKey']));
        
        if ( isset($_REQUEST['course_status_selection']))
        {
            if ($_REQUEST['course_status_selection'] == 'disable')
            {
                $this->status = isset($_REQUEST['course_status'])
                    ? trim($_REQUEST['course_status'])
                    : null
                    ;
            }
            elseif ($_REQUEST['course_status_selection'] == 'date' )
            {
                $this->status = 'date';
                    
                if ( isset($_REQUEST['course_publicationDate' ]) )
                {
                    $this->publicationDate = trim(strip_tags($_REQUEST['course_publicationDate']));
                }
                elseif (isset($_REQUEST['course_publicationYear'])
                    && isset($_REQUEST['course_publicationMonth'])
                    && isset($_REQUEST['course_publicationDay']))
                {
                    $this->publicationDate = mktime(
                        0,0,0,
                        $_REQUEST['course_publicationMonth'],
                        $_REQUEST['course_publicationDay'],
                        $_REQUEST['course_publicationYear'] );
                }
                else
                {
                    $this->publicationDate = mktime(23,59,59);
                }
                
                $this->useExpirationDate = (bool) (isset($_REQUEST['useExpirationDate']) && $_REQUEST['useExpirationDate']);
                
                if ( $this->useExpirationDate )
                {                
                    if ( isset($_REQUEST['course_expirationDate']) )
                    {
                        $this->expirationDate = trim(strip_tags($_REQUEST['course_expirationDate']));
                    }
                    elseif ( isset($_REQUEST['course_expirationYear'])
                        && isset($_REQUEST['course_expirationMonth'])
                        && isset($_REQUEST['course_expirationDay']) )
                    {
                        $this->expirationDate = mktime(
                            23,59,59,
                            $_REQUEST['course_expirationMonth'],
                            $_REQUEST['course_expirationDay'],
                            $_REQUEST['course_expirationYear'] );
                    }
                    else
                    {
                        $this->expirationDate = mktime(0,0,0);
                    }
                }
            }
            else
            {
                $this->status = 'enable';
            }
        }
    }
    
    
    /**
     * Validate data from object.  Error handling with a backlog object.
     *
     * @return boolean      success
     */
    function validate ()
    {
        $success = true ;
        
        // Configuration array , define here which field can be left empty or not
        $fieldRequiredStateList['title'          ] = get_conf('human_label_needed');
        $fieldRequiredStateList['officialCode'   ] = get_conf('human_code_needed');
        $fieldRequiredStateList['sourceCourseId' ] = true;
        $fieldRequiredStateList['titular'        ] = false;
        $fieldRequiredStateList['email'          ] = get_conf('course_email_needed');
        $fieldRequiredStateList['categories'     ] = false;
        $fieldRequiredStateList['language'       ] = true;
        $fieldRequiredStateList['departmentName' ] = get_conf('extLinkNameNeeded');
        $fieldRequiredStateList['extLinkUrl'     ] = get_conf('extLinkUrlNeeded');
        $fieldRequiredStateList['publicationDate'] = $this->status == 'date';
        $fieldRequiredStateList['expirationDate' ] = $this->status == 'date' && $this->useExpirationDate;
        
        // Validate course access
        if ( empty($this->access) || ! in_array($this->access, array('public','private','platform')) )
        {
            $this->backlog->failure(get_lang('Missing or invalid course access'));
            $success = false ;
        }
        
        // Validate course title
        if ( empty($this->title) && $fieldRequiredStateList['title'] )
        {
            $this->backlog->failure(get_lang('Course title needed'));
            $success = false ;
        }
        
        // Validate course code
        if ( empty($this->officialCode) && $fieldRequiredStateList['officialCode'] )
        {
            $this->backlog->failure(get_lang('Course code needed'));
            $success = false ;
        }
        
        // Validate source course id
        if ( empty($this->sourceCourseId) && $fieldRequiredStateList['sourceCourseId'] )
        {
            $this->backlog->failure(get_lang('Source course id needed'));
            $success = false ;
        }
        
        
        // Course designated as source cannot be a session course
        if ( ClaroCourse::isSessionCourse($this->sourceCourseId) )
        {
            $this->backlog->failure(get_lang('Session course cannot be used as a source course'));
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
        
        // Validate course publication date
        if ( empty($this->publicationDate) && $fieldRequiredStateList['publicationDate'])
        {
            $this->backlog->failure(get_lang('Publication date needed'));
            $success = false ;
        }
        
        //TODO check expirationDate
        if ( empty($this->expirationDate) && $fieldRequiredStateList['expirationDate'])
        {
            $this->backlog->failure(get_lang('Expiration date needed'));
            $success = false ;
        }
        
        if ( !empty($this->expirationDate) && $fieldRequiredStateList['expirationDate'] )
        {
            if ( $this->publicationDate > $this->expirationDate )
            {
                $this->backlog->failure(get_lang('Publication date must precede expiration date'));
                $success = false ;
            }
        }
    
        return $success;
    }
    
    
    /**
     * Display form.  If it's a form to create a new session course, the 
     * form is pre-filled with the source course informations.
     *
     * @param string    url of the cancel button
     * @param string    code of the source course
     * @return string   html output of form
     */
    function displayForm ($cancelUrl=null, $sourceCourseId=null)
    {
        
        $languageList = claro_get_lang_flat_list();
        
        // TODO cancelUrl cannot be null
        if ( is_null($cancelUrl) )
            $cancelUrl = get_path('clarolineRepositoryWeb') . 'course/index.php?cid=' . htmlspecialchars($this->courseId);
        
        $html = '';
        
        $sourceCourseId = ( is_null($sourceCourseId) ) ? ($this->sourceCourseId) : ($sourceCourseId);
        
        // Instanciate the source course to give some informations to the user
        $sourceCourse = new claroCourse();
        $sourceCourse->load(claroCourse::getCodeFromId($sourceCourseId));
        
        $html .= '<p>' . get_lang('Source course') . ': <b>' 
        .    $sourceCourse->title . '</b> (' .$sourceCourse->courseId. ').</p>'
        .   '<p>' . get_block('blockSessionCourseTip') . '</p>';
        
        
        $html .= '<form method="post" id="courseSessionSettings" action="' . $_SERVER['PHP_SELF'] . '" >' . "\n"
            .    claro_form_relay_context()
            . '<input type="hidden" name="cmd" value="'.(empty($this->courseId)?'rqProgress':'exEdit').'" />' . "\n"
            . '<input type="hidden" name="cours_id" value="'.(empty($this->id)?'':$this->id).'" />' . "\n"
            . '<input type="hidden" name="course_sourceCourseId" value="'.(empty($this->sourceCourseId)?$sourceCourse->id:$this->sourceCourseId).'" />' . "\n"
            . '<input type="hidden" name="claroFormId" value="' . uniqid('') . '" />' . "\n"
        
            . $this->getHtmlParamList('POST');
        
        $html .= '<fieldset>' . "\n"
        .   '<dl>' . "\n";
        
        // Course title
        $html .= '<dt>'
            . '<label for="course_title">'
            . get_lang('Course title')
            . (get_conf('human_label_needed') ? '<span class="required">*</span> ':'') 
            .'</label>&nbsp;:</dt>'
            . '<dd>'
            . '<input type="text" name="course_title" id="course_title" value="' . (empty($this->title)?(htmlspecialchars($sourceCourse->title)):(htmlspecialchars($this->title))) . '" size="60" />'
            . (empty($this->courseId) ? '<br /><small>'.get_lang('e.g. <em>History of Literature</em>').'</small>':'')
            . '</dd>' . "\n" ;
        
        // Course code
        $html .= '<dt>'
            . '<label for="course_officialCode">'
            . get_lang('Course code')
            . '<span class="required">*</span> '
            . '</label>&nbsp;:</dt>'
            . '<dd><input type="text" id="course_officialCode" name="course_officialCode" value="' . (empty($this->officialCode)?htmlspecialchars($sourceCourse->officialCode):htmlspecialchars($this->officialCode)) . '" size="20" maxlength="12" />'
            . (empty($this->courseId) ? '<br /><small>'.get_lang('max. 12 characters, e.g. <em>ROM2121</em>').'</small>':'')
            . '</dd>' . "\n" ;
        
        // Course titular
        $html .= '<dt>'
            . '<label for="course_titular">' . get_lang('Lecturer(s)') 
            . '</label>&nbsp;:</dt>'
            . '<dd><input type="text"  id="course_titular" name="course_titular" value="' . htmlspecialchars($this->titular) . '" size="60" />'
            . '</dd>' . "\n" ;
        
        // Course email
        $html .= '<dt>'
            . '<label for="course_email">'
            . get_lang('Email')
            . (get_conf('course_email_needed')?'<span class="required">*</span> ':'') 
            . '</label>'
            . '&nbsp;:'
            . '</dt>'
            . '<dd>'
            . '<input type="text" id="course_email" name="course_email" value="' . htmlspecialchars($this->email) . '" size="60" maxlength="255" />'
            . '</dd>'
            . "\n";
        
        // Course department name
        $html .= '<dt>'
            . '<label for="course_departmentName">'
            . (get_conf('extLinkNameNeeded')?'<span class="required">*</span> ':'')
            . get_lang('Department') . '</label>&nbsp;: </dt>'
            . '<dd>'
            . '<input type="text" name="course_departmentName" id="course_departmentName" value="' . (empty($this->departmentName)?htmlspecialchars($sourceCourse->departmentName):htmlspecialchars($this->departmentName)) . '" size="20" maxlength="30" />'
            . '</dd>'
            . "\n" ;
        
        // Course department url
        $html .= '<dt>'
            . '<label for="course_extLinkUrl" >' . get_lang('Department URL') 
            . (get_conf('extLinkUrlNeeded')?'<span class="required">*</span> ':'')
            . '</label>'
            . '&nbsp;:'
            . '</dt>'
            . '<dd>'
            . '<input type="text" name="course_extLinkUrl" id="course_extLinkUrl" value="' . (empty($this->extLinkUrl)?htmlspecialchars($sourceCourse->extLinkUrl):htmlspecialchars($this->extLinkUrl)) . '" size="60" maxlength="180" />'
            . '</dd>'
            .  "\n" ;
        
        // Course language select box
        $html .= '<dt>'
            . '<label for="course_language">'
            . get_lang('Language') . '</label>'
            . '&nbsp;<span class="required">*</span>&nbsp;:' 
            . '</dt>'
            . '<dd>'
            . claro_html_form_select('course_language', $languageList, $this->language, array('id'=>'course_language'))
            . '</dd>'
            .  "\n" ;
        
        // Course access
        $html .= '<dt>' . get_lang('Course access') . '&nbsp;:</dt>'
            . '<dd>'
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
            . '</dd>'
            . "\n" ;
        
        // Course registration + registration key
        $html .='<dt>' . get_lang('Enrolment') . '&nbsp;:</dt>'
            . '<dd>'
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
            . '<input type="text" id="registrationKey" name="course_registrationKey" value="' . htmlspecialchars($this->registrationKey) . '" />'
            . '<br />' . "\n"
            . '<img src="' . get_icon_url('enroll_forbidden') . '"  alt="" />'
            . '<input type="radio" id="registration_false"  name="course_registration" value="0" ' . ( ! $this->registration ?'checked="checked"':'') . ' />'
            . '&nbsp;'
            . '<label for="registration_false">' . get_lang('Denied') . '</label>'
            . '</dd>'
            . "\n" ;
        
        // Block course settings tip
        $html .= '<dt>&nbsp;</dt>'
            . '<dd><small><font color="gray">' . get_block('blockCourseSettingsTip') . '</font></small></dd>'
            . "\n" ;
            
        $html .= '</dl>' . "\n"
            .   '</fieldset>' . "\n";
        
        // Course visibility
        if (claro_is_platform_admin())
        {
            // Administration Information
            $html .= '<fieldset id="advancedInformation" class="collapsible collapsed">' . "\n"
                    .   '<legend><a href="#" class="doCollapse">' . get_lang('Advanced settings for administrator') . '</a></legend>' . "\n"
                    .   '<div class="collapsible-wrapper">' . "\n"
                    .   '<dl>' . "\n";
            
            // Visibility in category list
            $html .= 
                 '<dt>' . get_lang('Course visibility') . '&nbsp;:</dt>'
                . '<dd>'
                . '<img src="' . get_icon_url('visible') . '" alt="" />'
                . '<input type="radio" id="visibility_show" name="course_visibility" value="1" ' . ($this->visibility ? 'checked="checked"':'') . ' />&nbsp;'
                . '<label for="visibility_show">' . get_lang('The course is shown in the courses listing') . '</label>'
                . '<br />' . "\n"
                . '<img src="' . get_icon_url('invisible') . '" alt="" />'
                . '<input type="radio" id="visibility_hidden" name="course_visibility" value="0" ' . ( ! $this->visibility ? 'checked="checked"':'' ) . ' />&nbsp;'
                . '<label for="visibility_hidden">'
                . get_lang('Visible only to people on the user list')
                . '</label>'
                . '</dd>'
                .  "\n"
                ;        // Required legend
            
            // status : enable, pending, disable, trash
            $html .=  "\n"
                . '<dt>' . get_lang('Status') . '&nbsp;:</dt>'
                . '<dd>'
                . '<input type="radio" id="course_status_enable" name="course_status_selection" value="enable" '
                . ($this->status == 'enable' ? 'checked="checked"':'') . ' />&nbsp;'
                . '<label for="course_status_enable">' . get_lang('Available') . '</label>'
                . '<br /><br />' . "\n"
                . '<input type="radio" id="course_status_date" name="course_status_selection" value="date" '
                . ($this->status == 'date' ? 'checked="checked"':'') . ' />&nbsp;'
                . '<label for="couse_status_date">' . get_lang('Available') . '&nbsp;'. get_lang('from') . '</label> '
                . claro_html_date_form('course_publicationDay', 'course_publicationMonth', 'course_publicationYear', $this->publicationDate, 'numeric')
                . '&nbsp;<small>' . get_lang('(d/m/y)') . '</small>'
                . "\n"
                .  '<blockquote>'
                .   '<input type="checkbox" id="useExpirationDate" name="useExpirationDate" value="true" '
                .   ( $this->useExpirationDate ?' checked="checked"':' ') . '/>'
                .   ' <label for="useExpirationDate">' . get_lang('to') . '</label> ' . "\n"
                . claro_html_date_form('course_expirationDay', 'course_expirationMonth', 'course_expirationYear', $this->expirationDate, 'numeric')
                . '&nbsp;<small>' . get_lang('(d/m/y)') . '</small>'
                . '</blockquote>'
                . "\n";    
            
            $html .=  "\n"           
                . '<input type="radio" id="course_status_disabled" name="course_status_selection" value="disable" '
                . ( $this->status == 'pending' || $this->status == 'disable' || $this->status == 'trash' ? 'checked="checked"':'' ) 
                . ' />&nbsp;'
                . '<label for="course_status_disabled">'. get_lang('Not available') . '</label>'
                . '<blockquote>'
                . '<input type="radio" id="status_pending" name="course_status" value="pending" '
                . ( $this->status == 'pending' || $this->status == 'enable' || $this->status == 'date'
                    ? 'checked="checked"'
                    :'' )
                . ' />&nbsp;'
                . '<label for="status_pending">'. get_lang('Reactivable by course manager') . '</label>'
                . '<br />' . "\n"
                . '<input type="radio" id="status_disable" name="course_status" value="disable" '
                . ($this->status == 'disable' ? 'checked="checked"':'') . ' />&nbsp;'
                . '<label for="status_disable">' . get_lang('Reactivable by administrator') . '</label>'
                . '<br />' . "\n"
                . '<input type="radio" id="status_trash" name="course_status" value="trash" '
                . ($this->status == 'trash' ? 'checked="checked"':'') . ' />&nbsp;'
                . '<label for="status_trash">' . get_lang('Move to trash') . '</label>'
                . '</blockquote>'
                . "\n";
                
              $html .=   '</dd></dl></div>' . "\n" // fieldset-wrapper
                .   '</fieldset>' . "\n";
        }    
        
        $html .= '<dl><dt>'
            . '<input type="submit" name="changeProperties" value="' . get_lang('Ok') . '" onclick="selectAll(this.form.elements[\'linked_categories\'],true)" />'
            . '&nbsp;'
            . claro_html_button($cancelUrl, get_lang('Cancel'))
            . '</dt>' . "\n" ;
        
        $html .= '</dl>' . "\n" . '</form>' . "\n" ;
        
        $html .= '<p><small>' . get_lang('<span class="required">*</span> denotes required field') 
            . '</small></p>' . "\n" ;
        
        $html .= '<script type="text/javascript">
    var courseStatusEnabled = function(){
        $("#status_pending").attr("disabled", true);
        $("#status_disable").attr("disabled", true);
        $("#status_trash").attr("disabled", true);
        
        $("#course_expirationDay").attr("disabled", true);
        $("#course_expirationMonth").attr("disabled", true);
        $("#course_expirationYear").attr("disabled", true);
        
        $("#course_publicationDay").attr("disabled", true);
        $("#course_publicationMonth").attr("disabled", true);
        $("#course_publicationYear").attr("disabled", true);
        
        $("#useExpirationDate").attr("disabled", true);
    };
    
    var courseStatusDate = function(){
        $("#status_trash").attr("disabled", true);
        $("#status_pending").attr("disabled", true);
        $("#status_disable").attr("disabled", true);
        
        $("#course_publicationDay").removeAttr("disabled");
        $("#course_publicationMonth").removeAttr("disabled");
        $("#course_publicationYear").removeAttr("disabled");
        
        $("#useExpirationDate").removeAttr("disabled");
        
        if ( $("#useExpirationDate").attr("checked") ) {
            $("#course_expirationDay").removeAttr("disabled");
            $("#course_expirationMonth").removeAttr("disabled");
            $("#course_expirationYear").removeAttr("disabled");
        }
        else {
            $("#course_expirationDay").attr("disabled", true);
            $("#course_expirationMonth").attr("disabled", true);
            $("#course_expirationYear").attr("disabled", true);
        }
    };
    
    var courseStatusDisabled = function(){
        $("#status_trash").removeAttr("disabled");
        $("#status_pending").removeAttr("disabled");
        $("#status_disable").removeAttr("disabled");
        
        $("#course_expirationDay").attr("disabled", true);
        $("#course_expirationMonth").attr("disabled", true);
        $("#course_expirationYear").attr("disabled", true);
        
        $("#course_publicationDay").attr("disabled", true);
        $("#course_publicationMonth").attr("disabled", true);
        $("#course_publicationYear").attr("disabled", true);
        
        $("#useExpirationDate").attr("disabled", true);
    };
    
    $("#course_status_enable").click(courseStatusEnabled);
    
    $("#course_status_date").click(courseStatusDate);
    
    $("#course_status_disabled").click(courseStatusDisabled);
    
    $("#useExpirationDate").click(function(){
        if ( $("#useExpirationDate").attr("checked") ) {
            $("#course_expirationDay").removeAttr("disabled");
            $("#course_expirationMonth").removeAttr("disabled");
            $("#course_expirationYear").removeAttr("disabled");
        }
        else {
            $("#course_expirationDay").attr("disabled", true);
            $("#course_expirationMonth").attr("disabled", true);
            $("#course_expirationYear").attr("disabled", true);
        }
    });
    
    if ( $("#course_status_enable").attr("checked") ) {
        courseStatusEnabled();
    }
    else if ( $("#course_status_date").attr("checked") ) {
        courseStatusDate();
    }
    else {
        courseStatusDisabled();
    }
    
    $("#courseSettings").submit(function(){
        if($("#registration_true").attr("checked")){
            $("#registrationKey").val("");
        }
    });
</script>' . "\n";
        
        return $html;
    }
    
    
    /**
     * Send course creation information by mail to all platform administrators.
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
        
        $paramList['course_title']              = $this->title;
        $paramList['course_officialCode']       = $this->officialCode;
        $paramList['course_sourceCourseId']     = $this->sourceCourseId;
        $paramList['course_titular']            = $this->titular;
        $paramList['course_email']              = $this->email;
        $paramList['course_departmentName']     = $this->departmentName;
        $paramList['course_extLinkUrl']         = $this->extLinkUrl;
        $paramList['course_language']           = $this->language;
        $paramList['course_visibility']         = $this->visibility;
        $paramList['course_access']             = $this->access;
        $paramList['course_registration']       = $this->registration;
        $paramList['course_registrationKey']    = $this->registrationKey;
        $paramList['course_publicationDate']    = $this->publicationDate;
        $paramList['course_expirationDate']     = $this->expirationDate;
        $paramList['useExpirationDate']         = $this->useExpirationDate;
        $paramList['course_status']             = $this->status;
        
        $paramList = array_merge($paramList, $this->htmlParamList);
        
        foreach ($paramList as $key => $value)
        {
            $url .= '&amp;' . rawurlencode($key) . '=' . rawurlencode($value);
        }
        
        return $url;
    }
}