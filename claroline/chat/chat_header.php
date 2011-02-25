<?php // $Id$
/**
 * CLAROLINE
 *
 * @version 1.8 $Revision$
 *
 * @copyright 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @see http://www.claroline.net/wiki/index.php/CLCHT
 *
 * @package CLCHT
 *
 * @author Claro Team <cvs@claroline.net>
 *
 */

$tlabelReq = 'CLCHT';

require '../inc/claro_init_global.inc.php';

$nameTools  = get_lang('Chat');
$noPHP_SELF = TRUE;

// Turn off session lost
$warnSessionLost = false ;

include get_path('incRepositorySys') . '/claro_init_header.inc.php';
$_group = claro_get_current_group_data();

$titleElement['mainTitle'] = $nameTools;
if ( claro_is_in_a_group() ) $titleElement['supraTitle'] = claro_get_current_group_data('name');

echo claro_html_tool_title($titleElement);

$hide_footer = TRUE;
include get_path('incRepositorySys') . '/claro_init_footer.inc.php';
?>
