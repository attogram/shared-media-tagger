# Shared Media Tagger

ALPHA RELEASE

* Shared Media Tagger is a rating website creator for Free Cultural Works
* Supports images, videos and audio files
* Site administrators create a curated set of free media files
* Users review and rate the files
* Media file data is loaded from Wikimedia Commons
* All media files are Free Cultural Works with permissive licensing allowing free re-use
* Source code is Open Source, available on Github: https://github.com/attogram/shared-media-tagger
* Built with PHP and SQLite. No external database required.
* Demos and Installation list:  http://fosiper.com/smt/

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
* Create Database tables: autocreated on first load of admin homepage:  //example.com/admin/
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

This is an alpha release.

Dev tools:
* https://www.codacy.com/app/attogram-project/shared-media-tagger

## TODO
- [ ] admin: setup: option to use CDN or local for bootstrap and jquery
- [ ] pagination on ./reviews.php
- [ ] pagination on ./users.php
- [ ] ./categories.php: sort by name
- [ ] admin: categories: sort by name
- [ ] admin: media: download files to local filesystem
- [ ] admin: database: default site name/about
- [ ] admin: database: default tags
- [ ] BUGFIX: admin: categories: import category information (multi)
- [ ] admin: support continue on api calls with 500+ returns (api limit is 500 per call)
- [ ] Network setup (network export, network import, 404 to redir on files categories, Also See on ./category.php )
- [ ] system for category status:  Master, Supporting
- [ ] admin: users: remove user: delete user, move tagging to system anonymous user
- [ ] admin: users: delete user: delete user, delete tagging
- [ ] admin: site: basic css/color settings in database
- [ ] admin: header.php and footer.php settings in database
