<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

if ( count( get_included_files() ) == 1 )
{
    die( 'The file ' . basename(__FILE__) . ' cannot be accessed directly, use include instead' );
}

/**
* CLAROLINE
*
* User desktop : course list portlet
*
* @version      1.9 $Revision$
 * @copyright (c) 2001-2010, Universite catholique de Louvain (UCL)
* @license      http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
* @package      DESKTOP
* @author       Claroline team <info@claroline.net>
*
*/

uses('courselist.lib');
// we need CLHOME conf file for render_user_course_list function
include claro_get_conf_repository() . 'CLHOME.conf.php'; // conf file

class MyCourseList extends UserDesktopPortlet
{
    public function renderContent()
    {
        global $platformLanguage;
        
        $out = '';
        
        if (get_conf('display_user_desktop'))
        {
            /**
             * Commands line
             */
            $userCommands = array();
            
            if (claro_is_allowed_to_create_course()) // 'Create Course Site' command. Only available for teacher.
            {
                $userCommands[] = '<a href="'.get_path('clarolineRepositoryWeb').'course/create.php" class="claroCmd">'
                .    '<img src="' . get_icon_url('courseadd') . '" alt="" /> '
                .    get_lang('Create a course site')
                .    '</a>';
            }
            elseif ( $GLOBALS['currentUser']->isCourseCreator )
            {
                $userCommands[] = '<span class="claroCmdDisabled">'
                .    '<img src="' . get_icon_url('courseadd') . '" alt="" /> '
                .    get_lang('Create a course site')
                .    '</span>';
            }
            
            if (get_conf('allowToSelfEnroll',true))
            {
                $userCommands[] = '<a href="'.get_path('clarolineRepositoryWeb').'auth/courses.php?cmd=rqReg&amp;categoryId=0" class="claroCmd">'
                .    '<img src="' . get_icon_url('enroll') . '" alt="" /> '
                .    get_lang('Enrol on a new course')
                .    '</a>';
            
                $userCommands[] = '<a href="'.get_path('clarolineRepositoryWeb').'auth/courses.php?cmd=rqUnreg" class="claroCmd">'
                .    '<img src="' . get_icon_url('unenroll') . '" alt="" /> '
                .    get_lang('Remove course enrolment')
                .    '</a>';
            }
            
            $out .= '<a name="myCourseList"></a><p>'
                . claro_html_menu_horizontal( $userCommands )
                . '</p>' . "\n";
        }
        else
        {
            $out .= '<p><a class="claroCmd" href="'.get_path('url')
                . '/index.php#myCourseList">'
                . '<img src="' . get_icon_url('edit') . '" alt="" /> '
                . get_lang('Edit')
                . '</a></p>';
        }

        $out .= '<div id="portletMyCourseList">'
        . render_user_course_list()
        . '</div>' . "\n";

        $this->content = $out;

        return $this->content;
    }

    public function renderTitle()
    {
        return get_lang('My course list');
    }
}
