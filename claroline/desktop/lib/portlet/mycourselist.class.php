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
* @copyright    (c) 2001-2008 Universite catholique de Louvain (UCL)
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

        $out = '<a class="claroCmd" href="'.get_path('url')
            . '/index.php#myCourseList">'
            . '<img src="' . get_icon_url('edit') . '" alt="" /> '
            . get_lang('Edit')
            . '</a>'
            ;

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
