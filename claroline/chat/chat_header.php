<?php // $Id$
/** 
 * CLAROLINE 
 *
 * @version 1.7 $Revision$
 *
 * @copyright 2001-2005 Universite catholique de Louvain (UCL)
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

if(isset($_gid))
{
    $interbredcrump[]  = array ('url'=>'../group/group.php', 'name'=> $langGroups);
    $interbredcrump[]= array ('url'=>'../group/group_space.php', 'name'=> $_group['name']);
}

$nameTools  = $langChat;
$noPHP_SELF = TRUE;

include($includePath . '/claro_init_header.inc.php');

$titleElement['mainTitle'] = $nameTools;
if ( $_gid ) $titleElement['supraTitle'] = $_group['name'];

echo claro_disp_tool_title($titleElement);

$hide_footer = TRUE;
include($includePath . '/claro_init_footer.inc.php');
?>