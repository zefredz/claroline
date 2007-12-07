<?php // $Id$

$langYouCannotDelCatOfGroupsForums = "You can not delete the group forum category. 
If you need to remove group forums, you rather have to delete the group";
$langGroupsForum = "Group Forum";

$lang_areYouSureToDelete = "Are you sure to delete";


$langFile = "forum_admin";
require '../inc/claro_init_global.inc.php';

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

include('../inc/claro_init_header.inc.php');

//////////////////////////////////////////////////////////////////////////////

/*
 * CONSTANT DEFINITION FOR DIPLAY SWITCH
 */

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

/*
 * CONSTANT DEFINING THE FORUM CATEGORY RESERVED FOR CLAROLINE GROUPS
 */

define("CAT_FOR_GROUPS",1);

$tbl_groups      = $_course['dbNameGlu'].'group_team';
$TBL_FORUMS      = $_course['dbNameGlu']."bb_forums";
$TBL_CATAGORIES  = $_course['dbNameGlu']."bb_categories";
$TBL_USERS       = $_course['dbNameGlu']."bb_users";
$TBL_FORUM_MODS  = $_course['dbNameGlu']."bb_forum_mods";
$TBL_FORUMTOPICS = $_course['dbNameGlu']."bb_forumtopics";



$is_allowedToEdit = $is_courseAdmin || $is_platformAdmin;

if ($is_allowedToEdit)
{
     /*======================================
      EXECUTE COMMAND OF REORDER WHEN CALLED
      ======================================*/


        switch($cmd)
           {
            case "exMovedown" :
                  $ThisForumId = $_GET['moveForumId'];
                  $sortDirection = "ASC";
                  break;
            case "exMoveup" :
                  $ThisForumId = $_GET['moveForumId'];
                  $sortDirection = "DESC";
            case "exMovedownCat" :
                  $ThisCatId = $_GET['moveCatId'];
                  $sortDirectionCat = "ASC";
                  break;
            case "exMoveupCat" :
                  $ThisCatId = $_GET['moveCatId'];
                  $sortDirectionCat = "DESC";
                  break;
            default:
                  break;
           }

        //re-order forum

        if ($sortDirection)
        {
           $sql = "SELECT f.`forum_id`, f.`forum_order` FROM `$TBL_FORUMS` f
                          WHERE
                          f.`cat_id` = $moveCat
                          ORDER BY f.`forum_order` $sortDirection";

            $result = mysql_query($sql);
            
	    while (list ($ForumId, $ForumOrderInCat) = mysql_fetch_row($result))
                {

               // STEP 2 : FOUND THE NEXT FORUM ID AND ORDER.
               //          COMMIT ORDER SWAP ON THE DB

                  if (isset($ThisForumOrderFound) && ($ThisForumOrderFound == true))
                  {
                     $nextForumId = $ForumId;
                     $nextForumOrder = $ForumOrderInCat;

                     mysql_query("UPDATE `$TBL_FORUMS`
                                        SET `forum_order` = \"$nextForumOrder\"
                                      WHERE `forum_id` =  \"$ThisForumId\"");
                     mysql_query("UPDATE `$TBL_FORUMS`
                                        SET `forum_order` = \"$ThisForumOrder\"
                                      WHERE `forum_id` =  \"$nextForumId\"");
                     break;
                  }

               // STEP 1 : FIND THE ORDER OF THE FORUM

                  if ($ForumId==$ThisForumId)
                  {
                      $ThisForumOrder = $ForumOrderInCat;
                      $ThisForumOrderFound = true;
                  }
                }

        }

        //re-order categories

        if ($sortDirectionCat)
        {

           $sql = "SELECT c.`cat_id`, c.`cat_order` FROM `$TBL_CATAGORIES` c
                          ORDER BY c.`cat_order` $sortDirectionCat
                          ";

           $result = mysql_query($sql);

           while (list ($CatId, $CatOrderInCatList) = mysql_fetch_row($result))
                {

               // STEP 2 : FOUND THE NEXT CAT ID AND ORDER.
               //          COMMIT ORDER SWAP ON THE DB

                  if (isset($ThisCatOrderFound) && ($ThisCatOrderFound == true))
                  {
                     $nextCatId = $CatId;
                     $nextCatOrder = $CatOrderInCatList;

                     mysql_query("UPDATE `$TBL_CATAGORIES`
                                        SET `cat_order` = \"$nextCatOrder\"
                                      WHERE `cat_id` = \"$ThisCatId\"");
                     mysql_query("UPDATE `$TBL_CATAGORIES`
                                        SET `cat_order` = \"$ThisCatOrder\"
                                      WHERE `cat_id` =  \"$nextCatId\"");
                     break;
                  }

               // STEP 1 : FIND THE ORDER OF THE CAT

                  if ($CatId==$ThisCatId)
                  {
                      $ThisCatOrder = $CatOrderInCatList;
                      $ThisCatOrderFound = true;
                  }
                }

        }
        // end execute command

	/*==================================
	  GO TO FORUMS LIST OF THIS CATEGORY
	  ==================================*/

	if($forumgo)
	{
		$display  = DISP_FORUM_GO;
		$subTitle = $langForCat." ' ".$ctg." ' ";
        $sql = "SELECT `f`.`forum_id`        `id`,
                       `f`.`forum_name`      `name`,
                       `f`.`forum_access`    `access`,
                       `f`.`forum_moderator` `moderator`, 
                       `f`.`forum_type`      `type`,
                       `f`.`forum_desc`      `forum_desc`,
					   `g`.`id`              `gid`,
					   `g`.`name`            `gname`
                 FROM `".$TBL_FORUMS."` f
				 LEFT JOIN `".$tbl_groups."` g
				 ON f.forum_id = g.forumId
                 WHERE f.cat_id='".$cat_id."'
                 ORDER BY f.forum_order";

		$result   = claro_sql_query($sql);
        $nbForumsInCat = mysql_num_rows($result);
		$forumList = array();
		while ($row	= mysql_fetch_array($result)) $forumList[] = $row;

        if ($cat_id != CAT_FOR_GROUPS) $show_formToAddAForum = true;
	}

/*==========================
      EDIT FORUM NAME
  ==========================*/

	elseif($forumgoedit)
	{
		$display = DISP_FORUM_GO_EDIT;

		$result = mysql_query("SELECT forum_id, forum_name, forum_desc, forum_access,
										forum_moderator, cat_id, forum_type
								FROM `".$TBL_FORUMS."`
                                WHERE forum_id = '".$forum_id."'");

		list($forum_id, $forum_name, $forum_desc, $forum_access,
				$forum_moderator, $current_cat_id, $forum_type)
				= mysql_fetch_row($result);

		$subTitle = $langModify." ' ".$forum_name." ' ";


		if ($current_cat_id==CAT_FOR_GROUPS)
		{
			$is_allowedToMoveForum = false;
		}
		else
		{
			$is_allowedToMoveForum = true;

			$result = mysql_query("SELECT cat_id, cat_title
                                   FROM `".$TBL_CATAGORIES."`");

			while(list($cat_id, $cat_title) = mysql_fetch_row($result))
			{
				if($cat_id != CAT_FOR_GROUPS)
				{
					$output_option_list	.= "
						<option	value=\"".$cat_id."\" ".($cat_id ==	$current_cat_id	? "selected":"").">
							".$cat_title."
						</option>";

					$targetCategoryList[] =
                    array('id'      =>  $cat_id,
					      'title'   =>  $cat_title,
					      'current' => ($cat_id == $current_cat_id	? true : false)
				         );
				}
			}
		}
	}

/*==========================
    FORUM CATEGORY EDIT
  ==========================*/

	elseif($forumcatedit)
	{
		$display  = DISP_FORUM_CAT_EDIT;
		$subTitle = $langModCatName;
		$result   = mysql_query("SELECT cat_id, cat_title
                                 FROM `".$TBL_CATAGORIES."`
                                 WHERE cat_id = '".$cat_id."'");
		list($cat_id, $cat_title) = mysql_fetch_row($result);

	}

/*==========================
     FORUM CATEGORY SAVE
  ==========================*/


	elseif ($forumcatsave)
	{
		$display = DISP_FORUM_CAT_SAVE;
		if ($cat_title != "")
		{

			mysql_query("UPDATE `$TBL_CATAGORIES`
        	             SET   cat_title = '".$cat_title."'
                	     WHERE cat_id    = '".$cat_id."'");
		}
		else
		{
            		$display_error_mess = true;
		}
	}

/*=============================
  SAVE FORUM NAME & DESCRIPTION
  =============================*/

	elseif($forumgosave)
	{
		$display = DISP_FORUM_GO_SAVE;
		if($forum_name != "")
		{
			$result  = mysql_query("SELECT user_id
                                FROM `".$TBL_USERS."`
                                WHERE username = \"".$forum_moderator."\"");

			list($forum_moderator) = mysql_fetch_row($result);

			mysql_query("UPDATE `".$TBL_USERS."`
                	     SET user_level = '2'
	                     WHERE user_id = '".$forum_moderator."'");

			mysql_query("UPDATE `".$TBL_FORUMS."`
			             SET forum_name     = '".$forum_name."',
		        	         forum_desc     = '".$forum_desc."',
		                	 forum_access   = '2',
			                 forum_moderator= '1',
			                 cat_id         = '".$cat_id."',
			                 forum_type     = '".$forum_type."'
		        	     WHERE forum_id = '".$forum_id."'");
		}
		else
		{
            		$display_error_mess = true;
		}

	}

/*==========================
     FORUM ADD CATEGORY
  ==========================*/

	elseif($forumcatadd)
	{
		$display=DISP_FORUM_CAT_ADD;


//         We have to absolutely reserved a specific cat_id for groups. Otherwise,
//         group doesn't work correctly. Usually this forum category is created at
//         course creation. But to be sure, we force its creation before // any
//         other new category creation.
//         The cat_id number is stored into the CAT_FOR_GROUPS constant


        if ($catagories!="")
        {
         // find order in the category we must give to the newly created forum

        $result = mysql_query("SELECT MAX(`cat_order`)
                                                 FROM `".$TBL_CATAGORIES."`
                                                 ");

                        list($orderMax) = mysql_fetch_row($result);
                        $order = $orderMax + 1;

		/*  not useful patch for 1.4.2 to 1.5 see Hugues...

        mysql_query("INSERT	IGNORE INTO `".$TBL_CATAGORIES."`
		        	 SET cat_title = \"groups\",
                     cat_id = '".CAT_FOR_GROUPS."',
                     cat_order = 0
                     ") or die("<center>Query error</center>");
        */
		mysql_query("INSERT INTO `".$TBL_CATAGORIES."`
                     SET cat_title = \"".$catagories."\",
                     cat_order = \"".$order."\"
                     ") or die("<center>Query error</center>");
        }
        else
        {
            $display_error_mess = true;
        }
	}

/*==========================
          Forum Go Add
  ==========================*/

	elseif($forumgoadd)
	{
		$display=DISP_FORUM_GO_ADD;

		$result = mysql_query("SELECT user_id 
                               FROM `".$TBL_USERS."` 
                               WHERE username = '".$forum_moderator."'");

		list($forum_moderator) = mysql_fetch_row($result);

        mysql_query("UPDATE `".$TBL_USERS."`
                     SET user_level = '2'
                     WHERE user_id = '".$forum_moderator."'");
        if ($forum_name !="") //do not add forum if empty name given
        {
            // find order in the category we must give to the newly created forum

            $result = mysql_query("SELECT MAX(`forum_order`)
                                                     FROM `".$TBL_FORUMS."`
                                                     WHERE cat_id = ".$cat_id."
                                                     ");

                            list($orderMax) = mysql_fetch_row($result);
                            $order = $orderMax + 1;

            // add new forum in DB

    		mysql_query("INSERT INTO `".$TBL_FORUMS."`
    		             (forum_id, forum_name, forum_desc, forum_access,
    		              forum_moderator, cat_id, forum_type, md5, forum_order)
    		             VALUES
    		             (NULL, '".$forum_name."', '".$forum_desc."', '2',
    		              '1', '".$cat_id."', '".$forum_type."', '".md5(time())."', ".$order.")");

    		$idforum=mysql_query("SELECT forum_id
    							  FROM `".$TBL_FORUMS."`
    							  WHERE	forum_name=\"".$forum_name."\"");

    		while ($my_forum_id = mysql_fetch_array($idforum))
    		{
    			$forid = $my_forum_id[0];
    		}

    		mysql_query("INSERT INTO `".$TBL_FORUM_MODS."`
                        (forum_id, user_id)
                        VALUES ('".$forid."', '1')");
        }
        else
        {
            $display_error_mess = true;
        }
	}

/*==========================
    FORUM DELETE CATEGORY
  ==========================*/

	elseif($forumcatdel)
	{
		$display = DISP_FORUM_CAT_DEL;

		if ($cat_id!=CAT_FOR_GROUPS)
		{
			$result = mysql_query("SELECT forum_id 
                                   FROM `".$TBL_FORUMS."` 
                                   WHERE cat_id = '".$cat_id."'");

			while(list($forum_id) = mysql_fetch_row($result))
			{
				mysql_query("DELETE FROM `".$TBL_FORUMTOPICS."` 
                             WHERE forum_id = \"".$forum_id."\"");
			}

			mysql_query("DELETE FROM `".$TBL_FORUMS."` 
                         WHERE cat_id = \"".$cat_id."\"");

			mysql_query("DELETE FROM `".$TBL_CATAGORIES."` 
                         WHERE cat_id = \"".$cat_id."\"");

			$msg_can_del_cat_1 = '';
		}
		else
		{
			$msg_can_del_cat_1 = $langYouCannotDelCatOfGroupsForums;
		}
	}

/*==========================
       FORUM GO DEL
  ==========================*/

	elseif($forumgodel)
	{
		$display=DISP_FORUM_GO_DEL;

		mysql_query("DELETE FROM `".$TBL_FORUMTOPICS."` 
                     WHERE forum_id = \"".$forum_id."\"");

		mysql_query("DELETE FROM `".$TBL_FORUMS."` 
                     WHERE forum_id = \"".$forum_id."\"");

	}

/*========================================================================*/

else
{
	$display  = DISP_FORUM_ADMIN;
	$subTitle = $langForCategories;

    $sql = "SELECT c.cat_id AS id, c.cat_title AS title, 
                   COUNT(f.forum_id) AS nb_forum 
                   FROM `".$TBL_CATAGORIES."` c
                   LEFT JOIN `".$TBL_FORUMS."` f 
                   ON f.cat_id = c.cat_id 
                   GROUP BY c.cat_id
                   ORDER BY c.cat_order";
	$result = claro_sql_query($sql);
    $nbOfCat = mysql_num_rows($result);

    $categoryList = array();
    while ($row = mysql_fetch_array($result)) $categoryList[] = $row;
    
} // end else ... if forum_go

/*========================================================================*/

} // end is_allowedToEdit

// else noop



////////////////// OUTPUT //////////////////

if ($is_allowedToEdit)
{
	claro_disp_tool_title(
	array(
	'mainTitle'=>$nameTools,
	'subTitle'=>$subTitle
	)
	);

	claro_disp_msg_arr($controlMsg);
?>
<div align="right">
<a href="#" onClick="MyWindow=window.open('../help/help_forum.php','MyWindow','toolbar=no,location=no,directories=no,status=yes,menubar=no,scrollbars=yes,resizable=yes,width=450,height=550,left=10,top=10'); return false;">
	<?php echo $langHelp ?>
</a>
</div>
<?php
}



if($display == DISP_FORUM_GO)
{
	echo    "<div align=\"right\">".
			"<a href=\"".$_SERVER['PHP_SELF']."?forumadmin=yes\">".$langBackCat."</a>".
			"<form action=\"forum_admin.php?forumgoadd=yes&ctg=".urlencode($ctg)."&cat_id=".$cat_id."\" method=post>".
			"</div>".

			"<table border=0 cellpadding=4 cellspacing=2 class=\"claroTable\">".

			"<tr class=\"headerX\">\n".
			"<th>".$langForName."</th>\n".
			"<th>".$langDescription."</th>\n".
			"<th align=\"center\">".$langModify."</th>\n".
			"<th align=\"center\">".$langDelete."</th>\n".
            "<th colspan=\"2\">".$langOrder."</th>"
			."</tr>";

	if (count($forumList) >	0)
	{
        $iteratorInCat=1;
		foreach	($forumList	as $thisForum)
		{
			echo	"<tr>\n".
					"<td valign=top>".$thisForum['name']."</td>\n".
					"<td valign=top>".
				( empty($thisForum['forum_desc'])	? '<center>	- </center>' : $thisForum['forum_desc']).
					"</td>\n".
					"<td valign=top	align=\"center\">\n".
					"<a	href=forum_admin.php".
					"?forumgoedit=yes&forum_id=".$thisForum['id']."&ctg=".urlencode($ctg)."&cat_id=".$cat_id.">".
					"<img src=\"".$clarolineRepositoryWeb."img/edit.gif\" alt=\"".$langModify."\" border=\"0\">".
					"</a>".
					"</td>\n".
					"<td align=\"center\">".

					(!is_null($thisForum['gid'])?
					"<small><i>".$langCannotBeDeleted."</i> (".(empty($thisForum['gname'])?$thisForum['gid']:$thisForum['gname']).")</small>"
					:
					"<a	href=\"forum_admin.php?forumgodel=yes&forum_id=".$thisForum['id']."&cat_id=".$cat_id."&ctg=".urlencode($ctg)."&ok=0\"	onclick=\"return confirmation('".addslashes(htmlentities($lang_areYouSureToDelete .' \'' .$thisForum['name'].'\'	?'))."');\">".
					"<img src=\"".$clarolineRepositoryWeb."img/delete.gif\"	alt=\"".$langDelete."\"	border=\"0\">".
					"</a>").

					"</td>\n";

            ///display re-order links added for claroline 1.5

           if ($iteratorInCat==$nbForumsInCat)
                  {
                      echo "<td></td>";
                  }
           else
           {
              echo "<td align=\"center\"><a href=\"forum_admin.php?cmd=exMovedown&moveForumId=".$thisForum['id']."&moveCat=".$cat_id."&cat_id=".$cat_id."&ctg=".urlencode($ctg)."&forumgo=yes\">
                        <img src=\"".$clarolineRepositoryWeb."img/down.gif\"></a>
                    </td>";
           }
           if ($iteratorInCat>1)
           {
              echo "<td align=\"center\"><a href=\"forum_admin.php?cmd=exMoveup&moveForumId=".$thisForum['id']."&moveCat=".$cat_id."&cat_id=".$cat_id."&ctg=".urlencode($ctg)."&forumgo=yes\">
                        <img src=\"".$clarolineRepositoryWeb."img/up.gif\"></a>
                    </td>";
           }
           else
           {
              echo "<td></td>";
           }
           $iteratorInCat++;

		   echo "</tr>\n";
		} // end foreach forumList
	} // end if	count forumList	> 0

    echo "</table>";

	if ($show_formToAddAForum)
	{
		echo
			"<p><b>",$langAddForCat," ",$ctg,"</b></p>",

			"<form action=\"forum_admin.php?forumgoadd=yes&ctg=".urlencode($ctg)."&cat_id=$cat_id\" method=post>\n",

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
}
elseif($display == DISP_FORUM_GO_EDIT)
{

	echo	"<form action=\"forum_admin.php?forumgosave=yes&ctg=".urlencode($ctg)."&cat_id=$cat_id\" method=post>\n",
			"<input	type=hidden	name=forum_id value=$forum_id>\n",

			"<table	border=\"0\">\n",

			"<tr>\n",
			"<td align=\"right\"><label for=\"forum_name\">",$langForName," :	</label></td>\n",
			"<td><input	type=text name=\"forum_name\" id=\"forum_name\" size=\"50\" value=\"$forum_name\"></td>\n",
			"</tr>\n",

			"<tr valign=\"top\">\n",
			"<td align=\"right\"><label for=\"forum_desc\">",$langDescription," : </label></td>\n",
			"<td><textarea name=\"forum_desc\" id=\"forum_desc\" cols=\"50\" rows=\"3\">",$forum_desc,"</textarea></td>\n",
			"</tr>\n",

			"<tr>\n",
			"<td align=\"right\"><label for=\"cat_id\">",$langChangeCat,"	: </label></td>\n",
			"<td>";

	if ($is_allowedToMoveForum)
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
			"<input	type=submit	value=\"$langSave\">\n",
			"</td>",
			"</tr>\n",
			"</table>\n",
			"<input	type=hidden	name=forumgosave value=yes>\n",
			"</form>\n";

}
elseif($display == DISP_FORUM_CAT_EDIT)
{
		echo	"
<form action=\"forum_admin.php?forumcatsave=yes\" method=post>
				<input type=hidden name=cat_id value=".$cat_id.">",
                "<label for=\"cat_title\">".$langCat." : </label>".
				"<input type=\"text\" name=\"cat_title\" id=\"cat_title\" size=\"55\" value=\"",$cat_title,"\">\n",
				"<input type=submit value=\"",$langSave,"\">\n",
				"</form>";
	//   <input type=hidden name=forumcatsave value=yes>
}
elseif($display == DISP_FORUM_CAT_SAVE)
{
    if ($display_error_mess)
    {
       echo "<center>".$langemptycatname."</center>".
		"<a href=\"$PHP_SELF?forumcatedit=yes&cat_id=".$cat_id."\">$langBack</a>";
    }
    else
    {
	echo "<center>".$langNameCat."</center>".
		"<a href=\"$PHP_SELF?forumadmin=yes\">$langBack</a>";
    }
}
elseif($display == DISP_FORUM_GO_SAVE)
{
    if ($display_error_mess)
    {
       echo "<center>".$langemptyforumname."</center>".
    	"<a href=\"$PHP_SELF?forumgoedit=yes&forum_id=$forum_id&cat_id=$cat_id&ctg=".urlencode($ctg)."\">".$langBack."</a>";
    }
    else
    {
      echo "<center>".$langForumModified."</center>".
    	"<a href=\"$PHP_SELF?forumgo=yes&cat_id=$cat_id&ctg=".urlencode($ctg)."\">".$langBack."</a>";
    }
}
elseif($display == DISP_FORUM_CAT_ADD)
{
    if ($display_error_mess)
    {
       echo "<center>".$langemptycatname."</center>";
    }
    else
    {
      echo "<center>".$langcatcreated."</center>";
    }
    echo "<a href=\"$PHP_SELF?forumadmin=yes\">".$langBack."</a>";
}
elseif($display == DISP_FORUM_GO_ADD)
{
    if ($display_error_mess)
    {
       echo "<center>".$langemptyforumname."</center>";
    }
    else
    {
      echo "<center>".$langforumcreated."</center>";
    }
    echo	"<a href=\"$PHP_SELF?forumgo=yes&cat_id=$cat_id&ctg=".urlencode($ctg)."\">$langBack</a>\n";
}
elseif($display == DISP_FORUM_CAT_DEL)
{
	echo	$msg_can_del_cat_1.
		"<a href=\"$PHP_SELF?forumadmin=yes\">$langBack</a>";
}
elseif($display == DISP_FORUM_GO_DEL)
{
		echo "<center>".$langForumDeleted."</center>"
				."<a href=\"$PHP_SELF?forumgo=yes&ctg=".urlencode($ctg)."&cat_id=$cat_id\">",$langBack,"</a>";

}
elseif($display == DISP_FORUM_ADMIN)
{
	echo    "<p>",$langAddForums,

			"<form action=\"forum_admin.php?forumadmin=yes\" method=\"post\">\n",

			"<table border=\"0\" cellspacing=\"2\" cellpadding=\"4\" class=\"claroTable\">\n",

			"<tr class=\"headerX\">",
			"<th>",$langCategories,"</th>",
			"<th align=\"center\">",$langModify,"</th>",
			"<th align=\"center\">",$langDelete,"</th>",
            "<th colspan=\"2\">".$langOrder."</th>",
			"</tr>\n";

    if (count($categoryList) > 0)
    {
        $iteratorInCat = 1;
        foreach($categoryList as $thisCategory)
        {
            echo "<tr>"

                 ."<td>"
                 ."<a href=\"forum_admin.php"
                 ."?forumgo=yes&cat_id=".$thisCategory['id']."&ctg=".urlencode($thisCategory['title'])."\">"
                 .$thisCategory['title']
                 ."</a>"
                 ." <small>(".$thisCategory['nb_forum'].")</small>"
                 ."</td>"

                 ."<td align=\"center\">"
                 ."<a href=\"forum_admin.php?forumcatedit=yes&cat_id=".$thisCategory['id']."\">"
                 ."<img src=\"".$clarolineRepositoryWeb."img/edit.gif\" alt=\"".$langModify."\" border=\"0\">"
                 ."</a>"
                 ."</td>\n"

                 ."<td align=\"center\">";

            if ($thisCategory['id'] != CAT_FOR_GROUPS)
            {
                echo "<a href=\"forum_admin.php?"
                    ."forumcatdel=yes&cat_id=".$thisCategory['id']."&ok=0\" "
                    ."onclick=\"return confirmation('".addslashes(htmlentities($lang_areYouSureToDelete .' \'' .$thisCategory['title'].'\' ?'))."');\">".
                    "<img src=\"".$clarolineRepositoryWeb."img/delete.gif\" alt=\"".$langDelete."\" border=\"0\">".
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
              echo "<td align=\"center\"><a href=\"forum_admin.php?cmd=exMovedownCat&moveCatId=".$thisCategory['id']."\">
                        <img src=\"".$clarolineRepositoryWeb."img/down.gif\"></a>
                    </td>";
           }
           if ($iteratorInCat>1)
           {
              echo "<td align=\"center\"><a href=\"forum_admin.php?cmd=exMoveupCat&moveCatId=".$thisCategory['id']."\">
                        <img src=\"".$clarolineRepositoryWeb."img/up.gif\"></a>
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
	
    echo    "</table>",
            "</form>",

            "<h4>".$langAddCategory."</h4>".
            "<form action=\"forum_admin.php?forumcatadd=yes\" method=\"post\">\n".

			"<label for=\"catagories\">".$langCat." : </label>".
			"<input type=\"text\" name=\"catagories\" id=\"catagories\" size=\"50\">\n".
			"<input type=\"submit\" value=\"".$langAdd."\">\n".
			"<input type=\"hidden\" name=\"forumcatadd\" value=\"yes\">\n".
			"</form>\n";


}
else
{
	echo "<center>You are not allowed here</center>";
}
include($includePath."/claro_init_footer.inc.php");
?>
