<?php // $Id$

/**
 * CLAROLINE
 *
 * Language library.  Contains function to manage l10n.
 *
 * @version     Claroline 1.12 $Revision$
 * @copyright   (c) 2001-2014, Universite catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @see         http://www.claroline.net/wiki/CLUSR
 * @package     kernel.core
 * @author      Claro Team <cvs@claroline.net>
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
    if ( ! isset($GLOBALS['_lang']) ) $GLOBALS['_lang'] = array();
    
    $translation = '';

    if ( isset($GLOBALS['_lang'][$name]) )
    {
        $translation = $GLOBALS['_lang'][$name];
    }
    else
    {
        // missing translation
        $translation = $name;
    }


    if ( !empty($var_to_replace) && is_array($var_to_replace) )
    {
        if (claro_debug_mode())
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

/**
 * Get locale information
 * @staticvar array $initValueList
 * @param string $localeInfoName
 * @return mixed|null
 */
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
                                  'dateFormatCompact',
                                  'dateFormatShort', // not used
                                  'dateFormatLong', // used
                                  'dateFormatNumeric',
                                  'dateTimeFormatLong', // used
                                  'dateTimeFormatShort',
                                  'timeNoSecFormat');

    if(!in_array($localeInfoName, $initValueList )) trigger_error( claro_htmlentities($localeInfoName) . ' is not a know locale value name ', E_USER_NOTICE);
    
    //TODO create a real auth function to eval this state

    if ( array_key_exists($localeInfoName,$GLOBALS) )  return $GLOBALS[$localeInfoName];
    elseif ( defined($localeInfoName)         )        return constant($localeInfoName);
    else return null;

}

/**
 * Language l10n class
 */
class language
{
    /**
     * Load the global array ($_lang) with all translations of the language
     *
     * @param string $language language (default : english)
     * @param string $mode     TRANSLATION or PRODUCTION (default : PRODUCTION)
     *
     */
    public static function load_translation ( $language = null )
    {
        if ( ! isset($GLOBALS['_lang']) ) $GLOBALS['_lang'] = array();

        /*----------------------------------------------------------------------
          Initialise language array
          ----------------------------------------------------------------------*/

        $_lang = array();

        if ( is_null($language) ) $language = language::current_language();

        /*----------------------------------------------------------------------
          Common language properties and generic expressions
          ----------------------------------------------------------------------*/

        include(get_path('incRepositorySys') . '/../lang/english/complete.lang.php');
        
        // overwrite with custom english language file if any
            
        $language_file = realpath(get_path('incRepositorySys') . '/../../platform/lang/english/complete.lang.php');
        
        if ( file_exists($language_file) )
        {
            include($language_file);
        }

        if ($language != 'english') // Avoid useless include as English lang is preloaded
        {
            // overwrite with specific language file
            
            $language_file = realpath(get_path('incRepositorySys') . '/../lang/' . $language . '/complete.lang.php');

            if ( file_exists($language_file) )
            {
                include($language_file);
            }
            
            // load module name translations
            
            $language_file  = get_path('rootSys') . get_conf('cacheRepository', 'tmp/cache/') . 'module_lang_cache/' . $language . '.lang.php';
            
            if ( file_exists($language_file) )
            {
                include($language_file);
            }
            
            // overwrite with custom language files
            
            $language_file = realpath(get_path('incRepositorySys') . '/../../platform/lang/' . $language . '/complete.lang.php');
            
            if ( file_exists($language_file) )
            {
                include($language_file);
            }
        }
        
        $GLOBALS['_lang'] = array_merge($GLOBALS['_lang'], $_lang);
    }
    
    /**
     * Load locale settings
     * @param string $language
     */
    public static function load_locale_settings( $language = null )
    {
        if ( is_null( $language ) )
        {
            $language = language::current_language();
        }

        // include the default locale_settings
        include get_path('incRepositorySys').'/../lang/english/locale_settings.php';
        
        // include the language specific locale_settings
        if ( $language != 'english' ) // Avoid useless include as English lang is preloaded
        {
            $locale_settings_file = get_path('incRepositorySys')
                . '/../lang/' . $language . '/locale_settings.php'
                ;
            
            if ( file_exists($locale_settings_file) )
            {
                include $locale_settings_file;
            }
        }
        
        // FIXME: IS this code mandatory ?!?
        $GLOBALS['langNameOfLang'] = $langNameOfLang;
        $GLOBALS['langDay_of_weekNames'] = $langDay_of_weekNames;
        $GLOBALS['langMonthNames'] = $langMonthNames;
        $GLOBALS['charset'] = $charset;
        $GLOBALS['iso639_1_code'] = $iso639_1_code;
        $GLOBALS['iso639_2_code'] = $iso639_2_code;
        $GLOBALS['byteUnits'] = $byteUnits;
        $GLOBALS['text_dir'] = $text_dir;
        $GLOBALS['left_font_family'] = $left_font_family;
        $GLOBALS['right_font_family'] = $right_font_family;
        $GLOBALS['number_thousands_separator'] = $number_thousands_separator;
        $GLOBALS['number_decimal_separator'] = $number_decimal_separator;
        $GLOBALS['dateFormatCompact'] = $dateFormatCompact;
        $GLOBALS['dateFormatShort'] = $dateFormatShort;
        $GLOBALS['dateFormatLong'] = $dateFormatLong;
        $GLOBALS['dateTimeFormatLong'] = $dateTimeFormatLong;
        $GLOBALS['dateFormatNumeric'] = $dateFormatNumeric;
        $GLOBALS['dateTimeFormatShort'] = $dateTimeFormatShort;
        $GLOBALS['timeNoSecFormat'] = $timeNoSecFormat;
    }
    
    /**
     * Load module translation
     * @param string $moduleLabel
     * @param string $language
     */
    public static function load_module_translation( $moduleLabel = null, $language = null )
    {
        if ( ! isset($GLOBALS['_lang']) ) $GLOBALS['_lang'] = array();
        
        $_lang = array();
        
        $moduleLabel = is_null( $moduleLabel ) ? get_current_module_label() : $moduleLabel;
        
        // In a module
        if ( ! empty( $moduleLabel ) )
        {
            $module_path = get_module_path( $moduleLabel );
            
            $language = is_null( $language )
                ? language::current_language()
                : $language
                ;
            
            // load english by default if exists
            if ( file_exists( $module_path.'/lang/lang_english.php' ) )
            {
                /* FIXME : DEPRECATED !!!!! */
                $mod_lang = array();
                
                include $module_path.'/lang/lang_english.php';
                
                $_lang = array_merge($_lang,$mod_lang);
                
                if ( claro_debug_mode() )
                {
                    pushClaroMessage(__FUNCTION__."::".$moduleLabel.'::'
                        . 'English lang file loaded', 'debug');
                }
            }
            else
            {
                // no language file to load
                if ( claro_debug_mode() )
                {
                    pushClaroMessage(__FUNCTION__."::".$moduleLabel.'::'
                        . 'English lang file  not found', 'debug');
                }
            }
            
            // load requested language if exists
            if ( $language != 'english'
                && file_exists($module_path.'/lang/lang_'.$language.'.php') )
            {
                /* FIXME : CODE DUPLICATION see 263-274 !!!!! */
                /* FIXME : DEPRECATED !!!!! */
                $mod_lang = array();
                
                include $module_path.'/lang/lang_'.$language.'.php';
                
                $_lang = array_merge($_lang,$mod_lang);
                
                if ( claro_debug_mode() )
                {
                    pushClaroMessage(__FUNCTION__."::".$moduleLabel.'::'
                        . ucfirst( $language ).' lang file loaded', 'debug');
                }
            }
            elseif ( $language != 'english' )
            {
                // no language file to load
                if ( claro_debug_mode() )
                {
                    pushClaroMessage(__FUNCTION__."::".$moduleLabel.'::'
                        . ucfirst( $language ) .' lang file  not found', 'debug');
                }
            }
            else
            {
                // nothing to do
            }
        }
        else
        {
            // Not in a module
        }
        
        $GLOBALS['_lang'] = array_merge($GLOBALS['_lang'], $_lang);
    }
    
    /**
     * Get the current language name
     * @return string
     */
    public static function current_language()
    {
        // FIXME : use init.lib instead of global variables !!!
        $_course = get_init('_course');
        $_user = get_init('_user');

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
                        return $GLOBALS['platformLanguage'];
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
 * @return array
*/
function get_language_list()
{
    // language path
    $language_dirname = get_path('rootSys') . 'claroline/lang/' ;

    // init accepted_values list
    $language_list = array();

    if ( is_dir($language_dirname) )
    {
        // TODO : use DirectoryIterator
        $handle = opendir($language_dirname);
        
        while ( $elt = readdir($handle) )
        {
            if ( $elt == '.' || $elt == '..' || $elt == '.svn' ) continue;

            // skip if not a dir
            if ( is_dir( $language_dirname.$elt ) )
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

/**
 * Get language list for display purpose
 * @param string $param
 * @return array
 */
function get_language_to_display_list( $param = 'language_to_display' )
{
    $language_list = array();

    $language_to_display_list = get_conf( $param );
    $language_to_display_list[] = $GLOBALS['platformLanguage'];

    foreach ( $language_to_display_list as $language )
    {
        $key = ucfirst( get_translation_of_language($language) );
        $value = $language;
        $language_list[$key] = $value;
    }

    asort($language_list);

    return $language_list;
}

/**
 * Get translation of a language name
 * @param string $language
 * @return string
 */
function get_translation_of_language($language)
{
    if ( !empty($GLOBALS['langNameOfLang'][$language])
            && $GLOBALS['langNameOfLang'][$language]!=$language )
    {
        return $GLOBALS['langNameOfLang'][$language];
    }
    else
    {
        return $language;
    }
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
    switch ($size)
    {
        case 'abbr' :
            $nameList = $GLOBALS['langMonthNames']['init'];
            break;
        case 'short' :
            $nameList = $GLOBALS['langMonthNames']['short'];
            break;
        case 'long' :
        default :
            $nameList = $GLOBALS['langMonthNames']['long'];
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
    switch ($size)
    {
        case 'abbr' :
            $nameList = $GLOBALS['langDay_of_weekNames']['init'];
            break;
        case 'short' :
            $nameList = $GLOBALS['langDay_of_weekNames']['short'];
            break;
        case 'long' :
        default :
            $nameList = $GLOBALS['langDay_of_weekNames']['long'];
            break;
    }
    return $nameList;
}

/**
 * Display a date at localized format
 * @copyright   (c) 2001-2014, Universite catholique de Louvain (UCL)
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

/**
 * L10N date for display purpose
 * @param string $formatOfDate
 * @param int $timestamp
 * @return string
 */
function claro_html_localised_date($formatOfDate,$timestamp = -1) //PMAInspiration :)
{
    $langDay_of_weekNames['long'] = get_lang_weekday_name_list('long');
    $langDay_of_weekNames['short'] = get_lang_weekday_name_list('short');

    $langMonthNames['short'] = get_lang_month_name_list('short');
    $langMonthNames['long'] = get_lang_month_name_list('long');

    if ($timestamp == -1) $timestamp = claro_time();

    // with the ereg  we  replace %aAbB of date format
    //(they can be done by the system when  locale date aren't aivailable

    $formatOfDate = preg_replace('/%[A]/', $langDay_of_weekNames['long'][(int)strftime('%w', $timestamp)], $formatOfDate);
    $formatOfDate = preg_replace('/%[a]/', $langDay_of_weekNames['short'][(int)strftime('%w', $timestamp)], $formatOfDate);
    $formatOfDate = preg_replace('/%[B]/', $langMonthNames['long'][(int)strftime('%m', $timestamp)-1], $formatOfDate);
    $formatOfDate = preg_replace('/%[b]/', $langMonthNames['short'][(int)strftime('%m', $timestamp)-1], $formatOfDate);
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
 * Returns utf-8 encoded $str. No changes are made if it was already utf-8
 *
 */
function claro_utf8_encode($str, $fromCharset = '' )
{
    if( $fromCharset != '' )    $charset = $fromCharset;
    else                        $charset = $GLOBALS['charset'];


    if( strtoupper($charset) == 'UTF-8' || seems_utf8($str) )
    {
        return $str;
    }
    elseif ( strtoupper($charset) == 'ISO-8859-1' )
    {
        return utf8_encode( $str );
    }
    elseif ( function_exists('mb_convert_encoding') )
    {
        return mb_convert_encoding( $str, $charset, 'UTF-8' );
    }
    elseif ( function_exists('iconv'))
    {
        return iconv( $charset, 'UTF-8//TRANSLIT', $str );
    }
    else
    {
        $converted = claro_htmlentities( $str, ENT_NOQUOTES, $charset );
        return claro_html_entity_decode( $converted, ENT_NOQUOTES, 'UTF-8' );
    }
}

/**
 * Returns decoded utf-8 $str. No changes are made if it was not utf-8
 *
 */
function claro_utf8_decode($str, $toCharset = '')
{
    if( $toCharset != '' )  $charset = $toCharset;
    else                    $charset = $GLOBALS['charset'];

    if( strtoupper($charset) == 'UTF-8' || !seems_utf8($str) )
    {
        return $str;
    }
    elseif ( strtoupper($charset) == 'ISO-8859-1' )
    {
        return utf8_decode( $str );
    }
    elseif ( function_exists('mb_convert_encoding') )
    {
        return mb_convert_encoding( $str, 'UTF-8', $charset );
    }
    elseif ( function_exists('iconv'))
    {
        return iconv( 'UTF-8', $charset.'//TRANSLIT', $str );
    }
    else
    {
        $converted = claro_htmlentities( $str, ENT_NOQUOTES, 'UTF-8' );
        return claro_html_entity_decode( $converted, ENT_NOQUOTES, $charset );
    }
}

/**
 * Encode an array of string in utf-8 using array walk
 * @param array $var
 */
function claro_utf8_encode_array( &$var )
{
    if ( !is_array( $var ) )
    {
        $var = claro_utf8_encode( $var );
    }
    else
    {
        array_walk( $var, 'claro_utf8_encode_array' );
    }
}

/*
 * Usage :
 * =======
 * 
 * In PHP :
 * --------
 *
 *      JavascriptLanguage::getInstance()->addLangVar('User list');
 *      // ...
 * 
 * In javascript :
 * ---------------
 * 
 *      Claroline.getLang('User list');
*/
class JavascriptLanguage
{    
    private static $variables = array();
    
    /**
     * Add a variable to the Javascript for client-side L10N
     * @param string $varName
     * @param string $varValue
     */
    public function addLangVar ( $varName, $varValue = null )
    {
        self::$variables[$varName] = $varValue ? $varValue : get_lang($varName);
    }
    
    protected function pack()
    {
        $pack = array();
        
        foreach ( self::$variables as $name => $translation )
        {
            $pack[] = '"' 
                . str_replace( '"', '\\"', claro_htmlspecialchars( $name ) ) 
                . '" : \'' 
                . str_replace( "'", "\\'", claro_htmlspecialchars( $translation ) ) 
                . '\''
                ;
        }
        
        return implode ( ",\n\t", $pack ) . "\n";
    }
    
    /**
     * Build the javascript for client side L10N
     * @return string
     */
    public function buildJavascript()
    {
        return "
<script type=\"text/javascript\">
var __ = (function(){

    var translation = {
    " . $this->pack() . "
    };

    return function(string) {
        return translation[string] || string;
    };

})();
</script>
            ";
    }
    
    /**
     * @deprecated called automatically by kernel
     * @return string
     */
    public function render()
    {
        Console::debug( __CLASS__ . '::' . __FUNCTION__ . " is deprecated" );
        
        return '';
    }
    
    protected static $instance = false;
    
    /**
     * Get an instance of the JavascriptLanguage class
     * @return type
     */
    public static function getInstance()
    {
        if ( ! self::$instance )
        {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
}
