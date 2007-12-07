<?php // $Id$
/** 
 * CLAROLINE 
 *
 * Build the frameset for chat.
 *
 * @version 1.7 $Revision$
 *
 * @copyright 2001-2005 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE 
 *
 * @see http://www.claroline.net/wiki/index.php/Libs-mail
 *
 * @package KERNEL
 *
 * @author Claro Team <cvs@claroline.net>
 *
 * @todo use better claro_failure
 * @todo finish function ClaroMailUsers($users_ids, $message, $subject ,$specificFrom="")
 * @todo finish function ClaroMailAll($message, $subject ,$specificFrom="")
 * @todo finish function ClaroMailCourse($course_id, $message, $subject ,$specificFrom="")
 */


require_once dirname(__FILE__) . '/class.phpmailer.php' ;
include_once dirname(__FILE__) . '/auth.lib.inc.php' ;

  //needed to see if email is valid to try sending the notification

    /**
    * Send many e-mail to many users
    * $mails must be an array with 
    * one line by mailbody to send
    ** [to] the id of the user (of array of id) that must receive the mail
    ** [cc] the id of the user (of array of id) that must receive the mail by cc
    ** [bcc] the id of the user (of array of id) that must receive the mail by bcc
    ** [body]
    ** [subject]
    ** [from]
     ** [headers]
    * to be implemented !!!  
    * @param array $mails to send
    */

function claro_mail_spool($mails) 
{
    global $administrator_email, $administrator_name;

    $tbl = claro_sql_get_main_tbl();
    $tbl_user = $tbl['user'];

    foreach ($mails as $mailToSend)
    {
        $specificFrom = trim( $mailToSend['from'] == '' ? $administrator_email : $mailToSend['from']);
     //find user email in claro db
       $sql = '    SELECT * 
                       FROM `'.$tbl_user.'` as `claro_user`
                    WHERE 0 ';
        if (isset($mailToSend['to']))
        $sql.=    (is_array($mailToSend['to']) 
                ? ' OR `claro_user`.`user_id` IN ("'.implode($mailToSend['to'],'","').'") '
                : ' OR `claro_user`.`user_id` = "'. (int)$mailToSend['to'] .'" ');
        
        if (isset($mailToSend['cc']))
        $sql.=    (is_array($mailToSend['cc']) 
                ? ' OR `claro_user`.`user_id` IN ("'.implode($mailToSend['cc'],'","').'") '
                : ' OR `claro_user`.`user_id` = "'. (int)$mailToSend['cc'].'" ')        ;
        
        if (isset($mailToSend['bcc']))
        $sql.=    (
                is_array($mailToSend['bcc']) 
                ? ' OR `claro_user`.`user_id` IN ("'.implode($mailToSend['bcc'],'","').'") '
                : ' OR `claro_user`.`user_id` = "'. (int)$mailToSend['bcc'].'" '
                );
       $result = claro_sql_query($sql);
       if (mysql_num_rows($result))
       {
            $listDest = claro_sql_fetch_all($result);
       }
       else
       {
            return claro_failure::set_failure('N0_USER_WITH_SUCH_AN_ID');
       }
    
       // create mailer and configure it.

       $mail = new PHPMailer();

       if ($specificFrom != '')   //takes from email address if given in parameters
       {
            $mail->From = $specificFrom;
       }
       else
       {
            $mail->From = $administrator_email;
       }

       if ($specificFromName != '') //takes from name if given in parameters
       {
            $mail->FromName = $specificFromName;
       }
       else
       {
            $mail->FromName = $administrator_name;
       }

       $mail->CharSet = $GLOBALS['charset'];     
       $mail->IsMail();
       $mail->Subject = $mailToSend['subject'];
       $mail->Body    = $mailToSend['body'];
    
       //$mail->AltBody = $message; //let's suppose first that people who receive mails can receive html mails...
        foreach ($listDest as $list)
        {
            if (    
                    isset($mailToSend['to']) &&
                    (
                    (    !is_array($mailToSend['to']) && $mailToSend['to'] == $list['user_id']) 
                    || 
                    in_array($list['user_id'],$mailToSend['to'])
                ) )
            {
                $mail->AddAddress($list['email'], $list['nom'] . ' ' . $list['prenom']);
            }

            if (    
                    isset($mailToSend['cc']) &&
                    (
                    (    !is_array($mailToSend['cc'])
                        &&
                        $mailToSend['cc']== $list['user_id']
                    )
                    ||     
                    in_array($list['user_id'],$mailToSend['cc']))
                )
            {
                   $mail->AddCC($list['email'], $list['nom'] . ' ' . $list['prenom']);
            }

            if (        
                        isset($mailToSend['bcc']) &&
                        ((    !is_array($mailToSend['bcc']) 
                        && 
                        $mailToSend['bcc'] == $list['user_id'] 
                    )
                    || 
                    in_array($list['user_id'],$mailToSend['bcc']))
                )
            {
                   $mail->AddBCC($list['email'], $list['nom'] . ' ' . $list['prenom']);
            }
        }    
       //send mail
    
       if (!$mail->Send())
       {
            return claro_failure::set_failure ('There has been a mail error sending subject was "' .$mailToSend['subject'] . '"');
       }

       // Clear all addresses and attachments for next use

       $mail->ClearAddresses();
       return 1;
     }
 }
 
 /*
  * Send an e-mail to a user of Claroline
  * default from clause in email address will be the platorm admin adress
  * default from name clause in email will be the platform admin name and surname
  * return boolean 0 if sending mail failed, 1 if it succeeded.
  */

function claro_mail_user($user_id, $message, $subject ,$specificFrom="", $specificFromName="" ) 
{

    global $administrator_name, $administrator_email, $regexp;

    $tbl = claro_sql_get_main_tbl();
    $tbl_user = $tbl['user'];

    //find user email in claro db

    $sql = 'SELECT * FROM `'.$tbl_user.'` as `claro_user`
            WHERE `claro_user`.`user_id` = "'. (int)$user_id . '"';

    $result = mysql_query($sql);

    if (mysql_num_rows($result))
    {
        $list = mysql_fetch_array($result);
    }
    else
    {
        return claro_failure::set_failure('No user with such an ID !!!');
    }
        
    if(! is_well_formed_email_address($list['email']) or empty($list['email']) )
    {
        return claro_failure::set_failure( $list['nom'] . ' ' . $list['prenom'] . ' : wrong or empty email address');
    }
        
    // create mailer and configure it.
        
    $mail = new PHPMailer();
        
    if ($specificFrom != '')   //takes from email address if given in parameters
    {
        $mail->From = $specificFrom;
    }
    else
    {
        // by default the mail is sent by the administrator
        $mail->From = $administrator_email;
    }
        
    if ($specificFromName!="") //takes from name if given in parameters
    {
        $mail->FromName = $specificFromName;
    }
    else
    {
        $mail->FromName = $administrator_name;
    }
        
    $mail->CharSet = $GLOBALS['charset'];     
    $mail->IsMail();
    $mail->Subject = $subject;
    $mail->Body    = $message;
        
    //$mail->AltBody = $message; //let's suppose first that people who receive mails can receive html mails...
        
    $mail->AddAddress($list['email'], $list['nom']." ".$list['prenom']);
        
    //send mail
        
    if (!$mail->Send())
    {
        return claro_failure::set_failure($list['nom'] . " " . $list['prenom'] . " : there has been a mail error sending to " . $list['email']);
    }
    // Clear all addresses and attachments for next use
        
    $mail->ClearAddresses();
    $mail->ClearAttachments();
    return 1;
}

  /**
   *  Send an e-mail to all the users of a course.
   *  to be implemented !!!
   */

   function ClaroMailCourse($course_id, $message, $subject ,$specificFrom="")
   {

   }

  /**
  *  Send an e-mail to all the users of the plateform
  *  to be implemented !!!
  *
  */
   function ClaroMailAll($message, $subject ,$specificFrom="")
   {

   }

   /**
    * Send an e-mail to a specific group of users
    * $users_ids must be an array with the id of the users that must receive the mail
    * to be implemented !!!  
    *
    */

    function ClaroMailUsers($users_ids, $message, $subject ,$specificFrom="")
    {

    }
?>
