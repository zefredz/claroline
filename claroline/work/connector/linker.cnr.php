<?php

class CLWRK_Resolver implements ModuleResourceResolver
{
    public function resolve ( ResourceLocator $locator )
    {
        $baseUrl = get_module_url('CLWRK');
        
        if ( $locator->hasResourceId() )
        {
            if( strpos( $locator->getResourceId(), 'ass_' ) !== FALSE )
            {
                return get_module_url('CLWRK') . '/work_list.php?assigId=' . (int) str_replace( 'ass_', '', $locator->getResourceId() );
            }
            elseif( strpos( $locator->getResourceId(), 'sub_' ) !== FALSE )
            {
                $submissionId = (int) str_replace( 'sub_', '', $locator->getResourceId() );
                require_once get_module_path( 'CLWRK' ) . '/lib/submission.class.php';
                
                $submission = new Submission();
                $submission->load( $submissionId );
                
                return get_module_url('CLWRK') . '/user_work.php?authId=' . $submission->getUserId() . '&assigId=' . $submission->getAssignmentId();
            }
            else
            {
                return get_module_entry_url( 'CLWRK' );
            }
        }
        else
        {
            return get_module_entry_url('CLWRK');
        }
    }

    public function getResourceName( ResourceLocator $locator)
    {
        if( $locator->hasResourceId() && $locator->inCourse() )
        {
            return $this->_getTitle( $locator->getCourseId(), $locator->getResourceId() );
        }
        
        return false;
    }
    /**
     * @param  $course_sys_code identifies a course in data base
     * @param  $id integer who identifies the exercice
     * @return the title of a work
     */
    function _getTitle( $courseId , $id )
    {
        
        $tbl_cdb_names = get_module_course_tbl( array( 'wrk_assignment', 'wrk_submission' ), $courseId );
        $tblSubmission = $tbl_cdb_names['wrk_submission'];
        $tblAssigment = $tbl_cdb_names['wrk_assignment'];
        
        if( strpos( $id, 'ass_') !== FALSE )
        {
            $assignmentId = str_replace( 'ass_', '', $id );
            $sql = 'SELECT `title`
                    FROM `'.$tblAssigment.'`
                    WHERE `id`='. (int) $assignmentId;
            $title = claro_sql_query_get_single_value($sql);

            return $title;   
        }
        elseif( strpos( $id, 'sub_') !== FALSE )
        {
            $submissionId = str_replace( 'sub_', '', $id );
            $sql = 'SELECT `title`
                    FROM `'.$tblSubmission.'`
                    WHERE `id`='. (int) $submissionId;
            $title = claro_sql_query_get_single_value($sql);

            return $title;   
        }
        else
        {
            return false;
        }
        
    }
}

class CLWRK_Navigator implements ModuleResourceNavigator
{
    public function getResourceId( $params = array() )
    {
        return false;
    }
    
    public function isNavigable( ResourceLocator $locator )
    {
        return true;
        if (  $locator->hasResourceId() )
        {
            $elems = explode( '/', ltrim( $locator->getResourceId(), '/') );
            
            return ( count( $elems ) == 1 );
        }
        else
        {
            return $locator->inModule() && $locator->getModuleLabel() == 'CLWRK';
        }
    }
    
    public function getParentResourceId( ResourceLocator $locator )
    {
        
    }
    
    public function getResourceList( ResourceLocator $locator )
    {
        $resourceList = new LinkerResourceIterator;
        
        $tbl = get_module_course_tbl( array('wrk_assignment','wrk_submission'), $locator->getCourseId() );
        
        if ( !$locator->hasResourceId() )
        {
            
            $sql = "SELECT
                        `id`,
                        `title`
                    FROM
                        `{$tbl['wrk_assignment']}`
                    WHERE `visibility` = 'VISIBLE'";
            
            $assigmentsList = Claroline::getDatabase()->query( $sql );
            
            foreach( $assigmentsList as $assigment )
            {
                $assigmentLoc = new ClarolineResourceLocator(
                    $locator->getCourseId(),
                    'CLWRK',
                    'ass_' . $assigment['id'],
                    null
                );
                
                $assignmentResource = new LinkerResource(
                  $assigment['title'],
                  $assigmentLoc,
                  true,
                  true,
                  true
                );
                
                $resourceList->addResource( $assignmentResource );
            }
        }
        else
        {
            if( strpos( $locator->getResourceId(), 'ass_') !== FALSE )
            {
                $assigmentId = (int) str_replace( 'ass_', '', $locator->getResourceId() );
                $sql = "SELECT
                            `id`,
                            `title`,
                            `authors`
                        FROM
                            `{$tbl['wrk_submission']}`
                        WHERE `assignment_id` = " . $assigmentId;
                        
                $submissionsList = Claroline::getDatabase()->query( $sql );
                
                foreach( $submissionsList as $submission )
                {
                    $submissionLoc = new ClarolineResourceLocator(
                        $locator->getCourseId(),
                        'CLWRK',
                        'sub_' . $submission['id']
                    );
                    
                    $submissionResource = new LinkerResource(
                        $submission['title'] . ' ('. $submission['authors'] .')',
                        $submissionLoc,
                        true,
                        true,
                        false
                    );
                
                    $resourceList->addResource( $submissionResource );
                }
            }
        }
        return $resourceList;
    }
}

?>