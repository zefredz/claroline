<?
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.4                                                |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2002, 2003 Universite catholique de Louvain (UCL)|
      +----------------------------------------------------------------------+
      |   Este programa es software libre; usted puede redistribuirlo y/o    | 
      |   modificarlo bajo los términos de la Licencia Pública General (GNU) | 
      |   como fué publicada por la Fundación de Sofware Libre; desde la     |
      |   versión 2 de esta Licencia o (a su opción) cualquier versión       |
      |   posterior.                                                         |
      |   Este programa es distribuído con la esperanza de que sea útil,     |
      |   pero SIN NINGUNA GARANTIA; sin ninguna garantía implícita de       |
      |   MERCATIBILILIDAD o ADECUACIÓN PARA PROPOSITOS PARTICULARES.        |
      |   Vea la Licencia Pública General GNU por más detalles.              |
      |   Usted pudo haber recibido una copia de la Licencia Pública         |
      |   General GNU junto con este programa; sino, escriba a la Fundación  |
      |   de Sofware Libre : Free Software Foundation, Inc., 59 Temple Place |
      |   - Suite 330, Boston, MA 02111-1307, USA. La licencia GNU GPL       |
      |   también está disponible a través de la world-wide-web en la        |
      |   dirección  http://www.gnu.org/copyleft/gpl.html                    |
      +----------------------------------------------------------------------+
      | Autores: Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
      |          Hugues Peeters    <peeters@ipm.ucl.ac.be>                   |
      |          Christophe Gesché <gesche@ipm.ucl.ac.be>                    |
      +----------------------------------------------------------------------+
      | Traducción :                                                         |
      |          Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
      |          Andrew Lynn       <Andrew.Lynn@strath.ac.uk>                |
      +----------------------------------------------------------------------+
      | Basado en la traducción al castellano de                             |
      |          Xavier Casassas Canals <xcc@ics.co.at>                      |
      | Adaptado al español latinoamericano en Agosto-2003 por               |
      |          Carlos Brys       <brys@fce.unam.edu.ar>                    |
      +----------------------------------------------------------------------+
 */
 
 $msgAdminPanel   = "Panel de administraci&oacute;n";
 $langStats       = "Estad&iacute;sticas";
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
 $msgCreateCook      = "<b>Administrador:</b> Un cookie ha sido archivado en su ordenador,<BR>
     Usted no ser&aacute; contabilizado en las entradas.<BR><BR><BR><BR>";
 $msgCreateCookError = "<b>Administrador:</b> El cookie no ha podido ser archivado en su computadorar.<br>
     Verifique que su navegador los acepte, y actualice su p&aacute;gina.<br><br><br><br>";
 $msgInstalComments  = "<p>El procedimiento de instalaci&oacute;n autom&aacute;tica tratar&aacute; de:</p>
       <ul>
         <li>crear una tabla llamada <b>liste_domaines</b> en su base de datos SQL<br>
           </b>Esta tabla se llenar&aacute; autom&aacute;ticamente con el c&oacute;digo del pa&iacute;s y el c&oacute;digo InterNIC associado
           </li>
         <li>crear una tabla llamada <b>logezboo</b><br>
           Esta tabla contendr&aacute; sus entradas</li>
       </ul>
       <font color=\"#FF3333\">Usted debe haber modificado manualmente:<ul><li>el archivo <b>config_sql.php3</b> con su <b>nombre de usuario</b>, <b>contraseña</b> y <b>nombre de la base de datos</b> para la conexi&oacute;n al servidor SQL.</li><br><li>El archivo <b>config.inc.php3</b> debe ser modificado para seleccionar el idioma apropiado.</font></li></ul><br>Para hacerlo, usted puede utilizar un editor texto como Notepad.";
 $msgInstallAbort   = "INSTALACI&Oacute;N INTERRUMPIDA";
 $msgInstall1       = "Si no apareci&oacute; ning&uacute;n error, la instalaci&oacute;n se ha efectuado correctamente.";
 $msgInstall2       = "Se han creado dos tablas en su base de datos SQL";
 $msgInstall3       = "Usted puede abrir la interfase principal";
 $msgInstall4       = "Para llenar su tabla cuando las p&aacute;ginas son cargadas, debe escribir una etiqueta en las p&aacute;ginas a observar.";

 $msgUpgradeComments ="La nueva versi&oacute;n de ezBOO WebStats utiliza la misma tabla <b>logezboo</b> 
	   	que las versiones precedentes.<br>
		Si el pa&iacute;s no aparece en espa&ntilde;ol, debe suprimir la tabla 
  		<b>liste_domaines</b> y lanzar de nuevo la instalaci&oacute;n.<br>
  		Esto no tendr&aacute; ning&uacute;n efecto sobre la tabla <b>logezboo</b> .<br>
  		El mensaje de error es normal :-)";


 $langStats   = "Estad&iacute;sticas";

?>
