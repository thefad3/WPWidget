<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WP_Job_Manager_Form' ) )
	include( JOB_MANAGER_PLUGIN_DIR . '/includes/abstracts/abstract-wp-job-manager-form.php' );
if ( ! class_exists( 'WP_Job_Manager_Form_Submit_Job' ) )
	require_once( JOB_MANAGER_PLUGIN_DIR . '/includes/forms/class-wp-job-manager-form-submit-job.php' );

class WP_Job_Manager_Visibility_Integration_JM extends WP_Job_Manager_Form_Submit_Job {


	/**
	 * Override parent class construct
	 */
	public function __construct() {

	}

	/**
	 * Get all job (and company) fields
	 *
	 * To get all the job fields we have to extend the submit job class, set the fields
	 * to null, re-initialize the fields, and then use the abstract method to return them.
	 *
	 * @since @@since
	 *
	 * @param string $type
	 *
	 * @return array
	 */
	function get_all_fields( $type = 'job' ) {

		$this->fields = NULL;
		$this->init_fields();
		$fields = $this->get_fields( $type );
		return $fields;
	}
}