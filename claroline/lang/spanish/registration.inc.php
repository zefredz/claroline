<?php // $Id$

/*
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
	|vicm3 <vicm3@linux.ajusco.upn.mx>
	+----------------------------------------------------------------------|
    | Translation to Spanish v.1.5.1                                       |
    | Rodrigo Alejandro Parra Soto , Ing. (e) En Computación eInformatica  |
    | Concepción, Chile  <raparra@gmail.com>                               |
    +----------------------------------------------------------------------|

	"by moosh" in translation file = please check it. :-)
 */

// lostPassword  :
$lang_lost_password = "Contraseña extraviada";
$lang_your_password_has_been_emailed_to_you = "T&uacute; contraeña te ha sido enviada al correo que registraste";
$lang_no_user_account_with_this_email_address = "No existe ningun usuario registrado con esa direcci&oacute;n de correo electr&oacute;nico";
$lang_enter_email_and_well_send_you_password = "Escribe la direcci&oacute;n de correo electr&oacute;nico que usaste para darte de alta y te enviaremos tu contrase&ntilde;a";


// GENERIC

$langFirstname = "Nombre"; // by moosh
$langLastname = "Apellido"; // by moosh
$langEmail = "Email";// by moosh
$langRetrieve ="Recuperar los datos personales";// by moosh
$langMailSentToAdmin = "El correo se ha enviado al administrador.";// by moosh
$langAccountNotExist = "Cuenta no encontrada.<BR>".$langMailSentToAdmin." Buscar la de forma manual.<BR>";// by moosh
$langAccountExist = "La cuenta existe.<BR>".$langMailSentToAdmin."<BR>";// by moosh
$langWaitAMailOn = "El correo puede ser enviado a";// by moosh
$langCaseSensitiveCaution = "El sistema diferencia entre letras may&uacute;sculas y min&uacute;sculas.";// by moosh
$langDataFromUser = "Datos enviados por el usuario";// by moosh
$langDataFromDb = "Datos en la base de datos";// by moosh
$langLoginRequest = "Petici&oacute;n del nombre de usuario";// by moosh
$langExplainFormLostPass = "Escriba aqu&iacute; los datos que Vd. recuerda haber puesto cuando se inscribi&oacute;.";// by moosh
$langTotalEntryFound = "Entrada encontrada";// by moosh
$langEmailNotSent = "Si algo no funciona, envie un correo a";// by moosh
$langYourAccountParam = "Este es tu nombre de usuario y contrase&ntilde;a";// by moosh
$langTryWith ="Intenta con";// by moosh
$langInPlaceOf ="y no con";// by moosh
$langParamSentTo = "Informacion de la identificaci&oacute;n enviada a";// by moosh

$langModify="modificar";
$langDelete="borrar";
$langTitle="T&iacute;tulo";
$langHelp="ayuda";
$langOk="aceptar";
$langAddIntro="A&Ntilde;ADIR UN TEXTO DE INTRODUCCI&Oacute;N";
$langBackList="Regresar a la lista";
$langAddVarUser="Añadir varios usuarios a la vez";





// REGISTRATION - AUTH - inscription.php
$langRegistration  = "Inscripci&oacute;n";
$langName          = "Apellido";
$langSurname       = "Nombre";
$langUsername      = "Nombre de usuario";
$langPass          = "Clave de acceso";
$langConfirmation  = "confirmaci&oacute;n";
$langEmail         = "Correo electrónico";
$langStatus        = "Estatus";
$langRegStudent    = "Inscribirme a cursos (estudiante)";
$langRegAdmin      = "Crear sitios de cursos (Profesor)";


// inscription_second.php


$langRegistration  = "Inscripci&oacute;n";
$langPassTwice     = "No ha escrito la misma clave de acceso dos veces.
Use el bot&oacute;n de Regresar del navegador y vuelva a intentarlo.";

$langEmptyFields   = "No ha llenado todos los campos.
Use el bot&oacute;n de Regresar del navegador y vuelva a intentarlo.";

$langUserFree      = "El nombre de usuario que eligi&oacute; ya existe.
Use el bot&oacute;n de Regresar del navegador y eliga uno diferente.";

$langYourReg       = "Su inscripcion en";
$langDear          = "Estimado(a)";
$langYouAreReg     = "Usted ha sido inscrito en";
$langSettings      = "con los parámetros siguientes:\n\nNombre de usuario:";
$langAddress       = "La dirección de";
$langIs            = "es";
$langProblem       = "En caso de tener algún problema, no dude en contactarnos.";
$langFormula       = "Cordialmente";
$langManager       = "Responsable";
$langPersonalSettings = "Sus datos personales han sido registrados y ha sido enviado un correo electr&oacute;nico a su buz&oacute;n
para recordarle su nombre de usuario y su clave de acceso.</p>
Seleccione de la lista los cursos a los que desea tener acceso.";

$langNowGoChooseYourCourses ="ahora puedes ir y seleccionar en la lista los cursos a los que quieres acceder.";
$langNowGoCreateYourCourse  ="ahora puedes ir a crear tu curso";

$langYourRegTo     = "Su inscripci&oacute;n a";
$langIsReg         = "cursos ha sido registrada";
$langCanEnter      = "Ahora usted puede <a href=../../index.php>entrar al curso</a>";

// profile.php

$langModifProfile  = "Modificar mi perfil";
$langPassTwo       = "Usted no ha escrito la misma clave de acceso dos veces";
$langAgain         = "&iexcl;Vuelva a comenzar!";
$langFields        = "No ha rellenado todos los campos";
$langUserTaken     = "El nombre de usuario que ha elegido ya existe";
$langEmailWrong    = "La direcci&oacute;n de correo electr&oacute;nico que ha escrito est&aacute; incompleta o
contiene caracteres inv&aacute;lidos";
$langProfileReg    = "Su nuevo perfil de usuario ha sido guardado";
$langHome          = "Regresar a la p&aacute;gina de inicio";
$langMyStats = "Ver mis estad&iacute;sticas";

// user.php

$langUsers         = "Usuarios";
$langModRight      = "Modificar los derechos de administraci&oacute;n de";
$langNone          = "ninguno";
$langAll           = "todos";
$langNoAdmin       = "ahora no tiene <b>ning&uacute;n derecho de administraci&oacute;n sobre este sitio</b>";
$langAllAdmin      = "ahora tiene <b>todos los derechos de administraci&oacute;n de este sitio</b>";
$langModRole       = "Modificar el papel (rol) de";
$langRole          = " Papel (Rol)";
$langIsNow         = "es ahora";
$langInC           = "en este curso";
$langFilled        = "No ha rellenado todos los campos.";
$langUserNo        = "El nombre de usuario que eligi&oacute;";
$langTaken         = "ya existe. Elija uno diferente.";
$langOneResp       = "Uno de los administradores del curso";
$langRegYou        = "lo ha inscrito en este curso";
$langTheU          = "El usuario";
$langAddedU        = "ha sido a&ntilde;adido. Si ya escribi&oacute; su direccci&oacute;n electr&oacute;nica, se le enviar&aacute; un mensaje para comunicarle su nombre de usuario";
$langAndP          = "y su clave de acceso";
$langDereg         = "ha sido dado de baja de este curso";
$langAddAU         = "A&ntilde;adir un usuario";
$langStudent       = "estudiante";
$langBegin         = "inicio.";
$langPreced50      = "50 anteriores";
$langFollow50      = "50 siguientes";
$langEnd           = "fin";
$langAdmR          = "Derechos de administraci&oacute;n.";
$langUnreg         = "Dar de baja";
$langAddHereSomeCourses = "<font size=2 face='Arial, Helvetica'><big>Modificar lista de cursos</big><br><br>
Selecciona los cursos en los que quieres inscribirte.<br>
Deseleccione los cursos en los que no desea seguir inscrito.<br> Luego haga click en el bot&oacute;n Ok de la lista";
$langTitular = "Autor";
$langCanNotUnsubscribeYourSelf = "No se puede dar de baja de un curso que administra, s&oacute;lo otro administrador del curso puede hacerlo.";

$langGroup		= "Grupo";
$langUserNoneMasc	= "-";
$langTutor		= "Tutor";
$langTutorDefinition	= "Tutor (derecho a supervisar grupos)";
$langAdminDefinition	= "Administrador (derecho a modificar el contenido del curso)";
$langDeleteUserDefinition= "No registrado (borrar de la lista de usuarios de <b>este</b> curso)";
$langNoTutor 		= "no ser tutor en este curso";
$langYesTutor 		= "ser tutor en este curso";
$langUserRights		= "Derechos de usuario";
$langNow		= "ahora";
$langOneByOne		= "A&ntilde;adir un usuario de forma manual";
$langUserMany		= "Importar lista de usuarios a trav&eacute;s de un fichero .txt";
$langNo			= "no";
$langYes		= "si";
$langUserAddExplanation	= "Cada linea del archivo que env&iacute;e necesariamente tiene que incluir
todos y cada uno de estos 5 campos (y ninguno m&aacute;s):  <b>Nombre&nbsp;&nbsp;&nbsp;Apellidos&nbsp;&nbsp;&nbsp;
		Nombre de usuario&nbsp;&nbsp;&nbsp;Clave&nbsp;
		&nbsp;&nbsp;Correo Electr&oacute;nico</b> separadas por tabuladores y en &oacute;ste orden.
		Los usuarios recibir&aacute;n un correo de confirmaci&oacute;n son su nombre de usuario y contrase&ntilde;a.";
$langSend		= "Enviar";
$langDownloadUserList	= "Subir lista";
$langUserNumber		= "n&uacute;mero";
$langGiveAdmin		= "Ser administrador";
$langRemoveRight	= "Retirar los derechos";
$langGiveTutor		= "Ser tutor";
$langUserOneByOneExplanation = "El (ella) recibir&aacute; un correo electr&oacute;nico de confirmaci&oacute;n con el nombre de usuario y la contrase&ntilde;a";
$langBackUser		= "Regresar a la lista de usuarios";
$langUserAlreadyRegistered = "Un usuario con el mismo nombre ya ha sido registrado en este curso.";

$langAddedToCourse		="ha sido registrado en su curso";
$langGroupUserManagement	="Gesti&oacute;n de Grupos";
$langIsReg			="Sus cambios han sido guardados";
$langPassTooEasy		="esta contrase&ntilde;a es demasiado simple. Use una como esta";

$langIfYouWantToAddManyUsers = "Si quieres a&ntilde;adir una lista de usuarios a su curso, contacte con el administrador.";

$langCourses =	"cursos";

$langLastVisits	="Mis &uacute;ltimas visitas";
$langSee	= "Ir a";
$langSubscribe	= "Subscribe";
$langCourseName	= "Nombre del curso";
$langLanguage	= "Idioma";

$langConfirmUnsubscribe = "Confirmar desuscribir usuario";
$langAdded 		= "A&ntilde;adir";
$langDeleted 		= "Borrar";
$langPreserved 		= "Proteger";

$langDate 	= "Fecha";
$langAction 	= "Acci&oacute;n";
$langLogin 	= "Entrar";
$langLogout 	= "Salir";
$langModify 	= "Modificar";

$langUserName 	= "Nombre de usuario";


$langEdit 		= "Editar";
$langCourseManager 	= "Responsable del curso";
$langAddImage= "Incluir foto";
$langImageWrong="El archivo de imagen debe tener un tamaño menor de";



///////////////////////////////////////////////////////
//agregados por Rodrigo Parra Soto
$langLostPassword = "Clave de acceso olvidada";
$langPasswordHasBeenEmailed = "Su clave de acceso ha sido enviado al e-mail ";
$langEmailAddressNotFound = "No existe la cuenta de usuario con este e-mail.";
$langEnterMail = "Introduzca su e-mail para que se le envíe a ese correo su clave de acceso.";
$langPlatformAdmin = "Administradór de laplataforma";
$langPhone = "Teléfono";
$langSaveChange ="Guardar cambios";
$langRegister = "Registrar";
$langReturnSearchUser="Volver a usuarios";
$langManage              = "Administrar Campus";
$langAdministrationTools = "Administración";
$langUpdateImage = "Cambiar la imágen"; //by Moosh
$langDelImage = "Eliminar la imágen"; 	//by Moosh
$langOfficialCode = "Código administrativo";
$langAuthInfo = "Autentificación";
$langEnter2passToChange = "Ingrese su clave de acceso dos veces para que pueda ser cambiada o dejelo vacío si quiere conservar el actual";
$langConfirm = "Confirmar";
$lang_SearchUser_ModifOk            = "Actualización no disponible";

$langNoUserSelected = "No se ha seleccionado a ningun usuario!";

// dialogbox messages

$langUserUnsubscribed = "El usuario a sido exitosamente desincribido del curso";
$langUserNotUnsubscribed = "Error!! Usted no puede desincribir al administrador del curso";

///////////////////////////////////////////////////////
?>