<?php // $Id$
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.4.1 $Revision$                            |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2003 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      |   This program is free software; you can redistribute it and/or      |
      |   modify it under the terms of the GNU General Public License        |
      |   as published by the Free Software Foundation; either version 2     |
      |   of the License, or (at your option) any later version.             |
      +----------------------------------------------------------------------+
      | Authors: see 'credits' file                                          |
      +----------------------------------------------------------------------+
*/

$langFile='link';

require '../inc/claro_init_global.inc.php';

$nameTools=$langLinks;

$noPHP_SELF=true;

include($includePath.'/claro_init_header.inc.php');

if(!strstr($link,'/') && preg_match("/.html?$/",$link) && file_exists($coursesRepositoryWeb.$_course['path'].'/page/'.$link))
{
        //stats
        @include($includePath."/lib/events.lib.inc.php");
        event_access_tool($link);
	readfile($coursesRepositoryWeb.$_course['path'].'/page/'.$link);
}

@include($includePath.'/claro_init_footer.inc.php');
?>
