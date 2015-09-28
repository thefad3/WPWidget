<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Job_Manager_Visibility_Admin_Settings_Handlers extends WP_Job_Manager_Visibility_Admin_Settings_Fields {

	/**
	 * Settings Button Method Handler
	 *
	 *
	 * @since @@version
	 *
	 * @param $input
	 * @param $option
	 *
	 * @return bool
	 */
	public function cache_button_handler( $input, $option ) {

		if ( empty( $_POST[ 'button_submit' ] ) || ( $this->process_count > 0 ) ) return $input;

		$action = filter_input( INPUT_POST, 'button_submit', FILTER_SANITIZE_STRING );

		switch ( $action ) {

			case 'cache_purge_all':
				$user_cache = new WP_Job_Manager_Visibility_User_Transients();
				$user_cache->purge();
				$this->add_updated_alert( __( 'All cache has been purged/removed!', 'wp-job-manager-visibility' ) );
				break;

			case 'cache_purge_user':
				$user_cache = new WP_Job_Manager_Visibility_User_Transients();
				$user_cache->purge_user();
				$this->add_updated_alert( __( 'All user config cache has been purged/removed!', 'wp-job-manager-visibility' ) );
				break;

			case 'cache_flush_all':
				wp_cache_flush();
				wp_cache_init();
				$this->add_updated_alert( __( 'The core WordPress cache has been flushed!', 'wp-job-manager-visibility' ) );
				break;

			case 'cache_purge_groups':
				$user_cache = new WP_Job_Manager_Visibility_User_Transients();
				$user_cache->purge_group();
				$this->add_updated_alert( __( 'All user group config cache has been purged/removed!', 'wp-job-manager-visibility' ) );
				break;

		}

		$this->process_count ++;

		return FALSE;

	}

	/**
	 * Add WP Updated Alert
	 *
	 *
	 * @since @@version
	 *
	 * @param $message
	 */
	function add_updated_alert( $message ) {

		add_settings_error(
			$this->settings_group,
			esc_attr( 'settings_updated' ),
			$message,
			'updated'
		);

	}

	/**
	 * Add WP Error Alert
	 *
	 *
	 * @since @@version
	 *
	 * @param $message
	 */
	function add_error_alert( $message ) {

		add_settings_error(
			$this->settings_group,
			esc_attr( 'settings_error' ),
			$message,
			'error'
		);

	}

	/**
	 * Settings Button Handler
	 *
	 * Default handler that gets executed whenever the options are saved
	 * as long a there isn't another method that matches {$field_type}_handler
	 *
	 * @since @@version
	 *
	 * @param $input
	 * @param $option
	 *
	 * @return bool
	 */
	public function submit_handler( $input, $option ) {
		// If POST button_submit means a button field type was executed, and to prevent other options from being updated
		// we checks if POST is set for button_submit and return the actual value of the option (to prevent updating any options)
		if ( ! empty( $_POST[ 'button_submit' ] ) ) return get_option( $option );

		return $input;

	}
}