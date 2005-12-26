<?php
require '../inc/claro_init_global.inc.php';
$nameTools = get_lang('HHome');
$hide_banner = TRUE;
include($includePath."/claro_init_header.inc.php");
?>
<table width="100%" border="0" cellpadding="1" cellspacing="1">
<tr>
  <td align="left" valign="top">

    <?php echo "<h4>get_lang('HHome')</h4>"; ?>

  </td>
  <td align="right" valign="top">
    <a href="javascript:window.close();"><?php echo get_lang('CloseWindow'); ?></a>
  </td>
</tr>
<tr>
  <td colspan="2">
    <?php echo get_lang('ConfigMenuContent'); ?>
    <h3>Edit main(old system)</h3>
    <p>
        Lien vers l'ancienne page de configuration (Claroline 1.5.0) pour les  valeurs principales de la configuration.
    </p>
    <h3>Search Properties in old config system</h3>
    <p>
        Tente de retrouver les valeurs enregistrées dans les fichiers de configurations actuels.
    </p>
    <h3>Tableau des outils</h3>
    <table cellspacing="0" cellpadding="2" border="1">
        <tr>
            <td><small>nom de l'outil</small></td>
            <td><small>éditer</small></td>
            <td><small>appliquer</small></td>
            <td><small>Show Def</small></td>
            <td><small>Show Conf</small></td>
        </tr>
    </table>
    <ul>
        <li><strong>Nom de l'outil</strong> : Nom de l'outil, ou son code s'il n'a pas de nom</li>
        <li><strong>éditer</strong> : permet d'éditer les paramètres</li>
        <li><strong>appliquer</strong> : créer le fichier de configuration</li>
        <li><strong>Show Def</strong> : afficher le fichier de définition qui détermine les paramètres et le formulaire pour les éditer</li>
        <li><strong>Show Conf</strong> : affichier le fichier de configuration généré par cet outil</li>
    </ul>

    <ul>
        <li><strong><strike>Show Def</strike></strong> : il n'y a pas de fichier de définition pour cet outil</li>
        <li><strong><strike>Show Conf</strike></strong> : le fichier n'a jamais été généré</li>
    </ul>

  </td>
</tr>
<tr>
  <td colspan="2">
    <br>
    <center><a href="javascript:window.close();"><?php echo get_lang('CloseWindow'); ?></a></center>
  </td>
</tr>
</table>
<?php

$hide_footer = true;
include($includePath."/claro_init_footer.inc.php");

?>
