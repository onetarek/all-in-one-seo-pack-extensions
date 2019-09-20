<?php
/*
Plugin Name: All In One SEO Pack Extensions
Plugin URI: http://onetarek.com/wordpress/plugins/all-in-one-seo-pack-extensions/
Description: Extend the popular SEO plugin All In One SEO Pack. Add new features like 'Link counter'.
Version: 1.0.0
Author: oneTarek
Author URI: http://onetarek.com
Text Domain: all-in-one-seo-pack-extensions}
Domain Path: /i18n/
*/


/*
Copyright (C) 2019 oneTarek, https://onetarek.com

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; version 2 of the License.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * All in One SEO Pack Extensions.
 * Extended features for All In One SEO Pack plugin.
 *
 * @package All-in-One-SEO-Pack-Extensions
 * @version 1.0.0
 */


if ( ! defined( 'AIOSEOPEXT_PLUGIN_NAME' ) ) {
		define( 'AIOSEOPEXT_PLUGIN_NAME', 'All in One SEO Pack Extensions' );

}
if ( ! defined( 'AIOSEOPEXT_VERSION' ) ) {
	define( 'AIOSEOPEXT_VERSION', '1.0.0' );
}

if ( ! defined( 'AIOSEOPEXT_PLUGIN_DIR' ) ) {
	define( 'AIOSEOPEXT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
} 

if ( ! defined( 'AIOSEOPEXT_PLUGIN_BASENAME' ) ) {
	define( 'AIOSEOPEXT_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
}
if ( ! defined( 'AIOSEOPEXT_PLUGIN_DIRNAME' ) ) {
	define( 'AIOSEOPEXT_PLUGIN_DIRNAME', dirname( AIOSEOPEXT_PLUGIN_BASENAME ) );
}

if ( ! defined( 'AIOSEOPEXT_PLUGIN_MODULES_DIR' ) ) {
	define( 'AIOSEOPEXT_PLUGIN_MODULES_DIR', AIOSEOPEXT_PLUGIN_DIR.'modules/' );
} 

if ( ! defined( 'AIOSEOPEXT_PLUGIN_URL' ) ) {
	define( 'AIOSEOPEXT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}
if ( ! defined( 'AIOSEOPEXT_PLUGIN_IMAGES_URL' ) ) {
	define( 'AIOSEOPEXT_PLUGIN_IMAGES_URL', AIOSEOPEXT_PLUGIN_URL . 'images/' );
}
if ( ! defined( 'AIOSEOPEXT_PLUGIN_MODULES_URL' ) ) {
	define( 'AIOSEOPEXT_PLUGIN_MODULES_URL', AIOSEOPEXT_PLUGIN_URL . 'modules/' );
}


require_once( AIOSEOPEXT_PLUGIN_DIR . '/admin/class-all-in-one-seo-pack-extensions-module-manager.php' );
new All_in_One_SEO_Pack_Extensions_Module_Manager();

