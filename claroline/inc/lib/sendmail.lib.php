<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );
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


require_once dirname(__FILE__) . '/phpmailer/class.phpmailer.php' ;
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

    $sql = 'SELECT DISTINCT email
            FROM `'.$tbl_user.'`
            WHERE user_id IN ('. implode(', ', array_map('intval', $userIdList) ) . ')';

    $emailList = claro_sql_query_fetch_all_cols($sql);
    $emailList = $emailList['email'];

    $emailList = array_filter($emailList, 'is_well_formed_email_address');

    $mail = new PHPMailer();

    if ($specificFrom != '')     $mail->From = $specificFrom;
    else                         $mail->From = get_conf('administrator_email');

    if ($specificFromName != '') $mail->FromName = $specificFromName;
    else                         $mail->FromName = get_conf('administrator_name');

    $mail->CharSet = $GLOBALS['charset'];
    $mail->IsMail();
    if (strlen($subject)> 78)
    {
        $message = $subject . "\n" . $message;
        $subject = substr($subject,0,73) . '...';
    }
    $mail->Subject = $subject;
    $mail->Body    = $message;
    $emailSentCount = 0;

    if (get_conf('CLARO_DEBUG_MODE',false))
    {
        $message = '<p>Subject : ' . htmlspecialchars($subject) . '</p>' . "\n"
                 . '<p>Message : <pre>' . htmlspecialchars($message) . '</pre></p>' . "\n"                 
                 . '<p>From : ' . htmlspecialchars($mail->FromName) . ' - ' . htmlspecialchars($mail->From) . '</p>' . "\n"
                 . '<p>Dest : ' . implode(', ', $emailList) . '</p>' . "\n";
        pushClaroMessage($message,'mail');
    }

    foreach($emailList as $thisEmail)
    {
        $mail->AddAddress($thisEmail);
        if ( $mail->Send() ) $emailSentCount ++;
        $mail->ClearAddresses();
    }

    return $emailSentCount;
}

?>