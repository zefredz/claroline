<?php // $Id$

    // vim: expandtab sw=4 ts=4 sts=4:
    
    if ( count( get_included_files() ) == 1 )
    {
        die( 'The file ' . basename(__FILE__) . ' cannot be accessed directly, use include instead' );
    }
    
    /**
     * Claroline Template Engine
     *
     * @version     1.9 $Revision$
     * @copyright   2001-2007 Universite catholique de Louvain (UCL)
     * @author      Claroline Team <info@claroline.net>
     * @author      Frederic Minne <zefredz@claroline.net>
     * @license     http://www.gnu.org/copyleft/gpl.html
     *              GNU GENERAL PUBLIC LICENSE version 2.0
     * @package     DISPLAY
     */
    
    define ( 'BLOCK_MAX_RECURSION_DEPTH', 5 );

    class ClaroTemplate
    {
        private $_replacementList;
        private $_tplString;
        
        private $_allowCallback;
        private $_callBack;
        
        private $_blockDisplay;
        private $_blocks;
        private $_displayBlockByDefault = false;
        
        public function __construct( $tplString, $replacementList = null )
        {
            $this->_tplString = $tplString;
            $this->_replacementList = array();
            $this->_allowCallback = false;
            $this->_callBack = array();
            
            $this->_blockDisplay = array();
            $this->_blocks = array();
            
            if ( is_array( $replacementList ) && !empty( $replacementList ) )
            {
                $this->_replacementList = $replacementList;
            }
            
            $this->_initCommonPlaceholders();
        }
        
        private function _initCommonPlaceholders()
        {
            $course = claro_get_current_course_data();
            $this->addReplacement( 'course', $course );
            
            $user = claro_get_current_user_data();
            $this->addReplacement( 'user', $user );
        }
        
        public function setBlockDisplay( $blockName, $display = true )
        {
            $this->_blockDisplay[$blockName] = $display ? true : false;
        }
        
        public function showBlocksByDefault()
        {
            $this->_displayBlockByDefault = true;
        }
        
        public function hideBlocksByDefault()
        {
            $this->_displayBlockByDefault = false;
        }
        
        private function _parseTemplate()
        {
            $parser = new ClaroTemplateParser;
            
            if ( ( $ret = $parser->parse( $this->_tplString ) ) )
            {
                $this->_blocks = $parser->getBlocks();
                return $ret;
            }
            else
            {
                return false;
            }
        }
        
        public function addReplacement( $placeHolder, $replacementValue )
        {
            if ( array_key_exists( $placeHolder, $this->_replacementList ) )
            {
                Console::debug( "Overwriting existing place holder $placeHolder" );
            }
            
            $this->_replacementList[$placeHolder] = $replacementValue;
        }
        
        public function addReplacementList( $replacementList )
        {
            $overwritten = array_diff( $this->_replacementList, $replacementList );
            
            if ( count ( $overwritten ) )
            {
                Console::debug( "Overwriting existing place holders "
                    . implode( ',', $overwritten ) );
            }
            
            $this->_replacementList = array_merge( $this->_replacementList, $replacementList );
        }
        
        public function render()
        {
            $output = $this->_parseTemplate();
            
            $output = $this->_renderBlocks( $output );
            
            foreach ( $this->_replacementList as $placeHolder => $replacementValue )
            {
                if ( false === $this->_replace( $placeHolder, $replacementValue, $output ) )
                {
                    Console::debug( "Place holder $placeHolder not found in template" );
                }
            }
            
            // process template public functions
            
            // parse call to locale() -> get_locale
            while ( preg_match( "/%locale\(\s*([\w_]+)\s*\)%/", $output, $matches ) )
            {
                $replacement = get_locale( $matches[1] );
                $output = preg_replace( "/%locale\(\s*(".$matches[1].")\s*\)%/"
                    , $replacement, $output );
            }
            
            // parse call to lang() -> get_lang
            while ( preg_match( "/%lang\(\s*([\w_]+)\s*\)%/", $output, $matches ) )
            {
                $replacement = get_lang( $matches[1] );
                $output = preg_replace( "/%lang\(\s*(".$matches[1].")\s*\)%/"
                    , $replacement, $output );
            }
            
            // parse call to path() -> get_path
            while ( preg_match( "/%path\(\s*([\w_]+)\s*\)%/", $output, $matches ) )
            {
                $replacement = get_path( $matches[1] );
                $output = preg_replace( "/%path\(\s*(".$matches[1].")\s*\)%/"
                    , $replacement, $output );
            }
            
            // parse call to conf() -> get_conf
            while ( preg_match( "/%conf\(\s*([\w_]+)\s*\)%/", $output, $matches ) )
            {
                $replacement = get_conf( $matches[1] );
                $output = preg_replace( "/%conf\(\s*(".$matches[1].")\s*\)%/"
                    , $replacement, $output );
            }
            
            // parse call to init() -> get_init
            while ( preg_match( "/%init\(\s*([\w_]+)\s*\)%/", $output, $matches ) )
            {
                $replacement = get_init( $matches[1] );
                $output = preg_replace( "/%init\(\s*(".$matches[1].")\s*\)%/"
                    , $replacement, $output );
            }
            
            // parse call to dock() -> ClaroDock::render()
            while ( preg_match( "/%dock\(\s*([\w_]+)\s*\)%/", $output, $matches ) )
            {
                $dock = new ClaroDock( $matches[1] );
                $output = preg_replace( "/%dock\(\s*(".$matches[1].")\s*\)%/"
                    , $dock->render(), $output );
            }
            
            return $output;
        }
        
        public function allowCallback()
        {
            $this->_allowCallback = true;
        }

        public function registerCallback( $key, $callback )
        {
            $this->_callBack[$key] = $callback;
        }
        
        private function _formatPlaceHolder( $placeHolder )
        {
            if ( $placeHolder[0] == '%' )
            {
                $placeHolder = substr( $placeHolder, 1 );
            }

            if ( $placeHolder[strlen($placeHolder)-1] == '%' )
            {
                $placeHolder = substr( $placeHolder, 0, strlen($placeHolder)-1 );
            }

            return $placeHolder;
        }

        private function _renderBlocks( $output )
        {
            foreach ( $this->_blocks as $blockName => $blockTemplate )
            {
                $output = $this->_renderBlockRec( $blockName, $output );
            }

            return $output;
        }
        
        /**
         * Render nested blocks
         */
        private function _renderBlockRec( $blockName, $output, $depth = 0 )
        {
            if ( ( array_key_exists( $blockName, $this->_blockDisplay )
                    &&  $this->_blockDisplay[$blockName] )
                || ( ! array_key_exists( $blockName, $this->_blockDisplay )
                    && $this->_displayBlockByDefault ) )
            {
                $blockTemplate = $this->_blocks[$blockName];
                
                if ( preg_match( '/\%@\([\w_\.]+\)%/', $blockTemplate )
                    && $depth < BLOCK_RECURSION_MAX_DEPTH )
                {
                    $depth++;
                    $output = $this->_renderBlockRec( $blockName, $blockTemplate, $depth );
                }

                $output = str_replace( "%@($blockName)%"
                    , $blockTemplate, $output );
            }
            else
            {
                $output = str_replace( "%@($blockName)%", '', $output );
            }
                
            return $output;
        }
        
        private function _replace( $placeHolder, $value, &$output )
        {
            $found = false;
            
            $placeHolder = $this->_formatPlaceHolder( $placeHolder );
            
            if ( false !== strpos( $output, "%$placeHolder%" ) )
            {
                $output = str_replace( "%$placeHolder%", $value, $output );
                $found = true;
            }
            
            if ( false !== strpos( $output, "%html($placeHolder)%" ) )
            {
                $output = str_replace( "%html($placeHolder)%", htmlspecialchars( $value ), $output );
                $found = true;
            }
            
            if ( false !== strpos( $output, "%uu($placeHolder)%" ) )
            {
                $output = str_replace( "%uu($placeHolder)%", rawurlencode( $value ), $output );
                $found = true;
            }
            
            if ( false !== strpos( $output, "%int($placeHolder)%" ) )
            {
                $output = str_replace( "%int($placeHolder)%", (int) $value, $output );
                $found = true;
            }
            
            $matches = array();
            
            while ( preg_match( "/\%$placeHolder\[(\w+)\]\%/", $output, $matches ) )
            {
                $index = $matches[1];
                
                if ( is_array( $value )
                    && array_key_exists( $index, $value ) )
                {
                    $output = preg_replace(
                        "/\%$placeHolder\[".$matches[1]."\]\%/"
                        , $value[$index], $output );
                        
                    $found = true;
                }
                else
                {
                    Console::debug( "$index not found in $placeHolder" );
                    
                    $output = preg_replace(
                        "/\%$placeHolder\[".$index."\]\%/"
                        , '', $output );
                }
            }
            
            $matches = array();
            
            while ( preg_match( "/\%(\w+)\(${placeHolder}\[(\w+)\]\)\%/", $output, $matches ) )
            {
                $func = $matches[1];
                $index = $matches[2];
                    
                if ( is_array( $value )
                    && array_key_exists( $matches[2], $value ) )
                {
                    $val = $value[$index];
                    
                    if ( $func == 'html' )
                    {
                        $val = htmlspecialchars($val);
                    }
                    elseif ( $func == 'uu' )
                    {
                        $val = rawurlencode( $val );
                    }
                    elseif ( $func == 'int' )
                    {
                        $val = (int) $val;
                    }
                    
                    $output = preg_replace(
                        "/\%${func}\(${placeHolder}\[${index}\]\)\%/"
                        , $val , $output );

                    $found = true;
                }
                else
                {
                    $func = $matches[1];
                    $index = $matches[2];
                    
                    Console::debug( "$index not found in $placeHolder" );
                    
                    $output = preg_replace(
                        "/\%${func}\(${placeHolder}\[${index}\]\)\%/"
                        , '' , $output );
                }
            }
            
            if ( $this->_allowCallback && array_key_exists( $placeHolder, $this->_callBack ) )
            {
                $matches = array();

                while ( preg_match( "/%apply\(\s*([\w_]+)\s*,\s*(".$placeHolder.")\s*\)%/", $output, $matches ) )
                {
                    if ( $this->_callBack[$placeHolder] == $matches[1] )
                    {
                        $replacement = call_user_func( $matches[1], $value, $matches[2] );
                        $output = preg_replace( "/%apply\(\s*([\w_]+)\s*,\s*(".$placeHolder.")\s*\)%/"
                            , $replacement, $output );
                            
                        $found = true;
                    }
                    else
                    {
                        Console::debug( "Not allowed callback for $placeHolder" );
                    }
                }
            }
            
            return $found;
        }
    }
    
    class ClaroTemplateLoader
    {
        private $tplFile;
        private $extension;
        private $baseDir;
        
        public function __construct( $tplFile, $baseDir = '', $extension = 'tpl' )
        {
            $this->extension = $extension;
            $this->baseDir = empty($baseDir)
                ? $this->getTemplatePath()
                : $baseDir
                ;
            $this->tplFile = $tplFile;
        }
        
        public function getTemplatePath()
        {
            return get_path('rootSys') . '/platform/tpl';
        }
        
        public function load()
        {
            $tplFile = $this->baseDir . '/' . $this->tplFile;
            
            if ( file_exists( $tplFile ) )
            {
                if ( substr( $tplFile, - strlen($this->extension) ) == $this->extension )
                {

                    $this->tplFile = $tplFile;

                    $tplString = file_get_contents( $tplFile );

                    if ( $tplString )
                    {
                        $tpl = new ClaroTemplate($tplString);
                        return $tpl;
                    }
                    else
                    {
                        // error
                        Console::error( "Cannot read file $tplFile" );
                        return false;
                    }
                }
                else
                {
                    Console::error( "Not a template file extension $tplFile" );
                    return false;
                }
            }
            else
            {
                // error
                Console::error( "File not found $tplFile" );
                return false;
            }
        }
        
        public function setBaseDir( $baseDir )
        {
            $this->baseDir = $baseDir;
        }
        
        public function setTemplateExtension( $extension )
        {
            $this->extension = $extension;
        }
    }
    
    class ClaroTemplateParser
    {
        private $_blocks = array();
        private $_stack = array();
        
        public function getBlocks()
        {
            return $this->_blocks;
        }

        public function inBlock()
        {
            return !empty($this->_stack);
        }

        public function parse( $tplString )
        {
            $blockStart = '/\%block\(\s*([\w_\.-]+)\s*\)\:\%/';
            $blockEnd = '/\%end\(\s*([\w_\.-]+)\s*\)\%/';

            $lines = preg_split( '/(\r\n|\r|\n)/', $tplString );

            $retFile = '';

            foreach ( $lines as $line )
            {
                $matches = array();

                if ( preg_match( $blockStart, $line, $matches ) )
                {
                    array_push( $this->_stack, $matches[1] );
                    $this->_blocks[$matches[1]] = '';
                }
                elseif ( preg_match( $blockEnd, $line, $matches ) )
                {
                    if ( $this->inBlock() )
                    {
                        if ( $matches[1] == array_pop( $this->_stack ) )
                        {
                            if ( $this->inBlock() )
                            {
                                $parent = $this->_stack[count($this->_stack)-1];

                                $this->_blocks[$parent] .= "%@(".$matches[1].")%\n";
                            }
                            else
                            {
                                $retFile .= "%@(".$matches[1].")%\n";
                            }
                        }
                        else
                        {
                            trigger_error( "Error : missing end of block $line" );
                            return false;
                        }
                    }
                    else
                    {
                        trigger_error( "Error : end of block when not in a block $line" );
                        return false;
                    }
                }
                elseif ( $this->inBlock() )
                {
                    $current = $this->_stack[count($this->_stack)-1];
                    $this->_blocks[$current] .= $line . "\n";
                }
                // ---- Default ----
                else
                {
                    $retFile .= $line . "\n";
                }
            }

            if ( $this->inBlock() )
            {
                trigger_error( "Some blocks have not been properly closed : "
                    . implode ( ',', $this->_stack ) );
                return false;
            }

            return $retFile;
        }
    }
?>