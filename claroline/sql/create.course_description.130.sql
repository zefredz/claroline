# for claro 1.3.0
# write  by Christophe Gesché
# $Id$ #
CREATE TABLE `course_description` 
(
	`id` TINYINT UNSIGNED DEFAULT '0' NOT NULL,
	`title` VARCHAR(255),
	`content` TEXT,
	`upDate` DATETIME NOT NULL,
	UNIQUE (`id`)
)
COMMENT = 'for course description tool';