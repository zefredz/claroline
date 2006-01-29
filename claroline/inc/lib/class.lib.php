<?php // $Id$
/**
 * CLAROLINE
 *
 * Library for class
 *
 * @version 1.8 $Revision$
 *
 * @copyright 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @author Claro Team <cvs@claroline.net>
 * @author Guillaume Lederer <guillaume@claroline.net>
 *
 * @since 1.6
 */

/**
 * Enter description here...
 *
 * @param integer $class_id
 * @param string $course_code
 * @return unknown
 */
function register_class_to_course($class_id, $course_code)
{
    $tbl_mdb_names  = claro_sql_get_main_tbl();
    $tbl_user       = $tbl_mdb_names['user'];
    $tbl_class_user = $tbl_mdb_names['rel_class_user'];
    $tbl_class      = $tbl_mdb_names['class'];

    echo '<br>' . sprintf(get_lang('we are in the recursion of class : %s'),$class_id) . '<br>';

    //get the list of users in this class

    $sql = "SELECT *
            FROM `" . $tbl_class_user . "` AS `rel_c_u`,
                 `" . $tbl_user . "`       AS `u`
                    WHERE `class_id`= " . (int) $class_id . "
               AND `rel_c_u`.`user_id` = `u`.`user_id`";
    $result = claro_sql_query_fetch_all($sql);

    //subscribe the users each by each

    $resultLog = array();

    foreach ($result as $user)
    {
        $done = user_add_to_course($user['user_id'], $course_code);
        if ($done) $resultLog['OK'][] = $user;
        else       $resultLog['KO'][] = $user;
    }

    //find subclasses of current class

    $sql = "SELECT `id`
            FROM `" . $tbl_class . "`
            WHERE `class_parent_id`=" . (int) $class_id;

    $subClassesList = claro_sql_query_fetch_all($sql);

    //RECURSIVE CALL to register subClasses too

    if (!isset($resultLog['OK'])) $resultLog['OK'] = array();
    if (!isset($resultLog['KO'])) $resultLog['KO'] = array();

    foreach ($subClassesList as $subClass)
    {
        $subClassResultLog = register_class_to_course($subClass['id'], $course_code);

        if (isset($subClassResultLog['OK'])) $resultLog['OK'] = array_merge($resultLog['OK'],$subClassResultLog['OK']);
        if (isset($subClassResultLog['KO'])) $resultLog['KO'] = array_merge($resultLog['KO'],$subClassResultLog['KO']);
    }

    return $resultLog;
}

/**
 * Display the tree of classes
 *
 * @param unknown_type $class_list list of all the classes informations of the platform
 * @param unknown_type $parent_class
 * @param unknown_type $deep
 * @return unknown
 */

function display_tree_class_in_admin ($class_list, $parent_class = null, $deep = 0)
{

    //global variables needed

    global $clarolineRepositoryWeb;
    global $imgRepositoryWeb;

    foreach ($class_list as $cur_class)
    {

        if (($parent_class == $cur_class['class_parent_id']))
        {

            //Set space characters to add in name display

            $blankspace = '&nbsp;&nbsp;&nbsp;';
            for ($i = 0; $i < $deep; $i++)
            {
                $blankspace .= '&nbsp;&nbsp;&nbsp;';
            }

            //see if current class to display has children

            $has_children = FALSE;
            foreach ($class_list as $search_parent)
            {
                if ($cur_class['id'] == $search_parent['class_parent_id'])
                {
                    $has_children = TRUE;
                    break;
                }
            }

            //Set link to open or close current class

            if ($has_children)
            {
                if (isset($_SESSION['admin_visible_class'][$cur_class['id']]) && $_SESSION['admin_visible_class'][$cur_class['id']]=="open")
                {
                    $open_close_link = '<a href="' . $_SERVER['PHP_SELF'] . '?cmd=exClose&amp;class=' . $cur_class['id'] . '">' . "\n"
                    .                  '<img src="' . $imgRepositoryWeb . 'minus.gif" border="0" />' . "\n"
                    .                  '</a>' . "\n"
                    ;
                }
                else
                {
                    $open_close_link = '<a href="' . $_SERVER['PHP_SELF'] . '?cmd=exOpen&amp;class=' . $cur_class['id'] . '">' . "\n"
                    .                  '<img src="' . $imgRepositoryWeb . 'plus.gif" border="0" />' . "\n"
                    .                  '</a>' . "\n"
                    ;
                }
            }
            else
            {
                $open_close_link =" ° ";
            }

            //DISPLAY CURRENT ELEMENT (CLASS)

            //Name
            $qty_user = get_class_user_number($cur_class['id']);

            echo '<tr>' . "\n"
            .    '<td>' . "\n"
            .    '    ' . $blankspace . $open_close_link . ' ' . $cur_class['name']
            .    '</td>' . "\n"
            .    '<td align="center">' . "\n"
            .    '<a href="' . $clarolineRepositoryWeb . 'admin/admin_class_user.php?class=' . $cur_class['id'] . '">' . "\n"
            .    '<img src="' . $imgRepositoryWeb . 'user.gif" border="0" />' . "\n"
            .    '(' . $qty_user . '  ' . get_lang('UsersMin') . ')' . "\n"
            .    '</a>' . "\n"
            .    '</td>' . "\n"
            .    '<td align="center">' . "\n"
            .    '<a href="' . $_SERVER['PHP_SELF'] . '?cmd=edit&amp;class=' . $cur_class['id'] . '">' . "\n"
            .    '<img src="' . $imgRepositoryWeb . 'edit.gif" border="0" />' . "\n"
            .    '</a>' . "\n"
            .    '</td>' . "\n"
            .    '<td align="center">' . "\n"
            .    '<a href="' . $_SERVER['PHP_SELF'] . '?cmd=move&amp;class=' . $cur_class['id'] . '&classname=' . $cur_class['name'] . '">' . "\n"
            .    '<img src="' . $imgRepositoryWeb . 'move.gif" border="0" />' . "\n"
            .    '</a>' . "\n"
            .    '</td>' . "\n"
            .    '<td align="center">' . "\n"
            .    '<a href="' . $_SERVER['PHP_SELF']
            .    '?cmd=del&amp;class=' . $cur_class['id'] . '"'
            .    ' onClick="return confirmation(\'' . clean_str_for_javascript($cur_class['name']) . '\');">' . "\n"
            .    '<img src="' . $imgRepositoryWeb . 'delete.gif" border="0" />' . "\n"
            .    '</a>' . "\n"
            .    '</td>' . "\n"
            .    '</tr>' . "\n"
            ;
            // RECURSIVE CALL TO DISPLAY CHILDREN

            if (isset($_SESSION['admin_visible_class'][$cur_class['id']]) && ($_SESSION['admin_visible_class'][$cur_class['id']]=="open"))
            {
                display_tree_class_in_admin($class_list, $cur_class['id'], $deep+1);
            }
        }
    }
}

/**
 * Get the number of users in a class, including sublclasses
 *
 * @author Guillaume Lederer
 * @param  id of the (parent) class ffrom which we want to know the number of users
 * @return (int) number of users in this class and its subclasses
 *
 */

function get_class_user_number($class_id)
{
    $tbl_mdb_names  = claro_sql_get_main_tbl();
    $tbl_class_user = $tbl_mdb_names['rel_class_user'];
    $tbl_class      = $tbl_mdb_names['class'];
    //1- get class users number

    $sqlcount = "SELECT COUNT(`user_id`) AS qty_user
                 FROM `" . $tbl_class_user . "`
                 WHERE `class_id`=" . (int) $class_id;

    $qty_user =  claro_sql_query_get_single_value($sqlcount);

    $sql = "SELECT `id`
            FROM `" . $tbl_class . "`
            WHERE `class_parent_id`=" . (int) $class_id;

    $subClassesList = claro_sql_query_fetch_all($sql);

    //2- recursive call to get subclasses'users too

    foreach ($subClassesList as $subClass)
    {
        $qty_user += get_class_user_number($subClass['id']);
    }

    //3- return result of counts and recursive calls

    return $qty_user;
}

/**
 * Display the tree of classes
 *
 * @author Guillaume Lederer
 * @param  list of all the classes informations of the platform
 * @param  list of the classes that must be visible
 * @return
 *
 * @see
 *
 */

function display_tree_class_in_user($class_list, $parent_class = null, $deep = 0)
{

    global $clarolineRepositoryWeb;
    global $imgRepositoryWeb;

    $tbl_mdb_names  = claro_sql_get_main_tbl();
    $tbl_class_user = $tbl_mdb_names['rel_class_user'];

    foreach ($class_list as $cur_class)
    {
        if (($parent_class==$cur_class['class_parent_id']))
        {

            //Set space characters to add in name display

            $blankspace = '&nbsp;&nbsp;&nbsp;';
            for ($i = 0; $i < $deep; $i++)
            {
                $blankspace .= '&nbsp;&nbsp;&nbsp;';
            }

            //see if current class to display has children

            $has_children = FALSE;
            foreach ($class_list as $search_parent)
            {
                if ($cur_class['id'] == $search_parent['class_parent_id'])
                {
                    $has_children = TRUE;
                    break;
                }
            }

            //Set link to open or close current class

            if ($has_children)
            {
                if (isset($_SESSION['class_add_visible_class'][$cur_class['id']]) && $_SESSION['class_add_visible_class'][$cur_class['id']]=="open")
                {
                    $open_close_link = '<a href="' . $_SERVER['PHP_SELF']
                    .                  '?cmd=exClose&amp;class=' . $cur_class['id'] . '">' . "\n"
                    .                  '<img src="' . $imgRepositoryWeb . 'minus.gif" border="0" />' . "\n"
                    .                  '</a>' . "\n"
                    ;
                }
                else
                {
                    $open_close_link = '<a href="' . $_SERVER['PHP_SELF'] . '?cmd=exOpen&amp;class=' . $cur_class['id'] . '">' . "\n"
                    .                  '<img src="' . $imgRepositoryWeb . 'plus.gif" border="0" />' . "\n"
                    .                  '</a>' . "\n"
                    ;
                }
            }
            else
            {
                $open_close_link = '°';
            }


            $sqlcount="SELECT COUNT(`user_id`) AS qty_user
                       FROM `" . $tbl_class_user . "`
                       WHERE `class_id`= " . (int) $cur_class['id'];
            $qty_user = claro_sql_query_get_single_value($sqlcount);


            //DISPLAY CURRENT ELEMENT (CLASS)

            //Name

            echo '<tr>' . "\n"
            .    '<td>' . "\n"
            .    $blankspace.$open_close_link." ".$cur_class['name'] . "\n"
            .    '</td>' . "\n"
            .    '<td align="center">' . "\n"
            .    $qty_user . '  ' . get_lang('UsersMin') . "\n"
            .    '</td>' . "\n"
            .    '<td align="center">' . "\n"
            .    '<a onClick="return confirmation(\'' . clean_str_for_javascript($cur_class['name']) . '\');" href="' . $_SERVER['PHP_SELF'] . '?cmd=subscribe&amp;class=' . $cur_class['id'] . '&amp;classname=' . $cur_class['name'] . '">' . "\n"
            .    '<img src="' . $imgRepositoryWeb . 'enroll.gif" border="0" alt="' . get_lang('SubscribeToCourse') . '" />' . "\n"
            .    '</a>' . "\n"
            .    '</td>' . "\n"
            .    '</tr>' . "\n"
            ;
            // RECURSIVE CALL TO DISPLAY CHILDREN

            if (isset($_SESSION['class_add_visible_class'][$cur_class['id']]) && ($_SESSION['class_add_visible_class'][$cur_class['id']]=="open"))
            {
                display_tree_class_in_user($class_list, $cur_class['id'], $deep+1);
            }
        }
    }
}


/**
 *This function create the select box to choose the parent class
 *
 * @param  the pre-selected class'id in the select box
 * @param  space to display for children to show deepness
 * @global $tbl_class
 * @global get_lang('TopLevel')
 * @return void
*/

function displaySelectBox($selected=null,$space="&nbsp;&nbsp;&nbsp;")
{
    $tbl_mdb_names  = claro_sql_get_main_tbl();
    $tbl_class      = $tbl_mdb_names['class'];

    $sql = " SELECT *
             FROM `" . $tbl_class . "`
             ORDER BY `name`";
    $classes = claro_sql_query_fetch_all($sql);

    $result = '<select name="theclass">' . "\n"
    .         '<option value="root">' . get_lang('TopLevel') . '</option>';
    $result .= buildSelectClass($classes,$selected,null,$space);
    $result .= '</select>' . "\n";
    return $result;
}

/**
 * This function create the list for the select box to choose the parent class
 *
 * @author Guillaume Lederer
 * @param  tab containing at least all the classes with their id, parent_id and name
 * @param  parent_id of the class we want to display the children of
 * @param  the pre-selected class'id in the select box
 * @param  space to display for children to show deepness
 * @return string to output
 *
*/
function buildSelectClass($classes,$selected,$father=null,$space="&nbsp;&nbsp;&nbsp;")
{
    $result = '';
    if($classes)
    {
        foreach($classes as $one_class)
        {
            //echo $one_class["class_parent_id"]." versus ".$father."<br>";

            if($one_class['class_parent_id']==$father)
            {
                $result .= '<option value="'.$one_class['id'].'" ';
                if ($one_class['id'] == $selected)
                {
                    $result .= 'selected ';
                }
                $result .= '> '.$space.$one_class['name'].' </option>'."\n";
                $result .=  buildSelectClass($classes,$selected,$one_class['id'],$space.'&nbsp;&nbsp;&nbsp;');
            }
        }
    }
    return $result;
}

function getSubClasses($class_id)
{
    $tbl_mdb_names  = claro_sql_get_main_tbl();
    $tbl_class      = $tbl_mdb_names['class'];

    $sub_classes_list = array();

    $sql = "SELECT `id`
            FROM `" . $tbl_class . "`
            WHERE `class_parent_id`=" . (int) $class_id;

    $query_result = claro_sql_query($sql);

    while ( () $this_sub_class = mysql_fetch_array($query_result) ) )
    {
        // add this subclass id to array
        $sub_classes_list[] = $this_sub_class['id'];
        // add children of this subclass id to array
        $this_sub_classes_list = getSubClasses($this_sub_class['id']);
        $sub_classes_list = array_merge($this_sub_classes_list,$sub_classes_list);
    }

    return $sub_classes_list;
}

?>