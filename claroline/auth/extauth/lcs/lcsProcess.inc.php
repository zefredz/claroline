<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );

/**
 * CLAROLINE
 *
 * @version 1.8
 *
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @package CLAUTH
 *
 * @author  Olivier Lecluse (olivier.lecluse@crdp.ac-caen.fr) 22/05/07
 */

/**
 LCS 
 */

if ($lcs_idpers != 0) 
{
    // We are authenticated on lcs as $lcs_login

    $claro_loginRequested = true;

    // lookup the user in the Claroline database

    $sql = 'SELECT user_id, username, authSource
            FROM `' . $tbl_user . '`
            WHERE '
            . ( get_conf('claro_authUsernameCaseSensitive',true) ? 'BINARY' : '')
            . ' username = "'. addslashes($lcs_login) .'"'
            ;

    $result = claro_sql_query($sql);

	if ( mysql_num_rows($result) > 0 )
	{
    	// the user is in mysql database
	    // we open session
    	while ( ( $uData = mysql_fetch_array($result) ) && ! $claro_loginSucceeded )
	    {
		    $_uid=$uData['user_id'];
    		if ( $_uid > 0 )
            {
                $uidReset             = true;
                $claro_loginSucceeded = true;
            }
	    } // end while
	}
    else
    {
	    // the user is not in Claroline database
    	// we have to create him	
	    if ( people_get_group($lcs_login)=="Profs" )
        {
       		$icc=1;
	    }
        else
        {
       	    $icc=0;
	    }

    	$lcs_user=people_get_variables($lcs_login, false);

	    $userAttrList = array('lastname'     => $lcs_user[0]["nom"],
    				'firstname'    => getprenom($lcs_user[0]["fullname"],$lcs_user[0]["nom"]),
	    			'loginName'    => $lcs_login,
		    		'email'        => $lcs_user[0]["email"],
			    	'officialCode' => NULL,
				    'phoneNumber'  => $lcs_user["tel"],
    				'isCourseCreator' => $icc,
	    			'authSource'   => 'lcs');

    	$userTbl = claro_sql_get_main_tbl();

	    $dbFieldToClaroMap = array('nom'          => 'lastname',
		    			'prenom'       => 'firstname',
			    		'username'     => 'loginName',
				    	'email'        => 'email',
					    'officialCode' => 'officialCode',
    					'phoneNumber'  => 'phoneNumber',
	    				'isCourseCreator' => 'isCourseCreator',
		    			'authSource'   => 'authSource');
    	$sqlPrepareList = array();
	
	    foreach($dbFieldToClaroMap as $dbFieldName => $claroAttribName)
	    {
		    if ( ! is_null($userAttrList[$claroAttribName]) )
		    {
			    $sqlPrepareList[] = $dbFieldName. ' = "'.addslashes($userAttrList[$claroAttribName]).'"';
		    }
	    } // foreach

       	$sql = ($uid ? 'UPDATE' : 'INSERT INTO') . " `".$userTbl['user']."` "
       	    	."SET ".implode(', ', $sqlPrepareList)
       		    .($uid ? 'WHERE user_id = '.(int)$uid : '');
    	$res  = mysql_query($sql)
               	or die('<center>UPDATE QUERY FAILED LINE '.__LINE__.'<center>');

	    $_uid=mysql_insert_id();

    	if ( $_uid > 0 )
        {
            $uidReset             = true;
            $claro_loginSucceeded = true;
        }
    } // end if user exists in database
}
else
{
    // We are not authenticated on lcs
    $_uid                 = null;
    $claro_loginSucceeded = false;
} // end if lcs auth

?>
