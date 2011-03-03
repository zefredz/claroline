<?php // $Id$
/**
 * CLAROLINE
 *
 * This tool try to repair a broken category tree
 *
 * @version 1.9 $Revision$
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @see http://www.claroline.net/wiki/index.php/CLTREE
 *
 * @package CLTREE
 *
 * @author Claro Team <cvs@claroline.net>
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 * @deprecated since 1.10
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
if ( ! claro_is_user_authenticated() ) claro_disp_auth_form();
if ( ! claro_is_platform_admin() ) claro_die(get_lang('Not allowed'));

include_once get_path('incRepositorySys') . '/lib/course.lib.inc.php';
include_once get_path('incRepositorySys') . '/lib/faculty.lib.inc.php';

// build bredcrump
ClaroBreadCrumbs::getInstance()->prepend( get_lang('Categories'), get_path('rootAdminWeb').'admincats.php' );
ClaroBreadCrumbs::getInstance()->prepend( get_lang('Administration'), get_path('rootAdminWeb') );
$nameTools        = get_lang('Repair category structure');

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
        $dataAnalyseResult=array();
        foreach( $category_array as $catName => $catCode )
        {
            $analyseResult = analyseCat($catCode);
            $dataAnalyseResult[] = array ( 'Code'=>$catCode
            , 'Result'=>$analyseResult?get_lang('Ok'):get_lang('Fail')
            , 'Message'=>$analyseResult?'':claro_failure::get_last_failure());
            if (! $analyseResult) $errorCounter++;

        }
        $dgDataAnalyseResult = new claro_datagrid($dataAnalyseResult);
        $dgDataAnalyseResult->set_idLineType('numeric');

        $dgDataAnalyseResult->set_noRowMessage( get_lang('There is no category'));
        $dgDataAnalyseResult->set_colTitleList(array ( 'Code' =>  get_lang('Code'),
        'Result' =>  get_lang('Result'),
        'Message' =>  get_lang('Message'),));

        if (0 < $errorCounter) $analyseTreeResultMsg['error'][] = get_lang('%nb errors found', array('%nb'=>$errorCounter));
        // analyse Course onwance
        if (false === $courseOwnanceCheck = checkCourseOwnance())
        {
            $courseOwnanceCheck = array();
        }
            $dgCourseOwnanceCheck = new claro_datagrid($courseOwnanceCheck);
            $dgCourseOwnanceCheck->set_idLineType('numeric');
            $dgCourseOwnanceCheck->set_colTitleList(array( get_lang('Course code'), get_lang('Unknow faculty')));

        $view = DISP_ANALYSE;
        break;
    case 'repairTree' :
        $repairResult = repairTree();
        if ($repairResult)
        {
            $repairResultMsg['success'][] = get_lang('Categories structure is right');
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
$out = '';

/**
* Information edit for create or edit a category
*/

switch ($view)
{
    case DISP_ANALYSE :
        $out .= claro_html_tool_title(array('mainTitle' => 'ANALYSE RESULT', 'subTitle' => 'Tree Structure '))
        .    claro_html_msg_list($analyseTreeResultMsg, 1)
        .    $dgDataAnalyseResult->render()
        .    ($errorCounter?claro_html_button($_SERVER['PHP_SELF'] . '?cmd=repairTree','Repair','Run repair task on the tree ? ') : '' )
        .    claro_html_tool_title('Course ownance')
        .    $dgCourseOwnanceCheck->render()
        ;
        break;
    case  DISP_REPAIR_RESULT :
        $out .= claro_html_tool_title(array('mainTitle' => 'REPAIR RESULT', 'subTitle' => 'Tree Structure '))
        .    claro_html_msg_list($repairResultMsg, 1)
        .    claro_html_button($_SERVER['PHP_SELF'] . '?cmd=','Analyse')
        ;

        break;
    default :
        $out .= '<div>' . __LINE__ . ': $view = <pre>'. var_export($view,1).'</PRE></div>';
}

$claroline->display->body->appendContent($out);

echo $claroline->display->render();

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

    if (false !== ($res =  claro_sql_query_fetch_all($sql))) return $res;
    else
        return claro_failure::set_failure('QUERY_ERROR_'.__LINE__ );


}
?>