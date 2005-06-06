<?php // $Id$
/**
 * CLAROLINE
 *
 * Show news read from claroline.net
 *
 * @version 1.7 $Revision$
 *
 * @copyright 2001-2005 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE 
 *
 * @see http://www.claroline.net/wiki/CLNEWS/
 *
 * @author Claro Team <cvs@claroline.net>
 *
 * @package CLNEWS
 * 
 */
$cidReset=true;$gidReset=true;
require '../inc/claro_init_global.inc.php';

if(file_exists($includePath.'/currentVersion.inc.php')) include ($includePath.'/currentVersion.inc.php');
include ($includePath . '/lib/admin.lib.inc.php');
// rss reader library
require ($includePath . '/lib/lastRSS/lastRSS.php');

//SECURITY CHECK
$is_allowedToAdmin     = $is_platformAdmin;
if (!$is_allowedToAdmin) claro_disp_auth_form();

$nameTools = $langClarolineNetNews;

$interbredcrump[] = array ("url"=>$rootAdminWeb, "name"=> $langAdministration);
$noQUERY_STRING   = TRUE;


//----------------------------------
// prepare rss reader
//----------------------------------
// url where the reader will have to get the rss feed
$urlNewsClaroline = 'http://www.claroline.net/rss.php';

$rss = new lastRSS;

// where the cached file will be written
$rss->cache_dir = '.';
// how long without refresh the cache
$rss->cache_time = 1200; 

//----------------------------------
// DISPLAY
//----------------------------------
// title variable
include($includePath."/claro_init_header.inc.php");	
claro_disp_tool_title($nameTools);

if ($rs = $rss->get($urlNewsClaroline))
{
	echo '<table class="claroTable" width="100%">'."\n\n";

	foreach ($rs['items'] as $item) 
	{
		$href = $item['link'];
	    $title = $item['title'];
		$summary = $rss->unhtmlentities($item['description']);
		$date = strtotime($item['pubDate']);

	    echo '<tr>'."\n"
            .'<th class="headerX">'."\n"
	        .'<a href="'.$href.'">'.$title.'</a>'."\n"
			.'<small> - '.claro_disp_localised_date($dateFormatLong,$date).'</small>'."\n"
	        .'</th>'."\n"
			.'</tr>'."\n"
            .'<tr>'."\n"
            .'<td>'."\n"
	        .$summary."\n"
            .'</td>'."\n"
			.'</tr>'."\n\n";
	}

	echo '</table>'."\n\n";
}
else
{	
	claro_disp_message_box($langErrorCannotReadRSSFile);
}

include($includePath."/claro_init_footer.inc.php");
?>