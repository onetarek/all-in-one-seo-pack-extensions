<?php

add_action('plugins_loaded', 'aiosepext_check_main_plugin' );
function aiosepext_check_main_plugin(){
	if( is_admin() ) {
		if( !aioseopext_is_main_plugin_exists() ) {
			add_action('admin_notices', 'aioseopext_main_plugin_not_exists_admin_notice' );
		}
	}
}
function aioseopext_is_main_plugin_exists() {
	return defined('AIOSEOP_VERSION');
}

function aioseopext_main_plugin_not_exists_admin_notice() {
	echo '<div class="notice notice-error"><p><strong>All In One SEO Pack Extensions</strong> : Main <strong>All In One SEO Pack</strong> plugin is not activated or not installed. Install and activate <strong>All In One SEO Pack</strong></p></div>';
}