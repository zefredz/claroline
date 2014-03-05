<?php // $Id$

/**
 * Claroline extension modules cache generation functions
 *
 * @version     1.12 $Revision$
 * @copyright   (c) 2001-2014, Universite catholique de Louvain (UCL)
 * @license     http://www.gnu.org/copyleft/gpl.html
 *      GNU GENERAL PUBLIC LICENSE version 2 or later
 * @author      Claro Team <cvs@claroline.net>
 * @package     kernel.module
 * @since       1.12
 */

function generate_module_names_translation_cache()
{
    $cacheRepositorySys = get_path('rootSys') . get_conf('cacheRepository', 'tmp/cache/');
    $moduleLangCache = $cacheRepositorySys . 'module_lang_cache';

    if ( ! file_exists($moduleLangCache) )
    {
        claro_mkdir( $moduleLangCache, CLARO_FILE_PERMISSIONS, true );
    }

    $tbl = claro_sql_get_main_tbl();
    $sql = "SELECT `name`, `label`
              FROM `" . $tbl['module'] . "`
             WHERE activation = 'activated'";

    $module_list = claro_sql_query_fetch_all($sql);

    $langVars = array();

    foreach ( $module_list as $module )
    {
        $langPath = get_module_path( $module['label'] ).'/lang/';

        if ( file_exists( $langPath ) )
        {
            $it = new DirectoryIterator( $langPath );

            foreach ( $it as $file )
            {
                if ( $file->isFile()
                    && preg_match('/^lang_\w+.php$/', $file->getFilename() ) )
                {
                    $langName = str_replace( 'lang_', '', $file->getFilename() );
                    $langName = str_replace( '.php', '', $langName );

                    if ( $langName != 'english' )
                    {
                        pushClaroMessage( $langName . ':' . $module['label'], 'debug');

                        $_lang = array();

                        ob_start();
                        include $file->getPathname();
                        ob_end_clean();

                        if ( ! isset( $langVars[$langName] ) )
                        {
                            $langVars[$langName] = '';
                        }

                        if ( isset($_lang[$module['name']]) )
                        {
                            $langVars[$langName] .= '$_lang[\''.$module['name'].'\'] = \''.str_replace( "'", "\\'", $_lang[ $module['name'] ]).'\';'."\n";
                        }
                    }
                }
            }
        }
    }

    foreach ( $langVars as $lgnNm => $contents )
    {
        $langFile = $moduleLangCache . '/'.$lgnNm.'.lang.php';

        if ( file_exists( $langFile ) )
        {
            unlink( $langFile );
        }

        file_put_contents( $langFile, "<?php\n".$contents );
    }
}

/**
 * Generate the cache php file with the needed include of activated module of the platform.
 * @return boolean true if succeed, false on failure
 */
function generate_module_cache()
{

    $module_cache_filename = get_conf('module_cache_filename','moduleCache.inc.php');
    $cacheRepositorySys = get_path('rootSys') . get_conf('cacheRepository', 'tmp/cache/');
    $module_cache_filepath = $cacheRepositorySys . $module_cache_filename;

    if ( ! file_exists( $cacheRepositorySys ) )
    {
        claro_mkdir($cacheRepositorySys, CLARO_FILE_PERMISSIONS, true);
    }

    $tbl = claro_sql_get_main_tbl();
    $sql = "SELECT `label`
              FROM `" . $tbl['module'] . "`
             WHERE activation = 'activated'";

    $module_list = claro_sql_query_fetch_all($sql);

    if (file_exists($cacheRepositorySys) && is_writable($cacheRepositorySys))
    {
        if ( file_exists( $module_cache_filepath ) && ! is_writable( $module_cache_filepath ) )
        {
            return claro_failure::set_failure('cannot write to cache file ' . $module_cache_filepath);
        }
        else
        {
            if ( false !== ( $handle = fopen($module_cache_filepath, 'w') ) )
            {
                $cache = '<?php #auto created by claroline modify it at your own risks'."\n";
                $cache .= 'if (count( get_included_files() ) == 1) die();'."\n";
                $cache .= "\n" . '# ---- start of cache ----'."\n\n";

                foreach($module_list as $module)
                {
                    $functionsFilePath = get_module_path($module['label']) . '/functions.php';

                    if (file_exists( $functionsFilePath ))
                    {
                        $cache .= '# ' . $module['label'] . "\n" ;
                        $cache .= 'if (file_exists(get_module_path("'.addslashes($module['label']).'")."/functions.php") ){' . "\n";
                        $cache .= 'set_current_module_label("'.addslashes($module['label']).'");' . "\n";
                        $cache .= 'load_module_config("'.addslashes($module['label']).'");' . "\n";
                        $cache .= 'language::load_module_translation("'.addslashes($module['label']).'","'.language::current_language().'");' . "\n";
                        $cache .= 'require get_module_path("'.addslashes($module['label']).'")."/functions.php";' . "\n";
                        $cache .= 'clear_current_module_label();'. "\n";
                        $cache .= '}' . "\n";
                    }
                }

                $cache .= "\n";

                fwrite( $handle, $cache );
                fclose( $handle );
            }
            else
            {
                return claro_failure::set_failure('Cannot open path %path', array('%path'=> $module_cache_filepath));
            }
        }
    }
    else
    {
        // FIXME E_USER_ERROR instead of E_USER_NOTICE
        return claro_failure::set_failure('Directory %directory is not writable', array('%directory' => $cacheRepositorySys) );
    }

    generate_module_names_translation_cache();

    return true;
}
