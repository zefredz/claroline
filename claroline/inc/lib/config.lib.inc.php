<?php // $Id$
/** 
 * CLAROLINE 
 *
 * Config lib contain function to manage conf file
 *
 * @version 1.7 $Revision$
 *
 * @copyright 2001-2005 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE   
 *
 * @see http://www.claroline.net/wiki/config_def/
 *
 * @package CONFIG
 *
 * @author Claro Team <cvs@claroline.net>
 * @author Christophe Gesché <moosh@claroline.net>
 * @author Mathieu Laurent <laurent@cerdecam.be>
 *
 */

/**
 * Proceed to rename conf.php.dist file in unexisting .conf.php files
 *
 * @param string $file syspath:complete path to .dist file
 *
 * @return boolean whether succes return true
 *
 * @author Mathieu Laurent <laurent@cerdecam.be>
 *
 */

function claro_undist_file ($file)
{
    if ( !file_exists($file))
    {
        if ( file_exists($file.".dist"))
        {
            /**
             * @var $perms file permission of dist file are keep to set perms of new file
             */
 
            $perms = fileperms($file.".dist");

            /**
             * @var $group internal var for affect same group to new file
             */
            
            $group = filegroup($file.".dist");
            
            // $perms|bindec(110000) <- preserve perms but force rw right on group
            @copy($file.".dist",$file) && chmod ($file,$perms|bindec(110000)) && @chgrp($file,$group);
            if (file_exists($file))
            {
                return TRUE;
            }
            else
            {
                return FALSE;
            }
        }
        else
        {
            return FALSE;
        }
    }
    else
    {
        return TRUE;
    }
}

/**
 * The boolean value as string
 *
 * @param $booleanState boolean
 *
 * @return string boolean value as string
 * * CLAROLINE
 *
 * @version 1.4
 *
 */
function trueFalse($booleanState)
{
    return ($booleanState?'TRUE':'FALSE');
}

/**
 * Replace value of variable in file
 *
 * @param string $varName name of the variable
 * @param string $value new value of the variable
 * @param string $file path to file
 *
 * @return whether the success
 *
 * @author Benoit
 * @deprecated          
 */

function replace_var_value_in_conf_file ($varName,$value,$file)
{

    $replace = false;

    // Quote regular expression characters of varName

    if ($varName != '')
    {
        // build regexp
        $regVarName = preg_quote($varName);
        $regExp = '~(\$(' . $regVarName . '))[[:space:]]*=[[:space:]]*(.*);~U';
    }
    else
    {
        return false;
    }

    if(file_exists($file))
    {
        //Open config file
        if($fp = @fopen($file,"r"))
        {
            // take all lines in the file
            while(!feof($fp))
            {
                // length param in fgets is required before PHP 4.2.0
                $line=fgets($fp,1024);
                trim($line);

                unset($find);
                $find = preg_match_all($regExp,$line,$result);

                if($find)
                {
                    // $result[0] the variable and the value
                    // $result[1] the name of the variable
                    // $result[2] the value

                    // replace the variable with the new value
                    $line = str_replace($result[3]," \"".$value."\"",$line);
                    $replace = true;
                }
                //Create a table with correct ligne to create de new file config
                $newLines[]= $line;
            }
            fclose($fp);
        }
        else
        {
            // can't open file in read
            return false;
        }
    }
    else
    {
        // file doesn't exists
        return false;
    }

    if ($replace)
    {

        // rewrite file
        if($nf=@fopen($file,"w+"))
        {
            if(isset($newLines))
            {
                foreach($newLines as $line)
                {
                    fwrite($nf,$line);
                }
            }
            fclose($nf);
        }
        else
        {
            // can't open file in write
            return false;
        }
    }

    return true;

}


/**
 * brutal replacement of ; by :
 * stripslashes striptags trim
 * these functions are  use to manage free strings.
 * function cleanvalue($string) this function remove tags, ; , top and terminal blank
 * this function is called by two others
 *
 * function cleanoutputvalue($string) protect html entities before an output (in html page)
 * function cleanwritevalue($string) protect befor write it in a file between " ";
 *
 * @param string string:
 * @return string trimed and without ;
 **/
function cleanvalue($string)
{
    return trim(str_replace(';',':',strip_tags(stripslashes($string))));
}

/**
 * Make string ready to output in a html stream
 *
 * @param $string string change
 * @return string prepare to output in html stream
 **/
function cleanoutputvalue($string)
{
    return trim(htmlspecialchars(cleanvalue($string)));
}

/**
 * Make string ready to output in a php file
 *
 * @param $string
 * @return string cleaned string
 **/
function cleanwritevalue($string)
{
    return trim(str_replace('"','\"',cleanvalue($string)));
}

/**
 * return array list of found definition files
 * @return array list of found definition files
 * @global string includePath use to access to def repository.
 */

function get_def_file_list()
{
    GLOBAL $includePath ;
    
    $defConfFileList = array();
    if (is_dir($includePath . '/conf/def') && $handle = opendir($includePath . '/conf/def'))
    {
        // group of def list

        // Browse folder of definition file

        while (FALSE !== ($file = readdir($handle)))
        {

            if ($file != "." && $file != ".." && substr($file, -17)=='.def.conf.inc.php')
            {
                $config_code = str_replace('.def.conf.inc.php','',$file);
                $defConfFileList[$config_code] ['name'] = get_conf_name($config_code);
                $defConfFileList[$config_code] ['class'] = get_conf_class($config_code);
            }
        }
        closedir($handle);
        return $defConfFileList;
    }
    else
    {
        return FALSE;
    }

}

/**
 * Return the complete path and name of the config file of a given $config_code
 *
 * @param  string $config_code the config code to process
 * @global string includePath use to access to conf repository.
 * @return string the name of the config file (with complete path)
 *
 */

function get_conf_file($config_code)
{
    global $includePath;

    // include definition file and get $conf_def array
    $def_file = get_def_file($config_code);
    unset($conf_def);
    if (file_exists($def_file)) include $def_file;

    if ( isset($conf_def['config_file']) && !empty($conf_def['config_file']) )
    {
       // get the name of config file in definition file
       return realpath($includePath . '/conf/').'/'.$conf_def['config_file'];
    }
    else
    {
       // build the filename with the config_code
       return realpath($includePath . '/conf/').'/'.$config_code.'.conf.php';
    }
}

/**
 * Return the complete path and name of the definition file of a given $config_code
 *
 * @param  string $config_code the config code to process
 * @global string includePath use to access to def repository.
 * @return the name of the config file (with complete path)
 *
 */

function get_def_file($config_code)
{
    global $includePath;

    $def_filename = realpath($includePath . '/conf/def/' . $config_code . '.def.conf.inc.php');

    return $def_filename;
}


/**
 * Return the name (public label) of the Config of a given $config_code
 *
 * @param   $config_code string the config code to process
 *
 * @return  the name of the config 
 *
 */

function get_conf_name($config_code)
{
    $def_file = get_def_file($config_code);

     // include definition file and get $conf_def array
    unset($conf_def);
    if ( file_exists($def_file) )
        include $def_file;

    if ( isset($conf_def['config_name']) )
    {
        // name is the config name
        $name = $conf_def['config_name'];
    }
    else
    {
        if ( isset($conf_def['config_file']) )
        {
            // name is the config_file name
            $name = $conf_def['config_file'];
        }
        else
        {
            // name is the config code
            $name = $config_code;
        }
    }
    return $name;
}

/**
 * Return the name (public label) of the Config of a given $config_code
 *
 * @param  string $config_code the config code to process
 *
 * @return the name of the config 
 *
 */

function get_conf_class($config_code)
{
    $def_file = get_def_file($config_code);

     // include definition file and get $conf_def array
    unset($conf_def);
    if ( file_exists($def_file) )
        include $def_file;

    $class = isset($conf_def['config_class']) 
    ? strtolower($conf_def['config_class'])
    : 'other';

    return $class;
}

/**
 * @param config_code string code of a claroline config.
 * @return the hash code stored for a given config
 */

function get_conf_hash($config_code)
{
   $tbl_mdb_names   = claro_sql_get_main_tbl();
   $tbl_config_file = $tbl_mdb_names['config_file'];

   $sql = ' SELECT `config_hash` `config_hash`
            FROM `'.$tbl_config_file.'`
            WHERE `config_code` = "'. addslashes($config_code) .'"';

   $result = claro_sql_query($sql);

   if ($row = mysql_fetch_row($result))
   {
       // return hash value
       return $row[0];
   }
   else
   {
       // no hash value in db
       return '';
   }
}

/**
 * @param string $config_code code of a claroline config.
 * @return boolean true if the config of the given config_code 
 *         is change since the last build by config tool  
 */

function is_conf_file_modified($config_code)
{
    $conf_file = get_conf_file($config_code);

    if ( file_exists($conf_file) )
    {
        $hash = get_conf_hash($config_code);
        if ( !empty($hash) && $hash != md5_file($conf_file) )
        //if ( !empty($hash) && $hash != filemtime($conf_file) )
        {
            // file is modified
            return TRUE;
        }
        else
        {
            // no hash value in db
            return FALSE;
        }
    }
    else
    {
        // conf file doesn't exists
        return FALSE;
    }

}

/**
 *
 * @param mixed $propertyValue
 * @param array $propertyDef
 * @return boolean whether the value is valide following the rules in $propertyDef
 * @global array controlMsg array push a line if the validation failed
 */
function validate_property ($propertyValue, $propertyDef)
{
    global $controlMsg, $rootSys;

    $is_valid = TRUE;

    // get validation value from property definition
    if ( isset($propertyDef['acceptedValue']) ) $acceptedValue = $propertyDef['acceptedValue'];
    else                                        $acceptedValue = array();
    if ( isset($propertyDef['label']) ) $propertyName  = $propertyDef['label'];
    else                                $propertyName  = '';
    if ( isset($propertyDef['type']) )  $type = $propertyDef['type'];
    else                                $type = '';

    if( is_array($propertyDef) )
    {
                // display label
        // if Type = css or lang,  acceptedValue would be fill
        // and work after as enum.
        switch($type)
        {
            case 'css' :
                if ($handle = opendir($rootSys.'claroline/css'))
                {
                    $acceptedValue=array();
                   while (false !== ($file = readdir($handle)))
                   {
                       $ext = strrchr($file, '.');
                       if ($file != "." && $file != ".." && (strtolower($ext)==".css"))
                       {
                           $acceptedValue[$file] = $file;
                       }
                   }
                   closedir($handle);
                }

                $type='enum';
                break;
            case 'lang' :
                $dirname = $rootSys . 'claroline/lang/';
                if($dirname[strlen($dirname)-1]!='/')
                    $dirname.='/';
                $handle=opendir($dirname);
                $acceptedValue=array();
                while ($entries = readdir($handle))
                {
                    if ($entries=='.' || $entries=='..' || $entries=='CVS')
                        continue;
                    if (is_dir($dirname . $entries))
                    {
                        $acceptedValue[$entries] = $entries;
                    }
                }
                closedir($handle);
                $type='enum';
                break;
			case 'editor' :
                $dirname = $rootSys . 'claroline/editor/';
                if($dirname[strlen($dirname)-1]!='/')
                    $dirname.='/';
                $handle=opendir($dirname);
                $acceptedValue=array();
                while ($entries = readdir($handle))
                {
                    if ($entries=='.' || $entries=='..' || $entries=='CVS')
                        continue;
                    if (is_dir($dirname . $entries))
                    {
                        $acceptedValue[$entries] = $entries;
                    }
                }
                closedir($handle);
                $type='enum';
                break;
        }



        switch($type)
        {
            case 'boolean' :
                if ( !($propertyValue==TRUE || $propertyValue==FALSE) )
                {
                    $controlMsg['error'][] = $propertyName.' would be boolean';
                    $is_valid = FALSE;
                }
                break;

            case 'integer' :
                if ( eregi("[^0-9]",$propertyValue) )
                {
                    $controlMsg['error'][] = $propertyName.' would be integer';
                    $is_valid = FALSE;
                }
                elseif ( isset($acceptedValue['max']) && $acceptedValue['max'] < $propertyValue )
                {
                    $controlMsg['error'][] = $propertyName.' would be integer inferior or equal to '.$acceptedValue['max'];
                    $is_valid = FALSE;
                }
                elseif ( isset($acceptedValue['min']) && $acceptedValue['min'] > $propertyValue )
                {
                    $controlMsg['error'][] = $propertyName.' would be integer superior or equal to '.$acceptedValue['min'];
                    $is_valid = FALSE;
                }
                break;

            case 'lang' :

            case 'enum' :
                if ( is_array($acceptedValue) )
                {
                    if ( !in_array($propertyValue, array_keys($acceptedValue)) )
                    {
                        $controlMsg['error'][] = $propertyName.' would be in enum list';
                        $is_valid = FALSE;
                    }
                }
                else
                {
                    trigger_error('propertyDef is not an array, coding error', E_USER_WARNING);
                }
                break;

            case 'relpath' :
                break;
            case 'syspath' :
            case 'wwwpath' :
                if ( empty($propertyValue) )
                {
                    $controlMsg['error'][] = $propertyName.' is empty';
                    $is_valid = FALSE;
                }
                break;
            case 'regexp' :
                if (isset($acceptedValue) && !eregi( $acceptedValue, $propValue ))
                {
                    $controlMsg['error'][] = $propertyName.' would be valid for '.$acceptedValue;
                    $is_valid = FALSE;
                }
                break;
            case 'php' :
                if (eval('$foo ='.$propertyValue.';'))
                {
                    $controlMsg['error'][] = $propertyName.' would be php valid code return in 1 value';
                    $is_valid = FALSE;
                }
                break;
            case 'string' :
            default :
        }
    }
    else
    {
        //trigger_error('propertyDef is not an array, coding error',E_USER_WARNING);
        return false;
    }
    return $is_valid;
}

function save_config_hash_in_db($config_code,$conf_hash)
{
    // get table name of config file
    $mainTbl = claro_sql_get_main_tbl();
    $tbl_config_file = $mainTbl['config_file'];

    // update config : set hash
    $sql =" UPDATE `" . $tbl_config_file  . "`"
    .     " SET config_hash = '" . addslashes($conf_hash) . "'"
    .     " WHERE config_code = '" . addslashes($config_code) . "'" ;

    if ( !claro_sql_query_affected_rows($sql) )
    {
        // insert an entry for config_file
        $sql ="INSERT IGNORE INTO `" . $tbl_config_file  . "` 
               SET config_hash = '" . addslashes($conf_hash) . "'
               ,   config_code = '" . addslashes($config_code) . "'";
        return claro_sql_query($sql);
    }
    else
    {
        return true;
    }
}

function write_conf_file($conf_def,$conf_def_property_list,$storedPropertyList,$confFile,$generatorFile=__FILE__)
{
    global $dateTimeFormatLong;

    if ( strlen($generatorFile)>50 )
    {
        $generatorFile = str_replace("\\","/",$generatorFile);
        $generatorFile = "\n\t\t".str_replace("/","\n\t\t/",$generatorFile);
    }

    $fileHeader = '<?php '."\n"
                  . '/** '
                  . ' * DONT EDIT THIS FILE - NE MODIFIEZ PAS CE FICHIER '."\n"
                  . ' * -------------------------------------------------'."\n"
                  . ' * Generated by '.$generatorFile.' '."\n"
                  . ' * Date '.claro_disp_localised_date($dateTimeFormatLong)."\n"
                  . ' * -------------------------------------------------'."\n"
                  . ' * DONT EDIT THIS FILE - NE MODIFIEZ PAS CE FICHIER '."\n"
                  . ' **/'."\n\n"
                  . '// $'.$conf_def['config_code'].'GenDate is an internal mark'."\n"
                  . '   $'.$conf_def['config_code'].'GenDate = "'.time().'";'."\n\n"
                  . (isset($conf_def['technicalInfo'])
                  ? '/*'
                  . str_replace('*/', '* /', $conf_def['technicalInfo'])
                  . '*/'
                  : '')
                  ;

    // open configuration file
    if ( $handle = fopen($confFile,'w') )
    {

        // write header
        fwrite($handle,$fileHeader);

        foreach($storedPropertyList as $storedProperty)
        {
            // Writting of a properties include
            // The  comment from technical info
            // the creation (const or var)
            // the comment  of lastChange

            $propertyName = $storedProperty['propName'];
            $propertyValue = $storedProperty['propValue'];

            if ( isset($conf_def_property_list[$propertyName]['container']) )
            {
                $container = $conf_def_property_list[$propertyName]['container'];
            }
            else
            {
                $container = '';
            }

            if ( isset($conf_def_property_list[$propertyName]['description']) )
            {
                $description = $conf_def_property_list[$propertyName]['description'];
            }
            else
            {
                $description = '';
            }

            if ( isset($conf_def_property_list[$propertyName]['technicalInfo']) )
            {
                $technicalInfo = $conf_def_property_list[$propertyName]['technicalInfo'];
            }
            else
            {
                $technicalInfo = '';
            }

            // property type define how to write the value
            switch ($conf_def_property_list[$propertyName]['type'])
            {
                case 'boolean':
                    if ( is_bool($propertyValue) ) 
                    {
                        $valueToWrite = trueFalse($propertyValue);
                    } 
                    else
                    {
                        $valueToWrite = $propertyValue;
                    }
                    break;
                case 'php':
                case 'integer':
                    $valueToWrite = $propertyValue;
                    break;
                default:
                    $valueToWrite = "'". str_replace("'","\'",$propertyValue) . "'";
                    break;
            }

            // description
            if ( !empty($description) )
            {
                $propertyDesc = '/* ' . $propertyName . ' : ' . str_replace("\n",'',$description) . ' */' . "\n";
            }
            else
            {
                if ( isset($conf_def_property_list[$propertyName]['label']) )
                {
                    $propertyDesc = '/* '.$propertyName.' : '.str_replace("\n","",$conf_def_property_list[$propertyName]['label']).' */'."\n";
                }
                else
                {
                    $propertyDesc = '';
                }
            }

            // technical information
            if ( !empty($technicalInfo) )
            {
                $propertyDesc .= '/*'."\n"
                               . str_replace('*/', '* /', $conf_def_property_list[$propertyName]['technicalInfo'])."\n"
                               . '*/'."\n";
            }


            // container : Constance or variable
            if ( isset($conf_def_property_list[$propertyName]['container']) )
            {
                $container = $conf_def_property_list[$propertyName]['container'];
            }
            else
            {
                $container = '';
            }
            
            if ( strtoupper($container)=='CONST' )
            {
                $propertyLine = 'if (!defined("'.$propertyName.'")) define("'.$propertyName.'",'.$valueToWrite.');'."\n";
            }
            else
            {
                $propertyLine = '$'.$propertyName.' = '. $valueToWrite .';'."\n";
            }
            $propertyLine .= "\n\n";

            fwrite($handle,$propertyDesc);
            fwrite($handle,$propertyLine);

        }
        fwrite($handle,"\n".'?>');
        fclose($handle);
        return TRUE;
    }
    else
    {
        return FALSE;
    }
}

/**
 * Parse a php file and return an array of containers of a present affectation
 *
 * @return  array where key are value name, and content is value
 *
 * @global  string 
 */

function parse_config_file($conf_file)
{

    if( file_exists($conf_file) )
    {
        $code = file_get_contents($conf_file);
        $tokens = token_get_all($code);

        include($conf_file);

        $vars = array();
        for($i=0; $i < count($tokens); $i++)
        {
            if (($tokens[$i][0] == T_VARIABLE ))
            {
                $possibleVar = substr($tokens[$i][1], 1);
                if (  $tokens[$i+1][0] == T_WHITESPACE
                && $tokens[$i+2] == '='
                )
                {
                    $i += 2;
                    if ($tokens[$i+1][0] == T_WHITESPACE) $i++;
                    $vars[$possibleVar] = '';
                    while ($i++)
                    {
                        if ($tokens[$i] == ';') break;
                        if (is_array($tokens[$i]))
                        $val = $tokens[$i][1];
                        else
                        $val = $tokens[$i];
                        $vars[$possibleVar] .= $val;
                    }
                }
                //$propList[$possibleVar] =  $vars[$possibleVar];
                $propList[$possibleVar] =  $$possibleVar;
            }
            elseif (($tokens[$i][0] == T_CONSTANT_ENCAPSED_STRING ))
            {
                $tokens[$i][1] = str_replace('\'','',$tokens[$i][1]);
                $tokens[$i][1] = str_replace('"','',$tokens[$i][1]);
                if (defined($tokens[$i][1]))
                {
                    unset($value);

                    @eval('$value = '.$tokens[$i][1].';');
                    $propList[$tokens[$i][1]] =  $value;
                }
            }
        }
    }
    else
    {
        $propList = array();
    }
    return  $propList;
}

function claroconf_disp_editbox_of_a_value($property_def, $property_name, $currentValue=NULL)
{
    if (  isset($property_def['type']) ) $type = $property_def['type'];
    else                                 $type = '';

    // current value: set to TRUE or false if boolean
    if ( is_bool($currentValue) )
    {
        $currentValue = $currentValue?'TRUE':'FALSE';
    }

    // name of the form element
    $htmlPropName = 'prop['.($property_name).']';

    // label of the form element
    if ( isset($property_def['label']) )
    {
        $htmlPropLabel = htmlspecialchars($property_def['label']);
    }
    else
    {
        $htmlPropLabel = htmlspecialchars($property_name);
    }

    // type description to display
    if ( strlen($type) > 0 )
    {
        $htmlPropType = ' <small>(' . htmlspecialchars($type) . ')</small>';
    }
    else
    {
        $htmlPropType = '';
    }

    // actual value to display
    if ( isset($property_def['acceptedValue'][$currentValue]) )
    {
        // get the label of the actual value
        $actual_value = $property_def['acceptedValue'][$currentValue];
    }
    else
    {
        $actual_value = $currentValue;
    }

    // default value to display
    // 1st, if  boolean, stringify value

    if ( isset($property_def['default']) ) $default = $property_def['default'];
    else                                   $default = '';

    if ( $type == 'boolean' )
    {
        $fooDef = trueFalse($default);
    }
    else
    {
        $fooDef = $default;
    }

    if ( isset($property_def['acceptedValue'][$fooDef]) )
    {
        $default_value = $property_def['acceptedValue'][$fooDef];
    }
    else
    {
        $default_value = $fooDef;
    }

    // description to display 
    if ( isset($property_def['description']) )
    {
        $htmlPropDesc = nl2br(htmlspecialchars($property_def['description']));
    }
    else
    {
        $htmlPropDesc = '';
    }
 
    if ( isset($currentValue) )
    {
        $htmlPropValue = $currentValue;

        /* Default value  have  perhaps no sense to be display
        As $htmlPropDefault is never set with this comment, I comment  his display below in the code
        if ( $currentValue != $property_def['default']) 
        {
            $htmlPropDefault = 'Default : ' . (empty($property_def['default'])?get_lang('Empty'):$default_value);
        }
        */
    } 
    else
    {
        // if not set --> default value
        $htmlPropValue = $default;
    }

    $size = (int) strlen($htmlPropValue);
    $size = 2+(($size > 50)?50:(($size < 15)?15:$size));

    $htmlUnit = (isset($property_def['unit'])?''.htmlspecialchars($property_def['unit']).' ':'');

    if ( isset($property_def['display']) && !$property_def['display'] )
    {
        echo '<input type="hidden" value="'. htmlspecialchars($htmlPropValue).'" name="'.$htmlPropName.'">'."\n";
    }
    elseif ( isset($property_def['readonly']) && $property_def['readonly'] )
    {
        echo '<tr style="vertical-align: top">' .
             '<td style="text-align: right" width="250">' . $htmlPropLabel . '&nbsp;:</td>' . "\n";

        echo '<input type="hidden" value="'. htmlspecialchars($htmlPropValue).'" name="'.$htmlPropName.'">'."\n";

        echo '<td nowrap="nowrap">' . "\n";

        switch ( $type )
        {
            case 'boolean' :
            case 'lang' :
            case 'enum' :
                if ( isset($property_def['acceptedValue'][$htmlPropValue]) )
                {
                    echo $property_def['acceptedValue'][$htmlPropValue];
                }
                else
                {
                    echo $htmlPropValue;
                }
                break;
            case 'integer' :
            case 'string' :
            default:
                // probably a string or integer
                if ( empty($htmlPropValue) )
                {
                    echo get_lang('Empty');
                }
                else
                {
                    echo $htmlPropValue;
                }
        } // switch

        echo '</td>';
        echo '<td><em>' . $htmlPropDesc . '</em></td>' . "\n";
        echo '</tr>';
    }
    else
    // Prupose a form following the type
    {
        // display label
        // if Type = css or lang,  acceptedValue would be fill
        // and work after as enum.
        $invalid_css = array('print.css','compatible.css');

        switch ( $type )
        {
            case 'css' :
                if ($handle = opendir('../../css'))
                {
                   $property_def['acceptedValue']=array();
                   while (false !== ($file = readdir($handle)))
                   {
                       $ext = strrchr($file, '.');
                       if ($file != "." && $file != ".." && (strtolower($ext)==".css"))
                       {
                            if ( ! in_array($file,$invalid_css) )
                            {
                                $property_def['acceptedValue'][$file] = $file;
                            }
                       }
                   }
                   closedir($handle);
                }
                $type = 'enum';
                break;
            case 'lang' :
                $dirname = '../../lang/';
                if($dirname[strlen($dirname)-1]!='/')
                    $dirname.='/';
                $handle=opendir($dirname);
                $property_def['acceptedValue']=array();
                while ($entries = readdir($handle))
                {
                    if ($entries=='.'||$entries=='..'||$entries=='CVS')
                        continue;
                    if (is_dir($dirname.$entries))
                    {
                        $property_def['acceptedValue'][$entries] = $entries;
                    }
                }
                closedir($handle);
                $type = 'enum';
                break;
            case 'editor' :
                $dirname = '../../editor/';
                if($dirname[strlen($dirname)-1]!='/')
                    $dirname.='/';
                $handle=opendir($dirname);
                $property_def['acceptedValue']=array();
                while ($entries = readdir($handle))
                {
                    if ($entries=='.'||$entries=='..'||$entries=='CVS')
                        continue;
                    if (is_dir($dirname.$entries))
                    {
                        $property_def['acceptedValue'][$entries] = $entries;
                    }
                }
                closedir($handle);
                $type = 'enum';
                break;
        }

        switch( $type )
        {
            case 'boolean' :
                echo '<tr style="vertical-align: top">' .
                    '<td style="text-align: right" width="250">' . $htmlPropLabel . '&nbsp;:</td>' ;

                echo '<td nowrap="nowrap">' . "\n";

                echo '<input id="'.$property_name.'_TRUE"  type="radio" name="'.$htmlPropName.'" value="TRUE"  '
                    . ($htmlPropValue=='TRUE'?' checked="checked" ':' ')
                    . ' >' ;
                echo '<label for="'.$property_name.'_TRUE"  >'
                     . ($property_def['acceptedValue']['TRUE' ]?$property_def['acceptedValue']['TRUE' ]:'TRUE' )
                     . '</label>';

                echo '<br />';

                echo '<input id="'.$property_name.'_FALSE" type="radio" name="'.$htmlPropName.'" value="FALSE" '
                     . ($htmlPropValue=='FALSE'?' checked="checked" ': ' ')
                     . ' >' ;
                echo '<label for="'.$property_name.'_FALSE" >'
                     .($property_def['acceptedValue']['FALSE']?$property_def['acceptedValue']['FALSE']:'FALSE')
                     .'</label>';
                break;

            case 'enum' :

                echo '<tr style="vertical-align: top">' ;

                if ( count($property_def['acceptedValue']) > 3 )
                {
                    echo '<td style="text-align: right" width="250"><label for="' . $property_name . '"  >' . $htmlPropLabel . '&nbsp;:</label></td>' ;
                    echo '<td nowrap="nowrap">' . "\n";
                    echo '<select id="' . $property_name . '" name="'.$htmlPropName.'">' . "\n";

                    foreach($property_def['acceptedValue'] as  $keyVal => $labelVal)
                    {
                        if ($htmlPropValue==$keyVal)
                        {
                            echo '<option value="'. htmlspecialchars($keyVal) .'" selected="selected">' . ($labelVal?$labelVal:$keyVal ). $htmlUnit .'</option>' . "\n";
                        }
                        else
                        {
                            echo '<option value="'. htmlspecialchars($keyVal) .'">' . ($labelVal?$labelVal:$keyVal ). $htmlUnit .'</option>' . "\n";
                        }
                    }

                    echo '</select>' . "\n";

                }
                else
                {
                    echo '<td style="text-align: right" width="250">' . $htmlPropLabel . '&nbsp;:</td>' ;
                    echo '<td nowrap="nowrap">' . "\n";

                    foreach($property_def['acceptedValue'] as  $keyVal => $labelVal)
                    {
                        echo '<input id="'.$property_name.'_'.$keyVal.'"  type="radio" name="'.$htmlPropName.'" value="'.$keyVal.'"  '
                        .($htmlPropValue==$keyVal?' checked="checked" ':' ')
                        .' >'
                        .'<label for="'.$property_name.'_'.$keyVal.'"  >'.($labelVal?$labelVal:$keyVal ).'</label>'
                        .'<span class="propUnit">'.$htmlUnit.'</span>'
                        .'<br />'."\n";
                    }
                }
                break;

            //TYPE : integer, an integer is attempt
            case 'integer' :

                echo '<tr style="vertical-align: top">' .
                    '<td style="text-align: right" width="250"><label for="'.$property_name.'"  >' . $htmlPropLabel . '&nbsp;:</label></td>' ;

                echo '<td nowrap="nowrap">' . "\n";

                echo '<input size="'.$size.'"  align="right" id="'.$property_name.'" type="text" name="'.$htmlPropName.'" value="'. htmlspecialchars($htmlPropValue) .'"> '."\n"
                .'<span class="propUnit">'.$htmlUnit.'</span>'
                .'<span class="propType">'.$htmlPropType.'</span>'
                ."\n" ;
                break;

            default:
                // probably a string
                echo '<tr style="vertical-align: top">' .
                    '<td style="text-align: right" width="250"><label for="'.$property_name.'"  >' . $htmlPropLabel . '&nbsp;:</label></td>' ;

                echo '<td nowrap="nowrap">' . "\n";
                echo '<input size="'.$size.'"  id="'.$property_name.'" type="text" name="'.$htmlPropName.'" value="'. htmlspecialchars($htmlPropValue) .'"> '
                .'<span class="propUnit">'.$htmlUnit.'</span>'
                .'<span class="propType">'.$htmlPropType.'</span>'."\n";

        } // switch

        echo '</td><td>';
        /*  this disabling is  commented  in $htmlPropDefault setting  100 lines before.
        if (!empty($htmlPropDefault))
        {
            echo '<small>(' . $htmlPropDefault . ' ' . $htmlUnit . ')</small> ';
        }
        */
        if (!empty($htmlPropDesc))
        {
            echo '<em>' . $htmlPropDesc. '</em>';
        }
        echo '</td></tr>' . "\n";

    } // else
}

// To work
/*
function claro_create_conf_filename($config_code)
{
    return true;
}

function validate_conf_property ($name, $value, $config_code)
{
    return true;
}

function validate_conf_properties ($properties, $config_code)
{
    return true;
}

*/
/**
 * return value found in a given file following a given property list
 *
 */
function get_values_from_confFile($file_name,$conf_def_property_list)
{
    $value_list = array();
    if(file_exists($file_name))
    {
        include($file_name);
        if ( is_array($conf_def_property_list) )
        {
            foreach($conf_def_property_list as $propName => $propDef )
            {
                if ( isset($propDef['container']) && $propDef['container']=='CONST')
                {
                    if ( defined($propName) )
                        @eval('$value_list[$propName] = '.$propName.';');
                }
                else
                {
                    if(isset($$propName))
                    {
                        $value_list[$propName] = $$propName;
                    }
                    else 
                    {
                        $value_list[$propName] = null;
                    }
                    
                }
            }
        }
    }
    return $value_list;
}


/**
 * redefine  unexisting function (for older php)
 */

if (!function_exists('md5_file'))
{
    function md5_file($file_name)
    {
        $fileContent = file($file_name);
        $fileContent = !$fileContent ? FALSE : implode('', $fileContent);
        return md5($fileContent);
    }
}


?>
