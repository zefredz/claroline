<?

/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.3.0 $Revision$                          |
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
      +----------------------------------------------------------------------+
      | Copyright (c) 2002, High Sierra Networks, Inc.                       |
      | This module was modifyed 2002-02-21 by                               |
      |          Mayra Angeles     <mayra.angeles@eduservers.com>            |
      |          Jorge Gonzalez    <jgonzalez@eduservers.com>                |
      | Description:                                                         |
      | Translation to Spanish.                                              |
      +----------------------------------------------------------------------|                                           |
      | adaptation spanisch (Spain) Xavier Casassas Canals <xcc@ics.co.at>
      +----------------------------------------------------------------------+
      | Translation to Spanish v.1.4                                         |
      | e-learning dept CESGA <teleensino@cesga.es >                         |
      +----------------------------------------------------------------------|
 */
 
 $langStats       = "Estad&iacute;sticas";
 $msgAdminPanel   = "Panel de administraci&oacute;n";
 $msgStats        = "Estad&iacute;sticas";
 $msgStatsBy      = "Estad&iacute;sticas por";
 $msgHours        = "horas";
 $msgDay          = "d&iacute;a";
 $msgWeek         = "semana";
 $msgMonth        = "mes";
 $msgYear         = "a&ntilde;o";
 $msgFrom         = "de ";
 $msgTo           = "a ";
 $msgPreviousDay  = "d&iacute;a anterior";
 $msgNextDay      = "d&iacute;a siguiente";
 $msgPreviousWeek = "semana anterior";
 $msgNextWeek     = "semana siguiente";
 $msgCalendar     = "calendario";
 $msgShowRowLogs  = "ver los registros brutos";
 $msgRowLogs      = "registros brutos";
 $msgRecords      = "registros";
 $msgDaySort      = "Ordenados por d&iacute;a";
 $msgMonthSort    = "Ordenados por mes";
 $msgCountrySort  = "Ordenados por pa&iacute;s";
 $msgOsSort       = "Ordenados por sistema operativo";
 $msgBrowserSort  = "Ordenados por navegador";
 $msgProviderSort = "Ordenados por proveedor";
 $msgTotal        = "Total";
 $msgBaseConnectImpossible = "Imposible seleccionar la base SQL";
 $msgSqlConnectImpossible  = "Imposible conectarse al servidor SQL";
 $msgSqlQuerryError        = "Consulta SQL imposible";
 $msgBaseCreateError = "Error en la creaci&oacute;n de la base de datos";
 $msgMonthsArray     = array("enero","febrero","marzo","abril","mayo","junio","julio","agosto","septiembre","octubre","noviembre","diciembre");
 $msgDaysArray       = array("Domingo","Lunes","Martes","Mi&eacute;rcoles","Jueves","Viernes","S&aacute;bado");
 $msgDaysShortArray  = array("D","L","M","M","J","V","S");
 $msgToday           = "Hoy";
 $msgOther           = "Otro";
 $msgUnknown         = "Desconocido";
 $msgServerInfo      = "Informaci&oacute;n del Servidor php";
 $msgStatBy          = "Estad&iacute;sticas por";
 $msgVersion         = "Webstats 1.30";
 $msgCreateCook      = "<b>Administrador:</b> Un cookie ha sido archivado en su computadora,<BR>
     Usted no ser&aacute; contabilizado en las entradas.<BR><BR><BR><BR>";
 $msgCreateCookError = "<b>Administrador:</b> El cookie no ha podido ser archivado en su computadora.<br>
     Verifique que su navegador los acepte, y actualice su p&aacute;gina.<br><br><br><br>";
 $msgInstalComments  = "<p>El procedimiento de instalaci&oacute;n autom&aacute;tica tratar&aacute; de:</p>
       <ul>
         <li>crear una tabla llamada <b>liste_domaines</b> en su base de datos SQL<br>
           </b>Esta tabla se llenar&aacute; autom&aacute;ticamente con el c&oacute;digo del pa&iacute;s y el c&oacute;digo InterNIC associado
           </li>
         <li>crear una tabla llamada <b>logezboo</b><br>
           Esta tabla contendr&aacute; sus entradas</li>
       </ul>
       <font color=\"#FF3333\">Usted debe haber modificado manualmente:<ul><li>el archivo <b>config_sql.php3</b> con su <b>nombre de usuario</b>, <b>clave de acceso</b> y <b>nombre de la base de datos</b> para la conexi&oacute;n al servidor SQL.</li><br><li>El archivo <b>config.inc.php3</b> debe ser modificado para seleccionar el idioma apropiado.</font></li></ul><br>Para hacerlo, usted puede utilizar un editor texto como Notepad.";
 $msgInstallAbort   = "INSTALACI&Oacute;N INTERRUMPIDA";
 $msgInstall1       = "Si no apareci&oacute; ning&uacute;n error, la instalaci&oacute;n se ha efectuado correctamente.";
 $msgInstall2       = "Se han creado dos tabla en su base de datos SQL";
 $msgInstall3       = "Usted puede abrir la interfaze principal";
 $msgInstall4       = "Para llenar su tabla cuando las p&aacute;ginas se cargan, debe escribir una etiqueta en las p&aacute;ginas a observar.";

 $msgUpgradeComments ="La nueva versi&oacute;n de ezBOO WebStats utiliza la misma tabla <b>logezboo</b> 
	   	que las versiones precedentes.<br>
		Si el pa&iacute;s no aparece en espa&ntilde;ol, debe suprimir la tabla 
  		<b>liste_domaines</b> y lanzar de nuevo la instalaci&oacute;n.<br>
  		Esto no tendr&aacute; ning&uacute;n efecto sobre la tabla <b>logezboo</b> .<br>
  		El mensaje de error es normal :-)";


 $langStats   = "Estad&iacute;sticas";

?>
