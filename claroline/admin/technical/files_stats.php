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

require_once '../../inc/claro_init_global.inc.php';

// Security check
if ( ! claro_is_user_authenticated() ) claro_disp_auth_form();
if ( ! claro_is_platform_admin() ) claro_die(get_lang('Not allowed'));

// Breadcrumb
$nameTools = get_lang('Files Statistics');
ClaroBreadCrumbs::getInstance()->prepend( get_lang('Administration'), get_path('rootAdminWeb') );

// Work in progress

$claroline->display->body->appendContent('Work in progress');
echo $claroline->display->render();