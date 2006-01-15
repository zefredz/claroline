<?php // $Id$
/**
 * Claroline
 * SHUFFLE COURSE SITE CREATION TOOL
 * Créateur de cours bidon pour les tests
 * fake course creator to test
 *
 * create nc courses
 * insert between smin and smax students
 * insert between pmin and pmax courses admins
 *
 * @version 1.8 $Revision$
 *
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @package SDK
 *
 * @author Claro Team <cvs@claroline.net>
 * @author Christophe Gesché <moosh@claroline.net>
 *
 */


DEFINE('CONF_COURSE_ADMIN_CAN_BE_STUDENT',true);
DEFINE('CONF_PLATFORM_ADMIN_CAN_BE_COURSE_ADMIN',true);
DEFINE('DEFAULT_NUMBER_CREATED_COURSE',25);
DEFINE('DEFAULT_MIN_QTY_STUDENT_REGISTRED_IN_COURSE',5);
DEFINE('DEFAULT_MAX_QTY_STUDENT_REGISTRED_IN_COURSE',50);
DEFINE('DEFAULT_MIN_QTY_TEACHER_REGISTRED_IN_COURSE',0);// Exclude the creator
DEFINE('DEFAULT_MAX_QTY_TEACHER_REGISTRED_IN_COURSE',3);// Exclude the creator
DEFINE('DEFAULT_MIN_QTY_GROUP_REGISTRED_IN_COURSE',0);
DEFINE('DEFAULT_MAX_QTY_GROUP_REGISTRED_IN_COURSE',10);
DEFINE('DEFAULT_MIN_QTY_STUDENT_REGISTRED_IN_GROUP',5);
DEFINE('DEFAULT_MAX_QTY_STUDENT_REGISTRED_IN_GROUP',8);
DEFINE('DEFAULT_MIN_QTY_GROUP_OF_A_STUDENT',1);
DEFINE('DEFAULT_MAX_QTY_GROUP_OF_A_STUDENT',3);
DEFINE('DEFAULT_PREFIX','TEST');


/////////////////////DON'T EDIT /////////////
DEFINE('DISP_RESULT_INSERT'     ,__LINE__); //
DEFINE('DISP_FORM_SET_OPTION'   ,__LINE__); ///
DEFINE('CONF_VAL_STUDENT_STATUS',5);        ///
DEFINE('CONF_VAL_TEACHER_STATUS',1);        //
/////////////////////DON'T EDIT /////////////


unset($includePath);
$cidReset=true;
$gidReset=true;
unset($cidReq);

require '../../inc/claro_init_global.inc.php';

// Security check
if ( ! $_uid ) claro_disp_auth_form();
if ( ! $is_platformAdmin ) claro_die(get_lang('Not allowed'));

if (!isset($includePath)) trigger_error('init not run',E_USER_ERROR);
if (!isset($_uid)) trigger_error('you need to be logged',E_USER_ERROR);

//// Config tool
include $includePath . '/conf/course_main.conf.php';
//// LIBS

include_once $includePath . '/lib/add_course.lib.inc.php';
include_once $includePath . '/lib/course.lib.inc.php';
include_once $includePath . '/lib/group.lib.inc.php';
include_once $includePath . '/lib/debug.lib.inc.php';
include_once $includePath . '/lib/fileManage.lib.php';

$nameTools = get_lang('CreateSite');
$interbredcrump[]= array ('url' => '../index.php', 'name' => get_lang('Administration'));
$interbredcrump[]= array ('url' => 'index.php',    'name' => get_lang('DevTools'));
/*
* DB tables definition
*/

$tbl_cdb_names      = claro_sql_get_course_tbl();
$tbl_mdb_names      = claro_sql_get_main_tbl();
$TABLECOURSE        = $tbl_mdb_names['course'           ];
$TABLECOURSUSER     = $tbl_mdb_names['rel_course_user'  ];
$TABLECOURSDOMAIN   = $tbl_mdb_names['category'         ];
$TABLEUSER          = $tbl_mdb_names['user'             ];
$TABLEANNOUNCEMENTS = $tbl_cdb_names['announcement'          ];

$can_create_courses   = (bool) ($is_allowedCreateCourse);
$coursesRepositories  = $coursesRepositorySys;

$nc     = isset($_REQUEST['nc'])    ? (int) $_REQUEST['nc']    : DEFAULT_NUMBER_CREATED_COURSE;
$smin   = isset($_REQUEST['smin'])  ? (int) $_REQUEST['smin']  : DEFAULT_MIN_QTY_STUDENT_REGISTRED_IN_COURSE;
$smax   = isset($_REQUEST['smax'])  ? (int) $_REQUEST['smax']  : DEFAULT_MAX_QTY_STUDENT_REGISTRED_IN_COURSE;
$pmin   = isset($_REQUEST['pmin'])  ? (int) $_REQUEST['pmin']  : DEFAULT_MIN_QTY_TEACHER_REGISTRED_IN_COURSE;
$pmax   = isset($_REQUEST['pmax'])  ? (int) $_REQUEST['pmax']  : DEFAULT_MAX_QTY_TEACHER_REGISTRED_IN_COURSE;
$gmin   = isset($_REQUEST['gmin'])  ? (int) $_REQUEST['gmin']  : DEFAULT_MIN_QTY_GROUP_REGISTRED_IN_COURSE;
$gmax   = isset($_REQUEST['gmax'])  ? (int) $_REQUEST['gmax']  : DEFAULT_MAX_QTY_GROUP_REGISTRED_IN_COURSE;
$gpumin = isset($_REQUEST['gpumin'])? (int) $_REQUEST['gpumin']: DEFAULT_MIN_QTY_GROUP_OF_A_STUDENT;
$gpumax = isset($_REQUEST['gpumax'])? (int) $_REQUEST['gpumax']: DEFAULT_MAX_QTY_GROUP_OF_A_STUDENT;
$emin   = isset($_REQUEST['emin'])  ? (int) $_REQUEST['emin']  : DEFAULT_MIN_QTY_STUDENT_REGISTRED_IN_GROUP;
$emax   = isset($_REQUEST['emax'])  ? (int) $_REQUEST['emax']  : DEFAULT_MAX_QTY_STUDENT_REGISTRED_IN_GROUP;
$pfCode = strtoupper((isset($_REQUEST['pfCode'])&&$_REQUEST['pfCode']!='')?$_REQUEST['pfCode']:DEFAULT_PREFIX);


$display =DISP_FORM_SET_OPTION;

if (isset($_REQUEST['cmd'])  )
$cmd = $_REQUEST['cmd'];
else
$cmd ='rqFill';

if ($cmd == 'exFill')
{
    srand ((double) microtime() * 10000000);
    $nameOfCourses = array (
    'Neo', 'Morpheus', 'Trinit&eacute;e', 'Cypher', 'Tank',
    'Math','Algo','jablo','phraz','dea','inc','sc.po','touch','ordering','system',
    'ecologie','tv screening','microtime','tabl','dutch','french','english',
    'german','swali','suedish','romanian','Welcome','site','powered',
    'Apache','Mandrake','Linux','Claroline','Note','webpage','server',
    'upgrade','tomorrow','software','webmaster','directory','version'
    ,'wood' ,'chair','green','house','brique','syster of mercy','depeche mode'
    ,'step','front','depot','html','sapin','camion','balai','citrouille'
    ,'tente','radiateur','lune','baleine','fenetre','windows','cartable'
    ,'geographie','geometrie','history','physic','pot','electronic'
    ,'mecanic','horticulture','dactylo' ,'Astronomie','Biologie','Chimie'
    ,'Écologie','Mathématiques','Physique','Sciences de la Terre'
    ,'Sciences de l\'Univers','Statistiques','Anthropologie','Archéologie'
    ,'Éducation','Géographie','Histoire','Langue et Linguistique'
    ,'Pédagogie','Philosophie','Psychologie'
    ,'Sciences cognitives','Sociologie','Politique','Société','Associations'
    ,'Organismes','Commerce','Défense','Droit','Économie','Entreprise'
    ,'Famille','Gestion','Gestion de l\'environnement','Métiers','Politique'
    ,'Urbanisme','Agnosticisme','Athéisme','Ésotérisme','Mysticisme'
    ,'Mythologie','Religion','Sectes','Spiritualité','Théologie','Art'
    ,'Arts visuels','Arts du spectacle','Cinéma','Culture populaire','Danse'
    ,'Littérature','Médias','Musique','Techniques et sciences appliquées'
    ,'Aérospatiale','Agriculture','Architecture','Communication','Électronique'
    ,'Industrie','Informatique','Internet','Ingénierie','Médecine','Technologie'
    ,'Télécommunications','Transport','Vie quotidienne et loisirs','Bricolage'
    ,'Cuisine','Divertissement','Jardinage','Jeux','Nutrition','Santé'
    ,'Sexualité','Sport','Tourisme','pays du monde','Actualité de l\'année'
    ,'Éphéméride','Biographies','Arts','Movies','Television','Music','Business'
    ,'Jobs','Real Estate','Investing','Computers','Internet','Software'
    ,'Hardware','Games','Video Games','RPGs','Gambling','Health','Fitness'
    ,'Medicine','Alternative','Home','Family','Consumers','Cooking'
    ,'Kids and Teens','Arts','School Time','Teen Life','News','Media'
    ,'Newspapers','Weather','Recreation','Travel','Food','Outdoors','Humor'
    ,'Reference','Maps','Education','Libraries','Regional','US','Canada','UK'
    ,'Europe','Science','Biology','Psychology','Physics','Shopping','Autos'
    ,'Clothing','Gifts','Society','People','Religion','Issues','Sports'
    ,'Baseball','Soccer','Basketball','World'
    );

    $aivailableLang[]= $platformLanguage;
    if (isset($_REQUEST['random_lang']) && $_REQUEST['random_lang'] == 'random_lang')
    {
        $aivailableLang = array_keys(claro_get_language_list());
    }

    $aivailableFaculty = array_keys(claro_get_cat_flat_list());

    $sqlTeachers = "SELECT `user_id` `uid` FROM `" . $TABLEUSER . "` WHERE statut = 1";
    $resTeachers = claro_sql_query($sqlTeachers);
    while ($teacher = mysql_fetch_array($resTeachers,MYSQL_ASSOC))
    {
        $teachersUid[] = $teacher['uid'];
    }

    $sqlUsers = "SELECT `user_id` `uid` FROM `" . $TABLEUSER . "`";
    if (!CONF_COURSE_ADMIN_CAN_BE_STUDENT)
    $sqlUsers .= " WHERE statut = '" . CONF_VAL_STUDENT_STATUS . "'";
    $resUsers = claro_sql_query($sqlUsers);
    while ($users = mysql_fetch_array($resUsers,MYSQL_ASSOC))
    {
        $usersUid[] = $users['uid'];
    }

    $strWork = '<OL>';
    for($noCourse=1; $noCourse<=$nc; $noCourse++)
    {
        $wantedCode        = substr($pfCode . ' ' . field_rand($nameOfCourses) . ' (' . substr(md5(uniqid('')),0,3) . ')',0,12);
        $faculte           = field_rand($aivailableFaculty);

        $language_course   = field_rand($aivailableLang);
        $uidCourse         = field_rand($teachersUid);
        //  function define_course_keys ($wantedCode, $prefix4all="", $prefix4baseName="",     $prefix4path="", $addUniquePrefix =false,    $useCodeInDepedentKeys = TRUE    )
        $keys             = define_course_keys ($wantedCode,'',$dbNamePrefix);
        $currentCourseCode       = $keys['currentCourseCode'];
        $currentCourseId         = $keys['currentCourseId'];
        $currentCourseDbName     = $keys['currentCourseDbName'];
        $currentCourseRepository = $keys['currentCourseRepository'];
        $expirationDate          = time() + 3600*12*24*30;

        if ($DEBUG) echo '[Code:',    $currentCourseCode,'][Id:',$currentCourseId,'][Db:',$currentCourseDbName     ,'][Path:',$coursesRepositorySys, ' - ',$coursesRepositories,' - ',$currentCourseRepository ,']';

        //function prepare_course_repository($courseRepository, $courseId)
        prepare_course_repository(
        $currentCourseRepository,
        $currentCourseId
        );
        update_db_course(
        $currentCourseDbName
        );
        fill_course_repository(
        $currentCourseRepository
        );

        // function     fill_db_course($courseDbName,$courseRepository)
        fill_db_course(
        $currentCourseDbName,
        $language_course
        );
        register_course(
        $currentCourseId,
        $currentCourseCode,
        $currentCourseRepository,
        $currentCourseDbName,
        'test team',
        (isset($_user['email'])?$_user['email']:$administrator_email),
        $faculte,
        $wantedCode,
        $language_course,
        $uidCourse,true,true,''
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
        INSERT IGNORE INTO `" . $TABLECOURSUSER . "`
        (`code_cours`, `user_id`, `statut`)
        VALUES
            " . implode(', ', $userSqlSegment);
            $resAddUsers = claro_sql_query($sqlAddUserToCourse);
            $addedUsers = mysql_affected_rows();
        }


        //-----------------------------------------------------------------------------------
        $group_quantity = rand($gmin,$gmax);
        $group_max        = $emax; //maximum of student for a group


        $tbl_cdb_names   = claro_sql_get_course_tbl(claro_get_course_db_name_glued($currentCourseId));
        $tbl_Groups      = $tbl_cdb_names['group_team'];
        $tbl_GroupsUsers = $tbl_cdb_names['group_rel_team_user'];
        $tbl_Forums      = $tbl_cdb_names['bb_forums'];
        /*
        // For all Group forums, cat_id=2
        */

        for ($i = 1; $i <= $group_quantity; $i++)
        {
            /*
            * Insert a new group in the course group table and keep its ID
            */

            $sql = "INSERT INTO `" . $tbl_Groups . "`
                    (maxStudent) VALUES ('" . $group_max . "')";

            $lastId = claro_sql_query_insert_id($sql);

            /*
            * Create a forum for the group in the forum table
            */

            $sql = "INSERT INTO `" . $tbl_Forums . "`
                    (forum_id, forum_name, forum_desc, forum_access, forum_moderator,
                    forum_topics, forum_posts, forum_last_post_id, cat_id,
                    forum_type)
                    VALUES ('','" . get_lang('ForumGroup') . " " . $lastId . "','', 2, 1, 0, 0,
                            1, 1, 0)";


            $forumInsertId = claro_sql_query_insert_id($sql);

            /*
            * Create a directory for to allow group student to upload documents
            */

            /*  Create a Unique ID path preventing other enter */

            $secretDirectory = uniqid($platform_id) . '_team_' . $lastId;

            while ( check_name_exist($coursesRepositorySys . $currentCourseRepository . '/group/' . $secretDirectory) )
            {
                $secretDirectory = uniqid($platform_id) . '_team_' . $lastId;
            }

            claro_mkdir($coursesRepositorySys . $currentCourseRepository . '/group/' . $secretDirectory, CLARO_FILE_PERMISSIONS);

            /* Stores the directory path into the group table */

            $sql = "UPDATE `" . $tbl_Groups . "`
                    SET name            = '" . get_lang('Group') . ' ' . $lastId . "',
#                       forumId         = '" . $forumInsertId . "',
                        secretDirectory = '" . $secretDirectory . "'
                    WHERE id ='" . $lastId . "'";

            claro_sql_query($sql);

        }    // end for ($i = 1; $i <= $group_quantity; $i++)

        $nbGroupPerUser = rand($gpumin, $gpumax);
        $tbl_CoursUsers = $TABLECOURSUSER;
        $tbl_Users      = $TABLEUSER;

        if ($group_quantity > 0)
        fill_in_groups($currentCourseId);

        //-----------------------------------------------------------

        $strWork .= '<LI>'
        .           '<strong>'
        .           '[wantedCode:           ' . $wantedCode              . ']'
        .           '</strong>'
        .           '<br />'
        .           '[Code:                 ' . $currentCourseCode       . ']'
        .           '[Id:                   ' . $currentCourseId         . ']'
        .           '[Db:                   ' . $currentCourseDbName     . ']'
        .           '[Path:                 ' . $currentCourseRepository . ']'
        .           '<br />'
        .           '[language_course:      ' . $language_course         . ']'
        .           '[faculte:              ' . $faculte                 . ']'
        .           '[uidCourse:            ' . $uidCourse               . ']'
        .           '<br />'
        .           '[nb users added:       ' . $addedUsers              . ']'
        .           '[nb group:             ' . $group_quantity          . ']'
        .           '[max student per group:' . $group_max               . ']'
        .           '</LI>'
        ;
    }
    $strWork .= '</OL>';
    $display = DISP_RESULT_INSERT;
}

//////////////// //////////////// //////////////// ////////////////
//OUTPUT
//////////////// //////////////// //////////////// ////////////////

include( $includePath . '/claro_init_header.inc.php');
echo claro_disp_tool_title( $nameTools);

switch ($display)
{
    case DISP_RESULT_INSERT :
    echo $strWork;
        ?>
            <UL class="menu">
                <LI>
                    <a href="<?php echo $_SERVER['PHP_SELF'] ?>" >Again</a>
                </LI>
                <LI>
                    <a href="<?php echo $rootAdminWeb ?>" >Admin</a>
                </LI>
            </UL>
        <?php
        break;
        case DISP_FORM_SET_OPTION :
        ?>

<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="POST" enctype="multipart/form-data" target="_self">
    <fieldset>
    <legend > <?php echo get_lang('CreateCourses') ?> </legend>
    <label for="nc"> <?php echo get_lang('Quantity') ?> </label>
    <input align="right" type="text" id="nc" name="nc" value="<?php echo $nc ?>" size="5" maxlength="3"><br />
    <label for="pfCode"> <?php echo get_lang('Prefix') ?> </label>
    <input align="right" type="text" id="pfCode" name="pfCode" value="<?php echo $pfCode ?>" size="5" maxlength="5">
    </fieldset>
    <fieldset >
    <legend > <?php echo get_lang('Student') ?> </legend>
    <Label for="smin"><?php echo get_lang('Min') ?> </Label>
    <input type="text" id="smin" align="right" name="smin" value="<?php echo $smin ?>" size="5" maxlength="3"><br />
    <Label for="smax"><?php echo get_lang('Maximum') ?> </Label>
    <input type="text" id="smax" align="right" name="smax" value="<?php echo $smax ?>" size="5" maxlength="3">
    </fieldset>
    <fieldset>
    <legend ><?php echo get_lang('Course manager'); ?> </legend>
    <Label for="pmin"> <?php echo get_lang('Min') ?> </Label>
    <input align="right" id="pmin"  type="text" name="pmin" value="<?php echo $pmin ?>" size="5" maxlength="3"><br />
    <Label for="pmax"> <?php echo get_lang('Maximum') ?> </Label>
    <input align="right" id="pmax"  type="text" name="pmax" value="<?php echo $pmax ?>" size="5" maxlength="3">
    </fieldset>
    <fieldset>
    <Label for="noLangRand">
    <input type="radio" id="noLangRand" name="random_lang" value="no" checked="checked">
    <?php echo get_lang('Only') . " " . $langNameOfLang[$platformLanguage] ?>
    </label>
    <Label for="langRand">
    <input type="radio" id="langRand" name="random_lang" value="random_lang">
    <?php echo get_lang('RandomLanguage') ?>
    </label>
    </fieldset>
    <fieldset>
    <legend ><?php echo get_lang('NumGroup'); ?> </legend>
    <Label for="gmin"> <?php echo get_lang('Min') ?> </Label>
    <input align="right" id="gmin"  type="text" name="gmin" value="<?php echo $gmin ?>" size="5" maxlength="3"><br />
    <Label for="gmax"> <?php echo get_lang('Maximum') ?> </Label>
    <input align="right" id="gmax"  type="text" name="gmax" value="<?php echo $gmax ?>" size="5" maxlength="3">
    </fieldset>
        <fieldset>
    <legend ><?php echo get_lang('MaxStudentGroup'); ?> </legend>
    <Label for="emax"> <?php echo get_lang('Maximum') ?> </Label>
    <input align="right" id="emax"  type="text" name="emax" value="<?php echo $emax ?>" size="5" maxlength="3">
    </fieldset>
        <fieldset>
    <legend ><?php echo get_lang('NumGroupStudent'); ?> </legend>
    <Label for="gpumin"> <?php echo get_lang('Min') ?> </Label>
    <input align="right" id="gpumin"  type="text" name="gmin" value="<?php echo $gpumin ?>" size="5" maxlength="3"><br />
    <Label for="gpumax"> <?php echo get_lang('Maximum') ?> </Label>
    <input align="right" id="gpumax"  type="text" name="gmax" value="<?php echo $gpumax ?>" size="5" maxlength="3">
    </fieldset>


    <input type="hidden" name="cmd" value="exFill">
    <input type="submit" name="create" value="<?php echo get_lang('Create') ?>">
</form>
        <?php
        break;
        default : "hum DISPLAY ERROR";

}

include( $includePath . '/claro_init_footer.inc.php');

/* ...functions... */
function field_rand($arr)
{
    $rand_keys = array_rand ($arr);
    return $arr[$rand_keys];
}

?>
