<?
require("class.phpmailer.php");

  //needed to see if email is valid to try sending the notification
 $regexp = "^[0-9a-z_\.-]+@(([0-9]{1,3}\.){3}[0-9]{1,3}|([0-9a-z][0-9a-z-]*[0-9a-z]\.)+[a-z]{2,3})$";


 /*
  * function to send an e-mail to a user of Claroline
  * default from clause in email address will be the platorm admin adress
  * default from name clause in email will be the platform admin name and surname
  * return 0 if sending mail failed, 1 if it succeeded.
  */

 function claro_mail_user($user_id, $message, $subject ,$specificFrom="", $specificFromName="" ) {

   include('../inc/conf/claro_main.conf.php');

   global $From;
   global $administrator;

   if ($specificFrom=="") {$specificFrom = $From;}
   $TABLEUSER = $mainDbName.".user";

     //find user email in claro db

   $sql = "SELECT * FROM $TABLEUSER as claro_user
                      WHERE claro_user.user_id = ".$user_id."
                      ";
   $result = mysql_query($sql);
   if (mysql_num_rows($result))
   {
        $list = mysql_fetch_array($result);
   }
   else
   {
        echo "No user with such an ID !!!";
        return 0;
   }

   // create mailer and configure it.

   $mail = new PHPMailer();

   if ($specificFrom!="")   //takes from email address if given in parameters
   {
        $mail->From = $specificFrom;
   }
   else
   {
        $mail->From = $From;
   }

   if ($specificFromName!="") //takes from name if given in parameters
   {
        $mail->FromName = $specificFromName;
   }
   else
   {
        $mail->FromName = $administrator["name"];
   }

   $mail->IsMail();
   $mail->Subject = $subject;
   $mail->Body    = $message;

   //$mail->AltBody = $message; //let's suppose first that people who receive mails can receive html mails...

   $mail->AddAddress($list['email'], $list['nom']." ".$list['prenom']);

   //send mail

   if (!$mail->Send())
   {
        echo "There has been a mail error sending to " .$usermail."<br>";
        return 0;
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
