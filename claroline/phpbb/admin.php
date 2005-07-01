<?php # -$Id$

if( (bool) stristr($_SERVER['PHP_SELF'], basename(FILE) ) ) die();
if( ! $is_allowedToEdit) die();

if ( isset($_REQUEST['cmd']) ) $cmd = $_REQUEST['cmd'];

if ($cmd == 'exMkCat')
{
	if ( trim($_REQUEST['catName']) != '')
	{
        if ( create_category( trim($_REQUEST['catName']) ) )
    	{
    	   $dialogBox = $langcatcreated . "\n";
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

if($cmd == 'rqMkCat')
{
    $dialogBox .= '<h4>'.$langAddCategory.'</h4>'
               .  '<form action="'.$_SERVER['PHP_SELF'].'" method="POST">'."\n"
               .  '<input type="hidden" name="claroFormId" value="'.uniqid().'">'
               .  '<input type="hidden" name="cmd" value="exMkCat">'
               .  '<label for="catName">'.$langName.' : </label><br />'
               .  '<input type="text" name="catName" id="catName"'
               .  ' value="'.$_REQUEST['catName'].'"><br />'
               .  '<input type="submit" value="'.$langOk.'"> '
               .  claro_disp_button($_SERVER['PHP_SELF'], $langCancel)
               .  '</form>'
               .  "\n";
}

if($cmd == 'exMkForum')
{
    if (   ( ( trim($_REQUEST['forumName']) != '') )
	    && (   0 < (int) $_REQUEST['forumCatId']   )  )
	{
	 
            if ( create_forum(trim($_REQUEST['forumName']), 
	                          trim($_REQUEST['forumDesc']), 
	                            0,  // forum_type ... int he phpBB structure
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

if($cmd == 'rqMkForum')
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

    $dialogBox .= '<h4>Add Forum</h4>'
               .'<form action="'.$_SERVER['PHP_SELF'].'" method="POST">'."\n"
               .'<input type="hidden" name="cmd" value="exMkForum">'
               .'<input type="hidden" name="claroFormId" value="'.uniqid().'">'
               .'<label for="forumName">'.$langName.': </label><br />'
               .'<input type="text" name="forumName" id="forumName"'
               .' value="'.$_REQUEST['forumName'].'"><br />'
               .'<label for="forumDesc">' . $langDescription . ' : </label><br />'
               .'<textarea name="forumDesc" id="forumDesc" cols="50" rows="3">'
               .$_REQUEST['forumDesc']
               .'</textarea><br />'
               .$catSelectBox
               .'<br />'
               .'<input type="submit" value="'.$langOk.'">     '
               . claro_disp_button($_SERVER['PHP_SELF'], $langCancel)
               .'</form>';
}

if($cmd == 'exEdCat')
{
    if (trim($_REQUEST['catName']) != '')
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

if($cmd == 'rqEdCat')
{
    $categorySettingList = get_category_settings($_REQUEST['catId']);

    if ($categorySettingList)
    {
        $dialogBox .= '<h4>Edit Category</h4>'
               .  '<form action="'.$_SERVER['PHP_SELF'].'" method="POST">'."\n"
               .  '<input type="hidden" name="claroFormId" value="'.uniqid().'">'
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

if($cmd == 'exEdForum')
{
    if ( trim($_REQUEST['forumName'] != '') )
    {   
        if ( update_forum_settings($_REQUEST['forumId'   ], $_REQUEST['forumName'], 
                                   $_REQUEST['forumDesc' ], 0, 
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

if($cmd == 'rqEdForum')
{
	$forumSettingList = get_forum_settings($_REQUEST['forumId']);
	
	$formCategoryList = get_category_list();

    if (count($formCategoryList) > 0 )
    {
        $catSelectBox = $langCategory . ' : <br />'
                       .'<select name="forumCatId">';

        foreach($formCategoryList as $thisFormCategory)
        {
            if ($forumSettingList['cat_id'] == $thisFormCategory['cat_id'] )
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
                                 
    $dialogBox .= '<h4>Add Forum</h4>'
               .'<form action="'.$_SERVER['PHP_SELF'].'" method="POST">'."\n"
               .'<input type="hidden" name="cmd" value="exEdForum">'
               .'<input type="hidden" name="claroFormId" value="'.uniqid().'">'
               .'<input type="hidden" name="forumId" value="'.$forumSettingList['forum_id'].'">'
               .'<label for="forumName">'.htmlspecialchars($langName).': </label><br />'
               .'<input type="text" name="forumName" id="forumName"'
               .' value="'.$formForumNameValue.'"><br />'
               .'<label for="forumDesc">' . htmlspecialchars($langDescription) . ' : </label><br />'
               .'<textarea name="forumDesc" id="forumDesc" cols="50" rows="3">'
               .$formForumDescriptionValue
               .'</textarea><br />'
               .$catSelectBox
               .'<br />'
               .'<input type="submit" value="'.$langOk.'">     '
               . claro_disp_button($_SERVER['PHP_SELF'], $langCancel)
               .'</form>';
}

if($cmd == 'exDelCat')
{
    delete_category($_REQUEST['catId']);
}

if($cmd == 'exDelForum')
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

if ($cmd == 'exEmptyForum')
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

if( $cmd == 'exMvUpCat' )
{
    move_up_category($_REQUEST['catId']);
}

if ($cmd == 'exMvDownCat')
{
	move_down_category($_REQUEST['catId']);
}

if ( $cmd == 'exMvUpForum' )
{
	move_up_forum($_REQUEST['forumId']);
}

if( $cmd == 'exMvDownForum' )
{
	move_down_forum($_REQUEST['forumId']);
}

?>