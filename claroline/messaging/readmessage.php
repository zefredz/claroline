<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * read message
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
    require_once dirname(__FILE__).'/lib/displaymessage.lib.php';
    require_once dirname(__FILE__).'/lib/message/receivedmessage.lib.php';
    require_once dirname(__FILE__).'/lib/message/sentmessage.lib.php';
    require_once dirname(__FILE__).'/lib/permission.lib.php';
    require_once dirname(__FILE__).'/lib/tools.lib.php';
    
    // move to kernel
    $claroline = Claroline::getInstance();
    
    // ------------- Business Logic ---------------------------
    if ( ! claro_is_user_authenticated() )
    {
        claro_disp_auth_form(false);
    }
    
    
    
    $userId = claro_get_current_user_id();
    $displayConfimation = FALSE;

    if (isset($_REQUEST['userId']))
    {
        $userId = $_REQUEST['userId'];
    }
    if ($userId != claro_get_current_user_id() && !claro_is_platform_admin())
    {
        claro_die(get_lang("Not allowed"));
    }
    
    // load the message
    if (!isset($_REQUEST['messageId']) 
            || !isset($_REQUEST['type']) 
            || ($_REQUEST['type'] != "received" && $_REQUEST['type'] != "sent"))
    {
        claro_die(get_lang('Not allowed'));
    }
    if ($_REQUEST['type'] == "received")
    {
        try
        {
            $message = ReceivedMessage::fromId($_REQUEST['messageId'],$userId);
            
            if (claro_get_current_user_id() == $userId)
            {
                $message->markRead();
            }
        }
        catch (Exeption $e)
        {
            claro_die(get_lang('Message not found'));
        }
    }
    else
    {
        $message = SentMessage::fromId($_REQUEST['messageId']);
        
        // the sender is different from the current user id
        if ($message->getSender() != $userId)
        {
            claro_die(get_lang('Not allowed'));
        }
    }
    
    // command
    $acceptedCmd = array('exRestore','exDelete','rqDelete','markUnread');
    
    if (isset($_REQUEST['cmd']) 
            && in_array($_REQUEST['cmd'],$acceptedCmd) 
            && $_REQUEST['type'] == "received")
    {
        if ($_REQUEST['cmd'] == 'exRestore')
        {
            $message->moveToInBox();
            header('Location:./messagebox.php?box=trashbox');
        }
        elseif ($_REQUEST['cmd'] == 'exDelete')
        {
            $message->moveToTrashBox();
            header('Location:./messagebox.php?box=inbox');
        }
        elseif ($_REQUEST['cmd'] == 'rqDelete')
        {
            $displayConfimation = true;
        }
        elseif ($_REQUEST['cmd'] == 'markUnread')
        {
            $message->markUnread();
            if ($message->isDeleted())
            {
                header('Location:./messagebox.php?box=trashbox');
            }
            else
            {
                header('Location:./messagebox.php?box=inbox');
            }
                
        }
    }
    
    // ------------ Prepare display --------------------
    $content = "";
    
    if ($displayConfimation)
    {
        $content .= '<table class="claroMessageBox" border="0" cellpadding="10" cellspacing="0"><tbody><tr><td>'."\n";
        $content .= '<div class="dialogQuestion">'."\n";
        $content .= get_lang('Are you sure to delete').'<br/><br/>'."\n";
        $content .= '<a href="'.$_SERVER['PHP_SELF'].'?cmd=exDelete&amp;messageId='.$_REQUEST['messageId'].'&amp;type='.$_REQUEST['type'].'&amp;userId='.$userId.'">'.get_lang('Yes').'</a> | <a href="./messagebox.php?box=inbox&amp;userId='.$userId.'">'.get_lang('No').'</a>'."\n";
        $content .= '</div>'."\n";
        $content .= '</td></tr></tbody></table><br/>'."\n\n";
    }
    
    
    $action = '';
    if ($_REQUEST['type'] == "received")
    {
        if ($message->getRecipient() > 0 || claro_is_platform_admin())
        {
            if ($message->isDeleted())
            {
                if ($message->getRecipient() == $userId || claro_is_platform_admin())
                {
                    $action .= ' [<a href="'.$_SERVER['PHP_SELF'].'?cmd=exRestore&amp;messageId='.$_REQUEST['messageId'].'&amp;type='.$_REQUEST['type'].'&amp;userId='.$userId.'">'.get_lang('Move to inbox').'</a>]';
                }
            }
            else
            {
                $javascriptDelete = '
                <script type="text/javascript">
                function deleteMessage ( localPath )
                {
                    if (confirm("'.get_lang('Are you sure to delete').'"))
                    {
                        window.location=localPath;
                        return false;
                    }
                    else
                    {
                        return false;
                    }
                }
                </script>';
                $claroline->display->header->addHtmlHeader($javascriptDelete);
                
                $action .= ' [<a href="'.$_SERVER['PHP_SELF'].'?cmd=rqDelete&amp;messageId='.$_REQUEST['messageId'].'&amp;type='.$_REQUEST['type'].'&amp;userId='.$userId.'"
                 onclick="return deleteMessage(\''.$_SERVER['PHP_SELF'].'?cmd=exDelete&amp;messageId='.$_REQUEST['messageId'].'&amp;type='.$_REQUEST['type'].'&amp;userId='.$userId.'\')"><img src="img/user-trash-full.gif" alt="" /></a>]';
            }
        }
        else
        {
            //tothing to do
        }
    }
    else
    {
        // nothing to do
    }
    
    $content .= DisplayMessage::display($message,$action);
    
    if ($_REQUEST['type'] == "received")
    {
        if ($message->isDeleted())
        {
            $claroline->display->banner->breadcrumbs->append(get_lang('My trashbox'),'./messagebox.php?box=trashbox&amp;userId='.$userId);
        }
        else
        {
            $claroline->display->banner->breadcrumbs->append(get_lang('My inbox'),'./messagebox.php?box=inbox&amp;userId='.$userId);
        }
    }
    else
    {
        $claroline->display->banner->breadcrumbs->append(get_lang('My outbox'),'./messagebox.php?box=outbox&amp;userId='.$userId);
    }
    
    $claroline->display->banner->breadcrumbs->append(get_lang('Read message'));
    $claroline->display->body->appendContent(claro_html_tool_title(get_lang('Read Message')));
    $claroline->display->body->appendContent(getBarMessageBox($userId));
    $claroline->display->body->appendContent($content);
    
    // ------------- Display page -----------------------------
    echo $claroline->display->render();
    // ------------- End of script ----------------------------
?>