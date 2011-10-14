<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * CLAROLINE
 *
 * Claroline User Privileges in Courses.
 *
 * @version     Claroline 1.11 $Revision$
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 * @author      Claroline Team <info@claroline.net>
 * @author      Frederic Minne <zefredz@claroline.net>
 * @license     http://www.gnu.org/copyleft/gpl.html
 *              GNU GENERAL PUBLIC LICENSE version 2 or later
 * @package     kernel.course
 */

/**
 * This class represents one user's privileges in a course.
 * 
 * WARNING! this class behaves differently than the init.lib function 
 * claro_get_course_user_privilege() : 
 *  1) the platform admin status is not taken into account when evaluating 
 *      isCourseManager; 
 *  2) isPending is not taken into account when evaluating isCourseMember
 */
class CourseUserPrivileges
{
    protected 
        $userId, 
        $courseId;
    
    protected 
        $_profileId, 
        $is_courseAdmin, 
        $is_courseTutor, 
        $is_coursePending, 
        $is_courseMember;
    
    /**
     * Constructor
     * @param string $courseId course system code
     * @param int $userId user id
     */
    public function __construct( $courseId, $userId )
    {
        $this->userId = $userId;
        $this->courseId = $courseId;
    }
    
    public function load()
    {
        $tbl_mdb_names = claro_sql_get_main_tbl();
        
        $cuData = Claroline::getDatabase()->query("
            SELECT 
                cu.profile_id AS profileId,
                cu.isCourseManager,
                cu.isPending,
                cu.tutor
            FROM 
                `{$tbl_mdb_names['rel_course_user']}` `cu`
            WHERE 
                cu.`user_id`  = " . Claroline::getDatabase()->escape($this->userId) . "
            AND 
                cu.`code_cours` = " . Claroline::getDatabase()->quote($this->courseId))->fetch();

        if ( !empty($cuData) )
        {
            $this->_profileId        = $cuData['profileId'];
            $this->is_coursePending  = (bool) ($cuData['isPending' ]);
            $this->is_courseMember   = true;
            $this->is_courseTutor    = (bool) ($cuData['tutor'] == 1 );
            $this->is_courseAdmin    = (bool) ($cuData['isCourseManager'] == 1 );
        }
        else // this user has no status related to this course
        {
            $this->_profileId        = claro_get_profile_id('guest');
            $this->is_coursePending  = false;
            $this->is_courseMember   = false;
            $this->is_courseTutor    = false;
            $this->is_courseAdmin    = false;
        }
    }
    
    /**
     * Is the user a course manager ?
     * @return bool
     */
    public function isCourseManager()
    {
        return $this->is_courseAdmin;
    }
    
    /**
     * Is the user a member of the course ?
     * @return bool
     */
    public function isCourseMember()
    {
        return $this->is_courseMember;
    }
    
    /**
     * Is the user's enrolment in the course pending ?
     * @return bool
     */
    public function isEnrolmentPending()
    {
        return $this->is_coursePending;
    }
    
    /**
     * Is the user a tutor within the course
     * @return bool 
     */
    public function isCourseTutor()
    {
        return $this->is_courseTutor;
    }
    
    /**
     * Get the user's profile id in the course
     * @return int
     */
    public function getProfileId()
    {
        return $this->_profileId;
    }
    
    public function getUserId()
    {
        return $this->userId;
    }
    
    public function  getCourseId()
    {
        return $this->courseId;
    }
    
    public static function fromArray( $userId, $courseId, $data = null )
    {
        $priv = new self( $courseId, $userId );
        
        if ( !empty($data) )
        {
            $priv->_profileId        = $data['profileId'];
            $priv->is_coursePending  = (bool) ($data['isPending' ]);
            $priv->is_courseMember   = true;
            $priv->is_courseTutor    = (bool) ($data['tutor'] == 1 );
            $priv->is_courseAdmin    = (bool) ($data['isCourseManager'] == 1 );
        }
        else
        {
            $priv->_profileId        = claro_get_profile_id('guest');
            $priv->is_coursePending  = false;
            $priv->is_courseMember   = false;
            $priv->is_courseTutor    = false;
            $priv->is_courseAdmin    = false;
        }
        
        return $priv;
    }
}

class CourseUserPrivilegesList
{
    protected $userId, $coursePrivilegesList;
    
    public function __construct( $userId )
    {
        $this->userId = $userId;
        $this->coursePrivilegesList = array();
    }
    
    public function load()
    {
        $tbl_mdb_names = claro_sql_get_main_tbl();
        
        $coursePrivilegesList = Claroline::getDatabase()->query("
            SELECT 
                cu.`code_cours` AS courseId,
                cu.profile_id AS profileId,
                cu.isCourseManager,
                cu.isPending,
                cu.tutor
            FROM 
                `{$tbl_mdb_names['rel_course_user']}` `cu`
            WHERE 
                cu.`user_id`  = " . Claroline::getDatabase()->escape($this->userId) );
        
        foreach ( $coursePrivilegesList as $coursePrivileges )
        {
            $this->coursePrivilegesList[$coursePrivileges['courseId']] = $coursePrivileges;
        }
    }
    
    public function getCoursePrivileges( $courseCode )
    {
        if ( isset( $this->coursePrivilegesList[$courseCode] ) )
        {
            $priv = CourseUserPrivileges::fromArray($this->userId, $courseCode, $this->coursePrivilegesList[$courseCode] );
        }
        else
        {
            $priv = CourseUserPrivileges::fromArray($this->userId, $courseCode);
        }
        
        return $priv;
    }
    
    public function getIterator()
    {
        $it = new CourseUserPrivilegesIterator( $this->userId, $this->coursePrivilegesList );
        
        return $it;
    }
}

abstract class RowToObjectArrayIterator implements Iterator, Countable
{

  protected $collection = null;
  protected $currentIndex = 0;
  protected $maxIndex;
  protected $keys = null;
  
  public function __construct($array) 
  {

    $this->collection = $array;
    $this->maxIndex = count( $array );
    $this->keys = array_keys( $array );
  }

  public function key()
  {
    return $this->keys[$this->currentIndex];
  }

  public function next()
  {
    ++$this->currentIndex;
  }

  public function rewind()
  {
    $this->currentIndex = 0;
  }

  public function valid()
  {
    return ( isset($this->keys[$this->currentIndex]) );
  }
  
  public function count()
  {
      return count($this->collection);
  }
  
}

class CourseUserPrivilegesIterator extends RowToObjectArrayIterator
{
    public function __construct( $userId, $array )
    {
        $this->userId = $userId;
        
        parent::__construct($array);
    }
    
    public function current()
    {
        $data = $this->collection[$this->key()];
        
        if ( !isset( $data['courseId'] ) )
        {
            throw new Exception("Missing courseId in data");
        }
        
        return CourseUserPrivileges::fromArray( $this->userId, $data['courseId'], $data );
    }
}
