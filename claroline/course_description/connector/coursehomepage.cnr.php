<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * CLAROLINE
 *
 * Course home page: Announcements portlet
 *
 * @version     $Revision$
 * @copyright   (c) 2001-2010, Universite catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @package     CLCHP
 * @author      Antonin Bourguignon <antonin.bourguignon@claroline.net>
 * @author      Claroline team <info@claroline.net>
 * @since       1.10
 */

//TODO work in progress

class CLDSC_Portlet extends CourseHomePagePortlet
{
    public function __construct()
    {
        
    }

    public function renderContent()
    {
        $output = '';
        
        $output .= "\n"
                 . '<dl>' . "\n"
                 . '<dt>' . "\n"
                 . '<img class="iconDefinitionList" src="' . get_icon_url('course_description', 'CLDSC') . '" alt="Description icon" />'
                 . ' ' . get_lang('No description to display') . "\n"
                 . '</dt>' . "\n"
                 . '</dl>' . "\n"
                 ;
        
        return $output;
    }

    public function renderTitle()
    {
        return get_lang('Description');
    }
}