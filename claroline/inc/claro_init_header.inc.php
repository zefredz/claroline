<?php // $Id$
if ((bool) stristr($_SERVER['PHP_SELF'], basename(__FILE__))) die('---');

/*----------------------------------------
              HEADERS SECTION
  --------------------------------------*/

/*
 * HTTP HEADER
 */
if (isset($charset)) header('Content-Type: text/html; charset='. $charset);

if (!empty($httpHeadXtra) && is_array($httpHeadXtra) )
{
    foreach($httpHeadXtra as $thisHttpHead)
    {
        header($thisHttpHead);
    }
}

/*
 * HTML HEADER
 */

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<?php

$titlePage = '';

if(!empty($nameTools))
{
    $titlePage .= $nameTools . ' - ';
}

if(!empty($_course['officialCode']))
{
    $titlePage .= $_course['officialCode'] . ' - ';
}

$titlePage .= $siteName; 

?>

<title><?php echo $titlePage; ?></title>

<meta http-equiv="Content-Script-Type" content="text/javascript" />
<meta http-equiv="Content-Style-Type" content="text/css" />
<meta http-equiv="Content-Type" content="text/HTML; charset=<?php echo $charset; ?>"  />

<link rel="stylesheet" type="text/css" href="<?php echo $clarolineRepositoryWeb ?>css/<?php echo $claro_stylesheet ?>" media="screen, projection, tv" />
<link rel="stylesheet" type="text/css" href="<?php echo $clarolineRepositoryWeb ?>css/print.css" media="print" />


<link rel="top" href="<?php echo $rootWeb ?>index.php" title="" />
<link rel="courses" href="<?php echo $clarolineRepositoryWeb ?>auth/courses.php" title="<?php echo get_lang('Course list') ?>" />
<link rel="profil" href="<?php echo $clarolineRepositoryWeb ?>auth/profile.php" title="<?php echo get_lang('My User Account') ?>" />
<link href="http://www.claroline.net/documentation.htm" rel="Help" />
<link href="http://www.claroline.net/credits.htm" rel="Author" />
<link href="http://www.claroline.net" rel="Copyright" />

<script type="text/javascript">document.cookie="javascriptEnabled=true";</script>
<?php
if ( !empty($htmlHeadXtra) && is_array($htmlHeadXtra) )
{
    foreach($htmlHeadXtra as $thisHtmlHead)
    {
        echo($thisHtmlHead);
    }
}
?>
</head>

<?php

//add onload javascript function calls to body
if( isset($claroBodyOnload) && is_array($claroBodyOnload) && count($claroBodyOnload) > 0 )
{
    $onload = ' onload="';
    $onload .= implode('', $claroBodyOnload );
    $onload .= '"';
} 
else
{
    $onload = '';
}

echo '<body dir="' . $text_dir . '" ' . $onload . '>';

//  Banner

if (!isset($hide_banner) || $hide_banner == false) 
{
    include dirname(__FILE__) . '/claro_init_banner.inc.php' ;
}

if (!isset($hide_body) || $hide_body == false)
{
    // need body div
    echo "\n\n\n" 
    .    '<!-- - - - - - - - - - - Claroline Body - - - - - - - - - -->' . "\n"
    .    '<div id="claroBody">' . "\n\n"
    ;
}
?>
