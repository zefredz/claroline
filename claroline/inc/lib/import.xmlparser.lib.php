<?php // $Id$
if ( count( get_included_files() ) == 1 ) die( '---' );


/**
* CLAROLINE
*
* @version 1.8 $Revision$
*
* @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
* @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
*
* @package CLEXPORT
*
* @author Yannick Wautelet <yannick_wautelet@hotmail.com>
* @author Claro Team <cvs@claroline.net>
*/

/**
 *
 * parse the generic xml file of a tool into an array
 *
 * @var array   $tab  		- xml content
 * @var string  $tag  		- xml tag
 * @var int     $id   		- id of the "record"
 * @var int     $deep 		- integer to inform how deep we are in the xml tree
 * @var string  $tabname    - name of the table to import into the array
 * @var boolean $isGeneric  - boolean to check if the tag name is "Generic"
 * @var boolean $isDataNull - boolean to check if the imported data value must be null
 *
 * @since 1.8 - 10-avr.-2006
 *
 * @access public
 */
class generic_tool_parser
{
	var $tab = array ();
	var $tag;
	var $id;
	var $deep = 0;
	var $tabname = null;
	var $isGeneric = false;
	var $isDataNull = false;

	/**
	 * sax start_element handler
	 * @access private
	 * @see sax api
	 */
	function start_element($parser, $tag, $attributes)
	{
		$this->deep ++;
		$this->tag = $tag;
		if(2 == $this->deep)
		{
			$this->tabName = $tag;
			$this->tab[$this->tabName] = array();
		}

		if($this->isGeneric)
		{
			if (! isset($attributes["isNull"]))
			{
				$this->tab[$this->tabName]["content"][$this->id][$this->tag] = '';
				$this->isDataNull = false;
			}
			else
			{
				$this->tab[$this->tabName]["content"][$this->id][$this->tag] = null;
				$this->isDataNull = true;
			}
		}
		if(3 == $this->deep && "content" == $this->tag)
		{
			$this->isGeneric = true;
			$this->id = $attributes["id"];
			$this->tab[$this->tabName][$this->tag][$this->id] = array();
		}
		elseif(3 == $this->deep && "create_table" == $this->tag)
		{
			$this->tab[$this->tabName][$this->tag] = '';
		}
		elseif(3 == $this->deep && "prefix" == $this->tag)
		{
			$this->tab[$this->tabName][$this->tag] = '';
		}
		elseif(3 == $this->deep && "table_name" == $this->tag)
		{
			$this->tab[$this->tabName][$this->tag] = '';
		}

	}
	/**
	 * sax end_element handler
	 * @access private
	 * @see sax api
	 */
	function end_element($parser, $tag)
	{

		if($tag == $this->tabName)
		{
			$this->deep = 1;
			$this->tabName = null ;
		}
		if('create_table' == $tag || 'prefix' == $tag || 'content' == $tag || 'table_name' == $tag)
		{
			$this->deep = 2;
			$this->isGeneric = false;
		}
	}

	/**
	 * sax get_data handler
	 * @access private
	 * @see sax api
	 */
	function get_data($parser, $data)
	{

		if(3 == $this->deep && "create_table" == $this->tag)
		{
			$this->tab[$this->tabName][$this->tag] .= $data;
		}
		elseif(3 == $this->deep && "prefix" == $this->tag)
		{
			$this->tab[$this->tabName][$this->tag] = $data;
		}
		elseif(3 == $this->deep && "table_name" == $this->tag)
		{
			$this->tab[$this->tabName][$this->tag] = $data;
		}
		elseif(! is_null($this->tabName) && ! $this->isDataNull)
		{
			$this->tab[$this->tabName]["content"][$this->id][$this->tag] .= $data;
		}
	}
	/**
	 * return the tab contained the parsed data
	 */
	function get_tab()
	{
		return $this->tab;
	}
}
/**
 *
 * parse the file "description.xml" into an array
 *
 * @var array  $tab - xml content
 * @var string $tag - xml tag
 * @var int    $id  - id of the "record"
 * @since 1.8 - 10-avr.-2006
 *
 * @access public
 */
class description_parser
{
	var $tab = array ();
	var $tag;
	var $id;

	/**
	 * sax start_element handler
	 * @access private
	 * @see sax api
	 */
	function start_element($parser, $tag, $attributes)
	{
		$this->tag = $tag;
		if ('id' == $this->tag)
		{
			$this->id = $attributes["id"];
			$this->tab[$this->id] = array ();
			$this->tab[$this->id]['id'] = $this->id;
			$this->tab[$this->id]['title'] = '';
			$this->tab[$this->id]['content'] = '';
			$this->tab[$this->id]['upDate'] = '';
			$this->tab[$this->id]['visibility'] = '';
		}
	}
	/**
	 * sax end_element handler
	 * @access private
	 * @see sax api
	 */
	function end_element($parser, $tag)
	{

	}

	/**
	 * sax get_data handler
	 * @access private
	 * @see sax api
	 */
	function get_data($parser, $data)
	{
		if ('title' == $this->tag)
		{
			$this->tab[$this->id]['title'] .= $data;
		}
		elseif ('content' == $this->tag)
		{
			$this->tab[$this->id]['content'] .= $data;
		}
		elseif ('upDate' == $this->tag)
		{
			$this->tab[$this->id]['upDate'] .= $data;
		}
		elseif ('visibility' == $this->tag)
		{
			$this->tab[$this->id]['visibility'] .= $data;
		}
	}
	/**
	 * return the tab contained the parsed data
	 */
	function get_tab()
	{
		return $this->tab;
	}
}
/**
*
* parse the file "announcement.xml" into an array
*
* @var array  $tab - xml content
* @var string $tag - xml tag
* @var int    $id  - id of the "record"
* @since 1.8 - 10-avr.-2006
*
* @access public
*/
class announcement_parser
{
	var $tab = array ();
	var $tag;
	var $id;

	/**
	 * sax start_element handler
	 * @access private
	 * @see sax api
	 */
	function start_element($parser, $tag, $attributes)
	{
		$this->tag = $tag;
		if ('id' == $this->tag)
		{
			$this->id = $attributes["id"];
			$this->tab[$this->id] = array ();
			$this->tab[$this->id]['id'] = $this->id;
			$this->tab[$this->id]['title'] = '';
			$this->tab[$this->id]['content'] = '';
			$this->tab[$this->id]['time'] = '';
			$this->tab[$this->id]['order'] = '';
			$this->tab[$this->id]['visibility'] = '';
		}
	}
	/**
	 * sax end_element handler
	 * @access private
	 * @see sax api
	 */
	function end_element($parser, $tag)
	{

	}
	/**
	 * sax get_data handler
	 * @access private
	 * @see sax api
	 */
	function get_data($parser, $data)
	{
		if ('title' == $this->tag)
		{
			$this->tab[$this->id]['title'] .= $data;
		}
		elseif ('content' == $this->tag)
		{
			$this->tab[$this->id]['content'] .= $data;
		}
		elseif ('time' == $this->tag)
		{
			$this->tab[$this->id]['time'] .= $data;
		}
		elseif ('rank' == $this->tag)
		{
			$this->tab[$this->id]['order'] .= $data;
		}
		elseif ('visibility' == $this->tag)
		{
			$this->tab[$this->id]['visibility'] .= $data;
		}
	}
	/**
	 * return the tab contained the parsed data
	 */
	function get_tab()
	{
		return $this->tab;
	}
}
/**
 *
 * parse the file "calendar.xml" into an array
 *
 * @var array  $tab - xml content
 * @var string $tag - xml tag
 * @var int    $id  - id of the "record"
 * @since 1.8 - 10-avr.-2006
 *
 * @access public
 */
class calendar_parser
{
	var $tab = array ();
	var $tag;
	var $id;

	/**
	 * sax start_element handler
	 * @access private
	 * @see sax api
	 */
	function start_element($parser, $tag, $attributes)
	{
		$this->tag = $tag;
		if ('id' == $this->tag)
		{
			$this->id = $attributes["id"];
			$this->tab[$this->id] = array ();
			$this->tab[$this->id]['id'] = $this->id;
			$this->tab[$this->id]['title'] = '';
			$this->tab[$this->id]['content'] = '';
			$this->tab[$this->id]['day'] = '';
			$this->tab[$this->id]['hour'] = '';
			$this->tab[$this->id]['lasting'] = '';
			$this->tab[$this->id]['visibility'] = '';
		}
	}
	/**
	 * sax end_element handler
	 * @access private
	 * @see sax api
	 */
	function end_element($parser, $tag)
	{

	}
	/**
	 * sax get_data handler
	 * @access private
	 * @see sax api
	 */
	function get_data($parser, $data)
	{
		if ('title' == $this->tag)
		{
			$this->tab[$this->id]['title'] .= $data;
		}
		elseif ('content' == $this->tag)
		{
			$this->tab[$this->id]['content'] .= $data;
		}
		elseif ('day' == $this->tag)
		{
			$this->tab[$this->id]['day'] .= $data;
		}
		elseif ('hour' == $this->tag)
		{
			$this->tab[$this->id]['hour'] .= $data;
		}
		elseif ('lasting' == $this->tag)
		{
			$this->tab[$this->id]['lasting'] .= $data;
		}
		elseif ('visibility' == $this->tag)
		{
			$this->tab[$this->id]['visibility'] .= $data;
		}
	}
	/**
	 * return the tab contained the parsed data
	 */
	function get_tab()
	{
		return $this->tab;
	}
}
/**
 *
 * parse the file "link.xml" into an array
 *
 * @var array  $tab - xml content
 * @var string $tag - xml tag
 * @var int    $id  - id of the "record"
 * @since 1.8 - 10-avr.-2006
 *
 * @access public
 */
class link_parser
{
	var $tab = array ();
	var $tag;
	var $id;
	var $tabName;

	/**
	 * sax start_element handler
	 * @access private
	 * @see sax api
	 */
	function start_element($parser, $tag, $attributes)
	{
		$this->tag = $tag;
		if ('links' == $this->tag)
		{
			$this->tabName = $tag;
			$this->tab[$this->tabName] = array ();
		}
		if ('resources' == $this->tag)
		{
			$this->tabName = $tag;
			$this->tab[$this->tabName] = array ();
		}
		if ('id' == $this->tag)
		{

			$this->id = $attributes["id"];
			if ('links' == $this->tabName)
			{
				$this->tab[$this->tabName][$this->id] = array ();
				$this->tab[$this->tabName][$this->id]['id'] = $this->id;
				$this->tab[$this->tabName][$this->id]['src_id'] = '';
				$this->tab[$this->tabName][$this->id]['dest_id'] = '';
				$this->tab[$this->tabName][$this->id]['creation_time'] = '';
			}
			if ('resources' == $this->tabName)
			{
				$this->tab[$this->tabName][$this->id] = array ();
				$this->tab[$this->tabName][$this->id]['id'] = $this->id;
				$this->tab[$this->tabName][$this->id]['crl'] = '';
				$this->tab[$this->tabName][$this->id]['title'] = '';
			}

		}
	}
	/**
	 * sax end_element handler
	 * @access private
	 * @see sax api
	 */
	function end_element($parser, $tag)
	{

	}
	/**
	 * sax get_data handler
	 * @access private
	 * @see sax api
	 */
	function get_data($parser, $data)
	{
		if ('links' == $this->tabName)
		{
			if ('src_id' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['src_id'] .= $data;
			}
			elseif ('dest_id' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['dest_id'] .= $data;
			}
			elseif ('creation_time' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['creation_time'] .= $data;
			}
		}
		if ('resources' == $this->tabName)
		{
			if ('crl' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['crl'] .= $data;
			}
			elseif ('title' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['title'] .= $data;
			}
		}
	}
	/**
	 * return the tab contained the parsed data
	 */
	function get_tab()
	{
		return $this->tab;
	}
}
/**
 *
 * parse the file "learnpath.xml" into an array
 *
 * @var array  $tab - xml content
 * @var string $tag - xml tag
 * @var int    $id  - id of the "record"
 * @since 1.8 - 10-avr.-2006
 *
 * @access public
 */
class lp_parser
{
	var $tab = array ();
	var $tag;
	var $id;
	var $tabName;

	/**
	 * sax start_element handler
	 * @access private
	 * @see sax api
	 */
	function start_element($parser, $tag, $attributes)
	{
		$this->tag = $tag;

		if ('asset' == $this->tag)
		{
			$this->tabName = $tag;
			$this->tab[$this->tabName] = array ();
		}
		if ('learnpath' == $this->tag)
		{
			$this->tabName = $tag;
			$this->tab[$this->tabName] = array ();
		}
		if ('module' == $this->tag)
		{
			$this->tabName = $tag;
			$this->tab[$this->tabName] = array ();
		}
		if ('rel_learnpath_module' == $this->tag)
		{
			$this->tabName = $tag;
			$this->tab[$this->tabName] = array ();
		}
		if ('user_module_progress' == $this->tag)
		{
			$this->tabName = $tag;
			$this->tab[$this->tabName] = array ();
		}
		if (('asset_id' == $this->tag) & ('asset' == $this->tabName))
		{
			$this->id = $attributes["asset_id"];
			$this->tab[$this->tabName][$this->id] = array ();
			$this->tab[$this->tabName][$this->id]['asset_id'] = $this->id;
			$this->tab[$this->tabName][$this->id]['module_id'] = '';
			$this->tab[$this->tabName][$this->id]['path'] = '';
			$this->tab[$this->tabName][$this->id]['comment'] = '';

		}
		if (('learnPath_id' == $this->tag) & ('learnpath' == $this->tabName))
		{

			$this->id = $attributes["learnPath_id"];
			$this->tab[$this->tabName][$this->id] = array ();
			$this->tab[$this->tabName][$this->id]['learnPath_id'] = $this->id;
			$this->tab[$this->tabName][$this->id]['name'] = '';
			$this->tab[$this->tabName][$this->id]['comment'] = '';
			$this->tab[$this->tabName][$this->id]['lock'] = '';
			$this->tab[$this->tabName][$this->id]['visibility'] = '';
			$this->tab[$this->tabName][$this->id]['rank'] = '';
		}
		if (('module_id' == $this->tag) & ('module' == $this->tabName))
		{
			$this->id = $attributes["module_id"];
			$this->tab[$this->tabName][$this->id] = array ();
			$this->tab[$this->tabName][$this->id]['module_id'] = $this->id;
			$this->tab[$this->tabName][$this->id]['name'] = '';
			$this->tab[$this->tabName][$this->id]['comment'] = '';
			$this->tab[$this->tabName][$this->id]['accessibility'] = '';
			$this->tab[$this->tabName][$this->id]['startAsset_id'] = '';
			$this->tab[$this->tabName][$this->id]['contentType'] = '';
			$this->tab[$this->tabName][$this->id]['launch_data'] = '';
		}
		if (('learnPath_module_id' == $this->tag) & ('rel_learnpath_module' == $this->tabName))
		{
			$this->id = $attributes["learnPath_module_id"];
			$this->tab[$this->tabName][$this->id] = array ();
			$this->tab[$this->tabName][$this->id]['learnPath_module_id'] = $this->id;
			$this->tab[$this->tabName][$this->id]['learnPath_id'] = '';
			$this->tab[$this->tabName][$this->id]['module_id'] = '';
			$this->tab[$this->tabName][$this->id]['lock'] = '';
			$this->tab[$this->tabName][$this->id]['visibility'] = '';
			$this->tab[$this->tabName][$this->id]['specificComment'] = '';
			$this->tab[$this->tabName][$this->id]['rank'] = '';
			$this->tab[$this->tabName][$this->id]['parent'] = '';
			$this->tab[$this->tabName][$this->id]['raw_to_pass'] = '';
		}
		if (('user_module_progress_id' == $this->tag) & ('user_module_progress' == $this->tabName))
		{
			$this->id = $attributes["user_module_progress_id"];
			$this->tab[$this->tabName][$this->id] = array ();
			$this->tab[$this->tabName][$this->id]['user_module_progress_id'] = $this->id;
			$this->tab[$this->tabName][$this->id]['user_id'] = '';
			$this->tab[$this->tabName][$this->id]['learnPath_module_id'] = '';
			$this->tab[$this->tabName][$this->id]['learnPath_id'] = '';
			$this->tab[$this->tabName][$this->id]['lesson_location'] = '';
			$this->tab[$this->tabName][$this->id]['lesson_status'] = '';
			$this->tab[$this->tabName][$this->id]['entry'] = '';
			$this->tab[$this->tabName][$this->id]['raw'] = '';
			$this->tab[$this->tabName][$this->id]['scoreMin'] = '';
			$this->tab[$this->tabName][$this->id]['scoreMax'] = '';
			$this->tab[$this->tabName][$this->id]['total_time'] = '';
			$this->tab[$this->tabName][$this->id]['session_time'] = '';
			$this->tab[$this->tabName][$this->id]['suspend_data'] = '';
			$this->tab[$this->tabName][$this->id]['credit'] = '';
		}
	}
	/**
	 * sax end_element handler
	 * @access private
	 * @see sax api
	 */
	function end_element($parser, $tag)
	{

	}
	/**
	 * sax get_data handler
	 * @access private
	 * @see sax api
	 */
	function get_data($parser, $data)
	{
		if ('asset' == $this->tabName)
		{
			if ('module_id' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['module_id'] .= $data;
			}
			elseif ('path' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['path'] .= $data;
			}
			elseif ('comment' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['comment'] .= $data;
			}
		}
		if ('learnpath' == $this->tabName)
		{
			if ('name' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['name'] .= $data;
			}
			elseif ('comment' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['comment'] .= $data;
			}
			elseif ('lock' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['lock'] .= $data;
			}
			elseif ('visibility' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['visibility'] .= $data;
			}
			elseif ('rank' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['rank'] .= $data;
			}
		}
		if ('module' == $this->tabName)
		{
			if ('name' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['name'] .= $data;
			}
			elseif ('comment' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['comment'] .= $data;
			}
			elseif ('accessibility' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['accessibility'] .= $data;
			}
			elseif ('startAsset_id' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['startAsset_id'] .= $data;
			}
			elseif ('contentType' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['contentType'] .= $data;
			}
			elseif ('launch_data' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['launch_data'] .= $data;
			}
		}
		if ('rel_learnpath_module' == $this->tabName)
		{
			if ('learnPath_id' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['learnPath_id'] .= $data;
			}
			elseif ('module_id' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['module_id'] .= $data;
			}
			elseif ('lock' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['lock'] .= $data;
			}
			elseif ('visibility' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['visibility'] .= $data;
			}
			elseif ('specificComment' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['specificComment'] .= $data;
			}
			elseif ('rank' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['rank'] .= $data;
			}
			elseif ('parent' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['parent'] .= $data;
			}
			elseif ('raw_to_pass' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['raw_to_pass'] .= $data;
			}
		}

		if ('user_module_progress' == $this->tabName)
		{
			if ('user_id' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['user_id'] .= $data;
			}
			elseif ('learnPath_module_id' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['learnPath_module_id'] .= $data;
			}
			elseif ('learnPath_id' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['learnPath_id'] .= $data;
			}
			elseif ('lesson_location' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['lesson_location'] .= $data;
			}
			elseif ('lesson_status' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['lesson_status'] .= $data;
			}
			elseif ('entry' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['entry'] .= $data;
			}
			elseif ('raw' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['raw'] .= $data;
			}
			elseif ('scoreMin' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['scoreMin'] .= $data;
			}
			elseif ('scoreMax' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['scoreMax'] .= $data;
			}
			elseif ('total_time' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['total_time'] .= $data;
			}
			elseif ('session_time' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['session_time'] .= $data;
			}
			elseif ('suspend_data' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['suspend_data'] .= $data;
			}
			elseif ('credit' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['credit'] .= $data;
			}
		}
	}
	/**
	 * return the tab contained the parsed data
	 */
	function get_tab()
	{
		return $this->tab;
	}
}
/**
 *
 * parse the file "quiz.xml" into an array
 *
 * @var array  $tab - xml content
 * @var string $tag - xml tag
 * @var int    $id  - id of the "record"
 * @since 1.8 - 10-avr.-2006
 *
 * @access public
 */
class quiz_parser
{
	var $tab = array ();
	var $tag;
	var $id;
	var $tabName;
	var $cpt;

	/**
	 * sax start_element handler
	 * @access private
	 * @see sax api
	 */
	function start_element($parser, $tag, $attributes)
	{
		$this->tag = $tag;

		if ('answer' == $this->tag)
		{
			$this->tabName = $tag;
			$this->tab[$this->tabName] = array ();
		}
		if ('questions' == $this->tag)
		{

			$this->tabName = $tag;
			$this->tab[$this->tabName] = array ();

		}
		if ('rel_test_question' == $this->tag)
		{
			$this->tabName = $tag;
			$this->tab[$this->tabName] = array ();
		}
		if ('test' == $this->tag)
		{
			$this->tabName = $tag;
			$this->tab[$this->tabName] = array ();
		}
		if (('id' == $this->tag) & ('answer' == $this->tabName))
		{
			$this->id = $attributes["id"];
			$this->tab[$this->tabName][$this->id] = array ();
			$this->tab[$this->tabName][$this->id]['id'] = $this->id;
			$this->tab[$this->tabName][$this->id]['question_id'] = '';
			$this->tab[$this->tabName][$this->id]['reponse'] = '';
			$this->tab[$this->tabName][$this->id]['correct'] = '';
			$this->tab[$this->tabName][$this->id]['comment'] = '';
			$this->tab[$this->tabName][$this->id]['ponderation'] = '';
			$this->tab[$this->tabName][$this->id]['r_position'] = '';
		}
		if (('id' == $this->tag) & ('questions' == $this->tabName))
		{
			$this->id = $attributes["id"];
			$this->tab[$this->tabName][$this->id] = array ();
			$this->tab[$this->tabName][$this->id]['id'] = $this->id;
			$this->tab[$this->tabName][$this->id]['question'] = '';
			$this->tab[$this->tabName][$this->id]['description'] = '';
			$this->tab[$this->tabName][$this->id]['ponderation'] = '';
			$this->tab[$this->tabName][$this->id]['q_position'] = '';
			$this->tab[$this->tabName][$this->id]['type'] = '';
			$this->tab[$this->tabName][$this->id]['attached_file'] = '';
		}
		if ('question_id' == $this->tag & ('rel_test_question' == $this->tabName))
		{
			$this->cpt = $this->cpt++;
			$this->tab[$this->tabName][$this->cpt]['question_id'] = '';
			$this->tab[$this->tabName][$this->cpt]['exercice_id'] = '';
		}
		if (('id' == $this->tag) & ('test' == $this->tabName))
		{
			$this->id = $attributes["id"];
			$this->tab[$this->tabName][$this->id] = array ();
			$this->tab[$this->tabName][$this->id]['id'] = $this->id;
			$this->tab[$this->tabName][$this->id]['titre'] = '';
			$this->tab[$this->tabName][$this->id]['description'] = '';
			$this->tab[$this->tabName][$this->id]['type'] = '';
			$this->tab[$this->tabName][$this->id]['random'] = '';
			$this->tab[$this->tabName][$this->id]['active'] = '';
			$this->tab[$this->tabName][$this->id]['max_time'] = '';
			$this->tab[$this->tabName][$this->id]['max_attempt'] = '';
			$this->tab[$this->tabName][$this->id]['show_answer'] = '';
			$this->tab[$this->tabName][$this->id]['anonymous_attempts'] = '';
			$this->tab[$this->tabName][$this->id]['start_date'] = '';
			$this->tab[$this->tabName][$this->id]['end_date'] = '';
		}
	}
	/**
	 * sax end_element handler
	 * @access private
	 * @see sax api
	 */
	function end_element($parser, $tag)
	{

	}
	/**
	 * sax get_data handler
	 * @access private
	 * @see sax api
	 */
	function get_data($parser, $data)
	{

		if ('answer' == $this->tabName)
		{
			if ('id' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['id'] .= $data;
			}
			elseif ('question_id' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['question_id'] .= $data;
			}
			elseif ('reponse' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['reponse'] .= $data;
			}
			elseif ('correct' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['correct'] .= $data;
			}
			elseif ('comment' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['comment'] .= $data;
			}
			elseif ('ponderation' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['ponderation'] .= $data;
			}
			elseif ('r_position' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['r_position'] .= $data;
			}
		}
		if ('questions' == $this->tabName)
		{
			if ('question' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['question'] .= $data;
			}
			elseif ('description' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['description'] .= $data;
			}
			elseif ('ponderation' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['ponderation'] .= $data;
			} else
			if ('q_position' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['q_position'] .= $data;
			}
			elseif ('type' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['type'] .= $data;
			}
			elseif ('attached_file' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['attached_file'] .= $data;
			}
		}
		if ('rel_test_question' == $this->tabName)
		{
			if ('question_id' == $this->tag)
			{
				$this->tab[$this->tabName][$this->cpt]['question_id'] .= $data;
			}
			elseif ('exercice_id' == $this->tag)
			{
				$this->tab[$this->tabName][$this->cpt]['exercice_id'] .= $data;
			}
		}
		if ('test' == $this->tabName)
		{
			if ('title' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['titre'] .= $data;
			}
			elseif ('description' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['description'] .= $data;
			}
			elseif ('type' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['type'] .= $data;
			}
			elseif ('random' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['random'] .= $data;
			}
			elseif ('active' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['active'] .= $data;
			}
			elseif ('max_time' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['max_time'] .= $data;
			}
			elseif ('max_attempt' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['max_attempt'] .= $data;
			}
			elseif ('show_answer' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['show_answer'] .= $data;
			}
			elseif ('anonymous_attempts' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['anonymous_attempts'] .= $data;
			}
			elseif ('start_date' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['start_date'] .= $data;
			}
			elseif ('end_date' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['end_date'] .= $data;
			}
		}
	}
	/**
	 * return the tab contained the parsed data
	 */
	function get_tab()
	{
		return $this->tab;
	}
}
/**
 *
 * parse the file "tool.xml" into an array
 *
 * @var array  $tab     - xml content
 * @var string $tag     - xml tag
 * @var int    $id 		- id of the "record"
 * @var string $tabName - contain the sub array index name
 * @since 1.8 - 10-avr.-2006
 *
 * @access public
 */
class tool_parser
{
	var $tab = array ();
	var $tag;
	var $id;
	var $tabName;

	/**
	 * sax start_element handler
	 * @access private
	 * @see sax api
	 */
	function start_element($parser, $tag, $attributes)
	{
		$this->tag = $tag;

		if ('tool_intro' == $this->tag)
		{
			$this->tabName = $tag;
			$this->tab[$this->tabName] = array ();
		}
		if ('tool_list' == $this->tag)
		{
			$this->tabName = $tag;
			$this->tab[$this->tabName] = array ();
		}

		if (('id' == $this->tag) & ('tool_intro' == $this->tabName))
		{
			$this->id = $attributes["id"];
			$this->tab[$this->tabName][$this->id] = array ();
			$this->tab[$this->tabName][$this->id]['id'] = $this->id;
			$this->tab[$this->tabName][$this->id]['tool_id'] = '';
			$this->tab[$this->tabName][$this->id]['title'] = '';
			$this->tab[$this->tabName][$this->id]['display_date'] = '';
			$this->tab[$this->tabName][$this->id]['content'] = '';
			$this->tab[$this->tabName][$this->id]['rank'] = '';
			$this->tab[$this->tabName][$this->id]['visibility'] = '';
		}
		if (('id' == $this->tag) & ('tool_list' == $this->tabName))
		{

			$this->id = $attributes["id"];
			$this->tab[$this->tabName][$this->id] = array ();
			$this->tab[$this->tabName][$this->id]['id'] = $this->id;
			$this->tab[$this->tabName][$this->id]['tool_id'] = '';
			$this->tab[$this->tabName][$this->id]['rank'] = '';
			$this->tab[$this->tabName][$this->id]['access'] = '';
			$this->tab[$this->tabName][$this->id]['script_url'] = NULL;
			$this->tab[$this->tabName][$this->id]['script_name'] = NULL;
			$this->tab[$this->tabName][$this->id]['addedTool'] = '';
		}
	}
	/**
	 * sax end_element handler
	 * @access private
	 * @see sax api
	 */
	function end_element($parser, $tag)
	{

	}
	/**
	 * sax get_data handler
	 * @access private
	 * @see sax api
	 */
	function get_data($parser, $data)
	{

		if ('tool_intro' == $this->tabName)
		{
			if ('tool_id' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['tool_id'] .= $data;
			}
			elseif ('title' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['title'] .= $data;
			}
			elseif ('display_date' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['display_date'] .= $data;
			}
			elseif ('content' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['content'] .= $data;
			}
			elseif ('rank' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['rank'] .= $data;
			}
			elseif ('visibility' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['visibility'] .= $data;
			}
		}
		if ('tool_list' == $this->tabName)
		{
			if ('tool_id' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['tool_id'] .= $data;
			}
			elseif ('rank' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['rank'] .= $data;
			}
			elseif ('access' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['access'] .= $data;
			}
			elseif ('script_url' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['script_url'] .= $data;
			}
			elseif ('script_name' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['script_name'] .= $data;
			}
			elseif ('addedTool' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['addedTool'] .= $data;
			}
		}
	}
	/**
	 * return the tab contained the parsed data
	 */
	function get_tab()
	{
		return $this->tab;
	}
}
/**
 *
 * parse the file "document.xml" into an array
 *
 * @var array  $tab     - xml content
 * @var string $tag     - xml tag
 * @var int    $id 		- id of the "record"
 * @since 1.8 - 10-avr.-2006
 *
 * @access public
 */

class document_parser
{
	var $tab = array ();
	var $tag;
	var $id;

	/**
	 * sax start_element handler
	 * @access private
	 * @see sax api
	 */
	function start_element($parser, $tag, $attributes)
	{
		$this->tag = $tag;
		if ('id' == $this->tag)
		{
			$this->id = $attributes["id"];
			$this->tab[$this->id] = array ();
			$this->tab[$this->id]['id'] = $this->id;
			$this->tab[$this->id]['path'] = '';
			$this->tab[$this->id]['visibility'] = '';
			$this->tab[$this->id]['comment'] = '';
		}
	}
	/**
	 * sax end_element handler
	 * @access private
	 * @see sax api
	 */
	function end_element($parser, $tag)
	{

	}
	/**
	 * sax get_data handler
	 * @access private
	 * @see sax api
	 */
	function get_data($parser, $data)
	{
		if ('path' == $this->tag)
		{
			$this->tab[$this->id]['path'] .= $data;
		} elseif ('visibility' == $this->tag)
		{
			$this->tab[$this->id]['visibility'] .= $data;
		}
		elseif ('comment' == $this->tag)
		{
			$this->tab[$this->id]['comment'] .= $data;
		}
	}
	/**
	 * return the tab contained the parsed data
	 */
	function get_tab()
	{
		return $this->tab;
	}
}
/**
 *
 * parse the file "group.xml" into an array
 *
 * @var array  $tab     - xml content
 * @var string $tag     - xml tag
 * @var int    $id 		- id of the "record"
 * @var string $tabName - contain the sub array index name
 * @since 1.8 - 10-avr.-2006
 *
 * @access public
 */

class group_parser
{
	var $tab = array ();
	var $tag;
	var $id;
	var $tabName;

	/**
	 * sax start_element handler
	 * @access private
	 * @see sax api
	 */
	function start_element($parser, $tag, $attributes)
	{
		$this->tag = $tag;
		if (('group_property' == $tag))
		{
			$this->tabName = $tag;
		}
		if (('group_rel_team_user' == $tag))
		{
			$this->tabName = $tag;
		}
		if (('group_team' == $tag))
		{
			$this->tabName = $tag;
		}
		if (('id' == $this->tag) & ('group_property' == $this->tabName))
		{
			$this->id = $attributes["id"];
			$this->tab[$this->tabName][$this->id] = array ();
			$this->tab[$this->tabName][$this->id]['id'] = $this->id;
			$this->tab[$this->tabName][$this->id]['self_registration'] = '';
			$this->tab[$this->tabName][$this->id]['nbGroupPerUser'] = '';
			$this->tab[$this->tabName][$this->id]['private'] = '';
			$this->tab[$this->tabName][$this->id]['forum'] = '';
			$this->tab[$this->tabName][$this->id]['document'] = '';
			$this->tab[$this->tabName][$this->id]['wiki'] = '';
			$this->tab[$this->tabName][$this->id]['chat'] = '';
		}
		if (('id' == $this->tag) & ('group_rel_team_user' == $this->tabName))
		{
			$this->id = $attributes["id"];
			$this->tab[$this->tabName][$this->id] = array ();
			$this->tab[$this->tabName][$this->id]['id'] = $this->id;
			$this->tab[$this->tabName][$this->id]['user'] = '';
			$this->tab[$this->tabName][$this->id]['team'] = '';
			$this->tab[$this->tabName][$this->id]['status'] = '';
			$this->tab[$this->tabName][$this->id]['role'] = '';
		}
		if (('id' == $this->tag) & ('group_team' == $this->tabName))
		{
			$this->id = $attributes["id"];
			$this->tab[$this->tabName][$this->id] = array ();
			$this->tab[$this->tabName][$this->id]['id'] = $this->id;
			$this->tab[$this->tabName][$this->id]['name'] = '';
			$this->tab[$this->tabName][$this->id]['description'] = NULL;
			$this->tab[$this->tabName][$this->id]['tutor'] = NULL;
			$this->tab[$this->tabName][$this->id]['maxStudent'] = '';
			$this->tab[$this->tabName][$this->id]['secretDirectory'] = '';
		}
	}
	/**
	 * sax end_element handler
	 * @access private
	 * @see sax api
	 */
	function end_element($parser, $tag)
	{

	}
	/**
	 * sax get_data handler
	 * @access private
	 * @see sax api
	 */
	function get_data($parser, $data)
	{
		if ('group_property' == $this->tabName)
		{
			if ('self_registration' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['self_registration'] .= $data;
			}
			elseif ('nbGroupPerUser' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['nbGroupPerUser'] .= $data;
			}
			elseif ('private' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['private'] .= $data;
			}
			elseif ('forum' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['forum'] .= $data;
			}
			elseif ('document' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['document'] .= $data;
			}
			elseif ('wiki' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['wiki'] .= $data;
			}
			elseif ('chat' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['chat'] .= $data;
			}
		}
		if ('group_rel_team_user' == $this->tabName)
		{
			if ('user' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['user'] .= $data;
			}
			elseif ('team' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['team'] .= $data;
			}
			elseif ('status' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['status'] .= $data;
			}
			elseif ('role' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['role'] .= $data;
			}
		}
		if ('group_team' == $this->tabName)
		{
			if ('name' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['name'] .= $data;
			}
			elseif ('description' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['description'] .= $data;
			}
			elseif ('tutor' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['tutor'] .= $data;
			}
			elseif ('maxStudent' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['maxStudent'] .= $data;
			}
			elseif ('secretDirectory' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['secretDirectory'] .= $data;
			}
		}
	}
	/**
	 * return the tab contained the parsed data
	 */
	function get_tab()
	{
		return $this->tab;
	}
}
/**
 *
 * parse the file "userinfo.xml" into an array
 *
 * @var array  $tab     - xml content
 * @var string $tag     - xml tag
 * @var int    $id 		- id of the "record"
 * @var string $tabName - contain the sub array index name
 * @since 1.8 - 10-avr.-2006
 *
 * @access public
 */

class userinfo_parser
{
	var $tab = array ();
	var $tag;
	var $id;
	var $tabName;

	/**
	 * sax start_element handler
	 * @access private
	 * @see sax api
	 */
	function start_element($parser, $tag, $attributes)
	{
		$this->tag = $tag;
		if ('userinfo_def' == $tag)
		{
			$this->tabName = $tag;
		}
		if ('userinfo_content' == $tag)
		{
			$this->tabName = $tag;
		}
		if ('id' == $this->tag && 'userinfo_def' == $this->tabName)
		{
			$this->id = $attributes["id"];
			$this->tab[$this->tabName][$this->id] = array ();
			$this->tab[$this->tabName][$this->id]['id'] = $this->id;
			$this->tab[$this->tabName][$this->id]['title'] = '';
			$this->tab[$this->tabName][$this->id]['comment'] = '';
			$this->tab[$this->tabName][$this->id]['nbLine'] = '';
			$this->tab[$this->tabName][$this->id]['rank'] = '';
		}
		if ('id' == $this->tag && 'userinfo_content' == $this->tabName)
		{
			$this->id = $attributes["id"];
			$this->tab[$this->tabName][$this->id] = array ();
			$this->tab[$this->tabName][$this->id]['id'] = $this->id;
			$this->tab[$this->tabName][$this->id]['user_id'] = '';
			$this->tab[$this->tabName][$this->id]['def_id'] = '';
			$this->tab[$this->tabName][$this->id]['ed_ip'] = '';
			$this->tab[$this->tabName][$this->id]['ed_date'] = '';
			$this->tab[$this->tabName][$this->id]['content'] = '';
		}
	}
	/**
	 * sax end_element handler
	 * @access private
	 * @see sax api
	 */
	function end_element($parser, $tag)
	{

	}
	/**
	 * sax get_data handler
	 * @access private
	 * @see sax api
	 */
	function get_data($parser, $data)
	{
		if ('userinfo_def' == $this->tabName)
		{
			if ('title' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['title'] .= $data;
			}
			elseif ('comment' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['comment'] .= $data;
			}
			elseif ('nbLine' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['nbLine'] .= $data;
			}
			elseif ('rank' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['rank'] .= $data;
			}
		}
		if ('userinfo_content' == $this->tabName)
		{
			if ('user_id' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['user_id'] .= $data;
			}
			elseif ('def_id' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['def_id'] .= $data;
			}
			elseif ('ed_ip' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['ed_ip'] .= $data;
			}
			elseif ('ed_date' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['ed_date'] .= $data;
			}
			elseif ('content' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['content'] .= $data;
			}
		}

	}
	/**
	 * return the tab contained the parsed data
	 */
	function get_tab()
	{
		return $this->tab;
	}
}
/**
 *
 * parse the file "bb.xml" into an array
 *
 * @var array  $tab     - xml content
 * @var string $tag     - xml tag
 * @var int    $id 		- id of the "record"
 * @var string $tabName - contain the sub array index name
 * @since 1.8 - 10-avr.-2006
 *
 * @access public
 */

class bb_parser
{
	var $tab = array ();
	var $tag;
	var $id;
	var $tabName;

	/**
	 * sax start_element handler
	 * @access private
	 * @see sax api
	 */
	function start_element($parser, $tag, $attributes)
	{
		$this->tag = $tag;
		if ('bb_categories' == $tag)
		{
			$this->tabName = $tag;
		}
		if ('bb_forums' == $tag)
		{
			$this->tabName = $tag;
		}
		if ('bb_posts' == $tag)
		{
			$this->tabName = $tag;
		}
		if ('bb_posts_text' == $tag)
		{
			$this->tabName = $tag;
		}
		if ('bb_priv_msgs' == $tag)
		{
			$this->tabName = $tag;
		}
		if ('bb_rel_topic_userstonotify' == $tag)
		{
			$this->tabName = $tag;
		}
		if ('bb_topics' == $tag)
		{
			$this->tabName = $tag;
		}
		if ('bb_users' == $tag)
		{
			$this->tabName = $tag;
		}
		if ('cat_id' == $this->tag && 'bb_categories' == $this->tabName)
		{

			$this->id = $attributes["cat_id"];
			$this->tab[$this->tabName][$this->id] = array ();
			$this->tab[$this->tabName][$this->id]['cat_id'] = $this->id;
			$this->tab[$this->tabName][$this->id]['cat_title'] = '';
			$this->tab[$this->tabName][$this->id]['cat_order'] = '';
		}
		if ('forum_id' == $this->tag && 'bb_forums' == $this->tabName)
		{
			$this->id = $attributes["forum_id"];
			$this->tab[$this->tabName][$this->id] = array ();
			$this->tab[$this->tabName][$this->id]['forum_id'] = $this->id;
			$this->tab[$this->tabName][$this->id]['group_id'] = null;
			$this->tab[$this->tabName][$this->id]['forum_name'] = '';
			$this->tab[$this->tabName][$this->id]['forum_desc'] = '';
			$this->tab[$this->tabName][$this->id]['forum_access'] = '';
			$this->tab[$this->tabName][$this->id]['forum_moderator'] = '';
			$this->tab[$this->tabName][$this->id]['forum_topics'] = '';
			$this->tab[$this->tabName][$this->id]['forum_posts'] = '';
			$this->tab[$this->tabName][$this->id]['forum_last_post_id'] = '';
			$this->tab[$this->tabName][$this->id]['cat_id'] = '';
			$this->tab[$this->tabName][$this->id]['forum_type'] = '';
			$this->tab[$this->tabName][$this->id]['forum_order'] = '';
		}
		if ('post_id' == $this->tag && 'bb_posts' == $this->tabName)
		{
			$this->id = $attributes["post_id"];
			$this->tab[$this->tabName][$this->id] = array ();
			$this->tab[$this->tabName][$this->id]['post_id'] = $this->id;
			$this->tab[$this->tabName][$this->id]['topic_id'] = '';
			$this->tab[$this->tabName][$this->id]['forum_id'] = '';
			$this->tab[$this->tabName][$this->id]['poster_id'] = '';
			$this->tab[$this->tabName][$this->id]['post_time'] = '';
			$this->tab[$this->tabName][$this->id]['poster_ip'] = '';
			$this->tab[$this->tabName][$this->id]['firstname'] = '';
			$this->tab[$this->tabName][$this->id]['lastname'] = '';
		}
		if ('post_id' == $this->tag && 'bb_posts_text' == $this->tabName)
		{
			$this->id = $attributes["post_id"];
			$this->tab[$this->tabName][$this->id] = array ();
			$this->tab[$this->tabName][$this->id]['post_id'] = $this->id;
			$this->tab[$this->tabName][$this->id]['post_text'] = '';
		}
		if ('msg_id' == $this->tag && 'bb_priv_msgs' == $this->tabName)
		{
			$this->id = $attributes["msg_id"];
			$this->tab[$this->tabName][$this->id] = array ();
			$this->tab[$this->tabName][$this->id]['msg_id'] = $this->id;
			$this->tab[$this->tabName][$this->id]['from_userid'] = '';
			$this->tab[$this->tabName][$this->id]['to_userid'] = '';
			$this->tab[$this->tabName][$this->id]['msg_time'] = '';
			$this->tab[$this->tabName][$this->id]['poster_ip'] = '';
			$this->tab[$this->tabName][$this->id]['msg_status'] = '';
			$this->tab[$this->tabName][$this->id]['msg_text'] = '';
		}
		if ('notify_id' == $this->tag && 'bb_rel_topic_userstonotify' == $this->tabName)
		{
			$this->id = $attributes["notify_id"];
			$this->tab[$this->tabName][$this->id] = array ();
			$this->tab[$this->tabName][$this->id]['notify_id'] = $this->id;
			$this->tab[$this->tabName][$this->id]['user_id'] = '';
			$this->tab[$this->tabName][$this->id]['topic_id'] = '';
		}
		if ('topic_id' == $this->tag && 'bb_topics' == $this->tabName)
		{
			$this->id = $attributes["topic_id"];
			$this->tab[$this->tabName][$this->id] = array ();
			$this->tab[$this->tabName][$this->id]['topic_id'] = $this->id;
			$this->tab[$this->tabName][$this->id]['topic_title'] = '';
			$this->tab[$this->tabName][$this->id]['topic_poster'] = '';
			$this->tab[$this->tabName][$this->id]['topic_time'] = '';
			$this->tab[$this->tabName][$this->id]['topic_views'] = '';
			$this->tab[$this->tabName][$this->id]['topic_replies'] = '';
			$this->tab[$this->tabName][$this->id]['topic_last_post_id'] = '';
			$this->tab[$this->tabName][$this->id]['forum_id'] = '';
			$this->tab[$this->tabName][$this->id]['topic_status'] = '';
			$this->tab[$this->tabName][$this->id]['topic_notify'] = '';
			$this->tab[$this->tabName][$this->id]['lastname'] = '';
			$this->tab[$this->tabName][$this->id]['firstname'] = '';
		}
		if ('user_id' == $this->tag && 'bb_users' == $this->tabName)
		{
			$this->id = $attributes["user_id"];
			$this->tab[$this->tabName][$this->id] = array ();
			$this->tab[$this->tabName][$this->id]['user_id'] = $this->id;
			$this->tab[$this->tabName][$this->id]['username'] = '';
			$this->tab[$this->tabName][$this->id]['user_regdate'] = '';
			$this->tab[$this->tabName][$this->id]['user_password'] = '';
			$this->tab[$this->tabName][$this->id]['user_email'] = '';
			$this->tab[$this->tabName][$this->id]['user_icq'] = '';
			$this->tab[$this->tabName][$this->id]['user_website'] = '';
			$this->tab[$this->tabName][$this->id]['user_occ'] = '';
			$this->tab[$this->tabName][$this->id]['user_from'] = '';
			$this->tab[$this->tabName][$this->id]['user_intrest'] = '';
			$this->tab[$this->tabName][$this->id]['user_sig'] = '';
			$this->tab[$this->tabName][$this->id]['user_viewemail'] = '';
			$this->tab[$this->tabName][$this->id]['user_theme'] = '';
			$this->tab[$this->tabName][$this->id]['user_aim'] = '';
			$this->tab[$this->tabName][$this->id]['user_yim'] = '';
			$this->tab[$this->tabName][$this->id]['user_msnm'] = '';
			$this->tab[$this->tabName][$this->id]['user_posts'] = '';
			$this->tab[$this->tabName][$this->id]['user_attachsig'] = '';
			$this->tab[$this->tabName][$this->id]['user_desmile'] = '';
			$this->tab[$this->tabName][$this->id]['user_html'] = '';
			$this->tab[$this->tabName][$this->id]['user_bbcode'] = '';
			$this->tab[$this->tabName][$this->id]['user_rank'] = '';
			$this->tab[$this->tabName][$this->id]['user_level'] = '';
			$this->tab[$this->tabName][$this->id]['user_lang'] = '';
			$this->tab[$this->tabName][$this->id]['user_actkey'] = '';
			$this->tab[$this->tabName][$this->id]['user_newpasswd'] = '';
		}

	}
	/**
	 * sax end_element handler
	 * @access private
	 * @see sax api
	 */
	function end_element($parser, $tag)
	{

	}
	/**
	 * sax get_data handler
	 * @access private
	 * @see sax api
	 */
	function get_data($parser, $data)
	{
		if ('bb_categories' == $this->tabName)
		{
			if ("cat_id" == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['cat_id'] .= $data;
			}
			elseif ("cat_title" == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['cat_title'] .= $data;
			}
			elseif ("cat_order" == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['cat_order'] .= $data;
			}
		}
		if ('bb_forums' == $this->tabName)
		{
			if ("group_id" == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['group_id'] .= $data;
			}
			elseif ("forum_name" == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['forum_name'] .= $data;
			}
			elseif ("forum_desc" == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['forum_desc'] .= $data;
			}
			elseif ("forum_access" == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['forum_access'] .= $data;
			}
			elseif ("forum_moderator" == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['forum_moderator'] .= $data;
			}
			elseif ("forum_topics" == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['forum_topics'] .= $data;
			}
			elseif ("forum_posts" == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['forum_posts'] .= $data;
			}
			elseif ("forum_last_post_id" == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['forum_last_post_id'] .= $data;
			}
			elseif ("cat_id" == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['cat_id'] .= $data;
			}
			elseif ("forum_type" == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['forum_type'] .= $data;
			}
			elseif ("forum_order" == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['forum_order'] .= $data;
			}
		}
		if ('bb_posts' == $this->tabName)
		{
			if ("topic_id" == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['topic_id'] .= $data;
			}
			elseif ("forum_id" == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['forum_id'] .= $data;
			}
			elseif ("poster_id" == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['poster_id'] .= $data;
			}
			elseif ("post_time" == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['post_time'] .= $data;
			}
			elseif ("poster_ip" == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['poster_ip'] .= $data;
			}
			elseif ("lastname" == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['lastname'] .= $data;
			}
			elseif ("firstname" == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['firstname'] .= $data;
			}
		}
		if ('bb_posts_text' == $this->tabName)
		{
			if ("post_text" == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['post_text'] .= $data;
			}
		}
		if ('bb_priv_msgs' == $this->tabName)
		{
			if ("from_userid" == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['from_userid'] .= $data;
			}
			elseif ("to_userid" == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['to_userid'] .= $data;
			}
			elseif ("msg_time" == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['msg_time'] .= $data;
			}
			elseif ("poster_ip" == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['poster_ip'] .= $data;
			}
			elseif ("msg_status" == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['msg_status'] .= $data;
			}
			elseif ("msg_text" == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['msg_text'] .= $data;
			}
		}
		if ('bb_rel_topic_userstonotify' == $this->tabName)
		{
			if ("user_id" == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['user_id'] .= $data;
			}
			elseif ("topic_id" == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['topic_id'] .= $data;
			}
		}
		if ('bb_topics' == $this->tabName)
		{
			if ("topic_title" == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['topic_title'] .= $data;
			}
			elseif ("topic_poster" == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['topic_poster'] .= $data;
			}
			elseif ("topic_time" == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['topic_time'] .= $data;
			}
			elseif ("topic_views" == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['topic_views'] .= $data;
			}
			elseif ("topic_replies" == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['topic_replies'] .= $data;
			}
			elseif ("topic_last_post_id" == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['topic_last_post_id'] .= $data;
			}
			elseif ("forum_id" == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['forum_id'] .= $data;
			}
			elseif ("topic_status" == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['topic_status'] .= $data;
			}
			elseif ("topic_notify" == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['topic_notify'] .= $data;
			}
			elseif ("firstname" == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['firstname'] .= $data;
			}
			elseif ("lastname" == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['lastname'] .= $data;
			}
		}
		if ('bb_users' == $this->tabName)
		{
			if ("username" == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['username'] .= $data;
			}
			elseif ("user_regdate" == $this->tag)
			{
					$this->tab[$this->tabName][$this->id]['user_regdate'] .= $data;
			}
			elseif ("user_password" == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['user_password'] .= $data;
			}
			elseif ("user_email" == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['user_email'] .= $data;
			}
			elseif ("user_icq" == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['user_icq'] .= $data;
			}
			elseif ("user_website" == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['user_website'] .= $data;
			}
			elseif ("user_occ" == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['user_occ'] .= $data;
			}
			elseif ("user_from" == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['user_from'] .= $data;
			}
			elseif ("user_intrest" == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['user_intrest'] .= $data;
			}
			elseif ("user_sig" == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['user_sig'] .= $data;
			}
			elseif ("user_viewemail" == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['user_viewemail'] .= $data;
			}
			elseif ("user_theme" == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['user_theme'] .= $data;
			}
			elseif ("user_aim" == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['user_aim'] .= $data;
			}
			elseif ("user_yim" == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['user_yim'] .= $data;
			}
			elseif ("user_msnm" == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['user_msnm'] .= $data;
			}
			elseif ("user_posts" == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['user_posts'] .= $data;
			}
			elseif ("user_attachsig" == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['user_attachsig'] .= $data;
			}
			elseif ("user_desmile" == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['user_desmile'] .= $data;
			}
			elseif ("user_html" == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['user_html'] .= $data;
			}
			elseif ("user_bbcode" == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['user_bbcode'] .= $data;
			}
			elseif ("user_rank" == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['user_rank'] .= $data;
			}
			elseif ("user_level" == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['user_level'] .= $data;
			}
			elseif ("user_lang" == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['user_lang'] .= $data;
			}
			elseif ("user_actkey" == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['user_actkey'] .= $data;
			}
			elseif ("user_newpasswd" == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]['user_newpasswd'] .= $data;
			}
		}

	}
	/**
	 * return the tab contained the parsed data
	 */
	function get_tab()
	{
		return $this->tab;
	}
}


/**
 *
 * parse the file "manifest.xml" into an array
 *
 * @var array  $tab     - xml content
 * @var string $tag     - xml tag
 * @var int    $id 		- id of the "record"
 * @since 1.8 - 10-avr.-2006
 *
 * @access public
 */
class manifest_parser
{
	var $tab = array ();
	var $tag;
	var $id;
	var $cpt = 0;
	/**
	 * sax start_element handler
	 * @access private
	 * @see sax api
	 */
	function start_element($parser, $tag, $attributes)
	{
		$this->tag = $tag;
		if ('course' == $this->tag)
		{
			$this->tabName = $this->tag;
		}
		if ('users' == $this->tag)
		{
			$this->tabName = $this->tag;
		}
		if ('group_team' == $this->tag)
		{
			$this->tabName = $this->tag;
		}
		if ('group_property' == $this->tag)
		{
			$this->tabName = $this->tag;
		}
		if ('toolsInfo' == $this->tag)
		{
			$this->tabName = $this->tag;
		}
		if ('claro_info' == $this->tag)
		{
			$this->tabName = $this->tab;
			$this->tab[$this->tabName]['plateform_id'] = "";
			$this->tab[$this->tabName]['new_version'] = "";
			$this->tab[$this->tabName]['new_version_branch'] = "";
			$this->tab[$this->tabName]['clarolineVersion'] = "";
			$this->tab[$this->tabName]['versionDb'] = "";
		}
		if ('user_id' == $this->tag && 'users' == $this->tabName)
		{
			$this->id = $attributes['user_id'];
			$this->tab[$this->tabName][$this->id]['user_id'] = $this->id;
			$this->tab[$this->tabName][$this->id]['username'] = "";
			$this->tab[$this->tabName][$this->id]['lastName'] = "";
			$this->tab[$this->tabName][$this->id]['firstName'] = "";
		}
		if ('id' == $this->tag && 'group_team' == $this->tabName)
		{
			$this->id = $attributes['id'];
			$this->tab[$this->tabName][$this->id]['id'] = $this->id;
			$this->tab[$this->tabName][$this->id]['name'] = "";
		}
		if ('id' == $this->tag && 'group_property' == $this->tabName)
		{
			$this->id = $attributes['id'];
			$this->tab[$this->tabName][$this->id]['id'] = $this->id;
			$this->tab[$this->tabName][$this->id]['self_registration'] = "";
			$this->tab[$this->tabName][$this->id]['nbGroupPerUser'] = "";
			$this->tab[$this->tabName][$this->id]['private'] = "";
			$this->tab[$this->tabName][$this->id]['forum'] = "";
			$this->tab[$this->tabName][$this->id]['document'] = "";
			$this->tab[$this->tabName][$this->id]['wiki'] = "";
			$this->tab[$this->tabName][$this->id]['chat'] = "";
		}
		if('group' == $this->tag && 'toolsInfo' == $this->tabName)
		{
			$this->id = $attributes['id'];
			$this->tab[$this->tabName][$this->id] = array();

		}
		if ('cours_id' == $this->tag && 'course' == $this->tabName)
		{
			$this->tab[$this->tabName]['cours_id'] = $this->id;
			$this->tab[$this->tabName]['code'] = "";
			$this->tab[$this->tabName]['fake_code'] = "";
			$this->tab[$this->tabName]['directory'] = "";
			$this->tab[$this->tabName]['dbName'] = "";
			$this->tab[$this->tabName]['languageCourse'] = "";
			$this->tab[$this->tabName]['intitule'] = "";
			$this->tab[$this->tabName]['faculte'] = "";
			$this->tab[$this->tabName]['enrollment_key'] = "";
			$this->tab[$this->tabName]['titulaires'] = "";
			$this->tab[$this->tabName]['email'] = "";
			$this->tab[$this->tabName]['departmentUrlName'] = "";
			$this->tab[$this->tabName]['departmentUrl'] = "";
			$this->tab[$this->tabName]['diskQuota'] = "";
			$this->tab[$this->tabName]['versionDb'] = "";
			$this->tab[$this->tabName]['versionClaro'] = "";
			$this->tab[$this->tabName]['lastVisit'] = "";
			$this->tab[$this->tabName]['lastEdit'] = "";
			$this->tab[$this->tabName]['creationDate'] = "";
			$this->tab[$this->tabName]['expirationDate'] = "";
			$this->tab[$this->tabName]['courseEnrollAllowed'] = "";
			$this->tab[$this->tabName]['courseVisibility'] = "";
		}


	}
	/**
	 * sax end_element handler
	 * @access private
	 * @see sax api
	 */
	function end_element($parser, $tag)
	{

	}
	/**
	 * sax get_data handler
	 * @access private
	 * @see sax api
	 */
	function get_data($parser, $data)
	{
		if ('claro_info' == $this->tag)
		{
			if ('plateform_id' == $this->tag)
			{
				$this->tab['plateform_id'] = $data;
			}
			elseif ('new_version' == $this->tag)
			{
				$this->tab['new_version'] = $data;
			}
			elseif ('new_version_branch' == $this->tag)
			{
				$this->tab['new_version_branch'] = $data;
			}
			elseif ('clarolineVersion' == $this->tag)
			{
				$this->tab['clarolineVersion'] = $data;
			}
			elseif ('versionDb' == $this->tag)
			{
				$this->tab['versionDb'] = $data;
			}
		}
		if ('user_id' == $this->tag && 'users' == $this->tabName)
		{
			if ('username' == $this->tag)
			{
				$this->tab[$this->tabName]['username'] = $data;
			}
			elseif ('lastName' == $this->tag)
			{
				$this->tab[$this->tabName]['lastName'] = $data;
			}
			elseif ('versionDb' == $this->tag)
			{
				$this->tab[$this->tabName]['versionDb'] = $data;
			}
			elseif ('firstName' == $this->tag)
			{
				$this->tab[$this->tabName]['firstName'] = $data;
			}
		}
		if ('id' == $this->tag && 'group_team' == $this->tabName)
		{
			if ('name' == $this->tag)
			{
				$this->tab[$this->tabName]['name'] = $data;
			}
		}
		if ('id' == $this->tag && 'group_property' == $this->tabName)
		{
			if ('self_registration' == $this->tag)
			{
				$this->tab[$this->tabName]['self_registration'] = $data;
			}
			elseif ('nbGroupPerUser' == $this->tag)
			{
				$this->tab[$this->tabName]['nbGroupPerUser'] = $data;
			}
			elseif ('private' == $this->tag)
			{
				$this->tab[$this->tabName]['private'] = $data;
			}
			elseif ('forum' == $this->tag)
			{
				$this->tab[$this->tabName]['forum'] = $data;
			}
			elseif ('document' == $this->tag)
			{
				$this->tab[$this->tabName]['document'] = $data;
			}
			elseif ('wiki' == $this->tag)
			{
				$this->tab[$this->tabName]['wiki'] = $data;
			}
			elseif ('chat' == $this->tag)
			{
				$this->tab[$this->tabName]['chat'] = $data;
			}
		}
		if ('toolsInfo' == $this->tabName && 'tool' == $this->tag)
		{
			$this->tab[$this->tabName][$this->id][$this->cpt] = $data;
			$this->cpt++;
		}
		if ('course' == $this->tabName)
		{
			if ('cours_id' == $this->tag)
			{
				$this->tab[$this->tabName]['cours_id'] = $data;
			}
			elseif ('code' == $this->tag)
			{
				$this->tab[$this->tabName]['code'] = $data;
			}
			elseif ('fake_code' == $this->tag)
			{
				$this->tab[$this->tabName]['fake_code'] = $data;
			}
			elseif ('directory' == $this->tag)
			{
				$this->tab[$this->tabName]['directory'] = $data;
			}
			elseif ('dbName' == $this->tag)
			{
				$this->tab[$this->tabName]['dbName'] = $data;
			}
			elseif ('languageCourse' == $this->tag)
			{
				$this->tab[$this->tabName]['languageCourse'] = $data;
			}
			elseif ('intitule' == $this->tag)
			{
				$this->tab[$this->tabName]['intitule'] = $data;
			}
			elseif ('faculte' == $this->tag)
			{
				$this->tab[$this->tabName]['faculte'] = $data;
			}
			elseif ('enrollment_key' == $this->tag)
			{
				$this->tab[$this->tabName]['enrollment_key'] = $data;
			}
			elseif ('titulaires' == $this->tag)
			{
				$this->tab[$this->tabName]['titulaires'] = $data;
			}
			elseif ('email' == $this->tag)
			{
				$this->tab[$this->tabName]['email'] = $data;
			}
			elseif ('departmentUrlName' == $this->tag)
			{
				$this->tab[$this->tabName]['departmentUrlName'] = $data;
			}
			elseif ('departmentUrl' == $this->tag)
			{
				$this->tab[$this->tabName]['departmentUrl'] = $data;
			}
			elseif ('diskQuota' == $this->tag)
			{
				$this->tab[$this->tabName]['diskQuota'] = $data;
			}
			elseif ('versionDb' == $this->tag)
			{
				$this->tab[$this->tabName]['versionDb'] = $data;
			}
			elseif ('versionClaro' == $this->tag)
			{
				$this->tab[$this->tabName]['versionClaro'] = $data;
			}
			elseif ('lastVisit' == $this->tag)
			{
				$this->tab[$this->tabName]['lastVisit'] = $data;
			}
			elseif ('lastEdit' == $this->tag)
			{
				$this->tab[$this->tabName]['lastEdit'] = $data;
			}
			elseif ('creationDate' == $this->tag)
			{
				$this->tab[$this->tabName]['creationDate'] = $data;
			}
			elseif ('expirationDate' == $this->tag)
			{
				$this->tab[$this->tabName]['expirationDate'] = $data;
			}
			elseif ('courseVisibility' == $this->tag)
			{
				$this->tab[$this->tabName]['courseVisibility'] = $data;
			}
			elseif ('courseEnrollAllowed' == $this->tag)
			{
				$this->tab[$this->tabName]['courseEnrollAllowed'] = $data;
			}
		}
	}
	/**
	 * return the tab contained the parsed data
	 */
	function get_tab()
	{
		return $this->tab;
	}
}
/**
 *
 * parse the file "wiki.xml" into an array
 *
 * @var array  $tab     - xml content
 * @var string $tag     - xml tag
 * @var int    $id 		- id of the "record"
 * @var string $tabName - contain the sub array index name
 * @var int    $cpt     - counter to set some ids
 * @since 1.8 - 10-avr.-2006
 *
 * @access public
 */

class wiki_parser
{
	var $tab = array ();
	var $tag;
	var $id;
	var $cpt = -1;
	var $tabName;

 	/**
	 * sax start_element handler
	 * @access private
	 * @see sax api
	 */
	function start_element($parser, $tag, $attributes)
	{
		$this->tag = $tag;
		if ('wiki_acls' == $this->tag)
		{
			$this->tabName = $this->tag;
		}
		if ('wiki_pages' == $this->tag)
		{
			$this->tabName = $this->tag;
		}
		if ('wiki_pages_content' == $this->tag)
		{
			$this->tabName = $this->tag;
		}
		if ('wiki_properties' == $this->tag)
		{
			$this->tabName = $this->tag;
		}
		if ('wiki_id' == $this->tag && 'wiki_acls' == $this->tabName)
		{
			$this->cpt++;
			$this->tab[$this->tabName][$this->cpt]["wiki_id"] = $attributes['wiki_id'];
			$this->tab[$this->tabName][$this->cpt]["flag"] = "";
			$this->tab[$this->tabName][$this->cpt]["value"] = "";

		}
		if ('id' == $this->tag && 'wiki_pages' == $this->tabName)
		{
			$this->id = $attributes["id"];
			$this->tab[$this->tabName][$this->id]["id"] = $this->id;
			$this->tab[$this->tabName][$this->id]["wiki_id"] = "";
			$this->tab[$this->tabName][$this->id]["owner_id"] = "";
			$this->tab[$this->tabName][$this->id]["title"] = "";
			$this->tab[$this->tabName][$this->id]["ctime"] = "";
			$this->tab[$this->tabName][$this->id]["last_version"] = "";
			$this->tab[$this->tabName][$this->id]["last_mtime"] = "";
		}
		if ('id' == $this->tag && 'wiki_pages_content' == $this->tabName)
		{
			$this->id = $attributes["id"];
			$this->tab[$this->tabName][$this->id]["id"] = $this->id;
			$this->tab[$this->tabName][$this->id]["pid"] = "";
			$this->tab[$this->tabName][$this->id]["editor_id"] = "";
			$this->tab[$this->tabName][$this->id]["mtime"] = "";
			$this->tab[$this->tabName][$this->id]["content"] = "";
		}
		if ('id' == $this->tag && 'wiki_properties' == $this->tabName)
		{
			$this->id = $attributes["id"];
			$this->tab[$this->tabName][$this->id]["id"] = $this->id;
			$this->tab[$this->tabName][$this->id]["title"] = "";
			$this->tab[$this->tabName][$this->id]["description"] = "";
			$this->tab[$this->tabName][$this->id]["group_id"] = null;
		}
	}
	/**
	 * sax end_element handler
	 * @access private
	 * @see sax api
	 */
	function end_element($parser, $tag)
	{

	}
	/**
	 * sax get_data handler
	 * @access private
	 * @see sax api
	 */
	function get_data($parser, $data)
	{
		if ('wiki_acls' == $this->tabName)
		{
			if ("flag" == $this->tag)
			{
				$this->tab[$this->tabName][$this->cpt]["flag"] .= $data;
			}
			elseif ("value" == $this->tag)
			{
				$this->tab[$this->tabName][$this->cpt]["value"] .= $data;
			}
		}
		if ('wiki_pages' == $this->tabName)
		{
			if ("wiki_id" == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]["wiki_id"] .= $data;
			}
			elseif ("owner_id" == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]["owner_id"] .= $data;
			}
			elseif ("title" == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]["title"] .= $data;
			}
			elseif ("ctime" == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]["ctime"] .= $data;
			}
			elseif ("last_version" == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]["last_version"] .= $data;
			}
			elseif ("last_mtime" == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]["last_mtime"] .= $data;
			}
		}
		if ('wiki_pages_content' == $this->tabName)
		{
			if ("pid" == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]["pid"] .= $data;
			}
			elseif ("editor_id" == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]["editor_id"] .= $data;
			}
			elseif ("mtime" == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]["mtime"] .= $data;
			}
			elseif ("content" == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]["content"] .= $data;
			}
		}
		if ('wiki_properties' == $this->tabName)
		{
			if ("title" == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]["title"] .= $data;
			}
			elseif ("description" == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]["description"] .= $data;
			}
			elseif ("group_id" == $this->tag)
			{
				if (0 == $data)
					$data = null;
				$this->tab[$this->tabName][$this->id]["group_id"] .= $data;
			}
		}
	}
	/**
	 * return the tab contained the parsed data
	 */
	function get_tab()
	{
		return $this->tab;
	}
}
/**
 *
 * parse the file "work.xml" into an array
 *
 * @var array  $tab     - xml content
 * @var string $tag     - xml tag
 * @var int    $id 		- id of the "record"
 * @var string $tabName - contain the sub array index name
 * @since 1.8 - 10-avr.-2006
 *
 * @access public
 */

class wrk_parser
{
	var $tab = array ();
	var $tag;
	var $id;
	var $tabName;

	/**
	 * sax start_element handler
	 * @access private
	 * @see sax api
	 */
	function start_element($parser, $tag, $attributes)
	{
		$this->tag = $tag;
		if ('assignment' == $this->tag)
		{
			$this->tabName = $this->tag;
		}
		if ('submission' == $this->tag)
		{
			$this->tabName = $this->tag;
		}
		if ('id' == $this->tag && 'assignment' == $this->tabName)
		{
			$this->id = $attributes["id"];
			$this->tab[$this->tabName][$this->id]["id"] = $this->id;
			$this->tab[$this->tabName][$this->id]["title"] = "";
			$this->tab[$this->tabName][$this->id]["description"] = "";
			$this->tab[$this->tabName][$this->id]["visibility"] = "";
			$this->tab[$this->tabName][$this->id]["def_submission_visibility"] = "";
			$this->tab[$this->tabName][$this->id]["assignment_type"] = "";
			$this->tab[$this->tabName][$this->id]["authorized_content"] = "";
			$this->tab[$this->tabName][$this->id]["allow_late_upload"] = "";
			$this->tab[$this->tabName][$this->id]["start_date"] = "";
			$this->tab[$this->tabName][$this->id]["end_date"] = "";
			$this->tab[$this->tabName][$this->id]["prefill_text"] = "";
			$this->tab[$this->tabName][$this->id]["prefill_doc_path"] = "";
			$this->tab[$this->tabName][$this->id]["prefill_submit"] = "";
		}
		if ('id' == $this->tag && 'submission' == $this->tabName)
		{
			$this->id = $attributes["id"];
			$this->tab[$this->tabName][$this->id]["id"] = $this->id;
			$this->tab[$this->tabName][$this->id]["assignment_id"] = "";
			$this->tab[$this->tabName][$this->id]["parent_id"] = "";
			$this->tab[$this->tabName][$this->id]["user_id"] = "";
			$this->tab[$this->tabName][$this->id]["group_id"] = "";
			$this->tab[$this->tabName][$this->id]["title"] = "";
			$this->tab[$this->tabName][$this->id]["visibility"] = "";
			$this->tab[$this->tabName][$this->id]["creation_date"] = "";
			$this->tab[$this->tabName][$this->id]["last_edit_date"] = "";
			$this->tab[$this->tabName][$this->id]["authors"] = "";
			$this->tab[$this->tabName][$this->id]["submitted_text"] = "";
			$this->tab[$this->tabName][$this->id]["submitted_doc_path"] = "";
			$this->tab[$this->tabName][$this->id]["private_feedback"] = "";
			$this->tab[$this->tabName][$this->id]["original_id"] = "";
			$this->tab[$this->tabName][$this->id]["score"] = "";

		}

	}
	/**
	 * sax end_element handler
	 * @access private
	 * @see sax api
	 */
	function end_element($parser, $tag)
	{

	}
	/**
	 * sax get_data handler
	 * @access private
	 * @see sax api
	 */
	function get_data($parser, $data)
	{
		if ('assignment' == $this->tabName)
		{
			if ('title' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]["title"] .= $data;
			}
			elseif ('description' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]["description"] .= $data;
			}
			elseif ('visibility' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]["visibility"] .= $data;
			}
			elseif ('def_submission_visibility' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]["def_submission_visibility"] .= $data;
			}
			elseif ('assignment_type' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]["assignment_type"] .= $data;
			}
			elseif ('authorized_content' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]["authorized_content"] .= $data;
			}
			elseif ('allow_late_upload' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]["allow_late_upload"] .= $data;
			}
			elseif ('start_date' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]["start_date"] .= $data;
			}
			elseif ('end_date' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]["end_date"] .= $data;
			}
			elseif ('prefill_text' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]["prefill_text"] .= $data;
			}
			elseif ('prefill_doc_path' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]["prefill_doc_path"] .= $data;
			}
			elseif ('prefill_submit' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]["prefill_submit"] .= $data;
			}
		}
		if ('submission' == $this->tabName)
		{
			if ('assignment_id' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]["assignment_id"] .= $data;
			}
			elseif ('parent_id' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]["parent_id"] .= $data;
			}
			elseif ('user_id' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]["user_id"] .= $data;
			}
			elseif ('group_id' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]["group_id"] .= $data;
			}
			elseif ('title' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]["title"] .= $data;
			}
			elseif ('visibility' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]["visibility"] .= $data;
			}
			elseif ('creation_date' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]["creation_date"] .= $data;
			}
			elseif ('last_edit_date' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]["last_edit_date"] .= $data;
			}
			elseif ('authors' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]["authors"] .= $data;
			}
			elseif ('submitted_text' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]["submitted_text"] .= $data;
			}
			elseif ('submitted_doc_path' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]["submitted_doc_path"] .= $data;
			}
			elseif ('private_feedback' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]["private_feedback"] .= $data;
			}
			elseif ('original_id' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]["original_id"] .= $data;
			}
			elseif ('score' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]["score"] .= $data;
			}
		}
	}
	/**
	 * return the tab contained the parsed data
	 */
	function get_tab()
	{
		return $this->tab;
	}
}
/**
 *
 * parse the file "users.xml" into an array
 *
 * @var array  $tab     - xml content
 * @var string $tag     - xml tag
 * @var int    $id 		- id of the "record"
 * @var string $tabName - contain the sub array index name
 * @var int    $cpt     - counter to set some ids
 * @since 1.8 - 10-avr.-2006
 *
 * @access public
 */

class users_parser
{
	var $tab = array ();
	var $tag;
	var $id;
	var $tabName;
	var $cpt = 0;

	/**
	 * sax start_element handler
	 * @access private
	 * @see sax api
	 */
	function start_element($parser, $tag, $attributes)
	{
		$this->tag = $tag;

		if ('user' == $this->tag)
		{
			$this->tabName = $this->tag;
		}
		if ('rel_course_user' == $this->tag)
		{
			$this->tabName = $this->tag;
		}
		if ('user_id' == $this->tag && 'user' == $this->tabName)
		{
			$this->id = $attributes["user_id"];
			$this->tab[$this->tabName][$this->id]["user_id"] = $this->id;
			$this->tab[$this->tabName][$this->id]["firstname"] = "";
			$this->tab[$this->tabName][$this->id]["lastname"] = "";
			$this->tab[$this->tabName][$this->id]["username"] = "";
			$this->tab[$this->tabName][$this->id]["password"] = "";
			$this->tab[$this->tabName][$this->id]["authSource"] = "";
			$this->tab[$this->tabName][$this->id]["email"] = "";
			$this->tab[$this->tabName][$this->id]["statut"] = "";
			$this->tab[$this->tabName][$this->id]["officialCode"] = "";
			$this->tab[$this->tabName][$this->id]["phoneNumber"] = "";
			$this->tab[$this->tabName][$this->id]["pictureUri"] = "";
			$this->tab[$this->tabName][$this->id]["creatorId"] = "";
		}
		if ('user_id' == $this->tag && 'rel_course_user' == $this->tabName)
		{

			$this->id = $attributes["user_id"];
			$this->tab[$this->tabName][$this->id]["user_id"] = $this->id;
			$this->tab[$this->tabName][$this->id]["statut"] = "";
			$this->tab[$this->tabName][$this->id]["role"] = "";
			$this->tab[$this->tabName][$this->id]["team"] = "";
			$this->tab[$this->tabName][$this->id]["tutor"] = "";
		}
	}
	/**
	 * sax end_element handler
	 * @access private
	 * @see sax api
	 */
	function end_element($parser, $tag)
	{

	}
	/**
	 * sax get_data handler
	 * @access private
	 * @see sax api
	 */
	function get_data($parser, $data)
	{
		if ('rel_course_user' == $this->tabName)
		{
			if ('statut' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]["statut"] .= $data;
			}
			if ('role' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]["role"] .= $data;
			}
			if ('team' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]["team"] .= $data;
			}
			if ('tutor' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]["tutor"] .= $data;
			}
		}
		if ('user' == $this->tabName)
		{
			if ('firstname' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]["firstname"] .= $data;
			}
			elseif ('lastname' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]["lastname"] .= $data;
			}
			elseif ('username' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]["username"] .= $data;
			}
			elseif ('password' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]["password"] .= $data;
			}
			elseif ('authSource' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]["authSource"] .= $data;
			}
			elseif ('email' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]["email"] .= $data;
			}
			elseif ('statut' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]["statut"] .= $data;
			}
			elseif ('officialCode' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]["officialCode"] .= $data;
			}
			elseif ('phoneNumber' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]["phoneNumber"] .= $data;
			}
			elseif ('pictureUri' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]["pictureUri"] .= $data;
			}
			elseif ('creatorId' == $this->tag)
			{
				$this->tab[$this->tabName][$this->id]["creatorId"] .= $data;
			}
		}
	}
	/**
	 * return the tab contained the parsed data
	 */
	function get_tab()
	{
		return $this->tab;
	}
}
?>