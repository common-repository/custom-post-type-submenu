=== Custom Post Type Submenu ===
Contributors: receter, queenvictoria
Tags: menus, cpt
Requires at least: 3.6
Tested up to: 3.6.1
Stable tag: trunk
License: GPL2

Adds a new menu item type that shows all posts for a given post type as children. If the post type has an archive, the parent menu item will link to the archive. If there is no archive for a given post type, the parent will link to the first child.

== Description ==

Adds a new menu item type that shows all posts for a given post type as children. If the post type has an archive, the parent menu item will link to the archive. If there is no archive for a given post type, the parent will link to the first child.

For Example: If you have a custom post type "products", adding "products" to a nav-menu will result in a menu item "products" with all products in the submenu. 

== Installation ==

1. Upload `menu-posts-in-category/` directory to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Add a Custom Post Type to a Wordpress menu

== Frequently Asked Questions ==

== Changelog ==

= 0.1 =
* Initial version

= 0.2 =
* Fixed a bug where the spinner wheel was displayed all the time.
* Links to archive instead of first child if the custom post type has has_archive = true

== Upgrade Notice ==

== Roadmap ==

* Provide for configuration of limit (ie: numposts).