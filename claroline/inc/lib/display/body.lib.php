<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * Class used to configure and display the page body.
 *
 * @version     Claroline 1.12 $Revision$
 * @copyright   (c) 2001-2014, Universite catholique de Louvain (UCL)
 * @author      Claroline Team <info@claroline.net>
 * @author      Frederic Minne <zefredz@claroline.net>
 * @license     http://www.gnu.org/copyleft/gpl.html
 *              GNU GENERAL PUBLIC LICENSE version 2 or later
 * @package     kernel.display
 */

require_once __DIR__ . '/course.lib.php';

/**
 * Claroline display - page body
 */
class ClaroBody extends CoreTemplate
{
    protected $content = '';
    protected $claroBodyHidden = false;
    protected $courseTitleAndTools = true;
    protected $inPopup = false;
    
    /**
     * Initialize the display
     */
    public function __construct()
    {
        parent::__construct('body.tpl.php');
    }
    
    /**
     * Hide the claroBody div in the body
     */
    public function hideClaroBody()
    {
        $this->claroBodyHidden = true;
    }
    
    /**
     * Display the claroBody div in the body
     */
    public function showClaroBody()
    {
        $this->claroBodyHidden = false;
    }
    
    /**
     * Hide course title and tool list
     */
    public function hideCourseTitleAndTools()
    {
        $this->hideBlock('courseTitleAndTools');
    }
    
    /**
     * Show course title and tool list
     */
    public function showCourseTitleAndTools()
    {
        $this->showBlock('courseTitleAndTools');
    }
    
    /**
     * Show 'Close window' buttons
     */
    public function popupMode()
    {
        $this->inPopup = true;
    }
    
    /**
     * Set the content of the page
     * @param   string $content contents of the page
     */
    public function setContent( $content )
    {
        $this->content = $content;
    }
    
    /**
     * Prepend a string before the content of the page
     * @param   string $str add contents before the current contents of the page
     * @since Claroline 1.10.5
     */
    public function prependContent( $str )
    {
        $this->setContent( $str . $this->getContent() );
    }
    
    /**
     * Append a string to the content of the page
     * @param   string $str add contents after the current contents of the page
     */
    public function appendContent( $str )
    {
        $this->content .= $str;
    }
    
    /**
     * Clear the content of the paget
     */
    public function clearContent()
    {
        $this->setContent('');
    }
    
    /**
     * Return the content of the page
     * @return  string  pagecontent
     */
    public function getContent()
    {
        return $this->content;
    }
    
    /**
     * Render the page body
     * @return  string
     */
    public function render()
    {
        if ( claro_is_in_a_course() )
        {
            $this->assign( 'courseToolList',  new CurrentCourseToolListBlock() );
        }
        
        if ( ! $this->claroBodyHidden )
        {
            $this->assign('claroBodyStart', true);
            $this->assign('claroBodyEnd', true);
        }
        else
        {
            $this->assign('claroBodyStart', false);
            $this->assign('claroBodyEnd', false);
        }
        
        // automatic since $this->content already exists
        // $this->assign('content', $this->getContent() );
        
        
        if ( $this->inPopup )
        {
            $this->hideCourseTitleAndTools();
            $output = PopupWindowHelper::popupEmbed( parent::render() );
        }
        else
        {
            $output = parent::render();
        }
            
        return $output;
    }
    
    protected static $instance = false;
    
    /**
     * Get an instance of the ClaroBody class
     * @return ClaroBody
     */
    public static function getInstance()
    {
        if ( ! self::$instance )
        {
            self::$instance = new self;
        }
        
        return self::$instance;
    }
}
