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

claro_unquote_gpc();

$nameTools = $langOrganisation;
$subTitle = '';

$display_error_mess = FALSE;

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

define ('DISP_FORUM_GO'      ,__LINE__);
define ('DISP_FORUM_GO_EDIT' ,__LINE__);
define ('DISP_FORUM_CAT_EDIT',__LINE__);
define ('DISP_FORUM_CAT_SAVE',__LINE__);
define ('DISP_FORUM_GO_SAVE' ,__LINE__);
define ('DISP_FORUM_CAT_ADD' ,__LINE__);
define ('DISP_FORUM_GO_ADD'  ,__LINE__);
define ('DISP_FORUM_CAT_DEL' ,__LINE__);
define ('DISP_FORUM_GO_DEL'  ,__LINE__);
define ('DISP_FORUM_ADMIN'   ,__LINE__);
define ('DISP_NO_WAY'        ,__LINE__);

/*---------------------------------------------------------------------
  CONSTANT DEFINING THE FORUM CATEGORY RESERVED FOR CLAROLINE GROUPS
 ---------------------------------------------------------------------*/

define('CAT_FOR_GROUPS',1);

/*---------------------------------------------------------------------
  DB tables definition
 ---------------------------------------------------------------------*/

$tbl_cdb_names = claro_sql_get_course_tbl();
$tbl_forum_categories = $tbl_cdb_names['bb_categories'         ];
$tbl_forum_forums     = $tbl_cdb_names['bb_forums'             ];
$tbl_forum_topics     = $tbl_cdb_names['bb_topics'             ];


/*---------------------------------------------------------------------
  Get form params
 ---------------------------------------------------------------------*/

if ( isset($_REQUEST['cat_id']) ) $cat_id = (int) $_REQUEST['cat_id'];
else                              $cat_id = 0;

if ( isset($_REQUEST['cat_title']) ) $cat_title = $_REQUEST['cat_title'];
else				                 $cat_title = '';

if ( isset($_REQUEST['forum_id']) ) $forum_id = (int) $_REQUEST['forum_id'];
else                                $forum_id = 0;

if ( isset($_REQUEST['forum_name']) ) $forum_name = $_REQUEST['forum_name'];
else                                  $forum_name = 0;
    
if ( isset($_REQUEST['forum_desc']) ) $forum_desc = $_REQUEST['forum_desc'];
else                                  $forum_desc = '';
	
if ( isset($_REQUEST['forum_type']) ) $forum_type = (int) $_REQUEST['forum_type']; // not used
else                                  $forum_type = 0;

if ( isset($_REQUEST['cmd']) ) $cmd = $_REQUEST['cmd'];
else                           $cmd = '';

/*=====================================================================
  Main Section
 =====================================================================*/


/*---------------------------------------------------------------------
 re-order forum
 ---------------------------------------------------------------------*/

if ( !empty($cat_id) && !empty($forum_id) && ( $cmd == 'exMovedown' || $cmd == 'exMoveup' ) )
{
    $order = get_forum_rank($forum_id);

    if ( $cmd == 'exMoveup')
    {
        move_up_forum($forum_id, $cat_id);
    }

    if ( $cmd == 'exMovedown' )
    {
        move_down_forum($forum_id, $cat_id);
    }
}

/*---------------------------------------------------------------------
 re-order categories
 ---------------------------------------------------------------------*/

if ( !empty($cat_id) && $cmd == 'exMovedownCat' || $cmd == 'exMoveupCat' )
{
    $sql = 'SELECT f.`cat_order` 
            FROM `'.$tbl_forum_categories.'` f
            WHERE f.`cat_id` = ' . $cat_id;

    $order = claro_sql_query_get_single_value($sql);

    if ( $cmd == 'exMoveupCat' && $order>1 )
    {
        // previous cat +1
        $sql = 'UPDATE `'.$tbl_forum_categories.'`
                SET `cat_order` = `cat_order`+1
                WHERE `cat_order` = ' . ($order-1);
        claro_sql_query($sql);

        // cat -1
        $sql = 'UPDATE `'.$tbl_forum_categories.'`
                SET `cat_order` = `cat_order`-1
                WHERE `cat_id` = ' . $cat_id ;
        claro_sql_query($sql);
        
    }
    
    if ( $cmd == 'exMovedownCat' )
    {
         $sql = 'SELECT max(f.`cat_order`) as `cat_order`
                 FROM `'.$tbl_forum_categories.'` f';
         $max_order = claro_sql_query_get_single_value($sql);
    
        if ( $order<$max_order )
        {
            // next cat - 1
            $sql = 'UPDATE `'.$tbl_forum_categories.'`
                    SET `cat_order` = `cat_order`-1
                    WHERE `cat_order` =  '. ($order+1);
            claro_sql_query($sql);
    
            // cat + 1
            $sql = 'UPDATE `'.$tbl_forum_categories.'`
                    SET `cat_order` = `cat_order`+1
                    WHERE `cat_id` = '. $cat_id;
            claro_sql_query($sql);
        }
    }
}

/*---------------------------------------------------------------------
  GO TO FORUMS LIST OF THIS CATEGORY
 ---------------------------------------------------------------------*/

if ( !empty($cat_id) && ! isset($_REQUEST['forumcatsave']) ) 
{
    $sql = "SELECT cat_title
             FROM `". $tbl_forum_categories . "`
             WHERE cat_id='". $cat_id ."'";

    $result = claro_sql_query($sql);
    
    if (mysql_num_rows($result))
    {
        $row = mysql_fetch_array($result);
        $cat_title = $row['cat_title'];
    }
    else
    {
        $cat_title = $langEmpty; 
    }

}

if ( isset($_REQUEST['forumgo']) )
{
    $display  = DISP_FORUM_GO;
    $subTitle = $langForCat." ' ".$cat_title." ' ";
    $sql = "SELECT forum_id id,
                   forum_name name,
                   forum_access access,
                   forum_moderator moderator, 
                   forum_type type,
                   forum_desc
             FROM `".$tbl_forum_forums."`
             WHERE cat_id='". $cat_id ."'
             ORDER BY forum_order";

    $result = claro_sql_query($sql);
    $nbForumsInCat = mysql_num_rows($result);
    $forumList = array();
    while ( $row = mysql_fetch_array($result)) $forumList[] = $row;
    
    if ( $cat_id != CAT_FOR_GROUPS) $show_formToAddAForum = true;
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

    if ( $current_cat_id==CAT_FOR_GROUPS )
    {
        $is_allowedToMoveForum = false;
    }
    else
    {
        $is_allowedToMoveForum = true;

        $sql = "SELECT cat_id, cat_title
                FROM `".$tbl_forum_categories."`";
        $result = claro_sql_query($sql);

        while ( list($cat_id, $cat_title) = mysql_fetch_row($result) )
        {
            if ( $cat_id != CAT_FOR_GROUPS )
            {
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

elseif ( isset($_REQUEST['forumcatedit']) )
{
    $display  = DISP_FORUM_CAT_EDIT;
    $subTitle = $langModCatName;

    $sql = "SELECT cat_id, cat_title
            FROM `".$tbl_forum_categories."`
            WHERE cat_id = '".$cat_id."'";

    $result   = claro_sql_query($sql);
    list($cat_id, $cat_title) = mysql_fetch_row($result);
}

/*---------------------------------------------------------------------
     FORUM CATEGORY SAVE
 ---------------------------------------------------------------------*/

elseif ( isset($_REQUEST['forumcatsave']) )
{
    $display = DISP_FORUM_CAT_SAVE;

    if ( !empty($cat_title) )
    {
        update_category_title( $cat_id, $cat_title );
    }
    else
    {
        $display_error_mess = TRUE;
    }
}



/*---------------------------------------------------------------------
  SAVE FORUM NAME & DESCRIPTION
 ---------------------------------------------------------------------*/

elseif ( isset($_REQUEST['forumgosave']) )
{
    $display = DISP_FORUM_GO_SAVE;

    if ( !empty($forum_name) )
    {
        update_forum_settings($forum_id, $forum_name, $forum_desc, $forum_type, $cat_id);
    }
    else
    {
        $display_error_mess = TRUE;
    }

}

/*---------------------------------------------------------------------
     FORUM ADD CATEGORY
 ---------------------------------------------------------------------*/

elseif ( isset($_REQUEST['forumcatadd']) )
{
    $display=DISP_FORUM_CAT_ADD;

    /*
         We have to absolutely reserved a specific cat_id for groups. Otherwise,
         group doesn't work correctly. Usually this forum category is created at
         course creation. But to be sure, we force its creation before // any
         other new category creation.
         The cat_id number is stored into the CAT_FOR_GROUPS constant
     */

    if ( !empty($cat_title) )
    {
        create_category($cat_title);
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
	
    if ( !empty($forum_name)) //do not add forum if empty name given
    {
        create_forum($forum_name, $forum_desc, $forum_type, $cat_id);
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

    delete_forum ($forum_id);
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
            ."<form action=\"".$_SERVER['PHP_SELF']."\" method=post>"
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
                   "<td valign=top>". htmlspecialchars($thisForum['name'])."</td>\n".
                   "<td valign=top>".
                   ( empty($thisForum['forum_desc'])    ? '<center>    - </center>' : htmlspecialchars($thisForum['forum_desc']) ).
                   "</td>\n".
                   "<td valign=top    align=\"center\">\n".
                   "<a    href=\"".$_SERVER['PHP_SELF']."\"".
                   "?forumgoedit=yes&amp;forum_id=".$thisForum['id']."&amp;cat_id=".$cat_id.">".
                   "<img src=\"".$imgRepositoryWeb."edit.gif\" alt=\"".$langModify."\" border=\"0\">".
                   "</a>".
                   "</td>\n".
                   "<td align=\"center\">".
                   ($cat_id ==    CAT_FOR_GROUPS ?
                   "<small><i>".$langCannotBeDeleted."</i></small>"
                   :
                   "<a    href=\"".$_SERVER['PHP_SELF']."?forumgodel=yes&amp;forum_id=".$thisForum['id']."&amp;cat_id=".$cat_id."&amp;ok=0\"    onclick=\"return confirmation('".clean_str_for_javascript($langAreYouSureToDelete .' \'' . htmlspecialchars($thisForum['name']) .'\'    ?')."');\">".
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
                   echo "<td align=\"center\"><a href=\"".$_SERVER['PHP_SELF']."?cmd=exMovedown&amp;forum_id=".$thisForum['id']."&amp;cat_id=".$cat_id."&amp;forumgo=yes\">
                   <img src=\"".$imgRepositoryWeb."down.gif\"></a>
               </td>";
               }

               if ( $iteratorInCat>1 )
               {
                   echo "<td align=\"center\"><a href=\"".$HP_SELF."?cmd=exMoveup&amp;forum_id=".$thisForum['id']."&amp;cat_id=".$cat_id."&amp;forumgo=yes\">
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
            echo "<p><b>",$langAddForCat," ",$cat_title,"</b></p>",
                "<form action=\"".$_SERVER['PHP_SELF']."?forumgoadd=yes&amp;cat_id=$cat_id\" method=post>\n",

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

        echo    '<form action='.$_SERVER['PHP_SELF'].'?forumgosave=yes&amp;cat_id=$cat_id" method="post">' . "\n" .
                '<input type="hidden" name="forum_id" value="' . $forum_id. '">' . "\n" .
    
                '<table border="0">' . "\n" .

                '<tr>' . "\n" . 
                '<td align="right"><label for="forum_name">' . $langForName . ' : </label></td>' . "\n" . 
                '<td><input type="text" name="forum_name" id="forum_name" size="50" value="' . htmlspecialchars($forum_name) . '"></td>' . "\n" .
                '</tr>' . "\n" .

                '<tr valign="top">' . "\n" .
                '<td align="right"><label for="forum_desc">' . $langDescription . ' : </label></td>' . "\n" .
                '<td><textarea name="forum_desc" id="forum_desc" cols="50" rows="3">' . htmlspecialchars($forum_desc) . '</textarea></td>' . "\n" .
                '</tr> ' . "\n" .

                '<tr>' . "\n".
                '<td align="right"><label for="cat_id">' . $langChangeCat . ' : </label></td>' . "\n" .
                '<td>';

        if ( $is_allowedToMoveForum )
        {
            echo '<select name="cat_id" id="cat_id">' . "\n";

            foreach ( $targetCategoryList as $thisTargetCategory )
            {
                echo '<option value="' . $thisTargetCategory['id'] . '" '
                    .($thisTargetCategory['current'] ? 'selected' : '').">"
                    .$thisTargetCategory['title']
                    . '</option>' . "\n";
            }
                
            echo '</select>' . "\n";
        }
        else
        {    
            echo '<em>' . $langCannotMoveGroupForum . '</em>';
        }
    
        echo    '</td>' . "\n" .
                '</tr>' . "\n" .
                '<tr valign="top">' . "\n" .
                '<td>' . "\n" . 
                '</td>' . "\n" .
                '<td>' . "\n" .
                '<input type="submit" value="' . $langSave. '">' . "\n" .
                '</td>',
                '</tr>' . "\n" .
                '</table>' . "\n" .
                '<input type="hidden" name="forumgosave" value="yes">' . "\n" .
                '</form>' . "\n";

        break;

    /*---------------------------------------------------------------------
        Display Edit Forum Category
     ---------------------------------------------------------------------*/

    case DISP_FORUM_CAT_EDIT:

        echo '<form action="'.$_SERVER['PHP_SELF'].'?forumcatsave=yes" method="post">' . 
            '<input type="hidden" name="cat_id" value="' . $cat_id . '">' . 
            '<label for="cat_title">' . $langCategory . ' : </label>' .
            '<input type="text" name="cat_title" id="cat_title" size="55" value="' . htmlspecialchars($cat_title) . '">' . "\n" .
            '<input type="submit" value="' . $langSave . '">' . "\n" .
            '</form>';
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
            "<form action=\"".$_SERVER['PHP_SELF']."?forumadmin=yes\" method=\"post\">\n",
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
                     ."<a href=\"".$_SERVER['PHP_SELF']
                     ."?forumgo=yes&amp;cat_id=".$thisCategory['id']."\">"
                     . htmlspecialchars($thisCategory['title'])
                     ."</a>"
                     ." <small>(".$thisCategory['nb_forum'].")</small>"
                     ."</td>"
    
                     ."<td align=\"center\">"
                     ."<a href=\"".$_PHP_SELF."?forumcatedit=yes&amp;cat_id=".$thisCategory['id']."\">"
                     ."<img src=\"".$imgRepositoryWeb."edit.gif\" alt=\"".$langModify."\" border=\"0\">"
                     ."</a>"
                     ."</td>\n"
    
                     ."<td align=\"center\">";
    
                if ($thisCategory['id'] != CAT_FOR_GROUPS)
                {
                    echo "<a href=\"".$_SERVER['PHP_SELF']."?"
                        ."forumcatdel=yes&amp;cat_id=".$thisCategory['id']."&amp;ok=0\" "
                        ."onclick=\"return confirmation('".clean_str_for_javascript($langAreYouSureToDelete .' \'' . htmlspecialchars($thisCategory['title']) .'\' ?')."');\">".
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
                    echo "<td align=\"center\"><a href=\"".$_SERVER['PHP_SELF']."?cmd=exMovedownCat&amp;cat_id=".$thisCategory['id']."\">
                            <img src=\"".$imgRepositoryWeb."down.gif\"></a>
                        </td>";
                }

                if ( $iteratorInCat > 1 )
                {
                    echo "<td align=\"center\"><a href=\"".$_SERVER['PHP_SELF']."?cmd=exMoveupCat&amp;cat_id=".$thisCategory['id']."\">
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
            ."<form action=\"".$_SERVER['PHP_SELF']."\" method=\"post\">\n"
            ."<label for=\"cat_title\">".$langCategory." : </label>"
            ."<input type=\"text\" name=\"cat_title\" id=\"cat_title\" size=\"50\">\n"
            ."<input type=\"submit\" value=\"".$langAdd."\">\n"
            ."<input type=\"hidden\" name=\"forumcatadd\" value=\"yes\">\n"
            ."</form>\n";

        break;
}

/*---------------------------------------------------------------------
    Display footer
 ---------------------------------------------------------------------*/

include $includePath.'/claro_init_footer.inc.php';

//////////////////////////////////////////////////////////////////////////////



function update_category_title( $catId, $catTitle )
{
    $tbl_cdb_names        = claro_sql_get_course_tbl();
    $tbl_forum_categories = $tbl_cdb_names['bb_categories'];

    if ( !empty($catTitle) )
    {
        $sql = "UPDATE `".$tbl_forum_categories."`
            SET   cat_title = '". addslashes($catTitle) ."'
            WHERE cat_id    = '".(int)$catId."'";
        
        if (claro_sql_query($sql) != false) return true;
    }

    return false;
}

function update_forum_settings($forum_id, $forum_name, $forum_desc, $forum_type, $cat_id)
{
    $tbl_cdb_names        = claro_sql_get_course_tbl();
    $tbl_forum_forums     = $tbl_cdb_names['bb_forums'];
    $sql = 'UPDATE `'.$tbl_forum_forums.'`
            SET `forum_name`     = "'. addslashes($forum_name) .'",
                `forum_desc`     = "'. addslashes($forum_desc) .'",
                `forum_access`   = 2,
                `forum_moderator`= 1,
                `cat_id`         = "' . (int)$cat_id     . '",
                `forum_type`     = "' . $forum_type .'"
            WHERE `forum_id` = ' . (int)$forum_id;

    if (claro_sql_query($sql) != false) return true;
    else                                return false;
}

function create_category($cat_title)
{
    $tbl_cdb_names        = claro_sql_get_course_tbl();
    $tbl_forum_categories = $tbl_cdb_names['bb_categories'];

    // Find order in the category we must give to the newly created forum
    $sql = 'SELECT MAX(`cat_order`) FROM `'.$tbl_forum_categories.'`';
    $result = claro_sql_query($sql);

    list($orderMax) = mysql_fetch_row($result);
    $order = $orderMax + 1;

    $sql = 'INSERT INTO `'.$tbl_forum_categories.'`
            SET `cat_title` = "'. addslashes($cat_title) .'",
                `cat_order` = "'.$order.'"';

    if ( claro_sql_query($sql) != false) return true;
    else                                 return false;
}


function delete_forum($forum_id)
{
    $tbl_cdb_names = claro_sql_get_course_tbl();
    $tbl_forum_categories = $tbl_cdb_names['bb_categories'];
    $tbl_forum_forums     = $tbl_cdb_names['bb_forums'    ];
    $tbl_forum_topics     = $tbl_cdb_names['bb_topics'    ];

    $sql = 'DELETE FROM `'.$tbl_forum_topics.'` 
            WHERE `forum_id` = "'.$forum_id.'"';
    
    if ( claro_sql_query($sql) == false ) return false;

    $sql = 'DELETE FROM `'.$tbl_forum_forums.'` 
            WHERE `forum_id` = "'.$forum_id.'"';
        
    if ( claro_sql_query($sql) == false) return false;
    else                                 return true;

    // note we should also clean the topic notification table ...
}



function create_forum($forum_name, $forum_desc, $forum_type, $cat_id)
{
     $tbl_cdb_names    = claro_sql_get_course_tbl();
     $tbl_forum_forums = $tbl_cdb_names['bb_forums'             ];

   // find order in the category we have to give to the newly created forum

    $sql = 'SELECT MAX(`forum_order`)
            FROM `'.$tbl_forum_forums.'`
            WHERE cat_id = "'.$cat_id.'"';

    $result = claro_sql_query($sql);

    list($orderMax) = mysql_fetch_row($result);
    $order = $orderMax + 1;

    // add new forum in DB

    $sql = 'INSERT INTO `'.$tbl_forum_forums.'`
            SET forum_name  = "'. addslashes($forum_name) .'", 
            forum_desc      = "'. addslashes($forum_desc) .'", 
            forum_access    = 2,
            forum_moderator = 1, 
            cat_id          = "'. (int)$cat_id .'", 
            forum_type      = "'. $forum_type  . '",
            md5             ="'.md5(time()).'", 
            forum_order    ="'. (int) $order.'"';

    if ( claro_sql_query($sql) != false) return true;
    else                                 return false;
}


$tbl_cdb_names        = claro_sql_get_course_tbl();
$tbl_forum_categories = $tbl_cdb_names['bb_categories'         ];
$tbl_forum_forums     = $tbl_cdb_names['bb_forums'             ];
$tbl_forum_topics     = $tbl_cdb_names['bb_topics'             ];

function move_up_forum($forum_id, $cat_id)
{
    $tbl_cdb_names    = claro_sql_get_course_tbl();
    $tbl_forum_forums = $tbl_cdb_names['bb_forums'];

    $forum_rank = get_forum_rank($forum_id);

    if ($forum_rank > 1 )
    {
        // previous forum +1
        $sql = 'UPDATE `'.$tbl_forum_forums.'`
                SET    `forum_order` = `forum_order`+1
                WHERE  `forum_order` =  '. ($forum_rank - 1) . '
                  AND  `cat_id` = '. $cat_id ;

        if ( claro_sql_query($sql) == false ) return false;

        // forum -1
        $sql = 'UPDATE `'.$tbl_forum_forums.'`
                SET    `forum_order` = `forum_order`-1
                WHERE  `forum_id` =  "'.$forum_id.'"
                  AND  `cat_id` = '. $cat_id ;
        if ( claro_sql_query($sql) == false ) return false;

        return true;
    }
}

function move_down_forum($forum_id, $cat_id)
{
    $tbl_cdb_names    = claro_sql_get_course_tbl();
    $tbl_forum_forums = $tbl_cdb_names['bb_forums'];

    $forum_rank = get_forum_rank($forum_id);

    $sql = 'SELECT MAX(f.`forum_order`) AS `max_order`
            FROM  `'.$tbl_forum_forums.'` f
            WHERE `cat_id` = '. $cat_id ;

    $max_order = claro_sql_query_get_single_value($sql);

    if ( $forum_rank < $max_order )
    {
        // next forum - 1
        $sql = 'UPDATE `'.$tbl_forum_forums.'`
                SET `forum_order` = `forum_order`-1
                WHERE `forum_order` =  '. ($forum_rank + 1) . ' 
                    AND `cat_id` = '. $cat_id ;

        if ( claro_sql_query($sql) == false ) return false;

        // forum + 1
        $sql = 'UPDATE `'.$tbl_forum_forums.'`
                SET `forum_order` = `forum_order`+1
                WHERE `forum_id` =  ' . $forum_id . '
                AND `cat_id` = ' . $cat_id ;

        if ( claro_sql_query($sql) == false ) return false;
    }
}


/**
 * return the rank (order) of a forum into a category
 *
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param  int fourm id
 * @return int forum rank (order)
 */

function get_forum_rank($forum_id)
{
    $tbl_cdb_names    = claro_sql_get_course_tbl();
    $tbl_forum_forums = $tbl_cdb_names['bb_forums'];

    $sql = 'SELECT f.`forum_order` 
            FROM `'.$tbl_forum_forums.'` f
            WHERE `forum_id` = ' . $forum_id ;

    $forum_rank = claro_sql_query_get_single_value($sql);

    return $forum_rank;
}
?>