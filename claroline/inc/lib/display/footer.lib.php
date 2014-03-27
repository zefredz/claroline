<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * CLAROLINE
 *
 * Claroline page footer.
 *
 * @version     Claroline 1.12 $Revision$
 * @copyright   (c) 2001-2014, Universite catholique de Louvain (UCL)
 * @author      Claroline Team <info@claroline.net>
 * @author      Frederic Minne <zefredz@claroline.net>
 * @license     http://www.gnu.org/copyleft/gpl.html
 *              GNU GENERAL PUBLIC LICENSE version 2 or later
 * @package     kernel.display
 */
 
/**
 * Claroline page footer
 */
class ClaroFooter extends CoreTemplate
{
    private static $instance = false;
    
    private $hidden = false;
    
    /**
     * Initialize the footer
     */
    public function __construct()
    {
        parent::__construct('footer.tpl.php');
    }
    
    /**
     * Get an instance of the footer
     * @return ClaroFooter
     */
    public static function getInstance()
    {
        if ( ! self::$instance )
        {
            self::$instance = new ClaroFooter;
        }
        
        return self::$instance;
    }
    
    /**
     * Hide the footer
     */
    public function hide()
    {
        $this->hidden = true;
    }
    
    /**
     * Show the footer
     */
    public function show()
    {
        $this->hidden = false;
    }
    
    /**
     * Render the footer as HTML
     * @see Display
     * @return string
     */
    public function render()
    {
        if ( $this->hidden )
        {
            return '<!-- footer hidden -->' . "\n";
        }
        
        $currentCourse =  claro_get_current_course_data();
        
        if ( claro_is_in_a_course() )
        {
            $courseManagerOutput = '<div id="courseManager">'
                . get_lang('Manager(s) for %course_code'
                    , array('%course_code' => $currentCourse['officialCode']) )
                . ' : '
                ;
                
            $currentCourseTitular = empty ( $currentCourse['titular'] )
                ? get_lang ( 'Course manager' )
                : $currentCourse['titular']
                ;
            
            if ( empty($currentCourse['email']) )
            {
                $courseManagerOutput .= '<a href="' . get_module_url('CLUSR') . '/user.php">'. $currentCourseTitular.'</a>';
            }
            else
            {
                $courseManagerOutput .= '<a href="mailto:' . $currentCourse['email'] . '?body=' . $currentCourse['officialCode'] . '&amp;subject=[' . rawurlencode( get_conf('siteName')) . ']' . '">' . $currentCourseTitular . '</a>';
            }
            
            $courseManagerOutput .= '</div>';
            
            $this->assign( 'courseManager', $courseManagerOutput );
        }
        else
        {
            $this->assign( 'courseManager', '' );
        }
        
        $platformManagerOutput = '<div id="platformManager">'
            . get_lang('Administrator for %site_name'
                , array('%site_name'=>get_conf('siteName'))). ' : '
            . '<a href="mailto:' . get_conf('administrator_email')
            . '?subject=[' . rawurlencode( get_conf('siteName') ) . ']'.'">'
            . get_conf('administrator_name')
            . '</a>'
            ;
        
        if ( get_conf('administrator_phone') != '' )
        {
            $platformManagerOutput .= '<br />' . "\n"
                . get_lang('Phone : %phone_number'
                    , array('%phone_number' => get_conf('administrator_phone'))) ;
        }
        
        $platformManagerOutput .= '</div>';
        
        $this->assign( 'platformManager', $platformManagerOutput );
        
        $poweredByOutput = '<span class="poweredBy">'
            . get_lang('Powered by')
            . ' <a href="http://www.claroline.net" target="_blank">Claroline</a> '
            . '&copy; 2001 - 2014'
            . '</span>';
        
        $this->assign( 'poweredBy', $poweredByOutput );
        
        return parent::render();
    }
}