<?php

/**
 * Post List Column Manager
 *
 * @package All_in_One_SEO_Pack_Extensions
 * @since 1.0
 */

if ( ! class_exists( 'AIOSEOPEXT_Link_Counter_Column_Manager' ) ) {
	class AIOSEOPEXT_Link_Counter_Column_Manager {

		/**
		 * @var object of the main caller All_in_One_SEO_Pack_Link_Counter
		 */
		private $main;
		
		/**
		 * @var object of AIOSEOPEXT_Link_Counter_Processor
		 */
		private $processor;
		

		/**
		 * Constructor.
		 */
		function __construct( $main ) {
			$this->main = $main;
			$this->processor = $main->processor;
			
			if ( is_admin() ) {
				add_action( 'admin_init', array( $this, 'register_columns_hooks' ), 2 );
			}

		}

		/**
		 * Add some style and scripts to admin head section of post list page
		 */
		public function post_list_admin_head() {
			wp_enqueue_style(
				'aioseopext-module-link-counter-style',
				AIOSEOPEXT_PLUGIN_MODULES_URL . $this->main->slug.'/css/aioseopext-link-counter.css',
				array(),
				AIOSEOPEXT_VERSION
			);
		}

		/**
		 * Registers hooks to add and manage new columns to post list table
		 *
		 * Adds link counter columns.
		 *
		 * @since 1.0
		 */
		public function register_columns_hooks() {
			global $aioseop_options, $pagenow;
			$aiosp_posttypecolumns = array();
			if ( ! empty( $aioseop_options ) && ! empty( $aioseop_options['aiosp_posttypecolumns'] ) ) {
				$aiosp_posttypecolumns = $aioseop_options['aiosp_posttypecolumns'];
			}
			if ( ! empty( $pagenow ) && ( $pagenow === 'upload.php' ) ) {
				$post_type = 'attachment';
			} elseif ( ! isset( $_REQUEST['post_type'] ) ) {
				$post_type = 'post';
			} else {
				$post_type = $_REQUEST['post_type'];
			}
			if ( is_array( $aiosp_posttypecolumns ) && in_array( $post_type, $aiosp_posttypecolumns ) ) {
				add_action( 'admin_head', array( $this, 'post_list_admin_head' ) );
				
				if ( $post_type === 'page' ) {
					add_filter( 'manage_pages_columns', array( $this, 'add_columns') );
				} elseif ( $post_type === 'attachment' ) {
					//do nothing
				} else {
					add_filter( 'manage_posts_columns', array( $this, 'add_columns') );
				}

				if ( $post_type === 'attachment' ) {
					//do nothing
				} elseif ( is_post_type_hierarchical( $post_type ) ) {
					add_action( 'manage_pages_custom_column', array( $this, 'handle_columns'), 10, 2 );
				} else {
					add_action( 'manage_posts_custom_column', array( $this, 'handle_columns'), 10, 2 );
				}
			}
		}

		/**
		 * Adds Link Counter Columns To Post List
		 *
		 * @since 1.0
		 *
		 * @param array $columns
		 * @return array
		 */
		public function add_columns( $columns ) {
			global $aioseop_options;
			$columns['outgoing_internal_link_count'] = sprintf(
			'<span class="aioseopext-lc-colum-header aioseopext-lc-tooltip-toggle" tooltip-text="%1$s"><span class="aioseopext-lc-colum-header-icon aioseopext-lc-icon-out_int_link"><span style="display:none">%2$s</span></span></span>',
			esc_attr__( 'Number of outgoing internal links in posts.', 'all-in-one-seo-pack-ext' ),
			esc_attr__( 'Outgoing internal links', 'all-in-one-seo-pack-ext' )
			);

			$columns['outgoing_external_link_count']  = sprintf(
			'<span class="aioseopext-lc-colum-header aioseopext-lc-tooltip-toggle" tooltip-text="%1$s"><span class="aioseopext-lc-colum-header-icon aioseopext-lc-icon-out_ext_link"><span style="display:none">%2$s</span></span></span>',
			esc_attr__( 'Number of outgoing external links in posts.', 'all-in-one-seo-pack-ext' ),
			esc_attr__( 'Outgoing external links', 'all-in-one-seo-pack-ext' )
			);

			$columns['incoming_link_count']  = sprintf(
			'<span class="aioseopext-lc-colum-header aioseopext-lc-tooltip-toggle" tooltip-text="%1$s"><span class="aioseopext-lc-colum-header-icon aioseopext-lc-icon-inc_link"><span style="display:none">%2$s</span></span></span>',
			esc_attr__( 'Number of incoming links in posts.', 'all-in-one-seo-pack-ext' ),
			esc_attr__( 'Incoming links', 'all-in-one-seo-pack-ext' )
			);
			
			return $columns;
		}

		/**
		 * Handle Link Counter columns output
		 *
		 * @since 1.0
		 *
		 * @param array $columns
		 * @return array
		 */
		public function handle_columns( $column_name, $post_id ) {
			global $aioseop_options;
			if( !in_array($column_name, array('outgoing_internal_link_count', 'outgoing_external_link_count', 'incoming_link_count') ) ) {
				return ;
			}
			switch ($column_name) {
				case 'outgoing_internal_link_count':
					$val = intval( get_post_meta( $post_id, $this->processor::OUTGOING_INTERNAL_LINK_COUNT_POST_META , true ) );
					break;
				case 'outgoing_external_link_count':
					$val = intval( get_post_meta( $post_id, $this->processor::OUTGOING_EXTERNAL_LINK_COUNT_POST_META , true ) );
					break;	
				case 'incoming_link_count':
					$val = intval( get_post_meta( $post_id, $this->processor::INCOMING_LINK_COUNT_POST_META , true ) );
					break;
				
			}
			echo $val;
			
		}

	}//END CLASS
}
