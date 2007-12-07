<?php # $Id$
//----------------------------------------------------------------------
// CLAROLINE
//----------------------------------------------------------------------
// Copyright (c) 2001-2004 Universite catholique de Louvain (UCL)
//----------------------------------------------------------------------
// This program is under the terms of the GENERAL PUBLIC LICENSE (GPL)
// as published by the FREE SOFTWARE FOUNDATION. The GPL is available
// through the world-wide-web at http://www.gnu.org/copyleft/gpl.html
//----------------------------------------------------------------------
// Authors: see 'credits' file
//----------------------------------------------------------------------

$langAdministrationTools ="Outils d'administration";
$langManage = "Gestion du campus";

$langFile = "admin.add.users";
$cidReset = TRUE;$gidReset = TRUE;$tidReset = TRUE;
require '../inc/claro_init_global.inc.php';

$nameTools             = $lang_addUser_addUser;
$interbredcrump[]    = array ("url" => $rootAdminWeb, "name"=> $langAdministrationTools);
$interbredcrump[]    = array ("url" => "index.php", "name"=> $langManage);
$noQUERY_STRING     = TRUE;

include($includePath."/lib/text.lib.php");
include($includePath."/lib/debug.lib.inc.php");
include($includePath."/lib/userManage.lib.php");
include($includePath."/lib/admin.lib.inc.php");

//SECURITY CHECK

if (!$is_platformAdmin) claro_disp_auth_form();


$dateNow             = claro_format_locale_date($dateTimeFormatLong);
$is_allowedToAdmin     = $is_platformAdmin;

//TABLES

$tbl_user             = $mainDbName."`.`user";

$display_form        =TRUE;
$display_resultCSV    =FALSE;

/*-------------------------------------------------------------------
Data checking
--------------------------------------------------------------------*/
if(isset($_REQUEST["register"]))
{
    /*
    * Fields Checking
    */
    $array_Users[0]['lastname'    ] = trim($_REQUEST["nom_form"]);
    $array_Users[0]['firstname'   ] = trim($_REQUEST["prenom_form"]);
    $array_Users[0]['username'    ] = trim($_REQUEST["username_form"]);
    $array_Users[0]['password'    ] = trim($_REQUEST["password_form"]);
    $array_Users[0]['email'       ] = trim($_REQUEST["email_form"]);
    $array_Users[0]['status'      ] = $_REQUEST["status_form"];
    $array_Users[0]['officialCode'] = NULL;
    $array_Users[0]['phone'       ] = NULL;
    $array_Users[0]['picture'     ] = "";
    $array_Users[0]['authSource'  ] = "claroline";
}




/*-------------------------------------------------------------------
Cut the file CSV
--------------------------------------------------------------------*/
if(isset($_REQUEST["searchUsers"]))
{
    //for $array_error
    $e=0;

    //look if is a CSV file
    if(!strpos($_FILES["importCSV"]["name"],".csv",strlen($_FILES["importCSV"]["name"])-4))
        $controlMsg["error"][]=$lang_addUser_NoFileCSV;
    else
    {
        //move the file
        if (move_uploaded_file($_FILES["importCSV"]["tmp_name"], dirname(__FILE__)."/".$_FILES["importCSV"]["name"]))
        {
            $fp = @fopen (dirname(__FILE__)."/". $_FILES["importCSV"]["name"],"r")
                or die ("Impossible d'ouvrir le fichier".dirname(__FILE__)."/". $_FILES["importCSV"]["name"]);

            //Read each ligne
            $i=0;
            while ($data = @fgetcsv($fp, 1000, ";"))
            {
                //number of registration per ligne
                $num = count($data);

                //For each registration
                for ($c=0; $c<$num; $c++)
                {
                    $data[$c]=str_replace('"','&quot;',$data[$c]);
                    $data[$c]=str_replace("'","\'",$data[$c]);
                    //search information in an array
                    $arrayUser[$i][$c]=$data[$c];
                }
                $i++;
            }

            fclose ($fp);

            //For each user registration, check if the number of registration is 11 else error
            $i=0;
            if(count($arrayUser)>0)
            {
                foreach($arrayUser as $one_user)
                {
                    if(count($one_user)!=11)
                    {
                        foreach($one_user as $info)
                            $user.=$info.",";

                        $array_error[$e]["user"]=$user;
                        $array_error[$e]["error"]=$lang_addUser_NbParamWrong;
                        $e++;
                        unset($user);
                    }
                    else
                    {
                        //Create a new array with information of each correct user
                        $array_Users[$i]["lastname"]    =$one_user[0];
                        $array_Users[$i]["firstname"]    =$one_user[1];
                        $array_Users[$i]["username"]    =$one_user[2];
                        $array_Users[$i]["password"]    =$one_user[3];
                        $array_Users[$i]["authSource"]    =$one_user[4];
                        $array_Users[$i]["email"]        =$one_user[5];
                        $array_Users[$i]["status"]        =$one_user[6];
                        $array_Users[$i]["officialCode"]=$one_user[7];
                        $array_Users[$i]["phone"]        =$one_user[8];
                        $array_Users[$i]["picture"]        =$one_user[9];
                        $array_Users[$i]["creatorId"]    =$one_user[10];
                    }

                    $i++;
                }
            }
            else
                $controlMsg["error"][]=$lang_addUser_FileEmpty;

            //delete the file
            unlink(dirname(__FILE__)."/". $_FILES["importCSV"]["name"]);
        }
    }

}




/*-------------------------------------------------------------------
Check if informations of the users are correct and create each user in the databse
--------------------------------------------------------------------*/
if( (isset($_REQUEST["register"]) || isset($_REQUEST["searchUsers"])) && count($array_Users)>0)
{
    //for the display
    if(isset($_REQUEST["searchUsers"]))
    {
        $display_resultCSV=true;
        $display_form=false;
    }

    //For each user
    foreach($array_Users as $user)
    {
        $firstname       =$user["firstname"];
        $lastname        =$user["lastname"];
        $status          =$user["status"];
        $email           =$user["email"];
        $username        =$user["username"];
        $password        =$user["password"];
        $officialCode    =$user["officialCode"];
        $phone           =$user["phone"];
        $picture         =$user["picture"];
        $authSource      =$user["authSource"];

        //Check if the parametres are correct
        $error=infoOk($firstname,$lastname,$status,$email,$username,$password,$officialCode,$phone,$picture,$authSource);

        // prevent conflict with existing user account
        if(count($error)==0)
        {
            $result=mysql_query("SELECT user_id,
                                (username='$username') AS loginExists,
                                (nom='$lastname' AND prenom='$firstname' AND email='$email') AS userExists
                                FROM `$tbl_user`
                                WHERE username='$username' OR (nom='$lastname' AND prenom='$firstname'
                                    AND email='$email')
                                ORDER BY userExists DESC, loginExists DESC");

            if(mysql_num_rows($result))
            {
                while($user=mysql_fetch_array($result))
                {
                    // check if the user is already registered to the platform

                    if($user['userExists'])
                    {
                        $userExists = TRUE;
                        $userId     = $user['user_id'];
                        $error[0]=$lang_addUser_UserExist;
                        break;
                    }

                    // check if the login name choosen is already taken by another user

                    if($user['loginExists'])
                    {
                        $loginExists = TRUE;
                        $userId      = 0;
                        $error[0]=$lang_addUser_UserNo." (".$username.") ".$lang_addUser_Taken;
                        break;
                    }
                }                // end while $result
            }                    // end if num rows
            else
            {
                //If the user is created
                if (create_new_user($firstname,$lastname,$status,$email,$username,$password,$officialCode
                    ,$phone,$picture,$authSource))
                {
                    //mail notification to new user
                    sendMail($lastname,$firstname,$username,$password,$email);

                    //If one user added manually
                    if(isset($_REQUEST["register"]))
                        $controlMsg["info"][] = "$firstname $lastname $lang_addUser_addPlatform.";
                    else //If add user with a CSV file
                        $array_addUserPlatForm[]=$firstname." ".$lastname;

                    // remove <form> variables to prevent any pre-filled fields
                    unset($lastname, $firstname, $username, $password, $email, $status);
                }
            }
        }
        else //The are an error in the parameters
        {
            //If they are missing parameters
            if(isset($error["empty"]))
                $error[0]=$lang_addUser_Filled;

            //If the are a problem in the email
            elseif(isset($error["email"]))
                $error[0]=$lang_addUser_EmailWrong;

            //If the status is different of 1 or 5
            elseif(isset($error["status"]))
                $error[0]=$lang_addUser_StatusWrong;
        }

        //If add an user manually, display the error in the same page
        if(count($error)>0 && isset($_REQUEST["register"]))
            $controlMsg["error"][]=$error[0];
        elseif(count($error)>0)  //else create a array of the errors
        {
            $array_error[$e]["error"]=$error[0];
            $array_error[$e]["user"]=$firstname.",".$lastname.",".$status.",".$email.",".$username.",".$password.",".
                        $officialCode.",".$phone.",".$picture.",".$authSource;
            $e++;
        }

        unset($error);
    }
}



// END OF WORKS


include($includePath."/claro_init_header.inc.php");

claro_disp_tool_title(
    array(
    'mainTitle'=>$nameTools,
    'subTitle'=> $PHP_AUTH_USER." - ".$siteName." - ".$clarolineVersion." - ".$dateNow
    )
    );
claro_disp_msg_arr($controlMsg);

//OUTPUT


if($display_form)
{

    /*==========================
        IMPORT CSV USERS LIST
    ==========================*/
    if($is_platformAdmin && (! $userPasswordCrypted))
    {
        echo $lang_addUser_addSeveralUser ?>
        <br><br>

        <form enctype="multipart/form-data"  method="POST" action="<?php echo $PHP_SELF ?>"  >
            <input type="file" name="importCSV" >
            <br><br>
            <input type="submit" name="searchUsers" value="<?php echo $lang_buttonShearchUsers; ?>">
        </form>

        <font color="gray">
        <p><?php echo $lang_addUser_formatCSVTitle; ?>:</p>

        <blockquote>
        <code>
        <?php echo $lang_addUser_formatCSV; ?><?php echo $_uid ?>
        </code>
        </blockquote>
        </font>

    <?php
    } // if is_platformAdmin
}





/*---------------------------------------------------------------------------
//Display the users added correctly in the database and the registration wrong for the CSV file
--------------------------------------------------------------------------*/
if($display_resultCSV)
{
    echo "<br>";
    echo "<u><font color=\"gray\">".$lang_addUser_UserOk."</font></u>";
    echo "<br><br>";

    //The user added correctly
    if(count($array_addUserPlatForm)<1)
        echo "&nbsp;&nbsp;<font color=\"red\">".$lang_addUser_NoUserAdd."</font>";
    else
        foreach($array_addUserPlatForm as $user)
            echo "&nbsp;".$user."<br>";


    echo "<br><br><br>";
    echo "<u><font color=\"gray\">".$lang_addUser_UserWrong."</font></u>";
    echo "<br><br>";

    //The registration wrong
    if(count($array_error)>0)
        foreach($array_error as $error)
        {
            echo "&nbsp;".$error["user"]."<br>";
            echo "&nbsp;&nbsp;-->&nbsp;<font color=\"red\">".$error["error"]."</font><br><br>";
        }
?>
    <br><a href="<?php echo $PHP_SELF; ?>"> <?php echo $lang_addUser_ReturnPageAddUser; ?> </a>
<?php
}


include($includePath."/claro_init_footer.inc.php");
?>