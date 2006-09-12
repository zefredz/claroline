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

// This page is used to launch an event when a user click to download a document

$tlabelReq = 'CLDOC';

require '../../inc/claro_init_global.inc.php';

require_once $includePath . '/lib/url.lib.php';
require_once $includePath . '/lib/file.lib.php';

if (isset($_REQUEST['url']) )
{
    $requestUrl = $_REQUEST['url'];
}
else
{
    $requestUrl = stripslashes( urldecode (get_slashed_argument( get_request_uri(), 
                                           'document/goto/index.php' ) ) );
}

if ( ! $_cid) claro_disp_auth_form(true);

if ($_gid)
{
    $groupContext  = true;
    $courseContext = false;
    $is_allowedToEdit = $is_groupMember || $is_groupTutor|| $is_courseAdmin;
}
else
{
    $groupContext  = false;
    $courseContext = true;
    $is_allowedToEdit = $is_courseAdmin;
}

if ( empty($requestUrl) )
{ 
    header('HTTP/1.1 404 Not Found'); exit; 
}
else
{

    if ($courseContext)
    {
        $courseTblList = claro_sql_get_course_tbl();
        $tbl_document =  $courseTblList['document'];

        $sql = 'SELECT visibility FROM `'.$tbl_document.'`
                WHERE path = "'.addslashes($requestUrl).'"';

        $docVisibilityStatus = claro_sql_query_get_single_value($sql);

        if (    ( ! is_null($docVisibilityStatus) ) // hidden document can only be viewed by course manager
             && $docVisibilityStatus == 'i'
             && ( ! $is_allowedToEdit ) )
        {
           // header('Status: 404 Not Found'); exit; 
           header('HTTP/1.1 404 Not Found'); exit; 
        }
    }
}


// event_download($requestUrl);

if ($_gid && $is_groupAllowed)
{
    $intermediatePath = $_course['path']. '/group/'.$_group['directory'];
}
else
{
    $intermediatePath = $_course['path']. '/document';
}

if ( get_conf('secureDocumentDownload') && $GLOBALS['is_Apache'] )
{
    $pathInfo = realpath($coursesRepositorySys . $intermediatePath . '/' . $requestUrl);
    $pathInfo = str_replace('\\', '/', $pathInfo); // OS harmonize ...
}
else
{
    $pathInfo = $coursesRepositorySys. $intermediatePath 
                . implode ( '/',   
                            array_map('rawurlencode', explode('/',$requestUrl)));
}

// Check if path exists in course folder

if ( preg_match('|^'.$coursesRepositorySys . $intermediatePath.'|', $pathInfo) )
{
    if (file_exists($pathInfo) && ! is_dir($pathInfo) )
    {
        $mimeType = get_mime_on_ext( basename($pathInfo) );
        if ( ! is_null($mimeType) ) header('Content-Type: '.$mimeType);
        if( readfile($pathInfo)  > 0) event_download($requestUrl);
    }
    else
    {
        header('HTTP/1.1 404 Not Found'); exit;
    }
}
else
{
    header('HTTP/1.1 404 Not Found'); exit;
}

die();

?>
