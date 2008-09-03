<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * Claroline Kernel objects
 *
 * @version     1.9 $Revision$
 * @copyright   2001-2008 Universite catholique de Louvain (UCL)
 * @author      Claroline Team <info@claroline.net>
 * @author      Frederic Minne <zefredz@claroline.net>
 * @license     http://www.gnu.org/copyleft/gpl.html
 *              GNU GENERAL PUBLIC LICENSE version 2 or later
 * @package     kernel.objects
 */

if ( count( get_included_files() ) == 1 )
{
    die( 'The file ' . basename(__FILE__) . ' cannot be accessed directly, use include instead' );
}

FromKernel::uses ( 'kernel/object.lib' );

class Claro_Course extends KernelObject
{
    protected $_courseId;
    
    public function __construct( $courseId )
    {
        $this->_courseId = $courseId;
        $this->load();
    }
    
    protected function loadFromDatabase()
    {
        $this->_rawData = $this->loadCourseKernelData();
        $this->loadCourseProperties();
        $this->loadGroupProperties();
    }
    
    protected function loadCourseKernelData()
    {
        // get course data from main
        $tbl =  claro_sql_get_tbl(array('cours','faculte',));
        
        $tblCourse = $tbl['cours'];
        $tblCat = $tbl['faculte'];
        
        $sqlCourseId = claro_sql_escape($this->_courseId);

        $sql_getCourseData =  "SELECT\n"
            . "\tc.code                 AS courseId,\n"
            . "\tc.code                 AS sysCode,\n"
            . "\tc.cours_id             AS courseDbId,\n"
            . "\tc.intitule             AS name,\n"
            . "\tc.administrativeNumber AS officialCode,\n"
            . "\tc.directory            AS path,\n"
            . "\tc.dbName               AS dbName,\n"
            . "\tc.titulaires           AS titular,\n"
            . "\tc.email                AS email  ,\n"
            . "\tc.language             AS language,\n"
            . "\tc.extLinkUrl           AS extLinkUrl,\n"
            . "\tc.extLinkName          AS extLinkName,\n"
            . "\tc.visibility           AS visibility,\n"
            . "\tc.access               AS access,\n"
            . "\tc.registration         AS registration,\n"
            . "\tc.registrationKey      AS registrationKey ,\n"
            . "\tcat.code               AS categoryCode,\n"
            . "\tcat.name               AS categoryName,\n"
            . "\tc.diskQuota            AS diskQuota\n\n"
            . "FROM      `{$tblCourse}`   AS c\n"
            . "LEFT JOIN `{$tblCat}` AS cat\n"
            . "\tON c.faculte =  cat.code\n"
            . "WHERE c.code = '{$sqlCourseId}'"
            ;

        $courseDataList = claro_sql_query_get_single_row( $sql_getCourseData );
        
        if ( ! $courseDataList )
        {
            throw new Exception("Cannot load course data for {$this->_courseId}");
        }
        
        // set bool values
        $courseDataList['access'] = $courseDataList['access'];
        $courseDataList['visibility'] = (bool) ('visible' == $courseDataList['visibility'] );
        $courseDataList['registrationAllowed'] = (bool) ('open' == $courseDataList['registration'] );
        
        // set dbNameGlu
        $courseDataList['dbNameGlu'] = get_conf('courseTablePrefix') 
            . $courseDataList['dbName'] . get_conf('dbGlu')
            ;
            
        return $courseDataList;
    }
    
    protected function loadCourseProperties()
    {
        // get extra course properties
        $tbl = claro_sql_get_course_tbl( $this->_rawData['dbNameGlu'] );
        
        $sql_getCourseProperties = "SELECT name, value\n"
            . "FROM `{$tbl['course_properties']}`\n"
            . "WHERE category = 'MAIN'"
            ;

        $courseProperties = claro_sql_query_fetch_all( $sql_getCourseProperties );
        
        $coursePropertyList = array();

        if ( is_array( $courseProperties ) )
        {
            foreach ( $courseProperties as $currentProperty )
            {
                $coursePropertyList[$currentProperty['name']] = $currentProperty['value'];
            }
        }
        
        $this->_rawData['courseProperties'] = $coursePropertyList;
    }
    
    protected function loadGroupProperties()
    {
        $tbl = claro_sql_get_course_tbl( $this->_rawData['dbNameGlu'] );
        
        $sql_getGroupProperties = "SELECT name, value\n"
            . "FROM `{$tbl['course_properties']}`\n"
            . "WHERE category = 'GROUP'"
            ;

        $db_groupProperties = claro_sql_query_fetch_all( $sql_getGroupProperties );
        
        if ( ! $db_groupProperties )
        {
            throw new Exception("Cannot load group properties for {$courseId}");
        }
        
        $groupProperties = array();
        
        foreach($db_groupProperties as $currentProperty)
        {
            $groupProperties[$currentProperty['name']] = (int) $currentProperty['value'];
        }
        
        $groupProperties ['registrationAllowed'] =  (bool) ($groupProperties['self_registration'] == 1);
        unset ( $groupProperties['self_registration'] );
        $groupProperties ['private'] =  (bool) ($groupProperties['private'] == 1);

        $groupProperties['tools'] = array();
        
        $groupToolList = get_group_tool_label_list();
        
        foreach ( $groupToolList as $thisGroupTool )
        {
            $groupTLabel = $thisGroupTool['label'];
            
            if ( array_key_exists( $groupTLabel, $groupProperties ) )
            {
                $groupProperties ['tools'] [$groupTLabel] = (bool) ($groupProperties[$groupTLabel] == 1);
                unset ( $groupProperties[$groupTLabel] );
            }
            else
            {
                $groupProperties ['tools'] [$groupTLabel] = false;
            }
        }
        
        $this->_rawData['groupProperties'] = $groupProperties;
    }
    
    public function getGroupProperties()
    {
        return $this->_rawData['groupProperties'];
    }
    
    public function getCourseProperties()
    {
        return $this->_rawData['courseProperties'];
    }
    
    public function __get( $nm )
    {
        if ( isset ( $this->_rawData[$nm] ) )
        {
            return $this->_rawData[$nm];
        }
        elseif ( isset ( $this->_rawData['courseProperties'][$nm] ) )
        {
            return $this->_rawData['courseProperties'][$nm];
        }
        else
        {
            return null;
        }
    }
}

class Claro_CurrentCourse extends Claro_Course
{
    public function __construct( $courseId = null )
    {
        $courseId = empty( $courseId )
            ? claro_get_current_course_id()
            : $courseId
            ;
            
        parent::__construct( $courseId );
    }
    
    public function loadFromSession()
    {
        if ( !empty($_SESSION['_course']) )
        {
            $this->_rawData = $_SESSION['_course'];
            pushClaroMessage( "Course {$this->_courseId} loaded from session", 'debug' );
        }
        else
        {
            throw new Exception("Cannot load course data from session for {$this->_courseId}");
        }
    }
    
    public function saveToSession()
    {
        $_SESSION['_course'] = $this->_rawData;
    }
}
