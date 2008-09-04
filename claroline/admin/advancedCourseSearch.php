<?php // $Id$
/**
 * CLAROLINE
 *
 * prupose an multifield search in courses
 *
 * @version 1.9 $Revision$
 *
 * @copyright (c) 2001-2007 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @package COURSE
 * @subpackage CLADMIN
 *
 * @author Claro Team <cvs@claroline.net>
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

//header and bredcrump display

include get_path('incRepositorySys') . '/claro_init_header.inc.php';

//tool title

echo claro_html_tool_title($nameTools . ' : ');

?>
<form action="admincourses.php" method="get" >
<table border="0">
<tr>
  <td align="right">
   <label for="code"><?php echo get_lang('Administrative code')?></label> : <br />
  </td>
  <td colspan="3">
    <input type="text" size="40" name="code" id="code" value="<?php echo htmlspecialchars($code); ?>"/>
  </td>
</tr>

<tr>
  <td align="right">
   <label for="intitule"><?php echo get_lang('Course title')?></label> :  <br />
  </td>
  <td colspan="3">
    <input type="text" size="40" name="intitule"  id="intitule" value="<?php echo htmlspecialchars($intitule);?>"/>
  </td>
</tr>

<tr>
  <td align="right">
   <label for="category"><?php echo get_lang('Category')?></label> : <br />
  </td>
  <td colspan="3">
  <?php echo claro_html_form_select( 'category'
                                 , $category_array
                                 , ''
                                 , array('id'=>'category'))
                                 ; ?>
</td>
</tr>
<tr>
<td align="right">
<label for="searchLang"><?php echo get_lang('Language')?></label> : <br />
</td>
<td colspan="3">
<?php echo claro_html_form_select( 'searchLang'
                                 , $language_list
                                 , ''
                                 , array('id'=>'searchLang'))
                                 ; ?>    </td>
</tr>

<tr>
  <td align="right">
   <?php echo get_lang('Course access') ?>   :
  </td>
  <td>
   <input type="radio" name="access" value="public"  id="access_public"  <?php if ($access=="public") echo "checked";?>  />
   <label for="access_public"><?php echo get_lang('Public') ?></label>
  </td>
  <td>
      <input type="radio" name="access" value="platform" id="access_platform" <?php if ($access=="platform") echo "checked";?> />
    <label for="access_platform"><?php echo get_lang('Platform') ?></label>
  </td>
  <td>
      <input type="radio" name="access" value="private" id="access_private" <?php if ($access=="private") echo "checked";?> />
    <label for="access_private"><?php echo get_lang('Private') ?></label>
  </td>
  <td>
      <input type="radio" name="access" value="all"        id="access_all"     <?php if ($access=="all") echo "checked";?> />
    <label for="access_all"><?php echo get_lang('All') ?></label>
  </td>
</tr>

<tr>
  <td align="right">
      <?php echo get_lang('Enrolment') ?>    :
  </td>
  <td>
      <input type="radio" name="subscription" value="allowed" id="subscription_allowed" <?php if ($subscription=="allowed") echo "checked";?> />
      <label for="subscription_allowed"><?php echo get_lang('Allowed') ?></label>
  </td>
  <td>
      <input type="radio" name="subscription" value="key"  id="subscription_key" <?php if ($subscription=="key") echo "checked";?> />
    <label for="subscription_key"><?php echo get_lang('Allowed with enrolment key') ?></label>
  </td>
  <td>
      <input type="radio" name="subscription" value="denied"  id="subscription_denied" <?php if ($subscription=="denied") echo "checked";?> />
    <label for="subscription_denied"><?php echo get_lang('Denied') ?></label>
  </td>
  <td>
      <input type="radio" name="subscription" value="all"  id="subscription_all" <?php if ($subscription=="all") echo "checked";?> />
    <label for="subscription_all"><?php echo get_lang('All') ?></label>
  </td>
</tr>

<tr>
  <td align="right">
      <?php echo get_lang('Visibility') ?>    :
  </td>
  <td>
      <input type="radio" name="visibility" value="visible" id="visibility_show" <?php if ($visibility=="visible") echo "checked";?> />
      <label for="visibility_show"><?php echo get_lang('Show') ?></label>
  </td>
  <td>
      <input type="radio" name="visibility" value="invisible"  id="visibility_hidden" <?php if ($visibility=="invisible") echo "checked";?> />
      <label for="visibility_hidden"><?php echo get_lang('Hidden') ?></label>
  </td>
  <td>
      <input type="radio" name="visibility" value="all"  id="visibility_all" <?php if ($visibility == "all") echo "checked";?> />
    <label for="visibility_all"><?php echo get_lang('All') ?></label>
  </td>
</tr>

<tr>
  <td>

  </td>
  <td colspan="3">
    <input type="submit" class="claroButton" value="<?php echo get_lang('Search course')?>"  />
  </td>
</tr>
</table>
</form>
<?php
include get_path('incRepositorySys') . '/claro_init_footer.inc.php';

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