<?php // $Id$
/**
 * CLAROLINE
 *
 * request an iCal for a tool
 *
 * @version 1.8 $Revision$
 *
 * @copyright 2001-2007 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @see http://www.claroline.net/wiki/index.php/CLRSS
 *
 * @package CLICAL
 * @since 1.8
 *
 * @author Claro Team <cvs@claroline.net>
 * @author Christophe Gesché <moosh@claroline.net>
 */
 
// moved to backends
die( "Moved to backends !" );

$_course = array();
$siteName ='';
$is_courseAllowed = false;

require '../inc/claro_init_global.inc.php';
include_once claro_get_conf_repository() . 'ical.conf.php';
include_once get_path('incRepositorySys') . '/lib/ical.write.lib.php';
$formatList = array('ics'=>'iCalendar','xcs'=>'xCalendar (xml)','rdf'=>'rdf');

if ( ! get_conf('enableICalInCourse') )
{
    // Codes Status HTTP 404 for rss feeder
    header('HTTP/1.0 404 Not Found');
    exit;
}

$calType = (array_key_exists('calFormat',$_REQUEST) && array_key_exists($_REQUEST['calFormat'],$formatList))?$_REQUEST['calFormat']:get_conf('calType','ics');

// need to be in a course
if( ! claro_is_in_a_course() )
{
    die( '<form >cidReq = <input name="cidReq" type="text"  /><input type="submit" /></form>');
}

if ( !$_course['visibility'] && !claro_is_course_allowed() )
{
    if (!isset($_SERVER['PHP_AUTH_USER']))
    {
        header('WWW-Authenticate: Basic realm="'. get_lang('iCal feed for %course', array('%course' => $_course['name']) ) . '"');
        header('HTTP/1.0 401 Unauthorized');
        echo '<h2>' . get_lang('You need to be authenticated with your %sitename account', array('%sitename'=>$siteName) ) . '</h2>'
        .    '<a href="index.php?cidReq=' . claro_get_current_course_id() . '">' . get_lang('Retry') . '</a>'
        ;
        exit;
    }
    else
    {
        if ( get_magic_quotes_gpc() ) // claro_unquote_gpc don't wash
        {
            $_REQUEST['login']    = stripslashes($_SERVER['PHP_AUTH_USER']);
            $_REQUEST['password'] = stripslashes($_SERVER['PHP_AUTH_PW']);
        }
        else
        {
            $_REQUEST['login']    = $_SERVER['PHP_AUTH_USER'];
            $_REQUEST['password'] = $_SERVER['PHP_AUTH_PW'] ;
        }
        require '../inc/claro_init_local.inc.php';
        if (!$_course['visibility'] && !claro_is_course_allowed())
        {
            header('WWW-Authenticate: Basic realm="'. get_lang('iCal feed for %course', array('%course' => $_course['name']) ) .'"');
            header('HTTP/1.0 401 Unauthorized');
            echo '<h2>' . get_lang('You need to be authenticated with your %sitename account', array('%sitename'=>$siteName) ) . '</h2>'
            .    '<a href="index.php?cidReq=' . claro_get_current_course_id() . '">' . get_lang('Retry') . '</a>'
            ;
            exit;
        }
    }
}

// OK TO SEND FEED


header('Content-type: ' . get_ical_MimeType($calType) . ';');
readfile ( buildICal(array(CLARO_CONTEXT_COURSE=> claro_get_current_course_id()), $calType));

?>