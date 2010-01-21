<?php

/**
 * CLAROLINE
 *
 * Management tools for categories' tree
 *
 * @version 1.9 $Revision: 11765 $
 * @copyright 2001-2010 Universite catholique de Louvain (UCL)
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 * @see http://www.claroline.net/wiki/index.php/CLTREE
 * @package 
 * @author Antonin Bourguignon <antonin.bourguignon@claroline.net>
 */

// Reset session variables
$cidReset = true; // course id
$gidReset = true; // group id
$tidReset = true; // tool id

// Load Claroline kernel
require_once dirname(__FILE__) . '/../inc/claro_init_global.inc.php';

// Security check: is the user logged as administrator ?
if ( ! claro_is_user_authenticated() ) claro_disp_auth_form();
if ( ! claro_is_platform_admin() ) claro_die(get_lang('Not allowed'));

// Initialisation of global variables and used classes and libraries
require_once get_path('incRepositorySys') . '/lib/claroCategory.class.php';

// Instanciate dialog box
$dialogBox = new DialogBox();

// DB tables definition
$tbl_mdb_names = claro_sql_get_main_tbl();
$tbl_class     = $tbl_mdb_names['category_dev'];

// Build the breadcrumb
ClaroBreadCrumbs::getInstance()->prepend( get_lang('Administration'), get_path('rootAdminWeb') );
$nameTools = get_lang('Categories');

// Get the cmd and id arguments
$cmd   = isset($_REQUEST['cmd'])?$_REQUEST['cmd']:null;
$id    = isset($_REQUEST['categoryId'])?$_REQUEST['categoryId']:null;

// Initialize output
$out = '';


switch ( $cmd )
{
    // Delete an existing category
    case 'exDelete' :
        // Code
    break;
    
    // Display form to create a new category
    case 'rqAdd' :
        // Code
    break;
    
    // Create a new category
    case 'exAdd' :
        // Code
    break;
    
    // Display form to edit a category
    case 'rqEdit' :
        // Code
    break;
    
    // Edit a new category
    case 'exEdit' :
        // Code
    break;
    
    // Display form to move a category in the tree (change parent or rank)
    case 'rqMove' :
        // Code
    break;
    
    // Move a category in the tree (change parent or rank)
    case 'exMove' :
        // Code
    break;
    
    // Display form to shift or displace a category
    case 'rqMoveUp' :
        // Code
    break;
    
    // Shift or displace category
    case 'exMoveUp' :
        // Code
    break;
    
    // Display form to shift or displace a category
    case 'rqMoveDown' :
        // Code
    break;
    
    // Shift or displace category
    case 'exMoveDown' :
        // Code
    break;
    
    // Change the visibility of a category
    case 'exVisibility' : 
        $category = new claroCategory(null, null, null, null, null, null, null, null);
        $category->load($id);
        if( $category->swapVisibility())
        {
            $dialogBox->success( get_lang('Category\'s visibility modified') );
        }
        else 
        {
            switch ( claro_failure::get_last_failure() )
            {
                case 'category_not_found' :
                    $dialogBox->error( get_lang('Error : Category not found') );
                    break;
            }
        }
    break;
}

// Display dialog box
$out .= $dialogBox->render();
		
// Display page title
$out .= claro_html_tool_title($nameTools);

// Display categories array
$categories = claroCategory::fetchAllCategories();

    // "Create category" link
$out .= 
	 '<p>'
.    '<a class="claroCmd" href="' . $_SERVER['PHP_SELF'] . '?cmd=rqAdd">'
.    '<img src="' . get_icon_url('category') . '" />' . get_lang('Create a category')
.    '</a>'
.    '</p>' . "\n";

    // Array header
$out .= 
	 '<table class="claroTable emphaseLine" width="100%" border="0" cellspacing="2">' . "\n"
.    '<thead>' . "\n"
.    '<tr class="headerX">' . "\n"
     // Array titles
.    '<th>' . get_lang('Category label') . '</th>' . "\n"
.    '<th>' . get_lang('Courses') . '</th>' . "\n"
.    '<th>' . get_lang('Visibility') . '</th>' . "\n"
.    '<th>' . get_lang('Edit') . '</th>' . "\n"
.    '<th>' . get_lang('Move') . '</th>' . "\n"
.    '<th>' . get_lang('Delete') . '</th>' . "\n"
.    '<th colspan="2">' . get_lang('Order') . '</th>'."\n"
.    '</tr>' . "\n"
.    '</thead>' . "\n";

    // Array body
$out .= 
	'<tbody>' . "\n";

foreach ($categories as $elmt)
{
    $out .=
    	'<tr>'
	.   '<td>' . str_repeat('&nbsp;', 4*$elmt['level']) . $elmt['name'] . ' (' . $elmt['code'] . ')</td>'
	.   '<td align="center">' . $elmt['nbCourses'] . '</td>'
	.   '<td align="center">'
	.   	'<a href="' . $_SERVER['PHP_SELF'] . '?cmd=exVisibility&amp;categoryId=' . $elmt['id'] . '">' . "\n"
    .   	'<img src="' . get_icon_url($elmt['visible']?'visible':'invisible') . '" alt="Change visibility" />' . "\n"
    .   	'</a>'
    .   '</td>'
	.   '<td align="center">'
	.   	'<a href="' . $_SERVER['PHP_SELF'] . '?cmd=rqEdit&amp;categoryId=' . $elmt['id'] . '">' . "\n"
    .   	'<img src="' . get_icon_url('edit') . '" alt="Edit category" />' . "\n"
    .   	'</a>'
    .   '</td>'
	.   '<td align="center">'
	.   	'<a href="' . $_SERVER['PHP_SELF'] . '?cmd=rqMove&amp;categoryId=' . $elmt['id'] . '">' . "\n"
    .   	'<img src="' . get_icon_url('move') . '" alt="Move category" />' . "\n"
    .   	'</a>'
    .   '</td>'
	.   '<td align="center">'
	.   	'<a href="' . $_SERVER['PHP_SELF'] . '?cmd=rqDelete&amp;categoryId=' . $elmt['id']
	.		' onclick="return confirmation(\'' . clean_str_for_javascript($elmt['name']) . '\');">' . "\n"
    .   	'<img src="' . get_icon_url('delete') . '" alt="Delete category" />' . "\n"
    .   	'</a>'
    .   '</td>'
	.   '<td align="center">'
	.   	'<a href="' . $_SERVER['PHP_SELF'] . '?cmd=rqMoveUp&amp;categoryId=' . $elmt['id'] . '">' . "\n"
    .   	'<img src="' . get_icon_url('move_up') . '" alt="Move up category" />' . "\n"
    .   	'</a>'
    .   '</td>'
	.   '<td align="center">'
	.   	'<a href="' . $_SERVER['PHP_SELF'] . '?cmd=rqMoveDown&amp;categoryId=' . $elmt['id'] . '">' . "\n"
    .   	'<img src="' . get_icon_url('move_down') . '" alt="Move down category" />' . "\n"
    .   	'</a>'
    .   '</td>'
    .   '</tr>';
}

$out .= 
	'</tbody>' 
.   '</table>' . "\n";

// Append output
$claroline->display->body->appendContent($out);

// Generate output
echo $claroline->display->render();

?>