<?php // $Id$
/**
 * add_course lib contain function to add a course
 * add is, find keys names aivailable, build the the course database
 * fill the course database, build the content directorys, build the index page
 * build the directory tree, register the course.
 */

/**
 * with  the WantedCode we can define the 4 keys  to find courses datas
 * @param string prefix //  prefix added  for ALL keys
 */

function define_course_keys ($wantedCode,          $prefix4all="",
                             $prefix4baseName ="", $prefix4path="",
							 $addUniquePrefix = false,
							 $useCodeInDepedentKeys = TRUE	)
{
	// What do better :
	// actually if suffix is not unique   the next append and not  replace
	GLOBAL $TABLECOURSE, $coursesRepositories,$prefixAntiNumber,$prefixAntiEmpty,$DEBUG;

	$nbCharFinalSuffix = 4 ; // Number of car to add on end of key

	if ($coursesRepositories == "")
	{
	};
/*
	When  need to compute 4 keys
	Public
	$keys["currentCourseCode"] 			: Must be alphaNumeric and outputable in HTML
	System
	$keys["currentCourseId"]			: Must be unique in mainDb.course it's the primary key
	$keys["currentCourseDbName"]		: Must be unique it's the database name.
	$keys["currentCourseRepository"]	: Must be unique in /$coursesRepositories/

	if  $useWantedCodeInDepedentKeys = true is used in systems keys
*/

	// $keys["currentCourseCode"] is the "public code"

	$wantedCode =  strtr($wantedCode,
	"ÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝßàáâãäåæçèéêëìíîïðñòóôõöøùúûüýÿ",
	"AAAAAACEEEEIIIIDNOOOOOOUUUUYsaaaaaaaceeeeiiiionoooooouuuuyy");

	$wantedCode = ereg_replace("[^A-Z0-9]","",strtoupper($wantedCode));

	if ($wantedCode=="") $wantedCode = $prefixAntiEmpty;

	$keysCourseCode	= $wantedCode;

	if (!$useCodeInDepedentKeys) $wantedCode = "";

	// $keys["currentCourseId"] would Became $cid in normal using.

    if ($addUniquePrefix) $uniquePrefix =  substr(md5 (uniqid (rand())),0,10);
    if ($addUniqueSuffix) $uniqueSuffix =  substr(md5 (uniqid (rand())),0,10);

	$keysAreUnique = FALSE;

	unset($finalSuffix);

	while (!$keysAreUnique)
	{
		$keysCourseId			= $prefix4all.$uniquePrefix.strtoupper($wantedCode).$uniqueSuffix.$finalSuffix['CourseId'];

		$keysCourseDbName		= $prefix4baseName.$uniquePrefix.strtoupper($keysCourseId).$uniqueSuffix.$finalSuffix['CourseDb'];

		$keysCourseRepository 	= $prefix4path.$uniquePrefix.strtoupper($wantedCode).$uniqueSuffix.$finalSuffix['CourseDir'];

		$keysAreUnique = TRUE;
		// Now we go to check if there are unique

		$sqlCheckCourseId    = "SELECT COUNT(code) existAllready
		                        FROM `".$TABLECOURSE."`
								WHERE code = '".$keysCourseId."'";

		$resCheckCourseId    = mysql_query ($sqlCheckCourseId);
		$isCheckCourseIdUsed = mysql_fetch_array($resCheckCourseId);

		if ($isCheckCourseIdUsed[0]['existAllready'] > 0)
		{
			$keysAreUnique = FALSE;
			$tryNewFSCId++;
			$finalSuffix["CourseId"]	= substr(md5 (uniqid (rand())),0,$nbCharFinalSuffix);
			if ($DEBUG) echo "[id]";
		};

		$sqlCheckCourseDb = "SHOW DATABASES LIKE '".$keysCourseDbName."'";

		$resCheckCourseDb = mysql_query ($sqlCheckCourseDb);

		$isCheckCourseDbUsed = mysql_num_rows($resCheckCourseDb);

		if ($isCheckCourseDbUsed>0)
		{
			$keysAreUnique = FALSE;
			$tryNewFSCDb++;
			$finalSuffix['CourseDb']	= substr('_'.md5 (uniqid (rand())),0,$nbCharFinalSuffix);
			if ($DEBUG) echo "[db:$isCheckCourseDbUsed:$sqlCheckCourseDb]";
		};

		if (file_exists($coursesRepositories."/".$keysCourseRepository))
		{
			$keysAreUnique = FALSE;
			$tryNewFSCDir++;
			$finalSuffix["CourseDir"]	= substr(md5 (uniqid (rand())),0,$nbCharFinalSuffix);
			if ($DEBUG) echo "[dir]";
		};
	}

	// here  we  can add a  counter  to exit  if need too many try
	$limitNumbTry = 128;

	if (($tryNewFSCId+$tryNewFSCDb+$tryNewFSCDir > $limitNumbTry)
			or ($tryNewFSCId > $limitNumbTry / 2 )
			or ($tryNewFSCDb > $limitNumbTry / 2 )
			or ($tryNewFSCDir > $limitNumbTry / 2 )
		)
	{
		return false;
	}

	// dbName Can't begin with a number
	if (!strstr("abcdefghijklmnopqrstuvwyzABCDEFGHIJKLMNOPQRSTUVWXYZ",$keysCourseDbName[0]))
	{
		$keysCourseDbName = $prefixAntiNumber.$keysCourseDbName;
	}

	//
	$keys["currentCourseCode"]		= $keysCourseCode; 		// screen code
	$keys["currentCourseId"]		= $keysCourseId;		// sysCode
	$keys["currentCourseDbName"]	= $keysCourseDbName;	// dbname
	$keys["currentCourseRepository"]= $keysCourseRepository;// append to course repository
	return $keys;
};

/**
 * function prepare_course_repository($courseRepository, $courseId)
 * @desc create directory used by course.
 *
 * @param	string	$courseRepository		path from $coursesRepositorySys to root of course
 * @param	string	$courseId				sysId of course
 * @GLOBAL	string	$coursesRepositorySys	path to root of courses
 *
 * @author	Christophe Gesché <moosh@tiscali.be>
 * @version	1.0
 */
function prepare_course_repository($courseRepository, $courseId)
{
	GLOBAL $coursesRepositorySys, $clarolineRepositorySys, $includePath;
  if( !is_dir($coursesRepositorySys) )
  {
    mkpath($coursesRepositorySys);
  }
	if (is_writable($coursesRepositorySys))
	{
		umask(0);

		/*
			here would come new section of code to
			read in tools table witch directories to create
		*/
		mkdir($coursesRepositorySys.$courseRepository, 0777);
		mkdir($coursesRepositorySys.$courseRepository."/image", 0777);
		mkdir($coursesRepositorySys.$courseRepository."/document", 0777);
		mkdir($coursesRepositorySys.$courseRepository."/page", 0777);
		mkdir($coursesRepositorySys.$courseRepository."/work", 0777);
		mkdir($coursesRepositorySys.$courseRepository."/group", 0777);
		mkdir($coursesRepositorySys.$courseRepository."/chat", 0777);

		mkdir($coursesRepositorySys.$courseRepository."/modules", 0777);
		mkdir($coursesRepositorySys.$courseRepository."/scormPackages", 0777);

		mkdir($coursesRepositorySys.$courseRepository."/modules/module_1", 0777);
		// for sample learning path <- probably to delete .


		// build index.php of course
		$fd=fopen($coursesRepositorySys.$courseRepository."/index.php", "w");

		// str_replace() removes \r that cause squares to appear at the end of each line
		$string=str_replace("\r","","<?"."php
//		session_start();
	\$cidReq = \"$courseId\";
  \$claroGlobalPath = \"$includePath\";
	include(\"".$clarolineRepositorySys."course_home/course_home.php\");
	?>");
		fwrite($fd, "$string");
		$fd=fopen($coursesRepositorySys.$courseRepository."/group/index.php", "w");
		$string="<"."?"."php"." session_start"."()"."; ?>";
		fwrite($fd, "$string");
		return 0;
	}
	else
	{
		GLOBAL $rootWeb,
		$siteName,
		$administrator;
		die("
		<B>prepare_course_repository</B> in
		<small><I>".__FILE__."</I></small>
		can't create dir,
		<br>
		<br>
		Please contact file system admin :
		<big><U>".$administrator['name']."</U></big>
		<ul>
			<li>
				to phone : ".$administrator['phone']."
			</li>
			<li>
				or <a href=\"mailto:".$administrator['email']."\" >".$administrator['email']."</A>
			</LI>
		</ul>
		and
		<UL>
		<LI>request  to php an write access on <U>".$coursesRepositorySys."</U></LI>
		<LI>or check \$rootSys and  \$coursesRepositorySys
			in <U>/inc/conf/claro_main.conf.php</U></LI>
		</UL>

		<a href=\"".$rootWeb."\" >BACK TO ".$siteName."</a>
		");
		return 1;
	}

};

/**
 * function update_Db_course()
 *
 * @desc Add starting files in course
 *
 * @param	string	$courseDbName	partial dbName form course table tu build real DbName
 * @GLOBAL	boolean	$singleDbEnabled	whether all campus use only one DB
 * @GLOBAL	string	$courseTablePrefix	common prefix for all table of courses
 * @GLOBAL	string	$dbGlu				glu between logical name of DB and  logical name of table *
 *
 * @author	Christophe Gesché moosh@tiscali.be
 * @version 1.0
 */

function update_Db_course($courseDbName)
{
	global $singleDbEnabled, $courseTablePrefix, $dbGlu;

	if(!$singleDbEnabled)
	{
		mysql_query("CREATE DATABASE $courseDbName");
		if (mysql_errno()>0)
			return CLARO_ERROR_CANT_CREATE_DB;
	}

	$courseDbName=$courseTablePrefix.$courseDbName.$dbGlu;


	/*
		Here function claro_sql_get_course_tbl() from main lib would be
		called to replace the table name assignement
	*/

	$TABLECOURSEHOMEPAGE	= $courseDbName."tool_list";
	$TABLEINTROS			= $courseDbName."tool_intro";

	$TABLEGROUPS			= $courseDbName."group_team";
	$TABLEGROUPUSER			= $courseDbName."group_rel_team_user";
	$TABLEGROUPPROPERTIES	= $courseDbName."group_property";

	$TABLETOOLUSERINFOCONTENT	= $courseDbName."userinfo_content";
	$TABLETOOLUSERINFODEF		= $courseDbName."userinfo_def";

	$TABLETOOLCOURSEDESC	= $courseDbName."course_description";
	$TABLETOOLAGENDA		= $courseDbName."calendar_event";
	$TABLETOOLANNOUNCEMENTS	= $courseDbName."announcement";
	$TABLETOOLWORKS			= $courseDbName."assignment_doc";
	$TABLETOOLWORKSUSER		= $courseDbName."work_student";
	$TABLETOOLDOCUMENT		= $courseDbName."document";

	//$TABLETOOLLINK			= $courseDbName."link";

	$TABLEQUIZ				= $courseDbName."quiz_test";
	$TABLEQUIZQUESTION		= $courseDbName."quiz_rel_test_question";
	$TABLEQUIZQUESTIONLIST	= $courseDbName."quiz_question";
	$TABLEQUIZANSWERSLIST	= $courseDbName."quiz_answer";

	$TABLEPHPBBACCESS		= $courseDbName."bb_access";
	$TABLEPHPBBBANLIST		= $courseDbName."bb_banlist";
	$TABLEPHPBBCATEGORIES	= $courseDbName."bb_categories";
	$TABLEPHPBBCONFIG		= $courseDbName."bb_config";
	$TABLEPHPBBDISALLOW		= $courseDbName."bb_disallow";
	$TABLEPHPBBFORUMACCESS	= $courseDbName."bb_forum_access";
	$TABLEPHPBBFORUMMODS	= $courseDbName."bb_forum_mods";
	$TABLEPHPBBFORUMS		= $courseDbName."bb_forums";
	$TABLEPHPBBHEADFOOT		= $courseDbName."bb_headermetafooter";
	$TABLEPHPBBDOMAINLIST	= $courseDbName."liste_domaines";
	$TABLEPHPBBPAGES		= $courseDbName."pages";
	$TABLEPHPBBPOSTS		= $courseDbName."bb_posts";
	$TABLEPHPBBPOSTSTEXT	= $courseDbName."bb_posts_text";
	$TABLEPHPBBPRIVMSG		= $courseDbName."bb_priv_msgs";
	$TABLEPHPBBRANK			= $courseDbName."bb_ranks";
	$TABLEPHPBBSESSIONS		= $courseDbName."bb_sessions";
	$TABLEPHPBBTHEMES		= $courseDbName."bb_themes";
	$TABLEPHPBBTOPICS		= $courseDbName."bb_topics";
	$TABLEPHPBBUSERS		= $courseDbName."bb_users";
	$TABLEPHPBBWHOSONLINE	= $courseDbName."bb_whosonline";
	$TABLEPHPBBWORDS		= $courseDbName."bb_words";
    $TABLEPHPBBNOTIFY       = $courseDbName."bb_rel_topic_userstonotify"; //added for notification by email sytem for claroline 1.5

  $TABLELEARNPATH         = $courseDbName."lp_learnPath";
  $TABLEMODULE            = $courseDbName."lp_module";
  $TABLELEARNPATHMODULE   = $courseDbName."lp_rel_learnPath_module";
  $TABLEASSET             = $courseDbName."lp_asset";
  $TABLEUSERMODULEPROGRESS= $courseDbName."lp_user_module_progress";
  // stats
  $TABLETRACKACCESS        = $courseDbName."track_e_access";
  $TABLETRACKDOWNLOADS     = $courseDbName."track_e_downloads";
  $TABLETRACKUPLOADS       = $courseDbName."track_e_uploads";
  $TABLETRACKLINKS         = $courseDbName."track_e_links";
  $TABLETRACKEXERCICES     = $courseDbName."track_e_exercices";

		$sql ="
CREATE TABLE `".$TABLETOOLANNOUNCEMENTS."` (
  `id` mediumint(11) NOT NULL auto_increment,
  `title` varchar(80) default NULL,
  `contenu` text,
  `temps` date default NULL,
  `ordre` mediumint(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM COMMENT='announcements table'";
mysql_query($sql);

		$sql ="
CREATE TABLE `".$TABLETOOLUSERINFOCONTENT."` (
   `id` int(10) unsigned NOT NULL auto_increment,
   `user_id` mediumint(8) unsigned NOT NULL default '0',
   `def_id` int(10) unsigned NOT NULL default '0',
   `ed_ip` varchar(39) default NULL,
   `ed_date` datetime default NULL,
   `content` text,
   PRIMARY KEY  (`id`),
   KEY `user_id` (`user_id`)
) TYPE=MyISAM COMMENT='content of users information - organisation based on
userinf'";

mysql_query($sql);

		$sql ="
CREATE TABLE `".$TABLETOOLUSERINFODEF."` (
   `id` int(10) unsigned NOT NULL auto_increment,
   `title` varchar(80) NOT NULL default '',
   `comment` varchar(160) default NULL,
   `nbLine` int(10) unsigned NOT NULL default '5',
   `rank` tinyint(3) unsigned NOT NULL default '0',
   PRIMARY KEY  (`id`)
) TYPE=MyISAM COMMENT='categories definition for user information of a course'";
mysql_query($sql);

		$sql ="
	CREATE
	TABLE `".$TABLEPHPBBACCESS."`
	(
		access_id int(10) NOT NULL auto_increment,
		access_title varchar(20),
	PRIMARY KEY (access_id)
	)";
mysql_query($sql);

mysql_query("
	CREATE TABLE `".$TABLEPHPBBBANLIST."` (
		ban_id int(10) NOT NULL auto_increment,
		ban_userid int(10),
		ban_ip varchar(16),
		ban_start int(32),
		ban_end int(50),
		ban_time_type int(10),
	PRIMARY KEY (ban_id),
		KEY ban_id (ban_id)
	)");
mysql_query("

	CREATE TABLE `".$TABLEPHPBBCATEGORIES."` (
		cat_id int(10) NOT NULL auto_increment,
		cat_title varchar(100),
		cat_order varchar(10),
	PRIMARY KEY (cat_id)
	)");
mysql_query("
	CREATE TABLE `".$TABLEPHPBBCONFIG."`
	(
		config_id int(10) NOT NULL auto_increment,
		sitename varchar(100),
		allow_html int(2),
		allow_bbcode int(2),
		allow_sig int(2),
		allow_namechange int(2) DEFAULT '0',
		admin_passwd varchar(32),
		selected int(2) DEFAULT '0' NOT NULL,
		posts_per_page int(10),
		hot_threshold int(10),
		topics_per_page int(10),
		allow_theme_create int(10),
		override_themes int(2) DEFAULT '0',
		email_sig varchar(255),
		email_from varchar(100),
		default_lang varchar(255),
	PRIMARY KEY (config_id),
		UNIQUE selected (selected)
	)");
mysql_query("
	CREATE TABLE `".$TABLEPHPBBDISALLOW."`(
		disallow_id int(10) NOT NULL auto_increment,
		disallow_username varchar(50),
	PRIMARY KEY (disallow_id)
	)");
mysql_query("
	CREATE TABLE `".$TABLEPHPBBFORUMACCESS."`(
		forum_id int(10) DEFAULT '0' NOT NULL,
		user_id int(10) DEFAULT '0' NOT NULL,
		can_post tinyint(1) DEFAULT '0' NOT NULL,
	PRIMARY KEY (forum_id, user_id)
	)");
mysql_query("
	CREATE TABLE `".$TABLEPHPBBFORUMMODS."`(
		forum_id int(10) DEFAULT '0' NOT NULL,
		user_id int(10) DEFAULT '0' NOT NULL
	)");
mysql_query("
	CREATE TABLE `".$TABLEPHPBBFORUMS."`(
		forum_id int(10) NOT NULL auto_increment,
		forum_name varchar(150),
		forum_desc text,
		forum_access int(10) DEFAULT '1',
		forum_moderator int(10),
		forum_topics int(10) DEFAULT '0' NOT NULL,
		forum_posts int(10) DEFAULT '0' NOT NULL,
		forum_last_post_id int(10) DEFAULT '0' NOT NULL,
		cat_id int(10),
		forum_type int(10) DEFAULT '0',
		md5 varchar(32) NOT NULL,
	PRIMARY KEY (forum_id),
		KEY forum_last_post_id (forum_last_post_id),
        forum_order int(10) DEFAULT '0'
	)");
mysql_query("
	CREATE TABLE `".$TABLEPHPBBHEADFOOT."`(
		header text,
		meta text,
		footer text
	)");
mysql_query("
	CREATE TABLE `".$TABLEPHPBBPOSTS."`(
		post_id int(10) NOT NULL auto_increment,
		topic_id int(10) DEFAULT '0' NOT NULL,
		forum_id int(10) DEFAULT '0' NOT NULL,
		poster_id int(10) DEFAULT '0' NOT NULL,
		post_time varchar(20),
		poster_ip varchar(16),
		nom varchar(30),
		prenom varchar(30),
	PRIMARY KEY (post_id),
		KEY post_id (post_id),
		KEY forum_id (forum_id),
		KEY topic_id (topic_id),
		KEY poster_id (poster_id)
	)");

//  Structure de la table 'priv_msgs'
mysql_query("
	CREATE TABLE `".$TABLEPHPBBPRIVMSG."` (
		msg_id int(10) NOT NULL auto_increment,
		from_userid int(10) DEFAULT '0' NOT NULL,
		to_userid int(10) DEFAULT '0' NOT NULL,
		msg_time varchar(20),
		poster_ip varchar(16),
		msg_status int(10) DEFAULT '0',
		msg_text text,
	PRIMARY KEY (msg_id),
		KEY msg_id (msg_id),
		KEY to_userid (to_userid)
	)");

//  Structure de la table 'ranks'
mysql_query("
	CREATE TABLE `".$TABLEPHPBBRANK."` (

		rank_id int(10) NOT NULL auto_increment,
		rank_title varchar(50) NOT NULL,
		rank_min int(10) DEFAULT '0' NOT NULL,
		rank_max int(10) DEFAULT '0' NOT NULL,
		rank_special int(2) DEFAULT '0',
		rank_image varchar(255),
	PRIMARY KEY (rank_id),
		KEY rank_min (rank_min),
		KEY rank_max (rank_max)
	)");

//  structure de la table 'session'
mysql_query("
	CREATE TABLE `".$TABLEPHPBBSESSIONS."` (
		sess_id int(10) unsigned DEFAULT '0' NOT NULL,
		user_id int(10) DEFAULT '0' NOT NULL,
		start_time int(10) unsigned DEFAULT '0' NOT NULL,
		remote_ip varchar(15) NOT NULL,
	PRIMARY KEY (sess_id),
		KEY sess_id (sess_id),
		KEY start_time (start_time),
		KEY remote_ip (remote_ip)
	)");

//  Structure de la table 'themes'
mysql_query("
	CREATE TABLE `".$TABLEPHPBBTHEMES."` (
		theme_id int(10) NOT NULL auto_increment,
		theme_name varchar(35),
		bgcolor varchar(10),
		textcolor varchar(10),
		color1 varchar(10),
		color2 varchar(10),
		table_bgcolor varchar(10),
		header_image varchar(50),
		newtopic_image varchar(50),
		reply_image varchar(50),
		linkcolor varchar(15),
		vlinkcolor varchar(15),
		theme_default int(2) DEFAULT '0',
		fontface varchar(100),
		fontsize1 varchar(5),
		fontsize2 varchar(5),
		fontsize3 varchar(5),
		fontsize4 varchar(5),
		tablewidth varchar(10),
		replylocked_image varchar(255),
	PRIMARY KEY (theme_id)
	)");

//  Structure de la table 'topics'
mysql_query("
	CREATE TABLE `".$TABLEPHPBBTOPICS."` (
		topic_id int(10) NOT NULL auto_increment,
		topic_title varchar(100),
		topic_poster int(10),
		topic_time varchar(20),
		topic_views int(10) DEFAULT '0' NOT NULL,
		topic_replies int(10) DEFAULT '0' NOT NULL,
		topic_last_post_id int(10) DEFAULT '0' NOT NULL,
		forum_id int(10) DEFAULT '0' NOT NULL,
		topic_status int(10) DEFAULT '0' NOT NULL,
		topic_notify int(2) DEFAULT '0',
		nom varchar(30),
		prenom varchar(30),
	PRIMARY KEY (topic_id),
		KEY topic_id (topic_id),
		KEY forum_id (forum_id),
		KEY topic_last_post_id (topic_last_post_id)
	)");

//  Structure de la table 'users'
mysql_query("
	CREATE TABLE `".$TABLEPHPBBUSERS."` (
		user_id int(10) NOT NULL auto_increment,
		username varchar(40) NOT NULL,
		user_regdate varchar(20) NOT NULL,
		user_password varchar(32) NOT NULL,
		user_email varchar(50),
		user_icq varchar(15),
		user_website varchar(100),
		user_occ varchar(100),
		user_from varchar(100),
		user_intrest varchar(150),
		user_sig varchar(255),
		user_viewemail tinyint(2),
		user_theme int(10),
		user_aim varchar(18),
		user_yim varchar(25),
		user_msnm varchar(25),
		user_posts int(10) DEFAULT '0',
		user_attachsig int(2) DEFAULT '0',
		user_desmile int(2) DEFAULT '0',
		user_html int(2) DEFAULT '0',
		user_bbcode int(2) DEFAULT '0',
		user_rank int(10) DEFAULT '0',
		user_level int(10) DEFAULT '1',
		user_lang varchar(255),
		user_actkey varchar(32),
		user_newpasswd varchar(32),
	PRIMARY KEY (user_id)
	)");

//  Structure de la table 'whosonline'
mysql_query("
	CREATE TABLE `".$TABLEPHPBBWHOSONLINE."` (
		id int(3) NOT NULL auto_increment,
		ip varchar(255),
		name varchar(255),
		count varchar(255),
		date varchar(255),
		username varchar(40),
		forum int(10),
	PRIMARY KEY (id)
	)");

//  Structure de la table 'words'
mysql_query("
	CREATE TABLE `".$TABLEPHPBBWORDS."` (
		word_id int(10) NOT NULL auto_increment,
		word varchar(100),
		replacement varchar(100),
	PRIMARY KEY (word_id)
	)");
mysql_query("CREATE TABLE `".$TABLEPHPBBNOTIFY."` (
  `notify_id` int(10) NOT NULL auto_increment,
  `user_id` int(10) NOT NULL default '0',
  `topic_id` int(10) NOT NULL default '0',
  PRIMARY KEY  (`notify_id`),
  KEY `SECONDARY` (`user_id`,`topic_id`)
  ) ");



//  EXERCICES
mysql_query("
	CREATE TABLE `".$TABLEQUIZ."` (
		id mediumint(8) unsigned NOT NULL auto_increment,
		titre varchar(200) NOT NULL,
		description text NOT NULL,
		type tinyint(4) unsigned NOT NULL default '1',
		random smallint(6) NOT NULL default '0',
		active tinyint(4) unsigned NOT NULL default '0',
	PRIMARY KEY  (id)
	)");

//  QUESTIONS
mysql_query("
	CREATE TABLE `".$TABLEQUIZQUESTIONLIST."` (
		id mediumint(8) unsigned NOT NULL auto_increment,
		question varchar(200) NOT NULL,
		description text NOT NULL,
		ponderation smallint(5) unsigned default NULL,
		q_position mediumint(8) unsigned NOT NULL default '1',
		type tinyint(3) unsigned NOT NULL default '2',
                picture_name varchar(50) default '',
	PRIMARY KEY  (id)
	)");

//  REPONSES
mysql_query("
	CREATE TABLE `".$TABLEQUIZANSWERSLIST."` (
		id mediumint(8) unsigned NOT NULL default '0',
		question_id mediumint(8) unsigned NOT NULL default '0',
		reponse text NOT NULL,
		correct mediumint(8) unsigned default NULL,
		comment text default NULL,
		ponderation smallint(5) default NULL,
		r_position mediumint(8) unsigned NOT NULL default '1',
	PRIMARY KEY  (id, question_id)
	)");

//  EXERCICE_QUESTION
mysql_query("
	CREATE TABLE `".$TABLEQUIZQUESTION."` (
		question_id mediumint(8) unsigned NOT NULL default '0',
		exercice_id mediumint(8) unsigned NOT NULL default '0',
	PRIMARY KEY  (question_id,exercice_id)
	)");

#######################COURSE_DESCRIPTION ################################
mysql_query("
	CREATE TABLE `".$TABLETOOLCOURSEDESC."` (
		`id` TINYINT UNSIGNED DEFAULT '0' NOT NULL,
		`title` VARCHAR(255),
		`content` TEXT,
		`upDate` DATETIME NOT NULL,
		UNIQUE (`id`)
	)
	COMMENT = 'for course description tool';");

####################### TOOL_LIST ###########################################
mysql_query("
    CREATE TABLE `".$TABLECOURSEHOMEPAGE."` (
      `id` int(11) NOT NULL auto_increment,
      `tool_id` int(10) unsigned default NULL,
      `rank` int(10) unsigned NOT NULL,
      `access` enum('ALL','PLATFORM_MEMBER','COURSE_MEMBER','COURSE_TUTOR','GROUP_MEMBER','GROUP_TUTOR','COURSE_ADMIN','PLATFORM_ADMIN') NOT NULL default 'ALL',
      `script_url` varchar(255) default NULL,
      `script_name` varchar(255) default NULL,
      PRIMARY KEY  (`id`)) ");

mysql_query("ALTER TABLE `".$TABLECOURSEHOMEPAGE."` ADD `addedTool` ENUM('YES','NO') DEFAULT 'YES';");

#################################### AGENDA ################################
mysql_query("
	CREATE TABLE `".$TABLETOOLAGENDA."` (
		id int(11) NOT NULL auto_increment,
		titre varchar(200),
		contenu text,
		day date NOT NULL default '0000-00-00',
		hour time NOT NULL default '00:00:00',
		lasting varchar(20),
	PRIMARY KEY (id))");

############################# PAGES ###########################################
mysql_query("
	CREATE TABLE `".$TABLEPHPBBPAGES."` (
		id int(11) NOT NULL auto_increment,
		url varchar(200),
		titre varchar(200),
		description text,
	PRIMARY KEY (id))");

############################# DOCUMENTS ###########################################
mysql_query ("
	CREATE TABLE `".$TABLETOOLDOCUMENT."` (
		id int(4) NOT NULL auto_increment,
		path varchar(255) NOT NULL,
		visibility char(1) DEFAULT 'v' NOT NULL,
		comment varchar(255),
	PRIMARY KEY (id))");

############################# WORKS ###########################################
mysql_query("

	CREATE TABLE `".$TABLETOOLWORKS."` (
	id int(11) NOT NULL auto_increment,
		url varchar(200),
		titre varchar(200),
		description varchar(250),
		auteurs varchar(200),
		active tinyint(1),
		accepted tinyint(1),
	PRIMARY KEY (id))");
mysql_query("
	CREATE TABLE `".$TABLETOOLWORKSUSER."` (
	work_id int(11) NOT NULL,
		uname varchar(30),
	PRIMARY KEY  (work_id,uname)	)");

############################## LIENS #############################################
/*
mysql_query("
	CREATE TABLE `".$TABLETOOLLINK."` (
		id int(11) NOT NULL auto_increment,
		url varchar(150),
		titre varchar(150),
		description text,
	PRIMARY KEY (id))");
*/
mysql_query("

	CREATE TABLE `".$TABLEGROUPS."` (
	id int(11) NOT NULL auto_increment,
		name varchar(100) default NULL,
		description text,
		tutor int(11) default NULL,
		forumId int(11) default NULL,
		maxStudent int(11) NOT NULL default '0',
		secretDirectory varchar(30) NOT NULL default '0',
	PRIMARY KEY  (id)
	)");
mysql_query("
	CREATE TABLE `".$TABLEGROUPUSER."` (
		id int(11) NOT NULL auto_increment,
		user int(11) NOT NULL default '0',
		team int(11) NOT NULL default '0',
		status int(11) NOT NULL default '0',
		role varchar(50) NOT NULL default '',
	PRIMARY KEY  (id)
	)");
mysql_query("
	CREATE TABLE `".$TABLEGROUPPROPERTIES."` (
	id tinyint(4) NOT NULL auto_increment,
		self_registration tinyint(4) default '1',
		`nbGroupPerUser` TINYINT UNSIGNED DEFAULT '1',
		private tinyint(4) default '0',
		forum tinyint(4) default '1',
		document tinyint(4) default '1',
		wiki tinyint(4) default '0',
		chat tinyint(4) default '1',
	PRIMARY KEY  (id)
	)");

############################## INTRODUCTION #######################################
mysql_query("
	CREATE TABLE `".$TABLEINTROS."` (
	id int(11) NOT NULL default '1',
		texte_intro text,
	PRIMARY KEY (id))");

############################# LEARNING PATHS ######################################
mysql_query     ("
         CREATE TABLE `".$TABLEMODULE."` (
              `module_id` int(11) NOT NULL auto_increment,
              `name` varchar(255) NOT NULL default '',
              `comment` text NOT NULL,
              `accessibility` enum('PRIVATE','PUBLIC') NOT NULL default 'PRIVATE',
              `startAsset_id` int(11) NOT NULL default '0',
              `contentType` enum('CLARODOC','DOCUMENT','EXERCISE','HANDMADE','SCORM','LABEL') NOT NULL,
              `launch_data` text NOT NULL,
              PRIMARY KEY  (`module_id`)
            ) TYPE=MyISAM COMMENT='List of available modules used in learning paths';");

mysql_query  ("
          CREATE TABLE `".$TABLELEARNPATH."` (
              `learnPath_id` int(11) NOT NULL auto_increment,
              `name` varchar(255) NOT NULL default '',
              `comment` text NOT NULL,
              `lock` enum('OPEN','CLOSE') NOT NULL default 'OPEN',
              `visibility` enum('HIDE','SHOW') NOT NULL default 'SHOW',
              `rank` int(11) NOT NULL default '0',
              PRIMARY KEY  (`learnPath_id`),
              UNIQUE KEY rank (`rank`)
            ) TYPE=MyISAM COMMENT='List of learning Paths';");
mysql_query ("
          CREATE TABLE `".$TABLELEARNPATHMODULE."` (
                `learnPath_module_id` int(11) NOT NULL auto_increment,
                `learnPath_id` int(11) NOT NULL default '0',
                `module_id` int(11) NOT NULL default '0',
                `lock` enum('OPEN','CLOSE') NOT NULL default 'OPEN',
                `visibility` enum('HIDE','SHOW') NOT NULL default 'SHOW',
                `specificComment` text NOT NULL,
                `rank` int(11) NOT NULL default '0',
                `parent` int(11) NOT NULL default '0',
                `raw_to_pass` tinyint(4) NOT NULL default '50',
                PRIMARY KEY  (`learnPath_module_id`)
              ) TYPE=MyISAM COMMENT='This table links module to the learning path using them';");
mysql_query ("
          CREATE TABLE `".$TABLEASSET."` (
              `asset_id` int(11) NOT NULL auto_increment,
              `module_id` int(11) NOT NULL default '0',
              `path` varchar(255) NOT NULL default '',
              `comment` varchar(255) default NULL,
              PRIMARY KEY  (`asset_id`)
            ) TYPE=MyISAM COMMENT='List of resources of module of learning paths';");
mysql_query ("
          CREATE TABLE `".$TABLEUSERMODULEPROGRESS."` (
              `user_module_progress_id` int(22) NOT NULL auto_increment,
              `user_id` mediumint(9) NOT NULL default '0',
              `learnPath_module_id` int(11) NOT NULL default '0',
              `learnPath_id` int(11) NOT NULL default '0',
              `lesson_location` varchar(255) NOT NULL default '',
              `lesson_status` enum('NOT ATTEMPTED','PASSED','FAILED','COMPLETED','BROWSED','INCOMPLETE','UNKNOWN') NOT NULL default 'NOT ATTEMPTED',
              `entry` enum('AB-INITIO','RESUME','') NOT NULL default 'AB-INITIO',
              `raw` tinyint(4) NOT NULL default '-1',
              `scoreMin` tinyint(4) NOT NULL default '-1',
              `scoreMax` tinyint(4) NOT NULL default '-1',
              `total_time` varchar(13) NOT NULL default '0000:00:00.00',
              `session_time` varchar(13) NOT NULL default '0000:00:00.00',
              `suspend_data` text NOT NULL,
              `credit` enum('CREDIT','NO-CREDIT') NOT NULL default 'NO-CREDIT',
              PRIMARY KEY  (`user_module_progress_id`)
            ) TYPE=MyISAM COMMENT='Record the last known status of the user in the course';");


########################## STATISTICS ##############################
        $sql = "CREATE TABLE `".$TABLETRACKACCESS."` (
                  `access_id` int(11) NOT NULL auto_increment,
                  `access_user_id` int(10) default NULL,
                  `access_date` datetime NOT NULL default '0000-00-00 00:00:00',
                  `access_tool` varchar(30) default NULL,
                  PRIMARY KEY  (`access_id`)
                ) TYPE=MyISAM COMMENT='Record informations about access to course or tools'";
        mysql_query($sql);

        $sql = "CREATE TABLE `".$TABLETRACKDOWNLOADS."` (
                  `down_id` int(11) NOT NULL auto_increment,
                  `down_user_id` int(10) default NULL,
                  `down_date` datetime NOT NULL default '0000-00-00 00:00:00',
                  `down_doc_path` varchar(255) NOT NULL default '0',
                  PRIMARY KEY  (`down_id`)
                ) TYPE=MyISAM COMMENT='Record informations about downloads'";
        mysql_query($sql);
        
        $sql = "CREATE TABLE `".$TABLETRACKEXERCICES."` (
                  `exe_id` int(11) NOT NULL auto_increment,
                  `exe_user_id` int(10) default NULL,
                  `exe_date` datetime NOT NULL default '0000-00-00 00:00:00',
                  `exe_exo_id` tinyint(4) NOT NULL default '0',
                  `exe_result` mediumint(8) NOT NULL default '0',
                  `exe_weighting` mediumint(8) NOT NULL default '0',
                  PRIMARY KEY  (`exe_id`)
                ) TYPE=MyISAM COMMENT='Record informations about exercices'";
        mysql_query($sql);
        
/*        $sql = "CREATE TABLE `".$TABLETRACKLINKS."` (
                  `links_id` int(11) NOT NULL auto_increment,
                  `links_user_id` int(10) default NULL,
                  `links_date` datetime NOT NULL default '0000-00-00 00:00:00',
                  `links_link_id` int(11) NOT NULL default '0',
                  PRIMARY KEY  (`links_id`)
                ) TYPE=MyISAM COMMENT='Record informations about clicks on links'";
        mysql_query($sql);
*/
        $sql = "CREATE TABLE `".$TABLETRACKUPLOADS."` (
                  `upload_id` int(11) NOT NULL auto_increment,
                  `upload_user_id` int(10) default NULL,
                  `upload_date` datetime NOT NULL default '0000-00-00 00:00:00',
                  `upload_work_id` int(11) NOT NULL default '0',
                  PRIMARY KEY  (`upload_id`)
                ) TYPE=MyISAM COMMENT='Record some more informations about uploaded works'";
        mysql_query($sql);

	return 0;
};




/**
 * function fill_course_repository()
 *
 * @desc Add starting files in course
 *
 * @param	string	$courseRepository		path from $coursesRepositorySys to root of course
 * @GLOBAL	string	$clarolineRepositorySys	path to claroline scripts
 * @GLOBAL	string	$coursesRepositorySys	path to root of courses
 *
 * @author	Christophe Gesché moosh@tiscali.be
 * @version 1.0
 */

function 	fill_course_repository($courseRepository)
{
	############# COPIER DOCUMENTS #############
	GLOBAL $clarolineRepositorySys, $coursesRepositorySys;
  // attention : do not forget to change the queris in fill_Db_course if something changed here
	copy($clarolineRepositorySys."document/Example_document.pdf", $coursesRepositorySys.$courseRepository."/document/Example_document.pdf");
	return 0;
};



/**
 * function fill_Db_course()
 * @desc insert starting data in db of course.
 *
 * @param	string	$courseDbName		partial DbName. to build as $courseTablePrefix.$courseDbName.$dbGlu;
 * @param	string	$courseRepository	path from $coursesRepositorySys to root of course
 * @param	string	$language			language request for this course
 *
 * @GLOBAL	boolean	$singleDbEnabled		whether all campus use only one DB
 * @GLOBAL	string	$courseTablePrefix		common prefix for all table of courses
 * @GLOBAL	string	$dbGlu					glu between logical name of DB and  logical name of table
 * @GLOBAL	string	$clarolineRepositorySys
 * @GLOBAL	integer	$_user					id of course creator.
 *
 * @author	Christophe Gesché <moosh@tiscali.be>
 * @version 1.0
 *
 * note  $language would be removed soon.
 */

function fill_Db_course($courseDbName,$courseRepository, $language)
{
    global $singleDbEnabled, $courseTablePrefix, $dbGlu, $clarolineRepositorySys, $_user, $mainDbName;

	if(!$singleDbEnabled)
	{
		mysql_query("CREATE DATABASE $courseDbName");
	}

	$courseDbName=$courseTablePrefix.$courseDbName.$dbGlu;

	$TABLECOURSEHOMEPAGE	= $courseDbName."tool_list";
	$TABLEINTROS			= $courseDbName."tool_intro";

	$TABLEGROUPS			= $courseDbName."group_team";
	$TABLEGROUPUSER			= $courseDbName."group_rel_team_user";
	$TABLEGROUPPROPERTIES	= $courseDbName."group_property";

	$TABLETOOLCOURSEDESC	= $courseDbName."course_description";
	$TABLETOOLAGENDA		= $courseDbName."calendar_event";
	$TABLETOOLANNOUNCEMENTS	= $courseDbName."announcement";
	$TABLETOOLWORKS			= $courseDbName."assignment_doc";
	$TABLETOOLWORKSUSER		= $courseDbName."work_student";
	$TABLETOOLDOCUMENT		= $courseDbName."document";

	//$TABLETOOLLINK			= $courseDbName."link";

	$TABLEQUIZ				= $courseDbName."quiz_test";
	$TABLEQUIZQUESTION		= $courseDbName."quiz_rel_test_question";
	$TABLEQUIZQUESTIONLIST	= $courseDbName."quiz_question";
	$TABLEQUIZANSWERSLIST	= $courseDbName."quiz_answer";

	$TABLEPHPBBACCESS		= $courseDbName."bb_access";
	$TABLEPHPBBBANLIST		= $courseDbName."bb_banlist";
	$TABLEPHPBBCATEGORIES	= $courseDbName."bb_categories";
	$TABLEPHPBBCONFIG		= $courseDbName."bb_config";
	$TABLEPHPBBDISALLOW		= $courseDbName."bb_disallow";
	$TABLEPHPBBFORUMACCESS	= $courseDbName."bb_forum_access";
	$TABLEPHPBBFORUMMODS	= $courseDbName."bb_forum_mods";
	$TABLEPHPBBFORUMS		= $courseDbName."bb_forums";
	$TABLEPHPBBHEADFOOT		= $courseDbName."bb_headermetafooter";
	$TABLEPHPBBDOMAINLIST	= $courseDbName."bb_liste_domaines";
	$TABLEPHPBBPAGES		= $courseDbName."pages";
	$TABLEPHPBBPOSTS		= $courseDbName."bb_posts";
	$TABLEPHPBBPOSTSTEXT	= $courseDbName."bb_posts_text";
	$TABLEPHPBBPRIVMSG		= $courseDbName."bb_priv_msgs";
	$TABLEPHPBBRANK			= $courseDbName."bb_ranks";
	$TABLEPHPBBSESSIONS		= $courseDbName."bb_sessions";
	$TABLEPHPBBTHEMES		= $courseDbName."bb_themes";
	$TABLEPHPBBTOPICS		= $courseDbName."bb_topics";
	$TABLEPHPBBUSERS		= $courseDbName."bb_users";
	$TABLEPHPBBWHOSONLINE	= $courseDbName."bb_whosonline";
	$TABLEPHPBBWORDS		= $courseDbName."bb_words";
   $TABLEPHPBBNOTIFY        = $courseDbName."bb_rel_topic_userstonotify";


  $TABLELEARNPATH         = $courseDbName."lp_learnPath";
  $TABLEMODULE            = $courseDbName."lp_module";
  $TABLELEARNPATHMODULE   = $courseDbName."lp_rel_learnPath_module";
  $TABLEASSET             = $courseDbName."lp_asset";
  $TABLEUSERMODULEPROGRESS= $courseDbName."lp_user_module_progress";

  // stats
  $TABLETRACKACCESS        = $courseDbName."`.`track_e_access";
  $TABLETRACKDOWNLOADS     = $courseDbName."`.`track_e_downloads";
  $TABLETRACKUPLOADS       = $courseDbName."`.`track_e_uploads";
  //$TABLETRACKLINKS         = $courseDbName."`.`track_e_links";
  $TABLETRACKEXERCICES     = $courseDbName."`.`track_e_exercices";

    
	$cahier_charges="$CourseProgram/$repertoire.html";

	$nom = $_user['lastName'];
	$prenom =$_user['firstName'];

	@include($clarolineRepositorySys."lang/english/create_course.inc.php");
	@include($clarolineRepositorySys."lang/".$language."/create_course.inc.php");

	mysql_select_db("$courseDbName");
	mysql_query("INSERT INTO `".$TABLEPHPBBACCESS."` VALUES (	'-1',	'Deleted')");
	mysql_query("INSERT INTO `".$TABLEPHPBBACCESS."` VALUES (	'1',	'User')");
	mysql_query("INSERT INTO `".$TABLEPHPBBACCESS."` VALUES (	'2',	'Moderator')");
	mysql_query("INSERT INTO `".$TABLEPHPBBACCESS."` VALUES (	'3',	'Super Moderator')");
	mysql_query("INSERT INTO `".$TABLEPHPBBACCESS."` VALUES (	'4',	'Administrator')");
// Create a hidden catagory for group forums
	mysql_query("INSERT INTO `".$TABLEPHPBBCATEGORIES."` VALUES (1,'$langCatagoryGroup',1)");
// Create an example catagory
	mysql_query("INSERT INTO `".$TABLEPHPBBCATEGORIES."` VALUES (2,'$langCatagoryMain',2)");
############################## GROUPS ###########################################
	mysql_query("INSERT INTO `".$TABLEGROUPPROPERTIES."`
(id, self_registration, private, forum, document, wiki, chat)
VALUES (NULL, '1', '0', '1', '1', '0', '1')");
	mysql_query("INSERT INTO `".$TABLEPHPBBCONFIG."` VALUES (
         '1',
         '$intitule',
         '1',
         '1',
         '1',
         '0',
         NULL,
         '1',
         '15',
         '15',
         '50',
         NULL,
         '0',
         '$langFormula',
         '$email',
         '$language'
         )");
	mysql_query("INSERT INTO `".$TABLEPHPBBFORUMMODS."` VALUES (
         '1',
         '1'
         )");
	mysql_query("INSERT INTO `".$TABLEPHPBBFORUMS."` VALUES (1,'$langTestForum','$langDelAdmin',2,1,1,1,1,2,0,'c4ca4238a0b923820dcc509a6f75849b',1)");
	mysql_query("INSERT INTO `".$TABLEPHPBBHEADFOOT."` VALUES (
         '<center><a href=\"../".$courseRepository."\"><img border=0 src=../claroline/img/logo.gif></a></center>',
         '',
         ''
         )");
	mysql_query("INSERT INTO `".$TABLEPHPBBPOSTS."` VALUES (1,1,1,1,NOW(),'127.0.0.1',\"$nom\",\"$prenom\")");
	mysql_query("CREATE TABLE `".$TABLEPHPBBPOSTSTEXT."` (
        post_id int(10) DEFAULT '0' NOT NULL,
        post_text text,
        PRIMARY KEY (post_id)
        )");
	mysql_query("INSERT INTO `".$TABLEPHPBBPOSTSTEXT."` VALUES ('1', \"$langMessage\")");
// Contenu de la table 'themes'
	mysql_query("INSERT INTO `".$TABLEPHPBBTHEMES."` VALUES (
       '1',
       'Default',
       '#000000',
       '#FFFFFF',
       '#6C706D',
       '#2E4460',
       '#001100',
       'images/header-dark.jpg',
       'images/new_topic-dark.jpg',
       'images/reply-dark.jpg',
       '#0000FF',
       '#800080',
       '0',
       'sans-serif',
       '1',
       '2',
       '-2',
       '+1',
       '95%',
       'images/reply_locked-dark.jpg'
       )");
	mysql_query("INSERT INTO `".$TABLEPHPBBTHEMES."` VALUES (
       '2',
       'Ocean',
       '#FFFFFF',
       '#000000',
       '#CCCCCC',
       '#9BB6DA',
       '#000000',
       'images/header.jpg',
       'images/new_topic.jpg',
       'images/reply.jpg',
        '#0000FF',
       '#800080',
       '0',
       'sans-serif',
       '1',
       '2',
       '-2',
       '+1',
       '95%',
       'images/reply_locked-dark.jpg'
       )");
	mysql_query("INSERT INTO `".$TABLEPHPBBTHEMES."` VALUES (
        '3',
        'OCPrices.com',
        '#FFFFFF',
        '#000000',
        '#F5F5F5',
        '#E6E6E6',
        '#FFFFFF',
        'images/forum.jpg',
        'images/nouveausujet.jpg',
        'images/repondre.jpg',
       '#0000FF',
       '#800080',
        '1',
        'Arial,Helvetica, Sans-serif',
        '1',
        '2',
        '-2',
        '+1',
        '600',
        'images/reply_locked-dark.jpg'
        )");
// Contenu de la table 'users'
	mysql_query("INSERT INTO `".$TABLEPHPBBUSERS."` VALUES (
       '1',
       '$nom $prenom',
       NOW(),
       'password',
       '$email',
       NULL,
       NULL,
       NULL,
       NULL,
       NULL,
       NULL,
       NULL,
       NULL,
       NULL,
       NULL,
       NULL,
       '0',
       '0',
       '0',
       '0',
       '0',
       '0',
       '1',
       NULL,
       NULL,
       NULL
       )");
	mysql_query("INSERT INTO `".$TABLEPHPBBUSERS."` VALUES (
       '-1',       '$langAnonymous',       NOW(),       'password',       '',
       NULL,       NULL,       NULL,       NULL,       NULL,       NULL,       NULL,
       NULL,       NULL,       NULL,       NULL,       '0',       '0',       '0',       '0',       '0',
       '0',       '1',       NULL,       NULL,       NULL       )");



##################### register tools in course ######################################

    $TABLECOURSETOOL = $mainDbName.'`.`course_tool';

    $sql = "SELECT id, def_access, def_rank, claro_label FROM `". $TABLECOURSETOOL . "` where add_in_course = 'AUTOMATIC'";

    $result = claro_sql_query($sql);

    if (mysql_num_rows($result) > 0)
    {
        while ( $courseTool = mysql_fetch_array($result, MYSQL_ASSOC))
        {
            $sql_insert = " INSERT INTO `" . $TABLECOURSEHOMEPAGE . "` "
                          . " (tool_id, rank, access) "
                          . " VALUES ('" . $courseTool['id'] . "','" . $courseTool['def_rank'] . "','" . $courseTool['def_access'] . "')";
            $intro_id = claro_sql_query_insert_id($sql_insert);
        }
    }

############################## EXERCICES #######################################
	mysql_query("INSERT INTO `".$TABLEQUIZANSWERSLIST."` VALUES ( '1', '1', '$langRidiculise', '0', '$langNoPsychology', '-5', '1')");
	mysql_query("INSERT INTO `".$TABLEQUIZANSWERSLIST."` VALUES ( '2', '1', '$langAdmitError', '0', '$langNoSeduction', '-5', '2')");
	mysql_query("INSERT INTO `".$TABLEQUIZANSWERSLIST."` VALUES ( '3', '1', '$langForce', '1', '$langIndeed', '5', '3')");
	mysql_query("INSERT INTO `".$TABLEQUIZANSWERSLIST."` VALUES ( '4', '1', '$langContradiction', '1', '$langNotFalse', '5', '4')");
	mysql_query("INSERT INTO `".$TABLEQUIZ."` VALUES ( '1', '$langExerciceEx', '$langAntique', '1', '0', '0')");
	mysql_query("INSERT INTO `".$TABLEQUIZQUESTIONLIST."` VALUES ( '1', '$langSocraticIrony', '$langManyAnswers', '10', '1', '2','')");
	mysql_query("INSERT INTO `".$TABLEQUIZQUESTION."` VALUES ( '1', '1')");

############################### LEARNING PATH  ####################################
  // HANDMADE module type are not used for first version of claroline 1.5 beta so we don't show any exemple!
  mysql_query("INSERT INTO `".$TABLELEARNPATH."` VALUES ('1', '$langSampleLearnPath', '$langSampleLearnPathDesc', 'OPEN', 'SHOW', '1')");
  
  mysql_query("INSERT INTO `".$TABLELEARNPATHMODULE."` VALUES ('1', '1', '1', 'OPEN', 'SHOW', '', '1', '0', '50')");
  mysql_query("INSERT INTO `".$TABLELEARNPATHMODULE."` VALUES ('2', '1', '2', 'OPEN', 'SHOW', '', '2', '0', '50')");

  mysql_query("INSERT INTO `".$TABLEMODULE."` VALUES ('1', '$langSampleDocument', '$langSampleDocumentDesc', 'PRIVATE', '1', 'DOCUMENT', '')");
  mysql_query("INSERT INTO `".$TABLEMODULE."` VALUES ('2', '$langExerciceEx', '$langSampleExerciseDesc', 'PRIVATE', '2', 'EXERCISE', '')");

  mysql_query("INSERT INTO `".$TABLEASSET."` VALUES ('1', '1', '/Example_document.pdf', '')");
  mysql_query("INSERT INTO `".$TABLEASSET."` VALUES ('2', '2', '1', '')");

############################## FORUMS  #######################################
	mysql_query("INSERT INTO `".$TABLEPHPBBTOPICS."` VALUES (1,'$langExMessage',-1,'2001-09-18 20:25',1,'',1,1,'0','1', '$nom', '$prenom')");

	return 0;
};


/**
 * function register_course
 * @desc to create a record in the course tabale of main database
 * @param string	$courseId
 * @param string	$courseCode
 * @param string	$courseRepository
 * @param string	$courseDbName
 * @param string	$titulaires
 * @param string	$faculte
 * @param string	$intitule			complete name of course
 * @param string	$languageCourse		lang for this course
 * @param string	$uid				uid of owner
 * @GLOBALS tables names
 * @GLOBALS var lang
 * @GLOBALS $defaultVisibilityForANewCourse
 * @author Christophe Gesché <moosh@tiscali.be>
 */

function register_course($courseSysCode, $courseScreenCode, $courseRepository, $courseDbName, $titular, $email, $faculte, $intitule, $languageCourse, $uidCreator, $expirationDate="")
{
	GLOBAL $TABLECOURSE, $TABLECOURSUSER, $TABLEANNOUNCEMENTS, $DEBUG, $defaultVisibilityForANewCourse,
	$langCourseDescription,
	$langProfessor, $includePath,
	$error_msg, $courseTablePrefix, $dbGlu;
  
	$okForRegisterCourse = TRUE;

	// Check if  I have all
	if ($courseSysCode== "")
	{
		$error_msg[] = "courseSysCode is missing";
		$okForRegisterCourse = FALSE;
	}
	if ($courseScreenCode== "")
	{
		$error_msg[] = "courseScreenCode is missing";
		$okForRegisterCourse = FALSE;
	}
	if ($courseDbName== "")
	{
		$error_msg[] = "courseDbName is missing";
		$okForRegisterCourse = FALSE;
	}
	if ($courseRepository == "")
	{
		$error_msg[] = "course Repository is missing";
		$okForRegisterCourse = FALSE;
	}
	if ($titular == "")
	{
		$error_msg[] = "titular is missing";
		$screen_msg[] = "langTitularIsMissing";
	}
	if ($email == "")
	{
		$error_msg[] = "email is missing";
	}
	if ($faculte=="")
	{
		$error_msg[] = "faculte is missing";
		$okForRegisterCourse = FALSE;
	}
	if ($intitule== "")
	{
		if ($courseScreenCode== "")
		{
			$error_msg[] = "intitule is missing";
			$okForRegisterCourse = FALSE;
		}
		else 
		{
			$intitule =$courseScreenCode;
		}
	}
	if ($languageCourse == "")
	{
		$error_msg[] = "language is missing";
		$languageCourse = 'english';
	}
	if ($uidCreator== "")
	{
		$error_msg[] = "uidCreator is missing";
		$okForRegisterCourse = FALSE;
	}

	if ($expirationDate=="")
	{
		$expirationDate = "NULL";
	}
	else
	{
		$expirationDate = "FROM_UNIXTIME(".$expirationDate.")";
	}

	if ($okForRegisterCourse)
	{
		include_once($includePath."/installedVersion.inc.php");
		// here we must add 2 fields
		$sql ="INSERT INTO `".$TABLECOURSE."` SET
			code = '".$courseSysCode."',
			dbName = '".$courseDbName."',
			directory = '".$courseRepository."',
			languageCourse = '".$languageCourse."',
			intitule = '".$intitule."',
			description = '".$langCourseDescription."',
			faculte = '".$faculte."',
			visible = '".$defaultVisibilityForANewCourse."',
			cahier_charges = '$CourseProgram/".strtolower($courseScreenCode).".html',
			scoreShow = '',
			diskQuota = NULL,
			creationDate = now(),
			expirationDate = '".$expirationDate."',
			versionDb = '".$versionDb."',
			versionClaro = '".$clarolineVersion."',
			lastEdit = now(),
			lastVisit = NULL,
			titulaires = '".$titular."',
			email = '".$email."',
			fake_code = '".$courseScreenCode."'";
		claro_sql_query($sql);
		$sql = "INSERT INTO `".$TABLECOURSUSER."` SET
			code_cours = '".$courseSysCode."',
			user_id = '".$uidCreator."',
			statut = '1',
			role = '".$langProfessor."',
			tutor='1'";
		claro_sql_query($sql);
	}
	else //if ($okForRegisterCourse)
	{
		return 1;
	}
	return 0;
};

/**
 * function checkArchive()
 * @desc check intergrity and security of content to insert in campus
 * @param string	$pathToArchive 		COMPLETE path to archive.
 * @author	Christophe Gesché moosh@tiscali.be
 * @version 0.1
 */
function checkArchive($pathToArchive)
{
	return TRUE;
};


/**
 * function readPropertiesInArchive()
 * @desc search and read archive.ini file add ins archive build by claroline
 * @param string	$archive 		COMPLETE path to archive.
 * @param boolean	$isCompressed	whether archive would be unzip before read in
 * @author	Christophe Gesché moosh@tiscali.be
 * @version 1.0
 */

function readPropertiesInArchive($archive,$isCompressed=TRUE)
{
	include("../inc/lib/pclzip/pclzip.lib.php");
	printVar(dirname($archive), "Zip : ");
	/*
	string tempnam ( string dir, string prefix)
	tempnam() crée un fichier temporaire unique dans le dossier dir. Si le dossier n'existe pas, tempnam() va générer un nom de fichier dans le dossier temporaire du système.
	Avant PHP 4.0.6, le comportement de tempnam() dépendait de l'OS sous-jacent. Sous Windows, la variable d'environnement TMP remplace le paramètre dir; sous Linux, la variable d'environnement TMPDIR a la priorité, tandis que pour les OS en système V R4, le paramètre dir sera toujours utilisé, si le dossier qu'il représente existe. Consultez votre documentation pour plus de détails.
	tempnam() retourne le nom du fichier temporaire, ou la chaîne NULL en cas d'échec.
	*/
	$zipFile = new pclZip($archive);
	$tmpDirName = dirname($archive)."/tmp".$uid.uniqid($uid);
	if (mkpath($tmpDirName))
		$unzippingSate = $zipFile->extract($tmpDirName);
	else
		die ("mkpath va pas");
	$pathToArchiveIni = dirname($tmpDirName)."/archive.ini";
//	echo $pathToArchiveIni;
	$courseProperties = parse_ini_file($pathToArchiveIni);
	rmdir($tmpDirName);
	return 	$courseProperties;
};
?>