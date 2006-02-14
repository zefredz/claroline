<?php # $Id$
/**
 * CLAROLINE
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

require_once(dirname(__FILE__) . '/db.mysql.lib.php');
require_once(dirname(__FILE__) . '/html.lib.php');

/**
 * get unique keys of a course.
 * @param  string $course_id (optionnal)  If not set, it use the current course
 *         will be taken.
 * @return array list of unique keys (sys, db & path) of a course
 * @author Christophe Gesché <moosh@claroline.net>
 * @since 1.7
 */

function claro_get_course_data($course_id = NULL)
{
    global $_cid, $_course, $courseTablePrefix , $dbGlu;
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

                    `c`.`code` `sysCode`,
                    `c`.`intitule`  `name`,
                    `c`.`fake_code` `officialCode`,
                    `c`.`directory` `path`,
                    `c`.`dbName` `dbName`,
                    `c`.`titulaires` `titular`,
                    `c`.`email` ,
                    `c`.`enrollment_key`  `enrollmentKey` ,
                    `c`.`languageCourse` `language`,
                    `c`.`departmentUrl` `extLinkUrl`,
                    `c`.`departmentUrlName` `extLinkName`,
                    `c`.`visible` `visible`,
                    `cat`.`code` `categoryCode`,
                    `cat`.`name` `categoryName`,
                    `c`.`diskQuota` `diskQuota`
             FROM `" . $tbl_mdb_names['course'] . "` `c`
             LEFT JOIN `" . $tbl_mdb_names['category'] . "` `cat`
             ON `c`.`faculte` =  `cat`.`code`
             WHERE `c`.`code` = '" . addslashes($course_id) . "'";
            $_courseDatas = claro_sql_query_fetch_all($sql);
            if (!is_array($_courseDatas) || count($_courseDatas) == 0)
                return claro_failure::set_failure('course_not_found');
            ;
            $_courseDatas = $_courseDatas[0];
            $courseDataInCache = $course_id;
            $_courseDatas['visibility'  ]         = (bool) ($_courseDatas['visible'] == 2 || $_courseDatas['visible'] == 3);
            $_courseDatas['registrationAllowed']  = (bool) ($_courseDatas['visible'] == 1 || $_courseDatas['visible'] == 2);
            $_courseDatas['dbNameGlu'] = $courseTablePrefix . $_courseDatas['dbName'] . $dbGlu; // use in all queries
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


//////////////////////////////////////////////////////////////////////////////
//                    CLAROLINE FAILURE MANGEMENT
//////////////////////////////////////////////////////////////////////////////


$claro_failureList = array();

/**
 * collects and manage failures occuring during script execution
 * The main purpose is allowing to manage the display messages externaly
 * from functions or objects. This strengthens encapsulation principle
 *
 * Example :
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
 * Set if  the  access level switcher is aivailable
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
 * @param  $viewMode 'STUDENT' or 'COURSE_ADMIN'
 * @return true if set succeed.
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
 * Terminate the script and display message
 *
 * @param string message
 */

function claro_die($message)
{
    global $includePath, $clarolineRepositoryWeb, $claro_stylesheet, $rootWeb,
           $siteName, $text_dir, $_uid, $_cid, $administrator_name, $administrator_email,
           $is_platformAdmin, $_course, $_user, $_courseToolList, $coursesRepositoryWeb,
           $is_courseAllowed, $imgRepositoryWeb, $_tid;

    if ( ! headers_sent () )
    {
    // display header
        require $includePath . '/claro_init_header.inc.php';
    }

    echo '<table align="center">'
    .    '<tr><td>'
    .    claro_html::message_box($message)
    .    '</td></tr>'
    .    '</table>'
    ;

    require $includePath . '/claro_init_footer.inc.php' ;

    die(); // necessary to prevent any continuation of the application
}

/**
 * Checks if the string has been written html style (ie &eacute; etc)
 *
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param string $string
 * @return boolean true if the string is written in html style, false otherwise
 */

function is_htmlspecialcharized($string)
{
    return (bool) preg_match('/(&[a-z]+;)|(&#[0-9]+;)/', $string);
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
 * Parse the user text (e.g. stored in database)
 * before displaying it to the screen
 * For example it change new line charater to <br> tag etc.
 *
 * @param string $userText original user tex
 * @return string parsed user text
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 */

function claro_parse_user_text($userText)
{
   global $claro_texRendererUrl; // see 'inc/conf/claro_main.conf.php'

   if ( !empty($claro_texRendererUrl) )
   {
       $userText = str_replace('[tex]',
                          '<img src="'.$claro_texRendererUrl.'?',
                          $userText);

       $userText = str_replace('[/tex]',
                           '" border="0" align="absmiddle">',
                           $userText);
   }
   else
   {
       $userText = str_replace('[tex]',
                              '<embed TYPE="application/x-techexplorer" texdata="',
                              $userText);

       $userText = str_replace('[/tex]',
                               '" width="100%" pluginspace="http://www.integretechpub.com/">',
                               $userText);
   }

   $userText = make_clickable($userText);

   if ( strpos($userText, '<!-- content: html -->') === false )
   {
        // only if the content isn't HTML change new line to <br>
        // Note the '<!-- content: html -->' is introduced by HTML Area
        $userText = nl2br($userText);
   }

    return $userText;
}

/**
 * Completes url contained in the text with "<a href ...".
 * However the function simply returns the submitted text without any
 * transformation if it already contains some "<a href:" or "<img src=".
 * @param  string $text text to be converted
 * @return string   text after conversion
 *
 * Actually this function is taken from the PHP BB 1.4 script
 * - Goes through the given string, and replaces xxxx://yyyy with an HTML <a> tag linking
 *  to that URL
 * - Goes through the given string, and replaces www.xxxx.yyyy[zzzz] with an HTML <a> tag linking
 *  to http://www.xxxx.yyyy[/zzzz]
 * - Goes through the given string, and replaces xxxx@yyyy with an HTML mailto: tag linking
 *      to that email address
 * - Only matches these 2 patterns either after a space, or at the beginning of a line
 *
 * Notes: the email one might get annoying - it's easy to make it more restrictive, though.. maybe
 * have it require something like xxxx@yyyy.zzzz or such. We'll see.
 *
 * @author Rewritten by Nathan Codding - Feb 6, 2001.
 *         completed by Hugues Peeters - July 22, 2002
 */

function make_clickable($text)
{

    // If the user has decided to deeply use html and manage himself hyperlink
    // cancel the make clickable() function and return the text untouched. HP

    if (preg_match ( "<(a|img)[[:space:]]*(href|src)[[:space:]]*=(.*)>", $text) )
    {
        return $text;
    }

    // pad it with a space so we can match things at the start of the 1st line.
    $ret = " " . $text;


    // matches an "xxxx://yyyy" URL at the start of a line, or after a space.
    // xxxx can only be alpha characters.
    // yyyy is anything up to the first space, newline, or comma.

    $ret = preg_replace("#([\n ])([a-z]+?)://([^, \n\r]+)#i",
                        "\\1<a href=\"\\2://\\3\" >\\2://\\3</a>",
                        $ret);

    // matches a "www.xxxx.yyyy[/zzzz]" kinda lazy URL thing
    // Must contain at least 2 dots. xxxx contains either alphanum, or "-"
    // yyyy contains either alphanum, "-", or "."
    // zzzz is optional.. will contain everything up to the first space, newline, or comma.
    // This is slightly restrictive - it's not going to match stuff like "forums.foo.com"
    // This is to keep it from getting annoying and matching stuff that's not meant to be a link.

    $ret = preg_replace("#([\n ])www\.([a-z0-9\-]+)\.([a-z0-9\-.\~]+)((?:/[^, \n\r]*)?)#i",
                        "\\1<a href=\"http://www.\\2.\\3\\4\" >www.\\2.\\3\\4</a>",
                        $ret);

    // matches an email@domain type address at the start of a line, or after a space.
    // Note: before the @ sign, the only valid characters are the alphanums and "-", "_", or ".".
    // After the @ sign, we accept anything up to the first space, linebreak, or comma.

    $ret = preg_replace("#([\n ])([a-z0-9\-_.]+?)@([^, \n\r]+)#i",
                        "\\1<a href=\"mailto:\\2@\\3\">\\2@\\3</a>",
                        $ret);

    // Remove our padding..
    $ret = substr($ret, 1);

    return($ret);
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
 * Get names  of tools in an array where key are Claro_label
 * @return array list of localised name of tools
 * @todo with plugin, this lis would be read in a dynamic datasource
 */
function claro_get_tool_name_list()
{
    static $toolNameList;

    if( ! isset( $toolNameList ) )
    {
        $toolNameList = array('CLANN___' => get_lang('Announcement')
        ,                     'CLFRM___' => get_lang('Forums')
        ,                      'CLCAL___' => get_lang('Agenda')
        ,                      'CLCHT___' => get_lang('Chat')
        ,                      'CLDOC___' => get_lang('Documents and Links')
        ,                      'CLDSC___' => get_lang('Course description')
        ,                      'CLGRP___' => get_lang('Groups')
        ,                      'CLLNP___' => get_lang('Learning path')
        ,                      'CLQWZ___' => get_lang('Exercises')
        ,                      'CLWRK___' => get_lang('Work')
        ,                      'CLUSR___' => get_lang('Users')
        ,                      'CLWIKI__' => get_lang('Wiki')
        );
    }
    return $toolNameList;
}



/**
 * function that cleans php string for javascript
 *
 * This function is needed to clean strings used in javascript output
 * Newlines are prohibited in the script, specialchar  are prohibited
 * quotes must be addslashes
 *
 * @param $str string original string
 * @return string cleaned string
 *
 * @author Piraux Sébastien <pir@cerdecam.be>
 *
 */
function clean_str_for_javascript( $str )
{
    $output = $str;
    // 1. addslashes, prevent problems with quotes
    // must be before the str_replace to avoid double backslash for \n
    $output = addslashes($output);
    // 2. turn windows CR into *nix CR
    $output = str_replace("\r", '', $output);
    // 3. replace "\n" by uninterpreted '\n'
    $output = str_replace("\n",'\n', $output);
    // 4. convert special chars into html entities
    $output = htmlspecialchars($output);

    return $output;
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

        $sql ="SELECT ctl.id                       AS id,
                      pct.claro_label              AS label,
                      ctl.script_name              AS name,
                      ctl.access                   AS access,
                      IFNULL(pct.icon,'tool.gif')  AS icon,
                      pct.access_manager           AS access_manager,
                      ISNULL(ctl.tool_id)           AS external,

                      IFNULL( ctl.script_url ,
                              CONCAT('".$clarolineRepositoryWeb."', pct.script_url) )
                      AS url

               FROM `". $tbl_course_tool_list ."` ctl

               LEFT JOIN `" . $tbl_tool_list . "` pct
                      ON  pct.id = ctl.tool_id

               WHERE ctl.access IN (\"".implode("\", \"", $accessList)."\")
               ORDER BY external, ctl.rank";

        $courseToolList = claro_sql_query_fetch_all($sql);

        /*
         * Complete the list with the appropriate tool names
         */

        $toolNameList = claro_get_tool_name_list();

        foreach ($courseToolList as $thisToolKey => $thisToolAttributeList)
        {
            if ( trim($thisToolAttributeList['name']) == '')
            {
                if ( ! empty ($thisToolAttributeList['label'] ) )
                {
                    $courseToolList[$thisToolKey]['name'] = $toolNameList[$thisToolAttributeList['label']];
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

# For PHP backward compatibility

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

?>