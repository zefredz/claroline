<?php // $Id$
/*
  +----------------------------------------------------------------------+
  | CLAROLINE version 1.5.*
  +----------------------------------------------------------------------+
  | Copyright (c) 2001, 2004 Universite catholique de Louvain (UCL)      |
  +----------------------------------------------------------------------+
  | This source file is subject to the GENERAL PUBLIC LICENSE,           |
  | available through the world-wide-web at                              |
  | http://www.gnu.org/copyleft/gpl.html                                 |
  +----------------------------------------------------------------------+
  | Authors: Piraux Sébastien <pir@cerdecam.be>                          |
  |          Lederer Guillaume <led@cerdecam.be>                         |
  +----------------------------------------------------------------------+
  
*/
$langFile = "learnPath";
require '../../inc/claro_init_global.inc.php'; 

include($includePath."/lib/learnPath.lib.inc.php");

$TABLELEARNPATH         = $_course['dbNameGlu']."lp_learnPath";
$TABLEMODULE            = $_course['dbNameGlu']."lp_module";
$TABLELEARNPATHMODULE   = $_course['dbNameGlu']."lp_rel_learnPath_module";
$TABLEASSET             = $_course['dbNameGlu']."lp_asset";
$TABLEUSERMODULEPROGRESS= $_course['dbNameGlu']."lp_user_module_progress";

$TABLEUSERS                    = $mainDbName."`.`user";


$TOCurl = $clarolineRepositoryWeb."learnPath/navigation/tableOfContent.php"; 

/*********************/
/* HANDLING API FORM */
/*********************/

// handling of the API form if posted by the SCORM API
if($_POST['ump_id']) 
{
  // set values for some vars because we are not sure we will change it later
  $lesson_status_value = strtoupper($_POST['lesson_status']);
  $credit_value = strtoupper($_POST['credit']);
  
  // next visit of the sco will not be the first so entry must be setted to RESUME
  $entry_value = "RESUME"; 
  
  // Set lesson status to COMPLETED if the SCO didn't change it itself.
  if ( $lesson_status_value == "NOT ATTEMPTED" )
      $lesson_status_value = "COMPLETED";

  // set credit if needed
  if ( $lesson_status_value == "COMPLETED" || $lesson_status_value == "PASSED")
  {
      if ( strtoupper($_POST['credit']) == "CREDIT" )
        $credit_value = "CREDIT";
  }

  if(isScormTime($_POST['session_time']))
  {
    $total_time_value = addScormTime($_POST['total_time'], $_POST['session_time']);
  }
  else
  {
    $total_time_value = $_POST['total_time'];
  }
  
  $sql = "UPDATE `".$TABLEUSERMODULEPROGRESS."` 
            SET 
                `lesson_location` = '".$_POST['lesson_location']."',
                `lesson_status` = '".$lesson_status_value."',
                `entry` = '".$entry_value."',
                `raw` = '".$_POST['raw']."',
                `scoreMin` = '".$_POST['scoreMin']."',
                `scoreMax` = '".$_POST['scoreMax']."',
                `total_time` = '".$total_time_value."',
                `session_time` = '".$_POST['session_time']."',
                `suspend_data` = '".$_POST['suspend_data']."',
                `credit` = '".$credit_value."'
          WHERE `user_module_progress_id` = ".$_POST['ump_id'];
  claro_sql_query($sql);
}

// display the form to accept new commit and
// refresh TOC frame, has to be done here to show recorded progression as soon as it is recorded
            
?>

<!-- API form -->
<html>
<head>
   <title>update progression</title>
<?php
if($_POST['ump_id']) 
{
?>
    <script language="javascript">
    <!--//
      parent.tocFrame.location.href="<?php echo $TOCurl; ?>";
    //--> 
    </script>
<?php
}
?>
</head>
<body>
   <form name="cmiForm" method="POST" action="<?php echo $_SERVER["PHP_SELF"] ?>"> 
	<input type="hidden" name="ump_id" />
	<input type="hidden" name="lesson_status" />
	<input type="hidden" name="lesson_location" />
    <input type="hidden" name="credit" />
	<input type="hidden" name="entry" />
	<input type="hidden" name="raw" />
    <input type="hidden" name="total_time" />
	<input type="hidden" name="session_time" />
	<input type="hidden" name="suspend_data" />
	<input type="hidden" name="scoreMin" />
	<input type="hidden" name="scoreMax" />
   </form>
</body>
</html>
