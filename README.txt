=== Fave It ===
Contributors: funkatronic, scribu
Donate link: http://crosseyedesign.com/
Tags: faves, favorites, p2p
Requires at least: 3.5
Tested up to: 3.6
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

API plugin to create user favorite posts.

== Description ==

This is a plugin that allows users to fave or favorite posts of various post types using a connection via scribu's Posts 2 Posts core.  Once faves are created, a user's fave posts can be queried as well as all the users that have faved a particular post.

This plugin is designed to be used to augment existing plugins and themes; in other words, client side  styling is needed in order for this to be benificial.  Included with this, however, is a jQuery plugin that should help with this.  This plugin can be applied to any HTML element as long as that element has a `data-fave-post` attribute with a`post_id` as a value.  The plugin element, when clicked, will fave the post via AJAX then add a class of `fave` onto that element.  Developers must style this class in order let a user know whether their fave was successful.

You can specify whichpost types can be faved via the admin page in the settings menu.  Post types can also be filtered via the `fave_post_types` filter.

== Installation ==



== Changelog ==

= 1.0 =
* First release!
* Fave posts using `fave_post` function
* Unfave using `unfave_post` function
