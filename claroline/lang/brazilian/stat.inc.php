<?
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.3.0 $Revision$                        	 |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2002 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      |   $Id$         	 |
      |   Brazillian Translation (portugese)                                 |
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
      | Translator :                                                         |
      |           Marcello R. Minholi, <minholi@unipar.be>                   |
	  |									from Universidade Paranaense         |
      +----------------------------------------------------------------------+
 */
 $msgAdminPanel = "Painel do Administrador";
 $msgStats = "Estatísticas";
 $msgStatsBy = "Estatísticas por";
 $msgHours = "horas";
 $msgDay = "dia";
 $msgWeek = "semana";
 $msgMonth = "mês";
 $msgYear = "ano";
 $msgFrom = "de ";
 $msgTo = "para ";
 $msgPreviousDay = "dia anterior";
 $msgNextDay = "próximo dia";
 $msgPreviousWeek = "semana anterior";
 $msgNextWeek = "próxima semana";
 $msgCalendar = "calendário";
 $msgShowRowLogs = "mostrar os logs";
 $msgRowLogs = "os logs";
 $msgRecords = "registros";
 $msgDaySort = "Classificado por Dia";
 $msgMonthSort = "Classificado por Mês";
 $msgCountrySort = "Classificado por País";
 $msgOsSort = "Classificado por S.O.";
 $msgBrowserSort = "Classificado por Browser";
 $msgProviderSort = "Classificado por Provedor";
 $msgTotal = "Total";
 $msgBaseConnectImpossible = "Impossível selecionar Base SQL";
 $msgSqlConnectImpossible = "Conexão com o Servidor SQL impossível";
 $msgSqlQuerryError = "Consulta SQL impossível";
 $msgBaseCreateError = "Ocorreu um erro ao criar a base ezboo";
 $msgMonthsArray = array("janeiro","fevereiro","março","abril","maio","junho","julho","agosto","setembro","outubro","novembro","dezembro");
 $msgDaysArray = array("Domingo","Segunda-feira","Terça-feira","Quarta-feira","Quinta-feira","Sexta-feira","Sábado");
 $msgDaysShortArray=array("D","S","T","Q","Q","S","S");
 $msgToday = "Hoje";
 $msgOther = "Outro";
 $msgUnknown = "Desconhecido";
 $msgServerInfo = "Informação do Servidor php";
 $msgStatBy = "Estatísticas por";
 $msgVersion = "Webstats 1.30";
 $msgCreateCook = "<b>Administrador:</b> Um cookie foi criado em seu computador,<BR>
     Você não irá mais aparecer em seus logs.<br><br><br><br>";
 $msgCreateCookError = "<b>Administrador:</b> cookie não pode ser salva no seu computador.<br>
     Verifique as configurações do seu browser e recarregue a página.<br><br><br><br>";
 $msgInstalComments = "<p>O procedimento de instalação automática irá tentar:</p>
       <ul>
         <li>criar as tabelas chamadas <b>lista de domínios</b> na sua base SQL<br>
           </b>Esta tabela será preenchida automaticamente com os nomes de país do InterNIC
           </li>
         <li>criar tablela chamada <b>logezboo</b><br>
           Essa tabela irá armazenar seus logs.</li>
       </ul>
       <font color=\"#FF3333\">Você terá que modificar manualmente:<ul><li><b>config_sql.php3</b> com o seu <b>login</b>, <b>password</b> e <b>base name</b> para conexão com o servidor SQL.</li><br><li>The file <b>config.inc.php3</b> must have been modified to select apropriate language.</font></li></ul><br>To do so, you can you anykind of text editor (such as Notepad).";
 $msgInstallAbort = "SETUP ABORTED";
 $msgInstall1 = "If there is no error message above, installation is successfull.";
 $msgInstall2 = "2 tables have been created in your SQL base";
 $msgInstall3 = "You can now open the main interface";
 $msgInstall4 = "In order to fill your table when pages are loaded, you must put a tag in monitored pages.";

 $msgUpgradeComments ="This new version of ezBOO WebStats uses the same table <b>logezboo</b> as previous 
  						versions.<br>
  						If countries are not written in english, you must erase table <b>liste_domaine</b> 
  						et launch setup.<br>
  						This will have no effect on the table <b>logezboo</b> .<br>
  						Error message is normal. :-)";


$langStats="Statistics";
?>