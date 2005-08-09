<?php // $Id$
//----------------------------------------------------------------------
// CLAROLINE
//----------------------------------------------------------------------
// Copyright (c) 2001-2003 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see 'credits' file
//----------------------------------------------------------------------

// This page is used to launch an event when a user click to download a document
// - It gets name of the document
// - It calls the event function
// - It redirects the user to the download
// Need document.id, user.user_id, cours.cours_id
// when called
// http://.../document_dl.php?doc_url=$urlFileName&user_id=$uid&cid=$currenCourseID
// 
// REM : 
// -----
// Line  echo "<a href=\"".$urlFileName."\"".$style.">\n"; in document.php, 
// must be replaced 2 times by 
// echo "<a href=\"document_goto.php?doc_url=".urlencode($urlFileName)."\"".$style.">";

require '../../inc/claro_init_global.inc.php';

require $includePath.'/lib/events.lib.inc.php';

$requestUrl = stripslashes( urldecode (get_slashed_argument( $_SERVER['REQUEST_URI'], 
                                       'document/goto/index.php' ) ) );

event_download($requestUrl);

if ($_gid && $is_groupAllowed)
{
	$intermediatePath = $_course['path']. '/group/'.$_group['directory'];
}
else
{
	$intermediatePath = $_course['path']. '/document'; 
}

if ($secureDocumentDownload)
{
    $pathInfo = realpath($coursesRepositorySys . $intermediatePath . '/' . $requestUrl);
    $pathInfo = str_replace('\\', '/', $pathInfo); // OS harmonize ...

    if ( preg_match('|^'.$coursesRepositorySys . $intermediatePath.'|', $pathInfo) )
    {
        if (file_exists($pathInfo) && ! is_dir($pathInfo) )
        {
            readfile($pathInfo);
        }
    }
}
else
{
    header('Cache-Control: no-store, no-cache, must-revalidate');   // HTTP/1.1
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache');                                     // HTTP/1.0

    $doc_dl_url = $coursesRepositoryWeb. $intermediatePath 
                . implode ( '/',   
                            array_map('rawurlencode', explode('/',$requestUrl)));

    header('Location: ' . $doc_dl_url);
    //header("Content-Location: $doc_dl_url");

    // if the browser doesn't support the location header
    echo  $langIfNotRedirect
        .'<a href="'.$doc_dl_url.'">'.$lang_click_here.'</a> .';

    // exit to be sure the script stop running
    exit;
} // end else if $secureDownload


function get_slashed_argument($completePath, $baseFile)
{
    $pahtElementList = explode($baseFile, $completePath);
    return $pahtElementList[1];
}



?>