<?php // $Id$
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.5.*
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2004 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      |   Brazillian Translation (portugese)                                 |
      +----------------------------------------------------------------------+

      +----------------------------------------------------------------------+
      | Translator :                                                         |
      |           Marcello R. Minholi, <minholi@unipar.be>                   |
	  |									from Universidade Paranaense         |
      +----------------------------------------------------------------------+
 */

$englishLangName = "Brazilian Portuguese";
$localLangName = "Português";

/*
Brazil (br or pt-br):
in english: "Brazilian Portuguese"
in "brazilian" portuguese: "Português"
in portuguese of Portugal: "Português do Brasil"

Portugal (pt):
in english: "Portuguese"
in "brazilian" portuguese: "Português de Portugal"
in portuguese of Portugal: "Português"
*/

$iso639_2_code = "br";
//$iso639_1_code = "bre";
//http://www.w3.org/WAI/ER/IG/ert/iso639.htm

$langNameOfLang['brazilian'		] = "português";
//$langNameOfLang[brazilian_portuguese]="português";
$langNameOfLang['croatia'		] = "brazilian";
$langNameOfLang['dutch'			] = "Nederlands";
$langNameOfLang['english'		] = "inglês";
$langNameOfLang['finnish'		] = "finlandês";
$langNameOfLang['french'		] = "francês";
$langNameOfLang['german'		] = "alemão";
$langNameOfLang['greek'			] = "greek";
$langNameOfLang['italian'		] = "italiano";
$langNameOfLang['japanese'		] = "japonês";
$langNameOfLang['polish'		] = "polonês";
$langNameOfLang['simpl_chinese'	] = "chinês simples";
$langNameOfLang['spanish'		] = "espanhol";
$langNameOfLang['swedish'		] = "sueco";
$langNameOfLang['thai'			] = "tailandês";
$langNameOfLang['arabic'		] = "arabian";
$langNameOfLang['turkish'		] = "turkish";

$charset = 'iso-8859-1';
$text_dir = 'ltr'; // ('ltr' for left to right, 'rtl' for right to left)
$left_font_family = 'verdana, arial, helvetica, geneva, sans-serif';
$right_font_family = 'arial, helvetica, geneva, sans-serif';
$number_thousands_separator = ',';
$number_decimal_separator = '.';
$byteUnits = array('Bytes', 'KB', 'MB', 'GB');

$langDay_of_weekNames['init'] = array('D', 'S', 'T', 'Q', 'Q', 'S', 'S');
$langDay_of_weekNames['short'] = array('Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab');
$langDay_of_weekNames['long'] = array('Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado');
$langMonthNames['init']  = array('J', 'F', 'M', 'A', 'M', 'J', 'J', 'A', 'S', 'O', 'N', 'D');
$langMonthNames['short'] = array('Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez');
$langMonthNames['long'] = array('Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro');

// Voir http://www.php.net/manual/en/function.strftime.php pour la variable
// ci-dessous

// http://phedre.ipm.ucl.ac.be/phpBB/viewtopic.php?topic=224&forum=6&2
$dateFormatShort =  "%d de %b de %y";
$dateFormatLong  = '%A, %d de %B de %Y';
$dateTimeFormatLong  = '%A, %d de %B de %Y às %H:%Mh';
$timeNoSecFormat = '%H:%Mh';



// GENERIC

$langModify="modificar";
$langDelete="apagar";
$langTitle="Título";
$langHelp="ajuda";
$langOk="Ok";
$langAddIntro="Adicionar texto introdutório";
$langBackList="Voltar para a lista";
$langBack="Vontar para as informações do curso";
$langBackH="Home Page do Curso";
$langPropositions="Sugestões";


// banner

$langMyCourses="Meus cursos";
$langModifyProfile="Modificar meu perfíl";
$langLogout="Logout";

$langAgenda="Agenda";
$langDocument="Documentos";
$langWork="Trabalhos dos Estudantes";
$langAnnouncement="Anúncios";
$langUser="Usuários";
$langForum="Fóruns";
$langExercise="Exercícios";

?>