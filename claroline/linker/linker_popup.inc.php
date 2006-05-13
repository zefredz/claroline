<?php // $Id$
/**
 * CLAROLINE 
 *
 * @version 1.8 $Revision$ 
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE 
 * 
 * @author claroline Team <cvs@claroline.net>
 * @author Renaud Fallier <renaud.claroline@gmail.com>
 * @author Frédéric Minne <minne@ipm.ucl.ac.be>
 *
 * @package CLLINKER
 *
 */
/**
    * linkerPopup script
    * @package CLLINKER
    * @author Fallier Renaud <renaud.claroline@gmail.com>
    **/

// for hide the claro banner
$hide_banner = TRUE;
require_once '../inc/claro_init_global.inc.php';

// Library for the linker (navigator and resolver)
require_once('navigator.lib.php');
require_once('resolver.lib.php');
require_once('CRLTool.php');
require_once('linker_sql.lib.php');
require_once('linker_popup.lib.php');
require_once('linker_popup_display.lib.php');

$htmlHeadXtra[] = '<script type="text/javascript">'
.                 'var coursecrl = \'' . CRLTool::createCRL($platform_id,$_course['sysCode']) . '\';</script>' . "\n";

$htmlHeadXtra[] = '<script type="text/javascript">'
.                 'var lang_linker_prompt_for_url = \'' . addslashes(get_lang("Enter link url")) . '\';</script>' . "\n";

$htmlHeadXtra[] = '<script type="text/javascript">'
.                 'var lang_linker_prompt_invalid_url = \'' . addslashes(get_lang("Invalid url")) . '\';</script>' . "\n";

$htmlHeadXtra[] = '<script type="text/javascript">'
.                 'var lang_linker_prompt_invalid_email = \'' . addslashes(get_lang("Invalid email address")) . '\';</script>' . "\n";

$htmlHeadXtra[] = '<script type="text/javascript" src="' . path() . '/prompt_utils.js"></script>' . "\n";

require_once '../inc/claro_init_header.inc.php';

// javascript function
echo '<script type="text/javascript">' . "\n"

.    'function linker_confirm()' . "\n"
.    '{'                         . "\n"
.    'linker_cancel();'          . "\n"
.    '}'                         . "\n"
.    'function linker_cancel()'  . "\n"
.    '{'                         . "\n"
.    'window.close();'           . "\n"
.    '}'                         . "\n"
.    '</script>'                 . "\n"
;

$isToolAllowed = claro_is_allowed_to_edit();

if ($isToolAllowed)
{
    /*-------------------------------------------------*
     * TO FIX issue in Calendar and Announcement tools *
     *-------------------------------------------------*/
    if ( !isset ($_REQUEST['cmd']) )
    {
        $linkerTLabel = ( isset( $_REQUEST['linkerTLabel'] ) ) ? $_REQUEST['linkerTLabel'] : NULL;
        $crlSource = getSourceCrl( $linkerTLabel );
        if( isset($_SESSION['claro_linker_current']) )
        {
            if(    $crlSource != $_SESSION['claro_linker_current'] )
            {
                $_SESSION['claro_linker_current'] = $crlSource;
                $_SESSION['AttachmentList'] = array();
                $_SESSION['AttachmentList']['crl'] = array();
                $_SESSION['AttachmentList']['title'] = array();
                $_SESSION['servAdd'] = array();
                $_SESSION['servDel'] = array();
            }
        }
        $_SESSION['claro_linker_current'] = $crlSource;
    }
    // END OF FIX CALENDAR

    // FIX E_ALL

    if( is_array( $_SESSION['AttachmentList'] )
    && ( ! isset($_SESSION['AttachmentList']['crl'])
    && ! isset($_SESSION['AttachmentList']['title']) ) )
    {
        $_SESSION['AttachmentList']['crl'] = array();
        $_SESSION['AttachmentList']['title'] = array();
    }

    // END OF FIX E_ALL

    // init the variable
    $baseServDir = $coursesRepositorySys;
    $baseServUrl = get_conf('rootWeb');
    $sysCode = $_course['sysCode'];
    $cmd = 'browse';
    $crl = '';
    $current_crl = CRLTool::createCRL($platform_id,$sysCode);
    $caddy = new AttachmentList();

    //-------------------------------------------------------------------------------------------------------------------------
    // init the caddy
    if ( !isset ($_REQUEST['cmd']) )
    {
        $crlSource = getSourceCrl();
        $caddy->initAttachmentList();
    }
    //-------------------------------------------------------------------------------------------------------------------------
    // get the request variable
    if ( isset ($_REQUEST['cmd']) )
    {
        $cmd = $_REQUEST['cmd'];
    }
    if ( isset ($_REQUEST['crl']) )
    {
        $crl = stripslashes($_REQUEST['crl']);
    }
    if ( isset ($_REQUEST['current_crl']) )
    {
        $current_crl = stripslashes($_REQUEST['current_crl']);
    }
    //-------------------------------------------------------------------------------------------------------------------------
    // command processing
    if ($cmd == 'browse' || $cmd == 'delete')
    {
        if( $cmd == 'delete')
        {

            $caddy->removeItem($crl);
        }

        if($current_crl != $crl)
        {
            displayNav($baseServDir, $current_crl);
        }
        else
        {
            displayInterfaceOfMyOtherCourse($baseServDir , $current_crl);
        }
    }
    else if($cmd == 'add')
    {
        if(! ($caddy->addItem($crl)) )
        {
            $res = new Resolver('');
            $title = $res->getResourceName($crl);
            echo claro_html_message_box('[' . $title . ']' . get_lang(" is already attached"));
        }

        if($current_crl != $crl)
        {
            displayNav($baseServDir,$current_crl);
        }
        else
        {
            displayInterfaceOfMyOtherCourse($baseServDir , $current_crl);
        }
    }
    else if($cmd == 'browseMyCourses')
    {
        displayInterfaceOfMyOtherCourse($baseServDir , $current_crl);
    }
    else if($cmd == 'browsePublicCourses')
    {
        displayInterfaceOfPublicCourse($baseServDir , $current_crl);
    }
    else
    {
        echo 'acces denied<br />' . "\n";
    }
}
else
{
    echo '<h2>acces denied because you are a student or that the course is not select</h2><br />'. "\n";
}

require_once '../inc/claro_init_footer.inc.php';

?>