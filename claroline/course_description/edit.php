<?php // $Id$
/*
+----------------------------------------------------------------------+
| CLAROLINE 1.6
+----------------------------------------------------------------------+
| Copyright (c) 2001, 2004 Universite catholique de Louvain (UCL)      |
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
define("DISP_EDIT_FORM", __LINE__);
define("DISP_LIST_BLOC", __LINE__);

$langFile = "course_description";

$showPedaSuggest = true; 

require('../inc/claro_init_global.inc.php'); 
if ( ! $_cid) claro_disp_select_course();

$is_allowedToEdit = $is_courseAdmin;
if ( ! $is_courseAllowed) claro_disp_auth_form();
$tbl_cdb_names = claro_sql_get_course_tbl();
$tbl_course_description  = $tbl_cdb_names['course_description'];

include('tiplistinit.inc.php');

$htmlHeadXtra[] =
         "<script>
          function confirmation (name)
          {
              if (confirm(\" $langAreYouSureToDelete \"+ name +\" ?\"))
                  {return true;}
              else
                  {return false;}
          }
          </script>";
		  
if ( !$is_allowedToEdit )
{
    header("Location:./index.php");
}
else // if user is not admin, they can change content
{ 
    //// SAVE THE BLOC
    if (isset($_REQUEST['save']))
    {
        // it's second  submit,  data  must be write in db
        // if edIdBloc contain Id  was edited
        // So  if  it's add,   line  must be created
        if($_REQUEST['edIdBloc']=='add')
        {
            $sql="SELECT MAX(id) as idMax From `".$tbl_course_description."` ";
            $resGetMax = claro_sql_query_fetch_all($sql);
            $idMax = max(sizeof($titreBloc),$resGetMax[0]["idMax"]);
            $sql ="INSERT IGNORE
                   INTO `".$tbl_course_description."` 
                          (`id`) 
                   VALUES ('".($idMax+1)."');";
            $edIdBloc = $idMax+1;
        }
        else
        {
            $edIdBloc = (int) $_REQUEST['edIdBloc'];
            $sql ="INSERT IGNORE
                   INTO `".$tbl_course_description."` 
                          (`id`) 
                   VALUES ('".$edIdBloc."');";
        }

        claro_sql_query($sql);

        if (isset($_REQUEST['edTitleBloc']))
        {
            $edTitleBloc = claro_addslashes($_REQUEST['edTitleBloc']);
        }
        else
        {
            $edTitleBloc = $titreBloc[$edIdBloc];
        }

        $sql ="Update `".$tbl_course_description."` 
               SET
                   `title`= '".trim($edTitleBloc)."',
                `content` ='".trim(claro_addslashes($_REQUEST['edContentBloc']))."',
                 `upDate` = NOW() 
               WHERE 
                     `id` = '". $edIdBloc ."'";
        claro_sql_query($sql);
    }

//// Kill THE BLOC
    if (isset($_REQUEST['deleteOK']))
    {
        $dialogBox = $langDeleted;

        $sql ="DELETE From `".$tbl_course_description."` where id = '".$_REQUEST["edIdBloc"]."'";
        $res = claro_sql_query($sql,$db);
        $display = DISP_LIST_BLOC;
    }
	
//// Edit THE BLOC 
    if(isset($_REQUEST['numBloc']))
    {
        if (is_numeric($_REQUEST['numBloc']))
        {
            $sql = "SELECT `title`, `content`  
                    FROM `".$tbl_course_description."` 
                    WHERE `id` = '".$_REQUEST['numBloc']."'";
            $blocs = claro_sql_query_fetch_all($sql,$db);
            $blocs = $blocs[0]; // 1 line attempt
            if (is_array($blocs))
            {
                $titreBloc[$numBloc]=$blocs['title'];
                $contentBloc = $blocs['content'];
            }
        }
        $display= DISP_EDIT_FORM;
    }
    else
    {
        $sql = "SELECT `id`, `title`, `content`  
               FROM `".$tbl_course_description."` 
               ORDER BY `id`";
        $blocList = claro_sql_query_fetch_all($sql);
        if (is_array($blocList))
        {
            foreach($blocList as $thisBloc)
            {
                $blocState  [$thisBloc['id']]     = 'used';
                $titreBloc  [$thisBloc['id']]    = $thisBloc['title'];
                $contentBloc[$thisBloc['id']]     = $thisBloc['content'];
            }
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

    if (isset($display)) // this if would be remove when convertion to MVC is done
    {
        $nameTools = $langEditCourseProgram ;
        $interbredcrump[]= array ("url"=>"index.php", "name"=> $langCourseProgram);
        include($includePath."/claro_init_header.inc.php");
        claro_disp_tool_title($nameTools);
    }

    switch ($display)
    {
        case DISP_LIST_BLOC :
		
		if( isset($dialogBox) ) claro_disp_message_box($dialogBox);
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
?>
    <TR class="headerX">
        <TH >
            <?php echo $titreBloc[$numBloc] ?>
        </TH>
        <TH align="left">
            <a href="<?php echo $_SERVER['PHP_SELF']; ?>?numBloc=<?php echo $numBloc; ?>"><img src="<?php echo $clarolineRepositoryWeb; ?>img/edit.gif" alt="<?php echo $langModify; ?>" border="0"></a>
            <a href="<?php echo $_SERVER['PHP_SELF']; ?>?deleteOK=1&amp;edIdBloc=<?php echo$numBloc; ?>" "onClick="return confirmation('<?php echo addslashes($titreBloc[$numBloc]); ?>');"><img src="<?php echo $clarolineRepositoryWeb; ?>img/delete.gif" alt="<?php echo $langDelete; ?>" border="0"></a>
        </TH>
    </TR>
    <TR>
        <TD colspan="2">
            <?php echo claro_parse_user_text($contentBloc[$numBloc]) ?>
        </TD>
    </TR>
<?php 
            }
        }
        echo "
</TABLE>";
}
            break;

        case DISP_EDIT_FORM :
        ?>
<form  method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
<p>
<b>
<?php echo $titreBloc[$numBloc] ?>
</b>
<br />
<?php 
		echo '<input type="hidden" name="edIdBloc" value="'
            .($numBloc =="add" ? 'add' : $numBloc)
            .'">';

        if (($numBloc == "add" ) || !$titreBlocNotEditable[$numBloc] )
        { 
            echo '
<table>
    <tr>
        <td colspan="2">
            <label for="edTitleBloc">'.$langTitle.'</label>
            <br />
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
<?php
            claro_disp_html_area('edContentBloc', $contentBloc, 20, 80, $optAttrib=' wrap="virtual"');
?>
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
                    <td valign="top">
                        <b>
                            <?php echo $langQuestionPlan ?>
                        </b>
                        <br />
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
                    <td valign="top">
                        <b>
                            <?php echo $langInfo2Say ?>
                        </b>
                        <br />
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
