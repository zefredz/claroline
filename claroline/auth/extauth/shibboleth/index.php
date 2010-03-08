<?php // $Id$

/**
 * Claroline Shibboleth / Switch AAI
 *
 * Authenticate User with Shibboleth authSource
 *
 * @version 0.4
 *
 * @author Daniel Streiff <daniel.streiff@fh-htwchur.ch>
 *
 */
 /*       
// Shibboleth attributes available, process login
$_REQUEST['shibbolethLogin'] = true;
 */
require ('../../../inc/claro_init_global.inc.php');
require_once get_path('incRepositorySys') . '/lib/auth/sso.lib.php';
require_once( claro_get_conf_repository() . '/sso/shibboleth.conf.php' );
/*
// The unique id has to contain something
if ( isset($_SERVER[$shibbolethUniqueIdAttr]) )
{
    if ( !$_SERVER[$shibbolethUniqueIdAttr] == '' )
    {
        // Redirect to rootWeb
        if ( isset($_REQUEST['sourceUrl']) )
        {
            $sourceUrl = base64_decode($_REQUEST['sourceUrl']);
            claro_redirect($sourceUrl);
        }
        else
        {
            claro_redirect($rootWeb);            
        }
    }
    else
    {
        // Shibboleth authentication failed
        claro_die('<center>WARNING ! SHIBBOLETH AUTHENTICATION FAILED.</center>');
    }
}
else
{
    // Directory not protected
    claro_die('<center>WARNING ! PROTECT THIS FOLDER IN YOUR WEBSERVER CONFIGURATION.</center>');
}*/

$driver = new shibbolethAuthDriver( 'shibboleth' );


if( isset( $_POST['SAMLResponse'] ) )
{
    $samlReponse =  utf8_decode( base64_decode( $_POST['SAMLResponse'] ) );
    
    $test = simplexml_load_string( $samlReponse );
    $test->registerXPathNamespace('saml2',     'urn:oasis:names:tc:SAML:2.0:assertion');
    $statusCode = $test->xpath('saml2p:Status/saml2p:StatusCode');
    if( $statusCode[0]->attributes()->Value == 'urn:oasis:names:tc:SAML:2.0:status:Success' )
    {
     $user = array();
     $data =  $test->xpath('saml2:Assertion/saml2:AttributeStatement/saml2:Attribute');
     foreach( $data as $id => $d )
     {
        $FriendlyName = $d[0]->attributes()->FriendlyName;
        $value = $d[0]->xpath( 'saml2:AttributeValue' );
        $value = (string) $value[0][0];
        switch( $FriendlyName )
        {
            case 'o' : $user['organization'] = $value; break;
            case 'sn' : $user['lastName'] = $value; break;
            case 'cn' : $user['userID'] = $value; break;
            case 'mail' : $user['email'] = $value; break;
            case 'givenName' : $user['firstName'] = $value;
        }
     }
     var_dump( $user );
    }
}
else
{
    //TODO, redirect to form
}


?>
