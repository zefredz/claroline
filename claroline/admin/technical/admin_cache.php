<?php // $Id$

/**
 * CLAROLINE
 *
 * This  tool allows to empty the cache of claroline.
 *
 * @version     $Revision$
 * @copyright   (c) 2001-2014, Universite catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @author      Frédéric Minne <zefredz@claroline.net>
 */

// Reset context session variables
$GLOBALS['contextReset'] = true;

require_once '../../inc/claro_init_global.inc.php';

FromKernel::uses('display/dialogBox.lib', 'fileDisplay.lib');

require_once __DIR__ . '/lib/admincache.lib.php';

// Security check
if (!claro_is_user_authenticated()) claro_disp_auth_form();
if (!claro_is_platform_admin()) claro_die(get_lang('Not allowed'));

// Breadcrumb
$nameTools = get_lang('Cache management');
ClaroBreadCrumbs::getInstance()->prepend( get_lang('Administration'), get_path('rootAdminWeb') );

// empty cache : ical, rss, other (campusProblem...)
$cmd = isset($_REQUEST['cmd']) ? $_REQUEST['cmd'] : null;

$cmdMap = array(
    'cleanNews' => get_path('rootSys') . 'tmp/cache/news',
    'cleanCal' => get_path('rootSys') . 'tmp/cache/ical',
    'cleanGarbage' => get_path('rootSys') . 'tmp/cache/garbage',
    'cleanProblems' => get_path('rootSys') . 'tmp/cache/campusProblem'
);

$pathMap = array_flip($cmdMap);

$dialogBox = new DialogBox();

if ( ! is_null($cmd) )
{
    if ( isset( $cmdMap[$cmd] ) )
    {
        $pathToClean = $cmdMap[$cmd];
        
        try
        {
            $cleaner = new FolderCleaner( secure_file_path( $pathToClean ) );
            $pathRemoved = $cleaner->clean();
            
            $dialogBox->success( get_lang('Folder %path cleaned up', array( '%path' => $pathToClean ) ) );
            
            if ( claro_debug_mode () )
            {
                $dialogBox->success( var_export ( $pathRemoved, true ) );
            }
        }
        catch ( Exception $e )
        {
            $dialogBox->error( $e->getMessage () );
            
            if ( claro_debug_mode () )
            {
                $dialogBox->error($e->__toString());
            }
        }
        
        $dialogBox->info(get_lang('Will delete %path', array( '%path' => $pathToClean ) ) );
    }
    else
    {
        $dialogBox->error( get_lang('Unknown command given') );
    }
}
// empty garbage

// display cache and garbage size + link to empty
$pathList = array(
    get_path('rootSys') . 'tmp/cache/news',
    get_path('rootSys') . 'tmp/cache/ical',
    get_path('rootSys') . 'tmp/cache/garbage',
    get_path('rootSys') . 'tmp/cache/campusProblem'
);

$stats = array();

foreach ( $pathList as $path )
{
    $stats[$path] = array(
        'size' => 0,
        'count' => 0
    );
    
    if ( file_exists($path) )
    {
        foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)) as $file)
        {
            try 
            {
                if ( $file->isFile() )
                {
                        $stats[$path]['count'] ++;
                        $stats[$path]['size'] += $file->getSize();
                }
            }
            catch(Exception $ex)
            {
                $dialogBox->error( $ex->getMessage() );
            }
        }
    }
}

$template = new CoreTemplate('admin_cache.tpl.php');
$template->assign( 'dialogBox', $dialogBox );
$template->assign( 'stats', $stats );
$template->assign( 'cmdList', $pathMap );

Claroline::getDisplay()->body->appendContent( $template->render () );

echo Claroline::getDisplay()->render();
