<?php // $Id$

// this file is deprecated, course choice is made directly in userLog.php


require_once dirname( __FILE__ ) . '../../inc/claro_init_global.inc.php';

$url = './userReport.php?userId=' . claro_get_current_user_id();

header("Location:".$url);

?>