<?php // $Id: chatMsgList.class.php 415 2008-03-31 13:32:19Z fragile_be $
if ( count( get_included_files() ) == 1 ) die( '---' );
/**
 * CLAROLINE
 *
 * @version 1.8 $Revision: 415 $
 *
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @package CLDOC
 *
 * @author Claro Team <cvs@claroline.net>
 * @author Sebastien Piraux <pir@cerdecam.be>
 */

class CLDOC_CourseTrackingRenderer extends TrackingRenderer
{   
    private $tbl_course_tracking_event;
    public function __construct()
    {
        $tbl_cdb_names = claro_sql_get_course_tbl();
        $this->tbl_course_tracking_event = $tbl_cdb_names['tracking_event'];
    }
    protected function renderHeader()
    {
        return claro_get_tool_name('CLDOC');
    }
    
    protected function renderContent()
    {
        $html = '';
        
        $sql = "SELECT `data`,
                        COUNT(DISTINCT `user_id`) AS `nbr_distinct_user_downloads`,
                        COUNT(`data`) AS `nbr_total_downloads`
                    FROM `".$this->tbl_course_tracking_event."`
                    WHERE `type` = 'download'
                    GROUP BY `data`";

        $results = claro_sql_query_fetch_all($sql);

        $html .= '<table class="claroTable" cellpadding="2" cellspacing="1" border="0" align="center">'."\n"
            .'<tr class="headerX">'."\n"
            .'<th>&nbsp;'.get_lang('Document').'&nbsp;</th>'."\n"
            .'<th>&nbsp;'.get_lang('Users Downloads').'&nbsp;</th>'."\n"
            .'<th>&nbsp;'.get_lang('Total Downloads').'&nbsp;</th>'."\n"
            .'</tr>'."\n"
            .'<tbody>'."\n"
            ;
        if( !empty($results) && is_array($results) )
        {
            foreach( $results as $result )
            {
                $data = unserialize($result['data']);
                if( !empty( $data['url']) )
                {
                    $path = $data['url'];
                    $html .= '<tr>'."\n"
                    .'<td>'.htmlspecialchars($path).'</td>'."\n"
                    .'<td align="right"><a href="user_access_details.php?cmd=doc&amp;path='.urlencode($path).'">'.htmlspecialchars($result['nbr_distinct_user_downloads']).'</a></td>'."\n"
                    .'<td align="right">'.$result['nbr_total_downloads'].'</td>'."\n"
                    .'</tr>'."\n\n"
                    ;
                }
                else
                {
                    // no data to display ... so drop this record
                }
            }

        }
        else
        {
            $html .=  '<tr>'."\n"
                .'<td colspan="3"><div align="center">'.get_lang('No result').'</div></td>'."\n"
                .'</tr>'."\n"
                ;
        }
        $html .= '</tbody>'
            .'</table>'."\n"
            ;
        return $html;
    }
    
    protected function renderFooter()
    {
        return '';
    }
}

TrackingRendererRegistry::registerCourse('CLDOC_CourseTrackingRenderer');

class CLDOC_UserTrackingRenderer extends TrackingRenderer
{   
    protected function renderHeader()
    {
        return claro_get_tool_name('CLDOC');
    }
    
    protected function renderContent()
    {
        return 'content';
    }
    
    protected function renderFooter()
    {
        return '';
    }
}

TrackingRendererRegistry::registerUser('CLDOC_UserTrackingRenderer');
?>