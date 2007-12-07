<?php // $Id$
/**
    +-------------------------------------------------------------------+
    | CLAROLINE version 1.6.*                               |
    +-------------------------------------------------------------------+
    | Copyright (c) 2001, 2002 Universite catholique de Louvain (UCL)   |
    +-------------------------------------------------------------------+
    | Authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>             |
    |          Hugues Peeters    <peeters@ipm.ucl.ac.be>                |
    |          Christophe Gesché <gesche@ipm.ucl.ac.be>                 |
    +-------------------------------------------------------------------+
    |   This page is used to launch an event when a user click          |
    |   to download a document                                          |
    |   - It gets name of the document                                  |
    |   - It calls the event function                                   |
    |   - It redirects the user to the download                         |
    |                                                                   |
    |   Need document.id, user.user_id, cours.cours_id                  |
    |   when called                                                     |
    |   http://.../document_dl.php?doc_url=$urlFileName&user_id=$uid&cid=$currenCourseID
    +-------------------------------------------------------------------+
    
    REM : 
    -----
    Line 
        echo "<a href=\"".$urlFileName."\"".$style.">\n";
    in document.php, must be replaced 2 times by 
        echo "<a href=\"document_goto.php?doc_url=".urlencode($urlFileName)."\"".$style.">";
*/
require '../../inc/claro_init_global.inc.php';

include($includePath."/lib/events.lib.inc.php");

// launch event
$doc_url = stripslashes(urldecode($doc_url));
event_download($doc_url);


header("Cache-Control: no-store, no-cache, must-revalidate");   // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");                                     // HTTP/1.0

if ($_gid && $is_groupAllowed)
{
  $doc_dl_url = $coursesRepositoryWeb.$_course['path']."/group/".$_group['directory'].implode ( "/",   array_map("rawurlencode", explode("/",$doc_url)));
}
else
{
  $doc_dl_url = $coursesRepositoryWeb.$_course['path']."/document".implode ( "/",   array_map("rawurlencode", explode("/",$doc_url)));
}

header('Location: ' . http_response_splitting_workaround($doc_dl_url) );
//header("Content-Location: $doc_dl_url");
// if the browser doesn't support the location header
echo $langIfNotRedirect."<a href='".$doc_dl_url."'>".$lang_click_here."</a> .";
// to be sure the script stop running
exit;

?>
