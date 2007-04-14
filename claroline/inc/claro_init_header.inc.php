<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );

/* --------------------------------------
 * HEADERS SECTION
 * --------------------------------------*/

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
echo claro_html_doctype() . "\n"
.    '<html>' . "\n"
.    '<head>' . "\n"
;

$titlePage = '';

if(!empty($nameTools))
{
    $titlePage .= $nameTools . ' - ';
}

if(!empty($_course['officialCode']))
{
    $titlePage .= $_course['officialCode'] . ' - ';
}

$titlePage .= get_conf('siteName');

?>
<title><?php echo $titlePage; ?></title>

<meta http-equiv="Content-Script-Type" content="text/javascript" />
<meta http-equiv="Content-Style-Type" content="text/css" />
<meta http-equiv="Content-Type" content="text/HTML; charset=<?php echo $charset; ?>"  />

<link rel="stylesheet" type="text/css" href="<?php echo $clarolineRepositoryWeb ?>css/<?php echo $claro_stylesheet ?>" media="screen, projection, tv" />
<link rel="stylesheet" type="text/css" href="<?php echo $clarolineRepositoryWeb ?>css/print.css" media="print" />

<link rel="top" href="<?php echo $urlAppend ?>/index.php" title="" />
<link href="http://www.claroline.net/documentation.htm" rel="Help" />
<link href="http://www.claroline.net/credits.htm" rel="Author" />
<link href="http://www.claroline.net" rel="Copyright" />

<script type="text/javascript">
document.cookie="javascriptEnabled=true; path=<?php echo get_conf('urlAppend')?>";
<?php
if ( true === get_conf( 'warnSessionLost', true ) && $_uid )
{
    echo "function claro_session_loss_countdown(sessionLifeTime){
    var chrono = setTimeout('claro_warn_of_session_loss()', sessionLifeTime * 1000);
}

function claro_warn_of_session_loss() {
    alert('" . clean_str_for_javascript (get_lang('WARNING ! You have just lost your session on the server.') . "\n" . get_lang('Copy any text you are currently writing and paste it outside the browser')) . "');
}
";
    $claroBodyOnload[] = 'claro_session_loss_countdown(' . ini_get('session.gc_maxlifetime') . ');';
}
?>
</script>

<?php
if ( isset($htmlHeadXtra) && is_array($htmlHeadXtra) )
{
    foreach($htmlHeadXtra as $thisHtmlHead)
    {
        echo($thisHtmlHead);
    }
}

echo '</head>';

if ( isset( $claroBodyOnload ) )
{
    echo '<body dir="' . $text_dir . '" onload="' . implode('', $claroBodyOnload ) . '">';
}
else
{
    echo '<body dir="' . $text_dir . '">';
}

//  Banner

if (!isset($hide_banner) || false == $hide_banner)
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