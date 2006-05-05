<?php // $Id$
/**
 * CLAROLINE
 *
 * @version 1.8 $Revision$
 *
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @author Claro Team <cvs@claroline.net>
 *
 */
 
 
function get_filter_list()
{
	$tbl_cdb_names = claro_sql_get_course_tbl();
	$tbl_quiz_exercise = $tbl_cdb_names['quiz_2_exercise'];

	$filterList['all'] = get_lang('All exercises');
	$filterList['orphan'] = get_lang('Orphan questions');
	
	// get exercise list
	$sql = "SELECT `id`, `title` 
	          FROM `".$tbl_quiz_exercise."` 
	          ORDER BY `title`";
	$exerciseList = claro_sql_query_fetch_all($sql);
	
	if( is_array($exerciseList) && !empty($exerciseList) )
	{
		foreach( $exerciseList as $anExercise )
		{
			$filterList[$anExercise['id']] = $anExercise['title'];
		}
	} 	
	return $filterList;
}

function get_localized_question_type()
{
	$questionType['MCUA'] 		= get_lang('Multiple choice (Unique answer)');
	$questionType['MCMA'] 		= get_lang('Multiple choice (Multiple answers)');
	$questionType['TF'] 		= get_lang('True/False');
	$questionType['FIB'] 		= get_lang('Fill in blanks');
	$questionType['MATCHING'] 	= get_lang('Matching');
	
	return $questionType;
}

/**
 *
 *
 *
 */
function display_attached_file($attachedFile)
{
  global $attachedFilePathWeb;
  global $attachedFilePathSys;
  
  if( !file_exists($attachedFilePathSys.'/'.$attachedFile) )return false;
  
  // get extension
  $extension = strtolower(substr(strrchr($attachedFile, '.'), 1));
  
  $returnedString = '<p>'."\n";
  switch($extension)
  {
    case 'jpg' :
    case 'jpeg' :
    case 'gif' :
    case 'png' :
    case 'bmp' :
        $returnedString .= '<img src="'.$attachedFilePathWeb.'/'.$attachedFile.'" border="0" alt="'.$attachedFile.'" />'."\n";
        break;
    case 'swf' :
        $returnedString .= '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"'
                    .' codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0"'
                    .' id="'.$attachedFile.'">'."\n"
                    .'<param name="movie" value="'.$attachedFilePathWeb.'/'.$attachedFile.'">'."\n"
                    .'<param name="quality" value="high">'."\n"
                    .'<param name="bgcolor" value="#FFFFFF">'."\n"
                    .'<embed src="'.$attachedFilePathWeb.'/'.$attachedFile.'"  quality="high" bgcolor="#FFFFFF" name="'.$attachedFile.'" type="application/x-shockwave-flash"  pluginspage="http://www.macromedia.com/go/getflashplayer">'."\n"
                    .'</embed>'."\n"
                    .'</object>'."\n";
        break;
    
    case 'mp3' :
    		// a fake param with time() as value is added used to force cache refresh
    		$time = time();
    		$playerUrl = get_conf('clarolineRepositoryWeb').'inc/swf/dewplayer.swf?son='.$attachedFilePathWeb.'/'.$attachedFile.'&amp;bgcolor=FFFFFF&amp;fake='.$time;
    		
			$returnedString .= 
					'<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,0,0"'
						.' width="200" height="20" id="dewplayer" align="middle">' . "\n"
					.'<param name="allowScriptAccess" value="sameDomain" />' . "\n"
					.'<param name="movie" value="'.$playerUrl.'" />' . "\n"
					.'<param name="quality" value="high" />' . "\n"
					.'<param name="bgcolor" value="FFFFFF" />' . "\n"
					.'<embed src="'.$playerUrl.'" quality="high" bgcolor="FFFFFF" width="200" height="20" name="dewplayer"'
						.' align="middle" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />' . "\n"
					.'</object>' ."\n"
					.'<p>' . "\n" . '<small>' . "\n"
					.'<a href="'.$attachedFilePathWeb.'/'.$attachedFile.'">'.get_lang('Download attached file').'</a>' . "\n"
					.'</small>'."\n\n";
        break;
    
    default :
        $returnedString .= '<a href="'.$attachedFilePathWeb.'/'.$attachedFile.'" target="_blank">'.get_lang('Download attached file').'</a>'."\n";
        break;        
  
  }
  $returnedString .= '</p>'."\n";
  return $returnedString;
}
?>
