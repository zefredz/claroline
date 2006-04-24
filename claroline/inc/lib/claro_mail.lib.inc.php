<?php // $Id$
/**
 * CLAROLINE
 *
 * @version 1.8 $Revision$
 * @copyright 2001-2006 Universite catholique de Louvain (UCL)
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @see http://www.claroline.net/wiki/index.php/Libs-mail
 * @package KERNEL
 * @author Claro Team <cvs@claroline.net>
 *
 */


require_once dirname(__FILE__) . '/class.phpmailer.php' ;
include_once dirname(__FILE__) . '/user.lib.php' ;

 /**
  * Send e-mail to Claroline users form their ID a user of Claroline
  * default from clause in email address will be the platorm admin adress
  * default from name clause in email will be the platform admin name and surname
  * @author Hugues Peeters <peeters@advalavas.be>
  * @param  int or array $userIdList - sendee id's
  * @param  string $message - mail content
  * @param  string $subject - mail subject
  * @param  string $specificFrom (optional) sender's email address
  * @param  string  $specificFromName (optional) sender's name
  * @return int total count of sent email
  */

function claro_mail_user($userIdList, $message, $subject , $specificFrom='', $specificFromName='' )
{
    if ( ! is_array($userIdList) ) $userIdList = array($userIdList);
    if ( count($userIdList) == 0)  return 0;

    $tbl      = claro_sql_get_main_tbl();
    $tbl_user = $tbl['user'];

    $sql = 'SELECT email 
            FROM `'.$tbl_user.'`
            WHERE user_id IN ('. implode(', ', $userIdList) . ')';

    $emailList = claro_sql_query_fetch_all_cols($sql);
    $emailList = $emailList['email'];

    $emailList = array_filter($emailList, 'is_well_formed_email_address');

    $mail = new PHPMailer();

    if ($specificFrom != '')     $mail->From = $specificFrom;
    else                         $mail->From = $GLOBALS['administrator_email'];

    if ($specificFromName != '') $mail->FromName = $specificFromName;

    $mail->CharSet = $GLOBALS['charset'];
    $mail->IsMail();
    $mail->Subject = $subject;
    $mail->Body    = $message;

    $emailSentCount = 0;

    foreach($emailList as $thisEmail)
    {
        $mail->AddAddress($thisEmail);
        if ( $mail->Send() ) $emailSentCount ++;
        $mail->ClearAddresses();
    }

    return $emailSentCount;
}

?>