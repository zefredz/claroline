<?php // $Id$
//----------------------------------------------------------------------
// CLAROLINE 1.6.0
//----------------------------------------------------------------------
// Copyright (c) 2001-2004 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: Muret Benoît
//----------------------------------------------------------------------
$langFile = "faculty";
$cidReset=TRUE;$gidReset=TRUE;$tidReset=TRUE;
require '../../inc/claro_init_global.inc.php';
$is_allowedToAdmin 	= $is_platformAdmin;

$nameTools 			= $lang_faculty_faculty;
$interbredcrump[]	= array ("url"=>$rootAdminWeb, "name"=> $langAdministrationTools);
$interbredcrump[]	= array ("url"=>"../managing/index.php", "name"=>$langManage);

include($includePath."/lib/text.lib.php");
include($includePath."/lib/debug.lib.inc.php");
include("../../inc/lib/faculty.lib.inc.php");


$dateNow 			= claro_format_locale_date($dateTimeFormatLong);

//TABLES
$tbl_faculty 		= $mainDbName."`.`faculte";
$tbl_courses		= $mainDbName."`.`cours";


//DISPLAY
$INFOFAC=TRUE;
$CREATE=TRUE;
$BOM=TRUE;
$EDIT=FALSE;
$MOVE=FALSE;


// WORKS

if(!$is_allowedToAdmin)
{
	$INFOFAC=FALSE;
	$CREATE=FALSE;
	$BOM=FALSE;
	$controlMsg["error"][]=$lang_faculty_NoAdmin;
}
else
{
	/*-----------------------------------------------------------------------------------
	Show or hide categories
	-----------------------------------------------------------------------------------*/

	if(isset($_REQUEST["id"]) && !isset($_REQUEST["UpDown"]) && !isset($_REQUEST["delete"]) && !isset($_REQUEST["edit"]) &&
		!isset($_REQUEST["change"]))
	{
		$id=$_REQUEST["id"];
		$faculty=$_SESSION["savFaculty"];

		//Change the parameter 'visible'
		if(!is_null($faculty))
		{
			foreach($faculty as $key=>$one_faculty)
			{
				if($one_faculty["id"]==$id)
				{
					if($faculty[$key]["visible"])
						$faculty[$key]["visible"]=FALSE;
					else
						$faculty[$key]["visible"]=TRUE;
				}
			}
		}
		// SAVE IN THE SESSION
		$savFaculty=$faculty;
		session_register("savFaculty");
	}
	else
	{
		// SAVE IN THE SESSION
		$faculty=$_SESSION["savFaculty"];



		/*-----------------------------------------------------------------------------------
		Create a category
		-----------------------------------------------------------------------------------*/
		if(isset($_REQUEST["create"]))
		{
			//if the new category have a name, a code and she can have child (categories or courses)
			if(!empty($_REQUEST["nameCat"]) && !empty($_REQUEST["codeCat"]))
			{
				$nameCat=$_REQUEST["nameCat"];
				$codeCat=$_REQUEST["codeCat"];
				$fatherCat=$_REQUEST["fatherCat"];
				$canHaveCoursesChild=($_REQUEST["canHaveCoursesChild"]==1?"TRUE":"FALSE");

				//If the category don't have as parent NULL (root), all parent of this category have a child more
				$fatherChangeChild=(!strcmp($fatherCat,"NULL")?NULL:$fatherCat);

				addNbChildFather($fatherChangeChild,1);

				//If the parent of the new category isn't root
				if(strcmp($fatherCat,"NULL"))
				{
					$sql_SearchFather="select treePos,nb_childs from `$tbl_faculty` where code='".$fatherCat."'";
					$array=claro_sql_query_fetch_all($sql_SearchFather);

					//The treePos from the new category (treePos from this father + nb_childs from this father)
					$treePosCat=$array[0]["treePos"]+$array[0]["nb_childs"];

					//Add 1 to all category who have treePos >= of the treePos of the new category
					$sql_ChangeTree="update `$tbl_faculty` set treePos=treePos+1 where treePos>='".$treePosCat."'";
					claro_sql_query($sql_ChangeTree);
				}
				else	//The parent of the new category is root
				{
					//Search the maximum treePos
					$treePosCat=SearchMaxTreePos()+1;
				}

				//insert the new category to the table
				$sql_InsertCat="insert into `$tbl_faculty` (name,code,bc,nb_childs,canHaveCoursesChild,canHaveCatChild,treePos,code_P)
											values ('".$nameCat."','".$codeCat."',NULL,'0','".$canHaveCoursesChild."','TRUE','".$treePosCat."'";

				if ($fatherCat == "NULL")
				{
					$sql_InsertCat .= ",NULL)";
				}
				else
				{
					$sql_InsertCat .= ",'".$fatherCat."')";
				}

				claro_sql_query($sql_InsertCat);

				//Confirm creating
				$controlMsg['info'][]=$lang_faculty_CreateOk;
			}
			else //if the new category don't have a name or a code or she can't have child (categories or courses)
			{
				if(empty($_REQUEST["nameCat"]))
					$controlMsg["error"][]=$lang_faculty_NameEmpty;

				if(empty($_REQUEST["codeCat"]))
					$controlMsg["error"][]=$lang_faculty_CodeEmpty;
			}
		}



		/*-----------------------------------------------------------------------------------
		If you move the category in the same father of the bom
		-----------------------------------------------------------------------------------*/
		if(isset($_REQUEST["UpDown"] ))
		{
			//Search the minimum and the maximum
			$sql_InfoTree="select min(treePos) minimum, max(treePos) maximum from `$tbl_faculty`";
			$array=claro_sql_query_fetch_all($sql_InfoTree);

			$TreeMin=$array[0]["minimum"];
			$TreeMax=$array[0]["maximum"];

			//Search the category who move in the bom
			$i=0;
			while($i<count($faculty) && $faculty[$i]["id"]!=$_REQUEST["id"])
				$i++;

			/*-----------------------------------------------------------------------------------
			If Up the category and the treePos of this category isn't the first category
			-----------------------------------------------------------------------------------*/
			if($_REQUEST["UpDown"]=="u" && $i>=$TreeMin )
			{
				//Search the previous brother of this category
				$j=$i-1;
				while($j>0 && strcmp($faculty[$j]["code_P"],$faculty[$i]["code_P"]))
					$j--;

				//If they are a brother
				if(!strcmp($faculty[$j]["code_P"],$faculty[$i]["code_P"]) )
				{
					//change the brother and his children
					for($k=0;$k<=$faculty[$j]["nb_childs"];$k++)
					{
						$searchId=$faculty[$j+$k]["id"];
						$newTree=$faculty[$j]["treePos"]+$faculty[$i]["nb_childs"]+1+$k;

						$sql_Update = "update `$tbl_faculty` set treePos='".$newTree."' where id='".$searchId."'";
						claro_sql_query($sql_Update) ;
					}

					//change the choose category and his childeren
					for($k=0;$k<=$faculty[$i]["nb_childs"];$k++)
					{
						$searchId=$faculty[$i+$k]["id"];
						$newTree=$faculty[$i]["treePos"]-$faculty[$j]["nb_childs"]-1+$k;

						$sql_Update = "update `$tbl_faculty` set treePos='".$newTree."' where id='".$searchId."'";
						claro_sql_query($sql_Update) ;
					}

					//Confirm move
					$controlMsg['info'][]=$lang_faculty_MoveOk;
				}
			}

			/*-----------------------------------------------------------------------------------
			//If Up the category and the treePos of this category isn't the last category
			-----------------------------------------------------------------------------------*/
			if($_REQUEST["UpDown"]=="d" && $i<$TreeMax-1 )
			{
				//Search the next brother
				$j=$i+1;
				while($j<=count($faculty) && strcmp($faculty[$j]["code_P"],$faculty[$i]["code_P"]))
					$j++;

				//If they are a brother
				if(!strcmp($faculty[$j]["code_P"],$faculty[$i]["code_P"]))
				{
					//change the brother and his children
					for($k=0;$k<=$faculty[$j]["nb_childs"];$k++)
					{
						$searchId=$faculty[$j+$k]["id"];
						$newTree=$faculty[$j]["treePos"]-$faculty[$i]["nb_childs"]-1+$k;

						$sql_Update = "update `$tbl_faculty` set treePos='".$newTree."' where id='".$searchId."'";
						claro_sql_query($sql_Update);
					}

					//change the choose category and his childeren
					for($k=0;$k<=$faculty[$i]["nb_childs"];$k++)
					{
						$searchId=$faculty[$i+$k]["id"];
						$newTree=$faculty[$i]["treePos"]+$faculty[$j]["nb_childs"]+1+$k;

						$sql_Update = "update `$tbl_faculty` set treePos='".$newTree."' where id='".$searchId."'";
						claro_sql_query($sql_Update) ;
					}

						//Confirm move
						$controlMsg['info'][]=$lang_faculty_MoveOk;
				}
			}

		}



		/*-----------------------------------------------------------------------------------
		If you delete a category
		-----------------------------------------------------------------------------------*/
		if(isset($_REQUEST["delete"]))
		{
			//Search information of the category
			$sql_SearchDelete="select treePos,code,code_P,nb_childs from `$tbl_faculty` where id='".$_REQUEST["id"]."'";
			$res_treePosDelete=claro_sql_query_fetch_all($sql_SearchDelete);
			$treePosDelete=$res_treePosDelete[0];

			if($res_treePosDelete==FALSE)
				$treePosDelete=NULL;

			if(!is_null($treePosDelete))
			{
				//Look if they aren't courses in this category
				$sql_SearchCourses="select count(cours_id) num from `$tbl_courses` where faculte='".$treePosDelete["code"]."'";
				$res_SearchCourses=claro_sql_query_fetch_all($sql_SearchCourses);

				if($treePosDelete[0]["nb_childs"]>0 || $res_SearchCourses[0]["num"]>0)
				{
					if($treePosDelete["nb_childs"]>0)
						$controlMsg['error'][]=$lang_faculty_CatHaveCat;

					if($res_SearchCourses[0]["num"]>0)
						$controlMsg['error'][]=$lang_faculty_CatHaveCourses;
				}
				else
				{
					//delete the category
					$sql_Delete="delete from `$tbl_faculty` where id='".$_REQUEST["id"]."'";
					claro_sql_query($sql_Delete);

					//treePos-1 for all category who have treePos>treePos of the faculty delete
					$sql_ChangeTree="update `$tbl_faculty` set treePos=treePos-1 where treePos>'".$treePosDelete["treePos"]."'";
					claro_sql_query($sql_ChangeTree);

					//nb_childs-1 for all parent of the category delete
					$fatherChangeChild=$treePosDelete["code_P"];

					deleteNbChildFather($fatherChangeChild,1);

					//Confirm deleting
					$controlMsg['info'][]=$lang_faculty_DeleteOk;
				}
			}
		}



		/*-----------------------------------------------------------------------------------
		Edit a category
		-----------------------------------------------------------------------------------*/
		if(isset($_REQUEST["edit"]))
		{
			$INFOFAC=TRUE;
			$EDIT=TRUE;
			$CREATE=FALSE;
			$BOM=FALSE;
			$MOVE=FALSE;

			$nameTools 			= $lang_faculty_EditCat;
			$interbredcrump[]	= array ("url"=>$PHP_SELF, "name"=> $lang_faculty_faculty);

			//Search information of the category edit
			$sql_SearchInfoTreeFaculty="select * from `$tbl_faculty` where id='".$_REQUEST["id"]."'";
			$array=claro_sql_query_fetch_all($sql_SearchInfoTreeFaculty);

			$EditId=$array[0]["id"];
			$EditName=$array[0]["name"];
			$EditCode=$array[0]["code"];
			$EditFather=$array[0]["code_P"];
			$EditCanHaveCatChild=$array[0]["canHaveCatChild"];
			$EditCanHaveCoursesChild=$array[0]["canHaveCoursesChild"];

			if(isset($_REQUEST["move"]))
			{
				$MOVE	=TRUE;
				$INFOFAC=FALSE;
				$EDIT	=FALSE;
				$CREATE	=FALSE;
				$BOM	=FALSE;
			}
		}



		/*-----------------------------------------------------------------------------------
		Change information of category
		-----------------------------------------------------------------------------------*/
		if(isset($_REQUEST["change"]))
		{
			//Search information
			$sql_FacultyEdit="select * from `$tbl_faculty` where id='".$_REQUEST["id"]."'";
			$arrayfacultyEdit=claro_sql_query_fetch_all($sql_FacultyEdit);
			$facultyEdit=$arrayfacultyEdit[0];

			//Edit a category (don't move the category)
			if(!isset($_REQUEST["fatherCat"]))
			{
				$canHaveCoursesChild=($_REQUEST["canHaveCoursesChild"]==1?"TRUE":"FALSE");

				//If nothing is different
				if(!strcmp($facultyEdit["name"],$_REQUEST["nameCat"]) && !strcmp($facultyEdit["code"],$_REQUEST["codeCat"])
				&& !strcmp($facultyEdit["canHaveCoursesChild"],$canHaveCoursesChild) )
				{
					$controlMsg['warning'][]=$lang_faculty_NoChange;
				}
					//If the category can't have course child, look if they haven't already
				else
				{
					if(!strcmp($canHaveCoursesChild,"FALSE"))
					{
						$sql_SearchCourses="select count(cours_id) num from `$tbl_courses` where faculte='".$facultyEdit["code"]."'";
						$array=claro_sql_query_fetch_all($sql_SearchCourses);

						if($array[0]["num"]>0)
						{
							$controlMsg['warning'][]=$lang_faculty_HaveCourses;
							$canHaveCoursesChild="TRUE";
						}
					}
					else
					{
						$sql_ChangeInfoFaculty="update `$tbl_faculty` set name='".$_REQUEST["nameCat"]."',code='".$_REQUEST["codeCat"]."'
									,canHaveCoursesChild='".$canHaveCoursesChild."' where id='".$_REQUEST["id"]."'";

						claro_sql_query($sql_ChangeInfoFaculty);

						//Change code_P for his childeren
						if(strcmp($_REQUEST["codeCat"],$facultyEdit["code"]))
						{
							$sql_ChangeCodeParent="update `$tbl_faculty` set code_P='".$_REQUEST["codeCat"]."' where
													code_P='".$facultyEdit["code"]."'";
							claro_sql_query($sql_ChangeCodeParent);
						}

						//Confirm edition
						$controlMsg['info'][]=$lang_faculty_EditOk;
					}

					//Change the code of the faculte in the table cours
					if(strcmp($facultyEdit["code"],$_REQUEST["codeCat"]))
					{
						$sql_ChangeInfoFaculty="update `$tbl_courses` set faculte='".$_REQUEST["codeCat"]."'
													where faculte='".$facultyEdit["code"]."'";

						claro_sql_query($sql_ChangeInfoFaculty);
					}
				}
			}
			elseif(!strcmp($facultyEdit["code_P"],$_REQUEST["fatherCat"]) ||
					($_REQUEST["fatherCat"]=="NULL" && $facultyEdit["code_P"]==NULL))
			{
				$controlMsg['warning'][]=$lang_faculty_NoChange;
			}
			else //Move the category //($_REQUEST["MoveChild"]==1)
			{
				//For the table
				$fatherCat=(!strcmp($_REQUEST["fatherCat"],"NULL")?"":$_REQUEST["fatherCat"]);

				//Check all children to look if the new parent of this category isn't his child
				//The first and last treePos of his child
				$treeFirst=$facultyEdit["treePos"];
				$treeLast=$facultyEdit["treePos"]+$facultyEdit["nb_childs"];

				$error=0;
				for($i=$treeFirst;$i<=$treeLast;$i++)
				{
					$sql_SearchChild="select code from `$tbl_faculty` where treePos=".$i;
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
					//The treePos afther his childeren
					$treePosLastChild=$facultyEdit["treePos"]+$facultyEdit["nb_childs"];

					//the treePos max
					$maxTree=SearchMaxTreePos();

					//the treePos of her and his childeren = max(treePos)+i
					$i=1;
					while($i<=$facultyEdit["nb_childs"]+1)
					{
						$sql_TempTree="update `$tbl_faculty` set treePos=".$maxTree."+".$i."
										where treePos=".$facultyEdit["treePos"]."+".$i."-1";

						claro_sql_query($sql_TempTree);
						$i++;
					}

					//Change treePos of the faculty they have a treePos > treePos of the last child
					$sql_ChangeTree="update `$tbl_faculty` set treePos=treePos-".$facultyEdit["nb_childs"]."-1 where
								treePos>".$treePosLastChild." and	treePos<=".$maxTree;

					claro_sql_query($sql_ChangeTree);

					//if the father isn't root
					if(strcmp($_REQUEST["fatherCat"],"NULL"))
					{
						//Search treePos of the new father
						$sql_SearchNewTreePos="select treePos from `$tbl_faculty` where code='".$_REQUEST["fatherCat"]."'";
						$res_SearchNewTreePos=claro_sql_query_fetch_all($sql_SearchNewTreePos);

						$newFather=$res_SearchNewTreePos[0];

						//Ajoute a tous les treePos apres le nouveau pere le nombre d enfant + 1 de celui qu on deplace
						$sql_ChangeTree="update `$tbl_faculty` set treePos=treePos+".$facultyEdit["nb_childs"]."+1 where
									treePos>".$newFather["treePos"]." and	treePos<=".$maxTree;

						claro_sql_query($sql_ChangeTree);

						//the new treePos is the treePos of the new father+1
						$newTree=$newFather["treePos"]+1;
					}
					else //The new treePos is the last treePos exist
						$newTree=$maxTree;

					//Change the treePos of her and his childeren
					$i=0;
					while($i<=$facultyEdit["nb_childs"])
					{
						$sql_ChangeTree="update `$tbl_faculty` set treePos=".$newTree."+".$i." where
								treePos=".$maxTree."+".$i."+1";

						claro_sql_query($sql_ChangeTree);
						$i++;
					}

					$father=(!strcmp($_REQUEST["fatherCat"],"NULL")?"NULL":("'".$_REQUEST["fatherCat"]."'"));

					//Change the category edit
					$sql_ChangeInfoFaculty="update `$tbl_faculty` set code_P=".$father." where id='".$_REQUEST["id"]."'";

					claro_sql_query($sql_ChangeInfoFaculty);

					$newNbChild=$facultyEdit["nb_childs"]+1;

					//Change the number of childeren of the father category and his parent
					$fatherChangeChild=$facultyEdit["code_P"];
					deleteNbChildFather($fatherChangeChild,$newNbChild);

					//Change the number of childeren of the new father and his parent
					$fatherChangeChild=$_REQUEST["fatherCat"];
					addNbChildFather($fatherChangeChild,$newNbChild);

					//Search nb_childs of the new father
					$sql_SearchNbChild="select nb_childs from `$tbl_faculty`where code=".$father;
					$array=claro_sql_query_fetch_all($sql_SearchNbChild);

					$nbChildFather=$array[0];

					//Si le nouveau pere avait des enfants replace celui que l on vient de deplacer comme dernier enfant
					if($nbChildFather["nb_childs"]>$facultyEdit["nb_childs"]+1)
					{
						//Met des treePos temporaire pour celui qu on vient de deplacer et ses enfants
						$i=1;
						while($i<=$facultyEdit["nb_childs"]+1)
						{
							$sql_TempTree="update `$tbl_faculty` set treePos=".$maxTree."+".$i."
											where treePos=".$newTree."+".$i."-1";

							claro_sql_query($sql_TempTree);
							$i++;
						}

						//Deplace les enfants restant du pere
						$i=1;
						while($i<=($nbChildFather["nb_childs"]-$facultyEdit["nb_childs"]-1) )
						{
							$sql_MoveTree="update `$tbl_faculty` set treePos=".$newTree."+".$i."-1
											where treePos=".$newTree."+".$facultyEdit["nb_childs"]."+".$i;
							claro_sql_query($sql_MoveTree);
							$i++;
						}

						//Remet les treePos de celui qu on a deplacé et de ses enfants
						$i=1;
						while($i<=$facultyEdit["nb_childs"]+1)
						{
							$sql_TempTree="update `$tbl_faculty` set
								treePos=".$newTree."+".$nbChildFather["nb_childs"]."-".$facultyEdit["nb_childs"]."-2+".$i."
								where treePos=".$maxTree."+".$i;

							claro_sql_query($sql_TempTree);
							$i++;
						}

						//Confirm move
						$controlMsg['info'][]=$lang_faculty_MoveOk;
					}
				}
			}



			/*-----------------------------------------------------------------------------------------
			If you move the category without his childeren
			------------------------------------------------------------------------------------------

			//If the parent is different and they move the category alone (without his childeren)
			elseif($_REQUEST["MoveChild"]==0)
			{
				//Error if the new parent is self
				if(!strcmp($facultyEdit["code"],$_REQUEST["fatherCat"]))
				{
					$controlMsg['error'][]=$lang_faculty_NoParentSelf;
				}
				else
				{
					$maxTree=SearchMaxTreePos();

					//Met a temp le treePos de lui
					$sql_TempTree="update `$tbl_faculty` set treePos=".$maxTree."+1
										where id=".$facultyEdit["id"];

					claro_sql_query($sql_TempTree);

					//Change treePos of category who have treePos>treePos of the category edit
					$sql_ChangeTree="update `$tbl_faculty` set treePos=treePos-1 where
							treePos>".$facultyEdit["treePos"]." and	treePos<=".$maxTree;

					claro_sql_query($sql_ChangeTree);

					//Change the father of his childeren (=the father of the category edit)
					if($facultyEdit["nb_childs"]>0)
					{
						$sql_ChangeFather="update `$tbl_faculty` set code_P='".$facultyEdit["code_P"]."' where
											code_P='".$facultyEdit["code"]."'";

						claro_sql_query($sql_ChangeFather);
					}

					//Search the treePos of the new father
					$sql_SearchNewTreePos="select treePos from `$tbl_faculty` where code='".$_REQUEST["fatherCat"]."'";
					$array=claro_sql_query_fetch_all($sql_SearchNewTreePos);

					$newFather=$array[0];

					//The new treePos is the treePos of his new father+nb_childs of his new father+1
					$newTree=$newFather["treePos"]+$newFather["nb_childs"]+1;

					//Ajoute a tous les treePos apres le nouveau pere et ses enfants treePos+1
					$sql_ChangeTree="update `$tbl_faculty` set treePos=treePos+1 where
							treePos>=".$newTree." and	treePos<=".$maxTree;

					claro_sql_query($sql_ChangeTree);

					$father=(!strcmp($_REQUEST["fatherCat"],"NULL")?"NULL":("'".$_REQUEST["fatherCat"]."'"));

					//Change information of the category
					$sql_ChangeInfoFaculty="update `$tbl_faculty` set
							name='".$_REQUEST["nameCat"]."',code='".$_REQUEST["codeCat"]."',code_P=".$father.
							",nb_childs=0,treePos='".$newTree."',canHaveCoursesChild='".$canHaveCoursesChild."'
							where id='".$_REQUEST["id"]."'";

					claro_sql_query($sql_ChangeInfoFaculty);

					//nb_childs of the old father (and his fathers) = nb_childs-1
					$fatherChangeChild=$facultyEdit["code_P"];
					deleteNbChildFather($fatherChangeChild,1);

					//nb_childs of the new father (and his fathers) = nb_childs+1
					$fatherChangeChild=$_REQUEST["fatherCat"];
					addNbChildFather($fatherChangeChild,1);
				}
			}*/
		}



		/*-----------------------------------------------------------------------------------
		search informations from the table
		-----------------------------------------------------------------------------------*/
		$sql_searchfaculty = "select * FROM `$tbl_faculty` order by treePos";
		$array=claro_sql_query_fetch_all($sql_searchfaculty);

		$tempFaculty=$faculty;
		unset($faculty);


		//Build the array of catégories
		if ($array)
		{
			$i=0;
			for($i=0;$i<count($array);$i++)
			{
				$array[$i]["visible"]=FALSE;
				$faculty[]=$array[$i];
			}

			//Pour remettre a visible ou non comme prédédement
			for($i=0;$i<count($faculty);$i++)
			{
				$searchId=$faculty[$i]["id"];
				$j=0;
				while($j<count($tempFaculty) && strcmp($tempFaculty[$j]["id"],$faculty[$i]["id"]))
					$j++;

				if($j<count($tempFaculty))
				{
					$faculty[$i]["visible"]=$tempFaculty[$j]["visible"];
				}
			}

			$savFaculty=$faculty;

			//SESSION
			session_unregister("savFaculty");
			session_register("savFaculty");
		}
		else
		{
			$controlMsg['warning'][]=$lang_faculty_NoCat;

			$faculty=NULL;
			$savFaculty=$faculty;
			//SESSION
			session_unregister("savFaculty");
			session_register("savFaculty");
		}
	}
}

// END OF WORKS




include($includePath."/claro_init_header.inc.php");

claro_disp_tool_title(
	array(
	'mainTitle'=>$nameTools,
	'subTitle'=> $PHP_AUTH_USER." - ".$siteName." - ".$clarolineVersion." - ".$dateNow
	)
	);
claro_disp_msg_arr($controlMsg);



/*-----------------------------------------------------------------------------------
 OUTPUT
-----------------------------------------------------------------------------------*/


/*-----------------------------------------------------------------------------------
Information edit for create or edit a category
-----------------------------------------------------------------------------------*/

if($INFOFAC)
{
?>
	<br>
	<form action="<?php echo $PHP_SELF?>" method="POST">
	<table border="0">
	<tr>
		<td width="400">
		<label for="nameCat"> <?php echo $lang_faculty_NameCat; ?> </label >
		</td>

		<td>
		<input type="texte" name="nameCat" value="<?php echo $EditName; ?>" size="20" maxlength="100">
		</td>
	</tr>
	<tr>
		<td width="200">
		<label for="codeCat"> <?php echo $lang_faculty_CodeCat; ?> </label >
		</td>

		<td>
		<input type="texte" name="codeCat" value="<?php echo $EditCode; ?>" size="20" maxlength="40">
		</td>
	</tr>
	<tr>
		<td>
		<label for="canHaveCoursesChild"> <?php echo $lang_faculty_CanHaveCatCourse; ?> </label>
		</td>

		<td>
		<input type="radio" name="canHaveCoursesChild"
			<?php	if(isset($EditCanHaveCoursesChild))
						echo (!strcmp($EditCanHaveCoursesChild,"TRUE")?"checked":"");
					else
						echo "checked";
			?>
	 	value="1"> <?php echo $lang_faculty_Yes; ?>

		<input type="radio" name="canHaveCoursesChild"
			<?php	if(isset($EditCanHaveCoursesChild))
						echo (!strcmp($EditCanHaveCoursesChild,"FALSE")?"checked":"");
			?>
		value="0"> <?php echo $lang_faculty_No; ?>

		</td>
	</tr>

<?php
}


/*-----------------------------------------------------------------------------------
Display the selectBox of faculties
-----------------------------------------------------------------------------------*/
if($CREATE)
{
?>
	<tr>
		<td>
		<label for="fatherCat"> <?php echo $lang_faculty_Father; ?> </label >
		</td>

		<td>
		<select name="fatherCat">
		<option value="NULL" > &nbsp;&nbsp;&nbsp;<?php echo $siteName;?> </option>
		<?php
		//Display each category in the select
		buildSelectFaculty($savFaculty,NULL,$EditFather,"");
		?>
		</select>
		</td>
	</tr>
<?php
}


/*-----------------------------------------------------------------------------------
Display the bom of categories and the button to create a new category
-----------------------------------------------------------------------------------*/
if($BOM)
{
?>
	<tr>
		<td><br>
		</td>
	</tr>
	<tr>
		<td>
		</td>

		<td>
		<input type="submit" name="create" value=" Créer ">
		</td>
	</tr>
	</table>
	</form>
	<hr>

	<table border="0">
<?php

	displayBom($faculty,NULL,"");

	?>	</table>
<?php
}

/*-----------------------------------------------------------------------------------
Display information to edit a category and the bom of categories
/*-----------------------------------------------------------------------------------*/
if($EDIT)
{
?>
	<tr>
		<td><br>
		</td>
	</tr>
		<input type="hidden" name="id" value="<?php echo $EditId ?>">
	<tr>
		<td>
		</td>

		<td>
		<input type="reset" name="reset" value="Réinitialiser">
		<input type="submit" name="change" value=" Remplacer ">
		</td>
	</tr>
	</table>
	</form>
	<br><hr><br>

<?php

	displaySimpleBom($faculty,NULL,$EditCode);

}

/*-----------------------------------------------------------------------------------
Display information to change root of the category
/*-----------------------------------------------------------------------------------*/
if($MOVE)
{
?>
	<br>
	<form action="<?php echo $PHP_SELF?>" method="POST">
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
		buildSelectFaculty($savFaculty,NULL,$EditFather,"");
		?>
		</select>
		</td>
	</tr>
			<input type="hidden" name="id" value="<?php echo $EditId ?>">
	<tr>
		<td><br>
		</td>
	</tr>
	<tr>
		<td>
		</td>

		<td>
		<input type="reset" name="reset" value="Réinitialiser">
		<input type="submit" name="change" value=" Remplacer ">
		</td>
	</tr>
	</table>
	</form>
	<br><hr><br>

	<?php
	displaySimpleBom($faculty,NULL,$EditCode);

}

include($includePath."/claro_init_footer.inc.php");

?>
