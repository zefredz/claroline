<?php # -$Id$

// Confirm javascript code

$htmlHeadXtra[] =
          "<script type=\"text/javascript\">
           function confirm_delete(name)
           {
               if (confirm('". clean_str_for_javascript(get_lang('AreYouSureToDelete')) . "' + name + ' ?'))
               {return true;}
               else
               {return false;}
           }

           function confirm_empty(name)
           {
               if (confirm('". clean_str_for_javascript(get_lang('ConfirmEmptyForum')) . "' + name + ' ?'))
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
    	   $dialogBox .= '<p>'.$langcatcreated.'</p>'."\n";
    	}
    	else
    	{
    	    $dialogBox .= '<p>'.get_lang('UnableCreateCategory').'</p>'."\n";
    	    $cmd = 'rqMkCat';
    	}
	}
	else
	{
	     $dialogBox .= '<p>'.get_lang('MissingFields').'</p>'."\n";
    	 $cmd = 'rqMkCat';
	}
}

if ( $cmd == 'rqMkCat' )
{
    if ( isset($_REQUEST['catName']) ) $catName = $_REQUEST['catName'];
    else                               $catName = '';

    $dialogBox .= '<h4>'.get_lang('AddCategory').'</h4>'
               .  '<form action="'.$_SERVER['PHP_SELF'].'" method="POST">'."\n"
               .  '<input type="hidden" name="claroFormId" value="'.uniqid('').'" />'."\n"
               .  '<input type="hidden" name="cmd" value="exMkCat" />'."\n"
               .  '<label for="catName">'.get_lang('Name').' : </label><br />'."\n"
               .  '<input type="text" name="catName" id="catName"'
               .  ' value="' . $catName . '" /><br />'."\n"
               .  '<input type="submit" value="'.get_lang('Ok').'" /> '
               .  claro_disp_button($_SERVER['PHP_SELF'], get_lang('Cancel'))
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
	           $dialogBox .= '<p>'.get_lang('ForumCreated').'</p>'."\n";
	        }
	        else
	        {                   
	           $dialogBox .= '<p>'.get_lang('UnableCreateForum').'</p>'."\n";
	           $cmd        = 'rqMkForum';
	        }
    }
	else 
	{
        $dialogBox .= '<p>'.get_lang('MissingFields').'</p>'."\n";
	    $cmd        = 'rqMkForum';
	}
}

if ( $cmd == 'rqMkForum' )
{
    $formCategoryList = get_category_list();

    if ( count($formCategoryList) > 0 )
    {
        $catSelectBox = get_lang('Category') . ' : <br />'."\n"
                       .'<select name="forumCatId">';

        foreach($formCategoryList as $thisFormCategory)
        {
            $catSelectBox .= '<option  value="'.$thisFormCategory['cat_id'].'">'
                          .  $thisFormCategory['cat_title']
                          .'</option>';
        }

        $catSelectBox .= '</select><br />'."\n";
    }
    else
    {
        $catSelectBox = '';
    }

    $reqForumName = isset($_REQUEST['forumName']) ? $_REQUEST['forumName'] : '';
    $reqForumDesc = isset($_REQUEST['forumDesc']) ? $_REQUEST['forumDesc'] : '';

    $reqForumPostUnallowedState = isset($_REQUEST['forumPostUnallowed']) ?
                                  ' checked ' : '';


    $dialogBox .= '<h4>'.get_lang('AddForum').'</h4>'
               .'<form action="'.$_SERVER['PHP_SELF'].'" method="POST">'."\n"
               .'<input type="hidden" name="cmd" value="exMkForum" />'."\n"
               .'<input type="hidden" name="claroFormId" value="'.uniqid('').'" />'."\n"
               .'<label for="forumName">'.get_lang('Name').': </label><br />'."\n"
               .'<input type="text" name="forumName" id="forumName"'
               .' value="'.$reqForumName.'" /><br />'."\n"
               .'<label for="forumDesc">' . get_lang('Description') . ' : </label><br />'."\n"
               .'<textarea name="forumDesc" id="forumDesc" cols="50" rows="3">'."\n"
               .$reqForumDesc
               .'</textarea><br />'."\n"
               .$catSelectBox."\n"
               .'<br />'."\n"
               .'<input type="checkbox" id="forumPostUnallowed" name="forumPostUnallowed" '.$reqForumPostUnallowedState.' />'."\n"
               .'<label for="forumPostUnallowed">'.get_lang('Locked').' <small>('.get_lang('NoPostAllowed').')</small></label><br />'."\n"
               .'<br />'."\n"
               .'<input type="submit" value="'.get_lang('Ok').'" />'."\n"
               . claro_disp_button($_SERVER['PHP_SELF'], get_lang('Cancel'))
               .'</form>'."\n\n";
}

if ( $cmd == 'exEdCat' )
{
    if ( trim($_REQUEST['catName']) != '' )
    {
        if ( update_category_title( $_REQUEST['catId'], $_REQUEST['catName'] ) )
        {
            $dialogBox .= '<p>'.get_lang('CategoryUpdated').'</p>'."\n";
        }
        else
        {
            $dialogBox .= '<p>'.get_lang('UnableToUpdateCategory').'</p>'."\n";
        }
    }
    else
    {
        $dialogBox .= '<p>'.get_lang('MissingFields').'</p>'."\n";
        $cmd        = 'rqEdCat';
    }
}

if ( $cmd == 'rqEdCat' )
{
    $categorySettingList = get_category_settings($_REQUEST['catId']);

    if ( $categorySettingList )
    {
        $dialogBox .= '<h4>'.get_lang('EditCategory').'</h4>'."\n"
               .  '<form action="'.$_SERVER['PHP_SELF'].'" method="POST">'."\n"
               .  '<input type="hidden" name="claroFormId" value="'.uniqid('').'" />'."\n"
               .  '<input type="hidden" name="catId" value="'.$categorySettingList['cat_id'].'" />'."\n"
               .  '<input type="hidden" name="cmd" value="exEdCat" />'."\n"
               .  '<label for="catName">'.get_lang('Name').' : </label><br />'."\n"
               .  '<input type="text" name="catName" id="catName"'
               .  ' value="'.$categorySettingList['cat_title'].'" /><br />'."\n"
               .  '<input type="submit" value="'.get_lang('Ok').'" /> '
               .  claro_disp_button($_SERVER['PHP_SELF'], get_lang('Cancel'))
               .  '</form>'."\n"
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
            $dialogBox .= '<p>'.get_lang('ForumUpdated').'</p>'."\n";
        }
        else
        {
            $dialogBox .= '<p>'.get_lang('UnableToUpdateForum').'</p>'."\n";
        }
    }
    else 
    {
        $dialogBox .= '<p>'.get_lang('MissingFields').'</p>'."\n";
        $cmd        = 'rqEdForum';
    }
}

if ( $cmd == 'rqEdForum' )
{
    $forumSettingList = get_forum_settings($_REQUEST['forumId']);

    $formCategoryList = get_category_list();

    if ( count($formCategoryList) > 0 )
    {
        $catSelectBox = get_lang('Category') . ' : <br />'."\n"
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

        $catSelectBox .= '</select><br />'."\n";
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

    $dialogBox .= '<h4>'.get_lang('AddForum').'</h4>'."\n"
               .'<form action="'.$_SERVER['PHP_SELF'].'" method="POST">'."\n"
               .'<input type="hidden" name="cmd" value="exEdForum" />'."\n"
               .'<input type="hidden" name="claroFormId" value="'.uniqid('').'" />'."\n"
               .'<input type="hidden" name="forumId" value="'.$forumSettingList['forum_id'].'" />'."\n"
               .'<label for="forumName">'.get_lang('Name').': </label><br />'."\n"
               .'<input type="text" name="forumName" id="forumName"'
               .' value="'.htmlspecialchars($formForumNameValue).'" /><br />'."\n"
               .'<label for="forumDesc">' . get_lang('Description') . ' : </label><br />'."\n"
               .'<textarea name="forumDesc" id="forumDesc" cols="50" rows="3">'."\n"
               .htmlspecialchars($formForumDescriptionValue)
               .'</textarea><br />'."\n"
               .$catSelectBox."\n"
               .'<br />'."\n"
               .'<input type="checkbox" id="forumPostUnallowed" name="forumPostUnallowed" '.$formForumPostUnallowedState.' />'."\n"
               .'<label for="forumPostUnallowed">'.get_lang('Locked').' <small>('.get_lang('NoPostAllowed').')</small></label><br />'."\n"
               .'<br />'."\n"
               .'<input type="submit" value="'.get_lang('Ok').'" /> '."\n"
               . claro_disp_button($_SERVER['PHP_SELF'], get_lang('Cancel'))
               .'</form>'."\n\n";

}

if ( $cmd == 'exDelCat' )
{
    if ( delete_category($_REQUEST['catId']) )
    {
    	$dialogBox .= '<p>'.get_lang('CategoryDeleted').'</p>'."\n";
    }
    else
    {
    	$dialogBox .= '<p>'.get_lang('UnableDeleteCategory').'</p>'."\n";

        if ( claro_failure::get_last_failure() == 'GROUP_FORUMS_CATEGORY_REMOVALE_FORBIDDEN' )
        {
            $dialogBox .= '<p>'.get_lang('UnableDeleteGroupCategoryForum').'</p>'."\n";
        }
        elseif(claro_failure::get_last_failure() == 'GROUP_FORUM_REMOVALE_FORBIDDEN')
        {
        	$dialogBox .= '<p>'.get_lang('CannotRemoveGroupForum').'</p>' ;
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
    	    $dialogBox .= '<p>'.get_lang('ForumDeleted').'</p>'."\n";
    	}
    	else
    	{
    	    $dialogBox .= '<p>'.get_lang('UnableDeleteForum').'</p>'."\n";
    	}
    }
    else
    {
            $dialogBox .= '<p>'.get_lang('CannotRemoveGroupForum').'</p>'."\n";
    }
}

if ( $cmd == 'exEmptyForum' )
{
	if ( delete_all_post_in_forum($_REQUEST['forumId']) )
	{
	    $dialogBox .= '<p>'.get_lang('ForumEmptied').'</p>'."\n";
	}
	else
	{
	    $dialogBox .= '<p>'.get_lang('UnableToEmptyForum').'</p>'."\n";
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
