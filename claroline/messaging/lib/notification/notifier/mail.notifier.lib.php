<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * mailnofifier class
 *
 * @version     1.9 $Revision$
 * @copyright   2001-2008 Universite catholique de Louvain (UCL)
 * @author      Claroline Team <info@claroline.net>
 * @author      Christophe Mertens <thetotof@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html
 *              GNU GENERAL PUBLIC LICENSE version 2 or later
 * @package     internal_messaging
 */

//load notfier class
require_once dirname(__FILE__) . '/../notifier.lib.php';
//load course data
require_once get_path('incRepositorySys') . '/lib/course.lib.inc.php';
// load  PHPMail class
require_once get_path('incRepositorySys') . '/lib/sendmail.lib.php';

include claro_get_conf_repository() . 'CLMSG.conf.php';

class MailNotifier implements MessagingNotifier 
{
    /**
     * notify by email the user of the reception of a message
     *
     * @param array of int: $userDataList user identificatin list
     * @param MessageToSend $message message envoy�
     * @param int $messageId identification of the message
     * 
     */
    public function notify ($userDataList,$message,$messageId)
    {
        if (!get_conf('mailNotfication',TRUE))
        {
            return;
        }
        
        // sender name and email
        $courseSender = claro_get_current_user_data('firstName') . ' ' . claro_get_current_user_data('lastName');
    
        //************************************ IS MANAGER
        $stringManager = false;
        $courseManagers =  claro_get_course_manager_id($message->getCourseCode());
        
        for ($countManager = 0; $countManager<count($courseManagers); $countManager++)
        {
            if ($message->getSender() == $courseManagers[$countManager])
            {
                $courseData = claro_get_course_data();
                
                $stringManager = get_block('Course manager of %course%(%courseCode%)',
                array('%course%' => $courseData['name'], '%courseCode%'=> $courseData['sysCode']));
            }
        }
        
        
        //---------------------- email subject
        $emailSubject = '[' . get_conf('siteName');
        
        if (!is_null($message->getCourseCode()))
        {
            $courseData = claro_get_course_data($message->getCourseCode());
            if ($courseData)
            {
                $emailSubject .= ' - ' . $courseData['officialCode'];
            }
        }
        
        $emailSubject .= '] '.$message->getSubject();

        //------------------------------subject
        $altUrl = get_lang('If you can\'t read this message go to: ') . "http://localhost/clarolineSVN/claroline/messaging/readmessage.php?messageId=" . $messageId . "&userId=1&type=received\n\n"
            . "-- "
            . claro_get_current_user_data('lastName') . " " . claro_get_current_user_data('firstName') . "\n"
            . $stringManager
            . "\n\n".get_conf('siteName') ." <" . get_conf('rootWeb') . ">\n"
            . "   " . get_lang('Administrator') . ": " . get_conf('administrator_name') . " <" . get_conf('administrator_email') . ">\n"
            . ""
            ;
        
        
        //-------------------------BODY
        $msgContent = claro_parse_user_text($message->getMessage());
        
        $emailBody = "<html><head></head><body>" . $msgContent
                    . '<br /><br />'
               // footer
                    . '-- <br />'
                    . claro_get_current_user_data('lastName') . " " . claro_get_current_user_data('firstName') . "<br />"
                    .$stringManager
                    . '<br /><br /><a href="' . get_conf('rootWeb') . '">' . get_conf('siteName') . '</a><br />'
                    . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . get_lang('Administrator')  . ': <a href="mailto:' . get_conf('administrator_email') . '">' . get_conf('administrator_name') . '</a><br />'
                    . '</body></html>'
                    ;
        //******************************** END BODY
        //******************************************

        $userData = claro_get_current_user_data();
        
        self::emailNotification($userDataList, $emailBody,$emailSubject, $userData['mail'], $userData['lastName']." ".$userData['firstName'],$altUrl);
    }
    
    /**
     * Send a mail to the user list
     *
     * @param int $userIdList list of the user
     * @param string $message body of the mail
     * @param string $subject subject of the mail
     * @param string $specificFrom email of the sender
     * @param string $specificFromName name to display
     * @param string $altBody link of the message in case of problem of read
     * 
     */
    protected static function emailNotification($userIdList, $message, $subject , $specificFrom='', $specificFromName='',$altBody='')
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
    
        $mail = new ClaroPHPMailer();
        $mail->IsHTML(true);
        
        if (!empty($altBody))
        {
            $mail->AltBody = get_lang('If you can\'t read this message go to: ').$altBody;
        }    
        
        if ($specificFrom != '')     $mail->From = $specificFrom;
        else                         $mail->From = get_conf('administrator_email');
    
        if ($specificFromName != '') $mail->FromName = $specificFromName;
        else                         $mail->FromName = get_conf('administrator_name');
    
        $mail->Sender = $mail->From;
    
        if (strlen($subject)> 78)
        {
            $message = $subject . "\n" . $message;
            $subject = substr($subject,0,73) . '...';
        }
        
        $mail->Subject = $subject;
        $mail->Body    = $message;
        
        if (get_conf('CLARO_DEBUG_MODE',false))
        {
            $message = '<p>Subject : ' . htmlspecialchars($subject) . '</p>' . "\n"
                     . '<p>Message : <pre>' . htmlspecialchars($message) . '</pre></p>' . "\n"
                     . '<p>From : ' . htmlspecialchars($mail->FromName) . ' - ' . htmlspecialchars($mail->From) . '</p>' . "\n"
                     . '<p>Dest : ' . implode(', ', $emailList) . '</p>' . "\n";
            pushClaroMessage($message,'mail');
        }
    
        foreach ($emailList as $thisEmail)
        {
            $mail->AddAddress($thisEmail);
            
            if (! $mail->Send() )
            {
                if ( get_conf('CLARO_DEBUG_MODE') )
                {
                    pushClaroMessage($mail->getError(),'error');
                }
            }
            
            $mail->ClearAddresses();
        }
    }
}
