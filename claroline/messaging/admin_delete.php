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

$displayRemovePlateformMessageConfirmation = FALSE;
$displayRemovePlateformMessageValidated = FALSE;

//use only the search of user
$arguments = array();

$acceptedCommand = array('rqDeleteAll','exDeleteAll'
                        ,'rqFromUser','exFromUser'
                        ,'rqOlderThan','exOlderThan'
                        ,'rqPlateformMessage','exPlateformMessage');

// ------------- display
if (isset($_REQUEST['cmd']) && in_array($_REQUEST['cmd'],$acceptedCommand))
{
    // -------- delete all
    if ($_REQUEST['cmd'] == "rqDeleteAll")
    {
        $displayRemoveAllConfirmation = TRUE;
    }
    
    if ($_REQUEST['cmd'] == "exDeleteAll")
    {
        $box = new AdminMessageBox();
        $box->deleteAllMessages();
        $displayRemoveAllValidated = TRUE;
    }
    
    // -----------delete from user
    if ($_REQUEST['cmd'] == 'rqFromUser')
    {
        $arguments['cmd'] = 'rqFromUser';
        if(isset($_REQUEST['userId']))
        {
            $displayRemoveFromUserConfirmation = TRUE;
        }
        else
        {
            $displaySearchUser = TRUE;
        }
        
        if(isset($_REQUEST['search']) && $_REQUEST['search'] != "")
        {
            $displayResultUserSearch = TRUE;
            $arguments['search'] = $_REQUEST['search'];
                        
            $userList = new UserList();
            $selector = $userList->getSelector();
            
            //order
            if (isset($_REQUEST['order']) && ($_REQUEST['order'] == 'asc' || $_REQUEST['order'] == 'desc'))
            {
                $arguments['order'] = $_REQUEST['order'];
                
                if ($_REQUEST['order'] == 'asc')
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
            if (isset($_REQUEST['fieldOrder']) && 
                ($_REQUEST['fieldOrder'] == 'name' || $_REQUEST['fieldOrder'] == 'username'))
            {
                $arguments['fieldOrder'] = $_REQUEST['fieldOrder'];
                
                if ($_REQUEST['fieldOrder'] == 'name')
                {
                    $selector->setFieldOrder(UserStrategy::ORDER_BY_NAME);
                }
                else
                {
                    $selector->setFieldOrder(UserStrategy::ORDER_BY_USERNAME);
                }
            }
            //namesearch
            $selector->setSearch($_REQUEST['search']);
            //paging
            if(isset($_REQUEST['page']))
            {
                $page = max(array(1,$_REQUEST['page']));
                $page = min(array($page,$userList->getNumberOfPage()));
                
                $arguments['page'] = $page;
                
                $selector->setPageToDisplay($page);
            }
            $userList->setSelector($selector);
        }
    }
    
    if ($_REQUEST['cmd'] == 'exFromUser' && isset($_REQUEST['userId']))
    {
        $box = new AdminMessageBox();
        $box->deleteAllMessageFromUser($_REQUEST['userId']);
        $displayRemoveFromUserValidated = TRUE;
    }
    // delete older than
    if ($_REQUEST['cmd'] == 'rqOlderThan')
    {
        $displayRemoveOlderThanConfirmation = TRUE;
    }
    
    if ($_REQUEST['cmd'] == 'exOlderThan' && isset($_REQUEST['date']))
    {
        $box = new AdminMessageBox();
        $box->deleteMessageOlderThan(strtotime(substr($_REQUEST['date'],6,4).'-'.substr($_REQUEST['date'],3,2).'-'.substr($_REQUEST['date'],0,2)));
        $displayRemoveOlderThanValidated = TRUE;
    }
    
    // -------- delete plateform message
    if ($_REQUEST['cmd'] == "rqPlateformMessage")
    {
        $displayRemovePlateformMessageConfirmation = TRUE;
    }
    
    if ($_REQUEST['cmd'] == "exPlateformMessage")
    {
        $box = new AdminMessageBox();
        $box->deletePlateformMessage();
        $displayRemovePlateformMessageValidated = TRUE;
    }
}
else
{
    claro_die("missing command");
}

// ----------- delete all --------------
if ($displayRemoveAllConfirmation)
{
    $javascriptDelete = '
        <script type="text/javascript">
        if (confirm("'.get_lang('Are you sure to delete to delete all messages?\n\nWarning all data will be deleted from the database').'"))
        {
            window.location=\''.$_SERVER['PHP_SELF'].'?cmd=exDeleteAll'.'\';
        }
        else
        {
            window.location=\'admin.php\';
        }
        </script>';
    $claroline->display->header->addHtmlHeader($javascriptDelete);
    
    $dialBoxMsg = get_lang('Are you sure to delete to delete all messages?<br /><br />WARNING all data will be deleted from the database')
         . '<br /><br />'
         . '<a href="'.$_SERVER['PHP_SELF'].'?cmd=exDeleteAll">' . get_lang('Yes') . '</a> | <a href="admin.php">' . get_lang('No') .'</a>'
         ;
    $dialbox = new DialogBox();
    $dialbox->question($dialBoxMsg);
    $content .= '<br />'.$dialbox->render();
}

if ($displayRemoveAllValidated)
{
    $dialBoxMsg = get_lang('All messages has been deleted')
         . '<br /><br />'
         . '<a href="admin.php">' . get_lang('Back') .'</a>'
         ;
    $dialbox = new DialogBox();
    $dialbox->info($dialBoxMsg);
    $content .= '<br />'.$dialbox->render();
}

// ----------- end delete all

// --------- from user

if ($displayRemoveFromUserConfirmation)
{
    $confirmation =
         get_lang('Are you sur to delete user\'s message?')
        .'<br /><br />'
        .'' 
        ;
    $dialbox = new DialogBox();
    $dialbox->question($confirmation);
    $content .= $dialbox->render();
}

if ($displayRemoveFromUserValidated)
{
    
}

if($displaySearchUser)
{
    if(isset($_REQUEST['search']))
    {
        $search = $_REQUEST['search']; 
    }
    else
    {
        $search = "";
    }
    $form =
         '<form action="" method="post">'
        .get_lang('User').': <input type="text" name="search" value="'.$search.'" class="inputSearch" />'
        .'<input type="submit" value="'.get_lang('Search').'">' 
        .'</form>'
        ;
        
    $dialbox = new DialogBox();
    $dialbox->form($form);
    
    $content .= $dialbox->render();
    
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
        $linkDelete = $_SERVER['PHP_SELF']."?userId=";
    }
    else
    {
        $linkDelete = $_SERVER['PHP_SELF']."?".$arg_delete."&amp;userId=";
    }
    
    $content .= '<br />'
       .'<table class="claroTable emphaseLine">'
       .'<tr class="headerX">'
       .'<th>'.get_lang('Id').'</td>'
       .'<th><a href="'.$linkSorting.'name&amp;order='.$nextOrder.'">'.get_lang('Name').'</a></td>'
       .'<th><a href="'.$linkSorting.'username&amp;order='.$nextOrder.'">'.get_lang('Username').'</a></td>'
       .'<th>'.get_lang('action').'</td>'
       .'</tr>' 
       ;
     foreach ($userList as $key => $user)
     {
         $content .=
              '<tr>'
             .'<td>'.$user['id'].'</td>'
             .'<td>'.$user['lastname'].' '.$user['firstname'].'</td>'
             .'<td>'.$user['username'].'</td>'
             .'<td><a href="'.$linkDelete.$user['id'].'">delete messages</a></td>' 
             .'</tr>'
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
    if(!isset($_REQUEST['date']))
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
        	. '<input type="text" name="date" value="'.date('d/m/Y').'" id="dateinput" /><br />'
        	. '<input type="submit" value="delete" />'
        	. '</form>'
        	;
        $dialbox = new DialogBox();
        $dialbox->form($disp);
        
        $content .= $dialbox->render();
    }
    else
    {
        $javascriptDelete = '
            <script type="text/javascript">
            if (confirm("'.get_lang('Are you sure to delete to delete the messages older than %date%?\n\n            			 Warning all data will be deleted from the database',
                        array('%date%'=>$_REQUEST['date'])).'"));
            {
                window.location=\''.$_SERVER['PHP_SELF'].'?cmd=exOlderThan&amp;date='.urlencode($_REQUEST['date']).'\';
            }
            else
            {
                window.location=\'admin.php\';
            }
            </script>';
        $claroline->display->header->addHtmlHeader($javascriptDelete);
        
        $dialBoxMsg = get_lang('Are you sure to delete to delete the messages older than %date%?<br /><br />
            			 Warning all data will be deleted from the database',
                        array('%date%'=>$_REQUEST['date']))
             . '<br /><br />'
             . '<a href="'.$_SERVER['PHP_SELF'].'?cmd=exOlderThan&amp;date='.urlencode($_REQUEST['date']).'">' . get_lang('Yes') . '</a> | <a href="admin.php">' . get_lang('No') .'</a>'
             ;
        $dialbox = new DialogBox();
        $dialbox->question($dialBoxMsg);
        $content .= '<br />'.$dialbox->render();
    }
}

if ($displayRemoveOlderThanValidated)
{
    $dialBoxMsg = get_lang('All messages older than %date% has been deleted',array('%date%' => $_REQUEST['date']))
         . '<br /><br />'
         . '<a href="admin.php">' . get_lang('Back') .'</a>'
         ;
    $dialbox = new DialogBox();
    $dialbox->info($dialBoxMsg);
    $content .= '<br />'.$dialbox->render();
}
// --------------- end older than

// ------------ plateform message

if ($displayRemovePlateformMessageConfirmation)
{
    $javascriptDelete = '
        <script type="text/javascript">
        if (confirm("'.get_lang('Are you sure to delete to delete all palteform messages?\n\nWarning all data will be deleted from the database').'"))
        {
            window.location=\''.$_SERVER['PHP_SELF'].'?cmd=exPlateformMessage'.'\';
        }
        else
        {
            window.location=\'admin.php\';
        }
        </script>';
    $claroline->display->header->addHtmlHeader($javascriptDelete);
    
    $dialBoxMsg = get_lang('Are you sure to delete to delete all palteform messages?<br /><br />WARNING all data will be deleted from the database')
         . '<br /><br />'
         . '<a href="'.$_SERVER['PHP_SELF'].'?cmd=exPlateformMessage">' . get_lang('Yes') . '</a> | <a href="admin.php">' . get_lang('No') .'</a>'
         ;
    $dialbox = new DialogBox();
    $dialbox->question($dialBoxMsg);
    $content .= '<br />'.$dialbox->render();
}

if ($displayRemovePlateformMessageValidated)
{
    $dialBoxMsg = get_lang('All plateform messages has been deleted')
         . '<br /><br />'
         . '<a href="admin.php">' . get_lang('Back') .'</a>'
         ;
    $dialbox = new DialogBox();
    $dialbox->info($dialBoxMsg);
    $content .= '<br />'.$dialbox->render();
}

// ------------- end plateform message

// ------------------- render ----------------------------
$claroline->display->banner->breadcrumbs->append(get_lang('My messages'),'index.php');
$claroline->display->banner->breadcrumbs->append(get_lang('Administration'),'admin.php');

$claroline->display->body->appendContent(claro_html_tool_title(get_lang('Internal messaging')." - ".get_lang('administration')));
$claroline->display->body->appendContent($content);

echo $claroline->display->render();

?>