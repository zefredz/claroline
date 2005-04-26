<?php // $Id$
$tlabelReq = "CLUSR___";
require '../inc/claro_init_global.inc.php';
if (!($_cid)) 	claro_disp_select_course();

$htmlHeadXtra[] =
'
<script type="text/javascript" language="JavaScript" >
function confirmation (name)
{
	if (confirm(" '.clean_str_for_javascript($langAreYouSureToDelete).' "+ name + " ?"))
		{return true;}
	else
		{return false;}
}
</script>
';

include($includePath.'/lib/admin.lib.inc.php');
@include($includePath.'/lib/debug.lib.inc.php');

claro_set_display_mode_available(true);

$step             = (isset($nbUsersPerPage)?$nbUsersPerPage:50);

$is_allowedToEdit = claro_is_allowed_to_edit();

$can_add_user     = (bool) (   $is_courseAdmin 
                     && isset($is_coursemanager_allowed_to_add_user)
                     && $is_coursemanager_allowed_to_add_user)
				    || $is_platformAdmin;
$currentCourse    = $currentCourseID  = $_course['sysCode'];

/**
 * DB tables definition
 */

$tbl_cdb_names = claro_sql_get_course_tbl();
$tbl_mdb_names = claro_sql_get_main_tbl();
$tbl_rel_course_user = $tbl_mdb_names['rel_course_user'  ];
$tbl_users           = $tbl_mdb_names['user'             ];
$tbl_courses_users   = $tbl_rel_course_user;
$tbl_rel_users_groups= $tbl_cdb_names['group_rel_team_user'    ];
$tbl_groups          = $tbl_cdb_names['group_team'             ];

////////// WORKS /////////////

if ($is_allowedToEdit)
{
    $disp_tool_link=TRUE;
    // Unregister user from course
	// (notice : it does not delete user from claroline main DB)

	if($unregister)
	{
        // delete user from course user list

        $done = remove_user_from_course($user_id, $_cid);
	if ($done)
        {
           $dialogBox =$langUserUnsubscribedFromCourse;
        }
        else
        {
           $dialogBox =$langUserNotUnsubscribedFromCourse;
        }
   }
}	// end if allowed to edit

$sqlNbUser = 'SELECT count(user.user_id) `nb_users`
              FROM `'.$tbl_rel_course_user.'` `cours_user`,
                   `'.$tbl_users.'` `user`
              WHERE `cours_user`.`code_cours` = "'.$currentCourseID.'"
              AND cours_user.user_id = `user`.user_id';

$userTotalNb = claro_sql_query_fetch_all($sqlNbUser);
$userTotalNb = $userTotalNb[0]['nb_users'];

$nameTools = $langUsers;

///////////// OUTPUTS ///////////

if ( ! $is_courseAllowed) claro_disp_auth_form();

include($includePath."/claro_init_header.inc.php");

//stats
include($includePath."/lib/events.lib.inc.php");
event_access_tool($_tid, $_courseTool['label']);

claro_disp_tool_title($nameTools." (".$langUserNumber." : ".$userTotalNb.")",
			$is_allowedToEdit ? 'help_user.php' : FALSE);

// Display Forms or dialog box(if needed)

if($dialogBox)
{
    claro_disp_message_box($dialogBox);
}

// display tool links

if ($disp_tool_link)
{
	echo "<p>";
    if ($can_add_user)
	{ 
	   //add a user link
	?>
	<a class="claroCmd" href="user_add.php"><img src="<?php echo $imgRepositoryWeb; ?>user.gif"><?php echo $langAddAU; ?></a> |
	<?php

       //add CSV file of user link
	?>
	<a class="claroCmd" href="AddCSVusers.php?AddType=userTool"><img src="<?php echo $imgRepositoryWeb; ?>importlist.gif"> <?php echo $langAddListUser; ?></a> |
	<?php 
	   //add a class link
	?>
	<a class="claroCmd" href="class_add.php"><img src="<?php echo $imgRepositoryWeb; ?>class.gif"> <?php echo $langAddClass; ?></a> |
	<?php
	
	}
	?>
	<a class="claroCmd" href="../group/group.php"><img src="<?php echo $imgRepositoryWeb; ?>group.gif"><?php echo $langGroupUserManagement; ?></a>
	</p>
<?php
}

/*==========================
      DISPLAY USERS LIST
  ==========================*/

// PAGER - don't show the pager link if there are less than 50 user

if ($userTotalNb > $step)
{
	if(!isset($offset))
	{
		$offset = 0;
	}

	$next     = $offset + $step;
	$previous = $offset - $step;

	$navLink = '<table summary="'.$langSummaryNavBar.'" width="100%" border="0">'."\n"
	          .'<tr>'."\n"
			  .'<td align="left">'
			  ;

	if ($previous >= 0)
	{
		$navLink .= '<small><a href="'.$_SERVER['PHP_SELF'].'?offset='.$previous.'" rel="next" >&lt;&lt; </a></small>';
	}

	$navLink .= '</td>'."\n"
	         .  '<td align="right">';

	if ($next < $userTotalNb)
	{
		$navLink .= '<small><a href="'.$_SERVER['PHP_SELF'].'?offset='.$next.'">&gt;&gt;</a></small>';
	}

	$navLink .= '</td>'."\n"
	           .'</tr>'."\n"
	           .'</table>'."\n"
	           ;
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


echo	"<table class=\"claroTable emphaseLine\" ",
		"width=\"100%\" cellpadding=\"2\" cellspacing=\"1\" ",
		"border=\"0\" summary=\"".$langListCourseUsers."\">\n",

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
		echo	"\n<th scope=\"col\" id=\"tut\"  >",$langGroupTutor,"</th>",
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

                        FROM `".$tbl_users."` `user`, `".$tbl_rel_course_user."` `cours_user`
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

$sqlGetUsers ='SELECT `user`.`user_id`, `user`.`nom`, `user`.`prenom`, 
                      `user`.`email`, `cours_user`.`statut`, 
                      `cours_user`.`tutor`, `cours_user`.`role`
               FROM `'.$tbl_users.'` `user`, `'.$tbl_rel_course_user.'` `cours_user`
               WHERE `user`.`user_id`=`cours_user`.`user_id`
               AND `cours_user`.`code_cours`="'.$currentCourseID.'"
               ORDER BY `cours_user`.`statut` ASC, `cours_user`.`tutor` DESC,
                        UPPER(`user`.`nom`), UPPER(`user`.`prenom`)
			   LIMIT '.$offset.', '.$step;

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

while (list(,$thisUser) = each($users))
{
	// User name column
	$i++;

	echo	"\n<tr align=\"center\" valign=\"top\">",

			"\n<td id=\"u".$i."\" headers=\"name\" align=\"left\">",
            "<img src=\"".$imgRepositoryWeb."user.gif\">",
			"\n<small>\n",$i,"</small>\n&nbsp;",
			"<a href=\"userInfo.php?uInfo=",$thisUser['user_id'],"\">",
			ucfirst(strtolower($thisUser['prenom']))," ",ucfirst(strtolower($thisUser['nom'])),
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
			echo  '<div>'
				 .$thisGroupsName["nameTeam"]
				 .'<small>('.$thisGroupsNo.')</small>'
				 .'</div>';
		}

		echo "\n</td>\n";
	}


	if ($previousUser == $thisUser['user_id'])
	{
		echo 	'<td headers="team u'.$i.'" >&nbsp;</td>'."\n";
	}
	elseif($is_allowedToEdit)
	{
		// Tutor column

		if($thisUser['tutor'] == '0')
		{
			echo	'<td headers="tut u'.$i.'"> - </td>';
		}
		else
		{
			echo	'<td headers="tut u'.$i.'">'.$langGroupTutor.'</td>';
		}
		echo "\n";
		// course manager column

		if($thisUser['statut'] == '1')
		{
			echo 	'<td headers="CM u'.$i.'">'.$langCourseManager.'</td>';
		}
		else
		{
			echo 	'<td headers="CM u'.$i.'"> - </td>';
		}
		echo "\n";

		// Edit user column


		echo	"<td headers=\"edit u".$i."\">",
				"<a href=\"userInfo.php?editMainUserInfo=".$thisUser['user_id']."\">",
				"<img border=\"0\" alt=\"".$langEdit."\" src=\"".$imgRepositoryWeb."edit.gif\">",
				"</a>",
				"</td>\n";


		// Unregister user column
		echo '<td headers="del u'.$i.'" >';

		if ($thisUser["user_id"] != $_uid)
		{
			echo   '<a href="'.$_SERVER['PHP_SELF'].'?unregister=yes&amp;user_id='.$thisUser['user_id'].'" '
				  .'onClick="return confirmation(\''.clean_str_for_javascript($langUnreg .' '.$thisUser['nom'].' '.$thisUser['prenom']).'\');">'
				  .'<img border="0" alt="'.$langUnreg.'" src="'.$imgRepositoryWeb.'unenroll.gif">'
				  .'</a>';
		}

		echo	'</td>'."\n";
	}													// END - is_allowedToEdit

	echo	'</tr>'."\n";

	$previousUser = $thisUser['user_id'];
} 							// END - while fetch_array

echo '</tbody>'
	.'</table>';

echo $navLink;

include($includePath."/claro_init_footer.inc.php");
?>