# $Id$ #

# this  SQL update Main database of  claroline from 120 to 130
############### NEW TABLE ############### 


#
# Structure de la table `course_description`
#

CREATE TABLE course_description (
  id tinyint(3) unsigned NOT NULL default '0',
  title varchar(255) default NULL,
  content text,
  upDate datetime NOT NULL default '0000-00-00 00:00:00',
  UNIQUE KEY id (id)
) TYPE=MyISAM COMMENT='for course description tool';



