<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );
/**
 * CLAROLINE
 *
 * @version 1.8 $Revision$
 *
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @see http://www.claroline.net/wiki/CLCRS/
 *
 * @package COURSE
 *
 * @author Claro Team <cvs@claroline.net>
 * @author Muret Benoît <muret_ben@hotmail.com>
 *
 */

/**
  * Delete a directory
  * @param string $dir    the directory deleting
  *
  * @return boolean whether success true
  *
  */
function delete_directory($dir)
{
    $deleteOk = true;

    $current_dir = opendir($dir);

      while($entryname = readdir($current_dir))
      {
         if(is_dir("$dir/$entryname") && ($entryname != "." && $entryname != '..'))
         {
               delete_directory("${dir}/${entryname}");
         }
        elseif($entryname != '.' && $entryname != '..')
        {
               unlink("${dir}/${entryname}");
         }
      }

      closedir($current_dir);
      rmdir(${dir}."/");
      return $deleteOk;
}

/**
  * Create a command to create a selectBox with the language
  * @param string $selected the language selected
  * @return the command to create the selectBox
  * @todo merge this with  claro_disp_select_box
  */

function create_select_box_language($selected=NULL)
{
    $arrayLanguage = language_exists();
    foreach($arrayLanguage as $entries)
    {
        $selectBox .= '<option value="' . $entries . '" ';

        if ($entries == $selected)
            $selectBox .= ' selected ';

        $selectBox .= '>' . $entries;

        global $langNameOfLang;
        if (!empty($langNameOfLang[$entries]) && $langNameOfLang[$entries] != '' && $langNameOfLang[$entries] != $entries)
            $selectBox .= ' - ' . $langNameOfLang[$entries];

        $selectBox .= '</option>' . "\n";
    }

    return $selectBox;
}

/**
  * Return an array with the language
  * @return an array with the language
  */
function language_exists()
{
    global $clarolineRepositorySys;
    $dirname = $clarolineRepositorySys . 'lang/';

    if($dirname[strlen($dirname)-1]!='/')
        $dirname.='/';

    //Open the repertoy
    $handle=opendir($dirname);

    //For each reportery in the repertory /lang/
    while ($entries = readdir($handle))
    {
        //If . or .. or CVS continue
        if ($entries=='.' || $entries=='..' || $entries=='CVS')
            continue;

        //else it is a repertory of a language
        if (is_dir($dirname.$entries))
        {
            $arrayLanguage[] = $entries;
        }
    }
    closedir($handle);

    return $arrayLanguage;
}

/**
 * build the <option> element with categories where we can create/have courses
 *
 * @param the code of the preselected categorie
 * @param the separator used between a cat and its paretn cat to display in the <select>
 * @return echo all the <option> elements needed for a <select>.
 *
 */

function build_editable_cat_table($selectedCat = null, $separator = "&gt;")
{
    $tbl_mdb_names = claro_sql_get_main_tbl();
    $tbl_category        = $tbl_mdb_names['category'];

    $sql = " SELECT code, code_P, name, canHaveCoursesChild
               FROM `" . $tbl_category . "`
               ORDER BY `name`";
    $result = claro_sql_query($sql);
    // first we get the categories available in DB from the SQL query result in parameter

    while ($myfac = mysql_fetch_array($result))
    {
        $categories[$myfac['code']]['code']   = $myfac['code'];
        $categories[$myfac['code']]['parent'] = $myfac['code_P'];
        $categories[$myfac['code']]['name']   = $myfac['name'];
        $categories[$myfac['code']]['childs'] = $myfac['canHaveCoursesChild'];
    }

    // then we build the table we need : full path of editable cats in an array

    $tableToDisplay = array();
    echo '<select name="faculte" id="faculte">' . "\n";
    foreach ($categories as $cat)
    {
        if ( $cat["childs"] == 'TRUE' )
        {

            echo '<option value="' . $cat['code'] . '"';
            if ($cat["code"]==$selectedCat) echo ' selected ';
            echo '>';
            $tableToDisplay[$cat['code']]= $cat;
            $parentPath  = get_full_path($categories, $cat['code'], $separator);

            $tableToDisplay[$cat['code']]['fullpath'] = $parentPath;
            echo '(' . $tableToDisplay[$cat['code']]['fullpath'] . ') ' . $cat['name'];
            echo '</option>' . "\n";
        }
    }
    echo '</select>' . "\n";

    return $tableToDisplay;
}


/**
 * build the <option> element with categories where we can create/have courses
 *
 * @param the code of the preselected categorie
 * @param the separator used between a cat and its paretn cat to display in the <select>
 * @return echo all the <option> elements needed for a <select>.
 *
 */


function claro_get_cat_list()
{
    $tbl_mdb_names = claro_sql_get_main_tbl();
    $tbl_category  = $tbl_mdb_names['category'];

    $sql = " SELECT code, code_P, name, canHaveCoursesChild, treePos
               FROM `" . $tbl_category . "`
               ORDER BY `treePos`";
    return claro_sql_query_fetch_all($sql);

}


function claro_get_cat_flat_list($separator = ' > ')
{
    $fac_list = claro_get_cat_list();
    $categories = array();
    $fac_array = array();
    if(is_array($fac_list))
    foreach ($fac_list as $myfac)
    {
        $categories[$myfac['code']]['treePos'] = $myfac['treePos'];
        $categories[$myfac['code']]['code']    = $myfac['code'];
        $categories[$myfac['code']]['parent']  = $myfac['code_P'];
        $categories[$myfac['code']]['name']    = $myfac['name'];
        $categories[$myfac['code']]['childs']  = $myfac['canHaveCoursesChild'];
    }

    // then we build the table we need : full path of editable cats in an array

    foreach ($categories as $cat)
    {
        if ( $cat['childs'] == 'TRUE' )
        {
            $label = '('
            .   get_full_path($categories, $cat['code'], $separator)
            .   ') '
            .   htmlspecialchars($cat['name'])
            ;
            
            $fac_array[$label] = $cat['code'];
        }
    }

    return $fac_array;
}

/**
 * Recursive function to get the full categories path of a specified categorie
 *
 * @param table of all the categories, 2 dimension tables, first dimension for cat codes, second for names,
 *  parent's cat code.
 * @param $catcode   string the categorie we want to have its full path from root categorie
 * @param $separator string
 * @return void
  */


function get_full_path($categories, $catcode = NULL, $separator = ' > ')
{
    //Find parent code

    $parent = null;

    foreach ($categories as $currentCat)
    {
        if (( $currentCat['code'] == $catcode))
        {
            $parent       = $currentCat['parent'];
            $childTreePos = $currentCat['treePos']; // for protection anti loop
        }
    }
    // RECURSION : find parent categorie in table
    if ($parent == null)
    {
        return $catcode;
    }

    foreach ($categories as $currentCat)
    {
        if (($currentCat['code'] == $parent))
        {

            if ($currentCat['treePos'] > $childTreePos ) return claro_failure::set_failure('loop_in_structure');

            return get_full_path($categories, $parent, $separator)
            .      $separator
            .      $catcode
            ;

        }
    }
}


function claro_get_lang_flat_list()
{
    $language_array = claro_get_language_list();

    // following foreach  build the array of selectable  items
    if(is_array($language_array))
    foreach ($language_array as $languageCode => $this_language)
    {
        $languageLabel = '';
        if (   !empty($this_language['langNameCurrentLang'])
            && $this_language['langNameCurrentLang'] != ''
            && $this_language['langNameCurrentLang'] != $this_language['langNameLocaleLang'])
            $languageLabel  .=  $this_language['langNameCurrentLang'] . ' - ';
        $languageLabel .=  $this_language['langNameLocaleLang'];

        $language_flat_list[ucwords($languageLabel)] = $languageCode;
    }
    asort($language_flat_list);
    return $language_flat_list;
}

/**
 * Display course form
 *
 * $_course['title']
 * $_course['officialCode']
 * $_course['titular']
 * $_course['email']
 * $_course['category']
 * $_course['departmentName']
 * $_course['departmentUrl']
 * $_course['language']
 * $_course['access']
 * $_course['enrolment']
 * $_course['enrolmentKey']
 */

function course_display_form ($course, $cid)
{
    global $clarolineRepositoryWeb, $imgRepositoryWeb;

    $languageList = claro_get_lang_flat_list();
    $categoryList = claro_get_cat_flat_list();

    if ( empty($course['language']) ) $course['language'] = get_conf('platformLanguage');

    if ( ! in_array($course['category'],$categoryList) )
    {
        $course['category'] = 'choose_one';
        $categoryList = array_merge( array(get_lang('Choose one')=>'choose_one'), $categoryList);
    }

	if ( is_null($course['access']) )
	{
		$course['access'] = get_conf('defaultVisibilityForANewCourse') == 2 or get_conf('defaultVisibilityForANewCourse') == 3 ? true : false;
	}
	
	if ( is_null($course['enrolment']) )
	{
		$course['enrolment'] = get_conf('defaultVisibilityForANewCourse') == 1 or get_conf('defaultVisibilityForANewCourse') == 2 ? true : false;
	}
	
    $html = '';

    $html .= '<form method="post" action="' . $_SERVER['PHP_SELF'] . '">' . "\n"
        . '<table  cellpadding="3" border="0">' . "\n" ;

    // Course title

    $html .= '<tr>' . "\n"
        . '<td align="right">'
        . '<label for="course_title">'
        . (get_conf('human_label_needed') ? '<span class="required">*</span>':'') . get_lang('Course title')
        .'</label>&nbsp;:</td>' 
        . '<td><input type="text" name="course_title" id="course_title" value="' . htmlspecialchars($course['title']) . '" size="60"></td>'
        . '</tr>' . "\n" ;

    // Course code

    $html .= '<tr>' . "\n"
        . '<td align="right">'
        . '<label for="course_code">' 
        . (get_conf('human_code_needed') ? '<span class="required">*</span>' :'') . get_lang('Course code')
        . '</label>&nbsp;:</td>'
        . '<td><input type="text" id="course_code" name="course_code" value="' . htmlspecialchars($course['officialCode']) . '" size="20"></td>'
        . '</tr>' . "\n" ;

    // Course titular

    $html .= '<tr>' . "\n"
        . '<td align="right">'
        . '<label for="course_titular">' . get_lang('Lecturer(s)') . '</label>&nbsp;:</td>'
        . '<td><input type="text"  id="course_titular" name="course_titular" value="' . htmlspecialchars($course['titular']) . '" size="60"></td>'
        . '</tr>' . "\n" ;

    // Course email

    $html .= '<tr>' . "\n"
        . '<td align="right">'
        . '<label for="course_email">'
        . (get_conf('course_email_needed')?'<span class="required">*</span>':'') . get_lang('Email')
        . '</label>&nbsp;:</td>'
        . '<td><input type="text" id="course_email" name="course_email" value="' . htmlspecialchars($course['email']) . '" size="60" maxlength="255"></td>'
        . '</tr>' . "\n";

    // Course category select box

    $html .= '<tr>' . "\n"
        . '<td align="right">'
        . '<label for="course_category"><span class="required">*</span>' . get_lang('Category') . '</label> :</td>'
        . '<td>'
        . claro_html_form_select( 'course_category', $categoryList, $course['category'], array('id'=>'course_category') )
        . '</td>'
        . '</tr>' . "\n" ;

    // Course department name

    $html .= '<tr valign="top">' . "\n"
        . '<td align="right"><label for="course_dept_name">' . get_lang('Department') . '</label>&nbsp;: </td>'
        . '<td><input type="text" name="course_dept_name" id="course_dept_name" value="' . htmlspecialchars($course['departmentName']) . '" size="20" maxlength="30"></td>'
        . '</tr>' . "\n" ;

    // Course department url

    $html .= '<tr valign="top" >' . "\n"
        . '<td align="right" nowrap="nowrap"><label for="course_dept_url" >' . get_lang('Department URL') . '</label>&nbsp;:</td>'
        . '<td><input type="text" name="course_dept_url" id="course_dept_url" value="' . htmlspecialchars($course['departmentUrl']) . '" size="60" maxlength="180"></td>'
        . '</tr>' . "\n" ;

    // Course language select box

    $html .= '<tr valign="top" >' . "\n"
        . '<td align="right">'
        . '<label for="course_language"><span class="required">*</span>' . get_lang('Language') . '</label>&nbsp;:</td>'
        . '<td>'
        . claro_html_form_select('course_language', $languageList, $course['language'], array('id'=>'course_language'))
        . '</td>'
        . '</tr>' . "\n" ;

    // Course access

    $html .= '<tr valign="top" >' . "\n"
        . '<td align="right" nowrap>' . get_lang('Course access') . '&nbsp;:</td>'
        . '<td>'
        . '<img src="' . $imgRepositoryWeb . '/access_open.gif" />'
        . '<input type="radio" id="access_true" name="course_access" value="1" ' . ($course['access'] ? 'checked="checked"':'') . '>&nbsp;'
        . '<label for="access_true">' . get_lang('Public access from campus home page even without login') . '</label>'
        . '<br />' . "\n"
        . '<img src="' . $imgRepositoryWeb . 'access_locked.gif" />'
        . '<input type="radio" id="access_false" name="course_access" value="0" ' . ( ! $course['access'] ? 'checked="checked"':'' ) . '>&nbsp;'
        . '<label for="access_false">' . get_lang('Private access (site accessible only to people on the <a href="%url">User list</a>)' ,
                                          array('%url'=> '../user/user.php')) 
        . '</label>'
        . '</td>'
        . '</tr>' . "\n" ;

    // Course enrolment + enrolment key

    $html .= '<tr valign="top">' . "\n"
        . '<td align="right">' . get_lang('Enrolment') . '&nbsp;:</td>'
        . '<td>'
        . '<img src="' . $imgRepositoryWeb . '/enroll_open.gif" />'
        . '<input type="radio" id="enrolment_true" name="course_enrolment" value="1" ' . ($course['enrolment']?'checked="checked"':'') . '>&nbsp;'
        . '<label for="enrolment_true">' . get_lang('Allowed') . '</label>'
        . '<label for="enrolment_key">'
        . ' - ' . get_lang('Enrolment key') . '<small>(' . get_lang('Optional') . ')</small> :'
        . '</label>'
        . '<input type="text" id="enrolment_key" name="course_enrolment_key" value="' . htmlspecialchars($course['enrolmentKey']) . '" />'
        . '<br />' . "\n"
        . '<img src="' . $imgRepositoryWeb . 'enroll_locked.gif" />'
        . '<input type="radio" id="enrolment_false"  name="course_enrolment" value="0"' . ( ! $course['enrolment'] ?'checked="checked"':'') . '>&nbsp;'
        . '<label for="enrolment_false">' . get_lang('Denied') . '</label>'
        . '</td>'
        . '</tr>' . "\n" ;

    // Block course settings tip

    $html .= '<tr>' . "\n"
        . '<td>&nbsp;</td>'
        . '<td><small><font color="gray">' . get_block('blockCourseSettingsTip') . '</font></small></td>'
        . '</tr>' . "\n" ;

    // Required legend

    $html .= '<tr>' . "\n"
        . '<td>&nbsp;</td>'
        . '<td>' . get_lang('<span class=\"required\">*</span> denotes required field') . '</td>'
        . '</tr>' . "\n" ;

    $html .= '<tr>' . "\n"
        . '<td>&nbsp;</td>'
        . '<td>'
        . '<input type="submit" name="changeProperties" value="' . get_lang('Ok') . '" />'
        . claro_html_button( $clarolineRepositoryWeb . 'course/index.php?cid=' . htmlspecialchars($cid), get_lang('Cancel'))
        . '</td>' . "\n"
        . '</tr>' . "\n" ;

    $html .= '</table>' . "\n"
        .'</form>' . "\n" ;

    return $html;

}

function getCourseVisibility ( $access, $enrolment )
{
    $visibility = 0 ;

    if     ( ! $access && ! $enrolment ) $visibility = 0;
    elseif ( ! $access &&   $enrolment ) $visibility = 1;
    elseif (   $access && ! $enrolment ) $visibility = 3;
    elseif (   $access &&   $enrolment ) $visibility = 2;

    return $visibility ;
}

function getCourseAccess ( $visibility )
{
    if ( $visibility >= 2 ) return true ;
    else                    return false ;
}

function getCourseEnrolment ( $visibility )
{
    if ( $visibility == 1 || $visibility == 2 ) return true ;
    else                                        return false;
}

/**
 * Check e-mail validity
 *
 * email can be a list
 *  accept ; [space] and , as separator but  if all is right replace all by ;
 * if  one is wrong display the erronous and dont change
 */
function check_email_validity($email)
{

    if ( ! empty( $email ))
    {
        $is_emailListValid = true;

        /* TODO check if the fix for bug #716 and 717 does not break the code
         * since moosh does not remember why the strpos was there.
         */
        $emailControlList = strtr($email,', ',';');
        $emailControlList = preg_replace( '/;+/', ';', $emailControlList );

        $emailControlList = explode(';',$emailControlList);
        
        foreach ($emailControlList as $emailControl )
        {
            if ( ! is_well_formed_email_address( trim($emailControl)) )
            {
                $is_emailListValid = false;
                $errorMsgList[] = get_lang('The email address is not valid');
            }
            else
            {
                $emailValidList[] = trim($emailControl);
            }
        }

        if ($is_emailListValid && is_array($emailValidList))
        {
            $email = implode(';',$emailValidList);
        }
    }
    
    return $email;
}

function course_validate($course)
{
    require_once dirname(__FILE__) . '/backlog.class.php';

    $backlog = new Backlog();
    $success = true ;

    /**
     * Configuration array , define here which field can be left empty or not
     */

    $fieldRequiredStateList['title'        ] = get_conf('human_label_needed');
    $fieldRequiredStateList['officialCode' ] = get_conf('human_code_needed');
    $fieldRequiredStateList['titular'      ] = false;
    $fieldRequiredStateList['email'        ] = get_conf('course_email_needed');
    $fieldRequiredStateList['category'     ] = true;
    $fieldRequiredStateList['language'     ] = true;

    // Course title
    if ( empty($course['title']) && $fieldRequiredStateList['title'] )
    {
        $backlog->failure(get_lang('Course title needed'));
        $success = false ;
    }
    
    // Course code
    if ( empty($course['officialCode']) && $fieldRequiredStateList['officialCode'])
    {
        $backlog->failure(get_lang('Course code needed'));
        $success = false ;
    }
    
    // Course email
    if ( empty($course['email']) && $fieldRequiredStateList['email'])
    {
        $backlog->failure(get_lang('Email needed'));
        $success = false ;
    }

    // TODO : email validation 

    if ( !empty($course['email']) )
    {
        
    }

    // Course category
    if ( is_null($course['category']) && $fieldRequiredStateList['category'])
    {
        $backlog->failure(get_lang('Category needed (you must choose a category)'));
        $success = false ;
    }
    
    // Course language
    if ( empty($course['language']) && $fieldRequiredStateList['language'])
    {
        $backlog->failure(get_lang('language needed'));
        $success = false ;
    }

    // Course department url

    // check if department url is set properly
    $regexp = "^(http|https|ftp)\://[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(:[a-zA-Z0-9]*)?/?([a-zA-Z0-9\-\._\?\,\'/\\\+&%\$#\=~])*$";

    if ( (!empty($course['departmentUrl'])) && !eregi( $regexp, $course['departmentUrl']) )
    {
        // Problem with url. try to repair
        // if  it  only the protocol missing add http
        if ( ! eregi('^[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(:[a-zA-Z0-9]*)?/?([a-zA-Z0-9\-\._\?\,\'/\\\+&%\$#\=~])*$', $course['departmentUrl'])
        && ( ! eregi($regexp, 'http://' . $course['departmentUrl'])))
        {
             $backlog->failure(get_lang('Department URL is not valid'));
            $success = false ;
        }
    }

    return array ($backlog, $success);
}

function course_save()
{



}

?>
