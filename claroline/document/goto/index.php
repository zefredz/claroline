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
    
$nameTools = get_lang('Display file');
$noPHP_SELF=true;

$interbredcrump[]= array ('url' => '../document.php', 'name' => get_lang('Documents and Links'));

$isDownloadable = true ;

if ( ! $_cid) claro_disp_auth_form(true);

if ( isset($_REQUEST['url']) )
{
    $requestUrl = $_REQUEST['url'];
}
else
{
    $requestUrl = get_path_info();
}

if ( empty($requestUrl) )
{
    $isDownloadable = false ;
    $message = get_lang('Missing parameters');
}
else
{
    if ($_gid)
    {
        $groupContext  = true;
        $courseContext = false;
        $is_allowedToEdit = $is_groupMember || $is_groupTutor || $is_courseAdmin;
    }
    else
    {
        $groupContext  = false;
        $courseContext = true;
        $is_allowedToEdit = $is_courseAdmin;
    }

    if ($courseContext)
    {
        $courseTblList = claro_sql_get_course_tbl();
        $tbl_document =  $courseTblList['document'];

        $sql = 'SELECT visibility 
                FROM `'.$tbl_document.'`
                WHERE path = "'.addslashes($requestUrl).'"';

        $docVisibilityStatus = claro_sql_query_get_single_value($sql);

        if (    ( ! is_null($docVisibilityStatus) ) // hidden document can only be viewed by course manager
             && $docVisibilityStatus == 'i'
             && ( ! $is_allowedToEdit ) )
        {
            $isDownloadable = false ;
            $message = get_lang('Not allowed');
        }
    }

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
        // pretty url
        $pathInfo = realpath($coursesRepositorySys . $intermediatePath . '/' . $requestUrl);
        $pathInfo = str_replace('\\', '/', $pathInfo); // OS harmonize ...
    }
    else
    {
        // TODO check if we can remove rawurldecode
        $pathInfo = $coursesRepositorySys. $intermediatePath 
                    . implode ( '/',   
                            array_map('rawurldecode', explode('/',$requestUrl)));
    }
        
    if (get_conf('CLARO_DEBUG_MODE'))
    {
        pushClaroMessage('<p>File path : ' . $pathInfo . '</p>','pathInfo');
    }

    // Check if path exists in course folder

    if ( preg_match('|^'.$coursesRepositorySys . $intermediatePath.'|', $pathInfo) )
    {
        if ( ! file_exists($pathInfo) || is_dir($pathInfo) )
        {
            $isDownloadable = false ;
        
            $message = '<h1>' . get_lang('Not found') . '</h1>' . "\n"
                . '<p>' . get_lang('The requested file <strong>%file</strong> was not found on the platform.', 
                                    array('%file' => basename($pathInfo) ) ) . '</p>' ;
        }

    }
    else
    {
        // file outside of the course document folder
        $isDownloadable = false ;
        $message = get_lang('Not allowed');
    }   

}

// Output section
            
if ( $isDownloadable )
{
    if( claro_send_file( $pathInfo )  > 0 )
    {
        event_download( $requestUrl );
    }
}
else
{
    header('HTTP/1.1 404 Not Found'); 

    include $includePath  . '/claro_init_header.inc.php';

    if ( ! empty($message) )
    {
        echo claro_html_message_box($message);
    }

    include $includePath  . '/claro_init_footer.inc.php';
    exit;
}

die();

?>
