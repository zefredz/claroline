<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * CLAROLINE
 *
 * Backlog.
 *
 * @version     Claroline 1.12 $Revision$
 * @copyright   (c) 2001-2014, Universite catholique de Louvain (UCL)
 * @author      Claroline Team <info@claroline.net>
 * @license     http://www.gnu.org/copyleft/gpl.html
 *              GNU GENERAL PUBLIC LICENSE version 2 or later
 * @package     kernel.core
 */

define ( 'BACKLOG_SUCCESS', 'BACKLOG_SUCCESS' );
define ( 'BACKLOG_FAILURE', 'BACKLOG_FAILURE' );
define ( 'BACKLOG_DEBUG',   'BACKLOG_DEBUG' );
define ( 'BACKLOG_INFO',   'BACKLOG_INFO' );

/**
 * Backlog class
 * @deprecated since Claroline 1.10, use exceptions, Console.lib or DialogBox 
 *  instead
 */
class Backlog implements Display
{
    protected $_backlog = array();
    protected $_size = array();
    
    /**
     * Initialize backlog
     */
    public function __construct()
    {
        $this->_size[BACKLOG_SUCCESS] = 0;
        $this->_size[BACKLOG_FAILURE] = 0;
        $this->_size[BACKLOG_DEBUG] = 0;
        $this->_size[BACKLOG_INFO] = 0;
    }
    
    /**
     * Add success message to backlog
     * @param string $msg
     */
    public function success( $msg )
    {
        $this->message( $msg, BACKLOG_SUCCESS );
        $this->_size[BACKLOG_SUCCESS]++;
    }
    
    /**
     * Add failure message to backlog
     * @param string $msg
     */
    public function failure( $msg )
    {
        $this->message( $msg, BACKLOG_FAILURE );
        $this->_size[BACKLOG_FAILURE]++;
    }
    
    /**
     * Add debug message to backlog
     * @param string $msg
     */
    public function debug( $msg )
    {
        $this->message( $msg, BACKLOG_DEBUG );
        $this->_size[BACKLOG_DEBUG]++;
    }
    
    /**
     * Add information message to backlog
     * @param string $msg
     */
    public function info( $msg )
    {
        $this->message( $msg, BACKLOG_INFO );
        $this->_size[BACKLOG_INFO]++;
    }
    
    /**
     * Add message to backlog
     * @param string $msg
     * @param type
     */
    protected function message( $msg, $type )
    {
        $this->_backlog[] = array( 'type' => $type, 'msg' => $msg );
    }
    
    /**
     * Get size of the backlog by type
     * @param string $type type, if none given, the function will return the total size of the backlog
     * @return int
     */
    public function size( $type = null )
    {
        switch ( $type )
        {
            case BACKLOG_SUCCESS:
            case BACKLOG_FAILURE:
            case BACKLOG_DEBUG:
            case BACKLOG_INFO:
            {
                return $this->_size[$type];
            } break;

            default:
            {
                return count($this->_backlog);
            }
        }
    }
    
    /**
     * Render the backlog to HTML
     * @see Display
     * @return string
     * @since Claroline 1.12
     */
    public function render()
    {
        $out = array();

        foreach ( $this->_backlog as $entry )
        {
            $type = $entry['type'];
            $msg = $entry['msg'];

            switch ( $type )
            {
                case BACKLOG_SUCCESS:
                {
                    $out[] = '<span class="backlogSuccess">' . $msg . '</span>';
                } break;
                case BACKLOG_FAILURE:
                {
                    $out[] = '<span class="backlogFailure">' . $msg . '</span>';
                } break;
                case BACKLOG_DEBUG:
                {
                    $out[] = '<span class="backlogDebug">' . $msg . '</span>';
                } break;
                case BACKLOG_INFO:
                {
                    $out[] = '<span class="backlogInfo">' . $msg . '</span>';
                } break;
                default:
                {
                    $out[] = '<span class="backlogMessage">' . $msg . '</span>';
                }
            }

            unset ($type, $msg );
        }

        return implode( '<br />' . "\n", $out );
    }
    
    /**
     * Alias of render()
     * @return string
     */
    public function output()
    {
        return $this->render();
    }
    
    /**
     * Append another backlog to the current one
     * @param Backlog $other
     * @return \Backlog $this
     */
    public function append( Backlog $other )
    {
        $this->_backlog = array_merge( $this->_backlog, $other->_backlog );
        return $this;
    }
}

/**
 * HTML report viewer for Backlog message
 */
class Backlog_Reporter
{
    public static function report( $summary, $details, $label = '', $focus = false )
    {
        $id = uniqid('details');

        if ( empty( $details ) )
        {
            $display = '<span class="backlogSummary">'.$summary.'</span>';
        }
        else
        {
            $linkName = ( $focus ) ? "#lnk_$id" : "#";
            $labeldetails = empty( $label ) ? get_lang('details') : $label;
            $display = <<<__ERRDISP__
<script type="text/javascript">
function toggleDetails( id )
{
var details = document.getElementById( id );

if ( details.style.display == 'block' )
{
    details.style.display = 'none';
}
else
{
    details.style.display = 'block';
}
}
</script>
<a name="lnk_$id"></a>
<span class="backlogSummary">$summary
[<a href="$linkName" onclick="toggleDetails('$id');return false;">$labeldetails</a>]
</span>
<div id="$id" style="display: none;" class="backlogDetails">
$details
</div>
__ERRDISP__;
        }

        return $display;
    }
}
