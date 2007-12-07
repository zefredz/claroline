<?php // $Id$
/**
 * CLAROLINE 
 *
 * prupose an multifield search in courses
 *
 * @version 1.7 $Revision$
 *
 * @copyright (c) 2001-2005 Universite catholique de Louvain (UCL)
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
if ( ! $_uid ) claro_disp_auth_form();
if ( ! $is_platformAdmin ) claro_die($langNotAllowed);

include($includePath . '/lib/admin.lib.inc.php');
include_once($includePath . '/lib/course.lib.inc.php');
include_once($includePath . '/lib/form.lib.php');

//declare needed tables
$tbl_mdb_names    = claro_sql_get_main_tbl();
$tbl_course_nodes = $tbl_mdb_names['category'];

// Deal with interbredcrumps  and title variable

$interbredcrump[]= array ('url'=>$rootAdminWeb, 'name'=> $langAdministration);
$nameTools = $langSearchCourseAdvanced;

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

if (isset($_REQUEST['access']))        $access        = $_REQUEST['access'];        else $access = "";
if (isset($_REQUEST['subscription']))  $subscription  = $_REQUEST['subscription'];  else $subscription = "";
if (isset($_REQUEST['code']))          $code          = $_REQUEST['code'];          else $code = "";
if (isset($_REQUEST['intitule']))      $intitule      = $_REQUEST['intitule'];      else $intitule = "";
if (isset($_REQUEST['category']))      $category      = $_REQUEST['category'];      else $category = "";
if (isset($_REQUEST['language']))      $language      = $_REQUEST['language'];      else $language = "";

// Search needed info in db to create the right formulaire
$arrayFaculty = course_category_get_list();
$category_array = claro_get_cat_flat_list();
$category_array = array_merge(array('' => $langAll),$category_array);
$language_list = claro_get_lang_flat_list();
$language_list = array_merge(array('' => $langAll),$language_list);

//----------------------------------
// DISPLAY
//----------------------------------

//header and bredcrump display

include($includePath . '/claro_init_header.inc.php' );

//tool title

echo claro_disp_tool_title($nameTools . ' : ');

?>

<form action="admincourses.php" method="GET" >
<table border="0">
<tr>
  <td align="right">
   <label for="code"><?php echo $langOfficialCode?></label> : <br>
  </td>
  <td colspan="3">
    <input type="text" size="40" name="code" id="code" value="<?php echo htmlspecialchars($code); ?>"/>
  </td>
</tr>

<tr>
  <td align="right">
   <label for="intitule"><?php echo $langCourseTitle?></label> :  <br>
  </td>
  <td colspan="3">
    <input type="text" size="40" name="intitule"  id="intitule" value="<?php echo htmlspecialchars($intitule);?>"/>
  </td>
</tr>

<tr>
  <td align="right">
   <label for="category"><?php echo $langCategory?></label> : <br>
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
   <label for="language"><?php echo $langLanguage?></label> : <br>
  </td>
  <td colspan="3">
    <?php echo claro_html_form_select( 'language'
                                 , $language_list
                                 , ''
                                 , array('id'=>'language'))
                                 ; ?>
    </td>
</tr>

<tr>
  <td align="right">
   <?php echo $langCourseAccess ?> 
   :
  </td>
  <td>
   <input type="radio" name="access" value="public"  id="access_public"  <?php if ($access=="public") echo "checked";?> >
   <label for="access_public"><?php echo $langPublic ?></label>
  </td>
  <td>
      <input type="radio" name="access" value="private" id="access_private" <?php if ($access=="private") echo "checked";?>>
    <label for="access_private"><?php echo $langPrivate ?></label>
  </td>
  <td>
      <input type="radio" name="access" value=""        id="access_all"     <?php if ($access=="") echo "checked";?>>
    <label for="access_all"><?php echo $langAll ?></label>
  </td>
</tr>

<tr>
  <td align="right">
      <?php echo $langSubscription ?> 
    :
  </td>
  <td>
      <input type="radio" name="subscription" value="allowed" id="subscription_allowed" <?php if ($subscription=="allowed") echo "checked";?>>
    <label for="subscription_allowed"><?php echo $langAllowed ?></label>
  </td>
  <td>
      <input type="radio" name="subscription" value="denied"  id="subscription_denied" <?php if ($subscription=="denied") echo "checked";?>>
    <label for="subscription_denied"><?php echo $langDenied ?></label>
  </td>
  <td>
      <input type="radio" name="subscription" value=""  id="subscription_all" <?php if ($subscription=="") echo "checked";?>>
    <label for="subscription_all"><?php echo $langAll ?></label>
  </td>
</tr>

<tr>
  <td>

  </td>
  <td colspan="3">
    <input type="submit" class="claroButton" value="<?php echo $langSearchCourse?>" >
  </td>
</tr>
</table>
</form>
<?php
include($includePath . '/claro_init_footer.inc.php');

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
 * @return array (id, name, code, code_P, bc, treePos, nb_childs, canHaveCoursesChild, canHaveCatChild )
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
    bc,
    treePos,
    nb_childs,
    canHaveCoursesChild,
    canHaveCatChild 
FROM `" . $tbl_course_nodes . "` 
ORDER BY `treePos`";
    
    return claro_sql_query_fetch_all($sql_searchfaculty);
}

?>
