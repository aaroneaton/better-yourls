Better YOURLS [![Build Status](https://travis-ci.org/ChrisWiegman/Better-YOURLS.svg?branch=develop)](https://travis-ci.org/ChrisWiegman/Better-YOURLS)
=============

Integrate your blog with [YOURLS](http://yourls.org) custom URL generator.

## License
Released under the terms of the GNU General Public License.

## Features

* Creates YOURLs links for all content using wp_shortlink
* Saves links to post_meta to reduce server calls
* Easily access link stats from the admin bar
* Only 2 options: domain and api key

## Installation

1. Backup your WordPress database, config file, and .htaccess file
2. Upload the zip file to the `/wp-content/plugins/` directory
3. Unzip
4. Activate the plugin through the 'Plugins' menu in WordPress
5. Visit the Better YOURLS page under settings to add domain and API key

## FAQ

##### Are you still developing this plugin?
* Yes.

##### Does this work with network or multisite installations?
* I don't know. I haven't tried it.

##### Can I help?
* Of course! I am in constant need of testers and I would be more than happy to add the right contributor. In addition, I could always use help with translations for internationalization.

## Changelog

##### 2.1.1
* Fix: ShortURL generation will now work better with many social sharing plugins such as Jetpack

##### 2.1.0
* Enhancement: Allow for https access to YOURLS installation for API actions
* Enhancement: Disable short-url creation for specific content types
* Enhancement: Numerous additional hooks for more finer-grained control of URL creation
* Enhancement: Use POST instead of GET for URL creation
* Fix: Better checking of posts before creating a link to avoid issues

##### 2.0.1
* Fix : Spaces should no longer be eliminated from titles
* Enhancement: Allow filtering of post types (credit to domsammut)

##### 2.0.0
* Enhancement: complete refactor for better efficiency and less bugs

##### 1.0.5
* Fixed: Fixed an issue preventing the shortlink from displaying for some URLS (see https://github.com/ChrisWiegman/Better-YOURLS/pull/1)

##### 1.0.4
* Minor typo fixes and test with version 4.1

##### 1.0.3
* Added hook to generate short url on post transition
* Added get_shortlink hook to cover normal shortlink generation
* No longer try to generate a shortlink in pre_get_shortlink. Just return it if it already exists
* More efficient shortlink creation
* General code cleanup

##### 1.0.2
* Improved URL validation to avoid saving extraneous data
* Minor typo fixes

##### 1.0.1
* Don't generate URLs in admin, wait for the first post view

##### 1.0.0
* Initial Release
