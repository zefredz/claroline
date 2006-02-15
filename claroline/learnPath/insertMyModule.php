<?php // $Id$
/**
 * CLAROLINE 
 *
 * @version 1.8 $Revision$
 *
 * @copyright (c) 2001-2006 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @author Piraux Sébastien <pir@cerdecam.be>
 * @author Lederer Guillaume <led@cerdecam.be>
 *
 * @package CLLNP
 */

/*======================================
       CLAROLINE MAIN
 ======================================*/

$tlabelReq = 'CLLNP___';
require '../inc/claro_init_global.inc.php';

$is_AllowedToEdit = $is_courseAdmin;
if ( ! $_cid || ! $is_courseAllowed ) claro_disp_auth_form(true);
if ( ! $is_AllowedToEdit ) claro_die(get_lang('Not allowed'));

$interbredcrump[]= array ("url"=>"../learnPath/learningPathList.php", "name"=> get_lang('Learning path list'));
$interbredcrump[]= array ("url"=>"../learnPath/learningPathAdmin.php", "name"=> get_lang('Learning path admin'));
$nameTools = get_lang('Insert my module');

//header
include($includePath."/claro_init_header.inc.php");

// tables names
$TABLELEARNPATH         = $_course['dbNameGlu']."lp_learnPath";
$TABLEMODULE            = $_course['dbNameGlu']."lp_module";
$TABLELEARNPATHMODULE   = $_course['dbNameGlu']."lp_rel_learnPath_module";
$TABLEASSET             = $_course['dbNameGlu']."lp_asset";
$TABLEUSERMODULEPROGRESS= $_course['dbNameGlu']."lp_user_module_progress";

//lib of this tool
include($includePath."/lib/learnPath.lib.inc.php");

//lib of document tool
include($includePath."/lib/fileDisplay.lib.php");

// $_SESSION
if ( !isset($_SESSION['path_id']) )
{
    die ("<center> Not allowed ! (path_id not set :@ )</center>");
}

/*======================================
       CLAROLINE MAIN
 ======================================*/

// FUNCTION NEEDED TO BUILD THE QUERY TO SELECT THE MODULES THAT MUST BE AVAILABLE

// 1)  We select first the modules that must not be displayed because
// as they are already in this learning path

function buildRequestModules()
{

 global $TABLELEARNPATHMODULE;
 global $TABLEMODULE;
 global $TABLEASSET;

 $firstSql = "SELECT LPM.`module_id`
              FROM `".$TABLELEARNPATHMODULE."` AS LPM
              WHERE LPM.`learnPath_id` = ". (int)$_SESSION['path_id'];

 $firstResult = claro_sql_query($firstSql);

 // 2) We build the request to get the modules we need

 $sql = "SELECT M.*, A.`path`
         FROM `".$TABLEMODULE."` AS M
           LEFT JOIN `".$TABLEASSET."` AS A ON M.`startAsset_id` = A.`asset_id`
         WHERE M.`contentType` != \"SCORM\"
           AND M.`contentType` != \"LABEL\"";

 while ($list=mysql_fetch_array($firstResult))
 {
    $sql .=" AND M.`module_id` != ". (int)$list['module_id'];
 }
 
 //$sql .= " AND M.`contentType` != \"".CTSCORM_."\"";

 /** To find which module must displayed we can also proceed  with only one query.
  * But this implies to use some features of MySQL not available in the version 3.23, so we use
  * two differents queries to get the right list.
  * Here is how to proceed with only one

  $query = "SELECT *
             FROM `".$TABLEMODULE."` AS M
             WHERE NOT EXISTS(SELECT * FROM `".$TABLELEARNPATHMODULE."` AS TLPM
             WHERE TLPM.`module_id` = M.`module_id`)"; 
 */

  return $sql;

}//end function

// display title

echo claro_disp_tool_title(get_lang('Insert a module of the course'));

//COMMAND ADD SELECTED MODULE(S):

if (isset($_REQUEST['cmdglobal']) && ($_REQUEST['cmdglobal'] == 'add')) 
{

    // select all 'addable' modules of this course for this learning path

    $result = claro_sql_query(buildRequestModules());
    $atLeastOne = FALSE;
    $nb=0;
    while ($list = mysql_fetch_array($result))
    {
        // see if check box was checked
        if (isset($_REQUEST['check_'.$list['module_id']]) && $_REQUEST['check_'.$list['module_id']]) 
        {
            // find the order place where the module has to be put in the learning path
            $sql = "SELECT MAX(`rank`)
                    FROM `".$TABLELEARNPATHMODULE."`
                    WHERE learnPath_id = " . (int)$_SESSION['path_id'];
            $result2 = claro_sql_query($sql);

            list($orderMax) = mysql_fetch_row($result2);
            $order = $orderMax + 1;

            //create and call the insertquery on the DB to add the checked module to the learning path

            $insertquery="INSERT INTO `".$TABLELEARNPATHMODULE."`
                          (`learnPath_id`, `module_id`, `specificComment`, `rank`, `lock` )
                          VALUES (". (int)$_SESSION['path_id'].", ". (int)$list['module_id'].", '',".$order.", 'OPEN')";
            claro_sql_query($insertquery);

            $atleastOne = TRUE;
            $nb++;
        }
    }
     /*
     if ( !$atleastOne )
     {
         echo claro_html::message_box("No module selected !!");
     }
     */
} //end if ADD command

//STEP ONE : display form to add module of the course that are not in this path yet
// this is the same SELECT as "select all 'addable' modules of this course for this learning path"
// **BUT** normally there is less 'addable' modules here than in the first one

$result = claro_sql_query(buildRequestModules());

echo '<table class="claroTable" width="100%">'."\n"
       .'<thead>'."\n"
       .'<tr class="headerX">'."\n"
       .'<th width="10%">'
       .get_lang('Add')
       .'</th>'."\n"
       .'<th>'
       .get_lang('Module')
       .'</th>'."\n"
       .'</tr>'."\n"
       .'</thead>'."\n\n"
       .'<tbody>'."\n\n";

// Display available modules
echo '<form name="addmodule" action="'.$_SERVER['PHP_SELF'].'?cmdglobal=add">'."\n";

$atleastOne = FALSE;

while ($list=mysql_fetch_array($result))
{
    //CHECKBOX, NAME, RENAME, COMMENT
    if($list['contentType'] == CTEXERCISE_ ) 
        $moduleImg = "quiz.gif";
    else
        $moduleImg = choose_image(basename($list['path']));
        
    $contentType_alt = selectAlt($list['contentType']);
    
    echo '<tr>'."\n"
        .'<td align="center">'."\n"
        .'<input type="checkbox" name="check_'.$list['module_id'].'" id="check_'.$list['module_id'].'">'."\n"
        .'</td>'."\n"
        .'<td align="left">'."\n"
        .'<label for="check_'.$list['module_id'].'" ><img src="'.$imgRepositoryWeb.$moduleImg.'" alt="'.$contentType_alt.'" />'.$list['name'].'</label>'."\n"
        .'</td>'."\n"
        .'</tr>'."\n\n";

    // COMMENT

    if ($list['comment'] != null)
    {
        echo '<tr>'."\n"
            .'<td>&nbsp;</td>'."\n"
            .'<td>'."\n"
            .'<small>'.$list['comment'].'</small>'."\n"
            .'</td>'."\n"
            .'</tr>'."\n\n";
    }
    $atleastOne = TRUE;

}//end while another module to display

echo "\n".'</tbody>'."\n\n".'<tfoot>'."\n\n";

if ( !$atleastOne )
{
    echo '<tr>'."\n"
        .'<td colspan="2" align="center">'
        .get_lang('All modules of this course are already used in this learning path.')
        .'</td>'."\n"
        .'</tr>'."\n";
}
echo '<tr>'
    .'<td colspan="6"><hr noshade size="1"></td>'
    .'</tr>'."\n"
    ;
// Display button to add selected modules

if ( $atleastOne )
{
    echo '<tr>'."\n"
        .'<td colspan="2">'."\n"
        .'<input type="submit" value="'.get_lang('Add module(s)').'" />'."\n"
        .'<input type="hidden" name="cmdglobal" value="add">'."\n"
        .'</td>'."\n"
        .'</tr>'."\n";
}

echo "\n".'</tfoot>'."\n\n".'</form>'."\n".'</table>';

//####################################################################################\\
//################################## MODULES LIST ####################################\\
//####################################################################################\\

// display subtitle
echo claro_disp_tool_title(get_lang('Learning path content'));

// display back link to return to the LP administration
echo '<a href="learningPathAdmin.php">&lt;&lt;&nbsp;'.get_lang('Back to learning path administration').'</a>';

// display list of modules used by this learning path
display_path_content();

// footer
include($includePath."/claro_init_footer.inc.php");

?>
