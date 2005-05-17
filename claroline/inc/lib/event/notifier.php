<?php
/**
 *  Class which update the main database with the date of the latest new item in each tool of each course.
 */
class Notifier extends EventDriven {
     
    /**
     * constructor
     */
    function Notifier ( & $registry )
    {
        parent::EventDriven( $registry );
    }
	
    /**
    * implements the parent update function
    */
     
    function update ($event) { 
	
	// get needed info from event
	
	$event_args = $event->getArgs();
	
	$course     = $event_args['cid'];
	$tool       = $event_args['tid'];
	$ressource  = $event_args['rid'];
	$gid        = $event_args['gid'];
	$uid        = $event_args['uid'];
	$eventType  = $event->getEventType();

	// call function to update db info
	
	if ($eventType != "DELETE")
	{    
            $this->update_last_event($course, $tool, $ressource,$gid, $uid);
	}    
    }    
	    
    /**
     * Function used to tell the notifier that some new event happened in a tool :
     * For a specific tool in a specific course, 
     * the field 'date' of the latest new event, is set to the current date.
     * Event might concern a specific group (optionnal)
     * Event might concern a specific user (optionnal)
     * 
     * @return void 
     */
     
    function update_last_event($course_id,$tool_id, $ressource_id, $gid, $uid) {
        
        global $tbl_notify;
	$tbl_notify = "notify";
		
	// 1- check if row already exists
	
	$sql = "SELECT * 
	          FROM `".$tbl_notify."` 
		 WHERE `course_code`='".$course_id."'
		   AND `tool_id`='".$tool_id."'
		   AND `ressource_id`='".$ressource_id."'
		   AND `group_id` = '".$gid."'
		   AND `user_id` = '".$uid."'
		   ";
	
	$notificationTable = claro_sql_query_fetch_all($sql);
	$notification = $notificationTable[0];
	
	if ($notification!=null)
	{
	  $do_update = true;
	}
	else
	{
	  $do_update = false;
	}
	
	claro_sql_query($sql);
	
	// 2- update or create for concerned row
	
	if ($do_update) {
		
	    $sql = "UPDATE `".$tbl_notify."` 
	               SET `date`= NOW() 
		     WHERE `course_code`='".$course_id."' 
		       AND `tool_id`='".$tool_id."'
		       AND `ressource_id`='".$ressource_id."'
		       AND `group_id` = '".$gid."'
		       AND `user_id` = '".$uid."'
		           ";
	    claro_sql_query($sql);
        }
	else
	{
	    
            $sql = "INSERT INTO `".$tbl_notify."` 
                    SET   `course_code`   = '".$course_id."',
                          `tool_id` = '".$tool_id."',
			  `date` = NOW(),
			  `ressource_id` = '".$ressource_id."',
			  `group_id` = '".$gid."',
			  `user_id` = '".$uid."'
			  ";
	    claro_sql_query($sql);
	}
	
	//echo "updating in db for last event : ".$course_id." ".$tool_id." : <br>";
	
	// echo $sql."<br>"; debug	
    
    }
     
    /**
     *  Function to know which course of a user
     *  $date must be given in unix_timestamp
     * 
     *  @return an array with the courses with recent unknown event until the date '$date' in the course list of the user
     */
     
    function get_notified_courses($user_id,$date) {
    
	$tbl_cours_user = "cl_cours_user";
        $tbl_notify                = "notify";
	
	$courses = array();
	
	//1- find the list of the user's course and in this list, take only the course where recent events happened
	
	$sql="SELECT `code_cours` FROM `".$tbl_cours_user."` AS CU, `".$tbl_notify."` AS N 
	       WHERE CU.`code_cours` = N.`course_code`
	         AND CU.`user_id` = '".$user_id."'
		 AND UNIX_TIMESTAMP(N.`date`) > '".$date."'
	         ";
		
	$result = claro_sql_query_fetch_all($sql);
	
	foreach ($result as $course)
	{
	    $courses[] = $course["code_cours"];
	} 
	
	//2- return an array with the courses with recent unknow event until the date '$date' in the course list of the user
	
	return $courses;
    }
    
    /**
     *  Function to know which course of a user
     * 
     *  @return an array with the tools id with recent unknow event until the date '$date'
     */
     
    function get_notified_tools($course_id, $date) {
        
	global $tbl_notify;
	$tbl_notify = "notify";
	
	$tools = array();
	
	// 1- find the tool list of the given course that contains some event newer than the date '$date'
	
	$sql="SELECT `tool_id`, MAX(`date`)
	        FROM `".$tbl_notify."` AS N 
	       WHERE N.`course_code` = '".$course_id."'
		 AND UNIX_TIMESTAMP(N.`date`) > '".$date."'
	       GROUP BY `tool_id`
		 
	         ";
	$result = claro_sql_query_fetch_all($sql);
	
	foreach ($result as $tool)
	{
	    $tools[] = $tool["tool_id"];
	} 
	
	// 2- return an array with the tools id with recent unknow event until the date '$date'
	
	return $tools;
    
    }    
}

?>