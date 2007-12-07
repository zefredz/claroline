<?php // $Id$
/*
  +----------------------------------------------------------------------+
  | CLAROLINE version 1.5.*
  +----------------------------------------------------------------------+
  | Copyright (c) 2001, 2004 Universite catholique de Louvain (UCL)      |
  +----------------------------------------------------------------------+
  | This source file is subject to the GENERAL PUBLIC LICENSE,           |
  | available through the world-wide-web at                              |
  | http://www.gnu.org/copyleft/gpl.html                                 |
  +----------------------------------------------------------------------+
  | Authors:   Piraux Sébastien <pir@cerdecam.be>                        |
  |            Lederer Guillaume <led@cerdecam.be>                       |
  +----------------------------------------------------------------------+

  DESCRIPTION:
  ****

*/

/*======================================
       CLAROLINE MAIN
  ======================================*/

  $langFile = "learnPath";

  $tlabelReq = 'CLLNP___';
  require '../inc/claro_init_global.inc.php';
  
  // if there is an auth information missing redirect to the first page of lp tool 
  // this page will do the necessary to auth the user, 
  // when leaving a course all the LP sessions infos are cleared so we use this trick to avoid other errors
  if ( ! $_cid) header("Location:./learningPathList.php");
  if ( ! $is_courseAllowed) header("Location:./learningPathList.php");
  
  $htmlHeadXtra[] =
            "<script>
            function confirmation (name)
            {
                if (confirm(\" $langAreYouSureDeleteModule \"+ name + \" ?\"))
                    {return true;}
                else
                    {return false;}
            }
            </script>";

  $interbredcrump[]= array ("url"=>"../learnPath/learningPathList.php", "name"=> $langLearningPathList);
  $interbredcrump[]= array ("url"=>"../learnPath/learningPathAdmin.php", "name"=> $langLearningPathAdmin);
  $nameTools = $langInsertMyModuleToolName;

  //header
  include($includePath."/claro_init_header.inc.php");


  // tables names
  $TABLELEARNPATH         = $_course['dbNameGlu']."lp_learnPath";
  $TABLEMODULE            = $_course['dbNameGlu']."lp_module";
  $TABLELEARNPATHMODULE   = $_course['dbNameGlu']."lp_rel_learnPath_module";
  $TABLEASSET             = $_course['dbNameGlu']."lp_asset";
  $TABLEUSERMODULEPROGRESS= $_course['dbNameGlu']."lp_user_module_progress";


  //lib of this tool
  include($includePath."/lib/learnPath.lib.inc.php");

  //lib of document tool
  include($includePath."/lib/fileDisplay.lib.php");
  
  // $_SESSION
  if ( !isset($_SESSION['path_id']) )
  {
        die ("<center> Not allowed ! (path_id not set :@ )</center>");
  }


/*======================================
       CLAROLINE MAIN
  ======================================*/


   // main page

   $is_AllowedToEdit = $is_courseAdmin;
   if (! $is_AllowedToEdit ) header("Location:./learningPathList.php");



   // FUNCTION NEEDED TO BUILD THE QUERY TO SELECT THE MODULES THAT MUST BE AVAILABLE

   // 1)  We select first the modules that must not be displayed because
   // as they are already in this learning path

   function buildRequestModules()
   {

     global $TABLELEARNPATHMODULE;
     global $TABLEMODULE;
     global $TABLEASSET;

     $firstSql = "SELECT LPM.`module_id`
                    FROM `".$TABLELEARNPATHMODULE."` AS LPM
                   WHERE LPM.`learnPath_id` = ".$_SESSION['path_id'];

     $firstResult = claro_sql_query($firstSql);

     // 2) We build the request to get the modules we need

     $sql = "SELECT M.*, A.`path`
               FROM `".$TABLEMODULE."` AS M
              LEFT JOIN `".$TABLEASSET."` AS A
                ON M.`startAsset_id` = A.`asset_id`
               WHERE M.`contentType` != \"SCORM\"
                 AND M.`contentType` != \"LABEL\"
               ";

     while ($list=mysql_fetch_array($firstResult))
     {
            $sql .=" AND M.`module_id` != ".$list['module_id'];
     }
     //$sql .= " AND M.`contentType` != \"".CTSCORM_."\"";

     /** To find which module must displayed we can also proceed  with only one query.
       * But this implies to use some features of MySQL not available in the version 3.23, so we use
       * two differents queries to get the right list.
       * Here is how to proceed with only one

     $query = "SELECT *
                FROM `".$TABLEMODULE."` AS M
                WHERE NOT EXISTS(SELECT * FROM `".$TABLELEARNPATHMODULE."` AS TLPM
                WHERE TLPM.`module_id` = M.`module_id`)"; */

     return $sql;
   }//end function



   // display title

  claro_disp_tool_title($langInsertMyModulesTitle);



         //COMMAND ADD SELECTED MODULE(S):

   if ($cmdglobal == 'add') {

      // select all 'addable' modules of this course for this learning path

      $result = claro_sql_query(buildRequestModules());
      $atLeastOne="false";
      $nb=0;
      while ($list = mysql_fetch_array($result))
      {
         // see if check box was checked
         if ($_GET['check_'.$list['module_id']]) {

             // find the order place where the module has to be put in the learning path

             $result2 = claro_sql_query("SELECT MAX(`rank`)
                                       FROM `".$TABLELEARNPATHMODULE."`
                                      WHERE learnPath_id = ".$_SESSION['path_id']);
             list($orderMax) = mysql_fetch_row($result2);
             $order = $orderMax + 1;

             //create and call the insertquery on the DB to add the checked module to the learning path

             $insertquery="INSERT
                             INTO `".$TABLELEARNPATHMODULE."`
                                  (`learnPath_id`, `module_id`, `specificComment`, `rank`, `lock` )
                           VALUES ";
             $insertquery.="(".$_SESSION['path_id'].", ".$list['module_id'].", '',".$order.", 'OPEN')";


             claro_sql_query($insertquery);
             $atleastOne="true";
             $nb++;
             }
         }
     if ($atleastOne=="false") {echo "<font color=\"red\">No module selected !!!</font>";}
   } //end if ADD command

     //STEP ONE : display form to add module of the course that are not in this path yet
     // this is the same SELECT as "select all 'addable' modules of this course for this learning path"
     // **BUT** normally there is less 'addable' modules here than in the first one
     $result = claro_sql_query(buildRequestModules());

     echo "<table class=\"claroTable\" width=\"100%\" border=\"0\" cellspacing=\"2\">
            <tr class=\"headerX\" bgcolor=\"#E6E6E6\" align=\"center\" valign=\"top\">
              <th width=\"10%\">
                ".$langAddModule."
              </th>
              <th>
                ".$langModule."
              </th>
            </tr>\n<tbody>\n";

     // Display available modules
     echo "<form name=\"addmodule\" action=\"",$PHP_SELF,"?cmdglobal=add\">";
     $atleastOne="false";
     while ($list=mysql_fetch_array($result))
     {

            //CHECKBOX, NAME, RENAME, COMMENT
            if($list['contentType'] == CTEXERCISE_ ) 
              $moduleImg = "quiz.gif";
            else
              $moduleImg = choose_image(basename($list['path']));
            $contentType_alt = selectAlt($list['contentType']);
            echo "
                 <tr>
                    <td align=\"center\">
                       <input type=\"checkbox\" name=\"check_".$list['module_id']."\"  id=\"check_".$list['module_id']."\">
                    </td>

                    <td align=\"left\">
                       <label for=\"check_".$list['module_id']."\" ><img src=\"".$clarolineRepositoryWeb."img/".$moduleImg."\" alt=\"".$contentType_alt."\" />".$list['name']."</label>
                    </td>";

            // COMMENT

            if ($list['comment'] != null) {
                echo "
                      <tr>
                         <td>&nbsp;</td>
                         <td>
                                <small>".$list['comment']."</small>
                         </td>
                      </tr>";
                }
            $atleastOne="true";
            }//end while another module to display
      echo "</tbody>\n<tfoot>";
      if ($atleastOne == "false") {echo "<tr><td colspan=\"6\" align=\"center\">".$langNoMoreModuleToAdd."</td></tr>";}

     // Display button to add selected modules

     echo "<tr><td colspan=\"2\"><hr noshade size=\"1\" /></td></tr>";
     if ($atleastOne=="true") {
         echo "<tr><td colspan=\"2\"><input type=\"submit\" value=\"".$langAddModulesButton."\" /></td></tr>
               <input type=\"hidden\" name=\"cmdglobal\" value=\"add\">";
         }
     echo "</tfoot>\n</form>\n</table>";

     //####################################################################################\\
   //################################## MODULES LIST ####################################\\
   //####################################################################################\\

 // display subtitle
 claro_disp_tool_title($langPathContentTitle);
 // display back link to return to the LP administration
 echo '<a href="learningPathAdmin.php">&lt;&lt;&nbsp;'.$langBackToLPAdmin.'</a>';
 // display list of modules used by this learning path
 display_path_content($param_array, $table);

   // footer

   include($includePath."/claro_init_footer.inc.php");
?>
