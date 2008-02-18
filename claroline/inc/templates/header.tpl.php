<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title><?php echo $this->pageTitle; ?></title>
<meta http-equiv="Content-Script-Type" content="text/javascript" />
<meta http-equiv="Content-Style-Type" content="text/css" />
<meta http-equiv="Content-Type" content="text/HTML; charset=<?php echo get_locale('charset');?>"  />
<link rel="stylesheet" type="text/css" 
    href="<?php echo get_path('clarolineRepositoryWeb');?>css/<?php echo get_conf('claro_stylesheet');?>" 
    media="screen, projection, tv" />
<link rel="stylesheet" type="text/css" 
    href="<?php echo get_path('clarolineRepositoryWeb');?>css/print.css" 
    media="print" />
<link rel="top" href="<?php get_path('url'); ?>/index.php" title="" />
<link href="http://www.claroline.net/documentation.htm" rel="Help" />
<link href="http://www.claroline.net/credits.htm" rel="Author" />
<link href="http://www.claroline.net" rel="Copyright" />
<script type="text/javascript">
    document.cookie="javascriptEnabled=true; path=<?php echo get_path('url');?>";
    <?php echo $this->warnSessionLost;?>
</script>
<?php echo $this->htmlScriptDefinedHeaders;?>
</head>