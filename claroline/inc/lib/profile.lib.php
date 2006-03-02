<?php // $Id$
/** 
 * CLAROLINE 
 *
 * User lib contains function to manage users on the platform 
 *
 * @version 1.8 $Revision$
 *
 * @copyright 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE   
 *
 * @package USERS
 *
 * @author Claro Team <cvs@claroline.net>
 * @author Christophe Gesché <moosh@claroline.net>
 * @author Mathieu Laurent <laurent@cerdecam.be>
 *
 */

/**
 * Current logged user send a mail to ask course creator status
 *
 * @param string explanation message
 *
 * @author Mathieu Laurent <laurent@cerdecam.be>
 *
 */

function profile_send_request_course_creator_status ($explanation)
{
    global $_uid, $_user, $dateFormatLong;

    $mailToUidList = claro_get_uid_of_platform_admin();

    $requestMessage_Title = '[' . get_conf('siteName') . '][Request]' . sprintf(get_lang('CourseManagerStatusToUser'),$_user['lastName'],$_user['firstName']);

    $requestMessage_Content = claro_disp_localised_date($dateFormatLong) . "\n"
                            . sprintf(get_lang('CourseManagerStatusToUser'),$_user['lastName'],$_user['firstName']) . "\n"
                            . get_lang('User') . ': ' . $_uid . "\n"
                            . get_lang('Name') . ': ' . $_user['firstName']. ' ' . $_user['lastName'] . "\n"
                            . get_lang('Email') . ':' . $_user['mail'] . "\n"
                            . get_lang('Comment') . ': ' . nl2br($explanation) . "\n"
                            . get_lang('Link') . ': ' . get_conf('rootAdminWeb') . 'adminprofile.php?uidToEdit=' . $_uid;

    foreach ( $mailToUidList as $mailToUid )
    {
        claro_mail_user($mailToUid['idUser'], $requestMessage_Content, $requestMessage_Title, get_conf('administrator_email'), 'profile');
    }
    
    return true;
}

/**
 * Current logged user send a mail to ask course creator status
 *
 * @param string explanation message
 *
 * @author Mathieu Laurent <laurent@cerdecam.be>
 *
 */

function profile_send_request_revoquation ($explanation,$login,$password)
{
    global $_uid, $_user, $dateFormatLong;

    /**
     * @todo with new profil it would be interresting to have a profil to select who receipt this "mail"
     */
    $mailToUidList = claro_get_uid_of_platform_admin();
    $requestMessage_Title = '[' . get_conf('siteName') .'][Request]' . sprintf(get_lang('RevoquationOfUser'),$_user['lastName'],$_user['firstName']);
    $requestMessage_Content = claro_disp_localised_date($dateFormatLong) . "\n"
                            . sprintf(get_lang('RevoquationOfUser'),$_user['lastName'],$_user['firstName']) . "\n"
                            . get_lang('User') . ': ' . $_uid . "\n"
                            . get_lang('Name') . ': ' . $_user['firstName'] . ' ' . $_user['lastName'] . "\n"
                            . get_lang('Email') . ': ' . $_user['mail'] . "\n"
                            . get_lang('Login') . ': ' . $login . "\n"
                            . get_lang('Password') . ':' . $password . "\n"
                            . get_lang('Comment') . ': ' . $explanation . "\n"
                            . get_lang('Link') . ' : ' . get_conf('rootAdminWeb') . 'adminprofile.php?uidToEdit=' . $_uid . "\n";

    foreach ($mailToUidList as $mailToUid)
    {
        claro_mail_user($mailToUid['idUser'], $requestMessage_Content, $requestMessage_Title, get_conf('administrator_email'), 'profile');
    }
    return true;
}

/**
 * @return list of users wich have admin status
 * @author Christophe Gesché <Moosh@claroline.net>
 **/

function claro_get_uid_of_platform_admin()
{
    $tbl_mdb_names = claro_sql_get_main_tbl();
    $sql = 'SELECT idUser 
            FROM `' . $tbl_mdb_names['admin'] . '`';
    $adminUidList = claro_sql_query_fetch_all($sql);
    return $adminUidList;
}

?>
