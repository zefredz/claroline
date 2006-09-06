<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );
/**
 * CLAROLINE
 *
 * Course Class
 *
 * @version 1.8 $Revision$
 *
 * @copyright 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @package Kernel
 *
 * @author Claro Team <cvs@claroline.net>
 * @author Mathieu Laurent <laurent@cerdecam.be>
 * @author Sebastien Piraux <piraux@cerdecam.be>
 *
 */

require_once dirname(__FILE__) . '/backlog.class.php';
require_once dirname(__FILE__) . '/admin.lib.inc.php'; // for delete course function

class Course
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
    var $departmentUrl;

    // Language of the course
    var $language;

    // Course access (true = open, false = private)
    var $access;

    // Enrolment (true = open, false = close)
    var $enrolment;

    // Enrolment key
    var $enrolmentKey;

    // Backlog object
    var $backlog;

    /**
     *
     */

    function Course ()
    {
        $this->courseId = '';
        $this->title = '';
        $this->officialCode = '';
        $this->titular = '';
        $this->email = '';
        $this->category = '';
        $this->departmentName = '';
        $this->departmentUrl = '';
        $this->language = get_conf('platformLanguage');
        $this->access = get_conf('defaultVisibilityForANewCourse') == 2 or get_conf('defaultVisibilityForANewCourse') == 3 ? true : false;
        $this->enrolment = get_conf('defaultVisibilityForANewCourse') == 1 or get_conf('defaultVisibilityForANewCourse') == 2 ? true : false;
        $this->enrolmentKey = '';

        $this->backlog = new Backlog();
    }
    
    /**
     *
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
            $this->departmentUrl = $course_data['extLinkUrl'];
            $this->language = $course_data['language'];
            $this->access = $course_data['visibility'];
            $this->enrolment = $course_data['registrationAllowed'];
            $this->enrolmentKey = $course_data['enrollmentKey'];
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     *
     */

    function save ()
    {
        $tbl_mdb_names = claro_sql_get_main_tbl();
        $tbl_course = $tbl_mdb_names['course'];

        if ( empty($this->courseId) )
        {
            // TODO get code from claroline/course/create.php
            // insert            
        }
        else
        {
            // update

            $visibility = getCourseVisibility($this->access,$this->enrolment);

            $sql = "UPDATE `" . $tbl_course . "`
                    SET `intitule`         = '" . addslashes($this->title) . "',
                        `faculte`          = '" . addslashes($this->category) . "',
                        `titulaires`       = '" . addslashes($this->titular) . "',
                        `fake_code`        = '" . addslashes($this->officialCode) . "',
                        `languageCourse`   = '" . addslashes($this->language) . "',
                        `departmentUrlName`= '" . addslashes($this->departmentName) . "',
                        `departmentUrl`    = '" . addslashes($this->departmentUrl) . "',
                        `email`            = '" . addslashes($this->email) . "',
                        `enrollment_key`   = '" . addslashes($this->enrolmentKey) . "',
                        `visible`          = "  . (int) $visibility . "
                    WHERE code='" . addslashes($this->courseId) . "'";

            return claro_sql_query($sql);
        }
    }
    
    /**
     *
     */

    function delete ()
    {
        return delete_course($this->courseId);
    }
    
    /**
     *
     */

    function handleForm ()
    {
        // TODO get data from $_REQUEST, trim, cast, ...

        $this->title = $_REQUEST['course_title'];
        $this->officialCode = $_REQUEST['course_officialCode'];
        $this->titular = $_REQUEST['course_titular'];
        $this->email = $_REQUEST['course_email'];            
        $this->category = $_REQUEST['course_category'];
        $this->departmentUrl = $_REQUEST['course_departmentUrl'];
        $this->departmentUrl = $_REQUEST['course_departmentUrl'];
        $this->language = $_REQUEST['course_language'];
        $this->access = $_REQUEST['course_access'];
        $this->enrolment = $_REQUEST['course_enrolment'];
        $this->enrolmentKey = $_REQUEST['course_enrolmentKey'];
    }
    
    /**
     *
     */

    function validate ()
    {
        $success = true ;

        /**
         * Configuration array , define here which field can be left empty or not
         */

        $fieldRequiredStateList['title'        ] = get_conf('human_label_needed');
        $fieldRequiredStateList['officialCode' ] = get_conf('human_code_needed');
        $fieldRequiredStateList['titular'      ] = false;
        $fieldRequiredStateList['email'        ] = get_conf('course_email_needed');
        $fieldRequiredStateList['category'     ] = true;
        $fieldRequiredStateList['language'     ] = true;

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
        
        // Validate email
        if ( empty($this->email) && $fieldRequiredStateList['email'])
        {
            $this->backlog->failure(get_lang('Email needed'));
            $success = false ;
        }
        else
        {
            if ( !empty($this->email) )
            {
                // TODO : multi-email validation 
            }
        }

        // Validate course category
        if ( is_null($this->category) && $fieldRequiredStateList['category'])
        {
            $this->backlog->failure(get_lang('Category needed (you must choose a category)'));
            $success = false ;
        }
        
        // Validate course language
        if ( empty($this->language) && $fieldRequiredStateList['language'])
        {
            $this->backlog->failure(get_lang('language needed'));
            $success = false ;
        }

        // Validate department url

        // check if department url is set properly
        $regexp = "^(http|https|ftp)\://[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(:[a-zA-Z0-9]*)?/?([a-zA-Z0-9\-\._\?\,\'/\\\+&%\$#\=~])*$";

        if ( (!empty($this->departmentUrl)) && !eregi($regexp,$this->departmentUrl) )
        {
            // Problem with url. try to repair
            // if  it  only the protocol missing add http
            if ( eregi('^[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(:[a-zA-Z0-9]*)?/?([a-zA-Z0-9\-\._\?\,\'/\\\+&%\$#\=~])*$', $this->departmentUrl)
            && ( eregi($regexp, 'http://' . $this->departmentUrl)))
            {
                $this->departmentUrl = 'http://' . $this->departmentUrl;
            }
            else
            {
                 $this->backlog->failure(get_lang('Department URL is not valid'));
                 $success = false ;                       
            }
        }

        return $success ;
    }
    
    /**
     *
     */

    function displayForm ($cancelUrl=null)
    {
        global $clarolineRepositoryWeb, $imgRepositoryWeb;

        $languageList = claro_get_lang_flat_list();
        $categoryList = claro_get_cat_flat_list();

        if ( ! in_array($this->category,$categoryList) )
        {
            $this->category = 'choose_one';
            $categoryList = array_merge( array(get_lang('Choose one')=>'choose_one'), $categoryList);
        }

        // TODO cancelUrl cannot be null
        if ( is_null($cancelUrl) )
            $cancelUrl = $clarolineRepositoryWeb . 'course/index.php?cid=' . htmlspecialchars($this->courseId);
        
        $html = '';

        $html .= '<form method="post" action="' . $_SERVER['PHP_SELF'] . '">' . "\n"
            . '<table  cellpadding="3" border="0">' . "\n" ;

        // Course title

        $html .= '<tr>' . "\n"
            . '<td align="right">'
            . '<label for="course_title">'
            . (get_conf('human_label_needed') ? '<span class="required">*</span>':'') . get_lang('Course title')
            .'</label>&nbsp;:</td>' 
            . '<td><input type="text" name="course_title" id="course_title" value="' . htmlspecialchars($this->title) . '" size="60"></td>'
            . '</tr>' . "\n" ;

        // Course code

        $html .= '<tr>' . "\n"
            . '<td align="right">'
            . '<label for="course_code">' 
            . (get_conf('human_code_needed') ? '<span class="required">*</span>' :'') . get_lang('Course code')
            . '</label>&nbsp;:</td>'
            . '<td><input type="text" id="course_code" name="course_code" value="' . htmlspecialchars($this->officialCode) . '" size="20"></td>'
            . '</tr>' . "\n" ;

        // Course titular

        $html .= '<tr>' . "\n"
            . '<td align="right">'
            . '<label for="course_titular">' . get_lang('Lecturer(s)') . '</label>&nbsp;:</td>'
            . '<td><input type="text"  id="course_titular" name="course_titular" value="' . htmlspecialchars($this->titular) . '" size="60"></td>'
            . '</tr>' . "\n" ;

        // Course email

        $html .= '<tr>' . "\n"
            . '<td align="right">'
            . '<label for="course_email">'
            . (get_conf('course_email_needed')?'<span class="required">*</span>':'') . get_lang('Email')
            . '</label>&nbsp;:</td>'
            . '<td><input type="text" id="course_email" name="course_email" value="' . htmlspecialchars($this->email) . '" size="60" maxlength="255"></td>'
            . '</tr>' . "\n";

        // Course category select box

        $html .= '<tr>' . "\n"
            . '<td align="right">'
            . '<label for="course_category"><span class="required">*</span>' . get_lang('Category') . '</label> :</td>'
            . '<td>'
            . claro_html_form_select( 'course_category', $categoryList, $this->category, array('id'=>'course_category') )
            . '</td>'
            . '</tr>' . "\n" ;

        // Course department name

        $html .= '<tr valign="top">' . "\n"
            . '<td align="right"><label for="course_dept_name">' . get_lang('Department') . '</label>&nbsp;: </td>'
            . '<td><input type="text" name="course_dept_name" id="course_dept_name" value="' . htmlspecialchars($this->departmentName) . '" size="20" maxlength="30"></td>'
            . '</tr>' . "\n" ;

        // Course department url

        $html .= '<tr valign="top" >' . "\n"
            . '<td align="right" nowrap="nowrap"><label for="course_dept_url" >' . get_lang('Department URL') . '</label>&nbsp;:</td>'
            . '<td><input type="text" name="course_dept_url" id="course_dept_url" value="' . htmlspecialchars($this->departmentUrl) . '" size="60" maxlength="180"></td>'
            . '</tr>' . "\n" ;

        // Course language select box

        $html .= '<tr valign="top" >' . "\n"
            . '<td align="right">'
            . '<label for="course_language"><span class="required">*</span>' . get_lang('Language') . '</label>&nbsp;:</td>'
            . '<td>'
            . claro_html_form_select('course_language', $languageList, $this->language, array('id'=>'course_language'))
            . '</td>'
            . '</tr>' . "\n" ;

        // Course access

        $html .= '<tr valign="top" >' . "\n"
            . '<td align="right" nowrap>' . get_lang('Course access') . '&nbsp;:</td>'
            . '<td>'
            . '<img src="' . $imgRepositoryWeb . '/access_open.gif" />'
            . '<input type="radio" id="access_true" name="course_access" value="1" ' . ($this->access ? 'checked="checked"':'') . '>&nbsp;'
            . '<label for="access_true">' . get_lang('Public access from campus home page even without login') . '</label>'
            . '<br />' . "\n"
            . '<img src="' . $imgRepositoryWeb . 'access_locked.gif" />'
            . '<input type="radio" id="access_false" name="course_access" value="0" ' . ( ! $this->access ? 'checked="checked"':'' ) . '>&nbsp;'
            . '<label for="access_false">' . get_lang('Private access (site accessible only to people on the <a href="%url">User list</a>)' ,
                                              array('%url'=> '../user/user.php')) 
            . '</label>'
            . '</td>'
            . '</tr>' . "\n" ;
        // Course enrolment + enrolment key

        $html .= '<tr valign="top">' . "\n"
            . '<td align="right">' . get_lang('Enrolment') . '&nbsp;:</td>'
            . '<td>'
            . '<img src="' . $imgRepositoryWeb . '/enroll_open.gif" />'
            . '<input type="radio" id="enrolment_true" name="course_enrolment" value="1" ' . ($this->enrolment?'checked="checked"':'') . '>&nbsp;'
            . '<label for="enrolment_true">' . get_lang('Allowed') . '</label>'
            . '<label for="enrolment_key">'
            . ' - ' . get_lang('Enrolment key') . '<small>(' . get_lang('Optional') . ')</small> :'
            . '</label>'
            . '<input type="text" id="enrolment_key" name="course_enrolment_key" value="' . htmlspecialchars($this->enrolmentKey) . '" />'
            . '<br />' . "\n"
            . '<img src="' . $imgRepositoryWeb . 'enroll_locked.gif" />'
            . '<input type="radio" id="enrolment_false"  name="course_enrolment" value="0"' . ( ! $this->enrolment ?'checked="checked"':'') . '>&nbsp;'
            . '<label for="enrolment_false">' . get_lang('Denied') . '</label>'
            . '</td>'
            . '</tr>' . "\n" ;

        // Block course settings tip

        $html .= '<tr>' . "\n"
            . '<td>&nbsp;</td>'
            . '<td><small><font color="gray">' . get_block('blockCourseSettingsTip') . '</font></small></td>'
            . '</tr>' . "\n" ;

        // Required legend

        $html .= '<tr>' . "\n"
            . '<td>&nbsp;</td>'
            . '<td>' . get_lang('<span class=\"required\">*</span> denotes required field') . '</td>'
            . '</tr>' . "\n" ;

        $html .= '<tr>' . "\n"
            . '<td>&nbsp;</td>'
            . '<td>'
            . '<input type="submit" name="changeProperties" value="' . get_lang('Ok') . '" />'
            . claro_html_button($cancelUrl, get_lang('Cancel'))
            . '</td>' . "\n"
            . '</tr>' . "\n" ;

        $html .= '</table>' . "\n"
            .'</form>' . "\n" ;

        return $html;

    }

}

?>
