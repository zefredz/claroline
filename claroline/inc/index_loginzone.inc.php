<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );

if ( get_conf('claro_CasEnabled') ) // CAS is a special case of external authentication
{
    echo '<!-- CAS login hyperlink -->' . "\n"
    .    '<div align="center">' . "\n"
    .    '<a href="' . $clarolineRepositoryWeb . 'auth/login.php?authModeReq=CAS">' . "\n"
    .    get_conf('claro_CasLoginString')  . "\n"
    .    '</a>' . "\n"
    .    '</div>' . "\n"
    ;
}

if( get_conf('claro_displayLocalAuthForm') )
{
    echo '<!-- Authentication Form -->' . "\n"
    .    '<form class="claroLoginForm" action ="' . $clarolineRepositoryWeb . 'auth/login.php' . '" method="post">' . "\n"
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