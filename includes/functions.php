<?php

function aioseop_plugin_file_path() {
	if( ! defined('AIOSEOP_PLUGIN_BASENAME' ) ) {
		return '';
	}
	return WP_PLUGIN_DIR.'/'.AIOSEOP_PLUGIN_BASENAME;
}