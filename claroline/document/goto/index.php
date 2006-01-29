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

require '../../inc/claro_init_global.inc.php';

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

if ( get_conf('secureDocumentDownload')
    && strstr($_SERVER['SERVER_SOFTWARE'], 'Apache') )
{
    $pathInfo = realpath($coursesRepositorySys . $intermediatePath . '/' . $requestUrl);
    $pathInfo = str_replace('\\', '/', $pathInfo); // OS harmonize ...

    if ( preg_match('|^'.$coursesRepositorySys . $intermediatePath.'|', $pathInfo) )
    {
        if (file_exists($pathInfo) && ! is_dir($pathInfo) )
        {
            $mimeType = get_mime_on_ext( basename($pathInfo) );
            if ( ! is_null($mimeType) ) header('Content-Type: '.$mimeType);
            if( readfile($pathInfo)  > 0) event_download($requestUrl);
        }
    }
    else
    {
        header('HTTP/1.1 404 Not Found'); exit;
    }
}
else
{
    header('Cache-Control: no-store, no-cache, must-revalidate');   // HTTP/1.1
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache');                                     // HTTP/1.0

    // check that the file really exists before trigging the download tracking
    if ( file_exists($coursesRepositorySys . $intermediatePath . $requestUrl) )
    {
        event_download($requestUrl);
    }

    $doc_dl_url = $coursesRepositoryWeb. $intermediatePath 
                . implode ( '/',   
                            array_map('rawurlencode', explode('/',$requestUrl)));

    header('Location: ' . http_response_splitting_workaround( $doc_dl_url ) );
    //header("Content-Location: $doc_dl_url");

    // if the browser doesn't support the location header
    echo  get_lang('IfNotRedirect')
        .'<a href="'.$doc_dl_url.'">'.get_lang('_click_here').'</a> .';

    // exit to be sure the script stop running
    exit();
} // end else if $secureDownload


function get_slashed_argument($completePath, $baseFile)
{
    
    $pahtElementList = explode($baseFile, $completePath);

    if ( count($pahtElementList) > 1)
    {
        $argument = array_pop($pahtElementList);

        $questionMarkPos = strpos($argument, '?');
        
        if (is_int($questionMarkPos)) return substr($argument, 0, $questionMarkPos);
        else                          return $argument;
        
    }
    else
    {
        return '';
    }
}

/**
 * Returns the name of the current script, WITH the querystring portion.
 * this function is necessary because PHP_SELF and REQUEST_URI and SCRIPT_NAME
 * return different things depending on a lot of things like your OS, Web
 * server, and the way PHP is compiled (ie. as a CGI, module, ISAPI, etc.)
 * <b>NOTE:</b> This function returns false if the global variables needed are not set.
 *
 * @return string
 */
 function get_request_uri() {

    if (!empty($_SERVER['REQUEST_URI'])) {
        return $_SERVER['REQUEST_URI'];

    } else if (!empty($_SERVER['PHP_SELF'])) {
        if (!empty($_SERVER['QUERY_STRING'])) {
            return $_SERVER['PHP_SELF'] .'?'. $_SERVER['QUERY_STRING'];
        }
        return $_SERVER['PHP_SELF'];

    } else if (!empty($_SERVER['SCRIPT_NAME'])) {
        if (!empty($_SERVER['QUERY_STRING'])) {
            return $_SERVER['SCRIPT_NAME'] .'?'. $_SERVER['QUERY_STRING'];
        }
        return $_SERVER['SCRIPT_NAME'];

    } else if (!empty($_SERVER['URL'])) {     // May help IIS (not well tested)
        if (!empty($_SERVER['QUERY_STRING'])) {
            return $_SERVER['URL'] .'?'. $_SERVER['QUERY_STRING'];
        }
        return $_SERVER['URL'];

    } else {
        notify('Warning: Could not find any of these web server variables: $REQUEST_URI, $PHP_SELF, $SCRIPT_NAME or $URL');
        return false;
    }
}

function get_mime_on_ext($fileName)
{
    $mimeType = null;

    /*
     * Check if the file has an extension AND if the browser has send a MIME Type
     */

    if( preg_match('|.[[:alnum:]]+$|', $fileName, $match) )
    {
        $fileExtension = $match[0];

        /*
         * Build a "MIME-types / extensions" connection table
         */

        $mimeTypeList = array(); $extension = array();

        $mimeTypeList[] = 'text/plain';                     $extensionList[] ='.txt';
        $mimeTypeList[] = 'application/msword';             $extensionList[] ='.doc';
        $mimeTypeList[] = 'application/rtf';                $extensionList[] ='.rtf';
        $mimeTypeList[] = 'application/vnd.ms-powerpoint';  $extensionList[] ='.ppt';
        $mimeTypeList[] = 'application/vnd.ms-powerpoint';  $extensionList[] ='.pps';
        $mimeTypeList[] = 'application/vnd.ms-excel';       $extensionList[] ='.xls';
        $mimeTypeList[] = 'application/pdf';                $extensionList[] ='.pdf';
        $mimeTypeList[] = 'application/postscript';         $extensionList[] ='.ps';
        $mimeTypeList[] = 'application/mac-binhex40';       $extensionList[] ='.hqx';
        $mimeTypeList[] = 'application/x-gzip';             $extensionList[] ='tar.gz';
        $mimeTypeList[] = 'application/x-shockwave-flash';  $extensionList[] ='.swf';
        $mimeTypeList[] = 'application/x-stuffit';          $extensionList[] ='.sit';
        $mimeTypeList[] = 'application/x-tar';              $extensionList[] ='.tar';
        $mimeTypeList[] = 'application/zip';                $extensionList[] ='.zip';
        $mimeTypeList[] = 'application/x-tar';              $extensionList[] ='.tar';
        $mimeTypeList[] = 'text/html';                      $extensionList[] ='.htm';
        $mimeTypeList[] = 'text/plain';                     $extensionList[] ='.txt';
        $mimeTypeList[] = 'text/rtf';                       $extensionList[] ='.rtf';
        $mimeTypeList[] = 'img/gif';                        $extensionList[] ='.gif';
        $mimeTypeList[] = 'img/jpeg';                       $extensionList[] ='.jpg';
        $mimeTypeList[] = 'img/png';                        $extensionList[] ='.png';
        $mimeTypeList[] = 'audio/midi';                     $extensionList[] ='.mid';
        $mimeTypeList[] = 'audio/mpeg';                     $extensionList[] ='.mp3';
        $mimeTypeList[] = 'audio/x-aiff';                   $extensionList[] ='.aif';
        $mimeTypeList[] = 'audio/x-pn-realaudio';           $extensionList[] ='.rm';
        $mimeTypeList[] = 'audio/x-pn-realaudio-plugin';    $extensionList[] ='.rpm';
        $mimeTypeList[] = 'audio/x-wav';                    $extensionList[] ='.wav';
        $mimeTypeList[] = 'video/mpeg';                     $extensionList[] ='.mpg';
        $mimeTypeList[] = 'video/quicktime';                $extensionList[] ='.mov';
        $mimeTypeList[] = 'video/x-msvideo';                $extensionList[] ='.avi';

        /*
         * Check if the MIME type send by the browser is in the table
         */

        foreach($extensionList as $key => $extension)
        {
            if ($extension == $fileExtension)
            {
                $mimeType = $mimeTypeList[$key];
                break;
            }
        }
    }

    return $mimeType;
}


?>
