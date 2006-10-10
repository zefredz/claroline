<?php // $Id$
/**
 * CLAROLINE
 *
 * @version 1.8 $Revision$
 *
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @author: S³awomir Gurda³a <guslaw@uni.lodz.pl>                    |
 * @author: claro team <cvs@claroline.net>
 *
 * @package LANG-PL
*/
$englishLangName = "Polish";

$iso639_1_code = "pl";
$iso639_2_code = "pol";

$langNameOfLang['arabic']          = 'Arabski';
$langNameOfLang['armenian']        = 'Ormiañski';
//$langNameOfLang['brazilian]      = "brazilian";
$langNameOfLang['ulgarian']        = 'Bu³garski';
//$langNameOfLang['catalan']       = "";
$langNameOfLang['croatian']        = 'Chorwacki';
$langNameOfLang['czech']           = 'Czeski';
$langNameOfLang['czechSlovak']     = 'Czesko-s³owacki';
$langNameOfLang['danish']          = 'Duñski';
//$langNameOfLang['dutch']         = "";
//$langNameOfLang['dutch']         = "";
$langNameOfLang['english']         = 'Angielski';
$langNameOfLang['esperanto']       = 'Esperanto';
$langNameOfLang['estonian']        = 'Estoñski';
//$langNameOfLang['finnish']       = "finnish";
//$langNameOfLang['french']        = "french";
$langNameOfLang['georgian']        = 'Gruziñski';
$langNameOfLang['german']          = 'Niemiecki';
$langNameOfLang['greek']           = 'Grecki';
//$langNameOfLang['italian']       = "italian";
//$langNameOfLang['japanese']      = "japanese";
$langNameOfLang['polish']          = 'Polski';
//$langNameOfLang['simpl_chinese'] = "simplified chinese";
$langNameOfLang['spanish']         = 'Hiszpañski';
$langNameOfLang['swedish']         = 'Szwedzki';
$langNameOfLang['thai']            = 'Tajski';
$langNameOfLang['turkish']         = 'Turecki';
$langNameOfLang['ukrainian']       = 'Ukraiñski';;

$charset = 'iso-8859-2';
$text_dir = 'ltr'; // ('ltr' for left to right, 'rtl' for right to left)
$left_font_family = 'verdana, helvetica, arial, geneva, sans-serif';
$right_font_family = 'helvetica, arial, geneva, sans-serif';
$number_thousands_separator = ' ';
$number_decimal_separator = ',';
// shortcuts for Byte, Kilo, Mega, Giga, Tera, Peta, Exa
$byteUnits = array('bajtów', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB');

$langDay_of_weekNames['init'] = array('N', 'P', 'W', '¦', 'C', 'Pt', 'S');
$langDay_of_weekNames['short'] = array('Nied', 'Pon', 'Wt', '¦r', 'Czw', 'Pt', 'Sob');
$langDay_of_weekNames['long'] = array('Niedziela', 'Poniedzia³ek', 'Wtorek', '¦roda', 'Czwartek', 'Pi±tek', 'Sobota');

$langMonthNames['init']  = array('S', 'L', 'M', 'K', 'M', 'C', 'L', 'S', 'W', 'P', 'L', 'G');
$langMonthNames['short'] = array('Sty', 'Lut', 'Mar', 'Kwi', 'Maj', 'Cze', 'Lip', 'Sie', 'Wrz', 'Pa¼', 'Lis', 'Gru');
$langMonthNames['long'] = array('Styczeñ', 'Luty', 'Marzec', 'Kwiecieñ', 'Maj', 'Czerwiec', 'Lipiec', 'Sierpieñ', 'Wrzesieñ', 'Pa¼dziernik', 'Listopad', 'Grudzieñ');

// Voir http://www.php.net/manual/en/function.strftime.php pour la variable
// ci-dessous

$dateFormatShort =  "%d %b %y";
$dateFormatLong  = '%A, %d %B %Y';
$dateTimeFormatLong  = '%d %B %Y, %H:%M';
$timeNoSecFormat = '%H:%M';
$timespanfmt = '%s dni, %s godzin, %s minut i %s sekund';

?>