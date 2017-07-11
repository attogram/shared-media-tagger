# Shared Media Tagger 

ALPHA RELEASE

* Shared Media Tagger (SMT) allows users to tag a curated set of images and media from WikiMedia Commons.


## Requirements

* PHP 7 or PHP 5.3.3 or higher, installed with SQLite PDO driver
* Web server has write access to database file  ( ./admin/db/media.sqlite )
* Web server has internet access to the WikiMedia Commons API endpoint


## Setup

* install the source code into web-accessible area on your server
* setup protection for admin/ directory: create admin/.htaccess and admin/.htpasswd
* make database directory writeable: chmod 777 admin/db
* (optional) copy _setup.sample.php to _setup.php and edit as needed
* (optional) create extra site header/footers: header.php, footer.php
* (optional) change db web admin password:  admin/phpliteadmin.config.php  (default password:  test)
* Create Database tables: autocreated on first load of admin homepag:  //example.com/admin/
* Set site name and description: //example.com/admin/site.php  
* (optional) setup tags: //example.com/admin/site.php
* goto admin category page:  //example.com/admin/category.php
* Find categories from commons: search and save categories
* [Import Category Info] to save all categories info
* Load media: click _Import_ for each category you want to import
* once at least 1 image and 1 category are saved, the site is active!
* more soon....

Dev notes:
* DB web admin password: test (set in: admin/phpliteadmin.config.php)


## License

The Shared Media Tagger is an open source project.

The Shared Media Tagger is dual licensed under
[The MIT License](http://opensource.org/licenses/MIT) or the
[GNU General Public License](http://opensource.org/licenses/GPL-3.0), at your choosing.


# Alpha Release

This is an early alpha release.

Dev tools:
* https://www.codacy.com/app/attogram-project/shared-media-tagger
