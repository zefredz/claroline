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

// add_course
$langNewCourse 			= "Nuevo curso";
$langAddNewCourse 		= "Agregar un nuevo curso";
$langRestoreACourse		= "Restaurar un curso";
$langOtherProperties  	        = "Autres propriétés trouvées dans l'archive";
$langSysId 			= "Id Système";
$langDescription  		= "Descripción";
$langDepartment	  		= "Departamento";
$langDepartmentUrl	  	= "URL";
$langScoreShow  		= "Mostar puntajes";
$langVisibility  		= "Visibilidad";
$langVersionDb  		= "Version de la base de donnée lors de l'archivage";
$langVersionClaro  		= "Version de claroline lors de l'archivage";
$langLastVisit  		= "Ultima visita";
$langLastEdit  			= "Ultima contribución";
$langExpire 			= "Expiración";
$langChoseFile 			= "Selecccione el archivo";
$langFtpFileTips 		= "Si le fichier est sur un ordinateur tiers et accessible par ftp";
$langLocalFileTips		= "Si le fichier est sur l'espace de stockage des cours de ce campus";
$langHttpFileTips		= "Si le fichier est sur un ordinateur tiers et accessible par http";
$langPostFileTips		= "Si le fichier est sur  votre ordinateur";


// create_course.php
$langLn="Idioma";


$langCreateSite  = "Crear un sitio web de un curso";
$langFieldsRequ  = "Todos los campos son obligatorios";
$langTitle       = "Nombre del Curso";
$langEx          = "p. ej. <i>Historia de la literatura</i>";
$langFac         = \"Facultad/Carrera";
$langTargetFac   = \"Se trata de la Facultad en la que se realiza el curso";
$langCode        = \"Código del curso";
$langMaxSizeCourseCode         = \"Max. 12 caracteres, p. ej.<i>ROM2121</i>";
$langDoubt       = \"En caso de dudas sobre el título exacto del curso o el código que le corresponde , consultar el";
$langProgram     = \"Programa del curso</a>. Si el sitio web que usted quiere crear no corresponde con ningún código de curso existente, usted puede definir uno. Por ejemplo <i>INNOVACION</i> si se trata de un programa de formación  sobre gestión de la innovación";
$langProfessors  = "Profesor(es)";
$langExplanation = \"Una vez que usted haya pulsado OK, será creado in sitio web que incluirá: Foro, 
                   Lista de enlaces, Ejercicios, Agenda, Lista de documentos... Con su
                  identificación de usuario, usted podrá modificar su contenido";
$langEmpty       = \"Usted no ha rellenado todos los campos.<br>Utilice el botón 'Atrás' de su navegador y vuelva a empezar.<br>Si usted no conoce el código de su curso, consulte el programa del curso";
$langCodeTaken   = \"Este código de curso ya se utilizó por otro curso.<br>Utilice el botón 'Atrás' de su navegador y vuelva a empezar.";


// tables MySQL
$langFormula       = \"Cordialmente, el profesor";
$langForumLanguage = \"Español";	// other possibilities are english, spanish (this uses phpbb language functions)
$langTestForum     = "Foro de pruebas ";
$langDelAdmin      = \"A eliminar vía la administración de los foros";
$langMessage       = \"En el momento que usted suprima el foro \"Foro de pruebas\", igualmente se suprimirá el presente tema que no contiene más que este mensaje";
$langExMessage     = "Mensaje de ejemplo";
$langAnonymous     = \"Anónimo";
$langExerciceEx    = "Ejemplo de ejercicio";
$langAntique       = \"Historia de la filosofía clásica";
$langSocraticIrony = \"La ironía socrática consiste en...";
$langManyAnswers   = "(varias respuestas correctas son posibles)";
$langRidiculise    = "Ridiculizar al interlocutor para hacerle admitir su error.";
$langNoPsychology  = \"No. La ironía socrática no se aplica al terreno de la psicología, sino en el de la argumentación.";
$langAdmitError    = "Reconocer los propios errores para invitar al interlocutor a hacer lo mismo.";
$langNoSeduction   = \"No. No se trata de una estrategia de seducción o de un método por ejemplo.";
$langForce         = \"Forzar  al interlocutor, por medio de una serie de cuestiones y subcuestiones, para que reconozca que no sabe lo que pretende saber."\;
$langIndeed        = \"Correcto. La ironía socrática es un metódo interrogativo. El término griego \"eirotao\" significa , por otro lado, \"interrogar\"."\;
$langContradiction = \"Utilizar el principio de no contradicción para llevar al interlocutor a un callejón sin salida.";
$langNotFalse      = "Esta respuesta no es falsa. Es exacto que la puesta en evidencia de la ignorancia del interlocutor se realiza poniendo en evidencia las contradicciones en que desembocan sus tesis.";

// Home Page MySQL Table "Inicio"
$langAgenda        = "Agenda";
$langLinks         = "Enlaces";
$langDoc           = "Documentos";
$langVideo         = "Video";
$langWorks         = "Trabajos de Alumnos";
$langCourseProgram = "Programa del Curso";
$langAnnouncements = "Anuncios";
$langUsers         = "Usuarios";
$langForums        = "Foros";
$langExercices     = "Ejercicios";
$langStatistics    = "Estadísticas";
$langAddPageHome   = "Enviar una página y enlazarla con la página principal";
$langLinkSite      = "Agregar un enlace a  la página principal";
$langModifInfo     = "Modificar la informacióne del curso";



// Other SQL tables
$langAgendaTitle       = "Martes 11 diciembre 14h00 : curso de filosofía (1) - Local : Sur 18";
$langAgendaText        = "Introducción general a la filosofía y explicación sobre el funcionamiento del curso";
$langMicro             = "Entrevistas de calle";
$langVideoText         = "Este un ejemplo de Real Video. Usted puede enviar videos en todos los formatos (.mov, .rm, .mpeg...), siempre que los estudiantes estén condiciones de leerlos.";
$langGoogle            = "Potente motor de búsqueda";
$langIntroductionText  = "Este es el texto de introducción de su curso. Para modificarlo, haga click sobre \"modificar\".";
$langIntroductionTwo   = "Esta página permite a cada estudiante o grupo de estudiantes colocar un documento en el sitio web del curso. Envíe documentos en formato HTML únicamente si estos no contienen imágenes.";
$langCourseDescription = "Escriba aquí la descripción que aparecerá en la lista de los cursos";
$langProfessor         = "Profesor";
$langAnnouncementEx    = "Este es un ejemplo de un anuncio.";
$langJustCreated       = "Usted acaba de crear el sitio web del curso";
$langEnter             = "Volver a mi lista de cursos";
$langMillikan          = "Experimento Millikan ";
$langCourseDesc        = "Descripción del Curso ";

 // Groups
$langGroups="Grupos";
$langCreateCourseGroups="Grupos";
$langCatagoryMain="Principal";
$langCatagoryGroup="Foros de grupos";
$langChat ="Chat";

?>
