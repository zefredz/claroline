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

    
    // -------------------- selector form ----------------
    if (isset($displaySearch) && $displaySearch)
    {
        $arg_search = makeArgLink($link_arg,array('SelectorReadStatus','SelectorName','SelectorSubject'));
        $linkSearch = $linkPage."?".$arg_search;
        
        $searchBox = '<form action="'.$linkSearch.'" method="post">'."\n";
        $searchBox .= '<input type="text" name="search" value="';
        
        if (isset($link_arg['search']))
        {
            $searchBox .= $link_arg['search'];
        }
        
        $searchBox .= '" class="inputSearch" />'."\n";
        $searchBox .= '<input type="submit" value="'.get_lang("Search").'" /><br />'."\n";
        $searchBox .= '<input type="checkbox" name="searchStrategy" value="'.get_lang('Match the exact expression').'"';
        
        
        if (isset($link_arg['searchStrategy']) && $link_arg['searchStrategy'] == 1)
        {
            $searchBox .= ' checked="checked"';
        }
        
        $searchBox .= ' />'.get_lang('Exact expression')."\n";
        
        $searchBox .= '</form>'."\n";
        
        $dialogbox = new DialogBox();
        $dialogbox->form($searchBox);
        $content .= $dialogbox->render();        
    }
    else
    {
        $arg_search = makeArgLink($link_arg,array('SelectorReadStatus','search','searchStrategy'));
        $linkSearch = $linkPage."?".$arg_search;
        $linkSearch .= '&amp;cmd=rqSearch';
        
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
        
        $dialogbox = new DialogBox();
        $dialogbox->form($serachForm);
        
        $content .= $dialogbox->render();
    }
    //----------------------end selector form -----------------
    
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
    
    $content .= '</tr>'."\n\n";
    
    if ($box->getNumberOfMessage() == 0)
    {
        $content .= '<tr><td colspan="3">'.get_lang('Empty').'</td></tr>'."\n\n";
    }
    else
    {
        foreach ($box as $key => $message)
        {
            $recipientList = $message->getRecipientList();
            //var_dump($recipientList);
            $content .= '<tr';
            if ($message->isPlatformMessage())
            {
                $content .= ' class="platformMessage"';
            }
            $content .= '><td>';

            if ($message->isPlatformMessage())
            {
                $content .= '<img src="' . get_icon_url('important') . '" alt="" />';
            }
            
            if (!is_null($message->getCourseCode()))
            {
                $courseData = claro_get_course_data($message->getCourseCode());
                if ($courseData)
                {
                    $content .= '<span class="im_context">'
                    .   '[' . $courseData['officialCode'];
                    
                    if (!is_null($message->getToolsLabel()))
                    {
                        $md = get_module_data($message->getToolsLabel());
                        $content .= ' - '.get_lang($md['moduleName']);
                    }
                    
                    $content .= ']</span> ';
                }
            }
            
            $content .= ' <a href="readmessage.php?messageId='.$message->getId().'&amp;type=sent&amp;userId='.$currentUserId.'">';
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
                    $content .= get_lang('Course').' : '.get_lang('unknown'). "; " .get_lang('Group').' : '.get_lang('unknown');
                }
                else
                {
                    $content .= get_lang('Course').' : ' . $courseInfo['officialCode'] . "; " .get_lang('Group').' : '. $groupInfo['name'];
                }
            }
            elseif ($message->isPlatformMessage())
            {
                 $content .= get_lang('All users of the platform');
            }
            else
            {
                $content .= get_lang('Unknown recipient');
            }
            
            $content .=  '</td>'
                        .'<td>'.claro_html_localised_date(get_locale('dateTimeFormatLong'),strtotime($message->getSendTime())).'</td>'."\n"
                        ;
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
?>