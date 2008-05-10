<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * front controler for message box
 *
 * @version     1.9 $Revision$
 * @copyright   2001-2008 Universite catholique de Louvain (UCL)
 * @author      Claroline Team <info@claroline.net>
 * @author      Christophe Mertens <thetotof@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html
 *              GNU GENERAL PUBLIC LICENSE version 2 or later
 * @package     internal_messaging
 */


    $cidReset = TRUE; 
    require_once dirname(__FILE__) . '/../../claroline/inc/claro_init_global.inc.php';

    // move to kernel
    $claroline = Claroline::getInstance();
    
    // ------------- Business Logic ---------------------------
    if ( ! claro_is_user_authenticated() )
    {
        claro_disp_auth_form(false);
    }
    
    include claro_get_conf_repository() . 'CLMSG.conf.php';
    require_once dirname(__FILE__) . '/lib/permission.lib.php';
    
    
    $link_arg = array();
    
    if (isset($_REQUEST['userId']) && !empty($_REQUEST['userId']))
    {
        $currentUserId = (int)$_REQUEST['userId'];
        $link_arg['userId'] = $currentUserId;
    }
    else
    {
        $currentUserId = claro_get_current_user_id();
    }
    
    if ($currentUserId != claro_get_current_user_id() && !claro_is_platform_admin())
    {
        claro_die(get_lang("Not allowed"));
    }
    
    $cssLoader = CssLoader::getInstance();
    $linkPage = $_SERVER['PHP_SELF'];
    
    $acceptedValues = array('inbox','outbox','trashbox');
    
    if (!isset($_REQUEST['box']) && !in_array($_REQUEST['box'],$acceptedValues))
    {
        $_REQUEST['box'] = "inbox";
    }
    $link_arg['box'] = $_REQUEST['box'];
    
    
    require_once dirname(__FILE__) . '/lib/tools.lib.php';
    
    $content = "";
    if ($_REQUEST['box'] == "inbox")
    {
        $title = get_lang('Inbox');
        include dirname(__FILE__) . '/inboxcontroler.inc.php';
    }
    elseif ($_REQUEST['box'] == "outbox")
    {
        $title = get_lang('Outbox');
        include dirname(__FILE__) . '/outboxcontroler.inc.php';
    }
    else
    {
        $title = get_lang('Trashbox');
        include dirname(__FILE__) . '/trashboxcontroler.inc.php';
    }
    
    $claroline->display->banner->breadcrumbs->append($title,$_SERVER['PHP_SELF'].'?box='.$link_arg['box']);
    $claroline->display->body->appendContent(claro_html_tool_title(get_lang('My messages')." - ".$title));
    $claroline->display->body->appendContent($content);
    // ------------ display ----------------------
    
    echo $claroline->display->render();
?>