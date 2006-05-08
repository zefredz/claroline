<?php // $Id$
/**
 * CLAROLINE
 *
 * @version 1.8 $Revision$
 *
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @package CLAUTH
 *
 * @author Claro Team <cvs@claroline.net>
 */

if ((bool) stristr($_SERVER['PHP_SELF'], basename(__FILE__))) die();

if (   ! isset($_SESSION['init_CasCheckinDone'] )
    || $logout
    || ( basename($_SERVER['SCRIPT_NAME']) == 'login.php' && isset($_REQUEST['authModeReq']) && $_REQUEST['authModeReq'] == 'CAS' )
    || isset($_REQUEST['fromCasServer']) )
{
    include_once $claro_CasLibPath;
    phpCAS::client(CAS_VERSION_2_0, $claro_CasServerHostUrl, $claro_CasServerHostPort , '');

    if ($logout)
    {
        $userLoggedOnCas = false;
        phpCAS::logout($rootWeb.'index.php');
    }
    elseif( basename($_SERVER['SCRIPT_NAME']) == 'login.php' )
    {
        // set the call back url
        if     (   isset($_REQUEST['sourceUrl'])     ) $casCallBackUrl = $_REQUEST['sourceUrl'];
        elseif ( ! is_null($_SERVER['HTTP_REFERER']) ) $casCallBackUrl = $_SERVER['HTTP_REFERER'];
        else                                           $casCallBackUrl = $rootWeb;

        $casCallBackUrl .= ( strstr( $casCallBackUrl, '?' ) ? '&' : '?')
                        .  'fromCasServer=true';

        if ( $_SESSION['_cid'] )
        {
            $casCallBackUrl .= ( strstr( $casCallBackUrl, '?' ) ? '&' : '?')
                            .  'cidReq='.urlencode($_SESSION['_cid']);
        }

        if ( $_SESSION['_gid'] )
        {
            $casCallBackUrl .= ( strstr( $casCallBackUrl, '?' ) ? '&' : '?')
                         .  'gidReq='.urlencode($_SESSION['_gid']);
        }

        phpCAS::setFixedServiceURL($casCallBackUrl);
        phpCAS::forceAuthentication();

        $userLoggedOnCas                  = true;
        $_SESSION['init_CasCheckingDone'] = true;
    }
    elseif( ! isset($_SESSION['init_CasCheckinDone']) || $_REQUEST['fromCasServer'] == true )
    {

        if ( phpCAS::checkAuthentication() ) $userLoggedOnCas = true;
        else                                 $userLoggedOnCas = false;

        $_SESSION['init_CasCheckinDone'] = true;
    }

    if ($userLoggedOnCas)
    {
            $sql = "SELECT user_id  AS userId
                FROM `" . $tbl_user . "`
                WHERE username = '" . addslashes(phpCAS::getUser()) . "'
                AND   authSource = 'CAS'";

        $uData = claro_sql_query_fetch_all($sql);

        if( count($uData) > 0)
        {
            $_uid                 = $uData[0]['userId'];
            $uidReset             = true;

            $claro_loginRequested = true;
            $claro_loginSucceeded = true;
        }
        else
        {
            $_uid                 = null;

            $claro_loginRequested = true;
            $claro_loginSucceeded = false;
        }
    } // end if userLoggedOnCas


} // end if init_CasCheckinDone' || logout _SERVER['SCRIPT_NAME']) == 'login.php'

?>