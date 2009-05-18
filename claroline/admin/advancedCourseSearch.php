<?php // $Id$
/**
 * CLAROLINE
 *
 * prupose an multifield search in courses
 *
 * @version 1.9 $Revision$
 *
 * @copyright (c) 2001-2009 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @package COURSE
 * @subpackage CLADMIN
 *
 * @author Claro Team <cvs@claroline.net>
 * @author Dimitri Rambout <dimitri.rambout@uclouvain.be>
 */

$cidReset = TRUE;$gidReset = TRUE;$tidReset = TRUE;
require '../inc/claro_init_global.inc.php';

// Security check
if ( ! claro_is_user_authenticated() ) claro_disp_auth_form();
if ( ! claro_is_platform_admin() ) claro_die(get_lang('Not allowed'));

include_once get_path('incRepositorySys') . '/lib/admin.lib.inc.php';
include_once get_path('incRepositorySys') . '/lib/course.lib.inc.php';
include_once get_path('incRepositorySys') . '/lib/form.lib.php';

//declare needed tables
$tbl_mdb_names    = claro_sql_get_main_tbl();
$tbl_course_nodes = $tbl_mdb_names['category'];

// Deal with interbredcrumps  and title variable

ClaroBreadCrumbs::getInstance()->prepend( get_lang('Administration'), get_path('rootAdminWeb') );
$nameTools = get_lang('Advanced course search');

//--------------------------------------------------------------------------------------------
//  USED SESSION VARIABLES
//--------------------------------------------------------------------------------------------
// clean session of possible previous search information.

unset($_SESSION['admin_course_code'        ]);
unset($_SESSION['admin_course_letter'      ]);
unset($_SESSION['admin_course_search'      ]);
unset($_SESSION['admin_course_intitule'    ]);
unset($_SESSION['admin_course_category'    ]);
unset($_SESSION['admin_course_language'    ]);
unset($_SESSION['admin_course_access'      ]);
unset($_SESSION['admin_course_subscription']);
unset($_SESSION['admin_course_order_crit']);

//retrieve needed parameters from URL to prefill search form

if (isset($_REQUEST['access']))        $access        = $_REQUEST['access'];        else $access       = "all";
if (isset($_REQUEST['subscription']))  $subscription  = $_REQUEST['subscription'];  else $subscription = "all";
if (isset($_REQUEST['visibility']))    $visibility    = $_REQUEST['visibility'];    else $visibility   = "all";
if (isset($_REQUEST['code']))          $code          = $_REQUEST['code'];          else $code         = "";
if (isset($_REQUEST['intitule']))      $intitule      = $_REQUEST['intitule'];      else $intitule     = "";
if (isset($_REQUEST['category']))      $category      = $_REQUEST['category'];      else $category     = "";
if (isset($_REQUEST['searchLang']))    $searchLang    = $_REQUEST['searchLang'];      else $searchLang = "";

// Search needed info in db to create the right formulaire
$arrayFaculty = course_category_get_list();
$category_array = claro_get_cat_flat_list();
$category_array = array_merge(array(get_lang('All') => ''),$category_array);
$language_list = claro_get_lang_flat_list();
$language_list = array_merge(array(get_lang('All') => ''),$language_list);

//----------------------------------
// DISPLAY
//----------------------------------

$out = '';

//tool title

$out .= claro_html_tool_title($nameTools . ' : ');

$tpl = new PhpTemplate( dirname(__FILE__) . '/templates/advancedCourseSearch.tpl.php' );

$tpl->assign('code', $code);
$tpl->assign('intitule', $intitule);
$tpl->assign('category_array', $category_array);
$tpl->assign('language_list', $language_list);
$tpl->assign('access', $access);
$tpl->assign('subscription', $subscription);
$tpl->assign('visibility', $visibility);

$out .= $tpl->render();

$claroline->display->body->appendContent($out);

echo $claroline->display->render();

//NEEDED FUNCTION (to be moved in libraries)


/**
 *This function create de select box to choose categories
 *
 * @author  - < Benoît Muret >
 * @param   - elem            array     :     the faculties
 * @param   - father        string    :    the father of the faculty
 * @param    - $editFather    string    :    the faculty editing
 * @param    - $space        string    :    space to the bom of the faculty

 * @return  - void
 *
 * @desc : create de select box categories
 */

function build_select_faculty($elem,$father,$editFather,$space)
{
    if($elem)
    {
        $space.="&nbsp;&nbsp;&nbsp;";
        foreach($elem as $one_faculty)
        {
            if(!strcmp($one_faculty["code_P"],$father))
            {
                echo "<option value=\"".$one_faculty['code']."\" ".
                        ($one_faculty['code']==$editFather?"selected ":"")
                ."> ".$space.$one_faculty['code']." </option>
                ";
                build_select_faculty($elem,$one_faculty["code"],$editFather,$space);
            }
        }
    }
}


/**
 * return all courses category order by treepos
 * @return array (id, name, code, code_P, treePos, nb_childs, canHaveCoursesChild, canHaveCatChild )
 */
function  course_category_get_list()
{
    $tbl_mdb_names = claro_sql_get_main_tbl();
    $tbl_course_nodes     = $tbl_mdb_names['category'];
    $sql_searchfaculty = "
SELECT
    id,
    name,
    code,
    code_P,
    treePos,
    nb_childs,
    canHaveCoursesChild,
    canHaveCatChild
FROM `" . $tbl_course_nodes . "`
ORDER BY `treePos`";

    return claro_sql_query_fetch_all($sql_searchfaculty);
}

?>