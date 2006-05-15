<?php // $Id$
/**
 * CLAROLINE
 *
 * This lib contain many parts of frequently used function.
 * This is not a thematic lib
 *
 * @version 1.8 $Revision$
 *
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @author see 'credits' file
 *
 * @package KERNEL
 *
 */

/**
 * SECTION :  Function to access the sql datas
 */

require_once(dirname(__FILE__) . '/sql.lib.php');

/**
 * SECTION :  Class & function to prepare a normalised html output.
 */

require_once(dirname(__FILE__) . '/html.lib.php');
require_once(dirname(__FILE__) . '/module.lib.php');

/**
 * SECTION :  Get kernel
 * SUBSECTION datas for courses
 */

/**
 * Get unique keys of a course.
 *
 * @param  string $course_id (optionnal)  If not set, it use the current course
 *         will be taken.
 * @return array list of unique keys (sys, db & path) of a course
 * @author Christophe Gesché <moosh@claroline.net>
 * @since 1.7
 */

function claro_get_course_data($course_id = NULL)
{
    global $_cid, $_course ;
    static $courseDataInCache='';
    static $_courseDatas = array();
    if ( is_null($course_id) )
    {
        $course_id = $_cid;
        $_courseDatas  = $_course;
        $courseDataInCache = $_cid;
    }
    else
    {
        if($courseDataInCache != $course_id)
        {
            $tbl_mdb_names =  claro_sql_get_main_tbl();
            $sql =  "SELECT
                    `c`.`code`              AS `sysCode`,
                    `c`.`intitule`          AS `name`,
                    `c`.`fake_code`         AS `officialCode`,
                    `c`.`directory`         AS `path`,
                    `c`.`dbName`            AS `dbName`,
                    `c`.`titulaires`        AS `titular`,
                    `c`.`email`             AS `email`  ,
                    `c`.`enrollment_key`    AS `enrollmentKey` ,
                    `c`.`languageCourse`    AS `language`,
                    `c`.`departmentUrl`     AS `extLinkUrl`,
                    `c`.`departmentUrlName` AS `extLinkName`,
                    `c`.`visible`           AS `visible`,
                    `cat`.`code`            AS `categoryCode`,
                    `cat`.`name`            AS `categoryName`,
                    `c`.`diskQuota`         AS `diskQuota`
             FROM `" . $tbl_mdb_names['course'] . "`        AS `c`
             LEFT JOIN `" . $tbl_mdb_names['category'] . "` AS `cat`
             ON `c`.`faculte` =  `cat`.`code`
             WHERE `c`.`code` = '" . addslashes($course_id) . "'";
            $_courseDatas = claro_sql_query_fetch_all($sql);
            if (!is_array($_courseDatas) || 0 == count($_courseDatas))
                return claro_failure::set_failure('course_not_found');
            ;
            $_courseDatas = $_courseDatas[0];
            $courseDataInCache = $course_id;
            $_courseDatas['visibility'  ]         = (bool) (2 == $_courseDatas['visible'] || 3 == $_courseDatas['visible'] );
            $_courseDatas['registrationAllowed']  = (bool) (1 == $_courseDatas['visible'] || 2 == $_courseDatas['visible'] );
            $_courseDatas['dbNameGlu'] = get_conf('courseTablePrefix') . $_courseDatas['dbName'] . get_conf('dbGlu'); // use in all queries
        }

    } // end if ( count($course_tbl) == 0 )
    return $_courseDatas;
}

/**
 * Get the db name of a course.
 * @param  string $course_id (optionnal)  If not set, it use the current course
 *         will be taken.
 * @return string db_name
 * @author Christophe Gesché <moosh@claroline.net>
 * @since 1.7
 */
function claro_get_course_db_name($cid=NULL)
{
    $k = claro_get_course_data($cid);

    if (isset($k['dbName'])) return $k['dbName'];
    else                     return NULL;

}

/**
 * Get the glued db name of a course.Read to be use in claro_get_course_table_name
 * @param  string $course_id (optionnal)  If not set, it use the current course
 *         will be taken.
 * @return string db_name glued
 * @author Christophe Gesché <moosh@claroline.net>
 * @since 1.7
 */
function claro_get_course_db_name_glued($cid=NULL)
{
    $k = claro_get_course_data($cid);

    if (isset($k['dbNameGlu'])) return $k['dbNameGlu'];
    else                        return NULL;
}

/**
 * Get the path of a course.
 * @param  string $course_id (optionnal)  If not set, it use the current course
 *         will be taken.
 * @return string path
 * @author Christophe Gesché <moosh@claroline.net>
 * @since 1.7
 */
function claro_get_course_path($cid=NULL)
{
    $k = claro_get_course_data($cid);
    if (isset($k['path'])) return $k['path'];
    else                   return NULL;
}

/**
 * SECTION :  Get kernel
 * SUBSECTION datas for tools
 */

/**
 * Get names  of tools in an array where key are Claro_label
 * @return array list of localised name of tools
 * @todo with plugin, this lis would be read in a dynamic datasource
 */
function claro_get_tool_name_list()
{
    static $toolNameList;

    if( ! isset( $toolNameList ) )
    {
        $toolNameList = array('CLANN___' => 'Announcement'
        ,                     'CLFRM___' => 'Forums'
        ,                     'CLCAL___' => 'Agenda'
        ,                     'CLCHT___' => 'Chat'
        ,                     'CLDOC___' => 'Documents and Links'
        ,                     'CLDSC___' => 'Course description'
        ,                     'CLGRP___' => 'Groups'
        ,                     'CLLNP___' => 'Learning path'
        ,                     'CLQWZ___' => 'Exercises'
        ,                     'CLWRK___' => 'Work'
        ,                     'CLUSR___' => 'Users'
        ,                     'CLWIKI__' => 'Wiki'
        );
    }
    return $toolNameList;
}

/**
 * SECTION :  Get kernel
 * SUBSECTION datas for rel tool courses
 */

/**
 * Return the tool list for a course according a certain access level
 * @param  string  $courseIdReq - the requested course id
 * @param  string  $accessLevelReq (optionnal) -  should be in 'ALL', 'COURSE_MEMBER',
 *                'GROUP_MEMBER', 'COURSE_TUTOR','COURSE_MANAGER', 'PLATFORM_ADMIN'.
 *                 Default is 'ALL'
 * @param  boolean $force (optionnal) - reset the result cache, default is false
 * @return array   the course list
 */

function claro_get_course_tool_list($courseIdReq, $accessLevelReq = 'ALL', $force = false)
{
    global $clarolineRepositoryWeb;

    static $courseTooList = null, $courseId = null, $accessLevel = null;

    if (   is_null($courseTooList)
        || $courseId    != $courseIdReq
        || $accessLevel != $accessLevelReq
        || $force )
    {
        $courseId   = $courseIdReq;
        $accessLevel = $accessLevelReq;

        $tbl_mdb_names        = claro_sql_get_main_tbl();
        $tbl_tool_list        = $tbl_mdb_names['tool'];
        $tbl_cdb_names        = claro_sql_get_course_tbl( claro_get_course_db_name_glued($courseIdReq) );
        $tbl_course_tool_list = $tbl_cdb_names['tool'];

        /*
         * Build a list containing all the necessary access level
         */

        $standartAccessList = array('ALL',           'PLATFORM_MEMBER',
                                    'COURSE_MEMBER', 'COURSE_TUTOR',
                                    'GROUP_MEMBER',  'GROUP_TUTOR',
                                    'COURSE_ADMIN',  'PLATFORM_ADMIN');

        if ( ! in_array($accessLevel, $standartAccessList) ) claro_die('Wrong access level : '.$accessLevel);

        foreach($standartAccessList as $thisAccessType)
        {
            $accessList[] = $thisAccessType;

            if ($thisAccessType == $accessLevel) break;
        }

        /*
         * Search all the tool corresponding to this access levels
         */

        $sql ="SELECT ctl.id                      AS id,
                      pct.claro_label             AS label,
                      ctl.script_name             AS name,
                      ctl.access                  AS access,
                      IFNULL(pct.icon,'tool.gif') AS icon,
                      pct.access_manager          AS access_manager,
                      ISNULL(ctl.tool_id)         AS external,

                      IFNULL( ctl.script_url ,
                              CONCAT('" . $clarolineRepositoryWeb . "', pct.script_url) )
                      AS url

               FROM `" . $tbl_course_tool_list . "` AS ctl

               LEFT JOIN `" . $tbl_tool_list . "` AS pct
                      ON  pct.id = ctl.tool_id

               WHERE ctl.access IN ('" . implode("', '", $accessList) . "')
               ORDER BY external, ctl.rank";

        $courseToolList = claro_sql_query_fetch_all($sql);

        /**
         * Complete the list with the appropriate tool names
         */

        $toolNameList = claro_get_tool_name_list();

        foreach ($courseToolList as $thisToolKey => $thisToolAttributeList)
        {
            if ( trim($thisToolAttributeList['name']) == '')
            {
                if ( ! empty ($thisToolAttributeList['label'] ) )
                {
                    $courseToolList[$thisToolKey]['name'] = get_lang($toolNameList[$thisToolAttributeList['label']]);
                }
                else
                {
                    $courseToolList[$thisToolKey]['name'] = get_lang('No name');
                }
            }
            else
            {
                continue;
            }
        }
    } // end if $force

    return $courseToolList;
}

/**
 * SECTION : CLAROLINE FAILURE MANGEMENT
 */


$claro_failureList = array();

/**
 * collects and manage failures occuring during script execution
 * The main purpose is allowing to manage the display messages externaly
 * from functions or objects. This strengthens encapsulation principle
 *
 * @example :
 *
 *  function my_function()
 *  {
 *      if ($succeeds) return true;
 *      else           return claro_failure::set_failure('my_failure_type');
 *  }
 *
 *  if ( my_function() )
 *  {
 *      SOME CODE ...
 *  }
 *  else
 *  {
 *      $failure_type = claro_failure::get_last_failure()
 *  }
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 * @package failure
 */

class claro_failure
{
    /*
     * IMPLEMENTATION NOTE : For now the $claro_failureList list is set to the
     * global scope, as PHP 4 is unable to manage static variable in class. But
     * this feature is awaited in PHP 5. The class is already written to
     * minimize the changes when static class variable will be possible. And the
     * API won't change.
     */

    // var $claro_failureList = array();

    /**
     * Pile the last failure in the failure list
     *
     * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
     * @param  string $failureType the type of failure
     * @global array  claro_failureList
     * @return boolean false to stay consistent with the main script
     */

    function set_failure($failureType)
    {
        global $claro_failureList;

        $claro_failureList[] = $failureType;

        return false;
    }


    /**
     * get the last failure stored
     *
     * @author Hugues Peeters <hugues.peeters@claroline.net>
     * @return string the last failure stored
     */

    function get_last_failure()
    {
        global $claro_failureList;

        if( isset( $claro_failureList[ count($claro_failureList) - 1 ] ) )
            return $claro_failureList[ count($claro_failureList) - 1 ];
        else
            return '';
    }
}


/**
 * SECTION :  "view AS"
 */


/**
 * Set if  the  access level switcher is aivailable
 *
 * @global boolean claro_toolViewOptionEnabled
 * @return true
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 */

function claro_enable_tool_view_option()
{
    global $claro_toolViewOptionEnabled;
    $claro_toolViewOptionEnabled = true;
    return true;
}


/**
 * Set if  the  access level switcher is aivailable
 *
 * @param  $viewMode 'STUDENT' or 'COURSE_ADMIN'
 * @return true if set succeed.
 *
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 */

function claro_set_tool_view_mode($viewMode)
{
    $viewMode = strtoupper($viewMode); // to be sure ...

    if ( in_array($viewMode, array('STUDENT', 'COURSE_ADMIN') ) )
    {
        $_SESSION['claro_toolViewMode'] = $viewMode;
        return true;
    }
    else
    {
        return false;
    }
}


/**
 * Display options to switch between student view and course manager view
 * This function is mainly used by the claro_init_banner.inc.php file
 * The display mode command will only be displayed if
 * claro_set_tool_view_mode(true) has been previously called.
 * This will affect the return value of claro_is_allowed_to_edit() function.
 * It will ten return false as the user is a simple student.
 *
 * @author roan embrechts
 * @author Hugues Peeters
 * @param string - $viewModeRequested.
 *                 For now it can be 'STUDENT' or 'COURSE_ADMIN'
 * @see claro_is_allowed_to_edit()
 * @see claro_is_display_mode_available()
 * @see claro_set_display_mode_available()
 * @see claro_get_tool_view_mode()
 * @see claro_set_tool_view_mode()
 * @return true;
 */


function claro_disp_tool_view_option($viewModeRequested = false)
{
    global $is_courseAdmin;

    if ( ! $is_courseAdmin || ! claro_is_display_mode_available() ) return false;

    if ($viewModeRequested) claro_set_tool_view_mode($viewModeRequested);

    $currentViewMode = claro_get_tool_view_mode();

    /*------------------------------------------------------------------------
    PREPARE URL
    ------------------------------------------------------------------------*/

    /*
    * check if the REQUEST_URI contains already URL parameters
    * (thus a questionmark)
    */

    if ( strstr($_SERVER['REQUEST_URI' ], '?') ) $url = $_SERVER['REQUEST_URI' ];
    else                                         $url = $_SERVER['PHP_SELF'] . '?';

    /*
    * remove previous view mode request from the url
    */

    $url = str_replace('&amp;viewMode=STUDENT'     , '', $url);
    $url = str_replace('&amp;viewMode=COURSE_ADMIN', '', $url);

    /*------------------------------------------------------------------------
    INIT BUTTONS
    -------------------------------------------------------------------------*/


    switch ($currentViewMode)
    {
        case 'COURSE_ADMIN' :

        $studentButton = '<a href="' . $url . '&amp;viewMode=STUDENT">'
        .                get_lang('Student')
        .                '</a>'
        ;
        $courseAdminButton = '<b>' . get_lang('Course manager') . '</b>';

        break;

        case 'STUDENT' :

        $studentButton     = '<b>'.get_lang('Student').'</b>';
        $courseAdminButton = '<a href="' . $url . '&amp;viewMode=COURSE_ADMIN">'
        . get_lang('Course manager')
        . '</a>';
        break;
    }

    /*------------------------------------------------------------------------
    DISPLAY COMMANDS MENU
    ------------------------------------------------------------------------*/

    return get_lang('View mode') . ' : '
    .    $studentButton
    .    ' | '
    .    $courseAdminButton
    ;
}



/**
 * return the current mode in tool able to handle different view mode
 *
 * @return string 'COURSE_ADMIN' or 'STUDENT'
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 */

function claro_get_tool_view_mode()
{
    // check first if a viewMode has been requested
    // if one was requested change the current viewMode to the mode asked
    // if there was no change requested and there is nothing in session
    // concerning view mode set the default viewMode
    // if there was something in session and nothing
    // in request keep the session value ( == nothing to do)
    if( isset($_REQUEST['viewMode']) )
    {
        claro_set_tool_view_mode($_REQUEST['viewMode']);
    }
    elseif( ! isset($_SESSION['claro_toolViewMode']) )
    {
        claro_set_tool_view_mode('COURSE_ADMIN'); // default
    }

    return $_SESSION['claro_toolViewMode'];
}


/**
 * Function that removes the need to directly use is_courseAdmin global in
 * tool scripts. It returns true or false depending on the user's rights in
 * this particular course.
 *
 * @version 1.1, February 2004
 * @return boolean true: the user has the rights to edit, false: he does not
 * @author Roan Embrechts
 * @author Patrick Cool
 */

function claro_is_allowed_to_edit()
{
    global $is_courseAdmin;

    if ( claro_is_display_mode_available() )
    {
        return $is_courseAdmin && (claro_get_tool_view_mode() != 'STUDENT');
    }
    else
    {
        return $is_courseAdmin;
    }
}

/**
 *
 *
 * @return boolean
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 */

function claro_is_display_mode_available()
{
    global $is_display_mode_available;
    return $is_display_mode_available;
}

/**
 *
 *
 * @param boolean $mode state to set in mode
 * @return boolean mode
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 */


function claro_set_display_mode_available($mode)
{
    global $is_display_mode_available;
    $is_display_mode_available = $mode;
}


/**
 * compose currentdate with server time shift
 *
 */
function claro_date($format, $timestamp = -1)
{
    if ($timestamp == -1) return date($format, claro_time());
    else                  return date($format, $timestamp);

}

/**
 * compose currentdate with server time shift
 *
 */
function claro_time()
{
     $mainTimeShift = (int) (isset($GLOBALS['mainTimeShift'])?$GLOBALS['mainTimeShift']:0);
     return time()+(3600 * $mainTimeShift);
}
//////////////////////////////////////////////////////////////////////////////
//                              INPUT HANDLING
//
//////////////////////////////////////////////////////////////////////////////

/**
 * checks if the javascript is enabled on the client browser
 * Actually a cookies is set on the header by a javascript code.
 * If this cookie isn't set, it means javascript isn't enabled.
 *
 * @return boolean enabling state of javascript
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 */

function claro_is_javascript_enabled()
{
    global $_COOKIE;

    if ( isset( $_COOKIE['javascriptEnabled'] ) && $_COOKIE['javascriptEnabled'] == true)
    {
        return true;
    }
    else
    {
        return false;
    }
}



/**
 * get the list  of aivailable languages on the platform
 *
 * @author Christophe Gesché <moosh@claroline.net>
 *
 * @return array( langCode => langLabel) with aivailable languages
 */
function claro_get_language_list()
{
    global $includePath, $langNameOfLang;
    $dirname = $includePath . '/../lang/';

    if($dirname[strlen($dirname)-1]!='/')
    $dirname .= '/';

    if (!file_exists($dirname)) trigger_error('lang repository not found',E_USER_WARNING);

    $handle = opendir($dirname);

    while ( ($entries = readdir($handle) ) )
    {
        if ($entries == '.' || $entries == '..' || $entries == 'CVS')
        continue;
        if (is_dir($dirname . $entries))
        {
            if (isset($langNameOfLang[$entries])) $language_list[$entries]['langNameCurrentLang'] = $langNameOfLang[$entries];
            $language_list[$entries]['langNameLocaleLang']  = $entries;
        }
    }
    closedir($handle);
    return $language_list;
}

/**
 * SECTION : PHP COMPAT For PHP backward compatibility
 */

/**
 * Return the value of a Claroline configuration parameter
 * @param string $param config parameter
 * @param mixed $default (optionnal) - set a defaut to return value
 *                                     if no paramater with such a name is found.
 * @return string param value
 * @todo http://www.claroline.net/forum/viewtopic.php?t=4579
*/

function get_conf($param, $default = null)
{
    if     ( isset($GLOBALS[$param]) )  return $GLOBALS[$param];
    elseif ( defined($param)         )  return constant($param);
    else                                return $default;
}

/**
 * SECTION : PHP COMPAT For PHP backward compatibility
 */

/**
 * Replace str_ireplace()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @link        http://php.net/function.str_ireplace
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision$
 * @since       PHP 5
 * @require     PHP 4.0.0 (user_error)
 * @note        count not by returned by reference, to enable
 *              change '$count = null' to '&$count'
 */
if (!function_exists('str_ireplace')) {
    function str_ireplace($search, $replace, $subject, $count = null)
    {
        // Sanity check
        if (is_string($search) && is_array($replace)) {
            user_error('Array to string conversion', E_USER_NOTICE);
            $replace = (string) $replace;
        }

        // If search isn't an array, make it one
        if (!is_array($search)) {
            $search = array ($search);
        }
        $search = array_values($search);

        // If replace isn't an array, make it one, and pad it to the length of search
        if (!is_array($replace)) {
            $replace_string = $replace;

            $replace = array ();
            for ($i = 0, $c = count($search); $i < $c; $i++) {
                $replace[$i] = $replace_string;
            }
        }
        $replace = array_values($replace);

        // Check the replace array is padded to the correct length
        $length_replace = count($replace);
        $length_search = count($search);
        if ($length_replace < $length_search) {
            for ($i = $length_replace; $i < $length_search; $i++) {
                $replace[$i] = '';
            }
        }

        // If subject is not an array, make it one
        $was_array = false;
        if (!is_array($subject)) {
            $was_array = true;
            $subject = array ($subject);
        }

        // Loop through each subject
        $count = 0;
        foreach ($subject as $subject_key => $subject_value) {
            // Loop through each search
            foreach ($search as $search_key => $search_value) {
                // Split the array into segments, in between each part is our search
                $segments = explode(strtolower($search_value), strtolower($subject_value));

                // The number of replacements done is the number of segments minus the first
                $count += count($segments) - 1;
                $pos = 0;

                // Loop through each segment
                foreach ($segments as $segment_key => $segment_value) {
                    // Replace the lowercase segments with the upper case versions
                    $segments[$segment_key] = substr($subject_value, $pos, strlen($segment_value));
                    // Increase the position relative to the initial string
                    $pos += strlen($segment_value) + strlen($search_value);
                }

                // Put our original string back together
                $subject_value = implode($replace[$search_key], $segments);
            }

            $result[$subject_key] = $subject_value;
        }

        // Check if subject was initially a string and return it as a string
        if ($was_array === true) {
            return $result[0];
        }

        // Otherwise, just return the array
        return $result;
    }
}
/**
 * SECTION : security
 */

/**
 * Terminate the script and display message
 *
 * @param string message
 */

function claro_die($message)
{
    global $includePath, $clarolineRepositoryWeb, $claro_stylesheet, $urlAppend ,
           $siteName, $text_dir, $_uid, $_cid, $administrator_name, $administrator_email,
           $is_platformAdmin, $_course, $_user, $_courseToolList, $coursesRepositoryWeb,
           $is_courseAllowed, $imgRepositoryWeb, $_tid, $is_courseMember, $_gid;

    if ( ! headers_sent () )
    {
    // display header
        require $includePath . '/claro_init_header.inc.php';
    }

    echo '<table align="center">'
    .    '<tr><td>'
    .    claro_html_message_box($message)
    .    '</td></tr>'
    .    '</table>'
    ;

    require $includePath . '/claro_init_footer.inc.php' ;

    die(); // necessary to prevent any continuation of the application
}


/**
 * HTTP response splitting security flaw filter
 * @author Frederic Minne <zefredz@gmail.com>
 * @return string clean string to filter http_response_splitting attack
 * @see http://www.saintcorporation.com/cgi-bin/demo_tut.pl?tutorial_name=HTTP_Response_Splitting.html
 */

function http_response_splitting_workaround( $str )
{
    $dangerousCharactersPattern = '~(\r\n|\r|\n|%0a|%0d|%0D|%0A)~';
    return preg_replace( $dangerousCharactersPattern, '', $str );
}

/**
 * Strip the slashes coming from browser request
 *
 * If the php.ini setting MAGIC_QUOTE_GPC is set to ON, all the variables
 * content comming frome the browser are automatically quoted by adding
 * slashes (default setting before PHP 4.3). claro_unquote_gpc() removes
 * these slashes. It needs to be called just once at the biginning
 * of the script.
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 *
 * @return void
 */

function claro_unquote_gpc()
{
    if ( ! defined('CL_GPC_UNQUOTED') )
    {
        if ( get_magic_quotes_gpc() )
        {
         /*
          * The new version is written in a safer approach inspired by Ilia
          * Alshanetsky. The previous approach which was using recursive
          * function permits to smash the stack and crash PHP. For example if
          * the user supplies a very deep multidimensional array, such as
          * foo[][][][] ..., the recursion can reach the point of exhausting
          * the stack. Generating such an attack is quite trivial, via the
          * use of :
          *
          *    str_repeat() function example $str = str_repeat("[]", 100000);
          *    file_get_contents("http://sitre.com.scriptphp?foo={$str}");
          */

            $inputList = array(&$_REQUEST, &$_GET, &$_POST,
                               &$_COOKIE , &$_ENV, &$_SERVER);

            while ( list($topKey, $array) = each($inputList) )
            {
                foreach( $array as $childKey => $value)
                {
                    if ( ! is_array($value) )
                    {
                        $inputList[$topKey][$childKey] = stripslashes($value);
                    }
                    else
                    {
                        $inputList[] =& $inputList[$topKey][$childKey];
                    }
                }
            }

            define('CL_GPC_UNQUOTED', true);

        } // end if get_magic_quotes_gpc
    }
}

/**
 * Return the value of a Claroline configuration parameter
 * @param string $param config parameter
 * @param mixed $default (optionnal) - set a defaut to return value
 *                                     if no paramater with such a name is found.
 * @return string param value
 * @todo http://www.claroline.net/forum/viewtopic.php?t=4579
*/

function get_init($param)
{

    static $initValueList = array( '_uid','_cid','_gid','_tid'
                                 , 'is_platformAdmin'
                                 , '_course'
                                 , '_user'
                                 , '_group'
                                 , '_groupProperties'
                                 , '_courseUser'
                                 , '_courseTool'
                                 , '_courseToolList'
                                 , 'is_courseMember'
                                 , 'is_courseTutor'
                                 , 'is_courseAdmin'
                                 , 'is_allowedCreateCourse'
                                 , 'is_groupMember'
                                 , 'is_groupTutor'
                                 , 'is_groupAllowed'
                                 , 'is_toolAllowed'
                                 );

    if(!in_array($param, $initValueList )) trigger_error( htmlentities($param) . ' is not a know init value name', E_USER_NOTICE);
    if     ( array_key_exists($param,$GLOBALS) )  return $GLOBALS[$param];
    elseif ( defined($param)         )            return constant($param);
    else                                          trigger_error( htmlentities($param) . ' is not a setted init value name', E_USER_NOTICE);
    return null;
}



/**
 * convert a duration in seconds to a human readable duration
 * @author Sébastien Piraux <pir@cerdecam.be>
 * @param integer duration time in seconds to convert to a human readable duration
 */

function claro_disp_duration( $duration  )
{
    if( $duration == 0 ) return '0 '.get_lang('SecondShort');

    $days = floor(($duration/86400));
    $duration = $duration % 86400;

    $hours = floor(($duration/3600));
    $duration = $duration % 3600;

    $minutes = floor(($duration/60));
    $duration = $duration % 60;
    // $duration is now equal to seconds

    $durationString = '';

    if( $days > 0 ) $durationString .= $days . ' ' . get_lang('PeriodDayShort') . ' ';
    if( $hours > 0 ) $durationString .= $hours . ' ' . get_lang('PeriodHourShort') . ' ';
    if( $minutes > 0 ) $durationString .= $minutes . ' ' . get_lang('MinuteShort') . ' ';
    if( $duration > 0 ) $durationString .= $duration . ' ' . get_lang('SecondShort');

    return $durationString;
}


/**
 * @param $contextKeys array or null
 *
 * array can contain course, group, user and/or toolInstance
 *
 * return array of context requested containing current id fors these context.
 */
function claro_get_current_context($contextKeys = null)
{
    $currentKeys = array();

    $_courseTool = get_init('_courseTool');
    if(!is_null($contextKeys) && !is_array($contextKeys)) $contextKeys = array($contextKeys);

    if((is_null($contextKeys) || in_array(CLARO_CONTEXT_COURSE,$contextKeys))       && !is_null($GLOBALS['_cid'])) $currentKeys[CLARO_CONTEXT_COURSE]       = $GLOBALS['_cid'];
    if((is_null($contextKeys) || in_array(CLARO_CONTEXT_GROUP,$contextKeys))        && !is_null($GLOBALS['_gid'])) $currentKeys[CLARO_CONTEXT_GROUP]        = get_init('_gid');
    if((is_null($contextKeys) || in_array(CLARO_CONTEXT_USER,$contextKeys))         && !is_null($GLOBALS['_uid'])) $currentKeys[CLARO_CONTEXT_USER]         = get_init('_uid');
    //if((is_null($contextKeys) || in_array('session',$contextKeys))      && !is_null($GLOBALS['_sid']))  $currentKeys['session']       = get_init('_sid');
    if((is_null($contextKeys) || in_array('toolInstance',$contextKeys)) && !is_null($GLOBALS['_tid'])) $currentKeys['toolInstance'] = get_init('_tid');

    return $currentKeys;
}
?>