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

    $requestMessage_Title = get_block('[%sitename][Request] Course creator status to %firstname %lastname', array('%sitename' => get_conf('siteName'),
                                                                                                                  '%firstname' => $_user['firstName'],
                                                                                                                  '%firstname' => $_user['lastName'] ) );
    $requestMessage_Content = get_block('blockRequestCourseManagerStatusMail', 
                                                 array( '%time' => claro_disp_localised_date($dateFormatLong),
                                                        '%user_id' => $_uid,
                                                        '%firstname' => $_user['firstName'],
                                                        '%lastname' => $_user['lastName'],
                                                        '%email' => $_user['mail'],
                                                        '%comment' => nl2br($explanation),
                                                        '%url' => get_conf('rootAdminWeb') . 'adminprofile.php?uidToEdit=' . $_uid 
                                                      )
                                        );

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
    
    $requestMessage_Title = get_block('[%sitename][Request] Revocation of %firstname %lastname', array('%sitename' => get_conf('siteName'),
                                                                                                       '%firstname' => $_user['firstName'],
                                                                                                       '%firstname' => $_user['lastName'] ) );
    
    $requestMessage_Content = get_block('blockRequestUserRevoquationMail', 
                                                 array( '%time' => claro_disp_localised_date($dateFormatLong),
                                                        '%user_id' => $_uid,
                                                        '%firstname' => $_user['firstName'],
                                                        '%lastname' => $_user['lastName'],
                                                        '%email' => $_user['mail'],
                                                        '%login' => $login,
                                                        '%password' => $password,
                                                        '%comment' => nl2br($explanation),
                                                        '%url' =>  get_conf('rootAdminWeb') . 'adminprofile.php?uidToEdit=' . $_uid 
                                                      )
                                        );

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
