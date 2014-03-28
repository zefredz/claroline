<?php // $Id$

/**
 * CLAROLINE
 *
 * @version     Claroline 1.12 $Revision$
 * @copyright   (c) 2001-2014, Universite catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @see         http://www.claroline.net/wiki/index.php/Libs-mail
 * @package     kernel.utils
 * @author      Claro Team <cvs@claroline.net>
 *
 */

require_once __DIR__ . '/thirdparty/phpmailer/class.phpmailer.php' ;
include_once __DIR__ . '/user.lib.php' ;

/**
 * Claroline mailing system
 */
class ClaroPHPMailer extends PHPMailer
{
    /**
     * INitialize the mailer
     */
    public function __construct()
    {
    	//prevent phpMailer from echo'ing anything
        parent::__construct(true);
        // set charset
        $this->CharSet = get_locale('charset');        

        if ( get_conf('smtp_host') != '' )
        {
            // set smtp mode and smtp host
            $this->IsSMTP();
            $this->Host = get_conf('smtp_host');

            if ( get_conf('smtp_username') != '' )
            {
                // SMTP authentification
                $this->SMTPAuth = true;     // turn on SMTP
                $this->Username = get_conf('smtp_username'); // SMTP username
                $this->Password = get_conf('smtp_password'); // SMTP password
            }
        	if ( get_conf('smtp_port') != '' )
            {              
                $this->Port = (int)get_conf('smtp_port');
            }
        	if ( get_conf('smtp_secure') != '' )
            {              
                $this->SMTPSecure = get_conf('smtp_secure');
            }
            
        }
        else
        {
            // set sendmail mode
            $this->IsMail();
        }
        
    }

    /**
     * Returns a message in the appropriate language.
     *
     * @access private
     * @return string
     */
    public function Lang($key) {
            return "Language string failed to load: " . $key . " ";
    }
    
    /**
     * Get error
     * @return string
     */
    public function getError ()
    {
        return $this->ErrorInfo;
    }    
    
    /**
     * Send the message
     * @return boolean
     */
    public function Send()
    {
        // errors can be retrieved when return value is false by calling getError method
        try
        {
        	return parent::Send();
        }
        catch (phpmailerException $e)
        {
        	return false;
        }
    }
}

/**
 * Helper to send e-mail
 * 
 * @param string $subject
 * @param string $message
 * @param string $to destination email address
 * @param string $toName destination name
 * @param string $from sender email address (default: platform contact address)
 * @param string $fromName sender name (default: platform contact name)
 * @return boolean
 */
function claro_mail($subject, $message, $to, $toName, $from, $fromName)
{
    $mail = new ClaroPHPMailer();

    if (empty($from))
    {
        $from = get_conf('administrator_email');
        if (empty($fromName))
        {
            $fromName = get_conf('administrator_name');
        }
    }
    
    $mail->Subject  = $subject;
    $mail->Body     = $message;
    $mail->From     = $from;
    $mail->FromName = $fromName;
    $mail->Sender   = $from;
    
    $mail->AddAddress($to,$toName);

    if ( $mail->Send() )
    {
        return true;
    }
    else
    {
        return claro_failure::set_failure($mail->getError());
    }
}

 /**
  * Send e-mail to Claroline users form their user ID in Claroline
  *
  * @author Hugues Peeters <peeters@advalavas.be>
  * @param  int or array $userIdList - sender id's
  * @param  string $message - mail content
  * @param  string $subject - mail subject
  * @param  string $specificFrom (optional) sender's email address (default: platform contact address)
  * @param  string  $specificFromName (optional) sender's name (default: platform contact name)
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

    $mail = new ClaroPHPMailer();

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
    $emailSentCount = 0;

    if ( claro_debug_mode() )
    {
        $message = '<p>Subject : ' . claro_htmlspecialchars($subject) . '</p>' . "\n"
                 . '<p>Message : <pre>' . claro_htmlspecialchars($message) . '</pre></p>' . "\n"
                 . '<p>From : ' . claro_htmlspecialchars($mail->FromName) . ' - ' . claro_htmlspecialchars($mail->From) . '</p>' . "\n"
                 . '<p>Dest : ' . implode(', ', $emailList) . '</p>' . "\n";
        pushClaroMessage($message,'mail');
    }

    foreach ($emailList as $thisEmail)
    {
        $mail->AddAddress($thisEmail);
        if ( $mail->Send() )
        {
            $emailSentCount ++;
        }
        else
        {
            if ( claro_debug_mode() )
            {
                pushClaroMessage($mail->getError(),'error');
            }
        }
        $mail->ClearAddresses();
    }

    return $emailSentCount;
}
