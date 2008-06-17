<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * controler of the outbox
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

    require_once dirname(__FILE__) . '/lib/messagebox/outbox.lib.php';
    
    $deleteConfirmation = FALSE;
    
    $acceptedCmdList = array('rqSearch');
    
    if (isset($_REQUEST['cmd']) && in_array($_REQUEST['cmd'], $acceptedCmdList))
    {
        if ($_REQUEST['cmd'] == 'rqSearch')
        {
            $displaySearch = TRUE;
        }
    }
    
    $currentSection = isset( $_REQUEST['box'] )
        ? $_REQUEST['box']
        : 'inbox'
        ;
    
    // create box
    $box = new OutBox($currentUserId);
    
    $messageStategy = $box->getMessageStrategy();
    
    if (isset($_REQUEST['fieldOrder']))
    {
        $link_arg['fieldOrder'] = $_REQUEST['fieldOrder'] == 'date' ? 'date' : 'date';
        
        if ($link_arg['fieldOrder'] == 'date')
        {
            $messageStategy->setFieldOrder(OutBoxStrategy::ORDER_BY_DATE);
        }
    }
    
    if (isset($_REQUEST['order']))
    {
        $order = $_REQUEST['order'] == 'asc' ? 'asc' : 'desc';
        
        $link_arg['order'] = $order;
        
        if ($link_arg['order'] == 'asc')
        {
            $nextOrder = "desc";
            $messageStategy->setOrder(OutBoxStrategy::ORDER_ASC);
        }
        else
        {
            $nextOrder = "asc";
            $messageStategy->setOrder(OutBoxStrategy::ORDER_DESC);
        }
    }
    else
    {
        $nextOrder = "asc";
    }    
    
    // search
    if (isset($_POST['search']) && $_POST['search'] != "")
    {
        $link_arg['search'] = $_POST['search'];
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
        $link_arg['searchStrategy'] = (int)$_GET['searchStrategy'];
    }
    
    if (isset($link_arg['search']))
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
    
    
    if (isset($_GET['page']))
    {
        $page = min(array((int)$_REQUEST['page'],$box->getNumberOfPage()));
        $page = max(array($page,1));
        $link_arg['page'] = $page;
        $messageStategy->setPageToDisplay($link_arg['page']);
    }
    
    $content .= getBarMessageBox($currentUserId, $currentSection);
    
    include "outboxview.inc.php";
?>