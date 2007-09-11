<?php // $Id$

if ( count( get_included_files() ) == 1 )
{
    die( 'The file ' . basename(__FILE__) . ' cannot be accessed directly, use include instead' );
}

define ( 'DIALOG_INFO',   'DIALOG_INFO' );
define ( 'DIALOG_SUCCESS', 'DIALOG_SUCCESS' );
define ( 'DIALOG_ERROR', 'DIALOG_ERROR' );
define ( 'DIALOG_QUESTION', 'DIALOG_QUESTION');
define ( 'DIALOG_FORM', 'DIALOG_FORM' );
define ( 'DIALOG_DEBUG',   'DIALOG_DEBUG' );

class DialogBox
{
    var $_dialogBox = array();
    var $_size = array();

	/*
	 * Constructor
	 */
    function DialogBox()
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
    function info( $msg )
    {
        $this->message( $msg, DIALOG_INFO );
        $this->_size[DIALOG_INFO]++;
    }

	/*
	 * Add a success message
	 * @param $msg string text to show in dialog
	 */
    function success( $msg )
    {
        $this->message( $msg, DIALOG_SUCCESS );
        $this->_size[DIALOG_SUCCESS]++;
    }

	/*
	 * Add an error message
	 * @param $msg string text to show in dialog
	 */
    function error( $msg )
    {
        $this->message( $msg, DIALOG_ERROR );
        $this->_size[DIALOG_ERROR]++;
    }

	/*
	 * Add a question
	 * @param $msg string text to show in dialog
	 */
    function question( $msg )
    {
        $this->message( $msg, DIALOG_QUESTION );
        $this->_size[DIALOG_QUESTION]++;
    }

	/*
	 * Add a form
	 * @param $msg string text to show in dialog
	 */
    function form( $msg )
    {
        $this->message( $msg, DIALOG_FORM );
        $this->_size[DIALOG_FORM]++;
    }

	/*
	 * Add a debug message
	 * @param $msg string text to show in dialog
	 */
    function debug( $msg )
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
    function render()
    {
    	if( !empty($this->_dialogBox) )
    	{
	        $out = array();

	        foreach ( $this->_dialogBox as $entry )
	        {
	            $type = $entry['type'];
	            $msg = $entry['msg'];

				// depending on content use a span or a div to display message
				// css style should work in both cases
				if( preg_match('/<span.*>/', $msg ) || preg_match('/<p.*>/', $msg )
					|| preg_match('/<div.*>/', $msg ) || preg_match('/<br.*>/', $msg ) )
				{
					$mode = 'div';
				}
				else
				{
					$mode = 'span';
				}

	            switch ( $type )
	            {
	                case DIALOG_INFO:
	                {
	                    $out[] = '<'.$mode.' class="dialogInfo">' . $msg . '</'.$mode.'>';
	                } break;
	                case DIALOG_SUCCESS:
	                {
	                    $out[] = '<'.$mode.' class="dialogSuccess">' . $msg . '</'.$mode.'>';
	                } break;
	                case DIALOG_ERROR:
	                {
	                    $out[] = '<'.$mode.' class="dialogError">' . $msg . '</'.$mode.'>';
	                } break;
	                case DIALOG_QUESTION:
	                {
	                    $out[] = '<'.$mode.' class="dialogQuestion">' . $msg . '</'.$mode.'>';
	                } break;
	                case DIALOG_FORM:
	                {
	                	// forms must always be in a div
	                    $out[] = '<div class="dialogForm">' . $msg . '</span>';
	                } break;
	                case DIALOG_DEBUG:
	                {
	                    $out[] = '<'.$mode.' class="dialogDebug">' . $msg . '</'.$mode.'>';
	                } break;
	                default:
	                {
	                    $out[] = '<'.$mode.' class="dialogMessage">' . $msg . '</'.$mode.'>';
	                }
	            }

	            unset ($type, $msg );
	        }

	        return '<table class="claroMessageBox" border="0" cellspacing="0" cellpadding="10">' . "\n"
			.	 '<tr>' . "\n"
			.	 '<td>' . "\n"
	        .	 implode( '<br />' . "\n", $out )
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