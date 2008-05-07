<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * send message
 *
 * @version     1.9 $Revision$
 * @copyright   2001-2008 Universite catholique de Louvain (UCL)
 * @author      Claroline Team <info@claroline.net>
 * @author      Christophe Mertens <thetotof@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html
 *              GNU GENERAL PUBLIC LICENSE version 2 or later
 * @package     internal_messaging
 */


    // initializtion
    require_once dirname(__FILE__) . '/../../claroline/inc/claro_init_global.inc.php';
    
    // move to kernel
    $claroline = Claroline::getInstance();
    
    // ------------- Business Logic ---------------------------
    if ( ! claro_is_user_authenticated() )
    {
        claro_disp_auth_form(true);
    }
    
    include claro_get_conf_repository() . 'CLMSG.conf.php';
    require_once dirname(__FILE__).'/lib/message/messagetosend.lib.php';
    require_once dirname(__FILE__).'/lib/recipient/singleuserrecipient.lib.php';
    require_once dirname(__FILE__).'/lib/recipient/courserecipient.lib.php';
    require_once dirname(__FILE__).'/lib/recipient/grouprecipient.lib.php';
    require_once dirname(__FILE__).'/lib/recipient/allusersrecipient.lib.php';
    require_once dirname(__FILE__).'/lib/permission.lib.php';
    
    $acceptedCmdList = array('rqMessageToUser','rqMessageToCourse','rqMessageToAllUsers','rqMessageToGroup', 'exSendMessage');
    
    $addForm = FALSE;
    $content = "";

    if (isset($_REQUEST['cmd']) && in_array($_REQUEST['cmd'], $acceptedCmdList) )
    {
        if (isset($_REQUEST['subject']))
        {
            $subject = $_REQUEST['subject'];
        }
        else
        {
            $subject = "";
        }
        
        if (isset($_REQUEST['message']))
        {
            $message = $_REQUEST['message'];
        }
        else
        {
            $message = "";
        }
        
        if ($_REQUEST['cmd'] == 'rqMessageToUser' && isset($_REQUEST['userId']))
        {
            if (!current_user_is_allowed_to_send_message_to_user($_REQUEST['userId']))
            {
                claro_die("Not Allowed");
            }
            $typeRecipient = 'user';
            $userRecipient = $_REQUEST['userId'];
            $groupRecipient = '';
            $courseRecipient = '';
            
            $addForm = TRUE;
        }
        
        if ($_REQUEST['cmd'] == 'rqMessageToCourse')
        {
            if (!claro_is_in_a_course())
            {
                claro_die(get_lang('You are not in a course'));
            }
            if (!current_user_is_allowed_to_send_message_to_current_course())
            {
                claro_die(get_lang('Not allowed'));
            }
            
            $typeRecipient = 'course';
            $userRecipient = '';
            $groupRecipient = '';
            $courseRecipient = claro_get_current_course_id();
            
            $addForm = TRUE;
        }
        
        if ($_REQUEST['cmd'] == 'rqMessageToAllUsers')
        {
            if (!claro_is_platform_admin())
            {
                claro_die(get_lang('Not allowed'));
            }
            $typeRecipient = 'all';
            $userRecipient = '';
            $groupRecipient = '';
            $courseRecipient = '';
            
            $addForm = TRUE;
        }
        
        if ($_REQUEST['cmd'] ==  'rqMessageToGroup')
        {
            if (!claro_is_in_a_group())
            {
                claro_die(get_lang('You must be in a group to send a message to a group'));
            }
            
            $typeRecipient = 'group';
            $userRecipient = '';
            $groupRecipient = claro_get_current_group_id();
            $courseRecipient = claro_get_current_course_id();
            
            $addForm = TRUE;
        }
        
        if ($_REQUEST['cmd'] == 'exSendMessage')
        {
            if (!isset($_POST['message'])
                    || !isset($_POST['subject']))
            {
                 header('Location:./index.php');
            }
            else
            {
                
                
                $message = trim($_POST['message']);
                $subject = trim($_POST['subject']);
                
                
                //test subject is fillin
                if ($subject == "")
                {
                    $dialogBox = new DialogBox();
                    $dialogBox->error(get_lang("Subject couldn't be empty"));
                    $content .= $dialogBox->render();
                    $addForm = TRUE;
                }
                else
                {
                    $message = new MessageToSend(claro_get_current_user_id(),$subject,$message);
                    if ($_REQUEST['typeRecipient'] == "user")
                    {
                        $recipient = new SingleUserRecipient($_POST['userRecipient']);
                        
                        if (claro_is_in_a_course())
                        {
                            $message->setCourse(claro_get_current_course_id());
                        }
                        
                        if (claro_is_in_a_group())
                        {
                            $message->setCourse(claro_get_current_group_id());
                        }
                        
                    }
                    elseif ( $_REQUEST['typeRecipient'] == "course" )
                    {
                        $recipient = new CourseRecipient($_POST['courseRecipient']);
                        $message->setCourse($_POST['courseRecipient']);
                    }
                    elseif ($_REQUEST['typeRecipient'] == "all" )
                    {
                        $recipient = new AllUsersRecipient();
                    }
                    elseif ($_REQUEST['typeRecipient'] == 'group')
                    {
                        $recipient = new GroupRecipient($_POST['groupRecipient'],$_POST['courseRecipient']);
                        $message->setCourse($_POST['courseRecipient']);
                        $message->setGroup($_POST['groupRecipient']);
                    }
                    
                    $recipient->sendMessage($message);
                    
                    $dialogbox = new DialogBox();
                    $dialogbox->info(get_lang('Message sent'));
                    $content .= $dialogbox->render();
                }
            }
        }
    }
	
    // ------------ Prepare display --------------------
    if ($addForm)
    {
        $content .= "<br/>";
        
        $content .= '<form method="post" action="sendmessage.php?cmd=exSendMessage'.claro_url_relay_context('&amp').'">';
        $content .= '<input type="hidden" name="claroFormId" value="' . uniqid('') . '" />';
        $content .= claro_form_relay_context();
        $content .= '<input type="hidden" name="cmd" value="exSendMessage" />';
        $content .= '<input type="hidden" name="typeRecipient" value="'.$typeRecipient.'" />';
        $content .= '<input type="hidden" name="userRecipient" value="'.$userRecipient.'" />';
        $content .= '<input type="hidden" name="courseRecipient" value="'.$courseRecipient.'" />';
        $content .= '<input type="hidden" name="groupRecipient" value="'.$groupRecipient.'" />';
        $content .= '<label>Subject: </label><input type="text" name="subject" value="'.htmlspecialchars($subject).'" /><br/>';
        $content .= '<label>message</label><br/>'.claro_html_textarea_editor('message', $message).'<br/><br/>';
        $content .= '<input type="submit" value="Send" name="send" /> <input type="button" value="back" name="back" />';
        $content .= '</form>';
    }
    
    $claroline->display->body->appendContent(claro_html_tool_title(get_lang('Compose a message')));
    $claroline->display->body->appendContent($content);
    
    // ------------- Display page -----------------------------
    echo $claroline->display->render();
    // ------------- End of script ----------------------------
?>