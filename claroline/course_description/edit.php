<?php // | $Id$ |
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.3.2 $Revision$                            |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2002 Universite catholique de Louvain (UCL)      |
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
 *
 */
/*
 
CREATE TABLE `course_description` 
(
	`id` TINYINT UNSIGNED DEFAULT '0' NOT NULL,
	`title` VARCHAR(255),
	`content` TEXT,
	`upDate` DATETIME NOT NULL,
	UNIQUE (`id`)
)
COMMENT = 'for course description tool';

*/
$langFile = "course_description";

@include('../inc/claro_init_global.inc.php'); 
@include($includePath."/lib/text.lib.php"); 

$nameTools = $langCourseProgram;
$htmlHeadXtra[] = "<style type=\"text/css\">
<!--
	BODY {background-color: #FFFFFF;}
	.QuestionDePlanification {  background-color: ". $color2."; background-position: left center; letter-spacing: normal; text-align: justify; text-indent: 3pt; word-spacing: normal; padding-top: 2px; padding-right: 5px; padding-bottom: 2px; padding-left: 5px}
	.InfoACommuniquer { background-color: ". $color1."; background-position: left center; letter-spacing: normal; text-align: justify; text-indent: 3pt; word-spacing: normal; padding-top: 2px; padding-right: 5px; padding-bottom: 2px; padding-left: 5px ; }
-->
</style>";


// $interbredcrump[]= array ("url"=>"index.php", "name"=> $langCourseProgram);

$nameTools = $langEditCourseProgram ;
$interbredcrump[]= array ("url"=>"index.php", "name"=> $langCourseProgram);
$TABLECOURSEDESCRIPTION = $_course['dbNameGlu']."course_description";

$is_allowedToEdit = $is_courseAdmin;
$showPedaSuggest = true;
@include($includePath."/../lang/english/pedaSuggest.inc.php");
@include($includePath."/../lang/".$_course['language']."/pedaSuggest.inc.php");

if ( !$is_allowedToEdit )
{
	header("Location:./index.php");
}
@include($includePath."/claro_init_header.inc.php");
?>
<h3>
	<?php echo $nameTools ?>
</h3>

<?
####################################################

if ($is_allowedToEdit)
// if user is not admin,  they can change content
{ 
//// SAVE THE BLOC
	if (isset($save))
	{
	// it's second  submit,  data  must be write in db
	// if edIdBloc contain Id  was edited
	// So  if  it's add,   line  must be created
		if($HTTP_POST_VARS["edIdBloc"]=="add")
		{
		    $sql="SELECT MAX(id) as idMax From `".$TABLECOURSEDESCRIPTION."` ";
			$res = mysql_query_dbg($sql);
			$idMax = mysql_fetch_array($res);
			$idMax = max(sizeof($titreBloc),$idMax["idMax"]);
			$sql ="
	INSERT IGNORE
		INTO `".$TABLECOURSEDESCRIPTION."` 
		(`id`) 
		VALUES
		('".($idMax+1)."');";
		$HTTP_POST_VARS["edIdBloc"]= $idMax+1;
		}
		else
		{
			$sql ="
	INSERT IGNORE
		INTO `".$TABLECOURSEDESCRIPTION."` 
		(`id`) 
		VALUES 
		('".$HTTP_POST_VARS["edIdBloc"]."');";
		}
		mysql_query_dbg($sql);
		if ($edTitleBloc=="")
		{
			$edTitleBloc = $titreBloc[$edIdBloc];
		};
		$sql ="
		Update 
		`".$TABLECOURSEDESCRIPTION."` 
		SET
		`title`= '".trim($edTitleBloc)."',
		`content` ='".trim($edContentBloc)."',
		`upDate` = NOW() 
		WHERE id = '".$HTTP_POST_VARS["edIdBloc"]."';";
		mysql_query_dbg($sql);
	}
	
//// Kill THE BLOC
	if (isset($deleteOK))
	{
		$sql = "SELECT * FROM `".$TABLECOURSEDESCRIPTION."` where id = '".$HTTP_POST_VARS["edIdBloc"]."'";
		$res = mysql_query_dbg($sql,$db);
		$blocs = mysql_fetch_array($res);
		if (is_array($blocs))
		{
			echo "
			<DIV class=\"deleted\">
				<B>
					".$blocs["title"]."
				</B>
				<BR>
				".$blocs["content"]."
			</Div>";
		}
		
		$sql ="Delete From `".$TABLECOURSEDESCRIPTION."` where id = '".$HTTP_POST_VARS["edIdBloc"]."'";
		$res = mysql_query_dbg($sql,$db);
		echo "
		<BR>
		<a href=\"".$PHP_SELF."\">".$langBack."</a>";
	}
//// Edit THE BLOC 
	elseif(isset($numBloc))
	{
		if (is_numeric($numBloc))
		{
			$sql = "SELECT * FROM `".$TABLECOURSEDESCRIPTION."` where id = '".$numBloc."'";
			$res = mysql_query_dbg($sql,$db);
			$blocs = mysql_fetch_array($res);
			if (is_array($blocs))
			{
				$titreBloc[$numBloc]=$blocs["title"];
				$contentBloc = $blocs["content"];
			}
		}
		echo "
<form  method=\"post\" action=\"$PHP_SELF\">
						<p>
							<b>
									".$titreBloc[$numBloc]."
							</b>
							<br>";
		if ($delete=="ask")
		{
			echo "
	".ucfirst($langDelete)." :
	<input type=\"submit\" name=\"deleteOK\" value=\"".$langDelete."\">
	<BR>";
		}

		if (($numBloc == "add" ) || !$titreBlocNotEditable[$numBloc] )
		{ 
			echo '
	<label for="edTitleBloc">'.$langOuAutreTitre."</label>
	<br>
	<input type=\"text\" name=\"edTitleBloc\" id=\"edTitleBloc\" size=\"50\" value=\"".$titreBloc[$numBloc]."\" >";
		}
		else
		{
			echo "
	<input type=\"hidden\" name=\"edTitleBloc\" value=\"".$titreBloc[$numBloc]."\" >";
		}

		if ($numBloc =="add")
		{ 
			echo "
	<input type=\"hidden\" name=\"edIdBloc\" value=\"add\">";
		}
		else
		{
			echo "
	<input type=\"hidden\" name=\"edIdBloc\" value=\"".$numBloc."\">";
		}
		echo "
</p>
<table>
	<tr>
		<td valign=\"top\">		
			<p>
					<label for=\"edContentBloc\">".$langContenuPlan."</label>
				<textarea cols=\"40\" rows=\"10\" name=\"edContentBloc\" id=\"edContentBloc\" wrap=\"virtual\">"
				.$contentBloc
				."</textarea>
			</p>
		</td>";
		if ($showPedaSuggest)
		{
			if (isset($questionPlan[$numBloc]))
			{
				echo "
								<td valign=\"top\">		
			<table>
				<tr>
					<td valign=\"top\" class=\"QuestionDePlanification\">		
						<b>
								".$langQuestionPlan."
						</b>
						<br>
							".$questionPlan[$numBloc]."
					</td>		
				</tr>
			</table>";
			}
			if (isset($info2Say[$numBloc]))
			{
				echo "
			<TABLE>
				<TR>
					<td valign=\"top\" class=\"InfoACommuniquer\">		
						<b>
							$langInfo2Say
						</b>
						<br>
							".$info2Say[$numBloc]."
					</td>
				</TR>
			</TABLE>
		</td>";
			}
		}
		echo "
	</tr>
</table>
<input type=\"submit\" name=\"save\" value=\"".$langValid."\">
<input type=\"submit\" name=\"ignore\" value=\"".$langBackAndForget ."\">
</form>
";
	}
	else
	{

		$sql = " SELECT * FROM `".$TABLECOURSEDESCRIPTION."` order by id";
		$res = mysql_query_dbg($sql,$db);
		while($bloc = mysql_fetch_array($res))
		{
			$blocState[$bloc["id"]] 	= "used";
			$titreBloc[$bloc["id"]]		= $bloc["title"];
			$contentBloc[$bloc["id"]] 	= $bloc["content"];
		}
		echo"
<table width=\"100%\" >
	<TR>
		<TD valign=\"middle\">
			<b>
					$langAddCat
			</b>
		</TD>
		<TD align=\"right\" valign=\"middle\">
<form   method=\"post\" action=\"$PHP_SELF\">
			<select name=\"numBloc\" size=\"1\">";
		while (list($numBloc,) = each($titreBloc))
		{ 
			if (!isset($blocState[$numBloc])||$blocState[$numBloc]!="used")
			echo "
				<option value=\"".$numBloc."\">".$titreBloc[$numBloc]."</option>";
		}
		echo "
				<option value=\"add\">".$langNewBloc."</option>
			</select>
			<input type=\"submit\" name=\"add\" value=\"".$langAdd."\">
</form>
		</TD>
	</TR>
</TABLE>
";
		echo "
<TABLE width=\"100%\">
	<TR>
		<TD colspan=\"2\" bgcolor=\"".$color2."\" class=\"alternativeBgDark\">
		</TD>
	</TR>";
		reset($titreBloc);		
		while (list($numBloc,) = each($titreBloc))
		{ 
			if (isset($blocState[$numBloc])&&$blocState[$numBloc]=="used")
			{
				echo "
	<TR>
		<TD  bgcolor=\"$color1\" class=\"alternativeBgLight\">
			<H4>".$titreBloc[$numBloc]."</H4>
		</TD>
		<TD align=\"left\">
			<a href=\"".$PHP_SELF."?numBloc=".$numBloc."\"><img src=\"../img/edit.gif\" alt=\"$langModify\" border=\"0\"></a>
			<a href=\"".$PHP_SELF."?delete=ask&numBloc=".$numBloc."\"><img src=\"../img/delete.gif\" alt=\"",$langDelete,"\" border=\"0\"></a>
		</TD>
	</TR>
	<TR>
		<TD colspan=\"2\">
			".make_clickable(nl2br($contentBloc[$numBloc]))."
		</TD>
	</TR>";
			}
		}
		echo "
</TABLE>";
	}
}
else 
{
	exit();
}

// End of page

@include($includePath."/claro_init_footer.inc.php");

function mysql_query_dbg($sql,$db="###")
{
    if ($db=="###")
	{
		$val =  @mysql_query($sql);
	}
	else
	{
		$val =  @mysql_query($sql,$db);
	}
	if (mysql_errno())
	{
		echo "<HR>".mysql_errno().": ".mysql_error()."<br><PRE>$sql</PRE><HR>";
	}
    else
	{
		echo "<!-- \n$sql\n-->";
	}

	return $val;
}

?>
