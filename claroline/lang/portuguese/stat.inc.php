<?

/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.3.0 $Revision$                             |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2002 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      |   $Id$            |
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
      +----------------------------------------------------------------------|
      | Translation to European Portuguese (pt_PT):                          |
      | Dionisio Martínez Soler  <dmsoler@edu.xunta.es >                     |
      | 	(Escola Oficial de Idiomas de Vigo, Spain)                   |
      +----------------------------------------------------------------------|
 */
 
 $langStats       = "Estat&iacute;sticas";
 $msgAdminPanel   = "Painel de administra&ccedil;&atilde;o";
 $msgStats        = "Estat&iacute;sticas";
 $msgStatsBy      = "Estat&iacute;sticas por";
 $msgHours        = "horas";
 $msgDay          = "dia";
 $msgWeek         = "semana";
 $msgMonth        = "m&ecirc;s";
 $msgYear         = "ano";
 $msgFrom         = "de ";
 $msgTo           = "a ";
 $msgPreviousDay  = "dia anterior";
 $msgNextDay      = "dia seguinte";
 $msgPreviousWeek = "semana anterior";
 $msgNextWeek     = "semana seguinte";
 $msgCalendar     = "calend&aacute;rio";
 $msgShowRowLogs  = "ver os registos em bruto";
 $msgRowLogs      = "registos em bruto";
 $msgRecords      = "registos";
 $msgDaySort      = "Ordenados por dia";
 $msgMonthSort    = "Ordenados por m&ecirc;s";
 $msgCountrySort  = "Ordenados por pa&iacute;s";
 $msgOsSort       = "Ordenados por sistema operativo";
 $msgBrowserSort  = "Ordenados por navegador";
 $msgProviderSort = "Ordenados por fornecedor de acesso &agrave; internet";
 $msgTotal        = "Total";
 $msgBaseConnectImpossible = "N&atilde;o foi poss&iacute;vel seleccionar a base de dados SQL";
 $msgSqlConnectImpossible  = "N&atilde;o foi poss&iacute;vel estabelecer a liga&ccedil;&atilde;o ao servidor SQL";
 $msgSqlQuerryError        = "Consulta SQL imposs&iacute;vel";
 $msgBaseCreateError = "Erro na cria&ccedil;&atilde;o da base de dados";
 $msgMonthsArray     = array("Janeiro","Fevereiro","Mar&ccedil;o","Abril","Maio","Junho","Julho","Agosto","Setembro","Outubro","Novembro","Dezembro");
 $msgDaysArray       = array("domingo","segunda-feira","ter&ccedil;a-feira","quarta-feira","quinta-feira","sexta-feira","s&aacute;bado");
 $msgDaysShortArray  = array("D","2&ordf;","3&ordf;","4&ordf;","5&ordf;","6&ordf;","S");
 $msgToday           = "Hoje";
 $msgOther           = "Outro";
 $msgUnknown         = "Desconhecido";
 $msgServerInfo      = "Informa&ccedil;&atilde;o do Servidor php";
 $msgStatBy          = "Estat&iacute;sticas por";
 $msgVersion         = "Webstats 1.30";
 $msgCreateCook      = "<b>Administrador:</b> uma 'cookie' foi arquivada no seu computador.<BR>
     N&atilde;o ser&aacute; inclu&iacute;do nas estat&iacute;sticas de acesso.<BR><BR><BR><BR>";
 $msgCreateCookError = "<b>Administrador:</b> n&atilde;o foi poss&iacute;vel arquivar uma 'cookie' no seu computador.<br>
     Verifique que o seu navegador aceita 'cookies', e actualize a sua p&aacute;gina.<br><br><br><br>";
 $msgInstalComments  = "<p>O procedimento de instala&ccedil;&atilde;o autom&aacute;tica tentar&aacute;:</p>
       <ul>
         <li>criar uma tabela chamada <b>liste_domaines</b> na sua base de dados SQL<br>
           </b>Esta tabela ser&aacute; preenchida automaticamente com o c&oacute;digo do pa&iacute;s e com o c&oacute;digo InterNIC associado
           </li>
         <li>criar uma tabela chamada <b>logezboo</b><br>
           Esta tabela conter&aacute; os seus acessos</li>
       </ul>
       <font color=\"#FF3333\">&Eacute; preciso que tenha modificado maualmente:<ul><li>o ficheiro <b>config_sql.php3</b> com o seu <b>nome de utilizador</b>, <b>senha</b> e <b>nome da base de dados</b> para a liga&ccedil;&atilde;o ao servidor SQL.</li><br><li>O ficheiro <b>config.inc.php3</b> deve ser modificado para escolher a l&iacute;ngua adequada.</font></li></ul><br>Para o fazer, pode utilizar um editor de texto como Notepad.";
 $msgInstallAbort   = "INSTALA&Ccedil;&Atilde;O INTERROMPIDA";
 $msgInstall1       = "Se n&atilde;o apareceu qualquer indica&ccedil;&atilde;o de erro, a instala&ccedil;&atilde;o foi realizada com sucesso.";
 $msgInstall2       = "Foram criadas duas tabelas na sua base de dados SQL";
 $msgInstall3       = "Pode abrir o interface principal";
 $msgInstall4       = "Para preencher a tabela quando as p&aacute;ginas s&atilde;o lidas, deve escrever uma etiqueta nas p&aacute;ginas que tenham que ser observadas.";

 $msgUpgradeComments ="A nova vers&atilde;o de ezBOO WebStats utiliza a mesma tabela <b>logezboo</b> 
	   	que as vers&otilde;es anteriores.<br>
		Se o pa&iacute;s n&atilde;o aparecer em portugu&ecirc;s, deve eliminar a tabela <b>liste_domaines</b> e iniciar de novo a instala&ccedil;&atilde;o.<br>
  		Isto n&atilde;o ter&aacute; qualquer efeito sobre a tabela <b>logezboo</b> .<br>
  		A mensagem de erro &eacute; normal :-)";


 $langStats   = "Estat&iacute;sticas";

?>
