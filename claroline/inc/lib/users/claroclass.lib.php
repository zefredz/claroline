<?php  

// $Id$

/**
 * Set of PHP classes to manipulate Claroline user classes
 *
 * @version 1.11 $Revision$
 * @copyright (c) 2013 Universite catholique de Louvain (UCL)
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @package kernel
 * @author Frederic Minne <zefredz@claroline.net>
 * @todo move to Claroline kernel
 */

/**
 * @since Claroline 1.11.6
 */
class Claro_Class_Exception extends Exception {}; 

/**
 * Represent a user class
 * @since Claroline 1.11.6
 */
class Claro_Class
{
    protected 
        $id = null,
        $name = null,
        $parentId = null,
        $level = null;
    
    protected 
        $name_changed = false,
        $parentId_changed = false;
    
    protected $database;
    
    /**
     * @param Database_Connection $database connection to the database
     */
    public function __construct( $database = null )
    {
        $this->database = $database ? $database : Claroline::getDatabase();    
    }
    
    /**
     * @param string $name class name
     * @return $this
     */
    public function setName( $name )
    {
        if ( $name != $this->name )
        {
            $this->name = $name;
            $this->name_changed = true;
        }
        
        return $this;
    }
    
    /**
     * @param int $parentId id of the parent class
     * @return $this
     */
    public function setParentId ( $parentId )
    {
        if ( $parentId != $this->parentId )
        {
            $this->parentId = $parentId;
            $this->parentId_changed = true;
        }
        
        return $this;
    }
    
    /**
     * Create ths class in database
     * @throws Claro_Class_Exception if class already exists (i.e. id given)
     * @throws Database_Connection_Exception in case of database error
     */
    public function create ( )
    {
        if ( $this->id )
        {
            throw new Claro_Class_Exception("Cannot create class : id not empty");
        }
        
        $tbl = claro_sql_get_main_tbl();
        
        if ( $this->parentId )
        {
            // load parent class
            $parentClass = new self ( $this->database );
            $parentClass->load( $this->parentId );
            $this->level = $parentClass->getLevel()+1;
        }
        else
        {
            $this->level = 1;
        }
        
        $fields = array();
        
        $fields[] = "name = " . $this->database->quote( $this->name );
        
        if ( $this->parentId )
        {
            $parentClass = new self ( $this->database );
            $parentClass->load( $this->parentId );
            $this->level = $parentClass->getLevel()+1;
            $fields[] = "class_parent_id = " . $this->database->escape( $this->parentId );
        }
        else
        {
            $this->level = 1;
        }

        $fields[] = "class_level = " . $this->database->escape( $this->level );
        
        $this->database->exec("
            INSERT INTO 
                `" . $tbl['class'] . "`
            SET
                " . implode( ",\n", $fields ) . "
        ");
        
        $this->id = $this->database->insertId();
        
        return $this;
    }
    
    /**
     * Update ths class in database
     * @throws Claro_Class_Exception if class does not exists (i.e. no id given)
     * @throws Database_Connection_Exception in case of database error
     */
    public function update ( )
    {
        if ( ! $this->id )
        {
            throw new Exception("Cannot update class information : no id given");
        }
        
        $fields = array();
        
        if ( $this->name_changed )
        {
            $fields[] = "name = " . $this->database->quote( $this->name );
        }
        
        if ( $this->parentId_changed )
        {
            if ( $this->parentId )
            {
                $parentClass = new self ( $this->database );
                $parentClass->load( $this->parentId );
                $this->level = $parentClass->getLevel()+1;
            }
            else
            {
                $this->level = 1;
            }
            
            $fields[] = "class_parent_id = " . $this->database->escape( $this->parentId );
            $fields[] = "class_level = " . $this->database->escape( $this->level );
        }
        
        if ( count( $fields ) == 0 )
        {
            return $this;
        }
        
        $tbl = claro_sql_get_main_tbl();
        
        $this->database->exec("
            UPDATE 
                `" . $tbl['class'] . "`
            SET
                " . implode( ",\n", $fields ) . "
            WHERE
                id = " . $this->database->escape($this->id) . "
        ");
        
        return $this;
    }
    
    /**
     * Load a class
     * @param int $classId id of the class
     * @return Claro_Class
     * @throws Claro_Class_Exception if class does not exists (i.e. no id given)
     * @throws Database_Connection_Exception in case of database error
     */
    public function load( $classId )
    {
        $this->id = $classId;
        
        $tbl = claro_sql_get_main_tbl();
        
        // what about the level ?
        
        $result = $this->database->query( "
            SELECT id,
                name,
                class_parent_id,
                class_level
            FROM `" . $tbl['class'] . "`
            WHERE `id`= ". (int) $this->id )->fetch();

        if ( $result )
        {
            $this->parentId = $result['class_parent_id'];
            $this->name = $result['name'];
            $this->level = $result['class_level'];
            
            return $this;
        }
        else
        {
            throw new Claro_Class_Exception("Cannot load class {$this->id}");
        }
    }
    
    /**
     * Load a class
     * @param int $classId id of the class
     * @return Claro_Class
     * @throws Claro_Class_Exception if class does not exists (i.e. no id given)
     * @throws Database_Connection_Exception in case of database error
     */
    public function loadByName( $className )
    {
        $this->name = $className;
        
        $tbl = claro_sql_get_main_tbl();
        
        // what about the level ?
        
        $result = $this->database->query( "
            SELECT id,
                name,
                class_parent_id,
                class_level
            FROM `" . $tbl['class'] . "`
            WHERE `name` = ". $this->database->quote($this->name) )->fetch();

        if ( $result )
        {
            $this->parentId = $result['class_parent_id'];
            $this->id = $result['id'];
            $this->level = $result['class_level'];
            
            return $this;
        }
        else
        {
            throw new Claro_Class_Exception("Cannot load class {$this->id}");
        }
    }
    
    /**
     * Get the id of the class
     * @return int class id
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Get the name of the class
     * @return string class name
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * Get the id of the parent class
     * @return int id of the parent class if any, null if no parent class
     */
    public function getParentId()
    {
        return $this->parentId;
    }
    
    /**
     * Get the level (i.e. the depth of the class in the class tree)
     * @return int depth of the class
     */
    public function getLevel()
    {
        return $this->level;
    }
    
    /**
     * Get the userlist of the class
     * @return Claro_ClassUserList
     */
    public function getClassUserList()
    {
        $classUserList = new Claro_ClassUserList( $this, $this->database );
        
        return $classUserList;
    }
    
    /**
     * Get the list of course in which the class is registered
     * @return Iterator
     */
    public function getClassCourseList()
    {
        $tbl  = claro_sql_get_main_tbl();
    
        return $this->database->query("
            SELECT
                cc.courseId AS code,
                c.administrativeNumber,
                c.intitule AS title,
                c.titulaires AS titulars
            FROM 
                `{$tbl['rel_course_class']}` AS `cc`
            JOIN
                `{$tbl['course']}` AS `c`
            ON
                `c`.`code` = `cc`.`courseId`
            WHERE
                cc.classId = ".$this->database->escape($this->id)."
        ");
    }
    
    /**
     * Add the class (but not its users) to the given course
     * @param string $courseId course code
     * @return bool
     */
    public function registerToCourse( $courseId )
    {
        if ( $this->isRegisteredToCourse ( $courseId ) )
        {
            return;
        }
        
        $tbl  = claro_sql_get_main_tbl();
    
        return $this->database->exec("
            INSERT INTO 
                `{$tbl['rel_course_class']}`
            SET
                classId = ".$this->database->escape($this->id).",
                courseId = ".$this->database->quote( $courseId )."
        ");
    }
    
    /**
     * Remove the class (but not its users) from the given course
     * @param string $courseId course code
     * @return bool
     */
    public function unregisterFromCourse( $courseId )
    {
        if ( !$this->isRegisteredToCourse ( $courseId ) )
        {
            return;
        }
        
        $tbl  = claro_sql_get_main_tbl();
    
        return $this->database->exec("
            DELETE FROM 
                `{$tbl['rel_course_class']}`
            WHERE
                classId = ".$this->database->escape($this->id)."
            AND
                courseId = ".$this->database->quote( $courseId )."
        ");
    }
    
    /**
     * Check if the class is registered to the given course
     * @param string $courseId course code
     * @return bool
     */
    public function isRegisteredToCourse( $courseId )
    {
        $tbl  = claro_sql_get_main_tbl();
    
        return $this->database->query("
            SELECT
                courseId as code
            FROM 
                `{$tbl['rel_course_class']}`
            WHERE
                classId = ".$this->database->escape($this->id)."
            AND
                courseId = ".$this->database->quote( $courseId )."
        ")->numRows() > 0;
    }
}

/**
 * User list of a class
 * @since Claroline 1.11.6
 */
class Claro_ClassUserList
{
    protected $class, $database, $tbl;
    
    protected $userIdList, $classUserList;
    
    /**
     * 
     * @param Claro_Class $class
     * @param Database_Connection $database
     */
    public function __construct( $class, $database = null )
    {
        $this->class = $class;
        $this->database = $database ? $database : Claroline::getDatabase();
        $this->userIdList = false;
        $this->tbl = get_module_main_tbl( array('rel_class_user') );
    }
    
    /**
     * Generate the query to add the given user ids
     * @param array $userIdList list of user id (array of int)
     * @return string
     */
    protected function generateAddUserListQuery ( $userIdList )
    {
        $alreadyInClass = $this->getUsersAlreadyInClass($userIdList);
        
        $lines = array();
        
        $classId = $this->class->getId();
        
        foreach ( $userIdList as $userId )
        {
            $userId = (int) $userId;
            
            if ( $userId > 0 && !isset($alreadyInClass[$userId])  )
            {
                $lines[] = "({$userId}, {$classId})";
            }
        }
        
        if ( ! count( $lines ) )
        {
            return false;
        }
        
        return "INSERT INTO 
                `{$this->tbl['rel_class_user']}`(user_id, class_id)
            VALUES
        " . implode ( ",\n", $lines ) . "
        ";
    }
    
    /**
     * Generate the query to remove the given user ids
     * @param array $userIdList list of user id (array of int)
     * @return string
     */
    protected function generateRemoveUserListQuery( $userIdList )
    {
        $ids = array();
        
        foreach ( $userIdList as $userId )
        {
            $userId = (int) $userId;
            
            if ( $userId > 0 )
            {
                $ids[] = $userId;
            }
        }
        
        if ( ! count( $ids ) )
        {
            return false;
        }
        
        $classId = $this->class->getId();

        return "DELETE FROM 
                `{$this->tbl['rel_class_user']}`
            WHERE 
                user_id 
            IN 
                ( " . implode( ',', array_unique( $ids ) ) . ")
            AND
                class_id = {$classId}
         ";
    }
    
    /**
     * Generate the query to remove the given user ids
     * @param array $userIdList list of user ids (array of int)
     * @return string
     */
    protected function getUsersAlreadyInClass ( $userIdList )
    {
        $ids = array();
        
        $classId = $this->class->getId();
        
        $results = $this->database->query("
            SELECT 
                user_id 
            FROM 
                `{$this->tbl['rel_class_user']}` 
            WHERE 
                class_id = {$classId}
            AND
                user_id IN (".implode( ',', $userIdList ).")");
                
        foreach ( $results as $user )
        {
            $ids[$user['user_id']] = true;
        }
        
        return $ids;
    }
    
    /**
     * Add a user id to the class
     * @param int $userId
     * @return number of user added
     */
    public function addUserId( $userId )
    {
        return $this->addUserIdList( array( $userId ) );
    }
    
    /**
     * Add a list of user ids to the class
     * @param array $userIdList
     * @return number of user added
     */
    public function addUserIdList ( $userIdList )
    {
        if ( ! count( $userIdList ) )
        {
            return false;
        }
        // register users to class
        $query = $this->generateAddUserListQuery($userIdList);
        
        if ( $query )
        {     
            return $this->database->exec( $query );
        }
        else
        {
            return false;
        }
        
        // register users to course
        // is this the role of the class ?
    }
    
    /**
     * Remove a user id from the class
     * @param int $userId
     * @return boolean
     */
    public function removeUserId ( $userId )
    {
        return $this->removeUserIdList( array( $userId ) );
    }
    
    /**
     * Remove a list of user ids from the class
     * @param array $userIdList
     * @return boolean
     */
    public function removeUserIdList ( $userIdList )
    {
        // remove users from courses
        $query = $this->generateRemoveUserListQuery($userIdList);
        
        if ( $query )
        {     
            return $this->database->exec( $query );
        }
        else
        {
            return false;
        }
        // remove users from class
    }
    
    /**
     * Get the iterator of user id list
     * @param boolean $forceRefresh
     * @return return user id list (array of int)
     */
    public function getClassUserIdListIterator( $forceRefresh = false )
    {
        if ( ! $this->userIdList || $forceRefresh )
        {
            $classId = $this->class->getId();

            $this->userIdList = $this->database->query("
                SELECT 
                    user_id 
                FROM 
                    `{$this->tbl['rel_class_user']}` 
                WHERE 
                    class_id = {$classId}" );
        }
        
        return $this->userIdList;
    }
    
    /**
     * Get the iterator of user id list
     * @param boolean $forceRefresh
     * @return return user id list (array of int)
     */
    public function getClassUserIdList( $forceRefresh = false )
    {
        $it = $this->getClassUserIdListIterator($forceRefresh);
        
        $list = array();
        
        foreach ( $it as $row )
        {
            $list[$row['user_id']] = $row['user_id'];
        }
        
        return $list;
    }
    
    /**
     * Get the iterator for the users registered in the class
     * @param bool $forceRefresh
     * @return Iterator Countable
     */
    public function getClassUserListIterator ( $forceRefresh = false )
    {
        if ( !is_array ( $this->classUserList ) || $forceRefresh )
        {
            $tbl_mdb_names = claro_sql_get_main_tbl ();
            $tbl_user = $tbl_mdb_names[ 'user' ];
            $tbl_rel_class_user = $tbl_mdb_names[ 'rel_class_user' ];

            $classId = $this->database->quote ( $this->class->getId () );

            $this->classUserList = $this->database->query ( "
                SELECT 
                    u.username AS username,
                    u.nom AS lastname,
                    u.prenom AS firstname,
                    u.email AS email,
                    u.officialCode AS fgs,
                    cu.user_id AS user_id
                FROM
                    `{$tbl_rel_class_user}` AS cu
                JOIN
                    `{$tbl_user}` AS u
                ON
                    cu.user_id = u.user_id
                WHERE
                    cu.class_id = {$classId}
                ORDER BY
                    u.nom, u.prenom, u.email
            " );
        }

        return $this->classUserList;
    }
    
    /**
     * Get the list of user in class. Can be used as a set.
     * @param type $forceRefresh
     * @return array of user_id => user(username, firstname, lastname, email, user_id)
     */
    public function getClassUserList( $forceRefresh = false )
    {
        $classUserList = array ( );

        foreach ( $this->getClassUserIdListIterator ( $forceRefresh ) as $user )
        {
            // use the user id as key to allow to use this list as a set
            $classUserList[ $user['user_id'] ] = $user;
        }

        return $classUserList;
    }
    
    /**
     * Get the list of user indexed by username
     * @param bool $forceRefresh
     * @return array
     */
    public function getClassUserListIndexedByUsername( $forceRefresh = false )
    {
        $classUsernameList = array();
        
        foreach ( $this->getClassUserList($forceRefresh) as $user )
        {
            $classUsernameList[$user['username']] = $user;
        }
        
        return $classUsernameList;
    }
    
    /**
     * Check if a user id is in the course
     * @param int $userId
     * @return boolean
     */
    public function isUserIdInClass( $userId )
    {
        $classId = $this->class->getId();
        $userId = $this->database->escape( $userId );

        return $this->database->query("
            SELECT 
                user_id 
            FROM 
                `{$this->tbl['rel_class_user']}` 
            WHERE 
                class_id = {$classId}
            AND
                user_id = {$userId}" )->numRows() > 0;
    }
}
