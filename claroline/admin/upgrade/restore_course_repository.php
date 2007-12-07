<?php // $Id$
/**
 * CLAROLINE 
 *
 * Try to create main database of claroline without remove existing content
 * 
 * @version 1.8 $Revision$
 *
 * @copyright 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE 
 *
 * @see http://www.claroline.net/wiki/index.php/Upgrade_claroline_1.6
 *
 * @package UPGRADE
 *
 * @author Claro Team <cvs@claroline.net>
 * @author Christophe Gesch� <moosh@claroline.net>
 * @author Mathieu Laurent <laurent@cerdecam.be>
 * @since 1.6
 *
 */

// Include library file

require '../../inc/claro_init_global.inc.php';
include_once($includePath . '/lib/debug.lib.inc.php');
include_once($includePath . '/lib/admin.lib.inc.php');

$nameTools = get_lang('Restore course repository');

// Security Check

if ( !$is_platformAdmin ) claro_disp_auth_form();

// Execute command

if ( isset($_REQUEST['cmd']) && $_REQUEST['cmd'] == 'exRestore' )
{
    $tbl_mdb_names = claro_sql_get_main_tbl();
    
    $tbl_course = $tbl_mdb_names['course'];
    
    $sqlListCourses = " SELECT code sysCode, directory coursePath ".
                      " FROM `". $tbl_course . "` " .
                      " ORDER BY sysCode";
    
    $res_listCourses = claro_sql_query($sqlListCourses);
    
    if (mysql_num_rows($res_listCourses))
    {
        $restored_courses =  '<ol>' . "\n";
        
        while ( ( $course = mysql_fetch_array($res_listCourses)) )
        {
            $currentcoursePathSys = $coursesRepositorySys . $course['coursePath'] . '/';
            $currentCourseIDsys = $course['sysCode'];
            
            if ( restore_course_repository($currentCourseIDsys,$currentcoursePathSys) )
            {
                $restored_courses .= '<li>' . sprintf('Course repository "%s" updated', $currentcoursePathSys) . '</li>' . "\n";       
            }
        
        }
        $restored_courses .= '</ol>' . "\n";
    }
}

// Display

// Deal with interbredcrumps  and title variable
$interbredcrump[]  = array ('url' => $rootAdminWeb, 'name' => get_lang('Administration'));

include($includePath . '/claro_init_header.inc.php');

echo claro_html_tool_title($nameTools);

// display result

if (isset($restored_courses)) echo $restored_courses;

// display link to launch the restore

echo '<p><a href="' . $_SERVER['PHP_SELF'] . '?cmd=exRestore">' . get_lang('Launch restore of the course repository') . '</a></p>';

include $includePath . '/claro_init_footer.inc.php';


/**
 * @global $includePath
 * @global $clarolineRepositorySys
 */

function restore_course_repository($courseId, $courseRepository)
{

    global $clarolineRepositorySys, $clarolineRepositoryWeb, $urlAppend, $includePath;

    if ( is_writable($courseRepository) )
    {
        umask(0);

        /**
            create directory for new tools of claroline 1.5 
        */
    
        if ( !is_dir($courseRepository) ) mkdir($courseRepository, CLARO_FILE_PERMISSIONS);
        if ( !is_dir($courseRepository . '/chat'          ) ) mkdir($courseRepository . '/chat'          , CLARO_FILE_PERMISSIONS);
        if ( !is_dir($courseRepository . '/modules'       ) ) mkdir($courseRepository . '/modules'       , CLARO_FILE_PERMISSIONS);
        if ( !is_dir($courseRepository . '/scormPackages' ) ) mkdir($courseRepository . '/scormPackages' , CLARO_FILE_PERMISSIONS);

        // build index.php of course
        $fd = fopen($courseRepository . '/index.php', 'w');
        if ( ! $fd) return claro_failure::set_failure('CANT_CREATE_COURSE_INDEX');

        $string = '<?php ' . "\n"
                . 'header (\'Location: '. $urlAppend . '/claroline/course/index.php?cid=' . htmlspecialchars($courseId) . '\') ;' . "\n"
              . '?' . '>' . "\n" ;

        if ( ! fwrite($fd, $string) ) return false;
        if ( ! fclose($fd) )          return false;

        $fd = fopen($courseRepository . '/group/index.php', 'w');
        if ( ! $fd ) return false;

        $string = '<?php session_start(); ?'.'>';

        if ( ! fwrite($fd, $string) ) return false;

        return true;
    
    } else {
        printf ('repository %s not writable', $courseRepository);
        return 0;
    }

}

?>
