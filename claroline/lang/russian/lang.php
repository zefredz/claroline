<?php // $Id$
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.5.*                             |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2004 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      |   $Id$         |
      +----------------------------------------------------------------------+

      +----------------------------------------------------------------------+
      | Authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
      |          Hugues Peeters    <peeters@ipm.ucl.ac.be>                   |
      |          Christophe Geschй <gesche@ipm.ucl.ac.be>                    |
      +----------------------------------------------------------------------+
 */

/***************************************************************
*                   Language translation
****************************************************************
GOAL
****
Translate the interface in chosen language

*****************************************************************/



// GENERIC

$langModify="Изменить";
$langDelete="Удалить";
$langTitle="Название";
$langHelp="Помощь";
$langOk="ОК";
$langAddIntro="ДОБАВИТЬ ВВОДНЫЙ ТЕКСТ";
$langBackList="Назад к списку";





// index.php CAMPUS HOME PAGE

$langInvalidId="Это имя пользователя не действительно. Если вы еще не зарегистрированы, 
заполните  <a href='claroline/auth/inscription.php'>бланк регистрации</a></font color>";
$langMyCourses="Мои курсы";
$langCourseCreate="Создать сайт курса";
$langModifyProfile="Изменить мой профиль";
$langTodo="Предложения по доработке";
$langWelcome="Курсы ниже находятся в свободном доступе. Другие курсы требуют наличия имени 
пользователя и пароля, которые можно получить, перейдя по ссылке 'регистрация'. Преподаватели и 
ассистенты могут создать новые курсы, также перейдя по ссылке 'регистрация'.";
$langUserName="Имя пользователя";
$langPass="Пароль";
$langEnter="вход";
$langHelp="ПомощьAide";
$langManager="Ответственный";
$langPlatform="Виртуальный университет использует платформу";



// REGISTRATION - AUTH - INSCRIPTION
$langRegistration="Регистрация";
$langName="Фамилия";
$langSurname="Имя";

// COURSE HOME PAGE

$langAnnouncements="Объявления";
$langLinks="Ссылки";
$langWorks="Задания";
$langUsers="Пользователи";
$langStatistics="Статистика";
$langCourseProgram="Программа курса";
$langAddPageHome="Добавить страницу и связать с главной страницей курса";
$langLinkSite="Ссылка на сайт с главной страницы курса";
$langModifyInfo="Изменить информацию о курсе";
$langDeactivate="сделать неактивным";
$langActivate="сделать активным";
$langInactiveLinks="Неактивные ссылки";
$langAdminOnly="Доступно только администраторам";





// AGENDA

$langAddEvent="Добавить событие";
$langDetail="Подробности";
$langHour="Час";
$langLasting="Продолжительность";
$month_default="месяц";
$january="январь";
$february="февраль";
$march="март";
$april="апрель";
$may="май";
$june="июнь";
$july="июль";
$august="август";
$september="сентябрь";
$october="октябрь";
$november="ноябрь";
$december="декабрь";
$year_default="год";
$year1="2001";
$year2="2002";
$year3="2003";
$hour_default="час";
$hour1="08.30";
$hour2="09.30";
$hour3="10.45";
$hour4="11.45";
$hour5="12.30";
$hour6="12.45";
$hour7="13.00";
$hour8="14.00";
$hour9="15.00";
$hour10="16.15";
$hour11="17.15";
$hour12="18.15";
$lasting_default="продолжительность";
$lasting1="30 минут";
$lasting2="45 минут";
$lasting3="1 час";
$lasting4="1 час 30 минут";
$lasting5="2 часа";
$lasting6="4 часа";





// DOCUMENT

$langDownloadFile= "Загрузить файл на сервер";
$langDownload="загрузить";
$langCreateDir="Создать папку";
$langName="Название";
$langNameDir="Название новой папки";
$langSize="Размер";
$langDate="Дата";
$langMove="Переместить";
$langRename="Переименовать";
$langComment="Комментарий";
$langVisible="Видимая/невидимая";
$langCopy="Скопировать";
$langTo="в";
$langNoSpace="Загрузка не удалась. В вашей папке недостаточно места.";
$langDownloadEnd="Загрузка завершена";
$langFileExists="Невозможно выполнить операцию. <br>Файл с данным именем уже существует.";
$langIn="в";
$langNewDir="название новой папки";
$langImpossible="Невозможно выполнить операцию";
$langAddComment="добавить/изменить комментарий к ";
$langUp="вверх";



// WORKS

$langTooBig="Вы не выбрали файл для загрузки на сервер или он слишком большой.";
$langListDeleted="Список был полностью удален";
$langDocModif="Документ изменен";
$langDocAdd="Документ добавлен";
$langDocDel="Документ удален";
$langTitleWork="Название документа (без сокращений)";
$langAuthors="Авторы";
$langDescription="Возможное описание";
$langDelList="Полностью удалить список";



// ANNOUCEMENTS
$langAnnEmpty="Все объявления удалены";
$langAnnModify="Объявление изменено";
$langAnnAdd="Объявление добавлено";
$langAnnDel="Объявление удалено";
$langPubl="Опубликовано";
$langAddAnn="Добавить объявление";
$langContent="Содержание";
$langEmptyAnn="Удалить все объявления";




// OLD
$langAddPage="Добавить страницу";
$langPageAdded="Страница была добавлена";
$langPageTitleModified="название страницы изменено";
$langAddPage="Добавить страницу";
$langSendPage="Страница для загрузки на сервер";
$langCouldNotSendPage="Этот файл не в формате HTML и не будет размещен на сервере. 
Если вы хотите опубликовать документы в других форматах 
(PDF, Word, Power Point, Vidйo, etc.), используйте 
<a href=../document/document.php>Учебные материалы</a>";
$langAddPageToSite="Добавить страницу на сайт";
$langNotAllowed="Вы не являтесь редактором данного курса";
$langExercices="Тесты";

?>
