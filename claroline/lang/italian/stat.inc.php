<?php
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.3.0 $Revision$                             |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2002 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      |   $Id$              |
      |   Italian translation                                                |
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
      |          Christophe Gesché <gesche@ipm.ucl.ac.be>                    |
      +----------------------------------------------------------------------+
      | Translator:                                                          |
      +----------------------------------------------------------------------+
 */

 $langStats="Statistiche";
 $msgAdminPanel = "Console di amministrazione";
 $msgStats = "Statistiche";
 $msgStatsBy = "Statistiche da";
 $msgHours = "Ore";
 $msgDay = "giorno";
 $msgWeek = "settimana";
 $msgMonth = "mese";
 $msgYear = "anno";
 $msgFrom = "dal ";
 $msgTo = "al";
 $msgPreviousDay = "giorno precedente";
 $msgNextDay = "giorno seguente";
 $msgPreviousWeek = "settimana precedente";
 $msgNextWeek = "settimana seguente";
 $msgCalendar = "calendario";
 $msgShowRowLogs = "vedere direttamente i log";
 $msgRowLogs = "colonna dei log";
 $msgRecords = "registrazioni";
 $msgDaySort = "Ordinamento per giorno";
 $msgMonthSort = "Ordinamento per mese";
 $msgCountrySort = "Ordinamento per Paese";
 $msgOsSort = "Ordinamento per S.O.";
 $msgBrowserSort = "Ordinamento per Browser";
 $msgProviderSort = "Ordinamento per giorno provider";
 $msgTotal = "Totale";
 $msgBaseConnectImpossible = "Impossibile selezionare il database SQL";
 $msgSqlConnectImpossible = "Impossibile connettersi al server SQL";
 $msgSqlQuerryError = "Interrogazione SQL impossibile";
 $msgBaseCreateError = "Errore nella creazione del database";
 $msgMonthsArray = array("gennaio","febbraio","marzo","aprile","maggio","giugno","luglio","agosto","settembre","ottobre","novembre","dicembre");
 $msgDaysArray=array("Domenica","Lunedì","Martedì","Mercoledì","Giovedì","Venerdì","Sabato");
 $msgDaysShortArray=array("D","L","M","M","G","V","S");
 $msgToday = "Oggi";
 $msgOther = "Diverso";
 $msgUnknown = "Sconosciuto";
 $msgServerInfo = "informazioni Server php";
 $msgStatBy = "Statistica per";
 $msgversoion = "Webstats 1.30";
 $msgCreateCook = "<b>Amministratore:</b> Un cookie è stato memorizzato su voltro computer,<BR>
     Non sarete più ricontato nei log.<BR><BR><BR><BR>";
 $msgCreateCookError = "<b>Amministratore:</b> il non è stato possibile memorizzare il cookie nel computer.<br>
     Verificate che il browser accetti cookie e aggiornate la pagina.<br><br><br><br>";
 $msgInstalComments = "<p>La procedura automatica d'installazione cercherà di:</p>
       <ul>
         <li>creare una tabella chiamata <b>liste_domaines</b> nel sistema SQL<br>
           </b>La tabella sarà automaticamente riempita con il nome del Paese e il codice InterNIC associato
           </li>
         <li>creare una tabella di nome <b>logezboo</b><br>
           che conterrà i vostri logs</li>
       </ul>
       <font color=\"#FF3333\">Dovete prima modificare manualmente:<ul><li>Il documento <b>config_sql.php3</b> con il vostro <b>login</b>, <b>Password</b> e <b>Nome del database</b> Per la connessione al server SQL.</li><br><li>Il documento <b>config.inc.php3</b> deve essere modificato per selezionare la lingua appropriata.</font></li></ul><br>Per farlo potete utilizzare un editor di teso (ad es. Notepad).";
 $msgInstallAbort = "INSTALLAZIONE INTERROTTA";
 $msgInstall1 = "Se non sono comparsi errori nella parte di sopra, l'istallazione è andata a buon fine.";
 $msgInstall2 = "2 tabelle sono state create nel DDMS SQL";
 $msgInstall3 = "Ora potete aprire l'interfaccia principale";
 $msgInstall4 = "Per aggiornare i log nella tabella vovete mettere un tab nelle pagine da tracciare.";

 $msgUpgradeComments ="La nuova versione di ezBOO WebStats utilizza la la stessa tabella <b>logezboo</b> 
					   	della versione precedente.<br>
  						Se il paese di appartenenza non è la Francia dovete rimuovere la tabella in  
  						<b>liste_domaines</b> e rilanciare l'installazione.<br>
  						Ciò non avrà alcun effetto sulla tabella <b>logezboo</b> .<br>
  						Il messaggio d errore è normale :-)";


$langStats="Statistiche";

?>