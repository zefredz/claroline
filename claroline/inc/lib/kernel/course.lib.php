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

uses ( 'kernel/object.lib' );

class ClaroCourse extends KernelObject
{
    protected $_courseId;
    
    public function __construct( $courseId )
    {
        $this->_courseId = $courseId;
        $this->load();
    }
    
    protected function load()
    {
        $this->_rawData = $this->loadCourseKernelData();
        $this->_rawData['courseProperties'] = $this->loadCourseProperties();
        $this->_rawData['groupProperties'] = $this->loadGroupProperties();
    }
    
    protected function loadCourseKernelData()
    {
        // get course data from main
        $tbl =  claro_sql_get_tbl(array('cours','faculte',));
        // TODO : change table names to english !!!!
        $tblCourse = $tbl['cours'];
        $tblCat = $tbl['faculte'];
        
        $sqlCourseId = addslashes($this->_courseId);

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
        $courseDataList['access'] = (bool) ('public' == $courseDataList['access'] );
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
        
        $tblCourseProperties = $tbl['course_properties'];
        
        $sql_getCourseProperties = "SELECT name, value\n"
            . "FROM `{$tblCourseProperties}`\n"
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
        
        return $coursePropertyList;
    }
    
    protected function loadGroupProperties()
    {
        $tbl = claro_sql_get_course_tbl( $this->_rawData['dbNameGlu'] );
        
        $tblCourseProperties = $tbl['course_properties'];
        
        $sql_getGroupProperties = "SELECT name, value\n"
            . "FROM `{$tblCourseProperties}`\n"
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

        /*$groupProperties ['tools'] ['CLFRM'] =  (bool) ($groupProperties['CLFRM'] == 1);
        unset ( $groupProperties['CLFRM'] );
        $groupProperties ['tools'] ['CLDOC'] =  (bool) ($groupProperties['CLDOC'] == 1);
        unset ( $groupProperties['CLDOC'] );
        $groupProperties ['tools'] ['CLWIKI'] =  (bool) ($groupProperties['CLWIKI'] == 1);
        unset ( $groupProperties['CLWIKI'] );
        $groupProperties ['tools'] ['CLCHT'] =  (bool) ($groupProperties['CLCHT'] == 1);
        unset ( $groupProperties['CLCHT'] );*/
        
        return $groupProperties;
    }
    
    public function getGroupProperties()
    {
        return $this->_rawData['groupProperties'];
    }
}  
