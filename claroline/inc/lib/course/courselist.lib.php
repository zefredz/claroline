<?php // $Id$

/**
 * CLAROLINE
 *
 * @version     $Revision$
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @package     CLCOURSELIST
 * @author      Claro Team <cvs@claroline.net>
 * @since       1.11
 */


require_once dirname(__FILE__) . '/../kernel/course.lib.php';
require_once dirname(__FILE__) . '/../utils/iterators.lib.php';
require_once dirname(__FILE__) . '/../courselist.lib.php';


interface CourseList
{
    /**
     * @return CourseListIterator
     */
    public function getIterator();
}


abstract class AbstractCourseList implements CourseList
{
    /**
     * @todo implement here the orderBy, orderDirection, etc.
     */
}


class UserCourseList extends AbstractCourseList
{
    /**
     * @var int user id
     */
    protected $userId;
    
    public function __construct($userId)
    {
        $this->userId = $userId;
    }
    
    public function getIterator()
    {
        $tbl_mdb_names              = claro_sql_get_main_tbl();
        $tbl_courses                = $tbl_mdb_names['course'];
        $tbl_rel_course_user        = $tbl_mdb_names['rel_course_user'];
        
        $curdate = claro_mktime();
        
        $sql = "SELECT
                c.code                  AS courseId,
                c.code                  AS sysCode,
                c.cours_id              AS id,
                c.isSourceCourse        AS isSourceCourse,
                c.sourceCourseId        AS sourceCourseId,
                c.intitule              AS name,
                c.administrativeNumber  AS officialCode,
                c.administrativeNumber  AS administrativeNumber,
                c.directory             AS path,
                c.dbName                AS dbName,
                c.titulaires            AS titular,
                c.email                 AS email,
                c.language              AS language,
                c.extLinkUrl            AS extLinkUrl,
                c.extLinkName           AS extLinkName,
                c.visibility            AS visibility,
                c.access                AS access,
                c.registration          AS registration,
                c.registrationKey       AS registrationKey,
                c.diskQuota             AS diskQuota,
                UNIX_TIMESTAMP(c.creationDate)          AS publicationDate,
                UNIX_TIMESTAMP(c.expirationDate)        AS expirationDate,
                c.status                AS status,
                c.userLimit             AS userLimit
                
                FROM `" . $tbl_courses . "` AS c
                
                JOIN `" . $tbl_rel_course_user . "` AS rcu
                ON rcu.code_cours = c.code
                AND rcu.user_id = " . (int) $this->userId . "
                
                ORDER BY UPPER(administrativeNumber), intitule";
        
        $result = Claroline::getDatabase()->query($sql);
        
        return new CourseListIterator($result);
    }
}


class CategoryCourseList extends AbstractCourseList
{
    /**
     * @var int category id
     */
    protected $categoryId;
    
    public function __construct($categoryId)
    {
        $this->categoryId = $categoryId;
    }
    
    public function getIterator()
    {
        
    }
}


class SearchedCourseList extends AbstractCourseList
{
    /**
     * @var String keyword
     */
    protected $keyword;
    
    public function __construct($keyword)
    {
        $this->keyword = $keyword;
    }
    
    public function getIterator()
    {
        
    }
}


class CourseListIterator extends RowToObjectIteratorIterator
{
    public function current ()
    {
        $courseData = $this->internalIterator->current();
        
        $courseObj = new Claro_Course($courseData['courseId']);
        $courseObj->loadFromArray($courseData);
        
        return $courseObj;
    }
}


Class CourseListView implements Display
{
    /**
     * @var CourseListIterator
     */
    protected $courseList;
    
    /**
     * Constructor
     * @param CourseListIterator
     */
    public function __construct($courseList)
    {
        $this->courseList = $courseList;
    }
    
    public function render()
    {
        $tpl = new CoreTemplate('new_course_list.tpl.php');
        
        $tpl->assign('courseList', $this->courseList);
        
        return $tpl->render();
    }
}
