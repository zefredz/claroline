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
//////////////////////////////////////////////////////////////////////////////
//                   CLAROLINE DB    QUERY WRAPPRER MODULE
//////////////////////////////////////////////////////////////////////////////

/**
 * Get list of table names for central table.
 * @return array list of the central claroline database tables
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 */

function claro_sql_get_main_tbl()
{
    global $mainDbName, $statsDbName, $mainTblPrefix, $statsTblPrefix;
    static $mainTblList = array();

    if ( count($mainTblList) == 0 )
    {
        $mainTblList= array (
        'config_property'           => $mainDbName . '`.`' . $mainTblPrefix . 'config_property',
        'config_file'               => $mainDbName . '`.`' . $mainTblPrefix . 'config_file',
        'admin'                     => $mainDbName . '`.`' . $mainTblPrefix . 'admin',
        'course'                    => $mainDbName . '`.`' . $mainTblPrefix . 'cours',
        'rel_course_user'           => $mainDbName . '`.`' . $mainTblPrefix . 'cours_user',
        'category'                  => $mainDbName . '`.`' . $mainTblPrefix . 'faculte',
        'user'                      => $mainDbName . '`.`' . $mainTblPrefix . 'user',
        'tool'                      => $mainDbName . '`.`' . $mainTblPrefix . 'course_tool',
        'user_category'             => $mainDbName . '`.`' . $mainTblPrefix . 'class',
        'user_rel_profile_category' => $mainDbName . '`.`' . $mainTblPrefix . 'rel_class_user',
        'class'                     => $mainDbName . '`.`' . $mainTblPrefix . 'class',
        'rel_class_user'            => $mainDbName . '`.`' . $mainTblPrefix . 'rel_class_user',
        'sso'                       => $mainDbName . '`.`' . $mainTblPrefix . 'sso',
        'notify'                    => $mainDbName . '`.`' . $mainTblPrefix . 'notify',
        'upgrade_status'            => $mainDbName . '`.`' . $mainTblPrefix . 'upgrade_status',
        'track_e_default'           => $statsDbName . '`.`' . $statsTblPrefix . 'track_e_default',
        'track_e_login'             => $statsDbName . '`.`' . $statsTblPrefix . 'track_e_login',
        'track_e_open'              => $statsDbName . '`.`' . $statsTblPrefix . 'track_e_open'
        );
    }

    return $mainTblList;
}

/**
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 * @param  string $dbNameGlued (optionnal) course database with its platform
 *         glue already append. If no db name are set, the current course db
 *         will be taken.
 * @return array list of the current course database tables
 */

function claro_sql_get_course_tbl($dbNameGlued = null)
{
    global $_course;
    static $courseTblList = array();
    static $courseDbInCache = null;

    if ( is_null($dbNameGlued) )
    {

        $forceTableSet   = (bool) ( $courseDbInCache != $_course['dbNameGlu'] );
        $courseDbInCache = $_course['dbNameGlu'];
    }
    else
    {

        $forceTableSet   = (bool) ( $courseDbInCache != $dbNameGlued );
        $courseDbInCache = $dbNameGlued;
    }

    if ( count($courseTblList) == 0 || $forceTableSet )
    {

        $courseTblList = array(

              'announcement'           => $courseDbInCache . 'announcement',
              'bb_categories'          => $courseDbInCache . 'bb_categories',
              'bb_forums'              => $courseDbInCache . 'bb_forums',
              'bb_posts'               => $courseDbInCache . 'bb_posts',
              'bb_posts_text'          => $courseDbInCache . 'bb_posts_text',
              'bb_priv_msgs'           => $courseDbInCache . 'bb_priv_msgs',
              'bb_rel_topic_userstonotify'
                            => $courseDbInCache . 'bb_rel_topic_userstonotify',
              'bb_topics'              => $courseDbInCache . 'bb_topics',
              'bb_users'               => $courseDbInCache . 'bb_users',
              'bb_whosonline'          => $courseDbInCache . 'bb_whosonline',

              'calendar_event'         => $courseDbInCache . 'calendar_event',
              'course_description'     => $courseDbInCache . 'course_description',
              'document'               => $courseDbInCache . 'document',
              'group_property'         => $courseDbInCache . 'group_property',
              'group_rel_team_user'    => $courseDbInCache . 'group_rel_team_user',
              'group_team'             => $courseDbInCache . 'group_team',
              'lp_learnPath'           => $courseDbInCache . 'lp_learnPath',
              'lp_rel_learnPath_module'=> $courseDbInCache . 'lp_rel_learnPath_module',
              'lp_user_module_progress'=> $courseDbInCache . 'lp_user_module_progress',
              'lp_module'              => $courseDbInCache . 'lp_module',
              'lp_asset'               => $courseDbInCache . 'lp_asset',
              'quiz_answer'            => $courseDbInCache . 'quiz_answer',
              'quiz_question'          => $courseDbInCache . 'quiz_question',
              'quiz_rel_test_question' => $courseDbInCache . 'quiz_rel_test_question',
              'quiz_test'              => $courseDbInCache . 'quiz_test' ,
              'tool_intro'             => $courseDbInCache . 'tool_intro',
              'tool'                   => $courseDbInCache . 'tool_list',
              'track_e_access'         => $courseDbInCache . 'track_e_access',
              'track_e_downloads'      => $courseDbInCache . 'track_e_downloads',
              'track_e_exe_details'    => $courseDbInCache . 'track_e_exe_details',
              'track_e_exe_answers'    => $courseDbInCache . 'track_e_exe_answers',
              'track_e_exercices'      => $courseDbInCache . 'track_e_exercices',
              'track_e_uploads'        => $courseDbInCache . 'track_e_uploads',
              'userinfo_content'       => $courseDbInCache . 'userinfo_content',
              'userinfo_def'           => $courseDbInCache . 'userinfo_def',
              'wrk_assignment'         => $courseDbInCache . 'wrk_assignment',
              'wrk_submission'         => $courseDbInCache . 'wrk_submission',
              'links'                  => $courseDbInCache . 'lnk_links',
              'resources'              => $courseDbInCache . 'lnk_resources',
              'wiki_properties'        => $courseDbInCache . 'wiki_properties',
              'wiki_pages'             => $courseDbInCache . 'wiki_pages',
              'wiki_pages_content'     => $courseDbInCache . 'wiki_pages_content',
              'wiki_acls'              => $courseDbInCache . 'wiki_acls'
              ); // end array

    } // end if ( count($course_tbl) == 0 )

    return $courseTblList;
}

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

/**
 * Claroline mySQL query wrapper. It also provides a debug display which works
 * when the CLARO_DEBUG_MODE constant flag is set to on (true)
 *
 * @author Hugues Peeters    <peeters@ipm.ucl.ac.be>,
 * @author Christophe Gesché <moosh@claroline.net>
 * @param  string  $sqlQuery   - the sql query
 * @param  handler $dbHandler  - optional
 * @return handler             - the result handler
 */

function claro_sql_query($sqlQuery, $dbHandler = '#' )
{

    if ( $dbHandler == '#')
    {
        $resultHandler =  @mysql_query($sqlQuery);
    }
    else
    {
        $resultHandler =  @mysql_query($sqlQuery, $dbHandler);
    }

    if ( defined('CLARO_DEBUG_MODE') && CLARO_DEBUG_MODE && mysql_errno() )
    {
                echo '<hr size="1" noshade>'
                     .mysql_errno(), " : ", mysql_error(), '<br>'
                     .'<pre style="color:red">'
                     .$sqlQuery
                     .'</pre>'
                     .'<hr size="1" noshade>';
    }

    return $resultHandler;
}

/**
 * Get the name of the specified fields in a query result
 *
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param string $sq - SQL query
 * @param ressource (optional) - result pointer
 * @return  names of the specified field index
 */

function claro_sql_field_names( $sql, $resultPt = null )
{
    static $_colNameList = array();

    $sqlHash = md5($sql);

    if ( ! array_key_exists( $sqlHash, $_colNameList) )
    {
        if ( ! is_resource($resultPt) || get_resource_type($resultPt) != 'Unknown' )
        {
            $resultPt     = claro_sql_query($sql);
            $releasablePt = true;
        }
        else
        {
            $releasablePt = false;
        }

        $resultFieldCount = mysql_num_fields($resultPt);

        for ( $i = 0; $i < $resultFieldCount ; ++$i )
        {
            $_colNameList[$sqlHash][] = mysql_field_name($resultPt, $i);
        }

        if ( $releasablePt ) mysql_free_result($resultPt);
    }

    return $_colNameList[$sqlHash];
}

/**
 * Claroline SQL query and fetch array wrapper. It returns all the result rows
 * in an associative array.
 *
 * @param  string  $sqlQuery the sql query
 * @param  handler $dbHandler optional
 * @return array associative array containing all the result rows
 * @author Hugues Peeters <hugues.peeters@claroline.net>,
 */

function claro_sql_query_fetch_all($sqlQuery, $dbHandler = '#')
{
    $result = claro_sql_query($sqlQuery, $dbHandler);

    if ($result)
    {
        $rowList = array();

        while( $row = mysql_fetch_array($result, MYSQL_ASSOC) )
        {
            $rowList [] = $row;
        }

        if ( count($rowList) == 0 )
        {
            // If there is no result at all, anticipate that the user could ask
            // for field name at least. It is more efficient to call the
            // function now as we still hold the result pointer. The field names
            // will be statically cached into the claro_sql_field_names() funtion.

            claro_sql_field_names($sqlQuery, $result);
        }

        mysql_free_result($result);

        return $rowList;
    }
    else
    {
        return false;
    }
}

/**
 * Claroline SQL query and fetch array wrapper. It returns all the result in
 * associative array ARRANGED BY COLUMNS.
 *
 * @param  string  $sqlQuery  the sql query
 * @param  handler $dbHandler optional
 * @return associative array containing all the result arranged by columns
 *
 * @see    claro_sql_query()
 * @author Hugues Peeters <hugues.peeters@claroline.net>,
 *
 */

function claro_sql_query_fetch_all_cols($sqlQuery, $dbHandler = '#')
{
    $result = claro_sql_query($sqlQuery, $dbHandler);

    if ($result)
    {
        $colList = array();

        while( $row = mysql_fetch_array($result, MYSQL_ASSOC) )
        {
            foreach($row as $key => $value ) $colList[$key][] = $value;
        }

        if( count($colList) == 0 )
        {
            // WHEN NO RESULT, THE SCRIPT CREATES AT LEAST COLUMN HEADERS

            $FieldNamelist = claro_sql_field_names($sqlQuery, $result);

            foreach($FieldNamelist as $thisFieldName)
            {
                $colList[$thisFieldName] = array();
            }
        } // end if( count($colList) == 0)

        mysql_free_result($result);

        return $colList;

    }
    else
    {
        return false;
    }
}


/**
 * Claroline SQL query wrapper returning only a single result value.
 * Useful in some cases because, it avoid nested arrays of results.
 *
 * @param  string  $sqlQuery  the sql query
 * @param  handler $dbHandler optional
 * @return associative array containing all the result rows
 * @since  1.5.1
 * @see    claro_sql_query()
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>,
 */


function claro_sql_query_get_single_value($sqlQuery, $dbHandler = '#')
{
    $result = claro_sql_query($sqlQuery, $dbHandler);

    if($result)
    {
        list($value) = mysql_fetch_row($result);
        mysql_free_result($result);
        return $value;
    }
    else
    {
        return false;
    }
}

/**
 * Claroline SQL query wrapper returning only the first row of the result
 * Useful in some cases because, it avoid nested arrays of results.
 *
 * @param  string  $sqlQuery  the sql query
 * @param  handler $dbHandler optional
 * @return associative array containing all the result rows
 * @since  1.5.1
 * @see    claro_sql_query()
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>,
 */


function claro_sql_query_get_single_row($sqlQuery, $dbHandler = '#')
{
    $result = claro_sql_query($sqlQuery, $dbHandler);

    if($result)
    {
        $row = mysql_fetch_array($result, MYSQL_ASSOC);
        mysql_free_result($result);
        return $row;
    }
    else
    {
        return false;
    }
}



/**
 * Claroline SQL query wrapper returning the number of rows affected by the
 * query
 *
 * @param  string  $sqlQuery  the sql query
 * @param  handler $dbHandler optional
 * @return int                the number of rows affected by the query
 *
 * @see    claro_sql_query()
 * @author Hugues Peeters <hugues.peeters@claroline.net>,
 *
 */


function claro_sql_query_affected_rows($sqlQuery, $dbHandler = '#')
{
    $result = claro_sql_query($sqlQuery, $dbHandler);

    if ($result)
    {
        if ($dbHandler == '#') return mysql_affected_rows();
        else                   return mysql_affected_rows($dbHandler);
    }
    else
    {
        return false;
    }
}

/**
 * Claroline mySQL query wrapper returning the last id generated by the last
 * inserted row
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>,
 * @param  string  $sqlQuery  the sql query
 * @param  handler $dbHandler optional
 * @return integer the id generated by the previous insert query
 *
 * @see    claro_sql_query()
 *
 */

function claro_sql_query_insert_id($sqlQuery, $dbHandler = '#')
{
    $result = claro_sql_query($sqlQuery, $dbHandler);

    if ($result)
    {
        if ($dbHandler == '#') return mysql_insert_id();
        else                   return mysql_insert_id($dbHandler);
    }
    else
    {
        return false;
    }
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

//////////////////////////////////////////////////////////////////////////////
//                              DISPLAY OPTIONS
//                            student    view, title, ...
//////////////////////////////////////////////////////////////////////////////


/**
 * Displays the title of a tool. Optionally, there can be a subtitle below
 * the normal title, and / or a supra title above the normal title.
 *
 * e.g. supra title:
 * group
 * GROUP PROPERTIES
 *
 * e.g. subtitle:
 * AGENDA
 * calender & events tool
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 * @param  mixed $titleElement - it could either be a string or an array
 *                               containing 'supraTitle', 'mainTitle',
 *                               'subTitle'
 * @return void
 */

function claro_disp_tool_title($titlePart, $helpUrl = false)
{
    // if titleElement is simply a string transform it into an array

    if ( is_array($titlePart) )
    {
        $titleElement = $titlePart;
    }
    else
    {
        $titleElement['mainTitle'] = $titlePart;
    }


    $string = "\n" . '<h3 class="claroToolTitle">' . "\n";

    if ($helpUrl)
    {
        global $clarolineRepositoryWeb, $imgRepositoryWeb;

    $string .= "<a href='#' onClick=\"MyWindow=window.open('". $clarolineRepositoryWeb . "help/" .$helpUrl
            ."','MyWindow','toolbar=no,location=no,directories=no,status=yes,menubar=no,scrollbars=yes,resizable=yes,width=350,height=450,left=300,top=10'); return false;\">"

            .'<img src="'.$imgRepositoryWeb.'/help.gif" '
            .' alt ="'.get_lang('Help').'"'
            .' align="right"'
            .' hspace="30">'
            .'</a>' . "\n"
            ;
    }


    if ( isset($titleElement['supraTitle']) )
    {
        $string .= '<small>' . $titleElement['supraTitle'] . '</small><br />' . "\n";
    }

    if ( isset($titleElement['mainTitle']) )
    {
        $string .= $titleElement['mainTitle'] . "\n";
    }

    if ( isset($titleElement['subTitle']) )
    {
        $string .= '<br /><small>' . $titleElement['subTitle'] . '</small>' . "\n";
    }

    $string .= '</h3>'."\n\n";

    return $string;
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
    global $clarolineRepositoryWeb, $is_courseAdmin;

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
    else                                         $url = $_SERVER['PHP_SELF'].'?';

    /*
     * remove previous view mode request from the url
     */

    $url = str_replace('&viewMode=STUDENT'     , '', $url);
    $url = str_replace('&viewMode=COURSE_ADMIN', '', $url);

    /*------------------------------------------------------------------------
                            INIT BUTTONS
      -------------------------------------------------------------------------*/


    switch ($currentViewMode)
    {
        case 'COURSE_ADMIN' :

            $studentButton     = '<a href="' . $url . '&amp;viewMode=STUDENT">'
                               . get_lang('Student')
                               . '</a>'
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

    echo get_lang('View mode') . ' : '
    .    $studentButton
    .    ' | '
    .    $courseAdminButton
    ;

    return true;
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
    Display    list of    messages

    @param $msgArrBody array of messages
    @author Christophe Gesché <moosh@claroline.net>
    @version 1.0

    Example    code for using this    in your    tools:
    $msgArrBody["nameOfCssClass"][]="foo";
.    css    class can be defined in    script but try to use
    class from    generic    css    ()
    error success warning
    ...
*/

function claro_disp_msg_arr($msgArrBody, $return=true)
{
    $msgBox = '';
    if (is_array($msgArrBody) && count($msgArrBody) > 0)
    {
        foreach ($msgArrBody as $classMsg => $thisMsgArr)
        {
            if( is_array($thisMsgArr) && count($thisMsgArr) > 0 )
            {
                $msgBox .= '<div class="' . $classMsg . '">';
                foreach ($thisMsgArr as $anotherThis) $msgBox .= '<div class="msgLine" >' . $anotherThis . '</div>';
                $msgBox .= '</div>';
            }
        }
        if($return) return claro_disp_message_box($msgBox);
        else        echo   claro_disp_message_box($msgBox);
    }
}


/**
 * Route the script to an auhtentication form if user id is missing.
 * Once authenticated, the system get back to the source where the form
 * was trigged
 *
 * @param boolean $cidRequired - if the course id is required to leave the form
 * @author Christophe gesché <moosh@claroline.net>
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 */

function claro_disp_auth_form($cidRequired = false)
{
    global $rootWeb, $includePath, $_cid;

    $sourceUrl = ( isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on' 
                   ? 'https://' 
                   : 'http://')
               .  $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];

    // note : some people say that REQUEST_URI isn't available on IIS.
    // It has to be checked  ...

    if ( ! headers_sent () )
    {
        $urlCmd = ($cidRequired && ! $_cid ? '&cidRequired=true' : '');
        header('Location:' . $rootWeb . 'claroline/auth/login.php?sourceUrl=' . urlencode($sourceUrl) . $urlCmd );
    }
    else // HTTP header has already been sent - impossible to relocate
    {
        echo '<p align="center">'
        .    'WARNING ! Login Required <br />'
        .    'Click '
        .    '<a href="' . $rootWeb . 'claroline/auth/login.php'
        .    '?sourceUrl=' . urlencode($sourceUrl) . '">'
        .    'here'
        .    '</a>'
        .    '</p>'
        ;

        require $includePath . '/claro_init_footer.inc.php';
    }

    die(); // necessary to prevent any continuation of the application
}

/**
 * Prepare display of the message box appearing on the top of the window,
 * just    below the tool title. It is recommended to use this function
 * to display any confirmation or error messages, or to ask to the user
 * to enter simple parameters.
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 * @param string $message - include your self any additionnal html
 *                          tag if you need them
 * @return $string - the
 */

function claro_disp_message_box($message)
{
    return "\n".'<table class="claroMessageBox" border="0" cellspacing="0" cellpadding="10">'
    .      '<tr>'
    .      '<td>'
    .      $message
    .      '</td>'
    .      '</tr>'
    .      '</table>' . "\n\n"
    ;
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
    .    claro_disp_message_box($message)
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
 * Allows to easily display a breadcrumb trail
 *
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param array $nameList bame of each breadcrumb
 * @param array $urlList url corresponding to the breadcrumb name above
 * @param string $separator (optionnal) element which segregate the breadcrumbs
 * @param string $homeImg (optionnal) source url for a home icon at the trail start
 * @return string the build breadcrumb trail
 */

function claro_disp_breadcrumbtrail($nameList, $urlList, $separator = ' &gt; ', $homeImg = null)
{
    // trail of only one element has no sense ...
    if (count ($nameList) < 2 ) return '<div class="breadcrumbTrail">&nbsp;</div>';

    $breadCrumbList = array();

    foreach($nameList as $thisKey => $thisName)
    {
        if (   array_key_exists($thisKey, $urlList)
            && ! is_null($urlList[$thisKey])       )
        {
            $startAnchorTag = '<a href="'.$urlList[$thisKey].'" target="_top">';
            $endAnchorTag   = '</a>';
        }
        else
        {
            $startAnchorTag = '';
            $endAnchorTag   = '';
        }

        $htmlizedName = is_htmlspecialcharized($thisName)
                        ? $thisName
                        : htmlspecialchars($thisName);

        $breadCrumbList [] = $startAnchorTag
                           . $htmlizedName
                           . $endAnchorTag;
    }

    // Embed the last bread crumb entry of the list.

    $breadCrumbList[count($breadCrumbList)-1] = '<strong>'
                                               .end($breadCrumbList)
                                               .'</strong>';

    return  '<div class="breadcrumbTrail">'
          . ( is_null($homeImg) ? '' : '<img src="' . $homeImg . '" alt=""> ' )
          . implode($separator, $breadCrumbList)
          . '</div>';
}

/**
 * Prepare the display of a clikcable button
 *
 * This function is needed because claroline buttons rely on javascript.
 * The function return an optionnal behavior fo browser where javascript
 * isn't  available.
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 *
 * @param string $url url inserted into the 'href' part of the tag
 * @param string $text text inserted between the two <a>...</a> tags (note : it
 *        could also be an image ...)
 * @param string $confirmMessage (optionnal) introduce a javascript confirmation popup
 * @return string the button
 */

function claro_disp_button($url, $text, $confirmMessage = '')
{

    if (   claro_is_javascript_enabled()
        && ! preg_match('~^Mozilla/4\.[1234567]~', $_SERVER['HTTP_USER_AGENT']))
    {
        if ($confirmMessage != '')
        {
            $onClickCommand = "if(confirm('" . clean_str_for_javascript($confirmMessage) . "')){document.location='" . $url . "';return false}";
        }
        else
        {
            $onClickCommand = "document.location='".$url."';return false";
        }

        return '<button class="claroButton" onclick="' . $onClickCommand . '">'
        .      $text
        .      '</button>&nbsp;' . "\n"
        ;
    }
    else
    {
        return '<nobr>[ <a href="' . $url . '">' . $text . '</a> ]</nobr>';
    }
}

/**
 * Function used to draw a progression bar
 *
 * @author Piraux Sébastien <pir@cerdecam.be>
 *
 * @param integer $progress progression in pourcent
 * @param integer $factor will be multiply by 100 to have the full size of the bar
 * (i.e. 1 will give a 100 pixel wide bar)
 */

function claro_disp_progress_bar ($progress, $factor)
{
    global $clarolineRepositoryWeb, $imgRepositoryWeb;
           $maxSize  = $factor * 100; //pixels
           $barwidth = $factor * $progress ;

    // display progress bar
    // origin of the bar
    $progressBar = '<img src="' . $imgRepositoryWeb . 'bar_1.gif" width="1" height="12" alt="">';

    if($progress != 0)
            $progressBar .= '<img src="' . $imgRepositoryWeb . 'bar_1u.gif" width="' . $barwidth . '" height="12" alt="">';
    // display 100% bar

    if($progress!= 100 && $progress != 0)
            $progressBar .= '<img src="' . $imgRepositoryWeb . 'bar_1m.gif" width="1" height="12" alt="">';

    if($progress != 100)
            $progressBar .= '<img src="' . $imgRepositoryWeb . 'bar_1r.gif" width="' . ($maxSize - $barwidth) . '" height="12" alt="">';
    // end of the bar
    $progressBar .=  '<img src="' . $imgRepositoryWeb . 'bar_1.gif" width="1" height="12" alt="">';

    return $progressBar;
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

/**
 * Display a date at localized format
 * @author Christophe Gesché <gesche@ipm.ucl.ac.be>
 * @param formatOfDate
         see http://www.php.net/manual/en/function.strftime.php
         for syntax to use for this string
         I suggest to use the format you can find in trad4all.inc.php files
 * @param timestamp timestamp of date to format
 */

function claro_disp_localised_date($formatOfDate,$timestamp = -1) //PMAInspiration :)
{
    global $langMonthNames, $langDay_of_weekNames;

    if ($timestamp == -1) $timestamp = claro_time();

    // avec un ereg on fait nous même le replace des jours et des mois
    // with the ereg  we  replace %aAbB of date format
    //(they can be done by the system when  locale date aren't aivailable

    $formatOfDate = ereg_replace('%[A]', $langDay_of_weekNames['long'][(int)strftime('%w', $timestamp)], $formatOfDate);
    $formatOfDate = ereg_replace('%[a]', $langDay_of_weekNames['short'][(int)strftime('%w', $timestamp)], $formatOfDate);
    $formatOfDate = ereg_replace('%[B]', $langMonthNames['long'][(int)strftime('%m', $timestamp)-1], $formatOfDate);
    $formatOfDate = ereg_replace('%[b]', $langMonthNames['short'][(int)strftime('%m', $timestamp)-1], $formatOfDate);
    return strftime($formatOfDate, $timestamp);
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
 * Insert a Wysiwyg editor inside a form instead of a textarea
 * A standard textarea is displayed if the Wysiwyg editor is disabled or if
 * the user's browser have no activated javascript support
 *
 * @param string $name content for name attribute in textarea tag
 * @param string $content optional content previously inserted into    the    area
 * @param int     $rows optional    textarea rows
 * @param int    $cols optional    textarea columns
 * @param string $optAttrib    optional - additionnal tag attributes
 *                                       (wrap, class, ...)
 * @return string html output for standard textarea or Wysiwyg editor
 *
 * @global string rootWeb from claro_main.conf.php
 * @global string rootSys from claro_main.conf.php
 * @global string langTextEditorDisable from lang file
 * @global string langTextEditorEnable from lang file
 * @global string langSwitchEditorToTextConfirm from lang file
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 * @author Sébastien Piraux <pir@cerdecam.be>
 */

function claro_disp_textarea_editor($name, $content = '',
                                  $rows=20,    $cols=80,
                                  $optAttrib='')
{
    global $rootWeb, $rootSys;
    global $claro_editor;

    if( !isset($claro_editor) ) $claro_editor = 'tiny_mce';

     $returnString = '';

    // default value of htmlEditor
    if( !isset($_SESSION['htmlEditor']) ) $_SESSION['htmlEditor'] = 'enabled';

    // get content if in url
    if( isset($_REQUEST['areaContent']) ) $content = stripslashes($_REQUEST['areaContent']);

    // $claro_editor is the directory name of the editor
    $incPath = $rootSys . 'claroline/editor/'.$claro_editor;
    $editorPath = $rootWeb . 'claroline/editor/';
    $webPath = $editorPath . $claro_editor;

    if( file_exists($incPath . '/editor.class.php') )
    {
        // include editor class
        include_once $incPath . '/editor.class.php';

        // editor instance
        $editor = new editor($name,$content,$rows,$cols,$optAttrib,$webPath);

        if (claro_is_javascript_enabled())
        {
            if ( isset($_SESSION['htmlEditor']) && $_SESSION['htmlEditor'] != 'disabled' )
            {
                $switchState = 'off';
                $message     = get_lang('Disable text editor');
                $confirmCommand = "if(!confirm('".clean_str_for_javascript(get_lang('SwitchEditorToTextConfirm'))."'))return(false);";
            }
            else
            {
                $switchState = 'on';
                $message     = get_lang('Enable text editor');
                $confirmCommand = '';
            }

            $location = '\''
            .           $editorPath.'/editorswitcher.php?'
            .           'switch='.$switchState
            .           '&sourceUrl=' . urlencode($_SERVER['REQUEST_URI'])
            .           '&areaContent='
            .           '\''
            .           '+escape(document.getElementById(\''.$name.'\').value)'
            ;
            // use REQUEST_URI in href to avoid an ugly error if there is a javascript error in onclick
            $returnString .=
            "\n".'<div align="right">'
            .    '<small>'
            .    '<b>'
            .    '<a href="'.$_SERVER['REQUEST_URI'].'" '
            .     'onClick ="' . $confirmCommand . 'window.location='
            .    $location . ';return(false);">'
            .    $message
            .    '</a>'
            .    '</b>'
            .    '</small>'
            .    '</div>'."\n"
            ;
        }

        if( isset($_SESSION['htmlEditor']) && $_SESSION['htmlEditor'] != 'disabled' )
        {
            $returnString .= $editor->getAdvancedEditor();
        }
        else
        {
            // get standard text area
               $returnString .=
                '<textarea '
                .'id="'.$name.'" '
                .'name="'.$name.'" '
                .'style="width:100%" '
                .'rows="'.$rows.'" '
                .'cols="'.$cols.'" '
                .$optAttrib.' >'
                ."\n".$content."\n"
                .'</textarea>'."\n";
        }
    }
    else
    {
        // if the editor class doesn't exists we cannot rely on it to display
        // the standard textarea
        $returnString .=
            '<textarea '
            .'id="'.$name.'" '
            .'name="'.$name.'" '
            .'style="width:100%" '
            .'rows="'.$rows.'" '
            .'cols="'.$cols.'" '
            .$optAttrib.' >'
            ."\n".$content."\n"
            .'</textarea>'."\n";
    }

    return $returnString;
}

function claro_disp_html_area($name, $content = '',
                              $rows=20, $cols=80,
                              $optAttrib='')
{
    // becomes a alias while the function call is not replaced by the new one
    return claro_disp_textarea_editor($name,$content,$rows,$cols,$optAttrib);
}


/**
 * function claro_build_nested_select_menu($name, $elementList)
 * Build in a relevant way 'select' menu for an HTML form containing nested data
 *
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 *
 * @param string $name, name of the select tag
 *
 * @param array nested data in a composite way
 *
 *  Exemple :
 *
 *  $elementList[1]['name'    ] = 'level1';
 *  $elementList[1]['value'   ] = 'level1';
 *
 *  $elementList[1]['children'][1]['name' ] = 'level2';
 *  $elementList[1]['children'][1]['value'] = 'level2';
 *
 *  $elementList[1]['children'][2]['name' ] = 'level2';
 *  $elementList[1]['children'][2]['value'] = 'level2';
 *
 *  $elementList[2]['name' ]  = 'level1';
 *  $elementList[2]['value']  = 'level1';
 *
 * @return string the HTML flow
 * @desc depends on prepare option tags
 *
 */

function claro_build_nested_select_menu($name, $elementList)
{
    return '<select name="' . $name . '">' . "\n"
    .      implode("\n", prepare_option_tags($elementList) )
    .      '</select>' .  "\n"
    ;
}

/**
 * prepare the 'option' html tag for the claro_disp_nested_select_menu()
 * fucntion
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 * @param array $elementList
 * @param int  $deepness (optionnal, default is 0)
 * @return array of option tag list
 */


function prepare_option_tags($elementList, $deepness = 0)
{
    foreach($elementList as $thisElement)
    {
        $tab = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $deepness);

        $optionTagList[] = '<option value="'.$thisElement['value'].'">'
        .                  $tab.$thisElement['name']
        .                  '</option>'
        ;
        if (   isset( $thisElement['children'] )
            && sizeof($thisElement['children'] ) > 0)
        {
            $optionTagList = array_merge( $optionTagList,
                                          prepare_option_tags($thisElement['children'],
                                                              $deepness + 1 ) );
        }
    }

    return  $optionTagList;
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

?>