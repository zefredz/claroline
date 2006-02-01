<?php // $Id$
/**
 * CLAROLINE
 *
 * add_course lib contain function to add a course
 * add is, find keys names aivailable, build the the course database
 * fill the course database, build the content directorys, build the index page
 * build the directory tree, register the course.
 *
 * @version 1.8 $Revision$
 *
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @see http://www.claroline.net/wiki/CLCRS/
 *
 * @package COURSE
 *
 * @author Claro Team <cvs@claroline.net>
 * @author Christophe Gesché <moosh@claroline.net>
 *
 */

/**
 * with  the WantedCode we can define the 4 keys  to find courses datas
 *
 * @param string $wantedCode initial model
 * @param string $prefix4all       prefix added  for ALL keys
 * @param string $prefix4baseName  prefix added  for basename key (after the $prefix4all)
 * @param string $prefix4path      prefix added  for repository key (after the $prefix4all)
 * @param string $addUniquePrefix  prefix randomly generated prepend to model
 * @param boolean $useCodeInDepedentKeys   whether not ignore $wantedCode param. If FALSE use an empty model.
 * @param boolean $addUniqueSuffix suffix randomly generated append to model
 * @param string $suffix4baseName  suffix added  for db key (prepend to $suffix4all)
 * @param string $suffix4path      suffix added  for repository key (prepend to $suffix4all)
 * @param string $suffix4all       suffix added  for ALL keys
 * @return array
 * - ["currentCourseCode"]          : Must be alphaNumeric and outputable in HTML System
 * - ["currentCourseId"]            : Must be unique in mainDb.course it's the primary key
 * - ["currentCourseDbName"]        : Must be unique it's the database name.
 * - ["currentCourseRepository"]    : Must be unique in /$coursesRepositories/
 *
 * @todo actually if suffix is not unique  the next append and not  replace
 * @todo add param listing keyg wich wouldbe identical
 * @todo manage an error on brake for too many try
 * @todo $keysCourseCode is always
 */

function define_course_keys ($wantedCode,
                             $prefix4all = '',
                             $prefix4baseName = '',
                             $prefix4path = '',
                             $addUniquePrefix = FALSE,
                             $useCodeInDepedentKeys = TRUE,
                             $addUniqueSuffix = FALSE

                             )
{
    $tbl_mdb_names = claro_sql_get_main_tbl();
    $tbl_course    = $tbl_mdb_names['course'];

    GLOBAL $coursesRepositories, $DEBUG,$singleDbEnabled;

    $nbCharFinalSuffix = get_conf('nbCharFinalSuffix');

    // $keys["currentCourseCode"] is the "public code"

    $wantedCode =  strtr($wantedCode,
    'ÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝßàáâãäåæçèéêëìíîïðñòóôõöøùúûüýÿ',
    'AAAAAACEEEEIIIIDNOOOOOOUUUUYsaaaaaaaceeeeiiiionoooooouuuuyy');

    //$wantedCode = strtoupper($wantedCode);
    $charToReplaceByUnderscore = '- ';
    $wantedCode = ereg_replace('['.$charToReplaceByUnderscore.']', '_', $wantedCode);
    $wantedCode = ereg_replace('[^A-Za-z0-9_]', '', $wantedCode);

    if ($wantedCode=='') $wantedCode = get_conf('prefixAntiEmpty');

    $keysCourseCode    = $wantedCode;

    if (!$useCodeInDepedentKeys) $wantedCode = '';
    // $keys['currentCourseId'] would Became $cid in normal using.

    if ($addUniquePrefix) $uniquePrefix =  substr(md5 (uniqid('')),0,10);
    else                  $uniquePrefix = '';

    if ($addUniqueSuffix) $uniqueSuffix =  substr(md5 (uniqid('')),0,10);

    else                  $uniqueSuffix = '';

    $keysAreUnique = FALSE;

    $finalSuffix = array('CourseId'=>''
                        ,'CourseDb'=>''
                        ,'CourseDir'=>''
                        );
    $tryNewFSCId = $tryNewFSCDb = $tryNewFSCDir = 0;

    while (!$keysAreUnique)
    {
        $keysCourseId         = $prefix4all . $uniquePrefix . strtoupper($wantedCode) . $uniqueSuffix . $finalSuffix['CourseId'];
        $keysCourseDbName     = $prefix4baseName . $uniquePrefix . strtoupper($keysCourseId) . $uniqueSuffix . $finalSuffix['CourseDb'];
        $keysCourseRepository = $prefix4path . $uniquePrefix . strtoupper($wantedCode) . $uniqueSuffix . $finalSuffix['CourseDir'];

        $keysAreUnique = TRUE;
        // Now we go to check if there are unique

        $sqlCheckCourseId    = "SELECT COUNT(code) existAllready
                                FROM `" . $tbl_course . "`
                                WHERE code = '" . $keysCourseId  ."'";

        $resCheckCourseId    = claro_sql_query ($sqlCheckCourseId);
        $isCheckCourseIdUsed = mysql_fetch_array($resCheckCourseId);

        if ($isCheckCourseIdUsed[0]['existAllready'] > 0)
        {
            $keysAreUnique = FALSE;
            $tryNewFSCId++;
            $finalSuffix['CourseId'] = substr(md5 (uniqid('')), 0, $nbCharFinalSuffix);
        };

        if ($singleDbEnabled)
        {
            $sqlCheckCourseDb = "SHOW TABLES LIKE '".$keysCourseDbName."%'";
        }
        else
        {
            $sqlCheckCourseDb = "SHOW DATABASES LIKE '".$keysCourseDbName."'";
        }

        $resCheckCourseDb = claro_sql_query ($sqlCheckCourseDb);

        $isCheckCourseDbUsed = mysql_num_rows($resCheckCourseDb);

        if ($isCheckCourseDbUsed>0)
        {
            $keysAreUnique = FALSE;
            $tryNewFSCDb++;
            $finalSuffix['CourseDb'] = substr('_'.md5 (uniqid('')), 0, $nbCharFinalSuffix);
        };

        if (file_exists($coursesRepositories . '/' . $keysCourseRepository))
        {
            $keysAreUnique = FALSE;
            $tryNewFSCDir++;
            $finalSuffix['CourseDir'] = substr(md5 (uniqid('')), 0, $nbCharFinalSuffix);
            if ($DEBUG) echo '[dir'.$coursesRepositories . '/' . $keysCourseRepository.']<br>';
        };

        if(!$keysAreUnique)
        {
            $finalSuffix['CourseDir'] = substr(md5 (uniqid ('')), 0, $nbCharFinalSuffix);
            $finalSuffix['CourseId']  = $finalSuffix['CourseDir'];
            $finalSuffix['CourseDb']  = $finalSuffix['CourseDir'];
        }


        // here  we can add a counter to exit if need too many try
        $limitQtyTry = 128;

        if (($tryNewFSCId+$tryNewFSCDb+$tryNewFSCDir > $limitQtyTry)
                or ($tryNewFSCId > $limitQtyTry / 2 )
                or ($tryNewFSCDb > $limitQtyTry / 2 )
                or ($tryNewFSCDir > $limitQtyTry / 2 )
            )
        {
            return FALSE;
        }
    }

    // dbName Can't begin with a number
    if (!strstr("abcdefghijklmnopqrstuvwyzABCDEFGHIJKLMNOPQRSTUVWXYZ",$keysCourseDbName[0]))
    {
        $keysCourseDbName = get_conf('prefixAntiNumber') . $keysCourseDbName;
    }

    $keys['currentCourseCode'      ] = $keysCourseCode;      // screen code
    $keys['currentCourseId'        ] = $keysCourseId;        // sysCode
    $keys['currentCourseDbName'    ] = $keysCourseDbName;    // dbname
    $keys['currentCourseRepository'] = $keysCourseRepository;// append to course repository

    return $keys;
};

/**
 * Create directories used by course.
 *
 * @param  string $courseRepository path from $coursesRepositorySys to root of course
 * @param  string $courseId         sysId of course
 *
 * @author Christophe Gesché <moosh@claroline.net>
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 */

function prepare_course_repository($courseRepository, $courseId)
{
    GLOBAL $coursesRepositorySys, $clarolineRepositorySys, $includePath;

    if( ! is_dir($coursesRepositorySys) )
    {
        claro_mkdir($coursesRepositorySys, CLARO_FILE_PERMISSIONS, true);
    }

    $courseDirPath = $coursesRepositorySys . $courseRepository;

    if ( ! is_writable($coursesRepositorySys) ) return claro_failure::set_failure('READ_ONLY_SYSTEM_FILE');

    if ( ! claro_mkdir($courseDirPath, CLARO_FILE_PERMISSIONS,true) ) return claro_failure::set_failure('CANT_CREATE_COURSE_REP');

    if ( ! claro_mkdir($courseDirPath . '/exercise'      , CLARO_FILE_PERMISSIONS,true) ) return claro_failure::set_failure('CANT_CREATE_COURSE_REP_CLQWZ');
    if ( ! claro_mkdir($courseDirPath . '/document'      , CLARO_FILE_PERMISSIONS,true) ) return claro_failure::set_failure('CANT_CREATE_COURSE_REP_CLDOC');
    if ( ! claro_mkdir($courseDirPath . '/work'          , CLARO_FILE_PERMISSIONS,true) ) return claro_failure::set_failure('CANT_CREATE_COURSE_REP_CLWRK');
    if ( ! claro_mkdir($courseDirPath . '/group'         , CLARO_FILE_PERMISSIONS,true) ) return claro_failure::set_failure('CANT_CREATE_COURSE_REP_CLGRP');
    if ( ! claro_mkdir($courseDirPath . '/chat'          , CLARO_FILE_PERMISSIONS,true) ) return claro_failure::set_failure('CANT_CREATE_COURSE_REP_CLCHT');
    if ( ! claro_mkdir($courseDirPath . '/modules'       , CLARO_FILE_PERMISSIONS,true) ) return claro_failure::set_failure('CANT_CREATE_COURSE_REP_MODULES');
    if ( ! claro_mkdir($courseDirPath . '/scormPackages' , CLARO_FILE_PERMISSIONS,true) ) return claro_failure::set_failure('CANT_CREATE_COURSE_REP_SCORM');
    // for sample learning path
    if ( ! claro_mkdir($courseDirPath . '/modules/module_1', CLARO_FILE_PERMISSIONS,true)) return claro_failure::set_failure('CANT_CREATE_COURSE_REP_MODULE_1');

    // build index.php of course
    $fd = fopen($courseDirPath . '/index.php', 'w');
    if ( ! $fd) return claro_failure::set_failure('CANT_CREATE_COURSE_INDEX');

    $string= '<?php '                                                                  . "\n"
    .        '$cidReq = \''.$courseId.'\';'                                            . "\n"
    .        '$claroGlobalPath = \'' . $includePath . '\';'                            . "\n"
    .        'include \'' . $clarolineRepositorySys . 'course_home/course_home.php\';' . "\n"
    .        '?'.'>'                                                                   . "\n"
    ;

    if ( ! fwrite($fd, $string) ) return claro_failure::set_failure('CANT_WRITE_COURSE_INDEX');
    if ( ! fclose($fd) )          return claro_failure::set_failure('CANT_SAVE_COURSE_INDEX');


    $fd     = fopen($coursesRepositorySys.$courseRepository . '/group/index.php', 'w');
    if ( ! $fd ) return false;

    $string = '<?php session_start(); ?'.'>';

    if ( ! fwrite($fd, $string) ) return false;

    return true;
};

/**
 * Add starting files in course
 *
 * @param   string  $courseDbName partial dbName form course table tu build real DbName
 * @global  boolean singleDbEnabled   whether all campus use only one DB
 * @global  string  courseTablePrefix common prefix for all table of courses
 * @global  string  dbGlu glu between logical name of DB and logical name of table 267
 *
 * @author Christophe Gesché <moosh@claroline.net>
 * @version 1.0
 */

function update_db_course($courseDbName)
{
    global $singleDbEnabled;
    global $courseTablePrefix;
    global $dbGlu;

    if(!$singleDbEnabled)
    {
        claro_sql_query('CREATE DATABASE `'.$courseDbName.'`');
        if (mysql_errno() > 0)
            return CLARO_ERROR_CANT_CREATE_DB;
    }

    $courseDbName = $courseTablePrefix . $courseDbName . $dbGlu;
    /**
        Here function claro_sql_get_course_tbl() from main lib would be
        called to replace the table name assignement
    */

    $tbl_cdb_names = claro_sql_get_course_tbl($courseDbName);

    $TABLECOURSEHOMEPAGE    = $tbl_cdb_names['tool'];
    $TABLEINTROS            = $tbl_cdb_names['tool_intro'];

    $TABLEGROUPS            = $tbl_cdb_names['group_team'];// $courseDbName."group_team";
    $TABLEGROUPUSER         = $tbl_cdb_names['group_rel_team_user'];//$courseDbName."group_rel_team_user";
    $TABLEGROUPPROPERTIES   = $tbl_cdb_names['group_property'];// $courseDbName."group_property";

    $TABLETOOLUSERINFOCONTENT = $tbl_cdb_names['userinfo_content'];// $courseDbName."userinfo_content";
    $TABLETOOLUSERINFODEF     = $tbl_cdb_names['userinfo_def'];// $courseDbName."userinfo_def";

    $TABLETOOLCOURSEDESC    = $tbl_cdb_names['course_description'];// $courseDbName."course_description";
    $TABLETOOLAGENDA        = $tbl_cdb_names['calendar_event'];// $courseDbName."calendar_event";
    $TABLETOOLANNOUNCEMENTS = $tbl_cdb_names['announcement'];// $courseDbName."announcement";
    $TABLETOOLDOCUMENT      = $tbl_cdb_names['document'];// $courseDbName."document";
    $TABLETOOLWRKASSIGNMENT = $tbl_cdb_names['wrk_assignment'];// $courseDbName."wrk_assignment";
    $TABLETOOLWRKSUBMISSION = $tbl_cdb_names['wrk_submission'];// $courseDbName."wrk_submission";

    $TABLEQUIZ              = $tbl_cdb_names['quiz_test'];//  $courseDbName."quiz_test";
    $TABLEQUIZQUESTION      = $tbl_cdb_names['quiz_rel_test_question'];
    $TABLEQUIZQUESTIONLIST  = $tbl_cdb_names['quiz_question'];//  "quiz_question";
    $TABLEQUIZANSWERSLIST   = $tbl_cdb_names['quiz_answer'];//  "quiz_answer";

    $TABLEPHPBBCATEGORIES   = $tbl_cdb_names['bb_categories'];//  "bb_categories";
    $TABLEPHPBBFORUMS       = $tbl_cdb_names['bb_forums'];//  "bb_forums";
    $TABLEPHPBBNOTIFY       = $tbl_cdb_names['bb_rel_topic_userstonotify'];//  "bb_rel_topic_userstonotify"; //added for notification by email sytem for claroline 1.5
    $TABLEPHPBBPOSTS        = $tbl_cdb_names['bb_posts'];//  "bb_posts";
    $TABLEPHPBBPRIVMSG      = $tbl_cdb_names['bb_priv_msgs'];//  "bb_priv_msgs";
    $TABLEPHPBBTOPICS       = $tbl_cdb_names['bb_topics'];//  "bb_topics";
    $TABLEPHPBBUSERS        = $tbl_cdb_names['bb_users'];//  "bb_users";
    $TABLEPHPBBWHOSONLINE   = $tbl_cdb_names['bb_whosonline'];//  "bb_whosonline";

    //linker
    $TABLELINKS               = $tbl_cdb_names['links'];//  "lnk_links";
    $TABLERESOURCES           = $tbl_cdb_names['resources'];//  "lnk_resources";

    $TABLELEARNPATH          = $tbl_cdb_names['lp_learnPath'];//  "lp_learnPath";
    $TABLEMODULE             = $tbl_cdb_names['lp_module'];//  "lp_module";
    $TABLELEARNPATHMODULE    = $tbl_cdb_names['lp_rel_learnPath_module'];//  "lp_rel_learnPath_module";
    $TABLEASSET              = $tbl_cdb_names['lp_asset'];//  "lp_asset";
    $TABLEUSERMODULEPROGRESS = $tbl_cdb_names['lp_user_module_progress'];//  "lp_user_module_progress";
    // stats
    $TABLETRACKACCESS     = $tbl_cdb_names['track_e_access'];//  "track_e_access";
    $TABLETRACKDOWNLOADS  = $tbl_cdb_names['track_e_downloads'];//  "track_e_downloads";
    $TABLETRACKUPLOADS    = $tbl_cdb_names['track_e_uploads'];//  "track_e_uploads";
    $TABLETRACKEXERCICES  = $tbl_cdb_names['track_e_exercices'];//  "track_e_exercices";
    $TABLETRACKEXEDETAILS = $tbl_cdb_names['track_e_exe_details']; //"track_e_exe_details"
    $TABLETRACKEXEANSWERS = $tbl_cdb_names['track_e_exe_answers']; //"track_e_exe_details"

    //wiki
    $TABLEWIKIPROPERTIES   = $tbl_cdb_names['wiki_properties']; // "wiki_properties"
    $TABLEWIKIACLS         = $tbl_cdb_names['wiki_acls']; // "wiki_acls"
    $TABLEWIKIPAGES        = $tbl_cdb_names['wiki_pages']; // "wiki_pages"
    $TABLEWIKIPAGESCONTENT = $tbl_cdb_names['wiki_pages_content']; // "wiki_pages_content"

    $sqlList[] ="
    CREATE TABLE `".$TABLETOOLANNOUNCEMENTS."` (
      `id` mediumint(11) NOT NULL auto_increment,
      `title` varchar(80) default NULL,
      `contenu` text,
      `temps` date default NULL,
      `ordre` mediumint(11) NOT NULL default '0',
      `visibility` enum('SHOW','HIDE') NOT NULL default 'SHOW',
      PRIMARY KEY  (`id`)
    ) TYPE=MyISAM COMMENT='announcements table'";

    $sqlList[] ="
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

    $sqlList[] ="
    CREATE TABLE `".$TABLETOOLUSERINFODEF."` (
       `id` int(10) unsigned NOT NULL auto_increment,
       `title` varchar(80) NOT NULL default '',
       `comment` varchar(160) default NULL,
       `nbLine` int(10) unsigned NOT NULL default '5',
       `rank` tinyint(3) unsigned NOT NULL default '0',
       PRIMARY KEY  (`id`)
    ) TYPE=MyISAM COMMENT='categories definition for user information of a course'";

    $sqlList[] = "
    CREATE TABLE `".$TABLEPHPBBCATEGORIES."` (
        cat_id int(10) NOT NULL auto_increment,
        cat_title varchar(100),
        cat_order varchar(10),
    PRIMARY KEY (cat_id)
    )";

    $sqlList[] = "
    CREATE TABLE `".$TABLEPHPBBFORUMS."`(
        forum_id int(10) NOT NULL auto_increment,
        group_id int(11) default NULL,
        forum_name varchar(150),
        forum_desc text,
        forum_access int(10) DEFAULT '1',
        forum_moderator int(10),
        forum_topics int(10) DEFAULT '0' NOT NULL,
        forum_posts int(10) DEFAULT '0' NOT NULL,
        forum_last_post_id int(10) DEFAULT '0' NOT NULL,
        cat_id int(10),
        forum_type int(10) DEFAULT '0',
    PRIMARY KEY (forum_id),
        KEY forum_last_post_id (forum_last_post_id),
        forum_order int(10) DEFAULT '0'
    )";

    $sqlList[] = "
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
    )";


//  Structure de la table 'priv_msgs'
    $sqlList[] = "
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
    )";


//  Structure de la table 'topics'
    $sqlList[] = "
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
    )";


//  Structure de la table 'users'
    $sqlList[] = "
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
    )";


//  Structure de la table 'whosonline'
    $sqlList[] = "
    CREATE TABLE `".$TABLEPHPBBWHOSONLINE."` (
        id int(3) NOT NULL auto_increment,
        ip varchar(255),
        name varchar(255),
        count varchar(255),
        date varchar(255),
        username varchar(40),
        forum int(10),
    PRIMARY KEY (id)
    )";


    $sqlList[] = "
    CREATE TABLE `".$TABLEPHPBBNOTIFY."` (
    `notify_id` int(10) NOT NULL auto_increment,
    `user_id` int(10) NOT NULL default '0',
    `topic_id` int(10) NOT NULL default '0',
    PRIMARY KEY  (`notify_id`),
    KEY `SECONDARY` (`user_id`,`topic_id`)
    )";


//  EXERCICES
    $sqlList[] = "
    CREATE TABLE `".$TABLEQUIZ."` (
        `id` mediumint(8) unsigned NOT NULL auto_increment,
        `titre` varchar(200) NOT NULL,
        `description` text NOT NULL,
        `type` tinyint(4) unsigned NOT NULL default '1',
        `random` smallint(6) NOT NULL default '0',
        `active` tinyint(4) unsigned NOT NULL default '0',
        `max_time` smallint(5) unsigned NOT NULL default '0',
  `max_attempt` tinyint(3) unsigned NOT NULL default '0',
  `show_answer` enum('ALWAYS','NEVER','LASTTRY') NOT NULL default 'ALWAYS',
  `anonymous_attempts` enum('YES','NO') NOT NULL default 'YES',
  `start_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `end_date` datetime NOT NULL default '0000-00-00 00:00:00',
    PRIMARY KEY  (id)
    )";

//  QUESTIONS
    $sqlList[] = "
    CREATE TABLE `".$TABLEQUIZQUESTIONLIST."` (
        id mediumint(8) unsigned NOT NULL auto_increment,
        question varchar(200) NOT NULL,
        description text NOT NULL,
        ponderation float unsigned default NULL,
        q_position mediumint(8) unsigned NOT NULL default '1',
        type tinyint(3) unsigned NOT NULL default '2',
   attached_file varchar(50) default '',
    PRIMARY KEY  (id)
    )";

//  REPONSES
    $sqlList[] = "
    CREATE TABLE `".$TABLEQUIZANSWERSLIST."` (
        id mediumint(8) unsigned NOT NULL default '0',
        question_id mediumint(8) unsigned NOT NULL default '0',
        reponse text NOT NULL,
        correct mediumint(8) unsigned default NULL,
        comment text default NULL,
        ponderation float default NULL,
        r_position mediumint(8) unsigned NOT NULL default '1',
    PRIMARY KEY  (id, question_id)
    )";

//  EXERCICE_QUESTION
    $sqlList[] = "
    CREATE TABLE `".$TABLEQUIZQUESTION."` (
        question_id mediumint(8) unsigned NOT NULL default '0',
        exercice_id mediumint(8) unsigned NOT NULL default '0',
    PRIMARY KEY  (question_id,exercice_id)
    )";

#######################COURSE_DESCRIPTION ################################
    $sqlList[] = "
    CREATE TABLE `".$TABLETOOLCOURSEDESC."` (
        `id` TINYINT UNSIGNED DEFAULT '0' NOT NULL,
        `title` VARCHAR(255),
        `content` TEXT,
        `upDate` DATETIME NOT NULL,
        `visibility` enum('SHOW','HIDE') NOT NULL default 'SHOW',
        UNIQUE (`id`)
    )
    COMMENT = 'for course description tool';";

####################### TOOL_LIST ###########################################
    $sqlList[] = "
    CREATE TABLE `".$TABLECOURSEHOMEPAGE."` (
      `id` int(11) NOT NULL auto_increment,
      `tool_id` int(10) unsigned default NULL,
      `rank` int(10) unsigned NOT NULL,
      `access` enum('ALL','PLATFORM_MEMBER','COURSE_MEMBER','COURSE_TUTOR','GROUP_MEMBER','GROUP_TUTOR','COURSE_ADMIN','PLATFORM_ADMIN') NOT NULL default 'ALL',
      `script_url` varchar(255) default NULL,
      `script_name` varchar(255) default NULL,
      PRIMARY KEY  (`id`)) ";

    $sqlList[] =
        "ALTER TABLE `".$TABLECOURSEHOMEPAGE."`
         ADD `addedTool` ENUM('YES','NO') DEFAULT 'YES';";

#################################### AGENDA ################################
    $sqlList[] = "
    CREATE TABLE `".$TABLETOOLAGENDA."` (
        `id` int(11) NOT NULL auto_increment,
        `titre` varchar(200),
        `contenu` text,
        `day` date NOT NULL default '0000-00-00',
        `hour` time NOT NULL default '00:00:00',
        `lasting` varchar(20),
        `visibility` enum('SHOW','HIDE') NOT NULL default 'SHOW',
    PRIMARY KEY (id))";

############################# DOCUMENTS ###########################################
    $sqlList[] = "
    CREATE TABLE `".$TABLETOOLDOCUMENT."` (
        id int(4) NOT NULL auto_increment,
        path varchar(255) NOT NULL,
        visibility char(1) DEFAULT 'v' NOT NULL,
        comment varchar(255),
    PRIMARY KEY (id))";

############################# WORKS ###########################################
// original_id is used to store the author id of the original work if this is a feedback
// private_feedback
    $sqlList[] = "
    CREATE TABLE `".$TABLETOOLWRKSUBMISSION."` (
        `id` int(11) NOT NULL auto_increment,
        `assignment_id` int(11) default NULL,
        `parent_id` int(11) default NULL,
        `user_id` int(11) default NULL,
        `group_id` int(11) default NULL,
        `title` varchar(200) NOT NULL default '',
        `visibility` enum('VISIBLE','INVISIBLE') default 'VISIBLE',
        `creation_date` datetime NOT NULL default '0000-00-00 00:00:00',
        `last_edit_date` datetime NOT NULL default '0000-00-00 00:00:00',
        `authors` varchar(200) NOT NULL default '',
        `submitted_text` text NOT NULL,
        `submitted_doc_path` varchar(200) NOT NULL default '',
        `private_feedback` text default NULL,
        `original_id` int(11) default NULL,
        `score` smallint(3) NULL default NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;";

    $sqlList[] = "
    CREATE TABLE `".$TABLETOOLWRKASSIGNMENT."` (
        `id` int(11) NOT NULL auto_increment,
        `title` varchar(200) NOT NULL default '',
        `description` text NOT NULL,
        `visibility` enum('VISIBLE','INVISIBLE') NOT NULL default 'VISIBLE',
        `def_submission_visibility` enum('VISIBLE','INVISIBLE') NOT NULL default 'VISIBLE',
        `assignment_type` enum('INDIVIDUAL','GROUP') NOT NULL default 'INDIVIDUAL',
        `authorized_content`  enum('TEXT','FILE','TEXTFILE') NOT NULL default 'FILE',
        `allow_late_upload` enum('YES','NO') NOT NULL default 'YES',
        `start_date` datetime NOT NULL default '0000-00-00 00:00:00',
        `end_date` datetime NOT NULL default '0000-00-00 00:00:00',
        `prefill_text` text NOT NULL,
        `prefill_doc_path` varchar(200) NOT NULL default '',
        `prefill_submit` enum('ENDDATE','AFTERPOST') NOT NULL default 'ENDDATE',
        PRIMARY KEY  (`id`)
    ) TYPE=MyISAM;";

    ########## GROUPPS ###############
    $sqlList[] = "
    CREATE TABLE `".$TABLEGROUPS."` (
    id int(11) NOT NULL auto_increment,
        name varchar(100) default NULL,
        description text,
        tutor int(11) default NULL,
        maxStudent int(11) NULL default '0',
        secretDirectory varchar(30) NOT NULL default '0',
    PRIMARY KEY  (id)
    )";

    $sqlList[] = "
    CREATE TABLE `".$TABLEGROUPUSER."` (
        id int(11) NOT NULL auto_increment,
        user int(11) NOT NULL default '0',
        team int(11) NOT NULL default '0',
        status int(11) NOT NULL default '0',
        role varchar(50) NOT NULL default '',
    PRIMARY KEY  (id)
    )";

    $sqlList[] = "
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
    )";

############################## INTRODUCTION #######################################

    $sqlList[] = "
    CREATE TABLE `".$TABLEINTROS."` (
  `id` int(11) NOT NULL auto_increment,
  `tool_id` int(11) NOT NULL default '0',
  `title` varchar(255) default NULL,
  `display_date` datetime default NULL,
  `content` text,
  `rank` int(11) default '1',
  `visibility` enum('SHOW','HIDE') NOT NULL default 'SHOW',
  PRIMARY KEY  (`id`)
)";


############################# LEARNING PATHS ######################################
    $sqlList[] = "
         CREATE TABLE `".$TABLEMODULE."` (
              `module_id` int(11) NOT NULL auto_increment,
              `name` varchar(255) NOT NULL default '',
              `comment` text NOT NULL,
              `accessibility` enum('PRIVATE','PUBLIC') NOT NULL default 'PRIVATE',
              `startAsset_id` int(11) NOT NULL default '0',
              `contentType` enum('CLARODOC','DOCUMENT','EXERCISE','HANDMADE','SCORM','LABEL') NOT NULL,
              `launch_data` text NOT NULL,
              PRIMARY KEY  (`module_id`)
            ) TYPE=MyISAM COMMENT='List of available modules used in learning paths';";

    $sqlList[] = "
          CREATE TABLE `".$TABLELEARNPATH."` (
              `learnPath_id` int(11) NOT NULL auto_increment,
              `name` varchar(255) NOT NULL default '',
              `comment` text NOT NULL,
              `lock` enum('OPEN','CLOSE') NOT NULL default 'OPEN',
              `visibility` enum('HIDE','SHOW') NOT NULL default 'SHOW',
              `rank` int(11) NOT NULL default '0',
              PRIMARY KEY  (`learnPath_id`),
              UNIQUE KEY rank (`rank`)
            ) TYPE=MyISAM COMMENT='List of learning Paths';";

    $sqlList[] = ("
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

    $sqlList[] = "
          CREATE TABLE `".$TABLEASSET."` (
              `asset_id` int(11) NOT NULL auto_increment,
              `module_id` int(11) NOT NULL default '0',
              `path` varchar(255) NOT NULL default '',
              `comment` varchar(255) default NULL,
              PRIMARY KEY  (`asset_id`)
            ) TYPE=MyISAM COMMENT='List of resources of module of learning paths';";

    $sqlList[] = "
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
            ) TYPE=MyISAM COMMENT='Record the last known status of the user in the course';";


        ########################## STATISTICS ##############################
        $sqlList[] = "CREATE TABLE `".$TABLETRACKACCESS."` (
                  `access_id` int(11) NOT NULL auto_increment,
                  `access_user_id` int(10) default NULL,
                  `access_date` datetime NOT NULL default '0000-00-00 00:00:00',
                  `access_tid` int(10) default NULL,
                  `access_tlabel` varchar(8) default NULL,
                  PRIMARY KEY  (`access_id`)
                ) TYPE=MyISAM COMMENT='Record informations about access to course or tools'";


        $sqlList[] = "CREATE TABLE `".$TABLETRACKDOWNLOADS."` (
                  `down_id` int(11) NOT NULL auto_increment,
                  `down_user_id` int(10) default NULL,
                  `down_date` datetime NOT NULL default '0000-00-00 00:00:00',
                  `down_doc_path` varchar(255) NOT NULL default '0',
                  PRIMARY KEY  (`down_id`)
                ) TYPE=MyISAM COMMENT='Record informations about downloads'";


        $sqlList[] = "CREATE TABLE `".$TABLETRACKEXERCICES."` (
                  `exe_id` int(11) NOT NULL auto_increment,
                  `exe_user_id` int(10) default NULL,
                  `exe_date` datetime NOT NULL default '0000-00-00 00:00:00',
                  `exe_exo_id` tinyint(4) NOT NULL default '0',
                  `exe_result` float NOT NULL default '0',
                  `exe_time`    mediumint(8) NOT NULL default '0',
                  `exe_weighting` float NOT NULL default '0',
                  PRIMARY KEY  (`exe_id`)
                ) TYPE=MyISAM COMMENT='Record informations about exercices'";


        $sqlList[] = "CREATE TABLE `".$TABLETRACKEXEDETAILS."` (
                  `id` int(11) NOT NULL auto_increment,
                  `exercise_track_id` int(11) NOT NULL default '0',
                  `question_id` int(11) NOT NULL default '0',
                  `result` float NOT NULL default '0',
                  PRIMARY KEY  (`id`)
                ) TYPE=MyISAM COMMENT='Record answers of students in exercices'";


        $sqlList[] = "CREATE TABLE `" . $TABLETRACKEXEANSWERS . "` (
                  `id` int(11) NOT NULL auto_increment,
                  `details_id` int(11) NOT NULL default '0',
                  `answer` text NOT NULL,
                  PRIMARY KEY  (`id`)
                ) TYPE=MyISAM COMMENT=''";


        $sqlList[] = "CREATE TABLE `".$TABLETRACKUPLOADS."` (
                  `upload_id` int(11) NOT NULL auto_increment,
                  `upload_user_id` int(10) default NULL,
                  `upload_date` datetime NOT NULL default '0000-00-00 00:00:00',
                  `upload_work_id` int(11) NOT NULL default '0',
                  PRIMARY KEY  (`upload_id`)
                ) TYPE=MyISAM COMMENT='Record some more informations about uploaded works'";


    ########################## linker ##############################
        $sqlList[] = "CREATE TABLE IF NOT EXISTS `".$TABLELINKS."` (
                  `id` int(11) NOT NULL auto_increment,
                    `src_id` int(11) NOT NULL default '0',
                    `dest_id` int(11) NOT NULL default '0',
                    `creation_time` timestamp(14) NOT NULL,
                    PRIMARY KEY  (`id`)
                ) TYPE=MyISAM";


        $sqlList[] = "CREATE TABLE IF NOT EXISTS `".$TABLERESOURCES."` (
                   `id` int(11) NOT NULL auto_increment,
                  `crl` text NOT NULL,
                  `title` text NOT NULL,
                  PRIMARY KEY  (`id`)
                ) TYPE=MyISAM";


    ######################## wiki ##################################

    $sqlList[] = "CREATE TABLE IF NOT EXISTS `".$TABLEWIKIPROPERTIES."`(
            `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `title` VARCHAR(255) NOT NULL DEFAULT '',
            `description` TEXT NULL,
            `group_id` INT(11) NOT NULL DEFAULT 0,
            PRIMARY KEY(`id`)
            )"
            ;

    $sqlList[] = "CREATE TABLE IF NOT EXISTS `".$TABLEWIKIACLS."` (
            `wiki_id` INT(11) UNSIGNED NOT NULL,
            `flag` VARCHAR(255) NOT NULL,
            `value` ENUM('false','true') NOT NULL DEFAULT 'false'
            )"
            ;

    $sqlList[] = "CREATE TABLE IF NOT EXISTS `".$TABLEWIKIPAGES."` (
            `id` int(11) unsigned NOT NULL auto_increment,
            `wiki_id` int(11) unsigned NOT NULL default '0',
            `owner_id` int(11) unsigned NOT NULL default '0',
            `title` varchar(255) NOT NULL default '',
            `ctime` datetime NOT NULL default '0000-00-00 00:00:00',
            `last_version` int(11) unsigned NOT NULL default '0',
            `last_mtime` datetime NOT NULL default '0000-00-00 00:00:00',
            PRIMARY KEY  (`id`)
            )"
            ;

    $sqlList[] = "CREATE TABLE IF NOT EXISTS `".$TABLEWIKIPAGESCONTENT."` (
            `id` int(11) unsigned NOT NULL auto_increment,
            `pid` int(11) unsigned NOT NULL default '0',
            `editor_id` int(11) NOT NULL default '0',
            `mtime` datetime NOT NULL default '0000-00-00 00:00:00',
            `content` text NOT NULL,
            PRIMARY KEY  (`id`)
            )"
            ;

    foreach($sqlList as $thisSql)
    {
        if ( claro_sql_query($thisSql) == false) return false;
        else                                     continue;
    }

    return true;
};

/**
 * Add starting files in course
 *
 * @param    string    $courseRepository        path from $coursesRepositorySys to root of course
 *
 * @author Christophe Gesché <moosh@claroline.net>
 * @version 1.0
 */

function     fill_course_repository($courseRepository)
{
  // WARNING. Do not forget to adapt queries in fill_Db_course()
  // if something changed here

    global $clarolineRepositorySys, $coursesRepositorySys;

    return copy($clarolineRepositorySys.'document/Example_document.pdf',
                $coursesRepositorySys.$courseRepository.'/document/Example_document.pdf');
};

/**
 * Insert starting data in db of course.
 *
 * @param  string  $courseDbName        partial DbName. to build as $courseTablePrefix.$courseDbName.$dbGlu;
 * @param  string  $language            language request for this course
 *
 * @author Christophe Gesché <moosh@claroline.net>
 * @version 1.0
 *
 * note  $language would be removed soon.
 */

function fill_db_course($courseDbName,$language)
{
    global $singleDbEnabled, $courseTablePrefix, $dbGlu,
           $clarolineRepositorySys, $_user, $includePath;

    // include the language file with all language variables
    language::load_translation($language,'TRANSLATION');
    language::load_locale_settings($language);

    $courseDbName = $courseTablePrefix . $courseDbName.$dbGlu;
    $tbl_cdb_names = claro_sql_get_course_tbl($courseDbName);
    $TABLECOURSEHOMEPAGE    = $tbl_cdb_names['tool'];

    $TABLEGROUPPROPERTIES    = $tbl_cdb_names['group_property'];// $courseDbName."group_property";

    $TABLEQUIZ              = $tbl_cdb_names['quiz_test'];//  $courseDbName."quiz_test";
    $TABLEQUIZQUESTION      = $tbl_cdb_names['quiz_rel_test_question'];
    $TABLEQUIZQUESTIONLIST  = $tbl_cdb_names['quiz_question'];//  "quiz_question";
    $TABLEQUIZANSWERSLIST   = $tbl_cdb_names['quiz_answer'];//  "quiz_answer";

    $TABLEPHPBBCATEGORIES   = $tbl_cdb_names['bb_categories'];//  "bb_categories";
    $TABLEPHPBBFORUMS       = $tbl_cdb_names['bb_forums'];//  "bb_forums";
    $TABLEPHPBBPOSTS        = $tbl_cdb_names['bb_posts'];//  "bb_posts";
    $TABLEPHPBBPOSTSTEXT    = $tbl_cdb_names['bb_posts_text'];//  "bb_posts_text";
    $TABLEPHPBBTOPICS       = $tbl_cdb_names['bb_topics'];//  "bb_topics";
    $TABLEPHPBBUSERS        = $tbl_cdb_names['bb_users'];//  "bb_users";

    $TABLELEARNPATH         = $tbl_cdb_names['lp_learnPath'];//  "lp_learnPath";
    $TABLEMODULE            = $tbl_cdb_names['lp_module'];//  "lp_module";
    $TABLELEARNPATHMODULE   = $tbl_cdb_names['lp_rel_learnPath_module'];//  "lp_rel_learnPath_module";
    $TABLEASSET             = $tbl_cdb_names['lp_asset'];//  "lp_asset";

    $nom = $_user['lastName'];
    $prenom = $_user['firstName'];
    $email = $_user['mail'];

    mysql_select_db($courseDbName);

// Create an example category
    claro_sql_query("INSERT INTO `".$TABLEPHPBBCATEGORIES."` VALUES (2,'".addslashes(get_lang('CatagoryMain'))."',1)");

// Create a hidden category for group forums
    claro_sql_query("INSERT INTO `".$TABLEPHPBBCATEGORIES."` VALUES (1,'".addslashes(get_lang('CatagoryGroup'))."',2)");
############################## GROUPS ###########################################
    claro_sql_query("INSERT INTO `".$TABLEGROUPPROPERTIES."`
(id, self_registration, private, forum, document, wiki, chat)
VALUES (NULL, '1', '0', '1', '1', '1', '1')");
    claro_sql_query("INSERT
                        INTO `".$TABLEPHPBBFORUMS."`
                        VALUES ( 1
                               , NULL
                               , '".addslashes(get_lang('TestForum'))."'
                               , '".addslashes(get_lang('DelAdmin'))."'
                               ,2,1,1,1,1,2,0,1)");
    claro_sql_query("INSERT INTO `".$TABLEPHPBBPOSTS."` VALUES (1,1,1,1,NOW(),'127.0.0.1',\"".addslashes($nom)."\",\"".addslashes($prenom)."\")");
    claro_sql_query("CREATE TABLE `".$TABLEPHPBBPOSTSTEXT."` (
        post_id int(10) DEFAULT '0' NOT NULL,
        post_text text,
        PRIMARY KEY (post_id)
        )");
    claro_sql_query("INSERT INTO `".$TABLEPHPBBPOSTSTEXT."` VALUES ('1', '".addslashes(get_lang('Message'))."')");
// Contenu de la table 'users'
    claro_sql_query("INSERT INTO `".$TABLEPHPBBUSERS."` VALUES (
       '1',
       '".addslashes($nom." ".$prenom)."',
       NOW(),
       'password',
       '".addslashes($email)."',
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
    claro_sql_query("INSERT INTO `".$TABLEPHPBBUSERS."` VALUES (
       '-1',       '".addslashes(get_lang('Anonymous'))."',       NOW(),       'password',       '',
       NULL,       NULL,       NULL,       NULL,       NULL,       NULL,       NULL,
       NULL,       NULL,       NULL,       NULL,       '0',       '0',       '0',       '0',       '0',
       '0',       '1',       NULL,       NULL,       NULL       )");

##################### register tools in course ######################################

    $tbl_mdb_names   = claro_sql_get_main_tbl();
    $TABLECOURSETOOL = $tbl_mdb_names['tool'  ];

    $sql = "SELECT id, def_access, def_rank, claro_label FROM `". $TABLECOURSETOOL . "` where add_in_course = 'AUTOMATIC'";

    $result = claro_sql_query($sql);

    if (mysql_num_rows($result) > 0)
    {
        while ( ($courseTool = mysql_fetch_array($result, MYSQL_ASSOC) ))
        {
            $sql_insert = " INSERT INTO `" . $TABLECOURSEHOMEPAGE . "` "
                        . " (tool_id, rank, access) "
                        . " VALUES ('" . $courseTool['id'] . "','" . $courseTool['def_rank'] . "','" . $courseTool['def_access'] . "')";
            claro_sql_query_insert_id($sql_insert);
        }
    }

############################## EXERCICES #######################################
    claro_sql_query("INSERT INTO `".$TABLEQUIZANSWERSLIST."` VALUES ( '1', '1', '".addslashes(get_lang('Ridiculise'))."', '0', '".addslashes(get_lang('NoPsychology'))."', '-5', '1')");
    claro_sql_query("INSERT INTO `".$TABLEQUIZANSWERSLIST."` VALUES ( '2', '1', '".addslashes(get_lang('AdmitError'))."', '0', '".addslashes(get_lang('NoSeduction'))."', '-5', '2')");
    claro_sql_query("INSERT INTO `".$TABLEQUIZANSWERSLIST."` VALUES ( '3', '1', '".addslashes(get_lang('Force'))."', '1', '".addslashes(get_lang('Indeed'))."', '5', '3')");
    claro_sql_query("INSERT INTO `".$TABLEQUIZANSWERSLIST."` VALUES ( '4', '1', '".addslashes(get_lang('Contradiction'))."', '1', '".addslashes(get_lang('NotFalse'))."', '5', '4')");
    claro_sql_query("INSERT INTO `".$TABLEQUIZ."` VALUES ( '1', '".addslashes(get_lang('ExerciceEx'))."', '".addslashes(get_lang('Antique'))."', '1', '0', '0', '0', '0' , 'ALWAYS', 'NO', NOW(), DATE_ADD(NOW(), INTERVAL 1 YEAR) )");
    claro_sql_query("INSERT INTO `".$TABLEQUIZQUESTIONLIST."` VALUES ( '1', '".addslashes(get_lang('SocraticIrony'))."', '".addslashes(get_lang('ManyAnswers'))."', '10', '1', '2','')");
    claro_sql_query("INSERT INTO `".$TABLEQUIZQUESTION."` VALUES ( '1', '1')");

############################### LEARNING PATH  ####################################
  // HANDMADE module type are not used for first version of claroline 1.5 beta so we don't show any exemple!
  claro_sql_query("INSERT INTO `".$TABLELEARNPATH."` VALUES ('1', '".addslashes(get_lang('SampleLearnPath'))."', '".addslashes(get_lang('SampleLearnPathDesc'))."', 'OPEN', 'SHOW', '1')");

  claro_sql_query("INSERT INTO `".$TABLELEARNPATHMODULE."` VALUES ('1', '1', '1', 'OPEN', 'SHOW', '', '1', '0', '50')");
  claro_sql_query("INSERT INTO `".$TABLELEARNPATHMODULE."` VALUES ('2', '1', '2', 'OPEN', 'SHOW', '', '2', '0', '50')");

  claro_sql_query("INSERT INTO `".$TABLEMODULE."` VALUES ('1', '".addslashes(get_lang('SampleDocument'))."', '".addslashes(get_lang('SampleDocumentDesc'))."', 'PRIVATE', '1', 'DOCUMENT', '')");
  claro_sql_query("INSERT INTO `".$TABLEMODULE."` VALUES ('2', '".addslashes(get_lang('ExerciceEx'))."', '".addslashes(get_lang('SampleExerciseDesc'))."', 'PRIVATE', '2', 'EXERCISE', '')");

  claro_sql_query("INSERT INTO `".$TABLEASSET."` VALUES ('1', '1', '/Example_document.pdf', '')");
  claro_sql_query("INSERT INTO `".$TABLEASSET."` VALUES ('2', '2', '1', '')");

############################## FORUMS  #######################################
    claro_sql_query("INSERT INTO `".$TABLEPHPBBTOPICS."` VALUES (1,'".addslashes(get_lang('ExMessage'))."',-1,NOW(),1,0,1,1,'0','1', '".addslashes($nom)."', '".addslashes($prenom)."')");

    return true;
};


/**
 * To create a record in the course tabale of main database
 * @param string    $courseSysCode
 * @param string    $courseScreenCode
 * @param string    $courseRepository
 * @param string    $courseDbName
 * @param string    $titular
 * @param string    $email
 * @param string    $faculte
 * @param string    $intitule
 * @param string    $languageCourse
 * @param string    $uidCreator
 * @param bool      $visibility
 * @param bool      $registrationAllowed
 * @param string    $enrollmentKey
 * @author Christophe Gesché <moosh@claroline.net>
 */

function register_course($courseSysCode, $courseScreenCode, $courseRepository, $courseDbName, $titular, $email, $faculte, $intitule, $languageCourse='', $uidCreator, $visibility, $registrationAllowed, $enrollmentKey='', $expirationDate='', $extLinkName='', $extLinkUrl='')
{
    global $includePath, $versionDb, $clarolineVersion;

    $tblList         = claro_sql_get_main_tbl();
    $tbl_course      = $tblList['course'         ];
    $tbl_course_user = $tblList['rel_course_user'];

    // Needed parameters
    if ($courseSysCode    == '') return claro_failure::set_failure('courseSysCode is missing');
    if ($courseScreenCode == '') return claro_failure::set_failure('courseScreenCode is missing');
    if ($courseDbName     == '') return claro_failure::set_failure('courseDbName is missing');
    if ($courseRepository == '') return claro_failure::set_failure('course Repository is missing');
    if ($uidCreator       == '') return claro_failure::set_failure('uidCreator is missing');

    if     ( ! $visibility && ! $registrationAllowed) $visibilityState = 0;
    elseif ( ! $visibility &&   $registrationAllowed) $visibilityState = 1;
    elseif (   $visibility && ! $registrationAllowed) $visibilityState = 3;
    elseif (   $visibility &&   $registrationAllowed) $visibilityState = 2;

    // optionnal parameters
    if ($languageCourse == '') $languageCourse = 'english';
    if ($expirationDate == '') $expirationDate = 'NULL';
    else                       $expirationDate = 'FROM_UNIXTIME('.$expirationDate.')';

    $currenVersionFilePath = $includePath . '/currentVersion.inc.php';
    file_exists($currenVersionFilePath) && require $includePath . '/currentVersion.inc.php';

    $sql = "INSERT INTO `" . $tbl_course . "` SET
            code              = '" . addslashes($courseSysCode)    . "',
            dbName            = '" . addslashes($courseDbName)     . "',
            directory         = '" . addslashes($courseRepository) . "',
            languageCourse    = '" . addslashes($languageCourse)   . "',
            intitule          = '" . addslashes($intitule)         . "',
            faculte           = '" . addslashes($faculte)          . "',
            visible           = '" . (int) $visibilityState        . "',
            enrollment_key    = '".  addslashes($enrollmentKey)    . "',
            diskQuota         = NULL,
            creationDate      = NOW(),
            expirationDate    = " . addslashes($expirationDate)   . ",
            versionDb         = '" . addslashes($versionDb)        . "',
            versionClaro      = '" . addslashes($clarolineVersion) . "',
            lastEdit          = NOW(),
            lastVisit         = NULL,
            titulaires        = '" . addslashes($titular)          . "',
            email             = '" . addslashes($email)            . "',
            fake_code         = '" . addslashes($courseScreenCode) . "',
            departmentUrlName = '".  addslashes($extLinkName)      ."',
            departmentUrl     = '".  addslashes($extLinkUrl)       ."'";

    if ( claro_sql_query($sql) == false) return false;

    $sql = "INSERT INTO `" . $tbl_course_user . "`
            SET code_cours     = '" . $courseSysCode . "',
                user_id = '" . (int) $uidCreator."',
                statut  = 1,
                role    = '" . addslashes( get_lang('Course manager') ) . "',
                tutor   = 1";

    if ( claro_sql_query($sql) == false) return false;

    return true;
}

/**
 * get  uid list of platform admin
 * @author Christophe Gesché <moosh@claroline.net>
 * @return array of uid
 */
function claro_get_admin_list()
{
    $tbl_mdb_names = claro_sql_get_main_tbl();

    $sql = "SELECT `idUser` FROM `" . $tbl_mdb_names['admin'] . "`";
    return  claro_sql_query_fetch_all($sql);
}
?>
