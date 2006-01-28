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
if ( ! $_uid ) claro_disp_auth_form();
if ( ! $is_platformAdmin ) claro_die(get_lang('Not allowed'));

require_once $includePath . '/lib/pager.lib.php';
require_once $includePath . '/lib/admin.lib.inc.php';
require_once $includePath . '/lib/user.lib.php';

if ((isset($_REQUEST['cidToEdit'])) && ($_REQUEST['cidToEdit']=='')) {unset($_REQUEST['cidToEdit']);}


//------------------------------------------------------------------------------
//  USED SESSION VARIABLES
//------------------------------------------------------------------------------

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

//TABLES
//declare needed tables
$tbl_mdb_names   = claro_sql_get_main_tbl();

$tbl_admin       = $tbl_mdb_names['admin'          ];
$tbl_course_user = $tbl_mdb_names['rel_course_user'];
$tbl_user        = $tbl_mdb_names['user'           ];

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

// Deal with interbredcrumps

$interbredcrump[] = array ('url' => $rootAdminWeb, 'name' => get_lang('Administration'));
$nameTools = get_lang('ListUsers');
//TABLES

//------------------------------------
// Execute COMMAND section
//------------------------------------

$cmd = (isset($_REQUEST['cmd'])? $_REQUEST['cmd'] : null);

switch ( $cmd )
{
    case 'delete' :
    {
        if  (isset($_REQUEST['user_id']) ) $user_id = $_REQUEST['user_id'];
        else                               $user_id = null;
        $dialogBox = ( user_delete($user_id) ? get_lang('UserDelete') : get_lang('NotUnregYourself'));
    }   break;
}

//----------------------------------
// Build query and find info in db
//----------------------------------

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
       FROM  `" . $tbl_user . "` AS `U`";

//deal with admin user search only (PART ONE)

if (isset($_SESSION['admin_user_action']) && $_SESSION['admin_user_action'] == 'plateformadmin')
{
    $sql .= ", `" . $tbl_admin . "` AS `AD`";
}

// join with course table to find course numbers of each user and last login

$sql.= "
       LEFT JOIN `" . $tbl_course_user . "` AS `CU`
       ON `CU`.`user_id` = `U`.`user_id`

       WHERE 1=1 ";

//deal with admin user search only (PART TWO)

if (isset($_SESSION['admin_user_action']) && ($_SESSION['admin_user_action'] == 'plateformadmin'))
{
    $sql .= " AND `AD`.`idUser` = `U`.`user_id` ";
    $sql .= ", `" . $tbl_admin . "` AS `AD`";
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

if (   isset($_SESSION['admin_user_action']))
{
    if(    $_SESSION['admin_user_action'] == 'createcourse'
    || $_SESSION['admin_user_action'] == 'plateformadmin')
    {
        $sql.=" AND (U.`statut`=" . COURSE_CREATOR . ")";
    }
    elseif ( $_SESSION['admin_user_action'] == 'followcourse' )
    {
        $sql.=" AND (U.`statut`=" . COURSE_STUDENT . ")";
    }
}
$sql.=" GROUP BY U.`user_id` ";

//Build pager with SQL request

$offset       = isset($_REQUEST['offset']) ? $_REQUEST['offset'] : 0 ;
$myPager      = new claro_sql_pager($sql, $offset, $userPerPage);

$pagerSortKey = isset($_REQUEST['sort']) ? $_REQUEST['sort'] : 'name';
$pagerSortDir = isset($_REQUEST['dir' ]) ? $_REQUEST['dir' ] : SORT_ASC;
$myPager->set_sort_key($pagerSortKey, $pagerSortDir);


$userList = $myPager->get_result_list();

//Display search form
//see passed search parameters :

$advanced_search_query_string = array();
$isSearched = array();
if ( !empty($_SESSION['admin_user_search']) )
{
    $isSearched[] = '*' . $_SESSION['admin_user_search'] . '*';
}
if ( !empty($_SESSION['admin_user_firstName']) )
{
    $isSearched[] = get_lang('FirstName') . '=' . $_SESSION['admin_user_firstName'] . '*';
    $advanced_search_query_string [] = 'firstName=' . urlencode($_SESSION['admin_user_firstName']);
}
if ( !empty($_SESSION['admin_user_lastName']) )
{
    $isSearched[] = get_lang('LastName') . '=' . $_SESSION['admin_user_lastName'] . '*';
    $advanced_search_query_string[] = 'lastName=' . urlencode($_SESSION['admin_user_lastName']);
}
if ( !empty($_SESSION['admin_user_userName']) )
{
    $isSearched[] = get_lang('UserName') . '=' . $_SESSION['admin_user_userName'] . '*';
    $advanced_search_query_string[] = 'userName=' . urlencode($_SESSION['admin_user_userName']);
}
if ( !empty($_SESSION['admin_user_mail']) )
{
    $isSearched[] = get_lang('Email') . '=' . $_SESSION['admin_user_mail'] . '*';
    $advanced_search_query_string[] = 'mail=' . urlencode($_SESSION['admin_user_mail']);
}

if ( !empty($_SESSION['admin_user_action']) && ($_SESSION['admin_user_action'] == 'followcourse'))
{
    $isSearched[] = '<b>' . get_lang('RegStudent') . '</b>';
    $advanced_search_query_string[] = 'action=' . urlencode($_SESSION['admin_user_action']);
}
elseif ( !empty($_SESSION['admin_user_action']) && ($_SESSION['admin_user_action'] == 'createcourse'))
{
    $isSearched[] = '<b>' . get_lang('CourseCreator') . '</b>';
    $advanced_search_query_string[] = 'action=' . urlencode($_SESSION['admin_user_action']);
}
elseif (isset($_SESSION['admin_user_action']) && ($_SESSION['admin_user_action']=='plateformadmin'))
{
    $isSearched[] = '<b> <br />' . get_lang('PlatformAdministrator') . '  </b> ';
    $advanced_search_query_string[] = 'action=' . urlencode($_SESSION['admin_user_action']);
}
else $advanced_search_query_string[] = 'action=all';

if ( count($advanced_search_query_string) > 0 ) $addtoAdvanced = '?' . implode('&amp;',$advanced_search_query_string);
else                                            $addtoAdvanced = '';

if(count($isSearched) )
{
    $title = get_lang('SearchOn') . ' : ';
    $isSearched = implode('<br>', $isSearched);
}
else
{
    $title = "&nbsp;";
    $isSearched = "&nbsp;";
}

//get the search keyword, if any
$search  = (isset($_REQUEST['search']) ? $_REQUEST['search'] : '');



//---------
// DISPLAY
//---------

if (!isset($addToURL)) $addToURL ='';

//Header
include $includePath . '/claro_init_header.inc.php';

// Display tool title
echo claro_disp_tool_title($nameTools) . "\n\n";

//Display Forms or dialog box(if needed)

if(isset($dialogBox))
{
    echo claro_disp_message_box($dialogBox);
}

//Display selectbox and advanced search link

//TOOL LINKS

//Display search form

echo '<table width="100%">'
.    '<tr>'
.    '<td align="left">'
.    '<b>' . $title . '</b>'
.    '<small>'
.    $isSearched
.    '</small>'
.    '</td>'
.    '<td align="right">'
.    '<form action="' . $_SERVER['PHP_SELF'] . '">'
.    '<label for="search">' . get_lang('MakeNewSearch') . '</label>'
.    '<input type="text" value="' . htmlspecialchars($search).'" name="search" id="search" />'
.    '<input type="submit" value=" ' . get_lang('Ok') . ' " />'
.    '<input type="hidden" name="newsearch" value="yes" />'
.    '[<a class="claroCmd" href="advancedUserSearch.php' . $addtoAdvanced . '" >' . get_lang('Advanced') . '</a>]'
.    '</form>'
.    '</td>'
.    '</tr>'
.    '</table>'
;

//Pager

echo $myPager->disp_pager_tool_bar($_SERVER['PHP_SELF']);

$sortUrlList = $myPager->get_sort_url_list($_SERVER['PHP_SELF']);

// Display list of users

// start table...

echo '<table class="claroTable emphaseLine" width="100%" border="0" cellspacing="2">'
.    '<thead>'
.    '<tr class="headerX" align="center" valign="top">'
.    '<th><a href="' . $sortUrlList['user_id'     ] . '">' . get_lang('Numero') . '</a></th>'
.    '<th><a href="' . $sortUrlList['name'        ] . '">' . get_lang('LastName') . '</a></th>'
.    '<th><a href="' . $sortUrlList['firstname'   ] . '">' . get_lang('FirstName') . '</a></th>'
.    '<th><a href="' . $sortUrlList['officialCode'] . '">' . get_lang('OfficialCode') . '</a></th>'
.    '<th><a href="' . $sortUrlList['email'       ] . '">' . get_lang('Email') . '</a></th>'
.    '<th><a href="' . $sortUrlList['status'      ] . '">' . get_lang('UserStatus') . '</a></th>'
.    '<th>' . get_lang('UserSettings') . '</th>'
.    '<th><a href="' . $sortUrlList['qty_course'  ] . '">' . get_lang('Courses') . '</a></th>'
.    '<th>' . get_lang('Delete') . '</th>'
.    '</tr><tbody>'
;




// Start the list of users...
foreach($userList as $user)
{

    if ( !empty($_SESSION['admin_user_search']) )
    {
        $bold_search = str_replace('*','.*',$_SESSION['admin_user_search']);

        $user['name']   = eregi_replace('(' . $bold_search . ')' , '<b>\\1</b>', $user['name']);
        $user['firstname'] = eregi_replace('(' . $bold_search . ')' , '<b>\\1</b>', $user['firstname']);
        $user['email']  = eregi_replace('(' . $bold_search . ')', '<b>\\1</b>' , $user['email']);
    }

    if ( empty($user['officialCode']) ) $user['officialCode'] = ' - ';

    $userStatus = ($user['status']=='COURSE_CREATOR' ? get_lang('CourseCreator') : get_lang('NormalUser'));
    if (isAdminUser($user['user_id'])) $userStatus .= '<br /><font color="red">' . get_lang('Administrator').'</font>';

    echo '<tr>'
    .    '<td align="center">' . $user['user_id'] . '</td>' . "\n"
    .    '<td align="left">'   . $user['name'] . '</td>' . "\n"
    .    '<td align="left">'   . $user['firstname'] . '</td>' . "\n"
    .    '<td align="center">' . $user['officialCode'] . '</td>' . "\n"
    .    '<td align="left">'   . $user['email'] . '</td>' . "\n"
    .    '<td align="center">' . $userStatus . '</td>' . "\n"

    // Modify link

    .    '<td align="center">' . "\n"
    .    '<a href="adminprofile.php'
    .    '?uidToEdit=' . $user['user_id']
    .    '&amp;cfrom=ulist' . $addToURL . '">'
    .    '<img src="' . $imgRepositoryWeb . 'usersetting.gif" border="0" alt="' . get_lang('UserSettings') . '" />'
    .    '</a>'
    .    '</td>' . "\n"

    // All course of this user

    .    '<td align="center">'
    .    '<a href="adminusercourses.php?uidToEdit=' . $user['user_id']
    .    '&amp;cfrom=ulist' . $addToURL . '">' . "\n"
    .    sprintf(($user['qty_course']>1 ? get_lang('_p_d_courses') : get_lang('_p_d_course')), $user['qty_course']) . "\n"
    .    '</a>' . "\n"
    .    '</td>' . "\n"

    //  Delete link

    .    '<td align="center">'
    .    '<a href="'.$_SERVER['PHP_SELF']
    .    '?cmd=delete&amp;user_id=' . $user['user_id']
    .    '&amp;ffset=' . $offset . $addToURL . '" '
    .    ' onClick="return confirmation(\'' . clean_str_for_javascript(' ' . $user['firstname'] . ' ' . $user['name']).'\');">'."\n"
    .    '<img src="' . $imgRepositoryWeb . 'deluser.gif" border="0" alt="' . get_lang('Delete') . '" />' . "\n"
    .    '</a> '."\n"
    .    '</td>'."\n"
    .    '</tr>'
    ;
    $atLeastOne= TRUE;
}
// end display users table
if ( ! isset($atLeastOne) )
{
    echo '<tr>' . "\n"
    .    '<td colspan="9" align="center">' . "\n"
    .    get_lang('NoUserResult') . "\n"
    .    '<br />' . "\n"
    .    '<a href="advancedUserSearch.php' . $addtoAdvanced . '">' . get_lang('SearchAgain') . '</a>' . "\n"
    .    '</td>' . "\n"
    .    '</tr>'
    ;
}
echo '</tbody>' . "\n"
.    '</table>' . "\n"
;

//Pager

echo $myPager->disp_pager_tool_bar($_SERVER['PHP_SELF']);
include $includePath . '/claro_init_footer.inc.php';
/*******************/
// END OF SCRIPT
/*******************/

/**
 * return wheter is  user id of a platform admin.
 *
 * this  function  use a static array.
 *
 * @param integer $user_id id  of user
 * @return boolean : is  user  platform admin
 */
function isAdminUser($user_id)
{
    $tbl_mdb_names = claro_sql_get_main_tbl();
    $tbl_admin     = $tbl_mdb_names['admin'];

    static $admin_list = array();

    if ( count($admin_list) == 0 )

    {
        $sql = "SELECT `idUser` `admin_id`
                FROM `" . $tbl_admin . "` ";
        $result = claro_sql_query_fetch_all($sql);
        foreach($result as $admin_id)
        {
            $admin_list[] = $admin_id['admin_id'];
        }
    }
    return (in_array($user_id,$admin_list));
}

?>