<?
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.3.0 $Revision$                        	 |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2002 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      |   $Id$        |
      |   Brazillian Translation (portugese)                                 |
      +----------------------------------------------------------------------+
      |   This program is free software; you can redistribute it and/or      |
      |   modify it under the terms of the GNU General Public License        |
      |   as published by the Free Software Foundation; either version 2     |
      |   of the License, or (at your option) any later version.             |
      |                                                                      |
      |   This program is distributed in the hope that it will be useful,    |
      |   but WITHOUT ANY WARRANTY; without even the implied warranty of     |
      |   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the      |
      |   GNU General Public License for more details.                       |
      |                                                                      |
      |   You should have received a copy of the GNU General Public License  |
      |   along with this program; if not, write to the Free Software        |
      |   Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA          |
      |   02111-1307, USA. The GNU GPL license is also available through     |
      |   the world-wide-web at http://www.gnu.org/copyleft/gpl.html         |
      +----------------------------------------------------------------------+
      | Authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
      |          Hugues Peeters    <peeters@ipm.ucl.ac.be>                   |
      |          Christophe Gesché <gesche@ipm.ucl.ac.be>                    |
      +----------------------------------------------------------------------+
      | Translator :                                                         |
      |           Marcello R. Minholi, <minholi@unipar.be>                   |
	  |									from Universidade Paranaense         |
      +----------------------------------------------------------------------+
 */

// Brazillian Translation (portugese)
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

$langNameOfLang[brazilian]="português";
//$langNameOfLang[brazilian_portuguese]="português";
$langNameOfLang[croatian]="brazilian";
$langNameOfLang[dutch]="Nederlands";
$langNameOfLang[english]="inglês";
$langNameOfLang[finnish]="finlandês";
$langNameOfLang[french]="francês";
$langNameOfLang[german]="alemão";
$langNameOfLang[greek]="greek";
$langNameOfLang[italian]="italiano";
$langNameOfLang[japanese]="japonês";
$langNameOfLang[polish]="polonês";
$langNameOfLang[simpl_chinese]="chinês simples";
$langNameOfLang[spanish]="espanhol";
$langNameOfLang[swedish]="sueco";
$langNameOfLang[thai]="tailandês";
$langNameOfLang[arabic]="arabian";
$langNameOfLang[turkish]="turkish";

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
?>