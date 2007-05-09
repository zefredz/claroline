<?php // $Id$

    // vim: expandtab sw=4 ts=4 sts=4:
    
    if ( count( get_included_files() ) == 1 )
    {
        die( 'The file ' . basename(__FILE__) . ' cannot be accessed directly, use include instead' );
    }
    
    /**
     *
     *
     * @version     1.9 $Revision$
     * @copyright   2001-2007 Universite catholique de Louvain (UCL)
     * @author      Frederic Minne <zefredz@claroline.net>
     * @license     http://www.gnu.org/copyleft/gpl.html
     *              GNU GENERAL PUBLIC LICENSE
     * @package     EMBED
     */

    /**
     * Popup helper
     *
     * @access public
     */
    class PopupWindowHelper
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
            $out = PopupWindowHelper::windowClose()
                . $content
                . PopupWindowHelper::windowClose()
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
     * @todo    TODO return string instead of echoing it
     */
    function claro_embed( $output
        , $inPopup = false
        , $hide_banner = false
        , $hide_footer = false
        , $hide_body = false
        , $no_body = false )
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
        global $no_body;

        if ( true == $inPopup )
        {
            $output = PopupWindowHelper::popupEmbed( $output );
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
     *
     * @access  public
     */
    class ClarolineScriptEmbed
    {
        var $inPopup = false;
        var $inFrame = false;
        var $inFrameset = false;
        var $hide_footer = false;
        var $hide_banner = false;
        var $hide_body = false;
        var $content = '';

        // claroline diplay options

        /**
         * Hide Claroline banner in display
         *
         * @access  public
         */
        function hideBanner()
        {
            $this->hide_banner = true;
        }
        
        /**
         * Hide Claroline footer in display
         *
         * @access  public
         */
        function hideFooter()
        {
            $this->hide_footer = true;
        }
        
        /**
         * Hide Claroline claroBody class div in display
         *
         * @access  public
         */
        function hideClaroBody()
        {
            $this->hide_body = true;
        }

        // display mode

        /**
         * Set options to display in a popup window
         *
         * @access  public
         */
        function popupMode()
        {
            $this->hideBanner();
            $this->hideFooter();
            $this->inPopup = true;
        }
        
        /**
         * Set options to display in a frame
         *
         * @access  public
         */
        function frameMode()
        {
            $this->hideBanner();
            $this->hideFooter();
            $this->inFrame = true;
        }

        /*function embedInPage()
        {
            $this->hideBanner();
            $this->hideFooter();
            $this->hideBody();
        }*/

        /**
         * Set page content
         *
         * @access  public
         * @param   string content, page content
         */
        function setContent( $content )
        {
            $this->content = $content;
        }

        // claroline header methods

        /**
         * Add extra HTML header elements
         *
         * @access  public
         * @param   string content, page content
         */
        function addHtmlHeader( $header )
        {
            $GLOBALS['htmlHeadXtra'][] = $header;
        }
        
        /**
         * Add extra HTTP header elements
         *
         * @access  public
         * @param   string content, page content
         */
        function addHttpHeader( $header )
        {
            $GLOBALS['httpHeadXtra'][] = $header;
        }
        
        /**
         * Add extra javascript executed when body is loaded
         *
         * @access  public
         * @param   string content, page content
         */
        function addBodyOnloadFunction( $function )
        {
            $GLOBALS['claroBodyOnload'][] = $function;
        }

        // output methods
        /**
         * Generate and set output to client
         *
         * @access  public
         */
        function output()
        {
            if ( $this->inPopup )
            {
                $this->content = PopupWindowHelper::popupEmbed( $this->content );
            }

            $this->embed( $this->content
                , $this->hide_banner
                , $this->hide_footer
                , $this->hide_body );
        }

        /**
         * Embed given contents in Claroline page layout
         *
         * @access  public
         * @static
         * @param   string output, content to display in page
         * @param   bool hide_banner, set to true hide Claroline banner
         * @param   bool hide_footer, set to true hide Claroline footer
         * @param   bool hide_body, set to true remove Claroline claroBody div
         * @todo    TODO return string instead of echoing it
         */
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
    
    /**
     * Claroline html frame element class
     *
     * @access  public
     * @interface
     */
    class ClaroFramesetElement
    {
        /**
         * Render the frameset element to embed in a HTML frameset
         *
         * @access  public
         * @abstract
         * @return   string, frame html code
         */
        function render()
        {
            return null;
        }
    }
    
    /**
     * Claroline html frame class
     *
     * @access  public
     */
    class ClaroFrame extends ClaroFramesetElement
    {
        var $src;
        var $name;
        var $id;
        var $scrolling = false;
        
        /**
         * Constructor
         *
         * @access  public
         * @param   string name, frame name
         * @param   string src, frame content url
         * @param   string id, frame id, optional, if not given the name will be
         *  used as the frame id
         */
        function ClaroFrame( $name, $src, $id = '' )
        {
            $this->name = $name;
            $this->src = $src;
            $this->id = empty( $id ) ? $name : $id;
        }
        
        /**
         * Allow scrolling in frame
         *
         * @access  public
         */
        function allowScrolling()
        {
            $this->scrolling = true;
        }
        
        /**
         * Render the frame to embed in a HTML frameset
         *
         * @access  public
         * @see     ClaroFramesetElement::render()
         */
        function render()
        {
            return '<frame src="'.$this->src.'"'
                . ' name="'.$this->name.'"'
                . ' id="'.$this->id.'"'
                . ' scrolling="'.($this->scrolling ? 'yes' : 'no' ).'" />'
                . "\n"
                ;
        }
    }
    
    /**
     * Claroline html frameset class
     *
     * @access  public
     */
    class ClaroFrameset extends ClaroFramesetElement
    {
        var $frameset = array();
        var $rows = array();
        var $cols = array();
        
        /**
         * Add a frame or frameset object to the current frameset
         *
         * @access  public
         * @param   ClaroFramesetElement claroFrame, frame to add could be a
         *  ClaroFrame or a ClaroFrameset or any other convenient Object
         *  implementing the ClaroFramesetElement API
         */
        function addFrame( $claroFrame )
        {
            $this->frameset[] = $claroFrame;
        }
        
        /**
         * Add a frame or frameset object to the current frameset as a new row
         *
         * @access  public
         * @param   ClaroFramesetElement claroFrame, frame to add could be a
         *  ClaroFrame or a ClaroFrameset or any other convenient Object
         *  implementing the ClaroFramesetElement API
         * @param   mixed size, row size, could be an int or '*'
         */
        function addRow( $claroFrame, $size )
        {
            $this->rows[] = $size;
            $this->addFrame( $claroFrame );
        }
        
        /**
         * Add a frame or frameset object to the current frameset as a new colum
         *
         * @access  public
         * @param   ClaroFramesetElement claroFrame, frame to add could be a
         *  ClaroFrame or a ClaroFrameset or any other convenient Object
         *  implementing the ClaroFramesetElement API
         * @param   mixed size, column size, could be an int or '*'
         */
        function addCol( $claroFrame, $size )
        {
            $this->cols[] = $size;
            $this->addFrame( $claroFrame );
        }
        
        /**
         * Render the current frameset to be embedded in another HTML frameset
         *
         * @access  public
         * @see     ClaroFramesetElement::render()
         */
        function render()
        {
            $html = '<frameset '
                . ( ! empty( $this->rows )
                    ? 'rows="'. implode(',', $this->rows). '" ' : '' )
                . ( ! empty( $this->cols )
                    ? 'cols="'. implode(',', $this->cols). '" ' : '' )
                . '>' . "\n"
                ;
                
            foreach ( $this->frameset as $element )
            {
                $html .= $element->render();
            }
            
            $html .= '</frameset>' . "\n";
            
            return $html;
        }
        
        /**
         * Send the frameset to the client
         *
         * @access  public
         * @todo    TODO return string instead of echoing it
         */
        function output()
        {
            $output = claro_html_doctype()
                . "\n". '<html>' . "\n"
                . claro_html_headers() . "\n"
                ;
                
            $output .= $this->render();
            
            $output .= '</html>';
            
            echo $output;
        }
    }
?>