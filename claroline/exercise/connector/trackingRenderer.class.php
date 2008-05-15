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
 * @package CLQWZ
 *
 * @author Claro Team <cvs@claroline.net>
 * @author Sebastien Piraux <pir@cerdecam.be>
 */

class CLQWZ_CourseTrackingRenderer extends TrackingRenderer
{   
    private $tbl_qwz_exercise;
    private $tbl_track_exercises;
    
    public function __construct()
    {
        $tbl_cdb_names = claro_sql_get_course_tbl();
        $this->tbl_qwz_exercise = $tbl_cdb_names['qwz_exercise'];
        $this->tbl_track_exercises = $tbl_cdb_names['track_e_exercices'];
    }
    protected function renderHeader()
    {
        return claro_get_tool_name('CLQWZ');
    }
    
    protected function renderContent()
    {
        $html = '';
        
        $sql = "SELECT TEX.`exe_exo_id`,
                    COUNT(DISTINCT TEX.`exe_user_id`) AS `nbr_distinct_user_attempts`,
                    COUNT(TEX.`exe_exo_id`) AS `nbr_total_attempts`,
                    EX.`title`
                FROM `".$this->tbl_track_exercises."` AS TEX, `".$this->tbl_qwz_exercise."` AS EX
                WHERE TEX.`exe_exo_id` = EX.`id`
                GROUP BY TEX.`exe_exo_id`";

        $results = claro_sql_query_fetch_all($sql);
        
        $html .= '<table class="claroTable" cellpadding="2" cellspacing="1" border="0" align="center">'."\n"
        .   '<tr class="headerX">'."\n"
        .   '<th>&nbsp;'.get_lang('Exercises').'&nbsp;</th>'."\n"
        .   '<th>&nbsp;'.get_lang('User attempts').'&nbsp;</th>'."\n"
        .   '<th>&nbsp;'.get_lang('Total attempts').'&nbsp;</th>'."\n"
        .   '</tr>'."\n"
        .   '<tbody>'."\n"
        ;

        if( !empty($results) && is_array($results) )
        {
            foreach( $results as $result )
            {
                    $html .= '<tr>'."\n"
                    .   '<td><a href="../exercise/track_exercises.php?exId='.$result['exe_exo_id'].'">'.$result['title'].'</a></td>'."\n"
                    .   '<td align="right">'.$result['nbr_distinct_user_attempts'].'</td>'."\n"
                    .   '<td align="right">'.$result['nbr_total_attempts'].'</td>'."\n"
                    .   '</tr>'."\n\n"
                    ;
            }
        }
        else
        {
            $html .= '<tr>' . "\n"
            .    '<td colspan="3">'
            .    '<div align="center">' . get_lang('No result') . '</div>'
            .    '</td>' . "\n"
            .    '</tr>' . "\n"
            ;
        }
        $html .= '</tbody>'."\n"
        .   '</table>'."\n"
        ;
            
        return $html;
    }
    
    protected function renderFooter()
    {
        return '';
    }
}

TrackingRendererRegistry::registerCourse('CLQWZ_CourseTrackingRenderer');

class CLQWZ_UserTrackingRenderer extends TrackingRenderer
{   
    protected function renderHeader()
    {
        return claro_get_tool_name('CLQWZ');
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

TrackingRendererRegistry::registerUser('CLQWZ_UserTrackingRenderer');
?>