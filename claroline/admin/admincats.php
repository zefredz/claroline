<?php // $Id$
/**
 * CLAROLINE
 *
 * This tool can edit category tree
 *
 * @version 1.8 $Revision$
 * @copyright 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @see http://www.claroline.net/wiki/index.php/CLTREE
 *
 * @package CLCOURSES
 *
 * @author Claro Team <cvs@claroline.net>
 *
 */
define ('DISP_FORM_CREATE', __LINE__);
define ('DISP_FORM_EDIT', __LINE__);
define ('DISP_FORM_MOVE', __LINE__);

$cidReset = true;
$gidReset = true;
$tidReset = true;

// include claro main global
require '../inc/claro_init_global.inc.php';

// check if user is logged as administrator
if ( ! claro_is_user_authenticated() ) claro_disp_auth_form();
if ( ! claro_is_platform_admin() ) claro_die(get_lang('Not allowed'));

include_once get_path('incRepositorySys') . '/lib/course.lib.inc.php';
include_once get_path('incRepositorySys') . '/lib/faculty.lib.inc.php';

// build bredcrump
ClaroBreadCrumbs::getInstance()->prepend( get_lang('Administration'), get_path('rootAdminWeb') );
$nameTools        = get_lang('Categories');

// get table name
$tbl_mdb_names   = claro_sql_get_main_tbl();
$tbl_course      = $tbl_mdb_names['course'  ];
$tbl_course_node = $tbl_mdb_names['category'];

$dialogBox = new DialogBox();

// Display variables

$display_form = null;
//Get Parameters from URL or post

$validCmdList = array('exCreate','exDelete', 'exUp', 'exDown', 'rqCreate', 'rqEdit', 'rqMove', 'exChange');
$cmd = (isset($_REQUEST['cmd']) && in_array($_REQUEST['cmd'],$validCmdList)? $_REQUEST['cmd'] : '');


/**
 * Show or hide sub categories
 */

if ( isset($_REQUEST['id'])
&& empty($cmd)   )
{
    $id = $_REQUEST['id'];
    $categories = $_SESSION['categories'];

    // Change the parameter 'visible'

    if(!is_null($categories))
    {
        foreach($categories as $key=>$category)
        {
            if($category['id'] == $id)
            {
                if($categories[$key]['visible'])
                $categories[$key]['visible'] = FALSE;
                else
                $categories[$key]['visible'] = TRUE;
            }
        }
    }

    // Save in session
    $_SESSION['categories'] = $categories;
}
else
{
    // Get value from session variables
    $categories = ( isset($_SESSION['categories']) ? $_SESSION['categories']: array());

    /**
     * Create a category
     */

    if($cmd == 'exCreate' )
    {
        $noQUERY_STRING=true;
        // If the new category have a name, a code and she can have child (categories or courses)
        if( !empty($_REQUEST['nameCat']) && !empty($_REQUEST['codeCat']) )
        {
            // If a category with the same code already exists we only display an error message
            $cat_data = get_cat_data(get_cat_id_from_code($_REQUEST['codeCat']));
            if (isset($cat_data['code']))
            {
                // Error message for attempt to create a duplicate
                $dialogBox->error( get_lang('This category already exists!') );
            }
            else
            {
                $nameCat   = $_REQUEST['nameCat'];
                $codeCat   = $_REQUEST['codeCat'];
                $fatherCat = $_REQUEST['fatherCat'];
                $canHaveCoursesChild = ($_REQUEST['canHaveCoursesChild'] == 1 ? 'TRUE' : 'FALSE');



                // If the category don't have as parent NULL (root), all parent of this category have a child more
                $fatherChangeChild = ($fatherCat == 'NULL') ? NULL : $fatherCat;

                addNbChildFather($fatherChangeChild, 1);

                // If the parent of the new category isn't root
                if(strcmp($fatherCat, 'NULL'))
                {
                    $cat_data = get_cat_data(get_cat_id_from_code($fatherCat));

                    // The treePos from the new category (treePos from this father + nb_childs from this father)
                    $treePosCat = $cat_data['treePos'] + $cat_data['nb_childs'];

                    // Add 1 to all category who have treePos >= of the treePos of the new category
                    $sql_ChangeTree=" UPDATE `" . $tbl_course_node . "`
                                      SET treePos = treePos + 1
                                      WHERE treePos >= '" . $treePosCat . "'";

                    claro_sql_query($sql_ChangeTree);
                }
                else    // The parent of the new category is root
                {
                    // Search the maximum treePos
                    $treePosCat = search_max_tree_pos() + 1;
                }

                // Insert the new category to the table

                $sql_InsertCat = " INSERT INTO `" . $tbl_course_node . "` "
                .                " (name"
                .                " , code"
                .                " , nb_childs"
                .                " , canHaveCoursesChild"
                .                " , canHaveCatChild"
                .                " , treePos "
                .                " , code_P )"
                .                " VALUES ('" . claro_sql_escape($nameCat) . "'"
                .                " ,'" . claro_sql_escape($codeCat) . "'"
                .                " , 0"
                .                " , '" . $canHaveCoursesChild . "'"
                .                " , 'TRUE'"
                .                " , " . (int) $treePosCat
                ;
                if ($fatherCat == "NULL")
                {
                    $sql_InsertCat .= ",NULL)";
                }
                else
                {
                    $sql_InsertCat .= ",'".claro_sql_escape($fatherCat)."')";
                }

                claro_sql_query($sql_InsertCat);

                // Confirm creating
                $dialogBox->success( get_lang('Category created') );

            }
        }
        else // if the new category don't have a name or a code or she can't have child (categories or courses)
        {
            if(empty($_REQUEST['nameCat']))
            $dialogBox->error( get_lang('Category name is required') );

            if(empty($_REQUEST['codeCat']))
            $dialogBox->error( get_lang('Category code is required') );
        }
    }

    /**
     * If you move the category in the same father of the bom
     */

    if($cmd == 'exUp' || $cmd == 'exDown')
    {
        $noQUERY_STRING=true;

        $extremesTreePos =  get_extremesTreePos();
        $treePosMin=$extremesTreePos['minimum'];
        $treePosMax=$extremesTreePos['maximum'];

        // Search the category who move in the bom
        $i=0;
        while( $i < count($categories) && $categories[$i]['id'] != $_REQUEST['id'])
        $i++;

        /**
         * If Up the category and the treePos of this category isn't the first category
         */

        if($cmd=='exUp' && $i >= $treePosMin )
        {
            // Search the previous brother of this category
            $j=$i-1;
            while($j>0 && ($categories[$j]['code_P']!=$categories[$i]['code_P'])) $j--;

            // If they are a brother
            if($categories[$j]['code_P'] == $categories[$i]['code_P'])
            {
                // change the brother and his children
                for($k = 0; $k <= $categories[$j]['nb_childs']; $k++)
                {
                    $searchId = $categories[$j + $k]['id'];
                    $newTree = $categories[$j]['treePos'] + $categories[$i]['nb_childs'] + 1 + $k;

                    $sql_Update = " UPDATE `" . $tbl_course_node . "`
                                    SET treePos='" . (int) $newTree . "'
                                    WHERE id='". (int) $searchId . "'";
                    claro_sql_query($sql_Update) ;
                }

                // change the choose category and his childeren
                for($k=0; $k <= $categories[$i]['nb_childs']; $k++)
                {
                    $searchId = $categories[$i+$k]['id'];
                    $newTree  = $categories[$i]['treePos'] - $categories[$j]['nb_childs'] - 1 + $k;

                    $sql_Update = " UPDATE `" . $tbl_course_node . "`"
                    .             "  SET treePos = '" . (int) $newTree . "'"
                    .             "  WHERE id='" . (int) $searchId . "'"
                    ;
                    claro_sql_query($sql_Update) ;
                }

                //$dialogBox->success( get_lang('Category moved') );
            }
        }

        /**
         * If Up the category and the treePos of this category isn't the last category
         */

        if ($cmd=='exDown' && $i < $treePosMax-1 )
        {
            // Search the next brother
            $j = $i+1;
            while($j<=count($categories) && ($categories[$j]['code_P'] != $categories[$i]['code_P']))
            $j++;

            // If they are a brother
            if($categories[$j]['code_P'] == $categories[$i]['code_P'])
            {
                // change the brother and his children
                for($k=0; $k <= $categories[$j]['nb_childs']; $k++)
                {
                    $searchId = $categories[$j+$k]['id'];
                    $newTree  = $categories[$j]['treePos'] - $categories[$i]['nb_childs'] - 1 + $k;

                    $sql_Update = " UPDATE `" .  $tbl_course_node . "`
                                    SET treePos='" . (int) $newTree . "'
                                    WHERE id='" . (int) $searchId."'";
                    claro_sql_query($sql_Update);
                }

                // change the choose category and his childeren
                for($k = 0; $k <= $categories[$i]['nb_childs']; $k++)
                {
                    $searchId=$categories[$i+$k]['id'];
                    $newTree=$categories[$i]['treePos'] + $categories[$j]['nb_childs'] + 1 + $k;

                    $sql_Update = " UPDATE `" . $tbl_course_node . "`
                                    SET treePos='" . (int) $newTree . "'
                                    WHERE id='" . (int) $searchId . "'";
                    claro_sql_query($sql_Update) ;
                }

                //$dialogBox->success( get_lang('Category moved') );
            }
        }
    }

    /**
     * If you delete a category
     */

    if($cmd == 'exDelete')
    {
        $noQUERY_STRING=true;

        // Search information about category
        $cat_data = get_cat_data($_REQUEST['id']);
        if ($cat_data)
        {
            // we delete if we do not encounter any problem...default is that there is no problem, then we check
            $delok = TRUE;

            $code_cat     = $cat_data['code'];
            $code_parent  = $cat_data['code_P'];
            $nb_childs    = $cat_data['nb_childs'];
            $treePos      = $cat_data['treePos'];

            // Look if there isn't any subcategory in this category first
            if($nb_childs > 0)
            {
                $dialogBox->error( get_lang('This category contains other categories, you must delete them before !') );
                $delok = FALSE;
            }

            // Look if they aren't courses in this category
            $sql_courseQty= "SELECT count(cours_id) num
                                 FROM `" . $tbl_course . "`
                                 WHERE faculte='" . claro_sql_escape($code_cat) . "'";
            $courseQty= claro_sql_query_get_single_value($sql_courseQty);

            if ($courseQty > 0)
            {
                $dialogBox->error( get_lang("This category contains some courses, you can't erase it!") );
                $delok = FALSE;
            }

            if ($delok == TRUE)
            {
                if (delete_node( $_REQUEST['id'] )) $dialogBox->success( get_lang('Category deleted') );
                else                                $dialogBox->error( get_lang('Unable to delete category') );
            }
        }

    }

    /**
     * Create a category : display form
     */

    elseif($cmd == 'rqCreate')
    {
        $display_form = DISP_FORM_CREATE;

        // try to retrieve previsiously posted parameters for the new category

        $editedCat_Name = isset($_REQUEST['nameCat']) ? $_REQUEST['nameCat'] : '';
        $editedCat_Code = isset($_REQUEST['codeCat']) ? $_REQUEST['codeCat'] : '';
        $canHaveCoursesChild = isset($_REQUEST['canHaveCoursesChild']) ? $_REQUEST['canHaveCoursesChild'] : '';

    }

    /**
     * Edit a category : display form
     */

    if($cmd == 'rqEdit' && isset($_REQUEST['id']))
    {


        // Search information of the category edit
        $editedCat_data = get_cat_data( $_REQUEST['id'] );

        if ($editedCat_data)
        {
            $display_form = DISP_FORM_EDIT;
            $editedCat_Id                  = $editedCat_data['id'];
            $editedCat_Name                = $editedCat_data['name'];
            $editedCat_Code                = $editedCat_data['code'];
            $editFather                    = $editedCat_data['code_P'];
            $editedCat_CanHaveCatChild     = $editedCat_data['canHaveCatChild'];
            $editedCat_CanHaveCoursesChild = $editedCat_data['canHaveCoursesChild'];

            unset ($editedCat_data);
        }
    }

    /**
     * Move a category : display form
     */

    if($cmd == 'rqMove')
    {
        // Search information of the category edit
        $editedCat_data = get_cat_data( $_REQUEST['id'] );

        if ($editedCat_data)
        {
            $display_form = DISP_FORM_MOVE;
            $editedCat_Id                  = $editedCat_data['id'];
            $editedCat_Name                = $editedCat_data['name'];
            $editedCat_Code                = $editedCat_data['code'];
            $editFather                    = $editedCat_data['code_P'];
            $editedCat_CanHaveCatChild     = $editedCat_data['canHaveCatChild'];
            $editedCat_CanHaveCoursesChild = $editedCat_data['canHaveCoursesChild'];

            unset ($editedCat_data);
        }
    }

    /**
     * Change information of category : do change in db
     */

    elseif( $cmd == 'exChange' )
    {
        $noQUERY_STRING = true;

            // Search information
            if ( ($facultyEdit = get_cat_data($_REQUEST['id'])) )
            {
                $doChange = true;
    
                // See if we try to set the categorie as a cat that can not have course
                // and that the cat already contain courses
                if (isset($_REQUEST['canHaveCoursesChild']) && $_REQUEST['canHaveCoursesChild'] == 0)
                {
                    $sql_SearchCourses= " SELECT count(cours_id) num"
                    .                    " FROM `" . $tbl_course . "`"
                    .                    " WHERE faculte='" . claro_sql_escape($facultyEdit['code']) . "'"
                    ;
                    $res_SearchCourses = claro_sql_query_get_single_value($sql_SearchCourses);
    
                    if($res_SearchCourses > 0)
                    {
                        $dialogBox->warning( get_lang('This category include some courses, you must delete or move them before') );
                        $doChange = false;
                    }
                }
            }
            else
            {
                $dialogBox->error( get_lang('There is no category available !'));
                $doChange = false;
            }
    
        // Edit a category (don't move the category)
        $_REQUEST['nameCat'] = trim($_REQUEST['nameCat']);
        $_REQUEST['codeCat'] = trim($_REQUEST['codeCat']);
        if( !empty($_REQUEST['nameCat']) && !empty($_REQUEST['codeCat']) )
        {
            if(!isset($_REQUEST['fatherCat']) && $doChange)
            {
                $canHaveCoursesChild=($_REQUEST['canHaveCoursesChild'] == 1 ? 'TRUE' : 'FALSE');
    
                // If nothing is different
                if(($facultyEdit['name'] != $_REQUEST['nameCat']) && ($facultyEdit['code'] != $_REQUEST['codeCat'])
                && ($facultyEdit['canHaveCoursesChild'] != $canHaveCoursesChild) )
                {
                     $dialogBox->info( get_lang('No change applied.') );
                }
                else
                {
                    // If the category can't have course child, look if they haven't already
                    if($canHaveCoursesChild == 'FALSE' )
                    {
                        $sql_SearchCourses = " SELECT count(cours_id) num"
                        .                    " FROM `" . $tbl_course . "`"
                        .                    " WHERE faculte = '" . claro_sql_escape( $facultyEdit['code']) . "'";
    
                        $array=claro_sql_query_fetch_all($sql_SearchCourses);
    
                        if($array[0]['num'] > 0)
                        {
                            $dialogBox->error( get_lang('This category include some courses, you must delete or move them before') );
                            $canHaveCoursesChild = "TRUE";
                        }
                        else
                        {
                            $sql_ChangeInfoFaculty= " UPDATE `" . $tbl_course_node . "`
                                                      SET name='" . claro_sql_escape($_REQUEST['nameCat']) . "',
                                                          code='" . claro_sql_escape($_REQUEST['codeCat']) . "',
                                                          canHaveCoursesChild='" . $canHaveCoursesChild . "'
                                                      WHERE id='" . (int) $_REQUEST['id'] . "'";
                            claro_sql_query($sql_ChangeInfoFaculty);
                            $dialogBox->success( get_lang('Changes have been saved') );
                        }
                    }
                    else
                    {
                        $sql_ChangeInfoFaculty= "SELECT id
                                                FROM `" . $tbl_course_node . "`
                                                WHERE code = '". claro_sql_escape($_REQUEST["codeCat"]) ."'
                                                AND id != '". (int)$_REQUEST["id"] ."'"
                                                ;
                        $_id = claro_sql_query_fetch_single_value( $sql_ChangeInfoFaculty );
                        if($_id )
                        {
                            $dialogBox->error( get_lang('Code already exists for an other category') );
                        }
                        else
                        {
                            $sql_ChangeInfoFaculty= " UPDATE `" . $tbl_course_node . "`
                                                      SET name='". claro_sql_escape($_REQUEST["nameCat"]) ."',
                                                          code='". claro_sql_escape($_REQUEST["codeCat"]) ."',
                                                          canHaveCoursesChild='".$canHaveCoursesChild."'
                                                          WHERE id='". (int)$_REQUEST["id"]."'";
                            $result = claro_sql_query($sql_ChangeInfoFaculty);
                            if( !$result )
                            {
                                $dialogBox->error( get_lang('Code already exists for an other category') );
                            }
                            else
                            {
                                // Change code_P for his childeren
                                if($_REQUEST['codeCat'] != $facultyEdit['code'])
                                {
                                    $sql_ChangeCodeParent= " UPDATE `" . $tbl_course_node . "`
                                                             SET code_P='" . claro_sql_escape($_REQUEST['codeCat']) . "'
                                                             WHERE code_P='" . claro_sql_escape($facultyEdit['code']) . "'";
                                    claro_sql_query($sql_ChangeCodeParent);
                                }
            
                                // Confirm edition
                                $dialogBox->success( get_lang('Changes have been saved') );
                            }
                        }    
                        
                    }
    
                    //Change the code of the faculte in the table cours
                    if($facultyEdit['code'] != $_REQUEST['codeCat'])
                    {
                        $sql_ChangeInfoFaculty=" UPDATE `" . $tbl_course . "`
                                                 SET faculte='" . claro_sql_escape($_REQUEST['codeCat']) . "'
                                                 WHERE faculte='" . claro_sql_escape($facultyEdit['code']) . "'";
    
                        claro_sql_query($sql_ChangeInfoFaculty);
                    }
                }
            }
            elseif(!strcmp($facultyEdit['code_P'],$_REQUEST['fatherCat']) ||
                  ($_REQUEST["fatherCat"] == 'NULL' && $facultyEdit['code_P']==NULL))
            {
                $dialogBox->info( get_lang('No change applied.') );
            }
            else
            {
                //Move the category
                //($_REQUEST["MoveChild"]==1)
                //For the table
                $fatherCat = (!strcmp($_REQUEST['fatherCat'],'NULL') ? '' : $_REQUEST['fatherCat']);
    
                //Check all children to look if the new parent of this category isn't his child
                //The first and last treePos of his child
                $treeFirst = $facultyEdit['treePos'];
                $treeLast  = $facultyEdit['treePos'] + $facultyEdit['nb_childs'];
    
                $error=0;
                for($i=$treeFirst; $i<= $treeLast; $i++)
                {
                    $sql_SearchChild = " SELECT code FROM `" . $tbl_course_node . "`
                                         WHERE treePos=" . (int) $i;
                    $code = claro_sql_query_get_single_value($sql_SearchChild);
    
                    if($_REQUEST['fatherCat'] == $code)
                    $error=1;
                }
    
                if($error)
                {
                    $dialogBox->error( get_lang("You can't move %facultyCode in the same category !", array('%facultyCode'=>$facultyEdit['code'])) );
                }
                else
                {
                    // The treePos afther his childeren
                    $treePosLastChild = $facultyEdit['treePos']+$facultyEdit['nb_childs'];
    
                    // The treePos max
                    $maxTree=search_max_tree_pos();
    
                    // The treePos of her and his childeren = max(treePos)+i
                    $i=1;
                    while($i <= $facultyEdit['nb_childs']+1)
                    {
                        $sql_TempTree=" UPDATE `" . $tbl_course_node . "`
                                        SET treePos=" . $maxTree . "+" . $i . "
                                        WHERE treePos = " . (int) $facultyEdit['treePos'] . "+" . $i . " - 1";
    
                        claro_sql_query($sql_TempTree);
                        $i++;
                    }
    
                    // Change treePos of the faculty they have a treePos > treePos of the last child
                    $sql_ChangeTree= " UPDATE `" . $tbl_course_node . "`
                                       SET treePos = treePos - " . (int) $facultyEdit['nb_childs'] . "-1
                                       WHERE treePos > " . (int) $treePosLastChild . " AND treePos <= " . (int) $maxTree;
    
                    claro_sql_query($sql_ChangeTree);
    
                    // if the father isn't root
                    if($_REQUEST['fatherCat'] != 'NULL')
                    {
                        // Search treePos of the new father
                        $newFather = get_cat_data(get_cat_id_from_code($_REQUEST['fatherCat']));
    
                        //Ajoute a tous les treePos apres le nouveau pere le nombre d enfant + 1 de celui qu on deplace
                        $sql_ChangeTree=" UPDATE `" . $tbl_course_node . "`
                                          SET treePos=treePos + " . (int)$facultyEdit['nb_childs'] . " + 1
                                          WHERE treePos > " . (int) $newFather['treePos'] . " and treePos <= " . (int) $maxTree;
    
                        claro_sql_query($sql_ChangeTree);
    
                        // the new treePos is the treePos of the new father+1
                        $newTree = $newFather['treePos'] + 1;
                    }
                    else
                    {
                        // The new treePos is the last treePos exist
                        $newTree = $maxTree;
                    }
    
                    // Change the treePos of her and his childeren
                    $i=0;
                    while($i <= $facultyEdit['nb_childs'])
                    {
                        $sql_ChangeTree= " UPDATE `" . $tbl_course_node . "`
                                           SET treePos=" . $newTree . "+" . $i . "
                                           WHERE treePos=" . $maxTree . "+" . $i . "+1";
    
                        claro_sql_query($sql_ChangeTree);
                        $i++;
                    }
    
                    // Change the category edit
                    $sql_ChangeInfoFaculty= " UPDATE `" . $tbl_course_node . "`";
                    if ($_REQUEST['fatherCat'] == 'NULL' )
                    {
                        $sql_ChangeInfoFaculty .= "SET code_P = NULL ";
                    }
                    else
                    {
                        $sql_ChangeInfoFaculty .= "SET code_P = '" . claro_sql_escape($_REQUEST['fatherCat']) . "' ";
                    }
    
                    $sql_ChangeInfoFaculty .= " WHERE id='" . (int) $_REQUEST['id'] . "'";
    
                    claro_sql_query($sql_ChangeInfoFaculty);
    
                    $newNbChild = $facultyEdit['nb_childs'] + 1;
    
                    // Change the number of childeren of the father category and his parent
                    $fatherChangeChild=$facultyEdit['code_P'];
    
                    delete_qty_child_father($fatherChangeChild, $newNbChild);
    
                    // Change the number of childeren of the new father and his parent
                    $fatherChangeChild=$_REQUEST['fatherCat'];
                    addNbChildFather($fatherChangeChild,$newNbChild);
    
                    // Search nb_childs of the new father
                    $nbChildFather = get_node_descendance_count(($_REQUEST['fatherCat'] == 'NULL') ? null : $_REQUEST['fatherCat']);
                    // Si le nouveau pere avait des enfants replace celui que l on vient de deplacer comme dernier enfant
                    if($nbChildFather>$facultyEdit['nb_childs'] + 1)
                    {
                        // Met des treePos temporaire pour celui qu on vient de deplacer et ses enfants
                        $i=1;
                        while( $i <= $facultyEdit['nb_childs'] + 1 )
                        {
                            $sql_TempTree = " UPDATE `" . $tbl_course_node . "`
                                              SET treePos=" . $maxTree . "+" . $i . "
                                              WHERE treePos=" . $newTree . "+" . $i . "-1";
    
                            claro_sql_query($sql_TempTree);
                            $i++;
                        }
    
                        // Deplace les enfants restant du pere
                        $i=1;
                        while($i<= ( $nbChildFather - $facultyEdit['nb_childs'] - 1 ) )
                        {
                            $sql_MoveTree= " UPDATE `" . $tbl_course_node . "`
                                             SET treePos=" . $newTree . " + " . $i . "-1
                                             WHERE treePos=" . $newTree . " + " . $facultyEdit['nb_childs'] . "+" . $i;
                            claro_sql_query($sql_MoveTree);
                            $i++;
                        }
    
                        // Remet les treePos de celui qu on a deplacé et de ses enfants
                        $i=1;
                        while($i <= $facultyEdit['nb_childs'] + 1)
                        {
                            $sql_TempTree= " UPDATE  `" . $tbl_course_node . "`
                                            SET
                                treePos=" . (int) $newTree . "+" . (int) $nbChildFather . '-' . (int) $facultyEdit['nb_childs'] . "-2+" . $i . "
                                WHERE treePos=".(int)$maxTree."+".$i;
    
                            claro_sql_query($sql_TempTree);
                            $i++;
                        }
    
                        // Confirm move
                        $dialogBox->success( get_lang('Category moved') );
                    }
                }
            }
        }
        else // if the new category don't have a name or a code or she can't have child (categories or courses)
        {
            if(empty($_REQUEST['nameCat']))
            $dialogBox->error( get_lang('Category name is required') );

            if(empty($_REQUEST['codeCat']))
            $dialogBox->error( get_lang('Category code is required') );
        }
    }

    /**
     * search informations from the table
     */

    $sql_searchfaculty = " SELECT *
                           FROM `" . $tbl_course_node . "`
                           ORDER BY treePos";
    $catList=claro_sql_query_fetch_all($sql_searchfaculty);

    $tempCategories=$categories;
    unset($categories);

    // Build the array of categories
    if ($catList)
    {
        $i=0;
        for($i=0;$i<count($catList);$i++)
        {
            $catList[$i]['visible']=TRUE;
            $categories[]=$catList[$i];
        }

        // Pour remettre a visible ou non comme prédédement
        for($i=0;$i<count($categories);$i++)
        {
            $searchId=$categories[$i]["id"];
            $j=0;
            while($j<count($tempCategories) && strcmp($tempCategories[$j]['id'],$categories[$i]['id']))
            $j++;

            if($j<count($tempCategories))
            {
                $categories[$i]['visible']=$tempCategories[$j]['visible'];
            }
        }

        $_SESSION['categories'] = $categories;

    }
    else
    {
        $dialogBox->info( get_lang('There is no category available !') );

        $categories=NULL;
        $_SESSION['categories'] = $categories;

    }
}


/**
 * prepare display
 */

$category_array = claro_get_cat_flat_list();
// If there is no current $category, add a fake option
// to prevent auto select the first in list
if ( isset($category['id']) && is_array($category_array)
&& in_array($category['id'] ,$category_array))
{
    $cat_preselect = $category['id'];
}
else
{
    $cat_preselect = 'choose_one';
    $category_array = array_merge(array('--'=>'choose_one'),$category_array);
}

switch ($display_form)
{
    case DISP_FORM_CREATE :
    {
        $dialogBox->title( get_lang('Create a category') );
        
        $form = '<form action="' . $_SERVER['PHP_SELF'] . '" method="POST">' . "\n"
        .    '<input type="hidden" name="cmd" value="exCreate"  />' . "\n"
        .     '<input type="hidden" name="claroFormId" value="'.uniqid('').'" />' . "\n"
        .    '<label for="nameCat"> ' .  get_lang('Category name') . '<span class="required">*</span></label><br />' . "\n"
        .    '<input type="texte" name="nameCat" id="nameCat" value="' .  htmlspecialchars($editedCat_Name) . '" size="30" maxlength="100" /><br /><br />' . "\n"

        .    '<label for="codeCat"> ' . get_lang('Category code') . '<span class="required">*</span></label><br />' . "\n"
        .    '<input type="texte" name="codeCat" id="codeCat" value="' . htmlspecialchars($editedCat_Code) . '" size="30" maxlength="40" /><br /><br />' . "\n"

        .    '<label for="canHaveCoursesChild">' .  get_lang('Can have courses') . '<span class="required">*</span></label><br />' . "\n"
        .    '<input type="radio" name="canHaveCoursesChild" id="canHaveCoursesChild_1" '
        .    (isset($editedCat_CanHaveCoursesChild)
             ?    (!strcmp($editedCat_CanHaveCoursesChild,"TRUE")?'checked':'')
             :    'checked'
             )
        .    ' value="1">'
        .    '<label for="canHaveCoursesChild_1">' .  get_lang('Yes') . '</label>' . "\n"
        .    '<input type="radio" name="canHaveCoursesChild" id="canHaveCoursesChild_0" '
        ;

        if(isset($editedCat_CanHaveCoursesChild))
            $form .= (!strcmp($editedCat_CanHaveCoursesChild,"FALSE")?"checked":"");

        $form .= ' value="0" />' . "\n"
        .    ' ' . "\n"
        .    '<label for="canHaveCoursesChild_0">' .  get_lang('No') . '</label>' . "\n"
        .    '<br /><br />' . "\n"
        .    '<label for="fatherCat"> ' .  get_lang('Parent category') . ' </label>' . "\n"
        .    '<select name="fatherCat" id="fatherCat">' . "\n"
        .    '<option value="NULL" > &nbsp;&nbsp;&nbsp;' .  get_conf('siteName') . '</option>'

        // Display each category in the select
        .   build_select_faculty($categories,null,null,'')

        .    '</select>' . "\n"
        .    '<br /><br />' . "\n"
        .    '<span class="required">*</span>&nbsp;'.get_lang('Denotes required fields') . '<br />' . "\n"

        .    '<input type="submit" value="' . get_lang('Ok') . '" />' . "\n"
        .    claro_html_button($_SERVER['PHP_SELF'], get_lang('Cancel'))
        .    '</form>' . "\n"
        ;

        $dialogBox->form( $form );

    }
    break;
    case DISP_FORM_EDIT :
    {
        $dialogBox->title( get_lang('Edit a category') );
        /**
         * Display information to edit a category and the bom of categories
         */

        $form = '<form action="' .  $_SERVER['PHP_SELF'] . '" method="post">' . "\n"
        .    '<input type="hidden" name="cmd" value="exChange" />' . "\n"
        .    '<input type="hidden" name="claroFormId" value="'.uniqid('').'" />' . "\n"
        .    '<input type="hidden" name="id" value="' .  $editedCat_Id .'" />' . "\n"
        .    '<label for="nameCat"> ' .  get_lang('Category name') . '<span class="required">*</span></label ><br />' . "\n"
        .    '<input type="texte" name="nameCat" id="nameCat" value="' .  htmlspecialchars($editedCat_Name) . '" size="30" maxlength="100" /><br /><br />' . "\n"
        .    '<label for="codeCat"> ' .  get_lang('Category code') . '<span class="required">*</span></label ><br />' . "\n"
        .    '<input type="texte" name="codeCat" id="codeCat" value="' .  htmlspecialchars($editedCat_Code) . '" size="30" maxlength="40" /><br /><br />' . "\n"
        .    '<label for="canHaveCoursesChild"> ' .  get_lang('Can have courses') . '<span class="required">*</span></label><br />' . "\n"
        .    '<input type="radio" name="canHaveCoursesChild" id="canHaveCoursesChild_1"' . "\n"
        ;

        if(isset($editedCat_CanHaveCoursesChild))
            $form .= (!strcmp($editedCat_CanHaveCoursesChild,'TRUE') ? 'checked' : '');
        else
            $form .= "checked";

        $form .= ' value="1" />' . "\n"
        .    ' ' . "\n"
        .    '<label for="canHaveCoursesChild_1">' .  get_lang('Yes') . '</label>' . "\n"
        .    '' . "\n"
        .    '<input type="radio" name="canHaveCoursesChild" id="canHaveCoursesChild_0" '
        ;

        if(isset($editedCat_CanHaveCoursesChild)) $form .= (!strcmp($editedCat_CanHaveCoursesChild, 'FALSE') ? 'checked' : '');

        $form .= ' value="0" />' . "\n"
        .    '<label for="canHaveCoursesChild_0">' .  get_lang('No') . '</label>' . "\n"
        .    '<br /><br />' . "\n"
        .    '<span class="required">*</span>&nbsp;'.get_lang('Denotes required fields') . '<br />' . "\n"

        .    '<input type="submit" value="' . get_lang('Ok') . '" />' . "\n"
        .    claro_html_button($_SERVER['PHP_SELF'], get_lang('Cancel'))
        .    '</form>' . "\n"
        ;

        $dialogBox->form( $form );

    }
    break;
    case  DISP_FORM_MOVE :
    {
        /**
         * Display information to change root of the category
         */
        $dialogBox->title( get_lang("Change parent's category of %catCode", array('%catCode' => $editedCat_Code)) );
        $form = '<form action=" ' .  $_SERVER['PHP_SELF'] . '" method="post">' . "\n"
        .    '<input type="hidden" name="id" value="' .  $editedCat_Id . '" />' . "\n"
        .    '<input type="hidden" name="cmd" value="exChange" />' . "\n"
        .    '<input type="hidden" name="nameCat" value="'.$editedCat_Name.'" />' . "\n"
        .    '<input type="hidden" name="codeCat" value="'.$editedCat_Code.'" />' . "\n"
        .    '<input type="hidden" name="claroFormId" value="'.uniqid('').'" />' . "\n"
        .    '<label for="fatherCat"> ' . get_lang('Parent category') . ' </label >' . "\n"
        .    '<select name="fatherCat">' . "\n"
        .    '<option value="NULL" > &nbsp;&nbsp;&nbsp;' .  get_conf('siteName') . ' </option>' . "\n"
        ;

        //Display each category in the select
        $form .= build_select_faculty($categories,NULL,$editFather, '');

        $form .= '</select><br /><br />' . "\n"
        .    '<input type="submit" value="'.get_lang('Ok').'" />' . "\n"
        .    claro_html_button($_SERVER['PHP_SELF'], get_lang('Cancel'))
        .    '</form>' . "\n"
        ;

        $dialogBox->form( $form );

    }
    break;
    default :
    {
        //
    }
}

/**
 * Output
 */

// display claroline header
include get_path('incRepositorySys') . '/claro_init_header.inc.php';

echo claro_html_tool_title($nameTools);

echo $dialogBox->render();

/** 
 * Command and list
 */

echo '<p>' . "\n"
.    '<a class="claroCmd" href="' . $_SERVER['PHP_SELF'] . '?cmd=rqCreate">'
.    get_lang('Create a category')
.    '</a>' . "\n"
.    '</p>' . "\n"
.    '<table class="claroTable emphaseLine" width="100%" border="0" cellspacing="2">' . "\n"
.    '<thead>' . "\n"
.    '<tr class="headerX" align="center" valign="top">' . "\n"
// Add titles for the table
.    '<th>' . get_lang('Category code') . '</th>' . "\n"
.    '<th>' . get_lang('Courses') . '</th>'."\n"
.    '<th>' . get_lang('Edit') . '</th>'."\n"
.    '<th>' . get_lang('Move') . '</th>'."\n"
.    '<th>' . get_lang('Delete') . '</th>'."\n"
.    '<th colspan="2">' . get_lang('Order') . '</th>'."\n"
.    '</tr>' . "\n"
.    '</thead>' . "\n"
.    '<tbody>' . "\n"
;

claro_disp_tree($categories, NULL, '');

echo '</tbody>' . "\n"
.    '</table>' . "\n"
;

include get_path('incRepositorySys') . '/claro_init_footer.inc.php';

?>