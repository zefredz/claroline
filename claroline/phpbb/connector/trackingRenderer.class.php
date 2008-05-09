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
 * @package CLFRM
 *
 * @author Claro Team <cvs@claroline.net>
 * @author Sebastien Piraux <pir@cerdecam.be>
 */

class CLFRM_CourseTrackingRenderer extends TrackingRenderer
{   
    private $tbl_bb_topics;
    private $tbl_bb_posts;
    
    public function __construct()
    {
        $tbl_cdb_names = claro_sql_get_course_tbl();
        $this->tbl_bb_topics = $tbl_cdb_names['bb_topics'];
        $this->tbl_bb_posts  = $tbl_cdb_names['bb_posts'];
    }
    protected function renderHeader()
    {
        return claro_get_tool_name('CLFRM');
    }
    
    protected function renderContent()
    {
        $html = '';
        
        // total number of posts
        $sql = "SELECT count(`post_id`)
                        FROM `".$this->tbl_bb_posts."`";
        $totalPosts = claro_sql_query_get_single_value($sql);

        // total number of threads
        $sql = "SELECT count(`topic_title`)
                        FROM `".$this->tbl_bb_topics."`";
        $totalTopics = claro_sql_query_get_single_value($sql);

        // display total of posts and threads
        $html .= '<ul>'."\n"
        .   '<li>'.get_lang('Messages posted').' : '.$totalPosts.'</li>'."\n"
        .   '<li>'.get_lang('Topics started').' : '.$totalTopics.'</li>'."\n"
        .   '</ul>' . "\n";
        
        // top 10 topics more active (more responses)
        $sql = "SELECT `topic_id`, `topic_title`, `topic_replies`
                    FROM `".$this->tbl_bb_topics."`
                    ORDER BY `topic_replies` DESC
                    LIMIT 10
                    ";
        $results = claro_sql_query_fetch_all($sql);
        $html .= '<table class="claroTable" cellpadding="2" cellspacing="1" border="0" align="center">'."\n"
        .   '<tr class="headerX">'."\n"
        .   '<th>'.get_lang('More active topics').'</th>'."\n"
        .   '<th>'.get_lang('Replies').'</th>'."\n"
        .   '</tr>'."\n";
        
        if( !empty($results) && is_array($results) )
        {
            $html .= '<tbody>'."\n";
            foreach( $results as $result )
            {
                $html .= '<tr>'."\n"
                    .'<td><a href="../phpbb/viewtopic.php?topic='.$result['topic_id'].'">'.$result['topic_title'].'</a></td>'."\n"
                    .'<td>'.$result['topic_replies'].'</td>'."\n"
                    .'</tr>'."\n";
            }
            $html .= '</tbody>'."\n";

        }
        else
        {
            $html .= '<tfoot>'."\n".'<tr>'."\n"
            .   '<td align="center">'.get_lang('No result').'</td>'."\n"
            .   '</tr>'."\n".'</tfoot>'."\n";
        }
        $html .= '</table>'."\n";

        // top 10 topics more seen
        $sql = "SELECT `topic_id`, `topic_title`, `topic_views`
                    FROM `".$this->tbl_bb_topics."`
                    ORDER BY `topic_views` DESC
                    LIMIT 10
                    ";
        $results = claro_sql_query_fetch_all($sql);

        $html .= '<table class="claroTable" cellpadding="2" cellspacing="1" border="0" align="center">'."\n"
        .   '<tr class="headerX">'."\n"
        .   '<th>'.get_lang('More read topics').'</th>'."\n"
        .   '<th>'.get_lang('Seen').'</th>'."\n"
        .   '</tr>'."\n";
        
        if( !empty($results) && is_array($results) )
        {
            $html .= '<tbody>'."\n";
            foreach( $results as $result )
            {
                $html .= '<tr>'."\n"
                    .'<td><a href="../phpbb/viewtopic.php?topic='.$result['topic_id'].'">'.$result['topic_title'].'</a></td>'."\n"
                    .'<td>'.$result['topic_views'].'</td>'."\n"
                    .'</tr>'."\n";
            }
            $html .= '</tbody>'."\n";

        }
        else
        {
            $html .= '<tfoot>'."\n".'<tr>'."\n"
            .   '<td align="center">'.get_lang('No result').'</td>'."\n"
            .   '</tr>'."\n".'</tfoot>'."\n";
        }
        $html .= '</table>'."\n";

        // last 10 distinct messages posted
        $sql = "SELECT `bb_t`.`topic_id`, `bb_t`.`topic_title`, max(`bb_t`.`topic_time`) as `last_message`
                FROM `".$this->tbl_bb_posts."` as `bb_p`, `".$this->tbl_bb_topics."` as `bb_t`
                WHERE `bb_t`.`topic_id` = `bb_p`.`topic_id`
                GROUP BY `bb_t`.`topic_title`
                ORDER BY `bb_p`.`post_time` DESC
                LIMIT 10";

        $results = claro_sql_query_fetch_all($sql);

        $html .= '<table class="claroTable" cellpadding="2" cellspacing="1" border="0" align="center">'."\n"
        .   '<tr class="headerX">'."\n"
        .   '<th>'.get_lang('Most recently active topics').'</th>'."\n"
        .   '<th>'.get_lang('Last message').'</th>'."\n"
        .   '</tr>'."\n";
        
        if (is_array($results))
        {
            $html .= '<tbody>'."\n";
            foreach( $results as $result )
            {
                $html .= '<tr>'."\n"
                .    '<td>'
                .    '<a href="../phpbb/viewtopic.php?topic=' . $result['topic_id'].'">' . $result['topic_title'] . '</a>'
                .    '</td>' . "\n"
                .    '<td>' . $result['last_message'] . '</td>' . "\n"
                .    '</tr>' . "\n"
                ;
            }
            $html .= '</tbody>'."\n";

        }
        else
        {
            $html .= '<tfoot>' . "\n"
            .    '<tr>' . "\n"
            .    '<td align="center">'
            .    get_lang('No result')
            .    '</td>' . "\n"
            .    '</tr>' . "\n"
            .    '</tfoot>' . "\n"
            ;
        }
        $html .= '</table>'."\n";

        
            
        return $html;
    }
    
    protected function renderFooter()
    {
        return '';
    }
}

TrackingRendererRegistry::registerCourse('CLFRM', 'CLFRM_CourseTrackingRenderer');

class CLFRM_UserTrackingRenderer extends TrackingRenderer
{   
    protected function renderHeader()
    {
        return claro_get_tool_name('CLFRM');
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

TrackingRendererRegistry::registerUser('CLFRM', 'CLFRM_UserTrackingRenderer');
?>