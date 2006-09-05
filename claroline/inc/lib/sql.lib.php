<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );
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
 * @param array $tableList
 * @param array $contextData id To discrim table. Do not add context Id  of an context active but managed by tool.
 * @return array
 */
//function claro_sql_get_tbl($toolId,$tableId,$courseId=null,$groupId=null)
function claro_sql_get_tbl( $tableList, $contextData=null)
{
    /**
     * If it's in a course, $courseId is set or $courseId is null but not get_init('_cid')
     * if both are null, it's a main table
     *
     * when
     */

    if( ! is_array($tableList))
    {
        $tableListArr[] = $tableList;
        $tableList = $tableListArr;
    }
    else $tableList = $tableList;

    /**
     * Tool Context capatibility
     *
     * There is many context in claroline,
     * a new tool can  d'ont provide initially
     * all field to discrim each context ins  fields.
     * When a tool can't discrim a context,
     * the table would be duplicated for each instance
     * and the name of table (or db) contain the discriminator
     *
     * This extreme modularity provide an easy growing
     * and integration but
     * easy
     *
     * Easy can't mean slowly.
     * If  I prupose a blog tool wich can't discrim user
     * I need to duplicate all blog table (in same or separate db).
     */

    if (!is_array($contextData)) $contextData = array();

    if ( isset($GLOBALS['_courseTool']['label']) )
    {
        $toolId = rtrim($GLOBALS['_courseTool']['label'],'_');
    }
    else
    {
        $toolId = null;
    }

    $contextDependance = get_context_db_discriminator($toolId);

    // Now place discriminator in db & table name.
    // if a context is needed ($contextData) and $contextDependance is found,
    // add the discriminator in schema name or table prefix

    $schemaPrefix = array();

    if (is_array($contextDependance) )
    {
        if (array_key_exists('schema',$contextDependance))
        {
            if (array_key_exists(CLARO_CONTEXT_COURSE,$contextData)
            && !is_null($contextData[CLARO_CONTEXT_COURSE])
            && in_array(CLARO_CONTEXT_COURSE, $contextDependance['schema']))
            {
                $schemaPrefix[] = get_conf('courseTablePrefix') . claro_get_course_db_name($contextData[CLARO_CONTEXT_COURSE]);
            }
            if (array_key_exists('toolInstance',$contextData)
            && !is_null($contextData['toolInstance'])
            && in_array('toolInstance', $contextDependance['schema']))
            {
                $schemaPrefix[] = get_conf('dbPrefixForToolInstance', 'TI_')  . $contextData['toolInstance'];
            }
            if (array_key_exists('session',$contextData)
            && !is_null($contextData['session'])
            && in_array('session', $contextDependance['schema']))
            {
                $schemaPrefix[] = get_conf('dbPrefixForSession', 'S_') . $contextData['session'];
            }

            if (array_key_exists(CLARO_CONTEXT_GROUP,$contextData)
            && !is_null($contextData[CLARO_CONTEXT_GROUP])
            && in_array(CLARO_CONTEXT_GROUP, $contextDependance['schema'])
            )
            {
                $schemaPrefix[] = get_conf('dbPrefixForGroup', 'G_') . $contextData[CLARO_CONTEXT_GROUP];
            }
            if (array_key_exists(CLARO_CONTEXT_USER,$contextData)
            && !is_null($contextData[CLARO_CONTEXT_USER])
            && in_array(CLARO_CONTEXT_USER, $contextDependance['schema'])
            )
            {
                $schemaPrefix[] = get_conf('dbPrefixForUser', 'U_') . $contextData[CLARO_CONTEXT_USER] ;
            }
        }

        $tablePrefix = '';

        if (array_key_exists('table',$contextDependance))
        {
            if (array_key_exists(CLARO_CONTEXT_COURSE,$contextData)
            && !is_null($contextData[CLARO_CONTEXT_COURSE])
            && in_array(CLARO_CONTEXT_COURSE, $contextDependance['table']))
            {
                $tablePrefix .= 'C_' . $contextData[CLARO_CONTEXT_COURSE] . '_';
            }
            if (array_key_exists('toolInstance',$contextData)
            && !is_null($contextData['toolInstance'])
            && in_array('toolInstance', $contextDependance['table']))
            {
                $tablePrefix .= get_conf('dbPrefixForToolInstance', 'TI_') . $contextData['toolInstance'] . '_';
            }
            if (array_key_exists('session',$contextData)
            && !is_null($contextData['session'])
            && in_array('session', $contextDependance['table']))
            {
                $tablePrefix .= get_conf('dbPrefixForSession', 'S_') . $contextData['session'];
            }
            if (array_key_exists(CLARO_CONTEXT_GROUP,$contextData)
            && !is_null($contextData[CLARO_CONTEXT_GROUP])
            && in_array(CLARO_CONTEXT_GROUP, $contextDependance['table'])
            )
            {
                $tablePrefix .=  get_conf('dbPrefixForGroup', 'G_') . $contextData[CLARO_CONTEXT_GROUP] . '_';
            }
            if (array_key_exists(CLARO_CONTEXT_USER,$contextData)
            && !is_null($contextData[CLARO_CONTEXT_USER])
            && in_array(CLARO_CONTEXT_USER, $contextDependance['table'])
            )
            {
                $tablePrefix .= get_conf('dbPrefixForUser', 'U_') . $contextData[CLARO_CONTEXT_USER] . '_';
            }

        }
    }

    //$schemaPrefix = (0==count($schemaPrefix) ? get_conf('mainDbName') : implode(get_conf('dbGlu'),$schemaPrefix)); // ne pas utiliser dbGlu tant qu'il peut valoir .
    $schemaPrefix = (0 == count($schemaPrefix) ? get_conf('mainDbName') : implode('_',$schemaPrefix));
    $tablePrefix  = ('' == $tablePrefix) ? get_conf('mainTblPrefix') : $tablePrefix;

    foreach ($tableList as $tableId)
    {
        /**
         *  Read this  to understand chanche  since  previous version thant 1.8
         *
         * Until 1.8  there was 2 functions
         *
         * function claro_sql_get_main_tbl()
         * function claro_sql_get_course_tbl($dbNameGlued = null)
         *
         * both was using  conf values
         * claro_sql_get_main_tbl was using  conf values
         * * get_conf('mainDbName')
         * * get_conf('mainTblPrefix')
         *
         */
        $tableNameList[$tableId] = $schemaPrefix . '`.`' . $tablePrefix . $tableId;
    }

    return $tableNameList;
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
        'course'                    => get_conf('mainDbName') . '`.`' . get_conf('mainTblPrefix') . 'cours',
        'rel_course_user'           => get_conf('mainDbName') . '`.`' . get_conf('mainTblPrefix') . 'cours_user',
        'category'                  => get_conf('mainDbName') . '`.`' . get_conf('mainTblPrefix') . 'faculte',
        'user'                      => get_conf('mainDbName') . '`.`' . get_conf('mainTblPrefix') . 'user',
        'tool'                      => get_conf('mainDbName') . '`.`' . get_conf('mainTblPrefix') . 'course_tool',
        'user_category'             => get_conf('mainDbName') . '`.`' . get_conf('mainTblPrefix') . 'class',
        'user_rel_profile_category' => get_conf('mainDbName') . '`.`' . get_conf('mainTblPrefix') . 'rel_class_user',
        'class'                     => get_conf('mainDbName') . '`.`' . get_conf('mainTblPrefix') . 'class',
        'rel_class_user'            => get_conf('mainDbName') . '`.`' . get_conf('mainTblPrefix') . 'rel_class_user',
        'rel_course_class'          => get_conf('mainDbName') . '`.`' . get_conf('mainTblPrefix') . 'rel_course_class',
        'sso'                       => get_conf('mainDbName') . '`.`' . get_conf('mainTblPrefix') . 'sso',
        'notify'                    => get_conf('mainDbName') . '`.`' . get_conf('mainTblPrefix') . 'notify',
        'upgrade_status'            => get_conf('mainDbName') . '`.`' . get_conf('mainTblPrefix') . 'upgrade_status',
        'module'                    => get_conf('mainDbName') . '`.`' . get_conf('mainTblPrefix') . 'module',
        'module_info'               => get_conf('mainDbName') . '`.`' . get_conf('mainTblPrefix') . 'module_info',
        'dock'                      => get_conf('mainDbName') . '`.`' . get_conf('mainTblPrefix') . 'dock',
        'right_profile'             => get_conf('mainDbName') . '`.`' . get_conf('mainTblPrefix') . 'right_profile',
        'right_rel_profile_action'  => get_conf('mainDbName') . '`.`' . get_conf('mainTblPrefix') . 'right_rel_profile_action',
        'right_action'              => get_conf('mainDbName') . '`.`' . get_conf('mainTblPrefix') . 'right_action',
        'user_property'             => get_conf('mainDbName') . '`.`' . get_conf('mainTblPrefix') . 'user_property',
        'property_definition'       => get_conf('mainDbName') . '`.`' . get_conf('mainTblPrefix') . 'property_definition',
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
              'course_properties'      => $courseDbInCache . 'course_properties',
              'group_property'         => $courseDbInCache . 'group_property',
              'group_rel_team_user'    => $courseDbInCache . 'group_rel_team_user',
              'group_team'             => $courseDbInCache . 'group_team',
              'lp_learnPath'           => $courseDbInCache . 'lp_learnPath',
              'lp_rel_learnPath_module'=> $courseDbInCache . 'lp_rel_learnPath_module',
              'lp_user_module_progress'=> $courseDbInCache . 'lp_user_module_progress',
              'lp_module'              => $courseDbInCache . 'lp_module',
              'lp_asset'			   => $courseDbInCache . 'lp_asset',
              'qwz_exercise'              	=> $courseDbInCache . 'qwz_exercise' ,
              'qwz_question'          		=> $courseDbInCache . 'qwz_question',
              'qwz_rel_exercise_question' 	=> $courseDbInCache . 'qwz_rel_exercise_question',
              'qwz_answer_truefalse'		=> $courseDbInCache . 'qwz_answer_truefalse',
              'qwz_answer_multiple_choice'	=> $courseDbInCache . 'qwz_answer_multiple_choice',
              'qwz_answer_fib'            	=> $courseDbInCache . 'qwz_answer_fib',
              'qwz_answer_matching'       	=> $courseDbInCache . 'qwz_answer_matching',
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

if ( count( get_included_files() ) == 1 ) die( '---' );
/**
 * CLAROLINE mySQL query wrapper. It also provides a debug display which works
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

    if ( get_conf('CLARO_DEBUG_MODE',false)
      && get_conf('CLARO_PROFILE_SQL',false)
      )
      {
         $start = microtime();
      }
    if ( $dbHandler == '#')
    {
        $resultHandler =  @mysql_query($sqlQuery);
    }
    else
    {
        $resultHandler =  @mysql_query($sqlQuery, $dbHandler);
    }

    if ( get_conf('CLARO_DEBUG_MODE',false)
      && get_conf('CLARO_PROFILE_SQL',false)
      )
    {
        static $queryCounter = 1;
        $duration = microtime()-$start;
        $info = 'execution time : ' . ($duration > 0.001 ? '<b>' . round($duration,4) . '</b>':'&lt;0.001')  . '&#181;s'  ;
        //$info = ( $dbHandler == '#') ? mysql_info() : mysql_info($dbHandler);
        $info .= ': affected rows :' . (( $dbHandler == '#') ? mysql_affected_rows() : mysql_affected_rows($dbHandler));
        pushClaroMessage( '<br>Query counter : <b>' . $queryCounter++ . '</b> : ' . $info ,'sqlinfo');
        pushClaroMessage( '<code><span class="sqlcode">' . nl2br($sqlQuery) . '</span></code>', (mysql_errno()?'error':'sqlinfo'));

    }
if ( get_conf('CLARO_DEBUG_MODE',false)  && mysql_errno() )
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
 * @param string $sq - SQL query
 * @param ressource (optional) - result pointer
 * @return  names of the specified field index
 *
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 *
 */

function claro_sql_field_names( $sql, $resultPt = null )
{
    static $_colNameList = array();

    $sqlHash = md5($sql);

    if ( ! array_key_exists( $sqlHash, $_colNameList) )
    {
        if ( is_resource($resultPt) && get_resource_type($resultPt) == 'mysql result' )
        {
            // if ressource type is mysql result use it
            $releasablePt = false;
        }
        else
        {
            $resultPt     = claro_sql_query($sql);
            $releasablePt = true;
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

if ( count( get_included_files() ) == 1 ) die( '---' );
/**
 * CLAROLINE SQL query and fetch array wrapper. It returns all the result rows
 * in an associative array.
 *
 * @param  string  $sqlQuery the sql query
 * @param  handler $dbHandler optional
 * @return array associative array containing all the result rows
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 */

function claro_sql_query_fetch_all_rows($sqlQuery, $dbHandler = '#')
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

function claro_sql_query_fetch_all($sqlQuery, $dbHandler = '#')
{
    return claro_sql_query_fetch_all_rows($sqlQuery, $dbHandler);
}

if ( count( get_included_files() ) == 1 ) die( '---' );
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


if ( count( get_included_files() ) == 1 ) die( '---' );
/**
 * CLAROLINE SQL query wrapper returning only a single result value.
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

if ( count( get_included_files() ) == 1 ) die( '---' );
/**
 * CLAROLINE SQL query wrapper returning only the first row of the result
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



if ( count( get_included_files() ) == 1 ) die( '---' );
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
 *
 */


function claro_sql_query_affected_rows($sqlQuery, $dbHandler = '#')
{
    $result = claro_sql_query($sqlQuery, $dbHandler);

    if ($result)
    {
        if ($dbHandler == '#') return mysql_affected_rows();
        else                   return mysql_affected_rows($dbHandler);

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

if ( count( get_included_files() ) == 1 ) die( '---' );
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



/**
 * Return an array of 2 array containing context wich can't be manage by tool
 * and where to store the discriminator.
 *
 * By default it's in table name except of course context wich follow singleDbMode value.
 *
 * @param string $toolId claro_label
 * @return array of array
 *
 * @since 1.8
 */

//Temporary included
require_once(dirname(__FILE__) . '/module.lib.php');
function get_context_db_discriminator($toolId)
{

    // array ( CLARO_CONTEXT_USER, CLARO_CONTEXT_COURSE, CLARO_CONTEXT_GROUP, 'toolInstance', 'session')

    // This fixed result would became result of config
    // Admin can select for each context for each tool,
    // if the descriminator needed (because not managed by tool )
    // would be placed in table name or schema name.

 // switch n'as plus trop de sens ici.
 // le default  devrait probablement sortir
 // et le swtich des debrayage dans if (!get_conf('singleDbEnabled'))
 // parce que si singleDbEnabled =true $genericConfig['schema'] DOIT tre vide

    switch ($toolId)
    {
// ie        case 'CLANN' : return array('schema' => array (CLARO_CONTEXT_COURSE), 'table' => array(CLARO_CONTEXT_GROUP));
// ie        case 'CLWIKI' : return array('schema' => array (CLARO_CONTEXT_COURSE,CLARO_CONTEXT_GROUP));
        default:
            $dependance = get_module_db_dependance($toolId);

            // By default all is in tableName except for course wich follow singleDbEnabled;
            $genericConfig['table'] = $dependance ;
            if(is_array($dependance) && in_array(CLARO_CONTEXT_COURSE,$dependance))
            {
                if (!get_conf('singleDbEnabled'))
                {
                    $genericConfig['schema'] = array(CLARO_CONTEXT_COURSE);
                    $genericConfig['table'] = array_diff ($genericConfig['table'],$genericConfig['schema'] );
                }
            }
            return $genericConfig;
    }

}

?>
