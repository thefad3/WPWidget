<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Job_Manager_Visibility_Admin_Assets {

	/**
	 * WP_Job_Manager_Visibility_Admin_Assets constructor.
	 */
	public function __construct() {

		add_action( 'admin_enqueue_scripts', array( $this, 'register' ), 99 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ), 999 );
		add_action( 'admin_enqueue_scripts', array($this, 'js'), 100 );
	}

	function js(){

		$support_ticket_url = 'https://plugins.smyl.es/support/new/';

		$translations = array(
			'error_submit_ticket'  => sprintf( __( 'If you continue receive this error, please submit a <a target="_blank" href="%s">support ticket</a>.', 'wp-job-manager-visibility' ), esc_url( $support_ticket_url ) ),
			'field_required'       => __( 'This field is required!', 'wp-job-manager-visibility' ),
			'yes'                  => __( 'Yes', 'wp-job-manager-visibility' ),
			'loading'              => __( 'Loading', 'wp-job-manager-visibility' ),
			'no'                   => __( 'No', 'wp-job-manager-visibility' ),
			'cancel'               => __( 'Cancel', 'wp-job-manager-visibility' ),
			'close'                => __( 'Close', 'wp-job-manager-visibility' ),
			'enable'               => __( 'Enable', 'wp-job-manager-visibility' ),
			'disable'              => __( 'Disable', 'wp-job-manager-visibility' ),
			'error'                => __( 'Error', 'wp-job-manager-visibility' ),
			'unknown_error'        => __( 'Uknown Error! Refresh the page and try again.', 'wp-job-manager-visibility' ),
			'success'              => __( 'Success', 'wp-job-manager-visibility' ),
			'ays_remove'           => __( 'Are you sure you want to remove this configuration?', 'wp-job-manager-visibility' ),
			'error_metakey_in_visible' => __( 'You can\'t add a meta key to hide if it\'s set as a visible field!', 'wp-job-manager-visibility' )
		);

		wp_localize_script( 'jmv-admin-js', 'jmrvlocale', $translations );
	}

	/**
	 * Register Admin CSS & JS
	 *
	 *
	 * @since @@version
	 *
	 */
	function register(){

		$min = defined( 'RMV_DEBUG' ) ? '' : '.min';
		$build_dir = defined( 'RMV_DEBUG' ) ? '/build' : '';
		$admin_js = "/assets/js{$build_dir}/admin{$min}.js";
		$admin_js_time = defined( 'RMV_DEBUG' ) ? filemtime( __FILE__ ) : null;

		if( ! wp_script_is( 'chosen', 'registered' ) ){
			wp_register_script( 'chosen', JOB_MANAGER_VISIBILITY_PLUGIN_URL . "/assets/js/chosen.jquery.js", array('jquery'), NULL );
		}

		if( ! wp_style_is( 'chosen', 'registered' ) ){
			wp_register_style( 'chosen', JOB_MANAGER_VISIBILITY_PLUGIN_URL . "/assets/css/chosen.min.css", array(), NULL );
		}

		wp_register_style( 'jmv-admin-css', JOB_MANAGER_VISIBILITY_PLUGIN_URL . "/assets/css{$build_dir}/admin{$min}.css", array( 'chosen' ), $admin_js_time );
		wp_register_style( 'front-awesome-430', JOB_MANAGER_VISIBILITY_PLUGIN_URL . "/assets/css/vendor.min.css", array(), null );
		wp_register_script( 'jmv-admin-js', JOB_MANAGER_VISIBILITY_PLUGIN_URL . $admin_js, array( 'jquery', 'chosen', 'jquery-ui-spinner' ), null, true );
		//wp_register_script( 'jmv-default-js', JOB_MANAGER_VISIBILITY_PLUGIN_URL . "/assets/js/single/default.js", array('jquery', 'chosen' ), $admin_js_time, TRUE );
		//wp_register_script( 'jmv-groups-js', JOB_MANAGER_VISIBILITY_PLUGIN_URL . "/assets/js/single/groups.js", array( 'jquery', 'chosen', 'jquery-ui-spinner' ), $admin_js_time, true );

	}

	/**
	 * Enqueue Admin CSS & JS
	 *
	 *
	 * @since @@version
	 *
	 * @param $hook
	 */
	function enqueue( $hook ){
		global $post;

		$post_types = WP_Job_Manager_Visibility_CPT::get_post_types();

		if ( $hook === 'resume_page_resume-manager-settings' ) {
			wp_enqueue_style( 'jmv-admin-css' );
			//wp_enqueue_script( 'jmv-settings-js' );
			wp_enqueue_style( 'front-awesome-430' );
			wp_enqueue_script( 'jmv-admin-js' );
		}

		if ( empty( $hook ) || ! ( $hook === 'post.php' || $hook === 'post-new.php' || $hook === 'edit.php') ) return;
		if ( ! is_object( $post ) || ! in_array( $post->post_type, $post_types )) return;

		wp_enqueue_style( 'jmv-admin-css' );
		wp_enqueue_style( 'front-awesome-430' );
		wp_enqueue_script( 'jmv-admin-js' );

	}
}