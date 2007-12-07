<?php // $Id$
/**
 * This lib provide function to manage and use faculties.
 *
 * @version CLAROLINE 1.6
 * @copyright 2001, 2005 Universite catholique de Louvain (UCL)
 * @package faculty
 *
 * This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
 * as published by the FREE SOFTWARE FOUNDATION. The GPL is available
 * through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
 * @author Muret Benoît <muret_ben@hotmail.com>
 *
 */

/**
 * This function return the treePos maximum of the table faculty
 *
 * @author - Benoît Muret <>
 * @return  - int

 */

function SearchMaxTreePos()
{
	GLOBAL $tbl_faculty;

	$sql_MaxTreePos="select max(treePos) maximum from `$tbl_faculty`";
	$array=claro_sql_query_fetch_all($sql_MaxTreePos);

	return $array[0]["maximum"];
}


/**
 * This function display the bom with option to edit or delete the categories
 *
 * @param   elem 			array 	: the array of each category
 * @param   father		string 	: the father of the category

 * @return  void

 */

function displayBom($elem,$father,$space)
{
	GLOBAL $lang_faculty_ConfirmDelete, $imgRepositoryWeb, $langDelete;

	if($elem)
	{
		$space.="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		$num=0;
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
						$PM="-";
					else
						$PM="+";
				?>

					<a href="<?php echo $_SERVER['PHP_SELF']."?id=".$one_faculty["id"]."&date='".$date."'#pm".$one_faculty["id"] ?>"
					name="<?php echo "pm".$one_faculty["id"]; ?>">  <?php echo $PM ?></a> &nbsp;
				<?php
				}
				else
					echo "&nbsp;° &nbsp;&nbsp;&nbsp;";

				echo $one_faculty["code"]."&nbsp;&nbsp;&nbsp;";

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
				<td>

					<a href="<?php echo $_SERVER['PHP_SELF']."?id=".$one_faculty["id"]."&edit=1"; ?>" >
					<img src="<?php echo $imgRepositoryWeb ?>edit.gif" border="0" alt="<?php echo $langEdit ?>" > </a>
				</td>
				<td>
					<a href="<?php echo $_SERVER['PHP_SELF']."?id=".$one_faculty["id"]."&edit=1&move=1"; ?>" >
					<img src="<?php echo $imgRepositoryWeb ?>move.gif" border="0" alt="<?php echo $langMove ?>" > </a>
				</td>
				<td>
					<a href="<?php echo $_SERVER['PHP_SELF']."?id=".$one_faculty["id"]."&delete=1"; ?>"
					onclick="javascript:if(!confirm('<?php echo
					 addslashes(htmlentities($lang_faculty_ConfirmDelete.$one_faculty["code"])) ?>')) return false;" >
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
					<td>
					<?php
					//If isn't the first child, you can up
					if($num>1)
					{
					?>
						<a href="<?php echo $_SERVER['PHP_SELF']."?id=".$one_faculty["id"]."&UpDown=u&date='".$date."'#ud".$one_faculty["id"];
						?>" name ="<?php echo "ud".$one_faculty["id"]; ?>">
						<img src="<?php echo $imgRepositoryWeb ?>up.gif" border="0" alt="<?php echo $lang_faculty_imgUp ?>"> </a>
					<?php
					}

					?>
					</td>
					<td>
					<?php

					//If isn't the last child, you can down
					if($num<$nbChild)
					{
					?>
						<a href="<?php echo $_SERVER['PHP_SELF']."?id=".$one_faculty["id"]."&UpDown=d&date='".$date."'#ud".$one_faculty["id"];
						?>" name="<?php echo "ud".$one_faculty["id"]; ?>">
						<img src="<?php echo $imgRepositoryWeb ?>down.gif" border="0" alt="<?php echo $lang_faculty_imgDown ?>" > </a>
					<?php
					}
					?>
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
	}
}



/**
 *This function display the bom of category
 *
 * @param  elem 		array  : the categories
 * @param  father		string : the father of a category
 * @param  facultyEdit	key    : the category edit
 * @return  - void
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
 * @param   - fatherChangeChild		string 	: the father
 * @param   - newNbChild			int		: the number of child deleting

 * @return  - void
 *
 * @desc : delete a number of child of all father from a category
 */

function deleteNbChildFather($fatherChangeChild,$newNbChild)
{
	GLOBAL $tbl_faculty;
	while(!is_null($fatherChangeChild))
	{
		$sql_DeleteNbChildFather=
			"update `$tbl_faculty` set nb_childs=nb_childs-".$newNbChild." where code='".$fatherChangeChild."'";

		claro_sql_query($sql_DeleteNbChildFather);

		$sql_SelectCodeP="select code_P from `$tbl_faculty` where code='".$fatherChangeChild."'";
		$array=claro_sql_query_fetch_all($sql_SelectCodeP);

		$fatherChangeChild=$array[0]["code_P"];
	}
}


/**
 *This function add a number of child of all father from a category
 *
 * @param   - fatherChangeChild		string 	: the father
 * @param   - newNbChild			int		: the number of child adding

 *
 * @return  - void
 */

function addNbChildFather($fatherChangeChild,$newNbChild)
{
	GLOBAL $tbl_faculty;
	while(!is_null($fatherChangeChild))
	{
		$sql_DeleteNbChildFather=
			"update `$tbl_faculty` set nb_childs=nb_childs+".$newNbChild." where code='".$fatherChangeChild."'";

		claro_sql_query($sql_DeleteNbChildFather);

		$sql_SelectCodeP="select code_P from `$tbl_faculty` where code='".$fatherChangeChild."'";
		$array=claro_sql_query_fetch_all($sql_SelectCodeP);

		$fatherChangeChild=$array[0]["code_P"];
	}
}


/**
 *This function create de select box facolties
 *
 * @param  $elem		array 	: 	the faculties
 * @param  $father		string	:	the father of the faculty
 * @param  $EditFather	string	:	the faculty editing
 * @param  $space		string	:	space to the bom of the faculty
 * @return  - void
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
