<?php // $Id$
/*
+----------------------------------------------------------------------+
| CLAROLINE 1.6
+----------------------------------------------------------------------+
| Copyright (c) 2001, 2004 Universite catholique de Louvain (UCL)      |
+----------------------------------------------------------------------+
*/

/*=====================================================================
  Init Section
 =====================================================================*/

require '../inc/claro_init_global.inc.php';

/*---------------------------------------------------------------------
  Security Check
 ---------------------------------------------------------------------*/

if ( ! $_cid ) claro_disp_select_course();

$is_allowedToEdit = $is_courseAdmin || $is_platformAdmin;

if ( ! $is_allowedToEdit )
{
    include($includePath . '/claro_init_header.inc.php');
    claro_disp_message_box($langNotAllowed);
    include($includePath . '/claro_init_footer.inc.php');
    die();
}

/*---------------------------------------------------------------------
  CONSTANT DEFINITION FOR DIPLAY SWITCH
 ---------------------------------------------------------------------*/

define ('DISP_FORUM_GO'      ,1);
define ('DISP_FORUM_GO_EDIT' ,2);
define ('DISP_FORUM_CAT_EDIT',3);
define ('DISP_FORUM_CAT_SAVE',4);
define ('DISP_FORUM_GO_SAVE' ,5);
define ('DISP_FORUM_CAT_ADD' ,6);
define ('DISP_FORUM_GO_ADD'  ,7);
define ('DISP_FORUM_CAT_DEL' ,8);
define ('DISP_FORUM_GO_DEL'  ,9);
define ('DISP_FORUM_ADMIN'   ,10);
define ('DISP_NO_WAY'        ,11);

/*---------------------------------------------------------------------
  CONSTANT DEFINING THE FORUM CATEGORY RESERVED FOR CLAROLINE GROUPS
 ---------------------------------------------------------------------*/

define("CAT_FOR_GROUPS",1);

/*---------------------------------------------------------------------
  DB tables definition
 ---------------------------------------------------------------------*/

$tbl_cdb_names = claro_sql_get_course_tbl();
$tbl_forum_categories = $tbl_cdb_names['bb_categories'         ];
$tbl_forum_forums     = $tbl_cdb_names['bb_forums'             ];
$tbl_forum_topics     = $tbl_cdb_names['bb_topics'             ];

/*=====================================================================
  Statements Section
 =====================================================================*/

switch ( $_REQUEST['cmd'] )
{
    case "exMovedown" :
         $ThisForumId = (int) $_REQUEST['moveForumId'];
         $sortDirection = "ASC";
         break;
    case "exMoveup" :
         $ThisForumId = (int) $_REQUEST['moveForumId'];
         $sortDirection = "DESC";
    case "exMovedownCat" :
         $ThisCatId = (int) $_REQUEST['moveCatId'];
         $sortDirectionCat = "ASC";
         break;
    case "exMoveupCat" :
         $ThisCatId = (int) $_REQUEST['moveCatId'];
         $sortDirectionCat = "DESC";
         break;
    default:
         break;
}

/*---------------------------------------------------------------------
 re-order forum
 ---------------------------------------------------------------------*/

if ( isset($sortDirection) )
{
    $sql = 'SELECT f.`forum_id`, f.`forum_order` 
            FROM `'.$tbl_forum_forums.'` f
            WHERE f.`cat_id` = "'.$moveCat.'"
            ORDER BY f.`forum_order` '.$sortDirection;
    $result = claro_sql_query($sql);
        
    while ( list($ForumId, $ForumOrderInCat) = mysql_fetch_row($result) )
    {
        // STEP 2 : FOUND THE NEXT FORUM ID AND ORDER.
        //          COMMIT ORDER SWAP ON THE DB

        if ( isset($ThisForumOrderFound) && ($ThisForumOrderFound == true) )
        {
            $nextForumId = $ForumId;
            $nextForumOrder = $ForumOrderInCat;

            $sql = 'UPDATE `'.$tbl_forum_forums.'`
                    SET `forum_order` = "'.$nextForumOrder.'"
                    WHERE `forum_id` =  "'.$ThisForumId.'"';
            claro_sql_query($sql);

            $sql = 'UPDATE `'.$tbl_forum_forums.'`
                    SET `forum_order` = "'.$ThisForumOrder.'"
                    WHERE `forum_id` =  "'.$nextForumId.'"';
            claro_sql_query($sql);

            break;
        }

        // STEP 1 : FIND THE ORDER OF THE FORUM

        if ( $ForumId==$ThisForumId )
        {
            $ThisForumOrder = $ForumOrderInCat;
            $ThisForumOrderFound = true;
        }
    }
}

/*---------------------------------------------------------------------
 re-order categories
 ---------------------------------------------------------------------*/

if ( isset($sortDirectionCat) )
{

    $sql = 'SELECT c.`cat_id`, c.`cat_order` FROM `'.$tbl_forum_categories.'` c
            ORDER BY c.`cat_order` '.$sortDirectionCat;

    $result = claro_sql_query($sql);

    while ( list($CatId,$CatOrderInCatList) = mysql_fetch_row($result) )
    {

        // STEP 2 : FOUND THE NEXT CAT ID AND ORDER.
        //          COMMIT ORDER SWAP ON THE DB

        if ( isset($ThisCatOrderFound) && ($ThisCatOrderFound == true) )
        {
            $nextCatId = $CatId;
            $nextCatOrder = $CatOrderInCatList;
            $sql = 'UPDATE `'.$tbl_forum_categories.'`
                    SET `cat_order` = "'.$nextCatOrder.'"
                    WHERE `cat_id` = "'.$ThisCatId.'"';
            claro_sql_query($sql);
            $sql = 'UPDATE `'.$tbl_forum_categories.'`
                    SET `cat_order` = "'.$ThisCatOrder.'"
                    WHERE `cat_id` =  "'.$nextCatId.'"';
            claro_sql_query($sql);
            break;
        }

        // STEP 1 : FIND THE ORDER OF THE CAT

        if ($CatId==$ThisCatId)
        {
            $ThisCatOrder = $CatOrderInCatList;
            $ThisCatOrderFound = true;
        }
    }

} // end execute command

/*---------------------------------------------------------------------
  GO TO FORUMS LIST OF THIS CATEGORY
 ---------------------------------------------------------------------*/

if ( isset($_REQUEST['cat_id']) ) 
{
    $sql = "SELECT cat_title
             FROM `". $tbl_forum_categories . "`
             WHERE cat_id='". $_REQUEST['cat_id'] ."'";

    $result = claro_sql_query($sql);
    
    if (mysql_num_rows($result))
    {
        $row = mysql_fetch_array($result);
        $category_name = $row['cat_title'];
    }
    else
    {
        $category_name = $langEmpty; 
    }

}

if ( isset($_REQUEST['forumgo']) )
{
    $display  = DISP_FORUM_GO;
    $subTitle = $langForCat." ' ".$category_name." ' ";
    $sql = "SELECT forum_id id,
                   forum_name name,
                   forum_access access,
                   forum_moderator moderator, 
                   forum_type type,
                   forum_desc
             FROM `".$tbl_forum_forums."`
             WHERE cat_id='".$cat_id."'
             ORDER BY forum_order";

    $result = claro_sql_query($sql);
    $nbForumsInCat = mysql_num_rows($result);
    $forumList = array();
    while ( $row = mysql_fetch_array($result)) $forumList[] = $row;
    
    if ($cat_id != CAT_FOR_GROUPS) $show_formToAddAForum = true;
}

/*---------------------------------------------------------------------
      EDIT FORUM NAME
 ---------------------------------------------------------------------*/

elseif ( isset($_REQUEST['forumgoedit']) )
{
    $display = DISP_FORUM_GO_EDIT;

    $sql = "SELECT forum_id, forum_name, forum_desc, forum_access,
                   forum_moderator, cat_id, forum_type
            FROM `".$tbl_forum_forums."`
            WHERE forum_id = '".$forum_id."'";
    $result = claro_sql_query($sql);

    list($forum_id, $forum_name, $forum_desc, $forum_access,
         $forum_moderator, $current_cat_id, $forum_type)= mysql_fetch_row($result);

    $subTitle = $langModify." ' ".$forum_name." ' ";

    if ($current_cat_id==CAT_FOR_GROUPS)
    {
        $is_allowedToMoveForum = false;
    }
    else
    {
        $is_allowedToMoveForum = true;

        $sql = "SELECT cat_id, cat_title
                FROM `".$tbl_forum_categories."`";
        $result = claro_sql_query($sql);

        while(list($cat_id, $cat_title) = mysql_fetch_row($result))
        {
            if($cat_id != CAT_FOR_GROUPS)
            {
                $output_option_list .= "\n\t\t<option value=\"".$cat_id."\" "
                                    .($cat_id ==    $current_cat_id    ? "selected":"")
                                    .">\n\t\t\t"
                                    .$cat_title
                                    ."\n\t\t</option>";

                $targetCategoryList[] =
                array('id'      =>  $cat_id,
                      'title'   =>  $cat_title,
                      'current' => ($cat_id == $current_cat_id    ? true : false)
                     );
            }
        }
    }
}

/*---------------------------------------------------------------------
    FORUM CATEGORY EDIT
 ---------------------------------------------------------------------*/

elseif($forumcatedit)
{
    $display  = DISP_FORUM_CAT_EDIT;
    $subTitle = $langModCatName;
    $result   = claro_sql_query("SELECT cat_id, cat_title
                             FROM `".$tbl_forum_categories."`
                             WHERE cat_id = '".$cat_id."'");
    list($cat_id, $cat_title) = mysql_fetch_row($result);

}

/*---------------------------------------------------------------------
     FORUM CATEGORY SAVE
 ---------------------------------------------------------------------*/

elseif ($forumcatsave)
{
    $display = DISP_FORUM_CAT_SAVE;
    if ($cat_title != "")
    {
        $sql = "UPDATE `".$tbl_forum_categories."`
                SET   cat_title = '".$cat_title."'
                WHERE cat_id    = '".$cat_id."'";
        claro_sql_query($sql);
    }
    else
    {
        $display_error_mess = TRUE;
    }
}

/*---------------------------------------------------------------------
  SAVE FORUM NAME & DESCRIPTION
 ---------------------------------------------------------------------*/

elseif($forumgosave)
{
    $display = DISP_FORUM_GO_SAVE;
    if($forum_name != "")
    {
        $sql = 'UPDATE `'.$tbl_forum_forums.'`
                SET `forum_name`     = "'.$forum_name.'",
                    `forum_desc`     = "'.$forum_desc.'",
                    `forum_access`   = "2",
                    `forum_moderator`= "1",
                    `cat_id`         = "'.$cat_id.'",
                    `forum_type`     = "'.$forum_type.'"
                WHERE `forum_id` = "'.$forum_id.'"';
        claro_sql_query($sql);
    }
    else
    {
        $display_error_mess = TRUE;
    }

}

/*---------------------------------------------------------------------
     FORUM ADD CATEGORY
 ---------------------------------------------------------------------*/

elseif($forumcatadd)
{
    $display=DISP_FORUM_CAT_ADD;

    /*
         We have to absolutely reserved a specific cat_id for groups. Otherwise,
         group doesn't work correctly. Usually this forum category is created at
         course creation. But to be sure, we force its creation before // any
         other new category creation.
         The cat_id number is stored into the CAT_FOR_GROUPS constant
     */

    if ($catagories!="")
    {
        // find order in the category we must give to the newly created forum
        $sql = 'SELECT MAX(`cat_order`) FROM `'.$tbl_forum_categories.'`';
        $result = claro_sql_query($sql);

        list($orderMax) = mysql_fetch_row($result);
        $order = $orderMax + 1;

    /*  not useful patch for 1.4.2 to 1.5 see Hugues...

        claro_sql_query("INSERT    IGNORE INTO `".$tbl_forum_categories."`
                 SET cat_title = \"groups\",
                 cat_id = '".CAT_FOR_GROUPS."',
                 cat_order = 0
                 ");
    */
        $sql = 'INSERT INTO `'.$tbl_forum_categories.'`
                SET `cat_title` = "'.$catagories.'",
                    `cat_order` = "'.$order.'"';
        claro_sql_query($sql);
    }
    else
    {
        $display_error_mess = TRUE;
    }
}

/*---------------------------------------------------------------------
          Forum Go Add
 ---------------------------------------------------------------------*/

elseif ( isset($_REQUEST['forumgoadd']) )
{
    $display=DISP_FORUM_GO_ADD;

    if ($forum_name !="") //do not add forum if empty name given
    {
        // find order in the category we must give to the newly created forum

        $sql = 'SELECT MAX(`forum_order`)
                FROM `'.$tbl_forum_forums.'`
                WHERE cat_id = "'.$cat_id.'"';
        $result = claro_sql_query($sql);

        list($orderMax) = mysql_fetch_row($result);
        $order = $orderMax + 1;

        // add new forum in DB

        $sql = 'INSERT INTO `'.$tbl_forum_forums.'`
                (forum_id, forum_name, forum_desc, forum_access,forum_moderator, cat_id, forum_type, md5, forum_order)
                VALUES
                (NULL,"'.$forum_name.'", "'.$forum_desc.'", "2", "1", "'.$cat_id.'", "'.$forum_type.'", "'.md5(time()).'", "'.$order.'")';
        claro_sql_query($sql);
    }
    else
    {
        $display_error_mess = true;
    }
}

/*---------------------------------------------------------------------
    FORUM DELETE CATEGORY
 ---------------------------------------------------------------------*/

elseif ( isset($_REQUEST['forumcatdel']) )
{
    $display = DISP_FORUM_CAT_DEL;

    if ($cat_id!=CAT_FOR_GROUPS)
    {
        $sql = 'SELECT `forum_id` 
                FROM `'.$tbl_forum_forums.'` 
                WHERE `cat_id` = "'.$cat_id.'"';
        $result = claro_sql_query($sql);

        while(list($forum_id) = mysql_fetch_row($result))
        {
            $sql = 'DELETE FROM `'.$tbl_forum_topics.'` 
                    WHERE `forum_id` = "'.$forum_id.'"';
            claro_sql_query($sql);
        }
        $sql = 'DELETE FROM `'.$tbl_forum_forums.'` 
                WHERE `cat_id` = "'.$cat_id.'"';
        claro_sql_query($sql);

        $sql = 'DELETE FROM `'.$tbl_forum_categories.'` 
                WHERE `cat_id` = "'.$cat_id.'"';
        claro_sql_query($sql);

        $msg_can_del_cat_1 = $langForumCategoryDeleted;
    }
    else
    {
        $msg_can_del_cat_1 = $langYouCannotDelCatOfGroupsForums;
    }
}

/*---------------------------------------------------------------------
       FORUM GO DEL
 ---------------------------------------------------------------------*/

elseif ( isset($_REQUEST['forumgodel']) )
{
    $display=DISP_FORUM_GO_DEL;

	$sql = 'DELETE FROM `'.$tbl_forum_topics.'` 
            WHERE `forum_id` = "'.$forum_id.'"';
    claro_sql_query($sql);

	$sql = 'DELETE FROM `'.$tbl_forum_forums.'` 
            WHERE `forum_id` = "'.$forum_id.'"';
    claro_sql_query($sql);

}

/*---------------------------------------------------------------------
    DEFAULT
 ---------------------------------------------------------------------*/

else
{
    $display  = DISP_FORUM_ADMIN;
    $subTitle = $langForCategories;

    $sql = 'SELECT c.`cat_id` AS `id`, c.`cat_title` AS `title`, 
                   COUNT(f.`forum_id`) AS `nb_forum` 
                   FROM `'.$tbl_forum_categories.'` c
                   LEFT JOIN `'.$tbl_forum_forums.'` f 
                   ON f.`cat_id` = c.`cat_id` 
                   GROUP BY c.`cat_id`
                   ORDER BY c.`cat_order`';
    $result = claro_sql_query($sql);
    $nbOfCat = mysql_num_rows($result);

    $categoryList = array();
    while ($row = mysql_fetch_array($result)) $categoryList[] = $row;
    
} // end else ... if forum_go

/*=====================================================================
  Display Section
 =====================================================================*/

$nameTools = $langOrganisation;
$interbredcrump[] = array ("url"=>"../phpbb/index.php", "name"=> $langForums);

$htmlHeadXtra[] =
"<script>
function confirmation (message)
{
    if (confirm(message))
        {return true;}
    else
        {return false;}
}
</script>";

/*---------------------------------------------------------------------
    Display Header
 ---------------------------------------------------------------------*/

include('../inc/claro_init_header.inc.php');

/*---------------------------------------------------------------------
    Display Title
 ---------------------------------------------------------------------*/

claro_disp_tool_title(
    array(
        'mainTitle'=>$nameTools,
        'subTitle'=>$subTitle
        )
    , 'help_forum.php'
    );

/*---------------------------------------------------------------------
    Display Message   
 ---------------------------------------------------------------------*/

if ( !empty($controlMsg) )
{
    claro_disp_msg_arr($controlMsg);
}

/*---------------------------------------------------------------------
 Display statement
 ---------------------------------------------------------------------*/

switch ($display)
{
   /*---------------------------------------------------------------------
     Display forum
   ---------------------------------------------------------------------*/

   case DISP_FORUM_GO : 

        echo  "<p><small>"."<a href=\"".$_SERVER['PHP_SELF']."?forumadmin=yes\"><< " . $langBackCat . "</a>"."</small></p>"
            ."<form action=\"forum_admin.php\" method=post>"
            ."<input type='hidden' name='forumgoadd' value='yes'>"
            ."<input type='hidden' name='cat_id'     value='".$cat_id."'>"
            ."<table border=0 cellpadding=4 cellspacing=2 class=\"claroTable\">"
    
            ."<tr class=\"headerX\">\n"
            ."<th>".$langForName."</th>\n"
            ."<th>".$langDescription."</th>\n"
            ."<th align=\"center\">".$langModify."</th>\n"
            ."<th align=\"center\">".$langDelete."</th>\n"
            ."<th colspan=\"2\">".$langOrder."</th>"
            ."</tr>";

        if ( count($forumList) > 0 )
        {
           $iteratorInCat=1;
           foreach ( $forumList as $thisForum )
           {
               echo    "<tr>\n".
                   "<td valign=top>".$thisForum['name']."</td>\n".
                   "<td valign=top>".
                   ( empty($thisForum['forum_desc'])    ? '<center>    - </center>' : $thisForum['forum_desc']).
                   "</td>\n".
                   "<td valign=top    align=\"center\">\n".
                   "<a    href=forum_admin.php".
                   "?forumgoedit=yes&amp;forum_id=".$thisForum['id']."&amp;cat_id=".$cat_id.">".
                   "<img src=\"".$imgRepositoryWeb."edit.gif\" alt=\"".$langModify."\" border=\"0\">".
                   "</a>".
                   "</td>\n".
                   "<td align=\"center\">".
                   ($cat_id ==    CAT_FOR_GROUPS ?
                   "<small><i>".$langCannotBeDeleted."</i></small>"
                   :
                   "<a    href=\"forum_admin.php?forumgodel=yes&amp;forum_id=".$thisForum['id']."&amp;cat_id=".$cat_id."&amp;ok=0\"    onclick=\"return confirmation('".clean_str_for_javascript($langAreYouSureToDelete .' \'' .$thisForum['name'].'\'    ?')."');\">".
                   "<img src=\"".$imgRepositoryWeb."delete.gif\"    alt=\"".$langDelete."\"    border=\"0\">".
                   "</a>").

                   "</td>\n";

               ///display re-order links added for claroline 1.5

               if ( $iteratorInCat==$nbForumsInCat )
               {
                   echo "<td></td>";
               }
               else
               {
                   echo "<td align=\"center\"><a href=\"forum_admin.php?cmd=exMovedown&amp;moveForumId=".$thisForum['id']."&amp;moveCat=".$cat_id."&amp;cat_id=".$cat_id."&amp;forumgo=yes\">
                   <img src=\"".$imgRepositoryWeb."down.gif\"></a>
               </td>";
               }

               if ( $iteratorInCat>1 )
               {
                   echo "<td align=\"center\"><a href=\"forum_admin.php?cmd=exMoveup&amp;moveForumId=".$thisForum['id']."&amp;moveCat=".$cat_id."&amp;cat_id=".$cat_id."&amp;forumgo=yes\">
                   <img src=\"".$imgRepositoryWeb."up.gif\"></a>
               </td>";
               }
               else
               {
                   echo "<td></td>";
               }
               $iteratorInCat++;

               echo "</tr>\n";
           } // end foreach forumList
        } // end if    count forumList    > 0

        echo "</table>";

        if ( isset($show_formToAddAForum) && $show_formToAddAForum )
        {
            echo "<p><b>",$langAddForCat," ",$category_name,"</b></p>",
                "<form action=\"forum_admin.php?forumgoadd=yes&amp;cat_id=$cat_id\" method=post>\n",

                "<input type=hidden name=cat_id value=\"$cat_id\">\n",
                "<input type=hidden name=forumgoadd value=yes>\n",

                "<table border=0>\n",
                "<tr  valign=\"top\">\n",
                "<td align=\"right\"><label for=\"forum_name\">",$langForName," : </label></td>\n",
                "<td><input type=text name=\"forum_name\" id=\"forum_name\" size=\"40\"></td>\n",
                "</tr>\n",

                "<tr  valign=\"top\">\n",
                "<td align=\"right\"><label for=\"forum_desc\">",$langDescription," : </label></td>\n",
                "<td><textarea name=\"forum_desc\" id=\"forum_desc\" cols=\"40\" rows=\"3\"></textarea></td>\n",
                "</tr>\n",

                "<tr>\n",
                "<td>\n",
                "</td>\n",
                "<td>\n",
                "<input type=submit value=\"",$langAdd,"\">\n",
                "</td>\n",
                "</table>\n",
                "</form>\n";
        }
        else
        {
            echo "<p>".$langCannotAddForumInGroups."</p>";
        }
        
        break;

    /*---------------------------------------------------------------------
      Display Edit Forum
    ---------------------------------------------------------------------*/

    case DISP_FORUM_GO_EDIT:

        echo    "<form action=\"forum_admin.php?forumgosave=yes&amp;cat_id=$cat_id\" method=post>\n",
                "<input    type=hidden    name=forum_id value=$forum_id>\n",
    
                "<table    border=\"0\">\n",

                "<tr>\n",
                "<td align=\"right\"><label for=\"forum_name\">",$langForName," :    </label></td>\n",
                "<td><input    type=text name=\"forum_name\" id=\"forum_name\" size=\"50\" value=\"$forum_name\"></td>\n",
                "</tr>\n",

                "<tr valign=\"top\">\n",
                "<td align=\"right\"><label for=\"forum_desc\">",$langDescription," : </label></td>\n",
                "<td><textarea name=\"forum_desc\" id=\"forum_desc\" cols=\"50\" rows=\"3\">",$forum_desc,"</textarea></td>\n",
                "</tr>\n",

                "<tr>\n",
                "<td align=\"right\"><label for=\"cat_id\">",$langChangeCat,"    : </label></td>\n",
                "<td>";

        if ( $is_allowedToMoveForum )
        {
            echo "<select name=\"cat_id\" id==\"cat_id\">\n";

            foreach($targetCategoryList as $thisTargetCategory)
            {
                echo "<option value=\"".$thisTargetCategory['id']."\" "
                    .($thisTargetCategory['current'] ? 'selected' : '').">"
                    .$thisTargetCategory['title']
                    ."</option>\n";
            }
                
            echo "</select>\n";
        }
        else
        {    
            echo "<i>".$langCannotMoveGroupForum."</i>";
        }
    
        echo    "</td>\n",
                "</tr>\n",
                "<tr valign=\"top\">\n",
                "<td>\n",
                "</td>\n",
                "<td>\n",
                "<input    type=submit    value=\"$langSave\">\n",
                "</td>",
                "</tr>\n",
                "</table>\n",
                "<input    type=hidden    name=forumgosave value=yes>\n",
                "</form>\n";

        break;

    /*---------------------------------------------------------------------
        Display Edit Forum Category
     ---------------------------------------------------------------------*/

    case DISP_FORUM_CAT_EDIT:

        echo "<form action=\"forum_admin.php?forumcatsave=yes\" method=post>
            <input type=hidden name=cat_id value=".$cat_id.">",
            "<label for=\"cat_title\">".$langCategory." : </label>".
            "<input type=\"text\" name=\"cat_title\" id=\"cat_title\" size=\"55\" value=\"",$cat_title,"\">\n",
            "<input type=submit value=\"",$langSave,"\">\n",
            "</form>";
        break;

    /*---------------------------------------------------------------------
        Display Save Category
     ---------------------------------------------------------------------*/

    case DISP_FORUM_CAT_SAVE:

        if ($display_error_mess)
        {
            claro_disp_message_box($langemptycatname);
            echo "<p><a href=\"".$_SERVER['PHP_SELF']."?forumcatedit=yes&amp;cat_id=".$cat_id."\">$langBack</a></p>";
        }
        else
        {
            claro_disp_message_box($langNameCat);
            echo "<p><a href=\"".$_SERVER['PHP_SELF']."?forumadmin=yes\">$langBack</a></p>";
        }
        break;

    /*---------------------------------------------------------------------
        Display Save Forum
     ---------------------------------------------------------------------*/

    case DISP_FORUM_GO_SAVE:

        if ($display_error_mess)
        {
           claro_disp_message_box($langemptyforumname);
           echo "<p><a href=\"".$_SERVER['PHP_SELF']."?forumgoedit=yes&amp;forum_id=" . $forum_id . "&amp;cat_id=" . $cat_id ."\">".$langBack."</a></p>";
        }
        else
        {
            claro_disp_message_box($langForumModified);
            echo "<p><a href=\"".$_SERVER['PHP_SELF']."?forumgo=yes&amp;cat_id=" . $cat_id . "\">".$langBack."</a></p>";
        }
        break;

    /*---------------------------------------------------------------------
        Display Category Added
     ---------------------------------------------------------------------*/

    case DISP_FORUM_CAT_ADD:

        if ($display_error_mess)
        {
            claro_disp_message_box($langemptycatname);
        }
        else
        {
            claro_disp_message_box($langcatcreated);
        }
        echo "<p><a href=\"".$_SERVER['PHP_SELF']."?forumadmin=yes\">".$langBack."</a></p>";
        break;

    /*---------------------------------------------------------------------
        Display Forum Added
     ---------------------------------------------------------------------*/

    case DISP_FORUM_GO_ADD:

        if ($display_error_mess)
        {
            claro_disp_message_box($langemptyforumname);
        }
        else
        {
            claro_disp_message_box($langforumcreated);
        }
        echo "<p><a href=\"".$_SERVER['PHP_SELF']."?forumgo=yes&amp;cat_id=" . $cat_id . "\">" . $langBack . "</a></p>\n";
        break;

    /*---------------------------------------------------------------------
        Display Category deleted
     ---------------------------------------------------------------------*/

    case DISP_FORUM_CAT_DEL:

        claro_disp_message_box($msg_can_del_cat_1);
        echo "<p><a href=\"".$_SERVER['PHP_SELF']."?forumadmin=yes\">$langBack</a></p>";
        break;

    /*---------------------------------------------------------------------
        Display Forum deleted
     ---------------------------------------------------------------------*/

    case DISP_FORUM_GO_DEL:
        
        claro_disp_message_box($langForumDeleted);
        echo "<p><a href=\"".$_SERVER['PHP_SELF']."?forumgo=yes&amp;cat_id=$cat_id\">",$langBack,"</a></p>";
        break;

    /*---------------------------------------------------------------------
        Display default
     ---------------------------------------------------------------------*/

    case DISP_FORUM_ADMIN:
    default:

        echo "<p>",$langAddForums,
            "<form action=\"forum_admin.php?forumadmin=yes\" method=\"post\">\n",
            "<table border=\"0\" cellspacing=\"2\" cellpadding=\"4\" class=\"claroTable\">\n",
            "<tr class=\"headerX\">",
            "<th>",$langCategories,"</th>",
            "<th align=\"center\">",$langModify,"</th>",
            "<th align=\"center\">",$langDelete,"</th>",
            "<th colspan=\"2\">".$langOrder."</th>",
            "</tr>\n";
    
        if ( count($categoryList) > 0 )
        {
            $iteratorInCat = 1;
            foreach($categoryList as $thisCategory)
            {
                echo "<tr>"
    
                     ."<td>"
                     ."<a href=\"forum_admin.php"
                     ."?forumgo=yes&amp;cat_id=".$thisCategory['id']."\">"
                     .$thisCategory['title']
                     ."</a>"
                     ." <small>(".$thisCategory['nb_forum'].")</small>"
                     ."</td>"
    
                     ."<td align=\"center\">"
                     ."<a href=\"forum_admin.php?forumcatedit=yes&amp;cat_id=".$thisCategory['id']."\">"
                     ."<img src=\"".$imgRepositoryWeb."edit.gif\" alt=\"".$langModify."\" border=\"0\">"
                     ."</a>"
                     ."</td>\n"
    
                     ."<td align=\"center\">";
    
                if ($thisCategory['id'] != CAT_FOR_GROUPS)
                {
                    echo "<a href=\"forum_admin.php?"
                        ."forumcatdel=yes&amp;cat_id=".$thisCategory['id']."&amp;ok=0\" "
                        ."onclick=\"return confirmation('".clean_str_for_javascript($langAreYouSureToDelete .' \'' .$thisCategory['title'].'\' ?')."');\">".
                        "<img src=\"".$imgRepositoryWeb."delete.gif\" alt=\"".$langDelete."\" border=\"0\">".
                        "</a>";
                }
                else
                {
                    echo "<small><i>".$langCannotBeDeleted."</i></small>";
                }
    
                echo  "</td>";
    
                ///display re-order links added for claroline 1.5
    
                if ($iteratorInCat==$nbOfCat)
                {
                    echo "<td></td>";
                }
                else
                {
                    echo "<td align=\"center\"><a href=\"forum_admin.php?cmd=exMovedownCat&amp;moveCatId=".$thisCategory['id']."\">
                            <img src=\"".$imgRepositoryWeb."down.gif\"></a>
                        </td>";
                }

                if ( $iteratorInCat > 1 )
                {
                    echo "<td align=\"center\"><a href=\"forum_admin.php?cmd=exMoveupCat&amp;moveCatId=".$thisCategory['id']."\">
                            <img src=\"".$imgRepositoryWeb."up.gif\"></a>
                        </td>";
                }
                else
                {
                    echo "<td></td>";
                }
                $iteratorInCat++;
    
                echo  "</tr>\n";
    
            } // end foreach $categoryList

        } // end if count $categoryList
        
        echo "</table>"
            ."</form>"
    
            ."<h4>".$langAddCategory."</h4>"
            ."<form action=\"forum_admin.php\" method=\"post\">\n"
            ."<label for=\"catagories\">".$langCategory." : </label>"
            ."<input type=\"text\" name=\"catagories\" id=\"catagories\" size=\"50\">\n"
            ."<input type=\"submit\" value=\"".$langAdd."\">\n"
            ."<input type=\"hidden\" name=\"forumcatadd\" value=\"yes\">\n"
            ."</form>\n";

        break;
}

/*---------------------------------------------------------------------
    Display footer
 ---------------------------------------------------------------------*/

include($includePath."/claro_init_footer.inc.php");
?>
