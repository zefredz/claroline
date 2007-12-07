<?php // $Id$


/**
 * Claroline Shibboleth / Switch AAI
 *
 * Customization: Show Shibboleth login link 
 *
 * @version 0.4
 *
 * @author Daniel Streiff <daniel.streiff@fh-htwchur.ch>
 *
 */


if ( count( get_included_files() ) == 1 ) die( '---' );

/* Claroline CAS login */

if (get_conf('claro_CasEnabled',false))
{
    if ( trim(get_conf('claro_CasLoginString','') != '' ))
    {
        $login_text = trim(get_conf('claro_CasLoginString',''));
    }
    else
    {
        $login_text = get_lang('Login');
    }

    echo '<div align="center">'
    .    '<a href="' . get_path('clarolineRepositoryWeb') . 'auth/login.php?authModeReq=CAS">'
    .    $login_text
    .    '</a>'
    .    '</div>';
} 

/* Claroline Shibboleth / Switch AAI login */

if ( get_conf('claro_ShibbolethEnabled') )
{
    if ( trim(get_conf('claro_ShibbolethText','') != '' ) )
    {
        $login_text = trim(get_conf('claro_ShibbolethText',''));
    }
    else
    {
        $login_text = get_lang('Login');
    }

    echo  '<fieldset style="padding: 7px;">' . "\n"
    	. '<legend>' . $login_text . '</legend>' . "\n"
    	. '<div align="center">' . "\n"
        . '<a href="' . get_conf('claro_ShibbolethPath') . 'index.php"><img src="' . get_conf('claro_ShibbolethLogo') . '" border="0" title="' . $login_text . '" alt="' . $login_text . '"></a>' . "\n"
        . '</div>' . "\n"
        . '</fieldset>' . "\n"
        . '<br />' . "\n";
}

if( get_conf('claro_displayLocalAuthForm') )
{
    echo '<!-- Authentication Form -->' . "\n"
    .    '<form class="claroLoginForm" action ="' . get_path('clarolineRepositoryWeb') . 'auth/login.php' . '" method="post">' . "\n"
    .    '<fieldset style="padding: 7px;">' . "\n"
    .    '<legend>' . get_lang('Authentication') . ' : </legend>' . "\n"
    .    '<label for="login">' . "\n"
    .    get_lang('Username') . '<br />' . "\n"
    .    '<input type="text" name="login" id="login" size="12" tabindex="1" /><br />' . "\n"
    .    '</label>' . "\n"
    .    '<label for="password" >' . "\n"
    .    get_lang('Password') . '<br />' . "\n"
    .    '<input type="password" name="password" id="password" size="12" tabindex="2" /><br />' . "\n"
    .    '</label>' . "\n"
    .    '<input type="submit" value="' . get_lang('Enter') . '" name="submitAuth" tabindex="3" />' . "\n"
    .    '</fieldset>' . "\n"
    .    '</form>' . "\n\n"
    .    '<!-- "Lost Password" -->' . "\n"
    .    '<p>' . "\n"
    .    '<a href="claroline/auth/lostPassword.php">' . get_lang('Lost password') . '</a>' . "\n"
    .    '</p>' . "\n"
    ;


if( $allowSelfReg )
{
    echo '<!-- "Create user Account" -->' . "\n"
    .    '<p>' . "\n"
    .    '<a href="claroline/auth/inscription.php">' . get_lang('Create user account') . '</a>' . "\n"
    .    '</p>' . "\n"
    ;
}
} // end else if claro_displayLocalAuthForm
?>
