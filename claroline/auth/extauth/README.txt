                    CLAROLINE EXTERNAL AUTHETICATION SYSTEM

The Claroline external authentication system is based on the PEAR Auth library.

This system allows Claroline to rely on external system concerning 
authentication and user profile managemement. It is based on a collection of 
authentication drivers stored inside the claroline/auth/extauth/drivers 
directory.

These drivers can be loaded by the claroline kernel when a user attempt to log 
on the platform.

To use one of these drivers

1. Open the concerned driver into a text editor and adapt the parameters to your 
own context.

2. Uncomment the concerned line in the main claroline configuration file 
(claroline/inc/conf/claro_main.conf.php)


                                 HOW IT WORKS ?

These drivers can be called by the claroline authentication system in two 
circumstances.

1. When a user has never logged to the platform beforehand, ant try to log in to 
Claroline for the first time. No record concerning this user are found into the 
Claroline system, so it attempts to look for this user on the external 
authentication systems specified by its configuration file. When it founds it, 
Claroline duplicates the user profile into its own user table, stating that it 
comes for this specific externel authentication system.

	The driver treating this case is called by the Claroline Kernel by line like 
	this below into the claroline configuration file.

	$extAuthSource['authSourceName']['newUser'] = "path/file";

2. When a user log to the platform next time. A record concerning this user is 
already stored into the Claroline system. From this record Claroline is able to 
know from where does this user profiles comes. And it try to connect to the 
concerned external authentication system to check if this user account is still 
allowed to connect with this password. It also take the occason to update from 
the external authentication system the user data stored into the claroline 
system.

	The driver treating this case is called by the Claroline Kernel by line like 
	this below into the claroline configuration file.

	$extAuthSource['authSourceName']['login'  ] = = "path/file";


                                DRIVER SETTINGS

Each Claroline driver are set 5 parameters.

- $authSourceName : set the identity of the external authentication source 

    exemple : $authSourceName = 'phpnuke';

- $authSourceType : set the technical type of the of the external authentication source

    exemple : $authSourceName = 'DB';

- $extAuthOptionList : set the parameter needed to connect to the external 
  authentication source and the field to to retrieve in it.

    exemple : $extAuthOptionList = array(

                    'url'      => 'ldap://server_address',
                    'port'     => '636',
                    'basedn'   => 'ou=personne,o=your organisation unit,c=domaine',
                    'userattr' => 'uid',
                    'useroc'   => 'person',
                    'attributes' => array('sn', 'givenName', 'telephoneNumber','mail'),

                );

- $extAuthAttribNameList : set how the data retrieved form the external 
  authentication source match the claroline data structure. The keys are the 
  claroline attributes and the value are the authentication external attributes.    

    exemple : $extAuthAttribNameList = array (

                    'lastname'     => 'sn',
                    'firstname'    => 'givenName',
                    'email'        => 'mail',
                    'phoneNumber'  => 'telephoneNumber',
                    'authSource'   => 'ldap'

                );

$extAuthAttribTreatmentList : set any optionnal preliminary treatment to the 
data retrieved from the exernal authentication source before commiting it into 
Claroline. The keys are the concerend claroline attribute, ans the values are 
the name of the function which make the treatment. You can use standart PHP 
function or functions defined by your own.

        $extAuthAttribTreatmentList = array (

                'lastname'     => 'utf8_decode',
                'firstname'    => 'utf8_decode',
                'loginName'    => 'utf8_decode',
                'email'        => 'utf8_decode',
                'officialCode' => 'utf8_decode',
                'phoneNumber'  => 'utf8_decode',
                'status'       => 'treat_status_from_extauth_to_claroline'

        );
