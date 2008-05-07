<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * Description
 *
 * @version     1.9 $Revision$
 * @copyright   2001-2008 Universite catholique de Louvain (UCL)
 * @author      Claroline Team <info@claroline.net>
 * @author      Frederic Minne <zefredz@claroline.net>
 * @license     http://www.gnu.org/copyleft/gpl.html
 *              GNU GENERAL PUBLIC LICENSE version 2 or later
 * @package     PACKAGE_NAME
 */

if ( count( get_included_files() ) == 1 )
{
    die( 'The file ' . basename(__FILE__) . ' cannot be accessed directly, use include instead' );
}

uses ( 'kernel/object.lib' );

class ClaroUser extends KernelObject
{
    protected $_userId;
    
    public function __construct( $userId )
    {
        $this->_userId = $userId;
        // $this->load();
    }
    
    public function loadFromDatabase()
    {
        $tbl = claro_sql_get_main_tbl();
        
        $sqlUserId = (int) $this->_userId;
        
        $sql = "SELECT "
            . "`user`.`user_id` AS userId,\n"
            . "`user`.`prenom` AS firstName,\n"
            . "`user`.`nom` AS lastName,\n"
            . "`user`.`email`AS `mail`,\n"
            . "`user`.`officialEmail` AS `officialEmail`,\n"
            . "`user`.`language`,\n"
            . "`user`.`isCourseCreator`,\n"
            . "`user`.`isPlatformAdmin`,\n"
            . "`user`.`creatorId` AS creatorId,\n"
            
            . ( get_conf('is_trackingEnabled')
                ? "UNIX_TIMESTAMP(`login`.`login_date`) "
                : "DATE_SUB(CURDATE(), INTERVAL 1 DAY) " )
                
            . "AS lastLogin\n"
            . "FROM `{$tbl['user']}` AS `user`\n"
            
            . ( get_conf('is_trackingEnabled')
                ? "LEFT JOIN `{$tbl['track_e_login']}` AS `login`\n"
                . "ON `user`.`user_id`  = `login`.`login_user_id`\n"
                : '')
                
            . "WHERE `user`.`user_id` = {$sqlUserId}\n"
            
            . ( get_conf('is_trackingEnabled')
                ? "ORDER BY `login`.`login_date` DESC LIMIT 1"
                : '')
            ;

        $userData = claro_sql_query_get_single_row($sql);
        
        if ( ! $userData )
        {
            throw new Exception("Cannot load user data for {$this->_userId}");
        }
        else
        {
            $userData['isPlatformAdmin'] = (bool) $userData['isPlatformAdmin'];
            $userData['isCourseCreator'] = (bool) $userData['isCourseCreator'];
            
            $this->_rawData = $userData;
            pushClaroMessage( "User {$this->_userId} loaded from database", 'debug' );
        }
    }
}

class ClaroCurrentUser extends ClaroUser
{
    public function loadFromSession()
    {
        if ( !empty($_SESSION['_user']) )
        {
            $this->_rawData = $_SESSION['_user'];
            pushClaroMessage( "User {$this->_userId} loaded from session", 'debug' );
        }
        else
        {
            throw new Exception("Cannot load user data from session for {$this->_userId}");
        }
    }
    
    public function saveToSession()
    {
        $_SESSION['_user'] = $this->_rawData;
    }
    
    public function firstLogin()
    {
        return ($this->_userId != $this->creatorId);
    }
    
    public function updateCreatorId()
    {
        $tbl = claro_sql_get_main_tbl();
        
        $sql = "UPDATE `{$tbl['user']}`\n"
            . "SET   creatorId = user_id\n"
            . "WHERE user_id = " . (int)$this->_userId
            ;
            
        pushClaroMessage( "Creator id updated for user {$this->_userId}", 'debug' );
    
        return claro_sql_query($sql);
    }
}
