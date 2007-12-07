<?php // $Id$
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.4.* $Revision$                            |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2003 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      |   This program is free software; you can redistribute it and/or      |
      |   modify it under the terms of the GNU General Public License        |
      |   as published by the Free Software Foundation; either version 2     |
      |   of the License, or (at your option) any later version.             |
      |                                                                      |
      |   This program is distributed in the hope that it will be useful,    |
      |   but WITHOUT ANY WARRANTY; without even the implied warranty of     |
      |   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the      |
      |   GNU General Public License for more details.                       |
      |                                                                      |
      |   You should have received a copy of the GNU General Public License  |
      |   along with this program; if not, write to the Free Software        |
      |   Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA          |
      |   02111-1307, USA. The GNU GPL license is also available through     |
      |   the world-wide-web at http://www.gnu.org/copyleft/gpl.html         |
      +----------------------------------------------------------------------+
      | Authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
      |          Hugues Peeters    <peeters@ipm.ucl.ac.be>                   |
      |          Christophe Gesché <gesche@ipm.ucl.ac.be>                    |
      |          Sebastien Piraux  <piraux_seb@hotmail.com>
      +----------------------------------------------------------------------+
 */

$langFile = "tracking";
require '../inc/claro_init_global.inc.php';

$nameTools = $HTTP_GET_VARS["tool"];
$interbredcrump[]= array ("url"=>"index.php", "name"=> "Admin");
$interbredcrump[]= array ("url"=>"campusLog.php", "name"=> $langStatsOfCampus);
$interbredcrump[]= array ("url"=>basename($PHP_SELF), "name"=> $langDetails);

$htmlHeadXtra[] = "<style type='text/css'>
<!--
.mainLine {font-weight : bold;color : #FFFFFF;background-color : $colorDark;padding-left : 15px;padding-right : 15px;}
.secLine {color : #000000;background-color : $colorMedium;padding-left : 15px;padding-right : 15px;}
.content {padding-left : 25px;}
.specialLink{color : #0000FF;}
-->
</style>
<STYLE media='print' type='text/css'>
<!--
TD {border-bottom: thin dashed Gray;}
-->
</STYLE>";


@include($includePath."/claro_init_header.inc.php");

?>
<h3>
    <?php echo $langDetails; ?>
</h3>
<?php
unset($_cid); //to prevent cid to be set so that admin can see stats for a specific course ( not his job ! )
include($includePath."/tool_access_details.inc.php");

echo "<br>";
@include($includePath."/claro_init_footer.inc.php");
?>