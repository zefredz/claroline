<?php # $Id$
//// config  this tools
$toolConf['config_code']='CLADMUSR';
$toolConf['config_file']='admin.usermanagement.conf.inc.php';
$toolConf['section']['create']['label']='Creation properties';
$toolConf['section']['create']['properties'] = 
array ( 'defaultVisibilityForANewCourse'
      , 'HUMAN_CODE_NEEDED'
      , 'HUMAN_LABEL_NEEDED'
      , 'COURSE_EMAIL_NEEDED'
      , 'prefixAntiNumber'
      , 'prefixAntiEmpty');


unset($stepUser) ; //  null or unset  to dislabing pagingation
unset($stepCourse) ; //  null or unset  to dislabing pagingation

$stepUser 		= 20;
$stepCourse 	= 20;

//uncomment to force but so think  to  comment  link in bottom of page
$display1colPerCourse		= TRUE; // TRUE -> synopsis | FALSE -> List
//$listAllCourses4EachUser 	= FALSE;

$showCheckBox 		= TRUE;
$editSubscription	= TRUE;
$editStatut			= FALSE;
$editTutor			= FALSE;
$showIfMember		= FALSE;
$showStatut			= FALSE;

$showRemoveOfClarolineButton	= TRUE;
$showLockUnlockUserButton		= FALSE;
$showUpdateSubscriptionButton	= TRUE;

$urlUnderCoursesNamesBefore = "../../../";
$urlUnderCoursesNamesAfter = "/";

$dirTextInHeaders= "H"; // "V" or "H"
?>
