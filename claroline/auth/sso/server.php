<?php # -$Id$

/*
 * SOAP server available for Single Sign On (SSO) process.
 * 
 * Once a user logs to the Claroline platform a cookie is sent to the 
 * user browser if the authentication process succeeds. The cookie value 
 * is also stored in a internal table of the Claroline platform for a certain 
 * time.
 *
 * The function of this script is providing a way to retrieve the user 
 * parameter from another server on the internet on the base of this 
 * cookie value.
 *
 */


$langFile = 'trad4all';
require '../../inc/claro_init_global.inc.php';


// --> SOAP

require_once $includePath.'/inc/nusoap.php';

$server = new soap_server();

$server->register('get_user_info_from_cookie', array('cookie'=>'xsd:string', 'auth'=>'xsd:string'));
$server->service($HTTP_RAW_POST_DATA);

/**
 * get user parameter on the base of a cookie value
 *
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param  string $cookie
 * @return array   user parameters if it suceeds
 *         boolean false otherwise
 */

function get_user_info_from_cookie($cookie, $auth)
{
    if (! is_allowed_to_recieve_user_info($auth) )
    {
        return null;
    }

    $tbl_user            = 'claroline.user';
    $tbl_sso             = 'claroline.sso';
    $ssoCookieName       = 'icampusSsoCookie';
    $ssoCookieExpireTime = time()+3600;
    $ssoCookieDomain     = '.ucl.ac.be';

    $sql = "SELECT user.nom          lastname, 
                   user.prenom       firstname, 
                   user.username     loginName, 
                   user.email        email, 
                   user.officialCode officialCode,
                   user.user_id      userId

            FROM ".$tbl_sso."  AS sso,
                 ".$tbl_user." AS user
            WHERE cookie = '".$cookie."'
              AND user.user_id = sso.user_id";

    $result = claro_sql_query_fetch_all($sql);

    if (count($result) > 0)
    {
        $newSsoCookieValue = generate_cookie();
        record_sso_cookie( $result[0]['userId'], $newSsoCookieValue );
        $result[0]['ssoCookieName'      ] = $ssoCookieName;
        $result[0]['ssoCookieValue'     ] = $newSsoCookieValue;
        $result[0]['ssoCookieExpireTime'] = $ssoCookieExpireTime;
        $result[0]['ssoCookieDomain'    ] = $ssoCookieDomain;

        if ( isset($result[0]['uid']) )
        {
        	unset($result[0]['uid']);
        }
        

        return $result[0];
    }
    else
    {
    	return null;
    }
}


/**
 * generate a crypted aleatoric cookie value
 *
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @return string
 */


function generate_cookie()
{
	return md5(mktime());
}

/**
 * records the cookie value of specific user during authentication
 *
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param int    $userId
 * @param string $cookie
 */


function record_sso_cookie($userId, $ssoCookie)
{
    $tbl_sso = 'claroline.sso';

    $sql = "UPDATE ".$tbl_sso." 
            SET cookie    = '".$ssoCookie."',
                rec_time  = NOW()
            WHERE user_id = ". (int) $userId;

    $affectedRowCount = claro_sql_query_affected_rows($sql);

    if ($affectedRowCount < 1)
    {
        $sql = "INSERT INTO ".$tbl_sso." 
                SET cookie    = '".$ssoCookie."',
                    rec_time  = NOW(),
                    user_id   = ". (int) $userId;

        claro_sql_query($sql);
    }
}


/**
 * check if the soap client is allowed to recieve the user information 
 * recorded into the system
 *
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param string $auth
 * @return boolean true if is allowed, false otherwise
 */


function is_allowed_to_recieve_user_info($auth)
{
	return true;
}


// var_dump(get_user_info_from_cookie('ABCD'));
?>