<?php // $Id$
//----------------------------------------------------------------------
// CLAROLINE
//----------------------------------------------------------------------
// Copyright (c) 2001-2004 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see 'credits' file
//----------------------------------------------------------------------
$langFile = "admin";$cidReset=true;$gidReset=true;
require '../inc/claro_init_global.inc.php';


@include ($includePath.'/installedVersion.inc.php');
include($includePath.'/lib/admin.lib.inc.php');
require($includePath.'/lib/rssread/rss_fetch.inc.php');

//SECURITY CHECK
$is_allowedToAdmin     = $is_platformAdmin || $PHP_AUTH_USER;
if (!$is_allowedToAdmin) treatNotAuthorized();


$urlNewsClaroline = 'http://www.claroline.net/rss/';

//----------------------------------
// DISPLAY
//----------------------------------

// Deal with interbredcrumps  and title variable

$htmlHeadXtra[]="
<style>
.claroNews          {
	border-top: thin groove Blue;
	padding: 4px 2px 2px 6px;
}
.claroNewsTitle     {
	font-family: serif;
	font-size: larger;
	font-variant: small-caps;
	font-weight: bold;
	letter-spacing: 3px;
	text-decoration: none;
}
.claroNewsDate      {
	font-size: x-small;
	font-style: italic;
}
.claroNewsSummary   {
	background-color: Silver;
	color: Navy;
	padding: 3px 7x 2px 17px;
}
</style>
";

$dateNow             = claro_disp_localised_date($dateTimeFormatLong);

$rss = fetch_rss( $urlNewsClaroline );
$nameTools = $rss->channel['title'];

include($includePath."/claro_init_header.inc.php");
claro_disp_tool_title($nameTools);

foreach ($rss->items as $item) 
{
    $href = $item['link'];
    $title = $item['title'];
    echo '<div class="claroNews">'
        .'<H3 class="claroNewsTitle">'
        .'<a href="'.$href.'" lang="en">'.$title.'</a>'
        .'</H3>'
        .'<span class="claroNewsDate" >('.$item['pubdate'].')</span>'
        .'<br>'
        .'<span class="claroNewsSummary" '.$item['summary'].'</span>'
        .'</div>';
}


include($includePath."/claro_init_footer.inc.php");
?>