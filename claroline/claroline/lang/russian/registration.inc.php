<?php // $Id$
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.4.0 $Revision$
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2004 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
 */
// user management

// lang vars
$langAdminOfCourse		= "администратор";  //
$langSimpleUserOfCourse = "пользователь"; // strings for synopsis
$langIsTutor  			= "тьютор"; //

$langCourseCode			= "Курс";	// strings for list Mode
$langParamInTheCourse 	= "Параметры курса"; //

$langAddNewUser = "Добавить пользователя в систему";
$langMember ="зарегистрирован";

$langDelete	="удалить";
$langLock	= "заблокировать";
$langUnlock	= "разблокировать";

$langHaveNoCourse = "Нет курсов";

$langFirstname = "Имя";
$langLastname = "Фамилия";
$langEmail = "Адрес электронной почты";
$langRetrieve ="Найти мои параметры регистрации";
$langMailSentToAdmin = "Электронное сообщение отправлено администратору.";
$langAccountNotExist = "Этот аккаунт не существует. <BR>".$langMailSentToAdmin." 
Он произведет поиск вручную. <BR><BR>";
$langAccountExist = "Это аккаунт существует. <BR> Электронное сообщение отправлено администратору. <BR><BR>";
$langWaitAMailOn = "Ждите ответа по поводу ";
$langCaseSensitiveCaution = "Система различает строчные и прописные буквы.";
$langDataFromUser = "Данные, приведенные пользователем";
$langDataFromDb = "Соответствующие данные в база данных";
$langLoginRequest = "Просьба имени пользователя";
$langExplainFormLostPass = "Введите тот текст, который вы ввели во время регистрации.";
$langTotalEntryFound = "Количество найденных данных";
$langEmailNotSent = "Что-то не сработало, отправьте это сообщение ";
$langYourAccountParam = "Вот ваши параметры подключения";
$langTryWith ="попробуйте ";
$langInPlaceOf ="вместо";
$langParamSentTo = "Ваши параметры подключения отправлены на адрес";



// REGISTRATION - AUTH - inscription.php
$langRegistration="Регистрация";
$langName="Фамилия";
$langSurname="Имя";
$langUsername="Имя пользователя";
$langPass="Пароль";
$langConfirmation="подтверждение";
$langStatus="Действие";
$langRegStudent="Записать меня на курсы";
$langRegAdmin="Создать сайты курсов";
$langTitular = "Преподаватель";
// inscription_second.php


$langRegistration = "Регистрация";
$langPassTwice    = "Пароль, набранный вами дважды, не идентичен. Используйте кнопку Назад вашего браузера
и повторите операцию.";

$langEmptyFields = "Вы заполнили не все поля.
Используйте кнопку Назад вашего браузера и повторите операцию.";

$langPassTooEasy ="Этот пароль слишком простой. Выберите другой пароль, например: ";

$langUserFree    = "Выбранное вами имя пользователя уже используется. 
Используйте кнопку Назад вашего браузера и выберите другое имя.";

$langYourReg                = "Вааша регистрация на";
$langDear                   = "Уважаемый(ая)";
$langYouAreReg              = "Вы зарегистрированы на ";
$langSettings               = "со следующими параметрами: Имя пользователя:";
$langAddress                = "Адрес";
$langIs                     = "-";
$langProblem                = "В случае проблем, свяжитесь с нами.";
$langFormula                = "С уважением";
$langManager                = "Ответственный";
$langPersonalSettings       = "Ваши личные данные записаны и вам был отправлено электронное письмо
с напоминанием вашего имени пользователя и пароля. </p>";
$langNowGoChooseYourCourses ="Теперь вы можете выбрать курсы, к которым вы хотите иметь доступ.";
$langNowGoCreateYourCourse  = "Теперь вы можете создать свой курс.";
$langYourRegTo              = "Ваши изменения";
$langIsReg                  = "Ваши изменения сохранены.";
$langCanEnter               = "Теперь вы можете <a href=../../index.php>войти в виртуальный университет</a>";

// profile.php

$langModifProfile = "Изменить мои настройки";
$langPassTwo      = "Пароль, набранный вами дважды, не идентичен.";
$langAgain        = "Начните сначала!";
$langFields       = "Вы заполнили не все поля";
$langUserTaken    = "Выбранное вами имя пользователя уже используется.";
$langEmailWrong   = "Указанный вами электронный адрес неполон или содержит неподходящие символы. ";
$langProfileReg   = "Ваши новые настройки сохранены.";
$langHome         = "Вернуться на главную страницу";
$langMyStats      = "Моя статистика";


// user.php

$langUsers    = "Пользователи";
$langModRight ="Изменить права: ";
$langNone     ="нет";
$langAll      ="да";

$langNoAdmin            = "отныне не имеет <b>права редактирования сайта</b>";
$langAllAdmin           = "отныне имеет <b>все права администрирования сайта</b>";
$langModRole            = "Изменить роль ";
$langRole               = "Роль (не обязательно)";
$langIsNow              = "отныне является";
$langInC                = "этого курса";
$langFilled             = "Вы заполнили не все поля";
$langUserNo             = "Выбранное вами имя пользователя";
$langTaken              = "уже используется. Выберите другое. ";
$langOneResp            = "Один из отетственных за курс";
$langRegYou             = "зарегистрировал вас на ";
$langTheU               ="Пользователь";
$langAddedU             ="добавлен. Если вы ввели его адрес, ему отправлено сообщение с именем пользователя ";
$langAndP               = "и паролем.";
$langDereg              = "был отписан от данного курса";
$langAddAU              = "Добавить пользователей";
$langStudent            = "студент";
$langBegin              = "начало";
$langPreced50           = "50 предыдущих";
$langFollow50           = "50 следующих";
$langEnd                = "конец";
$langAdmR               = "Администратор";
$langUnreg              = "Отписать";
$langAddHereSomeCourses = "<font size=2 face='arial, helvetica'><big>Мои курсы</big><br><br>
			Выберите курсы, к которым вы хотите иметь доступ. Уберите галочки напротив курсов, 
			к которым вы не хотите иметь доступ (курсы, за которые вы отвечаете, всегда будут вам доступны).
			Затем нажмите ОК в конце списка.";

$langCanNotUnsubscribeYourSelf = "Вы не можете отписаться от курса, чьим администратором вы являетесь. 
Только другой администратор курса может это сделать.";

$langGroup="Группа";
$langUserNoneMasc="-";

$langTutor                = "Тьютор";
$langTutorDefinition      = "Тьютор (право наблюдать за действиями групп)";
$langAdminDefinition      = "Администратор (право изменять содержание сайта)";
$langDeleteUserDefinition = "Отписать (удалить из списка пользователей <b>этого</b> курса)";
$langNoTutor              = "не является тьютором этого курса";
$langYesTutor             = "является тьютором этого курса";
$langUserRights           = "права пользователей";
$langNow                  = "в настоящее время";
$langOneByOne             = "Добавить пользователя вручную";
$langUserMany             = "Импортировать список пользователей из текстового файла";
$langNo                   = "нет";
$langYes                  = "да";

$langUserAddExplanation   = "Каждая строка необходимого текстого файла должна содержать 5 полей: 
<b>Фамилия Имя Имя пользователя Пароль Электронный адрес</b>
разделенных клавишей Tab и представленных в этом порядке. 
Пользователи получат по электронной почте имя пользователя и пароль. ";

$langSend             = "Отправить";
$langDownloadUserList = "Отправить список";
$langUserNumber       = "Количество пользователей";
$langGiveAdmin        = "Сделать администратором";
$langRemoveRight      = "Лишить этого права";
$langGiveTutor        = "Сделать тьютором";

$langUserOneByOneExplanation = "Он получит по электронной почте имя пользователя и пароль.";
$langBackUser                = "Назад к списку пользователей";
$langUserAlreadyRegistered   = "Пользователь, имеющий то же имя пользователя и пароль, уже зарегистрирован на курсе.";

$langAddedToCourse           = "зарегистрирован на вашем курсе";

$langGroupUserManagement     = "Управление группами";

$langIfYouWantToAddManyUsers = "Если вы хотите добавить список пользователей на ваш курс, свяжитесь с
вашим администратором платформы.";

$langCourses    = "курсы.";
$langLastVisits = "Мои последние посещения";
$langSee        = "Просмотреть";
$langSubscribe  = "Записаться<br>отметить&nbsp;=&nbsp;да";
$langCourseName = "Название курса";
$langLanguage   = "Язык";

$langConfirmUnsubscribe = "Потдвердить запрет доступа к курсу этого пользователя.";
$langAdded              = "Добавлены";
$langDeleted            = "Удалены";
$langPreserved          = "Сохранены";
$langDate               = "Дата";
$langAction             = "Действие";
$langLogin              = "Вход, логин";
$langLogout             = "Выход";
$langModify             = "Изменить";
$langUserName           = "Имя пользователя";
$langEdit               = "Редактировать";

$langCourseManager       = "Менеджер курса";
$langManage              = "Управление кампусом";
$langAdministrationTools = "Средства администрирования";
$langModifProfile	     = "Изменить настройки";
$langUserProfileReg	     = "Изменение осуществлено";
$lang_lost_password      = "Пароль утерян";

$lang_enter_email_and_well_send_you_password  = "Введите адрес электронной почты, который вы использовали при регистрации,
и мы вышлем вам пароль. ";
$lang_your_password_has_been_emailed_to_you   = "Ваш пароль выслан вам по электронной почте.";
$lang_no_user_account_with_this_email_address = "Нет пользователя с таким электронным адресом.";
$langCourses4User  = "Курсы для этого пользователя";
$langCoursesByUser = "Показать курсы по критерию Пользователь";

?>