<?php # $Id$
//----------------------------------------------------------------------
// CLAROLINE
//----------------------------------------------------------------------
// Copyright (c) 2001-2003 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see 'credits' file
//----------------------------------------------------------------------
/*
      +----------------------------------------------------------------------+
      | Translator :                                                         |
      |          Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
      |          Andrew Lynn       <Andrew.Lynn@strath.ac.uk>                |
      +----------------------------------------------------------------------+
 */

$langFirstname = "First name"; // by moosh
$langLastname = "Last name"; // by moosh
$langEmail = "Email";// by moosh
$langRetrieve ="Retrieve identification information";// by moosh
$langMailSentToAdmin = "A mail is sent to  administrator.";// by moosh
$langAccountNotExist = "Account not found.<BR>".$langMailSentToAdmin." They  would search manually.<BR>";// by moosh
$langAccountExist = "This account exist.<BR>".$langMailSentToAdmin."<BR>";// by moosh
$langWaitAMailOn = "A mail  can be sent to ";// by moosh
$langCaseSensitiveCaution = "System made difference between uppercase and lowercase letters";// by moosh
$langDataFromUser = "Data sent by user";// by moosh
$langDataFromDb = "Data in the database";// by moosh
$langLoginRequest = "Login request";// by moosh
$langTotalEntryFound = "Entry  found";// by moosh

// lost password
$langLostPassword = "Lost password";
$langExplainFormLostPass = "Enter data as you think you have input it while registering.";// by moosh
$langEmailNotSent = "The system is unable to send you an e-mail.<br>Please contact the  ";// by moosh
$langYourAccountParam = "This  is  your account Login-Pass";// by moosh
$langPasswordHasBeenEmailed = "Your password has been emailed to ";
$langEmailAddressNotFound = "There is no user account with this email address.";
$langEnterMail = "Enter your email so we can send you your password.";
$langPlatformAdmin = "Platform administrator";

$langTryWith ="Try with";// by moosh
$langInPlaceOf ="and  not with ";// by moosh
$langParamSentTo = "Identification information sent to ";// by moosh
$langAddVarUser="Enroll a list of users";

// REGISTRATION - AUTH - inscription.php
$langRegistration="Registration";
$langName="First Name";
$langSurname="Last Name";
$langUsername="Username";
$langPass="Password";
$langConfirmation="Confirmation";
$langEmail="Email";
$langStatus="Action";
$langRegStudent="Follow courses";
$langRegAdmin="Create course websites";
$langPhone = "Phone";
$langSaveChange ="Save changes";
$langRegister = "Register";

// inscription_second.php
$langPassTwice="You typed two different passwords";
$langEmptyFields="You left some required fields empty";
$langUserFree="This username is already taken";
$langYourReg="Your registration on";
$langDear="Dear";
$langYouAreReg="You are registered on";
$langSettings="with the following settings:\nUsername:";
$langAddress="The address of ";
$langIs="is";
$langProblem="In case of problems, contact us.";
$langFormula="Yours sincerely";
$langManager="Manager";
$langPersonalSettings="Your personal settings have been registered and an email has been sent to help you remember your username and password.</p>";

$langNowGoChooseYourCourses ="You can now select, in the list, the courses you want to access.";
$langNowGoCreateYourCourse  ="You can now create  your  course";

$langYourRegTo="Your are registered to";
$langIsReg="has been updated";
$langCanEnter="You can now <a href=../../index.php>enter the campus</a>";

// profile.php

$langModifProfile="Modify my profile";
$langPassTwo="You have typed two different passwords";
$langAgain="Try again!";
$langFields="You left some fields empty";
$langUserTaken="This username is already taken";
$langEmailWrong="The email address is not complete or contains some unvalid characters";
$langProfileReg="Your new profile has been saved";
$langHome="Back to Home Page";
$langMyStats = "View my statistics";
$langReturnSearchUser="Return to the user";

// user.php

$langUsers="Users";
$langModRight="Modify admin rights of";
$langNone="None";
$langAll="All";
$langNoAdmin="has now <b>NO admin rights on this site</b>";
$langAllAdmin="has now <b>ALL admin rights on this site</b>";
$langModRole="Modify the role of";
$langRole="Role";
$langIsNow="is now";
$langInC="in this course";
$langFilled="You have left some fields empty.";
$langUserNo="The username you choose ";
$langTaken="is already taken. Choose another one.";
$langOneResp="One of the course administrators";
$langRegYou="has registered you on this course";
$langTheU="The user";
$langAddedU="has been added. An email has been sent to give him his username ";
$langAndP="and his password";
$langDereg="has been unregistered from this course";
$langAddAU="Add a user";
$langStudent="student";
$langBegin="begin.";
$langPreced50 = "Previous 50";
$langFollow50 = "Next 50";
$langEnd = "end";
$langAdmR="Admin. rights";
$langUnreg = "Unregister";
$langAddHereSomeCourses = "<font size=2 face='Arial, Helvetica'><big>Modify courses list</big><br><br>
Check the courses you want to follow.<br>
Uncheck the ones you don't want to follow anymore.<br> Then click Ok at the bottom of the list";
$langTitular = "Author";
$langCanNotUnsubscribeYourSelf = "You can't unsubscribe yourself from a course that you admin, only another admin of the course can do it.";

$langGroup="Group";
$langUserNoneMasc="-";
$langTutor="Tutor";
$langTutorDefinition="Tutor (right to supervise groups)";
$langAdminDefinition="Admin (right to modify course website content)";
$langDeleteUserDefinition="Unregister (delete from users list of  <b>this</b> course)";
$langNoTutor = "is not tutor for this course";
$langYesTutor = "is tutor for this course";
$langUserRights="Users rights";
$langNow="now";
$langOneByOne="Add user manually";
$langUserMany="Import users list through textfile";
$langNo="no";
$langYes="yes";
$langUserAddExplanation="every line of file to send will necessarily an only
		include 5 fields: <b>Name&nbsp;&nbsp;&nbsp;Surname&nbsp;&nbsp;&nbsp;
		Login&nbsp;&nbsp;&nbsp;Password&nbsp;
		&nbsp;&nbsp;Email</b> separated by tabs and in this order.
		Users will receive email confirmation with login/password.";
$langSend="Send";
$langDownloadUserList="Upload list";
$langUserNumber="number";
$langGiveAdmin="Make admin";
$langRemoveRight="Remove this right";
$langGiveTutor="Make tutor";
$langUserOneByOneExplanation="He (she) will receive email confirmation with login and password";
$langBackUser="Back to users list";
$langUserAlreadyRegistered="A user with same name/surname is already registered	in this course.";

$langAddedToCourse="has been registered to your course";
$langGroupUserManagement="Group management";
$langIsReg="Your modifications have been registered";
$langPassTooEasy ="this password  is too simple. Use a pass like this ";

$langIfYouWantToAddManyUsers="If you want to add a list of users in 
			your course, please contact your web administrator.";

$langCourses="courses.";

$langLastVisits="My last visits";
$langSee		= "Go&nbsp;to";
$langSubscribe	= "Subscribe";
$langCourseName	= "Name&nbsp;of&nbsp;course";
$langLanguage	= "Language";

$langConfirmUnsubscribe = "Confirm user unsubscription";
$langAdded = "Added";
$langDeleted = "Deleted";
$langPreserved = "Preserved";

$langDate = "Date";
$langAction = "Action";
$langLogin = "Log In";
$langModify = "Modify";

$langUserName = "User name";


$langEdit = "Edit";
$langCourseManager = "Course Manager";
$langManage              = "Manage Campus";
$langAdministrationTools = "Administration";
$langAddImage= "Include picture";
$langImageWrong="The file size should be smaller than";
$langUpdateImage = "Change picture"; //by Moosh
$langDelImage = "Remove picture"; 	//by Moosh
$langOfficialCode = "Administrative Code";

$langAuthInfo = "Authentication";
$langEnter2passToChange = "Enter new pass twice to change, and empty to keep";
$langConfirm = "Confirm";
$lang_SearchUser_ModifOk            = "Update down";

$langNoUserSelected = "No user selected!";

// dialogbox messages

$langUserUnsubscribed = "User has been sucessfully unregistered from course";
$langUserNotUnsubscribed = "Error!! you can not unregister a course manager";

?>
