<?php // $Id$

/**
 * CLAROLINE
 *
 * @version 1.8 $Revision$
 *
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @package CLIMPORT
 *
 * @author Yannick Wautelet <yannick_wautelet@hotmail.com>
 * @author Claro Team <cvs@claroline.net>
 */

//$tlabelReq = 'CLCRS';
//$cidReq='ES1';
$dialogBox = '';
		
require '../inc/claro_init_global.inc.php';
if ( ! $_cid || ! $is_courseAllowed ) claro_disp_auth_form(true);

include_once($includePath . '/lib/import.lib.php');

// filter incoming data
$acceptCmd = array('doImport');
$cmd= (isset($_REQUEST['cmd']) && in_array($_REQUEST['cmd'],$acceptCmd)) ? $_REQUEST['cmd'] : '';
$archiveFile = $_cid.".zip";
$filePath = "c:/program files/easyPHP1-8/www/cvs/claroline.test/export";

// command
$taskDoImport = false;
switch($cmd)
{
	case 'doImport' :
	{
		$taskDoImport = true;
	} break;
}
// task

if ($taskDoImport)
{
	
	if (import_all_data_course_in_db($filePath."/".$archiveFile , $_cid))
	{
		$dialogBox = get_lang('Import succeed');
	}
	else
	{			
		$dialogBox = get_lang("Import failed : <br>".claro_failure::get_last_failure());			
	}
	;
}


// prepare display
$nameTools = get_lang('Import course');

// Display 
include $includePath . '/claro_init_header.inc.php';

echo claro_html::tool_title($nameTools);

if ( !empty($dialogBox) ) echo claro_html::message_box($dialogBox);

echo '<a href="' . $_SERVER['PHP_SELF'] . '?cmd=doImport">'. get_lang('Import this course') . '</a>';

include $includePath . '/claro_init_footer.inc.php';

?>