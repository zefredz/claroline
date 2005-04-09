<?php // $Id$µ
/**
 * CLAROLINE 
 *
 * Filler for tools in course
 *
 * @version 1.6
 *
 * @copyright (c) 2001-2005 Universite catholique de Louvain (UCL)
 *
 * @license  http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE 
 *
 * SHUFFLE COURSE FILL
 */

DEFINE("DISP_RESULT_INSERT"		,1);
DEFINE("DISP_FORM_SET_OPTION"	,2);
DEFINE("DISP_INSERT_COMPLETE"	,3);

unset($includePath);
require '../../inc/claro_init_global.inc.php';
// Security check
if (!$is_platformAdmin) claro_disp_auth_form();
//// Config tool
include($includePath."/conf/course_main.conf.php");
//// LIBS
include($includePath."/lib/add_course.lib.inc.php");
include($includePath."/lib/debug.lib.inc.php");
include($includePath."/lib/fileManage.lib.php");
include($includePath."/conf/course_main.conf.php");

$nameTools = $langAdd_users;
$interbredcrump[]= array ("url"=>"../index.php", "name"=> $langAdmin);
$interbredcrump[]= array ("url"=>"index.php", "name"=> $langDevTools);

$tbl_mdb_names = claro_sql_get_main_tbl();
$tbl_user      = $tbl_mdb_names['user'];
$tbl_tool      = $tbl_mdb_names['tool'];
$can_create_courses = (bool) ($is_allowedCreateCourse);


$toolNameList = array('CLANN___' => $langAnnouncement,
	                      'CLFRM___' => $langForums,
	                      'CLCAL___' => $langAgenda,
	                      'CLCHT___' => $langChat,
	                      'CLDOC___' => $langDocument,
	                      'CLDSC___' => $langDescriptionCours,
	                      'CLGRP___' => $langGroups,
	                      'CLLNP___' => $langLearningPath,
	                      'CLQWZ___' => $langExercises,
	                      'CLWRK___' => $langWork,
	                      'CLUSR___' => $langUsers);
if (isset($_REQUEST['create']))
{
    //echo '<p>$_REQUEST = <pre>'.var_export( $_REQUEST,1).'</pre>';
    
    $sqlCourses ='select * FROM `'.$tbl_course.'`'; 
    $course_list  = claro_sql_query_fetch_all($sqlCourses);
    foreach ($course_list as $course)
    {
        foreach ($_REQUEST['toolToFill'] as $tool_label)
        {
            for ($i = 1; $i <= rand(1,5); $i++)
                $result[$course['code']][$tool_label] = fill_tool_in_course($course['code'],$tool_label);
        }
    }
    echo '</ul>';
    
    
    $display=DISP_RESULT_INSERT;
}
else 
{
$display = DISP_FORM_SET_OPTION;
    $sql ="SELECT pct.id             id,
                   pct.claro_label    label,
                        pct.icon           icon,
                        pct.access_manager access_manager,
                        pct.script_url url
               FROM`".$tbl_tool."` pct";
    $tool_list  = claro_sql_query_fetch_all($sql);
    
}


	                      

include($includePath.'/claro_init_header.inc.php');
claro_disp_tool_title(
	array(
	'mainTitle'=>$nameTools,
	'subTitle'=> $siteName." - ".$clarolineVersion
	)
	);
	                      
claro_disp_msg_arr($controlMsg);

//////////////// OUTPUT
switch ($display)
{
	case DISP_RESULT_INSERT :
    echo '<ul>';
    foreach ($course_list as $course)
    {
        echo '<LI><b>'.$course['code'].'</b> : '.$course['intitule'].'<ul>';
        foreach ($_REQUEST['toolToFill'] as $tool_label)
        {
            echo '<li>Fill '.$toolNameList[$tool_label].' '.$result[$course['code']][$tool_label].'  </li>';
        }
        echo '</ul></LI>';
    }
    echo '</ul>';
		break;
	case DISP_FORM_SET_OPTION :
		?><br><br>
<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="POST" enctype="multipart/form-data" target="_self">
	<fieldset>
		<legend >Outils à remplir</legend>
		<table class="claroTable" >
			<tr>
				<th >
					<label for="toolToFill">Outils  : </label>
				</th>
				<th>
					<label for="courses">Cours  : </label>
				</th>
			</tr>
			<tr>
				<td>				
                    <select name="toolToFill[]" id="toolToFill" size="<?php echo (sizeof($tool_list)+1); ?>" multiple>
                    <?php
                    foreach($tool_list as $tool)
                    		echo '<option selected="selected" value="'.$tool['label'].'" >'.$toolNameList[$tool['label']].'</option>'."\n";
                    ?>
                    </select>
				</td>
				<td>
					<input type="radio" id="courses" selected="selected" name="courses" value="<?php echo $courses ?>" size="5" maxlength="4"> ALL
					Ya pas le choix pour le moment
				</td>
			</tr>
		</table>
	</fieldset>
	<fieldset >
		<legend >Données</legend>
		<table class="claroTable" >
            Ajout d'une ligne par outil par cours.
		</table>
	</fieldset>
	<input type="submit" name="create" value="create">
</form>
		<?php
		break;
	default : "hum erreur de display";

}

function fill_tool_in_course($course_code,$tool_label)
{
    global  $courseTablePrefix, $dbGlu, $coursesRepositorySys;
    $tbl_mdb_names = claro_sql_get_main_tbl();
    $tbl_course = $tbl_mdb_names['course'];
    $sql = 'SELECT code, dbName, directory path From `'.$tbl_course.'` where code="'.$course_code.'"';
    
    $course = claro_sql_query_fetch_all($sql);

    $course_id = $course[0]['code'];
    $course_dbNameGlu  = $courseTablePrefix . $course[0]['dbName'] . $dbGlu; // use in all queries
    $course_repository = $coursesRepositorySys.$course[0]['path'];
    
    $tbl_cdb_names = claro_sql_get_course_tbl($course_dbNameGlu);
    //echo '<p>$tbl_cdb_names= <pre>'.var_export( $tbl_cdb_names,1).'</pre>';        
    /*
              'bb_categories'          => $courseDb.'bb_categories',
              'bb_forums'              => $courseDb.'bb_forums',
              'bb_posts'               => $courseDb.'bb_posts',
              'bb_posts_text'          => $courseDb.'bb_posts_text',
              'bb_priv_msgs'           => $courseDb.'bb_priv_msgs',
              'bb_rel_topic_userstonotify'
                            => $courseDb.'bb_rel_topic_userstonotify',
              'bb_topics'              => $courseDb.'bb_topics',
              'bb_users'               => $courseDb.'bb_users',
              'bb_whosonline'          => $courseDb.'bb_whosonline',

              'course_description'     => $courseDb.'course_description',
              'document'               => $courseDb.'document',
              'lp_learnPath'           => $courseDb.'lp_learnPath',
              'lp_rel_learnPath_module'=> $courseDb.'lp_rel_learnPath_module',
              'lp_user_module_progress'=> $courseDb.'lp_user_module_progress',
              'lp_module'              => $courseDb.'lp_module',
              'lp_asset'               => $courseDb.'lp_asset',
              'quiz_answer'            => $courseDb.'quiz_answer',
              'quiz_question'          => $courseDb.'quiz_question',
              'quiz_rel_test_question' => $courseDb.'quiz_rel_test_question',
              'quiz_test'              => $courseDb.'quiz_test' ,
              'tool_intro'             => $courseDb.'tool_intro',
              'userinfo_content'       => $courseDb.'userinfo_content',
              'userinfo_def'           => $courseDb.'userinfo_def',
              'wrk_assignment'         => $courseDb.'wrk_assignment',
              'wrk_submission'         => $courseDb.'wrk_submission'

    */

    $tbl_rel_usergroup       = $tbl_cdb_names['group_rel_team_user'];
    $tbl_group               = $tbl_cdb_names['group_team'];
    $tbl_userInfo            = $tbl_cdb_names['userinfo_content'];
    
    $tbl_track_access    = $tbl_cdb_names['track_e_access'];    // access_user_id
    $tbl_track_downloads = $tbl_cdb_names['track_e_downloads'];
    $tbl_track_exercices = $tbl_cdb_names['track_e_exercices'];
    $tbl_track_upload    = $tbl_cdb_names['track_e_uploads'];// upload_user_id
    switch (trim($tool_label,'_'))
    {
        case 'CLANN' : 
            
            $lorem_title    = lorem('characters',rand(10,80));
            $lorem_content  = lorem('paragraphs',rand(1,8));
            $tbl_announcement        = $tbl_cdb_names['announcement'];
            $sql = "SELECT MAX(ordre)
                    FROM  `".$tbl_announcement."`";

            $result = claro_sql_query($sql);

            list($orderMax) = mysql_fetch_row($result);
            $order = $orderMax + 1;

            // INSERT ANNOUNCEMENT

            $sql = "INSERT INTO  `".$tbl_announcement."`
                    SET title ='".$lorem_title."',
                        contenu = '".$lorem_content."',
                    temps = NOW(),
                    ordre =\"".$order."\"";
           claro_sql_query($sql);
            return 'ok';
            break;
        case 'CLCAL' : 
            $lorem_title    = lorem('characters',rand(10,80));
            $lorem_content  = lorem('paragraphs',rand(1,8));
            
            $tbl_calendar_event        = $tbl_cdb_names['calendar_event'];
            $sql = "INSERT INTO `".$tbl_calendar_event."` 
                SET   titre   = '".$lorem_title."',
                      contenu = '".$lorem_content."',
                      day     = now(),
                      hour    = '".rand(1,23).":".rand(11,55)."',
                      lasting = '".rand(1,6)."h'";
            claro_sql_query($sql);
            return 'ok' ;
            break;
        case 'CLCHT' : 
            $nick     = 'lorem hips';
            $chatLine = lorem("words",rand(3,20));
            $curChatRep = $course_repository.'/chat/';
            
            if ( ! is_dir($curChatRep) ) claro_mkdir($curChatRep, 0777);
            $activeChatFile = $curChatRep.$course_id.'.chat.html';
            $timeNow = claro_disp_localised_date('%d/%m/%y [%H:%M]');
            if ( ! file_exists($activeChatFile))
            {
               	$fp = @fopen($activeChatFile, 'w');	
               	@fclose($fp);
            }
            if ($chatLine)
            {
            	$fchat = fopen($activeChatFile,'a');
            	$chatLine = htmlspecialchars( stripslashes($chatLine) );
            	$chatLine = ereg_replace("(http://)(([[:punct:]]|[[:alnum:]])*)","<a href=\"\\0\" target=\"_blank\">\\2</a>",$chatLine);
            
            	fwrite($fchat,
            	       '<small>'
            	       .$timeNow.' '
            	       .'<b>'.$nick.'</b>'
            	       .' &gt; '
            	       .$chatLine
            	       ."</small><br />\n");
            	
            	fclose($fchat);
            }
            return 'ok';
            break;
        case 'CLDOC' : 
            //$foo = lorem('words', 180);
            return $foo ;
        case 'CLDSC' : 
            break;
        case 'CLFRM' : 
        
            break;
        case 'CLGRP' : 
            break;
        case 'CLLNP' : 
            break;
        case 'CLQWZ' : 
        
            break;
        case 'CLUSR' : 
            break;
        case 'CLWRK' : 
            break;
        default : 
            return 'Nothing done';        
    }

}

function lorem($units, $length)
{
		$greekingList[] = 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit, '
        		        . 'sed diam nonummy nibh euismod tincidunt ut laoreet dolore '
        		        . 'magna aliquam erat volutpat. '
        		        . 'Ut wisi enim ad minim veniam, quis nostrud exerci tation '
        		        . 'ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. '
        		        . 'Duis autem vel eum iriure dolor in hendrerit in vulputate '
        		        . 'velit esse molestie consequat, vel illum dolore eu feugiat '
        		        . 'nulla facilisis at vero eros et accumsan et iusto odio '
        		        . 'dignissim qui blandit praesent luptatum zzril delenit augue '
        		        . 'duis dolore te feugait nulla facilisi. '
        		        ;
		$greekingList[] = 'Claroline is an Open Source software based on PHP/MySQL. It\'s a collaborative learning environment allowing teachers or education institutions to create and administer courses through the web.';
        $greekingList[] = 'The system provides group management, forums, document repositories, calendar, chat, assignment areas, links, user profile administration on a single and highly integrated package.';
        $greekingList[] = 'Claroline is translated in 28 languages and used by hundreds of institutions around world.The software was initially started by the University of Louvain (Belgium) and released under Open Source licence (GPL). Since then, a comunity of developper around the world contributes to its development. Downloading and using Claroline is completly free of charge.';
        $greekingList[] = 'A book on Claroline. Marcel Lebrun has just published "eLearning pour enseigner et apprendre" (eLearning, for Teaching and Learning).'
                         .'Based on the experience of Claroline at the University of Louvain (Belgium), it treats in a positive way how to elaborate pedagogical devices both adapted to these new technological tools and devoted to promote learning.';        		        
        $greekingList[] = 'Claroline 1.6 Release Candidate available. '
                        . 'Thanks to the Claroline community and a huge debugging campaign, Claroline 1.6 RC is now available. It should be the last release before the stable version of Claroline 1.6. Now, focus will be on the upgrade script as no further change would be planned to the new database structure of Claroline.';
        $greekingList[] = 'Li Europan lingues es membres del sam familie. Lor separat existentie es un myth. Por scientie, musica, sport etc., li tot Europa usa li sam vocabularium. Li lingues differe solmen in li grammatica, li pronunciation e li plu commun vocabules. Omnicos directe al desirabilitá de un nov lingua franca: on refusa continuar payar custosi traductores. It solmen va esser necessi far uniform grammatica, pronunciation e plu sommun paroles.';
        $greekingList[] = 'Ma quande lingues coalesce, li grammatica del resultant lingue es plu simplic e regulari quam ti del coalescent lingues. Li nov lingua franca va esser plu simplic e regulari quam li existent Europan lingues. It va esser tam simplic quam Occidental: in fact, it va esser Occidental. A un Angleso it va semblar un simplificat Angles, quam un skeptic Cambridge amico dit me que Occidental es.';
        $greekingList[] = 'Epsum factorial non deposit quid pro quo hic escorol. Olypian quarrels et gorilla congolium sic ad nauseum. Souvlaki ignitus carborundum e pluribus unum. Defacto lingo est igpay atinlay. Marquee selectus non provisio incongruous feline nolo contendre. Gratuitous octopus niacin, sodium glutimate. Quote meon an estimate et non interruptus stadium. Sic tempus fugit esperanto hiccup estrogen. Glorious baklava ex librus hup hey ad infinitum. Non sequitur condominium facile et geranium incognito. Epsum factorial non deposit quid pro quo hic escorol. Marquee selectus non provisio incongruous feline nolo contendre Olypian quarrels et gorilla congolium sic ad nauseum. Souvlaki ignitus carborundum e pluribus unum.';
//        $greekingList[] = '';
        //$greekingList[] = '';
        $greeking = $greekingList[rand(0,(sizeof($greekingList)-1))];
        $errorMsg = 'You need to supply attributes for "units" (legal values are "characters", "words", "sentences" or "paragraphs") and a positive integer, "length".<br /><br />Usage Example:<br />&nbsp;&nbsp;&nbsp;&nbsp;print(<strong>greek(\'paragraphs\', 3)</strong>);';

		if (!isset($units) || !isset($length) || ($length < 1)){
			exit($errorMsg);
		}
	
		$output = "";
	
		switch ($units)
		{
		
			case "characters":
				$output = substr($greeking, 0, $length);
				break;
		
			case "words":
				$aWord = strtok($greeking, " ");
				for ($ctr = 1; $ctr <= $length; $ctr++)
				{
					$output = $output . " " . $aWord;
					$aWord = strtok(" ");
		        }
		   		break;
		
		   case "sentences":
				$aSentence = strtok($greeking, ".");
				for ($ctr = 1; $ctr <= $length; $ctr++)
				{
					$output = $output . " " . $aSentence . ".";
					$aSentence = strtok(".");
		        }
		   		break;
		
			case "paragraphs":
				$aSentence = strtok($greeking, ".");
				srand((double)microtime()*1000000);//seed random number generator
				for ($ctrParagraph = 1; $ctrParagraph <= $length; $ctrParagraph++){
					$paragraph = "";
					$numberOfSentences = rand( 1, 3 );
					for ($ctrSentence = 1; $ctrSentence <= $numberOfSentences; $ctrSentence++)
					{
						$paragraph = $paragraph . " " . $aSentence . ".";
						$aSentence = strtok(".");
					}
					if ($ctrParagraph < $length)
					{
						$paragraph = $paragraph . "<br /><br />";
					}
					$output = $output . $paragraph;
				}
				break;
		
			default:
				exit($errorMsg);
		
		}//end switch($units)

	return $output;
	}//end function greek()


?>
