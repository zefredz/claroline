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
$langFile = "admin";
include('../inc/claro_init_global.inc.php');

@include ($includePath."/installedVersion.inc.php");

$htmlHeadXtra[] = "<style type=\"text/css\">
<!--
    ul { font-size : small }
-->
</STYLE>";

//declare needed tables

$tbl_faculty      = $mainDbName.'`.`faculte';

// Deal with interbredcrumps  and title variable

$interbredcrump[]= array ("url"=>$rootAdminWeb, "name"=> $langAdministrationTools);
$nameTools = $langSearchCourseAdvanced." : ";

//------------------------------------------------------------------------------------------------------------------------
//  USED SESSION VARIABLES
//------------------------------------------------------------------------------------------------------------------------
// clean session of possible previous search information.

session_unregister('admin_course_code');
session_unregister('admin_course_letter');
session_unregister('admin_course_search');
session_unregister('admin_course_intitule');
session_unregister('admin_course_category');
session_unregister('admin_course_language');
session_unregister('admin_course_access');
session_unregister('admin_course_subscription');
session_unregister('admin_course_order_crit');

// Search needed info in db to create the right formulaire

$sql_searchfaculty = "SELECT * FROM `$tbl_faculty` order by `treePos`";
$arrayFaculty = claro_sql_query_fetch_all($sql_searchfaculty);


//----------------------------------
// DISPLAY
//----------------------------------


//header and bredcrump display

include($includePath."/claro_init_header.inc.php");

if (! $_uid) exit("<center>You're not logged in !!</center></body>");

//tool title

claro_disp_tool_title($nameTools);

?>
<small><?=$langYouCanUsefields?> : </small><br>
<form action="admincourses.php" method="GET" >
<table border="0">
<tr>
  <td>
   <?=$langOfficialCode?> : <br>
  </td>
  <td>
    <input type="text" name="code" value="<?=$_GET['code']?>"/>
  </td>
</tr>

<tr>
  <td>
   <?=$langCourseTitle?> :  <br>
  </td>
  <td>
    <input type="text" name="intitule" value="<?=$_GET['intitule']?>"/>
  </td>
</tr>

<tr>
  <td>
   <?=$langCategory?> : <br>
  </td>
  <td>
    <select name="category">
    <option value="" ></option>
    <?php

        //Display each option value for categories in the select
        buildSelectFaculty($arrayFaculty,NULL,$_GET['category'],"");
    ?>
    </select>
  </td>
</tr>

<tr>
  <td>
   <?=$langLanguage?> : <br>
  </td>
  <td>
    <select name="language">
    <option value=""></option>
    <?php
      echo createSelectBoxLanguage($_GET['language']);
    ?>
    </select>
  </td>
</tr>

<tr>
  <td>
   <?=$langCourseAccess?> :
  </td>
  <td>
    <input type="radio" name="access" value="public"<?if ($_GET['access']=="public") echo "checked";?> ><?=$langPublic?></input>
    <input type="radio" name="access" value="private" <?if ($_GET['access']=="private") echo "checked";?>><?=$langPrivate?></input>
    <input type="radio" name="access" value="" <?if ($_GET['access']=="") echo "checked";?>><?=$langAll?></input>
  </td>
</tr>

<tr>
  <td>
   <?=$langSubscription?> :
  </td>
  <td>
    <input type="radio" name="subscription" value="allowed" <?if ($_GET['subscription']=="allowed") echo "checked";?>><?=$langAllowed?></input>
    <input type="radio" name="subscription" value="denied" <?if ($_GET['subscription']=="denied") echo "checked";?>><?=$langDenied?></input>
    <input type="radio" name="subscription" value="" <?if ($_GET['subscription']=="") echo "checked";?>><?=$langAll?></input>
  </td>
</tr>

<tr>
  <td>

  </td>
  <td>
    <input type="submit" class="claroButton" value="<?=$langSearchCourse?>" ></input>
  </td>
</tr>
</table>
</form>
<?php
include($includePath."/claro_init_footer.inc.php");

//NEEDED FUNCTION (to be moved in libraries)


/**
 *This function create de select box to choose categories
 *
 * @author  - < Benoît Muret >
 * @param   - elem            array     :     the faculties
 * @param   - father        string    :    the father of the faculty
 * @param    - $EditFather    string    :    the faculty editing
 * @param    - $space        string    :    space to the bom of the faculty

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
                ."> ".$space.$one_faculty['code']." </option>
                ";
                buildSelectFaculty($elem,$one_faculty["code"],$EditFather,$space);
            }
        }
    }
}

function createSelectBoxLanguage($selected=NULL)
{
    $arrayLangage=langageExist();
    foreach($arrayLangage as $entries)
    {
        $selectBox.="<option value=\"$entries\" ";

        if ($entries == $selected)
            $selectBox.=" selected ";

        $selectBox.=">".$entries;

        global $langNameOfLang;
        if (!empty($langNameOfLang[$entries]) && $langNameOfLang[$entries]!="" && $langNameOfLang[$entries]!=$entries)
            $selectBox.=" - $langNameOfLang[$entries]";

        $selectBox.="</option>\n";
    }

    return $selectBox;
}

function langageExist()
{
    global $clarolineRepositorySys;
    $dirname = $clarolineRepositorySys."lang/";

    if($dirname[strlen($dirname)-1]!='/')
        $dirname.='/';

    //Open the repertoy
    $handle=opendir($dirname);

    //For each reportery in the repertory /lang/
    while ($entries = readdir($handle))
    {
        //If . or .. or CVS continue
        if ($entries=='.' || $entries=='..' || $entries=='CVS')
            continue;

        //else it is a repertory of a langage
        if (is_dir($dirname.$entries))
        {
            $arrayLangage[]=$entries;
        }
    }
    closedir($handle);

    return $arrayLangage;
}

?>