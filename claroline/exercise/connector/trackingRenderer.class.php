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

class CLQWZ_CourseTrackingRenderer extends CourseTrackingRenderer
{   
    private $tbl_qwz_exercise;
    private $tbl_track_exercises;
    
    public function __construct($courseId)
    {
        $this->courseId = (int) $courseId;
        
        $tbl_cdb_names = claro_sql_get_course_tbl(claro_get_course_db_name_glued($this->courseId));
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



/*
 * 
 */
class CLQWZ_UserTrackingRenderer extends UserTrackingRenderer
{   
    private $tbl_qwz_exercise;
    private $tbl_track_e_exercises;
    
    public function __construct($courseId, $userId)
    {
        $this->courseId = (int) $courseId;
        $this->userId = (int) $userId;
        
        $tbl_cdb_names = claro_sql_get_course_tbl(claro_get_course_db_name_glued($this->courseId));
        $this->tbl_qwz_exercise = $tbl_cdb_names['qwz_exercise'];
	    $this->tbl_track_e_exercises = $tbl_cdb_names['track_e_exercices'];
        
    }
    
    protected function renderHeader()
    {
        return claro_get_tool_name('CLQWZ');
    }
    
    protected function renderContent()
    {
        if( isset($_REQUEST['exId']) && is_numeric($_REQUEST['exId']) )   $exId = (int) $_REQUEST['exId'];
        else                                                              $exId = null;
        
        $exerciseResults = $this->prepareContent();
        
        $html = '';
        
    	$html = '<table class="claroTable emphaseLine" cellpadding="2" cellspacing="1" border="0" align="center">' . "\n"
    	.    '<tr class="headerX">' . "\n"
    	.    '<th>' . get_lang('Exercises').'</th>' . "\n"
    	.    '<th>' . get_lang('Worst score').'</th>' . "\n"
    	.    '<th>' . get_lang('Best score').'</th>' . "\n"
    	.    '<th>' . get_lang('Average score').'</th>' . "\n"
    	.    '<th>' . get_lang('Average Time').'</th>' . "\n"
    	.    '<th>' . get_lang('Attempts').'</th>' . "\n"
    	.    '<th>' . get_lang('Last attempt').'</th>' . "\n"
    	.    '</tr>'
        ;
    
    	if( !empty($exerciseResults) && is_array($exerciseResults) )
    	{
    	    $html .= '<tbody>' . "\n";
    	    foreach( $exerciseResults as $result )
    	    {
    	        $html .= '<tr>' . "\n"
    	        .    '<td><a href="userReport.php?userId='.(int) $this->userId.'&amp;cidReq='.$this->courseId.'&amp;exId='.(int) $result['id'].'">'.htmlspecialchars($result['title']).'</td>' . "\n"
    	        .    '<td>'.(int) $result['minimum'].'</td>' . "\n"
    	        .    '<td>'.(int) $result['maximum'].'</td>' . "\n"
    	        .    '<td>'.(round($result['average']*10)/10).'</td>' . "\n"
    	        .    '<td>'.claro_html_duration(floor($result['avgTime'])).'</td>' . "\n"
    	        .    '<td>'.(int) $result['attempts'].'</td>' . "\n"
    	        .    '<td>'.$result['lastAttempt'].'</td>' . "\n"
    	        .    '</tr>' . "\n";
    
    	        // display details of the exercise, all attempts
    	        if ( isset($exId) && $exId == $result['id'])
    	        {
    				$exerciseDetails = $this->getUserExerciceDetails($exId);
    
    	            $html .= '<tr>'
    	            .    '<td class="noHover">&nbsp;</td>' . "\n"
    	            .    '<td colspan="6" class="noHover">' . "\n"
    	            .    '<table class="claroTable emphaseLine" cellspacing="1" cellpadding="2" border="0" width="100%">' . "\n"
    	            .    '<tr class="headerX">' . "\n"
    	            .    '<th><small>' . get_lang('Date').'</small></th>' . "\n"
    	            .    '<th><small>' . get_lang('Score').'</small></th>' . "\n"
    	            .    '<th><small>' . get_lang('Time').'</small></th>' . "\n"
    	            .    '</tr>' . "\n"
    	            .    '<tbody>' . "\n";
    
    	            foreach ( $exerciseDetails as $details )
    	            {
    	                $html .= '<tr>' . "\n"
    	                .    '<td><small><a href="'.get_module_url('CLQWZ') . '/track_exercise_details.php?trackedExId='.$details['exe_id'].'">'.$details['exe_date'].'</a></small></td>' . "\n"
    	                .    '<td><small>'.$details['exe_result'].'/'.$details['exe_weighting'].'</small></td>' . "\n"
    	                .    '<td><small>'.claro_html_duration($details['exe_time']).'</small></td>' . "\n"
    	                .    '</tr>' . "\n";
    	            }
    	            $html .= '</tbody>' . "\n"
    	            .    '</table>' . "\n\n"
    	            .    '</td>' . "\n"
    	            .    '</tr>' . "\n";
    
    	        }
    
    	    }
    	    $html .= '</tbody>' . "\n";
    	}
    	else
    	{
    	    $html .= '<tfoot>' . "\n"
    	    .    '<tr>' . "\n"
    	    .    '<td colspan="7" align="center">' . get_lang('No result').'</td>' . "\n"
    	    .    '</tr>' . "\n"
    	    .    '</tfoot>' . "\n";
    	}
    	$html .= '</table>' . "\n\n";
        
        return $html;
    }
    
    protected function renderFooter()
    {
        return get_lang('Click on exercise title for more details');
    }
    
    private function prepareContent()
    {
    	$sql = "SELECT `E`.`title`,
                       `E`.`id`,
                       MIN(`TEX`.`exe_result`)    AS `minimum`,
                       MAX(`TEX`.`exe_result`)    AS `maximum`,
                       AVG(`TEX`.`exe_result`)    AS `average`,
                       MAX(`TEX`.`exe_weighting`) AS `weighting`,
                       COUNT(`TEX`.`exe_user_id`) AS `attempts`,
                       MAX(`TEX`.`exe_date`)      AS `lastAttempt`,
                       AVG(`TEX`.`exe_time`)      AS `avgTime`
                  FROM `" . $this->tbl_qwz_exercise . "` AS `E`
                     , `" . $this->tbl_track_e_exercises . "` AS `TEX`
            WHERE `TEX`.`exe_user_id` = " . (int) $this->userId . "
                AND `TEX`.`exe_exo_id` = `E`.`id`
            GROUP BY `TEX`.`exe_exo_id`
            ORDER BY `E`.`title` ASC";
    
        $results = claro_sql_query_fetch_all($sql);
    
        return $results;
    }
    
    private function getUserExerciceDetails($exerciseId)
    {
    	$sql = "SELECT `exe_id`, `exe_date`, `exe_result`, `exe_weighting`, `exe_time`
                FROM `" . $this->tbl_track_e_exercises . "`
                WHERE `exe_exo_id` = ". (int) $exerciseId."
                AND `exe_user_id` = ". (int) $this->userId."
                ORDER BY `exe_date` ASC";
    
        $results = claro_sql_query_fetch_all($sql);
    
        return $results;
    }
}

TrackingRendererRegistry::registerUser('CLQWZ_UserTrackingRenderer');
?>