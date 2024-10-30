=== CC-Minify ===
Contributors: ClearcodeHQ, PiotrPress
Tags: minify, minification, compress, combine, concatenation, css, stylesheet, javascript, js, optimize, optimization, performance, styles, scripts, dependency, dependencies, enqueue styles, enqueue_scripts, wp_enqueue_style, wp_enqueue_script, wp_register_style, wp_register_script, wp_styles, wp_scripts, wp_dependencies, cache, clearcode, Apache, mod_rewrite
Requires at least: 4.6.1
Tested up to: 4.6.1
Stable tag: trunk
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.txt

This plugin combines and minifies your CSS and JS files to improve page load time.

== Description ==

The CC-Minify plugin optimizes your website's CSS and JS files by combining, minifying, grouping and caching them to speed up your page's load time.
This plugin uses the [Minify](https://github.com/matthiasmullie/minify) PHP library.
Additionally using Apache with mod_rewrite (or other server supports URL rewriting) it can rewrite URL to simplify the CSS and JS paths, for example:
from: `http://example.com/wp-content/cache/styles/all.css` to: `http://example.com/styles/all.css`
from: `http://example.com/wp-content/cache/scripts/head.js` to: `http://example.com/scripts/head.js`
This plugin is compatible with Multisite WordPress installations.

= Tips & Tricks =

You can check if a css and/or js files has been served from a cache by opening the page's source code.
If files are served from cache, you should see a comment with the date and time when it was last cached, for example:
`<!-- Minify @ 2016-11-07 12:34:56 -->`

== Requirements ==

1. Server supports URL rewriting: Apache with mod_rewrite, IIS 7.0+ permalink support or nginx.
2. Read/Write access to wp-content/cache directory.
3. PHP interpreter version >= 5.3.

== Installation ==

= From your WordPress Dashboard =

1. Go to 'Plugins > Add New'
2. Search for 'CC-Minify'
3. Activate the plugin from the Plugin section on your WordPress Dashboard.

= From WordPress.org =

1. Download 'CC-Minify'.
2. Upload the 'CC-Minify' directory to your '/wp-content/plugins/' directory using your favorite method (ftp, sftp, scp, etc...)
3. Activate the plugin from the Plugin section in your WordPress Dashboard.

= Once Activated =

1. Visit the 'Settings > Minify' page, select your preferred options and save them.
2. To properly rewrite URLs, add the rules listed in 'Settings > Minify' to the beginning of your `.htaccess` file.

= Multisite =

The plugin can be activated and used for just about any use case.

* Activate at the site level to load the plugin on that site only.
* Activate at the network level for full integration with all sites in your network (this is the most common type of multisite installation).

== Screenshots ==

1. **CC-Minify Settings** - Visit the 'Settings > Minify' page, select your preferred options and save them.

== Changelog ==

= 1.0.0 =
*Release date: 07.11.2016*

* First stable version of the plugin.