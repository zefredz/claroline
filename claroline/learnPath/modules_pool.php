<?php
    // $Id$
/*
  +----------------------------------------------------------------------+
  | CLAROLINE version 1.5.*                                              |
  +----------------------------------------------------------------------+
  | Copyright (c) 2001, 2004 Universite catholique de Louvain (UCL)      |
  +----------------------------------------------------------------------+
  | This source file is subject to the GENERAL PUBLIC LICENSE,           |
  | available through the world-wide-web at                              |
  | http://www.gnu.org/copyleft/gpl.html                                 |
  +----------------------------------------------------------------------+
  | Authors: Lederer Guillaume <led@cerdecam.be>                         |
  |          Piraux Sébastien <pir@cerdecam.be>                          |
  +----------------------------------------------------------------------+

  DESCRIPTION:  This is the page where the list of modules of the course present on the platform can be browsed
                user allowed to edit the course can delete the modules form this page
  ****

*/

/*======================================
       CLAROLINE MAIN
  ======================================*/

  $langFile = "learnPath";

  $tlabelReq = 'CLLNP___';
  include ('../inc/claro_init_global.inc.php');

  $htmlHeadXtra[] =
            "<script>
            function confirmation (name)
            {
                if (confirm(\" $langAreYouSureDeleteModule \"+ name))
                    {return true;}
                else
                    {return false;}
            }
            </script>";

  $interbredcrump[]= array ("url"=>"../learnPath/learningPathList.php", "name"=> $langLearningPathList);
  $nameTools = $langModulesPoolToolName;

  //header
  @include($includePath."/claro_init_header.inc.php");



  // tables names
  $TABLELEARNPATH         = $_course['dbNameGlu']."lp_learnPath";
  $TABLEMODULE            = $_course['dbNameGlu']."lp_module";
  $TABLELEARNPATHMODULE   = $_course['dbNameGlu']."lp_rel_learnPath_module";
  $TABLEASSET             = $_course['dbNameGlu']."lp_asset";
  $TABLEUSERMODULEPROGRESS= $_course['dbNameGlu']."lp_user_module_progress";


  //lib of this tool
  @include($includePath."/lib/learnPath.lib.inc.php");




/*======================================
       CLAROLINE MAIN
  ======================================*/


      // main page

   $is_AllowedToEdit = $is_courseAdmin;
   if (! $is_AllowedToEdit or ! $is_courseAllowed ) die ("<center>Not allowed !</center>");



   // display title

   claro_disp_tool_title($nameTools);

   // display use explication text

   echo $langUseOfPool."<br><br>";

   // HANDLE COMMANDS:
   switch ($cmd)
   {

      // MODULE DELETE
      case "eraseModule" :

            // used to physically delete the module  from server
            include($includePath."/lib/fileManage.lib.php");

            $moduleDir   = $_course['path']."/modules";
            $moduleWorkDir = $coursesRepositorySys.$moduleDir;

            // delete all assets of this module
            claro_sql_query("DELETE
                           FROM `".$TABLEASSET."`
                          WHERE `module_id` = ".$_GET['cmdid'] );
            // delete from all learning path of this course but keep there id before
            $sql = "SELECT *
                         FROM `".$TABLELEARNPATHMODULE."`
                         WHERE `module_id` = ".$_GET['cmdid'];
            $result = claro_sql_query($sql);

            claro_sql_query("DELETE
                           FROM `".$TABLELEARNPATHMODULE."`
                          WHERE `module_id` = ".$_GET['cmdid'] );

            // delete the module in modules table
            claro_sql_query("DELETE
                           FROM `".$TABLEMODULE."`
                          WHERE `module_id` = ".$_GET['cmdid'] );

            //delete all user progression concerning this module
            $sql = "DELETE
                           FROM `".$TABLEUSERMODULEPROGRESS."`
                           WHERE 1=0 ";
            while ($list=mysql_fetch_array($result))
            {
                $sql.=" OR `learnPath_module_id`=".$list['learnPath_module_id'];
            }
            claro_sql_query($sql);

            // This query does the same as the 3 previous queries but does not work with MySQL versions before 4.0.0
            // delete all asset, all learning path module, and from module table
            /*
            claro_sql_query("DELETE
                           FROM `".$TABLEASSET."`, `".$TABLELEARNPATHMODULE."`, `".$TABLEMODULE."`
                          WHERE `module_id` = ".$_GET['cmdid'] );
            */

            // delete directory and it content
            @my_delete($moduleWorkDir."/module_".$_GET['cmdid']);
            break;

      // COMMAND RENAME :
      //display the form to enter new name
      case "rename" :
         //get current comment from DB
         $query="SELECT `name`
                   FROM `".$TABLEMODULE."`
                  WHERE `module_id` = '".$_GET['mod_id']."'";
         $result = claro_sql_query($query);
         $list = mysql_fetch_array($result);
         echo "
         <form method=\"POST\" name=\"rename\" action=\"",$PHP_SELF,"?cmd=mkrename\">
             <label for=\"newName\">".$langInsertNewModuleName."</label> :
             <input type=\"text\" name=\"newName\" id=\"newName\" value=\"".htmlspecialchars($list['name'])."\"></input>
             <input type=\"submit\" value=\" Ok \" name=\"submit\">
             <input type=\"hidden\" name=\"cmd\" value=\"mkrename\">
             <input type=\"hidden\" name=\"mod_id\" value=\"".$_GET['mod_id']."\">
         </form>
         ";
         break;

         //try to change name for selected module
      case "mkrename" :
         //check if newname is empty
         if( isset($_POST["newName"]) && $_POST["newName"] != "")
         {

              //check if newname is not already used in another module of the same course
              $sql="SELECT `name`
                      FROM `".$TABLEMODULE."`
                      WHERE `name` = '".claro_addslashes($_POST['newName'])."'";
              //echo $sql."<br>";
              $query = claro_sql_query($sql);
              $num = mysql_numrows($query);
              if($num == 0 ) // "name" doesn't already exist
              {
                 // if no error occurred, update module's name in the database
                 $query="UPDATE `".$TABLEMODULE."`
                         SET `name`= '".claro_addslashes($_POST['newName'])."'
                         WHERE module_id = '".$mod_id."'";
                  //echo $query."<br>";
                  $result = claro_sql_query($query);
              }
              else
              {
                   echo "<font color='red'>".$langErrorNameAlreadyExists."</font><br>";
              }
         }
         else
         {
              echo "<font color='red'>".$langErrorEmptyName."</font><br>";
         }
         break;

        // COMMAND COMMENT :
        //display the form to modify the comment
      case "comment" :
         //get current comment from DB
         $query="SELECT *
                 FROM `".$TABLEMODULE."`
                 WHERE `module_id` = '".$_GET['mod_id']."'";
         $result = claro_sql_query($query);
         $list = mysql_fetch_array($result);

         echo "
         <form method=\"POST\" name='comment' action=\"",$PHP_SELF,"?cmd=mkcomment\">
             <label for=\"newcomment\">".$langModifyCommentModuleName." ".$list['name']."</label> :<br>
             <textarea name=\"newcomment\" id=\"newcomment\" rows=\"5\" cols=\"50\">".$list['comment']."</textarea><br>
             <input type=\"submit\" value=\"$langOk\" name=\"submit\">
             <input type=\"hidden\" name=\"cmd\" value=\"mkcomment\">
             <input type=\"hidden\" name=\"mod_id\" value=\"".$_GET['mod_id']."\">
         </form>
         ";
         break;

      //make update to change the comment in the database for this module
      case "mkcomment":
          $sql = "UPDATE `".$TABLEMODULE."`
                    SET `comment` = '".claro_addslashes($_POST['newcomment'])."'
                    WHERE `module_id` = '".$_POST['mod_id']."'";
          //echo $sql."<br>";
          claro_sql_query($sql);
          break;

      //COMMAND CHANGE ACCESSIBILITY
      case "mkPublic" :
      case "mkPrivate" :
            $cmd == "mkPublic" ? $accessibility = 'PUBLIC' : $accessibility = 'PRIVATE';
            $sql = "UPDATE `".$TABLEMODULE."`
                            SET `accessibility` = '$accessibility'
                            WHERE `module_id` = ".$_GET['mod_id']."
                              AND `accessibility` != '$accessibility'";
            $query = claro_sql_query ($sql);
            break;

   }


     echo "<table class=\"claroTable\" width=\"100%\" border=\"0\" cellspacing=\"2\">

            <tr class=\"headerX\" align=\"center\" valign=\"top\">
              <th>
                ".$langModule."
              </th>
              <th>
                ".$langDelete."
              </th>
              <th>
                ".$langRename."
              </th>
              <th>
                ".$langComment."
              </th>";
    /*
    echo      "<td>
                ".$langAccess."
              </td>";
    */
    echo      "</tr>\n",
                  "<tbody>\n";

    $sql = "SELECT M.*, count(M.`module_id`) AS timesUsed
               FROM `".$TABLEMODULE."` AS M
          LEFT JOIN `".$TABLELEARNPATHMODULE."` AS LPM
                 ON LPM.`module_id` = M.`module_id`
           WHERE M.`contentType` != \"".CTSCORM_."\"
             AND M.`contentType` != \"".CTLABEL_."\"
           GROUP BY M.`module_id`
           ORDER BY M.`name` ASC, M.`contentType`ASC, M.`accessibility` ASC";

     $result =claro_sql_query($sql);
     $atleastOne = false;

     // Display modules of the pool of this course

     while ($list=mysql_fetch_array($result))
     {
            //DELETE , RENAME, COMMENT

            $contentType_img = selectImage($list['contentType']);
            $contentType_alt = selectAlt($list['contentType']);
            echo "
                 <tr>
                    <td align=\"left\">
                    <img src=\"../img/".$contentType_img."\" alt=\"".$contentType_alt."\" />".$list['name']."
                    </td>
                    <td align='center'>
                     <a href=\"",$PHP_SELF,"?cmd=eraseModule&cmdid=".$list['module_id']."\"
                        onClick=\"return confirmation('",htmlspecialchars(addslashes($list['name'])),$langUsedInLearningPaths,$list['timesUsed'],"');\">
                        <img src=\"../img/delete.gif\" border=\"0\" alt=\"$langAreYouSureDeleteModule\" />
                        </a>
                    </td>
                    <td align=\"center\">
                       <a href=\"",$PHP_SELF,"?cmd=rename&mod_id=".$list['module_id']."\"><img src=\"../img/edit.gif\" border=0 alt=\"$langModify\" /></a>
                    </td>
                    <td align=\"center\">
                       <a href=\"",$PHP_SELF,"?cmd=comment&mod_id=".$list['module_id']."\"><img src=\"../img/comment.gif\" border=0 alt=\"$langModify\" /></a>
                    </td>";
            /*
                    // ACCESSIBILITY

                    echo    "<td align=\"center\">";
                    if ( $list['accessibility'] == 'PRIVATE')
                    {
                        echo    "<a href=\"",$PHP_SELF,"?cmd=mkPublic&mod_id=".$list['module_id']."\">",
                                "<img src=\"../img/private.gif\" border=0 alt='".$langShareWithOtherCourse."' />",
                                "</a>";
                    }
                    else
                    {
                        echo    "<a href=\"",$PHP_SELF,"?cmd=mkPrivate&mod_id=".$list['module_id']."\">",
                                "<img src=\"../img/public.gif\" border=0 alt='".$langStopShare."' />",
                                "</a>";
                    }
                    echo    "</td>\n";
            */
            echo "</tr>";
            /*
            // COMMENT

            if ($list['comment']!=null)
            {
                echo "
                      <tr>
                         <td colspan=\"5\">
                                <small>".$list['comment']."</small>
                         </td>
                      </tr>";
            }
            */

     $atleastOne = true;

     } //end while another module to display

     if ($atleastOne == false) {echo "<tr><td align=\"center\" colspan=\"5\">".$langNoModule."</td></tr>";}

     // Display button to add selected modules

     echo "</tbody>\n</table>";

   // footer

   @include($includePath."/claro_init_footer.inc.php");
?>
