<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * page of deleting message for the administrator
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
// manager of the admin message box
require_once dirname(__FILE__) . '/lib/messagebox/adminmessagebox.lib.php';

require_once dirname(__FILE__) . '/lib/tools.lib.php';
require_once dirname(__FILE__) . '/lib/userlist.lib.php';

// move to kernel
$claroline = Claroline::getInstance();

// ------------- permission ---------------------------
if ( ! claro_is_user_authenticated())
{
    claro_disp_auth_form(false);
}

if ( ! claro_is_platform_admin() )
{
    claro_die(get_lang('Not allowed'));
}

// -------------- business logic ----------------------
$content = "";

$displayRemoveAllConfirmation = FALSE;
$displayRemoveAllValidated = FALSE;

$displayRemoveFromUserConfirmation = FALSE;
$displayRemoveFromUserValidated = FALSE;
$displaySearchUser = FALSE;
$displayResultUserSearch = FALSE;

$displayRemoveOlderThanConfirmation = FALSE;
$displayRemoveOlderThanValidated = FALSE;

$displayRemovePlatformMessageConfirmation = FALSE;
$displayRemovePlatformMessageValidated = FALSE;

$userId = isset($_REQUEST['userId'])? (int)$_REQUEST['userId'] : NULL;

//used for user search
$arguments = array();

$acceptedCommand = array('rqDeleteAll','exDeleteAll'
                        ,'rqFromUser','exFromUser'
                        ,'rqOlderThan','exOlderThan'
                        ,'rqPlatformMessage','exPlatformMessage');

// ------------- display
if (isset($_REQUEST['cmd']) && in_array($_REQUEST['cmd'],$acceptedCommand))
{
    // -------- delete all
    if ($_REQUEST['cmd'] == "rqDeleteAll")
    {
        $claroline->display->body->appendContent(claro_html_tool_title(get_lang('Internal messaging')." - ".get_lang('Delete all messages')));
        $displayRemoveAllConfirmation = TRUE;
    }
    
    if ($_REQUEST['cmd'] == "exDeleteAll")
    {
        $claroline->display->body->appendContent(claro_html_tool_title(get_lang('Internal messaging')." - ".get_lang('Delete all messages')));
        $box = new AdminMessageBox();
        $box->deleteAllMessages();
        $displayRemoveAllValidated = TRUE;
    }
    
    // -----------delete from user
    if ($_REQUEST['cmd'] == 'rqFromUser')
    {
        $claroline->display->body->appendContent(claro_html_tool_title(get_lang('Internal messaging')." - ".get_lang('Delete all user\'s messages')));
        $arguments['cmd'] = 'rqFromUser';
        if ( ! is_null($userId) )
        {
            $displayRemoveFromUserConfirmation = TRUE;
        }
        else
        {
            $displaySearchUser = TRUE;
        }
        // generate the user list
        if (isset($_REQUEST['search']) && $_REQUEST['search'] != "")
        {
            $displayResultUserSearch = TRUE;
            $arguments['search'] = strip_tags($_REQUEST['search']);
                        
            $userList = new UserList();
            $selector = $userList->getSelector();
            
            //order
            if (isset($_REQUEST['order']))
            {
                $order = $_REQUEST['order'] == 'asc' ? 'asc' : 'desc';
                
                $arguments['order'] = $order;
                
                if ($arguments['order'] == 'asc')
                {
                    $selector->setOrder(UserStrategy::ORDER_ASC);
                    $nextOrder = 'desc';
                }
                else
                {
                    $selector->setOrder(UserStrategy::ORDER_DESC);
                    $nextOrder = 'asc';
                }
            }
            else
            {
                $nextOrder = 'desc';
            }
            //orderfield
            if (isset($_REQUEST['fieldOrder']))
            {
                $fieldOrder = $_REQUEST['fieldOrder'] == 'name' ? 'name' : 'username';
                
                $arguments['fieldOrder'] = $fieldOrder;
                
                if ($arguments['fieldOrder'] == 'name')
                {
                    $selector->setFieldOrder(UserStrategy::ORDER_BY_NAME);
                }
                else
                {
                    $selector->setFieldOrder(UserStrategy::ORDER_BY_USERNAME);
                }
            }
            //namesearch
            $selector->setSearch($arguments['search']);
            //paging
            if (isset($_REQUEST['page']))
            {
                $page = max(array(1,$_REQUEST['page']));
                $page = min(array($page,$userList->getNumberOfPage()));
                
                $arguments['page'] = $page;
                
                $selector->setPageToDisplay($page);
            }
            $userList->setSelector($selector);
        }
    }
    
    if ($_REQUEST['cmd'] == 'exFromUser' && ! is_null($userId))
    {
        $claroline->display->body->appendContent(claro_html_tool_title(get_lang('Internal messaging')." - ".get_lang('Delete all user\'s messages')));
        $box = new AdminMessageBox();
        $box->deleteAllMessageFromUser($userId);
        $displayRemoveFromUserValidated = TRUE;
    }
    // delete older than
    if ($_REQUEST['cmd'] == 'rqOlderThan')
    {
        $claroline->display->body->appendContent(claro_html_tool_title(get_lang('Internal messaging')." - ".get_lang('Delete messages older than')));
        $displayRemoveOlderThanConfirmation = TRUE;
    }
    
    if ($_REQUEST['cmd'] == 'exOlderThan' && isset($_REQUEST['date']))
    {
        $claroline->display->body->appendContent(claro_html_tool_title(get_lang('Internal messaging')." - ".get_lang('Delete messages older than')));
        $box = new AdminMessageBox();
        
        list($day,$month,$year) = explode('/',$_REQUEST['date']);
        
        if (checkdate($month,$day,$year))
        {
            $box->deleteMessageOlderThan(strtotime($year.'-'.$month.'-'.$day));
            $displayRemoveOlderThanValidated = TRUE;
        }
        else
        {
            $dialogbox = new DialogBox();
            $dialogbox->info(get_lang('invalid date'));
            $content .= $dialogbox->render();
        }
        
    }
    
    // -------- delete platform message
    if ($_REQUEST['cmd'] == "rqPlatformMessage")
    {
        $claroline->display->body->appendContent(claro_html_tool_title(get_lang('Internal messaging')." - ".get_lang('Delete platform message')));
        $displayRemovePlatformMessageConfirmation = TRUE;
    }
    
    if ($_REQUEST['cmd'] == "exPlatformMessage")
    {
        $claroline->display->body->appendContent(claro_html_tool_title(get_lang('Internal messaging')." - ".get_lang('Delete platform message')));
        $box = new AdminMessageBox();
        $box->deletePlatformMessage();
        $displayRemovePlatformMessageValidated = TRUE;
    }
}
else
{
    claro_die("missing command");
}

// ----------- delete all --------------
if ($displayRemoveAllConfirmation)
{
    $dialogboxMsg = get_lang('Are you sure to delete to delete all messages?<br /><br />WARNING all data will be deleted from the database')
         . '<br /><br />'
         . '<a href="'.$_SERVER['PHP_SELF'].'?cmd=exDeleteAll">' . get_lang('Yes') . '</a> | <a href="admin.php">' . get_lang('No') .'</a>'
         ;
    $dialogbox = new DialogBox();
    $dialogbox->question($dialogboxMsg);
    $content .= '<br />'.$dialogbox->render();
}

if ($displayRemoveAllValidated)
{
    $dialogboxMsg = get_lang('All messages has been deleted')
         . '<br /><br />'
         . '<a href="admin.php">' . get_lang('Back') .'</a>'
         ;
    $dialogbox = new DialogBox();
    $dialogbox->info($dialogboxMsg);
    $content .= '<br />'.$dialogbox->render();
}

// ----------- end delete all

// --------- from user

if ($displayRemoveFromUserConfirmation)
{
    $userId = (int)$_REQUEST['userId'];
    
    $confirmation =
         get_lang('Are you sur to delete user\'s message?')
        .'<br /><br />'
        .'<a href="'.$_SERVER['PHP_SELF'].'?cmd=exFromUser&amp;userId='.$userId.'">'.get_lang('Yes').'</a> | <a href="admin.php">'.get_lang('No').'</a>' 
        ;
    $dialogbox = new DialogBox();
    $dialogbox->question($confirmation);
    $content .= $dialogbox->render();
}

if ($displayRemoveFromUserValidated)
{
    $dialogboxMsg = get_lang('All user\'s message hs been deleted')
         . '<br /><br />'
         . '<a href="admin.php">' . get_lang('Back') .'</a>'
         ;
    $dialogbox = new DialogBox();
    $dialogbox->info($dialogboxMsg);
    $content .= '<br />'.$dialogbox->render();
}

if ($displaySearchUser)
{
    if (isset($arguments['search']))
    {
        $search = $arguments['search'];
    }
    else
    {
        $search = "";
    }
    $form =
         '<form action="" method="post">'
        .get_lang('User').': <input type="text" name="search" value="'.$search.'" class="inputSearch" />'
        .'<input type="submit" value="'.get_lang('Search').'" />' 
        .'</form>'
        ;
        
    $dialogbox = new DialogBox();
    $dialogbox->form($form);
    
    $content .= $dialogbox->render();
    
}

if ($displayResultUserSearch)
{
    
    $arg_sorting = makeArgLink($arguments,array('fieldOrder','order'));  
    if ($arg_sorting == "")
    {
        $linkSorting = $_SERVER['PHP_SELF']."?fieldOrder=";
    }
    else
    {
        $linkSorting = $_SERVER['PHP_SELF']."?".$arg_sorting."&amp;fieldOrder=";
    }
    $arg_delete = makeArgLink($arguments);  
    if ($arg_sorting == "")
    {
        $linkDelete = $_SERVER['PHP_SELF']."?";
    }
    else
    {
        $linkDelete = $_SERVER['PHP_SELF']."?".$arg_delete."&amp;";
    }
    
    $content .= '<br />'
       .'<table class="claroTable emphaseLine">'."\n\n"
       .'<tr class="headerX">'."\n"
       .'<th>'.get_lang('Id').'</th>'."\n"
       .'<th><a href="'.$linkSorting.'name&amp;order='.$nextOrder.'">'.get_lang('Name').'</a></th>'."\n"
       .'<th><a href="'.$linkSorting.'username&amp;order='.$nextOrder.'">'.get_lang('Username').'</a></th>'."\n"
       .'<th>'.get_lang('action').'</th>'."\n"
       .'</tr>'."\n\n"
       ;

     if ( $userList->getNumberOfUser() > 0)
     {
         $javascriptDelete = '
            <script type="text/javascript">
            function deleteMessageFromUser ( localPath )
            {
                if (confirm("'.get_lang('Are you sure to delete the message from user').'"))
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
 
         foreach ($userList as $key => $user)
         {
             $content .=
                  '<tr>'."\n"
                 .'<td>'.$user['id'].'</td>'."\n"
                 .'<td>'.get_lang('%firstName %lastName', array ('%firstName' =>htmlspecialchars($user['firstname']), '%lastName' => htmlspecialchars($user['lastname']))).'</td>'."\n"
                 .'<td>'.$user['username'].'</td>'."\n"
                 .'<td><a href="'.$linkDelete.'cmd=rqFromUser&amp;userId='.$user['id'].'" '
                 .' onclick="return deleteMessageFromUser(\''.$linkDelete.'cmd=exFromUser&amp;userId='.$user['id'].'\')">'
                 .'delete messages</a></td>' ."\n"
                 .'</tr>'."\n\n"
                 ; 
         }
     }
     else
     {
         $content .=
              '<tr>'."\n"
             .'<td colspan="4">'.get_lang('Empty').'</td>' ."\n"
             .'</tr>'."\n\n"
             ; 
     }
     $content .=
        '</table>'
       ;
     if ($userList->getNumberOfPage() > 1)
     {
         $arg_paging = makeArgLink($arguments,array('page'));  
         if ($arg_paging == "")
         {
             $linkPaging = $_SERVER['PHP_SELF']."?page=";
         }
         else
         {
             $linkPaging = $_SERVER['PHP_SELF']."?".$arg_paging."&amp;page=";
         }
         
         $content .= getPager($linkPaging,$arguments['page'],$userList->getNumberOfPage());
     }
      
}
//----------- end from user

//--------------- older than
if ($displayRemoveOlderThanConfirmation)
{
    
    $date = isset($_REQUEST['date']) ? $_REQUEST['date'] : NULL;
    
    
    if (is_null($date))
    {
        $CssLoader = CssLoader::getInstance();
        $CssLoader->load('ui.datepicker');
        
        $JsLoader = JavascriptLoader::getInstance();
        $JsLoader->load('jquery');
        $JsLoader->load('ui.datepicker');
        
        $javascript = '
            <script type="text/javascript" charset="utf-8">
                jQuery(function($){
                    $("#dateinput").datepicker({dateFormat: \'dd/mm/yy\'});
                });
            </script>';
        $claroline->display->header->addHtmlHeader($javascript);
            
        $disp = '
                Select a date:<br />'
                . '<form action="'.$_SERVER['PHP_SELF'].'?cmd=rqOlderThan" method="post">'
                . '<input type="text" name="date" value="'.date('d/m/Y').'" id="dateinput" /> '.get_lang('(JJ/MM/AAAA)').'<br />'
                . '<input type="submit" value="delete" />'
                . '</form>'
                ;
        $dialogbox = new DialogBox();
        $dialogbox->form($disp);
        
        $content .= $dialogbox->render();
    }
    else
    {
        $javascriptDelete = '
            <script type="text/javascript">
            if (confirm("'.get_lang('Are you sure to delete to delete the messages older than %date%?\n\n                         Warning all data will be deleted from the database',
                        array('%date%'=>$date)).'"));
            {
                window.location=\''.$_SERVER['PHP_SELF'].'?cmd=exOlderThan&amp;date='.urlencode($date).'\';
            }
            else
            {
                window.location=\'admin.php\';
            }
            </script>';
        $claroline->display->header->addHtmlHeader($javascriptDelete);
        
        $dialogboxMsg = get_lang('Are you sure to delete to delete the messages older than %date%?<br /><br />
                         Warning all data will be deleted from the database',
                        array('%date%'=>$date))
             . '<br /><br />'
             . '<a href="'.$_SERVER['PHP_SELF'].'?cmd=exOlderThan&amp;date='.urlencode($_REQUEST['date']).'">' . get_lang('Yes') . '</a> | <a href="admin.php">' . get_lang('No') .'</a>'
             ;
        $dialogbox = new DialogBox();
        $dialogbox->question($dialogboxMsg);
        $content .= '<br />'.$dialogbox->render();
    }
}

if ($displayRemoveOlderThanValidated)
{
    $date = htmlspecialchars($_REQUEST['date']);
    $dialogboxMsg = get_lang('All messages older than %date% has been deleted',array('%date%' => $date))
         . '<br /><br />'
         . '<a href="admin.php">' . get_lang('Back') .'</a>'
         ;
    $dialogbox = new DialogBox();
    $dialogbox->info($dialogboxMsg);
    $content .= '<br />'.$dialogbox->render();
}
// --------------- end older than

// ------------ platform message

if ($displayRemovePlatformMessageConfirmation)
{    
    $dialogboxMsg = get_lang('Are you sure to delete to delete all palteform messages?<br /><br />WARNING all data will be deleted from the database')
         . '<br /><br />'
         . '<a href="'.$_SERVER['PHP_SELF'].'?cmd=exPlatformMessage">' . get_lang('Yes') . '</a> | <a href="admin.php">' . get_lang('No') .'</a>'
         ;
    $dialogbox = new DialogBox();
    $dialogbox->question($dialogboxMsg);
    $content .= '<br />'.$dialogbox->render();
}

if ($displayRemovePlatformMessageValidated)
{
    $dialogboxMsg = get_lang('All platform messages has been deleted')
         . '<br /><br />'
         . '<a href="admin.php">' . get_lang('Back') .'</a>'
         ;
    $dialogbox = new DialogBox();
    $dialogbox->info($dialogboxMsg);
    $content .= '<br />'.$dialogbox->render();
}

// ------------- end platform message

// ------------------- render ----------------------------
$claroline->display->banner->breadcrumbs->append(get_lang('Administration'),get_path('rootAdminWeb'));
$claroline->display->banner->breadcrumbs->append(get_lang('Messages'),'admin.php');

$claroline->display->body->appendContent($content);

echo $claroline->display->render();

?>