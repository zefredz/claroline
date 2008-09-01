<?php // $Id$
/**
 * CLAROLINE
 *
 * Show news read from claroline.net
 *
 * @version 1.8 $Revision$
 *
 * @copyright 2001-2007 Universite catholique de Louvain (UCL)
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
if ( ! claro_is_user_authenticated() ) claro_disp_auth_form();
if ( ! claro_is_platform_admin() ) claro_die(get_lang('Not allowed'));

require_once get_path('incRepositorySys') . '/lib/admin.lib.inc.php';
// rss reader library
require get_path('incRepositorySys') . '/lib/thirdparty/lastRSS/lastRSS.lib.php';


$nameTools = get_lang('Claroline.net news');
ClaroBreadCrumbs::getInstance()->prepend( get_lang('Administration'), get_path('rootAdminWeb') );
$noQUERY_STRING   = TRUE;


//----------------------------------
// prepare rss reader
//----------------------------------
// url where the reader will have to get the rss feed
$urlNewsClaroline = 'http://www.claroline.net/rss.php';

$rss = new lastRSS;

// where the cached file will be written
$rss->cache_dir = get_path('rootSys') . '/tmp/cache/';
// how long without refresh the cache
$rss->cache_time = 1200;

//----------------------------------
// DISPLAY
//----------------------------------
// title variable
include get_path('incRepositorySys') . '/claro_init_header.inc.php';
echo claro_html_tool_title($nameTools);

if (false !== $rs = $rss->get($urlNewsClaroline))
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
            .'<small> - '.claro_html_localised_date(get_locale('dateFormatLong'),$date).'</small>'."\n"
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
    echo claro_html_message_box(get_lang('Error : cannot read RSS feed (Check feed url and if php setting "allow_url_fopen" is turned on).'));
}

include get_path('incRepositorySys') . '/claro_init_footer.inc.php';
?>
