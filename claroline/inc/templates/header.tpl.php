<!DOCTYPE html>

<!-- $Id$ -->

<html>
<head>
<title><?php echo $this->pageTitle; ?></title>
<meta http-equiv="Content-Script-Type" content="text/javascript" />
<meta http-equiv="Content-Style-Type" content="text/css" />
<meta http-equiv="Content-Type" content="text/HTML; charset=<?php echo get_locale('charset');?>"  />
<meta name="generator" content="Claroline <?php echo $this->version; ?> - http://claroline.net" />
<?php echo link_to_css( get_conf('claro_stylesheet') . '/main.css', 'screen, projection, tv' );?>
<?php
if ( get_locale('text_dir') == 'rtl' ):
    echo link_to_css( get_conf('claro_stylesheet') . '/rtl.css', 'screen, projection, tv' );
endif;
?>
<?php echo link_to_css( 'print.css', 'print' );?>
<link rel="top" href="<?php get_path('url'); ?>/index.php" title="" />
<link href="http://www.claroline.net/documentation.htm" rel="Help" />
<link href="http://www.claroline.net/credits.htm" rel="Author" />
<link href="http://www.claroline.net" rel="Copyright" />
<?php if (file_exists(get_path('rootSys').'favicon.ico')): ?>
<link href="<?php echo rtrim( get_path('clarolineRepositoryWeb'), '/' ).'/../favicon.ico'; ?>" rel="shortcut icon" />
<?php endif; ?>
<script type="text/javascript">
    document.cookie="javascriptEnabled=true; path=<?php echo get_path('url');?>";
    <?php echo $this->warnSessionLost;?>
</script>
<?php echo $this->htmlScriptDefinedHeaders;?>
</head>