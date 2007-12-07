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

$tlabelReq = 'CLCHT___';

require '../inc/claro_init_global.inc.php';

$nameTools  = get_lang('Chat');
$noPHP_SELF = TRUE;

include($includePath . '/claro_init_header.inc.php');

$titleElement['mainTitle'] = $nameTools;
if ( $_gid ) $titleElement['supraTitle'] = $_group['name'];

echo claro_html_tool_title($titleElement);

$hide_footer = TRUE;
include $includePath . '/claro_init_footer.inc.php';
?>