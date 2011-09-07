<?php // $Id$

/**
 * CLAROLINE
 *
 * Course search box Class.
 *
 * @version     $Revision$
 * @copyright   (c) 2001-2011, Universite catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @author      Claro Team <cvs@claroline.net>
 * @author      Antonin Bourguignon <antonin.bourguignon@claroline.net>
 * @since       1.11
 *
 * @todo        while we browse through platform's categories, the search box
 *              doesn't take the current category in account for its researches
 */

require_once dirname(__FILE__) . '/backlog.class.php';
require_once dirname(__FILE__) . '/admin.lib.inc.php'; // for delete course function
require_once dirname(__FILE__) . '/clarocategory.class.php';
require_once dirname(__FILE__) . '/../../messaging/lib/message/messagetosend.lib.php';
require_once dirname(__FILE__) . '/../../messaging/lib/recipient/userlistrecipient.lib.php';

class CourseSearchBox implements Display
{
    /**
     * Where the script is executed
     *
     * @var string
     */
    protected $formAction;
    
    /**
     * The specified keyword(s)
     *
     * @var string
     */
    protected $keyword;
    
    /**
     * Course list
     *
     * @var array
     */
    protected $searchResults;
    
    public function __construct($formAction)
    {
        $this->formAction   = $formAction;
        
        if (isset($_REQUEST['coursesearchbox_keyword']))
        {
            // Note: $keyword get secured later, in the SQL request
            $this->keyword = $_REQUEST['coursesearchbox_keyword'];
        }
        else
        {
            $this->keyword = '';
        }
    }
    
    private function fetchResults()
    {
        $this->searchResults = search_course($this->keyword);
    }
    
    public function getTemplate()
    {
        if (!empty($this->keyword))
        {
            $this->fetchResults();
        }
        
        $templateCourseSearchBox = new CoreTemplate('course_search_box.tpl.php');
        $templateCourseSearchBox->assign('formAction', $this->formAction);
        $templateCourseSearchBox->assign('courseList', $this->searchResults);
        $templateCourseSearchBox->assign('keyword', $this->keyword);
        
        return $templateCourseSearchBox;
    }
    
    public function render()
    {
        return $this->getTemplate()->render();
    }
}
