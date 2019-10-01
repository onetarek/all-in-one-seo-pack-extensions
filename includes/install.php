<?php

/**
 * Installation
 */


function aioseopext_activation(){ write_log( "aioseopext_activation" );
	
	//fire the activation hook for modules those are 'enabled'
	$aioseop_path = aioseop_plugin_file_path();
	if( empty( $aioseop_path ) ) {
		return ;
	}
	global $aioseopext_mod_man;
	global $aioseop_options;
	if( empty( $aioseop_options ) ) {
		return;
	}

	$modules_info = $aioseopext_mod_man->get_modules_info();
	//write_log( $modules_info , $aioseop_options );
	foreach( $modules_info as $mod => $mod_info ) {
		if( isset( $aioseop_options['modules']['aiosp_feature_manager_options']['aiosp_feature_manager_enable_'.$mod] ) 
			&& $aioseop_options['modules']['aiosp_feature_manager_options']['aiosp_feature_manager_enable_'.$mod] == 'on' ) {

			$classname = 'All_in_One_SEO_Pack_' . strtr( ucwords( strtr( $mod, '_', ' ' ) ), ' ', '_' );
			$classname = apply_filters( "aioseop_class_$mod", $classname );
			if( ! class_exists( $classname ) ) {
				require_once( $mod_info['mod_path'] );
				new $classname();
			}
			//Fire the activation hook
			write_log( 'should fire '.'aiospext_activate_module_'.$mod);
			do_action( 'aiospext_activate_module_'.$mod );
		}
		
	}
}

function aioseopext_deactivation(){write_log( "aioseopext_deactivation" );
	
	$aioseop_path = aioseop_plugin_file_path();
	if( empty( $aioseop_path ) ) {
		return ;
	}

	global $aioseopext_mod_man;
	$modules_info = $aioseopext_mod_man->get_modules_info();
	foreach( $modules_info as $mod => $mod_info ) {

		$classname = 'All_in_One_SEO_Pack_' . strtr( ucwords( strtr( $mod, '_', ' ' ) ), ' ', '_' );
		$classname = apply_filters( "aioseop_class_$mod", $classname );

		if( ! class_exists( $classname ) ) {
			require_once( $mod_info['mod_path'] );
			new $classname();
		}
		//Fire the deactivation hook
		do_action( 'aiospext_deactivate_module_'.$mod );
	}
}

//add_action('plugins_loaded', 'aioseopext_set_activation_deactivation');

function aioseopext_set_activation_deactivation() { write_log('PLUGINS LOADED');

	//register_activation_hook( AIOSEOPEXT_PLUGIN_FILE, 'aioseopext_activation' ); 
	//register_deactivation_hook( AIOSEOPEXT_PLUGIN_FILE, 'aioseopext_deactivation' );
	//write_log( aioseop_plugin_file_path() );




	$main_aioseop_path = aioseop_plugin_file_path();
	write_log( $main_aioseop_path );
	if( !empty( $main_aioseop_path ) ) {
		register_activation_hook( $main_aioseop_path, 'aioseopext_activation' ); 
		register_deactivation_hook( $main_aioseop_path, 'aioseopext_deactivation' );
	}
}

add_action( 'activate_plugin', 'aioseopext_process_activate_plugin', 10, 2 );

/**
 * Do something after activation of All In One SEO Plugin.
 */

function aioseopext_process_activate_plugin( $plugin, $network_wide ) {
	$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin );
	write_log( $plugin, $plugin_data );
	if( isset( $plugin_data['Name'] ) && strtolower( trim($plugin_data['Name']) ) == 'all in one seo pack' ) {
		write_log("Condition true");
		
		aioseopext_activation();
	}
	
}

add_action( 'deactivate_plugin', 'aioseopext_process_deactivate_plugin', 10, 2 );

/**
 * Do something after deactivation of All In One SEO Plugin.
 */
function aioseopext_process_deactivate_plugin( $plugin, $network_wide ) {
	$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin );
	write_log( $plugin, $plugin_data );
	if( isset( $plugin_data['Name'] ) && strtolower( trim($plugin_data['Name'] ) ) == 'all in one seo pack' ) {
		
		aioseopext_deactivation();
	}
	
}

