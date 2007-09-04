<?php // $Id$

class Form
{
	// vars
	var $_paramList = array();
	var $_fieldList = array();

	var $_inputSize;
	var $_isDatePickerLoaded;

	// construct
	function Form($name)
	{
		$this->setName($name);

		// TODO use get_conf
		//$this->_inputSize = get_conf('formSize', 40);
		$this->_inputSize = 40;
		$this->_isDatePickerLoaded = false;
	}

	// define form
	//-- general set param method
	function setParam($paramName, $value)
	{
		$this->_paramList[$paramName] = trim($value);
	}

	//-- set params helper
	function setName($value)
	{
		$this->setParam('name', $value );
	}

	function setId($value)
	{
		$this->setParam('id', $value );
	}

	function setClass($value)
	{
		$this->setParam('class', $value );
	}

	function setAction($value)
	{
		$this->setParam('action', $value );
	}

	function setMethod($value)
	{
		$this->setParam('method', $value );
	}

	function setEnctype($value)
	{
		$this->setParam('enctype', $value );
	}

	// populate form
	function _addField($field)
	{
		$this->_fieldList[] = $field;
	}

	// display
	function output()
	{
		// header
		$html = '<form ' . $this->outputParams() . '>' . "\n";

		// fields
		$html .= $this->outputFields() . "\n";

		// buttons
		$html .= '<input type="submit" value="Ok" />' . "\n";
		// footer
		$html .= '</form>' . "\n";

		return $html;
	}

	function outputParams()
	{
		$out = array();

		foreach( $this->_paramList as $param => $value )
		{
			$out[] = $param . '="' . htmlspecialchars($value) . '"';
		}

		return implode(' ', $out);
	}

	function outputFields()
	{
		$out = array();

		foreach( $this->_fieldList as $field )
		{
			$out[] = $field;
		}

		return implode(' ' . "\n", $out);
	}


	function requiredField($label)
	{
		return '<span class="required">*</span>&nbsp;' . $label;
	}

	function openFieldset($legend)
	{
		$html = '<fieldset>' . "\n"
		.	 '<legend>' . htmlspecialchars($legend) . '</legend>' . "\n";

		$this->_addField($html);
	}

	function closeFieldset()
	{
		$html = "\n" . '</fieldset>' . "\n";

		$this->_addField($html);
	}
	// create fields
	/*
	 * text
	 * password
	 * hidden
	 * textearea
	 * select box
	 * radio
	 * check
	 * date / time  / date time
	 * descriptive text
	 */
	function addInputHidden($name, $value)
	{
		$html = '<input type="hidden" id="'.$name.'" name="'.$name.'" value="'.htmlspecialchars($value).'" />';

		$this->_addField($html);
	}

	function addInputText($name, $value, $label = '', $required = false, $size = -1, $maxLength = -1)
	{
		if( $required ) $displayedLabel = $this->requiredField($label);
		else			$displayedLabel = $label;

		if( $size != -1 ) 	$displayedSize = (int) $size;
		else				$displayedSize = $this->_inputSize;

		if( $maxLength != -1 ) 	$displayedMaxLength = (int) $maxLength;
		else					$displayedMaxLength = (int) 255;

		$html = '<p>' . "\n"
		.	 '<label for="'.$name.'">'.$displayedLabel.'</label>' . "\n"
		.	 '<input type="text" id="'.$name.'" name="'.$name.'" value="'.htmlspecialchars($value).'" size="'.$displayedSize.'" maxlength="'.$displayedMaxLength.'" />' . "\n"
		.	 '</p>';

		$this->_addField($html);
	}

	function addInputPassword($name, $value, $label = '', $required = false,  $size = -1, $maxLength = -1)
	{
		if( $required ) $displayedLabel = $this->requiredField($label);
		else			$displayedLabel = $label;

		if( $size != -1 ) 	$displayedSize = (int) $size;
		else				$displayedSize = $this->_inputSize;

		if( $maxLength != -1 ) 	$displayedMaxLength = (int) $maxLength;
		else					$displayedMaxLength = (int) 255;

		$html = '<p>' . "\n"
		.	 '<label for="'.$name.'">'.$displayedLabel.'</label>' . "\n"
		.	 '<input type="password" id="'.$name.'" name="'.$name.'" value="'.htmlspecialchars($value).'" size="'.$displayedSize.'" maxlength="'.$displayedMaxLength.'" />' . "\n"
		.	 '</p>';

		$this->_addField($html);
	}

	function addTextarea($name, $value, $label = '', $required = false, $wysiwyg = false, $cols  = -1, $rows = 20,$optAttrib = '')
	{
		if( $required ) $displayedLabel = $this->requiredField($label);
		else			$displayedLabel = $label;

		if( $cols != -1 ) 	$displayedCols = (int) $cols;
		else				$displayedCols = $this->_inputSize;

		if( $rows != -1 ) 	$displayedRows = (int) $rows;
		else				$displayedRows = (int) 20;

		$html = '';

		if( $wysiwyg )
		{
			$claro_editor = get_conf('claro_editor', 'tiny_mce');

		    // $claro_editor is the directory name of the editor
		    $incPath = get_path('rootSys') . 'claroline/editor/' . $claro_editor;
		    $editorPath = get_path('url') . '/claroline/editor/';
		    $webPath = $editorPath . $claro_editor;

		    if( file_exists($incPath . '/editor.class.php') )
		    {
		        // include editor class
		        include_once $incPath . '/editor.class.php';

		        // editor instance
		        $editor = new editor($name,$value,$rows,$cols,$optAttrib,$webPath);

		        $html .= $editor->getAdvancedEditor();
		    }
		    else
		    {
		    	// force display of textarea as it will not be possible to display it in wysiwyg mode
		    	$wysiwyg = false;
		    }

		}

		if( !$wysiwyg )
		{
			$html = '<p>' . "\n"
			.	 '<label for="'.$name.'">'.$displayedLabel.'</label>' . "\n"
			.	 '<textarea id="'.$name.'" name="'.$name.'" cols="'.$cols.'" rows="'.$rows.'" '.$optAttrib.'>'
			.	 htmlspecialchars($value)
			.	 "\n" . '</textarea>' . "\n"
			.	 '</p>';
		}

		$this->_addField($html);

	}

	function addSelect($name, $optionList, $label = '', $required = false, $selected = null, $size = -1, $attrList = null )
	{
		if( $required ) $displayedLabel = $this->requiredField($label);
		else			$displayedLabel = $label;

		$html = '<p>'  . "\n"
		.	 '<label for="'.$name.'">'.$displayedLabel.'</label>' . "\n"
		.	 '<select id="'.$name.'" name="'.$name.'" >' . "\n"
		.	 $this->_buildOptionList($optionList, $selected) . "\n"
		.	 '</select>' . "\n"
		.	 '</p>' . "\n";

		$this->_addField($html);
	}

	function _buildOptionList($optionList, $selected)
	{
		$html = '';

		if( !is_array($optionList) ) return $html;

		foreach( $optionList as $optionValue => $optionLabel )
		{
			// check if value must be selected
			if( ( !is_array($selected) && $optionValue == $selected )
				|| ( is_array($selected) && in_array($optionValue, $selected) )
			)
			{
				$displaySelected = 'selected="selected"';
			}
			else
			{
				$displaySelected = '';
			}

			$html .= '<option value="'.$optionValue.'" '.$displaySelected.'>'
			.	 htmlspecialchars($optionLabel)
			.	 '</option>' . "\n";
		}

		return $html;
	}

	function addRadioList($name, $radioList, $label, $required = false, $checked = null)
	{
		if( $required ) $displayedLabel = $this->requiredField($label);
		else			$displayedLabel = $label;

		if( !is_array($radioList) || empty($radioList) ) return '';

		// cannot make a multiple selection so get the first value if checked is an array
		if( is_array($checked) && !empty($checked) ) $checked = $checked[0];

		$html = '<fieldset class="radio">'  . "\n"
		.	 '<legend>'.$displayedLabel.'</legend>' . "\n";

		$i = 1;
		foreach( $radioList as $radioValue => $radioLabel )
		{
			// check if value must be selected
			if( $radioValue == $checked )
			{
				$displayChecked = 'checked="checked"';
			}
			else
			{
				$displayChecked = '';
			}

			$html .= '<label for="'.$name.$i.'">' . "\n"
			.	 '<input type="radio" id="'.$name.$i.'" name="'.$name.'" value="'.$radioValue.'" '.$displayChecked.' />'
			.	 htmlspecialchars($radioLabel).'</label>' . "\n";

			$i++;
		}

		$html .= '</fieldset>' . "\n";

		$this->_addField($html);
	}


	function addCheckboxList($name, $checkboxList, $label, $required = false, $checked = null)
	{
		if( $required ) $displayedLabel = $this->requiredField($label);
		else			$displayedLabel = $label;

		if( !is_array($checkboxList) || empty($checkboxList) ) return '';

		$html = '<fieldset class="checkbox">'  . "\n"
		.	 '<legend>'.$displayedLabel.'</legend>' . "\n";

		$i = 1;
		foreach( $checkboxList as $checkboxValue => $checkboxLabel )
		{
			// check if value must be selected
			if( ( !is_array($checked) && $checkboxValue == $checked )
				|| ( is_array($checked) && in_array($checkboxValue, $checked) )
			)
			{
				$displayChecked = 'checked="checked"';
			}
			else
			{
				$displayChecked = '';
			}

			$html .= '<label for="'.$name.$i.'">' . "\n"
			.	 '<input type="checkbox" id="'.$name.$i.'" name="'.$name.'" value="'.$checkboxValue.'" '.$displayChecked.' />'
			.	 htmlspecialchars($checkboxLabel).'</label>' . "\n";

			$i++;
		}

		$html .= '</fieldset>' . "\n";

		$this->_addField($html);
	}

	function addInputFile($name, $label = '', $required = false)
	{
		// accept,

		if( $required ) $displayedLabel = $this->requiredField($label);
		else			$displayedLabel = $label;

		// ensure enctype is correct
		$this->setEnctype('multipart/form-data');

		$html = '<p>' . "\n"
		.	 '<label for="'.$name.'">'.$displayedLabel.'</label>' . "\n"
		.	 '<input type="file" id="'.$name.'" name="'.$name.'" />' . "\n"
		.	 '</p>';

		$this->_addField($html);

	}

	function loadDatePicker()
	{
		if( ! $this->_isDatePickerLoaded )
		{
			$GLOBALS['htmlHeadXtra'][] = '<script type="text/javascript" src="./js/jquery.js"></script>';
			$GLOBALS['htmlHeadXtra'][] = '<style type="text/css">@import url(./js/jquery-calendar.css);</style>';
			$GLOBALS['htmlHeadXtra'][] = '<script type="text/javascript" src="./js/jquery-calendar.js"></script>';

			// default configuration of date pickers
			$GLOBALS['htmlHeadXtra'][] = '<script type="text/javascript">' . "\n"
			.	 '$(document).ready(function(){'
			.	 ' popUpCal.setDefaults({'
			.	  	'autoPopUp: "button",'
			.	  	'buttonImageOnly: true,'
			.	  	'buttonImage: "./img/calendar.png",'
			.	  	'buttonText: "'.get_lang('Choose a date').'",'
			.	  	'firstDay: 1,'
			.	  	'changeFirstDay: false,'
			.	  	'changeMonth: false,'
			.	  	'changeYear: false'
			.	 ' });'
			.	 '});</script>';

			// function used to get the date of an input (mainly used for date range methods)
			$GLOBALS['htmlHeadXtra'][] = '<script type="text/javascript">' . "\n"
			.	 'function getDate(value) {' . "\n"
			.	 ' fields = value.split("/");' . "\n"
			.	 ' return (fields.length < 3 ? null : new Date(parseInt(fields[2], 10), parseInt(fields[1], 10) - 1, parseInt(fields[0], 10)));' . "\n"
			.	 '}' . "\n"
			.	 '</script>' . "\n";

       		$this->_isDatePickerLoaded = true;
		}
	}

	function addDateSelector($name, $timestamp, $label, $required = false)
	{
		$this->loadDatePicker();

		// http://marcgrabanski.com/code/jquery-calendar/
		$format = '';

		if( $required ) $displayedLabel = $this->requiredField($label);
		else			$displayedLabel = $label;

		$html = '<p>' . "\n"
		.	 '<label for="'.$name.'">'.$displayedLabel.'</label>' . "\n"
		.	 '<input type="text" size="10" id="'.$name.'" name="'.$name.'" class="'.$name.'_datePicker" />' . "\n"
		.	 '</p>' . "\n"
		.    '<script type="text/javascript">$(document).ready(function(){' . "\n"
       	.	 ' $(".'.$name.'_datePicker").calendar({});' . "\n"
       	.	 '});</script>' . "\n";

		$this->_addField($html);
	}

	function addDateRange($name, $startTimestamp, $endTimestamp, $label, $required = false)
	{
		$this->loadDatePicker();

		// http://marcgrabanski.com/code/jquery-calendar/
		$startDate = date('d/m/Y', $startTimestamp);
		$endDate = date('d/m/Y', $endTimestamp);

		if( $required ) $displayedLabel = $this->requiredField($label);
		else			$displayedLabel = $label;

		$html = '<p>' . "\n"
		.	 '<label for="'.$name.'">'.$displayedLabel.'</label>' . "\n"
		.	 get_lang('From') . '&nbsp; '
		.	 '<input type="text" size="10" id="'.$name.'_from" name="'.$name.'_from" class="'.$name.'_datePicker" value="'.$startDate.'" />' . "\n"
		.	 get_lang('to') . '&nbsp; '
		.	 '<input type="text" size="10" id="'.$name.'_to" name="'.$name.'_to" class="'.$name.'_datePicker" value="'.$endDate.'" />' . "\n"
		.	 '</p>' . "\n"
		.    '<script type="text/javascript">$(document).ready(function(){' . "\n"
       	.	 ' $(".'.$name.'_datePicker").calendar({fieldSettings: dateRange});' . "\n"
		.	 ' function dateRange(input) {' . "\n"
		.	 ' return {minDate: (input.id == "'.$name.'_to" ? getDate($("#'.$name.'_from").val()) : null),' . "\n"
		.	 ' maxDate: (input.id == "'.$name.'_from" ? getDate($("#'.$name.'_to").val()) : null)};' . "\n"
		.	 ' }' . "\n"
       	.	 '});</script>' . "\n";

		$this->_addField($html);
	}

	function addTimeSelector($label, $name, $timestamp)
	{

	}

	function addDateTimeSelector($label, $name, $timestamp)
	{

	}
}

?>