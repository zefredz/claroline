<?php // $Id$µ
/**
 * CLAROLINE 
 *
 * Filler for tools in course
 *
 * @version 1.6 $Revision$
 *
 * @copyright (c) 2001-2005 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE 
 *
 * @package SDK
 *
 * @author Claro Team <cvs@claroline.net>
 * @author Christophe Gesché <moosh@claroline.net>
 *
 */

$langPopulateTools = 'Populate tools in courses';

DEFINE('DISP_RESULT_INSERT'		,1);
DEFINE('DISP_FORM_SET_OPTION'	,2);
DEFINE('DISP_INSERT_COMPLETE'	,3);

unset($includePath);
require '../../inc/claro_init_global.inc.php';
// Security check
if (!$is_platformAdmin) claro_disp_auth_form();
//// Config tool
include($includePath.'/conf/course_main.conf.php');
//// LIBS
include($includePath.'/lib/add_course.lib.inc.php');
include($includePath.'/lib/debug.lib.inc.php');
include($includePath.'/lib/fileManage.lib.php');
include($includePath.'/conf/course_main.conf.php');

$nameTools = $langPopulateTools;
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
                      'CLUSR___' => $langUsers
                      );
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
    global  $courseTablePrefix, $dbGlu, $coursesRepositorySys,$includePath, $_course;
    $tbl_mdb_names = claro_sql_get_main_tbl();
    $tbl_course = $tbl_mdb_names['course'];
    $tbl_rel_course_user = $tbl_mdb_names['rel_course_user'];
    $sql = 'SELECT code, dbName, directory path From `'.$tbl_course.'` where code="'.$course_code.'"';
    
    $course = claro_sql_query_fetch_all($sql);

    $course_id = $course[0]['code'];
    $course_dbNameGlu  = $courseTablePrefix . $course[0]['dbName'] . $dbGlu; // use in all queries
    $course_repository = $coursesRepositorySys.$course[0]['path'];
    
    $_course['dbNameGlu'] = $course_dbNameGlu;
    
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
            $hour = 3600; 
            $day = 24 * $hour; 
            $week = 7 * $day; 
            $month = 31 * $day; 
            $randomTime = time()-6*$month
                        + rand(0,18) * $month
                        + rand(0, 5) * $week
                        + rand(0, 7) * $day
                        ;
            $randomTime = date('Y-m-d h:i',$randomTime);
            $tbl_announcement        = $tbl_cdb_names['announcement'];
            $sql = "SELECT MAX(ordre)
                    FROM  `".$tbl_announcement."`";

            $result = claro_sql_query($sql);

            list($orderMax) = mysql_fetch_row($result);
            $order = $orderMax + 1;

            // INSERT ANNOUNCEMENT

            $sql = "INSERT INTO  `".$tbl_announcement."`
                    SET title ='".addslashes($lorem_title)."',
                        contenu = '".addslashes($lorem_content)."',
                    temps = '".$randomTime."',
                    ordre =\"".$order."\"";
           claro_sql_query($sql);
            return 'ok';
            break;
        case 'CLCAL' : 
            $lorem_title    = lorem('characters',rand(10,80));
            $lorem_content  = lorem('paragraphs',rand(1,8));
            
            $hour = 3600; 
            $day = 24 * $hour; 
            $week = 7 * $day; 
            $month = 31 * $day; 
            $randomTime = time()-6*$month
                        + rand(0,18) * $month
                        + rand(0, 5) * $week
                        + rand(0, 7) * $day
                        ;
            $randomTime = date('Y-m-d',$randomTime);
                        
            
            $tbl_calendar_event        = $tbl_cdb_names['calendar_event'];
            $sql = "INSERT INTO `".$tbl_calendar_event."` 
                SET   titre   = '".addslashes($lorem_title)."',
                      contenu = '".addslashes($lorem_content)."',
                      day     = '".$randomTime."',
                      hour    = '".rand(1,23).":".rand(11,55)."',
                      lasting = '".rand(1,6)."h'";
              
            claro_sql_query($sql);
            return 'ok';
            break;
        case 'CLCHT' : 
            $nick     = 'lorem hips';
            $chatLine = lorem("words",rand(3,20));
            $curChatRep = $course_repository.'/chat/';
            
            if ( ! is_dir($curChatRep) ) 
            {
                claro_mkdir($curChatRep, 0777);
                if ( ! is_dir($curChatRep) ) 
                {
                    echo '<br> <b>création '.$curChatRep.' impossible</b>';
                }
            }
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
            //return ' this  filler is broken';
            //add ONE post. 
            // in a existing or new cat
            // in a existing or new forum
            // in a existing or new topic

            $tbl_forum_categories = $tbl_cdb_names['bb_categories'];
            $tbl_forum_forums     = $tbl_cdb_names['bb_forums'];
            $tbl_posts            = $tbl_cdb_names['bb_posts'];
            $tbl_posts_text       = $tbl_cdb_names['bb_posts_text'];
            $tbl_priv_msgs        = $tbl_cdb_names['bb_priv_msgs'];
            $tbl_topics           = $tbl_cdb_names['bb_topics'];

            $tbl_forums = $tbl_forum_forums;
            require_once '../../phpbb/functions.php';

            $resultPopulate = '<ul>';        
            
            // SELECT CATEGORY... Create it if needed
            $sql = "SELECT COUNT(`cat_id`) cat_qty
                    FROM   `".$tbl_forum_categories."` ";
            $categories       = claro_sql_query_fetch_all($sql);
            $total_categories = (int) $categories[0]['cat_qty'];
            $categoryToPopulate = rand(1,$total_categories+1);
            if ($categoryToPopulate > $total_categories)
            {
                $category_title =  lorem('characters',rand(10,150));
                //Create a new category
                $sql = 'SELECT MAX(`cat_order`) max_order'
                     . ' FROM `'.$tbl_forum_categories.'`';
        
                $orderMax = claro_sql_query_fetch_all($sql);
                $order = $orderMax[0]['max_order'] + 1;
        
                $sql = 'INSERT INTO `'.$tbl_forum_categories.'`
                        SET `cat_title` = "'.$category_title.'",
                            `cat_order` = "'.$order.'"';
                $categoryToPopulate = claro_sql_query_insert_id($sql);
                $resultPopulate .= '<li>Cat '.$categoryToPopulate.' created :  <i>'.$category_title.'</i>'.'</li>';        
            }
            $resultPopulate .= '<li>Cat '.$categoryToPopulate.' selected.  '.'</li>';        
            
            // SELECT FORUM... Create it if needed
            $sql = "SELECT count(`forum_id`) frm_qty 
                    FROM    `".$tbl_forum_forums."` f
                    WHERE `cat_id`='".$categoryToPopulate."'";
            
            $frm_qty = claro_sql_query_fetch_all($sql);
            $frm_qty = (int) $frm_qty[0]['frm_qty'];
            if ($categoryToPopulate=1)
            {
                $forumToPopulate = rand(1,$frm_qty);
                // Can't create forum in group category
            }
            else
            {
                $forumToPopulate = rand(1,$frm_qty+1);
            }
            if ($forumToPopulate > $frm_qty)
            {
                $forum_name = lorem('words',rand(2  ,10));
                $forum_desc = lorem('paragraphs',rand(1,5));
                
                // find order in the category we must give to the newly created forum
        
                $sql = 'SELECT MAX(`forum_order`) max_order
                        FROM `'.$tbl_forum_forums.'`
                        WHERE cat_id = "'.$categoryToPopulate.'"';
                $orderMax = claro_sql_query_fetch_all($sql);
                $order = $orderMax[0]['max_order'] + 1;

                // add new forum in DB
        
                $sql = 'INSERT INTO `'.$tbl_forum_forums.'`
                        (forum_id, forum_name, forum_desc, forum_access,forum_moderator, cat_id, forum_type, md5, forum_order)
                        VALUES
                        (NULL,"'.$forum_name.'", "'.$forum_desc.'", "2", "1", "'.$categoryToPopulate.'", "0", "'.md5(time()).'", "'.$order.'")';

                $categoryToPopulate = claro_sql_query_insert_id($sql);
                $resultPopulate .= '<li> Forum '.$forumToPopulate.' created :  <i>'.$forum_name.'</i>'.'</li>';        
            }
            $resultPopulate .= '<li> Forum '.$forumToPopulate.' selected.  '.'</li>';        
            
            // SELECT TOPIC... Create it if needed
            $sql = "SELECT count(`topic_id`) topic_qty 
                    FROM  `".$tbl_topics."` 
                    WHERE `topic_id`='".$topicToPopulate."'";
            
            $topic_qty = claro_sql_query_fetch_all($sql);
            $topic_qty = (int) $topic_qty[0]['frm_qty'];
            $topicToPopulate = rand(1,$topic_qty+1);
            $time = date('Y-m-d H:i');
            if ($topicToPopulate > $topic_qty)
            {
                $topic_title = lorem('words',rand(2  ,10));
                
                // find order in the category we must give to the newly created forum
        
                // add new topic in DB
        
                $topicToPopulate = create_new_topic(   $topic_title, 
                                                       $time, 
                                                       $forumToPopulate, 
                                                       $_uid,
                                                       $_user['firstname'], 
                                                       $_user['lastname']);

                $resultPopulate .= '<li> Topic '.$topicToPopulate.' created  :  <i>'.$topic_title.'</i>'.'</li>';        
            }
            $resultPopulate .= '<li> Topic '.$topicToPopulate.' selected.  '.'</li>';

            $lorem_message = lorem('paragraphs',rand(1,10));
    
            create_new_post($topicToPopulate, 
                            $forumToPopulate, 
                            $_uid, 
                            $time, 
                            $REMOTE_ADDR, 
                            $_user['lastname'], 
                            $_user['firstname'], 
                            $lorem_message);
            $resultPopulate .= '<li> New post created'.'</li>';        
                        
            $resultPopulate .= '</ul>';        
            return $resultPopulate;
            break;
        case 'CLGRP' : 
                return 'Nothing to add here, use fillCourse with add randomly groups';
            break;
        case 'CLLNP' : 
                return 'Filler not ready';
            break;
        case 'CLQWZ' : 
                ///// Select An quizz
                ///// Select a Question
                ///// Add an Answer
                
                return 'Filler not ready';
            break;
        case 'CLUSR' : 
            return 'Filler not ready';
            require_once($includePath.'/lib/user.lib.inc.php');
            $def_title = lorem('words',rand(2  ,10));
            $def_comment = lorem('paragraphs',rand(1,5));
            $tbl_userinfo_def     = $tbl_cdb_names['userinfo_def'];
            $tbl_userinfo_content = $tbl_cdb_names['userinfo_content'];
            $resultPopulate .= '<ul>';        
            // Create user_info blocs
            $sql = "SELECT count(`id`) def_qty 
                    FROM  `".$tbl_userinfo_def."` ";
            
            $def_qty = claro_sql_query_fetch_all($sql);
            $def_qty = (int) $def_qty[0]['def_qty'];
            
            $defToPopulate = rand (1,$def_qty+1);
            if ($defToPopulate>$def_qty)
            {
                $defToPopulate = claro_user_info_create_cat_def($def_title, $def_comment, rand(1,10));
                $resultPopulate .='<li>Create Def bloc '.$defToPopulate. ' : <i>'.$def_comment.'</i></li>';
            }
            $resultPopulate .='<li>Use Def bloc '.$defToPopulate.'</li>';

            // add user_info contents
            $sql = "select user_id From `".$tbl_rel_course_user."` WHERE code_cours='".$course_code."' ";
            $userList = claro_sql_query_fetch_all($sql);
            
            $rand_keys = array_rand ($userList, rand(1, sizeof($userList)));
            if(!is_array($rand_keys)) // stupid array_rand do not an array 
                                      // if result contain only 1 value
            {   // rebuild an array
                $rand_key = $rand_keys;
                unset($rand_keys);
                $rand_keys[0] = $rand_key;
                unset($rand_key);
            }
            
            
            foreach($rand_keys as $rand_key)
            {
                $user = $userList[$rand_key];
                $userIdViewed = $user['user_id'];
                $def_content = lorem('paragraphs',rand(1,5));
                $resultPopulate .= '<li>'.'Complete userInfo of user '.$userIdViewed.'</LI>';        
                $sql = "SELECT count(id) userDef_qty
                        FROM  `".$tbl_userinfo_content."` 
                        WHERE `user_id` = '".$userIdViewed."' AND `def_id` = '".$defToPopulate."' ";

                $userDefQty = claro_sql_query_fetch_all($sql);
                $userDefQty = (int) $userDefQty[0]['userDef_qty'];
                //choose a bloc to fill
                if ($userDefQty)    // submit a content change
                {
                    claro_user_info_edit_cat_content($defToPopulate, $userIdViewed, $def_content, $REMOTE_ADDR);
                }
                else        // submit a totally new content
                {
                    claro_user_info_fill_new_cat_content($defToPopulate, $userIdViewed, $def_content, $REMOTE_ADDR);
                }
            }
            $resultPopulate .= '</ul>';        
            return $resultPopulate;
            break;
        case 'CLWRK' : 
                return 'Filler not ready';
            break;
        default : 
            return 'Nothing done';        
    }

}

function lorem($units, $length)
{
		$greekingList[] = 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Cras vel lacus. Phasellus laoreet lacus nec nunc. Suspendisse fermentum orci in ligula fermentum hendrerit. Integer magna sapien, consequat volutpat, laoreet at, posuere sed, enim. Proin ornare. Aenean ullamcorper iaculis risus. Suspendisse potenti. Nulla vitae enim eget magna tristique dictum. Vestibulum facilisis ipsum nec odio. Curabitur mi.';
    	$greekingList[] = 'In nonummy metus id turpis. Curabitur sagittis, arcu id venenatis ullamcorper, odio orci viverra augue, a facilisis erat turpis a dolor. Quisque condimentum eros non pede. Fusce ullamcorper massa vitae libero. Fusce erat wisi, ornare vel, molestie eget, rutrum ac, lectus. Etiam risus nibh, gravida in, hendrerit in, accumsan a, orci. In semper lacinia lectus. Vivamus eget purus eget lorem feugiat ultrices. Mauris fringilla. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos hymenaeos. Curabitur convallis orci eget neque. Vestibulum vestibulum sapien eu neque.';
    	$greekingList[] = 'Morbi orci. Cras a risus at nisl consequat fermentum. Aliquam eu arcu eget ante adipiscing ultrices. Mauris diam. Donec nec libero. Aliquam iaculis felis at mauris. Nulla facilisi. Nullam dictum luctus erat. In hac habitasse platea dictumst. Praesent mollis massa sit amet massa. Sed et enim sed nulla pellentesque mollis. Vestibulum faucibus urna sed felis.';
    	$greekingList[] = 'Quisque fringilla purus ut felis. Phasellus quam erat, tincidunt commodo, vestibulum vel, ultricies eleifend, diam. In hac habitasse platea dictumst. Phasellus pretium. Donec tincidunt mauris sed eros. Nam ut sem. Cras pretium bibendum sem. Pellentesque lectus felis, tempor quis, sollicitudin ac, elementum ut, odio. Donec euismod nunc ut tortor. Phasellus dictum pede eu leo. Nulla nunc nibh, sagittis sed, molestie et, blandit nec, lorem. Phasellus ante quam, rutrum non, nonummy quis, viverra sit amet, quam. Quisque volutpat tellus a justo. Donec laoreet urna sit amet tortor. Vivamus justo. Aenean sit amet odio ut odio eleifend vulputate. Fusce blandit enim quis turpis. Aenean cursus lectus eu neque. Curabitur tempor rutrum neque. Fusce viverra.';
    	$greekingList[] = 'Morbi est. Nulla velit eros, iaculis quis, luctus nec, varius nec, velit. Nulla facilisi. Aliquam erat volutpat. Aliquam ante mauris, dignissim eget, adipiscing eget, volutpat at, urna. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Aenean tristique, erat nec scelerisque aliquet, nisl urna cursus metus, vitae dapibus mi mauris ac nibh. Nam pellentesque auctor urna. Pellentesque non tellus. In hac habitasse platea dictumst. Ut tristique interdum urna. Proin metus. Ut fermentum diam sed magna. Integer scelerisque faucibus magna. Ut molestie, wisi et vulputate lobortis, nunc felis sollicitudin ante, in tincidunt tortor erat et lectus. Maecenas cursus. Nunc arcu wisi, facilisis ut, malesuada eget, fringilla in, wisi. Vestibulum urna elit, nonummy pretium, ullamcorper faucibus, consectetuer eu, dui. Quisque pretium nibh eu metus. Quisque sit amet ante eget nulla hendrerit vestibulum.';
    	$greekingList[] = 'Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Proin at enim ac ante convallis pretium. Duis in dui. Mauris at quam et lacus consequat condimentum. Vestibulum rhoncus condimentum arcu. Donec quis mauris sed mi malesuada lacinia. Curabitur quis libero. Aliquam eget metus. Sed sapien massa, auctor ac, euismod sed, facilisis vitae, erat. Nulla scelerisque nibh in leo. Maecenas porttitor orci sollicitudin lorem. Etiam ac erat vulputate wisi scelerisque semper. Maecenas eget ipsum. Sed congue velit ut lorem. Vestibulum condimentum orci nec leo. Sed dui diam, lacinia in, ultricies ac, bibendum sollicitudin, est.';
    	$greekingList[] = 'Vestibulum ac quam. Cras id justo. Praesent scelerisque. Nunc lectus. Quisque scelerisque. Phasellus euismod, enim sit amet pretium tristique, neque velit sodales lectus, at varius eros elit at erat. Maecenas lacinia, tortor interdum lobortis pharetra, sem mi imperdiet orci, sed semper elit nunc vel metus. Ut in neque. Etiam pede. Phasellus blandit semper dui. Maecenas lectus neque, sagittis vitae, feugiat vitae, consequat ut, mauris.';
    	$greekingList[] = 'Aenean vitae quam. Vivamus imperdiet. Fusce nunc elit, cursus commodo, ornare ut, lobortis vel, augue. In eleifend mi ac eros. Nullam viverra lorem. Etiam wisi nisl, rutrum vel, laoreet quis, aliquet eu, pede. Aenean sed est eget libero feugiat tincidunt. Sed porttitor leo ut tellus. Nullam aliquam tellus quis enim. Phasellus eget eros.';
    	$greekingList[] = 'Ut eget magna quis quam hendrerit pretium. Suspendisse et justo sodales lacus nonummy suscipit. Morbi imperdiet suscipit eros. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos hymenaeos. Vestibulum a wisi. Nunc in lacus. Praesent quis sapien nec nisl volutpat egestas. Donec elit. Phasellus nibh. Quisque bibendum purus id tellus. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Suspendisse potenti. Aenean pharetra. Nam purus turpis, dignissim non, venenatis sit amet, luctus eu, pede. Aliquam fermentum vehicula dolor.';
    	$greekingList[] = 'Vestibulum et risus. Donec nisl purus, interdum sed, tristique nec, pretium ac, odio. Sed tempor mollis felis. Cras eget wisi quis est consequat posuere. Aenean eu elit. Etiam lobortis venenatis nulla. Sed id magna a tortor interdum tristique. Etiam pretium wisi sagittis justo. Nullam malesuada enim varius nulla. Maecenas diam. Phasellus consectetuer quam non metus. Integer dignissim nonummy dolor.';
    	$greekingList[] = 'Morbi a arcu. Nullam non odio a dui tempor porttitor. Sed blandit felis at elit. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Cras et sem. Ut mi purus, hendrerit in, pellentesque id, lacinia nec, turpis. In hac habitasse platea dictumst. Maecenas pulvinar egestas magna. Fusce tincidunt cursus lacus. Donec placerat lacus id pede. Fusce commodo. Vestibulum volutpat, velit vitae congue pulvinar, ipsum libero commodo neque, eu pulvinar pede nulla sed neque. Donec neque. Sed id purus. Etiam malesuada dictum neque. Proin feugiat augue vel risus. Phasellus sagittis tristique pede.';
    	$greekingList[] = 'In mauris. Donec sapien. Pellentesque eget risus. Aenean eu mi sit amet velit mattis rutrum. In accumsan pede interdum neque. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Donec lacus. Integer eget mi. Nulla elit lectus, tempus at, vulputate id, pulvinar quis, pede. Cras ornare aliquet wisi.';
    	$greekingList[] = 'Maecenas ac nibh. Nullam pede orci, pulvinar adipiscing, nonummy quis, ornare at, enim. Praesent lectus lorem, scelerisque id, convallis in, tristique id, sapien. Aliquam mattis. Duis auctor felis nec ligula. Fusce nec urna. Pellentesque viverra massa ac mi. Aenean placerat wisi ac justo. Integer vestibulum venenatis orci. Cras ac erat nec odio dignissim ultrices. In commodo turpis eget magna. Curabitur condimentum, odio in scelerisque condimentum, justo odio suscipit eros, vitae imperdiet magna tellus in lacus. Duis semper, justo et porta dictum, est mi dapibus nunc, consequat imperdiet turpis sapien eget felis. Sed vel wisi eu velit sollicitudin venenatis. Aenean non orci ac lacus porta varius.';
    	$greekingList[] = 'Curabitur suscipit pulvinar mi. Praesent et massa. Vivamus nunc nibh, ornare nec, sagittis quis, egestas id, neque. Suspendisse odio. Praesent vitae nunc in risus dignissim accumsan. Sed placerat mauris ac ante. Sed pellentesque mattis libero. Suspendisse faucibus ante et leo. Nullam sapien magna, feugiat a, cursus sed, ullamcorper eget, tellus. Ut vulputate urna eu erat. Cras et nisl.';
    	$greekingList[] = 'Fusce at mi nec eros viverra tristique. Nullam a urna. Etiam placerat metus et augue. Aliquam tincidunt orci nec nulla. Nam adipiscing justo a lectus. Quisque at nisl. Nullam massa urna, sodales in, rutrum elementum, vehicula at, leo. Nullam malesuada feugiat massa. Mauris pharetra enim vel lacus. Mauris fermentum dignissim libero. Praesent lacus. Donec rhoncus dui vel neque. Morbi bibendum, leo id lacinia interdum, odio metus aliquet ligula, ac rhoncus urna lorem aliquam lectus. Etiam pharetra, velit quis tincidunt eleifend, eros sapien ultricies erat, quis lacinia risus augue vitae ante. Phasellus tempor leo in velit. Ut molestie, nibh nec laoreet imperdiet, est velit rhoncus risus, a bibendum massa tortor sit amet ante. Integer ipsum. Donec nulla nunc, rhoncus nec, imperdiet in, tincidunt sit amet, orci. Donec quis nisl vitae augue semper iaculis. Etiam tincidunt euismod justo.';
    	$greekingList[] = 'Pellentesque leo enim, consectetuer ut, consectetuer eu, vestibulum et, lorem. Praesent in lectus sit amet lorem porttitor egestas. Quisque auctor, metus ut dictum euismod, felis orci pretium justo, interdum vestibulum est metus in enim. Mauris ut wisi et ipsum luctus nonummy. Pellentesque sem tellus, elementum nec, venenatis id, iaculis quis, mi. Vestibulum metus leo, facilisis eu, pellentesque eget, mollis vitae, massa. Mauris tempus, ante ut interdum gravida, nibh urna consectetuer velit, ornare vehicula tellus est sed metus. Nulla facilisi. Pellentesque nec nulla. Mauris tortor lorem, rhoncus eget, consectetuer ut, ultrices sit amet, leo. In molestie, mi sed faucibus tempor, arcu justo convallis urna, vulputate molestie ligula massa vel enim. Curabitur cursus placerat neque. Vestibulum tempor, purus ut imperdiet convallis, enim ipsum ullamcorper felis, ut vestibulum ligula magna scelerisque metus.';
    	$greekingList[] = 'Donec eu nibh. Ut ut odio vel felis blandit condimentum. Fusce velit pede, nonummy vel, tincidunt vel, mattis vestibulum, lorem. Proin id lorem. Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Ut erat lectus, placerat vitae, porttitor non, consectetuer quis, libero. Aliquam commodo, est non elementum varius, magna erat faucibus magna, et fermentum nisl nisl eu magna. Nullam vitae lectus vel nisl varius feugiat. Phasellus odio. Vivamus placerat quam vitae libero. Morbi non enim. Sed vel libero non libero porta laoreet. Etiam nonummy justo cursus lectus.';
    	$greekingList[] = 'Integer ornare, orci dictum blandit ornare, lorem quam scelerisque tellus, nec dignissim felis libero eget elit. Suspendisse quis sapien. Etiam vel wisi scelerisque urna nonummy pharetra. Integer pellentesque tristique enim. Pellentesque molestie justo sit amet arcu. Suspendisse potenti. Pellentesque eu nulla. Aliquam eu turpis. In hac habitasse platea dictumst. Ut quis risus ac mauris iaculis imperdiet. Sed sed velit nec ligula consequat faucibus. Praesent auctor, risus a feugiat posuere, eros tellus tincidunt dolor, eget laoreet augue pede eu erat. Vestibulum quis odio et justo luctus molestie. Integer sodales sagittis nunc. Integer quis tortor eu pede tempus fringilla. Quisque suscipit purus sed turpis. Maecenas at metus. Proin ut risus in augue vehicula semper. Ut sodales fringilla nibh.';
    	$greekingList[] = 'Nam nec sem commodo dui sodales semper. Mauris euismod dui vitae ante. Vestibulum at risus. Proin lacus neque, feugiat ac, cursus ac, tincidunt quis, augue. Sed a libero. Proin nunc. Nullam faucibus aliquam elit. Mauris et turpis sed nunc iaculis posuere. Mauris sed lacus ultricies urna accumsan luctus. Nunc elementum aliquam ipsum. Mauris pede sem, cursus a, tristique sed, venenatis in, sapien. Aliquam id magna vitae nibh euismod nonummy. In eget nulla et justo aliquet vehicula. Sed facilisis, enim in luctus dapibus, metus risus tincidunt risus, sed tristique velit sem sed sapien. Nunc felis. Nulla eu turpis sed odio consequat interdum. Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Nulla facilisi. Ut in diam. Morbi quis enim et dui volutpat blandit.';
    	$greekingList[] = 'Sed ac lorem ut odio lacinia volutpat. In eget augue ac nibh tincidunt fermentum. Fusce sagittis viverra odio. Duis ultricies tincidunt arcu. Quisque vehicula lacus sit amet turpis. Donec dictum, augue ac ultrices volutpat, odio turpis ultrices nulla, id lacinia erat ante at magna. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Ut ut nisl. Curabitur elit dolor, interdum a, nonummy eu, venenatis eu, sem. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Sed nunc neque, condimentum vitae, posuere sit amet, suscipit facilisis, enim. Aliquam urna lacus, commodo eu, tristique in, convallis at, massa. Donec nec orci. Morbi facilisis, sapien elementum condimentum molestie, odio pede congue pede, id porttitor lorem metus lacinia dolor.';
    	$greekingList[] = 'Quisque ut nulla ut enim ornare vestibulum. Pellentesque varius, tortor sed iaculis luctus, dolor risus consequat velit, at venenatis mauris quam vitae quam. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Cras aliquam egestas eros. Praesent at diam. Praesent accumsan pede id elit. Donec volutpat sem non magna. Suspendisse blandit tincidunt nulla. Morbi laoreet, tellus in lobortis vulputate, felis nibh imperdiet odio, a posuere elit eros at lectus. Sed dapibus, justo vitae dictum nonummy, felis urna rhoncus ligula, vitae cursus velit turpis porttitor magna. Donec felis tellus, accumsan eget, consequat nec, tincidunt at, enim. Donec in lectus vitae felis scelerisque pellentesque. Curabitur consequat vehicula tortor. Praesent vitae justo sed massa luctus vehicula. Curabitur vehicula. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos hymenaeos. Proin mattis arcu eget justo. Integer pulvinar ultrices nunc. Vestibulum fringilla dui sit amet metus.';
    	$greekingList[] = 'Aliquam ipsum. Ut urna ipsum, iaculis eget, pharetra eget, dapibus quis, risus. Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Etiam in est eu augue ullamcorper placerat. Aenean quis mi eget neque lacinia accumsan. Sed pulvinar fermentum dui. Sed eu massa. Nulla mattis velit vel odio. Duis id dui nec elit sodales fringilla. Mauris mi. Aliquam erat volutpat. Duis tortor dolor, consequat sagittis, lacinia a, aliquet laoreet, nibh. Sed tempus lacinia justo. Maecenas sollicitudin aliquet tellus. Aliquam fringilla wisi a tortor. In eu massa. Praesent gravida purus in est. Ut vitae neque. In hac habitasse platea dictumst.';
    	$greekingList[] = 'Fusce varius pulvinar diam. Pellentesque consequat, enim sed accumsan bibendum, massa lacus cursus tortor, vitae rutrum tellus diam at augue. Pellentesque tortor erat, auctor volutpat, venenatis non, accumsan eu, velit. In fermentum. Aenean ante tellus, ultrices vitae, porttitor et, cursus eget, augue. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos hymenaeos. Morbi tincidunt feugiat tortor. Maecenas ullamcorper tincidunt massa. Sed magna metus, porttitor ac, bibendum eu, sagittis a, lacus. Donec eu purus ut erat mattis tempor. Cras magna nisl, ultricies vitae, ullamcorper vitae, tristique ac, lorem.';
    	$greekingList[] = 'Phasellus tincidunt quam non sapien. Pellentesque varius. Sed auctor. Etiam ornare. Suspendisse pede tortor, pulvinar id, consequat vitae, mattis vel, massa. Nulla tempor urna quis leo. Donec pulvinar consectetuer augue. Cras leo. Proin fermentum, magna quis laoreet mattis, tellus pede molestie orci, et fermentum wisi tellus vitae urna. Phasellus rhoncus rutrum purus. Fusce pretium dolor eget justo.';
    	$greekingList[] = 'Nam ultrices pellentesque tellus. Curabitur sit amet mauris a nulla pretium placerat. Morbi fringilla viverra metus. Vestibulum vel urna. Mauris tempus vulputate sapien. Sed quam massa, molestie quis, aliquam quis, luctus sit amet, ante. Nulla et neque hendrerit risus posuere lacinia. Ut congue condimentum nibh. Phasellus arcu diam, consectetuer id, adipiscing quis, tempus id, neque. Etiam dapibus mollis risus.';
    	$greekingList[] = 'Etiam ultrices magna vel leo vehicula sodales. Morbi et erat et quam vestibulum euismod. Nullam sit amet elit. Duis wisi arcu, cursus eu, imperdiet sed, tincidunt quis, lacus. Aenean in dolor. In hac habitasse platea dictumst. Aliquam a eros. Suspendisse potenti. Phasellus volutpat. Pellentesque tristique aliquam est. Mauris semper wisi id diam. Curabitur rhoncus. Donec gravida erat eget ligula pellentesque consectetuer. Phasellus ipsum. Etiam dignissim ipsum sed turpis. Aenean commodo mi eu est. Nam sit amet mi non erat condimentum aliquet. Proin ultrices.';
    	$greekingList[] = 'Vivamus at lacus. Maecenas bibendum cursus mauris. Nunc condimentum, quam ut elementum luctus, lacus metus bibendum ipsum, id vehicula dui lacus eget lacus. Vivamus velit odio, rutrum sed, lacinia vitae, adipiscing ut, diam. Nulla sit amet sapien. Sed elit quam, egestas vitae, lacinia ut, iaculis eu, lectus. Quisque et diam ut sem pulvinar convallis. In hac habitasse platea dictumst. Morbi vestibulum libero eget wisi. Phasellus vel nisl eget magna dapibus euismod. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos hymenaeos. Integer id neque sed ante mattis tempor. Phasellus eget tellus ac sem ullamcorper fermentum. Curabitur nec ligula id wisi interdum sodales. Curabitur pede nisl, facilisis at, suscipit et, tincidunt sed, urna. Pellentesque eu elit eu leo feugiat placerat. Nunc nec ipsum sit amet ligula egestas feugiat.';
    	$greekingList[] = 'Mauris egestas felis. Proin feugiat. Nulla ac mi sit amet elit facilisis pulvinar. Curabitur non felis. Morbi arcu nibh, viverra dignissim, hendrerit a, vulputate sit amet, odio. In non orci. Vivamus eget enim non neque euismod vehicula. Curabitur tempor velit. Phasellus placerat porta quam. Morbi quis ante. Donec pharetra diam vitae odio. Proin id nibh. Nullam ac felis. Aenean vitae ipsum. Vivamus rutrum ante nec nulla. Sed id augue sit amet leo aliquet consequat. Cras accumsan magna eget mauris luctus viverra. Sed sed massa ut nulla tincidunt faucibus.';
    	$greekingList[] = 'Vivamus fringilla mattis mi. Morbi tempor neque eu elit. Fusce id nunc non urna viverra viverra. Phasellus sed ligula at erat bibendum suscipit. Nullam consequat erat sit amet augue. Sed id nunc sed metus blandit eleifend. Nulla et lacus a eros tincidunt dictum. Aliquam erat volutpat. Aenean non tortor scelerisque risus ultricies laoreet. Cras in nulla ac mauris lobortis congue. Ut nec velit. Morbi malesuada. Nunc volutpat. Nam massa felis, viverra tempor, adipiscing vitae, tincidunt et, dui. Curabitur quis tellus. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Nullam sem turpis, rhoncus at, tincidunt vel, consectetuer nonummy, wisi. Morbi vel urna. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos hymenaeos.';
    	$greekingList[] = 'Quisque vitae pede in lectus fringilla faucibus. Maecenas dapibus pede a lacus. Donec lectus lorem, ullamcorper et, aliquet id, gravida a, erat. Proin suscipit sollicitudin arcu. Curabitur at odio a massa posuere pharetra. Donec nibh enim, scelerisque sit amet, scelerisque sed, hendrerit eget, ipsum. Sed scelerisque orci id urna. Aliquam erat volutpat. Quisque faucibus. Sed volutpat egestas sem. Quisque at orci eu dui consequat vulputate. Sed fringilla facilisis metus. In pretium, ipsum sed aliquam lacinia, odio tortor ultrices diam, et vehicula nisl velit feugiat odio. Mauris imperdiet purus vel pede. In hac habitasse platea dictumst. Phasellus facilisis mollis orci. Aliquam erat volutpat. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Nulla facilisis, libero ut ultricies fermentum, mauris enim hendrerit ante, in suscipit nisl enim at odio. In lorem.';
    	$greekingList[] = 'Nulla malesuada est vitae nisl. In dui. Maecenas interdum magna sed wisi. Phasellus pretium ornare elit. Cras et velit. Fusce non velit. Pellentesque et odio. Pellentesque laoreet tellus quis diam. Aliquam sed sapien vel wisi suscipit consequat. Morbi est. Ut ac sem.';
    	$greekingList[] = 'Mauris rhoncus mauris in orci. Donec vehicula orci quis libero. Vestibulum viverra. Maecenas dapibus tempus massa. Donec fermentum libero ut nulla. Nullam diam risus, tincidunt ornare, gravida sed, mattis in, lorem. Vestibulum hendrerit mattis felis. Nunc dapibus, erat vitae consectetuer ultricies, metus augue dignissim wisi, at scelerisque tortor elit et mauris. Nullam consectetuer pulvinar massa. Donec neque. Nunc ac tellus ac purus adipiscing lobortis. Praesent ut mi. Fusce odio est, ultricies eget, aliquet eget, aliquet et, quam. Suspendisse pretium velit non est. Nam vel nisl. Integer congue ullamcorper velit.';
    	$greekingList[] = 'Nulla faucibus, arcu in varius pellentesque, lacus odio dapibus eros, at rutrum justo mi ut lacus. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Ut congue fermentum ipsum. Fusce nec orci et velit elementum consequat. Aliquam rutrum semper dolor. Praesent augue dolor, consequat vitae, rhoncus eu, posuere id, nulla. Mauris tempus justo ut augue. Vestibulum neque mi, commodo a, ornare sed, tempor non, arcu. Suspendisse malesuada dolor a nulla. Nulla facilisi. In rutrum risus vitae odio. Duis nec turpis ac mi viverra posuere. Nulla quis ligula id elit euismod faucibus.';
    	$greekingList[] = 'Nam ac nisl. Suspendisse mattis neque ut mauris vehicula laoreet. Pellentesque vehicula neque eget sapien. Mauris tincidunt semper nibh. Donec consectetuer sollicitudin lectus. Praesent interdum diam eget dolor. Curabitur sodales velit eu mauris semper facilisis. Fusce tincidunt sem quis mauris. Pellentesque ultricies enim in lorem viverra posuere. Morbi non ipsum at massa pellentesque ornare. Mauris pellentesque. Phasellus massa nulla, tempor lobortis, tempor ut, tempus eget, eros. Cras elit. Sed feugiat. Maecenas tempor ipsum eget lorem commodo ullamcorper. Praesent ante orci, viverra a, luctus quis, convallis quis, leo. Lorem ipsum dolor sit amet, consectetuer adipiscing elit. In vitae nunc. Nulla tincidunt consequat augue.';
    	$greekingList[] = 'Pellentesque molestie, nulla suscipit iaculis tristique, lorem nisl commodo neque, a volutpat orci odio sit amet sem. Aliquam ac sem nec turpis suscipit elementum. Integer posuere lacus ac odio convallis ultricies. Pellentesque metus urna, pharetra at, bibendum quis, fringilla eu, odio. Aliquam dapibus pellentesque tellus. Maecenas wisi ipsum, imperdiet id, venenatis id, posuere non, elit. Donec arcu turpis, vehicula quis, lobortis vel, pulvinar sit amet, mauris. Donec semper ante et augue. Nunc vestibulum venenatis dolor. Nulla et erat. Aenean at arcu vel ipsum semper faucibus.';
    	$greekingList[] = 'Morbi quis risus. Phasellus metus. Integer ac lorem. Morbi nec ligula sit amet ipsum aliquam mattis. Ut nec ante. Morbi hendrerit. Morbi aliquet orci eget lorem. Integer pretium gravida diam. Maecenas tempus. Ut fermentum ipsum vitae nibh. Phasellus sapien orci, iaculis nec, lobortis et, facilisis eget, leo. In sodales. Phasellus massa. Suspendisse lobortis, nibh id pellentesque scelerisque, nunc enim viverra elit, et varius tellus dui sit amet eros.';
    	$greekingList[] = 'Vivamus tincidunt augue aliquam nibh. Nulla cursus, enim vitae pellentesque auctor, quam velit nonummy nisl, euismod volutpat lectus arcu id elit. Vivamus id ligula. In interdum consectetuer libero. Quisque vulputate. Vestibulum interdum tincidunt purus. Fusce vel nisl. Sed egestas scelerisque lorem. Ut ut leo id leo suscipit tempus. Proin neque. Ut lacinia ornare ligula. Donec volutpat, mauris nec tempus convallis, erat eros placerat neque, eget ornare lorem nunc at orci. Curabitur ut elit ut augue dignissim sodales. Fusce ullamcorper. Morbi vulputate odio. Suspendisse imperdiet interdum lacus. Curabitur posuere.';
    	$greekingList[] = 'In elementum. Quisque consectetuer vehicula massa. Nam nunc magna, rutrum eu, porta ac, egestas a, arcu. Proin magna tortor, bibendum sit amet, iaculis sit amet, vulputate id, elit. Phasellus quis felis et lacus scelerisque pulvinar. Donec vestibulum nulla vel lacus. Integer mi. Vivamus elementum fermentum odio. Duis quis arcu non velit feugiat mollis. Nam vulputate erat ut pede. Nulla facilisi.';
    	$greekingList[] = 'Mauris quam sapien, aliquam sit amet, pharetra in, venenatis at, lorem. Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Pellentesque vitae libero. Suspendisse quis lorem. Nulla dolor lacus, dignissim eget, dignissim at, porttitor vel, est. Aliquam urna. Mauris pulvinar sagittis lacus. Donec quis risus ac nulla molestie mollis. Proin tempus commodo nibh. Curabitur a risus eget magna aliquam tincidunt. Quisque sit amet augue eu eros ultricies cursus. Mauris adipiscing interdum sapien. Nunc mollis sapien sit amet eros. Duis porttitor. Aliquam velit risus, elementum ac, ultricies id, auctor vitae, leo. Quisque tincidunt, erat id tempor consectetuer, est wisi varius nibh, quis placerat lectus arcu nec wisi. Nulla egestas, massa id adipiscing pellentesque, sapien orci fringilla ante, sit amet vehicula orci erat eu tellus.';
    	$greekingList[] = 'Sed enim lorem, placerat quis, facilisis at, luctus in, lacus. Nam volutpat risus a velit. Curabitur et arcu nec metus interdum vehicula. Duis velit augue, sodales vitae, facilisis eu, porttitor ac, ligula. Quisque lacus. In hac habitasse platea dictumst. Pellentesque ac nisl nec mauris suscipit vehicula. Donec vel massa nec nulla consectetuer semper. Aenean sed nulla. In rutrum. Nam iaculis tortor et wisi. Aliquam erat volutpat. Aenean dignissim. Nunc feugiat, urna sed congue dignissim, ante sem egestas neque, commodo gravida ante ligula ac mauris. Sed dictum hendrerit odio.';
    	$greekingList[] = 'Nullam ornare, ligula sed fringilla lacinia, nisl justo sodales augue, at commodo lorem nibh a mauris. Quisque accumsan vulputate augue. Maecenas sem justo, tincidunt ultrices, porta in, dignissim sit amet, lacus. Pellentesque dapibus ligula a magna. Suspendisse nulla. Etiam porta odio sed pede. Vivamus consequat metus eget erat. Nulla tempus quam at lorem. Praesent ultricies ligula ullamcorper nisl. Ut condimentum lectus quis tellus. Nam ac justo non lectus ullamcorper dictum. Nunc blandit libero non odio. Curabitur posuere. Etiam aliquet laoreet erat.';
    	$greekingList[] = 'Phasellus auctor porta est. Vestibulum ultrices sem sed lectus. Suspendisse rutrum mauris sit amet nibh. Cras tempus varius dolor. Mauris justo eros, consequat a, luctus at, vulputate non, nulla. Phasellus facilisis vestibulum erat. Donec enim metus, mattis vitae, commodo non, tristique vel, diam. Nunc dignissim nunc sit amet purus. Pellentesque ullamcorper, arcu nec imperdiet elementum, libero mauris aliquam mi, quis consectetuer erat diam vitae urna. Fusce non elit at nunc laoreet commodo. Sed convallis, leo vitae fermentum dictum, leo ligula condimentum enim, id convallis dui justo et turpis. Etiam sem. Sed rhoncus consequat metus. Integer nunc diam, gravida ac, aliquet vel, posuere eu, sapien. Quisque ac neque. Phasellus vehicula rutrum nibh. Vestibulum rutrum, ante vel cursus feugiat, ligula sapien volutpat orci, vitae aliquam ligula ipsum quis ligula. Phasellus fringilla, augue id lacinia placerat, lacus ipsum venenatis elit, at pulvinar eros turpis vitae mauris.';
    	$greekingList[] = 'Suspendisse quis elit. Aenean sodales. Ut scelerisque congue mi. Nulla facilisi. Curabitur auctor, risus ac auctor sodales, est diam sagittis justo, nec venenatis sapien velit a wisi. Sed purus lacus, euismod at, consectetuer id, gravida et, libero. Aenean vitae pede non ligula tincidunt porta. Aliquam tempor, nibh at interdum scelerisque, lectus massa viverra massa, non elementum wisi sem eget nibh. Donec vitae dolor. Pellentesque scelerisque.';
    	$greekingList[] = 'Nulla facilisi. Ut aliquam malesuada metus. Integer eget erat non dui lobortis posuere. Integer porttitor, ante et dapibus auctor, tellus ante ultricies wisi, eu ullamcorper augue lectus ut nibh. Proin tortor odio, dignissim eget, imperdiet et, molestie malesuada, mauris. Phasellus pellentesque semper urna. Etiam pellentesque adipiscing nibh. Vivamus imperdiet sollicitudin pede. Praesent quis mi a sem tristique bibendum. In hac habitasse platea dictumst. Cras dictum, mauris nec aliquam pharetra, purus tortor nonummy orci, sed porta lectus nunc vel eros.';
    	$greekingList[] = 'Aliquam in leo sed ante tempor bibendum. In tempor sem sed odio. Fusce viverra, massa ac posuere adipiscing, neque leo vehicula urna, nec adipiscing lacus libero sed sapien. Mauris pharetra, est ac euismod congue, mauris nulla venenatis sapien, eu accumsan neque metus vel erat. Integer at libero. Nullam a turpis. Phasellus dapibus velit vehicula lorem. Nam molestie pretium odio. Quisque enim. Aliquam et sapien. Mauris a justo. Nullam leo. Vivamus sagittis elit in pede. Ut vitae massa id nisl suscipit accumsan. Quisque at erat.';
    	$greekingList[] = 'Claroline is an Open Source software based on PHP/MySQL.  '
        		        . 'It\'s a collaborative learning environment allowing teachers  '
        		        . 'or education institutions to create and administer courses through the web.';
        $greekingList[] = 'The system provides group management, forums,  '
        		        . 'document repositories, calendar, chat, assignment areas,  '
        		        . 'links, user profile administration on a single  '
        		        . 'and highly integrated package.';
        $greekingList[] = 'Claroline is translated in 28 languages and used by hundreds  '
        		        . 'of institutions around world.The software was initially  '
        		        . 'started by the University of Louvain (Belgium)  '
        		        . 'and released under Open Source licence (GPL).  '
        		        . 'Since then, a comunity of developper around the world  '
        		        . 'contributes to its development. Downloading and using  '
        		        . 'Claroline is completly free of charge.';
        $greekingList[] = 'A book on Claroline. Marcel Lebrun has just published  '
        		        . '"eLearning pour enseigner et apprendre"  '
        		        . '(eLearning, for Teaching and Learning).'
                         .'Based on the experience of Claroline at the  '
        		        . 'University of Louvain (Belgium), it treats in a  '
        		        . 'positive way how to elaborate pedagogical devices  '
        		        . 'both adapted to these new technological tools and  '
        		        . 'devoted to promote learning.';        		        
        $greekingList[] = 'Claroline 1.6 Release Candidate available. '
                        . 'Thanks to the Claroline community and a huge  '
        		        . 'debugging campaign, Claroline 1.6 RC is now available. '
        		        . 'It should be the last release before the '
        		        . 'stable version of Claroline 1.6. '
        		        . 'Now, focus will be on the upgrade script as no further '
        		        . 'change would be planned to the new database '
        		        . 'structure of Claroline.';
        		        
        $greekingList[] = 'Li Europan lingues es membres del sam familie.  '
        		        . 'Lor separat existentie es un myth.  '
        		        . 'Por scientie, musica, sport etc.,  '
        		        . 'li tot Europa usa li sam vocabularium.  '
        		        . 'Li lingues differe solmen in li grammatica,  '
        		        . 'li pronunciation e li plu commun vocabules.  '
        		        . 'Omnicos directe al desirabilitá de un nov lingua franca:  '
        		        . 'on refusa continuar payar custosi traductores.  '
        		        . 'It solmen va esser necessi far uniform grammatica,  '
        		        . 'pronunciation e plu sommun paroles.';
        		        
        $greekingList[] = 'Ma quande lingues coalesce, li grammatica del resultant  '
        		        . 'lingue es plu simplic e regulari quam ti del  '
        		        . 'coalescent lingues. Li nov lingua franca va  '
        		        . 'esser plu simplic e regulari quam li existent Europan lingues.  '
        		        . 'It va esser tam simplic quam Occidental:  '
        		        . 'in fact, it va esser Occidental.  '
        		        . 'A un Angleso it va semblar un simplificat Angles,  '
        		        . 'quam un skeptic Cambridge amico dit me que Occidental es.';
        		        
        $greekingList[] = 'Epsum factorial non deposit quid pro quo hic escorol.  '
        		        . 'Olypian quarrels et gorilla congolium sic ad nauseum.  '
        		        . 'Souvlaki ignitus carborundum e pluribus unum.  '
        		        . 'Defacto lingo est igpay atinlay.  '
        		        . 'Marquee selectus non provisio incongruous feline nolo contendre.  '
        		        . 'Gratuitous octopus niacin, sodium glutimate.  '
        		        . 'Quote meon an estimate et non interruptus stadium.  '
        		        . 'Sic tempus fugit esperanto hiccup estrogen.  '
        		        . 'Glorious baklava ex librus hup hey ad infinitum.  '
        		        . 'Non sequitur condominium facile et geranium incognito.  '
        		        . 'Epsum factorial non deposit quid pro quo hic escorol.  '
        		        . 'Marquee selectus non provisio incongruous feline nolo contendre  '
        		        . 'Olypian quarrels et gorilla congolium sic ad nauseum.  '
        		        . 'Souvlaki ignitus carborundum e pluribus unum.';
        $greekingList[] = 'The PEAR::Auth package provides methods for creating  '
        		        . 'an authentication system using PHP. '
                        . 'Currently it supports the following storage containers  '
        		        . 'to read/write the login data:  '
        		        . 'All databases supported by the PEAR database layer,  '
        		        . 'All databases supported by the MDB database layer,  '
        		        . 'All databases supported by the MDB2 database layer,  '
        		        . 'Plaintext files, LDAP servers, POP3 servers,  '
        		        . 'IMAP servers, vpopmail accounts, RADIUS,  '
        		        . 'SAMBA password files, SOAP';
        $greekingList[] = 'African Sanctus (originally known as African Revelations)  '
        		        . 'was first performed by the Saltarello Choir in July 1972  '
        		        . 'at St. John\'s Smith Square, London, and later broadcast  '
        		        . 'on BBC Radio on United Nations Day.  '
        		        . 'In 1974, BBC Television\'s *Omnibus* made a documentary  '
        		        . 'film of African Sanctus on location in North and East Africa.  '
        		        . 'This film, directed by Herbert Chappell,  '
        		        . 'nominated for the *Prix Italia*, was first screened on  '
        		        . 'Easter Day, 1975 and coincided with the release of the original  '
        		        . 'Philips recording. The score was first published in 1977  '
        		        . 'and premiere performances were given in Toronto, at  '
        		        . 'The Three Choirs Festival, Worcester Cathedral in 1978,  '
        		        . 'followed by the Royal Albert Hall in 1979,  '
        		        . 'conducted by Sir David Willcocks.';
        $greekingList[] = 'La récurrence transfinie, appelée aussi sous  '
        		        . 'l\'influence anglaise induction transfinie,  '
        		        . 'permet de construire des objets et de démontrer des théorèmes ;  '
        		        . 'elle généralise la récurrence ordinaire sur N  '
        		        . 'en considérant des familles indexées par un ordinal  '
        		        . 'infini quelconque au lieu de se borner au plus petit qu\'est N.  '
        		        . 'Une fois un peu compris ce qu\'est un ordinal,  '
        		        . 'on dispose là d\'un outil très commode pour faire  '
        		        . 'des constructions conformes à l\'intuition et on dispose  '
        		        . 'de renseignements précis pour une étude approfondie  '
        		        . '(ce que ne permet pas le lemme de Zorn, qui a été introduit  '
        		        . 'pour éviter l\'usage des ordinaux transfinis).';
        $greekingList[] = 'Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur?';
        $greekingList[] = 'But I must explain to you how all this mistaken idea of denouncing pleasure and praising pain was born and I will give you a complete account of the system, and expound the actual teachings of the great explorer of the truth, the master-builder of human happiness. No one rejects, dislikes, or avoids pleasure itself, because it is pleasure, but because those who do not know how to pursue pleasure rationally encounter consequences that are extremely painful. Nor again is there anyone who loves or pursues or desires to obtain pain of itself, because it is pain, but because occasionally circumstances occur in which toil and pain can procure him some great pleasure. To take a trivial example, which of us ever undertakes laborious physical exercise, except to obtain some advantage from it? But who has any right to find fault with a man who chooses to enjoy a pleasure that has no annoying consequences, or one who avoids a pain that produces no resultant pleasure?';
        //$greekingList[] = '';
        //$greekingList[] = '';
        //$greekingList[] = '';
        $greeking = $greekingList[rand(0,(sizeof($greekingList)-1))];
        $errorMsg = 'You need to supply attributes for "units" (legal values are "characters", "words", "sentences" or "paragraphs") and a positive integer, "length".<br /><br />Usage Example:<br />&nbsp;&nbsp;&nbsp;&nbsp;print(<strong>greek(\'paragraphs\', 3)</strong>);';

		if (!isset($units) || !isset($length) || ($length < 1))
		{
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
				for ($ctrParagraph = 1; $ctrParagraph <= $length; $ctrParagraph++)
				{
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

	return trim($output,' .');
	}//end function greek()


?>
