<?php // $Id$

 /*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.4.* Lib transform text $Revision$          |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2003 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      | Authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
      |          Hugues Peeters    <peeters@ipm.ucl.ac.be>                   |
      |          Christophe Gesché <gesche@ipm.ucl.ac.be>                    |
      +----------------------------------------------------------------------+
 */


/**
 * formats the date according to the locale settings
 *
 * @author  Christophe Gesché <gesche@ipm.ucl.ac.be>
 *          originally inspired from from PhpMyAdmin
 *
 * @params  string  $formatOfDate date pattern
 * @params  integer $timestamp, default is NOW.
 *
 * @globals $langMonthNames and $langDay_of_weekNames 
 *          set in lang/.../trad4all.inc.php
 *
 * @return the formatted date
 *
 * @see lang/.../trad4all.inc.php for the locale format
 * @see http://www.php.net/manual/fr/function.strftime.php
 *      to understand the possible date format
 *
 */

function claro_format_locale_date( $dateFormat, $timeStamp = -1)
{
	// Retrieve $langMonthNames and $langDay_of_weekNames 
	// from the approriate lang/*/trad4all.inc.php where they are set

	$langMonthNames			= $GLOBALS['langMonthNames'      ]; 
	$langDay_of_weekNames	= $GLOBALS['langDay_of_weekNames'];

	if ($timeStamp == -1) $timeStamp = time();

	// with the ereg  we  replace %aAbB of date format
	//(they can be done by the system when  locale date aren't aivailable
	$date = ereg_replace('%[A]', $langDay_of_weekNames['long'][(int)strftime('%w', $timeStamp)], $dateFormat);
	$date = ereg_replace('%[a]', $langDay_of_weekNames['short'][(int)strftime('%w', $timeStamp)], $date);
	$date = ereg_replace('%[B]', $langMonthNames['long'][(int)strftime('%m', $timeStamp)-1], $date);
	$date = ereg_replace('%[b]', $langMonthNames['short'][(int)strftime('%m', $timeStamp)-1], $date);

	return strftime($date, $timeStamp);

} // end function claro_format_locale_date



?>