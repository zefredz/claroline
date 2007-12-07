<?php //$Id$
/**
 * CLAROLINE
 *
 * this tool manage the
 *
 * @version 1.8 $Revision$
 *
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @author Claro Team <cvs@claroline.net>
 * @author  Guillaume Lederer <lederer@cerdecam.be>
 */
// initialisation of global variables and used libraries

require '../inc/claro_init_global.inc.php';

require_once get_path('incRepositorySys') . '/lib/pager.lib.php';
require_once get_path('incRepositorySys') . '/lib/user.lib.php';
require_once get_path('incRepositorySys') . '/lib/class.lib.php';
require_once get_path('incRepositorySys') . '/lib/admin.lib.inc.php';
require_once get_path('incRepositorySys') . '/lib/user.lib.php';

// Security check
if ( ! claro_is_user_authenticated() ) claro_disp_auth_form();
if ( ! claro_is_platform_admin() ) claro_die(get_lang('Not allowed'));

$userPerPage = 20; // numbers of user to display on the same page

/*
 * DB tables definition
 */
$tbl_mdb_names  = claro_sql_get_main_tbl();
$tbl_user       = $tbl_mdb_names['user'];
$tbl_class      = $tbl_mdb_names['user_category'];
$tbl_class_user = $tbl_mdb_names['user_rel_profile_category'];

// Main section

$cmd = isset($_REQUEST['cmd'])?$_REQUEST['cmd']:null;
$user_id = isset($_REQUEST['user_id'])?(int)$_REQUEST['user_id']:0;
$class_id = isset($_REQUEST['class_id'])?(int)$_REQUEST['class_id']:0;
$searchFor = isset($_REQUEST['searchFor'])?$_REQUEST['searchFor']:'';

// find info about the class

if ( ($classinfo = class_get_properties ($class_id)) === false )
{
    $class_id = 0;
}

if ( !empty($class_id) )
{
    switch ( $cmd )
    {
        case 'subscribe' :
            if ( user_add_to_class($user_id,$class_id) )
            {
                $dialogBox = get_lang('User has been sucessfully registered to the class');
            }
            break;

        case 'unsubscribe' :
            if ( user_remove_to_class($user_id,$class_id) )
            {
                $dialogBox = get_lang('User has been sucessfully unregistered from the class');
            }
            break;
    }

    //----------------------------------
    // Build query and find info in db
    //----------------------------------

    $sql = "SELECT *, U.`user_id`
            FROM  `" . $tbl_user . "` AS U
            LEFT JOIN `" . $tbl_class_user . "` AS CU
                   ON  CU.`user_id` = U.`user_id`
                  AND CU.`class_id` = " . (int) $class_id;

    if ( !empty($searchFor) )
    {
       $sql .= ' WHERE U.`nom` LIKE(\''.claro_sql_escape($searchFor).'%\')';
    }

    // deal with REORDER

    // See SESSION variables used for reorder criteria :

    if (isset($_REQUEST['dir'])) $_SESSION['admin_class_reg_user_order_crit'] = ($_REQUEST['dir']=='DESC'?'DESC':'ASC');
    else                         $_REQUEST['dir'] = 'ASC';

    if (isset($_REQUEST['order_crit']))
    {
        $_SESSION['admin_class_reg_user_order_crit'] = $_REQUEST['order_crit'];
        if ($_REQUEST['order_crit']=="user_id")
        {
            $_SESSION['admin_class_reg_user_order_crit'] = "U`.`user_id";
        }
    }
    else
    {
       $_SESSION['admin_class_reg_user_order_crit'] = 'nom';
       $_SESSION['admin_class_reg_user_dir'] = 'ASC';
    }

    // first if direction must be changed

    if (isset($_REQUEST['chdir']) && ($_REQUEST['chdir']=="yes"))
    {
      if ($_SESSION['admin_class_reg_user_dir'] == "ASC") {$_SESSION['admin_class_reg_user_dir']="DESC";}
      elseif ($_SESSION['admin_class_reg_user_dir'] == "DESC") {$_SESSION['admin_class_reg_user_dir']="ASC";}
    }
    elseif (!isset($_SESSION['admin_class_reg_user_dir']))
    {
        $_SESSION['admin_class_reg_user_dir'] = 'DESC';
    }

    if (isset($_SESSION['admin_class_reg_user_order_crit']))
    {
        if ($_SESSION['admin_class_reg_user_order_crit']=="user_id")
        {
            $toAdd = " ORDER BY CU.`user_id` ".$_SESSION['admin_class_reg_user_dir'];
        }
        else
        {
            $toAdd = " ORDER BY `".$_SESSION['admin_class_reg_user_order_crit']."` ".$_SESSION['admin_class_reg_user_dir'];
        }
        $sql.=$toAdd;
    }

    //Build pager with SQL request

    if (!isset($_REQUEST['offset'])) $offset = '0';
    else                             $offset = $_REQUEST['offset'];

    $myPager = new claro_sql_pager($sql, $offset, $userPerPage);
    $resultList = $myPager->get_result_list();
}

//------------------------------------
// DISPLAY
//------------------------------------

// Deal with interbredcrumps

$interbredcrump[]= array ('url' => get_path('rootAdminWeb'), 'name' => get_lang('Administration'));
$interbredcrump[]= array ('url' => get_path('rootAdminWeb') . 'admin_class.php', 'name' => get_lang('Classes'));
$interbredcrump[]    = array ('url' => get_path('rootAdminWeb') . 'admin_class_user.php?class_id='.$class_id, 'name' => get_lang('Class users'));
$nameTools = get_lang('Register user to class');

// Header
include get_path('incRepositorySys') . '/claro_init_header.inc.php';

if ( empty($class_id) )
{
    echo claro_html_message_box(get_lang('Class not found'));
}
else
{
    // Display tool title

    echo claro_html_tool_title($nameTools . ' : ' . $classinfo['name']);
    
    // Display Forms or dialog box(if needed)
    
    if (isset($dialogBox)) echo claro_html_message_box($dialogBox);
    
    // Display tool link

    echo '<p><a class="claroCmd" href="' . get_path('clarolineRepositoryWeb').'admin/admin_class_user.php?class_id='.$class_id.'">'. 
         get_lang('Class members').'</a></p>'."\n";

    if (isset($_REQUEST['cfrom']) && ($_REQUEST['cfrom']=='clist')) echo claro_html_button('admincourses.php', get_lang('Back to course list'));

    // Display search form
    echo '<div align="center">'."\n"
    .   '<form action="'.$_SERVER['PHP_SELF'].'" method="GET">'."\n"
    .   get_lang('Name') . ' : <input type="text" name="searchFor" size="20" value="' . htmlspecialchars($searchFor) . '"/>'."\n"
    .   '<input type="hidden" name="class_id" value="' . $class_id . '"/>'."\n"
    .   '<input type="submit" value="'.get_lang('Search').'"/>'."\n"
    .   '</form>'."\n"
    .   '</div>'."\n";

    // Display pager

    echo $myPager->disp_pager_tool_bar($_SERVER['PHP_SELF'].'?class_id='.$class_id . ($searchFor?'&amp;searchFor='.urlencode($searchFor):''));

    // Display list of users
    // start table...

    echo '<table class="claroTable emphaseLine" width="100%" border="0" cellspacing="2">' . "\n"
    .    '<thead>' . "\n"
    .    '<tr class="headerX" align="center" valign="top">'
    .    '<th><a href="' . $_SERVER['PHP_SELF'] . '?class_id='.$class_id.'&amp;order_crit=user_id&amp;chdir=yes">' . get_lang('User Id') . '</a></th>' . "\n"
    .    '<th><a href="' . $_SERVER['PHP_SELF'] . '?class_id='.$class_id.'&amp;order_crit=nom&amp;chdir=yes"    >' . get_lang('Last name') . '</a></th>' . "\n"
    .    '<th><a href="' . $_SERVER['PHP_SELF'] . '?class_id='.$class_id.'&amp;order_crit=prenom&amp;chdir=yes" >' . get_lang('First name') . '</a></th>' . "\n"
    .    '<th>' . get_lang('Register to the class') . '</th>'
    .    '<th>' . get_lang('Unregister from class') . '</th>'
    .    '</tr>' . "\n"
    .    '</thead>' . "\n"
    .    '<tbody>' . "\n"
    ;

    // Start the list of users...

    foreach ( $resultList as $list )
    {
         echo '<tr>'
         .    '<td align="center">'
         .    '<a name="u' . $list['user_id'] . '"></a>' // no label in the a it's a target.
         .    $list['user_id'] . '</td>' . "\n"
         .    '<td align="left">' . $list['nom']    . '</td>' . "\n"
         .    '<td align="left">' . $list['prenom'] . '</td>' . "\n"
         ;
         // Register

         if ($list['id']==null)
         {
             echo '<td align="center">' . "\n"
             .    '<a href="' . $_SERVER['PHP_SELF'] . '?class_id=' . $class_id . '&amp;cmd=subscribe&user_id=' . $list['user_id'].'&amp;offset=' . $offset . '#u' . $list['user_id'] . '">' . "\n"
             .    '<img src="' . get_path('imgRepositoryWeb') . 'enroll.gif" border="0" alt="' . get_lang('Register to the class') . '" />' . "\n"
             .    '</a>' . "\n"
             .    '</td>' . "\n"
             ;
         }
         else
         {
             echo '<td align="center">' . "\n"
             .    '<small>' . get_lang('User already in class') . '</small>' . "\n"
             .    '</td>' . "\n"
             ;
         }

        // Unregister

         if ($list['id']!=null)
         {
             echo '<td align="center">' . "\n"
             .    '<a href="'.$_SERVER['PHP_SELF'].'?class_id='.$class_id.'&amp;cmd=unsubscribe&user_id='.$list['user_id'].'&amp;offset='.$offset.'#u'.$list['user_id'].'">' . "\n"
             .    '<img src="' . get_path('imgRepositoryWeb') . 'unenroll.gif" border="0" alt="' . get_lang('Unregister from class').'" />' . "\n"
             .    '</a>' . "\n"
             .    '</td>' . "\n"
             ;
         }
         else
         {
             echo '<td align="center">' . "\n"
             .    '<small>' . get_lang('User not in the class') . '</small>' . "\n"
             .    '</td>' . "\n"
             ;
         }
         echo '</tr>' . "\n";
    }

    // end display users table

    echo '</tbody>' . "\n"
    .    '</table>' . "\n"
    ;

    //Pager
    echo $myPager->disp_pager_tool_bar($_SERVER['PHP_SELF'].'?class_id='.$class_id . ($searchFor?'&amp;searchFor='.urlencode($searchFor):''));

}

include get_path('incRepositorySys') . '/claro_init_footer.inc.php';
?>
