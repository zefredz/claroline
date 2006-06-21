<?php // $Id$
/**
 * CLAROLINE
 *
 * language library
 * contains function to manage l10n
 *
 * @copyright 2001-2006 Universite catholique de Louvain (UCL)
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @see http://www.claroline.net/wiki/CLUSR
 * @package CLUSR
 * @author Claro Team <cvs@claroline.net>
 *
 */

/**
 * Translate strings to the current locale
 *
 * When using get_lang(), try to put entire sentences and strings in
 * one get_lang() call. This makes it easier for translators.
 *
 * @code
 *  $msg = get_lang('Hello %name',array('%name' => $username))
 * @endcode
 *
 * @param $name
 *   A string containing the English string to translate.
 * @param $var_to_replace
 *   An associative array of replacements to make after translation. Incidences
 *   of any key in this array are replaced with the corresponding value.
 * @return
 *   The translated string.
 */

function get_lang ($name,$var_to_replace=null)
{
    global $_lang;

    $translation = '';

    if ( isset($_lang[$name]) )
    {
        $translation = $_lang[$name];
    }
    else
    {
        // missing translation
        $translation = $name;
    }

    if ( !empty($var_to_replace) && is_array($var_to_replace) )
    {
        return strtr($translation, $var_to_replace);
    }
    else
    {
        // return translation
        return $translation;
    }
}

/**
 * Get the translation of the block
 *
 * @param $name block name
 * @param $var_to_remplace array with variables to replace
 *
 * @return block translation
 *
 */

function get_block ($name,$var_to_replace=null)
{
    if ( !empty($var_to_replace) && is_array($var_to_replace) )
    {
        return get_lang($name,$var_to_replace);
    }
    else
    {
        return get_lang($name);
    }
}

class language
{
    /**
     * Load the global array ($_lang) with all translations of the language
     *
     * @param string $language language (default : english)
     * @param string $mode     TRANSLATION or PRODUCTION (default : PRODUCTION)
     *
     */

    function load_translation ($language=null,$mode=null)
    {
        global $_lang ;
        global $includePath, $urlAppend ;

        /*----------------------------------------------------------------------
          Initialise language array
          ----------------------------------------------------------------------*/

        $_lang = array();

        if ( is_null($language) ) $language = language::current_language();
        if ( is_null($mode) )     $mode = get_conf('CLAROLANG');

        /*----------------------------------------------------------------------
          Common language properties and generic expressions
          ----------------------------------------------------------------------*/

        if ( $mode == 'TRANSLATION' )
        {
            // TRANSLATION MODE : include the language file with all language variables

            include($includePath . '/../lang/english/complete.lang.php');

            if ($language  != 'english') // Avoid useless include as English lang is preloaded
            {
                include($includePath . '/../lang/' . $language . '/complete.lang.php');
            }

        }
        else
        {
            // PRODUCTION MODE : include the language file with variables used by the script

            /*
             * tool specific language translation
             */

            // build lang file of the tool
            $languageFilename = preg_replace('|^'.preg_quote($urlAppend.'/').'|', '',  $_SERVER['PHP_SELF'] );
            $pos = strpos($languageFilename, 'claroline/');

            if ($pos === FALSE || $pos != 0)
            {
                // if the script isn't in the claroline folder the language file base name is index
                $languageFilename = 'index';
            }
            else
            {
                // else language file basename is like claroline_folder_subfolder_...
                $languageFilename = dirname($languageFilename);
                $languageFilename = str_replace('/','_',$languageFilename);
            }

            // add extension to file
            $languageFile = $languageFilename . '.lang.php';

            if ( ! file_exists($includePath . '/../lang/english/' . $languageFile) )
            {
                include($includePath . '/../lang/english/complete.lang.php');
            }
            else
            {
                include($includePath . '/../lang/english/' . $languageFile);
            }

            // load previously english file to be sure every get_lang('variable')
            // have at least some content

            if ( $language != 'english' )
            {
                @include($includePath . '/../lang/' . $language . '/' . $languageFile);
            }

        }

    }

    function load_locale_settings($language=null)
    {
        global $includePath;

        global $iso639_1_code, $iso639_2_code, $charset,
               $langNameOfLang , $langDay_of_weekNames, $langMonthNames, $byteUnits,
               $text_dir, $left_font_family, $right_font_family,
               $number_thousands_separator, $number_decimal_separator,
               $dateFormatShort, $dateFormatLong, $dateTimeFormatLong, $dateTimeFormatShort, $timeNoSecFormat;

        /*
        * tool specific language translation
        */
        if ( is_null($language) ) $language = language::current_language();

        // include the locale settings language
        include($includePath.'/../lang/english/locale_settings.php');

        if ( $language != 'english' ) // Avoid useless include as English lang is preloaded
        {
            include($includePath.'/../lang/'.$language.'/locale_settings.php');
        }

        $GLOBALS['langNameOfLang'] = $langNameOfLang;
        $GLOBALS['langDay_of_weekNames'] = $langDay_of_weekNames;
        $GLOBALS['langMonthNames'] = $langMonthNames;

    }

    function current_language()
    {
        global $_course, $_user, $platformLanguage, $_cid, $_uid ;

        if ( isset($_cid) && isset($_course['language']) )
        {
            // course language
            return $_course['language'];
        }
        else
        {
            if ( isset($_uid) && !empty($_user['language']) )
            {
                // user language
                return $_user['language'];
            }
            else
            {
                if ( isset($_REQUEST['language']) )
                {
                    // selected language
                    $_SESSION['language'] = $_REQUEST['language'];
                    return $_REQUEST['language'];
                }
                else
                {
                    if ( empty($_SESSION['language']) )
                    {
                        // default platform language
                        return $platformLanguage;
                    }
                    else
                    {
                        return $_SESSION['language'];
                    }
                }
            }
        }

    }
}

/**
*   Displays a form (drop down menu) so the user can select his/her preferred
language.
*   The form works with or without javascript
*   TODO : need some refactoring there is a lot of function to get platform
language
*/

function get_language_list()
{
    // language path
    $language_dirname = get_conf('rootSys') . 'claroline/lang/' ;

    // init accepted_values list
    $language_list = array();

    if ( is_dir($dirname) )
    {
        $handle = opendir($language_dirname);
        while ( $elt = readdir($handle) )
        {
            // skip '.', '..' and 'CVS'
            if ( $elt == '.' || $elt == '..' || $elt == 'CVS' ) continue;

            // skip if not a dir
            if ( ! is_dir($language_dirname.$elt) )
            {
                $elt_key = $elt;
                $elt_value = get_translation_of_language($elt_key);
                $language_list[$elt_key] = $elt_value;
            }
        }

        asort($language_list);
        return $language_list;
    }
    else
    {
        return false;
    }
}

function get_language_to_display_list()
{
    global $platformLanguage;

    $language_list = array();

    $language_to_display_list = get_conf('language_to_display');
    $language_to_display_list[] = $platformLanguage;

    foreach ( $language_to_display_list as $language )
    {
        $key = get_translation_of_language($language);
        $value = $language;
        $language_list[$key] = $value;
    }

    asort($language_list);

    return $language_list;
}

function get_translation_of_language($language)
{
    global $langNameOfLang;

    if ( !empty($langNameOfLang[$language])
            && $langNameOfLang[$language]!=$language )
    {
        return $langNameOfLang[$language];
    }
    else
    {
        return $language;
    }
}

/**
*   Displays a form (drop down menu) so the user can select his/her preferred language.
*   The form works with or without javascript
*   TODO : need some refactoring there is a lot of function to get platform language
*/

function claro_display_preferred_language_form()
{
    require_once(dirname(__FILE__).'/form.lib.php');

    $language_list = get_language_to_display_list();

    $form = '';

    if ( is_array($language_list) && count($language_list) > 1 )
    {
        // get the the current language
        $user_language = language::current_language();

        foreach ( $language_list as $key => $value )
        {
            $languageOption_list[$key] = $_SERVER['PHP_SELF'].'?language='.urlencode($value);
        }

        // build language selector form
        $form .= '<form action="'.$_SERVER['PHP_SELF'].'" name="language_selector" method="post" style="margin:5px;">' . "\n" ;

        $form .= claro_html_form_select('language',$languageOption_list,$_SERVER['PHP_SELF'].'?language='.urlencode($user_language),array('id'=>'langSelector', 'onchange'=>'top.location=this.options[selectedIndex].value')) . "\n";

        $form .= '<noscript><input type="submit" value="' . get_lang('Ok') . '" /></noscript>' . "\n";
        $form .= '</form>' . "\n";
    }

    return $form;
}



/**
 * return an array with names of months
 *
 * @param string $size size / format of strings
 *                           'long' for complete name
 *                           'short' or 'abbr' for abbreviation
 * @return array of 12 strings (0 = january)
 */


function get_lang_month_name_list($size='long')
{
    global $langMonthNames ;

    switch ($size)
    {
        case 'abbr' :
            $nameList = $langMonthNames['init'];
            break;
        case 'short' :
            $nameList = $langMonthNames['short'];
            break;
        case 'long' :
        default :
            $nameList = $langMonthNames['long'];
            break;
    }
    return $nameList;
}

/**
 * return an array with names of weekdays
 *
 * @param string $size size / format of strings
 *                           'long' for complete name
 *                           'short' or 'abbr' for abbreviation
 * @return array of 7 strings (0 = monday)
 */

function get_lang_weekday_name_list($size='long')
{
    global $langDay_of_weekNames;

    switch ($size)
    {
        case 'abbr' :
            $nameList = $langDay_of_weekNames['init'];
            break;
        case 'short' :
            $nameList = $langDay_of_weekNames['short'];
            break;
        case 'long' :
        default :
            $nameList = $langDay_of_weekNames['long'];
            break;
    }
    return $nameList;
}

/**
 * Display a date at localized format
 * @author Christophe Gesché <gesche@ipm.ucl.ac.be>
 * @param formatOfDate
         see http://www.php.net/manual/en/function.strftime.php
         for syntax to use for this string
         I suggest to use the format you can find in trad4all.inc.php files
 * @param timestamp timestamp of date to format
 */

function claro_disp_localised_date($formatOfDate,$timestamp = -1) //PMAInspiration :)
{
    $langDay_of_weekNames['long'] = get_lang_weekday_name_list('long');
    $langDay_of_weekNames['short'] = get_lang_weekday_name_list('short');

    $langMonthNames['short'] = get_lang_month_name_list('short');
    $langMonthNames['long'] = get_lang_month_name_list('long');

    if ($timestamp == -1) $timestamp = claro_time();

    // avec un ereg on fait nous même le replace des jours et des mois
    // with the ereg  we  replace %aAbB of date format
    //(they can be done by the system when  locale date aren't aivailable

    $formatOfDate = ereg_replace('%[A]', $langDay_of_weekNames['long'][(int)strftime('%w', $timestamp)], $formatOfDate);
    $formatOfDate = ereg_replace('%[a]', $langDay_of_weekNames['short'][(int)strftime('%w', $timestamp)], $formatOfDate);
    $formatOfDate = ereg_replace('%[B]', $langMonthNames['long'][(int)strftime('%m', $timestamp)-1], $formatOfDate);
    $formatOfDate = ereg_replace('%[b]', $langMonthNames['short'][(int)strftime('%m', $timestamp)-1], $formatOfDate);
    return strftime($formatOfDate, $timestamp);
}

?>