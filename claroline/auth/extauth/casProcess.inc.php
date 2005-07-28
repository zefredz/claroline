<?php # -$Id$

if (   ! isset($_SESSION['init_CasCheckinDone'] )
    || $logout 
    || basename($_SERVER['SCRIPT_NAME']) == 'login.php')
{
    include_once $claro_CasLibPath;
    phpCAS::client(CAS_VERSION_2_0, $claro_CasSeverHostUrl, $claro_CasSeverHostPort, '');

    // set the call back url

    $casCallBackUrl = $_SERVER['HTTP_REFERER'];

    if ( $_SESSION['_cid'] )
    {
        $casCallBackUrl .= ( strstr( $_SERVER['HTTP_REFERER'], '?' ) ? '&' : '?') 
                     .  'cidReq='.urlencode($_SESSION['_cid']);
    }

    if ( $_SESSION['_gid'] )
    {
        $casCallBackUrl .= ( strstr( $_SERVER['HTTP_REFERER'], '?' ) ? '&' : '?') 
                     .  'gidReq='.urlencode($_SESSION['_gid']);
    }

    // phpCAS::setFixedServiceURL ($casCallBackUrl);

    if ($logout)
    {
        phpCAS::logout($rootWeb.'index.php');
        $userLoggedOnCas = false;
    }
    elseif( basename($_SERVER['SCRIPT_NAME']) == 'login.php' )
    {
        phpCAS::forceAuthentication();
        $userLoggedOnCas              = true;
        $_SESSION['init_CasChecking'] = true;
    }
    elseif( ! isset($_SESSION['init_CasCheckinDone']) )
    {
        if ( phpCAS::checkAuthentication() ) $userLoggedOnCas = true;
        else                                 $userLoggedOnCas = false;

        $_SESSION['init_CasCheckinDone'] = true;
    }

    if ($userLoggedOnCas)
    {
        $sql = "SELECT user_id  AS userId
                FROM `".$tbl_user."`
                WHERE username = '". addslashes(phpCAS::getUser())."'
                AND   authSource = 'CAS'";

        $uData = claro_sql_query_fetch_all($sql);
       
        if( count($uData) > 0)
        {
            $_uid        = $uData[0]['userId'];
            $uidReset    = true;            
            $loginFailed = false;
        }
        else
        {
            $_uid = null;
            $loginFailed = true;
        }
    } // end if userLoggedOnCas

} // end if init_CasCheckinDone' || logout _SERVER['SCRIPT_NAME']) == 'login.php'


?>