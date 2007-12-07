<?php // $Id$

/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.5.*                           |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2004 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      |   $Id$         |
      +----------------------------------------------------------------------+

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
      +----------------------------------------------------------------------|
      | Translation to Galician                                              |
      | e-learning dept CESGA <teleensino@cesga.es >                         |
      |                                                                      |
      +----------------------------------------------------------------------|
 */


/***************************************************************
*                   Language translation
****************************************************************
GOAL
****
Translate the interface in chosen language

*****************************************************************/



// GENERIC

$langFirstname = "Nome"; // by moosh
$langLastname = "Apelido"; // by moosh
$langEmail = "Correo electr&oacute;nico";// by moosh
$langRetrieve ="Recupera-los datos persoais";// by moosh
$langMailSentToAdmin = "O correo remitiuse &oacute; administrador.";// by moosh
$langAccountNotExist = "Non se atopou a conta.<BR>".$langMailSentToAdmin." Busca-la forma manual.<BR>";// by moosh
$langAccountExist = "A conta xa existe.<BR>".$langMailSentToAdmin."<BR>";// by moosh
$langWaitAMailOn = "O correo pode ser enviado a";// by moosh
$langCaseSensitiveCaution = "O sistema diferencia entre letras mai&uacute;sculas e min&uacute;sculas.";// by moosh
$langDataFromUser = "Datos enviados polo usuario";// by moosh
$langDataFromDb = "Datos na base de datos";// by moosh
$langLoginRequest = "Petici&oacute;n do nome de usuario";// by moosh
$langExplainFormLostPass = "Escriba aqu&iacute; os datos que Vde. recorde que puxo cando se inscrib&iacute;u.";// by moosh
$langTotalEntryFound = "Entrada atopada";// by moosh
$langEmailNotSent = "Si algo non funciona, envie un correo a";// by moosh
$langYourAccountParam = "Este &eacute; o teu nome de usuario e contrasinal";// by moosh
$langTryWith ="Intenta con";// by moosh
$langInPlaceOf ="e non con";// by moosh
$langParamSentTo = "Informacion da identificaci&oacute;n enviada a";// by moosh

$langModify="modificar";
$langDelete="borrar";
$langTitle="T&iacute;tulo";
$langHelp="axuda";
$langOk="aceptar";
$langAddIntro="ENGADIR UN TEXTO DE INTRODUCCI&Oacute;N";
$langBackList="Voltar &aacute; lista";





// REGISTRATION - AUTH - inscription.php
$langRegistration  = "Inscripci&oacute;n";
$langName          = "Apelido";
$langSurname       = "Nome";
$langUsername      = "Nome de usuario";
$langPass          = "Clave de acceso";
$langConfirmation  = "confirmaci&oacute;n";
$langEmail         = "Correo electr&oacute;nico";
$langStatus        = "Estatus";
$langRegStudent    = "Inscribirme a cursos (estudiante)";
$langRegAdmin      = "Crear sitios de cursos (Profesor)";


// inscription_second.php


$langRegistration  = "Inscripci&oacute;n";
$langPassTwice     = "Non escrib&iacute;u a mesma clave de acceso d&uacute;as veces.
Use o bot&oacute;n de Volver do navegador e volva intentalo.";

$langEmptyFields   = "Non cubriu todos os campos.
Use o bot&oacute;n de Volver do navegador e volva intentalo.";

$langUserFree      = "O nome de usuario que elixiu xa existe.
Use o bot&oacute;n de Volver do navegador e elixa un diferente.";

$langYourReg       = "A s&uacute;a inscripci&oacute;n en";
$langDear          = "Estimado(a)";
$langYouAreReg     = "Vostede inscrib&iacute;use en";
$langSettings      = "cos par&aacute;metros seguintes:\nNome de usuario:";
$langAddress       = "O enderezo de";
$langIs            = "&eacute;";
$langProblem       = "En caso de tener alg&uacute;n problema, no dude en contactarnos.";
$langFormula       = "Cordialmente";
$langManager       = "Responsable";
$langPersonalSettings = "Os seus datos personais xa foron rexistrados e xa se enviou un correo electr&oacute;nico &oacute; seu buz&oacute;n
para recordarlle o seu nome de usuario e a s&uacute;a clave de acceso.</p>
Seleccione da lista os cursos &oacute;s que desexa ter acceso.";

$langNowGoChooseYourCourses ="agora podes ir e seleccionar na lista os cursos &oacute;s que queres acceder.";
$langNowGoCreateYourCourse  ="agora podes ir a crear o teu curso";

$langYourRegTo     = "A s&uacute;a inscripci&oacute;n a";
$langIsReg         = "cursos xa foi rexistrada";
$langCanEnter      = "Agora vostede pode <a href=../../index.php>entrar &oacute; curso</a>";

// profile.php

$langModifProfile  = "Modificar o meu perfil";
$langPassTwo       = "Vostede non escrib&iacute;u a mesma clave de acceso d&uacute;as veces";
$langAgain         = "&iexcl;Volva a empezar!";
$langFields        = "Non cubr&iacute;u todos os campos";
$langUserTaken     = "O nome de usuario que escolleu xa existe";
$langEmailWrong    = "O enderezo de correo electr&oacute;nico que escrib&iacute;u est&aacute; incompleto ou
cont&eacute;n caracteres inv&aacute;lidos";
$langProfileReg    = "O seu novo perfil de usuario xa se gardou";
$langHome          = "Volver &aacute; p&aacute;xina de inicio";
$langMyStats = "Ver as mi&ntilde;as estad&iacute;sticas";

// user.php

$langUsers         = "Usuarios";
$langModRight      = "Modificar os dereitos de administraci&oacute;n de";
$langNone          = "ning&uacute;n";
$langAll           = "todos";
$langNoAdmin       = "agora non t&eacute;n <b>ning&uacute;n dereito de administraci&oacute;n sobre este sitio</b>";
$langAllAdmin      = "agora ten <b>todos os dereitos de administraci&oacute;n deste sitio</b>";
$langModRole       = "Modificar o papel (rol) de";
$langRole          = " Papel (Rol)";
$langIsNow         = "&eacute; agora";
$langInC           = "neste curso";
$langFilled        = "Non cubr&iacute;u todos os campos.";
$langUserNo        = "O nome de usuario que elix&iacute;u";
$langTaken         = "xa existe. Escolla un diferente.";
$langOneResp       = "Un dos administradores do curso";
$langRegYou        = "inscrib&iacute;uno neste curso";
$langTheU          = "O usuario";
$langAddedU        = "engad&iacute;use. Se xa escrib&iacute;u o seu enderezo electr&oacute;nica, remitir&aacute;selle unha mensaxe para comunicarlle o seu nome de usuario";
$langAndP          = "e a s&uacute;a clave de acceso";
$langDereg         = "deuse de baixa deste curso";
$langAddAU         = "Engadir un usuario";
$langStudent       = "estudiante";
$langBegin         = "inicio.";
$langPreced50      = "50 anteriores";
$langFollow50      = "50 seguintes";
$langEnd           = "fin";
$langAdmR          = "Dereitos de administraci&oacute;n.";
$langUnreg         = "Dar de baixa";
$langAddHereSomeCourses = "<font size=2 face='Arial, Helvetica'><big>Modificar a lista de cursos</big><br><br>
Selecciona os cursos nos que queres inscribirte.<br>
Deselecciona os cursos nos que non desexa seguir inscrito.<br> Despois prema no bot&oacute;n Ok da lista";
$langTitular = "Autor";
$langCanNotUnsubscribeYourSelf = "Non se pode dar de baixa dun curso que administra, s&oacute; outro administrador do curso pode facelo.";

$langGroup="Grupo";
$langUserNoneMasc="-";
$langTutor="Titor";
$langTutorDefinition="Titor (dereito a supervisar grupos)";
$langAdminDefinition="Administrador (dereito a modificar o contido do curso)";
$langDeleteUserDefinition="Non rexistrado (borrar da lista de usuarios do <b>este</b> curso)";
$langNoTutor = "non ser titor neste curso";
$langYesTutor = "ser titor neste curso";
$langUserRights="Dereitos de usuario";
$langNow="agora";
$langOneByOne="Engadir un usuario de forma manual";
$langUserMany="Importar lista de usuarios a trav&eacute;s dun ficheiro .txt";
$langNo="non";
$langYes="si";
$langUserAddExplanation="Cada li&ntilde;a do arquivo que env&iacute;e necesariamente ten que incluir
todos e cada un destes 5 campos (e ning&uacute;no m&aacute;is):  <b>Nome&nbsp;&nbsp;&nbsp;Apelidos&nbsp;&nbsp;&nbsp;
		Nome de usuario&nbsp;&nbsp;&nbsp;Contrasinal&nbsp;
		&nbsp;&nbsp;Correo Electr&oacute;nico</b> separadas por tabuladores e nesta orde.
		Os usuarios recibir&aacute;n un correo de confirmaci&oacute;n co seu nome de usuario e contrasinal.";
$langSend="Enviar";
$langDownloadUserList="Subir lista";
$langUserNumber="n&uacute;mero";
$langGiveAdmin="Ser administrador";
$langRemoveRight="Retirar os dereitos";
$langGiveTutor="Ser titor";
$langUserOneByOneExplanation="El (ela) recibir&aacute; un correo electr&oacute;nico de confirmaci&oacute;n co nome de usuario e o contrasinal";
$langBackUser="Volver &aacute; lista de usuarios";
$langUserAlreadyRegistered="Un usuario co mesmo nome xa se rexistrou neste curso.";

$langAddedToCourse="rexistrouse no seu curso";
$langGroupUserManagement="Xesti&oacute;n de Grupos";
$langIsReg="Gard&aacute;ronse os seus cambios";
$langPassTooEasy ="este contrasinal &eacute; demasiado simple. Use un como este";

$langIfYouWantToAddManyUsers="Se queres engadir unha lista de usuarios &oacute; teu curso, contacte co administrador.";

$langCourses="cursos.";

$langLastVisits="As mi&ntilde;as &uacute;ltimas visitas";
$langSee		= "Ir a";
$langSubscribe	= "Subscribe";
$langCourseName	= "Nome do curso";
$langLanguage	= "Idioma";

$langConfirmUnsubscribe = "Confirmar desuscribir usuario";
$langAdded = "Engadir";
$langDeleted = "Borrar";
$langPreserved = "Protexer";

$langDate = "Data";
$langAction = "Acci&oacute;n";
$langLogin = "Entrar";
$langLogout = "Sa&iacute;r";
$langModify = "Modificar";

$langUserName = "Nome de usuario";


$langEdit = "Editar";
$langCourseManager = "Xestor do curso";
$langAddVarUser="Engadir unha lista de usuarios";
$langAddImage= "Engadir foto";
$langImageWrong="O arquivo de imagen debe ser menor de";
?>
