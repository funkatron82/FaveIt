<?php
/**
 * The WordPress Plugin Boilerplate.
 *
 * A foundation off of which to build well-documented WordPress plugins that also follow
 * WordPress coding standards and PHP best practices.
 *
 * @package   FaveIt
 * @author    Manny Fleurmond <funkatronic@gmail.com>
 * @license   GPL-2.0+
 * @copyright 2013 Cross Eye Design
 *
 * @wordpress-plugin
 * Plugin Name: Fave-It
 * Plugin URI:  TODO
 * Description: API for creating favorite posts using the Posts 2 Posts core
 * Version:     1.0.0
 * Author:      Manny Fleurmond
 * Author URI:  www.crosseyedesign.com
 * Text Domain: fave-it-locale
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path: /lang
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

require_once( plugin_dir_path( __FILE__ ) . 'class-fave-it.php' );
require_once( plugin_dir_path( __FILE__ ) . 'functions.php' );

// Register hooks that are fired when the plugin is activated, deactivated, and uninstalled, respectively.
register_activation_hook( __FILE__, array( 'FaveIt', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'FaveIt', 'deactivate' ) );

//Get instance
FaveIt::get_instance();