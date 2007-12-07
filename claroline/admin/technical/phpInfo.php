<?php // $Id$
/**
 * CLAROLINE
 *
 * This script present state of
 * - configuration of Claroline, PHP, Mysql, Webserver
 * - credits
 *
 * @version 1.8 $Revision$
 * @copyright (c) 2001-2006 Université catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @author : Christophe Gesché <moosh@claroline.net>
 *
 * @package MAINTENANCE
 */

require '../../inc/claro_init_global.inc.php';

// Security check
if ( ! $_uid ) claro_disp_auth_form();
if ( ! $is_platformAdmin ) claro_die(get_lang('Not allowed'));

$claroCreditFilePath = get_conf('rootSys').'CREDITS.txt';

if(file_exists($rootSys.'platform/currentVersion.inc.php')) include ($rootSys.'platform/currentVersion.inc.php');
if ( ! $is_platformAdmin ) claro_disp_auth_form();



if (! isset($clarolineVersion) )  $clarolineVersion= 'X';


$nameTools = get_lang('PHP system information');
$interbredcrump[]= array ('url' => '..', 'name' => get_lang('Admin'));
$interbredcrump[]= array ('url' => 'index.php', 'name' => get_lang('Technical Tools'));
if (isset($_REQUEST['to']))
{
    $interbredcrump[]= array ('url' => basename($_SERVER['PHP_SELF']), 'name' => get_lang('PHP system information'));
    $nameTools = $_REQUEST['to'];
}

$is_allowedToAdmin = $is_platformAdmin;
if ($is_allowedToAdmin)
{
    include($includePath . '/claro_init_header.inc.php');
    echo claro_html_tool_title( array( 'mainTitle'=>$nameTools
                                , 'subTitle'=> $siteName . ' - ' . $clarolineVersion . ' - '
                                )
                         );

?>
<img src="http://www.claroline.net/image/logo.gif"  alt="claroline" border="0" align="right">
<?php
    if (isset($_REQUEST)) while( (list($name, $value) = each($_REQUEST)))  $$name = $value;
    if (!isset($cmd)) $cmd = '';
    if (!isset($ext)) $ext = '';
    if (!isset($ext)) $do = '';
    if (!isset($ext)) $directory = '';


    function localtest()
    {
        global $local_test;
        $local_addr = $_SERVER['REMOTE_ADDR'];
        if ($local_addr == "127.0.0.1")
        {
            $local_test = true;
        }
        else
        {
            $local_test = false;
        }
    }
?>

<br />
<DIV class="elementServeur">
<span class="elementServeur" >PHP</span>  <?php echo phpversion()?> :
[<a href="<?php echo $_SERVER['PHP_SELF'] ?>?cmd=phpinfo">PHP info</a>]&nbsp;
[<a href="<?php echo $_SERVER['PHP_SELF'] ?>?cmd=phpcredit">PHP credit</a>]&nbsp;
[<a href="<?php echo $_SERVER['PHP_SELF'] ?>?cmd=ext">Extentions</a>]
</DIV>
<DIV class="elementServeur">
<span class="elementServeur" >Claroline</span> <?php echo $clarolineVersion ;?></strong> : [<a href="<?php echo $_SERVER['PHP_SELF'] ?>?cmd=clarconf">Config Claroline</a>]&nbsp;
[<a href="<?php echo $_SERVER['PHP_SELF'] ?>?cmd=clarcredit">Claroline credit</a>]&nbsp;
</DIV>
<DIV class="elementServeur">
<span class="elementServeur" >WebServer</span> <?php echo $_SERVER['SERVER_SOFTWARE'] ;?></strong><br />

[<?php echo get_lang('Mail to') . ' : ' ; ?><a href="mailto:<?php echo $_SERVER['SERVER_ADMIN'] ?>">Admin apache (<?php echo $_SERVER['SERVER_ADMIN'] ?>)</A>]
<br />
</DIV>
<HR size="1" noshade="noshade">
<?php

    if ($cmd == 'ext')
    {
        $extensions = @get_loaded_extensions();
        echo count($extensions) . ' extensions <hr /><br />';
        @sort($extensions);
        foreach($extensions as $extension)
        {
            echo $extension.' &nbsp; <a href="'.$_SERVER['PHP_SELF'].'?cmd=ext&amp;ext='.$extension.'" >'.get_lang('Function list').'</a><br />'."\n";
            if ($extension==$ext)
            {
                $functions = @get_extension_funcs($ext);
                @sort($functions);
                if (is_array($functions))
                {
                    echo '<OL>';
                    foreach($functions as $function)
                    {
                        print '<LI>' . $function . '</li>';
                    }
                    echo '</OL>';
                }
                else
                {
                    echo '!! ' . get_lang('No function in this extension') . '!!<br />';
                }
            }
        }
    }
    elseif ($cmd == 'phpinfo')
    {
        phpinfo();
    }
    elseif ($cmd == 'phpcredit')
    {
        phpcredits(CREDITS_ALL);
    }

    elseif ($cmd == 'clarconf')
    {
        echo '<div style="background-color: #dfdfff;"><hr />config file<hr />';
        highlight_file(claro_get_conf_repository() . 'claro_main.conf.php');
        echo '<hr /></div>';

    }
    elseif ($cmd == 'clarcredit' )
    {
    ?>

    <a href="http://www.claroline.net/credits.htm">See online Credits</a>

<PRE>
<?php
        echo "\n";
        if (file_exists($claroCreditFilePath)) include ($claroCreditFilePath);
    }
    else
    {
        $hideBar = true;
    }


}
else
{
    echo get_lang('No way');
}

?>
<HR size="1" noshade="noshade">
[<a href="http://freshmeat.net/projects/claroline/?topic_id=92%2C72%2C20%2C71"  hreflang="en">FreshMeat</a>]
[<a href="http://freshmeat.net/rate/20465/"  hreflang="en" >Rate it</a>]<br />
[<a href="https://sourceforge.net/projects/claroline/" hreflang="en">SourceForge</a>]<br />
<?php
include $includePath . '/claro_init_footer.inc.php';
?>
