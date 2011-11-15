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


/**
 * List of courses for a specific user.
 */
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


/**
 * List of courses for a specific category.
 */
class CategoryCourseList extends AbstractCourseList
{
    /**
     * @var int user id
     */
    protected $categoryId;
    
    public function __construct($categoryId)
    {
        $this->categoryId = $categoryId;
    }
    
    public function getIterator()
    {
        $tbl_mdb_names              = claro_sql_get_main_tbl();
        $tbl_courses                = $tbl_mdb_names['course'];
        $tbl_rel_course_category    = $tbl_mdb_names['rel_course_category'];
        
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
                
                JOIN `" . $tbl_rel_course_category . "` AS rcc
                ON rcc.courseId = c.cours_id
                AND rcc.categoryId = " . (int) $this->categoryId . "
                
                ORDER BY UPPER(administrativeNumber), intitule";
        
        $result = Claroline::getDatabase()->query($sql);
        
        return new CourseListIterator($result);
    }
}


/**
 * List of courses for a specific user and a specific category
 */
class UserCategoryCourseList extends AbstractCourseList
{
    /**
     * @var int user id
     */
    protected $userId;
    
    /**
     * @var int category id
     */
    protected $categoryId;
    
    public function __construct($userId, $categoryId)
    {
        $this->userId = $userId;
        $this->categoryId = $categoryId;
    }
    
    public function getIterator()
    {
        $tbl_mdb_names              = claro_sql_get_main_tbl();
        $tbl_courses                = $tbl_mdb_names['course'];
        $tbl_rel_course_user        = $tbl_mdb_names['rel_course_user'];
        $tbl_rel_course_category    = $tbl_mdb_names['rel_course_category'];
        
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
                
                JOIN `" . $tbl_rel_course_category . "` AS rcc
                ON rcc.courseId = c.cours_id
                AND rcc.categoryId = " . (int) $this->categoryId . "
                
                ORDER BY UPPER(administrativeNumber), intitule";
        
        $result = Claroline::getDatabase()->query($sql);
        
        return new CourseListIterator($result);
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


/**
 * Manage source and session courses tree structure.
 * Is implemented in a way that can only handle a two level depth tree.
 * That's why the class is CourseTree and not Tree.
 */
class CourseTree
{
    /**
     * @var CourseTreeNode
     * @todo Not sure about the name of this var
     */
    protected $root;
    
    /**
     * Constructor.
     * @param CourseListIterator
     */
    public function __construct($courseListIterator)
    {
        // Root of the course tree
        $root = new CourseTreeNode(null);
        
        $tempNodesList = array();
        
        foreach ($courseListIterator as $course)
        {
            // Create a new course tree node
            $node = new CourseTreeNode($course->id);
            $node->setCourse($course);
            
            // Is it a source course ?
            if ($course->isSourceCourse)
            {
                // Can we find it in the temp list ?
                if (isset($tempNodesList[$course->id]))
                {
                    $tempNodesList[$course->id]->setCourse($course);
                    
                    // Merge it from the temp list to the actual tree
                    $root->appendChild($tempNodesList[$course->id]);
                    unset($tempNodesList[$course->id]);
                }
                else
                {
                    // Add it to the tree
                    $root->appendChild($node);
                }
            }
            
            // Is it a session course ?
            elseif ($course->sourceCourseId)
            {
                // Is the parent in the tree ?
                if ($root->getChild($course->sourceCourseId))
                {
                    // Append the child to its parent in the tree
                    $root->getChild($course->sourceCourseId)->appendChild($node);
                }
                // Is the parent in the temp list ?
                elseif (isset($tempNodesList[$course->sourceCourseId]))
                {
                    // Append the child to its parent in the temp list
                    $tempNodesList[$course->sourceCourseId]->appendChild($node);
                }
                else
                {
                    // Add the parent and its child in the temp list
                    $parentNode = new CourseTreeNode($course->sourceCourseId);
                    $parentNode->appendChild($node);
                    
                    /*
                     * Note that the parent doesn't have any course data yet.
                     * Those data will have to be loaded later, when the parent
                     * will be found in the course list iterator
                     * ($courseListIterator), before getting merged in the tree.
                     */
                    $tempNodesList[$course->sourceCourseId] = $parentNode;
                }
            }
            
            // It seems to be a regular course
            else
            {
                // Add it to the tree
                $root->appendChild($node);
            }
        }
        
        /*
         * The tree should be fully builded now BUT...
         * What if one (or more) session course don't have their parent
         * in the course list iterator ($courseListIterator) ?  That could
         * happen (on rare occasions), so it has to be supported.
         *
         * The nodes wich belong to what we'll call "unfound parents"
         * will be reasigned to an "adoptive parent" node and appended
         * at the end of the tree.
         */
        if (!empty($tempNodesList))
        {
            // Set an adoptive node
            $adoptiveParent = new CourseTreeNode(null);
            
            foreach ($tempNodesList as $unfoundParentNode)
            {
                // If the unfound parent has children
                if ($unfoundParentNode->hasChildren())
                {
                    // Reasign each orphan node to the adoptive parent node
                    foreach ($unfoundParentNode->getChildren() as $orphanNode)
                    {
                        $adoptiveParent->appendChild($orphanNode);
                    }
                }
                
                unset($unfoundParentNode);
            }
            
            // Append the adoptive parent node at the end of the tree
            $root->appendChild($adoptiveParent);
        }
        
        $this->root = $root;
    }
    
    /**
     * @return CourseTreeNode
     * @todo Not sure about the name of this method
     */
    public function getRootNode()
    {
        return $this->root;
    }
    
    public function __toString()
    {
        return $this->recursiveToString();
    }
    
    protected function recursiveToString($node = null, $level = 0)
    {
        $out = '';
        
        if (!isset($node))
        {
            $currentNode = $this->root;
        }
        else
        {
            $currentNode = $node;
            
            $out .= str_repeat('_', $level-1)
                  . get_lang('I\'m node <b>%id</b>', array('%id' => $currentNode->getId()))
                  . ($currentNode->hasChildren() ?
                        get_lang(
                            ' and i have %nbChildren children',
                            array('%nbChildren' => $currentNode->countChildren())
                        ) :
                        ''
                    )
                  . '<br />';
        }
        
        if ($currentNode->hasChildren())
        {
            $level++;
            foreach ($currentNode->getChildren() as $childNode)
            {
                $out .= $this->recursiveToString($childNode, $level);
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
    protected $id;
    
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
     * @return $this for chaining
     */
    public function appendChild($node)
    {
        $this->children[$node->id] = $node;
        
        return $this;
    }
    
    /**
     * @return boolean
     */
    public function hasChildren()
    {
        return !empty($this->children);
    }
    
    /**
     * @return boolean
     */
    public function hasCourse()
    {
        return !empty($this->course);
    }
    
    /**
     * @return int number of children
     */
    public function countChildren()
    {
        return count($this->children);
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
     * @return Claro_Course (null if empty)
     */
    public function getCourse()
    {
        if (!empty($this->course))
        {
            return $this->course;
        }
        else
        {
            return null;
        }
    }
    
    /**
     * @return int node id
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * @param int node id
     * @return $this for chaining
     */
    public function setId($id)
    {
        $this->id = $id;
        
        return $this;
    }
    
    /**
     * @param Claro_Course
     * @return $this for chaining
     */
    public function setCourse($course)
    {
        $this->course = $course;
        
        return $this;
    }
}


class CourseTreeView implements Display
{
    /**
     * @var CourseTreeNode
     */
    protected $courseTreeRootNode;
    
    /**
     * @var CourseUserPrivilegesList
     */
    protected $courseUserPrivilegesList;
    
    /**
     * @var NotifiedCourseList
     */
    protected $notifiedCourseList;

    /**
     * @var Database_ResultSet
     */
    protected $categoryList;

    /**
     * @var int id of selected category to view
     */
    protected $selectedViewCategory;
    
    /**
     * Constructor
     * @param CourseTree
     * @param CourseUserPrivilegesList (default: null)
     * @param NotifiedCourseList
     * @param Database_ResultSet (default: null)
     * @param int (default: null)
     */
    public function __construct(
        $courseTreeNode,
        $courseUserPrivilegesList = null,
        $notifiedCourseList = null,
        $categoryList = null, 
        $selectedViewCategory = null
    )
    {
        $this->courseTreeRootNode = $courseTreeNode;
        $this->courseUserPrivilegesList = $courseUserPrivilegesList;
        $this->notifiedCourseList = $notifiedCourseList;
        $this->categoryList = $categoryList;
        $this->selectedViewCategory = $selectedViewCategory;
    }
    
    public function render()
    {
        $tpl = new CoreTemplate('course_tree.tpl.php');
        
        $tpl->assign('courseTreeRootNode', $this->courseTreeRootNode);
        $tpl->assign('courseUserPrivilegesList', $this->courseUserPrivilegesList);
        $tpl->assign('notifiedCourseList', $this->notifiedCourseList);
        $tpl->assign('categoryList', $this->categoryList);
        $tpl->assign('selectedViewCategory', $this->selectedViewCategory);
        
        return $tpl->render();
    }
}


abstract class AbstractCourseTreeNodeView implements Display
{
    /**
     * @var CourseTreeNode
     */
    protected $courseTreeNode;
    
    /**
     * @var CourseUserPrivilegesList
     */
    protected $courseUserPrivilegesList;
    
    /**
     * @var NotifiedCourseList
     */
    protected $notifiedCourseList;
    
    /**
     * Constructor
     * @param CourseTreeNode
     * @param CourseUserPrivilegesList (default: null)
     * @param NotifiedCourseList (default: null)
     */
    public function __construct(
        $courseTreeNode, 
        $courseUserPrivilegesList = null, 
        $notifiedCourseList = null)
    {
        $this->courseTreeNode = $courseTreeNode;
        $this->courseUserPrivilegesList = $courseUserPrivilegesList;
        $this->notifiedCourseList = $notifiedCourseList;
    }
}


class CourseTreeNodeView extends AbstractCourseTreeNodeView
{
    public function render()
    {
        $tpl = new CoreTemplate('course_tree_node.tpl.php');
        
        $tpl->assign('node', $this->courseTreeNode);
        $tpl->assign('courseUserPrivilegesList', $this->courseUserPrivilegesList);
        $tpl->assign('notifiedCourseList', $this->notifiedCourseList);
        
        return $tpl->render();
    }
}


class CourseTreeNodeAnonymousView extends AbstractCourseTreeNodeView
{
    public function render()
    {
        $tpl = new CoreTemplate('course_tree_node_anonymous.tpl.php');
        
        $tpl->assign('node', $this->courseTreeNode);
        $tpl->assign('courseUserPrivilegesList', $this->courseUserPrivilegesList);
        $tpl->assign('notifiedCourseList', $this->notifiedCourseList);
        
        return $tpl->render();
    }
}


class CourseTreeNodeDesactivatedView extends AbstractCourseTreeNodeView
{
    public function render()
    {
        $tpl = new CoreTemplate('course_tree_node_desactivated.tpl.php');
        
        $tpl->assign('node', $this->courseTreeNode);
        $tpl->assign('courseUserPrivilegesList', $this->courseUserPrivilegesList);
        $tpl->assign('notifiedCourseList', $this->notifiedCourseList);
        
        return $tpl->render();
    }
}


class CourseTreeNodeViewFactory
{
    /**
     * @param int user id
     * @return CourseTreeView 
     */
    static public function getUserCourseTreeView($userId)
    {
        // CourseListIterator
        $courseList = new UserCourseList($userId);
        $courseListIterator = $courseList->getIterator();
        
        // User rights
        $privilegeList = new CourseUserPrivilegesList($userId);
        $privilegeList->load();
        
        // Hot courses
        $notifiedCourseList = new NotifiedCourseList($userId);
        
        // User categories
        $userCategoryList = ClaroCategory::getUserCategoriesFlat($userId);
        
        // Selected category
        $selectedCategoryId = isset($_REQUEST['viewCategory']) ? 
            (int) $_REQUEST['viewCategory'] : 
            null;
        
        // Course tree
        $courseTree = new CourseTree($courseListIterator);
        
        $courseTreeView = new CourseTreeView(
            $courseTree->getRootNode(), 
            $privilegeList,
            $notifiedCourseList, 
            $userCategoryList,
            $selectedCategoryId);
        
        return $courseTreeView;
    }
    
    /**
     * @param int category id
     * @return CourseTreeView 
     */
    static public function getCategoryCourseTreeView($categoryId)
    {
        // CourseListIterator
        $courseList = new CategoryCourseList($categoryId);
        $courseListIterator = $courseList->getIterator();
        
        // Course tree
        $courseTree = new CourseTree($courseListIterator);
        
        $courseTreeView = new CourseTreeView(
            $courseTree->getRootNode());
        
        return $courseTreeView;
    }
    
    /**
     * @param int user id
     * @param int category id
     * @return CourseTreeView 
     */
    static public function getUserCategoryCourseTreeView($userId, $categoryId)
    {
        // CourseListIterator
        $courseList = new UserCategoryCourseList($userId, $categoryId);
        $courseListIterator = $courseList->getIterator();
        
        // User rights
        $privilegeList = new CourseUserPrivilegesList($userId);
        $privilegeList->load();
        
        // Hot courses
        $notifiedCourseList = new NotifiedCourseList($userId);
        
        // User categories
        $userCategoryList = ClaroCategory::getUserCategoriesFlat($userId);
        
        // Selected category
        $selectedCategoryId = isset($_REQUEST['viewCategory']) ? 
            (int) $_REQUEST['viewCategory'] : 
            null;
        
        // Course tree
        $courseTree = new CourseTree($courseListIterator);
        
        $courseTreeView = new CourseTreeView(
            $courseTree->getRootNode(), 
            $privilegeList,
            $notifiedCourseList, 
            $userCategoryList,
            $selectedCategoryId);
        
        return $courseTreeView;
    }
    
    static public function getSearchedCourseTreeView($keyword)
    {
        //@todo
    }
}
