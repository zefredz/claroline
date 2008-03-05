<?php // $Id$
/**
 * CLAROLINE
 *
 * This script prupose to user to edit his own profile
 *
 * @version 1.8 $Revision$
 *
 * @copyright 2001-2007 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @see http://www.claroline.net/wiki/Auth/
 *
 * @author Claro Team <cvs@claroline.net>
 *
 * @package Auth
 *
 */


class mystats extends portlet
{
    function __construct()
    {
        $this->title = get_lang('My statistics');
        $this->content = 'le contenu de mon portlet';
    }
    
    function renderContent()
    {
        
        $output = '';
        
        $userId = claro_get_current_user_id();
        $userData = user_get_properties( $userId );
        $userCourseList = get_user_course_list( $userId, true );
        $courseId = claro_get_current_course_id();

    	if( !is_array($userCourseList) )
    	{
    		$userCourseList = array();
    	}
        
        $output .= '<ul id="navlist">' . "\n"
    	.	 '<li><a '.(empty($courseId)?'class="current"':'').' href="../tracking/userLog.php?userId='.$userId.'">'.get_lang('Platform').'</a></li>' . "\n";

    	foreach( $userCourseList as $course )
    	{
    		if( $course['sysCode'] == $courseId ) 	$class = 'class="current"';
    		else										$class = '';

    		$output .= ' <li>'
    		.	 '<a '.$class.' href=../tracking/userLog.php?userId='.$userId.'&amp;courseId='.$course['sysCode'].'>'.$course['title'].'</a>'
    		.	 '</li>' . "\n";
    	}

    	$output .= '</ul>' . "\n\n";
        
        $this->content = $output;
        
        return $this->content;
    }
    
    function renderTitle()
    {
        return $this->title;
    }
}

?>