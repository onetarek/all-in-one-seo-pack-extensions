<?php

/**
 * State Manager
 *
 * @package Extensions_For_All_In_One_SEO_Pack
 * @since 1.0
 */

if ( ! class_exists( 'AIOSEOPEXT_Link_Counter_Stat_Manager' ) ) {
	class AIOSEOPEXT_Link_Counter_Stat_Manager {

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

		}

		/**
		 * Generate HTML for stat data
		 * @return string
		 */
		public function get_stat_html() {
			$status = $this->processor->get_status();
			$counted_alreay = ( isset( $status['counted_alreay'] ) ) ? intval( $status['counted_alreay'] ) : 0;
			if( $counted_alreay == 0 ) {
				return __('Stat is not ready yet. We need to processe all posts to count links first. Go to "Action" section below and start counting', "ext-for-all-in-one-seo-pack" );
			}
			$html = '';
			ob_start();
				
				?>
				<table class="widefat">
					<thead>
						<th style="width:350px;"></th>
						<th></th>
					</thead>
					<tbody>
						<tr>
							<td><?php _e("Total number of posts we have processed", "ext-for-all-in-one-seo-pack" )?></td>
							<td><?php echo $this->processor->get_post_count(); ?></td>
						</tr>

						<tr>
							<td><?php _e("Total number of posts those have outgoing links", "ext-for-all-in-one-seo-pack" )?></td>
							<td><?php echo $this->processor->get_total_posts_conatining_outgoing_links(); ?></td>
						</tr>
						<tr>
							<td><?php _e("Total number of posts those have outgoing internal links", "ext-for-all-in-one-seo-pack" )?></td>
							<td><?php echo $this->processor->get_total_posts_containing_outgoing_internal_links(); ?></td>
						</tr>
						<tr>
							<td><?php _e("Total number of posts those have outgoing external links", "ext-for-all-in-one-seo-pack" )?></td>
							<td><?php echo $this->processor->get_total_posts_containing_outgoing_external_links(); ?></td>
						</tr>
						<tr>
							<td><?php _e("Total number of posts those have incoming links", "ext-for-all-in-one-seo-pack" )?></td>
							<td><?php echo $this->processor->get_total_posts_conatining_incoming_links(); ?></td>
						</tr>
					</tbody>

				</table>
				<?php 
			$html = ob_get_clean();
			return $html;
		}

		

	}//END CLASS
}
