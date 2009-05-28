<?php // $Id$
/**
 * CLAROLINE
 *
 * List courses aivailable on the platform and prupose admin link to it
 *
 * @version 1.9 $Revision$
 *
 * @copyright 2001-2007 Universite catholique de Louvain (UCL)
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
require '../inc/claro_init_global.inc.php';

// Security check
if ( ! claro_is_user_authenticated() ) claro_disp_auth_form();
if ( ! claro_is_platform_admin() ) claro_die(get_lang('Not allowed'));

// initialisation of global variables and used libraries
require_once get_path('incRepositorySys') . '/lib/admin.lib.inc.php';
require_once get_path('incRepositorySys') . '/lib/pager.lib.php';

/**
 * Check incoming data
 */
$offsetC = isset($_REQUEST['offsetC']) ? $_REQUEST['offsetC'] : '0';
$validCmdList = array('exDelete', 'rqDelete');
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
    if (confirm("' . clean_str_for_javascript(get_lang('Are you sure to delete')) . ' \"" + name + "\" ? "))
        {return true;}
    else
        {return false;}
}
</script>';

// Deal with interbredcrumps

ClaroBreadCrumbs::getInstance()->prepend( get_lang('Administration'), get_path('rootAdminWeb') );
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
    unset($_SESSION['admin_course_visibility'  ]);
    unset($_SESSION['admin_course_subscription']);
}

if (isset($_REQUEST['code'        ])) $_SESSION['admin_course_code'        ] = trim($_REQUEST['code'    ]);
if (isset($_REQUEST['search'      ])) $_SESSION['admin_course_search'      ] = trim($_REQUEST['search'  ]);
if (isset($_REQUEST['intitule'    ])) $_SESSION['admin_course_intitule'    ] = trim($_REQUEST['intitule']);
if (isset($_REQUEST['category'    ])) $_SESSION['admin_course_category'    ] = trim($_REQUEST['category']);
if (isset($_REQUEST['language'    ])) $_SESSION['admin_course_language'    ] = trim($_REQUEST['language']);
if (isset($_REQUEST['access'      ])) $_SESSION['admin_course_access'      ] = trim($_REQUEST['access'  ]);
if (isset($_REQUEST['visibility'  ])) $_SESSION['admin_course_visibility'  ] = trim($_REQUEST['visibility'  ]);
if (isset($_REQUEST['subscription'])) $_SESSION['admin_course_subscription'] = trim($_REQUEST['subscription']);

if ('clist' != $cfrom) $addToURL .= '&amp;offsetC=' . $offsetC;

$dialogBox = new DialogBox();

/**
 * PARSE COMMAND
 */
$courseToDelete = claro_get_course_data($delCode);
if ('exDelete' == $cmd)
{
    
    if ($courseToDelete) $do = 'delete';
    else
    {
        switch(claro_failure::get_last_failure())
        {
            case 'course_not_found':
                $dialogBox->error( get_lang('Course not found') );
                break;
            default  : $dialogBox->error( get_lang('Course not found') );
        }
    }
}
elseif( 'rqDelete' == $cmd )
{
    if( $courseToDelete )
    {
        $dialogBox->question( get_lang('Are you sure to delete course %name', array('%name' => $courseToDelete['name'])).'<br/><br/>'."\n"
        .    '<a href="'.$_SERVER['PHP_SELF'].'?cmd=exDelete&amp;delCode='.$delCode.'&amp;offsetC='.$offsetC. $addToURL .'">'.get_lang('Yes').'</a>'
        .    ' | '
        .    '<a href="'.$_SERVER['PHP_SELF'].'">'.get_lang('No').'</a>'."\n");
    }
    else
    {
        $dialogBox->error( get_lang('Course not found') );
    }
}

// EXECUTE
if ('delete' == $do)
{
    if (delete_course($delCode))
    {
        $dialogBox->success( get_lang('The course has been successfully deleted') );
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


if (is_array($courseList))
{
    $tbl_mdb_names = claro_sql_get_main_tbl();

    foreach ($courseList as $courseKey => $course)
    {
    $sql ="SELECT
    count(IF(`isCourseManager`=0,1,null))
    AS `qty_stu`,
    #count only lines where user is not course manager

    count(IF(`isCourseManager`=1,1,null))
    AS `qty_cm`
    #count only lines where statut of user is 1
           FROM  `" . $tbl_mdb_names['rel_course_user'] . "`
           WHERE code_cours  = '". claro_sql_escape($course['sysCode']) ."'
          GROUP BY code_cours";


    $result = claro_sql_query_get_single_row($sql);
    $courseList[$courseKey]['qty_stu'] =  $result['qty_stu'];
    $courseList[$courseKey]['qty_cm']  =  $result['qty_cm'];
    }
}

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

if ( isset($_REQUEST['access']))
{
    $isSearched .= '<br />' . "\n";

    if ($_REQUEST['access'] == 'public' )
    {
        $isSearched .= '<b>' . get_lang('Course access') . ' : ' . get_lang('Access allowed to anybody (even without login)') . '</b>';
    }
    elseif ( $_REQUEST['access'] == 'platform' )
    {
        $isSearched .= '<b>' . get_lang('Course access') . ' : ' . get_lang('Access allowed only to platform members (user registered to the platform)') . '</b>';
    }
    elseif ( $_REQUEST['access'] == 'all' )
    {
        $isSearched .= '<b>' . get_lang('Course access') . ' : ' . get_lang('All') . '</b>';
    }
    else
    {
        $isSearched .= '<b>' . get_lang('Course access') . ' : ' . get_lang('Access allowed only to course members (people on the user list)') . '</b>';
    }
}

if ( isset($_REQUEST['subscription']) )
{
    $isSearched .= '<br />' . "\n";

    if ( $_REQUEST['subscription'] == 'allowed' )
    {
        $isSearched .= '<b>' . get_lang('Enrolment') . ' : ' . get_lang('Allowed') . '</b>';
    }
    elseif (  $_REQUEST['subscription'] == 'key' )
    {
        $isSearched .= '<b>' . get_lang('Enrolment') . ' : ' . get_lang('Allowed with enrolment key') . '</b>';
    }
    elseif (  $_REQUEST['subscription'] == 'all' )
    {
        $isSearched .= '<b>' . get_lang('Enrolment') . ' : ' . get_lang('All') . '</b>';
    }
    else
    {
        $isSearched .= '<b>' . get_lang('Enrolment') . ' : ' . get_lang('Denied') . ' </b>';
    }
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


$imgVisibilityStatus['visible'] = 'visible';
$imgVisibilityStatus['invisible'] = 'invisible';
$imgAccessStatus['private'] = 'access_locked';
$imgAccessStatus['public'] = 'access_open';
$imgAccessStatus['platform'] = 'access_platform';
$imgRegistrationStatus['open'] = 'enroll_allowed';
$imgRegistrationStatus['key'] = 'enroll_key';
$imgRegistrationStatus['close'] = 'enroll_forbidden';

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
    if ($courseLine['status'] == 'enable')
    {
        $courseDataList[$numLine]['officialCode'] = $courseLine['officialCode'];
    }
    else
    {
        $courseDataList[$numLine]['officialCode'] = '<span class="invisible" >'.$courseLine['officialCode'].'</span>';
    }

    // Label
    $courseDataList[$numLine]['intitule'] =  '<a href="' . get_path('clarolineRepositoryWeb') . 'course/index.php?cid=' . htmlspecialchars($courseLine['sysCode']) . '">'
    .                                        $courseLine['intitule']
    .                                        '</a>';
    // Category
    $courseDataList[$numLine]['faculte'] = $courseLine['faculte'];

    // Users in course
    $courseDataList[$numLine]['qty_cm'] = '<a href="admincourseusers.php'
    .                                     '?cidToEdit=' . $courseLine['sysCode'] . $addToURL . '&amp;cfrom=clist">'

    .                                     get_lang('%nb member(s)', array ( '%nb' => ($courseLine['qty_stu'] + $courseLine['qty_cm']) ) )
    .                                     '</a>'
    .                                     '<br />' . "\n" . '<small><small>' . "\n"
    .                                     get_lang('%nb course(s) manager(s)', array( '%nb' => $courseLine['qty_cm']) ) . ' - '
    .                                     get_lang('%nb student(s)', array ('%nb' => $courseLine['qty_stu']) ) . "\n"
    .                                     '</small></small>' . "\n"
    ;
    
    if ( $courseLine['registration'] == 'open' && !empty( $courseLine['registrationKey'] )  )
    {
        $regIcon = 'key';
    }
    else
    {
        $regIcon = $courseLine['registration'];
    }

    // Course Settings
    $courseDataList[$numLine]['cmdSetting'] = '<a href="' . get_path('clarolineRepositoryWeb') . 'course/settings.php?adminContext=1'
    .                                         '&amp;cidReq=' . $courseLine['sysCode'] . $addToURL . '&amp;cfrom=clist">'
    .                                         '<img src="' . get_icon_url('settings') . '" alt="" />'
    // .                                         '</a>'
    .                                         '&nbsp;&nbsp;&nbsp;'
    //.                                         '<a href="' . get_path('clarolineRepositoryWeb') . 'course/settings.php?adminContext=1'
    //.                                         '&amp;cidReq=' . $courseLine['sysCode'] . $addToURL . '&amp;cfrom=clist">'
    .                                         '<img src="' . get_icon_url( $imgVisibilityStatus[$courseLine['visibility']] ) . '" alt="" /> '
    .                                         '<img src="' . get_icon_url( $imgAccessStatus[$courseLine['access']] ) . '" alt="" /> '
    .                                         '<img src="' . get_icon_url( $imgRegistrationStatus[$regIcon] ) . '" alt="" />'
    .                                         '</a>'
    ;

    // Course Action Delete
    $courseDataList[$numLine]['cmdDelete'] = '<a href="' . $_SERVER['PHP_SELF']
    .                                        '?cmd=rqDelete&amp;delCode=' . $courseLine['sysCode'] . $addToURL . '" '
    //.                                        ' onclick="return confirmation(\'' . clean_str_for_javascript($courseLine['intitule']) . '\');"'
    .                                        ' class="delete" id="'.$courseLine['intitule'].'_'.$courseLine['sysCode'].'">' . "\n"
    .                                        '<img src="' . get_icon_url('delete') . '" border="0" alt="" />' . "\n"
    .                                        '</a>' . "\n"
    ;
}

// CONFIG DATAGRID.
$sortUrlList = $myPager->get_sort_url_list($_SERVER['PHP_SELF']);

$courseDataGrid = new claro_datagrid($courseDataList);

$courseDataGrid->set_colTitleList(array ( 'officialCode' => '<a href="' . $sortUrlList['officialCode'] . '">' . get_lang('Course code')        . '</a>'
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

$out = '';

$out .= claro_html_tool_title($nameTools);

if ( !empty($isSearched) )
{
    $dialogBox->info( '<b>' . get_lang('Search on') . '</b> : <small>' .$isSearched . '</small>' );
}

$out .= $dialogBox->render();

/**
 * DISPLAY : Search/filter panel
 */
$out .= '<table width="100%">' . "\n\n"
.    '<tr>' . "\n"
.    '<td align="left" valign="top">' . "\n"
.    '<a class="claroCmd" href="../course/create.php?adminContext=1">'
.    '<img src="' . get_icon_url('course') . '" alt="' . get_lang('Create course') . '" />'
.    get_lang('Create course')
.    '</a>'
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

$out .= $myPager->disp_pager_tool_bar($_SERVER['PHP_SELF'])
.    $courseDataGrid->render()
.    $myPager->disp_pager_tool_bar($_SERVER['PHP_SELF']);
;

$out .=
'<script type="text/javascript">
    $(document).ready(function(){
        $(".delete").each(function( i ){
            var _id = $(this).attr("id");
            var id = _id.substr(_id.lastIndexOf("_") + 1 );
            var course = _id.substr(0,_id.indexOf("_"));
            
            $(this).click(function(){
               return confirmation(" " + course ); 
            });
            $(this).attr("href","' . $_SERVER['PHP_SELF'] . '?cmd=exDelete&delCode=" + id + "'.$addToURL.'");
        });
    });
</script>';

$claroline->display->body->appendContent($out);

echo $claroline->display->render();


function prepare_get_filtred_course_list()
{
    $tbl_mdb_names       = claro_sql_get_main_tbl();

    $sqlFilter = array();
    // Prepare filter deal with KEY WORDS classification call
    if (isset($_SESSION['admin_course_search']))   $sqlFilter[]="(  C.`intitule`  LIKE '%". claro_sql_escape(pr_star_replace($_SESSION['admin_course_search'])) ."%'
                                                                 OR C.`administrativeNumber` LIKE '%". claro_sql_escape(pr_star_replace($_SESSION['admin_course_search'])) ."%'
                                                                 OR C.`faculte`   LIKE '%". claro_sql_escape(pr_star_replace($_SESSION['admin_course_search'])) ."%'
                                                             )";

    //deal with ADVANCED SEARCH parmaters call
    if (isset($_SESSION['admin_course_intitule']) && !empty($_SESSION['admin_course_intitule']) ) $sqlFilter[] = "(C.`intitule` LIKE '%". claro_sql_escape(pr_star_replace($_SESSION['admin_course_intitule'])) ."%')";
    if (isset($_SESSION['admin_course_code']) && !empty($_SESSION['admin_course_code']) )     $sqlFilter[] = "(C.`administrativeNumber` LIKE '%". claro_sql_escape(pr_star_replace($_SESSION['admin_course_code'])) ."%')";
    if (isset($_SESSION['admin_course_category']) && !empty($_SESSION['admin_course_category'])) $sqlFilter[] = "(C.`faculte` = '". claro_sql_escape(pr_star_replace($_SESSION['admin_course_category'])) ."')";
    if (isset($_SESSION['admin_course_language'])) $sqlFilter[] = "(C.`language` = '". claro_sql_escape($_SESSION['admin_course_language']) ."')";

    if (isset($_SESSION['admin_course_visibility']))
    {
        if     ($_SESSION['admin_course_visibility'] == 'invisible') $sqlFilter[]= "C.`visibility`='INVISIBLE'";
        elseif ($_SESSION['admin_course_visibility'] == 'visible'  ) $sqlFilter[]= "C.`visibility`='VISIBLE'";
    }

    if (isset($_SESSION['admin_course_access']))
    {
        if     ($_SESSION['admin_course_access'] == 'public' ) $sqlFilter[]= "C.`access`='public'";
        elseif ($_SESSION['admin_course_access'] == 'private') $sqlFilter[]= "C.`access`='private'";
        elseif ($_SESSION['admin_course_access'] == 'platform') $sqlFilter[]= "C.`access`='platform'";
    }

    if (isset($_SESSION['admin_course_subscription']))   // type of subscription allowed is used
    {
        if ($_SESSION['admin_course_subscription']     == 'allowed') $sqlFilter[]= "C.`registration`='OPEN'";
        elseif ($_SESSION['admin_course_subscription'] == 'denied' ) $sqlFilter[]= "C.`registration`='CLOSE'";
        elseif ($_SESSION['admin_course_subscription'] == 'key' ) $sqlFilter[]= "C.`registration`='OPEN' AND CHAR_LENGTH(C.`registrationKey`) != 0";
    }


    $sqlFilter = sizeof($sqlFilter) ? "WHERE " . implode(" AND ",$sqlFilter)  : "";


    // Build the complete SQL
    $sql = "SELECT  C.`administrativeNumber` AS `officialCode`,
                    C.`intitule`             AS `intitule`,
                    C.`faculte`              AS `faculte`,
                    C.`code`                 AS `sysCode`,
                    C.`visibility`           AS `visibility`,
                    C.`access`               AS `access`,
                    C.`registration`         AS `registration`,
                    C.`registrationKey`      AS `registrationKey`,
                    C.`directory`            AS `repository`,
                    C.`status`				 AS `status`
                    FROM  `" . $tbl_mdb_names['course'] . "` AS C
           " . $sqlFilter ;

    return $sql;
}


?>