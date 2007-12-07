<div style="border-top-color: Black; border-top-style: groove; border-top-width: thin; border-bottom-color: Black; border-bottom-style: ridge; border-bottom-width: thin; background-color: #C0C0C0; padding-left: 4px; padding-right: 7px; padding-top: 1px; padding-bottom: 2px;">
<?php 
	

echo $langAdminBy." ".$PHP_AUTH_USER. " - [".date("\H\I\S \:B   d/m/Y  \a H:i:s");?>
 ] 
<?php if (isset($clarolineRepositoryWeb)) 
{ ?> -  
[<a href="<?php echo $clarolineRepositoryWeb ?>admin/">Admin</a>]
<?php 
}
if (isset($up_link)) 
{ ?> -  
[<a href="<?php echo $up_link ?>">UP</a>]
<?} ?>
<!-- - [<a href="/">Site</a>]-->
<!-- - [<a href="?igger=<?php echo date("Bs")?>">Refresh</a>] -->
-  <?php 
if (isset($idAdmin))
	echo $idAdmin 
?>
<!-- - [<a href="#explic">Aide</A>]--></div><br>
