<?php // $Id$

/**
 * CLAROLINE
 *
 * This  tool compute the disk Usage of each course.
 *
 * @version     $Revision$
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @author      Antonin Bourguignon <antonin.bourguignon@claroline.net>
 */

// Reset session variables
$cidReset = true; // course id
$gidReset = true; // group id
$tidReset = true; // tool id

require_once '../../inc/claro_init_global.inc.php';
require_once get_path('incRepositorySys').'/lib/claroCourse.class.php';
require_once get_path('incRepositorySys').'/lib/csvexporter.class.php';

// Security check
if (!claro_is_user_authenticated()) claro_disp_auth_form();
if (!claro_is_platform_admin()) claro_die(get_lang('Not allowed'));

// Breadcrumb
$nameTools = get_lang('Files statistics');
ClaroBreadCrumbs::getInstance()->prepend( get_lang('Administration'), get_path('rootAdminWeb') );

$viewAs = (isset($_GET['view_as']) && in_array($_GET['view_as'], array('html', 'csv')) ?
    $_GET['view_as'] : 'html');

// Params
$extensions         = explode(',', get_conf('filesStatsExtensions'));
$coursesDirectory   = get_path('coursesRepositorySys');

// Run
$courses        = ClaroCourse::getAllCourses();
$allExtensions  = array_merge($extensions, array('others', 'sum'));
$stats          = array();

// Refresh and progression
/*
if ( $display == DISPLAY_RESULT_PANEL && ($count_course_upgraded + $count_course_error ) < $count_course )
{
    $refresh_time = 20;
    $htmlHeadXtra[] = '<meta http-equiv="refresh" content="'. $refresh_time  .'" />'."\n";
}
*/

foreach ($courses as $course)
{
    $coursePath = $coursesDirectory.'/'.$course['sysCode'];
    $courseStats = array();
    
    foreach($allExtensions as $ext)
    {
        $courseStats[$ext]['count']  = 0;
        $courseStats[$ext]['size']   = 0;
    }
    
    foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($coursePath)) as $file)
    {
        if ($file->getType() == 'file')
        {
            $type = strtolower(pathinfo( $file->getFilename(), PATHINFO_EXTENSION ));
            
            if (in_array($type, $extensions))
            {
                $courseStats[$type]['count'] ++;
                $courseStats[$type]['size'] += $file->getSize();
            }
            else
            {
                $courseStats['others']['count'] ++;
                $courseStats['others']['size'] += $file->getSize();
            }
            
            $courseStats['sum']['count'] ++;
            $courseStats['sum']['size'] += $file->getSize();
        }
    }
    
    $stats[$course['sysCode']]['courseTitle'] = $course['title'];
    $stats[$course['sysCode']]['courseStats'] = $courseStats;
}





if ($viewAs == 'html')
{
    $template = new CoreTemplate('admin_files_stats.tpl.php');
    $template->assign('extensions', $extensions);
    $template->assign('allExtensions', $allExtensions);
    $template->assign('stats', $stats);
    
    $claroline->display->body->appendContent($template->render());
    
    echo $claroline->display->render();
}
elseif ($viewAs == 'csv')
{
    $csvTab = array();
    foreach ($stats as $key => $elmt)
    {
        $csvSubTab = array();
        
        $csvSubTab['courseCode'] = $key;
        $csvSubTab['courseTitle'] = $elmt['courseTitle'];
        
        foreach ($elmt['courseStats'] as $key => $elmt)
        {
            $csvSubTab[$key.'_count'] = $elmt['count'];
            $csvSubTab[$key.'_size'] = format_bytes($elmt['size']);
        }
        
        $csvTab[] = $csvSubTab;
    }
    
    $csvExporter = new CsvExporter(', ', '"');
    $out = $csvExporter->exportAndSend(get_lang('files_stats'), $csvTab);
}





/**
 * Convert a size (Bytes) to K/M/G/TB
 * @param int $size
 * @return string
 *
 * @todo move it where it should be (wherever it is)
 */
function format_bytes($size)
{
    $units = array(' B', ' KB', ' MB', ' GB', ' TB');
    for ($i = 0; $size >= 1024 && $i < 4; $i++) $size /= 1024;
    return round($size, 2).$units[$i];
}