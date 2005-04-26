<?php # $Id$
/**
 * CLAROLINE 
 *
 * @version 1.6
 * 
 * @copyright (c) 2001, 2005 Universite catholique de Louvain (UCL)     
 * 
 * @license GPL
 * This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
 * as published by the FREE SOFTWARE FOUNDATION. The GPL is available
 * through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
 * 
 * @author see 'credits' file
 * 
 */
//////////////////////////////////////////////////////////////////////////////
//                   CLAROLINE DB    QUERY WRAPPRER MODULE
//////////////////////////////////////////////////////////////////////////////

/**
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 * @return array list of the central claroline database    tables
 */

function claro_sql_get_main_tbl()
{
    global $mainDbName,$statsDbName, $mainTblPrefix, $statsTblPrefix;
    static $mainTblList = array();

    if ( count($mainTblList) == 0 )
    {
        $mainTblList= array (
        'config_property'           => $mainDbName.'`.`'.$mainTblPrefix.'config_property',
        'config_file'               => $mainDbName.'`.`'.$mainTblPrefix.'config_file',
        'admin'                     => $mainDbName.'`.`'.$mainTblPrefix.'admin',
        'course'                    => $mainDbName.'`.`'.$mainTblPrefix.'cours',
        'rel_course_user'           => $mainDbName.'`.`'.$mainTblPrefix.'cours_user',
        'category'                  => $mainDbName.'`.`'.$mainTblPrefix.'faculte',
        'user'                      => $mainDbName.'`.`'.$mainTblPrefix.'user',
        'tool'                      => $mainDbName.'`.`'.$mainTblPrefix.'course_tool',
        'user_category'             => $mainDbName.'`.`'.$mainTblPrefix.'class',
        'user_rel_profile_category' => $mainDbName.'`.`'.$mainTblPrefix.'rel_class_user',
        'class'                     => $mainDbName.'`.`'.$mainTblPrefix.'class',
        'rel_class_user'            => $mainDbName.'`.`'.$mainTblPrefix.'rel_class_user',
        'sso'                       => $mainDbName.'`.`'.$mainTblPrefix.'sso',
        'track_e_default'           => $statsDbName.'`.`'.$statsTblPrefix.'track_e_default',
        'track_e_login'             => $statsDbName.'`.`'.$statsTblPrefix.'track_e_login',
        'track_e_open'              => $statsDbName.'`.`'.$statsTblPrefix.'track_e_open',
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
    static $courseDb      = null;

    if ( is_null($dbNameGlued) )
    { 
        $forceTableSet = (bool) ( $courseDb != $_course['dbNameGlu'] );
        $courseDb      = $_course['dbNameGlu'];
    }
    else
    {
        $forceTableSet = (bool) ( $courseDb != $dbNameGlued );
        $courseDb      = $dbNameGlued;
    }

    if ( count($courseTblList) == 0 || $forceTableSet )
    {
        $courseTblList = array(

              'announcement'           => $courseDb.'announcement',
              'bb_categories'          => $courseDb.'bb_categories',
              'bb_forums'              => $courseDb.'bb_forums',
              'bb_posts'               => $courseDb.'bb_posts',
              'bb_posts_text'          => $courseDb.'bb_posts_text',
              'bb_priv_msgs'           => $courseDb.'bb_priv_msgs',
              'bb_rel_topic_userstonotify'
                            => $courseDb.'bb_rel_topic_userstonotify',
              'bb_topics'              => $courseDb.'bb_topics',
              'bb_users'               => $courseDb.'bb_users',
              'bb_whosonline'          => $courseDb.'bb_whosonline',

              'calendar_event'         => $courseDb.'calendar_event',
              'course_description'     => $courseDb.'course_description',
              'document'               => $courseDb.'document',
              'group_property'         => $courseDb.'group_property',
              'group_rel_team_user'    => $courseDb.'group_rel_team_user',
              'group_team'             => $courseDb.'group_team',
              'lp_learnPath'           => $courseDb.'lp_learnPath',
              'lp_rel_learnPath_module'=> $courseDb.'lp_rel_learnPath_module',
              'lp_user_module_progress'=> $courseDb.'lp_user_module_progress',
              'lp_module'              => $courseDb.'lp_module',
              'lp_asset'               => $courseDb.'lp_asset',
              'quiz_answer'            => $courseDb.'quiz_answer',
              'quiz_question'          => $courseDb.'quiz_question',
              'quiz_rel_test_question' => $courseDb.'quiz_rel_test_question',
              'quiz_test'              => $courseDb.'quiz_test' ,
              'tool_intro'             => $courseDb.'tool_intro',
              'tool'                   => $courseDb.'tool_list',
              'track_e_access'         => $courseDb.'track_e_access',
              'track_e_downloads'      => $courseDb.'track_e_downloads',
              'track_e_exercices'      => $courseDb.'track_e_exercices',
              'track_e_uploads'        => $courseDb.'track_e_uploads',
              'userinfo_content'       => $courseDb.'userinfo_content',
              'userinfo_def'           => $courseDb.'userinfo_def',
              'wrk_assignment'         => $courseDb.'wrk_assignment',
              'wrk_submission'         => $courseDb.'wrk_submission'

              ); // end array

    } // end if ( count($course_tbl) == 0 )

    return $courseTblList;
}

/**
 * Claroline mySQL query wrapper. It also provides a debug display which works
 * when the CLARO_DEBUG_MODE constant flag is set to on (true)
 *
 * @author Hugues Peeters    <peeters@ipm.ucl.ac.be>,
 * @author Christophe Gesche <gesche@ipm.ucl.ac.be>
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
 * Claroline SQL fetch array returning all the result rows
 * in an associative array.    Compared to    the    PHP    mysql_fetch_array(),
 * it proceeds in a    single pass.
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>,
 * @param  handler    $sql - $sqlResultHandler
 * @param  int $resultType (optional) -    MYSQL_ASSOC    constant by    default
 * @return array         - associative array containing    all    the    result rows
 */


function claro_sql_fetch_all($sqlResultHandler, $resultType    = MYSQL_ASSOC)
{
    $rowList = array();

    while( $row = mysql_fetch_array($sqlResultHandler, $resultType) )
    {
        $rowList [] = $row;
    }

    mysql_free_result($sqlResultHandler);

    return $rowList;
}



/**
 * Claroline SQL query and fetch array wrapper. It returns all the result rows
 * in an associative array.
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>,
 * @param  string  $sqlQuery - the sql query
 * @param  handler $dbHandler - optional
 * @return array   - associative array containing all the result rows
 *
 * @see    claro_sql_query(), claro_sql_fetch_all
 *
 */

function claro_sql_query_fetch_all($sqlQuery, $dbHandler = '#')
{
    $result = claro_sql_query($sqlQuery, $dbHandler);

    if ($result) return claro_sql_fetch_all($result);
    else         return false;
}

/**
 * Claroline SQL query and fetch array wrapper. It returns all the result in
 * associative array ARRANGED BY COLUMNS.
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>,
 * @param  string  $sqlQuery  - the sql query
 * @param  handler $dbHandler - optional
 * @return associative array containing all the result arranged by columns
 *
 * @see    claro_sql_query()
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

        mysql_free_result($result);

        if( count($colList) < 1) // special case when there is no result
        {                        // the script will at least create the
                                 // column headers from the query

            $selectLine = substr($sqlQuery, 0, strpos($sqlQuery, 'FROM') -1 );

            $selectLine = str_replace( array('ALL', 'DISTINCT', 'DISTINCTROW',
                                             'HIGH_PRIORITY', 'STRAIGHT_JOIN',
                                           'SQL_SMALL_RESULT', 'SQL_BIG_RESULT',
                                           'SQL_BUFFER_RESULT', 'SQL_CACHE',
                                           'SQL_NO_CACHE', 'SQL_CALC_FOUND_ROWS', '`'),
                                       '', $selectLine);

            $exprList = explode(',', $selectLine);
            $exprList = array_map('trim', $exprList);

            foreach($exprList as $thisExpr)
            {
               $decomposedExprList = preg_split('/( AS |[ ]+)/', $thisExpr);
               $colNameList [] = end( explode( '.', end($decomposedExprList) ) );
            }

            foreach($colNameList as $thisColName) $colList[$thisColName]= array();
        } // end if( count($colList) < 1)

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
 * @author Hugues Peeters <hugues.peeters@claroline.net>,
 * @param  string  $sqlQuery - the sql query
 * @param  handler $dbHandler  - optional
 * @return associative array containing all the result rows
 * @since 1.5.1
 * @see    claro_sql_query()
 *
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
 * Claroline SQL query wrapper returning the number of rows affected by the
 * query
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>,
 * @param  string  $sqlQuery  - the sql query
 * @param  handler $dbHandler - optional
 * @return int                - the number of rows affected by the query
 *
 * @see    claro_sql_query()
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
 * @param  string  $sqlQuery  - the sql query
 * @param  handler $dbHandler - optional
 * @return long         -  the id generated by the previous insert query
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
 * from functions or objects. This strengthens encupsalation principle
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>
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
     * @param  string $failureType - the type of failure
     * @global array  $claro_failureList
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
     * @param void
     * @return string - the last failure stored
     */

    function get_last_failure()
    {
        global $claro_failureList;

        return $claro_failureList[ count($claro_failureList) - 1 ];
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

function claro_disp_tool_title($titleElement, $helpUrl = false)
{
    // if titleElement is simply a string transform it into an array

    if (is_string($titleElement))
    {
        $tit = $titleElement;
        unset($titleElement);
        $titleElement['mainTitle'] = $tit;
    }

    echo '<h3 class="claroToolTitle">';

    if ($helpUrl)
    {
        global $clarolineRepositoryWeb, $imgRepositoryWeb,$langHelp;

?><a href="#" onClick="MyWindow=window.open('<?php echo $clarolineRepositoryWeb ?>help/<?php echo $helpUrl ?>','MyWindow','toolbar=no,location=no,directories=no,status=yes,menubar=no,scrollbars=yes,resizable=yes,width=350,height=450,left=300,top=10'); return false;"><?php


        echo '<img src="'.$imgRepositoryWeb.'/help.gif" '
                .' alt ="'.$langHelp.'"'
                .' align="right"'
                .' hspace="30">'
            .'</a>';
    }


    if (isset($titleElement['supraTitle']))
    {
        echo '<small>'.$titleElement['supraTitle'].'</small><br>';
    }

    if (isset($titleElement['mainTitle']))
    {
        echo $titleElement['mainTitle'];
    }

    if (isset($titleElement['subTitle']))
    {
        echo '<br><small>'.$titleElement['subTitle'].'</small>';
    }

    echo '</h3>';
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
 */


function claro_disp_tool_view_option($viewModeRequested = false)
{
    global $clarolineRepositoryWeb, $is_courseAdmin,
           $langCourseManager,  $langStudent, $langViewMode;

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

            $studentButton     = '<a href="'.$url.'&amp;viewMode=STUDENT">'
                                 .$langStudent
                                 .'</a>';
            $courseAdminButton = '<b>'.$langCourseManager.'</b>';

            break;

        case 'STUDENT' :

            $studentButton     = '<b>'.$langStudent.'</b>';
            $courseAdminButton = '<a href="'.$url.'&amp;viewMode=COURSE_ADMIN">'
                                 .$langCourseManager
                                 .'</a>';
            break;
    }

    /*------------------------------------------------------------------------
                             DISPLAY COMMANDS MENU
      ------------------------------------------------------------------------*/

    echo $langViewMode." : "
        .$studentButton
        ." | "
        .$courseAdminButton;
}




function claro_enable_tool_view_option()
{
    global $claro_toolViewOptionEnabled;
    $claro_toolViewOptionEnabled = true;
}



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
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param void
 * @return string 'COURSE_ADMIN' or 'STUDENT'
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
 * @author Roan Embrechts
 * @author Patrick Cool
 *
 * @version 1.1, February 2004
 * @return boolean, true: the user has the rights to edit, false: he does not
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
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 * @return boolean
 */

function claro_is_display_mode_available()
{
    global $is_display_mode_available;
    return $is_display_mode_available;
}

/**
 *
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 * @param boolean
 */


function claro_set_display_mode_available($mode)
{
    global $is_display_mode_available;
    $is_display_mode_available = $mode;
}


/**
    Display    list of    messages

    !!! DEPRECATED !!!

    USE claro_disp_message_box($message) INSTEAD

    @param $msgArrBody array of    messages
    @author    Christophe gesché <moosh@claroline.net>
    @version 1.0

    Example    code for using this    in your    tools:
    $msgArrBody["nameOfCssClass"]="foo";
.    css    class can be defined in    script but try to use
    class from    generic    css    ()
    error success warning
    ...
*/

function claro_disp_msg_arr($msgArrBody)
{
    if (is_array($msgArrBody))
    {
        foreach ($msgArrBody as $thisMsgArr)
            foreach ($thisMsgArr as $anotherThis)
                $messageList[] = $anotherThis;

        claro_disp_message_box( '<p>'.implode('<p></p>', $messageList).'</p>' );
    }
}


/**
    Display    authencation form if needed

    @author    Christophe gesché <moosh@claroline.net>
    @version 0.1
*/

function claro_disp_auth_form()
{
    global  $includePath, $_uid, $_user, $is_courseAllowed, $_course,
            $langPassword , $langUserName, $langLogin, $langReg,
            $langNotAllowed, $lang_this_course_is_protected,
            $lang_enter_your_user_name_and_password, $lang_click_here,
            $lang_if_you_dont_have_a_user_account_profile_on,
            $lang_your_user_profile_doesnt_seem_to_be_enrolled_to_this_course,
            $lang_if_you_wish_to_enroll_to_this_course
            ;

    // var used in claro_init_header, banner and footer
    global  $charset, $rootWeb, $clarolineRepositoryWeb, $siteName,
            $claro_stylesheet, $langOtherCourses, $langModifyProfile,
            $institution_url, $institution_name, $langMyCourses, $langMyAgenda,
            $langLogout, $claro_brailleViewMode,
            $lang_footer_p_CourseManager,  $lang_p_platformManager, $administrator_name,
            $langPoweredBy, $claro_banner;

    include($includePath.'/claro_init_header.inc.php');

    if ( ! $is_courseAllowed )
    {

        if( ! $_uid && ! $_course['visibility'])
        {
            echo '<p align="center>">'
                .$lang_this_course_is_protected.'<br>'
                .$lang_enter_your_user_name_and_password
                .'</p>';

            echo '<table align="center">'."\n"
                .'<tr>'
                .'<td>'
                .'<form action="'.$_SERVER['PHP_SELF'].'" method="POST">'."\n"

                .'<fieldset>'."\n"

                .'<legend>'.$langLogin.'</legend>'."\n"

                .'<label for="username">'.$langUserName.' : </label><br>'."\n"
                .'<input type="text" name="login" id="username"><br>'."\n"

                .'<label for="password">'.$langPassword.' : </label><br>'."\n"
                .'<input type="password" name="password" id="password"><br>'."\n"
                .'<input type="submit" >'."\n"

                .'</fieldset>'."\n"

                .'</form>'."\n"
                .'</td>'
                .'</tr>'
                .'</table>';

            /**
             * If users are allowed to register themselves to the platform
             * redirect this user to the platform registration page
             */

            if ( $allowSelfReg || !isset($allowSelfReg) )
            {

                echo '<p>'."\n"
                    .$lang_if_you_dont_have_a_user_account_profile_on.' '.$siteName
                    .'&nbsp;' . '<a href="'.$clarolineRepositoryWeb.'auth/inscription.php">'
                    .$lang_click_here
                    .'</a>'."\n"
                    .'</p>'."\n"
                    ;
            }
        } // end if ! $uid && ! $course['visibility']

        /*
         * If the user is logged (authenticated) on the platform
         * and the course settings still allows user self enrollment,
         * redirect him to the course enrollment pages
         */

        elseif( $_uid && $_course['registrationAllowed'] )
        {
            echo '<p align="center>">'
                 .$lang_this_course_is_protected.'<br>'
                 .$lang_enter_your_user_name_and_password
                 .'</p>';

            // if  I'm logged but have no access
            // this course is close, right, but the subscribe to this course ?
                echo '<p>'."\n"
                    .$lang_your_user_profile_doesnt_seem_to_be_enrolled_to_this_course.'<br>'
                    .$lang_if_you_wish_to_enroll_to_this_course
                    .'<a href="'.$clarolineRepositoryWeb.'auth/courses.php?cmd=rqReg&amp;keyword='.$_course['officialCode'].'" >'
                    .$langReg
                    .'</a>'."\n"
                    .'</p>'."\n"
                    ;

        } // elseif$_uid && $_course['registrationAllowed']

        else
        {
            echo '<p>' . $langNotAllowed . '</p>';
        }

        include($includePath.'/claro_init_footer.inc.php');

        die('');
    }
}


/**
    Display selectbox for select a course

    @author    Christophe gesché <moosh@claroline.net>
    @version 0.1
*/

function claro_disp_select_course()
{
    global  $_uid, $_cid,

            $siteName,$includePath,
            $langManager, $administrator
            ;

    $mainTbl = claro_sql_get_main_tbl();
    $tbl_courses            = $mainTbl['course'];
    $tbl_rel_user_courses   = $mainTbl['rel_course_user'];
    if ( ! $_cid)
    {
        /*
            This function is called when a $_cid is request
        */

        if($_uid)
        {
            $sql_get_course_list =
            "select c.code `value`, concat(c.intitule,' (',c.fake_code,')') `name`
             from `".$tbl_courses."` c ,  `".$tbl_rel_user_courses."` cu
             WHERE c.code= cu.code_cours and cu.user_id = '".$_uid."'" ;
        } // end if $uid
        else
        {
            $sql_get_course_list =
            "select c.code `value`, concat(c.intitule,' (',c.fake_code,')') `name`
            from `".$tbl_courses."` c";
        }

        $resCourses = claro_sql_query($sql_get_course_list);
        while($course = mysql_fetch_array($resCourses))
        {
                $courses[]=$course;
        }
        if (is_array($courses))
        {
            claro_disp_tool_title("This tools need a course");
        ?>
<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
    <label for="selectCourse">Course</label> :
        <select name="cidReq" id="selectCourse">
    <?php
        echo implode("\t\t\t", prepare_option_tags($courses) );
    ?>
        </select>

    <input type="submit">
</form>
        <?php
        }
        include($includePath."/claro_init_footer.inc.php");

        die('');
    }
}



/**
    Display    intro of tool
    This   use introductionSection.inc.php in a    function.

    !!!!! BETA !!!!! this  a test.    That's    work but is    that pertinent ????

    @param $idTools
    @author    Christophe Gesché <moosh@claroline.net>
    @version 0.1
*/

function claro_disp_intro($idTools)
{
    global $includePath, $_course,$urlAppend, $is_courseAdmin,
           $langOk, $langAddIntro, $langModify,
           $langConfirmYourChoice,$langDelete;

    $moduleId =    $idTools; // Id    of the Student Paper introduction Area
    include($includePath.'/introductionSection.inc.php');
    return true;
}

/**
 * diplays the message box appearing on    the    top    of the window,
 * just    below the tool title. It is    recommended    to use this    function
 * to display any confirmation or error    messages, or to    ask    to the user
 * to enter    simple parameters.
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 * @param string $message -    include    your self any additionnal html
 *                            tag    if you need    them
 * @return void
 */

function claro_disp_message_box($message)
{
?>
<table class="claroMessageBox" border="0" cellspacing="0" cellpadding="10">
<tr>
<td>
<?php echo $message; ?>
</td>
</tr>
</table>
<?php
}

/**
 * displays an anchor tag (<a ...>) which, thanks to style sheet (css),
 * looks like a button.
 *
 * This function is needed, because Netscap 4 family browsers renders CSS
 * so badly that it makes the button unusable. The function prevents the problem
 * to occur by removing class style  if the browser is from the Netscape 4
 * familiy.
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 *
 * @param string $url url inserted into the 'href' part of the tag
 * @param string $text text inserted between the two <a>...</a> tags (note : it
 *        could also be an image ...)
 * @param string $confirmMessage (optionnal) introduce a javascript confirmation popup
 * @return void
 */


function claro_disp_button($url, $text, $confirmMessage = '')
{
    global $HTTP_USER_AGENT;

    if (   claro_is_javascript_enabled()
        && ! preg_match('~^Mozilla/4\.[1234567]~', $HTTP_USER_AGENT))
    {
        if ($confirmMessage != '')
        {
            $onClickCommand =" if(confirm('".clean_str_for_javascript($confirmMessage)."')){document.location='".$url."';return false}";
        }
        else
        {
            $onClickCommand = "document.location='".$url."';return false";
        }

        echo "<button class=\"claroButton\" onclick=\"".$onClickCommand."\">"
            .$text
            ."</button>&nbsp;\n";
    }
    else
    {
        echo '<nobr>[ <a  href="'.$url.'" '.$additionnalParam.'>'.$text.'</a> ] </nobr>';
    }
}

/**
 * Function used to draw a progression bar
 *
 * @author Piraux Sébastien <pir@cerdecam.be>
 *
 * @param $progress progression in pourcent
 * @param $factor will be multiply by 100 to have the full size of the bar
 * (i.e. 1 will give a 100 pixel wide bar)
 */

function claro_disp_progress_bar ($progress, $factor)
{
    global $clarolineRepositoryWeb, $imgRepositoryWeb;
           $maxSize  = $factor * 100; //pixels
           $barwidth = $factor * $progress ;

    // display progress bar
    // origin of the bar
    $progressBar = "<img src=\"".$imgRepositoryWeb."bar_1.gif\" width=\"1\" height=\"12\" alt=\"\">";

    if($progress != 0)
            $progressBar .= "<img src=\"".$imgRepositoryWeb."bar_1u.gif\" width=\"$barwidth\" height=\"12\" alt=\"\">";
    // display 100% bar

    if($progress!= 100 && $progress != 0)
            $progressBar .= "<img src=\"".$imgRepositoryWeb."bar_1m.gif\" width=\"1\" height=\"12\" alt=\"\">";

    if($progress != 100)
            $progressBar .= "<img src=\"".$imgRepositoryWeb."bar_1r.gif\" width=\"".($maxSize-$barwidth)."\" height=\"12\" alt=\"\">";
    // end of the bar
    $progressBar .=  "<img src=\"".$imgRepositoryWeb."bar_1.gif\" width=\"1\" height=\"12\" alt=\"\">";

    return $progressBar;
}

/**

 * @author Christophe Gesché <gesche@ipm.ucl.ac.be>
 * @param formatOfDate
         see http://www.php.net/manual/en/function.strftime.php
         for syntax to use for this string
         I suggest to use the format you can find in trad4all.inc.php files
 * @param timestamp timestamp of date to format
 * @desc  display a date at localized format
 */

function claro_disp_localised_date($formatOfDate,$timestamp = -1) //PMAInspiration :)
{
    global $langMonthNames;
    global $langDay_of_weekNames;

    if ($timestamp == -1)
    {
        $timestamp = time();
    }
    // avec un ereg on fait nous même le replace des jours et des mois
    // with the ereg  we  replace %aAbB of date format
    //(they can be done by the system when  locale date aren't aivailable


    $date = ereg_replace('%[A]', $langDay_of_weekNames["long"][(int)strftime('%w', $timestamp)], $formatOfDate);
    $date = ereg_replace('%[a]', $langDay_of_weekNames["short"][(int)strftime('%w', $timestamp)], $date);
    $date = ereg_replace('%[B]', $langMonthNames["long"][(int)strftime('%m', $timestamp)-1], $date);
    $date = ereg_replace('%[b]', $langMonthNames["short"][(int)strftime('%m', $timestamp)-1], $date);
    return strftime($date, $timestamp);
}

/**
 * Insert a    sort of    HTML Wysiwyg textarea inside a FORM
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 *
 * @param string $name
 * @param string $content optional content previously inserted into    the    area
 * @param int     $rows optional    textarea rows
 * @param int     $cols optional    textarea columns
 * @param string $optAttrib    optional - additionnal tag attributes
 *                                       (wrap, class, ...)
 * @return void
 *
 * @globals    $urlAppend from    claro_main.conf.php
 *
 * @desc the html area currently implemented is    HTMLArea 3.0. To work correctly,
 * the area    needs a    specific stylesheet    previously loaded in the html header.
 * For that, use the claroline $htmlHeadXtra[] array at    the    top    of the script
 * just    before including claro_init_header.inc.php
 *
 * Example : $htmlHeadXtra[] = '<style type="text/css">
 * @import url('.$urlAppend.'/claroline/inc/htmlarea'.'/htmlarea.css);
 *                                </style>';
 */

function claro_disp_html_area($name, $content =    '',
                              $rows=20,    $cols=80, $optAttrib='')
{
    global $urlAppend, $iso639_1_code, $langTextEditorDisable, $langTextEditorEnable,$langSwitchEditorToTextConfirm;
    $incPath = $urlAppend.'/claroline/inc/htmlarea';

    if (isset($_REQUEST['areaContent'])) $content = stripslashes($_REQUEST['areaContent']);

    if (claro_is_javascript_enabled())
    {
        if ($_SESSION['htmlArea'] != 'disabled')
        {
            $switchState = 'off';
            $message     = $langTextEditorDisable;
			$areaContent = 'editor.getHTML()';
            $confirmCommand = "if(!confirm('".clean_str_for_javascript($langSwitchEditorToTextConfirm)."'))return(false);";
        }
        else
        {
            $switchState = 'on';
            $message     = $langTextEditorEnable;
            $areaContent = 'document.getElementById(\''.$name.'\').value';
            $confirmCommand = '';
        }

        $location = '\''
                   .$incPath.'/editorswitcher.php?'
                   .'switch='.$switchState
                   .'&sourceUrl='.urlencode($_SERVER['REQUEST_URI'])
                   .'&areaContent='
                   .'\''
                  .'+escape('.$areaContent.')';
        
        echo '<div align="right">'
            .'<small>'
            .'<b>'
            .'<a href="" onClick ="'.$confirmCommand.'window.location='.$location.';return(false);">'
            .$message
           .'</a>'
            .'</b>'
            .'</small>'
            .'</div>';

    } // end if claro_is_javascript_enabled()

?>
<textarea id    = "<?php echo $name; ?>"
          name  = "<?php echo $name; ?>"
          style = "width:100%"
          rows  = "<?php echo $rows; ?>"
          cols  = "<?php echo $cols; ?>"
          <?php echo $optAttrib; ?> ><?php echo $content; ?></textarea>
<?php

    if ( $_SESSION['htmlArea'] != 'disabled' )
    {

?>

<script>_editor_url    = "<?php echo  $incPath?>";</script>
<script    type="text/javascript" src="<?php echo $incPath; ?>/htmlarea.js"></script>
<script    type="text/javascript" src="<?php echo $incPath; ?>/lang/<?php echo $iso639_1_code; ?>.js"></script>
<script    type="text/javascript" src="<?php echo $incPath; ?>/dialog.js"></script>

<script    type="text/javascript">
var    editor = null;
function initEditor() {
  editor = new HTMLArea("<?php echo    $name ?>");

  // comment the following two lines to    see    how    customization works
  editor.generate();
  return false;
}

function insertHTML() {
 var html =    prompt("Enter some HTML    code here");
 if    (html) {editor.insertHTML(html);}
}
function highlight() {
  editor.surroundHTML('<span style="background-color: yellow">', '</span>');
}
</script>

<script>
initEditor();
</script>
<?php
    } // end if  $_SESSION['htmlArea'] != 'disabled'
    else
    {
        // noop
    }
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
    return "<select name=\"".$name."\">\n"
          .implode("\n", prepare_option_tags($elementList) )
          ."</select>\n";
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
                          .$tab.$thisElement['name']
                          .'</option>';

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
//                            addslashes,...
//////////////////////////////////////////////////////////////////////////////

/**
 * Add slashes to $text if it has not be automatically done by magic_quotes
 * Use this function _ONLY_ for vars that are affected by magic_quote_gpc
 *
 * @author Piraux Sébastien <pir@cerdecam.be>
 * @param string text to add slashes in
 * @return string $text without change if magi_quote_gpc is on, addslahed $text else
 * @desc Use this only for get/post/cookies vars, not for lang vars,...
 */
function claro_addslashes($text)
{
  if( get_magic_quotes_gpc() && !defined('CL_GPC_UNQUOTED') )
  {
    // magic_quote_gpc is on : do not addslashes
    return $text;
  }
  else
  {
    // magic_quote_gpc is off : addslashes
    return addslashes($text);
  }
}

/**
 * checks if the javascript is enabled on the client browser
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 * @param void
 * @return boolean
 * @desc Actually a cookies is set on the header by a javascript code.
 *       If this cookie isn't set, it means javascript isn't enabled.
 */

function claro_is_javascript_enabled()
{
    global $_COOKIE;

    if ($_COOKIE['javascriptEnabled'] == true)
    {
        return true;
    }
    else
    {
        return false;
    }
}

/**
 * parse the user text (e.g. stored in database)
 * before displaying it to the screen
 * For example it change new line charater to <br> tag etc.
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 * @param string $userText original user tex
 * @return string parsed user text
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
 * function make_clickable($text)
 *
 * @desc   completes url contained in the text with "<a href ...".
 *         However the function simply returns the submitted text without any
 *         transformation if it already contains some "<a href:" or "<img src=".
 * @params string $text text to be converted
 * @return string - text after conversion
 * @author Rewritten by Nathan Codding - Feb 6, 2001.
 *         completed by Hugues Peeters - July 22, 2002
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
 * strip the slashes coming from browser request
 *
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @return void
 * @desc If the php.ini setting MAGIC_QUOTE_GPC is set to ON, all the variables
 *       content comming frome the browser are automatically quoted by adding
 *       slashes (default setting before PHP 4.3). claro_unquote_gpc() removes
 *       these slashes. It needs to be called just once at the biginning
 *       of the script.
 * @see
 *
 */

function claro_unquote_gpc()
{
    if ( ! defined('CL_GPC_UNQUOTED'))
    {
        if ( get_magic_quotes_gpc() )
        {
            if ( !empty($_GET) )     array_walk($_GET,     'claro_stripslashes_for_unquote_gpc');
            if ( !empty($_POST) )    array_walk($_POST,    'claro_stripslashes_for_unquote_gpc');
            if ( !empty($_REQUEST) ) array_walk($_REQUEST, 'claro_stripslashes_for_unquote_gpc' );
            if ( !empty($_COOKIE) )  array_walk($_COOKIE,  'claro_stripslashes_for_unquote_gpc' );
        }

        define('CL_GPC_UNQUOTED', true);
    }
}


/**
 * special function for claro_unquote_gpc()
 *
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @return void
 *
 * @desc This function is needed rather a simple stripslashes for two reasons.
 *       First the PHP function array_walk() works only with user functions,
 *       not PHP ones. Second, the submitted array could be an array of arrays,
 *       and all the values has to be treated.
 */ 

function claro_stripslashes_for_unquote_gpc( &$var )
{
	if (is_array($var) ) array_walk($var, 'claro_stripslashes_for_unquote_gpc');
    else                 $var = stripslashes($var);
}

/**
 * function that cleans php string for javascript
 *
 * @param $str string original string
 * @return string cleaned string
 *
 * @author Piraux Sébastien <pir@cerdecam.be>
 *
 * @desc This function is needed to clean strings used in javascript output
 *		 Newlines are prohibited in the script, specialchar  are prohibited
 *       quotes must be addslashes
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
?>