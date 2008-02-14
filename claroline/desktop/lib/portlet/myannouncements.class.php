<?php // $Id$
/**
 * CLAROLINE
 *
 * This script prupose to user to edit his own profile
 *
 * @version 1.8 $Revision$
 *
 * @copyright 2001-2007 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @see http://www.claroline.net/wiki/Auth/
 *
 * @author Claro Team <cvs@claroline.net>
 *
 * @package Auth
 *
 */

require_once dirname(__FILE__) . '/../../../../claroline/announcements/lib/announcement.lib.php';

class myannouncements extends portlet
{
    function __construct()
    {
    }
    
    function renderContent()
    {
        $output = '';
        
        $courseDigestList = array('courseSysCode'      => array(),
                                  'courseOfficialCode' => array(),
                                  'toolLabel'          => array(),
                                  'date'               => array(),
                                  'content'            => array());
                              
        $personnalCourseList = get_user_course_list(claro_get_current_user_id());

        foreach($personnalCourseList as $thisCourse)
        {
            $tableAnn = get_conf('courseTablePrefix') . $thisCourse['db'] . get_conf('dbGlu') . 'announcement';

            $sql = "SELECT '" . addslashes($thisCourse['sysCode']     ) ."' AS `courseSysCode`,
                       '" . addslashes($thisCourse['officialCode']) ."' AS `courseOfficialCode`,
                       'CLANN'                                          AS `toolLabel`,
                       CONCAT(`temps`, ' ', '00:00:00')                 AS `date`,
                       CONCAT(`title`,' - ',`contenu`)                  AS `content`

                FROM `" . $tableAnn . "`
                WHERE CONCAT(`title`, `contenu`) != ''
                  AND DATE_FORMAT( `temps`, '%Y %m %d') >= '".date('Y m d', $_user['lastLogin'])."'
                  AND visibility = 'SHOW'
                ORDER BY `date` DESC
                LIMIT 1";

            $resultList = claro_sql_query_fetch_all_cols($sql);

            foreach($resultList as $colName => $colValue)
            {
                if (count($colValue) == 0) break;
                $courseDigestList[$colName] = array_merge($courseDigestList[$colName], $colValue);
            }
        }    
        
        for( $i=0, $itemCount = count($courseDigestList['toolLabel']); $i < $itemCount; $i++)
        {
            $itemIcon = 'announcement.gif';
            $url = get_module_url('CLANN') . '/announcements.php?cidReq='
            . $courseDigestList['courseSysCode'][$i];

            $courseDigestList['content'][$i] = preg_replace('/<br( \/)?>/', ' ', $courseDigestList['content'][$i]);
            $courseDigestList['content'][$i] = strip_tags($courseDigestList['content'][$i]);
            //$courseDigestList['content'][$i] = substr($courseDigestList['content'][$i],0, get_conf('max_char_from_content') );
            $courseDigestList['content'][$i] = substr($courseDigestList['content'][$i],0,150);

            $output .= '<p>' . "\n"
            .    '<small>'
            .    '<a href="' . $url . '">'
            .    '<img src="' . get_path('imgRepositoryWeb') . $itemIcon . '" alt="" />'
            .    '</a>' . "\n"
            .    claro_html_localised_date( get_locale('dateFormatLong'),
            strtotime($courseDigestList['date'][$i]) )
            .    '<br />' . "\n"
            .    '<a href="' . $url . '">'
            .    $courseDigestList['courseOfficialCode'][$i]
            .    '</a> : ' . "\n"
            .    '<small>'  . "\n"
            .    $courseDigestList['content'][$i]  . "\n"
            .    '</small>' . "\n"
            .    '</small>' . "\n"
            .    '</p>' . "\n"
            ;
            
        }        
        
        $this->content = $output;

        return $this->content;
    }
    
    function renderTitle()
    {
        return $this->title = get_lang('Latest announcements');
    }
}

?>