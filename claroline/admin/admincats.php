<?php // $Id$
//----------------------------------------------------------------------
// CLAROLINE 1.6.*
//----------------------------------------------------------------------
// Copyright (c) 2001-2004 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: Muret Benoît && claroline Team.
//----------------------------------------------------------------------

$cidReset = TRUE;
$gidReset = TRUE;
$tidReset = TRUE;

// include claro main global
require '../inc/claro_init_global.inc.php';

// check if user is logged as administrator
$is_allowedToAdmin = $is_platformAdmin;
if (!$is_allowedToAdmin) claro_disp_auth_form();

include($includePath."/lib/debug.lib.inc.php");

// build bredcrump
$nameTools        = $langCategories;
$interbredcrump[] = array ("url"=>$rootAdminWeb, "name"=> $langAdministration);

// display claroline header
include($includePath."/claro_init_header.inc.php");

// get table name
$tbl_mdb_names   = claro_sql_get_main_tbl();
$tbl_course      = $tbl_mdb_names['course'           ];
$tbl_course_node = $tbl_mdb_names['category'         ];

// Display variables
$CREATE		= FALSE;
$EDIT		= FALSE;
$MOVE		= FALSE;

/**
 * Show or hide sub categories
 */

if ( isset($_REQUEST['id']) && 
    !isset($_REQUEST['cmd'])   )
{
    $id=$_REQUEST['id'];
    $categories=$_SESSION['categories'];

    // Change the parameter 'visible'

    if(!is_null($categories))
    {
        foreach($categories as $key=>$category)
        {
            if($category['id']==$id)
            {
                if($categories[$key]['visible'])
                    $categories[$key]['visible']=FALSE;
                else
                    $categories[$key]['visible']=TRUE;
            }
        }
    }

    // Save in session
    $_SESSION['categories']=$categories;
    session_register("categories");
}
else
{
    // Get value from session variables
    $categories=$_SESSION["categories"];

    /**
     * Create a category
     */

    if($_REQUEST['cmd'] == 'exCreate' )
    {
        // If the new category have a name, a code and she can have child (categories or courses)
        if( !empty($_REQUEST["nameCat"]) && !empty($_REQUEST["codeCat"]) )
        {
            
	        // If a category with the same code already exists we only display an error message
	        $sql_SearchSameCode=" SELECT code 
                                  FROM `" . $tbl_course_node . "` 
                                  WHERE code='".$_REQUEST["nameCat"]."'";
            $array=claro_sql_query_fetch_all($sql_SearchSameCode);

	        if (isset($array[0]["code"])) 
	        {	
		        // Error message for attempt to create a duplicate
		        $controlMsg['info'][]=$lang_faculty_CreateNotOk;
	        }
	        else
	        {	    
		        $nameCat=$_REQUEST["nameCat"];
		        $codeCat=$_REQUEST["codeCat"];
		        $fatherCat=$_REQUEST["fatherCat"];
		        $canHaveCoursesChild=($_REQUEST["canHaveCoursesChild"]==1?"TRUE":"FALSE");
	
		        // If the category don't have as parent NULL (root), all parent of this category have a child more
		        $fatherChangeChild=(!strcmp($fatherCat,"NULL")?NULL:$fatherCat);
	
		        addNbChildFather($fatherChangeChild,1);
	
		        // If the parent of the new category isn't root
		        if(strcmp($fatherCat,"NULL"))
		        {
			        $sql_SearchFather=" SELECT treePos,nb_childs 
                                        FROM `" . $tbl_course_node . "` 
                                        WHERE code='".$fatherCat."'";
			        $array=claro_sql_query_fetch_all($sql_SearchFather);
	
			        // The treePos from the new category (treePos from this father + nb_childs from this father)
			        $treePosCat=$array[0]["treePos"]+$array[0]["nb_childs"];
	
			        // Add 1 to all category who have treePos >= of the treePos of the new category
			        $sql_ChangeTree=" UPDATE `" . $tbl_course_node . "` 
                                      SET treePos=treePos+1 
                                      WHERE treePos>='".$treePosCat."'";
			        claro_sql_query($sql_ChangeTree);
		        }
		        else    // The parent of the new category is root
		        {
			        // Search the maximum treePos
			        $treePosCat=SearchMaxTreePos()+1;
		        }
	
		        // Insert the new category to the table
		        
                $sql_InsertCat=" INSERT INTO `". $tbl_course_node ."` 
                                 (name, code, bc , nb_childs, canHaveCoursesChild, canHaveCatChild,
                                  treePos ,code_P )
						         VALUES ('".$nameCat."','".$codeCat."',NULL,'0','".$canHaveCoursesChild."','TRUE',
                                  '".$treePosCat."'";
				if ($fatherCat == "NULL")
				{
				    $sql_InsertCat .= ",NULL)";
				}
				else
				{
				    $sql_InsertCat .= ",'".$fatherCat."')";
				}
	
		        claro_sql_query($sql_InsertCat);
	
		        // Confirm creating
		        $controlMsg['info'][]=$lang_faculty_CreateOk;

            }
        }
        else // if the new category don't have a name or a code or she can't have child (categories or courses)
        {
            if(empty($_REQUEST["nameCat"]))
                $controlMsg["error"][]=$lang_faculty_NameEmpty;

            if(empty($_REQUEST["codeCat"]))
                $controlMsg["error"][]=$lang_faculty_CodeEmpty;
        }
    }

    /**
     * If you move the category in the same father of the bom
     */
    
    if($_REQUEST['cmd']=='exUp' || $_REQUEST['cmd']=='exDown')
    {
        // Search the minimum and the maximum
        $sql_InfoTree=" SELECT min(treePos) minimum, max(treePos) maximum 
                        FROM `" . $tbl_course_node . "`";
        $array=claro_sql_query_fetch_all($sql_InfoTree);

        $TreeMin=$array[0]["minimum"];
        $TreeMax=$array[0]["maximum"];

        // Search the category who move in the bom
        $i=0;
        while($i<count($categories) && $categories[$i]["id"]!=$_REQUEST["id"])
            $i++;

        /**
         * If Up the category and the treePos of this category isn't the first category
         */

        if($_REQUEST['cmd']=='exUp' && $i>=$TreeMin )
        {
            // Search the previous brother of this category
            $j=$i-1;
            while($j>0 && strcmp($categories[$j]["code_P"],$categories[$i]["code_P"]))
                $j--;

            // If they are a brother
            if(!strcmp($categories[$j]["code_P"],$categories[$i]["code_P"]) )
            {
                // change the brother and his children
                for($k=0;$k<=$categories[$j]["nb_childs"];$k++)
                {
                    $searchId=$categories[$j+$k]["id"];
                    $newTree=$categories[$j]["treePos"]+$categories[$i]["nb_childs"]+1+$k;

                    $sql_Update = " UPDATE `" . $tbl_course_node . "` 
                                    SET treePos='".$newTree."' 
                                    WHERE id='".$searchId."'";
                    claro_sql_query($sql_Update) ;
                }

                // change the choose category and his childeren
                for($k=0;$k<=$categories[$i]["nb_childs"];$k++)
                {
                    $searchId=$categories[$i+$k]["id"];
                    $newTree=$categories[$i]["treePos"]-$categories[$j]["nb_childs"]-1+$k;

                    $sql_Update = " UPDATE `" . $tbl_course_node . "` 
                                    SET treePos='".$newTree."' 
                                    WHERE id='".$searchId."'";
                    claro_sql_query($sql_Update) ;
                }

                // Confirm move
                $controlMsg['info'][]=$lang_faculty_MoveOk;
            }
        }

        /**
         * If Up the category and the treePos of this category isn't the last category
         */

        if ($_REQUEST['cmd']=='exDown' && $i<$TreeMax-1 )
        {
            // Search the next brother
            $j=$i+1;
            while($j<=count($categories) && strcmp($categories[$j]["code_P"],$categories[$i]["code_P"]))
                $j++;

            // If they are a brother
            if(!strcmp($categories[$j]["code_P"],$categories[$i]["code_P"]))
            {
                // change the brother and his children
                for($k=0;$k<=$categories[$j]["nb_childs"];$k++)
                {
                    $searchId=$categories[$j+$k]["id"];
                    $newTree=$categories[$j]["treePos"]-$categories[$i]["nb_childs"]-1+$k;

                    $sql_Update = " UPDATE `". $tbl_course_node . "` 
                                    SET treePos='".$newTree."' 
                                    WHERE id='".$searchId."'";
                    claro_sql_query($sql_Update);
                }

                // change the choose category and his childeren
                for($k=0;$k<=$categories[$i]["nb_childs"];$k++)
                {
                    $searchId=$categories[$i+$k]["id"];
                    $newTree=$categories[$i]["treePos"]+$categories[$j]["nb_childs"]+1+$k;

                    $sql_Update = " UPDATE `" . $tbl_course_node . "` 
                                    SET treePos='".$newTree."' 
                                    WHERE id='".$searchId."'";
                    claro_sql_query($sql_Update) ;
                }

                //Confirm move
                $controlMsg['info'][]=$lang_faculty_MoveOk;
            }
        }

    }

    /**
     * If you delete a category
     */

    if($_REQUEST['cmd'] == 'exDelete')
    {

        // Search information about category
        $sql_SearchDelete = " SELECT code, code_P, treePos, nb_childs
                 FROM `". $tbl_course_node . "`
                 WHERE id='".$_REQUEST['id']."'";
        $res_SearchDelete = claro_sql_query_fetch_all($sql_SearchDelete);

        if ($res_SearchDelete != FALSE)
        {
	        // we delete if we do not encounter any problem...default is that there is no problem, then we check
	        $delok = TRUE;

            $code_parent  = $res_SearchDelete[0]['code_P'];
            $code_cat     = $res_SearchDelete[0]['code'];
            $nb_childs    = $res_SearchDelete[0]['nb_childs'];
            $treePos      = $res_SearchDelete[0]['treePos'];
	    
	        // Look if there isn't any subcategory in this category first	    
	        if($nb_childs > 0) 
	        {
	    	    $controlMsg['error'][]=$lang_faculty_CatHaveCat;
        		$delok = FALSE;
	        }
	    
	        // Look if they aren't courses in this category
            $sql_SearchCourses= "SELECT count(cours_id) num 
                                 FROM `" . $tbl_course . "` 
                                 WHERE faculte='".$code_cat."'";
            $res_SearchCourses= claro_sql_query_fetch_all($sql_SearchCourses);

            if ($res_SearchCourses[0]["num"]>0) 
            {
                $controlMsg['error'][]=$lang_faculty_CatHaveCourses;
        		$delok = FALSE;
            }
            
            if ($delok==TRUE) 
            {
                // Delete the category
                $sql_Delete= " DELETE FROM `" . $tbl_course_node . "` 
                               WHERE id='".$_REQUEST["id"]."'";
                claro_sql_query($sql_Delete);

                // Update nb_child of the parent
                if ($code_parent != NULL)
                {
                    $sql_update = " UPDATE `" . $tbl_course_node . "` 
                                    SET nb_childs = nb_childs - 1
                                    WHERE code ='". $code_parent ."'";
                    claro_sql_query($sql_update);
                }
                
                // Update treePos of next categories
                $sql_update = " UPDATE `" . $tbl_course_node . "` 
                                SET treePos = treePos - 1
                                WHERE treePos > '". $treePos ."'";
                claro_sql_query($sql_update);
               
                //Confirm deleting
                $controlMsg['info'][]=$lang_faculty_DeleteOk;
            }
        }

    }
    
    /**
     * Create a category : display form
     */

    if($_REQUEST['cmd'] == 'rqCreate')
    {
        $CREATE=TRUE;
    }
    
    /**
     * Edit a category : display form
     */

    if($_REQUEST['cmd'] == 'rqEdit')
    {
        $EDIT=TRUE;

        // Search information of the category edit
        $sql_SearchInfoTreeFaculty = " SELECT * FROM `" . $tbl_course_node . "` 
                                       WHERE id='".$_REQUEST["id"]."'";
        $array=claro_sql_query_fetch_all($sql_SearchInfoTreeFaculty);

        $EditId=$array[0]["id"];
        $EditName=$array[0]["name"];
        $EditCode=$array[0]["code"];
        $EditFather=$array[0]["code_P"];
        $EditCanHaveCatChild=$array[0]["canHaveCatChild"];
        $EditCanHaveCoursesChild=$array[0]["canHaveCoursesChild"];
	
    }
    
    /**
     * Move a category : display form
     */
        
    if($_REQUEST['cmd'] == 'rqMove')
    {
        // Search information of the category edit
        $sql_SearchInfoTreeFaculty = " SELECT * FROM `" . $tbl_course_node . "` 
                                       WHERE id='".$_REQUEST["id"]."'";
        $array=claro_sql_query_fetch_all($sql_SearchInfoTreeFaculty);

        $EditId=$array[0]["id"];
        $EditName=$array[0]["name"];
        $EditCode=$array[0]["code"];
        $EditFather=$array[0]["code_P"];
        $EditCanHaveCatChild=$array[0]["canHaveCatChild"];
        $EditCanHaveCoursesChild=$array[0]["canHaveCoursesChild"];

        $MOVE=TRUE;
    }

    /**
     * Change information of category : do change in db
     */

    if($_REQUEST['cmd'] == 'exChange' )
    {
        // Search information
        $sql_FacultyEdit = " SELECT * FROM `" . $tbl_course_node . "` 
                             WHERE id='".$_REQUEST["id"]."'";
        $arrayfacultyEdit=claro_sql_query_fetch_all($sql_FacultyEdit);
        $facultyEdit=$arrayfacultyEdit[0];
    	$doChange = TRUE;
	
    	// See if we try to set the categorie as a cat that can not have course 
        // and that the cat already contain courses
	    if ($_REQUEST["canHaveCoursesChild"]==0)
    	{
	    	$sql_SearchCourses= " SELECT count(cours_id) num 
                                  FROM `" . $tbl_course . "` 
                                  WHERE faculte='".$treePosDelete["code"]."'";
        	$res_SearchCourses=claro_sql_query_fetch_all($sql_SearchCourses);

            if($res_SearchCourses[0]["num"]>0)
		    {
			    $controlMsg['warning'][]=$lang_faculty_HaveCourses;
			    $doChange = false;
		    }
	    }
	
        // Edit a category (don't move the category)
        if(!isset($_REQUEST["fatherCat"]) && $doChange)
        {
            $canHaveCoursesChild=($_REQUEST["canHaveCoursesChild"]==1?"TRUE":"FALSE");

            // If nothing is different
            if(!strcmp($facultyEdit["name"],$_REQUEST["nameCat"]) && !strcmp($facultyEdit["code"],$_REQUEST["codeCat"])
              && !strcmp($facultyEdit["canHaveCoursesChild"],$canHaveCoursesChild) )
            {
                $controlMsg['warning'][]=$lang_faculty_NoChange;
            }
            else
            {
                // If the category can't have course child, look if they haven't already
                if(!strcmp($canHaveCoursesChild,"FALSE"))
                {
                    $sql_SearchCourses = " SELECT count(cours_id) num 
                                           FROM `" . $tbl_course . "` 
                                           WHERE faculte='".$facultyEdit["code"]."'";
                    $array=claro_sql_query_fetch_all($sql_SearchCourses);

                    if($array[0]["num"]>0)
                    {
                        $controlMsg['warning'][]=$lang_faculty_HaveCourses;
                        $canHaveCoursesChild="TRUE";
                    }
                    else
                    {
                        $sql_ChangeInfoFaculty= " UPDATE `" . $tbl_course_node . "` 
                                                  SET name='".$_REQUEST["nameCat"]."',
                                                      code='".$_REQUEST["codeCat"]."',
                                                      canHaveCoursesChild='".$canHaveCoursesChild."' 
                                                  WHERE id='".$_REQUEST["id"]."'";
                        claro_sql_query($sql_ChangeInfoFaculty);
            		    $controlMsg['warning'][]=$lang_faculty_EditOk;
                    }
                }
                else
                {
                    $sql_ChangeInfoFaculty= " UPDATE `" . $tbl_course_node . "` 
                                              SET name='".$_REQUEST["nameCat"]."',
                                                  code='".$_REQUEST["codeCat"]."',
                                                  canHaveCoursesChild='".$canHaveCoursesChild."' 
                                                  WHERE id='".$_REQUEST["id"]."'";
                    claro_sql_query($sql_ChangeInfoFaculty);

                    // Change code_P for his childeren
                    if(strcmp($_REQUEST["codeCat"],$facultyEdit["code"]))
                    {
                        $sql_ChangeCodeParent= " UPDATE `" . $tbl_course_node . "` 
                                                 SET code_P='".$_REQUEST["codeCat"]."' 
                                                 WHERE code_P='".$facultyEdit["code"]."'";
                        claro_sql_query($sql_ChangeCodeParent);
                    }

                    // Confirm edition
                    $controlMsg['info'][]=$lang_faculty_EditOk;
                }

                //Change the code of the faculte in the table cours
                if(strcmp($facultyEdit["code"],$_REQUEST["codeCat"]))
                {
                    $sql_ChangeInfoFaculty=" UPDATE `$tbl_course` 
                                             SET faculte='".$_REQUEST["codeCat"]."'
                                             WHERE faculte='".$facultyEdit["code"]."'";

                    claro_sql_query($sql_ChangeInfoFaculty);
                }
            }
        }
        elseif(!strcmp($facultyEdit["code_P"],$_REQUEST["fatherCat"]) ||
                ($_REQUEST["fatherCat"]=="NULL" && $facultyEdit["code_P"]==NULL))
        {
            $controlMsg['warning'][]=$lang_faculty_NoChange;
        }
        else
        {
            //Move the category 
            //($_REQUEST["MoveChild"]==1)
            //For the table
            $fatherCat=(!strcmp($_REQUEST["fatherCat"],"NULL")?"":$_REQUEST["fatherCat"]);

            //Check all children to look if the new parent of this category isn't his child
            //The first and last treePos of his child
            $treeFirst=$facultyEdit["treePos"];
            $treeLast=$facultyEdit["treePos"]+$facultyEdit["nb_childs"];

            $error=0;
            for($i=$treeFirst;$i<=$treeLast;$i++)
            {
                $sql_SearchChild = " SELECT code FROM `" . $tbl_course_node . "` 
                                     WHERE treePos=".$i;
                $code=claro_sql_query_fetch_all($sql_SearchChild);

                if(!strcmp($_REQUEST["fatherCat"],$code[0]["code"]))
                    $error=1;
            }

            if($error)
            {
                $controlMsg['error'][]=$lang_faculty_NoMove_1.$facultyEdit["code"].$lang_faculty_NoMove_2;
            }
            else
            {
                // The treePos afther his childeren
                $treePosLastChild=$facultyEdit["treePos"]+$facultyEdit["nb_childs"];

                // The treePos max
                $maxTree=SearchMaxTreePos();

                // The treePos of her and his childeren = max(treePos)+i
                $i=1;
                while($i<=$facultyEdit["nb_childs"]+1)
                {
                    $sql_TempTree=" UPDATE `" . $tbl_course_node . "` 
                                    SET treePos=".$maxTree."+".$i."
                                    WHERE treePos=".$facultyEdit["treePos"]."+".$i."-1";

                    claro_sql_query($sql_TempTree);
                    $i++;
                }

                // Change treePos of the faculty they have a treePos > treePos of the last child
                $sql_ChangeTree= " UPDATE `" . $tbl_course_node . "` 
                                   SET treePos=treePos-".$facultyEdit["nb_childs"]."-1 
                                   WHERE treePos>".$treePosLastChild." AND treePos<=".$maxTree;

                claro_sql_query($sql_ChangeTree);

                // if the father isn't root
                if(strcmp($_REQUEST["fatherCat"],"NULL"))
                {
                    // Search treePos of the new father
                    $sql_SearchNewTreePos=" SELECT treePos FROM `" . $tbl_course_node . "` 
                                            WHERE code='".$_REQUEST["fatherCat"]."'";
                    $res_SearchNewTreePos=claro_sql_query_fetch_all($sql_SearchNewTreePos);

                    $newFather=$res_SearchNewTreePos[0];

                    //Ajoute a tous les treePos apres le nouveau pere le nombre d enfant + 1 de celui qu on deplace
                    $sql_ChangeTree=" UPDATE `". $tbl_course_node . "` 
                                      SET treePos=treePos+".$facultyEdit["nb_childs"]."+1 
                                      WHERE treePos>".$newFather["treePos"]." and treePos<=".$maxTree;

                    claro_sql_query($sql_ChangeTree);

                    // the new treePos is the treePos of the new father+1
                    $newTree=$newFather["treePos"]+1;
                }
                else
                {
                    // The new treePos is the last treePos exist
                    $newTree=$maxTree;
                }

                // Change the treePos of her and his childeren
                $i=0;
                while($i<=$facultyEdit["nb_childs"])
                {
                    $sql_ChangeTree= " UPDATE `" . $tbl_course_node . "` 
                                       SET treePos=".$newTree."+".$i." 
                                       WHERE treePos=".$maxTree."+".$i."+1";

                    claro_sql_query($sql_ChangeTree);
                    $i++;
                }

                $father=(!strcmp($_REQUEST["fatherCat"],"NULL")?"NULL":("'".$_REQUEST["fatherCat"]."'"));

                // Change the category edit
                $sql_ChangeInfoFaculty= " UPDATE `" . $tbl_course_node . "` 
                                          SET code_P=".$father." 
                                          WHERE id='".$_REQUEST["id"]."'";

                claro_sql_query($sql_ChangeInfoFaculty);

                $newNbChild=$facultyEdit["nb_childs"]+1;

                // Change the number of childeren of the father category and his parent
                $fatherChangeChild=$facultyEdit["code_P"];
                deleteNbChildFather($fatherChangeChild,$newNbChild);

                // Change the number of childeren of the new father and his parent
                $fatherChangeChild=$_REQUEST["fatherCat"];
                addNbChildFather($fatherChangeChild,$newNbChild);

                // Search nb_childs of the new father
                $sql_SearchNbChild=" SELECT nb_childs 
                                     FROM `" . $tbl_course_node. "` 
                                     WHERE code=".$father;
                $array=claro_sql_query_fetch_all($sql_SearchNbChild);

                $nbChildFather=$array[0];

                // Si le nouveau pere avait des enfants replace celui que l on vient de deplacer comme dernier enfant
                if($nbChildFather["nb_childs"]>$facultyEdit["nb_childs"]+1)
                {
                    // Met des treePos temporaire pour celui qu on vient de deplacer et ses enfants
                    $i=1;
                    while($i<=$facultyEdit["nb_childs"]+1)
                    {
                        $sql_TempTree = " UPDATE `" . $tbl_course_node . "` 
                                          SET treePos=".$maxTree."+".$i."
                                          WHERE treePos=".$newTree."+".$i."-1";

                        claro_sql_query($sql_TempTree);
                        $i++;
                    }

                    // Deplace les enfants restant du pere
                    $i=1;
                    while($i<=($nbChildFather["nb_childs"]-$facultyEdit["nb_childs"]-1) )
                    {
                        $sql_MoveTree= " UPDATE `" . $tbl_course_node . "` 
                                         SET treePos=".$newTree."+".$i."-1
                                         WHERE treePos=".$newTree."+".$facultyEdit["nb_childs"]."+".$i;
                        claro_sql_query($sql_MoveTree);
                        $i++;
                    }

                    // Remet les treePos de celui qu on a deplacé et de ses enfants
                    $i=1;
                    while($i<=$facultyEdit["nb_childs"]+1)
                    {
                        $sql_TempTree= " UPDATE  `" . $tbl_course_node . "` 
                                        SET
                            treePos=".$newTree."+".$nbChildFather["nb_childs"]."-".$facultyEdit["nb_childs"]."-2+".$i."
                            WHERE treePos=".$maxTree."+".$i;

                        claro_sql_query($sql_TempTree);
                        $i++;
                    }

                    // Confirm move
                    $controlMsg['info'][]=$lang_faculty_MoveOk;
                }
            }
        }    
    }

    /** 
     * search informations from the table
     */

    $sql_searchfaculty = " SELECT * 
                           FROM `" . $tbl_course_node . "` 
                           ORDER BY treePos";
    $array=claro_sql_query_fetch_all($sql_searchfaculty);

    $tempCategories=$categories;
    unset($categories);

    // Build the array of catégories
    if ($array)
    {
        $i=0;
        for($i=0;$i<count($array);$i++)
        {
            $array[$i]["visible"]=TRUE;
            $categories[]=$array[$i];
        }

        // Pour remettre a visible ou non comme prédédement
        for($i=0;$i<count($categories);$i++)
        {
            $searchId=$categories[$i]["id"];
            $j=0;
            while($j<count($tempCategories) && strcmp($tempCategories[$j]["id"],$categories[$i]["id"]))
                $j++;

            if($j<count($tempCategories))
            {
                $categories[$i]["visible"]=$tempCategories[$j]["visible"];
            }
        }

        $_SESSION['categories']=$categories;

        // Session
        session_unregister("categories");
        session_register("categories");
    }
    else
    {
        $controlMsg['warning'][]=$lang_faculty_NoCat;

        $categories=NULL;
        $_SESSION['categories']=$categories;

        // Session
        session_unregister("categories");
        session_register("categories");
    }
}

/**
 * Display
 */

 /**
  * Information edit for create or edit a category
  */

if($CREATE)
{
    claro_disp_tool_title(array( 'mainTitle'=>$nameTools,'subTitle'=>$langSubTitleCreate));
    claro_disp_msg_arr($controlMsg);
?>
    <form action="<?php echo $_SERVER['PHP_SELF']?>" method="POST">
    <input type="hidden" name="cmd" value="exCreate" />
    <table border="0">
    <tr>
        <td >
        <label for="nameCat"> <?php echo $lang_faculty_NameCat; ?> </label >
        </td>

        <td>
        <input type="texte" name="nameCat" id="nameCat" value="<?php echo $EditName; ?>" size="20" maxlength="100">
        </td>
    </tr>
    <tr>
        <td >
        <label for="codeCat"> <?php echo $lang_faculty_CodeCat; ?> </label >
        </td>

        <td>
     	   <input type="texte" name="codeCat" id="codeCat" value="<?php echo $EditCode; ?>" size="20" maxlength="40">
        </td>
    </tr>
    <tr>
        <td>
        <label for="canHaveCoursesChild"> <?php echo $lang_faculty_CanHaveCatCourse; ?> </label>
        </td>

        <td>
        <input type="radio" name="canHaveCoursesChild" id="canHaveCoursesChild_1"
            <?php    if(isset($EditCanHaveCoursesChild))
                        echo (!strcmp($EditCanHaveCoursesChild,"TRUE")?"checked":"");
                    else
                        echo "checked";
            ?>
         value="1"> <label for="canHaveCoursesChild_1"><?php echo $langYes; ?></label>

        <input type="radio" name="canHaveCoursesChild" id="canHaveCoursesChild_0"
            <?php    if(isset($EditCanHaveCoursesChild))
                        echo (!strcmp($EditCanHaveCoursesChild,"FALSE")?"checked":"");
            ?>
        value="0"> <label for="canHaveCoursesChild_0"><?php echo $langNo; ?></label>

        </td>
    </tr>

<?php
}


if($CREATE)
{
    /**
     * Display the selectBox of categories
     */
?>
    <tr>
        <td>
        <label for="fatherCat"> <?php echo $lang_faculty_Father; ?> </label >
        </td>

        <td>
        <select name="fatherCat" id="fatherCat">
        <option value="NULL" > &nbsp;&nbsp;&nbsp;<?php echo $siteName;?> </option>
        <?php
        // Display each category in the select
        buildSelectFaculty($categories,NULL,$EditFather,"");
        ?>
        </select>
        </td>
    </tr>
        <tr>
        <td><br>
        </td>
    </tr>
    <tr>
        <td>
        </td>

        <td>
        <input type="submit" value="Ok">
        </td>
    </tr>
    </table>
    </form>
<?php
}
elseif($EDIT)
{

    /**
     * Display information to edit a category and the bom of categories
     */

    claro_disp_tool_title(array('mainTitle'=>$nameTools,'subTitle'=>$langSubTitleEdit));
    claro_disp_msg_arr($controlMsg);
?>
    <form action="<?php echo $_SERVER['PHP_SELF']?>" method="POST">
    <input type="hidden" name="cmd" value="exChange" />
    <table border="0">
    <tr>
        <td >
        <label for="nameCat"> <?php echo $lang_faculty_NameCat; ?> </label >
        </td>

        <td>
        <input type="texte" name="nameCat" id="nameCat" value="<?php echo $EditName; ?>" size="20" maxlength="100">
        </td>
    </tr>
    <tr>
        <td >
        <label for="codeCat"> <?php echo $lang_faculty_CodeCat; ?> </label >
        </td>

        <td>
        <input type="texte" name="codeCat" id="codeCat" value="<?php echo $EditCode; ?>" size="20" maxlength="40">
        </td>
    </tr>
    <tr>
        <td>
        <label for="canHaveCoursesChild"> <?php echo $lang_faculty_CanHaveCatCourse; ?> </label>
        </td>

        <td>
        <input type="radio" name="canHaveCoursesChild" id="canHaveCoursesChild_1"
            <?php    if(isset($EditCanHaveCoursesChild))
                        echo (!strcmp($EditCanHaveCoursesChild,"TRUE")?"checked":"");
                    else
                        echo "checked";
            ?>
         value="1"> <label for="canHaveCoursesChild_1"><?php echo $langYes; ?></label>

        <input type="radio" name="canHaveCoursesChild" id="canHaveCoursesChild_0"
            <?php    if(isset($EditCanHaveCoursesChild))
                        echo (!strcmp($EditCanHaveCoursesChild,"FALSE")?"checked":"");
            ?>
        value="0"> <label for="canHaveCoursesChild_0"><?php echo $langNo; ?></label>

        </td>
    </tr>

    <tr>
        <td><br>
        </td>
    </tr>
        <input type="hidden" name="id" value="<?php echo $EditId ?>">
    <tr>
        <td>
        </td>

        <td>
        <input type="submit" value="Ok">
        </td>
    </tr>
    </table>
    </form>
    <br>

<?php
}
elseif($MOVE)
{
    /**
     * Display information to change root of the category
     */

    claro_disp_tool_title(array('mainTitle'=>$nameTools,'subTitle'=>$langSubTitleChangeParent.$EditCode));
    claro_disp_msg_arr($controlMsg);
?>
    <form action=" <?php echo $_SERVER['PHP_SELF'] ?> " method="POST">
    <input type="hidden" name="cmd" value="exChange" />
    <table border="0">
    <tr>
        <td>
        <label for="fatherCat"> <?php echo $lang_faculty_Father; ?> </label >
        </td>

        <td align="RIGHT">
			<select name="fatherCat">
				<option value="NULL" > &nbsp;&nbsp;&nbsp;<?php echo $siteName;?> </option>
        <?php
        //Display each category in the select
        buildSelectFaculty($categories,NULL,$EditFather,"");
        ?>
			</select>
        </td>
    </tr>
    <tr>
        <td>
			<br>
        </td>
    </tr>
    <tr>
        <td>
        </td>
        <td>
            <input type="hidden" name="id" value="<?php echo $EditId ?>">
			<input type="submit" value="Ok">
        </td>
    </tr>
    </table>
    </form>
    <br>

<?php
}
else
{
    claro_disp_tool_title(array( 'mainTitle'=>$nameTools,'subTitle'=>$langManageCourseCategories));
    claro_disp_msg_arr($controlMsg);
}

/**
 * Display the bom of categories and the button to create a new category
 */

echo "<p><a class=\"claroCmd\" href=\"" . $_SERVER['PHP_SELF'] . "?cmd=rqCreate\">" . $langSubTitleCreate . "</a></p>";    

?>

	<table class="claroTable emphaseLine" width="100%" border="0" cellspacing="2">
    <thead>
       <tr class="headerX" align="center" valign="top">

<?php

// Add titles for the table

echo "<th>".$lang_faculty_CodeCat."</td>"
     ."<th>".$langEdit."</th>"
     ."<th>".$langMove."</th>"
     ."<th>".$langDelete."</th>"
     ."<th colspan=2>".$langOrder."</th>";

echo "</tr></thead>";

displayBom($categories,NULL,"");

echo "</table>";

include($includePath."/claro_init_footer.inc.php");



/***************************
*  functions
*****************************/


    /**
     *This function return the treePos maximum of the table faculty
     *
     * @author - Benoît Muret <>
     *
     * @return  - int
     *
     *@desc - return the treePos maximum of the table faculty
     */

    function SearchMaxTreePos()
    {
        GLOBAL $tbl_course_node;

        $sql_MaxTreePos=" SELECT max(treePos) maximum 
                          FROM `" . $tbl_course_node . "`";
        $array=claro_sql_query_fetch_all($sql_MaxTreePos);

        return $array[0]["maximum"];
    }


    /**
     *This function display the bom whith option to edit or delete the categories
     *
     * @author - < Benoît Muret >
     * @param   - elem             array     : the array of each category
     * @param   - father        string     : the father of the category

     * @return  - void
     *
     * @desc - display the bom whith option to edit or delete the categories
     */

    function displayBom($elem,$father,$space)
    {
        GLOBAL $lang_faculty_ConfirmDelete, $imgRepositoryWeb;

        if($elem)
        {
            $space.="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
            $num=0;
            echo "<tbody>";
            foreach($elem as $one_faculty)
            {

                if(!strcmp($one_faculty["code_P"],$father))
                {
                $num++;
                ?>
                    <tr>
                    <td>

                    <!-- display + or - to show or hide categories -->
                    <?php
                    $date=date("mjHis");

                    echo $space;

                    if($one_faculty["nb_childs"]>0)
                    {
                        if($one_faculty["visible"])
                            $PM='<img src="'.$imgRepositoryWeb.'minus.gif" border="0" alt="" >';
                        else
                            $PM='<img src="'.$imgRepositoryWeb.'plus.gif" border="0" alt="" >';
                    ?>

                    <a href="<?php echo $_SERVER['PHP_SELF']."?id=".$one_faculty["id"]."&amp;date=".$date."#pm".$one_faculty["id"] ?>"
                    name="<?php echo "pm".$one_faculty["id"]; ?>">  <?php echo $PM ?></a> &nbsp;
                    <?php
                    }
                    else
                        echo "&nbsp;° &nbsp;&nbsp;&nbsp;";

                    echo $one_faculty["name"] . " (" . $one_faculty["code"] . ")" ."&nbsp;&nbsp;&nbsp;";

                    //Number of faculty in this parent
                    $nb=0;
                    foreach($elem as $one_elem)
                    {
                        if(!strcmp($one_elem["code_P"],$one_faculty["code_P"]))
                            $nb++;
                    }


                    //Display the picture to edit and delete a category
                    
                    ?>
                    </td>
                    <td  align="center">

                        <a href="<?php echo $_SERVER['PHP_SELF']."?id=".$one_faculty["id"]."&amp;cmd=rqEdit"; ?>" >
                        <img src="<?php echo $imgRepositoryWeb ?>edit.gif" border="0" alt="<?php echo $langEdit ?>" > </a>
                    </td>
                    <td align="center">
                        <a href="<?php echo $_SERVER['PHP_SELF']."?id=".$one_faculty["id"]."&amp;cmd=rqMove"; ?>" >
                        <img src="<?php echo $imgRepositoryWeb ?>move.gif" border="0" alt="<?php echo $langMove ?>" > </a>
                    </td>
                    <td align="center">
                        <a href="<?php echo $_SERVER['PHP_SELF']."?id=".$one_faculty["id"]."&amp;cmd=exDelete"; ?>"
                        onclick="javascript:if(!confirm('<?php echo 
                         clean_str_for_javascript($lang_faculty_ConfirmDelete.$one_faculty["code"]." ?") ?>')) return false;" >
                        <img src="<?php echo $imgRepositoryWeb ?>delete.gif" border="0" alt="<?php echo $langDelete ?>"> </a>
                    </td>
                    <?php

                    //Search nbChild of the father
                    $nbChild=0;
                    $father=$one_faculty["code_P"];

                    foreach($elem as $fac)
                        if($fac["code_P"]==$father)
                            $nbChild++;

                    //If the number of child is >0, display the arrow up and down
                    if($nb>1)
                    {
                        ?>
                        <td align="center">
                        <?php
                        //If isn't the first child, you can up
                        if($num>1)
                        {
                        ?>
                            <a href="<?php echo $_SERVER['PHP_SELF']."?id=".$one_faculty["id"]."&amp;cmd=exUp&amp;date=".$date."#ud".$one_faculty["id"];
                            ?>" name ="<?php echo "ud".$one_faculty["id"]; ?>">
                            <img src="<?php echo $imgRepositoryWeb ?>up.gif" border="0" alt="<?php echo $langUp ?>"></a>
                        <?php
                        }
                        ?>
                         </td><td align="center">
                        <?php

                        //If isn't the last child, you can down
                        if($num<$nbChild)
                        {
                        ?>
                            <a href="<?php echo $_SERVER['PHP_SELF']."?id=".$one_faculty["id"]."&amp;cmd=exDown&amp;date=".$date."#ud".$one_faculty["id"];
                            ?>" name="<?php echo "ud".$one_faculty["id"]; ?>">
                            <img src="<?php echo $imgRepositoryWeb ?>down.gif" border="0" alt="<?php echo $lang_faculty_imgDown ?>" > </a>
                    <?php
                        }
                        ?>
                        </td>
                        </td>

                        <?php
                    }
?>
                    </tr>
<?php

                    //display the bom of this category
                    if($one_faculty["visible"])
                        displayBom($elem,$one_faculty["code"],$space);
                }
            }
            echo "</tbody>";
        }
    }

    /**
     *This function display the bom of category
     *
     * @author     - < Benoît Muret >
     * @param   - elem             array     : the categories
     * @param   - father        string     : the father of a category
     * @param   - facultyEdit    key     : the category edit

     * @return  - void
     *
     * @desc : display the bom of category and display in red the category edit and his childeren in blue
     */

    function displaySimpleBom($elem,$father,$facultyEdit)
    {
        if($elem)
        {
            foreach($elem as $one_faculty)
            {
                if(!strcmp($one_faculty["code_P"],$father))
                {
                ?>
                    <ul><li>
                    <?php
                    echo (!strcmp($one_faculty["code"],$facultyEdit)?"<font color=\"red\">":"");
                    echo $one_faculty["code"];
                    echo (!strcmp($one_faculty["code"],$facultyEdit)?"</font>":"");

                    echo (!strcmp($one_faculty["code"],$facultyEdit)?"<font color=\"blue\">":"");
                    displaySimpleBom($elem,$one_faculty["code"],$facultyEdit);
                    echo (!strcmp($one_faculty["code"],$facultyEdit)?"</font>":"");
                ?>
                    </li></ul>
                <?php

                }
            }
        }
    }

    /**
     *This function delete a number of child of all father from a category
     *
     * @author  - < Benoît Muret >
     * @param   - fatherChangeChild        string     : the father
     * @param   - newNbChild            int        : the number of child deleting

     * @return  - void
     *
     * @desc : delete a number of child of all father from a category
     */

    function deleteNbChildFather($fatherChangeChild,$newNbChild)
    {
        GLOBAL $tbl_course_node;
        while(!is_null($fatherChangeChild))
        {
            $sql_DeleteNbChildFather= " UPDATE `". $tbl_course_node . "` 
                                        SET nb_childs=nb_childs-".$newNbChild." 
                                        WHERE code='".$fatherChangeChild."'";

            claro_sql_query($sql_DeleteNbChildFather);

            $sql_SelectCodeP= " SELECT code_P 
                                FROM `" . $tbl_course_node . "` 
                                WHERE code='".$fatherChangeChild."'";
            $array=claro_sql_query_fetch_all($sql_SelectCodeP);

            $fatherChangeChild=$array[0]["code_P"];
        }
    }


    /**
     *This function add a number of child of all father from a category
     *
     * @author  - < Benoît Muret >
     * @param   - fatherChangeChild        string     : the father
     * @param   - newNbChild            int        : the number of child adding

     * @return  - void
     *
     * @desc : add a number of child of all father from a category
     */

    function addNbChildFather($fatherChangeChild,$newNbChild)
    {
        GLOBAL $tbl_course_node;
        while(!is_null($fatherChangeChild))
        {
            $sql_DeleteNbChildFather= " UPDATE `" . $tbl_course_node . "` 
                                        SET nb_childs=nb_childs+".$newNbChild." 
                                        WHERE code='".$fatherChangeChild."'";

            claro_sql_query($sql_DeleteNbChildFather);

            $sql_SelectCodeP= " SELECT code_P 
                                FROM `" . $tbl_course_node . "`
                                WHERE code='".$fatherChangeChild."'";
            $array=claro_sql_query_fetch_all($sql_SelectCodeP);

            $fatherChangeChild=$array[0]["code_P"];
        }
    }

    /**
     *This function create de select box categories
     *
     * @author  - < Benoît Muret >
     * @param   - elem            array     :     the categories
     * @param   - father        string    :    the father of the category
     * @param    - $EditFather    string    :    the category editing
     * @param    - $space        string    :    space to the bom of the category

     * @return  - void
     *
     * @desc : create de select box categories
     */

    function buildSelectFaculty($elem,$father,$EditFather,$space)
    {
        if($elem)
        {
            $space.="&nbsp;&nbsp;&nbsp;";
            foreach($elem as $one_faculty)
            {
                if(!strcmp($one_faculty["code_P"],$father))
                {
                    echo "<option value=\"".$one_faculty['code']."\" ".
                            ($one_faculty['code']==$EditFather?"selected ":"")
                    ."> ".$space.$one_faculty['code']." </option>";

                    buildSelectFaculty($elem,$one_faculty["code"],$EditFather,$space);
                }
            }
        }

    }


?>
