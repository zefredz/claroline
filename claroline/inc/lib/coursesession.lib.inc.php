<?php

/**
 * CLAROLINE
 *
 * SQL requests for claroCourseSession class
 *
 * @version 1.10
 *
 * @copyright 2001-2010 Universite catholique de Louvain (UCL)
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @author Claro Team <cvs@claroline.net>
 * @author Antonin Bourguignon <antonin.bourguignon@claroline.net>
 * @since 1.10
 */


/**
 * Get unique keys of a course.
 *
 * @param  string   course identifier.  If not set, current course 
 *                  identifier will be used.
 * @return array    list of unique keys (sys, db & path) of a course
 * @author Christophe Gesche <moosh@claroline.net>
 * @author Frederic Minne <zefredz@claroline.net>
 * @since 1.10
 */
function claro_get_session_course_data($courseId = null, $force = false )
{
    static $cachedDataList = array();
    
    $useCurrentCourseData = false;
    
    if ( is_null( $courseId ) && claro_is_in_a_course() )
    {
        $courseId =  claro_get_current_course_id();
        $useCurrentCourseData = true;
    }
    
    if ( ! array_key_exists( $courseId, $cachedDataList ) || true === $force )
    {
        if ( $useCurrentCourseData )
        {
            $courseDataList = $GLOBALS['_course'];
        }
        else
        {
            $tbl_mdb_names              = claro_sql_get_main_tbl();
            $tbl_courses                = $tbl_mdb_names['course'];
            $tbl_category               = $tbl_mdb_names['category'];
            $tbl_rel_course_category    = $tbl_mdb_names['rel_course_category'];
            
            // Get course datas
            $sql =  "SELECT
                    c.cours_id             AS id,
                    c.code                 AS sysCode,
                    c.sourceCourseId       AS sourceCourseId,
                    c.intitule             AS name,
                    c.administrativeNumber AS officialCode,
                    c.directory            AS path,
                    c.dbName               AS dbName,
                    c.titulaires           AS titular,
                    c.email                AS email,
                    c.language             AS language,
                    c.extLinkUrl           AS extLinkUrl,
                    c.extLinkName          AS extLinkName,
                    c.visibility           AS visibility,
                    c.access               AS access,
                    c.registration         AS registration,
                    c.registrationKey      AS registrationKey,
                    c.diskQuota            AS diskQuota,
                    UNIX_TIMESTAMP(c.creationDate)         AS publicationDate,
                    UNIX_TIMESTAMP(c.expirationDate)       AS expirationDate,
                    c.status               AS status
                    
                    FROM `" . $tbl_courses . "` AS c
                    
                    WHERE c.code = '" . claro_sql_escape($courseId) . "'";
            
            $courseDataList = claro_sql_query_get_single_row($sql);
            
            if ( ! $courseDataList ) return claro_failure::set_failure('session_course_not_found');
            
            // Get categories datas (from the source course)
            $sql = "SELECT
                    cat.id                  AS categoryId
                    
                    FROM `" . $tbl_category . "` AS cat
                    
                    LEFT JOIN `" . $tbl_rel_course_category . "` AS rcc
                           ON ( cat.id = rcc.categoryId )
                           
                    WHERE rcc.courseId = " . $courseDataList['sourceCourseId'];
            
            $categoriesDataList = claro_sql_query_fetch_all($sql);
            
            $courseDataList['access'             ] = $courseDataList['access'];
            $courseDataList['visibility'         ] = (bool) ('visible' == $courseDataList['visibility'] );
            $courseDataList['registrationAllowed'] = (bool) ('open' == $courseDataList['registration'] );
            $courseDataList['dbNameGlu'          ] = get_conf('courseTablePrefix') . $courseDataList['dbName'] . get_conf('dbGlu'); // use in all queries
            $courseDataList['categories'         ] = $categoriesDataList;
            
            
            /*
             * Doesn't work claro_sql_get_tbl need a tool id and is not for a tool
             * kernel table would be in mainDB.
             */ 
            #$tbl =  claro_sql_get_tbl('course_properties', array(CLARO_CONTEXT_COURSE=>$courseDataList['sysCode']));
            $tbl = claro_sql_get_course_tbl( $courseDataList['dbNameGlu'] );
            $sql = "SELECT name, value
                    FROM `" . $tbl['course_properties'] . "`
                    WHERE category = 'MAIN'";
            
            $extraDataList = claro_sql_query_fetch_all($sql);
            
            if (is_array($extraDataList) )
            {
                foreach($extraDataList as $thisData)
                {
                    $courseDataList[$thisData['name']] = $thisData['value'];
                }
            }
        }
        
        $cachedDataList[$courseId] = $courseDataList; // Cache for the next time...
    }
    
    #var_dump( $cachedDataList );
    
    return $cachedDataList[$courseId];
}


/**
 * To create a record in the course table of main database.  Also handles 
 * the categories links creation.
 * 
 * @param string    $courseSysCode
 * @param string    $courseScreenCode
 * @param int       $sourceCourseId
 * @param string    $courseRepository
 * @param string    $courseDbName
 * @param string    $titular
 * @param string    $email
 * @param arrat     $categories
 * @param string    $intitule
 * @param string    $languageCourse
 * @param string    $uidCreator
 * @param bool      $visibility
 * @param bool      $registrationAllowed
 * @param string    $registrationKey
 * @return bool     success;
 * @author Christophe Gesché <moosh@claroline.net>
 * @since 1.10
 */
function register_session_course( 
                          $courseSysCode, $courseScreenCode, $sourceCourseId,
                          $courseRepository, $courseDbName,
                          $titular, $email, $categories, $intitule, $languageCourse='',
                          $uidCreator,
                          $access, $registrationAllowed, $registrationKey='', $visibility=true,
                          $extLinkName='', $extLinkUrl='',$publicationDate, $expirationDate, $status)
{
    global $versionDb, $clarolineVersion;
    
    $tblList                    = claro_sql_get_main_tbl();
    $tbl_course                 = $tblList['course'];
    $tbl_category               = $tblList['category'];
    $tbl_rel_course_category    = $tblList['rel_course_category'];
    
    // Needed parameters
    if ($courseSysCode    == '') return claro_failure::set_failure('courseSysCode is missing');
    if ($courseScreenCode == '') return claro_failure::set_failure('courseScreenCode is missing');
    if ($courseDbName     == '') return claro_failure::set_failure('courseDbName is missing');
    if ($courseRepository == '') return claro_failure::set_failure('course Repository is missing');
    if ($uidCreator       == '') return claro_failure::set_failure('uidCreator is missing');
    
    // Optionnal parameters
    if ($languageCourse == '') $languageCourse = 'english';
    
    $currentVersionFilePath = get_conf('rootSys') . 'platform/currentVersion.inc.php';
    file_exists($currentVersionFilePath) && require $currentVersionFilePath;
    
    $defaultProfileId = claro_get_profile_id('user');
    
    // Insert course
    $sql = "INSERT INTO `" . $tbl_course . "` SET
            code                 = '" . claro_sql_escape($courseSysCode)    . "',
            sourceCourseId       = " . (int) $sourceCourseId . ",
            dbName               = '" . claro_sql_escape($courseDbName)     . "',
            directory            = '" . claro_sql_escape($courseRepository) . "',
            language             = '" . claro_sql_escape($languageCourse)   . "',
            intitule             = '" . claro_sql_escape($intitule)         . "',
            visibility           = '".  ($visibility?'VISIBLE':'INVISIBLE')    . "',
            access               = '".  claro_sql_escape($access)    . "',
            registration         = '".  ($registrationAllowed?'OPEN':'CLOSE')    . "',
            registrationKey      = '".  claro_sql_escape($registrationKey)    . "',
            diskQuota            = NULL,
            creationDate         = FROM_UNIXTIME(" . claro_sql_escape($publicationDate)   . "),
            expirationDate       = FROM_UNIXTIME(" . claro_sql_escape($expirationDate)   . "),
            status               = '" . claro_sql_escape($status)   . "',
            versionDb            = '" . claro_sql_escape($versionDb)        . "',
            versionClaro         = '" . claro_sql_escape($clarolineVersion) . "',
            lastEdit             = NOW(),
            lastVisit            = NULL,
            titulaires           = '" . claro_sql_escape($titular)          . "',
            email                = '" . claro_sql_escape($email)            . "',
            administrativeNumber = '" . claro_sql_escape($courseScreenCode) . "',
            extLinkName          = '" . claro_sql_escape($extLinkName)      . "',
            extLinkUrl           = '" . claro_sql_escape($extLinkUrl)       . "',
            defaultProfileId     = " . $defaultProfileId ;
    
    if ( claro_sql_query($sql) == false ) 
    {
        return false;
    }
    
    // Flag the source course
    $sql = "UPDATE `" . $tbl_course . "` SET
            isSourceCourse = 1
            WHERE cours_id = " . (int) $sourceCourseId;
    
    if ( claro_sql_query($sql) == false ) 
    {
        return false;
    }
    
    // Add user to course
    if ( user_add_to_course($uidCreator, $courseSysCode, true, true) === false )
    {
        return false;
    }
    
    return true;
}


/**
 * Delete a course of the plateform
 *
 * TODO detect failure with claro_failure
 *
 * @param  string   course identifier.
 * @return boolean  TRUE        if suceed
 *         boolean  FALSE       otherwise.
 * @since 1.10
 */
function delete_session_course($code, $sourceCourseId)
{
    global $eventNotifier;
    
    // Declare needed tables
    $tbl_mdb_names              = claro_sql_get_main_tbl();
    $tbl_course                 = $tbl_mdb_names['course'];
    $tbl_rel_course_user        = $tbl_mdb_names['rel_course_user'];
    $tbl_course_class           = $tbl_mdb_names['rel_course_class'];
    $tbl_rel_course_category    = $tbl_mdb_names['rel_course_category'];
    
    $this_course = claro_get_course_data($code);
    $currentCourseId = $this_course['sysCode'];
    
    // Delete user registrations into this course
    $sql = 'DELETE FROM `' . $tbl_rel_course_user . '`
            WHERE code_cours ="' . $currentCourseId . '"';
    
    claro_sql_query($sql);
    
    // Remove any recording in rel_cours_class
    $sql = "DELETE FROM `" . $tbl_course_class . "`
            WHERE courseId ='" . claro_sql_escape($currentCourseId) . "'";
    
    claro_sql_query($sql);
    
    // Delete the course inside the platform course registery
    $sql = 'DELETE FROM `' . $tbl_course . '`
            WHERE code = "' . claro_sql_escape($currentCourseId) . '"';
    
    claro_sql_query($sql);
    
    // Does the source course still have session courses ?
    $sql = "SELECT COUNT(cours_id) AS nbSessionCourses 
            FROM `" . $tbl_course . "` 
            WHERE sourceCourseId = " . (int) $sourceCourseId;
    
    $result = claro_sql_query_get_single_row($sql);
    var_dump($result['nbSessionCourses']);
    if ( $result['nbSessionCourses'] == 0 )
    {
        $sql = "UPDATE `" . $tbl_course . "` 
                SET isSourceCourse = 0 
                WHERE cours_id = " . (int) $sourceCourseId;
        
        claro_sql_query($sql);
    }
    
    // Delete course right
    RightCourseProfileToolRight::resetAllRightProfile($currentCourseId);
    
    // Delete course module tables
    // FIXME handle errors
    list( $success, $log ) = delete_all_modules_from_course( $currentCourseId );
    
    // Notify the course deletion event
    $args['cid'] = $this_course['sysCode'];
    $args['tid'] = null;
    $args['rid'] = null;
    $args['gid'] = null;
    $args['uid'] = $GLOBALS['_uid'];
    
    $eventNotifier->notifyEvent("course_deleted",$args);
    
    if ($currentCourseId == $code)
    {
        $currentCourseDbName    = $this_course['dbName'];
        $currentCourseDbNameGlu = $this_course['dbNameGlu'];
        $currentCoursePath      = $this_course['path'];
        
        if(get_conf('singleDbEnabled'))
        // IF THE PLATFORM IS IN MONO DATABASE MODE
        {
            // Search all tables related to the current course
            claro_sql_query("use " . get_conf('mainDbName'));
            $tbl_to_delete = claro_sql_get_course_tbl(claro_get_course_db_name_glued($currentCourseId));
            foreach($tbl_to_delete as $tbl_name)
            {
                $sql = 'DROP TABLE IF EXISTS `' . $tbl_name . '`';
                claro_sql_query($sql);
            }
            
            // Underscores must be replaced because they are used as wildcards in LIKE sql statement
            $cleanCourseDbNameGlu = str_replace("_","\_", $currentCourseDbNameGlu);
            $sql = 'SHOW TABLES LIKE "' . $cleanCourseDbNameGlu . '%"';
            
            $result = claro_sql_query($sql);
            
            // Delete all table of the current course
            $tblSurvivor = array();
            while( false !== ($courseTable = mysql_fetch_array($result,MYSQL_NUM ) ))
            {
                $tblSurvivor[]=$courseTable[0];
                #$tblSurvivor[$courseTable]='not deleted';
            }
            if (sizeof($tblSurvivor) > 0)
            {
                Claroline::getInstance()->log( 'DELETE_COURSE'
                , array_merge(array ('DELETED_COURSE_CODE'=>$code
                ,'UNDELETED_TABLE_COUNTER'=>sizeof($tblSurvivor)
                )
                , $tblSurvivor )
                );
            }
        }
        else
        // IF THE PLATFORM IS IN MULTI DATABASE MODE
        {
            $sql = "DROP DATABASE `" . $currentCourseDbName . "`";
            claro_sql_query($sql);
        }
        
        // Move the course diretory into the course garbage collector
        if(file_exists(get_conf('coursesRepositorySys') . $currentCoursePath . '/'))
        {
            claro_mkdir(get_conf('garbageRepositorySys'), CLARO_FILE_PERMISSIONS, true);
            
            rename(get_conf('coursesRepositorySys') . $currentCoursePath . '/',
            get_conf('garbageRepositorySys','garbage') . '/' . $currentCoursePath . '_' . date('YmdHis')
            );
        }
        #else pushClaroMessage('dir was already deleted');
        
        return true ;
    }
    else
    {
        return false ;
    }
}