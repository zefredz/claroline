<?php // $Id$
/**
 * CLAROLINE
 *
 * This script allows users to retrieve the password of their profile(s)
 * on the basis of their e-mail address. The password is send via email
 * to the user.
 *
 * Special case : If the password are encrypted in the database, we have
 * to generate a new one.
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

require '../inc/claro_init_global.inc.php';

$nameTools = get_lang('Lost password');

// DB tables definition
$tbl_mdb_names = claro_sql_get_main_tbl();
$tbl_user      = $tbl_mdb_names['user'];

// library for authentification and mail
include_once($includePath . '/lib/user.lib.php');
include_once($includePath . '/lib/sendmail.lib.php');

// Initialise variables

$passwordFound = FALSE;
$msg = '';

// Get the forgotten email from the form

if ( isset ($_REQUEST['Femail']) ) $Femail = strtolower(trim($_REQUEST['Femail']));
else                               $Femail = '';

// Main section

if ( isset($_REQUEST['searchPassword']) && !empty($Femail) )
{
    // search user with this email

    $sql = "SELECT  `user_id`   `uid`       ,
                    `nom`       `lastName`  ,
                    `prenom`    `firstName` ,
                    `username`  `loginName` ,
                    `password`              ,
                    `email`                 ,
                    `authSource`            ,
                    `creatorId`
             FROM `" . $tbl_user . "`
             WHERE LOWER(email) = '" . addslashes($Femail) . "'";

    $user = claro_sql_query_fetch_all($sql);

    $extAuthPasswordCount = 0;

    if ( count($user) > 0 )
    {
        for ($i = 0, $j = count($user); $i < $j; $i++)
        {
            if ( in_array(strtolower($user[$i]['authSource']),
                          array('claroline', 'clarocrypt')))
            {
                if (get_conf('userPasswordCrypted',false))
                {
                    /*
                     * If password are crypted, we can not send them as such.
                     * We have to generate new ones.
                     */

                    $user[$i]['password'] = generate_passwd();

                    // UPDATE THE DB WITH THE NEW GENERATED PASSWORD

                    $sql = 'UPDATE ' . $tbl_user . '
                            SET   `password` = "'. addslashes(md5($user[$i]['password'])) .'"
                             WHERE `user_id` = "'.$user[$i]['uid'].'"';

                    if (false === claro_sql_query($sql)) trigger_error('<p align="center">'. get_lang('Wrong operation') . '</p>', E_USER_ERROR);
                }
            }
            else
            {
                unset($user[$i]); // remove
                $extAuthPasswordCount ++;
            }
        }

        // recount if there are still password found
        if (count($user) > 0) $passwordFound = true;

        /*
         * Prepare the email message wich has to be send to the user
         */

        // mail subject
        $emailSubject = get_lang('Login request') . ' ' . get_conf('siteName');

        // mail body
        foreach($user as $thisUser)
        {
            $userAccountList[] =
                $thisUser['firstName'] .' ' . $thisUser['lastName']  . "\r\n\r\n"
                . "\t" . get_lang('Username') . ' : ' . $thisUser['loginName'] . "\r\n"
                . "\t" . get_lang('Password') . ' : ' . $thisUser['password']  . " \r\n"
                ;
        }

        if ($userAccountList)
        {
            $userAccountList = implode ("\r\n\r\n", $userAccountList);
        }

        $emailBody = $emailSubject."\r\n"
                    .get_conf('rootWeb')."\r\n"
                    .get_lang('This is your account Login-Pass')."\r\n\r\n"
                    .$userAccountList;


            // send message
            $emailTo = $user[0]['uid'];

            if( claro_mail_user($emailTo, $emailBody, $emailSubject) )
            {
                $msg = get_lang('Your password has been emailed to'). ' : ' . $Femail;
            }
            else
            {
                $msg = get_lang('The system is unable to send you an e-mail.') . '<br />'
                .   get_lang('Please contact') . ' : '
                .   '<a href="mailto:' . get_conf('administrator_email') . '?BODY=' . $Femail . '">'
                .   get_lang('Platform Administrator')
                .   '</a>';
            }
    }
    else
    {
        $msg = get_lang('There is no user account with this email address.');
    }

    if ($extAuthPasswordCount > 0 )
    {
        if ( count ($user) > 0 )
        {
            $msg .= '<p>'
                 . get_lang('Passwords of some of your user account(s) are recorded an in external authentication system outside the platform.') . '<br />'
                 . get_lang('For more information take contact with the platform administrator.')
                 .  '</p>';
        }
        else
        {
            $msg .= '<p>'
                 . get_lang('Your password(s) is (are) recorded in an external authentication system outside the platform.') . '<br />'
                 . get_lang('For more information take contact with the platform administrator.')
                 . '</p>';
        }
    }
}
else
{
    $msg = '<p>' . get_lang('Enter your email so we can send you your password.') . '</p>';
}


////////////////////////////////////////////////////
// display section

include$includePath . '/claro_init_header.inc.php';

// display title

echo claro_html_tool_title($nameTools);

// display message box

if ( ! $passwordFound )
{
    $msg .= '<form action="' . $_SERVER['PHP_SELF'] . '" method="post">'
    .       '<input type="hidden" name="searchPassword" value="1" />'
    .       '<label for="Femail">' . get_lang('Email') . ' : </label>'
    .       '<br />'
    .       '<input type="text" name="Femail" id="Femail" size="50" maxlength="100" value="' . htmlspecialchars($Femail) . '" />'
    .       '<br /><br />'
    .       '<input type="submit" name="retrieve" value="' . get_lang('Ok') . '" /> '
    .       claro_html_button('../../index.php', get_lang('Cancel'))
    .       '</form>'
    ;
}

if ( ! empty($msg) ) echo claro_html_message_box($msg);

// display form

include $includePath . '/claro_init_footer.inc.php';

?>
