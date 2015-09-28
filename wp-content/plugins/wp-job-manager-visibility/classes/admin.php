<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Job_Manager_Visibility_Admin {

	/**
	 * WP_Job_Manager_Visibility_Admin constructor.
	 */
	public function __construct() {

		if( get_option('job_manager_visibility_enabled') ){
			//new WP_Job_Manager_Visibility_Admin_WritePanels();
			//new WP_Job_Manager_Visibility_Admin_Ajax();
		}

		new WP_Job_Manager_Visibility_Admin_Assets();
		new WP_Job_Manager_Visibility_Admin_Default();
		//new WP_Job_Manager_Visibility_Admin_Custom();
		new WP_Job_Manager_Visibility_Admin_Groups();

		if( get_option( 'jmv_disable_heartbeat' ) ) add_action( 'init', array($this, 'death_to_heartbeat'), 1 );
		add_filter( 'sanitize_option_jmv_disable_postlock', array($this, 'death_to_postlock'), 1 );

	}

	/**
	 * Set disabled_post_lock on CPT
	 *
	 * Based on settings this will either set, or remove disabled_post_lock on
	 * this plugins custom post types.  To do this we hook into the sanitize
	 * filter to only execute when the option has been changed.
	 *
	 *
	 * @since @@version
	 *
	 * @param $value
	 *
	 * @return mixed
	 */
	function death_to_postlock( $value ){

		// Null means option was deselected, 1 means postlock disable was checked
		// 0 means add_option was called by settings class to add option
		if( $value === null || $value === 1 ){
			$post_types = WP_Job_Manager_Visibility_CPT::get_post_types();
			$post_type_support_method = $value === 1 ? 'add_post_type_support' : 'remove_post_type_support';
			foreach ( $post_types as $post_type ) {
				$post_type_support_method( $post_type, 'disabled_post_lock' );
			}
		}

		return $value;
	}

	/**
	 * Deregister WP Heartbeat Script
	 *
	 * @since @@version
	 *
	 */
	function death_to_heartbeat() {
		if ( $this->is_plugin_page() ) {
			if( wp_script_is( 'heartbeat', 'registered' ) || wp_script_is( 'heartbeat', 'enqueued' ) ){
				wp_deregister_script( 'heartbeat' );
			}
		}
	}

	/**
	 * Check if current page is one of plugin pages
	 *
	 *
	 * @since @@version
	 *
	 * @return bool
	 */
	function is_plugin_page() {

		global $pagenow;

		$post_types = WP_Job_Manager_Visibility_CPT::get_post_types();
		$post_types[] = 'job_listing';
		$post_types[] = 'resume';

		$current_post_type = ( isset( $_GET[ 'post_type' ] ) ? $_GET[ 'post_type' ] : '' );
		if ( in_array( $current_post_type, $post_types ) ) return TRUE;

		return FALSE;
	}

}