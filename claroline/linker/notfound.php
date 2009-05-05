<?php // $Id$
// vim: expandtab sw=4 ts=4 sts=4:
/**
 * CLAROLINE
 *
 * @version 1.8 $Revision$
 *
 * @copyright 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @author claro team <info@claroline.net>
 * @author Frederic Minne <zefredz@gmail.com>
 *
 * @package CLLNK
 */

require_once '../inc/claro_init_global.inc.php';

$referer = isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : '';

$requestedFile = isset( $_REQUEST['requestedFile'] ) ? $_REQUEST['requestedFile'] : 'unknown';

// $noBanner = true;
$toolName = 'File not found';

require_once get_path('incRepositorySys') . '/claro_init_header.inc.php';

echo '<p style="text-align: center;padding-top:1em; padding-left: 1em;">'
.    '<strong>File ' . striptags($requestedFile) . ' not found !</strong>'
.    '</p>'
;

echo '<p style="text-align: center;padding-top:1em; padding-left: 1em;" class="">'
.    'The file you have requested has not been found on this server. '
.    'Maybe it has been moved or deleted.'
.    '</p>'
;

echo '<p style="padding-left: 2em;">'
.    get_lang('Back to :')
.    '<ul>'
.    '<li>'
.    '<a href="' . get_module_url('CLDOC') . '/document.php">' . get_lang("Documents and Links") . '</a></li>'
.    ( (! empty($referer)) ? '<li><a href="' . htmlspecialchars(strip_tags($referer)) . '">' .  get_lang('Previous page') . '</a></li>' : '' )
.    '</ul>'
.    '</p>'
;

require_once get_path('incRepositorySys') . '/claro_init_footer.inc.php';
?>