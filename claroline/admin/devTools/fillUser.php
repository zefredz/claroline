<?php // $Id$µ
/**
 * Claroline
 * SHUFFLE USER Insertor
 * Créateur de compte utilisateur bidon pour les tests
 *
 * insert between smin and smax students
 * insert between pmin and pmax courses admins
 *
 * @version 1.7 $Revision$
 *
 * @copyright (c) 2001-2005 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE 
 *
 * @package SDK
 *
 * @author Claro Team <cvs@claroline.net>
 * @author Christophe Gesché <moosh@claroline.net>
 *
 */
$langTeacherQty = "Quantité de prof";
$langStudentQty = "Quantité d'étudiants";
require '../../inc/claro_init_global.inc.php';

// Security check
if ( ! $_uid ) claro_disp_auth_form();
if ( ! $is_platformAdmin ) claro_die($langNotAllowed);

DEFINE('DISP_RESULT_INSERT'		, __LINE__);
DEFINE('DISP_FORM_SET_OPTION'	, __LINE__);
DEFINE('DISP_INSERT_COMPLETE'	, __LINE__);

// default_display
$display = DISP_FORM_SET_OPTION;

// constant
DEFINE('DEFAULT_MIN_QTY_STUDENT_REGISTRED_IN_COURSE',5);
DEFINE('DEFAULT_MAX_QTY_STUDENT_REGISTRED_IN_COURSE',40);
DEFINE('DEFAULT_MIN_QTY_TEACHER_REGISTRED_IN_COURSE',1);
DEFINE('DEFAULT_MAX_QTY_TEACHER_REGISTRED_IN_COURSE',5);
DEFINE('DEFAULT_SUFFIX_MAIL','@example.com');
DEFINE('DEFAULT_QTY_TEACHER',5);
DEFINE('DEFAULT_QTY_STUDENT',20);
DEFINE('ADD_FIRSTNAMES_FROM_BASE',FALSE);
DEFINE('ADD_NAMES_FROM_BASE',FALSE);
DEFINE('ADD_USERNAMES_FROM_BASE',FALSE);
DEFINE('USE_FIRSTNAMES_AS_LASTNAMES',FALSE);
DEFINE('CONFVAL_LIST_USER_ADDED',TRUE);

// Config tool
include($includePath.'/conf/course_main.conf.php');

// LIBS
include($includePath . '/lib/add_course.lib.inc.php');
include($includePath . '/lib/debug.lib.inc.php');
include($includePath . '/lib/fileManage.lib.php');
include($includePath . '/conf/course_main.conf.php');

$nameTools = $langAdd_users;

$interbredcrump[]= array ('url'=>'../index.php', 'name'=> $langAdmin);
$interbredcrump[]= array ('url'=>'index.php', 'name'=> $langDevTools);

$tbl_mdb_names = claro_sql_get_main_tbl();
$tbl_user      = $tbl_mdb_names['user'];

$can_create_courses = (bool) ($is_allowedCreateCourse);
$controlMsg = array();

// fix setting
if ( isset($_REQUEST['create']) )
{
    $nbp    = (int) $_REQUEST['nbp'];
    $nbs    = (int) $_REQUEST['nbs'];
    $sfMail = strtoupper($_REQUEST['sfMail']); 
    $nom    = $_REQUEST['nom'];
    $prenom = $_REQUEST['prenom'];
    $login  = $_REQUEST['login'];
}
else
{
    $nbp    = (int) DEFAULT_QTY_TEACHER;
    $nbs    = (int) DEFAULT_QTY_STUDENT;
    $sfMail = strtoupper(DEFAULT_SUFFIX_MAIL);
    $nom    = '';
    $prenom = '';
    $login  = '';
}
    
$nc     = (int) DEFAULT_MIN_QTY_STUDENT_REGISTRED_IN_COURSE;
$smin   = (int) DEFAULT_MIN_QTY_STUDENT_REGISTRED_IN_COURSE;
$smax   = (int) DEFAULT_MAX_QTY_STUDENT_REGISTRED_IN_COURSE;
$pmin   = (int) DEFAULT_MIN_QTY_TEACHER_REGISTRED_IN_COURSE;
$pmax   = (int) DEFAULT_MAX_QTY_TEACHER_REGISTRED_IN_COURSE;

$nbUsers = $nbp + $nbs;

$result = "";

if ( isset($_REQUEST['create']) && $nbUsers > 0 )
{
    /* fillUSER */

	$firstnames = array (
		'jean', 'marc', 'françois', 'laurent', 'mathieu', 'matthieu',
		'simon', 'pol', 'paul', 'greg', 'gregoire', 'gregory', 'albert', 'alfred',
		'adolfe', 'armile', 'armand', 'jeff', 'jo', 'jack', 'john', 'claire',
		'annie', 'cécile', 'roland', 'mark', 'koen', 'dirk', 'jan', 'kim',
		'riri', 'fifi', 'loulou', 'michel', 'robin', 'serge', 'david', 'augustin',
		'sofienne', 'lucien', 'roberto', 'analysia', 'jaana', 'satu', 'christian',
		'marie', 'julie', 'justine', 'yves', 'lucas', 'teddy', 'giovanni',
		'yurgen', 'sven', 'fabien', 'fabian', 'pierre', 'mouloud', 'kevin',
		'axel', 'hervé', 'lydéric', 'manory', 'aly', 'francis', 'charles',
		'cédric', 'quentin', 'miguel', 'khalid', 'bilal', 'dries', 'pieter',
		'kjell', 'mehdi', 'damien', 'cyril', 'michael', 'jamil', 'mustafa',
		'georges', 'christophe', 'hugues', 'thomas', 'lorant', 'stéphanie',
		'martine', 'aurélie', 'caroline', 'simone', 'nathalie', 'audette', 'carole',
		'farid', 'antonella', 'graziella', 'lauredanna',
		'lyne', 'laure', 'jean-luc', 'luc', 'Nathanaël', 'kofi', 'sigmund', 'Mateus',
		'Jesus', 'Steve', 'dave', 'alan', 'alain', 'andré', 'andrew', 'Tahar',
		'mowgli', 'tom', 'donald', 'olivier', 'dimitri', 'joseph', 'mohamed',
		'sambegou', 'björn', 'jinks', 'Gonzague', 'Onder', 'kris', 'ivan',
		'cheikh', 'taner', 'Moussa', 'Louis', 'amadou', 'arnaud', 'rosario',
		'tilio', 'julio', 'jules', 'julos', 'liviu', 'celia', 'magda', 'youssef',
		'essam', 'boumedian', 'walit', 'thierry','zeev','jamal','ali', 'mathieu', 'fred', 'renaud');

		$voyel		= array( 'a','e','i','o','u');
		$consonne	= array('','b','c','d','f','j','k','l','m','n','p','r','s','t','v','z');

	$sqlUsers = "Select * from `" . $tbl_user . "`";

	$resUsers = claro_sql_query($sqlUsers);

	while (( $users = mysql_fetch_array($resUsers, MYSQL_ASSOC) ))
	{
		if ( ADD_FIRSTNAMES_FROM_BASE )	$firstnames[] 	= $users['prenom'];
		if ( ADD_NAMES_FROM_BASE )		$names[] 		= $users['nom'];
		if ( ADD_USERNAMES_FROM_BASE )	$usernames[] 	= $users['username'];
	}

	if (USE_FIRSTNAMES_AS_LASTNAMES)	$names 	= array_merge ( $names,$firstnames);

	unset($users);

	for ( $noUser=0 ; $noUser<=($nbUsers*10) ; $noUser++ )
    {
		$nom = '';
		for( $s=0 ; $s<rand(1,3) ; $s++ )
		{
			$nom .= field_rand($consonne).field_rand($voyel).field_rand($consonne);
		}
		$names[] = $nom;
	}

    $nbssAdded = 0;

    $nbp_created = 0;

	for( $noUser=0 ; $noUser<$nbUsers ; $noUser++ )
	{
		if ($nbp_created < $nbp) $statut = 1;
        else                     $statut = 5;

		$nom      = ucfirst(strToLower(field_rand($names)));
		$prenom   = ucfirst(strToLower(field_rand($firstnames)));
		$username = strToLower($nom);
		$password = strToLower($nom.$prenom);
		$email    = strToLower($prenom . '.' . $noUser) . $sfMail;

		$sqlInsertUser = "
        	INSERT INTO `".$tbl_user."`
            	(
        	    `nom`, 
        	    `prenom`,
            	`username`, `password`,
            	`email`, `statut`,
            	`creatorId`)
        	VALUES
        	('".$nom."', '".$prenom."',
        	'".$username."', '".$password."',
        	'".$email."', $statut,
        	'".$_uid."')
		";
		claro_sql_query($sqlInsertUser);

		$nbssAdded += mysql_affected_rows();
		$users[] = $prenom . ' ' . $nom . ', L/P ' . $username . ' / ' . $password;

        $nbp_created++;
	}
	$display = DISP_RESULT_INSERT;
}

// OUTPUT

include($includePath . '/claro_init_header.inc.php');

echo claro_disp_tool_title( array('mainTitle'=>$nameTools));

if ( count($controlMsg) )
{
    claro_disp_msg_arr($controlMsg);
}

switch ($display)
{
	case DISP_RESULT_INSERT :
        echo $lang_you_had_request; ?> :
		<UL>
			<LI>
				<?php echo $nbp . ' ' . $langTeachers; ?></LI>
			<LI>
				<?php echo $nbs . ' ' . $langStudents; ?>
			</LI>
		</UL>
<?php
			echo $nbssAdded.' new users';
			if ( CONFVAL_LIST_USER_ADDED )
			{
				echo '<ol>' . "\n" 
                    . '<li>' 
				    . implode('</LI>'."\n".'<LI>',$users)
				    . '</li>' . "\n" 
                    .  '</ol>' . "\n";
			}

?>
			<UL class="menu">
				<LI>
					<a href="<?php echo $_SERVER['PHP_SELF'] ?>" ><?php echo $langAgain; ?></a>
				</LI>
				<LI>
					<a href="<?php echo $rootAdminWeb ?>" ><?php echo $langAdmin; ?></a>
				</LI>
			</UL>
		<?php
		break;
	case DISP_FORM_SET_OPTION :
		?>
<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="POST" enctype="multipart/form-data" target="_self">
	<fieldset>
		<legend >Comptes à créer</legend>
		<table class="claroTable" >
			<tr>
				<th >
					<label for="nbp"><?php echo $langTeacherQty ?>  : </label>
				</th>
				<td>
					<input align="right" type="text" id="nbp" name="nbp" value="<?php echo $nbp ?>" size="5" maxlength="3">
				</td>
			</tr>
			<tr>
				<th>				
					<label for="nbs"><?php echo $langStudentQty ?> : </label>
				</th>
				<td>
					<input align="right" type="text" id="nbs" name="nbs" value="<?php echo $nbs ?>" size="5" maxlength="4">
				</td>
			</tr>
		</table>
	</fieldset>
	<fieldset >
		<legend >Données</legend>
		<table class="claroTable" >
			<tr>
				<th >
					Nom : 
				</th>
				<td>
					<div>
						<input type="radio" id="selNameRandom" name="selName" value="rand"  checked="checked">
						<Label for="selNameRandom" >Random</Label>
					</div>
					<div>
   			    	    <input type="radio" name="selName" value="fix"><input type="text" id="nom" align="right" name="nom" value="<?php echo $nom ?>" size="10" maxlength="25"><br>
					</div>
				</td>
			</tr>
			<tr>
				<th >
					<?php echo $langFirstName; ?>
				</th>
				<td>
					<div>
						<input type="radio" id="selFirstnameRandom" name="selFirstname" value="rand" checked="checked"  >
						<Label for="selFirstnameRandom" >Random</Label>
					</div>
					<div>
						<input type="radio" name="selFirstname" value="fix">
						<input type="text" id="prenom" align="right" name="prenom" value="<?php echo $prenom ?>" size="10" maxlength="25"><br>
					</div>
				</td>
			</tr>
			<tr>
				<th valign="top" >
					<?php echo $langLogin; ?>
				</th>
				<td>
					<div>
						<input type="radio" id="selLoginRandom" name="selLogin" value="rand"  checked="checked">
						<Label for="selLoginRandom" >Random</Label>
					</div>
					<div>
						<input type="radio" id="" name="selLogin" value="name">
						<Label for="selFirstnameRandom" ><?php echo $langName ?></Label>
					</div>
					<div>
						<input type="radio" id="" name="selLogin" value="firstname">
						<Label for="selFirstnameRandom" ><?php echo $langFirstName ?></Label>
					</div>
					<div>
						<input type="radio" name="selLogin" value="fix">
						<input type="text" id="selLoginFix" align="right" name="login" value="" size="10" maxlength="25">
						<Label for="selLoginFix" ><?php echo $langFree ?></Label>
					</div>
				</td>
			</tr>
			<tr>
				<th >
					<?php echo $langEmail; ?>
				</th>
				<td>
					<div>
						<input type="text" id="sfMail" name="sfMail" value="<?php echo $sfMail ?>" size="30" maxlength="35"><br>
					</div>
				</td>
			</tr>
		</table>
	</fieldset>29
	<input type="submit" name="create" value="create">
</form>
		<?php
		break;
	default : "hum erreur de display";

}

include($includePath . '/claro_init_footer.inc.php');

function field_rand($arr)
{
    $keys = array_rand($arr);
	return $arr[$keys] ;
}

?>
