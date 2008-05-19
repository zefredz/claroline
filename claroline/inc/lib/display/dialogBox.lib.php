<?php // $Id$

if ( count( get_included_files() ) == 1 )
{
    die( 'The file ' . basename(__FILE__) . ' cannot be accessed directly, use include instead' );
}

/**
 * Dialog Box
 *
 * @version     1.9 $Revision$
 * @copyright   2001-2008 Universite catholique de Louvain (UCL)
 * @author      Claroline Team <info@claroline.net>
 * @license     http://www.gnu.org/copyleft/gpl.html
 *              GNU GENERAL PUBLIC LICENSE version 2.0
 * @package     display
 */

define ( 'DIALOG_INFO',   'DIALOG_INFO' );
define ( 'DIALOG_SUCCESS', 'DIALOG_SUCCESS' );
define ( 'DIALOG_WARNING', 'DIALOG_WARNING' );
define ( 'DIALOG_ERROR', 'DIALOG_ERROR' );
define ( 'DIALOG_QUESTION', 'DIALOG_QUESTION');
define ( 'DIALOG_FORM', 'DIALOG_FORM' );
define ( 'DIALOG_TITLE',   'DIALOG_TITLE' );

class DialogBox implements Display
{
    private $_dialogBox = array();
    private $_size = array();
    private $_boxType = 'auto';

	/*
	 * Constructor
	 */
    public function __construct()
    {
        $this->_size[DIALOG_INFO] = 0;
        $this->_size[DIALOG_SUCCESS] = 0;
        $this->_size[DIALOG_WARNING] = 0;
        $this->_size[DIALOG_ERROR] = 0;
        $this->_size[DIALOG_QUESTION] = 0;
        $this->_size[DIALOG_FORM] = 0;
        $this->_size[DIALOG_TITLE] = 0;
    }

	/*
	 * Add a standard message
	 * @param $msg string text to show in dialog
	 */
    public function info( $msg )
    {
        $this->message( $msg, DIALOG_INFO );
        $this->_size[DIALOG_INFO]++;
    }

	/*
	 * Add a success message
	 * @param $msg string text to show in dialog
	 */
    public function success( $msg )
    {
        $this->message( $msg, DIALOG_SUCCESS );
        $this->_size[DIALOG_SUCCESS]++;
    }

	/*
	 * Add a success message
	 * @param $msg string text to show in dialog
	 */
    public function warning( $msg )
    {
        $this->message( $msg, DIALOG_WARNING );
        $this->_size[DIALOG_WARNING]++;
    }
    
	/*
	 * Add an error message
	 * @param $msg string text to show in dialog
	 */
    public function error( $msg )
    {
        $this->message( $msg, DIALOG_ERROR );
        $this->_size[DIALOG_ERROR]++;
    }

	/*
	 * Add a question
	 * @param $msg string text to show in dialog
	 */
    public function question( $msg )
    {
        $this->message( $msg, DIALOG_QUESTION );
        $this->_size[DIALOG_QUESTION]++;
    }

	/*
	 * Add a form
	 * @param $msg string text to show in dialog
	 */
    public function form( $msg )
    {
        $this->message( $msg, DIALOG_FORM );
        $this->_size[DIALOG_FORM]++;
    }

	/*
	 * Add a title message
	 * @param $msg string text to show in dialog
	 */
    public function title( $msg )
    {
        $this->message( $msg, DIALOG_TITLE );
        $this->_size[DIALOG_TITLE]++;
    }

	/*
	 * internal function used by helpers
	 * @param $msg string text to show in dialog
	 * @param $type type of message to be added
	 */
    private function message( $msg, $type )
    {
        $this->_dialogBox[] = array( 'type' => $type, 'msg' => $msg );
    }

	/*
	 * Set which style should the box have
	 * @param $boxType string text to show in dialog
	 */
    public function setBoxType( $boxType )
    {
        $this->_boxType = $boxType;
    }
    
	/*
	 * returns html required to display the dialog box
	 */
    public function render()
    {
    	if( !empty($this->_dialogBox) )
    	{
	        $out = array();

	        foreach ( $this->_dialogBox as $entry )
	        {
	            $type = $entry['type'];
	            $msg = $entry['msg'];

	            switch ( $type )
	            {
	                case DIALOG_INFO:
	                {
	                    $class = 'msgInfo';
	                } break;
	                case DIALOG_SUCCESS:
	                {
	                    $class = 'msgSuccess';
	                } break;
	                case DIALOG_WARNING:
	                {
	                    $class = 'msgWarning';
	                } break;
	                case DIALOG_ERROR:
	                {
	                    $class = 'msgError';
	                } break;
	                case DIALOG_QUESTION:
	                {
	                    $class = 'msgQuestion';
	                } break;
	                case DIALOG_FORM:
	                {
	                	// forms must always be in a div
	                    $class = 'msgForm';
	                } break;
	                case DIALOG_TITLE:
	                {
	                    $class = 'msgTitle';
	                } break;
	                default:
	                {
	                    $class = 'msgMessage';
	                }
	            }

	            $out[] = '<div class="claroDialogMsg ' . $class . '">' . $msg . '</div>';

	            unset ($type, $msg );
	        }

	        switch( $this->_boxType )
	        {
	            case 'auto' :
	            {
	                 // order is important first meet is choosed
    	            if( $this->_size[DIALOG_ERROR] > 0 )        { $boxClass = 'boxError'; }
    	            elseif( $this->_size[DIALOG_WARNING] > 0 )  { $boxClass = 'boxWarning'; }
	                elseif( $this->_size[DIALOG_SUCCESS] > 0 )  { $boxClass = 'boxSuccess'; }
	                elseif( $this->_size[DIALOG_INFO] > 0 )     { $boxClass = 'boxInfo'; }
	                else                                        { $boxClass = ''; }
	            } break;
	            case 'info' :
	            {
	                $boxClass = 'boxInfo';
	            } break;
	            case 'success' :
	            {
	                $boxClass = 'boxSuccess';
	            } break;
	            case 'warning' :
	            {
	                $boxClass = 'boxWarning';
	            } break;
	            case 'error' :
	            {
	                $boxClass = 'boxError';
	            } break;
	            default : 
	            {
	                $boxClass = '';
	            }
	        }
       
            // todo check that the floating div + spacer do not break design 
	         
	        return '<div class="claroDialogBox ' . $boxClass . '">' . "\n"
	        .	 implode( "\n", $out )
	        .	 '</div>' . "\n\n"
	        .    '<p class="spacer"></p>' . "\n\n";
    	}
    	else
    	{
    		return '';
    	}
    }

}