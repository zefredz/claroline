<?php // $Id$
/**
 * CLAROLINE
 *
 * @version 1.8 $Revision$
 *
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @author: claro team <cvs@claroline.net>
 *
 * @package LANG-TH
 */
/*แปลและพัฒนาระบบภาษาไทยโดย ผศ.ประชิด ทิณบุตร นับแต่เมื่อวันที่ 23 ตุลาคม 2549  ปรับปรุงล่าสุดเมื่อ 1 มกราคม 2552
อาจารย์ประจำสาขาวิชาศิลปกรรม มหาวิทยาลัยราชภัฏจันทรเกษม ถนนรัชดาภิเษก เขตจตุจักร กทม 10900.
Thai Developer & Translation : Assistant Professor Prachid Tinnabutr : Division of Fine and Applied Arts : Faculty of Humanities and Social Sciences, Chandrakasem Rajabhat University,Jatuchak District,Bangkok ,Thailand.10900.
http://artnet.chandra.ac.th , http://www.chandra.ac.th
Last update:1 -Jan-2009.

Personal Address : 144/157 Moo 1 ,Changwatana Rd.Pakkret District ,Nonthaburi Province,Thailand,11120 Tel/Fax:(66)0 2962 9505 prachid@prachid.com,prachid@wittycomputer.com ,Mobile Phone:(66)08 9667 0091
URL : http://www.prachid.com, http://www.wittycomputer.com, http://www.e107thailand.com, http//www.clarolinethai.info
*/
$iso639_1_code = "th";
$iso639_2_code = "tha";

unset($langNameOfLang);
unset($langDay_of_weekNames);
unset($langMonthNames);

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
$langNameOfLang['zh_tw']         = "traditional chinese";

$charset = 'UTF-8';
$text_dir = 'ltr';
$left_font_family = 'MS Sans Serif,Tahoma,verdana, helvetica, arial, geneva, sans-serif';
$right_font_family = 'MS Sans Serif,Tahoma, verdana, helvetica, arial, geneva, sans-serif';
$number_thousands_separator = ',';
$number_decimal_separator = '.';
$byteUnits = array('Bytes', 'KB', 'MB', 'GB');

$langDay_of_weekNames['init'] = array('S', 'M', 'T', 'W', 'T', 'F', 'S');
$langDay_of_weekNames['short'] = array('อา', 'จ', 'อ', 'พ', 'พฤ', 'ศ', 'ส');
$langDay_of_weekNames['long'] = array('วันอาทิตย์', 'วันจันทร์', 'วันอังคาร', 'วันพุธ', 'วันพฤหัสบดี', 'วันศุกร์', 'วันเสาร์');

$langMonthNames['init']  = array('เดือนมกราคม', 'เดือนกุมภาพันธ์', 'เดือนมีนาคม',  'เดือนเมษายน', 'เดือนพฤษภาคม', 'เดือนมิถุนายน', 'เดือนกรกฎาคม', 'เดือนสิงหาคม', 'เดือนกันยายน', 'เดือนตุลาคม', 'เดือนพฤศจิกายน', 'เดือนธันวาคม');
$langMonthNames['short'] = array('เดือนมกราคม', 'เดือนกุมภาพันธ์', 'เดือนมีนาคม',  'เดือนเมษายน', 'เดือนพฤษภาคม', 'เดือนมิถุนายน', 'เดือนกรกฎาคม', 'เดือนสิงหาคม', 'เดือนกันยายน', 'เดือนตุลาคม', 'เดือนพฤศจิกายน', 'เดือนธันวาคม');
$langMonthNames['long'] = array('เดือนมกราคม', 'เดือนกุมภาพันธ์', 'เดือนมีนาคม',  'เดือนเมษายน', 'เดือนพฤษภาคม', 'เดือนมิถุนายน', 'เดือนกรกฎาคม', 'เดือนสิงหาคม', 'เดือนกันยายน', 'เดือนตุลาคม', 'เดือนพฤศจิกายน', 'เดือนธันวาคม');

$dateFormatShort =  "%b %d, %y";
$dateFormatLong  = '%A %B %d, %Y';
$dateTimeFormatLong  = '%B %d, %Y at %I:%M %p';
$dateTimeFormatShort = "%b. %d, %y %I:%M %p";
$timeNoSecFormat = '%I:%M %p';

?>