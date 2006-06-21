<?php // $Id$
/**
 * CLAROLINE
 *
 * This tool try to repair a broken category tree
 *
 * @version 1.8 $Revision$
 * @copyright 2001-2006 Universite catholique de Louvain (UCL)
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
// build bredcrump
$nameTools        = get_lang('Repair category structure');
$interbredcrump[] = array ('url' => $rootAdminWeb, 'name' => get_lang('Administration'));
$interbredcrump[] = array ('url' => $rootAdminWeb. '/admincats.php', 'name' => get_lang('Categories'));

$htmlHeadXtra[] = '
<STYLE>
.error {color:red;}
</STYLE>
';
$analyseTreeResultMsg= null;
$controlMsg = array();

// Display variables
$view = DISP_ANALYSE;

//Get Parameters from URL or post

$validCmdList = array('repairTree', 'doAnalyse');
$cmd = (isset($_REQUEST['cmd']) && in_array($_REQUEST['cmd'],$validCmdList)? $_REQUEST['cmd'] : 'doAnalyse');

/**
 * Show or hide sub categories
 */

switch($cmd)
{
    case 'doAnalyse' :
        // analyse Tree Structure
        $errorCounter = 0;

        $category_array = claro_get_cat_flat_list();

        foreach( $category_array as $catName => $catCode )
        {
            $analyseResult = analyseCat($catCode);
            $dataAnalyseResult[] = array ( 'Code'=>$catCode
            , 'Result'=>$analyseResult?get_lang('Ok'):get_lang('Fail')
            , 'Message'=>$analyseResult?'':claro_failure::get_last_failure());
            if (! $analyseResult) $errorCounter++;

        }
        if (0 < $errorCounter) $analyseTreeResultMsg['error'][] = get_lang('%nb errors found', array('%s'=>$errorCounter));
        // analyse Course onwance
        $courseOwnanceCheck = checkCourseOwnance();

        $dgDataAnalyseResult = new claro_datagrid($dataAnalyseResult);
        $dgDataAnalyseResult->set_idLineType('numeric');
        $dgCourseOwnanceCheck = new claro_datagrid($courseOwnanceCheck);
        $dgCourseOwnanceCheck->set_idLineType('numeric');
        $dgCourseOwnanceCheck->set_colTitleList(array( get_lang('Course code'), get_lang('Unknow faculty')));

        $view = DISP_ANALYSE;
        break;
    case 'repairTree' :
        $repairResult = repairTree();
        if ($repairResult)
        {
            $repairResultMsg['success'][] = get_lang('Categories Structure is right');
        }
        else
        switch ($failure = claro_failure::get_last_failure())
        {
            case 'node_moved' :
                $repairResultMsg['warning'][] = get_lang('Node Moved, relaunch repair process to complete');
                break;
        }

        $view = DISP_REPAIR_RESULT;

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
        echo claro_html_tool_title(array('mainTitle' => 'ANALYSE RESULT', 'subTitle' => 'Tree Structure '))
        .    claro_html_msg_list($analyseTreeResultMsg, 1)
        .    $dgDataAnalyseResult->render()
        .    ($errorCounter?claro_html_button($_SERVER['PHP_SELF'] . '?cmd=repairTree','Repair','Run repair task on the tree ? ') : '' )
        .    claro_html_tool_title('Course ownance')
        .    $dgCourseOwnanceCheck->render()
        ;
        break;
    case  DISP_REPAIR_RESULT :
        echo claro_html_tool_title(array('mainTitle' => 'REPAIR RESULT', 'subTitle' => 'Tree Structure '))
        .    claro_html_msg_list($repairResultMsg, 1)
        .    claro_html_button($_SERVER['PHP_SELF'] . '?cmd=','Analyse')
        ;

        break;
    default :
        echo '<div>' . __LINE__ . ': $view = <pre>'. var_export($view,1).'</PRE></div>';
}

include $includePath . '/claro_init_footer.inc.php';

/**
 * Return course list which have an unexisting category as parent
 *
 * @author Christophe Gesché <moosh@claroline.net>
 * @since 1.8
 *
 * @return array('Course code'=>string, 'Unknow faculty'=>string)
 */
function checkCourseOwnance()
{
    $tbl_mdb_names   = claro_sql_get_main_tbl();

    $sql = "SELECT c.code    AS `Course code`,
                   c.faculte AS `Unknow faculty`
        FROM  `" . $tbl_mdb_names['course'] . "`       AS c
        LEFT JOIN  `" . $tbl_mdb_names['category']. "` AS f
        ON c.FACULTE = f.code
        WHERE f.id IS null ";
    if (($res =  claro_sql_query_fetch_all($sql))) return $res;
    else                                           return claro_failure::set_failure('QUERY_ERROR'.__LINE__);


}
?>