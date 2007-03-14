<?php // $Id$

    // vim: expandtab sw=4 ts=4 sts=4:
    
    if ( count( get_included_files() ) == 1 )
    {
        die( 'The file ' . basename(__FILE__) . ' cannot be accessed directly, use include instead' );
    }

    /**
     * @author  Frederic Minne <zefredz@claroline.net>
     * @copyright Copyright &copy; 2006-2007, Frederic Minne
     * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
     * @version 1.0
     * @package PlugIt
     */

    /**
     * Popup helper
     *
     * @access public
     */
    class PopupHelper
    {
        /**
         * generate window.close() html code
         *
         * @access public
         * @static
         * @return  string html code of the window.close() link
         */
        function windowClose()
        {
            return '<p style="text-align:center;"><a href="#" '
                . 'onclick="window.close()">'
                . get_lang('Close window')
                . '</a></p>'
                . "\n"
                ;
        }

        /**
         * Embed content between window.close() code
         *
         * @access  static
         * @static
         * @param   string content
         * @return  string embedded content
         */
        function popupEmbed( $content )
        {
            $out = PopupHelper::windowClose()
                . $content
                . PopupHelper::windowClose()
                ;

            return $out;
        }
    }

    /**
     * Embed script output into Claroline layout
     * @param   string  $output output to embed
     * @param   bool    $hide_banner hide Claroline banner (opt)
     * @param   bool    $hide_footer hide Claroline banner (opt)
     * @param   bool    $hide_body hide Claroline banner (opt)
     * @return  void
     */
    function claro_embed( $output
        , $inPopup = false
        , $hide_banner = false
        , $hide_footer = false
        , $hide_body = false )
    {
        // global variables needed by header and footer...
        // FIXME make global objects with all these craps !!!
        global $includePath, $clarolineRepositoryWeb, $claro_stylesheet, $urlAppend ,
               $siteName, $text_dir, $_uid, $_cid, $administrator_name, $administrator_email;
        global $is_platformAdmin, $_course, $_user, $_courseToolList, $coursesRepositoryWeb,
               $is_courseAllowed, $imgRepositoryWeb, $_tid, $is_courseMember, $_gid;
        global $claroBodyOnload, $httpHeadXtra, $htmlHeadXtra, $charset, $interbredcrump,
               $noPHP_SELF, $noQUERY_STRING;
        global $institution_name, $institution_url;

        if ( true == $inPopup )
        {
            $output = PopupHelper::popupEmbed( $output );
            $hide_banner = true;
            $hide_footer = true;
        }

        // embed script output here
        require $includePath . '/claro_init_header.inc.php';
        echo $output;
        require $includePath . '/claro_init_footer.inc.php' ;
    }

    /**
     * Claroline script embed class
     */
    class ClarolineScriptEmbed
    {
        var $inPopup = false;
        var $inFrame = false;
        var $hide_footer = false;
        var $hide_banner = false;
        var $hide_body = false;
        var $content = '';

        // claroline diplay options

        function hideBanner()
        {
            $this->hide_banner = true;
        }
        function hideFooter()
        {
            $this->hide_footer = true;
        }
        function hideBody()
        {
            $this->hide_body = true;
        }

        // display mode

        function popupMode()
        {
            $this->hideBanner();
            $this->hideFooter();
            $this->inPopup = true;
        }
        function frameMode()
        {
            $this->hideBanner();
            $this->hideFooter();
            $this->inFrame = true;
        }
        function embedInPage()
        {
            $this->hideBanner();
            $this->hideFooter();
            $this->hideBody();
        }

        function setContent( $content )
        {
            $this->content = $content;
        }

        // claroline header methods

        function addHtmlHeader( $header )
        {
            $GLOBALS['htmlHeadXtra'][] = $header;
        }
        function addHttpHeader( $header )
        {
            $GLOBALS['httpHeadXtra'][] = $header;
        }
        function addBodyOnloadFunction( $function )
        {
            $GLOBALS['claroBodyOnload'][] = $function;
        }

        // output methods

        function output()
        {
            if ( $this->inPopup )
            {
                $this->content = PopupHelper::popupEmbed( $this->content );
            }

            $this->embed( $this->content
                , $this->hide_banner
                , $this->hide_footer
                , $this->hide_body );
        }

        function embed( $output
            , $hide_banner = false
            , $hide_footer = false
            , $hide_body = false )
        {
            // global variables needed by header and footer...
            // FIXME make global objects with all these craps !!!
            global $includePath, $clarolineRepositoryWeb, $claro_stylesheet, $urlAppend ,
               $siteName, $text_dir, $_uid, $_cid, $administrator_name, $administrator_email;
            global $is_platformAdmin, $_course, $_user, $_courseToolList, $coursesRepositoryWeb,
                   $is_courseAllowed, $imgRepositoryWeb, $_tid, $is_courseMember, $_gid;
            global $claroBodyOnload, $httpHeadXtra, $htmlHeadXtra, $charset, $interbredcrump,
                   $noPHP_SELF, $noQUERY_STRING;
            global $institution_name, $institution_url;

            // embed script output here
            require $includePath . '/claro_init_header.inc.php';
            echo $this->content;
            require $includePath . '/claro_init_footer.inc.php' ;
        }
    }
?>