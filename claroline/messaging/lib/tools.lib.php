<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * some function used for internal messaging system
 *
 * @version     1.9 $Revision$
 * @copyright   2001-2008 Universite catholique de Louvain (UCL)
 * @author      Claroline Team <info@claroline.net>
 * @author      Christophe Mertens <thetotof@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html
 *              GNU GENERAL PUBLIC LICENSE version 2 or later
 * @package     internal_messaging
 */


    /**
     * create a argument string for a link
     *
     * @param array of string $paramList array of all argument
     * @param array of string $without array of argument name to don't add to the link
     * @return unknown
     */
    function makeArgLink($paramList,$without = array())
    {
    $argString = "";
    
    foreach ($paramList as $key => $arg)
    {
        if (!in_array($key, $without))
        {
            if ($argString != "")
            {
                $argString .= "&amp;";
            }
            $argString .= $key."=".rawurlencode($arg);
        }
    }

    return $argString;
    }
    
    /**
     * return the HTML source for the menu bar (to navigate between message box)
     *
     * @param int $currentUserId user identification (used for count of number of message unread
     * @return string HTML source
     */
    function getBarMessageBox( $currentUserId )
    {
        require_once dirname(__FILE__) . '/messagebox/inbox.lib.php';
        
        $inboxWithoutFilter = new InBox($currentUserId);

        /*$numberOfPlateformMessage = $inboxWithoutFilter->numberOfPlateformMessage();
        $messagePlateform = "";
        if ($numberOfPlateformMessage == 0)
        {
            $messagePlateform = "";
        }
        else
        {
            $messagePlateform = ''.$numberOfPlateformMessage." ".get_lang('plateform message');
        }*/
        
        $sectionList = array(
            'inbox' => get_lang(get_lang('inbox').'('.$inboxWithoutFilter->numberOfUnreadMessage().')'),
            'outbox' => get_lang('Outbox'),
            'trashbox' => get_lang('Trashbox')
        );
        
        $currentSection = isset( $_REQUEST['box'] )
        && in_array( $_REQUEST['box'], array_keys($sectionList) )
        ? $_REQUEST['box']
        : 'inbox'
        ;
        
        $parameter = array();
        if (isset($_REQUEST['userId']))
        {
            $parameter['userId'] = (int)$_REQUEST['userId'];
        }
        
        return claro_html_tab_bar($sectionList,$currentSection, $parameter, 'box');
        
        /*
        $menu[] = '<a href="' . get_path( 'clarolineRepositoryWeb' ) . 'messaging/messagebox.php?box=inbox&amp;userId='.$currentUserId.'" class="claroCmd">'.get_lang('inbox').'('.$inboxWithoutFilter->numberOfUnreadMessage().')</a>';
        $menu[] = '<a href="' . get_path( 'clarolineRepositoryWeb' ) . 'messaging/messagebox.php?box=outbox&amp;userId='.$currentUserId.'" class="claroCmd">'.get_lang('outbox').'</a>';
        $menu[] = '<a href="' . get_path( 'clarolineRepositoryWeb' ) . 'messaging/messagebox.php?box=trashbox&amp;userId='.$currentUserId.'" class="claroCmd">'.get_lang('trashbox').'</a>';
        
        return claro_html_menu_horizontal($menu);
		*/
    }
    
    function claro_is_user_platform_admin($userId)
    {
        require_once get_path('incRepositorySys') . '/lib/user.lib.php';

        $uidAdmin = claro_get_uid_of_platform_admin();
        
        return (in_array($userId,$uidAdmin));
    }
    
    function claro_is_user_course_manager($userId,$courseCode)
    {
        $tableName = get_module_main_tbl(array('cours_user'));
        
        $sql = "SELECT count(*)"
            ." FROM `".$tableName['cours_user']."`"
            ." WHERE code_cours = '" . claro_sql_escape($courseCode) . "'"
            ." AND user_id = " . (int)$userId
            ." AND isCourseManager = 1"
        ;
        
        return ( claro_sql_query_fetch_single_value($sql) > 0 );
    }

    function getPager($link,$currentPage,$totalPage)
    {
        // number of page to display in the page before and after thecurrent page
        $nbPageToDisplayBeforeAndAfterCurrentPage = 10;        
        
        $content = '<div id="im_paging">';
        
        // prepare list of page
        $beginPager = max(array(1,$currentPage-$nbPageToDisplayBeforeAndAfterCurrentPage));
        $endPager = min(array($totalPage,$currentPage+$nbPageToDisplayBeforeAndAfterCurrentPage));
        
        if ($beginPager != 1)
        {
            $content .= '&nbsp;<a href="'.$link.'1">1</a>'."\n";
            if ($beginPager-1 != 1)
            {
                $content .= '...'."\n";
            }
        }
        
        for ($countPage = $beginPager; $countPage <= $endPager; $countPage++)
        {
            if ($countPage == $currentPage)
            {
                $content .= $countPage."\n";
            }
            else
            {
                $content .= '<a href="'.$link.$countPage.'">'.$countPage.'</a>'."\n";
            }
        }
        if ($endPager != $totalPage)
        {
            if ($endPager+1 != $totalPage)
            {
                $content .= '...'."\n";
            }
            
            $content .= '<a href="'.$link.$totalPage.'">'.$totalPage.'</a>'."\n";
            
        }
        $content .= '<div/>';
        
        return $content;
    }