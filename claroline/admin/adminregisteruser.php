<?php // $Id$
/**
 * CLAROLINE
 *
 * This script list member of campus and  propose to subscribe it to the given course
 *
 * @version 1.8 $Revision$
 *
 * @copyright 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @see http://www.claroline.net/wiki/CLADMIN/
 *
 * @author Claro Team <cvs@claroline.net>
 *
 * @package CLUSR
 *
 */

$cidReset = TRUE; $gidReset = TRUE; $tidReset = TRUE;

// initialisation of global variables and used libraries
require '../inc/claro_init_global.inc.php';

include_once $includePath . '/lib/pager.lib.php';
include_once $includePath . '/lib/admin.lib.inc.php';
include_once $includePath . '/lib/user.lib.php';

// Security check
if ( ! $_uid ) claro_disp_auth_form();
if ( ! $is_platformAdmin ) claro_die(get_lang('Not allowed'));

if ((isset($_REQUEST['cidToEdit']) && $_REQUEST['cidToEdit']=='') || !isset($_REQUEST['cidToEdit']))
{
    unset($_REQUEST['cidToEdit']);
    $dialogBox = 'ERROR : NO COURSE SET!!!';

}
else
{
   $cidToEdit = $_REQUEST['cidToEdit'];
}
$userPerPage = 20; // numbers of user to display on the same page

//get needed parameter from URL

$user_id = isset( $_REQUEST['user_id'] ) ? $_REQUEST['user_id'] : null ;

if ($cidToEdit=='') { $dialogBox ='ERROR : NO USER SET!!!'; }

// Deal with interbredcrumps
$interbredcrump[]= array ( 'url' => $rootAdminWeb, 'name' => get_lang('Administration'));
$nameTools = get_lang('Enroll a user');

//TABLES
$tbl_mdb_names = claro_sql_get_main_tbl();
$tbl_user          = $tbl_mdb_names['user'            ];
$tbl_courses       = $tbl_mdb_names['course'          ];
$tbl_admin         = $tbl_mdb_names['admin'           ];
$tbl_course_user   = $tbl_mdb_names['rel_course_user' ];
$tbl_track_default = $tbl_mdb_names['track_e_default' ];

// See SESSION variables used for reorder criteria :

if (isset($_REQUEST['dir']))       {$_SESSION['admin_register_dir']        = $_REQUEST['dir'];       }
if (isset($_REQUEST['order_crit'])){$_SESSION['admin_register_order_crit'] = $_REQUEST['order_crit'];}

//------------------------------------
// Execute COMMAND section
//------------------------------------

$cmd = isset($_REQUEST['cmd']) ? $_REQUEST['cmd'] : null;

switch ( $cmd )
{
    case 'sub' : //execute subscription command...

        $done = user_add_to_course($user_id, $cidToEdit);

        // Set status requested
        if ( $_REQUEST['subas'] == 'teach' )     // ... as teacher
        {
            $properties['status'] = 1;
            $properties['role']   = get_lang('Course manager');
            $properties['tutor']  = 1;
        }
        elseif ($_REQUEST['subas']=='stud')  // ... as student
        {
            $properties['status'] = 5;
            $properties['role']   = '';
            $properties['tutor']  = 0;
        }
        user_update_course_properties($user_id, $cidToEdit, $properties);

        //set dialogbox message

        if ( $done )
        {
           $dialogBox = get_lang('_user_has_been_enrolled_to_the_course');
        }
        break;

}

//build and call DB to get info about current course (for title) if needed :

$courseData = claro_get_course_data($cidToEdit);

//----------------------------------
// Build query and find info in db
//----------------------------------

$sql = "
SELECT
    U.nom, U.prenom, U.`user_id` AS ID,
    CU.*,
    CU.`user_id` AS Register
FROM  `" . $tbl_user . "` AS U";

$toAdd = "
LEFT JOIN `" . $tbl_course_user . "` AS CU
    ON             CU.`user_id`=U.`user_id`
            AND CU.`code_cours` = '" . addslashes($cidToEdit) . "'
        ";

$sql.=$toAdd;

//deal with LETTER classification call

if (isset($_GET['letter']))
{
    $toAdd = "
            AND U.`nom` LIKE '" . addslashes($_GET['letter']) . "%' ";
    $sql .= $toAdd;
}

//deal with KEY WORDS classification call

if ( isset( $_REQUEST['search'] ) && $_REQUEST['search'] != '' )
{
    $toAdd = " WHERE (U.`nom` LIKE '" . addslashes($_REQUEST['search']) . "%'
              OR U.`username` LIKE '" . addslashes($_REQUEST['search']) . "%'
              OR U.`prenom` LIKE '" . addslashes($_REQUEST['search']) . "%') " ;

    $sql .= $toAdd;
}

// deal with REORDER

//first see is direction must be changed

if ( isset( $_REQUEST['chdir'] ) && ( $_REQUEST['chdir'] == 'yes' ) )
{
    if ( $_SESSION['admin_register_dir'] == 'ASC' )
    {
        $_SESSION['admin_register_dir'] = 'DESC';
    }
    else
    {
        $_SESSION['admin_register_dir'] = 'ASC';
    }
}

if (isset($_SESSION['admin_register_order_crit']))
{
    if ($_SESSION['admin_register_order_crit'] == 'user_id' )
    {
        $toAdd = " ORDER BY `U`.`user_id` " . $_SESSION['admin_register_dir'];
    }
    else
    {
        $toAdd = " ORDER BY `" . $_SESSION['admin_register_order_crit'] . "` " . $_SESSION['admin_register_dir'];
    }
    $sql .= $toAdd;
}

//Build pager with SQL request

if ( !isset( $_REQUEST['offset'] ) )
{
    $offset = '0';
}
else
{
    $offset = $_REQUEST['offset'];
}

$myPager = new claro_sql_pager($sql, $offset, $userPerPage);
$userList = $myPager->get_result_list();

$isSearched = '';

//get the search keyword, if any

if ( !isset( $_REQUEST['search']) )
{
   $search = '';
}
else
{
   $search = $_REQUEST['search'];
}

if ( !isset($addToURL) ) $addToURL = '';

$nameTools .= ' : ' . $courseData['name'];

// search form

if ( isset( $search ) && $search != '' )         { $isSearched .= $search . '* '; }
if (($isSearched == '') || !isset($isSearched) ) { $title = ''; }
                                            else { $title = get_lang('SearchOn') . ' : '; }

//Pager

if (isset($_REQUEST['order_crit']))
{
    $addToURL = '&amp;order_crit=' . $_SESSION['admin_register_order_crit']
              . '&amp;dir=' . $_SESSION['admin_register_dir']
              ;
}

//------------------------------------
// DISPLAY
//------------------------------------

// Display tool title

//Header
include $includePath . '/claro_init_header.inc.php';

echo claro_disp_tool_title( $nameTools );

// Display Forms or dialog box(if needed)

if( isset($dialogBox) )
{
    echo claro_disp_message_box($dialogBox);
}


echo '<table width="100%" >'
.    '<tr>'
.    '<td align="left">' . "\n"
.    '<b>'.$title.'</b>' . "\n"
.    '<small>' . "\n"
.    $isSearched . "\n"
.    '</small>' . "\n"
.    '</td>' . "\n"
.    '<td align="right">' . "\n"
.    '<form action="' . $_SERVER['PHP_SELF'] . '">' . "\n"
.    '<label for="search">' . get_lang('MakeSearch') . '</label> :' . "\n"
.    '<input type="text" value="' . htmlspecialchars($search) . '" name="search" id="search" >' . "\n"
.    '<input type="submit" value=" ' . get_lang('Ok') . ' ">' . "\n"
.    '<input type="hidden" name="newsearch" value="yes">' . "\n"
.    '<input type="hidden" name="cidToEdit" value="' . $cidToEdit . '">' . "\n"
.    '</form>' . "\n"
.    '</td>' . "\n"
.    '</tr>' . "\n"
.    '</table>' . "\n"
//TOOL LINKS
.    '<a class="claroCmd" href="admincourseusers.php?cidToEdit='.$cidToEdit.'">'
.    get_lang('AllUsersOfThisCourse')
.    '</a><br /><br />'
;

echo $myPager->disp_pager_tool_bar($_SERVER['PHP_SELF'] . '?cidToEdit=' . $cidToEdit . $addToURL);

// Display list of users
// start table...
//columns titles...

echo '<table class="claroTable emphaseLine" width="100%" border="0" cellspacing="2">' . "\n"
.    '<thead>' . "\n"
.    '<tr class="headerX" align="center" valign="top">' . "\n"
.    '<th>'

.    '<a href="' . $_SERVER['PHP_SELF']
.    '?order_crit=user_id&amp;chdir=yes&amp;search=' . $search . '&amp;cidToEdit=' . $cidToEdit . '">'
.    get_lang('Userid')
.    '</a>'
.    '</th>' . "\n"

.    '<th>'
.    '<a href="' . $_SERVER['PHP_SELF'] . '?order_crit=nom'
.    '&amp;chdir=yes&amp;search=' . $search
.    '&amp;cidToEdit=' . $cidToEdit . '">' . get_lang('LastName') . '</a>'
.    '</th>' . "\n"

.    '<th>'
.    '<a href="' . $_SERVER['PHP_SELF']
.    '?order_crit=prenom'
.    '&amp;chdir=yes'
.    '&amp;search=' . $search
.    '&amp;cidToEdit=' . $cidToEdit . '">'
.    get_lang('FirstName')
.    '</a>'
.    '</th>' . "\n"

.    '<th>' . get_lang('Enrol as student') . '</th>' . "\n"
.    '<th>' . get_lang('Enrol as course manager') . '</th>' . "\n"
.    '</tr>' . "\n"
.    '</thead>' . "\n"
.    '<tbody>'
;

// Start the list of users...

if (isset($order_crit))
{
    $addToURL = '&amp;order_crit=' . $order_crit;
}
if (isset($offset))
{
    $addToURL = '&amp;offset=' . $offset;
}
foreach($userList as $user)
{
    if (isset($_REQUEST['search'])&& ($_REQUEST['search']!=""))
    {
        $user['nom'] = eregi_replace("^(".$_REQUEST['search'].")",'<b>\\1</b>', $user['nom']);
        $user['prenom'] = eregi_replace("^(".$_REQUEST['search'].")","<b>\\1</b>", $user['prenom']);
    }

    echo '<tr>' . "\n"
    //  Id
    .   '<td align="center">'
    .   $user['ID']
    .   '</td>'."\n"
    // name
    .   '<td align="left">'
    .   $user['nom']
    .   '</td>'
    //  Firstname
    .   '<td align="left">'
    .   $user['prenom']
    .   '</td>'
    ;
    if ($user['statut'] != "5")  // user is already enrolled but as student
    {
        // Register as user
        echo '<td align="center">' . "\n"
            .'<a href="' . $_SERVER['PHP_SELF']
            .'?cidToEdit=' . $cidToEdit
            .'&amp;cmd=sub&amp;search='.$search
            .'&amp;user_id=' . $user['ID']
            .'&amp;subas=stud' . $addToURL.'">'
            .'<img src="' . $imgRepositoryWeb . 'enroll.gif" border="0" alt="' . get_lang('SubscribeUser') . '" />' . "\n"
            .'</a>'
            .'</td>'."\n"
            ;
    }
    else
    {
        // already enrolled as student
        echo '<td align="center" >' . "\n"
        .    '<small>'
        .    get_lang('_already_enrolled')
        .    '</small>'
        .    '</td>' . "\n"
        ;
    }
    if ($user['statut'] != "1")  // user is not enrolled
    {
            //register as teacher
        echo '<td align="center">' . "\n"
        .    '<a href="' . $_SERVER['PHP_SELF']
        .    '?cidToEdit=' . $cidToEdit
        .    '&amp;cmd=sub&amp;search='.$search
        .    '&amp;user_id=' . $user['ID']
        .    '&amp;subas=teach' . $addToURL.'">'
        .    '<img src="' . $imgRepositoryWeb . 'enroll.gif" border="0" alt="' . get_lang('SubscribeUser') . '" />'
        .    '</a>' . "\n"
        .    '</td>' . "\n"
        ;
    }
    else
    {
        // already enrolled as teacher
        echo '<td align="center" >'."\n"
        .    '<small>'
        .    get_lang('_already_enrolled')
        .    '</small>'
        .    '</td>'."\n"
        ;
    }
    echo '</tr>';
}
// end display users table
echo '</tbody></table>';
//Pager
echo $myPager->disp_pager_tool_bar($_SERVER['PHP_SELF'] . '?cidToEdit=' . $cidToEdit . $addToURL);
include $includePath . '/claro_init_footer.inc.php';
?>