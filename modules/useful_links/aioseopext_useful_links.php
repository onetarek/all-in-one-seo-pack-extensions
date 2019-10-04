<?php 
/**
 * Link Counter Module
 *
 * @package Extensions_For_All_In_One_SEO_Pack
 * @since 1.0
 */

if ( ! class_exists( 'All_in_One_SEO_Pack_Useful_Links' ) ) {

	/**
	 * Class All_in_One_SEO_Pack_Useful_Links
	 */
	class All_in_One_SEO_Pack_Useful_Links extends All_in_One_SEO_Pack_Module {

		/**
		 * Module Slug
		 *
		 * @since 1.0
		 *
		 * @var string $module_slug
		 */
		public $slug = 'useful_links';


		
		/**
		 * All_in_One_SEO_Pack_Useful_Links constructor.
		 */
		public function __construct() {
			global $wpdb;
			$this->name   = __( 'Useful Links', 'ext-for-all-in-one-seo-pack' );    // Human-readable name of the plugin.
			$this->prefix = 'aioseopext_useful_links_';                        // Option prefix.
			$this->file   = __FILE__;                                    // The current file.
			
			parent::__construct();

			$this->default_options = array(
				
			);
		}

		/**
		 * Override the parent function with a blank function to stop adding admin menu for this module.
		 */
		function add_menu( $parent_slug ){}

		/**
		 * Override the parent function with a blank function to stop adding adminbar submenu for this module.
		 */
		function add_admin_bar_submenu(){}
		
	}//end class
}