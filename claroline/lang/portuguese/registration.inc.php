<?php // $Id$

/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.5.*                              |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2004 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      |   $Id$    |
      +----------------------------------------------------------------------+

      +----------------------------------------------------------------------+
      | Authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
      |          Hugues Peeters    <peeters@ipm.ucl.ac.be>                   |
      +----------------------------------------------------------------------|
      | Translation to European Portuguese (pt_PT):                          |
      | Dionisio Martínez Soler  <dmsoler@edu.xunta.es >                     |
      | 	(Escola Oficial de Idiomas de Vigo, Spain)                   |
      +----------------------------------------------------------------------|
 */


// GENERIC

$langFirstname = "Primeiro nome"; // by moosh
$langLastname = "&Uacute;ltimo nome"; // by moosh
$langEmail = "Correio electr&oacute;nico";// by moosh
$langRetrieve ="Recuperar os dados pessoais";// by moosh
$langMailSentToAdmin = "A mensagem foi enviada ao administrador.";// by moosh
$langAccountNotExist = "Conta n&atilde;o encontrada.<BR>".$langMailSentToAdmin." Procure-a de forma manual.<BR>";// by moosh
$langAccountExist = "A conta existe.<BR>".$langMailSentToAdmin."<BR>";// by moosh
$langWaitAMailOn = "O correio pode ser enviado a";// by moosh
$langCaseSensitiveCaution = "O sistema distingue letras mai&uacute;sculas e min&uacute;sculas.";// by moosh
$langDataFromUser = "Dados enviados pelo utilizador";// by moosh
$langDataFromDb = "Dados na base de dados";// by moosh
$langLoginRequest = "Pedido do nome de utilizador";// by moosh
$langExplainFormLostPass = "Escreva aqui os dados que recorde ter fornecido quando se inscreveu.";// by moosh
$langTotalEntryFound = "Registo encontrado";// by moosh
$langEmailNotSent = "Se alguma coisa correr mal, envie uma mensagem para";// by moosh
$langYourAccountParam = "Estes s&atilde;o o seu nome de utilizador e a sua senha";// by moosh
$langTryWith ="Tente com";// by moosh
$langInPlaceOf ="em vez de";// by moosh
$langParamSentTo = "Informa&ccedil;&atilde;o da identifica&ccedil;&atilde;o enviada para";// by moosh

$langModify   = "alterar";
$langDelete   = "apagar";
$langTitle    = "T&iacute;tulo";
$langHelp     = "ajuda";
$langOk       = "validar";
$langAddIntro = "Acrescentar um texto de apresenta&ccedil;&atilde;o";
$langBackList = "Voltar &agrave; lista";
$langAddVarUser="Acrescentar v&aacute;rios utilizadores ao mesmo tempo";


// REGISTRATION - AUTH - inscription.php
$langRegistration  = "Inscri&ccedil;&atilde;o";
$langName          = "&Uacute;ltimo nome";
$langSurname       = "Primeiro nome";
$langUsername      = "Nome de utilizador";
$langPass          = "Senha";
$langConfirmation  = "confirma&ccedil;&atilde;o";
$langEmail         = "Correio electr&oacute;nico";
$langStatus        = "Estatuto";
$langRegStudent    = "Inscrever-se em cursos (estudante)";
$langRegAdmin      = "Criar cursos (professor)";

// inscription_second.php

$langRegistration  = "Inscri&ccedil;&atilde;o";
$langPassTwice     = "N&atilde;o escreveu a mesma senha as duas vezes.
Utilize o bot&atilde;o de Regressar do seu navegador e tente de novo.";

$langEmptyFields   = "N&atilde;o preencheu todos os campos.
Utilize o bot&atilde;o de Regressar do seu navegador e tente de novo.";

$langUserFree      = "O nome de utilizador que escolheu j&aacute; est&aacute; a ser usado por outro utilizador.
Utilize o bot&atilde;o de Regressar do seu navegador e escolha outro diferente.";

$langYourReg       = "A sua inscri&ccedil;&atilde;o em";
$langDear          = "Caro(a)";
$langYouAreReg     = "Foi inscrito em";
$langSettings      = "com os seguintes par&aacute;metros:\n\n\nNome de utilizador:";
$langAddress       = "A morada de";
$langIs            = "&eacute;";
$langProblem       = "Caso tenha algum problema, n&atilde;o hesite em nos contactar.";
$langFormula       = "Com os melhores cumprimentos";
$langManager       = "Respons&aacute;vel";
$langPersonalSettings = "Os seus dados pessoais foram registados e foi enviada uma mensagem &agrave; sua caixa de correio electr&oacute;nico para lhe lembrar o seu nome de utilizador e a sua senha.</p>
Escolha na lista os cursos a que deseja aceder.";

$langNowGoChooseYourCourses ="agora pode escolher na lista os cursos a que deseja aceder.";
$langNowGoCreateYourCourse  ="agora pode criar o seu curso";

$langYourRegTo     = "A sua inscri&ccedil;&atilde;o em";
$langIsReg         = "cursos foi registada";
$langCanEnter      = "Agora pode <a href=../../index.php>entrar no curso</a>";

// profile.php

$langModifProfile  = "Alterar o meu perfil";
$langPassTwo       = "N&atilde;o escreveu a mesma senha as duas vezes.";
$langAgain         = "Tente de novo!";
$langFields        = "N&atilde;o preencheu todos os campos";
$langUserTaken     = "O nome de utilizador que escolheu j&atilde; est&aacute; a ser usado por outro utilizador.";
$langEmailWrong    = "A morada de correio electr&oacute;nico que escreveu n&atilde;o est&aacute; completa ou cont&eacute;m caracteres n&atilde;o v&aacute;lidos";
$langProfileReg    = "O seu novo perfil de utilizador foi guardado com sucesso";
$langHome          = "Voltar &agrave; p&aacute;gina de in&iacute;cio";
$langMyStats = "Ver as minhas estat&iacute;sticas";

// user.php

$langUsers         = "Utilizadores";
$langModRight      = "Modificar os direitos de administra&ccedil;&atilde;o de";
$langNone          = "nenhum";
$langAll           = "todos";
$langNoAdmin       = "agora n&atilde;o tem <b>nenhum direito de administra&ccedil;&atilde;o sobre este site</b>";
$langAllAdmin      = "agora tem <b>todos os direitos de administra&ccedil;&atilde;o sobre este site</b>";
$langModRole       = "Alterar o estatuto de";
$langRole          = "Estatuto";
$langIsNow         = "&eacute; agora";
$langInC           = "neste curso";
$langFilled        = "N&atilde;o preencheu todos os campos.";
$langUserNo        = "O nome de utilizador que escolheu";
$langTaken         = "j&atilde; est&aacute; a ser usado por outro utilizador. Escolha outro diferente.";
$langOneResp       = "Um dos administradores do curso";
$langRegYou        = "inscreveu-o neste curso";
$langTheU          = "O utilizador";
$langAddedU        = "foi acrescentado. Se j&aacute; tiver fornecido a sua morada de correio electr&oacute;nico, ser-lhe-&aacute; enviada uma mensagem para o informar do seu nome de utilizador";
$langAndP          = "e da sua senha";
$langDereg         = "foi eliminado deste curso, cancelando a sua inscri&ccedil;&atilde;o";
$langAddAU         = "Acrescentar um utilizador";
$langStudent       = "estudante";
$langBegin         = "in&iacute;cio.";
$langPreced50      = "50 anteriores";
$langFollow50      = "50 seguintes";
$langEnd           = "fim";
$langAdmR          = "Direitos de administra&ccedil;&atilde;o.";
$langUnreg         = "Cancelar a inscri&ccedil;&atilde;o";
$langAddHereSomeCourses = "<font size=2 face='Arial, Helvetica'><big>Alterar a lista de cursos</big><br><br>
Escolha os cursos em que deseja inscrever-se.<br>
Apague a marca de selec&ccedil;&atilde;o dos cursos em que n&atilde;o deseja continuar inscrito.<br>
Depois carregue no bot&atilde;o 'Validar' da lista";
$langTitular = "Autor";
$langCanNotUnsubscribeYourSelf = "N&atilde;o pode cancelar a sua inscri&ccedil;&atilde;o num curso de que &eacute; o administrador: apenas outro administrador pode cancelar a sua inscri&ccedil;&atilde;o.";

$langGroup		= "Grupo";
$langUserNoneMasc	= "-";
$langTutor		= "Orientador";
$langTutorDefinition	= "Orientador (tem o direito de supervisionar grupos)";
$langAdminDefinition	= "Administrador (tem o direito de alterar o conte&uacute;do do curso)";
$langDeleteUserDefinition= "N&atilde;o registado (apagar da lista de utilizadores <b>deste</b> curso)";
$langNoTutor 		= "n&atilde;o ter o estatuto de orientador neste curso";
$langYesTutor 		= "ter o estatuto de orientador neste curso";
$langUserRights		= "Direitos do utilizador";
$langNow		= "agora";
$langOneByOne		= "Acrescentar um utilizador de forma manual";
$langUserMany		= "Importar uma lista de utilizadores contida num ficheiro .txt";
$langNo			= "n&atilde;o";
$langYes		= "sim";
$langUserAddExplanation	= "Cada linha do ficheiro que enviar tem que conter obrigatoriamente os 5 campos seguintes (e mais nenhum), sem qualquer omiss&atilde;o:
  <b>Primeiro nome&nbsp;&nbsp;&nbsp;&Uacute;ltimo nome&nbsp;&nbsp;&nbsp;
		Nome de utilizador&nbsp;&nbsp;&nbsp;Senha&nbsp;
		&nbsp;&nbsp;Correio Electr&oacute;nico</b> separados por tabuladores e nesta ordem.
Os utilizadores receber&atilde;o uma mensagem de confirma&ccedil;&atilde;o com o seu nome de utilizador e a sua senha atrav&eacute;s do correio electr&oacute;nico indicado.";
$langSend		= "Enviar";
$langDownloadUserList	= "Transferir ficheiro com a lista";
$langUserNumber		= "n&uacute;mero";
$langGiveAdmin		= "Ser administrador";
$langRemoveRight	= "Retirar os direitos";
$langGiveTutor		= "Ser orientador";
$langUserOneByOneExplanation = "Vai receber uma mensagem de confirma&ccedil;&atilde;o com o seu nome de utilizador e a sua senha atrav&eacute;s do correio electr&oacute;nico.";
$langBackUser		= "Voltar &agrave; lista de utilizadores";
$langUserAlreadyRegistered = "Um utilizador com o mesmo nome j&aacute; foi registado neste curso.";

$langAddedToCourse		="foi registado no seu curso";
$langGroupUserManagement	="Gest&atilde;o de Grupos";
$langIsReg			="As mudan&ccedil;as que efectuou foram guardadas com sucesso";
$langPassTooEasy		="esta senha &eacute; demasiado simples. Utiliza uma semelhante a esta";

$langIfYouWantToAddManyUsers = "Se quer acrescentar uma lista de utilizadores ao seu curso, contacte o administrador.";

$langCourses =	"cursos";

$langLastVisits	="As minhas &uacute;ltimas visitas";
$langSee	= "Ir a";
$langSubscribe	= "Inscreve-se";
$langCourseName	= "Nome do curso";
$langLanguage	= "L&iacute;ngua";

$langConfirmUnsubscribe = "Confirmar o cancelamento da inscri&ccedil;&atilde;o do utilizador";
$langAdded 		= "Acrescentar";
$langDeleted 		= "Apagar";
$langPreserved 		= "Proteger";

$langDate 	= "Data";
$langAction 	= "Ac&ccedil;&atilde;o";
$langLogin 	= "Entrar";
$langLogout 	= "Sair";
$langModify 	= "Alterar";

$langUserName 	= "Nome de utilizador";


$langEdit 		= "Editar";
$langCourseManager 	= "Respons&aacute;vel pelo curso";
?>
