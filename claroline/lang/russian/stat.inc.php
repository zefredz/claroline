<?php
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.3.0 $Revision$                            |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2002 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      |   $Id$         |
      +----------------------------------------------------------------------+
      |   This program is free software; you can redistribute it and/or      |
      |   modify it under the terms of the GNU General Public License        |
      |   as published by the Free Software Foundation; either version 2     |
      |   of the License, or (at your option) any later version.             |
      |                                                                      |
      |   This program is distributed in the hope that it will be useful,    |
      |   but WITHOUT ANY WARRANTY; without even the implied warranty of     |
      |   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the      |
      |   GNU General Public License for more details.                       |
      |                                                                      |
      |   You should have received a copy of the GNU General Public License  |
      |   along with this program; if not, write to the Free Software        |
      |   Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA          |
      |   02111-1307, USA. The GNU GPL license is also available through     |
      |   the world-wide-web at http://www.gnu.org/copyleft/gpl.html         |
      +----------------------------------------------------------------------+
      | Authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
      |          Hugues Peeters    <peeters@ipm.ucl.ac.be>                   |
      |          Christophe Geschй <gesche@ipm.ucl.ac.be>                    |
      +----------------------------------------------------------------------+
 */

 $msgAdminPanel = "Панель администрирования";
 $msgStats = "Статистика";
 $msgStatsBy = "Статистика за";
 $msgHours = "час";
 $msgDay = "день";
 $msgWeek = "неделя";
 $msgMonth = "месяц";
 $msgYear = "год";
 $msgFrom = "с ";
 $msgTo = "по ";
 $msgPreviousDay = "предыдущий день";
 $msgNextDay = "следующий день";
 $msgPreviousWeek = "предыдущая неделя";
 $msgNextWeek = "следующая неделя";
 $msgCalendar = "календарь";
 $msgShowRowLogs = "показать количество входов";
 $msgRowLogs = "входы";
 $msgRecords = "записи";
 $msgDaySort = "Сортировка по дням";
 $msgMonthSort = "Сортировка по месяцам";
 $msgCountrySort = "Сортировка по странам";
 $msgOsSort = "Сортировка по операционным системам";
 $msgBrowserSort = "Сортировка по браузерам";
 $msgProviderSort = "Сортировка по провайдерам";
 $msgTotal = "Общее";
 $msgBaseConnectImpossible = "Невозможно выбрать базу SQL";
 $msgSqlConnectImpossible = "Невозможно подключиться к серверу SQL";
 $msgSqlQuerryError = "Запрос SQL невозможен";
 $msgBaseCreateError = "Ошибка во время создания базы";
 $msgMonthsArray = array("январь","февраль","март","апрель","май","июнь","июль","август","сентябрь","октябрь","ноябрь","декабрь");
 $msgDaysArray=array("Воскресенье","Понедельник","Вторник","Среда","Четверг","Пятница","Суббота");
 $msgDaysShortArray=array("Вс","Пн","Вт","Ср","Чт","Пт","Сб");
 $msgToday = "Сегодня";
 $msgOther = "Другой";
 $msgUnknown = "Неизвестен";
 $msgServerInfo = "php Server info";
 $msgStatBy = "Статистика по";
 $msgVersion = "Webstats 1.30";
 $msgCreateCook = "<b>Администратор:</b> cookie был размещен на вашем компьютере,<BR>
     Ваши посещения не будут заноситься в логи.<BR><BR><BR><BR>";
 $msgCreateCookError = "<b>Администратор:</b> невозможно разместить cookie на вашем компьютере.<br><br><br><br><br>";
 $msgInstalComments = "<p>Автоматическая процедура установки попробует:</p>
       <ul>
         <li>создать таблицу <b>liste_domaines</b> в вашу базу SQL<br>
           </b>Эта таблица будет автоматически заполняться названиями стран и соответствующих кодов InterNIC         </li>
         <li>создать таблицу с именем <b>logezboo</b><br>
           Эта таблица содержит ваши логи</li>
       </ul>
       <font color=\"#FF3333\">Предварительно вам нужно вручную изменить: 
	   <ul><li>файл <b>config_sql.php3</b>, внеся ваше <b>имя пользователя</b>, <b>пароль</b> 
	   и <b>название базы данных</b> для подключения к серверу SQL.</li><br>
	   <li>Файл <b>config.inc.php3</b> должен быть измене, чтобы выбрать нужный язык.</font></li></ul><br>Pour ce faire, vous pouvez utiliser un йditeur texte comme Notepad.";
 $msgInstallAbort = "УСТАНОВКА ПРЕРВАНА";
 $msgInstall1 = "Если нет сообщения об ошибке выше, установка прошла успешно.";
 $msgInstall2 = "2 таблицы были созданы в вашей базе данных SQL";
 $msgInstall3 = "Теперь вы можете открыть основной интерфейс";
 $msgInstall4 = "Чтобы заполнить таблицу логов, вы должны разместить таблицу в ваших страницах, за которыми вы ведете наблюдение.";

 $msgUpgradeComments ="Новая версия ezBOO WebStats использует ту же таблицу <b>logezboo</b>,  что и предыдущие версии.<br>
  						Если названия стран не показаны на русском языке, вам нужно удалить таблицу 
  						<b>liste_domaines</b> и снова начать установку. <br>
  						Это не окажет влияния на таблицу <b>logezboo</b> .<br>
  						Сообщение об ошибке является нормальным. :-)";


$langStats="Статистика";

?>