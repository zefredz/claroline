<?php // $Id$
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.5.
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2004 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      |   Brazilian Portuguese Translation                                                |
      +----------------------------------------------------------------------+
      |   This program is free software; you can redistribute it and/or      |
      |   modify it under the terms of the GNU General Public License        |
      |   as published by the Free Software Foundation; either version 2     |
      |   of the License, or (at your option) any later version.             |
      +----------------------------------------------------------------------+

      +----------------------------------------------------------------------+
      | Translator :                                                         |
      |          Marcelo R. Minholi <minholi@unipar.br>                |
      +----------------------------------------------------------------------+
*/

$langModifInfo="Modificar informações do curso";
$langModifDone="As informações foram modificadas";
$langHome="Voltar para a Home Page";
$langCode="Código do curso";
$langDelCourse="Apagar esse curso";
$langProfessor="Professor";
$langProfessors="Professores";
$langTitle="Título do curso";
$langFaculty="Faculdade";
$langDescription="Descrição";
$langConfidentiality="Sigilo";
$langPublic="Acesso público na home page do campus sem login";
$langPrivOpen="Acesso privado, inscrições abertas";
$langPrivate="Acesso privado, inscrições fechadas (site acessível somente para pessoas na <a href=../user/user.php>Lista de usuários</a>)";
$langForbidden="Não permitido";
$langLanguage="Lingua";
$langConfTip="Por padrão seu curso está acessível apenas para você como seu único usuário inscrito. No caso de você necessitar de algum sigilo, 
o modo mais simples é abrir as inscrições durante
uma semana, pedir aos estudantes que se inscrevam, então fechar as inscrições e checar possíveis intrusos na lista de usuários.";
$langTipLang="Essa lingua será válida para todos os visitantes do website do seu curso.";
$langIntroCourse="Você está na página inicial do seu curso.<br><br>Nessa página, você pode :
<li class=HelpText>ativar ou desativar ferramentas (clique no botão '".$langEditToolList."' no canto inferior esquerdo).
<li class=HelpText>modificar configurações ou visualizar estatísticas (clique nos link correspondentes abaixo).<BR><BR>
Agora, para adicionar um texto introdutório apresentando o seu curso para os estudantes, clique nesse botão ";

// Change Home Page
$langEditToolList="Editar lista de ferramentas";
$langProgramMenu="Programa do curso";
$langStats="Estatísticas";
$langUplPage="Enviar página e anexá-la à página inicial";
$langLinkSite="Adicionar link para página na página inicial";
$langVid="Vídeo";



// delete_course.php
$langDelCourse="Apagar o website do curso inteiro";
$langCourse="O website ";
$langHasDel="foi apagado";
$langBackHome="Voltar para a Home Page do ";
$langByDel="Apagar esse curso irá remover permanentemente todos os documentos, seus conteúdos e todos os seus estudantes (não removendo-os de outros cursos).<p>Você realmente quer apagar o curso ";
$langY="Sim";
$langN="Não";

$langDepartmentUrl = "URL do Departamento";
$langDepartmentUrlName = "Departmento";
$langDescriptionCours  = "Descrição do curso";

$langArchive="Arquivo";
$langArchiveCourse = "Arquivar um curso";
$langRestoreCourse = "Restaurar um curso";
$langRestore="Restaurar";
$langCreatedIn = "criado em";
$langCreateMissingDirectories ="Criação dos diretórios que faltam";
$langCopyDirectoryCourse = "Copiar os arquivos do curso";
$langDisk_free_space = "espaço livre";
$langBuildTheCompressedFile ="Criação do arquivo de backup";
$langFileCopied = "arquivo copiado";
$langArchiveLocation="Local do arquivo";
$langSizeOf ="Tamanho do";
$langArchiveName ="Nome do arquivo";
$langBackupSuccesfull = "Arquivado com sucesso";
$langBUCourseDataOfMainBase = "Backup dos dados do curso do banco de dados principal para";
$langBUUsersInMainBase = "Backup dos dados de usuário do banco de dados principal para";
$langBUAnnounceInMainBase="Backup dos dados dos anúncios do banco de dados principal para";
$langBackupOfDataBase="Backup do banco de dados";
$langBackupCourse="Arquivar esse curso";

$langCreationDate = "Criado";
$langExpirationDate  = "Expirar";
$langPostPone = "Postar";
$langLastEdit = "Última edição";
$langLastVisit = "Última visita";

$langSubscription="Inscrição";
$langCourseAccess="Acesso ao curso";

$langDownload="Download";
$langConfirmBackup="Você realmente quer arquivar o curso";

$langCreateSite="Criar um website de curso";

$langRestoreDescription="O curso está em um arquivo que você pode selecionar abaixo.<br><br>
Quando você clicar em &quot;Restaurar&quot;, o arquivo será descompactado e o curso recriado.";
$langRestoreNotice="Esse script não permite ainda restaurar automaticamente os usuários, mas os dados salvos no &quot;users.csv&quot; são suficientes para que are sufficient so as for the administrator to be able to make that work manually.";
$langAvailableArchives="Lista de arquivos disponíveis";
$langNoArchive="Nenhum arquivo foi selecionado";
$langArchiveNotFound="O arquivo não foi encontrado";
$langArchiveUncompressed="O arquivo está descompactado e instalado.";
$langCsvPutIntoDocTool="O arquivo &quot;users.csv&quot; foi colocado dentro da ferramenta documentos.";

$langSearchCours	= "Return to the informations of the course";
$langManage			= "Manage Campus";

$langAreYouSureToDelete = "Você tem certeza que quer deletar ";
$langBackToAdminPage = "Voltar para a página de administração";
$langToCourseSettings = "Voltar para as configurações do curso";
$langSeeCourseUsers = "Visualizar usuários do curso";
$langBackToCourseList = "Voltar para a lista de cursos";
$langBackToList = "Voltar para a lista";
$langAllUsersOfThisCourse = "Membros do curso";
$langViewCourse = "Visualizar curso";


// course_home_edit.php

$langIntroEditToolList="Selecione as ferramentas que você quer tornar visíveis para seus usuários.
Ferramentas invisíveis serão retiradas de sua interface pessoal";
$langTools="Ferramentas";
$langActivate="Ativar";
$langAddExternalTool="Adicionar link externo";
$langAddedExternalTool="Ferramenta Externa adicionada.";
$langUnableAddExternalTool="Impossível adicionar ferramenta externa";
$langMissingValue="Faltando valor";
$langToolName="Nome do link";
$langToolUrl="URL do link";
$langChangedTool="Acesso às ferramentas modificado";
$langUnableChangedTool="Impossível modificar o acesso às ferramentas";
$langUpdatedExternalTool="Ferramenta externa atualizada";
$langUnableUpdateExternalTool="Impossível atualizar ferramenta externa";
$langDeletedExternalTool='Ferramenta externa deletada';
$langUnableDeleteExternalTool='Impossível deletar ferramenta externa';
$langAdministrationTools="Administração";
?>
