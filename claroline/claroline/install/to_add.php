juste après le $db = mysql_connect("$dbHostForm", "$dbUsernameForm", "$dbPassForm");

ajouter

if (mysql_errno()>0) // problem with server
{
	$no = mysql_errno();     $msg = mysql_error(); 
	echo "<HR>[".$no."] - ".$msg."<HR>
    The Server Mysql  doesn't work or login pass is false.<br>
    please  chech this values 
    host : ".$dbHostForm."<br>
	user : ".$dbUsernameForm."<br>
	password  : ".$dbPassForm."<br>
	and back to step 2
    ";
    exit();
}

