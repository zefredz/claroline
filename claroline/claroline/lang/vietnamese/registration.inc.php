<?php // $Id$
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.4.0 $Revision$
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2004 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
	  |   English Translation                                                |
      +----------------------------------------------------------------------+
      | Authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
      |          Hugues Peeters    <peeters@ipm.ucl.ac.be>                   |
      |          Christophe Gesché <gesche@ipm.ucl.ac.be>                    |
      +----------------------------------------------------------------------+
      | Translator :                                                         |
      |          Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
      |          Andrew Lynn       <Andrew.Lynn@strath.ac.uk>                |
      +----------------------------------------------------------------------+
 */

// userMAnagement
$langAdminOfCourse		= "admin";  //
$langSimpleUserOfCourse = "normal"; // strings for synopsis
$langIsTutor  			= "giáo viên"; //

$langCourseCode			= "Khoá học";	// strings for list Mode
$langParamInTheCourse 	= "Trạng thái"; //

$langAddNewUser = "thêm user";
$langMember ="đã đăng ký";

$langDelete	="xoá";
$langLock	= "khoá";
$langUnlock	= "mở khoá";
// $langOk

$langHaveNoCourse = "không có khoá học";


$langFirstname = "Tên"; // by moosh
$langLastname = "Họ"; // by moosh
$langEmail = "Email";// by moosh
$langRetrieve ="Thông tin";// by moosh
$langMailSentToAdmin = "Đã gửi thư cho quản trị";// by moosh
$langAccountNotExist = "Không thấy tài khoản.<BR>".$langMailSentToAdmin." để tìm bằng tay<BR>";// by moosh
$langAccountExist = "Tìm thấy tài khoản.<BR>".$langMailSentToAdmin."<BR>";// by moosh
$langWaitAMailOn = "Thư sẽ gửi đến ";// by moosh
$langCaseSensitiveCaution = "Chú ý : case sensitive.";// by moosh
$langDataFromUser = "Dữ liệu do user gửi đi";// by moosh
$langDataFromDb = "Dữ liệu trong CSDL";// by moosh
$langLoginRequest = "Cần thiết Login ";// by moosh
$langExplainFormLostPass = "Hãy gõ lại những gì bạn còn nhớ được khi đăng ký";// by moosh
$langTotalEntryFound = "Đã tìm thấy";// by moosh
$langEmailNotSent = "Có trục trặc gì đó, xin báo lại cho ";// by moosh
$langYourAccountParam = "Đây là tên đăng nhập và mật khẩu của bạn";// by moosh
$langTryWith ="Thử với";// by moosh
$langInPlaceOf ="thay vì ";// by moosh
$langParamSentTo = "Thông tin đã gửi đến ";// by moosh

// REGISTRATION - AUTH - inscription.php
$langRegistration="Đăng ký";
$langName="Họ";
$langSurname="Tên";
$langUsername="Tên đăng nhập (username)";
$langPass="Mật khẩu";
$langConfirmation="Xác nhận lại";
$langEmail="Địa chỉ Email";
$langStatus="Chức năng";
$langRegStudent="Tham gia các khoá học (HS)";
$langRegAdmin="Tạo website cho khoá học (GV)";

// inscription_second.php
$langPassTwice="You typed two different passwords. Use your browser's back button and try again.";
$langEmptyFields="You left some fields empty. Use your browser's back button and try again.";
$langUserFree="This username is already taken. Use your browser's back button and choose another.";
$langYourReg="Your registration on";
$langDear="Dear";
$langYouAreReg="You are registered on";
$langSettings="with the following settings:\nUsername:";
$langAddress="The address of ";
$langIs="is";
$langProblem="In case of problems, contact us.";
$langFormula="Yours sincerely";
$langManager="Manager";
$langPersonalSettings="Your personnal settings have been registered and an email has been sent to help you remember your username and password.</p>";

$langNowGoChooseYourCourses ="You can now go to select, in the list, the courses you want to access.";
$langNowGoCreateYourCourse  ="You can now go to create  your  course";

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
$langMyStats = "Thống kê";

// user.php

$langUsers="Người dùng";
$langModRight="Hiệu chỉnh quyền admin của";
$langNone="Không";
$langAll="All";
$langNoAdmin="<b>KHÔNG có quyền quản trị đối với site này</b>";
$langAllAdmin="<b>CÓ quyền quản trị đối với site này</b>";
$langModRole="Thay đổi vai trò của";
$langRole="Vai trò";
$langIsNow="bây giờ";
$langInC="trong khoá học này";
$langFilled="Bạn còn để trống cột.";
$langUserNo="tên bạn chọn  ";
$langTaken="đã có rồi. Hãy chọn tên khác.";
$langOneResp="Một trong số quản trị viên của khoá học";
$langRegYou="đã đăng ký cho bạn trong khoá này";
$langTheU="Người dùng";
$langAddedU="đã được thêm. Sẽ gửi thông báo bằng email ";
$langAndP="";
$langDereg="đã thôi đăng ký cho khoá này";
$langAddAU="Thêm user";
$langStudent="học sinh";
$langBegin="bắt đầu.";
$langPreced50 = " 50 trước ";
$langFollow50 = " 50 sau ";
$langEnd = "kết thúc";
$langAdmR="Quyền quản trị";
$langUnreg = "Thôi đăng ký";
$langAddHereSomeCourses = "<font size=2 face='Arial, Helvetica'><big>Hiệu chỉnh danh sách khoá học</big><br><br>
Đánh dấu vào khoá học bạn chọn.<br>
Ngược lại xoá đánh dáu trên ô đã chọn.<br> Sau đó bấm OK tại cuối danh sách";
$langTitular = "Tác giả";
$langCanNotUnsubscribeYourSelf = "Bạn không thể thôi đăng ký một khoá học do chính bạn tạo ra.";

$langGroup="Nhóm";
$langUserNoneMasc="-";
$langTutor="Giáo viên";
$langTutorDefinition="Giáo viên (có quyền giám sát các nhóm)";
$langAdminDefinition="Quản trị (có quyền thay đổi nội dung website)";
$langDeleteUserDefinition="Unregister (delete from users list of  <b>this</b> course)";
$langNoTutor = "is not tutor for this course";
$langYesTutor = "is tutor for this course";
$langUserRights="Users rights";
$langNow="now";
$langOneByOne="Sau khi đăng ký";
$langUserMany="Import users list through textfile";
$langNo="no";
$langYes="yes";
$langUserAddExplanation="every line of file to send will necessarily an only
		include 5 fields: <b>Last Name&nbsp;&nbsp;&nbsp;First Name&nbsp;&nbsp;&nbsp;
		Login&nbsp;&nbsp;&nbsp;Password&nbsp;
		&nbsp;&nbsp;Email</b> separated by tabs and in this order.
		Users will recieve email confirmation with login/password.";
$langSend="Gửi";
$langDownloadUserList="Danh sách Upload";
$langUserNumber="số lượng";
$langGiveAdmin="Gán quyền admin";
$langRemoveRight="Xoá quyền này";
$langGiveTutor="gán quyền giáo viên";
$langUserOneByOneExplanation="Người dùng sẽ nhận được email thông báo login và password";
$langBackUser="Trở về danh sách user";
$langUserAlreadyRegistered="Trùng họ/tên với người dùng hiện có.";

$langAddedToCourse="đã đăng ký theo khoá học";
$langGroupUserManagement="Quản trị nhóm";
$langIsReg="Your modifications have been registered";
$langPassTooEasy ="mật khẩu này quá đơn giản. Nên dùng mật khẩu như ";

$langIfYouWantToAddManyUsers="Nếu bạn muốn nạp mới danh sách nhiều user cùng lúc, xin liên hệ với người quản trị";

$langCourses="các khoá học.";

$langLastVisits="Lần viếng thăm gần đây nhất ";
$langSee		= "Go&nbsp;to";
$langSubscribe	= "Subscribe";
$langCourseName	= "Name&nbsp;of&nbsp;course";
$langLanguage	= "Language";

$langConfirmUnsubscribe = "Xác nhận thôi đăng ký";
$langAdded = "Đã thêm";
$langDeleted = "Đã xoá";
$langPreserved = "Đã lưu";

$langDate = "Ngày";
$langAction = "Hành động";
$langLogin = "Log In";
$langLogout = "Log Out";
$langModify = "Hiệu chỉnh";

$langUserName = "Họ Tên";

$langEdit = "Edit";
$langCourseManager = "Quản lý khoá học";
$langManage				= "Quản lý chuyên môn";
$langAdministrationTools = "Các công cụ quản trị";
$langModifProfile	= "Sửa Profile";
$langUserProfileReg	= "updated";



$lang_lost_password = "Quên mật khẩu";
$lang_enter_email_and_well_send_you_password ="Nhập địa chỉ email bạn đã dùng để đăng ký, chúng tôi sẽ gửi lại mật khẩu cho bạn.";
$lang_your_password_has_been_emailed_to_you = "Đã gửi mật khẩu vào hòm thư của bạn.";
$lang_no_user_account_with_this_email_address = "Xin lỗi. Không có địa chỉ email như trên.";
$langCourses4User = "Các khoá học của user";
$langCoursesByUser = "Các khoá học tạo bởi user";

?>
