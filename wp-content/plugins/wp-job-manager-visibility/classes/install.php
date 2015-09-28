<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Job_Manager_Visibility_Install extends WP_Job_Manager_Visibility_CPT {

	/**
	 * WP_Job_Manager_Visibility_Admin_Install constructor.
	 *
	 * @param $capabilities
	 */
	public function __construct() {

		add_action( 'admin_init', array( $this, 'check' ) );
		parent::__construct();
	}

	/**
	 * Check if should include install file
	 *
	 * @since @@version
	 *
	 */
	public function check() {

		$current_version  = get_option( 'job_manager_visibility_version' );
		$plugin_activated = get_option( 'job_manager_visibility_activated' );

		if ( $plugin_activated || ! $current_version || version_compare( JOB_MANAGER_VISIBILITY_VERSION, $current_version, '>' ) ) {
			// Remove option if was set on plugin activation
			if ( $plugin_activated ) delete_option( 'job_manager_visibility_activated' );

			$this->init_user_roles();
			WP_Job_Manager_Visibility_Roles::add_anonymous();
		}

	}

	/**
	 * Init user roles
	 *
	 * @access public
	 * @return void
	 *
	 * @since @@version
	 */
	public function init_user_roles() {

		global $wp_roles;

		if ( class_exists( 'WP_Roles' ) && ! isset( $wp_roles ) )
			$wp_roles = new WP_Roles();

		if ( is_object( $wp_roles ) ) {

			if ( empty( $this->capabilities ) ) $this->init_capabilities();

			foreach ( $this->capabilities as $type => $cap ) {
				$wp_roles->add_cap( 'administrator', $cap );
			}

		}
	}

}