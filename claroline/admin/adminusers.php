<?php //$Id$
/**
 * CLAROLINE
 * @version 1.8 $Revision$
 *
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @package ADMIN
 *
 * @author Guillaume Lederer <lederer@claroline.net>
 */
$cidReset = TRUE;$gidReset = TRUE;$tidReset = TRUE;

// initialisation of global variables and used libraries
defined('COURSE_CREATOR') || define('COURSE_CREATOR',1);
defined('COURSE_STUDENT') || define('COURSE_STUDENT',5);

require '../inc/claro_init_global.inc.php';
$userPerPage = get_conf('userPerPage',20); // numbers of user to display on the same page

// Security check
if ( ! get_init('_uid') ) claro_disp_auth_form();
if ( ! get_init('is_platformAdmin') ) claro_die(get_lang('Not allowed'));

require_once $includePath . '/lib/pager.lib.php';
require_once $includePath . '/lib/admin.lib.inc.php';
require_once $includePath . '/lib/user.lib.php';

// CHECK INCOMING DATAS
if ((isset($_REQUEST['cidToEdit'])) && ($_REQUEST['cidToEdit']=='')) {unset($_REQUEST['cidToEdit']);}

$validCmdList = array('delete');
$cmd = (isset($_REQUEST['cmd']) && in_array($_REQUEST['cmd'],$validCmdList)? $_REQUEST['cmd'] : null);
$userIdReq = (int) (isset($_REQUEST['user_id']) ? $_REQUEST['user_id']: null);
// USED SESSION VARIABLES
// clean session if needed

if (isset($_REQUEST['newsearch']) && $_REQUEST['newsearch'] == 'yes')
{
    unset($_SESSION['admin_user_search'   ]);
    unset($_SESSION['admin_user_firstName']);
    unset($_SESSION['admin_user_lastName' ]);
    unset($_SESSION['admin_user_userName' ]);
    unset($_SESSION['admin_user_mail'     ]);
    unset($_SESSION['admin_user_action'   ]);
    unset($_SESSION['admin_order_crit'    ]);
}

// deal with session variables for search criteria, it depends where we come from :
// 1 ) we must be able to get back to the list that concerned the criteria we previously used (with out re entering them)
// 2 ) we must be able to arrive with new critera for a new search.

if (isset($_REQUEST['search'    ])) $_SESSION['admin_user_search'    ] = trim($_REQUEST['search'    ]);
if (isset($_REQUEST['firstName' ])) $_SESSION['admin_user_firstName' ] = trim($_REQUEST['firstName' ]);
if (isset($_REQUEST['lastName'  ])) $_SESSION['admin_user_lastName'  ] = trim($_REQUEST['lastName'  ]);
if (isset($_REQUEST['userName'  ])) $_SESSION['admin_user_userName'  ] = trim($_REQUEST['userName'  ]);
if (isset($_REQUEST['mail'      ])) $_SESSION['admin_user_mail'      ] = trim($_REQUEST['mail'      ]);
if (isset($_REQUEST['action'    ])) $_SESSION['admin_user_action'    ] = trim($_REQUEST['action'    ]);

if (isset($_REQUEST['order_crit'])) $_SESSION['admin_user_order_crit'] = trim($_REQUEST['order_crit']);
if (isset($_REQUEST['dir'       ])) $_SESSION['admin_user_dir'       ] = ($_REQUEST['dir'] == 'DESC' ? 'DESC' : 'ASC' );

if (!isset($addToURL)) $addToURL ='';

//TABLES
//declare needed tables

// Deal with interbredcrumps

$interbredcrump[] = array ('url' => $rootAdminWeb, 'name' => get_lang('Administration'));
$nameTools = get_lang('User list');
//TABLES

//------------------------------------
// Execute COMMAND section
//------------------------------------
switch ( $cmd )
{
    case 'delete' :
    {
        $dialogBox = ( user_delete($userIdReq) ? get_lang('Deletion of the user was done sucessfully') : get_lang('You can not change your own settings!'));
    }   break;
}
$searchInfo = prepare_search();

$isSearched    = $searchInfo['isSearched'];
$addtoAdvanced = $searchInfo['addtoAdvanced'];

if(count($searchInfo['isSearched']) )
{
    $title = get_lang('Search on') . ' : ';
    $isSearchedHTML = implode('<br>', $isSearched);
}
else
{
    $title = "&nbsp;";
    $isSearchedHTML = "&nbsp;";
}

//get the search keyword, if any
$search  = (isset($_REQUEST['search']) ? $_REQUEST['search'] : '');

$sql = get_sql_filtered_user_list();
$offset       = isset($_REQUEST['offset']) ? $_REQUEST['offset'] : 0 ;
$myPager      = new claro_sql_pager($sql, $offset, $userPerPage);

$pagerSortKey = isset($_REQUEST['sort']) ? $_REQUEST['sort'] : 'name';
$pagerSortDir = isset($_REQUEST['dir' ]) ? $_REQUEST['dir' ] : SORT_ASC;
$myPager->set_sort_key($pagerSortKey, $pagerSortDir);

$userList = $myPager->get_result_list();
$userGrid = array();
foreach ($userList as $userKey => $user)
{
    $userGrid[$userKey]['user_id']   = $user['user_id'];
    $userGrid[$userKey]['name']      = $user['name'];
    $userGrid[$userKey]['firstname'] = $user['firstname'];
    $userEmailLabel=null;
    if ( !empty($_SESSION['admin_user_search']) )
    {
        $bold_search = str_replace('*','.*',$_SESSION['admin_user_search']);

        $userGrid[$userKey]['name'] = eregi_replace('(' . $bold_search . ')' , '<b>\\1</b>', $user['name']);
        $userGrid[$userKey]['firstname'] = eregi_replace('(' . $bold_search . ')' , '<b>\\1</b>', $user['firstname']);
        $userEmailLabel  = eregi_replace('(' . $bold_search . ')', '<b>\\1</b>' , $user['email']);
    }

    $userGrid[$userKey]['officialCode'] = empty($user['officialCode']) ? ' - ' : $user['officialCode'];
    $userGrid[$userKey]['email'] = claro_html::mailTo($user['email'], $userEmailLabel);

    $userGrid[$userKey]['status'] =  ($user['status']=='COURSE_CREATOR' ? get_lang('Course creator') : get_lang('User'));

    if (user_is_admin($user['user_id']))
    {
        $userGrid[$userKey]['status'] .= '<br /><font color="red">' . get_lang('Administrator').'</font>';
    }
    $userGrid[$userKey]['settings'] = '<a href="adminprofile.php'
    .                                 '?uidToEdit=' . $user['user_id']
    .                                 '&amp;cfrom=ulist' . $addToURL . '">'
    .                                 '<img src="' . $imgRepositoryWeb . 'usersetting.gif" border="0" alt="' . get_lang('User settings') . '" />'
    .    '</a>';
    $userGrid[$userKey]['qty_course'] = '<a href="adminusercourses.php?uidToEdit=' . $user['user_id']
    .                                   '&amp;cfrom=ulist' . $addToURL . '">' . "\n"
    .                                   sprintf(($user['qty_course']>1 ? get_lang('_p_d_courses') : get_lang('_p_d_course')), $user['qty_course']) . "\n"
    .                                   '</a>' . "\n"
    ;

    $userGrid[$userKey]['delete'] = '<a href="' . $_SERVER['PHP_SELF']
    .                               '?cmd=delete&amp;user_id=' . $user['user_id']
    .                               '&amp;ffset=' . $offset . $addToURL . '" '
    .                               ' onClick="return confirmation(\'' . clean_str_for_javascript(' ' . $user['firstname'] . ' ' . $user['name']).'\');">' . "\n"
    .                               '<img src="' . $imgRepositoryWeb . 'deluser.gif" border="0" alt="' . get_lang('Delete') . '" />' . "\n"
    .                               '</a> '."\n"
    ;

}
$sortUrlList = $myPager->get_sort_url_list($_SERVER['PHP_SELF']);
$userDataGrid = new claro_datagrid();
$userDataGrid->set_grid($userGrid);
$userDataGrid->set_colHead('name') ;
$userDataGrid->set_colTitleList(array (
                 'user_id'=>'<a href="' . $sortUrlList['user_id'] . '">' . get_lang('Numero') . '</a>'
                ,'name'=>'<a href="' . $sortUrlList['name'] . '">' . get_lang('Last name') . '</a>'
                ,'firstname'=>'<a href="' . $sortUrlList['firstname'] . '">' . get_lang('First name') . '</a>'
                ,'officialCode'=>'<a href="' . $sortUrlList['officialCode'] . '">' . get_lang('Administrative code') . '</a>'
                ,'email'=>'<a href="' . $sortUrlList['email'] . '">' . get_lang('Email') . '</a>'
                ,'status'=>'<a href="' . $sortUrlList['status'] . '">' . get_lang('Status') . '</a>'
                ,'settings'=> get_lang('User settings')
                ,'qty_course'=>'<a href="' . $sortUrlList['qty_course'  ] . '">' . get_lang('Courses') . '</a>'
                ,'delete'=>get_lang('Delete') ));

if ( count($userGrid)==0 )
{
    $userDataGrid->set_noRowMessage( get_lang('No user to display') . "\n"
    .    '<br />' . "\n"
    .    '<a href="advancedUserSearch.php' . $addtoAdvanced . '">' . get_lang('Search again (advanced)') . '</a>' . "\n"
    );
}
else
{
    $userDataGrid->set_colAttributeList(array ( 'user_id'      => array ('align' => 'center')
                                              , 'officialCode' => array ('align' => 'center')
                                              , 'settings'     => array ('align' => 'center')
                                              , 'delete'       => array ('align' => 'center')
    ));
}

//---------
// DISPLAY
//---------


//PREPARE
// javascript confirm pop up declaration
$htmlHeadXtra[] =
'<script type="text/javascript">
        function confirmation (name)
        {
            if (confirm("'.clean_str_for_javascript(get_lang('Are you sure to delete')).'" + name + "? "))
                {return true;}
            else
                {return false;}
        }'
."\n".'</script>'."\n";




//Header
include $includePath . '/claro_init_header.inc.php';

// Display tool title
echo claro_html::tool_title($nameTools) . "\n\n";

//Display Forms or dialog box(if needed)

if( isset($dialogBox) ) echo claro_html::message_box($dialogBox);

//Display selectbox and advanced search link

//TOOL LINKS

//Display search form

echo '<table width="100%">'
.    '<tr>'
.    '<td align="left">'
.    '<b>' . $title . '</b>'
.    '<small>'
.    $isSearchedHTML
.    '</small>'
.    '</td>'
.    '<td align="right">'
.    '<form action="' . $_SERVER['PHP_SELF'] . '">'
.    '<label for="search">' . get_lang('Make new search') . ' : </label>'
.    '<input type="text" value="' . htmlspecialchars($search).'" name="search" id="search" />'
.    '<input type="submit" value=" ' . get_lang('Ok') . ' " />'
.    '<input type="hidden" name="newsearch" value="yes" />'
.    '[<a class="claroCmd" href="advancedUserSearch.php' . $addtoAdvanced . '" >' . get_lang('Advanced') . '</a>]'
.    '</form>'
.    '</td>'
.    '</tr>'
.    '</table>'
;

if ( count($userGrid) == 0 )
echo $myPager->disp_pager_tool_bar($_SERVER['PHP_SELF']);
echo $userDataGrid->render();
echo $myPager->disp_pager_tool_bar($_SERVER['PHP_SELF']);
include $includePath . '/claro_init_footer.inc.php';

/**
 *
 * @todo: the  name would  be review  befor move to a lib
 * @todo: eject usage  in function of  $_SESSION
 *
 * @return sql statements
 */
function get_sql_filtered_user_list()
{
    if ( isset($_SESSION['admin_user_action']) )
    {
        switch ($_SESSION['admin_user_action'])
        {
            case 'plateformadmin' :
            {
                $filterOnStatus = 'plateformadmin';
            }  break;
            case 'createcourse' :
            {
               $filterOnStatus= 'createcourse';
            }  break;
            case 'followcourse' :
            {
                $filterOnStatus='followcourse';
            }  break;
            case 'all' :
            {
                $filterOnStatus='';
            }  break;
            default:
            {
                trigger_error('admin_user_action value unknow : '.var_export($_SESSION['admin_user_action'],1),E_USER_NOTICE);
                $filterOnStatus='followcourse';
            }
        }
    }
    else $filterOnStatus='';

    $tbl_mdb_names   = claro_sql_get_main_tbl();

    $sql = "SELECT
           `U`.`user_id`      AS `user_id`,
           `U`.`nom`          AS `name`,
           `U`.`prenom`       AS `firstname`,
           `U`.`authSource`   AS `authSource`,
           `U`.`email`        AS `email`,
           `U`.`statut`       AS `status`,
           `U`.`officialCode` AS `officialCode`,
           `U`.`phoneNumber`  AS `phoneNumber`,
           `U`.`pictureUri`   AS `pictureUri`,
           `U`.`creatorId`    AS creator_id,
           IF(`U`.`statut`=" . COURSE_CREATOR . ",'COURSE_CREATOR','ORDINARY') AS `statut` ,
           count(DISTINCT `CU`.`code_cours`) AS `qty_course`
           FROM  `" . $tbl_mdb_names['user'] . "` AS `U`";

    //deal with admin user search only (PART ONE)


    if ($filterOnStatus == 'plateformadmin')
    {
        $sql .= ", `" . $tbl_mdb_names['admin'] . "` AS `AD`";
    }

    // join with course table to find course numbers of each user and last login

    $sql.= "
           LEFT JOIN `" . $tbl_mdb_names['rel_course_user'] . "` AS `CU`
           ON `CU`.`user_id` = `U`.`user_id`

           WHERE 1=1 ";

    //deal with admin user search only (PART TWO)

    if ($filterOnStatus=='plateformadmin')
    {
        $sql .= " AND `AD`.`idUser` = `U`.`user_id` ";
    }

    //deal with KEY WORDS classification call

    if (isset($_SESSION['admin_user_search']))
    {
        $sql .= " AND (U.`nom` LIKE '%". addslashes(pr_star_replace($_SESSION['admin_user_search'])) ."%'
                  OR U.`prenom` LIKE '%".addslashes(pr_star_replace($_SESSION['admin_user_search'])) ."%' ";
        $sql .= " OR U.`email` LIKE '%". addslashes(pr_star_replace($_SESSION['admin_user_search'])) ."%')";
    }

    //deal with ADVANCED SEARCH parameters call

    if (isset($_SESSION['admin_user_firstName']))
    {
        $sql .= " AND (U.`prenom` LIKE '". addslashes(pr_star_replace($_SESSION['admin_user_firstName'])) ."%') ";
    }

    if (isset($_SESSION['admin_user_lastName']))
    {
        $sql .= " AND (U.`nom` LIKE '". addslashes(pr_star_replace($_SESSION['admin_user_lastName']))."%') ";
    }

    if (isset($_SESSION['admin_user_userName']))
    {
        $sql.= " AND (U.`username` LIKE '". addslashes(pr_star_replace($_SESSION['admin_user_userName'])) ."%') ";
    }

    if (isset($_SESSION['admin_user_mail']))
    {
        $sql.= " AND (U.`email` LIKE '". addslashes(pr_star_replace($_SESSION['admin_user_mail'])) ."%') ";
    }

    if ($filterOnStatus== 'createcourse' )
    {
        $sql.=" AND (U.`statut`=" . COURSE_CREATOR . ")";
    }
    elseif ($filterOnStatus=='followcourse' )
    {
        $sql.=" AND (U.`statut`=" . COURSE_STUDENT . ")";
    }

    $sql.=" GROUP BY U.`user_id` ";
        return $sql;
}



function prepare_search()
{
    $queryStringElementList = array();
    $isSearched = array();

    if ( !empty($_SESSION['admin_user_search']) )
    {
        $isSearched[] = '*' . $_SESSION['admin_user_search'] . '*';
    }

    if ( !empty($_SESSION['admin_user_firstName']) )
    {
        $isSearched[] = get_lang('First name') . '=' . $_SESSION['admin_user_firstName'] . '*';
        $queryStringElementList [] = 'firstName=' . urlencode($_SESSION['admin_user_firstName']);
    }

    if ( !empty($_SESSION['admin_user_lastName']) )
    {
        $isSearched[] = get_lang('Last name') . '=' . $_SESSION['admin_user_lastName'] . '*';
        $queryStringElementList[] = 'lastName=' . urlencode($_SESSION['admin_user_lastName']);
    }

    if ( !empty($_SESSION['admin_user_userName']) )
    {
        $isSearched[] = get_lang('Username') . '=' . $_SESSION['admin_user_userName'] . '*';
        $queryStringElementList[] = 'userName=' . urlencode($_SESSION['admin_user_userName']);
    }
    if ( !empty($_SESSION['admin_user_mail']) )
    {
        $isSearched[] = get_lang('Email') . '=' . $_SESSION['admin_user_mail'] . '*';
        $queryStringElementList[] = 'mail=' . urlencode($_SESSION['admin_user_mail']);
    }

    if ( !empty($_SESSION['admin_user_action']) && ($_SESSION['admin_user_action'] == 'followcourse'))
    {
        $isSearched[] = '<b>' . get_lang('Follow courses') . '</b>';
        $queryStringElementList[] = 'action=' . urlencode($_SESSION['admin_user_action']);
    }
    elseif ( !empty($_SESSION['admin_user_action']) && ($_SESSION['admin_user_action'] == 'createcourse'))
    {
        $isSearched[] = '<b>' . get_lang('Course creator') . '</b>';
        $queryStringElementList[] = 'action=' . urlencode($_SESSION['admin_user_action']);
    }
    elseif (isset($_SESSION['admin_user_action']) && ($_SESSION['admin_user_action']=='plateformadmin'))
    {
        $isSearched[] = '<b>' . get_lang('Platform Administrator') . '  </b> ';
        $queryStringElementList[] = 'action=' . urlencode($_SESSION['admin_user_action']);
    }
    else $queryStringElementList[] = 'action=all';

    if ( count($queryStringElementList) > 0 ) $queryString = '?' . implode('&amp;',$queryStringElementList);
    else                                      $queryString = '';

    $searchInfo['isSearched'] = $isSearched;
    $searchInfo['addtoAdvanced'] = $queryString;

    return $searchInfo;
}
?>
