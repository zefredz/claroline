<?php // $Id$
/**
 * CLAROLINE 
 *
 * This script present state of 
 * - configuration of Claroline, PHP, Mysql, Webserver
 * - credits 
 *
 * @version 1.6
 * 
 * @copyright (c) 2001-2005 Université catholique de Louvain (UCL)
 * 
 * @license http://www.gnu.org/copyleft/gpl.html GENERAL PUBLIC LICENSE (GPL)
 *  
 * @author : Christophe Gesché <moosh@claroline.net>
 *
 * @package MAINTENANCE
 */

require '../../inc/claro_init_global.inc.php';
if(file_exists($includePath.'/currentVersion.inc.php')) include ($includePath.'/currentVersion.inc.php');
if ( ! $is_platformAdmin ) claro_disp_auth_form();

$langNoFunctionInThisSection = 'No functions in this extention';
$langFunctions = 'Functions list';

$nameTools = $lang_php_info;
$interbredcrump[]= array ("url"=>"..", "name"=> $langAdmin);
$interbredcrump[]= array ("url"=>"index.php", "name"=> $langTechnical);
if ($_REQUEST['to'])
{
	$interbredcrump[]= array ("url"=>basename($_SERVER['PHP_SELF']), "name"=> $lang_php_info);
	$nameTools = $HTTP_GET_VARS["to"];
}
@include($rootAdminSys."/checkIfHtAccessIsPresent.php");
$is_allowedToAdmin 	= $is_platformAdmin;
if ($is_allowedToAdmin)
{
	include($includePath."/claro_init_header.inc.php");
	claro_disp_tool_title(
	array(
	'mainTitle'=>$nameTools,
	'subTitle'=> $PHP_AUTH_USER." - ".$siteName." - ".$clarolineVersion." - ".$dateNow
	)
	);
	claro_disp_msg_arr($controlMsg);
?>
<img src="http://www.claroline.net/image/logo.gif"  alt="claroline" border="0" align="right">
<?php
	if (isset($_REQUEST))
	{
		while(list($name, $value) = each($_REQUEST))
		{
			$$name = $value;
		}
	}
	if (!isset($to)) $to = '';
	if (!isset($ext)) $ext = '';
	if (!isset($ext)) $do = '';
	if (!isset($ext)) $directory = '';


	function localtest()
	{
		global $local_test,$REMOTE_ADDR;
		$local_addr = $REMOTE_ADDR;
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

<br>
<DIV class="elementServeur">
<span class="elementServeur" >PHP</span>  <?php echo phpversion()?> :
[<a href="<?php echo $_SERVER['PHP_SELF'] ?>?to=phpinfo">PHP info</a>]&nbsp;
[<a href="<?php echo $_SERVER['PHP_SELF'] ?>?to=phpcredit">PHP credit</a>]&nbsp;
[<a href="<?php echo $_SERVER['PHP_SELF'] ?>?to=ext">Extentions</a>]
</DIV>
<DIV class="elementServeur">
<span class="elementServeur" >Claroline</span> <?php echo $clarolineVersion ;?></strong> : [<a href="<?php echo $_SERVER['PHP_SELF'] ?>?to=clarconf">Config Claroline</a>]&nbsp;
<!--[<a href="<?php echo $_SERVER['PHP_SELF'] ?>?to=clarcredit">Claroline credit</a>]&nbsp;-->
</DIV>
<DIV class="elementServeur">
<span class="elementServeur" >WebServer</span> <?php echo $SERVER_SOFTWARE ;?></strong><br>

[<?php echo $langMailTo ?><a href="mailto:<?php echo $SERVER_ADMIN ?>">Admin apache (<?php echo $SERVER_ADMIN ?>)</A>]
<!--[<a href="<?php echo $_SERVER['PHP_SELF'] ?>?to=mdp">Parametres</a>]&nbsp;--><BR>
 </DIV>
<HR size="1" noshade="noshade">

<?php

	if ($to=="ext")
	{
		$extensions = @get_loaded_extensions();
		echo count($extensions)." extensions
	<hr><br>
	";
		@sort($extensions);
		foreach($extensions as $extension)
		{
			echo $extension.' &nbsp; <a href="'.$_SERVER['PHP_SELF'].'?to=ext&amp;ext='.$extension.'" >'.$langFunctions.'</a><br>'."\n";
			if ($extension==$ext)
			{
				$functions = @get_extension_funcs($ext);
				@sort($functions);
				if (is_array($functions))
				{
    				echo '<OL>';
    				foreach($functions as $function)
    				{
    					print '<LI>'.$function.'</li>';
    				}
    				echo '</OL>';
				}
				else 
				{
				    echo '!! '.$langNoFunctionInThisSection.'!!<BR>';
				}
			}
		}
	}
	elseif ($to=="phpinfo")
	{
		phpinfo();
	}
	elseif ($to=="phpcredit")
	{
		phpcredits(CREDITS_ALL);
	}

	elseif ($to=="clarconf")
	{
	echo '<div style="background-color: #dfdfff;"><HR>config file<HR>';
	highlight_file($includePath."/conf/claro_main.conf.php");
	echo '<HR></div>';

	}
	elseif ($to=="clarcredit")
	{
	?>
<h3>Credits</h3>Claroline has been developed by an international team of
teachers and developers scattered around the world. It recycles entire programs
or pieces of code found in the vast programmes and scripts library of the
GPL Open Source internet mediated community. Thomas De Praetere initiated the process of gathering
this code together and was quickly followed by Hugues Peeters (who coined the name "claroline") and Christophe
Geschï¿½. Next came Andrew Lynn, Emmanuel Pecquet, Emmanuel Mathot,
Akira Yoshii, Dennis Daniels, Furio Petrossi, Francis Dubois, Maria Jose
Rodriges Malmierca and many others.<br>


<ul>

        <li>
          <a href="http://www.ucl.ac.be">Universitï¿½ catholique de Louvain</a> encouraged us at  <a href="http://www.ipm.ucl.ac.be/">Institut de Pï¿½dagogie universitaire et des Multimï¿½dias</a> (Institute for Education and Multimedia) to develop and distribute this software,</li>


<li>
          <a href="http://www.fondation-louvain.ucl.ac.be">Fondation Louvain</a> helped financialy,</li>




<li>Elie Milgrom helped analyse the needs for a Quiz tool,</li><li>Marc Lobelle helped analyse the needs for a tool linking claroline content with external content,</li>
        <li>Pascale Wouters helped analyse the needs for the Course Description tool,</li>
        <li>Fanny Meunier defined the priorities for a chat tool,</li>
        <li>
Marcel Lebrun provided much help on educational aspects,</li>
        <li>Keith Carlon helped analyse the needs for the Assignments (Students Upload) tool,</li>

        <li>Philippe Mercenier and Philippe Dekimpe helped analyse the needs for a User management tool,<br>
        </li>



<li>Translations :

          <ul>

<li>Arabic : Yassine Jelmam (<a href="mailto:yjelmam@myrealbox.com">yjelmam@myrealbox.com</a>),<br>
</li><li>Chinese : Maizeman (<a href="mailto:Maizeman@21cn.com">Maizeman@21cn.com</a>),</li>


<li>Finnish : Asmo Koskinen (<a href="mailto:asmo.koskinen@asmokoskinen.net">asmo.koskinen@asmokoskinen.net</a>),</li>

<li>German : Stiehl&nbsp;Nikolai&nbsp; (<a href="mailto:nikolai.stiehl@web.de">nikolai.stiehl@web.de</a>),</li>

<li>Italian : Maurizio Guercio&nbsp; (<a href="mailto:mguercio@libero.it">mguercio@libero.it</a>),
Furio  Petrossi&nbsp; (<a href="mailto:Furio.Petrossi@scuolefvg.org">Furio.Petrossi@scuolefvg.org</a>),</li>

<li>Japanese : Akira Yoshii (<a href="mailto:yoshii@cc.hokkyodai.ac.jp">yoshii@cc.hokkyodai.ac.jp</a>),</li>

<li>Polish : Slawomir Gurdala (<a href="mailto:guslaw@uni.lodz.pl">guslaw@uni.lodz.pl</a>),</li>

<li>Portugese : Marcello R. Minholi (<a href="mailto:minholi@unipar.be">minholi@unipar.be</a>),</li>

<li>Swedish : Jan Olsson (<a href="mailto:jano@artedi.nordmaling.se">jano@artedi.nordmaling.se</a>),

              </li><li>Spanish : Xavier Casassas ( <a href="mailto:xcc@mail.ics.co.at">xcc@mail.ics.co.at</a>),
Jorge Gonzales (&nbsp;<a href="mailto:jgonzalez@athenasoft.com.mx">jgonzalez@athenasoft.com.mx</a>),
Javier&nbsp;Picado Ladrï¿½n de Guevara (<a href="mailto:jpicado@eurosur.com">jpicado@eurosur.com</a>),
Oda Begares (<a href="mailto:begaeres@arch.ucl.ac.be">begaeres@arch.ucl.ac.be</a>),</li>

<li>Thaï¿½ : Sutas Jitchuen (<a href="mailto:dtsjc@mucc.mahidol.ac.th">dtsjc@mucc.mahidol.ac.th</a>).</li></ul><br>
</li><li>Main Database structure and authentication process are inspired from <a href="http://www.phpnuke.org/">PhpNuke</a>,</li>
        <li>Course Home Page Layout is inspired from Yahoo,</li>
        <li>Forum tool is adapted from <a href="http://www.phpbb.com/">phpBB,</a></li>
        <li>Stats tool is adapted from <a href="http://www.ezboo.com/">ezBOO,</a></li>

        <li>Unzipping option of document tool is based on PclZip library from  <a href="http://www.phpconcept.net">PHP Concept,</a></li>
        <li>Icons of Documents tool have been borrowed to <a href="http://www.webjeff.org/langages/php_scripts.htm">WebJeff File Manager</a>,</li>
        <li>FileSize function of Documents tool is inspired from <a href="http://www.webjeff.org/langages/php_scripts.htm">WebJeff File Manager</a><br>
        </li>
</ul>
    <ul><li>Special thanks to :&nbsp;</li>

        <ul>
          <li>Emmanuel Pecquet for his deep contribution during the debugging phase
(<a href="mailto:emmanuel.pecquet@wanadoo.fr">emmanuel.pecquet@wanadoo.fr</a>).</li>
          <li>Andrew Lynn for writing Claroline Manual
(<a href="mailto:alynn@strathclyde.ac.be">alynn@strathclyde.ac.be</a>).<br>
            <br>

            </li>

        </ul>

            <li>
Thanks also to Denis Daniel, Jean-Pierre Mitsch,
Paul Muraille, Bret Watson, Jan Olsson, Carlos Seabra, Damien Sï¿½guy,
Giuseppe Filice.</li>
<hr size="1" noshade="">

<address><font face="Helvetica, Arial, sans-serif">
Thomas  De Praetere <a href="mailto:depraetere@ipm.ucl.ac.be">depraetere@ipm.ucl.ac.be</a>
 - Hugues Peeters <a href="mailto:peeters@ipm.ucl.ac.be">peeters@ipm.ucl.ac.be</a>
 - Christophe Gesch&eacute; <a href="mailto:gesche@ipm.ucl.ac.be">gesche@ipm.ucl.ac.be</a></font>
</address>

<?php

	}
	else
	{
		$hideBar =true;
	}


}
else
{
	echo $lang_no_access_here;
}

?>
<HR size="1" noshade="noshade">
[<a href="http://freshmeat.net/projects/claroline/?topic_id=92%2C72%2C20%2C71"  hreflang="en">FreshMeat</a>]
[<a href="http://freshmeat.net/rate/20465/"  hreflang="en" >Rate it</a>]<br>
[<a href="https://sourceforge.net/projects/claroline/" hreflang="en">SourceForge</a>]<br>
<?php
include($includePath."/claro_init_footer.inc.php");
?>
