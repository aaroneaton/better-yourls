=== Better YOURLS ===
Contributors: ChrisWiegman
Donate link: https://www.chriswiegman.com
Tags: yourls, shortlink, custom shortlink
Requires at least: 4.0
Tested up to: 4.1
Stable tag: 1.0.4
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

1. Backup your Wordpress database, config file, and .htaccess file
2. Upload the zip file to the `/wp-content/plugins/` directory
3. Unzip
4. Activate the plugin through the 'Plugins' menu in WordPress
5. Visit the Better YOURLS page under settings to add domain and api key

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
* Don't generate URLs in admin, wait for the first post view.

= 1.0.0 =
* Initial Release

== Upgrade Notice ==

= 1.0.1 =
* This fixes a small bug that could lead to your URL reporting as "Auto Draft" in the URLs admin.

= 1.0.0 =
* Initial release. Thanks for Trying!
