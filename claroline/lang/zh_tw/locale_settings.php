<?php // $Id$
/**
 * CLAROLINE
 *
 * @version 1.7 $Revision$
 *
 * @copyright (c) 2001-2005 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @package LANG-EN
 *
 * @author Claro team <cvs@claroline.net>
 */

$iso639_1_code = "zh";
//$iso639_2_code = "eng";

$englishLangName = "taiwan";
$localLangName = "taiwan";

unset($langNameOfLang);
unset($langDay_of_weekNames);
unset($langMonthNames);
unset($byteUnits);

$langNameOfLang['arabic']        = "arabian";
$langNameOfLang['brazilian']     = "brazilian";
$langNameOfLang['bulgarian']     = "bulgarian";
$langNameOfLang['catalan']       = "catalan";
$langNameOfLang['croatian']      = "croatian";
$langNameOfLang['danish']        = "danish";
$langNameOfLang['dutch']         = "dutch";
$langNameOfLang['english']       = "english";
$langNameOfLang['finnish']       = "finnish";
$langNameOfLang['french']        = "french";
$langNameOfLang['galician']      = "galician";
$langNameOfLang['german']        = "german";
$langNameOfLang['greek']         = "greek";
$langNameOfLang['italian']       = "italian";
$langNameOfLang['indonesian']    = "indonesian";
$langNameOfLang['japanese']      = "japanese";
$langNameOfLang['malay']         = "malay";
$langNameOfLang['polish']        = "polish";
$langNameOfLang['portuguese']    = "portuguese";
$langNameOfLang['russian']       = "russian";
$langNameOfLang['simpl_chinese'] = "simplified chinese";
$langNameOfLang['slovenian']     = "slovenian";
$langNameOfLang['spanish']       = "spanish";
$langNameOfLang['swedish']       = "swedish";
$langNameOfLang['thai']          = "thai";
$langNameOfLang['turkish']       = "turkish";
$langNameOfLang['vietnamese']    = "vietnamese";
$langNameOfLang['zh_tw']         = "taiwan";

$charset = 'utf-8';
$text_dir = 'ltr'; // ('ltr' for left to right, 'rtl' for right to left)
$left_font_family = 'verdana, helvetica, arial, geneva, sans-serif';
$right_font_family = 'helvetica, arial, geneva, sans-serif';
$number_thousands_separator = ',';
$number_decimal_separator = '.';
$byteUnits = array('Bytes', 'KB', 'MB', 'GB');

$langDay_of_weekNames['init'] = array('S', 'M', 'T', 'W', 'T', 'F', 'S');
$langDay_of_weekNames['short'] = array('Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat');
$langDay_of_weekNames['long'] = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');

$langMonthNames['init']  = array('J', 'F', 'M', 'A', 'M', 'J', 'J', 'A', 'S', 'O', 'N', 'D');
$langMonthNames['short'] = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
$langMonthNames['long'] = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');

// Voir http://www.php.net/manual/en/function.strftime.php pour la variable
// ci-dessous

$dateFormatShort =  "%b. %d, %y";
$dateFormatLong  = '%A %B %d, %Y';
$dateTimeFormatLong  = '%B %d, %Y at %I:%M %p';
$dateTimeFormatShort = "%b. %d, %y %I:%M %p";
$timeNoSecFormat = '%I:%M %p';

?>