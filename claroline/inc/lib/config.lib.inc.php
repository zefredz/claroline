<?php // $Id$
/** ***************************************************
 * config lib contain function to manage conf file
 ******************************************************
 * @version CLAROLINE 1.6
 * @copyright &copy; 2001-2005 Universite catholique de Louvain (UCL)
 * @license This program is under the terms of the
 * GENERAL PUBLIC LICENSE (GPL) as published by the
 * FREE SOFTWARE FOUNDATION. The GPL is available
 * through the world-wide-web at
 * http://www.gnu.org/copyleft/gpl.html
 * @see http://www.claroline.net/wiki/config_def/
 * @package CONFIG
 * @author Christophe Gesch�<moosh@claroline.net>
 ******************************************************
 */

/**
 * Config tools 1.5
 */

/**
* proceed to rename conf.php.dist file in unexisting .conf.php files
*
* @author Mathieu Laurent <laurent@cerdecam.be>
*
* @param $file syspath:complete path to .dist file
* @return  boolean:wheter succes return true
* @var $perms file permission of dist file are keep to set perms of new file
* @var $group internal var for affect same group to new file
**/

function claro_undist_file ($file)
{
    if ( !file_exists($file))
    {
        if ( file_exists($file.".dist"))
        {
            $perms = fileperms($file.".dist");
            $group = filegroup($file.".dist");
            // $perms|bindec(110000) <- preserve perms but force rw right on group
            @copy($file.".dist",$file) && chmod ($file,$perms|bindec(110000)) && chgrp($file,$group);
            if (file_exists($file))
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }
    else
    {
        return false;
    }
}

/**
 * the boolean value as string
 *
 * @return the boolean value as string
 * @param $booleanState boolean
 * @version claroline 1.4
 */
function trueFalse($booleanState)
{
    return ($booleanState?'TRUE':'FALSE');
}

/**
 * Replace value of variable in file
 * @return wether the success
 * @param $varName name of the variable
 * @param $value new value of the variable
 * @param $file string path to file
 * @author Benoit
*/

function replace_var_value_in_conf_file ($varName,$value,$file)
{

 $replace = false;

 // Quote regular expression characters of varName

 if ($varName != "")
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

/// these functions are  use to manage free strings.
// function cleanvalue($string) this function remove tags, ; , top and terminal blank
// this function is called by two others

// function cleanoutputvalue($string) protect html entities before an output (in html page)
// function cleanwritevalue($string) protect befor write it in a file between " ";


/**
 * brutal replacement of ; by :
 * stripslashes striptags trim
 *
 * @param string string:
 * @return string:trimed and without ;
 **/
function cleanvalue($string)
{
    return trim(str_replace(';',':',strip_tags(stripslashes($string))));
}

/**
 * cleanoutputvalue()
 *
 * @param $string string change
 * @return string:prepare to output in html stream
 **/
function cleanoutputvalue($string)
{
    return trim(htmlspecialchars(cleanvalue($string)));
}

/**
 * cleanwritevalue()
 *
 * @param $string
 * @return string:cleaned string
 **/
function cleanwritevalue($string)
{
    return trim(str_replace('"','\"',cleanvalue($string)));
}

/**
 * Config tools 1.6
 */

function get_def_file_list()
{
    global $includePath, $toolNameList;

    $defConfFileList = array();

    if ($handle = opendir($includePath.'/conf/def'))
    {
        // group of def list

        $defConfFileList['platform']['name'] = 'Platform';
        $defConfFileList['course']['name']   = 'Course';
        $defConfFileList['user']['name']     = 'User';
        $defConfFileList['tool']['name']     = 'Tool';
        $defConfFileList['others']['name']   = 'Others';

        // Browse folder of definition file

        while (FALSE !== ($file = readdir($handle)))
        {

            if ($file != "." && $file != ".." && substr($file, -17)=='.def.conf.inc.php')
            {
                $config_code = str_replace('.def.conf.inc.php','',$file);

                if ( $config_code == 'CLMAIN' || $config_code == 'CLHOME')
                {
                    $defConfFileList['platform']['conf'][$config_code] = get_conf_name($config_code);
                }
                elseif ( $config_code == 'CLCRS')
                {
                    $defConfFileList['course']['conf'][$config_code] = get_conf_name($config_code);
                }
                elseif ( $config_code == 'CLPROFIL')
                {
                    $defConfFileList['user']['conf'][$config_code] = get_conf_name($config_code);
                }
                elseif ( is_array($toolNameList) && array_key_exists(str_pad($config_code,8,'_'),$toolNameList) )
                {
                    $defConfFileList['tool']['conf'][$config_code] = $toolNameList[str_pad($config_code,8,'_')];
                }
                else
                {
                    $defConfFileList['others']['conf'][$config_code] = get_conf_name($config_code);
                }
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
 * @param   $config_code string the config code to process
 * @return  the name of the config file (with complete path)
 *
 * @example get_conf_file('CLCAL');
 */

function get_conf_file($config_code)
{
   global $includePath;

   // include definition file and get $conf_def array
   $def_file = get_def_file($config_code);
   if (file_exists($def_file)) include $def_file;

   if ( isset($conf_def['config_file']) && !empty($conf_def['config_file']) )
   {
       // get the name of config file in definition file
       return realpath($includePath.'/conf/').'/'.$conf_def['config_file'];
   }
   else
   {
       // build the filename with the config_code
       return realpath($includePath.'/conf/').'/'.$config_code.'.conf.php';
   }
}

/**
 * Return the complete path and name of the definition file of a given $config_code
 *
 * @param   $config_code string the config code to process
 * @return  the name of the config file (with complete path)
 *
 * @example get_def_file('CLCAL');
 */

function get_def_file($config_code)
{
    global $includePath;

    $def_filename = realpath($includePath.'/conf/def/'.$config_code.'.def.conf.inc.php');

    return $def_filename;
}


function get_conf_name($config_code)
{
    $def_file = get_def_file($config_code);

    // include definition file and get $conf_def array
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

function get_conf_hash($config_code)
{
   $tbl_mdb_names   = claro_sql_get_main_tbl();
   $tbl_config_file = $tbl_mdb_names['config_file'];

   $sql = ' SELECT `config_hash` `config_hash`
            FROM `'.$tbl_config_file.'`
            WHERE `config_code` = "'.$config_code.'"';

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

function validate_property ($propertyValue, $propertyDef)
{
    global $controlMsg;

    $is_valid = TRUE;

    // get validation value from property definition
    $acceptedValue = $propertyDef['acceptedValue'];
    $propertyName  = $propertyDef['label'];
    $type          = $propertyDef['type'];



    if( is_array($propertyDef) )
    {
                // display label
        // if Type = css or lang,  acceptedValue would be fill
        // and work after as enum.
        switch($type)
        {
            case 'css' :
                if ($handle = opendir('../../css'))
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
                $dirname = '../../lang/';
                if($dirname[strlen($dirname)-1]!='/')
                    $dirname.='/';
                $handle=opendir($dirname);
                $acceptedValue=array();
                while ($entries = readdir($handle))
                {
                    if ($entries=='.'||$entries=='..'||$entries=='CVS')
                        continue;
                    if (is_dir($dirname.$entries))
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
                if ( !($propertyValue=='TRUE' || $propertyValue=='FALSE') )
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
                elseif ( isset($acceptedValue['max']) && $acceptedValue['max']<$propertyValue )
                {
                    $controlMsg['error'][] = $propertyName.' would be integer inferior or equal to '.$acceptedValue['max'];
                    $is_valid = FALSE;
                }
                elseif ( isset($acceptedValue['min']) && $acceptedValue['min']>$propertyValue )
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
                    trigger_error('propertyDef is not an array, coding error',E_USER_WARNING);
                }
                break;

            case 'relpath' :
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

function save_property_in_db($propertyName,$propertyValue,$config_code)
{
    // get config property table name
    $mainTblName = claro_sql_get_main_tbl();
    $tbl_config_property = $mainTblName['config_property'];

    // try to update existing property
    $sql ='UPDATE
            `'.$tbl_config_property.'`
           SET propName    ="'.$propertyName.'",
               propValue   ="'.$propertyValue.'",
               lastChange  = now()
           WHERE propName    ="'.$propertyName.'"
             AND config_code ="'.$config_code.'"
             ';

    if ( !claro_sql_query_affected_rows($sql) )
    {
        // insert new property
        $sql ='INSERT
                   INTO `'.$tbl_config_property.'`
                   SET propName    = "'.$propertyName.'",
                       propValue   = "'.$propertyValue.'",
                       lastChange  = now(),
                       config_code = "'.$config_code.'"';
        return claro_sql_query($sql);
    }
    else
    {
        return true;
    }
}

function save_config_hash_in_db($conf_file,$config_code,$conf_hash)
{
    // get table name of config file
    $mainTbl = claro_sql_get_main_tbl();
    $tbl_config_file = $mainTbl['config_file'];

    // update config : set hash
    $sql =' UPDATE `'. $tbl_config_file  .'`'
         .' SET config_hash = "'.$conf_hash.'"'
         .' WHERE config_code = "'.$config_code.'" ';

    if ( !claro_sql_query_affected_rows($sql) )
    {
        // insert an entry for config_file
        $sql =' INSERT  INTO `'. $tbl_config_file .'`  '
             .' SET config_hash = "'.$conf_hash.'"     '
             .' , config_code = "'.$config_code.'" ';
        return claro_sql_query($sql);
    }
    else
    {
        return true;
    }
}

/**
 * propName
 * propValue
 * lastChange
 */

function read_properties_in_db($config_code)
{
    $tbl_mdb_names = claro_sql_get_main_tbl();
    $tbl_config_property  = $tbl_mdb_names['config_property'];

    // get value from
    $sql = 'SELECT `propName`, `propValue`, unix_timestamp(`lastChange`) `lastChange`
                             FROM `'.$tbl_config_property.'`
                             WHERE config_code = "'.$config_code.'"';
    $properties = claro_sql_query_fetch_all($sql);

    return $properties;
};

function write_conf_file($conf_def,$conf_def_property_list,$storedPropertyList,$confFile,$generatorFile=__FILE__)
{
    global $_uid,$_user,$dateTimeFormatLong;

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
                  . ' // $'.$conf_def['config_code'].'GenDate is an interna mark'."\n"
                  . '$'.$conf_def['config_code'].'GenDate = "'.time().'";'."\n\n"
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
                case 'php':
                case 'integer':
                    $valueToWrite = $propertyValue;
                    break;
                default:
                    $valueToWrite = "'". $propertyValue . "'";
                    break;
            }

            // description
            if ( !empty($description) )
            {
                $propertyDesc = '/* ' . $propertyName . ' : ' . str_replace("\n","",$description) . ' */' . "\n";
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
            $container = $conf_def_property_list[$propertyName]['container'];
            if ( strtoupper($container)=='CONST' )
            {
                $propertyLine = 'define("'.$propertyName.'",'.$valueToWrite.');'."\n";
            }
            else
            {
                $propertyLine = '$'.$propertyName.' = '.$valueToWrite.';'."\n";
            }
            $propertyLine .= "\n\n";



            fwrite($handle,$propertyDesc);
            fwrite($handle,$propertyLine);
            fwrite($handle,$propertyGenComment);

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
 * @global  $includePath
 * @version  claroline 1.6
 */

function parse_config_file($conf_file)
{
    GLOBAL $includePath;

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

function claroconf_disp_editbox_of_a_value($conf_def_property_list, $property, $currentValue=NULL)
{
    global $langFirstDefOfThisValue, $langEmpty;

    // current value: set to TRUE or false if boolean
    if ( is_bool($currentValue) )
    {
        $currentValue = $currentValue?'TRUE':'FALSE';
    }

    // name
    $htmlPropName = 'prop['.($property).']';

    // label
    if ( isset($conf_def_property_list['label']) )
    {
        $htmlPropLabel = htmlentities($conf_def_property_list['label']);
    }
    else
    {
        $htmlPropLabel = $htmlPropName;
    }

    // type
    if ( is_string($conf_def_property_list['type']) )
    {
        $htmlPropType = ' <small>(' . htmlentities($conf_def_property_list['type']) . ')</small>';
    }
    else
    {
        $htmlPropType = '';
    }

    // actual value
    if ( isset($conf_def_property_list['acceptedValue'][$conf_def_property_list['actualValue']]) )
    {
        $actual_value = $conf_def_property_list['acceptedValue'][$conf_def_property_list['actualValue']];
    }
    else
    {
        $actual_value = $conf_def_property_list['actualValue'];
    }

    // default value
    if ( isset($conf_def_property_list['acceptedValue'][$conf_def_property_list['default']]) )
    {
        $default_value = $conf_def_property_list['acceptedValue'][$conf_def_property_list['default']];
    }
    else
    {
        $default_value = $conf_def_property_list['default'];
    }

   // description
    if ( isset($conf_def_property_list['description']) )
    {
        $htmlPropDesc = nl2br(htmlentities($conf_def_property_list['description']));
    }
    else
    {
        $htmlPropDesc = '';
    }

    if ( isset($currentValue) && $currentValue!=$conf_def_property_list['actualValue'] )
    {
        $htmlPropValue = $currentValue;
    }
    else
    {
        if ( isset($conf_def_property_list['actualValue']) )
        {
            $htmlPropValue = $conf_def_property_list['actualValue'];
            $htmlPropDefault = 'Default : ' . (empty($conf_def_property_list['default'])?$langEmpty:$default_value);
        }
        else
        {
            $htmlPropValue = $conf_def_property_list['default'];
        }
    }

    $size = (int) strlen($htmlPropValue);
    $size = 2+(($size > 50)?50:(($size < 15)?15:$size));

    $htmlUnit = (isset($conf_def_property_list['unit'])?''.htmlentities($conf_def_property_list['unit']).' ':'');

    if (isset($conf_def_property_list['display'])
           &&!$conf_def_property_list['display'])
    {
        echo '<input type="hidden" value="'.$htmlPropValue.'" name="'.$htmlPropName.'">'."\n";
    }
    elseif ($conf_def_property_list['readonly'])
    {
        echo '<tr style="vertical-align: top">' .
             '<td style="text-align: right" width="250">' . $htmlPropLabel . '&nbsp;:</td>' . "\n";

        echo '<input type="hidden" value="'.$htmlPropValue.'" name="'.$htmlPropName.'">'."\n";

        echo '<td nowrap="nowrap">' . "\n";

        switch($conf_def_property_list['type'])
        {
            case 'boolean' :
            case 'lang' :
            case 'enum' :
                if ( isset($conf_def_property_list['acceptedValue'][$htmlPropValue]) )
                {
                    echo $conf_def_property_list['acceptedValue'][$htmlPropValue];
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
                if ( empty($conf_def_property_list['default']) )
                {
                    echo '<em>' . $langEmpty . '</em>';
                }
                else
                {
                    echo $conf_def_property_list['default'];
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
        switch($conf_def_property_list['type'])
        {
            case 'css' :
                if ($handle = opendir('../../css'))
                {
                    $conf_def_property_list['acceptedValue']=array();
                   while (false !== ($file = readdir($handle)))
                   {
                       $ext = strrchr($file, '.');
                       if ($file != "." && $file != ".." && (strtolower($ext)==".css"))
                       {
                           $conf_def_property_list['acceptedValue'][$file] = $file;
                       }
                   }
                   closedir($handle);
                }

                $conf_def_property_list['type']='enum';
                break;
            case 'lang' :
                $dirname = '../../lang/';
                if($dirname[strlen($dirname)-1]!='/')
                    $dirname.='/';
                $handle=opendir($dirname);
                $conf_def_property_list['acceptedValue']=array();
                while ($entries = readdir($handle))
                {
                    if ($entries=='.'||$entries=='..'||$entries=='CVS')
                        continue;
                    if (is_dir($dirname.$entries))
                    {
                        $conf_def_property_list['acceptedValue'][$entries] = $entries;
                    }
                }
                closedir($handle);
                $conf_def_property_list['type']='enum';
                break;
        }

        switch($conf_def_property_list['type'])
        {
            case 'boolean' :
                echo '<tr style="vertical-align: top">' .
                    '<td style="text-align: right" width="250">' . $htmlPropLabel . '&nbsp;:</td>' ;

                echo '<td nowrap="nowrap">' . "\n";

                echo '<input id="'.$property.'_TRUE"  type="radio" name="'.$htmlPropName.'" value="TRUE"  '
                    . ($htmlPropValue=='TRUE'?' checked="checked" ':' ')
                    . ' >' ;
                echo '<label for="'.$property.'_TRUE"  >'
                     . ($conf_def_property_list['acceptedValue']['TRUE' ]?$conf_def_property_list['acceptedValue']['TRUE' ]:'TRUE' )
                     . '</label>';

                echo '<br />';

                echo '<input id="'.$property.'_FALSE" type="radio" name="'.$htmlPropName.'" value="FALSE" '
                     . ($htmlPropValue=='TRUE'?' ':' checked="checked" ')
                     . ' >' ;
                echo '<label for="'.$property.'_FALSE" >'
                     .($conf_def_property_list['acceptedValue']['FALSE']?$conf_def_property_list['acceptedValue']['FALSE']:'FALSE')
                     .'</label>';
                break;
            case 'enum' :

                echo '<tr style="vertical-align: top">' ;

                if ( count($conf_def_property_list['acceptedValue']) > 3 )
                {
                    echo '<td style="text-align: right" width="250"><label for="' . $property . '"  >' . $htmlPropLabel . '&nbsp;:</label></td>' ;
                    echo '<td nowrap="nowrap">' . "\n";
                    echo '<select id="' . $property . '" name="'.$htmlPropName.'">' . "\n";

                    foreach($conf_def_property_list['acceptedValue'] as  $keyVal => $labelVal)
                    {
                        if ($htmlPropValue==$keyVal)
                        {
                            echo '<option name="'.$htmlPropName.'" selected="selected">' . ($labelVal?$labelVal:$keyVal ). $htmlUnit .'</option>' . "\n";
                        }
                        else
                        {
                            echo '<option name="'.$htmlPropName.'">' . ($labelVal?$labelVal:$keyVal ). $htmlUnit .'</option>' . "\n";
                        }
                    }

                    echo '</select>' . "\n";

                }
                else
                {
                    echo '<td style="text-align: right" width="250">' . $htmlPropLabel . '&nbsp;:</td>' ;
                    echo '<td nowrap="nowrap">' . "\n";

                    foreach($conf_def_property_list['acceptedValue'] as  $keyVal => $labelVal)
                    {
                        echo '<input id="'.$property.'_'.$keyVal.'"  type="radio" name="'.$htmlPropName.'" value="'.$keyVal.'"  '
                        .($htmlPropValue==$keyVal?' checked="checked" ':' ')
                        .' >'
                        .'<label for="'.$property.'_'.$keyVal.'"  >'.($labelVal?$labelVal:$keyVal ).'</label>'
                        .'<span class="propUnit">'.$htmlUnit.'</span>'
                        .'<br />'."\n";
                    }
                }
                break;

            //TYPE : integer, an integer is attempt
            case 'integer' :

                echo '<tr style="vertical-align: top">' .
                    '<td style="text-align: right" width="250"><label for="'.$property.'"  >' . $htmlPropLabel . '&nbsp;:</label></td>' ;

                echo '<td nowrap="nowrap">' . "\n";

                echo '<input size="'.$size.'"  align="right" id="'.$property.'" type="text" name="'.$htmlPropName.'" value="'.$htmlPropValue.'"> '."\n"
                .'<span class="propUnit">'.$htmlUnit.'</span>'
                .'<span class="propType">'.$htmlPropType.'</span>'
                ."\n" ;
                break;

            default:
                // probably a string
                echo '<tr style="vertical-align: top">' .
                    '<td style="text-align: right" width="250"><label for="'.$property.'"  >' . $htmlPropLabel . '&nbsp;:</label></td>' ;

                echo '<td nowrap="nowrap">' . "\n";
                echo '<input size="'.$size.'"  id="'.$property.'" type="text" name="'.$htmlPropName.'" value="'.$htmlPropValue.'"> '
                .'<span class="propUnit">'.$htmlUnit.'</span>'
                .'<span class="propType">'.$htmlPropType.'</span>'."\n";

        } // switch

        echo '</td><td>';

        if (!empty($htmlPropDefault))
        {
            echo '<small>(' . $htmlPropDefault . ' ' . $htmlUnit . ') ';
        }

        if (!empty($htmlPropDesc))
        {
            echo '<em>' . $htmlPropDesc. '</em>';
        }
        echo '</small>' . "\n";
        echo '</td></tr>' . "\n";

    } // else
}

// To work

function claro_create_conf_filename($config_code)
{
}

function validate_conf_property ($name, $value, $config_code)
{

}

function validate_conf_properties ($properties, $config_code)
{

}



/**
 * redefine  unexisting function (for older php)
 */

if (!function_exists('md5_file'))
{
    function md5_file($file_name)
    {
       $fileContent = file($file_name);
       $fileContent = !$file ? false : implode('', $fileContent);
       return md5($fileContent);
    }
}


?>
