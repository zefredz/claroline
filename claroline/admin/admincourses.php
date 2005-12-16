<?php // $Id$
/**
 * CLAROLINE
 *
 * List courses aivailable on the platform and prupose admin link to it
 *
 * @version 1.7 $Revision$
 *
 * @copyright 2001-2005 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @see http://www.claroline.net/wiki/COURSE/
 *
 * @author Claro Team <cvs@claroline.net>
 *
 * @package COURSE
 *
 */


$cidReset = TRUE;$gidReset = TRUE;$tidReset = TRUE;
$coursePerPage= 20;
// initialisation of global variables and used libraries
defined('COURSE_CREATOR') || define('COURSE_CREATOR',1);
defined('COURSE_STUDENT') || define('COURSE_STUDENT',5);

require '../inc/claro_init_global.inc.php';

// Security check
if ( ! $_uid ) claro_disp_auth_form();
if ( ! $is_platformAdmin ) claro_die(get_lang('Not allowed'));

// initialisation of global variables and used libraries
require_once $includePath . '/lib/admin.lib.inc.php';
require_once $includePath . '/lib/pager.lib.php';
$tbl_mdb_names       = claro_sql_get_main_tbl();
$tbl_user            = $tbl_mdb_names['user'  ];
$tbl_course          = $tbl_mdb_names['course'];
$tbl_admin           = $tbl_mdb_names['admin' ];
$tbl_rel_course_user = $tbl_mdb_names['rel_course_user' ];

// javascript confirm pop up declaration

$htmlHeadXtra[] =
'<script type="text/javascript">
function confirmation (name)
{
    if (confirm("' . clean_str_for_javascript(get_lang('AreYouSureToDelete')) . '" + name + \'"? \'))
        {return true;}
    else
        {return false;}
}
</script>';

// Deal with interbredcrumps

$interbredcrump[]= array ('url' => $rootAdminWeb, 'name' => get_lang('Administration'));
$nameTools = get_lang('CourseList');

//------------------------
//  USED SESSION VARIABLES
//------------------------
// deal with session variables for search criteria, it depends where we come from :
// 1 ) we must be able to get back to the list that concerned the criteria we previously used (with out re entering them)
// 2 ) we must be able to arrive with new critera for a new search.

// clean session if needed from  previous search

if ( isset($_REQUEST['newsearch']) && $_REQUEST['newsearch']=='yes')
{
    unset($_SESSION['admin_course_code'        ]);
    unset($_SESSION['admin_course_letter'      ]);
    unset($_SESSION['admin_course_intitule'    ]);
    unset($_SESSION['admin_course_category'    ]);
    unset($_SESSION['admin_course_language'    ]);
    unset($_SESSION['admin_course_access'      ]);
    unset($_SESSION['admin_course_subscription']);
}

if (isset($_REQUEST['code'        ])) $_SESSION['admin_course_code'    ] = trim($_REQUEST['code'    ]);
if (isset($_REQUEST['letter'      ])) $_SESSION['admin_course_letter'  ] = trim($_REQUEST['letter'  ]);
if (isset($_REQUEST['search'      ])) $_SESSION['admin_course_search'  ] = trim($_REQUEST['search'  ]);
if (isset($_REQUEST['intitule'    ])) $_SESSION['admin_course_intitule'] = trim($_REQUEST['intitule']);
if (isset($_REQUEST['category'    ])) $_SESSION['admin_course_category'] = trim($_REQUEST['category']);
if (isset($_REQUEST['language'    ])) $_SESSION['admin_course_language'] = trim($_REQUEST['language']);
if (isset($_REQUEST['access'      ])) $_SESSION['admin_course_access'  ] = trim($_REQUEST['access'  ]);
if (isset($_REQUEST['subscription'])) $_SESSION['admin_course_subscription'] = trim($_REQUEST['subscription']);

if (isset($_REQUEST['search']))  $search = $_REQUEST['search'];
else                             $search='';

//set the reorder parameters for colomuns titles

if (!isset($order['code' ])) $order['code']   = '';
if (!isset($order['label'])) $order['label']  = '';
if (!isset($order['cat'  ])) $order['cat']    = '';

// Set parameters to add to URL to know where we come from and what options will be given to the user

$addToURL = '';
if (!isset($_REQUEST['offsetC']))
{
   $offsetC = '0';
}
else
{
   $offsetC = $_REQUEST['offsetC'];
}

if (!isset($cfrom) || $cfrom!='clist') //offset not kept when come from another list
{
   $addToURL .= '&amp;offsetC=' . $offsetC;
}


//----------------------------------
// EXECUTE COMMAND
//----------------------------------

if (isset($_REQUEST['cmd'])) $cmd = $_REQUEST['cmd'];
else                         $cmd = null;

switch ($cmd)
{
  case 'delete' :
        $delCode = $_REQUEST['delCode'];
        $the_course = claro_get_course_data($delCode);

        if ($the_course)
        {
            delete_course($delCode);
            $dialogBox = get_lang('CourseDelete');
            $noQUERY_STRING = true;
        }
        else
        {
            switch(claro_failure::get_last_failure())
            {
                case 'course_not_found':
                {
                    $dialogBox = get_lang('CourseNotFound');
                }
                break;
                default  :
                {
                    $dialogBox = get_lang('CourseNotFound');
                }
            }

        }
        break;
}

//----------------------------------
// Build query and find info in db
//----------------------------------

   // main query to know what must be displayed in the list

$sql = "SELECT  C.*,
                C.`fake_code` `officialCode`,
                C.`code`      `sysCode`,
                C.`directory` `repository`,
                count(IF(`CU`.`statut`=" . COURSE_STUDENT . ",".COURSE_CREATOR.",null)) `qty_stu` ,
                #count only lines where statut of user is COURSE_STUDENT

                count(IF(`CU`.`statut`=1,1,null)) `qty_cm`
                #count only lines where statut of user is 1

        FROM `".$tbl_course."` AS C
        LEFT JOIN `".$tbl_rel_course_user."` AS CU
            ON `CU`.`code_cours` = `C`.`code`
        WHERE 1=1 ";

//deal with LETTER classification call

if (isset($_SESSION['admin_course_letter']))
{
    $toAdd = " AND C.`intitule` LIKE '". addslashes($_SESSION['admin_course_letter']) ."%' ";
    $sql.=$toAdd;
}

//deal with KEY WORDS classification call
if (isset($_SESSION['admin_course_search']))
{
    $toAdd = " AND (      C.`intitule`  LIKE '%". addslashes(pr_star_replace($_SESSION['admin_course_search'])) ."%'
                       OR C.`fake_code` LIKE '%". addslashes(pr_star_replace($_SESSION['admin_course_search'])) ."%'
                       OR C.`faculte`   LIKE '%". addslashes(pr_star_replace($_SESSION['admin_course_search'])) ."%'
               )";
    $sql.=$toAdd;

}

//deal with ADVANCED SEARCH parmaters call

if (isset($_SESSION['admin_course_intitule']))    // title of the course keyword is used
{
    $toAdd = " AND (C.`intitule` LIKE '%". addslashes(pr_star_replace($_SESSION['admin_course_intitule'])) ."%') ";
    $sql.=$toAdd;

}

if (isset($_SESSION['admin_course_code']))        // code keyword is used
{
    $toAdd = " AND (C.`fake_code` LIKE '%". addslashes(pr_star_replace($_SESSION['admin_course_code'])) ."%') ";
    $sql.=$toAdd;

}

if (isset($_SESSION['admin_course_category']))     // course category keyword is used
{
    $toAdd = " AND (C.`faculte` LIKE '%". addslashes(pr_star_replace($_SESSION['admin_course_category'])) ."%') ";
    $sql.=$toAdd;

}

if (isset($_SESSION['admin_course_language']))    // language filter is used
{
    $toAdd = " AND (C.`languageCourse` LIKE '%". addslashes($_SESSION['admin_course_language']) ."%') ";
    $sql.=$toAdd;

}

if (isset($_SESSION['admin_course_access']))     // type of access to course filter is used
{
    $toAdd = "";
    if ($_SESSION['admin_course_access'] == 'private')
    {
       $toAdd = " AND NOT (C.`visible`=2 OR C.`visible`=3) ";
    }
    elseif ($_SESSION['admin_course_access'] == 'public')
    {
       $toAdd = " AND (C.`visible`=2 OR C.`visible`=3) ";
    }

    $sql.=$toAdd;

}

if (isset($_SESSION['admin_course_subscription']))   // type of subscription allowed is used
{
    $toAdd = "";
    if ($_SESSION['admin_course_subscription']=="allowed")
    {
       $toAdd = " AND (C.`visible`=1 OR C.`visible`=2) ";
    }
    elseif ($_SESSION['admin_course_subscription']=="denied")
    {
       $toAdd = " AND NOT (C.`visible`=1 OR C.`visible`=2) ";
    }

    $sql.=$toAdd;

}
    $sql.=' GROUP BY C.code';


// USE PAGER

$myPager = new claro_sql_pager($sql, $offsetC, $coursePerPage);

$sortKey = isset($_GET['sort']) ? $_GET['sort'] : 'officialCode';
$sortDir = isset($_GET['dir' ]) ? $_GET['dir' ] : SORT_ASC;

$myPager->set_sort_key($sortKey, $sortDir);

$myPager->set_pager_call_param_name('offsetC');
$resultList = $myPager->get_result_list();

//----------------------------------
// DISPLAY
//----------------------------------
include($includePath . '/claro_init_header.inc.php');

//display title

echo claro_disp_tool_title($nameTools);

// display forms and dialogBox, alphabetic choice,...

if (isset($dialogBox))
{
   echo claro_disp_message_box($dialogBox);
}
   //TOOL LINKS

   //Display search form


  //see passed search parameters :

$advanced_search_query_string = array();
$isSearched ='';

if ( !empty($_REQUEST['search']) )
{
    $isSearched .= trim($_REQUEST['search']) . ' ';
}

if ( !empty($_REQUEST['code']) )
{
    $isSearched .= get_lang('Code') . ' = ' . $_REQUEST['code'] . ' ';
    $advanced_search_query_string[] = 'code=' . urlencode($_REQUEST['code']);
}

if ( !empty($_REQUEST['intitule']) )
{
    $isSearched .= get_lang('CourseTitle') . ' = ' . $_REQUEST['intitule'] . ' ';
    $advanced_search_query_string[] = 'intitule=' . urlencode($_REQUEST['intitule']);
}

if ( !empty($_REQUEST['category']) )
{
    $isSearched .= get_lang('Category') . ' = ' . $_REQUEST['category'] . ' ';
    $advanced_search_query_string[] = 'category=' . urlencode($_REQUEST['category']);
}
if ( !empty($_REQUEST['language']) )
{
    $isSearched .= get_lang('Language') . ' : ' . $_REQUEST['language'] . ' ';
    $advanced_search_query_string[] = 'language=' . urlencode($_REQUEST['language']);
}
if (isset($_REQUEST['access'])   && $_REQUEST['access'] == 'public')
{
    $isSearched .= ' <b><br />' . get_lang('PublicOnly') . ' </b> ';

}
if (isset($_REQUEST['access']) && $_REQUEST['access'] == 'private')
{
    $isSearched .= ' <b><br />' . get_lang('PrivateOnly') . ' </b>  ';
}
if (isset($_REQUEST['subscription']) && $_REQUEST['subscription'] == 'allowed')
{
    $isSearched .= ' <b><br />' . get_lang('SubscriptionAllowedOnly') . ' </b>  ';
}
if (isset($_REQUEST['subscription']) && $_REQUEST['subscription'] == 'denied')
{
    $isSearched .= ' <b><br />' . get_lang('SubscriptionDeniedOnly') . ' </b>  ';
}

//see what must be kept for advanced links

if ( !empty($_REQUEST['access']) )
{
   $advanced_search_query_string[] ='access=' . urlencode($_REQUEST['access']);
}
if ( !empty($_REQUEST['subscription']) )
{
   $advanced_search_query_string[] ='subscription=' . urlencode($_REQUEST['subscription']);
}

if ( count($advanced_search_query_string) > 0 )
{
    $addtoAdvanced = '?' . implode('&',$advanced_search_query_string);
}
else
{
    $addtoAdvanced = '';
}

//finaly, form itself

if( empty($isSearched) )
{
    $title = '&nbsp;';
    $isSearched = '&nbsp;';
}
else
{
    $title = get_lang('SearchOn') . ' : ';
}

echo "\n".'<table width="100%">'."\n\n"
.    '<tr>'."\n"
.    '<td align="left">'."\n"
.    '<b>'
.    $title
.    '</b>'
.    '<small>'
.    $isSearched
.    '</small>'
.    '</td>'."\n"
.    '<td align="right">'."\n\n"
.    '<form action="' . $_SERVER['PHP_SELF'] . '">'."\n"
.    '<label for="search">' . get_lang('MakeNewSearch') . '</label>'."\n"
.    '<input type="text" value="' . htmlspecialchars($search) . '" name="search" id="search" />'."\n"
.    '<input type="submit" value=" ' . get_lang('Ok') . ' " />'."\n"
.    '<input type="hidden" name="newsearch" value="yes" />'."\n"
.    '[<a class="claroCmd" href="advancedCourseSearch.php' . $addtoAdvanced . '">'
.    get_lang('Advanced')
.    '</a>]'."\n"
.    '</form>'."\n\n"
.    '</td>'."\n"
.    '</tr>'."\n\n"
.    '</table>'."\n\n"
;


   //Pager

echo $myPager->disp_pager_tool_bar($_SERVER['PHP_SELF']);

$sortUrlList = $myPager->get_sort_url_list($_SERVER['PHP_SELF']);

// display list

echo                                                         "\n\n"
.    '<table class="claroTable emphaseLine" width="100%" >'. "\n\n"

.    '<thead>'                                             . "\n"
.    '<tr class="headerX" align="center" valign="top">'    . "\n"
.    '<th><a href="' . $sortUrlList['officialCode'] . '">' . get_lang('Code')        . '</a></th>'."\n"
.    '<th><a href="' . $sortUrlList['intitule'    ] . '">' . get_lang('CourseTitle') . '</a></th>'."\n"
.    '<th><a href="' . $sortUrlList['faculte'     ] . '">' . get_lang('Category')    . '</a></th>'."\n"

.    '<th>' . get_lang('AllUsersOfThisCourse'). '</th>' . "\n"
.    '<th>' . get_lang('CourseSettings')      . '</th>' . "\n"
.    '<th>' . get_lang('Delete')              . '</th>' . "\n"
.    '</tr>'                                            . "\n"
.    '</thead>' . "\n\n"

.    '<tbody>' ."\n\n"
;

foreach($resultList as $courseLine)
{
    echo '<tr>'."\n";


    if (    isset($_SESSION['admin_course_search'])
        && $_SESSION['admin_course_search'] != '' )
        //trick to prevent "//1" display when no keyword used in search
    {
        $bold_search = str_replace('*', '.*', $_SESSION['admin_course_search']);

        //  Code

        echo '<td >'
        .    eregi_replace("(".$bold_search.")","<b>\\1</b>", $courseLine['officialCode'])
        .    '</td>'."\n"

        // title

        .    '<td align="left">'
        .    '<a href="' . $coursesRepositoryWeb . $courseLine['directory'] . '">'
        .    eregi_replace("(".$bold_search.")","<b>\\1</b>", $courseLine['intitule'])
        .    '</a></td>'."\n"

        //  Category
        .    '<td align="left">'
        .    eregi_replace("(".$bold_search.")","<b>\\1</b>", $courseLine['faculte'])
        .    '</td>'."\n";
    }
    else
    {
        //  Code

        echo '<td >'
        .    $courseLine['officialCode']
        .    '</td>'."\n"

        // title

        .    '<td align="left">'
        .    '<a href="' . $coursesRepositoryWeb . $courseLine['directory'] . '">'
        .    $courseLine['intitule']
        .    '</a>'
        .    '</td>'."\n"

        //  Category

        .    '<td align="left">' . $courseLine['faculte'] . '</td>'."\n"
        ;
    }



     //  All users of this course

    echo  '<td align="right">' . "\n"
    .     '<a href="admincourseusers.php'
    .     '?cidToEdit=' . $courseLine['code'] . $addToURL . '&amp;cfrom=clist">'
    .     sprintf( ( $courseLine['qty_cm'] + $courseLine['qty_stu'] > 1 ? get_lang('_p_d_course_members') : get_lang('_p_d_course_member'))
                 , ( $courseLine['qty_stu'] + $courseLine['qty_cm'] ) )
    .     '</a>'
    .     '<br />'."\n".'<small><small>'."\n"
    .     sprintf( ( $courseLine['qty_cm'] > 1 ? get_lang('_p_d_course_managers') : get_lang('_p_d_course_manager'))
                 , $courseLine['qty_cm']) . "\n"
    .     sprintf( ( $courseLine['qty_stu'] > 1 ? get_lang('_p_d_students') : get_lang('_p_d_student'))
                 , $courseLine['qty_stu']) . "\n"
    .     '</small></small>' . "\n"
    .     '</td>' . "\n"

    // Modify course settings

    .    '<td align="center">' ."\n"
    .    '<a href="../course_info/infocours.php?adminContext=1'
    .    '&amp;cidReq=' . $courseLine['code'] . $addToURL . '&amp;cfrom=clist">'
    .    '<img src="' . $imgRepositoryWeb . 'settings.gif" alt="' . get_lang('CourseSettings'). '" />'
    .    '</a>'
    .    '</td>' . "\n"

    //  Delete link


    .    '<td align="center">' . "\n"
    .    '<a href="' . $_SERVER['PHP_SELF']
    .    '?cmd=delete&amp;delCode=' . $courseLine['code'] . $addToURL . '" '
    .    ' onClick="return confirmation(\'' . clean_str_for_javascript($courseLine['intitule']) . '\');">' . "\n"
    .    '<img src="' . $imgRepositoryWeb . 'delete.gif" border="0" alt="' . get_lang('Delete') . '" />' . "\n"
    .    '</a>' . "\n"
    .    '</td>' . "\n"
    .    '</tr>' . "\n\n"
    ;
    $atleastOneResult = TRUE;
}

if (!isset($atleastOneResult))
{
   echo '<tr>'."\n"
   .    '<td colspan="6" align="center">'
   .    get_lang('NoCourseResult') . '<br />'
   .    '<a href="advancedCourseSearch.php' . $addtoAdvanced . '">' . get_lang('SearchAgain') . '</a>'
   .    '</td>'."\n"
   .    '</tr>'."\n\n"
   ;
}
echo '</tbody>'."\n\n"
.    '</table>'."\n\n"
;

//Pager

echo $myPager->disp_pager_tool_bar($_SERVER['PHP_SELF']);

// display footer

include $includePath . '/claro_init_footer.inc.php';
?>
