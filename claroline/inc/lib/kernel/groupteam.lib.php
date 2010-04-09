<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * Objects used to represent groups in the platform.
 *
 * @version     1.10 $Revision$
 * @copyright   2001-2010 Universite catholique de Louvain (UCL)
 * @author      Claroline Team <info@claroline.net>
 * @author      Frederic Minne <zefredz@claroline.net>
 * @license     http://www.gnu.org/copyleft/gpl.html
 *              GNU GENERAL PUBLIC LICENSE version 2 or later
 * @package     kernel.objects
 */

require_once dirname(__FILE__) . '/object.lib.php';
require_once dirname(__FILE__) . '/../core/claroline.lib.php';
require_once dirname(__FILE__) . '/../database/database.lib.php';

/**
 * Claro_GroupTeam represents a Group/Team
 *
 * @author zefredz <zefredz@claroline.net>
 * @since 1.10
 */
class
    Claro_GroupTeam
extends
    KernelObject
implements
    Countable
{
    //put your code here
    protected $courseObj, $groupId, $userList, $_rawData;

    public function __construct( Claro_Course $courseObj, int $groupId )
    {
        $this->groupId = $groupId;
        $this->courseObj = $courseObj;
        $this->userList = null;
    }

    /**
     * Load course properties and group properties from database
     * @param bool $forceReload
     */
    protected function loadFromDatabase( $forceReload = false )
    {
        if ( $forceReload )
        {
            $this->_rawData = array();
            $this->userList = null;
        }

        if ( empty($this->_rawData) )
        {
            $this->loadGroupCourseProperties();
            $this->loadGroupTeamProperties();
            $this->userList = null;
        }
    }

    protected function loadGroupCourseProperties()
    {
        // get course data from main
        $tbl = claro_sql_get_course_tbl( $this->courseObj->dbNameGlu );

        $sql_getGroupProperties = "
            SELECT
                name, value
            FROM
                `{$tbl['course_properties']}`
            WHERE
                category = 'GROUP';
        ";

        $db_groupProperties = Claroline::getDatabase()
            ->query( $sql_getGroupProperties )
            ->fetch();

        if ( ! $db_groupProperties )
        {
            throw new Exception("Cannot load group properties for {$this->courseObj->sysCode}");
        }

        foreach($db_groupProperties as $currentProperty)
        {
            $this->_rawData[$currentProperty['name']] = (int) $currentProperty['value'];
        }

        $this->_rawData ['registrationAllowed'] =  ($groupProperties['self_registration'] == 1);

        unset ( $groupProperties['self_registration'] );
        
        $this->_rawData ['private'] =  ($groupProperties['private'] == 1);

        $this->_rawData['tools'] = array();

        $groupToolList = get_group_tool_label_list();

        foreach ( $groupToolList as $thisGroupTool )
        {
            $groupTLabel = $thisGroupTool['label'];

            $this->_rawData ['tools'] [$groupTLabel] =
                array_key_exists( $groupTLabel, $this->_rawData )
                && ($this->_rawData[$groupTLabel] == 1);

            unset ( $this->_rawData[$groupTLabel] );
        };
    }

    protected function loadGroupTeamProperties()
    {
        $tbl = claro_sql_get_course_tbl( $this->courseObj->dbNameGlu );

        $sql = "
            SELECT
                g.id               AS id          ,
                g.name             AS name        ,
                g.description      AS description ,
                g.tutor            AS tutorId     ,
                g.secretDirectory  AS directory   ,
                g.maxStudent       AS maxMember
            FROM
                `{$tbl_c_names['group_team']}`  AS g
            WHERE
                g.id = {$this->groupId};
        ";

        $this->_rawData = array_merge( $this->_rawData,
            Claroline::getDatabase()
            ->query( $sql )
            ->fetch() );
    }

    public function getUserPropertiesInGroup( Claro_User $userObj )
    {
        if ( !$this->_rawData )
        {
            throw new Exception("Group data not loaded !");
        }

        $tbl = claro_sql_get_course_tbl( $this->courseObj->dbNameGlu );

        $sql = "SELECT
                    status,
                    role
                FROM 
                    `{$tbl_c_names['group_rel_team_user']}`
                WHERE
                    `user` = {$userObj->userId}
                AND
                    `team`   = {$this->groupId};";

        $result = Claroline::getDatabase()
            ->query( $sql )
            ->fetch();

        $userProperties = new stdClass();

        if ( ! $result )
        {
            $userProperties->isGroupMember = false;
            $userProperties->status = false;
            $userProperties->role = null;
            $userProperties->isGroupTutor = $this->_rawData['tutorId'] == $userId;
        }
        else
        {
            $userProperties->isGroupMember = true;
            $userProperties->status = $result['status'];
            $userProperties->role = $result['role'];
            $userProperties->isGroupTutor = $this->_rawData['tutorId'] == $userId;
        }

        return $userProperties;
    }

    public function getGroupUsers()
    {
        if ( ! $this->userList )

        {
            $mainTableName = get_module_main_tbl(array('user','cours_user'));
            $courseTableName = get_module_course_tbl(array('group_rel_team_user'), $this->courseObj->sysCode);

            $sql = "
                SELECT
                    `user`.`user_id` AS `id`,
                    `user`.`nom` AS `lastName`,
                    `user`.`prenom` AS `firstName`,
                    `user`.`email`
                FROM
                    `{$mainTableName['user']}` AS `user`
                INNER JOIN
                    `{$courseTableName['group_rel_team_user']}` AS `user_group`
                ON
                    `user`.`user_id` = `user_group`.`user`
                INNER JOIN
                    `{$mainTableName['cours_user']}` AS `course_user`
                ON
                    `user`.`user_id` = `course_user`.`user_id`
                WHERE
                    `user_group`.`team`= {$this->groupId}
                AND
                    `course_user`.`code_cours` = '{$this->courseObj->sysCode}'";

            $this->userList = Claroline::getDatabase()->query($sql);
        }

        return $this->userList;
    }

    public function getCourse()
    {
        return $this->courseObj;
    }

    public function getTutor()
    {
        $tutor = null;

        if ( $this->tutorId )
        {
            $tutor = new Claro_User($this->tutorId);
            $tutor->loadFromDatabase();
        }

        return $tutor;
    }

    public function getGroupSpace()
    {
        $groupSpace = new Claro_GroupSpace($this);
        return $groupSpace;
    }

    public function reload()
    {

    }

    /**
     * @see Countable
     * @return int
     */
    public function count()
    {
        return count($this->getGroupUsers());
    }
}

/**
 * Claro_CurrentGroupTeam represents the current Group/Team
 *
 * @author zefredz <zefredz@claroline.net>
 * @since 1.10
 */
class Claro_CurrentGroupTeam extends Claro_GroupTeam
{
    public function __construct( $userId = null )
    {
        $userId = empty( $groupId )
            ? claro_get_current_group_id()
            : $groupId
            ;

        parent::__construct( $groupId );
    }

    /**
     * Load user properties from session
     */
    public function loadFromSession()
    {
        if ( !empty($_SESSION['_group']) )
        {
            $this->_rawData = $_SESSION['_group'];
            pushClaroMessage( "User {$this->groupId} loaded from session", 'debug' );
        }
        else
        {
            throw new Exception("Cannot load user data from session for {$this->groupId}");
        }
    }

    /**
     * Save user properties to session
     */
    public function saveToSession()
    {
        $_SESSION['_group'] = $this->_rawData;
    }
}

/**
 * Claro_GroupSpace represents a Group Space
 *
 * @author zefredz <zefredz@claroline.net>
 * @since 1.10
 */
class Claro_GroupSpace
{
    protected $groupObj;

    public function  __construct( Claro_GroupTeam $groupObj )
    {
        $this->groupObj = $groupObj;
    }

    public function getToolList()
    {

    }

    public function getToolListAvailableForUser( $userId )
    {

    }

    public function getGroup()
    {

    }
}
