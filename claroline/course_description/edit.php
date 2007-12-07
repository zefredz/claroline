<?php // $Id$
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.5.*
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2004 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      | Authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
      |          Hugues Peeters    <peeters@ipm.ucl.ac.be>                   |
      |          Christophe Gesché <gesche@ipm.ucl.ac.be>                    |
      +----------------------------------------------------------------------+
*/
/**
 * This script edit the course description.
 *
 * This script is reserved for  user with write access on the course
 */

/*
* todo : 
* - change delete working. prefers a "javascript warning"
* - merge edit.php with index.php
* - find a better solution for pedaSuggest. Would be editable by pedagogical manager 
* - use claro_sql_fetch
* - CSS from main
* - reduce code in display.
* - table is really needed ?
* - use getTableNames
* - $showPedaSuggest = true; would be in a configuration file
* - be compatible with register_global off
*/
define("DISP_CMD_RESULT",__LINE__);
define("DISP_EDIT_FORM", __LINE__);
define("DISP_LIST_BLOC", __LINE__);


$langFile = "course_description";

$showPedaSuggest = true; 

require('../inc/claro_init_global.inc.php'); 
require($includePath."/lib/text.lib.php"); 

$nameTools = $langCourseProgram;
$htmlHeadXtra[] = "<style type=\"text/css\">
<!--
	BODY {background-color: #FFFFFF;}
	.QuestionDePlanification {  background-color: #ccffff; background-position: left center; letter-spacing: normal; text-align: justify; text-indent: 3pt; word-spacing: normal; padding-top: 2px; padding-right: 5px; padding-bottom: 2px; padding-left: 5px}
	.InfoACommuniquer { background-color: #ffffcc; background-position: left center; letter-spacing: normal; text-align: justify; text-indent: 3pt; word-spacing: normal; padding-top: 2px; padding-right: 5px; padding-bottom: 2px; padding-left: 5px ; }
-->
</style>";

$nameTools = $langEditCourseProgram ;
$interbredcrump[]= array ("url"=>"index.php", "name"=> $langCourseProgram);
$TABLECOURSEDESCRIPTION = $_course['dbNameGlu']."course_description";

$is_allowedToEdit = $is_courseAdmin;

@include($includePath."/../lang/english/pedaSuggest.inc.php");
@include($includePath."/../lang/".$_course['language']."/pedaSuggest.inc.php");

if ( !$is_allowedToEdit )
{
	header("Location:./index.php");
}
else
// if user is not admin,  they can change content
{ 

//// SAVE THE BLOC
	if (isset($save))
	{
	// it's second  submit,  data  must be write in db
	// if edIdBloc contain Id  was edited
	// So  if  it's add,   line  must be created
		if($_REQUEST["edIdBloc"]=="add")
		{
		    $sql="SELECT MAX(id) as idMax From `".$TABLECOURSEDESCRIPTION."` ";
			$res = claro_sql_query($sql);
			$idMax = mysql_fetch_array($res);
			$idMax = max(sizeof($titreBloc),$idMax["idMax"]);
			$sql ="
	INSERT IGNORE
		INTO `".$TABLECOURSEDESCRIPTION."` 
		(`id`) 
		VALUES
		('".($idMax+1)."');";
			$_REQUEST["edIdBloc"] = $idMax+1;
		}
		else
		{
			$sql ="
	INSERT IGNORE
		INTO `".$TABLECOURSEDESCRIPTION."` 
		(`id`) 
		VALUES 
		('".$_REQUEST["edIdBloc"]."');";
		}
		claro_sql_query($sql);
		if (isset($_REQUEST['edTitleBloc']))
		{
			$edTitleBloc = $titreBloc[$edIdBloc];
		}
		else
		{
			$edTitleBloc = claro_addslashes($_REQUEST['edTitleBloc']);
		}
		$sql ="
		Update 
		`".$TABLECOURSEDESCRIPTION."` 
		SET
		`title`= '".trim($edTitleBloc)."',
		`content` ='".trim(claro_addslashes($_REQUEST['edContentBloc']))."',
		`upDate` = NOW() 
		WHERE id = '".$_REQUEST["edIdBloc"]."';";
		claro_sql_query($sql);
	}

//// Kill THE BLOC
	if (isset($deleteOK))
	{
		$sql = "SELECT * FROM `".$TABLECOURSEDESCRIPTION."` where id = '".$_REQUEST["edIdBloc"]."'";
		$res = claro_sql_query($sql,$db);
		$blocs = mysql_fetch_array($res);
		if (is_array($blocs))
		{
			$msg['success'][] = '
			<DIV class="deleted">
				<B>
					'.$blocs["title"].'
				</B>
				<BR>
				'.$blocs["content"].'
				<br>
				'.$langDeleted.'
			</DIV>';
		}

		$sql ="DELETE From `".$TABLECOURSEDESCRIPTION."` where id = '".$_REQUEST["edIdBloc"]."'";
		$res = claro_sql_query($sql,$db);
		$display = DISP_CMD_RESULT;
	}
//// Edit THE BLOC 
	elseif(isset($numBloc))
	{
		if (is_numeric($numBloc))
		{
			$sql = "SELECT * FROM `".$TABLECOURSEDESCRIPTION."` where id = '".$numBloc."'";
			$res = claro_sql_query($sql,$db);
			$blocs = mysql_fetch_array($res);
			if (is_array($blocs))
			{
				$titreBloc[$numBloc]=$blocs["title"];
				$contentBloc = $blocs["content"];
			}
		}
		$display= DISP_EDIT_FORM;
	}
	else
	{
		$sql = " SELECT * FROM `".$TABLECOURSEDESCRIPTION."` order by id";
		$res = claro_sql_query($sql,$db);
		while($bloc = mysql_fetch_array($res))
		{
			$blocState  [$bloc["id"]] 	= "used";
			$titreBloc  [$bloc["id"]]	= $bloc["title"];
			$contentBloc[$bloc["id"]] 	= $bloc["content"];
		}
		while (list($numBloc,) = each($titreBloc))
		{ 
			if (isset($blocState[$numBloc])&&$blocState[$numBloc]=="used")
			{
				$listExistingBloc[$numBloc]['titre']   = $titreBloc[$numBloc];
				$listExistingBloc[$numBloc]['content'] = $contentBloc[$numBloc];
			}
			else
			{
				$listUnusedBloc[$numBloc]= $titreBloc[$numBloc];
			}
		}

		$display = DISP_LIST_BLOC;
	}

	if (isset($display)) // this if would be remove when conrvertion to MVC is done
	{
		include($includePath."/claro_init_header.inc.php");
		claro_disp_tool_title($nameTools);
	}

	switch ($display)
	{
		case DISP_LIST_BLOC :
?>
<table width="100%" >
	<TR>
		<TD valign="middle">
			<b>
				<?php echo $langAddCat ?>
			</b>
		</td>
		<td align="right" valign="middle">
<form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
			<select name="numBloc" size="1">
<?php
		while (list($numBloc,$titre) = each($listUnusedBloc))
		{ 
			echo '
				<option value="'.$numBloc.'">'.$titre.'</option>';
		}
?>
				<option value="add"><?php echo $langNewBloc ?></option>
			</select>
			<input type="submit" name="add" value="<?php echo $langAdd ?>">
</form>
		</TD>
	</TR>
</TABLE>
<?php

if (count($listExistingBloc)>0)
{ 

?>
<!-- LIST of existing blocs -->
<TABLE width="100%" class="claroTable">
<?php
		reset($titreBloc);		
		while (list($numBloc,) = each($titreBloc))
		{ 
			if (isset($blocState[$numBloc])&&$blocState[$numBloc]=="used")
			{
				echo '
	<TR class="headerX">
		<TH >
			'.$titreBloc[$numBloc].'
		</TH>
		<TH align="left">
			<a href="'.$PHP_SELF.'?numBloc='.$numBloc.'"><img src="'.$clarolineRepositoryWeb.'img/edit.gif" alt="'.$langModify.'" border="0"></a>
			<a href="'.$PHP_SELF.'?delete=ask&numBloc='.$numBloc.'"><img src="'.$clarolineRepositoryWeb.'img/delete.gif" alt="'.$langDelete.'" border="0"></a>
		</TH>
	</TR>
	<TR>
		<TD colspan="2">
			'.make_clickable(claro_parse_user_text($contentBloc[$numBloc]))."
		</TD>
	</TR>";
			}
		}
		echo "
</TABLE>";
}
			break;
		case DISP_CMD_RESULT :
		claro_disp_msg_arr($msg);
		?>
		<BR>
		<a href="<?php echo $_SERVER['PHP_SELF'] ?>"><?php echo $langBack ?></a>
	<?php
		break;
		case DISP_EDIT_FORM :
		?>
<form  method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
						<p>
							<b>
									<?php echo $titreBloc[$numBloc] ?>
							</b>
							<br>
<?php 
		if ($delete=="ask")
		{
			echo ucfirst($langDelete).' :
	<input type="submit" name="deleteOK" value="'.$langDelete.'">
	<BR>';
		}

		echo '
	<input type="hidden" name="edIdBloc" value="'.($numBloc =="add" ? 'add' : $numBloc).'">';

		if (($numBloc == "add" ) || !$titreBlocNotEditable[$numBloc] )
		{ 
			echo '
<table>
	<tr>
		<td colspan="2">
			<label for="edTitleBloc">'.$langOuAutreTitre.'</label>
			<br>
			<input type="text" name="edTitleBloc" id="edTitleBloc" size="50" value="'.$titreBloc[$numBloc].'" >
			</td>
		</tr>';
		}
		else
		{
			echo '
	<input type="hidden" name="edTitleBloc" value="'.$titreBloc[$numBloc].'" ></p>
<table>
';
		}

?>
	<tr>
		<td valign="top">		
			<p>
				<label for="edContentBloc"><?php echo $langContenuPlan ?></label>
				<textarea cols="40" rows="10" name="edContentBloc" id="edContentBloc" wrap="virtual"><?php echo $contentBloc ?></textarea>
			</p>
		</td>
<?php 
		if ($showPedaSuggest)
		{
			if (isset($questionPlan[$numBloc]))
			{
?>
		<td valign="top">		
			<table>
				<tr>
					<td valign="top" class="QuestionDePlanification">		
						<b>
							<?php echo $langQuestionPlan ?>
						</b>
						<br>
						<?php echo $questionPlan[$numBloc] ?>
					</td>		
				</tr>
			</table>
<?php
			}
			if (isset($info2Say[$numBloc]))
			{
?>
			<table>
				<tr>
					<td valign="top" class="InfoACommuniquer">		
						<b>
							<?php echo $langInfo2Say ?>
						</b>
						<br>
						<?php echo $info2Say[$numBloc]?>
					</td>
				</tr>
			</table>
		</td>
		<?php 
			}
		}
		?>
	</tr>
</table>
<input type="submit" name="save" value="<?php echo $langValid ?>">
<input type="submit" name="ignore" value="<?php echo $langBackAndForget ?>">
</form>
		<?php
	}
}

// End of page
include($includePath."/claro_init_footer.inc.php");
?>
