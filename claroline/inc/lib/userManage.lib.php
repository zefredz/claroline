<?php # $Id$

//----------------------------------------------------------------------
// CLAROLINE
//----------------------------------------------------------------------
// Copyright (c) 2001-2003 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see 'credits' file
//----------------------------------------------------------------------

/*
==========================================================
	This library provides functions for user management.
==========================================================
*/

/**
  * Creates a new user for the platform
  * @author Hugues Peeters <peeters@ipm.ucl.ac.be>,
  * 		Roan Embrechts <roan_embrechts@yahoo.com>
  *
  * @param string $firstName
  *        string $lastName
  *        int    $statut
  *        string $email
  *        string $loginName
  *        string $password
  *        string $officialCode	(optional)
  *        string $phone		(optional)
  *        string $pictureUri	(optional)
  *        string $authSource	(optional)
  *
  * @return int     new user id - if the new user creation succeeds
  *         boolean false otherwise
  *
  * @desc The function tries to retrieve $tbl_user and $_uid from the global space.
  *       if it exists, $_uid is the creator id
  *       If a problem arises, it stores the error message in global $claro_failure_list
  */

function create_new_user($firstName, $lastName, $status,
						 $email, $loginName, $password,
						 $officialCode=NULL, $phone=NULL, $pictureUri='', $authSource='claroline')
{
	global $tbl_user, $_uid, $mainDbName, $userPasswordCrypted, $PLACEHOLDER;
	$TABLEUSER = $mainDbName."`.`user";

	//$tbl_user doesn't seems to exist yet

	if ($_uid) $creatorId = $_uid;
	else       $creatorId = '';

     /*
      * First check wether the login/password combination already exists
      */
	$result = mysql_query("SELECT * FROM `".$mainDbName."`.`user`
                                WHERE userName =\"$loginName\"
                                AND password =\"$password\"");

	if (mysql_num_rows($result) > 0)    return claro_set_failure('login-pass already taken');

	$password = ($userPasswordCrypted?md5($password):$password);

	//if ($phone == '') $phone = NULL;

	$lastName		=($lastName==NULL		?"NULL":"\"".$lastName."\""		);
	$firstName		=($firstName==NULL		?"NULL":"\"".$firstName."\""	);
	$loginName		=($loginName==NULL		?"NULL":"\"".$loginName."\""	);
	$status			=($status==NULL			?"NULL":"\"".$status."\""		);
	$password		=($password==NULL		?"NULL":"\"".$password."\""		);
	$email			=($email==NULL			?"NULL":"\"".$email."\""		);
	$officialCode	=($officialCode==NULL	?"NULL":"\"".$officialCode."\""	);
	$pictureUri		=($pictureUri==NULL		?"NULL":"\"".$pictureUri."\""	);
	$creatorId		=($creatorId==NULL		?"NULL":"\"".$creatorId."\""	);
	$authSource		=($authSource==NULL		?"NULL":"\"".$authSource."\""	);
	$phone			=($phone==NULL			?"NULL":"\"".$phone."\""		);

	$queryString = "INSERT INTO `".$mainDbName."`.`user`
	                SET nom = ".$lastName.",
	                prenom = ".$firstName.",
	                username = ".$loginName.",
	                statut = ".$status.",
	                password = ".$password.",
	                email = ".$email.",
	                officialCode = ".$officialCode.",
	                pictureUri 	= ".$pictureUri.",
	                creatorId  	= ".$creatorId.",
	                authSource = ".$authSource.",
					phoneNumber = ".$phone;

	$result = mysql_query($queryString);

	if ($result)
	{
		return mysql_insert_id();
	}
	else
	{
		return false;
	}
}


/**
  * Check if informations of a user are correct
  * @author Muret Benoît <muret_ben@hotmail.com>
  *
  * @param string $firstname
  *        string $lastname
  *        int    $status
  *        string $email
  *        string $username
  *        string $password
  *        string $officialCode
  *        string $phone
  *        string $pictureUri
  *        string $authSource
  *
  * @return array     the array content the errors of informations of a user
  *
  * @desc The function check if the are errors in the informations of a user and return a array of this errors
  */

function InfoOk($firstname, $lastname, $status,$email, $username, $password, $officialCode, $phone, $pictureUri, $authSource)
{

	if(empty($lastname) || empty($firstname) || empty($password) || empty($username) || empty($email))
	{
		$array_Error["empty"]="empty";
	}
	// valid mail address checking

	elseif(!eregi('^[0-9a-z_.-]+@([0-9a-z-]+\.)+([0-9a-z]){2,4}$',$email))
	{
		$array_Error["email"]="email";
	}
	elseif($status!=1 && $status!=5)
	{
		$array_Error["status"]="satus";
	}

	return $array_Error;
}



/**
  * Send a mail to a user
  * @author Muret Benoît <muret_ben@hotmail.com>
  *
  * @param string $lastname
  *        string $firstname
  *        string $username
  *        string $password
  *        string $email
  *
  * @return nothing
  *
  * @desc The function send a mail to a user to confirm his incsibe
  */
function sendMail($lastname,$firstname,$username,$password,$email)
{
	global $administratorEmail,$siteName,$lang_addUser_YourReg,$administratorSurname,$administratorName,$langDear,$langYouAreReg,
			$langSettings,$langPass,$langAddress,$langIs,$serverAddress,$langProblem,$langFormula,$langManager,$telephone,$langEmail,
			$emailAdministrator,$emailbody;

	$emailto       = "$lastname $firstname <$email>";
	$emailfromaddr = $administratorEmail;
	$emailfromname = $siteName;
	$emailsubject  = "$lang_addUser_YourReg $siteName";

	$emailheaders  = "From: ".$administratorSurname." ".$administratorName." <".$administratorEmail.">\n";
	$emailheaders .= "Reply-To: ".$administratorEmail."\n";
	$emailheaders .= "X-Mailer: PHP/" . phpversion() . "\n";
	$emailheaders .= "X-Sender-IP: $REMOTE_ADDR"; // (small security precaution...)


	$emailbody = "$langDear  $firstname $lastname,\n $langYouAreReg $siteName $langSettings $username\n$langPass: $password \n$langAddress $siteName $langIs: $serverAddress\n$langProblem\n$langFormula,\n$administratorSurname $administratorName\n$langManager $siteName\nT. $telephone\n$langEmail: $emailAdministrator\n";

	@mail($emailto, $emailsubject, $emailbody, $emailheaders);
}

?>