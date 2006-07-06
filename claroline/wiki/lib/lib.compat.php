<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );
    // vim: expandtab sw=4 ts=4 sts=4:
    if ( ! function_exists( 'file_put_contents' ) )
    {
        if ( !defined( 'FILE_APPEND' ) )
        {
            define( 'FILE_APPEND', 8 );
        }

        function file_put_contents( $file, $content, $flags = null )
        {
            if ( is_array( $content ) )
            {
                $content = implode( '', $content );
            }

            if ( !is_scalar( $content ) )
            {
                trigger_error( 'file_put_contents() The 2nd parameter should be'
                    . ' either a string or an array',
                    E_USER_WARNING );
                return false;
            }

            if ( FILE_APPEND === $flags )
            {
                $fd = fopen( $file, 'a' );
            }
            else
            {
                $fd = fopen( $file, 'wb' );
            }

            if ( false === $fd )
            {
                return false;
            }
            else
            {
                $nb_bytes = fwrite( $fd, $content );
                fclose( $fd );
                return $nb_bytes;
            }
        }
    }
?>