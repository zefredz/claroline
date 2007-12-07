<?php // $Id$
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.5.*                              |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2004 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      |   English Translation                                                |
      +----------------------------------------------------------------------+

      +----------------------------------------------------------------------+
      | Translator :                                                         |
      |          Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
      |          Andrew Lynn       <Andrew.Lynn@strath.ac.uk>                |
      +----------------------------------------------------------------------+
      | Translation to Spanish v.1.4                                         |
      | e-learning dept CESGA <teleensino@cesga.es >                         |
      +----------------------------------------------------------------------|
      | Translation to Spanish v.1.5.1                                       |
      | Rodrigo Alejandro Parra Soto , Ing. (e) En Computación eInformatica  |
      | Concepción, Chile  <raparra@gmail.com>                               |
      +----------------------------------------------------------------------|
 */

$langDBHost			= "Host de Base de Datos";
$langDBLogin		= "Username de Base de Datos";
$langDBPassword 	= "Clave de Base de Datos";
$langMainDB			= "BBDD Principal de Claroline";
$langStatDB             = "BBDD de Seguimiento. &Uacute;si s&oacute;lo si hay varias BBDD";
$langEnableTracking     = "Permitir Seguimiento";
$langAllFieldsRequired	= "Requerir todos los campos";
$langPrintVers			= "Versi&oacute;n para Imprimir";
$langLocalPath			= "Ruta local correspondiente";
$langAdminEmail			= "Email del Administrador ";
$langAdminName			= "Nombre del Administrador";
$langAdminSurname		= "Apellidos del Administrador";
$langAdminLogin			= "Login del Administrator ";
$langAdminPass			= "Clave del Administrator";
$langEducationManager	= "Responsable Educativo";
$langHelpDeskPhone		= "Tel&eacute;fono de Ayuda";
$langCampusName			= "Nombre de Su Campus";
$langInstituteShortName = "Acrónimo de la Instituci&oacute;n";
$langInstituteName		= "URL de la Instituci&oacute;n";


$langDBSettingIntro		= "
				El script de Instalación creará la BBDD principal de Claroline. Por favor, recuerde que Claroline 
				necesitar&aacute; crar varias BBDD. Si s&oacute;lo puede tener una BBDD
				en su proveedor, Claroline no funcionar&aacute;.";
$langStep1 			= "Paso 1 de 6 ";
$langStep2 			= "Paso 2 de 6 ";
$langStep3 			= "Paso 3 de 6 ";
$langStep4 			= "Paso 4 de 6 ";
$langStep5 			= "Paso 5 de 6 ";
$langStep6 			= "Paso 6 de 6 ";
$langCfgSetting		= "Par&aacute;metros de Configuraci&oacute;n";
$langDBSetting 		= "Par&aacute;metros de BBDD MySQL";
$langMainLang 		= "Idioma Principal";
$langLicence		= "Licencia";
$langLastCheck		= "&Uacute;ltima comprobaci&oacute;n antes de instalar";
$langRequirements	= "Requisitos";

$langDbPrefixForm	= "Prefijo MySQL";
$langDbPrefixCom	= "Dejar vacio si no se pide";
$langEncryptUserPass	= "Encriptar la clave de los usuarios en la Base de Datos";
$langSingleDb	= "Usar una o varias BBDD para Claroline";

//////////////////////////////////////////////////
//agregados por Rodrigo Parra Soto

$langDBConnectionParameters = "Parametros de conección de Mysql";
$lang_Note_this_account_would_be_existing ="Nota : Esta cuenta podría existir";
$langDBNamesRules	= "Nombres de la base de datos";
$langPMADB			= "Extenciones de la BD de PhpMyAdmin";// show in multi DB
$langDbName			= "Nombre de la BD"; // show in single DB
$langDBUse			= "Uso de la base de datos";
$langDBSettingAccountIntro		= "
				Claroline Está echo para trabajar con muchas bases de datos pero también puede trabajar con una sola BD,
				Para trabajar con múltiples bases de datos, su cuenta necesita que la base de datos esté bien creada.<BR>
				Si usted solo utiliza una 
				BD ya que su servidor de hosting solo le ofrece esta opción , Debe seleccionar la opción \"Uno\" que está abajo.";
$langDBSettingNamesIntro		= "
				El script  de instalación creará la base de datos principal de Claroline. 
				Usted puede crear una base de datos diferente 
				para el seguimiento y la extención PhpMyAdmin si ustede quiere
				o garantice que todas estas cosas esten en una sola base de datos, tal como lo quiere. 
				Después, Claroline creará una nueva base de datos para cada nuevo curso creado.. 
				También puede especificar el prefijo de estas bases de datos.
				<p>
				Si usted solo está autorizado para usar una sola base de datos, 
				debe volver a la página anteriory seleccionar la opción\"una sola(Single)\"
				</p>
				";
$langDBSettingNameIntro		= "
				El script de instalación creará la tabla principál de Claroline, el seguimiento y la relaciones  PhpMyAdmin en su 
				única BD..
				Eliga un nómbre para esa BD y el prefijo para futuras tablas de cursos.<BR>
				Si a ustéd se le permite crear varias BD, Regrese a la página anterior y seleccione la opción\"varias (Several)\".
				Esta es realmente más conveniente de utilizar";
$langTbPrefixForm	= "Prefijo para los nómbres de las tablas de cols cursos";
$langWarningResponsible = "Use este script solo después de hacer un respaldo de su información.El equipo de  Claroline  no se hace responsable por información perdida o corrupta";
$langAllowSelfReg	=	"Permitir auto-registro";
$langAllowSelfRegProf =	"permitir auto_registro para  creadores de cursos";
$langRecommended	=	"(Recomendado)";
//////////////////////////////////////////////////

?>