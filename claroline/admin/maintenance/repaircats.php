<?php // $Id$
/**
 * CLAROLINE
 *
 * This tool try to repair a broken category tree
 *
 * @version 1.8 $Revision$
 * @copyright 2001-2005 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @see http://www.claroline.net/wiki/index.php/CLTREE
 *
 * @package CLTREE
 *
 * @author Claro Team <cvs@claroline.net>
 *
 */
define ('DISP_ANALYSE', __LINE__);
define ('DISP_REPAIR_RESULT', __LINE__);

$cidReset = TRUE;
$gidReset = TRUE;
$tidReset = TRUE;

// include claro main global
require '../../inc/claro_init_global.inc.php';

// check if user is logged as administrator
if ( ! $_uid ) claro_disp_auth_form();
if ( ! $is_platformAdmin ) claro_die(get_lang('Not allowed'));

include_once $includePath . '/lib/course.lib.inc.php';
include_once $includePath . '/lib/faculty.lib.inc.php';
include_once $includePath . '/lib/datagrid.lib.php';

// build bredcrump
$nameTools        = get_lang('CategoriesRepairs');
$interbredcrump[] = array ('url' => $rootAdminWeb, 'name' => get_lang('Administration'));
$interbredcrump[] = array ('url' => $rootAdminWeb. '/admincats.php', 'name' => get_lang('Categories'));

$htmlHeadXtra[] = '
<STYLE>
.error {color:red;}
</STYLE>
';
$analyseTreeResultMsg= null;
// get table name
$tbl_mdb_names   = claro_sql_get_main_tbl();
$tbl_course      = $tbl_mdb_names['course'  ];
$tbl_course_node = $tbl_mdb_names['category'];

$controlMsg = array();

// Display variables
$view = DISP_ANALYSE;

//Get Parameters from URL or post

$cmd = ((isset($_REQUEST['cmd']) && !empty($_REQUEST['cmd']))? $_REQUEST['cmd'] : 'doAnalyse');

/**
 * Show or hide sub categories
 */

switch($cmd)
{
    case 'doAnalyse' :
    {
        // analyse Tree Structure
        $errorCounter =0;

        $category_array = claro_get_cat_flat_list();
        foreach (array_keys($category_array) as $catCode)
        {
            $analyseResult = analyseCat($catCode);
            $dataAnalyseResult[] = array ( 'Code'=>$catCode
                                         , 'Result'=>$analyseResult?get_lang('Ok'):get_lang('Fail')
                                         , 'Message'=>$analyseResult?'':claro_failure::get_last_failure());
            if (! $analyseResult) $errorCounter++;

        }
        if ($errorCounter == 1)    $analyseTreeResultMsg['error'][] = get_lang('One error found');
        elseif ($errorCounter > 1) $analyseTreeResultMsg['error'][] = sprintf(get_lang('%s errors found'), $errorCounter);
        // analyse Course onwance
        $sql = "SELECT c.code `Course code`, c.faculte `Unknow faculty`
        FROM  `" . $tbl_course . "`  c
        LEFT JOIN  `" . $tbl_course_node. "` f
        ON c.FACULTE = f.code
        WHERE f.id IS null ";
        $courseOwnanceCheck = claro_sql_query_fetch_all($sql);
        $view = DISP_ANALYSE;
    }
    break;
    case 'repairTree' :
    {
       $repairResult = repairTree();
       if ($repairResult)
       {
           $repairResultMsg['success'][] = get_lang('CategoriesStructureOK');
       }
       else
       switch ($failure = claro_failure::get_last_failure())
       {
           case 'node_moved' :
           {
                $repairResultMsg['warning'][] = get_lang('Node Moved, relaunch repair process to complete');
           } break;
           case defaut :
           {

           }

       }

       $view = DISP_REPAIR_RESULT;
    }
    break;
}

/**
 * prepare display
 */


/**
 * Display
 */
// display claroline header
include $includePath . '/claro_init_header.inc.php';

/**
  * Information edit for create or edit a category
  */

switch ($view)
{
    case DISP_ANALYSE :
    {
        echo claro_disp_tool_title(array('mainTitle' => 'ANALYSE RESULT', 'subTitle' => 'Tree Structure '))
        .    claro_disp_msg_arr($analyseTreeResultMsg, 1)
        .    claro_disp_datagrid($dataAnalyseResult, array('idLine' => 'numeric'))
        .    ($errorCounter?claro_disp_button($_SERVER['PHP_SELF'] . '?cmd=repairTree','Repair','Run repair task on the tree ? ') : '' )
        .    claro_disp_tool_title('Course ownance')
        .    claro_disp_datagrid($courseOwnanceCheck , array('idLine' => 'numeric'
                                                            ,'idLineShift' => 10
                                                            ,'colTitleList' => array( get_lang('Code')
                                                                                    , get_lang('Unknow faculty'))
                                                            ,))
        ;
    }
    break;
    case  DISP_REPAIR_RESULT :
    {
        echo claro_disp_tool_title(array('mainTitle' => 'REPAIR RESULT', 'subTitle' => 'Tree Structure '))
        .    claro_disp_msg_arr($repairResultMsg, 1)
        .    claro_disp_button($_SERVER['PHP_SELF'] . '?cmd=','Analyse')
        ;
    }
    break;
    default :
    {
        echo '<div>'.__LINE__.': $view = <pre>'. var_export($view,1).'</PRE></div>';
    }
}


include $includePath . '/claro_init_footer.inc.php';


/**
 * display data array in a <table>
 *
 * @param array $dataGrid array of data
 * @param array $option array of options
 * @return string html stream
 *
 * $dataGrid[]=array('nom'=>'dubois', 'prenom'=>'jean');
 * $dataGrid[]=array('nom'=>'dupont', 'prenom'=>'pol');
 * $dataGrid[]=array('nom'=>'durand', 'prenom'=>'simon');
 */
/*
function claro_disp_datagrid($dataGrid, $option = null)
{
    if(is_null($option) || ! is_array($option) )  $option=array();

    if (! array_key_exists('idLine',      $option)) $option['idLine'] = 'blank';
    if (! array_key_exists('idLineShift', $option)) $option['idLineShift'] = 1;
    if (! array_key_exists('colTitleList', $option)) $option['colTitleList'] = array_keys($dataGrid[0]);



    $stream = '';
    if (is_array($dataGrid) && count($dataGrid))
    {
        $stream .= '<table class="claroTable emphaseLine" width="100%" border="0" cellspacing="2">'
        .          '<thead>' . "\n"
        .          '<tr class="headerX" align="center" valign="top">' . "\n"
        .          '<th>'
        .          '</th>' . "\n"
        ;
        $i=0;
        foreach ($option['colTitleList'] as $colTitle)
            $stream .= '<th scope="col" id="c' . $i++ . '" >' . $colTitle . '</th>' . "\n";

        $stream .= '</tr>' . "\n"
        .          '</thead>' . "\n"
        ;

        if (array_key_exists('dispCounter',$option))
        {
            $stream .= '<tfoot>' . "\n"
            .          '<tr class="headerX" align="center" valign="top">' . "\n"
            .          '<td>' . "\n"
            .          '</td>' . "\n"
            .          '<td>' . "\n"
            .          count($dataGrid)
            .          '</td>' . "\n"
            .          '</tr>' . "\n"
            .          '</tr>' . "\n"
            .          '</tfoot>' . "\n"
            ;

        }

        $stream .= '<tbody>' . "\n"
        ;
        foreach ($dataGrid as $key => $dataLine)
        {
            switch ($option['idLine'])
            {
                case 'blank'   : $idLine = '';   break;
                case 'numeric' : $idLine = $key + $option['idLineShift']; break;
                default        : $idLine = '';   break;
            }

            $stream .= '<tr>' . "\n"
            .          '<td>' . $idLine .'</td>' . "\n"
            ;
            $i=0;
            foreach ($dataLine as $dataCell)
            {
                $stream .= '<td headers="c' . $i++ . '">';
                $stream .= $dataCell;
                $stream .= '</td>' . "\n";
            }
            $stream .= '</tr>' . "\n";

        }
        $stream .= '</tbody>' . "\n"
        .          '</table>' . "\n"
        ;

    }

    return $stream;

}

*/
?>