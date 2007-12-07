<?php // $Id$
//----------------------------------------------------------------------
// CLAROLINE
//----------------------------------------------------------------------
// Copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see 'credits' file
//----------------------------------------------------------------------

// This script allows to switch on the fly the wysiwyg editor. It retrieves the
// source url  and the textarea content, and after storing in session a value
// disabling the wysiwyg editor, it trigs a relocation to the source page with
// the area content.

require '../claro_init_global.inc.php';

$sourceUrl = preg_replace('|[&?]areaContent=.*|', '', $_REQUEST['sourceUrl'] );

$urlBinder = strpos($sourceUrl, '?') ? '&' : '?';
//$urlBinder = '&';

$content = stripslashes($_REQUEST['areaContent']);
if($_REQUEST['switch'] == 'off')
{
    $_SESSION['htmlArea'] = 'disabled';
    $areaContent = urlencode( html2txt($content) );
}
elseif ($_REQUEST['switch'] == 'on' )
{
    $_SESSION['htmlArea'] = 'enabled';
    $areaContent = urlencode(str_replace("\n", '<br />', '<!-- content: html -->'.$content));
}

header('Cache-Control: no-store, no-cache, must-revalidate');   // HTTP/1.1
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');                                     // HTTP/1.0
header('Location: '. $sourceUrl . $urlBinder . 'areaContent=' . $areaContent);

function html2txt($content)
{
    static $ruleList = array(
                             '<br[^>]*>'          => "\n"  ,
                             '<p[^>]*>'           => "\n\n",
                             '<blockquote[^>]*>'  => "\n\n",
                             '</blockquote[^>]*>' => "\n\n",
                             '<table[^>]*>'       => "\n\n",
                             '</table[^>]*>'      => "\n\n",
                             '<tr[^>]*>'          => "\n"  ,
                             '<td[^>]*>'          => "\t"  ,
                             '<hr[^>]*>'          => "\n--------------------------------------------------\n"
                            );

    foreach($ruleList as $pattern => $replace)
    {
    	$content = preg_replace('|'.$pattern.'|i', $replace , $content);
    }

    return strip_tags($content);
}





?>