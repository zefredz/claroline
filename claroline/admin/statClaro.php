<?php // $Id$

die ("deprecated. This script  is keep sql in");
/*
select DISTINCT statut, count(*) from user Group by statut
select DISTINCT faculte, count(*) from cours Group by faculte
select DISTINCT languageCourse, count(*) from cours Group by languageCourse
select DISTINCT visible, count(*) from cours Group by visible
select CONCAT(code_cours,\" Statut :\",statut), count(user_id) from cours_user Group by code_cours, statut order by code_cours
select DISTINCT username , count(*) as nb from user group by username HAVING nb > 1  order by nb desc;
select DISTINCT email , count(*) as nb from user group by email HAVING nb > 1  order by nb desc;
select DISTINCT CONCAT(username, \" -- \", password) as paire, count(*) as nb from user group by paire HAVING nb > 1   order by nb desc;
*/
?>
