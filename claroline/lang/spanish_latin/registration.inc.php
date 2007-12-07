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


$langFirstname = "Nombre"; 
$langLastname = "Apellido";
$langEmail = "Email";
$langRetrieve ="Recuperar información de identififación";
$langMailSentToAdmin = "Un mensaje se envió al administrador.";
$langAccountNotExist = "No se encontró la cuenta.<BR>".$langMailSentToAdmin." El puede buscar manualmente.<BR>";
$langAccountExist = "Esta cuenta existe.<BR>".$langMailSentToAdmin."<BR>";
$langWaitAMailOn = "Um mensaje puede ser enviado a  ";
$langCaseSensitiveCaution = "El sistema hace diferencias entre mayúsculas y minísculas.";
$langDataFromUser = "Datos enviado por el usuario";
$langDataFromDb = "Datos en la base de datos";
$langLoginRequest = "Requerimiento de conexión";
$langExplainFormLostPass = "Entrez ce que  vous pensez avoir  introduit comme données lors de votre inscription.";
$langTotalEntryFound = "Se contró la entrada";
$langEmailNotSent = "Algo no funciona, envíe por email esto a  ";
$langYourAccountParam = "Estos son su Usuario y Contraseña de su cuenta";
$langTryWith ="Intente con";
$langInPlaceOf ="y no con  ";
$langParamSentTo = "Información de identificación enviada a ";

// REGISTRATION - AUTH - inscription.php
$langRegistration  = "Inscripción";
$langName          = "Apellido";
$langSurname       = "Nombre";
$langUsername      = "Nombre de usuario";
$langPass          = "Contraseña";
$langConfirmation  = "Confirmación";
$langEmail         = "Correo electrónico";
$langStatus        = "Acción";
$langRegStudent    = "Inscribirme en cursos (estudiante)";
$langRegAdmin      = "Crear sitios web de cursos (Profesor)";


// inscription_second.php
$langPassTwice     = "Ha escrito dos contraseñas diferentes. Use el botón  'Atrás'  del navegador y vuelva a intentarlo.";
$langEmptyFields   = "Ha dejado algunos campos en blanco. Use el botón 'Atrás' del navegador y vuelva a intentarlo.";
$langUserFree      = "El nombre de usuario que eligió ya existe. Use el botón 'Atrás'  del navegador y elija uno diferente.";
$langYourReg       = "Su inscripción en";
$langDear          = "Estimado(a)";
$langYouAreReg     = "Usted se ha inscripto en";
$langSettings      = "con los parámetros siguientes:\Nombre de usuario:";
$langAddress       = "La dirección de";
$langIs            = "es";
$langProblem       = "En caso de tener algún problema, no dude en contactarnos.";
$langFormula       = "Cordialmente";
$langManager       = "Responsable";
$langPersonalSettings = "Sus datos personales han sido registrados y ha sido enviado un correo electrónico a su casilla para recordarle su nombre de usuario y su contraseña.</p> Ahora seleccione de la lista los cursos a los que desea tener  acceso.";

$langNowGoChooseYourCourses ="Ahora Ud. puede seleccionar, en la lista, los cursos a los cuales desea acceder.";
$langNowGoCreateYourCourse  ="Ahora Ud. puede crear su curso";

$langYourRegTo     = "Usted está inscripto en";
$langIsReg         = "ha sido actualizado";
$langCanEnter      = "Ahora usted puede <a href=../../index.php>entrar al campus</a>";

// profile.php

$langModifProfile  = "Modificar mi perfil";
$langPassTwo       = "Usted ha escrito dos contraseñas diferentes";
$langAgain         = "Intente de nuevo!";
$langFields        = "Ha dejado algunos campos sin completar";
$langUserTaken     = "El nombre de usuario que ha elegido ya existe";
$langEmailWrong    = "La dirección de correo electrónico que ha escrito está incompleta o contiene caracteres inválidos";
$langProfileReg    = "Su nuevo perfil de usuario ha sido registrado";
$langHome          = "Regresar a la página de inicio";
$langMyStats       = "Ver mis estadísticas";


// user.php

$langUsers         = "Usuarios";
$langModRight      = "Modificar los derechos de administración de";
$langNone          = "ninguno";
$langAll           = "Todos";
$langNoAdmin       = "Ahora no tiene <b>ningún derecho de administración sobre este sitio";
$langAllAdmin      = "Ahora tiene <b>todos los derechos de administración de este sitio";
$langModRole       = "Modificar el papel (rol) de";
$langRole          = " Papel (Rol)";
$langIsNow         = "es ahora";
$langInC           = "en este curso";
$langFilled        = "No ha llenado todos los campos.";
$langUserNo        = "El nombre de usuario que eligió;";
$langTaken         = "ya existe. Elija uno diferente.";
$langOneResp       = "Uno de los administradores del curso";
$langRegYou        = "lo ha inscripto en este curso";
$langTheU          = "El usuario";
$langAddedU        = "ha sido agregado. Si ya escribió su direccción de e-mail, se le enviará un mensaje para comunicarle su nombre de usuario";
$langAndP          = "y su contraseña";
$langDereg         = "ha sido dado de baja de este curso";
$langAddAU         = "Agregar un usuario";
$langStudent       = "estudiante";
$langBegin         = "inicio.";
$langPreced50      = "50 anteriores";
$langFollow50      = "50 siguientes";
$langEnd           = "fin";
$langAdmR          = "Derechos de administración.";
$langUnreg         = "Dar de baja";
$langAddHereSomeCourses = "<font size=2 face='Arial, Helvetica'><big>Modificar la lista de cursos</big><br><br>Marque los cursos que desea seguir.<br>Deseleccione aquellos que no desea seguir más.<br> Luego haga clic en 'Aceptar' al final de la lista";
$langTitular = "Autor";
$langCanNotUnsubscribeYourSelf = "Usted no puede quitarse de un curso que administra, solamente otro administrador puede hacerlo.";

$langGroup="grupo";
$langUserNoneMasc="-";
$langTutor="Tutor";
$langTutorDefinition="Tutor (tiene derechos para supervisar grupos)";
$langAdminDefinition="Administrador (tiene derechos para modificar el contenido del sitio web del curso)";
$langDeleteUserDefinition="No registrado (borrado de la lsita de usuarios de  <b>este</b> curso)";
$langNoTutor = "no es tutor de este curso";
$langYesTutor = "es tutor de este curso";
$langUserRights="Derechos de usuarios";
$langNow="ahora";
$langOneByOne="Agregar usuarios manualmente";
$langUserMany="Importar una lista de usuarios desde un archivo de texto";
$langNo="no";
$langYes="si";
$langConfirmUnsubscribe = "Confirmar que quita al usuario";
$langUserAddExplanation="cada línea del archivo a enviar tendrá que incluír necesariamente 5 campos: <b>Nombre&nbsp;&nbsp;&nbsp;Apellido&nbsp;&nbsp;&nbsp;Usuario&nbsp;&nbsp;&nbsp;Contraseña&nbsp;&nbsp;&nbsp;Email</b> separados por tabuladores y en ese orden. Los usuarios recibirán un e-mail de conformación con su nombre de usuario/contraseña.";
$langSend="Enviar";
$langDownloadUserList="Actualizar lista";
$langUserNumber="número";
$langGiveAdmin="Hacer administrador";
$langRemoveRight="Quitar este derecho";
$langGiveTutor="Hacer tutor";
$langUserOneByOneExplanation="El(ella) recibirá un e-mail de confirmación con su nombre de usuario y contaseña";
$langBackUser="Volver a la lista de usuarios";
$langUserAlreadyRegistered="Un usuario con el mismo nombre/apellido ha sido inscripto en este curso. No puede registrarlo dos veces.";
$langAddedToCourse="está registrado en el campus, pero no en este curso. Ahora lo está. ";
$langGroupUserManagement="Aadministración de grupos";
$langIsReg="Sus modificaciones han sido registradas";
$langPassTooEasy ="esa contraseña es demasiado simple. Use una contraseña como ésta ";
$langIfYouWantToAddManyUsers="Si desea agregar una lista de usuarios en su curso, por favor contacte con su administrador del sitio web.";
$langCourses="cursos.";

$langLastVisits="Mi última visita";
$langSee		= "Ir A";
$langSubscribe	= "Inscribirse";
$langCourseName	= "Nombre del curso";
$langLanguage	= "Idioma";


$langConfirmUnsubscribe = "Confirme que borra a un usuario";
$langAdded = "Agregado";
$langDeleted = "Eliminado";
$langPreserved = "Preservado";

$langDate = "Fecha";
$langAction = "Acción";
$langLogin = "Ingreso";
$langLogout = "Desconexión";
$langModify = "Modificar";

$langUserName = "Nombre de usuario";


$langEdit = "Editar";
$langCourseManager = "Administrador del Curso";

?>
