<?php
/**
 * function of claroline 1.7 needed for admincats.
 *
 */

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

    $sql = " SELECT code, code_P, name, canHaveCoursesChild
               FROM `" . $tbl_category . "`
               ORDER BY `treePos`";
    return claro_sql_query_fetch_all($sql);

}


function claro_get_cat_flat_list($separator = ' > ')
{

    $fac_list = claro_get_cat_list();

    if(is_array($fac_list))
    foreach ($fac_list as $myfac)
    {
        $categories[$myfac['code']]['code']   = $myfac['code'];
        $categories[$myfac['code']]['parent'] = $myfac['code_P'];
        $categories[$myfac['code']]['name']   = $myfac['name'];
        $categories[$myfac['code']]['childs'] = $myfac['canHaveCoursesChild'];
    }

    // then we build the table we need : full path of editable cats in an array

    if (is_array($categories ))
    foreach ($categories as $cat)
    {
        if ( $cat['childs'] == 'TRUE' )
        {
            $fac_array[$cat['code']] = '('
            .                          get_full_path($categories, $cat['code'], $separator)
            .                          ') '
            .                          htmlspecialchars($cat['name'])
            ;
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
            $parent = $currentCat['parent'];
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
            return get_full_path($categories, $parent, $separator) . $separator . $catcode;
            break;
        }
    }
}




?>