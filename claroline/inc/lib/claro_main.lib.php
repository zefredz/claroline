<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );
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
 * SECTION : PHP COMPAT For PHP backward compatibility
 */

require_once(dirname(__FILE__) . '/compat.lib.php');

/**
 * SECTION :  Class & function to prepare a normalised html output.
 */

require_once(dirname(__FILE__) . '/html.lib.php');

/**
 * SECTION :  Class & function to get text zone contents.
 */

require_once(dirname(__FILE__) . '/textzone.lib.php');

/**
 * SECTION :  Modules functions
 */
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

function claro_get_course_data($courseId = NULL, $force = false )
{
    $courseDataList = null;

    static $cachedDataList = null;

    if ( ! $force)
    {
        if ( $cachedDataList && $courseId == $cachedDataList['sysCode'] )
        {
            $courseDataList = $cachedDataList;
        }
        elseif ( ( is_null($courseId) && $GLOBALS['_cid']) )
        {
            $courseDataList = $GLOBALS['_course'];
        }
    }

    if ( ! $courseDataList )
    {
        $tbl_mdb_names =  claro_sql_get_main_tbl();

        $sql =  "SELECT
                c.code              AS sysCode,
                c.cours_id          AS courseId,
                c.intitule          AS name,
                c.fake_code         AS officialCode,
                c.directory         AS path,
                c.dbName            AS dbName,
                c.titulaires        AS titular,
                c.email             AS email  ,
                c.enrollment_key    AS enrollmentKey ,
                c.languageCourse    AS language,
                c.departmentUrl     AS extLinkUrl,
                c.departmentUrlName AS extLinkName,
                c.visible           AS visible,
                cat.code            AS categoryCode,
                cat.name            AS categoryName,
                c.diskQuota         AS diskQuota

                FROM      `" . $tbl_mdb_names['course'] . "`   AS c
                LEFT JOIN `" . $tbl_mdb_names['category'] . "` AS cat
                        ON c.faculte =  cat.code
                WHERE c.code = '" . addslashes($courseId) . "'";

        $courseDataList = claro_sql_query_get_single_row($sql);

        if ( ! $courseDataList ) return claro_failure::set_failure('course_not_found');

        $courseDataList['visibility'         ] = (bool) (2 == $courseDataList['visible'] || 3 == $courseDataList['visible'] );
        $courseDataList['registrationAllowed'] = (bool) (1 == $courseDataList['visible'] || 2 == $courseDataList['visible'] );
        $courseDataList['dbNameGlu'          ] = get_conf('courseTablePrefix') . $courseDataList['dbName'] . get_conf('dbGlu'); // use in all queries

        $cachedDataList = $courseDataList; // cache for the next time ...
    }

    return $courseDataList;
}

/**
 * This function return properties for groups in a given course context.
 *
 * @param string $courseId sysCode of the course.
 *
 * @return array ('registrationAllowed' ,
                  'self_registration',
                  'private',
                  'nbGroupPerUser',
                  'tools' => array ('CLFRM',
                                    'CLDOC',
                                    'CLWIKI',
                                    'CLCHT')
                                    )


 * The 4th first properties  are course properties dedicated to groups as default value.
 * The 'tool' array is like course.tool_list.
 */

function claro_get_main_group_properties($courseId)
{
    $tbl_cdb_names = claro_sql_get_course_tbl( claro_get_course_db_name_glued($courseId) );
    $tbl_course_properties   = $tbl_cdb_names['course_properties'];

    $sql = "SELECT name,
                   value
            FROM `" . $tbl_course_properties . "`
            WHERE category = 'GROUP'";

    $dbDataList = claro_sql_query_fetch_all($sql);

    if (is_array($dbDataList) )
    {
        foreach($dbDataList as $thisData)
        {
            $tempList[$thisData['name']] = (int) $thisData['value'];
        }

        $propertyList = array();

        $propertyList ['registrationAllowed'] =  ($tempList['self_registration'] == 1);
        $propertyList ['private'            ] =  ($tempList['private']           == 1);
        $propertyList ['nbGroupPerUser'     ] =  $tempList['nbGroupPerUser'];

        $propertyList['tools'] = array();

        $propertyList ['tools'] ['CLFRM'    ] =   ($tempList['CLFRM']             == 1);
        $propertyList ['tools'] ['CLDOC'    ] =   ($tempList['CLDOC']             == 1);
        $propertyList ['tools'] ['CLWIKI'   ] =   ($tempList['CLWIKI']            == 1);
        $propertyList ['tools'] ['CLCHT'    ] =   ($tempList['CLCHT']             == 1);

        return $propertyList;
    }
    else
    {
    	return false;
    }
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
    return claro_get_module_name_list();
}

function claro_get_module_name_list($active = true)
{
    static $toolNameList;

    $tbl_mdb_names   = claro_sql_get_main_tbl();
    $tbl_module      = $tbl_mdb_names['module'];

    if( ! isset( $toolNameList ) )
    {
        $toolNameList = array('CLANN' => 'Announcement'
        ,                     'CLFRM' => 'Forums'
        ,                     'CLCAL' => 'Agenda'
        ,                     'CLCHT' => 'Chat'
        ,                     'CLDOC' => 'Documents and Links'
        ,                     'CLDSC' => 'Course description'
        ,                     'CLGRP' => 'Groups'
        ,                     'CLLNP' => 'Learning path'
        ,                     'CLQWZ' => 'Exercises'
        ,                     'CLWRK' => 'Assignments'
        ,                     'CLUSR' => 'Users'
        ,                     'CLWIKI' => 'Wiki'
        );
    }

    //add in result the module of type 'tool'

    //see if we take only activated modules or all of them

    if ($active)
    {
        $activationSQL = " AND `activation`='activated'";
    }
    else
    {
        $activationSQL = "";
    }

    //find tool modules

    $sql = "SELECT `label`, `name` FROM `" . $tbl_module . "`
                            WHERE `type`='tool'
                              ".$activationSQL;

    $result = claro_sql_query_fetch_all($sql);

    //add them in result array

    foreach ($result as $tool)
    {

        if (!isset($toolNameList[$tool['label']]))
        {
            $toolNameList[$tool['label']] = $tool['name'];
        }
    }

    return $toolNameList;
}

/**
 * SECTION :  Get kernel
 * SUBSECTION datas for rel tool courses
 */

/**
 * Return the list of tool installed on the platform
 *
 * @param  boolean $force (optionnal) - reset the result cache, default is false
 *
 * @return array the main course list array ( $tid => 'label','name','url','icon','activation' )
 */

function claro_get_main_course_tool_list ( $force = false )
{
    static $courseToolList = null ;

    if ( is_null($courseToolList) || $force )
    {
        // Initialise course tool list
        $courseToolList = array();

        // Get name of the tables
        $tbl_mdb_names        = claro_sql_get_main_tbl();
        $tbl_tool_list        = $tbl_mdb_names['tool'];
        $tbl_module           = $tbl_mdb_names['module'];

        // Find module tools
        $sql = "SELECT t.id,
                       t.claro_label as label,
                       m.name,
                       m.activation,
                       t.icon,
                       t.script_url as url
                FROM `" . $tbl_module . "` as m,
                     `" . $tbl_tool_list . "` as t
               WHERE t.claro_label = m.label
                 AND m.type = 'tool'
               ORDER BY t.def_rank";

        $courseToolResult = claro_sql_query_fetch_all($sql);

        // Fill course tool list
        foreach ( $courseToolResult as $courseTool )
        {
            $toolId = $courseTool['id'];

            $courseToolList[$toolId]['label'] = $courseTool['label'];
            $courseToolList[$toolId]['name'] = $courseTool['name'];
            $courseToolList[$toolId]['url'] = get_module_url($courseTool['label']) . '/' . $courseTool['url'] ;

            if ( !empty($courseTool['icon']) )
            {
                $courseToolList[$toolId]['icon'] = get_module_url($courseTool['label']) . $courseTool['icon'];
            }
            else
            {
                $courseToolList[$toolId]['icon'] = $GLOBALS['imgRepositoryWeb'] .'/tool.gif';
            }

            if ( $courseTool['activation'] == 'activated' )
            {
                $courseToolList[$toolId]['activation'] = true;
            }
            else
            {
                $courseToolList[$toolId]['activation'] = false;
            }
        }
    }

    return $courseToolList ;
}

/**
 * Return the tool list for a course according a certain access level
 * @param  string  $courseIdReq - the requested course id
 *
 * @param  boolean $force (optionnal)  - reset the result cache, default is false
 * @param  boolean $active (optionnal) - get the list of active tool only if set to true (default behaviour)
 * @return array   the course list
 */

function claro_get_course_tool_list($courseIdReq, $profileIdReq, $force = false, $active=true )
{
    static $courseToolList = null, $courseId = null, $profileId = null;

    if (   is_null($courseToolList)
        || $courseId    != $courseIdReq
        || $profileId   != $profileIdReq
        || $force )
    {
        $courseId   = $courseIdReq;
        $profileId  = $profileIdReq;

        $tbl_mdb_names        = claro_sql_get_main_tbl();
        $tbl_tool_list        = $tbl_mdb_names['tool'];
        $tbl_module           = $tbl_mdb_names['module'];
        $tbl_cdb_names        = claro_sql_get_course_tbl( claro_get_course_db_name_glued($courseIdReq) );
        $tbl_course_tool_list = $tbl_cdb_names['tool'];

        /*
         * Search all the tool corresponding to this access levels
         */

        // find module or claroline existing tools

        $sql = "SELECT DISTINCT ctl.id            AS id,
                      pct.id                      AS tool_id,
                      pct.claro_label             AS label,
                      ctl.script_name             AS external_name,
                      ctl.visibility              AS visibility,
                      IFNULL(pct.icon,'tool.gif') AS icon,
                      ISNULL(ctl.tool_id)         AS external,
                      m.activation ,
                      m.name                      AS name,
                      IFNULL( ctl.script_url ,
                              pct.script_url )    AS url
               FROM `" . $tbl_course_tool_list . "` AS ctl,
                    `" . $tbl_module . "`           AS m,
                    `" . $tbl_tool_list . "`        AS pct

               WHERE pct.id = ctl.tool_id
                 AND pct.claro_label = m.label
                 ". ($active ? " AND m.activation = 'activated' " :"") . "
               ORDER BY external, pct.def_rank, ctl.rank";

        $courseToolList = claro_sql_query_fetch_all($sql);

        // right profile management

        $size = count($courseToolList);

        for ( $i=0 ; $i<$size ; $i++ )
        {
            $toolId = $courseToolList[$i]['tool_id'];
            $visibility = (bool) $courseToolList[$i]['visibility'];

            // delete tool from course tool list if :
            // 1. tool is invisible and profile has no right to edit tool
            // 2. profile has no right to view tool
            if ( ( $visibility == false && ! claro_is_allowed_tool_edit($toolId,$profileId,$courseId) )
                || ! claro_is_allowed_tool_read($toolId,$profileId,$courseId) )
            {
                unset($courseToolList[$i]);
            }
        }

        // find external url added by teacher

        $sql = "SELECT DISTINCT ctl.id            AS id,
                      NULL                        AS tool_id,
                      NULL                        AS label,
                      ctl.script_name             AS external_name,
                      ctl.visibility              AS visibility,
                      'tool.gif'                  AS icon,
                      ISNULL(ctl.tool_id)         AS external,
                      NULL                        AS name,
                      ctl.script_url              AS url

               FROM `" . $tbl_course_tool_list . "` AS ctl
               WHERE ISNULL(ctl.tool_id) ";

        if ( ! get_init('is_courseAdmin') )
        {
            $sql .= 'AND ctl.visibility = 1';
        }

        $result = claro_sql_query_fetch_all($sql);

        $courseToolList = array_merge($courseToolList,$result);
    }

    return $courseToolList;
}

/**
 * Return the tool list for a course according a certain access level
 *
 * @param  boolean $force (optionnal) - reset the result cache, default is false
 *
 * @return array the main course list array ( $id => 'name','url','icon','visibility' )
 */

function claro_get_course_external_link_list ( $courseIdReq = null, $force = false )
{
    static $courseExtLinkList = null, $courseId = null ;

    if ( is_null($courseIdReq) )
    {
        $courseIdReq = get_init('_cid');
    }

    if ( is_null($courseExtLinkList)
        || $courseIdReq != $courseId
        || $force )
    {
        // Initialise course tool list
        $courseId = $courseIdReq;
        $courseExtLinkList = array();

        // Get name of the tables
        $tbl_cdb_names        = claro_sql_get_course_tbl( claro_get_course_db_name_glued($courseIdReq) );
        $tbl_course_tool_list = $tbl_cdb_names['tool'];

        // Find external link
        $sql = "SELECT id,
                       script_name,
                       script_url,
                       visibility
               FROM `" . $tbl_course_tool_list . "`
               WHERE ISNULL(tool_id) ";

        if ( ! ( get_init('is_courseAdmin') || get_init('is_platformAdmin') ) )
        {
            $sql .= 'AND visibility = 1';
        }

        $courseExtLinkResult = claro_sql_query_fetch_all($sql);

        foreach ( $courseExtLinkResult as $courseExtLink )
        {
            $id = $courseExtLink['id'];
            $courseExtLinkList[$id]['name'] = $courseExtLink['script_name'];
            $courseExtLinkList[$id]['url'] = $courseExtLink['script_url'];
            $courseExtLinkList[$id]['icon'] = $GLOBALS['imgRepositoryWeb'] .'/tool.gif';
            $courseExtLinkList[$id]['visibility'] = (bool) $courseExtLink['visibility'];
        }
    }

    return $courseExtLinkList;
}

/**
 * Get the name of a tool
 * @param string identifier is tool_id or tool_label
 * @return string tool name
 */

function claro_get_tool_name ( $identifier )
{
    return claro_get_module_name($identifier);
}

function claro_get_module_name ( $identifier )
{
    static $cachedModuleIdList = null ;
    if ( is_numeric($identifier) )
    {
        // identifier is a tool_id
        if ( ! $cachedModuleIdList )
        {
            $tbl = claro_sql_get_main_tbl();

            $sql = "SELECT id      AS toolId,
                       claro_label AS claroLabel
                    FROM `" . $tbl['tool'] . "`";

            $moduleList = claro_sql_query_fetch_all($sql);

            foreach ($moduleList as $thisModule)
            {
                $toolId = $thisModule['toolId'];
                $claroLabel =  $thisModule['claroLabel'];
                $cachedModuleIdList[$toolId] = $claroLabel;
            }
        }
        // get tool label of the tool
        $toolLabel = $cachedModuleIdList[$identifier];
    }
    else
    {
        // identifier is a tool label
        $toolLabel = $identifier;
    }

    $toolNameList = claro_get_tool_name_list();

    if ( isset($toolNameList[$toolLabel]) )
    {
        return $toolNameList[$toolLabel];
    }

    if ( isset($toolNameList[$toolLabel]) )
    {

        $moduleData = get_module_data($toolLabel);
        if (is_array($moduleData))
        {
            $moduleName = $moduleData['moduleName'];
            return  get_lang($moduleName);
        }
    }

    return get_lang('No tool name') ;

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

    $url = str_replace('&viewMode=STUDENT'     , '', $url);
    $url = str_replace('&viewMode=COURSE_ADMIN', '', $url);

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
    global $is_courseAdmin, $_tid;

    if ( $is_courseAdmin )
    {
        $isAllowedToEdit = $is_courseAdmin ;
    }
    else
    {
        if ( !empty($_tid) )
        {
            $isAllowedToEdit = claro_is_allowed_tool_edit();
        }
        else
        {
            $isAllowedToEdit = $is_courseAdmin ;
        }
    }

    if ( claro_is_display_mode_available() )
    {
        return $isAllowedToEdit && (claro_get_tool_view_mode() != 'STUDENT');
    }
    else
    {
        return $isAllowedToEdit ;
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


function claro_get_conf_repository()
{
    return get_conf('rootSys') . 'platform/conf/';
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
    if (CLARO_DEBUG_MODE)
    {

        if ( ! isset($GLOBALS['_conf'][$param]) && ! isset($GLOBALS[$param]) && !defined($param))
        {
            static $paramList = array();

            if (!in_array($param,$paramList))
            {
                $paramList[]=$param;
                pushClaroMessage($param . ' use but not set. use default :' . var_export($default,1),'warning');
            }
        }
    }

    if     ( isset($GLOBALS['_conf'][$param]) )  return $GLOBALS['_conf'][$param];
    elseif ( isset($GLOBALS[$param]) )           return $GLOBALS[$param];
    elseif ( defined($param)         )           return constant($param);
    else                                         return $default;
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
        global $includePath, $claro_stylesheet, $urlAppend ,
               $siteName, $text_dir, $_uid, $_cid, $administrator_name, $administrator_email;
        global $_course, $_user, $_courseToolList, $coursesRepositoryWeb,
               $is_courseAllowed, $_tid, $is_courseMember, $_gid;
        global $claroBodyOnload, $httpHeadXtra, $htmlHeadXtra, $charset, $interbredcrump,
               $noPHP_SELF, $noQUERY_STRING;
        global $institution_name, $institution_url, $hide_banner, $hide_footer, $hide_body;

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

            $inputList = array(&$_REQUEST, &$_GET, &$_POST, &$_COOKIE);

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
                                 , 'is_authenticated'
                                 , 'in_course_context'
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
                                 , 'is_courseAllowed'
                                 , 'is_allowedCreateCourse'
                                 , 'is_groupMember'
                                 , 'is_groupTutor'
                                 , 'is_groupAllowed'
                                 , 'is_toolAllowed'
                                 );

    if(!in_array($param, $initValueList )) trigger_error( htmlentities($param) . ' is not a know init value name ', E_USER_NOTICE);
    //TODO create a real auth function to eval this state
    if ( $param == 'is_authenticated') return (bool) array_key_exists($param,$GLOBALS);
    //TODO create a real course function to eval this state
    if ( $param == 'in_course_context') return (bool) array_key_exists($param,$GLOBALS);
    if     ( array_key_exists($param,$GLOBALS) )  return $GLOBALS[$param];
    elseif ( defined($param)         )            return constant($param);
    return null;
}

/**
 * Return a common path of claroline
 *
 * @param string $pathKey key name of the path ( varname in previous version of claroline)
 * @return path
 */
function get_path($pathKey)
{
    switch ($pathKey)
    {
        case 'includePath'            : return dirname( dirname(__FILE__) );
        case 'incRepositorySys'       : return dirname( dirname(__FILE__) );

        case 'rootSys' : return get_conf('rootSys') ;
        case 'rootWeb' : return get_conf('rootWeb') ;

        case 'imgRepositoryAppend'       : return 'img/'; // <-this line would be editable in claroline 1.7
        case 'clarolineRepositoryAppend' : return get_conf('clarolineRepositoryAppend','claroline/');
        case 'coursesRepositoryAppend'   : return get_conf('coursesRepositoryAppend','courses/');
        case 'rootAdminAppend'           : return get_conf('rootAdminAppend','admin/');

        case 'clarolineRepositorySys' : return get_conf('rootSys') . get_conf('clarolineRepositoryAppend','claroline/');
        case 'clarolineRepositoryWeb' : return get_conf('urlAppend') . '/' . get_conf('clarolineRepositoryAppend','claroline/');
        case 'userImageRepositorySys' : return get_conf('rootSys') . get_conf('userImageRepositoryAppend','platform/img/users/');
        case 'userImageRepositoryWeb' : return get_conf('urlAppend') . '/' . get_conf('userImageRepositoryAppend','platform/img/users/');
        case 'coursesRepositorySys'   : return get_conf('rootSys') . get_conf('coursesRepositoryAppend','courses/');
        case 'coursesRepositoryWeb'   : return get_conf('urlAppend') . '/' . get_conf('coursesRepositoryAppend','courses/');
        case 'rootAdminSys'           : return get_path('clarolineRepositorySys') . get_conf('rootAdminAppend','admin/');
        case 'rootAdminWeb'           : return get_path('clarolineRepositoryWeb') . get_conf('rootAdminAppend','admin/');
        case 'imgRepositorySys'       : return get_path('clarolineRepositorySys') . get_path('imgRepositoryAppend');
        case 'imgRepositoryWeb'       : return get_path('clarolineRepositoryWeb') . get_path('imgRepositoryAppend');

        default : pushClaroMessage($pathKey . 'is an unknow path');
        return false;
    }

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

    if(!is_null($contextKeys) && !is_array($contextKeys)) $contextKeys = array($contextKeys);

    if((is_null($contextKeys) || in_array(CLARO_CONTEXT_COURSE,$contextKeys))       && !is_null($GLOBALS['_cid'])) $currentKeys[CLARO_CONTEXT_COURSE]       = $GLOBALS['_cid'];
    if((is_null($contextKeys) || in_array(CLARO_CONTEXT_GROUP,$contextKeys))        && !is_null($GLOBALS['_gid'])) $currentKeys[CLARO_CONTEXT_GROUP]        = get_init('_gid');
    if((is_null($contextKeys) || in_array(CLARO_CONTEXT_USER,$contextKeys))         && !is_null($GLOBALS['_uid'])) $currentKeys[CLARO_CONTEXT_USER]         = get_init('_uid');
    //if((is_null($contextKeys) || in_array('session',$contextKeys))      && !is_null($GLOBALS['_sid']))  $currentKeys['session']       = get_init('_sid');
    if((is_null($contextKeys) || in_array('toolInstance',$contextKeys)) && !is_null($GLOBALS['_tid'])) $currentKeys['toolInstance'] = get_init('_tid');

    return $currentKeys;
}


/**
 * Developper function to push a message in stack of devs messages
 * in debug mod this stack is output in footer
 * @author Christophe Gesché <moosh@claroline.net>
 */
if (!isset($claroErrorList)) $claroErrorList= array();
function pushClaroMessage($message,$errorClass='error')
{
    global $claroErrorList;
    $claroErrorList[$errorClass][]= $message;
    return true;
}

/**
 * get stack of devel message
 */
function getClaroMessageList($errorClass=null)
{
    if (isset($GLOBALS['claroErrorList']))
    {
        $claroErrorList = $GLOBALS['claroErrorList'];
        if (is_null($errorClass)) $returnedClaroErrorList = $claroErrorList;
        else
        {
            if (array_key_exists($errorClass,$claroErrorList))
            {
                $returnedClaroErrorList[$errorClass] = $claroErrorList[$errorClass];
            }
            else $returnedClaroErrorList[]=array();
        }
    }
    else $returnedClaroErrorList[]=array();

    return $returnedClaroErrorList;
}


/**
 * Return the list of tools for a user
 *
 *  in 1.8 only  CLCAL are both  course tool and user tool.
 *  ie : profile is'nt view as module,
 *  and other course tool can't work outside a course for a user.
 *
 * @param boolean $activeOnly default true
 * @return array of tools
 */
function claro_get_user_tool_list($activeOnly=true)
{
     $toolDataList= array();
     $toolData = get_module_data('CLCAL');

     if (false !== $toolData && (!$activeOnly || $toolData['activation'] != 'desactivated'))
     {
         $toolData['entry'] = 'myagenda.php';
         $toolDataList[]=$toolData;
     }
     return $toolDataList;
}

/**
 * Safe redirect
 * Works around IIS Bug
 */

function claro_redirect($location)
{
    global $is_IIS;

    $location = http_response_splitting_workaround($location);

    if ($is_IIS)
        header("Refresh: 0;url=$location");
    else
        header("Location: $location");
}

?>