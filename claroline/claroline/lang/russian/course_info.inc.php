<?php // $Id$
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.5.*                              |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2003 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      |   This program is free software; you can redistribute it and/or      |
      |   modify it under the terms of the GNU General Public License        |
      |   as published by the Free Software Foundation; either version 2     |
      |   of the License, or (at your option) any later version.             |
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

$langModifInfo="Своства курса";
$langModifDone="Информация была изменена";
$langHome="Назад на вводную страницу";
$langCode="Код курса";
$langDelCourse="Удалить курс";
$langProfessor="Преподаватель";
$langProfessors="Преподаватели";
$langTitle="звание";
$langFaculty="факультет";
$langDescription="Описание";
$langConfidentiality="Конфиденциальность";
$langPublic="вободный доступ (со страницы Кампуса баз пароля)";
$langPrivOpen="Закрытый доступ, свободная регистрация";
$langPrivate="Закрытый доступ (сайт доступен только пользователям, указанным в списке <a href=../user/user.php>пользователи</a>)";
$langForbidden="Доступ запрещен";
$langLanguage="Язык";
$langConfTip="По умолчанию, курс доступен только вам, так как вы являетесь его единственным пользователем.
Если вы хотите минимум конфиденциальности, самым простым решением будет открыть регистрацию в течение недели
, попросить студентов зарегистрироваться самостоятельно, а затем закрыть регистрацию и проверить список пользователей
, чтобы исключить возможных посторонних лиц.";
$langTipLang="Этот язык будет рабочим языком для всех посетителей вашего сайта курса.";

// Change Home Page
$langAgenda="Календарь";
$langLink="Ссылки";
$langDocument="Учебные материалы";
$langVid="Видео";
$langWork="Задания";
$langProgramMenu="Программа";
$langAnnouncement="Объявления";
$langUser="Пользователи";
$langForum="Форумы";
$langExercise="Тесты";
$langStats="Статистика";
$langGroups ="Группы";
$langChat ="Чат";
$langUplPage="Разместить страницу и связать с вводной страницей курса";
$langLinkSite="Добавить ссылку на вводную страницу курса";
$langModifGroups="Группы";

// delete_course.php
$langDelCourse="Удалить весь курс целиком";
$langCourse="Курс ";
$langHasDel="Был удален";
$langBackHome="Назад на главную страницу ";
$langByDel="Удаляя данный сайт, вы удалите все учебные материалы, которые он содержит. Кроме того, 
будет закрыт доступ всем студенты, которые были на него подписаны.  
<p>Вы действительно хотите удалить курс?";
$langY="ДА";
$langN="НЕТ";

$langDepartmentUrl = "URL кафедры";
$langDepartmentUrlName = "Кафедра";
$langDescriptionCours  = "Описание курса";

$langArchive="Архив";
$langArchiveCourse = "Архивирование курса";
$langRestoreCourse = "Восстановление курса";
$langRestore="Восстановить";
$langCreatedIn = "создан в";
$langCreateMissingDirectories ="Создание отсутствующих папок";
$langCopyDirectoryCourse = "Копирование файлов курса";
$langDisk_free_space = "Свободное дисковое пространство";
$langBuildTheCompressedFile ="Создание сжатого файла";
$langFileCopied = "файл скопирован";
$langArchiveLocation = "Размещение архива";
$langSizeOf ="Размер";
$langArchiveName ="Название архива";
$langBackupSuccesfull = "Архивирование прошло успешно";
$langBUCourseDataOfMainBase = "Архивирование данных курса в главной базе данных для";
$langBUUsersInMainBase = "Архивирование данных пользователей в главной базе данных для";
$langBUAnnounceInMainBase="Архивирование данных объявлений в главной базе данных для";
$langBackupOfDataBase="Архивирование базы данных";
$langBackupCourse="Архивировать курс";

$langCreationDate = "Создан";
$langExpirationDate  = "Срок хранения до";
$langPostPone = "Запись информации";
$langLastEdit = "Последние изменения";
$langLastVisit = "Последние посещения";

$langSubscription="Регистрация";
$langCourseAccess="Доступ к курсу";

$langDownload="Скачать";
$langConfirmBackup="Вы действительно хотите заархивировать курс?";

$langCreateSite="Создать сайт курса";

$langRestoreDescription="Курс находится в архиве, который вы можете выбрать ниже.<br><br>
Когда вы щелкните на ссылке &quot;Восстановить&quot;, архив будет раскрыт и курс воссоздан.";
$langRestoreNotice="Этот скрипт пока не позволяет автоматического восстановления пользователей, но данных, сохраненных в файле &quot;users.csv&quot; достаточно, чтобы администратор смог осуществить эту операцию вручную. .";
$langAvailableArchives="Список существующих архивов";
$langNoArchive="Ни один архив не был выбран";
$langArchiveNotFound="Архив не найден";
$langArchiveUncompressed="Архив был раскрыт и инсталлирован.";
$langCsvPutIntoDocTool="Файл &quot;users.csv&quot; был размещен в разделе Учебные материалы.";
?>