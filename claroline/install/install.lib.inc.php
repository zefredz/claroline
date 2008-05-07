<?php // $Id$

if ( count( get_included_files() ) == 1 ) die( basename(__FILE__) );

/**
 * CLAROLINE
 *
 * This lib prupose function use by installer.
 *
 * @version 1.8 $Revision$
 *
 * @copyright 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @see http://www.claroline.net/wiki/index.php/Install
 *
 * @author Claro Team <cvs@claroline.net>
 * @author Christophe Gesché <moosh@claroline.net>
 * @author Sebastien Piraux <seb@claroline.net>
 * @author Frederic Minne <zefredz@claroline.net>
 *
 * @package INSTALL
 *
 */

/**
 * check extention and  write  if exist  in a  <LI></LI>
 *
 * @param   string $extentionName name  of  php extention to be checked
 * @param   boolean $echoWhenOk true => show ok when  extention exist
 *
 */
function warnIfExtNotLoaded($extentionName,$echoWhenOk=false)
{
    if (extension_loaded ($extentionName))
    {
        if ($echoWhenOk)
        echo '<LI>'
        .    $extentionName
        .    ' - ok '
        .    '</LI>'
        ;
    }
    else
    {
        echo '<LI>'
        .    '<font color="red">Warning !</font>'
        .    $extentionName . ' is missing.</font>'
        .    '<br />'
        .    'Configure php to use this extention'
        .    '(see <a href="http://www.php.net/' . $extentionName . '">'
        .    $extentionName
        .    ' manual</a>)'
        .    '</LI>'
        ;
    }
}

/**
 * Search read and write access from the given directory to root
 *
 * @param path string path where begin the scan
 * @return array with 2 fields "topWritablePath" and "topReadablePath"
 *
 * @var $serchtop log is only use for debug
 */
function topRightPath($path='.')
{
    $whereIam = getcwd();
    chdir($path);
    $pathToCheck = realpath('.');
    $previousPath=$pathToCheck.'*****';

    $search_top_log = 'top right Path'.'<dl>';
    while(!empty($pathToCheck))
    {
        $pathToCheck = realpath('.');
        if (is_writable($pathToCheck))
        $topWritablePath = $pathToCheck;
        if (is_readable($pathToCheck))
        $topReadablePath = $pathToCheck;
        $search_top_log .= '<dt>' . $pathToCheck . '</dt>'
                        .  '<dd>write:'
                        .  (is_writable($pathToCheck)?'open':'close')
                        .  ' read:'
                        .  (is_readable($pathToCheck)?'open':'close')
                        .  '</dd>'
                        ;
        if (   $pathToCheck != '/'
           && $pathToCheck != $previousPath
           && (  is_writable($pathToCheck)
              || is_readable($pathToCheck)
              )
           )
        {
            chdir('..') ;
            $previousPath=$pathToCheck;
        }
        else
        {
            $pathToCheck ='';
        }

    }
    $search_top_log .= '</dl>'
    .  'topWritablePath = ' . $topWritablePath . '<br />'
    .  'topReadablePath = ' . $topReadablePath
    ;

    //echo $search_top_log;
    chdir($whereIam);
    return array("topWritablePath" => $topWritablePath, "topReadablePath" => $topReadablePath);
};

function check_if_db_exist($db_name,$db=null)
{

    // I HATE THIS SOLUTION .
    // It's would be better to have a SHOW DATABASE case insensitive
    // IF SHOW DATABASE IS NOT AIVAILABLE,   sql failed an function return false.
    if (PHP_OS != 'WIN32' && PHP_OS != 'WINNT')
    {
        $sql = "SHOW DATABASES LIKE '" . $db_name . "'";
    }
    else
    {
        $sql = "SHOW DATABASES LIKE '" . strtolower($db_name) . "'";
    }

    if ($db)
    {
        $res = claro_sql_query($sql,$db);
    }
    else
    {
        $res = claro_sql_query($sql);
    }

    if( mysql_errno() == 0 )
    {
        $foundDbName = mysql_fetch_array($res, MYSQL_NUM);
    }
    else
    {
        $foundDbName = false;
    }

    return $foundDbName;
}

/**
 * check current version is equal or greater than required version
 *
 * @param string $currentVersion like  '1.1.1'
 * @param string $requiredVersion like  '1.1.1'
 * @return boolean
 *
 * @todo check if param have a good format
 */
function checkVersion($currentVersion, $requiredVersion)
{
    $currentVersion = explode('.',$currentVersion);
    $requiredVersion = explode('.',$requiredVersion);

    if ((int) $currentVersion[0] < (int) $requiredVersion[0]) return false;
    elseif ((int) $currentVersion[0] > (int) $requiredVersion[0]) return true;
    else
    {
        if ((int) $currentVersion[1] < (int) $requiredVersion[1]) return false;
        elseif ((int) $currentVersion[1] < (int) $requiredVersion[1]) return true;
        else
        {
            if ((int) $currentVersion[2] < (int) $requiredVersion[2]) return false;
        }
    }
    return true;
}


/**
 */
function check_php_setting($php_setting, $recommended)
{
    $current = get_php_setting($php_setting);
    
    if( $current == strtoupper($recommended) )
    {
        return '<span class="ok">'.$current.'</span>';
    }
    else
    {
        return '<span class="ko">'.$current.'</span>';
    }
}

/**
 * Enter description here...
 *
 * @param string $val a php ini value
 * @return boolean: ON or OFF
 * @author Joomla <http://www.joomla.org>
 */
function get_php_setting( $val )
{
    return ( ini_get( $val ) == '1' ) ? 'ON' : 'OFF';
}

/**
 * Find all install.lang.php files in lang dirs and returns langs where this file is available
 *
 * @return array
 */
function get_available_install_language()
{
    $languageList = array();
    
    $it = new DirectoryIterator('../lang/');
    
    foreach( $it as $file )
    {
        if( $file->isDir() && !$file->isDot() )
        {
    
            if( file_exists( '../lang/' . $file->getFileName() . '/install.lang.php' ) )
            {
                $languageList[] = $file->getFileName();
            }
        }
        
    }
    
    return $languageList;
}

/**
 * Display database error
 * @param   string $query sql query
 * @param   string $error error message
 * @param   int $errno error number
 */
function displayDbError( $query, $error, $errno )
{
    echo '<hr size="1" noshade>'
        . $errno, " : ", $error, '<br>'
        . '<pre style="color:red">'
        . $query
        . '</pre>'
        . '<hr size="1" noshade>';
        
    return true;
}

/**
 * Installer class
 */
class ClaroInstaller
{
    protected $mainTblPrefix, $statsTblPrefix;
    
    public function __construct( $mainTblPrefix, $statsTblPrefix )
    {
        $this->mainTblPrefix = $mainTblPrefix;
        $this->statsTblPrefix = $statsTblPrefix;
    }
    
    public function executeSqlScript( $sqlStr, $onErrorCallback = false )
    {
        $queries = $this->pmaParse( $sqlStr );
        
        if ( ! $onErrorCallback )
        {
            $onErrorCallback = array( $this, 'onErrorCallback' );
        }
        
        foreach ( $queries as $query )
        {
            if ( ! mysql_query( $this->toClaroQuery( $query['query'] ) ) )
            {
                
                if ( call_user_func( $onErrorCallback,
                        $query, mysql_error(), mysql_errno() ) )
                {
                    continue;
                }
                else
                {
                    throw new Exception( mysql_error(), mysql_errno() );
                }
            }
        }
    }
    
    public function onErrorCallback( $query, $error, $errno )
    {
        return false;
    }
    
    public function toClaroQuery( $sql )
    {
        // replace __CL_MAIN__ with main database prefix
        $sql = str_replace ( '__CL_MAIN__', $this->mainTblPrefix, $sql );
        // replace __CL_MAIN__ with main database prefix
        $sql = str_replace ( '__CL_STATS__', $this->statsTblPrefix, $sql );
        
        return $sql;
    }
    
    protected function pmaParse( $sql )
    {
        $ret = array();
        
        $sql          = rtrim($sql, "\n\r");
        $sql_len      = strlen($sql);
        $char         = '';
        $string_start = '';
        $in_string    = false;
        $nothing      = true;
        
        for ($i = 0; $i < $sql_len; ++$i)
        {
            $char = $sql[$i];
            // We are in a string, check for not escaped end of strings except for
            // backquotes that can't be escaped
            if ($in_string)
            {
                for (;;)
                {
                    $i         = strpos($sql, $string_start, $i);
                    // No end of string found -> add the current substring to the
                    // returned array
                    if (!$i)
                    {
                        $ret[] = array('query' => $sql, 'empty' => $nothing);
                        return $ret;
                    }
                    // Backquotes or no backslashes before quotes: it's indeed the
                    // end of the string -> exit the loop
                    else if ($string_start == '`' || $sql[$i-1] != '\\')
                    {
                        $string_start      = '';
                        $in_string         = false;
                        break;
                    }
                    // one or more Backslashes before the presumed end of string...
                    else
                    {
                        // ... first checks for escaped backslashes
                        $j                     = 2;
                        $escaped_backslash     = false;
                        while ($i-$j > 0 && $sql[$i-$j] == '\\') {
                            $escaped_backslash = !$escaped_backslash;
                            $j++;
                        }
                        // ... if escaped backslashes: it's really the end of the
                        // string -> exit the loop
                        if ($escaped_backslash)
                        {
                            $string_start  = '';
                            $in_string     = false;
                            break;
                        }
                        // ... else loop
                        else
                        {
                            $i++;
                        }
                    } // end if...elseif...else
                } // end for
            } // end if (in string)
            
            // lets skip comments (/*, -- and #)
            else if (($char == '-' && $sql_len > $i + 2 && $sql[$i + 1] == '-' && $sql[$i + 2] <= ' ') 
                || $char == '#' || ($char == '/' && $sql_len > $i + 1 && $sql[$i + 1] == '*'))
            {
                $i = strpos($sql, $char == '/' ? '*/' : "\n", $i);
                // didn't we hit end of string?
                if ($i === false)
                {
                    break;
                }
                if ($char == '/') $i++;
            }
            
            // We are not in a string, first check for delimiter...
            else if ($char == ';')
            {
                // if delimiter found, add the parsed part to the returned array
                $ret[]      = array('query' => substr($sql, 0, $i), 'empty' => $nothing);
                $nothing    = true;
                $sql        = ltrim(substr($sql, min($i + 1, $sql_len)));
                $sql_len    = strlen($sql);
                if ($sql_len)
                {
                    $i      = -1;
                }
                else
                {
                    // The submited statement(s) end(s) here
                    return $ret;
                }
            } // end else if (is delimiter)
    
            // ... then check for start of a string,...
            else if (($char == '"') || ($char == '\'') || ($char == '`'))
            {
                $in_string    = true;
                $nothing      = false;
                $string_start = $char;
            } // end else if (is start of string)
            elseif ($nothing)
            {
                $nothing = false;
            }
        } // end for
    
        // add any rest to the returned array
        if (!empty($sql) && preg_match('@[^[:space:]]+@', $sql))
        {
            $ret[] = array('query' => $sql, 'empty' => $nothing);
        }
        
        return $ret;
    }
}

