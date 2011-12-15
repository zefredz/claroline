<?php // $Id$
/**
 * CLAROLINE
 ******************************************************************************
 * Campus Home Page
 *
 * @version 1.9 $Revision$
 *
 * @copyright (c) 2001-2008 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @package CLINDEX
 *
 * @author Claro Team <cvs@claroline.net>
 *
 */

unset($includePath); // prevent hacking

// Flag forcing the 'current course' reset, as we're not anymore inside a course

$cidReset = TRUE;
$tidReset = TRUE;

// Include Library and configuration file

require './claroline/inc/claro_init_global.inc.php'; // main init
include claro_get_conf_repository() . 'CLHOME.conf.php'; // conf file

require_once get_path('incRepositorySys') . '/lib/courselist.lib.php'; // conf file


// logout request : delete session data

if (isset($_REQUEST['logout']))
{
    // notify that a user has just loggued out
    if (isset($logout_uid)) // Set  by local_init
    {
        $eventNotifier->notifyEvent('user_logout', array('uid' => $logout_uid));
    }
    /* needed to be able to :
         - log with claroline when 'magic login' has previously been clicked
         - notify logout event
         (logout from CAS has been commented in casProcess.inc.php)*/
    if( get_conf('claro_CasEnabled', false) && ( get_conf('claro_CasGlobalLogout') && !phpCAS::checkAuthentication() ) )
    {
        phpCAS::logout((isset( $_SERVER['HTTPS']) && ($_SERVER['HTTPS']=='on'||$_SERVER['HTTPS']==1) ? 'https://' : 'http://')
                        . $_SERVER['HTTP_HOST'].get_conf('urlAppend').'/index.php');
    }
    session_destroy();
}

// Hide breadcrumbs and view mode on platform home page
// $claroline->display->banner->hideBreadcrumbLine();

$template = new CoreTemplate('platform_index.tpl.php');

$claroline->display->body->setContent($template->render());


echo $claroline->display->render();
