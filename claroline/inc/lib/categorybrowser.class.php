<?php // $Id$

/**
 * CLAROLINE
 *
 * @version     $Revision$
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @author      Claro Team <cvs@claroline.net>
 * @since       1.10
 */


require_once dirname(__FILE__) . '/clarocategory.class.php';
require_once dirname(__FILE__) . '/course/courselist.lib.php';

class CategoryBrowser
{
    // Identifier of the selected category
    public $categoryId;
    
    // Identifier of the current user
    public $userId;
    
    // Current category
    public $curentCategory;
    
    // List of categories
    public $categoryList;
    
    
    /**
     * Constructor
     *
     * @param mixed $categoryId null or valid category identifier
     * @param mixed $userId null or valid user identifier
     */
    public function __construct($categoryId = null, $userId = null)
    {
        $this->categoryId   = $categoryId;
        $this->userId       = $userId;
        
        $this->currentCategory  = new claroCategory();
        $this->currentCategory->load($categoryId);
        $this->categoryList     = claroCategory::getCategories($categoryId, 1);
        $this->coursesList      = claroCourse::getRestrictedCourses($categoryId, $userId);
    }
    
    
    /**
     * Get current category properties.
     * 
     * @return object ClaroCategory
     * @since 1.8
     */
    public function get_current_category_settings()
    {
        if (!is_null($this->currentCategory))
            return $this->currentCategory;
        else
            return null;
    }
    
    
    /**
     * Get the sub-category list of the current category.
     * 
     * @return iterator list of sub category of the current category
     * @since 1.8
     */
    public function get_sub_category_list()
    {
        if (!empty($this->categoryList))
            return $this->categoryList;
        else
            return array();
    }
    
    
    /**
     * Get the course list of the current category.
     *
     * This list include main data about the user but also
     * registration status.
     *
     * @return array list of courses of the current category
     * @since 1.8
     */
    public function getCourseList()
    {
        if (!empty($this->coursesList))
            return $this->coursesList;
        else
            return array();
    }
    
    
    /**
     * Fetch list of courses of the current category without
     * the session courses.
     *
     * This list include main data about
     * the user but also registration status
     *
     * @return array list of courses of the current category
     * without session courses
     * @since 1.10
     * @deprecated session and source courses are equally displayed since 1.11
     */
    public function getCoursesWithoutSessionCourses()
    {
        if (!empty($this->coursesList))
        {
            $coursesList = array();
            foreach ($this->coursesList as $course)
            {
                if (is_null($course['sourceCourseId']) || 
                    (isset($course['isCourseManager']) && 
                    $course['isCourseManager'] == 1)
                )
                {
                    $coursesList[] = $course;
                }
            }
            
            return $coursesList;
        }
        else
        {
            return array();
        }
    }
    
    
    /**
     * Fetch list of courses of the current category without
     * the source courses (i.e. courses having session courses).
     *
     * This list include main data about the user but also
     * registration status.
     *
     * @return array list of courses of the current category
     * without source courses
     * @since 1.10
     * @deprecated session and source courses are equally displayed since 1.11
     */
    public function getCoursesWithoutSourceCourses()
    {
        if (!empty($this->coursesList))
        {
            // Find the source courses identifiers
            $sourceCoursesIds = array();
            foreach ($this->coursesList as $course)
            {
                if (!is_null($course['sourceCourseId'])
                    && !in_array($course['sourceCourseId'], $sourceCoursesIds))
                {
                    $sourceCoursesIds[] = $course['sourceCourseId'];
                }
            }
            
            $coursesList = array();
            foreach ($this->coursesList as $course)
            {
                if (!in_array($course['id'], $sourceCoursesIds))
                    $coursesList[] = $course;
            }
            
            return $coursesList;
        }
        else
        {
            return array();
        }
    }
    
    
    /**
     * @return template object
     * @since 1.10
     * @todo write a CategoryBrowserView class (implementing Display)
     */
    public function getTemplate()
    {
        $currentCategory    = $this->get_current_category_settings();
        $categoryList       = $this->get_sub_category_list();
        $userId             = claro_get_current_user_id();
        
        $courseTreeView = 
            CourseTreeNodeViewFactory::getCategoryCourseTreeView($this->categoryId);
        
        $template = new CoreTemplate('categorybrowser.tpl.php');
        $template->assign('currentCategory', $currentCategory);
        $template->assign('categoryBrowser', $this);
        $template->assign('categoryList', $categoryList);
        $template->assign('courseTreeView', $courseTreeView);
        
        return $template;
    }
}