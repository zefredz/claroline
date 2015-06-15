<?php

$permissions =  array(
    'override' => array(
        'course' => array(
            'read' => function( $userPrivileges, $coursePrivileges, $groupPrivileges ) {
                if ( isset( $GLOBALS['inPathMode'] ) && $GLOBALS['inPathMode'] )
                {
                    $cllnpAccessManager = new Claro_ModuleAccessManager( new Claro_Module('CLLNP'));
                    
                    return $cllnpAccessManager->checkAccessRight($userPrivileges, 'READ', $coursePrivileges->getCourse(), $groupPrivileges->getGroup());
                }
                else
                {
                    return null;
                }
            }
        )
    )
);

return $permissions;
