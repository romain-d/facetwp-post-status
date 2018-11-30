<?php
/*
Plugin Name: FacetWP - Post Status
Plugin URI:  https://github.com/romain-d/facetwp-post-status
Description: Add a post status facet for the plugin FacetWP
Version:     1.0.0
Author:      Romain DORR
Author URI:  https://github.com/romain-d
License:     GPL2+
Text Domain: facetwp-post-status
*/

// don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

define( 'FWP_POST_STATUS_VER', '2.0.0' );
define( 'FWP_POST_STATUS_URL', plugin_dir_url( __FILE__ ) );
define( 'FWP_POST_STATUS_DIR', plugin_dir_path( __FILE__ ) );

class FWP_Post_Status {

	/**
	 * @var FWP_Post_Status
	 */
	private static $instance;

	/**
	 * FWP_P2P constructor.
	 */
	private function __construct() {
		add_action( 'init', array( $this, 'init' ) );
	}

	/**
	 * @return FWP_Post_Status
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new static();
		}

		return self::$instance;
	}

	/**
	 * Register all hooks
	 */
	public function init() {
		add_filter( 'facetwp_facet_sources', array( $this, 'facetwp_facet_sources' ) );
		add_filter( 'facetwp_index_row', array( $this, 'facetwp_index_row' ), 10, 2 );

		add_filter( 'facetwp_indexer_query_args', array( $this, 'facetwp_indexer_query_args' ) );
	}

	/**
	 * Add Post Status sources.
	 *
	 * @param array $sources
	 *
	 * @return array
	 */
	public function facetwp_facet_sources( $sources = array() ) {
		$sources['posts']['choices']['post_status'] = __( 'Post Status', 'facetwp-post-status' );
		return $sources;
	}

	/**
	 * Index values Post Status.
	 *
	 * @param array $rows
	 * @param array $defaults
	 *
	 * @return array
	 */
	public function facetwp_index_row( $row, $facetwp ) {
		if ( 'post_status' != $row['facet_source'] ) {
			return $row;
		}

		$status = get_post_status_object( $row['facet_value'] );
		if ( empty( $status ) ) {
			return $row;
		}

		$row['facet_display_value'] = $status->label;

		return $row;
	}

	/**
	 * Change Facetwp get posts args for query all public statuses
	 *
	 * @param $args
	 *
	 * @return mixed
	 */
	public function facetwp_indexer_query_args( $args ) {
		$statuses = get_post_stati( array( 'public' => true ) );
		if ( empty( $statuses ) ) {
			return $args;
		}

		$args['post_status'] = $statuses;

		return $args;
	}
}

/**
 * FWP P2P accessor.
 *
 * @return FWP_Post_Status
 */
function FWP_Post_Status() {
	return FWP_Post_Status::instance();
}

/**
 * Print warning notice if requirements are not met.
 */
function FWP_Post_Status_notice() {
	$message = __( 'FWP Post Status requires FacetWP 3.0.0 or above to work.', 'facetwp-post-status' );
	if ( ! defined( 'FACETWP_VERSION' ) ) {
		$message .= ' ';
		$message .= __( 'FacetWP doesn\'t seem to be install on your site.', 'facetwp-post-status' );
	} else {
		$message .= ' ';
		$message .= sprintf(
			__( 'You currently have FacetWP %s.', 'facetwp-post-status' ),
			FACETWP_VERSION
		);
	}
	echo '<div class="error"><p>' . $message . '</p></div>';
}

/**
 * Init FWP Post Status.
 */
function FWP_Post_Status_init() {
	// Check
	if ( ! defined( 'FACETWP_VERSION' ) || version_compare( FACETWP_VERSION, '3.0.0', '<' ) ) {
		add_action( 'admin_notices', 'FWP_Post_Status_notice' );

		return;
	}

	FWP_Post_Status();
}

add_action( 'plugins_loaded', 'FWP_Post_Status_init' );