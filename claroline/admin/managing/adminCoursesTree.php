<?php # $Id$
//----------------------------------------------------------------------
// CLAROLINE
//----------------------------------------------------------------------
// Copyright (c) 2001-2003 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see 'credits' file
//----------------------------------------------------------------------
/*
	  +----------------------------------------------------------------------+
      | This File                                                            |
      |          This file prupose to user to add, or delete some courses    |
      |          Christophe Gesché <gesche@ipm.ucl.ac.be>                    |
      +----------------------------------------------------------------------+

 HOW  WORK THE TREE.

 2 tables

TABLE node
  id 		int(11) 		NOT NULL 	default '0',	UNIQUE
  code 		varchar(40)	 	NOT NULL 	default '',		UNIQUE
  code_P 	varchar(20) 				default NULL,
  name 		varchar(100)	NOT NULL 	default '',
  treePos 	int(11) 		NOT NULL 	default '0',	UNIQUE


TABLE cours
  cours_id int(11) NOT NULL auto_increment,		UNIQUE
  code varchar(40) default NULL,				UNIQUE
  faculte varchar(12) default NULL,
  ...

relations
	node.code_P  	0,1->0,n node.code
	cours.cours_id  1,1->0,n node.code

	treePos = id in progressive tree.

 */


DEFINE("CONFVal_DEFAULT_DEEP",4);
DEFINE("DEFAULT_ROOT_CAN_HAVE_COURSE_CHILD",false); // true  is  strange
DEFINE("DEFAULT_ROOT_CAN_HAVE_CAT_CHILD",true); // false is very very strange



$langFile = "coursestree";
include("../../inc/claro_init_global.inc.php");
include($includePath."/lib/debug.lib.inc.php");





$interbredcrump[]= array ("url"=>$rootAdminWeb, "name"=> $langAdministrationTools);
$interbredcrump[]= array ("url"=>$rootAdminWeb."managing/", "name"=> $langManage);
$nameTools=$lang_course_tree_managment;

//$noPHP_SELF=true;
$htmlHeadXtra[] =
"<style type=\"text/css\">
		TABLE.list TR TD.add { background-color: ".$color2."; }
		TABLE.list TR TD.rem { background-color: ".$color1."; }
		TABLE.list TR TH {	border: 1px solid grey;		}
		fieldset label
		{
			display: block;
			width: 25%;
			float: left;
		}
		.editNode input, .editNode textarea { margin-left: 1em;	width: 20em;}
		fieldset { border-style: none;}
		fieldset, form {  margin: 0;	border: 0px;	padding: 0;  }
	.level1 	{ text-align:center; padding-bottom: 2px; padding-left: 5px; padding-right: 5px; padding-top: 1px;
	border-width: 1px; border: thin dashed Gray;
	background-color: #FFFFFF;	}
	.level2 	{ text-align:center; padding-bottom: 2px; padding-left: 5px; padding-right: 5px; padding-top: 1px;
	border-width: 1px; border: thin dashed Gray;
	background-color: #FFFFCC;	}
	.level3 	{  text-align:center; padding-bottom: 2px; padding-left: 5px; padding-right: 5px; padding-top: 1px;
	border-width: 1px; border: thin dashed Gray;
	background-color: #FFCCCC;	}
	.level4 	{ text-align:center;  padding-bottom: 2px; padding-left: 5px; padding-right: 5px; padding-top: 1px;
	border-width: 1px; border: thin dashed Gray;
	background-color: #CCCCCC;	}
	.level5 	{  text-align:center; padding-bottom: 2px; padding-left: 5px; padding-right: 5px; padding-top: 1px;
	border-width: 1px; border: thin dashed Gray;
	background-color: #CCCCAA;	}
	.level6 	{ text-text-align:center;  padding-bottom: 2px; padding-left: 5px; padding-right: 5px; padding-top: 1px;
	border-width: 1px; border: thin dashed Gray;
	background-color: #CCAAAA;	}
	.level7 	{  text-align:center; padding-bottom: 2px; padding-left: 5px; padding-right: 5px; padding-top: 1px;
	border-width: 1px; border: thin dashed Gray;
	background-color: #AAAAAA;	}
	.level8 	{  text-align:center; padding-bottom: 2px; padding-left: 5px; padding-right: 5px; padding-top: 1px;
	border-width: 1px; border: thin dashed Gray;
	background-color: #AAAA99;	}
	.childNode {float : left; }
-->
</STYLE>";

$now = gmdate('D, d M Y H:i:s') . ' GMT';
header('Expires: 0'); // rfc2616 - Section 14.21
header('Last-Modified: ' . $now);
header('Cache-Control: no-store, no-cache, must-revalidate'); // HTTP/1.1
header('Cache-Control: pre-check=0, post-check=0, max-age=0'); // HTTP/1.1
header('Pragma: no-cache'); // HTTP/1.0


$is_allowedToEdit = $is_platformAdmin || $PHP_AUTH_USER;


$tbl_courses		= $mainDbName."`.`cours";
$tbl_user 			= $mainDbName."`.`user";
$tbl_cours_users	= $mainDbName."`.`cours_user";
$tbl_users_groups  	= $_course['dbNameGlu']."user_group";
$tbl_groups 		= $_course['dbNameGlu']."student_group";
$tbl_cat			= $mainDbName."`.`faculte";


//Default view
$displayGoOut = true;
if (!$is_allowedToEdit)
{
	$displayGoOut = true;
}
else
{
	if ($nodeToEdit)
	{
		$nodeToEdit = getNodeByCode($nodeToEdit);
		$nodeToEdit["canHaveCatChild"] = ($nodeToEdit["canHaveCatChild"]=='TRUE')?" checked=\"checked\" ":" ";
		$nodeToEdit["canHaveCoursesChild"] = ($nodeToEdit["canHaveCoursesChild"]=='TRUE')?" checked=\"checked\" ":" ";

		$displayEditNode = true;
		$interbredcrump[]= array ("url"=>$rootAdminWeb."managing/adminCoursesTree.php", "name"=> $lang_course_tree_managment);
		$nameTools=$langEditNode;
		$noPHP_SELF=true;
	}
	elseif ($HTTP_GET_VARS["nodeToSwap"])
	{
		$nodeToSwap = $HTTP_GET_VARS["nodeToSwap"];
		swapNodes($nodeToSwap[1],$nodeToSwap[0]);
		$displayResultSwitchNodes = true;

	}
	elseif ($HTTP_POST_VARS["nodeIdToUpdate"])
	{
		updateNode($HTTP_POST_VARS["nodeIdToUpdate"],$HTTP_POST_VARS["nodeCode"],$HTTP_POST_VARS["nodeName"],$HTTP_POST_VARS["canHaveCatChild"],$HTTP_POST_VARS["canHaveCoursesChild"] );
		$displayResultUpdateNode = true;
	}
	elseif ($HTTP_GET_VARS["addChildNodeTo"])
	{
		if ($HTTP_GET_VARS["addChildNodeTo"]=="NULL")
		{
			$nodeParent["code"]=NULL;
			$nodeParent["canHaveCatChild"] = DEFAULT_ROOT_CAN_HAVE_CAT_CHILD;
		}
		else
		{
			$nodeParent = getNodeByCode($HTTP_GET_VARS["addChildNodeTo"]);
		}

		if ($nodeParent["canHaveCatChild"]=="TRUE")
		{
			$displayFormToAddNode = true;
		}
		else
		{
			$displayFormToAddNode = false;

		}
		$interbredcrump[]= array ("url"=>$rootAdminWeb."managing/adminCoursesTree.php", "name"=> $lang_course_tree_managment);
		$nameTools=$langAddChildNode;
		$noPHP_SELF=true;

	}
	elseif ($HTTP_POST_VARS["addNode"])
	{
		addNode($HTTP_POST_VARS["nodeCode"],$HTTP_POST_VARS["nodeName"],$HTTP_POST_VARS["nodeCode_P"],$HTTP_POST_VARS["canHaveCatChild"],$HTTP_POST_VARS["canHaveCoursesChild"]);
		$displayResultAddNode = true;
	}
	elseif ($HTTP_POST_VARS["rebuiltTreePos"])
	{
		$time_begin = getmicrotime();
		$sqlResetAllTreePos ="UPDATE `".$tbl_cat."`
		SET treePos = '0'";
		mysql_query_dbg($sqlResetAllTreePos);
		$treePos = 0;
		resfreshTreePosNode("NULL",$treePos);
		$time_end = getmicrotime();

		$displayResultrebuiltTreePos = true;

	}
	elseif ($HTTP_POST_VARS["refreshAllNbChildInBase"])
	{
		$time_begin = getmicrotime();
		refreshAllNbChildInBase();
		$time_end = getmicrotime();
		$displayResultrefreshAllNbChildInBase = true;

	}
	elseif ($HTTP_GET_VARS["showTree"])
	{

		if ($HTTP_GET_VARS["deep"])
		{
			$deep = $HTTP_GET_VARS["deep"];
		}
		else
		{
			$deep = CONFVal_DEFAULT_DEEP;
		}
		if ($HTTP_GET_VARS["showTree"]!="NULL")
		{
			$rootNodeOfView = getNodeByCode($HTTP_GET_VARS["showTree"]);
		}
		$displayShowTree = true;
		$interbredcrump[]= array ("url"=>$rootAdminWeb."managing/adminCoursesTree.php", "name"=> $lang_course_tree_managment);
		$nameTools=$langShowTree;
		$noPHP_SELF=true;


	}
	elseif ($HTTP_GET_VARS["nbChildHardCount"])
	{
		$time_begin = getmicrotime();
		if ($HTTP_GET_VARS["nbChildHardCount"] =="")
		{
			$nbChild = nbChildHardCount();
			$nbChildSoft = nbChildSoftCount();
		}
		else
		{
			$nbChild = nbChildHardCount($HTTP_GET_VARS["nbChildHardCount"]);
			$nbChildSoft = nbChildSoftCount($HTTP_GET_VARS["nbChildHardCount"]);
		}
		$time_end = getmicrotime();
		$displayResultnbChildHardCount = true;

	}
	elseif ($HTTP_GET_VARS["nodeToDelete"])
	{
		$time_begin = getmicrotime();
		removeNode($HTTP_GET_VARS["nodeToDelete"],false);
		$time_end = getmicrotime();
		$displayResultrefreshAllNbChildInBase = true;

	}
	else
	{
		/*===================
		BUILD PATH CATEGORY
		===================*/

		$sqlGetCatCodesList = "select code, code_P from `".$tbl_cat."`";
		$resGetCodesCatList = mysql_query_dbg($sqlGetCatCodesList);

		while ($nodes = mysql_fetch_array($resGetCodesCatList,MYSQL_ASSOC))
		{
			$code_p[$nodes["code"]]= $nodes["code_P"];
		}
		$this_code = $category;
		$htmlTreePath ="";
		while (!is_null($this_code))
		{
			$htmlTreePath = " &gt;&gt; <a href=\"".$PHP_SELF."?category=".$this_code."\">".$this_code."</a> ".$htmlTreePath;
			$this_code = $code_p[$this_code];
		}
		if ($htmlTreePath!="")
		{
			$htmlTreePath = " <a href=\"".$PHP_SELF."\">".$Institution."</a> ".$htmlTreePath;
		}

	/*====================================
	BUILD SUBCATEGORY LIST OF A CATEGORY
	====================================*/

		if ($category)
		{
			$noPHP_SELF =true;
			$sqlGetCatList = "
				SELECT
					`node`.`code`,
					`node`.`name`,
					`node`.`code_P`,
					`node`.`nb_childs` `nbChildNode`,
					COUNT( `course`.`cours_id` ) 	nbCourse,
					COUNT( `faculte_childs`.`id` ) 	nbChildCat
				FROM `".$tbl_cat."` `node`
				LEFT JOIN `".$tbl_cat."` faculte_childs
					ON `faculte_childs`.`code_P` = `node`.`code`
				LEFT JOIN `".$tbl_courses."` course
					ON `course`.`faculte` = `node`.`code`
				WHERE `node`.`code_P`=\"".$category."\"
				OR
				`node`.`code`=\"".$category."\"
				GROUP  BY `node`.`code`, `node`.`name` ";

			if ($showNodeEmpty) {$sqlGetCatList .= "HAVING nbCourse > 0 ";} // -> don't show empty faculties

			$sqlGetCatList .= "
				ORDER  BY `node`.`treePos` ";
			$dispBackHomePage = TRUE;
			//echo $sqlGetCatList;
		}
		else
		{
			$sqlGetCatList = "
				SELECT
					`node`.*,
					 (`node`.`nb_childs`) nbChildNode,
					COUNT( `cours`.`cours_id` ) nbCourse,
					COUNT( `faculte_childs`.`id` ) nbChildCat
				FROM `".$tbl_cat."` `node`
				LEFT JOIN `".$tbl_cat."` faculte_childs
					ON `faculte_childs`.`code_P` = `node`.`code`
				LEFT JOIN `".$tbl_courses."` cours
					ON `cours`.`faculte` = `node`.`code`
				WHERE `node`.`code_P` is NULL
				GROUP  BY `node`.`code` ";

			if ($showNodeEmpty) {$sqlGetCatList .= "HAVING nbCourse > 0 ";} // -> don't show empty faculties

			$sqlGetCatList .= "
			ORDER  BY `node`.`treePos`, `node`.`name`  ";
		}
		$resCats = mysql_query_dbg($sqlGetCatList);
		$thereIsSubCat = FALSE;
		if (mysql_num_rows($resCats)>0)
		{
			$htmlListCat ="
			<UL>";
			while ($catLine = mysql_fetch_array($resCats))
			{
				if ($catLine['code'] != $category)
				{
					$htmlListCat .="
				<li>
					<SMALL>
					<a href=\"".$PHP_SELF."?category=".$catLine['code']."\"><img src=\"".$clarolineRepositoryWeb."/img/opendir.gif\" border=\"0\" alt=\"".$langOpenNode."\" ></a>
					<a href=\"".$PHP_SELF."?showTree=".$catLine['code']."\"><img src=\"".$clarolineRepositoryWeb."/img/tree.gif\" border=\"0\" alt=\"".$langViewChildren."\" ></a>
					<a href=\"".$_SELF."?nbChildHardCount=".$catLine['code']."\"><img src=\"".$clarolineRepositoryWeb."/img/calculator.gif\" border=\"0\" alt=\"".$langRecountChildren."\" ></a>
					<a href=\"".$PHP_SELF."?nodeToEdit=".$catLine['code']."\"><img src=\"".$clarolineRepositoryWeb."/img/edit.gif\" border=\"0\" alt=\"".$langEditNode."\" ></a>
					<a href=\"".$PHP_SELF."?nodeToDelete=".$catLine['code']."\"><img src=\"".$clarolineRepositoryWeb."/img/delete.gif\" border=\"0\" alt=\"".$langDeleteNode."\" ></a>".
					(is_numeric($previousTreePos)?"<a href=\"".$_SELF."?nodeToSwap%5B%5D=".$catLine['treePos']."&nodeToSwap%5B%5D=".$previousTreePos."\"><img src=\"".$clarolineRepositoryWeb."/img/up.gif\" border=\"0\" alt=\"".$langUpInSameLevel."\" ></a>":"<img src=\"".$clarolineRepositoryWeb."/img/blank.gif\" width=\"15\" border=\"0\"   alt=\"no Action\" >").
					"

					</SMALL>
					".$catLine['name']."
					<small>
					(".$catLine['nbChildNode']." catégories - ".$catLine['nbCourse']." courses )
					</small>
";
					$previousTreePos = $catLine['treePos'];
					$thereIsSubCat = TRUE;
				}
				else
				{
					$htmlTitre = "
					<h3>".$catLine['name']."</h3>";
				}
			}
			$htmlListCat .="
			</UL>";
		}

		if ($dispBackHomePage)
		{
			$htmlBackHomePage = "
			<p>
				<small>
					<a href=\"".$PHP_SELF."\"><b>&lt;&lt;</b>
					".$langBackToHomePage."</a>
				</small>
			</p>
			";
		}

		$nodeToDetail = getNodeByCode($category);
		$displayListNode = true;
	}
}

include($includePath."/claro_init_header.inc.php");
claro_disp_tool_title(
	array(
	'mainTitle'=>$nameTools,
	'subTitle'=>$PHP_AUTH_USER." - ".$siteName." - ".$clarolineVersion
	)
	);
?>
<FORM action="" method="post" >
	<input type="submit" name="rebuiltTreePos" value="rebuilt Tree Pos">
	<input type="submit" name="refreshAllNbChildInBase" value="rebuilt nb Childs in db">
	<!--input type="submit" name="nbChildHardCount" value="count nodes"-->
</FORM>

<?
//////////////////////
// $displayEditNode //
//////////////////////
if ($displayEditNode)
{
?>
<br><br>
<DIV style="border: solid thin grey; margin-bottom:5px; padding:3px">
	<u>id</u> : <strong><?php echo $nodeToEdit["id"] ?></strong>
	<u>code</u> : <strong><?php echo $nodeToEdit["code"]?></strong>
	| <strong><?php echo $nodeToEdit["name"]?></strong>
	<u>Parent</u> : <strong><?php echo $nodeToEdit["code_P"]?></strong><br>
	<strong><?php echo $nodeToEdit["nb_childs"]?></strong> <u>childs</u>

	Pos : <?php echo $nodeToEdit["treePos"]?>

</DIV>
<FORM id="editNode" action="<?php echo $PHP_SELF ?>" method="POST">
	<fieldset id="editNode" name="editNode"  >
	<input type="hidden" name="nodeIdToUpdate" value="<?php echo $nodeToEdit["id"]?>"  >
	<label for="nodeCode" >Code : </label>
	<input type="text" id="nodeCode" name="nodeCode" value="<?php echo $nodeToEdit["code"]?>"  ><br>
	<label for="nodeName" >Nom : </label>
	<input type="text" id="nodeName" name="nodeName" value="<?php echo $nodeToEdit["name"]?>"  ><br>
	</fieldset>

	<input type="checkbox" id="canHaveCatChild" name="canHaveCatChild" <?php echo $nodeToEdit["canHaveCatChild"]?> value="true">
	<label for="canHaveCatChild" >can have child "nodes"</label><br>
	<input type="checkbox" id="canHaveCoursesChild" name="canHaveCoursesChild" <?php echo $nodeToEdit["canHaveCoursesChild"]?> value="true">
	<label for="canHaveCoursesChild" >can have child "courses"</label><br>
	<input type="submit">
</FORM><br><br><a href="<?php echo $PHP_SELF ?>"><?php echo $langBackToList ?></a><br>

<?php
}
///////////////////////////
// $displayFormToAddNode //
///////////////////////////
elseif ($displayFormToAddNode)
{
?>
<h4>Ajout d'une catégorie dans <?php echo $nodeParent["name"] ?><br></h4>
<FORM id="editNode" action="<?php echo $PHP_SELF ?>" method="POST">
	<fieldset id="addNode" >
	<input type="hidden" name="addNode" value="true"  >
	<input type="hidden" name="nodeCode_P" value="<?php echo $nodeParent["code"] ?>"  >
	Code Parent : <?php echo $nodeParent["code"]?><br>
	<label for="nodeCode" >Code : </label>
	<input type="text" id="nodeCode" name="nodeCode" value=""  ><br>
	<label for="nodeName" >Nom : </label>
	<input type="text" id="nodeName" name="nodeName" value=""  ><br>
	</fieldset>

	<input type="checkbox" id="canHaveCatChild" name="canHaveCatChild" <?php echo CONFVAL_DEFAULT_canHaveCatChild ?> value="true">
	<label for="canHaveCatChild" >can have child "nodes"</label><br>
	<input type="checkbox" id="canHaveCoursesChild" name="canHaveCoursesChild" <?php echo CONFVAL_DEFAULT_canHaveCoursesChild ?> value="true">
	<label for="canHaveCoursesChild" >can have child "courses"</label><br>

	<input type="submit">
</FORM>
<br><br><a href="<?php echo $PHP_SELF ?>?category=<?php echo $nodeParent["code"]?>"><?php echo $langBackToList ?></a><br>
<?php
}
///////////////////////////
// $displayResultAddNode //
///////////////////////////
elseif ($displayResultAddNode)
{
?>

 -- Node added --

<br><br><a href="<?php echo $PHP_SELF ?>?category=<?php echo $HTTP_POST_VARS["nodeCode_P"]?>"><?php echo $langBackToList ?></a><br>
<?php
}
///////////////////////////
// $displayResultUpdateNode //
///////////////////////////
elseif ($displayResultUpdateNode)
{
?>

 -- Node updated --

<br><br><a href="<?php echo $PHP_SELF ?>"><?php echo $langBackToList ?></a><br>
<br><br><a href="<?php echo $PHP_SELF ?>?category=<?php echo $HTTP_POST_VARS["nodeCode"]?>"><?php echo $langBackToList ?></a><br>

<?php
}


///////////////////////////////
// $displayResultSwitchNodes //
///////////////////////////////
elseif ($displayResultSwitchNodes)
{
?>
<br><br>
 OK
<br><br>
<br><br><a href="<?php echo $PHP_SELF ?>"><?php echo $langBackToList ?></a><br>


<?php
}




//////////////////////
// $displayShowTree //
//////////////////////
elseif ($displayShowTree)
{
?>
<br><br>
<a href="<?php echo $PHP_SELF ?>"><?php echo $langBackToList ?></a><br>
<br><br>

<?php
if ($rootNodeOfView["code"])
{
?>
<a href="<?php echo $PHP_SELF."?showTree=".$rootNodeOfView["code_P"]."&deep=".$deep ?>"><?php echo $rootNodeOfView["code_P"] ?></a>
<?php
}
?>
<a href="<?php echo $PHP_SELF."?showTree=".$rootNodeOfView["code"]."&deep=".($deep+1) ?>">Voir une génération plus loin</a>
<strong>Vue sur <?php echo $deep ?> générations</strong>
<?php if ($deep > 2)
{
?>
<a href="<?php echo $PHP_SELF."?showTree=".$rootNodeOfView["code"]."&deep=".($deep-1) ?>">Voir une génération de moins</a>
<?php
 }
?>
<?php if ($showActions)
{
	$showActions = true;
}
else
{
?>
<a href="<?php echo $PHP_SELF."?showTree=".$HTTP_GET_VARS["showTree"]."&deep=".$deep ?>&showActions=true">Montrer les commandes</a>
<?php
 }
?>
<br>

<br><?php htmlHorizontalTableViewOfTree($HTTP_GET_VARS["showTree"],0,$deep,$showActions) ?>
<br><br><a href="<?php echo $PHP_SELF ?>"><?php echo $langBackToList ?></a><br>

<?php

}

//////////////////////////////////
// $displayResultrebuiltTreePos //
//////////////////////////////////
elseif ($displayResultrebuiltTreePos)
{
?>
	<small>
	[Tree rebuilded-- in <?php echo $time_end-$time_begin; ?> seconds]
	</small>


<br><br><a href="<?php echo $PHP_SELF ?>"><?php echo $langBackToList ?></a><br>
<?php
}

///////////////////////////
// $displayResultrefreshAllNbChildInBase //
///////////////////////////
elseif ($displayResultrefreshAllNbChildInBase)
{
?>
	<small>
	[Tree re counted in <?php echo $time_end-$time_begin; ?> seconds]
	</small>
<br><br><a href="<?php echo $PHP_SELF ?>"><?php echo $langBackToList ?></a><br>
<?php
}

///////////////////////////
// $displayResultnbChildHardCount //
///////////////////////////
elseif ($displayResultnbChildHardCount)
{
?>
<br><br>
	<strong><?php echo $HTTP_GET_VARS["nbChildHardCount"]  ?></strong>
	have <?php echo $nbChild ?> childs node<br>
	<small>
	[count in <?php echo $time_end-$time_begin; ?> seconds]
	</small>

	<br>
	<?php if ($nbChild==$nbChildSoft)
	{ ?>
		<div class="success">
			Count in cache is right
			<?php echo $nbChild ?> childs node
		</div>
	<?php
	}
	else
	{
	?>
		<div class="warning">
			Count in cache is <strong>wrong</strong>
			<?php echo $nbChildsoft ?> childs node
		</div>
	<?php
	}
	?>

<br><br><a href="<?php echo $PHP_SELF ?>"><?php echo $langBackToList ?></a><br>
<?php
}

//////////////////////////
// $displayListOfCourse //
//////////////////////////
elseif ($displayListNode)
{
	echo $htmlTitre;
?>
<DIV style="border: solid thin grey; margin-bottom:5px; padding:3px">
	<u>id</u> : <strong><?php echo $nodeToDetail["id"] ?></strong>
	<u>code</u> : <strong><?php echo $nodeToDetail["code"]?></strong>
	| <strong><?php echo $nodeToDetail["name"]?></strong>
	<u>Parent</u> : <strong><?php echo $nodeToDetail["code_P"]?></strong><br>
	<strong><?php echo $nodeToDetail["nb_childs"]?></strong> <u>childs</u>

	Pos : <?php echo $nodeToDetail["treePos"]?>

</DIV>
<?php echo 
	($category?"
		<a href=\"".$PHP_SELF."?showTree=".$category."\"><img src=\"".$clarolineRepositoryWeb."/img/tree.gif\" border=\"0\" alt=\"".$langViewChildren."\" ></a>
		<a href=\"".$PHP_SELF."?nodeToEdit=".$category."\"><img src=\"".$clarolineRepositoryWeb."/img/edit.gif\" border=\"0\" alt=\"".$langEditNode."\" ></a>":"
		<a href=\"".$PHP_SELF."?showTree=NULL\"><img src=\"".$clarolineRepositoryWeb."/img/tree.gif\" border=\"0\" alt=\"".$langViewChildren."\" ></a>
")
	." <br><br>
	<small>".


	$htmlTreePath."</small>";
	if ($thereIsSubCat)		echo $htmlListCat;
	?><br><br>
	<a href="<?php echo $PHP_SELF ?>?addChildNodeTo=<?php if ($category) echo $category; else echo "NULL"; ?>" ><?php echo $langAddChildNode ?></a>
	<?php
}
/////////////////////////
// $displayListOfUsers //
/////////////////////////
elseif ($displayListOfUsers)
{
}
///////////////////
// $displayGoOut //
///////////////////
else
{
	echo "Vous n'avez pas accès à ceci";
}
//printCompleteTree();
@include($includePath."/claro_init_footer.inc.php");
function echosql($sqlString)
{
	echo "<br><span style=\"padding-left: 6px;	padding-right:
	6px;	padding-bottom: 1px;	padding-top: 1px;
	border-color: Blue;	font-size: small;	background-color: Silver;	color: Black;
	font-family: monospace;	border: thin ridge;	border-left: none;	border-right: none;
	text-align: justify;	float: right;\" >".$sqlString."</span>";
}

function getNodeByCode($code_node)
{
	GLOBAL $tbl_cat;
	if(is_null($code_node) || $code_node=="")
	{
		$sqlnbNode = "select count(id)  from `".$tbl_cat."` `node`";
		$resNbNode = mysql_query($sqlnbNode);
		$nbChilds = mysql_fetch_array($resNbNode);

		$node = array (
	 "code" => NULL,
	 "name" => "Root",
	 "treePos" => 0,
	 "nb_childs" => $nbChilds[0],
	 "canHaveCoursesChild" => DEFAULT_ROOT_CAN_HAVE_COURSE_CHILD,
	 "canHaveCatChild" => DEFAULT_ROOT_CAN_HAVE_CAT_CHILD);

	}
	else
	{
		$sqlGetNodeByCode = "select * from `".$tbl_cat."` `node`
		WHERE `node`.`code`='".$code_node."'";
		$resGetNodeByCode = mysql_query($sqlGetNodeByCode);
		$node = mysql_fetch_array($resGetNodeByCode,MYSQL_ASSOC);
	}
	return $node;
}

function updateNode($id,$code,$name,$canHaveCatChild=true,$canHaveCoursesChild=true)
{
	Global $tbl_cat;
/*
  ["nodeIdToUpdate"]		=>  string(2)	"20"
  ["nodeCode"]				=>  string(5)	"Hello"
  ["nodeName"]				=>  string(14)	"cours de salut"
  ["canHaveCatChild"]		=>  string(4)	"true"
  ["canHaveCoursesChild"]	=>  string(4)	"true"
*/
	$sqlGetNodeToUpdate = "select code from `".$tbl_cat."` `node`
	WHERE `id`	 = '".$id."'";
	$resGetNodeToUpdate = mysql_query_dbg($sqlGetNodeToUpdate);
	$nodeToUpdate = mysql_fetch_array($resGetNodeToUpdate,MYSQL_ASSOC);
	$nodeCodeUpdated = $nodeToUpdate["code"];

	$sqlUpdateNode = "UPDATE `".$tbl_cat."`
	SET
		`code`	 = '".$code."',
		`name`	 = '".$name."',
		`canHaveCoursesChild` 	= '".strtoupper($canHaveCoursesChild)."',
		`canHaveCatChild` 		= '".strtoupper($canHaveCatChild)."'
		WHERE
  			`id`	 = '".$id."'";
	mysql_query_dbg($sqlUpdateNode);

	$sqlUpdateNode = "UPDATE `".$tbl_cat."`
	SET
		`code_P` = '".$code."'
		WHERE
		`code_P`	 = '".$nodeCodeUpdated."'";
	mysql_query_dbg($sqlUpdateNode);
	return true;
}


 function htmlViewOfTree($code=null, $level=0)
 {
	if (is_null($code))
	{
		$nodeData["code"]		= "NULL";
		$nodeData["name"]		= "Racine de l'arbre";
		$nodeData["treePos"]	= 0;
		$childsNodes	= getNodesListChild("NULL");
	}
	else
	{
		$nodeData		= getNodeByCode($code);
		$childsNodes	= getNodesListChild($code);
	}
	$level++;
	echo "
	<div class=\"level".$level."\">"
	.$nodeData["code"]." : ".$nodeData["name"]."
	<br>
	<FONT size=\"".(3-$level)."\" >
	<small><small>

		(".$nodeData["nb_childs"]."ch Pos:".$nodeData["treePos"].")
	</small></small></FONT>";
	echo "</DIV>";
	if (is_array($childsNodes))
	{
		echo "<DIV>";
		while(list(,$child)=each($childsNodes))
		{
			echo "<DIV class=\"childNode\">";
			htmlViewOfTree($child["code"], $level);
			echo "</DIV>";
		}
		echo "</DIV>";
	}
 }

function htmlTableViewOfTree($code=null, $level=0, $showCommands=false)
 {
	if (is_null($code))
	{
		$nodeData["code"]		="NULL";
		$nodeData["name"]		="Racine de l'arbre";
		$nodeData["treePos"]	=0;
		$childsNodes	= getNodesListChild("NULL");
	}
	else
	{
		$nodeData		= getNodeByCode($code);
		$childsNodes	= getNodesListChild($code);
	}
	$level++;
	echo "
	<TABLE border=\"1\" cellpadding=\"0\"  cellspacing=\"0\" class=\"level".$level."\" >
	<TR class=\"level".$level."\" >
	<td valign=\"top\" align=\"center\" colspan=\"".$nodeData["nb_childs"]."\">"
	.$nodeData["code"]." : ".$nodeData["name"]."
	<br>
	<small><small>
		(".$nodeData["nb_childs"]."ch Pos:".$nodeData["treePos"].")


	</small></small>";
	echo "
		</td></TR>";
	if (is_array($childsNodes))
	{
		echo "<TR>
		";
		while(list(,$child)=each($childsNodes))
		{
			echo "<TD class=\"childNode\"  valign=\"top\" align=\"center\" >";
			htmlTableViewOfTree($child["code"], $level,  $showCommands);
			echo "</TD>";
		}
		echo "</TR>";
	}
	echo "</TABLE>";
 }


function htmlHorizontalTableViewOfTree($code="null", $level=0, $deep="all", $showCommand=false)
{
	global $langEditNode,$langViewChildren,$langDeleteNode, $clarolineRepositoryWeb;

	if (is_numeric($deep)) $deep--;
	echo "
";
	if (is_null($code)||strToLower($code)=="null")
	{
		$nodeData		= getNodeByCode(NULL);
		$childsNodes	= getNodesListChild("NULL");

	}
	else
	{
		$nodeData		= getNodeByCode($code);
		$childsNodes	= getNodesListChild($code);
	}
	//echo "<pre>".var_export($nodeData,true)."</pre>";
	$level++;
	if ($showCommand)
	{
		$commandHTML = "
		<a href=\"".$PHP_SELF."?category=".$nodeData['code']."\"><img src=\"".$clarolineRepositoryWeb."/img/textlist.gif\" border=\"0\" alt=\"".$langViewChildren."\" ></a>
		<a href=\"".$PHP_SELF."?nodeToEdit=".$nodeData['code']."\"><img src=\"".$clarolineRepositoryWeb."/img/edit.gif\" border=\"0\" alt=\"".$langEditNode."\" ></a>
		<a href=\"".$PHP_SELF."?showTree=".$nodeData['code']."\"><img src=\"".$clarolineRepositoryWeb."/img/tree.gif\" border=\"0\" alt=\"".$langViewChildren."\" ></a>
		<a href=\"".$PHP_SELF."?nodeToDelete=".$nodeData['code']."\"><img src=\"".$clarolineRepositoryWeb."/img/delete.gif\" border=\"0\" alt=\"".$langDeleteNode."\" ></a>
		<a href=\"".$_SELF."?nbChildHardCount=".$nodeData['code']."\"><img src=\"".$clarolineRepositoryWeb."/img/calculator.gif\" border=\"0\" alt=\"".$langRecountChildren."\" ></a><br><br>
		";
	}
	echo "
<!-- deep:".$deep." level:".$level." -->
<TABLE width=\"100%\" border=\"0\" cellpadding=\"0\"  cellspacing=\"0\"  >
	<TR >
		<td valign=\"center\" align=\"center\"".($nodeData["nb_childs"]>0?" rowspan=\"".$nodeData["nb_childs"]."\" ":"")." class=\"level".$level."\"  >
			<FONT size=\"".(7-$level)."\" >
				<a href=\"".$PHP_SELF."?category=".$nodeData["code"]."\" >"
					.$nodeData["code"]." : ".$nodeData["name"]."
				</a>
				<br>
				<small><small>
					(".($nodeData["nb_childs"]>0?$nodeData["nb_childs"]." childs ":"")."Pos:".$nodeData["treePos"].")
					".($showCommand?$commandHTML:"")."
				</small></small>
			</FONT>";
	echo "
		</td>";
	if (is_array($childsNodes)&&(deep=="all"||$deep>0))
	{
		$tr="";
		while(list(,$child)=each($childsNodes))
		{
			echo $tr."
		<TD class=\"childNode\"  valign=\"top\" align=\"center\" >";
			htmlHorizontalTableViewOfTree($child["code"], $level, $deep, $showCommand);
			echo "
		</TD>
	";
			$tr="
	</tr>
	<tr>";
		}
		//echo "<td>".($deep=="all"?"":$deep)."</td></TR>";
	}
	echo "
	</tr>
</table>";
 }


function getNodesListChild($codeParent)
{
	Global $tbl_cat;

	$sqlGetNodeParent = "select `node`.* from `".$tbl_cat."` `node`
		WHERE ";
		if ($codeParent == "NULL")
			$sqlGetNodeParent .= "`node`.`code_P` IS NULL";
		else
			$sqlGetNodeParent .= "`node`.`code_P`='".$codeParent."'";
	$sqlGetNodeParent .= " ORDER BY `node`.`treePos`";

	$resGetNodeParent = mysql_query_dbg($sqlGetNodeParent);
	while ($child =	mysql_fetch_array($resGetNodeParent,MYSQL_ASSOC))
	{
		$nodesListChild[] = $child;
	}

	return  $nodesListChild;

}

function printCompleteTree()
{
	global $tbl_cat;
/*
  ["addNode"]				=>  string(4)	"true"
  ["nodeCode_P"]			=>  string(4)	"ARTS"
  ["nodeTreePos"]			=>  string(0)	""
  ["nodeCode"]				=>  string(5)	"Hello"
  ["nodeName"]				=>  string(14)	"cours de salut"
  ["canHaveCatChild"]		=>  string(4)	"true"
  ["canHaveCoursesChild"]	=>  string(4)	"true"
*/
	$sqlGetNodeParent = "select * from `".$tbl_cat."` `node`
	order by code_P";
	$resGetNodeParent = mysql_query_dbg($sqlGetNodeParent);
	while ($node = mysql_fetch_array($resGetNodeParent,MYSQL_ASSOC))
	{
		$nodes[$node["code_P"]][$node["code"]] = $node["id"]." ".$node["treePos"]." ".$node["name"]." ";

	};
	echo "<PRE style=\"padding-left: 6px;	padding-right: 16px;	padding-bottom: 1px;	padding-top: 1px;
	border-color: Blue;	font-size: small;	background-color: Silver;	color: Black;
	font-family: monospace;	border: thin ridge;	border-left: none;	border-right: none;
	text-align: justify;	float: right;\">";
	echo "</PRE>";
}
/*
function nbChildRefresh($idCat)
{
	global $tbl_cat;
	$sqlGetNodes = "select * from `".$tbl_cat."` `node`
	WHERE code_P = '".$idCat."'";
	$resGetNodes = mysql_query_dbg($sqlGetNodes);
	while ($node = mysql_fetch_array($resGetNodes,MYSQL_ASSOC))
	{
		$idChild++;
		$idChild += nbChildRefresh($node["code"]);
	};

	return $nbChild

}
*/
function nbChildHardCount($idCat=null,$op=0)
{

	global $tbl_cat;
	$nbChild=0;

	if (is_null($idCat))
	{
		$sqlGetNodes = "select * from `".$tbl_cat."` `node`
		WHERE code_P is null";
	}
	else
	{
		$sqlGetNodes = "select * from `".$tbl_cat."` `node`
		WHERE code_P = '".$idCat."'";
	}
	$resGetNodes = mysql_query_dbg($sqlGetNodes);
	while ($node = mysql_fetch_array($resGetNodes,MYSQL_ASSOC))
	{
		$nbChild++;
		if ($op==1) echo "<ul>";
		$nbChild += nbChildHardCount($node["code"],$op);
		if ($op==1) echo "</ul>";

	};
	if ($op==1) echo "<li>Dans ".$idCat.": ". $nbChild." ";
	if ($op==2) echo "* ";

	return $nbChild;

}



function resfreshTreePosNode($idCat,$treePos)
{

	global $tbl_cat;

	echo "<LI>$idCat : $treePos</LI>";

	if ($idCat=="NULL")
	{
		$sqlGetNodes = "select * from `".$tbl_cat."` `node`
		WHERE code_P is null";
	}
	else
	{
		$sqlGetNodes = "select * from `".$tbl_cat."` `node`
		WHERE code_P = '".$idCat."'";
	}


	$resGetNodes = mysql_query($sqlGetNodes);
	while ($node = mysql_fetch_array($resGetNodes,MYSQL_ASSOC))
	{
		$treePos++;
		$sqlUpdateTreePos ="UPDATE `".$tbl_cat."`
		SET treePos = '".$treePos."'
		WHERE code = '".$node["code"]."'";
		mysql_query_dbg($sqlUpdateTreePos);
		echo "<ul>";
		$treePos = resfreshTreePosNode($node["code"],$treePos);
		echo "</ul>";
	};
	return $treePos;

}


function nbChildSoftCount($idCat=null)
{
	global $tbl_cat;
	$nbChild=0;
	if (is_null($idCat))
	{
		$sqlGetNodes = "select sum(`nb_childs` ) `nb_childs` from `".$tbl_cat."` `node`
		WHERE code_P is null";
	}
	else
	{
		$sqlGetNodes = "select `nb_childs` `nb_childs` from `".$tbl_cat."` `node`
		WHERE code = '".$idCat."'";
	}

	$resGetNodes = mysql_query_dbg($sqlGetNodes);
	$node = mysql_fetch_array($resGetNodes,MYSQL_ASSOC);
	return $node["nb_childs"];
}


function refreshNbChildInBase($idCat)
{
	global $tbl_cat;
	$sqlUpdateNbChilds =
	"UPDATE `".$tbl_cat."` SET `nb_childs` = ".nbChildHardCount($idCat,0)."
	WHERE code = '".$idCat."'";
	$resUpdateNodes = mysql_query_dbg($sqlUpdateNbChilds);
	return $nodeUpdated = mysql_affected_rows();
}


function refreshAllNbChildInBase()
{
	global $tbl_cat;


	$sqlGetChilds =
	"SELECT * FROM `".$tbl_cat."` `node` ";
	$resGetChilds = mysql_query_dbg($sqlGetChilds);
	while ($node = mysql_fetch_array($resGetChilds))
	{
		echo "<br>".$node["code"];
		$sqlUpdateNbChilds =
		"UPDATE `".$tbl_cat."` SET `nb_childs` = ".nbChildHardCount($node["code"],2)."
		WHERE code = '".$node["code"]."'";

		$resUpdateNodes = mysql_query_dbg($sqlUpdateNbChilds);
	}
	return $nodeUpdated = mysql_affected_rows();
}

  function getmicrotime()
  {
    list($usec, $sec) = explode(" ",microtime());
    return ((float)$usec + (float)$sec);
  }

/**
 * addNode($code,$name,$code_p,$canHaveCatChild=true,$canHaveCoursesChild=true)
 * @param $code 								string code of node
 * @param $name 								string name of node
 * @param $code_p 								string code of parent node (root  have code NULL)
 * @param $canHaveCatChild 	default true 	boolean if true this node can be parent of nodes
 * @param $canHaveCoursesChild	default true    boolean if true this node can be parent of courses
*/

//addNode($HTTP_POST_VARS["nodeCode"],$HTTP_POST_VARS["nodeName"],$HTTP_GET_VARS["nodeCode_P"],$HTTP_GET_VARS["canHaveCatChild"],$HTTP_GET_VARS["canHaveCoursesChild"]);

function addNode($code,$name,$code_P,$canHaveCatChild=true,$canHaveCoursesChild=true)
{
	global $tbl_cat;
// Ajouter un noeud.

// Je prends comme treePos, le treePos+nbchild de mon parent + 1
// j'incrémente de 1 tous les  treePos > mon treePos
// j'incrémente le nombre d'enfant de tous mes aileuls

// extention future
// Pour le moment on ajoute toujours  le nouveau comme fils cadet de la catégorie.
// On pourrait ajouter un enfant AVANT un autre en communicant son treepos ($treePosWanted)
// dans $myTreePos = (1*$nodeParent["nb_childs"])+ (1*$nodeParent["treePos"]) + 1;
// devient $myTreePos = $treePosWanted

//  SO
// 1° I need  get info about  the  node parent(with code_p)
// 2° newNode.treePos = P.treePos+P.nbchild+1
// 3° all node where (*.treePos > newNode.treePos) *.treePos = *.treePos + 1
// 4° increment code_p, code_p of code_P, ...


/*
  ["addNode"]				=>  string(4)	"true"
  ["nodeCode_P"]			=>  string(4)	"ARTS"
  ["nodeTreePos"]			=>  string(0)	""
  ["nodeCode"]				=>  string(5)	"Hello"
  ["nodeName"]				=>  string(14)	"cours de salut"
  ["canHaveCatChild"]		=>  string(4)	"true"
  ["canHaveCoursesChild"]	=>  string(4)	"true"
*/
	if ($code_P == "NULL" || $code_P == "")
	{
		$sqlGetInfoNodeParent = "select 0 treePos, count(id) nb_childs from `".$tbl_cat."`";
		unset($code_P);
	}
	else
	{
		$sqlGetInfoNodeParent = "select treePos, nb_childs
							from `".$tbl_cat."` `node`
						WHERE `node`.`code`='".$code_P."'";
	}
	$resGetNodeParent = mysql_query_dbg($sqlGetInfoNodeParent);
	$nodeParent = mysql_fetch_array($resGetNodeParent,MYSQL_ASSOC);
	$myTreePos = (1*$nodeParent["nb_childs"])+ (1*$nodeParent["treePos"]) + 1;
	//var_export($nodeParent);echo "--";var_export($myTreePos);

	$sqlUpdateNodesTreePos = "UPDATE `".$tbl_cat."`
		SET
   			`treePos`	 = `treePos`+1
			WHERE
			`treePos` >= '".$myTreePos."'
			ORDER BY `treePos` DESC";
		mysql_query_dbg($sqlUpdateNodesTreePos);

		$sqlInsertNode = "INSERT INTO `".$tbl_cat."`
		SET
   			`treePos`	 			= '".$myTreePos."',
			`code`	 				= '".$code."',
			`code_P` 				= ".(is_null($code_P)?"NULL":"'".$code_P."'").",
			`name`	 				= '".$name."',
			`canHaveCoursesChild` 	= '".strtoupper($canHaveCoursesChild)."',
			`canHaveCatChild` 		= '".strtoupper($canHaveCatChild)."'
		";

		mysql_query_dbg($sqlInsertNode);
		$idNewNode = mysql_insert_id();

		while (!is_null($code_P))
		{
			$sqlUpdateCodeP = "
			UPDATE `".$tbl_cat."`
				SET nb_childs = nb_childs +1
				WHERE `code` = '".$code_P."'";
			mysql_query_dbg($sqlUpdateCodeP);
			//echosql($sqlUpdateCodeP);
			$sqlGetCodeP = "
			Select code_P From `".$tbl_cat."`
				WHERE `code` = '".$code_P."'";
			$resNode_P = mysql_query_dbg($sqlGetCodeP);
			$node_P = mysql_fetch_array($resNode_P);
			$code_P = $node_P["code_P"];
		}

		return $idNewNode;
}



/**
 * removeNode($code,$removeNodesChilds=false)
 * @param $code 			string code of node
 * @param $removeNodesChilds 	default false 	boolean if true child are also deleted
*/

function removeNode($codeToDelete,$removeNodesChilds=false)
{
	global $tbl_cat, $tbl_courses, $langLogDeleteCat;
// supprimer un noeud.

//  2 Possibilités
//  /1/ $removeNodesChilds == true
//  Tous les noeuds enfants sont aussi supprimés
//  Les cours liés à ces noeuds sont liés au parent du noeud désigné
//  // En pratique
//		1° 	Lire le $codeToDelete.code_P.
//		2° 	Tous les cours dont faculté ont un code
//			dont le treepos est compris entre
//			Le $codeToDelete.treePos du code
//			et $codeToDelete.treePos+$codeToDelete.nbchild
//			sont rattaché à code.code_P
//		4°	Tous les treepos > $codeToDelete.treePos+$codeToDelete.nbchild
//			sont décrémenté de $codeToDelete.nbchild
//	/2/ $removeNodesChilds == false
//	Pour tous les noeuds dont code_P = $codeToDelete.code
//	et les cours cours dont faculté = $codeToDelete.code
//	on change le code en $codeToDelete.code_P
//	les noeuds parents perdent un enfant et
//	tous les treepos > $codeToDelete.treePos sont réduit de 1

//	2 possibilities
//  /1/ $removeNodesChilds == true
//  All node childs and sub child of the gived node are deleted
//  Courses linked to one of theses nodes are linked to parent of gived node
//  // Work
//		1° 	read $codeToDelete.code_P.
//		2° 	All course wich cat have a treePos between
//			$codeToDelete.treePos and $codeToDelete.treePos+$codeToDelete.nbchild
//			have now codeToDelete.code_p as now catergory (faculty) code
//		4°	Each node with treepos > $codeToDelete.treePos+$codeToDelete.nbchild
//			are substract value of $codeToDelete.nbchild
//	/2/ $removeNodesChilds == false
//	All node with code_P = $codeToDelete.code
//	and courses where faculty = $codeToDelete.code
//	code set as $codeToDelete.code_P
//	Parent node lose 1 child and
//	all treepos > $codeToDelete.treePos are reduce of 1.


	if ($removeNodesChilds)
	{
		return false; //not done actually
		/*

//		1° 	Lire le $codeToDelete.code_P.
//		2° 	Tous les cours dont faculté ont un code
//			dont le treepos est compris entre
//			Le $codeToDelete.treePos du code
//			et $codeToDelete.treePos+$codeToDelete.nbchild
//			sont rattaché à code.code_P
//		4°	Tous les treepos > $codeToDelete.treePos+$codeToDelete.nbchild
//			sont décrémenté de $codeToDelete.nbchild
		$sqlGetInfoNode = "Select * from `".$tbl_cat."` Where code = '".$codeToDelete."'";
		$resInfoNode = mysql_query_dbg($sqlGetInfoNode);
		$nbNode = mysql_num_rows($resInfoNode);
		if ($nbNode == 1)
		{
			$nodeToDelete = mysql_fetch_array($resInfoNode, MYSQL_ASSOC);
			claro_log($langLogDeleteCat." ".var_export($nodeToDelete,true));
		}
		else
		{
			die("table of category corrupted"); //
		}
		*/

	}
	else
	{
		$sqlGetInfoNode = "Select * from `".$tbl_cat."` Where code = '".$codeToDelete."'";
		$resInfoNode = mysql_query_dbg($sqlGetInfoNode);
		$nbNode = mysql_num_rows($resInfoNode);
		if ($nbNode == 1)
		{
			$nodeToDelete = mysql_fetch_array($resInfoNode, MYSQL_ASSOC);
			claro_log($langLogDeleteCat." ".var_export($nodeToDelete,true));
		}
		else
		{
			die("table of category corrupted"); //
		}
		$sqlUpdateCatChilds 	="Update `".$tbl_cat."` 	Set code_P = '".$nodeToDelete["code_P"]."' where code_P = '".$nodeToDelete["code"]."' ";
		$sqlUpdateCourseChilds 	="Update `".$tbl_courses."`	Set faculte = '".$nodeToDelete["code_P"]."' where faculte = '".$nodeToDelete["code"]."' ";
		$sqlUpdateTreePos		="Update `".$tbl_cat."` 	Set treePos = treePos - 1  where treePos > '".$nodeToDelete["treePos"]."' ";
		$sqlDeleteCat			="Delete From `".$tbl_cat."` where code = '".$nodeToDelete["code"]."' ";
		$resUpdateCatChilds 	= mysql_query_dbg($sqlUpdateCatChilds);
		$resUpdateCourseChilds 	= mysql_query_dbg($sqlUpdateCourseChilds);
		$resUpdateTreePos 		= mysql_query_dbg($sqlUpdateTreePos);
		$resDeleteCat 			= mysql_query_dbg($sqlDeleteCat);
		modifyNbChilds($nodeToDelete["code_P"],-1);
	}
}


function modifyNbChilds($code,$step=1)
{
	global $tbl_cat;
	if (!is_numeric($step))
		die("step is not numeric");
	$sqlGetInfoNode = "Select code_P from `".$tbl_cat."` Where code = '".$code."'";
	$resInfoNode = mysql_query_dbg($sqlGetInfoNode);
	$nodeToEdit = mysql_fetch_array($resInfoNode);
	if (!is_null($nodeToEdit["code_P"] ))
	{
		modifyNbChilds($nodeToEdit["code_P"],$step);
	}
	$sqlUpdateNbChilds = "Update `".$tbl_cat."` set `nb_childs` = `nb_childs` + ".$step." Where code = '".$code."'";
	mysql_query($sqlUpdateNbChilds);
}

/**
 * function swapNodes($nodeOne,$nodeTwo)
 *
 * @author Christophe Gesché <moosh@phpfrance.com>
 * switch two nodes, each keep childs but  treePos must be recomputed
 * if the two nodes don't have some code_P nb_child must change until
 * common code_P
 * practical
 * treePos of the  lowest node.treePos
 * About treePos
 * 3 interval
 *	- treepos = Node1.treepos to (Node1.treepos+Node1.nb_childs)
 *	- treepos = Node2.treepos to (Node2.treepos+Node2.nb_childs)
 *	- treepos = (Node1.treepos+Node1.nb_childs+1) to (Node2.treepos - 1)
 */
function swapNodes($nodeOne,$nodeTwo)
{
	global $tbl_cat;

	$sqlGetInfoNode = "Select * from `".$tbl_cat."` Where treePos IN ($nodeOne,$nodeTwo) Order By TreePos";
	$resInfoNode = mysql_query_dbg($sqlGetInfoNode);
	$nbNode = mysql_num_rows($resInfoNode);
	 if ($nbNode == 2)
	{
		$nodeA = mysql_fetch_array($resInfoNode, MYSQL_ASSOC);
		$nodeB = mysql_fetch_array($resInfoNode, MYSQL_ASSOC);
	}
	elseif ($nbNode < 2)
	{
		return false; // node(s) unexisting
	}
	else
	{
		die("table of category corrupted"); //
	}

	$sqlGetCodeSegment1 = "SELECT code FROM `".$tbl_cat."` WHERE treePos >= ".$nodeA["treePos"]." AND treePos <= ".($nodeB["treePos"]-1)." ";
	$sqlGetCodeSegment2 = "SELECT code FROM `".$tbl_cat."` WHERE treePos >= ".$nodeB["treePos"]." AND treePos <= ".($nodeB["treePos"]+$nodeB["nb_childs"])." ";

	$resCodeSegment1 = mysql_query_dbg($sqlGetCodeSegment1);
	$resCodeSegment2 = mysql_query_dbg($sqlGetCodeSegment2);

	while ($codeFound = mysql_fetch_array($resCodeSegment1,MYSQL_ASSOC))
	{
		$codesSeg1[] = $codeFound["code"];
	}
	while ($codeFound = mysql_fetch_array($resCodeSegment2,MYSQL_ASSOC))
	{
		$codesSeg2[] = $codeFound["code"];
	}

	$sqlUpdatePart1 ="UPDATE `".$tbl_cat."` SET treepos = (treePos+".($nodeB["nb_childs"]+1).") WHERE code IN ('".implode("','",$codesSeg1)."')";
	$sqlUpdatePart2 ="UPDATE `".$tbl_cat."` SET treepos = (treePos-".($nodeB["treePos"]-$nodeA["treePos"]).") WHERE code IN ('".implode("','",$codesSeg2)."')";

	$resCodeSegment1 = mysql_query_dbg($sqlUpdatePart1);
	$resCodeSegment2 = mysql_query_dbg($sqlUpdatePart2);

}


function claro_log($logString)
{
	 if (!function_exists('syslog'))
	 {
	 }
	 else
		syslog(LOG_INFO,$logString);
}
?>
