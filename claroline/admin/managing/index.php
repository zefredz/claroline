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

 $langMergeUsersAccount = "Merge User account";


$langFile = "admin.managing.menu";
require '../../inc/claro_init_global.inc.php';

$nameTools = $langManage;
$interbredcrump[]= array ("url"=>$rootAdminWeb, "name"=> $langAdministrationTools);

$htmlHeadXtra[] = "<style type=\"text/css\">
<!--
	ul { font-size : small }
-->
</STYLE>";
include($includePath."/lib/text.lib.php");
include($includePath."/lib/debug.lib.inc.php");
$dateNow 			= claro_format_locale_date($dateTimeFormatLong);
$is_allowedToAdmin 	= $is_platformAdmin || $PHP_AUTH_USER;


// make here some  test
// $checkMsgs[] = array("level" => 5, "target" => "test 1 ", "content" => "this is  just  a  warning test 1 ");

// ----- is install visible ----- begin
 if (file_exists("../../install/index.php") && !file_exists("../../install/.htaccess"))
 {
	 $controlMsg["warning"][]="install is not protected";
 }
// ----- is install visible ----- end



include($includePath."/claro_init_header.inc.php");

claro_disp_tool_title(
	array(
	'mainTitle'=>$nameTools,
	'subTitle'=> $PHP_AUTH_USER." - ".$siteName." - ".$clarolineVersion." - ".$dateNow
	)
	);
claro_disp_msg_arr($controlMsg);
?>
<font color="#996633">
	<?php echo  $langNoteAboutViaPMA ?>
</font><br>
<H3>
	<?php echo $langUsers; ?>
</H3>
	<UL>
		<LI>
			<a href="adminprofile.php" ><?php echo $langAdminProfileUser; ?></a>
		</LI>
		<LI>
			<a href="<?php echo $clarolineRepositoryWeb; ?>admin/managing/add_users.php"><?php echo $langAddAUser ?></a>
		</LI>
		<LI>
			<a href="search_user.php?searchForm"><?php echo $langSearchAUser ?></a>
		</LI>
<?php
if ($is_trackingEnabled && 0)
{
?>
		<LI>
			<a target="list" href="<?php echo $phpMyAdminWeb ?>sql.php?db=<?php echo $mysqlMainDb ?>&table=loginout&goto=db_details.php&sql_query=SELECT+%2A+FROM+%60loginout%60&pos=0"><?php echo  $langLogIdentLogout?></a> (PMA)
		</LI>
		<LI>
			<a target="list" href="<?php echo $phpMyAdminWeb ?>sql.php?db=<?php echo $mysqlMainDb ?>&table=loginout&goto=db_details.php&sql_query=SELECT+loginout.idLog,+loginout.when,+loginout.ip,+loginout.id_user,+user.username,+user.prenom,+user.nom,+user.statut,+user.email,+user.password,+loginout.action+FROM+loginout,+user+WHERE+(loginout.id_user+=+user.user_id)+order+by+loginout.when+desc"><?php echo $langLogIdentLogoutComplete; ?></a> (PMA)
		</LI>
<?php
}
?>
		<LI>
			<a href="<?php echo $rootAdminWeb?>managing/addAdminInhtpassword.php"><?php echo  $langAddAdminInApache ?></a>
		</LI>
				<?php
			if (!$stable||$dev)
			{
		?>
		<LI>
			<a href="<?php echo $rootAdminWeb; ?>managing/search_user.php?fusion"><?php echo $langMergeUsersAccount ?></A><br>
		</LI>
		<?php
			}
		?>

	</UL>
<H3>
	<?php echo $langCourses; ?>
</H3>
	<UL>

		<LI>
			<a href="<?php echo $clarolineRepositoryWeb?>create_course/add_course.php"><?php echo $langAddACourse ?></a>
		</LI>
		<LI>
			<a href="<?php echo $rootAdminWeb?>managing/search_course.php?choiceSearch"><?php echo $langSearchACourse ?></a>
		</LI>
		<LI>
			<a href="<?php echo $rootAdminWeb?>managing/search_course.php?choiceRestore"><?php echo $langRestoreACourse ?></a>
		</LI>
	</UL>
<H3>
	<?php echo $langCourses; ?> - <?php echo $langUsers; ?>
</H3>
	<UL>
		<LI>
			<a href="adminCoursesOfAUser.php" ><?php echo $langCourseOfAUser; ?></a>
		</LI>
		<LI>
			<a href="userManagement.php" ><?php echo $langCourseOfListUser; ?></a>
		</LI>
		<!--LI>
			<a target="list" href="<?php echo $phpMyAdminWeb?>sql.php?db=<?php echo $mysqlMainDb ?>&table=cours_user&printview=1&sql_query=SELECT++%2A++FROM++%60cours_user%60+"><?php echo $langListOfCourseOfUser ?></a> (PMA)
		</LI-->
		<LI>
			<a target="list" href="<?php echo $phpMyAdminWeb?>sql.php?db=<?php echo $mysqlMainDb ?>&table=cours_user&printview=1&sql_query=SELECT+utilisateur.nom%2C+utilisateur.prenom%2C+utilisateur.username%2C+utilisateur.email%2C+liaison.role%2C+cours.code%2C+cours.intitule+FROM++%60cours_user%60+liaison+LEFT++JOIN++%60cours%60+cours+ON+liaison.code_cours+%3D+cours.code+LEFT++JOIN++%60user%60+utilisateur+ON+liaison.user_id+%3D+utilisateur.user_id"><?php echo $langListOfCourseSubscriptionSimple ?></a> (PMA)
		</LI>
		<LI>
			<a target="list" href="<?php echo $phpMyAdminWeb?>sql.php?db=<?php echo $mysqlMainDb ?>&table=cours_user&printview=1&sql_query=SELECT++%2A++FROM++%60cours_user%60+liaison+LEFT++JOIN++%60cours%60+cours+ON+liaison.code_cours+%3D+cours.code+LEFT++JOIN++%60user%60+utilisateur+ON+liaison.user_id+%3D+utilisateur.user_id"><?php echo $langListOfCourseSubscriptionComplete  ?></a> (PMA)
		</LI>
	</UL>
<H3>
	<?php echo $langCategories; ?>
</H3>
	<UL>
		<LI>
			<a href="<?php echo $rootAdminWeb?>devTools/search_faculty.php"><?php echo $langAdminTree ?></a>
		</LI>
	</UL>
<H3>
	<?php echo  $langAdministrationTools ?>
</H3>
	<UL>
		<LI>
			<a href="<?php echo $phpMyAdminWeb?>">PHP My Admin</a>
		</LI>
		<LI>
			<a href="todo.php"><?php echo $langNomOutilTodo ?></a>
		</LI>
	</UL>
<?php
include($includePath."/claro_init_footer.inc.php");
?>
