<?php # -$Id$

/*
 * Single Sign On (SSO) Soap client allowing a system to request user parameter 
 * from a cookie retrieved on the user browser. The Soap request will return a 
 * sso updated cookie. It's the job of the soap client to update the cookie 
 * into the user browser.
 */

$nuSoapPath = '../../inc/lib/nusoap.php'; // adapt this path to your own need

require $nuSoapPath;

$cookieName = 'icampusSsoCookie';

if ( isset($_COOKIE[$cookieName]) )
{
    $debugCookie = $_COOKIE;

    $url = 'http://phedre.ipm.ucl.ac.be/claroline/claroline/auth/sso/server.php';

    $paramList = array( 'cookie' => $_COOKIE[$cookieName], 'auth'=>'blablabla' );

    $client = new soapclient($url);

    $result = $client->call('get_user_info_from_cookie', $paramList);

    if ($client->getError())
    {
        echo '<h1>Soap error</1>';

        echo "<p>"
             ."SOAP fault : [".$result['faultcode']."] ".$result['faultstring']
             ."</p>\n";
    }
    elseif (is_array($result) )
    {
         setcookie($result['ssoCookieName'      ], 
                   $result['ssoCookieValue'     ], 
                   $result['ssoCookieExpireTime'], 
                   '/', 
                   $result['ssoCookieDomain'    ]);
    }


///* <DEBUG> */
//echo "<pre> SOAP result";
//var_dump($result);
//echo "</pre>";
///* </DEBUG> */

}







?>