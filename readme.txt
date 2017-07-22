=== Better YOURLS ===
Contributors: ChrisWiegman, michaelbeil, domsammut
Donate link: https://chriswiegman.com
Tags: yourls, shortlink, custom shortlink
Requires at least: 4.2
Tested up to: 4.8.1
Stable tag: 2.2.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Integrate your blog with <a href="http://yourls.org" target="_blank">YOURLS</a> custom URL generator.

== License ==
Released under the terms of the GNU General Public License.

== Description ==

Integrates your blog with the <a href="http://yourls.org" target="_blank">YOURLS</a> custom URL generator.

= Features =

* Creates YOURLs links for all content using wp_shortlink
* Saves links to post_meta to reduce server calls
* Easily access link stats from the admin bar
* Only 2 options: domain and api key

= Translations =

* English

== Installation ==

1. Backup your WordPress database, config file, and .htaccess file
2. Upload the zip file to the `/wp-content/plugins/` directory
3. Unzip
4. Activate the plugin through the 'Plugins' menu in WordPress
5. Visit the Better YOURLS page under settings to add domain and API key

== Frequently Asked Questions ==

= Are you still developing this plugin? =
* Yes.

= Does this work with netowork or multisite installations? =
* I don't know. I haven't tried it.

= Can I help? =
* Of course! I am in constant need of testers and I would be more than happy to add the right contributor. In addition, I could always use help with translations for internationalization.

== Screenshots ==

1. Easy to use with only 2 options.

== Changelog ==

= 2.2.1 =
* Fixed error on settings save due to unavailable array.
* Fixed "Security Error" when saving ignored posts.
* Minor JS and CSS refactoring for easier debugging
* Moved .pot file to "languages" folder

= 2.2 =
* Added ability to properly handle non-public post types.
* Minor fixes and typo corrections.

= 2.1.6 =
* Minor code sniffer fixes.
* Added nonce to keyword form.

= 2.1.5 =
* Cleaned up various typos and other PHP Codesniffer issues.

= 2.1.4 =
* Fixed custom keyword issue (Credit Dom Sammut)
* Various typo and other minor fixes.

= 2.1.3 =
* 2.1.3 Cleans out extra files in the packaged plugin that my deployment script didn't catch.

= 2.1.2 =
* Fix: No longer will generate shortlinks for admin menu items
* Behind the scenes: Finally started adding proper Unit Tests to improve reliability. Coverage is up to about 25%

= 2.1.1 =
* Fix: ShortURL generation will now work better with many social sharing plugins such as Jetpack

= 2.1.0 =
* Enhancement: Allow for https access to YOURLS installation for API actions
* Enhancement: Disable short-url creation for specific content types
* Enhancement: Numerous additional hooks for more finer-grained control of URL creation
* Enhancement: Use POST instead of GET for URL creation
* Fix: Better checking of posts before creating a link to avoid issues

= 2.0.1 =
* Fix : Spaces should no longer be eliminated from titles
* Enhancement: Allow filtering of post types (credit to domsammut)

= 2.0.0 =
* Enhancement: complete refactor for better efficiency and less bugs

= 1.0.5 =
* Fixed: Fixed an issue preventing the shortlink from displaying for some URLS (see https://github.com/ChrisWiegman/Better-YOURLS/pull/1)

= 1.0.4 =
* Minor typo fixes and test with version 4.1

= 1.0.3 =
* Added hook to generate short url on post transition
* Added get_shortlink hook to cover normal shortlink generation
* No longer try to generate a shortlink in pre_get_shortlink. Just return it if it already exists
* More efficient shortlink creation
* General code cleanup

= 1.0.2 =
* Improved URL validation to avoid saving extraneous data
* Minor typo fixes

= 1.0.1 =
* Don't generate URLs in admin, wait for the first post view

= 1.0.0 =
* Initial Release

== Upgrade Notice ==

= 2.1.4 =
* Version 2.1.3 is a bugfix update that is recommended for all users.

= 2.1.3 =
* Version 2.1.3 is a bugfix update that is recommended for all users.

= 2.1.2 =
* Version 2.1.2 is a bugfix update that is recommended for all users.

= 2.1.1 =
* Version 2.1.1 is a bugfix update that is recommended for all users.

= 2.1 =
* Version 2.1.0 contains new features to improve plugin use for everyone.

= 2.0.1 =
2.0.1 is a bugfix update that is recommended for all users.

= 1.0.5 =
* This is a bugfix release that is recommended for all users

= 1.0.1 =
* This fixes a small bug that could lead to your URL reporting as "Auto Draft" in the URLs admin

= 1.0.0 =
* Initial release. Thanks for Trying!
