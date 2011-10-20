<?php // $Id$

/**
 * CLAROLINE
 *
 * @version     $Revision$
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @package     CLCOURSELIST
 * @author      Antonin Bourguignon <antonin.bourguignon@gmail.com>
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
        //@todo
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
        //@todo
    }
}


class CourseListIterator extends RowToObjectIteratorIterator
{
    public function current()
    {
        $courseData = $this->internalIterator->current();
        
        $courseObj = new Claro_Course($courseData['courseId']);
        $courseObj->loadFromArray($courseData);
        
        return $courseObj;
    }
}


class CourseTree
{
    /**
     * @var CourseTreeNode
     */
    protected $tree;
    
    /**
     * Constructor.
     * @param CourseListIterator
     */
    public function __construct($courseListIterator)
    {
        $root = new CourseTreeNode(null);
        
        $tempNodesList = array();
        
        foreach ($courseListIterator as $course)
        {
            $ctn = new CourseTreeNode($course->id);
            $ctn->setCourse($course);
            
            // Is it a source course ?
            if ($course->isSourceCourse)
            {
                // Can we find it in the temp list ?
                if (isset($tempNodesList[$course->id]))
                {
                    // Merge it from the temp list to the actual tree
                    $root->appendChild($tempNodesList[$course->id]);
                    unset($tempNodesList[$course->id]);
                    
                    #echo "On MERGE le cours SOURCE {$course->id} dans l'arbre<br/>";
                }
                else
                {
                    // Add it to the tree
                    $root->appendChild($ctn);
                    
                    #echo "On AJOUTE le cours SOURCE {$course->id} dans l'arbre<br/>";
                }
            }
            
            // Is it a session course ?
            elseif ($course->sourceCourseId)
            {
                // Is the parent in the tree ?
                if ($root->getChild($course->sourceCourseId))
                {
                    // Append the child to its parent in the tree
                    $root->getChild($course->sourceCourseId)->appendChild($ctn);
                    
                    #echo "On AJOUTE le cours SESSION {$course->id} dans l'arbre<br/>";
                }
                // Is the parent in the temp list ?
                elseif (isset($tempNodesList[$course->sourceCourseId]))
                {
                    // Append the child to its parent in the temp list
                    $tempNodesList[$course->sourceCourseId]->appendChild($ctn);
                    
                    #echo "On AJOUTE le cours SESSION {$course->id} dans la temp<br/>";
                }
                else
                {
                    // Add the parent and its child in the temp list
                    $ctnp = new CourseTreeNode($course->sourceCourseId);
                    $ctnp->appendChild($ctn);
                    
                    $tempNodesList[$ctnp->id] = $ctnp;
                    
                    #echo "On AJOUTE le cours SOURCE {$course->sourceCourseId} et son enfant {$course->id} dans la liste<br/>";
                }
            }
            
            // It seems to be a regular course
            else
            {
                // Add it to the tree
                $root->appendChild($ctn);
                
                #echo "On AJOUTE le cours NORMAL {$course->id} dans l'arbre<br/>";
            }
        }
        
        $this->tree = $root;
    }
    
    public function __toString()
    {
        return self::render();
    }
    
    public function render($node = null, $level = 0, $out = '')
    {
        if (!isset($node))
        {
            $currentNode = $this->tree;
        }
        else
        {
            $currentNode = $node;
            
            $out .= str_repeat('_', $level-1)."Je suis le node <b>{$currentNode->id}</b><br />";
        }
        
        if ($currentNode->hasChildren())
        {
            $level++;
            foreach ($currentNode->getChildren() as $childNode)
            {
                self::render($childNode, $level, &$out);
            }
        }
        
        return $out;
    }
}


class CourseTreeNode
{
    /**
     * @var int
     */
    public $id;
    
    /**
     * @var Claro_Course
     */
    protected $course;
    
    /**
     * @var array of CourseNode (array index: int course id)
     */
    protected $children;
    
    /**
     * Constructor.
     * @param int id
     */
    public function __construct($id)
    {
        $this->id = $id;
        $this->children = array();
    }
    
    /**
     * @param CourseTreeNode
     */
    public function appendChild($node)
    {
        $this->children[$node->id] = $node;
    }
    
    /**
     * @return boolean
     */
    public function hasChildren()
    {
        return !empty($this->children);
    }
    
    /**
     * @return array of CourseTreeNode
     */
    public function getChildren()
    {
        return $this->children;
    }
    
    /**
     * @param int id
     * @return CourseTreeNode (null if doesn't exist)
     */
    public function getChild($id)
    {
        if (!empty($this->children[$id]))
        {
            return $this->children[$id];
        }
        else
        {
            return null;
        }
    }
    
    /**
     * @param Claro_Course
     */
    public function setCourse($course)
    {
        $this->course = $course;
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
    public function __construct($courseList, $courseUserPrivilegesList)
    {
        $this->courseList = $courseList;
        $this->courseUserPrivilegesList = $courseUserPrivilegesList;
    }
    
    public function render()
    {
        $tpl = new CoreTemplate('user_course_list.tpl.php');
        
        $tpl->assign('courseList', $this->courseList);
        $tpl->assign('cupList', $this->courseUserPrivilegesList);
        
        return $tpl->render();
    }
}
