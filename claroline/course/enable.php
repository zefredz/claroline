<?php
/*
 * CLAROLINE
 *
 * This tool manage properties of an exiting course
 *
 * @version 1.8 $Revision$
 * @copyright (c) 2001-2007 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @author claroline Team <cvs@claroline.net>
 *
 * old version : http://cvs.claroline.net/cgi-bin/viewcvs.cgi/claroline/claroline/course_info/infocours.php
 *
 * @package CLCRS
 *
 */

if ( isset($_REQUEST['cid']) ) $cidReq = $_REQUEST['cid'];
else $cidReq ='';

require '../inc/claro_init_global.inc.php';

$nameTools = get_lang('Course settings');

if ( ! claro_is_in_a_course() || ! claro_is_user_authenticated()) claro_disp_auth_form(true);

$is_allowedToEdit = claro_is_course_manager();

if ( ! $is_allowedToEdit )
{
    claro_die(get_lang('Not allowed'));
}

//=================================
// Main section
//=================================

include claro_get_conf_repository() . 'course_main.conf.php';
require_once get_path('incRepositorySys') . '/lib/course.lib.inc.php';

$dialogBox = new DialogBox();
$out = '';

$cmd = isset($_REQUEST['cmd']) ? $_REQUEST['cmd'] : null;

if ($cmd == 'exEnable')
    {
        if (claro_set_course_enable($cidReq))
        {
            $dialogBox->success(get_lang('Course enable'));
        }
        else 
        {
            $dialogBox->error(get_lang('Unable to enable course'));
        }
    }
    
    $out .= $dialogBox->render();


// DISPLAY SECTION
$claroline->display->header;

// Body
$claroline->display->body->appendContent ( $out );
echo $claroline->display->render ();
?>