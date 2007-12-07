<?php

unset($titreBloc);
unset($titreBlocNotEditable);
unset($questionPlan);
unset($info2Say);

$titreBloc           [] = get_block('blockCourseDescriptionDescription');
$titreBlocNotEditable[] = false;
$questionPlan        [] = get_block('blockCourseDescriptionDescriptionComment1');
$info2Say            [] = get_block('blockCourseDescriptionDescriptionComment2');

$titreBloc           [] = get_block('blockCourseDescriptionQualificationsAndGoals');
$titreBlocNotEditable[] = true;
$questionPlan        [] = get_block('blockCourseDescriptionQualificationsAndGoalsComment1');
$info2Say            [] = get_block('blockCourseDescriptionQualificationsAndGoalsComment2');

$titreBloc           [] = get_block('blockCourseDescriptionCourseContent');
$titreBlocNotEditable[] = true;
$questionPlan        [] = get_block('blockCourseDescriptionCourseContentComment1');
$info2Say            [] = get_block('blockCourseDescriptionCourseContentComment2');

$titreBloc           [] = get_block('blockCourseDescriptionTeachingTrainingActivities');
$titreBlocNotEditable[] = true;
$questionPlan        [] = get_block('blockCourseDescriptionTeachingTrainingActivitiesComment1');
$info2Say            [] = get_block('blockCourseDescriptionTeachingTrainingActivitiesComment2');

$titreBloc           [] = get_block('blockCourseDescriptionSupports');
$titreBlocNotEditable[] = true;
$questionPlan        [] = get_block('blockCourseDescriptionSupportsComment1');
$info2Say            [] = get_block('blockCourseDescriptionSupportsComment2');

$titreBloc           [] = get_block('blockCourseDescriptionHumanAndPhysicalRessources');
$titreBlocNotEditable[] = true;
$questionPlan        [] = get_block('blockCourseDescriptionHumanAndPhysicalResourcesComment1');
$info2Say            [] = get_block('blockCourseDescriptionHumanAndPhysicalResourcesComment2');

$titreBloc           [] = get_block('blockCourseDescriptionMethodsOfEvaluation');
$titreBlocNotEditable[] = true;
$questionPlan        [] = get_block('blockCourseDescriptionMethodsOfEvaluationComment1');
$info2Say            [] = get_block('blockCourseDescriptionMethodsOfEvaluationComment1');

?>