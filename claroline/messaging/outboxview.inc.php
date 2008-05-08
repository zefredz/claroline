<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * view of the outbox
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

    // confirmation to delete message
    if ($deleteConfirmation)
    {
        // link to delete
        $arg_del = makeArgLink($link_arg,array('cmd'));
        if ($arg_del == "")
        {
            $linkDelete = $linkPage."?";
        }
        else
        {
            $linkDelete = $linkPage."?".$arg_del."&amp;";
        }
        $linkDelete .= "cmd=exDelete&amp;messageId=".$_REQUEST['messageId'];
        
        // link to back
        $arg_back = makeArgLink($link_arg);
        if ($arg_back == "")
        {
            $linkBack = $linkPage;
        }
        else
        {
            $linkBack = $linkPage."?".$arg_sort;
        }
        
        
        $confirmationString  = get_lang('<strong>Warning:</strong> The suppression of the message will erase all trace. It will deleted from message box of all users');
        $confirmationString .= '<br /><br />' . get_lang('Are you sure to delete this message');
        
        $confirmationString .= '<br /><br /><br /><a href="'.$linkDelete.'">'.get_lang('Yes').'</a> | <a href="'.$linkBack.'">'.get_lang('No').'</a>';
        
        $dialBox = new DialogBox();
        $dialBox->question($confirmationString);
        $content .= $dialBox->render();
    }
    
    
    
    // -------------------- selector form ----------------
    if (isset($displaySearch) && $displaySearch)
    {
        $arg_search = makeArgLink($link_arg,array('SelectorReadStatus','SelectorName','SelectorSubject'));
        $linkSearch = $linkPage."?".$arg_search;
        
        $searchBox = '<form action="'.$linkSearch.'" method="post">'."\n";
        $searchBox .= get_lang("Search").' : <input type="text" name="search" value="';
        if (isset($link_arg['search']))
        {
            $searchBox .= $link_arg['search'];
        }
        $searchBox .= '" /> <br />'."\n";
        $searchBox .= '<input type="checkbox" name="searchStrategy" value="'.get_lang('Match the exact expression').'"';
        if (isset($link_arg['searchStrategy']) && $link_arg['searchStrategy'] == 1)
        {
            $searchBox .= " CHECKED";
        }
        $searchBox .= ' />'.get_lang('Match the exact expression').'<br/><br/>'."\n";
        $searchBox .= '<input type="submit" value="'.get_lang("Search").'" />'."\n";
        $searchBox .= '</form>'."\n";
        
        $dialBox = new DialogBox();
        $dialBox->form($searchBox);
        $content .= "<br /><br />".$dialBox->render();        
    }
    else
    {
        $arg_search = makeArgLink($link_arg,array('SelectorReadStatus','search','searchStrategy'));
        $linkSearch = $linkPage."?".$arg_search;
        $linkSearch .= "&amp;cmd=rqSearch";
        
        $serachForm = '<form action="'.$linkSearch.'" method="post">'."\n"
                    . '<input type="text" name="search" value="'
                    ;
        if (isset($link_arg['search']))
        {
            $serachForm .= $link_arg['search'];
        }
        $serachForm .= '" class="inputSearch" />'."\n"
                . '<input type="submit" value="'.get_lang("Search").'" />'."\n"
                . '[<a href="'.$linkSearch.'">'.get_lang("Advanced").'</a>]'
                . '</form>'."\n"
                ;
        
        $dialbox = new DialogBox();
        $dialbox->form($serachForm);
        
        $content .= "<br /><br />".$dialbox->render();
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
        
    $content .= '<table class="claroTable emphaseLine" width="100%">'."\n";
    $content .= '<tr class ="headerX"> '."\n"
                    .'<th>'.get_lang("Subject").'</th>'."\n"
                    .'<th>'.get_lang("Recipient").'</th> '."\n"
                    .'<th><a href="'.$linkSort.'fieldOrder=date&amp;order='.$nextOrder.'">'.get_lang("Date").'</a></th>'."\n"
                    ;
    if ( claro_is_platform_admin() )
    {
        $javascriptDelete = '
            <script type="text/javascript">
            function deleteSentMessage ( localPath )
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
        $content .= '<th>'.get_lang("Delete").'</th> '."\n";
    }
    $content .= '</tr>'."\n\n";
    
    if ($box->getNumberOfMessage() == 0)
    {
        $content .= '<tr><td colspan="4">No message</td></tr>'."\n\n";
    }
    else
    {
        foreach ($box as $key => $message)
        {
            $recipientList = $message->getRecipientList();
            //var_dump($recipientList);
            $content .= '<tr';
            if ($recipientList['sentTo'] == 'toAll')
            {
                $content .= ' class="plateformMessage"';
            }
            $content .= '><td>';

            if ($recipientList['sentTo'] == 'toAll')
            {
                $content .= '<img src="img/important.png" alt="" />';
            }
            
            if (!is_null($message->getCourseCode()))
            {
                $content .= '<span class="im_context">[';
                $courseData = claro_get_course_data($message->getCourseCode());
                if($courseData)
                {
                    $content .= $courseData['officialCode'];
                }
                else
                {
                    $content .= get_lang('"Course deleted"');
                }
                if (!is_null($message->getToolsLabel()))
                {
                    $md = get_module_data($message->getToolsLabel());
                    $content .= ' - '.get_lang($md['moduleName']);
                }
                $content .= ']</span> ';
            }
            $content .= '<a href="readmessage.php?messageId='.$message->getId().'&amp;type=sent&amp;userId='.$currentUserId.'">';
            $content .=  htmlspecialchars($message->getSubject()).'</a></td>'."\n"
                        .'<td>';
                        
            if ( $recipientList['sentTo'] == 'toUser' )
            {
                $content .= htmlspecialchars($recipientList['userList'][0]['firstName'])." ".htmlspecialchars($recipientList['userList'][0]['lastName']);
                
                if ( count( $recipientList['userList'] ) > 1 )
                {
                    $content .=  ", ".htmlspecialchars($recipientList['userList'][1]['firstName'])." ".htmlspecialchars($recipientList['userList'][1]['lastName']);
                }
                
                if ( count( $recipientList['userList'] ) > 2 )
                {
                    $content .= ",...";
                }
            }
            elseif ($recipientList['sentTo'] == 'toCourse')
            {
                $content .= get_lang('Course: ')." ". $message->getCourseCode();
            }
            elseif ($recipientList['sentTo'] == 'toGroup')
            {
                $groupInfo = claro_get_group_data(array(CLARO_CONTEXT_COURSE => $message->getCourseCode(),
                										CLARO_CONTEXT_GROUP => $message->getGroupId()));
                $courseInfo = claro_get_course_data($message->getCourseCode());
                if (!$groupInfo)
                {
                    $content .= get_lang('Course: ')." ".get_lang('"course deleted"'). "; " .get_lang('Group: ')." ".get_lang('unknown');
                }
                else
                {
                    $content .= get_lang('Course: ')." ". $courseInfo['officialCode'] . "; " .get_lang('Group: ')." ". $groupInfo['name'];
                }
            }
            elseif ($recipientList['sentTo'] == 'toAll')
            {
                 $content .= get_lang('All users of the plateform');
            }
            else
            {
                $content .= get_lang('Unknown recipient');
            }
            
            $content .=  '</td>'
            			.'<td>'.claro_html_localised_date(get_locale('dateTimeFormatLong'),strtotime($message->getSendTime())).'</td>'."\n"
            			;
            if ( claro_is_platform_admin() )
            {
                $arg_sort = makeArgLink($link_arg,array('fieldOrder','order'));
                if ($arg_sort == "")
                {
                    $linkDel = $linkPage."?";
                }
                else
                {
                    $linkDel = $linkPage."?".$arg_sort."&amp;";
                }
                $linkDelete = $linkDel . "cmd=exDelete&amp;messageId=".$message->getId();
                $linkRqDelete = $linkDel . "cmd=rqDelete&amp;messageId=".$message->getId();
                $content .=  '<td><a href="'.$linkRqDelete.'"'
                 	. 'onclick="return deleteSentMessage(\''.$linkDelete.'\')"><img src="' . get_icon('delete.gif') . '" alt = "'.get_lang('Delete').'" /></a></td>';
            }
            $content .=  '</tr>'."\n\n";
        }
    }
    $content .= '</table>'."\n";
    
    // prepare the link to change of page
    // prepare the link to change of page
    if ($box->getNumberOfPage()>1)
    {
        // number of page to display in the page before and after thecurrent page
        $nbPageToDisplayBeforeAndAfterCurrentPage = 1;        
        
        $content .= '<div id="im_paging">';
        $arg_paging = makeArgLink($link_arg,array('page'));  
        if ($arg_paging == "")
        {
            $linkPaging = $linkPage."?page=";
        }
        else
        {
            $linkPaging = $linkPage."?".$arg_paging."&amp;page=";
        }
        
        if(!isset($link_arg['page']))
        {
            $page=1;
        }
        else
        {
            $page = $link_arg['page'];
        }
        
        echo getPager($linkPaging,$page,$box->getNumberOfPage());
        $content .= getPager($linkPaging,$page,$box->getNumberOfPage());
    }
?>