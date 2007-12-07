<?php // $Id$

/** 
 * CLAROLINE 
 *
 * User lib contains function to manage users on the platform 
 *
 * @version 1.7 $Revision$
 *
 * @copyright 2001-2005 Universite catholique de Louvain (UCL)
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
    global $_uid, $_user, $dateFormatLong, $siteName, $rootAdminWeb, $administrator_email,
           $langCourseManagerStatusToUser, $langUser, $langName, $langEmail, $langComment, $langLink ;

    $mailToUidList = claro_get_uid_of_platform_admin();

    $requestMessage_Title = '[' . $siteName . '][Request]' . sprintf($langCourseManagerStatusToUser,$_user['lastName'],$_user['firstName']);

	$requestMessage_Content = claro_disp_localised_date($dateFormatLong) . "\n"
                            . sprintf($langCourseManagerStatusToUser,$_user['lastName'],$_user['firstName']) . "\n"
                            . $langUser . ': ' . $_uid . "\n"
                            . $langName . ': ' . $_user['firstName']. ' ' . $_user['lastName'] . "\n"
                            . $langEmail . ':' . $_user['mail'] . "\n"
                            . $langComment . ': ' . nl2br($explanation) . "\n"
                            . $langLink . ': ' . $rootAdminWeb . 'adminprofile.php?uidToEdit=' . $_uid;

	foreach ( $mailToUidList as $mailToUid )
	{
		claro_mail_user($mailToUid['idUser'], $requestMessage_Content, $requestMessage_Title, $administrator_email, 'profile');
	}
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
    global $_uid, $_user, $siteName, $rootAdminWeb, $administrator_email, $dateFormatLong,
           $langRevoquationOfUser, $langUser, $langName, $langEmail, $langComment, $langLink;
           

    $mailToUidList = claro_get_uid_of_platform_admin();
	$requestMessage_Title = '[' . $siteName .'][Request]' . sprintf($langRevoquationOfUser,$_user['lastName'],$_user['firstName']);
	$requestMessage_Content = claro_disp_localised_date($dateFormatLong) . "\n"
                            . sprintf($langRevoquationOfUser,$_user['lastName'],$_user['firstName']) . "\n"
                            . $langUser . ': ' . $_uid . "\n"
                            . $langName . ': ' . $_user['firstName'] . ' ' . $_user['lastName'] . "\n"
                            . $langEmail . ': ' . $_user['mail'] . "\n"
                            . 'login de confirmation: ' . $login . "\n"
                            . 'paswd de confirmation: ' . $password . "\n"
                            . $langComment . ': ' . $explanation . "\n"
                            . $langLink . ' : ' . $rootAdminWeb . 'adminprofile.php?uidToEdit=' . $_uid . "\n";

	foreach ($mailToUidList as $mailToUid)
	{
		claro_mail_user($mailToUid['idUser'], $requestMessage_Content, $requestMessage_Title, $administrator_email, 'profile');
	}
}

/**
 * claro_get_uid_of_platform_admin()
 * 
 * @return list of users
 *
 * @author Moosh
 **/

function claro_get_uid_of_platform_admin()
{
	$tbl_mdb_names = claro_sql_get_main_tbl();
	$sql = 'SELECT idUser 
            FROM `'.$tbl_mdb_names['admin'].'`';

	$adminUidList =	claro_sql_query_fetch_all($sql);

	return $adminUidList;
}

?>
