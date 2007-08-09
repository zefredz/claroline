<?php // $Id$

if ( count( get_included_files() ) == 1 )
{
    die( 'The file ' . basename(__FILE__) . ' cannot be accessed directly, use include instead' );
}

/**
 * CLAROLINE
 *
 * add_course lib contain function to add a course
 * add is, find keys names aivailable, build the the course database
 * fill the course database, build the content directorys, build the index page
 * build the directory tree, register the course.
 *
 * @version 1.9 $Revision$
 *
 * @copyright (c) 2001-2007 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @see http://www.claroline.net/wiki/CLCRS/
 *
 * @package COURSE
 *
 * @author Claro Team <cvs@claroline.net>
 * @author Christophe Gesché <moosh@claroline.net>
 * @author Frédéric Minne <zefredz@claroline.net>
 *
 */

require_once get_path('includePath') . '/lib/course_user.lib.php';

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
 * @return array
 * - ["currentCourseCode"]          : Must be alphaNumeric and outputable in HTML System
 * - ["currentCourseId"]            : Must be unique in mainDb.course it's the primary key
 * - ["currentCourseDbName"]        : Must be unique it's the database name.
 * - ["currentCourseRepository"]    : Must be unique in /get_path('coursesRepositorySys')/
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
                             $addUniquePrefix = false,
                             $useCodeInDepedentKeys = true,
                             $addUniqueSuffix = false

                             )
{
    $tbl_mdb_names = claro_sql_get_main_tbl();
    $tbl_course    = $tbl_mdb_names['course'];


    $nbCharFinalSuffix = get_conf('nbCharFinalSuffix','3');

    // $keys["currentCourseCode"] is the "public code"

    $wantedCode =  strtr($wantedCode,
    'ÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝßàáâãäåæçèéêëìíîïðñòóôõöøùúûüýÿ',
    'AAAAAACEEEEIIIIDNOOOOOOUUUUYsaaaaaaaceeeeiiiionoooooouuuuyy');

    //$wantedCode = strtoupper($wantedCode);
    $charToReplaceByUnderscore = '- ';
    $wantedCode = preg_replace('/['.$charToReplaceByUnderscore.']/', '_', $wantedCode);
    $wantedCode = preg_replace('/[^A-Za-z0-9_]/', '', $wantedCode);

    if ($wantedCode=='') $wantedCode = get_conf('prefixAntiEmpty');

    $keysCourseCode    = $wantedCode;

    if (!$useCodeInDepedentKeys) $wantedCode = '';
    // $keys['currentCourseId'] would Became $cid in normal using.

    if ($addUniquePrefix) $uniquePrefix =  substr(md5 (uniqid('')),0,10);
    else                  $uniquePrefix = '';

    if ($addUniqueSuffix) $uniqueSuffix =  substr(md5 (uniqid('')),0,10);

    else                  $uniqueSuffix = '';

    $keysAreUnique = false;

    $finalSuffix = array('CourseId'=>''
                        ,'CourseDb'=>''
                        ,'CourseDir'=>''
                        );
                        
    $tryNewFSCId = $tryNewFSCDb = $tryNewFSCDir = 0;

    while (!$keysAreUnique)
    {
         $keysCourseId     = $prefix4all
         .                   $uniquePrefix
         .                   strtoupper($wantedCode)
         .                   $uniqueSuffix
         .                   ($finalSuffix['CourseId'] > 0?
                             sprintf("_%0" . $nbCharFinalSuffix . "s", $finalSuffix['CourseId']):'')
         ;

         $keysCourseDbName = $prefix4baseName
         .                   $uniquePrefix
         .                   strtoupper($wantedCode)
         .                   $uniqueSuffix
         .                   ($finalSuffix['CourseDb'] > 0?
                             sprintf("_%0" . $nbCharFinalSuffix . "s", $finalSuffix['CourseDb']):'')
         ;

         $keysCourseRepository = $prefix4path
         .                       $uniquePrefix
         .                       strtoupper($wantedCode)
         .                       $uniqueSuffix
         .                       ($finalSuffix['CourseDir'] > 0?
                                 sprintf("_%0" . $nbCharFinalSuffix . "s", $finalSuffix['CourseDir']):'')
         ;

        $keysAreUnique = true;
        // Now we go to check if there are unique

        $sqlCheckCourseId    = "SELECT COUNT(code) existAllready
                                FROM `" . $tbl_course . "`
                                WHERE code = '" . $keysCourseId  ."'";

        $resCheckCourseId    = claro_sql_query ($sqlCheckCourseId);
        $isCheckCourseIdUsed = mysql_fetch_array($resCheckCourseId);

        if ($isCheckCourseIdUsed[0]['existAllready'] > 0)
        {
            $keysAreUnique = false;
            $tryNewFSCId++;
            $finalSuffix['CourseId']++;
        };

        if (get_conf('singleDbEnabled'))
        {
            $sqlCheckCourseDb = "SHOW TABLES LIKE '".$keysCourseDbName."%'";
        }
        else
        {
            $sqlCheckCourseDb = "SHOW DATABASES LIKE '".$keysCourseDbName."'";
        }

        $resCheckCourseDb = claro_sql_query ($sqlCheckCourseDb);

        $isCheckCourseDbUsed = mysql_num_rows($resCheckCourseDb);

        if ($isCheckCourseDbUsed > 0)
        {
            $keysAreUnique = false;
            $tryNewFSCDb++;
            $finalSuffix['CourseDb']++;
        };

        if (file_exists(get_path('coursesRepositorySys') . '/' . $keysCourseRepository))
        {
            $keysAreUnique = false;
            $tryNewFSCDir++;
            $finalSuffix['CourseDir']++;

        };

        if(!$keysAreUnique)
        {
            $finalSuffix['CourseDir'] = max($finalSuffix);
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
            trigger_error('too many try for ' .  $wantedCode ,E_USER_WARNING);
            return false;

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
 * @return boolean
 * @author Christophe Gesché <moosh@claroline.net>
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 * @author Frédéric Minne <zefredz@claroline.net>
 */
function prepare_course_repository($courseRepository, $courseId)
{

    if( ! is_dir(get_path('coursesRepositorySys')) )
    {
        claro_mkdir(get_path('coursesRepositorySys'), CLARO_FILE_PERMISSIONS, true);
    }

    $courseDirPath = get_path('coursesRepositorySys') . $courseRepository;

    if ( ! is_writable(get_path('coursesRepositorySys')) )
    {
        return claro_failure::set_failure(
            get_lang( 'Folder %folder is not writable'
                , array( '%folder' => get_path('coursesRepositorySys') ) ) );
    }

    $folderList = array($courseDirPath ,
                        $courseDirPath . '/exercise',
                        $courseDirPath . '/document',
                        $courseDirPath . '/work',
                        $courseDirPath . '/group',
                        $courseDirPath . '/chat',
                        $courseDirPath . '/modules',
                        $courseDirPath . '/scormPackages',
                        $courseDirPath . '/modules/module_1' );

    foreach ( $folderList as $folder )
    {
        if ( ! claro_mkdir($folder, CLARO_FILE_PERMISSIONS,true) )
        {
            return claro_failure::set_failure(
                get_lang( 'Unable to create folder %folder'
                    ,array( '%folder' => $folder ) ) );
        }
    }

    // build index.php of course
    $courseIndex = $courseDirPath . '/index.php';
    
    $courseIndexContent = '<?php ' . "\n"
        . 'header (\'Location: '. get_path('clarolineRepositoryWeb')
        . 'course/index.php?cid=' . htmlspecialchars($courseId) . '\') ;' . "\n"
        . '?' . '>' . "\n"
        ;
    
    if ( ! file_put_contents( $courseIndex, $courseIndexContent ) )
    {
        return claro_failure::set_failure(
            get_lang('Unable to create file %file'
                , array('%file' => 'index.php' ) ) );
    }

    $groupIndex = get_path('coursesRepositorySys')
        . $courseRepository . '/group/index.php'
        ;

    $groupIndexContent = '<?php session_start(); ?'.'>';

    if ( ! file_put_contents( $groupIndex, $groupIndexContent ) )
    {
        return claro_failure::set_failure(
            get_lang('Unable to create file %file'
                , array('%file' => 'group/index.php' ) ) );
    }

    return true;
}

/**
 * Create course database and tables
 *
 * @param  string courseDbName partial dbName form course table tu build real DbName
 * @return boolean
 * @author Christophe Gesché <moosh@claroline.net>
 * @author Frédéric Minne <zefredz@claroline.net>
 */
// function update_db_course($courseDbName, $language, $courseDirectory)
function install_course_database( $courseDbName )
{
    if ( ! create_course_database( $courseDbName ) )
    {
        return false;
    }
    
    if ( ! create_course_tables( $courseDbName ) )
    {
        return false;
    }
    
    return true;
}

/**
 * Install course tool modules
 * @param string courseDbName partial dbName form course table tu build real DbName
 * @param string language course language
 * @param string courseDirectory
 * @return boolean
 */
function install_course_tools( $courseDbName, $language, $courseDirectory )
{
    // rename !!!!
    if ( ! setup_course_tools( $courseDbName, $language, $courseDirectory ) )
    {
        return false;
    }
    
    update_course_tool_list($courseDbName);
    
    return true;
}

/**
 * Run setup scripts for course tool modules
 * @param string courseDbName partial dbName form course table tu build real DbName
 * @param string language course language
 * @param string courseDirectory
 * @author Frédéric Minne <zefredz@claroline.net>
 */
function setup_course_tools( $courseDbName, $language, $courseDirectory )
{
    $installableToolList = get_course_installable_tool_list();
    
    if ( !empty( $installableToolList ) )
    {
        foreach ( $installableToolList as $tool )
        {
            if ( ! install_module_at_course_creation( $tool['claro_label']
                , $courseDbName, $language, $courseDirectory ) )
            {
                return claro_failure::set_failure(
                    get_lang('Unable to database tables for %label%'
                        , array('%label%' => $tool['claro_label'] ) ) );
            }
        }
    }
    
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
 * @param string    $registrationKey
 * @author Christophe Gesché <moosh@claroline.net>
 */

function register_course( $courseSysCode, $courseScreenCode,
                          $courseRepository, $courseDbName,
                          $titular, $email, $faculte, $intitule, $languageCourse='',
                          $uidCreator,
                          $access, $registrationAllowed, $registrationKey='', $visibility=true,
                          $expirationDate='', $extLinkName='', $extLinkUrl='')
{
    global $versionDb, $clarolineVersion;

    $tblList         = claro_sql_get_main_tbl();
    $tbl_course      = $tblList['course'         ];

    // Needed parameters
    if ($courseSysCode    == '') return claro_failure::set_failure('courseSysCode is missing');
    if ($courseScreenCode == '') return claro_failure::set_failure('courseScreenCode is missing');
    if ($courseDbName     == '') return claro_failure::set_failure('courseDbName is missing');
    if ($courseRepository == '') return claro_failure::set_failure('course Repository is missing');
    if ($uidCreator       == '') return claro_failure::set_failure('uidCreator is missing');

    // optionnal parameters
    if ($languageCourse == '') $languageCourse = 'english';
    if ($expirationDate == '') $expirationDate = 'NULL';
    else                       $expirationDate = 'FROM_UNIXTIME('.$expirationDate.')';

    $currentVersionFilePath = get_conf('rootSys') . 'platform/currentVersion.inc.php';
    file_exists($currentVersionFilePath) && require $currentVersionFilePath;

    $defaultProfileId = claro_get_profile_id('user');

    $sql = "INSERT INTO `" . $tbl_course . "` SET
            code                 = '" . addslashes($courseSysCode)    . "',
            dbName               = '" . addslashes($courseDbName)     . "',
            directory            = '" . addslashes($courseRepository) . "',
            language             = '" . addslashes($languageCourse)   . "',
            intitule             = '" . addslashes($intitule)         . "',
            faculte              = '" . addslashes($faculte)          . "',
            visibility           = '".  ($visibility?'VISIBLE':'INVISIBLE')    . "',
            access               = '".  ($access?'PUBLIC':'PRIVATE')    . "',
            registration         = '".  ($registrationAllowed?'OPEN':'CLOSE')    . "',
            registrationKey      = '".  addslashes($registrationKey)    . "',
            diskQuota            = NULL,
            creationDate         = NOW(),
            expirationDate       = " . addslashes($expirationDate)   . ",
            versionDb            = '" . addslashes($versionDb)        . "',
            versionClaro         = '" . addslashes($clarolineVersion) . "',
            lastEdit             = NOW(),
            lastVisit            = NULL,
            titulaires           = '" . addslashes($titular)          . "',
            email                = '" . addslashes($email)            . "',
            administrativeNumber = '" . addslashes($courseScreenCode) . "',
            extLinkName          = '" . addslashes($extLinkName)      . "',
            extLinkUrl           = '" . addslashes($extLinkUrl)       . "',
            defaultProfileId     = " . $defaultProfileId ;

    if ( claro_sql_query($sql) == false) return false;

    // add user to course

    if ( user_add_to_course($uidCreator, $courseSysCode, true, true) === false )
    {
        return false;
    }

    return true;
}


/**
 * Get the list of all installable course tool modules from kernel
 * @author Frédéric Minne <zefredz@claroline.net>
 */
function get_course_installable_tool_list()
{
    $tbl_mdb_names   = claro_sql_get_main_tbl();

    $tbl_courseTool = $tbl_mdb_names['tool'  ];

    $sql = "SELECT id, def_access, def_rank, claro_label "
        . "FROM `". $tbl_courseTool . "` "
        . "WHERE add_in_course = 'AUTOMATIC'"
        ;

    $list = claro_sql_query_fetch_all_rows($sql);
    
    return $list;
}

// TODO: check if tool installed successfuly !!!!
/**
 * Register installed course tool in course database
 * @author Frédéric Minne <zefredz@claroline.net>
 */
function update_course_tool_list($courseDbName)
{
    $toolList = get_course_installable_tool_list();
    
    $courseDbName = get_conf('courseTablePrefix') . $courseDbName . get_conf('dbGlu');

    $tbl_cdb_names = claro_sql_get_course_tbl($courseDbName);
    $tbl_courseToolList    = $tbl_cdb_names['tool'];

    foreach ( $toolList as $courseTool )
    {
        $sql_insert = " INSERT INTO `" . $tbl_courseToolList . "` "
            . " (tool_id, rank, visibility) "
            . " VALUES ('" . $courseTool['id'] . "',"
            . "'" . $courseTool['def_rank'] . "',"
            . "'" .($courseTool['def_access']=='ALL'?1:0) . "')"
            ;
            
        claro_sql_query_insert_id($sql_insert);
    }
}

/**
 * Create course database :
 * @param string courseDbName partial dbName form course table tu build real DbName
 * @return boolean
 */
function create_course_database( $courseDbName )
{
    // Create course database
    if ( !get_conf( 'singleDbEnabled' ) )
    {
        claro_sql_query('CREATE DATABASE `'.$courseDbName.'`');

        if (claro_sql_errno() > 0)
        {
            return claro_failure::set_failure(
                get_lang( 'Unable to create course database' ) );
        }
    }
    
    return true;
}

/**
 * Create course tables in database :
 * @param string courseDbName partial dbName form course table tu build real DbName
 * @return boolean
 */
function create_course_tables( $courseDbName )
{
    $courseDbName = get_conf('courseTablePrefix') . $courseDbName . get_conf('dbGlu');

    // var_dump( $GLOBALS['_course'] );

    $tbl_cdb_names = claro_sql_get_course_tbl($courseDbName);
    
    // Tool list
    $tbl_toolList           = $tbl_cdb_names['tool'];
    
    // Course properties
    $tbl_courseProperties   = $tbl_cdb_names['course_properties'];
    
    // Intro sections
    $tbl_introSection       = $tbl_cdb_names['tool_intro'];

    // Group
    $tbl_groups             = $tbl_cdb_names['group_team'];
    $tbl_groupUser          = $tbl_cdb_names['group_rel_team_user'];


    // User Info
    $tbl_userInfoContent    = $tbl_cdb_names['userinfo_content'];
    $tbl_userInfoDef        = $tbl_cdb_names['userinfo_def'];
    
    // Linker
    $tbl_links              = $tbl_cdb_names['links'];
    $tbl_resources          = $tbl_cdb_names['resources'];
    
    // Tracking
    $tbl_trackAccess        = $tbl_cdb_names['track_e_access'];
    $tbl_trackDownloads     = $tbl_cdb_names['track_e_downloads'];
    $tbl_trackUploads       = $tbl_cdb_names['track_e_uploads'];
    
    // Tool List
    $sqlList[] = "
    CREATE TABLE IF NOT EXISTS `".$tbl_toolList."` (
        `id` int(11) NOT NULL auto_increment,
        `tool_id` int(10) unsigned default NULL,
        `rank` int(10) unsigned NOT NULL,
        `visibility` tinyint(4) default 0,
        `script_url` varchar(255) default NULL,
        `script_name` varchar(255) default NULL,
        `addedTool` ENUM('YES','NO') DEFAULT 'YES',
    PRIMARY KEY  (`id`)
    ) TYPE=MyISAM  ";
    
    // Course properties
    $sqlList[] = "
    CREATE TABLE IF NOT EXISTS `".$tbl_courseProperties."` (
        `id` int(11) NOT NULL auto_increment,
        `name` varchar(255) NOT NULL default '',
        `value` varchar(255) default NULL,
        `category` varchar(255) default NULL,
        PRIMARY KEY  (`id`)
) TYPE=MyISAM ";

    // Intro sections
    $sqlList[] = "
    CREATE TABLE IF NOT EXISTS `".$tbl_introSection."` (
        `id` int(11) NOT NULL auto_increment,
        `tool_id` int(11) NOT NULL default '0',
        `title` varchar(255) default NULL,
        `display_date` datetime default NULL,
        `content` text,
        `rank` int(11) default '1',
        `visibility` enum('SHOW','HIDE') NOT NULL default 'SHOW',
    PRIMARY KEY  (`id`)
    ) TYPE=MyISAM ";
    
    // User Info
    $sqlList[] = "
    CREATE TABLE IF NOT EXISTS `".$tbl_userInfoContent."` (
       `id` int(10) unsigned NOT NULL auto_increment,
       `user_id` mediumint(8) unsigned NOT NULL default '0',
       `def_id` int(10) unsigned NOT NULL default '0',
       `ed_ip` varchar(39) default NULL,
       `ed_date` datetime default NULL,
       `content` text,
       PRIMARY KEY  (`id`),
       KEY `user_id` (`user_id`)
    ) TYPE=MyISAM COMMENT='content of users information'";

    $sqlList[] = "
    CREATE TABLE IF NOT EXISTS `".$tbl_userInfoDef."` (
       `id` int(10) unsigned NOT NULL auto_increment,
       `title` varchar(80) NOT NULL default '',
       `comment` varchar(160) default NULL,
       `nbLine` int(10) unsigned NOT NULL default '5',
       `rank` tinyint(3) unsigned NOT NULL default '0',
       PRIMARY KEY  (`id`)
    ) TYPE=MyISAM COMMENT='categories definition for user information of a course'";
    
    // Groups
    $sqlList[] = "
    CREATE TABLE IF NOT EXISTS `".$tbl_groups."` (
        id int(11) NOT NULL auto_increment,
        name varchar(100) default NULL,
        description text,
        tutor int(11) default NULL,
        maxStudent int(11) NULL default '0',
        secretDirectory varchar(30) NOT NULL default '0',
    PRIMARY KEY  (id)
    ) TYPE=MyISAM ";

    $sqlList[] = "
    CREATE TABLE IF NOT EXISTS `".$tbl_groupUser."` (
        id int(11) NOT NULL auto_increment,
        user int(11) NOT NULL default '0',
        team int(11) NOT NULL default '0',
        status int(11) NOT NULL default '0',
        role varchar(50) NOT NULL default '',
    PRIMARY KEY  (id)
    ) TYPE=MyISAM ";
    
    $sqlList[] = "INSERT INTO `".$tbl_courseProperties."`
    (`name`, `value`, `category`)
    VALUES  ('self_registration', '1', 'GROUP'),
        ('nbGroupPerUser'   , '1', 'GROUP'),
        ('private'          , '1', 'GROUP'),
        ('CLFRM'            , '1', 'GROUP'),
        ('CLDOC'            , '1', 'GROUP'),
        ('CLWIKI'           , '1', 'GROUP'),
        ('CLCHT'            , '1', 'GROUP')";
    
    // Tracking
    $sqlList[] = "
    CREATE TABLE IF NOT EXISTS `".$tbl_trackAccess."` (
        `access_id` int(11) NOT NULL auto_increment,
        `access_user_id` int(10) default NULL,
        `access_date` datetime NOT NULL default '0000-00-00 00:00:00',
        `access_tid` int(10) default NULL,
        `access_tlabel` varchar(8) default NULL,
    PRIMARY KEY  (`access_id`)
    ) TYPE=MyISAM  COMMENT='Record informations about access to course or tools'";

    $sqlList[] = "
    CREATE TABLE IF NOT EXISTS `".$tbl_trackDownloads."` (
        `down_id` int(11) NOT NULL auto_increment,
        `down_user_id` int(10) default NULL,
        `down_date` datetime NOT NULL default '0000-00-00 00:00:00',
        `down_doc_path` varchar(255) NOT NULL default '0',
    PRIMARY KEY  (`down_id`)
    ) TYPE=MyISAM  COMMENT='Record informations about downloads'";
    
    $sqlList[] = "
    CREATE TABLE IF NOT EXISTS `".$tbl_trackUploads."` (
        `upload_id` int(11) NOT NULL auto_increment,
        `upload_user_id` int(10) default NULL,
        `upload_date` datetime NOT NULL default '0000-00-00 00:00:00',
        `upload_work_id` int(11) NOT NULL default '0',
    PRIMARY KEY  (`upload_id`)
    ) TYPE=MyISAM  COMMENT='Record some more informations about uploaded works'";

    // Linker
    $sqlList[] = "
    CREATE TABLE IF NOT EXISTS `".$tbl_links."` (
        `id` int(11) NOT NULL auto_increment,
        `src_id` int(11) NOT NULL default '0',
        `dest_id` int(11) NOT NULL default '0',
        `creation_time` timestamp(14) NOT NULL,
    PRIMARY KEY  (`id`)
    ) TYPE=MyISAM ";

    $sqlList[] = "
    CREATE TABLE IF NOT EXISTS `".$tbl_resources."` (
        `id` int(11) NOT NULL auto_increment,
        `crl` text NOT NULL,
        `title` text NOT NULL,
    PRIMARY KEY  (`id`)
    ) TYPE=MyISAM ";
    
    foreach($sqlList as $thisSql)
    {
        if ( claro_sql_query($thisSql) == false)
        {
            return claro_failure::set_failure(
                get_lang( 'Unable to create course tables' ) );
        }
    }

    return true;
}

// TODO: use module.lib functions instead (need to update $_course in global namespace)
/**
 * Install module databases at course creation
 */
function install_module_at_course_creation( $moduleLabel, $courseDbName, $language, $courseDirectory )
{
    $sqlPath = get_module_path( $moduleLabel ) . '/setup/course_install.sql';
    $phpPath = get_module_path( $moduleLabel ) . '/setup/course_install.php';

    if ( file_exists( $sqlPath ) )
    {
        if ( ! execute_sql_at_course_creation( $sqlPath, $courseDbName ) )
        {
            return false;
        }
    }

    if ( file_exists( $phpPath ) )
    {
        // include the language file with all language variables
        language::load_translation($language,'TRANSLATION');
        language::load_locale_settings($language);
        
        // define tables to use in php install scripts
        $courseDbName = get_conf('courseTablePrefix') . $courseDbName.get_conf('dbGlu');
        $tbl_cdb_names = claro_sql_get_course_tbl($courseDbName);
        
        claro_sql_select_db($courseDbName);
        
        require_once $phpPath;
    }
    
    return true;
}

/**
 * Execute SQL files at course creation
 */
function execute_sql_at_course_creation( $sqlPath, $courseDbName )
{
    if ( file_exists( $sqlPath ) )
    {
        $sql = file_get_contents( $sqlPath );
        
        $currentCourseDbNameGlu = get_conf('courseTablePrefix') . $courseDbName . get_conf('dbGlu');

        $sql = str_replace('__CL_COURSE__', $currentCourseDbNameGlu, $sql );

        if ( ! claro_sql_multi_query($sql) )
        {
            return claro_failure::set_failure( 'SQL_QUERY_FAILED' );
        }
        else
        {
            return true;
        }
    }
    else
    {
        return claro_failure::set_failure( 'SQL_FILE_NOT_FOUND' );
    }
}

?>