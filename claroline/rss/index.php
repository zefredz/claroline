<?php // $Id$
/** 
 * CLAROLINE 
 *
 * Build the frameset for chat.
 *
 * @version 1.7 $Revision$
 *
 * @copyright 2001-2005 Universite catholique de Louvain (UCL)
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
$langRetry = 'retry';
$is_courseAllowed = false;
require '../inc/claro_init_global.inc.php';
if(!$_cid) 
{
    
    die( '<form >cidReq = <input name="cidReq" type="text" ><input type="submit"></form>');
}

if (!$_course['visibility'] && !$is_courseAllowed)
{
    if (!isset($_SERVER['PHP_AUTH_USER'])) 
    {
       header('WWW-Authenticate: Basic realm="'.sprintf($lang_p_FeedOf_s, $_course['name']).'"');
       header('HTTP/1.0 401 Unauthorized');
       echo '<h2>' . sprintf($lang_p_youNeedToBeAuthenticatedWithYour_s_account, $siteName) . '</h2>'
       .    '<a href="index.php?cidReq=' . $_cid . '">' . $langRetry . '</a>'
       ;
       exit;
    } 
    else 
    {
            $login    = $_SERVER['PHP_AUTH_USER'];
            $password = $_SERVER['PHP_AUTH_PW'];
            require '../inc/claro_init_local.inc.php';
            if (!$_course['visibility'] && !$is_courseAllowed)
            {
                   header('WWW-Authenticate: Basic realm="'.sprintf($lang_p_FeedOf_s, $_course['name']).'"');
                   header('HTTP/1.0 401 Unauthorized');
                   echo '<h2>' . sprintf($lang_p_youNeedToBeAuthenticatedWithYour_s_account, $siteName) . '</h2>'
                   .    '<a href="index.php?cidReq=' . $_cid . '">' . $langRetry . '</a>'
                   ;
                   exit;
            }
    }
}

// OK TO SEND FEED

include_once $includePath . '/conf/rss.conf.php';
include( $includePath . '/lib/rss/write/gencourse_rss.inc.php');

header('Content-type: text/xml;'); 
readfile (build_course_feed(!$use_rss_cache, $_cid));

?>