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

$lastTreatedCourseId = (!empty($_SESSION['lastTreatedCourseId']) ? (int) $_SESSION['lastTreatedCourseId'] : 0);
$stats = (!empty($_SESSION['progressingStats']) ? $_SESSION['progressingStats'] : array());

// Params
$cmd                = (!empty($_REQUEST['cmd'])) ? $_REQUEST['cmd'] : '';
$inProgress         = (!empty($_SESSION['inProgress']) && $_SESSION['inProgress'] == true) ? true : false;
$extensionsFromConf = get_conf('filesStatsExtensions');
$extensions         = (!empty($extensionsFromConf) ? explode(',', get_conf('filesStatsExtensions')) : array());
$coursesDirectory   = get_path('coursesRepositorySys');
$coursesPool        = 2;

// Run
if ($cmd == 'run' || $inProgress)
{
    $allExtensions  = array_merge($extensions, array('others', 'sum'));
    $dialogBox = new DialogBox();
    
    // Get courses
    $tbl_mdb_names              = claro_sql_get_main_tbl();
    $tbl_course                 = $tbl_mdb_names['course'];
    
    $req = "SELECT c.cours_id               AS id,
                   c.titulaires             AS titulars,
                   c.code                   AS sysCode,
                   c.isSourceCourse         AS isSourceCourse,
                   c.sourceCourseId         AS sourceCourseId,
                   c.intitule               AS title,
                   c.administrativeNumber   AS officialCode,
                   c.directory
                   
            FROM `" . $tbl_course . "` AS c
            WHERE c.cours_id > ".$lastTreatedCourseId."
            ORDER BY c.cours_id ASC
            LIMIT 0, ".$coursesPool;
    
    $sql = Claroline::getDatabase()->query($req);
    
    $i = 0;
    
    if ($sql->count() > 0)
    {
        foreach ($sql as $course)
        {
            $coursePath = $coursesDirectory.'/'.$course['directory'];
            $courseStats = array();
            
            // Initialize statistics to 0
            foreach($allExtensions as $ext)
            {
                $courseStats[$ext]['count']  = 0;
                $courseStats[$ext]['size']   = 0;
            }
            
            // Browse the file system
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
            $stats[$course['sysCode']]['courseTitulars'] = $course['titulars'];
            $stats[$course['sysCode']]['courseStats'] = $courseStats;
            
            $i++;
            
            // Courses pool's limit reached ?
            if ($i == $coursesPool)
            {
                $_SESSION['inProgress'] = true;
                $_SESSION['lastTreatedCourseId'] = $course['id'];
                $_SESSION['progressingStats'] = $stats;
                if (empty($_SESSION['viewAs']))
                {
                    $_SESSION['viewAs'] = (isset($_REQUEST['viewAs']) && in_array($_REQUEST['viewAs'], array('html', 'csv')) ? $_REQUEST['viewAs'] : 'html');
                }
                
                $htmlHeadXtra[] = '<meta http-equiv="refresh" content="1" />'."\n";
                
                break;
            }
        }
    }
    
    // All courses treated ?
    if ($i < $coursesPool && !$sql->valid())
    {
        $dialogBox->success(get_lang('All courses treated !'));
        $viewAs = $_SESSION['viewAs'];
        
        unset($_SESSION['lastTreatedCourseId']);
        unset($_SESSION['progressingStats']);
        unset($_SESSION['inProgress']);
        unset($_SESSION['viewAs']);
        
        ksort($stats);
    }
    
    
    
    
    if (!isset($_SESSION['inProgress']))
    {
        if (!empty($extensions))
        {
            $dialogBox->info(get_lang('You\'ve chosen to isolate the following extensions: %types.  If you wish to modify these extensions, check the advanced platform settings', array('%types' => implode(', ', $extensions))));
        }
        else
        {
            $dialogBox->info(get_lang('You don\'t have chosen any extension to isolate.  If you wish to isolate extensions in your statistics, check the advanced platform settings'));
        }
        
        
        if ($viewAs == 'html')
        {
            $template = new CoreTemplate('admin_files_stats.tpl.php');
            $template->assign('dialogBox', $dialogBox);
            $template->assign('extensions', $extensions);
            $template->assign('allExtensions', $allExtensions);
            $template->assign('stats', $stats);
            $template->assign('formAction', $_SERVER['PHP_SELF']);
            
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
                $csvSubTab['courseTitulars'] = $elmt['courseTitulars'];
                
                foreach ($elmt['courseStats'] as $key => $elmt)
                {
                    $csvSubTab[$key.'_count'] = $elmt['count'];
                    $csvSubTab[$key.'_size'] = format_bytes($elmt['size']);
                }
                
                $csvTab[] = $csvSubTab;
            }
            
            $csvExporter = new CsvExporter(', ', '"');
            $fileName = get_lang('files_stats').'_'.claro_date('d-m-Y');
            $stream = $csvExporter->export($csvTab);
            claro_send_stream($stream, $fileName, 'text/csv');
        }
    }
    else
    {
        $dialogBox->warning(get_lang('Statistics in progress, please don\'t refresh until further instructions !'));
        
        $claroline->display->body->appendContent($dialogBox->render());
        echo $claroline->display->render();
    }
}
else
{
    $dialogBox = new DialogBox();
    $dialogBox->warning(get_lang('Caution: building files\' statistics is a pretty heavy work.  It might take a while and a lot of resources, depending of the size of your campus.'));
    
    if (!empty($extensions))
    {
        $dialogBox->info(get_lang('You\'ve chosen to isolate the following extensions: %types.  If you wish to modify these extensions, check the advanced platform settings', array('%types' => implode(', ', $extensions))));
    }
    else
    {
        $dialogBox->info(get_lang('You don\'t have chosen any extension to isolate.  If you wish to isolate extensions in your statistics, check the advanced platform settings'));
    }
    
    $template = new CoreTemplate('admin_files_stats.tpl.php');
    $template->assign('dialogBox', $dialogBox);
    $template->assign('extensions', $extensions);
    $template->assign('formAction', $_SERVER['PHP_SELF']);
    $template->assign('cancelUrl', get_path('rootAdminWeb'));
    
    $claroline->display->body->appendContent($template->render());
    
    echo $claroline->display->render();
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