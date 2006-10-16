<?php // $Id$
/**
 * CLAROLINE
 *
 * Build the frameset for chat.
 *
 * @version 1.8 $Revision$
 *
 * @copyright 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @see http://www.claroline.net/wiki/index.php/CLRSS
 *
 * @package CLRSS
 *
 * @author Claro Team <cvs@claroline.net>
 * @author Christophe Gesché <moosh@claroline.net>
 */

$_course = array();
$siteName ='';
$is_courseAllowed = false;
require '../inc/claro_init_global.inc.php';
include claro_get_conf_repository() . 'rss.conf.php';

// RSS enabled
if ( ! get_conf('enableRssInCourse') )
{
    // Codes Status HTTP 404 for rss feeder
    header('HTTP/1.0 404 Not Found');
    exit;
}

if(!$_cid)
{
    die( '<form >cidReq = <input name="cidReq" type="text" ><input type="submit"></form>');
}

if ( !$_course['visibility'] && !$is_courseAllowed )
{
    if (!isset($_SERVER['PHP_AUTH_USER']))
    {
        header('WWW-Authenticate: Basic realm="'. get_lang('Rss feed for %course', array('%course' => $_course['name']) ) . '"');
        header('HTTP/1.0 401 Unauthorized');
        echo '<h2>' . get_lang('You need to be authenticated with your %sitename account', array('%sitename'=>$siteName) ) . '</h2>'
        .    '<a href="index.php?cidReq=' . $_cid . '">' . get_lang('Retry') . '</a>'
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
        if (!$_course['visibility'] && !$is_courseAllowed)
        {
            header('WWW-Authenticate: Basic realm="'. get_lang('Rss feed for %course', array('%course' => $_course['name']) ) .'"');
            header('HTTP/1.0 401 Unauthorized');
            echo '<h2>' . get_lang('You need to be authenticated with your %sitename account', array('%sitename'=>$siteName) ) . '</h2>'
            .    '<a href="index.php?cidReq=' . $_cid . '">' . get_lang('Retry') . '</a>'
            ;
            exit;
        }
    }
}

// OK TO SEND FEED
include $includePath . '/lib/rss.write.lib.php';

header('Content-type: text/xml;');
readfile (build_rss(array('course' => $_cid)));

?>