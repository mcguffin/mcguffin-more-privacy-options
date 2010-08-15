=== More Privacy Options ===
Contributors: dsader
Donate link: http://dsader.snowotherway.org
Tags: privacy, private blog, multisite, members only
Requires at least: 3.0
Tested up to: 3.0
Stable tag: Trunk

WP3.0 multisite "mu-plugin" to add more privacy options to the options-privacy and ms-blogs pages. Just drop in mu-plugins.

== Description ==
Adds three more levels of privacy to the Options--Privacy page.

1. Blog visible to any logged in community member.

2. Blog visible only to registered users of blog.

3. Blog visible only to administrators.

Mulitsite SuperAdmin can set an override on blog privacy at "Network Privacy Selector" on SuperAdmin-Options page

Multisite SuperAdmin can set privacy options at SuperAdmin-Sites-Edit under "Misc Site Options" as well.

== Installation ==

This section describes how to install the plugin and get it working.

1. Upload `ds_wp3_private_blog.php` to the `/wp-content/mu-plugins/` directory
2. Set multisite "Network Privacy" option at SuperAdmin-Options page
3. Set individual blog privacy options at Settings-Privacy page, or...
4. Set individual blog privacy options at SuperAdmin-Sites-Edit page

== Frequently Asked Questions ==

* Will this plugin also protect feeds? Yes.
* Will this plugin protect uploaded files and images? No.

== Screenshots ==

1. Settings Privacy: Site Visibility Settings
2. SuperAdmin Option: Network Privacy Selector
3. Sites Edit: Misc Site Options

== Changelog ==
= 3.0.1 = 

deprecated $user_level check replaced with is_user_logged_in()

= 3.0 =
* WP3.0 Multisite enabled

= 2.9.2 =
* WPMU version no longer supported.

== Upgrade Notice ==
= 3.0.1 = 

deprecated $user_level check replaced with is_user_logged_in()

= 3.0 =
WP3.0 Multisite enabled

= 2.9.2 =
WPMU version no longer supported.
