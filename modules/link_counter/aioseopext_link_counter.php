<?php 
/**
 * Link Counter Module
 *
 * @package Extensions_For_All_In_One_SEO_Pack
 * @since 1.0
 */

if ( ! class_exists( 'All_in_One_SEO_Pack_Link_Counter' ) ) {

	/**
	 * Class All_in_One_SEO_Pack_Link_Counter
	 */
	class All_in_One_SEO_Pack_Link_Counter extends All_in_One_SEO_Pack_Module {

		/**
		 * Module Slug
		 *
		 * @since 1.0
		 *
		 * @var string $module_slug
		 */
		public $slug = 'link_counter';

		/**
		 * Links Table
		 * @since 1.0
		 *
		 * @var string
		 */
		public $links_table;

		/**
		 * Dashboard Settings Page name for this module
		 * @since 1.0
		 *
		 * @var string
		 */
		public $dashboard_page;

		
		/**
		 * @var object of AIOSEOPEXT_Link_Counter_Processor
		 */
		public $processor;

		/**
		 * @var object of AIOSEOPEXT_Link_Counter_Post_List_Column_Manager
		 */
		public $column_manager;

		/**
		 * @var object of AIOSEOPEXT_Link_Counter_Stat_Manager
		 */
		public $stat_manager;

		
		/**
		 * All_in_One_SEO_Pack_Link_Counter constructor.
		 */
		function __construct() {
			global $wpdb;
			$this->name   = __( 'Link Counter', 'ext-for-all-in-one-seo-pack' );    // Human-readable name of the plugin.
			$this->prefix = 'aioseopext_link_counter_';                        // Option prefix.
			$this->file   = __FILE__;                                    // The current file.
			$this->links_table = $wpdb->prefix.'aioseopext_links';
			$this->dashboard_page = plugin_basename( $this->file );
			//delete_option('aioseopext_link_counter_status');
			parent::__construct();

			if( !class_exists('AIOSEOPEXT_Link_Counter_Processor') ) {
				require_once( AIOSEOPEXT_PLUGIN_MODULES_DIR . "link_counter/class-aioseopext-link-counter-processor.php" );
			}
			$this->processor = new AIOSEOPEXT_Link_Counter_Processor( $this->links_table );

			if( is_admin() ) {

				if( !class_exists('AIOSEOPEXT_Link_Counter_Processor') ) {
					require_once( AIOSEOPEXT_PLUGIN_MODULES_DIR . "link_counter/class-aioseopext-link-counter-processor.php" );
				}
				$this->processor = new AIOSEOPEXT_Link_Counter_Processor( $this->links_table );

				if( !class_exists('AIOSEOPEXT_Link_Counter_Column_Manager') ) {
					require_once( AIOSEOPEXT_PLUGIN_MODULES_DIR . "link_counter/class-aioseopext-link-counter-column-manager.php" );
				}
				$this->column_manager = new AIOSEOPEXT_Link_Counter_Column_Manager( $this );
				
				if( !class_exists('AIOSEOPEXT_Link_Counter_Stat_Manager') ) {
					require_once( AIOSEOPEXT_PLUGIN_MODULES_DIR . "link_counter/class-aioseopext-link-counter-stat-manager.php" );
				}
				$this->stat_manager = new AIOSEOPEXT_Link_Counter_Stat_Manager( $this );

			}


			$this->default_options = array(
				
			);

			$stat_options = array(
				'stat' => array(
					'default' => '',
					'type'    => 'html',
					'label'   => 'none',
					'save'    => false,
				)
			);

			$process_options = array(
				'process' => array(
					'default' => '',
					'type'    => 'html',
					'label'   => 'none',
					'save'    => false,
				)
			);

			$this->layout = array(
				'stat'  => array(
					'name'      => __( 'Stat', 'ext-for-all-in-one-seo-pack' ),
					'options'   => array_keys( $stat_options ),
				),
				'process'  => array(
					'name'      => __( 'Action', 'ext-for-all-in-one-seo-pack' ),
					'options'   => array_keys( $process_options ),
				),
				
			);

			$this->default_options = array_merge( $stat_options,$process_options, $this->default_options );

			add_filter( $this->prefix . 'display_options', array( $this, 'filter_display_options' ) );

			if( ! has_action( 'aiospext_activate_module_'.$this->slug ) ) {
				add_action( 'aiospext_activate_module_'.$this->slug , array( $this, 'activation' ) );
			}

			if( ! has_action( 'aiospext_deactivate_module_'.$this->slug ) ) {
				add_action( 'aiospext_deactivate_module_'.$this->slug, array( $this, 'deactivation' ) );
			}

			
			if ( is_admin() ) {
				add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ), 20 );
			}


		}

		/**
		 * Checks if the current page is the dashboard page for this module.
		 *
		 * @return bool
		 */
		private function is_dashboard_page() {
			return ( filter_input( INPUT_GET, 'page' ) === $this->dashboard_page );
		}		

		/**
		 *	Do something on activation
		 */
		public function activation() {
			$this->create_db_tables();
		}

		/**
		 *	Do something on deactivation
		 */
		public function deactivation() {
			//deactivating
		}

		/**
		 *	Create database tables for this module
		 */
		private function create_db_tables() {

		   require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			
		   $links_table = $this->links_table;

		   $links_table_sql = "CREATE TABLE IF NOT EXISTS $links_table (
		      id bigint(20) NOT NULL AUTO_INCREMENT,
			  from_post_id bigint(20) NOT NULL DEFAULT '0',
			  to_post_id bigint(20) NOT NULL DEFAULT '0',
			  url varchar(255) NOT NULL,
			  type varchar(8) NOT NULL,
			  host varchar(100) NULL,
			  target varchar(10) NULL,
			  rel varchar(100) NULL,

		      UNIQUE KEY id (id)
		    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
		    ";
		 
		    dbDelta($links_table_sql);

		}

		/**
		 * Filter display options.
		 *
		 * Show custom HTML on settings page
		 *
		 * @since 1.0
		 *
		 * @param $options
		 * @return mixed
		 */
		public function filter_display_options( $options ) {
			
			$options[ $this->prefix . 'stat' ] = $this->get_stat_html();
			$options[ $this->prefix . 'process' ] = $this->get_process_html();

			return $options;
		}

		/**
		 * Generate HTML for stat data
		 * @return string
		 */
		private function get_stat_html() {
			return $this->stat_manager->get_stat_html();
		}

		/**
		 * Generate HTML for processing all posts to calculate links
		 * @return string
		 */
		private function get_process_html() {
			$status = $this->processor->get_status();

			$html = '';
			$btn_text = esc_attr__("Start counting", 'ext-for-all-in-one-seo-pack');
			$btn_text2 = esc_attr__("Stop counting", 'ext-for-all-in-one-seo-pack');
			$modal_title = esc_attr__("Counting Process", 'ext-for-all-in-one-seo-pack');
			$counted_alreay = ( isset( $status['counted_alreay'] ) ) ? intval( $status['counted_alreay'] ) : 0;
			$has_unprocessed_items = ( isset( $status['has_unprocessed_items'] ) ) ? intval( $status['has_unprocessed_items'] ) : 0;
			$progress_status_msg = '';
			$progress_percentage = 0;
			if( $has_unprocessed_items ) {
				$progress_status_msg = $this->processor->get_progress_message();
				$progress_percentage = $this->processor->get_progress_percentage();
			}
			ob_start();
			?>
			<div id="lc_process_box_1">
				<?php
				if( $counted_alreay ) {
					_e('We have processed all posts and counted links.', 'ext-for-all-in-one-seo-pack');
					$btn_text = esc_attr__("Start counting again", 'ext-for-all-in-one-seo-pack'); 
				} 

				if ( !$counted_alreay && !$has_unprocessed_items ) {
					echo '<p>';
					_e('We need to processe all posts to count links. Click on the button below to start proessing. Do not close the browser until the process done', 'ext-for-all-in-one-seo-pack');
					echo '</p>';
					
				} 
				if( $has_unprocessed_items ) {
					echo '<p>';
					_e('A counting process was started before but was not completed.', 'ext-for-all-in-one-seo-pack');
					echo '</p>';
					$btn_text = esc_attr__("Start counting again", 'ext-for-all-in-one-seo-pack');
					
				}
				?>
					<p>
						<a href="#" id="aioseopext_link_counter_start_process" class="button aioseopext-link-counter-btn"><?php echo $btn_text ?></a>
					</p>
			</div>
			<div id="lc_process_box_2" class="hidden">
				<p><?php _e('Do not close the browser until the process done', 'ext-for-all-in-one-seo-pack'); ?></p>
				<div id="aioseopext_lc_progressbar_wrap"><div id="aioseopext_lc_progressbar" style="width:<?php echo esc_attr( $progress_percentage ) ?>%"></div></div>
				<div id="aioseopext_lc_progress_status"><?php echo $progress_status_msg ?></div>
				<p>
					<a href="#" id="aioseopext_link_counter_stop_process" class="button aioseopext-link-counter-btn"><?php echo $btn_text2 ?></a>
				</p>
			</div>
			<?php 

			$html = ob_get_clean();
			return $html;
		}


		/**
		 * Add script and style file to admin
		 */
		public function admin_scripts() {
			if ( ! $this->is_dashboard_page() ) {
				return;
			}
			wp_enqueue_script( 'jquery' );
			
			wp_enqueue_script(
				'aioseopext-module-link-counter-script',
				AIOSEOPEXT_PLUGIN_MODULES_URL . $this->slug.'/js/aioseopext-link-counter.js',
				array('jquery'),
				AIOSEOPEXT_VERSION
			);

			wp_enqueue_style(
				'aioseopext-module-link-counter-style',
				AIOSEOPEXT_PLUGIN_MODULES_URL . $this->slug.'/css/aioseopext-link-counter.css',
				array(),
				AIOSEOPEXT_VERSION
			);

			//localize vars
			$data = array(
				'ajax_action_name'=> $this->processor::AJAX_ACTION_NAME,
				'nonce' => wp_create_nonce( $this->processor::AJAX_ACTION_NAME )
			);
			wp_localize_script( 'aioseopext-module-link-counter-script', 'aioseopext_link_counter_vars', $data ); 

		}

		
		
		
	}//end class
}