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

class CLANN_Portlet extends CourseHomePagePortlet
{
    private $courseCode;
    
    public function __construct()
    {
        $this->courseCode = $courseCode = claro_get_current_course_id();
        
        if (file_exists(claro_get_conf_repository() . 'CLANN.conf.php'))
        {
            include claro_get_conf_repository() . 'CLANN.conf.php';
        }
    }
    
    public function renderContent()
    {
        // Select announcements for this course
        $tbl = claro_sql_get_course_tbl(claro_get_course_db_name_glued($this->courseCode));
        $tbl_announcement   = $tbl['announcement'];
        
        $currentCourseData  = claro_get_course_data($this->courseCode);
        $curdate            = claro_mktime();
        $output             = '';
        
        $sql = "SELECT " . Claroline::getDatabase()->quote($currentCourseData['sysCode']) . " AS `courseSysCode`, " . "\n"
                . Claroline::getDatabase()->quote($currentCourseData['officialCode']) . " AS `courseOfficialCode`, " . "\n"
                . "'CLANN'                                              AS `toolLabel`, " . "\n"
                . "CONCAT(`temps`, ' ', '00:00:00')                     AS `date`, " . "\n"
                . "CONCAT(`title`,' - ',`contenu`)                      AS `content`, " . "\n"
                . "`title`, " . "\n"
                . "`visibility`, " . "\n"
                . "`visibleFrom`, " . "\n"
                . "`visibleUntil` " . "\n"
                . "FROM `" . $tbl_announcement . "` " . "\n"
                . "WHERE CONCAT(`title`, `contenu`) != '' " . "\n"
                . "AND visibility = 'SHOW' " . "\n"
                . "            AND (UNIX_TIMESTAMP(`visibleFrom`) < '" . $curdate . "'
                                     OR `visibleFrom` IS NULL OR UNIX_TIMESTAMP(`visibleFrom`) = 0
                                   )
                               AND ('" . $curdate . "' < UNIX_TIMESTAMP(`visibleUntil`) OR `visibleUntil` IS NULL)"
                . "ORDER BY `date` DESC" . "\n"
                ;
        
        $announcementList = Claroline::getDatabase()->query($sql);
        
        // Manage announcement's datas
        if($announcementList)
        {
            $output .= '<dl id="portletMyAnnouncements">' . "\n";
            
            foreach($announcementList as $announcementItem)
            {
                // Generate announcement URL
                $announcementItem['url'] = get_path('url')
                    . '/claroline/announcements/announcements.php?cidReq='
                    . $currentCourseData['sysCode'];
                
                // Generate announcement title and content
                $announcementItem['title'] = trim(strip_tags($announcementItem['title']));
                if ( $announcementItem['title'] == '' )
                {
                    $announcementItem['title'] = substr($announcementItem['title'], 0, 60) . (strlen($announcementItem['title']) > 60 ? ' (...)' : '');
                }
                
                $announcementItem['content'] = trim(strip_tags($announcementItem['content']));
                if ( $announcementItem['content'] == '' )
                {
                    $announcementItem['content'] = substr($announcementItem['content'], 0, 60) . (strlen($announcementItem['content']) > 60 ? ' (...)' : '');
                }
                
                // Don't display hidden and expired elements
                $isVisible = (bool) ($announcementItem['visibility'] == 'SHOW') ? (1) : (0);
                $isOffDeadline = (bool)
                    (
                        (isset($announcementItem['visibleFrom'])
                            && strtotime($announcementItem['visibleFrom']) > time()
                        )
                        ||
                        (isset($announcementItem['visibleUntil'])
                            && time() >= strtotime($announcementItem['visibleUntil'])
                        )
                    ) ? (1) : (0);
                
                // Prepare the render
                if ( $isVisible && !$isOffDeadline )
                {
                    $output .= '<dt>' . "\n"
                             . '<img class="iconDefinitionList" src="' . get_icon_url('announcement', 'CLANN') . '" alt="" /> '
                             . '<a href="' . $announcementItem['url'] . '">'
                             . $announcementItem['title']
                             . '</a>' . "\n"
                             . '</dt>' . "\n"
                             . '<dd>' . "\n"
                             . $announcementItem['content'] . "\n"
                             . '</dd>' . "\n"
                             ;
                }
            }
            
            $output .= '</dl>';
        }
        else
        {
            $output .= "\n"
                     . '<dl>' . "\n"
                     . '<dt>' . "\n"
                     . '<img class="iconDefinitionList" src="' . get_icon_url('announcement', 'CLANN') . '" alt="Announcement icon" />'
                     . ' ' . get_lang('No announce to display') . "\n"
                     . '</dt>' . "\n"
                     . '</dl>' . "\n" . "\n"
                     ;
        }
        
        return $output;
    }
    
    public function renderTitle()
    {
        return get_lang('Latest announcements');
    }
}