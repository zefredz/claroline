<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );
/**
 * CLAROLINE
 *
 * language library
 * contains function to manage l10n
 *
 * @copyright 2001-2007 Universite catholique de Louvain (UCL)
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
        if (get_conf('CLARO_DEBUG_MODE',false))
        {
            foreach (array_keys($var_to_replace) as $signature)
            {

                if (false === strpos($translation, $signature))
                if (is_numeric($signature) && isset($var_to_replace[($signature+1)]) )
                {
                     pushClaroMessage($signature . ' not in varlang.<br>
"<b>' . $var_to_replace[$signature] . '</b>" is probably the key of "<b>' . $var_to_replace[($signature+1)] . '</b>"' ,'translation' );
                }
                else pushClaroMessage($signature . ' not in varlang.' ,'translation');
            }
        }


        uksort( $var_to_replace, 'cmp_string_by_length' );
        return strtr($translation, $var_to_replace);
    }
    else
    {
        // return translation
        return $translation;
    }
}

if ( ! function_exists( 'cmp_string_by_length' ) )
{
    /**
     * Compare strings based on their length
     */
    function cmp_string_by_length( $a, $b )
    {
        if ( strlen( $a ) == strlen( $b ) )
        {
            return 0;
        }
        elseif ( strlen( $a ) > strlen( $b ) )
        {
            return -1;
        }
        else
        {
            return 1;
        }
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


function get_locale($localeInfoName)
{
    static $initValueList = array('englishLangName',
                                  'localLangName',
                                  'iso639_1_code',
                                  'iso639_2_code',
                                  'langNameOfLang',
                                  'charset',
                                  'text_dir',
                                  'left_font_family',
                                  'right_font_family',
                                  'number_thousands_separator',
                                  'number_decimal_separator',
                                  'byteUnits',
                                  'langDay_of_weekNames',
                                  'langMonthNames',
                                  'dateFormatShort', // not used
                                  'dateFormatLong', // used
                                  'dateTimeFormatLong', // used
                                  'dateTimeFormatShort',
                                  'timeNoSecFormat');

    if(!in_array($localeInfoName, $initValueList )) trigger_error( htmlentities($localeInfoName) . ' is not a know locale value name ', E_USER_NOTICE);
                                 //TODO create a real auth function to eval this state

                                 if ( array_key_exists($localeInfoName,$GLOBALS) )  return $GLOBALS[$localeInfoName];
                                 elseif ( defined($localeInfoName)         )        return constant($localeInfoName);
                                 return null;

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
        global $urlAppend ;

        /*----------------------------------------------------------------------
          Initialise language array
          ----------------------------------------------------------------------*/

        $_lang = array();

        if ( is_null($language) ) $language = language::current_language();
        if ( is_null($mode) )     $mode = get_conf('CLAROLANG');

        /*----------------------------------------------------------------------
          Common language properties and generic expressions
          ----------------------------------------------------------------------*/

        // FIXME : force translation mode

        $mode = 'TRANSLATION';

        if ( $mode == 'TRANSLATION' )
        {
            // TRANSLATION MODE : include the language file with all language variables

            include(get_path('incRepositorySys') . '/../lang/english/complete.lang.php');

            if ($language != 'english') // Avoid useless include as English lang is preloaded
            {
                $language_file = realpath(get_path('incRepositorySys') . '/../lang/' . $language . '/complete.lang.php');

                if ( file_exists($language_file) )
                {
                    include($language_file);
                }
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

            if ( ! file_exists(get_path('incRepositorySys') . '/../lang/english/' . $languageFile) )
            {
                include(get_path('incRepositorySys') . '/../lang/english/complete.lang.php');
            }
            else
            {
                include(get_path('incRepositorySys') . '/../lang/english/' . $languageFile);
            }

            // load previously english file to be sure every get_lang('variable')
            // have at least some content

            if ( $language != 'english' )
            {
                @include(get_path('incRepositorySys') . '/../lang/' . $language . '/' . $languageFile);
            }

        }

    }

    function load_locale_settings($language=null)
    {
        global $iso639_1_code, $iso639_2_code, $charset,
               $_locale,
               $langNameOfLang , $langDay_of_weekNames, $langMonthNames, $byteUnits,
               $text_dir, $left_font_family, $right_font_family,
               $number_thousands_separator, $number_decimal_separator,
               $dateFormatShort, $dateFormatLong, $dateTimeFormatLong, $dateTimeFormatShort, $timeNoSecFormat;

        /*
        * tool specific language translation
        */

        if ( is_null($language) ) $language = language::current_language();

        // include the locale settings language
        include(get_path('incRepositorySys').'/../lang/english/locale_settings.php');
        if ( $language != 'english' ) // Avoid useless include as English lang is preloaded
        {
            $locale_settings_file = realpath(get_path('incRepositorySys') . '/../lang/' . $language . '/locale_settings.php');
            if ( file_exists($locale_settings_file) )
            {
                include($locale_settings_file);
            }
        }

        $GLOBALS['langNameOfLang'] = $langNameOfLang;
        $GLOBALS['langDay_of_weekNames'] = $langDay_of_weekNames;
        $GLOBALS['langMonthNames'] = $langMonthNames;

    }

    function current_language()
    {
        global $_course, $_user, $platformLanguage;

        if ( claro_is_in_a_course() && isset($_course['language']) )
        {
            // course language
            return $_course['language'];
        }
        else
        {
            if ( claro_is_user_authenticated() && !empty($_user['language']) )
            {
                // user language
                return $_user['language'];
            }
            else
            {
                if ( isset($_REQUEST['language'])
                    && in_array($_REQUEST['language'], array_keys(get_language_list())) )
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
    $language_dirname = get_path('rootSys') . 'claroline/lang/' ;

    // init accepted_values list
    $language_list = array();

    if ( is_dir($language_dirname) )
    {
        $handle = opendir($language_dirname);
        while ( $elt = readdir($handle) )
        {
            // skip '.', '..' and 'CVS'
            if ( $elt == '.' || $elt == '..' || $elt == 'CVS' ) continue;

            // skip if not a dir
            if ( is_dir($language_dirname.$elt) )
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

function claro_disp_localised_date($formatOfDate,$timestamp = -1)
{
    pushClaroMessage( (function_exists('claro_html_debug_backtrace')
             ? claro_html_debug_backtrace()
             : 'claro_html_debug_backtrace() not defined'
             )
             .'claro_ disp _localised_date() is deprecated , use claro_ html _localised_date()','error');

    return claro_html_localised_date($formatOfDate,$timestamp);
}

function claro_html_localised_date($formatOfDate,$timestamp = -1) //PMAInspiration :)
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

/**
 * This function return true if $Str could be UTF-8, false otehrwise
 *
 * function found @ http://www.php.net/manual/en/function.utf8-encode.php
 */
function seems_utf8($str)
{
    for ($i=0; $i<strlen($str); $i++)
    {
        if (ord($str[$i]) < 0x80) continue; // 0bbbbbbb
        elseif ((ord($str[$i]) & 0xE0) == 0xC0) $n=1; // 110bbbbb
        elseif ((ord($str[$i]) & 0xF0) == 0xE0) $n=2; // 1110bbbb
        elseif ((ord($str[$i]) & 0xF8) == 0xF0) $n=3; // 11110bbb
        elseif ((ord($str[$i]) & 0xFC) == 0xF8) $n=4; // 111110bb
        elseif ((ord($str[$i]) & 0xFE) == 0xFC) $n=5; // 1111110b
        else return false; // Does not match any model
        for ($j=0; $j<$n; $j++) // n bytes matching 10bbbbbb follow ?
        {
            if ((++$i == strlen($str)) || ((ord($str[$i]) & 0xC0) != 0x80))
            return false;
        }
    }
    return true;
}

/**
 * decode $str if $str is utf8 encoded
 */
function utf8_decode_if_is_utf8($str)
{
    if( $GLOBALS['charset'] == 'utf-8' || !seems_utf8($str) )
    {
        return $str;
    }
    else
    {
        return utf8_decode($str);
    }
}


?>