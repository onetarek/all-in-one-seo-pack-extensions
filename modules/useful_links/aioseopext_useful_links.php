<?php 
/**
 * Useful Links Module
 *
 * @package Extensions_For_All_In_One_SEO_Pack
 * @since 1.1
 */

if ( ! class_exists( 'All_in_One_SEO_Pack_Useful_Links' ) ) {

	/**
	 * Class All_in_One_SEO_Pack_Useful_Links
	 */
	class All_in_One_SEO_Pack_Useful_Links extends All_in_One_SEO_Pack_Module {

		/**
		 * Module Slug
		 *
		 * @since 1.1
		 *
		 * @var string $module_slug
		 */
		public $slug = 'useful_links';

		/**
		 * idendifire items in adminbar
		 * @var string 
		 */
		 public $menu_identifire;
		
		/**
		 * All_in_One_SEO_Pack_Useful_Links constructor.
		 */
		public function __construct() {
			global $wpdb;
			$this->name   = __( 'Useful Links', 'ext-for-all-in-one-seo-pack' );    // Human-readable name of the plugin.
			$this->prefix = 'aioseopext_useful_links_';                        // Option prefix.
			$this->file   = __FILE__;                                    // The current file.
			
			if( defined('AIOSEOP_PLUGIN_DIRNAME') ) {
				$this->menu_identifire = AIOSEOP_PLUGIN_DIRNAME;
			}

			parent::__construct();

			$this->default_options = array(
				
			);

			if ( is_user_logged_in() && is_admin_bar_showing() && current_user_can( 'aiosp_manage_seo' ) ) {
				add_action( 'admin_bar_menu', array( $this, 'admin_bar_menu' ), 1030 );
			}

		}

		/**
		 * Override the parent function with a blank function to stop adding admin menu for this module.
		 */
		//function add_menu( $parent_slug ){}

		/**
		 * Override the parent function with a blank function to stop adding adminbar submenu for this module.
		 */
		//function add_admin_bar_submenu(){}

		/**
		 * Add new links to adminbar
		 */
		public function admin_bar_menu() {

			if( empty( $this->menu_identifire ) ) {
				return;
			}
			$this->add_separator_submenu();
			$this->add_keyword_research_submenu();
			if( !is_admin() ) {
				$this->add_analysis_submenu();
			}

		}

		/**
		 * Add a separator between AIOSEOP links and links by this useful_links module
		 */
		private function add_separator_submenu(){
			global $wp_admin_bar, $aioseop_admin_menu;
			$text = '<hr>';
			$title = '<div style="color:#42474c;">---------------------------</div>';
			$menu_args = array(
				'parent' => $this->menu_identifire,
				'id'     => 'aioseopext-separator',
				'title'  => $title,
				'meta'   => array( 'tabindex' => '0' ),
			);
			$wp_admin_bar->add_menu( $menu_args );
		}

		/**
		 * Adds the admin bar keyword research submenu.		 
		 */
		private function add_keyword_research_submenu() {
			global $wp_admin_bar, $aioseop_admin_menu;
			
			$kyeres_submenu_identifire = 'aioseopext-keyres';

			$adwords_url = 'https://ads.google.com/home';
			$trends_url  = 'https://trends.google.com/trends/explore';

			$focus_keyword = $this->get_focus_keyword();
			if ( ! empty( $focus_keyword ) ) {
				$trends_url .= '#q=' . urlencode( $focus_keyword );
			}
			

			$menu_args = array(
				'parent' => $this->menu_identifire,
				'id'     => $kyeres_submenu_identifire,
				'title'  => __( 'Keyword Research', 'ext-for-all-in-one-seo-pack' ),
				'meta'   => array( 'tabindex' => '0' ),
			);
			$wp_admin_bar->add_menu( $menu_args );

			$submenu_items = array(
				array(
					'id'     => 'aioseopext-adwordsexternal',
					'title'  => __( 'Google Ads', 'ext-for-all-in-one-seo-pack' ),
					'href'   => $adwords_url,
				),
				array(
					'id'     => 'aioseopext-googleinsights',
					'title'  => __( 'Google Trends', 'ext-for-all-in-one-seo-pack' ),
					'href'   => $trends_url,
				),
			);

			foreach ( $submenu_items as $menu_item ) {
				$menu_args = array(
					'parent' => $kyeres_submenu_identifire,
					'id'     => $menu_item['id'],
					'title'  => $menu_item['title'],
					'href'   => $menu_item['href'],
					'meta'   => array( 'target' => '_blank' ),
				);
				$wp_admin_bar->add_menu( $menu_args );
			}
		}

		/**
		 * Adds the admin bar analysis submenu.
		 */
		private function add_analysis_submenu() {
			global $wp_admin_bar, $aioseop_admin_menu;
			$analysis_submenu_identifire = 'aioseopext-analyze-this-page';

			$url = $this->get_canonical();
			if ( ! $url ) {
				return;
			}
			$focus_keyword = $this->get_focus_keyword();

			$menu_args = array(
				'parent' => $this->menu_identifire,
				'id'     => $analysis_submenu_identifire,
				'title'  => __( 'Analyze this page', 'ext-for-all-in-one-seo-pack' ),
				'meta'   => array( 'tabindex' => '0' ),
			);
			$wp_admin_bar->add_menu( $menu_args );

			$encoded_url   = urlencode( $url );
			$submenu_items = array(
				array(
					'id'     => 'aioseopext-inlinks',
					'title'  => __( 'Check links to this URL', 'ext-for-all-in-one-seo-pack' ),
					'href'   => 'https://search.google.com/search-console/links/drilldown?resource_id=' . urlencode( get_option( 'siteurl' ) ) . '&type=EXTERNAL&target=' . $encoded_url . '&domain=',
				),
				array(
					'id'     => 'aioseopext-kwdensity',
					'title'  => __( 'Check Keyphrase Density', 'ext-for-all-in-one-seo-pack' ),
					// HTTPS not available.
					'href'   => 'http://www.zippy.co.uk/keyworddensity/index.php?url=' . $encoded_url . '&keyword=' . urlencode( $focus_keyword ),
				),
				array(
					'id'     => 'aioseopext-cache',
					'title'  => __( 'Check Google Cache', 'ext-for-all-in-one-seo-pack' ),
					'href'   => '//webcache.googleusercontent.com/search?strip=1&q=cache:' . $encoded_url,
				),
				array(
					'id'     => 'aioseopext-header',
					'title'  => __( 'Check Headers', 'ext-for-all-in-one-seo-pack' ),
					'href'   => '//quixapp.com/headers/?r=' . urlencode( $url ),
				),
				array(
					'id'     => 'aioseopext-structureddata',
					'title'  => __( 'Google Structured Data Test', 'ext-for-all-in-one-seo-pack' ),
					'href'   => 'https://search.google.com/structured-data/testing-tool#url=' . $encoded_url,
				),
				array(
					'id'     => 'aioseopext-facebookdebug',
					'title'  => __( 'Facebook Debugger', 'ext-for-all-in-one-seo-pack' ),
					'href'   => '//developers.facebook.com/tools/debug/og/object?q=' . $encoded_url,
				),
				array(
					'id'     => 'aioseopext-pinterestvalidator',
					'title'  => __( 'Pinterest Rich Pins Validator', 'ext-for-all-in-one-seo-pack' ),
					'href'   => 'https://developers.pinterest.com/tools/url-debugger/?link=' . $encoded_url,
				),
				array(
					'id'     => 'aioseopext-htmlvalidation',
					'title'  => __( 'HTML Validator', 'ext-for-all-in-one-seo-pack' ),
					'href'   => '//validator.w3.org/check?uri=' . $encoded_url,
				),
				array(
					'id'     => 'aioseopext-cssvalidation',
					'title'  => __( 'CSS Validator', 'ext-for-all-in-one-seo-pack' ),
					'href'   => '//jigsaw.w3.org/css-validator/validator?uri=' . $encoded_url,
				),
				array(
					'id'     => 'aioseopext-pagespeed',
					'title'  => __( 'Google Page Speed Test', 'ext-for-all-in-one-seo-pack' ),
					'href'   => '//developers.google.com/speed/pagespeed/insights/?url=' . $encoded_url,
				),
				array(
					'id'     => 'aioseopext-google-mobile-friendly',
					'title'  => __( 'Mobile-Friendly Test', 'ext-for-all-in-one-seo-pack' ),
					'href'   => 'https://www.google.com/webmasters/tools/mobile-friendly/?url=' . $encoded_url,
				),
			);

			foreach ( $submenu_items as $menu_item ) {
				$menu_args = array(
					'parent' => $analysis_submenu_identifire,
					'id'     => $menu_item['id'],
					'title'  => $menu_item['title'],
					'href'   => $menu_item['href'],
					'meta'   => array( 'target' => '_blank' ),
				);
				$wp_admin_bar->add_menu( $menu_args );
			}
		}

		private function get_focus_keyword(){
			return '';
		}

		/**
		 *
		 * Retrieves the canonical URL for the current page.
		 *
		 * @return string
		 */
		private function get_canonical() {
			
			global $aiosp, $aioseop_options, $wp_query;
		
			$show_page = true;
			if ( ! empty( $aioseop_options['aiosp_no_paged_canonical_links'] ) ) {
				$show_page = false;
			}
			$opts = $aiosp->meta_opts;
			$url = '';
			if ( isset( $aioseop_options['aiosp_can'] ) && $aioseop_options['aiosp_can'] ) {
				$url = '';
				if ( ! empty( $opts['aiosp_custom_link'] ) && ! is_home() ) {
					$url = $opts['aiosp_custom_link'];
					if ( apply_filters( 'aioseop_canonical_url_pagination', $show_page ) ) {
						$url = $aiosp->get_paged( $url );
					}
				}
			}
			if ( empty( $url ) ) {
				$url = $aiosp->aiosp_mrt_get_url( $wp_query, $show_page );
			}
			$url = $aiosp->validate_url_scheme( $url );
			$url = apply_filters( 'aioseop_canonical_url', $url );
			
			return $url;
		}

		
	}//end class
}