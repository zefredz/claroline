<?php // $Id$

    // vim: expandtab sw=4 ts=4 sts=4:
    
    if ( count( get_included_files() ) == 1 )
    {
        die( 'The file ' . basename(__FILE__) . ' cannot be accessed directly, use include instead' );
    }

    /**
     * Class to manipulate Urls
     */
    class Url
    {
        private $url = '';

        /**
         * Constructor
         * @param   string url base url (use PHP_SELF if missing)
         */
        public function __construct( $url = '' )
        {
            $url = htmlspecialchars_decode( $url );

            $this->url = empty($url)
                ? $_SERVER['PHP_SELF']
                : $url
                ;
        }

        /**
         * Relay Claroline current context in urls
         */
        public function relayCurrentContext()
        {
            $paramToAdd = array();

            if (claro_is_in_a_course())
            {
                $paramToAdd['cidReq'] = claro_get_current_course_id();
            }

            if (claro_is_in_a_group())
            {
                $paramToAdd['gidReq'] = claro_get_current_group_id();
            }

            $this->addParamList( $paramToAdd );
        }

        /**
         * Add a list of parameters to the current url
         * @param   array paramList associative array of parameters name=>value
         */
        public function addParamList( $paramList )
        {
            if ( !empty( $paramList ) && is_array( $paramList ) )
            {
                $paramListToAdd = array();

                foreach ( $paramList as $name => $value )
                {
                    if ( !preg_match( '/%\d\d/', $value ) )
                    {
                        $value = rawurlencode( $value );
                    }

                    $paramListToAdd[] = "$name=$value";
                }

                $paramListToAdd = implode ( '&', $paramListToAdd );

                if ( strpos ( $this->url, '?' ) === false )
                {
                    $this->url .= '?' . $paramListToAdd;
                }
                else
                {
                    $this->url .= '&' . $paramListToAdd;
                }
            }
        }

        /**
         * Add one parameter to the current url
         * @param   string name parameter name
         * @param   string value parameter value
         */
        public function addParam( $name, $value )
        {
            if ( !preg_match( '/%\d\d/', $value ) )
            {
                $value = rawurlencode( $value );
            }

            $paramToAdd = "$name=$value";

            if ( strpos ( $this->url, '?' ) === false )
            {
                $this->url .= '?' . $paramToAdd;
            }
            else
            {
                $this->url .= '&' . $paramToAdd;
            }
        }

        /**
         * Replace the value of the given parameter with the given value
         * @param   string name parameter name
         * @param   string value parameter value
         * @param   boolean addIfMissing add the parameter if missing (default false)
         * @return  boolean true if replaced or added, else false
         */
        public function replaceParam( $name, $value, $addIfMissing = false )
        {
            if ( !preg_match( '/%\d\d/', $value ) )
            {
                $value = rawurlencode( $value );
            }

            if ( preg_match( "/(&|\?)($name=)([^&])+/", $this->url ) )
            {
                $this->url = preg_replace( "/(&|\?)($name=)([^&])+/", "$1$2$value", $this->url );

                return true;
            }
            elseif ( $addIfMissing )
            {
                $this->addParam( $name, $value );

                return true;
            }
            else
            {
                return false;
            }
        }

        /**
         * Remove the given parameter
         * @param   string name parameter name
         * @return  boolean true if removed, else false
         */
        public function removeParam( $name )
        {
            if ( preg_match( "/(&|\?)($name=)[^&]/", $this->url ) )
            {
                $this->url = preg_replace( "/&$name=[^&]*/", "", $this->url );
                $this->url = preg_replace( "/\?$name=[^&]*/", "?", $this->url );
                $this->url = str_replace( '?&', '?', $this->url );

                return true;
            }
            else
            {
                return false;
            }
        }

        public function toUrl()
        {
            return $this->url;
        }
    }
?>