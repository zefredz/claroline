<?php # $Id$
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.5.*
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2003 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      | Authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
      |          Hugues Peeters    <peeters@ipm.ucl.ac.be>                   |
      |      	 Christophe Gesché  <gesche@ipm.ucl.ac.be>                   |
      +----------------------------------------------------------------------+
	  |   Brazilian Portuguese Translation                                                |
      +----------------------------------------------------------------------+
      | Translator :                                                         |
      |          Marcelo R. Minholi <minholi@unipar.br>                |
      +----------------------------------------------------------------------+
 */

$langEG 			= "ex.";
$langDBConnectionParameters = "Parâmetros de conexão do mysql";
$lang_Note_this_account_would_be_existing ="Nota : essa conta deveria existir";
$langDBHost			= "Servidor de Banco de Dados";
$langDBLogin		= "Usuário do Banco de Dados";
$langDBPassword 	= "Senha do Banco de Dados";
$langDBNamesRules	= "Nomes da base de dados";
$langMainDB			= "BD principal do Claroline";
$langStatDB             = "BD de rastreamento. Útil apenas com vários BD";
$langPMADB			= "DB para extensão do PhpMyAdmin";// show in multi DB
$langDbName			= "Nome da BD"; // show in single DB
$langDBUse			= "Utilização da base de dados";
$langEnableTracking     = "Habilitar Rastreamento";
$langAllFieldsRequired	= "todos os campos requeridos";
$langPrintVers			= "Versão imprimível";
$langLocalPath			= "Caminho local correspondente";
$langAdminEmail			= "E-mail do Administrador";
$langAdminName			= "Nome do Administrador";
$langAdminSurname		= "Sobrenome do Administrator";
$langAdminLogin			= "Login do Administrador";
$langAdminPass			= "Senha do Administrator";
$langEducationManager	= "Responsável Educacional";
$langHelpDeskPhone		= "Telefone do Helpdesk";
$langCampusName			= "Nome do seu campus";
$langInstituteShortName = "Nome fantasia da instituição";
$langInstituteName		= "URL da sua instituição";


$langDBSettingIntro		= "
				O script de instalação irá criar o banco de dados principal do claroline. Note que o Claroline irá precisar criar vários BDs (a não ser que você selecione a opção \"Um\" abaixo). Se você tiver permissão para apenas um BD para o seu website dada pelo serviço de hospedagem, o Claroline não irá funcionar.";
$langDBSettingAccountIntro		= "
				O Claroline foi concebido para trabalhar com vários DBs mas ele pode trabalhar com apenas um,
				Para trabalhar com vários DBs, sua conta precisa ter direitos de criação.<BR>
				Se tem permissão para apenas um 
				DB para o seu website no seu Serviço de Hospedagem, Você precisa selecionar a opção \"Um\" abaixo.";
$langDBSettingNamesIntro		= "
				O script de instalação irá criar a base de dados principal do claroline. 
				Você pode criar bases de dados diferentes
				para o rastreamento e para a extensão do PhpMyAdmin se você quiser
				ou armazenar todas essas coisas em uma base de dados, como você quiser. 
				Posteriormente, o Claroline irá criar uma nova base de dados para cada novo curso criado. 
				Você pode especificar o prefixo para esses nomes de bases de dados.
				<p>
				Se você tem permissão para usar apenas uma base de dados, 
				volte para a página anterior e selecione a opção \"Único\"
				</p>
				";
$langDBSettingNameIntro		= "
				O script de instalação irá criar tabelas para o claroline, rastreamento e PhpMyAdmin na sua
				Base de Dados.
				Escolha o nome para essa Base de Dados e um prefixo para futuras tabelas de cursos.<BR>
				Se você tiver permissão para criar vários BD, volte para a página anterior e selecione a opção \"Vários\".
				Isso lhe oferecerá um uso muito mais convidativo";
$langStep1 			= "Passo 1 de 7 ";
$langStep2 			= "Passo 2 de 7 ";
$langStep3 			= "Passo 3 de 7 ";
$langStep4 			= "Passo 4 de 7 ";
$langStep5 			= "Passo 5 de 7 ";
$langStep6 			= "Passo 6 de 7 ";
$langStep7 			= "Passo 7 de 7 ";
$langCfgSetting		= "Configurações";
$langDBSetting 		= "Configurações do banco de dados MySQL";
$langMainLang 		= "Língua principal";
$langLicence		= "Licença";
$langLastCheck		= "Última verificação antes da instalação";
$langRequirements	= "Requisitos";

$langDbPrefixForm	= "Prefixo para o nome do BD dos cursos";
$langTbPrefixForm	= "Prefixo para o nome das tabelas dos cursos";
$langDbPrefixCom	= "ex. 'CL_'";
$langEncryptUserPass	= "Encriptar senhas de usuário no banco de dados";
$langSingleDb	= "Utilizar um ou muitos BD para o Claroline";


$langWarningResponsible = "Use esse script apenas depois de fazer backup. O time do Claroline não é responsável se você perder ou corromper dados";
$langAllowSelfReg	=	"Permitir auto-cadastramento";
$langAllowSelfRegProf =	"Permitir auto-cadastramento como criador de curso";
$langRecommended	=	"(recomendado)";


?>
