<?php # $Id$
//// config  this tools
$toolConf['label']='CLADMUSR';

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
$display1colPerCourse		= True; // true -> synopsis | False -> List
//$listAllCourses4EachUser 	= false;

$showCheckBox 		= true;
$editSubscription	= true;
$editStatut			= false;
$editTutor			= false;
$showIfMember		= false;
$showStatut			= false;

$showRemoveOfClarolineButton	= true;
$showLockUnlockUserButton		= false;
$showUpdateSubscriptionButton	= true;

$urlUnderCoursesNamesBefore = "../../../";
$urlUnderCoursesNamesAfter = "/";

$dirTextInHeaders= "H"; // "V" or "H"
?>
