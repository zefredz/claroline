<?php # $Id$

/*
 * The INTRODUCTION MICRO MODULE is used to insert and edit
 * an introduction section on a Claroline Module.
 * It can be inserted on any Claroline Module, provided a connection 
 * to a course Database is already active.
 *
 * The introduction content are stored on a table called "introduction" 
 * in the course Database. Each module introduction has an Id stored on 
 * the table. It is this id that can make correspondance to a specific module.
 *
 * 'introduction' table description
 *   id : int
 *   texte_intro :text
 *
 *
 * usage :
 *
 * $moduleId = XX // specifying the module Id
 * include(moduleIntro.inc.php);
 */

$TBL_INTRODUCTION = $_course['dbNameGlu']."tool_intro";

@include_once($includePath.'/lib/text.lib.php');
/*
if ($is_courseAdmin) 
{
	$intro_editAllowed = true; // "view & edit" Mode
}
else
{
	$intro_editAllowed = false; // "view only" Mode
}
*/
$intro_editAllowed = $is_courseAdmin; 

/*=========================================================
  INTRODUCTION MICRO MODULE - COMMANDS SECTION (IF ALLOWED)
  ========================================================*/

if ($intro_editAllowed)
{
	/* Replace command */

	if( $intro_cmdUpdate )
	{
		$intro_content = trim($intro_content);

		if ( ! empty($intro_content) )
		{
			$sql = "REPLACE `".$TBL_INTRODUCTION."` 
                    SET id=\"".$moduleId."\", 
                        texte_intro=\"".claro_addslashes($intro_content)."\"";

        // Note : I don't understant why but in this special case we need 
        // an addslashes() where anywhere else we don't ... (Hugues)

            claro_sql_query($sql);
		}
		else 
		{
			$intro_cmdDel = true;	// got to the delete command
		}
	}

	/* Delete Command */

	if($intro_cmdDel)
	{
		$sql = "DELETE FROM `".$TBL_INTRODUCTION."` 
                WHERE id=\"".$moduleId."\"";

        claro_sql_query($sql);
	}
}


/*===========================================
  INTRODUCTION MICRO MODULE - DISPLAY SECTION
  ===========================================*/

/* Retrieves the module introduction text, if exist */

$sql = "SELECT texte_intro 
        FROM `".$TBL_INTRODUCTION."` 
        WHERE id=\"".$moduleId."\"";

list($intro_dbResult) = claro_sql_query_fetch_all($sql);
$intro_content  = $intro_dbResult['texte_intro'];

/* Determines the correct display */

if ($intro_cmdEdit || $intro_cmdAdd)
{
	$intro_dispDefault = false;
	$intro_dispForm    = true;
	$intro_dispCommand = false;
}
else
{
	$intro_dispDefault = true;
	$intro_dispForm    = false;

	if ($intro_editAllowed)
	{
		$intro_dispCommand = true;
	}
	else
	{
		$intro_dispCommand = false;
	}
}


/* Executes the display */

if ($intro_dispForm)
{
    echo	"<form action=\"".$PHP_SELF."\" method=\"post\">\n";

    claro_disp_html_area('intro_content', $intro_content);

    echo	"<br>\n",
			"<input class=\"claroButton\" type=submit value=\"".$langOk."\" name=\"intro_cmdUpdate\">\n";
    claro_disp_button($PHP_SELF, 'Cancel');
	echo	"<br>\n",
			"</form>\n";
}

if ($intro_dispDefault)
{
	$intro_content = claro_parse_user_text(($intro_content));
	$intro_content = make_clickable($intro_content); // make url in text clickable

	echo	"<p>\n",
			$intro_content,"\n",
			"</p>\n";
}

if ($intro_dispCommand)
{
    if( '' == trim(strip_tags($intro_content)) ) // displays "Add intro" Commands
	{
        echo '<p class="HelpText">'.$langHelpAddIntroText.'</p><p>';
		claro_disp_button($PHP_SELF.'?intro_cmdAdd=1', $langAddIntro);
        echo '</p>';
	}
	else // displays "edit intro && delete intro" Commands
	{
		echo	"<p>\n".
				"<small>\n".
				"<a href=\"".$PHP_SELF."?intro_cmdEdit=1\"><img src=\"",$urlAppend,"/claroline/img/edit.gif\" alt=\"",$langModify,"\" border=\"0\"></a>\n".
				"<a href=\"".$PHP_SELF."?intro_cmdDel=1\" onclick=\"javascript:if(!confirm('".addslashes(htmlentities($langConfirmYourChoice))."')) return false;\"><img src=\"",$urlAppend,"/claroline/img/delete.gif\" alt=\"",$langDelete,"\" border=\"0\"></a>\n".
				"</small>\n".
				"</p>\n";
	}
}


?>
