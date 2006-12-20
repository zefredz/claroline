<?php // $Id$
//----------------------------------------------------------------------
// CLAROLINE
//----------------------------------------------------------------------
// Copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available
// through the world-wide-Web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see 'credits' file
//----------------------------------------------------------------------

// This page is used to launch an event when a user click to download a document

$tlabelReq = 'CLDOC';

require '../../inc/claro_init_global.inc.php';

require_once get_path('incRepositorySys') . '/lib/url.lib.php';
require_once get_path('incRepositorySys') . '/lib/file.lib.php';

$nameTools = get_lang('Display file');
$noPHP_SELF=true;

$interbredcrump[]= array ('url' => '../document.php', 'name' => get_lang('Documents and Links'));

$isDownloadable = true ;

if ( ! claro_is_in_a_course()) claro_disp_auth_form(true);

$_course = claro_get_current_course_data();
$_group  = claro_get_current_group_data();


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
    if (claro_is_in_a_group())
    {
        $groupContext  = true;
        $courseContext = false;
        $is_allowedToEdit = claro_is_group_member() ||  claro_is_group_tutor() || claro_is_course_manager();
    }
    else
    {
        $groupContext  = false;
        $courseContext = true;
        $is_allowedToEdit = claro_is_course_manager();
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

    if (claro_is_in_a_group() && claro_is_group_allowed())
    {
        $intermediatePath = claro_get_course_path(). '/group/'.claro_get_current_group_data('directory');
    }
    else
    {
        $intermediatePath = claro_get_course_path(). '/document';
    }

    if ( get_conf('secureDocumentDownload') && $GLOBALS['is_Apache'] )
    {
        // pretty url
        $pathInfo = realpath(get_path('coursesRepositorySys') . $intermediatePath . '/' . $requestUrl);
        $pathInfo = str_replace('\\', '/', $pathInfo); // OS harmonize ...
    }
    else
    {
        // TODO check if we can remove rawurldecode
        $pathInfo = get_path('coursesRepositorySys'). $intermediatePath
                    . implode ( '/',
                            array_map('rawurldecode', explode('/',$requestUrl)));
    }

    if (get_conf('CLARO_DEBUG_MODE'))
    {
        pushClaroMessage('<p>File path : ' . $pathInfo . '</p>','pathInfo');
    }
    
    $pathInfo = secure_file_path( $pathInfo );
    
    // Check if path exists in course folder

    if ( ! file_exists($pathInfo) || is_dir($pathInfo) )
    {
        $isDownloadable = false ;

        $message = '<h1>' . get_lang('Not found') . '</h1>' . "\n"
            . '<p>' . get_lang('The requested file <strong>%file</strong> was not found on the platform.',
                                array('%file' => basename($pathInfo) ) ) . '</p>' ;
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

    include get_path('incRepositorySys')  . '/claro_init_header.inc.php';

    if ( ! empty($message) )
    {
        echo claro_html_message_box($message);
    }

    include get_path('incRepositorySys')  . '/claro_init_footer.inc.php';
    exit;
}

die();

?>
