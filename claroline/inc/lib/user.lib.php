<?php

function user_initialise()
{
    $data = array();
    
    $data['lastname'] = '';
    $data['firstname'] = '';
    $data['officialCode'] = '';
    $data['username'] = '';
    $data['password'] = '';
    $data['password_conf'] = '';
    $data['status'] = '';
    $data['email'] = '';
    $data['phone'] = '';
    $data['picture'] = '';
    
    return $data;
}

function user_get_data($user_id)
{
    $tbl_mdb_names = claro_sql_get_main_tbl();
    $tbl_user      = $tbl_mdb_names['user'];

    $sql = 'SELECT `nom` as `lastname` , 
    			    `prenom` as `firstname` , 
    			    `username` , 
    			    `email` , 
    			    `pictureUri` , 
    			    `officialCode` , 
    			    `phoneNumber` as `phone`,  
    			    `statut` as `status`  
            FROM  `' . $tbl_user . '`
            WHERE 
    			`user_id` = "'.(int) $user_id.'"';

    $result = claro_mysql_query($sql);

    if ( mysql_num_rows($result) )
    {
        $data = mysql_fetch_array($result);
        return $data;
    }
    else
    {
        return false;
    }
}

function user_display_form_registration( $data )
{

    global $langLastname, $langFirstname, $langOfficialCode, $langUserName, $langPassword,
           $langConfirmation, $langEmail, $langPhone, $langAction, $langRegister,
           $langRegStudent, $langRegAdmin ;

    global $allowSelfRegProf;

    // display registration form
    echo '<form action="' . $_SERVER['PHP_SELF'] . '" method="post">' . "\n"
        . '<input type="hidden" name="cmd" value="registration" />' . "\n"
        . '<input type="hidden" name="claroFormId" value="' . uniqid(rand()) . '" />' . "\n"
    
        . '<table cellpadding="3" cellspacing="0" border="0">' . "\n"
        . ' <tr>' . "\n"
        . '  <td align="right"><label for="lastname">' . $langLastname . '&nbsp;:</label></td>' . "\n"
        . '  <td><input type="text" size="40" name="lastname" id="lastname" value="' . htmlspecialchars($data['lastname']) . '" /></td>' . "\n"
        . ' </tr>' . "\n"

        . ' <tr>' . "\n"
        . '  <td align="right"><label for="firstname">' . $langFirstname . '&nbsp;:</label></td>' . "\n"
        . '  <td><input type="text" size="40" id="firstname" name="firstname" value="' . htmlspecialchars($data['firstname']) . '" /></td>' . "\n"
        . ' </tr>' . "\n" ;

    if ( defined('CONFVAL_ASK_FOR_OFFICIAL_CODE') && CONFVAL_ASK_FOR_OFFICIAL_CODE )
    {
        echo ' <tr>'  . "\n"
            . '  <td align="right"><label for="officialCode">' . $langOfficialCode . '&nbsp;:</label></td>'  . "\n"
            . '  <td><input type="text" size="40" id="offcialCode" name="officialCode" value="' . htmlspecialchars($data['officialCode']) . '" /></td>' . "\n"
            . ' </tr>' . "\n";
    }

    echo ' <tr>' . "\n"
        . '  <td>&nbsp;</td>' . "\n"
        . '  <td>&nbsp;</td>' . "\n"
        . ' </tr>' . "\n";

    echo ' <tr>' . "\n"
        . '  <td align="right"><label for="username">' . $langUserName . '&nbsp;:</label></td>' . "\n"
        . '  <td><input type="text" size="40" id="username" name="username" value="' . htmlspecialchars($data['username']) . '" /></td>' . "\n"
        . ' </tr>' . "\n"

        . ' <tr>'  . "\n"
        . '     <td align="right"><label for="password">' . $langPassword . '&nbsp;:</label></td>' . "\n"
        . '  <td><input type="password" size="40" id="password" name="password" /></td>' . "\n"
        . '    </tr>' . "\n"

        . ' <tr>' . "\n"
        . '     <td align="right"><label for="password_conf">' . $langPassword . '&nbsp;:<br>' . "\n" . "\n"
        . ' <small>(' . $langConfirmation . ')</small></label></td>' . "\n"
        . '  <td><input type="password" size="40" id="password_conf" name="password_conf" /></td>' . "\n"
        . ' </tr>' . "\n";
    
    echo ' <tr>' . "\n"
        . '  <td>&nbsp;</td>' . "\n"
        . '  <td>&nbsp;</td>' . "\n"
        . ' </tr>' . "\n";


    echo ' <tr>' . "\n"
        . '  <td align="right"><label for="email">' . $langEmail . '&nbsp;:</label></td>' . "\n"
        . '  <td><input type="text" size="40" id="email" name="email" value="' . htmlspecialchars($data['email']) . '" /></td>' . "\n"
        . ' </tr>' . "\n"

        . ' <tr>' . "\n"
        . '  <td align="right"><label for="phone">' . $langPhone . '&nbsp;:</label></td>' . "\n"
        . '  <td><input type="text" size="40" id="phone" name="phone" value="' . htmlspecialchars($data['phone']) . '" /></td>' . "\n"
        . ' </tr>' . "\n";

    // Allow registration as course manager

    if ( $allowSelfRegProf )
    {
        echo ' <tr>' . "\n"
            . '  <td align="right"><label for="status">' . $langAction . '&nbsp;:</label></td>' . "\n"
            . '  <td>' . "\n"
            . '<select id="status" name="status">'
            . '    <option value="' . STUDENT . '">' . $langRegStudent . '</option>'
            . '    <option value="' . COURSEMANAGER . '" ' . ($data['status'] == COURSEMANAGER ? 'selected="selected"' : '') . '>' . $langRegAdmin . '</option>'
            . '</select>'
            . '  </td>' . "\n"
            . ' </tr>' . "\n";
    }

    echo ' <tr>' . "\n"
        . '  <td>&nbsp;</td>' . "\n"
        . '     <td><input type="submit" value="' . $langRegister . '" /></td>' . "\n"
        . ' </tr>' . "\n"

        . '</table>' . "\n"

        . '</form>' . "\n";

}

function user_display_form_profile($data)
{


}

function user_insert ($data)
{
    global $userPasswordCrypted;

    $password = $userPasswordCrypted?md5($date['password']):$data['password'];
    
    $tbl_mdb_names = claro_sql_get_main_tbl();
    $tbl_user      = $tbl_mdb_names['user'];

    $sql = "INSERT INTO `".$tbl_user."`
            SET `nom`          = '". addslashes($data['lastname']) ."' ,
                `prenom`       = '". addslashes($data['firstname']) ."',
                `username`     = '". addslashes($data['username']) ."',
                `password`     = '". addslashes($data['password']) ."',
                `email`        = '". addslashes($data['email']) ."',
                `statut`       = '". (int) $data['status'] ."',
                `officialCode` = '". addslashes($data['officialCode']) ."',
                `phoneNumber`  = '". addslashes($data['phone']) ."'";

    return claro_sql_query_insert_id($sql);
}

function user_update ($user_id, $data)
{


}

function user_update_right_profile($user_id, $profile_id)
{


}

function user_delete ($user_id)
{



}

function user_send_registration_mail ($user_id, $data)
{
    global $langDear, $langYourReg, $langYouAreReg, $langSettings, $langPassword, $langAddress,
           $langIs, $langProblem, $langFormula, $langManager, $langEmail;

    global $siteName, $rootWeb, $administrator_name, $administrator_phone, $administrator_email;

    if ( ! empty($data['email']) )
    {
        // email subjet
        $emailSubject  = '[' . $siteName . '] ' . $langYourReg ;

        // email body
        $emailBody = $langDear . ' ' . $data['firstname'] . ' ' . $data['lastname'] . ',' . "\n"
                    . $langYouAreReg . ' ' . $siteName . ' ' . $langSettings . ' ' . $data['username'] . "\n"
                    . $langPassword . ' : ' . $data['password'] . "\n"
                    . $langAddress . ' ' . $siteName . ' ' . $langIs . ' : ' . $rootWeb . "\n"
                    . $langProblem . "\n"
                    . $langFormula . ',' . "\n"
                    . $administrator_name . "\n"
                    . $langManager . ' ' . $siteName . "\n"
                    . 'T. ' . $administrator_phone . "\n"
                    . $langEmail . ' : ' . $administrator_email . "\n";

        if ( claro_mail_user($user_id, $emailBody, $emailSubject) )
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    else
    {
        return false;
    }

}

?>
