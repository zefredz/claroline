CLAROLINE 1.4.2 README
======================

Claroline is a GPL software working with PHP/MySQL. It is a course based 
educational tool allowing the professor to create, admin and feed his courses 
through the web.If you would like to help develop this software, please visit 
our homepage at http://www.claroline.netLICENSE


Claroline is distributed under Gnu General Public license (GPL).See 
claroline/license/gpl_print.txt

PORTABILITY
===========

Claroline is supposed to work on the following OS:	

        Linux
        Windows (9*, Me, NT4, 2000, XP)
        Unix
        MacOs X.

We tested it on Mandrake 8.2, Mandrake 9, Mandrake 8.2 for PPC, Windows 98, 2000 
and MacOs X.Email functions remain silent on systems where there is no mail 
sending software (Sendmail, Postfix, Hamster...), which is the case by default 
on a Windows machine.

PHP CONFIGURATION
=================

Claroline works on PHP 4 and later. However some users could meet problems if 
their PHP setting doesn't fit these ones:	

        short_open_tag       = On
        register_globals     = On
        safe_mode            = Off
        magic_quotes_gpc     = On
        magic_quotes_runtime = Off

It also seems the backticks charachters (`) inserted inside most of the 
Claroline SQL queries since Claroline 1.3 doesn't work with MySQL versions 
previous to 3.23.6In some sections, Claroline also requires the zlib library.

SECURITY
========

Install script has created a '.htaccess' file toprotect claroline/admin (caution 
: this protection works only on Apache server). To access admin, use 
login/password you entered during install. If it doesn't work, delete 
'claroline/admin/.htaccess'.

INSTALL
=======	

See INSTALL.txt

DOCUMENTATION
=============

1.  http://www.claroline.net presents shortly our philosophy.

2.  Help is slowly progressing. The most complex tools have a
    contextual mini HTML Help page.

3.  A complete manual for trainers and student is also available
    at http://www.claroline.net.


NEW FEATURES IN CLAROLINE 1.4.2 SINCE CLAROLINE 1.4.1
=====================================================

No feature is added to Claroline 1.4.2. This release is basically stressed on 
stability increase and bugs fixing.


NEW FEATURES IN CLAROLINE 1.4.1 SINCE CLAROLINE 1.4.0
=====================================================

No feature is added to Claroline 1.4.1. This release is basically stressed on 
stability increase and bugs fixing.


NEW FEATURES IN CLAROLINE 1.4 SINCE CLAROLINE 1.3
======================================================

The main Claroline improvements are hidden behind the screen. We have totally 
recoded the Claroline Kernel to be more stable and more modular.

* SINGLE DB. Claroline is able now to work on a single DB.   The single DB 
feature is an option to choose at install step.

* LANGUAGE. New languages added. Claroline is now in 20 languages :   Arab, 
Catalan, Crotian, Chinese, Dutch, English, Finnish, French,   German, Galician, 
Greek, Hungarian, Italian, Japanese, Polish,   Portugese, Spanish , Swedish, 
Thaï, Turkish.

* LAYOUT. New 'liquid layout' spreading on all the window.   Introduction of CSS 
system.

* MAIN PAGE. New courses list display in a 'dynamic tree' system.   It allows 
the main page to display faster, especially on servers   with important number 
of course.

* QUIZZ. Totally new quizz tool with 4 different question forms   (multiple 
choices / mutiple answers / fill inn / matching).   The quizz tool  also allows  
:  - image attachment to questions,   - questions retrieval from another quizz,   
- random question list generation.

* DOCUMENT. It automatically detects IMG tag inside HTML file offering   to 
upload the corresponding image.

* WEB CHAT. simple chat with archive function,

* FORUMS. Better protection for private forums

* COURSE ENROLLEMENT. Much simplified enrollment interface.

* LOST PASSWORD. Allowing user recover to recover his/her personnal   password

* ASSIGNMENT. New features allowing Course manager to remove,   edit, or hide 
assignements send by course students.

* USER. New interface and new features allowing each course manager   to insert 
additional information headings inside its own course.   Each course attendee 
can then fill these headings to provide the   information.

* ADMIN SECTION. Restore sytem to insert previously backed up courses.  Most 
admin tools have been consedirably simplified. Bulk user subribe,  possibility 
to deactivate self registration.

* IMPORT / EXPORT : backup and restore for Claroline to Claroline   import, to 
prepare IMS and WebCT to Claroline import,

* TRACKING. The ezBoo statistics tool has been removed.  and a complete tracking 
system is implemented recording   every user's action on the platform.


NEW FEATURES IN CLAROLINE 1.3.1 SINCE CLAROLINE 1.3.0 
===================================================== No feature is added to 

Claroline 1.3.1. This release is basically stressed on stability increase and 
bugs fixing.


NEW FEATURES IN CLAROLINE 1.3.0 SINCE CLAROLINE 1.2
====================================================


* LANGUAGE. New languages added. Claroline is now in 12 languages : Chinese, 
English, Finnish, French, German, Italian, Japanese, Polish,   Portugese, 
Spanish, Swedish and Thaï.

* GROUPS. A new comprehensive group tool has been added. It allows to easily 
organise student groups, attribute them a tutor and create private forum and 
document area for each of them.

* ANNOUNCEMENTS. New annoucements can be sent by mail to students registered 
to the course.

* DOCUMENTS. You can now upload several files in one go by compressing them in 
a single zip file. Once uploaded the file is automatically uncompressed and 
the files structure accurately reproduced.

* COURSE INFO SECTION. An archive function has been added. It allows teacher 
to keep an archive copy of its course (useful when one needs   to modify the 
course for a new session but wants to keep tracks of the   previous session).

* WEB INSTALL. New functions allowing to upgrade from 1.2 and 1.1 versions to 
1.3 has been added. 

* ADMIN SECTION. The new PHPMyAdmin 2.3 has been included and admin tools   
completely rewritten.

NEW FEATURES IN CLAROLINE 1.2.0 SINCE CLAROLINE 1.1.1
=====================================================


* Claroline can be installed in a server sub-directory.

* UCL local settings have been removed (Faculties, IPM link, Course Program...).

* LANGUAGES. Claroline is now in 7 languages: English, Finnish, French, German,   
Italian, Japanese, Spanish. New language adding has been automated. You just   
need to add new language directory into claroline/lang dir. It is then available   
at Course creation as long as 'Modify Course Info' tool.

* WEB INSTALL. Open browser and go to (...)claroline/install to install new 
package.   WATCH OUT! The web install does not manage claroline1.1.1 upgrade to 
1.2.0 yet.

* TEXT EDITING. In almost all tools, URLs and emails are converted into links 
and new   lines into HTML new lines (tags <br>).

* AGENDA. New layout, clearer view (Month separation and mention of current 
day).   Language date formatting automated.

* ANNOUNCEMENTS. Can be moved up and down the list through blue arrow.

* DOCUMENTS. '.php' files uploads are renamed '.phps ' (increase security).   
Renaming a directory doesn't remove its content comments anymore. Directories   
size calculation works with PHP 4.1+ (no problem with previous versions).

* STATS. Install bug fixed (no manual stats settings editing required). Users   
registered only once per session per course.

* ADMINISTRATION. Basic administration tool at claroline/admin/ protected   by 
'.htaccess' (protection only functional if Apache server). Contains   phpSysInfo 
(only functional in Linux servers) and phpMyAdmin 2.2.6.

* TODO. Improved from Admin point of view. You can clasify todos, attribute   
them to a developper, check state of progress and inform users that their   todo 
is taken into account, at what stage and by who.

CREDITS
=======	

See CREDITS.txt file


=======================================================================
Email : info@claroline.net
Europe, Belgium, Louvain-la-NeuveMarch 20, 2003.Tel. +32 10 47 85 48.
================================== END ================================