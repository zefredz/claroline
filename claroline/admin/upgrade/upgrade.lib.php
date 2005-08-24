<?php // $Id$
/**
 * CLAROLINE 
 *
 * The script works with the 
 *
 * @version 1.7 $Revision$
 *
 * @copyright (c) 2001-2005 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE 
 *
 * @package UPGRADE
 *
 * @author Claro Team <cvs@claroline.net>
 * @author Mathieu Laurent <mathieu@claroline.net>
 */

/**
 * Initialise upgrade tool and its global variables
 *
 * @param string claroline version
 * @param string database version
 *
 * @since  1.7
 */

function upgrade_init_global()
{

    global $accepted_error_list;
    global $currentClarolineVersion, $currentDbVersion;
    global $new_version, $new_version_branch;

    /**
     * misc: define function mysql_info if not exists
     */

    if ( !function_exists('mysql_info') )
    {

        /**
         * This is a fake function declared if mysql_info don't exist
         * The output is use for additional info.
         * @return string empty.
         */
        function mysql_info() {return '';} // mysql_info is used in verbose mode
    }

    /**
     * List of accepted error - See MySQL error codes : 
     *
     * Error: 1017 SQLSTATE: HY000 (ER_FILE_NOT_FOUND) : already upgraded
     * Error: 1050 SQLSTATE: 42S01 (ER_TABLE_EXISTS_ERROR) : already upgraded
     * Error: 1054 SQLSTATE: 42S22 (ER_BAD_FIELD_ERROR) : Unknown column '%s' in '%s'
     * Error: 1060 SQLSTATE: 42S21 (ER_DUP_FIELDNAME)  : already upgraded
     * Error: 1062 SQLSTATE: 23000 (ER_DUP_ENTRY) : duplicate entry '%s' for key %d
     * Error: 1065 SQLSTATE: 42000 (ER_EMPTY_QUERY) : when  sql contain only a comment
     * Error: 1091 SQLSTATE: 42000 (ER_CANT_DROP_FIELD_OR_KEY) : Can't DROP '%s'; check that column/key exists
     * Error: 1146 SQLSTATE: 42S02 (ER_NO_SUCH_TABLE) : already upgraded
     * @see http://dev.mysql.com/doc/mysql/en/error-handling.html
     */

    $accepted_error_list = array(1017,1050,1054,1060,1062,1065,1091,1146);

    /*
     * Initialize version variables
     */

    // Current Version
    $current_version = get_current_version();
    $currentClarolineVersion = $current_version['claroline'];
    $currentDbVersion = $current_version['db'];

    // New Version
    $this_new_version = get_new_version();
    $new_version = $this_new_version['complete'];
    $new_version_branch = $this_new_version['branch'];

}

/**
 * Display header of the upgrade tool
 *
 * @param string claroline version
 * @param string database version
 *
 * @since  1.7
 */

function upgrade_disp_header()
{
    global $htmlHeadXtra, $text_dir;
    global $new_version, $langUpgrade;

    $output = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">

<head>
  <meta http-equiv="Content-Type" content="text/HTML; charset=iso-8859-1"  />
  <title>-- Claroline upgrade -- version ' . $new_version . '</title>
  <link rel="stylesheet" type="text/css" href="upgrade.css" media="screen" />
  <style media="print" >
    .notethis {    border: thin double Black;    margin-left: 15px;    margin-right: 15px;}
  </style>';

if ( !empty($htmlHeadXtra) && is_array($htmlHeadXtra) )
{
    foreach($htmlHeadXtra as $thisHtmlHead)
    {
        $output .= $thisHtmlHead ;
    }
}

$output .='</head>
<body bgcolor="white" dir="' . $text_dir . '">

<center>

<table cellpadding="10" cellspacing="0" border="0" width="650" bgcolor="#E6E6E6">
<tbody>
<tr bgcolor="navy">
<td valign="top" align="left">

<div id="header">' . sprintf('<h1>Claroline (%s) - ' . $langUpgrade . '</h1>',$new_version) . '
</div>
</td>
</tr>
<tr valign="top" align="left">
<td>
<div id="content">
';

    return $output;

}

/**
 * Display footer of the upgrade tool
 *
 * @param string claroline version
 * @param string database version
 *
 * @since  1.7
 */

function upgrade_disp_footer()
{

    $output = '</div>

</td>
</tr>
</tbody>
</table>

</body>
</html>';

    return $output;
}

/**
 * Save the file currentVersion.inc.php
 *
 * @param string claroline version
 * @param string database version
 *
 * @since  1.7
 */

function save_current_version_file ( $clarolineVersion, $databaseVersion )
{
    global $includePath;

    // open file in write mode
    $fp_currentVersion = fopen($includePath .'/currentVersion.inc.php','w');

    // build content
    $currentVersionStr = '<?php 
$clarolineVersion = "' . $clarolineVersion . '";
$versionDb = "' . $databaseVersion . '";
?>';

    // write content in file
    fwrite($fp_currentVersion, $currentVersionStr);
    // close file
    fclose($fp_currentVersion);

}

/**
 * Get current version of claroline and database
 *
 * @return array with current version of claroline and database
 * @since  1.7
 */

function get_current_version ()
{
    global $clarolineVersion, $versionDb;
    global $includePath;

    if ( file_exists($includePath.'/currentVersion.inc.php') )
    {
        // get claroline version in currentVersion file (new in 1.6)
        // before the clarolineVersion was in claro_main.conf.php
        include ($includePath.'/currentVersion.inc.php');
    }
    
    $current_version['claroline'] = $clarolineVersion;
    $current_version['db'] = $versionDb;

    return $current_version;
}

/**
 * Get new version of claroline and database
 *
 * @return array with new version of claroline and database
 * @since  1.7
 */

function get_new_version ()
{
    global $includePath;

    include ( $includePath . '/installedVersion.inc.php' ) ;
    
    $new_version = array( 'complete' => $new_version,
                          'branch' => $new_version_branch );

    return $new_version;
}

/**
 * Apply sql queries to upgrade main database
 *
 * @param array sql queries
 * @param boolean verbose mode
 *
 * @return integer number of errors
 *
 * @since  1.7
 */

function upgrade_apply_sql_to_main_database ( $array_query , $verbose = false )
{
    global $lang_p_d_affected_rows, $langModeVerbose;
    global $accepted_error_list;

    $nb_error = 0;

    if ( $verbose ) echo '<p class="info">' . $langModeVerbose . ':</p>' . "\n";

    echo '<ol>' . "\n";

    foreach ( $array_query as $sql )
    {
        if ( $sql[0] == "#" && $verbose )
        {
            // Upgrade comment displayed in verbose mode
            echo '<p class="comment">' . 'Comment:' . $sql . '</p>' . "\n";
        }
        else
        {
            // Sql query
            claro_sql_query($sql);

            // Start Verbose Bloc
            if ( $verbose )
            {
                echo  '<li>' . "\n"
                    . '<p class="tt">' . $sql . '</p>' . "\n"
                    . '<p>' 
                    . sprintf($lang_p_d_affected_rows,mysql_affected_rows()) . '<br />' 
                    . mysql_info() 
                    . '</p>' . "\n";
            }

            // Sql error
            if ( mysql_errno() > 0 )
            {
                if ( in_array(mysql_errno(),$accepted_error_list) )
                {
                    // Sql error is accepted
                    if ( $verbose )
                    {
                        echo '<p class="success">' . mysql_errno(). ': ' . mysql_error() . '</p>' . "\n";
                    }
                }
                else
                {
                    echo '<p class="error">' . "\n"
                        . (++$nb_error) . '<strong>' . 'n°' . mysql_errno() . '</strong>: '. mysql_error() . '<br />' . "\n"
                        . '<code>' . $sql . '</code>' . "\n"
                        . '</p>' . "\n";
                }
            }

            // End Verbose Bloc
            if ( $verbose ) {
                echo '</li>' . "\n";
                flush();
            }
        }
    } // end foreach $array_query
    
    echo '</ol>' . "\n";

    return $nb_error;
}

/**
 * Apply sql queries to upgrade
 *
 * @param array sql queries
 * @param boolean verbose mode
 *
 * @return integer number of errors
 *
 * @since  1.7
 */

function upgrade_apply_sql ( $array_query , $verbose = false )
{
    $nb_error = 0;

    foreach ( $array_query as $sql )
    {
        if ( !upgrade_sql_query($sql, $verbose) ) $nb_error++;
    }

    if ( $nb_error == 0 ) return true;
    else                  return false;

}

function upgrade_sql_query($sql, $verbose = false)
{
    global $accepted_error_list;
        
    // Sql query
    mysql_query($sql);
        
    // Sql error
    if ( mysql_errno() > 0 )
    {
        if ( in_array(mysql_errno(),$accepted_error_list) )
        {
            // error accepted
            if ( $verbose )
            {
                echo '<p class="success">' . mysql_errno(). ': ' . mysql_error() . '</p>' . "\n";
            }
            return true;
        }
        else
        {
            echo '<p class="error">' . "\n"
                . '<strong>' . 'N°' . mysql_errno() . '</strong>: '. mysql_error() . '<br />' . "\n"
                . '<code>' . $sql . '</code>' . "\n"
                . '</p>' . "\n";

            // error not accepted
            return false;
        }
    }
    else
    {
        // no error
        return true;
    }
}

/**
 * Count courses, courses upgraded and upgrade failed
 *
 * @param string new database version
 * @param string new file version
 *
 * @return array 
 */

function count_course_upgraded($version)
{
    $tbl_mdb_names = claro_sql_get_main_tbl();

    $tbl_course = $tbl_mdb_names['course'];
 
    /**
     * In cours table, versionClaro contain :
     * - 'error' if upgrade already tried but failed
     * - version of last upgrade succeed (so previous or current)
     */

    $count_course = array( 'upgraded'=>0 , 
                           'error'=>0 , 
                           'total'=>0 );

    $sql = "SELECT versionClaro, count(*) as count_course 
            FROM `" . $tbl_course . "`
            GROUP BY versionClaro";

    $result = claro_sql_query($sql);

    while ( ( $row = mysql_fetch_array($result) ) )
    {
        // Count courses upgraded and upgrade failed    
        if ( preg_match('/^' . $version . '/',$row['versionClaro']) ) 
        {
            // upgrade succeed
            $count_course['upgraded'] += $row['count_course'];
        }
        elseif ( preg_match('/^error/',$row['versionClaro']) ) 
        {
            // upgrade failed
            $count_course['error'] += $row['count_course'];
        }

        // Count courses
        $count_course['total'] += $row['count_course'];
    }

    return $count_course;
}

/**
 * Add a new tool in course_tool table
 *
 * @param string claro_label
 * @param string script_url
 * @param string icon
 * @param string default_access
 * @param string add_in_course
 * @param string access_manager
 *
 * @return boolean
 */

function register_tool_in_main_database ( $claro_label, $script_url, $icon, $default_access = 'ALL', 
                                          $add_in_course = 'AUTOMATIC', $access_manager = 'COURSE_ADMIN' )
{
    $tbl_mdb_names = claro_sql_get_main_tbl();

    $tbl_tool = $tbl_mdb_names['tool'];
    
    $sql = "SELECT `id`
            FROM `" . $tbl_tool . "`
            WHERE `claro_label` = '" . addslashes($claro_label) . "'";
   
    $result = claro_sql_query($sql);

    if ( mysql_num_rows($result) == 0 )
    {
        // tool not registered

        // find max default rank
        $sql = "SELECT MAX(def_rank) AS `max_rank`
                FROM `" . $tbl_tool . "`";
    
        $default_rank =  claro_sql_query_get_single_value($sql);
        
        $default_rank++ ;
    
        // add tool in course_tool table
        $sql = "INSERT INTO `" . $tbl_tool . "`
               (`claro_label`,`script_url`,`icon`,`def_access`,`def_rank`,`add_in_course`,`access_manager`)
               VALUES
               ('" . addslashes($claro_label) . "','" . addslashes($script_url) . "','" . addslashes($icon) . "',
                '" . addslashes($default_access) .  "','" . addslashes($default_rank) . "',
                '" . addslashes($add_in_course) . "','" . addslashes($access_manager) . "')";

        return claro_sql_query_insert_id($sql);
    
    }
    else
    {
        return FALSE;
    }
    
}

/**
 * Add a new tool in tool_list table of a course
 *
 * @param string claro_label
 * @param string access level to tools if null get the default value from main table
 * @param string course db name glued
 *
 * @return boolean
 */

function add_tool_in_course_tool_list ( $claro_label, $access = null , $courseDbNameGlu = null )
{
    $tbl_mdb_names = claro_sql_get_main_tbl();
    $tbl_cdb_names = claro_sql_get_course_tbl($courseDbNameGlu);

    $tbl_course_tool = $tbl_mdb_names['tool'];
    $tbl_tool_list = $tbl_cdb_names['tool'];
    
    // get rank of tool in course table    
    $sql = "SELECT MAX(`rank`)  as `max_rank`
            FROM `" . $tbl_tool_list . "`";       

    $rank =  claro_sql_query_get_single_value($sql);
    $rank++;
    
    // get id of tool on the platform and default access    
    $sql = "SELECT `id`, `def_access`
            FROM `" . $tbl_course_tool . "`
            WHERE `claro_label` = '" . addslashes($claro_label) . "'";
   
    $result = claro_sql_query($sql);

    if ( mysql_num_rows($result) )
    {
        $row = mysql_fetch_array($result);        

        // if $access emtpy get default access
        if ( empty($access) ) $access = $row['access'];

        // add tool in course_tool table
        $sql = "INSERT INTO `" . $tbl_tool_list . "`
               (`tool_id`,`rank`,`access`)
               VALUES
               ('" . $row['id'] . "','" . $rank . "','" . $access . "')";

        return claro_sql_query_insert_id($sql);
    }
    else
    {
        return FALSE;
    }
    
}

/**
 * Save the file currentVersion.inc.php
 *
 * @param string course code
 * @param string claroline version
 * @param string database version
 *
 * @since  1.7
 */

function save_course_current_version ( $course_code, $fileVersion )
{
    $tbl_mdb_names = claro_sql_get_main_tbl();

    // query to update version of course

    $sql = " UPDATE `" . $tbl_mdb_names['course'] . "`
             SET versionClaro = '" . addslashes($fileVersion) . "'
             WHERE code = '". $course_code ."'";

    return claro_sql_query($sql);

}

/**
 * Execute repair query on main table
 *
 * @since  1.7
 */

function sql_repair_main_database()
{
    $tbl_names = claro_sql_get_main_tbl();

    foreach ( $tbl_names as $tbl )
    {
        $sql = "REPAIR TABLE `" . $tbl . "`";
        mysql_query($sql);
    }
}

/**
 * Execute repair query on course table
 *
 * @since  1.7
 */

function sql_repair_course_database($courseDbNameGlu)
{
    $tbl_names = claro_sql_get_course_tbl($courseDbNameGlu);

    foreach ( $tbl_names as $tbl )
    {
        $sql = "REPAIR TABLE `" . $tbl . "`";
        mysql_query($sql);
    }
}

/**
 * Get upgrade status of a tool
 *
 * @param string claro_label
 * @param string course_code
 *
 * @return integer status value
 *
 * @since  1.7
 */

function get_upgrade_status($claro_label,$course_code=null)
{
    // get table name
    $tbl_mdb_names = claro_sql_get_main_tbl();
    $tbl_upgrade_status = $tbl_mdb_names['upgrade_status'];
    
    // course_code empty 
    if ( is_null($course_code) ) $course_code = '';

    // query to find status
    $sql = "SELECT `status`
            FROM `" . $tbl_upgrade_status . "`
            WHERE cid = '" . $course_code . "' 
              AND claro_label = '" . $claro_label . "' ";

    $result = claro_sql_query($sql);
    
    if ( mysql_num_rows($result) > 0 )
    {
        // get status
        $row = mysql_fetch_array($result);       
        $status = $row['status'];
    }
    else
    {
        // initialise status to 1
        $status = 1;
        // insert status
        $sql = "INSERT INTO `" . $tbl_upgrade_status . "`
                (`cid`,`claro_label`,`status`)
                VALUES
                ('" . $course_code .  "','" . $claro_label . "','" . $status ."')";
        claro_sql_query($sql);
    }

    return $status;
}

/**
 * Get upgrade status of a tool
 *
 * @param string claro_label
 * @param int status value
 * @param string course_code
 *
 * @return integer status value
 *
 * @since  1.7
 */

function set_upgrade_status($claro_label,$status,$course_code=null)
{
    // get table name
    $tbl_mdb_names = claro_sql_get_main_tbl();
    $tbl_upgrade_status = $tbl_mdb_names['upgrade_status'];

    // course_code empty 
    if ( is_null($course_code) ) $course_code = '';

    // update status
    $sql = " UPDATE `" . $tbl_upgrade_status . "`
             SET `status` = '" . $status . "'
             WHERE cid = '" . $course_code . "' 
               AND claro_label = '" . $claro_label . "' ";

    return claro_sql_query($sql);      

}

/**
 * Get upgrade status of a tool
 *
 * @param string claro_label
 * @param int status value
 * @param string course_code
 *
 * @return integer status value
 *
 * @since  1.7
 */

function clean_upgrade_status($course_code=null)
{
    // get table name
    $tbl_mdb_names = claro_sql_get_main_tbl();
    $tbl_upgrade_status = $tbl_mdb_names['upgrade_status'];

    // course_code empty 
    if ( is_null($course_code) ) $course_code = '';

    // delete all status for this course
    $sql = " DELETE FROM `" . $tbl_upgrade_status . "`
             WHERE cid = '" . $course_code . "' ";

    return claro_sql_query($sql);
}

?>
