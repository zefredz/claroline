<?php // $Id$
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.5.*
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2004, 2003 Universite catholique de Louvain (UCL)|
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
      |          Olivier Brouckaert <oli.brouckaert@skynet.be>               |
      +----------------------------------------------------------------------+
      | Traducción :                                                         |
      |          Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
      |          Andrew Lynn       <Andrew.Lynn@strath.ac.uk>                |
      |          Olivier Brouckaert <oli.brouckaert@skynet.be>               |
      +----------------------------------------------------------------------+
      | Basado en la traducción al castellano de                             |
      |          Xavier Casassas Canals <xcc@ics.co.at>                      |
      | Adaptado al español latinoamericano en Agosto-2003 por               |
      |          Carlos Brys       <brys@fce.unam.edu.ar>                    |
      +----------------------------------------------------------------------+
 */

$englishLangName = "spanish";
$localLangName = "español";

$iso639_2_code = "es";
$iso639_1_code = "esp";

$langNameOfLang['arabic'		]="árabe";
$langNameOfLang['brazilian'		]="portugués";
$langNameOfLang['bulgarian'		]="bulgarian";
$langNameOfLang['croatian'		]="croato";
$langNameOfLang['dutch'			]="dutch";
$langNameOfLang['english'		]="inglés";
$langNameOfLang['finnish'		]="finlandés";
$langNameOfLang['french'		]="francés";
$langNameOfLang['german'		]="alemán";
$langNameOfLang['greek'			]="griego";
$langNameOfLang['italian'		]="italiano";
$langNameOfLang['japanese'		]="japonés";
$langNameOfLang['polish'		]="polaco";
$langNameOfLang['simpl_chinese'	]="chino";
$langNameOfLang['spanish'		]="español";
$langNameOfLang['spanish_latin'	]="español latin";
$langNameOfLang['swedish'		]="sueco";
$langNameOfLang['thai'			]="thailandés";
$langNameOfLang['turkish'		]="turco";


$charset = 'iso-8859-1';
$text_dir = 'ltr'; // ('ltr' para izq a der, 'rtl' para der a izq)
$left_font_family = 'verdana, helvetica, arial, geneva, sans-serif';
$right_font_family = 'helvetica, arial, geneva, sans-serif';
$number_thousands_separator = '.';
$number_decimal_separator = ',';
$byteUnits = array('Bytes', 'Kb', 'Mb', 'Gb');

$langDay_of_weekNames['init'] = array('D', 'L', 'M',' M', 'J', 'V', 'S');
$langDay_of_weekNames['short'] = array('Dom', 'Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab');
$langDay_of_weekNames['long'] = array('Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado');

$langMonthNames['init']  = array('E', 'F', 'M', 'A', 'M', 'J', 'J', 'A', 'S', 'O', 'N', 'D');
$langMonthNames['short'] = array('Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic');
$langMonthNames['long'] = array('Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre');

// Voir http://www.php.net/manual/en/function.strftime.php pour la variable
// ci-dessous

$dateFormatShort =  "%b %d, %y";
$dateFormatLong  = '%A %B %d, %Y';
$dateTimeFormatLong  = '%B %d, %Y at %I:%M %p';
$timeNoSecFormat = '%I:%M %p';

// GENERIC 

$langYes="Si";
$langNo="No";
$langBack="Atrás";
$langNext="siguiente";
$langAllowed="Permitido";
$langDenied="Denegado";
$langBackHome="Volver al inicio";
$langPropositions="Sugerencias para mejoras de";
$langMaj="Actualizar";
$langModify="Modificar";
$langDelete="Eliminar";
$langMove="Mover";
$langTitle="Título";
$langHelp="Ayuda";
$langOk="Aceptar";
$langAdd="Agregar";
$langAddIntro="Agregar un texto introductorio";
$langBackList="Volver a la lista";
$langText="Texto";
$langEmpty="Vacío";
$langConfirmYourChoice="Por favor, confirme su elección";
$langCheckAll="Marcar todo";
$langAnd="y";
$langChoice="Su elección";
$langFinish="Terminar";
$langCancel="Cancelar";
$langNotAllowed="Ud. no está admitido aquí";
$langManager="Administrador";
$langPlatform="Funciona con";
$langOptional="Opcional";
$langNextPage="Próxima página";
$langPreviousPage="Página anterior";
$langUse="Usa";
$langTotal="Total";
$langTake="toma";
$langOne="Uno";
$langSeveral="Algunos";
$langNotice="Aviso";
$langDate="Fecha";

// banner

$langMyCourses="Lista de mis cursos";
$langModifyProfile="Modificar mi perfil";
$langMyStats = "Ver mis estadísticas";
$langLogout="Salir";

// Tools names 

$langAgenda             = "Agenda";
$langDocument           = "Documentos";
$langWork               = "Trabajos de los Estudiantes";
$langAnnouncement       = "Anuncios";
$langUser               = "Usuarios";
$langForum              = "Foros";
$langExercise           = "Ejercicios";
$langStats              = "Estadísticas";
$langGroups             = "Grupos";
$langChat               = "Charlar";
$langDescriptionCours   = "Descripción del Curso";

?>
