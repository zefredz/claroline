<?php // $Id$
/***************************************************************************
 *                           lang_english.php  -  description
 *                              -------------------
 *     begin                : Sat Dec 16 2000
 *	    copyright            : (C) 2001 The phpBB Group
 *  	 email                : support@phpbb.com
 *
 *     $Id$
 *
 *  ***************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/



// GENERIC

$langModify="Изменить";
$langDelete="Удалить";
$langTitle="Название";
$langHelp="Поомщь";
$langOk="ОК";
$langBackList="Назад к списку";



$langLoginBeforePost1 = "Чтобы разместить сообщение в форуме, ";
$langLoginBeforePost2 = "вы должны сначала ";
$langLoginBeforePost3 = "зарегистрироваться в виртуальном университете";


// page_header.php

$langNewTopic="Начать новую тему";
$langAnswer="Ответить";
$langHelp="помощь";
$langAdm="Администрировать";
$langQuote="Цитировать";
$langEditDel="редактировать/удалить";
$langSeen="Прочитанные";
$langLastMsg="Последнее сообщение";
$langLastMsgs ="Последние сообщения";

$l_forum 	= "Форум";
$l_forums	= "Форумы";
$l_topic	= "Тема";
$l_topics 	= "Темы";
$l_replies	= "Ответы";
$l_poster	= "редактор";
$l_author	= "автор";
$l_views	= "прочитанные";
$l_post 	= "Сообщение";
$l_posts 	= "Сообщений";
$l_message	= "Сообщение";
$l_messages	= "Сообщения";
$l_subject	= "Тема";
$l_body		= "$l_message";
$l_from		= "от";   // Message from
$l_moderator 	= "модератор";
$l_username 	= "имя пользователя";
$l_password 	= "пароль";
$l_email 	= "Email";
$l_emailaddress	= "адрес Email";
$l_preferences	= "Настройки";

$l_anonymous	= "Аноним";  // Post
$l_guest	= "Гость"; // Whosonline
$l_noposts	= "нет $l_posts";
$l_joined	= "Зарегистрирован";
$l_gotopage	= "на страницу";
$l_nextpage 	= "Следующая страница";
$l_prevpage     = "Предыдущая страница";
$l_go		= "Перейти к ";
$l_selectforum	= "Выбрать $l_forum";

$l_date		= "Дата";
$l_number	= "Число";
$l_name		= "Название";
$l_options 	= "Опции";
$l_submit	= "Опубликовать";
$l_confirm 	= "Подтвердить";
$l_enter 	= "ОК";
$l_by		= "кто автор"; // Postй par
$l_ondate	= ""; // Ce message a йtй йditй par: $username le $date
$l_new          = "новые";

$l_html		= "HTML";
$l_bbcode	= "BBcode";
$l_smilies	= "Smilies";
$l_on		= "On";
$l_off		= "Off";
$l_yes		= "Да";
$l_no		= "Нет";

$l_click 	= "Щелкните";
$l_here 	= "здесь";
$l_toreturn	= "чтобы вернуться";
$l_returnindex	= "$l_toreturn к оглавлению форума";
$l_returntopic	= "$l_toreturn к списку тем форума.";

$l_error	= "Ошибка";
$l_tryagain	= "Вернитесь назад и попробуйте еще раз.";
$l_mismatch 	= "пароли не совпадают.";
$l_userremoved 	= "Этот участник был удален из базы данных.";
$l_wrongpass	= "Вы ввели неверный пароль.";
$l_userpass	= "Введите ваши имя пользователя и пароль.";
$l_banned 	= "Вам запрещен доступ в этот форум. Свяжитесь с администратором системы, если у вас есть вопросы.";
$l_enterpassword= "Введите ваш пароль.";

$l_nopost	= "У вас нет права размещать сообщения в этом форуме.";
$l_noread	= "У вас нет права читать сообщения данного форума.";

$l_lastpost 	= "Последнее $l_post";
$l_sincelast	= "с вашего последнего посещения";
$l_newposts 	= "Новые $l_posts $l_sincelast";
$l_nonewposts 	= "Нет новых $l_posts $l_sincelast";

// Index page
$l_indextitle	= "Оглавление форума";

// Members and profile
$l_profile	= "Профиль";
$l_register	= "Зарегистрироваться";
$l_onlyreq 	= "нужно, только если надо изменить регистрацию";
$l_location 	= "из";
$l_viewpostuser	= "просмотреть сообщения этого участника";
$l_perday       = "$l_messages в день";
$l_oftotal      = "от общего количества";
$l_url 		= "URL";
$l_icq 		= "ICQ";
$l_icqnumber	= "Номер ICQ";
$l_icqadd	= "добавить";
$l_icqpager	= "Pager";
$l_aim 		= "AIM";
$l_yim 		= "YIM";
$l_yahoo 	= "Yahoo Messenger";
$l_msn 		= "MSN";
$l_messenger 	= "MSN Messenger";
$l_website 	= "Адрес веб-сайта";
$l_occupation 	= "Место работы";
$l_interests 	= "Хобби";
$l_signature 	= "Подпись";
$l_sigexplain 	= "Это текст, который вы можете добавить к вашим сообщениям. <BR>максимум 255 знаков!";
$l_usertaken	= "$l_username, которое вы выбрали, уже существует";
$l_userdisallowed= "Выбранное $l_username не разрешено администратором. $l_tryagain";
$l_infoupdated	= "Ваши данные обновлены";
$l_publicmail	= "Разрешить другим участникам видеть ваш $l_emailaddress";
$l_itemsreq	= "Поля, отмеченные * обязательны";

// Viewforum
$l_viewforum	= "Зайти в форум";
$l_notopics	= "В этом форуме нет тем. Вы можете начать новую тему.";
$l_hotthres	= "Больше нет $hot_threshold $l_posts";
$l_islocked	= "$l_topic закрыт. (нельзя расместить новые $l_posts)";
$l_moderatedby	= "Модератор:";

// Private forums
$l_privateforum	= "Это <b>Закрытый форум</b>.";
$l_private 	= "$l_privateforum<br>Примечание: вы должны разрешить cookies на вашем компьютере, 
чтобы импользовать закрытые форумы.";
$l_noprivatepost = "$l_privateforum Вы не можете размещать сообщения в этом форуме.";

// Viewtopic
$l_topictitle	= "Читать $l_topic";
$l_unregistered	= "Незарегистрированный участник";
$l_posted	= "Опубликовано ";
$l_profileof	= "Просмотреть профиль ";
$l_viewsite	= "Просмотреть веб-сайт";
$l_icqstatus	= "состояние $l_icq";  // Etat ICQ
$l_editdelete	= "редактировать/удалить это $l_post";
$l_replyquote	= "Ответить, цитируя";
$l_viewip	= "Показать IP адреса (доступно только модераторам и администраторам)";
$l_locktopic	= "Закрыть эту $l_topic";
$l_unlocktopic	= "Открыть эту $l_topic";
$l_movetopic	= "Переместить эту $l_topic";
$l_deletetopic	= "Удалить эту $l_topic";

// Functions
$l_loggedinas	= "подключен как";
$l_notloggedin	= "не подключен";
$l_logout	= "выйти";
$l_login	= "войти";

// Page_header
$l_separator	= " > ";  // Included here because some languages have
		          // problems with high ASCII (Big-5 and the like).
$l_editprofile	= "Изменить профиль";
$l_editprefs	= "Изменить $l_preferences";
$l_search	= "Найти";
$l_memberslist	= "Список участников";
$l_faq		= "FAQ";
$l_privmsgs	= "частные $l_messages";
$l_sendpmsg	= "Отправить частное сообщение";
$l_statsblock   = '$statsblock = "Наши участники отправили -$total_posts- $l_messages.<br>
У нас -$total_users- зарегистрированных участников.<br>
Последний зарегистрированный участник -<a href=\"$profile_url\">$newest_user</a>-.<br>
-$users_online- ". ($users_online==1?"участник":"участники") ." 
<a href=\"$online_url\">сейчас</a> в этих форумах.<br>";';
$l_privnotify   = '$privnotify = "<br>У вас есть
 $new_message <a href=\"$privmsg_url\">новое частное".($new_message>1?"messages":"message")."</a>.";';

// Page_tail
$l_adminpanel	= "Администрирование";
$l_poweredby	= "Предоставлено";
$l_version	= "Версия";

// Auth

// Register
$l_notfilledin	= "Ошибка - вы не заполнили все обязательные поля.";
$l_invalidname	= "Выбранное вами имя участника  \"$username\" уже используется.";
$l_disallowname	= "Выбранное вами имя пользователя \"$username\" не разрешено администратором.";

$l_welcomesubj	= "Добро пожаловать в форумы $sitename";
$l_welcomemail	=
"
$l_welcomesubj,

Сохраните это письмо в ваших архивах.


Информация о вашем доступе: 

----------------------------
Имя пользователя: $username
Пароль : $password
----------------------------

Не забывайте ваш пароль, он зашифрован в нашей базе данных и мы не сможем восстановить его для вас. 
Однако, если вы его потеряете, мы предоставим вам простую программу, которая позволит вам создать себе новый пароль. 

Благодарим за регистрацию. 

$email_sig
";
$l_beenadded	= "Вы были добавлены в базу данных.";
$l_thankregister= "Спасибо за регистрацию!";
$l_useruniq	= "должно быть оригинальным. Два участника не могут иметь одно и то же имя пользователя.";
$l_storecookie	= "Разместите ваше имя пользователя в cookie на срок 1 год.";

// Prefs
$l_prefupdated	= "$l_preferences обновлены. $l_click <a href=\"index.$phpEx\">$l_here</a> $l_returnindex";
$l_editprefs	= "Редактировать ваши $l_preferences";
$l_themecookie	= "ПРИМЕЧАНИЕ: Чтобы использовать цветовые настройки вы ДОЛЖНЫ активировать cookies.";
$l_alwayssig	= "Всегда прикреплять мою подпись";
$l_alwaysdisable= "Никогда не использовать"; // Utilisй pour les 3 phrases suivantes
$l_alwayssmile	= "$l_alwaysdisable $l_smilies";
$l_alwayshtml	= "$l_alwaysdisable $l_html";
$l_alwaysbbcode	= "$l_alwaysdisable $l_bbcode";
$l_boardtheme	= "Цветовые настройки";
$l_boardlang    = "Язык";
$l_nothemes	= "Нет цветовых настроек в базе данных";
$l_saveprefs	= "Сохранить мои $l_preferences";

// Search
$l_searchterms	= "Ключевые слова";
$l_searchany	= "Искать КАЖДОЕ из этих слов (по умолчанию)";
$l_searchall	= "Искать ВСЕ слова";
$l_searchallfrm	= "Искать во всех форумах";
$l_sortby	= "Сортировать по";
$l_searchin	= "Искать по";
$l_titletext	= "Названию & Тексту";
$l_search	= "Искать";
$l_nomatches	= "Ни одна запись не отвечает вашему запросу. Уточните свой поиск.";

// Whosonline
$l_whosonline	= "Кто в онлайн?";
$l_nousers	= "Ни одного участника в данный момент нет в форумах.";


// Editpost
$l_notedit	= "Вы не можете редактировать чужие сообщения.";
$l_permdeny	= "Вы ввели неверный $l_password и или не имеете права редактировать это сообщение. $l_tryagain";
$l_editedby	= "Это $l_message изменено кем:";
$l_stored	= "Ваше $l_message записано.";
$l_viewmsg	= "чтобы увидеть ваше $l_message.";
$l_deleted	= "Ваше $l_post удалено.";
$l_nouser	= "Этот $l_username не существует.";
$l_passwdlost	= "Я потерял пароль!";
$l_delete	= "Удалить это сообщение";

$l_disable	= "Сделать неактивным";
$l_onthispost	= "для этого сообщения";

$l_htmlis	= "$l_html ";
$l_bbcodeis	= "$l_bbcode ";

$l_notify	= "сообщить по электронный почте об ответах на сообщение";

// Newtopic
$l_emptymsg	= "Введите текст $l_message. Вы не можете разместить пустое $l_message.";
$l_aboutpost	= "О сообщении";
$l_regusers	= "Все участники <b>Зарегистрированы</b>";
$l_anonusers	= "Участники <b>Анонимны</b>";
$l_modusers	= "<B>Только модераторы и администраторы</b>";
$l_anonhint	= "<br>(Чтобы разместить сообщение, не указывая вашего авторства, не воодите имя пользователя и пароль)";
$l_inthisforum	= "Могут начинать новые темы и отвечать на сообщения в этом форуме";
$l_attachsig	= "Показать подпись <font size=-2>(Это может быть изменено или добавлено в ваш профиль)</font>";
$l_cancelpost	= "Отменить это сообщение";

// Reply
$l_nopostlock	= "Вы не можете послать сообщение в эту тему, она закрыта.";
$l_topicreview  = "Обзор темы";
$l_notifysubj	= "Поступил ответ на вашу тему.";
$l_notifybody	= 'Уважаемый $m[username]\r\nВы получили это письмо, так как поступил ответ на сообщение, которое вы разместили на
форуме, и вы просили проинформировать вас об этом.

Вы можете увидеть тему по адресу:

http://$SERVER_NAME$url_phpbb/viewtopic.$phpEx?topic=$topic&forum=$forum

Или просмотреть оглавление форума $sitename по адресу

http://$SERVER_NAME$url_phpbb

Спасибо за использование форума $sitename.

До свидания.

$email_sig';


$l_quotemsg	= '[quote]\nLe $m[post_time], $m[username] написал:\n$text\n[/quote]';

// Sendpmsg
$l_norecipient	= "Вы должны ввести имя пользователя, которому вы хотите отправить это $l_message.";
$l_sendothermsg	= "Отправить частное сообщение";
$l_cansend	= "может отправлять $l_privmsgs";  // Tous les utilisateurs enregistrйs peuvent envoyer des MPs
$l_yourname	= "Ваше $l_username";
$l_recptname	= "$l_username адресата";

// Replypmsg
$l_pmposted	= "Ответ размещен, вы можете перейти по 
<a href=\"viewpmsg.$phpEx\">ссылке</a>, чтобы увидеть ваши $l_privmsgs";

// Viewpmsg
$l_nopmsgs	= "У вас нет $l_privmsgs.";
$l_reply	= "Ответ";

// Delpmsg
$l_deletesucces	= "Удаление прошло успешно.";

// Smilies
$l_smilesym	= "Что написать";
$l_smileemotion	= "смайлики";
$l_smilepict	= "Рисунок";

// Sendpasswd
$l_wrongactiv	= "Ключ активации неверен. Проверьте электронное $l_message, которое вы получили
и удостоверьтесь, что ссылка скопирована верно.";
$l_passchange	= "Ваш пароль успешно изменен. Теперь вы можете перейти к вашим 
<a href=\"bb_profile.$phpEx?mode=edit\">настройкам</a> и изменить ваш пароль.";
$l_wrongmail	= "Указанный электронный адрес не соответствует адресу, указанному в нашей базе данных.";

$l_passsubj	= "Форумы $sitename - Изменение пароля";

$l_pwdmessage	= 'Уважаемый $checkinfo[username],
Вы получили это сообщение, так как вы (ил кто-то, кто выдает себя за вас) попросил изменить ваш пароль 
на форумах $sitename. Если вы считаете, что получили это сообщение по ошибке, удалите его и ваш пароль 
останется прежним. 

Ваш новый сгенерированный пароль: $newpw

Чтобы изменение состоялось, вы должны зайти на эту страницу: 

   http://$SERVER_NAME$PHP_SELF?actkey=$key

При посещении этой страницы ваш пароль в нашей базе данных будет изменен и 
при желании вы сможете его изменить в ваших Настройках. 

Спасибо за использование форумов $sitename

$email_sig';

$l_passsent	= "Ваш пароль изменен на новый, сгенерированный случайным образом. Проверьте вашу почту, чтобы
узнать, как закончить процесс изменения пароля.";
$l_emailpass	= "выслать по электронной почте потерянный пароль";
$l_passexplain	= "Заполните этот бланк, новый пароль будет выслан на ваш электронный адрес";
$l_sendpass	= "Отправить пароль";




// Groups Management Claroline

$langGroupSpaceLink="Пространство группы";
$langGroupForumLink="Форум группы";
$langGroupDocumentsLink="Документы группы";
$langMyGroup="Моя группа";
$langOneMyGroups="под моей ответственностью";
?>