<?php // $Id$
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.3.0 $Revision$                             |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2002 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      |   Brazilian Portuguese Translation                                   |
      +----------------------------------------------------------------------+
      | Authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
      |          Hugues Peeters    <peeters@ipm.ucl.ac.be>                   |
      |          Christophe Gesché <gesche@ipm.ucl.ac.be>                    |
      +----------------------------------------------------------------------+
      | Translator :                                                         |
      |          Marcelo R. Minholi <minholi@unipar.br>                      |
      +----------------------------------------------------------------------+
 */
// add_course
$langNewCourse 			= "Novo Curso";
$langAddNewCourse 		= "Adicionar um novo curso";
$langRestoreCourse		= "Restaurar um curso";
$langOtherProperties  		= "Outras propriedades encontradas no arquivo";
$langSysId 			= "Identificação do Sistema";
$langDescription  		= "Descrição";
$langDepartment	  		= "Departamento";
$langDepartmentUrl	  	= "Url";
$langScoreShow  		= "Mostrar notas";
$langVisibility  		= "Visibilidade";
$langVersionDb  		= "Versão do banco de dados no momento do arquivamento";
$langVersionClaro  		= "Versão do claroline durante o arquivamento";
$langLastVisit  		= "Última visita";
$langLastEdit  			= "Última contribuição";
$langExpire 			= "Expiração";
$langChoseFile 			= "Selecionar arquivo";
$langFtpFileTips 		= "Se o arquivo estiver em um terceiro computador e acessível por ftp";
$langLocalFileTips		= "Se o arquivo estiver no espaço de armazenamento dos cursos desse campus";
$langHttpFileTips		= "Se o arquivo estiver em um terceiro computador e acessível por HTTP";
$langPostFileTips		= "Se o arquivo estiver em seu computador";
$langEmail			= "E-mail";

// create_course.php
$langLn="Lingua";


$langCreateSite="Criar um site de curso";
$langFieldsRequ="Todos os campos requeridos";
$langTitle="Título do curso";
$langEx="e.g. <i>Historia da Literatura</i>";
$langFac="Categoria";
$langTargetFac="Esta é a faculdade, departamento ou escola onde esse curso será disponibilizado"; 
$langCode="Código do curso";
$langMax="máx. de 12 caracteres, ex.: <i>ROM2121</i>";
$langDoubt="Se você tem dúvidas sobre o código do curso, consulte, ";
$langProgram="Programa do Curso</a>. Se seu curso não possui código, independente da razão, invente um. Por exemplo <i>INOVACAO</i> se seu curso for sobre Gerenciamento de Inovações";
$langProfessors="Professor(es)";
$langExplanation="Assim que clicar em OK, um site com Fórum, Agenda, Gerenciador de documentos, etc. será criado. Seu login, como criador do site, permite a você modifica-lo de acordo com suas necessidades.";
$langEmpty="Você deixou alguns campos vazios.<br>Use o botão <b>Voltar</b> do seu browser e tente novamente.<br>Se você não sabe o código do seu curso, veja o Programa do Curso";
$langCodeTaken="Esse código de curso já está em uso.  <br>Use o botão <b>Voltar</b> do seu browser e tente novamente";
$langBackToAdmin = "Voltar para a página de administração";
$langAnotherCreateSite = "Criar outro website de curso";


// tables MySQL
$langFormula="Sinceramente, seu professor";
$langForumLanguage="brazilian";	// other possibilities are english, spanish (this uses phpbb language functions)
$langTestForum="Fórum de teste";
$langDelAdmin="Remova isso através da ferramenta de administração do fórum";
$langMessage="Quando você remover o fórum de teste, irá remover todas as suas mensagens também.";
$langExMessage="Mensagem de exemplo";
$langAnonymous="Anônimo";
$langExerciceEx="Exercício de exemplo";
$langAntique="Historia da Filosofia Antiga";
$langSocraticIrony="Ironia socrática é...";
$langManyAnswers="(mais de uma resposta pode ser verdadeira)";
$langRidiculise="Ridicularizar um interlocutor de forma que ele assuma estar errado.";
$langNoPsychology="Não. Ironia socrática não é um tipo de psicologia, ela diz respeito à argumentação.";
$langAdmitError="Admitir alguns de seus erros de forma a levar o interlocutor a fazer o mesmo.";
$langNoSeduction="Não. Ironia socrática não é uma estratégia de sedução ou um método baseado no exemplo.";
$langForce="Levar seu interlocutor, por uma série de perguntas e sub-perguntas, a admitir que ele não sabe o que ele afirma saber.";
$langIndeed="Certamente. Ironia socrática é um método interrogativo. O Grego \"eirotao\" siginifica \"fazer perguntas\"";
$langContradiction="Usar o principio da Não Contradição para forçar o seu interlocutor até um beco sem saída.";
$langNotFalse="Essa resposta não é falsa. É verdade que a revelação da ignorância de seu interlocutor se dá mostrando suas conclusões contraditórias as suas premissas iniciais.";

$langSampleLearnPath = "Exemplo de rota de aprendizagem";
$langSampleLearnPathDesc = "Esse é um exemplo de rota de aprendizagem, ele usa o exercício de exemplo e o documento de exemplo da ferramenta de exercícios e da ferramenta de documentos. Clique em
                            <b>Modificar</b> para alterar esse texto.";
$langSampleHandmade = "Exemplo de módulo feito manualmente";
$langSampleHandmadeDesc = "Você pode criar um módulo \'manualmente\' usando páginas html, animações flash, videos, arquivos de som (mp3, ogg,..)<br /><br /> Para permitir que seus estudantes possam ver o conteúdo do seu recém criado módulo você deve definir uma página inicial (recurso) para o módulo.";
$langSampleDocument = "documento_de_exemplo";
$langSampleDocumentDesc = "Você pode usar qualquer documento existente na ferramenta de documentos desse curso.";
$langSampleExerciseDesc = "Você pode usar qualquer exercício da ferramenta de exercícios do seu curso.";

// Home Page MySQL Table "accueil"
$langAgenda="Agenda";
$langLinks="Links";
$langDoc="Documentos";
$langVideo="Vídeo";
$langWorks="Trabalhos";
$langCourseProgram="Programa do curso";
$langAnnouncements="Avisos";
$langUsers="Usuários";
$langForums="Fóruns";
$langExercices="Exercícios";
$langStatistics="Estatísticas";
$langAddPageHome="Enviar página e link para Homepage";
$langLinkSite="Adicionar link na Homepage";
$langModifyInfo="Configurações do curso";
$langLearningPath="Rota de Aprendizagem";


// Other SQL tables
$langAgendaTitle="Terça-feira 11 de Dezembro - Primeira lição : Newton 18";
$langAgendaText="Introdução geral à filosofia e princípios da metodologia";
$langMicro="Entrevistas de rua";
$langVideoText="Este é um exemplo de um arquivo RealVideo. Você pode enviar qualquer tipo de arquivo de audio e vídeo (.mov, .rm, .mpeg...), desde que seus estudantes tenham o plug-in correspondente para lê-los";
$langGoogle="Mecanismo de busca rápido e poderoso";
//$langIntroductionText="Este é o texto introdutório do seu curso. Para substituí-lo pelo seu próprio texto, clique abaixo em <b>modificar</b>.";
//$langIntroductionTwo="Esta página permite a qualquer estudante ou grupo enviar um documento para o website do curso. Envie arquivos HTML apenas se eles não contiverem imagens.";
//$langIntroductionLearningPath="<p>This is the introduction text of the learning paths of your course.  Use this tool to provide your students with a sequential path between documents, exercises, HTML pages,...</p><p>Replace this text with your own introduction text.<br></p>";
$langCourseDescription="Escreva aqui a descrição que irá aparecer na lista de cursos.";
$langProfessor="Professor";
$langAnnouncementExTitle = "Exemplo de Aviso";
$langAnnouncementEx="Este é um exemplo de aviso. Apenas o professor e outros administradores do curso podem publicar avisos.";
$langJustCreated="Você acabou de criar o website do curso";
$langEnter="Voltar para minha lista de cursos";
$langMillikan="Experimento de Millikan";
$langCourseDesc = "Descrição do curso";
 // Groups
$langGroups="Grupos";
$langCreateCourseGroups="Grupos";
$langCatagoryMain="Principal";
$langCatagoryGroup="Fóruns dos grupos";
$langChat ="Chat";

$langOnly = "Apenas";
$langRandomLanguage = "Escolha aleatória entre as linguas disponíveis";

//Display
$langCreateCourse="Criar cursos";
$langQantity="Quantidade : ";
$langPrefix="Prefíxo : ";
$langStudent="Estudantes";
$langMin="Mínimo : ";
$langMax="Máximo : ";
$langNumGroup="Qtde. de grupos por curso";
$langMaxStudentGroup="Máx. de estudantes por grupo";
$langAdmin ="administração";
$langNumGroupStudent="Qtde. do time que o estudante pode inscrever no curso";


$langLabelCanBeEmpty ="É necessário informar o título do curso";
$langTitularCanBeEmpty ="O campo titular precisa ser preenchido";
$langEmailCanBeEmpty ="O campo e-mail precisa ser preenchido";
$langCodeCanBeEmpty ="O campo código do curso precisa ser preenchido";
$langEmailWrong = "E-mail incorreto";
$langCreationMailNotificationSubject = 'Criação do curso';
$langCreationMailNotificationBody = 'Curso criado em'; 
$langByUser = 'pelo usuário';
?>
