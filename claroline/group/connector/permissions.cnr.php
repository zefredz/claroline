<?php

$permissions =  array(
    'override' => array(
        'group' => array(
            'read' => function( $userPrivileges, $coursePrivileges, $groupPrivileges ) {
                return $groupPrivileges->isGroupAllowed();
            },
            'register' => function ( $userPrivileges, $coursePrivileges, $groupPrivileges ) {
                if ( $userPrivileges->isAuthenticated()
                    && $coursePrivileges->isCourseMember()
                    && ! $coursePrivileges->isCourseAdmin()
                    && !$groupPrivileges->isGroupMember()
                    && !$groupPrivileges->isGroupTutor() )
                {
                    $groupProperties = $coursePrivileges->getCourse()->getGroupProperties();
                    
                    return $groupProperties['registrationAllowed'] === true;
                }
                else
                {
                    return false;
                }
            },
            'unregister' => function ( $userPrivileges, $coursePrivileges, $groupPrivileges ) {
                if ( $userPrivileges->isAuthenticated()
                    && $coursePrivileges->isCourseMember()
                    && $groupPrivileges->isGroupMember()
                    && !$groupPrivileges->isGroupTutor() )
                {
                    $groupProperties = $coursePrivileges->getCourse()->getGroupProperties();
                    
                    return $groupProperties['unRegistrationAllowed'] === true;
                }
                else
                {
                    return false;
                }
            }
        )
    )
);

return $permissions;
