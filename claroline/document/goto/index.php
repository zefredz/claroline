<?php // $Id$

/**
 * Download a document
 *
 * @version     1.8 $Revision$
 * @copyright   2001-2007 Universite catholique de Louvain (UCL)
 * @author      Claroline Team <info@claroline.net>
 * @license     http://www.gnu.org/copyleft/gpl.html
 *              GNU GENERAL PUBLIC LICENSE version 2.0
 * @package     CLDOC
 */

require '../../inc/claro_init_global.inc.php';

require_once get_path('incRepositorySys') . '/lib/url.lib.php';
require_once get_path('incRepositorySys') . '/lib/file.lib.php';

$nameTools = get_lang('Display file');

$noPHP_SELF=true;

$interbredcrump[]= array ('url' => '../document.php', 'name' => get_lang('Documents and Links'));

$isDownloadable = true ;

if ( ! claro_is_in_a_course() || ! claro_is_course_allowed() ) claro_disp_auth_form(true);

$_course = claro_get_current_course_data();
$_group  = claro_get_current_group_data();

if ( isset($_REQUEST['url']) )
{
    $requestUrl = strip_tags($_REQUEST['url']);
}
else
{
    $requestUrl = strip_tags(get_path_info());
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
        // check document visibility only if we are not coming from the Learning Path
        if ( isset( $_SESSION ) && is_array( $_SESSION ) 
            && array_key_exists( '_courseTool', $_SESSION ) 
            && is_array($_SESSION['_courseTool'])
            && array_key_exists( 'label', $_SESSION['_courseTool'] )
            && $_SESSION['_courseTool']['label'] == 'CLLNP'
        )
        {
            // do nothing
        }
        else
        {
            $courseTblList = claro_sql_get_course_tbl();
            $tbl_document =  $courseTblList['document'];

            $sql = 'SELECT visibility
                    FROM `'.$tbl_document.'`
                    WHERE path = "'.addslashes($requestUrl).'"';

            $docVisibilityStatus = claro_sql_query_get_single_value($sql);
            
            // hidden document can only be viewed by course manager            
            if (    ( ! is_null($docVisibilityStatus) ) 
                 && $docVisibilityStatus == 'i'
                 && ( ! $is_allowedToEdit ) )
            {
                $isDownloadable = false ;
                $message = get_lang('Not allowed');
            }
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
    session_write_close();
    
    $extension = get_file_extension($pathInfo);
    $mimeType = get_mime_on_ext($pathInfo);

    if ( $mimeType == 'text/html' && $extension != 'url' )
    {
        event_download($requestUrl);
        
        if (substr(PHP_OS, 0, 3) == "WIN")
        {
            $rootSys =  str_replace( '//', '/', strtolower( str_replace('\\', '/', $rootSys) ) );
            $pathInfo = strtolower( str_replace('\\', '/', $pathInfo) );
        }

        // replace rootSys by urlAppend, encode url for ie7, don't encode '/'
        $document_url = $urlAppend . '/' . rawurlencode(str_replace($rootSys,'',$pathInfo));
        $document_url = str_replace('%2F', '/', $document_url);

        // redirect to document
        claro_redirect($document_url);
       
        die();
    }
    else
    {
        if( get_conf('useSendfile', true) )
        {
            if ( claro_send_file( $pathInfo )  !== false )
            {
                event_download( $requestUrl );
            }
            else
            {
                header('HTTP/1.1 404 Not Found');
                claro_die( get_lang('File download failed : %failureMSg%',
                    array( '%failureMsg%' => claro_failure::get_last_failure() ) ) );
                die();
            }
        }
        else
        {
            if (substr(PHP_OS, 0, 3) == "WIN")
            {
                $rootSys =  str_replace( '//', '/', strtolower( str_replace('\\', '/', $rootSys) ) );
                $pathInfo = strtolower( str_replace('\\', '/', $pathInfo) );
            }
            
            // replace rootSys by urlAppend, encode url for ie7, don't encode '/'
            $document_url = $urlAppend . '/' . rawurlencode(str_replace($rootSys,'',$pathInfo));
            $document_url = str_replace('%2F', '/', $document_url);

            claro_redirect($document_url);

            die();
        }
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
