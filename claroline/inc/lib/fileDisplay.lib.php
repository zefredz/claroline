<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );

/**
 * CLAROLINE
 *
 * @version 1.8 $Revision$
 *
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @see http://www.claroline.net/wiki/config_def/
 *
 * @package KERNEL
 *
 * @author Claro Team <cvs@claroline.net>
 *
 */


/**
 * Define the image to display for each file extension
 * This needs an existing image repository to works
 *
 * @author - Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param  - fileName (string) - name of a file
 * @retrun - the gif image to chose
 */

function choose_image($fileName)
{
    static $type, $image;

    /* TABLES INITILIASATION */

    if (!$type || !$image)
    {
        $type['word'      ] = array('doc', 'dot', 'rtf', 'mcw', 'wps');
        $type['web'       ] = array('htm', 'html', 'htx', 'xml', 'xsl', 'php');
        $type['image'     ] = array('gif', 'jpg', 'png', 'bmp', 'jpeg');
        $type['audio'     ] = array('wav', 'midi', 'mp2', 'mp3', 'mp4', 'vqf');
        $type['excel'     ] = array('xls', 'xlt');
        $type['compressed'] = array('zip', 'tar', 'rar', 'gz');
        $type['code'      ] = array('js', 'cpp', 'c', 'java');
        $type['acrobat'   ] = array('pdf');
        $type['powerpoint'] = array('ppt', 'pps');
        $type['link'      ] = array('url');
        $type['writer'	  ] = array('odt');
        $type['calc'	  ] = array('ods');
        $type['base'	  ] = array('odb');
        $type['draw'	  ] = array('odg');
        $type['impress'	  ] = array('odp');
        $type['math'	  ] = array('odf');

        $image['word'      ] = 'doc.gif';
        $image['web'       ] = 'html.gif';
        $image['image'     ] = 'gif.gif';
        $image['audio'     ] = 'wav.gif';
        $image['excel'     ] = 'xls.gif';
        $image['compressed'] = 'zip.gif';
        $image['code'      ] = 'js.gif';
        $image['acrobat'   ] = 'pdf.gif';
        $image['powerpoint'] = 'ppt.gif';
        $image['link'      ] = 'link.gif';
        $image['writer'    ] = 'odt.png';
        $image['calc'      ] = 'ods.png';
        $image['base'      ] = 'odb.png';
        $image['draw'      ] = 'odg.png';
        $image['impress'   ] = 'odp.png';
        $image['math'      ] = 'odf.png';
    }

    /* FUNCTION CORE */

    if (ereg("\.([[:alnum:]]+)$", $fileName, $extension))
    {
        $extension[1] = strtolower ($extension[1]);

        foreach( $type as $genericType => $typeList)
        {
            if (in_array($extension[1], $typeList))
            {
                return$image[$genericType];
            }
        }
    }

    return 'default.gif';
}

//------------------------------------------------------------------------------

/**
 * Transform the file size in a human readable format
 *
 * @author - ???
 * @param  - fileSize (int) - size of the file in bytes
 */

function format_file_size($fileSize)
{
    // byteUnits is setted in trad4all
    global $byteUnits;

    if($fileSize >= 1073741824)
    {
        $fileSize = round($fileSize / 1073741824 * 100) / 100 . '&nbsp;' . $byteUnits[3]; //GB
    }
    elseif($fileSize >= 1048576)
    {
        $fileSize = round($fileSize / 1048576 * 100) / 100 . '&nbsp;' . $byteUnits[2]; //MB
    }
    elseif($fileSize >= 1024)
    {
        $fileSize = round($fileSize / 1024 * 100) / 100 . '&nbsp;' . $byteUnits[1]; //KB
    }
    else
    {
        $fileSize = $fileSize . '&nbsp;' . $byteUnits[0];
    }

    return $fileSize;
}


//------------------------------------------------------------------------------


/**
 * Transform a UNIX time stamp in human readable format date
 *
 * @author - Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param - date - UNIX time stamp
 */

function format_date($fileDate)
{
    return date('d.m.Y', $fileDate);
}

//------------------------------------------------------------------------------


/**
 * Transform the file path in a url
 *
 * @param - url (string) - relative local path of the file on the Hard disk
 * @return - relative url
 */

function url_already_encoded( $url )
{
    return ( false !== strpos( $url, '%' ) );
}

function format_url($url)
{
    if ( url_already_encoded( $url ) )
    {
        return $url;
    }

    $urlArray = parse_url( $url );


	$urlToRet = isset($urlArray['scheme'])
		? $urlArray['scheme']
		: ''
		;

    if ( isset($urlArray['scheme'])
        && 'mailto' == $urlArray['scheme'] )
    {
        $urlToRet .= ':';
    }
    elseif ( isset($urlArray['scheme']) )
    {
        $urlToRet .= '://';
    }

    if ( isset( $urlArray['user'] ) )
    {
        $urlToRet = $urlArray['user'];
        $urlToRet .= isset( $urlArray['pass'] )
            ? ':'.$urlArray['pass']
            : ''
            ;
        $urlToRet .= '@';
    }

    $urlToRet .= isset( $urlArray['host']  )
    	? $urlArray['host']
    	: ''
    	;
    $urlToRet .= isset( $urlArray['port']  )
        ? ':' . $urlArray['port']
        : ''
        ;

    $urlToRet .= isset( $urlArray['path'] )
        ? '/' . format_url_path( substr( $urlArray['path'],  1 ) )
        : ''
        ;

    $urlToRet .= isset( $urlArray['query'] )
        ? '?' . format_url_query( $urlArray['query'] )
        : ''
        ;

    $urlToRet .= isset( $urlArray['fragment'] )
        ? '#' . $urlArray['fragment']
        : ''
        ;

    return $urlToRet;
}

/**
 * Enter description here...
 *
 * @param string $path
 * @return string
 *
 */
function format_url_path( $path )
{
    $pathElementList = explode('/', $path);

    for ($i = 0; $i < sizeof($pathElementList); $i++)
    {
        $pathElementList[$i] = rawurlencode($pathElementList[$i]);
    }

    return implode('/',$pathElementList);
}

/**
 * Enter description here...
 *
 * @param string $query
 * @return string
 */
function format_url_query( $query )
{
    $ret = '';

    if ( strpos( $query, '&' ) !== false
        || strpos( $query, '&amp;' ) !== false
        || strpos( $query, '=' ) !== false )
    {
        $queryArray = preg_split( '~(&|&amp;)~', $query );
        $parts = array();
        foreach ( $queryArray as $part )
        {
            if ( preg_match( '~(.*?)=(.*?)~', $part ) )
            {
                $parts[] = preg_replace_callback( '~(.+?)=(.+)~', 'query_make_part', $part );
            }
            elseif ( preg_match( '~/?[^=]+~', $part ) )
            {
                // option 1 :
                $parts[] = '/' . format_url_path( substr( $part,  1 ) );
                // option 2
                // $parts[] = $part;
                // option 3
                // $parts[] = rawurlencode($part);
            }
            else
            {
                // option 1
                // $parts[] = $part;
                // option 2
                // $parts[] = rawurlencode($part);
            }
        }
        $ret = implode( '&', $parts );
    }
    elseif ( strpos( $query, '/' ) !== false )
    {
        $ret = format_url_path( $query );
    }
    else
    {
        $ret = rawurlencode( $query );
    }

    return $ret;
}


/**
 * Callbacked function
 *
 * @param array $matches
 * @return string
 */
function query_make_part( $matches )
{
    return $matches[1] . '=' . rawurlencode( $matches[2] );
}


//------------------------------------------------------------------------------


/**
 *
 *
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 * @param string $curDirPath current path in the documents tree navugation
 * @return string breadcrumb trail
 */

function claro_disp_document_breadcrumb($curDirPath)
{
    $curDirPathList = explode('/', $curDirPath);

    $urlTrail = '';

    $breadcrumbNameList = array();
    $breadcrumbUrlList  = array();

    foreach($curDirPathList as $thisDir)
    {
        if ( empty($thisDir) )
        {
            $breadcrumbNameList[] = get_lang('Root');
            $breadcrumbUrlList[]  = '?cmd=exChDir&amp;file=';
        }
        else
        {
            $breadcrumbNameList[] = $thisDir;
            $urlTrail .= '/'.$thisDir;
            $breadcrumbUrlList[] = $_SERVER['PHP_SELF']
                                 . '?cmd=exChDir&amp;file='.rawurlencode($urlTrail);
        }
    }

    // remove the url on the last (current) element
    $breadcrumbUrlList[ count($breadcrumbUrlList) - 1] = null;

    return claro_html_breadcrumbtrail($breadcrumbNameList, $breadcrumbUrlList);
}

?>