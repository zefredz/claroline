<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * view of the inbox and trashbox
 *
 * @version     1.9 $Revision$
 * @copyright   2001-2008 Universite catholique de Louvain (UCL)
 * @author      Claroline Team <info@claroline.net>
 * @author      Christophe Mertens <thetotof@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html
 *              GNU GENERAL PUBLIC LICENSE version 2 or later
 * @package     internal_messaging
 */


    if ( count( get_included_files() ) == 1 )
    {
        die( 'The file ' . basename(__FILE__) . ' cannot be accessed directly, use include instead' );
    }

    require_once dirname(__FILE__) . '/lib/displaymessage.lib.php';
    
    // variable initilization
    
    $messageId = (isset($_GET['messageId'])) ? (int)$_GET['messageId']: NULL;
    
    $content .= "<br />";
        
    if (isset($displayConfimation) && $displayConfimation)
    {
        // link to delete
        $arg_deleting = makeArgLink($link_arg);
        if ($arg_deleting == "")
        {
            $linkDelete = $linkPage."?";
            $linkBack = $linkPage;
        }
        else
        {
            $linkDelete = $linkPage."?".$arg_deleting."&amp;";
            $linkBack = $linkPage."?".$arg_paging;
        }
        $linkDelete .= "cmd=exDeleteMessage&amp;messageId=".$messageId;
        
        //----------------------- table display --------------------
        
        $confirmationDelete = get_lang('Are you sure to move to trashbox').'<br/><br/>'."\n";
        $confirmationDelete .= '<a href="'.$linkDelete.'">'.get_lang('Yes').'</a> | <a href="'.$linkBack.'">'.get_lang('No').'</a>'."\n";
        
        $dialogbox = new DialogBox();
        $dialogbox->question($confirmationDelete);
        $content .= "<br />" .$dialogbox->render();
    }
    
    if (isset($displayConfimationEmptyTrashbox) && $displayConfimationEmptyTrashbox)
    {
        $arg_emptyTrashBox = makeArgLink($link_arg);
        $linkEmptyTrashBox = $linkPage."?".$arg_emptyTrashBox;
        $linkBack = $linkEmptyTrashBox;
        if ($arg_emptyTrashBox != "")
        {
            $linkEmptyTrashBox .= "&amp;";
        }
        $linkEmptyTrashBox = $linkEmptyTrashBox."cmd=exEmptyTrashBox";

        $confirmationEmpty = get_lang('Are you sure to empty you trash box')
            . '<br /><br />'
            . '<a href="'.$linkEmptyTrashBox.'">'.get_lang('Yes').'</a> | <a href="'.$linkBack.'">'.get_lang('No').'</a>'
            ;
        $dialogbox = new DialogBox();
        $dialogbox->question($confirmationEmpty);
        $content .= $dialogbox->render();
    }
    
    // -------------------- selector form ----------------
    if (isset($displaySearch) && $displaySearch)
    {
        $arg_search = makeArgLink($link_arg,array('SelectorReadStatus','search','searchStrategy'));
        $linkSearch = $linkPage."?".$arg_search;
        
        $searchForm = '<form action="'.$linkSearch.'" method="post">'."\n"
                    . '<input type="text" name="search" value="'
                    ;
        if (isset($link_arg['search']))
        {
            $searchForm .= $link_arg['search'];
        }
        $searchForm .= '" class="inputSearch" /> '."\n"
                     . '    <select name="SelectorReadStatus" size="1">'
                     . '        <option value="all" '
                     ;
        if (isset($link_arg['SelectorReadStatus']) && $link_arg['SelectorReadStatus'] == "all")
        {
            $searchForm .= "selected";
        }
        $searchForm .= '>'.get_lang("read and unread").'</option>'
                    . '        <option value="read" '
                    ;
        if (isset($link_arg['SelectorReadStatus']) && $link_arg['SelectorReadStatus'] == "read")
        {
            $searchForm .= "selected";
        }
        $searchForm .= '>'.get_lang("read only").'</option>'
                    . '        <option value="unread" ';
        if (isset($link_arg['SelectorReadStatus']) && $link_arg['SelectorReadStatus'] == "unread")
        {
            $searchForm .= "selected";
        }
        $searchForm .= '>'.get_lang("unread only").'</option>'    
                    . '    </select> '
                    . '<input type="submit" value="'.get_lang("Search").'" /><br />'."\n"
                    . '</form>'."\n"
                    . '<input type="checkbox" name="searchStrategy" value="'.get_lang('Match the exact expression').'"'
                    ;
                    
        if (isset($link_arg['searchStrategy']) && $link_arg['searchStrategy'] == 1)
        {
            $searchForm .= " CHECKED";
        }
        $searchForm .= ' />'.get_lang('Exact expression')."\n";
        $dialogbox = new DialogBox();
        $dialogbox->form($searchForm);
        
        $content .= "<br />".$dialogbox->render();
        
    }
    else
    {
        $arg_search = makeArgLink($link_arg,array('SelectorReadStatus','search','searchStrategy'));
        $linkSearch = $linkPage."?".$arg_search;
        
        $serachForm = '<form action="'.$linkSearch.'" method="post">'."\n"
                    . '<input type="text" name="search" value="'
                    ;
        if (isset($link_arg['search']))
        {
            $serachForm .= $link_arg['search'];
        }
        $serachForm .= '" class="inputSearch" />'."\n"
                . '<input type="submit" value="'.get_lang("Search").'" />'."\n"
                . '[<a href="'.$linkSearch.'&amp;cmd=rqSearch">'.get_lang("Advanced").'</a>]'
                . '</form>'."\n"
                ;
        
        $dialogbox = new DialogBox();
        $dialogbox->form($serachForm);
        
        $content .= "<br />".$dialogbox->render();
    }
    //----------------------end selector form -----------------
    
    $content .= "<br />";

    $arg_sort = makeArgLink($link_arg,array('fieldOrder','order'));
    if ($arg_sort == "")
    {
        $linkSort = $linkPage."?";
    }
    else
    {
        $linkSort = $linkPage."?".$arg_sort."&amp;";
    }
    
    $content .= '<table class="claroTable emphaseLine" width="100%">'."\n\n";
    $content .= '<tr class ="headerX"> '."\n"
                .'<th>'.get_lang("Subject").'</th>'."\n"
                .'<th><a href="'.$linkSort.'fieldOrder=sender&amp;order='.$nextOrder.'">'.get_lang("Sender").'</a></th>'."\n"
                .'<th><a href="'.$linkSort.'fieldOrder=date&amp;order='.$nextOrder.'">'.get_lang("Date").'</a></th>'."\n"
                .'<th class="im_list_action">';
    if ($link_arg['box'] == "inbox")
    {
       $content .= get_lang("Delete"); 
    }
    else
    {
        $content .= get_lang("Restore"); 
    }
    $content .=      '</th>'."\n"
            .'</tr>'."\n\n"
            ;
    
    if ($box->getNumberOfMessage() == 0)
    {
        $content .= '<tr><td colspan="4">'.get_lang("No message").'</td></tr>'."\n\n";
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
        
        $arg_deleting = makeArgLink($link_arg);
        if ($arg_deleting == "")
        {
            $link = $linkPage."?";
        }
        else
        {
            $link = $linkPage."?".$arg_deleting."&amp;";
        }
        foreach ($box as $key => $message)
        {
            $content .= '<tr';
            if ($message->isPlatformMessage())
            {
                $content .= ' class="plateformMessage"';
            }
            elseif (!$message->isRead())
            {
                $content .= ' class="unreadMessage"';
            }
            else
            {
                $content .= ' class="readMessage"';
            }
            // ---------------- sujet
            $content .= '>'."\n".'<td>';
            
            if ( ! $message->isPlatformMessage() )
            {
                if (!$message->isRead())
                {
                    if (claro_get_current_user_id() == $currentUserId)
                    {
                        $content .= '<a href="'.$link.'cmd=exMarkRead&amp;messageId='.$message->getId().'">';
                        $content .= '<img src="img/unreadmessage.gif" alt="'.get_lang("Unread message").'" /></a>&nbsp;';
                    }
                    //if admin read messagebox of a other user he cannot change status
                    else
                    {
                        $content .= '<img src="img/unreadmessage.gif" alt="'.get_lang("Unread message").'" />&nbsp;';    
                    }
                }
                else
                {
                    $content .= '<a href="'.$link.'cmd=exMarkUnread&amp;messageId='.$message->getId().'">';
                    $content .= '<img src="img/readmessage.gif" alt="'.get_lang("read").'" /></a>&nbsp;';
                }
            }
            else
            {
                $content .= '<img src="img/important.gif" alt="" />&nbsp;';
            }
            
            if (!is_null($message->getCourseCode()))
            {
                $content .= '<span class="im_context">[';
                $courseData = claro_get_course_data($message->getCourseCode());
                if ($courseData)
                {
                    $content .= $courseData['officialCode'];
                }
                else
                {
                    $content .= '?';
                }
                
                if (!is_null($message->getToolsLabel()))
                {
                    $md = get_module_data($message->getToolsLabel());
                    $content .= ' - '.get_lang($md['moduleName']);
                }
                $content .= ']</span>&nbsp;';
            }
            $content.= '<a href="readmessage.php?messageId='.$message->getId().'&amp;userId='.$currentUserId.'&amp;type=received">';
            $content .= htmlspecialchars($message->getSubject()).'</a></td>'."\n"
                    . '<td>'
                    ;
            // ------------------ sender
            
            $content .= DisplayMessage::dispNameLinkCompose($message->getSender(),$message->getSenderLastName(),$message->getSenderFirstName());
            
            $isManager = FALSE;
            $isAdmin = claro_is_user_platform_admin($message->getSender());
            if (!is_null($message->getCourseCode()))
            {
                $isManager = claro_is_user_course_manager($message->getSender(),$message->getCourseCode());
            }
            
            if ($isManager)
            {
                $content .= '&nbsp;<img src="' . get_icon('manager.gif') . '" alt="" />';
            }
            elseif ($isAdmin)
            {
                $content .= '&nbsp;<img src="' . get_icon('platformadmin.gif') . '" alt="" />';
            }
            
            $content .= '</td>'."\n"
            // --------------------date
                .'<td>'.claro_html_localised_date(get_locale('dateTimeFormatShort'),strtotime($message->getSendTime())).'</td>'."\n"
            // ------------------- action
                .'<td class="im_list_action">';
            if ( ! $message->isPlatformMessage() )
            {
                if ($link_arg['box'] == "inbox")
                {
                    $content .= '<a href="'.$link.'cmd=rqDeleteMessage&amp;messageId='.$message->getId().'"'
                        .' onclick="return deleteMessage(\''.$link.'cmd=exDeleteMessage&amp;messageId='.$message->getId().'\')"><img src="img/user-trash-full.gif" alt="" /></a>';
                }
                else
                {
                    $content .= '<a href="'.$link.'cmd=exRestoreMessage&amp;messageId='.$message->getId().'">'.get_lang('Move to inBox').'</a>';
                }
            }
            else
            {
                $content .= "&nbsp;";
            }
            $content .=     '</td>'."\n"
            // ----------------- end of line
                        .'</tr>'."\n\n";
        }
    }
    $content .= '</table>'."\n";
    
    // prepare the link to change of page
    if ($box->getNumberOfPage()>1)
    {
        $arg_paging = makeArgLink($link_arg,array('page'));  
        if ($arg_paging == "")
        {
            $linkPaging = $linkPage."?page=";
        }
        else
        {
            $linkPaging = $linkPage."?".$arg_paging."&amp;page=";
        }
        
        if (!isset($link_arg['page']))
        {
            $page=1;
        }
        else
        {
            $page = $link_arg['page'];
        }
        
        $content .= getPager($linkPaging,$page,$box->getNumberOfPage());
    }
//------------------ function of the trashbox
    if ($link_arg['box'] == "trashbox")
    {
        // ---------- generate the link
        $arg_emptyTrashBox = makeArgLink($link_arg);
        $linkTOEmpltyTrashBox = $linkPage."?".$arg_emptyTrashBox;
        if ($arg_emptyTrashBox != "")
        {
            $linkTOEmpltyTrashBox .= "&amp;";
        }
        $linkToRqEmptyTrashBox = $linkTOEmpltyTrashBox."cmd=rqEmptyTrashBox";
        $linkToExEmptyTrashBox = $linkTOEmpltyTrashBox."cmd=exEmptyTrashBox";
        // ------------ end of generating link
        
        $javascriptDelete = '
            <script type="text/javascript">
            function emptyTrashBox ( localPath )
            {
                if (confirm("'.get_lang('Are you sure to empty trashbox').'"))
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
        
        $content .= "<br />";
        $menu[] = '<a href="'.$linkToRqEmptyTrashBox.'" 
                    onclick="return emptyTrashBox(\''.$linkToExEmptyTrashBox.'\')" class="claroCmd" >'.get_lang('Empty my trashbox').'</a>';
        
        $content .= claro_html_menu_horizontal($menu);
        $content .= "<br /><br />\n\n";
    }
    // ------------------ end of fonction of the trash box
        
?>