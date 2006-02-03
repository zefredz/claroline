<?php // $Id$
/**
 * CLAROLINE
 *
 * Show news read from claroline.net
 *
 * @version 1.8 $Revision$
 *
 * @copyright 2001-2006 Universite catholique de Louvain (UCL)
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

// Security check
if ( ! $_uid ) claro_disp_auth_form();
if ( ! $is_platformAdmin ) claro_die(get_lang('Not allowed'));

require_once $includePath . '/lib/admin.lib.inc.php';
// rss reader library
require $includePath . '/lib/lastRSS/lastRSS.php';


$nameTools = get_lang('Claroline.net news');
$interbredcrump[] = array ('url' => $rootAdminWeb, 'name' => get_lang('Administration'));
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
include $includePath . '/claro_init_header.inc.php';
echo claro_disp_tool_title($nameTools);

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
    echo claro_html::message_box(get_lang('Error : cannot read RSS feed'));
}

include $includePath . '/claro_init_footer.inc.php';
?>