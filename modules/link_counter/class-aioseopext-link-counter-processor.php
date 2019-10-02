<?php

/**
 * Link Counter Processor
 *
 * @package Extensions_For_All_In_One_SEO_Pack
 * @since 1.0
 */

if ( ! class_exists( 'AIOSEOPEXT_Link_Counter_Processor' ) ) {
	class AIOSEOPEXT_Link_Counter_Processor {

		/**
		 * Post meta name for incoming internal link count.
		 *
		 * @var string
		 */
		const INCOMING_LINK_COUNT_POST_META = '_aioseopext_lc_incoming_link_count';

		/**
		 * Post meta name for outgoing internal link count.
		 *
		 * @var string
		 */
		const OUTGOING_INTERNAL_LINK_COUNT_POST_META = '_aioseopext_lc_outgoing_internal_link_count';

		/**
		 * Post meta name for outgoing external link count.
		 *
		 * @var string
		 */
		const OUTGOING_EXTERNAL_LINK_COUNT_POST_META = '_aioseopext_lc_outgoing_external_link_count';


		/**
		 * Options field name to store last status of counter
		 * @since 1.0
		 *
		 * @var string
		 */
		const STATUS_FIELD_NAME = 'aioseopext_link_counter_status';


		/**
		 * Ajax nonce field name
		 * @since 1.0
		 *
		 * @var string
		 */
		const AJAX_ACTION_NAME = 'aioseopext_link_counter_process';


		/**
		 * Process posts per request
		 * @since 1.0
		 *
		 * @var int
		 */
		public $process_posts_per_request = 10;


		/**
		 * Last counter status
		 * @since 1.0
		 *
		 * @var array | null
		 */
		public $status = null;

		/**
		 * Total posts count
		 * @since 1.0
		 *
		 * @var int
		 */
		public $total_post_count;

		/**
		 * @var object of AIOSEOPEXT_Link_Counter_Link_Manager
		 */
		public $link_manager;

		/**
		 * Links Table
		 * @since 1.0
		 *
		 * @var string
		 */
		public $links_table;


		/**
		 * Constructor.
		 */
		public function __construct( $links_table ) {

			$this->links_table = $links_table;
			if( !class_exists('AIOSEOPEXT_Link_Counter_Link_Manager') ) {
				require_once( AIOSEOPEXT_PLUGIN_MODULES_DIR . "link_counter/class-aioseopext-link-counter-link-manager.php" );
			}
			$this->link_manager = new AIOSEOPEXT_Link_Counter_Link_Manager();
			
			add_action( 'wp_ajax_'.self::AJAX_ACTION_NAME, array( $this, 'process_ajax' ) );

			if ( is_admin() ) {
				add_action( 'admin_init', array( $this, 'admin_init') );
				
			}
			
			
		}

		/**
		 * Admin init
		 */
		public function admin_init() {
			add_action( 'publish_post', array( $this, 'do_after_publish_post' ), 10, 2 );
			add_action( 'delete_post', array( $this, 'do_after_delete_post' ), 10 );
			add_action( 'transition_post_status', array( $this, 'do_after_unpublish_post' ), 10, 3 );

		}
		/**
		 * Handle ajax request
		 */
		public function process_ajax() {
			check_admin_referer( self::AJAX_ACTION_NAME );
		
			$response = array();

			//check permision 
			if( !current_user_can( 'manage_options' ) ){
				$response['status'] = 'fail';
				$response['message'] = __( 'You are  not permitted to do this', 'ext-for-all-in-one-seo-pack' );
				wp_send_json( $response );
				return;
			}
			
			$status = $this->get_status();
			$offset = 0;
			if( $status['has_unprocessed_items'] == 1 ) {
				$offset = isset( $status['last_processed_items_count'] ) ?  $status['last_processed_items_count'] : 0;
			}

			$total_post_count = $this->get_post_count();
			
			$result = $this->process_posts( array('offset' => $offset ) );

			$total_processed = $offset + $this->process_posts_per_request;
			if( $total_processed >= $total_post_count ) {
				$status['has_unprocessed_items'] = 0;
				$status['last_processed_items_count'] = 0;
				$status['counted_alreay'] = 1;
				$this->set_status( $status );

				$response['completed'] = 1;
				$response['progress_percentage'] = 100;
				$response['progress_msg'] = $this->get_progress_message();

			} else {
				$status['has_unprocessed_items'] = 1;
				$status['last_processed_items_count'] = $total_processed;
				$this->set_status( $status );

				$response['last_processed_items_count'] = $total_processed;
				$response['completed'] = 0;
				$response['progress_percentage'] = $this->get_progress_percentage();
				$response['progress_msg'] = $this->get_progress_message();
			}
			
			$response['total_post_count'] = $total_post_count;
			$response['status'] = 'success';
	 		
			
	 		
	 		wp_send_json( $response );

		}

		/**
		 * Process the post when a post is published
		 */
		public function do_after_publish_post( $post_id, $post ) {
			$post_types = $this->get_post_types();
			if( !in_array($post->post_type, $post_types ) ) {
				return;
			}
			$this->calculate_links_for_a_post( $post );
		}

		/**
		 * Process the post when a post is deleted or remove published status
		 */
		public function do_after_delete_post( $post_id ) {
			
			$this->delete_links_for_a_post( $post_id );
			
		}

		/**
		 * Process the post when a post status is changed from published to anything else
		 */
		public function do_after_unpublish_post( $new_status, $old_status, $post ) {
			
			$post_types = $this->get_post_types();
			if( !in_array($post->post_type, $post_types ) ) {
				return;
			}

			if ( 'publish' !== $old_status || 'publish' === $new_status ) {
		            return;
		    }

			$this->delete_links_for_a_post( $post->ID );
		}


		/**
		 * Get all allowed post types 
		 *
		 * @return array
		 */
		public function get_post_types() {
			$opt = aioseop_get_options();
			$post_types = ( isset( $opt['aiosp_cpostactive'] ) && is_array( $opt['aiosp_cpostactive'] ) ) ? $opt['aiosp_cpostactive'] : array('post');
			return $post_types;
		}

		/**
		 * Get total allowed posts count
		 * @return int
		 */
		public function get_post_count() {
			if( isset( $this->total_post_count ) ) {
				return $this->total_post_count;
			}

			$post_types = $this->get_post_types();
			$total = 0;
			foreach( $post_types as $post_type ) {
				$count_posts = wp_count_posts( $post_type );
				$total += $count_posts->publish;
			}
			$this->total_post_count = $total;
			return $total;
		}

		/**
		 * Process posts to find and count links from post content
		 * @param array $args
		 * @return array
		 */
		public function process_posts( $args = array() ) {
			$post_types = $this->get_post_types();
			$offset = isset( $args['offset'] ) ? $args['offset'] :0;
			$query_args = array(
				'post_type' => $post_types,
				'post_status' => 'publish',
				'offset' => $args['offset'],
				'posts_per_page' => $this->process_posts_per_request,
			);
			$posts_array = get_posts( $query_args );
			$found_post_count = count( $posts_array );
			foreach ($posts_array as $post ) {
				$data = $this->calculate_links_for_a_post( $post );	
			}


		}

		/** 
		 * Calculate links from a postdata
		 * @param object $post
		 * 
		 */
		public function calculate_links_for_a_post( $post ) {
			
			$post_id = $post->ID;
			$links_details = $this->link_manager->get_links_details( $post );
			$this->insert_links_to_db_and_update_post_meta( $links_details , $post_id );

		}

		/**
		 * Inserts links detail to database and update posts meta related to links
		 * 
		 * @param array $links
		 * @return void
		 */ 
		private function insert_links_to_db_and_update_post_meta( $links, $post_id ) {
			global $wpdb;

			//Delete all existing links related to current post id
			$this->delete_links_for_a_post( $post_id );

			$to_post_ids = array();
			//Insert new links
			foreach( $links as $link ) {
				if( $link['to_post_id'] != 0 &&  $link['type'] == $this->link_manager::TYPE_INTERNAL ) {
					$to_post_ids[] = $link['to_post_id'];
				}
				
				$sql = $wpdb->prepare( "INSERT INTO ".$this->links_table." ( from_post_id, to_post_id,url, type, host, target, rel ) VALUES(%d, %d, %s, %s, %s, %s, %s) ", $link['from_post_id'], $link['to_post_id'], $link['url'], $link['type'], $link['host'], $link['target'],$link['rel'] );
				$res = $wpdb->query( $sql );
			}
			
			//UPATE META
			$this->update_link_related_post_meta( $post_id );

			if( count( $to_post_ids ) ) {
				$to_post_ids = array_unique( $to_post_ids, SORT_NUMERIC );
				foreach ($to_post_ids as $id) {
					$this->update_link_related_post_meta( $id );
				}
			}
			

		}

		/**
		 * Deletes all existing links related to current post id
		 * @param int $post_id
		 */
		private function delete_links_for_a_post( $post_id ) {
			global $wpdb;
			$sql = $wpdb->prepare("DELETE FROM ".$this->links_table." WHERE from_post_id = %d", $post_id );
			$res = $wpdb->query( $sql );
		}

		/**
		 * Counts links for a post id from links table and updates post meta
		 * 
		 * @param int $post_id
		 * @return void
		 */
		private function update_link_related_post_meta( $post_id ) {
			global $wpdb;
			$sql = $wpdb->prepare("SELECT COUNT(*) FROM ".$this->links_table." WHERE from_post_id = %d AND type = %s", $post_id, $this->link_manager::TYPE_INTERNAL );
			$outgoing_internal_link_count =  $wpdb->get_var( $sql );

			$sql = $wpdb->prepare("SELECT COUNT(*) FROM ".$this->links_table." WHERE from_post_id = %d AND type = %s", $post_id, $this->link_manager::TYPE_EXTERNAL );
			$outgoing_external_link_count =  $wpdb->get_var( $sql );

			$sql = $wpdb->prepare("SELECT COUNT(*) FROM ".$this->links_table." WHERE to_post_id = %d", $post_id );
			$incoming_link_count =  $wpdb->get_var( $sql );

			update_post_meta( $post_id , self::OUTGOING_INTERNAL_LINK_COUNT_POST_META , intval( $outgoing_internal_link_count ) );
			update_post_meta( $post_id , self::OUTGOING_EXTERNAL_LINK_COUNT_POST_META , intval( $outgoing_external_link_count ) );
			update_post_meta( $post_id , self::INCOMING_LINK_COUNT_POST_META , intval( $incoming_link_count ) );
		}

		/**
		 * Get status array from options table
		 */
		public function get_status() {
			if( $this->status !== null ) {
				return $this->status;
			}
			$status = get_option( self::STATUS_FIELD_NAME , array() );
			if( !is_array( $status ) ) {
				$status = array();
			}

			if( !isset( $status['counted_alreay'] ) ) {
				$status['counted_alreay'] = 0;
			}
			if( !isset( $status['has_unprocessed_items'] ) ) {
				$status['has_unprocessed_items'] = 0;
			}
			
			$this->status = $status;
			return $this->status;
		}

		/**
		 * Set status array to options table
		 * @param array $status
		 */
		public function set_status( $status ) {
			update_option( self::STATUS_FIELD_NAME , $status );
			$this->status = $status;
		}

		/**
		 * Prepares progress percentage value 
		 * @return float
		 */
		public function get_progress_percentage() {
			$status = $this->get_status();
			$total_post_count = $this->get_post_count();
			if( $total_post_count == 0 ) {
				return 0;
			}
			$total_processed = isset( $status['last_processed_items_count'] ) ? $status['last_processed_items_count'] : 0;
			return round( ( ( $total_processed / $total_post_count ) * 100) , 2 );
		}

		/**
		 * Prepares progress status message 
		 * @return string
		 */
		public function get_progress_message() {
			$status = $this->get_status();
			$total_post_count = $this->get_post_count();
			$total_processed = isset( $status['last_processed_items_count'] ) ? $status['last_processed_items_count'] : 0;
			$has_unprocessed_items = isset( $status['has_unprocessed_items'] ) ? $status['has_unprocessed_items'] : 0;
			if( $has_unprocessed_items ) {
				$msg = sprintf( __( "%d of %d posts have been processed", 'all-in-one-seo-pack' ), $total_processed, $total_post_count );
			} else {
				$msg = __( 'Process completed! We have processed all posts and counted links.', 'ext-for-all-in-one-seo-pack' );
			}
			return $msg;
		}

		/**
		 * Caluclates the number of posts those have outgoing links
		 * @return int
		 */
		public function get_total_posts_conatining_outgoing_links() {
			global $wpdb;
			$sql = "SELECT COUNT( DISTINCT from_post_id ) FROM ".$this->links_table;
			$val = $wpdb->get_var( $sql );
			return $val;

		}

		/**
		 * Caluclates the number of posts those have outgoing internal links
		 * @return int
		 */
		public function get_total_posts_containing_outgoing_internal_links() {
			global $wpdb;
			$sql = $wpdb->prepare("SELECT COUNT( DISTINCT from_post_id ) FROM ".$this->links_table." WHERE type=%s", $this->link_manager::TYPE_INTERNAL);
			$val = $wpdb->get_var( $sql );
			return $val;
		}

		/**
		 * Caluclates the number of posts those have outgoing external links
		 * @return int
		 */
		public function get_total_posts_containing_outgoing_external_links() {
			global $wpdb;
			$sql = $wpdb->prepare("SELECT COUNT( DISTINCT from_post_id ) FROM ".$this->links_table." WHERE type=%s", $this->link_manager::TYPE_EXTERNAL);
			$val = $wpdb->get_var( $sql );
			return $val;
		}

		/**
		 * Caluclates the number of posts those have incoming links
		 * @return int
		 */
		public function get_total_posts_conatining_incoming_links() {
			global $wpdb;
			$sql = "SELECT COUNT( DISTINCT to_post_id ) FROM ".$this->links_table." WHERE to_post_id != 0";
			$val = $wpdb->get_var( $sql );
			return $val;
		}

		/**
		 * Caluclates unique outgoing external links used in all posts
		 * @return int
		 */
		public function get_total_unique_outgoing_links() {
			//
		}


	}
}