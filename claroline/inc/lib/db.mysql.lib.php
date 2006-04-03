<?php // $Id$
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
 * return the tablename for a tool, en tenant compte du fait que it's (or not)
 * * in a course,
 * * in a group
 *
 * @param string $toolId
 * @param string $tableId
 * @param string $courseId
 * @param int $groupId
 * @return unknown
 */
function claro_sql_get_tbl($toolId,$tableId,$courseId=null,$groupId=null)
{
    /**
     * if it's in a course, $courseId is set or $courseId is null but not get_init('_cid')
     * if both are null, it's a main table
     *
     * when
     */

    if(is_null($courseId) && is_null(get_init('_cid'))) return get_conf('mainDbName') . '`.`' . get_conf('mainTblPrefix') . $tableId;
    else                                                return claro_get_course_db_name_glued($courseId) . $tableId;

}

/**
 * Get list of table names for central table.
 * @return array list of the central claroline database tables
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 */

function claro_sql_get_main_tbl()
{
    static $mainTblList = array();

    if ( count($mainTblList) == 0 )
    {
        $mainTblList= array (
        'config_property'           => get_conf('mainDbName') . '`.`' . get_conf('mainTblPrefix') . 'config_property',
        'config_file'               => get_conf('mainDbName') . '`.`' . get_conf('mainTblPrefix') . 'config_file',
        'admin'                     => get_conf('mainDbName') . '`.`' . get_conf('mainTblPrefix') . 'admin',
        'course'                    => get_conf('mainDbName') . '`.`' . get_conf('mainTblPrefix') . 'cours',
        'rel_course_user'           => get_conf('mainDbName') . '`.`' . get_conf('mainTblPrefix') . 'cours_user',
        'category'                  => get_conf('mainDbName') . '`.`' . get_conf('mainTblPrefix') . 'faculte',
        'user'                      => get_conf('mainDbName') . '`.`' . get_conf('mainTblPrefix') . 'user',
        'tool'                      => get_conf('mainDbName') . '`.`' . get_conf('mainTblPrefix') . 'course_tool',
        'user_category'             => get_conf('mainDbName') . '`.`' . get_conf('mainTblPrefix') . 'class',
        'user_rel_profile_category' => get_conf('mainDbName') . '`.`' . get_conf('mainTblPrefix') . 'rel_class_user',
        'class'                     => get_conf('mainDbName') . '`.`' . get_conf('mainTblPrefix') . 'class',
        'rel_class_user'            => get_conf('mainDbName') . '`.`' . get_conf('mainTblPrefix') . 'rel_class_user',
        'sso'                       => get_conf('mainDbName') . '`.`' . get_conf('mainTblPrefix') . 'sso',
        'notify'                    => get_conf('mainDbName') . '`.`' . get_conf('mainTblPrefix') . 'notify',
        'upgrade_status'            => get_conf('mainDbName') . '`.`' . get_conf('mainTblPrefix') . 'upgrade_status',
        'module'                    => get_conf('mainDbName') . '`.`' . get_conf('mainTblPrefix') . 'module',
        'module_info'               => get_conf('mainDbName') . '`.`' . get_conf('mainTblPrefix') . 'module_info',
        'dock'                      => get_conf('mainDbName') . '`.`' . get_conf('mainTblPrefix') . 'dock',

        'track_e_default'           => get_conf('statsDbName') . '`.`' . get_conf('statsTblPrefix') . 'track_e_default',
        'track_e_login'             => get_conf('statsDbName') . '`.`' . get_conf('statsTblPrefix') . 'track_e_login',
        'track_e_open'              => get_conf('statsDbName') . '`.`' . get_conf('statsTblPrefix') . 'track_e_open'
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

/**
 * Protect Sql statment
 *
 * @param unknown_type $statement
 * @param unknown_type $db
 * @return unknown
 */
function claro_sql_escape($statement,$db=null)
{
    if (is_null($db)) return mysql_real_escape_string($statement);
    else              return mysql_real_escape_string($statement, $db);

}
?>