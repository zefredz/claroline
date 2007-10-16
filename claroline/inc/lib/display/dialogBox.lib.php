<?php // $Id$

if ( count( get_included_files() ) == 1 )
{
    die( 'The file ' . basename(__FILE__) . ' cannot be accessed directly, use include instead' );
}

/**
 * Dialog Box
 *
 * @version     1.9 $Revision$
 * @copyright   2001-2007 Universite catholique de Louvain (UCL)
 * @author      Claroline Team <info@claroline.net>
 * @license     http://www.gnu.org/copyleft/gpl.html
 *              GNU GENERAL PUBLIC LICENSE version 2.0
 * @package     DISPLAY
 */

define ( 'DIALOG_INFO',   'DIALOG_INFO' );
define ( 'DIALOG_SUCCESS', 'DIALOG_SUCCESS' );
define ( 'DIALOG_ERROR', 'DIALOG_ERROR' );
define ( 'DIALOG_QUESTION', 'DIALOG_QUESTION');
define ( 'DIALOG_FORM', 'DIALOG_FORM' );
define ( 'DIALOG_DEBUG',   'DIALOG_DEBUG' );

class DialogBox implements Display
{
    private $_dialogBox = array();
    private $_size = array();

	/*
	 * Constructor
	 */
    public function __construct()
    {
        $this->_size[DIALOG_INFO] = 0;
        $this->_size[DIALOG_SUCCESS] = 0;
        $this->_size[DIALOG_ERROR] = 0;
        $this->_size[DIALOG_QUESTION] = 0;
        $this->_size[DIALOG_FORM] = 0;
        $this->_size[DIALOG_DEBUG] = 0;
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
	 * Add a debug message
	 * @param $msg string text to show in dialog
	 */
    public function debug( $msg )
    {
        $this->message( $msg, DIALOG_DEBUG );
        $this->_size[DIALOG_DEBUG]++;
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
	                    $class = 'dialogInfo';
	                } break;
	                case DIALOG_SUCCESS:
	                {
	                    $class = 'dialogSuccess';
	                } break;
	                case DIALOG_ERROR:
	                {
	                    $class = 'dialogError';
	                } break;
	                case DIALOG_QUESTION:
	                {
	                    $class = 'dialogQuestion';
	                } break;
	                case DIALOG_FORM:
	                {
	                	// forms must always be in a div
	                    $class = 'dialogForm';
	                } break;
	                case DIALOG_DEBUG:
	                {
	                    $class = 'dialogDebug';
	                } break;
	                default:
	                {
	                    $class = 'dialogMessage';
	                }
	            }

	            $out[] = '<div class="' . $class . '">' . $msg . '</div>';

	            unset ($type, $msg );
	        }

	        return '<table class="claroMessageBox" border="0" cellspacing="0" cellpadding="10">' . "\n"
			.	 '<tr>' . "\n"
			.	 '<td>' . "\n"
	        .	 implode( "\n", $out )
	        .	 '</td>' . "\n"
	        .	 '</tr>' . "\n"
	        .	 '</table>' . "\n\n";
    	}
    	else
    	{
    		return '';
    	}
    }

}