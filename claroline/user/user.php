<?php // $Id$

$langFile = 'registration';
$tlabelReq = "CLUSR___";
require '../inc/claro_init_global.inc.php';
if (!($_cid)) 	claro_disp_select_course();

$htmlHeadXtra[] =
"
<script type=\"text/javascript\" language=\"JavaScript\" >
function confirmation (name)
{
	if (confirm(\" $langAreYouSureToDelete \"+ name + \" ?\"))
		{return true;}
	else
		{return false;}
}
</script>
";

include($includePath."/conf/user.conf.php");
include($includePath."/lib/admin.lib.inc.php");

@include($includePath."/lib/debug.lib.inc.php");

$step             = $nbUsersPerPage;
$is_allowedToEdit = $is_courseAdmin;
$can_add_user     = ($is_courseAdmin && CONF_COURSEADMIN_IS_ALLOWED_TO_ADD_USER)
				    || $is_platformAdmin;
$currentCourse    = $currentCourseID  = $_course['sysCode'];

$tbl_users 				= $mainDbName."`.`user";
$tbl_courses_users		= $mainDbName."`.`cours_user";
$tbl_rel_users_groups	= $_course['dbNameGlu']."group_rel_team_user";
$tbl_groups 			= $_course['dbNameGlu']."group_team";

////////// WORKS /////////////

if ($is_allowedToEdit)
{
	// Unregister user from course
	// (notice : it does not delete user from claroline main DB)

	if($unregister)
	{
        // delete user from course user list

        $done = remove_user_from_course($user_id, $_cid);
	if ($done)
        {
           $dialogBox =$langUserUnsubscribed;
        }
        else
        {
           $dialogBox =$langUserNotUnsubscribed;
        }
   }
}	// end if allowed to edit

$sqlNbUser = "SELECT count(user.user_id) nb_users
              FROM `".$tbl_courses_users."` `cours_user`,
                   `".$tbl_users."` `user`
              WHERE code_cours = \"".$currentCourseID."\"
              AND cours_user.user_id = `user`.user_id";

$result      = claro_sql_query($sqlNbUser);
$userTotalNb = mysql_fetch_array($result, MYSQL_ASSOC);
$userTotalNb = $userTotalNb["nb_users"];

$nameTools = $langUsers;

///////////// OUTPUTS ///////////

include($includePath."/claro_init_header.inc.php");

if ( ! $is_courseAllowed)
	claro_disp_auth_form();

//stats
include($includePath."/lib/events.lib.inc.php");
event_access_tool($nameTools);
claro_disp_tool_title($nameTools." (".$langUserNumber." : ".$userTotalNb.")",
			$is_allowedToEdit ? 'help_user.php' : false);

// Display Forms or dialog box(if needed)

if($dialogBox)
  {
    claro_disp_message_box($dialogBox);
  }


if ($is_allowedToEdit)
{
?>
<p align="right">
	<?php if ($can_add_user)
	{ ?>
	<a href="user_add.php"><?php echo $langAddAU; ?></a> |
	<?php
	}
	?>
	<a href="../group/group.php"><?php echo $langGroupUserManagement; ?></a>
</p>

<?
}

/*==========================
      DISPLAY USERS LIST
  ==========================*/

// PAGER - don't show the pager link if there are less than 50 user

if ($userTotalNb > $step)
{
	if(!isset($offset))
	{
		$offset=0;
	}

	$next     = $offset + $step;
	$previous = $offset - $step;

	$navLink = "<table summary=\"".$langSummaryNavBar."\" width=\"100%\" border=\"0\">\n"
	          ."<tr >\n"
			  ."<td align=\"left\">";

	if ($previous >= 0)
	{
		$navLink .= "<small><a href=\"$PHP_SELF?offset=$previous\">&lt;&lt; </a></small>";
	}

	$navLink .= "</td>\n"
	           ."<td align=\"right\">";

	if ($next < $userTotalNb)
	{
		$navLink .= "<small><a href=\"$PHP_SELF?offset=$next\">&gt;&gt;</a></small>";
	}

	$navLink .= "</td>\n"
	           ."</tr>\n"
	           ."</table>\n";
}
else
{
	$offset = 0;
}

echo $navLink;

// NOTE !! NEED ALSO 'SHOW ALL USERS'

$i= $offset;

/*----------------------------------------
              COLUMN HEADERS
 --------------------------------------*/


echo	"<table class=\"claroTable\" ",
		"width=\"100%\" cellpadding=\"2\" cellspacing=\"1\" ",
		"border=\"0\" summary=\"".$langSummaryTable."\">\n",

		"<colgroup span=\"3\" align=\"left\"></colgroup>";

	if($is_allowedToEdit)
	{
		echo	"<colgroup span=\"2\"></colgroup>",
				"<colgroup span=\"2\" width=\"0\" ></colgroup>";
	}

	echo	"<thead>",
			"\n<tr class=\"headerX\" align=\"center\" valign=\"top\">",
			"\n<th scope=\"col\" id=\"name\">",$langUserName,"</th>",
			"\n<th scope=\"col\" id=\"role\">",$langRole,"</th>",
			"\n<th scope=\"col\" id=\"team\">",$langGroup,"</th>\n";

	if($is_allowedToEdit) // EDIT COMMANDS
	{
		echo	"\n<th scope=\"col\" id=\"tut\"  >",$langTutor,"</th>",
				"\n<th scope=\"col\" id=\"CM\"   >",$langCourseManager,"</th>",
				"\n<th scope=\"col\" id=\"edit\" >",$langEdit,"</th>",
				"\n<th scope=\"col\" id=\"del\"  >",$langUnreg,"</th>\n";
	}

echo	"\n</tr>",
		"\n</thead>",
		"\n<tbody>";

/*==========================
      DB USERS SEARCH
  ==========================*/


$resultUsers = claro_sql_query("SELECT `user`.`user_id`, `user`.`nom`, `user`.`prenom`, `user`.`email`,
                              `cours_user`.`statut`, `cours_user`.`tutor`, `cours_user`.`role`,
                               `ug`.`team` ,
                               `sg`.`name` nameTeam

                        FROM `$tbl_users` `user`, `$tbl_courses_users` `cours_user`
                        LEFT JOIN `".$tbl_rel_users_groups."` `ug`
                        ON `user`.`user_id`=`ug`.`user`
                        LEFT JOIN `".$tbl_groups."` `sg`
                        ON `ug`.`team` = `sg`.`id`
                        WHERE `user`.`user_id`=`cours_user`.`user_id`
                        AND `cours_user`.`code_cours`='".$currentCourseID."'

                        ORDER BY
                            `cours_user`.`statut` ASC,
                            `cours_user`.`tutor` DESC,
                            UPPER(`user`.`nom`),
                            UPPER(`user`.`prenom`),
                            UPPER(`sg`.`name`)

                        LIMIT $offset, $step"); // ORDER BY cours_user.statut, tutor DESC, nom, prenom

$sqlGetUsers ="SELECT `user`.`user_id`, `user`.`nom`, `user`.`prenom`, 
                      `user`.`email`, `cours_user`.`statut`, 
                      `cours_user`.`tutor`, `cours_user`.`role`
               FROM `".$tbl_users."` `user`, `".$tbl_courses_users."` `cours_user`
               WHERE `user`.`user_id`=`cours_user`.`user_id`
               AND `cours_user`.`code_cours`='".$currentCourseID."'
               ORDER BY `cours_user`.`statut` ASC, `cours_user`.`tutor` DESC,
                        UPPER(`user`.`nom`), UPPER(`user`.`prenom`)
			   LIMIT $offset, $step";

$resultUsers = claro_sql_query($sqlGetUsers);

// ORDER BY cours_user.statut, tutor DESC, nom, prenom
while ($thisUser = mysql_fetch_array($resultUsers,MYSQL_ASSOC))
{
	$users[$thisUser["user_id"]]	= $thisUser;
	$usersId[]	= $thisUser["user_id"];
}

$sqlGroupOfUsers = "SELECT `ug`.`user` uid, `ug`.`team` team, 
                    `sg`.`name` nameTeam
                    FROM `".$tbl_rel_users_groups."` `ug`
                    LEFT JOIN `".$tbl_groups."` `sg`
                    ON `ug`.`team` = `sg`.`id`
                    WHERE `ug`.`user` IN (".implode(",",$usersId).")";

$resultUserGroup = claro_sql_query($sqlGroupOfUsers);

while ($thisAffiliation = mysql_fetch_array($resultUserGroup,MYSQL_ASSOC))
{
	$usersGroup[$thisAffiliation['uid']][$thisAffiliation["team"]]["nameTeam"]	= $thisAffiliation["nameTeam"];
}

/*==========================
      USERS LIST DISPLAY
  ==========================*/

$previousUser = "";
reset($users);
$idColor = 1;
$colorArr[1] 	= $colorEvenLines;
$colorArr[-1] 	= $colorOddLines;

while (list(,$thisUser) = each($users))
{
	// User name column
	$i++;

	echo	"\n<tr align=\"center\" valign=\"top\">",

			"\n<td id=\"u".$i."\" headers=\"name\" align=\"left\">",

			"\n<small>\n",$i,"</small>\n&nbsp;",
			"<a href=\"userInfo.php?uInfo=",$thisUser['user_id'],"\">",
			$thisUser['prenom']," ",$thisUser['nom'],
			"</a>",

			"</td>\n";
	// User role column

	echo	"<td headers=\"role u".$i."\" align=\"left\">",$thisUser["role"],"</td>\n";

	// User group column
	$userGroups = $usersGroup[$thisUser['user_id']];
	if($userGroups == NULL)	// NULL and not '0' because team can be inexistent
	{
		echo "\n<td headers=\"team\" > - </td>";
	}
	else
	{
		echo	"\n<td headers=\"team u".$i."\">";
		reset($userGroups);

		while (list($thisGroupsNo,$thisGroupsName)=each($userGroups))
		{
			echo 	"\n<div>",
					"\n",$thisGroupsName["nameTeam"],"",
					"\n<small>(".$thisGroupsNo.")</small>",
					"\n</div>";
		}

		echo "\n</td>\n";
	}


	if ($previousUser == $thisUser['user_id'])
	{
		echo 	"<td headers=\"team u".$i."\" >\n</td>\n";
	}
	elseif($is_allowedToEdit)
	{
		// Tutor column

		if($thisUser['tutor'] == '0')
		{
			echo	"<td headers=\"tut u".$i."\"> - </td>\n";
		}
		else
		{
			echo	"<td headers=\"tut u".$i."\">",$langTutor,"</td>\n";
		}

		// course manager column

		if($thisUser['statut'] == '1')
		{
			echo 	"<td headers=\"CM u".$i."\">",$langCourseManager,"</td>\n";
		}
		else
		{
			echo 	"<td headers=\"CM u".$i."\"> - </td>\n";
		}

		// Edit user column

		echo	"<td headers=\"edit u".$i."\">",
				"<a href=\"userInfo.php?editMainUserInfo=".$thisUser[user_id]."\">",
				"<img border=\"0\" alt=\"".$langEdit."\" src=\"".$clarolineRepositoryWeb."img/edit.gif\">",
				"</a>",
				"</td>\n";

		// Unregister user column
		echo "<td headers=\"del u".$i."\" >";

		if ($thisUser["user_id"] != $_uid)
		{
			echo	"<a href=\"$PHP_SELF?unregister=yes&user_id=".$thisUser[user_id]."\" ",
					"onClick=\"return confirmation('".$langUnreg ." ".$thisUser["nom"]."".$thisUser["prenom"]."');\">",
					"<img border=\"0\" alt=\"".$langUnreg."\" src=\"".$clarolineRepositoryWeb."img/unenroll.gif\">",
					"</a>";
		}

		echo	"</td>\n";
	}													// END - is_allowedToEdit

	echo	"</tr>\n";

	$previousUser = $thisUser['user_id'];
} 							// END - while fetch_array

echo	"</tbody>",
		"</table>\n";

echo $navLink;

include($includePath."/claro_init_footer.inc.php");
?>
