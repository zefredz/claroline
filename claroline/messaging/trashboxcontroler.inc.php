<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * controler of the trashbox
 *
 * @version     1.9 $Revision$
 * @copyright (c) 2001-2010, Universite catholique de Louvain (UCL)
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

    require_once dirname(__FILE__) . '/lib/messagebox/trashbox.lib.php';
    
    // create box
    $box = new TrashBox($currentUserId);
    $displayConfimationEmptyTrashbox = false;
    
    $messageId = isset($_REQUEST['messageId']) ? (int)$_REQUEST['messageId'] : NULL;
    
    $acceptedCmdList = array('exRestoreMessage','exMarkUnread','exMarkRead','rqSearch','rqEmptyTrashBox','exEmptyTrashBox');
    
    if (isset($_REQUEST['cmd']) && in_array($_REQUEST['cmd'], $acceptedCmdList))
    {
        if ($_REQUEST['cmd'] == 'rqSearch')
        {
            $displaySearch = TRUE;
        }
        
        if ($_REQUEST['cmd'] == 'exRestoreMessage' && ! is_null($messageId))
        {
            TrashBox::moveMessageToInBox($_REQUEST['messageId'],$currentUserId);
        }
        
        if ($_REQUEST['cmd'] == 'exMarkUnread' && ! is_null($messageId))
        {
            TrashBox::markUnread($_REQUEST['messageId'],$currentUserId);
        }
        
        if ($_REQUEST['cmd'] == 'exMarkRead' && ! is_null($messageId))
        {
            TrashBox::markRead($_REQUEST['messageId'],$currentUserId);
        }
        
        if ($_REQUEST['cmd'] == 'rqEmptyTrashBox')
        {
            $displayConfimationEmptyTrashbox = true;
        }
        
        if ($_REQUEST['cmd'] == 'exEmptyTrashBox')
        {
            $box->empyTrashBox();
            $box = new TrashBox($currentUserId);
        }
    }
    
    $currentSection = isset( $_REQUEST['box'] )
        ? $_REQUEST['box']
        : 'inbox'
        ;
    
    // set the order
    $messageStategy = $box->getMessageStrategy();

    if (isset($_REQUEST['fieldOrder']))
    {
        $link_arg['fieldOrder'] = $_REQUEST['fieldOrder'] == 'sender' ? 'sender' : 'date';
        
        
        if ($link_arg['fieldOrder'] == 'sender')
        {
            $messageStategy->setFieldOrder(ReceivedMessageStrategy::ORDER_BY_SENDER);
        }
        elseif ($link_arg['fieldOrder'] == 'date')
        {
            $messageStategy->setFieldOrder(ReceivedMessageStrategy::ORDER_BY_DATE);
        }
    }
    
    if (isset($_REQUEST['order']))
    {
        $order = $_REQUEST['order'] == 'asc' ? 'asc' : 'desc';
        
        $link_arg['order'] = $order;
        
        if ($link_arg['order'] == 'asc')
        {
            $nextOrder = "desc";
            $messageStategy->setOrder(ReceivedMessageStrategy::ORDER_ASC);
        }
        elseif ($link_arg['order'] == 'desc')
        {
            $nextOrder = "asc";
            $messageStategy->setOrder(ReceivedMessageStrategy::ORDER_DESC);
        }
    }
    else
    {
        $nextOrder = "asc";
    }
    
    // ----- read selector ------------
    $link_arg['SelectorReadStatus'] = isset( $_REQUEST['SelectorReadStatus'] )
        && in_array( $_REQUEST['SelectorReadStatus'], array('all','read','unread') )
        ? $_REQUEST['SelectorReadStatus']
        : 'all'
        ;
        
    if (isset($link_arg['SelectorReadStatus']))
    {
        if ($link_arg['SelectorReadStatus'] == "all")
        {
            $messageStategy->setReadStrategy(ReceivedMessageStrategy::NO_FILTER);
        }
        elseif ($link_arg['SelectorReadStatus'] == "read")
        {
            $messageStategy->setReadStrategy(ReceivedMessageStrategy::ONLY_READ);
        }
        elseif ($link_arg['SelectorReadStatus'] == "unread")
        {
            $messageStategy->setReadStrategy(ReceivedMessageStrategy::ONLY_UNREAD);
        }
    }
    
    // search
    if (isset($_POST['search']) && $_POST['search'] != "")
    {
        $link_arg['search'] = strip_tags($_POST['search']);
        if (isset($_POST['searchStrategy']))
        {
            $link_arg['searchStrategy'] = 1;
        }
        else
        {
            $link_arg['searchStrategy'] = 0;
        }
    }
    elseif (isset($_GET['search']) && $_GET['search'] != "")
    {
        $link_arg['search'] = strip_tags($_GET['search']);
        $link_arg['searchStrategy'] = $_GET['searchStrategy'];
    }
    
    if (isset ($link_arg['search']))
    {
        $messageStategy->setSearch($link_arg['search']);
        if ($link_arg['searchStrategy'] == 1)
        {
            $messageStategy->setSearchStrategy(MessageStrategy::SEARCH_STRATEGY_EXPRESSION);
        }
        elseif ($link_arg['searchStrategy'] == 0)
        {
            $messageStategy->setSearchStrategy(MessageStrategy::SEARCH_STRATEGY_WORD);
        }
    }
    
    // ---------------- set limit -----------------------
    // lets this part after selector/filter nb page depend of the selector/filter
    
    
    if (isset( $_REQUEST['page']))
    {
        
        $page = min(array((int)$_REQUEST['page'],$box->getNumberOfPage()));
        $page = max(array($page,1));
        $link_arg['page'] = $page;
        $messageStategy->setPageToDisplay($link_arg['page']);
    }
    
    // ------------ set the strategy
    $box->setMessageStrategy($messageStategy);
    
    $content .= getBarMessageBox($currentUserId, $currentSection);
    
    include "receivedmessageboxview.inc.php";
?>