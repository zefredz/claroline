<?php // $Id$
if ( ! defined('CLARO_INCLUDE_ALLOWED') ) die('---');

// Confirm javascript code

$htmlHeadXtra[] =
          "<script type=\"text/javascript\">
           function confirm_delete(name)
           {
               if (confirm('". clean_str_for_javascript($langAreYouSureToDelete) . "' + name + ' ?'))
               {return true;}
               else
               {return false;}
           }

           function confirm_empty(name)
           {
               if (confirm('". clean_str_for_javascript($langConfirmEmptyForum) . "' + name + ' ?'))
               {return true;}
               else
               {return false;}
           }
           </script>";

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
    	    $dialogBox .= '<p>'.$langUnableCreateCategory.'</p>'."\n";
    	    $cmd = 'rqMkCat';
    	}
	}
	else
	{
	     $dialogBox .= '<p>'.$langMissingFields.'</p>'."\n";
    	 $cmd = 'rqMkCat';
	}
}

if ( $cmd == 'rqMkCat' )
{
    if ( isset($_REQUEST['catName']) ) $catName = $_REQUEST['catName'];
    else                               $catName = '';

    $dialogBox .= '<h4>'.$langAddCategory.'</h4>'
               .  '<form action="'.$_SERVER['PHP_SELF'].'" method="POST">'."\n"
               .  '<input type="hidden" name="claroFormId" value="'.uniqid('').'" />'."\n"
               .  '<input type="hidden" name="cmd" value="exMkCat" />'."\n"
               .  '<label for="catName">'.$langName.' : </label><br />'."\n"
               .  '<input type="text" name="catName" id="catName"'
               .  ' value="' . $catName . '" /><br />'."\n"
               .  '<input type="submit" value="'.$langOk.'" /> '
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
	           $dialogBox .= '<p>'.$langForumCreated.'</p>'."\n";
	        }
	        else
	        {
	           $dialogBox .= '<p>'.$langUnableCreateForum.'</p>'."\n";
	           $cmd        = 'rqMkForum';
	        }
    }
	else
	{
        $dialogBox .= '<p>'.$langMissingFields.'</p>'."\n";
	    $cmd        = 'rqMkForum';
	}
}

if ( $cmd == 'rqMkForum' )
{
    $formCategoryList = get_category_list();

    if ( count($formCategoryList) > 0 )
    {
        $catSelectBox = $langCategory . ' : <br />'."\n"
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


    $dialogBox .= '<h4>'.$langAddForum.'</h4>'
               .'<form action="'.$_SERVER['PHP_SELF'].'" method="POST">'."\n"
               .'<input type="hidden" name="cmd" value="exMkForum" />'."\n"
               .'<input type="hidden" name="claroFormId" value="'.uniqid('').'" />'."\n"
               .'<label for="forumName">'.$langName.': </label><br />'."\n"
               .'<input type="text" name="forumName" id="forumName"'
               .' value="'.$reqForumName.'" /><br />'."\n"
               .'<label for="forumDesc">' . $langDescription . ' : </label><br />'."\n"
               .'<textarea name="forumDesc" id="forumDesc" cols="50" rows="3">'."\n"
               .$reqForumDesc
               .'</textarea><br />'."\n"
               .$catSelectBox."\n"
               .'<br />'."\n"
               .'<input type="checkbox" id="forumPostUnallowed" name="forumPostUnallowed" '.$reqForumPostUnallowedState.' />'."\n"
               .'<label for="forumPostUnallowed">'.$langLocked.' <small>('.$langNoPostAllowed.')</small></label><br />'."\n"
               .'<br />'."\n"
               .'<input type="submit" value="'.$langOk.'" />'."\n"
               . claro_disp_button($_SERVER['PHP_SELF'], $langCancel)
               .'</form>'."\n\n";
}

if ( $cmd == 'exEdCat' )
{
    if ( trim($_REQUEST['catName']) != '' )
    {
        if ( update_category_title( $_REQUEST['catId'], $_REQUEST['catName'] ) )
        {
            $dialogBox .= '<p>'.$langCategoryUpdated.'</p>'."\n";
        }
        else
        {
            $dialogBox .= '<p>'.$langUnableToUpdateCategory.'</p>'."\n";
        }
    }
    else
    {
        $dialogBox .= '<p>'.$langMissingFields.'</p>'."\n";
        $cmd        = 'rqEdCat';
    }
}

if ( $cmd == 'rqEdCat' )
{
    $categorySettingList = get_category_settings($_REQUEST['catId']);

    if ( $categorySettingList )
    {
        $dialogBox .= '<h4>'.$langEditCategory.'</h4>'."\n"
               .  '<form action="'.$_SERVER['PHP_SELF'].'" method="POST">'."\n"
               .  '<input type="hidden" name="claroFormId" value="'.uniqid('').'" />'."\n"
               .  '<input type="hidden" name="catId" value="'.$categorySettingList['cat_id'].'" />'."\n"
               .  '<input type="hidden" name="cmd" value="exEdCat" />'."\n"
               .  '<label for="catName">'.$langName.' : </label><br />'."\n"
               .  '<input type="text" name="catName" id="catName"'
               .  ' value="'.$categorySettingList['cat_title'].'" /><br />'."\n"
               .  '<input type="submit" value="'.$langOk.'" /> '
               .  claro_disp_button($_SERVER['PHP_SELF'], $langCancel)
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
            $dialogBox .= '<p>'.$langForumUpdated.'</p>'."\n";
        }
        else
        {
            $dialogBox .= '<p>'.$langUnableToUpdateForum.'</p>'."\n";
        }
    }
    else
    {
        $dialogBox .= '<p>'.$langMissingFields.'</p>'."\n";
        $cmd        = 'rqEdForum';
    }
}

if ( $cmd == 'rqEdForum' )
{
    $forumSettingList = get_forum_settings($_REQUEST['forumId']);

    $formCategoryList = get_category_list();

    if ( count($formCategoryList) > 0 )
    {
        $catSelectBox = $langCategory . ' : <br />'."\n"
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

    $dialogBox .= '<h4>'.$langAddForum.'</h4>'."\n"
               .'<form action="'.$_SERVER['PHP_SELF'].'" method="POST">'."\n"
               .'<input type="hidden" name="cmd" value="exEdForum" />'."\n"
               .'<input type="hidden" name="claroFormId" value="'.uniqid('').'" />'."\n"
               .'<input type="hidden" name="forumId" value="'.$forumSettingList['forum_id'].'" />'."\n"
               .'<label for="forumName">'.$langName.': </label><br />'."\n"
               .'<input type="text" name="forumName" id="forumName"'
               .' value="'.htmlspecialchars($formForumNameValue).'" /><br />'."\n"
               .'<label for="forumDesc">' . $langDescription . ' : </label><br />'."\n"
               .'<textarea name="forumDesc" id="forumDesc" cols="50" rows="3">'."\n"
               .htmlspecialchars($formForumDescriptionValue)
               .'</textarea><br />'."\n"
               .$catSelectBox."\n"
               .'<br />'."\n"
               .'<input type="checkbox" id="forumPostUnallowed" name="forumPostUnallowed" '.$formForumPostUnallowedState.' />'."\n"
               .'<label for="forumPostUnallowed">'.$langLocked.' <small>('.$langNoPostAllowed.')</small></label><br />'."\n"
               .'<br />'."\n"
               .'<input type="submit" value="'.$langOk.'" /> '."\n"
               . claro_disp_button($_SERVER['PHP_SELF'], $langCancel)
               .'</form>'."\n\n";

}

if ( $cmd == 'exDelCat' )
{
    if ( delete_category($_REQUEST['catId']) )
    {
    	$dialogBox .= '<p>'.$langCategoryDeleted.'</p>'."\n";
    }
    else
    {
    	$dialogBox .= '<p>'.$langUnableDeleteCategory.'</p>'."\n";

        if ( claro_failure::get_last_failure() == 'GROUP_FORUMS_CATEGORY_REMOVALE_FORBIDDEN' )
        {
            $dialogBox .= '<p>'.$langUnableDeleteGroupCategoryForum.'</p>'."\n";
        }
        elseif(claro_failure::get_last_failure() == 'GROUP_FORUM_REMOVALE_FORBIDDEN')
        {
        	$dialogBox .= '<p>'.$langCannotRemoveGroupForum.'</p>' ;
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
    	    $dialogBox .= '<p>'.$langForumDeleted.'</p>'."\n";
    	}
    	else
    	{
    	    $dialogBox .= '<p>'.$langUnableDeleteForum.'</p>'."\n";
    	}
    }
    else
    {
            $dialogBox .= '<p>'.$langCannotRemoveGroupForum.'</p>'."\n";
    }
}

if ( $cmd == 'exEmptyForum' )
{
	if ( delete_all_post_in_forum($_REQUEST['forumId']) )
	{
	    $dialogBox .= '<p>'.$langForumEmptied.'</p>'."\n";
	}
	else
	{
	    $dialogBox .= '<p>'.$langUnableToEmptyForum.'</p>'."\n";
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
