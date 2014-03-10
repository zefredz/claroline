<?php // $Id$

/**
 * CLAROLINE
 *
 * @version     $Revision$
 * @copyright   (c) 2001-2014, Universite catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC
 *              LICENSE version 2 or later
 * @author      see 'credits' file
 * @package     KERNEL
 */

//////////////////////////////////////////////////////////////////////////////
//                   CLAROLINE DB    QUERY WRAPPRER MODULE
//////////////////////////////////////////////////////////////////////////////

/**
 * Get main database table aliases
 * @return array 
 */
function get_main_tbl_aliases()
{
    return array (    
        'course'                    => 'cours',
        'user_category'             => 'class',
        'user_rel_profile_category' => 'rel_class_user',
        'course_user'               => 'rel_course_user',
        'tool'                      => 'course_tool'
    );
}

/**
 * Get course database table aliases
 * @return array 
 */
function get_course_tbl_aliases()
{
    return array(
        'links'                  => 'lnk_links',
        'resources'              => 'lnk_resources',
        'tool'                   => 'tool_list'
    );
}


/**
 * Return the tablename for a tool, depending on the execution context (course or not)
 *
 * @param array $tableList
 * @param string $courseCode
 * @return array of table names
 */
function claro_sql_get_tbl( $tableList, $courseCode = null )
{

    if( ! is_array( $tableList ) )
    {
        $arrTblName = array( $tableList );
    }
    else 
    {
        $arrTblName = $tableList;
    }
    
    // we are in a course
    if ( ! empty( $courseCode ) )
    {
        $currentCourseDbNameGlu = claro_get_course_db_name_glued( $courseCode );

        if ( ! $currentCourseDbNameGlu )
        {
            throw new Exception('Invalid course !');
        }

        return __claro_sql_get_course_tbl_private( $tableList, $currentCourseDbNameGlu );
    }
    else
    {
        
        $aliases = get_main_tbl_aliases();
    
        $mainDbNameGlu = get_conf('mainDbName') . '`.`' . get_conf('mainTblPrefix');

        $arrToReturn = array();

        foreach ( $arrTblName as $name )
        {
            if ( array_key_exists( $name, $aliases ) )
            {
                $arrToReturn[$name] = $mainDbNameGlu . $aliases[$name];
            }
            else
            {
                $arrToReturn[$name] = $mainDbNameGlu . $name;
            }
        }

        return $arrToReturn;
    }
}

/**
 * DO NOT CALLK OUTSIDE OF THIS LIBRARY
 * Return the tablename for a tool, depending on the execution context (course or not)
 *
 * @private
 * @param array $tableList
 * @param string $courseDbNameGlued (use only for internals)
 * @return array of table names
 */
function __claro_sql_get_course_tbl_private( $tableList, $courseDbNameGlued = null )
{
    $aliases = get_course_tbl_aliases();

    $arrToReturn = array();

    foreach ( $tableList as $name )
    {
        if ( array_key_exists( $name, $aliases ) )
        {
            $arrToReturn[$name] = $courseDbNameGlued . $aliases[$name];
        }
        else
        {
            $arrToReturn[$name] = $courseDbNameGlued . $name;
        }

    }

    return $arrToReturn;
}

// Helpers and backward compatibility functions

/**
 * Get list of module table names 'localized' for the given course
 * @param array $arrTblName of tableName
 * @param string $courseCode course code
 * @return array $tableName => $dbNameGlue . $tableName
 * @throws Exception if no course code given and not in a course or
 *  course not valid
 */
function get_module_course_tbl( $arrTblName, $courseCode = null )
{
    if ( empty ( $courseCode ) )
    {
        if ( ! claro_is_in_a_course() )
        {
            throw new Exception('Not in a course !');
        }
        else
        {
            $courseCode = claro_get_current_course_id();
        }
    }
    
    return claro_sql_get_tbl( $arrTblName, $courseCode );
}

/**
 * Get list of module table names 'localized' for the main db
 * @param array $arrTblName of tableName
 * @return array $tableName => mainTblPrefix . $tableName
 */
function get_module_main_tbl( $arrTblName )
{
    return claro_sql_get_tbl( $arrTblName );
}

/**
 * Get list of table names for central table.
 *
 * @return array list of the central claroline database tables
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 * @deprecated for module development since Claroline 1.9, use
 *  get_module_main_tbl or claro_sql_get_tbl instead
 */
function claro_sql_get_main_tbl()
{
    static $mainTblList = array();
    
    if ( count($mainTblList) == 0 )
    {
        $mainTblList = claro_sql_get_tbl ( array(
            'coursehomepage_portlet',
            'config_property',
            'config_file',
            'course',
            'category',
            'event_resource',
            'user',
            'tool',
            'user_category',
            'user_rel_profile_category',
            'class',
            'rel_class_user',
            'rel_course_category',
            'rel_course_class',
            'rel_course_portlet',
            'rel_course_user',
            'sso',
            'notify',
            'upgrade_status',
            'module',
            'module_info',
            'module_contexts',
            'dock',
            'right_profile',
            'right_rel_profile_action',
            'right_action',
            'user_property',
            'property_definition',
            'im_message',
            'im_message_status',
            'im_recipient',
            'desktop_portlet',
            'desktop_portlet_data',
            'tracking_event',
            'log'
        ) );
    }
    
    return $mainTblList;
}

/**
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 * @param  string $dbNameGlued (optionnal) course database with its platform
 *         glue already append. If no db name are set, the current course db
 *         will be taken.
 * @return array list of the current course database tables
 * @deprecated for module development since Claroline 1.9, use
 *  get_module_course_tbl or claro_get_sql_tbl instead
 */
function claro_sql_get_course_tbl($dbNameGlued = null)
{
    
    $_course = get_init('_course');
    
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
        // FIXME remove tables of up to date modules
        $courseTblList = __claro_sql_get_course_tbl_private( array(
              'announcement',
              'bb_categories',
              'bb_forums',
              'bb_posts' ,
              'bb_posts_text',
              'bb_priv_msgs',
              'bb_rel_topic_userstonotify',
              'bb_rel_forum_userstonotify',
              'bb_topics',
              'bb_users',
              'bb_whosonline',
              'calendar_event',
              'course_description',
              'document',
              'course_properties',
              'group_property',
              'group_rel_team_user',
              'group_team',
              'lp_learnPath',
              'lp_rel_learnPath_module',
              'lp_user_module_progress',
              'lp_module',
              'lp_asset',
              'qwz_exercise',
              'qwz_question',
              'qwz_rel_exercise_question',
              'qwz_answer_truefalse',
              'qwz_answer_multiple_choice',
              'qwz_answer_fib',
              'qwz_answer_matching',
              'tool_intro',
              'tool',
              'tracking_event',
              'userinfo_content',
              'userinfo_def',
              'wrk_assignment',
              'wrk_submission',
              'links',
              'resources',
              'wiki_properties',
              'wiki_pages',
              'wiki_pages_content',
              'wiki_acls',
              ), $courseDbInCache );

    } // end if ( count($course_tbl) == 0 )

    return $courseTblList;
}

/**
 * CLAROLINE mySQL query wrapper. It also provides a debug display which works
 * when the CLARO_DEBUG_MODE constant flag is set to on (true)
 *
 * @copyright   (c) 2001-2014, Universite catholique de Louvain (UCL)
 * @author Christophe Gesch√© <moosh@claroline.net>
 * @param  string  $sqlQuery   - the sql query
 * @param  handler $dbHandler  - optional
 * @return handler             - the result handler
 * @deprecated since Claroline 1.9, use Claroline::getDatabase() and new classes
 *  in database/database.lib.php instead
 */
function claro_sql_query($sqlQuery, $dbHandler = '#' )
{

    if ( claro_debug_mode()
      && get_conf('CLARO_PROFILE_SQL',false)
      )
      {
         $start = microtime();
      }
    if ( $dbHandler == '#')
    {
        $resultHandler =  @mysqli_query($GLOBALS["___mysqli_ston"], $sqlQuery);
    }
    else
    {
        $resultHandler =  @mysqli_query( $dbHandler, $sqlQuery);
    }

    if ( claro_debug_mode()
      && get_conf('CLARO_PROFILE_SQL',false)
      )
    {
        static $queryCounter = 1;
        $duration = microtime()-$start;
        $info = 'execution time : ' . ($duration > 0.001 ? '<b>' . round($duration,4) . '</b>':'&lt;0.001')  . '&#181;s'  ;
        // $info = ( $dbHandler == '#') ? mysql_info() : mysql_info($dbHandler);
        // $info .= ': affected rows :' . (( $dbHandler == '#') ? mysql_affected_rows() : mysql_affected_rows($dbHandler));
        $info .= ': affected rows :' . claro_sql_affected_rows();

        pushClaroMessage( '<br />Query counter : <b>' . $queryCounter++ . '</b> : ' . $info . '<br />'
            . '<code><span class="sqlcode">' . nl2br($sqlQuery) . '</span></code>'
            , (claro_sql_errno()?'error':'sqlinfo'));

    }
    if ( claro_debug_mode() && claro_sql_errno() )
    {
        echo '<hr size="1" noshade>'
        .    claro_sql_errno() . ' : '. claro_sql_error() . '<br>'
        .    '<pre style="color:red">'
        .    $sqlQuery
        .    '</pre>'
        .    ( function_exists('claro_html_debug_backtrace')
             ? claro_html_debug_backtrace()
             : ''
             )
        .    '<hr size="1" noshade>'
        ;
    }

    return $resultHandler;
}

/**
 * CLAROLINE mySQL errno wrapper.
 * @deprecated since Claroline 1.9, use Claroline::getDatabase() and new classes
 *  in database/database.lib.php instead
 */
function claro_sql_errno($dbHandler = '#')
{
    if ( $dbHandler == '#' )
    {
        return ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false));
    }
    else
    {
        return ((is_object($dbHandler)) ? mysqli_errno($dbHandler) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false));
    }
}

/**
 * CLAROLINE mySQL error wrapper.
 * @deprecated since Claroline 1.9, use Claroline::getDatabase() and new classes
 *  in database/database.lib.php instead
 */
function claro_sql_error($dbHandler = '#')
{
    if ( $dbHandler == '#' )
    {
        return ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false));
    }
    else
    {
        return ((is_object($dbHandler)) ? mysqli_error($dbHandler) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false));
    }
}

/**
 * CLAROLINE mySQL selectDb wrapper.
 * @deprecated since Claroline 1.9, use Claroline::getDatabase() and new classes
 *  in database/database.lib.php instead
 */
function claro_sql_select_db($dbName, $dbHandler = '#')
{
    if ( $dbHandler == '#' )
    {
        return ((bool)mysqli_query($GLOBALS["___mysqli_ston"], "USE $dbName"));
    }
    else
    {
        return ((bool)mysqli_query( $dbHandler, "USE $dbName"));
    }
}

/**
 * CLAROLINE mySQL affected rows wrapper.
 * @deprecated since Claroline 1.9, use Claroline::getDatabase() and new classes
 *  in database/database.lib.php instead
 */
function claro_sql_affected_rows($dbHandler = '#')
{
    if ( $dbHandler == '#' )
    {
        return mysqli_affected_rows($GLOBALS["___mysqli_ston"]);
    }
    else
    {
        return mysqli_affected_rows($dbHandler);
    }
}

/**
 * CLAROLINE mySQL insert id wrapper.
 * @deprecated since Claroline 1.9, use Claroline::getDatabase() and new classes
 *  in database/database.lib.php instead
 */
function claro_sql_insert_id($dbHandler = '#')
{
    if ( $dbHandler == '#' )
    {
        return ((is_null($___mysqli_res = mysqli_insert_id($GLOBALS["___mysqli_ston"]))) ? false : $___mysqli_res);
    }
    else
    {
        return ((is_null($___mysqli_res = mysqli_insert_id($dbHandler))) ? false : $___mysqli_res);
    }
}

/**
 * Get the name of the specified fields in a query result
 *
 * @param string $sq - SQL query
 * @param ressource (optional) - result pointer
 * @return  names of the specified field index
 *
 * @copyright   (c) 2001-2014, Universite catholique de Louvain (UCL)
 * @deprecated since Claroline 1.9, use Claroline::getDatabase() and new classes
 *  in database/database.lib.php instead
 */
function claro_sql_field_names( $sql, $resultPt = null )
{
    static $_colNameList = array();

    $sqlHash = md5($sql);

    if ( ! array_key_exists( $sqlHash, $_colNameList) )
    {
        if ( is_object($resultPt) && $resultPt instanceof mysqli_result )
        {
            // if ressource type is mysql result use it
            $releasablePt = false;
        }
        else
        {
            $resultPt     = claro_sql_query($sql);
            $releasablePt = true;
        }

        $resultFieldCount = (($___mysqli_tmp = mysqli_num_fields($resultPt)) ? $___mysqli_tmp : false);

        for ( $i = 0; $i < $resultFieldCount ; ++$i )
        {
            $_colNameList[$sqlHash][] = ((($___mysqli_tmp = mysqli_fetch_field_direct($resultPt, 0)->name) && (!is_null($___mysqli_tmp))) ? $___mysqli_tmp : false);
        }

        if ( $releasablePt ) ((mysqli_free_result($resultPt) || (is_object($resultPt) && (get_class($resultPt) == "mysqli_result"))) ? true : false);
    }

    return $_colNameList[$sqlHash];
}

/**
 * CLAROLINE SQL query and fetch array wrapper. It returns all the result rows
 * in an associative array.
 *
 * @param  string  $sqlQuery the sql query
 * @param  handler $dbHandler optional
 * @return array associative array containing all the result rows
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 * @deprecated since Claroline 1.9, use Claroline::getDatabase() and new classes
 *  in database/database.lib.php instead
 */

function claro_sql_query_fetch_all_rows($sqlQuery, $dbHandler = '#')
{
    $result = claro_sql_query($sqlQuery, $dbHandler);

    if ($result)
    {
        $rowList = array();

        while( $row = mysqli_fetch_array($result,  MYSQLI_ASSOC) )
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

        ((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);

        return $rowList;
    }
    else
    {
        return false;
    }
}

/**
 * Alias for claro_sql_query_fetch_all_rows
 * @see claro_sql_query_fetch_all_rows()
 * @deprecated since Claroline 1.9, use Claroline::getDatabase() and new classes
 *  in database/database.lib.php instead
 */
function claro_sql_query_fetch_all($sqlQuery, $dbHandler = '#')
{
    return claro_sql_query_fetch_all_rows($sqlQuery, $dbHandler);
}

/**
 * CLAROLINE SQL query and fetch array wrapper. It returns all the result in
 * associative array ARRANGED BY COLUMNS.
 *
 * @param  string  $sqlQuery  the sql query
 * @param  handler $dbHandler optional
 * @return associative array containing all the result arranged by columns
 *
 * @see    claro_sql_query()
 * @author Hugues Peeters <hugues.peeters@claroline.net>,
 * @deprecated since Claroline 1.9, use Claroline::getDatabase() and new classes
 *  in database/database.lib.php instead
 */
function claro_sql_query_fetch_all_cols($sqlQuery, $dbHandler = '#')
{
    $result = claro_sql_query($sqlQuery, $dbHandler);

    if ($result)
    {
        $colList = array();

        while( $row = mysqli_fetch_array($result,  MYSQLI_ASSOC) )
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

        ((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);

        return $colList;

    }
    else
    {
        return false;
    }
}


/**
 * CLAROLINE SQL query wrapper returning only a single result value.
 * Useful in some cases because, it avoid nested arrays of results.
 *
 * @param  string  $sqlQuery  the sql query
 * @param  handler $dbHandler optional
 * @return associative array containing all the result rows
 * @since  1.9
 * @see    claro_sql_query()
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>,
 * @deprecated since Claroline 1.9, use Claroline::getDatabase() and new classes
 *  in database/database.lib.php instead
 */
function claro_sql_query_fetch_single_value($sqlQuery, $dbHandler = '#')
{
    $result = claro_sql_query($sqlQuery, $dbHandler);

    if($result)
    {
        $row = mysqli_fetch_row($result);

        if ( is_array( $row ) )
        {
            list($value) = $row;
        }
        else
        {
            $value = null;
        }

        ((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);
        return $value;
    }
    else
    {
        return false;
    }
}

/**
 * CLAROLINE SQL query wrapper returning only a single result value.
 * Useful in some cases because, it avoid nested arrays of results.
 *
 * @param  string  $sqlQuery  the sql query
 * @param  handler $dbHandler optional
 * @return associative array containing all the result column
 * @since  1.5.1
 * @see    claro_sql_query_fetch_single_value()
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>,
 * @deprecated since Claroline 1.9, use Claroline::getDatabase() and new classes
 *  in database/database.lib.php instead
 */
function claro_sql_query_get_single_value($sqlQuery, $dbHandler = '#')
{
    return claro_sql_query_fetch_single_value($sqlQuery, $dbHandler);
}

/**
 * CLAROLINE SQL query wrapper returning only the first row of the result
 * Useful in some cases because, it avoid nested arrays of results.
 *
 * @param  string  $sqlQuery  the sql query
 * @param  handler $dbHandler optional
 * @return associative array containing all the result column
 * @since  1.9.*
 * @see    claro_sql_query_get_single_row()
 * @deprecated since Claroline 1.9, use Claroline::getDatabase() and new classes
 *  in database/database.lib.php instead
 */
function claro_sql_query_fetch_single_row($sqlQuery, $dbHandler = '#')
{
    return claro_sql_query_get_single_row($sqlQuery, $dbHandler);
}

/**
 * Get a single row from a SQL query
 * @param string $sqlQuery
 * @param ressource $dbHandler
 * @return array or false
 * @deprecated since Claroline 1.9, use Claroline::getDatabase() and new classes
 *  in database/database.lib.php instead
 */
function claro_sql_query_get_single_row($sqlQuery, $dbHandler = '#')
{
    $result = claro_sql_query($sqlQuery, $dbHandler);
    // TODO if $result is empty it can't return false but empty array.
    if($result)
    {
        $row = mysqli_fetch_array($result,  MYSQLI_ASSOC);
        ((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);
        return $row;
    }
    else
    {
        return false;
    }
}



/**
 * CLAROLINE SQL query wrapper returning the number of rows affected by the
 * query
 *
 * @param  string  $sqlQuery  the sql query
 * @param  handler $dbHandler optional
 * @return int                the number of rows affected by the query
 *
 * @see    claro_sql_query()
 * @author Hugues Peeters <hugues.peeters@claroline.net>,
 * @deprecated since Claroline 1.9, use Claroline::getDatabase() and new classes
 *  in database/database.lib.php instead
 */
function claro_sql_query_affected_rows($sqlQuery, $dbHandler = '#')
{
    $result = claro_sql_query($sqlQuery, $dbHandler);

    if ($result)
    {
        if ($dbHandler == '#') return mysqli_affected_rows($GLOBALS["___mysqli_ston"]);
        else                   return mysqli_affected_rows($dbHandler);

        // NOTE. To make claro_sql_query_affected_rows() work properly,
        // database connection is required with CLIENT_FOUND_ROWS flag.
        //
        // When using UPDATE, MySQL will not update columns where the new
        // value is the same as the old value. This creates the possiblity
        // that mysql_affected_rows() may not actually equal the number of
        // rows matched, only the number of rows that were literally affected
        // by the query. But this behavior can be changed by setting the
        // CLIENT_FOUND_ROWS flag in mysql_connect(). mysql_affected_rows()
        // will return then the number of rows matched, even if none are
        // updated.
    }
    else
    {
        return false;
    }
}

/**
 * CLAROLINE mySQL query wrapper returning the last id generated by the last
 * inserted row
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>,
 * @param  string  $sqlQuery  the sql query
 * @param  handler $dbHandler optional
 * @return integer the id generated by the previous insert query
 *
 * @see    claro_sql_query()
 * @deprecated since Claroline 1.9, use Claroline::getDatabase() and new classes
 *  in database/database.lib.php instead
 */
function claro_sql_query_insert_id($sqlQuery, $dbHandler = '#')
{
    $result = claro_sql_query($sqlQuery, $dbHandler);

    if ($result)
    {
        if ($dbHandler == '#') return ((is_null($___mysqli_res = mysqli_insert_id($GLOBALS["___mysqli_ston"]))) ? false : $___mysqli_res);
        else                   return ((is_null($___mysqli_res = mysqli_insert_id($dbHandler))) ? false : $___mysqli_res);
    }
    else
    {
        return false;
    }
}

/**
 * Protect Sql statment
 *
 * @param unknown_type $statement
 * @param unknown_type $db
 * @return unknown
 * @deprecated since Claroline 1.9, use Claroline::getDatabase() and new classes
 *  in database/database.lib.php instead
 */
function claro_sql_escape($statement,$db=null)
{
    if (is_null($db)) return ((isset($GLOBALS["___mysqli_ston"]) && is_object($GLOBALS["___mysqli_ston"])) ? mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $statement) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : ""));
    else              return mysqli_real_escape_string( $db, $statement);

}
