<?php # $Id$
//----------------------------------------------------------------------
// CLAROLINE
//----------------------------------------------------------------------
// Copyright (c) 2001-2004 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see 'credits' file
//----------------------------------------------------------------------
/**
 * SHUFFLE COURSE SITE CREATION TOOL
 * GOALS
 * *******

// Créateur de cours bidon pour les tests
// fake course creator to test

// create nc courses
// insert between smin and smax students
// insert between pmin and pmax courses admins

 * ******************************************************************
 */

DEFINE("CONF_COURSE_ADMIN_CAN_BE_STUDENT",True);
DEFINE("CONF_PLATFORM_ADMIN_CAN_BE_COURSE_ADMIN",True);
DEFINE("DEFAULT_NUMBER_CREATED_COURSE",25);
DEFINE("DEFAULT_MIN_QTY_STUDENT_REGISTRED_IN_COURSE",5);
DEFINE("DEFAULT_MAX_QTY_STUDENT_REGISTRED_IN_COURSE",50);
DEFINE("DEFAULT_MIN_QTY_TEACHER_REGISTRED_IN_COURSE",0);// Exclude the creator
DEFINE("DEFAULT_MAX_QTY_TEACHER_REGISTRED_IN_COURSE",3);// Exclude the creator
DEFINE("DEFAULT_MIN_QTY_GROUP_REGISTRED_IN_COURSE",0);
DEFINE("DEFAULT_MAX_QTY_GROUP_REGISTRED_IN_COURSE",10);
DEFINE("DEFAULT_MIN_QTY_STUDENT_REGISTRED_IN_GROUP",5);
DEFINE("DEFAULT_MAX_QTY_STUDENT_REGISTRED_IN_GROUP",8);
DEFINE("DEFAULT_MIN_QTY_GROUP_OF_A_STUDENT",1);
DEFINE("DEFAULT_MAX_QTY_GROUP_OF_A_STUDENT",3);
DEFINE("DEFAULT_PREFIX","TEST");


/////////////////////DON'T EDIT ///////////
DEFINE("DISP_RESULT_INSERT"        ,1);     //
DEFINE("DISP_FORM_SET_OPTION"      ,2);     //
DEFINE("CONF_VAL_STUDENT_STATUS"    ,5); //
DEFINE("CONF_VAL_TEACHER_STATUS"    ,1); //
/////////////////////DON'T EDIT ///////////


$langFile = "create_course";
unset($includePath);
require '../../inc/claro_init_global.inc.php';
if (!isset($includePath)) die("init not run");
if (!isset($_uid)) die("you need to be logged");

//// Config tool
include($includePath."/conf/add_course.conf.php");
//// LIBS
include($includePath."/lib/text.lib.php");
include($includePath."/lib/add_course.lib.inc.php");
include($includePath."/lib/group.lib.inc.php");
include($includePath."/lib/debug.lib.inc.php");
include($includePath."/lib/fileManage.lib.php");
include($includePath."/conf/course_info.conf.php");

$nameTools = $langCreateSite;
$interbredcrump[]= array ("url"=>"../index.php", "name"=> $langAdmin);

$htmlHeadXtra[] =
"<style type=\"text/css\">
        TABLE.list TR TD.rem { background-color: ".$color1."; }
        label
        {
            display: block;
            width: 25%;
            float: left;
            text-align: right;
            background-color: ".$color2.";
        }
        input, textarea { margin-left: 1em;    background-color: ".$color1.";}
        fieldset, form {  margin: 10; padding: 4;  }

-->
</STYLE>";

$TABLECOURSE          = "$mainDbName`.`cours";
$TABLECOURSDOMAIN     = "$mainDbName`.`faculte";
$TABLEUSER            = "$mainDbName`.`user";
$TABLECOURSUSER       = "$mainDbName`.`cours_user";
$TABLEANNOUNCEMENTS   = "annonces";
$can_create_courses   = (bool) ($is_allowedCreateCourse);
$coursesRepositories  = $coursesRepositorySys;

$nc   = is_numeric($HTTP_POST_VARS["nc"])?$HTTP_POST_VARS["nc"]:DEFAULT_MIN_QTY_STUDENT_REGISTRED_IN_COURSE;
$smin = is_numeric($HTTP_POST_VARS["smin"])?$HTTP_POST_VARS["smin"]:DEFAULT_MIN_QTY_STUDENT_REGISTRED_IN_COURSE;
$smax = is_numeric($HTTP_POST_VARS["smax"])?$HTTP_POST_VARS["smax"]:DEFAULT_MAX_QTY_STUDENT_REGISTRED_IN_COURSE;
$pmin = is_numeric($HTTP_POST_VARS["pmin"])?$HTTP_POST_VARS["pmin"]:DEFAULT_MIN_QTY_TEACHER_REGISTRED_IN_COURSE;
$pmax = is_numeric($HTTP_POST_VARS["pmax"])?$HTTP_POST_VARS["pmax"]:DEFAULT_MAX_QTY_TEACHER_REGISTRED_IN_COURSE;
$gmin = is_numeric($HTTP_POST_VARS["gmin"])?$HTTP_POST_VARS["gmin"]:DEFAULT_MIN_QTY_GROUP_REGISTRED_IN_COURSE;
$gmax = is_numeric($HTTP_POST_VARS["gmax"])?$HTTP_POST_VARS["gmax"]:DEFAULT_MAX_QTY_GROUP_REGISTRED_IN_COURSE;
$gpumin = is_numeric($HTTP_POST_VARS["gpumin"])?$HTTP_POST_VARS["gpumin"]:DEFAULT_MIN_QTY_GROUP_OF_A_STUDENT;
$gpumax = is_numeric($HTTP_POST_VARS["gpumax"])?$HTTP_POST_VARS["gpumax"]:DEFAULT_MAX_QTY_GROUP_OF_A_STUDENT;

$emin = is_numeric($HTTP_POST_VARS["emin"])?$HTTP_POST_VARS["emin"]:DEFAULT_MIN_QTY_STUDENT_REGISTRED_IN_GROUP;
$emax = is_numeric($HTTP_POST_VARS["emax"])?$HTTP_POST_VARS["emax"]:DEFAULT_MAX_QTY_STUDENT_REGISTRED_IN_GROUP;
$pfCode = strtoupper($HTTP_POST_VARS["pfCode"]!=""?$HTTP_POST_VARS["pfCode"]:DEFAULT_PREFIX);

$display =DISP_FORM_SET_OPTION;

if (isset($HTTP_POST_VARS["nc"]))
{


    srand ((double) microtime() * 10000000);
    $nameOfCourses = array (
    "Neo", "Morpheus", "Trinit&eacute;e", "Cypher", "Tank",
    "Math","Algo","jablo","phraz","dea","inc","sc.po","touch","ordering","system",
    "ecologie","tv screening","microtime","tabl","dutch","french","english",
    "german","swali","suedish","romanian","Welcome","site","powered",
    "Apache","Mandrake","Linux","Claroline","Note","webpage","server",
    "upgrade","tomorrow","software","webmaster","directory","version"
    ,"wood" ,"chair","green","house","brique","syster of mercy","depeche mode"
    ,"step","front","depot","html","sapin","camion","balai","citrouille"
    ,"tente","radiateur","lune","baleine","fenetre","windows","cartable"
    ,"geographie","geometrie","history","physic","pot","electronic"
    ,"mecanic","horticulture","dactylo" ,"Astronomie","Biologie","Chimie"
    ,"Écologie","Mathématiques","Physique","Sciences de la Terre"
    ,"Sciences de l'Univers","Statistiques","Anthropologie","Archéologie"
    ,"Éducation","Géographie","Histoire","Langue et Linguistique"
    ,"Pédagogie","Philosophie","Psychologie"
    ,"Sciences cognitives","Sociologie","Politique","Société","Associations"
    ,"Organismes","Commerce","Défense","Droit","Économie","Entreprise"
    ,"Famille","Gestion","Gestion de l'environnement","Métiers","Politique"
    ,"Urbanisme","Agnosticisme","Athéisme","Ésotérisme","Mysticisme"
    ,"Mythologie","Religion","Sectes","Spiritualité","Théologie","Art"
    ,"Arts visuels","Arts du spectacle","Cinéma","Culture populaire","Danse"
    ,"Littérature","Médias","Musique","Techniques et sciences appliquées"
    ,"Aérospatiale","Agriculture","Architecture","Communication","Électronique"
    ,"Industrie","Informatique","Internet","Ingénierie","Médecine","Technologie"
    ,"Télécommunications","Transport","Vie quotidienne et loisirs","Bricolage"
    ,"Cuisine","Divertissement","Jardinage","Jeux","Nutrition","Santé"
    ,"Sexualité","Sport","Tourisme","pays du monde","Actualité de l'année"
    ,"Éphéméride","Biographies","Arts","Movies","Television","Music","Business"
    ,"Jobs","Real Estate","Investing","Computers","Internet","Software"
    ,"Hardware","Games","Video Games","RPGs","Gambling","Health","Fitness"
    ,"Medicine","Alternative","Home","Family","Consumers","Cooking"
    ,"Kids and Teens","Arts","School Time","Teen Life","News","Media"
    ,"Newspapers","Weather","Recreation","Travel","Food","Outdoors","Humor"
    ,"Reference","Maps","Education","Libraries","Regional","US","Canada","UK"
    ,"Europe","Science","Biology","Psychology","Physics","Shopping","Autos"
    ,"Clothing","Gifts","Society","People","Religion","Issues","Sports"
    ,"Baseball","Soccer","Basketball","World"
    );

    $aivailableLang[]= $platformLanguage;
    if ($HTTP_POST_VARS["random_lang"])
    {
        $dirname = $includePath."/../lang/";
        if($dirname[strlen($dirname)-1]!='/')
            $dirname.='/';
        $handle=opendir($dirname);
        while ($entries = readdir($handle))
        {
            if ($entries=='.'||$entries=='..'||$entries=='CVS')
                continue;
            if (is_dir($dirname.$entries))
            {
                $aivailableLang[]=$entries;
            }
        }
        closedir($handle);
    }
    $sqlCat = "Select `code` `code` from `".$TABLECOURSDOMAIN."` WHERE canHaveCoursesChild  = 'TRUE'";
    $resCat = mysql_query($sqlCat);
    while ($fac = mysql_fetch_array($resCat,MYSQL_ASSOC))
    {
        $aivailableFaculty[] = $fac["code"];
    }

    $sqlTeachers = "Select `user_id` `uid` from `".$TABLEUSER."` WHERE statut = 1";
    $resTeachers = mysql_query($sqlTeachers);
    while ($teacher = mysql_fetch_array($resTeachers,MYSQL_ASSOC))
    {
        $teachersUid[] = $teacher["uid"];
    }

    $sqlUsers = "Select `user_id` `uid` from `".$TABLEUSER."`";
    if (!CONF_COURSE_ADMIN_CAN_BE_STUDENT)
        $sqlUsers .= " WHERE statut = '".CONF_VAL_STUDENT_STATUS."'";
    $resUsers = mysql_query($sqlUsers);
    while ($users = mysql_fetch_array($resUsers,MYSQL_ASSOC))
    {
        $usersUid[] = $users["uid"];
    }

    $strWork = "<OL>";
    for($noCourse=1;$noCourse<=$nc;$noCourse++)
    {
            $wantedCode     = $pfCode." ".field_rand($nameOfCourses)." (".substr(md5(uniqid("")),0,3).")";
            $faculte           = field_rand($aivailableFaculty);
            $langue_course     = field_rand($aivailableLang);
            $uidCourse         = field_rand($teachersUid);
        //  function define_course_keys ($wantedCode, $prefix4all="", $prefix4baseName="",     $prefix4path="", $addUniquePrefix =false,    $useCodeInDepedentKeys = TRUE    )
            $keys             = define_course_keys ($wantedCode,"",$dbNamePrefix);
            $currentCourseCode       = $keys["currentCourseCode"];
            $currentCourseId         = $keys["currentCourseId"];
            $currentCourseDbName     = $keys["currentCourseDbName"];
            $currentCourseRepository = $keys["currentCourseRepository"];
            $expirationDate          = time() + $firstExpirationDelay;

        if ($DEBUG) echo "[Code:",    $currentCourseCode,"][Id:",$currentCourseId,"][Db:",$currentCourseDbName     ,"][Path:",$coursesRepositorySys, " - ",$coursesRepositories," - ",$currentCourseRepository ,"]";

        //function prepare_course_repository($courseRepository, $courseId)
            prepare_course_repository(
                $currentCourseRepository,
                $currentCourseId
                );
            update_Db_course(
                $currentCourseDbName
                );
            fill_course_repository(
                $currentCourseRepository
                );

        // function     fill_Db_course($courseDbName,$courseRepository)
            fill_Db_course(
                $currentCourseDbName,
                $currentCourseRepository,
                $langue_course
                );
            register_course(
                $currentCourseId,
                $currentCourseCode,
                $currentCourseRepository,
                $currentCourseDbName,
                "test team",
                $_user['email'],
				$faculte,
                $wantedCode,
                $langue_course,
                $uidCourse,
                $expirationDate
                );

/////// REGISTER TEATCHERS
                $qtyOfTeacher = rand(min($pmin,count($teachersUid)),min($pmax,count($teachersUid)));
                if ($qtyOfTeacher>0)
                {
                    $addTeatcher = array_rand($teachersUid,$qtyOfTeacher);
                    if (is_array($addTeatcher))
                    while (list(,$key)=each($addTeatcher))
                    {
                        $userSqlSegment[]="('".$currentCourseId."', ".$teachersUid[$key].", 1)";
                    }
                }
/////// REGISTER STUDENTS
                $qtyOfStudents = rand(min($smin,count($usersUid)),min($smax,count($usersUid)));
                if ($qtyOfStudents>0)
                {
                    $addStudents = array_rand($usersUid,$qtyOfStudents);
                    if (is_array($addStudents))
                    while (list(,$key)=each($addStudents))
                    {
                        $userSqlSegment[]="('".$currentCourseId."', ".$usersUid[$key].", 5)";
                    }
                }
        if (is_array($userSqlSegment))
        {
            $sqlAddUserToCourse = "
        INSERT IGNORE INTO `".$TABLECOURSUSER."`
        (`code_cours`, `user_id`, `statut`)
        VALUES
            ".implode(", ",$userSqlSegment);
            $resAddUsers = mysql_query($sqlAddUserToCourse);
            $addedUsers = mysql_affected_rows();
        }


//-----------------------------------------------------------------------------------
		$group_quantity = rand($gmin,$gmax);
		$group_max		= $emax; //maximum of student for a group

		$_course['dbNameGlu']	= $courseTablePrefix . $currentCourseDbName. $dbGlu; // use in all queries
		$tbl_Groups		   		= $_course['dbNameGlu']."group_team";
		$tbl_GroupsUsers		= $_course['dbNameGlu']."group_rel_team_user";
		$tbl_Forums             = $_course['dbNameGlu'].'bb_forums';


/*
	// For all Group forums, cat_id=2
*/

		for ($i = 1; $i <= $group_quantity; $i++)
		{
			/*
			* Insert a new group in the course group table and keep its ID
			*/

			$sql = "INSERT INTO `".$tbl_Groups."`
					(maxStudent) VALUES ('".$group_max."')";

			mysql_query($sql);
			$lastId = mysql_insert_id();

			/*
			* Create a forum for the group in the forum table
			*/

			$sql = "INSERT INTO `".$tbl_Forums."`
					(forum_id, forum_name, forum_desc, forum_access, forum_moderator,
					forum_topics, forum_posts, forum_last_post_id, cat_id,
					forum_type, md5)
					VALUES ('','$langForumGroup $lastId','', 2, 1, 0, 0,
							1, 1, 0,'".md5(time())."')";

			mysql_query($sql);
			$forumInsertId = mysql_insert_id();

			/*
			* Create a directory for to allow group student to upload documents
			*/

			/*  Create a Unique ID path preventing other enter */

			$secretDirectory	=	uniqid("")."_team_".$lastId;

			while ( check_name_exist($coursesRepositorySys.$currentCourseRepository."/group/$secretDirectory") )
			{
				$secretDirectory = uniqid("")."_team_".$lastId;
			}

			mkdirs($coursesRepositorySys.$currentCourseRepository."/group/".$secretDirectory, 0777);

			/* Stores the directory path into the group table */

			$sql = "UPDATE `".$tbl_Groups."`
					SET   name            = '".$langGroup." ".$lastId."',
						forumId         = '".$forumInsertId."',
						secretDirectory = '".$secretDirectory."'
					WHERE id ='".$lastId."'";

			mysql_query($sql);

		}	// end for ($i = 1; $i <= $group_quantity; $i++)

			$nbGroupPerUser	= rand($gpumin,$gpumax);
			$tbl_CoursUsers	=$TABLECOURSUSER;
			$tbl_Users		=$TABLEUSER;

		if ($group_quantity>0)
			fill_in_groups();

//-----------------------------------------------------------

                $strWork .= "
        <LI>    <strong>[wantedCode:".$wantedCode ."]</strong><br>
                [Code:".    $currentCourseCode."]
                [Id:".$currentCourseId."]
                [Db:".$currentCourseDbName     ."]
                [Path:".$currentCourseRepository ."]<br>
                [langue_course:".$langue_course ."]
                [faculte:".$faculte ."]
                [uidCourse:".$uidCourse."]<br>
                [nb users added:".$addedUsers."]
				[nb group:".$group_quantity."]
				[maximum student per group:".$group_max."]
        </LI>        ";
    }
    $strWork .= "</OL>";
    $display = DISP_RESULT_INSERT;
}

//////////////// OUTPUT
    #### OUTPUT ####
    //// HEADER ////
include($includePath.'/claro_init_header.inc.php');
claro_disp_tool_title(
	array(
	'mainTitle'=>$nameTools,
	'subTitle'=>$PHP_AUTH_USER." - ".$siteName." - ".$clarolineVersion
	)
	);
claro_disp_msg_arr($controlMsg);
switch ($display)
{
    case DISP_RESULT_INSERT :
        echo $strWork;
        ?>
            <UL class="menu">
                <LI>
                    <a href="<?php echo $PHP_SELF ?>" >Again</a>
                </LI>
                <LI>
                    <a href="<?php echo $rootAdminWeb ?>" >Admin</a>
                </LI>
            </UL>
        <?php
        break;
    case DISP_FORM_SET_OPTION :
        ?>

<form action="<?php echo $PHP_SELF ?>" method="POST" enctype="multipart/form-data" target="_self">
    <fieldset>
    <legend > <?php echo $langCreateCourse ?> </legend>
    <label for="nc"> <?php echo $langQantity ?> </label>
    <input align="right" type="text" id="nc" name="nc" value="<?php echo $nc ?>" size="5" maxlength="3"><br>
    <label for="pfCode"> <?php echo $langPrefix ?> </label>
    <input align="right" type="text" id="pfCode" name="pfCode" value="<?php echo $pfCode ?>" size="5" maxlength="5">
    </fieldset>
    <fieldset >
    <legend > <?php echo $langStudent ?> </legend>
    <Label for="smin"><?php echo $langMin ?> </Label>
    <input type="text" id="smin" align="right" name="smin" value="<?php echo $smin ?>" size="5" maxlength="3"><br>
    <Label for="smax"><?php echo $langMax ?> </Label>
    <input type="text" id="smax" align="right" name="smax" value="<?php echo $smax ?>" size="5" maxlength="3">
    </fieldset>
    <fieldset>
    <legend ><?php echo $langProfessor."(".$langAddedToCreator.")"; ?> </legend>
    <Label for="pmin"> <?php echo $langMin ?> </Label>
    <input align="right" id="pmin"  type="text" name="pmin" value="<?php echo $pmin ?>" size="5" maxlength="3"><br>
    <Label for="pmax"> <?php echo $langMax ?> </Label>
    <input align="right" id="pmax"  type="text" name="pmax" value="<?php echo $pmax ?>" size="5" maxlength="3">
    </fieldset>
    <fieldset>
    <Label for="noLangRand"><input type="radio" id="noLangRand" name="random_lang" value="" checked="checked">    <?php echo $langOnly." ".$language ?></label>
    <Label for="langRand"><input type="radio" id="langRand"   name="random_lang" value="random_lang"><?php echo $langRandomLanguage ?></label>
    </fieldset>
	<fieldset>
    <legend ><?php echo $langNumGroup; ?> </legend>
    <Label for="gmin"> <?php echo $langMin ?> </Label>
    <input align="right" id="gmin"  type="text" name="gmin" value="<?php echo $gmin ?>" size="5" maxlength="3"><br>
    <Label for="gmax"> <?php echo $langMax ?> </Label>
    <input align="right" id="gmax"  type="text" name="gmax" value="<?php echo $gmax ?>" size="5" maxlength="3">
    </fieldset>
		<fieldset>
    <legend ><?php echo $langMaxStudentGroup; ?> </legend>
    <Label for="emax"> <?php echo $langMax ?> </Label>
    <input align="right" id="emax"  type="text" name="emax" value="<?php echo $emax ?>" size="5" maxlength="3">
    </fieldset>
		<fieldset>
    <legend ><?php echo $langNumGroupStudent; ?> </legend>
    <Label for="gpumin"> <?php echo $langMin ?> </Label>
    <input align="right" id="gpumin"  type="text" name="gmin" value="<?php echo $gpumin ?>" size="5" maxlength="3"><br>
    <Label for="gpumax"> <?php echo $langMax ?> </Label>
    <input align="right" id="gpumax"  type="text" name="gmax" value="<?php echo $gpumax ?>" size="5" maxlength="3">
    </fieldset>


    <input type="submit" name="create" value="create">
</form>
        <?php
        break;
    default : "hum erreur de display";

}

function field_rand($arr)
{
    $rand_keys    = array_rand ($arr);
    return $arr[$rand_keys];
}
?>