<?php # $Id$

//----------------------------------------------------------------------
// CLAROLINE
//----------------------------------------------------------------------
// Copyright (c) 2001-2005 Universite catholique de Louvain (UCL)
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
  * The function tries to retrieve $tbl_user and $_uid from the global space.
  * if it exists, $_uid is the creator id
  * If a problem arises, it stores the error message in global $claro_failure_list
  *
  * @param string $firstName
  *        string $lastName
  *        int    $statut
  *        string $email
  *        string $loginName
  *        string $password
  *        string $officialCode    (optional)
  *        string $phone        (optional)
  *        string $pictureUri    (optional)
  *        string $authSource    (optional)
  *
  * @author Hugues Peeters <peeters@ipm.ucl.ac.be>,
  * @author Roan Embrechts <roan_embrechts@yahoo.com>
  *
  * @return int     new user id - if the new user creation succeeds
  *         boolean false otherwise
  *
  */

function create_new_user($firstName, $lastName, $status,
                         $email, $loginName, $password,
                         $officialCode=NULL, $phone=NULL, $pictureUri='', $authSource='claroline')
{
    global $_uid,  $userPasswordCrypted;
    global $mainDbName, $PLACEHOLDER; // i'm not that used
    
    $tbl_mdb_names = claro_sql_get_main_tbl();
    $tbl_user      = $tbl_mdb_names['user'];

    //$tbl_user doesn't seems to exist yet

    if ($_uid) $creatorId = $_uid;
    else       $creatorId = '';

     /*
      * First check wether the login/password combination already exists
      */
    $sql = 'SELECT count(user_id) account_qty
            FROM `' . $tbl_user . '`
               WHERE     userName ="' . $loginName . '"
                AND password ="' . $password . '"';  
    $account_qty = claro_sql_query_get_single_value($sql);

    if ($account_qty > 0)    return claro_failure::set_failure('login-pass already taken');

    $password = ($userPasswordCrypted ? md5($password) : $password);

    //if ($phone == '') $phone = NULL;

    $lastName      = ($lastName     == NULL ? 'NULL' : '"' . $lastName     . '"');
    $firstName      = ($firstName    == NULL ? 'NULL' : '"' . $firstName    . '"');
    $loginName      = ($loginName    == NULL ? 'NULL' : '"' . $loginName    . '"');
    $status          = ($status       == NULL ? 'NULL' : '"' . $status       . '"');
    $password      = ($password     == NULL ? 'NULL' : '"' . $password     . '"');
    $email          = ($email        == NULL ? 'NULL' : '"' . $email        . '"');
    $officialCode = ($officialCode == NULL ? 'NULL' : '"' . $officialCode . '"');
    $pictureUri      = ($pictureUri   == NULL ? 'NULL' : '"' . $pictureUri   . '"');
    $creatorId      = ($creatorId    == NULL ? 'NULL' : '"' . $creatorId    . '"');
    $authSource      = ($authSource   == NULL ? 'NULL' : '"' . $authSource   . '"');
    $phone          = ($phone        == NULL ? 'NULL' : '"' . $phone        . '"');

    $queryString = "INSERT INTO `".$tbl_user."`
                    SET 
                        nom          = '" . $lastName     . "',
                        prenom       = '" . $firstName    . "',
                        username     = '" . $loginName    . "',
                        statut       = '" . $status       . "',
                        password     = '" . $password     . "',
                        email        = '" . $email        . "',
                        officialCode = '" . $officialCode . "',
                        pictureUri   = '" . $pictureUri   . "',
                        creatorId    = '" . $creatorId    . "',
                        authSource   = '" . $authSource   . "',
                        phoneNumber  = '" . $phone . "'";

    $result = claro_sql_query($queryString);

    if ($result)
    {
        return mysql_insert_id();
    }
    else
    {
        return FALSE;
    }
}
?>