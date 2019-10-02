<?php

/**
 * Link Manager
 *
 * @package Extensions_For_All_In_One_SEO_Pack
 * @since 1.0
 */

if ( ! class_exists( 'AIOSEOPEXT_Link_Counter_Link_Manager' ) ) {
	class AIOSEOPEXT_Link_Counter_Link_Manager {

		/**
		 * @var string
		 */
		const TYPE_EXTERNAL = 'external';

		/**
		 * @var string
		 */
		const TYPE_INTERNAL = 'internal';

		
		/**
		 * @var string
		 */
		private $base_url = '';

		/**
		 * @var string
		 */
		private $base_host = '';

		/**
		 * @var string
		 */
		private $base_path = '';

		/**
		 * @var object wp_post
		 */
		private $current_post = null;

		/**
		 * @var string
		 */
		private $current_post_path = '';


		/**
		 * Constructor.
		 */
		public function __construct() {

			$this->base_url = untrailingslashit( home_url() );
			$url_part = wp_parse_url( $this->base_url );
			if( isset($url_part['host'] ) ) {
				$this->base_host = $url_part['host'];
			}
			if( isset($url_part['path'] ) ) {
				$this->base_path = $url_part['path'];
			}
			
		}

		/**
		 * Sets Current post properties and calculated other related vars
		 *
		 * @param object $post
		 */
		private function set_current_post( $post ) {
			$this->current_post = $post;
			$url_parts = wp_parse_url( get_permalink( $post->ID ) );
			$this->current_post_path = isset( $url_parts['path'] ) ? untrailingslashit( $url_parts['path'] ) : "";
		}

		/**
		 * Extracts the href values from the string and returns them as an array.
		 *
		 * @return array All the extracted links
		 */
		public function extract_links( $str ) {
			$links = array();

			if ( strpos( $str, 'href' ) === false ) {
				return $links;
			}

			$regexp = '<a\s[^>]*href=("??)([^" >]*?)\\1[^>]*>';
			$regexp_target = '<a\s[^>]*target=("??)([^" >]*?)\\1[^>]*>';
			$regexp_rel = '<a\s[^>]*rel=("??)([^">]*?)\\1[^>]*>';//this rule allows spaces in target.
			// Used modifiers iU to match case insensitive and make greedy quantifiers lazy.
			if ( preg_match_all( "/$regexp/iU", $str, $matches, PREG_SET_ORDER ) ) {
			
				foreach ( $matches as $match ) {
					$link = array("url"=>"", "target" => "", "rel" => "");
					$link['url'] = trim( $match[2], "'" );
					$str2 = $match[0];
					if ( preg_match_all( "/$regexp_target/iU", $str2, $matches_target, PREG_SET_ORDER ) ) {
						$link['target'] = $matches_target[0][2];
					}

					if ( preg_match_all( "/$regexp_rel/iU", $str2, $matches_rel, PREG_SET_ORDER ) ) {
						$link['rel'] = $matches_rel[0][2];
					}

					$links[] = $link;

				}
			}
			return $links;
		}


		/**
		 * Prepares information for an url
		 *
		 * @param string $link
		 */
		private function get_link_detail( $link ) {
			
			$url_parts = wp_parse_url( untrailingslashit( $link ) );
			$host = isset( $url_parts['host'] ) ?  $url_parts['host'] : '';
			$info = array();
			$info['url'] = $link;
			$info['host'] = $host;
			$info['type'] = $this->get_link_type( $link );
			$info['allowed'] = $this->is_allowed( $link , $info['type'] );
			$info['to_post_id'] = 0;
			$info['from_post_id'] = $this->current_post->ID;
			if( $info['allowed'] == true && $info['type'] == self::TYPE_INTERNAL )
			{
				$info['to_post_id'] = url_to_postid( $link );
				if( $info['from_post_id'] == $info['to_post_id'] ) {
					$info['allowed'] = false;
				}

			}
			
			return $info;
		}

		/**
		 * Prepares information for all url in a post content
		 *
		 * @param object $post
		 */
		public function get_links_details( $post ) { 
			$this->set_current_post( $post );
			$content = apply_filters('the_content', $this->current_post->post_content );
			$links = $this->extract_links( $content );
			
			$links_details = array();

			foreach( $links as $link ) {
				$link_detail = $this->get_link_detail( $link['url'] );
				
				if( $link_detail['allowed'] ==  false ) {
					continue;
				}
				$link_detail['target'] = $link['target'];
				$link_detail['rel'] = $link['rel'];
				
				$links_details[] = $link_detail;
			}
			
			return $links_details;

		}

		
		/**
		 * Determines if the given link is an outbound or an internal link.
		 *
		 * @param string $link The link to classify.
		 *
		 * @return string Returns outbound or internal.
		 */
		private function get_link_type( $link ) {

			if ( $this->is_external_link( $link ) ) {
				return self::TYPE_EXTERNAL;
			}

			return self::TYPE_INTERNAL;
		}


		/**
		 * Checks whether a link is an external link.
		 *
		 * @param string $link.
		 *
		 * @return bool
		 */
		private function is_external_link( $link ) {
			
			$url_parts = wp_parse_url( untrailingslashit( $link ) );
			// wp_parse_url may return false.
			if ( ! is_array( $url_parts ) ) {
				$url_parts = array();
			}

			//Check whether a link starts with a protocol like http, https etc.
			if( ! isset( $url_parts['scheme'] ) || $url_parts['scheme'] === null ){
				return false;
			}

			//Check whether protocol is http or https 
			if ( isset( $url_parts['scheme'] ) && ! in_array( $url_parts['scheme'], array( 'http', 'https' ), true ) ) {
				return true;
			}
			// When the base host is equal to the host.
			if ( isset( $url_parts['host'] ) && $url_parts['host'] !== $this->base_host ) {
				return true;
			}

			/*
			 * From SEO perspective if base host is equal to the host , the link should be internal, not outbound.
			 * But here we will check path too.
			 */


			// There is no base path.
			if ( empty( $this->base_path ) ) {
				return false;
			}

			// When there is a path.
			if ( isset( $url_parts['path'] ) ) {
				return ( strpos( $url_parts['path'], $this->base_path ) !== 0 );
			}

			return true;
		}

		/**
		 * Check if the link is not the current post url, link can contains one fragment in the current post URL. Exclude those
		 *
		 * @param string $link.
		 *
		 * @return bool False when link is not allowed.
		 */
		
		private function is_allowed( $link , $type ) {

			$url_parts = wp_parse_url( untrailingslashit( $link ) );

			if( ! is_array( $url_parts ) || empty( $url_parts ) ) {
				return false;
			}

			// When the type is external.
			if ( $type === self::TYPE_EXTERNAL ) {
				return true;
			}

			if ( isset( $url_parts['path'] ) ) {
				$url_path = untrailingslashit( $url_parts['path'] );
				//return false when url is current page url
				return !( ! empty( $url_path ) && $url_path === $this->current_post_path  );

			}

			return ( ! isset( $url_parts['fragment'] ) && ! isset( $url_parts['query'] ) );
		}
		

	}
}