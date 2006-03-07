<?php // $Id$
/**
 * CLAROLINE
 *
 * List courses aivailable on the platform and prupose admin link to it
 *
 * @version 1.8 $Revision$
 *
 * @copyright 2001-2006 Universite catholique de Louvain (UCL)
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


/**
 * Check incoming data
 */
$offsetC = isset($_REQUEST['offsetC']) ? $_REQUEST['offsetC'] : '0';
$validCmdList = array('delete',);
$cmd = (isset($_REQUEST['cmd']) && in_array($_REQUEST['cmd'],$validCmdList)? $_REQUEST['cmd'] : null);
$delCode = isset($_REQUEST['delCode']) ? $_REQUEST['delCode'] : null;
$resetFilter = (bool) (isset($_REQUEST['newsearch']) && 'yes' == $_REQUEST['newsearch']);
$search = (isset($_REQUEST['search']))  ? $_REQUEST['search'] :'';
$validRefererList = array('clist',);
$cfrom = (isset($_REQUEST['cfrom']) && in_array($_REQUEST['cfrom'],$validRefererList) ? $_REQUEST['cfrom'] : null);
$addToURL = '';
$do=null;

// javascript confirm pop up declaration
$htmlHeadXtra[] =
'<script type="text/javascript">
function confirmation (name)
{
    if (confirm("' . clean_str_for_javascript(get_lang('Are you sure to delete')) . '" + name + \'"? \'))
        {return true;}
    else
        {return false;}
}
</script>';

// Deal with interbredcrumps

$interbredcrump[]= array ('url' => get_conf('rootAdminWeb') , 'name' => get_lang('Administration'));
$nameTools = get_lang('Course list');

//------------------------
//  USED SESSION VARIABLES
//------------------------
// deal with session variables for search criteria, it depends where we come from :
// 1 ) we must be able to get back to the list that concerned the criteria we previously used (with out re entering them)
// 2 ) we must be able to arrive with new critera for a new search.

// clean session if needed from  previous search

if ( $resetFilter )
{
    unset($_SESSION['admin_course_code'        ]);
    unset($_SESSION['admin_course_intitule'    ]);
    unset($_SESSION['admin_course_category'    ]);
    unset($_SESSION['admin_course_language'    ]);
    unset($_SESSION['admin_course_access'      ]);
    unset($_SESSION['admin_course_subscription']);
}

if (isset($_REQUEST['code'        ])) $_SESSION['admin_course_code'    ] = trim($_REQUEST['code'    ]);
if (isset($_REQUEST['search'      ])) $_SESSION['admin_course_search'  ] = trim($_REQUEST['search'  ]);
if (isset($_REQUEST['intitule'    ])) $_SESSION['admin_course_intitule'] = trim($_REQUEST['intitule']);
if (isset($_REQUEST['category'    ])) $_SESSION['admin_course_category'] = trim($_REQUEST['category']);
if (isset($_REQUEST['language'    ])) $_SESSION['admin_course_language'] = trim($_REQUEST['language']);
if (isset($_REQUEST['access'      ])) $_SESSION['admin_course_access'  ] = trim($_REQUEST['access'  ]);
if (isset($_REQUEST['subscription'])) $_SESSION['admin_course_subscription'] = trim($_REQUEST['subscription']);

if ('clist' != $cfrom) $addToURL .= '&amp;offsetC=' . $offsetC;

/**
 * PARSE COMMAND
 */

if ('delete' == $cmd)
{
    $courseToDelete = claro_get_course_data($delCode);
    if ($courseToDelete) $do = 'delete';
    else
    {
        switch(claro_failure::get_last_failure())
        {
            case 'course_not_found':
                $dialogBox = get_lang('Course not found');
                break;
            default  : $dialogBox = get_lang('Course not found');
        }
    }
}

// EXECUTE
if ('delete' == $do)
{
    if (delete_course($delCode))
    {
        $dialogBox = get_lang('The course has been successfully deleted');
        $noQUERY_STRING = true;
    }
}

/**
 * PREPARE DISPLAY
 *
 * Display contain 2 part
 *
 * * Filter/search panel
 * * List of datas
 */

$sqlCourseList = prepare_get_filtred_course_list();
$myPager = new claro_sql_pager($sqlCourseList, $offsetC, get_conf('coursePerPage',20));
$sortKey = isset($_GET['sort']) ? $_GET['sort'] : 'officialCode';
$sortDir = isset($_GET['dir' ]) ? $_GET['dir' ] : SORT_ASC;
$myPager->set_sort_key($sortKey, $sortDir);
$myPager->set_pager_call_param_name('offsetC');
$courseList = $myPager->get_result_list();

/**
 * Prepare display of search/Filter panel
 */

$advanced_search_query_string = array();
$isSearched ='';

if ( !empty($_REQUEST['search']) ) $isSearched .= trim($_REQUEST['search']) . ' ';

if ( !empty($_REQUEST['code']) )
{
    $isSearched .= get_lang('Course code') . ' = ' . $_REQUEST['code'] . ' ';
    $advanced_search_query_string[] = 'code=' . urlencode($_REQUEST['code']);
}

if ( !empty($_REQUEST['intitule']) )
{
    $isSearched .= get_lang('Course title') . ' = ' . $_REQUEST['intitule'] . ' ';
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
    $isSearched .= ' <b><br />' . get_lang('Public course only') . ' </b> ';

}
if (isset($_REQUEST['access']) && $_REQUEST['access'] == 'private')
{
    $isSearched .= ' <b><br />' . get_lang('Private course only') . ' </b>  ';
}
if (isset($_REQUEST['subscription']) && $_REQUEST['subscription'] == 'allowed')
{
    $isSearched .= ' <b><br />' . get_lang('Enrolment allowed only') . ' </b>  ';
}
if (isset($_REQUEST['subscription']) && $_REQUEST['subscription'] == 'denied')
{
    $isSearched .= ' <b><br />' . get_lang('Enrolment denied only') . ' </b>  ';
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

if ( count($advanced_search_query_string) > 0 ) $addtoAdvanced = '?' . implode('&',$advanced_search_query_string);
else                                            $addtoAdvanced = '';

//finaly, form itself

if( empty($isSearched) )
{
    $title = '&nbsp;';
    $isSearched = '&nbsp;';
}
else $title = get_lang('Search on') . ' : ';

$courseDataList=array();
// Now read datas and rebuild cell content to set datagrid to display.
foreach($courseList as $numLine => $courseLine)
{
    if (    isset($_SESSION['admin_course_search'])
        && $_SESSION['admin_course_search'] != '' )
        //trick to prevent "//1" display when no keyword used in search
    {
        $bold_search = str_replace('*', '.*', $_SESSION['admin_course_search']);
        $courseLine['officialCode'] = eregi_replace("(".$bold_search.")","<b>\\1</b>", $courseLine['officialCode']);
        $courseLine['intitule'] = eregi_replace("(".$bold_search.")","<b>\\1</b>", $courseLine['intitule']);
        $courseLine['faculte'] = eregi_replace("(".$bold_search.")","<b>\\1</b>", $courseLine['faculte']);
    }

    // Official Code
    $courseDataList[$numLine]['officialCode'] = $courseLine['officialCode'];

    // Label
    $courseDataList[$numLine]['intitule'] =  '<a href="' . $clarolineRepositoryWeb . 'course/index.php?cid=' . htmlspecialchars($courseLine['sysCode']) . '">'
    .                                        $courseLine['intitule']
    .                                        '</a>';
    // Category
    $courseDataList[$numLine]['faculte'] = $courseLine['faculte'];

    // Users in course
    $courseDataList[$numLine]['qty_cm'] = '<a href="admincourseusers.php'
    .                                     '?cidToEdit=' . $courseLine['sysCode'] . $addToURL . '&amp;cfrom=clist">'

    .                                     sprintf( ( $courseLine['qty_cm'] + $courseLine['qty_stu'] > 1 ? get_lang('%2d members') : get_lang('%2d member'))
                                                 , ( $courseLine['qty_stu'] + $courseLine['qty_cm'] ) )
    .                                     '</a>'
    .                                     '<br />' . "\n" . '<small><small>' . "\n"
    .                                     sprintf( ( $courseLine['qty_cm'] > 1 ? get_lang('%2d profs') : get_lang('%2d prof'))
                                                 , $courseLine['qty_cm']) . "\n"
    .                                     sprintf( ( $courseLine['qty_stu'] > 1 ? get_lang('%2d students') : get_lang('%2d student'))
                                                 , $courseLine['qty_stu']) . "\n"
    .                                     '</small></small>' . "\n"
    ;

    // Course Settings
    $courseDataList[$numLine]['cmdSetting'] = '<a href="' . $clarolineRepositoryWeb . '/course/settings.php?adminContext=1'
    .                                         '&amp;cidReq=' . $courseLine['sysCode'] . $addToURL . '&amp;cfrom=clist">'
    .                                         '<img src="' . get_conf('imgRepositoryWeb') . 'settings.gif" alt="' . get_lang('Course settings'). '" />'
    .                                         '</a>'
    ;

    // Course Action Delete
    $courseDataList[$numLine]['cmdDelete'] = '<a href="' . $_SERVER['PHP_SELF']
    .                                        '?cmd=delete&amp;delCode=' . $courseLine['sysCode'] . $addToURL . '" '
    .                                        ' onClick="return confirmation(\'' . clean_str_for_javascript($courseLine['intitule']) . '\');">' . "\n"
    .                                        '<img src="' . get_conf('imgRepositoryWeb') . 'delete.gif" border="0" alt="' . get_lang('Delete') . '" />' . "\n"
    .                                        '</a>' . "\n"
    ;
}

// CONFIG DATAGRID.
$sortUrlList = $myPager->get_sort_url_list($_SERVER['PHP_SELF']);

$courseDataGrid = new claro_datagrid($courseDataList);

$courseDataGrid->set_colTitleList(array ( 'officialCode' => '<a href="' . $sortUrlList['officialCode'] . '">' . get_lang('Code')        . '</a>'
                                        , 'intitule'     => '<a href="' . $sortUrlList['intitule'    ] . '">' . get_lang('Course title') . '</a>'
                                        , 'faculte'      => '<a href="' . $sortUrlList['faculte'     ] . '">' . get_lang('Category')    . '</a>'
                                        , 'qty_cm'       => get_lang('Course members')
                                        , 'cmdSetting'   => get_lang('Course settings')
                                        , 'cmdDelete'    => get_lang('Delete')));

$courseDataGrid->set_colAttributeList( array ( 'qty_cm'     => array ('align' => 'right')
                                             , 'cmdSetting' => array ('align' => 'center')
                                             , 'cmdDelete'  => array ('align' => 'center')
                                             ));

$courseDataGrid->set_idLineType('none');
$courseDataGrid->set_colHead('officialCode') ;

$courseDataGrid->set_noRowMessage( get_lang('There is no course matching such criteria') . '<br />'
   .    '<a href="advancedCourseSearch.php' . $addtoAdvanced . '">' . get_lang('Search again (advanced)') . '</a>');

/** ***********************************************************************************
 * DISPLAY
 */

/** DISPLAY : Common Header */

include $includePath . '/claro_init_header.inc.php';
echo claro_html::tool_title($nameTools);
if (isset($dialogBox)) echo claro_html::message_box($dialogBox);

/**
 * DISPLAY : Search/filter panel
 */
echo '<table width="100%">' . "\n\n"
.    '<tr>' . "\n"
.    '<td align="left" valign="top">' . "\n"
.    '<b>' . $title . '</b>'
.    '</td>' . "\n"
.    '<td align="left"  valign="top">' . "\n\n"
.    '<small>' .$isSearched . '</small>'
.    '</td>' . "\n"
.    '<td align="right"  valign="top">' . "\n\n"
.    '<form action="' . $_SERVER['PHP_SELF'] . '">' . "\n"
.    '<label for="search">' . get_lang('Make new search') . ' : </label>'."\n"
.    '<input type="text" value="' . htmlspecialchars($search) . '" name="search" id="search" />' . "\n"
.    '<input type="submit" value=" ' . get_lang('Ok') . ' " />' . "\n"
.    '<input type="hidden" name="newsearch" value="yes" />' . "\n"
.    '[<a class="claroCmd" href="advancedCourseSearch.php' . $addtoAdvanced . '">'
.    get_lang('Advanced')
.    '</a>]'    . "\n"
.    '</form>'  . "\n\n"
.    '</td>'    . "\n"
.    '</tr>'    . "\n\n"
.    '</table>' . "\n\n"
;

/** DISPLAY : LIST of data */

echo $myPager->disp_pager_tool_bar($_SERVER['PHP_SELF'])
.    $courseDataGrid->render()
.    $myPager->disp_pager_tool_bar($_SERVER['PHP_SELF']);
;

/** DISPLAY : Common footer */
include $includePath . '/claro_init_footer.inc.php';


function prepare_get_filtred_course_list()
{
    $tbl_mdb_names       = claro_sql_get_main_tbl();

    $sqlFilter = array();
    // Prepare filter deal with KEY WORDS classification call
    if (isset($_SESSION['admin_course_search']))   $sqlFilter[]="(      C.`intitule`  LIKE '%". addslashes(pr_star_replace($_SESSION['admin_course_search'])) ."%'
                                                                 OR C.`fake_code` LIKE '%". addslashes(pr_star_replace($_SESSION['admin_course_search'])) ."%'
                                                                 OR C.`faculte`   LIKE '%". addslashes(pr_star_replace($_SESSION['admin_course_search'])) ."%'
                                                             )";

    //deal with ADVANCED SEARCH parmaters call
    if (isset($_SESSION['admin_course_intitule'])) $sqlFilter[] = "(C.`intitule` LIKE '%". addslashes(pr_star_replace($_SESSION['admin_course_intitule'])) ."%')";
    if (isset($_SESSION['admin_course_code']))     $sqlFilter[] = "(C.`fake_code` LIKE '%". addslashes(pr_star_replace($_SESSION['admin_course_code'])) ."%')";
    if (isset($_SESSION['admin_course_category'])) $sqlFilter[] = "(C.`faculte` LIKE '%". addslashes(pr_star_replace($_SESSION['admin_course_category'])) ."%')";
    if (isset($_SESSION['admin_course_language'])) $sqlFilter[] = "(C.`languageCourse` LIKE '%". addslashes($_SESSION['admin_course_language']) ."%')";

    if (isset($_SESSION['admin_course_access']))
    {
        if ($_SESSION['admin_course_access'] == 'private')    $sqlFilter[]= "NOT (C.`visible`=2 OR C.`visible`=3)";
        elseif ($_SESSION['admin_course_access'] == 'public') $sqlFilter[]="(C.`visible`=2 OR C.`visible`=3) ";
    }

    if (isset($_SESSION['admin_course_subscription']))   // type of subscription allowed is used
    {
        if ($_SESSION['admin_course_subscription'] == 'allowed')     $sqlFilter[] ="(C.`visible`=1 OR C.`visible`=2)";
        elseif ($_SESSION['admin_course_subscription'] == 'denied' ) $sqlFilter[] ="NOT (C.`visible`=1 OR C.`visible`=2)";
    }


    $sqlFilter = sizeof($sqlFilter) ? "WHERE " . implode(" AND ",$sqlFilter)  : "";


    // Build the complete SQL
    $sql = "SELECT  C.`fake_code` AS `officialCode`,
                    C.intitule    AS `intitule`,
                    C.faculte     AS `faculte`,
                    C.`code`      AS `sysCode`,
                    C.`directory` AS `repository`,
                    count(IF(`CU`.`statut`=" . COURSE_STUDENT . ",".COURSE_CREATOR.",null))
                                  AS `qty_stu`,
                    #count only lines where statut of user is COURSE_STUDENT

                    count(IF(`CU`.`statut`=1,1,null))
                                  AS `qty_cm`
                    #count only lines where statut of user is 1

            FROM `" . $tbl_mdb_names['course'] . "` AS C
            LEFT JOIN `" . $tbl_mdb_names['rel_course_user' ] . "` AS CU
              ON `CU`.`code_cours` = `C`.`code`
            " . $sqlFilter . "
            GROUP BY C.code";

    return $sql;


}
?>
