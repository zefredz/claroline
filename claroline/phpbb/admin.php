<?php # -$Id$

// Confirm javascript code

$htmlHeadXtra[] =
          "<script>
           function confirm_delete(name)
           {
               if (confirm('". clean_str_for_javascript($langAreYouSureToDelete) . "' + name + ' ?'))
               {return true;}
               else
               {return false;}
           }

           function confirm_empty(name)
           {
               if (confirm('". clean_str_for_javascript('Delete all messages of ') . "' + name + ' ?'))
               {return true;}
               else
               {return false;}
           }
           </script>";

if( (bool) stristr($_SERVER['PHP_SELF'], basename(__FILE__) ) ) die();
if( ! $is_allowedToEdit) die();

if ( isset($_REQUEST['cmd']) ) $cmd = $_REQUEST['cmd']; else $cmd = null;

if ( $cmd == 'exMkCat' )
{
	if ( trim($_REQUEST['catName']) != '')
	{
        if ( create_category( trim($_REQUEST['catName']) ) )
    	{
    	   $dialogBox .= $langcatcreated . "\n";
    	}
    	else
    	{
    	    $dialogBox .= '<p>Unable to create category</p>' . "\n";
    	    $cmd = 'rqMkCat';
    	}
	}
	else
	{
	     $dialogBox .= '<p>Missing field(s)</p>' . "\n";
    	 $cmd = 'rqMkCat';
	}
}

if ( $cmd == 'rqMkCat' )
{
    if ( isset($_REQUEST['catName']) ) $catName = $_REQUEST['catName'];
    else                               $catName = '';

    $dialogBox .= '<h4>'.$langAddCategory.'</h4>'
               .  '<form action="'.$_SERVER['PHP_SELF'].'" method="POST">'."\n"
               .  '<input type="hidden" name="claroFormId" value="'.uniqid(rand()).'">'
               .  '<input type="hidden" name="cmd" value="exMkCat">'
               .  '<label for="catName">'.$langName.' : </label><br />'
               .  '<input type="text" name="catName" id="catName"'
               .  ' value="' . $catName . '"><br />'
               .  '<input type="submit" value="'.$langOk.'"> '
               .  claro_disp_button($_SERVER['PHP_SELF'], $langCancel)
               .  '</form>'
               .  "\n";
}

if ( $cmd == 'exMkForum' )
{
    $forumPostAllowed = ( isset($_REQUEST['forumPostUnallowed']) ) ? false : true;

    if (   ( ( trim($_REQUEST['forumName']) != '') )
	    && (   0 < (int) $_REQUEST['forumCatId']   )  )
	{
            if ( create_forum(trim($_REQUEST['forumName']), 
	                          trim($_REQUEST['forumDesc']), 
	                          $forumPostAllowed,
	                          (int) $_REQUEST['forumCatId'] ) )
	        {
	           $dialogBox .= 'Forum created';   
	        }
	        else
	        {                   
	           $dialogBox .= 'Unable to create forum';
	           $cmd        = 'rqMkForum';
	        }
    }
	else 
	{
        $dialogBox .= 'Missing field(s)';
	    $cmd        = 'rqMkForum';
	}
}

if ( $cmd == 'rqMkForum' )
{
    $formCategoryList = get_category_list();

    if ( count($formCategoryList) > 0 )
    {
        $catSelectBox = $langCategory . ' : <br />'
                       .'<select name="forumCatId">';

        foreach($formCategoryList as $thisFormCategory)
        {
            $catSelectBox .= '<option  value="'.$thisFormCategory['cat_id'].'">'
                          .  $thisFormCategory['cat_title']
                          .'</option>';
        }

        $catSelectBox .= '</select><br />';
    }
    else
    {
        $catSelectBox = '';
    }

    $reqForumName = isset($_REQUEST['forumName']) ? $_REQUEST['forumName'] : '';
    $reqForumDesc = isset($_REQUEST['forumDesc']) ? $_REQUEST['forumDesc'] : '';

    $reqForumPostUnallowedState = isset($_REQUEST['forumPostUnallowed']) ?
                                  ' checked ' : '';


    $dialogBox .= '<h4>Add Forum</h4>'
               .'<form action="'.$_SERVER['PHP_SELF'].'" method="POST">'."\n"
               .'<input type="hidden" name="cmd" value="exMkForum">'
               .'<input type="hidden" name="claroFormId" value="'.uniqid(rand()).'">'
               .'<label for="forumName">'.$langName.': </label><br />'
               .'<input type="text" name="forumName" id="forumName"'
               .' value="'.$reqForumName.'"><br />'
               .'<label for="forumDesc">' . $langDescription . ' : </label><br />'
               .'<textarea name="forumDesc" id="forumDesc" cols="50" rows="3">'
               .$reqForumDesc
               .'</textarea><br />'
               .$catSelectBox
               .'<br />'
               .'<input type="checkbox" name="forumPostUnallowed" '.$reqForumPostUnallowedState.'>'
               .'Locked <small>(No new post allowed)</small><br />'
   // Technical Note : It seems impossible to add an ID to a 
   // checkbox tag. Adding this ID seems to prevent the checkbox 
   // state to be sent frim the browser(at least in Mozilla/Firefox).
   // So no <label> for the tag above for the moment ...
               .'<br />'
               .'<input type="submit" value="'.$langOk.'">     '
               . claro_disp_button($_SERVER['PHP_SELF'], $langCancel)
               .'</form>';
}

if ( $cmd == 'exEdCat' )
{
    if ( trim($_REQUEST['catName']) != '' )
    {
        if ( update_category_title( $_REQUEST['catId'], $_REQUEST['catName'] ) )
        {
            $dialogBox .= '<p>Category updated.</p>';
        }
        else
        {
            $dialogBox .= '<p>Unable to update category.</p>';
        }
    }
    else
    {
        $dialogBox .= '<p>Missing field</p>';
        $cmd        = 'rqEdCat';
    }
}

if ( $cmd == 'rqEdCat' )
{
    $categorySettingList = get_category_settings($_REQUEST['catId']);

    if ( $categorySettingList )
    {
        $dialogBox .= '<h4>Edit Category</h4>'
               .  '<form action="'.$_SERVER['PHP_SELF'].'" method="POST">'."\n"
               .  '<input type="hidden" name="claroFormId" value="'.uniqid(rand()).'">'
               .  '<input type="hidden" name="catId" value="'.$categorySettingList['cat_id'].'">'
               .  '<input type="hidden" name="cmd" value="exEdCat">'
               .  '<label for="catName">'.$langName.' : </label><br />'
               .  '<input type="text" name="catName" id="catName"'
               .  ' value="'.$categorySettingList['cat_title'].'"><br />'
               .  '<input type="submit" value="'.$langOk.'"> '
               .  claro_disp_button($_SERVER['PHP_SELF'], $langCancel)
               .  '</form>'
               .  "\n";
    }   
}

if ( $cmd == 'exEdForum' )
{
    $forumPostAllowed = ( isset($_REQUEST['forumPostUnallowed']) ) ? false : true;

    if ( trim($_REQUEST['forumName'] != '') )
    {   
        if ( update_forum_settings($_REQUEST['forumId'   ], $_REQUEST['forumName'], 
                                   $_REQUEST['forumDesc' ], $forumPostAllowed, 
                                   $_REQUEST['forumCatId']) )
        {
            $dialogBox .= '<p>Forum data updated.</p>';
        }
        else
        {
            $dialogBox .= '<p>Unable to update forum.</p>';
        }
    }
    else 
    {
        $dialogBox .= '<p>Missing Field(s).</p>';
        $cmd        = 'rqEdForum';
    }
}

if ( $cmd == 'rqEdForum' )
{
    $forumSettingList = get_forum_settings($_REQUEST['forumId']);

    $formCategoryList = get_category_list();

    if ( count($formCategoryList) > 0 )
    {
        $catSelectBox = $langCategory . ' : <br />'
                       .'<select name="forumCatId">';

        foreach( $formCategoryList as $thisFormCategory )
        {
            if ( $forumSettingList['cat_id'] == $thisFormCategory['cat_id'] )
            {
                $selectedState = ' selected="selected" ';
            }
            else
            {
                $selectedState = '';
                
            }
            $catSelectBox .= '<option  value="'.$thisFormCategory['cat_id'].'"'.$selectedState.'>'
                          .  htmlspecialchars($thisFormCategory['cat_title'])
                          .'</option>';
        }

        $catSelectBox .= '</select><br />';
    }
    else
    {
    	$catSelectBox = '';
    }

    $formForumNameValue        = isset($_REQUEST['forumName']) ? 
                                 $_REQUEST['forumName'] : $forumSettingList['forum_name'];
    
    $formForumDescriptionValue = isset($_REQUEST['forumDesc']) ?
                                 $_REQUEST['forumDesc'] : $forumSettingList['forum_desc'];

    $formForumPostUnallowedState = $_REQUEST['cmd'] == 'exEdForum' ?
                                    ( isset($_REQUEST['forumPostUnallowed']) ? ' checked ' : '' )
                                   :
                                    ( $forumSettingList['forum_access'] == 0 ? ' checked ' : '' );

    $dialogBox .= '<h4>Add Forum</h4>'
               .'<form action="'.$_SERVER['PHP_SELF'].'" method="POST">'."\n"
               .'<input type="hidden" name="cmd" value="exEdForum">'
               .'<input type="hidden" name="claroFormId" value="'.uniqid(rand()).'">'
               .'<input type="hidden" name="forumId" value="'.$forumSettingList['forum_id'].'">'
               .'<label for="forumName">'.$langName.': </label><br />'
               .'<input type="text" name="forumName" id="forumName"'
               .' value="'.htmlspecialchars($formForumNameValue).'"><br />'
               .'<label for="forumDesc">' . $langDescription . ' : </label><br />'
               .'<textarea name="forumDesc" id="forumDesc" cols="50" rows="3">'
               .htmlspecialchars($formForumDescriptionValue)
               .'</textarea><br />'
               .$catSelectBox
               .'<input type="checkbox" name="forumPostUnallowed" '.$formForumPostUnallowedState.'>'
               .'Locked <small>(No new post allowed)</small><br />'
   // Technical Note : It seems impossible to add an ID to a 
   // checkbox tag. Adding this ID seems to prevent the checkbox 
   // state to be sent frim the browser(at least in Mozilla/Firefox).
   // So no <label> for the tag above for the moment ...
               .'<br />'
               .'<input type="submit" value="'.$langOk.'"> '
               . claro_disp_button($_SERVER['PHP_SELF'], $langCancel)
               .'</form>';

}

if ( $cmd == 'exDelCat' )
{
    if ( delete_category($_REQUEST['catId']) )
    {
    	$dialogBox .= '<p>Category deleted.</p>';
    }
    else
    {
    	$dialogBox .= '<p>Unable to delete category.</p>';

        if ( claro_failure::get_last_failure() == 'GROUP_FORUMS_CATEGORY_REMOVALE_FORBIDDEN' )
        {
            $dialogBox .= '<p>Group forums category can not be deleted.</p>';
        }
    }
}

if ( $cmd == 'exDelForum' )
{
    $forumSettingList = get_forum_settings($_REQUEST['forumId']);
    
    if ( is_null($forumSettingList['idGroup']) )
    {
        if ( delete_forum ($_REQUEST['forumId']) )
    	{
    	    $dialogBox .= '<p>Forum deleted.</p>';
    	}
    	else
    	{
    	    $dialogBox .= '<p>Unable to delete Forum.</p>';
    	}
    }
    else
    {
            $dialogBox .= '<p>'
                       .  'You can not remove a group forum. '
                       .  'You have to remove the group first'
                       .  '</p>';   
    }
}

if ( $cmd == 'exEmptyForum' )
{
	if ( delete_all_post_in_forum($_REQUEST['forumId']) )
	{
	    $dialogBox .= '<p>forum emptied.</p>';  
	}
	else
	{
	    $dialogBox .= '<p>Unable to empty forum.</p>';
	}
}

if ( $cmd == 'exMvUpCat' )
{
    move_up_category($_REQUEST['catId']);
}

if ( $cmd == 'exMvDownCat')
{
	move_down_category($_REQUEST['catId']);
}

if ( $cmd == 'exMvUpForum' )
{
	move_up_forum($_REQUEST['forumId']);
}

if ( $cmd == 'exMvDownForum' )
{
	move_down_forum($_REQUEST['forumId']);
}

?>
