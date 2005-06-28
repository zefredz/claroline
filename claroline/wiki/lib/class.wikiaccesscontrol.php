<?php // $Id$

    // vim: expandtab sw=4 ts=4 sts=4:
    
    if( strtolower( basename( $_SERVER['PHP_SELF'] ) )
        == strtolower( basename( __FILE__ ) ) )
    {
        die("This file cannot be accessed directly! Include it in your script instead!");
    }
    
    /**
     * @version CLAROLINE 1.7
     *
     * @copyright 2001-2005 Universite catholique de Louvain (UCL)
     *
     * @license GENERAL PUBLIC LICENSE (GPL)
     * This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
     * as published by the FREE SOFTWARE FOUNDATION. The GPL is available
     * through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
     *
     * @author Frederic Minne <zefredz@gmail.com>
     *
     * @package Wiki
     */
    
    class WikiAccessControl
    {
        function checkAccess( $accessControlList, $accessLevel, $privilege )
        {
            $prefixList = WikiAccessControl::prefixList();
            $privilegeList = WikiAccessControl::privilegeList();
            
            if ( isset( $prefixList[$accessLevel] ) &&
                    isset( $privilegeList[$privilege] ) )
            {
                $accessKey = $prefixList[$accessLevel]
                    . $privilegeList[$privilege]
                    ;

                $accessControlFlag = isset( $accessControlList[$accessKey] )
                    ? $accessControlList[$accessKey]
                    : false
                    ;
            }
            else
            {
                $accessControlFlag = false;
            }
        
            if ( $accessControlFlag == true )
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        
        function prefixList()
        {
            static $prefixList = array(
                'course' => 'course_',
                'group' => 'group_',
                'other' => 'other_'
            );
            
            return $prefixList;
        }
        
        function privilegeList()
        {
            static $privilegeList = array(
                'read' => 'read',
                'edit' => 'edit',
                'create' => 'create'
            );
            
            return $privilegeList;
        }
        
        function defaultCourseWikiACL()
        {
            static $defaultCourseWikiACL = array(
                'course_read' => true,
                'course_edit' => true,
                'course_create' => true,
                'group_read' => false,
                'group_edit' => false,
                'group_create' => false,
                'other_read' => true,
                'other_edit' => false,
                'other_create' => false
            );
            
            return $defaultCourseWikiACL;
        }
        
        function emptyWikiACL()
        {
            static $emptyWikiACL = array(
                'course_read' => false,
                'course_edit' => false,
                'course_create' => false,
                'group_read' => false,
                'group_edit' => false,
                'group_create' => false,
                'other_read' => false,
                'other_edit' => false,
                'other_create' => false
            );

            return $emptyWikiACL;
        }
        
        function defaultGroupWikiACL()
        {
            static $defaultGroupWikiACL = array(
                'course_read' => true,
                'course_edit' => false,
                'course_create' => false,
                'group_read' => true,
                'group_edit' => true,
                'group_create' => true,
                'other_read' => false,
                'other_edit' => false,
                'other_create' => false
            );

            return $defaultGroupWikiACL;
        }
        
        function isAllowedToReadPage( $accessControlList, $accessLevel )
        {
            $privilege = 'read';
            return WikiAccessControl::checkAccess( $accessControlList, $accessLevel, $privilege );
        }
        
        function isAllowedToEditPage( $accessControlList, $accessLevel )
        {
            $privilege = 'edit';
            return WikiAccessControl::checkAccess( $accessControlList, $accessLevel, $privilege );
        }
        
        function isAllowedToCreatePage( $accessControlList, $accessLevel )
        {
            $privilege = 'create';
            return WikiAccessControl::checkAccess( $accessControlList, $accessLevel, $privilege );
        }
        
        function grantPrivilegeToAccessLevel( &$accessControlList, $accessLevel, $privilege )
        {
            $prefixList = WikiAccessControl::prefixList();
            $privilegeList = WikiAccessControl::privilegeList();

            if ( isset( $prefixList[$accessLevel] ) && isset( $privilegeList[$privilege] ) )
            {
                $key = $prefixList[$accessLevel] . $privilegeList[$privilege];
                $accessControlList[$key] = true;
                return true;
            }
            else
            {
                return false;
            }
        }
        
        function removePrivilegeFromAccessLevel( &$accessControlList, $accessLevel, $privilege )
        {
            $prefixList = WikiAccessControl::prefixList();
            $privilegeList = WikiAccessControl::privilegeList();

            if ( isset( $prefixList[$accessLevel] ) && isset( $privilegeList[$privilege] ) )
            {
                $key = $prefixList[$accessLevel] . $privilegeList[$privilege];
                $accessControlList[$key] = false;
                return true;
            }
            else
            {
                return false;
            }
        }
        
        function grantReadPrivilegeToAccessLevel( &$accessControlList, $accessLevel )
        {
            $privilege = 'read';

            return WikiAccessControl::grantPrivilegeToAccessLevel(
                $accessControlList
                , $accessLevel
                , $privilege
                );
        }
        
        function grantEditPrivilegeToAccessLevel( &$accessControlList, $accessLevel )
        {
            $privilege = 'edit';
            
            return WikiAccessControl::grantPrivilegeToAccessLevel(
                $accessControlList
                , $accessLevel
                , $privilege
                );
        }
        
        function grantCreatePrivilegeToAccessLevel( &$accessControlList, $accessLevel )
        {
            $privilege = 'create';

            return WikiAccessControl::grantPrivilegeToAccessLevel(
                $accessControlList
                , $accessLevel
                , $privilege
                );
        }
        
        function removeReadPrivilegeFromAccessLevel( &$accessControlList, $accessLevel )
        {
            $privilege = 'read';

            return WikiAccessControl::removePrivilegeFromAccessLevel(
                $accessControlList
                , $accessLevel
                , $privilege
                );
        }

        function removeEditPrivilegeFromAccessLevel( &$accessControlList, $accessLevel )
        {
            $privilege = 'edit';

            return WikiAccessControl::removePrivilegeFromAccessLevel(
                $accessControlList
                , $accessLevel
                , $privilege
                );
        }

        function removeCreatePrivilegeFromAccessLevel( &$accessControlList, $accessLevel )
        {
            $privilege = 'create';

            return WikiAccessControl::removePrivilegeFromAccessLevel(
                $accessControlList
                , $accessLevel
                , $privilege
                );
        }
        
        function exportACL( $accessControlList, $echoExport = true )
        {
            $export = "<pre>\n";
            $prefixList = WikiAccessControl::prefixList();
            $privilegeList = WikiAccessControl::privilegeList();
            
            foreach ( $prefixList as $accessLevel => $prefix )
            {
                $export .= $accessLevel . ':';
                
                foreach ( $privilegeList as $privilege )
                {
                    $aclKey = $prefix . $privilege;
                    
                    $boolValue = ( $accessControlList[$aclKey] == true ) ? 'true' : 'false';
                    $export .= $privilege . '('.$boolValue.')';
                }
                
                $export .= "<br />\n";
            }
            
            $export .= "</pre>\n";
            
            if ( $echoExport == true )
            {
                echo $export;
            }
            
            return $export;
        }
    }
?>