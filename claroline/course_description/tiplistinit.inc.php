<?php // $Id$
if ( ! defined('CLARO_INCLUDE_ALLOWED') ) die('---');


unset($titreBloc);
unset($titreBlocNotEditable);
unset($questionPlan);
unset($info2Say);

$titreBloc           [] = $langDescription;
$titreBlocNotEditable[] = false;
$questionPlan        [] = $langDescriptionComment1;
$info2Say            [] = $langDescriptionComment2;

$titreBloc           [] = $langQualificationsAndGoals;
$titreBlocNotEditable[] = true;
$questionPlan        [] = $langQualificationsAndGoalsComment1;
$info2Say            [] = $langQualificationsAndGoalsComment2;

$titreBloc           [] = $langCourseContent;
$titreBlocNotEditable[] = true;
$questionPlan        [] = $langCourseContentComment1;
$info2Say            [] = $langCourseContentComment2;

$titreBloc           [] = $langTeachingTrainingActivities;
$titreBlocNotEditable[] = true;
$questionPlan        [] = $langTeachingTrainingActivitiesComment1;
$info2Say            [] = $langTeachingTrainingActivitiesComment2;

$titreBloc           [] = $langSupports;
$titreBlocNotEditable[] = true;
$questionPlan        [] = $langSupportsComment1;
$info2Say            [] = $langSupportsComment2;

$titreBloc           [] = $langHumanAndPhysicalRessources;
$titreBlocNotEditable[] = true;
$questionPlan        [] = $langHumanAndPhysicalResourcesComment1;
$info2Say            [] = $langHumanAndPhysicalResourcesComment2;

$titreBloc           [] = $langMethodsOfEvaluation;
$titreBlocNotEditable[] = true;
$questionPlan        [] = $langMethodsOfEvaluationComment1;
$info2Say            [] = $langMethodsOfEvaluationComment1;

?>