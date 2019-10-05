<?php
/**
 * The Module Manager.
 *
 * Mostly hooks modules to All-In-One-SEO-Pack module process.
 *
 * @package Extensions_For_All_In_One_SEO_Pack
 * @since 1.0
 */

if ( ! class_exists( 'Extensions_For_All_In_One_SEO_Pack_Module_Manager' ) ) {

	/**
	 * Class Extensions_For_All_In_One_SEO_Pack_Module_Manager
	 */
	class Extensions_For_All_In_One_SEO_Pack_Module_Manager {

		/**
		 * Module Info
		 *
		 * @since 1.0
		 *
		 * @var array|mixed|void $module_info
		 */
		protected $module_info = array();

		/**
		 * Extensions_For_All_In_One_SEO_Pack_Module_Manager constructor.
		 *
		 * @since 1.0
		 *
		 */
		function __construct() {
			$this->module_info = array(
				'link_counter'           => array(
					'name'        => __( 'Link counter', 'ext-for-all-in-one-seo-pack' ),
					'description' => __( 'Count internal and external links in post contents and show in new columns of posts list', 'ext-for-all-in-one-seo-pack' ),
					'image' => AIOSEOPEXT_PLUGIN_MODULES_URL.'link_counter/images/link-counter.png',
					'mod_path' => AIOSEOPEXT_PLUGIN_MODULES_DIR . "link_counter/aioseopext_link_counter.php",
				),
				'useful_links'           => array(
					'name'        => __( 'Useful Links', 'ext-for-all-in-one-seo-pack' ),
					'description' => __( 'Show useful links for SEO tools in adminbar', 'ext-for-all-in-one-seo-pack' ),
					'image' => AIOSEOPEXT_PLUGIN_MODULES_URL.'useful_links/images/useful-links.png',
					'mod_path' => AIOSEOPEXT_PLUGIN_MODULES_DIR . "useful_links/aioseopext_useful_links.php",
					'default'     => 'on',
				),
				
			);

			add_filter( 'aioseop_module_list', array($this, 'add_to_aioseop_module_list'), 10, 1 );
			add_filter( 'aioseop_module_info', array($this, 'add_to_aiseop_module_info'), 10, 1 );

			foreach( $this->module_info as $mod => $args ) {
				add_filter( "aioseop_include_$mod", array( $this, "filter_mod_path" ), 10, 1 );
			}
			
			//do something on module activation and deactivation. See method handle_settings_updates in all-in-one-seo-pack/admin/aioseop_module_class.php 
			add_action( "aiosp_feature_manager_settings_update", array( $this, "after_feature_manager_settings_update"), 10, 2 );   

			if( is_admin() ) {
				add_action( 'aiosp_feature_manager_settings_footer', array( $this, 'feature_manager_footer' ), 10, 1 );
			}
			
		}

		/**
		 * Add new items to the main aiseop modules info array
		 *
		 * @since 1.0
		 * @param array $aiseop_module_info
		 * @return array
		 */
		public function add_to_aiseop_module_info( $aiseop_module_info ) {
			$aiseop_module_info = array_merge($aiseop_module_info, $this->module_info );
			return $aiseop_module_info;
		}

		/**
		 * Add new items to the main aiseop modules list array
		 *
		 * @since 1.0
		 * @param array $aiseop_module_list
		 * @return array
		 */
		public function add_to_aioseop_module_list( $aiseop_module_list ) {
			foreach ($this->module_info as $key => $value) {
				$aiseop_module_list[] = $key;
			}
			return $aiseop_module_list;
		}

		/**
		 * Filter the module main file path
		 *
		 * @since 1.0
		 * @param string $mod_path
		 * @return string
		 */
		public function filter_mod_path( $mod_path ) {
			
			$path = untrailingslashit( $mod_path );
			$parts = explode("/", $path );
			$last = end( $parts );
			$last = str_replace(".php", "", $last );
			$mod = str_replace("aioseop_", "", $last );
			if( array_key_exists( $mod, $this->module_info ) ) {
				$mod_path =  $this->module_info[$mod]['mod_path'];
			}
			return $mod_path;
		}

		/**
		 * //do something on module activation and deactivation
		 *
		 * @since 1.0
		 * @param string $options
		 * @return string $location
		 * @see See method handle_settings_updates in all-in-one-seo-pack/admin/aioseop_module_class.php 
		 */

		public function after_feature_manager_settings_update( $options, $location ) {
			
			$mods = array_keys( $this->module_info );
			foreach( $mods as $mod ) {
				$key = "aiosp_feature_manager_enable_".$mod;
				$classname = 'All_in_One_SEO_Pack_' . strtr( ucwords( strtr( $mod, '_', ' ' ) ), ' ', '_' );
				$classname = apply_filters( "aioseop_class_$mod", $classname );
				if( isset( $options[$key] ) && $options[$key] == 'on' ) {
					
					if( !class_exists( $classname ) ) { //module is not loaded yet, it means it is not previously activated.
						//Load this module file. Do something on activating.
						require_once( $this->get_mod_path( $mod ) );
						$module_class = new $classname();
						//Fire the activation hook
						do_action( 'aiospext_activate_module_'.$mod );
					}
				} else {
					//deactivating this module
					do_action( 'aiospext_deactivate_module_'.$mod );
				}

			}
		}

		/**
		 * Get the path of module file
		 *
		 * @since 1.0
		 * @param string $mod , module slug
		 * @return string
		 */
		public function get_mod_path( $mod ) {
			if( array_key_exists( $mod, $this->module_info ) && isset( $this->module_info[$mod]['mod_path'] ) ) {
				return $mod_path =  $this->module_info[$mod]['mod_path'];
			}
			return '';	
		}

		/**
		 * Adds scripts to fix the module image in feature manager page
		 *
		 * @since 1.0
		 */
		 public function feature_manager_footer( $location ) {
		 	$mods_data = array();
		 	foreach ($this->module_info as $key => $value) {
		 		$mods_data[] = array('mod_slug' => $key, 'image'=> $value['image'] );
		 	}
		 	?>
			<script type="text/javascript">
				var aioseopext_mods_data = <?php echo wp_json_encode( $mods_data ) ?>;

				jQuery(document).ready(function(){

					for( var i in aioseopext_mods_data ) {
						var mod = aioseopext_mods_data[i];
						var box = jQuery('#aioseop_'+mod['mod_slug'] );
						var img = box.find('img').first();
						img.attr('src', mod['image'] );
						img.parent().css('text-align', 'center');
					}
				});
			</script>
		 	<?php 
		 }



	}//end class
}