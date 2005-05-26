<?php // $Id$

    // vim: expandtab sw=4 ts=4 sts=4

    /**
     * Custom Error Handler
     * @access protected
     */
    function server_error_handler( $errno, $errmsg, $errfile, $errline, $context )
    {            
        $str = "ERROR $errno : $errmsg in file $errfile on line $errline\n";
        $str .= "STACK TRACE : \n";
        $str .= var_export( $context, true );

        $error_log = fopen( "/home/renaud/error_server", 'a' );
        $date = date( "Y-m-d H:i:s" );
        $str = ">>>>>" . $date . "\n" . $str . "\n<<<<<<\n";

        fwrite( $fd, $str );
        fclose( $fd );
    }

    // set_error_handler( 'server_error_handler' );
?>