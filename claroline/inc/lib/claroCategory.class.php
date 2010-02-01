<?php

if ( count( get_included_files() ) == 1 ) die( '---' );

/**
 * CLAROLINE
 *
 * claroCategory Class
 *
 * @version 1.10 $Revision: 11894 $
 *
 * @copyright 2001-2010 Universite catholique de Louvain (UCL)
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @author Claro Team <cvs@claroline.net>
 * @author Antonin Bourguignon <antonin.bourguignon@claroline.net>
 */



/**
 * 
 * Development notes
 * /////////////////
 * 
 * Database
 * ========
 * 
 * Adaptation of the previous table `faculte` (becoming `category`)
 * ----------------------------------------------------------------
 * 
 * RENAME TABLE `db_name`.`PREFIX_faculte`  TO `db_name`.`PREFIX_category` ;
 * 
 * ALTER TABLE `PREFIX_category` CHANGE `code_P` `idParent` VARCHAR( 12 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL ; 
 * ALTER TABLE `PREFIX_category` CHANGE `treePos` `rank` INT( 11 ) NOT NULL DEFAULT '0' ;
 * /!\ Conversion of 'TRUE'/'FALSE' values into '1'/'0' !  Then: 
 * ALTER TABLE `PREFIX_category` CHANGE `canHaveCoursesChild` `canHaveCoursesChild` TINYINT( 1 ) NOT NULL DEFAULT '1' ;
 * ALTER TABLE `PREFIX_category` DROP `canHaveCatChild` ;
 * ALTER TABLE `PREFIX_category` DROP INDEX `code_P` ;
 * ALTER TABLE `PREFIX_category` DROP INDEX `treePos` ;
 * ALTER TABLE `PREFIX_category` DROP INDEX `code` ;
 * ALTER TABLE `PREFIX_category` ADD UNIQUE (`code`) ;
 * /!\ Conversion of CODES into IDS !  Then: 
 * ALTER TABLE `PREFIX_category` CHANGE `idParent` `idParent` INT( 11 ) NOT NULL DEFAULT '0' ;
 * ALTER TABLE `PREFIX_category` DROP `nb_childs` ;
 *  
 *  
 * 
 * Creation of the join table `rel_course_category`
 * ------------------------------------------------
 * 
 * CREATE TABLE `db_name`.`PREFIX_rel_course_category` (
 * `idCourse` INT NOT NULL ,
 * `idCategory` INT NOT NULL ,
 * `rootCourse` BOOL NOT NULL DEFAULT '0'
 * ) ENGINE = MYISAM ;
 * ALTER TABLE `db_name`.`PREFIX_rel_course_category` ADD PRIMARY KEY ( `idCourse` , `idCategory` ) ;
 * 
 * 
 * 
 * Adaptation of the previous table `cours`
 * ----------------------------------------
 * 
 * ALTER TABLE `db_name`.`PREFIX_cours` DROP `faculte` ;
 * 
 * 
 * 
 * Structures for testing purpose
 * ------------------------------
 * 

--
-- Structure of table `db_name`.`PREFIX_category`
--

CREATE TABLE IF NOT EXISTS `PREFIX_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  `code` varchar(12) NOT NULL DEFAULT '',
  `idParent` int(11) NOT NULL DEFAULT '0',
  `rank` int(11) NOT NULL DEFAULT '0',
  `visible` tinyint(1) NOT NULL DEFAULT '1',
  `canHaveCoursesChild` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=22 ;

--
-- Structure of table `db_name`.`PREFIX_rel_course_category`
--

CREATE TABLE IF NOT EXISTS `PREFIX_rel_course_category` (
  `courseId` int(11) NOT NULL,
  `categoryId` int(11) NOT NULL,
  `rootCourse` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`courseId`,`categoryId`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


 * 
 * Datas for testing purpose
 * -------------------------
 * 

--
-- Content of table `db_name`.`PREFIX_category`
--

INSERT INTO `PREFIX_category` (`id`, `name`, `code`, `idParent`, `rank`, `visible`, `canHaveCoursesChild`) VALUES
(1, 'Sciences', 'SC', 0, 6, 1, 0),
(2, 'Economics', 'ECO', 0, 5, 1, 0),
(3, 'Humanities', 'HUMA', 0, 3, 1, 1),
(4, 'Informatique', 'INFO', 0, 4, 1, 1),
(5, 'Physique', 'PHY', 1, 1, 1, 1),
(6, 'Chimie', 'CHIM', 1, 1, 1, 0),
(7, 'Géologie', 'GEO', 1, 2, 1, 0),
(8, 'Macro Economie', 'MACROECO', 2, 1, 1, 1),
(9, 'Catégorie cachée', 'CATCACH', 5, 1, 1, 0),
(18, 'Test 1', 'TEST1', 9, 1, 1, 0),
(19, 'Test 2', 'TEST2', 9, 2, 1, 1),
(20, 'Test 3', 'TEST3', 9, 3, 1, 1),
(21, 'Intro aux faits et mécanismes économiques', 'INTROFME', 2, 2, 1, 1);

--
-- Content of table `db_name`.`PREFIX_rel_course_category`
--

INSERT INTO `PREFIX_rel_course_category` (`courseId`, `categoryId`, `rootCourse`) VALUES
(1, 1, 0),
(2, 6, 0),
(3, 6, 0);


 * 
 * Tasks
 * =====
 * 
 * TODO: stuff to fix before definitive update
 * -------------------------------------------
 * 
 * * Delete inc/lib/faculty.inc.lib.php
 * * Modify inc/lib/sql.lib.php claro_sql_get_main_tbl() (table names have to be fixed)
 * * In french language files, make difference between "Aucun" and "Aucune" (cf. dropdown list in the form)c
 * 
 * 
 * TODO: questions
 * ---------------
 * 
 * * When hiding a category, should all its children categories also get hidden ? (seems logic)
 * * What's the best way managing and displaying: (1) the number of courses in a category and 
 *   (2) the number of categories in a category ?
 * * What's the purpose of variable $cancelUrl ?
 * * Fold/Unfold categories: is it necessary ?
 * 
 * 
 * Regarding users: main modifications
 * ===================================
 * 
 * * If you want to change the parent of a category, you have to use the "Edit" function (there is no more "Move/Displace" function)
 * * The menu has been improved (a little more user friendly: disabled valors in the drop down list, ...)
 *
 */


require_once dirname(__FILE__) . '/backlog.class.php'; // Manage the backlog entries
require_once dirname(__FILE__) . '/category.lib.inc.php'; // Contains all MySQL requests for this class
require_once dirname(__FILE__) . '/course.lib.inc.php'; // Contains certain usefull functions for this class: claro_get_lang_flat_list(), ...
require_once dirname(__FILE__) . '/../../messaging/lib/message/messagetosend.lib.php';


$jsLoader = JavascriptLoader::getInstance();
$jsLoader->load( 'claroline.ui' );

class ClaroCategory
{
    // Identifier
    public $id;

    // Name
    public $name;

    // Code
    public $code;

    // Identifier of the parent category
    public $idParent;

    // Position in the tree's level
    public $rank;
    
    // Visibility
    public $visible;

    // Allowed to possess children (true = yes, false = no)
    public $canHaveCoursesChild;
    
    // Backlog object
    public $backlog;
    
    // List of GET or POST parameters
    public $htmlParamList = array();    
    

    /**
     * Constructor
     */
    function ClaroCategory ($id = null, $name = null, $code = null, $idParent = null, $rank = null, $visible = 1, $canHaveCoursesChild = 1)
    {
        $this->id                   = $id;
        $this->name                 = $name;
        $this->code                 = $code;
        $this->idParent             = $idParent;
        $this->rank                 = $rank;
        $this->visible              = $visible;
        $this->canHaveCoursesChild  = $canHaveCoursesChild;
        $this->backlog 				= new Backlog();
    }
    

    /**
     * Load category data from database in the current object
     *
     * @param $id int category identifier
     * @return boolean success
     */
    public function load ($id)
    {
        $data = claro_get_cat_datas($id);

        if ( !$data ) 
        {
            claro_failure::set_failure('category_not_found');
            return false;
        }
        else
        {            
	        $this->id                   = $id;
	        $this->name                 = $data['name'];
	        $this->code                 = $data['code'];
	        $this->idParent             = $data['idParent'];
	        $this->rank                 = $data['rank'];
	        $this->visible              = $data['visible'];
	        $this->canHaveCoursesChild  = $data['canHaveCoursesChild'];
	        
	        return true;
        }
    }
    

    /**
     * Insert or update current category data
     *
     * @return boolean success
     */
    public function save ()
    {		
        if ( empty($this->id) )
        {
            // No id: it's a new category -> insert
            
            if( claro_insert_cat_datas($this->name, $this->code, $this->idParent, $this->rank, $this->visible, $this->canHaveCoursesChild) ) 
                return true;
            else 
            {
            	claro_failure::set_failure('category_not_saved');
            	return false;
            }
        }
        else
        {
            // No id: it's a new category -> update
            
            if( claro_update_cat_datas($this->id, $this->name, $this->code, $this->idParent, $this->rank, $this->visible, $this->canHaveCoursesChild) ) 
                return true;
            else 
            {
            	claro_failure::set_failure('category_not_saved');
            	return false;
            }
        }
    }
    

    /**
     * Delete current category data and content
     *
     * @return boolean success
     */
    public function delete ()
    {
        //TODO handle cases where the category has subcategories
        if ( claro_delete_cat_datas($this->id) ) 
            return true;
        else 
            return false;
    }
    
    
    /**
     * Select all categories in database from a certain point
     *
	 * @param $start_node the parent from wich we want to get the categories tree (default: 0)
	 * @param $start_level the level where we start (default: 0)
     * @return array containing all the categories organized hierarchically and ordered by rank
     */
    public static function fetchAllCategories ( $start_node = 0, $start_level = 0 )
    {
        return claro_get_all_categories($start_node, $start_level);
    }
    
    
    /**
     * Count the number of courses in the current category (DOESN'T include courses 
     * in sub categories).
     * 
     * @return integer number of courses
     */
    public function countCategoryCourses ()
    {
        return claro_count_category_courses($this->id);
    }    
    
    
    /**
     * Swap the visibility value of a category (from TRUE to FALSE or from FALSE to TRUE) 
     * and save it into the database
     * 
     * @return boolean success
     */
    public function swapVisibility () 
    {
        $this->visible = !$this->visible;
        
        if ( claro_set_cat_visibility($this->id, $this->visible) ) 
            return true;
        else 
            return false;
    }
    
    
    /**
     * Exchange category's position with previous category of the same level
     * 
     * @return boolean success
     */
    public function lowerRank () 
    {
		// Get the id of the previous category (if any)
		$idSwapCategory = claro_get_previous_cat_datas($this->rank, $this->idParent);
		if (!empty($idSwapCategory))
		{
			$this->exchangeRanks($idSwapCategory);
			return true;
		}
		else
		{
            claro_failure::set_failure('category_no_predecessor');
            return false;
		}
    }
    
    
    /**
     * Exchange category's position with following category of the same level
     * 
     * @return boolean success
     */
    public function higherRank () 
    {
		// Get the id of the following category (if any)
		$idSwapCategory = claro_get_following_cat_datas($this->rank, $this->idParent);
		if (!empty($idSwapCategory))
		{
			$this->exchangeRanks($idSwapCategory);
			return true;
		}
		else
		{
            claro_failure::set_failure('category_no_successor');
            return false;
		}
    }
    
    
    /**
     * Exchange ranks between the current category and another one 
     * and save the modification in database.
     * 
     * @param $id identifier of the other category
     */
    public function exchangeRanks ($id)
    {
    	// Get the other category
		$swapCategory = new claroCategory();
		$swapCategory->load($id);
		
		// Exchange the ranks
		$tempRank = $this->rank;
		$this->rank = $swapCategory->rank;
		$swapCategory->rank = $tempRank;
		
		// Save the modifications
		$this->save();
		$swapCategory->save();
    }
    
    
    /**
     * Check if the code of the category is unique (doesn't already exists in database)
     * 
     * @return boolean: TRUE if the code is unique, FALSE if it's not
     */
    public function checkUniqueCode () 
    {
        if ( claro_count_code($this->id, $this->code) == 0 ) 
            return true;
        else 
            return false;
    }
    
    
    /**
     * Check if the specified category is a child of the current category
     * 
     * @param $id the identifier of the category we want to check
     * @return boolean: TRUE if the specified category is the child of the current category
     */
    public function checkIsChild ($id) 
    {
     	$ids = claro_get_parents_ids($id);
     	 
        if ( in_array($this->id, $ids) ) 
            return true;
        else 
            return false;
    }
    

    /**
     * Retrieve category data from form and fill current category with it
     */
    public function handleForm ()
    {
    	if ( isset($_REQUEST['category_id']) )                 	 $this->id = trim(strip_tags($_REQUEST['category_id']));
        if ( isset($_REQUEST['category_name']) )                 $this->name = trim(strip_tags($_REQUEST['category_name']));

        if ( isset($_REQUEST['category_code']) ) // Only capital letters and numbers
        {
            $this->code = trim(strip_tags($_REQUEST['category_code']));
            $this->code = preg_replace('/[^A-Za-z0-9_]/', '', $this->code);
            $this->code = strtoupper($this->code);
        }

        if ( isset($_REQUEST['category_parent']) )               $this->idParent = trim(strip_tags($_REQUEST['category_parent']));
        
        if ( isset($_REQUEST['category_rank']) )                 $this->rank = trim(strip_tags($_REQUEST['category_rank']));
       		
        if ( isset($_REQUEST['category_visible']) )              $this->visible = trim(strip_tags($_REQUEST['category_visible']));
        if ( isset($_REQUEST['category_can_have_courses']) )     $this->canHaveCoursesChild = trim(strip_tags($_REQUEST['category_can_have_courses']));
    }
    

    /**
     * Validate data from current object.  Error handling with a backlog object.
     *
     * @return boolean success
     */
    public function validate ()
    {
        //TODO don't get how this function actually works
        
        $success = true ;

        /**
         * Configuration array , define here which field can be left empty or not
         */

        //TODO make it more accurate using function get_conf('human_label_needed');
        $fieldRequiredStateList['name']                 = true;
        $fieldRequiredStateList['code']                 = true;
        $fieldRequiredStateList['idParent']             = true;
        $fieldRequiredStateList['rank']                 = false;
        $fieldRequiredStateList['visible']              = true;
        $fieldRequiredStateList['canHaveCoursesChild']  = true;

        // Validate category name
        if ( is_null($this->name) && $fieldRequiredStateList['name'] )
        {
        	claro_failure::set_failure('category_missing_field_name');
            $this->backlog->failure(get_lang('Category name needed'));
            $success = false ;
        }

        // Validate category code
        if ( is_null($this->code) && $fieldRequiredStateList['code'] )
        {
        	claro_failure::set_failure('category_missing_field_code');
            $this->backlog->failure(get_lang('Category code needed'));
            $success = false ;
        }
        
        // Check if the code is unique
        if ( !$this->checkUniqueCode() )
        {
        	claro_failure::set_failure('category_duplicate_code');
	       	$this->backlog->failure(get_lang('This category already exists !'));
            $success = false ;
        }
        
        // Validate parent identifier
        if ( is_null($this->idParent) && $fieldRequiredStateList['idParent'] )
        {
        	claro_failure::set_failure('category_missing_field_idParent');
            $this->backlog->failure(get_lang('Category parent needed'));
            $success = false ;
        }
        
        // Category can't be its own parent
		if ( $this->idParent == $this->id ) 
		{
        	claro_failure::set_failure('category_self_linked');
	       	$this->backlog->failure(get_lang('Category can\'t be its own parent'));
            $success = false ;
		}
        
        // Category can't be linked to one of its own children
		if ( $this->checkIsChild($this->idParent) ) 
		{
        	claro_failure::set_failure('category_child_linked');
	       	$this->backlog->failure(get_lang('Category can\'t be linked to one of its own children'));
            $success = false ;
		}
        
        // Check authorisation to possess courses
        if ( is_null($this->visible) && $fieldRequiredStateList['visible'] )
        {
        	claro_failure::set_failure('category_missing_field_visible');
            $this->backlog->failure(get_lang('Visibility of the category must be set'));
            $success = false;
        }
        
        // Check authorisation to possess courses
        if ( is_null($this->canHaveCoursesChild) && $fieldRequiredStateList['canHaveCoursesChild'] )
        {
        	claro_failure::set_failure('category_missing_field_canHaveCoursesChild');
            $this->backlog->failure(get_lang('Category must be authorized or not to have courses children'));
            $success = false;
        }

        return $success;
    }
    
    
    /**
     * Put the current category's datas into a string format
     * 
     * @return string
     */
    public function toString ()
    {
        $str = 
        	 'id = ' . $this->id . "\n"
        .    'name = ' . $this->name . "\n"
        .    'code = ' . $this->code . "\n"
        .    'idParent = ' . $this->idParent . "\n"
        .    'rank = ' . $this->rank . "\n"
        .    'visible = ' . $this->visible . "\n"
        .    'canHaveCoursesChild = ' . $this->canHaveCoursesChild . "\n";
        
        return $str;
    }
    

    /**
     * Display form
     *
     * @param $cancelUrl string url of the cancel button
     * @return string html output of form
     */
    public function displayForm ($cancelUrl=null)
    {
        $languageList = claro_get_lang_flat_list();
        $categoryList = claroCategory::fetchAllCategories();

        // TODO cancelUrl cannot be null
        
        if ( is_null($cancelUrl) )
            $cancelUrl = get_path('clarolineRepositoryWeb') . 'course/index.php?cid=' . htmlspecialchars($this->id);

        $html = '';

        $html .= '<form method="post" id="categorySettings" action="' . $_SERVER['PHP_SELF'] . '" >' . "\n"
            . claro_form_relay_context()
            . '<input type="hidden" name="cmd" value="' . (empty($this->id)?'exAdd':'exEdit') . '" />' . "\n"
            . '<input type="hidden" name="claroFormId" value="' . uniqid('') . '" />' . "\n"

            . $this->getHtmlParamList('POST');

        $html .= '<fieldset>' . "\n"
        	. '<dl>' . "\n";
        	
        // Category identifier
        $html .= '<input type="hidden" name="category_id" value="' . $this->id . '" />' . "\n";

        // Category name
        $html .= '<dt>'
            . '<label for="category_name">'
            . get_lang('Category name')
            . (get_conf('human_label_needed') ? '<span class="required">*</span> ':'') 
            .'</label>&nbsp;:</dt>'
            . '<dd>'
            . '<input type="text" name="category_name" id="category_name" value="' . htmlspecialchars($this->name) . '" size="30" maxlength="100" />'
            . (empty($this->id) ? '<br /><small>'.get_lang('e.g. <em>Sciences of Economics</em>').'</small>':'')
            . '</dd>' . "\n" ;

        // Category code
        $html .= '<dt>'
            . '<label for="category_code">'
            . get_lang('Category code')
            . '<span class="required">*</span> '
            . '</label>&nbsp;:</dt>'
            . '<dd><input type="text" id="category_code" name="category_code" value="' . htmlspecialchars($this->code) . '" size="30" maxlength="12" />'
            . (empty($this->id) ? '<br /><small>'.get_lang('max. 12 characters, e.g. <em>ROM2121</em>').'</small>':'')
            . '</dd>' . "\n" ;

        // Category's parent
        $html .= '<dt>'
            . '<label for="category_parent">' 
            . get_lang('Parent category') 
            . '</label>&nbsp;:</dt>'
            . '<dd>'
            . '<select  id="category_parent" name="category_parent" />'
            . '<option value="0">' . get_lang("None") . '</option>';		// TODO: in French, manage the feminine gender of "Aucun"
            
            $disabled = false;
            $tempLevel = null;
            foreach ($categoryList as $elmt)
            {
            	// Enable/disable elements in the drop down list
            	if ( !empty($elmt['id']) && $elmt['id'] == $this->id )
            	{
            		$disabled = true;
            		$tempLevel = $elmt['level'];
            	}
            	elseif ( isset($tempLevel) && $elmt['level'] > $tempLevel )
            	{
            		$disabled = true;
            	}
            	else
            	{
            		$disabled = false;
            		$tempLevel = null;
            	}
            	
            	$html .= '<option value="' . $elmt['id'] . '" ' . ( ( !empty($elmt['id']) && $elmt['id'] == $this->idParent ) ? 'selected="selected"' : null ) . ( ( $disabled ) ? 'disabled="disabled"' : null ) . '>' . str_repeat('&nbsp;', 4*$elmt['level']) . $elmt['name'] . ' (' . $elmt['code'] . ') </option>';
            }
            
        $html .= '</select>'
            . '</dd>' . "\n" ;

        // Category's rank
        /*
        $html .= '<dt>'
            . '<label for="category_rank">'
            . get_lang('Category\'s rank')
            . '</label>'
            . '&nbsp;:'
            . '</dt>'
            . '<dd>'
            . '<input type="text" id="category_rank" name="category_rank" value="' . htmlspecialchars($this->rank) . '" size="60" />'
            . '</dd>' . "\n";
        */
        $html .= '<input type="hidden" name="category_rank" value="' . (empty($this->rank)?0:$this->rank) . '" />'."\n";

        // Category's visibility
        $html .= '<dt>'
            . get_lang('Category visibility') 
            . '<span class="required">*</span> '
            . ' :'
            . '</dt>'
            . '<dd>'
            . '<input type="radio" id="visible" name="category_visible" value="1" ' . (( $this->visible == 1 || !isset($this->visible) ) ? 'checked="checked"' : null ) . ' />'
            . '&nbsp;'
            . '<label for="visible">' . get_lang('Visible') . '</label><br/>'
            . '<input type="radio" id="hidden" name="category_visible" value="0" ' . (( $this->visible == 0 && isset($this->visible) ) ? 'checked="checked"' : null ) . ' />'
            . '&nbsp;'
            . '<label for="hidden">' . get_lang('Hidden') . '</label>'
            . '</dd>' . "\n" ;

        // Category's right to possess courses
        $html .= '<dt>'
            . get_lang('Can have courses')
            . '<span class="required">*</span> '
            . ' :'
            . '</dt>'
            . '<dd>'
            . '<input type="radio" id="can_have_courses" name="category_can_have_courses" value="1" ' . (( $this->canHaveCoursesChild == 1 || !isset($this->canHaveCoursesChild) ) ? 'checked="checked"':'' ) . ' />'
            . '&nbsp;'
            . '<label for="can_have_courses">' . get_lang('Yes') . '</label><br/>'
            . '<input type="radio" id="cant_have_courses" name="category_can_have_courses" value="0" ' . (( $this->canHaveCoursesChild == 0 && isset($this->canHaveCoursesChild) ) ? 'checked="checked"':'' ) . ' />'
            . '&nbsp;'
            . '<label for="cant_have_courses">' . get_lang('No') . '</label><br/>'
            . '<small>'.get_lang('Authorize the category to possess courses or not (opened or closed category)').'</small>'
            . '</dd>' . "\n" ;
            
        // Form's footer
        $html .= '</fieldset>' . "\n"
        	. '<span class="required">*</span>&nbsp;'.get_lang('Denotes required fields') . '<br/>' . "\n"
			. '<input type="submit" value="' . get_lang('Ok') . '" />' . "\n"
	        . claro_html_button($_SERVER['PHP_SELF'], get_lang('Cancel'))
	        . '</form>' . "\n";

        return $html;
    }

    /**
     * Display question of delete confirmation
     *
     * @param $cancelUrl string url of the cancel button
     * @return string html output of form
     */
    public function displayDeleteConfirmation ()
    {
        //TODO Give and warn if subcategories exist (in this case, can't delete the current category)        
        
        $paramString = $this->getHtmlParamList('GET');

        $deleteUrl = './settings.php?cmd=exDelete&amp;'.$paramString;
        $cancelUrl = './settings.php?'.$paramString ;

        $html = '';

        $html .= '<p>'
        . '<font color="#CC0000">'
        . get_lang('Are you sure to delete the category "%ccategory_name" ( %category_code ) ?', array('%category_name' => $this->name,
                                                                                                       '%category_code' => $this->code ))
        . '</font>'
        . '</p>'
        . '<p>'
        . '<font color="#CC0000">'
        . '<a href="'.$deleteUrl.'">'.get_lang('Yes').'</a>'
        . '&nbsp;|&nbsp;'
        . '<a href="'.$cancelUrl.'">'.get_lang('No').'</a>'
        . '</font>'
        . '</p>';

        return $html;
    }
    

    /**
     * Add html parameter to list
     *
     * @param $name string input name
     * @param $value string input value
     */
    public function addHtmlParam($name, $value)
    {
        $this->htmlParamList[$name] = $value;
    }
    

    /**
     * Get html representing parameter list depending on method (POST for form, GET for URL's')
     *
     * @param $method string GET OR POST (default: GET)
     * @return string html output of params for $method method
     */
    public function getHtmlParamList($method = 'GET')
    {
        if ( empty($this->htmlParamList) ) return '';

        $html = '';

        if ( $method == 'POST' )
        {
            foreach ( $this->htmlParamList as $name => $value )
            {
                $html .= '<input type="hidden" name="' . htmlspecialchars($name) . '" value="' . htmlspecialchars($value) . '" />' . "\n" ;
            }
        }
        else // GET
        {
            $params = array();
            foreach ( $this->htmlParamList as $name => $value )
            {
                $params[] = rawurlencode($name) . '=' . rawurlencode($value);
            }

            $html = implode('&amp;', $params );
        }

        return $html;
    }
    

    /**
     * Build progress param url
     *
     * @return string url
     */
    public function buildProgressUrl ()
    {
        $url = $_SERVER['PHP_SELF'] . '?cmd=exEdit';

        $paramList = array();

        $paramList['category_name']                = $this->name;
        $paramList['category_code']                = $this->code;
        $paramList['category_idParent']            = $this->idParent;
        $paramList['category_rank']                = $this->rank;
        $paramList['category_visible']             = $this->visible;
        $paramList['category_canHavecoursesChild'] = $this->canHavecoursesChild;

        $paramList = array_merge($paramList, $this->htmlParamList);

        foreach ($paramList as $key => $value)
        {
            $url .= '&amp;' . rawurlencode($key) . '=' . rawurlencode($value);
        }

        return $url;
    }
}

?>