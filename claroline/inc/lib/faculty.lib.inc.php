<?php // $Id$

if ( count( get_included_files() ) == 1 )
{
    die( 'The file ' . basename(__FILE__) . ' cannot be accessed directly, use include instead' );
}

/**
 * CLAROLINE
 *
 * This is lib for manage course tree with tree structure version 1
 *
 * @version     1.9 $Revision$
 * @copyright   (c) 2001-2010, Universite catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @see         http://www.claroline.net/wiki/index.php/CLTREE
 * @package     CLTREE
 * @author      Claro Team <cvs@claroline.net>
 * @deprecated  since 1.10 (now using clarocategory.class.php and category.lib.inc.php)
 */

/**
     *This function display the bom whith option to edit or delete the categories
     *
     * @author - < Benoit Muret >
     * @param   - elem             array     : the array of each category
     * @param   - father        string     : the father of the category
     *
     * @return  - $out
     *
     * display the bom whith option to edit or delete the categories
     */

function claro_disp_tree($elem,$father,$space)
{
    $out = '';
    if($elem)
    {
        $space.="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
        $num=0;

        foreach($elem as $one_faculty)
        {
            if(!strcmp($one_faculty['code_P'],$father))
            {
                $num++;

                $out .= '<tr><td>';

                    $out .= $space;

                    if($one_faculty['nb_childs']>0)
                    {

                        $out .= '<a href="' . $_SERVER['PHP_SELF']
                        .    '?id=' . $one_faculty['id'] . '"> '
                        .    ( $one_faculty['visible']
                             ?    '<img src="' . get_icon_url('collapse') . '" alt="-"  />'
                             :    '<img src="' . get_icon_url('expand') . '" alt="+"  />'
                             )
                        .    '</a> '
                        .    '&nbsp;'
                        ;
                    }
                    else
                    $out .= '&nbsp;&nbsp;&nbsp;&nbsp;';

                    $out .= $one_faculty['name'] . ' (' . $one_faculty['code'] . ') &nbsp;&nbsp;&nbsp;';

                    //Number of faculty in this parent
                    $nb=0;
                    foreach($elem as $one_elem)
                    {
                        if(!strcmp($one_elem['code_P'], $one_faculty['code_P']))
                        $nb++;
                    }


                    //Display the picture to edit and delete a category
                    $out .= '</td>'
                    .    '<td  align="center">'
                    .    '<a href="./admincourses.php?category=' . urlencode($one_faculty['code']) . '">'
                    .    get_node_children_count_course( $one_faculty['code'] )
                    .    '</a>' ;

                    $out .= '</td>'
                        . '<td  align="center">'
                        . '<a href="' . $_SERVER['PHP_SELF'] . '?id=' . $one_faculty['id'] . '&amp;cmd=rqEdit" >'
                        . '<img src="' . get_icon_url('edit') . '"alt="' . get_lang('Edit') . '" /></a>'
                        . '</td>'
                    . '<td align="center">'
                    . '<a href="' . $_SERVER['PHP_SELF'] . '?id='.$one_faculty['id'].'&amp;cmd=rqMove" >'
                    . '<img src="' . get_icon_url('move') . '" alt="' . get_lang('Move') . '"  /></a>'
                    . '</td>'
                    . '<td align="center">'
                    . '<a href="' . $_SERVER['PHP_SELF'] . '?id='.$one_faculty['id'].'&amp;cmd=exDelete"'
                    . 'onclick="javascript:if(!confirm(\''
                    . clean_str_for_javascript(get_lang('Do you really want to delete the "%categoryCode%" category ?', array('%categoryCode%' => $one_faculty['code']))) . '\')) return false" >'
                    . '<img src="' . get_icon_url('delete') . '" alt="' . get_lang('Delete') . '" /> </a>'
                    . '</td>';

                    //Search nbChild of the father
                    $nbChild=0;
                    $father=$one_faculty['code_P'];

                    foreach($elem as $fac)
                    if($fac['code_P']==$father)
                    $nbChild++;

                    //If the number of child is >0, display the arrow up and down
                    if($nb > 1)
                    {
                        $out .= '<td align="center">' . "\n";

                        //If isn't the first child, you can up
                        if ($num>1)
                        {
                            $out .= '<a href="' . $_SERVER['PHP_SELF'] . '?id='.$one_faculty['id'].'&amp;cmd=exUp">'
                            . '<img src="' . get_icon_url('move_up') . '" alt="' . get_lang('Move up') .'" /></a>';
                        }
                        else
                        {
                            $out .= '&nbsp;';
                        }

                        $out .= '</td>' . "\n" ;

                        $out .= '<td align="center">' . "\n" ;

                        // If isn't the last child, you can down
                        if ($num<$nbChild)
                        {
                            $out .= '<a href="' . $_SERVER['PHP_SELF'] . '?id=' . $one_faculty['id'] . '&amp;cmd=exDown">'
                            . '<img src="' . get_icon_url('move_down') . '" alt="' . get_lang('Move down') . '"  /> </a>';
                        }
                        else
                        {
                            $out .= '&nbsp;';
                        }

                        $out .= '</td>' . "\n" ;

                    }
                    else
                    {
                        $out .= '<td>&nbsp;</td>' . "\n"
                        .    '<td>&nbsp;</td>' . "\n"
                        ;
                    }

                    $out .= '</tr>' . "\n";

                    //display the bom of this category

                    if($one_faculty['visible'])
                    {
                        $out .= claro_disp_tree($elem, $one_faculty['code'], $space);
                    }
            }
        }
    }
    return $out;
}

/**
     * display the bom of category and display in red the category edit and his childeren in blue
     *
     * @author < Benoît Muret >
     * @param  elem             array     : the categories
     * @param  father        string     : the father of a category
     * @param  facultyEdit    key     : the category edit

     * @return void
     *
     *
     */

function displaySimpleBom($elem,$father,$facultyEdit)
{
    if($elem)
    {
        foreach($elem as $one_faculty)
        {
            if( !strcmp( $one_faculty['code_P'], $father ))
            {
                ?>
                    <ul><li>
                    <?php
                    echo (!strcmp($one_faculty['code'],$facultyEdit)?'<font color="red">':'');
                    echo $one_faculty['code'];
                    echo (!strcmp($one_faculty['code'],$facultyEdit)?'</font>':'');

                    echo (!strcmp($one_faculty['code'],$facultyEdit)?'<font color="blue">':'');
                    displaySimpleBom($elem,$one_faculty['code'],$facultyEdit);
                    echo (!strcmp($one_faculty['code'],$facultyEdit)?'</font>':'');
                ?>
                    </li></ul>
                <?php

            }
        }
    }
}

/**
 * Update nb_chils fields in node ascedant a deleted node.
 *
 * @param   $node_code string : the father
 * @param   $childQty  int    : the number of child deleting

 * @return  true on success
 *
 */

function delete_qty_child_father($node_code, $childQty)
{
    $tbl_mdb_names   = claro_sql_get_main_tbl();
    $tbl_course_node = $tbl_mdb_names['category'];

    while(!is_null($node_code))
    {
        $sql_DeleteNbChildFather= " UPDATE `". $tbl_course_node . "`
                                        SET nb_childs=nb_childs-".(int) $childQty."
                                        WHERE code='" . claro_sql_escape($node_code) . "'";

        claro_sql_query($sql_DeleteNbChildFather);

        $sql_SelectCodeP= " SELECT code_P
                                FROM `" . $tbl_course_node . "`
                                WHERE code='" . $node_code . "'";
        $node_code = claro_sql_query_get_single_value($sql_SelectCodeP);
    }
}


/**
     *This function add a number of child of all father from a category
     *
     * @author < Benoit Muret >
     * @param  fatherChangeChild        string     : the father
     * @param  newNbChild            int        : the number of child adding

     * @return void
     */

function addNbChildFather($fatherChangeChild, $newNbChild)
{
    $tbl_mdb_names   = claro_sql_get_main_tbl();
    $tbl_course_node = $tbl_mdb_names['category'];
    while(!is_null($fatherChangeChild))
    {
        $sql_DeleteNbChildFather= " UPDATE `" . $tbl_course_node . "`
                                        SET nb_childs = nb_childs+" . (int) $newNbChild . "
                                        WHERE code='" . $fatherChangeChild . "'";
        claro_sql_query($sql_DeleteNbChildFather);

        $sql_SelectCodeP= " SELECT code_P
                                FROM `" . $tbl_course_node . "`
                                WHERE code='".$fatherChangeChild."'";

        $fatherChangeChild = claro_sql_query_get_single_value($sql_SelectCodeP);
    }
}

/**
     *This function create de select box categories
     *
     * @author Benoit Muret
     * @param   $elem array the categories
     * @param   $father string the father of the category
     * @param   $editFather string the category editing
     * @param   $space string space to the bom of the category
     * @return  void
     *
     */

function build_select_faculty($elem,$father, $editFather, $space)
{
    $html = '';

    if($elem)
    {
        $space .= '&nbsp;&nbsp;&nbsp;';
        foreach($elem as $one_faculty)
        {
            if(!strcmp($one_faculty['code_P'],$father))
            {
                $html .= '<option value="' . $one_faculty['code'] . '" '
                .    ($one_faculty['code'] == $editFather ? 'selected="selected" ':'')
                .    '> ' . $space . $one_faculty['code'] . ' </option>'
                ;

                $html .= build_select_faculty($elem,$one_faculty['code'], $editFather, $space);
            }
        }
    }

    return $html ;
}


/**
 *
 * @param $cat_id string code of cat to get data
 * @return array of data id, name, code, code_P, treePos, nb_childs, canHaveCatChild, canHaveCoursesChild
 * @author Christophe Gesche <moosh@claroline.net>
 * @since 1.7
 */
function get_cat_data($cat_id)
{
    $tbl_mdb_names   = claro_sql_get_main_tbl();
    $tbl_course_node = $tbl_mdb_names['category'];
    $sql_get_cat_data = " SELECT id, name, code, code_P, treePos, nb_childs, canHaveCatChild, canHaveCoursesChild
                          FROM `" . $tbl_course_node . "`
                          WHERE id= ". (int) $cat_id;

    return claro_sql_query_get_single_row($sql_get_cat_data);

}

/**
 *
 * @param $cat_id string code of cat to get data
 * @return array of data id, name, code, code_P, canHaveCatChild, canHaveCoursesChild
 * @author Christophe Gesche <moosh@claroline.net>
 * @since 1.7
 *
 */
function get_cat_id_from_code($cat_code)
{
    $tbl_mdb_names   = claro_sql_get_main_tbl();
    $tbl_course_node = $tbl_mdb_names['category'];

    $sql_get_cat_id = " SELECT id
                                       FROM `" . $tbl_course_node . "`
                                       WHERE code='". claro_sql_escape( $cat_code ) ."'";
    return claro_sql_query_get_single_value($sql_get_cat_id);

}

/**
 * THEORIC FUNCTION TO COMPUTE  NB_CHILDS
 * @param $node_code
 * @return
 * @author Christophe Gesche <moosh@claroline.net>
 * @since 1.7
 *

function cat_count_descendance($node_code)
{
    global $nodeList;
    foreach ($nodeList as $node)
    $child_count = $node['code_P'] == $node_code ? cat_count_descendance($node['code']) : 0;

    return $child_count +1;
}
*/

/**
 * Return  minimum and the maximum value for treePos
 * @return minimum and the maximum value for treePos
 * @author Christophe Gesche <moosh@claroline.net>
 * @since 1.7
 *
 */
function get_extremesTreePos()
{
    $tbl_mdb_names   = claro_sql_get_main_tbl();
    $tbl_course_node = $tbl_mdb_names['category'];

    $sql_InfoTree=" SELECT min(treePos) minimum, max(treePos) maximum
                FROM `" . $tbl_course_node . "`";
    return claro_sql_query_get_single_row($sql_InfoTree);
}
/**
 * Get the last treePos of the table faculty
 *
 * @return  biggest treePos
 * @since 1.5
 *
 */

function search_max_tree_pos()
{
    $extremeTreePos = get_extremesTreePos();
    return $extremeTreePos['maximum'];
}

/**
 *
 * @param $node
 * @return
 * @author Christophe Gesche <moosh@claroline.net>
 *
 */
function get_node_children_count($node)
{
    $tbl_mdb_names   = claro_sql_get_main_tbl();
    $tbl_course_node = $tbl_mdb_names['category'];

    $sql="SELECT count(id)
          FROM `" . $tbl_course_node. "` ";

    if (is_null($node))
    {
        $sql .= "WHERE code_P IS NULL ";
    }
    else
    {
        $sql .= "WHERE code_P = '" . $node . "'";
    }

    return claro_sql_query_get_single_value($sql);
}

/**
 *
 * @param $node
 * @return
 * @author Christophe Gesche <moosh@claroline.net>
 *
 */
function get_node_descendance_count($node)
{
    $tbl_mdb_names   = claro_sql_get_main_tbl();
    $tbl_course_node = $tbl_mdb_names['category'];

    if (is_null($node))
    {
        $sql="SELECT count(id) nb_childs FROM `" . $tbl_course_node. "` ";
    }
    else
    {
        $sql="SELECT nb_childs
          FROM `" . $tbl_course_node. "`
        WHERE code = '" . $node . "'";
    }

    return claro_sql_query_get_single_value($sql);
}

/**
 *
 * @param $node
 * @return
 * @author Christophe Gesche <moosh@claroline.net>
 *
 */
function get_node_children_count_course($node)
{
    $tbl_mdb_names   = claro_sql_get_main_tbl();
    $tbl_course      = $tbl_mdb_names['course'];

    $sql = "SELECT COUNT( `courses`.`cours_id` ) `nbCourse`
            FROM `" . $tbl_course . "` `courses`
            WHERE `courses`.`faculte` = '". claro_sql_escape($node) ."'";

    return claro_sql_query_get_single_value($sql);
}

/**
 *
 * @param $id_node
 * @return
 * @author Christophe Gesche <moosh@claroline.net>
 *
 */
function delete_node($id_node)
{
    $tbl_mdb_names   = claro_sql_get_main_tbl();
    $tbl_course_node = $tbl_mdb_names['category'];

    $cat_data = get_cat_data($id_node);
    if (!$cat_data) return false;

    $sql_Delete= " DELETE FROM `" . $tbl_course_node . "`
                   WHERE id= ". (int) $cat_data['id'];
    if (!claro_sql_query($sql_Delete)) return false;

    // Update nb_child of the parent

    delete_qty_child_father($cat_data['code_P'], 1);
    // Update treePos of next categories
    $sql_update = " UPDATE `" . $tbl_course_node . "`
                    SET treePos = treePos - 1
                    WHERE treePos > " . (int) $cat_data['treePos'] ;
    claro_sql_query($sql_update);
    return true;
}

/**
 * make 6 test on the given category and and return the status
 * @param $cat_Code
 * @return boolean tree status
 * @author Christophe Gesche <moosh@claroline.net>
 *
 */
function analyseCat($catCode)
{
    $catData = get_cat_data(get_cat_id_from_code($catCode));
    //TEST 1 :
    if(!ctype_digit($catData['id'])) return claro_failure::set_failure('id field of row "'.$catCode.'" is wrong. It would contain integer value');
    //TEST 2 :
    if(!ctype_digit($catData['treePos'])) return claro_failure::set_failure('treePos field of row "'.$catCode.'" is wrong. It would contain integer value');
    //TEST 3 :
    if(!ctype_digit($catData['nb_childs'])) return claro_failure::set_failure('nb_childs field of row "'.$catCode.'" is wrong. It would contain integer value');
    //TEST 4
    // Was stupid
    //TEST 5
    if(!is_null($catData['code_P']) && !get_cat_id_from_code($catData['code_P'])  ) return claro_failure::set_failure('parent_code_not_valid: '.$catData['code_P']);
    //TEST 6

    $parentCatData = get_cat_data(get_cat_id_from_code($catData['code_P']));
    if($catData['treePos'] < $parentCatData['treePos']) return claro_failure::set_failure('loop_asendance ' .$catData['code'].':'.$catData['treePos'] . ' < ' .$parentCatData['code'].':'.$parentCatData['treePos'] );

    //TEST 7 // CheckPath
    if(countChild($catCode)!=($catData['nb_childs'])) return claro_failure::set_failure('nb_childs field of row "'.$catCode.'" is wrong. Correct value : ' . countChild($catCode) . '; Stored Value : '.($catData['nb_childs']));


    return true;
}

function countChild($catCode)
{
    $catToCheck = get_cat_data(get_cat_id_from_code($catCode));
    $catList = claro_get_cat_list();
    $i = 0;
    while ((list(,$cat) = each($catList)))
    {
        if ($cat['code_P'] == $catToCheck['code'] ) $i++;
    }
    return $i;
}


function repairTree()
{
    $tbl_mdb_names = claro_sql_get_main_tbl();
    $tbl_category  = $tbl_mdb_names['category'];

    //  get  list of all node
    $sql = " SELECT code, code_P, treePos, name, nb_childs
               FROM `" . $tbl_category . "`
               ORDER BY `treePos`";
    $catList = claro_sql_query_fetch_all($sql);
    $newTreePos= 1;
    $listSize = count($catList);

    // foreach node check code_parent,  treepos and nbchilds

    foreach ($catList as $cat)

    {
        $newCatList[$cat['code']] = $cat;
        $parentCatData = get_cat_data(get_cat_id_from_code($cat['code_P']));

        if($cat['treePos'] < $parentCatData['treePos'])
        {
            $newCatList[$cat['code']]['newCode_P'] = ' root ';
            $newCatList[$cat['code']]['newTreePos'] = $listSize--;
        }
        else
        {

            if(!is_null($cat['code_P']) && !get_cat_id_from_code($cat['code_P'])  )
            {
                $newCatList[$cat['code']]['newCode_P'] = ' root ';
                $newCatList[$cat['code']]['newTreePos'] = $listSize--;
            }
            else
            {
                $newCatList[$cat['code']]['newTreePos'] = $newTreePos++;
                $newCatList[$cat['code']]['newNb_childs'] = countChild($cat['code']);
            }
        }


    }
    reset($newCatList);

    $node_moved=false;
    // rescan node list  and  update data if difference was detected.
    foreach ($newCatList as $cat)
    {
        if(isset($cat['newCode_P']) && ($cat['code_P'] != $cat['newCode_P']))
        {
            $sql = "UPDATE  `" . $tbl_category . "` "
            . ($cat['newCode_P']==' root ' ? "   SET code_P = null "
                                          : "   SET code_P = " . (int) $cat['newCode_P'])
            .      " WHERE code = '" . claro_sql_escape($cat['code']) . "'"
            ;
            $node_moved=true; // repair ownance but brok countchild
            claro_sql_query($sql);
        }
        if(isset($cat['newNb_childs']) && ($cat['nb_childs'] != $cat['newNb_childs']))
        {
            $sql = "UPDATE  `" . $tbl_category . "` "
            .      "   SET nb_childs = " . (int) $cat['newNb_childs']
            .      " WHERE code = '" . claro_sql_escape($cat['code']) . "'"
            ;
            claro_sql_query($sql);
        }

        if($cat['treePos'] != $cat['newTreePos'])
        {
            $sql = "UPDATE  `" . $tbl_category . "` "
            .      "   SET treePos = " . (int) $cat['newTreePos']
            .      " WHERE code = '" . claro_sql_escape($cat['code']) . "'"
            ;

            claro_sql_query($sql);
        }

    }

    if ($node_moved) return claro_failure::set_failure('node_moved');
    else             return true;
};
