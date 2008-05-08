<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * page of search for administrator
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

// search user info
require_once get_path('incRepositorySys') . '/lib/user.lib.php';

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
$arguments = array();

$displayTable = TRUE;

$acceptedSearch = array('fromUser','olderThan','timeInterval','plateformMessage');
$acceptedCommand = array('rqDeleteSelection','exDeleteSelection');

$box = new AdminMessageBox();
$strategy = $box->getSelector();


if (isset($_REQUEST['cmd']) && in_array($_REQUEST['cmd'],$acceptedCommand))
{
    
    $cmd = $_REQUEST['cmd'];
    
    if ($cmd == "exDeleteSelection" && isset($_REQUEST['msg']) 
            && is_array($_REQUEST['msg']))
    {
        
        $box->deleteMessageList($_REQUEST['msg']);
    }
    
    if ($cmd == "rqDeleteSelection" && isset($_REQUEST['msg']) 
            && is_array($_REQUEST['msg']))
    {
        
        $form =    get_lang('Are you sure to delete selected message?') 
        		 .	'<form action="" method="post">'
        		 .  '<input type="hidden" name="cmd" value="exDeleteSelection" />'
        		 ;
        foreach ( $_REQUEST['msg'] as $count => $idMessage )
        {
            $form .= '<input type="hidden" name="msg[]" value="'.$idMessage.'" />';
        }
		$form .= '<input type="submit" value="'.get_lang('Yes').'" /> '
				 .  '<a href=""><input type="button" value="'.get_lang('No').'" /></a>'        		 
                 .  '</form>'
        		 ;
        
        $dialbox = new DialogBox();
        $dialbox->form($form);
        
        $content .= $dialbox->render();
    }
}

// ---------------- order

if (isset($_REQUEST['order']))
{
    if ($_REQUEST['order'] == 'asc')
    {
        $strategy->setOrder(AdminBoxStrategy::ORDER_ASC);
        $arguments['order'] = $_REQUEST['order'];
        $nextOrder = 'desc';
    }
    elseif ($_REQUEST['order'] == 'desc')
    {
        $strategy->setOrder(AdminBoxStrategy::ORDER_DESC);
        $arguments['order'] = $_REQUEST['order'];
        $nextOrder = 'asc';
    }
    else
    {
        $nextOrder = 'asc';
    }
}
else
{
    $nextOrder = 'asc';
}

if (isset($_REQUEST['fieldOrder']))
{
    if ($_REQUEST['fieldOrder'] == 'name')
    {
        $strategy->setFieldOrder(AdminBoxStrategy::FIELD_ORDER_NAME);
    }
    elseif ($_REQUEST['fieldOrder'] == 'username')
    {
        $strategy->setFieldOrder(AdminBoxStrategy::FIELD_ORDER_USERNAME);
    }
    elseif ($_REQUEST['fieldOrder'] == 'date')
    {
        $strategy->setFieldOrder(AdminBoxStrategy::FIELD_ORDER_DATE);
    }
    else
    {
        //nothing to do
    }
}


if (isset($_REQUEST['search']) && in_array($_REQUEST['search'],$acceptedSearch))
{
    $arguments['search'] = $_REQUEST['search'];
    
    if ($_REQUEST['search'] == 'fromUser')
    {
        $title = get_lang("User's messages");
        if (!isset($_REQUEST['name']) || $_REQUEST['name'] == "")
        {
            $displayTable = FALSE;
        }
        else
        {
            $arguments['name'] = trim($_REQUEST['name']);
            $strategy->setStrategy(AdminBoxStrategy::SENT_BY , array('name' => $arguments['name']));
        }
    }

    if ($_REQUEST['search'] == 'olderThan')
    {
        $title = get_lang("Messages older than");
        if (!isset($_REQUEST['date']))
        {
            $displayTable = FALSE;
        }
        else
        {
            $arguments['date'] = $_REQUEST['date'];
            $strategy->setStrategy(AdminBoxStrategy::OLDER_THAN , array('date' => strtotime(substr($_REQUEST['date'],6,4).'-'.substr($_REQUEST['date'],3,2).'-'.substr($_REQUEST['date'],0,2))));
        }
    }
    
    if ($_REQUEST['search'] == 'timeInterval')
    {
        $title = get_lang("Message dating");
        if (!isset($_REQUEST['date1']) || !isset($_REQUEST['date2']))
        {
            $displayTable = FALSE;
        }
        else
        {
            $arguments['date1'] = $_REQUEST['date1'];
            $arguments['date2'] = $_REQUEST['date2'];
            $strategy->setStrategy(AdminBoxStrategy::DATED_INTERVAL , 
                array('date1' => strtotime(substr($_REQUEST['date1'],6,4).'-'.substr($_REQUEST['date1'],3,2).'-'.substr($_REQUEST['date1'],0,2))
                    ,'date2' => strtotime(substr($_REQUEST['date2'],6,4).'-'.substr($_REQUEST['date2'],3,2).'-'.substr($_REQUEST['date2'],0,2))));
        }
    }

    if ($_REQUEST['search'] == 'plateformMessage')
    {
        $title = get_lang("Plateforme messages");
        $strategy->setStrategy(AdminBoxStrategy::PLATFORM_MESSAGE);
    }    
}
else
{
    claro_die("missing search");
}

// ---------- paging
if (isset($_REQUEST['page']))
{
    $page = min(array($_REQUEST['page'],$box->getNumberOfPage()));
    $page = max(array($page,1));
    $strategy->setPageToDisplay($page);
    $arguments['page'] = $page;
    
}


// ------------- display

if ($_REQUEST['search'] == 'fromUser')
{
    if (isset($_REQUEST['name']))
    {
        $name = $_REQUEST['name'];
        
    }
    else
    {
        $name = "";
    }
    
    $searchForm = 
        '<form action="'.$_SERVER['PHP_SELF'].'?search=fromUser" method="post">'."\n"
       .'Name: <input type="text" name="name" value="'.$name.'"/>'."\n"
       .'<input type="submit" value="'.get_lang("Search").'" />'."\n"
       .'</form>'."\n"
       ;
    $dialbox = new DialogBox();
    $dialbox->form($searchForm);
    
    $content .= "<br />".$dialbox->render();
}

if ($_REQUEST['search'] == 'olderThan')
{
    if (isset($_REQUEST['date']))
    {
        $date = $_REQUEST['date'];
        
    }
    else
    {
        $date = date('d/m/Y');
    }
    
    $CssLoader = CssLoader::getInstance();
    $CssLoader->load('ui.datepicker');
    
    $JsLoader = JavascriptLoader::getInstance();
    $JsLoader->load('jquery');
    $JsLoader->load('ui.datepicker');
    
    $javascript .= '
    	<script type="text/javascript" charset="utf-8">
			jQuery(function($){
				$("#dateinput").datepicker({dateFormat: \'dd/mm/yy\'});
			});
		</script>';
    $claroline->display->header->addHtmlHeader($javascript);   
    $disp = '
    	Select a date:<br />'
    	. '<form action="'.$_SERVER['PHP_SELF'].'?search=olderThan" method="post">'
    	. '<input type="text" name="date" value="'.$date.'" id="dateinput" /><br />'
    	. '<input type="submit" value="'.get_lang('search').'" />'
    	. '</form>'
    	;
    $dialbox = new DialogBox();
    $dialbox->form($disp);
    
    $content .= $dialbox->render();
}

if ($_REQUEST['search'] == 'timeInterval')
{
    if (isset($_REQUEST['date1']) && isset($_REQUEST['date2']))
    {
        $date1 = $_REQUEST['date1'];
        $date2 = $_REQUEST['date2'];
    }
    else
    {
        $date1 = date('d/m/Y');
        $date2 = date('d/m/Y');
    }
    
    $CssLoader = CssLoader::getInstance();
    $CssLoader->load('ui.datepicker');
    
    $JsLoader = JavascriptLoader::getInstance();
    $JsLoader->load('jquery');
    $JsLoader->load('ui.datepicker');
    
    $javascript = '
    	<script type="text/javascript" charset="utf-8">
			jQuery(function($){
				$("#dateinput1").datepicker({dateFormat: \'dd/mm/yy\'});
			});
			jQuery(function($){
				$("#dateinput2").datepicker({dateFormat: \'dd/mm/yy\'});
			});
		</script>';
    $claroline->display->header->addHtmlHeader($javascript);    
    $disp = '
    	Select a interval:<br />'
    	. '<form action="'.$_SERVER['PHP_SELF'].'?search=timeInterval" method="post">'
    	. get_lang('begin date').': <input type="text" name="date1" value="'.$date1.'" id="dateinput1" /><br />'
    	. get_lang('end date').': <input type="text" name="date2" value="'.$date2.'" id="dateinput2" /><br />'
    	. '<input type="submit" value="'.get_lang('search').'" />'
    	. '</form>'
    	;
    $dialbox = new DialogBox();
    $dialbox->form($disp);
    
    $content .= $dialbox->render();
}


if ($displayTable)
{
    $argLink = makeArgLink($arguments,array('fieldOrder','order'));
    $orderLink = $_SERVER['PHP_SELF'].'?'.$argLink;
    
    if($argLink != "")
    {
        $orderLink .= "&amp;";
    }
    $orderLink .= "order=".$nextOrder."&amp;";
    
    $JsLoader = JavascriptLoader::getInstance();
    $JsLoader->load('jquery');
    $javascriptDelete = '
    <script type="text/javascript">
        function countCheckedBox()
        {
           var counter = 0;
        
           $("input[@type=checkbox][@checked]").each( function() {
               counter++;
           });
        
           return counter;
        }
        
        function deleteSelection ()
        {
           if ( ! countCheckedBox() )
           {
               alert("No document selected !");
               return false;
           }
        
           if (confirm(" Are you sure to delete the selected message ?"))
           {
               $("input[@name=cmd]").val("exDeleteSelection");
               return true;
           }
           else
           {
               return false;
           }
        }
    </script>';
    $claroline->display->header->addHtmlHeader($javascriptDelete);
    $argDeleteSelection = makeArgLink($arguments,array('cmd'));
    $content .= '<form action="'.$_SERVER['PHP_SELF'].'?'.$argDeleteSelection.'" method="post"
    			onsubmit="return deleteSelection(this)">'
    		. '<input type="hidden" name="cmd" value="rqDeleteSelection" />'
    		;
    $content .= "<br />"
       .'<table class="claroTable emphaseLine" width="100%">'."\n"
       .'	<tr class ="headerX">'."\n"
       .'		<th>&nbsp;</th>'."\n"
       .'		<th>'.get_lang('Subject').'</th>'."\n"
       .'		<th><a href="'.$orderLink.'fieldOrder=name">'.get_lang('Sender').'</a></th>'."\n"
       .'		<th><a href="'.$orderLink.'fieldOrder=username">'.get_lang('Username').'</a></th>'."\n"
       .'		<th><a href="'.$orderLink.'fieldOrder=date">'.get_lang('Date').'</a></th>'."\n"
       .'		<th class="im_list_action">'.get_lang('Delete').'</th>'."\n"
       .'	</tr>'
       ;
    if ($box->getNumberOfMessage() == 0)
    {
        $content .= 
            '<tr>'
           .'	<td colspan="6">'.get_lang('There is no message corresponding on your request').'</td>'
           .'</tr>' 
           ;
    }
    else 
    {
        foreach ($box as $key => $message)
        {
            $userData = user_get_properties($message->getSender());
            
            $content .= 
                '<tr>'
               .'<td class="im_list_selection"><input type="checkbox" name="msg[]" value="'.$message->getId().'" /></td>'
               .'<td>'.htmlspecialchars($message->getSubject()).'</td>'
               .'<td>'.htmlspecialchars($message->getSenderLastName().' '.$message->getSenderFirstName()).'</td>'
               .'<td>'.htmlspecialchars($userData['username']).'</td>'
               .'<td>'.claro_html_localised_date(get_locale('dateTimeFormatLong'),strtotime($message->getSendTime())).'</td>'
               .'<td class="im_list_action"><img src="'.get_icon('delete.gif').'" alt="" /></td>'
			   .'</tr>'
			   ;
	        $count++;
       }
       $content .= '<tr><td colspan="6"><input type="submit" value="'.get_lang('Delete message selected').'" /></td></tr>';
   }
   $content .= '</table>';
   $content .= '</form>';
    // prepare the link to change of page
    if ($box->getNumberOfPage()>1)
    {
        // number of page to display in the page before and after thecurrent page
        $nbPageToDisplayBeforeAndAfterCurrentPage = 1;        
        
        $content .= '<div id="im_paging">';
        
        $arg_paging = makeArgLink($arguments,array('page'));  
        if ($arg_paging == "")
        {
            $linkPaging = $linkPage."?page=";
        }
        else
        {
            $linkPaging = $linkPage."?".$arg_paging."&amp;page=";
        }
        
        if(!isset($arguments['page']))
        {
            $page=1;
        }
        else
        {
            $page = $arguments['page'];
        }
        $content .= getPager($linkPaging,$page,$box->getNumberOfPage());
    }
}

// ------------------- render ----------------------------
$claroline->display->banner->breadcrumbs->append(get_lang('My messages'),'index.php');
$claroline->display->banner->breadcrumbs->append(get_lang('Administration'),'admin.php');
$claroline->display->banner->breadcrumbs->append(get_lang('Search messages'),'admin_search.php?search='.$arguments['search']);

$claroline->display->body->appendContent(claro_html_tool_title($title));
$claroline->display->body->appendContent($content);

echo $claroline->display->render();

?>