<?php // $Id$
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.5.* $Revision: 
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2004 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      |   English Translation                                                |
      +----------------------------------------------------------------------+
      |   This program is free software; you can redistribute it and/or      |
      |   modify it under the terms of the GNU General Public License        |
      |   as published by the Free Software Foundation; either version 2     |
      |   of the License, or (at your option) any later version.             |
      +----------------------------------------------------------------------+

      +----------------------------------------------------------------------+
      | Translator :                                                         |
      |          Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
      |          Andrew Lynn       <Andrew.Lynn@strath.ac.uk>                |
      |          Olivier Brouckaert <oli.brouckaert@skynet.be>               |
      +----------------------------------------------------------------------+
*/

$iso639_2_code = "en";
$iso639_1_code = "eng";

$langNameOfLang['arabic'		]="arabian";
$langNameOfLang['brazilian'		]="brazilian";
$langNameOfLang['bulgarian'		]="bulgarian";
$langNameOfLang['croatian'		]="croatian";
$langNameOfLang['dutch'			]="dutch";
$langNameOfLang['english'		]="english";
$langNameOfLang['finnish'		]="finnish";
$langNameOfLang['french'		]="french";
$langNameOfLang['german'		]="german";
$langNameOfLang['greek'			]="greek";
$langNameOfLang['italian'		]="italian";
$langNameOfLang['japanese'		]="japanese";
$langNameOfLang['polish'		]="polish";
$langNameOfLang['simpl_chinese'	]="simplified chinese";
$langNameOfLang['spanish'		]="spanish";
$langNameOfLang['swedish'		]="swedish";
$langNameOfLang['thai'			]="thai";
$langNameOfLang['turkish'		]="turkish";

$charset = 'iso-8859-1';
$text_dir = 'ltr'; // ('ltr' for left to right, 'rtl' for right to left)
$left_font_family = 'verdana, helvetica, arial, geneva, sans-serif';
$right_font_family = 'helvetica, arial, geneva, sans-serif';
$number_thousands_separator = ',';
$number_decimal_separator = '.';
$byteUnits = array('Bytes', 'KB', 'MB', 'GB');

$langDay_of_weekNames['init'] = array('S', 'M', 'T', 'W', 'T', 'F', 'S');
$langDay_of_weekNames['short'] = array('Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat');
$langDay_of_weekNames['long'] = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');

$langMonthNames['init']  = array('J', 'F', 'M', 'A', 'M', 'J', 'J', 'A', 'S', 'O', 'N', 'D');
$langMonthNames['short'] = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
$langMonthNames['long'] = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');

// Voir http://www.php.net/manual/en/function.strftime.php pour la variable
// ci-dessous

$dateFormatShort =  "%b %d, %y";
$dateFormatLong  = '%A %B %d, %Y';
$dateTimeFormatLong  = '%B %d, %Y at %I:%M %p';
$timeNoSecFormat = '%I:%M %p';

// GENERIC

$langYes="Yes";
$langNo="No";
$langBack="Back";
$langNext="Next";
$langAllowed="Allowed";
$langDenied="Denied";
$langBackHome="Back to home";
$langPropositions="Proposals for an improvement of";
$langMaj="Update";
$langModify="Modify";
$langDelete="Delete";
$langMove="Move";
$langTitle="Title";
$langHelp="Help";
$langOk="Ok";
$langAdd="Add";
$langAddIntro="Add introduction text";
$langBackList="Return to the list";
$langText="Text";
$langEmpty="Empty";
$langConfirmYourChoice="Please confirm your choice";
$langAnd="and";
$langChoice="Your choice";
$langFinish="Finish";
$langCancel="Cancel";
$langNotAllowed="You are not allowed here";
$langManager="Manager";
$lang_footer_CourseManager = "Course Manager(s)";
$langPlatform="Powered by";
$langOptional="Optional";
$langNextPage="Next page";
$langPreviousPage="Previous page";
$langUse="Use";
$langTotal="Total";
$langTake="take";
$langOne="One";
$langSeveral="Several";
$langNotice="Notice";
$langDate="Date";
$langAmong="among";

// banner

$langMyCourses="My course list";
$langModifyProfile="Modify my profile";
$langMyAgenda = "My agenda";
$langLogout="Logout";


//needed for student view
$langCourseManagerview = "Course Manager View";
$langStudentView = "Student View";






$lang_this_course_is_protected = 'This course is protected';
$lang_enter_your_user_name_and_password = 'Enter your user name and password';
$lang_if_you_dont_have_a_user_account_profile_on = 'If you don\'t have a user account on';
$lang_click_here = 'click here';
$lang_your_user_profile_doesnt_seem_to_be_enrolled_to_this_course = "You're user profile doesn't seem to be enrolled to this course";
$lang_if_you_wish_to_enroll_to_this_course = "If you wish to enroll to this course";
$lang_username = "User Name";
$lang_password = "Password";



// TOOLNAMES
$langCourseHome = "Course Home";
$langAgenda = "Agenda";
$langLink="Links";
$langDocument="Documents and Links";
$langWork="Assignments";
$langAnnouncement="Announcements";
$langUser="Users";
$langForum="Forums";
$langExercise="Exercises";
$langGroups ="Groups";
$langChat ="Chat";
$langLearnPath="Learning Path";
$langDescriptionCours  = "Course description";
$langPlatformAdministration = "Platform Administration";

?>