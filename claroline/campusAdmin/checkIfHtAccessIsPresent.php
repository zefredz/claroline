<?
/*--protectAdminIndex--*/
if (	("apache" ==  strtolower(substr($_SERVER['SERVER_SOFTWARE'],0,6))) 
		&& ($PHP_AUTH_USER=="" ) 
	    && ($REMOTE_ADDR != $SERVER_ADDR)
	)  
{  
	session_unregister("is_admin");
	echo "This  directory  must be protected with an .htaccess file to  works";
	echo "
			<BR>
			if you wan't  unsecure/unprotect the admin  remove<BR>

			<B>".__FILE__." </B> on server
			";
	die ("");
}
/*--protectAdminIndex--*/

?>